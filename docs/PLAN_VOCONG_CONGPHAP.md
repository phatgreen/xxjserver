# KẾ HOẠCH HOÀN THIỆN VÕ CÔNG & CÔNG PHÁP — "Thi triển từng thế võ"

> Ngày lập: 2026-10-27 · Trạng thái: **GĐ 0 (phần võ công) + GĐ 1 + GĐ 2 (quái thường) + GĐ 4 (trang Võ Công) đã code** — xem mục 8.
> ⚠ Mục 3.1 (Nộ Khí) và nút Phòng Thủ **đã bị thay** theo quyết định ở mục 7: dùng **Thể lực** có sẵn, không có nút Phòng Thủ.
> Mục tiêu người chơi: mỗi lượt đánh quái thấy rõ **mình tung thế võ gì → gây bao nhiêu sát thương →
> quái/boss phản kích chiêu gì → mình mất bao nhiêu**. Có qua có lại, có lựa chọn.

---

## 1. Hiện trạng (đã kiểm tra code)

### 1.1 Luồng chiến đấu PVE (`game/pve.php`)
- Mỗi lần bấm **⚔ Tấn công** (`cmd=pvegj`) = 1 lượt trao đổi: người chơi đánh 1 đòn, quái đánh lại 1 đòn.
- Sát thương người chơi: `ugj + pet - gfy*0.75` (sàn 15%), bạo kích x1.72 (`pve.php:~258`).
- Sát thương quái: `ggj - ufy*0.75` (sàn 15%). **Quái không có chiêu**, `gbj`/`gxx` (bạo kích, hút máu của quái) có trong `guaiwu_config.json` nhưng **không được dùng**.
- Mô tả đòn đánh: 3 câu văn ngẫu nhiên chung cho mọi võ công (`pve.php:~388`). Võ công chỉ hiện tên, không có thế võ.
- Hàng "Phù lục 1/2/3" (`jineng`) khi dùng lại ghi nhãn **"(công pháp)"** (`pve.php:397`) → sai tên, gây hiểu nhầm với hệ Công Pháp thật.

### 1.2 Võ công (`class/config/wugong_config.json` — 30 bộ, 1 bộ trang bị)
- Có: chỉ số cộng (`add_gj/fy/hp/bj/xx`), hệ, loại (ngoại/nội/khinh công), phẩm, **1 hiệu ứng bị động** `effect_type`.
- Hiệu ứng **đã chạy** trong PVE: `pierce_def`, `damage_reduce`, `control_reduce_damage`, `combo_hit`, `counter_lifesteal`, `steal_reward`, `drop_bonus`.
- Hiệu ứng **khai báo nhưng KHÔNG chạy ở đâu cả** (người chơi bị thiệt):

| effect_type | Số bộ | Ghi chú |
|---|---|---|
| `crit_dmg` | 6 | tăng sát thương bạo kích |
| `hp_regen` | 3 | hồi máu mỗi lượt |
| `escape` | 3 | khinh công thoát trận |
| `poison_immune` | 1 | chưa có hệ độc |

- Nâng cấp: kinh nghiệm võ công đầy → tiêu bí tịch để lên cấp (`game/wugong.php`). Cấp võ công hiện **chỉ** tăng chỉ số, không mở thêm gì.

### 1.3 Công pháp (`class/config/congphap_config.json`, tối đa 1–3 bộ)
- `congphap_calc_buffs()` (`class/function/congphap.php:351`) chỉ thực sự dùng **`absorb_speed`** (kèm phạt công/thủ) và **`exp_bonus`**.
- Các hiệu ứng **hiện trên giao diện nhưng không có tác dụng**: `atk_bonus`, `def_bonus`, `crit_dmg`, `dmg_reduce`, `hp_regen`, `heal_bonus`, `drop_rate`, `stone_bonus`, `mining_bonus`.

### 1.4 Code chiến đấu bị nhân bản
Mỗi màn có công thức riêng: `pve.php`, `boss.php`, `club_boss.php`, `toayeuthap.php`, `vandinhdai.php`.
Thêm thế võ vào từng file riêng → 5 bản sửa, dễ lệch. **Cần 1 module chung** (mục 4).

---

## 2. Mục tiêu thiết kế

1. **Thế võ**: mỗi võ công có 3 thế, mở dần theo cấp võ công. Mỗi thế có tên, văn miêu tả, hệ số sát thương, hiệu ứng riêng, hồi chiêu.
2. **Nhật ký lượt đánh rõ ràng**: mỗi lượt in 2 dòng chính — đòn của ta và phản kích của địch — kèm con số.
3. **Quái/Boss có chiêu**: phản kích bằng chiêu có tên; boss có **báo trước** chiêu mạnh để người chơi chọn Phòng Thủ.
4. **Lựa chọn có ý nghĩa**: Đánh thường (tích Nộ) / Thế võ (tốn Nộ, có hồi chiêu) / Phòng Thủ (giảm sát thương, tích Nộ).
5. **Không phá kinh tế**: sát thương trung bình mỗi lượt tăng nhẹ (≤ +15%), thời gian hạ quái thường giữ 3–5 lượt.
6. **Sửa nợ**: mọi hiệu ứng hiện trên giao diện phải có tác dụng thật.

---

## 3. Thiết kế chi tiết

### 3.1 Tài nguyên: **Nộ Khí** (0–100)
| Hành động | Nộ |
|---|---|
| Đánh thường trúng | +20 |
| Bị đánh trúng | +10 |
| Phòng Thủ | +30 |
| Thế 2 | −40 |
| Thế 3 (Tuyệt Chiêu) | −100 |

Nộ reset khi vào trận mới. Lý do chọn Nộ thay vì Nội lực/MP: không cần cột chỉ số mới, không đụng đan dược/kinh tế.

### 3.2 Ba thế của mỗi võ công
| Thế | Mở khóa | Hệ số | Hồi chiêu | Đặc trưng |
|---|---|---|---|---|
| Thế 1 — Khởi thức | cấp 1 | x1.0 | 0 | = đánh thường, có văn riêng |
| Thế 2 — Biến thức | cấp 5 | x1.6 | 2 lượt | kèm hiệu ứng phụ theo loại võ công |
| Thế 3 — Tuyệt Chiêu | cấp 10 | x2.8 (phẩm Thần Công x3.2) | 4 lượt | hiệu ứng mạnh, 100 Nộ |

Hiệu ứng Thế 2/3 gợi ý theo `category`:
- **Ngoại công**: xuyên phòng / đánh 2–3 lần (`hits`).
- **Nội công**: hút máu / gây "Nội thương" (mất % máu 2 lượt).
- **Khinh công**: né đòn kế tiếp / ra tay trước (quái mất lượt phản kích).

Văn thi triển dùng mẫu có biến, ví dụ:
> «Thập Tự Phong Đao · Thế 2: **Song Phong Giao Trảm**» — Đao quang vạch hai đường chữ thập xé gió, {target} không kịp né! → **1.284** sát thương (xuyên 25% phòng ngự)

### 3.3 Quái & Boss phản kích
- Quái thường: 1 chiêu cơ bản + 1 chiêu đặc trưng theo **hệ** (`element`) với tỉ lệ 20–30%. Dùng luôn `gbj` (bạo kích) và `gxx` (hút máu) đang bỏ không.
- Boss: 3–4 chiêu, có **phase** (HP ≤ 50%: cuồng nộ +20% công; HP ≤ 20%: tuyệt kỹ).
- **Báo trước**: lượt N in "⚠ {Boss} đang tụ lực — lượt sau tung «Hủy Thiên Chưởng»!", lượt N+1 tung chiêu x2.5. Nếu người chơi Phòng Thủ → chỉ nhận 30%. Đây là điểm "có qua có lại" quan trọng nhất.

Mẫu nhật ký 1 lượt:
```
── Lượt 3 ──  Nộ: 60/100
⚔ Ngươi thi triển «Song Phong Giao Trảm» → Hồ Yêu mất 1.284 HP (Bạo kích!)
🐾 Hồ Yêu phản kích «Hồ Hỏa Phệ Tâm» → ngươi mất 312 HP, bị Thiêu Đốt 2 lượt
❤ Hút máu +64 · Còn 4.102/5.000 HP
```

### 3.4 Nút hành động (giao diện trận)
`⚔ Đánh thường` · `[Thế 2 (40 nộ)]` · `[Tuyệt Chiêu (100 nộ)]` · `🛡 Phòng Thủ` · Phù lục 1–3 · Dược 1–3 · `↩ Bỏ chạy`.
Thế đang hồi chiêu/thiếu nộ hiện mờ kèm "còn 2 lượt"/"thiếu 40 nộ". Có thanh Nộ dưới thanh HP.

### 3.5 Lưu trạng thái trận
Bảng mới (1 trận/người chơi):
```sql
CREATE TABLE player_combat_state (
  sid      VARCHAR(32) NOT NULL PRIMARY KEY,
  gid      INT NOT NULL,            -- id midguaiwu / boss đang đánh
  turn     SMALLINT NOT NULL DEFAULT 0,
  rage     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  cd_json  VARCHAR(255) NOT NULL DEFAULT '',  -- hồi chiêu các thế
  st_json  VARCHAR(255) NOT NULL DEFAULT '',  -- trạng thái: thiêu đốt, nội thương, né, boss tụ lực
  updated  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
1 query đọc + 1 query ghi mỗi lượt (theo PK, không cần index thêm). Khi `gid` khác trận đang lưu → reset.

### 3.6 Config mới (không hard-code số trong PHP)
- `class/config/wugong_skill_config.json` — theo `wgid`:
```json
"2": [
  {"tier":1,"name":"Phong Đao Khởi Thức","unlock_lv":1,"mult":1.0,"cd":0,"rage":0,"text":"..."},
  {"tier":2,"name":"Song Phong Giao Trảm","unlock_lv":5,"mult":1.6,"cd":2,"rage":40,"effect":"pierce_def","value":25,"text":"..."},
  {"tier":3,"name":"Thập Tự Diệt Không","unlock_lv":10,"mult":2.8,"cd":4,"rage":100,"effect":"multi_hit","value":3,"text":"..."}
]
```
- `class/config/guaiwu_skill_config.json` — chiêu theo hệ (dùng chung cho quái thường) + chiêu riêng theo `id` boss.
- Võ công thiếu cấu hình thế → **tự sinh** từ `category` + `element` (fallback), để 30 bộ chạy được ngay, viết văn dần sau.

---

## 4. Kiến trúc

Tạo `class/function/combat.php` (namespace `player`, hàm bọc `function_exists`, PHP 5.6):

| Hàm | Việc |
|---|---|
| `combat_state_load/save($sid, $gid, $dblj)` | đọc/ghi `player_combat_state` |
| `combat_wugong_moves($wgid, $wglv)` | trả 3 thế + trạng thái mở khóa |
| `combat_player_action($player, $mob, $action, &$state)` | tính đòn của ta → `array(dmg, heal, logs)` |
| `combat_mob_action($player, $mob, &$state)` | chọn chiêu quái/boss, báo trước, phase → `array(dmg, logs)` |
| `combat_apply_passives($player)` | gom hiệu ứng võ công + công pháp đang thiếu (mục 1.2, 1.3) |
| `combat_log_html($turn, $logs)` | in nhật ký lượt |

`pve.php` chỉ gọi các hàm này **trong nhánh `cmd=pvegj`**; phần thưởng, nhiệm vụ, rơi đồ giữ nguyên.
Các màn boss chuyển sang module ở giai đoạn sau, từng file một.

---

## 5. Lộ trình triển khai

| GĐ | Nội dung | File chính | Test / nghiệm thu |
|---|---|---|---|
| **0 — Sửa nợ** | (a) Áp dụng `crit_dmg`, `hp_regen`, `escape` của võ công; (b) áp dụng `atk_bonus`, `def_bonus`, `crit_dmg`, `dmg_reduce`, `hp_regen`, `heal_bonus`, `drop_rate`, `stone_bonus` của công pháp; (c) quái dùng `gbj`/`gxx`; (d) đổi nhãn "(công pháp)" → "(phù lục)" | `pve.php`, `class/player.php`, `congphap.php` | `tests/test_combat_passives.php`: mỗi effect có tác dụng đúng giá trị |
| **1 — Thế võ PVE** | config thế + fallback tự sinh; bảng state; Nộ; 3 nút thế; nhật ký lượt | `combat.php` (mới), `wugong_skill_config.json` (mới), `sql/migrate_combat_state.php`, `pve.php`, `css/mystyle.css` | test hệ số/CD/Nộ; giả lập 1.000 trận: số lượt hạ quái thường 3–5 |
| **2 — Quái phản kích** | chiêu theo hệ, Phòng Thủ, trạng thái (thiêu đốt, nội thương, né) | `combat.php`, `guaiwu_skill_config.json` | test xác suất chiêu, Phòng Thủ giảm đúng % |
| **3 — Boss** | phase, báo trước, chiêu riêng; chuyển `boss.php` → `club_boss.php` → `toayeuthap.php` | các file boss | đánh boss thật: báo trước + Phòng Thủ hoạt động |
| **4 — Giao diện Võ Công / Công Pháp** | `wugong.php`: tab **Chiêu Thức** (3 thế, mở khóa cấp mấy, hệ số, văn, sát thương ước tính theo chỉ số hiện tại); `congphap.php`: ghi rõ hiệu ứng tác dụng ở đâu | `game/wugong.php`, `game/congphap.php` | ảnh chụp 390px/840px như Thiên Phú |
| **5 — Viết văn & cân bằng** | viết tên/văn thế cho 30 võ công, 10–15 chiêu quái theo hệ, chiêu boss | config JSON | soát chính tả, giọng văn Tu Tiên |
| **6 — Tài liệu & deploy** | `UI_VOCONG.md`, runbook `DEPLOY_PRODUCTION_COMBAT.md` (chạy migrate trước khi chép code) | docs | — |

Thứ tự khuyến nghị: **0 → 1 → 2 → 4 → 3 → 5 → 6**. GĐ 0 nhỏ, sửa ngay được lỗi "hiện mà không có tác dụng"; GĐ 1+2 là phần người chơi cảm nhận rõ nhất.

---

## 6. Rủi ro & kiểm soát

| Rủi ro | Kiểm soát |
|---|---|
| Lực chiến tăng → farm nhanh, lạm phát linh thạch | Tuyệt Chiêu có CD 4 lượt + 100 Nộ; giả lập TTK trước khi bật; hệ số đặt trong JSON để chỉnh nóng |
| GĐ 0 làm mạnh người chơi đột ngột (hiệu ứng "ngủ" nay chạy thật) | So lực chiến trước/sau trên dữ liệu thật; cần thì giảm `effect_value` trong config |
| Thêm 2 query mỗi lượt | Truy vấn theo PK; có thể cache Redis sau nếu cần |
| Token `cmd` dùng một lần + F5 → bấm 2 lần cùng lượt | Lưu `turn` trong state, lượt cũ gửi lại thì bỏ qua |
| Tự động đánh (nếu có) | Tự động chỉ dùng Đánh thường + Thế 2, không dùng Tuyệt Chiêu |

---

## 7. Quyết định đã chốt (2026-10-27)

| # | Câu hỏi | Chốt |
|---|---|---|
| 1 | Tài nguyên dùng thế | **Thể lực có sẵn** (`ustamina`). Võ Kỹ 5% thể lực tối đa (như cũ), Tuyệt Kỹ x1.5, Tuyệt Chiêu x2 |
| 2 | Độ mạnh | Tăng dần theo **cấp võ công**; bị động mới bật chỉ 30% ở cấp 0, đủ 100% ở cấp 10. Mọi số trong JSON |
| 3 | Nút Phòng Thủ | **Bỏ**. Thay bằng thế có hiệu ứng `guard` (giảm sát thương lượt đó) / `stun` (quái mất lượt) |
| 4 | Văn thế võ | Viết đủ 3 thế × 29 võ công |

Nguyên tắc cân bằng: **sát thương / 1 điểm thể lực** của thế mạnh lúc mới mở < đánh thường
(Tuyệt Kỹ cấp 5: 1.25/1.5 = 0.83; Tuyệt Chiêu cấp 10: 1.8/2 = 0.9), chỉ vượt khi luyện võ công cao
(Tuyệt Kỹ trần 1.6/1.5 = 1.07; Tuyệt Chiêu trần 2.6/2 = 1.3, có hồi 4 lượt). ⇒ Thế võ giúp hạ nhanh quái khó / boss,
**không** làm tăng mạnh số quái hạ được trên 1 thanh thể lực ⇒ không phá nhịp cày.

## 8. Đã triển khai (2026-10-27)

| Hạng mục | File |
|---|---|
| Config thế võ (rules + 29 võ công × 3 thế, có văn) | `class/config/wugong_skill_config.json` |
| Config chiêu phản kích quái theo 6 hệ | `class/config/guaiwu_skill_config.json` |
| Module thế võ (hệ số, hồi chiêu, trạng thái trận, chiêu quái) | `class/function/combat.php` (nạp ở `class/player.php`) |
| Bảng `player_combat_state` (tự tạo; SQL dự phòng) | `sql/sql_combat_state.sql` |
| Trận PVE: nút Tuyệt Kỹ/Tuyệt Chiêu, nhật ký thế võ, quái phản kích có chiêu + bạo kích (`gbj`) | `game/pve.php` |
| Bật hiệu ứng võ công đang "ngủ": `crit_dmg`, `hp_regen`, `escape` (né), `poison_immune` (miễn đan độc) | `game/pve.php` |
| Nhãn "(công pháp)" của phù lục → "(phù lục)" | `game/pve.php` |
| Thẻ **Chiêu thức** trên trang Võ Công | `game/wugong.php`, `css/mystyle.css` |
| Test thuần (không DB) | `tests/test_combat_moves.php` |

Hiệu ứng thế: `pierce` (xuyên % phòng), `crit` (% chắc chắn bạo kích), `stun` (% quái mất lượt), `guard` (giảm % sát thương lượt này),
`heal` (% khí huyết tối đa), `lifesteal` (% sát thương), `burn` (thêm % sát thương). `hits` chỉ để hiển thị.

### Đợt 2 (2026-10-27)

| Hạng mục | Chi tiết | File |
|---|---|---|
| **Công pháp có tác dụng thật** | `atk_bonus`/`def_bonus` vào chỉ số (getplayer); `crit_dmg`, `dmg_reduce`, `hp_regen`, `drop_rate` (chung trần may mắn với Thiên Phú), `stone_bonus` trong PVE + Boss; `heal_bonus` vào dược. Hệ số + trần ở `rules.congphap_ratio/congphap_cap`. Trang Công Pháp hiện **giá trị thực áp dụng** | `class/player.php`, `game/pve.php`, `game/boss.php`, `game/congphap.php` |
| **Boss** | Thế võ dùng được (đòn thường vẫn miễn phí; Tuyệt Kỹ/Tuyệt Chiêu tốn thể lực). Cứ 4 lượt boss **tụ lực** (báo trước) → lượt sau x1.8; **Tuyệt Chiêu phá thế**; thế `guard`/`stun` chống đỡ. Máu ≤ 50% **cuồng nộ** x1.2. Khống chế lên boss còn 50%. Tuyệt Kỹ/Tuyệt Chiêu không bị boss né | `game/boss.php`, `guaiwu_skill_config.json > boss` |
| **Tháp Yêu / Vấn Đỉnh Đài / Boss bang** | Nhật ký dùng **tên + văn thế võ thật**, luân phiên theo hồi chiêu. **Chỉ hiển thị** — sát thương giữ nguyên công thức cũ để không lệch độ khó tháp, điểm đài và xếp hạng tông môn | `game/toayeuthap.php`, `game/vandinhdai.php`, `game/club_boss.php`, `js/club_boss.js` |

Bảng hệ số công pháp (giá trị config × hệ số, chặn trần tổng):

| Loại | Hệ số | Trần | Ví dụ max |
|---|---|---|---|
| atk_bonus / def_bonus | 0.4 | 25% | Như Lai Thần Chưởng 50% → 20% |
| dmg_reduce | 0.4 | 20% | Ngũ Hành Đại Đạo 45% → 18% |
| crit_dmg | 0.5 | +30% | Vạn Kiếm Quy Tông 50% → +25% hệ số bạo kích |
| hp_regen | 0.1 | 5%/lượt | Hỏa Phượng Quyết 50 → 5% khí huyết/lượt |
| heal_bonus | 1.0 | 40% | Hồi Sinh Quyết 25% |
| drop_rate | 0.3 | 20% | Vô Tự Thiên Thư 55% → 16.5% |
| stone_bonus | 0.3 | 15% | Tụ Bảo Huyền Công 45% → 13.5% |

**Còn mở:**
- `mining_bonus` (Phá Đá Khai Sơn): game **chưa có hệ đào mỏ** → công pháp này vô dụng. Đề xuất đổi sang `stone_bonus` (cần chủ dự án duyệt).
- Thế võ ở Tháp/Đài/Boss bang mới là hiển thị; muốn tính sát thương thật cần cân lại độ khó tháp trước.
- Cộng Hưởng (mục 9).

## 9. Đề xuất tiếp: Tàng Kinh Các — Cộng Hưởng (tham khảo ý tưởng, không sao chép)

- Mọi võ công/công pháp **đã học** (không chỉ cái đang trang bị) được tính vào bộ sưu tập.
- Chia **nhóm 3–5 bộ** theo chủ đề (cùng hệ ngũ hành / cùng loại). Đạt mốc số bộ "Viên Mãn" (đạt cấp mốc) trong nhóm ⇒ thưởng chỉ số vĩnh viễn nhỏ.
- Tạo mục tiêu dài hạn cho bí tịch cũ (hiện học xong bộ mạnh hơn là bỏ bộ cũ).
- Cần chốt trước: số nhóm, cấp "Viên Mãn", độ lớn thưởng (đề xuất tổng thưởng cả hệ ≤ 5% chỉ số).
