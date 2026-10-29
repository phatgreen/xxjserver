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

## 2. Bảng thọ nguyên gốc (chưa tính % hệ thống)

| Cảnh giới (cấp) | Gốc | Cảnh giới (cấp) | Gốc |
|---|---|---|---|
| Nhập Môn (0) | 60 | Kim Đan (400) | 18.000 |
| Luyện Thể (50) | 400 | Ngưng Anh (450) | 25.000 |
| Thối Thể (100) | 700 | Nguyên Anh (500) | 35.000 |
| Tụ Thân (150) | 1.500 | 550 / 600 / 650 | 50.000 / 70.000 / 100.000 |
| Tụ Khí (200) | 2.500 | 700 | 140.000 |
| Ngự Khí (250) | 4.000 | 750 → 1200 | ×1,4 mỗi cảnh giới (~4 triệu ở 1200) |
| Luyện Khí (300) | 6.000 | **Phi Thăng (1250)** | ×2,8 (~1.100 vạn) |
| Trúc Cơ (350) | 9.000 | Tiên giới trở lên | ×1,6 mỗi cảnh giới (cấp 4250 ≈ 2×10¹⁹) |

## 3. Cân bằng (mô phỏng `tools/sim/econ_sim.py`, 5 năm giờ thật, 3 seed, F2P)

Vì tuổi tính theo giờ thật nên mọi nhân vật già như nhau (12 tuổi/ngày); thọ nguyên phải chạy kịp **người chơi chậm nhất còn hoạt động**. Bảng gốc được cân để người chơi **1h/ngày** luôn có thọ nguyên ≥ 1,5 × tuổi **chưa cần % hệ thống**:

| Giờ/ngày | Thọ nguyên / tuổi thấp nhất (5 năm) | Còn lại thấp nhất | Bị phạt? |
|---|---|---|---|
| 1h | 1,50 (ngày 188, cấp 249) | 33% | Không |
| 2h | 1,93 | 48% | Không |
| 4h | 2,14 | 53% | Không |
| 8h – 24h | cao hơn | > 53% | Không |
| Bỏ game (không lên cấp) | Cấp 0–49 chết sau 5–20 ngày; cấp 100 ≈ 2 tháng; cấp 300 ≈ 1,4 năm | | Tọa Hóa |

Mốc cấp của người 1h/ngày (để tra): cấp 50 ngày 14 · 100 ngày 31 · 150 ngày 63 · 200 ngày 110 · 250 ngày 186 · 300 ngày 274 · 400 ngày 764 · 500 ngày 1.490.

Chỉnh số: sửa `realm_base`, `growth`, `tien_growth`, `penalty`, `price`… trong `thonguyen_config.json` (không cần sửa code). Nội dung lên cấp thay đổi nhiều ⇒ chạy lại mô phỏng để kiểm.
