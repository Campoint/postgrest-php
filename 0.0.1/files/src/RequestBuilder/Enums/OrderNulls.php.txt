<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the null order types PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#ordering
 */
enum OrderNulls: string
{
    case NONE = '';
    case NULLS_FIRST = 'nullsfirst';
    case NULLS_LAST = 'nullslast';
}
