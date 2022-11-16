<?php

use App\App;
use App\Controllers\FilmController;
use App\Controllers\RegisteredUserController;
use App\Controllers\AuthenticatedUserController;
use App\Controllers\AccountController;
use App\Controllers\UserFilmController;
use App\Models\User;

if (!App::$request) {
    $router->get('init/{aud}', AuthenticatedUserController::class);
}

if (App::$request && App::$userId === User::DEFAULT_USER_ID) {
    $router->post('register', RegisteredUserController::class, 'store');
    $router->post('login', AuthenticatedUserController::class, 'store');
}

if (App::$request && App::$userId !== User::DEFAULT_USER_ID) {
    $router->post('logout', AuthenticatedUserController::class, 'destroy');
    $router->post('account/index/{activePage}/{itemsNumberOnPage}', AccountController::class);
    $router->post('account/filmCard/{filmId}', AccountController::class, 'filmCard');
    $router->post('account/getFilm/{filmId}', AccountController::class, 'getFilm');
    $router->post('userFilm/{filmId}', UserFilmController::class, 'addFilm');
    $router->delete('userFilm/{filmId}', UserFilmController::class, 'deleteFilm');
    $router->delete('account', AccountController::class, 'removeAccount');
}

$router->post('film/{activePage}/{itemsNumberOnPage}', FilmController::class);
