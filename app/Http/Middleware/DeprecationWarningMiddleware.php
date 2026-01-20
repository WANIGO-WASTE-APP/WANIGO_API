<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DeprecationWarningMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $newEndpoint = null, string $sunsetDate = null): Response
    {
        // Log usage of deprecated endpoint
        Log::warning('Deprecated endpoint accessed', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Process the request
        $response = $next($request);
        
        // Add deprecation warning to response
        if ($response->headers->get('content-type') === 'application/json') {
            $content = json_decode($response->getContent(), true);
            
            // Add deprecation fields
            $content['deprecated'] = true;
            $content['deprecation_message'] = 'This endpoint is deprecated and will be removed in a future version.';
            
            if ($newEndpoint) {
                $content['new_endpoint'] = $newEndpoint;
            }
            
            if ($sunsetDate) {
                $content['sunset_date'] = $sunsetDate;
            }
            
            $response->setContent(json_encode($content));
        }
        
        // Add deprecation headers
        $response->headers->set('X-API-Deprecated', 'true');
        
        if ($sunsetDate) {
            $response->headers->set('Sunset', $sunsetDate);
        }
        
        if ($newEndpoint) {
            $response->headers->set('X-API-New-Endpoint', $newEndpoint);
        }
        
        return $response;
    }
}
