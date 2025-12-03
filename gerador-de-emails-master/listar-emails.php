<?php
require_once __DIR__ . '/auth/session.php';

// Requer permissão para acessar relatórios (admin ou report)
Auth::requireRole(['admin', 'report']);

/**
 * Função para listar todos os arquivos HTML recursivamente
 */
function listarEmails($dir) {
    $emails = [];

    if (!is_dir($dir)) {
        return $emails;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'html') {
            $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            $emails[] = [
                'path' => $relativePath,
                'name' => $file->getFilename(),
                'date' => $file->getMTime(),
                'size' => $file->getSize(),
                'dir' => dirname($relativePath)
            ];
        }
    }

    // Ordena por data (mais recentes primeiro)
    usort($emails, function($a, $b) {
        return $b['date'] - $a['date'];
    });

    return $emails;
}

$emailsDir = __DIR__ . '/emails';
$emails = listarEmails($emailsDir);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emails Gerados - Sistema de Emails Dynamics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #1aa97f;
            --secondary-color: #1cd09b;
        }
        body {
            background: #f4f7f6;
        }
        .navbar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
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
        }
        .email-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s;
        }
        .email-item:hover {
            background: #f8f9fa;
        }
        .email-item:last-child {
            border-bottom: none;
        }
        .email-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .email-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .btn-view {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-view:hover {
            background: #168a68;
            color: white;
            transform: translateY(-2px);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
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
                    <?= htmlspecialchars(Auth::name()) ?>
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
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Emails Gerados</li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="bi bi-envelope-open me-2"></i>
                        Emails Gerados
                    </h2>
                    <p class="text-muted mb-0">
                        Total de <?= count($emails) ?> email(s) encontrado(s)
                    </p>
                </div>
                <?php if (Auth::canGenerate()): ?>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Criar Novo Email
                </a>
                <?php endif; ?>
            </div>

            <!-- Emails List -->
            <div class="card">
                <?php if (empty($emails)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h4>Nenhum email gerado ainda</h4>
                        <p class="text-muted">
                            Os emails que você criar aparecerão aqui.
                        </p>
                        <?php if (Auth::canGenerate()): ?>
                        <a href="index.php" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-2"></i>
                            Criar Primeiro Email
                        </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="card-body p-0">
                        <?php foreach ($emails as $email): ?>
                        <div class="email-item d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="email-name">
                                    <i class="bi bi-file-earmark-text me-2" style="color: var(--primary-color);"></i>
                                    <?= htmlspecialchars($email['name']) ?>
                                </div>
                                <div class="email-meta">
                                    <i class="bi bi-folder2 me-1"></i>
                                    <?= htmlspecialchars($email['dir']) ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date('d/m/Y H:i', $email['date']) ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-hdd me-1"></i>
                                    <?= number_format($email['size'] / 1024, 2) ?> KB
                                </div>
                            </div>
                            <div>
                                <a href="visualizar.php?arquivo=<?= urlencode($email['path']) ?>"
                                   class="btn btn-view" target="_blank">
                                    <i class="bi bi-eye me-1"></i>
                                    Visualizar
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info Box -->
            <?php if (!empty($emails)): ?>
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Dica:</strong> Clique em "Visualizar" para ver o email e ter a opção de enviá-lo para o Dynamics 365.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
