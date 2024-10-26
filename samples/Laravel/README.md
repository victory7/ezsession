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
Add this to your .env (or session config) file:
```sh
SESSION_DRIVER=ezsession;
SESSION_COOKIE=SESSTOKEN // OR any otherthing you like
```

