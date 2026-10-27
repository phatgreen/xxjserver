# Deploy production — Thuộc tính Ngũ Hành + nợ schema sid

**Trạng thái: CHƯA CHẠY trên production.** File này là runbook đã chuẩn bị sẵn;
chủ server phải duyệt từng mục rồi tự tay chạy. Không có script nào trong repo
này tự connect production.

Ngày chuẩn bị: 2026-10-04 · Spec: `docs/DESIGN_NGUHANH_KICHHOAT.md`
· Nhật ký tính năng: `docs/CHANGELOG.md` (mục "Trang bị: Nạp Ngũ Hành")

---

## 1. Hai nợ gộp một đợt

| Nhóm | Nội dung | Nguồn |
|---|---|---|
| A | 3 cột mới cho Thuộc tính Ngũ Hành (`element` × 2, `log_type`) | `sql/nguhanh_kichhoat.sql` |
| B | 15 cột `sid`/`killer_sid` đang khai báo INT nhưng game ghi hex 32 ký tự | `sql/fix_sid_column_type.sql` |

Hai nhóm đã gộp và **rút hết DELETE** vào một file duy nhất:
`sql/deploy_production_20261004.sql` (18 câu ALTER, 0 câu DELETE/UPDATE/INSERT).

`sql/fix_sid_column_type.sql` gốc có 5 câu `DELETE FROM` (bảng 1–5, PK chứa `sid`)
vì ở local các dòng đó đã bị ghi đè lẫn nhau không còn truy ra chủ được.
**Bản production không xoá**: dữ liệu production là tiến độ thật của người chơi.

## 2. Việc cần chủ server quyết trước khi bấm chạy

1. **5 bảng PK chứa `sid`** (`player_linhcan_quest`, `player_alchemist`,
   `player_dan_dinh`, `player_huyetmach_boss`, `player_huyetmach_quest`):
   các dòng cũ vẫn mang `sid` đã bị cắt ngắn (`0`, `1`, `8`, `547`, …) và **không
   thể suy lại chủ**. Nới cột VARCHAR(64) thì chúng vẫn còn đó nhưng vô chủ.
   Chọn một trong ba:
   - (a) giữ nguyên như file hiện tại → tiến độ cũ biến mất khỏi tra cứu, không mất dung lượng;
   - (b) xoá sạch 5 bảng đó trước khi ALTER (như đã làm ở local) → mất tiến độ của người chơi;
   - (c) tự viết câu UPDATE gán lại chủ theo `game1.sid` → tốn công, chỉ làm nếu thật sự cần.
2. **Thời điểm chạy**: `ALTER TABLE ... MODIFY COLUMN` trên MySQL 5.5 sẽ rebuild
   toàn bảng. Chạy lúc không có người chơi, và backup trước.
3. **Ảnh trang bị**: đợt này đổi art ô Phi Phong (`images/16-20.png`,
   `images/91-95.png`, `images/slot/zb_slot_08.png`). **Bản hiện tại là vòng 2**
   (05/10/2026): 10 icon **mỗi món một master riêng**, `ZB_IMG_VER = 2026100406`;
   bản vòng 1 có 3 cặp trùng file nên **không được copy lên production**.
   Nếu production chưa từng nhận ảnh Plan Z thì phải copy cả `images/` + `images/slot/`.

## 3. Trình tự chạy

```bash
# 0) Sao lưu (bắt buộc)
mysqldump -u <USER> -p -h <HOST> <DB> > game_before_deploy_20261004.sql

# 1) Kiểm hiện trạng — chỉ đọc, không sửa gì
php tests/deploy_preflight.php --host=<HOST> --db=<DB> --user=<USER> --pass=<PASS>
#    -> in tung bang: element/log_type da co chua, sid la so hay da nong.
#    -> exit 0 = schema dung roi (khong can chay nua), exit 1 = con thieu.

# 2) Chay 18 cau ALTER
mysql -u <USER> -p -h <HOST> <DB> < sql/deploy_production_20261004.sql
#    MySQL 5.5 khong ho tro "ADD COLUMN IF NOT EXISTS": neu chay lan 2 se
#    dung o loi 1060 "Duplicate column name". Do la cot da co -> xoa cau do
#    khoi file tam va chay phan con lai.

# 3) KIEM TRA LAI — bat buoc ra "Hang schema con thieu/sai: 0"
php tests/deploy_preflight.php --host=<HOST> --db=<DB> --user=<USER> --pass=<PASS>
```

**File SQL này đã diễn tập ngày 2026-10-04** trên DB tạm `game_sqltest`
(84 bảng, dump schema từ `game` rồi đặt lại đúng trạng thái CHƯA migrate:
drop 3 cột mới + ép 15 cột sid về INT):
`deploy_preflight` báo **18** dòng sai → chạy `sql/deploy_production_20261004.sql`
một lần duy nhất, **không lỗi** → `deploy_preflight` báo **0**, và
`COLUMN_TYPE / IS_NULLABLE / COLUMN_DEFAULT` của 3 cột mới khớp từng chữ với DB `game`
đang chạy thật. DB tạm đã `DROP DATABASE` sau đó.

## 4. Copy code (PHP 5.6, không build)

Nhóm A — tính năng:

```
class/config/nguhanh_config.json      (MOI)
class/function/nap_hanh.php           (MOI)
class/function/itemtip.php            (MOI)
class/function/zhuangbei.php          (doi)
class/player.php                      (doi)
game/nap_hanh.php                     (MOI)
game/zbinfo.php  game/pve.php  game/boss.php
game/trangbi.php  game/zhuangtai.php  game/bagzb.php  game/fangshi.php
game.php                              ($cmdMap them nap_hanh)
js/itemtip.js                         (MOI)
css/mystyle.css                       (doi)
tongquan.php                          (doi)
```

Nhóm B — code khong doi (chi la schema; PHP hien tai da bind hex sid).

Asset anh: `images/16.png` … `images/20.png`, `images/91.png` … `images/95.png`,
`images/slot/zb_slot_08.png`. `class/function/zhuangbei.php` co
`const ZB_IMG_VER` — neu production dung gia tri khac thi **giu ngun gia tri
cua production** hoac tang len, dung de cache client khong thay anh cu.

## 5. Nghiệm thu sau deploy (bắt buộc bấm tay, không chỉ xem schema)

Local đã chạy đủ 4 bộ test (`tests/`, xem `docs/CHANGELOG.md`). Production thì
làm nhanh 7 bước bằng 1 tài khoản phụ:

1. `cmd=trangbi` — mở 1 món, section **"Thuộc tính Ngũ Hành"** hiện nút *Kích hoạt*.
2. Kích hoạt hệ 1 → popup kết quả `win`, badge `1/5`.
3. Refresh trang bị → hệ vẫn còn (đọc từ DB, không phải từ session).
4. Treo bán món đó ở Phương Thị → mua bằng tài khoản khác → mở popup, **hệ còn nguyên**.
5. Đánh 1 con quái khắc hệ → dòng chữ `Trang bị khắc <Hành>! +20%` xuất hiện.
6. `cmd=zhuangtai` — mục hợp căn hiển thị % nội tại.
7. `tongquan.php` — bộ đếm "Thuộc tính Ngũ Hành" tăng.

## 6. Rollback

Schema chỉ **thêm cột** (nhóm A) → có thể để nguyên, code cũ không đọc cột đó.
Riêng `player_chetao_log.sid` và 14 cột nhóm B **không rollback được về INT**
nếu dữ liệu đã ghi hex (MySQL sẽ cắt ngắn trở lại, đúng lỗi cũ). Vì vậy:
backup + preflight trước, và chạy thử trên bản dump trước khi chạm production.

## 7. Hai điểm trống phát hiện khi test — cần chủ dự án quyết, chưa tự sửa

1. **Boss không khắc chế được bằng trang bị.** Bảng `boss` không có cột `element`
   (`class/player.php` `getboss()` chỉ đọc `$row['element']` nếu cột tồn tại),
   nên khối khắc chế trong `game/boss.php:241-274` không bao giờ kích hoạt.
   Quái hoang (`guaiwu_config.json`) thì chạy đúng. Muốn boss khắc chế: thêm cột
   `element` vào bảng `boss` + điền giá trị cho từng boss.
2. **Ô 16 "Bảo Vật" không có món nào.** `class/config/zhuangbei_config.json`
   không có item nào `zbtool=16`, nên ô 16 không thể mặc gì; một dòng cũ (zbid 55)
   nay map sang tool 11. Cần bổ sung config (theo Plan Z, sửa config phải được
   chủ dự án duyệt).

Ngoài ra (lặt vặt, không ảnh hưởng chơi):
- Nhánh báo lỗi `'Trang bị đã đủ 5 hệ ngũ hành.'` trong `nap_hanh_do()` **không tới được**
  — khi đã đủ 5 hệ hợp lệ thì kiểm tra "trùng hệ" luôn chặn trước. Đã ghi chú trong test.
- `game/trangbi.php:37` và `game/zhuangtai.php:90` nối `&v=filemtime(...)` vào URL
  đã có `?v=ZB_IMG_VER` → thành `?v=X&v=Y`. Hợp lệ, chỉ thừa.
