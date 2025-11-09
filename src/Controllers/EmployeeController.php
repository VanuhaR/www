<?php
// src/Controllers/EmployeeController.php

require_once __DIR__ . '/../Auth.php';

class EmployeeController {
    
    /**
     * Показать список всех сотрудников с фильтрацией, поиском и массовыми действиями
     */
    public function index() {
        $pdo = Database::getInstance();

        // --- 1. Получаем параметры ---
        $search = trim($_GET['search'] ?? '');
        $filter_department = (int)($_GET['filter_department'] ?? 0);
        $filter_position = (int)($_GET['filter_position'] ?? 0);
        $filter_status = $_GET['filter_status'] ?? 'active';

        // --- 2. Строим и выполняем SQL-запрос ---
                $sql = "SELECT e.*, p.name as position_name, d.name as department_name, r.name as role_name
                FROM users e
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN user_roles ur ON e.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE e.status = ?";
        $params = [$filter_status];

        // Добавляем условие для поиска по фамилии
        if (!empty($search)) {
            $sql .= " AND e.full_name LIKE ?";
            $search_term = explode(' ', $search, 2)[0] . '%';
            $params[] = $search_term;
        }

        // Добавляем условие для фильтра по отделению
        if ($filter_department > 0) {
            $sql .= " AND e.department_id = ?";
            $params[] = $filter_department;
        }

        // Добавляем условие для фильтра по должности
        if ($filter_position > 0) {
            $sql .= " AND e.position_id = ?";
            $params[] = $filter_position;
        }

        $sql .= " ORDER BY e.full_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $employees = $stmt->fetchAll();

        // --- 3. Получаем данные для фильтров и статистики ---
        $positions = $pdo->query("SELECT id, name FROM positions ORDER BY name")->fetchAll();
        $departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
        $total_employees = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $active_employees = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
        $inactive_employees = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();

        // --- 4. Генерируем HTML-контент ---
        $content = "<h3>Панель управления сотрудниками</h3>";
        
        // Статистика
        $content .= "<div class='stats-bar'>";
        $content .= "<span>Всего: <strong>" . $total_employees . "</strong></span>";
        $content .= "<span>Активны: <strong class='status-active'>" . $active_employees . "</strong></span>";
        $content .= "<span>Неактивны: <strong class='status-inactive'>" . $inactive_employees . "</strong></span>";
        $content .= "</div>";

        // Форма фильтров и поиска
        $content .= "<form action='/public/employees' method='GET' class='filter-form'>";
        $content .= "<input type='text' name='search' placeholder='Поиск по фамилии...' value='" . htmlspecialchars($search) . "'>";

        $content .= "<select name='filter_position'>"; // <-- СНАЧАЛА БЛОК ДОЛЖНОСТЕЙ
        $content .= "<option value='0'>Все должности</option>";
            foreach ($positions as $pos) {
        $selected = ($filter_position == $pos['id']) ? 'selected' : '';
        $content .= "<option value='{$pos['id']}' $selected>" . htmlspecialchars($pos['name']) . "</option>";
        }
        $content .= "</select>";

        $content .= "<select name='filter_department'>"; // <-- ПОТОМ БЛОК ОТДЕЛЕНИЙ
        $content .= "<option value='0'>Все отделения</option>";
            foreach ($departments as $dept) {
        $selected = ($filter_department == $dept['id']) ? 'selected' : '';
        $content .= "<option value='{$dept['id']}' $selected>" . htmlspecialchars($dept['name']) . "</option>";
}
        $content .= "</select>";

        $content .= "<select name='filter_status'>";
        $content .= "<option value='active' " . ($filter_status === 'active' ? 'selected' : '') . ">Только активные</option>";
        $content .= "<option value='inactive' " . ($filter_status === 'inactive' ? 'selected' : '') . ">Только неактивные</option>";
        $content .= "</select>";

        $content .= "<button type='submit' class='btn'>Применить</button>";
        $content .= "<a href='/public/employees' class='btn btn-secondary'>Сбросить</a>";
        $content .= "</form>";

        // Кнопка добавления и экспорта
        $content .= "<div class='actions-bar'>";
        $content .= "<a href='/public/employees/create' class='btn'>Добавить нового сотрудника</a>";
        $content .= "<a href='/public/employees/export' class='btn btn-secondary'>Экспортировать в CSV</a>";
        $content .= "<form action='/public/employees/import' method='POST' enctype='multipart/form-data' class='import-form-inline'>";
        $content .= "<input type='file' name='csv_file' accept='.csv' required>";
        $content .= "<button type='submit' class='btn btn-secondary'>Импортировать из CSV</button>";
        $content .= "</form>";
        $content .= "</div>";

        // =================================================================
        // НАЧАЛО: ВРЕМЕННО ОТКЛЮЧЕННАЯ ПАНЕЛЬ МАССОВЫХ ДЕЙСТВИЙ
        // =================================================================
        if (false) {
            $content .= "<div id='bulk-actions-panel' class='bulk-actions-panel' style='display: none;'>";
            $content .= "<span>Выбрано: <strong id='selected-count'>0</strong></span>";
            $content .= "<select id='bulk-action-select'>";
            $content .= "<option value=''>-- Выбрать действие --</option>";
            $content .= "<option value='deactivate'>Деактивировать</option>";
            $content .= "<option value='activate'>Активировать</option>";
            $content .= "<option value='change_position'>Изменить должность</option>";
            $content .= "<option value='change_department'>Изменить отделение</option>";
            $content .= "</select>";
            
            // Выпадающий список для должностей
            $content .= "<select id='bulk-position-select' style='display:none;'>";
            $content .= "<option value=''>-- Новая должность --</option>";
            foreach ($positions as $pos) {
                $content .= "<option value='{$pos['id']}'>" . htmlspecialchars($pos['name']) . "</option>";
            }
            $content .= "</select>";

            // Выпадающий список для отделений
            $content .= "<select id='bulk-department-select' class='bulk-action-select' style='display:none;'>";
            $content .= "<option value=''>-- Новое отделение --</option>";
            foreach ($departments as $dept) {
                $content .= "<option value='{$dept['id']}'>" . htmlspecialchars($dept['name']) . "</option>";
            }
            $content .= "</select>";

            $content .= "<button id='bulk-action-submit' class='btn'>Выполнить</button>";
            $content .= "</div>";
        }
        // =================================================================
        // КОНЕЦ: ВРЕМЕННО ОТКЛЮЧЕННАЯ ПАНЕЛЬ МАССОВЫХ ДЕЙСТВИЙ
        // =================================================================

        // =================================================================
        // НАЧАЛО: ВРЕМЕННО ОТКЛЮЧЕННАЯ ФОРМА С ТАБЛИЦЕЙ
        // =================================================================
        if (false) {
            $content .= "<form id='bulk-form' action='/public/employees/bulk-action' method='POST'>";
            $content .= "<table class='data-table'>";
            $content .= "<thead><tr><th><input type='checkbox' id='select-all-checkbox'></th><th>ФИО</th><th>Телефон</th><th>Должность</th><th>Отделение</th><th>Статус</th><th>Действия</th></tr></thead>";
            $content .= "<tbody>";
            if (empty($employees)) {
                $content .= "<tr><td colspan='7'>Сотрудники не найдены.</td></tr>";
            } else {
                foreach ($employees as $employee) {
                    $status_class = $employee['status'] === 'active' ? 'status-active' : 'status-inactive';
                    $status_text = $employee['status'] === 'active' ? 'Активен' : 'Неактивен';
                    
                    $content .= "<tr>";
                    $content .= "<td><input type='checkbox' class='row-checkbox' name='selected_ids[]' value='{$employee['id']}'></td>";
                    $content .= "<td>" . htmlspecialchars($employee['full_name']) . "</td>";
                    $content .= "<td>" . htmlspecialchars($employee['phone_number'] ?? 'Не указан') . "</td>";
                    $content .= "<td>" . htmlspecialchars($employee['position_name'] ?? 'Не указано') . "</td>";
                    $content .= "<td>" . htmlspecialchars($employee['department_name'] ?? 'Не указано') . "</td>";
                    $content .= "<td><span class='status-badge {$status_class}'>{$status_text}</span></td>";
                    $content .= "<td>";
                    $content .= "<a href='/public/employees/edit?id={$employee['id']}' class='btn btn-sm btn-primary'>Редактировать</a> ";
                    $content .= "<a href='/public/employees/delete?id={$employee['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Вы уверены, что хотите удалить сотрудника &quot;{$employee['full_name']}&quot;? Это действие необратимо.');\">Удалить</a> ";
                        if ($employee['status'] === 'active') {
                    $content .= "<a href='/public/employees/deactivate?id={$employee['id']}' class='btn btn-sm btn-warning' onclick=\"return confirm('Вы уверены?');\">Деактивировать</a>";
                        } else {
                    $content .= "<a href='/public/employees/activate?id={$employee['id']}' class='btn btn-sm btn-success' onclick=\"return confirm('Вы уверены?');\">Активировать</a>";
                    }
                    $content .= "</td>";
                    $content .= "</tr>";
                }
            }
            $content .= "</tbody></table>";
            $content .= "</form>";
        }
        // =================================================================
        // КОНЕЦ: ВРЕМЕННО ОТКЛЮЧЕННАЯ ФОРМА С ТАБЛИЦЕЙ
        // =================================================================

        // --- НОВАЯ ТАБЛИЦА БЕЗ ЧЕКБОКСОВ ---
        $content .= "<table class='data-table'>";
        $content .= "<thead><tr><th>ФИО</th><th>Телефон</th><th>Должность</th><th>Отделение</th><th>Статус</th><th>Действия</th></tr></thead>";
        $content .= "<tbody>";
        if (empty($employees)) {
            $content .= "<tr><td colspan='6'>Сотрудники не найдены.</td></tr>";
        } else {
            foreach ($employees as $employee) {
                $status_class = $employee['status'] === 'active' ? 'status-active' : 'status-inactive';
                $status_text = $employee['status'] === 'active' ? 'Активен' : 'Неактивен';
                
                $content .= "<tr>";
                $content .= "<td>" . htmlspecialchars($employee['full_name']) . "</td>";
                $content .= "<td>" . htmlspecialchars($employee['phone_number'] ?? 'Не указан') . "</td>";
                $content .= "<td>" . htmlspecialchars($employee['position_name'] ?? 'Не указано') . "</td>";
                $content .= "<td>" . htmlspecialchars($employee['department_name'] ?? 'Не указано') . "</td>";
                $content .= "<td><span class='status-badge {$status_class}'>{$status_text}</span></td>";
                
                // =================================================================
                // ИЗМЕНЕННЫЙ БЛОК: КНОПКИ ДЕЙСТВИЙ
                // =================================================================
                $content .= "<td>";
                $content .= "<a href='/public/employees/edit?id={$employee['id']}' class='btn btn-sm btn-primary'>Редактировать</a> ";
                // --- НОВАЯ ПРОВЕРКА: НЕ ПОКАЗЫВАТЬ КНОПКУ УДАЛЕНИЯ ДЛЯ РАЗРАБОТЧИКА ---
                if ($employee['role_name'] !== 'developer') {
                    $content .= "<a href='/public/employees/delete?id={$employee['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Вы уверены, что хотите удалить сотрудника &quot;{$employee['full_name']}&quot;? Это действие необратимо.');\">Удалить</a> ";
                }
                // --- КОНЕЦ ПРОВЕРКИ ---
                if ($employee['status'] === 'active') {
                    $content .= "<a href='/public/employees/deactivate?id={$employee['id']}' class='btn btn-sm btn-warning' onclick=\"return confirm('Вы уверены?');\">Деактивировать</a>";
                } else {
                    $content .= "<a href='/public/employees/activate?id={$employee['id']}' class='btn btn-sm btn-success' onclick=\"return confirm('Вы уверены?');\">Активировать</a>";
                }
                $content .= "</td>";
                // =================================================================
                // КОНЕЦ ИЗМЕНЕННОГО БЛОКА
                // =================================================================
                
                $content .= "</tr>";
            }
        }
        $content .= "</tbody></table>";

        $title = "Панель управления сотрудниками";
        require __DIR__ . '/../Views/layout.php';
    }

    /*
     * =================================================================
     * НАЧАЛО: ВРЕМЕННО ОТКЛЮЧЕННЫЙ МЕТОД ОБРАБОТКИ МАССОВЫХ ДЕЙСТВИЙ
     * =================================================================
    public function bulkAction() {
        if (!Auth::hasRole('director') || Auth::hasRole('developer')) {
            $_SESSION['flash_error'] = 'У вас нет прав для выполнения этого действия.';
            header('Location: /public/employees');
            exit();
        }

        $selectedIds = $_POST['selected_ids'] ?? [];
        $action = $_POST['bulk_action'] ?? '';
        $newPositionId = (int)($_POST['new_position_id'] ?? 0);
        $newDepartmentId = (int)($_POST['new_department_id'] ?? 0);

        if (empty($selectedIds) || empty($action)) {
            $_SESSION['flash_error'] = 'Не выбраны сотрудники или действие.';
            header('Location: /public/employees');
            exit();
        }

        $pdo = Database::getInstance();
        $placeholders = rtrim(str_repeat('?,', count($selectedIds)), ',');
        $updatedCount = 0;

        try {
            $pdo->beginTransaction();

            switch ($action) {
                case 'activate':
                case 'deactivate':
                    $newStatus = ($action === 'activate') ? 'active' : 'inactive';
                    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$newStatus], $selectedIds));
                    $updatedCount = $stmt->rowCount();
                    break;

                case 'change_position':
                    if ($newPositionId === 0) throw new Exception("Не выбрана новая должность.");
                    $stmt = $pdo->prepare("UPDATE users SET position_id = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$newPositionId], $selectedIds));
                    $updatedCount = $stmt->rowCount();
                    break;

                case 'change_department':
                    if ($newDepartmentId === 0) throw new Exception("Не выбрано новое отделение.");
                    $stmt = $pdo->prepare("UPDATE users SET department_id = ? WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$newDepartmentId], $selectedIds));
                    $updatedCount = $stmt->rowCount();
                    break;
                
                default:
                    throw new Exception("Неизвестное действие.");
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Операция успешно выполнена. Обновлено записей: {$updatedCount}";

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = "Ошибка при выполнении операции: " . $e->getMessage();
        }

        header('Location: /public/employees');
        exit();
    }
    */
    // =================================================================
    // КОНЕЦ: ВРЕМЕННО ОТКЛЮЧЕННЫЙ МЕТОД ОБРАБОТКИ МАССОВЫХ ДЕЙСТВИЙ
    // =================================================================

    /**
     * Деактивировать сотрудника
     */
    public function deactivate() {
        $id = $_GET['id'] ?? null;
        $employee = Employee::findById((int)$id);
        if ($employee) {
            $employee->status = 'inactive';
            $employee->save();
            $_SESSION['flash_success'] = "Сотрудник '{$employee->full_name}' деактивирован.";
        } else {
            $_SESSION['flash_error'] = "Сотрудник не найден.";
        }
        header('Location: /public/employees');
        exit();
    }

    /**
     * Активировать сотрудника
     */
    public function activate() {
        $id = $_GET['id'] ?? null;
        $employee = Employee::findById((int)$id);
        if ($employee) {
            $employee->status = 'active';
            $employee->save();
            $_SESSION['flash_success'] = "Сотрудник '{$employee->full_name}' активирован.";
        } else {
            $_SESSION['flash_error'] = "Сотрудник не найден.";
        }
        header('Location: /public/employees');
        exit();
    }
    
        /**
     * Показать форму для добавления сотрудника
     */
    public function create() {
        $pdo = Database::getInstance();
        $positions = $pdo->query("SELECT id, name FROM positions ORDER BY name")->fetchAll();
        $departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
        $roles = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll();

        // Готовим переменные для шаблона
        $title = "Добавить нового сотрудника";
        $body_class = 'centered-layout';
        
        // Если есть ошибка валидации, сохраняем ее
        if (isset($error)) {
            $content_error = $error;
        }

        // Включаем буферизацию вывода
        ob_start();
        require __DIR__ . '/../Views/employees/create.php';
        $content = ob_get_clean();

        // Подключаем главный шаблон
        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Сохранить нового сотрудника
     */
    public function store() {
        $full_name = $_POST['full_name'] ?? '';
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $phone_number = $_POST['phone_number'] ?? null;
        $hire_date = $_POST['hire_date'] ?? '';
        $position_id = $_POST['position_id'] ?? null;
        $department_id = $_POST['department_id'] ?? null;
        $role_id = $_POST['role_id'] ?? null;

        if (empty($full_name) || empty($login) || empty($password) || empty($gender) || empty($hire_date) || empty($role_id)) {
            $error = "Пожалуйста, заполните все обязательные поля, включая роль.";
            $pdo = Database::getInstance();
            $positions = $pdo->query("SELECT id, name FROM positions ORDER BY name")->fetchAll();
            $departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
            $roles = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll();
            require __DIR__ . '/../Views/employees/create.php';
            return;
        }

        if (Employee::findByLogin($login)) {
            $error = "Пользователь с таким логином уже существует.";
            $pdo = Database::getInstance();
            $positions = $pdo->query("SELECT id, name FROM positions ORDER BY name")->fetchAll();
            $departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
            $roles = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll();
            require __DIR__ . '/../Views/employees/create.php';
            return;
        }

        $employee = new Employee();
        $employee->full_name = $full_name;
        $employee->login = $login;
        $employee->password_hash = password_hash($password, PASSWORD_DEFAULT);
        $employee->gender = $gender;
        $employee->phone_number = $phone_number;
        $employee->hire_date = $hire_date;
        $employee->position_id = $position_id;
        $employee->department_id = $department_id;

        if ($employee->save()) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$pdo->lastInsertId(), $role_id]);
            
            $_SESSION['flash_success'] = "Сотрудник успешно добавлен!";
            header('Location: /public/employees');
            exit();
        } else {
            $error = "Произошла ошибка при сохранении сотрудника.";
            $pdo = Database::getInstance();
            $positions = $pdo->query("SELECT id, name FROM positions ORDER BY name")->fetchAll();
            $departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
            $roles = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll();
            require __DIR__ . '/../Views/employees/create.php';
        }
    }

        /**
     * Удалить сотрудника
     */
    public function delete() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = "Не указан ID сотрудника для удаления.";
            header('Location: /public/employees');
            exit();
        }

        // --- НОВАЯ ПРОВЕРКА: ЗАПРЕЩАЕМ УДАЛЕНИЕ РАЗРАБОТЧИКА ---
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT r.name FROM users e JOIN user_roles ur ON e.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE e.id = ?");
        $stmt->execute([$id]);
        $role_name = $stmt->fetchColumn();

        if ($role_name === 'developer') {
            $_SESSION['flash_error'] = "Нельзя удалить пользователя с ролью 'Главный разработчик'.";
            header('Location: /public/employees');
            exit();
        }
        // --- КОНЕЦ ПРОВЕРКИ ---

        $employee = Employee::findById((int)$id);

        if ($employee) {
            if ($employee->delete()) {
                $_SESSION['flash_success'] = "Сотрудник '{$employee->full_name}' успешно удален.";
            } else {
                $_SESSION['flash_error'] = "Не удалось удалить сотрудника.";
            }
        } else {
            $_SESSION['flash_error'] = "Сотрудник не найден.";
        }

        header('Location: /public/employees');
        exit();
    }

        /**
     * Показать форму для редактирования сотрудника
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /public/employees'); exit(); }

        $employee = Employee::findById((int)$id);
        if (!$employee) { header('Location: /public/employees'); exit(); }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
        $stmt->execute([$id]);
        $employee->role_id = $stmt->fetchColumn();

        $positions = $pdo->query("SELECT id, name FROM positions ORDER BY name")->fetchAll();
        $departments = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
        $roles = $pdo->query("SELECT id, name FROM roles ORDER BY name")->fetchAll();

        // Готовим переменные для шаблона
        $title = "Редактировать сотрудника: " . htmlspecialchars($employee->full_name);
        $body_class = 'centered-layout';

        // Включаем буферизацию вывода, чтобы "поймать" HTML из вида
        ob_start();
        require __DIR__ . '/../Views/employees/edit.php';
        $content = ob_get_clean(); // Получаем весь HTML из файла в переменную

        // Подключаем главный шаблон, который уже выведет все
        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * Экспортировать список сотрудников в CSV-файл
     */
    public function export() {
        // Проверяем права доступа
        if (!Auth::hasRole('director') && !Auth::hasRole('developer')) {
            http_response_code(403);
            echo "<h1>Доступ запрещен</h1>";
            return;
        }

        $pdo = Database::getInstance();

        // Получаем всех сотрудников вместе с их должностями и отделениями
        $sql = "SELECT e.full_name, e.phone_number, p.name as position_name, d.name as department_name, e.status
                FROM users e
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON e.department_id = d.id
                ORDER BY e.full_name";
        
        $stmt = $pdo->query($sql);
        $employees = $stmt->fetchAll();

        // Устанавливаем заголовки для скачивания CSV-файла
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Список сотрудников_' . date('Y-m-d') . '.csv"');
        
        // Открываем поток для вывода в браузер
        $output = fopen('php://output', 'w');

        // Добавляем BOM для корректного отображения кириллицы в Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Добавляем заголовки (названия колонок), используя ';' как разделитель
        fputcsv($output, ['ФИО', 'Телефон', 'Должность', 'Отделение', 'Статус'], ';');

        // Добавляем данные сотрудников, также используя ';' как разделитель
        foreach ($employees as $employee) {
            fputcsv($output, [
                $employee['full_name'],
                $employee['phone_number'] ?? 'Не указан',
                $employee['position_name'] ?? 'Не указано',
                $employee['department_name'] ?? 'Не указано',
                ($employee['status'] === 'active') ? 'Активен' : 'Неактивен'
            ], ';');
        }

        // Закрываем поток
        fclose($output);
        exit(); // Важно завершить скрипт, чтобы ничего лишнего не попало в файл
    }
     /**
     * Обновить данные сотрудника
     */
    public function update() {
        $id = $_POST['id'] ?? null;
        $employee = Employee::findById((int)$id);
        if (!$employee) { $_SESSION['flash_error'] = 'Сотрудник не найден'; header('Location: /public/employees'); exit(); }

        $employee->full_name = $_POST['full_name'];
        $employee->login = $_POST['login'];
        $employee->gender = $_POST['gender'];
        $employee->phone_number = $_POST['phone_number'];
        $employee->hire_date = $_POST['hire_date'];
        $employee->position_id = $_POST['position_id'];
        $employee->department_id = $_POST['department_id'];

        if ($employee->save()) {
            $pdo = Database::getInstance();
            $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$id, $_POST['role_id']]);
            
            $_SESSION['flash_success'] = "Данные сотрудника '{$employee->full_name}' обновлены!";
        } else {
            $_SESSION['flash_error'] = "Не удалось обновить данные сотрудника.";
        }

        header('Location: /public/employees');
        exit();
    }

    /**
     * Импортировать сотрудников из CSV-файла
     */
    public function import() {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Ошибка при загрузке файла. Пожалуйста, попробуйте еще раз.';
            header('Location: /public/employees');
            exit();
        }

        $filePath = $_FILES['csv_file']['tmp_name'];
        $pdo = Database::getInstance();
        $importedCount = 0;
        $errors = [];
        $generatedAccounts = [];

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            fgetcsv($handle, 1000, ';');

            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                foreach ($data as $key => $value) {
                    $encoding = mb_detect_encoding($value, ['UTF-8', 'Windows-1251', 'ASCII'], true);
                    if ($encoding && $encoding !== 'UTF-8') {
                        $data[$key] = mb_convert_encoding($value, 'UTF-8', $encoding);
                    }
                }

                $full_name = trim($data[0] ?? '');
                $phone_number = trim($data[1] ?? '');
                $position_name = trim($data[2] ?? '');
                $department_name = trim($data[3] ?? '');
                $status_text = trim($data[4] ?? '');

                if (empty($full_name)) {
                    $errors[] = "Пропущена строка с пустым ФИО.";
                    continue;
                }
                
                if (Employee::findByFullName($full_name)) {
                    $errors[] = "Сотрудник с ФИО '{$full_name}' уже существует.";
                    continue;
                }

                $login = $this->generateLogin($full_name);
                $default_password = 'password123';
                $password_hash = password_hash($default_password, PASSWORD_DEFAULT);
                $generatedAccounts[] = "Сотрудник '{$full_name}': Логин - {$login}, Пароль - {$default_password}";

                $position_id = null;
                if (!empty($position_name)) {
                    $stmt = $pdo->prepare("SELECT id FROM positions WHERE name = ?");
                    $stmt->execute([$position_name]);
                    $position_id = $stmt->fetchColumn();
                    if (!$position_id) {
                        $errors[] = "Должность '{$position_name}' для сотрудника '{$full_name}' не найдена в базе.";
                        continue;
                    }
                }

                $department_id = null;
                if (!empty($department_name)) {
                    $stmt = $pdo->prepare("SELECT id FROM departments WHERE name = ?");
                    $stmt->execute([$department_name]);
                    $department_id = $stmt->fetchColumn();
                    if (!$department_id) {
                        $errors[] = "Отделение '{$department_name}' для сотрудника '{$full_name}' не найдено в базе.";
                        continue;
                    }
                }

                $status = ($status_text === 'Активен') ? 'active' : 'inactive';

                $employee = new Employee();
                $employee->full_name = $full_name;
                $employee->login = $login;
                $employee->password_hash = $password_hash;
                $employee->phone_number = $phone_number ?: null;
                $employee->position_id = $position_id;
                $employee->department_id = $department_id;
                $employee->status = $status;
                $employee->gender = 'male';
                $employee->hire_date = date('Y-m-d');
                
                if ($employee->save()) {
                    $importedCount++;
                    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, (SELECT id FROM roles WHERE name = 'employee'))");
                    $stmt->execute([$pdo->lastInsertId()]);
                } else {
                    $errors[] = "Не удалось сохранить сотрудника '{$full_name}'.";
                }
            }
            fclose($handle);
        }

        $message = "Импорт завершен. Успешно добавлено: {$importedCount}.";
        if (!empty($generatedAccounts)) {
            $message .= "<br><br>Для новых сотрудников были сгенерированы учетные данные:<br>" . implode('<br>', $generatedAccounts);
        }
        if (!empty($errors)) {
            $message .= "<br><br>Обнаружены ошибки:<br>" . implode('<br>', $errors);
            $_SESSION['flash_error'] = $message;
        } else {
            $_SESSION['flash_success'] = $message;
        }

        header('Location: /public/employees');
        exit();
    }

    /**
     * Вспомогательная функция для генерации логина из ФИО
     */
    private function generateLogin($full_name) {
        $pdo = Database::getInstance();
        $login = $this->transliterate($full_name);
        $login = strtolower($login);
        $login = preg_replace('/[^a-z0-9_]/', '', $login);
        
        $base_login = $login;
        $counter = 1;
        
        while (Employee::findByLogin($login)) {
            $login = $base_login . $counter;
            $counter++;
        }
        
        return $login;
    }

    /**
     * Простая функция транслитерации
     */
    private function transliterate($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($string, $converter);
  

        // 6. Формируем сообщение о результате
        $message = "Импорт завершен. Успешно добавлено: {$importedCount}.";
        if (!empty($errors)) {
            $message .= "<br><br>Обнаружены ошибки:<br>" . implode('<br>', $errors);
            $_SESSION['flash_error'] = $message;
        } else {
            $_SESSION['flash_success'] = $message;
        }

        header('Location: /public/employees');
        exit();
    }

        /**
     * Сбросить пароль для сотрудника (для AJAX-запроса)
     */
    public function resetPassword() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $employeeId = $input['id'] ?? null;

        if (!$employeeId) {
            echo json_encode(['success' => false, 'message' => 'ID сотрудника не передан.']);
            return;
        }

        $employee = Employee::findById((int)$employeeId);
        if (!$employee) {
            echo json_encode(['success' => false, 'message' => 'Сотрудник не найден.']);
            return;
        }

        // Генерируем надежный пароль
        $newPassword = bin2hex(random_bytes(8)); // Генерирует 16-символьный пароль
        $employee->password_hash = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($employee->save()) {
            echo json_encode(['success' => true, 'password' => $newPassword]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не удалось обновить пароль в базе данных.']);
        }
    }

} 