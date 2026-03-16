<?php

namespace App\Providers;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Categorias\CreateCategoriaAction;
use App\Actions\Categorias\DeleteCategoriaAction;
use App\Actions\Categorias\UpdateCategoriaAction;
use App\Queries\Categorias\GetAllCategorias;
use App\Queries\Categorias\GetCategoriaById;
use App\Services\Auth\AuthService;
use App\Services\Categorias\CategoriaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CategoriaService::class, function ($app) {
            return new CategoriaService(
                $app->make(GetAllCategorias::class),
                $app->make(GetCategoriaById::class),
                $app->make(CreateCategoriaAction::class),
                $app->make(UpdateCategoriaAction::class),
                $app->make(DeleteCategoriaAction::class),
            );
        });

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(RegisterUserAction::class),
                $app->make(LoginUserAction::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
