<?php
namespace Ferrox\Utils\Dates;

use DateTime;
use DateTimeZone;

class DateUtils
{
    /**
     * Returns the current time strictly in UTC.
     * All databases in Ferrox MUST store dates in UTC.
     *
     * @return DateTime The current UTC date and time.
     */
    public static function nowUtc(): DateTime
    {
        return new DateTime('now', new DateTimeZone('UTC'));
    }

    /**
     * Formats a given DateTime object to ISO-8601 string representation.
     * Used for JSON payload serialization.
     *
     * @param DateTime $date The date to format.
     * @return string The ISO-8601 formatted string.
     */
    public static function formatIso8601(DateTime $date): string
    {
        return $date->format(DateTime::ATOM);
    }
    
    /**
     * Converts a given UTC DateTime to a formatted GMT string.
     * Required by Ferrox Rust standards for specific Frontend/Header compatibilities.
     *
     * @param DateTime $date The UTC date to convert.
     * @return string The GMT formatted string (e.g., "Mon, 01 Jan 2024 12:00:00 GMT")
     */
    public static function toGmtString(DateTime $date): string
    {
        $clone = clone $date;
        $clone->setTimezone(new DateTimeZone('GMT'));
        return $clone->format('D, d M Y H:i:s \G\M\T');
    }
}
