<?php declare(strict_types=1);

namespace PetStore\Services;

use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use PetStore\Data\FileUploadResponse;
use PetStore\Data\Pet;
use PetStore\Data\Result;
use PetStore\Providers\PathProvider;
use PetStore\Repositories\ICategoryRepository;
use PetStore\Repositories\IPetRepository;
use PetStore\Enums\CreatePetErrorResult;
use PetStore\Enums\FindPetByStatusErrorResult;
use PetStore\Enums\GetPetByIdErrorResult;
use PetStore\Enums\UpdatePetErrorResult;
use PetStore\Repositories\ITagRepository;
use Throwable;
use Tracy\Debugger;

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
     * @param ITagRepository $tagRepository
     * @param PathProvider $pathProvider
     */
    public function __construct(
        private IPetRepository $repository,
        private ICategoryRepository $categoryRepository,
        private ITagRepository $tagRepository,
        private PathProvider $pathProvider,
    )
    {
    }

    /**
     * Creates a new Pet.
     *
     * @param Pet $data
     *
     * @return Result<CreatePetErrorResult, Pet>
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

        foreach ($data->tags as $tag)
        {
            if(!$this->tagRepository->exists($tag->id))
            {
                return Result::of(failure: CreatePetErrorResult::TAG_NOT_FOUND);
            }
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
     * @return Result<UpdatePetErrorResult, Pet>
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

        foreach ($data->tags as $tag)
        {
            if(!$this->tagRepository->exists($tag->id))
            {
                return Result::of(failure: UpdatePetErrorResult::TAG_NOT_FOUND);
            }
        }

        $petUpdated = $this->repository->update($data);
        if(!$petUpdated)
        {
            return Result::of(failure: UpdatePetErrorResult::PET_NOT_FOUND);
        }

        return Result::of(success: $data);
    }

    /**
     * PARTIALLY Updates a Pet.
     *
     * @param int $id
     * @param string|null $name
     * @param string|null $status
     *
     * @return Result<UpdatePetErrorResult, Pet>
     */
    public function partialUpdate(int $id, ?string $name, ?string $status): Result
    {
        if($id <= 0)
        {
            return Result::of(failure: UpdatePetErrorResult::INVALID_ID);
        }

        $pet = $this->repository->getById($id);
        if($pet === null)
        {
            return Result::of(failure: UpdatePetErrorResult::PET_NOT_FOUND);
        }

        if(!empty($name))
        {
            $pet->name = $name;
        }

        if(!empty($status))
        {
            $pet->status = $status;
        }

        $this->repository->update($pet);
        return Result::of(success: $pet);
    }

    /**
     * Get a pet by its ID.
     *
     * @param int $id
     *
     * @return Result<GetPetByIdErrorResult, Pet>
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

    /**
     * Finds all the pets with the given status.
     *
     * @param string $status
     *
     * @return Result<FindPetByStatusErrorResult, Pet[]>
     */
    public function findByStatus(string $status): Result
    {
        if(empty($status))
        {
            return Result::of(failure: FindPetByStatusErrorResult::INVALID_STATUS);
        }

        return Result::of(success: $this->repository->findByStatus($status));
    }

    /**
     * Finds all the pets that have at least one of the tags.
     *
     * @param string $tags
     *
     * @return Pet[]
     */
    public function findByTags(string $tags): array
    {
        $cleanedTags = explode(',', str_replace(' ', '', $tags));

        return $this->repository->findByTags($cleanedTags);
    }

    /**
     * Gets all the pets.
     *
     * @return Pet[]
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    /**
     * Uploads images for the given Pet.
     *
     * @param int $id
     * @param FileUpload[] $files
     *
     * @return FileUploadResponse
     */
    public function uploadImagesById(int $id, array $files): FileUploadResponse
    {
        if(count($files) === 0)
        {
            return new FileUploadResponse(400, 'failed', 'At least one file is required.');
        }

        $pet = $this->repository->getById($id);
        if($pet === null)
        {
            return new FileUploadResponse(404, 'failed', 'Pet not found.');
        }

        $hasInvalidFiles = Arrays::some($files, static fn(FileUpload $file) => !$file->isImage());
        if($hasInvalidFiles)
        {
            return new FileUploadResponse(400, 'failed', 'All files must be an image.');
        }

        foreach($files as $file)
        {
            try
            {
                $imagePath = $this->getNextImagePath($pet, $file);
                $file->move($imagePath);
            }
            catch (Throwable $e)
            {
                Debugger::log($e, Debugger::ERROR);
                return new FileUploadResponse(500, 'failed', 'Failed to upload images.');
            }

            $pet->photoUrls[] = $imagePath;
        }

        $this->repository->update($pet);
        return new FileUploadResponse(200, 'success', 'Files uploaded successfully.');
    }

    /**
     * Validates pet data.
     *
     * @param Pet $data
     *
     * @return bool
     */
    private function validatePetData(Pet $data): bool
    {
        if(!isset($data->id) || $data->id < 0)
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

        if(count($data->tags) === 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Returns the next pet image path.
     *
     * @param Pet $pet
     * @param FileUpload $file
     *
     * @return string
     */
    private function getNextImagePath(Pet $pet, FileUpload $file): string
    {
        $basePath = $this->pathProvider->petImagePath;

        $fileName = count($pet->photoUrls) + 1;
        $fileExtension = $file->getSuggestedExtension();

        return $basePath . '/' . $pet->id . '/' . $fileName . '.' . $fileExtension;
    }
}