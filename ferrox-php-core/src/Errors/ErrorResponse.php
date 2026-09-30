<?php
namespace Ferrox\Core\Errors;

use Ferrox\Core\Http\Response;

class ErrorResponse
{
    public static function fromAppError(AppError $error): Response
    {
        $body = [
            'error' => [
                'code' => $error->errorCode ?? 'INTERNAL_SERVER_ERROR',
                'message' => $error->getMessage()
            ]
        ];

        if (!empty($error->details)) {
            $body['error']['details'] = $error->details;
        }

        return Response::json($body, $error->statusCode);
    }

    public static function internal(string $message = 'Internal Server Error'): Response
    {
        return Response::json([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $message
            ]
        ], 500);
    }
}
