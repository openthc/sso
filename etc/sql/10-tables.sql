--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;

--
-- Name: auth_company; Type: TABLE; Schema: public;
--

CREATE TABLE auth_company (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
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
	company_id_prime character varying(32) NOT NULL,
	company_id_child character varying(32) NOT NULL
);

CREATE TABLE auth_company_contact (
	company_id character varying(32) NOT NULL,
	contact_id character varying(32) NOT NULL,
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
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	stat integer DEFAULT 100 NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	session_at timestamp with time zone,
	username character varying(256) NOT NULL,
	password character varying(256)
);

--
-- Name: auth_company_contact; Type: TABLE; Schema: public;
--



CREATE TABLE auth_context (
	id varchar(32) not null default ulid_create() primary key,
	code varchar(256)
);

--
-- Name: auth_hash; Type: TABLE; Schema: public;
--

CREATE TABLE auth_context_secret (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	code character varying(128),
	meta jsonb
);



CREATE TABLE auth_program_contact (
	auth_program_id varchar(32) not null,
	auth_contact_id varchar(32) not null,
	created_at timestamp with time zone default now() not null,
	expires_at timestamp with time zone default (now() + '365 days'::interval) not null
);

--
-- Name: log_delta; Type: TABLE; Schema: public;
--

CREATE TABLE log_delta (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	ct timestamp with time zone DEFAULT now() NOT NULL,
	op smallint,
	tb character varying(64) NOT NULL,
	pk character varying(32) NOT NULL,
	v0 jsonb,
	v1 jsonb
);


--
-- Name: log_event; Type: TABLE; Schema: public;
--

CREATE TABLE log_event (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	ct timestamp with time zone DEFAULT now() NOT NULL,
	company_id character varying(32),
	contact_id character varying(32),
	code character varying(64) NOT NULL,
	link character varying(256),
	meta jsonb
);


CREATE TABLE company (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	name text
);

CREATE TABLE contact (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	name text,
	email text,
	phone text
);
