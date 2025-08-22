from flask import Blueprint, session, request, jsonify
from .requests_handler import do_get, do_post
from ..constants import USERTOKEN, GW_PAIR, VC_PAIR

test_bp = Blueprint("test", __name__)

@test_bp.route('/users/json', methods=['GET'])
def get_users():
    url = 'http://' + GW_PAIR + '/auth/v1/view/users/all?access_token=' + session.get(USERTOKEN)
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"users": data}), status

@test_bp.route("/pubsub/publish", methods=["POST"])
def add_service():
    res = {'msg': 'Error'}
    payload = {}
    payload['idworkflow'] = request.form.get('idworkflow')
    payload['iduser'] = request.form.get('iduser')
    url = 'http://' + VC_PAIR + '/api/v1/workflows/publish/to_user?access_token=' + session.get(USERTOKEN)
    r = do_post(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 201:
        res = {"msg": r.json()}
    return jsonify(res), status

@test_bp.route('/services/subs/json', methods=['GET'])
def get_services_subs():
    url = 'http://' + VC_PAIR + '/api/v1/workflows/subscribed/to_me?access_token=' + session.get(USERTOKEN)
    print(url)
    r = do_get(url)
    print(r)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"services": data}), status