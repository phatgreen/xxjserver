@echo off
REM Bam dup: xoa 5 giftcode vip cu, chia TANTHU 50 vien thanh 5 ma x 10 vien (sql/migrate_giftcode_tanthu2.php). Hoi truoc khi ghi.
cd /d D:\XXJserver\phpstudy_pro\WWW
set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php7.4.3nts\php.exe"
if not exist "%PHP%" set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe"
if not exist "%PHP%" (echo Khong tim thay php.exe trong phpstudy_pro\Extensions\php & pause & exit /b 1)
"%PHP%" sql\migrate_giftcode_tanthu2.php --dry-run
echo.
set /p OK=Ghi that cac thay doi tren? Go Y roi Enter, phim khac de thoat: 
if /i not "%OK%"=="Y" (echo Da huy, chua ghi gi. & pause & exit /b 0)
"%PHP%" sql\migrate_giftcode_tanthu2.php --apply
echo.
echo Xong. Xem lai o trang GM, tab Giftcode.
pause
