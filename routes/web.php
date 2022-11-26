<?php
/**
 * Формирование массива маршрутов
 */

use App\App;
use App\Controllers\FilmController;
use App\Controllers\RegisteredUserController;
use App\Controllers\AuthenticatedUserController;
use App\Controllers\AccountController;
use App\Controllers\UserFilmController;
use App\Models\User;

// Первый запрос клиентского приложения не имеет тела (нет токена)
if (!App::$request) {
    $router->get('init/{aud}', AuthenticatedUserController::class);
}
// Экшены для не аутентифицированного пользователя
if (App::$request && App::$userId === User::DEFAULT_USER_ID) {
    $router->post('register', RegisteredUserController::class, 'store');
    $router->post('login', AuthenticatedUserController::class, 'store');
}
// Экшены для аутентифицированного пользователя
if (App::$request && App::$userId !== User::DEFAULT_USER_ID) {
    $router->post('logout', AuthenticatedUserController::class, 'destroy');
    $router->post('account/index/{activePage}/{itemsNumberOnPage}', AccountController::class);
    $router->post('account/filmCard/{filmId}', AccountController::class, 'filmCard');
    $router->post('account/getFilm/{filmId}', AccountController::class, 'getFilm');
    $router->post('userFilm/{filmId}', UserFilmController::class, 'addFilm');
    $router->delete('userFilm/{filmId}', UserFilmController::class, 'deleteFilm');
    $router->delete('account', AccountController::class, 'removeAccount');
}
// Экшены для всех пользователей с токеном
$router->post('film/{activePage}/{itemsNumberOnPage}', FilmController::class);
