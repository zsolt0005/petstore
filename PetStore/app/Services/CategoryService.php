<?php declare(strict_types=1);

namespace PetStore\Services;

use PetStore\Data\Category;
use PetStore\Data\Result;
use PetStore\Repositories\ICategoryRepository;
use PetStore\Results\CreateCategoryErrorResult;

/**
 * Class CategoryService
 *
 * @package PetStore\Services
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class CategoryService
{
    /**
     * Constructor.
     *
     * @param ICategoryRepository $repository
     */
    public function __construct(private ICategoryRepository $repository)
    {
    }

    /**
     * Creates a new category.
     *
     * @param Category $category
     *
     * @return Result
     */
    public function create(Category $category): Result
    {
        if($category->id <= 0 || empty($category->name))
        {
            return Result::of(failure: CreateCategoryErrorResult::INVALID_DATA);
        }

        $createdCategory = $this->repository->create($category);
        if(!$createdCategory)
        {
            return Result::of(failure: CreateCategoryErrorResult::FAILED);
        }

        return Result::of(success: $category);
    }

    /**
     * Deletes a category.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        if(!$this->repository->exists($id))
        {
            return true;
        }

        $this->repository->delete($id);
        return true;
    }
}