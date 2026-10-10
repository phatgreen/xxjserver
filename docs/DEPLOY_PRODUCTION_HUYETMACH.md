# Deploy production — MẠCH HUYẾT (8 mạch luyện được + boss riêng + nâng phẩm)

**Trạng thái: CHƯA CHẠY trên production.** Đây là runbook đã chuẩn bị sẵn; chủ server
duyệt từng mục rồi tự tay chạy. Không có script nào trong repo này tự connect production,
và không có lệnh nào ở đây được chạy thử trên production trong phiên 2026-10-10.

Ngày chuẩn bị: 2026-10-10 · Spec: `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md`
· Plan: `docs/superpowers/plans/2026-10-09-huyetmach-mang-mach.md` (12 task, Task 12 là file này)
· Nhật ký tính năng + sổ nợ 27 dòng: `docs/UI_HUYETMACH.md`
· Runbook đợt trước (chưa chạy): `docs/DEPLOY_PRODUCTION_NGUHANH.md`

---

## 1. Ba bảng MẠCH HUYẾT chưa tồn tại ở production

Đo trên DB `game` **local** ngày 2026-10-10 (bằng `E:/window/Temp/_hm_status.php`, chỉ đọc).
Cột "production" là **CHƯA ĐO** — chủ server chạy mục 3 bước 1 để lấy số thật.

| Bảng / cột | Local 2026-10-10 | Nguồn schema | Production |
|---|---|---|---|
| `huyetmach_boss` | exists=1 rows=0 (AUTO_INCREMENT=69, đã sinh rồi xoá khi test) | `sql/sql_huyetmach_vessel.sql:15` | **CHƯA TỒN TẠI** (bảng mới Task 8) |
| `player_huyetmach_vessel` | exists=1 rows=8 (sid test `e2e2e2…`) | `sql/sql_huyetmach_vessel.sql:7` | **CHƯA TỒN TẠI** (bảng mới Task 2/3) |
| `player_huyetmach_upgrade_log` | exists=1 rows=0 (AUTO_INCREMENT=431 do test) | `sql/sql_huyetmach_vessel.sql:35` | **CHƯA TỒN TẠI** (bảng mới Task 9) |
| `player_huyetmach_boss` | exists=1 rows=0, `sid varchar(64)` | bảng **CŨ** — Task 8 ghi qua `huyetmach_boss_mark()` (`class/function/huyetmach.php:325`) | có thể đã có, **không được drop** (mục 6) |
| `player_huyetmach_quest` | exists=1 rows=0, `sid varchar(64)` | bảng **CŨ**, hiện **không cuộc gọi nào ghi** (Sổ nợ dòng 6) | có thể đã có, để nguyên |
| `game1.huyetmach_id` | int(11) DEFAULT 0 | `sql/sql_thechat_huyetmach.sql:16` | **CHƯA ĐO** |
| `game1.huyetmach_quality` | int(11) DEFAULT 1 | `sql/sql_thechat_huyetmach.sql:17` | **CHƯA ĐO** |
| `game1.huyetmach_awaken_stage` | tinyint(4) DEFAULT 0 | `sql/sql_thechat_huyetmach.sql:18` | **CHƯA ĐO** |
| `game1.huyetmach_transform_cd` | datetime DEFAULT NULL | `sql/sql_thechat_huyetmach.sql:19` | **CHƯA ĐO** |

**Hai chi tiết dễ nhầm, ghi rõ để lần sau không ai "dọn" nhầm:**

1. `sql/sql_huyetmach_vessel.sql` **chỉ có 3 câu `CREATE TABLE IF NOT EXISTS`, không có câu
   `ALTER` nào** (`grep -n ALTER` = rỗng). Bốn cột `game1` ở bảng trên **không** nằm trong file đó —
   chúng đến từ `sql/sql_thechat_huyetmach.sql:16-19` của đợt Thể Chất. Chạy riêng file vessel
   sẽ tạo đủ 3 bảng nhưng **không** thêm cột `game1`; thiếu cột thì `getplayer()` không đọc được
   `huyetmach_quality`/`huyetmach_awaken_stage` và trang mạch huyết hiện ra bậc 0 với phẩm 1.
2. `huyetmach_boss` là **MyISAM / utf8mb4_general_ci**, còn `game1` là **gb2312_general_ci**.
   Hiện **không có câu JOIN nào** so `sid` giữa hai bảng (`grep -rn JOIN class/function/huyetmach_boss.php`
   = rỗng), mỗi bên tự lọc `sid` riêng nên không lỗi. **Đừng viết JOIN** — MySQL 5.5 sẽ báo
   `Illegal mix of collations`. Nếu muốn thống nhất, đổi `huyetmach_boss` sang InnoDB/utf8
   **trước khi** có dữ liệu thật, không đổi sau.
---

## 2. Việc cần chủ server quyết TRƯỚC khi bấm chạy

Bốn việc này là quyết định thiết kế, không phải lỗi kỹ thuật — xem `docs/UI_HUYETMACH.md`
(Sổ nợ dòng 1, 2, 3, 6) để thấy chứng cứ.

1. **Nợ schema `sid` của 15 bảng phải trả trước.** `docs/DEPLOY_PRODUCTION_NGUHANH.md` mục 3
   vẫn CHƯA chạy trên production. Lý do buộc phải theo thứ tự: bảng mới
   `player_huyetmach_vessel.sid` khai báo `VARCHAR(64)`, trong khi `game1.sid` ở production
   vẫn có thể là `INT`. Hai kiểu khác nhau thì mọi truy vấn theo `sid` vẫn chạy (mỗi bảng lọc
   riêng), nhưng code đối chiếu tiến độ giữa `game1` và bảng mạch huyết sẽ trả rỗng, và
   `tests/deploy_preflight.php` sẽ không bao giờ ra `0`.
2. **Kinh tế cày mạch huyết:** một nhân vật cần **1.481 Huyết Thạch** (luyện 1.344 + thức tỉnh 60
   + nâng phẩm 77), nguồn rơi 5 trận/ngày ⇒ **~71 / 36 / 18 ngày** tuỳ stage boss. Deploy nguyên
   trạng là chấp nhận con số đó. Muốn nhanh: sửa `refine.material_base` / `material_per_lv`
   hoặc `boss_config.drops.by_stage` / `max_fights_per_day` (toàn bộ nằm trong
   `class/config/huyetmach_vessel_config.json` + `huyetmach_boss_config.json`, không đụng code).
3. **Huyết Mạch Đan (id 113) không có nguồn rơi.** Bậc thức tỉnh 4 cần `113 × 1`, còn
   `huyetmach_boss_finish()` chỉ cộng id 112 ⇒ trên production **không ai lên được bậc 4**
   cho tới khi chủ server mở nguồn (boss stage 4 rơi % / bán bằng Linh Thạch / đan từ 112).
   Tương tự, **stage 0 và stage 4 không có boss** (`huyetmach_boss_spawn()` đòi stage hiện tại + 1,
   config chỉ có boss stage 2/3/4) ⇒ đường rơi Huyết Thạch bằng 0 ở hai bậc đó.
4. **Bảng `player_huyetmach_quest` vẫn bỏ trống.** `quest_require` còn `true` trong
   `huyetmach_awakening_config.json:36,50` nhưng không có chuỗi quest nào nối vào
   (`class/function/huyetmach.php:335` đọc + `:359` ghi, hai hàm này **không cuộc gọi nào** trong repo).
   Chọn: dựng quest thật, hoặc xoá key + bỏ bảng khỏi `deploy_preflight.php:24`.

---

## 3. Trình tự chạy

```bash
# 0) SAO LƯU (bat buoc). Chi 3 bang lien quan + cot game1, khong dump ca DB de kho phuc hoi.
mysqldump -u <USER> -p -h <HOST> <DB> \
  huyetmach_boss player_huyetmach_vessel player_huyetmach_upgrade_log \
  player_huyetmach_boss player_huyetmach_quest game1 \
  > huyetmach_before_deploy_20261010.sql
#    -> 3 bang MOI se bao "Couldn't find table" o production: do la dung, do khong phai loi.
#       game1 + 2 bang cu bat buoc phai dump duoc.

# 1) KIEM HIEN TRANG (chi doc, khong sua) — chay trong mysql client:
#    SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES
#      WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE '%huyetmach%';
#    SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
#      WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='game1' AND COLUMN_NAME LIKE 'huyetmach%';
#    SELECT VERSION(), @@sql_mode;
#    -> du rong 3 bang moi => tien dung. Thieu 1 trong 4 cot game1 => them cau ALTER (muc 3.2).

# 2) TAO 3 BANG MOI
mysql -u <USER> -p -h <HOST> <DB> < sql/sql_huyetmach_vessel.sql
#    File chi co CREATE TABLE IF NOT EXISTS => chay lan 2 khong loi.
#    Neu buoc 1 thieu cot game1, them tay 4 cau o `sql/sql_thechat_huyetmach.sql:16-19`
#    (COPY dung nguyen 4 do, khong "nang cap" ngu).

# 3) KIEM TRA LAI — bat buoc ra "Hang schema con thieu/sai: 0"
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/deploy_preflight.php \
  --host=<HOST> --db=<DB> --user=<USER> --pass=<PASS>
```

**Ba ràng buộc kỹ thuật khi chạy mục 3 — không được bỏ qua:**

1. **MySQL 5.5.29 không hỗ trợ `ADD COLUMN IF NOT EXISTS`.** Nếu cột đã tồn tại mà chạy thêm
   `ALTER … ADD COLUMN` sẽ dừng ở lỗi `1060 Duplicate column name`. Đó không phải hỏng —
   chỉ cần xoá câu đó khỏi bản tạm rồi chạy phần còn lại. Đừng "nâng cấp" file SQL.
2. **Cũng MySQL 5.5.29 không hỗ trợ `DEFAULT CURRENT_TIMESTAMP`** cho cột `DATETIME`.
   `sql/sql_huyetmach_vessel.sql` vì vậy dùng **`updated_at` / `spawned_at` / `killed_at` /
   `created_at` kiểu `DATETIME DEFAULT NULL`** (`sql/sql_huyetmach_vessel.sql:11,27,28,41`)
   và code tự ghi `date('Y-m-d H:i:s')` từ PHP. Đây là lựa chọn cố ý, **không phải thiếu sót** —
   ai đó "chỉnh cho chuẩn" sang `CURRENT_TIMESTAMP` sẽ làm file chạy lỗi trên production.
3. **`sql_mode` local đo được là chuỗi rỗng, production thường đặt `STRICT_TRANS_TABLES`.**
   Ở mode rỗng, `INSERT` một chuỗi hex 32 ký tự vào cột `INT` chỉ bị **cắt im im** thành `0`;
   ở STRICT câu đó **dừng transaction với lỗi**. Đây chính là nợ 15 bảng sid trong
   `docs/DEPLOY_PRODUCTION_NGUHANH.md`. Hệ quả đặc thù của mạch huyết: nếu `game1.sid` vẫn là
   `INT` mà `player_huyetmach_vessel.sid` là `VARCHAR(64)`, mỗi bảng tự lưu một kiểu sid khác nhau
   ⇒ tiến độ mạch huyết ghi được nhưng **không tra được theo nhân vật** (mọi truy vấn khớp `sid`
   trả rỗng, người chơi thấy "0 mạch" dù DB có dòng). **Trả xong nợ sid trước, rồi mới deploy mạch huyết.**

Kiểm chứng cục bộ trước khi copy code (chạy trên local, đã chạy đủ ngày 2026-10-10 — kết quả trong
`docs/UI_HUYETMACH.md` §9.1):

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
$PHP tests/test_huyetmach_schema.php     # 3 bang + 4 cot game1 khop dinh nghia
$PHP tests/test_huyetmach_vessel.php     # cap mach, chi phi, tran, milestone
$PHP tests/test_huyetmach_boss.php       # sinh boss per-player, gioi han 5 ngay
$PHP tests/test_huyetmach_upgrade.php    # 9 bac nang pham, transaction + rowCount
$PHP tests/test_huyetmach_combat.php     # 4 chi so am (khoi A-D)
```
---

## 4. Copy code (PHP 5.6, không build)

```
class/config/huyetmach_vessel_config.json        (MOI — 8 mach, 5 bac, refine, milestone)
class/config/huyetmach_boss_config.json          (MOI — 33 boss per-player + drops)
class/config/huyetmach_upgrade_config.json       (MOI — 9 bac nang pham)
class/config/huyetmach_transform_config.json     (DA CO — **chua duoc git theo doi**, bat buoc copy tay)
class/config/daoju_config.json                   (doi — vat pham 112 Huyet Thach, 113 Huyet Mach Dan)
class/function/huyetmach.php                     (doi — **append** 641 -> 1148 dong)
class/function/huyetmach_boss.php                (MOI — sinh boss, danh, roi do, gioi han ngay)
class/player.php                                 (doi — $huyetmach_vessel, cong 4 chi so duong)
game/huyetmach.php                               (VIET LAI — 1 man 2 card)
game/huyetmach_boss.php                          (MOI)
game/huyetmach_upgrade.php                       (MOI)
game/huyetmach_transform.php                     (viet lai theo khung moi)
game/zhuangtai.php game/boss.php game/pve.php game/pvp.php game/thechat.php   (doi)
game.php                                         ($cmdMap 4 route + `?v=` CSS)
css/mystyle.css                                  (doi — khoi `.hm-*` + `.hmq-*`)
tongquan.php                                     (doi — muc `mach-huyet`)
images/huyetmach/                                (MOI — 22 file: kinh-mach.jpg + cac bo ngoc)
```

**Bốn lưu ý khi copy:**

1. **Xoá `game/huyetmach_awakening.php` SAU CÙNG**, và chỉ sau khi chắc `game.php` không còn
   route nào trỏ tới nó. File này local đã xoá (`git status` = not tracked, not present);
   nếu production còn file cũ mà code mới đã gộp màn, người chơi vẫn vào được màn cũ
   qua URL đánh tay — màn đó đọc `huyetmach_awakening_config.json` theo kiểu cũ.
2. **`class/function/huyetmach.php` là append, không phải sửa trộn.** Mọi khối mới bọc
   `if (!function_exists(...))` ⇒ copy nguyên file local đè lên production là an toàn,
   **nhưng** không được dùng bản vá tay từng hàm (dễ lệch số dòng đã ghi trong docs).
3. **`css/mystyle.css` phải đi kèm bump version trong `game.php`.** Bản hiện tại là
   `game.php:502` `css/mystyle.css?v=2026101005`. Nếu production đang dùng `?v=` khác,
   **giữ giá trị của production hoặc đặt cao hơn**, đừng lấy số local đè xuống — nếu không
   trình duyệt người chơi (đặc biệt là WebView game) sẽ giữ CSS cũ và `.hm-*` thành vô hình.
4. **Ảnh:** `$hmImgVer = '2026100901'` khai ở `game/huyetmach.php:11`, dùng cho mọi
   `images/huyetmach/*`. **Đổi ảnh thì phải bump biến này**, nếu không điện thoại giữ ảnh cũ
   (đây là bài học `ZB_IMG_VER` của đợt Phi Phong).
   `images/huyetmach/` là thư mục MỚI — copy cả thư mục, không copy lọt mỗi `kinh-mach.jpg`.

**Cảnh báo chung cho mọi đợt deploy của repo này:** `git ls-files class/config` chỉ trả về
**6/40** file JSON ⇒ phần lớn config (gồm `huyetmach_transform_config.json`) **không được git theo dõi**.
Không được deploy bằng `git archive`/`git clone` — phải copy tệp theo danh sách mục 4.

## 5. Nghiệm thu sau deploy (bắt buộc bấm tay, không chỉ xem schema)

Làm bằng 1 tài khoản phụ trên production. Tám bước, bước nào chưa qua thì **dừng**, ghi lại
kết quả vào `docs/UI_HUYETMACH.md` §10 thay vì làm tiếp.

1. `cmd=huyetmach` — mở được màn, ảnh kinh mạch hiện (không phải ô vỡ), rail 5 bậc hiện tên.
2. Chưa có Huyết Thạch: bấm *Luyện Mạch* ⇒ báo **thiếu vật liệu**, **không** bị trừ Linh Thạch.
   (Đây là nghiệm thu `rowCount()`/transaction ở `class/function/huyetmach.php:688-703`.)
3. `cmd=use_dj` ăn 1 **Tố Thể Đan** ⇒ `cmd=huyetmach` hiện tên huyết mạch, 8 ngọc đổi màu khoá.
4. Luyện 1 cấp ⇒ `SELECT level FROM player_huyetmach_vessel WHERE sid='<sid>'` có dòng,
   `updated_at` **không NULL** (chứng tỏ PHP ghi ngày, không phải mặc định DB).
5. `cmd=huyetmach_boss` ⇒ sinh được boss; giết xong `player_huyetmach_boss` có dòng
   (`huyetmach_boss_mark()`), và 112 **Huyết Thạch** vào túi thật (không chỉ in chữ).
6. Đánh 1 con quái thường ⇒ dòng sát thương có **Né Tránh** hiện ra; lên bậc có chỉ số âm
   thì `cmd=zhuangtai` hiển thị 8 dòng `Mạch …`.
7. `cmd=huyetmach_upgrade` ⇒ bảng 9 bậc hiện, bấm nâng phẩm thất bại thì **không mất** vật liệu
   (`success=0` trong `player_huyetmach_upgrade_log`).
8. `tongquan.php` ⇒ mục **Mạch Huyết (8 mạch luyện được)** hiện với **17 dòng**
   (8 mạch + 5 bậc + 4 mốc), tìm "Né Tránh" trong ô lọc của `tbl-mh` ra đúng 1 dòng.

## 6. Rollback

```sql
-- Rollback an TOAN: chi drop 3 bang MOI (production chua co ai dung)
DROP TABLE IF EXISTS huyetmach_boss;
DROP TABLE IF EXISTS player_huyetmach_vessel;
DROP TABLE IF EXISTS player_huyetmach_upgrade_log;
```

**KHÔNG drop `player_huyetmach_boss` và `player_huyetmach_quest`** — hai bảng CŨ:
`player_huyetmach_boss` được Task 8 ghi qua `huyetmach_boss_mark()`
(`class/function/huyetmach.php:325`), xoá là mất dấu tiến độ boss của người chơi;
`player_huyetmach_quest` là bảng của chuỗi nhiệm vụ cũ, xoá không thu lại được gì.

Bốn cột `game1` (`huyetmach_*`): **để nguyên**. Code cũ không đọc cột này nên không hại gì,
mà `DROP COLUMN` trên MySQL 5.5 sẽ rebuild `game1` (bảng lớn nhất, đang có người chơi).
Code rollback: copy lại `game.php`, `class/player.php`, `css/mystyle.css`, `tongquan.php`
từ backup; `game/huyetmach_awakening.php` khôi phục từ backup nếu đã xoá.

## 7. Bốn điểm trống phát hiện khi làm tài liệu — cần chủ dự án quyết, chưa tự sửa

1. **`huyetmach_boss` MyISAM/utf8mb4 trong khi các bảng khác InnoDB/utf8**
   (`sql/sql_huyetmach_vessel.sql:15-33`). Không có JOIN nào nên chưa lỗi, nhưng xem mục 1.
2. **`sql/sql_huyetmach_vessel.sql` không tạo cột `game1`** — bốn cột nằm ở file của đợt Thể Chất
   (`sql/sql_thechat_huyetmach.sql:16-19`). Runbook nào chỉ chạy file vessel sẽ có 3 bảng mà
   không có chỗ lưu phẩm chất/bậc ⇒ nên gộp 4 câu ALTER thành file deploy riêng cho đợt này
   (quyết định của chủ dự án, chưa tự làm vì đụng file của đợt khác).
3. **`docs/img/huyetmach/*` không được commit** (24 ảnh chứng cứ). Đây là chủ ý: repo này
   không theo dõi ảnh chụp kiểm thử. Hệ quả: người sau muốn đối chiếu phải chụp lại theo
   playbook §9 của `docs/UI_HUYETMACH.md`.
4. **Change `.main .nb-btn` (2 nút màn Nhục Thân) vẫn chưa commit** — xem Sổ nợ dòng 14.
   Nếu đợt deploy này có `css/mystyle.css` thì change đó **đi kèm luôn**, không tách được;
   nghĩa là production sẽ đổi màu nút của trang Nhục Thân trong cùng đợt mạch huyết.
   Chủ dự án cần gật đầu điều này trước khi copy `css/mystyle.css`.
