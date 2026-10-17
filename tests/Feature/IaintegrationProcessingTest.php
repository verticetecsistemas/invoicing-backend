<?php

namespace Tests\Feature;

use App\Models\Conselhos;
use App\Models\Convenios;
use App\Models\Credenciados;
use App\Models\CredenciadosConvenios;
use App\Models\Especialidades;
use App\Models\Guias;
use App\Models\GuiasConsulta;
use App\Models\Iaintegration;
use App\Models\IaintegrationDetail;
use App\Models\Medicos;
use App\Models\Pacientes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IaintegrationProcessingTest extends TestCase
{
    use RefreshDatabase;

    private int $fixtureCredenciadoId;

    public function test_process_creates_guide_and_consultation_and_marks_integration(): void
    {
        $user = User::factory()->create(['id' => 1]);
        Sanctum::actingAs($user);
        $integration = $this->createPendingIntegration();

        $response = $this->postJson('/api/iaintegration/process');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.processed', 1)
            ->assertJsonPath('data.failed', 0);

        $this->assertDatabaseHas('iaintegration', [
            'id' => $integration->id,
            'integrada' => 1,
        ]);
        $this->assertDatabaseCount('iaintegration_details', 0);

        $guia = Guias::query()->firstOrFail();
        $this->assertSame('C', $guia->tipo);
        $this->assertSame($this->fixtureCredenciadoId, $guia->credenciado_id);
        $this->assertSame(Convenios::where('ans', '421715')->value('id'), $guia->convenio_id);
        $this->assertSame(1, $guia->usuario_id);
        $this->assertSame('Pendente', $guia->status);
        $this->assertSame('1909852038', $guia->numero);
        $this->assertSame('2026-09-21', $guia->getRawOriginal('data'));
        $this->assertSame('2026-09-22', $guia->getRawOriginal('data_entrega'));

        $consulta = GuiasConsulta::query()->firstOrFail();
        $this->assertSame($guia->id, $consulta->guia_id);
        $this->assertSame('1203053', Medicos::findOrFail($consulta->executante_id)->numero_conselho);
        $this->assertSame('1', $consulta->tipo);
        $this->assertSame('22', $consulta->tabela);
        $this->assertSame('CONSULTA ELETIVA', $consulta->descricao);
        $this->assertEquals(150, $consulta->valor_total);
        $this->assertSame('9', $consulta->indicacao_acidente);

        $paciente = Pacientes::withoutGlobalScope('empresa')->firstOrFail();
        $this->assertSame('JOEL DE FREITAS ESTEVO', $paciente->nome);
        $this->assertSame('954490068845002', $paciente->matricula);
        $this->assertSame('2026-09-30', $paciente->getRawOriginal('validade_carteira'));
    }

    public function test_missing_existing_doctor_keeps_integration_pending(): void
    {
        $user = User::factory()->create(['id' => 1]);
        Sanctum::actingAs($user);
        $integration = $this->createPendingIntegration(false);

        $this->postJson('/api/iaintegration/process')
            ->assertStatus(207)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.failed', 1)
            ->assertJsonPath('data.errors.0.iaintegration_id', $integration->id);

        $this->assertDatabaseHas('iaintegration', [
            'id' => $integration->id,
            'integrada' => 0,
        ]);
        $this->assertDatabaseHas('iaintegration_details', [
            'iaintegration_id' => $integration->id,
            'exception_class' => \DomainException::class,
            'message' => "Médico com conselho '1203053/RJ' não encontrado.",
        ]);
        $this->assertDatabaseCount('guias', 0);
        $this->assertDatabaseCount('guias_consulta', 0);
        $this->assertDatabaseCount('pacientes', 0);
        $this->assertDatabaseCount('medicos', 0);

        $this->postJson('/api/iaintegration/process')
            ->assertStatus(207)
            ->assertJsonPath('data.failed', 1);

        $this->assertSame(2, IaintegrationDetail::where('iaintegration_id', $integration->id)->count());
    }

    public function test_duplicate_guide_is_rejected_and_recorded_as_integration_error(): void
    {
        Sanctum::actingAs(User::factory()->create(['id' => 1]));
        $integration = $this->createPendingIntegration();
        $convenioId = Convenios::where('ans', '421715')->value('id');
        $paciente = Pacientes::create([
            'nome' => 'Paciente Existente',
            'convenio_id' => $convenioId,
            'matricula' => 'MATRICULA-EXISTENTE',
        ]);

        Guias::create([
            'tipo' => 'C',
            'credenciado_id' => $this->fixtureCredenciadoId,
            'convenio_id' => $convenioId,
            'paciente_id' => $paciente->id,
            'usuario_id' => 1,
            'numero' => $integration->guia_prestador,
            'numero_operadora' => null,
            'data' => '2026-09-21',
            'data_entrega' => '2026-09-22',
            'status' => 'Pendente',
        ]);

        $this->postJson('/api/iaintegration/process')
            ->assertStatus(207)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.failed', 1)
            ->assertJsonPath('data.errors.0.message', 'guia em duplicidade');

        $this->assertDatabaseHas('iaintegration', [
            'id' => $integration->id,
            'integrada' => 0,
        ]);
        $this->assertDatabaseHas('iaintegration_details', [
            'iaintegration_id' => $integration->id,
            'message' => 'guia em duplicidade',
        ]);
        $this->assertDatabaseCount('guias', 1);
        $this->assertDatabaseCount('guias_consulta', 0);
    }

    private function createPendingIntegration(bool $createDoctor = true): Iaintegration
    {
        $convenio = Convenios::create([
            'cnpj' => '12345678000190',
            'razao_social' => 'Convênio Teste',
            'ans' => '421715',
        ]);

        $credenciado = Credenciados::create([
            'tipo' => 'J',
            'cnpj_cpf' => '98765432000110',
            'razao_social_nome' => 'Clínica Teste LTDA',
            'nome_fantasia_apelido' => 'Clínica Teste',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '1',
            'bairro' => 'Centro',
            'cidade' => 'Rio de Janeiro',
            'uf' => 'RJ',
        ]);

        $vinculoConvenio = Convenios::create([
            'cnpj' => '12345678000191',
            'razao_social' => 'Outro Convênio',
            'ans' => '999999',
        ]);

        $credenciadoDecoy = Credenciados::create([
            'tipo' => 'J',
            'cnpj_cpf' => '98765432000111',
            'razao_social_nome' => 'Clínica Incorreta LTDA',
            'nome_fantasia_apelido' => 'Clínica Incorreta',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '2',
            'bairro' => 'Centro',
            'cidade' => 'Rio de Janeiro',
            'uf' => 'RJ',
        ]);

        CredenciadosConvenios::create([
            'credenciado_id' => $credenciadoDecoy->id,
            'convenio_id' => $vinculoConvenio->id,
            'codigo_convenio' => '299421',
            'codigo_consulta' => '99999999',
            'valor_consulta' => '999.00',
            'entrega_consulta' => '2026-09-22',
        ]);

        CredenciadosConvenios::create([
            'credenciado_id' => $credenciado->id,
            'convenio_id' => $convenio->id,
            'codigo_convenio' => '299421',
            'codigo_consulta' => '10101012',
            'valor_consulta' => '150.00',
            'entrega_consulta' => '2026-09-22',
        ]);
        $this->fixtureCredenciadoId = $credenciado->id;

        if ($createDoctor) {
            $conselho = Conselhos::create([
                'nome' => 'Conselho Regional de Medicina',
                'sigla' => 'CRM',
            ]);
            $especialidade = Especialidades::create([
                'nome' => 'Clínica Médica',
                'cbos' => '225125',
            ]);

            Medicos::create([
                'nome' => 'Bruna De Oliveira Rio',
                'cpf' => '12345678901',
                'especialidade_id' => $especialidade->id,
                'conselho_id' => $conselho->id,
                'numero_conselho' => '1203053',
                'uf_conselho' => 'RJ',
            ]);
        }

        return Iaintegration::create([
            'arquivo' => 'GUIA.pdf',
            'pagina' => 1,
            'codigo_ans' => '421715',
            'guia_prestador' => '1909852038',
            'guia_operadora' => null,
            'carteira' => '954490068845002',
            'nome_beneficiario' => 'JOEL DE FREITAS ESTEVO',
            'validade_carteira' => '30/09/2026',
            'codigo_operadora' => '299421',
            'numero_conselho' => '1203053',
            'atendimento_rn' => 'N',
            'ia_codigo' => '9',
            'data_atendimento' => '21/09/2026',
            'tp_cons_codigo' => '1',
            'cobertura_especial' => null,
            'regime_atendimento' => '01',
            'saude_ocupacional' => null,
        ]);
    }
}
