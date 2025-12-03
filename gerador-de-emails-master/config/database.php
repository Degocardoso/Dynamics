<?php
/**
 * Configuração do Banco de Dados
 *
 * IMPORTANTE: Altere as credenciais abaixo conforme seu ambiente
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'dynamics_emails');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe de conexão com o banco de dados usando PDO
 */
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Retorna a instância única da conexão (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Previne a clonagem do objeto
     */
    private function __clone() {}

    /**
     * Previne a deserialização do objeto
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Função helper para obter a conexão rapidamente
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
