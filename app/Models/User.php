<?php

namespace App\Models;

use Base\Jwt\JwtHelper;
use App\App;

class User 
{
    use \App\Tools\ProcessRequest;
    
    const DEFAULT_USER_ID = 0;
    const TOKEN_EXP = '+30 day';
//    const TOKEN_EXP = '+1 minute';
    
    private string $password;

    /**
     * Выполняет регистрацию пользователя.
     * Возвращает массив ошибок. Пустой массив, если ошибок нет
     * @return array
     */
    public function processRegistration(): object
    {
        $errors = [];
        
        if ($this->isLogin(App::$request->login)) {
            $errors[] = "Данный логин уже существует. Для регистрации нужно задать другой логин";
        }
        
        if(App::$request->password !== App::$request->verification) {
            $errors[] = "Введённый пароль не совпадает с подверждённым";
        }
        
        if (count($errors) === 0) {
            App::$userId = $this->insert(App::$request->login, App::$request->password);
            $app = (object)[
                'token' => $this->generateToken(App::$request->aud, App::$userId, self::TOKEN_EXP),
                'isGuest' => false
            ];
            $user = (object)[
                'login' => App::$request->login
            ];
        } else {
            $app = (object)[];
            $user = (object)[];
        }
        
        return (object)[
            'app' => $app,
            'user' => $user,
            'errors' => $errors
        ];
    }
    
    public function processLogin(): object
    {
        $errors = [];

        // По логину из базы получаем запись пользователя
        $this->getRecord(App::$request->login);

        // Проверяем полученный пароль
        if (!password_verify(App::$request->password, $this->password)) {
            $errors[] = "Неправильный логин или пароль";
        }
        
        if (count($errors) === 0) {
            // Если пароль введён верно, то создаём токен с id данного пользователя
            // Пользователь становиться аутентифицированным
            $app = (object)[
                'token' => $this->generateToken(App::$request->aud, App::$userId, self::TOKEN_EXP),
                'isGuest' => false
            ];
            $user = (object)[
                'login' => App::$request->login
            ];
        } else {
            // Иначе оставляем токен и статус пользователя без изменений
            $app = (object)[];
            $user = (object)[];
        }
        
        return (object)[
            'app' => $app,
            'user' => $user,
            'errors' => $errors
        ];
    }
    
    /**
     * Разлогирование пользователя
     * @return object
     */
    public function processLogout(): object
    {
        return (object)[
            'app' => (object)[
                'token' => $this->generateToken(App::$request->aud, self::DEFAULT_USER_ID, self::TOKEN_EXP),
                'isGuest' => true,
            ],
            'user' => (object)[
                'login' => ''
            ]
        ];
    }
    
    public function confirmPassword(): bool
    {
        $this->setPassword(App::$userId);
        return password_verify(App::$request->password, $this->password);
    }
    
    /**
     * Удаление аккаунта
     * @return object
     */
    public function removeAccount(): object
    {
        $errors = [];
        $app = (object)[];
        $user = (object)[];
        
        if ($this->confirmPassword()) {
            // Если пароль верный, то удаляем аккаунт
            $this->delete();
            // Задаём данные для отправки клиенту, соответсвующие неаутифицированному пользователю
            $app = (object) [
                'token' => $this->generateToken(App::$request->aud, self::DEFAULT_USER_ID, self::TOKEN_EXP),
                'isGuest' => true,
            ];
            $user = (object) [
                'login' => ''
            ];
        } else {
            $errors[] = 'Попробуйте ввести пароль ещё раз';
        }
        
        return (object)[
            'errors' => $errors,
            'app' => $app,
            'user' => $user
        ];
    }
    
    private static function insert(string $login, string $password): int 
    {
        return App::$db->selectValue(<<<SQL
                    INSERT INTO person.users (login, password) VALUES (:login, :password)
                    RETURNING id
                SQL, [
                    'login' => $login,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);
    }
    
    /**
     * Запрос в базу на удаление аккаунта
     * @return void
     */
    private function delete(): void
    {
        App::$db->execute(<<<SQL
                DELETE FROM person.users WHERE id = :id
            SQL, [
                'id' => App::$userId
            ]);
    }

    /**
     * Проверяет существование логина
     * @param string $login
     * @return bool
     */
    private function isLogin(string $login): bool
    {
        return App::$db->selectValue(<<<SQL
                SELECT EXISTS(SELECT FROM person.users WHERE login = :login)
            SQL, ['login' => $login]);
    }
    
    /**
     * По логину извлекает из базы и задаёт id, login, password пользователя
     * @param string $userLogin 
     * @return void
     */
    private function getRecord(string $userLogin): void 
    {
        $row = App::$db->selectObject(<<<SQL
                SELECT id, login, password FROM person.users WHERE login = :userLogin
            SQL, ['userLogin' => $userLogin]);

        App::$userId = isset($row->id) ? $row->id : self::DEFAULT_USER_ID;
        $this->password = isset($row->password) ? $row->password : '';
    }
    
    private function setPassword(int $userId): void
    {
        $this->password = App::$db->selectValue(<<<SQL
                SELECT password FROM person.users WHERE id = :id
            SQL, ['id' => $userId]);
    }
}
