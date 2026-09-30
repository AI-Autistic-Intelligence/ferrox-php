<?php
namespace Ferrox\Storage;

use Ferrox\Storage\Adapters\LocalStorageAdapter;
// use Ferrox\Storage\Adapters\S3StorageAdapter;
// use Ferrox\Storage\Adapters\GcpStorageAdapter;

class StorageFactory
{
    public static function create(): StorageInterface
    {
        $driver = getenv('FERROX_STORAGE_DRIVER') ?: 'local';

        return match (strtolower($driver)) {
            // 's3' => new S3StorageAdapter(getenv('AWS_BUCKET_NAME'), getenv('AWS_REGION')),
            // 'gcp' => new GcpStorageAdapter(getenv('GCP_BUCKET_NAME')),
            default => new LocalStorageAdapter(getenv('FERROX_STORAGE_PATH') ?: '/var/www/ferrox/storage/public'),
        };
    }
}
