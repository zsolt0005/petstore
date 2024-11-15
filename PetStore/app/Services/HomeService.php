<?php declare(strict_types=1);

namespace PetStore\Services;

use InvalidArgumentException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Http\IResponse;
use PetStore\Data\HomeFilterData;
use PetStore\Data\Pet;
use PetStore\Data\PetFormData;
use PetStore\Data\Result;
use PetStore\Enums\HomeActionCreateErrorResult;
use PetStore\Enums\HomeActionDefaultErrorResult;
use PetStore\Enums\HomeActionDeleteErrorResult;
use PetStore\Enums\HomeActionUpdateErrorResult;
use PetStore\Presenters\Components\Grid\Builders\GridDataBuilder;
use PetStore\Presenters\Components\Grid\Data\GridColumnActionData;
use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\SDK\Exceptions\RequestException;
use PetStore\SDK\PetStoreSdk;

/**
 * Class HomeService
 *
 * @package PetStore\Services
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class HomeService
{
    /**
     * Constructor.
     *
     * @param LinkGenerator $linkGenerator
     * @param CategoryService $categoryService
     * @param TagService $tagService
     */
    public function __construct(
        private LinkGenerator $linkGenerator,
        private CategoryService $categoryService,
        private TagService $tagService
    )
    {
    }

    /**
     * Prepares the grid data.
     *
     * @param HomeFilterData|null $filterData
     *
     * @return Result<HomeActionDefaultErrorResult, GridData>
     */
    public function prepareGridData(?HomeFilterData $filterData): Result
    {
        $dataBuilder = GridDataBuilder::create()
            ->addHeader('ID')
            ->addHeader('Name')
            ->addHeader('Category')
            ->addHeader('Status')
            ->addHeader('Tags')
            ->addHeader('Actions');

        try
        {
            $pets = match (true)
            {
                $filterData?->id !== null => [PetStoreSdk::createInstance()->getById($filterData->id)],
                $filterData?->status !== null => PetStoreSdk::createInstance()->findByStatus($filterData->status),
                $filterData?->tags !== null => PetStoreSdk::createInstance()->findByTags($filterData->tags),
                default => PetStoreSdk::createInstance()->getAll(),
            };

            if(empty($pets))
            {
                return Result::of(HomeActionDefaultErrorResult::NOT_FOUND, $dataBuilder->build());
            }

            foreach($pets as $pet)
            {
                $actions = [
                    GridColumnActionData::create('edit', 'Edit', $this->linkGenerator->link('Home:edit', ['id' => $pet->id]), 'btn-info'),
                    GridColumnActionData::create('delete', 'Delete', $this->linkGenerator->link('Home:delete', ['id' => $pet->id]), 'btn-danger')
                ];

                $dataBuilder->addRow()
                    ->addColumn((string) $pet->id)
                    ->addColumn($pet->name)
                    ->addColumn($pet->category->name)
                    ->addColumn($pet->status)
                    ->addColumn($pet->getTagNames(', '))
                    ->addActionsColumn($actions);
            }
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S404_NotFound => Result::of(HomeActionDefaultErrorResult::NOT_FOUND, $dataBuilder->build()),
                IResponse::S400_BadRequest => Result::of(HomeActionDefaultErrorResult::INVALID_FILTER_VALUE, $dataBuilder->build()),
                default => Result::of(HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR, $dataBuilder->build())
            };
        }
        catch (InvalidArgumentException | InvalidLinkException $e)
        {
            return Result::of(HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR, $dataBuilder->build());
        }

        return Result::of(null, $dataBuilder->build());
    }

    /**
     * Deletes a pet by its ID.
     *
     * @param int $id
     *
     * @return Result<HomeActionDeleteErrorResult, int>
     */
    public function deleteById(int $id): Result
    {
        try
        {
            PetStoreSdk::createInstance()->deleteById($id);
            return Result::of(success: $id);
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S400_BadRequest => Result::of(failure: HomeActionDeleteErrorResult::BAD_REQUEST),
                default => Result::of(failure: HomeActionDeleteErrorResult::INTERNAL_SERVER_ERROR)
            };
        }
    }

    /**
     * Creates a new pet.
     *
     * @param PetFormData $data
     *
     * @return Result<HomeActionCreateErrorResult, Pet>
     */
    public function createPet(PetFormData $data): Result
    {
        $category = $this->categoryService->findByName($data->category);
        if($category === null)
        {
            return Result::of(failure: HomeActionCreateErrorResult::CATEGORY_NOT_FOUND);
        }

        $tagNames = explode(',', str_replace(' ', '', $data->tags));
        $tags = [];
        foreach($tagNames as $tagName)
        {
            $tag = $this->tagService->findByName($tagName);
            if($tag === null)
            {
                return Result::of(failure: HomeActionCreateErrorResult::TAG_NOT_FOUND);
            }

            $tags[] = $tag;
        }

        $pet = new Pet();
        $pet->name = $data->name;
        $pet->category = $category;
        $pet->tags = $tags;
        $pet->status = $data->status;

        $sdk = PetStoreSdk::createInstance();

        try
        {
            $createdPet = $sdk->create($pet);
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S405_MethodNotAllowed, IResponse::S400_BadRequest => Result::of(failure: HomeActionCreateErrorResult::INVALID_INPUT),
                default => Result::of(failure: HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR)
            };
        }
        catch (InvalidArgumentException)
        {
            return Result::of(failure: HomeActionCreateErrorResult::INTERNAL_SERVER_ERROR);
        }

        if(count($data->images) === 0)
        {
            return Result::of(success: $createdPet);
        }

        try
        {
            $sdk->uploadImages($createdPet, $data->images);
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S400_BadRequest => Result::of(failure: HomeActionCreateErrorResult::INVALID_IMAGE_FILE),
                default => Result::of(failure: HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR)
            };
        }

        return Result::of(success: $createdPet);
    }

    /**
     * Updates a pet.
     *
     * @param Pet $pet
     * @param PetFormData $data
     *
     * @return Result<HomeActionCreateErrorResult, Pet>
     */
    public function updatePet(Pet $pet, PetFormData $data): Result
    {
        $category = $this->categoryService->findByName($data->category);
        if($category === null)
        {
            return Result::of(failure: HomeActionCreateErrorResult::CATEGORY_NOT_FOUND);
        }

        $tagNames = explode(',', str_replace(' ', '', $data->tags));
        $tags = [];
        foreach($tagNames as $tagName)
        {
            $tag = $this->tagService->findByName($tagName);
            if($tag === null)
            {
                return Result::of(failure: HomeActionCreateErrorResult::TAG_NOT_FOUND);
            }

            $tags[] = $tag;
        }

        $updatePetData = new Pet();
        $updatePetData->id = $pet->id;
        $updatePetData->name = $data->name;
        $updatePetData->category = $category;
        $updatePetData->tags = $tags;
        $updatePetData->status = $data->status;
        $updatePetData->photoUrls = $pet->photoUrls;

        $sdk = PetStoreSdk::createInstance();

        try
        {
            $updatedPet = $sdk->update($updatePetData);
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S400_BadRequest , IResponse::S404_NotFound, IResponse::S405_MethodNotAllowed
                    => Result::of(failure: HomeActionCreateErrorResult::INVALID_INPUT),
                default => Result::of(failure: HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR)
            };
        }
        catch (InvalidArgumentException)
        {
            return Result::of(failure: HomeActionCreateErrorResult::INTERNAL_SERVER_ERROR);
        }

        if(count($data->images) === 0)
        {
            return Result::of(success: $updatedPet);
        }

        try
        {
            $sdk->uploadImages($updatedPet, $data->images);
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S400_BadRequest => Result::of(failure: HomeActionCreateErrorResult::INVALID_IMAGE_FILE),
                default => Result::of(failure: HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR)
            };
        }

        return Result::of(success: $updatedPet);
    }

    /**
     * Gets a pet by its ID.
     *
     * @param int $id
     *
     * @return Result<HomeActionUpdateErrorResult, Pet>
     */
    public function getById(int $id): Result
    {
        try
        {
            return Result::of(success: PetStoreSdk::createInstance()->getById($id));
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S400_BadRequest, IResponse::S404_NotFound => Result::of(failure: HomeActionUpdateErrorResult::PET_NOT_FOUND),
                default => Result::of(failure: HomeActionUpdateErrorResult::INTERNAL_SERVER_ERROR)
            };
        }
        catch (InvalidArgumentException | InvalidLinkException $e)
        {
            return Result::of(failure: HomeActionUpdateErrorResult::INTERNAL_SERVER_ERROR);
        }
    }
}