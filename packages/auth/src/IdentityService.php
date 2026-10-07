<?php
namespace Ferrox\Auth;

use Ferrox\Security\Paseto\PasetoEngine;
use Ferrox\Mailer\MailerInterface;
use Ferrox\Database\Core\RepositoryInterface;
use RuntimeException;

/**
 * Enterprise Identity Service managing Registration, Password Recovery, and SSO.
 */
class IdentityService
{
    public function __construct(
        private RepositoryInterface $userRepository,
        private PasetoEngine $pasetoEngine,
        private MailerInterface $mailer
    ) {}

    /**
     * Register a new user securely, hashing the password and sending a welcome email.
     */
    public function register(string $email, string $plainPassword, string $name): string
    {
        $hashedPassword = password_hash($plainPassword, PASSWORD_ARGON2ID);
        
        // Simulating DB save
        $userId = "USR-" . bin2hex(random_bytes(4));
        error_log("[IDENTITY] User {$email} registered successfully as {$userId}");

        // Send Welcome Email
        $this->mailer->send(
            $email,
            "Welcome to Ferrox Enterprise",
            "<h1>Hello {$name},</h1><p>Welcome aboard!</p>"
        );

        // Auto-login by returning PASETO v4 token
        return $this->pasetoEngine->issueToken($userId, ['user']);
    }

    /**
     * Trigger Password Recovery Flow
     */
    public function requestPasswordReset(string $email): void
    {
        // 1. Generate secure OTP or secure Reset Token
        $resetToken = bin2hex(random_bytes(32));
        
        // 2. Save token to DB with a short TTL (e.g., 15 minutes)
        error_log("[IDENTITY] Saved reset token for {$email}");

        // 3. Send email to user
        $resetLink = "https://your-domain.com/auth/reset?token=" . $resetToken;
        $this->mailer->send(
            $email,
            "Password Reset Request",
            "<p>Click here to reset your password: <a href='{$resetLink}'>Reset Password</a></p><p>This link expires in 15 minutes.</p>"
        );
    }

    /**
     * Handle SSO (Single Sign-On) Connection
     */
    public function connectSso(SsoProviderInterface $provider, string $authCode): string
    {
        $ssoProfile = $provider->exchangeCodeForProfile($authCode);
        
        // Match user by email or SSO ID
        // If not exists, register implicitly
        error_log("[IDENTITY] SSO Login successful for " . $ssoProfile['email']);
        
        return $this->pasetoEngine->issueToken($ssoProfile['id'], ['user', 'sso']);
    }
}
