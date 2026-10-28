# THIẾT KẾ — CÂN BẰNG TÔNG MÔN (chờ chủ dự án duyệt)

> Ngày 2026-10-28. Nhánh `claude/tong-quan-du-an`. Trạng thái: **ĐÃ DUYỆT + ĐÃ LÀM** (cả 3 câu mục 5 = đồng ý) — kết quả ở mục 6.
> File liên quan: `game/club.php`, `class/function/club_quest.php`, `game/xiulian.php` (dòng cộng EXP tông môn 5%),
> `class/player.php` (chỗ nhân % chỉ số ~dòng 722–782), `tools/sim/econ_sim.py`.
> Bối cảnh: đợt G (`DOT_G_KINH_TE_THE_LUC_TRANG_BI.md`) và mô phỏng kinh tế **chưa có Tông Môn**.

## 1. Hiện trạng (đã kiểm chứng bằng code + DB)

| Hạng mục | Giá trị trong code |
|---|---|
| Nâng cấp 1→10 (tổng) | **29,5 triệu LT + 2,95 triệu MT + 2,145 triệu EXP tông môn** (`$club_upgrade_config`) |
| Lợi ích theo cấp | Thành viên tối đa 8→50; HP +2→+20%, ATK/DEF +1→+10%, EXP tu luyện +5→+50% (`$club_benefits_config`) |
| Nguồn EXP tông môn | Tu luyện 5% tu vi nhận (`xiulian.php`), quyên góp (10 LT hoặc 1 MT = 1 EXP), online 50/giờ, nhiệm vụ tông môn 4–6/ngày (100–1.200 EXP), boss tông môn 500 + 50 × thành viên/ngày |
| Quyên góp | Trừ LT/MT người chơi → quỹ tông môn; người góp nhận cùng lượng EXP cá nhân (không đáng kể) |

Thu nhập 1 người chơi F2P trong 30 ngày (chạy `econ_sim.py`, 10 lần, kịch bản săn đồ):

| Kiểu chơi | Cấp ngày 30 | LT thu | MT thu | Tu vi từ tu luyện |
|---|---|---|---|---|
| Nhẹ 2h | 130 | 5,8 tr | 6,4k | 60 tr |
| Vừa 5h | 146 | 8,4 tr | 8,6k | 98 tr |
| Cày 10h | 196 | 16,7 tr | 16,6k | 248 tr |

## 2. Vấn đề

### 2.1 Lợi ích tông môn KHÔNG có tác dụng (mức nghiêm trọng: cao — tính năng "rỗng")
HP/ATK/DEF/EXP chỉ in ra màn Tông Môn; `getplayer()`, chiến đấu, tu luyện không đọc tới. Nâng cấp chỉ tăng số thành viên.

### 2.2 Lỗi khai thác EXP tông môn (mức nghiêm trọng: rất cao — **đã xảy ra trong DB**)
- **Online (`claimonline`)**: mốc lần nhận trước lấy từ `$_SESSION`, lần đầu = 0 ⇒ `giờ = time()/3600 ≈ 496.000` ⇒ nhận **~24,8 triệu EXP** trong 1 lần bấm.
  Bằng chứng: `docs/db/game.sql` — tông môn `clubid = 20` có `clubexp = 24.817.310` (= `floor(time()/3600) × 50`), gấp 11 lần EXP cần để lên cấp 10.
  Đăng xuất/đăng nhập lại (session mới) ⇒ bấm lại được.
- **Boss tông môn (`clubboss`)**: không có trận đánh, chỉ 1 nút bấm; khóa "đã hạ hôm nay" nằm trong `$_SESSION` ⇒ mỗi thành viên / mỗi lần đăng nhập lại bấm được thêm lần nữa.

### 2.3 Hiển thị "+105%" EXP (đã sửa trong lượt này)
Cột EXP lưu kiểu hệ số (105 = ×1,05) trong khi màn hình in `+X%`. Đã đổi bảng sang phần cộng thêm (5…50) — không ảnh hưởng gì vì cột này chưa được dùng để tính.

### 2.4 Chi phí nâng cấp không khớp thu nhập
- **MT là nút thắt**: lên cấp 10 cần 2,95 tr MT; 10 thành viên "Vừa 5h" góp **toàn bộ** MT cả tháng mới được ~86k ⇒ F2P không bao giờ quá cấp 3–4.
- **EXP thì quá dễ**: chỉ riêng 5% tu luyện, 10 thành viên "Vừa" tạo ~49 tr EXP/tháng (gấp 23 lần tổng 2,145 tr cần) ⇒ cột EXP vô nghĩa.
- **EXP +50% quá mạnh nếu áp thật**: tu luyện là nguồn tu vi lớn nhất (mô phỏng: 60–248 tr/tháng). +50% ≈ rút ngắn rất nhiều thời gian lên cấp — trái mục tiêu "không tăng cấp nhanh".

## 3. Đề xuất

### 3.1 Sửa lỗi khai thác (làm trước, không đổi cân bằng)
| Việc | Cách làm |
|---|---|
| Online | Lưu mốc nhận vào DB (cột mới `clubplayer.online_claim_at`), lần đầu chỉ ghi mốc (0 EXP), trần 24 giờ/lần nhận |
| Boss tông môn | Khóa theo `clubid + ngày` trong DB (cột `club.boss_date`), cả tông môn 1 lần/ngày |
| Dữ liệu sai | `sql/migrate_tongmon.php --apply` đặt lại `clubexp` của tông môn vượt trần (vd. về 0) — game chưa mở nên an toàn |

### 3.2 Áp dụng lợi ích — mức nhỏ, mang tính "gắn kết" hơn là "sức mạnh"
| Cấp | Thành viên | HP | ATK | DEF | EXP tu luyện |
|---|---|---|---|---|---|
| 1 | 8 | +1% | +0,5% | +0,5% | +1% |
| 5 | 20 | +5% | +2,5% | +2,5% | +5% |
| 10 | 50 | +10% | +5% | +5% | +10% |

(tuyến tính theo cấp). Vị trí áp:
- HP/ATK/DEF: trong `getplayer()` cùng chỗ các % khác (`class/player.php` ~dòng 731–741), dùng `getclubplayer_once` + `getclub` (có cache theo request ⇒ thêm 1–2 query/request khi người chơi có tông môn — cần xác nhận cache).
- EXP: trong `game/xiulian.php` nhân cùng nhóm `(1 + trang bị%) × (1 + Thiên Phú%)` — **ngoài** chuỗi `min()` nhưng chỉ tối đa +10%.

Phương án khác: giữ bảng hiện tại (+20% HP, +50% EXP) — **không khuyến nghị**, đẩy tốc độ lên cấp quá xa so với mô phỏng đợt G.

### 3.3 Chi phí nâng cấp mới (mục tiêu: tông môn 10 người "Vừa", góp ~20% LT + ~30% MT ⇒ cấp 5 sau ~1 tháng, cấp 10 sau ~5–6 tháng)
| Lên cấp | LT | MT | EXP tông môn |
|---|---|---|---|
| 1→2 | 1,0 tr | 2.000 | 50k |
| 2→3 | 2,0 tr | 4.000 | 150k |
| 3→4 | 3,5 tr | 6.000 | 400k |
| 4→5 | 5,5 tr | 9.000 | 1 tr |
| 5→6 | 8 tr | 13.000 | 2 tr |
| 6→7 | 11 tr | 18.000 | 4 tr |
| 7→8 | 15 tr | 24.000 | 7 tr |
| 8→9 | 20 tr | 32.000 | 11 tr |
| 9→10 | 27 tr | 42.000 | 16 tr |
| **Tổng** | **93 tr** | **150k** | **41,6 tr** |

Kèm: giảm EXP tông môn từ tu luyện **5% → 1%** để cột EXP có ý nghĩa (10 người "Vừa" ≈ 10 tr/tháng ⇒ cấp 10 sau ~4 tháng, khớp với LT/MT).

### 3.4 Đưa Tông Môn vào mô phỏng
Thêm vào `tools/sim/econ_sim.py`: 1 tông môn N thành viên (dùng lại `sim()` cho từng người), tỉ lệ góp LT/MT, EXP tông môn từ tu luyện/nhiệm vụ/boss/online ⇒ in **cấp tông môn ngày 7/14/30/90/180** và **chênh lệch cấp người chơi có/không tông môn**. Dùng kết quả này chốt lại bảng 3.2/3.3 trước khi triển khai.

## 4. Kế hoạch triển khai (sau khi duyệt)
1. Mục 3.1 (sửa lỗi) + migrate + test `tests/test_tongmon.php` (online lần đầu = 0, boss 1 lần/ngày/tông môn).
2. Mục 3.4 (mô phỏng) → báo số liệu → chốt bảng.
3. Mục 3.2 + 3.3 → test chỉ số có/không tông môn → chụp màn hình → cập nhật `BAN_GIAO_PHIEN.md`.

## 5. Cần chủ dự án quyết định
1. Mức lợi ích: đề xuất 3.2 (tối đa +10% HP / +5% ATK-DEF / +10% EXP) hay mức khác?
2. Bảng chi phí 3.3 và giảm EXP tu luyện 5% → 1%: đồng ý?
3. Tông môn `clubid = 20` (EXP 24,8 tr do lỗi): đặt lại về 0?

## 6. Đã làm (2026-10-28)

| Hạng mục | File |
|---|---|
| Bảng lợi ích dùng chung `club_benefits_table()` + `club_player_bonus()` (1 query JOIN, chỉ khi có tông môn) | `class/player.php` |
| `getplayer()` nhân % Công / Phòng / Khí huyết cùng chỗ Cộng Hưởng (trước chỉ số cộng thẳng của Linh Căn) | `class/player.php` |
| Tu luyện: × (1 + EXP tông môn%) cùng nhóm trang bị / Thiên Phú; EXP tông môn từ tu luyện 5% → 1% | `game/xiulian.php` |
| Chi phí nâng cấp mới (mục 3.3), chữ hướng dẫn trên màn Tông Môn | `game/club.php` |
| Online: mốc nhận ở DB, lần đầu 0 EXP, trần 24 giờ/lần, chống bấm đôi | `game/club.php` |
| Boss: 1 lần/ngày cho cả tông môn (khóa DB, cập nhật có điều kiện) | `game/club.php` |
| Thưởng hoàn thành nhiệm vụ ngày: khóa DB thay session (cùng lỗi đăng nhập lại nhận lại) | `class/function/club_quest.php` |
| Migrate: 3 cột + đặt lại `clubexp >= 10 tr` về 0 | `sql/migrate_tongmon.php` |
| Test | `tests/test_tongmon.php` (bảng, % trong getplayer, chờ duyệt = 0, cột migrate) |
| Mô phỏng | `python3 tools/sim/econ_sim.py --tongmon` |

**Kiểm chứng (server thử cloud):** online lần đầu 0 EXP → lần 2 "còn 60 phút" → sau 30 giờ + session mới chỉ nhận 1.200 (trần 24h);
boss lần 2 trong ngày (cả session mới) bị chặn. `test_tongmon` ALL PASS; `test_stamina`, `test_canhgioi`, `test_dot_f` PASS;
`test_dot_c` D1 FAIL **có sẵn từ trước** (chạy trên code cũ cũng FAIL, không liên quan).

**Mô phỏng** (10 người: 3×2h, 5×5h, 2×10h; góp 20% LT + 30% MT/ngày; giả định góp không làm chậm người góp — lạc quan):
cấp tông môn đạt ngày 2:3 · 3:5 · 4:9 · **5:20** · 6:39 · 7:61 · 8:96 · 9:123 · **10:165**.
Ảnh hưởng EXP tu luyện lên người chơi "Vừa 5h" ngày 30: +0% → cấp 146,5 · +5% → 151,3 · +10% → 150,9 (≈ +3%, nhiễu ngẫu nhiên).

**Máy chủ cần chạy:** `sql\migrate_tongmon.php --dry-run` rồi `--apply` (sao lưu trước) — **bắt buộc**, code mới đọc 3 cột mới.
