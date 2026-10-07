<?php
namespace Ferrox\Utils\Secrets;

/**
 * Connects to Google Cloud Secret Manager to retrieve secrets securely.
 */
class GcpSecretManager implements SecretManagerInterface
{
    private string $projectId;
    // Note: In a real app, inject the Google\Cloud\SecretManager\V1\SecretManagerServiceClient

    public function __construct(string $projectId)
    {
        $this->projectId = $projectId;
    }

    public function getSecret(string $secretName): ?string
    {
        // 1. Check if the GCP SDK is available
        if (!class_exists('\Google\Cloud\SecretManager\V1\SecretManagerServiceClient')) {
            throw new \RuntimeException("GCP SDK not found. Run: composer require google/cloud-secret-manager");
        }

        try {
            $client = new \Google\Cloud\SecretManager\V1\SecretManagerServiceClient();
            
            // Format: projects/{project_id}/secrets/{secret_id}/versions/latest
            $name = $client->secretVersionName($this->projectId, $secretName, 'latest');
            
            $response = $client->accessSecretVersion($name);
            return $response->getPayload()->getData();
            
        } catch (\Exception $e) {
            error_log("[GCP Secret Manager] Error retrieving {$secretName}: " . $e->getMessage());
            return null;
        }
    }
}
