@echo off
schtasks /Create /F /SC MINUTE /MO 5 /TN "XXJ Stamina Recovery" /TR "D:\XXJserver\phpstudy_pro\WWW\cron\run_stamina_recovery.bat" /RU SYSTEM
echo.
echo Task "XXJ Stamina Recovery" da duoc tao. Chay moi 5 phut.
pause