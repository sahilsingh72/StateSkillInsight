<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect to login page on CSRF / Session Timeout (419 Page Expired)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh and log in again.',
                    'redirect' => route('login'),
                ], 419);
            }
            return redirect()->route('login')->with('warning', 'Your session has expired due to inactivity. Please log in again to continue.');
        });

        // Redirect to login page on Unauthenticated access (401)
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('warning', 'Your session has expired. Please log in to access the portal.');
        });
    })->create();
