<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifiedUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer || is_null($customer->is_verified)) {
            return response()->json([
                'message' => __('messages.need_to_be_verified_customer'),
                'success'=> false,

            ], 403);
        }




        return $next($request);
    }
}
