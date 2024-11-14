<?php declare(strict_types=1);

namespace PetStore\Repositories;

use PetStore\Data\Category;

/**
 * Class ICategoryRepository
 *
 * @package PetStore\Repositories
 * @author  Zsolt Döme
 * @since   2024
 */
interface ICategoryRepository
{
    /**
     * Creates a new Category.
     *
     * @param Category $data
     *
     * @return bool
     */
    public function create(Category $data): bool;

    /**
     * Deletes an existing Category.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Checks if a Category exists or not
     *
     * @param int $id
     *
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Finds a category by its name.
     *
     * @param string $name
     *
     * @return Category|null
     */
    public function findByName(string $name): ?Category;
}