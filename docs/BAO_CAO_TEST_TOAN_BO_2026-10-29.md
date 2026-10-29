# Báo cáo test toàn bộ tính năng + tính toán chỉ số nhân vật — 2026-10-29

Môi trường: bản chạy cloud `/tmp/xxjrun` (PHP 8.3, `php -S 127.0.0.1:8091`), MariaDB 10.11, DB `game`.
`sql_mode` toàn cục của DB cloud: `STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION`.

Tôi không sửa dòng code game nào, không commit. Các file mới:

| File | Vai trò |
|---|---|
| `tests/test_e2e_full.php` | Tạo nhân vật test mới, cho đi qua toàn bộ tính năng bằng route thật (HTTP), in PASS/FAIL từng mục + `ALL PASS` / `FAILED: n`, tự dọn khi xong (`--keep` để giữ lại, `--clean` để chỉ dọn, `--relax` để chạy với sql_mode không strict) |
| `tests/test_player_stats.php` | Tính lại chỉ số độc lập từ dữ liệu thô + config JSON rồi so với `\player\getplayer()`, chạy 3 cấu hình (trắng / full trang bị / full hệ thống) và 5 kiểm tra hồi quy |
| `tests/e2e_full_lib.php` | Thư viện dùng chung: tạo/dọn nhân vật test, gọi HTTP, đọc log lỗi PHP, `--dump` getplayer() trong một tiến trình mới |
| `tests/e2e_relax_prepend.php` | `auto_prepend_file` cho server phụ 8092 (chỉ dùng với `--relax`): đặt `sql_mode` không strict cho kết nối PDO persistent của phiên đó, không đổi sql_mode toàn cục |

Cách chạy:
```
bash tools/cloud/sync_run.sh
php tests/test_e2e_full.php            # chạy với DB như hiện tại (strict)
php tests/test_e2e_full.php --relax    # tự bật server phụ 8092, sql_mode không strict
php tests/test_player_stats.php
```

Phạm vi dữ liệu đã chạm:
- Có 2 nhân vật test: `E2EFull` (sid = md5(tên+token+'229'), token bắt đầu bằng `e2efull`) và `E2EFull2` (làm đối thủ Vấn Đỉnh Đài và người bán ở Phường thị). Nhân vật cho test chỉ số là `E2EStats`. Uid lấy từ auto-increment. Hàm dọn từ chối chạy trên mọi sid có token không bắt đầu bằng `e2efull`, và không bao giờ đụng uid 543, 547, 987650, 990125.
- Boss thế giới bị đánh trong test (ví dụ bossid 9003) được chụp trước khi đánh và khôi phục sau. Nếu test chết giữa chừng thì `register_shutdown_function` vẫn khôi phục.
- Một lần chạy cũ (`--keep`) chết vì lỗi trong chính test, trước khi bước khôi phục được thêm vào. Lần đó để boss 9003 ở 186016 HP; tôi đã đưa về 194800, đúng giá trị chụp lúc đầu.

---

## 1. Kết quả e2e theo tính năng

Kết quả có dao động ±2 mục giữa các lần chạy vì có bước ngẫu nhiên: quái rơi đồ, đột phá, cường hóa, luyện đan, hầm mỏ.

Cột **Strict** là chạy với DB như hiện tại: 258–261 mục, **FAIL 24–26**. Cột **Relax** là `--relax` với `sql_mode` không strict: 263 mục, **FAIL 6**.

| Tính năng | Strict (PASS/FAIL) | Relax (PASS/FAIL) | Ghi chú |
|---|---|---|---|
| Tạo nhân vật (`cjplayer`) + đăng nhập | 2 / 2 | 4 / 0 | Ở strict, INSERT game1 hỏng (BUG-01) |
| Mở khóa tính năng theo cấp | 3 / 0 | 3 / 0 | Trang 🔒 đúng ở cấp 1 |
| Tu luyện (thường, x2 Linh Thạch) | 11 / 1 | 11 / 1 | Chữ "Tiêu hao" lệch với số thật bị trừ khi có phút lẻ (BUG-12) |
| Đột phá | 4 / 0 | 3–4 / 0 | Chỉ số cộng đúng công thức |
| Cơ Duyên (nhiệm vụ "ghé trang") | 2 / 0 | 2 / 0 | |
| Cửa hàng (đan / đạo cụ / trang bị) | 6 / 6 | 12 / 0 | Strict: **trừ tiền nhưng không nhận đồ** (BUG-01, BUG-03) |
| Túi đồ (5 tab + xem chi tiết) | 8 / 0 | 8 / 0 | |
| Trang bị: mặc, tháo, mặc nhanh, cường hóa, nạp ngũ hành, phân giải, bán | 24 / 0 | 24 / 0 | Phí và chỉ số cộng đúng |
| Thiên Cơ Các: võ công, công pháp (LT/MT), đổi Tàn Quyển | 16 / 0 | 16 / 0 | |
| Võ công: học, cwinfo, "Mua ngay" bí tịch, nâng cấp, bế quan | 17 / 1 | 17 / 1 | Có thể luyện võ công chưa sở hữu (BUG-09) |
| Công pháp: vận, thu, giới hạn slot, Cộng Hưởng | 13 / 0 | 13 / 0 | |
| Chế tạo | 6 / 0 | 6 / 0 | |
| Luyện đan | 4–6 / 0–2 | 6 / 0 | Strict: luyện thành công vẫn mất đan (BUG-01) |
| Linh căn (luyện, đột phá, quest, boss, tẩy luyện) | 8 / 0 | 8 / 0 | |
| Thể chất (Nhục Thân, nâng phẩm) | 6 / 0 | 6 / 0 | |
| Huyết mạch (Mạch, nâng phẩm, biến thân, boss) | 7 / 0 | 7 / 0 | |
| Thiên phú (Khai Phú POST, bộ sưu tập) | 6 / 0 | 6 / 0 | |
| Linh sủng | 8 / 2 | 9 / 1 | **Xuất chiến được linh sủng của người khác** (BUG-07) |
| Nhiệm vụ (8 tab, taskguide, mytask, NPC) | 12 / 0 | 12 / 0 | |
| Đánh quái PVE | 6–7 / 0–1 | 7 / 0 | Strict: không sinh được quái mới, quái rơi đan bị mất (BUG-01) |
| Boss thế giới | 9 / 0 | 9 / 0 | |
| Phó bản: Tỏa Yêu Tháp, Vấn Đỉnh Đài, Hầm Mỏ, Sào Thú | 14 / 1–2 | 15 / 1 | Hầm Mỏ mất đồ rơi: ở strict là BUG-01, ở relax là BUG-02 |
| Tông môn: lập, cống hiến, quest, boss, giải tán | 12 / 0 | 12 / 0 | % tông môn áp vào getplayer() đúng |
| Giao dịch (Phường thị): treo, mua, thu hồi | 3 / 2 | 10 / 0 | Strict: treo bán lỗi `fangshi_zb.sid` INT (BUG-05) |
| Quy đổi MT→LT, giftcode | 9 / 1 | 10 / 0 | Strict: giftcode bị đánh dấu đã dùng nhưng không nhận trang bị |
| Xếp hạng (5 tab) | 5 / 0 | 5 / 0 | Chiến lực bảng xếp hạng ≠ trang Trạng thái (STAT-3) |
| Chat | 2 / 4 | 6 / 0 | Strict: không gửi được tin (BUG-01) |
| Bản đồ, Hảo hữu, xem người chơi, gamnol, qydt | 6 / 0 | 6 / 0 | |
| Bảo mật router | 1 / 2 | 1 / 2 | **Include file tùy ý qua `ym`** (BUG-04) và XSS phản xạ (BUG-06) |

Cảnh báo PHP ghi trong `php_error.log` không tính là FAIL. Ở PHP 8 chúng là Warning, tương đương Notice ở PHP 5.6, nên chỉ là lỗi nhỏ:

| Vị trí | Nội dung | Khi nào |
|---|---|---|
| `game/shangdian.php:214` | `Attempt to read property "ydjg" on false` | Mua đan với `ydid` không tồn tại. `$yaodan` bị dùng mà không kiểm tra |
| `game/boss.php:978,980,982` | `Attempt to read property "ypname"/"ypsum" on false` | Vào trận boss khi ô thuốc yp1..3 trống |
| `game/luonden.php:90` | `Undefined variable $isAjax` | `use_detox_pill` |
| `game/ginfo.php:5` | `Undefined variable $nowmid` | Xem quái (getginfo) |
| `game/pve.php:1034` | `Undefined variable $cwts` | Vào trận PVE |
| `npc/muban/zl.php:76` | `Undefined variable $czyxb` | Mở NPC 17 |

---

## 2. Danh sách lỗi

Thang mức độ: **Nghiêm trọng** (mất tiền hoặc đồ, hoặc lỗ hổng bảo mật), **Cao**, **Trung bình**, **Thấp**.

### BUG-01 — Nhiều câu INSERT thiếu cột NOT NULL không có default. Hỏng khi MySQL bật `STRICT_TRANS_TABLES`

- **Mức độ:** Nghiêm trọng nếu MySQL production bật strict (MySQL 5.7 bật mặc định). Ở môi trường cloud hiện tại thì đang xảy ra.
- **Nguyên nhân gốc (chắc chắn — log có `SQLSTATE[HY000]: 1364 Field '…' doesn't have a default value`):** schema đặt nhiều cột là `NOT NULL` không có default (tên/mô tả cũ của bảng tĩnh, `tool1..6`, `sid`…) nhưng code INSERT không truyền các cột đó.

| Chỗ INSERT | Cột thiếu | Hậu quả thấy trong test |
|---|---|---|
| `game.php:196` (`cjplayer`) | `nowguaiwu`, `tool1..16`, `yp1..3`, `jn1..3`, `cw`, `congphap`, `taozhuang`… | Không tạo được nhân vật, trang vẫn in "Hoan Nghênh" (`game.php:232`, không kiểm tra kết quả `execute`) |
| `game.php:235`, `game.php:260` (`ggliaotian`) | `sid` | Không gửi được chat, tin chào mừng mất |
| `class/player.php:1382` `addzb()` | `zbname, zbinfo, zblv, zbtool` | Mua trang bị ở shop **bị trừ tiền nhưng không có đồ**. Giftcode bị đánh dấu đã dùng nhưng mất trang bị (`game/duihuan.php:72` chạy sau `gift_mark_code_used`). Chế tạo trang bị bị trừ nguyên liệu rồi mới hỏng (`class/function/chetao.php:160-174`) |
| `class/player.php:1424` `adddj()` | `djname, djzl, djinfo` | Mua đạo cụ mới bị trừ tiền nhưng không có đồ |
| `class/player.php:1766` `addyaodan()` | `ydname…ydjg` | Mua đan bị trừ 15000 LT nhưng không có đan |
| `class/function/luonden.php:469` (+ `:456`) | `ypname…ypjg` / `djname…` | Luyện đan thành công vẫn mất nguyên liệu, không nhận đan |
| `game/hammo.php:102`, `game/toayeuthap.php:161`, `game/sauthu.php:45`, `class/function/chetao.php:201`, `class/function/club_treasury.php:92` | `djname, djzl, djinfo, uid` | Mất đồ rơi (đã thấy ở Hầm Mỏ) |
| `class/player.php:2570`, `class/player.php:3274` (`playerchongwu`) | `tool1..6`, `cwbj`, `cwexp` | Bắt hoặc thêm linh sủng không được |
| `game/nowmid.php:172-184` (`midguaiwu`) | `sid` | Bản đồ không sinh quái mới |
| Quái rơi đan (`pvegj`) | `ypname…` | Mất đồ rơi |

- **Tái hiện:** `php tests/test_e2e_full.php` rồi xem các mục FAIL có `log: … 1364`.
- **Chỗ khiến lỗi nặng thêm:** shop, giftcode và chế tạo đều trừ tiền hoặc nguyên liệu trước, thêm đồ sau, không có transaction, không kiểm tra kết quả. Vì vậy khi INSERT hỏng thì người chơi mất của.
- **Hướng xử lý:** thêm DEFAULT cho các cột này trong schema (migration), hoặc INSERT đủ cột. Các luồng trả tiền nên gói chung trong transaction với việc thêm đồ.

### BUG-02 — `playerdaoju` có khóa chính `(uid, djid)`, nhưng 6 chỗ INSERT không truyền `uid`, nên đồ rơi bị "giành chỗ" giữa người chơi

- **Mức độ:** Nghiêm trọng khi MySQL không strict (production phpstudy có thể là trường hợp này).
- **Nguyên nhân gốc (chắc chắn):** `INSERT INTO playerdaoju (sid, djid, djsum)` ở `game/hammo.php:102`, `game/toayeuthap.php:161`, `game/sauthu.php:45`, `class/function/chetao.php:201`, `class/function/luonden.php:456`, `class/function/club_treasury.php:92`. Khi không strict, `uid` mặc định thành 0, nên khóa `(0, djid)` chỉ có một dòng cho **toàn server**. Người đầu tiên nhận djid X qua các đường này giữ dòng `(0, X)`. Mọi người sau đó gặp `1062 Duplicate entry '0-X'`, và đồ bị mất âm thầm.
- **Bằng chứng:** chạy `--relax` thì Hầm Mỏ báo `Duplicate entry '0-50'`, `'0-54'`, `'0-58'`, `'0-59'`. Trong DB cloud đã có 18 dòng `playerdaoju.uid = 0`, gồm đá ngũ hành djid 30–38 của sid nhân vật 547. Như vậy nhân vật thật đang "giữ" khóa `(0, 32..38)`, và người khác chế tạo hoặc nhận các đá này qua những đường trên sẽ bị mất đồ.
- **Tái hiện:** `php tests/test_e2e_full.php --relax`, mục "Hầm Mỏ: đào …".
- **Hướng xử lý:** INSERT kèm `uid`, và gộp các đường thêm đồ về một hàm `adddj()` đã sửa.

### BUG-03 — Cửa hàng trừ tiền trước khi thêm đồ, không kiểm tra kết quả

- **Mức độ:** Cao. Đây là hệ quả của BUG-01.
- **Vị trí:** `game/shangdian.php:212-216` (đan), `:305-311` (đạo cụ), `:392-405` (trang bị). `changeyxb(2, …)` chạy thành công, rồi `addyaodan()` / `adddj()` / `addzb()` hỏng mà không có hoàn tiền. Ngoài ra Cơ Duyên "Lần đầu vào tiệm" vẫn được thưởng.
- **Chắc chắn:** cao. Test đo được 15000 LT bị trừ mà số viên đan vẫn 0→0.

### BUG-04 — Include file tùy ý (LFI) qua biến `ym` trong URL

- **Mức độ:** Nghiêm trọng (bảo mật).
- **Vị trí:** `game.php:89-90` và `:115-116`, `extract($__cmdVars)` đưa **mọi** tham số URL thành biến toàn cục. `game.php:389`: `$ym` chỉ bị gán lại khi `cmd` có trong `$cmdMap`. Sau đó `game.php:656` chạy `include "$ym"`.
- **Tái hiện:** URL mã hoá `cmd=khongco&ym=class/config/feature_unlock.json&sid=<sid hợp lệ>`. Trang in ra nội dung JSON (test "Bảo mật router"). Khóa mã hoá `cxphp` cố định nên ai cũng tự tạo được URL. Đường dẫn tuyệt đối như `/etc/passwd` hay file log cũng include được, có thể dẫn tới chạy mã nếu log chứa PHP.
- **Chắc chắn:** cao, đã tái hiện.
- **Hướng xử lý:** không dùng `extract` cho biến nội bộ (dùng `EXTR_SKIP`, hoặc whitelist tên biến), và chỉ include `$ym` khi nó là một giá trị trong `$cmdMap`.

### BUG-05 — Treo bán trang bị ở Phường thị hỏng: `fangshi_zb.sid` có kiểu INT

- **Mức độ:** Cao, vì mất tính năng giao dịch trang bị.
- **Vị trí:** `game/zbinfo.php:60`. Câu INSERT ghi sid dạng hex 32 ký tự vào cột INT. Strict mode báo `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'sid'`. `game/zbinfo.php:85` còn **in thẳng thông báo SQL ra trang**. Khi không strict thì sid bị cắt về 0 hoặc một số đầu chuỗi, và mọi chỗ so `fangshi_zb.sid` bằng chuỗi đều sai (xem ghi chú "sid-schema-debt" trong `tests/e2e_fixture.php`).
- **Chắc chắn:** cao, đã tái hiện.

### BUG-06 — XSS phản xạ: `$pvpts` / `$tpts` có thể bị ghi đè từ URL

- **Mức độ:** Trung bình (bảo mật).
- **Vị trí:** `game.php:34-35` khởi tạo `$pvpts = ''`, `$tpts = ''`. Sau đó `extract()` ở `:90` và `:116` ghi đè chúng, và trang in hai biến này ra mà không escape. URL `cmd=gomid&newmid=225&pvpts=<script>…</script>&sid=…` chèn được script.
- **Chắc chắn:** cao, đã tái hiện. Cùng nguyên nhân với BUG-04.

### BUG-07 — Xuất chiến được linh sủng của người khác

- **Mức độ:** Cao.
- **Vị trí:** `game/chongwu.php:245-248`, case `chuzhan` gọi `changeplayersx('cw', $cwid, …)` mà không kiểm tra `playerchongwu.sid`.
- **Hậu quả:** người chơi nhận chỉ số của linh sủng người khác trong trận. `subchongwuhp()` (`class/player.php:1832`) trừ máu theo `cwid` không kèm sid, nên **linh sủng của chủ thật bị mất máu**.
- **Tái hiện:** test "Linh sủng – xuất chiến linh sủng của người khác" (dùng cwid của một sid khác; test đặt lại `cw = 0` ngay sau đó).
- **Chắc chắn:** cao.

### BUG-08 — `cjplayer` in lời chào dù không tạo được nhân vật

- **Mức độ:** Trung bình.
- **Vị trí:** `game.php:196-236`, không kiểm tra kết quả `execute`. Ngoài ra `token` không được đối chiếu với `userinfo`, nên ai cũng tạo được nhân vật với token tự đặt dài ≥ 32 ký tự (`game/cj.php:6` chỉ kiểm tra độ dài).

### BUG-09 — Luyện được võ công chưa sở hữu

- **Mức độ:** Thấp.
- **Vị trí:** `game/xxwg.php:22-27`, case `xuexi` gán `game1.wugong = $wgid` không kiểm tra `playerwugong`.
- **Hậu quả:** thực tế ít, vì getplayer() và chiến đấu đều đi qua `wgcx()` và nhận `false` thì không cộng gì. Nhưng UI và dữ liệu bị sai: `game1.wugong` trỏ tới võ công không có.

### BUG-10 — Chỉ số Thể chất / Huyết mạch phụ thuộc `$GLOBALS['player']` (người đang xem), không phải nhân vật đang được tính (STAT-1)

- **Mức độ:** Cao, vì làm sai số liệu.
- **Vị trí:** `class/function/thechat.php:302`, `class/function/huyetmach.php:235`. Hệ số cảnh giới đọc từ `$GLOBALS['player']->jingjieValue`.
- **Hậu quả, đo bằng `tests/test_player_stats.php` (nhân vật cấp 130, cảnh giới 3):**
  - Lần gọi `getplayer()` đầu tiên của request: game.php mới có `$player = new \player\player()` nên `jingjieValue = null`, hệ số = ×1. Kết quả **Công 7844, HP 25467**.
  - Lần gọi thứ hai trong cùng request (sau khi cache bị xoá, ví dụ sau một hành động): hệ số = ×(1 + 0.15·3) / ×(1 + 0.18·3). Kết quả **Công 8055, HP 26606**. Lệch +211 Công, +1139 HP.
  - `getplayer1()` / `getplayer($sid khác)` (xếp hạng, Vấn Đỉnh Đài, xem người chơi) dùng **cảnh giới của người xem** để tính cho nhân vật khác.
- **Chắc chắn:** cao. Có comment ở `thechat.php:816` cho thấy tác giả đã biết vấn đề cho phần Nhục Thân nhưng phần base_stats vẫn giữ.
- **Hướng xử lý:** truyền `jingjieValue` của chính nhân vật vào `thechat_calc_bonuses()` / `huyetmach_calc_bonuses()`, rồi chốt công thức (×1 như hiện hiển thị lần đầu, hay theo cảnh giới như thiết kế).

### BUG-11 — Cache tĩnh của `wgcx()` làm getplayer() dùng cấp võ công cũ trong cùng request (STAT-2)

- **Mức độ:** Thấp. Chỉ sai trên trang ngay sau khi nâng cấp, request sau thì đúng.
- **Vị trí:** `class/player.php:3043` (`static $cache`). `game/wugong.php:10` nạp cache. Sau `UPDATE … wgdj+1` (`:186-201`) trang gọi `getplayer()`, mà getplayer gọi `wgcx()` không `forceRefresh` (`class/player.php:661`). Chỉ số hiển thị thiếu `add_*` của 1 cấp. Test đo được lệch đúng `add_gj` = 18.
- **Chắc chắn:** cao, đã tái hiện bằng hàm.

### BUG-12 — Tu luyện tốn tài nguyên: số hiển thị "Tiêu hao" ≠ số thật bị trừ khi có phút lẻ

- **Mức độ:** Thấp.
- **Vị trí:** `game/xiulian.php:193` trừ `round(phút × giá)`, còn `:227` hiển thị `ceil(giây/60) × giá`. Ví dụ 70 giây: trừ 1 LT, hiển thị 2 LT.
- **Chắc chắn:** cao, đã tái hiện.

### Lỗi nhỏ về tính chỉ số (từ `tests/test_player_stats.php`)

| Mã | Nội dung | Vị trí | Chắc chắn |
|---|---|---|---|
| STAT-3 | Chiến lực ở Xếp hạng tính khác trang Trạng thái: thiếu 200·phẩm Thể chất + 500·giác tỉnh + 250·phẩm Huyết mạch + 600·giác tỉnh. Ví dụ 173791 so với 175991. Comment ghi là "LỆCH có chủ ý", nhưng người chơi vẫn thấy hai con số khác nhau | `game/paihang.php:10-21` so với `class/player.php:441` | cao |
| STAT-4 | `ubj` (Bạo kích) thành số thực, ví dụ 213.05, do Nhục Thân Cốt Tủy cộng 0.2/cấp với `round(…, 2)` rồi `ubj += crit` không ép kiểu int. Huyết mạch thì `intval` → xử lý không thống nhất | `class/function/thechat.php:847-849`, `class/player.php:854` | cao |
| STAT-5 | Mạch Xung (Bạo kích 0.2/cấp) và Mạch Đới (Hút máu 0.4/cấp) bị `round()` từng mạch, nên Xung cấp 1–2 cho 0 Bạo kích. Comment ở `:1080-1081` nói crit/lifesteal "vẫn là float" nhưng code làm tròn | `class/function/huyetmach.php:1077` | cao |
| STAT-6 | `combat_power()` luôn cộng 200 + 250 (phẩm mặc định = 1) kể cả khi chưa có Thể chất / Huyết mạch | `class/player.php:441-462` + mặc định `quality = 1` ở `:821`, `:860` | cao (có thể là chủ ý) |
| STAT-7 | `upplayerlv()` cộng 2.5 / 7.5 vào cột INT `ugj`/`ufy`. Mỗi lần MySQL làm tròn theo giá trị hiện tại (chẵn/lẻ), nên chỉ số gốc ở cấp 130 thấp hơn thiết kế 2.5 Công và 1.5 Thủ. Độ lệch nhỏ, nằm trong dung sai | `class/player.php:1287-1330` | cao |

---

## 3. Chỉ số kỳ vọng và thực tế (`tests/test_player_stats.php`)

Phương pháp: tính lại **độc lập** từ `game1` (chỉ số gốc sau khi lên cấp bằng đúng đường đột phá), `playerzhuangbei` + `zhuangbei_config.json` (chỉ số = max(DB, JSON gốc), tức gốc + phần cường hóa), `wugong_config.json` (add_* × cấp), `taozhuang_config.json` (mốc 4/8/12/15 món), `congphap_config.json` + `wugong_skill_config.json` (phạt absorb_speed, % atk/def_bonus × 0.4 trần 25), `club_benefits_table` (bảng ghi lại trong test), `linhcan_element_config.json` (per_lv × Lv × mốc tổng Lv, Kim +0.5%/bậc), `nguhanh_config.json` (nấc 1/3/6/9/12 món hợp hệ), `thechat_config.json` + `thechat_body_config.json`, `huyetmach_config.json` + `huyetmach_vessel_config.json`. Thứ tự áp dụng giống getplayer(). Riêng Cộng Hưởng dùng hàm thuần `conghuong_eval()`. Thiên phú và Linh sủng không cộng vào 5 chỉ số chính (đúng thiết kế trong comment).

Nhân vật cấp 130 (cảnh giới 3), chỉ số gốc Công 4224 / Thủ 5218 / HP 13660.

| Chỉ số | A. Trắng: kỳ vọng / getplayer | B. Full 15 ô trang bị (+5 vũ khí, ngũ hành): kỳ vọng / getplayer | C. Full hệ thống: kỳ vọng / getplayer |
|---|---|---|---|
| Công | 4224 / 4224 | 6860 / 6860 | 7844 / 7844 |
| Thủ | 5218 / 5218 | 7518 / 7518 | 7715 / 7715 |
| Khí huyết | 13660 / 13660 | 21139 / 21139 | 25467 / 25467 |
| Bạo kích | 0 / 0 | 73 / 73 | 213.05 / 213.05 (STAT-4) |
| Hút máu | 0 / 0 | 34 / 34 | 58 / 58 |
| Chiến lực | 98094 / 98094 | 153373 / 153373 | 175991 / 175991 |
| EXP cần (131²×110) | 1887710 / 1887710 | 1887710 / 1887710 | 1887710 / 1887710 |
| **Lệch** | **0** | **0** | **0** |

Phần đóng góp của từng nguồn ở cấu hình C (Công, Thủ, HP, Bạo, Hút):

| Nguồn | Công | Thủ | HP | Bạo | Hút |
|---|---|---|---|---|---|
| Gốc (cấp / cảnh giới) | 4224 | 5218 | 13660 | 0 | 0 |
| Trang bị + cường hóa | 2128 | 2226 | 5913 | 72 | 34 |
| Võ công (Bát Quái Quyền cấp 6) | 108 | 24 | 480 | 36 | 0 |
| Thần Trang (15 món cùng bậc: HP/Công +8%, Thủ/Bạo +1%) | 517 | 75 | 1604 | 1 | 0 |
| Công pháp (atk +, def +, absorb_speed phạt) | 72 | −243 | 0 | 0 | 0 |
| Cộng Hưởng | 0 | 0 | 0 | 0 | 0 |
| Tông môn cấp 5 (+2.5% / +2.5% / +5%) | 176 | 182 | 1082 | 0 | 0 |
| Linh căn (tổng Lv 105, mốc +5%) | 104 | 25 | 273 | 4 | 0 |
| Nạp Hành (hợp hệ Kim) | 72 | 48 | 0 | 1 | 0 |
| Thể chất (phẩm 3, giác tỉnh 1) + Nhục Thân | 127 | 160 | 579 | 23.05 | 2 |
| Huyết mạch (phẩm 2, giác tỉnh 1) + 8 Mạch | 316 | 0 | 1876 | 76 | 22 |
| Thiên phú / Linh sủng | 0 | 0 | 0 | 0 | 0 |

Kết luận: với **ngữ cảnh một request mới**, công thức tổng của `getplayer()` khớp tuyệt đối với cách tính độc lập ở cả 3 cấu hình. Những chỗ sai nằm ở chỗ khác: chỉ số đổi theo ngữ cảnh gọi (BUG-10), cache (BUG-11), cách làm tròn (STAT-4, STAT-5), và công thức Chiến lực ở Xếp hạng (STAT-3). Kết quả kiểm tra thêm: 5 FAIL (STAT-1 đến STAT-5), A0 (chỉ số gốc theo công thức đột phá) PASS, B0 (cường hóa +8% gốc mỗi cấp) PASS.

Trong e2e, các phép đo getplayer() sau mỗi hành động qua route thật đều đúng (đọc trong một tiến trình PHP mới): mặc vũ khí, cường hóa (+round(gốc×8%)), nâng võ công (+add_gj), vận công pháp atk_bonus, luyện Kim (+2 Công cộng Nạp Hành 3% Công vũ khí hệ Kim), Nhục Thân Bì Phủ (+4 Thủ), Đốc Mạch (+3 Công), tông môn cấp 1 (+1% HP, +0.5% Công/Thủ).

---

## 4. Ghi chú cho người chạy lại

- Server 8091 được chia sẻ với người đang sửa giao diện. Test chỉ đọc `/tmp/xxjrun/php_error.log` theo offset trước/sau từng request, nên nếu có request khác chen vào thì có thể nhận nhầm dòng log của họ. Khả năng này thấp.
- `--relax` mở server phụ `127.0.0.1:8092` (tự tắt khi xong). Không đổi `sql_mode` toàn cục, không ảnh hưởng 8091.
- Lỗi đã biết, không thuộc phạm vi báo cáo này: `tests/test_dot_c.php` và `tests/test_dot_d.php` FAIL do strict mode và dữ liệu cũ. Theo BUG-01 thì đây cũng là cùng một nguyên nhân gốc.

---

## 5. Đã sửa (2026-10-29)

| Lỗi | Cách sửa | File |
|---|---|---|
| BUG-01 INSERT thiếu cột NOT NULL | Migration gắn DEFAULT cho 298 cột (số → 0, chuỗi → '', text/ngày → NULL); bỏ qua cột khóa chính và `game1.sid/token/endtime` (bảng chạm giới hạn kích thước dòng, code luôn truyền) | `sql/migrate_default_cols.php` |
| BUG-02 `playerdaoju` thiếu uid | 6 câu INSERT lấy uid từ `game1` theo sid; kho Tông Môn: tham số thực ra là UID người nhận (trước bị dùng như sid ⇒ đồ không tới ai) — tra sid thật. Migration sửa dữ liệu uid = 0 (gán uid / gộp số lượng) | `game/hammo.php`, `game/toayeuthap.php`, `game/sauthu.php`, `class/function/chetao.php`, `class/function/luonden.php`, `class/function/club_treasury.php`, `sql/migrate_daoju_uid.php` |
| BUG-03 shop trừ tiền mất đồ | Hết lỗi khi BUG-01 đã sửa (INSERT không còn hỏng) | — |
| BUG-04 / BUG-06 include file tùy ý + XSS | Trước mỗi `extract()` loại khỏi tham số URL các biến nội bộ router (`ym`, `pvpts`, `tpts`, `dblj`, `player`…) | `game.php` |
| BUG-05 Phường thị | `fangshi_zb.sid` / `fangshi_sd.sid` INT → VARCHAR(32); không in lỗi SQL ra trang | `sql/migrate_fangshi_sid.php`, `game/zbinfo.php` |
| BUG-07 xuất chiến linh sủng người khác | Kiểm tra `playerchongwu.sid` trước khi xuất chiến | `game/chongwu.php` |
| BUG-08 lời chào khi tạo nhân vật lỗi | Kiểm tra kết quả INSERT | `game.php` |
| BUG-09 luyện võ công chưa sở hữu | Kiểm tra `wgcx()` trước khi đặt | `game/xxwg.php` |
| BUG-10 / STAT-1 Thể chất / Huyết mạch theo người xem | Chủ dự án chốt **nhân theo cảnh giới của chính nhân vật**; `getplayer()` truyền `jingjieValue` vào `thechat_calc_bonuses` / `huyetmach_calc_bonuses` | `class/player.php`, `class/function/thechat.php`, `class/function/huyetmach.php` |
| BUG-11 cache `wgcx()` sau nâng cấp | Làm mới cache ngay sau UPDATE | `game/wugong.php` |
| BUG-12 tu luyện hiển thị ≠ trừ | Hiển thị đúng số đã trừ (`$cost`) | `game/xiulian.php` |
| STAT-3 Chiến lực Xếp hạng | Dùng chung `\player\combat_power()` | `game/paihang.php` |
| STAT-4 / STAT-5 Bạo kích lẻ / Mạch Xung 0 | Mạng Mạch giữ số lẻ, `getplayer()` làm tròn tổng Bạo kích / Hút máu 1 lần | `class/player.php`, `class/function/huyetmach.php` |

Kết quả sau sửa (DB strict): `tests/test_e2e_full.php` 263/263 PASS, `tests/test_player_stats.php` ALL PASS (đã cập nhật kỳ vọng theo quy tắc mới).
Còn lại (có từ trước, số FAIL giống hệt trước/sau sửa): `test_dot_c` 1, `test_drop_cover` 2, `test_huyetmach_schema` 1, `test_kt0` 1,
`test_linhcan_pages` 29, `test_nguhanh_market` 12, `test_tianfu_migration` 16, `test_tianfu_schema` 1; nhiều test cũ dựng `game1` thiếu `token` nên chỉ chạy được khi DB không strict.
Chưa sửa: STAT-6 (Chiến lực luôn cộng phẩm mặc định — có thể chủ ý), STAT-7 (làm tròn cột INT khi lên cấp, lệch rất nhỏ), BUG-08 phần token tạo nhân vật không đối chiếu `userinfo`.
