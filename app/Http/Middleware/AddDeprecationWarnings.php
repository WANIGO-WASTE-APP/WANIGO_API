<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddDeprecationWarnings
{
    /**
     * Handle an incoming request.
     *
     * Add deprecation warnings to responses containing @deprecated fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        if ($this->hasDeprecatedFields($response)) {
            $response->header(
                'Warning',
                '299 - "Deprecated fields (@deprecated) will be removed in the next release"'
            );
        }
        
        return $response;
    }
    
    /**
     * Check if response contains deprecated fields.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return bool
     */
    protected function hasDeprecatedFields(Response $response): bool
    {
        // Only check JSON responses
        $contentType = $response->headers->get('content-type');
        if (!$contentType || !str_contains($contentType, 'application/json')) {
            return false;
        }
        
        $content = $response->getContent();
        if (empty($content)) {
            return false;
        }
        
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        // Check if @deprecated key exists anywhere in the response
        return $this->containsDeprecatedKey($data);
    }
    
    /**
     * Recursively check if data contains @deprecated key.
     *
     * @param  mixed  $data
     * @return bool
     */
    protected function containsDeprecatedKey($data): bool
    {
        if (!is_array($data)) {
            return false;
        }
        
        // Check if current level has @deprecated key
        if (array_key_exists('@deprecated', $data)) {
            return true;
        }
        
        // Recursively check nested arrays
        foreach ($data as $value) {
            if (is_array($value) && $this->containsDeprecatedKey($value)) {
                return true;
            }
        }
        
        return false;
    }
}
