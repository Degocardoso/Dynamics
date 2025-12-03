<?php
require_once __DIR__ . '/auth/session.php';

// Se já estiver logado, redireciona para o dashboard
if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        if (Auth::login($username, $password)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuário ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Emails Dynamics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1aa97f 0%, #1cd09b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(90deg, #1aa97f, #1cd09b);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .login-body {
            padding: 30px;
        }
        .form-control:focus {
            border-color: #1aa97f;
            box-shadow: 0 0 0 0.2rem rgba(26, 169, 127, 0.25);
        }
        .btn-login {
            background: linear-gradient(90deg, #1aa97f, #1cd09b);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            background: #168a68;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 169, 127, 0.4);
        }
        .input-group-text {
            background: white;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-envelope-fill"></i>
            <h3 class="mb-0">Sistema de Emails</h3>
            <p class="mb-0 small">Dynamics 365</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Digite seu usuário" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Digite sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Entrar
                </button>
            </form>

            <hr class="my-4">

            <div class="text-center text-muted small">
                <p class="mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Usuário padrão: <strong>admin</strong>
                </p>
                <p class="mb-0">
                    Senha padrão: <strong>admin123</strong>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
