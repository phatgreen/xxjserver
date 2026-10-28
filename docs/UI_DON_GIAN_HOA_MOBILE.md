# GIAO DIỆN — Đơn giản hóa cho người mới + Rà soát mobile (CHỜ DUYỆT)

> Lập 2026-10-28. Đi cùng `docs/MO_KHOA_TINH_NANG.md` (mở tính năng theo cấp). Hai tài liệu bổ sung nhau:
> tài liệu này quyết định **bố cục / điều hướng**, tài liệu kia quyết định **khi nào một tính năng xuất hiện**.

## 1. Mục tiêu

Ý tưởng gốc của game: **Tu luyện · Linh sủng · vài món trang bị · đánh quái · nhiệm vụ cơ bản**. Hiện màn hình đầu tiên
của người chơi mới có **~40 lối vào** cùng lúc ⇒ sốc, không biết bắt đầu từ đâu.

Mục tiêu:
1. Người mới chỉ thấy **5 việc chính**, luôn ở cùng một chỗ (thanh dưới cố định).
2. Mỗi lối vào chỉ xuất hiện **một lần** (bỏ trùng lặp).
3. Tính năng mở rộng gom vào một chỗ ("Tiên Đồ"), hiện dần theo `MO_KHOA_TINH_NANG.md`.
4. Mobile (390 px) dùng thoải mái bằng một tay; desktop giữ khung giữa như hiện tại.
5. **Không đổi logic game** — chỉ đổi lớp hiển thị / điều hướng.

## 2. Hiện trạng — kiểm kê lối vào

| Nơi | Lối vào | Số lượng |
|---|---|---|
| Thanh trên (`game.php`) | Trang Chủ · Tin Tức · Sự Kiện · Cẩm Nang · Cộng Đồng | 5 |
| Trang Tu Luyện (`game/xiulian.php`) | Cột trái: Công Pháp, Linh Căn, Thể Chất, Huyết Mạch, Luyện Đan · Cột phải: Trang Bị, Chế Tạo, Võ Công, Bí Tịch, Thiên Phú · Chip: Dùng Đan, Lò Đan, Đột Phá · 3 chế độ tu luyện | 16 |
| Thanh dưới (chỉ ở trang Tu Luyện) | Tu Luyện · Nhân Vật · Bản Đồ · Tông Môn · Nhiệm Vụ · Cửa Hàng | 6 |
| Đại Sảnh (`game/nowmid.php`) | 6 nút lớn: Tu Luyện, Võ Công, Phó Bản, Nhiệm Vụ, Bản Đồ, Thiên Cơ Các · lưới 15 nút: Thông Tin, Trang Bị, Túi Đồ, Luyện Đan, Chế Tạo, Tán Gẫu, Thú Cưng, Xếp Hạng, Giao Dịch, Môn Phái, Hảo Hữu, Giftcode, Quy Đổi, Cửa Hàng, Rời Khỏi | 21 |

**Trùng lặp:** Luyện Đan (3 chỗ), Trang Bị (2), Chế Tạo (2), Võ Công (2), Nhiệm Vụ (2), Bản Đồ (2), Cửa Hàng (2), Tu Luyện (2), Tông Môn/Môn Phái (2).
**Thiếu:** Linh Sủng (Thú Cưng) — một trong 5 trụ cột gốc — chỉ nằm lẫn trong lưới 15 nút.

## 3. Rà soát mobile (đo thật, khung 390×844, nhân vật thử)

Đo 26 trang bằng Playwright (`tools/cloud/audit_mobile.js`): bề rộng trang, phần tử tràn mép, nút cao < 32 px, chữ < 11 px.

| # | Vấn đề | Trang | Mức |
|---|---|---|---|
| M1 | **Thanh trên chiếm ~220 px** (5 nút xếp 3 hàng) ở mọi trang — 26% màn hình trước khi thấy nội dung | tất cả | Cao |
| M2 | **Thanh dưới không cố định** và chỉ có ở trang Tu Luyện; trang khác không có điều hướng chính, phải cuộn xuống cuối | tất cả | Cao |
| M3 | Đại Sảnh cao **2.112 px**; quái/NPC (việc chính) bị đẩy xuống, 21 nút chiếm nửa dưới | Đại Sảnh | Cao |
| M4 | Tiền bị cắt "2.09…", "50…" ở thanh trên | tất cả | Trung |
| M5 | Nền body hết ở cuối nội dung ⇒ lộ **dải trắng** dưới trang ngắn | Tu Luyện, … | Trung |
| M6 | Phần tử tràn mép phải: chip lọc Thiên Cơ Các; hàng bậc Thể Chất / Huyết Mạch (bậc 4 bị cắt, không có dấu hiệu cuộn ngang) | 3 trang | Trung |
| M7 | Nút thấp < 32 px (khó bấm): Trang Bị 15 · Cửa Hàng 10 · Xếp Hạng 10 · Chế Tạo 8 · Túi Đồ 2 | 5 trang | Trung |
| M8 | Chữ < 11 px: Thiên Cơ Các **233** chỗ · Lò Đan 21 · Xếp Hạng 21 · Tu Luyện 19 · Thiên Phú 12 | 5 trang | Thấp |
| M9 | Không trang nào tràn ngang toàn trang (bề rộng = 390) | — | Đạt |

> Ghi chú môi trường thử: tên bản đồ hiện lỗi font trên server cloud do cách nạp DB — trên máy chủ thật (ảnh chủ dự án gửi) hiển thị đúng, **không phải lỗi game**.

## 4. Bố cục mới đề xuất

### 4.1 Khung chung (mọi trang)

```
┌──────────────────────────────┐
│ 👤 Tên · Cảnh giới   💰 2,09B 💎 500K  ☰ │  ← thanh trên 1 hàng (~52 px). ☰ mở: Tin Tức, Sự Kiện, Cẩm Nang, Cộng Đồng, Giftcode, Rời Khỏi
├──────────────────────────────┤
│                              │
│        nội dung trang        │
│                              │
├──────────────────────────────┤
│ 🧘 Tu Luyện 🗺 Hành Tẩu 👤 Nhân Vật 🐾 Linh Sủng 📜 Nhiệm Vụ │  ← thanh dưới CỐ ĐỊNH, mọi trang
└──────────────────────────────┘
```

- Số tiền rút gọn kiểu `2,09B / 500K` (đầy đủ khi chạm).
- Thanh dưới **5 tab = 5 trụ cột gốc**. Tab có việc cần làm (nhiệm vụ xong, đột phá được) hiện chấm đỏ.
- Desktop: giữ khung giữa ~620 px như hiện tại, thanh dưới nằm trong khung.

### 4.2 Năm tab

| Tab | Gồm | Thay cho |
|---|---|---|
| 🧘 **Tu Luyện** | Quả cầu tu luyện, thanh tu vi, Đột Phá, Dùng Đan. Bên dưới: hàng **"Tiên Đồ"** — ô các hệ tu thân **đã mở** (Công Pháp, Linh Căn, Thể Chất, Huyết Mạch, Thiên Phú, Luyện Đan…) | 2 cột 10 icon hai bên quả cầu |
| 🗺 **Hành Tẩu** | Đại Sảnh rút gọn: tên map, quái, boss, NPC, đường đi, kênh hệ thống. Nút nhỏ: Bản đồ thế giới, Phó Bản (khi mở) | Đại Sảnh + Bản Đồ |
| 👤 **Nhân Vật** | Thông tin, Trang Bị, Túi Đồ, Võ Công / Bí Tịch, Chế Tạo (khi mở) — dạng tab con | Thông Tin, Trang Bị, Túi Đồ, Võ Công rải rác |
| 🐾 **Linh Sủng** | Thú cưng, Sào Thú (khi mở) | Thú Cưng trong lưới |
| 📜 **Nhiệm Vụ** | Chính tuyến, ngày/tuần, Trực Tuyến, Bế Quan | giữ nguyên trang `nhiemvu` |

**Gom vào nút ☰ / "Thêm" (chỉ hiện khi đã mở):** Cửa Hàng, Thiên Cơ Các, Tông Môn, Giao Dịch, Hảo Hữu, Xếp Hạng, Tán Gẫu, Quy Đổi, Tháp Yêu, Vấn Đỉnh Đài.

### 4.3 Người mới (cấp 1) sẽ thấy

Thanh dưới 5 tab (Linh Sủng hiện khóa "🔒 cấp 8" để gợi tò mò) · trang Tu Luyện chỉ có quả cầu + Đột Phá + Dùng Đan ·
Hành Tẩu có quái, NPC tân thủ · ☰ có Tin Tức / Cẩm Nang / Giftcode. **≈ 12 lối vào thay vì ~40.**

## 5. Chia đợt

| Đợt | Nội dung | File chính | Rủi ro |
|---|---|---|---|
| **UI-1** Sửa mobile nhỏ | M4 rút gọn tiền, M5 nền body, M6 hàng bậc cuộn ngang có dấu hiệu, M7 nút ≥ 36 px, M8 chữ ≥ 11 px | `css/mystyle.css` (+ CSS trong trang), tăng `?v=` | Thấp |
| **UI-2** Khung mới | Thanh trên 1 hàng + ☰, thanh dưới cố định 5 tab mọi trang | `game.php` (khung chung), `css/mystyle.css` | Trung — đụng mọi trang, test 26 trang × mobile/desktop |
| **UI-3** Trang gọn | Tu Luyện: bỏ 2 cột icon → hàng Tiên Đồ. Hành Tẩu: bỏ 21 nút. Nhân Vật: tab con | `game/xiulian.php`, `game/nowmid.php`, `game/zhuangtai.php` | Trung |
| Kiểm chứng | Chụp 26 trang ở 390×844 và 1366×768 trước/sau, chạy lại `tools/cloud/audit_mobile.js` (mục tiêu: 0 tràn mép, 0 nút < 32 px ở các trang chính) | — | — |

UI-2/UI-3 làm **sau** khi có hệ mở khóa (đợt MK-1 của `MO_KHOA_TINH_NANG.md`) để hàng Tiên Đồ / ☰ chỉ hiện đúng tính năng đã mở.

## 6. Cần chủ dự án quyết

| # | Câu hỏi | Đề xuất |
|---|---|---|
| U1 | 5 tab thanh dưới như mục 4.2? | Đồng ý |
| U2 | Tên tab đánh quái: "Hành Tẩu" / "Bản Đồ" / "Thám Hiểm"? | Hành Tẩu |
| U3 | Thanh trên: bỏ 5 nút Trang Chủ/Tin Tức/… vào ☰? | Đồng ý |
| U4 | Làm UI-1 (sửa nhỏ, rủi ro thấp) ngay trong lúc chờ duyệt phần còn lại? | Có |
