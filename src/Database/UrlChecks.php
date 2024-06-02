<?php

namespace App\Database;

class UrlChecks extends DB
{
    public function getChecksById($id)
    {
        $stmt = $this->pdo->prepare('SELECT id, created_at, status_code, h1, title, description 
                                  FROM url_checks 
                                  WHERE url_id = :url_id ORDER BY id DESC');
        $stmt->execute([$id]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}