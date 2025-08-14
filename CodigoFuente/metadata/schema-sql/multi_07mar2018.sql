--
-- PostgreSQL database dump
--

-- SET statement_timeout = 0;
-- SET lock_timeout = 0;
-- SET client_encoding = 'UTF8';
-- SET standard_conforming_strings = on;
-- SET check_function_bodies = false;
-- SET client_min_messages = warning;

-- --
-- -- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
-- --

-- CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


-- --
-- -- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
-- --

-- COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


-- SET search_path = public, pg_catalog;

-- SET default_tablespace = '';

-- SET default_with_oids = false;

--
-- Name: data; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE data (
    id_obt text,
    idfile text,
    publicated integer
);


--ALTER TABLE public.data OWNER TO postgres;

--
-- Name: file_stats; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE file_stats (
    namefile character varying(1000),
    sizefile bigint,
    chunks integer,
    upload_time integer,
    cryp_time integer,
    id integer NOT NULL
);


--ALTER TABLE public.file_stats OWNER TO postgres;

--
-- Name: file_stats_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE file_stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--ALTER TABLE public.file_stats_id_seq OWNER TO postgres;

--
-- Name: file_stats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

--ALTER SEQUENCE file_stats_id_seq OWNED BY file_stats.id;


--
-- Name: files; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE files (
    keyfile character varying(100),
    namefile character varying(1000),
    sizefile bigint,
    chunks integer,
    isciphered boolean,
    hashfile character varying(400),
    disperse character varying(100),
    created_at timestamp DEFAULT NOW()
);


--ALTER TABLE public.files OWNER TO postgres;

--
-- Name: log_stats; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE log_stats (
    auth_time integer,
    id integer NOT NULL
);


--ALTER TABLE public.log_stats OWNER TO postgres;

--
-- Name: log_stats_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE log_stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--ALTER TABLE public.log_stats_id_seq OWNER TO postgres;

--
-- Name: log_stats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE log_stats_id_seq OWNED BY log_stats.id;


--
-- Name: logsfed; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE logsfed (
    idlog text,
    idfile text,
    typeoperation text,
    "time" double precision,
    size double precision
);


--ALTER TABLE public.logsfed OWNER TO postgres;

--
-- Name: publishfed; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE publishfed (
    id_ob text,
    url text,
    obt integer,
    "position" integer,
    organization text,
    id_chunk text,
    idcatalog text,
    size double precision,
    url2 text
);


--ALTER TABLE public.publishfed OWNER TO postgres;

--
-- Name: push; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE push (
    keyuser character varying(100),
    keyfile character varying(100),
    keyresource character varying(120),
    "time" bigint
);


--ALTER TABLE public.push OWNER TO postgres;

--
-- Name: resources; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE resources (
    keyresource character varying(100),
    typeresource character varying(100),
    nameresource character varying(100),
    "time" date
);


--ALTER TABLE public.resources OWNER TO postgres;

--
-- Name: servers; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE servers (
    url character varying(100)
);


--ALTER TABLE public.servers OWNER TO postgres;

--
-- Name: stats; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE stats (
    tokenuser character varying(200),
    tokenapi character varying(200),
    sizefile bigint,
    chunks integer,
    "time" bigint,
    type character varying(100),
    id integer NOT NULL,
    id_root integer,
    typetask text,
    keyfile character varying(100),
    organization character varying(100),
    sequence integer NOT NULL
);


--ALTER TABLE public.stats OWNER TO postgres;

--
-- Name: stats_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--ALTER TABLE public.stats_id_seq OWNER TO postgres;

--
-- Name: stats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE stats_id_seq OWNED BY stats.id;


--
-- Name: stats_sequence_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE stats_sequence_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--ALTER TABLE public.stats_sequence_seq OWNER TO postgres;

--
-- Name: stats_sequence_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE stats_sequence_seq OWNED BY stats.sequence;


--
-- Name: subscribe; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE subscribe (
    keyuser character varying(100),
    keyresource character varying(100)
);


--ALTER TABLE public.subscribe OWNER TO postgres;

--
-- Name: user_resources; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_resources (
    keyresource character varying(100),
    keyuser character varying(100)
);


--ALTER TABLE public.user_resources OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users (
    keyuser character varying(100),
    password character varying(100),
    nameuser character varying(100),
    tokenuser character varying(100),
    apikey character varying(150)
);


--ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_resources; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users_resources (
    keyresource character varying(200),
    typeresource character varying(150)
);

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


CREATE TABLE abekeys (
    keyfile character varying(100),
    url text,
    PRIMARY KEY (keyfile, url)
);


--ALTER TABLE public.users_resources OWNER TO postgres;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY file_stats ALTER COLUMN id SET DEFAULT nextval('file_stats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY log_stats ALTER COLUMN id SET DEFAULT nextval('log_stats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY stats ALTER COLUMN id SET DEFAULT nextval('stats_id_seq'::regclass);


--
-- Name: sequence; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY stats ALTER COLUMN sequence SET DEFAULT nextval('stats_sequence_seq'::regclass);


--
-- Data for Name: chunks; Type: TABLE DATA; Schema: public; Owner: postgres
--




--
-- Data for Name: data; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: file_stats; Type: TABLE DATA; Schema: public; Owner: postgres
--


--
-- Name: file_stats_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('file_stats_id_seq', 1, false);


--
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: postgres
--


--
-- Data for Name: log_stats; Type: TABLE DATA; Schema: public; Owner: postgres
--


--
-- Name: log_stats_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('log_stats_id_seq', 1, false);


--
-- Data for Name: logsfed; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: publishfed; Type: TABLE DATA; Schema: public; Owner: postgres
--


--
-- Data for Name: push; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: resources; Type: TABLE DATA; Schema: public; Owner: postgres
--

-- COPY resources (keyresource, typeresource, nameresource, "time") FROM stdin;
-- 7a54c1228c21d8ec9377d47f7343cc4cbeeae781	1	datos_CTTB	\N
-- 575277e31149ad8488929c3c12079825310141db	1	escapula	\N
-- ac7ea96e38bdd8f7f7d8b50fe26a0b99228cb12c	1	maniqui	\N
-- 49ca751ebdcaecbbc0e577d5b8f721915ba5e342	1	maniqui_tomo	\N
-- 8b983d2f96385524f3b37ff34c064d0c2b4361ef	1	maxilar_cerdo	\N
-- 8d34f001c744a5f43e3de73db9dacc51286f6866	1	raton_porfiria	\N
-- 3814fada8f7e9ba119d860c8dbc1ab8e1e629ba8	1	raton_rodaja	\N
-- feb327c23ec64f64e97704f5a12280c3bbed16be	1	total_process	\N
-- \.


--
-- Data for Name: servers; Type: TABLE DATA; Schema: public; Owner: postgres
--

-- COPY servers (url) FROM stdin;
-- http://127.0.0.1/s1
-- http://127.0.0.1/s2
-- http://127.0.0.1/s3
-- http://127.0.0.1/s4
-- http://127.0.0.1/s5
-- \.


--
-- Data for Name: stats; Type: TABLE DATA; Schema: public; Owner: postgres
--


--
-- Name: stats_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('stats_id_seq', 382747, true);


--
-- Name: stats_sequence_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('stats_sequence_seq', 52376, true);


--
-- Data for Name: subscribe; Type: TABLE DATA; Schema: public; Owner: postgres
--

-- COPY subscribe (keyuser, keyresource) FROM stdin;
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	7a54c1228c21d8ec9377d47f7343cc4cbeeae781
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	575277e31149ad8488929c3c12079825310141db
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	ac7ea96e38bdd8f7f7d8b50fe26a0b99228cb12c
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	49ca751ebdcaecbbc0e577d5b8f721915ba5e342
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	8b983d2f96385524f3b37ff34c064d0c2b4361ef
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	8d34f001c744a5f43e3de73db9dacc51286f6866
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	3814fada8f7e9ba119d860c8dbc1ab8e1e629ba8
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	feb327c23ec64f64e97704f5a12280c3bbed16be
-- \.


--
-- Data for Name: user_resources; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

-- COPY users (keyuser, password, nameuser, tokenuser, apikey) FROM stdin;
-- e259e8b10d002a9b3a21183a38699dfda9614ffb	holamundo	Rios	5b1a32474bf6975d16f69dafb3ae094306b01302	e735767956703ccc2ccad1ce70728effe6ec7138
-- \.


-- --
-- -- Data for Name: users_resources; Type: TABLE DATA; Schema: public; Owner: postgres
-- --

-- COPY users_resources (keyresource, typeresource) FROM stdin;
-- 6c2a3d9cd9407f32e9a20b618ef8ff5db9f95b76	d0346d5c69a5244237db40577e973af7c1148b5a
-- 6796f4210bcfb58461d7338c9bea7db0de64bcbd	63c9f17669d1d179deca5b0e59440e6d11be4083
-- d2c6c0569573f82b41d828758b7a8fcbfc713d81	9c1ae0c683204cc92ce2ad8e352a132820ef697e
-- eed457bb0f86a33b40f0a74e0a69e527c24e5147	f6cf326e0d6399469e854b9621e33e91535792fd
-- 124f1102cc32df62bed3e8ba22bb2dbd264e8818	9c1ae0c683204cc92ce2ad8e352a132820ef697e
-- 7a54c1228c21d8ec9377d47f7343cc4cbeeae781	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- 575277e31149ad8488929c3c12079825310141db	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- ac7ea96e38bdd8f7f7d8b50fe26a0b99228cb12c	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- 49ca751ebdcaecbbc0e577d5b8f721915ba5e342	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- 8b983d2f96385524f3b37ff34c064d0c2b4361ef	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- 8d34f001c744a5f43e3de73db9dacc51286f6866	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- 3814fada8f7e9ba119d860c8dbc1ab8e1e629ba8	e259e8b10d002a9b3a21183a38699dfda9614ffb
-- feb327c23ec64f64e97704f5a12280c3bbed16be	e259e8b10d002a9b3a21183a38699dfda9614ffb
\.


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

ALTER TABLE ONLY push
    ADD CONSTRAINT push_ibfk_1 FOREIGN KEY (keyfile) REFERENCES files(keyfile) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY chunks_file
    ADD CONSTRAINT file_chunks_ibfk_1 FOREIGN KEY (chunk_id) REFERENCES chunks(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY stats
    ADD CONSTRAINT stats_ibfk_1 FOREIGN KEY (keyfile) REFERENCES files(keyfile) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

-- REVOKE ALL ON SCHEMA public FROM PUBLIC;
-- REVOKE ALL ON SCHEMA public FROM postgres;
-- GRANT ALL ON SCHEMA public TO postgres;
-- GRANT ALL ON SCHEMA public TO PUBLIC;

-- --


--
-- PostgreSQL database dump complete
--

