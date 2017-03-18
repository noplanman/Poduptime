#!/usr/bin/env sh

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

FLAG_FILE="/tmp/poduptime.pulling"
HOUR=`date +%k`
HAPPY=" :-)"
SAD=" :-("

if [ -f "$FLAG_FILE" ]; then
  echo "Already running pull"
  exit 1
fi
touch "$FLAG_FILE"

# https://stackoverflow.com/a/26820300
printf "%s" "Checking for internet..."
if ! wget -q --spider --tries=2 --timeout=15 https://www.google.com; then
  echo "$SAD"
  echo "Could not update pods as no internet"
  rm "$FLAG_FILE"
  exit 1
fi
echo "$HAPPY"

if [ "$HOUR" = 6 ]; then
  echo "Pulling in master versions...";
  php pull-masterversions.php
  echo

  printf "%s" "Updating CA..."
  if wget -q https://curl.haxx.se/ca/cacert.pem -O ../cacert.pem; then
    echo "$HAPPY"
  else
    echo "$SAD"
  fi
fi

echo "Pulling in new pod data...";
php pull.php $1
touch last.data
echo "Finished pull!"

echo "Backing up..."
php backup.php
echo

rm "$FLAG_FILE"
