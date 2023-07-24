RequestBuilder
==============

postgrest-php provides a request builder which is responsible for
constructing the HTTP request which will be run by the client.

.. contents::
    :local:

Usage
-----

The ``PostgrestRequestBuilder`` can be instantiated using the ``from()``
method in the client. Both async and sync client implement this method.
Once the ``PostgrestRequestBuilder`` is created, you can chain
operations and filters on that object.

.. code:: php

    // Function chaining with PostgrestRequestBuilder
    $query = $client->from('schema_name', 'table_name')
        ->select('*')
        ->any()->eq('a', -1, 0, 1)
        ->in('b', 'foo', 'bar', 'foobar')
        ->not()->gt('c', 1.23);

To execute the HTTP request built by the ``PostgrestRequestBuilder``
pass it to the ``run()`` method of the client.

.. code:: php

    $response = $client->run($query);

Exceptions
----------

When constructing a query there are two exceptions which can be thrown,
``NotUnifiedValuesException`` and ``FilterLogicException``. These
exceptions will be invoked when the construction of the query encounters
an abnormal setting, like combining the ``any()`` with the ``all()``
modifier or if the values in an array are not of the same type.
