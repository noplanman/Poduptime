ALTER TABLE masterversions ADD devlastcommit timestamp;
ALTER TABLE masterversions ADD releasedate timestamp;
ALTER TABLE pods ADD daysmonitored int, ADD countryname text;
ALTER TABLE rating_comments RENAME TO ratingcomments;
ALTER TABLE pods ADD detectedlanguage text;
