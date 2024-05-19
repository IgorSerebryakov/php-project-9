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
}