<?php declare(strict_types=1);

namespace PetStore\Services;

use PetStore\Data\Pet;
use PetStore\Data\Result;
use PetStore\Repositories\ICategoryRepository;
use PetStore\Repositories\IPetRepository;
use PetStore\Results\CreatePetErrorResult;

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
     * @param ICategoryRepository $categoryRepository
     */
    public function __construct(
        private IPetRepository $repository,
        private ICategoryRepository $categoryRepository
    )
    {
    }

    /**
     * Creates a new Pet.
     *
     * @param Pet $data
     *
     * @return Result<Pet, CreatePetErrorResult>
     */
    public function create(Pet $data): Result
    {
        if(!$this->categoryRepository->exists($data->category->id))
        {
            return Result::of(failure: CreatePetErrorResult::CATEGORY_DOES_NOT_EXIST);
        }

        $petCreated = $this->repository->create($data);
        if($petCreated)
        {
            Result::of(success: $data);
        }

        return Result::of(failure: CreatePetErrorResult::FAILED);
    }
}