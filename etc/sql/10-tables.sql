
CREATE TABLE auth_scope (
	id varchar(26) PRIMARY KEY,
	stat int not null default 200,
	flag int not null default 0,
	code varchar(64) not null,
	name varchar(256) not null
);

