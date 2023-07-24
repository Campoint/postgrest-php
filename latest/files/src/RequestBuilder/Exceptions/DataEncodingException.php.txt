<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Exceptions;

use Exception;

/**
 * Exception thrown when data can not be converted to a specified format.
 */
class DataEncodingException extends Exception
{
    final public const JSON_ENCODING_FAILED = 'Failed to encode data as JSON';

    /**
     * Create a new DataEncodingException.
     *
     * @param string $error The error message.
     */
    public function __construct(string $error)
    {
        parent::__construct($error, 0, null);
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
}
