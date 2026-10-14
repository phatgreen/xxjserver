# Tài liệu: Thiên Phú — Phủ Các sưu tầm 18 thiên phú (game/tianfu.php)

**Nguồn chính, cập nhật lần cuối: 2026-10-14.**
Spec: `docs/superpowers/specs/2026-10-12-thien-phu-phu-cac-design.md` (commit `ab2870a`)
Plan: `docs/superpowers/plans/2026-10-12-thien-phu-phu-cac.md` (12 task, 96 step, commit `7309de2`)
Khung 12 mục lấy theo `docs/UI_HUYETMACH.md`. Mọi số chưa đo bằng lệnh thật đều ghi **CHƯA**.

---

## 0. Trả lời nhanh: tính năng này để làm gì, có tác dụng thật không

Thiên Phú cũ (2006-style) là **1 con số ngẫu nhiên lúc tạo nhân vật**: 6 cột `tf tfgj tfhp tffy
tfbj tfsb` trên `game1`, cộng thẳng vào thuộc tính, người chơi không nhìn thấy, không tăng được,
không chọn được. Bản mới đổi thành **Phủ Các sưu tầm**: 18 thiên phú bẩm sinh chia 6 nhóm, mỗi ô
khai mở bằng **Khai Phú Đan** (djid 114), có 10 phẩm chất (Phàm → Đạo), nâng cấp bằng điểm `tf` +
Linh Thạch, đột phá phẩm, và **4 Phú Vi** để chọn ô nào thực sự phát huy. Người chơi thấy rõ mình
đang có gì, thiếu gì, và chọn được hướng đi (tu luyện / rơi đồ / luyện đan / cường hóa / đột phá /
thể lực).

Bảy "cổng vận khí" (`gates`) là duy nhất mà Thiên Phú tác động vào game — nó KHÔNG cộng vào
thuộc tính chiến đấu nữa (xem §4.3).

### Có tác dụng thật — đã đo trên đường dẫn thật

| Cổng | Cắm vào đâu (file:dòng) | Bằng chứng | Ngày |
|---|---|---|---|
| `exp_cultivate` | `game/xiulian.php:113` — nhân NGOÀI chuỗi `min()` của linh khí/giây | `tests/test_tianfu_gates.php` (unit) | 2026-10-14 |
| `exp_monster` | `game/pve.php:709`, `game/boss.php:604` | `tests/test_tianfu_gates.php` | 2026-10-14 |
| `drop` | `game/pve.php:470/490/519`, `game/boss.php:458/485/503` — nới ngưỡng `mt_rand` | unit + đọc lại 6 dòng `luck_gate` | 2026-10-14 |
| `alchemy` | `class/function/luonden.php:348` | unit — **NHƯNG đường thật không chạy qua đây, xem §8.4** | 2026-10-14 |
| `forge_cost` | `game/zbinfo.php:41` và `:149` — giảm Linh Thạch cường hóa | unit | 2026-10-14 |
| `stamina` | `game.php:460` → `class/player.php:351-352`; `cron/stamina_recovery.php:57` | unit + `settle_stamina()` | 2026-10-14 |
| `breakthrough` | `game/tupo.php:118,122` — cộng điểm vào `$rate` trước `min(99, …)` | unit | 2026-10-14 |
| Chiến lực | `class/player.php:433` — `tianfu_power` trọng số **1** | `combat_power()` chạy thật, `zhuangtai` hiện 148.721 | 2026-10-14 |
| Xếp hạng | `game/paihang.php:19` — `+ tianfu_power` | `tests/test_tianfu_gates.php` | 2026-10-14 |

**Nhưng**: trên DB local hiện tại `player_tianfu` có **0 dòng** (§9.1) — chưa nhân vật nào thực sự
khai phú, nên mọi cổng đang trả 0 khi vào game thật. Bằng chứng ở mức **hàm + test + trang render**,
chưa phải **combat log trong game thật**.

### Phần phải nói thẳng (đừng tin số nếu chưa đọc 4 mục này)

1. **Ngưỡng rơi đồ nhân ĐÔI, mất 0,33 điểm % làm tròn.** `tianfu_luck_gate()`
   (`class/function/tianfu.php:882-887`) bọc `intval(round())` một lớp, ngoài nó là
   `huyetmach_combat_luck_gate()` (`class/function/huyetmach.php:1142`) bọc thêm một lớp nữa.
   Với ngưỡng gốc 120 và hai hệ số 1,15 × 1,30:
   `round(round(120 × 1,15) × 1,30) = round(138 × 1,30) = 179` → **+49,17%**, không phải
   `1,15 × 1,30 = +49,5%` như tính tay. Hai lần `round()` ăn mất 0,33%.
   **CHƯA đo trong game thật** — đây là suy ra từ mã nguồn, chưa phải combat log.
2. **Trần thật ≠ `cap_percent = 30` trong config.** `tianfu_percent_calc()`
   (`class/function/tianfu.php:187-191`) clamp 30 đúng, nhưng host formula clamp LẦN HAI:
   luyện đan `min(95, …)` (`class/function/luonden.php:349`), đột phá `min(99, …)`
   (`game/tupo.php:122`). Nghĩa là +30 điểm % chỉ còn tác dụng khi tỉ lệ gốc ≤ 65 (đan) / 69
   (đột phá). **CHƯA đo phân bố tỉ lệ gốc thực tế.**
3. **Phú Vi làm thay đổi hoàn toàn hiệu ứng, nhưng `tianfu_power` chỉ nặng 1 điểm chiến lực.**
   Ô để trong Phủ Các (slot 0) tính vào `total_lv` (mốc) nhưng KHÔNG tính vào 7 cổng
   (`class/function/tianfu.php:348: if slot <= 0 continue`). Đã xác nhận `tianfu_power` CÓ nằm
   trong `$weights` tại `class/player.php:433` với trọng số 1 — thấp nhất bảng (so với `ubj` 30,
   `huyetmach_awaken_stage` 600).
4. **Cột `tfsb` vẫn là cột chết.** 0 nơi đọc. Đã chốt **không** nối vào vận khí vì trùng nhịp
   Né Tranh của Ẩm Kieu Mach — `huyetmach_combat_roll_dodge()`
   (`class/function/huyetmach.php:1112`, trần né 15%). Giữ cột, không drop.

---

## 1. Bối cảnh

- Thiên phú cũ sinh ngẫu nhiên lúc tạo nhân vật và **cộng thẳng vào 6 chỉ số chiến đấu**
  (`tfgj` công, `tfhp` khí huyết, `tffy` phòng ngự, `tfbj` bạo kích, `tfxx` tu vi, `tfsb` vận khí).
  Người chơi không có màn nào để xem; nó chỉ hiện nhờ trong `zhuangtai`.
- Từ 2026-10-06 (quyết định "khởi đầu nhân vật trắng", `project-khoi-diem-trang`) việc random lúc
  tạo bị bỏ → thiên phú cũ gần như luôn = 0 trên nhân vật mới, chỉ nhân vật cũ còn mang.
- Ba hệ thống đã làm trước đây (Huyết Mạch, Linh Căn, Nhục Thân) đều theo mẫu: **màn chính +
  màn sưu tầm + màn nâng phẩm + 4 tab điều hướng + hiệu ứng phân bậc**. Thiên Phú là hệ thứ tư,
  làm cùng khuôn để người chơi không phải học lại.
- Phạm vi vòng này: **chỉ local**. Production chưa migration, chưa copy code
  (`docs/DEPLOY_PRODUCTION_THIENPHU.md` là runbook trên giấy).

---
## 2. File đã sửa / đã tạo

| Đường dẫn | Vai trò | Dòng | Git |
|---|---|---:|---|
| `class/config/tianfu_config.json` | 6 nhóm · 18 thiên phú · 10 phẩm · 7 cổng · 4 Phú Vi · mốc · giá · `cap_percent` · đan 114 | 79 | mới (Task 1) |
| `class/function/tianfu.php` | **43 hàm** thuần `namespace player` — mọi logic Thiên Phú nằm đây | 931 | mới (Task 1-6) |
| `sql/sql_tianfu.sql` | `CREATE TABLE IF NOT EXISTS` 2 bảng + 4 cột `game1.tianfu_*` | — | mới (Task 2) |
| `sql/migrate_tianfu.php` | Hoàn điểm `tf` cũ + tặng 1 Khai Phú Đan, `--dry-run` / `--apply` / `--sid=` | 164 | mới (Commit D `be5f61c`) |
| `game/tianfu.php` | Màn chính `.tp-*` | 296 | mới (Task 8) |
| `game/tianfu_collection.php` | Phủ Các `.tpc-*` — lưới 18 ô | 261 | mới (Task 9) |
| `game/tianfu_open.php` | Khai Phú `.tpo-*` | 115 | mới (Task 10) |
| `game.php` | 3 route `:349-351`; `:460` truyền `stamina` vào `settle_stamina()` | — | sửa |
| `class/player.php` | 8 property `:160-167`; whitelist `:320`; `:351-352` nhịp hồi thể lực; `:433` trọng số chiến lực; `:869-897` hydrate + tự canh mirror. **Đã xoá 6 dòng 588-593** (Phương án A) | — | sửa |
| `game/zhuangtai.php` | `:360-365` dữ liệu chủ phú; `:426-427` 2 thẻ "Chủ phú" + "Phú Vị" | — | sửa |
| `game/xiulian.php` `game/pve.php` `game/boss.php` `game/tupo.php` `game/zbinfo.php` `class/function/luonden.php` `cron/stamina_recovery.php` `game/paihang.php` | 7 cổng + chiến lực + xếp hạng | — | **8 file này lần đầu tiên vào git** (commit `35823e8` / `5930630`, 2026-10-14) — trước đó repo không theo dõi chúng |
| `css/mystyle.css` | Khối `.tp-*` / `.tpc-*` / `.tpo-*` tại `:7422-7613`; **đã xoá 425 dòng `.talent-*`** (4062-4486) | 7613 | sửa |
| `images/tianfu/*.jpg` | 6 ảnh nhóm + 1 ảnh Phủ Các | 7 file | mới (Task 7) |
| `tests/test_tianfu_*.php` | 9 bộ: config · schema · bonuses · open · lvup · slot · gates · pages · migration | 1182 | mới (migration vào git ở Commit D `be5f61c`) |
| `docs/img/thienphu/*.png` | 18 ảnh bằng chứng | — | mới/sửa (Commit D `be5f61c`) |
| `tongquan.php` | Mục "Thiên Phú (Phủ Các)" `:446-515`, badge 43, 5 bảng lọc/được sắp xếp | 976 | sửa (Commit C `bce3fb5`) |

---

## 3. Cấu trúc trang — 4 màn, 1 thanh tab

Bốn tab giống hệt khuôn Linh Căn / Huyết Mạch (`game/tianfu.php:290-295`):

```
🌟 Thiên Phú | 🗄 Phú Các | ⚗ Khai Phú | 👤 Nhân Vật
```

| Màn | Route | File | Nội dung |
|---|---|---|---|
| **Thiên Phú** (chủ) | `cmd=tianfu&grp=1..6` | `game/tianfu.php` | Header + tên chủ phú; rail 6 nhóm (mỗi nhóm `x/3` ô đã có); ảnh tôn giả theo nhóm (tỉ lệ 3:4); 7 cổng "Kỹ Năng Kích Hoạt" kèm `trần 30%`; 4 thẻ Phú Vi; bảng mốc tổng Lv |
| **Phủ Các** (sưu tầm) | `cmd=tianfu_collection&id=1..18` | `game/tianfu_collection.php` | Banner Phủ Các; lưới 18 ô (3 cột mobile / 6 cột desktop); panel chi tiết theo ô đang chọn; nút Nâng Cấp / Đột Phá / gán–tháo Phú Vi |
| **Khai Phú** (uống đan) | `cmd=tianfu_open` | `game/tianfu_open.php` | Số đan trong túi + số lần đã khai; nút "Khai Mở"; bảng 10 phẩm (danh xưng · trần Lv · tỉ lệ); "Mua đan" |
| **Nhân Vật** | `cmd=zhuangtai` | `game/zhuangtai.php:426-427` | 2 thẻ: "☯ Chủ phú" (tên + phẩm, màu theo phẩm) và "🎖 Phú Vị" (`đã kích hoạt / 4`) |

### Bản đồ selector → file:dòng

| Selector | Xuất hiện ở | Nguồn |
|---|---|---|
| `.tp-wrap.tp-fx<N>` | `game/tianfu.php:125` | `tianfu_fx_class()` `class/function/tianfu.php:196` |
| `.tp-rail` | `game/tianfu.php:139` | `tianfu_groups()` config `:3-10` |
| `.tp-stage` / `.tp-stage-img` | `game/tianfu.php:168-169` | `groups[g].img` → `images/tianfu/nhom-*.jpg` |
| `.tp-attr` × 7 | `game/tianfu.php:212-215` | `tianfu_bonuses()` `:333`, đơn vị `gates[g].unit` |
| `.tp-ms-fill` (thanh mốc) | `game/tianfu.php:226` | `tianfu_milestone()` `:290` |
| `.tp-slot` × 4 | `game/tianfu.php:244-262` | `tianfu_slot_defs()` config `:70-75`, `slots[i].ulv` |
| `.tpc-grid` / `.tpc-cell` | `game/tianfu_collection.php:138-161` | `tianfu_owned()` `:223` (1 query cho 18 ô) |
| `.tpc-detail` | `game/tianfu_collection.php:165-203` | `tianfu_def()` + `tianfu_lvup_rows()` + `tianfu_break_rows()` |
| `.tpo-table` (10 phẩm) | `game/tianfu_open.php:86` | `qualities` config `:41-50` |
| `.profile-stat` chủ phú | `game/zhuangtai.php:426` | `$player->tianfu_name/_icon/_color` hydrate `class/player.php:888-896` |

**Landmine route**: token `?cmd=` **dùng một lần** (`class/encode.php`) — mọi URL phải `encode()`
lại ở mỗi lần render, không được lưu chuỗi đã encode. Xem `game/tianfu_collection.php:117-121`.

---
## 4. Logic server-side (`class/function/tianfu.php`, 931 dòng, 43 hàm)

### 4.1 Chữ ký hàm đã produce (nhóm theo việc)

| Nhóm | Hàm | Chữ ký / trả về | Dòng |
|---|---|---|---:|
| Đọc config | `tianfu_config_path()` `tianfu_config_all()` `tianfu_cfg_key($key)` | `array` (static cache, `json_decode` 1 lần) | 13-45 |
| | `tianfu_groups()` `tianfu_defs()` `tianfu_def($id)` `tianfu_gate_def($gate)` `tianfu_gate_name` `tianfu_gate_unit` | `array｜string｜false` | 47-82 |
| | `tianfu_quality_def($q)` `tianfu_quality_name` `tianfu_quality_color` `tianfu_quality_icon` | `array｜string` | 84-109 |
| Trần & giá | `tianfu_cap_percent()` | `float` = 30.0 | 113 |
| | `tianfu_lv_cap($q)` `tianfu_lv_cost($lv)` `tianfu_break_cost($q)` | `int` / `array{tf,linh_thach}` / `array{tf,linh_thach,ulv_require}｜false` | 121-148 |
| Phú Vi | `tianfu_slot_defs()` `tianfu_slot_open($ulv)` | `array` / `array(int)` các số thứ tự đã mở | 152-164 |
| Toán | `tianfu_contributions($def)` | `gate => per_lv` (ô nhóm Ngộ Tinh trả **2** cổng: `exp_cultivate` + `exp_monster` qua `per_lv_sub`) | 169 |
| | **`tianfu_percent_calc($lv,$perLv,$mult,$milestonePct)`** | `round(min(30, lv·perLv·mult·(1+ms/100)), 2)` | 187 |
| | `tianfu_fx_class($quality)` | `int 0..5` (q≤2→0, q≤4→1, q=5→2, q=6→3, q≤8→4, else 5) | 196 |
| | `tianfu_power_of($q,$lv)` | `q·150 + lv·8` (điểm chiến lực) | 279 |
| | `tianfu_milestone($total_lv)` | `array{name,percent,next_name,next_percent,next_lv}` | 290 |
| | **`tianfu_bonuses($sid,$dblj)`** | **15 key**: 7 cổng float + `total_lv` `milestone_name` `milestone_percent` `next_milestone_*` `activated_count` `power` | 333 |
| DB | `tianfu_owned($sid,$dblj)` | 18 ô luôn đủ, `LEFT JOIN` 1 query | 223 |
| | `tianfu_activated($sid,$dblj)` | `slot => row` chỉ ô có `slot > 0` | 256 |
| | `tianfu_log($sid,$kind,$tf,$from_q,$to_q,$detail,$dblj)` | `kind ∈ open｜lvup｜break｜slot｜migrate`, `detail` cắt 200 ký tự | 379 |
| | `tianfu_recalc_mirror($sid,$dblj)` | `UPDATE game1` 4 cột `tianfu_id/quality/lv/power` | 394 |
| Khai phú | `tianfu_weighted_pick($weights)` `tianfu_open($sid,$dblj)` `tianfu_open_count` `tianfu_player_wallet` | `string｜false` / `array` / `int` / `array` | 421-548 |
| Nâng & đột phá | `tianfu_lvup($sid,$dblj,$id)` `tianfu_break(...)` `tianfu_lvup_rows` `tianfu_break_rows` | `array{ok,msg}` / `array{ok,no,msg}` | 556-745 |
| Phú Vi | `tianfu_slot_conflict($sid,$dblj,$id,$slot,$act)` `tianfu_slot_set(...)` | `int｜false` / `array` | 748-876 |
| Cổng | `tianfu_luck_gate($pct,$gate)` `tianfu_forge_cost($pct,$cost)` `tianfu_stamina_interval($pct,$iv=300)` `tianfu_bonus_pct($sid,$dblj,$gate)` | `int` / `int` (sàn 1) / `int` (sàn 30 s) / `float` | 882-931 |

### 4.2 Một hàm tổng hợp duy nhất

Mọi nơi (UI, `getplayer`, 7 cổng) đều đọc qua `tianfu_bonuses()`. Một cổng có thể nhận đóng góp từ
nhiều ô **cùng cổng** (nhóm Ngộ Tinh có 3 ô cùng `exp_cultivate`), nên:

```
raw[g] = Σ (lv_i · per_lv_i · mult_i)          # chỉ tính ô có slot > 0
out[g] = tianfu_percent_calc(1, raw[g], 1, milestone_percent)
```

Ba tham số đầu `(1, raw, 1)` để **dùng lại đúng một công thức** thay vì viết công thức thứ hai
(chú thích `class/function/tianfu.php:326-331`).

### 4.3 Đường vào nhân vật — KHÔNG còn cộng thuộc tính

- `class/player.php:873-886`: hydrate `tianfu_bonuses()` → `$player->tianfu_bonus`; nếu `power`
  trong `game1` lệch với bảng thật thì **tự gọi `tianfu_recalc_mirror()`** và ghi đè 4 property.
- **Đã xoá** 6 dòng cũ (588-593) kiểu `if ((int)$player->tfhp != 0) $player->umaxhp += …`.
  Đó là "Phương án A" — Thiên Phú mới không đụng 6 chỉ số chiến đấu nữa. Xem §7.4.
- 6 cột cũ `tf tfgj tfhp tffy tfbj tfsb` vẫn còn trên `game1` nhưng `tf` chỉ còn là **thước quỹ
  điểm nâng cấp**, 5 cột kia = 0 sau migration.

### 4.4 Trần thật ≠ con số trong config

| Cổng | Clamp của `tianfu_percent_calc` | Clamp của host | Hệ quả |
|---|---|---|---|
| `exp_cultivate` | 30% | không có — `game/xiulian.php:113` nhân **ngoài** chuỗi `min(1.3,…)` | +30% là thật, không bị trần 1,3 của Linh Căn/Thể Chất nuốt |
| `exp_monster` | 30% | `game/pve.php:709`, `game/boss.php:604` | cần đọc lại từng dòng nhân — **CHƯA đo end-to-end** |
| `drop` | 30% | 2 lớp `intval(round())` (§0.1) | 120 → 179 (+49,17%) |
| `alchemy` | 30 điểm % | `min(95, max(5, …))` `class/function/luonden.php:349` | chỉ còn tác dụng khi tỉ lệ gốc ≤ 65 — **và xem §8.4** |
| `forge_cost` | 30% | `max(1, …)` `:901` | giá không về 0 (chống cường hoá miễn phí) |
| `stamina` | 30% | `max(30, …)` giây `:916` | chu kỳ 300 s → ngắn nhất 210 s |
| `breakthrough` | 30 điểm % | `min(99, max(5, …))` `game/tupo.php:122` | trần mềm 99% |

---

## 5. Ảnh đã sinh

### Ảnh trong game (`images/tianfu/`) — 7 file, dùng thật

| File | Bytes | md5 (12) | Dùng ở |
|---|---:|---|---|
| `nhom-ngo-tinh.jpg` | 104.994 | `5d4a3b579351` | `.tp-stage` nhóm 1 |
| `nhom-co-duyen.jpg` | 136.255 | `0f3a65f7ba67` | nhóm 2 |
| `nhom-dan-dao.jpg` | 109.335 | `f2828ec6acd7` | nhóm 3 |
| `nhom-tai-van.jpg` | 108.810 | `d29327783f61` | nhóm 4 |
| `nhom-canh-gioi.jpg` | 132.388 | `bab8bfd5ae3a` | nhóm 5 |
| `nhom-than-suat.jpg` | 130.200 | `99f682eb5e20` | nhóm 6 |
| `phu-cac.jpg` | 189.634 | `f3efb74f3207` | `.tpc-cover` (banner Phủ Các) |

Đường dẫn ghép `groups[*].img` trong config; `game/tianfu.php:169` nối
`?v=<?= $tpImgVer ?>` để phá cache — **đổi ảnh phải bump `$tpImgVer`** (`game/tianfu.php:7`, hiện
`2026101201`).

### Ảnh bằng chứng (`docs/img/thienphu/`) — 18 file, chỉ để đọc lại

`main-390-q1` `main-390-q8` `main-840-q8` (màn chính, Task 8) · `cac-390` `cac-390-lock`
`cac-840` (Phủ Các, Task 9) · `open-390` `open-840` `open-nodan` `open-result` (Khai Phú, Task 10) ·
`zhuangtai-390` `zhuangtai-840` (Trang thái, Task 11) · `tianfu-390/840` `tianfu_collection-390/840`
`tianfu_open-390/840` (chụp lại sau migration + sửa CSS, Task 12 Step 7).
Kích thước 204 KB → 1,50 MB; ảnh 840 px luôn > 490 KB (nền full).

---

## 6. Ràng buộc CSS phải giữ — **MỒI, dễ tái phạm**

Khối Thiên Phú: `css/mystyle.css:7422-7613`. Đầu khối có ghi 3 lệ, lặp lại ở đây:

1. **`a:link` trong `css/gamecss.css` có đặc tính (0,1,1)** → mọi điều khiển dạng `<a>` của
   Thiên Phú **phải** có nhóm `.main a.tp-*` / `.main a.tpc-act` (0,2,1), không thì bị đổi màu +
   margin hệ thống. Bằng chứng: `css/mystyle.css:7593`
   `.main a.tpc-act,.main a.tpc-act:link,.main a.tpc-act:visited,.main a.tpc-act:hover{…}`.
2. **`.main button` = (0,1,1)** → mọi nút submit phải đặt `.main .tp-act` / `.main .tpc-act` /
   `.main .tpo-act` (0,2,0) đè lên (`:7592`, `:7611`).
3. **Không đặt `max-width` lên `.tp-stage` / `.tpc-grid`** — chỉ lên `.tp-wrap` / `.tpc-wrap` /
   `.tpo-wrap`. Mobile mặc định 660 px, desktop `@media (min-width:601px)` → 840 px (`:7563`,
   `:7594`, `:7613`).
4. **6 bậc hiệu ứng** `--tp-tier/--tp-glow/--tp-dur/--tp-aura` (`:7431-7440`); bậc thấp nhất
   `tp-fx0` vẫn phải thấy quầng sáng (`--tp-glow:4px`), **không** dùng `duration:0s` vì Chrome giữ
   keyframe 0% — dùng `animation:none`.
5. **Trang game có `zoom:.75`** → khi đo bằng Chrome headless phải đặt `zoom=1` trong iframe trước,
   không số đo sẽ sai (§9).
6. Nhãn đơn vị: `unit = '%'` viết liền (`0%`), `unit = 'điểm %'` phải có dấu cách (`0 điểm %`) —
   xem `game/tianfu.php:214` và `game/tianfu_collection.php:184`.

---
## 7. Bug đã tìm ra và cách sửa

| # | Triệu chứng | Nguyên nhân | file:dòng | Cách sửa |
|---|---|---|---|---|
| 7.1 | Hiện `0điểm %` dính liền (chỉ ra khi **nhìn ảnh**, không ra khi đọc code) | `unit` ngắn (`%`) không cần cách, unit dài (`điểm %`) thì cần | `game/tianfu.php:214`, `game/tianfu_collection.php:184` | `<?= $unit === '%' ? '' : ' ' ?>` trước unit |
| 7.2 | Ở 390 px, nhãn "Sưu tầm" nằm **chen giữa** dòng stat đã wrap → đọc thành chuỗi lộn xộn | `.tpc-head` là flex `space-between` + `align-items:center`; vế phải dài 291 px trong khi vế trái 62 px, tổng 353 px > 330 px | `css/mystyle.css:7570` | mặc định `flex-direction:column;align-items:flex-start`, trả về hàng ngang trong `@media (min-width:601px)` (`:7594`) |
| 7.3 | Banner "18 thiên phú bẩm sinh" sát lề phải 1 px (đo: `sw=328 cw=328`) | flex không cho phép thụt dòng | `css/mystyle.css:7567` | thêm `flex-wrap:wrap` (không đổi hình ở 840) |
| 7.4 | Nhân vật cũ mất chỉ số sau khi dựng hệ thống mới | 6 dòng `if ((int)$player->tf* != 0) $player->… += …` cộng cột cũ vào thuộc tính, trong khi cột đã bị migration đưa về 0 → **kiến nghị bỏ** | `class/player.php:588-593` (đã xoá) | Phương án A — chủ dự án chốt 2026-10-14: "acc test thôi, không quan trọng", chấp nhận nerf |
| 7.5 | `tests/test_tianfu_migration.php` FAIL 2 mục (`apply exit 0` nhận `1`) | `sql/migrate_tianfu.php` bản theo plan kiểm tra hậu kỳ **toàn DB**, trong khi `--sid=` chỉ giới hạn 1 nhân vật → đối chiếu sai | `sql/migrate_tianfu.php` (block `$c1..$c4`) | thêm `$sc = ($ONLY !== null) ? ' AND sid = …' : ''` nối vào 4 câu kiểm tra; **sửa script, không nới test** |
| 7.6 | Trang `qydt` (ngay sau khối bị cắt) có thể vỡ theo | Xoá 425 dòng `.talent-*` trong `css/mystyle.css` (4062-4486) | `css/mystyle.css` | xác minh `0 insertions / 425 deletions`, cân bằng ngoặc 4645/4645, `grep -c '\.talent'` = 0, **và chụp lại `qydt` + 3 trang Thiên Phú** |
| 7.7 | Ảnh chụp ra trang đăng nhập thay vì trang game | `sid` lấy 8 ký tự đầu in ở bảng legacy; `sid` thật là **32 ký tự hex** | — | query `game1.sid` đầy đủ trước khi dựng URL |
| 7.8 | `getplayer()` fatal "Call to a member function prepare() on null" | thiếu tham số 2 | `class/player.php` `getplayer($sid, $dblj)` | include `pdo.php` và truyền `$dblj` |
| 7.9 | `combat_power()` báo 0 | `method_exists('\player','combat_power')` sai — đây là **hàm**, không phải method | `class/player.php:425` | gọi thẳng `\player\combat_power($p)` |
| 7.10 | Chrome headless trả ảnh 0 byte, tên file cụt | `${spec%%,*}` trên `"390:500,1500"` sinh `390:500`; Windows cắt tên file ở dấu `:`; `--window-size` tối thiểu 500 px | — | vòng lặp `for w in 390 840` + iframe harness `_lcshot.html` (§9) |

---

## 8. Bug CŨ có sẵn — phát hiện nhưng **CHƯA sửa** (chủ dự án quyết)

| # | Bug | Chờ | Vì sao chưa sửa |
|---|---|---|---|
| 8.1 | `upplayerlv()` vẫn cấp 6 cột `tf*` (`tfgj tfhp tffy tfbj tfxx tfxy`) theo `shenfen` mỗi lần lên cấp | `class/player.php:1201` (hàm), khối `UPDATE` tại `:1232-1256` | Sau Phương án A, 6 cột này **không còn tác dụng** nhưng vẫn tồn tại → tốn DB, gây hiểu nhầm cho người đọc code sau. Cần chốt: xoá hẳn hay giữ để tích luỹ `tf` cho đợt sau. *(Plan ghi `:1199-1215` — sai, số thật đo lại hôm nay là `:1232-1256`.)* |
| 8.2 | `usedaoju()` giới hạn mỗi loại đan **tối đa 2 lần** | `class/player.php:2059`, chốt tại `:2083-2089` | Cần cột `djuse` mà `daoju_config.json` (109-113) không có → luồng Khai Phú Đan thực tế đang **bít**. Đã lách bằng `deledjsum(114, 1, $sid, $dblj)` trực tiếp trong `tianfu_open()`. *(Plan gọi tên hàm là `use_dj()` ở `:2058-2087` — tên và dòng đều sai.)* |
| 8.3 | `paihang.php` xếp hạng theo chiến lực cũ | `game/paihang.php:17-19` | Task 11 Step 14 đã đổi trọng số; **bảng xếp hạng nhảy hạng ngay sau migration** → người chơi thấy thứ tự đảo lộn. Phải báo trước khi deploy |
| 8.4 | **Cổng `alchemy` không có tác dụng trên đường luyện đan thật** | `class/function/luonden.php:899` trong `luonden_dung_dan()` | Đã truy vết: UI gọi `game/luonden.php:46` → `luonden_craft_v2()` (`:1058`) → `luonden_dung_dan()` (`:1112`), và hàm này tự tính `$finalSuccessRate` ở `:899` **không cộng `$tpAlchemy`**. Trong khi đó `luonden_calc_success_rate()` (`:324`, cộng cổng ở `:348`) chỉ được gọi từ `luonden_craft()` (`:595`, **hàm chết — không UI nào gọi**) và `luonden_success_text()` (`:735`, **chữ hiển thị** qua `game/luonden.php:223`). **Hệ quả: người chơi nhìn thấy tỉ lệ đã cộng Thiên Phú nhưng khi quay số lại không được cộng.** Chưa sửa vì đây là quyết định cân bằng (cộng vào Dung Đan hay đổi sang đường `calc_success_rate`) — cần chủ dự án chốt |
| 8.5 | `tfsb` là cột chết | `game1.tfsb` | Đã chốt không nối vào vận khí (trùng nhịp Né Tranh của Ẩm Kieu Mach — `huyetmach_combat_roll_dodge()`, `class/function/huyetmach.php:1112`). Giữ cột, không drop |

---

## 9. Quy trình kiểm chứng (lặp lại được)

```bash
# 0) Server dev: Apache port 80 CHET -> phai chay built-in PHP 5.6
P=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
(cd /d/XXJserver/phpstudy_pro/WWW && "$P" -S 127.0.0.1:8091 -t . ) &

# 1) Lint PHP 5.6 moi file da cham
for f in class/function/tianfu.php class/player.php game.php game/tianfu.php \
         game/tianfu_collection.php game/tianfu_open.php game/xiulian.php \
         game/pve.php game/boss.php game/tupo.php game/zbinfo.php game/paihang.php \
         game/zhuangtai.php class/function/luonden.php cron/stamina_recovery.php \
         sql/migrate_tianfu.php tests/test_tianfu_*.php; do "$P" -l "$f"; done

# 2) Toan bo Thien Phu (9 bo) — phai "ALL PASS"/"TAT CA PASS"
for t in tests/test_tianfu_*.php; do echo "== $t"; "$P" "$t" 2>&1 | tail -4; done

# 3) Migration: DUMP TRUOC, dry-run, roi moi apply
D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysqldump.exe \
  -uroot -pwww.aae.ink game game1 playerdaoju player_tianfu player_tianfu_log \
  > bak/tianfu_pre_<ngay>.sql
"$P" sql/migrate_tianfu.php --dry-run
"$P" sql/migrate_tianfu.php --apply            # co the --sid=<32hex> de do 1 nhan vat

# 4) Chup anh — Chrome CLI, KHONG dung browser-use MCP.
#    window-size < 500 px bi Chrome tu choi -> dung iframe harness 390/840 trong frame >=500:
bash E:/window/Temp/_tp_shots.sh        # (scratch, khong vao repo)
#    URL token: php E:/window/Temp/_tp_url.php "cmd=tianfu&sid=<32hex>"   (moi lan 1 token)
```

**Bẫy đồng hồ**: PHP `date()` = +8, MySQL `NOW()` = +7. Mọi mốc so với đồng hồ PHP
(`xiuliantime`, `endtime`) phải ghi bằng đồng hồ PHP, nếu không trang sẽ báo "chưa tới giờ".
**Bẫy token**: `?cmd=` dùng một lần — mỗi request phải encode lại chuỗi mới.
**Bẫy fixture**: `tests/test_tianfu_pages.php` xóa log migrate + đan 114 của sid
`e2e2e2…` (nhân vật e2e dùng chung) — chạy test xong thì số liệu migration thay đổi, xem §9.1.

### 9.1 Số đã đo (2026-10-14, DB local `game`)

| Chỉ số | Giá trị | Lệnh |
|---|---|---|
| Phiên bản MySQL | 5.5.29 | `SELECT VERSION()` |
| Nhân vật trong `game1` | **13** (plan ghi 14 — sai, xem §10) | `SELECT COUNT(*) FROM game1` |
| Dòng `player_tianfu` | **0** — chưa ai khai phú | `SELECT COUNT(*) FROM player_tianfu` |
| Tổng `tf` sau migration | **1190** (= 780 có trước + 410 hoàn). Lưu ý: 500 trong số đó là `tf` **fixture** của `E2E Test` (`tests/e2e_fixture.php` đặt sẵn 500), không phải điểm hoàn | `SELECT SUM(tf) FROM game1` |
| Dòng `player_tianfu_log kind='migrate'` | **12 lúc 16:0x** (bằng chứng `--dry-run`: `SKIP=12 SE_MIGRATE=1`) — lúc apply 15:05 là 13, xem cảnh báo fixture bên dưới | `SELECT COUNT(*) … WHERE kind='migrate'` |
| Người giữ Khai Phú Đan (djid 114) | **12** (đồng bộ với log migrate) | `SELECT COUNT(DISTINCT sid) FROM playerdaoju WHERE djid=114` |
| dry-run tổng hoàn | 410 = apply tổng hoàn | `php sql/migrate_tianfu.php --dry-run` |
| Kết quả apply (15:05) | `OK=13 FAIL=0 SKIP=0 TONG_HOAN=410 TONG_DAN_114=13` | `--apply` |
| Chạy lại lần 2 | toàn bộ `SKIP`, số liệu không đổi (idempotent) | `--apply` |
| PhátĐây `tf` | 542 (= 180 gốc + 362 hoàn) | `SELECT tf FROM game1 WHERE nick='PhátĐây'` |
| PhátĐây Bạo kích sau Phương án A | **37** (trước 91 — bảng plan ghi 61→7 là sai vì bỏ qua trang bị) | hydrate `getplayer()` |
| PhátĐây Chiến lực | 148.721 | `\player\combat_power($p)` |
| Dump an toàn | `bak/tianfu_pre_20261014_1504.sql` — 36.937 B / 218 dòng | `mysqldump …` |
| Dòng `.talent-*` đã xoá | 425 (0 insertions) | `git diff --numstat css/mystyle.css` |
| Ảnh `.tpc-head` đo ở 390 px | vế trái 62 px, vế phải 291 px, khung 330 px | harness `_tpmeasure.html` |
| 9 bộ test Thiên Phú | ALL PASS (2026-10-14 15:2x) | mục 2 ở trên |
| 25/25 bộ test `tests/test_*.php` | **ALL PASS** khi đủ môi trường (chạy lại xác nhận 2026-10-14 16:1x — `test_linhcan_schema.php` in 12 dòng PASS, 0 dòng FAIL, exit 0) | `for t in tests/test_*.php; do "$P" "$t"; done` |
| 3 bộ "FAIL" lần chạy đầu | `test_linhcan_pages.php` (thiếu `LC_BASE`), `test_nguhanh_combat.php` + `test_nguhanh_market.php` (cần server port 80) — **không phải regression** | `LC_BASE=http://127.0.0.1:8091 "$P" tests/test_linhcan_pages.php`; `"$P" -S 127.0.0.1:80 -t .` rồi chạy lại → ALL PASS |

**CẢNH BÁO FIXTURE — số migrate tụt từ 13 xuống 12 mà không có lỗi code.** Nhân vật test
`E2E Test` (sid `e2e…`, uid 987650) dùng chung cho nhiều bộ test. `tests/test_tianfu_pages.php:68` (SETUP) và `:148` (CLEANUP)
xóa có chủ đích `player_tianfu_log` + `playerdaoju djid=114` của sid này để trang Thiên Phú
hiện ra ở trạng thái "chưa khai phú". Vì vậy **sau khi chạy bộ test đó**, `--dry-run` thấy
`E2E Test` chưa migrate (`SE_MIGRATE=1`, hoàn 0 điểm vì 7 cột cũ đều 0). Kiểm chứng:
`SELECT COUNT(*) FROM player_tianfu_log WHERE sid='e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2'` = **0**,
12 sid còn lại đều = 1. **Không phải bug migration** — nhưng nếu bấm `--apply` lần nữa trên
máy này sẽ cấp thêm 1 đan cho `E2E Test`. Production không có fixture này nên không dính.

### 9.2 Bảng chiến lực 13d — 10 nhân vật (đo 2026-10-14 15:5x, `E:/window/Temp/_tp_power13d.php`)

`CU` = công thức CŨ (`_tp_base/paihang.php:10-20` = `tf*50 + tfxy*10 + tfsb*10`) **cộng thêm**
khối hydrate `tf*` đã bị xoá ở `class/player.php:588-593` (Phương án A) — dựng lại để so đúng.
`MOI(combat)` = `\player\combat_power()` (`class/player.php:425`).
`MOI(paihang)` = `tcg_calc_chien_luc_rank()` bản hiện tại (`game/paihang.php:10-21`).

| uname | ulv | tf | CU | MOI(combat) | MOI(paihang) | lệch % (paihang−CU) | lệch % (combat−CU) |
|---|---|---|---|---|---|---|---|
| E2E Test | 1000 | 500 | 122.678 | 98.128 | 97.678 | **−20,4%** | −20,0% |
| 艾尔资源 | 1000 | 0 | 2.098.074.074 | 2.098.076.524 | 2.098.074.074 | 0,0% | 0,0% |
| QATuChan | 200 | 0 | 541.824 | 545.474 | 541.824 | 0,0% | +0,7% |
| PhátĐây | 93 | 542 | 175.371 | 148.721 | 148.271 | **−15,5%** | **−15,2%** |
| LCPASSIVE | 30 | 0 | 43.600 | 44.050 | 43.600 | 0,0% | +1,0% |
| tttttt | 12 | 77 | 5.315 | 1.915 | 1.465 | **−72,4%** | **−64,0%** |
| Tiên Nhân | 9 | 64 | 4.782 | 2.032 | 1.582 | **−66,9%** | **−57,5%** |
| LouLxGM | 2 | 7 | 686 | 786 | 336 | **−51,0%** | +14,6% |
| 1231231 | 1 | 0 | 195 | 645 | 195 | 0,0% | **+230,8%** |
| 444444 | 1 | 0 | 195 | 645 | 195 | 0,0% | **+230,8%** |

**Ba kết luận bắt buộc:**

1. `MOI(combat)` **lớn hơn** `CU` ở nhân vật có Nhục Thân/Mạch Huyết (`1231231`, `444444` +230,8%,
   `LouLxGM` +14,6%, `QATuChan` +0,7%) — vì `combat_power()` cộng 4 chỉ số
   (`thechat_quality×200`, `thechat_awaken_stage×500`, `huyetmach_quality×250`,
   `huyetmach_awaken_stage×600`). Đây là **sửa lỗi có sẵn**, không phải buff: hai nhân vật cấp 1
   chênh 230,8% chỉ vì một bên có Nhục Thân/Mạch Huyết được tính, bên kia (CU) không.
2. `MOI(paihang)` **nhỏ hơn** `CU` vì `tianfu_power = 0` cho tới khi migration chạy **và** người
   chơi khai phú. Tức: **giữa lúc migrate và lúc khai phú, chiến lực trên bảng xếp hạng tut**
   (PhátĐây −15,5%, `tttttt` −72,4%, `Tiên Nhân` −66,9%). → phải báo trước (Sổ nợ dòng 4).
3. **Lệch lớn nhất = −72,4% (tttttt) và +230,8% (1231231)** — **VƯỢT NGƯỠNG 20%** mà plan §6545
   đặt ra. Theo đúng plan: **DỪNG bàn giao, hỏi chủ dự án có chấp nhận không — không tự quét
   xuống dưới thảm.** Nhân vật bị nuốt nhiều nhất là nhân vật **test** (`tttttt`, `Tiên Nhân`,
   `E2E Test` có `tf` lớn nhưng chỉ số nền bé); ba nhân vật có `tf = 0` (`QATuChan`, `LCPASSIVE`,
   `艾尔资源`) gần như không đổi. **KHÔNG nhân vật nào trong số này là tài khoản người chơi thật.**

---
## 10. Nợ còn lại — **CHƯA test / CHƯA làm**

### 10.1 CHƯA test trong game thật

| # | Việc | Tình trạng thật |
|---|---|---|
| a | Chuỗi đầy đủ: uống Khai Phú Đan → có ô → gán Phú Vi → **thấy** linh khí/giây đổi trên màn Tu Luyện | **CHƯA** — mới chứng minh bằng hàm + test + render trang. DB local `player_tianfu = 0 dòng` nên chưa nhân vật nào đang hưởng cổng nào |
| b | `drop` và `exp_monster` trong combat log thật (quay số thực tế) | **CHƯA** — mới có unit test `tianfu_luck_gate()` |
| c | Phân bố tỉ lệ gốc để biết trần 95 (đan) / 99 (đột phá) nuốt bao nhiêu của +30 | **CHƯA đo** |
| d | `cron/stamina_recovery.php` chạy thật theo nhịp mới (210-300 s) | **CHƯA** — mới test hàm `tianfu_stamina_interval()` |
| e | Bảng xếp hạng `paihang` đổi thứ tự sau migration | **CHƯA** xem trên tài khoản có thiên phú thật |
| f | Tương tác Ne Tranh (Ẩm Kieu Mach) với Thiên Phú | **Không làm** — đã chốt để `tfsb` chết (§8.5) |
| g | 14 màn khác có đọc `$player->tf*` còn sót không | **ĐÃ quét**: `grep tfbj\|tfhp\|tffy\|tfgj\|tfxx` trong `class/player.php` = 0 sau khi xoá; **CHƯA** quét lại toàn repo cho các trang `game/*` |

**Ghi theo lệ Step 11 (plan dòng 6452-6454) — không được im lặng bỏ qua:**

- `test_linhcan_pages.php` FAIL do thiếu fixture `LC_BASE` (mặc định `http://127.0.0.1:8123`, máy này chạy 8091), **không phải lỗi code** — chạy lại với `LC_BASE=http://127.0.0.1:8091` → ALL PASS.
- `test_nguhanh_combat.php` và `test_nguhanh_market.php` FAIL do thiếu fixture `server port 80` (hai bộ hardcode `http://localhost/...`), **không phải lỗi code** — chạy `php -S 127.0.0.1:80 -t .` rồi chạy lại → ALL PASS. Server tạm này **đã tắt** sau khi đo.
- Ba bộ trên **không FAIL do hồi quy** của Step 3 (xoá `class/player.php:588-593`) hay Step 6 (migration): toàn bộ 25/25 bộ PASS khi đủ môi trường, `chan_loi=0` (không Fatal/Parse/Uncaught) ở mọi bộ.
- Nợ còn lại của nhóm test: `test_linhcan_schema.php` in từng dòng PASS mà **không in dòng kết luận** → grep tự động sẽ lọt; đã kiểm tra bằng `grep -c '^FAIL'` = 0 và exit code 0.

### 10.2 CHƯA làm / chưa chốt (đòi hỏi quyết định của chủ dự án)

1. **§8.4 cổng `alchemy` không có tác dụng trên đường thật** — ưu tiên cao nhất, vì UI đang hứa
   một thứ mà khi quay số không có.
2. Xoá hay giữ 6 cột `tf*` + khối cấp theo `shenfen` trong `upplayerlv()` (§8.1).
3. `docs/DEPLOY_PRODUCTION_THIENPHU.md` **CHƯA chạy** — production chưa có 2 bảng, 4 cột, đan 114,
   và chưa migration.
4. Trần hoàn `tf`: local tổng 410 điểm chia 12 nhân vật được migrate (bé). Production có thể có nhân vật `tf`
   hàng chục nghìn → chưa đặt trần.
5. Có báo trước cho người chơi về việc bảng xếp hạng đảo thứ tự không (§8.3).
6. `bak/` (dump DB có dữ liệu người chơi thật) đang nằm ngoài git nhờ `.git/info/exclude` — repo
   **không có `.gitignore`**, mọi `git add -A` đều sẽ hút theo `.idea/`, `chajian/`, `shot_*.png`.

---

## 11. Bảng tra nhanh (hỏi → đáp → chỗ mở ra xem)

| Hỏi | Đáp | Mở ra xem |
|---|---|---|
| Khai Phú Đan lấy ở đâu? | Thương Điếm tab Đạo Cụ, djid **114**, giá 50.000 Linh Thạch | `class/config/daoju_config.json`, `game/tianfu_open.php:104` |
| Tỷ lệ ra phẩm? | 40 / 25 / 15 / 10 / 6 / 2,5 / 1 / 0,4 / 0,09 / 0,01 (%) | `class/config/tianfu_config.json:41-50` |
| Nâng cấp mất bao nhiêu? | 1 `tf` + (200 + 25·Lv) Linh Thạch | `tianfu_lv_cost()` `class/function/tianfu.php:128` |
| Đột phá phẩm mất bao nhiêu? | 10→150 `tf`, 10.000→20.000.000 Linh Thạch, cần cấp nhân vật 0→500 | `break_quality` config `:53-63` |
| Mở Phú Vi khi nào? | Ô 1 cấp 0 · ô 2 cấp 100 · ô 3 cấp 200 · ô 4 cấp 280 | `slots` config `:70-75` |
| Mốc tổng Lv? | 60 → +5% · 150 → +12% · 300 → +20% · 500 → +30% | `milestones` config `:64-69` |
| Ô để trong Phủ Các có tính không? | **Có** tính vào `total_lv` (mốc), **không** tính vào 7 cổng | `class/function/tianfu.php:348` |
| Hai ô cùng một cổng bị cấm không? | Bị — `tianfu_slot_conflict()` chặn từ lúc gán | `class/function/tianfu.php:748` |
| Chiến lực tính sao? | `phẩm·150 + Lv·8` mỗi ô đang gán, tổng lại × trọng số 1 | `tianfu_power_of()` `:279`, `combat_power()` `class/player.php:433` |
| Nhân vật cũ được gì khi migration? | Cộng `tf` cũ vào quỹ + đặt 5 cột về 0 + tặng 1 Khai Phú Đan + ghi log `kind='migrate'` | `sql/migrate_tianfu.php` |
| Chạy migration 2 lần có sao? | Không — có chốt `kind='migrate'` → `SKIP` | `sql/migrate_tianfu.php` (idempotent guard) |
| Route nào vào trang nào? | `tianfu` / `tianfu_collection` / `tianfu_open` | `game.php:349-351` |
| Sao trang trắng? | Kiểm tra UTF-8 file PHP (Apache trả trang trắng khi file hỏng encoding) → `iconv -f UTF-8 -t UTF-8 file >/dev/null` | §9 bước 1 |

---

## 12. Thang hiệu ứng phân bậc — `tp-fx0 … tp-fx5`

Theo **phẩm chất của Chủ Phú** (Phú Vi 1), qua `tianfu_fx_class()`
(`class/function/tianfu.php:196`) → class trên `.tp-wrap` (`game/tianfu.php:125`) và `.tpc-wrap`
(`game/tianfu_collection.php:123`). **Lệ của chủ dự án: bậc thấp nhất vẫn phải thấy gì đó.**

| Bậc | Phẩm | `--tp-glow` | `--tp-dur` | `--tp-aura` | Cảm giác |
|---|---|---|---|---|---|
| `tp-fx0` | 1-2 Phàm/Linh | 4 px | `0s` (tắt pulse, **giữ halo tĩnh**) | .05 | viền sáng nhẹ, không động |
| `tp-fx1` | 3-4 Huyền/Địa | 7 px | 11 s | .07 | thở rất chậm |
| `tp-fx2` | 5 Thiên | 11 px | 9 s | .10 | thở chậm |
| `tp-fx3` | 6 Thánh | 15 px | 6,5 s | .14 | thở đều |
| `tp-fx4` | 7-8 Tiên/Thần | 20 px | 4,4 s | .18 | nhịp nhanh, màu vàng kim |
| `tp-fx5` | 9-10 Đế/Đạo | 26 px | 3 s | .23 | rực nhất, gần như rung |

Bốn biến (`:7431-7440`): `--tp-tier` = **màu phẩm chất** của chủ phú (`tianfu_quality_color`),
`--tp-color` = **màu nhóm** đang xem (`groups[*].color`) — hai cái này độc lập, đừng gộp.
`tp-fx0` tắt động bằng `animation:none`, **không** dùng `duration:0s` (Chrome vẫn giữ keyframe 0%).
**CHƯA** quay video/kiểm chứng cảm giác chuyển động bằng mắt trên thiết bị thật — mới nhìn ảnh tĩnh.

---

## Sổ nợ chờ chủ dự án chốt (đo ngày 2026-10-14)

**Cách dùng bảng này:** mỗi dòng là một việc **đang còn mở** — không dòng nào được tự sửa số,
không dòng nào tự đóng. Cột "Chỗ" là `file:dòng` đo thật trong repo hôm nay (nhiều chỗ lệch với
plan — xem mục "Sai lệch Plan ↔ Thực tế" bên dưới). Cột "Ai quyết" ghi rõ: nếu là chủ dự án thì
agent **dừng lại**, không chọn thay. Dòng nào có chữ **KHÔNG LẤY LẠI ĐƯỢC** là quyết định một chiều.

| # | Món nợ | Chỗ (file:dòng) | Ai quyết | Hậu quả nếu quyết sai / nếu không quyết |
|---|---|---|---|---|
| 1 | **Phương án A hay C cho khối thiên phú cũ** — plan bắt xoá, đã xoá theo A vì anh nói "acc test thôi không quan trọng" | `class/player.php` — 6 dòng **đã xoá** tại vị trí `:588-593` (trước khi xoá) | **Chủ dự án** (duyệt lại) | **A**: `PhátĐây` Bạo kích **91 → 37** (số đo thật sau hydrate; bảng plan ghi `ubj 61→7` là sai vì bỏ qua trang bị cộng), `tttttt` `uxx` 22→0 — bạo kích/hút máu tut nặng, **KHÔNG LẤY LẠI ĐƯỢC** sau khi người chơi tiêu `tf`. **C**: 6 cột cũ tiếp tục cộng vào thuộc tính, trái spec §6 và trái cả chiến lực mới |
| 2 | **`paihang.php` A hay B** — giữ hàm riêng (thiếu 4 chỉ số) hay gọi `\player\combat_power()` | `game/paihang.php:10-21` (ghi chú ở `:12-13`), chỗ gọi duy nhất `:67` | **Chủ dự án** | **A** (đang chạy): bảng xếp hạng **vẫn thiếu** `thechat_quality/thechat_awaken_stage/huyetmach_quality/huyetmach_awaken_stage` → top rank sai so với chiến lực thật. **B**: đúng một công thức, nhưng **mở rộng phạm vi** ngoài spec (sửa luôn cách tính hạng toàn game) |
| 3 | **Cổng `alchemy` hiển thị ≠ roll thật** | `class/function/luonden.php:348` (hiển thị, có cộng) vs `:899` (roll thật trong `luonden_dung_dan()`, **không cộng**) | **Chủ dự án** | Người chơi thấy "đã cộng Thiên Phú" nhưng quay số không được cộng → **cảm giác bị lừa**. Không tự sửa vì phải chọn: sửa roll theo hiển thị (**đổi cân bằng kinh tế luyện đan**) hay sửa hiển thị theo roll (cổng 3 thành chữ trang trí) |
| 4 | **Bảng xếp hạng xáo trộn ngay ngày migration** — có báo trước trong game không | `game/paihang.php:17-19` | **Chủ dự án** | Chiến lực tính lại theo trọng số mới → thứ tự đảo lộn, người chơi thấy "mất hạng". Deploy im lặng = mất người; báo trước = phải viết thông báo |
| 5 | **`upplayerlv()` vẫn cấp 6 cột `tf*` cũ** | `class/player.php:1201` (hàm), khối `UPDATE` tại `:1232-1256` | **Chủ dự án** | Sau A: điểm tăng mà **vô tác dụng** → người chơi tưởng được thưởng. Sau C: sống bình thường. Chưa sửa vì nằm ngoài spec |
| 6 | **`usedaoju()` đang bít đường Khai Phu Đan** | `class/player.php:2059`, chốt 2 lần/người tại `:2083-2089` (cần cột `djuse`; `daoju_config.json:109-113` không có) | **Chủ dự án** | Đã lách bằng `deledjsum(114, 1, …)` trực tiếp trong `tianfu_open()`. Nếu sau này thêm đan khác đi qua `usedaoju()` → **lại bích**. Nợ kỹ thuật, chưa trả |
| 7 | **Boss thiên phú riêng (phase 2)** | chưa viết | **Chủ dự án** | Thiên Phú **không có đường nào để đánh dấu** như Mạch Huyết có `huyetmach_boss` → nguồn `tianfu_id` phẩm cao chỉ từ `Khai Phú Đan`. Chưa làm vì spec chỉ định 1 phase |
| 8 | **Production CHƯA migration gì cả** | `docs/DEPLOY_PRODUCTION_THIENPHU.md` (RUNBOOK, chưa chạy bước nào) | **Chủ server** | Production thiếu 2 bảng + 4 cột + đan 114, và **còn nợ schema sid của 15 bảng** (`docs/DEPLOY_PRODUCTION_NGUHANH.md`). Copy code mà bỏ qua → trang Thiên Phú trắng tinh |
| 9 | **Repo không có `.gitignore`** | gốc repo (`ls .gitignore` → không tồn tại, đo 2026-10-14) | **Chủ dự án** | Dump DB có **dữ liệu người chơi thật** ở `bak/tianfu_pre_*.sql` chỉ được bọc bằng `.git/info/exclude` (**chỉ máy này, không commit**). Mọi `git add -A` sẽ hút theo `.idea/`, `chajian/`, `shot_*.png`, `bak/`. Plan **không tự tạo** `.gitignore` vì ảnh hưởng toàn repo |
| 10 | **Trần hoàn `tf` khi migration production** | `sql/migrate_tianfu.php` (không có trần) | **Chủ dự án** | Local chỉ 410 điểm chia 12 nhân vật được migrate (bé). Production có thể có nhân vật `tf` hàng chục nghìn → một người burst lên cấp 60 ngay khi khai phú. Chưa đặt trần vì chưa thấy số production |
| 11 | **Chuỗi end-to-end chưa mắt thấy** | `player_tianfu` local = **0 dòng** (§9.1) | Người thi hành + chủ dự án bấm | Toàn bộ bằng chứng là hàm + test + render trang. Chưa có nhân vật thật nào uống đan → gán Phú Vi → **nhìn thấy** linh khí/giây đổi trên màn Tu Luyện (§10.1a) |
| 12 | **Nhãn / đơn vị in ra** | `game/tianfu.php:214`, `game/tianfu_collection.php:184` (đã sửa: thêm dấu cách khi đơn vị không phải `%`) | Chủ dự án duyệt chữ | Trang Trạng Thái in chỉ số % không kèm đơn vị ở hệ Mạch Huyết (`game/zhuangtai.php:587-591`, sổ nợ Mạch Huyết dòng 21) — hai hệ đang **không nhất quán**, chưa hợp nhất vì đụng trang của hệ khác |
| 13 | **[CHOT] Lệch chiến lực vượt ngưỡng 20% mà chính plan đặt ra** — plan dòng 6545 bắt "lệch > 20% → DỪNG, hỏi chủ dự án", nên dòng này **chặn bàn giao** | §9.2 (bảng 10 dòng, đo 2026-10-14) | **Chủ dự án** | Số đo thật: `tttttt` **−72,4%**, `Tiên Nhân` **−66,9%**, `E2E Test` **−20,4%**, `1231231`/`444444` **+230,8%**. Cả 5 đều là **nhân vật test** (`tf` lớn, chỉ số nền bé), không phải acc người chơi thật. Nếu anh quyết **chấp nhận**: deploy được, bảng xếp hạng đổi thứ tự ngay. Nếu **không chấp nhận**: phải hoặc giữ lại khối hydrate cũ (Phương án C, quay lại dòng 1), hoặc cho `paihang.php` gọi `combat_power()` (dòng 2 phương án B), hoặc đặt trần `tf` (dòng 10). **Tôi không tự chốt.** |

### Sai lệch Plan ↔ Thực tế (ghi để lần sau đừng tin plan mù)

| Plan nói | Thực tế đo được |
|---|---|
| 14 nhân vật trong `game1` | **13** |
| `mystyle.css` BEFORE = 7846 dòng | 8038 dòng trước khi cắt |
| Dump bảo hiểm "vài nghìn dòng / >100 KB" | 218 dòng / 36.937 B |
| Mọi nhân vật có `tf > 0` sau migration | chỉ 5/13 có `tf` gốc > 0 |
| PhátĐây `ubj` 61 → 7 | số thật sau hydrate: **Bạo kích 91 → 37** (bảng plan bỏ qua trang bị cộng) |
| `use_dj()` `class/player.php:2058-2087` | tên thật `usedaoju()` `:2059`, chốt 2 lần ở `:2083-2089` |
| `upplayerlv()` khối `tf*` ở `:1199-1215` | `:1232-1256` |
| `grep -c … # Expected: >= 6` | `grep -c` đếm **dòng**, kết quả 3 — phải dùng `substr_count` |
| Chụp bằng `--window-size=$W,1400` trực tiếp | Chrome tối thiểu 500 px → phải qua iframe harness |
| 13e mục 2: "`git status --short` sau Commit D chỉ còn `?? shot_*.png`" | **Repo chỉ track ~98 file** — toàn bộ game cũ (`game/` 40+ file, `images/` 300+ file, `class/config/` 34 file, `sql/`, `cron/`) **chưa từng được track**, nên vẫn còn `??` dài tràng. Đã lọc kỹ: **0 file trong phạm vi Thiên Phú còn sót** (lọc `tianfu|luonden|paihang|tupo|zbinfo|xiulian|shangdian|stamina` → chỉ còn file không liên quan như `game/luonden.php` (mtime 2026-10-04, TÔI KHÔNG SỬA), `sql/stamina_recovery.sql`, ảnh `images/xiulian/`) |
| 13e mục 4-5: "log migrate = số nhân vật = 14" | **12** — 13 nhân vật trừ sid fixture `e2e2e2…`. `tests/test_tianfu_pages.php:28,68,148` xóa `player_tianfu_log` + đan 114 của sid đó để trang hiện "chưa khai phú". Apply lúc 15:05 in `OK=13`, chạy test lúc 15:2x–16:1x làm số tụt về 12. **Không phải bug migration** (§9.1 có cảnh báo) |
| 13b rule 3: "lệch > 20% → DỪNG, hỏi chủ dự án" | **ĐÃ DỪNG THẬT**: đo được −72,4% (`tttttt`), −66,9% (`Tiên Nhân`), −20,4% (`E2E Test`), +230,8% (`1231231`/`444444`). Không tự cắt giấu, ghi thành **Sổ nợ dòng 13 [CHOT]** |
