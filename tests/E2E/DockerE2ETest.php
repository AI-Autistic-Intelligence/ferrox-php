<?php
namespace Tests\E2E;

use PHPUnit\Framework\TestCase;

/**
 * Ferrox PHP - E2E Docker Integration Test
 * 
 * This test suite expects the application to be running inside the `ops/docker/docker-compose.yml` environment.
 * It sends real HTTP requests to the exposed API Gateway to verify that the Pipeline, Sentinel, and CQRS all work together.
 */
class DockerE2ETest extends TestCase
{
    private string $baseUrl = 'http://localhost:8080/api/v1';

    public function test_healthcheck_returns_200()
    {
        // Skip actual HTTP request if the Docker container is not running during local dev
        if (!getenv('E2E_DOCKER_RUNNING')) {
            $this->markTestSkipped('E2E_DOCKER_RUNNING env var not set. Skipping E2E.');
        }

        $ch = curl_init("{$this->baseUrl}/health");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('status', $response);
    }

    public function test_sentinel_blocks_malicious_payload_e2e()
    {
        if (!getenv('E2E_DOCKER_RUNNING')) {
            $this->markTestSkipped('E2E_DOCKER_RUNNING env var not set. Skipping E2E.');
        }

        $ch = curl_init("{$this->baseUrl}/orders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, base64_encode(random_bytes(256))); // Very high entropy
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(403, $httpCode, 'Sentinel should block high entropy payloads at Layer 2');
    }
}
