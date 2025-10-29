<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCharacterEncoding
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->headers->has('Content-Type')) {
            $contentType = $response->headers->get('Content-Type');
            if (strpos($contentType, 'charset') === false) {
                $response->headers->set('Content-Type', $contentType . '; charset=utf-8');
            }
        } else {
            $response->headers->set('Content-Type', 'text/html; charset=utf-8');
        }

        return $response;
    }
}
