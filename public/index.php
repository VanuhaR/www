<?php
// public/index.php

// Подключаем все необходимые классы
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Employee.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/DashboardController.php';
require_once __DIR__ . '/../src/Controllers/EmployeeController.php';
require_once __DIR__ . '/../src/Controllers/ScheduleController.php';
require_once __DIR__ . '/../src/Auth.php';


// Запускаем сессию в самом начале
session_start();

// Получаем URL и метод
 $url = $_GET['url'] ?? '';
 $method = $_SERVER['REQUEST_METHOD'];

 $authController = new AuthController();

// --- Обработка публичных маршрутов (вход, выход) ---
if ($url === 'logout') {
    $authController->logout();
} elseif ($url === 'login') {
    if ($method === 'GET') {
        $authController->showLoginForm();
    } elseif ($method === 'POST') {
        $authController->login();
    }
} else {
    // --- Обработка остальных страниц (требующих авторизации) ---
    
    function isLoggedIn() {
        return !empty($_SESSION['user_id']);
    }

    if (isLoggedIn()) {
        
        if ($url === '') {
            // Главная страница - показываем дашборд
            $dashboardController = new DashboardController();
            $dashboardController->show();

                        } elseif ($url === 'employees') {
            // Страница со списком сотрудников
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                $employeeController->index();
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/create') {
            // Страница добавления нового сотрудника
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'GET') {
                    $employeeController->create();
                } elseif ($method === 'POST') {
                    $employeeController->store();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/deactivate') {
            // Деактивация сотрудника
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'GET') {
                    $employeeController->deactivate();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/activate') {
            // Активация сотрудника
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'GET') {
                    $employeeController->activate();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/delete') {
            // Удаление сотрудника
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'GET') {
                    $employeeController->delete();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/bulk-action') {
            // Массовые действия
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'POST') {
                    $employeeController->bulkAction();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/edit') {
            // Редактирование сотрудника
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'GET') {
                    $employeeController->edit();
                } elseif ($method === 'POST') {
                    $employeeController->update();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/export') {
            // Экспорт сотрудников в CSV
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                $employeeController->export();
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/import') {
            // Импорт сотрудников из CSV
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'POST') {
                    $employeeController->import();
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'employees/reset-password') {
            // Сброс пароля сотрудника (AJAX-запрос)
            if (Auth::hasRole('director') || Auth::hasRole('developer')) {
                $employeeController = new EmployeeController();
                if ($method === 'POST') {
                    $employeeController->resetPassword();
                } else {
                    http_response_code(405); // Method Not Allowed
                    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса.']);
                }
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
            }
            exit(); // Важно завершить выполнение для AJAX-запроса

        } elseif ($url === 'schedule') {
            // Страница управления графиками
            if (Auth::hasRole('head_nurse') || Auth::hasRole('developer')) {
                $scheduleController = new ScheduleController();
                if ($method === 'GET') {
                    $scheduleController->index();
                } elseif ($method === 'POST') {
                    // Здесь будет метод для сохранения
                }
            } else {
                http_response_code(403);
                echo "<h1>Доступ запрещен</h1>";
            }

        } elseif ($url === 'schedule/load-employees') {
            // AJAX-запрос на загрузку сотрудников
            if (Auth::hasRole('head_nurse') || Auth::hasRole('developer')) {
                $scheduleController = new ScheduleController();
                $scheduleController->loadEmployeesByGroup();
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
            }
            exit(); // Важно для AJAX

        } elseif ($url === 'schedule/load-data') {
            // AJAX-запрос на загрузку данных графика
            if (Auth::hasRole('head_nurse') || Auth::hasRole('developer')) {
                $scheduleController = new ScheduleController();
                $scheduleController->loadScheduleData();
            } else {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
            }
            exit(); // Важно для AJAX

        } else {
            // Если ни один из маршрутов не подошел - 404
            http_response_code(404);
            echo "<h1>404 - Страница не найдена</h1>";
        }

    } else {
        // Если пользователь не авторизован, перенаправляем на страницу входа
        $host = $_SERVER['HTTP_HOST'];
        header("Location: http://$host/public/login");
        exit();
    }
} 