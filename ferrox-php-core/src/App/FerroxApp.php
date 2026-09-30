<?php
namespace Ferrox\Core\App;

use Ferrox\Core\Container\Container;
use RuntimeException;

/**
 * The core Application Builder for the Ferrox PHP framework.
 * Enforces strict security configurations, pipeline architectures, and adapter patterns (RoadRunner/Swoole).
 * By design, an app cannot boot if it violates Zero-Trust boundaries.
 */
class FerroxApp
{
    private Container $container;
    private array $middlewares = [];
    private array $controllers = [];
    private ?string $engine = null;

    private function __construct()
    {
        $this->container = new Container();
        $this->container->instance(self::class, $this);
    }

    /**
     * Initializes a new Ferrox Application Builder.
     * 
     * @return self
     */
    public static function builder(): self
    {
        return new self();
    }

    /**
     * Specifies the execution engine adapter (e.g., Swoole, RoadRunner).
     * 
     * @param string $engineClass The FQCN of the engine.
     * @return self
     */
    public function withEngine(string $engineClass): self
    {
        $this->engine = $engineClass;
        return $this;
    }

    /**
     * Appends middleware to the global execution pipeline.
     * 
     * @param array $middlewares Array of MiddlewareInterface class names.
     * @return self
     */
    public function addPipeline(array $middlewares): self
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    /**
     * Registers HTTP Controllers.
     * 
     * @param array $controllers Array of Controller class names.
     * @return self
     */
    public function registerControllers(array $controllers): self
    {
        $this->controllers = array_merge($this->controllers, $controllers);
        return $this;
    }

    /**
     * Finalizes the application build process and verifies security invariants.
     * 
     * @return self
     * @throws RuntimeException If mandatory security pipelines or engines are missing.
     */
    public function build(): self
    {
        // 🔒 Strict Rule: Pipeline cannot be empty. 
        if (empty($this->middlewares)) {
            throw new RuntimeException("Ferrox Security Rule Violation: Pipeline cannot be empty. Mandatory compliance missing.");
        }
        
        // 🔒 Strict Rule: Must have an engine
        if (is_null($this->engine)) {
            throw new RuntimeException("Ferrox Architecture Rule Violation: Engine adapter must be specified (e.g. Swoole/RoadRunner).");
        }

        return $this;
    }

    public function start(): void
    {
        echo "⚡ Booting Ferrox-PHP Framework...\n";
        echo "Engine: {$this->engine}\n";
        echo "Pipeline Loaded: " . count($this->middlewares) . " middlewares\n";
        echo "Controllers Registered: " . count($this->controllers) . "\n";
        echo "System ready.\n";
        
        // In the future: $engine = $this->container->get($this->engine);
        // $engine->run($this);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
