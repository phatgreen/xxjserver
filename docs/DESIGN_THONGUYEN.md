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

## 2. Bảng thọ nguyên gốc (chưa tính % hệ thống) — bản 3 (2026-10-29, chủ dự án chốt: số kiểu truyện, giữ 2h = 1 tuổi)

| Cảnh giới (cấp) | Gốc | Cảnh giới (cấp) | Gốc |
|---|---|---|---|
| Nhập Môn (0) | 60 | Trúc Cơ (350) | 500 |
| Luyện Thể (50) | 100 | Kim Đan (400) | 1.000 |
| Thối Thể (100) | 120 | Ngưng Anh (450) | 1.500 |
| Tụ Thân (150) | 150 | Nguyên Anh (500) | 2.000 |
| Tụ Khí (200) | 180 | 550 / 600 / 650 / 700 | 2.800 / 3.900 / 5.500 / 7.700 |
| Ngự Khí (250) | 210 | 750 → 1200 | ×1,4 mỗi cảnh giới |
| Luyện Khí (300) | 250 | Phi Thăng (1250) ×2,8, Tiên giới | ×1,6 mỗi cảnh giới |

## 3. Hệ quả (mô phỏng `tools/sim/econ_sim.py` có quà Tân Thủ, cấp thấp nhất trong 3 seed, F2P, chưa tính % hệ thống / đan)

Tuổi tính theo giờ thật (12 tuổi/ngày) mà sau 3 ngày quà người chơi đứng lâu ở cấp 220–350 ⇒ **gần như ai cũng Tọa Hóa trong tháng đầu**, phải dùng Diên Thọ Đan / Hồi Hồn Đan (chủ dự án đã chấp nhận).

| Giờ/ngày | Vào vùng −20% | Tọa Hóa lần đầu |
|---|---|---|
| 1h | ngày 13 | ngày 16 (cấp 231) |
| 2h | ngày 15 | ngày 19 (cấp 279) |
| 4h | ngày 16 | ngày 19 (cấp 287) |
| 8h | ngày 16 | ngày 21 (cấp 300) |
| 24h | ngày 26 | ngày 34 (cấp 349) |

Bản 2 (đã bỏ) dùng Ngự Khí 800 / Luyện Khí 2.700 / Trúc Cơ 7.000… để người chơi 1h/ngày không bao giờ chết. Muốn quay lại hoặc đổi tốc độ tuổi (`hours_per_year` 24 = 1 ngày thật 1 tuổi) thì chỉ sửa `thonguyen_config.json`.

Chỉnh số: sửa `realm_base`, `growth`, `tien_growth`, `penalty`, `price`… trong `thonguyen_config.json` (không cần sửa code).

## 4. Nguồn Diên Thọ Đan (djid 121) — "khó nhưng có đường"

| Nguồn | Lượng | Ghi chú |
|---|---|---|
| Mốc trực tuyến 240 phút / ngày | 1 viên / ngày | `nhiemvu_reward_rules.json` → `online.milestones` |
| Tỏa Yêu Tháp tầng 50 / 100 / 150 | 1 viên / tầng, tối đa 3 viên / tuần | `game/toayeuthap.php` `tyt_floor_rewards` (cả tự động vượt) |
| Vấn Đỉnh Đài chốt 21:00 | top 1: 3 · top 2–3: 2 · top 4–10: 1 / ngày | `class/function/vandinhdai_reward.php` |
| Giftcode / Phát quà GM | tuỳ GM | trang GM: thêm đạo cụ 121 vào giftcode hoặc tab Phát quà |
| Mua ở trang Thọ Nguyên | 1.000 × (cảnh giới+1)^1,5 LT | |

Nhu cầu ở cấp 200–350: 1 viên (−10% thọ nguyên) bù ~1,5–2,5 ngày tuổi ⇒ cần ~0,4–0,7 viên/ngày. Người chơi ≥ 4h/ngày + leo tháp đủ dùng; người chơi ít phải mua bằng LT hoặc chờ quà GM.
