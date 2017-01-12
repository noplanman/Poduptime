ALTER TABLE pods ADD terms text, ADD sslexpire timestamp, ADD uptime_custom text, ADD dnssec boolean, ADD masterversion text, ADD shortversion text;
ALTER TABLE pods DROP Hgitdate, DROP Hgitref, DROP Hruntime, DROP Hencoding, DROP longversion, DROP ptr, DROP whois, DROP postalcode, DROP connection, DROP pingdomlast;

ALTER TABLE pods RENAME COLUMN pingdomurl TO statsurl;
ALTER TABLE pods RENAME COLUMN xmpp TO service_xmpp;
ALTER TABLE pods RENAME COLUMN uptimelast7 TO uptime_alltime;
ALTER TABLE pods RENAME COLUMN responsetimelast7 TO responsetime;

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
 dateCreated timestamp DEFAULT current_timestamp
);

CREATE TABLE clicks (
 domain text,
 manualclick int,
 autoclick int,
 dateClicked timestamp DEFAULT current_timestamp
);

CREATE TABLE checks (
 domain text,
 online boolean,
 error text,
 dateChecked timestamp DEFAULT current_timestamp
);
