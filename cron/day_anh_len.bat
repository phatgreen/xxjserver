@echo off
REM Bam dup: day thu muc anh moi (images\newitem) len GitHub de Claude dung.
cd /d D:\XXJserver\phpstudy_pro\WWW
git pull --ff-only
git add images\newitem
git commit -m "Them anh vat pham moi images/newitem"
git push
if errorlevel 1 (echo Day len that bai - xem loi o tren. & pause & exit /b 1)
echo.
echo Xong. Bao lai cho Claude de cap nhat anh vao game.
pause
