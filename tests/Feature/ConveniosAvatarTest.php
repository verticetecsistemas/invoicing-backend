<?php

namespace Tests\Feature;

use App\Models\Convenios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConveniosAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_saves_avatar_and_index_returns_its_path(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/convenios/create', [
            'cnpj' => '12345678000190',
            'razao_social' => 'Convenio Teste',
            'avatar' => 'data:image/jpeg;base64,' . base64_encode('imagem de teste'),
        ]);

        $response->assertCreated();

        $avatarPath = $response->json('data.avatar');
        $this->assertNotEmpty($avatarPath);
        Storage::disk('public')->assertExists($avatarPath);
        $this->assertDatabaseHas('convenios', [
            'cnpj' => '12345678000190',
            'avatar' => $avatarPath,
        ]);

        $this->getJson('/api/convenios/index')
            ->assertOk()
            ->assertJsonPath('data.0.avatar', $avatarPath);
    }
}
