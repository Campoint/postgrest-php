<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\RequestBuilder\Enums\ReturnFormat;

class DeleteTest extends TestCase
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
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'delete_test_table')
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

    public function testBasicDelete(): void
    {
        $response = self::$client->run(self::$client->from('test_schema', 'delete_test_table')->delete());

        $result = $response->result();
        $this->assertNull($result);
    }

    public function testDeleteReturnRepresentation(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'delete_test_table')
                ->delete(ReturnFormat::REPRESENTATION)
                ->gte('b', 5)
        );

        $result = $response->result() ?? [];
        $this->assertNotEquals(null, $result);
        $this->assertEquals(5, count($result));
    }
}
