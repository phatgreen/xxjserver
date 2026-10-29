# TỔNG HỢP THAY ĐỔI — phiên 2026-10-28 / 29

> Nhánh `claude/tong-quan-du-an`, commit `3ba1f3e` → `8a98596`.
> Chi tiết từng phần: giao diện `UI_GON_GIAO_DIEN_2026-10-29.md` · Tông Môn `DESIGN_TONGMON_CANBANG.md` ·
> test + lỗi `BAO_CAO_TEST_TOAN_BO_2026-10-29.md` · Tàn Quyển `DOT_D_TIENDAO_TANQUYEN.md` · sổ bàn giao `BAN_GIAO_PHIEN.md`.

---

## 0. Việc BẮT BUỘC trên máy chủ

Theo thứ tự, mỗi migration chạy `--dry-run` xem trước rồi mới `--apply`. **Sao lưu DB trước.**

```
cd /d D:\XXJserver\phpstudy_pro\WWW
cron\capnhat_code.bat
"D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup_truoc_migrate.sql
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\<tên migration>.php --dry-run
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\<tên migration>.php --apply
```

| # | Migration | Làm gì | Trạng thái máy chủ |
|---|---|---|---|
| 1 | `migrate_tongmon.php` | Cột khóa nhận thưởng Tông Môn (online / boss / nhiệm vụ) | đã chạy 2026-10-28 |
| 2 | `migrate_wugong_dedup.php` | Gộp dòng võ công trùng, bí tịch âm → 0, UNIQUE `(sid, wgid)` | **chưa chạy** |
| 3 | `migrate_default_cols.php` | Gắn DEFAULT cho ~300 cột NOT NULL thiếu mặc định (số → 0, chuỗi → '', TEXT/ngày → NULL); `midguaiwu.sid` → VARCHAR(32) DEFAULT '' | **chưa chạy** |
| 4 | `migrate_daoju_uid.php` | Sửa dòng `playerdaoju.uid = 0` (gán uid đúng chủ / gộp số lượng) | **chưa chạy** |
| 5 | `migrate_fangshi_sid.php` | `fangshi_zb.sid` / `fangshi_sd.sid` INT → VARCHAR(32) | **chưa chạy** |

Sau đó: Ctrl+F5 (CSS hiện tại `css/mystyle.css?v=2026102954`), chạy kiểm tra:
`tests\test_player_stats.php`, `tests\test_wugong_dedup.php`, `tests\test_vocong_bosung.php`, `tests\test_tongmon.php`.
`tests\test_e2e_full.php` gọi game qua HTTP — sửa `E2E_BASE` (mặc định `http://127.0.0.1:8091`) cho đúng máy trước khi chạy.

---

## 1. Giao diện — làm gọn toàn bộ (desktop vừa 1 màn hình 1920×880)

Nguyên tắc: đối tượng chính làm **trung tâm** (khung `.cws-*`), chỉ số 2 bên, danh sách dạng lưới ô; bỏ phần trùng
(Linh/Ma thạch đã ở thanh trên, nút Trở Về / Đại Sảnh, tab Nhân Vật đã ở thanh dưới). Logic PHP không đổi trừ khi ghi rõ.

| Nhóm | Trang |
|---|---|
| Khung chung | Bỏ đồng hồ cuối trang; thanh dưới không còn `fixed`; Tông Môn / Linh Sủng / Nhiệm Vụ tab con gọn; chấm đỏ nhiệm vụ đúng từng tab |
| Nhân vật | Tu Luyện, Nhân Vật, Trang Bị (búp bê + Chiến lực + 9 chỉ số), Túi Đồ (lưới ô + bảng thao tác, chia trang), Võ Công, Bí Tịch, Chế Tạo, Luyện Đan, Chi tiết trang bị (Ngũ Hành 5 ô) |
| Tu thân | Công Pháp, Linh Căn (vòng Ngũ Hành giữa), Thể Chất, Huyết Mạch (sửa khung Biến Thân vỡ chữ), Thiên Phú — tab lên đầu, bỏ tab Nhân Vật |
| Giao thương | Cửa Hàng (lưới 5 cột, 15 món/trang), Thiên Cơ Các (lưới theo giai) — **chung hàng 5 tab** Đan Dược / Đạo Cụ / Trang Bị / Võ Công / Công Pháp |
| Hành tẩu | Đại Sảnh = khung **la bàn** (khu vực giữa, Bắc/Nam/Tây/Đông đúng hướng), bỏ marquee trùng; 12 nút Tiên Đồ thấp lại |
| Đợt cuối | Bản Đồ, Vấn Đỉnh Đài, Sào Thú, Xếp Hạng (2 cột), Đột Phá, Bảng Nhiệm Vụ, Thần Trang, Xem nhân vật khác; bỏ Trở Về / Đại Sảnh ở 13 trang |
| Thông báo Cơ Duyên | Trước nổi đè thanh trên ⇒ giữa màn hình lệch lên (top 28%), bấm để đóng |

Bảng chi tiết từng trang + mẹo CSS (`a.x:link` đè `a:link{display:inline-block}`, rule có `!important`, `min-height` thắng `max-height`, style inline `!important`): `UI_GON_GIAO_DIEN_2026-10-29.md`.

---

## 2. Tính năng / cân bằng

| Việc | Nội dung | File chính |
|---|---|---|
| Tông Môn | Lợi ích thật theo cấp, chi phí nâng cấp mới, khóa nhận thưởng trong DB, EXP tông môn từ tu luyện 1% | `class/player.php`, `game/club.php`, `docs/DESIGN_TONGMON_CANBANG.md` |
| Võ Công bổ sung | 24 bộ mới CG 48 / 60 / 69 / 78 (wgid 111–134), chỉ số nội suy giữa 2 mốc cùng hệ, đổi 15/25/35 Hỗn Nguyên, 45 Đại Đạo Tàn Quyển | `tools/gen/vc_bosung_build.py`, `class/config/wugong_*.json` |
| Công Pháp cao | CG ≥ 30 không bán bằng tiền, đổi Tàn Quyển theo vùng boss (Tiên Đạo < 42, Hỗn Nguyên 42–71, Đại Đạo ≥ 72) | `tools/gen/dot_d_build.py`, `cp_price_build.py`, `game/thiencoccac.php` |
| "Mua ngay" bí tịch | Giá = giá bộ đó ở Thiên Cơ Các, đúng loại tiền (LT / MT / Tàn Quyển); shop không bán ⇒ giá thấp nhất cùng mốc | `\player\wugong_book_price()`, `game/wugong.php` |
| Tên ô trang bị | Chế Tạo + mô tả nhiệm vụ dùng bảng chuẩn `zhuangbei_tool_names()` (trước "Yêu Đai" hiện "Dây chuyền"); `nhiemvu_slot_id` khớp 16 ô chuẩn | `game/chetao.php`, `class/function/nhiemvu.php` |
| Thể Chất / Huyết Mạch | **Chốt nhân theo cảnh giới của chính nhân vật** (TC 1+0,15×CG, HM 1+0,18×CG). Trước lẫn lộn ×1 / ×cảnh giới người xem ⇒ chỉ số phần này tăng so với số người chơi từng thấy | `class/function/thechat.php`, `huyetmach.php`, `class/player.php` |
| Chiến lực | Xếp hạng dùng chung `combat_power()`; phần phẩm / giác tỉnh TC, HM **chỉ cộng khi đã có** (trước +450 không có gì) | `game/paihang.php`, `class/player.php` |
| Bạo kích / Hút máu | **Giữ số lẻ chính xác** (2 chữ số); mọi màn đánh tung xác suất bằng `\player\roll_pct()` (chính xác 0,01%; PVE / Boss giữ tỉ lệ = ubj / 2) | `class/player.php`, `game/pve.php`, `boss.php`, `pvp.php`, `vandinhdai.php`, `toayeuthap.php`, `club_boss.php`, `class/function/*_boss.php` |

---

## 3. Sửa lỗi

### Dữ liệu / mất đồ
| Lỗi | Nguyên nhân | Sửa |
|---|---|---|
| Võ công trùng dòng, bí tịch âm | Không có khóa duy nhất, `cqwg()` đọc-rồi-thêm | `INSERT IGNORE` + UNIQUE (migration #2) |
| Trừ tiền mà không có đồ (shop, giftcode, luyện đan, tạo nhân vật, chat) | MySQL strict từ chối INSERT thiếu cột NOT NULL | Migration #3 |
| Đạo cụ "giành chỗ" giữa người chơi | 6 chỗ INSERT `playerdaoju` không kèm uid (khóa `uid+djid`) | Lấy uid theo sid + migration #4 |
| Kho Tông Môn phân phát không tới ai | Hàm nhận UID nhưng dùng như sid | Tra sid thật |
| Treo bán Phường Thị hỏng, in lỗi SQL | `fangshi_*.sid` là INT | Migration #5, không in lỗi SQL |

### Bảo mật
| Lỗi | Sửa |
|---|---|
| Include file tùy ý qua `ym` + XSS `pvpts` / `tpts` (`extract()` ghi đè biến router) | Loại biến nội bộ khỏi tham số URL trước mỗi `extract()` — `game.php` |
| Xuất chiến linh sủng người khác | Kiểm tra chủ — `game/chongwu.php` |
| Tạo nhân vật bằng token tự đặt | Đối chiếu token với `userinfo` — `game.php`, `game/cj.php` |
| Luyện võ công chưa sở hữu | Kiểm tra `wgcx()` — `game/xxwg.php` |

### Hiển thị / nhỏ
Hộp xem nhanh lệch khi zoom 0.75 · vòng sáng chân dung lệch · nút Tu Luyện đè viền · chấm đỏ nhiệm vụ kẹt ·
tạo nhân vật lỗi vẫn hiện lời chào · trang Võ Công vừa nâng cấp thiếu 1 cấp chỉ số (cache `wgcx`) ·
tu luyện hiển thị "Tiêu hao" ≠ số đã trừ · nút Kích hoạt Ngũ Hành lệch / tràn ô.

---

## 4. Test

| Test | Kết quả (cloud, DB strict) |
|---|---|
| `tests/test_e2e_full.php` — nhân vật test mới đi qua toàn bộ tính năng bằng route thật | **266/266 PASS** |
| `tests/test_player_stats.php` — tính lại chỉ số độc lập 3 cấu hình, so `getplayer()` | **ALL PASS** (lệch 0) |
| `test_wugong_dedup`, `test_vocong_bosung`, `test_tongmon`, `test_stamina`, `test_combat_moves`, `test_dot_a_data`, `test_lan2…9_data`, `test_*_combat` | ALL PASS |

Còn lỗi **có từ trước** (số FAIL giống hệt trước / sau sửa): `test_dot_c` 1, `test_drop_cover` 2, `test_huyetmach_schema` 1, `test_kt0` 1,
`test_linhcan_pages` 29, `test_nguhanh_market` 12, `test_tianfu_migration` 16, `test_tianfu_schema` 1.
Nhiều test cũ tự dựng `game1` thiếu `token` nên chỉ chạy được khi DB không strict (`game1` chạm giới hạn kích thước dòng, không gắn DEFAULT được).

---

## 5. Còn để ngỏ

- STAT-7: chỉ số gốc khi lên cấp lệch rất nhỏ do làm tròn cột INT (`upplayerlv`).
- Ảnh võ công: các bộ cấp cao (gồm 24 bộ mới) chưa có ảnh riêng trong `images/wugong/`.
- Công Pháp và Võ Công dùng chung nguồn Tàn Quyển (có thể tách mảnh riêng nếu muốn).
- Mobile: danh sách dài vẫn cuộn (chỉ tối ưu desktop vừa 1 màn hình).
- Bảo mật repo: giữ Private; đổi mật khẩu MySQL (đã lộ trong phiên); `tests/e2e_fixture.php` còn mật khẩu ghi cứng.
