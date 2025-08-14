document.addEventListener("DOMContentLoaded", function(){
    var rightcard = false;
    var tempblock;
    var tempblock2;


    $.ajax({
        type: "POST",
        url: "controllers/controllerWorkflowCreator.php",
        data: {readStages:'readStages'},
        success: function(response){

            document.getElementById("blocklist").innerHTML = response;

        }
    });





    flowy(document.getElementById("canvas"), drag, release, snapping);
    function addEventListenerMulti(type, listener, capture, selector) {
        var nodes = document.querySelectorAll(selector);
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].addEventListener(type, listener, capture);
        }
    }
    function snapping(drag, first) {
        var grab = drag.querySelector(".grabme");
        grab.parentNode.removeChild(grab);
        var blockin = drag.querySelector(".blockin");
        blockin.parentNode.removeChild(blockin);
        
        var valueStage = drag.querySelector(".blockelemtype").value;

        drag.innerHTML += $('#'+valueStage+'').clone().html();


        return true;
    }
    function drag(block) {
        block.classList.add("blockdisabled");
        tempblock2 = block;
    }
    function release() {
        if (tempblock2) {
            tempblock2.classList.remove("blockdisabled");
        }
    }
    var disabledClick = function(){
        document.querySelector(".navactive").classList.add("navdisabled");
        document.querySelector(".navactive").classList.remove("navactive");
        this.classList.add("navactive");
        this.classList.remove("navdisabled");
        if (this.getAttribute("id") == "stages") {

            $.ajax({
                type: "POST",
                url: "controllers/controllerWorkflowCreator.php",
                data: {readStages:'readStages'},
                success: function(response){

                    document.getElementById("blocklist").innerHTML = response;

                }
            });
        } else if (this.getAttribute("id") == "newStage") {

        } else if (this.getAttribute("id") == "loggers") {
            document.getElementById("blocklist").innerHTML = '<div class="blockelem create-flowy noselect"><input type="hidden" name="blockelemtype" class="blockelemtype" value="9"><div class="grabme"><img src="assets/grabme.svg"></div><div class="blockin">                  <div class="blockico"><span></span><img src="assets/log.svg"></div><div class="blocktext">                        <p class="blocktitle">Add new log entry</p><p class="blockdesc">Adds a new log entry to this project</p>        </div></div></div><div class="blockelem create-flowy noselect"><input type="hidden" name="blockelemtype" class="blockelemtype" value="10"><div class="grabme"><img src="assets/grabme.svg"></div><div class="blockin">                  <div class="blockico"><span></span><img src="assets/log.svg"></div><div class="blocktext">                        <p class="blocktitle">Update logs</p><p class="blockdesc">Edits and deletes log entries in this project</p>        </div></div></div><div class="blockelem create-flowy noselect"><input type="hidden" name="blockelemtype" class="blockelemtype" value="11"><div class="grabme"><img src="assets/grabme.svg"></div><div class="blockin">                  <div class="blockico"><span></span><img src="assets/error.svg"></div><div class="blocktext">                        <p class="blocktitle">Prompt an error</p><p class="blockdesc">Triggers a specified error</p>        </div></div></div>';
        }
    }
addEventListenerMulti("click", disabledClick, false, ".side");

/*close properties*/
document.getElementById("close").addEventListener("click", function(){
 if (rightcard) {
     rightcard = false;
     document.getElementById("properties").classList.remove("expanded");
     setTimeout(function(){
        document.getElementById("propwrap").classList.remove("itson"); 
    }, 300);
     tempblock.classList.remove("selectedblock");
 } 
});

document.getElementById("removeblock").addEventListener("click", function(){
   flowy.deleteBlocks();
});
var aclick = false;
var noinfo = false;
var beginTouch = function (event) {
    aclick = true;
    noinfo = false;
    if (event.target.closest(".create-flowy")) {
        noinfo = true;
    }
}
var checkTouch = function (event) {
    aclick = false;
}
var doneTouch = function (event) {
    if (event.type === "mouseup" && aclick && !noinfo) {
      if (!rightcard && event.target.closest(".block") && !event.target.closest(".block").classList.contains("dragging")) {
        tempblock = event.target.closest(".block");
        rightcard = true;
        tempblock.classList.add("selectedblock");

readDataStage(tempblock.getAttribute("name"));

$("#nameStageAux").val(tempblock.getAttribute("name"));



        document.getElementById("properties").classList.add("expanded");
        document.getElementById("propwrap").classList.add("itson");
    } 
}
}

addEventListener("mousedown", beginTouch, false);
addEventListener("mousemove", checkTouch, false);
addEventListener("mouseup", doneTouch, false);
addEventListenerMulti("touchstart", beginTouch, false, ".block");
});




function drag(block) {
    block.classList.add("blockdisabled");
    tempblock2 = block;
}




function readDataStage(stage) {

    $.ajax({
            type: "POST",
            url: "controllers/controllerWorkflowCreator.php",
            data: {readBBStage:stage},
            success: function(response){

                document.getElementById("processing").innerHTML = response;

            }
        });
}