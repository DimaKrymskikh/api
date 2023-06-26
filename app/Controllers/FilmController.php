<?php

namespace App\Controllers;

use Base\Pagination;
use App\Models\Film;

/**
 * Взаимодействие со страницей "Каталог"
 */
class FilmController
{
    /**
     * Изменяет список фильмов:
     * первая загрузка, изменение страницы, изменение числа фильмов на странице, фильтрация фильмов
     * @param int $page - Страница для которой отдаются фильмы (активная страница)
     * @param int $quantity - Число фильмов на странице
     * @return string
     */
    public function index(int $page, int $quantity): string
    {
        $activePage = $page ?: Pagination::DEFAULT_ACTIVE_PAGE;
        $filmsNumberOnPage = $quantity ?: Pagination::DEFAULT_ITEMS_NUMBER_ON_PAGE;
        // Получаем список существующих фильмов для активной страницы
        $filmsList = (new Film())->getList($activePage, $filmsNumberOnPage);

        return json_encode((object)[
            'films' => $filmsList->films,
            'pagination' => (new Pagination($activePage, $filmsNumberOnPage, $filmsList->filmsNumberTotal))->get(),
        ]);
    }
}
