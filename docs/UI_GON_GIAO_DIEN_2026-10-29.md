# LÀM GỌN GIAO DIỆN — phiên 2026-10-28 / 29

> Nhánh `claude/tong-quan-du-an`, commit `3ba1f3e` → `bc74ebe`. Tông Môn có tài liệu riêng: `DESIGN_TONGMON_CANBANG.md`.
> **Máy chủ:** chỉ cần `cron\capnhat_code.bat` + Ctrl+F5 — trừ Tông Môn phải chạy `sql\migrate_tongmon.php --apply` (đã chạy 2026-10-28).
> CSS: `css/mystyle.css?v=2026102954`, `js/itemtip.js?v=2026102902`, `js/bagpop.js?v=2026102901` (khai báo trong `game.php`).

## 1. Mục tiêu chung

- Desktop (1920×880, đã trừ thanh trình duyệt) **vừa 1 màn hình, không cuộn**; mobile 390px vẫn cuộn nhưng không tràn ngang.
- **Nhân vật / đối tượng làm trung tâm**: khung "sân khấu" ảnh giữa, chỉ số 2 bên (mẫu là trang Tu Luyện).
- **Bỏ phần trùng**: Linh/Ma thạch (đã ở thanh trên), nút Đại Sảnh / Trở về / Túi đồ (đã ở thanh dưới + tab con).
- Mọi CSS mới đặt **cuối `mystyle.css`** dưới class riêng của trang (`pf-compact`, `cw-compact`, `bag-compact`, `tb-compact`, `shop-compact`, `tcc-compact`) +
  selector dài / `!important` để đè rule cũ mà không ảnh hưởng trang khác. Logic PHP (mặc, tháo, bán, phân giải…) **không đổi**.

## 2. Khung chung (`game.php`, `class/function/dieu_huong.php`)

| Việc | Chi tiết |
|---|---|
| Bỏ đồng hồ cuối trang | Xóa `#CurrentTime` + `changetime()` (setInterval 1s); ẩn khung `.footer` rỗng |
| Thanh dưới 5 tab | Bỏ `position:fixed` — nằm cuối `.tcg-page`, bo 4 góc, không che nội dung |
| Số Linh/Ma thạch thanh trên | Gắn `id="yxb_val"` / `czb_val` — trang Tu Luyện cập nhật live khi tu luyện x2/x3 |
| Tab Linh Sủng | Bỏ tab con Linh Sủng / Sào Thú; lệnh `sauthu` chuyển sang tab Hành Tẩu (vào từ Phó Bản) |
| Nhiệm Vụ | Chấm đỏ từng tab con (`nav_quest_tab_dots`), bỏ dòng đánh dấu `daily_bonus` khỏi điều kiện chấm đỏ (trước bị kẹt), chương chính tuyến đã xong ẩn hẳn |
| Đại Sảnh | Bỏ 3 thanh Khí huyết / Thể lực / Tu vi (đã có ở trang Nhân Vật) |

## 3. Từng trang

| Trang | File | Bố cục mới |
|---|---|---|
| Tu Luyện | `game/xiulian.php` | Cảnh giới + thanh tiến độ ở đáy khung ngồi thiền; Tiên Đồ 6 nút chia 3 mỗi bên; chỉ số 5×2; chế độ x1/x2/x3 → nút tròn (cách 26px để vòng xoay không đè) |
| Nhân Vật | `game/zhuangtai.php` | Chân dung giữa (vòng sáng `pfRing`), Chiến lực trên, 3 chỉ số mỗi bên, tên + cảnh giới + danh hiệu dưới; Khí huyết + Thể lực 1 hàng, Tu vi 1 hàng riêng; Linh Căn / Thể Chất / Huyết Mạch ô nhỏ |
| Trang Bị | `game/trangbi.php` | Búp bê nhân vật: 8 ô "Trang bị" trái, chân dung + Chiến Lực + Trang bị nhanh giữa, 8 ô "Vũ khí – Phù – Pháp bảo" phải; ô = ảnh + tên + nút × tháo; dưới là bảng 9 chỉ số (Công, Thủ, Khí huyết, Bạo, Hút máu, Đột phá %, Tu luyện %, tổng cường hóa, Đã mặc x/16) |
| Linh Sủng | `game/chongwu.php` | Sủng vật xuất chiến giữa khung (ảnh chung `images/sungvat/san_khau_linh_thu.jpg`), 3 chỉ số mỗi bên, thanh Khí huyết + Kinh nghiệm; danh sách thẻ nhỏ, desktop 6 con/trang (mobile 5); trang chi tiết cùng khung, `exit()` → `return` để còn thanh dưới |
| Túi Đồ (5 mục) | `game/bag{zb,dj,yp,jn,yd}.php`, `js/bagpop.js` | Banner 1 hàng; **lưới ô** (desktop 5 cột, mobile 3): ảnh/số lượng + tên + phẩm; **bấm ô → bảng nổi** gồm thông tin (trang bị dùng lại nội dung hộp xem nhanh: chỉ số, so sánh, cảnh báo cảnh giới) + nguyên các nút cũ (Trang bị / Phân Giải / Dùng / Bán / Xem / Sử dụng). Trang bị 20 món/trang, Đạo cụ chia trang 20 loại (giữ trang sau khi bán / dùng) |
| Cửa Hàng | `game/shangdian.php` | Banner thấp 74px; tab Linh/Ma thạch + ô tìm kiếm 1 hàng; **lưới ô** (desktop 5 cột, mobile 3): ảnh + tên đủ 2 dòng + giá gọn (💰 15.000, đơn vị đầy đủ ở title) + nút Mua; 15 món/trang (trước 10); bỏ nút Trở Về / Đại Sảnh; thêm tab Võ Công / Công Pháp (mở Thiên Cơ Các, 2 trang chung hàng 5 tab) |
| Thiên Cơ Các (Võ Công / Công Pháp) | `game/thiencoccac.php` | Banner thấp giống Cửa Hàng (cảnh giới ghi ở dòng phụ); hàng lọc 1 dòng cuộn ngang; mỗi giai / loại là lưới ô (desktop 4 cột, mobile 2): tên + phẩm/hệ + yêu cầu + giá/nút mua (2 loại tiền xếp dọc); bỏ Trở về / Đại sảnh. Lưu ý: ô không được `display:…!important` vì bộ lọc ẩn bằng `style.display='none'` |
| Chế Tạo | `game/chetao.php` | Khung `.cws-*`: thành phẩm công thức đang chọn ở giữa (ảnh trang bị / 💎 đạo cụ), trái Tỷ lệ · Yêu cầu cấp · Loại, phải tối đa 3 chỉ số; dưới tên là nguyên liệu + nút CHẾ TẠO (thiếu nguyên liệu thì nút nhạt). Mặc định chọn công thức đầu; 24 công thức dạng lưới ô (desktop 4 cột, mobile 2); bỏ banner, nút Nhân Vật (trùng hàng tab), Trở về / Đại Sảnh |
| Công Pháp | `game/congphap.php` | Khung `.cws-*`: công pháp đang vận đầu tiên ở giữa, trái Hiệu ứng · Loại, phải Cấp · Kinh nghiệm · Giảm Công/Thủ (hoặc số bộ sở hữu); bộ đang vận khác hiện nhãn dưới tên; danh sách 2 cột; bỏ banner, Trở Về / Đại Sảnh |
| Luyện Đan | `game/luonden.php` | Thêm tab Chế Tạo / Luyện Đan; khung `.cws-*`: đan đang chọn giữa (biểu tượng loại), trái Cấp sư · Kinh nghiệm · Luyện thành, phải Tỷ lệ · Phẩm · Độ khó/Đan độc, dưới tên nguyên liệu + nút LUYỆN ĐAN; mặc định chọn công thức đầu đủ điều kiện; phản phệ 1 hàng; 17 công thức lưới 4 cột (mobile 2, ẩn nhãn phẩm trong ô); bỏ banner, ô thống kê, Trở Về / Đại Sảnh. Rule `.event-task-*` cũ dùng `!important` nên rule mới cũng phải `!important` |
| Chi tiết trang bị | `game/zbinfo.php` | Khung `.cws-*`: ảnh giữa, trái Công · Thủ · HP, phải Bạo kích · Hút máu · Đột phá/Tu luyện; dưới tên: nhãn phẩm/hệ + cấp + yêu cầu cảnh giới + mô tả; thao tác 1 hàng (bỏ tiêu đề); Cường hóa 2 cột + 5 nút 1 hàng; Ngũ Hành 5 ô ngang (mobile 2); bỏ Trở Về / Đại Sảnh |
| Linh Căn | `game/linhcan.php`, `linhcan_quest.php`, `linhcan_boss.php` | Thanh tab 3 trang lên đầu (cùng vị trí), bỏ tab Nhân Vật; trang Luyện Căn: vòng Ngũ Hành giữa (300px) + nút Luyện/Đột phá, Thuộc tính Kích Hoạt trái, Đột Phá + kỹ năng phải (mobile: vòng trên, 2 cột dưới) |
| Thể Chất / Huyết Mạch / Thiên Phú | `game/thechat.php`, `huyetmach.php`, `huyetmach_boss.php`, `tianfu.php`, `tianfu_collection.php`, `tianfu_open.php` | Thanh tab lên đầu (trang chính có tab riêng thay cho tab Nhân Vật đang "on"), bỏ tab Nhân Vật; desktop: tab 1 hàng (biểu tượng cạnh chữ, cả Linh Căn) — mobile giữ kiểu xếp dọc. Huyết Mạch: khung Biến Thân sửa vỡ chữ (trạng thái `nowrap` ép tên thành cột) và trải ngang dưới 2 cột kèm nút. Thiên Phú desktop: ảnh 2:1, ô kỹ năng 3 cột. Desktop: Thể Chất 771px, Huyết Mạch 873px (trước 1.031), Thiên Phú 880px (trước 976) |
| Hành Tẩu (Đại Sảnh) | `game/nowmid.php` | Khung la bàn `.mid-stage`: tên khu vực + an toàn/PVP + tên nhân vật ở giữa, Bắc/Nam/Tây/Đông đặt đúng hướng (hướng không có lối để trống); bỏ dòng chữ chạy (trùng dòng Hệ thống đầu khung chat); quái / boss / NPC, chat, Tiên Đồ giữ nguyên dưới khung |
| Đợt cuối (CSS, desktop) | `qydt` Bản Đồ, `vandinhdai`, `sauthu`, `paihang`, `tupo`, `taskguide`, `taozhuang`, `otherzhuangtai` | Bỏ cặp Trở Về / Đại Sảnh ở 13 trang (`.qydt-bottom`, `.rank-bottom`, `.break-bottom`, `.news-task-bottom`, `.tz-actions`, `.tcg-club-rank-bottom`, `.allmap-bottom`, `.im-bottom`, `.tcg-chat-bottom`, `.gift-bottom`, `.tcg-market-bottom`, `.event-bottom`, `.tcg-profile-bottom`; GIỮ `.tcg-club-bottom` vì trang con Tông Môn dùng để về trang chính). Banner thấp + mô tả 1 dòng; Bản Đồ: ảnh 180px (bỏ `min-height:260px`), bỏ nhãn "Khu N" lặp, sửa mũi tên đè chữ; Sào Thú: thẻ 1 hàng 2 cột (`sauthu.php` sửa padding inline 32→16px); Xếp Hạng 2 cột; Vấn Đỉnh Đài: bỏ `min-height:350px` banner, đối thủ 1 dòng, ẩn nút Đại Sảnh (giữ Làm mới); Xem nhân vật khác: trang bị 3 cột. Tất cả ≤ 880px |
| Tính năng chưa mở | `game/feature_locked.php` | Sửa chữ 2 nút bị lệch (thêm `a.fk-lock-btn:link`) |

## 4. Lỗi đã sửa kèm

| Lỗi | Nguyên nhân | Sửa |
|---|---|---|
| Hộp xem nhanh vật phẩm lệch xa khi rê chuột (desktop) | `body{zoom:.75}` khi màn ≥ 1024px: rect tính px màn hình, `left/top` của hộp bị nhân thêm zoom | `js/itemtip.js` đo hệ số zoom thực trên hộp rồi chia lại |
| Vòng sáng chân dung Nhân Vật lệch trái | Dùng lại keyframes `xlRing` có `translateX(-50%)` | Keyframes riêng `pfRing` |
| Nút Tu Luyện đè viền hàng chế độ | Vòng xoay lấn ~11px, khoảng cách chỉ 12px | `margin-top:26px` |
| Chấm đỏ Nhiệm Vụ sáng mãi | Dòng `daily_bonus` ghi `completed=1, claimed=0` | Loại khỏi điều kiện |
| Rule chung `a:link{display:inline-block;margin}` (`gamecss.css`) | Mạnh hơn class nút ⇒ mất flex, lệch chữ | Luôn khai báo kèm `a.x:link,a.x:visited` cho nút mới |

## 5. Ghi nhớ khi sửa tiếp

- Ảnh chân dung chỉ có `images/avatar_nam.png` / `avatar_nu.png` (256×256) và 1 ảnh linh thú chung — có ảnh toàn thân / từng loài thì chỉ cần đổi đường dẫn.
- `js/itemtip.js` mở `window.tcgItemTip.html(el)` / `.hide()`; chạm ô trong `.bag-compact .bag-item-card` không mở hộp xem nhanh (để `bagpop.js` xử lý).
- Kiểm tra trên cloud: `bash tools/cloud/sync_run.sh`, server `127.0.0.1:8091`, chụp viewport 1920×880 + 390×844 (đo `.tcg-bottom-nav` bottom ≤ 880).
- Còn để ngỏ: mobile các danh sách dài vẫn cuộn; thanh "Đột Phá" to đầu trang (hiện khi đủ tu vi) chưa thu nhỏ.
