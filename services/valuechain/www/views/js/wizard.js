// BS-Stepper Init
document.addEventListener('DOMContentLoaded', function () {
    window.stepper = new Stepper(document.querySelector('.bs-stepper'), {
        linear: false
    });

    startPanel();
    load_pieces();
    load_data();
    load_nfrs();
    showDirsInShared();

    $("#frmSaveStructure").submit(function(e){
        e.preventDefault();
        let f_out = flowy.output();
        let emp = {};
        let name = "";
        let id = -1;
        console.log(f_out);
        if(f_out == null){
            toastr.error('Please add at least one piece.');
        }else{
            for (let i = 0, n = f_out.blocks.length; i < n; i++) {
                f_out.blocks[i]['attr'].forEach(element => {
                    if(element.hasOwnProperty("name")){
                        name = element.name;
                    }else if(element.hasOwnProperty("tmp-id")){
                        id = element["tmp-id"];
                    }
                });
                emp[i] = {
                    id: f_out.blocks[i]['id'],
                    parent: f_out.blocks[i]['parent'],
                    name: name,
                    block_id:id
                };
            }
            console.log(f_out);
            let data = {
                "nameWorkflow": $("#txtServiceName").val(),
                "statusWorkflow": "0",
                "stages": JSON.stringify(emp),
                "rawgraph": JSON.stringify(f_out),
                "type": "createProcessingStructure",
                "deploy_and_execute":  $("#deployAndExecute").val()
            };
            console.log(data);
            $.ajax({
                type: "POST",
                url: "../includes/controllers/controller.php",
                data: data,
                dataType: 'json',
                beforeSend: function(){
                    $("#confirmContent").append('<div class="overlay"> <i class="fas fa-2x fa-sync fa-spin"></i></div>');
                },
                success: function(data){
                    if(data.code == 201){
                        $("#confirmContent").children()[2].remove();
                        toastr.success(data.data.msg);
                        $("#modal-default").modal('hide');
                        window.location.href = 'puzzle/puzzle.php?id=' + data.data["workflow_id"] + '&deploy='+ $("#deployAndExecute").val();

                    }
                    
                },error: function(data){ //se lanza cuando ocurre un error
                    console.error(data.responseText);
                    toastr.error('Error creating service');
                    console.log($("#confirmContent").children()[2]);
                    $("#confirmContent").children()[2].remove();
                    
                }
            });
        }
        
    });

});

function startPanel() {
    let spacing_x = 20;
    let spacing_y = 20;
    flowy(document.getElementById("canvas"), onGrab, onRelease, onSnap, onRearrange,
        spacing_x, spacing_y);  
}

function clearStructure(){
    flowy.deleteBlocks();
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

function prepareDraggedBlock(drag, parent) {
    let id = drag.getAttribute("id");
    let name = drag.getAttribute("name");
    let tmp_id = "c-" + id;

    var grab = drag.querySelector(".grabme");
    grab.parentNode.removeChild(grab);
    var blockin = drag.querySelector(".blockin");
    blockin.parentNode.removeChild(blockin);
    //drag.innerHTML += "<div class='blockyleft'><i class='fas fa-puzzle-piece'></i><p class='blockyname'>" + name + "</p></div><div class='blockyright'><a href='#'> <i class='fa fa-window-close'></i></a></div>";
    drag.innerHTML += "<div class='blockyleft'><i class='fas fa-puzzle-piece'></i><p class='blockyname'>" + name + "</p></div><div class='blockyright'></div>";
    drag.setAttribute('id', tmp_id);
    drag.setAttribute('tmp-id', id);
    drag.classList.add('position-absolute');
}