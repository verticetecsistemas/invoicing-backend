<?php

namespace Tests\Feature;

use App\Models\Credenciados;
use App\Models\Empresas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CredenciadosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_user_object_with_credenciados_and_empresa(): void
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

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Usuário Teste',
            'email' => 'teste@exemplo.com',
        ]);

        $credenciado = Credenciados::create([
            'empresa_id' => $empresa->id,
            'tipo' => 'J',
            'cnpj_cpf' => '98765432000110',
            'razao_social_nome' => 'Clínica Teste',
            'nome_fantasia_apelido' => 'Clínica',
            'cep' => '12345678',
            'logradouro' => 'Rua Teste',
            'numero' => '456',
            'bairro' => 'Bairro Teste',
            'cidade' => 'Cidade Teste',
            'uf' => 'SP',
        ]);

        $user->credenciados()->attach($credenciado->id, ['permissao' => 'A']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/credenciados/index');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'empresa_id' => $empresa->id,
                    'name' => 'Usuário Teste',
                    'email' => 'teste@exemplo.com',
                    'empresa' => [
                        'id' => $empresa->id,
                        'razao_social_nome' => 'Empresa Teste LTDA',
                    ],
                ],
            ]);

        $this->assertIsArray($response->json('data.credenciados'));
        $this->assertCount(1, $response->json('data.credenciados'));
        $this->assertEquals($credenciado->id, $response->json('data.credenciados.0.id'));
    }

    public function test_index_returns_user_object_when_empresa_is_null(): void
    {
        $user = User::factory()->create([
            'empresa_id' => null,
            'name' => 'Usuário Sem Empresa',
            'email' => 'semempresa@exemplo.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/credenciados/index');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'empresa_id' => null,
                    'name' => 'Usuário Sem Empresa',
                    'empresa' => null,
                    'credenciados' => [],
                ],
            ]);
    }
}
