<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isOwner()) {
            return redirect()->route('dashboard')->with('error', 'Akses dibatasi. Halaman ini hanya untuk Pemilik Warung.');
        }

        return $next($request);
    }
}
