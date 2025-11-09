<!-- src/Views/schedule/index.php -->

<?php require_once __DIR__ . '/../../src/Security/CsrfTokenManager.php'; ?>

<form action="/public/employees/create" method="POST" class="edit-form">
    <!-- Добавляем скрытое поле -->
    <?= CsrfTokenManager::getHiddenInput() ?>
    
    <!-- Все остальные поля формы -->
    <div class="form-column">
        <!-- ... -->
    </div>
</form>

<div class="schedule-container">
    <h3>Управление графиками</h3>

    <!-- Панель управления -->
    <div class="schedule-controls">
        <div class="form-group-inline">
            <label for="group-select">Выберите группу:</label>
            <select id="group-select" name="group_id">
                <option value="">-- Выберите группу --</option>
                <?php foreach ($scheduleGroups as $group): ?>
                    <option value="<?php echo $group['id']; ?>">
                        <?php echo $group['full_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group-inline">
            <label for="month-select">Месяц:</label>
            <input type="month" id="month-select" name="month">
        </div>

        <button id="load-schedule-btn" class="btn">Загрузить</button>
    </div>

        <!-- Панель с действиями над графиком -->
    <div class="schedule-actions" style="display: none;">
        <button id="fill-template-btn" class="btn btn-secondary">Заполнить по шаблону</button>
        <button id="check-staffing-btn" class="btn btn-secondary">Проверить расстановку</button>
        <button id="save-schedule-btn" class="btn">Сохранить график</button>
    </div>

    <!-- ================================================================= -->
    <!-- НОВАЯ ПАНЕЛЬ С КНОПКАМИ СМЕН -->
    <!-- ================================================================= -->
    <div class="shift-palette-container" style="display: none;">
        <span class="palette-title">Текущая ячейка: <strong id="selected-cell-info">не выбрана</strong></span>
        <div class="shift-palette">
            <button class="shift-btn" data-shift="10ч">10ч</button>
            <button class="shift-btn" data-shift="14ч">14ч</button>
            <button class="shift-btn" data-shift="Б">Б</button>
            <button class="shift-btn" data-shift="ОТ">ОТ</button>
            <button class="shift-btn clear-btn" data-shift="">Очистить</button>
        </div>
    </div>

    <!-- Контейнер для таблицы с графиком -->
    <div id="schedule-table-container">
        <!-- Таблица будет сгенерирована здесь с помощью JavaScript -->
    </div>

    <!-- Контейнер для сводки по часам -->
    <div id="hours-summary-container" style="display: none;">
        <!-- Сводка будет сгенерирована здесь -->
    </div>
</div>

<!-- Весь JavaScript для этой страницы будет здесь -->
<script src="/public/assets/js/schedule.js"></script>