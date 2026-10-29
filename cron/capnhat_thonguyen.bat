@echo off
REM Bam dup: lay code moi + kiem tra Tuoi va Tho Nguyen (sql/check_thonguyen.php: tao bang player_thonguyen, kiem config + 3 dan). Khong sua du lieu nhan vat.
cd /d D:\XXJserver\phpstudy_pro\WWW
echo [1/2] Lay code moi tu GitHub...
git pull --ff-only
if errorlevel 1 (echo Lay code that bai - xem loi o tren. & pause & exit /b 1)
set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php7.4.3nts\php.exe"
if not exist "%PHP%" set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe"
if not exist "%PHP%" (echo Khong tim thay php.exe trong phpstudy_pro\Extensions\php & pause & exit /b 1)
echo.
echo [2/2] Kiem tra Tho Nguyen...
"%PHP%" sql\check_thonguyen.php
echo.
echo Mo game va bam Ctrl+F5. Trang ca nhan co thanh "Tuoi / Tho nguyen".
pause
