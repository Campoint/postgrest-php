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

Complex logic conditions
~~~~~~~~~~~~~~~~~~~~~~~~

The ``PostgrestRequestBuilder`` supports complex logic conditions using ``and``/ ``or``.
Unfortunately, when using ``and``/ ``or`` the ``PostgrestRequestBuilder`` will not be able to,
escape the values for you. You will have to escape the values yourself.

.. code:: php

    $query = $client->from('schema_name', 'table_name')
        ->select('*')
        ->or(
            (new LogicOperatorCondition('a', FilterOperator::EQUAL, 42)),
            (new LogicOperatorCondition('b', FilterOperator::LESS_THAN, 2.0, negate: true)),
            // escape strings yourself
            (new LogicOperatorCondition('c', FilterOperator::IN, '("foo bar",bar)')),
        );

Nested complex logic conditions are not supported using the LogicOperatorCondition class.
You will need to build the string yourself. You can implement your own logic condition class
which implements the ``Stringable`` interface, as the functions ``or()`` and ``and()`` accept this interface.

Exceptions
----------

When constructing a query there are two exceptions which can be thrown,
``NotUnifiedValuesException`` and ``FilterLogicException``. These
exceptions will be invoked when the construction of the query encounters
an abnormal setting, like combining the ``any()`` with the ``all()``
modifier or if the values in an array are not of the same type.
