<?php

declare(strict_types=1);

namespace PostgrestPhp\Response\Exceptions;

use Exception;
use React\Http\Message\ResponseException;
use Throwable;

/**
 * Exception which parses and wraps a Throwable.
 * Is thrown when the request fails.
 */
class PostgrestErrorException extends Exception
{
    private ?int $statusCode = null;

    private ?string $reasonPhrase = null;

    private ?string $responseBody = null;

    private ?string $postgrestErrorCode = null;

    private ?string $postgrestErrorMessage = null;

    /**
     * Create a new PostgrestErrorException.
     *
     * @param Throwable $previous The previous exception.
     */
    public function __construct(Throwable $previous)
    {
        if ($previous instanceof ResponseException) {
            $this->statusCode = $previous->getResponse()
                ->getStatusCode();
            $this->reasonPhrase = $previous->getResponse()
                ->getReasonPhrase();
            $this->responseBody = $previous->getResponse()
                ->getBody()
                ->getContents();
            $json = json_decode($this->responseBody, true);
            if ($json !== null) {
                $this->postgrestErrorCode = $json['code'] ?? null;
                $this->postgrestErrorMessage = $json['message'] ?? null;
            }

            if ($this->postgrestErrorCode !== null && $this->postgrestErrorMessage !== null) {
                parent::__construct(sprintf(
                    '(%s) %s',
                    $this->postgrestErrorCode,
                    $this->postgrestErrorMessage
                ), 0, $previous);
                return;
            }
            parent::__construct($previous->getMessage(), 0, $previous);
            return;
        }
        parent::__construct($previous->getMessage(), 0, $previous);
    }

    /**
     * String representation of the exception.
     *
     * @return string String representation of the exception.
     */
    public function __toString()
    {
        return __CLASS__ . ": {$this->message}\n";
    }

    /**
     * Get the response status code.
     *
     * @return int|null The response status code.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get the response reason phrase.
     *
     * @return string|null The response reason phrase.
     */
    public function getReasonPhrase(): ?string
    {
        return $this->reasonPhrase;
    }

    /**
     * Get the unparsed response body.
     *
     * @return string|null The unparsed response body.
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Get the Postgrest error code.
     *
     * @return string|null The Postgrest error code.
     */
    public function getPostgrestErrorCode(): ?string
    {
        return $this->postgrestErrorCode;
    }

    /**
     * Get the Postgrest error message.
     *
     * @return string|null The Postgrest error message.
     */
    public function getPostgrestErrorMessage(): ?string
    {
        return $this->postgrestErrorMessage;
    }
}
