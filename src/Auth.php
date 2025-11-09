<?php
// src/Auth.php

class Auth {
    
    /**
     * Проверяет, авторизован ли пользователь
     */
    public static function check(): bool {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Возвращает ID текущего пользователя
     */
    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Возвращает модель текущего пользователя
     */
    public static function user(): ?User {
        if (!self::check()) {
            return null;
        }
        // Чтобы не делать лишних запросов к БД, можно кэшировать пользователя в сессии
        if (!isset($_SESSION['user_object'])) {
            $_SESSION['user_object'] = User::findById(self::id());
        }
        return $_SESSION['user_object'];
    }

    /**
     * Проверяет, есть ли у пользователя определенная роль
     */
    public static function hasRole(string $roleName): bool {
        $user = self::user();
        if (!$user) {
            return false;
        }
        return $user->role === $roleName;
    }
}