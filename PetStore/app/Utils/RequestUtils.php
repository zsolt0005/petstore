<?php declare(strict_types=1);

namespace PetStore\Utils;

use JsonMapper;
use JsonMapper_Exception;
use Nette\Http\IRequest;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PetStore\Factories\SerializerFactory;
use Throwable;

/**
 * Class ReqeustUtils
 *
 * @package PetStore\Utils
 * @author  Zsolt Döme
 * @since   2024
 */
final class RequestUtils
{
    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Maps the request to the given type.
     *
     * @template T of object
     *
     * @param IRequest $request
     * @param class-string<T> $type
     *
     * @return T|null
     */
    public static function mapRequestToData(IRequest $request, string $type): ?object
    {
        $rawBody = $request->getRawBody();

        if($rawBody === null)
        {
            return null;
        }

        // If the content type is not provided, defaults to application/json
        return match ($request->getHeader('Content-Type'))
        {
            'application/xml' => self::mapFromXml($rawBody, $type),
            default => self::mapFromJson($rawBody, $type)
        };
    }

    /**
     * Maps the raw body as json.
     *
     * @template T of object
     *
     * @param string $rawBody
     * @param class-string<T> $type
     *
     * @return T|null
     */
    private static function mapFromJson(string $rawBody, string $type): ?object
    {
        try
        {
            /** @var object $parsedBody */
            $parsedBody = Json::decode($rawBody, false);
        }
        catch (JsonException)
        {
            return null;
        }

        $mapper = new JsonMapper();
        $mapper->undefinedPropertyHandler = static fn() => throw new JsonMapper_Exception();

        try
        {
            /** @var T $mappedObject */
            $mappedObject = $mapper->map($parsedBody, $type);

            return $mappedObject;
        }
        catch (JsonMapper_Exception $e)
        {
            return null;
        }
    }

    /**
     * Maps the raw body as xml.
     *
     * @template T of object
     *
     * @param string $rawBody
     * @param class-string<T> $type
     *
     * @return T|null
     */
    private static function mapFromXml(string $rawBody, string $type): ?object
    {
        $serializer = SerializerFactory::buildSerializer();
        try
        {
            /** @var T $mappedObject */
            $mappedObject = $serializer->deserialize($rawBody, $type, 'xml');
            return $mappedObject;
        }
        catch (Throwable)
        {
            return null;
        }
    }
}