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
}