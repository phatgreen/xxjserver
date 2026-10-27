# KẾ HOẠCH MỞ RỘNG NỘI DUNG — theo cấp độ & cảnh giới mới

> Lập: 2026-10-27 · Trạng thái: **đã có dump DB (`docs/db/game.sql`)**, chờ mốc cấp mục tiêu (mục 6).
> Bối cảnh: game ban đầu chỉ có tu luyện, võ công, công pháp, sủng vật cơ bản. Nay đã thêm Thiên Phú, Linh Căn,
> Huyết Mạch, Thể Chất, Luyện Đan, Chế Tạo, Thế Võ, Cộng Hưởng… và mở cảnh giới rất xa, nhưng **nội dung chưa theo kịp**.

---

## 1. Hiện trạng (đã đo trong code/config)

| Hạng mục | Hiện có | Khoảng trống |
|---|---|---|
| Cảnh giới | 74 cảnh giới × 50 cấp (`class/rangesjj.php`, `rangeslv` tới 4300) | — |
| Quái thường | **87 con, cấp 1 → 105** (`guaiwu_config.json`), dày ở 1–80, chỉ 1 con trên 100 | Không có quái từ cấp ~105 trở lên |
| Bản đồ | Bảng `mid` trong **DB** (không có trong repo); quái gắn vào map qua `mid.mgid` | Chưa xem được — cần dump (mục 6) |
| Nhiệm vụ chính tuyến | 3 chương, 21 nhiệm vụ, **cấp 1 → 50** (`nhiemvu_main_config.json`) | Không có chính tuyến sau cấp 50; không dạy các hệ mới |
| Nhiệm vụ NPC | 44 nhiệm vụ (`renwu_config.json`) — gần như đều không ghi cấp | Không có lộ trình theo cấp |
| Võ công | 29 bộ, 4 phẩm, yêu cầu cảnh giới tối đa 25 | Mỏng ở cảnh giới cao, chỉ có 1 nguồn (Thiên Cơ Các) |
| Công pháp | 28 bộ, yêu cầu cảnh giới tối đa 43 | Như trên |

### 1b. Bản đồ theo khu vực (từ dump DB, 142 bản đồ / 15 khu vực)

| qyid | Khu vực | Số map | Cấp quái |
|---|---|---|---|
| 14 | Tân Thủ Thôn | 9 | 1–12 |
| 16 | Tụ Tiên Thành | 9 | 13–18 |
| 17 | Ma Hóa Khu Vực | 11 | 19–27 |
| 18 | Vạn Thiên Sơn Vực | 13 | 28–51 |
| 20 | Yêu Vương Điện | 1 | 50–56 |
| 21 | Viêm Dương Thành | 10 | 45–58 |
| 22 | Thông Thiên Phủ | 9 | 51–60 |
| 23 | Thiên Không Thành | 7 | 57–59 |
| 25 | Tịch Dương Thành | 10 | 61–71 |
| 26 | Thiên Thủy Cốc | 10 | 57–77 |
| 24 | Hoa Quả Sơn | 13 | 92–100 |
| 29 | Thiên Tuyệt Địa | 1 | 48 |
| 27 | Tây Lương Địa | 9 | **chưa có quái** |
| 28 | Hoang Thiên Lý | 11 | **chưa có quái** |
| 30 | Hồi Đầu Ngạn | 10 | **chưa có quái** |
| 31 | (chưa đặt tên) | 9 | **chưa có quái** |

⇒ Có sẵn **39 bản đồ trống** ở 4 khu — đợt B dùng chúng trước (thêm quái vào `mid.mgid`), thiếu mới tạo map.
Dải cấp 78–91 cũng đang trống giữa Thiên Thủy Cốc và Hoa Quả Sơn.

## 1c. Chạy thử trên cloud

- MariaDB + dump `docs/db/game.sql` (cần `innodb_strict_mode=0`), PHP 8.3 chạy **bản sao** có vá `parse_str` 1 tham số
  (chỉ để thử, không sửa code thật — game vẫn là PHP 5.6). Đã kiểm chứng thật: thế võ PVE, Boss tụ lực/phá thế,
  trang Võ Công / Công Pháp / Cộng Hưởng.

## 2. Nguyên tắc

1. **Theo nhịp cảnh giới**: mỗi cảnh giới (50 cấp) phải có: quái để luyện, ≥ 1 nhiệm vụ chính tuyến, ≥ 1 võ công/công pháp mới để mua/rơi.
2. **Dạy hệ thống bằng nhiệm vụ**: mỗi hệ mới có 1 nhiệm vụ hướng dẫn đúng lúc mở khóa (Thiên Phú, Linh Căn, Huyết Mạch, Thể Chất, Luyện Đan, Chế Tạo, Thế Võ, Cộng Hưởng, Tháp, Đài, Boss bang).
3. **Số liệu theo công thức** (không gõ tay từng con) — quái/thưởng sinh từ đường cong cấp để dễ cân bằng lại; lưu JSON như các hệ khác.
4. **Làm theo đợt**, mỗi đợt chơi được ngay, không phá save cũ.

## 3. Lộ trình

| Đợt | Nội dung | Phụ thuộc |
|---|---|---|
| **A. Võ công / Công pháp** | 6 phẩm thống nhất (Cơ Bản · Hạ Thừa · Trung Cấp · Thượng Thừa · Tuyệt Thế · Tiên Đạo); thêm ~31 võ công (đủ 3 thế + văn) và ~32 công pháp, rải theo 5 hệ × 6 phẩm × mốc cảnh giới; nguồn: cửa hàng (phẩm thấp), quái/Boss/Tháp (phẩm giữa), Boss bang/sự kiện (Tuyệt Thế, Tiên Đạo); Cộng Hưởng thêm mốc khi nhóm đông | Không — làm được ngay |
| **B. Quái theo cảnh giới** | Đường cong chỉ số quái theo cấp (HP/Công/Thủ/EXP/linh thạch) khớp sức mạnh nhân vật có đủ hệ mới; sinh quái cho các dải cấp còn trống; mỗi dải 1 khu vực bản đồ mới + 1 boss khu vực | **Dump bảng `mid` (+ khu vực, NPC)**; mốc cấp mục tiêu |
| **C. Nhiệm vụ tân thủ (làm lại cấp 1–50)** | Giữ 21 nhiệm vụ cũ, chèn nhiệm vụ hướng dẫn hệ mới đúng thời điểm mở khóa; thưởng dẫn dắt (đan, bí tịch, Khai Phú Đan…) | Đợt A (thưởng bí tịch) |
| **D. Nhiệm vụ phát triển (cấp 50 → mục tiêu)** | Chương mới theo từng cảnh giới: săn quái khu mới, đột phá, Tháp tầng X, Cộng Hưởng mốc, Boss khu vực | Đợt B |
| **E. Thần Thông** | Hệ mới (kỹ năng cảnh giới cao) — thiết kế riêng sau | Sau D |

## 4. Đợt A — chi tiết

- Phẩm (giữ key cũ để không vỡ dữ liệu): `nhap_mon` → hiển thị **Cơ Bản**, thêm `ha_thua` (**Hạ Thừa**), `trung_cap`, `thuong_thua`,
  `than_cong` → hiển thị **Tuyệt Thế**, thêm `tien_dao` (**Tiên Đạo**). Công pháp dùng `quality` 1–6 tương ứng.
- Mốc yêu cầu cảnh giới gợi ý: Cơ Bản 0 · Hạ Thừa 3 · Trung Cấp 6 · Thượng Thừa 12 · Tuyệt Thế 20 · Tiên Đạo 30+.
- Chỉ số/hiệu ứng của bộ mới đi theo đường cong phẩm của bộ cũ; hiệu ứng chỉ dùng loại đã code (không thêm cơ chế mới).
- Thế võ: thêm `rarity_bonus` cho `ha_thua` (0.02) và `tien_dao` (0.2).

## 5. Rủi ro

| Rủi ro | Kiểm soát |
|---|---|
| Quái mới quá mạnh/yếu so với nhân vật đủ hệ | Giả lập lực chiến nhân vật mẫu từng mốc cấp trước khi sinh quái |
| Thêm map vào DB production | Script migration idempotent + runbook như Thiên Phú |
| Thưởng nhiệm vụ làm lạm phát | Tính tổng thưởng theo chương, so với `ECONOMY_BALANCE.md` |

## 6. Cần chủ dự án cung cấp

1. **Dump bảng bản đồ** (`mid`, bảng khu vực `qy`, bảng NPC nếu có) — cấu trúc + dữ liệu, để thêm khu vực và quái.
2. **Mốc cấp mục tiêu cho đợt nội dung này** (người chơi mạnh nhất hiện cấp bao nhiêu; muốn đợt này phủ tới cấp/cảnh giới nào).


## 7. Cập nhật sau đợt A, B (2026-10-27)

- Đợt A (võ công / công pháp): **xong** — `DOT_A_VOCONG_CONGPHAP.md`.
- Đợt B (quái tới cấp 500): **xong** — `DOT_B_QUAI_CANH_GIOI.md`.
- Đợt C (nhiệm vụ): **xong** — `DOT_C_NHIEM_VU.md`.
- Đợt F (trang bị): **thiết kế chờ duyệt** — `DOT_F_TRANG_BI.md`.

### Phạm vi cấp
Game có 74 cảnh giới (tới ~cấp 3.700). Nội dung làm **theo từng làn sóng**, mỗi làn ~500 cấp, vì người chơi thật cao nhất hiện cấp 93:
làn 1 = cấp 1–500 (đang làm) · làn 2 = 500–1000 · … Công cụ sinh (`tools/gen/`) dùng lại được, chỉ thêm khu/tên mới.

### Bổ sung theo yêu cầu chủ dự án
| Đợt | Nội dung |
|---|---|
| **C. Nhiệm vụ** (mở rộng) | Chính tuyến (tân thủ + phát triển), **nhiệm vụ NPC theo từng bản đồ/khu**, nhiệm vụ ngày/tuần/tháng, nhiệm vụ ẩn, **thưởng trực tuyến / ngoại tuyến** — khảo sát hệ hiện có rồi thiết kế lại theo dải cấp 1–500 |
| **F. Trang bị** | Hiện có 10 bậc (1/10/30/60/120/200/300/400/600/800) nhưng hở lớn 400→600→800 và không có gì sau 800; xem lại cường hóa / đột phá / bộ / ngũ hành để khớp dải cấp mới |
