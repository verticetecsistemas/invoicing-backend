<?php

namespace Tests\Feature;

use App\Models\Credenciados;
use App\Models\Empresas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RelatorioRepasseControllerTest extends TestCase
{
    use RefreshDatabase;

    private Empresas $empresa;

    private Credenciados $credenciado;

    private int $convenioId;

    private int $convenioOutroId;

    private int $pacienteId;

    private int $medicoA;

    private int $medicoB;

    private int $medicoAux;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = $this->createEmpresa('12345678000190');
        $user = User::factory()->create(['empresa_id' => $this->empresa->id]);
        $this->credenciado = $this->createCredenciado($this->empresa, '98765432000110');

        $this->convenioId = $this->insertConvenio('11111111000111', 'Unimed');
        $this->convenioOutroId = $this->insertConvenio('22222222000122', 'Amil');
        $this->pacienteId = DB::table('pacientes')->insertGetId([
            'empresa_id' => $this->empresa->id,
            'nome' => 'Zélia Paciente',
            'convenio_id' => $this->convenioId,
            'matricula' => '123',
        ]);

        $especialidadeId = DB::table('especialidades')->insertGetId(['nome' => 'Clínica', 'cbos' => '225125']);
        $conselhoId = DB::table('conselhos')->insertGetId(['nome' => 'CRM', 'sigla' => 'CRM', 'codigo' => '06']);
        $this->medicoA = $this->insertMedico('Dr. Bruno', '111', $especialidadeId, $conselhoId);
        $this->medicoB = $this->insertMedico('Dra. Ana', '222', $especialidadeId, $conselhoId);
        $this->medicoAux = $this->insertMedico('Dr. Carlos', '333', $especialidadeId, $conselhoId);

        Sanctum::actingAs($user);
    }

    public function test_analitico_groups_by_medico_and_applies_payload_filters(): void
    {
        $g1 = $this->insertGuia('0002', '2026-10-05', '2026-10-20');
        $g2 = $this->insertGuia('0001', '2026-10-10', '2026-10-21');
        $this->insertGuia('0003', '2026-09-30', '2026-10-21', ['convenio_id' => $this->convenioId]);
        $this->insertGuia('0004', '2026-10-12', '2026-10-21', ['convenio_id' => $this->convenioOutroId]);

        $this->insertItem($g1, $this->medicoA, ['codigo' => '10101012', 'valor_total' => 100, 'valor_pago' => 80]);
        $this->insertItem($g1, $this->medicoA, ['codigo' => '10101013', 'valor_total' => 50, 'valor_pago' => 50]);
        $this->insertItem($g1, $this->medicoB, ['valor_total' => 70, 'valor_pago' => 0]);
        $this->insertItem($g2, $this->medicoA, ['valor_total' => 30, 'valor_pago' => 30]);

        $response = $this->postJson('/api/relatorios/repasse', [
            'credenciado_id' => $this->credenciado->id,
            'tipo_periodo' => 'atendimento',
            'data_inicial' => '2026-10-01',
            'data_final' => '2026-10-31',
            'convenio_id' => $this->convenioId,
            'medico_id' => $this->medicoA,
            'baseado_medico_auxiliar' => false,
            'medico_auxiliar_id' => null,
            'ordenacao' => 'guia',
            'tipo_relatorio' => 'repasse_analitico',
            'ocultar_medico' => false,
            'procedimento_inicial' => null,
            'procedimento_final' => null,
            'guia' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.credenciado.id', $this->credenciado->id)
            ->assertJsonPath('data.tipo_relatorio', 'repasse_analitico')
            ->assertJsonCount(1, 'data.medicos')
            ->assertJsonPath('data.medicos.0.medico_id', $this->medicoA)
            ->assertJsonPath('data.medicos.0.nome', 'Dr. Bruno')
            ->assertJsonPath('data.medicos.0.totais.guias', 2)
            ->assertJsonPath('data.medicos.0.totais.procedimentos', 3)
            ->assertJsonPath('data.medicos.0.totais.valor_total', 180)
            ->assertJsonPath('data.medicos.0.totais.valor_pago', 160)
            ->assertJsonPath('data.medicos.0.totais.saldo', 20)
            ->assertJsonPath('data.medicos.0.guias.0.numero', '0001')
            ->assertJsonPath('data.medicos.0.guias.1.numero', '0002')
            ->assertJsonCount(2, 'data.medicos.0.guias.1.procedimentos')
            ->assertJsonPath('data.medicos.0.guias.1.paciente', 'Zélia Paciente')
            ->assertJsonPath('data.medicos.0.guias.1.convenio', 'Unimed')
            ->assertJsonPath('data.totais.valor_total', 180);
    }

    public function test_sintetico_without_medico_returns_flat_guias_without_procedimentos(): void
    {
        $g1 = $this->insertGuia('0001', '2026-10-05', '2026-10-20');
        $this->insertItem($g1, $this->medicoA, ['valor_total' => 100, 'valor_pago' => 100]);
        $this->insertItem($g1, $this->medicoB, ['valor_total' => 40, 'valor_pago' => 10]);

        $response = $this->postJson('/api/relatorios/repasse', $this->payload([
            'tipo_relatorio' => 'repasse_sintetico',
            'ocultar_medico' => true,
        ]));

        $response->assertOk()
            ->assertJsonMissingPath('data.medicos')
            ->assertJsonCount(1, 'data.guias')
            ->assertJsonMissingPath('data.guias.0.procedimentos')
            ->assertJsonPath('data.guias.0.valor_total', 140)
            ->assertJsonPath('data.guias.0.valor_pago', 110)
            ->assertJsonPath('data.guias.0.saldo', 30)
            ->assertJsonPath('data.totais.procedimentos', 2);
    }

    public function test_period_type_pagamento_filters_by_item_payment_date(): void
    {
        $g1 = $this->insertGuia('0001', '2026-08-01', '2026-08-10');
        $this->insertItem($g1, $this->medicoA, ['valor_total' => 10, 'valor_pago' => 10, 'data_pagamento' => '2026-10-15']);
        $this->insertItem($g1, $this->medicoA, ['valor_total' => 20, 'valor_pago' => 20, 'data_pagamento' => '2026-11-15']);
        $this->insertItem($g1, $this->medicoA, ['valor_total' => 30, 'valor_pago' => 0, 'data_pagamento' => null]);

        $this->postJson('/api/relatorios/repasse', $this->payload(['tipo_periodo' => 'pagamento']))
            ->assertOk()
            ->assertJsonPath('data.totais.procedimentos', 1)
            ->assertJsonPath('data.totais.valor_pago', 10);

        $this->postJson('/api/relatorios/repasse', $this->payload(['tipo_periodo' => 'entrega']))
            ->assertOk()
            ->assertJsonPath('data.totais.procedimentos', 0);
    }

    public function test_baseado_medico_auxiliar_groups_by_auxiliar_and_ignores_items_without_one(): void
    {
        $g1 = $this->insertGuia('0001', '2026-10-05', '2026-10-20');
        $this->insertItem($g1, $this->medicoA, ['auxiliar_id' => $this->medicoAux, 'valor_total' => 60, 'valor_pago' => 60]);
        $this->insertItem($g1, $this->medicoA, ['auxiliar_id' => null, 'valor_total' => 40, 'valor_pago' => 40]);

        $this->postJson('/api/relatorios/repasse', $this->payload([
            'baseado_medico_auxiliar' => true,
            'medico_auxiliar_id' => $this->medicoAux,
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data.medicos')
            ->assertJsonPath('data.medicos.0.medico_id', $this->medicoAux)
            ->assertJsonPath('data.medicos.0.totais.valor_total', 60);

        $this->postJson('/api/relatorios/repasse', $this->payload([
            'baseado_medico_auxiliar' => true,
            'medico_auxiliar_id' => $this->medicoB,
        ]))
            ->assertOk()
            ->assertJsonCount(0, 'data.medicos');
    }

    public function test_filters_by_procedimento_range_and_guia_and_orders_by_paciente(): void
    {
        $outroPaciente = DB::table('pacientes')->insertGetId([
            'empresa_id' => $this->empresa->id,
            'nome' => 'Abel Paciente',
            'convenio_id' => $this->convenioId,
            'matricula' => '456',
        ]);
        $g1 = $this->insertGuia('0001', '2026-10-05', '2026-10-20');
        $g2 = $this->insertGuia('0002', '2026-10-06', '2026-10-20', ['paciente_id' => $outroPaciente, 'numero_operadora' => 'OP-9']);

        $this->insertItem($g1, $this->medicoA, ['codigo' => '10101012']);
        $this->insertItem($g1, $this->medicoA, ['codigo' => '20202020']);
        $this->insertItem($g2, $this->medicoA, ['codigo' => '10101013']);

        $this->postJson('/api/relatorios/repasse', $this->payload([
            'procedimento_inicial' => '10101000',
            'procedimento_final' => '10101999',
            'ordenacao' => 'paciente',
        ]))
            ->assertOk()
            ->assertJsonPath('data.totais.procedimentos', 2)
            ->assertJsonPath('data.medicos.0.guias.0.paciente', 'Abel Paciente')
            ->assertJsonPath('data.medicos.0.guias.1.paciente', 'Zélia Paciente');

        $this->postJson('/api/relatorios/repasse', $this->payload(['guia' => 'OP-9']))
            ->assertOk()
            ->assertJsonPath('data.totais.guias', 1)
            ->assertJsonPath('data.medicos.0.guias.0.numero', '0002');
    }

    public function test_validation_errors(): void
    {
        $this->postJson('/api/relatorios/repasse', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['credenciado_id', 'tipo_periodo', 'data_inicial', 'data_final']);

        $this->postJson('/api/relatorios/repasse', $this->payload(['data_inicial' => '2026-11-01']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['data_final']);

        $this->postJson('/api/relatorios/repasse', $this->payload(['tipo_periodo' => 'x', 'ordenacao' => 'y', 'tipo_relatorio' => 'z']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tipo_periodo', 'ordenacao', 'tipo_relatorio']);

        $this->postJson('/api/relatorios/repasse', $this->payload(['procedimento_inicial' => '2', 'procedimento_final' => '1']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['procedimento_final']);
    }

    public function test_rejects_credenciado_from_another_empresa(): void
    {
        $outraEmpresa = $this->createEmpresa('55555555000155');
        $outroId = DB::table('credenciados')->insertGetId([
            'empresa_id' => $outraEmpresa->id,
            'tipo' => 'J',
            'cnpj_cpf' => '66666666000166',
            'razao_social_nome' => 'Outra Clínica',
            'nome_fantasia_apelido' => 'Outra',
            'cep' => '12345678',
            'logradouro' => 'Rua',
            'numero' => '1',
            'bairro' => 'Centro',
            'cidade' => 'Cidade',
            'uf' => 'SP',
        ]);

        $this->postJson('/api/relatorios/repasse', $this->payload(['credenciado_id' => $outroId]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['credenciado_id']);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'credenciado_id' => $this->credenciado->id,
            'tipo_periodo' => 'atendimento',
            'data_inicial' => '2026-10-01',
            'data_final' => '2026-10-31',
            'ordenacao' => 'guia',
            'tipo_relatorio' => 'repasse_analitico',
        ];
    }

    private function createEmpresa(string $cnpj): Empresas
    {
        return Empresas::create([
            'tipo' => 'J',
            'cnpj_cpf' => $cnpj,
            'razao_social_nome' => 'Empresa '.$cnpj,
            'nome_fantasia_apelido' => 'Empresa',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Bairro Teste',
            'cidade' => 'Cidade Teste',
            'uf' => 'SP',
        ]);
    }

    private function createCredenciado(Empresas $empresa, string $cnpj): Credenciados
    {
        return Credenciados::create([
            'empresa_id' => $empresa->id,
            'tipo' => 'J',
            'cnpj_cpf' => $cnpj,
            'razao_social_nome' => 'Clínica Teste',
            'nome_fantasia_apelido' => 'Clínica',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '456',
            'bairro' => 'Bairro Teste',
            'cidade' => 'Cidade Teste',
            'uf' => 'SP',
        ]);
    }

    private function insertConvenio(string $cnpj, string $razaoSocial): int
    {
        return DB::table('convenios')->insertGetId(['cnpj' => $cnpj, 'razao_social' => $razaoSocial]);
    }

    private function insertMedico(string $nome, string $cpf, int $especialidadeId, int $conselhoId): int
    {
        return DB::table('medicos')->insertGetId([
            'nome' => $nome,
            'cpf' => $cpf,
            'especialidade_id' => $especialidadeId,
            'conselho_id' => $conselhoId,
            'numero_conselho' => $cpf,
            'uf_conselho' => 'RJ',
        ]);
    }

    private function insertGuia(string $numero, string $data, string $dataEntrega, array $overrides = []): int
    {
        return DB::table('guias')->insertGetId($overrides + [
            'tipo' => 'C',
            'credenciado_id' => $this->credenciado->id,
            'convenio_id' => $this->convenioId,
            'paciente_id' => $this->pacienteId,
            'usuario_id' => User::query()->value('id'),
            'numero' => $numero,
            'numero_operadora' => null,
            'data' => $data,
            'data_entrega' => $dataEntrega,
            'status' => 'ABERTA',
        ]);
    }

    private function insertItem(int $guiaId, int $executanteId, array $overrides = []): int
    {
        return DB::table('guias_consulta')->insertGetId($overrides + [
            'guia_id' => $guiaId,
            'executante_id' => $executanteId,
            'auxiliar_id' => null,
            'tipo' => 'C',
            'tabela' => '22',
            'codigo' => '10101012',
            'descricao' => 'Consulta',
            'quantidade' => 1,
            'valor' => 10,
            'valor_total' => 10,
            'valor_pago' => 0,
            'data_pagamento' => null,
            'atendimento_rn' => 'N',
            'indicacao_acidente' => '9',
            'regime_atendimento' => '01',
        ]);
    }
}
