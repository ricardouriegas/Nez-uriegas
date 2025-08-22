<?php 

include_once("../../includes/conf.php");
include_once(CLASES . "/class.Curl.php");

$url = "http://" . APIGATEWAY_HOST . "/pub_sub/v1/view/groups/user/".$_SESSION["tokenuser"]."/subscribed?access_token=".$_SESSION["access_token"];
$curl = new Curl();
$response = $curl->get($url);
if($response["code"] == 200){
    $data = $response["data"];
    $catalogs = $data["data"];
}


?>

<form id="formNewCatalog" >
    <div class="form-group">
        <input type="hidden" name="newBlackBox">
        <label for="catalogName">Catalog (source) name</label>
        <input type="text" class="form-control" id="catalogName"  name="catalogName" placeholder="Enter the catalog name..." required>
    </div>


    <div class="form-group">
        <label for="cataloggroup">Group</label>

        <select class="form-control" name="cataloggroup" id="cataloggroup" required>
            <?php
                foreach($catalogs as $i){
                    echo '<option value="'.trim($i["tokengroup"]).'">'.trim($i["namegroup"]).'</option>';
                }
            ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary" id="btnNewGroup" >Create catalog</button>
</form>