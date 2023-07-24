<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestAsyncClient;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\RequestBuilder\Enums\CountType;
use PostgrestPhp\RequestBuilder\Enums\OrderDirection;
use PostgrestPhp\RequestBuilder\Enums\OrderNulls;
use PostgrestPhp\RequestBuilder\OrderColumn;
use PostgrestPhp\Response\PostgrestResponse;
use function React\Async\await;
use Throwable;

class SelectTest extends TestCase
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

    public function testBasicSyncSelect(): void
    {
        $response = self::$client->run(self::$client->from('test_schema', 'select_test_table')->select('*'));

        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(10, count($result));
        $this->assertEquals(2, count($result[0]));
        $this->assertNotEmpty($response->getHeaders());
    }

    public function testBasicAsyncSelect(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'password',
            ]
        );
        $asyncClient = new PostgrestAsyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $asyncClient->enableAutoAuth();
        await(
            $asyncClient->run($asyncClient->from('test_schema', 'select_test_table')->select('*'))
                ->then(
                    function (PostgrestResponse $response) {
                        $result = $response->result();
                        $this->assertNotNull($result);
                        $this->assertEquals(10, count($result));
                        $this->assertEquals(2, count($result[0]));
                        $this->assertNotEmpty($response->getHeaders());
                    },
                    function (Throwable $e) {
                        $this->fail($e->getMessage());
                    }
                )
        );
    }

    public function testCountSelect(): void
    {
        $response = self::$client->run(
            self::$client->from('test_schema', 'select_test_table')->select('*')->count(CountType::EXACT)->limit(2)
        );

        $this->assertEquals(0, $response->getRangeStart());
        $this->assertEquals(1, $response->getRangeEnd());
        $this->assertEquals(10, $response->getRangeTotal());
        $this->assertNotEmpty($response->getHeaders());
    }

    public function testBasicCsvSelect(): void
    {
        $query = self::$client
            ->from('test_schema', 'select_test_table')
            ->select('*')
            ->setHeader('Accept', 'text/csv');
        $this->assertEquals('text/csv', $query->getHeader('Accept'));
        $response = self::$client->run($query);
        $result = $response->result();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull($result);
        $this->assertStringContainsString('a,b', $response->rawResult());
    }

    public function testSelectColumns(): void
    {
        $response = self::$client->run(self::$client ->from('test_schema', 'select_test_table') ->select('a'));
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(10, count($result));
        $this->assertEquals(1, count($result[0]));
    }

    public function testLimitSelect(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'select_test_table')
                ->select('*')
                ->limit(5)
        );

        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(5, count($result));
    }

    public function testOffsetSelect(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'select_test_table')
                ->select('*')
                ->offset(5)
        );
        $result = $response->result() ?? [];
        $this->assertNotEquals(false, $result);
        $this->assertEquals(5, count($result));
    }

    public function testOrderBySelect(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'select_test_table')
                ->select('*')
                ->orderBy(new OrderColumn('b', OrderDirection::DESC, OrderNulls::NULLS_LAST))->limit(1)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('test9', $result[0]['a']);
    }
}
