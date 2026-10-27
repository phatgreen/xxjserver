# ĐỢT F — Trang bị theo dải cấp 1–500 (THIẾT KẾ — chờ duyệt)

> Lập 2026-10-28. Thuộc kế hoạch tổng `PLAN_MO_RONG_NOI_DUNG.md`. Số liệu đo bằng mô phỏng
> (`tools/gen/`-style, script tạm) trên đúng công thức trong code: lên cấp (`upplayerlv`), đột phá (`game/tupo.php`),
> cường hóa (`upzbsx`), Thần Trang (`taozhuang_config.json`), chỉ số quái đợt B, sát thương `công − 0,75 × thủ` (sàn 15%).

## 1. Hiện trạng trang bị (`zhuangbei_config.json`, 180 món)

| Hạng mục | Thực tế | Vấn đề |
|---|---|---|
| Bậc (zblv) | 1 · 10 · 30 · 60 · 120 · 200 · 300 · 400 · 600 · 800 | Dải cấp 1–500 không có bậc 500 (400 dùng tới 599) |
| Số ô | 16 ô; bậc 1/30/200/400/800 đủ 15 ô, bậc 10/60/120/300/600 **chỉ 11 ô** (thiếu Thiên Thuẫn, Thiên Quan, Hộ Thủ, Linh Đai) | Ô 16 Bảo Vật **không có món nào** |
| Phẩm chất (rarity) | Gán lộn xộn: bậc 1 có món "Đạo", bậc 400 có món "Thiên"; cùng bậc cùng ô các món **chỉ số y hệt** | Màu/tên phẩm chất không nói lên gì; `rarity_mult` (1→8) khai báo nhưng không dùng |
| Cường hóa | Mỗi lần +8% **chỉ số hiện tại** (lũy tiến), tốn `qh×3+1` Cường Hóa Thạch, tỉ lệ +10 ≈ 50%, +20 ≈ 16%, +29 ≈ 1%, tối đa +30 | +29 ≈ ×9,3 chỉ số gốc ⇒ một món cường hóa cao vượt cả bậc trên |
| Thần Trang (bộ) | % theo bậc (1–10%) + mốc 4/8/12/**16** món | Chỉ có 15 ô có đồ ⇒ mốc 16 "Hoàn chỉnh" **không bao giờ đạt** |
| Chế tạo | 7 công thức trang bị, đều yêu cầu cấp 300 nhưng ra đồ bậc 200–800 | Đồ bậc 600/800 chế ở cấp 300 không mặc được |
| Ngũ hành | Config luôn `none`, hệ thật nằm ở DB (nạp ngũ hành) | Ổn, giữ nguyên |
| `tuposjs` / `tupoexp` | Toàn bộ = 0 | Không dùng, giữ nguyên |

## 2. Phát hiện chính — đột phá làm chỉ số tăng theo cấp số nhân

Mỗi cấp đều phải Đột Phá (mỗi cấp là 1 "giai"). Nhánh đột phá thường (`tupo.php`) cộng:
**Công +1% tổng công hiện có, Phòng +1% tổng phòng, Máu +5% máu hiện tại** — tính cả trang bị, lũy tiến qua từng cấp.
(Chỉ nhánh "đại cảnh giới" mỗi 50 cấp là cộng số cố định: `2 + cấp/2,5` công, `3 + cấp/2` phòng, `4 + cấp/1,2` máu.)

Mô phỏng nhân vật chuẩn (đủ bộ trang bị bậc cao nhất được mặc, cường hóa +10), đánh quái Thường cùng cấp:

| Cấp | Công | Máu | Trang bị chiếm (công / máu) | Đòn hạ quái | Quái cần … đòn hạ người |
|---|---|---|---|---|---|
| 60 | 1.179 | 21.876 | 60% / 14% | 9,6 | 250 |
| 100 | 2.069 | 152.739 | 34% / 2% | 13,0 | 568 |
| 200 | 9.531 | 18,6 triệu | 22% / 0% | 7,2 | 25.953 |
| 300 | 28.686 | 2,2 tỉ | 9% / 0% | 4,3 | 1,5 triệu |
| 500 | 219.272 | 31.549 tỉ | 1% / 0% | 1,2 | 8,9 tỉ |

⇒ Từ cấp ~150 **máu gần như vô hạn, quái không làm gì được**, và **trang bị gần như vô nghĩa** (dưới 10% sức mạnh từ cấp 300).
Mở thêm bậc trang bị mà không sửa chỗ này thì vô ích.

## 3. Phương án

| | A. Chỉ thêm/nâng trang bị | **B. Sửa đột phá + làm lại trang bị (khuyến nghị)** | C. Đặt trần % cho đột phá |
|---|---|---|---|
| Làm gì | Thêm bậc 500, nâng chỉ số | Đột phá mỗi cấp cộng **số cố định theo cấp** (dùng đúng công thức nhánh đại cảnh giới có sẵn) + làm lại trang bị theo đường cong mới | Giữ % nhưng chặn tối đa |
| Ưu | Ít sửa code | Đường sức mạnh ổn định mọi cấp, trang bị luôn chiếm ~1/3, cân được quái | Sửa ít |
| Nhược | Không giải quyết máu vô hạn / trang bị vô nghĩa | Đổi cốt lõi tiến trình (chỉ ảnh hưởng các lần lên cấp **sau này** — game đang phát triển nên chấp nhận được) | Vẫn lũy tiến, khó cân, nửa vời |

Mô phỏng phương án B (cường hóa +10, trang bị hiện tại):

| Cấp | Công | Máu | Đòn hạ quái | Quái cần … đòn hạ người |
|---|---|---|---|---|
| 60 | 1.725 | 6.956 | 5,8 | 79 |
| 200 | 11.776 | 46.659 | 5,7 | 65 |
| 300 | 24.033 | 80.264 | 5,3 | 55 |
| 500 | 62.047 | 211.001 | 4,6 | 60 |

## 4. Chi tiết phương án B

| # | Hạng mục | Nội dung |
|---|---|---|
| F1 | **Đột phá** | Nhánh thường dùng cùng công thức cố định với nhánh đại cảnh giới. Chỉ số đã có của nhân vật giữ nguyên |
| F2 | **Chỉ số trang bị** | Mỗi bậc: tổng chỉ số đủ bộ ≈ **50% chỉ số gốc của nhân vật ở cấp đó** (trang bị ≈ 1/3 tổng lực). Giữ tỉ lệ giữa các ô như hiện tại. Hệ số nhân so với hiện tại (công/phòng/máu): bậc 60 ×1,5/3,6/1,2 · 120 ×2,7/6,4/1,7 · 200 ×4,8/6,9/1,8 · 300 ×8/19,5/3,8 · 400 ×10/15/3,5 |
| F3 | **Bậc 500 mới** | 15 món (đủ ô), tên theo hệ "Hồng Mông" / tương tự, rơi ở quái 450–500 và boss Thiên Ngoại Thiên |
| F4 | **Đủ 15 ô mọi bậc** | Bổ sung Thiên Thuẫn / Thiên Quan / Hộ Thủ / Linh Đai cho bậc 10, 60, 120, 300 (và 600) |
| F5 | **Phẩm chất theo bậc** | Phẩm chất = bậc (1 Phàm, 10 Linh, 30 Huyền, 60 Địa, 120 Thiên, 200 Thánh, 300 Tiên, 400 Thần, 500/600 Đế, 800 Đạo) ⇒ màu nói đúng sức mạnh |
| F6 | **Cường hóa** | Mỗi cấp +8% **chỉ số gốc** (không lũy tiến) ⇒ +10 = +80%, +30 = +240%. Giá đá & tỉ lệ giữ nguyên. Đồ đã cường hóa: tính lại theo công thức mới |
| F7 | **Thần Trang** | Mốc 4 / 8 / 12 / **15** món (15 = Hoàn chỉnh) |
| F8 | **Chế tạo** | Cấp yêu cầu = bậc đồ làm ra (đồ 600/800 không chế được trước cấp 600/800) |
| F9 | **Cân lại quái** | Chạy lại mô phỏng sau F1–F6, chỉnh hệ số quái đợt B (mục tiêu: 4–6 đòn hạ quái Thường, quái Thường cần 30–60 đòn hạ người, boss 4–8 lần khó hơn) |

Triển khai: sinh dữ liệu bằng `tools/gen/dot_f_build.py` (chạy lại cho cùng kết quả), test `tests/test_dot_f.php`, mô phỏng lưu `docs/` để kiểm chứng.

## 5. Cần chủ dự án quyết

| # | Câu hỏi | Đề xuất |
|---|---|---|
| Q1 | Sửa đột phá thành cộng số cố định (F1)? Đây là thay đổi cốt lõi | Đồng ý — không sửa thì mọi cân bằng sau cấp 150 đều vô nghĩa |
| Q2 | Cường hóa đổi sang +8% chỉ số gốc, không lũy tiến (F6)? | Đồng ý |
| Q3 | Phẩm chất theo bậc (F5)? Hay muốn phẩm chất là **chỉ số ngẫu nhiên khi rơi** (cùng món, phẩm cao mạnh hơn)? | Theo bậc — đơn giản, dễ cân; phẩm ngẫu nhiên để đợt sau nếu cần |
| Q4 | Ô 16 Bảo Vật: làm ngay hay để sau? | Để sau (cần thiết kế hiệu ứng riêng) |
