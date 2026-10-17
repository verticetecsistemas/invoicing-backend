<?php

namespace Tests\Feature;

use App\Models\Empresas;
use App\Models\Tabelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TabelasControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_insert_saves_empresa_id_from_authenticated_user(): void
    {
        $empresa = Empresas::create([
            'tipo' => 'J',
            'cnpj_cpf' => '12345678000190',
            'razao_social_nome' => 'Empresa Teste LTDA',
            'nome_fantasia_apelido' => 'Empresa Teste',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '123',
            'bairro' => 'Bairro Teste',
            'cidade' => 'Cidade Teste',
            'uf' => 'SP',
        ]);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tabelas/insert', [
            'nome' => 'BRADESCO TUSS',
            'tipo' => 'Pro',
            'ans' => '3434343',
            'empresa_id' => 9999,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.empresa_id', $empresa->id)
            ->assertJsonPath('data.ans', '3434343');

        $this->assertDatabaseHas('tabelas', [
            'id' => $response->json('data.id'),
            'empresa_id' => $empresa->id,
        ]);

        $this->assertSame($empresa->id, Tabelas::findOrFail($response->json('data.id'))->empresa_id);
    }
}
