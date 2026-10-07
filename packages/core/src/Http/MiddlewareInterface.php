<?php
namespace Ferrox\Core\Http;

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response;
}
