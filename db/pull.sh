#!/usr/bin/env sh

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

FLAG_FILE="/tmp/poduptime.pulling"
HOUR=`date +%H`
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

if [ "$HOUR" = 23 ]; then
  printf "%s" "Pulling in master versions..."
  if php pull-masterversions.php; then
    echo "$HAPPY"
  else
    echo "$SAD"
  fi
  printf "%s" "Updating Monthy Stats Table..."
  if php monthly_stats.php; then
    echo "$HAPPY"
  else
    echo "$SAD"
  fi
  printf "%s" "Crawling the federation..."
  if php podcrawler.php; then
    echo "$HAPPY"
  else
    echo "$SAD"
  fi
  printf "%s" "Updating CA..."
  if wget -q https://curl.haxx.se/ca/cacert.pem -O ../cacert.pem; then
    echo "$HAPPY"
  else
    echo "$SAD"
  fi
fi

echo "Pulling in new pod data...";
php pull.php $@
touch last.data
echo "Finished pull!"

echo "Backing up..."
php backup.php
echo

rm "$FLAG_FILE"
