<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests\Auth;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\PostgrestAsyncClient;
use PostgrestPhp\Response\Exceptions\PostgrestErrorException;

use function React\Async\await;
use Throwable;

class AsyncAuthTest extends TestCase
{
    public function testCorrectAuthentication(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'password',
            ],
        );
        $client = new PostgrestAsyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $client->disableAutoAuth();
        $this->assertFalse($client->isAuthenticated());
        await(
            $client->auth()
                ->then(
                    function () use ($client) {
                        $this->assertTrue($client->isAuthenticated());
                        $this->assertGreaterThanOrEqual(time() + 3595, $client->getTokenExpirationTime());
                    },
                    function (Throwable $e) {
                        $this->fail('Authentication should have succeeded.');
                    }
                )
        );
    }

    public function testWrongAuthentication(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'wrong_password',
            ],
        );
        $client = new PostgrestAsyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $this->assertFalse($client->isAuthenticated());
        await(
            $client->auth()
                ->then(
                    function () {
                        $this->fail('Authentication should have failed.');
                    },
                    function (Throwable $e) use ($client) {
                        $prev = $e->getPrevious();
                        if ($prev instanceof PostgrestErrorException) {
                            $this->assertEquals(403, $prev->getStatusCode());
                            $this->assertNotEquals('', $prev->getResponseBody());
                            $this->assertNotNull($prev->getPostgrestErrorCode());
                            $this->assertNotNull($prev->getReasonPhrase());
                            $this->assertNotNull($prev->getPostgrestErrorMessage());
                        } else {
                            $this->fail('Previous exception should be PostgrestErrorException.');
                        }
                        $this->assertFalse($client->isAuthenticated());
                    }
                )
        );
    }
}
