<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Http\Controllers;

use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;
use Ferrox\Observability\Metrics\PrometheusRegistry;

final class MetricsController
{
    public function export(Request $request): Response
    {
        return new Response(
            statusCode: 200,
            headers: [
                'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
            ],
            body: PrometheusRegistry::export()
        );
    }
}
