from flask import Flask, session
from flask_cors import CORS
from flask_sqlalchemy import SQLAlchemy
import docker

app = Flask(__name__, instance_relative_config=True)
app.config.from_object('config')
app.config.from_pyfile('config.py')

with app.app_context():
  CORS(app)
  db = SQLAlchemy(app)
  #from .models import *
  #db.init_app(app)
  # db.drop_all()
  #db.create_all()

  client = docker.DockerClient(base_url='unix://var/run/docker.sock')
  dockercli = docker.APIClient(base_url='unix://var/run/docker.sock')

def initialize_app():
  from .api.login import login_bp
  from .api.images import images_bp
  from .api.containers import containers_bp
  from .api.services import services_bp
  from .api.test import test_bp
  from .views import views_bp

  app.register_blueprint(login_bp)
  app.register_blueprint(images_bp, url_prefix="/images")
  app.register_blueprint(containers_bp, url_prefix="/containers")
  app.register_blueprint(services_bp)
  app.register_blueprint(test_bp)

  app.register_blueprint(views_bp)

  return app
