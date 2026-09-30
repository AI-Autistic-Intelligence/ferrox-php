<?php
namespace Ferrox\Validation;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Ferrox\Core\Errors\AppError;
use ReflectionClass;
use ReflectionProperty;

class ValidationPipe implements MiddlewareInterface
{
    /**
     * @var class-string
     */
    private string $dtoClass;

    public function __construct(string $dtoClass)
    {
        $this->dtoClass = $dtoClass;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if (!class_exists($this->dtoClass)) {
            throw AppError::internal("Validation Pipe misconfigured: DTO class not found.");
        }

        $reflection = new ReflectionClass($this->dtoClass);
        $attributes = $reflection->getAttributes(\Ferrox\Validation\Attributes\ValidatedDto::class);
        
        if (empty($attributes)) {
            throw AppError::internal("DTO must be annotated with #[ValidatedDto].");
        }

        $payload = $request->body ?? [];
        $errors = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $type = $prop->getType();
            
            // Required check
            if (!$type->allowsNull() && !isset($payload[$name])) {
                $errors[$name] = "Property '{$name}' is required.";
                continue;
            }
            
            // Type enforcement
            if (isset($payload[$name])) {
                $val = $payload[$name];
                $expectedType = $type->getName();
                
                if ($expectedType === 'int' && !is_int($val)) $errors[$name] = "Expected int.";
                if ($expectedType === 'string' && !is_string($val)) $errors[$name] = "Expected string.";
                if ($expectedType === 'bool' && !is_bool($val)) $errors[$name] = "Expected bool.";
                if ($expectedType === 'array' && !is_array($val)) $errors[$name] = "Expected array.";
            }
        }

        if (!empty($errors)) {
            throw AppError::badRequest("Validation failed", $errors);
        }
        
        // Pass a strictly instantiated DTO to the request to eliminate loose arrays
        $request->setAttribute('dto', new $this->dtoClass(...$payload));

        return $handler->handle($request);
    }
}
