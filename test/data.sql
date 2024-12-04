/**
 * Create a Base Test Contact, Company Records
 *
 * Assumes that dir/test/data.sql is already loaded
 */

\c openthc_auth

INSERT INTO auth_contact (id, username, stat, password)
VALUES ('010PENTHC0TEST0C0NTACT000A', 'test+a@openthc.example', 200, '$2y$10$spd6vxPBRMUZ2bm65EyCkuOyz0cvWmqXXEGgSWWIwgfyrmOxCR83m')
ON CONFLICT (id) DO UPDATE
	SET password = '$2y$10$spd6vxPBRMUZ2bm65EyCkuOyz0cvWmqXXEGgSWWIwgfyrmOxCR83m';

INSERT INTO auth_contact (id, username, stat, password)
VALUES ('010PENTHC0TESTC0NTACT0000B', 'test+b@openthc.example', 200, '$2y$10$spd6vxPBRMUZ2bm65EyCkuOyz0cvWmqXXEGgSWWIwgfyrmOxCR83m')
ON CONFLICT DO UPDATE
        SET password = '$2y$10$spd6vxPBRMUZ2bm65EyCkuOyz0cvWmqXXEGgSWWIwgfyrmOxCR83m';

INSERT INTO auth_contact (id, username, stat, password)
VALUES ('010PENTHC0TESTC0NTACT0000C', 'test+c@openthc.example', 100, '$2y$10$spd6vxPBRMUZ2bm65EyCkuOyz0cvWmqXXEGgSWWIwgfyrmOxCR83m')
ON CONFLICT DO UPDATE
        SET password = '$2y$10$spd6vxPBRMUZ2bm65EyCkuOyz0cvWmqXXEGgSWWIwgfyrmOxCR83m';

INSERT INTO auth_company (id, name)
VALUES ('010PENTHC0TEST0C0MPANY000A', 'TEST COMPANY A')
ON CONFLICT (id) DO UPDATE
	SET name = 'TEST COMPANY A';

INSERT INTO auth_company (id, name)
VALUES ('010PENTHC0TESTC0MPANY0000B', 'TEST COMPANY B')
ON CONFLICT (id) DO UPDATE
	SET name = 'TEST COMPANY B';


\c openthc_auth

UPDATE auth_company SET cre = 'openthc' WHERE id = '01H296MD1X9AC1EV8N907TBZ75';

INSERT INTO auth_company_contact (contact_id, company_id) VALUES ('010PENTHC0TEST0C0NTACT000A', '010PENTHC0TEST0C0MPANY000A');
INSERT INTO auth_company_contact (contact_id, company_id) VALUES ('010PENTHC0TESTC0NTACT0000B', '010PENTHC0TESTC0MPANY0000B');
INSERT INTO auth_company_contact (contact_id, company_id) VALUES ('010PENTHC0TESTC0NTACT0000C', '010PENTHC0TESTC0MPANY0000C');

