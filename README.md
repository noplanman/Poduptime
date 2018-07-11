# Poduptime

Poduptime is software to get live stats and data on listed Diaspora and other Pods that support nodeinfo output.

# To Install:

Environmental items you need (debian based system assumed):

OS Dependencies:
```
php7.2 php7.2-curl php7.2-pgsql php-geoip php7.2-cli php7.2-common php7.2-bcmath php7.2-json php7.2-readline php7.2-mbstring php7.2-xml php-cgi git curl postgresql postgresql-contrib dnsutils bind9 npm nodejs composer
```
GeoIP needs setup normally with a dat file

Yarn is a separate install: https://yarnpkg.com


Clone and setup:
```
git clone https://github.com/diasporg/Poduptime.git
cd Poduptime
yarn install
composer install
cp config.php.example config.php (all fields required)
```

If you need to setup your Postgresql/DB:
```
sudo adduser podupuser
sudo -u postgres bash -c "psql -c \"CREATE USER podupuser WITH PASSWORD 'MYpassword';\""
sudo -u postgres bash -c "psql -c \"CREATE DATABASE podupdb;\""
sudo -u postgres bash -c "psql -c \"GRANT ALL PRIVILEGES ON DATABASE podupdb TO podupuser;\""

# update your local line to allow md5 METHOD
sudo nano /etc/postgresql/vx.x/main/pg_hba.conf

# restart postgresql
```

Import database schema
```
psql -U podupuser podupdb < db/tables.sql
```

1. Edit `config.php` to add your DB and file settings
2. Touch add.log in location you configured in config.php
3. Create your backup folder
4. Add a pod and run `db/pull.sh init` 

# To Use:

run `db/pull.sh` manually or with cron to update your data  
run `db/pull.sh debug` to debug output  
run `db/pull.sh sqldebug` to debug sql  
run `db/pull.sh develop` to run without email alerts to end users  
run `db/pull.sh Check_System_Deleted` to re-check system deleted pods as needed  

# To Upgrade:
```
git pull
yarn install
composer install
psql -U podupuser podupdb < db/migrationXXX.sql (see db/version.md for proper migration versions)
```

# To Contribute

Something here or a link to the wiki on how to....

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/0515afa78df64b6385088246ee84acde)](https://www.codacy.com/app/diasporg/Poduptime?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=diasporg/Poduptime&amp;utm_campaign=Badge_Grade)


============================

Source for Diaspora Pod Uptime

  Poduptime is software to get live stats and data on listed Diaspora Pods.
  Copyright (C) 2011 David Morley

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see <https://www.gnu.org/licenses/>.
