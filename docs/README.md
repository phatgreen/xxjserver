# XXJ Server — Knowledge Information System

Tổng hợp tài liệu kỹ thuật và thông tin hệ thống game.

---

## Danh mục

### Thiết kế & Phân tích

| File | Mô tả |
|------|-------|
| [DESIGN_MASTER.md](DESIGN_MASTER.md) | Tài liệu tổng hợp phân tích & kế hoạch phát triển (master doc) |
| [WORLD_LORE.md](WORLD_LORE.md) | Bách khoa hệ thống tu chân — linh căn, cảnh giới, thế giới |
| [DESIGN_CONGPHAP.md](DESIGN_CONGPHAP.md) | Thiết kế hệ thống công pháp / võ công |
| [GAME_MECHANICS.md](GAME_MECHANICS.md) | Phân tích cơ chế game |
| [GAME_BALANCE.md](GAME_BALANCE.md) | Phân tích cân bằng game |
| [ECONOMY_BALANCE.md](ECONOMY_BALANCE.md) | Đề xuất cân bằng kinh tế |
| [PROGRESSION.md](PROGRESSION.md) | Hệ thống tiến trình chi tiết |
| [PROGRESSION_LONG_TERM.md](PROGRESSION_LONG_TERM.md) | Tiến trình dài hạn |
| [RESOURCE_ECONOMY.md](RESOURCE_ECONOMY.md) | Hệ thống tài nguyên hoàn chỉnh |

### Kế hoạch tính năng

| File | Mô tả |
|------|-------|
| [PLAN_THECHAT_HUYETMACH.md](PLAN_THECHAT_HUYETMACH.md) | Kế hoạch hệ thống Thệ Chất / Huyết Mạch |
| [DESIGN_NGUHANH_KICHHOAT.md](DESIGN_NGUHANH_KICHHOAT.md) | Spec **Nạp Ngũ Hành (đa hệ)** — rèn tối đa 5 hành lên một món trang bị |
| [superpowers/plans/2026-10-04-nguhanh-kichhoat.md](superpowers/plans/2026-10-04-nguhanh-kichhoat.md) | Implementation plan chia task của tính năng trên (đã thực hiện xong) |
| [LINHCAN_DEV.md](LINHCAN_DEV.md) | Dev notes hệ thống linh căn |
| [LINHCAN_EXPANSION.md](LINHCAN_EXPANSION.md) | Kế hoạch mở rộng linh căn |
| [LUONDEN_CHETAO.md](LUONDEN_CHETAO.md) | Kế hoạch lò đòn / chế tạo |

### Kỹ thuật & Cài đặt

| File | Mô tả |
|------|-------|
| [TEST_REPORT.md](TEST_REPORT.md) | 🔴 Báo cáo kiểm thử toàn diện game (bugs, SQL injection, balance) |
| [TONGHOP-TINHNANG.md](TONGHOP-TINHNANG.md) | Bản đồ 30 hệ thống in-game (mục 6 = Trang bị + Thuộc tính Ngũ Hành) |
| [CHANGELOG.md](CHANGELOG.md) | Nhật ký thay đổi code (từ `game/NOTE.md`) |
| [TONG_QUAN_DU_AN.md](TONG_QUAN_DU_AN.md) | **Đọc đầu tiên** — tổng quan kiến trúc, cấu trúc thư mục, trạng thái hiện tại |
| [DEPLOY_PRODUCTION_NGUHANH.md](DEPLOY_PRODUCTION_NGUHANH.md) | Runbook deploy 18 ALTER (ngũ hành + nợ sid) — **CHƯA chạy**, kèm preflight & nghiệm thu |
| `tests/` (ngoài thư mục gốc) | `e2e_fixture.php` dựng nhân vật test + URL route thật (`--clean` để dọn) · `test_nguhanh*.php` 4 suite · `deploy_preflight.php` (chỉ đọc) · `capture_tip_390.php` (ảnh tooltip 390px) |
| [SOURCE_SUMMARY.txt](SOURCE_SUMMARY.txt) | Tóm tắt cập nhật source tu chân giới |
| [server-notes.txt](server-notes.txt) | Ghi chú server (MySQL config, đan chống tụt cấp) |
| [cron-reward.md](cron-reward.md) | Hướng dẫn cron auto-reward Vạn Đỉnh Đại |
| [gm-items.txt](gm-items.txt) | Danh sách item ID cho GM tool |
| [map-images.md](map-images.md) | Quy ước ảnh bản đồ `dt/ditu/` |

---

## Cấu trúc thư mục

```
docs/
├── README.md                  ← file này (KIS index)
│
├── DESIGN_MASTER.md           ← master doc tổng hợp
├── WORLD_LORE.md              ← bách khoa thế giới
├── DESIGN_CONGPHAP.md         ← thiết kế công pháp
├── GAME_MECHANICS.md          ← phân tích cơ chế
├── GAME_BALANCE.md            ← phân tích cân bằng
├── ECONOMY_BALANCE.md         ← đề xuất kinh tế
├── PROGRESSION.md             ← tiến trình chi tiết
├── PROGRESSION_LONG_TERM.md   ← tiến trình dài hạn
├── RESOURCE_ECONOMY.md        ← hệ thống tài nguyên
│
├── PLAN_THECHAT_HUYETMACH.md  ← kế hoạch thệ chất/huyết mạch
├── LINHCAN_DEV.md             ← dev notes linh căn
├── LINHCAN_EXPANSION.md       ← kế hoạch mở rộng linh căn
├── LUONDEN_CHETAO.md          ← kế hoạch lò đòn/chế tạo
│
├── CHANGELOG.md               ← dev changelog
├── SOURCE_SUMMARY.txt         ← tóm tắt source update
├── server-notes.txt           ← ghi chú server
├── cron-reward.md             ← hướng dẫn cron
├── gm-items.txt               ← item ID list
└── map-images.md              ← quy ước ảnh bản đồ
```

---

## Quy tắc

1. **Mọi tài liệu kỹ thuật** đều đặt trong `docs/`, không rải rác trong `D:\XXJserver\` hay `game/`, `cron/`, `gm/`.
2. **File name**: snake_case hoặc UPPER_CASE, `.md` ưu tiên, `.txt` chỉ dùng cho data thô.
3. **Khi thêm docs mới**: cập nhật bảng danh mục ở trên.
4. **Khi xóa/sửa docs**: cập nhật ngày tháng trong bảng.
5. **Hướng dẫn cài đặt** (`Hướng dẫn cài đặt.txt`) giữ nguyên tại `D:\XXJserver\` vì là file setup gốc.
