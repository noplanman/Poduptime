CREATE INDEX idx_clicks ON clicks (domain);
UPDATE pods SET status=0 WHERE status = 'Down';
UPDATE pods SET status=1 WHERE status = 'Up';
/*
0 = Down
1 = Up
2 = Recheck
3 = Paused
4 = System Deleted
5 = User Deleted
*/
ALTER TABLE pods ALTER COLUMN status TYPE smallint USING status::smallint;

