# Tài liệu: Mạch Huyết — mạng mạch huyết 8 mạch (game/huyetmach.php)

> **Nguồn**: kế hoạch `docs/superpowers/plans/2026-10-09-huyetmach-mang-mach.md` (12 task, đã chạy xong Task 1-12),
> thiết kế `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md`.
> **Khung 12 mục này copy từ `docs/UI_THECHAT.md`** để ai quen màn Nhục Thân đọc được ngay.
> Ngày đo số liệu: **2026-10-10**, DB local `game`, server dev `php -S 127.0.0.1:8123`.
> Mọi mục **CHƯA test** đều in hoa CHƯA — đừng coi nó là đã xong.

---

## 0. Trả lời nhanh: tính năng này để làm gì, có tác dụng thật không

**8 mạch, mỗi mạch mang ĐÚNG MỘT chỉ số**, luyện bằng Huyết Thạch (112) + Linh Thạch, luyện thành công 100% (không có may rủi):

| # | Mạch | Chỉ số | Mỗi cấp | Trần stage 4 (mạch lv 48) | Ngã vào đâu |
|---|---|---|---|---|---|
| 1 | Đốc Mạch | Công Kích | +3 | **187** (144 × 1,30 mốc) | `game1.ugj` (điểm) |
| 2 | Nhâm Mạch | Khí Huyết | +24 | **1.498** (1152 × 1,30) | `game1.umaxhp` (điểm) |
| 3 | Xung Mạch | Bạo Kích | +0,2% | **12,48%** (9,6 × 1,30) | `game1.ubj` |
| 4 | Đới Mạch | Hút Máu | +0,4% | **24,96%** (19,2 × 1,30) | `game1.uxx` |
| 5 | Dương Duy Mạch | Giảm Vật Lý | +0,25% | **12%** (không ăn mốc) | % trên sát thương tới, lúc chạm đòn |
| 6 | Âm Duy Mạch | Xuyên Giảm | +0,25% | **12%** | % trừ phòng thủ quái/BOSS |
| 7 | Âm Kiều Mạch | Né Tránh | +0,25% | **12%** | xác suất `$phurt = 0` |
| 8 | Dương Kiều Mạch | Vận May | +0,25% | **12%** | nhân ngưỡng rơi đồ |

Bốn mốc tổng cấp (`milestones`) — Khai Mạch 32 cấp **+5%**, Nội Mạch 80 **+12%**, Huyết Hải 128 **+20%**, Đạt Tôn 176 **+30%** — **chỉ nhân cho 4 mạch dương** (`huyetmach.php:1073-1078`: nhánh `stat_cap` không hề dùng `$mult`).

### Có tác dụng thật — đã đo trên đường dẫn thật, không phải đoán

Bằng chứng: `tests/test_huyetmach_combat.php --live` chạy qua HTTP thật vào `game/pve.php` và `game/boss.php` ngày 2026-10-10, commit `e686135`, **50 PASS / 0 FAIL**:

- **Né Tránh** kích hoạt **6/60 đòn PVE (10,0%)** và **4/20 đòn BOSS (20,0%)** — lý thuyết 12%, mẫu nhỏ nên lệch là bình thường.
- **Giảm Vật Lý + Xuyên Giảm** xuất hiện trên **100/100 đòn** ra đòn (dòng thông báo + số máu thay đổi thật).
- 4 chỉ số dương cộng thẳng vào `ugj/umaxhp/ubj/uxx` trong `getplayer()` (`class/player.php:839-842`) ⇒ **mọi** route tính sát thương của game đều dùng, không riêng màn nào.
- **Vận May** nhân ngưỡng rơi đồ qua `huyetmach_combat_luck_gate()` ở cả 3 cổng rơi (`dljv` / `djjv` / `ypjv`).

### Phần phải nói thẳng: gần như vô dụng hoặc chưa có bằng chứng

- **Âm Duy Mạch (Xuyên Giảm) là mạch trang trí.** `game/pve.php:245-246` và `game/boss.php:242-243` đã có **sàn 15%** cho sát thương tối thiểu, nên xuyên phòng thủ chỉ nâng sát thương trung bình **+2,52% (PVE)** và **+0,1% (BOSS)** trên mẫu local. Đo ở pen 15%: PVE +7,26%, BOSS 0%, PVP **+29,59%** ⇒ **cố ý tắt pen trong PvP** (`game/pvp.php`, mô tả mạch 6 trong config ghi rõ).
- **Trần chạm được là 12%, không phải 15%** như `stat_cap` ghi — `stat_cap 15` là clamp trên giấy, không bao giờ binding (xem Sổ nợ dòng 8).
- **Nâng phẩm chất 9 bậc (Huyết Mạch Đan 113) chủ yếu là danh xưng.** Nó đổi tên/màu/huy hiệu + thang hiệu ứng `.hmq-*`, **có tỉ lệ FAIL và mất vật phẩm**, và `quality` chỉ nhân cho đường chỉ số **cũ** (`base_stats × quality × realmScale × awakenMult`, `huyetmach.php:226-255`) — đường này cần `huyetmach_id > 0`. Theo quyết định "khởi đầu trắng" (2026-10-06) thì nhân vật mới có `huyetmach_id = 0` ⇒ **nâng phẩm không cộng thêm điểm nào** cho tới khi chủ server gán huyết mạch.
- **BOSS công hội chưa nối mạch** (`game/club_boss.php:214-215`) — đây là bề mặt chiến đấu thứ tư, 4 chỉ số âm vô hình ở đó.
- **Route PvP thật chưa quay live**, **tỉ lệ rơi thực tế của Vận May chưa đo**, **chưa bấm thử trên điện thoại thật**.

Xem Sổ nợ cuối file (27 dòng) để biết cái nào cần chủ dự án chốt.

---

## 1. Bối cảnh

**Ba trang cũ gom về một màn.** Trước rework, Huyết Mạch có ba URL rời: `cmd=huyetmach` (danh sách + bị động), `cmd=huyetmach_awakening` (thức tỉnh — **đã xoá** ở Task 7), `cmd=huyetmach_transform` (biến hoá — giữ, nhưng viết lại theo khung mới). Người chơi phải nhảy qua lại giữa ba trang để trả lời một câu hỏi duy nhất: "hôm nay tôi nâng được gì". Nay một màn `game/huyetmach.php` có đủ: mạng mạch 8 ngọc trên ảnh kinh mạch, dải 5 bậc thức tỉnh, panel chỉ số + điều kiện, và các trang phụ (Boss mạch huyết / Nâng phẩm / Biến thân) nối bằng `.hm-tabbar`.

**Thêm lớp chỉ số thứ tư.** Thể Chất (Nhục Thân) đã là lớp chỉ số thứ ba; Mạch Huyết là lớp thứ tư và là lớp đầu tiên **can thiệp trực tiếp vào công thức lúc đánh nhau** (né, trừ % sát thương, xuyên phòng thủ, nhân ngưỡng rơi đồ) thay vì chỉ cộng điểm vào `game1`.

**Vì sao 8 mạch mà không phải 4 + 4 chỉ số mới:** 4 mạch dương (Đốc/Nhâm/Xung/Đới) rót vào 4 cột đã có sẵn (`ugj/umaxhp/ubj/uxx`) nên không phải đụng schema tính sát thương; 4 mạch âm (Duy/Duy/Kiều/Kiều) là 4 % mới hoàn toàn. Thiết kế §5 của spec ban đầu định ánh xạ Né→`tfsb`, Giảm→`ufy`, May→`uwx`, nhưng **cả ba đều không dùng được** — `tfsb` không xuất hiện trong bất kỳ công thức sát thương nào (chỉ `game/tianfu.php:19,100,152` + `zhuangtai.php:419` + `paihang.php:19`), `uwx` không nơi nào đọc, `huyetmach_passive_drop` chỉ để hiển thị. Chi tiết ở `11.0` của Task 11 trong plan; **đây là lý do 4 chỉ số âm là % thuần tính lúc chạm đòn, không vật hoá thành field**.

**Boss riêng cho từng người chơi.** `huyetmach_boss_config.json` sinh 33 boss (11 dòng huyết mạch × stage 2/3/4) theo `per_player = true`, `max_fights_per_day = 5`, `expire_minutes = 2880`. Boss công hội và boss Linh Căn không liên quan.

**Vật phẩm mới:** `112 Huyết Thạch` (nguyên liệu luyện mạch + nâng phẩm) và `113 Huyết Mạch Đan` (điều kiện bậc thức tỉnh 4), khai báo trong `class/config/daoju_config.json`.

---

## 2. File đã sửa / đã tạo

| File | Trạng thái | Dòng | Việc |
|---|---|---|---|
| `class/config/huyetmach_vessel_config.json` | **MỚI** | 33 | 8 mạch, 5 bậc, `refine`, 4 mốc, `stat_cap`, `stat_labels` (Task 1) |
| `class/config/huyetmach_boss_config.json` | **MỚI** | 372 | 33 boss per-player + `stats` + `drops.by_stage` (Task 8) |
| `class/config/huyetmach_upgrade_config.json` | **MỚI** | 15 | 9 bậc nâng phẩm (Task 9) |
| `class/function/huyetmach.php` | sửa | 641 → **1148** | **append 508 dòng**, không sửa trộn: Task 1/3/4/5/6/9/11 mỗi khối guard `function_exists` |
| `class/function/huyetmach_boss.php` | **MỚI** | 247 | sinh boss, đánh, rơi đồ, giới hạn ngày (Task 8) + 2 wrapper né/giảm (Task 11) |
| `class/player.php` | sửa | +12 | `$huyetmach_vessel` (`:148`), reset (`:814`), gắn từ calc (`:827`), cộng 4 chỉ số dương (`:839-842`) |
| `class/config/daoju_config.json` | sửa | +16 | vật phẩm 112 / 113 (Task 4) |
| `game/huyetmach.php` | viết lại | 119 → **327** | một màn: rail 5 bậc, stage ảnh, 8 ngọc, forge, panel (Task 7) |
| `game/huyetmach_boss.php` | **MỚI** | 93 | màn boss mạch huyết per-player (Task 8) |
| `game/huyetmach_upgrade.php` | **MỚI** | 106 | màn nâng phẩm + thang `.hmq-q1..q10` (Task 9) |
| `game/huyetmach_transform.php` | viết lại | 80 | theo khung mới, nối `.hm-tabbar` (Task 7/10) |
| `game/huyetmach_awakening.php` | **XOÁ** | −95 | gộp vào `game/huyetmach.php` (Task 7) |
| `game/zhuangtai.php` | sửa | +27 | 8 dòng `Mach …` + Tổng cấp mạch, `:583-597` (Task 10) |
| `game/boss.php` | sửa | +27/−5 | pen `:237-241`, reduce/dodge `:284-296`, luck `:401,428,446` (Task 11) |
| `game/pve.php` | sửa | +26/−4 | `$hmCs :231`, reduce/dodge `:303-315`, luck `:412,432,461` (Task 11) |
| `game/pvp.php` | sửa | +14/−2 | chỉ dodge + reduce `:111-13x`; **pen cố ý không nối** (Task 11) |
| `game/thechat.php` | sửa | +4/−1 | dùng lại hàm vật phẩm chung (Task 4) |
| `css/mystyle.css` | sửa | +~400 | `7273-7442` khung `.hm-*`; `7504-7573` fx ladder; `7580-7662` `.hmq-*` (Task 7/9/10) |
| `game.php` | sửa | 2 | route `huyetmach_boss` / `huyetmach_upgrade`, version CSS `?v=2026101005` (`:502`) |
| `sql/sql_huyetmach_vessel.sql` | **MỚI** | 44 | **chỉ 3 bảng mới** (`CREATE TABLE IF NOT EXISTS` × 3), không có câu ALTER nào (Task 2) |
| `sql/sql_thechat_huyetmach.sql` | đã có từ trước | `:16-19` | **nguồn thật của 4 cột `game1`** (`huyetmach_id`, `huyetmach_quality`, `huyetmach_awaken_stage`, `huyetmach_transform_cd`) — không nằm trong file vessel |
| `tests/deploy_preflight.php` | sửa | +3 | liệt kê 3 bảng mới (`:35-37`) |
| `images/huyetmach/*` | **MỚI** | 22 file | `kinh-mach.jpg` + 7 bộ ngọc ngày/sáng/khoá + 3 ảnh chung (Task 7) |
| `tests/test_huyetmach_vessel.php` | **MỚI** | 254 | Task 1/3/4/5/6 |
| `tests/test_huyetmach_schema.php` | **MỚI** | 47 | Task 2 |
| `tests/test_huyetmach_boss.php` | **MỚI** | 101 | Task 8 |
| `tests/test_huyetmach_upgrade.php` | **MỚI** | 133 | Task 9 |
| `tests/test_huyetmach_combat.php` | **MỚI** | 285 | Task 11 (khối A-D + `--live` + `--measure`) |

| `tongquan.php` | sửa | +43 (863 → 906 theo `wc -l`, nền `47fc7fa`) | mục **`mach-huyet`** (nhóm `nuoi-duong`, icon `❤` U+2764) chèn ngay sau mục `huyet-mach` (`:401`): 3 bảng `tbl-mh` (8 mạch) / `tbl-mht` (5 bậc) / `tbl-mhm` (4 mốc) + nhánh `else` khi chưa có config (Task 12). **Cột "Cần cảnh giới" đi qua `tq_realm_req()` nên hiện tên cảnh giới thật của game** (Luyện Thể 100 / Thối Thể 150 / Tụ Thân 200 / Ngự Khí 280), khác tên Trung Quốc trong spec (Trúc Cơ / Kim Đan / Nguyên Anh / Đại Thừa) — cùng một cấp, không phải lệch số |
| `docs/UI_HUYETMACH.md` | **MỚI** | 518 | chính file này (Task 12) |
| `docs/DEPLOY_PRODUCTION_HUYETMACH.md` | **MỚI** | 242 | runbook 7 mục, production **chưa chạy** (Task 12) |

**Commit thứ tự** (`git log --oneline`): `c14c785` T1 → `f3fd09c` T2 → `42adfba` T3 → `e194c91` T4 → `afff90a` T5 → `eea835f` T6 → `918d269` T7 → `da79abf` T8 → `3c4c509` T9 → `4201b08` T10 → `e686135` T11 → (T12 lần này). Nền: `47fc7fa`.

**ĐÃ commit riêng `e9fea2b`** (sau commit tài liệu `0ed1e40`, vì trang Nhục Thân nằm ngoài plan Mạch Huyết): `css/mystyle.css` thêm block `.main .nb-btn` (`:7084-7087`) để 2 nút Luyện Hoá / Đột Phá của màn Nhục Thân lấy lại gradient cam-vàng / tím-hồng giống hệt Luyện Mạch / Thúc Tinh (`:7558-7561`), và `game.php:502` bump `?v=2026101005`. **Đã xem bằng mắt** (chup `game.php?cmd=thechat` vs `cmd=huyetmach`, viewport 500px, `E:/window/Temp/_nb_shot.php`): "Luyện Hoá" = cam→vàng sáng, "Đột Phá" = tím→hồng, "Thúc Tinh" = tím→hồng cùng cặp stop `#7c3aed→#db2777` ⇒ khớp 1:1; "Luyện Mạch" trông xẫm hơn vì tài khoản test hết Huyết Thạch (0/1) nên rơi vào `[disabled]` opacity .45 — đúng chủ đích, không phải lệch màu.

---

## 3. Cấu trúc trang (một màn, 2 card)

```
.hm-wrap  (hm-fx0..fx4 + is-dao, biến --hm-tier / --hm-color)
├── .hm-msg                       thông báo Luyện/Thức Tỉnh
├── .hm-card.hm-card-body         CARD 1 — đầu trang + mạng mạch
│   ├── .hm-head > .hm-hrow > .hm-title + .hm-badge (bậc hiện tại · trần)
│   ├── .hm-rail                  dải 5 bậc: .hm-tier × 5, nối bằng .hm-link.is-lit
│   ├── .hm-viewline + .hm-note   đang xem bậc nào + ghi chú chưa khai mở
│   └── .hm-forge-wrap
│       ├── .hm-stage             ảnh kinh mach 663/377 + 8 .hm-gem đặt theo % + .hm-aura/.hm-sweep
│       └── .hm-forge             tên mạch, cấp/cap, +per_lv/cấp, 2 .hm-cost-item, nút .hm-act-refine
└── .hm-card.hm-cols              CARD 2 — 2 cột
    ├── .hm-col trái  .hm-h3 "Thuộc Tính Kích Hoạt" + 8 .hm-attr + .hm-ms (thanh mốc)
    └── .hm-col phải  .hm-h3 "Thức Tỉnh Huyết Mạch" + .hm-gate + .hm-reqs (.hm-req ok/no) + nút .hm-act-break
```

### Bản đồ selector → file:dòng

| Selector | CSS | HTML | Ghi chú |
|---|---|---|---|
| `.hm-wrap` | `mystyle.css:7273` | `huyetmach.php:164` | `margin:0` để content chiếm hết khung như `.xl-page`; **không có `max-width`** (xem Sổ nợ dòng 27) |
| `.hm-card`, `.hm-card-body` | `7274-7275` | `170`, `261` | |
| `.hm-title`, `.hm-hrow`, `.hm-badge`, `.hm-badge-ico`, `.hm-phys` | `7276-7280` | `172-174` | badge ăn `--hm-glow` + `--hm-tier` |
| `.hm-head` | `7283` | `171` | |
| `.hm-rail`, `.hm-link`, `.hm-link.is-lit` | `7284-7290` | `177-199` | rail 5 bậc, `overflow-x:auto` |
| `.hm-tier` (+ `.is-done/.is-cur/.is-lock/.is-view`) | `7291-7304` | `181-197` | `--tcolor` từ `tiers[stage].color` |
| `.hm-viewline`, `.hm-note` | `7305-7307`, `7577` | `201-206` | |
| `.hm-forge-wrap`, `.hm-stage`, `.hm-figure` | `7310-7314` | `210-213` | stage `height:268px` mobile, `aspect-ratio:663/377` desktop (`7433`) |
| `.hm-gem` + 8 slot | `7319-7342` | `222-233` | toạ độ `%` theo slot trong config; desktop override `7434-7439` |
| `.hm-gem-disc`, `.hm-gem-img`, `.hm-gem-tag` | `7321-7324` | `229-232` | `--ring` xanh `#6ee7b7` khi đạt trần |
| `.hm-gem.is-sel`, `.is-max` | `7343-7346` | `222-227` | `is-sel` = mạch đang chọn (thở `hmBreath`) |
| `.hm-forge`, `.hm-forge-top/name/lv/gain/row` | `7350-7356` | `237-249` | |
| `.hm-cost-item`, `.hm-cost-ok`, `.hm-cost-no` | `7357-7360` | `247-248` | đỏ khi thiếu vật phẩm |
| `.hm-act`, `.hm-act-refine`, `.hm-act-break` | `7362-7369` | `254`, `304` | shine `:after` `7363`, ăn `--hm-act-dur` |
| `.main .hm-act*` | **`7555-7561`** | — | **bắt buộc**, thắng `.main button` (xem §6) |
| `.main .hm-act-ghost`, `a.hm-act-ghost:*` | `7563-7564` | trang phụ | nút "Quay lại" |
| `.main .hm-act-transform` | `7573` | `huyetmach.php:160` | đỏ-cam |
| `.hm-capnote` | `7370` | `244` | |
| `.hm-cols`, `.hm-col`, `.hm-h3`, `.hm-attrs`, `.hm-attr` | `7373-7378` | `261-267` | 8 dòng chỉ số |
| `.hm-ms`, `.hm-ms-top`, `.hm-ms-bar`, `.hm-ms-fill`, `.hm-ms-note` | `7379-7383` | `269-286` | thanh tiến trình mốc tổng cấp |
| `.hm-gate`, `.hm-gate-arr`, `.hm-gate-cap` | `7384-7386` | `291-295` | "Sơ Giác → Thức Tỉnh, trần 24" |
| `.hm-reqs`, `.hm-req.ok/.no` | `7387-7390` | `297-300` | checklist `[Đủ] / [Thiếu]` |
| `.hm-node-desc` | `7392` | `312` | |
| `.hm-msg.ok`, `.hm-msg.err` | `7395-7397` | `166` | |
| `.hm-tabbar` (+ `a.on`, `a i`, `a b`) | `7402-7415` | `huyetmach.php` cuối, `huyetmach_boss.php:89-92` | 6 ô: Mạch Huyết / Nhân Vật / Boss / Nâng Phẩm / Biến Thân / ... |
| `.hm-transform`, `-ico`, `-name`, `-state` | `~7586` block transform | `huyetmach.php:155-159` | chỉ in khi stage ≥ 3 |
| `.hm-aura`, `.hm-sweep` | `7528-7529` | `213-214` | fx theo `--hm-tier` |
| `.hm-fx0 … .hm-fx4` | `7513-7517` | `114` | xem §12 |
| `.hmq-*` (nâng phẩm) | `7580-7662` | `huyetmach_upgrade.php:40,78-...` | xem §12 |

**Tên mạch / nhãn chỉ số không hard-code trong PHP** — mọi nhãn UI lấy qua `huyetmach_vessel_stat_label()` (`huyetmach.php:875`) đọc `stat_labels` từ JSON. Task 10 đã sửa typo "Né Tranh" → "Né Tránh" **tận gốc config** để battle log của Task 11 không nhiễm lại.

---

## 4. Logic server-side (`class/function/huyetmach.php`)

### 4.1 Chữ ký hàm đã produce

| Hàm | Dòng | Trả về / tác dụng |
|---|---|---|
| `huyetmach_vessel_config_all()` | 781 | decode JSON, guard `isset` |
| `huyetmach_vessel_defs()` / `_def($id)` | 793 / 807 | 8 mạch / 1 mạch |
| `huyetmach_vessel_tiers()` / `_tier($stage)` | 800 / 815 | 5 bậc / 1 bậc |
| `huyetmach_vessel_cap($stage)` | 823 | **trần cấp mỗi mạch** = `tiers[stage].lv_cap` (8/16/24/36/48) |
| `huyetmach_vessel_stage_max()` | 830 | 4 |
| `huyetmach_vessel_refine_cost($lv)` | 840 | `1 + floor(lv/8)` Huyết Thạch, `200 + 25×lv` Linh Thạch |
| `huyetmach_vessel_milestone($total)` | 855 | mốc hiện tại + mốc kế |
| `huyetmach_vessel_stat_label($stat)` | 875 | nhãn tiếng Việt từ config |
| `huyetmach_vessel_next_stage($stage)` | 884 | bậc kế hoặc `false` |
| `huyetmach_vessel_boss_name($bloodlineId, $toStage)` | 892 | tra `huyetmach_boss_config.bosses["<key>_<stage>"]` |
| `huyetmach_vessel_break_rows($sid,$dblj)` | 904 | checklist 5 điều kiện (tổng cấp / cảnh giới / vật liệu / Linh Thạch / boss) |
| `huyetmach_vessel_daoju_have($sid,$dblj,$djid)` | 953 | đếm `playerdaoju.djsum` |
| `huyetmach_vessel_refine($sid,$dblj,$vesselId)` | 963 | +1 cấp, transaction, **thành công 100%** |
| `huyetmach_vessel_levels($sid,$dblj)` | 1027 | 8 slot, thiếu = 0 |
| `huyetmach_vessel_total($levels)` | 1043 | tổng cấp |
| **`huyetmach_vessel_bonuses($sid,$dblj)`** | **1051** | **17 keys**, xem 4.2 |
| `huyetmach_combat_stats($vessel)` | 1095 | 4 % âm, kẹp `[0, stat_cap]`, **không query DB** |
| `huyetmach_combat_roll_dodge($pct)` | 1112 | `bool` |
| `huyetmach_combat_phys_reduce($pct,$dmg)` | 1121 | `int` sát thương còn lại |
| `huyetmach_combat_pierce_def($pct,$def)` | 1132 | `int` phòng ngự **còn lại** |
| `huyetmach_combat_luck_gate($pct,$gate)` | 1142 | `int` ngưỡng rơi đồ đã nhân |
| `huyetmach_upgrade($sid,$dblj)` | 639 | nâng phẩm, **có tỉ lệ FAIL, mất vật phẩm** |

### 4.2 `huyetmach_vessel_bonuses()` — 17 keys

`atk hp crit lifesteal dodge pen reduce luck` (8 chỉ số) + `levels total_lv cap stage milestone_name milestone_percent next_milestone_name next_milestone_percent next_milestone_lv`.

Đoạn nhân mốc (`:1069-1079`) là **chỗ duy nhất có nhánh**, và nó quyết định toàn bộ cân bằng:

```php
if (isset($caps[$stat])) {                       // 4 chi so AM: chi chan stat_cap, KHONG nhan $mult
    $out[$stat] = min(floatval($caps[$stat]), round($out[$stat] + $val, 2));
} else {                                         // 4 chi so DUONG: nhan moc
    $out[$stat] = $out[$stat] + round($val * $mult);
}
```

`atk`/`hp` ép `int` sau cùng (`:1082-1083`) vì ngã vào cột INT; `crit`/`lifesteal`/4 % âm giữ float.

### 4.3 Đường cộng vào nhân vật (`class/player.php`)

`:839-842` — `ugj += stats.atk`, `umaxhp += stats.hp`, `ubj += stats.crit`, `uxx += stats.lifesteal`; `:827` gán `$player->huyetmach_vessel` để Task 11 đọc lúc chạm đòn. **4 chỉ số âm KHÔNG có cột riêng** — tính tại chỗ trong `game/pve.php` / `game/boss.php` / `game/pvp.php`.

### 4.4 Trần thật ≠ con số trong config

`stat_cap` khai 15 cho cả 4 chỉ số âm, nhưng mỗi chỉ số chỉ do **một** mạch mang: `0,25 × 48 = 12`. Vì vậy **trần chạm được hiện tại là 12%**, `stat_cap 15` không bao giờ binding. Muốn chạm 15% phải hoặc nâng `per_lv` lên `0.3125`, hoặc thêm bậc thức tỉnh 5 (`lv_cap` 60), hoặc cho hai mạch cùng chỉ số. Chưa tự đổi — Sổ nợ dòng 8.

---

## 5. Ảnh đã sinh

**Asset trong repo — `images/huyetmach/` (22 file, đã commit ở `918d269`)**

| File | Dùng cho |
|---|---|
| `kinh-mach.jpg` (61 KB) | nền `.hm-stage`, tỉ lệ 663/377 |
| `ngoc-atk-{day,sang,khoa}.png` | Đốc Mạch (Công Kích) |
| `ngoc-hp-{day,sang,khoa}.png` | Nhâm Mạch (Khí Huyết) |
| `ngoc-crit-{day,sang,khoa}.png` | Xung Mạch (Bạo Kích) |
| `ngoc-lifesteal-{day,sang,khoa}.png` | Đới Mạch (Hút Máu) |
| `ngoc-def-{day,sang,khoa}.png` | Dương Duy (Giảm Vật Lý) |
| `ngoc-exp-{day,sang,khoa}.png` | Dương Kiều (Vận May) |
| `ngoc-{day,sang,khoa}.png` | 3 trạng thái chung cho 2 mạch chưa có ảnh riêng: Âm Duy (Xuyên Giảm) và Âm Kiều (Né Tránh) |

Quy ước tên (đo từ `game/huyetmach.php:220-221`): `-khoa` = mạch **chưa có cấp** (level 0), `-sang` = **đã có cấp nhưng chưa chạm trần**, `-day` = **đã chạm trần mạch** (`$hmGlv >= $hmCap`). Ảnh chọn theo **nhóm chỉ số** chứ không theo `slot`: 4 mach dương có ảnh riêng (`atk/hp/crit/lifesteal`), 4 mach âm dùng `ngoc-{khoa,sang,day}.png` chung + màu vòng `--ring` (Sổ nợ dòng 22). `?v=<?= $hmImgVer ?>` gắn ở `:212` (ảnh nền) và `:230` (ngọc), biến khai báo tại `:11` = `2026100901`. **Đổi ảnh phải bump `$hmImgVer`.**

**Ảnh chứng cứ — `docs/img/huyetmach/` (25 file, KHÔNG commit; repo đang `git ls-files docs/img` = 0)**

`v1_desktop_full.png` `v1_desktop_stage.png` `v1_mobile_full.png` `v1_mobile_stage.png` `v1_mobile_cols.png` (màn chính), `v2_boss_desktop.png` `v2_boss_mobile.png` `v2_boss_attack.png` (màn boss), `upgrade-q1-{desktop,mobile}.png` `upgrade-q5-mobile.png` `upgrade-q9-mobile.png` `upgrade-q10-mobile.png` `upgrade-post.png` (nâng phẩm), `fx-stage0-{desktop,mobile}.png` `fx-stage{1,2,3,4}-mobile.png` `fx-stage4-desktop.png` `fx-stage4-dao.png` `fx-ladder.png` (thang hiệu ứng), `nhanvat-8mach.png` (trang Nhân Vật), `tongquan-mach-huyet.png` (mục mới trên trang tổng quan, chụp 2026-10-10).

**Bản đồ 4 ảnh bắt buộc của plan Task 12 Step 6 → tên file thật trong repo:**

| Plan yêu cầu | File thật | Đã nhìn? |
|---|---|---|
| `hm-desktop-1440.png` | `v1_desktop_full.png` | rồi (Task 7) |
| `hm-mobile-390.png` | `v1_mobile_full.png` + `v1_mobile_cols.png` | rồi (Task 7) |
| `hm-fx-ladder.png` | `fx-ladder.png` (5 bậc đặt cạnh nhau) | rồi (Task 10) |
| `tongquan-mach-huyet.png` | `tongquan-mach-huyet.png` | **rồi, 2026-10-10** — xác nhận bằng mắt: icon `❤` (U+2764) **render đúng quả tim đỏ**, không ra hộp rỗng; 3 bảng hiện đủ 8 + 5 + 4 dòng; ô lọc của từng bảng ghi đúng "(8 dòng)"/"(5 dòng)"/"(4 dòng)"; cột "Chỉ số" hiện "(trần 15%)" cho đúng 4 mach âm |

**CHƯA có ảnh nào chụp trên điện thoại thật** — tất cả là Chrome headless desktop + iframe 390px. Xem §10.

---

## 6. Ràng buộc CSS phải giữ (MỒI — dễ tái phạm)

1. **`.main button` (0-1-1) thắng `.hm-act` (0-1-0).** `css/gamecss.css:122` cấp nền TRẮNG hệ thống cho mọi nút trong `.main`. Block `.main .hm-act*` ở `mystyle.css:7555-7573` **không được xoá**, nếu xoá thì nút Luyện Mạch / Thức Tỉnh trở thành nút trắng. Cùng landmine này vừa vá cho Nhục Thân (`:7081-7087`) và đã vá cho Nâng Phẩm Nhục Thân ngay trong `<style>` của `game/thechat_upgrade.php:91-93`.
2. **`a:link` có đặc tính (0-1-1)** nên rule một lớp cho `a.hm-tabbar` bị đè. `mystyle.css:7403` phải liệt kê đủ `a, a:link, a:visited, a:hover, a:active, a:focus` và `a.on:link, a.on:visited` (`:7406`) — thiếu một pseudo là tab mất màu.
3. **`--hm-dur: 0s` KHÔNG tắt animation trong Chrome.** Bậc 0 bắt buộc dùng `animation:none` (`:7546-7547`), chỉ hạ duration là vô hiệu.
4. **Aura dùng `z-index:-1` ⇒ thẻ chứa nó BẮT BUỘC `isolation:isolate`.** `.hmq-card` có sẵn (`:7598`); thẻ nào ghép `hmq-card` mà thiếu isolation thì aura tụt ra sau nền và vô hình.
5. **`color: transparent` làm emoji biến mất.** Chữ cầu vồng ở `.hmq-q10` chỉ áp lên text, emoji phải nằm trong `<i class="hmq-ico">` (`huyetmach_upgrade.php:79`).
6. **Emoji: ưu tiên cung astral.** BMP (`❤ ✨ ✔  💰 👤`) dễ render lệch ô flex; `.hm-badge-ico` (`:7279`), `.hm-tabbar a i` (`:7404`), `.hmq-ico` (`:7597`) đều khai `font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji"`. **Không tin lý thuyết — phải nhìn ảnh chụp.** Đã chốt bằng ảnh ngày 2026-10-10: `❤` (U+2764) trong tiêu đề mục `mach-huyet` của `tongquan.php` render thành quả tim đỏ bình thường trên Chrome desktop (`docs/img/huyetmach/tongquan-mach-huyet.png`, xem bảng §5) ⇒ BMP **chấp nhận được ở tiêu đề/khối rộng**; chưa có bằng chứng cho ô flex hẹp trên điện thoại thật (§10.1).
7. **`background:` shorthand reset `background-clip`.** Khối gradient chữ (rainbow Đạo Phẩm) dùng `background-clip:text` nên không được viết shorthand `background:` phía sau.
8. **Tiền tố `.hm-*` dùng chung hai tính năng.** `game/hammo.php` đang giữ `.hm-page .hm-hero .hm-btn .hm-panel .hm-stats ...` (`mystyle.css:6414-6489`); Mạch Huyết giữ từ `:7273` trở đi. Hiện **không trùng selector cụ thể nào** (đo bằng `grep -o "hm-[a-z-]*"` hai phía), nhưng sửa block nào cũng phải grep cả `game/hammo.php` trước.
9. **`.hm-wrap` không có `max-width`** (`:7273` chỉ `margin:0`); khung chung của trang vẫn 660px (`mystyle.css:199`). Muốn mở rộng phải xin chủ dự án, không tự nới (bài học v13 của Nhục Thân).
10. **`@media(max-width:760px)` có `.hm-sweep,.hm-ms-fill:after,.hm-act-refine:after,.hm-act-break:after{display:none}`** và `animation:none` cho `.hm-tier.is-cur`, `.hm-gem.is-max:not(.is-sel)`, `.hm-wrap.is-dao` — đây là nhánh reduced-motion, đừng nhân bản nhầm thành responsive.

---

## 7. Bug đã tìm ra và cách sửa

| Bug | Triệu chứng | Sửa | Bằng chứng |
|---|---|---|---|
| Biến thân trừ vào `uxx` (Hút Máu) thay vì HP | Bấm Biến Thân mất chỉ số hút máu, HP không giảm | `huyetmach_activate_transform()` trừ `% HP` theo `umaxhp`, tính lại `uhp` | `test_huyetmach_vessel.php`: "trừ uhp theo umaxhp tinh lại" + "uxx khong doi" (PASS) |
| `huyetmach_awaken()` không kiểm điều kiện thật | Lên bậc thức tỉnh bằng cách bấm nút, không cần vật liệu / boss / cảnh giới | Gate qua `huyetmach_vessel_break_rows()` 5 điều kiện, thiếu là từ chối | `test_huyetmach_vessel.php` (PASS), commit `eea835f` |
| Chèn reduce/dodge **sai chỗ** làm mất thưởng | Người chơi né được đòn = `$phurt <= 0` → `$hurt = true` (`boss.php:275-276`, `pve.php:291-293`) → `$yxb = 0` (`boss.php:343`, `pve.php:347`) | Mọi chỉnh `$phurt` đặt **SAU** các dòng đó (`boss.php:284-296`, `pve.php:303-315`) | `--live`: né 6/60 đòn nhưng phần thưởng vẫn ghi nhận |
| Nút submit hiển thị trắng | Nút mặc định của `.main button` đè gradient | `.main .hm-act*` `:7555-7573`; phiên này thêm `.main .nb-btn*` `:7081-7087` | `E:/window/Temp/nb_btn_compare.png` — 4 nút cùng `color #fff, pad 11/18, radius 12px, h=44` |
| Màu bậc 0 quá tối | Chữ "Vĩnh Viễn Trầm Mệnh" gần không đọc được trên nền | `tiers["0"].color` `#666666` → `#6b7280` (theo vessel config) | `docs/img/huyetmach/fx-stage0-desktop.png` |
| Typo "Né Tranh" | Nhãn UI sai chính tả, lan sang battle log | Sửa tại gốc: `stat_labels.dodge` + desc mạch 7 trong config, `game/huyetmach_boss.php:46`, và **7 dòng trong plan** | `grep -c "Né Tránh"` > 0, "Né Tranh" = 0 |
| Tên mạch `pen` do agent tự đặt | "Xuyên Giảm" chưa ai duyệt | **CHƯA chốt** — giữ nguyên, Sổ nợ dòng 20 | — |

---

## 8. Bug CŨ có sẵn — phát hiện nhưng **CHƯA sửa** (chủ dự án quyết)

1. **`game/linhcan_boss.php:37` — nút "TẤN CÔNG" của boss Linh Căn không bao giờ chạy.** Điều kiện `$cmd == 'attack_lc_boss'`, nhưng `$cmd` sau `parse_str` ở `game.php:114` luôn bằng `linhcan_boss`; biến đúng phải là `$attack_lc_boss` (nút truyền `attack_lc_boss=1` ở `:83`). Ngoài phạm vi Mạch Huyết.
2. **`linhcan_boss_drops()` chỉ in chữ, không cộng đồ.** `class/function/linhcan_boss.php:193-194` in "🪨 3 Đá <hành>" nhưng TODO ngay phía trên không được thực hiện. Mạch Huyết đã làm đúng (`huyetmach_boss_finish()` INSERT `playerdaoju`).
3. **Bảng `boss` thiếu cột `element`** — chặn sát thương hành của boss thường (nợ từ đợt Ngũ Hành, `docs/DEPLOY_PRODUCTION_NGUHANH.md:§7`).
4. **`game/club_boss.php` bỏ ngoài mọi lớp chỉ số.** Né tránh ở đây là **hằng số 15% cứng** cho cả hai phía (`:226` boss né, `:251` người né), không đọc `huyetmach_vessel` / `thechat` / trang bị.
5. **`material_id 108` hiển thị "Đạo Nguyên"** (`huyetmach.php:433-441`) nhưng không có mô tả nguồn ở `daoju_config.json` — người chơi mua/lấy ở đâu chưa định nghĩa. Nâng phẩm bậc 9 cần đúng 1 cái.
6. **`thechat_upgrade()` không dùng transaction, không kiểm `rowCount()`** — nếu vật liệu đã bị người khác dùng hết, code vẫn quay `mt_rand` và mất lượt mà không trừ gì. `huyetmach_upgrade()` (Task 9) đã vá cho Huyết Mạch; trang Nhục Thân giữ nguyên, không sửa lan.
7. **`tests/e2e_fixture.php` hard-code `http://localhost`** (port 80) trong khi server dev hiện chạy `127.0.0.1:8123` ⇒ fixture này không chạy được trên máy dev.

---

## 9. Quy trình kiểm chứng (lặp lại được)

**Helper ở `E:/window/Temp/` — ngoài repo, KHÔNG commit, là đồ vứt.** Chỉ cần lệnh để tái tạo kết quả; file helper có thể mất.

| Helper | Tác dụng |
|---|---|
| `_hm_seed.php seed` / `clear` | Seed tài khoản E2E `e2e2e2e2…` về `huyetmach_id=1, quality=1, stage=1` + xoá 8 dòng mạch / hoặc trả về trắng |
| `_hm_enc.php` | Sinh chuỗi `cmd=` đã verify round-trip (encode của game, **không** urlencode thường) |
| `_hmfx_dmg1.php` | **Đo sát thương thật mỗi đòn** (7848 PVE / 3747 BOSS) — dùng khi seed HP cho quái test |
| `_eco.php` | Tổng hợp kinh tế: 1.344 Huyết Thạch luyện mạch, 60 thức tỉnh, 77 nâng phẩm, Linh Thạch gấp 136,3 ⇒ ghi ra `_eco.txt` |
| `_hm_btn.html` `_hm_live.html` `_hmb_live.html` | Harness iframe 390px để chụp / bắn HTTP thật |
| `_nbbtn_probe.php` | Đo computed style 4 nút (Luyện Mạch / Thức Tinh / Luyện Hoá / Đột Phá) để so 1:1 |
| `_hm_clash.php` | So tập selector `.hm-*` giữa `game/hammo.php` và màn Mạch Huyết (mồi §6.8) |
| `_hm_status.php` | **Chỉ đọc**: đếm 5 bảng huyetmach + liệt kê cột `huyetmach_*` của `game1` + `VERSION()` + `@@sql_mode` — chính là số liệu mục 1 của runbook deploy |
| `_hm_scan.php` `_hm_scan2.php` `_hm_scan3.php` | Quét CJK / curly / U+FFFD trên 32 file; `scan2` so CJK với commit nền `47fc7fa` để biết CJK **mới sinh** hay của source Trung Quốc; `scan3` in mã hex từng ký tự + cho biết nằm trong code fence hay văn xuôi |
| `_hm_tq_block.txt` + `_hm_tq_insert.php` | Khối `mach-huyet` và script chèn vào `tongquan.php` theo **anchor ASCII `'tbl-hmt'`** (không dùng số dòng, không regex tiếng Việt), 3 guard: block không CJK / chưa có `'mach-huyet'` / anchor xuất hiện đúng 1 lần |
| `_tqshot_gen.php` | Sinh `_tqshot.html` vào web root: iframe `tongquan.php` 1240×1800, đặt `body.style.zoom=1`, `scrollIntoView()` vào `#sec-mach-huyet`, in số đo ra `<div id="m">` để đọc bằng `--dump-dom` |
| `_hm_step5.php` | `ob_start()` + `include "tongquan.php"` rồi đếm: `MUC mach-huyet: ON`, `tbl-mh* = 3` (mỗi id đúng 1 lần), `sec-mach-huyet = 1`, số lần `❤` — chính là số đo Step 5 |
| `_hm_scan4.php` | Bản chạy được của lệnh quét Step 4 (18 file, in `cjk=` / `curly=`); phải để trong file vì Git Bash ăn mất `$` và phá byte tiếng Việt khi chạy `php -r` inline |
| `_nb_shot.php` | Chup `cmd=thechat` và `cmd=huyetmach` trực tiếp (500×2600) rồi cắt bằng GD thành 3 dải 500×820 (`_nb_<cmd>_band<k>.png`) để **nhìn bằng mắt** 2 nút Nhục Thân so với 2 nút Mạch Huyết |

```bash
# 0) Server dev: Apache port 80 CHẾT -> phải chạy built-in PHP 5.6
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -S 127.0.0.1:8123 -t D:/XXJserver/phpstudy_pro/WWW
curl -s -o /dev/null -w "%{http_code}\n" "http://127.0.0.1:8123/game.php"   # phai ra 200

# 1) Lint PHP 5.6 (moi file sua, khong duoc bo)
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
for f in class/function/huyetmach.php class/function/huyetmach_boss.php class/player.php \
         game/huyetmach.php game/huyetmach_boss.php game/huyetmach_upgrade.php \
         game/huyetmach_transform.php game/zhuangtai.php game/boss.php game/pve.php game/pvp.php tongquan.php; do
  "$PHP" -l "$f"; done

# 2) Bo 5 test (E/F can mode; chi --live can server 8123 dang chay)
"$PHP" tests/test_huyetmach_schema.php
"$PHP" tests/test_huyetmach_vessel.php
"$PHP" tests/test_huyetmach_boss.php
"$PHP" tests/test_huyetmach_upgrade.php
"$PHP" tests/test_huyetmach_combat.php              # A-D
"$PHP" tests/test_huyetmach_combat.php --measure    # + khoi F (do nguong pen)
"$PHP" tests/test_huyetmach_combat.php --live        # + khoi E (60+20 HTTP that)
# may khac port thi:  HM_BASE=http://localhost "$PHP" tests/test_huyetmach_combat.php --live

# 3) Chup anh (Chrome CLI, KHONG dung browser-use MCP)
chrome --headless=new --disable-gpu --hide-scrollbars --no-first-run \
  --force-device-scale-factor=1 --user-data-dir=<dir theo getmypid> \
  --window-size=820,2500 --virtual-time-budget=25000 --screenshot=<abs.png> "http://127.0.0.1:8123/game.php?cmd=<chuoi-encode>"

# 4) Chup MUC TREN TRANG TONG QUAN (Task 12 Step 6) — ca trang 47.000px nên phải neo vào section
php E:/window/Temp/_tqshot_gen.php          # ghi D:/XXJserver/phpstudy_pro/WWW/_tqshot.html
chrome ... --window-size=1300,1900 --dump-dom    "http://127.0.0.1:8123/_tqshot.html" | grep -o '<div id="m"[^<]*'
chrome ... --window-size=1300,1900 --screenshot=D:/.../docs/img/huyetmach/tongquan-mach-huyet.png "http://127.0.0.1:8123/_tqshot.html"
rm -f _tqshot.html                            # KHONG de harness trong web root
# so ra lan 2026-10-10: TQOFF=20609 VIS=64..1806 H=48663 TABLES=43 MH/MHT/MHM=yes ROWS=17 ZOOM=1
#   -> section nam ở offsetTop 20609px, cao 1742px (64..1806) nen iframe 1800px la vua du.
```

**Bẫy đã trả giá** (áp dụng cho mọi harness đo/chụp):
- `--window-size` có **sàn ~500px** ⇒ muốn 390px thật phải nhét trang vào `iframe` 390px rồi đo trong iframe.
- Trang game có `zoom:.75` ⇒ **đặt `contentDocument.body.style.zoom=1` trước khi đo**, nếu không crop ra nền trống.
- Kết quả harness đọc từ `<pre id="out">`; regex phải là `/<pre id="out"[^>]*>/` — thiếu `[^>]*` là không khớp dù DOM đã render.
- `--user-data-dir` dùng lại làm Chrome trả **0 byte** ⇒ sinh thư mục mới theo `getmypid()`.
- Harness HTML phải copy vào **web root** (mở `file:///` thì iframe cross-origin, `contentDocument` = null) và **`@unlink` ngay sau khi chụp**.
- `--measure` chỉ chạy được khi DB local có `game1` thật; mẫu local mỏng (12 nhân vật, 8 dòng mạch của 1 sid test) nên số khác dự đoán — xem Sổ nợ dòng 24.

### 9.1 Số đã đo (2026-10-10, DB local)

| Lệnh | Kết quả |
|---|---|
| 5 test (schema/vessel/boss/upgrade/combat A-D) | **ALL PASS**, combat A-D = 28 PASS / 0 FAIL |
| `--live` (khối E) | **50 PASS / 0 FAIL** — dodge 6/60 PVE (10,0%), 4/20 BOSS (20,0%), pen+reduce hiện 100/100 đòn, E.5 restore đúng 8 dòng mạch (6,6,4,2,5,16,3,0) và stage 0 |
| `--measure` (khối F, pen 12%) | PVE mean **2,52%**, BOSS **0,1%**, PVP **9,32%**, `GUARD=OK` |
| `--measure` khi plan viết (pen 15%) | PVE 7,26%, BOSS 0%, PVP **29,59%** — vượt ngưỡng 8% ⇒ tắt pen ở PvP |
| Seed phải đổi | HP quái/boss 999.999 → **2.000.000.000** vì 1 đòn PVE = 7848 sát thương (60 đòn ≈ 470k) |
| Task 12 Step 4 — quét ký tự lạ (32 file) | **Không file nào có CJK mới sinh.** `class/player.php` cjk=79 và `game/boss.php` cjk=263 **là comment Trung Quốc của source gốc** — `git show 47fc7fa:<file>` cho đúng cùng số ⇒ không tăng. `curly` chỉ còn `…` (U+2026) trong văn xuôi 2 file `.md` (11 + 3), **0** nằm trong code fence |
| Task 12 Step 5 — `tongquan.php` | `php -l` sạch; `MUC mach-huyet: ON`; `tbl-mh*` = **3** bảng, mỗi bảng `data-for` đúng **1** lần (không giành nhau với `tbl-hm`/`tbl-hma`/`tbl-hmt` của mục cũ) |
| Task 12 Step 8 — chạy lại 5 test + lint 12 file | **ALL PASS** cả 5 (schema / vessel / boss / upgrade / combat A–D), lint 12 file không ra dòng lỗi nào |
| Task 12 Step 6 — ảnh mục tổng quan | `docs/img/huyetmach/tongquan-mach-huyet.png` (167 KB) — **đã nhìn**: `❤` render đúng, 8+5+4 dòng, 3 ô lọc đúng |

---

## 10. Nợ còn lại — **CHƯA test / CHƯA làm**

### 10.1 CHƯA test trong game thật (bốn mục, Task 11 Step 15 ghi nguyên văn)

1. **`game/pvp.php`**: mới chứng minh bằng đơn vị (khối A và D). Route PvP thật cần hai nhân vật cùng hội và `cmd=pvp`, harness **không** quay ⇒ **né tránh của người bị đánh trong PvP chưa có bằng chứng trên đường dẫn thật**.
2. **`luck`**: mới kiểm bằng công thức ngưỡng rơi đồ. **Chưa đo tỉ lệ rơi thực tế** trong trận (cần hàng ngàn lần giết để thống kê).
3. **`game/club_boss.php`** (BOSS công hội): có hai nhịp né riêng (`:226`, `:251`) nhưng **không nối mạch huyết** vào đây — bề mặt thứ tư bị bỏ ngoài.
4. **`$cwxl`** (sát thương thú cưng nhận lại, `game/boss.php:471`) bằng 0 khi nhân vật né được đòn — hành vi **có chủ đích**, chưa đối chiếu lại với thú cưng đang xuất chiến trong game thật.

### 10.2 CHƯA làm / chưa chốt

- **Production chưa migration gì cả** — runbook đã viết xong ở `docs/DEPLOY_PRODUCTION_HUYETMACH.md` (7 mục), nhưng **không có bước nào trong đó đã chạy**. Trước khi copy code phải trả xong nợ schema sid của 15 bảng trong `docs/DEPLOY_PRODUCTION_NGUHANH.md`.
- **Chưa bấm thử trên điện thoại thật.** Toàn bộ 25 ảnh là Chrome headless desktop hoặc iframe 390px.
- **Trang Nhân Vật in chỉ số % không kèm đơn vị `%`** (`game/zhuangtai.php:587-591`), trong khi màn mạch huyết đã có bảng hậu tố đúng (`game/huyetmach.php:96-97`) — Sổ nợ dòng 21.
- **Nhãn `pen` = "Xuyên Giảm" do agent tự đặt**, chưa chủ dự án duyệt (Phương án gợi ý: "Phá Giáp" / "Xuyên Thấu").
- **Ảnh riêng cho 4 mach âm chưa có** — đang dùng `ngoc-{day,sang,khoa}.png` chung + màu vòng — Sổ nợ dòng 22.
- **Âm Duy Mạch gần như vô dụng** (xem §0) — chưa quyết có làm lại mạch này không.
- **`class/config/huyetmach_transform_config.json` không được git theo dõi** (`git ls-files class/config` = 6/40 file) ⇒ không deploy được bằng `git clone`/`git archive`, phải copy tệp theo danh sách mục 4 của runbook.
- **CSS version giờ là `2026101005`** (`game.php:502`); lần sửa CSS tới phải bump tiếp, không dùng lại số cũ.
- **Hai commit cuối của Task 12 mới ở local**: `0ed1e40` (tài liệu + `tongquan.php`) và `e9fea2b` (2 nút Nhục Thân). Repo **không có remote** (`git remote -v` trả rỗng) ⇒ "đưa lên production" ở đây là làm theo runbook copy thủ công, không phải `git push`.
- **Sổ nợ cuối file đã ghi đủ 27 dòng** (Step 7 của Task 12) — không còn mục "chưa viết".

---

## 11. Bảng tra nhanh (hỏi → đáp → chỗ mở ra xem)

| Câu hỏi | Trả lời | Mở ra |
|---|---|---|
| Sao luyện mạch mãi không tăng được nữa? | Mạch bị **trần theo bậc thức tỉnh**: stage 0 = 8 cấp, 1 = 16, 2 = 24, 3 = 36, 4 = 48. Phải Thức Tỉnh. | `huyetmach.php:823` `huyetmach_vessel_cap()`; thông báo UI `.hm-capnote` `huyetmach.php:244` |
| Sao nhân vật mới toanh vẫn luyện được mạch khi chưa có huyết mạch? | **Có chủ đích.** `huyetmach_calc_bonuses()` trả `vessel` ngay cả khi `!$config`, nên 8 mạch đọc được lúc chưa khai mở. | `huyetmach.php:228`, `huyetmach.php:206` (`.hm-note`) |
| Sao né được đòn mà vẫn mất lượt/mất thưởng? | Không mất — chèn giảm đòn đặt **SAU** dòng đặt `$hurt=true` nên `$yxb` vẫn tính. Đây là lý do vị trí chèn là bắt buộc. | `game/boss.php:275-276` + `:284-296` + `:343`; `game/pve.php:291-293` + `:303-315` + `:347` |
| Sao né được mà thú cưng không được hút máu? | `$phurt` về 0 ⇒ `$pvbxx` (clamp `boss.php:279-285`) và `$cwxl = round($phurt*2.2)` (`:471`) cũng 0. **CHƯA đối chiếu trong game thật.** | `game/boss.php:471` |
| Sao Xuyên Giảm không tăng sát thương mấy? | Sàn 15% đã chặn trước: `$gphurt` không được thấp hơn `ugj*0.15`. Đo mean chỉ **+2,52% PVE**, **+0,1% BOSS**. | `game/pve.php:245-246`, `game/boss.php:242-243`, số ở §9.1 |
| Sao xuyên giảm không chạy khi PvP? | **Cố ý.** pen 15% làm sát thương PvP **+29,59%** (ngưỡng cho phép 8%). Mô tả mạch 6 trong config ghi rõ. | `game/pvp.php` (không gọi `huyetmach_combat_pierce_def`), `huyetmach_vessel_config.json` vessel 6 `desc` |
| Sao chỉ số âm chỉ lên 12% chứ không 15%? | Mỗi chỉ số do **một** mạch mang: `0,25 × 48 = 12`. `stat_cap 15` không bao giờ binding. | `huyetmach_vessel_config.json` `vessels` + `stat_cap` + `tiers["4"].lv_cap` |
| Sao mốc tổng cấp không cộng thêm cho Né Tránh? | **Có chủ đích.** Nhánh `stat_cap` không nhân `$mult` — mốc chỉ nhân 4 mạch dương. | `huyetmach.php:1073-1078` |
| Sao nâng phẩm rủi ro mà vẫn đáng? | Nâng phẩm **chỉ** đổi tên/màu/huy hiệu + nhân đường chỉ số **cũ** (`base_stats × quality × …`), đường này cần `huyetmach_id > 0`. Nhân vật khởi đầu trắng thì **không cộng điểm nào**. | `huyetmach.php:226-255`, `huyetmach.php:639` `huyetmach_upgrade()`, memory *khoi-diem-trang* |
| Sao nâng phẩm thất bại mà vẫn mất vật liệu? | Thiết kế lặp Nhục Thân: trừ vật liệu trước, quay `mt_rand` sau. Có log vào `player_huyetmach_upgrade_log`. | `huyetmach.php:639-732` |
| Nút Luyện Mạch / Thức Tỉnh bị trắng xoá? | Thiếu block `.main .hm-act*` (`.main button` thắng vì đặc tính 0-1-1 > 0-1-0). | `css/mystyle.css:7555-7573`, `css/gamecss.css:122` |
| Bậc 0 sao không thấy hiệu ứng gì? | Vẫn phải thấy: aura `opacity .04` + badge `box-shadow 0 0 4px` **tĩnh**; chỉ `animation:none` và `.hm-sweep{display:none}`. | `mystyle.css:7513`, `:7546-7547` |
| Sao `--hm-dur:0s` không tắt animation? | Chrome không coi duration 0 là tắt. Phải `animation:none`. | `mystyle.css:7546`, §6.3 |
| Boss mạch huyết của tôi sao biến mất? | `expire_minutes = 2880` (2 ngày) và **riêng từng người chơi** (`per_player = true`), tối đa `5` trận/ngày. | `huyetmach_boss_config.json` `boss_config` |
| Sao giết boss mãi không đủ điều kiện lên bậc 1? | Bậc 1 (Sơ Giác) `boss_require = 0` — không cần boss. Bậc 2 trở đi mới cần, và boss chỉ sinh ở **stage 2/3/4**. | config `tiers`, §10 Sổ nợ dòng 2 |
| Sao không thấy Huyết Mạch Đan rơi? | **Không có nguồn rơi.** `huyetmach_boss_finish()` chỉ cộng id 112; 113 chỉ khai báo trong `daoju_config.json`. | `huyetmach_boss.php:184` vùng cộng rơi, Sổ nợ dòng 3 |
| Trang Nhân Vật có hiện 8 mạch không? | Có, 8 dòng `Mach …` + `Tong cấp mach` trong 16 dòng `.profile-stat`, không còn nhãn tiếng Anh. | `game/zhuangtai.php:583-597`, ảnh `nhanvat-8mach.png` |
| Đổi ảnh ngọc có phải làm gì thêm? | Bump `$hmImgVer` (`game/huyetmach.php:212,230`), nếu không trình duyệt giữ ảnh cũ. | §5 |
| Sửa `.hm-*` có ảnh hưởng trang nào khác? | **Có**: `game/hammo.php` cũng dùng tiền tố `hm-` (`.hm-page .hm-btn …`, CSS `6414-6489`). | §6.8, `_hm_clash.php` |
| Số 1.481 Huyết Thạch ở đâu ra? | `1344` (8 mạch × 0→48) + `60` (thức tỉnh) + `77` (9 bậc nâng phẩm). | `E:/window/Temp/_eco.php` → `_eco.txt`, Sổ nợ dòng 1 |
| Test nào chứng minh chiến đấu? | `tests/test_huyetmach_combat.php` khối A–D (đơn vị) + `--live` (route thật) + `--measure` (ngưỡng pen). | §9 |
| Production đã có gì? | **3 bảng mới chắc chắn chưa có** (`huyetmach_boss`, `player_huyetmach_vessel`, `player_huyetmach_upgrade_log`). 4 cột `game1` thì **chưa đo** — chúng thuộc `sql/sql_thechat_huyetmach.sql:16-19` của đợt Thể Chất, không nằm trong file SQL của mach huyết. Nợ 15 bảng schema sid vẫn còn. | `docs/DEPLOY_PRODUCTION_HUYETMACH.md` mục 1–3 |
| Mở `tongquan.php` có gì mới? | Mục **"❤ Mạch Huyết (8 mạch luyện được)"** trong nhóm *Nuôi dưỡng nhân vật*, 17 dòng = 8 mạch + 5 bậc + 4 mốc, mỗi bảng có ô lọc riêng (`tbl-mh`/`tbl-mht`/`tbl-mhm`). | `tongquan.php:402-444`, ảnh `docs/img/huyetmach/tongquan-mach-huyet.png` |
| Vì sao bảng thức tỉnh ghi "Luyện Thể (cấp 100)" chứ không phải "Trúc Cơ"? | Cùng một cấp 100 — `tq_realm_req()` dịch cấp → **tên cảnh giới thật đang chạy trong game**, còn spec ghi tên Hán Việt (Trúc Cơ/Kim Đan/Nguyên Anh/Đại Thừa). Không phải lệch số. | `tongquan.php:89`, §2 dòng `tongquan.php` |
| Sao mục mới không rộng 840px như Nhục Thân? | Spec §1.8 (dòng 50) đòi vậy nhưng **không có CSS nào như vậy**: `.hm-wrap{margin:0}` và vỏ trang là `.main.tcg-main-panel` = `min(660px, 100vw − 24px)` dùng chung mọi trang. Nới ra là đụng mọi trang ⇒ để chủ dự án quyết. | `game.php:585`, `css/mystyle.css:199,7273`, Sổ nợ dòng 27 |

---

## 12. Thang hiệu ứng phân bậc

### 12.1 Màn chính — `hm-fx0 … hm-fx4` (theo bậc thức tỉnh)

Gán tại `game/huyetmach.php:114` → `hm-fx{max(0,min(4,stage))}`, đặt trên `.hm-wrap` (`:164`) cùng biến `--hm-tier` = `tiers[stage].color`.

| stage | class | `--hm-dur` | `--hm-aura` | `--hm-glow` | `--hm-act-dur` | màu bậc |
|---|---|---|---|---|---|---|
| 0 | `hm-fx0` | 0s | .04 | 4px | 6s | `#6b7280` |
| 1 | `hm-fx1` | 10s | .06 | 7px | 5s | `#4caf50` |
| 2 | `hm-fx2` | 8s | .09 | 10px | 4.2s | `#ff9800` |
| 3 | `hm-fx3` | 5.5s | .13 | 14px | 3.4s | `#f44336` |
| 4 | `hm-fx4` | 3.6s | .17 | 19px | 2.8s | `#ffd700` |

CSS: biến `7513-7517`, aura `7528`, sweep `7529`, `is-max` glow `7531`, `hmTierGlow` `7541`, **tắt cho fx0** `7546-7547`.

- **DELTA (cao stage / wrap / page) = 0 ở cả 5 bậc** ⇒ hiệu ứng chỉ thêm ánh sáng, không thêm hình dạng (đúng tiêu chí đã duyệt).
- **Bậc thấp nhất vẫn phải thấy gì đó**: fx0 tắt animation nhưng aura `opacity .04` và badge `box-shadow 0 0 4px` vẫn **tĩnh**.
- Reduced motion: mọi `animationName` về `none`/`display:none` (sweep, `.hm-ms-fill:after`, shine), `opacity` aura **giữ nguyên** biến fx.
- Tên biến thật là **`--hm-act-dur`** (không phải `--hm-btn-dur` như plan ghi); shine nằm ở `.hm-act-refine:after,.hm-act-break:after` (`7363`), **không có** `.hm-btn:after`.
- `game/huyetmach_transform.php` **không gán `hm-fxN`** ⇒ đo shine ở đó chỉ ra giá trị mặc định.

### 12.2 Màn Nâng Phẩm — `hmq-q1 … hmq-q10` (theo phẩm chất)

Gán tại `game/huyetmach_upgrade.php:22` (`hmq-q{max(1,min(10,quality))}`). CSS `mystyle.css:7580-7662`.

| phẩm | `--aura-op` | `--aura-dur` | `--glow` | `--halo` | `--sweep-op` |
|---|---|---|---|---|---|
| q1 (Phàm Phẩm) | .24 | 6s | 8px | 0px | 0 |
| q5 (Thánh Phẩm) | .52 | 5s | 19px | 11px | .16 |
| q9 (Đế Phẩm) | .82 | 2.4s | 34px | 20px | .32 |
| q10 (Đạo Phẩm) | .92 | 2s | 40px | 24px | .38 |

Đầy đủ 10 bậc ở `7615-7624`. **q1 không được tran** (vẫn phải thấy aura + glow); q1-q4 không sweep, q1-q3 không shine nút (`7628-7630`). **Chữ cầu vồng chỉ có ở q10**, q9 không.

**Số đo nghiệm thu**: `.hmq-card` **rộng 350px ở mọi trạng thái**; cao q1=281 / q9=296 / q10=70 (q10 là trạng thái "đã tới max", hết form nâng phẩm nên thấp hơn — **không so sánh cao giữa q1 và q10**, chỉ so rộng và overflow). `huyetmach_id=0` → card rỗng 66px.

### 12.3 Ba màn phụ dùng chung khung

`huyetmach_boss.php:60` gán `hm-fx{stage}` trực tiếp từ `$player->huyetmach_awaken_stage` (có clamp 0..4) và `--hm-tier` từ `huyetmach_awaken_color()`. `huyetmach_upgrade.php:40,78` và `huyetmach_transform.php:59` dùng `.hm-wrap .hm-card` nhưng **không** gán fx.

---

## Sổ nợ chờ chủ dự án

Đo ngày **2026-10-10** bằng `E:/window/Temp/_eco.php` (→ `_eco.txt`) + `tests/test_huyetmach_combat.php --measure` + `--live`. **Không dòng nào được tự sửa số** (quy tắc "không tự nới phạm vi sửa"). Chứng cứ là `file:dòng` thật trong repo hoặc neo Task + key config/tên hàm.

| # | Nhóm | Vấn đề | Chứng cứ | Cần chốt |
|---|---|---|---|---|
| 1 | Kinh tế | Tổng Huyết Thạch 1 nhân vật = **1.481**: luyện 8 mạch 0→48 = **1.344**; thức tỉnh stage 1..4 = **60**; nâng phẩm 1→10 = **77**. Nguồn rơi 5 trận/ngày theo stage: stage 1 = **20 HT/ngày**, stage 2 = **40**, stage 3 = **80** ⇒ **71 / 36 / 18 ngày** cày boss riêng phần luyện + thức tỉnh | Task 1 `refine` (`material_base 1` + `material_per_lv 8`, `linh_thach_base 200` + `25/cấp`); Task 1 `tiers`; Task 9 `huyetmach_upgrade_config.json` `upgrades`; Task 8 `huyetmach_boss_config.json` `drops.by_stage` (4/8/16) + `max_fights_per_day 5` | (a) giữ ⇒ ~3 tuần/character; (b) hạ `material_base`/`material_per_lv`; (c) nâng `max_fights_per_day`; (d) tăng `huyet_thach` mỗi đòn |
| 2 | Kinh tế | **Nguồn Huyết Thạch = 0 ở stage 0 và stage 4.** `huyetmach_boss_spawn()` (`huyetmach_boss.php:87`) sinh boss `stage = hiện tại + 1`, config chỉ có stage **2/3/4** ⇒ stage 0 đòi boss stage 1 (không tồn tại), stage 4 đòi tier 5 (`huyetmach_vessel_tier(5) === false`) — cả hai return false. Trong khi 9 bậc nâng phẩm vẫn cần 77 HT | `class/config/huyetmach_boss_config.json` (`bosses` = 33 bản ghi, key `*_2/*_3/*_4`); Task 1 `tiers` (chỉ 0..4) | thêm tier 5 (trần 60?), cho farm lại boss stage 4, hay mở nguồn HT khác (`game/duihuan.php`, công thức đan) |
| 3 | Kinh tế | **113 Huyết Mạch Đan không có nguồn rơi.** tier 4 cần `material_id 113 × 1`, nhưng `huyetmach_boss_finish()` (`huyetmach_boss.php:169,177`) chỉ cộng id lấy từ `drops.huyet_thach_id` = **112** | `huyetmach_vessel_config.json` `tiers["4"].material_id`; `huyetmach_boss.php:177`; `daoju_config.json:407-411` (chỉ khai báo) | đan rơi boss stage 4 với % bao nhiêu / bán bằng Linh Thạch / craft từ 112 (`game/use_dj.php`) |
| 4 | Kinh tế | Linh Thạch đường **nâng phẩm áp đảo** đường mạch: **126.406.000** (9 bước) so với **927.400** (toàn bộ luyện 8 mạch + thức tỉnh) = **gấp 136,3 lần** | `huyetmach_upgrade_config.json` `linh_thach_cost` (1.000 → 100.000.000); `_eco.php` | có thật sự muốn có người tới Đạo Phẩm? Nếu không, cắt bậc 7-9 hoặc đổi `linh_thach_cost` |
| 5 | Boss | Boss mạch **yếu hơn hẳn** boss Linh Căn: `hp_per_level 220 / atk 14 / def 9 / crit 15` so với `500 / 20 / 10 / 20` ⇒ chỉ **44% HP** | `huyetmach_boss_config.json` `boss_config.stats`; `class/config/linhcan_boss_config.json:8,12-14`; spec `...-design.md:110` | theo Task 8 (thuần `level × stage_mult`) hay theo spec (`hp = 8000 + level*220`, `atk = 260 + level*7`)? |
| 6 | Config chết | `quest_require` + bảng `player_huyetmach_quest`: config vẫn `true` ở 2 bậc; Task 6 chỉ **bỏ khỏi UI**; `huyetmach_quest_progress()`/`huyetmach_quest_update()` tồn tại nhưng **không cuộc gọi nào** ⇒ bảng 0 dòng; preflight vẫn liệt kê | `huyetmach_awakening_config.json:22,35-36`; `class/function/huyetmach.php:335,352`; `tests/deploy_preflight.php:24` | xoá key + bỏ khỏi preflight, hay dựng chuỗi quest thức tỉnh thật? |
| 7 | Config chết | Nhiều trường của `huyetmach_awakening_config.json` **không còn ai đọc** (`stat_bonus_mult`, `passive_description`, `boss_drop_require`, `material_id 108`, `material_amount`) vì mọi điều kiện lấy từ `tiers`; nơi duy nhất in `passive_description` là `game/huyetmach_awakening.php:30` — **file đã xoá** ở Task 7 | `huyetmach_awakening_config.json:10,12,23,25,37`; `huyetmach.php:433-441` (108 = "Đạo Nguyên") | dọn JSON hay giữ để đối chiếu lịch sử? |
| 8 | Cân bằng | **Trần thật của 4 chỉ số âm là 12%, không phải 15%:** mỗi chỉ số chỉ do MỘT mạch mang, `per_lv 0,25 × trần mạch 48 = 12` ⇒ `stat_cap 15` không bao giờ binding | `huyetmach_vessel_config.json` `vessels` 5-8, `stat_cap`, `tiers["4"].lv_cap`; logic `huyetmach.php:1073-1075` | nâng `per_lv` = **0,3125** (48 cấp = 15%), hay hạ `stat_cap` xuống 12, hay cho 2 mạch cùng chỉ số? |
| 9 | Cân bằng | **Xuyên Giảm cố ý KHÔNG áp dụng khi PvP:** pen 15% làm sát thương PvP tăng mean **+29,59%** (median +33,3%, max +72,7%), kể cả pen 5% vẫn **+9,64%** — vượt ngưỡng 8% spec §5 cho phép | §11.0b Task 11; `game/pvp.php` (không gọi `huyetmach_combat_pierce_def`); mô tả vessel 6 trong config | (a) chấp nhận tooltip "không áp dụng PvP"; (b) `per_lv` riêng cho bề mặt PvP; (c) bỏ hẳn mạch pen khỏi 8 mạch |
| 10 | Cân bằng | **Pen gần vô hiệu khi cày đúng cấp** (hệ quả có trước): sàn 15% chặn **78% cặp PVE** và **100% cặp BOSS** trong dữ liệu local ⇒ pen = 0 ở đó. Đo pen 12%: PVE **+2,52%**, BOSS **+0,1%** | `game/pve.php:245-246`, `game/boss.php:242-243`, `game/boss.php:215`; §9.1 | có nới sàn 15% không? (**đây là sửa công thức sát thương toàn game**, không phải bug mạch huyết) |
| 11 | Cân bằng | **Bề mặt chiến đấu thứ 4 chưa nối mạch huyết**: `game/club_boss.php` (boss bang hội) tự tính `$reduced = round($playerAtk - $bossDef*0.75)`, boss phản đòn, **né cứng 15%** và hút máu chia 220 — không gọi 5 helper `huyetmach_combat_*` nào | `game/club_boss.php:214` (công thức), `:226` + `:251` (`mt_rand(1,100) > 85` = né cứng 15%), `:234` (hút máu `/220`), `:239-240` (đòn phản); §4.1 | nối 4 chỉ số âm vào boss bang hội bằng task riêng, hay khai báo ngay trong UI rằng boss bang hội không dùng mạch huyết? |
| 12 | Spec vs code | **Mạch 5 và 7 đảo chỉ số so spec**: spec đặt `dodge` cho tay trái / `reduce` cho chân trái; config thật đặt id 5 (`arm-left`) = `reduce` (Dương Duy Mạch) và id 7 (`leg-left`) = `dodge` (Âm Kiều Mạch). Theo y học TQ thì Dương Duy mạch chạy hai tay, Âm Kiều mạch hai chân ⇒ code hợp lý hơn, nhưng khác văn bản đã duyệt | spec `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md:71,73`; `class/config/huyetmach_vessel_config.json` vessels id 5 và id 7 | giữ code (dương–tay / âm–chân) hay revert về spec? chỉ đổi `stat`, không đổi `slot` |
| 13 | Spec vs code | **Ba chỗ lệch số tăng trưởng**: (a) `total_lv_require` của 4 bậc thức tỉnh = **32 / 64 / 96 / 144** (config) vs **32 / 80 / 152 / 240** (spec); (b) mốc tổng cấp = **4 mốc 32/80/128/176** (+5/+12/+20/+30%) vs **5 mốc 40/88/160/248/340** (+5/+10/+15/+20/+25%); (c) bậc 2 và 3 có vật liệu **112 × 20 / 112 × 40** trong khi spec ghi "—" cho hai bậc này (spec chỉ đặt 113 × 1 ở bậc cuối) | `class/config/huyetmach_vessel_config.json` `tiers` và `milestones`; spec `:84-93` | chấp nhận bảng config (mềm hơn, về đích sớm hơn) hay trả về spec? lưu ý spec nhân "8 × trần cấp bậc hiện tại + dồn dập", config thì cộng đều 32 |
| 14 | Phạm vi sửa | **ĐÃ CHỐT 2026-10-10 — Nhục Thân đổi màu 2 nút theo Mạch Huyết, commit RIÊNG `e9fea2b`**: `.main .nb-btn-refine` cam→vàng (`#f97316→#eab308`), `.main .nb-btn-break` tím→hồng (`#7c3aed→#db2777`), `[disabled]` opacity .45; block `.main` là bắt buộc vì `.main button` (đặc tính 0-1-1) đè `.nb-btn` (0-1-0) | `css/mystyle.css:7084-7087`; đối chứng `.main .hm-act*` ở `css/mystyle.css:7558-7561`; bump `game.php:502` `?v=2026101005`; class có thật tại `game/thechat.php:259,309`; ảnh 1:1 `E:/window/Temp/_nb_thechat_band0.png` + `_nb_huyetmach_band1.png` | **màu đã chốt, không còn gì để hỏi**; việc còn lại duy nhất: 2 file này **chưa deploy production** (mục 4 của `docs/DEPLOY_PRODUCTION_HUYETMACH.md`) |
| 15 | Bug cũ | **Nút tấn công boss Linh Căn không bao giờ chạy**: điều kiện ở `game/linhcan_boss.php:37` kiểm `$cmd == 'attack_lc_boss'`, nhưng nút lại gửi `cmd=linhcan_boss&attack_lc_boss=1` (`:83`) ⇒ `$cmd` luôn là `linhcan_boss`, nhánh tấn công không bao giờ vào | `game/linhcan_boss.php:37` (điều kiện), `:83` (`$attackCmd`); `grep -n attack_lc_boss` chỉ ra đúng 2 dòng này | sửa thành `isset($attack_lc_boss)` (1 dòng, đụng trang Linh Căn ngoài plan) hay để chủ dự án quyết riêng cho Linh Căn? |
| 16 | Bug cũ | **Thưởng đá ngũ hành của boss Linh Căn chỉ in chữ, không cộng vào túi**: có comment TODO ngay trên dòng in "🪨 3 Đá {ngũ hành}" ⇒ người chơi thấy thưởng mà không nhận | `class/function/linhcan_boss.php:192-193` | bật cộng thật (dùng `add_daoju`) hay đổi chữ thành "sẽ nhận ở nhiệm vụ"? |
| 17 | Bug cũ | **Bảng `boss` không có cột `element`** (đo `SHOW COLUMNS FROM boss` = `bossid…bossexp`, không có `element`), trong khi ngũ hành là lõi của bản đồ khí huyết và của boss mạch huyết | `docs/DEPLOY_PRODUCTION_NGUHANH.md` (đã liệt kê nợ này); đo lại 2026-10-10 | thêm cột `element` cho boss thế giới hay để boss thế giới vô tính? (ảnh hưởng cả `game/boss.php`) |
| 18 | Bug cũ | **`thechat_upgrade()` (Nhục Thân / Thể Chất) không bọc transaction và không kiểm `rowCount()`**: trừ Linh Thạch rồi mới trừ vật liệu, nếu bước sau fail thì Linh Thạch mất không; mạch huyết đã làm đúng chuẩn để đối chứng | `class/function/thechat.php:388` (hàm), so `class/function/huyetmach.php:688-703` (`beginTransaction` + 3 lần `rowCount() !== 1` + `rollBack`) | port mẫu của mach huyết sang thechat bằng task riêng (đụng trang Nhuc Thân) hay chấp nhận rủi ro? |
| 19 | Deploy | **Production chưa migration gì cho Mach Huyết**: 3 bảng mới `huyetmach_boss` / `player_huyetmach_vessel` / `player_huyetmach_upgrade_log` chưa tồn tại; 2 bảng cũ `player_huyetmach_boss` / `player_huyetmach_quest` có nhưng 0 dòng; 4 cột `huyetmach_*` trong `game1` local đã có, production chưa kiểm | `docs/DEPLOY_PRODUCTION_HUYETMACH.md` (mục 1–2, đo 2026-10-10) + `sql/sql_huyetmach_vessel.sql` | chạy theo đúng trình tự trong file deploy (dump → SQL → preflight → copy code → 5 nghiệm thu) — và **phải trả xong nợ schema sid 15 bảng** trong `docs/DEPLOY_PRODUCTION_NGUHANH.md` trước |
| 20 | Thuật ngữ | **Nhãn "Xuyên Giảm" chưa chốt**: mach 6 là `pen` = xuyên phòng/phá giáp, "Xuyên Giảm" dễ đọc thành "giảm xuyên". Trang `huyetmach.php` và `tongquan.php` đang dùng cùng chuỗi này trong config nên chỉ cần sửa 1 chỗ | `class/config/huyetmach_vessel_config.json` (vessel id 6 `stat_labels`/`name`); §7 dòng typo | chốt "Xuyên Giáp" / "Phá Giáp" / "Xuyên Giảm"? (quy ước: nhãn UI phải là cặp đối nghĩa tự nhiên) |
| 21 | UI | **Trang Nhân Vật thiếu đơn vị `%` cho 6 chỉ số phần trăm**: `game/zhuangtai.php` in `Mạch <nhãn>` + `+<số>` thô nên "Mạch Bạo Kích +12,48" và "Mạch Né Tránh +12" nhìn giống hệt nhau dù cả hai đều là phần trăm, còn "Mạch Công +187" lại không có đơn vị; `game/huyetmach.php:96-97` đã có bảng hậu tố đúng | `game/zhuangtai.php:587-591`; đối chứng `game/huyetmach.php:96-97` (`crit/lifesteal/dodge/pen/reduce/luck` ⇒ `%`) | thêm `%` vào `zhuangtai.php` (đụng trang Nhân Vật, nằm ngoài plan) hay để nguyên vì cột đã có nhãn "Mạch"? |
| 22 | Tài sản | **4 mach âm dùng chung một viên ngọc, chỉ khác màu vòng**: ảnh riêng chỉ tồn tại cho `atk/hp/crit/lifesteal` (+ `def/exp` của hệ cũ); `dodge/pen/reduce/luck` render `ngoc-khoa|sang|day.png` với `--ring` theo màu bậc ⇒ trên điện thoại khó phân biệt 4 mach âm nếu không đọc nhãn | `game/huyetmach.php:220-221` (`$hmGfam` rỗng cho mach am); `ls images/huyetmach/` (22 file, không có `ngoc-dodge-*`…) | sinh 4 bộ ảnh riêng (ImageGen, nền theo màu trang) hay chấp nhận phân biệt bằng nhãn + màu? |
| 23 | Thẩm mỹ | **Bậc 0 màu xám `#6b7280`** (đổi từ `#666666` vì trên nền tối `#666` gần như mất nét). Quy ước đã chốt của chủ dự án: **bậc thấp nhất vẫn phải thấy gì đó** — bậc 0 hiện vẫn có viền + tên + chỉ số, nhưng không có `hm-fx0` nào trông thấy rõ | `class/config/huyetmach_vessel_config.json` tiers stage 0 `color`; §12.1 (bảng `hm-fx0..fx4`, DELTA = 0) | nới `hm-fx0` (thêm quầng rất nhẹ) hay chấp nhận "bậc 0 = chưa khai mở, phải xám"? |
| 24 | Kiểm chứng | **Mẫu local quá mỏng để chốt cân bằng**: toàn DB chỉ 12 nhân vật và 8 dòng `player_huyetmach_vessel` (một nhân vật seed để test); các con số +2,52% / +0,1% / 78% cặp bị sàn chặn đều đo trên mẫu đó | `SELECT COUNT(*) FROM game1` = 12, `FROM player_huyetmach_vessel` = 8 (đo 2026-10-10); §9.1 | sau khi deploy production, chạy lại `php tests/test_huyetmach_combat.php --measure --live` trên dữ liệu thật rồi mới chốt dòng 9-10 |
| 25 | Kiểm chứng | **Harness e2e vẫn trỏ `http://localhost` (port 80)** trong khi server dev local chạy `127.0.0.1:8123` (Apache 80 đã tắt) ⇒ mọi test đi qua `u()` gọi URL chết; test mach huyết phải truyền `HM_BASE` thủ công | `tests/e2e_fixture.php:132` (`return 'http://localhost/game.php?cmd='…`); §9 (khối lệnh 3) | sửa `e2e_fixture.php` lấy port từ `HM_BASE`/env (đụng harness của các tính năng khác) hay giữ và luôn truyền `HM_BASE`? |
| 26 | Thẩm mỹ | **Hai màn phụ không nối thang hiệu ứng**: chỉ `game/huyetmach.php:114` và `game/huyetmach_boss.php:60` gán `hm-fx<stage>`; màn Nâng Phẩm và màn Biến Thân mở ra trông như chưa có bậc ⇒ người chơi lên bậc nhưng qua 2 màn kia không thấy gì | `game/huyetmach.php:113-114`; `game/huyetmach_boss.php:60`; `grep -rn "hm-fx" game/*.php` chỉ ra đúng 2 file | gán `hm-fx` cho `game/huyetmach_upgrade.php` + `game/huyetmach_transform.php` (3 dòng CSS-compatible) hay khai báo 2 màn đó là màn thao tác, không cần hiệu ứng? |
| 27 | Spec vs code | **Spec §1.8 (dòng 50) yêu cầu `.hm-wrap` desktop 840px như Nhục Thân — không tồn tại trong CSS**: `.hm-wrap{margin:0}` không đặt `max-width`, trang bám khung chung `.main.tcg-main-panel` = `min(660px, 100vw − 24px)` (rule toàn cục, không nằm trong `@media`) nên mọi viewport từ 684px trở lên đều ra 660px ⇒ đây là quyết định "không tự nới khung chung", không phải sót CSS | spec `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md:50`; `css/mystyle.css:7273`; vỏ trang `game.php:585` + `css/mystyle.css:199`; §6 mục 9 | nới khung desktop lên 840px (đụng **mọi** trang vì `.tcg-main-panel` dùng chung) hay sửa spec xuống 660px? |

### Cách dùng bảng này

Bảng chia 6 nhóm, mỗi nhóm có một kiểu quyết định khác nhau — đừng đọc lần lượt từ trên xuống:

| Nhóm | Dòng | Ai trả lời | Cách trả lời |
|---|---|---|---|
| **Kinh tế / cân bằng** | 1, 2, 3, 4, 5, 8, 9, 10, 11 | chủ dự án | chỉ cần sửa JSON (`refine`, `tiers`, `boss_config`, `upgrades`), không đụng code; chạy lại `tests/test_huyetmach_economy.php` để lấy số mới |
| **Spec vs code** | 6, 7, 12, 13, 27 | chủ dự án | chốt "giữ code" hay "trả về spec"; nếu giữ code thì sửa spec để lần sau không ai thiết kế lại theo spec cũ |
| **Bug cũ của tính năng khác** | 15, 16, 17, 18 | chủ dự án (task riêng) | đều nằm ở Linh Căn / Thể Chất / bảng `boss` — mạch huyết chỉ mượn đường, sửa là đụng trang khác |
| **Phạm vi sửa / thuật ngữ / tài sản** | 14, 20, 21, 22, 23, 26 | chủ dự án | 14 là quyết định commit; 20/21 là nhãn; 22/23/26 là ảnh và hiệu ứng |
| **Deploy** | 19 | chủ dự án chạy, không ai chạy thay | theo `docs/DEPLOY_PRODUCTION_HUYETMACH.md`, và trả nợ sid trước |
| **Chưa kiểm chứng** | 24, 25 (+ §10.1 bốn mục) | lập lại sau deploy | đây là loại nợ "ghi rõ CHƯA test", không phải bug |

**Không dòng nào trong bảng được tự ý sửa số.** Mọi con số ở cột "Vấn đề" là kết quả đo ngày 2026-10-10 bằng `E:/window/Temp/_eco.php` và `php tests/test_huyetmach_combat.php --measure`; nếu code đổi, ghi thêm dòng mới kèm ngày đo, không ghi đè.
