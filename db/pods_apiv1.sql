DROP TABLE pods_apiv1;
CREATE TABLE pods_apiv1 AS SELECT * FROM pods;


ALTER TABLE pods_apiv1 RENAME COLUMN stats_apikey TO pingdomurl;
ALTER TABLE pods_apiv1 RENAME COLUMN service_xmpp TO xmpp;
ALTER TABLE pods_apiv1 RENAME COLUMN uptime_alltime TO uptimelast7;
ALTER TABLE pods_apiv1 RENAME COLUMN responsetime TO responsetimelast7;

ALTER TABLE pods_apiv1 RENAME COLUMN date_updated TO dateUpdated;
ALTER TABLE pods_apiv1 RENAME COLUMN date_laststats TO dateLaststats;
ALTER TABLE pods_apiv1 RENAME COLUMN date_created TO dateCreated;



ALTER TABLE pods_apiv1 ALTER COLUMN ipv6 TYPE text USING ipv6::text;
ALTER TABLE pods_apiv1 ALTER hidden DROP DEFAULT;
ALTER TABLE pods_apiv1 ALTER COLUMN hidden TYPE text USING hidden::text;
ALTER TABLE pods_apiv1 ALTER COLUMN secure TYPE text USING secure::text;
ALTER TABLE pods_apiv1 ALTER COLUMN signup TYPE text USING signup::text;

ALTER TABLE pods_apiv1 ADD Hgitdate text, ADD Hgitref text, ADD Hruntime text, ADD Hencoding text, ADD longversion text, ADD ptr text, ADD whois text, ADD postalcode text, ADD connection text, ADD pingdomlast text;
  
ALTER TABLE pods_apiv1 DROP podmin_statement, DROP sslexpire, DROP dnssec, DROP publickey, DROP podmin_notify;

UPDATE pods_apiv1 SET hgitdate = 'unsupported';
