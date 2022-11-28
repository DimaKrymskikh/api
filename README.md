# api
Серверная часть api

## Установка
Нужно выполнить клонирование
```
git clone git@github.com:DimaKrymskikh/api.git
```
Будет создана папка `api`. 
После перехода в эту папку следует выполнить команду
```
composer install
```
(Как установить composer изложено в инструкции [Composer Getting Started](https://getcomposer.org/doc/00-intro.md)).
Далее нужно создать файл `.env` и скопировать в него содержимое файла `.env.example`. Затем, в файле `.env` необходимо определить переменные окружения. Их смысл таков:
```
APP_DOMAIN - домен данного приложения, например, api.foo
APP_SECRET_KEY - секретный ключ приложения, для его генерации можно воспользоваться скриптом createSecretKey.php
DB_* - настройки базы данных postgres
AUD_* - клиентские приложения (в данный момент всего одно)
```
Нужно создать базу данных с любым именем `CREATE DATABASE name;` в редакторе клиентского программного приложения SQL 
(
[pgAdmin](https://www.pgadmin.org/) или
[DBeaver](https://dbeaver.io/)
) 
или `createdb -p *** -U *** name` в командной строке.
Далее, следует создать таблицы при помощи файла `create_tables.sql` из папки `SQL`.
Для этого нужно в интерактивном терминале (см. [Postgres Pro](https://postgrespro.ru/docs/postgrespro/14/app-psql)) подключиться к созданной базе `name`
```
psql -p *** -U *** name
```
и выполнить команду (если находимся в папке `SQL`)
```
psql \i 'create_tables.sql'
```
и заполнить таблицы данными (файлы из папки `SQL`)
```
psql \copy public.languages(id, name, updated_at) FROM 'languages.csv';

psql \copy dvd.films(id, title, description, release_year, language_id, updated_at) FROM 'films.csv';

psql \copy dvd.actors(id, first_name, last_name, updated_at) FROM 'actors.csv';

psql \copy dvd.films_actors(actor_id, film_id, updated_at) FROM 'films_actors.csv';
```
Эти данные позаимствованы из 
[Load PostgreSQL Sample Database](https://www.postgresqltutorial.com/postgresql-getting-started/load-postgresql-sample-database/)

## Настройка Apache
```
RewriteEngine on
# Если запрашиваемая в URL директория или файл существуют обращаемся к ним напрямую
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# Если нет - перенаправляем запрос на index.php
RewriteRule . index.php
```

## Клиентские приложения
Домен, например, `foo.bar` [приложения на нативном js](https://github.com/DimaKrymskikh/html) нужно указать в файле `.env` в переменной `AUD_HTML`.
