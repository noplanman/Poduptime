/* http://pointbeing.net/weblog/2008/03/mysql-versus-postgresql-adding-an-auto-increment-column-to-a-table.html */
CREATE SEQUENCE apikeys_id_seq;
ALTER TABLE apikeys ADD id serial8 UNIQUE PRIMARY KEY;
ALTER TABLE apikeys ALTER COLUMN id SET DEFAULT NEXTVAL('apikeys_id_seq');
UPDATE apikeys SET id = NEXTVAL('apikeys_id_seq');

CREATE SEQUENCE checks_id_seq;
ALTER TABLE checks ADD id serial8 UNIQUE PRIMARY KEY;
ALTER TABLE checks ALTER COLUMN id SET DEFAULT NEXTVAL('checks_id_seq');
UPDATE checks SET id = NEXTVAL('checks_id_seq');

CREATE SEQUENCE clicks_id_seq;
ALTER TABLE clicks ADD id serial8 UNIQUE PRIMARY KEY;
ALTER TABLE clicks ALTER COLUMN id SET DEFAULT NEXTVAL('clicks_id_seq');
UPDATE clicks SET id = NEXTVAL('clicks_id_seq');

CREATE SEQUENCE masterversions_id_seq;
ALTER TABLE masterversions ADD id serial8 UNIQUE PRIMARY KEY;
ALTER TABLE masterversions ALTER COLUMN id SET DEFAULT NEXTVAL('masterversions_id_seq');
UPDATE masterversions SET id = NEXTVAL('masterversions_id_seq');
