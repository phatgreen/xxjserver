# BÀN GIAO PHIÊN LÀM VIỆC — đọc file này đầu tiên khi mở phiên mới

> Cập nhật 2026-10-28. Nhánh làm việc: **`claude/tong-quan-du-an`** (repo `phatgreen/xxjserver` — **hiện đang Public, cần chuyển Private + đổi mật khẩu MySQL**).
> Tổng quan kiến trúc: `docs/TONG_QUAN_DU_AN.md`. Kế hoạch mở rộng tổng: `docs/PLAN_MO_RONG_NOI_DUNG.md`.

## 1. Bối cảnh & quy ước

- **Game:** H5 Tu Tiên "Tu Chân Giới", PHP 5.6 + MySQL 5.5 (phpStudy, Windows). Thư mục máy chủ chủ dự án: `D:\XXJserver\phpstudy_pro\WWW`.
  PHP 5.6: `D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe`; mysqldump: `...\Extensions\MySQL5.5.29\bin\mysqldump.exe`.
- **Game đang phát triển, CHƯA mở cho người chơi** ⇒ được phép đổi cân bằng / dữ liệu, không cần giữ tương thích người chơi cũ.
- **Chủ dự án muốn:** trả lời Tiếng Việt, comment code Tiếng Việt; thay đổi nhỏ nhất; không tự refactor; việc lớn thì
  **viết thiết kế (docs/…) → chủ dự án duyệt → làm → kiểm chứng thật (test + chụp màn hình)**; không để thưởng quá nhiều (tránh tăng cấp nhanh).
- **Bảo mật:** `pdo.php` (mật khẩu DB) và `docs/db/game.sql` (dữ liệu tài khoản) nằm trong repo ⇒ **repo phải luôn Private**.
  Không in mật khẩu ra chat. `tests/test_linhcan_*.php` có mật khẩu cứng (có từ trước).
- **Commit:** kết thúc bằng 2 dòng `Co-Authored-By: Claude …` + `Claude-Session: …` theo nhắc của hệ thống; không ghi tên model trong code/commit.
- **Dữ liệu sinh tự động:** mọi file config lớn sinh bằng `tools/gen/*.py` (chạy lại cho cùng kết quả). Sửa số thì sửa trong generator rồi chạy lại.

## 2. Đã làm trong các phiên trước (theo thứ tự)

| Hạng mục | Tóm tắt | Tài liệu |
|---|---|---|
| Võ công / công pháp (đợt A) | Thế võ 3 bậc (Võ Kỹ / Tuyệt Kỹ / Tuyệt Chiêu) có tên + sát thương + hồi chiêu, quái/boss phản kích, boss tụ lực/phá thế, Cộng Hưởng 6 nhóm; +30 võ công, +28 công pháp | `DOT_A_VOCONG_CONGPHAP.md`, `PLAN_VOCONG_CONGPHAP.md` |
| Quái & khu vực tới cấp 500 (đợt B) | 84 quái mới (mã 200–283), 5 khu mới (qy 32–36) + khu 27/28/30/31, 9 boss khu; ảnh bản đồ `dt/ditu/31–36.png` | `DOT_B_QUAI_CANH_GIOI.md` |
| Giao diện chiến đấu | `pve.php` / `boss.php`: màn **Chiến Trường** mô phỏng từng đòn (`js/battle_sim.js` dùng chung), kết quả thắng/thua dạng **popup** (đưa ra `<body>` vì khung có backdrop-filter), báo tử vong ngay lượt bị hạ | — |
| Nhiệm vụ (đợt C) | Thưởng theo % thanh cấp (`nhiemvu_reward_rules.json`), chính tuyến 13 chương (cấp 1–500), 9 NPC khu mới (mã 40–48, NV 70–96), tab **Trực Tuyến** + **Bế Quan** (thưởng offline), NPC hiện ở Đại Sảnh, chính tuyến thu gọn theo chương; sửa chuỗi NPC tân thủ 41–60 (sai loại, không NPC giao, mục tiêu sai) | `DOT_C_NHIEM_VU.md` |
| Trang bị (đợt F) | **Đột phá cộng số cố định** (bản cũ lũy tiến 1%/5% ⇒ máu vô hạn), 215 món đủ 15 ô ở 11 bậc + bậc 500 "Hồng Mông", phẩm chất theo bậc, cường hóa +8% chỉ số gốc, Thần Trang 15 món, chế tạo đúng cấp, cân lại quái/boss đợt B | `DOT_F_TRANG_BI.md` |
| Sửa lỗi kèm | Đường dẫn config `__DIR__ . '../../config'` thiếu `/` (renwu/daoju/yaodan/wugong — hỏng trên Linux); Thiên Cơ Các mua sách / tên cảnh giới; công pháp vận nhiều bộ | — |

**Máy chủ chủ dự án đã chạy xong (2026-10-28):** `migrate_dot_b.php --apply`, `migrate_dot_c.php --apply`, `migrate_dot_f.php --apply`;
test B / C / F đều PASS (test C D7 đã sửa lệch múi giờ PHP–MySQL sau đó — cần kéo code về chạy lại xác nhận).

### Phiên 2026-10-28 (Claude Code cloud) — đã đẩy lên nhánh

| Hạng mục | Tóm tắt | Tài liệu |
|---|---|---|
| Cấp tối đa 4300 | Lên đúng 4300 "Vô Ngã Viên Mãn — Đại Đạo Đỉnh Phong"; vá lỗi cấp 4299 đột phá miễn phí cộng chỉ số vô hạn | — |
| Đợt D | Tiên Đạo Tàn Quyển (djid 115): boss 400/450/500 rơi 3/4/5%, đổi 10 quyển/bộ ở Thiên Cơ Các; tiệm không bán (`no_shop`) | `DOT_D_TIENDAO_TANQUYEN.md` |
| Boss | Chặn hạ lại boss trước thời gian hồi sinh (`mid.ms`), mốc hồi ghi đúng map boss | `DOT_D_…` mục 6 |
| Đột phá + trang bị | Gốc từ cấp 94 theo mốc (500: −20%, 4300: −110%); ô Linh Phù/Ngọc Nhãn/Cổ Ngọc +đột phá, Phi Phong/Linh Đai/Linh Bội +tu luyện (trần 30%) | `KHUNG_TONG_500_4300.md` mục 4 |
| UI-1 mobile | Thanh trên 1 hàng (217→133px), số tiền gọn, vùng chạm ≥32px, chữ ≥11px | `UI_DON_GIAN_HOA_MOBILE.md` mục 3.1 |
| KT-0 nền kỹ thuật | Tương thích PHP 7.4/8, BIGINT 17 cột (`sql/migrate_kt0_bigint.php`), EXP quái > 500, đủ 86 màu cảnh giới, sửa tên (K6), Đột Phá Đan + sự kiện cuối tuần (K2), cron tự chọn PHP | `DEPLOY_KT0_PHP74.md` |
| UI-2/UI-3 khung mới | Thanh dưới cố định 5 tab mọi trang (chấm đỏ, 🔒), tab con Nhân Vật/Linh Sủng/Nhiệm Vụ, Tiên Đồ thay 2 cột nút Tu Luyện + 21 nút Đại Sảnh; HTML ở `class/function/dieu_huong.php` | `UI_DON_GIAN_HOA_MOBILE.md` mục 3.2 |
| Mở khóa MK-1..3 | 24 tính năng mở cấp 3→120 (chặn router, nút 🔒, popup "Tính năng mới"), lọc nhiệm vụ ngày/tuần/tháng, sửa kẹt chính tuyến 1.6, chương Cơ Duyên 24 nhiệm vụ | `MO_KHOA_TINH_NANG.md` |
| Chỉ mục DB | `sql/migrate_index_sid.php` — 13 bảng (game1, túi đồ…) | — |

**Máy chủ cần chạy:** `sql\migrate_index_sid.php --dry-run` rồi `--apply` (sao lưu trước). Các phần khác không cần migrate.
**Test mới:** `tests/test_dot_d.php`, `tests/test_mo_khoa.php`. Fixture e2e phải ở **cấp 1000** (test trang Thiên Phú/Linh Căn cần tính năng đã mở).

## 3. Việc còn treo / có thể làm tiếp

| Việc | Ghi chú |
|---|---|
| **Đợt D** — nguồn võ công Tiên Đạo | Hiện chỉ Thiên Cơ Các bán; dự kiến rơi từ boss khu vực |
| **Ô 16 Bảo Vật** | Chưa có món nào; cần thiết kế hiệu ứng riêng (đã thống nhất làm sau) |
| **Làn nội dung cấp 500–1000** | Game có 74 cảnh giới (~cấp 3.700); làm theo làn ~500 cấp, dùng lại `tools/gen/` |
| mining_bonus của công pháp | Chờ có hệ thống đào mỏ |
| Thiên Phú: đổi tên Khai Phú → Sơ Phú, Khai Phú Đan → Thiên Phú Đan | Còn mở trong `UI_THIENPHU.md` |
| Nhiệm vụ NPC gốc mồ côi 10 "Nạp Tiền", 14 "Thu tập phong mật" | Không NPC giao, vô hại — chưa xử lý |
| Quái cũ cấp ≤ 105 | Chưa cân lại theo đường sức mạnh mới (vẫn dễ: quái cần 50–180 đòn hạ người) |
| Lệch múi giờ PHP–MySQL trên máy chủ | Không ảnh hưởng game hiện tại; nên đặt cùng `date.timezone` |
| Thư mục `images/other` | Ảnh tham khảo game khác — chủ dự án sẽ xóa sau |
| **Chờ duyệt / làm tiếp (2026-10-28)** | Chủ dự án chuyển máy sang PHP 7.4 x64 theo `DEPLOY_KT0_PHP74.md` · làn 500→4300 (K5 tên phẩm) |

## 4. Bản đồ code nhanh (phần đã đụng tới)

| Mục | File |
|---|---|
| Định tuyến | `game.php` (`parse_str($Dcmd)` 1 tham số, token **không** dùng 1 lần), `class/encode.php` |
| Nhân vật / chỉ số | `class/player.php` (`getplayer` có cache theo request ⇒ gọi `player_cache_clear($sid)` sau khi UPDATE thô), `game/tupo.php` |
| Chiến đấu | `game/pve.php`, `game/boss.php`, `class/function/combat.php`, `js/battle_sim.js`, config `wugong_skill_config.json`, `guaiwu_skill_config.json` |
| Nhiệm vụ | `class/function/nhiemvu.php`, `class/function/online_reward.php`, `game/nhiemvu.php`, `game/task.php`, `npc/npc.php`, `game/taskguide.php`; móc ở `nowmid.php`, `pve.php`, `boss.php`, `thiencoccac.php`, `conghuong.php`, `tupo.php` |
| Trang bị | `class/function/zhuangbei.php`, `class/function/taozhuang.php`, `upzbsx` trong `player.php`, `game/zbinfo.php`, `game/taozhuang.php` |
| Bản đồ | `game/nowmid.php` (Đại Sảnh), bảng `mid` (`mgid` quái, `mnpc` NPC, `midboss`), `qy` khu vực |
| CSS | `css/mystyle.css` — **tăng số `?v=` trong `game.php` mỗi khi sửa** (hiện `2026102803`) |

## 5. Môi trường thử trên cloud (container mới mỗi phiên)

1. Cài/khởi MariaDB, tạo DB `game`, nạp `docs/db/game.sql` (cần `SET GLOBAL innodb_strict_mode=0; sql_mode=''`),
   đặt mật khẩu root khớp `pdo.php`. Sau đó chạy `migrate_dot_b/c/f.php --apply` như máy chủ.
2. `bash tools/cloud/start_env.sh` — bật DB + server PHP thử `127.0.0.1:8091` từ bản sao `/tmp/xxjrun`
   (vá `parse_str` cho PHP 8; **không sửa code thật**). MariaDB hay tự tắt ⇒ chạy lại script.
3. Đăng nhập thử: dùng nhân vật e2e có sẵn trong dump (`sid = e2e2…e2`, 32 ký tự; nếu thiếu thì tạo bằng `php tests/e2e_fixture.php`); trước khi chụp đặt `endtime = NOW()`
   (quá 1 giờ không request sẽ bị đá ra).
4. Tạo URL: `php tools/cloud/enc.php "cmd=gomid&newmid=225&sid=<sid>"`; chụp: `OUT=<dir> node tools/cloud/shot_url.js "ten|<url>"`.
5. Test: `php tests/test_dot_c.php`, `test_dot_f.php`, `test_dot_b_data.php`, `test_dot_a_data.php`, `test_combat_moves.php`, `test_conghuong.php`.
6. Mô phỏng cân bằng: `python3 tools/gen/sim_power.py 10`; rà nhiệm vụ NPC: `python3 tools/gen/audit_renwu.py 10 96`.

## 6. Lệnh cho máy chủ chủ dự án (Windows cmd)

```
cd /d D:\XXJserver\phpstudy_pro\WWW
chcp 65001
"D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup.sql
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\migrate_<đợt>.php --dry-run
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\migrate_<đợt>.php --apply
```
Mật khẩu mysqldump = giá trị `$sqlpass` trong `pdo.php`. Cập nhật code: `cron\capnhat_code.bat` (`git pull --ff-only` nhánh đang dùng), rồi Ctrl+F5.
