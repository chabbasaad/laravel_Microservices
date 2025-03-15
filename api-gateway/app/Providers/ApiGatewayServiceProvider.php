<?php

namespace App\Providers;

use App\Http\Middleware\ServiceRouter;
use App\Services\ServiceRegistry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ApiGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ServiceRegistry::class, function ($app) {
            return new ServiceRegistry();
        });
    }

    public function boot(): void
    {
        // Register the middleware
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(function () {
                Route::any('{service}/{path?}', function ($service, $path = '') {
                    return response()->json(['error' => 'Invalid request'], 400);
                })->where('path', '.*')->middleware(ServiceRouter::class);
            });
    }
}
