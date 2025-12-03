<?php
/**
 * Script para Corrigir a Senha do Admin
 * Execute este arquivo UMA VEZ para redefinir a senha do admin
 */

require_once __DIR__ . '/config/database.php';

echo "<h1>🔐 Corrigir Senha do Admin</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .ok { color: green; font-weight: bold; }
    .erro { color: red; font-weight: bold; }
    .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    h2 { color: #667eea; }
    code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    .button {
        background: #667eea;
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    .button:hover { background: #5568d3; }
</style>";

$corrigido = false;
$mensagem = '';

// Se recebeu o POST, executa a correção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['corrigir'])) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Gera um novo hash para a senha admin123
        $novaSenha = 'admin123';
        $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        // Atualiza o usuário admin
        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE username = 'admin'");
        $stmt->execute([$novoHash]);

        // Verifica se funcionou
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE username = 'admin'");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify('admin123', $user['password'])) {
            $corrigido = true;
            $mensagem = "✅ Senha corrigida com sucesso!";
        } else {
            $mensagem = "❌ Erro ao verificar a senha corrigida.";
        }

    } catch (PDOException $e) {
        $mensagem = "❌ Erro: " . $e->getMessage();
    }
}

if (!$corrigido && empty($mensagem)) {
    // Mostra o formulário
    echo "<div class='box'>";
    echo "<h2>❌ Problema Detectado</h2>";
    echo "<p>A senha do usuário <strong>admin</strong> está com hash incorreto no banco de dados.</p>";
    echo "<p>Este script vai:</p>";
    echo "<ol>";
    echo "<li>Gerar um novo hash correto para a senha <code>admin123</code></li>";
    echo "<li>Atualizar o usuário admin no banco de dados</li>";
    echo "<li>Você poderá fazer login normalmente</li>";
    echo "</ol>";

    echo "<form method='POST'>";
    echo "<input type='hidden' name='corrigir' value='1'>";
    echo "<button type='submit' class='button'>🔧 Corrigir Senha Agora</button>";
    echo "</form>";
    echo "</div>";

} elseif ($corrigido) {
    // Sucesso!
    echo "<div class='box'>";
    echo "<h2 class='ok'>{$mensagem}</h2>";
    echo "<p><strong>Nova senha do admin:</strong></p>";
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50;'>";
    echo "<p style='margin: 5px 0;'><strong>Usuário:</strong> <code>admin</code></p>";
    echo "<p style='margin: 5px 0;'><strong>Senha:</strong> <code>admin123</code></p>";
    echo "</div>";

    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-top: 20px;'>";
    echo "<p><strong>⚠️ IMPORTANTE:</strong></p>";
    echo "<ul style='margin: 10px 0;'>";
    echo "<li>Altere esta senha após fazer login!</li>";
    echo "<li>Delete este arquivo (<code>corrigir-senha.php</code>) por segurança!</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='margin-top: 30px;'>";
    echo "<a href='login.php' class='button'>🚀 Ir para o Login</a>";
    echo "</div>";
    echo "</div>";

} else {
    // Erro
    echo "<div class='box'>";
    echo "<h2 class='erro'>{$mensagem}</h2>";
    echo "<p><a href='corrigir-senha.php'>← Tentar novamente</a></p>";
    echo "</div>";
}

// Informações adicionais
echo "<div class='box' style='background: #f0f0f0;'>";
echo "<h3>ℹ️ Informações Técnicas</h3>";
echo "<p><strong>Versão do PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Algoritmo de hash:</strong> " . PASSWORD_DEFAULT . " (bcrypt)</p>";
echo "<p><strong>Banco de dados:</strong> " . DB_NAME . "</p>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; color: #666;'>";
echo "<p>Script de correção de senha v1.0</p>";
echo "</div>";
?>
