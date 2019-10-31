--
-- Default Scopes
--

insert into auth_scope (id, code, name) VALUES (ulid_create(), 'ops', 'OPS');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'contact', 'Contact Profile');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'company', 'Company Profile');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'cic', 'Communications Information Center');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'crm', 'CRM Tools');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'dump', 'Data Dumper Tools');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'lab', 'Laboratory Information Portal');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'menu', 'On-line Menu and Ordering');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'p2p', 'Peer-to-Peer Communications Tools');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'pipe', 'CRE Data Pipe');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'pos', 'Point of Sale');

insert into auth_scope (id, code, name) VALUES (ulid_create(), 'aux', 'Communications Information Center - AUX, Legacy Name');
insert into auth_scope (id, code, name) VALUES (ulid_create(), 'helpdesk', 'Communications Information Center - HelpDesk, Legacy Name');
