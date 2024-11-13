<?php declare(strict_types=1);

namespace PetStore\Data;

/**
 * Class HomeFilterData
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final class HomeFilterData
{
    /**
     * Constructor.
     *
     * @param int|null $id Filter value for filtering by ID.
     * @param string|null $status Filter value for filtering by Status.
     * @param string|null $tags Filter value for filtering by Tags.
     */
    public function __construct(
        public ?int $id = null,
        public ?string $status = null,
        public ?string $tags = null
    )
    {
    }
}