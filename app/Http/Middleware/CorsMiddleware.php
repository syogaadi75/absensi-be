<?php

/**
 * Location: /app/Http/Middleware
 */

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Log::info('CORS Middleware: Incoming Request', ['request' => $request->all()]);

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Expose-Headers' => 'Authorization',
            'X-Frame-Options' => 'Allow-From *'
        ];

        if ($request->isMethod('OPTIONS')) {
            \Log::info('CORS Middleware: OPTIONS Request', ['headers' => $headers]);
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        $response->headers->add($headers);

        \Log::info('CORS Middleware: Outgoing Response', ['response' => $response]);

        return $response;
    }

}
