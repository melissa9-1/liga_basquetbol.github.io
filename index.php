<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'register') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || strlen($password) < 4) {
            flash('error', 'Usuario y contraseña válidos son obligatorios.');
            redirect('index.php');
        }

        try { 
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
            $stmt->execute([
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            flash('success', 'Usuario registrado correctamente.');
        } catch (Throwable $e) {
            flash('error', 'Ese usuario ya existe.');
            redirect('index.php');
        }

        redirect('index.php');
    }

    if ($action === 'login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username']];
            flash('success', 'Sesión iniciada correctamente.');
            redirect('index.php');
        }

        flash('error', 'Credenciales incorrectas.');
        redirect('index.php');
    }
}

$successMessage = getFlash('success');
$errorMessage = getFlash('error');
$loggedUser = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liga de Básquetbol</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <p class="eyebrow">Panel administrativo</p>
                <h1>Liga de Básquetbol</h1>
            </div>
            <?php if ($loggedUser): ?>
                <div class="topbar-actions">
                    <span class="user-pill"><?= e($loggedUser['username']) ?></span>
                    <a class="button secondary" href="logout.php">Cerrar sesión</a>
                </div>
            <?php endif; ?>
        </header>

        <?php if ($successMessage): ?>
            <div class="alert success"><?= e($successMessage) ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert error"><?= e($errorMessage) ?></div>
        <?php endif; ?>

        <?php if (!$loggedUser): ?>
            <section class="auth-wrapper">
                <div class="panel auth-panel">
                    <div class="tabs">
                        <button class="tab active" type="button" data-target="login-form">Iniciar sesión</button>
                        <button class="tab" type="button" data-target="register-form">Registrarse</button>
                    </div>

                    <form id="login-form" class="auth-form" method="POST" action="index.php">
                        <input type="hidden" name="action" value="login">
                        <label>
                            Usuario
                            <input type="text" name="username" required>
                        </label>
                        <label>
                            Contraseña
                            <input type="password" name="password" required>
                        </label>
                        <button type="submit" class="button primary">Entrar</button>
                    </form>

                    <form id="register-form" class="auth-form hidden" method="POST" action="index.php">
                        <input type="hidden" name="action" value="register">
                        <label>
                            Usuario
                            <input type="text" name="username" required>
                        </label>
                        <label>
                            Contraseña
                            <input type="password" name="password" minlength="4" required>
                        </label>
                        <button type="submit" class="button primary">Registrarme</button>
                    </form>
                </div>
            </section>
        <?php else: ?>
            <main class="dashboard">
                <div class="panel stats-grid">
                    <a class="tile" href="equipos.php">
                        <span class="tile-label">Equipos</span>
                        <strong><?= count(getTeams($pdo)) ?></strong>
                    </a>
                    <a class="tile" href="jugadores.php">
                        <span class="tile-label">Jugadores</span>
                        <strong><?= (int) $pdo->query('SELECT COUNT(*) FROM players')->fetchColumn() ?></strong>
                    </a>
                    <a class="tile" href="partidos.php">
                        <span class="tile-label">Partidos</span>
                        <strong><?= (int) $pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn() ?></strong>
                    </a>
                    <a class="tile" href="estadisticas.php">
                        <span class="tile-label">Estadísticas</span>
                        <strong>Ver</strong>
                    </a>
                </div>

                <div class="panel">
                    <h2>Acciones rápidas</h2>
                    <div class="quick-actions">
                        <a class="button primary" href="equipos.php">Gestionar equipos</a>
                        <a class="button primary" href="jugadores.php">Gestionar jugadores</a>
                        <a class="button primary" href="partidos.php">Registrar partidos</a>
                        <a class="button secondary" href="estadisticas.php">Ver estadísticas</a>
                    </div>
                </div>
            </main>
        <?php endif; ?>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
