@echo off
schtasks /Create /F /SC MINUTE /MO 2 /TN "XXJ Auto Pull Code" /TR "D:\XXJserver\phpstudy_pro\WWW\cron\run_capnhat_code.bat"
echo.
echo Task "XXJ Auto Pull Code" da duoc tao. Tu lay code moi moi 2 phut.
echo Muon tat: schtasks /Delete /TN "XXJ Auto Pull Code" /F
pause
