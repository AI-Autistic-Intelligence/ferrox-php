<?php
namespace Ferrox\Core\Http;

interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}
