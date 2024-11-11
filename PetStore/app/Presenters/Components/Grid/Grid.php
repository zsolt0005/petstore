<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid;

use Nette\Application\UI\Control;
use PetStore\Presenters\Components\Grid\Builders\GridDataBuilder;
use PetStore\Presenters\Components\Grid\Data\GridData;

/**
 * Class Grid
 *
 * @package PetStore\Presenters\Components
 * @author  Zsolt Döme
 * @since   2024
 */
final class Grid extends Control
{
    /**
     * Constructor.
     *
     * @param GridData $data
     */
    public function __construct(private GridData $data)
    {
    }

    /**
     * Renders the component.
     *
     * @return void
     */
    public function render(): void
    {
        $template = $this->getTemplate()->setFile(__DIR__ . '/default.latte');
        $template->data = $this->data; // @phpstan-ignore-line
        $template->render();
    }

    /**
     * Sets the component data.
     *
     * @param GridData $data
     *
     * @return self
     */
    public function setData(GridData $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the component data builder.
     *
     * @return GridDataBuilder
     */
    public function getDataBuilder(): GridDataBuilder
    {
        return GridDataBuilder::from($this->data);
    }
}