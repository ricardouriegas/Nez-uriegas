
const loginFormTemplate = `<div>
<h1 class="h3 mb-3 font-weight-normal d-flex justify-content-center">Log in</h1>
<input name="email" id="email" class="form-control m-1" type="text" placeholder="Username" required oninput="setCustomValidity('')">
<input name="password" id="password" class="form-control m-1" type="password" placeholder="Password" pattern="[^\\s\\\\]+$" oninput="setCustomValidity('')">
<div class="pt-1 mx-3"><button onclick="login()" class="btn btn-lg btn-primary btn-block m-1">Log in</button>
<div class="checkbox mb-3 d-flex justify-content-center">
<small><a onclick="switchForm(event, 'signup')" href="#">Don't have an account?, Sign up here!</a></small>
</div></div></div>`;

const registerFormTemplate = `<div>
<h1 class="h3 mb-3 font-weight-normal d-flex justify-content-center">Sign up</h1>
<input name="username" id="username" class="form-control m-1" type="text" placeholder="Username" pattern="\\w+$" required oninput="setCustomValidity('')">
<input name="email" id="email" class="form-control m-1" type="email" placeholder="Email" required oninput="setCustomValidity('')">
<input name="password" id="password" class="form-control m-1" type="password" placeholder="Password" pattern="[^\\s\\\\]+$" required oninput="setCustomValidity('')">
<input name="confirmpassword" id="confirmPassword" class="form-control m-1" type="password" placeholder="Confirm Password" pattern="[^\\s\\\\]+$" required oninput="setCustomValidity('')">
<div class="row">
    <div class="col">
        <select class="form-control m-1" name="tokenorg" id="tokenorg" required>
            <option value="" disabled selected>Organization</option>
        </select>
    </div>
    <div class="col">
        <select class="form-control m-1" name="role" id="role" required>
            <option value="" disabled selected>Role</option>
        </select>
    </div>
</div>
<div class="pt-1 mx-3"><button class="btn btn-lg btn-primary btn-block m-1" onclick="signup()">Register</button>
<div class="checkbox mb-3 d-flex justify-content-center">
<small><a onclick="switchForm(event, 'signin')" href="#">Already have an account?, Log in here!</a></small>
</div></div></div>`;

var loginFieldsMessages = {
    'email': 'Only numbers, letters, _, and - are allowed.',
    'password': 'No Whitespaces',
}

var registerFieldsMessages = {
    'username': 'Only numbers, letters, _, and - are allowed.',
    'email': 'Only numbers, letters, _, and - are allowed.',
    'password': 'No Whitespaces',
    'confirmPassword': 'No Whitespaces'
}

function switchForm(e, to) {
    e.preventDefault();
    document.getElementById('form-type').innerHTML = to == "signup" ? registerFormTemplate : loginFormTemplate;
    if (to == 'signup') {
        fetchOrgs();
        fetchRoles();
    }
    setValidities();
} 

function login() {
    if(invalidParamsExists(loginFieldsMessages)) {
        showAlert('invalid params, check red marked fields!', 'danger');
        return;
    }
    email = $('#email')[0].value;
    password = $('#password')[0].value;
    if(email && password) {
        params = {
            'email': email,
            'password': password
        };
        let reqObj = {
            'type': 'POST',
            'url': '/login',
            'isAsync': false,
            'params': JSON.stringify(params),
            'requestHeaders': { 'Content-Type': 'application/json' }
        };
        sendRequest(reqObj, 
            (e) => console.log('loading request...'),
            (response) => {
                let res = JSON.parse(response.srcElement.response);
                if('error' in res) { 
                    showAlert(`Failed to log in, error: ${res['error']}`, 'danger');
                }
                else {
                    showAlert('login successfull!', 'success');
                    location.href = '/home';
                }
            }, 
            (error) => {
                showAlert('Container failed to create', 'danger');
                console.log('error for container creation: ', error);
            });
    } else {
        showAlert(`Don't leave empty fields`, 'danger');
    }
}

function signup() {
    if(invalidParamsExists(registerFieldsMessages)) {
        showAlert('invalid params, check red marked fields!', 'danger');
        return;
    }
    username = $('#username')[0].value;
    email = $('#email')[0].value;
    password = $('#password')[0].value;
    tokenorg = $('#tokenorg')[0].value;
    role = $('#role')[0].value;
    confirmPassword = $('#confirmPassword')[0].value;
    if(username && email && password && confirmPassword
        && tokenorg && role) {
        if(password != confirmPassword) {
            showAlert('Password fields does not match', 'danger');
            return;    
        }
        params = {
            'username': username,
            'email': email,
            'password': password,
            'tokenorg': tokenorg,
            'role': role,
        };
        let reqObj = {
            'type': 'POST',
            'url': '/signup',
            'isAsync': false,
            'params': JSON.stringify(params),
            'requestHeaders': { 'Content-Type': 'application/json' }
        };
        sendRequest(reqObj, 
            (e) => console.log('loading request...'),
            (response) => {
                let res = JSON.parse(response.srcElement.response);
                if('error' in res) { 
                    showAlert(`Failed to sign up, error: ${res['error']}`, 'danger');
                }
                else {
                    showAlert('successfully registered, loggin in!', 'success');
                    location.href = '/home';
                }
            }, 
            (error) => {
                showAlert('Failed to sign up.', 'danger');
            });
    } else {
        showAlert(`Don't leave empty fields`, 'danger');
    }
}

function setValidities() { 
    $('input:not([readonly])').on('change', (e) => {
        let id = e.target.id;
        if(!document.getElementById(id).checkValidity()) {
            document.getElementById(id).setCustomValidity(loginFieldsMessages[id]);
            document.getElementById(id).reportValidity();
        }
    });
}

$('form').ready((e) => { 
    // check if the invalidity popup should show after the
    // content of an input changed
    setValidities();
});

function fetchOrgs() {
    fetch('/organizations/json', {
        method: 'GET',
        body: null
    })
    .then(res => res.json())
    .then(data => drawOrgs(data.organizations.data))
    .catch(err => console.error(err))
}

function drawOrgs(data) {
    let html = '';
    data.forEach(d => {
        html += '<option value="'+d.tokenhierarchy+'" title="'+d.fullname+'">'+d.acronym+'</option>'
    });
    $('#tokenorg').html(html);
}

function fetchRoles() {
    fetch('/roles/json', {
        method: 'GET',
        body: null
    })
    .then(res => res.json())
    .then(data => drawRoles(data.roles.data))
    .catch(err => console.error(err))
}

function drawRoles(data) {
    let html = '';
    data.forEach(d => {
        html += '<option value="'+d.tokenrole+'">'+d.role+'</option>'
    });
    $('#role').html(html);
}