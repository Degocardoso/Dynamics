<?php
require_once __DIR__ . '/../auth/session.php';

// Apenas admin pode acessar
Auth::requireRole('admin');

$message = '';
$messageType = '';

// Processa ações (criar, editar, deletar, toggle status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $result = User::create(
                $_POST['username'] ?? '',
                $_POST['password'] ?? '',
                $_POST['nome_completo'] ?? '',
                $_POST['email'] ?? '',
                $_POST['role'] ?? 'gerador'
            );
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;

        case 'update':
            $data = [
                'nome_completo' => $_POST['nome_completo'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? 'gerador',
            ];
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            $result = User::update($_POST['id'] ?? 0, $data);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;

        case 'toggle_status':
            $userId = $_POST['id'] ?? 0;
            $user = User::find($userId);
            if ($user) {
                $result = User::update($userId, ['ativo' => $user['ativo'] ? 0 : 1]);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'danger';
            }
            break;

        case 'delete':
            $result = User::delete($_POST['id'] ?? 0);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;
    }
}

// Lista todos os usuários
$usuarios = User::all();

// Traduz roles
$roleNames = [
    'admin' => 'Administrador',
    'gerador' => 'Gerador',
    'report' => 'Relatórios'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Sistema de Emails Dynamics</title>
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
        }
        .table-actions button {
            margin: 0 2px;
        }
        .badge-role {
            padding: 6px 12px;
            border-radius: 20px;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-envelope-fill me-2"></i>
                Sistema de Emails Dynamics
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle me-2"></i>
                    <?= htmlspecialchars(Auth::name()) ?>
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
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
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Gerenciar Usuários</li>
                </ol>
            </nav>

            <!-- Alert Messages -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-people me-2"></i>
                    Gerenciar Usuários
                </h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
                    <i class="bi bi-person-plus me-2"></i>
                    Novo Usuário
                </button>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>Nome Completo</th>
                                    <th>Email</th>
                                    <th>Nível de Acesso</th>
                                    <th>Status</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['nome_completo']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge badge-role bg-<?= $user['role'] === 'admin' ? 'primary' : ($user['role'] === 'gerador' ? 'success' : 'info') ?>">
                                            <?= $roleNames[$user['role']] ?? $user['role'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-primary" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-<?= $user['ativo'] ? 'warning' : 'success' ?>"
                                                    onclick="return confirm('Deseja <?= $user['ativo'] ? 'desativar' : 'ativar' ?> este usuário?')">
                                                <i class="bi bi-<?= $user['ativo'] ? 'pause' : 'play' ?>-circle"></i>
                                            </button>
                                        </form>
                                        <?php if ($user['id'] != Auth::id()): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Deseja realmente deletar este usuário?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create User -->
    <div class="modal fade" id="modalCreateUser" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Novo Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Usuário *</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" name="nome_completo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível de Acesso *</label>
                            <select class="form-select" name="role" required>
                                <option value="gerador">Gerador de Emails</option>
                                <option value="report">Relatórios</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div class="modal fade" id="modalEditUser" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>
                        Editar Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Usuário</label>
                            <input type="text" class="form-control" id="edit_username" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" name="nome_completo" id="edit_nome_completo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível de Acesso *</label>
                            <select class="form-select" name="role" id="edit_role" required>
                                <option value="gerador">Gerador de Emails</option>
                                <option value="report">Relatórios</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_nome_completo').value = user.nome_completo;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;

            const modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
            modal.show();
        }
    </script>
</body>
</html>
