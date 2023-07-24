<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Exceptions;

use Exception;

/**
 * Exception thrown when the filter logic is invalid.
 */
class FilterLogicException extends Exception
{
    final public const MISSING_MODIFIER = 'Multiple values are only allowed with all() or any() modifier';

    final public const BOTH_MODIFIERS_ACTIVE = 'all() and any() modifier cannot be used together';

    final public const DUPLICATE_RESOLUTION_REQUIRED = 'Duplicate resolution required for upsert()';

    /**
     * Create a new FilterLogicException.
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
