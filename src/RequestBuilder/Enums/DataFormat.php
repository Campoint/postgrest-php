<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the data format PostgREST supports for transmitting data.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#bulk-insert
 */
enum DataFormat: string
{
    case JSON = 'json';
    case CSV = 'csv';
}
