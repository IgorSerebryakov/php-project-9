<?php

namespace PostgreSQL;

class Database
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    public function save($name, $created_at)
    {
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name' => $name,
            'created_at' => $created_at
        ]);
    }
    
    public function find($id)
    {
        $sql = 'SELECT id, name, created_at FROM urls WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([$id]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}