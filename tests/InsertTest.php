<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\RequestBuilder\Enums\DataFormat;
use PostgrestPhp\RequestBuilder\Enums\ReturnFormat;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;

class InsertTest extends TestCase
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
                ->from('test_schema', 'insert_test_table')
                ->delete()
                ->any()
                ->eq('a', 'test1', 'test2')
        );
    }

    public function testBasicCsvInsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'insert_test_table')
                ->insert([
                    [
                        'a' => 'test1',
                        'b' => 1,
                    ],
                    [
                        'a' => 'test2',
                        'b' => 2,
                    ],
                ], dataFormat: DataFormat::CSV)
        );
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCsvInsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'insert_test_table')
                ->insert([
                    [
                        'a' => 'test1',
                        'b' => null,
                        'c' => 'foo',
                    ],
                    [
                        'a' => 'test2',
                        'b' => 2,
                    ],
                ], dataFormat: DataFormat::CSV)
        );
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testBasicJsonInsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'insert_test_table')
                ->insert([
                    [
                        'a' => 'test1',
                        'b' => 1,
                    ],
                    [
                        'a' => 'test2',
                        'b' => 2,
                    ],
                ], dataFormat: DataFormat::JSON, returnFormat: ReturnFormat::HEADERS_ONLY)
        );
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testHeadersReturnInsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'insert_test_table')
                ->insert(
                    [
                        'a' => 'test1',
                        'b' => 1,
                    ],
                    dataFormat: DataFormat::JSON,
                    returnFormat: ReturnFormat::HEADERS_ONLY
                )
        );
        $this->assertEquals('test1', $response->getLocation('a'));
        $this->assertNull($response->getLocation('b'));
    }

    public function testMissingAsDefaultInsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'insert_test_table')
                ->insert(
                    [
                        [
                            'a' => 'test1',
                            'b' => 1,
                        ],
                        [
                            'a' => 'test2',
                        ],
                    ],
                    dataFormat: DataFormat::JSON,
                    missingAsDefault: true,
                    returnFormat: ReturnFormat::REPRESENTATION,
                    columns: ['a', 'b']
                )
        );
        $result = $response->result() ?? [];
        $this->assertEquals(2, count($result));
        $this->assertEquals(69, $result[1]['b']);
    }

    public function testLimitedColumnsInsert(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'insert_test_table')
                ->insert(
                    [
                        [
                            'a' => 'test1',
                            'b' => 1,
                        ],
                        [
                            'a' => 'test2',
                            'b' => 2,
                        ],
                    ],
                    columns: ['a'],
                    dataFormat: DataFormat::JSON,
                    returnFormat: ReturnFormat::REPRESENTATION,
                )
        );
        $result = $response->result() ?? [];
        $this->assertEquals(2, count($result));
        $this->assertEquals(69, $result[0]['b']);
        $this->assertEquals(69, $result[1]['b']);

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'insert_test_table')
            ->insert(
                [
                    [
                        'a' => 'test1',
                        'b' => 1,
                    ],
                    [
                        'a' => 'test2',
                        'b' => 2,
                    ],
                ],
                columns: ['a', 2], // @phpstan-ignore-line
                dataFormat: DataFormat::JSON,
                returnFormat: ReturnFormat::REPRESENTATION,
            );
    }
}
