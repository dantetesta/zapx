<?php
/**
 * Classe de Conexão com Banco de Dados
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class Database {
    private static $instance = null;
    private $connection;

    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        try {
            // Tentar diferentes métodos de conexão
            $dsn = $this->buildDSN();
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_TIMEOUT => 5
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage() . 
                "<br><br><strong>Dica:</strong> Tente usar '127.0.0.1' ao invés de 'localhost' no config.php");
        }
    }
    
    /**
     * Constrói o DSN tentando diferentes métodos
     */
    private function buildDSN() {
        $host = DB_HOST;
        $dbname = DB_NAME;
        $charset = DB_CHARSET;
        
        // Se for localhost, tentar socket Unix primeiro
        if ($host === 'localhost') {
            $commonSockets = [
                '/var/run/mysqld/mysqld.sock',
                '/tmp/mysql.sock',
                '/var/lib/mysql/mysql.sock',
                '/Applications/MAMP/tmp/mysql/mysql.sock'
            ];
            
            foreach ($commonSockets as $socket) {
                if (file_exists($socket)) {
                    return "mysql:unix_socket=$socket;dbname=$dbname;charset=$charset";
                }
            }
            
            // Se não encontrou socket, usar 127.0.0.1
            return "mysql:host=127.0.0.1;dbname=$dbname;charset=$charset";
        }
        
        // Conexão padrão
        return "mysql:host=$host;dbname=$dbname;charset=$charset";
    }

    /**
     * Obtém a instância única da classe (Singleton)
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
     * Previne clonagem do objeto
     */
    private function __clone() {}

    /**
     * Previne deserialização do objeto
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
