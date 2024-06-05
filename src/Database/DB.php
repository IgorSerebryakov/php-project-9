<?php

namespace App\Database;

use Illuminate\Support\Str;

class DB extends Connection
{
    protected \PDO $pdo;

    public function __construct()
    {
        try {
            $this->pdo = $this->connect();
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
