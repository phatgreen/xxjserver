# ĐỢT A — Mở rộng Võ Công & Công Pháp (bản thiết kế chờ duyệt)

> Lập 2026-10-27. Bảng số sinh bằng `tools/gen/dot_a_design.py` (công thức hiệu chỉnh theo bộ cũ) — sửa công thức ở đó rồi chạy lại, không gõ tay.
> Thuộc kế hoạch tổng `PLAN_MO_RONG_NOI_DUNG.md`.

## 1. Quy ước đã đo trong code

- `realm_require` so với `jingjieValue` (= chỉ số cảnh giới + 1). Yêu cầu **R** ⇒ cần cấp ≥ **(R − 1) × 50**.
  Ví dụ: R 3 → cấp 100 · R 6 → 250 · R 9 → 400 · R 12 → 550 · R 15 → 700 · R 23 → 1100 · R 30 → 1450.
- Chỉ số võ công = `add_*` × cấp võ công (`class/player.php` ~dòng 646). Ngân sách mỗi cấp (Công + Thủ + HP/10 + Bạo + Hút) của bộ cũ:
  Nhập Môn ~15 · Trung Cấp ~26 · Thượng Thừa ~38 · Thần Công ~55.
- Nguồn duy nhất hiện nay: **Thiên Cơ Các** (mua bằng Linh thạch / Ma thạch).

## 2. Quyết định thiết kế

| # | Nội dung | Đề xuất |
|---|---|---|
| D1 | Phẩm võ công | 6 phẩm, **giữ key cũ** để không vỡ dữ liệu: `nhap_mon` hiển thị **Cơ Bản**, thêm `ha_thua` (**Hạ Thừa**), `trung_cap`, `thuong_thua`, `than_cong` hiển thị **Tuyệt Thế**, thêm `tien_dao` (**Tiên Đạo**) |
| D2 | Công pháp | **Giữ hệ Giai × Phẩm có sẵn** (7 Giai Hoàng→Thánh × 4 phẩm Sơ/Trung/Cao/Thượng, đang dùng ở Thiên Cơ Các) thay vì ép sang 6 phẩm — hệ này đã chi tiết hơn và đúng phong cách tu tiên. Thêm **4 bộ mỗi giai** để mỗi ô có lựa chọn theo hệ |
| D3 | Dải cảnh giới | Võ công mới lấp khoảng trống: Hạ Thừa R3 (cấp 100), Trung Cấp R9 (cấp 400), Thượng Thừa R15, Tuyệt Thế R23, Tiên Đạo R30 |
| D4 | Nguồn | Cơ Bản → Tuyệt Thế: Thiên Cơ Các. **Tiên Đạo không bán** — rơi từ Boss (tỉ lệ thấp, boss cấp cao); gắn nguồn rơi ở đợt D cùng boss khu vực |
| D5 | Hiệu ứng | Chỉ dùng loại **đã code xong** (pierce_def, crit_dmg, damage_reduce, escape, hp_regen, exp_bonus, drop_bonus, combo_hit, steal_reward, counter_lifesteal; công pháp: atk/def/crit/dmg_reduce/hp_regen/heal/drop/stone/exp) — không thêm cơ chế mới cần cân bằng lại |
| D6 | Thế võ | Mỗi võ công mới viết đủ 3 thế (tên + văn). Thêm `rarity_bonus`: `ha_thua` 0.02, `tien_dao` 0.20 |
| D7 | Cộng Hưởng | Nhóm đông lên (11–17 bộ) ⇒ mốc thành **2 / 5 / 9 bộ / toàn bộ** (+0.3 / +0.4 / +0.5 / +0.8%) — tối đa 2%/nhóm, **4% mỗi chỉ số** (vẫn < 5%) |

## 3. Bảng chi tiết

### Võ công mới (30 bộ)

| wgid | Tên | Phẩm | Hệ | Loại | Công/Thủ/HP/Bạo/Hút mỗi cấp | Hiệu ứng | Yêu cầu CG | Giá |
|---|---|---|---|---|---|---|---|---|
| 40 | Phá Giáp Kim Cương Đao | Hạ Thừa | kim | Đao Pháp / Ngoại Công | 8 / 3 / 50 / 4 / 0 | pierce_def 15 | 3 | 3,000 LT |
| 41 | Thanh Đằng Dưỡng Khí Quyết | Hạ Thừa | moc | Nội Công / Luyện Thể | 2 / 6 / 100 / 0 / 2 | hp_regen 6 | 3 | 3,000 LT |
| 42 | Lưu Vân Phiêu Bộ | Hạ Thừa | thuy | Thân Pháp / Khinh Công | 4 / 6 / 70 / 1 / 2 | escape 40 | 3 | 3,000 LT |
| 43 | Xích Diễm Liên Quyền | Hạ Thừa | hoa | Quyền Pháp / Ngoại Công | 10 / 2 / 40 / 4 / 0 | crit_dmg 20 | 3 | 3,000 LT |
| 44 | Hậu Thổ Thiết Bố Sam | Hạ Thừa | tho | Hộ Thể / Ngoại Công | 2 / 9 / 90 / 0 / 0 | damage_reduce 12 | 3 | 3,000 LT |
| 45 | Bách Nghệ Tạp Thuật | Hạ Thừa | all | Tạp Kỹ / Thực Dụng | 5 / 5 / 50 / 3 / 2 | exp_bonus 8 | 3 | 3,000 LT |
| 46 | Truy Phong Kiếm Quyết | Trung Cấp | kim | Kiếm Pháp / Ngoại Công | 12 / 4 / 80 / 6 / 0 | pierce_def 20 | 9 | 700 MT |
| 47 | Xuân Phong Hóa Vũ Công | Trung Cấp | moc | Nội Công / Tâm Pháp | 4 / 8 / 140 / 0 / 4 | hp_regen 10 | 9 | 700 MT |
| 48 | Phi Yến Đạp Thủy | Trung Cấp | thuy | Thân Pháp / Khinh Công | 7 / 10 / 100 / 1 / 2 | escape 55 | 9 | 700 MT |
| 49 | Viêm Long Phá Thiên Quyền | Trung Cấp | hoa | Quyền Pháp / Ngoại Công | 15 / 2 / 60 / 7 / 0 | crit_dmg 30 | 9 | 700 MT |
| 50 | Bàn Thạch Hộ Thể Công | Trung Cấp | tho | Hộ Thể / Ngoại Công | 3 / 13 / 140 / 0 / 0 | damage_reduce 18 | 9 | 700 MT |
| 51 | Ngũ Hành Luân Chuyển Thủ | Trung Cấp | all | Tạp Kỹ / Thực Dụng | 8 / 8 / 80 / 4 / 3 | drop_bonus 10 | 9 | 700 MT |
| 52 | Thái Bạch Kinh Thiên Kiếm | Thượng Thừa | kim | Kiếm Pháp / Ngoại Công | 18 / 7 / 110 / 9 / 0 | pierce_def 28 | 15 | 2,800 MT |
| 53 | Cửu Mộc Trường Sinh Kinh | Thượng Thừa | moc | Nội Công / Tâm Pháp | 5 / 13 / 220 / 0 / 5 | hp_regen 14 | 15 | 2,800 MT |
| 54 | Huyền Băng Hàn Ảnh Bộ | Thượng Thừa | thuy | Thân Pháp / Khinh Công | 10 / 14 / 150 / 2 / 4 | escape 70 | 15 | 2,800 MT |
| 55 | Chu Tước Phần Thiên Chưởng | Thượng Thừa | hoa | Chưởng Pháp / Ngoại Công | 22 / 4 / 90 / 10 / 0 | crit_dmg 40 | 15 | 2,800 MT |
| 56 | Trấn Nhạc Kim Thân Quyết | Thượng Thừa | tho | Hộ Thể / Ngoại Công | 5 / 20 / 200 / 0 / 0 | damage_reduce 26 | 15 | 2,800 MT |
| 57 | Âm Dương Liên Hoàn Thủ | Thượng Thừa | all | Tạp Kỹ / Cầm Nã | 11 / 11 / 110 / 7 / 4 | combo_hit 22 | 15 | 2,800 MT |
| 58 | Tru Tiên Kiếm Trận | Tuyệt Thế | kim | Kiếm Pháp / Ngoại Công | 25 / 9 / 160 / 12 / 0 | pierce_def 35 | 23 | 45,000 MT |
| 59 | Thanh Đế Trường Xuân Công | Tuyệt Thế | moc | Nội Công / Tâm Pháp | 7 / 17 / 300 / 0 / 7 | hp_regen 20 | 23 | 45,000 MT |
| 60 | Huyền Minh Vô Lượng Bộ | Tuyệt Thế | thuy | Thân Pháp / Khinh Công | 14 / 20 / 210 / 2 / 5 | escape 85 | 23 | 45,000 MT |
| 61 | Kim Ô Đại Nhật Quyền | Tuyệt Thế | hoa | Quyền Pháp / Ngoại Công | 31 / 5 / 120 / 14 / 0 | crit_dmg 55 | 23 | 45,000 MT |
| 62 | Hỗn Độn Kim Thân Quyết | Tuyệt Thế | tho | Hộ Thể / Ngoại Công | 7 / 27 / 280 / 0 / 0 | damage_reduce 36 | 23 | 45,000 MT |
| 63 | Thiên Thủ Vạn Tượng | Tuyệt Thế | all | Tạp Kỹ / Thực Dụng | 16 / 16 / 160 / 9 / 6 | steal_reward 18 | 23 | 45,000 MT |
| 64 | Thái Thượng Trảm Đạo Kiếm | Tiên Đạo | kim | Kiếm Pháp / Tiên Đạo | 32 / 12 / 200 / 16 / 0 | pierce_def 45 | 30 | Rơi từ Boss |
| 65 | Kiến Mộc Thông Thiên Kinh | Tiên Đạo | moc | Nội Công / Tiên Đạo | 10 / 22 / 380 / 0 / 10 | hp_regen 25 | 30 | Rơi từ Boss |
| 66 | Thiên Hà Đảo Quyển Bộ | Tiên Đạo | thuy | Thân Pháp / Tiên Đạo | 18 / 26 / 270 / 3 / 6 | escape 95 | 30 | Rơi từ Boss |
| 67 | Cửu Thiên Huyền Hỏa Quyết | Tiên Đạo | hoa | Quyền Pháp / Tiên Đạo | 40 / 6 / 160 / 18 / 0 | crit_dmg 70 | 30 | Rơi từ Boss |
| 68 | Bàn Cổ Khai Thiên Thể | Tiên Đạo | tho | Hộ Thể / Tiên Đạo | 9 / 35 / 360 / 0 / 0 | damage_reduce 45 | 30 | Rơi từ Boss |
| 69 | Hồng Mông Tạo Hóa Công | Tiên Đạo | all | Tạp Kỹ / Tiên Đạo | 20 / 20 / 200 / 12 / 8 | counter_lifesteal 15 | 30 | Rơi từ Boss |

### Công pháp mới (28 bộ, 4 bộ / giai)

| cpid | Tên | Giai · Phẩm | Hệ | Hiệu ứng (Lv1 → Lv10) | Yêu cầu CG | Giá |
|---|---|---|---|---|---|---|
| 29 | Liệt Hỏa Tâm Kinh | Hoàng Giai · Sơ Phẩm | hoa | atk_bonus 3% → 20% | 0 | 1,000 LT |
| 30 | Thủy Kính Hộ Tâm Pháp | Hoàng Giai · Trung Phẩm | thuy | def_bonus 3% → 20% | 0 | 1,000 LT |
| 31 | Thảo Mộc Dưỡng Sinh Thuật | Hoàng Giai · Cao Phẩm | moc | heal_bonus 4% → 22% | 0 | 1,200 LT |
| 32 | Kim Thạch Tụ Tài Quyết | Hoàng Giai · Thượng Phẩm | kim | stone_bonus 4% → 22% | 0 | 1,200 LT |
| 33 | Canh Kim Duệ Khí Quyết | Huyền Giai · Sơ Phẩm | kim | atk_bonus 4% → 24% | 3 | 2,200 LT |
| 34 | Hỏa Nha Sát Quyết | Huyền Giai · Trung Phẩm | hoa | crit_dmg 4% → 24% | 3 | 2,500 LT |
| 35 | Thủy Nguyệt Tầm Bảo Pháp | Huyền Giai · Cao Phẩm | thuy | drop_rate 4% → 25% | 4 | 2,800 LT |
| 36 | Hậu Thổ Dưỡng Nguyên Công | Huyền Giai · Thượng Phẩm | tho | hp_regen 4% → 25% | 5 | 2,800 LT |
| 37 | Huyền Thủy Giáp Pháp | Địa Giai · Sơ Phẩm | thuy | def_bonus 5% → 30% | 7 | 900 MT |
| 38 | Địa Mạch Hộ Thân Quyết | Địa Giai · Trung Phẩm | tho | dmg_reduce 4% → 24% | 8 | 1,000 MT |
| 39 | Mộc Linh Ngộ Đạo Kinh | Địa Giai · Cao Phẩm | moc | exp_bonus 5% → 32% | 9 | 1,100 MT |
| 40 | Hỏa Luyện Kim Thạch Quyết | Địa Giai · Thượng Phẩm | hoa | stone_bonus 6% → 35% | 10 | 1,200 MT |
| 41 | Thiên Cương Phá Quân Quyết | Thiên Giai · Sơ Phẩm | kim | atk_bonus 6% → 32% | 12 | 2,200 MT |
| 42 | Vạn Mộc Tầm Linh Pháp | Thiên Giai · Trung Phẩm | moc | drop_rate 6% → 34% | 14 | 2,600 MT |
| 43 | Thủy Linh Hồi Xuân Quyết | Thiên Giai · Cao Phẩm | thuy | heal_bonus 7% → 36% | 16 | 3,000 MT |
| 44 | Thiên Diễn Ngộ Đạo Thuật | Thiên Giai · Thượng Phẩm | all | exp_bonus 7% → 40% | 18 | 3,500 MT |
| 45 | Tiên Thủy Linh Lung Giáp | Tiên Giai · Sơ Phẩm | thuy | def_bonus 8% → 42% | 18 | 16,000 MT |
| 46 | Tiên Hỏa Phần Tâm Quyết | Tiên Giai · Trung Phẩm | hoa | crit_dmg 8% → 40% | 20 | 19,000 MT |
| 47 | Kim Tiên Bất Hoại Quyết | Tiên Giai · Cao Phẩm | kim | dmg_reduce 7% → 36% | 22 | 21,000 MT |
| 48 | Tiên Mộc Thông Huyền Kinh | Tiên Giai · Thượng Phẩm | moc | exp_bonus 9% → 45% | 24 | 24,000 MT |
| 49 | Thần Kiếm Tru Ma Quyết | Thần Giai · Sơ Phẩm | kim | crit_dmg 10% → 48% | 25 | 38,000 MT |
| 50 | Thần Mộc Bất Diệt Kinh | Thần Giai · Trung Phẩm | moc | hp_regen 10% → 50% | 28 | 42,000 MT |
| 51 | Thần Hỏa Tầm Bảo Quyết | Thần Giai · Cao Phẩm | hoa | drop_rate 11% → 50% | 30 | 48,000 MT |
| 52 | Thần Thủy Huyền Minh Công | Thần Giai · Thượng Phẩm | thuy | dmg_reduce 9% → 44% | 32 | 55,000 MT |
| 53 | Thánh Hỏa Diệt Thế Quyết | Thánh Giai · Sơ Phẩm | hoa | atk_bonus 12% → 55% | 35 | 75,000 MT |
| 54 | Thánh Sơn Trấn Ngục Kinh | Thánh Giai · Trung Phẩm | tho | def_bonus 12% → 55% | 38 | 90,000 MT |
| 55 | Thánh Mộc Hồi Thiên Kinh | Thánh Giai · Cao Phẩm | moc | hp_regen 12% → 55% | 40 | 105,000 MT |
| 56 | Thánh Thủy Tụ Bảo Quyết | Thánh Giai · Thượng Phẩm | thuy | stone_bonus 12% → 55% | 43 | 125,000 MT |

## 4. Sửa lỗi phát hiện khi khảo sát

| Lỗi | Trạng thái |
|---|---|
| Thiên Cơ Các tra võ công theo key JSON thay vì `wgid`: **không mua được 4 bộ Thần Công** (id 30/32/34/36), mua Thập Tự Phong Đao bị tính giá/tên Nguyệt Nha Hoàn | **Đã sửa** (commit `81f51f4`), kiểm chứng trên DB thật |
| Thiên Cơ Các hiển thị tên cảnh giới yêu cầu lệch 1 bậc (`$rangesjjClean[$realmReq]` dùng chỉ số 0 trong khi `realm_require` là 1-based) | Sửa cùng đợt A |

## 5. Kiểm thử dự kiến

- Test thuần: mọi bộ mới có đủ trường, đủ 3 thế, wgid/cpid không trùng, key JSON = id, ngân sách nằm giữa phẩm liền kề.
- Trên DB thật (cloud): Thiên Cơ Các hiện đủ, mua được đúng bộ/giá, học → trang Võ Công / Công Pháp / Cộng Hưởng hiển thị đúng, đánh quái dùng thế võ mới.
