SCRIPT_DIR="$( cd "$( dirname "$0" )" && pwd )"
TIME=`date +%k`

if [ -s /tmp/index.google ];then
  echo "already running die"
exit;
else
  echo "Checking for internet";
fi

wget -q --tries=2 --timeout=15 http://www.google.com -O /tmp/index.google
sleep 2

if [ ! -s /tmp/index.google ];then
  echo "could not update pods as no internet"
  rm /tmp/index.google
exit;
else
  cd "$SCRIPT_DIR"
  if [ $TIME = 6 ];then
    echo "Pulling in master versions";
    php pull-masterversions.php
  fi
  echo "Pulling in new pod data";
  php pull.php
  touch last.data
  php backup.php
  rm /tmp/index.google
fi
