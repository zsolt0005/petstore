<?php declare(strict_types=1);

namespace PetStore\Tests\Data;

/**
 * Data for representing a pet.
 *
 * @package PetStore\Tests\Data
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
    public array $tags = [];

    /** @var string Status. */
    public string $status;

}