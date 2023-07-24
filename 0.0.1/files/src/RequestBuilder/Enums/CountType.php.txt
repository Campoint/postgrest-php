<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the type of counts PostgREST supports.
 */
enum CountType: string
{
    /** @link https://postgrest.org/en/stable/references/api/tables_views.html#exact-count */
    case EXACT = 'count=exact';
    /** @link https://postgrest.org/en/stable/references/api/tables_views.html#planned-count */
    case PLANNED = 'count=planned';
    /** @link https://postgrest.org/en/stable/references/api/tables_views.html#estimated-count */
    case ESTIMATED = 'count=estimated';
}
