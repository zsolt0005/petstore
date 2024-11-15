<?php declare(strict_types=1);

namespace PetStore\Presenters;

use Nette\Application\UI\Presenter;
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
     * @param array<string, mixed> $params
     *
     * @return void
     */
    protected function flashMessageInfo(string $message, array $params = []): void
    {
        $this->flashMessage($this->translator->translate($message, $params), 'info');
    }

    /**
     * Adds a flash message of type Warning.
     *
     * @param string $message
     * @param array<string, mixed> $params
     *
     * @return void
     */
    protected function flashMessageWarning(string $message, array $params = []): void
    {
        $this->flashMessage($this->translator->translate($message, $params), 'warning');
    }

    /**
     * Adds a flash message of type Error.
     *
     * @param string $message
     * @param array<string, mixed> $params
     *
     * @return void
     */
    protected function flashMessageError(string $message, array $params = []): void
    {
        $this->flashMessage($this->translator->translate($message, $params), 'danger');
    }
}