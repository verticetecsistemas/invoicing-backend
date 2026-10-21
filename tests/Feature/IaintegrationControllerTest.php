<?php

namespace Tests\Feature;

use App\Models\Iaintegration;
use App\Models\IaintegrationDetail;
use App\Models\Convenios;
use App\Models\Credenciados;
use App\Models\CredenciadosConvenios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IaintegrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_insert_saves_array_payload_and_maps_nested_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/iaintegration/insert', [[
            'arquivo' => 'GUIA.pdf',
            'pagina' => 1,
            'campos' => [
                'registro_ans' => '421715',
                'numero_guia_prestador' => '1909852038',
                'numero_guia_operadora' => null,
                'numero_carteira' => '954490068845002',
                'validade_carteira' => null,
                'atendimento_rn' => 'N',
                'nome_beneficiario' => 'JOEL DE FREITAS ESTEVO',
                'codigo_operadora' => '299421',
                'nome_contratado' => 'CTRO MEDICO NOVA IGUACU LTDA',
                'codigo_cnes' => '3145735',
                'nome_profissional_executante' => 'Bruna De Oliveira Rio',
                'conselho_profissional' => 'CRM',
                'numero_conselho' => '1203053',
                'indicacao_acidente' => ['codigo' => '9', 'descricao' => 'Nao'],
                'data_atendimento' => '21/09/2026',
                'tipo_consulta' => ['codigo' => '1', 'descricao' => 'Primeira'],
                'indicador_cobertura_especial' => null,
                'regime_atendimento' => '01',
                'saude_ocupacional' => null,
            ],
        ]]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.arquivo', 'GUIA.pdf')
            ->assertJsonPath('data.0.pagina', 1);

        $this->assertDatabaseHas('iaintegration', [
            'arquivo' => 'GUIA.pdf',
            'pagina' => 1,
            'codigo_ans' => '421715',
            'guia_prestador' => '1909852038',
            'ia_codigo' => '9',
            'ia_descricao' => 'Nao',
            'tp_cons_codigo' => '1',
            'tp_cons_descricao' => 'Primeira',
        ]);

        $this->assertSame(1, Iaintegration::count());
    }

    public function test_insert_rejects_invalid_array_payload(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/iaintegration/insert', [
            ['arquivo' => 'GUIA.pdf', 'pagina' => 0],
        ])->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('iaintegration', 0);
    }

    public function test_index_defaults_to_today_and_returns_integration_status_and_error_details(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $convenio = Convenios::create([
            'cnpj' => '12345678000190',
            'razao_social' => 'Convênio Teste',
            'nome_fantasia' => 'Convênio',
            'ans' => '421715',
        ]);
        $credenciado = Credenciados::create([
            'tipo' => 'J',
            'cnpj_cpf' => '98765432000110',
            'razao_social_nome' => 'Clínica Teste LTDA',
            'nome_fantasia_apelido' => 'Clínica',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '1',
            'bairro' => 'Centro',
            'cidade' => 'Rio de Janeiro',
            'uf' => 'RJ',
        ]);
        CredenciadosConvenios::create([
            'credenciado_id' => $credenciado->id,
            'convenio_id' => $convenio->id,
            'codigo_convenio' => '299421',
        ]);

        $failedIntegration = Iaintegration::create([
            'arquivo' => 'GUIA.pdf',
            'pagina' => 1,
            'codigo_ans' => '421715',
            'codigo_operadora' => '299421',
            'guia_prestador' => 'GUIA-ERRO',
            'integrada' => 0,
        ]);
        IaintegrationDetail::create([
            'iaintegration_id' => $failedIntegration->id,
            'exception_class' => 'DomainException',
            'message' => "Médico com conselho '702129/RJ' não encontrado.",
        ]);
        IaintegrationDetail::create([
            'iaintegration_id' => $failedIntegration->id,
            'exception_class' => 'DomainException',
            'message' => 'O vínculo de convênio não possui código e valor de consulta cadastrados.',
        ]);

        $pendingIntegration = Iaintegration::create([
            'arquivo' => 'GUIA.pdf',
            'pagina' => 2,
            'guia_prestador' => 'GUIA-PENDENTE',
            'integrada' => 0,
        ]);
        $pendingIntegration->created_at = now()->subDay();
        $pendingIntegration->save();

        $this->getJson('/api/iaintegration/index')
            ->assertOk()
            ->assertJsonPath('data_selecionada', now()->toDateString())
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data', now()->toDateString())
            ->assertJsonPath('data.0.credenciado', 'Clínica')
            ->assertJsonPath('data.0.convenio', 'Convênio')
            ->assertJsonPath('data.0.guia', 'GUIA-ERRO')
            ->assertJsonPath('data.0.status', 'erro')
            ->assertJsonCount(1, 'data.0.detalhes')
            ->assertJsonPath('data.0.detalhes.0.classe', 'DomainException')
            ->assertJsonPath('data.0.detalhes.0.mensagem', 'O vínculo de convênio não possui código e valor de consulta cadastrados.');
    }

    public function test_index_filters_by_selected_date_and_rejects_invalid_date(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $integration = Iaintegration::create([
            'arquivo' => 'GUIA.pdf',
            'pagina' => 1,
            'guia_prestador' => 'GUIA-INTEGRADA',
        ]);
        $integration->integrada = 1;
        $integration->created_at = now()->subDay();
        $integration->save();

        $this->getJson('/api/iaintegration/index?data='.now()->subDay()->toDateString())
            ->assertOk()
            ->assertJsonPath('data_selecionada', now()->subDay()->toDateString())
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'integrado')
            ->assertJsonPath('data.0.detalhes', []);

        $this->getJson('/api/iaintegration/index?data=17-10-2026')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data');
    }
}
