<?php
namespace Ferrox\Security\Auth;

/**
 * Zero-Trust Device & Client Fingerprint Binder for Session Tokens.
 */
class ClientFingerprint
{
    /**
     * Computes a deterministic fingerprint hash for a client HTTP session.
     *
     * @param string $userAgent The client's User-Agent string.
     * @param string $acceptLanguage The client's Accept-Language header.
     * @param string $ipAddress The client's IP address.
     * @return string A hex string fingerprint.
     */
    public static function computeFingerprint(string $userAgent, string $acceptLanguage, string $ipAddress): string
    {
        // Normalize IP subnet (e.g. 192.168.1.xxx -> 192.168.1.0)
        $subnet = self::normalizeSubnet($ipAddress);
        
        $data = $userAgent . '|' . $acceptLanguage . '|' . $subnet;
        return hash('sha256', $data);
    }

    /**
     * Verifies if a token binding fingerprint matches the current client request.
     *
     * @param string $boundFingerprint The fingerprint previously bound to the token.
     * @param string $currentUserAgent Current User-Agent.
     * @param string $currentLanguage Current Accept-Language.
     * @param string $currentIp Current IP address.
     * @return bool True if they match, false otherwise.
     */
    public static function verifyBinding(
        string $boundFingerprint,
        string $currentUserAgent,
        string $currentLanguage,
        string $currentIp
    ): bool {
        $currentFp = self::computeFingerprint($currentUserAgent, $currentLanguage, $currentIp);
        return hash_equals($boundFingerprint, $currentFp);
    }

    private static function normalizeSubnet(string $ip): string
    {
        if (str_contains($ip, '.')) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0';
            }
        } elseif (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            if (count($parts) >= 4) {
                return $parts[0] . ':' . $parts[1] . ':' . $parts[2] . ':' . $parts[3] . '::';
            }
        }
        return $ip;
    }
}
