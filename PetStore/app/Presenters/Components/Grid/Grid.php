<?php declare(strict_types=1);

namespace PetStore\Presenters\Components\Grid;

use Nette\Application\UI\Control;
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
     * Renders the component.
     *
     * @param GridData $data
     *
     * @return void
     */
    public function render(GridData $data): void
    {
        $template = $this->getTemplate()->setFile(__DIR__ . '/default.latte');
        $template->data = $data; // @phpstan-ignore-line
        $template->render();
    }
}