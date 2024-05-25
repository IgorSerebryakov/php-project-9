<?php

namespace PostgreSQL;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DB extends Connection
{
    private static $db;
    
    private static $connection = null;

    public function __construct()
    {
        if (self::$connection === null) {
            try {
                self::$db = Connection::get()->connect();
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
        
        return self::$connection;
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
        
        $pseudoVars = Str::of($query)->matchAll('/(?<=:)\w*/');
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*public function save($url)
    {
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $stmt = $this->pdo->prepare($sql);
        
        $name = $url['name'];
        $created_at = date("Y-m-d H:i:s");
        $stmt->execute([
            'name' => $name,
            'created_at' => $created_at
        ]);
    }
    
    private function executeQuery($sql, $params)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getById($id)
    {
        $sql = 'SELECT id, name, created_at FROM urls WHERE id = :id';
        return $this->executeQuery($sql, [':id' => $id]);
    }
    
    public function getByName($name)
    {
        $sql = 'SELECT id, name, created_at FROM urls WHERE name = :name';
        return $this->executeQuery($sql, [':name' => $name]);
    }
    
    public function find($url)
    {
        $sql = 'SELECT id, name, created_at FROM urls WHERE name = :name';
        $stmt = $this->pdo->prepare($sql);
        
        $name = $url['name'];
        $stmt->execute([$name]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function all()
    {
        $sql = 'SELECT urls.id, name, MAX(checks.created_at) AS last_reg 
                FROM urls
                INNER JOIN url_checks AS checks
                    ON 
                        urls.id = checks.url_id
                GROUP BY name, urls.id
                ORDER BY urls.id DESC';
        
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}*/