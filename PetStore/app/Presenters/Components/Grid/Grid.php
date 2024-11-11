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
     * @param GridData $gridData
     *
     * @return void
     */
    public function render(GridData $gridData): void
    {
        $template = $this->getTemplate()->setFile(__DIR__ . '/default.latte');
        $template->data = $gridData;
        $template->render();
    }
}