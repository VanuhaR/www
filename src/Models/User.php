<?php
// src/Models/User.php

class User {
    public ?int $id;
    public string $login;
    public string $full_name;
    public string $password_hash;
    public string $gender;
    public ?string $phone_number;
    public string $hire_date;
    public ?int $position_id;
    public ?int $department_id;
    public ?string $role; // Свойство для хранения роли

    /**
     * Найти пользователя по логину
     */
    public static function findByLogin(string $login): ?self {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $user = new self();
        foreach ($data as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    /**
     * Найти пользователя по ID и получить его роль
     */
    public static function findById(int $id): ?self {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT u.*, r.name as role FROM users u
                               LEFT JOIN user_roles ur ON u.id = ur.user_id
                               LEFT JOIN roles r ON ur.role_id = r.id
                               WHERE u.id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $user = new self();
        foreach ($data as $key => $value) {
            $user->$key = $value;
        }
        return $user;
    }

    /**
     * Проверить пароль
     */
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password_hash);
    }
}