# KHUNG TỔNG NỘI DUNG CẤP 500 → 4300 (CHỜ DUYỆT)

> Lập 2026-10-28 — phương án C (chủ dự án chọn): **làm khung tổng một lần**, sau đó **sinh dữ liệu từng làn** bằng một bộ công cụ chung.
> Bảng số mục 5 in bằng `python3 tools/gen/khung_tong_design.py` (sửa công thức ở đó rồi chạy lại, không gõ tay).
> Thay thế bản `LAN2_CAP_500_1000.md` (làn 2 trở thành làn đầu tiên dùng khung này).

## 0. Tóm tắt (bản 2 — theo phản hồi chủ dự án 2026-10-28)

| # | Hạng mục | Quyết định |
|---|---|---|
| 1 | Giới hạn số | **Lên PHP 7.4 bản 64-bit + đổi cột chỉ số sang `BIGINT`** (chủ dự án chọn) ⇒ giới hạn ~9,2 tỉ tỉ. Máy chủ hiện `PHP_INT_SIZE = 4` (đã kiểm). Làm ở đợt **KT-0**, test toàn bộ game trên PHP 7.4 trước khi chuyển máy chủ thật |
| 2 | Kinh nghiệm | **Giữ công thức EXP lên cấp cũ** (`110 × cấp²`). Quái cấp cao **cho nhiều EXP hơn** để số quái mỗi cấp **không phình** (≈ 400 con/cấp từ cấp 500) — độ khó tăng nhờ **quái mạnh hơn** + đột phá khó hơn, không nhờ bắt giết hàng nghìn con/cấp |
| 3 | Đột phá | Giữ 6 mục cộng/trừ hiện có (Cơ bản · Cấp độ · Cảnh giới · Milestone · Trang bị · Thiên Phú) + thêm Đan và Sự kiện; **tỉ lệ gốc càng cao càng âm** nhưng ở mức mà các khoản cộng **có tác dụng thật** (hiện từ cấp ~125 tỉ lệ gốc âm sâu, mọi khoản cộng bị nuốt, luôn dừng ở sàn 5%) |
| 4 | Bí Cảnh Luyện Cấp | Map luyện cấp dày quái cho người chơi cấp cao, có điều kiện vào + **giới hạn bằng Thể Lực** để không tăng cấp quá nhanh (mục 4b) |
| 5 | Sức mạnh | Kéo đường chỉ số đợt F tới 4300; quái/boss tính ngược từ nhân vật chuẩn (giữ nhịp 5,5 đòn / 50 đòn) |
| 6 | Trang bị | Thêm 8 bậc (1000 → 4000) + 4 phẩm mới; gắn chỉ số **cộng tỉ lệ đột phá** (`tuposjs`) cho trang bị (hiện mọi món = 0) |
| 7 | Võ công / Công pháp | Mỗi làn thêm 5 võ công + 4 công pháp; thêm 2 phẩm võ công trên Tiên Đạo |
| 8 | Cảnh giới | Thêm 12 màu; sửa tên "Sáng Thê", "Thánh Nhân" trùng (1101 & 3851), khoảng trắng thừa |
| 9 | Nội dung | 8 làn × ~10 khu: ~76 khu, ~600 map, ~1.300 loại quái, 76 boss, 76 chương chính tuyến — sinh bằng công cụ chung |

## 1. Nguyên tắc

1. **Không đổi nội dung cấp 1–500** (đã cân ở đợt B/C/F) — ngoại trừ công thức tỉ lệ đột phá (mục 4) vì đang lỗi từ cấp ~125.
2. **Mỗi cảnh giới (50 cấp) = 1 khu** có: quái luyện cấp, 1 boss, 1 NPC, 1 chương chính tuyến; mỗi làn có võ công/công pháp/trang bị mới.
3. **Càng lên cao càng khó** nhờ: quái mạnh hơn (cần đồ/kỹ năng mới), đột phá khó hơn (cần gom khoản cộng), tài nguyên đắt hơn — **không** nhờ nhân số quái phải giết.
4. **Một bộ công cụ cho mọi làn**: làn mới chỉ thêm 1 file mô tả (tên khu, boss, quái).
5. Cân theo nhân vật **chưa có buff %** ⇒ các hệ phụ là phần thưởng thật.
6. Mỗi làn: sinh → migrate (chạy lại không trùng) → test số liệu → **đánh thật + chụp màn hình** → bàn giao.

## 2. Nâng nền tảng: PHP 7.4 64-bit + BIGINT

| Hạng mục | Việc | Ghi chú |
|---|---|---|
| PHP | phpStudy chuyển site sang **PHP 7.4 (x64)** ⇒ `PHP_INT_SIZE = 8` | Kiểm: `php.exe -r "echo PHP_INT_SIZE;"` phải ra `8` |
| Tương thích code | Chạy toàn bộ game + 40 bộ test trên PHP 7.4 ở cloud trước | Server thử đã chạy game trên **PHP 8.3** chỉ với 1 chỗ vá `parse_str` 1 tham số (PHP 7.4 vẫn chấp nhận, chỉ báo deprecated) ⇒ khả năng tương thích cao. Rà thêm: `each()`, `create_function`, `{}` lấy ký tự chuỗi, `mysql_*` |
| DB | `ALTER TABLE game1` các cột `uexp, umaxhp, uhp, ugj, ufy, uyxb, uczb` → `BIGINT`; rà các bảng khác giữ chỉ số/tiền (trang bị, thú cưng, tông môn, xếp hạng) | `sql/migrate_kt0_bigint.php` có `--dry-run` / `--apply`; sao lưu trước |
| Code | `intval()` trên PHP 7.4 64-bit trả đúng tới 9,2 tỉ tỉ ⇒ **không phải sửa 2.934 chỗ `intval`** | Hiển thị số lớn: dùng hàm rút gọn sẵn có (K/M) — thêm đơn vị **B** (tỉ) |
| Quy ước dự án | Cập nhật: backend **PHP 7.4** (thay PHP 5.6) | Code mới vẫn viết tránh tính năng PHP 8 |

## 3. Kinh nghiệm

Nguyên tắc chủ dự án: *người chơi lên cấp ⇒ có quái tương ứng, quái khó hơn đồng thời cho nhiều EXP hơn* ⇒ không cần giết hàng triệu con.

```
EXP lên cấp  = 110 × cấp²                             (giữ nguyên)
EXP mỗi quái = 107 × cấp quái                         (cấp ≤ 500, giữ nguyên)
             = 110 × cấp quái² ÷ 400                  (cấp > 500 — mới, game/nowmid.php lúc sinh quái)
```

| Cấp | EXP lên cấp | EXP/quái cũ | EXP/quái mới | Quái/cấp cũ | Quái/cấp mới |
|---|---|---|---|---|---|
| 500 | 27.500.000 | 53.500 | 53.500 | 514 | 514 |
| 1000 | 110.000.000 | 107.000 | 275.000 | 1.028 | 400 |
| 2000 | 440.000.000 | 214.000 | 1.100.000 | 2.056 | 400 |
| 3000 | 990.000.000 | 321.000 | 2.475.000 | 3.084 | 400 |
| 4300 | 2.033.900.000 | 460.100 | 5.084.750 | 4.420 | 400 |

- 400 con/cấp là **nếu chỉ đánh quái thường**. Thực tế EXP còn từ nhiệm vụ (thưởng theo % thanh cấp), Bế Quan, tu luyện, Bí Cảnh ⇒ số quái thực tế ít hơn.
- Đánh quái **thấp cấp hơn** mình: EXP bị chia theo chênh lệch cấp (`pve.php`, có sẵn) ⇒ phải đi map đúng cấp.
- Hằng số `KILLS = 400` nằm trong `tools/gen/khung_tong_design.py` (và config khi làm) — đổi được.

## 4. Đột phá

### 4.1 Hiện tại (đúng như trang Đột Phá đang hiện)

`Tỉ lệ = Cơ bản 75 + Cấp độ (−0,6 × cấp) + Cảnh giới (−3 × bậc cảnh giới) + Milestone (+10 ở mốc ×50) + Trang bị + Thiên Phú`, sàn 5%, trần 99%.

| Cấp | Cơ bản | Cấp độ | Cảnh giới | Gốc | Ghi chú |
|---|---|---|---|---|---|
| 93 | 75 | −55,8 | −6 | **13,2%** | đúng như ảnh chủ dự án gửi |
| 125 | 75 | −75 | −9 | −9% | từ đây gốc âm |
| 500 | 75 | −300 | −33 | **−258%** | cộng bao nhiêu cũng chỉ ra 5% |
| 4300 | 75 | −2.580 | −258 | **−2.763%** | |

Thêm 2 phát hiện: **mọi trang bị có `tuposjs` = 0** (mục Trang bị luôn +0%) · **7 Đột Phá Đan (djid 20–26) chưa có tác dụng** (không có code dùng).

### 4.2 Đề xuất

Giữ cấu trúc 6 mục (+ 2 mục mới: **Đan**, **Sự kiện**). Đổi 2 mục trừ sao cho **gốc càng cao càng âm, đều đặn**, và các khoản cộng có thể kéo lên:

| Cấp | Gốc mục tiêu (Cơ bản + Cấp độ + Cảnh giới) | Người không gom gì | Người gom đủ (≈ +X%) |
|---|---|---|---|
| 1–93 | **giữ nguyên** công thức cũ | — | — |
| 100 | ≈ +10% | 10% | |
| 500 | ≈ −20% | sàn | |
| 1000 | ≈ −40% | sàn | |
| 2000 | ≈ −70% | sàn | |
| 4300 | ≈ −110% | sàn | |

Nguồn cộng (thiết kế cùng):

| Mục | Nguồn | Trần đề xuất |
|---|---|---|
| Milestone | mốc ×50 (có sẵn) | +10% |
| Trang bị | `tuposjs` trên ô Linh Phù / Ngọc Nhãn / Cổ Ngọc, tăng theo bậc | **K2** |
| Thiên Phú | Đột Phách Tâm Kinh, Bất Khúc Đạo Ý (0,25%/cấp, có sẵn) | theo trần cấp thiên phú |
| **Đan** (mới) | 7 Đột Phá Đan có sẵn (Trúc Cơ → Đại Thừa) + đan mới cho cảnh giới cao; uống trước khi đột phá, mất khi thử | **K2** |
| **Sự kiện** (mới) | cuối tuần / sự kiện server, cấu hình theo khung giờ | **K2** |

Sàn cuối cùng giữ **5%** (hoặc 1% — **K3**). Phí Linh Thạch giữ công thức cũ (`cấp×(cấp+1)`, đại cảnh giới `cấp²×3`) — với BIGINT không còn lo tràn ví.

## 4b. Bí Cảnh Luyện Cấp (map luyện cấp cho người chơi cấp cao)

Đề xuất của chủ dự án: map bãi quái dày cho người chơi cấp cao, cần kỹ năng tương ứng. **Ổn**, với các chốt chặn để không tăng cấp quá nhanh:

| Mục | Đề xuất |
|---|---|
| Vị trí | 1 Bí Cảnh / cảnh giới (map cuối mỗi khu có cổng vào) — quái **dày** (sinh lại nhanh), cùng cấp khu |
| Điều kiện vào | Đủ cấp khu **và** võ công đang dùng đạt cấp mở **Tuyệt Kỹ** (thế võ bậc 2, có sẵn `unlock_lv`) — "sở hữu kỹ năng tương ứng" |
| Giới hạn | Mỗi trận trong Bí Cảnh tốn **Thể Lực** (hệ thể lực đã có: `ustamina`/`umaxstamina`); hết thể lực thì phải ra ⇒ trần số trận/ngày tự nhiên |
| EXP | ×1,5 so với quái thường cùng cấp; **không rơi trang bị / Tàn Quyển** (tránh cày đồ) |
| Kiểm soát tổng | Mục tiêu: Bí Cảnh chiếm ≤ 25–30% EXP một ngày chơi đầy đủ — đo trên server thử mỗi làn |

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
| Ví `uyxb` cộng dồn không trần | Sau BIGINT không còn tràn; vẫn theo dõi tổng Linh Thạch toàn server mỗi làn để chống lạm phát |
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
| **KT-0** Nền kỹ thuật | PHP 7.4 64-bit + BIGINT (test toàn bộ trên cloud trước) · EXP quái cấp > 500 · công thức đột phá mới + mục Đan / Sự kiện + `tuposjs` trang bị · 12 màu + sửa tên cảnh giới · tách file quái theo làn · bộ công cụ `tools/gen/lan/` | — |
| **L2** 500–1000 | 10 khu (tên đã đề xuất ở `LAN2_CAP_500_1000.md`), Tàn Quyển boss mới | KT-0 |
| **L3** 1000–1500 | 10 khu, bậc đồ 1000/1250 (Hỗn Độn), 5 võ công + 4 công pháp | L2 |
| **L4** 1500–2000 | 10 khu, bậc 1500 | L3 |
| **L5–L9** | 2000 → 4300 (mỗi làn 10 khu; L9 = 6 khu 4000–4300) | lần lượt |

Song song với lộ trình này: **MK-1..3** (mở khóa tính năng) và **UI-1..3** (giao diện) — ưu tiên cao hơn vì ảnh hưởng **người chơi mới**,
trong khi người chơi thật cao nhất hiện mới cấp 93.

## 14. Cần chủ dự án quyết

| # | Câu hỏi | Đề xuất |
|---|---|---|
| ~~K1~~ | ~~Giới hạn số~~ | **Đã chốt: PHP 7.4 64-bit + BIGINT** |
| K2 | Tổng mức cộng tỉ lệ đột phá một người chơi chịu khó gom được ở cấp cao (Trang bị + Đan + Sự kiện + Thiên Phú + Milestone) | ≈ +100% ⇒ cấp 4300 gom đủ còn ~−10% → cần thêm sự kiện/đan đặc biệt mới qua được (đúng "tu tiên khó") |
| K3 | Sàn cuối cùng | 5% (giữ như cũ) |
| K4 | Áp dụng công thức đột phá mới từ cấp 94 (thay vì để âm sâu như hiện tại) | Có |
| K5 | Tên 4 phẩm trang bị mới | Hỗn Độn · Thái Sơ · Vĩnh Hằng · Chí Tôn |
| K6 | Đổi tên "Thánh Nhân" trùng ở cấp 3851 | "Hỗn Độn Thánh Nhân" |
| K7 | Số quái/cấp khi chỉ đánh quái thường (`KILLS`) | 400 |
| K8 | Thứ tự | MK + UI-1 → KT-0 (PHP 7.4 + BIGINT + EXP + đột phá) → L2 → … |
