<?php

declare(strict_types=1);

namespace PostgrestPhp\Tests\Auth;

use PHPUnit\Framework\TestCase;
use PostgrestPhp\Client\Base\ClientAuthConfig;
use PostgrestPhp\Client\Exceptions\FailedAuthException;
use PostgrestPhp\Client\PostgrestSyncClient;
use PostgrestPhp\Response\Exceptions\PostgrestErrorException;

class SyncAuthTest extends TestCase
{
    public function testCorrectAuthentication(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'password',
            ],
        );
        $client = new PostgrestSyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $client->disableAutoAuth();
        $this->assertFalse($client->isAuthenticated());
        $response = $client->auth();
        $this->assertNull($response);
        $this->assertTrue($client->isAuthenticated());
        $this->assertGreaterThanOrEqual(time() + 3595, $client->getTokenExpirationTime());
    }

    public function testWrongAuthentication(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'wrong_password',
            ],
        );
        $client = new PostgrestSyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $this->assertFalse($client->isAuthenticated());
        try {
            $client->auth();
        } catch (FailedAuthException $e) {
            $prev = $e->getPrevious();
            if ($prev instanceof PostgrestErrorException) {
                $this->assertEquals(403, $prev->getStatusCode());
                $this->assertNotEquals('', $prev->getResponseBody());
                $this->assertNotNull($prev->getPostgrestErrorCode());
                $this->assertNotNull($prev->getReasonPhrase());
                $this->assertNotNull($prev->getPostgrestErrorMessage());
                return;
            }
            $this->fail('Previous exception should be PostgrestErrorException.');
        }
    }

    public function testWrongAuthenticationAutoAuth(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'wrong_password',
            ],
        );
        $client = new PostgrestSyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);
        $client->enableAutoAuth();
        $this->expectException(FailedAuthException::class);
        $client->run($client ->from('test_schema', 'select_test_table') ->select('*'));
    }

    public function testWrongAuthenticationToken(): void
    {
        $clientAuthConfig = new ClientAuthConfig(
            authArguments: [
                'email' => 'test@acme.dev',
                'pass' => 'wrong_password',
            ],
        );
        $client = new PostgrestSyncClient('http://localhost:8080', 5, clientAuthConfig: $clientAuthConfig);

        try {
            $client->setAuthToken('wrong_token');
        } catch (FailedAuthException $e) {
            $this->assertInstanceOf(FailedAuthException::class, $e);
        }

        try {
            $client->setAuthToken('wrong./.token');
        } catch (FailedAuthException $e) {
            $this->assertInstanceOf(FailedAuthException::class, $e);
        }

        try {
            $client->setAuthToken('wrong.IiIK.token');
        } catch (FailedAuthException $e) {
            $this->assertInstanceOf(FailedAuthException::class, $e);
        }
    }
}
