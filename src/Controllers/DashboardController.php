<?php
// src/Controllers/DashboardController.php

require_once __DIR__ . '/../Auth.php';

class DashboardController {
    
    public function show() {
        $content = "<h2>Главная панель</h2>";

        if (Auth::hasRole('director') || Auth::hasRole('developer')) {
            // Контент для Директора
            $content .= "<h3>Панель директора</h3>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Общая информация</h4>";
            $content .= "<p>Всего сотрудников в системе: <strong>[TODO: получить число]</strong></p>";
            $content .= "<p>Ожидают одобрения отпуска: <strong>[TODO: получить число]</strong></p>";
            $content .= "</div>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Быстрые действия</h4>";
            $content .= "<a href='/public/employees'>Управление сотрудниками</a> | ";
            $content .= "<a href='/public/vacations'>Заявки на отпуск</a>";
            $content .= "</div>";

        } elseif (Auth::hasRole('head_nurse')) {
            // Контент для Старшей медсестры
            $content .= "<h3>Панель старшей медсестры</h3>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Сегодня, " . date('d.m.Y') . "</h4>";
            $content .= "<p>Дневных смен: <strong>[TODO: посчитать]</strong></p>";
            $content .= "<p>Ночных смен: <strong>[TODO: посчитать]</strong></p>";
            $content .= "<p>Сотрудников на больничном: <strong>[TODO: посчитать]</strong></p>";
            $content .= "</div>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Быстрые действия</h4>";
            $content .= "<a href='/public/schedule'>Редактировать график</a> | ";
            $content .= "<a href='/public/vacations'>Утвердить отпуска</a>";
            $content .= "</div>";

        } elseif (Auth::hasRole('employee')) {
            // Контент для Сотрудника
            $content .= "<h3>Ваша панель</h3>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Ваша следующая смена</h4>";
            $content .= "<p>[TODO: показать дату и время следующей смены]</p>";
            $content .= "</div>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Информация за текущий месяц</h4>";
            $content .= "<p>Отработано часов: <strong>[TODO: посчитать]</strong></p>";
            $content .= "<p>Норма часов: <strong>[TODO: посчитать]</strong></p>";
            $content .= "</div>";
            $content .= "<div class='dashboard-widget'>";
            $content .= "<h4>Быстрые действия</h4>";
            $content .= "<a href='/public/my-schedule'>Мой график</a> | ";
            $content .= "<a href='/public/payslip'>Расчетный лист</a>";
            $content .= "</div>";
        }

        // Подключаем шаблон и передаем в него контент
        require __DIR__ . '/../Views/layout.php';
    }
}