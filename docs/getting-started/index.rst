Getting started
===============

PostgREST client for PHP. This library provides a synchronous and
asynchronous interface to PostgREST.

.. contents::

Installation
------------

Requirements
~~~~~~~~~~~~

-  PHP >= 8.1
-  react/http >= 1.5
-  react/async >= 4.0
-  PostgreSQL >= 12
-  PostgREST >= 9

Instructions
~~~~~~~~~~~~

::

    composer require campoint/postgrest-php

Basic usage
-----------

Create a client
~~~~~~~~~~~~~~~

This library provides both an async and sync client. Under the hood we
use ReactPHP to dispatch requests and the only difference between the
sync and async client is, that the sync client calls ``await()`` on the
``Promise`` for you. You can optionally pass a configured ``Browser``
object to the client, but the ``baseUrl`` and ``timeout`` parameters
will be overwritten.

Sync
^^^^

Create a client for synchronous environments:

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
    try {
        $client->auth();
    } catch (FailedAuthException $e) {
       // do something
    }

Async
^^^^^

Create a client for asynchronous environments:

.. code:: php

    $clientAuthConfig = new ClientAuthConfig(
        authArguments: [
            'email' => 'test@acme.dev',
            'pass' => 'password',
        ],
    );
    $client = new PostgrestAsyncClient(
        'http://localhost:8080',
        5,
        (new Browser(null, $loop)),
        $clientAuthConfig
    );
    $client->auth()->then(
        function () {
            // do something on success
        },
        function (FailedAuthException $e) {
            // do something on rejection
        }
    );

Select
~~~~~~

Select data from any table, in any schema and apply arbitrary filters:

.. code:: php

    $response = $client->run(
        $client->from('schema_name', 'table_name')
            ->select('column_a', 'column_b')
            ->eq('column_c', 'foo')
            ->gt('column_d', 0.5)
            ->in('column_e', 1, 2, 3)
    );

Insert
~~~~~~

Insert data into any table, in any schema:

.. code:: php

    $response = $client->run(
        $client->from('schema_name', 'table_name')
            ->insert(
                [
                    [
                        'column_a' => 'foo'
                    ],
                    [
                        'column_a' => 'bar'
                    ]
                ]
            )
    );

Upsert
~~~~~~

Upsert data into any table, in any schema:

.. code:: php

    $response = $client->run(
        $client->from('schema_name', 'table_name')
            ->upsert(
                [
                    [
                        'column_a' => 'foo'
                    ],
                    [
                        'column_a' => 'bar'
                    ]
                ],
                duplicateResolution: DuplicateResolution::MERGE
            )
    );

Update
~~~~~~

Update any row in any table, in any schema with arbitrary filters:

.. code:: php

    $response = $client->run(
        $client->from('schema_name', 'table_name')
            ->update(['column_a' => 'foo'])
            ->eq('column_a', 'bar')
    );

Delete
~~~~~~

Delete any row in any table, in any schema with arbitrary filters:

.. code:: php

    $response = $client->run(
        $client->from('schema_name', 'table_name')
            ->delete()
            ->eq('column_a', 'bar')
    );

Call stored procedure
~~~~~~~~~~~~~~~~~~~~~

Call any stored procedure with arbitrary arguments:

.. code:: php

    $response = $client->call(
        'foobar',
        [
            'arg1' => 'foo',
            'arg2' => 'bar'
        ],
        'schema_name'
    );

Advanced usage
--------------

If you need further documentation on how to use this library, refer to
the documentation located `here <campoint.github.io/postgrest-php/latest>`.

Creating an issue
-----------------

When encountering a bug with this library, feel free to open a new
issue. To improve the understanding of your problem, you should fork
this repository and append a new failing test case which represents the
bug. If needed, create new testing databases in the
``testing_db/initdb`` path. Reference your new test in the issue. Issues
which report bugs but have no test cases attached to it, will be
probably ignored. Please also supply the used PostgREST and PostgreSQL
versions to bug reports, to ease the task of reproducing your issue.
Create feature request issues only if you have the intent to implement
them yourself.

Local development & testing
---------------------------

When developing or testing the client, you can use the pre-configured
``docker-compose`` environment to run both PostgreSQL and PostgREST. The
``docker-compose.yml`` file contains the services to start PostgreSQL
versions 12 to 15 and PostgREST at version 9 to 11. To start the local
environment, simply run:

::

    docker-compose up postgresql14 postgrest11

Once the environment started, you can access PostgREST at port ``8080``
and PostgreSQL at port ``5432``.

Local development
~~~~~~~~~~~~~~~~~

The repository provides a devcontainer which you can use for developing
the client. Development happens only over PR's because we want to keep
master stable and always usable for new, unreleased features. When
opening a PR against master all necessary checks and tests are executed,
to ensure nothing breaks. To ensure your PR does not fail due to linter
or static analyzer checks, run the following commands before opening the
PR:

::

    composer ci-ready

Testing
~~~~~~~

This client is integration tested using ``docker-compose`` to run the
needed dependencies. To run the tests locally, run these steps:

::

    docker-compose up -d postgresql14 postgrest11
    composer test


