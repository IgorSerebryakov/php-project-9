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
    
    public function get($id)
    {
        $sql = 'SELECT id, created_at FROM url_checks WHERE url_id = :url_id';
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([$id]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getLastRegs()
    {
        $sql = 'SELECT url_id, MAX(created_at) AS last_reg 
                FROM url_checks AS checks
                GROUP BY url_id
                ORDER BY url_id DESC';

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}