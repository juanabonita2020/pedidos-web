#!/bin/sh

cd ../logs
find . -type f -name '*.log' -mtime +7 -print > _files
for i in $(cat _files); do
	echo $i
	j=_backups/$(dirname $i)
	mkdir -p $j
	mv $i $j
done
tar -jcf $(date +%Y%m%d%H%I%S).tbz _backups
rm -drf _backups
rm -f _files
