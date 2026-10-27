@echo off
REM Chay ngam: tu dong lay code moi (khong hien cua so, khong ghi de file dang sua)
cd /d D:\XXJserver\phpstudy_pro\WWW
git pull --ff-only >> cron\capnhat_code.log 2>&1
