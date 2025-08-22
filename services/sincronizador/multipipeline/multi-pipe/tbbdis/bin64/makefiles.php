<?php
//500KB 1MB 2MB 4MB 8MB 16MB 32MB 64MB 128MB 256MB
$init_size = 1024; //initial size in KB

//Initial file (file0)
$size = $init_size * 1024; //in bytes
$file_name="file0";
$file_path = "./files/".$file_name;
exec("java file_generator $file_path $size");
echo ".";

//Generating files with size = previous size * 2
for($i=1; $i<11; $i++){
    $size = ($init_size* pow(2, $i)) * 1024; //in bytes
    $file_name="file".$i;
    $file_path = "./files/".$file_name;
    exec("java file_generator $file_path $size");
	echo ".";
}
?>

<?php
	return "ok"; 
?>
