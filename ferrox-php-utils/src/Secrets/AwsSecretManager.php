<?php
namespace Ferrox\Utils\Secrets;

/**
 * Connects to AWS Secrets Manager to retrieve secrets at runtime.
 * Avoids storing hardcoded secrets in the environment.
 */
class AwsSecretManager implements SecretManagerInterface
{
    private string $region;
    // Note: In a real app, inject the Aws\SecretsManager\SecretsManagerClient via Constructor

    public function __construct(string $region = 'eu-west-1')
    {
        $this->region = $region;
    }

    public function getSecret(string $secretName): ?string
    {
        // 1. Check if the AWS SDK is available
        if (!class_exists('\Aws\SecretsManager\SecretsManagerClient')) {
            throw new \RuntimeException("AWS SDK not found. Run: composer require aws/aws-sdk-php");
        }

        try {
            $client = new \Aws\SecretsManager\SecretsManagerClient([
                'version' => 'latest',
                'region' => $this->region,
            ]);

            $result = $client->getSecretValue([
                'SecretId' => $secretName,
            ]);

            if (isset($result['SecretString'])) {
                return $result['SecretString'];
            }
            
            return base64_decode($result['SecretBinary']);
        } catch (\Exception $e) {
            error_log("[AWS Secrets Manager] Error retrieving {$secretName}: " . $e->getMessage());
            return null;
        }
    }
}
