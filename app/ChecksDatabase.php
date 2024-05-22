<?php

namespace PostgreSQL;

class ChecksDatabase
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function save($id)
    {
        $sql = 'INSERT INTO url_checks (url_id, created_at) VALUES (:url_id, :created_at)';
        $stmt = $this->pdo->prepare($sql);
        
        $created_at = date("Y-m-d H:i:s");
        $stmt->execute([
            'url_id' => $id,
            'created_at' => $created_at
        ]);
    }
}