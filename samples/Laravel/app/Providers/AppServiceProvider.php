<?php

namespace App\Providers;

use App\Extensions\EzSession\EzSessionHandler;
use App\Extensions\EzSession\EzSessionManager;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Replace the default session manager with your custom manager
        $this->app->singleton('session', function ($app) {
            return new EzSessionManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {   
        Session::extend('ezsession', function ($app) {
            return new EzSessionHandler([
                'mysql' => [
                    'host'     => config('database.connections.mysql.host'),
                    'port'     => config('database.connections.mysql.port'),
                    'user'     => config('database.connections.mysql.username'),
                    'password' => config('database.connections.mysql.password'),
                    'database' => config('database.connections.mysql.database')
                ],
                'redis' => [
                    'host'     => config('database.redis.default.host'),
                    'port'     => config('database.redis.default.port'),
                    'password' => config('database.redis.default.password'),
                    'database' => config('database.redis.default.database')
                ],
                'jwt' => ['secret' => 'LpxqWRVdY9d4n4QMNb2uzjsIfBTLniK1UDIT8NbC']
            ]);;
        });
    }
}
