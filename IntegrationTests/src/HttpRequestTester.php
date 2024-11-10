<?php declare(strict_types = 1);

namespace PetStore\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class HttpRequestTester
{
    /** @var string Base URL. */
    private const string BASE_URL = 'http://nginx';

    /** @var string HTTP request method. */
    private const string
        POST = 'POST',
        GET = 'GET';

    /** @var string Key for response code assertion. */
    private const string ASSERT_STATUS_CODE = 'assert_status_code';

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
    private function __construct(private string $method, private string $endpoint)
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
        ]);
    }

    /**
     * Setst the request method to: POST.
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
     * Setst the request method to: GET.
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
     * Sends the request and runs the assertions.
     *
     * @return void
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws RuntimeException Should never happen.
     */
    public function test(): void
    {
        $response = null;

        try
        {
            $response = $this->client->request($this->method, $this->endpoint, $this->options);
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
            TestCase::assertSame($this->asserts[self::ASSERT_STATUS_CODE], $response->getStatusCode(), $this->endpoint);
        }
    }
}
