<?php
declare(strict_types=1);

/**
 * ⚡ Ferrox-PHP Light Quickstart with Dynamic Routing & RFC 7807 Error Handling
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ferrox\Core\App\FerroxApp;
use Ferrox\Core\Decorators\Controller;
use Ferrox\Core\Decorators\Get;
use Ferrox\Core\Decorators\Post;
use Ferrox\Core\Decorators\UseGuard;
use Ferrox\Core\Decorators\RequireRole;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\Response;
use Ferrox\Security\MandatoryComplianceMiddleware;
use Ferrox\Security\Sentinel\SentinelThreatEngineMiddleware;
use Ferrox\Security\Auth\PasetoAuthGuard;
use Ferrox\Validation\Attributes\ValidatedDto;
use Ferrox\Validation\ValidationPipe;
use Ferrox\Cqrs\CommandBus;
use Ferrox\Cqrs\CommandInterface;
use Ferrox\Data\Concurrency\Singleflight;

// ==============================================================================
// 1. DATA TRANSFER OBJECTS (DTOs)
// ==============================================================================

#[ValidatedDto(strict: true)]
class RegisterUserDto {
    public function __construct(
        public string $email = '',
        public string $password = '',
        public string $role = 'USER'
    ) {}
}

// ==============================================================================
// 2. CQRS COMMANDS & HANDLERS
// ==============================================================================

class RegisterUserCommand implements CommandInterface {
    public function __construct(
        public string $email,
        public string $password,
        public string $role
    ) {}
}

// ==============================================================================
// 3. RFC 7807 PROBLEM DETAILS HELPER
// ==============================================================================

final class ProblemDetails {
    public static function create(
        string $type,
        string $title,
        int $status,
        string $detail,
        string $instance,
        array $invalidParams = []
    ): Response {
        $body = [
            'type' => $type,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $instance,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
        ];

        if (!empty($invalidParams)) {
            $body['invalidParams'] = $invalidParams;
        }

        return new Response(
            statusCode: $status,
            headers: [
                'Content-Type' => 'application/problem+json',
                'X-Content-Type-Options' => 'nosniff'
            ],
            body: $body
        );
    }
}

// ==============================================================================
// 4. CONTROLLER WITH DYNAMIC ROUTES & RFC 7807 ERROR HANDLING
// ==============================================================================

#[Controller('/api/v1/users')]
class UserController {
    /** @var array<string, array> */
    private static array $userStore = [
        'usr_001' => ['id' => 'usr_001', 'email' => 'admin@ferrox.dev', 'role' => 'ADMIN'],
        'usr_002' => ['id' => 'usr_002', 'email' => 'dev@ferrox.dev', 'role' => 'USER']
    ];

    public function __construct(
        private ?CommandBus $commandBus = null,
        private ?Singleflight $singleflight = null
    ) {
        $this->singleflight ??= new Singleflight();
    }

    #[Post('/register')]
    #[RequireRole('ADMIN')]
    public function register(Request $request): Response {
        $body = $request->body ?? [];
        
        // Strict RFC 7807 Validation Check
        if (empty($body['email']) || !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            return ProblemDetails::create(
                type: 'https://ferrox.dev/errors/validation-failed',
                title: 'Validation Error',
                status: 422,
                detail: 'The provided user email address is invalid or missing.',
                instance: $request->uri ?? '/api/v1/users/register',
                invalidParams: [
                    ['name' => 'email', 'reason' => 'Must be a valid RFC 5322 email address']
                ]
            );
        }

        $userId = 'usr_' . bin2hex(random_bytes(4));
        self::$userStore[$userId] = [
            'id' => $userId,
            'email' => $body['email'],
            'role' => $body['role'] ?? 'USER'
        ];

        return Response::created([
            'id' => $userId,
            'email' => $body['email'],
            'role' => $body['role'] ?? 'USER'
        ]);
    }

    /**
     * Dynamic Route: GET /api/v1/users/{id}
     */
    #[Get('/{id}')]
    public function getUserById(Request $request, string $id): Response {
        if (!isset(self::$userStore[$id])) {
            return ProblemDetails::create(
                type: 'https://ferrox.dev/errors/resource-not-found',
                title: 'User Not Found',
                status: 404,
                detail: "User with identifier '{$id}' does not exist.",
                instance: $request->uri ?? "/api/v1/users/{$id}"
            );
        }

        return Response::json(self::$userStore[$id], 200);
    }
    
    #[Get('/stats')]
    public function getStats(): Response {
        // Cache Stampede Prevention (Singleflight)
        $data = $this->singleflight->work('global_stats', function() {
            return [
                'active_users' => count(self::$userStore),
                'cpu_load' => 24,
                'resident_memory_bytes' => memory_get_usage(true)
            ];
        });
        
        return Response::json($data);
    }
}

// ==============================================================================
// 5. BOOTSTRAP & DEMONSTRATION RUNNER
// ==============================================================================

echo "=================================================================\n";
echo "⚡ Ferrox-PHP Quickstart: Dynamic Routes & RFC 7807 Error Engine\n";
echo "=================================================================\n\n";

try {
    $app = FerroxApp::builder()
        ->withEngine('Swoole\Engine')
        ->addPipeline([
            MandatoryComplianceMiddleware::class,
            SentinelThreatEngineMiddleware::class,
            PasetoAuthGuard::class,
            ValidationPipe::class
        ])
        ->registerControllers([
            UserController::class
        ])
        ->build();

    $app->start();
    echo "\n";

    // Simulate Dynamic Routing & Controller execution
    $controller = new UserController();

    echo "1. Simulating Dynamic Route: GET /api/v1/users/usr_001\n";
    $req1 = new Request('GET', '/api/v1/users/usr_001', []);
    $res1 = $controller->getUserById($req1, 'usr_001');
    echo "   Status: {$res1->statusCode}\n";
    echo "   Payload: " . json_encode($res1->body) . "\n\n";

    echo "2. Simulating Dynamic Route 404 with RFC 7807 Problem Details: GET /api/v1/users/usr_999\n";
    $req2 = new Request('GET', '/api/v1/users/usr_999', []);
    $res2 = $controller->getUserById($req2, 'usr_999');
    echo "   Status: {$res2->statusCode}\n";
    echo "   Content-Type: " . ($res2->headers['Content-Type'] ?? 'none') . "\n";
    echo "   RFC 7807 Body: " . json_encode($res2->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

    echo "3. Simulating Registration with Invalid Email (RFC 7807 422):\n";
    $req3 = new Request('POST', '/api/v1/users/register', [], ['email' => 'not-an-email']);
    $res3 = $controller->register($req3);
    echo "   Status: {$res3->statusCode}\n";
    echo "   RFC 7807 Body: " . json_encode($res3->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

    echo "4. Singleflight Concurrency Stats:\n";
    $res4 = $controller->getStats();
    echo "   Status: {$res4->statusCode}\n";
    echo "   Stats: " . json_encode($res4->body) . "\n\n";

    echo "✅ Ferrox-PHP Basic App Quickstart executed successfully.\n";

} catch (\Exception $e) {
    echo "Startup Error: " . $e->getMessage() . "\n";
}
