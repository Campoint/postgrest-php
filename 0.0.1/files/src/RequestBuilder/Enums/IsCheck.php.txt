<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the is check types PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
 */
enum IsCheck: string
{
    case NULL = 'null';
    case TRUE = 'true';
    case FALSE = 'false';
    case UNKNOWN = 'unknown';
}
