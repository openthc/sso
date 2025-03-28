--
-- PostgreSQL database dump
--

\c openthc_auth

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET search_path TO public;
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: auth_company; Type: TABLE; Schema: public;
--

CREATE TABLE auth_company (
	id character varying(26) NOT NULL PRIMARY KEY,
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

ALTER TABLE public.auth_company OWNER TO openthc_auth;


-- Company Relationships
CREATE TABLE auth_company_company (
	company_id_prime character varying(26) NOT NULL,
	company_id_child character varying(26) NOT NULL
);

ALTER TABLE public.auth_company_company OWNER TO openthc_auth;

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

ALTER TABLE public.auth_company_contact OWNER TO openthc_auth;

--
-- Name: auth_contact; Type: TABLE; Schema: public;
--

CREATE TABLE auth_contact (
	id character varying(26) NOT NULL PRIMARY KEY,
	stat integer DEFAULT 100 NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	session_at timestamp with time zone,
	username character varying(256) NOT NULL,
	password character varying(256),
	iso3166 character varying(8),
	tz character varying(64)
);

ALTER TABLE public.auth_contact OWNER TO openthc_auth;

CREATE TABLE auth_context (
	id varchar(26) not null default ulid_create() primary key,
	stat int,
	flag int,
	code varchar(256),
	name varchar(256)
);

ALTER TABLE public.auth_context OWNER TO openthc_auth;


CREATE TABLE public.auth_context_ticket (
	id character varying(64) NOT NULL,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	expires_at timestamp with time zone DEFAULT (now() + '01:00:00'::interval) NOT NULL,
	meta jsonb
);
ALTER TABLE public.auth_context_ticket OWNER TO openthc_auth;

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
ALTER TABLE public.auth_service OWNER TO openthc_auth;

--
-- Name: auth_service_contact; Type: TABLE; Schema: public;
--

CREATE TABLE auth_service_contact (
	service_id varchar(26) not null,
	contact_id varchar(26) not null,
	created_at timestamp with time zone default now() not null,
	expires_at timestamp with time zone default (now() + '365 days'::interval) not null
);
ALTER TABLE public.auth_service_contact OWNER TO openthc_auth;

CREATE TABLE auth_service_keypair (
	id varchar(26) not null,
	service_id varchar(26) not null,
	stat int not null default 0,
	created_at timestamp with time zone not null default now(),
	updated_at timestamp with time zone,
	deleted_at timestamp with time zone,
	expires_at timestamp with time zone,
	pk text,
	sk text,
	flag jsonb
);
ALTER TABLE public.auth_service_keypair OWNER TO openthc_auth;

--
-- Name: iso3166; Type: TABLE; Schema: public;
--

CREATE TABLE iso3166 (
	id varchar(32) not null primary key,
	code2 varchar(2),
	code3 varchar(3),
	type text,
	name text,
	meta jsonb
);
ALTER TABLE public.iso3166 OWNER TO openthc_auth;

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
ALTER TABLE public.log_delta OWNER TO openthc_auth;

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
ALTER TABLE public.log_event OWNER TO openthc_auth;

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
ALTER TABLE public.acl_service_object_action OWNER TO openthc_auth;

CREATE TABLE acl_company_contact_service_object_action (
	id character varying(26) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	company_id character varying(26) not null,
	contact_id character varying(26) not null,
	service_id character varying(26) not null,
	obj character varying(256),
	act character varying(256),
	eft character varying(8)
);
ALTER TABLE public.acl_company_contact_service_object_action OWNER TO openthc_auth;
