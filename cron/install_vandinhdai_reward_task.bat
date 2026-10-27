@echo off
schtasks /Create /F /SC DAILY /ST 21:00 /TN "XXJ VDD Daily Reward" /TR "D:\XXJserver\phpstudy_pro\WWW\cron\run_vandinhdai_daily_reward.bat" /RU SYSTEM
pause
