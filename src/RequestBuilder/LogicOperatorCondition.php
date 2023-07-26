<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder;

use PostgrestPhp\RequestBuilder\Enums\FilterOperators;
use PostgrestPhp\RequestBuilder\Enums\LogicOperators;
use PostgrestPhp\RequestBuilder\Enums\OperatorModifier;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use Stringable;

class LogicOperatorCondition implements Stringable
{
    public function __construct(
        private string $column,
        private FilterOperators $operator,
        private string|int|float $value,
        private bool $negate = false,
        private ?OperatorModifier $modifier = null,
        private ?string $language = null,
    ) {
        if ($this->modifier && $this->language) {
            throw new FilterLogicException(FilterLogicException::INVALID_CONDITION);
        }
    }

    public function __toString()
    {
        $operator = $this->operator->value;
        $operator = $this->negate ? sprintf('%s.%s', LogicOperators::NOT->value, $operator) : $operator;
        $operator = $this->modifier ? sprintf('%s(%s)', $operator, $this->modifier->value) : $operator;
        $operator = $this->language ? sprintf('%s(%s)', $operator, $this->language) : $operator;
        return sprintf('%s.%s.%s', $this->column, $operator, strval($this->value));
    }
}
