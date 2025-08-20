<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     *
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        // Cek apakah user sudah login dan role sesuai
        if (!$user || $user->role !== $role){
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to access this resource.'
            ], 403);
        }
        return $next($request);
    }
}
