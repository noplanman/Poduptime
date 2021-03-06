ALTER TABLE pods ADD podmin_statement text, ADD sslexpire timestamp, ADD dnssec boolean, ADD masterversion text, ADD shortversion text, ADD publickey text, ADD podmin_notify boolean;
ALTER TABLE pods DROP Hgitdate, DROP Hgitref, DROP Hruntime, DROP Hencoding, DROP longversion, DROP ptr, DROP whois, DROP postalcode, DROP connection, DROP pingdomlast;

ALTER TABLE pods RENAME COLUMN pingdomurl TO stats_apikey;
ALTER TABLE pods RENAME COLUMN xmpp TO service_xmpp;
ALTER TABLE pods RENAME COLUMN uptimelast7 TO uptime_alltime;
ALTER TABLE pods RENAME COLUMN responsetimelast7 TO latency;

ALTER TABLE pods RENAME COLUMN dateUpdated TO date_updated;
ALTER TABLE pods RENAME COLUMN dateLaststats TO date_laststats;
ALTER TABLE pods RENAME COLUMN dateCreated TO date_created;

ALTER TABLE rating_comments RENAME COLUMN date TO date_created;

ALTER TABLE pods ALTER COLUMN ipv6 TYPE boolean USING ipv6::boolean;
ALTER TABLE pods ALTER hidden DROP DEFAULT;
ALTER TABLE pods ALTER COLUMN hidden TYPE boolean USING hidden::boolean;
ALTER TABLE pods ALTER COLUMN secure TYPE boolean USING secure::boolean;
ALTER TABLE pods ALTER COLUMN signup TYPE boolean USING signup::boolean;
ALTER TABLE pods ALTER COLUMN latency TYPE smallint USING latency::int;

ALTER TABLE pods ALTER weight SET DEFAULT 10;
ALTER TABLE pods ALTER score SET DEFAULT 50;
ALTER TABLE pods ALTER adminrating SET DEFAULT 0;
ALTER TABLE pods ALTER userrating SET DEFAULT 0;
ALTER TABLE pods ALTER weightedscore SET DEFAULT 0;

DROP TABLE users;

CREATE TABLE apikeys (
 key text,
 email text,
 usage int,
 date_created timestamp DEFAULT current_timestamp
);

CREATE TABLE clicks (
 domain text,
 manualclick int,
 autoclick int,
 date_clicked timestamp DEFAULT current_timestamp
);

CREATE TABLE checks (
 domain text,
 online boolean,
 error text,
 latency numeric(8,6),
 total_users int,
 local_posts int,
 comment_counts int,
 shortversion text,
 date_checked timestamp DEFAULT current_timestamp
);

CREATE TABLE masterversions (
 software text,
 version text,
 date_checked timestamp DEFAULT current_timestamp
);
