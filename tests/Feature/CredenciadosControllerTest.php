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

    public function test_create_accepts_credenciado_column_names_in_payload(): void
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

        $response = $this->postJson('/api/credenciados/create', [
            'tipo' => 'J',
            'cnpj_cpf' => '23934027000157',
            'razao_social_nome' => '23.934.027 ISABEL CRISTINA DE LACERDA',
            'nome_fantasia_apelido' => '23.934.027 ISABEL CRISTINA DE LACERDA',
            'cep' => '27332460',
            'logradouro' => 'RUA SAO SEBASTIAO, CASA 02',
            'numero' => '98',
            'bairro' => 'SAO JUDAS TADEU',
            'cidade' => 'BARRA MANSA',
            'uf' => 'RJ',
            'telefone' => '(24) 9975-5460',
            'celular' => '',
            'email' => 'ccoutinho2@hotmail.com',
            'observacoes' => '',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.cnpj_cpf', '23934027000157')
            ->assertJsonPath('data.razao_social_nome', '23.934.027 ISABEL CRISTINA DE LACERDA')
            ->assertJsonPath('data.logradouro', 'RUA SAO SEBASTIAO, CASA 02')
            ->assertJsonPath('data.uf', 'RJ')
            ->assertJsonPath('data.empresa_id', $empresa->id);

        $this->assertDatabaseHas('credenciados', [
            'cnpj_cpf' => '23934027000157',
            'razao_social_nome' => '23.934.027 ISABEL CRISTINA DE LACERDA',
            'nome_fantasia_apelido' => '23.934.027 ISABEL CRISTINA DE LACERDA',
            'logradouro' => 'RUA SAO SEBASTIAO, CASA 02',
            'uf' => 'RJ',
        ]);
    }

    public function test_update_preserves_existing_logradouro_when_payload_is_empty(): void
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
        $credenciado = Credenciados::create([
            'empresa_id' => $empresa->id,
            'tipo' => 'J',
            'cnpj_cpf' => '23934027000157',
            'razao_social_nome' => 'Nome Anterior',
            'nome_fantasia_apelido' => 'Nome Anterior',
            'cep' => '27332460',
            'logradouro' => 'RUA SAO SEBASTIAO, CASA 02',
            'numero' => '98',
            'bairro' => 'SAO JUDAS TADEU',
            'cidade' => 'BARRA MANSA',
            'uf' => 'RJ',
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/credenciados/update/{$credenciado->id}", [
            'tipo' => 'J',
            'cnpj_cpf' => '23934027000157',
            'razao_social_nome' => 'ISABEL CRISTINA DE LACERDA',
            'nome_fantasia_apelido' => 'ISABEL CRISTINA DE LACERDA',
            'cep' => '27332460',
            'logradouro' => '',
            'numero' => '98',
            'bairro' => 'SAO JUDAS TADEU',
            'cidade' => 'BARRA MANSA',
            'uf' => 'RJ',
            'telefone' => '(24) 9975-5460',
            'celular' => '',
            'email' => 'ccoutinho2@hotmail.com',
            'observacoes' => '',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.razao_social_nome', 'ISABEL CRISTINA DE LACERDA')
            ->assertJsonPath('data.logradouro', 'RUA SAO SEBASTIAO, CASA 02');

        $this->assertDatabaseHas('credenciados', [
            'id' => $credenciado->id,
            'logradouro' => 'RUA SAO SEBASTIAO, CASA 02',
            'telefone' => '(24) 9975-5460',
        ]);
    }
}
