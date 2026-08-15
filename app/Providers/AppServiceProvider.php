<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\ConveniosRepositoryInterface;
use App\Repositories\ConveniosRepository;
use App\Repositories\Contracts\TabelasRepositoryInterface;
use App\Repositories\TabelasRepository;
use App\Repositories\Contracts\TabelasItensRepositoryInterface;
use App\Repositories\TabelasItensRepository;
use App\Repositories\Contracts\UnidadesRepositoryInterface;
use App\Repositories\UnidadesRepository;
use App\Repositories\Contracts\GruposRepositoryInterface;
use App\Repositories\GruposRepository;
use App\Repositories\Contracts\MedicosRepositoryInterface;
use App\Repositories\MedicosRepository;
use App\Repositories\Contracts\PacientesRepositoryInterface;
use App\Repositories\PacientesRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding do Repository Pattern
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ConveniosRepositoryInterface::class, ConveniosRepository::class);
        $this->app->bind(TabelasRepositoryInterface::class, TabelasRepository::class);
        $this->app->bind(TabelasItensRepositoryInterface::class, TabelasItensRepository::class);
        $this->app->bind(UnidadesRepositoryInterface::class, UnidadesRepository::class);
        $this->app->bind(GruposRepositoryInterface::class, GruposRepository::class);
        $this->app->bind(MedicosRepositoryInterface::class, MedicosRepository::class);
        $this->app->bind(PacientesRepositoryInterface::class, PacientesRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
