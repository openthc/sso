--
-- Name: pg_ulid; Type: EXTENSION; Schema: -; Owner:
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

CREATE EXTENSION IF NOT EXISTS pg_ulid WITH SCHEMA public;
COMMENT ON EXTENSION pg_ulid IS 'ULID datatype and functions';
