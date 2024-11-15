<?php declare(strict_types=1);

namespace PetStore\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use JsonException;
use JsonMapper;
use JsonMapper_Exception;
use Nette\Http\FileUpload;
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
    public static function createInstance(): self
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
    public function getAll(): array
    {
        return $this->makeRequestAndParseArrayResponse(Pet::class, self::GET, '/pet');
    }

    /**
     * Gets a pet by its ID.
     *
     * @param int $petId
     *
     * @return Pet
     *
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    public function getById(int $petId): Pet
    {
        return $this->makeRequestAndParseObjectResponse(Pet::class, self::GET, '/pet/' . $petId);
    }

    /**
     * Gets all the pets by status.
     *
     * @return Pet[]
     *
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    public function findByStatus(string $status): array
    {
        return $this->makeRequestAndParseArrayResponse(Pet::class, self::GET, '/pet/findByStatus?status=' . $status);
    }

    /**
     * Gets all the pets by tags.
     *
     * @return Pet[]
     *
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    public function findByTags(string $tags): array
    {
        return $this->makeRequestAndParseArrayResponse(Pet::class, self::GET, '/pet/findByTags?tags=' . $tags);
    }

    /**
     * Deletes a pet.
     *
     * @param int $petId
     *
     * @return void
     * @throws SDKRequestException When the request fails
     */
    public function deleteById(int $petId): void
    {
        $this->makeRequest(self::DELETE, '/pet/' . $petId);
    }

    /**
     * Creates a new pet.
     *
     * @param Pet $pet
     *
     * @return Pet
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    public function create(Pet $pet): Pet
    {
        return $this->makeRequestAndParseObjectResponse(Pet::class, self::POST, '/pet', [
            RequestOptions::JSON => $pet
        ]);
    }

    /**
     * Updates a pet.
     *
     * @param Pet $updatePetData
     *
     * @return Pet
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    public function update(Pet $updatePetData): Pet
    {
        return $this->makeRequestAndParseObjectResponse(Pet::class, self::PUT, '/pet', [
            RequestOptions::JSON => $updatePetData
        ]);
    }

    /**
     * Upload images to a pet.
     *
     * @param Pet $pet
     * @param FileUpload[] $files
     *
     * @return void
     * @throws SDKRequestException
     */
    public function uploadImages(Pet $pet, array $files): void
    {
        $filesToUpload = [];
        foreach ($files as $file)
        {
            $filesToUpload[] = [
                'name'     => $file->getUntrustedName(),
                'contents' => $file->getContents(),
                'filename' => $file->getUntrustedName()
            ];
        }

        $this->makeRequest(self::POST, 'pet/' . $pet->id . '/uploadImage', [
            RequestOptions::MULTIPART => $filesToUpload
        ]);
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
     * Makes a request, parses the response and returns it.
     *
     * @template T of object
     *
     * @param class-string<T> $type
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     *
     * @return T
     *
     * @throws SDKRequestException When the request fails
     * @throws InvalidArgumentException When the parsing fails
     */
    private function makeRequestAndParseObjectResponse(string $type, string $method, string $endpoint, array $options = []): object
    {
        $mapper = new JsonMapper();
        $mapper->undefinedPropertyHandler = static fn() => throw new JsonMapper_Exception();

        $response = $this->makeRequest($method, $endpoint, $options);

        try
        {
            /** @var object $parsedObject */
            $parsedObject = Json::decode($response->getBody()->getContents(), false);

            /** @var T $mappedObject */
            $mappedObject = $mapper->map($parsedObject, $type);

            return $mappedObject;
        }
        catch (JsonMapper_Exception | JsonException | RuntimeException $e)
        {
            throw new InvalidArgumentException('Failed to parse as ' . $type . ' with message: ' . $e->getMessage());
        }
    }

    /**
     * Creates a request and returns the response. Handles errors and returns accordingly.
     *
     * @param string $method
     * @param string $endpoint
     * @param array<string, mixed> $options
     *
     * @return ResponseInterface
     * @throws SDKRequestException
     */
    private function makeRequest(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        $endpointToCall = self::BASE_API_PATH . (str_starts_with($endpoint, '/') ? '' : '/') . $endpoint;

        try
        {
            $response = $this->client->request($method, $endpointToCall, $options);
        }
        catch(RequestException $e)
        {
            $response =  $e->getResponse();
        }
        catch(Throwable $e)
        {
            throw new SDKRequestException($e->getCode());
        }

        if($response?->getStatusCode() !== 200)
        {
            throw new SDKRequestException($response?->getStatusCode() ?? 0);
        }

        return $response;
    }
}