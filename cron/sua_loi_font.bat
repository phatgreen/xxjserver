@echo off
REM Bam dup: quet chu loi font trong DB (sql/fix_mojibake.php), sao luu DB roi moi sua. Hoi truoc khi ghi.
cd /d D:\XXJserver\phpstudy_pro\WWW
set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php7.4.3nts\php.exe"
if not exist "%PHP%" set "PHP=D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe"
if not exist "%PHP%" (echo Khong tim thay php.exe trong phpstudy_pro\Extensions\php & pause & exit /b 1)

echo === Buoc 1: quet thu (khong ghi DB) ===
"%PHP%" sql\fix_mojibake.php --dry-run
if errorlevel 1 (echo Loi khi quet. & pause & exit /b 1)
"%PHP%" sql\fix_mojibake.php --dry-run | find "DB sach" >nul
if not errorlevel 1 (echo. & echo Khong can sua gi. & pause & exit /b 0)

echo.
set /p OK=Sua cac dong tren? Go Y roi Enter de sua, phim khac de thoat: 
if /i not "%OK%"=="Y" (echo Da huy, chua ghi gi. & pause & exit /b 0)

echo.
echo === Buoc 2: sao luu DB ===
set "DUMP="
for /d %%D in (D:\XXJserver\phpstudy_pro\Extensions\MySQL*) do if exist "%%D\bin\mysqldump.exe" set "DUMP=%%D\bin\mysqldump.exe"
if not defined DUMP (echo Khong tim thay mysqldump.exe trong phpstudy_pro\Extensions\MySQL* - dung lai, chua sua. & pause & exit /b 1)
set "BK=D:\XXJserver\backup_truoc_sua_font_%RANDOM%.sql"
echo Nhap mat khau MySQL root khi duoc hoi:
"%DUMP%" -uroot -p --default-character-set=utf8mb4 game > "%BK%"
if errorlevel 1 (echo Sao luu that bai - dung lai, chua sua. & del "%BK%" 2>nul & pause & exit /b 1)
echo Da sao luu: %BK%

echo.
echo === Buoc 3: sua ===
"%PHP%" sql\fix_mojibake.php --apply
echo.
echo Xong. Mo game va bam Ctrl+F5.
pause
