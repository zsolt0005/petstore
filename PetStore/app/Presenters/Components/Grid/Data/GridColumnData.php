<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid\Data;

/**
 * Class GridColumnData
 *
 * @package PetStore\Presenters\Components\Grid\data
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class GridColumnData implements IColumnData
{
    /**
     * Constructor.
     *
     * @param string $text
     */
    public function __construct(public string $text)
    {
    }

    /**
     * Factory method.
     *
     * @param string $text
     *
     * @return GridColumnData
     */
    public static function create(string $text): GridColumnData
    {
        return new self($text);
    }
}