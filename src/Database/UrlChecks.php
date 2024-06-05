<?php

namespace App\Database;

use App\Check;

class UrlChecks extends DB
{
    public function getChecksById($id)
    {
        $checkParams = [
            'id',
            'created_at',
            'status_code',
            'h1',
            'title',
            'description'
        ];
        $columnNames = implode(', ', $checkParams);

        $query = "SELECT {$columnNames} FROM url_checks WHERE url_id = :url_id ORDER BY id DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function save(Check $check)
    {
        $checkParams = [
            'url_id',
            'created_at',
            'status_code',
            'h1',
            'title',
            'description'
        ];

        $columnNames = implode(', ', $checkParams);
        $pseudoVars = implode(', ', array_map(fn($var) => ":{$var}", $checkParams));

        $query = "INSERT INTO url_checks ({$columnNames}) VALUES ({$pseudoVars})";
        $stmt = $this->pdo->prepare($query);

        $stmt->execute([
            'url_id' => $check->getUrlId(),
            'created_at' => $check->getCreatedAt(),
            'status_code' => $check->getStatusCode(),
            'h1' => $check->getH1(),
            'title' => $check->getTitle(),
            'description' => $check->getDescription()
        ]);
    }
}
