<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\RequestBuilder\Enums\DuplicateResolution;
use PostgrestPhp\RequestBuilder\Enums\ReturnFormat;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;

class UpsertTest extends TestCase
{
    private static PostgrestSyncClient $client;

    public static function setUpBeforeClass(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'password',
            ],
        );
        self::$client = new PostgrestSyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        self::$client->auth();
    }

    protected function tearDown(): void
    {
        self::$client->run(
            self::$client
                ->from('test_schema', 'upsert_test_table')
                ->upsert(
                    [
                        [
                            'a' => 'test1',
                            'b' => 1,
                        ],
                        [
                            'a' => 'test2',
                            'b' => 2,
                        ],
                        [
                            'a' => 'test3',
                            'b' => 3,
                        ],
                        [
                            'a' => 'test4',
                            'b' => 4,
                        ],
                        [
                            'a' => 'test5',
                            'b' => 5,
                        ],
                        [
                            'a' => 'test6',
                            'b' => 6,
                        ],
                        [
                            'a' => 'test7',
                            'b' => 7,
                        ],
                        [
                            'a' => 'test8',
                            'b' => 8,
                        ],
                        [
                            'a' => 'test9',
                            'b' => 9,
                        ],
                        [
                            'a' => 'test0',
                            'b' => 0,
                        ],
                    ]
                )
        );
    }

    public function testIgnoreUpsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'upsert_test_table')
                ->upsert(
                    [
                        [
                            'a' => 'test0',
                            'b' => 10,
                        ],
                    ],
                    returnFormat: ReturnFormat::REPRESENTATION,
                    duplicateResolution: DuplicateResolution::IGNORE
                )
        );
        $result = $response->result() ?? [];
        $this->assertEquals(0, count($result));
    }

    public function testMergeUpsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'upsert_test_table')
                ->upsert(
                    [
                        [
                            'a' => 'test0',
                            'b' => 10,
                        ],
                    ],
                    returnFormat: ReturnFormat::REPRESENTATION,
                    duplicateResolution: DuplicateResolution::MERGE
                )
        );
        $result = $response->result() ?? [];
        $this->assertEquals(1, count($result));
        $this->assertEquals('test0', $result[0]['a']);
    }

    public function testOnConflictUpsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'upsert_onconflict_test_table')
                ->upsert(
                    [
                        [
                            'a' => 'test0',
                            'b' => 10,
                        ],
                    ],
                    returnFormat: ReturnFormat::REPRESENTATION,
                    duplicateResolution: DuplicateResolution::MERGE,
                    onConflict: ['a']
                )
        );
        $result = $response->result() ?? [];
        $this->assertEquals(1, count($result));
        $this->assertEquals(10, $result[0]['b']);

        $this->expectException(NotUnifiedValuesException::class);
        self::$client->run(
            self::$client
                ->from('test_schema', 'upsert_test_table')
                ->upsert(
                    [
                        [
                            'a' => 'test0',
                            'b' => 10,
                        ],
                    ],
                    returnFormat: ReturnFormat::REPRESENTATION,
                    onConflict: ['a', 1], // @phpstan-ignore-line
                )
        );
    }

    public function testNoneUpsert(): void
    {
        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'upsert_test_table')
            ->upsert([], duplicateResolution: DuplicateResolution::NONE);
    }
}
