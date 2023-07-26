<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

enum LogicOperators: string
{
    case NOT = 'not';
    case OR = 'or';
    case AND = 'and';
}
