<?php
// src/Controllers/AuthController.php

class AuthController {
    
    /**
     * Показать форму входа
     */
    public function showLoginForm() {
        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Обработать попытку входа
     */
    public function login() {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($login) || empty($password)) {
            $error = "Пожалуйста, заполните все поля";
            require __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $user = User::findByLogin($login);

        if ($user && $user->verifyPassword($password)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_login'] = $user->login;
            $_SESSION['user_name'] = $user->full_name;
            
            $host = $_SERVER['HTTP_HOST'];
            header("Location: http://$host/public/");
            exit();

        } else {
            $error = "Неверный логин или пароль";
            require __DIR__ . '/../Views/auth/login.php';
        }
    }

    /**
     * Обработать выход из системы
     */
    public function logout() {
        session_start();
        $_SESSION = [];
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        $host = $_SERVER['HTTP_HOST'];
        header("Location: http://$host/public/");
        exit();
    }
}