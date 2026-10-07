<?php
namespace Ferrox\Utils\Strings;

class StringUtils
{
    /**
     * Converts a kebab-case or snake_case string into camelCase.
     * Essential for mapping incoming JSON payloads to PHP DTO properties.
     *
     * @param string $string The input string (e.g., "first-name" or "first_name")
     * @return string The converted string (e.g., "firstName")
     */
    public static function toCamelCase(string $string): string
    {
        $string = ucwords(str_replace(['-', '_'], ' ', $string));
        return lcfirst(str_replace(' ', '', $string));
    }

    /**
     * Converts a camelCase string into snake_case.
     * Required for translating PHP object properties to SQL database columns.
     *
     * @param string $string The camelCase string (e.g., "firstName")
     * @return string The snake_case string (e.g., "first_name")
     */
    public static function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
    
    public static function toKebabCase(string $string): string
    {
        return str_replace('_', '-', self::toSnakeCase($string));
    }
    
    public static function mask(string $string, int $keepStart, int $keepEnd): string
    {
        $len = strlen($string);
        if ($len <= $keepStart + $keepEnd) {
            return $string;
        }
        
        $start = substr($string, 0, $keepStart);
        $end = substr($string, -$keepEnd);
        $maskLength = $len - $keepStart - $keepEnd;
        
        return $start . str_repeat('*', $maskLength) . $end;
    }

    /**
     * Generates a standard UUID version 4.
     * Uses cryptographically secure pseudo-random number generator.
     *
     * @return string A valid UUIDv4 string (e.g., "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx")
     */
    public static function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Generates a time-ordered pseudo-UUIDv7.
     * Ferrox Rust natively uses UUID v7 (time-ordered). In PHP, we simulate 
     * the sorting capability by appending a timestamp to the prefix.
     * This prevents SQL index fragmentation on highly concurrent inserts.
     *
     * @return string A sortable pseudo-UUID string.
     */
    public static function generateUuidV7Fallback(): string
    {
        $timeMs = (int)(microtime(true) * 1000);
        $timeHex = str_pad(dechex($timeMs), 12, '0', STR_PAD_LEFT);
        
        $randomBytes = random_bytes(10);
        $randA = hexdec(bin2hex(substr($randomBytes, 0, 2)));
        $randB = hexdec(bin2hex(substr($randomBytes, 2, 2)));
        $randC = hexdec(bin2hex(substr($randomBytes, 4, 2)));
        $randD = hexdec(bin2hex(substr($randomBytes, 6, 2)));

        return sprintf(
            '%s-%04x-%04x-%04x-%04x%04x',
            substr($timeHex, 0, 8),
            hexdec(substr($timeHex, 8, 4)),
            ($randA & 0x0fff) | 0x7000,
            ($randB & 0x3fff) | 0x8000,
            $randC, $randD
        );
    }
}
