<?php

declare(strict_types=1);

namespace PostgrestPhp\Client\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when the authentication failed.
 */
class FailedAuthException extends Exception
{
    /**
     * Create a new FailedAuthException.
     *
     * @param null|string $reason The reason why the authentication failed.
     * @param null|Throwable $previous The previous exception used for the exception chaining.
     */
    public function __construct(?string $reason, ?Throwable $previous)
    {
        if ($reason === null) {
            parent::__construct('unknown reason', 0, $previous);
            return;
        }
        parent::__construct($reason, 0, $previous);
    }

    /**
     * String representation of the exception.
     *
     * @return string String representation of the exception.
     */
    public function __toString(): string
    {
        return __CLASS__ . ": {$this->message}\n";
    }
}
