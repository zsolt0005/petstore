<?php declare(strict_types=1);

namespace PetStore\Repositories;

use PetStore\Data\Pet;

/**
 * Class IPetRepository
 *
 * @package PetStore\Repositories
 * @author  Zsolt Döme
 * @since   2024
 */
interface IPetRepository
{
    /**
     * Creates a new Pet.
     *
     * @param Pet $data
     *
     * @return bool
     */
    public function create(Pet $data): bool;

    /**
     * Updates an existing Pet.
     *
     * @param Pet $data
     *
     * @return bool
     */
    public function update(Pet $data): bool;

    /**
     * Gets the pet by its ID.
     *
     * @param int $id
     *
     * @return Pet|null
     */
    public function getById(int $id): ?Pet;

    /**
     * Deletes the pet by its ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById(int $id): bool;
}