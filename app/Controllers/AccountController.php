<?php

namespace App\Controllers;

use Base\Pagination;
use App\Models\Film;

class AccountController 
{
    public function index(int $activePage, int $itemsNumberOnPage): string
    {
        $filmsActivePage = $activePage ?: Pagination::DEFAULT_ACTIVE_PAGE;
        $filmsNumberOnPage = $itemsNumberOnPage ?: Pagination::DEFAULT_ITEMS_NUMBER_ON_PAGE;
        
        // Получаем список существующих фильмов для активной страницы
        $filmsList = (new Film)->getList($filmsActivePage, $filmsNumberOnPage, true);
        
        return json_encode((object)[
            'films' => $filmsList->films,
            'pagination' => (new Pagination($filmsActivePage, $filmsNumberOnPage, $filmsList->filmsNumberTotal))->get(),
        ]);
    }
    
    public function filmCard($filmId): string
    {
        return json_encode((object)[
            'film' => (new Film)->getFilmCard($filmId)
        ]);
    }
    
    /**
     * Отдаёт данные о фильме для модального окна с подтверждением на удаление фильма
     * @return string
     */
    public function getFilm(int $filmId): string
    {
        return json_encode((new Film)->getFilm($filmId));
    }
}
