<?php

namespace PostgreSQL;

final class Connection
{
    private static ?Connection $conn = null;
    
    public function connect()
    {

//        if (getenv('DATABASE_URL')) {
//            $dbUrl = parse_url(getenv('DATABASE_URL'));
//            $params['host'] = $dbUrl['host'];
//        } 
        
        
        $params = parse_ini_file('database.ini');
        if ($params === false) {
            throw new \Exception("Error reading database configuration file");
        }
       
        
        // postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn
//        $conStr = sprintf(
//            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
//            $params['host'],
//            $params['port'],
//            $params['database'],
//            $params['user'],
//            $params['pass']
//        );
        
        $conStr = 'postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn';
        
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