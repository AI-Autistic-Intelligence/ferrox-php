<?php
namespace Ferrox\Security\Sentinel;

/**
 * Moving Target Defense (MTD): Ephemeral HMAC Route Tokens.
 * Mutates route signatures periodically to invalidate black-box adversarial probing.
 */
class MTDEngine
{
    private string $secret;
    private int $validityWindow;

    /**
     * @param string $secret The secret key used for HMAC.
     * @param int $validityWindow The window length in seconds.
     */
    public function __construct(string $secret, int $validityWindow = 30)
    {
        $this->secret = $secret;
        $this->validityWindow = $validityWindow;
    }

    /**
     * Generates an ephemeral token for a given path.
     *
     * @param string $path The API path or route.
     * @return string The HMAC token.
     */
    public function generateToken(string $path): string
    {
        $currentWindow = (int)(time() / $this->validityWindow);
        $msg = $path . ':' . $currentWindow;
        return hash_hmac('sha256', $msg, $this->secret);
    }

    /**
     * Validates a given token for a path.
     *
     * @param string $path The API path or route.
     * @param string $token The token to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validateToken(string $path, string $token): bool
    {
        $expected = $this->generateToken($path);
        return hash_equals($expected, $token);
    }
}
