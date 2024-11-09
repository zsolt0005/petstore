<?php declare(strict_types=1);

namespace PetStore\Services;

use PetStore\Data\Pet;
use PetStore\Data\Result;
use PetStore\Repositories\ICategoryRepository;
use PetStore\Repositories\IPetRepository;
use PetStore\Results\CreatePetErrorResult;
use PetStore\Results\GetPetByIdErrorResult;
use PetStore\Results\UpdatePetErrorResult;

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
            return Result::of(failure: CreatePetErrorResult::CATEGORY_NOT_FOUND);
        }

        $petCreated = $this->repository->create($data);
        if(!$petCreated)
        {
            return Result::of(failure: CreatePetErrorResult::FAILED);
        }

        return Result::of(success: $data);
    }

    /**
     * Creates a new Pet.
     *
     * @param Pet $data
     *
     * @return Result<Pet, UpdatePetErrorResult>
     */
    public function update(Pet $data): Result
    {
        if($data->id <= 0)
        {
            return Result::of(failure: UpdatePetErrorResult::INVALID_ID);
        }

        if(!$this->categoryRepository->exists($data->category->id))
        {
            return Result::of(failure: UpdatePetErrorResult::CATEGORY_NOT_FOUND);
        }

        $petUpdated = $this->repository->update($data);
        if(!$petUpdated)
        {
            return Result::of(failure: UpdatePetErrorResult::PET_NOT_FOUND);
        }

        return Result::of(success: $data);
    }

    /**
     * Get a pet by its ID.
     *
     * @param int $id
     *
     * @return Result<Pet, GetPetByIdErrorResult>
     */
    public function getById(int $id): Result
    {
        if($id <= 0)
        {
            return Result::of(failure: GetPetByIdErrorResult::INVALID_ID);
        }

        $data = $this->repository->getById($id);
        if($data === null)
        {
            return Result::of(failure: GetPetByIdErrorResult::PET_NOT_FOUND);
        }

        return Result::of(success: $data);
    }
}