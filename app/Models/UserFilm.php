<?php

namespace App\Models;

use App\App;

/**
 * Взаимодействие с таблицей person.users_films
 */
class UserFilm
{
    /**
     * Добавление фильма в список пользователя
     * @param int $filmId - id добавляемого фильма
     * @return void
     */
    public function addFilm(int $filmId): void
    {
        App::$db->execute(<<<SQL
                INSERT INTO person.users_films (user_id, film_id)
                VALUES (:userId, :filmId)
            SQL, [
                'userId' => App::$userId,
                'filmId' => $filmId,
            ]);
    }

    /**
     * Удаление фильма с id = $filmId из списка пользователя
     * @param int $filmId
     * @return void
     */
    public function deleteFilm(int $filmId): void
    {
        App::$db->execute(<<<SQL
                DELETE FROM person.users_films WHERE user_id = :userId AND film_id = :filmId
            SQL, [
                'userId' => App::$userId,
                'filmId' => $filmId
            ]);
    }
}
