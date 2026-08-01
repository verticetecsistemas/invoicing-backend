<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\ConveniosRepositoryInterface;
use App\Repositories\ConveniosRepository;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
