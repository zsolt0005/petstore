<?php declare(strict_types=1);

namespace PetStore\Presenters;

use Nette\Application\UI\Presenter;

/**
 * Class APresenter
 *
 * @package PetStore\Presenters
 * @author  Zsolt Döme
 * @since   2024
 */
abstract class APresenter extends Presenter
{
    /**
     * Adds a flash message of type Info.
     *
     * @param string $message
     *
     * @return void
     */
    protected function flashMessageInfo(string $message): void
    {
        $this->flashMessage($message, 'light');
    }

    /**
     * Adds a flash message of type Warning.
     *
     * @param string $message
     *
     * @return void
     */
    protected function flashMessageWarning(string $message): void
    {
        $this->flashMessage($message, 'warning');
    }

    /**
     * Adds a flash message of type Error.
     *
     * @param string $message
     *
     * @return void
     */
    protected function flashMessageError(string $message): void
    {
        $this->flashMessage($message, 'danger');
    }
}