<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserFilm;

/**
 * Взаимодействие с таблицей person.users_films
 */
class UserFilmController
{
    /**
     * Добавление фильма с id = $filmId в список пользователя
     * @param int $filmId
     * @return string
     */
    public function addFilm(int $filmId): string
    {
        (new UserFilm)->addFilm($filmId);
        
        return json_encode(true);
    }
    
    /**
     * Удаление фильма с id = $filmId из списка пользователя
     * @param int $filmId
     * @return string
     */
    public function deleteFilm(int $filmId): string
    {
        $errors = [];
        // Проверка введённого пароля
        if ((new User)->confirmPassword()) {
            (new UserFilm)->deleteFilm($filmId);
        } else {
            $errors[] = 'Попробуйте ввести пароль ещё раз';
        }
        
        return json_encode((object)[
            'errors' => $errors
        ]);
    }
}
