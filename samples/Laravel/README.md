# EzSession - Laravel Sample

Replace middlware with default StartSession middleware in app/Http/kernel.php
```
protected $middlewareGroups = [
    'web' => [
            \App\Extensions\EzSession\EzSessionStartMiddleware::class,
            // \Illuminate\Session\Middleware\StartSession::class,
        ],
];
```
Choose session driver in .env (or session config) file:
```sh
SESSION_DRIVER=ezsession;
SESSION_COOKIE=SESSTOKEN // OR any otherthing you like
...
```

Add Changes to Providers/AppServiceProvider.php
```
<?php
namespace App\Providers;

use EzSession\Integrations\Laravel\EzSessionHandler;
use EzSession\Integrations\Laravel\EzSessionManager;

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
                    'database' => config('database.connections.mysql.database'),
                    'table'    => config('session.table')
                ],
                'redis' => [
                    'host'      => config('database.redis.default.host'),
                    'port'      => config('database.redis.default.port'),
                    'auth'      => config('database.redis.default.password'),
                    'cacheTime' => 60 // Seconds
                ],
                'jwt' => [
                    'secret' => config('app.key')
                ],
                'cookie' => [
                    'name'      => config('session.cookie'),
                    'path'      => config('session.path'),
                    'domain'    => config('session.domain'),
                    'secure'    => config('session.secure'),
                    'httponly'  => config('session.http_only'),
                    'same_site' => config('session.same_site'),
                    'expires'   => config('session.lifetime')
                ]
            ]);
        });
    }
}
