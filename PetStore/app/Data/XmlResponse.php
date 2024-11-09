<?php declare(strict_types=1);

namespace PetStore\Data;

use Nette\Application\Response;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use PetStore\Factories\SerializerFactory;
use ReflectionClass;

/**
 * Class JsonResponse
 *
 * @package PetStore\Data
 * @author  Zsolt Döme
 * @since   2024
 */
final readonly class XmlResponse implements Response
{
    /**
     * Constructor.
     *
     * @param object|null $payload
     * @param int $responseCode
     * @param string $contentType
     */
    public function __construct(
        private ?object $payload,
        private int     $responseCode = IResponse::S200_OK,
        private string  $contentType  = 'application/xml'
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
     */
    function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setContentType($this->contentType, 'utf-8');
        $httpResponse->setCode($this->responseCode);

        $serializer = SerializerFactory::buildSerializer();
        if($this->payload !== null)
        {
            $reflectionClass = new ReflectionClass($this->payload);
            $payloadClassName = $reflectionClass->getShortName();

            echo $serializer->serialize($this->payload, 'xml', [
                'xml_root_node_name' => $payloadClassName
            ]);
        }
    }
}