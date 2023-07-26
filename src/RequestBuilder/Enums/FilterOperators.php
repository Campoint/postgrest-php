<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

/**
 * Enum for the operators PostgREST supports.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#operators
 */
enum FilterOperators: string
{
    case EQUAL = 'eq';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUAL = 'lte';
    case NOT_EQUAL = 'neq';
    case LIKE = 'like';
    case ILIKE = 'ilike';
    case MATCH = 'match';
    case IMATCH = 'imatch';
    case IN = 'in';
    case IS = 'is';
    case IS_DISTINCT_FROM = 'isdistinct';
    case FULL_TEXT_SEARCH = 'fts';
    case PLAIN_FULL_TEXT_SEARCH = 'plfts';
    case PHRASE_FULL_TEXT_SEARCH = 'phfts';
    case WEBSEARCH_FULL_TEXT_SEARCH = 'wfts';
    case CONTAINS = 'cs';
    case CONTAINED_IN = 'cd';
    case OVERLAP = 'ov';
    case STRICTLY_LEFT_OF = 'sl';
    case STRICTLY_RIGHT_OF = 'sr';
    case NOT_EXTEND_TO_RIGHT = 'nxr';
    case NOT_EXTEND_TO_LEFT = 'nxl';
    case ADJACENT = 'adj';
}
