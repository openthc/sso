--
-- Default Scopes
--

INSERT INTO auth_company (id, name) VALUES ('018NY6XC00CP00000000000000', '-system-');
INSERT INTO auth_contact (id, company_id, username, password) values ('018NY6XC00CT00000000000000', '018NY6XC00CP00000000000000', 'root@openthc.com', ('LOCK:' || md5(random()::text)::text));

INSERT INTO auth_company_contact (company_id, contact_id) VALUES ('018NY6XC00CP00000000000000', '018NY6XC00CT00000000000000');

INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'ops', 'OPS');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'cic', 'Communications Information Center');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'company', 'Company Profile');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'contact', 'Contact Profile');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'crm', 'CRM Tools');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'dump', 'Data Dumper Tools');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'lab', 'Laboratory Information Portal');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'menu', 'On-line Menu and Ordering');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'p2p', 'Peer-to-Peer Communications Tools');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'pipe', 'CRE Data Pipe');
INSERT INTO auth_context (id, code, name) VALUES (ulid_create(), 'pos', 'Point of Sale');
