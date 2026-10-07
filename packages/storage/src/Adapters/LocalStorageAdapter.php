<?php
namespace Ferrox\Storage\Adapters;

use Ferrox\Storage\StorageInterface;
use RuntimeException;

class LocalStorageAdapter implements StorageInterface
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }
    }

    public function put(string $path, string $contents, array $options = []): string
    {
        $fullPath = $this->buildPath($path);
        
        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        if (file_put_contents($fullPath, $contents) === false) {
            throw new RuntimeException("Failed to write to local storage: {$fullPath}");
        }

        return $path;
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->buildPath($path);
        if (!file_exists($fullPath)) return null;
        return file_get_contents($fullPath);
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->buildPath($path);
        if (!file_exists($fullPath)) return false;
        return unlink($fullPath);
    }

    public function getPresignedUrl(string $path, int $expiresInSeconds = 3600): string
    {
        // For Local Storage, we simulate a presigned URL by generating a signed JWT token
        // that the Ferrox server will validate before serving the file.
        $signature = hash_hmac('sha256', $path . time(), getenv('APP_SECRET') ?: 'dev-secret');
        return "/api/storage/download?file=" . urlencode($path) . "&sig={$signature}&expires=" . (time() + $expiresInSeconds);
    }

    private function buildPath(string $path): string
    {
        // Security: Prevent Directory Traversal Attacks
        $cleanPath = str_replace(['../', '..\\'], '', $path);
        return $this->basePath . '/' . ltrim($cleanPath, '/');
    }
}
