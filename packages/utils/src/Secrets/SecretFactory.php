<?php
namespace Ferrox\Utils\Secrets;

/**
 * Factory to abstract away the resolution of the correct SecretManager.
 */
class SecretFactory
{
    /**
     * Instantiates the appropriate SecretManager based on environment configuration.
     * Uses ENV fallback if no specific cloud provider is selected.
     */
    public static function create(): SecretManagerInterface
    {
        $provider = getenv('FERROX_SECRET_PROVIDER') ?: 'env';

        return match (strtolower($provider)) {
            'aws' => new AwsSecretManager(getenv('AWS_REGION') ?: 'eu-west-1'),
            'gcp' => new GcpSecretManager(getenv('GCP_PROJECT_ID') ?: 'default-project'),
            default => new EnvSecretManager(),
        };
    }
}
