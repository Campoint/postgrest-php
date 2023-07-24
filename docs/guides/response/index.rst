Response
========

postgrest-php provides a class ``PostgrestResponse`` which will be
instantiated when the PostgREST server returns a non error response.
``PostgrestResponse`` will attempt to parse the body using
``json_decode()``. Furthermore also the ``Location`` and
``Content-Range`` headers will be parsed an made available through
public methods.

.. contents::
    :local:

Usage
-----

``PostgrestResponse`` exposes several methods to expose data returned by
PostgREST.

.. code:: php

    $response = $client->run(...);

    // get status code of HTTP response
    $statusCode = $response->getStatusCode();

    // body methods
    $parsedBody = $response->result();
    $rawBody = $response->rawResult();

    // Location header methods
    // only set if ReturnType::HEADERS_ONLY is selected
    $insertedRowLocation = $response->location('column_name');

    // Content-Range header methods
    $rangeStart = $response->getRangeStart();
    $rangeEnd = $response->getRangeEnd();
    // only set when count() is used
    $rangeTotal = $response->getRangeTotal();

    // general header methods
    $headers = $response->getHeaders();

Error response
~~~~~~~~~~~~~~

If the request does not succeed you will yield a
``PostgrestErrorException``. This exception wraps all possible
``Exception`` objects thrown by ``Browser::request()`` and tries to
parse the PostgREST error message and code from the response. Please
mind, that if you enable the ``autoAuth`` feature of the client,
``run()``, can also return a ``FailedAuthException``.
``PostgrestErrorException`` exposes several methods to get more
information about the error PostgREST returned.

.. code:: php

    $client->disableAutoAuth();
    try {
        $response =$client->run($query);
    } catch (Throwable $e) {
        // Get status code of response if there was one
        $statusCode = $e->getStatusCode();

        // Get response body if there was one
        $responseBody = $e->getResponseBody();

        // Get PostgREST error code if there was one
        $postgrestErrCode = $e->getPostgrestErrorCode();

        // Get PostgREST error message if there was one
        $postgrestErrCode = $e->getPostgrestErrorMessage();

        // Get message from previous exception, the previous exception will always be set
        $previousMessage = $e->getPrevious()->getMessage();
    }
