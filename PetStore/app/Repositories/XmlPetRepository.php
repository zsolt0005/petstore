<?php declare(strict_types=1);

namespace PetStore\Repositories;

use Nette\Utils\Arrays;
use PetStore\Data\Pet;
use PetStore\Data\Tag;

/**
 * Class PetRepository
 *
 * @package PetStore\Repositories
 * @author  Zsolt Döme
 * @since   2024
 *
 * @extends AXmlRepository<Pet>
 */
final class XmlPetRepository extends AXmlRepository implements IPetRepository
{
    /** @var Pet[] Data. */
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
        return Pet::class;
    }

    /** @inheritDoc */
    public function create(Pet $data): bool
    {
        $existingData = Arrays::first($this->data, static fn(Pet $d) => $d->id === $data->id);
        if($existingData !== null)
        {
            return true;
        }

        // Generate a new ID if not supplied
        if($data->id === 0)
        {
            $lastPetId = Arrays::last($this->data)->id ?? 0;
            $data->id = $lastPetId + 1;
        }

        $this->data[] = $data;
        $this->save();

        return true;
    }

    /** @inheritDoc */
    public function update(Pet $data): bool
    {
        $existingDataKey = Arrays::firstKey($this->data, static fn(Pet $d) => $d->id === $data->id);
        if($existingDataKey === null)
        {
            return false;
        }

        $this->data[$existingDataKey] = $data;
        $this->save();

        return true;
    }

    /** @inheritDoc */
    public function getById(int $id): ?Pet
    {
        return Arrays::first($this->data, static fn(Pet $d) => $d->id === $id);
    }

    /** @inheritDoc */
    public function deleteById(int $id): bool
    {
        $existingDataKey = Arrays::firstKey($this->data, static fn(Pet $d) => $d->id === $id);
        if($existingDataKey === null)
        {
            return true;
        }

        unset($this->data[$existingDataKey]);
        $this->save();

        return true;
    }

    /** @inheritDoc */
    public function findByStatus(string $status): array
    {
        return array_values(Arrays::filter($this->data, static fn(Pet $d) => $d->status === $status));
    }

    /** @inheritDoc */
    public function findByTags(array $tags): array
    {
        return array_values(Arrays::filter(
            $this->data,
            function(Pet $d) use ($tags): bool
            {
                $tagNames = Arrays::map($d->tags, static fn(Tag $tag) => $tag->name);
                return Arrays::some($tagNames, static fn(string $tagName) => Arrays::contains($tags, (string) $tagName));
            }
        ));
    }

    /** @inheritDoc */
    public function getAll(): array
    {
        return array_values($this->data);
    }
}