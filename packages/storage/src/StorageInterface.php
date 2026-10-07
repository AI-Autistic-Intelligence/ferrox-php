<?php
namespace Ferrox\Storage;

/**
 * Universal Storage Abstraction for Ferrox Enterprise.
 * Allows seamless switching between Local VPS, AWS S3, and Google Cloud Storage.
 */
interface StorageInterface
{
    /**
     * Store a file and return its unique path/URI.
     */
    public function put(string $path, string $contents, array $options = []): string;

    /**
     * Retrieve a file's contents.
     */
    public function get(string $path): ?string;

    /**
     * Delete a file.
     */
    public function delete(string $path): bool;

    /**
     * Get a temporary/presigned secure URL for client download (Zero-Trust).
     */
    public function getPresignedUrl(string $path, int $expiresInSeconds = 3600): string;
}
