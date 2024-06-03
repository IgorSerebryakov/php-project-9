<?php

namespace App\Database;

use App\Url;

class Urls extends DB
{
    public function getUrlById($id)
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM urls WHERE id = :id');
        $stmt->execute([$id]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function save(Url $url)
    {
        // Check if exists
        $stmt = $this->pdo->prepare('SELECT * FROM urls WHERE name = :name');
        $stmt->execute([$url->getName()]);
        $possibleUrl = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($possibleUrl === false) {
            $stmt = $this->pdo->prepare('INSERT INTO urls (name, created_at) VALUES (:name, :created_at)');
            $stmt->execute([$url->getName(), $url->getCreatedAt()]);
            
            $url->setId($this->pdo->lastInsertId());
            $url->setNew();
        } else {
            $url->setId($possibleUrl['id']);
        }
    }
    
    public function getAll()
    {
        $query = 'SELECT DISTINCT ON (urls.name)
            urls.id,
            urls.name,
            checks.created_at AS last_reg,
            checks.status_code
            FROM urls
        LEFT JOIN url_checks AS checks
            ON
                urls.id = checks.url_id
                ORDER BY urls.name, last_reg DESC';
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}