# TỔNG QUAN DỰ ÁN — Tu Chân Giới (XXJ Server)

> Tài liệu nhập môn: đọc file này trước, rồi mới mở tài liệu chi tiết từng hệ thống.
> Cập nhật: 2026-10-27.

---

## 1. Công nghệ & môi trường

| Hạng mục | Giá trị |
|---|---|
| Backend | PHP **5.6** (phpStudy: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe`) |
| CSDL | MySQL qua PDO (`pdo.php` — **chứa mật khẩu DB, repo phải giữ Private**) |
| Frontend | HTML/CSS/JS thuần, render server-side; CSS chung `css/mystyle.css` |
| Thư mục local | `D:\XXJserver\phpstudy_pro\WWW` |
| Repo | `github.com/phatgreen/xxjserver` (private) |
| Mã hoá file | UTF-8 **không BOM** cho file mới (file hỏng encoding → trang trắng) |

## 2. Luồng request

```
game.php?cmd=<chuỗi mã hoá>
  └─ class/encode.php giải mã → $cmd
  └─ bảng route trong game.php ('tianfu' => 'game/tianfu.php', ...)
  └─ include trang game/*.php (dùng $player, $dblj, ...)
```

- Mọi link nội bộ phải sinh qua `$encode` (không viết `?cmd=` thô cho người chơi).
- Trang mặc định: `game/nowmid.php`. Lỗi PHP ghi vào `php_error.log` ở gốc.

## 3. Cấu trúc thư mục

| Thư mục | Vai trò |
|---|---|
| `game/` | Các màn hình (1 route = 1 file) |
| `class/player.php` | Class người chơi: `getplayer()`, `combat_power()`, `upplayerlv()`, `usedaoju()`... |
| `class/function/` | Logic từng hệ thống (tianfu, linhcan, huyetmach, thechat, luonden, zhuangbei...) |
| `class/config/*.json` | Số liệu cân bằng (đọc bằng hàm thuần, **không hard-code số trong PHP**) |
| `npc/` | NPC và template |
| `gm/` | Công cụ GM |
| `cron/` | Tác vụ định kỳ (hồi thể lực, thưởng Vấn Đỉnh Đài) — chạy bằng Task Scheduler (`.bat`) |
| `sql/` | Schema + script migration (`migrate_*.php`) |
| `tests/` | Test CLI dạng `test_*.php`, in `PASS/FAIL` + dòng cuối `ALL PASS` |
| `docs/` | Tài liệu; `docs/superpowers/` = spec + plan chia task |
| `images/` | Ảnh game |

## 4. Các hệ thống chính & tài liệu

| Hệ thống | Code | Tài liệu |
|---|---|---|
| Thiên Phú (Phủ Các) | `class/function/tianfu.php`, `game/tianfu*.php` | `UI_THIENPHU.md`, spec/plan `2026-10-12-thien-phu-phu-cac*` |
| Linh Căn (Vòng Ngũ Hành) | `class/function/linhcan*.php` | `UI_LINHCAN.md`, `LINHCAN_*.md` |
| Huyết Mạch | `class/function/huyetmach*.php` | `UI_HUYETMACH.md` |
| Thể Chất | `class/function/thechat.php` | `UI_THECHAT.md` |
| Nạp Ngũ Hành (trang bị) | `class/function/nap_hanh.php` | `DESIGN_NGUHANH_KICHHOAT.md` |
| Luyện Đan | `class/function/luonden.php` | `LUONDEN_CHETAO.md`, `PLAN_ALCHEMY_SYSTEM.md` |
| Tu Luyện | `game/xiulian.php` | `UI_XIULIAN.md` |
| Võ Công / Công Pháp | `class/function/wugong.php`, `congphap.php`, `game/pve.php` | `PLAN_VOCONG_CONGPHAP.md` (kế hoạch thế võ), `DESIGN_CONGPHAP.md` |
| Kinh tế / cân bằng | — | `ECONOMY_BALANCE.md`, `GAME_BALANCE.md`, `RESOURCE_ECONOMY.md` |
| Toàn bộ 30 hệ thống | — | `TONGHOP-TINHNANG.md` |
| Mở rộng nội dung (quái, nhiệm vụ, võ công/công pháp theo cảnh giới) | — | `PLAN_MO_RONG_NOI_DUNG.md` |

## 5. Quy ước làm việc

- **PHP 5.6**: không `??`, `<=>`, type hint scalar, return type.
- Thay đổi nhỏ nhất, không đổi tên hàm/biến sẵn có.
- Số liệu cân bằng để trong `class/config/*.json`.
- Deploy production có runbook riêng: `DEPLOY_PRODUCTION_*.md` (chạy SQL/migration **trước** khi chép code).
- Chạy test: `php.exe tests/test_xxx.php` (một số test cần server local, xem đầu file test).

---

## 6. Trạng thái Thiên Phú (2026-10-27)

**Đã xong** 12/12 task theo plan: config 18 thiên phú / 6 nhóm / 10 phẩm / 7 cổng, schema,
Khai Phú Đan (djid 114), nâng cấp + đột phẩm, 4 Phú Vi, 3 trang (`tianfu`, `tianfu_collection`,
`tianfu_open`), gắn 7 cổng vào code, chiến lực, migration hoàn điểm `tf` cũ.

**Việc còn mở — cần chủ dự án quyết** (chi tiết: `UI_THIENPHU.md` mục "Sổ nợ"):

| Ưu tiên | Việc | Chỗ |
|---|---|---|
| 1 | Cổng **Luyện đan** hiển thị có cộng nhưng roll thật không cộng | `luonden.php:348` vs `:899` |
| 2 | `upplayerlv()` vẫn cấp 6 cột `tf*` cũ (vô tác dụng) | `class/player.php` `upplayerlv()` |
| 3 | Bảng xếp hạng `paihang.php` dùng công thức riêng, thiếu 4 chỉ số | `game/paihang.php:10-21` |
| 4 | Trần hoàn `tf` khi migration production | `sql/migrate_tianfu.php` |
| 5 | Boss thiên phú (phase 2) — nguồn phẩm cao ngoài Khai Phú Đan | chưa viết |
| 6 | Production chưa chạy runbook | `DEPLOY_PRODUCTION_THIENPHU.md` |

**Chưa test trong game thật**: chuỗi uống đan → gán Phú Vi → thấy hiệu ứng trên màn Tu Luyện.
