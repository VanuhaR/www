<!-- src/Views/employees/edit.php -->

<div class="edit-form-container">
    <h3>Редактировать сотрудника: <?php echo htmlspecialchars($employee->full_name); ?></h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="/public/employees/edit" method="POST" class="edit-form">
        <!-- Скрытое поле с ID сотрудника -->
        <input type="hidden" name="id" value="<?php echo $employee->id; ?>">

        <div class="form-layout">
            <!-- Левая колонка -->
            <div class="form-column">
                <div class="form-group">
                    <label for="full_name">Полное ФИО:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($employee->full_name); ?>" required>
                </div>

                <div class="form-group">
                    <label for="login">Логин:</label>
                    <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($employee->login); ?>" required>
                </div>

                <div class="form-group">
                    <label>Пол:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="male" <?php echo ($employee->gender === 'male') ? 'checked' : ''; ?>> Мужской</label>
                        <label><input type="radio" name="gender" value="female" <?php echo ($employee->gender === 'female') ? 'checked' : ''; ?>> Женский</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone_number">Номер телефона:</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($employee->phone_number ?? ''); ?>">
                </div>
            </div>

            <!-- Правая колонка -->
            <div class="form-column">
                <div class="form-group">
                    <label for="hire_date">Дата найма:</label>
                    <input type="date" id="hire_date" name="hire_date" value="<?php echo htmlspecialchars($employee->hire_date); ?>" required>
                </div>

                <div class="form-group">
                    <label for="position_id">Должность:</label>
                    <select id="position_id" name="position_id" required>
                        <option value="">-- Выберите должность --</option>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?php echo $position['id']; ?>" <?php echo ($employee->position_id == $position['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($position['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="department_id">Отделение:</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">-- Выберите отделение --</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo $department['id']; ?>" <?php echo ($employee->department_id == $department['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($department['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="role_id">Роль в системе:</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">-- Выберите роль --</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo ($employee->role_id == $role['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Блок с кнопками действий -->
        <div class="form-actions">
            <button type="submit" class="btn">Сохранить изменения</button>
            <button type="button" id="reset-password-btn" class="btn btn-warning" data-id="<?php echo $employee->id; ?>">
                Сгенерировать новый пароль
            </button>
            <a href="/public/employees" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

<!-- ================================================================= -->
<!-- JAVASCRIPT ДЛЯ КНОПКИ СБРОСА ПАРОЛЯ -->
<!-- ================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetBtn = document.getElementById('reset-password-btn');

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            const employeeId = this.dataset.id;
            const employeeName = "<?php echo htmlspecialchars($employee->full_name); ?>";

            if (!confirm(`Вы уверены, что хотите сгенерировать новый пароль для сотрудника "${employeeName}"? Текущий пароль будет сброшен.`)) {
                return;
            }

            this.textContent = 'Генерация...';
            this.disabled = true;

            fetch('/public/employees/reset-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id: employeeId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.password) {
                    alert(`Новый пароль для сотрудника "${employeeName}":\n\n${data.password}\n\nСохраните его в надежном месте.`);
                } else {
                    alert('Произошла ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка сети. Попробуйте еще раз.');
            })
            .finally(() => {
                this.textContent = 'Сгенерировать новый пароль';
                this.disabled = false;
            });
        });
    }
});