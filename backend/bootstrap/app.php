<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Web routes (default)
        web: __DIR__.'/../routes/web.php',
        // API routes — prefix /api, uses Sanctum auth
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Idungag ang RoleMiddleware alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // I-throttle ang tanan API requests — 60 per minute per IP
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always return JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // I-hide ang error details sa production — generic messages lang
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && app()->environment('production')) {
                // I-log ang actual error para sa debugging
                \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'url'       => $request->fullUrl(),
                    'method'    => $request->method(),
                ]);

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors'  => $e->errors(),
                    ], 422);
                }

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
                }

                // Tangtangon ang stack trace sa production
                return response()->json([
                    'message' => 'An error occurred. Please try again.',
                ], 500);
            }
        });
    })->create();
