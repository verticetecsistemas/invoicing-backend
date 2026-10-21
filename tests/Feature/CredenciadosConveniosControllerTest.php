<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CredenciadosConveniosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class CredenciadosConveniosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_names_returns_convenio_ids_and_names_for_credenciado(): void
    {
        $credenciadoId = 42;
        Sanctum::actingAs(User::factory()->create());

        $this->mock(CredenciadosConveniosService::class, function (MockInterface $mock) use ($credenciadoId) {
            $mock->shouldReceive('getConvenioNamesByCredenciadoId')
                ->once()
                ->with($credenciadoId)
                ->andReturn([
                    [
                        'convenio_id' => 5,
                        'nome' => 'Convênio A',
                        'entrega_consulta' => '2026-10-01',
                        'entrega_sadt' => '2026-10-02',
                        'entrega_honorario' => '2026-10-03',
                    ],
                    [
                        'convenio_id' => 8,
                        'nome' => 'Convênio B',
                        'entrega_consulta' => null,
                        'entrega_sadt' => null,
                        'entrega_honorario' => null,
                    ],
                ]);
        });

        $this->getJson("/api/credenciados-convenios/nomes/{$credenciadoId}")
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => [
                    [
                        'convenio_id' => 5,
                        'nome' => 'Convênio A',
                        'entrega_consulta' => '2026-10-01',
                        'entrega_sadt' => '2026-10-02',
                        'entrega_honorario' => '2026-10-03',
                    ],
                    [
                        'convenio_id' => 8,
                        'nome' => 'Convênio B',
                        'entrega_consulta' => null,
                        'entrega_sadt' => null,
                        'entrega_honorario' => null,
                    ],
                ],
            ]);
    }
}
