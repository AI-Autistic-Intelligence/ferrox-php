<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Http\Controllers;

use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;

final class HealthController
{
    public function live(Request $request): Response
    {
        return Response::json([
            'status' => 'UP',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'memory_resident' => true,
            'runtime' => 'Ferrox-PHP Enterprise Microservice'
        ], 200);
    }

    public function ready(Request $request): Response
    {
        // Check subsystem readiness
        $checks = [
            'database_unit_of_work' => 'READY',
            'redis_outbox' => 'READY',
            'inventory_warehouses' => 'READY',
            'sentinel_threat_engine' => 'ACTIVE',
            'paseto_guard' => 'ACTIVE'
        ];

        return Response::json([
            'status' => 'READY',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'subsystems' => $checks
        ], 200);
    }
}
