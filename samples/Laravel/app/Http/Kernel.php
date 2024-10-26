<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Extensions\EzSession\EzSessionStartMiddleware::class,
            // \Illuminate\Session\Middleware\StartSession::class,
        ],
    ];
}
