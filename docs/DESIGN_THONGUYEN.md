# Tuổi & Thọ Nguyên (2026-10-29)

Code: `class/function/thonguyen.php`, số liệu: `class/config/thonguyen_config.json`, trang: `game/thonguyen.php` (`cmd=thonguyen`), test: `tests/test_thonguyen.php`.

## 1. Luật đã chốt

| Mục | Luật |
|---|---|
| Tuổi | **Giờ thật, kể cả offline**: 2 giờ = 1 tuổi (1 ngày = 12 tuổi, 1 tháng tuổi = 10 phút). Tuổi khởi đầu 0 |
| Lưu | Bảng `player_thonguyen (sid, moc)`, `moc` = "ngày sinh" (unix time). Bảng **tự tạo**, nhân vật chưa có dòng ⇒ tạo `moc = bây giờ` ở lần vào game đầu tiên (nhân vật cũ bắt đầu từ 0 tuổi lúc cập nhật). Không thêm cột `game1` |
| Thọ nguyên | `(gốc cảnh giới i + (gốc i+1 − gốc i) × 0,6 × cấp_trong_cảnh_giới / 50) × (1 + % hệ thống)` — lên cấp tăng dần, đột phá cảnh giới nhảy phần 40% còn lại |
| % hệ thống | Huyết Mạch +2%/phẩm, Thể Chất +2%/phẩm, Thiên Phú +1,5%/phẩm (từ phẩm 2), Linh Căn +0,1%/cấp node (tối đa 15%). **Tổng tối đa +50%** |
| Thọ nguyên cạn | Còn ≤ 20% / 10% / 5% ⇒ hấp thụ linh khí **tu luyện + bế quan** −20% / −30% / −40%. Đại sảnh nhắc |
| Tọa Hóa | Tuổi ≥ thọ nguyên. Khoá: tu luyện (bắt đầu), quái, boss, PK, đột phá, boss tông môn / linh căn / huyết mạch, Tỏa Yêu Tháp, Vấn Đỉnh Đài, Hầm Mỏ, Sào Thú → chuyển sang trang Thọ Nguyên. Vẫn vào túi đồ, chat, tông môn, mua bán. Bế quan không tích. **Không bảo vệ người mới** |
| Sống lại | Đại Tỉnh Hồi Hồn Đan (djid 122): còn 15% thọ nguyên. Cửu Chuyển Hoàn Hồn Đan (123): còn 50% |
| Kéo dài | Diên Thọ Đan (121): tuổi −10% thọ nguyên (không dùng khi đã Tọa Hóa) |
| Giá (trang Thọ Nguyên, vào túi, giao dịch được, không bán NPC) | n = cảnh giới + 1. Đại Tỉnh `2.000 × n^1,5` LT, Diên Thọ `1.000 × n^1,5` LT, Cửu Chuyển `30 × n` MT |

## 2. Bảng thọ nguyên gốc (chưa tính % hệ thống) — bản 2 (2026-10-29, có tính quà Tân Thủ)

Có quà Tân Thủ nên ngày 3 người chơi đã ở cấp ~220–310 ⇒ 5 cảnh giới đầu đi qua trong 1–3 ngày (tuổi ≤ 36), để số nhỏ theo truyện. Sau ngày 3 người chơi đứng lâu ở cấp 220–300 (Tụ Khí → Ngự Khí) ⇒ **từ Ngự Khí trở đi thọ nguyên tăng mạnh**.

| Cảnh giới (cấp) | Gốc | Cảnh giới (cấp) | Gốc |
|---|---|---|---|
| Nhập Môn (0) | 60 | Kim Đan (400) | 12.000 |
| Luyện Thể (50) | 100 | Ngưng Anh (450) | 21.000 |
| Thối Thể (100) | 120 | Nguyên Anh (500) | 30.000 |
| Tụ Thân (150) | 150 | 550 / 600 / 650 | 42.000 / 60.000 / 85.000 |
| Tụ Khí (200) | 180 | 700 | 120.000 |
| Ngự Khí (250) | 800 | 750 → 1200 | ×1,4 mỗi cảnh giới |
| Luyện Khí (300) | 2.700 | **Phi Thăng (1250)** | ×2,8 |
| Trúc Cơ (350) | 7.000 | Tiên giới trở lên | ×1,6 mỗi cảnh giới |

## 3. Cân bằng (mô phỏng `tools/sim/econ_sim.py` có quà Tân Thủ, 5 năm giờ thật, cấp thấp nhất trong 3 seed, F2P)

Vì tuổi tính theo giờ thật nên mọi nhân vật già như nhau (12 tuổi/ngày); thọ nguyên phải chạy kịp **người chơi chậm nhất còn hoạt động**.

| Giờ/ngày | Cấp ngày 3 / 30 / 90 / 365 | Thọ nguyên / tuổi thấp nhất | Còn lại thấp nhất |
|---|---|---|---|
| 1h | 218 / 252 / 292 / 370 | 1,54 (ngày 104, cấp 299) | 35% |
| 2h | 256 / 289 / 321 / 395 | 1,85 | 46% |
| 4h | 270 / 292 / 329 / 409 | 2,36 | 58% |
| 8h | 293 / 317 / 359 / 461 | 3,58 | 72% |
| 24h | 310 / 348 / 390 / 497 | 5,03 | 80% |

Không ai đang chơi ≥ 1h/ngày vào vùng bị phạt. Bỏ game: cấp ~220 (bỏ sau 3 ngày quà) Tọa Hóa khoảng ngày 20–30; cấp 300 khoảng 7–8 tháng.

Nếu muốn giữ đúng Luyện Khí ≈ 250 năm như truyện thì phải đổi tốc độ tuổi sang **1 ngày thật = 1 tuổi** (`hours_per_year` 24), khi đó: Luyện Khí 250, Trúc Cơ ~550, Kim Đan ~1.000, Ngưng Anh ~1.700, Nguyên Anh ~2.400.

Chỉnh số: sửa `realm_base`, `growth`, `tien_growth`, `penalty`, `price`… trong `thonguyen_config.json` (không cần sửa code). Nội dung lên cấp thay đổi nhiều ⇒ chạy lại mô phỏng để kiểm.
