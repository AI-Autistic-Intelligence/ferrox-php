<?php
namespace Ferrox\Utils\Env;

class EnvHelper
{
    /**
     * Safely retrieves an environment variable or a default value.
     */
    public static function get(string $key, $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        // Type conversion heuristcs
        if (strtolower($value) === 'true') return true;
        if (strtolower($value) === 'false') return false;
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }

        return $value;
    }

    /**
     * Strict requirement. Throws if missing.
     */
    public static function getOrThrow(string $key): mixed
    {
        $val = self::get($key);
        if ($val === null) {
            throw new \RuntimeException("Missing required environment variable: {$key}");
        }
        return $val;
    }
}
