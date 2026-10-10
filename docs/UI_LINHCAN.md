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


