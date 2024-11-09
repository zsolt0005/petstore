<?php declare(strict_types=1);

namespace PetStore\Presenters\Error\Error5xx;

use Nette\Application\Attributes\Requires;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\ILogger;

/**
 * Handles uncaught exceptions and errors, and logs them.
 */
#[Requires(forward: true)]
final readonly class Error5xxPresenter implements IPresenter
{
    /**
     * Constructor.
     *
     * @param ILogger $logger
     */
	public function __construct(private ILogger $logger)
    {
	}

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function run(Request $request): Response
	{
		// Log the exception
		$exception = $request->getParameter('exception');
		$this->logger->log($exception, ILogger::EXCEPTION);

		// Display a generic error message to the user
		return new CallbackResponse(function (IRequest $httpRequest, IResponse $httpResponse): void {
			if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/500.phtml';
			}
		});
	}
}
