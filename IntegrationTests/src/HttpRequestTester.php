<?php declare(strict_types = 1);

namespace PetStore\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use JsonMapper;
use JsonMapper_Exception;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Class HttpRequestTester
 *
 * @package PetStore\Tests
 * @author  Zsolt Döme
 * @since   2024
 */
final class HttpRequestTester
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

    /** @var string Key for response code assertion. */
    private const string ASSERT_STATUS_CODE = 'assert_status_code';

    /** @var string Key for response data assertion. */
    private const string ASSERT_RESPONSE_JSON = 'assert_response_json';

    /** @var Client Guzzle client. */
    private Client $client;

    /** @var array<string, mixed> Client options. */
    private array $options = [];

    /** @var array<string, mixed> Asserts. */
    private array $asserts = [];

    /**
     * Constructor.
     *
     * @param string $method
     * @param string $endpoint
     */
    private function __construct(private readonly string $method, private readonly string $endpoint)
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
        ]);
    }

    /**
     * Sets the request method to: POST.
     *
     * @param string $endpoint
     *
     * @return self
     */
    public static function post(string $endpoint): self
    {
        return new self(self::POST, $endpoint);
    }

    /**
     * Sets the request method to: PUT.
     *
     * @param string $endpoint
     *
     * @return self
     */
    public static function put(string $endpoint): self
    {
        return new self(self::PUT, $endpoint);
    }

    /**
     * Sets the request method to: DELETE.
     *
     * @param string $endpoint
     *
     * @return self
     */
    public static function delete(string $endpoint): self
    {
        return new self(self::DELETE, $endpoint);
    }

    /**
     * Sets the request method to: GET.
     *
     * @param string $endpoint
     *
     * @return self
     */
    public static function get(string $endpoint): self
    {
        return new self(self::GET, $endpoint);
    }

    /**
     * Sets the Json body.
     *
     * @param mixed $data
     *
     * @return self
     */
    public function json(mixed $data): self
    {
        $this->options[RequestOptions::JSON] = $data;

        return $this;
    }

    /**
     * Sets the cookie needed to trigger the XDebugger in PHPStorm.
     *
     * @return self
     */
    public function useXdebug(): self
    {
        $cookieJar = CookieJar::fromArray(['XDEBUG_SESSION' => 'PHPSTORM'], 'nginx');
        $this->options['cookies'] = $cookieJar;

        return $this;
    }

    /**
     * Sets an assertion on the HTTP Response code.
     *
     * @param int $statusCode
     *
     * @return self
     */
    public function assertResponseStatusCode(int $statusCode): self
    {
        $this->asserts[self::ASSERT_STATUS_CODE] = $statusCode;

        return $this;
    }

    /**
     * Sets an assertion on the HTTP Response data and its type.
     *
     * @param class-string $type
     * @param mixed        $data
     *
     * @return self
     */
    public function assertResponseJsonData(string $type, mixed $data): self
    {
        $this->asserts[self::ASSERT_RESPONSE_JSON] = [$type, $data];

        return $this;
    }

    /**
     * Sends the request and runs the assertions.
     *
     * @return void
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws RuntimeException Should never happen.
     */
    public function test(): void
    {
        $endpoint = self::BASE_API_PATH . (str_starts_with($this->endpoint, '/') ? '' : '/') . $this->endpoint;

        $response = null;
        try
        {


            $response = $this->client->request($this->method, $endpoint, $this->options);
        }
        catch(RequestException $e)
        {
            $response = $e->getResponse();
        }
        catch(Throwable $e)
        {
            TestCase::fail($e->getMessage());
        }

        if($response === null)
        {
            TestCase::fail('Failed to retrieve response');
        }

        if(isset($this->asserts[self::ASSERT_STATUS_CODE]))
        {
            TestCase::assertSame($this->asserts[self::ASSERT_STATUS_CODE], $response->getStatusCode(), $this->method . ' ' . $endpoint);
        }

        if(!empty($this->asserts[self::ASSERT_RESPONSE_JSON]) && is_array($this->asserts[self::ASSERT_RESPONSE_JSON]))
        {
            /** @var class-string $expectedType */
            [$expectedType, $expectedData] = @$this->asserts[self::ASSERT_RESPONSE_JSON];

            $mapper = new JsonMapper();
            $mapper->undefinedPropertyHandler = static fn() => throw new JsonMapper_Exception();

            try
            {
                /** @var object $parsedBody */
                $parsedBody = Json::decode($response->getBody()->getContents(), false);
                $parsedResponse = $mapper->map($parsedBody, $expectedType);
            }
            catch (JsonMapper_Exception | JsonException $e)
            {
                TestCase::fail(
                    'Failed to decode response data to ' . $expectedType . '. Response: ' . $response->getBody()->getContents() . ' :: ' . $e->getMessage()
                );
            }

            if($expectedData !== null)
            {
                TestCase::assertEquals($expectedData, $parsedResponse);
            }
        }
    }
}
