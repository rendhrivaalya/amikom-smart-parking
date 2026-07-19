<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Throwable $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'type' => 'Validation Error',
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 422);
            }

            return response()->json([
                'success' => false,
                'type' => class_basename($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500);

        });

    })

    ->create();