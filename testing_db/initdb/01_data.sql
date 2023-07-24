create schema if not exists test_schema;

CREATE TABLE IF NOT EXISTS test_schema.select_test_table (
    a text,
    b integer
);

INSERT INTO test_schema.select_test_table (a, b) VALUES
    ('test1', 1),
    ('test2', 2),
    ('test3', 3),
    ('test4', 4),
    ('test5', 5),
    ('test6', 6),
    ('test7', 7),
    ('test8', 8),
    ('test9', 9),
    ('test0', 0);

CREATE TABLE IF NOT EXISTS test_schema.insert_test_table (
    a text primary key,
    b integer default 69,
    c text
);

CREATE TABLE IF NOT EXISTS test_schema.insert_header_test_table (
    a serial primary key,
    b integer default 69
);

CREATE TABLE IF NOT EXISTS test_schema.update_test_table (
    a text,
    b integer
);

INSERT INTO test_schema.update_test_table (a, b) VALUES
    ('test1', 1),
    ('test2', 2),
    ('test3', 3),
    ('test4', 4),
    ('test5', 5),
    ('test6', 6),
    ('test7', 7),
    ('test8', 8),
    ('test9', 9),
    ('test0', 0);

CREATE TABLE IF NOT EXISTS test_schema.upsert_test_table (
    a text primary key,
    b integer
);

INSERT INTO test_schema.upsert_test_table (a, b) VALUES
    ('test1', 1),
    ('test2', 2),
    ('test3', 3),
    ('test4', 4),
    ('test5', 5),
    ('test6', 6),
    ('test7', 7),
    ('test8', 8),
    ('test9', 9),
    ('test0', 0);

CREATE TABLE IF NOT EXISTS test_schema.upsert_onconflict_test_table (
    a text unique,
    b integer
);

INSERT INTO test_schema.upsert_onconflict_test_table (a, b) VALUES
    ('test1', 1),
    ('test2', 2),
    ('test3', 3),
    ('test4', 4),
    ('test5', 5),
    ('test6', 6),
    ('test7', 7),
    ('test8', 8),
    ('test9', 9),
    ('test0', 0);

CREATE TABLE IF NOT EXISTS test_schema.delete_test_table (
    a text primary key,
    b integer
);

INSERT INTO test_schema.delete_test_table (a, b) VALUES
    ('test1', 1),
    ('test2', 2),
    ('test3', 3),
    ('test4', 4),
    ('test5', 5),
    ('test6', 6),
    ('test7', 7),
    ('test8', 8),
    ('test9', 9),
    ('test0', 0);

CREATE TABLE IF NOT EXISTS test_schema.filter_test_table (
    a text,
    b integer,
    c float,
    d boolean,
    e date,
    f integer[],
    g int8range
);

INSERT INTO test_schema.filter_test_table (a, b, c, d, e, f, g) VALUES
    ('test1', 1, 0.1, true, '2020-01-01', array[1,2,3], int8range(1, 11)),
    ('test2', 2, 0.2, false, '2020-01-02', array[4,5,6], int8range(2, 12)),
    ('test3', 3, 0.3, true, '2020-01-03', array[7,8,9], int8range(3, 13)),
    ('test4', 4, 0.4, false, '2020-01-04', array[1,2,3], int8range(4, 14)),
    ('test5', 5, 0.5, true, '2020-01-05', array[4,5,6], int8range(5, 15)),
    ('test6', 6, 0.6, false, '2020-01-06', array[7,8,9], int8range(6, 16)),
    ('test7', 7, 0.7, true, '2020-01-07', array[1,2,3], int8range(7, 17)),
    ('test8', 8, 0.8, false, '2020-01-08', array[4,5,6], int8range(8, 18)),
    ('test9', 9, 0.9, true, '2020-01-09', array[7,8,9], int8range(9, 19)),
    ('test10', 10, 1.0, false, '2020-01-10', array[1,2,3], int8range(10, 20));

CREATE TABLE IF NOT EXISTS test_schema.fts_test_table (
    a text
);

INSERT INTO test_schema.fts_test_table (a) VALUES
    ('The Terminator'),
    ('Terminator 2: Judgment Day'),
    ('Terminator 3: Rise of the Machines'),
    ('Terminator Salvation'),
    ('Terminator Genisys'),
    ('Terminator: Dark Fate'),
    ('The Matrix'),
    ('The Matrix Reloaded'),
    ('The Matrix Revolutions'),
    ('The Matrix Resurrections'),
    ('The Matrix 4');
