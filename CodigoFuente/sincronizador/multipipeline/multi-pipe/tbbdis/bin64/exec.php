<?php  
	$init_size=1024 * 1024;
	/*for ($i=0; $i < 11 ; $i++) { 
			echo "dis FILE".$i."\n";
		for($j=0;$j < 31; $j++){
			# code...
			shell_exec("./displus -i files/file".$i." -o http://148.247.201.164/test/multi.php?file=file".$i." -o http://148.247.201.166/test/multi.php?file=file".$i." -o http://148.247.201.168/test/multi.php?file=file".$i." -o http://148.247.201.173/test/multi.php?file=file".$i." -o http://148.247.201.174/test/multi.php?file=file".$i." > displusResult/res".$i."Repetition".$j.".txt \n");
		}
	}*/
	for($j=0;$j < 31; $j++){
		echo "Repetition".$j." rec\n";
		for($x=0;$x < 11; $x++) {
			shell_exec("./recplus -i  http://148.247.201.164/test/file".$x." -i  http://148.247.201.166/test/file".$x." -i  http://148.247.201.168/test/file".$x." -o files/fileRec".$x." > recplusResult/resFile".$x."Repetition".$j.".txt \n");
		}
	}
	
?>
