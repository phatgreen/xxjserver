# KHUNG TỔNG NỘI DUNG CẤP 500 → 4300 (CHỜ DUYỆT)

> Lập 2026-10-28 — phương án C (chủ dự án chọn): **làm khung tổng một lần**, sau đó **sinh dữ liệu từng làn** bằng một bộ công cụ chung.
> Mọi bảng số in bằng `python3 tools/gen/khung_tong_design.py` (sửa công thức ở đó rồi chạy lại, không gõ tay).
> Thay thế bản `LAN2_CAP_500_1000.md` (làn 2 trở thành làn đầu tiên dùng khung này).

## 0. Tóm tắt

| # | Hạng mục | Vấn đề hiện tại | Khung đề xuất |
|---|---|---|---|
| 1 | Giới hạn số | PHP 5.6 trên Windows dùng **số nguyên 32-bit** (tối đa 2.147.483.647, kể cả bản 64-bit); cột MySQL `INT` cùng giới hạn | Mọi giá trị ở cấp 4300 **≤ 1 tỉ** (trần an toàn ~47%) ⇒ **không cần** đổi BIGINT, không rà toàn bộ `intval()` |
| 2 | Kinh nghiệm | Giữ công thức cũ: cấp 4300 cần **2,03 tỉ** EXP (95% giới hạn), **4.420 quái/cấp**, tổng **9,4 triệu quái** | Từ cấp 500: EXP lên cấp tăng chậm lại, EXP quái tăng nhanh hơn ⇒ **514 → 300 quái/cấp**, tổng **1,38 triệu**; EXP lớn nhất 364 triệu |
| 3 | Đột phá | Tỉ lệ chạm sàn **5% từ cấp ~300**, phí `cấp×(cấp+1)` ⇒ cấp 1000 tốn TB **20 triệu LT/cấp**, cấp 4300 **370 triệu/cấp** | Sàn tỉ lệ theo loại đột phá + phí tăng chậm (mục 4) |
| 4 | Sức mạnh | Đường chỉ số đợt F mới tới 800 | Kéo dài tới 4300; quái/boss tính ngược từ nhân vật chuẩn (giữ nhịp 5,5 đòn / 50 đòn) |
| 5 | Trang bị | Bậc cao nhất 800, phẩm cao nhất "Đạo" | Thêm 8 bậc (1000 → 4000) + 4 phẩm mới |
| 6 | Võ công / Công pháp | Võ công tới CG 30 (cấp 1450), công pháp tới CG 43 (cấp 2100) | Mỗi làn thêm 5 võ công + 4 công pháp; thêm 2 phẩm võ công trên Tiên Đạo |
| 7 | Cảnh giới | 74 màu / 86 cảnh giới; lỗi tên "Sáng Thê", "Thánh Nhân" trùng (1101 & 3851), khoảng trắng thừa | Thêm 12 màu, sửa tên |
| 8 | Nội dung | Hết ở cấp 500 | 8 làn × ~10 khu: **~76 khu, ~600 map, ~1.300 loại quái, 76 boss, 76 chương chính tuyến** — sinh bằng công cụ chung |

## 1. Nguyên tắc

1. **Không đổi gì ở cấp 1–500** (nội dung đã chơi được, đã cân ở đợt B/C/F). Công thức mới chỉ áp dụng khi cấp > 500, nối liền tại 500.
2. **Mỗi cảnh giới (50 cấp) = 1 khu** có: quái luyện cấp, 1 boss, 1 NPC, 1 chương chính tuyến; mỗi làn có võ công/công pháp/trang bị mới.
3. **Một bộ công cụ cho mọi làn**: làn mới chỉ thêm 1 file mô tả (tên khu, boss, quái), không viết lại code sinh.
4. **Cân theo nhân vật chưa có buff %** (công pháp, cộng hưởng, linh căn, huyết mạch, thiên phú…) ⇒ các hệ phụ là phần thưởng thật, không bắt buộc.
5. Mỗi làn: sinh → migrate (chạy lại không trùng) → test số liệu → **đánh thật + chụp màn hình** → bàn giao.

## 2. Ràng buộc kỹ thuật: giới hạn số nguyên

| Nơi | Giới hạn | Hệ quả nếu vượt |
|---|---|---|
| PHP 5.6 Windows (`PHP_INT_MAX`) | 2.147.483.647 — số nguyên 64-bit trên Windows chỉ có từ PHP 7 | `intval()` trả sai, cộng/trừ thành số thực ⇒ sai so sánh, lỗi hiển thị |
| MySQL `INT` (cột `uexp`, `umaxhp`, `ugj`, `ufy`, `uyxb`, `uczb` của `game1`) | 2.147.483.647 | `sql_mode=''` (máy chủ đang dùng) **cắt im lặng** về 2.147.483.647 ⇒ mất tiền/exp không báo |
| Bảng `boss` (máu/công lưu `VARCHAR`) | đọc bằng `intval` ⇒ như PHP | như trên |

**Quyết định:** trần thiết kế **1.000.000.000** cho mọi giá trị lưu/tính (kể cả sau buff, cường hóa +30, nhân sát thương thế võ).
Kiểm tra trước khi làm (chạy trên máy chủ): `php.exe -r "echo PHP_INT_SIZE, ' ', PHP_INT_MAX;"` — kỳ vọng `4 2147483647`.

Đo bằng `khung_tong_design.py`: giá trị nhân vật lớn nhất ở 4300 = **61 triệu** (6% trần); máu boss lớn nhất **448 triệu** (45%); EXP lên cấp lớn nhất **364 triệu** (36%).
**Ngoại lệ phải xử lý — Linh Thạch tích lũy** (mục 9): tiền là thứ duy nhất cộng dồn không giới hạn.

## 3. Đường kinh nghiệm

Công thức (chỉ cấp > 500; cấp ≤ 500 giữ nguyên):

```
EXP lên cấp  = 27.500.000 × (cấp/500)^1,2     (cũ: 110 × cấp²)      — class/player.php (umaxexp)
EXP mỗi quái = 53.500 × (cấp quái/500)^1,45  (cũ: 107 × cấp quái)    — game/nowmid.php (lúc sinh quái)
```

| Cấp | EXP lên cấp (cũ) | EXP lên cấp (mới) | EXP/quái (cũ) | EXP/quái (mới) | Quái/cấp cũ | Quái/cấp mới |
|---|---|---|---|---|---|---|
| 500 | 27.500.000 | 27.500.000 | 53.500 | 53.500 | 514 | 514 |
| 750 | 61.875.000 | 44.734.461 | 80.250 | 96.313 | 771 | 464 |
| 1000 | 110.000.000 | 63.178.410 | 107.000 | 146.166 | 1028 | 432 |
| 1500 | 247.500.000 | 102.772.803 | 160.500 | 263.136 | 1542 | 390 |
| 2000 | 440.000.000 | 145.145.870 | 214.000 | 399.338 | 2056 | 363 |
| 2500 | 687.500.000 | 189.712.828 | 267.500 | 551.900 | 2570 | 343 |
| 3000 | 990.000.000 | 236.109.898 | 321.000 | 718.908 | 3084 | 328 |
| 3500 | 1.347.500.000 | 284.086.334 | 374.500 | 898.972 | 3598 | 316 |
| 4000 | 1.760.000.000 | 333.457.645 | 428.000 | 1.091.024 | 4112 | 305 |
| 4300 | 2.033.900.000 | 363.689.561 | 460.100 | 1.211.648 | 4420 | 300 |

Tổng quái 500 → 4300: **cũ 9.373.748 · mới 1.378.351** (chưa tính nhiệm vụ, Bế Quan, tu luyện).

| Làn | 500–1000 | 1000–1500 | 1500–2000 | 2000–2500 | 2500–3000 | 3000–3500 | 3500–4000 | 4000–4300 |
|---|---|---|---|---|---|---|---|---|
| Quái cần | 233.677 | 204.845 | 188.117 | 176.584 | 167.906 | 161.017 | 155.346 | 90.860 |

Ghi chú: thưởng nhiệm vụ đợt C tính theo **% thanh cấp** ⇒ tự co giãn, không cần sửa. Quái cấp ≤ 500 và người chơi ≤ 500 không bị ảnh hưởng.

## 4. Đột phá (lên cấp)

Hiện tại (`game/tupo.php`): `tỉ lệ = 75 − 0,6×cấp − 3×cảnh giới (+10 ở mốc ×50, + trang bị, + thiên phú)`, **sàn 5%**;
phí Linh Thạch: tiểu cảnh `cấp×(cấp+1)`, đại cảnh giới `cấp²×3`. Thất bại: mất phí; 1% tụt 1 cấp, 1% mất hết EXP, 1% mất 75%, 1% mất 50%.

| Cấp | Tỉ lệ | Phí / lần | Trung bình / cấp |
|---|---|---|---|
| 100 | 6% | 10.100 | 168.333 |
| 300 | 5% | 90.300 | 1.806.000 |
| 500 | 5% | 250.500 | 5.010.000 |
| 1000 | 5% | 1.001.000 | 20.020.000 |
| 2000 | 5% | 4.002.000 | 80.040.000 |
| 4300 | 5% | 18.494.300 | **369.886.000** |

⇒ Từ cấp ~300 mọi lần đột phá đều 5% (20 lần thử/cấp, 4% mỗi lần dính phạt nặng), chi phí tăng theo bình phương.
Ở 4300 một cấp tốn ~370 triệu LT — vượt xa thu nhập và dễ chạm trần ví.

**Đề xuất (chỉ cấp > 500):**

| Mục | Đề xuất |
|---|---|
| Sàn tỉ lệ | Tiểu cảnh **25%**, đại cảnh giới (mốc ×50) **12%** — vẫn cộng trang bị / thiên phú như cũ, trần 99% |
| Phí | `250.000 × (cấp/500)^1,2` (tiểu cảnh), ×3 cho đại cảnh giới ⇒ cấp 4300 ≈ 3,3 triệu/lần |
| Phạt thất bại | Giữ nguyên tỉ lệ 1%/1%/1%/1% (đặc trưng "tâm ma") |

Trung bình / cấp sau sửa: 1000 ≈ 2,3 triệu · 2000 ≈ 5,3 triệu · 4300 ≈ 13 triệu LT (so với 20 / 80 / 370 triệu).
**Câu hỏi K4:** có muốn áp dụng luôn cho cấp 300–500 (đang 5%) không?

## 5. Sức mạnh nhân vật / quái / boss

Nhân vật chuẩn = chỉ số gốc (đột phá đợt F) + đồ đủ 15 ô của bậc ≤ cấp (50% gốc ở bậc) cường hóa tăng dần +10 → +30,
nhân hệ số buff % tăng dần ×1,0 (cấp 500) → ×2,5 (cấp 4300) — **ước lượng trên** để kiểm tra trần số.

Quái Thường (cân theo nhân vật **chưa buff**): người hạ quái **~5,5 đòn**, quái hạ người **~50 đòn** (cùng công thức sát thương `sim_power.py`).
Kiểu quái giữ hệ số đợt B: Giáp 1,2/0,85/1,3 · Cuồng 0,85/1,25/0,8 · Tinh Anh 2/1,2/1,1. Boss khu vực: máu ×12, công ×1,6, thủ ×1,3.

| Cấp | Cường hóa | Buff % | Công | Thủ | Máu | Quái HP | Quái Công | Quái Thủ | Boss HP | Boss HP / trần |
|---|---|---|---|---|---|---|---|---|---|---|
| 500 | +10 | ×1.00 | 110.499 | 135.137 | 335.272 | 425.422 | 108.058 | 44.200 | 5.105.065 | 1% |
| 750 | +11 | ×1.10 | 232.262 | 283.106 | 730.191 | 813.889 | 206.550 | 84.560 | 9.766.668 | 1% |
| 1000 | +12 | ×1.20 | 554.901 | 675.674 | 1.756.165 | 1.784.220 | 452.558 | 185.374 | 21.410.644 | 2% |
| 1500 | +15 | ×1.39 | 1.500.961 | 1.834.618 | 4.505.822 | 4.143.219 | 1.051.152 | 430.464 | 49.718.627 | 5% |
| 2000 | +17 | ×1.59 | 3.091.979 | 3.792.703 | 8.830.848 | 7.476.967 | 1.897.578 | 776.828 | 89.723.604 | 9% |
| 2500 | +20 | ×1.79 | 5.635.576 | 6.931.502 | 15.469.713 | 12.124.775 | 3.078.012 | 1.259.717 | 145.497.302 | 15% |
| 3000 | +23 | ×1.99 | 9.364.645 | 11.541.981 | 24.912.273 | 18.146.325 | 4.607.679 | 1.885.332 | 217.755.899 | 22% |
| 3500 | +25 | ×2.18 | 14.339.656 | 17.702.341 | 37.198.247 | 25.275.803 | 6.419.125 | 2.626.058 | 303.309.641 | 30% |
| 4000 | +28 | ×2.38 | 21.242.919 | 26.258.369 | 53.983.090 | 34.340.763 | 8.722.549 | 3.567.871 | 412.089.154 | 41% |
| 4300 | +30 | ×2.50 | 24.267.688 | 30.005.247 | 61.406.569 | 37.372.240 | 9.492.827 | 3.882.830 | 448.466.877 | 45% |

Sát thương đỉnh (Tuyệt Chiêu ×2,6 × bạo kích +70%) ở 4300 ≈ 107 triệu — trong trần. Mỗi làn chạy lại `sim_power.py` mở rộng tới cấp làn để kiểm chứng.

## 6. Trang bị

| Bậc | Phẩm (mới) | Làn dùng | Ghi chú |
|---|---|---|---|
| 500 · 600 · 800 | Đế · Đế · Đạo (có sẵn) | 500–1000 | Đã có đủ 15 ô |
| **1000 · 1250** | **Hỗn Độn** | 1000–1500 | |
| **1500 · 2000** | **Thái Sơ** | 1500–2500 | |
| **2500 · 3000** | **Vĩnh Hằng** | 2500–3500 | |
| **3500 · 4000** | **Chí Tôn** | 3500–4300 | |

- Mỗi bậc 15 ô, tổng chỉ số = 50% gốc nhân vật ở cấp = bậc (đúng quy tắc đợt F). Sinh bằng `dot_f_build.py` (thêm bậc vào `TIERS`, `RARITY`, `PREFIX`).
- Rơi: quái cấp ≥ bậc rơi đồ bậc đó (tỉ lệ như đợt B), boss rơi cao hơn; công thức chế tạo: cấp yêu cầu = bậc.
- Thần Trang: mỗi phẩm mới 1 bộ 15 món (như đợt F).
- **Câu hỏi K5:** tên 4 phẩm mới.

## 7. Võ công / Công pháp

| Làn | Võ công mới | Công pháp mới | Nguồn |
|---|---|---|---|
| 500–1000 | — (đã có CG 12/15/20) | — | Thiên Cơ Các |
| 1000–1500 | 5 bộ Tuyệt Thế CG 25 (1 bộ/hệ) | 4 bộ | Thiên Cơ Các |
| 1500–2000 | (Tiên Đạo CG 30 đã có — đổi Tàn Quyển, đợt D) | 4 bộ | Boss: Tàn Quyển |
| 2000–2500 | 5 bộ **phẩm 7 "Hỗn Nguyên"** CG 42 | 4 bộ | Boss khu: "Hỗn Nguyên Tàn Quyển" (cơ chế đợt D) |
| 2500–3500 | 5 bộ Hỗn Nguyên CG 55 | 4 bộ / làn | như trên |
| 3500–4300 | 5 bộ **phẩm 8 "Đại Đạo"** CG 72 | 4 bộ / làn | Boss khu: "Đại Đạo Tàn Quyển" |

Sinh bằng `dot_a_design.py` (đã có công thức ngân sách chỉ số theo phẩm + cảnh giới); thế võ 3 bậc + văn mỗi bộ như đợt A.
Cộng Hưởng: thêm mốc khi nhóm đông lên (giữ trần 5% mỗi chỉ số).

## 8. Cảnh giới

- Thêm **12 màu** cho cảnh giới 75–86 (`class/jingjieColor.php`) — hiện cấp ≥ 3701 báo `Undefined array key`.
- Sửa tên (`class/rangesjj.php`): "Sáng Thê" → "Sáng Thế" (2801) · "Thánh Nhân" ở 3851 trùng 1101 → đổi "Thánh Nhân Cảnh"/"Hỗn Độn Thánh Nhân" (**K6**) · bỏ khoảng trắng thừa ("Vô Thượng  Tiên Đế", "Bất Hủ  Tiên Đế", "Đại  Chủ Tể").
- Mốc 4300 "Vô Ngã Viên Mãn — Đại Đạo Đỉnh Phong" đã làm (nhánh `claude/tong-quan-du-an`).

## 9. Kinh tế Linh Thạch

| Vấn đề | Đề xuất |
|---|---|
| Ví `uyxb` cộng dồn không trần; vượt 2,147 tỉ bị MySQL cắt im lặng | Trần ví **1.500.000.000**: `changeyxb` không cộng quá trần, báo "Túi linh thạch đã đầy" |
| Thu nhập cấp cao (boss: `cấp×(1–5)×30`, quái, nhiệm vụ) | Mỗi làn đo thu nhập/giờ trên server thử, so với chi đột phá (mục 4) + cường hóa + mua võ công; mục tiêu chi ≈ 60–80% thu |
| Giá Thiên Cơ Các / Thương Điếm theo cấp | Võ công/công pháp mới định giá theo thu nhập làn (như đợt A) |

## 10. Khu vực, bản đồ, quái, boss — khuôn mỗi cảnh giới

| Thành phần | Khuôn |
|---|---|
| Khu | 1 khu / cảnh giới (50 cấp), `qy` từ 37; ảnh `dt/ditu/<qy>.png` vẽ bằng `draw_region_map.py` |
| Map | **8 map**/khu nối chuỗi (`mup/mdown/mleft/mright`), map 1 nối từ map cuối khu trước; mỗi map 1 bậc cấp (cách ~6 cấp) |
| Quái | 2 loại/map (Thường/Giáp/Cuồng xoay vòng), map cuối thêm Tinh Anh ⇒ ~17 loại/khu; tên = tiền tố khu + đuôi theo kiểu & hệ |
| Boss | 1 boss khu ở map cuối, cấp = cấp cuối cảnh giới; hồi sinh 600 giây (đã sửa lỗi hồi máu ngay) |
| Rơi | Trang bị bậc ≤ cấp · đan đột phá theo cảnh giới (Nguyên Anh → Đại Thừa đã có djid 22–26; từ Độ Kiếp thêm đan mới) · dược · Tàn Quyển ở boss (mục 7) |
| NPC | 1 NPC / khu (giao chương chính tuyến + 2–3 nhiệm vụ khu) |

**Mã số dự trù** (không đụng dữ liệu cũ): quái **300 + …** (≈ 17/khu ⇒ tới ~1.600) · khu `qy` 37–112 · map tự tăng · boss tự tăng
(tra boss theo **cấp**, không theo id — như đợt D).

**Hiệu năng:** `guaiwu_config.json` hiện 283 quái; +1.300 quái ⇒ file lớn ~5×. Đề xuất tách **mỗi làn 1 file** `guaiwu_config_lan<N>.json`,
hàm tra cứu chọn file theo khoảng mã (giữ `static` cache) ⇒ mỗi request chỉ giải mã file cần dùng.

## 11. Nhiệm vụ — khuôn chương mỗi cảnh giới

| Bước | Loại (đã có ở đợt C) |
|---|---|
| 1. Tới khu mới | `reach_region` |
| 2–3. Diệt quái khu (2 dải cấp) | `kill` |
| 4. Thu thập vật phẩm khu | `collect_item` |
| 5. Hạ boss khu | `kill_boss` |
| 6. Đột phá đại cảnh giới | `breakthrough` |

Thưởng theo % thanh cấp như đợt C (`exp_pct`), chương 14 → 89. Sinh bằng `dot_c_build.py` (thêm khuôn chương).
Kết hợp `MO_KHOA_TINH_NANG.md`: chương có bước Cơ Duyên khi trùng mốc mở tính năng.

## 12. Bộ công cụ chung

```
tools/gen/lan/
  spec/lan2.json … lan9.json   # mô tả làn: danh sách khu (tên, hệ chủ đạo, tên boss, NPC, đuôi tên quái)
  design.py                    # công thức chung (mục 3–5): chỉ số quái/boss, EXP, rơi đồ
  build.py <lan>               # sinh: guaiwu_config_lan<N>.json, sql/lan<N>_data.json, chương nhiệm vụ, ảnh bản đồ
sql/migrate_lan.php --lan=N    # ghi mid/qy/boss/npc — chạy lại không trùng, --dry-run / --apply
tests/test_lan_data.php --lan=N  # mỗi khu đủ 8 map nối liền, quái đúng nhịp, boss ≤ trần, nhiệm vụ trỏ đúng map/quái
```

Làn mới = viết 1 file `spec/lanN.json` (tên) → `build.py N` → `migrate_lan.php --lan=N --apply` → test → chụp màn hình.

## 13. Lộ trình

| Đợt | Nội dung | Phụ thuộc |
|---|---|---|
| **KT-0** Nền kỹ thuật | Kiểm `PHP_INT_SIZE` máy chủ · EXP mới (2 chỗ code) · Đột phá mới · trần ví · 12 màu + sửa tên cảnh giới · tách file quái theo làn · bộ công cụ `tools/gen/lan/` | — |
| **L2** 500–1000 | 10 khu (tên đã đề xuất ở `LAN2_CAP_500_1000.md`), Tàn Quyển boss mới | KT-0 |
| **L3** 1000–1500 | 10 khu, bậc đồ 1000/1250 (Hỗn Độn), 5 võ công + 4 công pháp | L2 |
| **L4** 1500–2000 | 10 khu, bậc 1500 | L3 |
| **L5–L9** | 2000 → 4300 (mỗi làn 10 khu; L9 = 6 khu 4000–4300) | lần lượt |

Song song với lộ trình này: **MK-1..3** (mở khóa tính năng) và **UI-1..3** (giao diện) — ưu tiên cao hơn vì ảnh hưởng **người chơi mới**,
trong khi người chơi thật cao nhất hiện mới cấp 93.

## 14. Cần chủ dự án quyết

| # | Câu hỏi | Đề xuất |
|---|---|---|
| K1 | Đường EXP mục 3 (514 → 300 quái/cấp) | Đồng ý |
| K2 | Đột phá mục 4 (sàn 25%/12%, phí tăng chậm) | Đồng ý |
| K3 | Trần ví 1,5 tỉ Linh Thạch | Đồng ý |
| K4 | Áp dụng đột phá mới cho cả cấp 300–500? | Có (hiện 5% là quá khắt) |
| K5 | Tên 4 phẩm trang bị mới | Hỗn Độn · Thái Sơ · Vĩnh Hằng · Chí Tôn |
| K6 | Đổi tên "Thánh Nhân" trùng ở cấp 3851 | "Hỗn Độn Thánh Nhân" |
| K7 | Thứ tự ưu tiên | MK + UI-1 trước → KT-0 → L2 → … |
