create schema if not exists basic_auth;
-- GRANT USAGE ON SCHEMA basic_auth TO postgres;
-- ALTER DEFAULT PRIVILEGES IN SCHEMA basic_auth GRANT SELECT ON TABLES TO postgres;

create table if not exists basic_auth.users (
    email    text primary key check ( email ~* '^.+@.+\..+$' ),
    pass     text not null check (length(pass) < 512),
    role     name not null check (length(role) < 512)
);

create or replace function basic_auth.check_role_exists() returns trigger as $$
begin
    if not exists (select 1 from pg_roles as r where r.rolname = new.role) then
        raise foreign_key_violation using message = 'unknown database role: ' || new.role;
        return null;
    end if;
    return new;
end
$$ language plpgsql;

drop trigger if exists ensure_user_role_exists on basic_auth.users;
create constraint trigger ensure_user_role_exists
    after insert or update on basic_auth.users
    for each row
    execute procedure basic_auth.check_role_exists();

create extension if not exists pgcrypto;
create extension if not exists pgjwt;

create or replace function basic_auth.encrypt_pass() returns trigger as $$
begin
    if tg_op = 'INSERT' or new.pass <> old.pass then
        new.pass = crypt(new.pass, gen_salt('bf'));
    end if;
    return new;
end
$$ language plpgsql;

drop trigger if exists encrypt_pass on basic_auth.users;
create trigger encrypt_pass
    before insert or update on basic_auth.users
    for each row
    execute procedure basic_auth.encrypt_pass();

create or replace function basic_auth.user_role(email text, pass text) returns name
    language plpgsql
    as $$
begin
    return (
        select role from basic_auth.users
        where users.email = user_role.email
        and users.pass = crypt(user_role.pass, users.pass)
    );
end;
$$;

create role anon noinherit;
create role authenticator noinherit;
grant anon to authenticator;

CREATE TYPE basic_auth.jwt_token AS (
    token text
);

ALTER DATABASE postgres SET "app.jwt_secret" TO 'reallyreallyreallyreallyverysafe';

create or replace function login(email text, pass text) returns basic_auth.jwt_token as $$
declare
    _role name;
    result basic_auth.jwt_token;
begin
    -- check email and password
    select basic_auth.user_role(email, pass) into _role;
    if _role is null then
        raise invalid_password using message = 'invalid user or password';
    end if;

    select sign(
        row_to_json(r), current_setting('app.jwt_secret')
    ) as token
    from (
        select _role as role, login.email as email, extract(epoch from now())::integer + 60*60 as exp
    ) r
    into result;
    return result;
end;
$$ language plpgsql security definer;

grant execute on function login(text,text) to anon;

INSERT INTO basic_auth.users (email, pass, "role") VALUES ('test@acme.dev', 'password', 'postgres');
