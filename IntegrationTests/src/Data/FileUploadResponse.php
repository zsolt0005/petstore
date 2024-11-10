<?php declare(strict_types=1);

namespace PetStore\Tests\Data;

/**
 * Class FileUploadResponse
 *
 * @package PetStore\Tests\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class FileUploadResponse
{
    /**
     * Constructor.
     *
     * @param int $code
     * @param string $type
     * @param string $message
     */
    public function __construct(
        public int    $code,
        public string $type,
        public string $message
    )
    {
    }
}