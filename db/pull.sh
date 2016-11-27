WGET="/usr/bin/wget"
if [ -s /tmp/index.google ];then
        echo "already running die"
exit;
else
	echo "Checking for internet";
fi

$WGET -q --tries=10 --timeout=15 http://www.google.com -O /tmp/index.google
# &> /dev/null
sleep 2

if [ ! -s /tmp/index.google ];then
	echo "could not update pods as no internet"
	rm /tmp/index.google
exit;
else
	echo "Pulling in new pod data";
	cd /var/www/poduptime/db
	php pull.php debug=1
 	touch last.data
	php backup.php
	rm /tmp/index.google
fi
