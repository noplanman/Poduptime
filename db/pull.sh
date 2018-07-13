#!/usr/bin/env bash

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

FLAG_FILE="/tmp/poduptime.pulling"
HOUR=$(date +%H)
DAY=$(date +%d)
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

if [ "$HOUR" = 23 ] || [ "$1" = 'init' ]; then
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
  if [ "$DAY" = 23 ]  || [ "$1" = 'init' ]; then
    printf "%s" "Updating CA..."
    CACERT_FILE="$(php -r "include __DIR__ . '/../config.php'; echo \$cafullpath;")"
    if curl -Lss https://curl.haxx.se/ca/cacert.pem -o "$CACERT_FILE"; then
      echo "$HAPPY"
    else
      echo "$SAD"
    fi
    printf "%s" "Updating GeoIP2 DB..."
    GEODB_FILE="$(php -r "include __DIR__ . '/../config.php'; echo \$geoip2db;")"
    if funzip <(curl -Lss http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz) > "$GEODB_FILE"; then
      echo "$HAPPY"
    else
      echo "$SAD"
    fi
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
