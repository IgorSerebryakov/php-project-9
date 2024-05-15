<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostgreSQL\Connection;

try {
    Connection::get()->connect();
    echo 'A connection to db has been established successfully.';
} catch (\PDOException $e) {
    echo $e->getMessage();
}
