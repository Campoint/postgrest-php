<?php

declare(strict_types=1);

namespace PostgrestPhp\RequestBuilder\Enums;

enum OperatorModifier: string
{
    case ALL = 'all';
    case ANY = 'any';
}
