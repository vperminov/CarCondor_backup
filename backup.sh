#!/bin/bash
PATH=/etc:/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin

while [ -n "$1" ]; do
  case "$1" in
  -u) DBUSER="$2" ;;
  -p) DBPASS="$2" ;;
  -d) DBNAME="$2" ;;
  -f) FOLDER="$2" ;;

  esac
  shift
done

DATE=$(date +%d.%m.%Y:%H.%M.%S)
FILENAME=db-backup-$DATE.sql
FILEPATH=$FOLDER/$FILENAME

ARHIVED=$FOLDER/db-backup-$DATE.tar.gz

mysqldump --force --opt --add-drop-table --user=$DBUSER --password=$DBPASS --databases $DBNAME >$FILEPATH

tar -czvf $ARHIVED -C $FOLDER $FILENAME

rm $FILEPATH
echo "Backup done, the file name is: "$ARHIVED
