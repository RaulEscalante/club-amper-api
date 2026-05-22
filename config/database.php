<?php

class Database {

    private $host = $_ENV['MYSQLHOST'];

    private $db_name = $_ENV['MYSQLDATABASE'];

    private $username = $_ENV['MYSQLUSER'];

    private $password = $_ENV['MYSQLPASSWORD'];

    private $port = $_ENV['MYSQLPORT'];

    public $conn;

    public function getConnection() {

        $this->conn = null;

        try {

            $this->conn = new PDO(
                "mysql:host=" . $this->host .
                ";port=" . $this->port .
                ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

        } catch (PDOException $exception) {

            echo "Error de conexión: " .
                 $exception->getMessage();
        }

        return $this->conn;
    }
}