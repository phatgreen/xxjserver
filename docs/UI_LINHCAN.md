# Tài liệu: Linh Căn — Vòng Ngũ Hành (game/linhcan.php)

> **Nguồn**: kế hoạch `docs/superpowers/plans/2026-10-10-linhcan-vong-ngu-hanh.md` (12 task),
> thiết kế `docs/superpowers/specs/2026-10-10-linhcan-vong-ngu-hanh-design.md` (số `[]` đã duyệt 10/10).
> **Khung 12 mục copy từ `docs/UI_THECHAT.md` / `docs/UI_HUYETMACH.md`.**
> Ngày đo: **2026-10-10**, DB local `game`. Tài liệu này đang được viết dần theo từng task —
> mọi mục **CHƯA test** đều in hoa CHƯA.

---

## Kiểm kê consumer `linhcan_id` (Task 1 Step 1)

Lệnh: `grep -rn "linhcan_id" --include=*.php . | grep -v "^./bak/" | grep -v "^./docs/" | grep -v "^./sql/"`

Plan dự đoán 5 chỗ. Thực đo **26 dòng / 8 tệp** — 3 chỗ plan chưa liệt kê:

| Tệp: Dòng | Thuộc plan? | Xử lý |
|---|---|---|
| `class/function/linhcan.php:164-204,502-596` | ✅ | Task 3/11 viết lại + xoá |
| `class/function/linhcan_boss.php:40,44,102` | ✅ | Task 5 bỏ `get_dominant_element` |
| `class/player.php:113,304,746-747,2025-2031` | ✅ | Task 9 rewire; `:304` allowlist + `game.php:192` INSERT **GIỮ NGUYÊN** |
| `game/linhcan.php:6,11` | ✅ | Task 10 viết lại |
| `game/linhcan_upgrade.php:6,11,32` | ⚠️ plan có nhắc ở File Structure | Task 7 XOÁ file + route `game.php:353` |
| `game/zhuangtai.php:424` | ✅ | Task 9 sửa `:424-450` |
| `tests/e2e_fixture.php:62` | ❌ **ngoài plan** | Fixture ghi `linhcan_id=1, quality=5`. Sau Task 9 hệ mới không đọc cột này → giá trị thành dữ liệu chết. **CHƯA đụng**, ghi ở §Chưa làm. |
| `tests/test_nguhanh_random.php:59,144` | ❌ **ngoài plan** | Nhánh C set `linhcan_id=21` rồi **assert** `$player->linhcan_element === 'all'`. Sau Task 9, `linhcan_element` lấy từ `linhcan_dominant()` (node) → **test này sẽ FAIL**. Phải sửa ở Task 9. |

Không còn consumer nào ở `paihang` hay trang khác (đúng như plan).

**Hệ quả cho Nạp Ngũ Hành (`class/function/nap_hanh.php:45`)**: `linhcan_bonus` có sẵn
`kim 15 / moc 5 / thuy 0 / hoa 10 / tho 8` → ghi `linhcan_element` = hành chủ đạo là **Nạp Hành vẫn chạy đủ**.
Riêng 2 khoá `all` (+10, từng tới từ căn id 21) và `none` (0, từng tới từ Nghịch Thiên id 22) **không còn nguồn** —
chờ chủ dự án chốt (xem §Chưa làm).

## Nguồn đá 32..37 (Task 1 Step 2)

| djid | Tên (`daoju_config.json`) | `djyxb` | Giá cửa hàng (`shangdian.php:309` = `max(50, djyxb)`) | `djuse` |
|---|---|---|---|---|
| 32 | Hỏa Tinh Thạch | 30 | **50 Linh thạch** | trống |
| 33 | Thủy Tinh Thạch | 30 | 50 | trống |
| 34 | Mộc Tinh Thạch | 30 | 50 | trống |
| 35 | Kim Tinh Thạch | 30 | 50 | trống |
| 36 | Thổ Tinh Thạch | 30 | 50 | trống |
| 37 | Bình Anh Tinh Thạch | 100 | **100 Linh thạch** | trống |
| 111 | Tẩy Linh Đan | 30000 | 30000 | trống |

Đo thêm:
- **Quai KHÔNG rơi đá ngũ hành**: quét toàn bộ 87 entry `guaiwu_config.json`, field rơi đồ là `gdj`/`gyp`
  (danh sách id, `djjv`/`ypjv` = số lượng) — **không entry nào rơi id 32..37**. Quai chỉ rơi id 1/6/7/8/12.
- **Cửa hàng có bán**, tab Đạo Cụ (`game/shangdian.php:339` duyệt toàn bộ `daoju_config_all()`,
  chỉ trừ djid 112/113/114). Không món nào bị chặn ở `:308` vì `djuse` đều trống.

→ **Nguồn đá = cửa hàng (50/100 Linh thạch, mua không giới hạn) + boss thế giới (Task 5) + quest tuần (Task 6).**
⚠️ **PHẢI BÁO CHỦ DỰ ÁN**: thiết kế "Bình Anh là đá HIẾM, nguồn hiếm = boss thế giới 2h" sẽ **bị vô hiệu**
nếu cửa hàng còn bán Bình Anh 100 Linh thạch. Chặn bán là quyết định của chủ dự án — xem §Chưa làm.

## Chưa làm / chờ chủ dự án chốt

1. **(Task 1 phát hiện) Cửa hàng bán Bình Anh Tinh Thạch (37) giá 100 Linh thạch, mua không giới hạn.**
   Boss thế giới 2h mà Task 5 dựng sẽ không còn là "nguồn đá hiếm" nữa. Ba lựa chọn:
   (a) chặn 32..37 khỏi tab Đạo Cụ, (b) giới hạn số lượt mua mỗi tuần, (c) chấp nhận — đá không hiếm.
   **CHƯA tự sửa** vì đụng trang `shangdian.php` ngoài phạm vi plan.
2. **(Task 1 phát hiện) Nạp Ngũ Hành mất thưởng `all` (+10%) và `none` (0%)** do 2 khoá này từng tới từ
   căn id 21/22 (`linhcan_config.json` cũ). Cần chốt: Vô Cực Linh Thể (danh hiệu mới) có cấp `all` không.
3. **(Task 1 phát hiện) `tests/test_nguhanh_random.php` nhánh C sẽ FAIL sau Task 9** vì nó set
   `linhcan_id=21` và assert `linhcan_element === 'all'`. Task 9 phải viết lại nhánh C theo node.
4. **(Task 1 phát hiện) `tests/e2e_fixture.php:62` vẫn ghi `linhcan_id=1, linhcan_quality=5`** cho nhân fixture.
   Sau Task 9 cột này không còn được đọc → dữ liệu chết. Nên đổi sang seed node (Task 10/12 dùng `lcseed.php`).
5. `docs/UI_LINHCAN.md` mới có phần kiểm kê Task 1; 10 mục còn lại của khung (so ảnh 1:1, sổ nợ, playbook lệnh)
   sẽ viết dần ở Task 10-12.

