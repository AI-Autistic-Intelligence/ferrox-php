<?php

namespace Ferrox\Utils\Cloud;

interface CloudStorageProviderInterface {
    public function uploadFile(string $bucket, string $path, mixed $stream, string $contentType): string;
    public function downloadFile(string $bucket, string $path): mixed;
    public function generatePresignedUrl(string $bucket, string $path, int $expirationSeconds = 3600): string;
}

interface TerraformGeneratorInterface {
    public function generateVpc(string $name, string $cidrBlock): string;
    public function generateDatabase(string $name, string $engine, string $version, string $instanceClass): string;
}
