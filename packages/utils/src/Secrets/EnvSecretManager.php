<?php
namespace Ferrox\Utils\Secrets;

/**
 * Fallback Secret Manager that reads from environment variables.
 * Useful for local development or Kubernetes where Secrets are injected as env vars.
 */
class EnvSecretManager implements SecretManagerInterface
{
    public function getSecret(string $secretName): ?string
    {
        $value = getenv($secretName);
        return $value !== false ? $value : null;
    }
}
