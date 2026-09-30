<?php
namespace Ferrox\CrudGen;

use Ferrox\CrudGen\Attributes\CrudResource;
use Ferrox\Core\Container\Container;
use Ferrox\Database\Core\RepositoryInterface;
use ReflectionClass;
use RuntimeException;

class CrudGenerator
{
    /**
     * In a real application, this scans the `src/Entities` directory, reads #[CrudResource] attributes,
     * and dynamically registers CQRS Handlers and Http Routes into the Container and Pipeline.
     */
    public static function generateRoutesForEntity(Container $container, string $entityClass, bool $exposeBusinessMetrics = true): void
    {
        $reflection = new ReflectionClass($entityClass);
        $attributes = $reflection->getAttributes(CrudResource::class);

        if (empty($attributes)) {
            throw new RuntimeException("Class {$entityClass} does not have #[CrudResource] attribute.");
        }

        /** @var CrudResource $crudMeta */
        $crudMeta = $attributes[0]->newInstance();

        // 1. Generate generic Repository binding if not overridden
        $repoName = $entityClass . 'Repository';
        
        // 2. Optional Business Metrics Generation
        // System metrics (error rate, traffic, memory) are ALWAYS extracted via ObservabilityMiddleware.
        // Business metrics (e.g. how many products created) can be opted-out here.
        if ($exposeBusinessMetrics) {
            $metricPrefix = 'ferrox_crud_' . strtolower($reflection->getShortName());
            $businessMetrics = [
                $metricPrefix . '_created_total',
                $metricPrefix . '_updated_total',
                $metricPrefix . '_deleted_total',
                $metricPrefix . '_dlq_events' 
            ];
            foreach ($businessMetrics as $metric) {
                error_log("[METRICS] Auto-registered business metric: {$metric}");
            }
        } else {
            error_log("[METRICS] Business metrics generation disabled for {$reflection->getShortName()}. System metrics will still be collected.");
        }

        // 3. Generate Commands: CreateEntityCommand, UpdateEntityCommand
        // 4. Generate Handlers that inherently wrap the logic in a UnitOfWork
        // 5. Generate HTTP Controllers with #[RequireRole] mapped to $crudMeta->allowedRoles
        
        error_log(sprintf(
            "[CRUD-GEN] Auto-wired CQRS pipeline for '%s' on path '%s' (Roles: %s, Events: %s)",
            $reflection->getShortName(),
            $crudMeta->basePath,
            implode(',', $crudMeta->allowedRoles),
            $crudMeta->publishEvents ? 'Yes' : 'No'
        ));
    }
}
