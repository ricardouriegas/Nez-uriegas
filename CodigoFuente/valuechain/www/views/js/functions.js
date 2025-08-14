$(function () {

    $("#divOverlayPublish").hide();

    $("#example1").DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
    });
/*
    $("#executions").DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": true,
        "sort": true,
        "order": [[ 0, "asc" ]],
        "autoWidth": false,
        "responsive": true,
    });*/

    $('#deploymentsTable').DataTable( {
        order: [[ 1, 'desc' ], [ 0, 'asc' ]],
        "lengthChange": false,
        "searching": false,

    } );

    $('#executionsTable').DataTable( {
        order: [[ 1, 'desc' ]],
        "lengthChange": false,
        "searching": false,

    } );


    $('#btnNewBB').click(function (e) {
        e.preventDefault();

        name_bb = $("#nameBB").val();
        image = $("#imageBB").val();
        command = $("#commandBB").val();
        description = $("#descriptionBB").val();

        $.ajax({
            type: "POST",
            url: "../includes/controllers/controller.php",
            data: { "nameBB": name_bb, "imageBB": image, "commandBB": command, "descriptionBB": description, "type": "createBB" },
            dataType: 'json',
            success: function (data) {
                if (data.code == 201) {
                    toastr.success('Processing task created and added!');
                    $('#formNewBB')[0].reset();
                    $('#vert-tabs-tab a[href="#vert-tabs-home"]').tab('show');
                } else {
                    toastr.error('Error creating piece');
                }
                $('#formNewBB')[0].reset();
                $('#vert-tabs-tab a[href="#vert-tabs-home"]').tab('show');
            }, error: function (data) { //se lanza cuando ocurre un error
                toastr.error('Error creating piece');
                console.log("error");
                console.error(data.responseText);
            }
        });
    });

    $("#btnNewGroup").click(function (e) {
        e.preventDefault();
        name_C = $("#catalogName").val();
        group = $("#cataloggroup").val();

        $.ajax({
            type: "POST",
            url: "../includes/controllers/controller.php",
            data: { "name_C": name_C, "group": group, "type": "createCatalog" },
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if (data.code == 201) {
                    toastr.success('Catalog created and added!');
                    $('#vert-tabs-data a[href="#ver-tabs-pub"]').tab('show');
                    $('#formNewCatalog')[0].reset();
                    load_data();
                } else if (data.code == 400) {
                    toastr.error(data.data.message);
                } else {
                    toastr.error('Error creating catalog');
                }
            }, error: function (data) { //se lanza cuando ocurre un error
                toastr.error('Error creating catalog');
                console.log("error");
                console.error(data.responseText);
            }
        });
    });

    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        e.target // newly activated tab
        e.relatedTarget // previous active tab
        //$("#"+e.target.id.substring(0,e.relatedTarget.id.length-4)).html("");
        if (e.target.id == "vert-tabs-home-tab") {
            load_pieces();
        }
    });

});


function load_pieces() {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "readBBs" },
        dataType: 'json',
        beforeSend: function () {
            $("#grid-pieces").html('<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>');
        },
        success: function (data) {
            toastr.info('Pieces loaded!');
            $("#grid-pieces").html("");
            if (data["response"]["data"].length > 0) {
                data["response"]["data"].forEach(function (e) {
                    if (data["pieces"] != null && data["pieces"].hasOwnProperty(e.id)) {
                        $("#grid-pieces").append('<div class="col-sm-4"><div class="card"> <div class="card-body" > <h4 class="card-title">' + e.name.toUpperCase() + '</h4> <p class="card-text"><span class="text-primary">Image</span> <code>' + e.image + '</code> </p><p class="card-text"><span class="text-primary">Command</span> <code>' + e.command + '</code> </p><p class="card-text"><span class="text-primary">Description</span> <div style="height:75px;overflow-y: auto;">' + e.description + '</div> </p><button onclick="removePiece(' + e.id + ', \'' + e.name + '\', \'' + e.image + '\', this)" type="button" class="btn btn-danger btn-block"><i class="fa fa-minus-circle"></i>Remove</button></div></div>');

                        var box = '<div class="blockelem create-flowy noselect " id="' + e.id + '" name = "' + e.name + '"><input type="hidden" name="blockelemtype" class="blockelemtype" value="' + e.id + '"><div class="grabme"><i class="fas fa-grip-vertical"></i></div><div class="blockin"> <div class="blockico"><span></span><i class="fas fa-puzzle-piece"></i></div><div class="blocktext"><p class="blocktitle">' + e.name + '</p><p class="blockdesc">' + e.image + '</p>        </div></div></div>';

                        $('#blocklist').append(box);

                    } else {
                        $("#grid-pieces").append('<div class="col-sm-4"><div class="card"> <div class="card-body" > <h4 class="card-title">' + e.name.toUpperCase() + '</h4> <p class="card-text"><span class="text-primary">Image</span> <code>' + e.image + '</code> </p><p class="card-text"><span class="text-primary">Command</span> <code>' + e.command + '</code> </p><p class="card-text"><span class="text-primary">Description</span> <div style="height:75px;overflow-y: auto;">' + e.description + '</div> </p><button onclick="addPiece(' + e.id + ', \'' + e.name + '\', \'' + e.image + '\', this)" type="button" class="btn btn-primary btn-block"><i class="fa fa-plus"></i>Add</button></div></div>');
                    }

                });
            }

        }, error: function (data) { //se lanza cuando ocurre un error
            toastr.error('Error loading pieces');
            console.log("error");
            console.error(data.responseText); data
        }
    });
}


function addPiece(id, name, image, button) {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "addBB", "id": id, "name": name, "image": image },
        dataType: 'json',
        beforeSend: function () {
            button.classList.remove("btn-primary");
            button.classList.add("btn-info");
            button.innerHTML = "<i class='fa fa-spinner'></i>Adding...";
            //$("#grid-pieces").html('<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>');
        },
        success: function (data) {
            toastr.info(data["message"]);
            button.classList.remove("btn-info");
            button.classList.add("btn-danger");
            button.innerHTML = "<i class='fa fa-minus-circle'></i>Remove";
            button.onclick = function () { removePiece(id, name, image, button) };

            var box = '<div class="blockelem create-flowy noselect " id="' + id + '" name = "' + name + '"><input type="hidden" name="blockelemtype" class="blockelemtype" value="' + id + '"><div class="grabme"><i class="fas fa-grip-vertical"></i></div><div class="blockin"> <div class="blockico"><span></span><i class="fas fa-puzzle-piece"></i></div><div class="blocktext"><p class="blocktitle">' + name + '</p><p class="blockdesc">' + image + '</p>        </div></div></div>';

            $('#blocklist').append(box);

        }, error: function (data) { //se lanzload_data()ry");
            button.classList.remove("btn-info");
            button.innerHTML = "<i class='fa fa-plus'></i>Add";
            button.onclick = function () { addPiece(id, name, image, button) };

        }
    });
}

function removePiece(id, name, image, button) {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "removeBB", "id": id },
        dataType: 'json',
        beforeSend: function () {
            button.classList.remove("btn-danger");
            button.classList.add("btn-info");
            button.innerHTML = "<i class='fa fa-spinner'></i>Removing...";
        },
        success: function (data) {
            console.log(data);
            toastr.info(data["message"]);
            if (data.code == 0) {
                button.classList.remove("btn-info");
                button.classList.add("btn-primary");
                button.innerHTML = "<i class='fa fa-plus'></i>Add";
                $("#" + id).remove();
                flowy.deleteBlocks();
                button.onclick = function () { addPiece(id, name, image, button) };
            }


        }, error: function (data) { //se lanza cuando ocurre un error
            console.error(data.responseText);
            toastr.error('Error removing piece');
            console.log("error");
            button.classList.remove("btn-infload_data()");
            button.innerHTML = "<i class='fa fa-minus-circle'></i>Remove";
            button.onclick = function () { removePiece(id, name, image, button) };

        }
    });
}

function load_nfrs() {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "nfrs" },
        dataType: 'json',
        beforeSend: function () {
            $("#requirements").html('<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>');
        },
        success: function (data) {
            console.log("NFRS");
            console.log(data);
            $("#requirements").html("");
            if (data["nfrs"]["code"] == 200) {
                toastr.info('Requirements loaded!');
                if (data["nfrs"]["data"] != null) {
                    data["nfrs"]["data"].forEach(function (e) {
                        if (data["added"].hasOwnProperty(e.id)) {
                            if (e.type == 1) {
                                $("#requirements").append('<div id=' + (e.id + e.technique + e.requirement).replace(/[^\w\s]/gi, '') + ' class="col-lg-3 col-6"> <div class="small-box bg-danger"> <div class="inner"><h3>' + e.technique + '</h3> <p>' + e.requirement + '</p></div><div class="icon"><i class="fas fa-tachometer-alt"></i></div><a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a><a href="#" onclick="removeRequirement(' + e.id + ', \'' + e.technique + '\', ' + e.type + ', \'' + e.requirement + '\')" class="small-box-footer">Remove <i class="fas fa-minus-circle"></i></a></div></div>');
                            } else if (e.type == 2) {
                                $("#requirements").append('<div id=' + (e.id + e.technique + e.requirement).replace(/[^\w\s]/gi, '') + ' class="col-lg-3 col-6"> <div class="small-box bg-danger"> <div class="inner"><h3>' + e.technique + '</h3> <p>' + e.requirement + '</p></div><div class="icon"><i class="fas fa-user-lock"></i></div><a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a><a href="#" onclick="removeRequirement(' + e.id + ', \'' + e.technique + '\', ' + e.type + ', \'' + e.requirement + '\')" class="small-box-footer">Remove <i class="fas fa-minus-circle"></i></a></div></div>');
                            } else if (e.type == 3) {
                                $("#requirements").append('<div id=' + (e.id + e.technique + e.requirement).replace(/[^\w\s]/gi, '') + ' class="col-lg-3 col-6"> <div class="small-box bg-danger"> <div class="inner"><h3>' + e.technique + '</h3> <p>' + e.requirement + '</p></div><div class="icon"><i class="fas fa-cloud-download-alt"></i></div><a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a><a href="#" onclick="removeRequirement(' + e.id + ', \'' + e.technique + '\', ' + e.type + ', \'' + e.requirement + '\')" class="small-box-footer">Remove <i class="fas fa-minus-circle"></i></a></div></div>');
                            }
                        } else {
                            if (e.type == 1) {
                                $("#requirements").append('<div id=' + (e.id + e.technique + e.requirement).replace(/[^\w\s]/gi, '') + ' class="col-lg-3 col-6"> <div class="small-box bg-info"> <div class="inner"><h3>' + e.technique + '</h3> <p>' + e.requirement + '</p></div><div class="icon"><i class="fas fa-tachometer-alt"></i></div><a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a><a href="#" onclick="addRequirement(' + e.id + ', \'' + e.technique + '\', ' + e.type + ', \'' + e.requirement + '\')" class="small-box-footer">Add <i class="fas fa-plus-circle"></i></a></div></div>');
                            } else if (e.type == 2) {
                                $("#requirements").append('<div id=' + (e.id + e.technique + e.requirement).replace(/[^\w\s]/gi, '') + ' class="col-lg-3 col-6"> <div class="small-box bg-success"> <div class="inner"><h3>' + e.technique + '</h3> <p>' + e.requirement + '</p></div><div class="icon"><i class="fas fa-user-lock"></i></div><a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a><a href="#" onclick="addRequirement(' + e.id + ', \'' + e.technique + '\', ' + e.type + ', \'' + e.requirement + '\')" class="small-box-footer">Add <i class="fas fa-plus-circle"></i></a></div></div>');
                            } else if (e.type == 3) {
                                $("#requirements").append('<div id=' + (e.id + e.technique + e.requirement).replace(/[^\w\s]/gi, '') + ' class="col-lg-3 col-6"> <div class="small-box bg-secondary"> <div class="inner"><h3>' + e.technique + '</h3> <p>' + e.requirement + '</p></div><div class="icon"><i class="fas fa-cloud-download-alt"></i></div><a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a><a href="#" onclick="addRequirement(' + e.id + ', \'' + e.technique + '\', ' + e.type + ', \'' + e.requirement + '\')" class="small-box-footer">Add <i class="fas fa-plus-circle"></i></a></div></div>');
                            }
                        }

                    });
                }

            } else {
                toastr.error('Error loading requirements');
            }
        }, error: function (data) { //se lanza cuando ocurre un error
            toastr.error('Error loading requirements');
            console.log("error");
            console.error(data.responseText);
        }
    });
}

function addRequirement(id, technique, type, requirement) {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "addReq", "id": id, "technique": technique, "typeNFR": type, "requirement": requirement },
        dataType: 'json',
        beforeSend: function () {
            if (type == 1) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-info");
            } else if (type == 2) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-success");
            } else if (type == 3) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-secondary");
            }

            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-secondary");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].childNodes[4].outerHTML = '<a href="#" class="small-box-footer">Adding... <i class="fas fa-minus"></i></a>';
        },
        success: function (data) {
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-secondary");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-danger");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].childNodes[4].outerHTML = '<a href="#" onclick="removeRequirement(' + id + ', \'' + technique + '\', ' + type + ', \'' + requirement + '\')" class="small-box-footer">Remove <i class="fas fa-minus-circle"></i></a>';

        }, error: function (data) { //se lanzload_data()ry");
            console.error(data.responseText);
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-secondary");
            if (type == 1) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-info");
            } else if (type == 2) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-success");
            } else if (type == 3) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-secondary");
            }

            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].childNodes[4].outerHTML = '<a href="#" onclick="addRequirement(' + id + ', \'' + technique + '\', ' + type + ', \'' + requirement + '\')" class="small-box-footer">Add <i class="fas fa-plus-circle"></i></a>';
            /*button.classList.remove("btn-info");
            button.innerHTML = "<i class='fa fa-plus'></i>Add";
            button.onclick = function() {addPiece(id, name, image, button)};*/

        }
    });
}

function removeRequirement(id, technique, type, requirement) {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "addReq", "id": id, "technique": technique, "typeNFR": type, "requirement": requirement },
        dataType: 'json',
        beforeSend: function () {
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-danger");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-secondary");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].childNodes[4].outerHTML = '<a href="#" class="small-box-footer">Adding... <i class="fas fa-minus"></i></a>';
        },
        success: function (data) {
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-secondary");
            if (type == 1) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-info");
            } else if (type == 2) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-success");
            } else if (type == 3) {
                $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-secondary");
            }

            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].childNodes[4].outerHTML = '<a href="#" onclick="addRequirement(' + id + ', \'' + technique + '\', ' + type + ', \'' + requirement + '\')" class="small-box-footer">Add <i class="fas fa-plus-circle"></i></a>';

        }, error: function (data) { //se lanzload_data()ry");
            console.error(data.responseText);
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.remove("bg-secondary");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].classList.add("bg-danger");
            $("#" + (id + technique + requirement).replace(/[^\w\s]/gi, '')).children()[0].childNodes[4].outerHTML = '<a href="#" onclick="removeRequirement(' + id + ', \'' + technique + '\', ' + type + ', \'' + requirement + '\')" class="small-box-footer">Remove <i class="fas fa-minus-circle"></i></a>';
            /*button.classList.remove("btn-info");
            button.innerHTML = "<i class='fa fa-plus'></i>Add";
            button.onclick = function() {addPiece(id, name, image, button)};*/

        }
    });
}

function load_data() {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "getCatalogs" },
        dataType: 'json',
        beforeSend: function () {
            $("#catalogs_list").html('<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>');
        },
        success: function (data) {
            console.log(data);
            console.log("DATA");
            $("#catalogs_list").html("");
            if (data["published"]["code"] == 200 || data["published"]["code"] == 200) {
                toastr.info('Catalogs loaded!');
                added = [];
                prev_added = data["data_added"];
                if (data["published"]["data"] != null) {
                    published = data["published"]["data"]["data"];
                    
                    
                    
                    published.forEach(function (e) {
                        added.push(e.tokencatalog);
                        if (!prev_added.hasOwnProperty(e.tokencatalog)) {
                            $("#catalogs_list").append('<div id="' + e.tokencatalog + '" class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h4>' + e.namecatalog + '</h4><p>Created: ' + e.created_at + '</p></div><div class="icon"> <i class="fas fa-database"></i> </div><a href="#" class="small-box-footer" onclick="addcatalog(\'' + e.tokencatalog + '\', \'' + e.namecatalog + '\', \'' + e.created_at + '\', true)">Add <i class="fas fa-plus"></i></a></div></div>');
                        } else {
                            $("#catalogs_list").append('<div id="' + e.tokencatalog + '" class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h4>' + e.namecatalog + '</h4><p>Created: ' + e.created_at + '</p></div><div class="icon"> <i class="fas fa-database"></i> </div><a href="#" class="small-box-footer" onclick="removeCatalog(\'' + e.tokencatalog + '\', \'' + e.namecatalog + '\', \'' + e.created_at + '\', true)">Remove <i class="fas fa-minus"></i></a></div></div>');
                        }
                    });
                }

                if(data["suscribed"]["data"] != null){
                    suscribed = data["suscribed"]["data"]["data"];
                    suscribed.forEach(function (e) {
                        if (!added.includes(e.tokencatalog)) {
                            if (!prev_added.hasOwnProperty(e.tokencatalog)) {
                                $("#catalogs_list_suscribed").append('<div id="' + e.tokencatalog + '" class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h4>' + e.namecatalog + '</h4><p>Created: ' + e.created_at + '</p></div><div class="icon"> <i class="fas fa-database"></i> </div><a href="#" class="small-box-footer" onclick="addcatalog(\'' + e.tokencatalog + '\', \'' + e.namecatalog + '\', \'' + e.created_at + '\', false)">Add <i class="fas fa-plus"></i></a></div></div>');
                            } else {
                                $("#catalogs_list_suscribed").append('<div id="' + e.tokencatalog + '" class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h4>' + e.namecatalog + '</h4><p>Created: ' + e.created_at + '</p></div><div class="icon"> <i class="fas fa-database"></i> </div><a href="#" class="small-box-footer" onclick="removeCatalog(\'' + e.tokencatalog + '\', \'' + e.namecatalog + '\', \'' + e.created_at + '\', false)">Remove <i class="fas fa-minus"></i></a></div></div>');
                            }

                        }

                    });
                }

            } else {
                toastr.error('Error loading catalogs');
            }
        }, error: function (data) { //se lanza cuando ocurre un error
            toastr.error('Error loading catalogs');
            console.log("error");
            console.error(data.responseText);
        }
    });
}

function saveStructure() {

}

function addcatalog(token, namecatalog, created_at, published) {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "addCat", "token": token, "namecatalog": namecatalog, "created_at": created_at },
        dataType: 'json',
        beforeSend: function () {
            if (published) {
                $("#" + token).children()[0].classList.remove("bg-info");
            } else {
                $("#" + token).children()[0].classList.remove("bg-info");
            }

            $("#" + token).children()[0].classList.add("bg-secondary");
            $("#" + token).children()[0].childNodes[2].outerHTML = '<a href="#" class="small-box-footer">Adding... <i class="fas fa-minus"></i></a>';
        },
        success: function (data) {
            $("#" + token).children()[0].classList.remove("bg-secondary");
            $("#" + token).children()[0].classList.add("bg-danger");
            $("#" + token).children()[0].childNodes[2].outerHTML = '<a href="#" class="small-box-footer" onclick="removeCatalog(\'' + token + '\', \'' + namecatalog + '\', \'' + created_at + '\', ' + published + ')">Remove <i class="fas fa-minus"></i></a>';

        }, error: function (data) { //se lanzload_data()ry");
            $("#" + token).children()[0].classList.remove("bg-secondary");
            if (published) {
                $("#" + token).children()[0].classList.add("bg-info");
            } else {
                $("#" + token).children()[0].classList.add("bg-info");
            }

            $("#" + token).children()[0].childNodes[2].outerHTML = '<a href="#" class="small-box-footer" onclick="addcatalog(\'' + token + '\', \'' + namecatalog + '\', \'' + created_at + '\', ' + published + ')">Add <i class="fas fa-plus"></i></a>';
            /*button.classList.remove("btn-info");
            button.innerHTML = "<i class='fa fa-plus'></i>Add";
            button.onclick = function() {addPiece(id, name, image, button)};*/

        }
    });
}

function removeCatalog(token, namecatalog, created_at, published) {
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "removeCat", "token": token },
        dataType: 'json',
        beforeSend: function () {
            $("#" + token).children()[0].classList.remove("bg-danger");

            $("#" + token).children()[0].classList.add("bg-secondary");
            $("#" + token).children()[0].childNodes[2].outerHTML = '<a href="#" class="small-box-footer">Removing... <i class="fas fa-minus"></i></a>';
        },
        success: function (data) {
            console.log(data);
            $("#" + token).children()[0].classList.remove("bg-secondary");

            if (published) {
                $("#" + token).children()[0].classList.add("bg-info");
            } else {
                $("#" + token).children()[0].classList.add("bg-info");
            }

            $("#" + token).children()[0].childNodes[2].outerHTML = '<a href="#" class="small-box-footer" onclick="addcatalog(\'' + token + '\', \'' + namecatalog + '\', \'' + created_at + '\', ' + published + ')">Add <i class="fas fa-plus"></i></a>';

        }, error: function (data) { //se lanzload_data()ry");
            console.error(data.responseText);
            $("#" + token).children()[0].classList.remove("bg-secondary");
            $("#" + token).children()[0].classList.add("bg-danger");
            $("#" + token).children()[0].childNodes[2].outerHTML = '<a href="#" class="small-box-footer" onclick="removeCatalog(\'' + token + '\', \'' + namecatalog + '\', \'' + created_at + '\', ' + published + ')">Remove <i class="fas fa-minus"></i></a>';
            /*button.classList.remove("btn-info");
            button.innerHTML = "<i class='fa fa-plus'></i>Add";
            button.onclick = function() {addPiece(id, name, image, button)};*/

        }
    });
}


function publishCatalog(){
    user = $("#userSl").val();
    catalog = $("#txtCatalog").val();
    console.log(user);
    console.log(catalog);
    $.ajax({
        type: "POST",
        url: "../../includes/controllers/controller.php",
        data: { "type": "publishCatalog", "user": user, "catalog":catalog },
        dataType: 'json',
        beforeSend: function () {
            $("#divOverlayPublish").show();
        },
        success: function (data) {
            console.log(data);
            toastr.success('Catalog published');
            $("#divOverlayPublish").hide();
            $("#publishCatalog").modal('hide');
    
        }, error: function (data) { //se lanzload_data()ry");
            console.error(data.responseText);
            toastr.error('Error publishing catalog');
            $("#divOverlayPublish").hide();

        }
    });
}

function showDirsInShared(){
    $.ajax({
        type: "POST",
        url: "../includes/controllers/controller.php",
        data: { "type": "getDirsInShared" },
        dataType: 'json',
        beforeSend: function () {
            $("#dirsonservers").html('<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>');
        },
        success: function (data) {
            console.log("HOLAS");
            console.log(data);
            //console.log(data[Object.keys(data)[0]]);
            $("#dirsonservers").html("");

            var jsonDataCatalogs = {};
            jsonDataCatalogs['data'] = data;
            jsonDataCatalogs['themes'] = {
                dots: false
            };


            /*data[Object.keys(data)[0]].forEach(f => {
                folder = {
                    'text': f,
                    'id': f,
                    'type': 'folder',
                    'children': data[f]
                };
                jsonDataCatalogs['data'].push(folder);

                //$("#localsystem").append('<div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h4>' + f+ '</h4></div><div class="icon"> <i class="fas fa-database"></i> </div><a href="#" class="small-box-footer">Add <i class="fas fa-plus"></i></a></div></div>');
            });

            console.log(jsonDataCatalogs);*/
            $('#dirsonservers').jstree({
                'core': jsonDataCatalogs,
                "types": {
                    "folder": {
                        "icon": "ti-folder text-warning",
                    },
                    "file": {
                        "icon": "ti-file",
                    }
                },
                plugins: ["types"]
            });
            //data[]
            //$("#localsystem").append('<div  class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h4>' +  + '</h4><p>Created: ' + e.created_at + '</p></div><div class="icon"> <i class="fas fa-database"></i> </div><a href="#" class="small-box-footer" onclick="addcatalog(\'' + e.tokencatalog + '\', \'' + e.namecatalog + '\', \'' + e.created_at + '\', true)">Add <i class="fas fa-plus"></i></a></div></div>');
            
        }, error: function (data) { //se lanza cuando ocurre un error
            toastr.error('Error loading directories in shared volume');
            console.log("Error loading directories in shared volume");
            console.error(data.responseText);
        }
    });
}