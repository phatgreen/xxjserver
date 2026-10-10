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
| 5 (ngoài plan) | `docs/LINHCAN_DEV.md:65,87,162,167` ghi typo `element_bones` + `system道具` | Code hiện tại không hề đọc key `element_bones` (grep 0 kết quả) → 3 dòng là bug report **đã lỗi thời** | Đổi thành `element_bosses`; dòng 162 ghi rõ "ĐÃ SỬA xong"; dòng 167 bỏ CJK. **Không commit** — file legacy chưa tracked, Task 12 sẽ thay |
| — (môi trường) | `tests/test_nguhanh_market.php` chạy ra **12 FAIL** | KHÔNG phải regression: Apache phpstudy **đang tắt** (`netstat` chỉ có `:3306`; `curl http://localhost/game.php` → exit 7, `http_code=000`, size=0). 12 assert fail đều là assert đọc HTML qua `get_http()`; 2 assert "khong co loi PDO" PASS giả vì chuỗi rỗng không khớp regex | Không sửa code. Muốn chạy lại suite này phải bật Apache trong phpStudy. **Ghi chú nợ**: suite nên fail-fast khi server không reachable thay vì PASS giả |
| 6 Step 2 | Test `tests/test_linhcan_quest.php` của plan chỉ DELETE/`UPDATE game1` rồi gọi hàm | **Không nhân vật nào tồn tại** → `adddj()` đọc `uid` từ `getplayer()` ra `null` → FATAL `Column 'uid' cannot be null` tại `class/player.php:1309` (plan giả định nhân test còn sót từ suite khác) | Thêm seed tự thân: `INSERT INTO game1 (...) VALUES (sid 990112) ON DUPLICATE KEY UPDATE ...` ngay sau PDO. Test không còn phụ thuộc suite khác |
| 6 Step 1 | Plan ghi "per-quest reward key bị xóa — không fatal vì `game/linhcan_quest.php:117` có isset-guard" | Đúng là không crash, nhưng trang quest in **"0 Đá Ngũ Hành"** (thưởng thiệt là 1–2 Bình Anh) → panel thưởng **nói dối** | Sửa sớm 2 dòng `:117-118` + `:124` ở Task 6 (đọc `total_reward_binh_anh_min/max`) thay vì chờ Task 11 viết lại trang. `game/linhcan_quest.php` **chưa tracked** → bản sửa nằm ở working tree, commit cùng Task 11 |
| 6 (ngoài plan) | `tongquan.php:683` dựng `$rows3` từ `reward_linhthach`/`reward_da_ngu_hanh`; `:690` header vẫn 6 cột cho data 4 cột; `:687` ghi "Linh căn tuần → Đá Ngũ Hành" | Consumer thật, không nằm trong File Structure của plan → bảng tổng quan lệch cột + thưởng sai | `$rows3` còn 4 cột (tên/mô tả/hook/mục tiêu), header khớp 4 cột, caption in `500 LT + 1-2 Bình Anh`, nhãn → "LT + Bình Anh Tinh Thạch". **Không commit** (untracked + `:68` chứa mật khẩu DB) |


