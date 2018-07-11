ALTER TABLE masterversions ADD devlastcommit timestamp, ADD releasedate timestamp;
ALTER TABLE pods ADD daysmonitored int, ADD countryname text, ADD detectedlanguage text;
ALTER TABLE pods DROP COLUMN adminrating, DROP COLUMN hidden, DROP COLUMN secure;
UPDATE pods SET status=1 WHERE status IS NULL;
ALTER TABLE pods ALTER status SET DEFAULT 1;
ALTER TABLE rating_comments DROP COLUMN admin;
ALTER TABLE rating_comments RENAME TO ratingcomments;
