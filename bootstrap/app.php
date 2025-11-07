<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TwofaMiddleware;
use App\Http\Middleware\XSS;
use App\Http\Middleware\SecureHeaders;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['2fa' => TwofaMiddleware::class]);
        $middleware->append(XSS::class);
         $middleware->append(SecureHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
