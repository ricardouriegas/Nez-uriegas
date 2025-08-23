<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(empty($_SESSION['idUser'])){
  header("Location: login.php");
}

//print_r($_SESSION);

$user = isset($_SESSION['id']) ? $_SESSION['id'] : -1;

$_SESSION['actual_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

require_once(MODELOS . "/puzzlemesh/model.php");

$blackBoxModel = new BlackBoxes();
$data = $blackBoxModel->readBlackBoxe();


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nez creator</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- IonIcons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/alyssaxuu/flowy/flowy.min.css"> 
  <link rel="stylesheet" href="../plugins/toastr/toastr.min.css">
  <link rel="stylesheet" href="../views/css/workflows.css">
 
</head>
<!--
`body` tag options:

  Apply one or more of the following classes to to the body tag
  to get the desired effect

  * sidebar-collapse
  * sidebar-mini
-->
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <?php 
    include_once("../views/indexelements/navbar.php");
    //<!-- Right navbar links -->
    include_once("../views/indexelements/notifications.php");
    ?>
    
    
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php
  include_once("../views/indexelements/menu.php");
  ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Nez editor</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Nez editor</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
        <div class="col-lg-9">

          </div>
          <div class="col-lg-3">
              <form class="form-inline" id="frmCreatePuzzle">
                <div class="form-group mx-sm-3 mb-2">
                  <label for="puzzleName" class="sr-only">Service name</label>
                  <input type="text" class="form-control" id="puzzleName" placeholder="Puzzle name">
                </div>
                <button type="submit"  class="btn btn-primary mb-2">Create</button>
              </form>
              <!--<form id="frmCreatePuzzle" class="form-inline">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Puzzle name</label>
                    <input type="email" class="form-control" id="puzzleName" placeholder="Enter your puzzle name">
                    <button type="submit" id="publish" class="btn btn-primary mb-2">Create</button>
                  </div>
              </form>-->
          </div>
          <div class="col-lg-3">
            <div class="card">
              <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                  <h3 class="card-title">Pieces</h3>
                  <a href="javascript:void(0);" id="newStage">Create new</a>
                </div>
              </div>
              <div class="card-body" id="pieces"></div>
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col-md-6 -->
          <div class="col-lg-9">
            <div id="propwrap">
                <div id="properties">
                    <div id="close">
                        <img src="../dist/assets/close.svg">
                    </div>
                    <p id="header2">Properties</p>
                    <div id="propswitch">
                        <div id="dataprop">Data</div>
                        <div id="alertprop">Alerts</div>
                        <div id="logsprop">Logs</div>
                    </div>
                    <div id="proplist">
                        <p class="inputlabel">Select database</p>
                        <div class="dropme">Database 1 <img src="../dist/assets/dropdown.svg"></div>
                        <p class="inputlabel">Check properties</p>
                        <div class="dropme">All<img src="../dist/assets/dropdown.svg"></div>
                        <div class="checkus"><img src="../dist/assets/checkon.svg"><p>Log on successful performance</p></div>
                        <div class="checkus"><img src="../dist/assets/checkoff.svg"><p>Give priority to this block</p></div>
                    </div>
                    <div id="divisionthing"></div>
                    <div id="removeblock">Delete blocks</div>
                </div>
            </div>
            
            <div  id="canvas" style="height: 650px; overflow-y: scroll;" ></div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <?php
  include_once("../views/indexelements/footer.php");
  ?>


<!-- Modal -->
<div class="modal fade" id="modalStage" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="staticBackdropLabel">New piece</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="formNewStage">
            <input type="hidden" name="newStage">
            <div class="form-group">
              <label for="nameStage">Name</label>
              <input type="text" class="form-control" id="nameStage"  name="nameStage" placeholder="uncompressingstage">
            </div>
            <div class="form-group">
              <label for="sourceStage">Source</label>
              <input type="text" class="form-control" id="sourceStage" name="sourceStage" placeholder="Only path">
            </div>

            <div class="form-group">
              <label for="sinkStage">Sink</label>
              <input type="text" class="form-control" id="sinkStage" name="sinkStage" placeholder="Only path">
            </div>
            <input type="hidden" name="transformationStage" id="transformationStage" value="0">
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="btnNewStage">Submit</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" id="modalProcessingTasks">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Processing tasks</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12">
              <!-- Custom Tabs -->
              <div class="card">
                <div class="card-header d-flex p-0">
                  <h3 class="card-title p-3"></h3>
                  <ul class="nav nav-pills ml-auto p-2">
                    <li class="nav-item"><a class="nav-link active" href="#tab_1" data-toggle="tab">Choose task</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab_2" data-toggle="tab">Create task</a></li>
                  </ul>
                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                      <div id="respBB">
                          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                          <h1 class="h2">Building Box</h1>
                          <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group mr-2">
                              <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#staticBackdrop">New Building Box</button>
                            </div>
                          </div>
                        </div>

                        <div id="answer">

                        </div>

                        <div id="divTable">


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

                        </div>


                      </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                    <form id="formNewBB" >
                        <div class="form-group">
                          <input type="hidden" name="newBlackBox">
                          <label for="nameBB">Name</label>
                          <input type="text" class="form-control" id="nameBB"  name="nameBB" placeholder="corrections">
                        </div>
                        <div class="form-group">
                          <label for="commandBB">Command</label>
                          <input type="text" class="form-control" id="commandBB" name="commandBB" placeholder="python /app/LS.py @I @N">
                        </div>

                        <div class="form-group">
                          <label for="imageBB">Image</label>
                          <select class="form-control" name="imageBB" id="imageBB" aria-describedby="imageBBHelp">
                            <?php
                            DockerPs();
                            function DockerPs()
                            {
                              echo "entro";
                              exec("docker images --format \"{{.Repository}}:{{.Tag}}\"", $log);
                              print_r($log);
                              foreach($log as $out) {
                                list($Repository, $Tag) = explode(":", $out);
                                if (strpos($Repository, 'none') == false) {
                                  $linkName=$Repository.":".$Tag;
                                  echo '<option value="'.$linkName.'">'.$linkName .'</option>';
                                }
                              }
                            }
                            ?>
                          </select>
                          <small id="imageBBHelp" class="form-text text-muted">Clear text to select other image.</small>
                        </div>
                        
                        <div class="form-group">
                          <label for="portBB">Port</label>
                          <input type="text" class="form-control" id="portBB" name="portBB" placeholder="Port optional">
                        </div>
                        <button type="submit" class="btn btn-primary" id="btnNewBB">Save & add</button>
                        <button type="submit" class="btn btn-secondary" id="btnNewBB2">Save</button>
                      </form>
                    </div>
                    <!-- /.tab-pane -->
                
                    <!-- /.tab-pane -->
                  </div>
                  <!-- /.tab-content -->
                </div><!-- /.card-body -->
              </div>
              <!-- ./card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <!--<button type="button" class="btn btn-primary">Save changes</button>-->
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

  <!-- Modal deleteBB -->
  <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Delete Building Box</h5>
          <button type="button" class="close" onclick="$('#modalDelete').modal('hide');" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure?
          <form  id="deleteBlackBox">
            <input type="hidden" name="deleteBlackBox">
            <input type="hidden" name="idBlackBox" id="idBlackBox"> 
          </form>  
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="$('#modalDelete').modal('hide');">Close</button>
          <button type="button" class="btn btn-danger" id="btnDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>
  
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="../dist/js/adminlte.js"></script>


  <!-- modeler distro -->
  
<script src="https://cdn.jsdelivr.net/gh/alyssaxuu/flowy/flowy.min.js"></script>
<script src="../plugins/toastr/toastr.min.js"></script>
<script src="../views/js/functions.js"></script>

<script>
  var stage_selected = "";
  var stage_selected_key = "";
  function showNFRModal(id, name){
    console.log("aaaa");
  }


  function showTaskModal(id, name){
    stage_selected = name;
    stage_selected_key = id;
    $('#modalProcessingTasks').modal('show');
  }

  $("#newStage").click(function(e){
    e.preventDefault();
    $('#modalStage').modal('show');
  });

  $('#btnNewStage').click(function(){

    var nameStage = $("#nameStage").val();

    $.ajax({
      type: "POST",
      url: "../includes/controllers/controller.php",
      data: $('#formNewStage').serialize(),
      dataType: 'json',
      success: function(data){
        console.log(data);
        toastr.success('Piece created');
        $('#modalStage').modal('hide');
        $(".modal-body input").val('');

      },
      error: function(data){ //se lanza cuando ocurre un error
        toastr.error('Error creating piece');
          console.error(data.responseText);
      }
    });

    
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controllerWorkflowCreator.php",
        data: {readStages:'readStages'},
        dataType: 'json',
        beforeSend: function(){
            $("#pieces").html("Getting data...");
        },
        success  : function(data){ //muestra la respuesta
            //console.log(data);
            document.getElementById("pieces").innerHTML = "";
            data.forEach(function(d){
                //console.log(d);
                document.getElementById("pieces").innerHTML += '<div class="blockelem create-flowy noselect" id="'+d.id+'" name="'+d.name+'"> <input type="hidden" name="idPiece" class="idPiece" value="'+d.id+'"><input type="hidden" name="pieceName" class="pieceName" value="'+d.name+'"><div class="grabme"><img src="../dist/assets/grabme.svg"></div> <div class="blockin">  <div class="blockico"><span></span><span class="fas fa-box"></span></div>   <div class="blocktext">  <p class="blocktitle">'+d.name+'</p><p class="blockdesc"></p>    </div>   </div> </div>';
                //document.getElementById("pieces").innerHTML += '<input type="hidden" name="blockelemtype" class="blockelemtype" value="'+d.id+'">';
                //document.getElementById("pieces").innerHTML += ' <div class="grabme"><span class="fas fa-box"></span></div>';
                //document.getElementById("pieces").innerHTML += ' <div class="blockin"> <div class="blocktext"><p class="blocktitle">'+d.name+'</p><p class="blockdesc"></p> </div> </div> </div>';
            });
        },
        error: function(data){ //se lanza cuando ocurre un error
            $("#pieces").html("No pieces...");
            console.error(data.responseText);
        }
    });
  });

  $('#btnNewBB').click(function(e){
    e.preventDefault();
    
    $.ajax({
      type: "POST",
      url: "../includes/controllers/controller.php",
      data: $('#formNewBB').serialize(),
      success: function(data){
        console.log(data);
        toastr.success('Processing task created and added!');
        $("#processing" + stage_selected_key).append($("#nameBB").val());
        $('#formNewBB')[0].reset();
        $("#processing" + stage_selected_key).append("<br>");
        $('#modalProcessingTasks').modal('hide');
      },error: function(data){ //se lanza cuando ocurre un error
        toastr.error('Error creating piece');
          console.error(data.responseText);
      }
    });
  });

  $('#btnNewBB2').click(function(e){
    e.preventDefault();
    console.log($('#formNewBB').serialize());
    $.ajax({
      type: "POST",
      url: "../includes/controllers/controller.php",
      data: $('#formNewBB').serialize(),
      success: function(data){
        console.log(data);
        toastr.success('Processing task created!');
        $('#formNewBB')[0].reset();
        //$('#modalProcessingTasks').modal('hide');
      },error: function(data){ //se lanza cuando ocurre un error
        toastr.error('Error creating piece');
          console.error(data.responseText);
      }
    });
  });

  function readDataStage(stage, key) {

    $.ajax({
      type: "POST",
      url: "../includes/controllers/controllerWorkflowCreator.php",
      data: {readBBStage:stage},
      dataType: 'json',
      success: function(response){
          console.log(response);
          //document.getElementById("processing").innerHTML = response;
          $("#processing" + key).html("<h6>Processing stages</h6>");
          console.log(key);
          //$("#processing" + key).append("<ul id='ulProcessing"+key+"'>");
          response.forEach(function(d){
            $("#processing" + key).append(d.name);
            $("#processing" + key).append("<br>");
          });
          
          //$("#processing" + key).append("</ul>");

      },error: function(data){ //se lanza cuando ocurre un error
        toastr.error('Error reading piece');
          console.error(data.responseText);
      }
    });
  }

  $('#btnDelete').click(function(){

    $.ajax({
      type: "POST",
      url: "../includes/controllers/controllerWorkflowCreator.php",
      data: $('#deleteBlackBox').serialize(),
      success: function(data){
        toastr.options.closeButton = true;
        toastr.options.progressBar = true;
        toastr.options.positionClass = "toast-bottom-right";
        toastr.warning('<b>Building Box</b> successfully deleted!');
        $('#divTable').html(data);
        $('#tableBB').DataTable({
      "scrollY": 350,
      "scrollX": true
    });
      }
    });

    $('#modalDelete').modal('hide');
    $(".modal-body input").val('')

  });

  function modalDelete(id){
    $("#idBlackBox").val(id);  
    $("#modalDelete").modal();
  }

  function addStage(data){
    console.log(data);
    console.log(stage_selected);
    var arrayDeCadenas = data.split(",");

    var name = arrayDeCadenas[0];
    var id = arrayDeCadenas[1];
    //console.log($("ulProcessing"+stage_selected_key));
    //$("ulProcessing"+stage_selected_key).append("<li>"+name+"</li>");
    data = {"BBStage":id, "BBname":name, "idStageBB":stage_selected}
    $.ajax({
      type: "POST",
      url: "../includes/controllers/controllerWorkflowCreator.php",
      data: data,
      dataType: 'json',
      success: function(data){
        $("#processing" + stage_selected_key).append(name);
        $("#processing" + stage_selected_key).append("<br>");
        toastr.success('Task added to stage!');
      },error: function(data){ //se lanza cuando ocurre un error
        toastr.error('Error adding task');
          console.error(data.responseText);
      }
    });

  
    

  }

  $("#frmCreatePuzzle").submit(function(e){
    e.preventDefault();
    let json2 = flowy.output();
    console.log(json2);
    let emp = {};
    
    for (var i = 0; i <json2.blocks.length; i++) {
      emp[i] = {
        id: json2.blocks[i]['id'], 
        parent: json2.blocks[i]['parent'], 
        name: json2.blocks[i]['attr']['2']['name'] 
      };
    }
    console.log(emp);
    $.ajax({
      type: "POST",
      url: "../includes/controllers/controllerWorkflowCreator.php",
      data: {createWorkflow: '',nameWorkflow: $("#puzzleName").val(), statusWorkflow: "public", stages: JSON.stringify(emp)},
      success: function(data){
        toastr.success('<b>Value Chain</b> successfully created!');
        toastr.success('Task added to stage!');
      },
      error: function(data){
        console.error(data.responseText);
      }
    });
  });

</script>
</body>
</html>
