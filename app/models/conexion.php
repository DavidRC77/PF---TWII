<?php
date_default_timezone_set('America/La_Paz');

$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

class Conexion {
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $conn;

    public function __construct() {
        $this->host     = $_ENV['DB_HOST']  ?? getenv('DB_HOST')  ?? '';
        $this->port     = $_ENV['DB_PORT']  ?? getenv('DB_PORT')  ?? '5432';
        $this->dbname   = $_ENV['DB_NAME']  ?? getenv('DB_NAME')  ?? '';
        $this->user     = $_ENV['DB_USER']  ?? getenv('DB_USER')  ?? '';
        $this->password = $_ENV['DB_PASS']  ?? getenv('DB_PASS')  ?? '';
    }

    public function conectar() {
        $this->conn = null;

        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbname;
            
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true
            ];

            $this->conn = new PDO($dsn, $this->user, $this->password, $opciones);
            
            $this->conn->exec("SET TIME ZONE 'America/La_Paz'");
            
            return $this->conn;

        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>