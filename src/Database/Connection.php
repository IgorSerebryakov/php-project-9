<?php

namespace App\Database;

class Connection
{
    private static ?Connection $conn = null;
    
    public function connect()
    {
        if (getenv('DATABASE_URL')) {
            $dbUrl = parse_url(getenv('DATABASE_URL'));
        }

        if (isset($dbUrl['host'])) {
            $params['scheme'] = $dbUrl['scheme'];
            $params['host'] = $dbUrl['host'];
            $params['port'] = $dbUrl['port'] ?? null;
            $params['database'] = $dbUrl['path'] ? ltrim($dbUrl['path'], '/') : null;
            $params['user'] = $dbUrl['user'] ?? null;
            $params['pass'] = $dbUrl['pass'] ?? null;
        } else {
            $params = parse_ini_file('database.ini');
        }
        
        if ($params === false) {
            throw new \Exception("Error reading database configuration file");
        }
        
        $conStr = sprintf(
            "%s:host=%s;port=%s;dbname=%s;user=%s;password=%s",
            $params['scheme'],
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