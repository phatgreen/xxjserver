@echo off
rem KT-0: dung PHP 7.4 (64-bit) neu da cai, chua cai thi dung PHP 5.6 nhu cu (docs/DEPLOY_KT0_PHP74.md)
set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php7.4.3nts\php.exe"
if not exist "%PHP%" set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe"
cd /d D:\XXJserver\phpstudy_pro\WWW
"%PHP%" D:\XXJserver\phpstudy_pro\WWW\cron\stamina_recovery.php
