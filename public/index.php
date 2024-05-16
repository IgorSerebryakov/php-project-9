<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostgreSQL\Connection;



try {
    Connection::get()->connect();
    echo 'A connection to db has been established successfully.';
} catch (\PDOException $e) {
    echo $e->getMessage();
}












// psql -a -d $DATABASE_URL -f database.sql
// export DATABASE_URL=postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn