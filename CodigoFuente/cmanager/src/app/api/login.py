from flask import Blueprint, request, session, jsonify
from ..constants import USERID, USERNAME, USERTOKEN, VC_PAIR, GW_PAIR
from .. import db
from ..models import User, Container, UsersContainers
from .requests_handler import do_get, do_post
from Crypto.Hash import SHA3_256
from datetime import datetime

login_bp = Blueprint("login", __name__, static_folder="url_for('static')", 
    template_folder="url_for('templates')")

@login_bp.route('/logout', methods=['GET'])
def logout():
    session.clear()
    return jsonify({'success': True})

@login_bp.route('/login', methods=['POST'])
def login():
    rj = request.json
    if len(rj) == 0:
        return jsonify({'msg': 'No args.'}), 400
    allowed_args = {
        'email': (str),
        'password': (str),
    }
    args = {}
    for a, t in allowed_args.items():
        if rj.get(a) and isinstance(rj[a], t):
            args[a] = rj[a]
    if len(args) != len(allowed_args):
        return jsonify({'msg': 'Invalid args.'}), 400
    email = args.get('email')
    password = args.get('password')
    user = authenticate_by_email(email, password)
    if not user:
        return jsonify({'error': 'User does not exist'}), 404
    session[USERID] = user.uid
    session[USERNAME] = user.username
    session[USERTOKEN] = user.token
    return jsonify({'login': True})

@login_bp.route('/signup', methods=['POST'])
def signup():
    s = 400
    rj = request.json
    if len(rj) == 0:
        return jsonify({'msg': 'No args.'}), s
    allowed_args = {
        'username': (str),
        'email': (str),
        'password': (str),
        'tokenorg': (str),
        'role': (str),
    }
    args = {}
    for a, t in allowed_args.items():
        if rj.get(a) and isinstance(rj[a], t):
            args[a] = rj[a]
    if len(args) != len(allowed_args):
        return jsonify({'msg': 'Invalid args.'}), s
    authuser = create_user_in_auth(args)
    if not authuser:
        # TODO: return messaje if already exists
        return jsonify({'msg': 'Auth not available.'}), s
    payload = {
        'user': args.get('username'),
        'password': args.get('password'),
    }
    udata = login_user_in_auth(payload)
    if not udata or not udata.get('data'):
        return jsonify({'msg': 'Auth not available.'}), s
    ud = udata.get('data')
    status = create_user_in_valuechain(ud.get('access_token'))
    user = verify_existance(args.get('email'))
    if user:
        return jsonify({'Error': 'User already exists, login instead'}), s
    user = User(args.get('username'), 
                args.get('email'), 
                args.get('password'), 
                ud.get('access_token'))
    db.session.add(user)
    db.session.commit()
    db.session.refresh(user)
    session[USERID] = user.uid
    session[USERNAME] = args.get('username')
    session[USERTOKEN] = ud.get('access_token')
    return jsonify({'signup': True})

def authenticate_by_user(username, password):
    user = User.query.filter_by(username=username, password=password).first()
    if user:
        return user
    return None

def authenticate_by_email(email, password):
    user = User.query.filter_by(email=email, password=password).first()
    if user:
        return user
    return None

def verify_existance(email):
    user = User.query.filter_by(email=email).first()
    if user:
        return user
    return None

def create_user_in_auth(payload):
    url = 'http://'+GW_PAIR+'/auth/v1/users/create'
    r = do_post(url, payload=payload)
    s = (r.status_code if r != None and r.status_code else 500)
    if s == 201:
        return r.json()
    return None

def login_user_in_auth(payload):
    url = 'http://'+GW_PAIR+'/auth/v1/users/login'
    r = do_post(url, payload=payload)
    s = (r.status_code if r != None and r.status_code else 500)
    if s == 200:
        return r.json()
    return None

def create_user_in_valuechain(token):
    url = 'http://'+VC_PAIR+'/api/v1/users?access_token='+token
    r = do_post(url, payload=None)
    return (r.status_code if r != None and r.status_code else 500)

@login_bp.route('/organizations/json', methods=['GET'])
def orgs():
    url = 'http://' + GW_PAIR + '/auth/v1/view/hierarchy/all'
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"organizations": data}), status

@login_bp.route('/roles/json', methods=['GET'])
def roles():
    url = 'http://' + GW_PAIR + '/auth/v1/roles'
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"roles": data}), status

# def get_SHA3_256(data=None):
#     """Get the hex sha3 representation of a string.
#     Args:
#         data (str): string to get its hash
#     Returns:
#         str: hex hash representation
#     """
#     if not data:
#         data = parse_RFC3339_datetime_to_str(datetime.today())
#     bytes_data = bytes(data, 'utf-8')
#     h_obj = SHA3_256.new()
#     h_obj.update(bytes_data) #b'Some data'
#     return h_obj.hexdigest()

# def parse_RFC3339_datetime_to_str(date_time):
#     return datetime.strftime(date_time, '%Y-%m-%dT%H:%M:%S.%fZ')