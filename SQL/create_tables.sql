
-----------------------------------------------------------------------------------------------------------------------------------------------------
-- Создание схем
-- В схеме dvd будут таблицы для фильмов
-- В схеме person будут таблицы для пользователей
-----------------------------------------------------------------------------------------------------------------------------------------------------
create schema dvd;
create schema person;
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
-- Функция триггера, обновляющая поле updated_at таблиц
-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION public.last_updated()
 RETURNS trigger
 LANGUAGE plpgsql
AS 
$function$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END 
$function$
;

COMMENT ON FUNCTION public.last_updated IS
    'Функция триггера, обновляющая поле updated_at таблиц';
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE public.languages (
    id serial PRIMARY KEY,
    name text NOT NULL,
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TRIGGER last_updated BEFORE
UPDATE ON public.languages
FOR EACH ROW EXECUTE FUNCTION last_updated();

COMMENT ON TABLE public.languages IS
    'Языки народов';

COMMENT ON COLUMN public.languages.name IS 
    'Название';

COMMENT ON COLUMN public.languages.updated_at IS 
    'Время последнего изменения записи';
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE dvd.films (
    id bigserial PRIMARY KEY,
    title text NOT NULL,
    description text,
    release_year int,
    language_id int REFERENCES public.languages(id),
    updated_at timestamptz NOT NULL DEFAULT now()
);

CREATE TRIGGER last_updated BEFORE
UPDATE ON dvd.films
FOR EACH ROW EXECUTE FUNCTION last_updated();

COMMENT ON TABLE dvd.films IS
    'Художественные фильмы';

COMMENT ON COLUMN dvd.films.title IS 
    'Название фильма';

COMMENT ON COLUMN dvd.films.description IS 
    'Краткое описание фильма';

COMMENT ON COLUMN dvd.films.release_year IS 
    'Год выхода фильма';

COMMENT ON COLUMN dvd.films.language_id IS 
    'Язык, на котором снят фильм';

COMMENT ON COLUMN dvd.films.updated_at IS 
    'Время последнего изменения данных о фильме';
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE dvd.actors (
    id bigserial PRIMARY KEY,
    first_name text NOT NULL,
    last_name text NOT NULL,
    updated_at timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX ON dvd.actors(last_name);

CREATE TRIGGER last_updated BEFORE
UPDATE ON dvd.actors
FOR EACH ROW EXECUTE FUNCTION last_updated();

COMMENT ON TABLE dvd.actors IS
    'Актёры';

COMMENT ON COLUMN dvd.actors.first_name IS 
    'Имя актёра';

COMMENT ON COLUMN dvd.actors.last_name IS 
    'Фамилия актёра';

COMMENT ON COLUMN dvd.actors.updated_at IS 
    'Время последнего изменения данных об актёре';
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE dvd.films_actors (
    film_id int REFERENCES dvd.films(id),
    actor_id int  REFERENCES dvd.actors(id),
    updated_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (film_id, actor_id)
);
CREATE INDEX ON dvd.films_actors(film_id);

CREATE TRIGGER last_updated BEFORE
UPDATE ON dvd.films_actors
FOR EACH ROW EXECUTE FUNCTION last_updated();

COMMENT ON TABLE dvd.films_actors IS
    'Таблица, связывающая таблицы dvd.films и dvd.actors';

COMMENT ON COLUMN dvd.films_actors.updated_at IS 
    'Время последнего изменения записи';
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE person.users(
    id bigserial PRIMARY KEY,
    login text NOT NULL,
    password text NOT NULL,
    created_at timestamptz not null DEFAULT now(),
    updated_at timestamptz not null DEFAULT now()
);

CREATE UNIQUE INDEX ON person.users(login);

CREATE TRIGGER last_updated 
BEFORE UPDATE ON person.users
FOR EACH ROW EXECUTE FUNCTION last_updated();

COMMENT ON TABLE person.users IS
    'Пользователи';

COMMENT ON COLUMN person.users.login IS 
    'Логин';

COMMENT ON COLUMN person.users.password IS 
    'Пароль';

COMMENT ON COLUMN person.users.created_at IS 
    'Время создания аккаунта';

COMMENT ON COLUMN person.users.updated_at IS 
    'Время последнего изменения аккаунта';
-----------------------------------------------------------------------------------------------------------------------------------------------------


-----------------------------------------------------------------------------------------------------------------------------------------------------
CREATE TABLE person.users_films (
    user_id int4 REFERENCES person.users(id) ON DELETE CASCADE,
    film_id int4 REFERENCES dvd.films(id),
    updated_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (user_id, film_id)
);

CREATE INDEX ON person.users_films(film_id);

CREATE TRIGGER last_updated BEFORE
UPDATE ON person.users_films
FOR EACH ROW EXECUTE FUNCTION last_updated();

COMMENT ON TABLE person.users_films IS
    'Таблица, связывающая таблицы person.users и dvd.films';

COMMENT ON COLUMN person.users_films.updated_at IS 
    'Время последнего изменения записи';
-----------------------------------------------------------------------------------------------------------------------------------------------------





















