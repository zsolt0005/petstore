<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid\Data;

/**
 * Class GridActionsColumnData
 *
 * @package PetStore\Presenters\Components\Grid\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final class GridActionsColumnData implements IColumnData
{
    /**
     * Constructor.
     *
     * @param GridColumnActionData[] $actions
     */
    public function __construct(public array $actions = [])
    {
    }

    /**
     * Factory method.
     *
     * @param GridColumnActionData[] $actions
     *
     * @return self
     */
    public static function create(array $actions): self
    {
        return new self($actions);
    }
}