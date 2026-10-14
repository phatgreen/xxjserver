# Deploy production — THIÊN PHÚ (Phủ Các sưu tầm 18 thiên phú + 10 phẩm + 4 Phú Vi)

> **TRẠNG THÁI: RUNBOOK — CHƯA CHẠY.** Cập nhật lần cuối: 2026-10-14.

Không có lệnh nào trong file này được chạy trên production trong phiên 2026-10-14. Repo không có
remote, không có CI, không có script nào tự connect production. Chủ server đọc từng mục, tự quyết,
tự tay chạy.

Ngày chuẩn bị: 2026-10-14 · Spec: `docs/superpowers/specs/2026-10-12-thien-phu-phu-cac-design.md`
· Plan: `docs/superpowers/plans/2026-10-12-thien-phu-phu-cac.md` (12 task, Task 12 là file này)
· Nhật ký + sổ nợ: `docs/UI_THIENPHU.md` · Runbook đợt trước (cũng chưa chạy):
`docs/DEPLOY_PRODUCTION_HUYETMACH.md`

---

## 1. Bảng/cot CHƯA tồn tại ở production

Đo trên DB `game` **local** ngày 2026-10-14. Cột "Production" là **CHƯA ĐO** — chạy mục 3 bước 1
để lấy số thật, đừng đoán.

| Đối tượng | Local 2026-10-14 | Nguồn schema | Production |
|---|---|---|---|
| `player_tianfu` | tồn tại, `COUNT(*) = 0` (chưa ai khai phú) | `sql/sql_tianfu.sql:7-16` | **CHƯA TỒN TẠI** |
| `player_tianfu_log` | tồn tại, `COUNT(*) = 12` (= 13 nhân vật TRỪ sid fixture `e2e2e2…`; xem §9.1 `docs/UI_THIENPHU.md` — `tests/test_tianfu_pages.php:68,148` xóa log của sid đó) | `sql/sql_tianfu.sql:18-29` | **CHƯA TỒN TẠI** |
| `game1.tianfu_id` | `tinyint(4) NOT NULL DEFAULT 0` | `sql/tianfu_columns.php:14` | **CHƯA TỒN TẠI** |
| `game1.tianfu_quality` | `tinyint(4) NOT NULL DEFAULT 0` | `sql/tianfu_columns.php:15` | **CHƯA TỒN TẠI** |
| `game1.tianfu_lv` | `int(11) NOT NULL DEFAULT 0` | `sql/tianfu_columns.php:16` | **CHƯA TỒN TẠI** |
| `game1.tianfu_power` | `int(11) NOT NULL DEFAULT 0` | `sql/tianfu_columns.php:17` | **CHƯA TỒN TẠI** |
| `daoju` **114** Khai Phú Đan | có, **12** nhân vật đang giữ (sid fixture `e2e2e2…` đã bị test xóa đan) | `class/config/daoju_config.json` | **CHƯA TỒN TẠI** |
| 8 cột `tf tfgj tfhp tffy tfbj tfxx tfxy tfsb` | vẫn còn, **đã đặt về 0** trừ `tf` | schema gốc 2006-style | đã có, **không drop** |

**Bốn chi tiết dễ nhầm, đọc hết trước khi bấm:**

1. `sql/sql_tianfu.sql` **chỉ có 2 câu `CREATE TABLE IF NOT EXISTS`, không có `ALTER` nào** → chạy
   một mình nó **không** thêm 4 cột `game1`. Bốn cột do `sql/tianfu_columns.php` thêm (guard
   `information_schema`, idempotent, mặc định chỉ in kế hoạch, phải có `--apply` mới ghi).
   Thiếu cột thì `getplayer()` không đọc được `tianfu_power` → chiến lực và bảng xếp hạng sai.
2. **MySQL 5.5 không hỗ trợ `DEFAULT CURRENT_TIMESTAMP` cho kiểu `DATETIME`.** Hai bảng mới vì vậy
   dùng `obtained_at DATETIME DEFAULT NULL` / `created_at DATETIME DEFAULT NULL`, và mã PHP ghi
   `NOW()`. Đừng "nâng cấp" sang `CURRENT_TIMESTAMP`.
3. **8 cột `tf*` là `varchar(255) DEFAULT '0'`, không phải số.** `SUM(tf)` và `tf = tf + ?` vẫn chạy
   vì MySQL ép kiểu ngầm, nhưng nếu một dòng nào đó chứa chuỗi không phải số thì kết quả là 0 +
   giá trị hoàn. Trước khi migration, chạy câu kiểm tra ở mục 3 bước 1.4 để chắc chắn không có rác.
4. **Không có `JOIN` nào** giữa `player_tianfu` (InnoDB/utf8_general_ci) và `game1` (gb2312):
   `tianfu_owned()` (`class/function/tianfu.php:223-251`) gộp 18 ô bằng PHP. Đừng viết JOIN —
   MySQL 5.5 sẽ báo `Illegal mix of collations`.

---

## 2. Việc cần chủ server quyết TRƯỚC khi bấm chạy

| # | Câu hỏi | Ngữ cảnh |
|---|---|---|
| 2.1 | **Phương án A hay C** cho khối cộng thuộc tính cũ ở `class/player.php`? | Local đã chốt **A** (xoá 6 dòng 588-593, chủ dự án 2026-10-14: "acc test thôi không quan trọng"). Hệ quả: nhân vật cũ **mất chỉ số** do thiên phú cũ mang lại. Local đo: PhátĐây Bạo kích **91 → 37**. Production có nhân vật thật → **phải chạy `_tp_legacy.php` trên production trước** để ra con số từng người, rồi hãy quyết. Phương án C = giữ khối đó và nhân đôi giá trị. |
| 2.2 | Có **báo trước** về bảng xếp hạng đảo thứ tự không? | `game/paihang.php:19` cộng thêm `tianfu_power`. Ngay sau migration, mọi nhân vật có ô đang gán sẽ đổi hạng. |
| 2.3 | Điểm `tf` hoàn có đặt **trần** không? | Local tổng hoàn 410/12 nhân vật được migrate (~32 điểm/người). Production có thể có nhân vật `tf` cũ hàng chục nghìn → mua được hàng trăm lần nâng cấp ngay ngày đầu. |
| 2.4 | `tfsb` có được nối vào **vận khí** trong tương lai không? | Hiện **không** — trùng nhịp Né Tranh của Ẩm Kieu Mach (`huyetmach_combat_roll_dodge()`, `class/function/huyetmach.php:1112`). Cột giữ nguyên, không drop. |
| 2.5 | Có deploy **kèm** bản sửa cổng `alchemy` không? | `docs/UI_THIENPHU.md` §8.4: đường luyện đan thật (`luonden_craft_v2` → `luonden_dung_dan`, `class/function/luonden.php:899`) **không** cộng cổng Thiên Phú, trong khi chữ hiển thị tỉ lệ (`game/luonden.php:223`) thì có. Nếu deploy Thiên Phú mà chưa sửa, người chơi thấy % ảo. |

---

## 3. Trình tự chạy

```bash
# --- 0) SAO LƯU (bat buoc). Chi dump 3 bang lien quan, KHONG dump ca DB. ---
<mysqldump> -u<user> -p<pass> <db> game1 playerdaoju player_tianfu player_tianfu_log \
  > bak/tianfu_pre_<nhan-thi-gian>.sql
#    player_tianfu / player_tianfu_log se bao "Couldn't find table" o lan dau: DUNG, do khong phai loi.
#    game1 + playerdaoju BAT BUOC phai dump duoc, khong co loi thi dung lai nghi.
ls -l bak/tianfu_pre_*.sql        # kiem tra > 0 byte va du 2 ten bang cu

# --- 1) KIEM HIEN TRANG (chi doc, khong sua) ---
# 1.1 Bang:
#   SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES
#    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE '%tianfu%';
#   -> du rong = chua tao gi ca, tiep buoc 2.
# 1.2 Cot game1:
#   SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
#    WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='game1'
#      AND (COLUMN_NAME LIKE 'tf%' OR COLUMN_NAME LIKE 'tianfu%');
#   -> phai thay 8 cot tf* cu + (0 hoac 4) cot tianfu_* moi.
# 1.3 MySQL:
#   SELECT VERSION(), @@sql_mode;      -> 5.5.x, ghi lai sql_mode de doi chieu
# 1.4 Rac trong cot tf (truoc khi di migration):
#   SELECT COUNT(*) rac FROM game1 WHERE tf REGEXP '[^0-9]';
#   -> phai = 0. > 0 thi dung lai, xu ly tung dong.
# 1.5 Chan so that cua production (KHONG dung so local):
#   SELECT COUNT(*) nguoi, SUM(CAST(tf AS UNSIGNED)) tf_goc,
#          SUM(CAST(tfhp AS UNSIGNED)+CAST(tfgj AS UNSIGNED)+CAST(tffy AS UNSIGNED)
#            +CAST(tfbj AS UNSIGNED)+CAST(tfxx AS UNSIGNED)+CAST(tfxy AS UNSIGNED)
#            +CAST(tfsb AS UNSIGNED)) diem_cu
#     FROM game1;

# --- 2) TAO 2 BANG MOI ---
<mysql> -u<user> -p<pass> <db> < sql/sql_tianfu.sql
#    File chi co CREATE TABLE IF NOT EXISTS -> chay lan 2 khong loi.

# --- 2b) THEM 4 COT game1 (idempotent, co 2 luot) ---
<php5.6> sql/tianfu_columns.php            # chi in ke hoach "SE ADD"
#    SUA duong connect o sql/tianfu_columns.php:10 cho dung host/db/user/pass PRODUCTION
#    (file dang hardcode 'localhost;dbname=game' + root). Quen sua = ALTER vao DB khac.
<php5.6> sql/tianfu_columns.php --apply    # ALTER that

# --- 3) KIEM TRA LAI — bat buoc ra "Hang schema con thieu/sai: 0" ---
<php5.6> -r "require 'pdo.php'; ..."   # hoac chay 1.1 + 1.2 lan nu:
#   phai thay: 2 bang ton tai + 4 cot tianfu_* = 4. Thieu bat cu thu nao -> DUNG.

# --- 4) DRY-RUN migration (khong ghi DB) ---
<php5.6> sql/migrate_tianfu.php --dry-run
#   -> bang moi nhan vat: tf_goc | diem_cu | se_hoan | dan_114
#   -> SO SANH "tong se_hoan" voi `diem_cu` o 1.5. Lech nhau = dung 1 trong 2 phep do, DUNG.
#   -> exit 3 = thieu schema/daoju 114. exit 2 = chon sai co.

# --- 5) APPLY ---
<php5.6> sql/migrate_tianfu.php --apply
#   Ky vong: OK=<so nguoi> FAIL=0 SKIP=0 TONG_HOAN=<bang dry-run> TONG_DAN_114=<so nguoi>
#   MOT nhan vat FAIL -> rollBack nhan vat do va dung het (thiet ke co y, spec §7.4).
#   Chay lan 2 toan bo SKIP (guard player_tianfu_log kind='migrate') -> idempotent.

# --- 6) KIEM TRA SAU ---
#   SELECT COUNT(*) FROM player_tianfu_log WHERE kind='migrate';   -- = so nguoi OK
#   SELECT COUNT(*) FROM playerdaoju WHERE djid=114;               -- = so nguoi OK
#   SELECT SUM(CAST(tf AS UNSIGNED)) FROM game1;                   -- = tf_goc(1.5) + TONG_HOAN
#   SELECT COUNT(*) FROM game1 WHERE tfhp<>0 OR tfgj<>0 OR tffy<>0 OR tfbj<>0
#                              OR tfxx<>0 OR tfxy<>0 OR tfsb<>0;   -- = 0
#   SELECT sid, tianfu_id, tianfu_quality, tianfu_lv, tianfu_power FROM game1
#    WHERE tianfu_power > 0;                                        -- rong (chua ai khai phu)
```
---

## 4. Copy code (PHP 5.6, không build)

Repo không có pipeline. Copy thủ công theo đường dẫn tương đối — cấu trúc thư mục production
giống hệt local (`WWW/` là docroot). **Danh sách 21 file + 7 ảnh + 9 test, thiếu một file config
là hỏng cả tính năng:**

| Nhóm | File | Production cần sửa gì? |
|---|---|---|
| Config | `class/config/tianfu_config.json` | Không — nhưng **phải copy**, mọi hàm đọc từ đây |
| Config | `class/config/daoju_config.json` | **BẮT BUỘC copy.** Thiếu dòng 114 → `\player\getdaoju(114)` trả `false` và `sql/migrate_tianfu.php` **`exit(3)`** ngay ở bước kiểm tra schema |
| Hàm | `class/function/tianfu.php` | Không |
| Hàm | `class/player.php` | Không (đã include `function/tianfu.php` ở `:28`) |
| Hàm | `class/function/luonden.php` | Không — **nhưng xem §7.1 trước khi copy** |
| Trang | `game/tianfu.php` `game/tianfu_collection.php` `game/tianfu_open.php` | Không |
| Trang | `game/zhuangtai.php` `game/xiulian.php` `game/pve.php` `game/boss.php` `game/tupo.php` `game/zbinfo.php` `game/paihang.php` | Không |
| Route | `game.php` | **Có** — 3 route ở `:349-351` và `stamina` ở `:460`; nếu production đã độ `game.php` riêng thì **vá tay**, đừng đè |
| Admin | `tongquan.php` | Không (mục Thiên Phú `:446-515`) |
| SQL | `sql/sql_tianfu.sql` `sql/tianfu_columns.php` `sql/migrate_tianfu.php` | `tianfu_columns.php:10` hardcode `localhost;dbname=game` + `root` → **sửa cho đúng DB production** |
| CSS | `css/mystyle.css` | Không — nhưng **phải bump** `game.php:506` `css/mystyle.css?v=…` (local đang `2026101401`), nếu không trình duyệt người chơi giữ CSS cũ và 425 dòng `.talent-*` đã xoá vẫn còn trong cache |
| Ảnh | `images/tianfu/` (7 file `.jpg`) | Không — sau khi copy, nếu đổi ảnh thì bump `$tpImgVer` ở `game/tianfu.php:7` |
| Test | `tests/test_tianfu_*.php` (9 file) | Không bắt buộc trên production; nếu copy thì **chạy ở DB phụ**, các test này INSERT/DELETE thật |

**Kiểm tra sau khi copy** (PHP 5.6 tại production, không phải PHP 8):

```bash
for f in class/function/tianfu.php game/tianfu.php game/tianfu_collection.php \
         game/tianfu_open.php class/player.php game.php class/function/luonden.php \
         sql/migrate_tianfu.php sql/tianfu_columns.php; do <php5.6> -l "$f"; done
<php5.6> -r "echo PHP_VERSION, PHP_EOL;"          # phai 5.6.x
```

Mã nguồn **không** dùng `??`, `<=>`, arrow fn, `array_key_last()` — nếu production chạy PHP 8 thì
vẫn chạy được, nhưng **đừng lấy đó làm lý do nâng cấp** giữa đợt.

---

## 5. Nghiệm thu sau deploy (bắt buộc bấm tay, không chỉ xem schema)

Làm trên **một tài khoản thật** ở cấp ≥ 100 (để mở được Phú Vi 2), theo đúng thứ tự:

1. Đăng nhập → mở `cmd=tianfu_open`. Thấy: số đan trong túi = **1** (do migration phát),
   "đã khai mở **0** lần", bảng 10 phẩm với tỉ lệ `40,00% … 0,01%`.
2. Bấm **Khai Mở** → có kết quả 1 ô, phẩm bất kỳ. Mở lại `cmd=tianfu_open` → "đã khai mở **1** lần".
3. Mở `cmd=tianfu` → **4 thẻ Phú Vi**, ô 1 đã sáng và mang tên + màu theo phẩm vừa ra;
   rail 6 nhóm hiện `x/3`; bảng "Kỹ Năng Kích Hoạt" hiện **đúng 1 cổng** khác 0, 6 cổng còn lại 0.
4. Mở `cmd=tianfu_collection` → lưới 18 ô, ô đã có viền màu + nhãn phẩm, 17 ô còn lại nét đứt
   "Chưa khai mở". Bấm vào ô đang có → panel hiện Lv, phí nâng cấp, nút **Nâng Cấp** bật được
   nếu `tf` còn.
5. Nâng 1 cấp → cổng tương ứng tăng đúng `0,25% × mult` (làm tròn 2 chữ số, `cap_percent` 30).
6. Mở `cmd=zhuangtai` → 2 thẻ **"☯ Chủ phú"** (tên + phẩm, màu đúng) và **"🎖 Phú Vị 1 / 4"**.
7. **CHIẾN LỰC TRƯỚC/SAU**: ghi số ở bước 1 và sau bước 5. `tianfu_power` trọng số 1
   (`class/player.php:433`), mỗi ô = `phẩm·150 + Lv·8` → **độ lệch phải ≤ ±5%**. Lệch lớn hơn =
   Phương án A đã cắt nhầm thứ gì đó → **dừng, đừng bấm thêm gì, rollback (§6)**.
8. Mở `cmd=xiulian` → dòng linh khí/giây thay đổi theo cổng `exp_cultivate` (so với tài khoản
   chưa khai phú cùng cấp). Đây là **nghiệm thu cổng thật duy nhất nhìn thấy ngay**; các cổng
   `drop` / `exp_monster` phải giết quái thật mới thấy → **CHƯA có cách kiểm nhanh**.
9. `cmd=tianfu` bấm sang tab **Phú Các** rồi **Khai Phú** rồi **Nhân Vật** → 4 tab đều về đúng
   trang, không trang trắng, không lỗi PHP (`grep -i "fatal\|warning\|notice" <log Apache>`).

Ghi lại: ngày chạy, số nhân vật OK/FAIL/SKIP, `TONG_HOAN`, ảnh 4 màn. Thiếu một trong bốn mục này
thì đợt deploy **chưa được coi là xong**.

---

## 6. Rollback

```bash
# 1) Khoi phuc DB tu dump o muc 3 buoc 0
<mysql> -u<user> -p<pass> <db> < bak/tianfu_pre_<nhan-thi-gian>.sql
```

**Hai điều cấm:**

- **Cấm** rollback code (gỡ `game/tianfu.php`, revert `class/player.php`) mà **giữ nguyên DB đã
  migration**. Lúc đó 7 cột `tf*` đã bị đặt về 0 trong khi khối cộng thuộc tính cũ đã bị xoá →
  nhân vật **mất sạch** phần thiên phú cũ, không khôi phục được nếu không có dump.
  Hoặc giữ cả hai, hoặc bỏ cả hai.
- **Cấm** `DROP TABLE player_tianfu` / `DROP COLUMN tianfu_*` khi người chơi đã kịp khai phú —
  làm mất đan và mất ô đã mua. Nếu chỉ muốn ẩn tính năng: **xoá 3 route ở `game.php:349-351`**
  (trang không vào được nữa) và revert `class/player.php` + `game.php:460`, giữ nguyên schema.

Thứ tự đúng khi phải lùi: **DB trước, code sau** (vì code mới đọc schema mới; code cũ không đọc 2
bảng mới nên an toàn).

---

## 7. Bốn điểm trống phát hiện khi làm tài liệu — cần chủ dự án quyết, chưa tự sửa

1. **Cổng `alchemy` không có tác dụng trên đường luyện đan thật** (`docs/UI_THIENPHU.md` §8.4).
   UI gọi `luonden_craft_v2()` → `luonden_dung_dan()` và hàm này tính `$finalSuccessRate` ở
   `class/function/luonden.php:899` **không** cộng `$tpAlchemy`; trong khi `luonden_success_text()`
   (`:735`, hiển thị qua `game/luonden.php:223`) **có** cộng. Người chơi thấy tỉ lệ cao hơn mức
   thực tế được quay. Chưa sửa vì phải chọn: cộng vào Dung Đan, hay chuyển Dung Đan sang dùng
   `luonden_calc_success_rate()` — hai lựa chọn cho ra con số khác nhau.
2. **`upplayerlv()` vẫn cấp 6 cột `tf*` mỗi lần lên cấp** (`class/player.php:1201`, khối
   `:1232-1256`). Sau migration các cột này = 0, nhưng lên cấp là chúng lại lớn dần → **migration
   không phải một lần rồi thôi**, nhân vật lên cấp sau đó sẽ tích `tf*` vô nghĩa (và nếu ai đó chạy
   `--apply` lần hai mà bỏ guard thì sẽ hoàn trùng). Cần chốt: xoá khối đó, hay giữ và dùng `tf`
   như một khoản tích luỹ.
3. **`usedaoju()` chặn mỗi loại đan tối đa 2 lần** (`class/player.php:2083-2089`) trong khi
   `daoju_config.json` không có cột `djuse` cho 5 loại 109-113 → luồng "uống đan" của Khai Phú Đan
   phải đi đường vòng `deledjsum(114, 1, $sid, $dblj)` trong `tianfu_open()`. Nếu sau này thêm đan
   Thiên Phú, nhớ đường vòng này chứ đừng trông vào `usedaoju()`.
4. **Bảng xếp hạng đổi thứ tự ngay khi migration chạy xong** (`game/paihang.php:19` cộng
   `tianfu_power`). Chưa có cơ chế "giữ hạng ngày deploy" hay thông báo trong game.
