WGET="/usr/bin/wget"

$WGET -q --tries=10 --timeout=5 http://www.google.com -O /tmp/index.google &> /dev/null
if [ ! -s /tmp/index.google ];then
	echo "could not update pods as no internet"
else
cd /var/www/podup/db
php5 pull.php
touch last.data
fi
php5 backup.php
