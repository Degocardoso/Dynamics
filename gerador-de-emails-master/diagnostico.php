<?php
/**
 * Script de Diagnóstico do Sistema
 * Use este arquivo para descobrir problemas
 */

echo "<h1>🔍 Diagnóstico do Sistema</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .ok { color: green; font-weight: bold; }
    .erro { color: red; font-weight: bold; }
    .aviso { color: orange; font-weight: bold; }
    .box { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// Teste 1: Versão do PHP
echo "<div class='box'>";
echo "<h2>1️⃣ Versão do PHP</h2>";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "<p class='ok'>✅ PHP {$phpVersion} - OK!</p>";
} else {
    echo "<p class='erro'>❌ PHP {$phpVersion} - Versão muito antiga! Atualize para 7.4 ou superior.</p>";
}
echo "</div>";

// Teste 2: Extensões necessárias
echo "<div class='box'>";
echo "<h2>2️⃣ Extensões PHP</h2>";
$extensoes = ['pdo', 'pdo_mysql', 'mbstring'];
foreach ($extensoes as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='ok'>✅ {$ext} instalado</p>";
    } else {
        echo "<p class='erro'>❌ {$ext} NÃO instalado</p>";
    }
}
echo "</div>";

// Teste 3: Arquivo de configuração
echo "<div class='box'>";
echo "<h2>3️⃣ Arquivo de Configuração</h2>";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    echo "<p class='ok'>✅ Arquivo database.php existe</p>";
    require_once $configFile;

    echo "<p><strong>Configurações:</strong></p>";
    echo "<pre>";
    echo "Host: " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . "\n";
    echo "Database: " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . "\n";
    echo "User: " . (defined('DB_USER') ? DB_USER : 'NÃO DEFINIDO') . "\n";
    echo "Password: " . (defined('DB_PASS') ? (DB_PASS == '' ? '(vazio)' : '****') : 'NÃO DEFINIDO') . "\n";
    echo "</pre>";
} else {
    echo "<p class='erro'>❌ Arquivo database.php NÃO encontrado!</p>";
}
echo "</div>";

// Teste 4: Conexão com o banco
echo "<div class='box'>";
echo "<h2>4️⃣ Conexão com MySQL</h2>";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='ok'>✅ Conexão com MySQL estabelecida!</p>";

    // Teste se o banco existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='ok'>✅ Banco de dados '" . DB_NAME . "' existe</p>";

        // Conecta ao banco
        $pdo->exec("USE " . DB_NAME);

        // Verifica se a tabela usuarios existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='ok'>✅ Tabela 'usuarios' existe</p>";

            // Conta quantos usuários existem
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p class='ok'>✅ Total de usuários: {$result['total']}</p>";

            // Lista os usuários
            $stmt = $pdo->query("SELECT id, username, nome_completo, role, ativo FROM usuarios");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($usuarios) > 0) {
                echo "<h3>Usuários cadastrados:</h3>";
                echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Username</th><th>Nome</th><th>Role</th><th>Status</th></tr>";
                foreach ($usuarios as $user) {
                    $status = $user['ativo'] ? "<span class='ok'>Ativo</span>" : "<span class='erro'>Inativo</span>";
                    echo "<tr>";
                    echo "<td>{$user['id']}</td>";
                    echo "<td><strong>{$user['username']}</strong></td>";
                    echo "<td>{$user['nome_completo']}</td>";
                    echo "<td>{$user['role']}</td>";
                    echo "<td>{$status}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='erro'>❌ Nenhum usuário cadastrado! Execute o instalador.</p>";
            }

        } else {
            echo "<p class='erro'>❌ Tabela 'usuarios' NÃO existe! Execute o instalador.</p>";
        }

    } else {
        echo "<p class='erro'>❌ Banco de dados '" . DB_NAME . "' NÃO existe! Execute o instalador.</p>";
    }

} catch (PDOException $e) {
    echo "<p class='erro'>❌ Erro ao conectar: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Teste 5: Sessões PHP
echo "<div class='box'>";
echo "<h2>5️⃣ Sessões PHP</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='ok'>✅ Sessões funcionando!</p>";
    $_SESSION['teste_diagnostico'] = 'OK';
    echo "<p>Valor de teste gravado na sessão: " . $_SESSION['teste_diagnostico'] . "</p>";
} else {
    echo "<p class='erro'>❌ Sessões NÃO estão funcionando!</p>";
}
echo "</div>";

// Teste 6: Teste de senha
echo "<div class='box'>";
echo "<h2>6️⃣ Teste de Senha (admin123)</h2>";
$senha_teste = 'admin123';
$hash_esperado = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($senha_teste, $hash_esperado)) {
    echo "<p class='ok'>✅ Função password_verify() funcionando!</p>";
    echo "<p>A senha 'admin123' foi verificada com sucesso.</p>";
} else {
    echo "<p class='erro'>❌ Função password_verify() NÃO funcionou!</p>";
}

// Testa se consegue criar um novo hash
$novo_hash = password_hash('teste123', PASSWORD_DEFAULT);
echo "<p class='ok'>✅ Função password_hash() funcionando!</p>";
echo "<p>Hash gerado de teste: <code style='font-size: 10px;'>{$novo_hash}</code></p>";
echo "</div>";

// Teste 7: Permissões de escrita
echo "<div class='box'>";
echo "<h2>7️⃣ Permissões de Pasta</h2>";
$emailsDir = __DIR__ . '/emails';
if (is_dir($emailsDir)) {
    if (is_writable($emailsDir)) {
        echo "<p class='ok'>✅ Pasta 'emails/' tem permissão de escrita</p>";
    } else {
        echo "<p class='erro'>❌ Pasta 'emails/' NÃO tem permissão de escrita!</p>";
        echo "<p>Solução Windows: Clique direito na pasta → Propriedades → Segurança → Editar → Marque 'Controle Total'</p>";
        echo "<p>Solução Linux: <code>sudo chmod -R 777 {$emailsDir}</code></p>";
    }
} else {
    echo "<p class='aviso'>⚠️ Pasta 'emails/' não existe! Criando...</p>";
    if (mkdir($emailsDir, 0777, true)) {
        echo "<p class='ok'>✅ Pasta criada com sucesso!</p>";
    } else {
        echo "<p class='erro'>❌ Não foi possível criar a pasta!</p>";
    }
}
echo "</div>";

// Resumo Final
echo "<div class='box' style='background: #667eea; color: white;'>";
echo "<h2 style='color: white; border-bottom: 2px solid white;'>📊 Resumo</h2>";

$problemas = [];

if (version_compare($phpVersion, '7.4.0', '<')) {
    $problemas[] = "PHP versão antiga";
}

foreach (['pdo', 'pdo_mysql', 'mbstring'] as $ext) {
    if (!extension_loaded($ext)) {
        $problemas[] = "Extensão {$ext} não instalada";
    }
}

if (empty($problemas)) {
    echo "<h3 style='color: #90EE90;'>✅ Sistema OK - Pronto para uso!</h3>";
    echo "<p><a href='login.php' style='color: white; text-decoration: underline; font-weight: bold;'>→ Ir para o Login</a></p>";
} else {
    echo "<h3 style='color: #FFB6C1;'>❌ Problemas encontrados:</h3>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>{$problema}</li>";
    }
    echo "</ul>";
}

echo "</div>";

// Instruções
echo "<div class='box'>";
echo "<h2>📝 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Se o banco de dados NÃO existe, acesse: <a href='setup/install.php' target='_blank'>Instalador Automático</a></li>";
echo "<li>Se todos os testes estão OK, tente fazer login com: <strong>admin</strong> / <strong>admin123</strong></li>";
echo "<li>Se o login não funcionar mesmo assim, me envie o resultado desta página!</li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 20px; color: #666;'>";
echo "<p>Script de diagnóstico v1.0</p>";
echo "</div>";
?>
