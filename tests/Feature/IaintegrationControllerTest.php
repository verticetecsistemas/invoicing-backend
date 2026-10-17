<?php

namespace Tests\Feature;

use App\Models\Iaintegration;
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
}
