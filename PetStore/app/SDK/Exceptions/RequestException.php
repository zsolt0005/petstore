<?php declare(strict_types=1);

namespace PetStore\SDK\Exceptions;

use Exception;

/**
 * Class BadRequestException
 *
 * @package PetStore\SDK\Exceptions
 * @author  Zsolt Döme
 * @since   2024
 */
final class RequestException extends Exception
{
    /**
     * Constructor.
     *
     * @param int $httpStatusCode
     */
    public function __construct(public readonly int $httpStatusCode)
    {
        parent::__construct('Request failed with status ' . $httpStatusCode, $httpStatusCode, null);
    }
}