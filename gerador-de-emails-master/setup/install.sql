-- Script de instalação do sistema de autenticação
-- Execute este script no seu banco de dados MySQL

CREATE DATABASE IF NOT EXISTS dynamics_emails DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE dynamics_emails;

-- Tabela de usuários
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de acesso (opcional, para auditoria)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário admin padrão
-- Senha padrão: admin123 (IMPORTANTE: Alterar após primeiro login!)
INSERT INTO usuarios (username, password, nome_completo, email, role, ativo)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: admin123
    'Administrador do Sistema',
    'admin@example.com',
    'admin',
    1
);

-- Mensagem de conclusão
SELECT 'Banco de dados criado com sucesso!' AS 'STATUS';
SELECT 'Usuário admin criado:' AS 'AVISO';
SELECT 'Username: admin' AS 'CREDENCIAIS';
SELECT 'Senha: admin123' AS 'CREDENCIAIS';
SELECT 'IMPORTANTE: Altere a senha após o primeiro login!' AS 'ALERTA';
