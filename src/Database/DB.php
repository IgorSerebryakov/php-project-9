<?php

namespace App\Database;

use Illuminate\Support\Str;

class DB extends Connection
{
    private static $db;

    public function __construct()
    {
        try {
            self::$db = Connection::get()->connect();
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private static function query($query)
    {
        return self::$db->query($query);
    }

    public static function prepare($query)
    {
        return self::$db->prepare($query);
    }
    
    public static function run($query, $args = [])
    {
        if (empty($args)) {
            return self::query($query);
        }
        
        $stmt = self::prepare($query);
        
        $pseudoVars = \Illuminate\Support\Str::of($query)->matchAll('/(?<=:)\w*/');
        $queryParams = $pseudoVars->combine($args)->all();
        $stmt->execute($queryParams);
        return $stmt;
    }
    
    public static function getRow($query, $args = [])
    {
        return self::run($query, $args)->fetch(\PDO::FETCH_ASSOC);
    }
    
    public static function getRows($query, $args = [])
    {
        return self::run($query, $args)->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public static function save($query, $args = [])
    {
        return self::run($query, $args);
    }
    
}
