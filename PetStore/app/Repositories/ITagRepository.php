<?php declare(strict_types=1);

namespace PetStore\Repositories;

use PetStore\Data\Tag;

/**
 * Class ICategoryRepository
 *
 * @package PetStore\Repositories
 * @author  Zsolt Döme
 * @since   2024
 */
interface ITagRepository
{
    /**
     * Creates a new Tag.
     *
     * @param Tag $data
     *
     * @return bool
     */
    public function create(Tag $data): bool;

    /**
     * Deletes an existing Tag.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Checks if a Tag exists or not
     *
     * @param int $id
     *
     * @return bool
     */
    public function exists(int $id): bool;
}