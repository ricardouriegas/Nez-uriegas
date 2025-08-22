<?php
session_start();
?>

<html>

<head>
<title>Upload file</title>
<script src="jquery-2.1.4.js"></script>
<script>
function show(){
	var currentSelection = document.getElementById("op").value;
	if ( currentSelection == "all"){
		document.getElementById("scat").style.display = 'none';
		document.getElementById("catalog").value="";
	}
	else{
		document.getElementById("scat").style.display = 'block';
	}
}

</script>

<h1>View Files </h1>
<link href="css/bootstrap.css" rel="stylesheet"/>
<style>
form{
	margin-left: 50px;
	width: 500px;
}

</style>
</head>

<?php
 $tokenuser = $_SESSION['tokenuser']; 
 $tokenapi = $_SESSION['tokenapi'];

?>

<body>

<script>

function fetch(){
	$.get("http://148.247.204.90:8080/Proxy/CatalogServlet", function(response){
		alert(response);

	}
);
}
</script>

<form action="http://148.247.204.90:8080/Proxy/CatalogServlet" method="get">	

	<input type="hidden" value="<?php echo $tokenuser; ?>" name="tokenuser" id="tokenuser" />

	<input type="hidden" value="<?php echo $tokenapi; ?>" name="tokenapp" id="tokenapp" />
	
	<input type="hidden" value="files" name="op" id="op" />
	
	<div id="scat">
		Keyresource:
		<input type="text" class="form form-control" value="83f6c01fffb722c15e1b0ab1c181beb2af2d430a" name="catalog" id="catalog" />
	</div>
	<br/>

	<input type="submit" class="btn btn-default" value="Search" name="search" id="search"/>
</form>

</body>


</html>