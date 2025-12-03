<?php
/**
 * Sistema de Sessão e Autenticação
 */

// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Classe para gerenciar autenticação e sessões
 */
class Auth {

    /**
     * Verifica se o usuário está logado
     */
    public static function check() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    /**
     * Obtém o ID do usuário logado
     */
    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtém o role do usuário logado
     */
    public static function role() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Obtém o nome do usuário logado
     */
    public static function username() {
        return $_SESSION['username'] ?? null;
    }

    /**
     * Obtém o nome completo do usuário logado
     */
    public static function name() {
        return $_SESSION['nome_completo'] ?? null;
    }

    /**
     * Verifica se o usuário é admin
     */
    public static function isAdmin() {
        return self::role() === 'admin';
    }

    /**
     * Verifica se o usuário tem permissão para gerar emails
     */
    public static function canGenerate() {
        return in_array(self::role(), ['admin', 'gerador']);
    }

    /**
     * Verifica se o usuário tem permissão para acessar reports
     */
    public static function canReport() {
        return in_array(self::role(), ['admin', 'report']);
    }

    /**
     * Realiza o login do usuário
     */
    public static function login($username, $password) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? AND ativo = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Define as variáveis de sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nome_completo'] = $user['nome_completo'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Registra o log de acesso
                self::logAccess($user['id'], 'login');

                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Realiza o logout do usuário
     */
    public static function logout() {
        if (self::check()) {
            self::logAccess(self::id(), 'logout');
        }

        // Destrói todas as variáveis de sessão
        $_SESSION = [];

        // Destrói o cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destrói a sessão
        session_destroy();
    }

    /**
     * Verifica se a sessão expirou
     */
    public static function checkTimeout($timeout = 7200) {
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            if ($elapsed > $timeout) {
                self::logout();
                return false;
            }
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Registra log de acesso
     */
    private static function logAccess($userId, $action) {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO logs_acesso (usuario_id, acao, ip_address, user_agent)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Redireciona se não estiver autenticado
     */
    public static function requireAuth() {
        if (!self::check() || !self::checkTimeout()) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Redireciona se não tiver a permissão especificada
     */
    public static function requireRole($allowedRoles) {
        self::requireAuth();

        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        if (!in_array(self::role(), $allowedRoles)) {
            header('Location: /dashboard.php');
            exit;
        }
    }
}

/**
 * Classe para gerenciar usuários
 */
class User {

    /**
     * Cria um novo usuário
     */
    public static function create($username, $password, $nome_completo, $email, $role = 'gerador') {
        try {
            $db = getDB();

            // Verifica se o username já existe
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username já existe'];
            }

            // Cria o usuário
            $stmt = $db->prepare("
                INSERT INTO usuarios (username, password, nome_completo, email, role)
                VALUES (?, ?, ?, ?, ?)
            ");
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$username, $hashedPassword, $nome_completo, $email, $role]);

            return ['success' => true, 'message' => 'Usuário criado com sucesso'];
        } catch (PDOException $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao criar usuário'];
        }
    }

    /**
     * Lista todos os usuários
     */
    public static function all() {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT id, username, nome_completo, email, role, ativo, created_at FROM usuarios ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém um usuário por ID
     */
    public static function find($id) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, nome_completo, email, role, ativo, created_at FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Atualiza um usuário
     */
    public static function update($id, $data) {
        try {
            $db = getDB();

            $fields = [];
            $values = [];

            if (isset($data['nome_completo'])) {
                $fields[] = 'nome_completo = ?';
                $values[] = $data['nome_completo'];
            }
            if (isset($data['email'])) {
                $fields[] = 'email = ?';
                $values[] = $data['email'];
            }
            if (isset($data['role'])) {
                $fields[] = 'role = ?';
                $values[] = $data['role'];
            }
            if (isset($data['ativo'])) {
                $fields[] = 'ativo = ?';
                $values[] = $data['ativo'];
            }
            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = 'password = ?';
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($fields)) {
                return ['success' => false, 'message' => 'Nenhum campo para atualizar'];
            }

            $values[] = $id;
            $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'Usuário atualizado com sucesso'];
        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao atualizar usuário'];
        }
    }

    /**
     * Deleta um usuário
     */
    public static function delete($id) {
        try {
            $db = getDB();

            // Não permite deletar o próprio usuário
            if ($id == Auth::id()) {
                return ['success' => false, 'message' => 'Você não pode deletar seu próprio usuário'];
            }

            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);

            return ['success' => true, 'message' => 'Usuário deletado com sucesso'];
        } catch (PDOException $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao deletar usuário'];
        }
    }
}
