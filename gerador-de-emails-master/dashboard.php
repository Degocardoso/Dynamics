<?php
require_once __DIR__ . '/auth/session.php';

// Requer autenticação
Auth::requireAuth();

$userName = Auth::name();
$userRole = Auth::role();

// Traduz o role para exibição
$roleNames = [
    'admin' => 'Administrador',
    'gerador' => 'Gerador de Emails',
    'report' => 'Relatórios'
];
$roleName = $roleNames[$userRole] ?? $userRole;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Emails Dynamics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        .main-content {
            padding: 30px 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .card-gerador {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-report {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .card-admin {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            margin-bottom: 30px;
        }
        .btn-action {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .badge-role {
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-envelope-fill me-2"></i>
                Sistema de Emails Dynamics
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle me-2"></i>
                    <?= htmlspecialchars($userName) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Sair
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Welcome Card -->
            <div class="card welcome-card">
                <div class="card-body p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-hand-wave me-2"></i>
                        Bem-vindo, <?= htmlspecialchars($userName) ?>!
                    </h2>
                    <p class="mb-2">
                        Nível de acesso:
                        <span class="badge bg-white text-dark badge-role">
                            <i class="bi bi-shield-check me-1"></i>
                            <?= htmlspecialchars($roleName) ?>
                        </span>
                    </p>
                    <p class="mb-0 opacity-75">Escolha uma das opções abaixo para começar.</p>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="row g-4">
                <!-- Gerador de Emails -->
                <?php if (Auth::canGenerate()): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body p-4 text-center">
                            <div class="card-icon card-gerador text-white mx-auto">
                                <i class="bi bi-envelope-plus"></i>
                            </div>
                            <h4 class="card-title mb-3">Gerar Emails</h4>
                            <p class="text-muted mb-4">
                                Crie e personalize emails profissionais com nosso editor intuitivo.
                                Adicione conteúdo, botões e tabelas facilmente.
                            </p>
                            <a href="index.php" class="btn btn-primary btn-action">
                                <i class="bi bi-plus-circle me-2"></i>
                                Criar Novo Email
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Visualizar e Enviar (Report) -->
                <?php if (Auth::canReport()): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body p-4 text-center">
                            <div class="card-icon card-report text-white mx-auto">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h4 class="card-title mb-3">Relatórios e Envios</h4>
                            <p class="text-muted mb-4">
                                Visualize emails gerados e envie para o Dynamics 365.
                                Acompanhe campanhas e gerencie envios.
                            </p>
                            <a href="visualizar.php" class="btn btn-primary btn-action">
                                <i class="bi bi-eye me-2"></i>
                                Visualizar Emails
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Gerenciar Usuários (Admin) -->
                <?php if (Auth::isAdmin()): ?>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body p-4 text-center">
                            <div class="card-icon card-admin text-white mx-auto">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4 class="card-title mb-3">Gerenciar Usuários</h4>
                            <p class="text-muted mb-4">
                                Adicione, edite ou remova usuários do sistema.
                                Defina permissões e controle o acesso às funcionalidades.
                            </p>
                            <a href="admin/usuarios.php" class="btn btn-primary btn-action">
                                <i class="bi bi-person-gear me-2"></i>
                                Gerenciar Usuários
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info Box -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Suas Permissões
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <?php if (Auth::canGenerate()): ?>
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>Gerar Emails</span>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    <span class="text-muted">Gerar Emails</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <?php if (Auth::canReport()): ?>
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>Acessar Relatórios</span>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    <span class="text-muted">Acessar Relatórios</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <?php if (Auth::isAdmin()): ?>
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <span>Gerenciar Usuários</span>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                    <span class="text-muted">Gerenciar Usuários</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
