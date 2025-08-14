CREATE TABLE buildingblock (id serial NOT NULL PRIMARY KEY, owner varchar(50), name varchar(50), command varchar(100), image varchar(30), port varchar(10), created timestamp);

CREATE TABLE patterns (id serial NOT NULL PRIMARY KEY, owner varchar(50), name varchar(50), task varchar(50), pattern varchar(50), workers int, loadBalancer varchar(50), created timestamp);

CREATE TABLE stages(id serial NOT NULL PRIMARY KEY, owner varchar(300), name varchar(150), source text, sink text, transformation varchar(150), created timestamp);

CREATE TABLE workflows(id serial NOT NULL PRIMARY KEY, owner varchar(200), name varchar(50), status int, stages varchar(200),  created timestamp);

CREATE TABLE users(id serial NOT NULL PRIMARY KEY, name varchar(30), email varchar (30), password varchar(50), typeUser int);

CREATE TABLE pubsub(id serial NOT NULL PRIMARY KEY, idWorkflow int, idUser int, status int , c int, r int, u int, d int );

CREATE TABLE ymlFiles (id serial NOT NULL PRIMARY KEY, owner int, name varchar(100) , pathFile varchar(200), description text, created timestamp);



CREATE TABLE stagepaternbb (id serial NOT NULL PRIMARY KEY, stage varchar(100), pattern int, buildingBlock int);


select w.id, w.name, w.owner,   from workflos as w  join pubsub as p
  on w.id=p.idWorkflow where w.ower!=1;


SELECT *  FROM workflows w WHERE NOT EXISTS (SELECT NULL FROM pubsub p WHERE w.id = p.idWorkflow and w.owner = p.iduser);

