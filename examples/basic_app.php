<?php
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

// --- 1. Define a DTO ---
class RegisterUserDto {
    public string $email;
    public string $password;
}

// --- 2. Define a CQRS Command ---
class RegisterUserCommand implements CommandInterface {
    public function __construct(public string $email, public string $password) {}
}

// --- 3. Define the Controller ---
#[Controller('/api/v1/users')]
#[UseGuard(MandatoryComplianceGuard::class)] // Optional at controller level if global
class UserController {

    public function __construct(
        private CommandBus $commandBus,
        private Singleflight $singleflight
    ) {}

    #[Post('/register')]
    #[RequireRole('ADMIN')]
    public function register(#[ValidatedDto] RegisterUserDto $dto): Response {
        // Validation has already passed (Layer 5)
        // RBAC has already passed (Layer 4)
        // Sentinel threat check passed (Layer 2)
        
        $userId = $this->commandBus->dispatch(new RegisterUserCommand($dto->email, $dto->password));
        return Response::created(['id' => $userId]);
    }
    
    #[Get('/stats')]
    public function getStats(): Response {
        // 🔒 Cache Stampede Prevention (Singleflight)
        $data = $this->singleflight->work('global_stats', function() {
            // Simulate heavy DB query
            sleep(1); 
            return ['active_users' => 1000, 'cpu_load' => 45];
        });
        
        return Response::json($data);
    }
}

// --- 4. Bootstrap Ferrox-PHP ---
try {
    $app = FerroxApp::builder()
        ->withEngine('Swoole\Engine')
        ->addPipeline([
            MandatoryComplianceMiddleware::class,   // L1
            SentinelThreatEngineMiddleware::class,  // L2
            PasetoAuthGuard::class,                 // L3
            ValidationPipe::class                   // L5
        ])
        ->registerControllers([
            UserController::class
        ])
        ->build();

    $app->start();
    
    echo "Successfully built the Ferrox PHP environment!\n";
    
} catch (\Exception $e) {
    echo "Startup Error: " . $e->getMessage() . "\n";
}
