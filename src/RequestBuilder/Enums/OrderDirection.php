<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the order directions PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#ordering
 */
enum OrderDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
