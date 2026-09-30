<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Ferrox\Security\Sentinel\SentinelThreatEngineMiddleware;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;

class SentinelTest extends TestCase
{
    public function test_blocks_high_entropy_payloads()
    {
        $middleware = new SentinelThreatEngineMiddleware();
        
        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn(base64_encode(random_bytes(100))); // High entropy shellcode sim

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle'); // Handler should never be reached

        $this->expectException(\Ferrox\Core\Errors\AppError::class);
        $this->expectExceptionMessage('Threat Detected');

        $middleware->process($request, $handler);
    }

    public function test_allows_normal_entropy_payloads()
    {
        $middleware = new SentinelThreatEngineMiddleware();
        
        $request = $this->createMock(Request::class);
        $request->method('getBody')->willReturn('{"name": "John Doe", "email": "john@example.com"}'); // Normal entropy

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle');

        $middleware->process($request, $handler);
    }
}
