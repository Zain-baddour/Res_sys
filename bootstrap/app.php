<?php

use App\Http\Middleware\BlockedUserMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // ✅ هي المكان المخصص لتعريف الـ RateLimiter
            RateLimiter::for('login', function (Request $request) {
                $email = (string) $request->input('email');
                return [
                    Limit::perMinute(5)->by($email.$request->ip()),
                ];
            });
        }
    )
    ->withSchedule(function (Schedule $schedule){
        (require __DIR__.'/../routes/console.php')($schedule);
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'blocked' => BlockedUserMiddleware::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
