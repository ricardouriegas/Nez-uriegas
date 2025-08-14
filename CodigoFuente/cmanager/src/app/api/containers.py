from flask import Blueprint, session, jsonify, request
from flask_api import status
import docker
import requests
import simplejson as json
from ..constants import USERID, USERNAME#, USERCONTAINERS
from .. import dockercli, client, db
from ..models import UsersContainers, Container
from .images import getAllImages

containers_bp = Blueprint("containers", __name__, static_folder="url_for('static')", template_folder="url_for('templates')")
# containers_bp = Blueprint("containers", __name__)

@containers_bp.route('/create', methods=['POST'])
def createContainer():
    data = request.json
    container = {}

    # validate parameters
    parameters_allowedtype = {
        'image': (str),
        'command': (str, list),
        'auto_remove': (bool),
        'blkio_weight_device': (list),
        'blkio_weight': (int),
        'cap_add': (list),
        'cap_drop': (list),
        'cgroup_parent': (str),
        # 'cpu_count': (int),*
        # 'cpu_percent': (int),*
        'cpu_period': (int),
        'cpu_quota': (int),
        'cpu_rt_period': (int),
        'cpu_rt_runtime': (int),
        'cpu_shares': (int),
        'cpuset_cpus': (str),
        # 'cpuset_mems': (str),*
        'detach': (bool),
        'device_cgroup_rules': (list),
        'device_read_bps': (list),
        'device_read_iops': (int),
        'device_write_bps': (int),
        'device_write_iops': (int),
        'devices': (list),
        'device_requests': (list),
        'dns': (list),
        'dns_opt': (list),
        'dns_search': (list),
        'domainname': (str, list),
        'entrypoint': (str, list),
        'environment': (dict, list),
        'extra_hosts': (dict),
        'group_add': (list),
        'healthcheck': (dict),
        'hostname': (str),
        'init': (bool),
        'init_path': (str),
        'ipc_mode': (str),
        # 'isolation': (str), 'Default': None.
        'kernel_memory': (int, str),
        'labels': (dict, list),
        'links': (dict),
        # 'log_config': (LogConfig),
        'lxc_conf': (dict),
        'mac_address': (str),
        'mem_limit': (int, str),
        'mem_reservation': (int, str),
        'mem_swappiness': (int), 
        'memswap_limit': (str, int),
        'mounts': (list),
        'name': (str),
        'nano_cpus': (int),
        'network': (str),
        'network_disabled': (bool),
        # 'network_mode': (str),
        'oom_kill_disable': (bool),
        'oom_score_adj': (int),
        'pid_mode': (str),
        'pids_limit': (int),
        # 'platform': (str),#*
        'ports': (dict),
        'privileged': (bool),
        'publish_all_ports': (bool),
        'read_only': (bool),
        #'remove': (bool), 'Default': False.
        'restart_policy': (dict),
        'runtime': (str),
        'security_opt': (list),
        'shm_size': (str, int),
        'stdin_open': (bool),
        #'stdout': (bool), when detach=False. 'Default': True.
        #'stderr': (bool), when detach=False. 'Default': False.
        'stop_signal': (str),
        'storage_opt': (dict),
        'stream': (bool),
        'sysctls': (dict),
        'tmpfs': (dict),
        'tty': (bool),
        'ulimits': (list),
        'use_config_proxy': (bool),
        'user': (str, int),
        'userns_mode': (str),
        'uts_mode': (str),
        'version': (str),
        'volume_driver': (str),
        'volumes': (dict, list),
        'volumes_from': (list),
        'working_dir': (str)
    }
    params = {}
    for p,t in parameters_allowedtype.items():
        if data.get(p) and isinstance(data[p], t):
            params[p] = data[p]
    
    print('data: ', data)
    print('agrs: ', params)
    # TODO: if 'network', create the network if not exist
    

    if('advancedCreation' in data):
        if(not data['run']):
            # create without running
            try:
                container = client.containers.create(**params)
                print('created container', container.id)
                print('created container', container.status)
                addContainerToUser(container.id)
            except docker.errors.APIError as api_error:
                return { 'error': str(api_error) }
        else:        
            # create running
            try:
                container = client.containers.create(**params)
                # since run() doesnt return the new created container id, 
                # make a query to look up for the last created container on API
                # TODO: HERE
                print('created container', container.id)
                print('created container', container.status)
                addContainerToUser(container.id)
            except docker.errors.APIError as api_error:
                # since there's no way on knowing on client side, what caused the error
                # just return that there was an error, hoping for the sdk to implement something
                # for that.
                return { 'error': str(api_error) }

    else:
        # if not advanced
        print('basic data: ', data)
        volumeData = data['volume'].split(':') if 'volume' in data else None
        volume = {}
        if volumeData != None and len(volumeData) > 1:
            volume[volumeData[0]] = {
                'bind': volumeData[1],
                'mode': volumeData[2] if volumeData[2] else 'ro'
            }
        
        ports = data['ports'].split(':') if 'ports' in data else None
        ports = { ports[0]: ports[1] } if ports != None else None

        command = data['command'] if 'command' in data else None
        container = client.containers.create(image=data['image'], name=data['name'], ports=ports, command=command, tty=data['tty'], volumes=volume)
        addContainerToUser(container.id)
   
    if(data['run']):
        container.start()
    # Add container info to database
    new_container = Container(container.id, container.name, container.image.id)
    db.session.add(new_container)
    db.session.commit()
    
    # add recently created container to user session containers array.
    print('container id to append: ', container.id)
    # current_containers = session[USERCONTAINERS]
    # current_containers.append(container.id)
    # session[USERCONTAINERS] = current_containers
    print('session after adding container: ', session)

    return { 'containerid': container.short_id }

@containers_bp.route('/delete', methods=["DELETE"])
def deleteContainer():
    if request.method != 'DELETE':
        return jsonify({'delete': 'error'}), status.HTTP_405_METHOD_NOT_ALLOWED
    c_id = request.args.get('container')
    container = client.containers.get(c_id)
    if not container:
        return jsonify({'deleted': 'not found'}), status.HTTP_200_OK

    v = request.args.get('volumes')
    link = False # IGNORING SINCE CAUSES ERROR
    force = request.args.get('force')

    try:
        container.remove(v=v, link=link, force=force)
    except docker.errors.APIError as e:
        print(str(e))
    try:
        # remove with cli on docker
        dockercli.remove_container(c_id, v=v, link=link, force=force)
    except docker.errors.APIError as e:
        print(str(e))
        return jsonify({'deleted': 'error'}), status.HTTP_500_INTERNAL_SERVER_ERROR
    
    # delete from db, Container
    container_o = Container.query.get(c_id)
    if container_o:
        print(f'containerindb: {container_o}')
        db.session.delete(container_o)
        db.session.commit()
        
    # delete row on UserContainerTable
    usercontainer = UsersContainers.query.filter_by(
        user_id=session[USERID], container_id=c_id).first()
    if usercontainer:
        print('usercontainer to delete: ', usercontainer)
        db.session.delete(usercontainer)
        db.session.commit()

    return jsonify({'deleted': 'Ok'}), status.HTTP_200_OK



@containers_bp.route('/json', methods=["GET"])
def getContainers():
    containers = dockercli.containers(all=True)
    # if session.get(USERCONTAINERS) != None:
    #     print(f'usercontainers: {session.get(USERCONTAINERS)}')
    #     containers = list(filter(lambda c: c['Id'] in session[USERCONTAINERS], containers))
    # userContainers = db.session.query(Container, User).filter(
    #         UsersContainers.container_id == Container.id,
    #         UsersContainers.user_id == User.uid
    #     ).order_by(UsersContainers.user_id).all()
    return {'containers': containers}



@containers_bp.route('/inspect/<id>', methods=["GET"])
def getContainerInfo(id):
    container = {}
    try:
        container = dockercli.inspect_container(id)
    except docker.errors.APIError as err:
        return { "error": str(err) }
    return {"container": container} 

@containers_bp.route('/start/<id>', methods=["GET"])
def startContainer(id):
    try:
        dockercli.start(id)
    except docker.errors.APIError as err:
        return { 'error': str(err) }
    return { 'started': True }

@containers_bp.route('/stop/<id>', methods=["GET"])
def stopContainer(id):
    try:
        dockercli.stop(id)
    except docker.errors.APIError as err:
        return { 'error': str(err) }
    return { 'stopped': True }

@containers_bp.route('/restart/<id>', methods=["GET"])
def restartContainer(id):
    try:
        dockercli.restart(id)
    except docker.errors.APIError as err:
        return { 'error': str(err) }
    return { 'restarting': True }

@containers_bp.route('/pause/<id>', methods=["GET"])
def pauseContainer(id):
    try:
        dockercli.pause(id)
    except docker.errors.APIError as err:
        return { 'error': str(err) }
    return { 'paused': True }

@containers_bp.route('/unpause/<id>', methods=["GET"])
def unpauseContainer(id):
    try:
        dockercli.unpause(id)
    except docker.errors.APIError as err:
        return { 'error': str(err) }
    return { 'unpaused': True }

def addContainerToUser(idcontainer):
    # print('adding: ', idcontainer)
    # print('to user: ', session[USERNAME])
    # TODO: autoincrement id
    userContainer = UsersContainers(session[USERID], idcontainer)
    db.session.add(userContainer)
    db.session.commit()

# def containerToJson(container):
#     image = {"id": container.image.id, "tags": container.image.tags}
#     c = {"id": container.short_id, "name": container.name, "status": container.status, "image": image}
#     return json.dumps(c)