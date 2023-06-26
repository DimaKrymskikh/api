<?php

namespace App\Controllers;

use Base\Pagination;
use App\Models\Film;
use App\Models\User;

/**
 * Взаимодействие со страницей аккаунта
 */
class AccountController
{
    /**
     * Осуществляет процесс смены списка фильмов:
     * первая загрузка, изменение страницы, изменение числа фильмов на странице, фильтрация фильмов
     * @param int $activePage - Страница для которой отдаются фильмы (активная страница)
     * @param int $itemsNumberOnPage - Число фильмов на странице
     * @return string
     * Возвращает список фильмов с параметрами пагинации
     */
    public function index(int $activePage, int $itemsNumberOnPage): string
    {
        $filmsActivePage = $activePage ?: Pagination::DEFAULT_ACTIVE_PAGE;
        $filmsNumberOnPage = $itemsNumberOnPage ?: Pagination::DEFAULT_ITEMS_NUMBER_ON_PAGE;

        // Получаем список существующих фильмов для активной страницы
        $filmsList = (new Film())->getList($filmsActivePage, $filmsNumberOnPage, true);

        return json_encode((object)[
            'films' => $filmsList->films,
            'pagination' => (new Pagination($filmsActivePage, $filmsNumberOnPage, $filmsList->filmsNumberTotal))->get(),
        ]);
    }

    /**
     * Возвращает данные фильма с id = $filmId
     * @param int $filmId
     * @return string
     */
    public function filmCard(int $filmId): string
    {
        return json_encode((object)[
            'film' => (new Film())->getFilmCard($filmId)
        ]);
    }

    /**
     * Отдаёт данные о фильме для модального окна с подтверждением на удаление фильма
     * @return string
     */
    public function getFilm(int $filmId): string
    {
        return json_encode((new Film())->getFilm($filmId));
    }

    /**
     * Удаляет аккаунт
     * @return string
     */
    public function removeAccount(): string
    {
        return json_encode((new User())->removeAccount());
    }
}
