<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the overlap types PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
 * @link https://www.postgresql.org/docs/current/rangetypes.html
 */
enum OverlapType: string
{
    case ARRAY = '{}';
    case RANGE_ALL_EXCLUSIVE = '()';
    case RANGE_LOWER_EXCLUSIVE = '(]';
    case RANGE_UPPER_EXCLUSIVE = '[)';
    case RANGE_ALL_INCLUSIVE = '[]';
}
