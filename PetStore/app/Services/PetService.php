<?php declare(strict_types=1);

namespace PetStore\Services;

use PetStore\Data\Pet;
use PetStore\Data\Result;
use PetStore\Repositories\ICategoryRepository;
use PetStore\Repositories\IPetRepository;
use PetStore\Results\CreatePetErrorResult;
use PetStore\Results\DeletePetByIdErrorResult;
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
        $isValid = $this->validatePetData($data);
        if(!$isValid)
        {
            return Result::of(failure: CreatePetErrorResult::INVALID_INPUT);
        }

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
     * Updates a Pet.
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

        $isValid = $this->validatePetData($data);
        if(!$isValid)
        {
            return Result::of(failure: UpdatePetErrorResult::INVALID_INPUT);
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

    /**
     * Deletes a pet by its ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        if($id <= 0)
        {
            return false;
        }

        $this->repository->deleteById($id);
        return true;
    }

    private function validatePetData(Pet $data): bool
    {
        if(!isset($data->id) || $data->id <= 0)
        {
            return false;
        }

        if(empty($data->name))
        {
            return false;
        }

        if(!isset($data->category))
        {
            return false;
        }

        if(empty($data->status))
        {
            return false;
        }

        return true;
    }
}