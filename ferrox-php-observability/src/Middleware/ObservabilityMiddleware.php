<?php
namespace Ferrox\Observability\Middleware;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Ferrox\Observability\Metrics\PrometheusRegistry;

/**
 * Global Observability Interceptor.
 * Ensures that System-Level metrics (Traffic, Errors, Latency, Memory)
 * are ALWAYS extracted regardless of user configuration, guaranteeing
 * SRE visibility at all times.
 */
class ObservabilityMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $response = $handler->handle($request);
            $this->recordMetrics($request, $response->getStatusCode(), $startTime, $startMemory);
            return $response;
        } catch (\Throwable $e) {
            // Uncaught exceptions map to 500 Internal Server Error
            $this->recordMetrics($request, 500, $startTime, $startMemory);
            throw $e;
        }
    }

    private function recordMetrics(Request $request, int $statusCode, float $startTime, int $startMemory): void
    {
        $latencyMs = (microtime(true) - $startTime) * 1000;
        $memoryUsed = memory_get_usage() - $startMemory;

        $labels = [
            'method' => $request->method ?? 'GET',
            'path' => $request->uri ?? '/',
            'status' => (string)$statusCode
        ];

        // 1. Always track total traffic
        PrometheusRegistry::increment('ferrox_http_requests_total', $labels);
        
        // 2. Always track error rates (4xx and 5xx)
        if ($statusCode >= 400) {
            PrometheusRegistry::increment('ferrox_http_errors_total', $labels);
        }

        // 3. Always track consumptions and latency
        PrometheusRegistry::setGauge('ferrox_http_request_duration_ms', $latencyMs, $labels);
        PrometheusRegistry::setGauge('ferrox_memory_usage_bytes', memory_get_usage());
        PrometheusRegistry::setGauge('ferrox_request_memory_delta_bytes', $memoryUsed, $labels);
    }
}
