# ĐỢT D — Nguồn võ công Tiên Đạo: Tiên Đạo Tàn Quyển (ĐÃ LÀM 2026-10-28)

> Lập 2026-10-28. Thuộc kế hoạch tổng `PLAN_MO_RONG_NOI_DUNG.md`, nối tiếp quyết định D4 của `DOT_A_VOCONG_CONGPHAP.md`
> ("Tiên Đạo không bán — rơi từ Boss"). Hướng đã chọn: **Tàn Quyển** (chủ dự án chọn 2026-10-28).

## 1. Hiện trạng đã đo trong code

| Mục | Thực tế | Nguồn |
|---|---|---|
| 5 bộ Tiên Đạo | wgid 64–68, `realm_require = 30` (⇒ cần **cấp 1450**), giá LT = MT = 0 | `class/config/wugong_config.json` |
| Thiên Cơ Các | Bỏ qua bộ không có giá (`if ($priceLT <= 0 && $priceMT <= 0) continue;`) ⇒ Tiên Đạo **không hiện, không có nguồn nào** | `game/thiencoccac.php:260` |
| Boss khu vực cao nhất | Vạn Yêu Hoàng 400 · Cửu U Ma Tôn 450 · Thiên Ngoại Thiên Chủ 500; hồi sinh 600 giây; một con dùng chung cả server | bảng `boss`, `mid.ms` |
| Rơi đồ boss | 3 lượt độc lập: trang bị (`bosszb`/`dljv`), đạo cụ (`bossdj`/`djjv`, mỗi lần 3–12 cái), dược (`bossyp`/`ypjv`) | `game/boss.php:575-635` |
| Học võ công | `\player\cqwg($wgid, $sid, $dblj)` — chưa có thì INSERT, có rồi thì +1 `wgsum` | `class/player.php:3065` |
| Đạo cụ | Config JSON, djid lớn nhất **114** (Khai Phú Đan) | `class/config/daoju_config.json` |

**Vướng:** nội dung game mới tới cấp 500, Tiên Đạo cần cấp 1450 ⇒ không thể cho rơi bí tịch học ngay.

## 2. Quyết định thiết kế

| # | Nội dung | Đề xuất |
|---|---|---|
| D1 | Vật phẩm | **1 loại chung** "Tiên Đạo Tàn Quyển" (djid **115**), không chia 5 loại theo từng bộ ⇒ ít vật phẩm, người chơi tự chọn bộ khi đổi, không bị kẹt vì rơi lệch loại |
| D2 | Nguồn rơi | Chỉ 3 boss khu cấp **400 / 450 / 500**. Lượt rơi **riêng**, mỗi lần 1 cái — không nhét vào `bossdj` (sẽ rơi 3–12 cái và chia tỉ lệ với đạo cụ khác) |
| D3 | Tỉ lệ | 400: **3%** · 450: **4%** · 500: **5%** mỗi lần hạ. **Không** cộng may mắn Thiên Phú / Huyết Mạch (giữ hiếm, dễ kiểm soát kinh tế) |
| D4 | Tra boss theo **cấp** (`bosslv`), không theo `bossid` | `bossid` do AUTO_INCREMENT sinh khi migrate ⇒ máy chủ thật có thể khác cloud |
| D5 | Đổi | Thiên Cơ Các hiện 5 bộ Tiên Đạo, giá **10 Tàn Quyển**/bộ. Vẫn cần cảnh giới 30 (cấp 1450) mới đổi được — nút xám + ghi "Cần …" như bộ chưa đủ cảnh giới hiện nay |
| D6 | Thông báo | Khi rơi: dòng "Nhận được: Tiên Đạo Tàn Quyển x1" + thông báo kênh Hệ Thống (cùng kiểu thông báo rơi trang bị quý) |
| D7 | Nhiệm vụ / Cộng Hưởng | Không đổi. Đổi bộ Tiên Đạo vẫn gọi `nhiemvu_main_update(..., 'learn_book')` như mua thường |

### Ước tính kinh tế

- Mỗi boss tối đa 6 lần hạ/giờ ⇒ 3 boss = 18 lần/giờ **cả server**, tỉ lệ trung bình 4% ⇒ ~**0,72 Tàn Quyển/giờ** toàn server.
- 1 bộ = 10 cái ⇒ ~**14 giờ** cả server cày liên tục cả 3 boss mới ra 1 bộ. Người chơi cấp 500 phải đi tiếp ~950 cấp mới dùng được
  ⇒ đủ thời gian tích lũy, mà vẫn hiếm (đúng tinh thần phẩm Tiên Đạo).
- Không sinh Linh Thạch / Ma Thạch mới ⇒ không ảnh hưởng lạm phát.

## 3. Thay đổi code

| File | Thay đổi |
|---|---|
| `class/config/tiendao_config.json` (mới) | `{"djid":115,"need":10,"drop_pct":{"400":3,"450":4,"500":5}}` |
| `class/config/daoju_config.json` | Thêm djid 115 "Tiên Đạo Tàn Quyển" — mô tả: "Gom 10 quyển đổi 1 bộ võ công Tiên Đạo tại Thiên Cơ Các" |
| `game/boss.php` | Sau lượt rơi dược (`yp:`): 1 khối ~15 dòng — tra `drop_pct[bosslv]`, `mt_rand(1,100) <= pct` ⇒ `adddj(115, 1)` + thông báo |
| `game/thiencoccac.php` | Danh sách: không bỏ qua bộ có `need` Tàn Quyển, giá hiện "10 Tàn Quyển (đang có X)". `buy_wg`: nếu là Tiên Đạo ⇒ kiểm tra cảnh giới + số Tàn Quyển, trừ bằng `deledjsum(115, 10)` thay vì trừ tiền |
| `tools/gen/dot_d_build.py` (mới) | Sinh/ghi 2 file config ở trên (chạy lại cho cùng kết quả, đúng quy ước `tools/gen/`) |
| `tests/test_dot_d.php` (mới) | Kiểm thử mục 4 |

| `game/shangdian.php` | Cờ `no_shop` trong `daoju_config.json`: Thương Điếm không bày bán và chặn cả lệnh mua gọi thẳng URL (trước đây tiệm bán **mọi** đạo cụ trừ 112/113) |
| `class/function/wugong.php` | `tiendao_config()`, `tiendao_need($wgid)` |

**Không cần migrate DB** (đạo cụ và cấu hình đều là JSON; `playerdaoju` đã có sẵn).

## 4. Kiểm chứng (khi làm)

1. Hạ boss cấp 400/450/500 nhiều lần (ép tỉ lệ 100% trong test) ⇒ nhận đúng 1 Tàn Quyển/lần; boss cấp khác ⇒ không rơi.
2. Thiên Cơ Các: thấy 5 bộ Tiên Đạo, giá 10 Tàn Quyển.
3. Đổi khi chưa đủ cấp 1450 ⇒ báo lỗi, không mất Tàn Quyển. Đủ cấp nhưng thiếu quyển ⇒ báo lỗi.
4. Đủ cấp + đủ 10 quyển ⇒ học được bộ, trừ đúng 10, nhiệm vụ "mua bí tịch" vẫn tính.
5. Chụp màn hình Thiên Cơ Các và popup rơi đồ trên server thử.

## 5. Câu hỏi cần chủ dự án chốt

| # | Câu hỏi | Đề xuất mặc định |
|---|---|---|
| Q1 | Tỉ lệ 3/4/5% và cần 10 quyển/bộ có ổn? | Như trên |
| Q2 | Tên vật phẩm | "Tiên Đạo Tàn Quyển" |
| Q3 | Học trùng bộ đã có thì sao? | Như mua thường hiện nay (`cqwg` +1 `wgsum`) — không chặn |

## 6. Kết quả kiểm chứng (cloud, 2026-10-28)

| Kiểm tra | Kết quả |
|---|---|
| `tests/test_dot_d.php` | ALL PASS (cấu hình, đạo cụ 115, 5 bộ đổi 10 quyển, trừ quyển thiếu/đủ) |
| Thương Điếm tìm "Tàn Quyển" / mua thẳng URL djid 115 | Không thấy / không mua được |
| Thiên Cơ Các cấp 1000 + 10 quyển | "Cần đạt Địa Tiên mới có thể mua!", không mất quyển |
| Cấp 1460 + 9 quyển | "Không đủ Tiên Đạo Tàn Quyển! (cần 10, đang có 9)", không mất quyển |
| Cấp 1460 + 10 quyển | Học được Thái Thượng Trảm Đạo Kiếm, còn 0 quyển |
| Hạ boss cấp 500 (tạm đặt 100% ở bản sao chạy thử) / boss cấp 350 | Nhận "Tiên Đạo Tàn Quyển x1" + thông báo Hệ Thống / không rơi |
| Test cũ A, B, C, F, combat, Cộng Hưởng | ALL PASS |

### Lỗi có sẵn phát hiện khi test (chưa sửa — chờ chủ dự án)

`game/boss.php:66-72`: boss đã chết mà gọi lại link tấn công thì boss "bỏ trốn" và **hồi đầy máu ngay**, bỏ qua thời gian hồi
sinh `mid.ms` (600 giây) mà `nowmid.php:125` vẫn tôn trọng ⇒ có thể hạ boss liên tục, thưởng (Linh Thạch, Ma Thạch, trang bị,
nay thêm Tàn Quyển) không bị giới hạn bởi 600 giây. Ước tính kinh tế ở mục 2 chỉ đúng khi lỗi này được sửa.
