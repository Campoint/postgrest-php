<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Exceptions;

use Exception;

/**
 * Exception thrown when not all values are unified.
 * Unified means that all values are of the same type.
 */
class NotUnifiedValuesException extends Exception
{
    final public const NOT_UNIFIED_ARRAY = 'All array values must be of same type';

    final public const NOT_UNIFIED_START_END = '$start and $end parameters must be of same type';

    /**
     * Create a new NotUnifiedValuesException.
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
