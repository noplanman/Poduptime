WGET="/usr/bin/wget"
if [ -s /tmp/index.google ];then
        echo "already running"
exit;
else
echo "Checking for internet";
fi

$WGET -q --tries=10 --timeout=15 http://www.google.com -O /tmp/index.google &> /dev/null
if [ ! -s /tmp/index.google ];then
	echo "could not update pods as no internet"
rm /tmp/index.google
else
echo "Pulling in new pod data";
cd /var/www/podup/db
php5 pull.php
touch last.data
php5 backup.php
rm /tmp/index.google
fi
