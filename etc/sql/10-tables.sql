--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';
SET default_with_oids = false;


--
-- Name: auth_company; Type: TABLE; Schema: public;
--

CREATE TABLE auth_company (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	created_at timestamp without time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	stat integer DEFAULT 100 NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	name character varying(256),
	cre character varying(32),
	cre_meta jsonb
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
-- Name: auth_company_company; Type: TABLE; Schema: public;
--

CREATE TABLE auth_company_company (
	company_id_prime character varying(32) NOT NULL,
	company_id_child character varying(32) NOT NULL
);


--
-- Name: auth_company_contact; Type: TABLE; Schema: public;
--

CREATE TABLE auth_company_contact (
	company_id character varying(32) NOT NULL,
	contact_id character varying(32) NOT NULL,
	flag integer DEFAULT 0 NOT NULL,
	created_at timestamp with time zone DEFAULT now() NOT NULL,
	updated_at timestamp with time zone DEFAULT now() NOT NULL,
	deleted_at timestamp with time zone,
	session_at timestamp with time zone
);


--
-- Name: auth_hash; Type: TABLE; Schema: public;
--

CREATE TABLE auth_context_secret (
	id character varying(32) DEFAULT ulid_create() NOT NULL PRIMARY KEY,
	code character varying(128),
	meta jsonb
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
