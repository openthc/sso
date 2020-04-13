--
-- Default Scopes
--

INSERT INTO auth_company (id, name) VALUES ('019KAGVSC05RHV4QAS76VPV6J7', '-openthc-system-');
INSERT INTO auth_contact (id, username, password) values ('019KAGVX9MQRRV9H0G9N3Q9FMC', 'root@openthc.dev', ('LOCK:' || md5(random()::text)::text));
INSERT INTO auth_company_contact (company_id, contact_id) VALUES ('019KAGVSC05RHV4QAS76VPV6J7', '019KAGVX9MQRRV9H0G9N3Q9FMC');

INSERT INTO company (id, name) VALUES ('019KAGVSC05RHV4QAS76VPV6J7', '-openthc-system-');
INSERT INTO contact (id, name, email, phone) VALUES ('019KAGVX9MQRRV9H0G9N3Q9FMC', '-openthc-system-', 'root@openthc.dev', '+18559769333');


insert into auth_scope (id, code, name) VALUES (ulid_create(), 'ops', 'OPS');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'cic', 'Communications Information Center');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'company', 'Company Profile');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'contact', 'Contact Profile');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'crm', 'CRM Tools');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'dump', 'Data Dumper Tools');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'lab', 'Laboratory Information Portal');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'menu', 'On-line Menu and Ordering');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'p2p', 'Peer-to-Peer Communications Tools');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'pipe', 'CRE Data Pipe');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'pos', 'Point of Sale');
