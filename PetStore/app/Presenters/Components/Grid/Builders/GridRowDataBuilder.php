<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid\Builders;

use PetStore\Presenters\Components\Grid\Data\GridColumnData;
use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\Presenters\Components\Grid\Data\GridRowData;

/**
 * Class GridRowDataBuilder
 *
 * @package PetStore\Presenters\Components\Grid\Builders
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class GridRowDataBuilder
{
    /**
     * Constructor.
     *
     * @param GridDataBuilder $previousBuilder
     * @param GridRowData $data
     */
    private function __construct(private GridDataBuilder $previousBuilder, private GridRowData $data)
    {
    }

    /**
     * Factory method.
     *
     * @param GridDataBuilder $previousBuilder
     * @param GridRowData $data
     *
     * @return GridRowDataBuilder
     */
    public static function create(GridDataBuilder $previousBuilder, GridRowData $data): GridRowDataBuilder
    {
        return new self($previousBuilder, $data);
    }

    /**
     * Adds a new column to the row.
     *
     * @param string $text
     *
     * @return self
     */
    public function addColumn(string $text): self
    {
        $this->data->columns[] = GridColumnData::create($text);

        return $this;
    }

    /**
     * Adds a new row to the grid.
     *
     * @return GridRowDataBuilder
     */
    public function addRow(): GridRowDataBuilder
    {
        return $this->previousBuilder->addRow();
    }

    /**
     * Builds the data.
     *
     * @return GridData
     */
    public function build(): GridData
    {
        return $this->previousBuilder->build();
    }
}