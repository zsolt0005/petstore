<?php declare(strict_types=1);

namespace PetStore\Services;

use PetStore\Data\Result;
use PetStore\Data\Tag;
use PetStore\Repositories\ITagRepository;
use PetStore\Enums\CreateTagErrorResult;

/**
 * Class TagService
 *
 * @package PetStore\Services
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class TagService
{
    /**
     * Constructor.
     *
     * @param ITagRepository $repository
     */
    public function __construct(private ITagRepository $repository)
    {
    }

    /**
     * Creates a new tag.
     *
     * @param Tag $tag
     *
     * @return Result<CreateTagErrorResult, Tag>
     */
    public function create(Tag $tag): Result
    {
        if($tag->id <= 0 || empty($tag->name))
        {
            return Result::of(failure: CreateTagErrorResult::INVALID_DATA);
        }

        $createdTag = $this->repository->create($tag);
        if(!$createdTag)
        {
            return Result::of(failure: CreateTagErrorResult::FAILED);
        }

        return Result::of(success: $tag);
    }

    /**
     * Deletes a Tag.
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

    /**
     * Finds a tag by its name.
     *
     * @param string $name
     *
     * @return Tag|null
     */
    public function findByName(string $name): ?Tag
    {
        return $this->repository->findByName($name);
    }
}