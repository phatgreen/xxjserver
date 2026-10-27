#!/bin/bash
# Khởi động MariaDB + server PHP thử 127.0.0.1:8091 (bản sao /tmp/xxjrun). MariaDB hay tự tắt — chạy lại script này.
DIR=$(cd "$(dirname "$0")" && pwd)
if ! mysqladmin -uroot status >/dev/null 2>&1; then
  (nohup mysqld_safe --user=mysql >/tmp/mysqld.log 2>&1 &)
  for i in $(seq 1 25); do mysqladmin -uroot status >/dev/null 2>&1 && break; sleep 1; done
fi
mysql -uroot -e "SET GLOBAL innodb_strict_mode=0; SET GLOBAL sql_mode='';"
bash "$DIR/sync_run.sh"
if ! curl -s -o /dev/null http://127.0.0.1:8091/; then
  (cd /tmp/xxjrun && nohup php -d display_errors=1 -d error_reporting=32759 -S 127.0.0.1:8091 -t /tmp/xxjrun >/tmp/phpserver.log 2>&1 &)
  sleep 1
fi
mysql -uroot game -e "select count(*) maps from mid"
