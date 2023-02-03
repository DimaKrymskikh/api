<?php

namespace App\Models;

use Base\Pagination;
use Base\Utils\ArrayUtils;
use App\App;

/**
 * Модель для взаимодействия с таблицей dvd.films
 */
class Film 
{
    /**
     * Возвращает список фильмов
     * @param int $activePage - Номер активной страницы 
     * @param int $filmsNumberOnPage - Число элементов на странице
     * @param bool $isPersonal - true, если список фильмов для аккаунта
     * @return object
     */
    public function getList(int $activePage, int $filmsNumberOnPage, bool $isPersonal = false): object
    {
        // Массив для сбора условий
        $arrWhere = [];
        // Если запрос для аккаунта пользователя
        if($isPersonal) {
            $arrWhere[] = 'uf.user_id = :userId';
        }
        // Если в запросе присутствует фильтр по названию фильма
        if ($title = trim(App::$request->sortFilmTitle)) {
            $arrWhere[] = "f.title ILIKE '%{$title}%'";
        }
        // Если в запросе присутствует фильтр по описанию фильма
        if ($description = trim(App::$request->sortFilmDescription)) {
            $arrWhere[] = "f.description ILIKE '%{$description}%'";
        }
        // Формируем условие запроса
        $condition = count($arrWhere) ? "WHERE " . implode(' AND ', $arrWhere) : '';
        
        $films = App::$db->selectObjects(<<<SQL
                WITH _ AS (
                    SELECT
                        f.id,
                        f.title,
                        f.description,
                        f.language_id,
                        row_number () OVER (ORDER BY f.title) AS n,
                        count (*) OVER () AS count,
                        coalesce (uf.user_id::bool, false) AS "isAvailable"
                    FROM dvd.films f
                    LEFT JOIN person.users_films uf ON uf.film_id = f.id AND uf.user_id = :userId
                    $condition
                )
                SELECT
                    _.id,
                    _.n,
                    _.count,
                    _.title,
                    _.description,
                    l.name,
                    _."isAvailable"
                FROM _
                JOIN languages l ON l.id = _.language_id
                WHERE _.n >= :from AND _.n <= :to
                ORDER BY _.n
            SQL, [
                'from' => Pagination::from($activePage, $filmsNumberOnPage),
                'to' => Pagination::to($activePage, $filmsNumberOnPage),
                'userId' => App::$userId ?: User::DEFAULT_USER_ID
            ]);

        $filmsList = (object) [];
        // Убираем 'count' (общее число фильмов) из массива фильмов
        $filmsList->films = array_values(ArrayUtils::flatToComplex($films, 'n', ['n', 'id', 'title', 'description', 'name', 'isAvailable']));
        // 'count' сохраняем отдельно для передачи в пагинацию
        $filmsList->filmsNumberTotal = isset($films[0]) ? $films[0]->count : 0;
        
        return $filmsList;
    }
    
    /**
     * Извлекает подробные данные о фильме
     * @param int $filmId - id фильма
     * @return object
     */
    public function getFilmCard(int $filmId): object
    {
        $film = App::$db->selectObject(<<<SQL
                    SELECT 
                        f.id AS "filmId",
                        f.title,
                        f.description,
                        f.release_year AS "releaseYear",
                        string_agg(trim(concat(a.first_name, ' ', a.last_name)), ',' ORDER BY a.first_name, a.last_name) AS "actorNames",
                        l.name AS language
                    FROM dvd.films f 
                    JOIN dvd.films_actors fa ON fa.film_id = f.id
                    JOIN dvd.actors a ON a.id = fa.actor_id
                    JOIN languages l ON l.id = f.language_id
                    WHERE f.id = :filmId
                    GROUP BY f.id, l.name
                SQL, [
                    'filmId' => $filmId,
                ]);
        
        $film->actorNames = explode(',', $film->actorNames);
        
        return $film;
    }
    
    /**
     * Извлекает название фильма по id фильма
     * @param int $filmId - id фильма
     * @return object
     */
    public function getFilm(int $filmId): object
    {
        return App::$db->selectObject(<<<SQL
                SELECT 
                    title 
                FROM dvd.films
                WHERE id = ?
            SQL, [$filmId]);
    }
}
