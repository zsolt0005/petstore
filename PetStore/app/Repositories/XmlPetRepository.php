<?php declare(strict_types=1);

namespace PetStore\Repositories;

use Nette\Utils\Arrays;
use PetStore\Data\Pet;

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

        $this->data[] = $data;
        $this->save();

        return true;
    }
}