<?php
require_once 'DbHandler.php';

if(isset($_POST['url']) && isset($_POST['capacity']) && isset($_POST['memory'])) {
	$id = uniqid('', true);
	$url = $_POST['url'];
	$cap = $_POST['capacity'];
	$mem = $_POST['memory'];
	$db = new DbHandler();
	$result = $db->registerNode($url, $cap, $mem);
	if($result){
		echo "Node " . $url . " added";
	}else{
		echo "Error " . $url;
	}
	
}

if (isset($_GET['IP']) && isset($_GET['Port'])) {
    $port = $_GET['Port'];
    if (preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',$_GET['IP']) && is_numeric($port)){
        $url_base="http://".$_GET['IP'].":";
        $capacity = 40000000000;
        $memory = 2000000000;
        $db = new DbHandler();
        for ($i=0; $i < 5; $i++) {
            $url=$url_base.$port."/";
            $db->registerNode($url, $capacity, $memory);
            //echo "  registerNode(".$url_base.$port."/".", ".$capacity.", ".$memory.");";
            $port++;
        }
    }
}
if (isset($_GET['deleteNodes']) && $_GET['deleteNodes']==true) {
	$db = new DbHandler();
	$db->deleteAllNodes();
}
?>