#!/usr/bin/python
from flask import Flask, jsonify, request
from flask_api import status
import subprocess
import os
# import requests
# import simplejson as json
from config import Config, ProductionConfig, DevelopmentConfig


app = Flask(__name__)
app.config.from_object(Config())
if app.config.get('ENV') == 'production':
  app.config.from_object(ProductionConfig())
if app.config.get('ENV') == 'development':
  app.config.from_object(DevelopmentConfig())


@app.route('/', methods=['GET'])
def h():
  return jsonify({'msg': 'Deployer'}), status.HTTP_200_OK

@app.route('/file/segment', methods=['POST'])
def deploy():
  s = status.HTTP_400_BAD_REQUEST
  res = {'msg': 'Error'}
  rj = request.json
  print rj
  if not (rj.get('file') and rj.get('segments') and rj.get('base')):
    return jsonify(res), s
  
  args = "./ALInputFS %s %d %d" % (rj.get('file'), int(rj.get('segments')), int(rj.get('base')))
  

  sp = subprocess.Popen(args, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, shell=True)
  sp.wait()
  
  if sp.returncode == 0:
    s = status.HTTP_200_OK
    res['msg'] = 'executed'
  
  stdouterr, _ = sp.communicate()
  stdouterr = stdouterr.decode("utf-8")
  res['out'] = stdouterr

  return jsonify(res), s


if __name__ == '__main__':
  app.run(host='0.0.0.0', port=5001, debug=True)