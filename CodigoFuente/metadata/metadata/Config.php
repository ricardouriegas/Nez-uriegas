<?php
/*
* Config
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/

$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASSWORD'];
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$db = $_ENV['DB_NAME'];

date_default_timezone_set('America/Mexico_City');
define('DB_USER', $user);
define('DB_PASSWORD', $pass);
define('DB_HOST', $host);
define('DB_PORT', $port);
define('DB_NAME', $db);

define('AUTH_HOST', $_ENV['AUTH_HOST']);
define('PUB_SUB_HOST', $_ENV['PUB_SUB_HOST']);
define('GATEWAY', "apigateway");
?>