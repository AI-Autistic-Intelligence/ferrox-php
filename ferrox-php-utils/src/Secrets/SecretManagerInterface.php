<?php
namespace Ferrox\Utils\Secrets;

/**
 * Interface for reading secrets securely across different environments.
 */
interface SecretManagerInterface
{
    /**
     * Retrieves a secret by its name/key.
     * @param string $secretName The name of the secret to retrieve.
     * @return string|null Returns the secret value or null if not found.
     */
    public function getSecret(string $secretName): ?string;
}
