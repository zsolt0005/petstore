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
final class XmlPetRepository implements IPetRepository
{
    /** @var Pet[] */
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
        return true;
    }
}