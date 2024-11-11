<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid\Builders;

use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\Presenters\Components\Grid\Data\GridRowData;

/**
 * Class GridDataBuilder
 *
 * @package PetStore\Presenters\Components\Grid\Builders
 * @author  Zsolt Döme
 * @since   2024
 */
final class GridDataBuilder
{
    /** @var GridData Data to be built. */
    private GridData $data;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->data = new GridData();
    }

    /**
     * Factory method.
     *
     * @return self
     */
    public static function create(): GridDataBuilder
    {
        return new self();
    }

    /**
     * Creates a new instance with the data supplied as base.
     *
     * @param GridData $data
     *
     * @return GridDataBuilder
     */
    public static function from(GridData $data): GridDataBuilder
    {
        $builder = new self();
        $builder->data = $data;

        return $builder;
    }

    /**
     * Adds a new header to the grid data.
     *
     * @param string $name
     *
     * @return self
     */
    public function addHeader(string $name): self
    {
        $this->data->headerColumns[] = $name;

        return $this;
    }

    /**
     * Adds a new row to the grid.
     *
     * @return GridRowDataBuilder
     */
    public function addRow(): GridRowDataBuilder
    {
        $rowData = new GridRowData();

        $this->data->rows[] = $rowData;
        return GridRowDataBuilder::create($this, $rowData);
    }

    /**
     * Clears all the rows.
     *
     * @return self
     */
    public function clearRows(): self
    {
        $this->data->rows = [];
        return $this;
    }

    /**
     * Builds the data.
     *
     * @return GridData
     */
    public function build(): GridData
    {
        return $this->data;
    }
}