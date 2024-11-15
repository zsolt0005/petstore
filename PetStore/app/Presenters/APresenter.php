<?php declare(strict_types=1);

namespace PetStore\Presenters;

use Exception;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Localization\ITranslator;

/**
 * Class APresenter
 *
 * @package PetStore\Presenters
 * @author  Zsolt Döme
 * @since   2024
 */
abstract class APresenter extends Presenter
{
    /** @var ITranslator @inject */
    public ITranslator $translator;

    /**
     * Injects the translator.
     *
     * @param ITranslator $translator
     *
     * @return void
     */
    public function injectTranslator(ITranslator $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Adds a flash message of type Info.
     *
     * @param string $message
     *
     * @return void
     */
    protected function flashMessageInfo(string $message): void
    {
        $this->flashMessage($message, 'info');
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

    /**
     *
     * @template T of IComponent
     *
     * @param string $name
     * @param class-string<T> $type
     *
     * @return T
     */
    protected function getTypedComponent(string $name, string $type): ?IComponent
    {
        try
        {
            $component = $this->getComponent($name);
            return get_class($component) === $type ? $component : null;
        }
        catch (Exception)
        {
            return null;
        }
    }
}