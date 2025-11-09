// public/assets/js/schedule.js

document.addEventListener('DOMContentLoaded', function() {
    const groupSelect = document.getElementById('group-select');
    const monthSelect = document.getElementById('month-select');
    const loadBtn = document.getElementById('load-schedule-btn');
    const tableContainer = document.getElementById('schedule-table-container');
    const actionsPanel = document.querySelector('.schedule-actions');

    // Устанавливаем текущий месяц в поле выбора по умолчанию
    const today = new Date();
    monthSelect.value = today.toISOString().slice(0, 7);

    // Функция для генерации таблицы с группировкой по отделениям
    function generateTable(employees) {
        if (employees.length === 0) {
            tableContainer.innerHTML = '<p>В этой группе нет сотрудников.</p>';
            return;
        }

        // --- Группировка сотрудников по отделениям ---
        const employeesByDepartment = employees.reduce((acc, emp) => {
            const dept = emp.department_name || 'Без отдела';
            if (!acc[dept]) {
                acc[dept] = [];
            }
            acc[dept].push(emp);
            return acc;
        }, {});
        // --- Конец группировки ---

        // Получаем количество дней в выбранном месяце
        const year = parseInt(monthSelect.value.split('-')[0]);
        const month = parseInt(monthSelect.value.split('-')[1]);
        const daysInMonth = new Date(year, month, 0).getDate();

        let html = `
            <table class="data-table schedule-table">
                <thead>
                    <tr>
                        <th>ФИО</th>
        `;

        // Создаем заголовки для каждого дня месяца
        for (let day = 1; day <= daysInMonth; day++) {
            html += `<th>${day}</th>`;
        }

        html += `
                    </tr>
                </thead>
                <tbody>
        `;

        // --- Генерация строк таблицы ---
        for (const departmentName in employeesByDepartment) {
            const employeesInDept = employeesByDepartment[departmentName];

            // 1. Строка-разделитель для отдела
            html += `
                <tr class="department-separator">
                    <td colspan="${daysInMonth + 1}">${departmentName}</td>
                </tr>
            `;

            // 2. Строки для каждого сотрудника в отделе
            employeesInDept.forEach(emp => {
                html += `
                    <tr data-user-id="${emp.id}" data-user-name="${emp.full_name}">
                        <td>${emp.full_name}</td>
                `;

                // Создаем ячейки для каждого дня месяца
                for (let day = 1; day <= daysInMonth; day++) {
                    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    html += `<td class="schedule-cell" data-date="${dateStr}" data-shift=""></td>`;
                }

                html += `</tr>`;
            });
        }
        // --- Конец генерации ---

        html += `
                </tbody>
            </table>
        `;

        tableContainer.innerHTML = html;
    }

    // Функция для заполнения таблицы сохраненными данными
    function populateTable(scheduleData) {
        for (const userName in scheduleData) {
            const userRow = document.querySelector(`tr[data-user-name="${userName}"]`);
            if (userRow) {
                for (const dateStr in scheduleData[userName]) {
                    const cell = userRow.querySelector(`td[data-date="${dateStr}"]`);
                    if (cell) {
                        cell.dataset.shift = scheduleData[userName][dateStr];
                        cell.textContent = scheduleData[userName][dateStr];
                    }
                }
            }
        }
    }
    
    // Функция для назначения обработчиков клика на ячейки (циклическое переключение)
    function assignCellClickHandlers() {
        const cells = document.querySelectorAll('.schedule-cell');
        const shifts = ['', '10ч', '14ч', 'Б', 'ОТ']; // Цикл смен: пусто -> 10ч -> 14ч -> Б -> ОТ -> пусто

        cells.forEach(cell => {
            cell.addEventListener('click', function() {
                let currentShiftIndex = shifts.indexOf(this.dataset.shift);
                let nextShiftIndex = (currentShiftIndex + 1) % shifts.length;
                
                this.dataset.shift = shifts[nextShiftIndex];
                this.textContent = shifts[nextShiftIndex];
            });
        });
    }

    // Основной обработчик кнопки "Загрузить"
    async function loadSchedule() {
        const groupId = groupSelect.value;
        const month = monthSelect.value;

        if (!groupId || !month) {
            alert('Пожалуйста, выберите группу и месяц.');
            return;
        }

        loadBtn.textContent = 'Загрузка...';
        loadBtn.disabled = true;

        try {
            // 1. Загружаем список сотрудников
            const empResponse = await fetch(`/public/schedule/load-employees?group_id=${groupId}`);
            if (!empResponse.ok) throw new Error('Сетевая ошибка при загрузке сотрудников');
            const empResult = await empResponse.json();

            if (!empResult.success) {
                throw new Error(empResult.message);
            }

            generateTable(empResult.employees);
            
            // 2. Загружаем сохраненные данные графика
            const scheduleResponse = await fetch(`/public/schedule/load-data?group_id=${groupId}&month=${month}`);
            if (!scheduleResponse.ok) throw new Error('Сетевая ошибка при загрузке графика');
            const scheduleResult = await scheduleResponse.json();
            
            if (scheduleResult.success) {
                populateTable(scheduleResult.schedule);
            }

            // 3. Назначаем обработчики и показываем панель действий
            assignCellClickHandlers();
            if (actionsPanel) {
                actionsPanel.style.display = 'flex';
            }

        } catch (error) {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при загрузке данных: ' + error.message);
        } finally {
            loadBtn.textContent = 'Загрузить';
            loadBtn.disabled = false;
        }
    }

    // Назначаем обработчик на кнопку
    if (loadBtn) {
        loadBtn.addEventListener('click', loadSchedule);
    }
});