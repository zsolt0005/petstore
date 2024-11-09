<?php declare(strict_types=1);

namespace PetStore\Data;

use Symfony\Component\Serializer\Annotation\SerializedPath;

/**
 * Data for representing a pet.
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final class Pet
{
    /** @var int ID. */
    public int $id;

    /** @var string Name. */
    public string $name;

    /** @var Category Category */
    public Category $category;

    /** @var string[] Photo urls. */
    public array $photoUrls = [];

    /** @var Tag[] Tags. */
    #[SerializedPath('[tags][tag]')]
    public array $tags = [];

    /** @var string Status. */
    public string $status;
}