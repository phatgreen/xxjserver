# ĐỢT C — Nhiệm vụ theo dải cấp 1–500 (ĐÃ LÀM 2026-10-28)

> Lập 2026-10-28. Thuộc kế hoạch tổng `PLAN_MO_RONG_NOI_DUNG.md` (mục 7). Sau đợt A (võ công/công pháp) và B (quái tới cấp 500).

## 1. Hiện trạng (đã đo trong code / config / DB)

| Hệ | Ở đâu | Hiện có | Vấn đề |
|---|---|---|---|
| Chính tuyến | `nhiemvu_main_config.json`, bảng `player_nhiemvu_main`, `class/function/nhiemvu.php` | 3 chương / 21 NV, **cấp 1–50**; loại: hội thoại, diệt quái theo dải cấp, thu thập, luyện đan, chế tạo, cường hóa, hạ boss, đột phá | Dừng ở cấp 50. Thưởng cố định (50–8.000 tu vi) — ở cấp 50 lên 1 cấp cần 275.000 ⇒ gần như vô nghĩa |
| Ngày / Tuần / Tháng | `daily_quests.json` (8 chọn 5), `weekly_quests.json` (10), `monthly_quests.json` (5) | Đủ khung, có thưởng hoàn thành cả ngày/tuần/tháng | Thưởng cố định (ngày 300–700 tu vi). "Diệt 30 quái" **không giới hạn cấp** ⇒ cấp 300 đánh quái cấp 1 vẫn tính |
| Ẩn / Thành tựu | `hidden_quests.json` (8), achievements | Có móc sự kiện | Ổn, giữ nguyên |
| NPC theo bản đồ | `renwu_config.json` (44 NV), `npc_config.json` (26 NPC), `mid.mnpc`, bảng `playerrenwu`, `game/task.php` | Loại: thu thập / diệt quái / tìm NPC / luyện đan đóng góp; kiểu thường / ngày / chuỗi | NPC chỉ có ở khu cũ (cấp ≤ 100). **9 khu cấp 78–500 không có NPC nào** (khu 27, 28, 30–36) |
| Thưởng trực tuyến | — | **Chưa có** | |
| Thưởng ngoại tuyến | — | **Chưa có** (chỉ có Tu Luyện tính giờ: thường 30', linh thạch 180', ma thạch 360', phải bấm trước) | |

Móc sự kiện hiện có (`nhiemvu_update_all`): kill (pve), boss, breakthrough (tupo), cultivate (xiulian), alchemy (luonden), craft (chetao), upgrade (zbinfo), pvp (vandinhdai), collect (adddj), dialogue.

## 2. Nguyên tắc

1. **Thưởng tính theo % thanh kinh nghiệm cấp hiện tại** (cấp² × 110), không ghi số cứng ⇒ cấp nào cũng có ý nghĩa, không phá nhịp "cấp cao luyện lâu".
   Tham chiếu: lên 1 cấp ≈ hạ số quái bằng chính cấp mình (cấp 300 ≈ 310 quái).
2. Thưởng cũ giữ làm **mức sàn** (lấy số lớn hơn) ⇒ người chơi cấp thấp không bị thiệt.
3. Tận dụng hệ sẵn có (chính tuyến, ngày/tuần/tháng, NPC renwu) — **chỉ thêm dữ liệu + ít móc sự kiện**, không viết hệ mới trừ trực tuyến/ngoại tuyến.
4. PHP 5.6, bảng mới tự tạo khi chạy lần đầu (như `player_combat_state`), có script migrate chạy lại không trùng.

## 3. Thiết kế

### C1. Thưởng theo cấp (sửa chung)

Công thức: `tu vi = max(số cũ, tỉ lệ × cấp² × 110)`; linh thạch `= max(số cũ, hệ số × cấp)`.

| Loại | Mỗi nhiệm vụ | Hoàn thành tất cả | Tổng tối đa |
|---|---|---|---|
| Ngày (5 NV) | 3% cấp | +5% cấp | **20% cấp / ngày** |
| Tuần (9 NV + tổng hợp) | 10% cấp | +50% cấp | ~1,4 cấp / tuần |
| Tháng (4 NV + tổng hợp) | 0,5 cấp | +1,5 cấp | 3,5 cấp / tháng |
| Chính tuyến | 5–25% cấp (đến khu 5%, diệt quái 12%, hạ boss / đột phá 25%) | — | 1 lần duy nhất |

So sánh: 20% cấp/ngày ≈ hạ thêm 0,2 × cấp con quái cùng cấp (cấp 300 ≈ 62 con) ⇒ có ích nhưng không thay thế luyện quái.
Chính tuyến tính theo cấp người chơi nhưng **không vượt trần cấp của chương** (cấp cao làm lại chương đầu không nhận thưởng lớn).
Mọi con số nằm trong `class/config/nhiemvu_reward_rules.json` — chỉnh không cần sửa code.

Chống lạm dụng: nhiệm vụ "diệt quái" ngày/tuần chỉ tính **quái cấp ≥ cấp mình − 15** (quái thấp hơn không tính).

### C2. Chính tuyến tới cấp 500 (thêm 10 chương)

Mỗi chương gắn **một khu vực**, dẫn người chơi đi đúng lộ trình đợt B và dạy dần các hệ mới:

| Chương | Tên | Cấp | Khu | Dạy thêm |
|---|---|---|---|---|
| 4 | Viêm Dương Khởi Hành | 50–60 | Viêm Dương Thành / Thông Thiên Phủ | Mua bí tịch Thiên Cơ Các, dùng **Tuyệt Kỹ** |
| 5 | Tịch Dương Phong Ba | 60–78 | Tịch Dương Thành / Thiên Thủy Cốc | Vận công công pháp, cường hóa +8 |
| 6 | Tây Lương Sa Mạc | 78–92 | Tây Lương Địa | Hạ Boss·Tây Lương Sa Vương |
| 7 | Hoa Quả Sơn | 92–106 | Hoa Quả Sơn | Kích hoạt **Cộng Hưởng** bậc 1 |
| 8 | Hoang Thiên Lý | 106–150 | Hoang Thiên Lý | **Tuyệt Chiêu** (võ công cấp 10) |
| 9 | Hồi Đầu Ngạn | 152–200 | Hồi Đầu Ngạn | Đột phá cảnh giới |
| 10 | Hồng Hoang Cổ Địa | 202–250 | Hồng Hoang Cổ Địa | Công pháp Viên Mãn |
| 11 | Bắc Minh Hải Vực | 252–300 | Bắc Minh Hải Vực | Phá thế Boss tụ lực |
| 12 | Côn Lôn – Vạn Yêu | 302–400 | Côn Lôn Tiên Sơn, Vạn Yêu Lâm | Cộng Hưởng bậc 2 |
| 13 | Cửu U – Thiên Ngoại | 402–500 | Cửu U Ma Vực, Thiên Ngoại Thiên | Hạ Boss·Thiên Ngoại Thiên Chủ |

Mỗi chương 6–8 nhiệm vụ (tổng **~70 NV mới**), theo khuôn: *đến khu → diệt quái dải cấp → diệt Tinh Anh → thu vật phẩm rơi → dùng hệ mới → hạ Boss khu → đột phá cấp mốc*.

Loại nhiệm vụ mới cần thêm móc (nhỏ):

| Loại | Móc ở đâu | Điều kiện |
|---|---|---|
| `reach_map` | `game/nowmid.php` khi vào map | vào map thuộc khu X |
| `kill_monster` | `game/pve.php` (đã có móc kill, thêm `monster_id`) | diệt N con quái mã X |
| `kill_boss_id` | `game/boss.php` (đã có móc boss, thêm `boss_id`) | hạ boss mã X |
| `use_move` | `pve.php` / `boss.php` khi ra Tuyệt Kỹ / Tuyệt Chiêu | ra thế bậc ≥ X |
| `learn_book` | `game/thiencoccac.php` khi mua | mua 1 bí tịch |
| `conghuong` | `game/conghuong.php` khi mở trang (tính lại) | đạt bậc Cộng Hưởng ≥ X |

Chương 1–3 giữ nội dung, chỉ đổi thưởng theo C1.

### C3. NPC nhiệm vụ cho 9 khu mới / trống (chỉ thêm dữ liệu)

Mỗi khu 27, 28, 30–36 thêm **1 NPC** ở map cửa vào khu, mỗi NPC 3 nhiệm vụ dùng đúng hệ `renwu` có sẵn (không sửa code):

| Nhiệm vụ | Kiểu `renwu` | Nội dung |
|---|---|---|
| Chuỗi 1 | chính (rwlx 3) | Diệt 30 quái chủ lực của khu |
| Chuỗi 2 | chính, nối sau chuỗi 1 | Diệt 40 quái map giữa khu (không bắt nộp vật phẩm để khỏi mất đồ) |
| Ngày | ngày (rwlx 2) | Diệt 20 Tinh Anh map cuối khu — làm lại mỗi ngày |

⇒ **9 NPC, 27 nhiệm vụ**, có lời thoại/giới thiệu khu theo lore. Thưởng theo cấp khu (C1). Ghi qua `sql/migrate_dot_c.php` (`mid.mnpc`) + `npc_config.json` + `renwu_config.json`.

### C4. Thưởng trực tuyến (mới)

- Tính thời gian **đang hoạt động** trong ngày: mỗi request cộng thêm khoảng cách tới request trước nếu ≤ 5 phút (treo máy không bấm gì không được tính).
- Mốc trong ngày: **10 · 30 · 60 · 120 · 240 phút**, bấm nhận ở tab mới "Trực Tuyến" trong trang Nhiệm Vụ.
- Thưởng: linh thạch (2/4/6/8/10 × cấp) + Phục Thương Đan ×2 / Cường Hóa Thạch ×1 / 20 thể lực / Cường Hóa Thạch ×2 / **3 ma thạch**.
- Bảng mới `player_online_reward(sid, day, seconds, last_ts, claimed)`.

### C5. Thưởng ngoại tuyến — Bế Quan (mới)

- Lần đầu vào game sau khi offline **≥ 1 giờ**: hiện popup "Bế quan X giờ — nhận Y tu vi".
- Tu vi = **2% cấp mỗi giờ, tối đa 8 giờ mỗi ngày** (= 16% cấp). Không tính nếu đang Tu Luyện (tránh nhận đôi). Cần cấp ≥ 10.
- Tu vi đầy (chờ Đột Phá) thì chưa cho nhận, tránh mất thưởng.
- Không có nút "nhân đôi bằng tiền nạp" (tránh P2W).
- Bảng mới `player_offline_reward(sid, pending_exp, pending_hours, day, hours_today)`; giờ offline lấy từ `game1.endtime` (đã làm mới mỗi request).

## 4. Triển khai & kiểm thử

| Bước | File | Ghi chú |
|---|---|---|
| 1 | `class/function/nhiemvu.php` | hàm `nhiemvu_scale_reward()`, lọc cấp quái cho NV kill ngày/tuần, 6 loại NV mới |
| 2 | `class/config/nhiemvu_main_config.json` | thêm chương 4–13 |
| 3 | `npc_config.json`, `renwu_config.json`, `sql/migrate_dot_c.php` | 9 NPC, 27 NV |
| 4 | `class/function/online_reward.php`, `game/nhiemvu.php` (tab Trực tuyến), `game.php` (đếm giờ + popup bế quan) | |
| 5 | Móc sự kiện ở `nowmid.php`, `pve.php`, `boss.php`, `thiencoccac.php`, `conghuong.php` | mỗi chỗ 1–3 dòng |
| 6 | `tests/test_dot_c_*.php` + chạy thật trên DB thật (cloud) | |
| 7 | Sinh dữ liệu bằng `tools/gen/dot_c_build.py` (chạy lại cho cùng kết quả) | như đợt A/B |

## 5. Quyết định (2026-10-28)

Chủ dự án đồng ý cả 4 điểm, yêu cầu **không để thưởng nhiều quá** ⇒ mọi mức đã giảm ~một nửa so với đề xuất ban đầu (bảng C1, bế quan 2%/giờ, mốc 240' = 3 ma thạch).

## 6. Đã triển khai

| Hạng mục | File |
|---|---|
| Luật thưởng (mọi con số) | `class/config/nhiemvu_reward_rules.json` |
| Thưởng theo cấp, lọc cấp quái, 6 loại NV mới, chống nhận đúp | `class/function/nhiemvu.php` |
| Chính tuyến chương 4–13 (67 NV) | `class/config/nhiemvu_main_config.json` (sinh bởi `tools/gen/dot_c_build.py`) |
| 9 NPC (mã 40–48) × 3 NV (mã 70–96) | `npc_config.json`, `renwu_config.json`, `sql/dot_c_data.json`, `sql/migrate_dot_c.php` |
| Trực tuyến + Bế Quan | `class/function/online_reward.php`, `game.php` (đếm giờ, tính bế quan lúc đăng nhập), tab Trực Tuyến ở `game/nhiemvu.php`, nhắc ở Đại Sảnh |
| Móc sự kiện | `nowmid.php` (đến khu + đột phá bù), `pve.php` (mã quái, Tuyệt Kỹ), `boss.php` (tên boss, Tuyệt Kỹ), `thiencoccac.php` (mua bí tịch), `conghuong.php` (bậc Cộng Hưởng) |
| Sửa kèm | đường dẫn config `__DIR__ . '../../config'` thiếu `/` ở `renwu.php`, `daoju.php`, `yaodan.php`, `wugong.php` (Windows vẫn chạy, Linux/VPS thì hỏng) |
| Kiểm thử | `tests/test_dot_c.php` (cần DB) — ALL PASS; chụp màn hình thật: tab Chính Tuyến / Trực Tuyến, Bảng Nhiệm Vụ, trang NPC |

Ghi chú: Đại Sảnh hiện không liệt kê NPC trên bản đồ (từ bản gốc) — người chơi gặp NPC qua **Tin Tức → Bảng Nhiệm Vụ → Gặp NPC**.

## 7. Cách chạy trên máy bạn / server

```
php5.6 sql\migrate_dot_b.php --dry-run   (nếu chưa chạy đợt B)
php5.6 sql\migrate_dot_b.php --apply
php5.6 sql\migrate_dot_c.php --dry-run
php5.6 sql\migrate_dot_c.php --apply
php5.6 tests\test_dot_c.php
```
Bảng `player_online_reward`, `player_offline_reward` tự tạo khi game chạy lần đầu.
