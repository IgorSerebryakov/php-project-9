<?php

namespace App\Database;

class Connection
{
    private static ?Connection $conn = null;
    
    public function connect()
    {
        $params = [];

        if (getenv('DATABASE_URL')) {
            $dbUrl = parse_url(getenv('DATABASE_URL'));
            $params['host'] = $dbUrl['host'];
            $params['port'] = isset($dbUrl['port']) ?: 5432;
            $params['database'] = ltrim($dbUrl['path'], '/');
            $params['user'] = $dbUrl['user'];
            $params['pass'] = $dbUrl['pass'];
        } else {
            $params = parse_ini_file('database.ini');
        }
        
        if ($params === false) {
            throw new \Exception("Error reading database configuration file");
        }
        
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $params['host'],
            $params['port'],
            $params['database'],
            $params['user'],
            $params['pass']
        );

        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
    
    public static function get()
    {
        if (null === static::$conn) {
            static::$conn = new self();
        }
        
        return static::$conn;
    }
}