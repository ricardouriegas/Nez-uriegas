from sqlalchemy import ForeignKey
from . import db

class User(db.Model):
    __tablename__ = 'users'
    uid = db.Column(db.Integer, primary_key=True, autoincrement=True)
    username = db.Column(db.String(255))
    email = db.Column(db.String(255))
    password = db.Column(db.String(255))
    token = db.Column(db.String(64))
    containers = db.relationship('Container', secondary='users_containers')
    def __init__(self, username, email, password, token):
        self.username = username
        self.email = email
        self.password = password
        self.token = token
    @property
    def serialize(self):
       """Return object data in easily serializable format"""
       return {
           'uid': self.uid,
           'username': self.username,
           'email': self.email,
           'password': self.password,
           'token': self.token,
       }

class Image(db.Model):
    __tablename__ = 'images'
    id = db.Column(db.String(255), primary_key=True)
    name = db.Column(db.String(255))
    users = db.relationship('User', secondary='users_images')
    def __init__(self, id, name):
        self.id = id
        self.name = name
    @property
    def serialize(self):
       """Return object data in easily serializable format"""
       return {
           'id': self.id,
           'name': self.name,
       }

class Container(db.Model):
    __tablename__ = 'containers'
    id = db.Column(db.String(255), primary_key=True)
    name = db.Column(db.String(255)) 
    image_id = db.Column(db.String(255))
    users = db.relationship('User', secondary='users_containers')
    def __init__(self, id, name, image_id):
        self.id = id
        self.name = name
        self.image_id = image_id
    @property
    def serialize(self):
       """Return object data in easily serializable format"""
       return {
           'id': self.id,
           'name': self.name,
           'image_id': self.image_id,
       }

class UsersContainers(db.Model):
    __tablename__ = 'users_containers'
    id = db.Column(db.Integer(), primary_key=True, autoincrement=True)
    user_id = db.Column(db.Integer, 
        ForeignKey('users.uid'))
    container_id = db.Column(db.String(255),
        ForeignKey('containers.id'))
    def __init__(self, user_id, container_id):
        self.user_id = user_id
        self.container_id = container_id

class UsersImages(db.Model):
    __tablename__ = 'users_images'
    id = db.Column(db.Integer(), primary_key=True, autoincrement=True)
    user_id = db.Column(db.Integer, 
        ForeignKey('users.uid'))
    image_id = db.Column(db.String(255),
        ForeignKey('images.id'))
    def __init__(self, user_id, image_id):
        self.user_id = user_id
        self.image_id = image_id

class Context(db.Model):
    __tablename__ = 'contexts'
    id = db.Column(db.String, primary_key=True)
    folder = db.Column(db.String(255))
    def __init__(self, id, folder):
        self.id = id
        self.folder = folder
    @property
    def serialize(self):
       """Return object data in easily serializable format"""
       return {
           'id': self.id,
           'folder': self.folder,
       }

# class File(db.Model):
#     __tablename__ = 'files'
#     id = db.Column(db.Integer, primary_key=True)
#     ctx_id = db.Column(db.String(255))
#     name = db.Column(db.String(255))

# class Service(db.Model):
#     __tablename__ = 'services'
#     id = db.Column(db.String(255), primary_key=True)
#     name = db.Column(db.String(255))
#     image = db.Column(db.String(255))
#     replicas = db.Column(db.Integer)
#     def __init__(self, id, name, image, replicas):
#         self.id = id
#         self.name = name
#         self.image = image
#         self.replicas = replicas

# class Piece(db.Model):
#     __tablename__ = 'pieces'
#     id = db.Column(db.Integer, primary_key=True)
#     name = db.Column(db.String(255))
#     host = db.Column(db.String(255))
#     def __init__(self, id, name, host):
#         self.id = id
#         self.name = name
#         self.host = host
#     def __repr__(self):
#         return '<UC %d>' % self.id