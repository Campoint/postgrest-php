<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\RequestBuilder\Enums\ReturnFormat;

class UpdateTest extends TestCase
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
                ->from('test_schema', 'update_test_table')
                ->update([
                    'b' => 11,
                ])
                ->eq('a', 'test1')
        );
        $result = $response->result();
        $this->assertNull($result);
    }

    public function testBasicUpdate(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'update_test_table')
                ->update([
                    'b' => 11,
                ])
                ->eq('a', 'test1')
        );
        $result = $response->result();
        $this->assertNull($result);
    }

    public function testUpdateReturnRepresentation(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'update_test_table')
                ->update([
                    'b' => 11,
                ], ReturnFormat::REPRESENTATION)
                ->eq('a', 'test1')
        );
        $result = $response->result() ?? [];
        $this->assertNotEquals(null, $result);
        $this->assertEquals(1, count($result));
        $this->assertEquals(11, $result[0]['b']);
    }
}
