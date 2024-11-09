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
 */
final class XmlPetRepository extends AXmlRepository implements IPetRepository
{
    /** @var Pet[] Data. */
    private array $pets = [];

    /** @inheritDoc */
    public function create(Pet $data): bool
    {
        $existingPetData = Arrays::first($this->pets, static fn(Pet $d) => $d->id === $data->id);
        if($existingPetData !== null)
        {
            return true;
        }

        $this->pets[] = $data;
        $this->save();

        return true;
    }

    /** @inheritDoc */
    protected function getData(): array
    {
        return $this->pets;
    }

    /** @inheritDoc */
    protected function setData(array $data): void
    {
        $this->pets = $data;
    }

    /** @inheritDoc */
    protected function getDataType(): string
    {
        return Pet::class;
    }
}