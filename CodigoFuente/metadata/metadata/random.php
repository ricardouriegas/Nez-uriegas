<?php

function get_random_array($size){
	$dir = array();

	while(count($dir) < $size){
    		$c  = rand(0, $size-1);
		$dir[$c] = $c;
	}
	return $dir;
}

///print_r(get_random_array(5));

?>

