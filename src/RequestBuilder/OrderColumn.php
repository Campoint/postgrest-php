<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder;

use PostgrestPhp\RequestBuilder\Enums\OrderDirection;
use PostgrestPhp\RequestBuilder\Enums\OrderNulls;

/**
 * A column to order by.
 *
 * @link https://postgrest.org/en/stable/references/api/tables_views.html#ordering
 */
class OrderColumn
{
    /**
     * Create a new order column.
     *
     * @param string $name The name of the column.
     * @param OrderDirection $direction The direction to order by.
     * @param OrderNulls $orderNulls The null order type to use.
     */
    public function __construct(
        public string $name,
        public OrderDirection $direction = OrderDirection::ASC,
        public OrderNulls $orderNulls = OrderNulls::NONE,
    ) {
    }
}
