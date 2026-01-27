<?php

namespace Tests\Unit;

use App\Http\Middleware\AddDeprecationWarnings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Unit tests for AddDeprecationWarnings middleware
 * 
 * Requirements: 2.5, 7.2
 */
class AddDeprecationWarningsTest extends TestCase
{
    protected AddDeprecationWarnings $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AddDeprecationWarnings();
    }

    /**
     * Test that Warning header is added when @deprecated field is present
     * 
     * @test
     */
    public function test_adds_warning_header_when_deprecated_fields_present()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => 'Test',
                    '@deprecated' => [
                        'old_field' => 'value',
                    ],
                ],
            ]);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertTrue($response->headers->has('Warning'));
        $this->assertEquals(
            '299 - "Deprecated fields (@deprecated) will be removed in the next release"',
            $response->headers->get('Warning')
        );
    }

    /**
     * Test that Warning header is NOT added when @deprecated field is absent
     * 
     * @test
     */
    public function test_no_warning_header_when_deprecated_fields_absent()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => 'Test',
                ],
            ]);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertFalse($response->headers->has('Warning'));
    }

    /**
     * Test that Warning header is added when @deprecated is in nested data
     * 
     * @test
     */
    public function test_adds_warning_header_for_nested_deprecated_fields()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            return new JsonResponse([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Test 1',
                        '@deprecated' => [
                            'old_field' => 'value1',
                        ],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Test 2',
                        '@deprecated' => [
                            'old_field' => 'value2',
                        ],
                    ],
                ],
            ]);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertTrue($response->headers->has('Warning'));
        $this->assertEquals(
            '299 - "Deprecated fields (@deprecated) will be removed in the next release"',
            $response->headers->get('Warning')
        );
    }

    /**
     * Test that middleware handles non-JSON responses gracefully
     * 
     * @test
     */
    public function test_handles_non_json_responses_gracefully()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            return response('Plain text response', 200, ['Content-Type' => 'text/plain']);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertFalse($response->headers->has('Warning'));
        $this->assertEquals('Plain text response', $response->getContent());
    }

    /**
     * Test that middleware handles empty responses gracefully
     * 
     * @test
     */
    public function test_handles_empty_responses_gracefully()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            return new JsonResponse(null);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertFalse($response->headers->has('Warning'));
    }

    /**
     * Test that middleware handles invalid JSON gracefully
     * 
     * @test
     */
    public function test_handles_invalid_json_gracefully()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            $response = response('', 200);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent('invalid json {');
            return $response;
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertFalse($response->headers->has('Warning'));
    }

    /**
     * Test that middleware detects @deprecated in deeply nested structures
     * 
     * @test
     */
    public function test_detects_deprecated_in_deeply_nested_structures()
    {
        $request = Request::create('/api/test', 'GET');
        
        $next = function ($request) {
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'level1' => [
                        'level2' => [
                            'level3' => [
                                '@deprecated' => [
                                    'deep_field' => 'value',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertTrue($response->headers->has('Warning'));
    }
}
