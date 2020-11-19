--
-- PostgreSQL database dump
--

--
-- Name: auth_company; Type: TABLE; Schema: public;
--

CREATE TABLE auth_company (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	stat integer DEFAULT 100 NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	created_at timestamp without time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	name character varying(256),
	code varchar(64),
	cre character varying(32),
	cre_meta jsonb
);


-- Company Relationships
CREATE TABLE auth_company_company (
	company_id_prime character varying(26) NOT NULL,
	company_id_child character varying(26) NOT NULL
);


CREATE TABLE auth_company_contact (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	company_id character varying(26) NOT NULL,
	contact_id character varying(26) NOT NULL,
	stat integer DEFAULT 200 NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	session_at timestamp with time zone
);


--
-- Name: auth_contact; Type: TABLE; Schema: public;
--

CREATE TABLE auth_contact (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	stat integer DEFAULT 100 NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	session_at timestamp with time zone,
	username character varying(256) NOT NULL,
	password character varying(256)
);


CREATE TABLE auth_context (
	id varchar(26) not null default ulid_create() primary key,
	stat int,
	flag int,
	code varchar(256),
	name varchar(256)
);

--
-- Name: auth_context_ticket; Type: TABLE; Schema: public;
--

CREATE TABLE auth_context_ticket (
	id varchar(64) PRIMARY KEY,
	created_at timestamp with time zone not null default now(),
	expires_at timestamp with time zone not null default now() + '60 minutes',
	meta jsonb
);


CREATE TABLE auth_service (
	id varchar(26) NOT NULL DEFAULT ulid_create() PRIMARY KEY,
	company_id varchar(26) not null,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	stat int NOT NULL DEFAULT 100,
	flag int NOT NULL DEFAULT 0,
	code varchar(256),
	hash varchar(256),
	name varchar(256),
	context_list text
);

--
-- Name: auth_service_contact; Type: TABLE; Schema: public;
--

CREATE TABLE auth_service_contact (
	service_id varchar(26) not null,
	contact_id varchar(26) not null,
	created_at timestamp with time zone default now() not null,
	expires_at timestamp with time zone default (now() + '365 days'::interval) not null
);

--
-- Name: log_delta; Type: TABLE; Schema: public;
--

CREATE TABLE log_delta (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	ct timestamp with time zone DEFAULT now() NOT NULL,
	op varchar(8),
	tb character varying(64) NOT NULL,
	pk character varying(32) NOT NULL,
	v0 jsonb,
	v1 jsonb
);


--
-- Name: log_event; Type: TABLE; Schema: public;
--

CREATE TABLE log_event (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	ct timestamp with time zone DEFAULT now() NOT NULL,
	company_id character varying(26),
	contact_id character varying(26),
	code character varying(64) NOT NULL,
	link character varying(256),
	meta jsonb
);


-- CREATE TABLE company (
-- 	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
-- 	name text
-- );

-- CREATE TABLE contact (
-- 	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
-- 	created_at timestamp with time zone DEFAULT now() NOT NULL,
-- 	name text,
-- 	email text,
-- 	phone text
-- );

CREATE TABLE acl_service_object_action (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	service_id character varying(26) not null,
	obj character varying(256) not null,
	act character varying(256) not null
);


CREATE TABLE acl_company_contact_service_object_action (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	company_id character varying(26) not null,
	contact_id character varying(26) not null,
	service_id character varying(26) not null,
	obj character varying(256),
	act character varying(256),
	eft character varying(8)
);
