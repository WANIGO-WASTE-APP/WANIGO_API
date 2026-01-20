<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API version from header, default to 2.0
        $version = $request->header('X-API-Version', '2.0');
        
        // Store version in request for use in controllers
        $request->attributes->set('api_version', $version);
        
        // Process the request
        $response = $next($request);
        
        // Transform response for version 1.0 if needed
        if ($version === '1.0' && $response->headers->get('content-type') === 'application/json') {
            $content = json_decode($response->getContent(), true);
            
            // Transform v2.0 response structure to v1.0 format
            if (isset($content['success']) && isset($content['data'])) {
                // V1.0 format: direct data without wrapper
                $transformedContent = $content['data'];
                
                // If there's an error, keep the error structure
                if ($content['success'] === false) {
                    $transformedContent = [
                        'error' => $content['message'] ?? 'An error occurred',
                        'errors' => $content['errors'] ?? null
                    ];
                }
                
                $response->setContent(json_encode($transformedContent));
            }
        }
        
        // Add API version header to response
        $response->headers->set('X-API-Version', $version);
        
        return $response;
    }
}
