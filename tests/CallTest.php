<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestAsyncClient;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\Response\Exceptions\PostgrestErrorException;

use function React\Async\await;

class CallTest extends TestCase
{
    public function testSyncWrongCall(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'password',
            ],
        );
        $client = new PostgrestSyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $client->auth();
        $this->expectException(PostgrestErrorException::class);
        $client->call('non_existing_function', [
            'test' => 'test',
        ]);
    }

    public function testAsyncWrongCall(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'password',
            ],
            autoAuth: true,
        );
        $client = new PostgrestAsyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);

        $this->expectException(PostgrestErrorException::class);
        await($client->call('non_existing_function', [
            'test' => 'test',
        ]));
    }
}
