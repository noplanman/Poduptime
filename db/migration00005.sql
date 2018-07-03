ALTER TABLE masterversions ADD devlastcommit timestamp;
ALTER TABLE masterversions ADD releasedate timestamp;
ALTER TABLE pods ADD daysmonitored int, ADD countryname text;
