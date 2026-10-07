<?php
namespace Ferrox\Security\Auth;

use Ferrox\Core\Errors\AppError;

/**
 * Handles zero-trust password hashing using Argon2id.
 */
class PasswordManager
{
    /**
     * Hashes a password securely using Argon2id.
     *
     * @param string $password The plain text password.
     * @return string The Argon2id hash.
     * @throws AppError If hashing fails.
     */
    public static function hashPassword(string $password): string
    {
        $hash = password_hash($password, PASSWORD_ARGON2ID);
        if ($hash === false) {
            throw AppError::internalServerError("Failed to hash password with Argon2id");
        }
        return $hash;
    }

    /**
     * Verifies a password against an Argon2id hash.
     *
     * @param string $password The plain text password.
     * @param string $hash The Argon2id hash.
     * @return bool True if valid, false otherwise.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
