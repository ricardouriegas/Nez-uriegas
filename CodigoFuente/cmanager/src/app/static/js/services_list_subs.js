$(document).ready(function(e) {
  loadServicesSubs();
});

function loadServicesSubs(){
  $.ajax({
    type: "GET",
    url: "/services/subs/json",
    data: null,
    success: function(data){
      console.log(data);
      drawServicesSubs(data.services);
      // toastr.options.closeButton = true;
      // toastr.options.progressBar = true;
      // toastr.options.positionClass = "toast-bottom-right";
      // toastr.success('<b>Value Chain</b> successfully created!');
      // $('#servicesBodyTable').html(data);
    }
  });
}

function drawServicesSubs(data) {
  let t_body = "";
  var btnDel = "";
  var btnSub = "";
  var btnRun = "";
  var btnRead = "";
  var btnSupervise = "";
  var btnSupervise = "";
  for (let i = 0, n = data.length; i < n; i++) {
    const id = data[i]["idworkflow"];
    const idpub = data[i]["idpub"];
    const name = data[i]["wfname"];
    const stages = data[i]["stages"];
    const sub = data[i]["subscribed"];
    // if (data[i]["owner"]){
    //   btnDel = '<button type="button" class="btn btn-danger" onclick="modalDelete(' + id + ');" title="Delete">' +
    //   '<span class="fas fa-trash-alt"></span></button>';
    // }
    if (sub) {
      btnRun = '<button type="button" class="btn btn-success" onclick="modalRun(\'' + id + '\');" title="Deploy">' +
        '<span class="fas fa-play"></span></button>';
      btnRead = '<button type="button" class="btn btn-primary" onclick="modalRead((\'' + name + '\'));" title="Logs">' +
        '<span class="fas fa-file-alt"></span></button>';
      btnSupervise = '<button type="button" class="btn btn-info" onclick="supervise((\'' + name + '\'));" title="Supervision">' +
        '<span class="fas fa-eye"></span></button>';
      // '<button type="button" class="btn btn-warning" onclick="editService(' + id + ');" title="Edit">' +
      //   '<span class="fas fa-edit"></span></button>' +
    } else {
        btnSub = '<button type="button" class="btn btn-outline-primary" title="Subscribe to service"' +
          'onclick="subscribeToService(\'' + idpub + '\');">' +
          '<span class="fas fa-check"></span></button>';
    }
    t_body += 
      '<tr>' +
        '<td>' + id + '</td>' +
        '<td id="name_' + id + '"> ' + name + ' </td>' +
        '<td id="stages' + id + '"> ' + stages + ' </td>' +
        '<td>' +
          btnSub +
        '</td>' +
          btnRun + btnRead + btnSupervise +
        '<td>' +
          
        '</td>' +
      '</tr>';
  }
  $('#servicesBodyTable').html(t_body);
}

function subscribeToService(idpublish) {
  $.ajax({
    type: "POST",
    url: "/services/subscribe/confirm",
    data: {idpublish: idpublish},
    success: function(data){
      console.log(data);
      drawServicesSubs(data.services);
      // toastr.options.closeButton = true;
      // toastr.options.progressBar = true;
      // toastr.options.positionClass = "toast-bottom-right";
      // toastr.success('<b>Value Chain</b> successfully created!');
      // $('#servicesBodyTable').html(data);
    }
  });
}