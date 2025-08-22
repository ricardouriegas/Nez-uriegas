from flask import Blueprint, session, jsonify, request
from flask_api import status
import os
import requests
import docker
import json
from datetime import datetime
from Crypto.Hash import SHA3_256
from werkzeug.utils import secure_filename
# from werkzeug.datastructures import ImmutableMultiDict
from .. import dockercli, client, db
from ..constants import USERID, REGISTRY_PAIR, UPLOAD_FOLDER
from ..models import Context, UsersImages

images_bp = Blueprint("images", __name__, static_folder="url_for('static')", template_folder="url_for('templates')")

def get_hash():
    """Generares SHA3_256 hash to use as ID for store context files.

    Returns:
        [str]: [Hecimal digest]
    """
    now = datetime.now()
    # parse to format RFC 3339
    dt_string = now.strftime("%Y-%m-%dT%H:%M:%S.%fZ")
    dt_bytes = bytes(dt_string, 'utf-8')
    h_obj = SHA3_256.new()
    h_obj.update(dt_bytes) #b'Some data'
    return h_obj.hexdigest()

@images_bp.route('/json', methods=['GET'])
def getAllImages():
    # print(request.args)
    params = {}
    params['all'] = True if request.args.get('all') else False
    images = dockercli.images(**params)
    # print(f'images: {len(images)}')
    return { 'images': images }

def getTagsOf(repname, source):
    url = ''
    tags = []
    if(source == 'dockerhub'):
        # FROM DOCKERHUB, TAGS CAN BE OBTAINED WITH THE FOLLOW INSTRUCTION:
        url = 'https://registry.hub.docker.com/v1/repositories/{}/tags'.format(repname)
        data = requests.get(url).json()
        for t in data:
            tags.append(t['name'])
    else: 
        # FROM LOCAL REGISTRY, TAGS ARE OBTAINED WITH:
        url = 'http://'+REGISTRY_PAIR+'/v2/{}/tags/list'.format(repname)
        data = requests.get(url).json()
        tags = data['tags']

    # Return only the array of tags
    return tags

@images_bp.route('/registry', methods=["GET"])
def getImagesFromRegistry():
    source = request.args.get('source')
    text = request.args.get('text')
    repositories = []
    if source == 'dockerhub':
        repositories = client.images.search(text)
    else:
        try:
            r = requests.get('http://'+REGISTRY_PAIR+'/v2/_catalog')
            rj = r.json()
            print(rj)
            repositories = rj.get('repositories', [])
        except:
            repositories = []
    print(repositories)
    repsWithTags = dict()
    for rep in repositories:
        if source == 'dockerhub':
            repsWithTags[rep['name']] = { 
                    'automated': rep['is_automated'],
                    'official': rep['is_official'],
                    'name': rep['name'],
                    'description': rep['description'],
                    'stars': rep['star_count'],
                    'tags': getTagsOf(rep['name'], str(source))
            }
        else:
            repsWithTags[rep] = getTagsOf(rep, str(source))

    return { "repositories": repsWithTags }

@images_bp.route('/delete', methods=["GET"])
def deleteImage():
    print(request.args)
    image = request.args.get('imagerepo')
    force = request.args.get('force', False)
    noprune = request.args.get('noprune', False)
    msg = 'deleted'
    print(image)
    print(force)
    print(noprune)
    try: 
        client.images.remove(image=image, force=force, noprune=noprune) 
    except docker.errors.APIError as err:
        print(str(err))
        return jsonify({'delete': 'error' }), status.HTTP_500_INTERNAL_SERVER_ERROR
    # remove from database
    return jsonify({'delete': True}), status.HTTP_200_OK

@images_bp.route('/pull', methods=["GET"])
def pullImageFrom():
    repname = request.args.get('repname')
    source = request.args.get('source')
    image = client.images.pull(repname)

    imageid = ''

    if(source == 'dockerhub'):
        imageid = image.id
    else:
        imageid = image.id
 
    print('image id: ', imageid)
    return { 'id': imageid }

@images_bp.route('/rep/<repname>/push/<id>', methods=["GET"])
def pushToRepo(repname, id):
    pass

@images_bp.route('/inspect', methods=["GET"])
def getImageInfo():
    id = request.args['id']
    image = {}
    try:
        image = dockercli.inspect_image(id)
    except docker.errors.APIError as err:
        return { "error": str(err) }
    return {"image": image} 

@images_bp.route('/context', methods=['POST'])
def upload_context():
    # print(request.files)
    if 'files[]' in request.files:
        context = get_hash()
        upload_path = os.path.join(UPLOAD_FOLDER, context)
        make_subfolders(upload_path)
        folder = ''

        try:
            # handle ImmutableMultiDict, content in the form: files[], [object File]
            files = request.files.getlist('files[]')
            for f in files:
                folder = f.filename.split('/')[0]
                head, tail = os.path.split(f.filename)
                if head:
                    make_subfolders(os.path.join(upload_path, head))
                filename = secure_filename(tail) if tail else ''
                path = os.path.join(upload_path, head, filename)
                f.save(path)
        except:
            return jsonify({'context': ''}), status.HTTP_400_BAD_REQUEST

        c = Context(context, folder)
        db.session.add(c)
        db.session.commit()

        return jsonify({'context': context}), status.HTTP_201_CREATED
    return jsonify({'context': ''}), status.HTTP_400_BAD_REQUEST

@images_bp.route('/build', methods=['POST'])
def build_image():
    msg = ''
    log = []
    data_j = request.get_json()
    if not data_j.get('tag') or not data_j.get('context'):
        return jsonify({'image': 'data missing'}), status.HTTP_400_BAD_REQUEST
    image_params = {
        'path': (str),
        # fileobj,
        'tag': (str),
        'quiet': (bool),
        'nocache': (bool),
        'rm': (bool),
        'timeout': (int),
        # 'custom_context': (bool),Optional if using fileobj
        'encoding': (str),
        'pull': (bool),
        'forcerm': (bool),
        'dockerfile': (str),
        'buildargs': (dict),
        'container_limits': (dict),
        'shmsize': (int), # If omitted the system uses 64MB
        'labels': (dict),
        'cache_from': (list),
        'target': (str),
        'network_mode': (str),
        'squash': (bool),
        'extra_hosts': (dict),
        'platform': (str),
        'isolation': (str), # Default: None.
        'use_config_proxy': (bool),
    }
    params = {}
    ctx = data_j['context']
    ctx_o = Context.query.filter_by(id=ctx).first()
    p = os.path.join(UPLOAD_FOLDER, ctx, ctx_o.folder)
    params['path'] = p
    for p, t in image_params.items():
        if data_j.get(p) and isinstance(data_j[p], t):
            params[p] = data_j[p]
    # print('params: ', params)
    try:
        log_stream = dockercli.build(**params)
        log_l = list(log_stream)
    except docker.errors.APIError as e:
        print(str(e))
        return jsonify({'image': 'error'}), status.HTTP_500_INTERNAL_SERVER_ERROR
    print(log_l)
    for ln in log_l:
        t = clean_raw_text(ln)
        if t:
            log.append(t)
    img_id = ''
    if 'Successfully tagged' in log[-1]:
        img_id = data_j['tag']
        if 'Successfully' in log[-2]:
            img_id = log[-2].split(' ')[2]
            # save image to db
            userImage = UsersImages(session[USERID], img_id)
            db.session.add(userImage)
            db.session.commit()
    
    return jsonify({'image': img_id, 'log': log}), status.HTTP_201_CREATED

def clean_raw_text(t):
    """Convert element of <generator object APIClient._stream_helper> to str.
    Args:
        t (bytes): text with structure like: b'{"stream":"..."}\r\n'.
    Returns:
        str or None: text cleaned
    """
    if isinstance(t ,bytes):
        t = t.decode('utf-8')
    t = t.replace('\r\n', '')
    t = t.replace('\\n', '')
    t = t.replace('\\u003e', '>')
    t = t.replace('\u001b[91m', '')
    t = t.replace('\u001b[0m', '')
    t = json.loads(t)
    t = t.get('stream', None)
    t = t if t and t != '' else None
    return t

def make_subfolders(path, access_rights=0o665):
    """Make directories recursively with access rights.
    Args:
        path (str): path to folder
        access_rights (int, optional): access rights to apply recursively. Defaults to 0o665.
    Returns:
        bool: true if directory created false if not.
    """
    cr = False
    try:
        if not os.path.exists(path):
            os.makedirs(path, access_rights)
            cr = True
    except OSError:
        print ('Make directory failed: ', path)
    return cr
