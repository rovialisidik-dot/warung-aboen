<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
            //
        });

        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->is('pos/*') || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi login telah kedaluwarsa. Silakan muat ulang (refresh) halaman.',
                    'csrf_error' => true
                ], 419);
            }
            return redirect()->route('login')->with('error', 'Sesi Anda telah kedaluwarsa demi keamanan. Silakan masuk kembali.');
        });
    }
}
