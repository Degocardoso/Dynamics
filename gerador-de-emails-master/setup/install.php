<?php
/**
 * Script de Instalação Automática
 * Execute este arquivo no navegador para instalar o banco de dados automaticamente
 */

// Configurações do banco de dados
$dbConfig = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'dbname' => 'dynamics_emails',
    'charset' => 'utf8mb4'
];

// Flag de instalação
$installationComplete = false;
$errors = [];
$messages = [];

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Atualiza as configurações com os valores do formulário
    $dbConfig['host'] = $_POST['db_host'] ?? 'localhost';
    $dbConfig['user'] = $_POST['db_user'] ?? 'root';
    $dbConfig['pass'] = $_POST['db_pass'] ?? '';
    $dbConfig['dbname'] = $_POST['db_name'] ?? 'dynamics_emails';

    try {
        // Conecta ao MySQL sem especificar o banco de dados
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}",
            $dbConfig['user'],
            $dbConfig['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $messages[] = "✓ Conexão com MySQL estabelecida com sucesso!";

        // Cria o banco de dados se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['dbname']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $messages[] = "✓ Banco de dados '{$dbConfig['dbname']}' criado/verificado com sucesso!";

        // Seleciona o banco de dados
        $pdo->exec("USE `{$dbConfig['dbname']}`");

        // Cria a tabela de usuários
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                nome_completo VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                role ENUM('admin', 'gerador', 'report') NOT NULL DEFAULT 'gerador',
                ativo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_role (role),
                INDEX idx_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $messages[] = "✓ Tabela 'usuarios' criada com sucesso!";

        // Cria a tabela de logs
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS logs_acesso (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                acao VARCHAR(100) NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                INDEX idx_usuario_id (usuario_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $messages[] = "✓ Tabela 'logs_acesso' criada com sucesso!";

        // Verifica se já existe um usuário admin
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE role = 'admin'");
        $adminCount = $stmt->fetchColumn();

        if ($adminCount == 0) {
            // Cria o usuário admin padrão
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (username, password, nome_completo, email, role, ativo)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'admin',
                password_hash('admin123', PASSWORD_DEFAULT),
                'Administrador do Sistema',
                'admin@example.com',
                'admin',
                1
            ]);
            $messages[] = "✓ Usuário administrador criado com sucesso!";
            $messages[] = "<strong>Credenciais:</strong> Username: <code>admin</code> | Senha: <code>admin123</code>";
        } else {
            $messages[] = "⚠ Já existe um usuário administrador. Nenhum novo usuário foi criado.";
        }

        // Atualiza o arquivo de configuração
        $configContent = "<?php\n";
        $configContent .= "/**\n * Configuração do Banco de Dados\n *\n * IMPORTANTE: Altere as credenciais abaixo conforme seu ambiente\n */\n\n";
        $configContent .= "// Configurações do banco de dados\n";
        $configContent .= "define('DB_HOST', '{$dbConfig['host']}');\n";
        $configContent .= "define('DB_NAME', '{$dbConfig['dbname']}');\n";
        $configContent .= "define('DB_USER', '{$dbConfig['user']}');\n";
        $configContent .= "define('DB_PASS', '{$dbConfig['pass']}');\n";
        $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
        $configContent .= file_get_contents(__DIR__ . '/../config/database.php');

        // Remove as definições duplicadas do arquivo original
        $configContent = preg_replace('/define\([\'"]DB_HOST[\'"],[^\)]+\);[\r\n]*/m', '', $configContent, 1);
        $configContent = preg_replace('/define\([\'"]DB_NAME[\'"],[^\)]+\);[\r\n]*/m', '', $configContent, 1);
        $configContent = preg_replace('/define\([\'"]DB_USER[\'"],[^\)]+\);[\r\n]*/m', '', $configContent, 1);
        $configContent = preg_replace('/define\([\'"]DB_PASS[\'"],[^\)]+\);[\r\n]*/m', '', $configContent, 1);
        $configContent = preg_replace('/define\([\'"]DB_CHARSET[\'"],[^\)]+\);[\r\n]*/m', '', $configContent, 1);

        file_put_contents(__DIR__ . '/../config/database.php', $configContent);
        $messages[] = "✓ Arquivo de configuração atualizado com sucesso!";

        $installationComplete = true;

    } catch (PDOException $e) {
        $errors[] = "Erro: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Emails Dynamics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .install-body {
            padding: 30px;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            color: #e83e8c;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <i class="bi bi-database-fill-gear" style="font-size: 3rem;"></i>
            <h2 class="mt-3 mb-0">Instalação do Sistema</h2>
            <p class="mb-0 small opacity-75">Sistema de Emails Dynamics</p>
        </div>
        <div class="install-body">
            <?php if (!$installationComplete && empty($errors)): ?>
                <h5 class="mb-4">Configuração do Banco de Dados</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Host do MySQL</label>
                        <input type="text" class="form-control" id="db_host" name="db_host"
                               value="localhost" required>
                        <div class="form-text">Geralmente é "localhost"</div>
                    </div>

                    <div class="mb-3">
                        <label for="db_user" class="form-label">Usuário do MySQL</label>
                        <input type="text" class="form-control" id="db_user" name="db_user"
                               value="root" required>
                    </div>

                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Senha do MySQL</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                        <div class="form-text">Deixe em branco se não houver senha</div>
                    </div>

                    <div class="mb-3">
                        <label for="db_name" class="form-label">Nome do Banco de Dados</label>
                        <input type="text" class="form-control" id="db_name" name="db_name"
                               value="dynamics_emails" required>
                        <div class="form-text">O banco será criado automaticamente</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Atenção:</strong> Certifique-se de que o MySQL está rodando e as credenciais estão corretas.
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-download me-2"></i>
                        Instalar Sistema
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5 class="alert-heading">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erros na Instalação
                    </h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="install.php" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Tentar Novamente
                </a>
            <?php endif; ?>

            <?php if ($installationComplete): ?>
                <div class="text-center">
                    <i class="bi bi-check-circle success-icon"></i>
                    <h4 class="mb-3">Instalação Concluída com Sucesso!</h4>

                    <div class="alert alert-success text-start">
                        <?php foreach ($messages as $message): ?>
                            <p class="mb-1"><?= $message ?></p>
                        <?php endforeach; ?>
                    </div>

                    <div class="alert alert-warning text-start">
                        <strong><i class="bi bi-shield-exclamation me-2"></i>Importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Altere a senha do administrador após o primeiro login</li>
                            <li>Delete este arquivo de instalação por segurança</li>
                        </ul>
                    </div>

                    <a href="../login.php" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Ir para o Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
