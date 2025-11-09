// public/assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    // --- Переменные ---
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    // --- Боковое меню ---
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('is-open');
        });
    }

    /*
     * =================================================================
     * НАЧАЛО: ВРЕМЕННО ОТКЛЮЧЕННАЯ ЛОГИКА МАССОВЫХ ДЕЙСТВИЙ
     * Эта часть кода была отключена, так как соответствующие
     * элементы HTML и обработчики на сервере были временно удалены.
     * =================================================================
    
    // --- Переменные для массовых действий ---
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActionsPanel = document.getElementById('bulk-actions-panel');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkActionSelect = document.getElementById('bulk-action-select');
    const bulkPositionSelect = document.getElementById('bulk-position-select');
    const bulkDepartmentSelect = document.getElementById('bulk-department-select');
    const bulkActionSubmit = document.getElementById('bulk-action-submit');
    const bulkForm = document.getElementById('bulk-form');

    // --- Логика массовых действий ---
    function updateBulkActionsPanel() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCountSpan.textContent = count;
        if (count > 0) {
            bulkActionsPanel.style.display = 'flex';
        } else {
            bulkActionsPanel.style.display = 'none';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionsPanel();
        });
    }

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) selectAllCheckbox.checked = false;
            if (document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length) {
                selectAllCheckbox.checked = true;
            }
            updateBulkActionsPanel();
        });
    });

    if (bulkActionSelect) {
        bulkActionSelect.addEventListener('change', function() {
            bulkPositionSelect.style.display = 'none';
            bulkDepartmentSelect.style.display = 'none';
            if (this.value === 'change_position') {
                bulkPositionSelect.style.display = 'inline-block';
            } else if (this.value === 'change_department') {
                bulkDepartmentSelect.style.display = 'inline-block';
            }
        });
    }

    // --- Обработка кнопки "Выполнить" ---
    if (bulkActionSubmit) {
        bulkActionSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            const action = bulkActionSelect.value;
            if (!action) {
                alert('Пожалуйста, выберите действие.');
                return;
            }
            if (action === 'change_position' && !bulkPositionSelect.value) {
                alert('Пожалуйста, выберите новую должность.');
                return;
            }
            if (action === 'change_department' && !bulkDepartmentSelect.value) {
                alert('Пожалуйста, выберите новое отделение.');
                return;
            }

            const formData = new FormData(bulkForm);
            formData.append('bulk_action', action);
            formData.append('new_position_id', bulkPositionSelect.value);
            formData.append('new_department_id', bulkDepartmentSelect.value);

            fetch(bulkForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Произошла ошибка при выполнении операции.');
                }
            })
            .catch(error => {
                alert(`Ошибка сети: ${error.message}`);
            });
        });
    }
    
    */
    // =================================================================
    // КОНЕЦ: ВРЕМЕННО ОТКЛЮЧЕННАЯ ЛОГИКА МАССОВЫХ ДЕЙСТВИЙ
    // =================================================================
});