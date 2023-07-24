<?php

declare(strict_types=1);

namespace PostgrestPhp\Client\Base;

use PostgrestPhp\Client\Exceptions\FailedAuthException;
use PostgrestPhp\RequestBuilder\PostgrestRequestBuilder;
use PostgrestPhp\Response\Exceptions\PostgrestErrorException;
use PostgrestPhp\Response\PostgrestResponse;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;
use Throwable;

/**
 * The PostgrestBaseClient holds all general functionality for the PostgREST client.
 * Both the sync and async clients extend this class.
 */
abstract class PostgrestBaseClient
{
    protected int $tokenExpirationTime = 0;

    protected ?string $authHeader = null;

    protected string $authSchemaName;

    protected string $authFunctionName;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $authArguments;

    protected bool $autoAuth;

    private bool $currentlyInReAuth = false;

    private int $autoAuthGrace;

    /**
     * Create new PostgREST client.
     *
     * @param string $baseUrl The base URI of the PostgREST server.
     * @param int $timeout The timeout in seconds for the HTTP requests.
     * @param Browser $httpClient The HTTP client.
     * @param ClientAuthConfig $clientAuthConfig Extra client configuration.
     */
    final public function __construct(
        private string $baseUrl = 'http://localhost/',
        private int $timeout = 15,
        private Browser $httpClient = new Browser(),
        ClientAuthConfig $clientAuthConfig = new ClientAuthConfig(),
    ) {
        $this->httpClient = $this->httpClient
            ->withProtocolVersion('1.1')
            ->withRejectErrorResponse(true)
            ->withBase($this->baseUrl)
            ->withTimeout($this->timeout);

        $this->authSchemaName = $clientAuthConfig->authSchemaName;
        $this->authFunctionName = $clientAuthConfig->authFunctionName;
        $this->authArguments = $clientAuthConfig->authArguments;
        $this->autoAuth = $clientAuthConfig->autoAuth;
        $this->autoAuthGrace = $clientAuthConfig->autoAuthGrace;
    }

    /**
     * Check whether the client is authenticated.
     * When autoAuth is enabled, the autoAuthGrace + timeout
     * will be subtracted from the token expiration time.
     * If autoAuth is disabled, the timeout * 2 will be subtracted.
     *
     * @return bool Whether the client is authenticated.
     */
    final public function isAuthenticated(): bool
    {
        if ($this->autoAuth) {
            return time() + $this->timeout + $this->autoAuthGrace < $this->tokenExpirationTime;
        }
        return time() + $this->timeout * 2 < $this->tokenExpirationTime;
    }

    /**
     * Function to manually set authentication token.
     * Useful when not using stored procedure for authentication.
     *
     * @param string $token The JWT token.
     * @throws FailedAuthException If the token is invalid.
     */
    final public function setAuthToken(string $token): void
    {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            throw new FailedAuthException('invalid token provided', null);
        }
        $decodedTokenPart = base64_decode($tokenParts[1], true);
        if ($decodedTokenPart === false) {
            throw new FailedAuthException('failed to base64 decode token', null);
        }
        $tokenPayload = json_decode($decodedTokenPart, true);
        if (! is_array($tokenPayload) || (is_array($tokenPayload) && ! isset($tokenPayload['exp']))) {
            throw new FailedAuthException('no key exp in token', null);
        }
        $this->tokenExpirationTime = $tokenPayload['exp'];
        $this->authHeader = sprintf('Bearer %s', $token);
    }

    /**
     * Returns the expiration time of the authentication token.
     *
     * @return int Token expiration time
     */
    final public function getTokenExpirationTime(): int
    {
        return $this->tokenExpirationTime;
    }

    /**
     * Enable auto authentication feature.
     * Will enable check token expiration with every request.
     *
     * @param int $autoAuthGrace The grace period in seconds which will be subtracted from the token expiration time.
     */
    final public function enableAutoAuth(int $autoAuthGrace = 300): void
    {
        $this->autoAuthGrace = $autoAuthGrace;
        $this->autoAuth = true;
    }

    /**
     * Disable auto authentication feature
     *
     * Will disable check token expiration with every request
     */
    final public function disableAutoAuth(): void
    {
        $this->autoAuthGrace = 0;
        $this->autoAuth = false;
    }

    /**
     * Create a new request builder for a table in a schema.
     *
     * @param string $schemaName The schema name of the table.
     * @param string $tableName The table name.
     * @return PostgrestRequestBuilder The request builder.
     */
    final public function from(string $schemaName, string $tableName): PostgrestRequestBuilder
    {
        return new PostgrestRequestBuilder($schemaName, $tableName);
    }

    /**
     * Authenticate the client using stored procedure.
     *
     * @return PromiseInterface<true> The response.
     */
    final protected function _auth(): PromiseInterface
    {
        /** @phpstan-ignore-next-line */
        return $this->_call(
            $this->authFunctionName,
            $this->authArguments ?? [],
            $this->authSchemaName,
            skipAuth: true,
        )->then(
            function (PostgrestResponse $response) {
                if (
                    $response->result() === null
                    || ! isset($response->result()['token'])
                ) {
                    throw new FailedAuthException($response->rawResult(), null);
                }
                $token = $response->result()['token'];
                $this->setAuthToken($token);
                return true;
            },
            function (Throwable $e) {
                throw new FailedAuthException($e->getMessage(), $e);
            }
        );
    }

    /**
     * Call a stored procedure.
     *
     * @param string $functionName The function name.
     * @param array<string, mixed> $params The parameters for the function.
     * @param string $schemaName The schema name of the function.
     * @return PromiseInterface<PostgrestResponse> The response.
     */
    final protected function _call(
        string $functionName,
        array $params = [],
        string $schemaName = 'public',
        bool $skipAuth = false
    ): PromiseInterface {
        return $this->_run(
            $this->from($schemaName, sprintf('/rpc/%s', $functionName))
                ->insert($params),
            $skipAuth
        );
    }

    /**
     * Run a request.
     *
     * @param PostgrestRequestBuilder $requestBuilder The request builder.
     * @param bool $skipAuth Whether to skip authentication (only applied when autoAuth enabled).
     * @return PromiseInterface<PostgrestResponse> The response.
     */
    final protected function _run(PostgrestRequestBuilder $requestBuilder, bool $skipAuth = false): PromiseInterface
    {
        [$method, $url, $headers, $body] = $requestBuilder->getRequestData();
        return $this->_execute($method, $url, $headers, $body, $skipAuth);
    }

    /**
     * Execute a request.
     *
     * @param string $method The HTTP method.
     * @param string $url The URL.
     * @param array<array<string, mixed>> $headers Additional request options.
     * @param bool $skipAuth Whether to skip authentication (only applied when autoAuth enabled).
     * @return PromiseInterface<PostgrestResponse> The response.
     */
    final protected function _execute(
        string $method,
        string $url,
        array $headers,
        string $body,
        bool $skipAuth = false
    ): PromiseInterface {
        $promise = $this->createRequestPromise($method, $url, $headers, $body, $skipAuth);
        return $promise->then(
            function (ResponseInterface $response) {
                return new PostgrestResponse($response);
            },
            function (Throwable $e) {
                if (! $e instanceof FailedAuthException) {
                    throw new PostgrestErrorException($e);
                }
                throw $e;
            }
        );
    }

    /**
     * Sends the request and optionally prepends the authentication.
     *
     * @param string $method The HTTP method.
     * @param string $url The URL.
     * @param array<array<string, mixed>> $headers Additional request options.
     * @param bool $skipAuth Whether to skip authentication (only applied when autoAuth enabled).
     * @return PromiseInterface<ResponseInterface> The response.
     */
    private function createRequestPromise(
        string $method,
        string $url,
        array $headers,
        string $body,
        bool $skipAuth = false
    ): PromiseInterface {
        if (! $this->isAuthenticated() && $this->autoAuth && ! $skipAuth && ! $this->currentlyInReAuth) {
            $this->currentlyInReAuth = true;
            return $this->_auth()
                ->then(
                    function () use ($method, $url, $headers, $body) {
                        $this->currentlyInReAuth = false;
                        if ($this->authHeader !== null) {
                            $headers['Authorization'] = $this->authHeader;
                        }
                        return $this->httpClient->request($method, $url, $headers, $body);
                    },
                    function (Throwable $e) {
                        $this->currentlyInReAuth = false;
                        throw $e;
                    }
                );
        }
        if ($this->authHeader !== null) {
            $headers['Authorization'] = $this->authHeader;
        }
        return $this->httpClient->request($method, $url, $headers, $body);
    }
}
