# TRIỂN KHAI KT-0 — Chuyển máy chủ sang PHP 7.4 (64-bit) + BIGINT

> Lập 2026-10-28. Thiết kế: `docs/KHUNG_TONG_500_4300.md` mục 2. Code đã chạy được trên **cả PHP 5.6 và PHP 7.4/8**
> ⇒ có thể pull code trước, chuyển PHP sau. **Phải chuyển sang PHP 7.4 x64 trước khi có người chơi giữ > 2,1 tỉ Linh Thạch / Ma Thạch**
> (PHP 5.6 trên Windows là 32-bit: số > 2.147.483.647 bị kẹp khi đọc ra).

## Thứ tự làm (Windows, phpStudy)

| # | Việc | Lệnh / thao tác | Kiểm tra |
|---|---|---|---|
| 1 | Sao lưu DB | `"D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup_kt0.sql` | File > 0 KB. Báo "cannot find the path" ⇒ xem tên thư mục MySQL trong `D:\XXJserver\phpstudy_pro\Extensions\` |
| 2 | Lấy code | `git pull` | |
| 3 | Đổi cột sang BIGINT | `php sql\migrate_kt0_bigint.php --dry-run` rồi `--apply` | Chạy lại `--dry-run` phải ra `Doi: 0 cot | bo qua: 17` |
| 4 | Cài PHP 7.4 | phpStudy → **Phần mềm / 软件管理** → PHP → cài **php7.4.3nts** (bản mới nhất 7.4 có trong danh sách) | Có thư mục `D:\XXJserver\phpstudy_pro\Extensions\php\php7.4.3nts\` |
| 5 | Kiểm 64-bit | `D:\XXJserver\phpstudy_pro\Extensions\php\php7.4.3nts\php.exe -r "echo PHP_INT_SIZE;"` | Phải in **8** |
| 6 | Bật extension | phpStudy → PHP 7.4 → Cấu hình / 扩展: bật **pdo_mysql, mbstring, curl** (code dùng 3 cái này) | `php.exe -m` có `pdo_mysql`, `mbstring`, `curl` |
| 7 | Chạy test trên PHP 7.4 | `...\php7.4.3nts\php.exe tests\test_kt0.php` và `tests\test_mo_khoa.php`, `tests\test_dieu_huong.php` | `ALL PASS` |
| 8 | Chuyển website | phpStudy → Website → sửa site game → **Phiên bản PHP = 7.4.3nts** → lưu, khởi động lại | Mở game, Ctrl+F5, đi Đại Sảnh / Tu Luyện / Cửa Hàng / đánh 1 con quái |
| 9 | Cron | Không cần sửa: `cron\run_stamina_recovery.bat`, `run_vandinhdai_daily_reward.bat` tự dùng PHP 7.4 nếu thư mục `php7.4.3nts` tồn tại | Nếu phpStudy cài tên thư mục khác (vd `php7.4.33nts`) ⇒ sửa dòng `set "PHP=..."` trong 2 file |

**Quay lại nếu lỗi:** bước 8 chọn lại PHP 5.6 (code vẫn chạy được). Cột BIGINT không cần trả lại (PHP 5.6 đọc bình thường khi số < 2,1 tỉ).

## Đã làm trong code (KT-0)

| Hạng mục | Thay đổi | File |
|---|---|---|
| Tương thích PHP 7.4/8 | `php -l` 200 file trên PHP 8.3 sạch; không dùng `each`, `create_function`, `mysql_*`, `$s{0}`. `parse_str` 1 tham số (deprecated 7.2 — sẽ in cảnh báo đầu mọi trang nếu bật hiển thị lỗi) ⇒ dạng 2 tham số + `extract()`, hành vi giữ nguyên | `game.php` |
| BIGINT | 17 cột: `game1` uyxb, uczb, uexp, uhp, umaxhp, ugj, ufy · `club` clubyxb, clubczb, clubexp · `playerchongwu` cwhp, cwmaxhp, cwgj, cwfy, cwexp · `mine_player` total_yxb, total_czb. Giữ NULL / DEFAULT; chạy lại không trùng | `sql/migrate_kt0_bigint.php` |
| Cộng / trừ tiền | Đã làm trong SQL (`uyxb = uyxb - :val`) ⇒ đúng với BIGINT. Thử ví 5 tỉ: 7 trang chính hiện đúng "5B", mua đan trừ đúng | — |
| EXP quái cấp > 500 | `guaiwu_exp()`: ≤ 500 giữ `cấp × (106–108)`; > 500 = `110 × cấp² ÷ 400` (≈ 400 quái/cấp). Hiện chưa có quái > 500 nên chưa đổi gì trong game — dùng cho làn L2 trở đi | `class/function/guaiwu.php`, `game/nowmid.php` |
| Màu cảnh giới | Mảng màu chỉ có 74/86 ô vì 15 tầng Chu Thiên ghi gộp 3 dòng ⇒ **từ cấp 2900 màu lệch**, cấp ≥ 3700 không có màu. Tách thành 15 dòng (giữ màu đã chọn) ⇒ đủ 86 | `class/jingjieColor.php` |
| Tên cảnh giới | "Sáng Thê" → "Sáng Thế"; bỏ khoảng trắng đôi "Vô Thượng Tiên Đế", "Bất Hủ Tiên Đế", "Đại Chủ Tể" | `class/rangesjj.php` |
| Đột Phá Đan (K2) | Trang Đột Phá có khu chọn 0–3 viên đan **đúng cảnh giới** (Trúc Cơ 350–399 · Kim Đan 400–449 · Nguyên Anh 500–549 · Hợp Thể 650–699 · Hóa Thần 600–649 · Luyện Hư 750–799 · Đại Thừa 800–849, tính theo cấp đang đứng khi đột phá), +5%/viên; đan trừ sau khi trả Linh Thạch, mất dù thành bại. Không chọn được quá số viên đang có | `game/tupo.php`, `class/function/tupo_bonus.php`, `class/config/tupo_bonus_config.json` |
| Sự kiện (K2) | Thứ 7 +5%, Chủ Nhật +10% (giờ máy chủ); tắt bằng `"enabled": false` | như trên |
| K6 | Cảnh giới cấp 3851: "Thánh Nhân" → "Hỗn Độn Thánh Nhân" | `class/rangesjj.php` |
| Test | `tests/test_kt0.php` (26 mục) | |
