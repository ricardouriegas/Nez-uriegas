$(document).ready(function(e) {
  loadServices();
});

function loadServices(){
  $.ajax({
    type: "GET",
    url: "/services/json",
    data: null,
    success: function(data){
      console.log(data);
      drawServices(data.services);
      // toastr.options.closeButton = true;
      // toastr.options.progressBar = true;
      // toastr.options.positionClass = "toast-bottom-right";
      // toastr.success('<b>Value Chain</b> successfully created!');
      // $('#servicesBodyTable').html(data);
      // $('#tableWorkflow').DataTable();
    }
  });
}

function drawServices(data) {
  let t_body = "";
  var btnDel = "";
  var btnPub = "";
  for (let i = 0, n = data.length; i < n; i++) {
    const id = data[i]["id"];
    const name = data[i]["name"];
    const stages = data[i]["stages"];
    if (data[i]["owner"]){
      btnDel = '<button type="button" class="btn btn-danger" onclick="modalDelete(' + id + ');" title="Delete">' +
      '<span class="fas fa-trash-alt"></span></button>';
      btnPub = '<button type="button" class="btn btn-outline-primary" title="Publish to user"' +
        'data-toggle="modal" data-target="#modalPubSub" data-type="publish" data-idworkflow="'+id+'">' +
        '<span class="fas fa-user-plus"></span></button>';
    }
    t_body += 
      '<tr>' +
        '<td>' + id + '</td>' +
        '<td id="name_' + id + '"> ' + name + ' </td>' +
        '<td id="stages' + id + '"> ' + stages + ' </td>' +
        '<td>' +
          btnPub +
        '</td>' +
        '<td>' +
          '<button type="button" class="btn btn-success" onclick="modalRun(\'' + id + '\');" title="Deploy">' +
            '<span class="fas fa-play"></span></button>' +
          '<button type="button" class="btn btn-primary" onclick="modalRead((\'' + name + '\'));" title="Logs">' +
            '<span class="fas fa-file-alt"></span></button>' +
          '<button type="button" class="btn btn-info" onclick="supervise((\'' + name + '\'));" title="Supervision">' +
            '<span class="fas fa-eye"></span></button>' +
          '<button type="button" class="btn btn-warning" onclick="editService(' + id + ');" title="Edit">' +
            '<span class="fas fa-edit"></span></button>' +
          btnDel +
        '</td>' +
      '</tr>';
  }
  $('#servicesBodyTable').html(t_body);
}

    
  
  
function modalDelete(id){
  $("#idWorkflow").val(id);  
  $("#modalDelete").modal();
}
  
$('#btnDelete').click(function(){
  $.ajax({
    type: "DELETE",
    url: "/services/delete",
    data: $('#deleteWorkflow').serialize(),
    success: function(data){
      console.log(data);
      location.reload();
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>Service</b> successfully deleted!');
      // $('#divTable').html(data);
      // $('#tableWorkflow').DataTable();
    }
  });
  $('#modalDelete').modal('hide');
  $(".modal-body input").val('');
});
  
  
function modalRun(id){
  $("#idWorkflowRun").val(id);
  $("#modalRun").modal();
}
  
$('#btnRun').click(function(){
  console.log($('#runWorkflow').serialize());
  $.ajax({
    type: "POST",
    url: "/services/run",
    data: $('#runWorkflow').serialize(),
    success: function(data){
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.warning('<b>Run</b> successfully');
      $('#answer').html(data);
    }
  });
  $('#modalRun').modal('hide');
});

function modalRead(name){ 
  $.ajax({
    type: "POST",
    url: "/services/log",
    data: {"idWorkflowRead":name},
    // beforeSend:function() {
    //     $("#answer").html('<div class="spinner-border text-primary" role="status">  <span class="sr-only">Loading...</span></div>');
    // },
    success: function(data){
      $('#resLog').html(data.log);
      // $("#answer").html('');
    }
  });
  $("#modalRead").modal();
}
  
function supervise(id) {
  console.log('going to supervision');
  console.log(id + '.yml');
  $.ajax({
    type: 'POST',
    url: 'controllers/superviseWorkflow.php',
    data: {'superviseWorkflow': id + '.yml'},
    success: (res) => {
      if (res != '') { 
        //redirect to supervision page
        window.open(res, '_blank');
      }else{
        console.log('empty response');
      }
    }
  });
}
  
$('#new').click(function(){
  location.href('/services/new');
});
  
function editService(id) {
  $('#idEditService').val(id);
  let url = '/services/edit/' + id;
  console.log(url);
  location.href = url;
}




$('#modalPubSub').on('show.bs.modal', function (event) {
  let btn = $(event.relatedTarget); // Button that triggered the modal
  let type = btn.data('type'); // Extract info from data-* attributes
  let idw = btn.data('idworkflow'); // Extract info from data-* attributes
  let modal = $(this);
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  switch (type) {
    case 'publish':
      [h_title, h_body, h_footer] = publishHtmlForModal(idw);
      fetchUsers();
      break;
    case 'subscribe':
      [h_title, h_body, h_footer] = subscribeHtmlForModal(btn);
      break;
    default:
      break;
  }
  modal.find('.modal-title').text(h_title);
  modal.find('.modal-body').html(h_body);
  modal.find('.modal-footer').html(h_footer);
});

function publishHtmlForModal(idw) {
  let h_title = '';
  let h_body = '';
  let h_footer = '';
  h_title = 'Publish Service';
  h_body = '<form id="formPublishWorkflow">' +
      '<input type="hidden" name="idworkflow" value="'+idw+'">' +
      '<div class="form-group m-1">' +
        '<label for="iduser">User</label>' +
        '<select class="form-control" name="iduser" id="iduser" placeholder="Stage name"></select>' +
      '</div>' +
    '</form>';
  h_footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
  '<button type="submit" class="btn btn-primary" onclick="submitPublishWorkflow()">Submit</button>';
  return [h_title, h_body, h_footer];
}

function fetchUsers() {
  fetch('/users/json', {
      method: 'GET',
      body: null
  })
  .then(res => res.json())
  .then(data => drawUsers(data.users.data))
  .catch(err => console.error(err))
}

function drawUsers(data) {
  let html = '';
  if (typeof data === 'undefined') {return;}
  data.forEach(d => {
      html += '<option value="'+d.access_token+'" title="'+d.email+'">'+d.username+'</option>'
  });
  $('#iduser').html(html);
}

function submitPublishWorkflow(){
  $.ajax({
    type: "POST",
    url: "/pubsub/publish",
    data: $('#formPublishWorkflow').serialize(),
    success: function(data){
      console.log(data);
      toastr.options.closeButton = true;
      toastr.options.progressBar = true;
      toastr.options.positionClass = "toast-bottom-right";
      toastr.success('<b>Service</b> successfully published');
    }
  });
  $('#modalDelete').modal('hide');
  $(".modal-body input").val('');
}
