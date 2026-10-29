# BÀN GIAO PHIÊN LÀM VIỆC — đọc file này đầu tiên khi mở phiên mới

> Cập nhật 2026-10-28. Nhánh làm việc: **`claude/tong-quan-du-an`** (repo `phatgreen/xxjserver` — **hiện đang Public, cần chuyển Private + đổi mật khẩu MySQL**).
> Tổng quan kiến trúc: `docs/TONG_QUAN_DU_AN.md`. Kế hoạch mở rộng tổng: `docs/PLAN_MO_RONG_NOI_DUNG.md`.

## 1. Bối cảnh & quy ước

- **Game:** H5 Tu Tiên "Tu Chân Giới", PHP 5.6 + MySQL 5.5 (phpStudy, Windows). Thư mục máy chủ chủ dự án: `D:\XXJserver\phpstudy_pro\WWW`.
  PHP 5.6: `D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe`; mysqldump: `...\Extensions\MySQL5.5.29\bin\mysqldump.exe`.
- **Game đang phát triển, CHƯA mở cho người chơi** ⇒ được phép đổi cân bằng / dữ liệu, không cần giữ tương thích người chơi cũ.
- **Chủ dự án muốn:** trả lời Tiếng Việt, comment code Tiếng Việt; thay đổi nhỏ nhất; không tự refactor; việc lớn thì
  **viết thiết kế (docs/…) → chủ dự án duyệt → làm → kiểm chứng thật (test + chụp màn hình)**; không để thưởng quá nhiều (tránh tăng cấp nhanh).
- **Bảo mật:** `pdo.php` (mật khẩu DB) và `docs/db/game.sql` (dữ liệu tài khoản) nằm trong repo ⇒ **repo phải luôn Private**.
  Không in mật khẩu ra chat. `tests/test_linhcan_*.php` có mật khẩu cứng (có từ trước).
- **Commit:** kết thúc bằng 2 dòng `Co-Authored-By: Claude …` + `Claude-Session: …` theo nhắc của hệ thống; không ghi tên model trong code/commit.
- **Dữ liệu sinh tự động:** mọi file config lớn sinh bằng `tools/gen/*.py` (chạy lại cho cùng kết quả). Sửa số thì sửa trong generator rồi chạy lại.

## 2. Đã làm trong các phiên trước (theo thứ tự)

| Hạng mục | Tóm tắt | Tài liệu |
|---|---|---|
| Võ công / công pháp (đợt A) | Thế võ 3 bậc (Võ Kỹ / Tuyệt Kỹ / Tuyệt Chiêu) có tên + sát thương + hồi chiêu, quái/boss phản kích, boss tụ lực/phá thế, Cộng Hưởng 6 nhóm; +30 võ công, +28 công pháp | `DOT_A_VOCONG_CONGPHAP.md`, `PLAN_VOCONG_CONGPHAP.md` |
| Quái & khu vực tới cấp 500 (đợt B) | 84 quái mới (mã 200–283), 5 khu mới (qy 32–36) + khu 27/28/30/31, 9 boss khu; ảnh bản đồ `dt/ditu/31–36.png` | `DOT_B_QUAI_CANH_GIOI.md` |
| Giao diện chiến đấu | `pve.php` / `boss.php`: màn **Chiến Trường** mô phỏng từng đòn (`js/battle_sim.js` dùng chung), kết quả thắng/thua dạng **popup** (đưa ra `<body>` vì khung có backdrop-filter), báo tử vong ngay lượt bị hạ | — |
| Nhiệm vụ (đợt C) | Thưởng theo % thanh cấp (`nhiemvu_reward_rules.json`), chính tuyến 13 chương (cấp 1–500), 9 NPC khu mới (mã 40–48, NV 70–96), tab **Trực Tuyến** + **Bế Quan** (thưởng offline), NPC hiện ở Đại Sảnh, chính tuyến thu gọn theo chương; sửa chuỗi NPC tân thủ 41–60 (sai loại, không NPC giao, mục tiêu sai) | `DOT_C_NHIEM_VU.md` |
| Trang bị (đợt F) | **Đột phá cộng số cố định** (bản cũ lũy tiến 1%/5% ⇒ máu vô hạn), 215 món đủ 15 ô ở 11 bậc + bậc 500 "Hồng Mông", phẩm chất theo bậc, cường hóa +8% chỉ số gốc, Thần Trang 15 món, chế tạo đúng cấp, cân lại quái/boss đợt B | `DOT_F_TRANG_BI.md` |
| Kinh tế + thể lực + trang bị (đợt G, 2026-10-28) | Lộ Trình Cảnh Giới, trang Nhiệm Vụ gọn, MT boss chặn chênh ≥ 35, thể lực 100+cấp / hồi 1 phút / trừ theo thế, tiệm chỉ bán đồ bậc ≤ 30, đủ 15 ô có nguồn rơi (quái thường / boss quý), mô phỏng kinh tế `tools/sim/econ_sim.py` — **đã triển khai máy chủ thật** | `DOT_G_KINH_TE_THE_LUC_TRANG_BI.md` |
| Sửa lỗi kèm | Đường dẫn config `__DIR__ . '../../config'` thiếu `/` (renwu/daoju/yaodan/wugong — hỏng trên Linux); Thiên Cơ Các mua sách / tên cảnh giới; công pháp vận nhiều bộ | — |

**Máy chủ chủ dự án đã chạy xong (2026-10-28):** `migrate_dot_b.php --apply`, `migrate_dot_c.php --apply`, `migrate_dot_f.php --apply`;
test B / C / F đều PASS (test C D7 đã sửa lệch múi giờ PHP–MySQL sau đó — cần kéo code về chạy lại xác nhận).

### Phiên 2026-10-28 (Claude Code cloud) — đã đẩy lên nhánh

| Hạng mục | Tóm tắt | Tài liệu |
|---|---|---|
| Cấp tối đa 4300 | Lên đúng 4300 "Vô Ngã Viên Mãn — Đại Đạo Đỉnh Phong"; vá lỗi cấp 4299 đột phá miễn phí cộng chỉ số vô hạn | — |
| Đợt D | Tiên Đạo Tàn Quyển (djid 115): boss 400/450/500 rơi 3/4/5%, đổi 10 quyển/bộ ở Thiên Cơ Các; tiệm không bán (`no_shop`) | `DOT_D_TIENDAO_TANQUYEN.md` |
| Boss | Chặn hạ lại boss trước thời gian hồi sinh (`mid.ms`), mốc hồi ghi đúng map boss | `DOT_D_…` mục 6 |
| Đột phá + trang bị | Gốc từ cấp 94 theo mốc (500: −20%, 4300: −110%); ô Linh Phù/Ngọc Nhãn/Cổ Ngọc +đột phá, Phi Phong/Linh Đai/Linh Bội +tu luyện (trần 30%) | `KHUNG_TONG_500_4300.md` mục 4 |
| UI-1 mobile | Thanh trên 1 hàng (217→133px), số tiền gọn, vùng chạm ≥32px, chữ ≥11px | `UI_DON_GIAN_HOA_MOBILE.md` mục 3.1 |
| Làn 4 (cấp 1501–2000) | Đồ bậc 1500 phẩm Thái Sơ (15 món, bộ 12, 2 công thức), 10 khu / 80 map id 3001–3080 / 170 quái / 10 boss, chương 34–43, 10 NPC, Tàn Quyển 5%, 6 võ công Tiên Đạo CG 36 đổi 15 quyển; công pháp CG 32–40 đã có sẵn — `sql/migrate_lan4.php` + `migrate_lan4_c.php` | `LAN4_CAP_1500_2000.md` mục 0 |
| Làn 5 (cấp 2001–2500) | Đồ bậc 2000 Thái Sơ Hồng Hoang (15 món, 2 công thức), 10 khu / 80 map id 4001–4080 / 170 quái / 10 boss, chương 44–53, 10 NPC, Hỗn Nguyên Tàn Quyển (116) 5%, 6 võ công Hỗn Nguyên CG 42 đổi 10 quyển 116, 4 công pháp Đế Giai CG 45/48 — `sql/migrate_lan5.php` + `migrate_lan5_c.php` | `LAN5_CAP_2000_2500.md` mục 0 |
| Làn 6 (cấp 2501–3000) | Đồ bậc 2500 phẩm 13 Vĩnh Hằng (15 món, bộ 13, 2 công thức), 10 khu / 80 map id 5001–5080 / 170 quái / 10 boss, chương 54–63, 10 NPC, Hỗn Nguyên Tàn Quyển 5%, 6 võ công Hỗn Nguyên CG 55 đổi 15 quyển 116, 4 công pháp Đế Giai CG 52/57 — `sql/migrate_lan6.php` + `migrate_lan6_c.php` | `LAN6_CAP_2500_3000.md` mục 0 |
| Làn 7 (cấp 3001–3500) | Đồ bậc 3000 "Vạn Cổ Vĩnh Hằng" phẩm 13 (15 món, chung bộ 13, 2 công thức), 10 khu / 80 map id 6001–6080 / 170 quái / 10 boss, chương 64–73, 10 NPC, Hỗn Nguyên Tàn Quyển 5%, 6 võ công Hỗn Nguyên CG 65 đổi 20 quyển 116, giai 9 Đạo Giai 4 công pháp CG 62/67 — `sql/migrate_lan7.php` + `migrate_lan7_c.php` (chạy lại `migrate_lan6.php --apply` cho boss 3000) | `LAN7_CAP_3000_3500.md` mục 0 |
| Làn 8 (cấp 3501–4000) + điều chỉnh giá | Phẩm đồ 14 Chí Tôn (bậc 3500), 10 khu / 80 map id 7001–7080 / 170 quái / 10 boss, chương 74–83, 10 NPC, phẩm võ công Đại Đạo CG 72 (wgid 99–104) đổi 40 Đại Đạo Tàn Quyển (117, rơi 2%), Đạo Giai Cao/Thượng · **giá công pháp ×5 tích lũy tháng** (`cp_price_build.py`), giai 0–3 mua LT hoặc MT (LT = 3×MT), **Phàm Giai** cpid 73–76, Tàn Quyển làn 6/7 rơi 4/3% cần 20/30 — `migrate_lan8.php` + `migrate_lan8_c.php` (chạy lại `migrate_lan7.php --apply`) | `LAN8_CAP_3500_4000.md` mục 0 |
| Làn 9 (cấp 4001–4300, làn cuối) | Đồ bậc 4000 Vô Thượng Chí Tôn, 6 khu / 48 map id 8001–8048 / 102 quái / 6 boss (máu tới 2,92 tỉ), chương 84–89, 6 NPC, Đại Đạo CG 83 (wgid 105–110) đổi 50 quyển 117 (rơi 1%), giai 10 Chí Tôn Giai cpid 77–80 · trang nạp bỏ ×10 — `migrate_lan9.php` + `migrate_lan9_c.php` (chạy lại `migrate_lan8.php --apply`) | `LAN9_CAP_4000_4300.md` mục 0 |
| Lộ Trình Cảnh Giới | Tab Nhiệm Vụ → Cảnh Giới: 429 mốc (đột phá đại cảnh giới + Sơ/Trung/Hậu kỳ/Viên Mãn), thưởng tu vi + LT (đột phá thêm MT = 10 lần hạ boss), nhận 1 lần (mã CG+cấp trong `player_nhiemvu_achievements`), chấm đỏ nút Nhiệm Vụ — không cần migrate | `class/config/canhgioi_reward.json`, `tests/test_canhgioi.php` |
| Thể lực + MT boss | Thể lực tối đa 100 + 1/cấp (`stamina_cap`, cột `umaxstamina` giữ gốc 100), hồi 1 điểm/phút (`stamina_interval_sec`); mỗi đòn: thường 1 (boss 0), Tuyệt Kỹ 3→10 / Tuyệt Chiêu 6→20 theo phẩm võ công, bí kíp 3, Linh Căn 5/8/12 — `wugong_skill_config.json`. Boss: MT chặn khi chênh ≥ 35 cấp như LT — không cần migrate | `tests/test_stamina.php`, `tests/test_combat_moves.php` |
| Nguồn trang bị | Thương Điếm chỉ bán bậc <= 30; bậc >= 60: quái rơi 9 ô thường (chia 3 nhóm quái/bậc), boss rơi 6 ô quý (tu luyện 8/13/14, đột phá 9/10/11) → săn đem lên Phường Thị. Ô thường bậc >= 60 cộng đột phá / tu luyện = max(1, ô quý // 3). Chạy `tools/gen/drop_cover_build.py` SAU mọi bộ sinh khác; DB: `sql/migrate_drop_cover.php --apply` | `tests/test_drop_cover.php` |
| Tu Luyện gọn 1 màn hình | Cảnh giới + thanh tiến độ vào đáy khung nhân vật (bỏ chip LT/MT trùng thanh trên — số trên thanh trên `#yxb_val`/`#czb_val` cập nhật live), Tiên Đồ (6 nút tu thân) chia 3 nút mỗi bên khung nhân vật, chỉ số 5 cột × 2 hàng, bỏ đồng hồ `#CurrentTime` cuối trang, **thanh dưới 5 tab không còn `fixed`** (nằm cuối `.tcg-page`, không che nội dung) — không cần migrate | `game/xiulian.php`, `game.php`, `css/mystyle.css` |
| Làm gọn giao diện (2026-10-29) | Tu Luyện / Nhân Vật / Trang Bị / Linh Sủng / Túi Đồ vừa 1 màn hình desktop, nhân vật làm trung tâm; Túi Đồ dạng ô + bảng thao tác (`js/bagpop.js`), chia trang Đạo cụ; thanh dưới bỏ fixed; chấm đỏ từng tab Nhiệm Vụ; sửa hộp xem nhanh lệch do `zoom:.75` — không cần migrate | `UI_GON_GIAO_DIEN_2026-10-29.md` |
| Võ công trùng dòng | `playerwugong` không có khóa duy nhất, `cqwg()` đọc-rồi-thêm ⇒ đọc trượt thêm dòng trùng, mọi UPDATE trừ bí tịch chạy trên tất cả dòng ⇒ bí tịch âm. Sửa: `INSERT IGNORE` + làm mới cache `wgcx()`; **`sql/migrate_wugong_dedup.php --apply` bắt buộc** (gộp dòng, bí tịch âm → 0, UNIQUE `(sid(32), wgid)`) | `tests/test_wugong_dedup.php` |
| Võ Công bổ sung + giá Tàn Quyển | 24 võ công mới CG 48/60/69/78 (wgid 111–134, `tools/gen/vc_bosung_build.py`, chỉ số nội suy giữa 2 mốc kề cùng hệ) đổi 15/25/35 Hỗn Nguyên, 45 Đại Đạo Tàn Quyển. Công pháp CG ≥ 30 bỏ giá tiền, đổi Tàn Quyển theo vùng boss (`tiendao_config.json` `cp_need_by_cpid`/`cp_djid_by_cpid`, `\player\tiendao_cp_cost()`). "Mua ngay" bí tịch ở trang Võ Công = giá bộ đó ở Thiên Cơ Các, cùng loại tiền (`\player\wugong_book_price()`; shop không bán ⇒ giá thấp nhất cùng mốc). Chỉ đổi config + code, **không cần migrate** | `tests/test_vocong_bosung.php`, `tests/test_dot_a_data.php` |
| Sửa lỗi test toàn bộ | 12 lỗi + STAT-3/4/5 trong `docs/BAO_CAO_TEST_TOAN_BO_2026-10-29.md` mục 5 (INSERT thiếu cột, đạo cụ thiếu uid, include file tùy ý + XSS qua `extract`, Phường thị, linh sủng người khác, Thể chất/Huyết mạch nhân cảnh giới chính nhân vật…). **3 migration bắt buộc:** `sql/migrate_default_cols.php`, `sql/migrate_daoju_uid.php`, `sql/migrate_fangshi_sid.php` (--dry-run rồi --apply, sao lưu trước) | `tests/test_e2e_full.php`, `tests/test_player_stats.php` |
| Tông Môn cân bằng | Lợi ích áp thật (tối đa +10% HP / +5% công-thủ / +10% EXP tu luyện), chi phí nâng cấp mới (cấp 10 ≈ 5,5 tháng cho 10 người), EXP từ tu luyện 1%; vá online/boss/thưởng nhiệm vụ nhận lặp (session → DB) — **`sql/migrate_tongmon.php --apply` bắt buộc** | `DESIGN_TONGMON_CANBANG.md` mục 6, `tests/test_tongmon.php` |
| Làn 3 (cấp 1001–1500) | Đồ bậc 1000/1250 phẩm Hỗn Độn (30 món, bộ 11, 4 công thức), 10 khu / 80 map id 2001–2080 / 170 quái / 10 boss, chương 24–33, 10 NPC, Tàn Quyển 5%, 5 võ công Tuyệt Thế CG 25. Quái + boss **khó dần theo cấp** (áp cả làn 2). Đan đột phá không tác dụng từ Độ Kiếp — `sql/migrate_lan3.php` + `migrate_lan3_c.php` | `LAN3_CAP_1000_1500.md` mục 0 |
| Làn 2 (L2-B, C, D) | 10 khu cấp 501–1000, 80 map (id 1001–1080), 170 quái, 10 boss, ảnh khu, chương 14–23, 10 NPC, Tàn Quyển boss mới — `sql/migrate_lan2.php` + `migrate_lan2_c.php` | `LAN2_CAP_500_1000.md` mục 0 |
| KT-0 nền kỹ thuật | Tương thích PHP 7.4/8, BIGINT 17 cột (`sql/migrate_kt0_bigint.php`), EXP quái > 500, đủ 86 màu cảnh giới, sửa tên (K6), Đột Phá Đan + sự kiện cuối tuần (K2), cron tự chọn PHP | `DEPLOY_KT0_PHP74.md` |
| UI-2/UI-3 khung mới | Thanh dưới cố định 5 tab mọi trang (chấm đỏ, 🔒), tab con Nhân Vật/Linh Sủng/Nhiệm Vụ, Tiên Đồ thay 2 cột nút Tu Luyện + 21 nút Đại Sảnh; HTML ở `class/function/dieu_huong.php` | `UI_DON_GIAN_HOA_MOBILE.md` mục 3.2 |
| Mở khóa MK-1..3 | 24 tính năng mở cấp 3→120 (chặn router, nút 🔒, popup "Tính năng mới"), lọc nhiệm vụ ngày/tuần/tháng, sửa kẹt chính tuyến 1.6, chương Cơ Duyên 24 nhiệm vụ | `MO_KHOA_TINH_NANG.md` |
| Chỉ mục DB | `sql/migrate_index_sid.php` — 13 bảng (game1, túi đồ…) | — |

**Máy chủ cần chạy:** `sql\migrate_index_sid.php --dry-run` rồi `--apply` (sao lưu trước). Các phần khác không cần migrate.
**Test mới:** `tests/test_dot_d.php`, `tests/test_mo_khoa.php`. Fixture e2e phải ở **cấp 1000** (test trang Thiên Phú/Linh Căn cần tính năng đã mở).

## 3. Việc còn treo / có thể làm tiếp

| Việc | Ghi chú |
|---|---|
| **Đợt D** — nguồn võ công Tiên Đạo | Hiện chỉ Thiên Cơ Các bán; dự kiến rơi từ boss khu vực |
| **Ô 16 Bảo Vật** | Chưa có món nào; cần thiết kế hiệu ứng riêng (đã thống nhất làm sau) |
| **Làn nội dung cấp 500–1000** | Game có 74 cảnh giới (~cấp 3.700); làm theo làn ~500 cấp, dùng lại `tools/gen/` |
| mining_bonus của công pháp | Chờ có hệ thống đào mỏ |
| Thiên Phú: đổi tên Khai Phú → Sơ Phú, Khai Phú Đan → Thiên Phú Đan | Còn mở trong `UI_THIENPHU.md` |
| Nhiệm vụ NPC gốc mồ côi 10 "Nạp Tiền", 14 "Thu tập phong mật" | Không NPC giao, vô hại — chưa xử lý |
| Quái cũ cấp ≤ 105 | Chưa cân lại theo đường sức mạnh mới (vẫn dễ: quái cần 50–180 đòn hạ người) |
| Lệch múi giờ PHP–MySQL trên máy chủ | Không ảnh hưởng game hiện tại; nên đặt cùng `date.timezone` |
| Thư mục `images/other` | Ảnh tham khảo game khác — chủ dự án sẽ xóa sau |
| **Chờ duyệt / làm tiếp (2026-10-28)** | Chủ dự án chuyển máy sang PHP 7.4 x64 theo `DEPLOY_KT0_PHP74.md` · làn 5+ (2000–2500: bậc đồ 2000 Thái Sơ, võ công Hỗn Nguyên CG 42, công pháp CG 43 đã có) |

## 4. Bản đồ code nhanh (phần đã đụng tới)

| Mục | File |
|---|---|
| Định tuyến | `game.php` (`parse_str($Dcmd)` 1 tham số, token **không** dùng 1 lần), `class/encode.php` |
| Nhân vật / chỉ số | `class/player.php` (`getplayer` có cache theo request ⇒ gọi `player_cache_clear($sid)` sau khi UPDATE thô), `game/tupo.php` |
| Chiến đấu | `game/pve.php`, `game/boss.php`, `class/function/combat.php`, `js/battle_sim.js`, config `wugong_skill_config.json`, `guaiwu_skill_config.json` |
| Nhiệm vụ | `class/function/nhiemvu.php`, `class/function/online_reward.php`, `game/nhiemvu.php`, `game/task.php`, `npc/npc.php`, `game/taskguide.php`; móc ở `nowmid.php`, `pve.php`, `boss.php`, `thiencoccac.php`, `conghuong.php`, `tupo.php` |
| Trang bị | `class/function/zhuangbei.php`, `class/function/taozhuang.php`, `upzbsx` trong `player.php`, `game/zbinfo.php`, `game/taozhuang.php` |
| Bản đồ | `game/nowmid.php` (Đại Sảnh), bảng `mid` (`mgid` quái, `mnpc` NPC, `midboss`), `qy` khu vực |
| CSS | `css/mystyle.css` — **tăng số `?v=` trong `game.php` mỗi khi sửa** (hiện `2026102906`) |

## 5. Môi trường thử trên cloud (container mới mỗi phiên)

1. Cài/khởi MariaDB, tạo DB `game`, nạp `docs/db/game.sql` (cần `SET GLOBAL innodb_strict_mode=0; sql_mode=''`),
   đặt mật khẩu root khớp `pdo.php`. Sau đó chạy `migrate_dot_b/c/f.php --apply` như máy chủ.
2. `bash tools/cloud/start_env.sh` — bật DB + server PHP thử `127.0.0.1:8091` từ bản sao `/tmp/xxjrun`
   (vá `parse_str` cho PHP 8; **không sửa code thật**). MariaDB hay tự tắt ⇒ chạy lại script.
3. Đăng nhập thử: dùng nhân vật e2e có sẵn trong dump (`sid = e2e2…e2`, 32 ký tự; nếu thiếu thì tạo bằng `php tests/e2e_fixture.php`); trước khi chụp đặt `endtime = NOW()`
   (quá 1 giờ không request sẽ bị đá ra).
4. Tạo URL: `php tools/cloud/enc.php "cmd=gomid&newmid=225&sid=<sid>"`; chụp: `OUT=<dir> node tools/cloud/shot_url.js "ten|<url>"`.
5. Test: `php tests/test_dot_c.php`, `test_dot_f.php`, `test_dot_b_data.php`, `test_dot_a_data.php`, `test_combat_moves.php`, `test_conghuong.php`.
6. Mô phỏng cân bằng: `python3 tools/gen/sim_power.py 10`; rà nhiệm vụ NPC: `python3 tools/gen/audit_renwu.py 10 96`.

## 6. Lệnh cho máy chủ chủ dự án (Windows cmd)

```
cd /d D:\XXJserver\phpstudy_pro\WWW
chcp 65001
"D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup.sql
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\migrate_<đợt>.php --dry-run
"D:\XXJserver\phpstudy_pro\Extensions\php\php5.6.9nts\php.exe" sql\migrate_<đợt>.php --apply
```
Mật khẩu mysqldump = giá trị `$sqlpass` trong `pdo.php`. Cập nhật code: `cron\capnhat_code.bat` (`git pull --ff-only` nhánh đang dùng), rồi Ctrl+F5.
