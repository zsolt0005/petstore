<?php declare(strict_types=1);

namespace PetStore\Presenters\Error\Error4xx;

use Nette\Application\Attributes\Requires;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;

/**
 * Handles 4xx HTTP error responses.
 */
#[Requires(methods: '*', forward: true)]
final class Error4xxPresenter extends Presenter
{
    /**
     * @param BadRequestException $exception
     *
     * @return void
     */
	public function renderDefault(BadRequestException $exception): void
	{
		// renders the appropriate error template based on the HTTP status code
		$code = $exception->getCode();
		$file = is_file($file = __DIR__ . "/$code.latte")
			? $file
			: __DIR__ . '/4xx.latte';
		$this->template->httpCode = $code;
		$this->template->setFile($file); // @phpstan-ignore-line
	}
}
