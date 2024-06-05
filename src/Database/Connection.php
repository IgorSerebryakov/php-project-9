<?php

namespace App\Database;

class Connection
{
    protected $connection = null;
    protected function connect()
    {
        $databaseUrl = parse_url($_ENV['DATABASE_URL']);
        $user = $databaseUrl['user'];
        $pass = $databaseUrl['pass'];
        $host = $databaseUrl['host'];
        $port = $databaseUrl['port'];
        $dbName = ltrim($databaseUrl['path'], '/');
        $scheme = 'pgsql';

        $conStr = "{$scheme}:host={$host};port={$port};dbname={$dbName};user={$user};password=$pass";

        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->connection = $pdo;
        return $pdo;
    }
    
    protected function getConnection()
    {
        return $this->connection;
    }
}
