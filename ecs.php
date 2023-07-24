<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([
        SetList::SPACES,
        SetList::ARRAY,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::COMMENTS,
        SetList::PSR_12,
        SetList::STRICT,
        SetList::CLEAN_CODE,
        SetList::PHPUNIT,
        SetList::SYMPLIFY,
        SetList::COMMON,
    ]);

    $ecsConfig->ruleWithConfiguration(
        GeneralPhpdocAnnotationRemoveFixer::class,
        [
            'annotations' => [
                'author', 'package', 'group', 'covers', 'category'
            ]
        ]
    );

    $ecsConfig->lineEnding("\n");
};
