 <?php
 for ($i = 0; $i < count($data); $i++) {
  $id = $data[$i]["id"];
  $name= $data[$i]["name"];
  $source = $data[$i]["source"];
  $transformation = $data[$i]["transformation"];
  $val = $i+1;

  echo '
  <div class="blockelem create-flowy noselect " id="'.$val.'" name = "'.$name.'">
  <input type="hidden" name="blockelemtype" class="blockelemtype" value="'.$val.'">
  <div class="grabme"><span class="fas fa-box"></span>
  </div>
  <div class="blockin">                    

  <div class="blocktext"><p class="blocktitle">'.$name.'</p><p class="blockdesc"></p>
  </div>
  </div>
  </div>';
}
?>

