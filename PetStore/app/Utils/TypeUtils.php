<?php declare(strict_types=1);

namespace PetStore\Utils;

/**
 * Class TypeUtils
 *
 * @package PetStore\Utils
 * @author  Zsolt Döme
 * @since   2024
 */
final class TypeUtils
{
    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Converts input to string.
     *
     * @param mixed $input
     *
     * @return string|null
     */
    public static function convertToString(mixed $input): ?string
    {
        if($input === null)
        {
            return null;
        }

        if(is_string($input))
        {
            return $input;
        }

        if(is_numeric($input) || is_bool($input))
        {
            return (string) $input;
        }

        return null;
    }

    /**
     * Converts input to int.
     *
     * @param mixed $input
     *
     * @return int|null
     */
    public static function convertToInt(mixed $input): ?int
    {
        if($input === null)
        {
            return null;
        }

        if(is_int($input))
        {
            return $input;
        }

        if(is_numeric($input))
        {
            return (int) $input;
        }

        return null;
    }
}