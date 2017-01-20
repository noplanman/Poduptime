ALTER TABLE pods ADD terms text, ADD sslexpire timestamp, ADD uptime_custom text, ADD dnssec boolean, ADD masterversion text, ADD shortversion text;
ALTER TABLE pods DROP Hgitdate, DROP Hgitref, DROP Hruntime, DROP Hencoding, DROP longversion, DROP ptr, DROP whois, DROP postalcode, DROP connection, DROP pingdomlast;

ALTER TABLE pods RENAME COLUMN pingdomurl TO stats_apikey;
ALTER TABLE pods RENAME COLUMN xmpp TO service_xmpp;
ALTER TABLE pods RENAME COLUMN uptimelast7 TO uptime_alltime;
ALTER TABLE pods RENAME COLUMN responsetimelast7 TO responsetime;

ALTER TABLE pods RENAME COLUMN dateUpdated TO date_updated;
ALTER TABLE pods RENAME COLUMN dateLaststats TO date_laststats;
ALTER TABLE pods RENAME COLUMN dateCreated TO date_created;

ALTER TABLE rating_comments RENAME COLUMN date TO date_created;

ALTER TABLE pods ALTER COLUMN ipv6 TYPE boolean USING ipv6::boolean;
ALTER TABLE pods ALTER hidden DROP DEFAULT;
ALTER TABLE pods ALTER COLUMN hidden TYPE boolean USING hidden::boolean;
ALTER TABLE pods ALTER COLUMN secure TYPE boolean USING secure::boolean;
ALTER TABLE pods ALTER COLUMN signup TYPE boolean USING signup::boolean;



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
 ttl numeric(8,6),
 date_checked timestamp DEFAULT current_timestamp
);
