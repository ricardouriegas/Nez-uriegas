<?php

$user = $_ENV['POSTGRES_USER'];
$pass = $_ENV['POSTGRES_PASSWORD'];
$host = $_ENV['POSTGRES_HOST'];
$port = $_ENV['POSTGRES_PORT'];
$db = $_ENV['POSTGRES_DB'];

date_default_timezone_set('America/Mexico_City');
define('DB_USERNAME', $user);
define('DB_PASSWORD', $pass);
define('DB_HOST', $host);
define('DB_PORT', $port);
define('DB_NAME', $db);

?>