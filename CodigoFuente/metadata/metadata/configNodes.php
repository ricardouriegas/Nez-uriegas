<?php
require_once 'DbHandler.php';
if(isset($_POST['url']) && isset($_POST['capacity']) && isset($_POST['memory'])) {
	$id = uniqid('', true);
	$url = $_POST['url'];
	$cap = $_POST['capacity'];
	$mem = $_POST['memory'];
	$db = new DbHandler();
	$result = $db->registerNode($url, $cap, $mem);
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

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Upload Files</title>
	<style type="text/css" media="screen">
		th,td{
			padding: 5px;
			border: solid 1px;
			text-align: center;
		}
		.campo {
			font-size: 16px;
			margin: 6px;
		}
		.boton{
			padding: 4px;
		}
	</style>
</head>
<body>
	<div class="container-fluid"></div>
	<div class="container fluid">
		<div class="col-sm-6">
			<div class="panel panel-primary">
				<div class="panel-heading"><h2>Agregar máquinas de almacenamiento</h2></div>
				<div class="panel-body">
					<p>En este formulario puedes agregar las máquinas de almacenamiento con las que cuentas, sólo es necesario que registres sus características.</p>
					<form action="" method="POST">
						<label for="url">Dirección IP: </label><br/>
						<input type="text" class="campo" id="url" name="url" required="true" placeholder="http://127.0.0.1/" /> (http://127.0.0.1)<br/>		
						<label for="capacity">Capacidad: </label><br/>
						<input type="number" class="campo" id="capacity" name="capacity" required="true" placeholder="40000000000" /> (bytes)<br/>		
						<label for="memory">Memoria: </label><br/>
						<input type="number" class="campo" id="memory" name="memory" required="true" placeholder="2000000000" /> (bytes)<br/><br>
						<input type="submit" id="" value="Guardar" class="boton"/>
					</form>
				</div>
			</div>
		</div>

		<div class="col-sm-6">
			<div class="panel panel-primary">
				<div class="panel-heading"><h2>Máquinas de almacenamiento registradas</h2></div>
				<div class="panel-body">
					<p>En la siguiente tabla puedes observar las máquinas de almacenamiento que has agregado al sistema, recuerda que el sistema funciona correctamente con mínimo <b>5</b> máquinas activas, sin embargo puedes agregar los que desees.</p>
				<table>
				 	<tr>
				 		<th>#</th>
				 		<th>Dirección IP</th>
				 		<th>Capacidad</th>
				 		<th>Memoria</th>
				 		<th>Estatus</th>
				 	</tr>
				 	<?php
				 		$db = new DbHandler();
				 		$nodes = $db->getAllNodes();
				 		foreach ($nodes as $key => $node) {
				 			echo "<tr>";
				 			echo '<td>'.($key + 1).'</td>';
				 			echo '<td>'.$node['url'].'</td>';
				 			echo '<td>'.$node['capacity'].'</td>';
				 			echo '<td>'.$node['memory'].'</td>';
				 			
				 			if ($node['status'] === '1') {
				 				echo '<td>Activo</td>';
				 			} else {
				 				echo '<td>Inactivo</td>';
				 			}
				 			echo "</tr>";
				 		}
				 	?>
				</table>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
