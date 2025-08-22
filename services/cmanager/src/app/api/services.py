from flask import Blueprint, session, request, jsonify
from .requests_handler import do_get, do_post, do_put, do_delete
from ..constants import USERTOKEN, VC_PAIR

services_bp = Blueprint("services", __name__, static_folder="url_for('static')", template_folder="url_for('templates')")


@services_bp.route("/services/create", methods=["POST"])
def add_service():
    res = {'msg': 'Error'}
    payload = {}
    payload['name'] = request.form.get('nameWorkflow')
    payload['status'] = request.form.get('statusWorkflow')
    payload['stages'] = request.form.get('stages')
    payload['rawgraph'] = request.form.get('rawgraph')
    url = 'http://' + VC_PAIR + '/api/v1/workflows?access_token=' + session.get(USERTOKEN)
    r = do_post(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 201:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/services/json", methods=["GET"])
def get_services():
    url = 'http://' + VC_PAIR + '/api/v1/workflows?access_token=' + session.get(USERTOKEN)
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"services": data}), status

@services_bp.route("/services/edit", methods=["PUT"])
def edit_service():
    # print(request.form)
    res = {'msg': 'Error'}
    payload = {}
    payload['id'] = request.form.get('updateIdWF')
    payload['name'] = request.form.get('nameWorkflow')
    payload['status'] = request.form.get('statusWorkflow')
    payload['stages'] = request.form.get('stages')
    payload['rawgraph'] = request.form.get('rawgraph')
    url = 'http://' + VC_PAIR + '/api/v1/workflows?access_token=' + session.get(USERTOKEN)
    r = do_put(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/services/delete", methods=["DELETE"])
def delete_service():
    # print(request.args) # []
    # print(request.form) # [data]
    # print(request.values) # [ID(), ID()]
    # print(request.json) # None
    res = {'msg': 'Error'}
    id = request.form.get('idWorkflow')
    url = 'http://' + VC_PAIR + '/api/v1/workflows?access_token=' + session.get(USERTOKEN)
    r = do_delete(url, payload={'id': id})
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json(), "id": id}
    return jsonify(res), status

@services_bp.route("/services/id/<string:id>", methods=["GET"])
def get_single_service(id):
    url = 'http://' + VC_PAIR + '/api/v1/workflows/' + id +'?access_token=' + session.get(USERTOKEN)
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify(data), status

@services_bp.route("/services/run", methods=["POST"])
def run_service():
    res = {'msg': 'Error'}
    payload = {}
    payload['id'] = request.form.get('idWorkflowRun')
    url = 'http://' + VC_PAIR + '/api/v1/workflows/run?access_token=' + session.get(USERTOKEN)
    r = do_post(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/services/log", methods=["POST"])
def log_service():
    res = {'msg': 'Error'}
    payload = {}
    payload['name'] = request.form.get('idWorkflowRead')
    url = 'http://' + VC_PAIR + '/api/v1/workflows/log?access_token=' + session.get(USERTOKEN)
    r = do_post(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = r.json()
    return jsonify(res), status


@services_bp.route("/stages/create", methods=["POST"])
def add_stage():
    # print(request.form)
    res = {'msg': 'Error'}
    payload = {}
    payload['name'] = request.form.get('nameStage')
    payload['source'] = request.form.get('sourceStage')
    payload['sink'] = request.form.get('sinkStage')
    payload['transformation'] = request.form.get('transformationStage')
    url = 'http://' + VC_PAIR + '/api/v1/stages?access_token=' + session.get(USERTOKEN)
    r = do_post(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 201:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/stages/json", methods=["GET"])
def get_stages():
    url = 'http://' + VC_PAIR + '/api/v1/stages?access_token=' + session.get(USERTOKEN)
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"stages": data}), status

@services_bp.route("/stages/edit", methods=["PUT"])
def edit_stage():
    # print(request.form)
    res = {'msg': 'Error'}
    payload = {}
    payload['id'] = request.form.get('updateIdST')
    payload['name'] = request.form.get('updateNameST')
    payload['source'] = request.form.get('updateSourceST')
    payload['sink'] = request.form.get('updateSinkST')
    payload['transformation'] = request.form.get('updateTransfST')
    url = 'http://' + VC_PAIR + '/api/v1/stages?access_token=' + session.get(USERTOKEN)
    r = do_put(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/stages/delete", methods=["DELETE"])
def delete_stage():
    # print(request.form)
    res = {'msg': 'Error'}
    id = request.form.get('idStage')
    url = 'http://' + VC_PAIR + '/api/v1/stages?access_token=' + session.get(USERTOKEN)
    r = do_delete(url, payload={'id': id})
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json(), "id": id}
    return jsonify(res), status

@services_bp.route("/stages/updatetransformation", methods=["PUT"])
def edit_stage_transf():
    res = {'msg': 'Error'}
    payload = {}
    payload['id'] = request.form.get('idStage')
    payload['transformation'] = request.form.get('nameBB')
    url = 'http://' + VC_PAIR + '/api/v1/stages/transformation?access_token=' + session.get(USERTOKEN)
    r = do_put(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json()}
    return jsonify(res), status


@services_bp.route("/buildingblocks/create", methods=["POST"])
def add_buildingblock():
    res = {'msg': 'Error'}
    payload = {}
    payload['name'] = request.form.get('nameBB')
    payload['command'] = request.form.get('commandBB')
    payload['image'] = request.form.get('imageBB')
    payload['port'] = request.form.get('portBB')
    url = 'http://' + VC_PAIR + '/api/v1/buildingblocks?access_token=' + session.get(USERTOKEN)
    r = do_post(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 201:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/buildingblocks/json", methods=["GET"])
def get_buildingblocks():
    url = 'http://' + VC_PAIR + '/api/v1/buildingblocks?access_token=' + session.get(USERTOKEN)
    r = do_get(url)
    status = (r.status_code if r != None and r.status_code else 500)
    data = []
    if status == 200:
        data = r.json()
    return jsonify({"buildingblocks": data}), status

@services_bp.route("/buildingblocks/edit", methods=["PUT"])
def edit_buildingblocks():
    res = {'msg': 'Error'}
    payload = {}
    payload['id'] = request.form.get('updateIdBB')
    payload['name'] = request.form.get('updateNameBB')
    payload['image'] = request.form.get('updateImageBB')
    payload['command'] = request.form.get('updateCommandBB')
    payload['port'] = request.form.get('updatePortBB')
    url = 'http://' + VC_PAIR + '/api/v1/buildingblocks?access_token=' + session.get(USERTOKEN)
    r = do_put(url, payload)
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json()}
    return jsonify(res), status

@services_bp.route("/buildingblocks/delete", methods=["DELETE"])
def delete_buildingblock():
    res = {'msg': 'Error'}
    id = request.form.get('idBuildingBlock')
    url = 'http://' + VC_PAIR + '/api/v1/buildingblocks?access_token=' + session.get(USERTOKEN)
    r = do_delete(url, payload={'id': id})
    status = (r.status_code if r != None and r.status_code else 500)
    if status == 200:
        res = {"msg": r.json(), "id": id}
    return jsonify(res), status
