<!-- src/Views/layout.php -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Система учета времени'; ?></title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<!-- ================================================================= -->
<!-- ВОТ ГЛАВНОЕ ИЗМЕНЕНИЕ: ТЕПЕРЬ МЫ ДОБАВЛЯЕМ КЛАСС К <body> -->
<!-- ================================================================= -->
<body <?php echo isset($body_class) ? 'class="' . $body_class . '"' : ''; ?>>
    <header class="main-header">
        <div class="container">
            <button class="sidebar-toggle" id="sidebar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <h1>Система учета времени</h1>
            <div class="user-info">
                <span>Вы вошли как: <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Гость'); ?></span>
                <a href="/public/logout">Выйти</a>
            </div>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar" id="sidebar">
            <nav>
                <ul>
                    <!-- Общий пункт для всех авторизованных пользователей -->
                    <li><a href="/public/">Главная панель</a></li>

                    <!-- Вкладки Директора -->
                    <?php if (Auth::hasRole('director') || Auth::hasRole('developer')): ?>
                        <li class="nav-group-title">Директор</li>
                        <li><a href="/public/employees">Панель управления</a></li>
                        <li><a href="/public/schedule">Общий график</a></li>
                        <li><a href="/public/vacations">График отпусков</a></li>
                        <li><a href="/public/settings">Настройки</a></li>
                    <?php endif; ?>

                    <!-- Вкладки Старшей медсестры -->
                    <?php if (Auth::hasRole('head_nurse') || Auth::hasRole('developer')): ?>
                        <li class="nav-group-title">Старшая медсестра</li>
                        <li><a href="/public/schedule">Общий график</a></li>
                        <li><a href="/public/my-schedule">Индивидуальный график</a></li>
                        <li><a href="/public/vacations">График отпусков</a></li>
                        <li><a href="/public/payslip">Расчетный лист</a></li>
                    <?php endif; ?>

                    <!-- Вкладки Сотрудника -->
                    <?php if (Auth::hasRole('employee') || Auth::hasRole('developer')): ?>
                        <li class="nav-group-title">Сотрудник</li>
                        <li><a href="/public/schedule">Общий график</a></li>
                        <li><a href="/public/my-schedule">Индивидуальный график</a></li>
                        <li><a href="/public/payslip">Расчетный лист</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <!-- ================================================================= -->
        <!-- ВОТ ВТОРОЕ ИЗМЕНЕНИЕ: ВОЗВРАЩАЕМ <main> К ИСХОДНОМУ ВИДУ -->
        <!-- ================================================================= -->
        <main class="content">
            <!-- Блок для сообщения об успехе -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <?php
                    echo htmlspecialchars($_SESSION['flash_success']);
                    unset($_SESSION['flash_success']); // Удаляем сообщение после показа
                    ?>
                </div>
            <?php endif; ?>

            <!-- Блок для сообщения об ошибке -->
            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-error">
                    <?php
                    echo htmlspecialchars($_SESSION['flash_error']);
                    unset($_SESSION['flash_error']); // Удаляем сообщение после показа
                    ?>
                </div>
            <?php endif; ?>

            <!-- Основной контент страницы -->
            <?php echo $content ?? ''; ?>
        </main>
    </div>

    <script src="/public/assets/js/script.js"></script>
</body>
</html>