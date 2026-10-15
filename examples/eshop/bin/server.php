<?php
declare(strict_types=1);

/**
 * 🛒 Ferrox-PHP Enterprise E-Shop Microservice Runner
 * 
 * Resident-Memory Multi-Engine Runner compatible with:
 * - FrankenPHP (worker mode)
 * - Swoole (HTTP Server coroutines)
 * - Workerman
 * - Native Built-in CLI loop (for dev & test without extensions)
 */

// Fallback PSR-4 autoloader for standalone execution without composer dump-autoload
spl_autoload_register(function (string $class) {
    $prefix = 'Ferrox\\Examples\\Eshop\\';
    $baseDir = dirname(__DIR__) . '/src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) === 0) {
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Check packages in monorepo
    $packagesDir = dirname(__DIR__, 2) . '/packages/';
    $packageMap = [
        'Ferrox\\Core\\' => $packagesDir . 'core/src/',
        'Ferrox\\Cqrs\\' => $packagesDir . 'cqrs/src/',
        'Ferrox\\Database\\Core\\' => $packagesDir . 'database-core/src/',
        'Ferrox\\Events\\' => $packagesDir . 'events/src/',
        'Ferrox\\Validation\\' => $packagesDir . 'validation/src/',
        'Ferrox\\Security\\' => $packagesDir . 'security/src/',
        'Ferrox\\Observability\\' => $packagesDir . 'observability/src/',
        'Ferrox\\Utils\\' => $packagesDir . 'utils/src/',
        'Ferrox\\Mailer\\' => $packagesDir . 'mailer/src/',
        'Ferrox\\Data\\' => $packagesDir . 'data/src/',
    ];

    foreach ($packageMap as $ns => $dir) {
        $nsLen = strlen($ns);
        if (strncmp($ns, $class, $nsLen) === 0) {
            $rel = substr($class, $nsLen);
            $target = $dir . str_replace('\\', '/', $rel) . '.php';
            if (file_exists($target)) {
                require_once $target;
                return;
            }
        }
    }
});

$composerAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use Ferrox\Core\Container\Container;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;
use Ferrox\Cqrs\CommandBus;
use Ferrox\Events\EventDispatcher;
use Ferrox\Examples\Eshop\Application\Commands\CancelOrderCommand;
use Ferrox\Examples\Eshop\Application\Commands\SubmitOrderCommand;
use Ferrox\Examples\Eshop\Application\Commands\SyncPosBatchCommand;
use Ferrox\Examples\Eshop\Application\Handlers\CancelOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\GetOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\GetWarehouseStockHandler;
use Ferrox\Examples\Eshop\Application\Handlers\SubmitOrderHandler;
use Ferrox\Examples\Eshop\Application\Handlers\SyncPosBatchHandler;
use Ferrox\Examples\Eshop\Http\Controllers\HealthController;
use Ferrox\Examples\Eshop\Http\Controllers\MetricsController;
use Ferrox\Examples\Eshop\Http\Controllers\OrderController;
use Ferrox\Examples\Eshop\Http\Controllers\WarehouseController;
use Ferrox\Examples\Eshop\Infrastructure\Metrics\EshopMetrics;
use Ferrox\Examples\Eshop\Infrastructure\Outbox\RedisOutboxPublisher;
use Ferrox\Examples\Eshop\Infrastructure\Payment\SimulatedPaymentGateway;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoOrderRepository;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PdoWarehouseRepository;
use Ferrox\Examples\Eshop\Infrastructure\Persistence\PostgreSqlUnitOfWork;

// 1. Initialize Dependency Injection Container
$container = new Container();

// 2. Setup Persistence & Infrastructure
$pdo = null;
$dsn = getenv('DATABASE_URL') ?: null;
if ($dsn) {
    try {
        $pdo = new PDO($dsn, getenv('DB_USER') ?: 'ferrox', getenv('DB_PASSWORD') ?: 'ferrox_secret', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (\Throwable $e) {
        error_log("[POSTGRES WARNING] Failed connecting to Postgres: " . $e->getMessage() . " - Using high-speed resident memory store.");
    }
}

$uow = new PostgreSqlUnitOfWork($pdo);
$warehouseRepo = new PdoWarehouseRepository($uow, $pdo);
$orderRepo = new PdoOrderRepository($uow, $pdo);
$paymentGateway = new SimulatedPaymentGateway();
$events = new EventDispatcher($container);
$outbox = new RedisOutboxPublisher();

// 3. Setup CQRS Handlers
$submitOrderHandler = new SubmitOrderHandler($warehouseRepo, $orderRepo, $paymentGateway, $events, $outbox);
$cancelOrderHandler = new CancelOrderHandler($orderRepo, $warehouseRepo, $paymentGateway, $events, $outbox);
$getOrderHandler = new GetOrderHandler($orderRepo);
$getWarehouseStockHandler = new GetWarehouseStockHandler($warehouseRepo);
$syncPosBatchHandler = new SyncPosBatchHandler($submitOrderHandler);

$commandBus = new CommandBus($container);

// 4. Setup Controllers
$orderController = new OrderController($submitOrderHandler, $cancelOrderHandler, $getOrderHandler, $syncPosBatchHandler);
$warehouseController = new WarehouseController($getWarehouseStockHandler);
$metricsController = new MetricsController();
$healthController = new HealthController();

// 5. Enterprise Router function
$routeRequest = function (string $method, string $uri, array $headers, ?array $body = null) use (
    $orderController,
    $warehouseController,
    $metricsController,
    $healthController
): Response {
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $req = new Request($method, $path, $headers, $body ?? []);

    // Check Sentinel Threat Engine WAF heuristics
    if (class_exists(\Ferrox\Security\Sentinel\SentinelThreatEngineMiddleware::class)) {
        $waf = new \Ferrox\Security\Sentinel\SentinelThreatEngineMiddleware();
        // Light WAF pass
        $payloadStr = json_encode($body) . ' ' . $path;
        if (preg_match('/(\b(UNION|SELECT|DROP|ALTER)\b.*?\b(FROM|INTO|TABLE)\b)/i', $payloadStr)) {
            return Response::json([
                'type' => 'https://ferrox.dev/errors/waf-block',
                'title' => 'Forbidden Security Violation',
                'status' => 403,
                'detail' => 'Sentinel Threat Engine identified potential SQLi pattern.'
            ], 403);
        }
    }

    // Health probes
    if ($method === 'GET' && ($path === '/health' || $path === '/health/live')) {
        return $healthController->live($req);
    }
    if ($method === 'GET' && $path === '/health/ready') {
        return $healthController->ready($req);
    }

    // Metrics
    if ($method === 'GET' && $path === '/metrics') {
        return $metricsController->export($req);
    }

    // Warehouses
    if ($method === 'GET' && $path === '/api/v1/warehouses') {
        return $warehouseController->listWarehouses($req);
    }
    if ($method === 'GET' && preg_match('#^/api/v1/warehouses/([a-zA-Z0-9_-]+)$#', $path, $matches)) {
        return $warehouseController->getWarehouse($req, $matches[1]);
    }

    // Orders
    if ($method === 'POST' && $path === '/api/v1/orders') {
        return $orderController->submitOrder($req);
    }
    if ($method === 'GET' && preg_match('#^/api/v1/orders/([a-zA-Z0-9_-]+)$#', $path, $matches)) {
        return $orderController->getOrder($req, $matches[1]);
    }
    if ($method === 'POST' && preg_match('#^/api/v1/orders/([a-zA-Z0-9_-]+)/cancel$#', $path, $matches)) {
        return $orderController->cancelOrder($req, $matches[1]);
    }
    if ($method === 'POST' && $path === '/api/v1/pos/batch') {
        return $orderController->syncPosBatch($req);
    }

    return Response::json([
        'type' => 'https://ferrox.dev/errors/not-found',
        'title' => 'Route Not Found',
        'status' => 404,
        'detail' => "Path '{$path}' with method '{$method}' does not exist on this microservice."
    ], 404);
};

// 6. Runtime detection: FrankenPHP / Swoole / CLI Simulation
$port = (int)(getenv('PORT') ?: 8080);

if (isset($_SERVER['FRANKENPHP_WORKER'])) {
    // FrankenPHP Worker mode
    echo "⚡ [FrankenPHP] Resident worker booting on port {$port}...\n";
    $handler = static function () use ($routeRequest) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $headers = getallheaders() ?: [];
        $rawBody = file_get_contents('php://input');
        $body = !empty($rawBody) ? json_decode($rawBody, true) : [];

        $response = $routeRequest($method, $uri, $headers, $body);

        http_response_code($response->statusCode);
        foreach ($response->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        if (is_array($response->body)) {
            echo json_encode($response->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } else {
            echo $response->body;
        }
    };

    while (frankenphp_handle_request($handler)) {
        // Collect garbage periodically in idle state
        gc_collect_cycles();
    }
    exit(0);
}

if (extension_loaded('swoole') && php_sapi_name() === 'cli' && !isset($_SERVER['CLI_SIMULATION'])) {
    // Swoole HTTP Server
    $server = new \Swoole\Http\Server("0.0.0.0", $port);
    $server->set([
        'worker_num' => (int)(getenv('SWOOLE_WORKERS') ?: 4),
        'max_coroutine' => 100000,
        'enable_coroutine' => true,
    ]);

    $server->on('start', function () use ($port) {
        echo "🚀 [Swoole] Ferrox E-Shop resident HTTP server listening on http://0.0.0.0:{$port}\n";
    });

    $server->on('request', function ($swRequest, $swResponse) use ($routeRequest) {
        $method = $swRequest->server['request_method'] ?? 'GET';
        $uri = $swRequest->server['request_uri'] ?? '/';
        $headers = $swRequest->header ?? [];
        $rawBody = $swRequest->rawContent();
        $body = !empty($rawBody) ? json_decode($rawBody, true) : [];

        $response = $routeRequest($method, $uri, $headers, $body);

        $swResponse->status($response->statusCode);
        foreach ($response->headers as $k => $v) {
            $swResponse->header($k, $v);
        }
        if (is_array($response->body)) {
            $swResponse->end(json_encode($response->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $swResponse->end($response->body);
        }
    });

    $server->start();
    exit(0);
}

// Default CLI Demonstration & Smoke Test Runner
echo "=================================================================\n";
echo "🛒 Ferrox Enterprise E-Shop Microservice (Resident Mode)\n";
echo "=================================================================\n";
echo "Detected Runtime: PHP CLI resident memory mode.\n";
echo "Running enterprise self-check and Saga workflow demonstration...\n\n";

// 1. Health Probe Test
$resHealth = $routeRequest('GET', '/health/ready', []);
echo "1. Health Probe Status: " . $resHealth->statusCode . "\n";
echo "   Body: " . json_encode($resHealth->body) . "\n\n";

// 2. Query Initial Warehouses
$resWh = $routeRequest('GET', '/api/v1/warehouses', []);
echo "2. Initial Warehouses: " . count($resWh->body['data']) . " registered fulfillment hubs\n\n";

// 3. Submit Order that triggers multi-warehouse fallback (Rome has 0, Milan has 8)
echo "3. Submitting Order (1 item PROD-1) -> Expect Rome (0) to Milan (8) Fallback...\n";
$resOrder1 = $routeRequest('POST', '/api/v1/orders', ['Content-Type' => 'application/json'], [
    'productId' => 'PROD-1',
    'quantity' => 1,
    'customerId' => 'CUST-1001',
    'source' => 'ONLINE',
    'unitPriceCents' => 2999,
    'currency' => 'EUR'
]);
echo "   Response Code: {$resOrder1->statusCode}\n";
echo "   Assigned Warehouse: " . ($resOrder1->body['data']['warehouseId'] ?? 'none') . "\n";
echo "   Status: " . ($resOrder1->body['data']['status'] ?? 'unknown') . "\n\n";

// 4. Submit Order that triggers Out Of Stock Saga Compensation
echo "4. Submitting Order (50 items PROD-1) -> Expect Global Out-Of-Stock Compensation & Refund...\n";
$resOrder2 = $routeRequest('POST', '/api/v1/orders', ['Content-Type' => 'application/json'], [
    'productId' => 'PROD-1',
    'quantity' => 50,
    'customerId' => 'CUST-1002',
    'source' => 'POS',
    'unitPriceCents' => 2999,
    'currency' => 'EUR'
]);
echo "   Response Code: {$resOrder2->statusCode}\n";
echo "   Problem Type: " . ($resOrder2->body['type'] ?? 'none') . "\n";
echo "   Detail: " . ($resOrder2->body['detail'] ?? 'none') . "\n\n";

// 5. Offline POS Batch Sync
echo "5. Syncing Offline POS Batch (3 orders)...\n";
$resBatch = $routeRequest('POST', '/api/v1/pos/batch', ['Content-Type' => 'application/json'], [
    'posTerminalId' => 'POS-TERMINAL-ROME-01',
    'orders' => [
        ['productId' => 'PROD-2', 'quantity' => 2, 'customerId' => 'POS-WALKIN-1', 'unitPriceCents' => 999],
        ['productId' => 'PROD-2', 'quantity' => 1, 'customerId' => 'POS-WALKIN-2', 'unitPriceCents' => 999],
        ['productId' => 'PROD-1', 'quantity' => 2, 'customerId' => 'POS-WALKIN-3', 'unitPriceCents' => 2999],
    ]
]);
echo "   Response Code: {$resBatch->statusCode}\n";
echo "   Successful in Batch: " . ($resBatch->body['data']['successful'] ?? 0) . "\n\n";

// 6. Prometheus Metrics Exporter
$resMetrics = $routeRequest('GET', '/metrics', []);
echo "6. Prometheus /metrics Sample:\n";
echo "--------------------------------------------------------\n";
$metricLines = explode("\n", (string)$resMetrics->body);
foreach (array_slice(array_filter($metricLines), 0, 10) as $line) {
    echo "   {$line}\n";
}
echo "   [... " . count($metricLines) . " total metric lines exported]\n";
echo "--------------------------------------------------------\n";
echo "✅ Ferrox E-Shop Enterprise Microservice Ready.\n";
