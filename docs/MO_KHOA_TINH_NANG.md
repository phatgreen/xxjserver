# MỞ KHÓA TÍNH NĂNG THEO CẤP / CẢNH GIỚI (MK-1 ĐÃ LÀM 2026-10-28)

> Lập 2026-10-28. Đi cùng `docs/UI_DON_GIAN_HOA_MOBILE.md` (bố cục) và chính tuyến `class/config/nhiemvu_main_config.json`.

## 1. Vấn đề

- Game có **~30 tính năng**, tất cả mở ngay từ cấp 1 (chỉ vài khóa nội bộ: tạo Tông Môn cấp 30, công thức chế tạo từ cấp 25,
  bậc Huyết Mạch / Thể Chất theo cấp). Người mới vào thấy ~40 nút ⇒ sốc, không có cảm giác "khám phá".
- Chính tuyến chương 1 (cấp 1–10) bắt **Luyện Đan và Chế Tạo** ngay — trong khi **công thức chế tạo thấp nhất cần cấp 25**
  (`chetao_config.json`) ⇒ bước **1.6 "Rèn Tay Chế Tạo" không làm được** cho tới cấp 25 (**lỗi có sẵn**, người mới kẹt chính tuyến).

## 2. Mục tiêu

1. Cấp 1 chỉ có lõi: **tu luyện, đánh quái, trang bị, nhiệm vụ** (+ linh sủng sớm).
2. Mỗi 1–3 ngày chơi mở thêm 1 tính năng ⇒ luôn có "cái mới" để chờ (tò mò).
3. Mỗi lần mở: **thông báo + 1 nhiệm vụ Cơ Duyên** dạy đúng thao tác chính, thưởng nhỏ.
4. Tính năng sắp mở hiện **🔒 "Mở ở cấp X"** (chỉ 1–2 cái kế tiếp, không bày hết).

## 3. Lịch mở (đề xuất)

Nhịp dựa trên `est_days` của chính tuyến: chương 1 (cấp 1–10) ~2 ngày · chương 2 (10–25) ~5 ngày · chương 3 (25–50) ~10 ngày.

| Cấp | Tính năng mở | Lý do / khớp với code có sẵn | Nhiệm vụ Cơ Duyên |
|---|---|---|---|
| **1** | Tu Luyện, Đột Phá, Dùng Đan · Hành Tẩu (đánh quái, NPC) · Nhân Vật (Thông tin, Trang Bị, Túi Đồ) · Nhiệm Vụ chính tuyến · Cẩm Nang, Giftcode, Tin Tức | Lõi gốc. Đột Phá bắt buộc vì lên cấp đi qua `tupo` | (chính tuyến chương 1 hiện có) |
| **3** | Cửa Hàng (Thương Điếm) | Mua thuốc hồi máu khi bắt đầu thấy khó | Mua 1 bình dược |
| **5** | Bản Đồ thế giới (đi khu khác) | Rời làng tân thủ | Tới khu kế tiếp (`reach_region` có sẵn) |
| **8** | 🐾 **Linh Sủng** | Trụ cột gốc — mở sớm | Nhận / cho Linh Sủng xuất chiến *(loại mới)* |
| **10** | Võ Công, Bí Tịch, **Thiên Cơ Các** · Nhiệm vụ ngày/tuần · Trực Tuyến / Bế Quan | Cấp 10 = đột phá lớn đầu tiên. Nhiệm vụ ngày đã có `min_level 10` | Mua 1 bí tịch (`learn_book` có sẵn) |
| **15** | Luyện Đan (Lò Đan) | Đã có đủ nguyên liệu đánh quái rơi | Luyện 1 lò (`alchemy_count` có sẵn) |
| **20** | Hảo Hữu, Giao Dịch, Xếp Hạng, Quy Đổi, chat thế giới | Xã hội khi người chơi đã gắn bó | Kết 1 hảo hữu *(loại mới)* |
| **25** | Chế Tạo | **Khớp** công thức thấp nhất cấp 25 | Chế 1 món (`craft_count` có sẵn) |
| **30** | Tông Môn · Phó Bản: **Tỏa Yêu Tháp** | **Khớp** tạo tông môn cấp 30 | Vào/tạo tông môn *(loại mới)* · qua tầng 1 tháp *(loại mới)* |
| **40** | Công Pháp | Lớp chỉ số thứ 2 sau Võ Công | Vận 1 công pháp *(loại mới)* |
| **51** | Linh Căn *(vào cảnh giới 2)* | Mốc cảnh giới = cảm giác "lên tầm mới" | Tẩy Linh Căn 1 lần *(loại mới)* |
| **60** | Phó Bản: Hầm Mỏ, Sào Thú | Linh Sủng đã có vài chục cấp | Vào Sào Thú 1 lần *(loại mới)* |
| **75** | Thiên Phú | Khai Phú Đan 50.000 LT — đủ tiền ở cấp này | Khai Phú 1 lần *(loại mới)* |
| **80** | Vấn Đỉnh Đài (PvP) | Đã đủ hệ để so tài | Đấu 1 trận *(loại mới)* |
| **90** | Cộng Hưởng | Chính tuyến chương 7 (cấp 92–106) có bước 7.3 "Cộng Hưởng Sơ Thành" ⇒ phải mở trước 92 | (bước 7.3 chính tuyến) |
| **101** | Huyết Mạch *(vào cảnh giới 3)* | **Khớp** bậc Huyết Mạch đầu `realm_min_level 100` | Thức tỉnh Huyết Mạch *(loại mới)* |
| **120** | Thể Chất | Trước mốc thức tỉnh 150 (`thechat_awakening_config`) | Luyện hóa 1 lần *(loại mới)* |

> Các con số là đề xuất; nằm trong config nên đổi không cần sửa code.

## 4. Cơ chế (kỹ thuật)

### 4.1 Cấu hình — `class/config/feature_unlock.json`

```json
{
  "chongwu": { "name": "Linh Sủng", "icon": "🐾", "level": 8,
               "cmds": ["chongwu", "jninfo"], "desc": "Thu phục linh sủng cùng chiến đấu.",
               "quest": "cd_chongwu" },
  "luonden": { "name": "Luyện Đan", "icon": "🔥", "level": 15,
               "cmds": ["luonden", "do_craft_luonden", "use_detox_pill"], "...": "..." }
}
```

`cmds` = các `cmd` trong router `game.php` thuộc tính năng (bảng 77 route hiện có). Tính năng cấp 1 không cần khai báo.

### 4.2 Hàm dùng chung — `class/function/feature_unlock.php` (PHP 5.6)

| Hàm | Việc |
|---|---|
| `feature_unlock_config()` | Đọc JSON, cache `static` |
| `feature_is_open($player, $key)` | `ulv >= level` |
| `feature_key_for_cmd($cmd)` | Tra `cmd` → key (map dựng 1 lần) |
| `feature_next_locked($player, $n = 2)` | 1–2 tính năng sắp mở (để hiện 🔒) |
| `feature_new_opened($sid, $player, $dblj)` | Tính năng vừa đủ cấp mà người chơi **chưa xem** ⇒ hiện popup |

### 4.3 Chặn ở router — `game.php`

Trước `include` trang: nếu `cmd` thuộc tính năng chưa mở ⇒ hiện trang nhỏ **"🔒 {Tên} mở ở cấp {X}"** + nút về Tu Luyện.
Chặn ở router (1 chỗ) thay vì sửa từng trang ⇒ gọi thẳng URL cũng không vào được. **Chỉ chặn `cmd` khai báo trong config**
⇒ các `cmd` dùng chung (vd `getbagyp` cho Dùng Đan) không bị ảnh hưởng.

### 4.4 Ẩn lối vào

Mọi menu (thanh dưới, hàng Tiên Đồ, ☰ — xem `UI_DON_GIAN_HOA_MOBILE.md`) lọc qua `feature_is_open`; thêm 1–2 ô 🔒 của `feature_next_locked`.

### 4.5 "Tính năng mới" + Cơ Duyên

- Bảng mới `player_feature (sid VARCHAR(32), fkey VARCHAR(32), seen_at DATETIME, PRIMARY KEY (sid, fkey))` — ghi khi người chơi đã xem popup.
  1 truy vấn / trang (PK theo `sid`), có thể cache trong `getplayer`.
- Khi `feature_new_opened` trả về key ⇒ popup: icon + tên + 1 câu mô tả + nút **"Đi xem"** (dẫn tới tính năng) ⇒ ghi `player_feature`.
- Nhiệm vụ **Cơ Duyên**: chương phụ riêng trong `nhiemvu_main_config.json` (`"chapter": "co_duyen"`), mỗi bước gắn 1 tính năng,
  tự hiện khi tính năng mở, thưởng **nhỏ** theo % thanh cấp (`exp_pct` 3–5%, theo quy tắc thưởng đợt C — tránh tăng cấp nhanh).
- Loại nhiệm vụ **mới** cần thêm vào `class/function/nhiemvu.php` (mỗi loại = 1 dòng móc ở trang tương ứng, như các móc đợt C):
  `pet_active`, `friend_add`, `club_join`, `tower_floor`, `congphap_equip`, `linhcan_wash`, `sauthu_enter`, `tianfu_open`,
  `arena_fight`, `huyetmach_awaken`, `thechat_refine` (11 loại).

### 4.6 Sửa chính tuyến chương 1–3

| Bước | Hiện tại | Sửa |
|---|---|---|
| 1.5 Lần Đầu Luyện Đan (cấp ≤ 10) | Luyện Đan chưa mở ở cấp 15 | Chuyển sang chương 2 (sau cấp 15) |
| 1.6 Rèn Tay Chế Tạo (cấp ≤ 10) | **Không làm được trước cấp 25** | Chuyển sang chương 3 (sau cấp 25) |
| 1.4 / 2.1 / 3.1 thu thập nguyên liệu | Giữ | Giữ |
| Chương 1 thay bằng | — | "Mặc trang bị đầu tiên", "Đánh 10 quái", "Đột phá cấp 5", "Tới khu kế tiếp" |

## 5. Chia đợt

| Đợt | Nội dung | Kiểm chứng |
|---|---|---|
| **MK-1** | Config + hàm + chặn router + bảng `player_feature` + `sql/migrate_mo_khoa.php` | Test: cấp 1 gọi thẳng URL Luyện Đan ⇒ trang 🔒; cấp 15 ⇒ vào được |
| **MK-2** | Popup "Tính năng mới" + ẩn/🔒 ở menu hiện tại | Chụp màn hình cấp 1 / 8 / 15 / 30 / 101 |
| **MK-3** | Chương Cơ Duyên + 11 loại nhiệm vụ mới + sửa chương 1–3 | Chạy chuỗi nhiệm vụ cấp 1→30 trên server thử; `audit_renwu.py` |

Sau MK-2 làm tiếp UI-2/UI-3 (bố cục mới) để menu mới dùng sẵn hệ mở khóa.

## 5b. Kết quả MK-1 (ĐÃ LÀM 2026-10-28)

| Phần | File | Ghi chú |
|---|---|---|
| Cấu hình 24 tính năng | `class/config/feature_unlock.json` | Lịch mục 3; Cộng Hưởng chuyển sang **cấp 90** (xem bảng) |
| Hàm | `class/function/feature_unlock.php` (nạp trong `class/player.php`) | `feature_is_open`, `feature_lock_for_cmd`, `feature_quest_type_open`, `feature_next_locked` |
| Chặn router | `game.php` (sau khi nạp nhân vật) + chặn riêng `sendliaotian` (ghi DB trong `switch`) | Lệnh AJAX bị khóa trả JSON `{ok:false,msg}` |
| Trang 🔒 | `game/feature_locked.php` + CSS `.fk-lock` | Tên, cấp mở, còn bao nhiêu cấp, mô tả, nút Tu Luyện / Đại Sảnh |
| Chính tuyến | `nhiemvu_main_config.json` | Bỏ 1.5 (trùng 2.2 Luyện Đan); chuyển 1.6 Chế Tạo lên đầu chương 3 — **sửa luôn lỗi kẹt chính tuyến có sẵn** (1.6 cần cấp 25 nằm ở chương cấp 1–10). Giữ nguyên mã bước để không lệch tiến độ đã lưu |
| Nhiệm vụ ngày | `nhiemvu_daily_get` | Không bốc nhiệm vụ cần tính năng chưa mở (Luyện Đan < 15, Chế Tạo < 25, PvP < 80) — trước đây cấp 10–24 có thể nhận "Chế Tạo Mỗi Ngày" không làm được |
| Tuần / tháng | `nhiemvu_weekly_get`, `nhiemvu_monthly_get`, `nhiemvu_complete_all_sync` | Ẩn nhiệm vụ chưa mở; "Tổng Hợp" = min(mục tiêu, số nhiệm vụ đang làm được) — trước đây cấp < 25 **không thể** hoàn thành Tổng Hợp tuần |
| Test | `tests/test_mo_khoa.php` | ALL PASS (cấu hình khớp router, lệnh lõi không bị khóa, chặn/mở theo cấp, chính tuyến, nhiệm vụ ngày/tuần/tháng) |

Kiểm chứng thật (server thử): cấp 1 vào Luyện Đan / Chế Tạo / Linh Sủng / Thiên Cơ Các / Tông Môn ⇒ trang 🔒 đúng cấp; cấp 1 gửi chat ⇒ không ghi;
cấp 25 ⇒ Luyện Đan, Chế Tạo, chat mở, Tông Môn vẫn khóa. Test cũ C / D / F / Thiên Phú trang: ALL PASS
(`test_linhcan_pages` lỗi 29 mục **cả trên code gốc** — lỗi có sẵn của bài test, không do MK-1).

Bảng `player_feature` (đánh dấu đã xem popup) chuyển sang **MK-2** (nơi dùng tới).

## 6. Cần chủ dự án quyết

| # | Câu hỏi | Đề xuất |
|---|---|---|
| K1 | Lịch mở mục 3 | Như bảng |
| K2 | Mở theo **cấp** (đơn giản) hay **cấp + hoàn thành nhiệm vụ** (chặt hơn, dễ kẹt)? | Theo cấp; nhiệm vụ Cơ Duyên chỉ để dạy + thưởng |
| K3 | Nhân vật đang có (game chưa mở) | Không cần xử lý — mở theo cấp hiện tại; popup "Tính năng mới" không bắn hàng loạt cho nhân vật đã vượt mốc (đánh dấu đã xem khi migrate) |
