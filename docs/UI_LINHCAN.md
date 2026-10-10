# Tài liệu: Linh Căn — Vòng Ngũ Hành (game/linhcan.php)

> **Nguồn**: kế hoạch `docs/superpowers/plans/2026-10-10-linhcan-vong-ngu-hanh.md` (12 task),
> thiết kế `docs/superpowers/specs/2026-10-10-linhcan-vong-ngu-hanh-design.md` (số `[]` đã duyệt 10/10).
> **Khung 12 mục copy từ `docs/UI_THECHAT.md` / `docs/UI_HUYETMACH.md`.**
> Ngày đo: **2026-10-10**, DB local `game`. Tài liệu này đang được viết dần theo từng task —
> mọi mục **CHƯA test** đều in hoa CHƯA.

---

## Kiểm kê consumer `linhcan_id` (Task 1 Step 1)

Lệnh: `grep -rn "linhcan_id" --include=*.php . | grep -v "^./bak/" | grep -v "^./docs/" | grep -v "^./sql/"`

Plan dự đoán 5 chỗ. Thực đo **26 dòng / 8 tệp** — 3 chỗ plan chưa liệt kê:

| Tệp: Dòng | Thuộc plan? | Xử lý |
|---|---|---|
| `class/function/linhcan.php:164-204,502-596` | ✅ | Task 3/11 viết lại + xoá |
| `class/function/linhcan_boss.php:40,44,102` | ✅ | Task 5 bỏ `get_dominant_element` |
| `class/player.php:113,304,746-747,2025-2031` | ✅ | Task 9 rewire; `:304` allowlist + `game.php:192` INSERT **GIỮ NGUYÊN** |
| `game/linhcan.php:6,11` | ✅ | Task 10 viết lại |
| `game/linhcan_upgrade.php:6,11,32` | ⚠️ plan có nhắc ở File Structure | Task 7 XOÁ file + route `game.php:353` |
| `game/zhuangtai.php:424` | ✅ | Task 9 sửa `:424-450` |
| `tests/e2e_fixture.php:62` | ❌ **ngoài plan** | Fixture ghi `linhcan_id=1, quality=5`. Sau Task 9 hệ mới không đọc cột này → giá trị thành dữ liệu chết. **CHƯA đụng**, ghi ở §Chưa làm. |
| `tests/test_nguhanh_random.php:59,144` | ❌ **ngoài plan** | Nhánh C set `linhcan_id=21` rồi **assert** `$player->linhcan_element === 'all'`. Sau Task 9, `linhcan_element` lấy từ `linhcan_dominant()` (node) → **test này sẽ FAIL**. Phải sửa ở Task 9. |

Không còn consumer nào ở `paihang` hay trang khác (đúng như plan).

**Hệ quả cho Nạp Ngũ Hành (`class/function/nap_hanh.php:45`)**: `linhcan_bonus` có sẵn
`kim 15 / moc 5 / thuy 0 / hoa 10 / tho 8` → ghi `linhcan_element` = hành chủ đạo là **Nạp Hành vẫn chạy đủ**.
Riêng 2 khoá `all` (+10, từng tới từ căn id 21) và `none` (0, từng tới từ Nghịch Thiên id 22) **không còn nguồn** —
chờ chủ dự án chốt (xem §Chưa làm).

## Nguồn đá 32..37 (Task 1 Step 2)

| djid | Tên (`daoju_config.json`) | `djyxb` | Giá cửa hàng (`shangdian.php:309` = `max(50, djyxb)`) | `djuse` |
|---|---|---|---|---|
| 32 | Hỏa Tinh Thạch | 30 | **50 Linh thạch** | trống |
| 33 | Thủy Tinh Thạch | 30 | 50 | trống |
| 34 | Mộc Tinh Thạch | 30 | 50 | trống |
| 35 | Kim Tinh Thạch | 30 | 50 | trống |
| 36 | Thổ Tinh Thạch | 30 | 50 | trống |
| 37 | Bình Anh Tinh Thạch | 100 | **100 Linh thạch** | trống |
| 111 | Tẩy Linh Đan | 30000 | 30000 | trống |

Đo thêm:
- **Quai KHÔNG rơi đá ngũ hành**: quét toàn bộ 87 entry `guaiwu_config.json`, field rơi đồ là `gdj`/`gyp`
  (danh sách id, `djjv`/`ypjv` = số lượng) — **không entry nào rơi id 32..37**. Quai chỉ rơi id 1/6/7/8/12.
- **Cửa hàng có bán**, tab Đạo Cụ (`game/shangdian.php:339` duyệt toàn bộ `daoju_config_all()`,
  chỉ trừ djid 112/113/114). Không món nào bị chặn ở `:308` vì `djuse` đều trống.

→ **Nguồn đá = cửa hàng (50/100 Linh thạch, mua không giới hạn) + boss thế giới (Task 5 ĐÃ xong) + quest tuần (Task 6).**
⚠️ **VẪN PHẢI BÁO CHỦ DỰ ÁN**: từ Task 5, boss thế giới 2h rơi **THẬT** 1–2 Bình Anh Tinh Thạch vào túi đồ,
nhưng cửa hàng vẫn bán Bình Anh **100 Linh thạch không giới hạn** → "đá hiếm" vẫn mua được bằng tiền.
Chặn bán là quyết định của chủ dự án — xem §Chưa làm mục 1.

## Boss thế giới — số đang chạy (Task 5, 2026-10-11)

`class/function/linhcan_boss.php` + `class/config/linhcan_boss_config.json`:

| Mục | Giá trị | Nguồn |
|---|---|---|
| Chu kỳ spawn | 120 phút | `spawn_interval_minutes` (giữ nguyên) |
| Hành boss | **ngẫu nhiên 1 trong 5** `kim\|moc\|thuy\|hoa\|tho` | `world_element_pick: "random_of_5"` + `linhcan_boss_world_element()` |
| Cấp boss | `AVG(ulv)` của **mọi** nhân vật trong `game1` (không lọc `linhcan_id > 0` nữa) | `linhcan_boss_spawn()` |
| Máu / Công / Thủ | `5000 + 500·lv` / `200 + 20·lv` / `100 + 10·lv` | `base_stats` + `level_scaling` (giữ nguyên công thức cũ) |
| Drop Linh Thạch | 100–500 | `drops.linh_thach_min/max` |
| Drop đá **đúng hành boss** | 2–4 viên id 32/33/34/35/36 → `playerdaoju` THẬT | `drops.tinh_thach_min/max` + `linhcan_give_stone()` |
| Drop Bình Anh | 1–2 viên id 37 → `playerdaoju` THẬT | `drops.binh_anh_min/max` |
| Đã xóa | `element_bosses.none` (Hư Không Ma Vương), `da_ngu_hanh_count`, `rare_drop_chance`, `linhcan_boss_get_dominant_element()` | config + code |

Test: `tests/test_linhcan_boss.php` phần **E1–E4** (11 assert) — 200 lần quay chỉ ra đúng 5 hành,
spawn theo `AVG(ulv)` toàn DB, đá chỉ rơi **đúng hành boss** (id 33/34/35/36 = 0 dòng khi boss hành Hỏa),
Bình Anh có mặt trong `playerdaoju` đúng số hàm trả về. **CHƯA test trong game thật** (mới có harness CLI).


## Quest tuần — số đang chạy (Task 6, 2026-10-11)

`class/config/linhcan_quest_config.json` + `class/function/linhcan_quest.php`:

| # | Task | Hook thật | Mục tiêu | Nguồn increment |
|---|---|---|---|---|
| 1 | tu luyện ngũ hành | `train_element` | 10 | `linhcan_train()` (Task 3) |
| 2 | đánh quái | `kill_monsters` | 10 | pve hit path |
| 3 | giết boss thế giới | `kill_boss` | 1 | `linhcan_boss_drops()` (Task 5) |
| 4 | nhận đá ngũ hành | `receive_stone` | 5 | `linhcan_give_stone()` (Task 2) |
| 5 | đột phá | `break_once` | 1 | `linhcan_break()` (Task 4) |

| Mục | Giá trị |
|---|---|
| Thưởng hoàn 5/5 | **500 Linh Thạch** (`game1.uyxb`) + **1–2 Bình Anh Tinh Thạch** (djid 37, vào `playerdaoju` THẬT qua `linhcan_give_stone`) |
| Đã xóa | `reward_linhthach` / `reward_da_ngu_hanh` per-quest (3 quest cũ `xiulian_minutes`/`use_item`/`boss_kill` không có hook thật) |
| Chặn nhận 2 lần | cột `completed` (KHÔNG phải progress) — `linhcan_quest_claim` chỉ cộng thưởng khi `completed = 0` |
| Side effect cố ý | `give_stone` bên trong claim làm `receive_stone` nhích thêm — vô hại vì tuần đã `completed` |

Test: `tests/test_linhcan_quest.php` F1–F7 — config 5 hook đúng tên, từng hook tang dung quest,
`receive_stone` nhảy theo `linhcan_give_stone`, claim phụ fail, claim đủ 5/5 cap **500 LT + Binh Anh vao
inventory that** (`playerdaoju.djsum` > 0), claim lap lai bi chan. **CHƯA test trong game thật.**

## Tẩy Linh Đan (111) — số đang chạy (Task 7, 2026-10-11)

`linhcan_wash()` (`class/function/linhcan.php` cuối file) + `game/linhcan_wash.php` + route `game.php:353`:

| Mục | Giá trị |
|---|---|
| Điều kiện | có ≥1 đan 111 **và** hành đó `level > 0` |
| Kết quả | hành đó về **Lv 0**, **GIỮ bậc** (stage) — boss đột phá + kỹ năng không mất |
| Hoàn lại | `floor(80% × đá đã luyện)` vào `playerdaoju` + `100% LT đã luyện` vào `game1.uyxb` |
| KHÔNG hoàn | Bình Anh (37) + linh khí đã nạp lúc đột phá (ghi ngay trên trang) |
| Cố ý | hoàn đá bằng `adddj` trực tiếp, **không** qua `linhcan_give_stone` → không đếm hook `receive_stone` (chống cày quest: luyện → tẩy → đếm) |
| Ví dụ đo | Kim Lv 5: hoàn **4 đá + 650 LT**; Hỏa Lv 12: hoàn **9 đá + 2190 LT** |

Đường tắt cũ đã cắt: `game/linhcan_upgrade.php` **không còn trên working tree** (archive ở
`bak/linhcan_upgrade.20261011_truoc_vongnguhanh.php`), route `linhcan_upgrade` bị xóa,
nút "⬆ Nâng cấp" trên trang cũ chuyển thành "🌀 Tẩy Linh Đan" → `cmd=linhcan_wash`;
`use_effect.type` của đan 111: `reroll_linhcan` → `linhcan_wash`; `use_dj.php` không còn random căn,
chỉ redirect; `class/player.php` nhánh `djuse == 'linhcan'` trả chuỗi "không còn gán ngẫu nhiên".

**Kiểm kê nợ trừ oan (Step 7 — chỉ báo, KHÔNG tự hoàn):**

| Số đo local | Giá trị |
|---|---|
| `player_linhcan_log` (bảng code cũ INSERT) | **KHÔNG TỒN TẠI** → mỗi lần bấm Nâng cấp: trừ LT → trừ uexp → trừ đá → **PDO fatat**, nguyên liệu mất, căn giữ nguyên, **không có log để đối chiếu** |
| Nhân vật local | 13 |
| Đang âm LT hoặc uexp | **0** |
| Từng có `linhcan_id > 0` | 5 (căn 1 ×2, căn 4, 6, 16) |
| Đang giữ đan 111 | 0 |

→ Thiệt hại quá khứ **không định lượng được bằng dữ liệu hiện có**. Muốn hoàn cho ai phải do chủ dự án
tự quyết mức (không có sổ để tra). **CHƯA tự cộng tiền cho nhân vật nào.**

## 5 kỹ năng ngũ hành vào thực chiến (Task 8, 2026-10-11)

`class/config/linhcan_skill_config.json` — 5 skill, ổ khóa là **bậc của hành chủ đạo** (Địa Căn = bậc 2),
KHÔNG phải `ulv`. Số lấy nguyên từ spec đã duyệt:

| id | Tên | Hành | `unlock_stage` | Hiệu lực thực chiến | Khí huyết | Hồi chiêu |
|---|---|---|---|---|---|---|
| hoa_diem_chuong | Hỏa Diệm Chưởng | Hỏa | 2 | x1.5 sát thương (`aoe: true` — cố ý CHƯA dùng, PVE đánh 1 mục tiêu) | 10% max | 5 phút |
| thuy_ho_thuat | Thủy Hộ Thuật | Thủy | 2 | hồi 8%/lượt x 3 lượt = **24% khí huyết** | 10% max | 5 phút |
| moc_linh_thuat | Mộc Linh Thuật | Mộc | 2 | +25% hút máu | 10% max | 5 phút |
| kim_cuong_chuong | Kim Cương Chưởng | Kim | 2 | +40% chí mạng | 10% max | 5 phút |
| tho_ho_thuat | Thổ Hộ Thuật | Thổ | 2 | +30% phòng ngự | 10% max | 5 phút |

Hàm trong `class/function/linhcan_skill.php` (189 → **233** dòng, scanner ký tự CLEAN):
- `linhcan_skill_can_use($player, $skill, $lcStage)` — **3 tham số**; cổng 1 = bậc hành, cổng 2 =
  `uhp` phải **lớn hơn** chi phí (để skill không tự cạn máu tới 0).
- `linhcan_skill_for_stage($sid, $dblj)` — node → `linhcan_dominant` → `element_to_skill`; `false` khi bậc < 2.
- `linhcan_skill_combat_mods($skill)` — chốt **8 key**: `atk_mult / crit_add / lifesteal_add / def_pct /
  heal_pct / ignore_def_pct / name / icon`.
- `linhcan_skill_apply()` vẫn còn nhưng **không dùng trong combat** (docblock ghi rõ, Task 11 sẽ xét xóa).

Nút + wiring (số dòng **đo lại sau khi chèn**):
- `game/pve.php`: nút `:27-35`, init `$lcMods` `:126`, `case 'uselcskill'` `:141-162`,
  stat mods `:210-219`, ignore_def `:294-296`, atk_mult `:301-303`, render `:976`.
- `game/boss.php`: nút `:10-19`, `case` **standalone** `:191-212` (vì `switch($canshu)` của file bị
  comment hoá ở `:180-198`), stat mods `:248-257`, ignore_def `:291-293`, atk_mult `:298-300`, render `:918`.
- Chuỗi chặn hồi chiêu giống nhau ở 2 trang: "Kỹ năng còn hồi chiêu".

**Biến thể so với spec 2.3 (PHẢI báo chủ dự án)**: 1 request = 1 lượt chiến, nên hiệu lực theo lượt
(hồi 3 lượt, buff) chỉ sống **trong đúng 1 request**. Đây là bản rút gọn của "hiệu lực theo lượt",
không phải nguyên văn §2.3.

Bằng chứng — `tests/test_linhcan_combat.php` (26 assert CLI) + `--live` (20 assert route thật,
`php -S 127.0.0.1:8199` vì phpStudy đang tắt): **ALL PASS**, chạy lặp 2 lần.
- E2.4b: chi phí in trong lời = **10% `umaxhp` SAU buff** (3407 trên 34079), không phải cột thô.
- E3.2: hồi = 24% max sau buff = **8179** máu; E3.4 máu cuối trận bị chặn đúng ceiling.
- E4: chí mạng 150 đòn thường vs 150 đòn có skill — lần 1 `59.3% → 78.0%`, lần 2 `64% → 85.3%`
  (lý thuyết 61.5% → 81.5%, dung sai ±10 điểm %, delta ≥10).
- E5.1/E5.2: trang boss cũng có nút + lời "Thi triển", máu boss giảm thật.
- **CHƯA test trong game thật**: chưa bấm nút bằng mắt trên trình duyệt; mới chỉ assert HTML trả về
  từ route (`E5.0` đo độ dài HTML + `strpos`). Trang Vòng Ngũ Hành + ảnh + fx = Task 10.
## getplayer + Trạng thái đọc node Ngũ Hành (Task 9, 2026-10-11)

Từ task này, **mọi chỉ số và mọi chữ "Linh Căn" trong game đều lấy từ `player_linhcan_node`** —
`game1.linhcan_id` / `linhcan_quality` không còn được `getplayer()` đọc nữa (cột vẫn tồn tại trong DB,
Task 12 mới xử lý).

`class/player.php`:
- Thêm 3 `var`: `linhcan_icon` (`:122-124`), `linhcan_title_id`, `linhcan_total_lv`.
- Khối `===== BUFF LINH CĂN (Passive) =====` (`:701-762`): giữ nguyên reset + hợp đồng 16 key + cộng stat +
  penalty; **thay** 10 dòng "Load thông tin hiển thị" (đọc `linhcan_config_get($player->linhcan_id)`)
  bằng `linhcan_node_rows()` + `linhcan_title()`; `$player->linhcan_element = $lcB['element']` (hành chủ đạo).
- Field mới trên `$player`: `linhcan_element` (key không dấu `kim|moc|thuy|hoa|tho` hoặc `''`),
  `linhcan_name` (danh hiệu + ' · ' + tên hành), `linhcan_color`, `linhcan_icon`, `linhcan_title_id`,
  `linhcan_tier` = **bậc của hành chủ đạo** (0..4), `linhcan_total_lv` = tổng Lv 5 hành.
- `game/zhuangtai.php:424-485` thay khối cũ: điều kiện hiển thị là `!empty($player->linhcan_element)`
  (không còn `linhcan_id`), nhãn bậc lấy `linhcan_stage_def()`, thêm dòng "Cộng hưởng Ngũ Hành"/"Mốc cộng
  hưởng kế" từ `linhcan_milestone()`, `round(...,2)` cho Hấp thụ/Tốc độ (giờ là float %).
  **Xóa** `tierNames/tierIcons` 0..5 (có 'Niết' không tồn tại trong hệ mới) và `$elementIcons` có key
  `'không'`/`'mộc'` có dấu → không bao giờ khớp key không dấu, rơi vào 🌀 (lỗi hiển thị cũ).

Caller còn sót của hệ cũ (lệnh `grep -rn "linhcan_config_get\|linhcan_random\|linhcan_assign" class/ game/ tests/ --include=*.php`
trừ `class/function/linhcan.php`): **duy nhất `game/linhcan.php:11`** — trang cũ, Task 10 viết lại.
→ **Cửa trang cũ `cmd=linhcan` vẫn sống đến Task 10** (đo: HTTP 200, 7712 byte, 0 fatal), nhưng tên nó in
bây giờ là **danh hiệu mới** vì đọc `$player->linhcan_name` — lệch nhất quán tạm thời, chấp nhận.

Bảng Trạng thái đo thật (fixture e2e, node `kim 45/2` + `moc 12/1`):
```
🗡️ Linh Căn: Chân Linh Căn · Kim   Địa Căn · Kim · Tổng Lv 57
Linh Căn Công +91 | Linh Căn Huyết +168 | Mốc cộng hưởng kế: Tổng Lv 60 (+5%)
☯ Nội tại ngũ hành: Hợp căn 2 món — bậc 1 +3% (Kim +41, Mộc +7, Hỏa +1, Thổ +86)
```
(`game/zhuangtai.php` in đúng icon danh hiệu 🗡️ chứ không còn 🌀 lạc lỗi; Nạp Hành vẫn chạy vì
`nguhanh_*` đọc đúng `linhcan_element`.)

Đo test — `tests/test_linhcan_passive.php`: 17 FAIL trước khi sửa → **21 assert CLI + 6 assert live
= ALL PASS** (`--live` qua `php -S 127.0.0.1:8199`, L1/L2/L3/L4/L5 + tự restore node snapshot + endtime).
Hồi quy sau khi đổi `getplayer()` (blast radius = mọi trang): element 63, quest 14, boss 44, combat 25,
nguhanh 82, **nguhanh_random 25 (đã viết lại nhánh C)**, huyetmach schema/vessel/upgrade/combat/boss = ALL PASS,
linhcan_schema 12/12.
- **CHƯA test trong game thật**: chưa mở trình duyệt bấm vào trang Trạng thái của nhân vật thật;
  mới assert HTML trả về từ route + in thử bảng bằng fixture. Ảnh + trang Vòng Ngũ Hành = Task 10.

Cấu hình chết cần chủ dự án chốt (đã ghi §Chưa làm từ Task 1, nay thành hiện thực): `nguhanh_config.json`
còn `linhcan_bonus.all = 10` và `none = 0` trong khi **hệ mới không sinh ra được 2 khoá đó** → nhánh C của
`tests/test_nguhanh_random.php` phải chuyển từ `all` sang `hoa` (+10, cùng số) để vẫn test được trần 95%.
## Chưa làm / chờ chủ dự án chốt

1. **(Task 1 phát hiện, Task 5 xác nhận) Cửa hàng bán Bình Anh Tinh Thạch (37) giá 100 Linh thạch, mua không giới hạn.**
   Từ Task 5 boss thế giới 2h rơi THẬT 1–2 Bình Anh, nhưng ai có Linh thạch thì vẫn mua được vô hạn →
   điều kiện "boss đột phá không cày được" chỉ còn chặn ở **boss cá nhân + số lượng đá mỗi trận**. Ba lựa chọn:
   (a) chặn 32..37 khỏi tab Đạo Cụ, (b) giới hạn số lượt mua mỗi tuần, (c) chấp nhận — đá không hiếm.
   **CHƯA tự sửa** vì đụng trang `shangdian.php` ngoài phạm vi plan.
2. **(Task 1 phát hiện) Nạp Ngũ Hành mất thưởng `all` (+10%) và `none` (0%)** do 2 khoá này từng tới từ
   căn id 21/22 (`linhcan_config.json` cũ). Cần chốt: Vô Cực Linh Thể (danh hiệu mới) có cấp `all` không.
3. **(Task 1 phát hiện) `tests/test_nguhanh_random.php` nhánh C sẽ FAIL sau Task 9** vì nó set
   `linhcan_id=21` và assert `linhcan_element === 'all'`. Task 9 phải viết lại nhánh C theo node.
4. **(Task 1 phát hiện) `tests/e2e_fixture.php:62` vẫn ghi `linhcan_id=1, linhcan_quality=5`** cho nhân fixture.
   Sau Task 9 cột này không còn được đọc → dữ liệu chết. Nên đổi sang seed node (Task 10/12 dùng `lcseed.php`).
5. `docs/UI_LINHCAN.md` mới có phần kiểm kê Task 1; 10 mục còn lại của khung (so ảnh 1:1, sổ nợ, playbook lệnh)
   sẽ viết dần ở Task 10-12.
6. **(Task 7 phát hiện) Túi đồ KHÔNG có nút "Dùng" cho đan 111** — không món nào trong `daoju_config.json`
   có key `djuse` (`getdaoju(111)->djuse = NULL`), nên `usedaoju()` chặn ngay bằng
   "Đạo cụ này không thể sử dụng."; nhánh `djuse == 'linhcan'` trong `class/player.php` vì thế **vốn đã chết**
   từ trước khi rework. Lối vào trang Tẩy hiện tại = nút "🌀 Tẩy Linh Đan" trên trang Linh Căn.
   Cần chốt: (a) thêm `djuse` cho các đan để túi đồ có nút, hay (b) giữ lối vào duy nhất qua trang hệ.

## Sổ nợ plan — lỗi phát hiện khi chạy từng task

| Task | Chỗ trong plan | Thực tế | Cách xử lý |
|---|---|---|---|
| 1 Step 5 | Lệnh tách SQL theo `;` rồi bỏ chunk bắt đầu bằng `--` | 2/4 statement bị bỏ (`player_linhcan_node`, `player_linhcan_skill`) | Gỡ dòng comment TRƯỚC khi tách → `executed=4`, 4 bảng đủ |
| 2 Step 1 | `tassert('A5 chan linh ten kem hanh', title['name'], 'Chân Linh Căn')` | Code Step 4 (§Task 2) nối `' · ' . $domName` → trả `Chân Linh Căn · Kim` | Test bắt buộc FAIL chính code cùng task. Giữ **code** (tên kèm hành đúng ý "tên kèm hành"), sửa **test** thành `'Chân Linh Căn · Kim'` |
| 2 Step 1 | `tassert('A6 tong 60', milestone(60)['percent'], 5)` | `linhcan_milestone()` dùng `floatval` → `5.0`, mà `tassert` so `===` | Nhất quán kiểu: `percent`/`next_percent` khai báo `0.0` ngay từ đầu, test đổi `5`→`5.0`, `0`→`0.0` |
| 2 Step 1 | Test A1 thiếu `linhcan_binh_anh_id()` | Hàm này có trong map inter-task (File Structure) | Thêm `tassert('A1 binh anh', ..., 37)` |
| 4 Step 8 | Gate ký tự "Expected: CLEAN" trên `class/function/linhcan_boss.php` | Dòng 192 **code cũ** chứa 2 ký tự CJK `(dao-ju)` (comment TODO) → gate FAIL ngay khi file chưa kịp sạch | Sửa comment thành tiếng Việt không dấu, giữ nguyên logic; ghi rõ Task 5 sẽ viết lại hàm chứa nó |
| 5 Step 6 | "THAY toàn bộ thân hàm cũ `:167-206`" | Hàm thật đóng ở **`:209`** (3 dòng `return array(...)` dài) — cắt theo 206 sẽ để lại rác | Cắt 167–209, chèn hàm mới |
| 5 Step 6 | `html` in `"{$stone} {$bossElement} Tinh Thạch"` | In key không dấu → "3 hoa Tinh Thạch" | Dùng `linhcan_element_name($bossElement)` → "3 Hỏa Tinh Thạch" (hàm giữ đời Task 2) |
| 5 Step 2 | Test E phần Cleanup `djid IN (34, 35, 37)` | E4 rơi đá hành Hỏa = **djid 32** → nhân test còn rác sau chạy | Thêm 32 vào danh sách dọn |
| 5 (ngoài plan) | `tongquan.php:614` đọc `$dr['da_ngu_hanh_count']` | Key bị Task 5 xóa → trang tổng quan in "Đá Ngũ Hành" số trống + Notice PHP | Viết lại mô tả theo công thức drop mới (`tinh_thach`/`binh_anh` min–max). **Ngoài danh sách File của plan** nhưng là consumer thật. `tongquan.php` **không được commit** (file chưa tracked + `:68` chứa mật khẩu DB local) → bản sửa đang nằm ở working tree |
| 5 (ngoài plan) | `docs/LINHCAN_DEV.md:65,87,162,167` ghi typo `element_bones` + `system dao-ju` | Code hiện tại không hề đọc key `element_bones` (grep 0 kết quả) → 3 dòng là bug report **đã lỗi thời** | Đổi thành `element_bosses`; dòng 162 ghi rõ "ĐÃ SỬA xong"; dòng 167 bỏ CJK. **Không commit** — file legacy chưa tracked, Task 12 sẽ thay |
| — (môi trường) | `tests/test_nguhanh_market.php` chạy ra **12 FAIL**, `tests/test_nguhanh_combat.php` **3 FAIL** | KHÔNG phải regression: web server phpStudy **đang tắt** (`netstat` chỉ có `:3306`; `curl http://localhost/game.php` → exit 7, `http_code=000`, size=0). Mọi assert fail đều là assert đọc HTML qua `get_http()` ("HTTP 200 (have html)" + các assert chuỗi nối tiếp); 2 assert "khong co loi PDO" PASS giả vì chuỗi rỗng không khớp regex. Các suite CLI thuần vẫn xanh: schema 12/12, element 63/63, boss 44/44, quest 14/14, nguhanh 82/82, random 23/23 | Không sửa code. Muốn chạy lại 2 suite này phải bật nginx/Apache trong phpStudy (sẽ hỏi chủ trước khi bật dịch vụ). **Ghi chú nợ**: 2 suite nên fail-fast khi server không reachable thay vì PASS giả |
| 6 Step 2 | Test `tests/test_linhcan_quest.php` của plan chỉ DELETE/`UPDATE game1` rồi gọi hàm | **Không nhân vật nào tồn tại** → `adddj()` đọc `uid` từ `getplayer()` ra `null` → FATAL `Column 'uid' cannot be null` tại `class/player.php:1309` (plan giả định nhân test còn sót từ suite khác) | Thêm seed tự thân: `INSERT INTO game1 (...) VALUES (sid 990112) ON DUPLICATE KEY UPDATE ...` ngay sau PDO. Test không còn phụ thuộc suite khác |
| 6 Step 1 | Plan ghi "per-quest reward key bị xóa — không fatal vì `game/linhcan_quest.php:117` có isset-guard" | Đúng là không crash, nhưng trang quest in **"0 Đá Ngũ Hành"** (thưởng thiệt là 1–2 Bình Anh) → panel thưởng **nói dối** | Sửa sớm 2 dòng `:117-118` + `:124` ở Task 6 (đọc `total_reward_binh_anh_min/max`) thay vì chờ Task 11 viết lại trang. `game/linhcan_quest.php` **chưa tracked** → bản sửa nằm ở working tree, commit cùng Task 11 |
| 6 (ngoài plan) | `tongquan.php:683` dựng `$rows3` từ `reward_linhthach`/`reward_da_ngu_hanh`; `:690` header vẫn 6 cột cho data 4 cột; `:687` ghi "Linh căn tuần → Đá Ngũ Hành" | Consumer thật, không nằm trong File Structure của plan → bảng tổng quan lệch cột + thưởng sai | `$rows3` còn 4 cột (tên/mô tả/hook/mục tiêu), header khớp 4 cột, caption in `500 LT + 1-2 Bình Anh`, nhãn → "LT + Bình Anh Tinh Thạch". `tongquan.php` **có tracked** nhưng không commit kèm (`:68` chứa mật khẩu DB local) — gom vào commit tài liệu riêng ở Task 12 |
| 7 Step 3 | `git rm game/linhcan_upgrade.php` | File **chưa từng được git track** → `git rm` fail; xóa luôn = mất vĩnh viễn (không có trong history) | Archive reversibly: `cp` sang `bak/linhcan_upgrade.20261011_truoc_vongnguhanh.php` rồi `rm` khỏi working tree. Route vẫn xóa như plan |
| 7 Step 3 | "grep Expected: 0 kết quả ngoài `class/function/linhcan.php`" | Còn **2 dòng ngoài plan**: `game/linhcan.php:145` (`$upgradeCmd = encode("cmd=linhcan_upgrade…")`) và `:248` (nút "⬆ Nâng cấp") → sau khi xóa route, nút thành **lối chết** ngay trên trang người chơi đang thấy | Sửa 2 dòng: route → `linhcan_wash`, nhãn → "🌀 Tẩy Linh Đan". `game/linhcan.php` chưa tracked + Task 10 viết lại toàn trang nên **không commit** |
| 7 Step 6 | Plan viết "giữ `djuse` như cũ; đường `djuse=='linhcan'` đã thành chuỗi báo" | `getdaoju(111)->djuse` = **NULL**; không món nào trong `daoju_config.json` có key `djuse` → `usedaoju()` chặn từ `:2019` "Đạo cụ này không thể sử dụng", nhánh `linhcan` **vốn đã chết** từ trước rework | Vẫn đổi nhánh thành chuỗi báo theo plan (không đổi hành vi). Đường dùng đan thật = `game/use_dj.php` (`use_effect.type`). Ghi vào §Chưa làm mục 6: túi đồ không có nút "Dùng" cho đan 111 — chờ chủ chốt |
| 7 Step 8 | Gate "CLEAN" trên `class/player.php` + `game.php` | `class/player.php` có **11 dòng comment chữ Hán** ở `:998, :1034, :1152, :1453, :1459, :1468, :1471, :1850, :2094, :2095, :2324` (comment nội bộ của code gốc Trung Quốc, không phải chuỗi hiển thị cho người chơi) và `game.php` có 2 dòng `:160, :231` dùng cặp ngoặc vuông chữ Hán trong tin nhắn hệ thống — **đều có sẵn trong HEAD** (`git show HEAD:` + cùng scanner = đúng 13 dòng), không phải chuỗi do Task 7 thêm | Không sửa (ngoài phạm vi; 2 dòng `game.php` là style tin nhắn "[Hệ thống]" đang chạy thật). Gate thu hẹp: chỉ quét **dòng do Task 7 thêm** (`git diff -U0` qua byte-level scanner) → 0 ký tự lạ. Scanner: `E:/window/Temp/t7_scan2.php` |
| 7 Step 4 | Plan chỉ yêu cầu `php -l` cho trang mới | Web server phpStudy **đang tắt** nên chưa render được qua router | Dựng harness `E:/window/Temp/t7_render.php` include trực tiếp `game/linhcan_wash.php` (tự seed Kim Lv5/bậc1 + Hỏa Lv12 + 2 đan): xem = 1212 byte đủ 5 hàng, tẩy Kim = `Lv 5→0, bậc 1 giữ nguyên, +4 đá (35), +650 LT, đan 2→1`. **Vẫn CHƯA test qua `game.php` thật** |
| 8 Step 6 | Plan seed `UPDATE game1 SET uhp = umaxhp` cho fixture live | Cột thô `umaxhp` của fixture = **35**, còn `getplayer()` trả `umaxhp` **đã buff = 34079** → cổng MP chặn đúng ("còn trên 3407 HP"), 4 assert live FAIL oan | Không sửa code. Thêm `$lcTopUp()` ghi **giá trị sau buff** (cùng khuôn `game/pve.php:107 changeplayersx('uhp', $player->umaxhp, ...)`) + E2.4 so với `getplayer()->umaxhp` thay vì cột thô |
| 8 Step 6 | Plan assert E3 "đòn thường không chứa tên skill" | Trang PVE in tên skill trên **nút** nên chuỗi "Thủy Hộ Thuật" luôn hiện diện → FAIL do test, không do code | Siết assert thành chuỗi **lời thi triển** (`'Thủy Hộ Thuật: hồi'`, `'khí huyết)!'`) |
| 8 Step 6 | Plan "don that giam mau boss" = 1 đòn | `game/boss.php:363-368` có nhánh `$sb = mt_rand(1,100); if ($sb > 80) { $gphurt = 0; }` → **20% đòn của người chơi bị né**, đơn đòn là trò tung đồng xu | Loop tối đa 5 đòn (`E5.2 ... (sau toi da 5 don)`); đã chứng minh đòn thật bằng probe riêng (`E:/window/Temp/t8_bprobe.php`: 6382 sát thương trên boss mới) |
| 8 Step 7 | Gate "grep Expected: CLEAN" trên `game/pve.php` + `game/boss.php` | 2 file kế thừa có sẵn comment chữ Hán / ngoặc vuông chữ Hán: **pve 4 dòng, boss 36 dòng**. Đo `git show HEAD:` bằng cùng byte-level scanner → **đúng 4 và 36**, số không đổi sau Task 8 | Gate thu hẹp: chỉ quét **dòng thêm trong diff** (`E:/window/Temp/t8_diffscan.php`) → **0 ký tự lạ / 0 dòng**. 3 file còn lại (skill lib, config json, test) CLEAN tuyệt đối. Không sửa comment legacy (ngoài phạm vi) |
| 8 (ngoài plan) | `tests/test_linhcan_element.php` chạy đơn độc | FATAL tại `class/player.php:1309` (`adddj()` cần `game1.uid`): test chưa từng tự seed nhân vật, chỉ xanh khi chạy **sau** `test_linhcan_quest.php` → nợ cách ly có sẵn từ Task 6/7, **không phải regression Task 8** | Thêm seed `game1` tự thân (uid 990112, `ON DUPLICATE KEY UPDATE`) ngay sau PDO → ALL PASS khi chạy một mình. Commit kèm Task 8 |
| 9 Step 1 | Plan dự đoán dong red dau la `N1 total_lv = 0` ("property chua ton tai → null !== 0") | Test cua plan bao `intval($p0->linhcan_total_lv)` → `intval(null) === 0` **PASS ngay** | Red that la **17 dong** (toan bo field hien thi). Giu `intval()` nhu plan, ghi lai doan red |
| 9 Step 1 | `tassert('N2 ten danh hiem', $p1->linhcan_name, 'Chân Linh Căn')` | `linhcan_title()` (Task 2, da commit + da test o `test_linhcan_element:66`) noi `' · ' . $domName` → tra `Chân Linh Căn · Kim` | Lang theo **code da ban** (dung y "ten kem hanh"), sua test → `'Chân Linh Căn · Kim'`. Lan thu 2 lap lai nay (see row Task 2 Step 1) |
| 9 Step 1 | Test N1/N6 cua plan ngầm định "chua mo he → ten rong" | `title_rules` co `pham_can: {"total_lv_max":24}` → tong Lv **0** van khop rule nay → ten = `Phàm Căn`, icon `🌱`, KHONG phai `''` | Doi thanh assert dung thoi cuan: `linhcan_name = 'Phàm Căn'`, `linhcan_title_id = 'pham_can'`, `linhcan_icon = '🌱'`. Bang zhuangtai van AN vi dieu kien la `!empty($player->linhcan_element)` (element van `''`) |
| 9 Step 2 | `linhcan_node_rows()` goi trong khoi hien thi cua `getplayer()` | `linhcan_calc_bonuses()` **da goi** `linhcan_node_rows()` ben trong → 1 lần cache miss = **2 SELECT** cung mot bang | Chap nhan theo plan (getplayer duoc cache; do so: fixture 2 node → 21 assert PASS, khong co bai nào ve hieu nang). Ghi no: co the gop 1 query neu ve sau can |
| 9 (ngoai plan) | `tests/test_nguhanh_random.php` nhan `linhcan_id = 3/21` roi assert `linhcan_element = 'thuy'/'all'` | Day chinh la no du bao o §Kiem ke Task 1. Sau Task 9 → **4 FAIL**: fixture element `''`, C element `''`, C rate `[90]` thay vi `[95]`, ti le 89.7% | Viet lai theo nguon moi: fixture seed **node thuy 10/0** (+0 → rate 90), nhanh C seed **node hoa 20/0** (+10 = dung bang `all` cu → 90+10 chan tran 95). Them `DELETE FROM player_linhcan_node` vao `rnd_cleanup` (test khong con de lai rac / doc rac). **ALL PASS 2600 roll that** |
| 9 Step 5 | (harness cua toi) `t9_smoke.php` tra `len=3` cho ca 12 route | **Bug cua harness** (`shell_exec('curl -s ' . escapeshellarg($url))` goi lien tiep tren Git Bash), KHONG phai loi game — cung URL do curl truc tiep → HTTP 200, 7712 byte | Do lai bang vong lap curl trong bash: `linhcan`/`linhcan_wash`/`linhcan_quest`/`zhuangtai` = HTTP 200, `grep -c "Fatal error\|Uncaught\|SQLSTATE\|Warning:"` = **0** |



---

## Trang Vong Ngu Hanh — Task 10, 2026-10-11

### 10.1 Route va file (ban do hien trang)

| Thu | Gia tri | Ghi chu do duoc |
|---|---|---|
| Route trang | `cmd=linhcan` (`game.php:352-355`), include `game.php:596` | trang doc `$cmd/$sid/$canshu/$el` toan cuc (`parse_str` `game.php:114`), **cam `$_GET`** |
| Chon hanh | `&el=kim\|moc\|thuy\|hoa\|tho` | `el=xyz` -> tu ve hanh chu dao, khong loi, khong notice |
| Hanh dong | `&canshu=train` / `&canshu=break` | GET thuong, khong form/JS; `canshu=boom` bi bo qua |
| Ban | `cmd=linhcan_quest`, `cmd=linhcan_boss` | **van la trang cu** — Task 11 viet lai |
| Trang | `game/linhcan.php` 208 dong (viet moi hoan toan) | header/init copy khuon `game/huyetmach.php:1-8` |
| CSS | `css/mystyle.css:7664-7779`, **77 rule `.lc-`** | cuoi file, sau khoi `hmq-*` cua Mach Hyet |
| Anh vong | `images/linhcan/vong-ngu-hanh.jpg` 900x900, 172.337 B | nguon **ImageGen** (no 3, muc 10.7) |
| Cache | `game.php:502` `css/mystyle.css?v=2026101104` | moi sua CSS **bat buoc bump** |
| Anh chung minh | `docs/img/linhcan/`: `_lc390_dangcho`, `_lc1337_dangcho`, `_lcfx0.._lcfx4`, `_lcvo`, `_fxbang` | Chrome CLI, KHONG browser-use MCP |

### 10.2 Cay cau truc `.lc-*`

```
.lc-wrap.lc-fx{0..4}  (inline: --lc-tier, --lc-color)
├─ .lc-notice[.bad]              thong bao train/break (chi hien khi co $lcmsg)
├─ .lc-card.lc-card-ring
│  ├─ .lc-head  → .lc-title "LINH CĂN" + .lc-badge (danh hieu · tổng Lv)
│  ├─ .lc-rail  → 5 × a.lc-stage-chip(.is-done|.is-cur|.is-lock|.is-view)
│  │                └─ .lc-chip-icon / .lc-chip-name / .lc-chip-stage
│  ├─ .lc-viewline               "Kim · bậc 2 (Địa Căn) — Lv 40/50 — <skill>"
│  ├─ .lc-forge-wrap
│  │  ├─ .lc-stage  → img.lc-ring-img + 5 × a.lc-gem.slot-<el> + .lc-core[.lc-core-vo]
│  │  │                 .lc-gem └─ .lc-gem-icon / .lc-gem-lv   (--gem, --glow inline)
│  │  │                 .lc-core └─ .lc-core-icon / .lc-core-name (+ :before halo tinh)
│  │  └─ .lc-forge  → a.lc-btn-train · a.lc-btn-break|a.lc-btn-boss|span.lc-btn-off
│  └─ nav.lc-tabbar → 4 × a(<i>icon</i><b>nhãn</b>), tab dang dung class="on"
└─ .lc-card.lc-cols (≥601px: 2 cot)
   ├─ .lc-col → .lc-col-title "Thuộc Tính Kích Hoạt" + 5 × .lc-attr-row[.is-dom] + .lc-ms
   └─ .lc-col → .lc-col-title "Đột Phá <hanh>" + .lc-reqs(5 × .lc-reqs-row + .lc-reqs-num)
                + .lc-skillcard(.lc-skillgate)
```

### 10.3 Toa do 5 ngoc — do tu ANH that, khong phai so tron

Anh 900x900. Buoc 1: GD centroid theo mau (`E:/window/Temp/lcimg.php`).
Buoc 2: GD do lai trong vung nhiep (`lctho2.php`) vi aura mau lem ra ngoai dai.
Buoc 3: **ep doi xung qua tam 50%** (dia vong doi xung, centroid lech 1-2% do aura),
roi verify bang mat tren `_lc390` + `_lc1337`: ca 5 ngoc nam vung tren dai te.

| Hanh | GD centroid (buoc 1) | GD vung nhiep (buoc 2) | **CSS chot** | px tuong ung tren anh 900x900 |
|---|---|---|---|---|
| Kim | 50.2% / 12.9% | (dai trang, khong do mau) | `left:50%; top:12.9%` | 450 / 116 |
| Moc | 84.3% / 36.5% | 79.3% / 37.5% | `left:85.4%; top:37%` | 769 / 333 |
| Thuy | 13.6% / 37.5% | 16.0% / 37.4% | `left:14.6%; top:37%` | 131 / 333 |
| Hoa | 72.9% / 73.7% | 72.0% / 72.1% | `left:72.4%; top:72.5%` | 652 / 653 |
| Tho | (vang — giong Kim) | 28.2% / 71.3% | `left:27.6%; top:72.5%` | 248 / 653 |

Do lại trong trinh duyet (`_lcm.html`, Chrome headless `--dump-dom`, zoom ve 1):
`slot-kim @50.0,12.9 · slot-moc @85.4,37.0 · slot-thuy @14.6,37.0 · slot-hoa @72.4,72.5 · slot-tho @27.6,72.5`
— trung khop tuyet doi ca @390 va @840, ngoc 56px.

### 10.4 Thang hieu ung `lc-fx0 … lc-fx4` (phan bac theo bac dot pha CAO NHAT trong 5 hanh)

`$lcmaxStage = max(stage cua 5 node)` → class `lc-fx<N>` tren `.lc-wrap`.
Bac **thap nhat van phai thay gi do**: `lc-fx0` co halo tinh sau nhan hieu (`.lc-core:before`, opacity `.05`).

| Bậc | `--lc-aura` | `--lc-glow` | Halo tinh nhan hieu | Nhãn hiệu thở | Vòng conic quét | 5 ngọc phát tia | Cả vòng tự xoay |
|---|---|---|---|---|---|---|---|
| fx0 | .05 | 6px | ✔ (tĩnh) | ✘ | ✘ | ✘ |  |
| fx1 | .07 | 9px | ✔ | ✔ `lc-breathe 3.2s` | ✘ |  | ✘ |
| fx2 | .10 | 12px | ✔ | ✔ | ✔ `lc-sweep 12s` | ✘ |  |
| fx3 | .14 | 16px | ✔ | ✔ | ✔ | ✔ `lc-ray 2.4s` | ✘ |
| fx4 | .18 | 20px | ✔ | ✔ | ✔ | ✔ | ✔ `lc-spin 40s` |

**Số đo thật** (`_lcm.html` dump computed style của `::before/::after`, state kim lv50/stage3):

```
FX=lc-fx3 | .lc-stage::after op=0.18 anim=lc-sweep 12s blend=screen
          | .lc-gem::before  op=0.5  anim=lc-ray  2.4s
          | .lc-core::before op=0.14 anim=none    blend=screen   (halo tinh)
          | ringAnim=none   coreAnim=lc-breathe
```
Quet 5 bac: `fx0` → sweep `op=0 anim=none`; `fx1` → `coreAnim=lc-breathe`, sweep van 0;
`fx2/3/4` → sweep `op=.14/.18/.22`; `fx4` → `ringAnim=lc-spin`. Khong bac nao mat het dau.

**`prefers-reduced-motion: reduce`** (do bang `chrome --force-prefers-reduced-motion`, state fx2):
`anim=none` cho **moï** rule, `.lc-stage::after → display:none`; halo tinh `.lc-core::before` **van con**
(`op=0.1`) → tat hieu ung khong lam trang ve man hinh tinh khac.

**Danh hieu Vu Cuc (id `vo_cuc`, ten hien ra: "Vô Cực Linh Thể")**: `title_rules` khop theo **tong Lv 5 hanh**,
khong phai bac. Seed ca 5 hanh `lv=90 stage=4` → tong 450 → `.lc-core` duoc them `lc-core-vo`,
ten chay cau vong 6 mau (`lc-rainbow 6s`, `background-clip:text` dat **SAU** shorthand `background:`
vi thieu thu tu do lam an het chu — da dung 1 lan o mach huyet). Anh: `_lcvo.png`.

### 10.5 Loi chot va ten nut (nguyen van, Task 11/12 phai dung lai dung chuoi nay)

| Vi tri | Chuoi |
|---|---|
| Tieu de card | `LINH CĂN` |
| Badge | `<icon> <danh hieu> · tổng Lv <tổng>` |
| Rail chip | `<icon>` / `<tên hành>` / `bậc <N>` (3 dong cot) |
| Viewline | `<icon> <hành> · bậc <N> (<tên bậc>) — Lv <cur>/<cap> — <skill>` |
| Skill chưa mở | `🔒 kỹ năng cần <tên bậc unlock>` (unlock = `Địa Căn`) |
| Nút luyện | `LUYỆN <HÀNH HOA>` + `<stone> đá (có <have>) · <lt> LT` |
| Trần Lv | `ĐÃ CHẠM TRẦN Lv <cap>` (span `.lc-btn-off`, khong phai link) |
| Nút đột phá (đủ) | `✦ ĐỘT PHÁ LÊN <tên bậc kế>` |
| Nút boss (thiếu) | `⚔ Boss đột phá <hành>` |
| Trần bậc | `<Thánh Căn> — tối đa` |
| Cột trái | `Thuộc Tính Kích Hoạt` / dong: `<icon> <hành> Lv <N> · <stat> +<val>, …` |
| Cộng hưởng | `Cộng hưởng tổng Lv <N>: +<%> <tên mốc>` + ` — mốc kế: Lv <N> (+<%> <tên>)` hoặc ` — đã đạt đỉnh` |
| Cột phải | `Đột Phá <icon> <hành>` + `[Đủ]`/`[Thiếu]` `<nhãn> <cur>/<need>` |
| Thẻ skill | `<icon> <tên> — <mô tả> — đã mở · hồi <N> phút · tốn <N>% khí huyết` |
| Notice | `Luyện <hành> lên Lv <N+1>.` / `Chưa đủ điều kiện: <danh sách row thiếu>.` |
| Tab bar | `☯ Luyện Căn` · `📜 Nhiệm Vụ` · `👹 Boss Ngũ Hành` · `👤 Nhân Vật` |

Nhan nhieu dong trong nut (`.lc-btn` 130x60 @390) → **can giua bang flex, khong dung `.main button`**
(khuon `.main button` cua he thong lam lech chu — xem `docs/UI_HUYETMACH.md` §13).

### 10.6 Quy trinh kiem chung (lap lai duoc tren may nay)

```bash
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
# 0) Server dev: Apache phpStudy THAT — dung built-in PHP 5.6 (dang chay: 8199, 8123)
#    D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -S 127.0.0.1:8199 -t .
# 1) Lint + quet ky tu la + dead-link (Bat buoc, khong duoc bo)
$P -l game/linhcan.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" game/linhcan.php css/mystyle.css || echo CLEAN
grep -n "linhcan_upgrade" game/linhcan.php || echo NO-DEAD-LINK
# 2) Route that (fixture e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2, uid 987650)
cd E:/window/Temp && $P lcget.php "cmd=linhcan&sid={SID}&el=kim"   # in LEN + PHP-ERR, luu lcpage.html
$P lcread.php                                                      # doc het label tren trang (khong phai mo mat)
$P lcget.php "cmd=linhcan&sid={SID}&el=hoa&canshu=train"           # duot that: Lv tang + notice
# 3) Do layout / pseudo-element (zoom ve 1 trong iframe harness)
bash E:/window/Temp/lcshot.sh kim MEASURE 390
bash E:/window/Temp/lcshot.sh kim MEASURE 840
# 4) Anh chung minh (Chrome CLI, KHONG dung browser-use MCP)
bash E:/window/Temp/lcshot.sh kim _lc390_dangcho.png 390 1360 500,1400
bash E:/window/Temp/lcshot.sh kim _lc1337_dangcho.png 1337 1200 1337,1200
# 5) Seed thang fx: lcempty.php <sid> roi lcseed.php <sid> <el> <lv> <stage>
# 6) Ghep 6 anh fx0..fx4+vo thanh 1 bang doi chieu
$P E:/window/Temp/lcfx.php "" 195 612 165      # -> docs/img/linhcan/_fxbang.png
```
**Ba bay harness da dung hoat dong nay** (khong phai loi game):
1. `cmd` blob **dung mot lan** — `encode` co chong replay; dung lai blob cu → `game.php:391`
   `refresh:1;url=index.php`, body **3 byte (chỉ BOM)**. Moi lan goi/chup **phai encode lai**.
2. `php -S` **mot luong** → request sat lien tiep co luc tra 3 byte; nghi ngò thi goi lai, dung ket luan ngay.
3. `--screenshot` can **duong dan tuyet doi kieu Windows** (`D:\...`), duong dan tuong doi bi "cannot find the path".

### 10.7 SO NO Task 10 — CHƯA test / CHƯA làm

| # | No | Muc do | Ghi chu |
|---|---|---|---|
| 1 | **CHAM THAT tren mobile** (ngon tay, 5 ngoc + 4 tab + 2 nut) | **CHƯA TEST** | moi thu chi duy cua Chrome headless 390px trong iframe + curl. Chua ai ngam may that, chua test vuong tay 44px (nut `.lc-btn` cao 60px ✔ theo so, nhung ngoc 56px sat nhau o `slot-moc/thuy` cach nhau ~90px — phai ngam tren may). |
| 2 | **fx trong game that** (tốc độ thật, không virtual-time) | **CHƯA TEST** | `--virtual-time-budget=18000` dong dong ho cua Chrome → anh chup **khong** chung minh duoc `lc-sweep 12s`/`lc-spin 40s` chay mong hay giot. Moi thay duoc: computed style dung ten/thoi luong/opacity (10.4). |
| 3 | **Anh vong ngu hanh: nguon ImageGen** | **NO du lieu** | `E:/window/Temp` + 2 thu muc source game (MuH5 / TuTienPhucMa / NaTra tren F: va E:) **khong co** asset vong ngu hanh nao → dung anh ImageGen `vibe_images/vong-ngu-hanh_1791658141369_c6b27b20.png`, GD resample 1024→900 JPEG. Chu du an quyét: thay anh khac → **phai do lai 5 toa do 10.3** va bump `$lcImgVer` (`game/linhcan.php:10`) . |
| 4 | Trang `linhcan_quest` + `linhcan_boss` | **VAN LA TRANG CU** | Task 11. 2 tab tren bar da tro dung route nen khong dead-link, nhung noi dung chua theo khuon `.lc-*`. |
| 5 | Di trú 22 căn cũ → node Ngũ Hành | **CHƯA chạy** | Task 12. Trang nay doc bang `player_linhcan_node`; nguoi choi that chua co dong nao → ho thay Lv 0/bậc 0 (khong loi, nhung phai di trú). |
| 6 | Deploy production | **CHƯA** | Repo **khong co remote**; moi thay doi o day phai di tay + `docs/DEPLOY_PRODUCTION_LINHCAN.md` (Task 12 viet). |
| 7 | `.lc-gem` khi 5 hanh deu Lv 90 (chu "Lv90" dai nhat) | **MOI TEST 1 state** | `_lcvo.png` co 5 ngoc Lv90 → van trong tron ngoc 56px. Chua test Chu Nhat nho nhat thuc te (man 320px). |
| 9 | Harness `_lcshot.html` + `_lcm.html` **con nam trong thu muc game** (web root) | **PHAI XOA** | dung cho `lcshot.sh` do/chup; Task 11 can lai, **Task 12 Step cuoi phai xoa het** (khi do copy `E:/window/Temp/`). Chua commit lan nao (untracked). |
| 8 | Server dev 8199 (PID 12104) + 8123 (PID 24600) | **DANG MO** | Ban giao phai tat hoac bao chu du an; `tongquan.php` **dang co credentials** trong file → **khong duoc commit** (Task 12). |

### 10.8 Loi da tim va sua trong Task 10

| Bay | Nguyen nhan that | Cach sua |
|---|---|---|
| 5 ngoc lech +4px truc x va y, `display:flex` mat, icon+Lv nam canh nhau | `css/gamecss.css:70-75` `a:link{display:inline-block;margin:4px 3px 2px 4px}` **dac (0,1,1)** > `.lc-gem` (0,1,0) | reset bang `.main a.lc-gem,.main a.lc-stage-chip,.main a.lc-btn` (**0,2,1**) — cung cach khuon `.main .hm-act` da dung o mach huyet. Sau khi sua: do lai trung khop 100% toa do 10.3 |
| Chip rail in "Kimbậc 2" dinh lien | cung `a:link` lam `.lc-stage-chip{display:flex;flex-direction:column}` bi vo hieu hoa | `.main a.lc-stage-chip{display:flex;flex-direction:column;align-items:center;gap:2px}` → do ra 56x76 (3 dong) |
| Nhan hieu 2 dong cheo ra ngoai dai giua | `.lc-core{width:38%}` hep hon chu "Linh Căn Đã Chắc Lọc" (130px) | `width:42%` + `.lc-core-name{white-space:nowrap}` → do ra `.lc-core-name` 133x23 @390 (1 dong), 192x23 @840 |
| `Parse error: unexpected ')' game/linhcan.php:193` | dong nhat ternary 2 nhanh vao 1 bieu thuc, dong dau `)` truoc `:` | tach `$lcSkillCard` thanh bien tinh rieng phia tren khoi render |
| `max-width:840px` tren `.lc-wrap` khong tac dung | khung chung cua server **660px** (do `.hm-wrap` cung ra 622px @840) | **khong noi khung chung** — xoa override chieu rong, o ≥601px chi doi `.lc-cols` 2 cot + `.lc-stage{max-width:520px}` |
| The skill: mo ta dinh lien "…1 turn đã mở · hồi 5 phút" | thieu tach giua `description` va `lc-skillgate` | them ` — ` truoc `<span class="lc-skillgate">` |

---

## 11. TASK 11 — Trang Nhiệm Vụ + Boss Ngũ Hành + xoá 13 hàm cũ (22 căn)

**Pham vi:** 2 trang `game/linhcan_quest.php` + `game/linhcan_boss.php` viet lai theo khuon
`.lc-*` Task 10; khoi CSS `.lcq-*` / `.lcb-*` / `.lcw-*`; **xoá 13 ham dead-code** cua duong
22 can + upgrade RNG; `linhcan_quality_name` doi nghia theo spec §3.1.

### 11.1 File da cham

| File | Truoc | Sau | Ghi chu |
|---|---|---|---|
| `game/linhcan_quest.php` | 61 (trang cu) | **88** | viet lai hoan toan, khong inline style |
| `game/linhcan_boss.php` | 96 (trang cu) | **222** | **mot trang hai che do**: khong `el` = the gioi, `el=hanh` = ca nhan |
| `class/function/linhcan.php` | **1006** | **621** | xoa 385 dong dead code (13 ham + docblock moi) |
| `css/mystyle.css` | 7772 | **7843** | +71 dong khu Task 11 (58 rule), khong sua rule hm/nb/lc cu |
| `game.php` | — | — | chi `:502` mystyle.css `v=2026101111` |
| `tests/test_linhcan_pages.php` | — | **172** | **MOI** — 40 assertion chay qua HTTP that (cong 8199) |

### 11.2 Hai nut chet — chung cu + chinh sach xu ly (CHO CHU DU AN)

`game.php:114` `parse_str` bien moi cap trong `cmd=` thanh **bien toan cuc**, va
`$cmd` **luon bang ten trang** (route `game.php:352-355`). Vi vay dieu kien cu:

| Nut | Dong cu | Chinh | Lan cuoi chay |
|---|---|---|---|
| NHAAN THUONG | `game/linhcan_quest.php:8` (cu) `if ($cmd == 'claim_reward')` | `$cmd == 'linhcan_quest'` mai mai → nhanh **CHET** | **khong co** (toan bo lich su local) |
| TAN CONG boss | `game/linhcan_boss.php:37` (cu) `if ($cmd == 'attack_lc_boss')` | tuong tu → **CHET** | **khong co** |

**Hau qua nguoi dung:** khong ai tung nhan duoc thuong quest tuan (500 LT + Binh Anh) va khong
ai danh duoc boss Linh Can the gioi bang nut do — toan bo duong `linhcan_quest_claim()` /
`linhcan_boss_drops()` **chua lan nao chay trong game that**.

**Chinh sach de xuat (CHƯA lam, doi chu quyet):**
1. **Khong auto-refund.** Quet `player_linhcan_quest` xem bao nhieu nguoi choi co `completed=0`
   va du 5/5 → cho them mot lan `claim_reward` mien phi? Rui ro kinh te (moi nguoi +500 LT).
2. Phuong an lanh: **chi audit** — danh sach nguoi bi anh huong, khong tra lai; nguoi choi tu
   nhan khi version moi len (ho mo lai trang, nhan binh thuong).
3. **Chot phuong an truoc khi deploy production** (Task 12).

Khuon dung nay: `game/huyetmach_boss.php:16` `isset($attack)` — **da kiem chung** bang G2/G4
(chup qua URL that, tien +500 LT that, HP boss giam that).

### 11.3 Hop dong da dung (khong doi)

| Ham | Trang goi | Y nghia |
|---|---|---|
| `linhcan_quest_get_progress` / `linhcan_quest_claim` | quest | claim tra `array(ok,msg,linhthach,binh_anh)`; `completed=1` khoa nhan doi |
| `lcboss_active` → `lcboss_spawn` | boss `el=` | **tu spawn khi mo trang** — gate `stage<max && level>=cap && !killed` nam TRONG ham, trang khong tu suy dien |
| `lcboss_calc_damage` / `lcboss_boss_counter` | boss `el=` | counter = `max(1, atk - ufy*0.6)` |
| `lcboss_mark_killed` / `lcboss_drops` | boss `el=` | **`mark_killed` tu UPDATE status killed, hp=0** → trang **KHONG** duoc UPDATE lai (G5 kiem chung) |
| `linhcan_boss_check_spawn` → `linhcan_boss_spawn` | boss the gioi | song 2h, chung toan server |
| `linhcan_boss_mark_killed` / `linhcan_boss_drops` | boss the gioi | drop **that** vao `playerdaoju` + `INSERT ggliaotian` (mau: huyetmach_boss.php:36-39) |

**Bat buoc:** hook `kill_boss` / `receive_stone` **nam san** trong `lcboss_drops` +
`linhcan_boss_drops` + `linhcan_give_stone`. Trang **tuyet doi khong** goi
`linhcan_quest_update` lap lai → G5 do delta = **dung 1** (khong double-count).

### 11.4 So do layout (@390px trong iframe harness, `body.zoom=1`)

```
.lcq-wrap 350x708 @13 | .lcq-card 330x596 @23 pad 14/16 | .lcq-head 296x22 (1 dong)
.lcb-wrap 350 @13 | .lcb-card 330 | .lcb-chips 6 chip (3+2) | .lcb-log 14px
scrollW = 375 < 390 → khong tran ngang
```
Trung khop ho Linh Can: `.lc-wrap` 350 @13 / `.lc-card` 330 @23 (Task 10) va `.hm-wrap` 350
(Mach Huyet). **Da doi chieu 1:1 bang anh**: `_lcb_kim.png` vs anh tham chieu
`huyetmach_boss` cung viewport (anh ref de ngoai repo, khong commit).

### 11.5 Lech so voi CSS trong plan (4 quyet dinh, co so do)

| Plan | Thuc te | Ly do |
|---|---|---|
| `.lcb-chip.is-active` nen = `var(--lcb-color)`, chu trang | nen `rgba(148,163,184,.14)` + **border/glow** mau hanh, chu trang | do ra **trang tren #C0C0C0 = ~1.4:1** (Kim) → khong doc duoc |
| `.lcb-icon` 52px **hai icon** (hanh + boss) | **mot icon hanh**, 52px | 2 bang `linhcan_boss` / `linhcan_boss_personal` **khong co cot `icon`** → `$row['icon']` chi ton tai tren request vua spawn → trang **doi hinh lien tuc** (coin+coin vs coin+devil) |
| `.lcb-name` color = `var(--lcb-color)` | `color:#fff` + `text-shadow:0 0 10px var(--lcb-color)` | **Tho #8B4513** va **Moc #228B22** tren nen navy ~2:1 → mat ten boss; mau hanh van mang qua border + glow + chip |
| card nen `rgba(30,30,40,.95)` + border **2px mau dam** | `linear-gradient(160deg,#111a35,#0b1024)` + **1px** `rgba(148,163,184,.16)` (border boss van giu mau hanh) | so anh 1:1 voi Task 10: khung mau sac + nen xam dac lam 3 tab **cung mot tinh nang** nom nhu 3 game khac nhau |

Nho: `.lcb-footnote` 11→**12px**, `#777`→`#9fb3c8` · `.lcb-log` 13→**14px** · `.lcb-chip`
radius 14→**10** + padding 10/14 (~37px cao, de cham) · `.lcb-btn` gradient giong het
`.lc-btn-boss` Task 10 · `.lcb-empty` + `.lcb-level` + `.lcq-week` + `.lcq-desc` doi sang
`#9fb3c8` cho de doc tren navy.

### 11.6 Dead code: 13 ham da xoa (chung cu tung ham)

`linhcan_config_path` · `linhcan_config_all` · `linhcan_config_get` · `linhcan_random` ·
`linhcan_random_with_quality` · `linhcan_assign` · `linhcan_info` · `linhcan_tier_name` ·
`linhcan_upgrade_config_path` · `linhcan_upgrade_config_all` · `linhcan_get_upgrade_info` ·
`linhcan_find_next_tier_id` · `linhcan_upgrade`.

Cua ngan: grep ten tung ham tren **toan repo** (`.php`, loa tru chinh `class/function/linhcan.php`)
→ **0 caller ngoai file** cho ca 13 ham (ke ca `game/linhcan.php`, `class/player.php`, `api/`, `admin/`).
Sau khi xoa: `php -l` sạch + **10 suite** chay lai (ket qua 11.9).

`linhcan_quality_name($stage)` giu lai nhung **doi nghia** theo spec §3.1: tra ten bac
(`linhcan_stage_def`) thay vi map 1..5 "Tap/Chan/Dia/Thien/Tien Thien". **Hien 0 caller**
→ doi an toan; giu lai vi `docs/` va admin co the can.

**§3.2 quyet dinh:** file sau khi xoa con **621 dòng < 700** → **giu mot file `linhcan.php`**,
khong tach (tach chi them dead-index).

`class/config/linhcan_config.json` + `linhcan_upgrade_config.json` **van con tren dia** —
Task 12 di trú xong moi chuyen sang `bak/linhcan/`.

### 11.7 Sổ nợ Task 11 — CHƯA test / CHƯA làm

| # | No | Muc do | Ghi chu |
|---|---|---|---|
| 1 | **Cham that tren dien thoai** (6 chip + nut TAN CONG + tab bar) | **CHƯA TEST** | moi chung minh la Chrome headless 390px trong iframe. Chip cao ~37px **duoi** nguong 44px → phai ngam tren may that. |
| 2 | **Boss ca nhan trong vong lap 2h / reset tuan that** | **CHƯA TEST** | `quest_week` dung `date('Y').date('W')`; chua choi qua ranh gioi tuan (Chua Nhat 24h). |
| 3 | **`lcboss_spawn` tu spawn khi MO TRANG** | **CHƯA xin phep chu** | thiet ke plan cho phep, nhung nghia la *mo tab Boss = tao dong DB*. Can chu chot: giu nguyên hay doi thanh nut "Khiêu chiến"? |
| 4 | **2 nut chet trong lich su — co refund khong** | **CHO CHU QUYET** | xem 11.2. |
| 5 | `test_nguhanh_market.php` **12 FAIL** + `test_nguhanh_combat.php` **3 FAIL** | **MOI TRUONG, khong phai regression** | 2 test cu **hardcode `http://localhost/`** (Apache :80 **DANG TAT**); FAIL dau cua combat la `HTTP 200 (have html) got:false`. **12 FAIL nay da co tu Task 5** (dem khong doi). Fix = doi sang cong 8199 hoac bat Apache (can chu cho phep). |
| 6 | Header "Linh thạch" **le mot request** sau khi claim/kills | **BIET, KHONG SUA** | `game.php` render header **truoc** khi include page → moi trang lam bien currency deu tre 1 lan tai. Tien **DB van dung** (G2 do `uyxb` +500). Pre-existing toan he thong (ca `huyetmach_boss`). |
| 7 | Harness `_lcq.html` + `_lcshot.html` + `_lcm.html` nam trong web root | **PHAI XOA Task 12** | untracked, chua commit lan nao. |
| 8 | `game/linhcan_quest.php` + `game/linhcan_boss.php` + `tests/test_linhcan_pages.php` + `docs/img/linhcan/*` | **UNTRACKED** | commit Task 11 **phai `git add` tung file**; **TUYET DOI khong add `tongquan.php`** (`:67-68` chua `root` / mat khau DB). |
| 9 | Deploy production | **CHƯA** | repo khong co remote; Task 12 viet `docs/DEPLOY_PRODUCTION_LINHCAN.md`. |

### 11.8 Lỗi đã tìm + sửa trong Step 7 (chup anh / do 1:1)

| Bay | Nguyen nhan that | Cach sua + chung minh |
|---|---|---|
| **Nhan xong thuong ma nut NHAAN THUONG van con** | `$isCompleted = !empty($questData['completed'])` tinh **TRUOC** khoi `if (isset($claim_reward))`; `claim()` co `UPDATE completed=1`, trang doc lai `$questData` nhung **khong tinh lai** `$isCompleted` | doi thu tu (tinh **sau** khi claim). Test G2 them 2 assertion: page claim **khong con** `class="lcq-btn"` + status doi chu. **Khong phai loi mat tien** — `claim()` tu tu choi lan 2 (`Da nhan thuong tuan nay roi.`), chi sai hieu thi. |
| Boss nom "lech" gia hai trang thai | `.lcb-icon` in `icon_hanh + icon_boss`, ma `icon_boss` **khong co trong DB** | 11.5 dong 2 — chi dung icon hanh. |
| Ten boss **Thổ** gan nhu vo hinh | `#8B4513` tren `#111a35` ~2:1 | 11.5 dong 3 — chu trang + glow mau hanh; do lai bang anh boss Thổ. |
| Title quest **xuong 2 dong**, "Tuần 202641" bi nep | them `letter-spacing:1px` lam `.lcq-title` 211→228px vuot 294px trong khi `.lcq-week` can 68px | bo letter-spacing + `white-space:nowrap` ca hai; do lai `.lcq-head 296x22` (1 dong). |
| Status "Đang thực hiện — nhận khi đủ 5/5" hien khi **da du 5/5** | trang chi co 2 trang thai (`is-open` / `is-done`) | them `is-ready` + chu "Da du 5/5 nhiem vu — nhan thuong ben duoi". |
| Seed quest bi mat sau khi chay test | `test_linhcan_pages.php` **xoa** dong `player_linhcan_quest` cua fixture luc cleanup → `UPDATE ... WHERE sid` **0 row**, `get_progress` tao dong moi = 0/5 | seed bang `DELETE + INSERT` (khong dung UPDATE mot minh). |

### 11.9 Chung cu Task 11

* `php -l` sạch: `game/linhcan_quest.php` · `game/linhcan_boss.php` · `class/function/linhcan.php` · `game.php`.
* Quet ky tu la (CJK `4E00-9FFF`, smart quotes, NBSP) trong 5 file cham: **sach**.
  Chu `U+3010/3011` trong `INSERT ggliaotian(name,...)` **co y** — mau nguyen ven
  `game/huyetmach_boss.php:36`, nam NGOAI vung CJK nen khong bi cua ngan quet.
* `tests/test_linhcan_pages.php`: **40/40 ALL PASS** qua HTTP that (cong 8199), gom:
  G1 render · G2 claim that (+500 LT, +1-2 Binh Anh, `completed=1`, **an nut**) · G3 chan claim kep ·
  G4 boss the gioi HP giam + phan dong ghi `game1` · G5 boss ca nhan tu spawn → giot →
  `status=killed`, `hp=0` → **khong UPDATE lai** → da Kim vao toi → hook `kill_boss` **+1 dung** ·
  G6 `uhp=0` khong mat mau · G7 trang tay linh · G8 `el=kimchi` tu suy ve the gioi, khong loi PHP.
* 9 suite con lai: `test_linhcan_{boss,combat,element,passive,quest,schema}` · `test_nguhanh` ·
  `test_nguhanh_random` → **ALL PASS**. `test_nguhanh_{market,combat}` → 11.7 dong 5.
* 6 anh chung minh (`docs/img/linhcan/`, 390px, CSS `v=2026101111`):
  `_lcq_partial.png` (2 bar le) · `_lcq_ready.png` (5/5 + nut) · `_lcq_claim.png` (da nhan, an nut) ·
  `_lcb_world.png` (Moc Linh Vuong) · `_lcb_kim.png` · `_lcb_kim_hit.png` (log chien dau + bar 40%).
