CREATE TABLE monthlystats (
 id serial8 UNIQUE PRIMARY KEY,
 total_users int,
 total_posts int,
 total_comments int,
 total_pods int,
 total_uptime int,
 date_checked timestamp DEFAULT current_timestamp
);
