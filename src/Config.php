<?php
// src/Config.php

class Config {
    // Загружаем переменные из .env файла
    public static function get(string $key) {
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            throw new Exception("Файл .env не найден!");
        }

        $variables = parse_ini_file($envFile);
        return $variables[$key] ?? null;
    }
}