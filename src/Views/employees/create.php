<!-- src/Views/employees/create.php -->

<div class="edit-form-container">
    <h3>Добавить нового сотрудника</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="/public/employees/create" method="POST" class="edit-form">
        <div class="form-layout">
            <!-- Левая колонка -->
            <div class="form-column">
                <div class="form-group">
                    <label for="full_name">Полное ФИО:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="login">Логин:</label>
                    <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Пол:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="gender" value="male" <?php echo (($_POST['gender'] ?? '') === 'male') ? 'checked' : ''; ?>> Мужской</label>
                        <label><input type="radio" name="gender" value="female" <?php echo (($_POST['gender'] ?? '') === 'female') ? 'checked' : ''; ?>> Женский</label>
                    </div>
                </div>
            </div>

            <!-- Правая колонка -->
            <div class="form-column">
                <div class="form-group">
                    <label for="phone_number">Номер телефона:</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="hire_date">Дата найма:</label>
                    <input type="date" id="hire_date" name="hire_date" value="<?php echo htmlspecialchars($_POST['hire_date'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="position_id">Должность:</label>
                    <select id="position_id" name="position_id" required>
                        <option value="">-- Выберите должность --</option>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?php echo $position['id']; ?>" <?php echo (($_POST['position_id'] ?? '') == $position['id']) ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $department['id']; ?>" <?php echo (($_POST['department_id'] ?? '') == $department['id']) ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $role['id']; ?>" <?php echo (($_POST['role_id'] ?? '') == $role['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Блок с кнопками действий -->
        <div class="form-actions">
            <button type="submit" class="btn">Добавить сотрудника</button>
            <a href="/public/employees" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>