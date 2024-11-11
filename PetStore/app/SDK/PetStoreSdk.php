<?php declare(strict_types=1);

namespace PetStore\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use JsonException;
use JsonMapper;
use JsonMapper_Exception;
use Nette\Utils\Json;
use PetStore\Data\Pet;
use PetStore\SDK\Exceptions\RequestException as SDKRequestException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

/**
 * Class PetStoreSdk
 *
 * @package PetStore\SDK
 * @author  Zsolt Döme
 * @since   2024
 */
final class PetStoreSdk
{
    /** @var string Base URL. */
    private const string BASE_URL = 'http://nginx';

    /** @var string Base API PATH. */
    private const string BASE_API_PATH = '/api/v1';

    /** @var string HTTP request method. */
    private const string
        POST = 'POST',
        PUT = 'PUT',
        DELETE = 'DELETE',
        GET = 'GET';

    /** @var Client Guzzle client. */
    private readonly Client $client;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
        ]);
    }

    /**
     * Factory method.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Gets all the pets.
     *
     * @return Pet[]
     *
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    public function getAllPets(): array
    {
        return $this->makeRequestAndParseArrayResponse(Pet::class, self::GET, '/pet');
    }

    /**
     * Makes a request, parses the response and returns it.
     *
     * @template T
     *
     * @param class-string<T> $itemsType
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     *
     * @return T[]
     *
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    private function makeRequestAndParseArrayResponse(string $itemsType, string $method, string $endpoint, array $options = []): array
    {
        $mapper = new JsonMapper();
        $mapper->undefinedPropertyHandler = static fn() => throw new JsonMapper_Exception();

        $response = $this->makeRequest($method, $endpoint, $options);
        if($response?->getStatusCode() !== 200)
        {
            throw new SDKRequestException($response?->getStatusCode() ?? 0);
        }

        try
        {
            /** @var array<object> $parsedArray */
            $parsedArray = Json::decode($response->getBody()->getContents(), false);

            foreach ($parsedArray as $key => $parsedArrayItem)
            {
                $parsedItem = $mapper->map($parsedArrayItem, $itemsType);

                $parsedArray[$key] = $parsedItem;
            }
        }
        catch (JsonMapper_Exception | JsonException | RuntimeException $e)
        {
            throw new InvalidArgumentException('Failed to parse as ' . $itemsType . ' with message: ' . $e->getMessage());
        }

        /** @var array<T> $parsedArray */
        return $parsedArray;
    }

    /**
     * Creates a request and returns the response. Handles errors and returns accordingly.
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     *
     * @return ResponseInterface|null
     */
    private function makeRequest(string $method, string $endpoint, array $options = []): ?ResponseInterface
    {
        $endpointToCall = self::BASE_API_PATH . (str_starts_with($endpoint, '/') ? '' : '/') . $endpoint;

        try
        {
            return $this->client->request($method, $endpointToCall, $options);
        }
        catch(RequestException $e)
        {
            return $e->getResponse();
        }
        catch(Throwable $e)
        {
            return null;
        }
    }
}