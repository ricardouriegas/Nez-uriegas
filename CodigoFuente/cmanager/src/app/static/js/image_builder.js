const imageFieldMessages = {
  // 'context_path': 'Path format only, e.g /folder_name/folder',
  'tag': 'Invalid name: only letters, and :',
  'encoding': 'Invalid name: only letters, numbers, underscore and hyphen',
  'timeout': 'Invalid name: only numbers.',
}

var context = '';

// function invalidParamsExists() {
//   for(key in imageFieldMessages) {
//       if(!document.getElementById(key).checkValidity()) {
//           document.getElementById(key).setCustomValidity(imageFieldMessages[key]);
//           document.getElementById(key).reportValidity();
//           return true;
//       }
//   }
//   return false;
// }

async function fetchContext(formData) {
  await fetch('/images/context', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => context = data)
  .catch(err => console.error(err))
}

function uploadFiles() {
  // let dfile = document.getElementById('dockerfile').files[0];
  let files = document.getElementById('context_path').files;
  var formData = new FormData();
  for (let i = 0; i < files.length; i++) {
    formData.append('files[]', files[i]);
  }
  fetchContext(formData);
}

function createImage() {

  // if there are at least one invalid field(marked in red)
  // a message will show up and cannot procceed to creation.
  if(invalidParamsExists(imageFieldMessages)) {
    showAlert('invalid params, check red marked fields!', 'danger');
    return;
  }

  const tag = $('#tag')[0].value;
  const encoding = $('#encoding')[0].value;
  const timeout = valueOrNull($('#timeout')[0].value, 'number');
  const quiet = $('#quiet')[0].checked;
  const nocache = $('#nocache')[0].checked;
  const rm = $('#rm')[0].checked;
  const pull = $('#pull')[0].checked;
  const forcerm = $('#forcerm')[0].checked;
  const squash = $('#squash')[0].checked;
  const use_config_proxy = $('#use_config_proxy')[0].checked;
  let params = {}
  
  if(tag) params['tag'] = tag;
  if(encoding) params['encoding'] = encoding;
  if(timeout) params['timeout'] = timeout;
  if(quiet) params['quiet'] = quiet;
  if(nocache) params['nocache'] = nocache;
  if(rm) params['rm'] = rm;
  if(pull) params['pull'] = pull;
  if(forcerm) params['forcerm'] = forcerm;
  if(squash) params['squash'] = squash;
  if(use_config_proxy) params['use_config_proxy'] = use_config_proxy;

  // console.log('context: ', context);
  if(context != '') params['context'] = context['context'];
  // console.log('params: ', params);


  let reqObj = {
    'type': 'POST',
    'url': '/images/build',
    'isAsync': true,
    'params': JSON.stringify(params),
    'requestHeaders': { 'Content-Type': 'application/json' }
  };

  sendRequest(reqObj, 
    (e) => console.log('loading request...'),
    (response) => {
        console.log('response: ', response);
        let res = JSON.parse(response.srcElement.response);
        console.log(res);
        if(res.image.length == 12) {
          showAlert('Container created succesfully', 'success');
          location.href = '/images';
        }else {
          showAlert('An error ocurred creating the container, check logs.', 'danger');
          console.log(res.log);
        }
    }, 
    (error) => {
        showAlert('Image build failed', 'danger');
        console.log('error for image build: ', error);
    }
  );

}