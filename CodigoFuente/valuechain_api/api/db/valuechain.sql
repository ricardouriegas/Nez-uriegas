CREATE TABLE buildingblock(
    "id" serial NOT NULL PRIMARY KEY,
    "owner" character varying(400),
    "name" character varying(50),
    "command" character varying(500),
    "image" character varying(300),
    "port" character varying(10),
    "description" text,
    "created" timestamp without time zone
);

CREATE TABLE stages(
    "id" serial NOT NULL PRIMARY KEY,
    "owner"  character varying(400),
    "name" character varying(50),
    "buildingblock" int,
    "created" timestamp without time zone,
    CONSTRAINT ref_building_blocks FOREIGN KEY (buildingblock) REFERENCES buildingblock(id)
);

CREATE TABLE workflows(
    "id" serial NOT NULL PRIMARY KEY,
    "owner"  character varying(400),
    "name" character varying(200),
    "status" integer,
    "created" timestamp without time zone
);

CREATE TABLE source_type(
    "id" int NOT NULL PRIMARY KEY,
    "name" varchar(100)
);

INSERT INTO source_type("id", "name") VALUES(1, 'Catalog');
INSERT INTO source_type("id", "name") VALUES(2, 'Path');
INSERT INTO source_type("id", "name") VALUES(3, 'Stage');


CREATE TABLE stage_source(
    "id" serial NOT NULL PRIMARY KEY,
    "source_type" int,
    "stage_id" int,
     CONSTRAINT ref_stages_source FOREIGN KEY (stage_id) REFERENCES stages(id),
     CONSTRAINT ref_stages_source_type FOREIGN KEY (source_type) REFERENCES source_type(id)
);

CREATE TABLE stage_sink(
    "id" serial NOT NULL PRIMARY KEY,
    "sink_type" int,
    "stage_id" int,
     CONSTRAINT ref_stages_sink FOREIGN KEY (stage_id) REFERENCES stages(id),
     CONSTRAINT ref_stages_sink_type FOREIGN KEY (sink_type) REFERENCES source_type(id)
);


CREATE TABLE workflows_source(
    "id" serial NOT NULL PRIMARY KEY,
    "source_type" int,
    "workflow_id" int,
     CONSTRAINT ref_workflows_source FOREIGN KEY (workflow_id) REFERENCES workflows(id),
     CONSTRAINT ref_workflows_source_type FOREIGN KEY (source_type) REFERENCES source_type(id)
);

CREATE TABLE workflows_source_catalog(
    "id" int NOT NULL PRIMARY KEY,
    "catalog" character varying(200),
    CONSTRAINT ref_workflows_source_catalog FOREIGN KEY (id) REFERENCES stage_source(id)
);

CREATE TABLE workflows_source_path(
    "id" int NOT NULL PRIMARY KEY,
    "path" character varying(200),
    CONSTRAINT ref_workflows_source_path FOREIGN KEY (id) REFERENCES stage_source(id)
);

CREATE TABLE workflows_source_bb(
    "id" int NOT NULL PRIMARY KEY,
    "id_bb" int,
    CONSTRAINT ref_workflows_source_bb FOREIGN KEY (id) REFERENCES stage_source(id),
    CONSTRAINT ref_workflows_source_bb_id FOREIGN KEY (id_bb) REFERENCES buildingblock(id)
);


CREATE TABLE workflows_sink_bb(
    "id" int NOT NULL PRIMARY KEY,
    "id_bb" int,
    CONSTRAINT ref_workflows_sink_bb FOREIGN KEY (id) REFERENCES stage_sink(id),
    CONSTRAINT ref_workflows_sink_bb_id FOREIGN KEY (id_bb) REFERENCES buildingblock(id)
);


CREATE TABLE workflow_stages(
    "id_workflow" serial NOT NULL,
    "id_stage"  serial NOT NULL,
    CONSTRAINT ref_workflow_stages FOREIGN KEY (id_workflow) REFERENCES workflows(id),
    CONSTRAINT ref_stages_workflow FOREIGN KEY (id_stage) REFERENCES stages(id),
    PRIMARY KEY(id_workflow, id_stage)
);

CREATE TABLE pub_wf_to_user(
    "id" serial NOT NULL PRIMARY KEY,
    "idworkflow" integer,
    "iduser"  character varying(400),
    "subscribed" boolean NOT NULL DEFAULT false,
    "created" timestamp without time zone
);




CREATE TABLE non_functional_requirement(
    "id" int NOT NULL PRIMARY KEY,
    "name" character varying(100),
    "description" character varying(100),
    "created" timestamp without time zone
);


CREATE TABLE non_functional_technique(
    "id" serial NOT NULL PRIMARY KEY,
    "type" int,
    "name" character varying(100),
    "description" text,
    "image" character varying(300),
    "created" timestamp without time zone,
    CONSTRAINT ref_non_functional_requirement FOREIGN KEY (type) REFERENCES non_functional_requirement(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE workflows_requirements(
    "id_workflow" serial NOT NULL,
    "id_requirement"  serial NOT NULL,
    CONSTRAINT ref_workflow FOREIGN KEY (id_workflow) REFERENCES workflows(id),
    CONSTRAINT ref_requirement FOREIGN KEY (id_requirement) REFERENCES non_functional_technique(id),
    PRIMARY KEY(id_workflow, id_requirement)
);

INSERT INTO non_functional_requirement("id", "name", "description", "created") VALUES(1, 'Efficiency', 'Improves the performance of the applications.', now());
INSERT INTO non_functional_requirement("id","name", "description", "created") VALUES(2, 'Security', 'Manage data in a confidential manner.', now());
INSERT INTO non_functional_requirement("id","name", "description", "created") VALUES(3, 'Reliability', 'Faul-tolerance.', now());


INSERT INTO non_functional_technique("type", "name", "description", "image", "created") VALUES(1, 'Manager/worker', 'Task parallelism.', 'ubuntu:18.04', now());
INSERT INTO non_functional_technique("type", "name", "description", "image", "created") VALUES(1, 'Deduplication', 'Identify data duplicated.', 'ubuntu:18.04', now());
INSERT INTO non_functional_technique("type", "name", "description", "image", "created") VALUES(1, 'Compression', 'Compress the data to reduce their volume.', 'ubuntu:18.04', now());
INSERT INTO non_functional_technique("type", "name", "description", "image", "created") VALUES(2, 'AES4SEC', 'AES4SeC are attribute-based encryption (ABE) and short signatures (SSign)', 'ubuntu:18.04', now());
INSERT INTO non_functional_technique("type", "name", "description", "image", "created") VALUES(3, 'IDA', 'Information dispersal algorithm.', 'ubuntu:18.04', now());


CREATE TABLE deployments_status(
    id int NOT NULL PRIMARY KEY,
    "description" character varying(100)
);

INSERT INTO deployments_status("id", "description") VALUES(1, 'Ok');
INSERT INTO deployments_status("id", "description") VALUES(2, 'Failed');

CREATE TABLE platforms(
    id serial not null PRIMARY key,
    "platform" character varying(100)
);

INSERT INTO platforms("platform") VALUES('Compose');
INSERT INTO platforms("platform") VALUES('Swarm');


CREATE TABLE deployments(
    "id" serial NOT NULL PRIMARY KEY,
    "executed" timestamp without time zone,
    "final_status" int NOT NULL, 
    "platform" int NOT NULL,
    "id_structure" int NOT NULL,
    FOREIGN KEY (final_status) REFERENCES deployments_status(id),
    FOREIGN KEY (platform) REFERENCES platforms(id),
    FOREIGN KEY (id_structure) REFERENCES workflows(id)
);


CREATE TABLE executions(
    "id" serial NOT NULL PRIMARY KEY,
    "executed" timestamp without time zone,
    "final_status" int NOT NULL, 
    "platform" int NOT NULL,
    "id_structure" int NOT NULL,
    FOREIGN KEY (final_status) REFERENCES deployments_status(id),
    FOREIGN KEY (platform) REFERENCES platforms(id),
    FOREIGN KEY (id_structure) REFERENCES workflows(id)
);

-- CREATE TABLE patterns(
--     id serial NOT NULL PRIMARY KEY,
--     "owner" character varying(50),
--     "name" character varying(50),
--     task character varying(50),
--     pattern character varying(50),
--     workers integer,
--     loadBalancer character varying(50),
--     "created" timestamp without time zone
-- );

-- CREATE TABLE stagepaternbb(
--     id serial NOT NULL PRIMARY KEY,
--     stage character varying(100),
--     pattern integer,
--     buildingblock integer
-- );

-- CREATE TABLE ymlfiles(
--     id serial NOT NULL PRIMARY KEY,
--     "owner" integer,
--     "name" character varying(100) ,
--     pathfile character varying(200),
--     description text,
--     "created" timestamp without time zone
-- );