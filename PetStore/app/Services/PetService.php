<?php declare(strict_types=1);

namespace PetStore\Services;

use PetStore\Data\Pet;
use PetStore\Repositories\IPetRepository;

/**
 * Class PetService
 *
 * @package PetStore\Services
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class PetService
{
    /**
     * Constructor.
     *
     * @param IPetRepository $repository
     */
    public function __construct(private IPetRepository $repository)
    {
    }

    /**
     * Creates a new Pet.
     *
     * @param Pet $data
     *
     * @return Pet|null
     */
    public function create(Pet $data): ?Pet
    {
        $petCreated = $this->repository->create($data);

        return $petCreated ? $data : null;
    }
}