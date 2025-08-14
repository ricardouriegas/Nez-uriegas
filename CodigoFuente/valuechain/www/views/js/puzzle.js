(async () => {
  getStages();

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const deploy = urlParams.get('deploy');

  if(deploy != null){
    if(deploy == "on"){
      $("#modal-deployStructure").modal('show');
    }
  }

  $("#divOverlayExecution").hide();
  $("#divOverlayStop").hide();
  $("#divOverlayDeploy").hide();

  $("#btnDeploy").click(function (e) {
    e.preventDefault();
    id = $("#txtID").val();
    platform = $("#slPlatform").val();
    puzzle_name = $("#txtPuzzleName").val();
    console.log({ "id": id, "type": "deployPuzzle", "platform": platform });
    
    $.ajax({
      type: "POST",
      url: "../../includes/controllers/controller.php",
      data: { "id": id, "type": "deployPuzzle", "platform": platform },
      dataType: 'json',
      beforeSend: function () {
        $("#divOverlayDeploy").show();
        var registro = new Date().toLocaleString();
        var t = $("#deploymentsTable").DataTable();
        var buttonAction = '<div class="btn-group"><a type="button" class="btn btn-default" disabled>See logs</a><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown"><span class="sr-only">Toggle Dropdown</span></button><div class="dropdown-menu" role="menu"><a class="dropdown-item" >See logs</a></div></div>';
        t.row.add([0, registro, platform, "Deploying...", buttonAction]).draw();
      },
      success: function (data) {
        console.log(data);
        $("#divOverlayDeploy").hide();
        $("#modal-deployStructure").modal('hide');
        var table = $('#deploymentsTable').DataTable();
        var table_length = table.data().count() / 5 - 1;
        //var t = $("#executions").DataTable();
        console.log(table.data().count());
        console.log(table_length);
        var buttonAction = '<div class="btn-group"><a type="button" class="btn btn-default" onclick=\'showLogs(' + data.data.id_deployment + ', \"' + puzzle_name + '\", \"deployment\")\'>See logs</a disabled><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" disabled><span class="sr-only">Toggle Dropdown</span></button><div class="dropdown-menu" role="menu"><a class="dropdown-item" onclick=\'showLogs(' + data.data.id_deployment + ', \"' + puzzle_name + '\", \"deployment\")\' >See logs</a></div></div>';
        if (data.code == 200) {
          toastr.success('Puzzle deployed');
          var temp = table.row(table_length).data([data.data.id_deployment, data.data.deployed, data.data.deployment_mode, data.data.status, buttonAction]).draw();
        } else {
          toastr.error('Error deploying puzzle');
          var temp = table.row(table_length).data([data.data.id_deployment, data.data.deployed, data.data.deployment_mode, "Error", buttonAction]).draw();
        }
        
        //t.row.add(["", registro, platform, "Deploying...", buttonAction]).draw( false );

      }, error: function (data) { //se lanza cuando ocurre un error
        //$("#newDeployment").remove();
        console.log(data);
        $("#divOverlayDeploy").hide();
        toastr.error('Error in server deploying puzzle');
        console.log("error");
        console.error(data.responseText);
      }
    });
  });

  $("#btnExecute").click(function (e) {
    e.preventDefault();
    id = $("#txtID").val();
    platform = $("#slPlatform").val();
    puzzle_name = $("#txtPuzzleName").val();
    console.log(id);
    $.ajax({
      type: "POST",
      url: "../../includes/controllers/controller.php",
      data: { "id": id, "type": "executePuzzle", "puzzle_name":puzzle_name },
      dataType: 'json',
      beforeSend: function () {
        console.log("entro");
        $("#divOverlayExecution").show();
        var registro = new Date().toLocaleString();
        var t = $("#executionsTable").DataTable();
        var buttonAction = '<div class="btn-group"><a type="button" class="btn btn-default" disabled>See logs</a><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown"><span class="sr-only">Toggle Dropdown</span></button><div class="dropdown-menu" role="menu"><a class="dropdown-item" >See logs</a></div></div>';
        t.row.add(["", registro, "", "Executing...", buttonAction]).draw();
      },
      success: function (data) {
        $("#divOverlayExecution").hide();
        $("#modal-executeStructure").modal('hide');
        console.log(data);
        var table = $('#executionsTable').DataTable();
        var table_length = table.data().count() / 5 - 1;
        //var t = $("#executions").DataTable();
        console.log(table.data().count());
        console.log(table_length);
        var buttonAction = '<div class="btn-group"><a type="button" class="btn btn-default" onclick=\'showLogs(' + data.data.id_execution + ', \"' + puzzle_name + '\", \"execution\")\'>See logs</a disabled><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" disabled><span class="sr-only">Toggle Dropdown</span></button><div class="dropdown-menu" role="menu"><a class="dropdown-item" onclick=\'showLogs(' + data.data.id_execution + ', \"' + puzzle_name + '\", \"execution\")\' >See logs</a></div></div>';
        console.log(buttonAction);
        if (data.code == 200) {
          toastr.success('Puzzle deployed');
          var temp = table.row(table_length).data([data.data.id_execution, data.data.executed, data.data.deployment_mode, data.data.status, buttonAction]).draw();
        } else {
          toastr.error('Error deploying puzzle');
          var temp = table.row(table_length).data([data.data.id_execution, data.data.executed, data.data.deployment_mode, "Error", buttonAction]).draw();
        }
        //$("#modal-deployStructure").modal('hide');
        //t.row.add(["", registro, platform, "Deploying...", buttonAction]).draw( false );

      }, error: function (data) { //se lanza cuando ocurre un error
        $("#divOverlayExecution").hide();
        //$("#newDeployment").remove();
        toastr.error('Error in server deploying puzzle');
        console.log("error");
        console.error(data.responseText);
      }
    });
  });

  $("#btnStop").click(function (e) {
    e.preventDefault();
    id = $("#txtID").val();
    puzzle_name = $("#txtPuzzleName").val();
    console.log(id);
    $.ajax({
      type: "POST",
      url: "../../includes/controllers/controller.php",
      data: { "id": id, "type": "stopPuzzle", "puzzle_name":puzzle_name },
      dataType: 'json',
      beforeSend: function () {
        console.log("entro");
        $("#divOverlayStop").show();
      },
      success: function (data) {
        $("#divOverlayStop").hide();
        $("#modal-stopStructure").modal('hide');
        console.log(data);
        
        if (data.code == 200) {
          toastr.success('Puzzle stoped');
        } else {
          toastr.error('Error stopping puzzle');
        }
        //$("#modal-deployStructure").modal('hide');
        //t.row.add(["", registro, platform, "Deploying...", buttonAction]).draw( false );

      }, error: function (data) { //se lanza cuando ocurre un error
        $("#divOverlayStop").hide();
        //$("#newDeployment").remove();
        toastr.error('Error in server stopping puzzle');
        console.log("error");
        console.error(data.responseText);
      }
    });
  });

})();

function showLogs(id, puzzle_name, folder) {
  console.log(id);
  console.log(puzzle_name);
  $.ajax({
    type: "POST",
    url: "../../includes/controllers/controller.php",
    data: { "id": id, "type": "getLogsDeployment", "puzzle_name": puzzle_name, "folder": folder },
    dataType: 'json',
    beforeSend: function () {
      $("#divOverlayLogs").show();
      $("#puzzlelogs").modal('show');
    },
    success: function (data) {
      console.log(data);
      $("#divOverlayLogs").hide();
      if (data.code == 200) {
        toastr.success(data.data.msg);
        $("#divLogs").html(data.data.log.replace(/\n/g, "<br />"));
      } else {
        $("#puzzlelogs").modal('hide');
        toastr.error(data.data.msg);
      }

    }, error: function (data) { //se lanza cuando ocurre un error
      //$("#newDeployment").remove();
      $("#puzzlelogs").modal('hide');
      toastr.error('Error loading logs');
      console.log("error");
      console.error(data.responseText);
    }
  });
}

function downloadLogs() {
  
  var elHtml = document.getElementById("divLogs").innerHTML;
  var link = document.createElement('a');
  mimeType = 'text/plain';

  link.setAttribute('download', "logs.txt");
  link.setAttribute('href', 'data:' + mimeType + ';charset=utf-8,' + encodeURIComponent(elHtml.replace("<br>",/\n/g)));
  link.click();
}


function drawGraph(data) {
  const dag = d3.dagConnect()(data);
  const nodeRadius = 50;
  const layout = d3
    .sugiyama() // base layout
    .decross(d3.decrossOpt()) // minimize number of crossings
    .nodeSize((node) => [(node ? 3.6 : 0.25) * nodeRadius, 3 * nodeRadius]); // set node size instead of constraining to fit
  const { width, height } = layout(dag);

  // --------------------------------
  // This code only handles rendering
  // --------------------------------
  const svgSelection = d3.select("svg");
  //svgSelection.attr("viewBox", [0, 0, $("#svgCanvas").height(), $("#svgCanvas").width()].join(" "));
  const defs = svgSelection.append("defs"); // For gradients

  const steps = dag.size();
  const interp = d3.interpolateRainbow;
  const colorMap = new Map();
  for (const [i, node] of dag.idescendants().entries()) {
    colorMap.set(node.data.id, interp(i / steps));
  }

  // How to draw edges
  const line = d3
    .line()
    .curve(d3.curveCatmullRom)
    .x((d) => d.y)
    .y((d) => d.x);

  // Plot edges
  svgSelection
    .append("g")
    .selectAll("path")
    .data(dag.links())
    .enter()
    .append("path")
    .attr("d", ({ points }) => line(points))
    .attr("fill", "none")
    .attr("stroke-width", 2)
    .attr("stroke", ({ source, target }) => {
      // encodeURIComponents for spaces, hope id doesn't have a `--` in it
      const gradId = encodeURIComponent(`${source.data.id}--${target.data.id}`);
      const grad = defs
        .append("linearGradient")
        .attr("id", gradId)
        .attr("gradientUnits", "userSpaceOnUse")
        .attr("x1", source.y)
        .attr("x2", target.y)
        .attr("y1", source.x)
        .attr("y2", target.x);
      grad
        .append("stop")
        .attr("offset", "0%")
        .attr("stop-color", colorMap.get(source.data.id));
      grad
        .append("stop")
        .attr("offset", "100%")
        .attr("stop-color", colorMap.get(target.data.id));
      return `url(#${gradId})`;
    });

  // Select nodes
  const nodes = svgSelection
    .append("g")
    .selectAll("g")
    .data(dag.descendants())
    .enter()
    .append("g")
    .attr("transform", ({ x, y }) => `translate(${y}, ${x})`);

  // Plot node circles
  nodes
    .append("circle")
    .attr("r", nodeRadius)
    .attr("fill", (n) => colorMap.get(n.data.id));

  // Add text to nodes
  nodes
    .append("text")
    .text((d) => d.data.id)
    .style("font-size", "12px")
    .attr("font-family", "sans-serif")
    .attr("text-anchor", "middle")
    .attr("alignment-baseline", "middle")
    .attr("fill", "black");

  const arrow = d3.symbol().type(d3.symbolTriangle).size(nodeRadius * nodeRadius / 5.0);
  svgSelection.append('g')
    .selectAll('path')
    .data(dag.links())
    .enter()
    .append('path')
    .attr('d', arrow)
    .attr('transform', ({
      source,
      target,
      points
    }) => {
      const [end, start] = points.slice().reverse();
      // This sets the arrows the node radius (20) + a little bit (3) away from the node center, on the last line segment of the edge. This means that edges that only span ine level will work perfectly, but if the edge bends, this will be a little off.
      const dy = start.x - end.x;
      const dx = start.y - end.y;
      const scale = nodeRadius * 1.15 / Math.sqrt(dx * dx + dy * dy);
      // This is the angle of the last line segment
      const angle = Math.atan2(-dy, -dx) * 180 / Math.PI + 90;
      return `translate(${end.y + dx * scale},${end.x + dy * scale}) rotate(${angle})`;
    })
    .attr('fill', ({ target }) => colorMap[target.id])
    .attr('stroke', 'white')
    .attr('stroke-width', 1.5);


}


function getStages() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const id = urlParams.get('id');
  $.ajax({
    type: "POST",
    url: "../../includes/controllers/controller.php",
    data: { "type": "getStages", "id": id },
    dataType: 'json',
    success: function (data) {
      $("#listpieces").html("");
      $("#listsources").html("");
      $("#listreqs").html("");
      workflow_info = data["workflow_data"];
      catalogs = data["catalogs"]["data"]["data"];
      if (workflow_info.code == 200) {
        stages = workflow_info.data.stages;
        requirements = workflow_info.data.requirements;
        edges = [];

        requirements.forEach(x => {
          if (x.type == 1) {
            $("#listreqs").append('<div class="col-lg-3 col-6"> <div class="small-box bg-info"> <div class="inner"><h3>' + x.technique + '</h3> <p>' + x.requirement + '</p></div><div class="icon"><i class="fas fa-tachometer-alt"></i></div></div></div>');
          } else if (x.type == 2) {
            $("#listreqs").append('<div class="col-lg-3 col-6"> <div class="small-box bg-success"> <div class="inner"><h3>' + x.technique + '</h3> <p>' + x.requirement + '</p></div><div class="icon"><i class="fas fa-user-lock"></i></div></div></div>');
          } else if (x.type == 3) {
            $("#listreqs").append('<div class="col-lg-3 col-6"> <div class="small-box bg-secondary"> <div class="inner"><h3>' + x.technique + '</h3> <p>' + x.requirement + '</p></div><div class="icon"><i class="fas fa-cloud-download-alt"></i></div></div></div>');
          }
        });


        stages.forEach(element => {
          element.sinks.forEach(s => {
            edges.push([element.name, s.stage]);
          });

          element.sources.forEach(s => {
            if (s.source_type == 1) {
              catalogs.forEach(c => {
                if (c.tokencatalog == s.catalog) {
                  $("#listsources").append('<div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h4>' + c.namecatalog + '</h4><p>Created: ' + c.created_at + '</p></div><div class="icon"> <i class="fas fa-database"></i> </div></div></div>');
                }
              });
            }

            //$("#listsources").append('<div id="'+e.tokencatalog+'" class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h4>' + e.namecatalog + '</h4><p>Created: ' + e.created_at +  '</p></div><div class="icon"> <i class="fas fa-database"></i> </div></div></div>');
          });

          $("#listpieces").append('<div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>' + element.name + '</h3><p>' + element.buildingblock + '</p></div><div class="icon"><i class="fas fa-puzzle-piece"></i></div></div></div>');
        });
      }
      drawGraph(edges);

    }, error: function (data) { //se lanza cuando ocurre un error
      console.error(data.responseText);
    }
  });
}