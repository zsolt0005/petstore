<?php declare(strict_types=1);

namespace PetStore\Providers;

/**
 * Class PathProvider
 *
 * @package PetStore\Providers
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class PathProvider
{
    /**
     * Constructor.
     *
     * @param string $petImagePath
     */
    public function __construct(public string $petImagePath)
    {
    }
}