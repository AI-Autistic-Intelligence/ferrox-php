<?php
declare(strict_types=1);

namespace Ferrox\Examples\Eshop\Infrastructure\Metrics;

use Ferrox\Observability\Metrics\PrometheusRegistry;

final class EshopMetrics
{
    public static function recordOrderPlaced(string $source, string $status): void
    {
        PrometheusRegistry::increment('ferrox_orders_total', [
            'source' => $source,
            'status' => $status
        ]);
    }

    public static function recordStockDepleted(string $productId): void
    {
        PrometheusRegistry::increment('ferrox_alerts_total', [
            'type' => 'out_of_stock',
            'product' => $productId
        ]);
    }

    public static function recordSagaCompensation(string $stepName): void
    {
        PrometheusRegistry::increment('ferrox_saga_compensations_total', [
            'step' => $stepName
        ]);
    }

    public static function recordAnomaly(string $type): void
    {
        PrometheusRegistry::increment('ferrox_critical_anomalies_total', [
            'type' => $type
        ]);
    }

    public static function export(): string
    {
        return PrometheusRegistry::export();
    }
}
