<?php

namespace PostgreSQL;

class Database
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    public function save($url)
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
        $sql = 'SELECT * FROM urls ORDER BY id DESC';
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}