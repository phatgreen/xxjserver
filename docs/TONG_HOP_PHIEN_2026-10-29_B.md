# TỔNG HỢP THAY ĐỔI — phiên 2026-10-29 (phần B)

> Nhánh `claude/zen-goodall-kbuo4z`. Nối tiếp `TONG_HOP_PHIEN_2026-10-28_29.md`.
> Chi tiết: Thọ Nguyên `DESIGN_THONGUYEN.md` · Tân Thủ `DESIGN_TANTHU_3NGAY.md` · sổ bàn giao `BAN_GIAO_PHIEN.md`.

---

## 0. Việc cần làm trên máy chủ (theo thứ tự)

| # | Bấm đúp | Làm gì | Ghi chú |
|---|---|---|---|
| 1 | `cron\capnhat_code.bat` | Lấy code mới | Sau đó **Ctrl+F5** (CSS `mystyle.css?v=2026102981`, popup `game-alert.*?v=2026102901`) |
| 2 | `cron\capnhat_thonguyen.bat` | Tạo bảng `player_thonguyen`, kiểm config + 3 đan thọ nguyên | Thấy `XONG - Tho Nguyen da san sang` là được |
| 3 | `cron\giftcode_tanthu_moi.bat` | Xoá 5 giftcode vip cũ, tạo 5 mã tân thủ mới | Gõ `Y` để ghi; chạy lại không sao |
| 4 | `cron\sua_loi_font.bat` | Quét lại chữ lỗi font (script đã sửa để quét cả cột `utf8mb3`) | Ra `DB sach` là xong |
| 5 | `cron\day_anh_len.bat` | Đẩy thư mục `images\newitem` lên GitHub | Khi có ảnh vật phẩm mới, báo Claude để gắn vào game |

Mã GM: mở `http://localhost/gm/gm.php` **ngay trên máy chủ** để tạo mã lần đầu; quên mã chạy `php gm\reset_code.php`.

---

## 1. Tính năng / cân bằng

### 1.1 Tuổi & Thọ Nguyên (mới)
- **Tuổi theo giờ thật**, kể cả offline: 2 giờ = 1 tuổi. Tuổi khởi đầu 0; nhân vật cũ bắt đầu 0 tuổi ở lần vào game đầu sau cập nhật.
- **Thọ nguyên** = gốc cảnh giới (tăng dần theo cấp trong cảnh giới) × (1 + % Huyết Mạch / Thể Chất / Thiên Phú / Linh Căn, trần +50%).
- Bảng gốc (chủ dự án chốt): Nhập Môn 60 · Luyện Thể 100 · Thối Thể 120 · Tụ Thân 150 · Tụ Khí 180 · Ngự Khí 210 · Luyện Khí 250 · Trúc Cơ 500 · Kim Đan 1.000 · Ngưng Anh 1.500 · Nguyên Anh 2.000 · … Phi Thăng ×2,8, Tiên giới ×1,6/cảnh giới.
- Còn ≤ 20 / 10 / 5% ⇒ tu luyện + bế quan **−20 / −30 / −40%**. Hết ⇒ **Tọa Hóa** (khoá tu luyện, quái, boss, PK, đột phá, tháp, đấu trường, hầm mỏ, sào thú).
- Đan: Diên Thọ Đan (121, tuổi −10% thọ nguyên) · Đại Tỉnh Hồi Hồn Đan (122, sống lại còn 15%) · Cửu Chuyển Hoàn Hồn Đan (123, sống lại còn 50%). Mua ở trang Thọ Nguyên, giá theo cảnh giới.
- **Nguồn Diên Thọ Đan miễn phí:** mốc online 240 phút (1/ngày) · Tỏa Yêu Tháp tầng 50/100/150 (1/tầng) · Vấn Đỉnh Đài top 1: 3, top 2–3: 2, top 4–20: 1 / ngày · giftcode / phát quà GM.
- Hệ quả mô phỏng: người chơi thường Tọa Hóa lần đầu khoảng ngày 16–34 nếu không dùng đan (chủ dự án chấp nhận — "phải cho khó").
- GM: tab Nhân vật có khung ⏳ Thọ Nguyên (xem, đặt % còn lại để hồi sinh, đặt / cộng tuổi, ghi nhật ký).
- Code: `class/function/thonguyen.php`, `class/config/thonguyen_config.json`, `game/thonguyen.php`; test `tests/test_thonguyen.php`.

### 1.2 Giftcode tân thủ
- Xoá `vip999`, `vip888`, `vip666`, `vip666888`, `vip666sss`.
- TANTHU 50 viên chia thành 5 mã × 10 Tân Thủ Tu Vi Đan kèm Thể Lực Đan, đan HP, vật phẩm cơ bản, 2.000–3.000 LT: `tanthu` · `nhapmon` · `luyenkhi` · `tuchan` · `daotam` (ngày tuổi ≤ 3, cấp ≤ 300). `nhomtele` giữ nguyên.

### 1.3 Trang GM
- Tạo mã GM lần đầu ngay trên trang (chỉ tại localhost), đổi mã ở tab 🔑 Mã GM, quên mã `php gm/reset_code.php`.

---

## 2. Giao diện — ảnh / biểu tượng làm trọng tâm

Quy ước chung: ô vật phẩm `item-art` (viền + phát sáng theo màu phẩm `--ra`), biểu tượng chọn theo tên vật phẩm `\player\item_emoji()` / sủng vật `\player\pet_emoji()` (`class/function/daoju.php`), khung lớn trang chi tiết `zb-art`.

| Nhóm | Trang |
|---|---|
| Nhân vật | Trang cá nhân (huy hiệu tuổi, Chiến sĩ · Cấp · Tuổi 1 dòng, thanh Thọ nguyên), Trang Bị, xem người chơi khác (bỏ khung cuộn), Thần Trang |
| Túi đồ | Trang bị, Đạo cụ, Dược phẩm, Đan dược, Kỹ năng (biểu tượng + số lượng ở góc) |
| Mua bán | Cửa hàng, Thiên Cơ Các, Phường Thị, Bảo khố Tông Môn |
| Tu luyện | Tu Luyện (biểu tượng ô chỉ số), Đột Phá (biểu tượng ô cộng tỉ lệ), Công pháp, Bí tịch, Thiên Phú, Linh Căn / Huyết Mạch / Thể Chất (nhãn ✅ Đủ / ❌ Thiếu) |
| Chế tác | Luyện Đan, Chế Tạo |
| Hoạt động | Nhiệm vụ (mọi tab), Nhiệm vụ NPC, Tông Môn (ảnh đại diện, khung Nhậm chức 3 nhóm), Vấn Đỉnh Đài (ảnh đối thủ, huy chương Top 3), Tỏa Yêu Tháp, Sào Thú, Hầm Mỏ |
| Thế giới | Bản đồ (biểu tượng quái / Boss / NPC / người chơi), Thế giới (biểu tượng khu vực), Boss, Xếp hạng (bục vinh danh Top 1–3) |
| Trang chi tiết | Trang bị, trang bị hệ thống, đạo cụ, dược phẩm, đan dược, phù lục (làm lại từ HTML thô), sủng vật, quái, nhiệm vụ đang nhận, Thọ Nguyên |
| NPC | Đổi tên, hồi máu / chữa bệnh (bỏ chữ tiếng Trung còn sót), bán dược phẩm, đổi phù lục |
| Popup | Kiểu mới **⏳ Vui lòng chờ** (`GameAlert` type `wait`) cho thông báo chưa tới giờ ở Tông Môn; Đột Phá "Tu vi chưa đầy" dùng kiểu thông báo thay vì lỗi |

Đã rà toàn bộ `game/*.php`: không còn trang HTML thô. Mẫu NPC không dùng (`npc/muban/cs.php`, `mpgly.php`, các file tên tiếng Trung) để nguyên.

---

## 3. Sửa lỗi

| Lỗi | Sửa |
|---|---|
| `sql/fix_mojibake.php` bỏ sót cột `utf8mb3` (MariaDB 10.x / MySQL 8) nên vẫn báo "DB sạch" | Quét cả `utf8mb3`; máy thử sửa thêm 2.380 dòng (tên tông môn, khu vực, nhiệm vụ tông môn) |
| Tu luyện võ công hiện chữ dính "Không có võ côngChua hoc vo cong" | Bỏ tên mặc định khi chưa học |
| Tỉ lệ đột phá "+10%" bị ngắt dòng | Cụm "Tỉ Lệ Đột Phá +10%" giữ 1 dòng |
| Test e2e còn dùng giftcode `vip666sss` đã xoá | Đổi sang `daotam` |

---

## 4. Test (máy chủ thử, MariaDB 10.11 + PHP 8)

| Test | Kết quả |
|---|---|
| `tests/test_e2e_full.php` | 264–267 mục, ALL PASS (1 lần "boss mất máu sau 3 lượt" chập chờn, chạy lại PASS) |
| `tests/test_thonguyen.php` | ALL PASS |
| `tests/test_tanthu.php`, `tests/test_dot_c.php` | ALL PASS |
| `tests/test_gm.php` (cần `GM_TEST_CODE`) | ALL PASS |

Các file `.bat` chưa chạy được trên Windows thật (máy thử là Linux).

---

## 5. Còn để ngỏ

- Gắn ảnh thật từ `images/newitem` cho đạo cụ / đan dược (đang dùng biểu tượng emoji) — chờ ảnh đẩy lên.
- Giá đan thọ nguyên so với thu nhập LT người chơi nhiều giờ: chưa mô phỏng lại sau khi chốt bảng thọ nguyên "khó".
- Thông báo trước cho người chơi về Thọ Nguyên; cân nhắc phát 3–5 Diên Thọ Đan cho mọi nhân vật lúc cập nhật (tab Phát quà GM).
- Bảo mật: đổi mật khẩu MySQL (đã lộ trong chat), giữ repo Private.
