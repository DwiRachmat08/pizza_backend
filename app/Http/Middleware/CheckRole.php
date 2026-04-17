<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Cek apakah user sudah login DAN punya role yang sesuai
        if ($request->user() && $request->user()->role->slug === $role) {
            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak memiliki akses ke halaman ini.'], 403);
    }
}
