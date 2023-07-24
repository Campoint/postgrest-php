<?php

declare(strict_types=1);

namespace PostgrestPhp\Client\Base;

/**
 * Configuration for PostgREST client authentication.
 * Contains various authentication settings, like credentials.
 */
class ClientAuthConfig
{
    /**
     * Create a new PostgREST client auth config.
     *
     * @param string $authSchemaName The schema name to use for authentication.
     * @param string $authFunctionName The function name to use for authentication.
     * @param null|array<string, mixed> $authArguments The arguments passed to the login function.
     * @param bool $autoAuth Whether to ensure authentication before every request.
     * @param int $autoAuthGrace The grace period in seconds before the authentication token expires.
     */
    public function __construct(
        public string $authSchemaName = 'public',
        public string $authFunctionName = 'login',
        public ?array $authArguments = null,
        public bool $autoAuth = false,
        public int $autoAuthGrace = 300,
    ) {
    }
}
