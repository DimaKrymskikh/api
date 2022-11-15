<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserFilm;

class UserFilmController
{
    
    public function addFilm(int $filmId): string
    {
        (new UserFilm)->addFilm($filmId);
        
        return json_encode(true);
    }
    
    public function deleteFilm(int $filmId): string
    {
        $errors = [];
        
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
