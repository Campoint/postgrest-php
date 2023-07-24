<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the duplicate resolution strategies PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#upsert
 */
enum DuplicateResolution: string
{
    case NONE = '';
    case IGNORE = 'resolution=ignore-duplicates';
    case MERGE = 'resolution=merge-duplicates';
}
