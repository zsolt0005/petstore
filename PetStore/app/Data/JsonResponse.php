<?php declare(strict_types=1);

namespace PetStore\Data;

use Nette;
use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Utils\JsonException;

/**
 * Class JsonResponse
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class JsonResponse implements Response
{
    /**
     * Constructor.
     *
     * @param mixed $payload
     * @param int $responseCode
     * @param string $contentType
     */
    public function __construct(
        private mixed $payload,
        private int $responseCode = IResponse::S200_OK,
        private string $contentType  = 'application/json'
    )
    {
    }

    /**
     * Sends the response.
     *
     * @param IRequest $httpRequest
     * @param IResponse $httpResponse
     *
     * @return void
     * @throws JsonException
     */
    function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setContentType($this->contentType, 'utf-8');
        $httpResponse->setCode($this->responseCode);

        if($this->payload !== null)
        {
            echo Nette\Utils\Json::encode($this->payload);
        }
    }
}