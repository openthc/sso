--
-- Default Scopes
--

INSERT INTO auth_company (id, name) VALUES ('018NY6XC00C0MPANY000000000', '-system-');
INSERT INTO auth_contact (id, username, password) values ('018NY6XC00C0NTACT000000000', '-system-', ('LOCK:' || md5(random()::text)::text));

INSERT INTO auth_company_contact (company_id, contact_id) VALUES ('018NY6XC00C0MPANY000000000', '018NY6XC00C0NTACT000000000');

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

INSERT INTO auth_service (company_id, name, code, hash) VALUES ('018NY6XC00C0MPANY000000000', 'sso.openthc.local', 'sso.openthc.local', 'sso.openthc.local-secret');
INSERT INTO auth_service (company_id, name, code, hash) VALUES ('018NY6XC00C0MPANY000000000', 'cre.openthc.local', 'cre.openthc.local', 'cre.openthc.local-secret');
INSERT INTO auth_service (company_id, name, code, hash) VALUES ('018NY6XC00C0MPANY000000000', 'app.openthc.local', 'app.openthc.local', 'app.openthc.local-secret');
INSERT INTO auth_service (company_id, name, code, hash) VALUES ('018NY6XC00C0MPANY000000000', 'lab.openthc.local', 'lab.openthc.local', 'lab.openthc.local-secret');
INSERT INTO auth_service (company_id, name, code, hash) VALUES ('018NY6XC00C0MPANY000000000', 'pos.openthc.local', 'pos.openthc.local', 'pos.openthc.local-secret');
