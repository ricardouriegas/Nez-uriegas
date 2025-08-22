$('#loginform').submit(function(e){
    e.preventDefault();
   
    var user = $('#txtEmail').val();
    var passwd = $('#txtPassword').val();
    
    if (user == "" || passwd == "") {
        $("#message").html("Please, fill user and password fields");
    }else{

        $.ajax({
            url: '../models/auth/login.php',  //Server script to process data
            type: 'POST',
            data: {"user":user,"password":passwd},
            dataType: 'json',
            beforeSend: function(){
                $("#message").html("Processsing request...");
            },
            success  : function(data){ //muestra la respuesta
                if(data["code"] == 400){
                    $("#message").html(data["data"]["message"]);
                }else if(data["code"] == 200){
                    location.href="../index.php";     
                }
            },
            error: function(data){ //se lanza cuando ocurre un error
                alert(data.responseText);
            }
        });
    }
});

$('#frmSignUp').submit(function(e){
    e.preventDefault();
    var pass1 = $('#txtPassword').val();
    var pass2 = $('#txtPassword2').val();
    if (pass1 == pass2) {
        var name = $('#txtName').val();
        var username = $('#txtUsername').val();
        var email = $('#txtEmail').val();
        var organization = $('#idOrganization').val();
        $.ajax({
            url: '../models/auth/register.php',  //Server script to process data
            type: 'POST',
            data: {"name":name,"username":username,"email":email,"tokenorg":organization,"password":pass1},
            dataType: 'json',
            beforeSend: function(){
                $("#message").html("Processsing request...");
            },
            success  : function(data){ //muestra la respuesta
                console.log(data);
                if(data.code == 400 ){
                    $("#message").html("<code>"+data.data.message+"</code>");
                }else if(data.code == 201){
                    $("#message").html(data.data.message + " Please, check your email");
                    //$("#frmSignUp")[0].reset();
                }
            },
            error: function(data){ //se lanza cuando ocurre un error
                alert(data.responseText);
            }
        });
    }else{
        $("#message").html("<code>Passwords don't match</code>");
    }
});