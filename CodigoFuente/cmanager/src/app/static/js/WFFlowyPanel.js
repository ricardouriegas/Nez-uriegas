var root_box = {
    id: "",
    name: "",
  };
var boxes_data = [];
var root_added = false;
var tempblock;
var raw_root_output = {"html":"<div class=\"indicator invisible\"></div><div id=\"c-service-root\" name=\"SERVICE NAME\" class=\"blockelem noselect card block position-absolute\" style=\"left: 188px; top: 15px;\" tmp-id=\"service-root\">       <div class=\"card-header bg-primary text-white d-flex justify-content-between\"><p class=\"m-0\">SERVICE NAME</p></div><input type=\"hidden\" name=\"blockid\" class=\"blockid\" value=\"0\"></div>","blockarr":[{"parent":-1,"childwidth":0,"id":0,"x":708,"y":111.5,"width":318,"height":51}],"blocks":[{"id":0,"parent":-1,"data":[{"name":"blockid","value":"0"}],"attr":[{"id":"c-service-root"},{"name":"SERVICE NAME"},{"class":"blockelem noselect card block position-absolute"},{"style":"left: 188px; top: 15px;"},{"tmp-id":"service-root"}]}]};
var flag_opt_btns = false;
var flag_is_new_service = true;


$(document).ready(function(e) {
  startPanel();
  newOrEditService();
});

function startPanel() {
  let spacing_x = 20;
  let spacing_y = 20;
  flowy(document.getElementById("canvas"), onGrab, onRelease, onSnap, onRearrange,
    spacing_x, spacing_y);  
}

function onGrab(block) {
  // When the user grabs a block
  console.log('grab');
  block.classList.add("blockdisabled");
  tempblock = block;
}

function onRelease() {
  // When the user releases a block
  console.log('release');
  if (tempblock) tempblock.classList.remove("blockdisabled");
}

function onSnap(drag, first, parent) {
  // When a block snaps with another one
  console.log('snapping');
  // console.log(drag);
  prepareDraggedBlock(drag, parent);
  return true;
}

function onRearrange(block, parent) {
  // When a block is rearranged
  console.log('rearranging'); 
  // console.log('block: ', block, ' parent: ', parent);
  return false; 
}

function newOrEditService() {
  let wfid = $('#idEditService').val();
  if (wfid == '') {
    let modal = $('#modalNewService');
    modal.modal('show');
    $('#btnSaveService').val('');
  } else {
    flag_is_new_service = false;
    $('#btnSaveService').val(wfid);
    $.ajax({
      type: 'GET',
      url: '/services/id/'+wfid,
      data: null,
      success: function (data) {
        if (data['rawgraph'] && data['rawgraph'] != '') {
          let out = data.rawgraph.replace(/\\"/gi, ':XX:');
          let out_o = JSON.parse(out);
          let h = out_o.html.replace(/:XX:/gi, '\"');
          out_o.html = h
          flowy.import(out_o);
          root_box.name = data.name;
          root_added = true;
          showButtons(true);
          loadStages();
        }else{
          console.log('service not found');
        }
      },
      error: function (error) {
        console.log(error);
      }
    });
  }
}

$('#btnNewService').click(function() {
  let serviceName = $('#newServiceName').val();
  let rootOutput = {...raw_root_output};
  let new_html = rootOutput.html.replace(/SERVICE NAME/gi, serviceName);
  rootOutput.html = new_html;
  flowy.import(rootOutput);
  root_added = true;
  root_box.name = serviceName;
  let modal = $('#modalNewService');
  $('#newServiceName').val('');
  modal.modal('hide');
  showButtons(true);
  loadStages()
});

function showButtons(turn) {
  if (turn == true) {
    document.getElementById('header').classList.remove('d-none');
    document.getElementById('header-secondary').classList.remove('d-none');
  } else {
    document.getElementById('header').classList.add('d-none');
    document.getElementById('header-secondary').classList.add('d-none');
  }
}

function drawFlowyBoxesStages(boxes) {
  // TODO: fix error that does not draw half of tries
  $('#blocklist').html('');
  for (let i = 0, n = boxes.length; i < n; i++) {
    let id = boxes[i].id;
    let name = boxes[i].name;
    let owner = boxes[i].owner;
    let source = boxes[i].source;
    let sink = boxes[i].sink;
    let transformation = boxes[i].transformation;
    let bb_id = boxes[i].bb_id;
    if (owner) {
      var box = 
      '<div id="'+id+'" name="'+name+'" class="create-flowy blockelem noselect card"> \
        <div class="card-header bg-primary text-white d-flex justify-content-between">' +
          // '<span class="fas fa-box"></span>' +
          name +
        '</div>' +
        '<div class="card-body p-1 text-center stage-show-options d-none">'+
          '<button class="btn btn-outline-success btn-sm" title="Add app" onclick="btnAddBBToStage(this);" data-st-id="'+id+'" data-bb-id="'+bb_id+'" data-bb-name="'+transformation+'"><span class="fas fa-plus-square"></span></button>' +
          '<button class="btn btn-outline-warning btn-sm pl-1" title="Edit stage"' +
            'data-toggle="modal" data-target="#modalEditElement" data-type="stage" data-id="'+id+'" data-name="'+name+'" data-source="'+source+'" data-sink="'+sink+'" data-transformation="'+transformation+'">' +
            '<span class="fas fa-edit"></span></button>' +
          '<button class="btn btn-outline-danger btn-sm" title="Delete stage"' +
            'data-toggle="modal" data-target="#modalConfirmDeleteElement" data-type="stage" data-id="'+id+'" data-name="'+name+'">' +
            '<span class="fas fa-trash"></span></button>' +
        '</div>' +
        // '<div class="card-footer text-center bg-dark text-white">'+
        // '</div>' +
      '</div>'
      ;
      $('#blocklist').append(box);
    }
  }
  // $('#blocklist').html(boxes_html);
  activateStageOptions();
}

function prepareDraggedBlock(drag, parent) {
  let id = drag.getAttribute("id");
  let tmp_id = "c-" + id;
  let body = drag.querySelector(".card-body");
  
  drag.setAttribute('id', tmp_id);
  drag.setAttribute('tmp-id', id);
  drag.classList.add('position-absolute');
  body.parentNode.removeChild(body);
}

function loadStages() {
  $.ajax({
    type: 'GET',
    url: '/stages/json',
    data: null,
    success: function (data) {
      if (data.stages) {
        boxes_data = data.stages;
        drawFlowyBoxesStages(boxes_data);
      }else{
        console.log('no stages found')
      }
    },
    error: function (error) {
      console.log(error);
    }
  });
}

$('#modalNewElement').on('show.bs.modal', function (event) {
  let btn = $(event.relatedTarget); // Button that triggered the modal
  let type = btn.data('type'); // Extract info from data-* attributes
  let modal = $(this);
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  switch (type) {
    case 'stage':
      [h_title, h_body, h_footer] = newStageHtmlForModal(btn);
      break;
    case 'buildingblock':
      [h_title, h_body, h_footer] = newBBHtmlForModal(btn);
      break;
    default:
      break;
  }
  modal.find('.modal-title').text(h_title);
  modal.find('.modal-body').html(h_body);
  modal.find('.modal-footer').html(h_footer);
});

function newStageHtmlForModal() {
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  h_title = 'New Stage';
  h_body = '<form id="formNewStage">' +
      '<input type="hidden" name="newStage">' +
      '<div class="form-group">' +
        '<label for="nameStage">Name</label>' +
        '<input type="text" class="form-control" name="nameStage" placeholder="Stage name">' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="sourceStage">Source</label>' +
        '<input type="text" class="form-control" name="sourceStage" placeholder="Only path">' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="sinkStage">Sink</label>' +
        '<input type="text" class="form-control" name="sinkStage" placeholder="Only path">' +
      '</div>' +
      '<input type="hidden" name="transformationStage" value="">' +
    '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
  '<button type="submit" class="btn btn-primary" onclick="submitNewStage()">Submit</button>';
  return [h_title, h_body, h_footer];
}

function newBBHtmlForModal() {
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  let images = loadDockerImages();
  
  h_title = 'New App';
  h_body = '<form id="formNewBB">' +
    '<div class="form-group">' +
      '<label for="nameBB">Name</label>' +
      '<input type="text" class="form-control" name="nameBB" placeholder="corrections" required>' +
    '</div>' +
    '<div class="form-group">' +
      '<label for="imageBB">Image</label>' +
      '<select class="form-control" name="imageBB" id="imageBB">' +
      '</select>' +
    '</div>' +
    '<div class="form-group">' +
      '<label for="commandBB">Command</label>' +
      '<input type="text" class="form-control" name="commandBB" placeholder="python /app/LS.py @I @N">' +
    '</div>' +
    '<div class="form-group">' +
      '<label for="portBB">Port</label>' +
      '<input type="text" class="form-control" name="portBB" placeholder="Port optional">' +
    '</div>' +
  '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
  '<button type="submit" class="btn btn-primary" onclick="submitNewBB()">Submit</button>';
  return [h_title, h_body, h_footer];
}

function loadDockerImages() {
  $.ajax({
    type: "GET",
    url: "/images/json",
    data: null,
    success: function(data){
      let images_html = '';
      data.images.forEach(el => {
        images_html += '<option value="'+el.RepoTags[0]+'">'+el.RepoTags[0]+'</option>';
      });
      $('#imageBB').html(images_html);
      // toastr.options.closeButton = true;
      // toastr.options.progressBar = true;
      // toastr.options.positionClass = "toast-bottom-right";
      // toastr.warning('<b>Stage</b> successfully deleted!');
    },
    error: function(error) {
      console.log(error);
    }
  });
}

function submitNewStage(){
  $.ajax({
    type: "POST",
    url: "/stages/create",
    data: $('#formNewStage').serialize(),
    success: function(data){
      console.log('#is 1');
      console.log(data);
      console.log('#is 1.1');
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.success('<b>Stage</b> successfully created!');
    }
  });
  console.log('#is 3');
  let modal = $('#modalNewElement');
  modal.modal('hide');
  modal.find('.modal-title').text('');
  modal.find('.modal-body').html('');
  modal.find('.modal-footer').html('');
  loadStages();
}

function submitNewBB(){
  $.ajax({
    type: "POST",
    url: "/buildingblocks/create",
    data: $('#formNewBB').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.success('<b>Building Box</b> successfully created!');
    }
  });
  let modal = $('#modalNewElement');
  modal.modal('hide');
  modal.find('.modal-title').text('');
  modal.find('.modal-body').html('');
  modal.find('.modal-footer').html('');
  loadBBs();
}

$('#btnShowStageOptions').click(function () {
  let stage_opts_isactive = this.getAttribute('aria-pressed');
  if (stage_opts_isactive == 'true') {
    // deactivating
    flag_opt_btns = false;
  }else{
    // activating
    flag_opt_btns = true;
  }
  activateStageOptions();
});

function activateStageOptions() {
  let opts = $(".stage-show-options");
  if (flag_opt_btns == false) {
    // are inactive
    opts.addClass('d-none');
    let blocks = $(".create-flowy-paused");
    blocks.removeClass('create-flowy-paused').addClass('create-flowy');
  }else{
    // are activate
    opts.removeClass('d-none');
    let blocks = $(".create-flowy");
    blocks.removeClass('create-flowy').addClass('create-flowy-paused');
  }
}

$('#modalConfirmDeleteElement').on('show.bs.modal', function (event) {
  let btn = $(event.relatedTarget); // Button that triggered the modal
  let type = btn.data('type'); // Extract info from data-* attributes
  let modal = $(this);
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  switch (type) {
    case 'stage':
      [h_title, h_body, h_footer] = deleteStageHtmlForModal(btn);
      break;
    case 'buildingblock':
      [h_title, h_body, h_footer] = deleteBBHtmlForModal(btn);
      break;
    default:
      break;
  }
  modal.find('.modal-title').text(h_title);
  modal.find('.modal-body').html(h_body);
  modal.find('.modal-footer').html(h_footer);
});

function deleteStageHtmlForModal(btn) {
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  let id = btn.data('id');
  let name = btn.data('name');
  h_title = 'Are you sure to delete stage: ' + name + '?';
  h_body = '<form id="formDeleteStage">' +
      '<input type="hidden" name="idStage" value="'+id+'">' +
    '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>' +
    '<button type="button" class="btn btn-primary" onclick="btnConfirmDeleteStage()">Delete</button>';
  return [h_title, h_body, h_footer];
}

function deleteBBHtmlForModal(btn) {
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  let id = btn.data('id');
  let name = btn.data('name');
  h_title = 'Are you sure to delete stage: ' + name + '?';
  h_body = '<form  id="formDeleteBB">' +
      '<input type="hidden" name="idBuildingBlock" value="'+id+'">' +
    '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>' +
    '<button type="button" class="btn btn-primary" onclick="btnConfirmDeleteBB()">Delete</button>';
  return [h_title, h_body, h_footer];
}

function btnConfirmDeleteStage() {
  $.ajax({
    type: "DELETE",
    url: "/stages/delete",
    data: $('#formDeleteStage').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>Stage</b> successfully deleted!');
    },
    error: function(error) {
      console.log(error);
    }
  });
  let modal = $('#modalConfirmDeleteElement');
  modal.modal('hide');
  modal.find(".modal-body").html('');
  loadStages();
}

function btnConfirmDeleteBB() {
  $.ajax({
    type: "DELETE",
    url: "/buildingblocks/delete",
    data: $('#formDeleteBB').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>App</b> successfully deleted!');
    }
  });
  let modal = $('#modalConfirmDeleteElement');
  modal.modal('hide');
  modal.find(".modal-body").html('');
  loadBBs();
}

$('#modalEditElement').on('show.bs.modal', function (event) {
  let btn = $(event.relatedTarget); // Button that triggered the modal
  let type = btn.data('type'); // Extract info from data-* attributes
  let modal = $(this);
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  switch (type) {
    case 'stage':
      [h_title, h_body, h_footer] = editStageHtmlForModal(btn);
      break;
    case 'buildingblock':
      [h_title, h_body, h_footer] = editBBHtmlForModal(btn);
      break;
    default:
      break;
  }
  modal.find('.modal-title').text(h_title);
  modal.find('.modal-body').html(h_body);
  modal.find('.modal-footer').html(h_footer);
});

function editStageHtmlForModal(btn) {
  let id = btn.data('id');
  let name = btn.data('name');
  let source = btn.data('source');
  let sink = btn.data('sink');
  let transformation = btn.data('transformation');
  let h_title = 'Edit stage: ' + name;
  let h_body = '<form id="formEditStage">' +
      '<input type="hidden" name="updateIdST" value="'+id+'">' +
      '<div class="form-group">' +
        '<label for="updateNameST">Name</label>' +
        '<input type="text" class="form-control" name="updateNameST" value="'+name+'" required>' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="updateSourceST">Source</label>' +
        '<input type="text" class="form-control" name="updateSourceST" value="'+source+'">' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="updateSinkST">Sink</label>' +
        '<input type="text" class="form-control" name="updateSinkST" value="'+sink+'">' +
      '</div>' +
      '<input type="hidden" class="form-control" name="updateTransfST" value="'+transformation+'">' +
    '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
    '<button type="button" class="btn btn-primary" onclick="btnEditStage()">Update</button>';
  return [h_title, h_body, h_footer];
}

function editBBHtmlForModal(btn) {
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  let id = btn.data('id');
  let name = btn.data('name');
  let image = btn.data('image');
  let command = btn.data('command');
  let port = btn.data('port');
  h_body = '<form id="formEditBB" class="needs-validation" novalidate>' +
      '<input type="hidden" name="updateIdBB" value="'+id+'">' + 
      '<div class="form-group">' +
        '<label for="updateNameBB">Name</label>' +
        '<input type="text" class="form-control" name="updateNameBB" value="'+name+'" required>' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="updateImageBB">Image</label>' +
        '<input type="text" class="form-control" name="updateImageBB" value="'+image+'" required>' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="updateCommandBB">Command</label>' +
        '<input type="text" class="form-control" name="updateCommandBB" value="'+command+'" required>' +
      '</div>' +
      '<div class="form-group">' +
        '<label for="updatePortBB">Port</label>' +
        '<input type="text" class="form-control" name="updatePortBB" value="'+port+'" required>' +
      '</div>' +
    '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
    '<button type="button" class="btn btn-primary" onclick="btnEditBB()">Update</button>';
  return [h_title, h_body, h_footer];
}

function btnEditStage() {
  $.ajax({
    type: "PUT",
    url: "/stages/edit",
    data: $('#formEditStage').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>Stage</b> successfully updated!');
    },
    error: function(error) {
      console.log(error);
    }
  });
  let modal = $('#modalEditElement');
  modal.modal('hide');
  modal.find(".modal-title").text('');
  modal.find(".modal-body").html('');
  modal.find(".modal-footer").html('');
  loadStages();
}

function btnEditBB() {
  $.ajax({
    type: "PUT",
    url: "/buildingblocks/edit",
    data: $('#formEditBB').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>App</b> successfully updated!');
    },
    error: function(error) {
      console.log(error);
    }
  });
  let modal = $('#modalEditElement');
  modal.modal('hide');
  modal.find(".modal-title").text('');
  modal.find(".modal-body").html('');
  modal.find(".modal-footer").html('');
  loadBBs();
}

$('#btnDeleteBlocks').click(function() {
  flowy.deleteBlocks();
  $('#blocklist').html('');
  root_added = false;
  showButtons(false);
  let modal = $('#modalNewService');
  modal.modal('show');
});

function btnAddBBToStage(btn) {
  loadBBs();
  let st_id = btn.getAttribute('data-st-id');
  let bb_id = btn.getAttribute('data-bb-id');
  let bb_name = btn.getAttribute('data-bb-name');
  let stagecard = '<div class="card text-white bg-secondary">' +
    '<div class="card-header" id="addBBHeader">'+bb_name+'</div>' +
      '<div class="card-body d-none">' +
        '<form id="formBBStage">' +
          '<input type="hidden" id="addBBidStage" name="idStage" value="'+st_id+'">' +
          '<input type="hidden" id="addBBidBB" name="idBB" value="'+bb_id+'">' +
          '<input type="hidden" id="addBBnameBB" name="nameBB" value="'+bb_name+'">' +
        '</form>' +
      '</div>' +
    '</div>' +
    '</div>';
  $("#selectedBB").html(stagecard);
  let modal = $('#modalAddBBToStage');
  modal.modal('show');
}

function loadBBs() {
  $.ajax({
    type: "GET",
    url: "/buildingblocks/json",
    data: null,
    success: function(response){
      let res_html = '';
      let data = response.buildingblocks;
      data.forEach(elm => {
        const id = elm["id"];
        const name = elm["name"];
        const image = elm["image"];
        const command = elm["command"];
        const port = elm["port"];
        const row = '<tr>' +
          '<td>'+id+'</td>' +
          '<td id="name_'+id+'">'+name+'</td>' +
          '<td id="image_'+id+'">'+image+'</td>' +
          '<td id="command_'+id+'">'+command+'</td>' +
          '<td id="port_'+id+'">'+port+'</td>' +
          '<td>' +
            '<button type="button" class="btn btn-success" onclick="addStage(this)" title="Add/Replace app"' +
              'data-bb-id="'+id+'" data-bb-name="'+name+'">' +
              '<span class="fas fa-plus" value=""></span></button>' +
            '<button type="button" class="btn btn-outline-warning" title="Edit app"' +
              'data-toggle="modal" data-target="#modalEditElement" data-type="buildingblock"' +
              'data-id="'+id+'" data-name="'+name+'" data-image="'+image+'" data-command="'+command+'" data-port="'+port+'">' +
              '<span class="fas fa-edit"></span></button>' +
            '<button type="button" class="btn btn-outline-danger" title="Delete app"' +
              'data-toggle="modal" data-target="#modalConfirmDeleteElement" data-type="buildingblock"' +
              'data-id="'+id+'" data-name="'+name+'">' +
              '<span class="fas fa-trash-alt"></span></button>' +
          '</td>' +
          '</tr>';
        res_html += row;
      });
      $('#respBBList').html(res_html);
      // $('#tableBB').DataTable({
      //   "scrollY": 350,
      //   "scrollX": true
      // });
    }
  });
}

function addStage(btn) {
  let id = btn.getAttribute('data-bb-id');
  let name = btn.getAttribute('data-bb-name');
  $("#addBBHeader").text(name);
  $("#addBBidBB").val(id);
  $("#addBBnameBB").val(name);
}

$('#btnSubmitBBToStage').click(function () {
  $.ajax({
    type: "PUT",
    url: "/stages/updatetransformation",
    data: $('#formBBStage').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>Building Block</b> successfully added!');
      // $("#btnBBStage").attr('hidden', true);
    },
    error: function(error) {
      console.log(error.responseText);
    }
  });
  let modal = $('#modalAddBBToStage');
  modal.modal('hide');
  $('#selectedBB').html('');
  loadStages();
});

$('#btnSaveService').click(function() {
  let f_out = flowy.output();
  let emp = {};
  for (let i = 1, n = f_out.blocks.length; i < n; i++) {
    emp[i] = {
      id: f_out.blocks[i]['id'],
      parent: f_out.blocks[i]['parent'],
      name: f_out.blocks[i]['attr']['1']['name']
    };
  }
  let type = 'POST';
  let url = '/services/create';
  let data = {
    "nameWorkflow": root_box.name,
    "statusWorkflow": "0",
    "stages": JSON.stringify(emp),
    "rawgraph": JSON.stringify(f_out)
  };
  if (this.value != '') {
    type = 'PUT';
    url = '/services/edit';
    data['updateIdWF'] = this.value;
  }
  $.ajax({
    type: type,
    url: url,
    data: {...data},
    success: function(data){
      // toastr.options.closeButton = true;
      // toastr.options.progressBar = true;
      // toastr.options.positionClass = "toast-bottom-right";
      // toastr.success('<b>Value Chain</b> successfully created!');
    },
    error: function(error){
      console.log(error);
    }
  });
  $('#idEditService').val();
  // $("#nameWorkflow").val('');
  alert("Service submited");
  // TODO: redirect to services list
  // location.reload();
});

$('#btnCancelService').click(function() {
  window.history.back();
});

// allows multiple modals overlay
$(document).on('show.bs.modal', '.modal', function () {
  var zIndex = 1040 + (10 * $('.modal:visible').length);
  $(this).css('z-index', zIndex);
  setTimeout(function() {
      $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
  }, 0);
});
