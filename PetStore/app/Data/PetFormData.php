<?php declare(strict_types=1);

namespace PetStore\Data;

use Nette\Http\FileUpload;

/**
 * Class PetFormData
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final class PetFormData
{
    /**
     * Constructor.
     *
     * @param string $name
     * @param string $category
     * @param string $tags
     * @param string $status
     * @param FileUpload[] $images
     */
    public function __construct(
        public string $name,
        public string $category,
        public string $tags,
        public string $status,
        public array $images
    )
    {
    }
}