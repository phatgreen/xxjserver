# ĐỢT G — NHIỆM VỤ CẢNH GIỚI, THỂ LỰC, MA THẠCH BOSS, NGUỒN TRANG BỊ + MÔ PHỎNG KINH TẾ

> Làm và triển khai lên máy chủ thật ngày 2026-10-28 (nhánh `claude/tong-quan-du-an`).
> Đây là phần tổng hợp. Từng dòng việc ngắn có trong `docs/BAN_GIAO_PHIEN.md`.

## 0. Tóm tắt

| # | Hạng mục | Kết quả | Commit |
|---|---|---|---|
| 1 | Lộ Trình Cảnh Giới | Tab **Cảnh Giới** trong Nhiệm Vụ: 429 mốc (đột phá đại cảnh giới + Sơ / Trung / Hậu kỳ / Viên Mãn), nút **Nhận tất cả** | `2016dc8`, `21c26fb` |
| 2 | Trang Nhiệm Vụ gọn lại | Thành Tựu 1 dòng/nhóm; Cơ Duyên 3 dòng + "Xem thêm"; Chính Tuyến chỉ chương đang làm + 1 chương kế; phân trang Nhiệm Vụ NPC `1 … 5 6 7 … 40` | `21c26fb`, `09dd51b`, `ba49b70` |
| 3 | Ma Thạch boss | Chặn khi người chơi cao hơn boss ≥ 35 cấp (như Linh Thạch) — hết quét boss cấp thấp lấy MT | `ba49b70` |
| 4 | Thể lực | Tối đa **100 + 1/cấp**, hồi **1 điểm/phút**; trừ theo thế võ + phẩm võ công | `9bba382` |
| 5 | Thương Điếm | Chỉ bán trang bị bậc ≤ 30 (trước bán mọi bậc giá phẩm × 100 LT — món bậc 4000 = 300 LT) | `2a6c35b` |
| 6 | Nguồn trang bị | Bậc ≥ 60 đủ 15 ô có nguồn: quái rơi 9 ô thường, boss rơi 6 ô quý → săn đem lên Phường Thị | `6e54305` |
| 7 | Ô thường cộng đột phá / tu luyện | Số nguyên, nhỏ hơn ô quý: `max(1, ô quý // 3)` từ bậc 60 | `6e54305` |
| 8 | Mô phỏng kinh tế | `tools/sim/econ_sim.py` — người chơi mới 30 ngày, 3 kiểu chơi | (đợt này) |

## 1. Lộ Trình Cảnh Giới

- **Mốc:** mỗi đại cảnh giới (50 cấp) có 5 mốc. Đột phá vào là tầng 1, áp dụng từ cảnh giới thứ 2. Bốn mốc còn lại là Sơ kỳ (tầng 10), Trung kỳ (20), Hậu kỳ (30), Viên Mãn (40). Tổng cộng 429 mốc, từ CG10 tới CG4290.
- **Thưởng:** tính theo cấp của mốc, không theo cấp hiện tại của người chơi. Cấu hình ở `class/config/canhgioi_reward.json`.

| Mốc | Tu vi | Linh Thạch | Ma Thạch |
|---|---|---|---|
| Đột phá đại cảnh giới | 15% thanh cấp | 10 × cấp × (1 + cấp/100) | 10 × (cấp/10 + 3), bằng 10 lần hạ boss |
| Sơ / Trung / Hậu kỳ / Viên Mãn | 5% | 2 × cấp × (1 + cấp/100) | 0 |

- **Lưu "đã nhận":** dùng bảng có sẵn `player_nhiemvu_achievements`, mã `CG` + cấp. `INSERT IGNORE` trên khóa chính bảo đảm mỗi mốc chỉ nhận được 1 lần. Không cần bảng mới.
- **Hàm:** `nhiemvu_realm_*` trong `class/function/nhiemvu.php`.
- **Chấm đỏ:** nút Nhiệm Vụ có chấm đỏ khi còn mốc chưa nhận (`nav_quest_dot`).
- **Test:** `tests/test_canhgioi.php`.

## 2. Thể lực (`class/config/wugong_skill_config.json`)

| | Giá trị |
|---|---|
| Thể lực tối đa | `stamina_cap()` = cột `umaxstamina` (gốc 100) + (cấp − 1). Cột DB giữ nguyên 100; SQL tương ứng `umaxstamina + ulv - 1` |
| Hồi | `stamina_interval_sec()` = 60 giây/điểm (Thiên Phú vẫn rút ngắn, sàn 30 giây) |
| Đòn thường | 1 (đánh boss: 0) |
| Tuyệt Kỹ | 3 + hạng phẩm võ công (Nhập Môn 0 … Đại Đạo 7) → 3 … 10 |
| Tuyệt Chiêu | 6 + 2 × hạng phẩm → 6 … 20 |
| Kỹ năng bí kíp | 3 (kiểm thể lực trước khi tiêu bí kíp) |
| Kỹ năng Linh Căn | bậc 2 / 3 / 4 = 5 / 8 / 12 (kiểm trước khi trừ máu) |

- **Công pháp:** chỉ có hiệu ứng bị động, không có chiêu bấm, nên không trừ thể lực.
- **File:** `combat_stamina_cost()` (`class/function/combat.php`), `game/pve.php`, `game/boss.php`, `game/wugong.php`, `game.php`, `cron/stamina_recovery.php`, `class/function/online_reward.php`.
- **Test:** `tests/test_stamina.php`, `tests/test_combat_moves.php`.

## 3. Trang bị

### 3.1 Thương Điếm (`game/shangdian.php`)
- **Phạm vi bán:** chỉ trang bị bậc ≤ `TCG_SHOP_ZB_MAX_LV` (30), gồm Phàm, Linh, Huyền. Chặn cả danh sách hiển thị lẫn lệnh mua.
- **Bậc cao:** phải săn, chế tạo, hoặc mua của người chơi khác ở **Phường Thị** (giao dịch bằng LT, thuế 10% của người bán, `game/fangshi.php`).

### 3.2 Nguồn rơi bậc ≥ 60 (`tools/gen/drop_cover_build.py`)

| Nhóm | Ô | Nguồn |
|---|---|---|
| Thường | 1 Vũ Khí, 2 Thiên Thuẫn, 3 Thiên Quan · 4 Giáp Bào, 5 Hộ Thủ, 6 Giới Chỉ · 7 Yêu Đai, 12 Vai Khôi, 15 Tọa Kỵ | **Quái** cùng bậc, chia 3 nhóm theo thứ tự (cấp, mã); mỗi nhóm rơi 3 ô |
| Quý (tu luyện) | 8 Phi Phong, 13 Linh Đai, 14 Linh Bội | **Chỉ boss** |
| Quý (đột phá) | 9 Linh Phù, 10 Ngọc Nhãn, 11 Cổ Ngọc | **Chỉ boss** |

- **Tỉ lệ rơi giữ nguyên:** quái khoảng 17–24% mỗi con (`dljv`/120); boss khoảng 12,5% mỗi lần hạ (`dljv` 15/120).
- **Thứ tự chạy:** bộ sinh phải chạy **sau** mọi bộ sinh khác (`dot_b_build`, `dot_f_build`, `lan*_build`). Nó ghi `guaiwu_config.json` (`gzb`) và `sql/dot_b_data.json` + `sql/lan*_data.json` (`bosszb`), nhờ đó chạy lại `migrate_lan* --apply` không ghi đè.
- **Boss trong DB:** `sql/migrate_drop_cover.php --dry-run | --apply`, chạy lại nhiều lần vẫn cho cùng kết quả.

### 3.3 Ô thường cộng đột phá / tu luyện (`tools/gen/dot_f_build.py`, `normal_pct`)

| Bậc | Ô thường (đột phá / tu luyện) | Ô quý (đột phá / tu luyện) | Đột phá đủ bộ 15 ô |
|---|---|---|---|
| 1–30 | 0 / 0 | +0…1 / +1…2 | — |
| 60 | +1 / +1 | +2 / +4 | **+15%** |
| 600 | +1 / +3 | +5 / +10 | **+24%** |
| 1000 | +2 / +4 | +6 / +12 | **+36%** |
| 3500 | +3 / +6 | +9 / +18 | **+54%** |

- **Trần tu luyện:** tổng tu luyện vẫn có trần 30% (`game/xiulian.php`).
- **Không cần migrate đồ cũ:** đồ người chơi đang có đọc chỉ số này từ config (`merge_player_zhuangbei_with_config`).
- **Quyết định của chủ dự án:** giữ mức này, chấp nhận lên cấp nhanh hơn khoảng 25%, coi như thưởng cho người chịu săn đủ bộ.

**Test:** `tests/test_drop_cover.php`. Test này kiểm đủ 15 ô có nguồn, quái không rơi ô quý, boss chỉ rơi ô quý, ô thường nhỏ hơn ô quý, và tiệm chặn bậc > 30.

## 4. Ma Thạch từ boss (`game/boss.php`)

`$sj = 0` khi `$lvc >= 35`, cùng điều kiện với Linh Thạch.

Trước khi sửa, người cấp cao quét boss cấp thấp (chết trong 1 đòn, 3–10 phút hồi một lần) được khoảng **96.000 MT/tháng**, tương đương 96.000 VNĐ. Sau khi sửa, mô phỏng còn khoảng 12.000 MT/tháng với người cày.

## 5. Phát hiện kinh tế (đọc từ code, đã kiểm thật)

| Phát hiện | Chi tiết | Trạng thái |
|---|---|---|
| Tiệm bán mọi trang bị siêu rẻ | `zhuangbei_config_all()`, giá phẩm × 100 LT; đã mua thử món bậc 4000 với giá 300 LT | **Đã sửa** (mục 3) |
| 9/15 ô bậc ≥ 60 không có nguồn | Quái chỉ rơi 5 ô; ô 2, 3, 5, 10, 11, 12–15 không có ở đâu | **Đã sửa** (mục 3) |
| MT boss không chặn chênh lệch cấp | `boss.php:554` | **Đã sửa** (mục 4) |
| Thể lực 100, 5/đòn, 5 phút/điểm | Chỉ khoảng 11 quái cùng cấp/ngày; đánh quái chỉ chiếm 1–4% tu vi | **Đã sửa** (mục 2) |
| Linh căn rẻ | Lên cấp 90 cả 5 hệ khoảng 425.000 LT (đá mua tiệm 50 LT) — đầy trong tuần đầu | Mở |
| Cường hóa | +30 trong tuần đầu | Mở |
| Kinh nghiệm công pháp | 2% tu vi mỗi quái; cấp 10 cần khoảng 4.300 kinh nghiệm cộng dồn ⇒ đầy sau vài chục quái | Mở |
| Thương Điếm nhận MT bằng đúng số LT | Đạo cụ giá X LT cũng bán X MT (1 MT = 1 VNĐ) | Mở — người chơi chịu thiệt, không phải lỗ hổng |
| Hầm Mỏ, Luyện Đan, Chế Tạo | Không tạo thu nhập đáng kể; đan cộng chỉ số vĩnh viễn kèm độc tính | Ghi nhận |

## 6. Mô phỏng kinh tế (`tools/sim/econ_sim.py`)

### 6.1 Phương pháp
- **Công thức:** lấy từ code.
  - Thanh cấp: (cấp+1)² × 110.
  - Tu luyện thường: tu vi/giây = cấp, mỗi lần tối đa 30 phút. Tu luyện bằng LT: ×2, tối đa 180 phút.
  - Quái cho 107 × cấp tu vi (cấp ≤ 500).
  - Boss: Linh Thạch 90 × cấp + khoảng 1.330, Ma Thạch cấp/10 + 3, hồi sinh 10 phút.
  - Đột phá: theo `tupo.php`, kể cả xác suất tụt cấp.
  - Nhiệm vụ ngày / tuần / tháng, chính tuyến, trực tuyến, bế quan, Lộ Trình Cảnh Giới, Tỏa Yêu Tháp.
  - Săn đồ theo tỉ lệ rơi, linh sủng ở Sào Thú, võ công và công pháp lên cấp theo quái.
  - Chi tiêu: linh căn, thiên phú, huyết mạch, thể chất, cường hóa.
- **Giả định:**
  - F2P, mỗi đòn mất 1,5 giây, quái cùng cấp 5,5 đòn, boss cùng cấp 50 đòn.
  - Làm đủ nhiệm vụ; luôn giữ quỹ đủ cho khoảng 20 lần đột phá.
  - **Máy chủ chỉ 1 người** (không ai tranh boss), không mua bán ở Phường Thị.
  - Chưa tính đồ tốt hơn làm đánh nhanh hơn.
- **Cách chạy:** 30 lần cho mỗi kịch bản, lấy trung bình.

### 6.2 Kết quả (tự săn đồ, luật hiện tại)

| | Nhẹ 2h/ngày | Vừa 5h/ngày | Cày 10h/ngày |
|---|---|---|---|
| Ngày 7 | cấp 47 | cấp 77 | cấp 110 |
| Ngày 14 | cấp 79 | cấp 119 | cấp 143 |
| **Ngày 30** | **cấp 130** | **cấp 148** | **cấp 195** (180–218) |
| Tỉ lệ đột phá ngày 30 | 22% | 22% | 18% |
| Trang bị cộng (đột phá / tu luyện) | +15% / +21% | +15% / +21% | +15% / +22% |
| Số ô của bậc hiện tại đã có | 8,6/15 | 9,8/15 | 14,7/15 |
| Ma Thạch miễn phí còn ở ngày 30 | ~5.600 | ~7.800 | ~15.700 |
| Tụt cấp khi đột phá / 30 ngày | 2,2 | 2,9 | 4,3 |

Các số khác ở ngày 30 (lần chạy trước khi thêm săn đồ, người cày):
- **Linh Thạch:** thu khoảng 12,6 triệu, trong đó boss khoảng 67%, quái 24%, nhiệm vụ 7%.
- **Tu vi:** tu luyện 73%, quái 15%, nhiệm vụ 11%, tháp 1%.
- **Số lượng:** khoảng 145 quái/ngày, tháp tầng 142.
- **Tính năng:** linh căn đầy cấp 90 (bậc 4), thiên phú phẩm 5 cấp 30, huyết mạch phẩm 5, thể chất phẩm khoảng 5, cường hóa +30, võ công cấp 44.
- **Linh sủng:** phẩm 4 (Hoàn Mỹ, kỳ vọng khoảng 50.000 LT + 750 MT), cấp 84.

### 6.3 Nhận xét
- **Boss chiếm khoảng 70% Linh Thạch.** Trên máy chủ đông người, boss dùng chung, nên Linh Thạch và cấp thực tế sẽ thấp hơn bảng trên.
- **Ô quý là hàng hiếm thật.** Cần khoảng 120–150 lần hạ boss mới đủ 6 ô quý của một bậc. Ở cấp 120–149 chỉ có boss cấp 92 trong tầm, nên ô quý vẫn là bậc 60.
- **Linh căn, cường hóa, công pháp đầy quá nhanh** so với khung 4.300 cấp (mục 5 — còn mở).

## 7. Triển khai (đã chạy trên máy chủ thật 2026-10-28)

```
cd /d D:\XXJserver\phpstudy_pro\WWW
cron\capnhat_code.bat
"D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup_truoc_drop.sql
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\migrate_drop_cover.php --apply
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" tests\test_drop_cover.php
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" tests\test_stamina.php
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" tests\test_combat_moves.php
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" tests\test_canhgioi.php
```

**Kết quả trên máy chủ thật:**
- `migrate_drop_cover.php --apply`: 87 boss được cập nhật.
- Các test `test_drop_cover`, `test_stamina`, `test_combat_moves`, `test_canhgioi`: đều ALL PASS.

Lộ Trình Cảnh Giới, thể lực, Ma Thạch boss và Thương Điếm không cần migrate, chỉ cần `git pull` rồi Ctrl+F5.

## 8. Việc còn mở

1. **Bảo mật:**
   - Mật khẩu MySQL root đã nhiều lần nằm trong log chat (gõ ngay sau `-p`) ⇒ **đổi mật khẩu**, sửa `pdo.php` trên máy chủ (không commit).
   - `tests/e2e_fixture.php` và `tests/test_linhcan_*.php` có mật khẩu viết cứng ⇒ nên đọc từ `pdo.php`.
   - Repo phải Private.
2. **Lỗi nhỏ:** `game/boss.php:72` tính thời gian hồi sinh bằng `% 86400`. Nếu boss bị hạ cách đây hơn 24 giờ, sẽ có một khoảng ngắn bị báo "chưa hồi sinh" sai.
3. **Tỏa Yêu Tháp:** reset tuần chỉ chạy nếu người chơi mở tháp đúng ngày thứ Hai (`toayeuthap.php:24`). Chưa kiểm thật.
4. **Cân bằng:** linh căn, cường hóa, kinh nghiệm công pháp quá dễ đầy (mục 5).
5. **Theo dõi khi có người chơi:** giá ô quý ở Phường Thị (rẻ quá thì hạ `dljv` boss) và nhịp lên cấp thực tế so với mô phỏng.
