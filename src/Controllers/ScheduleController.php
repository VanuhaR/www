<?php
// src/Controllers/ScheduleController.php

require_once __DIR__ . '/../Auth.php';

class ScheduleController {

    /**
     * Показать страницу управления графиками для старшей медсестры
     */
    public function index() {
        // Проверяем права доступа. Только старшая медсестра или разработчик могут сюда заходить.
        if (!Auth::hasRole('head_nurse') && !Auth::hasRole('developer')) {
            http_response_code(403);
            echo "<h1>Доступ запрещен</h1>";
            return;
        }

        $pdo = Database::getInstance();

        // 1. Получаем список всех групп графиков для выпадающего списка
        $stmt = $pdo->query("SELECT sg.id, sg.name FROM schedule_groups sg ORDER BY sg.id");
        $allGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scheduleGroups = [];
        foreach ($allGroups as $group) {
            $groupId = $group['id'];
            
            // Получаем должности и отделения для этой группы
            $stmt = $pdo->prepare(
                "SELECT p.name as position_name, d.name as department_name 
                 FROM position_schedule_group psg
                 JOIN positions p ON psg.position_id = p.id
                 LEFT JOIN users u ON u.position_id = p.id AND u.status = 'active'
                 LEFT JOIN departments d ON u.department_id = d.id
                 WHERE psg.schedule_group_id = ?
                 GROUP BY p.name, d.name"
            );
            $stmt->execute([$groupId]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($details)) {
                continue; // Пропускаем группы, за которыми не закреплено ни одного сотрудника
            }

            $positions = [];
            $departments = [];
            foreach ($details as $detail) {
                $positions[] = htmlspecialchars($detail['position_name']);
                if ($detail['department_name']) {
                    $departments[] = htmlspecialchars($detail['department_name']);
                }
            }

            // Формируем красивое название
            $positionsStr = implode(' + ', array_unique($positions));
            $departmentsStr = '';
            if (!empty($departments)) {
                $departmentsStr = ' (' . implode(', ', array_unique($departments)) . ')';
            }
            
            $scheduleGroups[] = [
                'id' => $groupId,
                'name' => $group['name'],
                'full_name' => $positionsStr . $departmentsStr
            ];
        }

        $title = "Управление графиками";
        $body_class = 'centered-layout'; // Используем наш класс для центрирования

        ob_start();
        require __DIR__ . '/../Views/schedule/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layout.php';
    }

    /**
     * AJAX-метод для загрузки списка сотрудников по ID группы
     */
    public function loadEmployeesByGroup() {
        header('Content-Type: application/json');

        $groupId = $_GET['group_id'] ?? null;
        if (!$groupId) {
            echo json_encode(['success' => false, 'message' => 'ID группы не передан.']);
            return;
        }

        $pdo = Database::getInstance();
        
        // Получаем должности, принадлежащие этой группе
        $stmt = $pdo->prepare("SELECT p.id FROM position_schedule_group psg JOIN positions p ON psg.position_id = p.id WHERE psg.schedule_group_id = ?");
        $stmt->execute([$groupId]);
        $positionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($positionIds)) {
            echo json_encode(['success' => false, 'message' => 'Для этой группы не назначены должности.']);
            return;
        }

                // Получаем всех активных сотрудников с этими должностями
        $placeholders = implode(',', array_fill(0, count($positionIds), '?'));
        $stmt = $pdo->prepare(
            "SELECT u.id, u.full_name 
             FROM users u 
             WHERE u.status = 'active' AND u.position_id IN ($placeholders)
             ORDER BY u.full_name"
        );
        $stmt->execute($positionIds);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'employees' => $employees]);
    }

    /**
     * AJAX-метод для загрузки сохраненного графика
     */
    public function loadScheduleData() {
        header('Content-Type: application/json');

        $groupId = $_GET['group_id'] ?? null;
        $month = $_GET['month'] ?? null; // Формат '2023-10'
        if (!$groupId || !$month) {
            echo json_encode(['success' => false, 'message' => 'Не все параметры переданы.']);
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare(
            "SELECT u.full_name, s.date, s.shift_type
             FROM schedules s
             JOIN users u ON s.user_id = u.id
             JOIN user_roles ur ON u.id = ur.user_id
             JOIN roles r ON ur.role_id = r.id
             JOIN position_schedule_group psg ON u.position_id = psg.position_id
             WHERE psg.schedule_group_id = ? AND DATE_FORMAT(s.date, '%Y-%m') = ?"
        );
        $stmt->execute([$groupId, $month]);
        $scheduleData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Преобразуем в удобный для JS формат: ['user_name' => ['date' => 'shift_type']]
        $formattedData = [];
        foreach ($scheduleData as $row) {
            $formattedData[$row['full_name']][$row['date']] = $row['shift_type'];
        }

        echo json_encode(['success' => true, 'schedule' => $formattedData]);
    }

    /*
    // Здесь будет метод для сохранения графика. Мы добавим его на следующем шаге.
    public function saveSchedule() {
        // ... код для сохранения ...
    }
    */
}