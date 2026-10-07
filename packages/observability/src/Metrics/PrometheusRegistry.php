<?php
namespace Ferrox\Observability\Metrics;

/**
 * Native Prometheus Metrics Registry for Ferrox.
 * Collects counters and gauges across the framework for standard `/metrics` scraping.
 */
class PrometheusRegistry
{
    private static array $counters = [];
    private static array $gauges = [];

    /**
     * Increments a counter metric.
     */
    public static function increment(string $name, array $labels = [], int $value = 1): void
    {
        $key = self::formatKey($name, $labels);
        if (!isset(self::$counters[$key])) {
            self::$counters[$key] = [
                'name' => $name,
                'labels' => $labels,
                'value' => 0
            ];
        }
        self::$counters[$key]['value'] += $value;
    }

    /**
     * Sets an absolute value for a gauge metric (e.g. current stock, active users).
     */
    public static function setGauge(string $name, int|float $value, array $labels = []): void
    {
        $key = self::formatKey($name, $labels);
        self::$gauges[$key] = [
            'name' => $name,
            'labels' => $labels,
            'value' => $value
        ];
    }

    /**
     * Exports all registered metrics in the standard Prometheus text format.
     */
    public static function export(): string
    {
        $output = "";

        foreach (self::$counters as $metric) {
            $output .= "# TYPE {$metric['name']} counter\n";
            $labelStr = self::buildLabelString($metric['labels']);
            $output .= "{$metric['name']}{$labelStr} {$metric['value']}\n";
        }

        foreach (self::$gauges as $metric) {
            $output .= "# TYPE {$metric['name']} gauge\n";
            $labelStr = self::buildLabelString($metric['labels']);
            $output .= "{$metric['name']}{$labelStr} {$metric['value']}\n";
        }

        return $output;
    }

    private static function formatKey(string $name, array $labels): string
    {
        ksort($labels);
        return $name . ':' . json_encode($labels);
    }

    private static function buildLabelString(array $labels): string
    {
        if (empty($labels)) return "";
        $parts = [];
        foreach ($labels as $k => $v) {
            $parts[] = "{$k}=\"{$v}\"";
        }
        return "{" . implode(",", $parts) . "}";
    }
}
