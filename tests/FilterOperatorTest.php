<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\RequestBuilder\Enums\IsCheck;
use PostgrestPhp\RequestBuilder\Enums\OverlapType;
use PostgrestPhp\RequestBuilder\Exceptions\FilterLogicException;
use PostgrestPhp\RequestBuilder\Exceptions\NotUnifiedValuesException;

class FilterOperatorTest extends TestCase
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

    public function testAllAnyException(): void
    {
        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->all()
            ->any();
    }

    public function testAnyAllException(): void
    {
        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->any()
            ->all();
    }

    public function testMissingModifierException(): void
    {
        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->eq('a', 'test1', 'test2');
    }

    public function testNotUnifiedValuesException(): void
    {
        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->eq('a', 'test1', 1);
    }

    public function testEqualFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->eq('c', 0.5)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    public function testGreaterThanFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->gt('c', 0.9)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->any()
                ->gt('c', 0.9, 0.8)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(2, count($result));
    }

    public function testGreaterThanOrEqualFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->gte('c', 1.0)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->all()
                ->gte('c', 0.9, 1.0)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    public function testGreaterThanOrEqualDateFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->gte('e', '2020-01-10')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    public function testLessThanFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->lt('c', 0.2)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->any()
                ->lt('c', 0.1, 0.2)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    public function testLessThanOrEqualFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->lte('c', 0.1)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->all()
                ->lte('c', 0.1, 0.2)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    public function testNotEqualFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->not()
                ->neq('b', 1)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));
    }

    public function testLikeFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->like('a', '*t1')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->all()
                ->like('a', 't*', '*t1')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->like('a', 't*', '*t1');
    }

    public function testILikeFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->ilike('a', '*T1')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->all()
                ->ilike('a', 'T*', '*T1')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->ilike('a', 'T*', '*T1');
    }

    public function testMatchFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->match('a', 'te*')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(10, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->all()
                ->match('a', 'test', 'any')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(0, count($result));

        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->match('a', 'test', 'any');
    }

    public function testIMatchFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->imatch('a', 'Te*')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(10, count($result));

        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            return;
        }

        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->all()
                ->imatch('a', 'Te*', 'any')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(0, count($result));

        $this->expectException(FilterLogicException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->imatch('a', 'Te*', 'any');
    }

    public function testInFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->in('b', 1, 2, 3)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(3, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->in('b', 1, 2, '3');
    }

    public function testIsFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->is('d', IsCheck::FALSE)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(5, count($result));
    }

    public function testIsDistinctFilter(): void
    {
        if (in_array(getenv('POSTGREST_VERSION'), ['9', '10'], true)) {
            $this->markTestSkipped('PostgREST 9 and 10 do not support isdistinct filter.');
        }
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->isdistinct('b', 1)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(9, count($result));
    }

    public function testFullTextSearchFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'fts_test_table')
                ->select('*')
                ->fts('a', 'Matrix', 'english')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(5, count($result));
    }

    public function testPlainFullTextSearchFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'fts_test_table')
                ->select('*')
                ->plfts('a', 'The Matrix', 'english')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(5, count($result));
    }

    public function testPhraseFullTextSearchFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'fts_test_table')
                ->select('*')
                ->phfts('a', 'Terminator', 'english')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(6, count($result));
    }

    public function testWebsearchFullTextSearchFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'fts_test_table')
                ->select('*')
                ->wfts('a', 'Matrix', 'english')
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(5, count($result));
    }

    public function testContainsFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('a')
                ->cs('f', [1, 3])
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(4, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->cs('f', [1, '3']);
    }

    public function testContainedFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->cd('f', [1, 2, 3])
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(4, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->cd('f', [1, 2, '3']);
    }

    public function testArrayOverlapFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->ov('f', OverlapType::ARRAY, 1, 4, 7)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(10, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->ov('f', OverlapType::ARRAY, 1, 4, '7');
    }

    public function testRangeOverlapFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('a')
                ->ov('g', OverlapType::RANGE_UPPER_EXCLUSIVE, 1, 4)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(3, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->ov('g', OverlapType::RANGE_UPPER_EXCLUSIVE, 1, '4');
    }

    public function testStrictlyLeftOfFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->sl('g', 15, 17)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(6, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->sl('g', 15, 17.0);
    }

    public function testStrictlyRightOfFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->sr('g', -3, -1)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(10, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->sr('g', -3, -1.0);
    }

    public function testNotExtendToRightFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->nxr('g', 11, 13)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(3, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->nxr('g', 11, 13.0);
    }

    public function testNotExtendToLeftFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->nxl('g', 7, 13)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(3, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->nxl('g', 7, 13.0);
    }

    public function testAdjacentFilter(): void
    {
        $response = self::$client->run(
            self::$client
                ->from('test_schema', 'filter_test_table')
                ->select('*')
                ->adj('g', 10, 15)
        );
        $result = $response->result();
        $this->assertNotNull($result);
        $this->assertEquals(1, count($result));

        $this->expectException(NotUnifiedValuesException::class);
        self::$client
            ->from('test_schema', 'filter_test_table')
            ->select('*')
            ->adj('g', 10, 15.0);
    }
}
