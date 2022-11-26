# api
Серверная часть api

## Установка
Нужно выполнить клонирование
```
git@github.com:DimaKrymskikh/api.git
```
а затем команду
```
composer update
```
Далее нужно создать файл `.env` и скопировать в него используемые переменные окружения из файла `.env.example`.
```
APP_DOMAIN - uri данного приложения
APP_SECRET_KEY - секретный ключ приложения, для его генерации можно воспользоваться скриптом createSecretKey.php
DB_* - настройки базы данных postgres
AUD_* - клиентские приложениря (в данный момент всего одно)
```
Нужно создать базу данных с любым именем `CREATE DATABASE name;`.
Далее, следует создать таблицы при помощи файла `create_tables.sql` из папки `SQL`,
```
psql \i 'create_tables.sql'
```
и заполнить таблицы данными (файлы из папки `SQL`)
```
COPY public.languages(id, name, updated_at)
FROM 'languages.csv';

COPY dvd.films(id, title, description, release_year, language_id, updated_at)
FROM 'films.csv';

COPY dvd.actors(id, first_name, last_name, updated_at)
FROM 'actors.csv';

COPY dvd.films_actors(actor_id, film_id, updated_at)
FROM 'films_actors.csv';
```
Эти данные позаимствованы из 
[Load PostgreSQL Sample Database](https://www.postgresqltutorial.com/postgresql-getting-started/load-postgresql-sample-database/)
