from flask import Blueprint, render_template, session, redirect, url_for
from flask_api import status
import requests
import simplejson as json
from urllib.parse import urlencode
from .constants import USERID, USERNAME
from .models import User
from .api.images import getAllImages
from .api.containers import getContainers

views_bp = Blueprint("views", __name__, static_folder="url_for('static')", template_folder="url_for('templates')")

def isLogged():
  u = session.get(USERNAME)
  print(f'usersession: {u}')
  return True if u else False

""" LOGIN """
@views_bp.route('/', methods=['GET'])
@views_bp.route('/login', methods=['GET'])
def loginscreen():
  if isLogged():
    return redirect('/home', code=status.HTTP_302_FOUND)
  return render_template('login/login.html')


""" HOME """
@views_bp.route('/home', methods=['GET'])
def home():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('index.html', user=session[USERNAME])



""" CONTAINERS """
@views_bp.route('/containers/creation', methods=['GET'])
def showContainerCreation():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  data = getAllImages()
  return render_template('containers/creator.html', images=data['images'])

@views_bp.route('/containers/details', methods=['GET'])
def showContainerDetails():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('containers/details.html')

@views_bp.route('/containers', methods=['GET'])
@views_bp.route('/containers/', methods=['GET'])
def listContainers():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  containerslist = getContainers()
  return render_template('containers/list.html', containers=json.dumps(containerslist))



""" IMAGES """
@views_bp.route('/images/builder', methods=['GET'])
def showImageBuilder():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('images/builder.html')

@views_bp.route('/images/details', methods=['GET'])
def showImageDetails():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('images/details.html')

@views_bp.route('/images', methods=['GET'])
@views_bp.route('/images/', methods=['GET'])
def listImages():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('images/list.html')



""" SERVICES """
@views_bp.route('/services', methods=['GET'])
def listServices():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('services/list.html')

@views_bp.route('/services/new', methods=['GET'])
def newServices():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('services/creator.html')

@views_bp.route('/services/edit/<string:id_service>', methods=['GET'])
def editService(id_service):
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('services/creator.html', ids=id_service)

@views_bp.route('/services/subs', methods=['GET'])
def servicesSubs():
  if not isLogged():
    return redirect('/login', code=status.HTTP_302_FOUND)
  return render_template('services/list-subs.html')

