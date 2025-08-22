<?php 

$host = $_ENV['URL_METADATA'];
$folderFiles = $_ENV['FOLDER_UPLOADS'];
$folderAbekeys = $_ENV['FOLDER_ABEKEYS'];
$node = $_ENV['NODE_ID'];

define('FOLDER_UPLOADS', $folderFiles);
define('FOLDER_ABEKEYS', $folderAbekeys);
define('NODE_ID', $node);
define('URL_METADATA', 'http://' . $host . '/');

?>
