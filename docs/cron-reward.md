HUONG DAN TU DONG PHAT THUONG VAN DINH DAI LUC 21:00
====================================================

Muc dich
--------
He thong se tu tong ket top 10 Van Dinh Dai va phat thuong Linh thach + Ma thach moi ngay luc 21:00.

File chinh
----------
- D:\XXJserver\phpstudy_pro\WWW\cron\vandinhdai_daily_reward.php
  File PHP thuc hien tong ket va phat thuong.

- D:\XXJserver\phpstudy_pro\WWW\cron\run_vandinhdai_daily_reward.bat
  File chay tren Windows.

- D:\XXJserver\phpstudy_pro\WWW\cron\install_vandinhdai_reward_task.bat
  File cai lich tu dong tren Windows Task Scheduler.

- D:\XXJserver\phpstudy_pro\WWW\class\function\vandinhdai_reward.php
  Noi chinh sua moc thuong top 1 den top 10.


CAI TREN WINDOWS
----------------
1. Bam chuot phai vao file:
   D:\XXJserver\phpstudy_pro\WWW\cron\install_vandinhdai_reward_task.bat

2. Chon "Run as administrator".

3. Sau khi cai xong, Windows se tu chay tac vu:
   XXJ VDD Daily Reward

4. Tac vu nay chay moi ngay luc 21:00.

5. Neu muon kiem tra log, xem file:
   D:\XXJserver\phpstudy_pro\WWW\cron\logs\vandinhdai_daily_reward.log


CHAY THU TREN WINDOWS
---------------------
Mo Command Prompt hoac PowerShell, chay:

D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe D:\XXJserver\phpstudy_pro\WWW\cron\vandinhdai_daily_reward.php

Neu chua toi 21:00, ket qua se la:
issued=0 reason=not_time count=0

Neu da phat roi trong ngay, ket qua se la:
issued=0 reason=already_issued count=0

Neu phat thanh cong, ket qua se la:
issued=1 reason=ok count=10


CAI TREN SERVER LINUX
---------------------
1. Kiem tra duong dan PHP:

which php

Thuong se la:
/usr/bin/php

2. Mo crontab:

crontab -e

3. Them dong sau, nho doi /path/to/WWW thanh duong dan that tren server:

0 21 * * * /usr/bin/php /path/to/WWW/cron/vandinhdai_daily_reward.php >> /path/to/WWW/cron/logs/vandinhdai_cron.log 2>&1

Vi du neu source nam o /www/wwwroot/game/WWW:

0 21 * * * /usr/bin/php /www/wwwroot/game/WWW/cron/vandinhdai_daily_reward.php >> /www/wwwroot/game/WWW/cron/logs/vandinhdai_cron.log 2>&1

4. Nen dat timezone server ve Viet Nam:

timedatectl set-timezone Asia/Ho_Chi_Minh


LUU Y
----
- File PHP da dat timezone Asia/Ho_Chi_Minh, nen gio tinh thuong se theo gio Viet Nam.
- He thong co bang arena_daily_reward de tranh phat trung thuong trong cung mot ngay.
- Neu can doi moc thuong, sua trong ham vdd_daily_reward_table() tai file:
  D:\XXJserver\phpstudy_pro\WWW\class\function\vandinhdai_reward.php

