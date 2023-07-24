Client
======

postgrest-php provides two clients to interact with PostgreSQL, a
synchronous and an asynchronous one. Both clients implement the client
interface and the main difference between them, is the return type when
executing request against PostgREST.

.. contents::
    :local:

Configuration
-------------

Both clients are mainly configured over their constructor method which
they share over a base class which both clients extend.

General
~~~~~~~

To instantiate the client you will need to provide the URL for the
PostgREST server. Additionally to the URL, you can pass a custom timeout
value. Please mind, that even if you pass a custom ``Browser`` object to
the constructor, the ``baseUrl`` and ``timeout`` parameters will be
overwritten.

.. code:: php

    // specify baseUrl and timeout
    $client = new PostgrestSyncClient(
        'http://localhost:8080',
        5,
        // baseUrl and timeout configuration will be overwritten
        (new Browser(null, $loop)->withBase('https://foo.bar')->withTimeout(10))
    );

Authentication
~~~~~~~~~~~~~~

You have two ways to authenticate the client, you can either provide a
JWT token yourself, using ``setAuthToken`` on the client, or give the
client the needed credentials to get a token from PostgREST. To provide
the client with credentials, use the ``ClientAuthConfig`` object.
:php:method:`PostgrestPhp\Client\Base\PostgrestBaseClient::setAuthToken()`

.. code:: php

    // provide the token yourself
    $token = '<YOUR_JWT_TOKEN_HERE>';
    $client = new PostgrestSyncClient('http://localhost:8080', 5);
    $client->setAuthToken($token);
    $client->run(...);

.. code:: php

    // pass credentials
    $clientAuthConfig = new ClientAuthConfig(
        authArguments: [
            'email' => 'test@acme.dev',
            'pass' => 'password',
        ],
    );
    // pass auth config to client
    $client = new PostgrestSyncClient(
        'http://localhost:8080',
        5,
        clientAuthConfig: $clientAuthConfig
    );
    $client->auth();
    $client->run(...);

When providing credentials for authentication you can choose to enable
the auto authentication feature using the according parameter in the
``ClientAuthConfig`` constructor. This feature will ensure before every
request that the client is still authenticated, if not,
re-authentication will be triggered automatically. When enabling this
feature you must also set a grace time period (if you do not want to use
the default of 300) which will be subtracted from the token expiration
time to ensure that the token gets renewed before the old one expires.

.. code:: php

    // enable auto authentication feature
    $clientAuthConfig = new ClientAuthConfig(
        authArguments: [
            'email' => 'test@acme.dev',
            'pass' => 'password',
        ],
        autoAuth: true,
        autoAuthGrace: 120
    );
    $client = new PostgrestSyncClient(
        'http://localhost:8080',
        5,
        clientAuthConfig: $clientAuthConfig
    );
    $client->run(...);

.. code:: php

    $clientAuthConfig = new ClientAuthConfig(
        authArguments: [
            'email' => 'test@acme.dev',
            'pass' => 'password',
        ],
    );
    $client = new PostgrestSyncClient(
        'http://localhost:8080',
        5,
        clientAuthConfig: $clientAuthConfig
    );
    // enable auto authentication feature
    $client->enableAutoAuth(autoAuthGrace: 120);
    $client->run(...);
    // disable auto authentication feature
    $client->disableAutoAuth();

When providing credentials for client authentication, you can
additionally choose the name for the stored procedure which is
responsible for authentication and the schema name in which the stored
procedure resides.

.. code:: php

    // specify stored procedure name and schema
    $clientAuthConfig = new ClientAuthConfig(
        authArguments: [
            'email' => 'test@acme.dev',
            'pass' => 'password',
        ],
        authSchemaName: 'custom_schema',
        authFunctionName: 'custom_login'
    );
    $client = new PostgrestSyncClient(
        'http://localhost:8080',
        5,
        clientAuthConfig: $clientAuthConfig
    );
    $client->auth();
    $client->run(...);

Usage
-----

Once you have instantiated a client, there are three ways to use the
client: start building a request, run a stored procedure or run a query.

.. code:: php

    // start building a query
    $query = $client->from('schema_name', 'table_name')
        ->select('*')
        ->eq('foo', 'bar');

.. code:: php

    // run a query
    $response = $client->run($query);

.. code:: php

    // call a stored procedure
    $response = $client->call(
        'my_function',
        [
            'arg1' => 'foo',
            'arg2' => 'bar'
        ]
    );

Async
~~~~~

The async client will return a ``PromiseInterface`` instead of a
``PostgrestResponse`` when calling the ``run()`` and ``call()`` methods.
Also, the ``auth()`` method will return a ``PromiseInterface`` instead
of ``null`` or throwing a ``FailedAuthException``. You can use these
promises as any other ReactPHP ``PromiseInterface`` in your code.

.. code:: php

    $asyncClient->auth()->then(
        function () {
            // auth succeeded
        },
        function (Throwable $e) {
            // auth failed
            // Should always be FailedAuthException
        }
    );

.. code:: php

    $asyncClient->run($query)->then(
        function (PostgrestResponse $response) {
            // Handle returned data
        },
        function (Throwable $e) {
            // Handle error
            // FailedAuthException only possible with $autoAuth enabled
            // PostgrestErrorException wraps all other exceptions
        }
    );

Exceptions
----------

There are two exceptions which can be thrown upon an error when running
a query. The first one, ``FailedAuthException`` will be thrown when the
authentication process fails. The ``run()`` method will only throw the
``FailedAuthException``, if you use the ``autoAuth`` feature. The
``auth()`` and ``setAuthToken()`` methods throw the
``FailedAuthException`` when the request to PostgREST or parsing the
token fails. The other exception is ``PostgrestErrorException``, which
wraps all ``Exceptions`` thrown by ``Browser::request()`` and parses the
PostgREST error when one is returned. ``PostgrestErrorException`` tries
to parse the response body (if there is one) and extracts the PostgREST
error message and code. If no PostgREST error message and code are
found, the response status code and body will be used as the exception
message. If the the previous ``Exception`` has no response, the message
of the ``Exception`` will be used as the message for the
``PostgrestErrorException``.
