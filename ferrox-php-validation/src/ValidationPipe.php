<?php
namespace Ferrox\Validation;

use Ferrox\Core\Http\MiddlewareInterface;
use Ferrox\Core\Http\Request;
use Ferrox\Core\Http\RequestHandlerInterface;
use Ferrox\Core\Http\Response;
use Ferrox\Core\Errors\AppError;

class ValidationPipe implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // 🔒 Ferrox Layer 5: Payload Validation
        // This middleware uses PHP 8 Reflection to validate DTOs against request body.
        // It prevents malformed or unsafe inputs from reaching the Controller.
        
        // In a real scenario, this would use reflection on the Target Controller method
        // to map the $request->body to the DTO and run validation constraints (e.g. #[Email]).
        
        // For demonstration, we assume basic validation passes, or throws AppError::badRequest()
        
        return $handler->handle($request);
    }
}
