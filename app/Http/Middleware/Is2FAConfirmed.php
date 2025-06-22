<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Is2FAConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || is_null($user->two_factor_confirmed_at)) {
            return response()->json([
                'message' => __('messages.2fa_authentication_not_confirmed'),
                'success'=> false,

            ], 403);
        }

        return $next($request);
    }
}
