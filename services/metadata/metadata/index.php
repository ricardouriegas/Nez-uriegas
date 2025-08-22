<?php

session_start();


if ( isset($_REQUEST["out"])){
	session_destroy();
}

?>



<html>

<head>
<script src="jquery-2.1.4.js">
</script>

<script>

function authenticate(){

	var userName = document.getElementById("nameuser").value;
	var pass = document.getElementById("password").value;
	
	 var url = "http://148.247.204.202:47000/login.php?nameuser=" + userName + "&password=" + pass ; 
	
	$.get(
		url,
		function(data, status){
       	 location.reload();
   	 });
	

}




</script>

<title>Home</title>

<h1>Login</h1>
<link href="css/bootstrap.css" rel="stylesheet"/>


<style>
	.fo{
		margin-left: 50px;
		width: 500px;
	}
</style>

</head>




<body>

<?php

	if ( isset($_SESSION["tokenapi"])){
		
	
?>
<a href="upload_files_form.php" >Upload files</a>

<a href="view_files_form.php" >Catalogs</a>

<form action="index.php" method="post">
<input type="submit" value="Logout" class="btn btn-default" name="out"/>

</form>

<?php
	}
	else{
?>

<div class="fo">	


	Username:
	<input type="text" class="form form-control" value="" name="nameuser" id="nameuser" /><br/>
	Password:
	<input type="password" class="form form-control" value="" name="password" id="password" /><br/>

	<input type="button" value="Login" class="btn btn-default" onclick="authenticate()" />

</div>
<?php
	}
?>

</body>


</html>