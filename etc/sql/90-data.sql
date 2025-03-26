--
-- Default Scopes
--

\c openthc_auth

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
-- SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

INSERT INTO auth_company (id, name) VALUES ('010PENTHCXC0MPANY00000R00T', '-system-');
INSERT INTO auth_contact (id, username, password) values ('010PENTHCXC0NTACT000000000', '-system-', ('LOCK:' || md5(random()::text)::text));

INSERT INTO auth_company_contact (company_id, contact_id) VALUES ('010PENTHCXC0MPANY00000R00T', '010PENTHCXC0NTACT000000000');

INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'ops', 'OPS');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'b2b', 'Business to Business Sales Platform');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'company', 'Company Profile');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'contact', 'Contact Profile');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'crm', 'CRM Tools');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'dump', 'Data Dumper Tools');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'lab', 'Laboratory Information Portal');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'menu', 'On-line Menu and Ordering');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'p2p', 'Peer-to-Peer Communications Tools');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'pipe', 'CRE Data Pipe');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'pos', 'Point of Sale');

INSERT INTO auth_service (id, company_id, name, code, hash) VALUES ('010PENTHCXSERV1CE000000SS0', '010PENTHCXC0MPANY00000R00T', 'sso.openthc.local', 'sso.openthc.local', 'sso.openthc.local-secret');
INSERT INTO auth_service (id, company_id, name, code, hash) VALUES ('010PENTHCXSERV1CE000000CRE', '010PENTHCXC0MPANY00000R00T', 'cre.openthc.local', 'cre.openthc.local', 'cre.openthc.local-secret');
INSERT INTO auth_service (id, company_id, name, code, hash) VALUES ('010PENTHCXSERV1CE000000APP', '010PENTHCXC0MPANY00000R00T', 'app.openthc.local', 'app.openthc.local', 'app.openthc.local-secret');
INSERT INTO auth_service (id, company_id, name, code, hash) VALUES ('010PENTHCXSERV1CE000000LAB', '010PENTHCXC0MPANY00000R00T', 'lab.openthc.local', 'lab.openthc.local', 'lab.openthc.local-secret');
INSERT INTO auth_service (id, company_id, name, code, hash) VALUES ('010PENTHCXSERV1CE000000P0S', '010PENTHCXC0MPANY00000R00T', 'pos.openthc.local', 'pos.openthc.local', 'pos.openthc.local-secret');
