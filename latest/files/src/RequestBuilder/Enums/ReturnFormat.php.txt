<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the return formats PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#insert
 */
enum ReturnFormat: string
{
    case NONE = '';
    case HEADERS_ONLY = 'return=headers-only';
    case REPRESENTATION = 'return=representation';
}
