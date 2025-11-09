<?php
// src/Models/Employee.php

class Employee {
    public ?int $id;
    public string $full_name;
    public string $login;
    public string $password_hash;
    public string $gender;
    public ?string $phone_number;
    public string $hire_date;
    public ?int $position_id;
    public ?int $department_id;
    public ?string $position_name;
    public ?string $department_name;

    private bool $isNew = true;

    /**
     * Получить список всех сотрудников
     */
    public static function findAll(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT e.*, p.name as position_name, d.name as department_name
                             FROM users e
                             LEFT JOIN positions p ON e.position_id = p.id
                             LEFT JOIN departments d ON e.department_id = d.id
                             ORDER BY e.full_name");
        return $stmt->fetchAll();
    }

    /**
     * Найти сотрудника по ID
     */
    public static function findById(int $id): ?self {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $employee = new self();
        foreach ($data as $key => $value) {
            $employee->$key = $value;
        }
        $employee->isNew = false;
        return $employee;
    }

        /**
     * Сохранить (создать или обновить) сотрудника
     */
    public function save(): bool {
        $pdo = Database::getInstance();
        
        if ($this->isNew) {
            // Создание новой записи
            $stmt = $pdo->prepare("INSERT INTO users (full_name, login, password_hash, gender, phone_number, hire_date, position_id, department_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $this->full_name,
                $this->login,
                $this->password_hash,
                $this->gender,
                $this->phone_number,
                $this->hire_date,
                $this->position_id,
                $this->department_id,
                $this->status ?? 'active' // Убедимся, что статус есть
            ]);
            if ($result) {
                $this->id = (int)$pdo->lastInsertId();
                $this->isNew = false;
            }
            return $result;
        } else {
            // Обновление существующей записи
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, login = ?, gender = ?, phone_number = ?, hire_date = ?, position_id = ?, department_id = ?, status = ? WHERE id = ?");
            return $stmt->execute([
                $this->full_name,
                $this->login,
                $this->gender,
                $this->phone_number,
                $this->hire_date,
                $this->position_id,
                $this->department_id,
                $this->status,
                $this->id
            ]);
        }
    }

    /**
     * Удалить сотрудника
     */
    public function delete(): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Проверить, существует ли пользователь с таким логином
     */
    public static function findByLogin(string $login): ?self {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $employee = new self();
        foreach ($data as $key => $value) {
            $employee->$key = $value;
        }
        return $employee;
    }

    /**
     * Найти сотрудника по полному имени
     * @param string $full_name
     * @return Employee|null
     */
    public static function findByFullName(string $full_name): ?self {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE full_name = ?");
        $stmt->execute([$full_name]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        $employee = new self();
        foreach ($data as $key => $value) {
            $employee->$key = $value;
        }
        $employee->isNew = false; // Важно указать, что это не новый объект
        return $employee;
    }
}