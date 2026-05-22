<?php

class Database
{

    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;

    public $conn;

    public function __construct()
    {

        $this->host =
            $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');

        $this->db_name =
            $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');

        $this->username =
            $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');

        $this->password =
            $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');

        $this->port =
            $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT');
    }

    public function getConnection()
    {

        $this->conn = null;

        try {
            echo json_encode([
                "host" => $this->host,
                "db" => $this->db_name,
                "user" => $this->username,
                "port" => $this->port
            ]);

            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch (PDOException $exception) {

            echo json_encode([
                "success" => false,
                "error" => $exception->getMessage()
            ]);
        }

        return $this->conn;
    }
}