<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log detailed error information
            Log::error('500 Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // Custom handler for 500 errors in production
        $this->renderable(function (Throwable $e) {
            if (app()->environment('production')) {
                if ($e instanceof HttpException) {
                    return response()->view('errors.500', [], 500);
                }
                
                // For AJAX requests
                if (request()->expectsJson()) {
                    return response()->json([
                        'message' => 'Server Error',
                        'error_id' => uniqid('error_'),
                    ], 500);
                }
                
                return response()->view('errors.500', [], 500);
            }
        });
    }
}