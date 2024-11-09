<?php declare(strict_types=1);

namespace PetStore\Repositories;

use Nette\Utils\Arrays;
use PetStore\Data\Category;

/**
 * Class XmlCategoryRepository
 *
 * @package PetStore\Repositories
 * @author  Zsolt Döme
 * @since   2024
 *
 * @extends AXmlRepository<Category>
 */
final class XmlCategoryRepository extends AXmlRepository implements ICategoryRepository
{
    /** @var Category[] Data. */
    private array $data = [];

    /** @inheritDoc */
    protected function getData(): array
    {
        return $this->data;
    }

    /** @inheritDoc */
    protected function setData(array $data): void
    {
        $this->data = $data;
    }

    /** @inheritDoc */
    protected function getDataType(): string
    {
        return Category::class;
    }

    /** @inheritDoc */
    public function create(Category $data): bool
    {
        $existingData = Arrays::first($this->data, static fn(Category $d) => $d->id === $data->id);
        if($existingData !== null)
        {
            return true;
        }

        $this->data[] = $data;
        $this->save();

        return true;
    }

    /** @inheritDoc */
    public function delete(int $id): bool
    {
        $existingDataKey = Arrays::firstKey($this->data, static fn(Category $d) => $d->id === $id);
        if($existingDataKey === null)
        {
            return true;
        }

        unset($this->data[$existingDataKey]);
        $this->save();

        return true;
    }

    /** @inheritDoc */
    public function exists(int $id): bool
    {
        $existingData = Arrays::first($this->data, static fn(Category $d) => $d->id === $id);
        return $existingData !== null;
    }
}