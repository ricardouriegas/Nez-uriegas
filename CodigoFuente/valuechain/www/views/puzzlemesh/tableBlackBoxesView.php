<table id="tableBB" class="table table-hover" style="width:100%">
	 <thead>
		<tr>
			<th scope="col">#</th>
			<th scope="col">Name</th>
			<th scope="col">Command</th>
			<th scope="col">image</th>
			<th scope="col">port</th>
			<th scope="col">Actions</th>
		</tr>
	</thead>
	<tbody>

	 <?php
	 for ($i = 0; $i < count($data); $i++) {
		$id = $data[$i]["id"];
		$name = $data[$i]["name"];
		echo '
		<tr>
			<td>'.$id.'</td>
			<td id="name_'.$id.'">'.$data[$i]["name"].'</td>
			<td id="command_'.$id.'">'.$data[$i]["command"].'</td>
			<td id="image_'.$id.'">'.$data[$i]["image"].'</td>
			<td id="port_'.$id.'">'.$data[$i]["port"].'</td>
			<td><button type="button" class="btn btn-danger" onclick="modalDelete('.$id.');"><span class="fas fa-trash-alt"></span></button>  <button type="button" class="btn btn-info" onclick="addStage(\''.$name.', '.$id.'\')"><span class="fas fa-plus" value=""></span></button>
			</td>
		</tr>
		';
	}
	?>


</tbody>
</table>