<?php
namespace Ferrox\Security\Auth;

use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Protocol\Version4;

/**
 * Manages Dual Token rotation for zero-trust authentication.
 */
class DualTokenManager
{
    private SymmetricKey $key;

    /**
     * @param string $hexKey The hex-encoded 32-byte secret key for PASETO v4 local.
     */
    public function __construct(string $hexKey)
    {
        $this->key = SymmetricKey::fromHex($hexKey);
    }

    /**
     * Generates both an Access Token (short-lived) and a Refresh Token (long-lived).
     *
     * @param string $userId The user's ID.
     * @param array $roles The user's roles.
     * @return array{access_token: string, refresh_token: string}
     */
    public function generateTokens(string $userId, array $roles = []): array
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $exp = (clone $now)->add(new \DateInterval('PT15M')); // 15 minutes

        $builder = (new Builder())
            ->setKey($this->key)
            ->setVersion(new Version4())
            ->setPurpose(Purpose::local())
            ->setIssuedAt($now)
            ->setNotBefore($now)
            ->setExpiration($exp)
            ->setClaims([
                'sub' => $userId,
                'roles' => $roles
            ]);

        $accessToken = $builder->toString();

        // Refresh Token: Cryptographically secure random hex.
        // The refresh token should be stored in a persistence layer (e.g. Database/Redis)
        // by the calling authentication service to allow for revocation and tracking.
        $refreshToken = bin2hex(random_bytes(32));

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }
}
