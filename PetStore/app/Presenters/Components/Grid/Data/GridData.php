<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid\Data;

/**
 * Class GridData
 *
 * @package PetStore\Presenters\Components\Grid\data
 * @author  Zsolt Döme
 * @since   2024
 */
final class GridData
{
    /**
     * Constructor.
     *
     * @param string[] $headerColumns Headers.
     * @param GridRowData[] $rows Rows.
     */
    public function __construct(public array $headerColumns = [], public array $rows = [])
    {
    }

    /**
     * Factory method.
     *
     * @param string[] $headerColumns
     * @param GridRowData[] $rows
     *
     * @return GridData
     */
    public static function create(array $headerColumns, array $rows): GridData
    {
        return new self($headerColumns, $rows);
    }
}