--
-- PostgreSQL database dump
--

--SET statement_timeout = 0;
--SET lock_timeout = 0;
--SET client_encoding = 'UTF8';
--SET standard_conforming_strings = on;
--SET check_function_bodies = false;
--SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

--CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

--COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--SET search_path = public, pg_catalog;

--SET default_tablespace = '';

--SET default_with_oids = false;

--
-- Name: data; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--


--
-- Name: files; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE files (
    keyfile character varying(100),
    namefile character varying(100),
    sizefile bigint,
    chunks integer,
    isciphered boolean,
    hashfile character varying(400),
    created_at timestamp DEFAULT NOW()
);


--ALTER TABLE public.files OWNER TO postgres;


CREATE TABLE chunks (
    id character varying(23) NOT NULL,
    name text NOT NULL,
    size integer NOT NULL,
    status character(1) DEFAULT '1'::bpchar NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL
);

CREATE TABLE chunks_file (
    chunk_id character varying(23) NOT NULL,
    file_id character varying(100) NOT NULL
);

CREATE TABLE nodes (
    id serial NOT NULL,
    url text NOT NULL,
    capacity bigint NOT NULL,
    memory bigint NOT NULL,
    status character(1) DEFAULT '1'::bpchar NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL
);

CREATE TABLE operations (
    id character varying(23) NOT NULL,
    user_id character varying(100) NOT NULL,
    file_id character varying(100) NOT NULL,
    chunk_id character varying(23) NOT NULL,
    node_id integer NOT NULL,
    type character(1) NOT NULL,
    status character(1) DEFAULT '0'::bpchar NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL
);



--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--



--
-- Data for Name: chunks; Type: TABLE DATA; Schema: public; Owner: postgres
--




--
-- Data for Name: data; Type: TABLE DATA; Schema: public; Owner: postgres
--




--
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: postgres
--















--
-- Name: files_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY files
    ADD CONSTRAINT files_pkey PRIMARY KEY (keyfile);

ALTER TABLE ONLY chunks
    ADD CONSTRAINT chunks_pkey PRIMARY KEY (id);
--
-- Name: user_files_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY chunks_file
    ADD CONSTRAINT file_chunks_ibfk_2 FOREIGN KEY (file_id) REFERENCES files(keyfile) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY operations
    ADD CONSTRAINT operations_ibfk_1 FOREIGN KEY (file_id) REFERENCES files(keyfile) ON UPDATE CASCADE ON DELETE CASCADE;



ALTER TABLE ONLY chunks_file
    ADD CONSTRAINT file_chunks_ibfk_1 FOREIGN KEY (chunk_id) REFERENCES chunks(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

--REVOKE ALL ON SCHEMA public FROM PUBLIC;
--REVOKE ALL ON SCHEMA public FROM postgres;
--GRANT ALL ON SCHEMA public TO postgres;
--GRANT ALL ON SCHEMA public TO PUBLIC;

--
CREATE TABLE abekeys (
    keyfile character varying(100),
    url text,
    PRIMARY KEY (keyfile, url)
);


--
-- PostgreSQL database dump complete
--

