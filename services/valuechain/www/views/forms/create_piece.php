<?php 

include_once("../../includes/conf.php");
include_once(CLASES . "/class.Curl.php");

$curl = new Curl();
$response = $curl->get("http://container-manager:5000/images/json");


if($response["code"] == 200){
    $data = $response["data"];
    $images = $data["images"];
}


?>

<form id="formNewBB" >
    <div class="form-group">
        <input type="hidden" name="newBlackBox">
        <label for="nameBB">Name</label>
        <input type="text" class="form-control" id="nameBB"  name="nameBB" placeholder="Enter the piece name..." required>
    </div>
    <div class="form-group">
        <label for="commandBB">Command</label>
        <input type="text" class="form-control" id="commandBB" name="commandBB" placeholder="Enter the execution command of the piece..." required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea class="form-control" rows="3" id="descriptionBB" placeholder="Enter the description of the piece..." required></textarea>
        </div>
    </div>

    <div class="form-group">
        <div class="dropdown bootstrap-select input-group-btn form-control open">
            <label for="imageBB">Image</label>
            <select class="form-control input-group-btn selectpicker" style="z-index: 99999;" data-live-search="true" name="imageBB" id="imageBB" required>
                <?php
                    foreach($images as $i){
                        if(isset($i["RepoTags"])){
                            foreach($i["RepoTags"] as $t){
                                if(substr( trim($t), 0, 6 ) != "<none>"){
                                    echo '<option value="'.trim($t).'">'.trim($t) .'</option>';
                                }
                            }
                        }
                        
                        
                    }
                ?>
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-primary" id="btnNewBB" >Create piece</button>
</form>