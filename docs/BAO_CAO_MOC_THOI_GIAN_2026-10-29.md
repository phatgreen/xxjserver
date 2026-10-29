# Báo cáo nhân vật test theo mốc thời gian — ngày 1 / tuần 1 / tháng 1, 1h → 24h/ngày

> Ngày đo: 2026-10-29 · Nhánh `claude/tong-quan-du-an` (code mới nhất) · Môi trường cloud: MariaDB 10.11 + `docs/db/game.sql` + 30 migration (`kt0` → `lan9`, `drop_cover`, `tongmon`, 4 migration 10-29), server thử `127.0.0.1:8091`.
> Cách làm giống `DOT_G_KINH_TE_THE_LUC_TRANG_BI.md` mục 6 (mô hình `tools/sim/econ_sim.py`, công thức lấy từ code), mở rộng ra 9 mức giờ chơi và **đối chiếu bằng nhân vật test thật trên server**.

| Mục | Nội dung |
|---|---|
| 1 | Cách đo + đối chiếu thật |
| 2 | Kết quả tổng: cấp theo mốc |
| 3 | Ngày 1 |
| 4 | Tuần 1 |
| 5 | Tháng 1 |
| 6 | Nhận xét cân bằng |
| 7 | Sửa trong mô hình + giới hạn |

---

## 1. Cách đo

**Mô hình** (`tools/sim/moc_online_sim.py` → gọi `econ_sim.sim()`):
- 9 mức giờ online/ngày: **1, 2, 4, 6, 8, 12, 16, 20, 24h**; ghi số ở **ngày 1, 7, 30**; mỗi kịch bản 30 lần chạy, lấy trung bình (kèm min–max cấp).
- Chơi hết tính năng online theo giới hạn của từng mốc:
  - Tu luyện liên tục: thường 30 phút/lượt, LT ×2 khi đủ quỹ, không có hồi chiêu giữa các lượt.
  - Quái cùng cấp, giới hạn bởi thể lực (100 + cấp − 1, hồi 1/phút, đòn thường 1).
  - Boss cùng tầm (chênh < 35 cấp, hồi sinh `ms`).
  - Nhiệm vụ: ngày / tuần / tháng / chính tuyến, thưởng trực tuyến 10–240 phút, bế quan, Lộ Trình Cảnh Giới.
  - Tỏa Yêu Tháp, săn đồ 15 ô, linh sủng (Sào Thú).
  - Võ công / công pháp lên theo quái.
  - LT dư chi lần lượt cho linh căn → thiên phú → huyết mạch → thể chất → cường hóa, luôn giữ quỹ khoảng 20 lần đột phá.
- **Giả định:** F2P, máy chủ chỉ 1 người (không ai tranh boss), không mua bán Phường Thị, không dùng Đột Phá Đan / sự kiện cuối tuần.

**Đối chiếu thật** (`tools/sim/moc_crosscheck.php`):
- Dựng **27 nhân vật test** (9 mức giờ × 3 mốc). Mỗi nhân vật được INSERT với giá trị khởi tạo y hệt `cmd=cjplayer`, rồi đưa về đúng cấp / LT / MT mô hình ra (chỉ để đo).
- Đọc số game hiển thị qua route HTTP thật: trang Đột Phá, trang Tu Luyện, và 1 lượt tu luyện thật 30 phút (`endxiulian`).

| Hạng mục so sánh | Kết quả |
|---|---|
| Tỉ lệ đột phá hiển thị = mô hình | **27/27 khớp** |
| Phí LT đột phá = mô hình | **27/27** (sau khi sửa mô hình, mục 7) |
| Thanh tu vi `(cấp+1)²×110` | 27/27 |
| Linh khí/s = cấp | 27/27 |
| 1 lượt tu luyện 30' thật = `min(1800×cấp, thanh)` | 27/27 |
| Trần thể lực = 100 + cấp − 1 | 27/27 |

Bấm đột phá **thật** 400 lần mỗi cấp. Lần bấm bị anti-flood chặn (không trừ LT) không tính.

| Cấp | Tỉ lệ hiển thị | Thành công / 400 | Tỉ lệ đo |
|---|---|---|---|
| 12 | 64% | 250 | 62,5% |
| 96 | 11% | 37 | 9,2% |
| 233 | 5% | 21 | 5,2% |

Cả 3 nằm trong sai số mẫu (±2,4 / ±1,6 / ±1,1 điểm %).

## 2. Kết quả tổng — cấp trung bình (min–max)

| Giờ/ngày | Ngày 1 | Tuần 1 | Tháng 1 | Tính năng đã mở ngày 1 / tuần 1 / tháng 1 (trên 24) |
|---|---|---|---|---|
| 1h | 12 (9–12) | 33 (31–36) | 103 (97–108) | 5 / 15 / 23 |
| 2h | 16 (13–17) | 45 (35–51) | 127 (118–132) | 6 / 16 / 24 |
| 4h | 19 (17–21) | 66 (63–70) | 140 (129–148) | 6 / 19 / 24 |
| 6h | 22 (19–25) | 83 (79–87) | 150 (139–179) | 11 / 21 / 24 |
| 8h | 25 (22–28) | 96 (91–101) | 162 (144–191) | 11 / 22 / 24 |
| 12h | 29 (27–32) | 118 (110–123) | 188 (159–202) | 12 / 23 / 24 |
| 16h | 35 (29–40) | 136 (131–142) | 209 (190–249) | 15 / 24 / 24 |
| 20h | 42 (35–46) | 142 (134–149) | 225 (189–248) | 16 / 24 / 24 |
| 24h | 48 (41–54) | 146 (135–164) | 233 (199–257) | 16 / 24 / 24 |

Giờ chơi gấp 24 lần (1h → 24h) chỉ cho cấp tháng 1 gấp **2,3 lần**. Từ 16h trở lên, mỗi 4 giờ thêm chỉ được khoảng +10 cấp/tháng.

## 3. Ngày 1

| Giờ/ngày | Cấp | Tỉ lệ ĐP | Thử ĐP | Quái | Boss | LT thu | LT chi ĐP | LT còn | MT còn | Tu vi: tu luyện / quái / NV |
|---|---|---|---|---|---|---|---|---|---|---|
| 1h | 12 | 65% | 15 | 25 | 0 | 5,2K | 1,0K | 1,0K | 5 | 75 / 7 / 18% |
| 2h | 16 | 62% | 23 | 36 | 0 | 6,9K | 2,5K | 0,9K | 5 | 77 / 9 / 14% |
| 4h | 19 | 60% | 28 | 58 | 0 | 10,9K | 4,3K | 0,7K | 8 | 79 / 13 / 7% |
| 8h | 25 | 57% | 37 | 101 | 0 | 20,5K | 9,7K | 1,2K | 8 | 79 / 15 / 7% |
| 12h | 29 | 54% | 46 | 145 | 0 | 31,7K | 17,0K | 1,8K | 8 | 80 / 15 / 6% |
| 16h | 35 | 53% | 56 | 189 | 35 | 185K | 29,2K | 34,2K | 220 | 79 / 13 / 7% |
| 24h | 48 | 46% | 83 | 276 | 202 | 898K | 85,6K | 141K | 1.245 | 81 / 13 / 6% |

- **Dưới 12h: LT là nút thắt.** Thu nhập chỉ có quái + nhiệm vụ. Cuối ngày còn 0,7–1,8K LT, phần lớn đã chi cho đột phá và trang bị tiệm (bộ bậc 1/10 = 2,1K / 4,2K LT).
- **Từ 16h: boss cấp 30 lọt vào tầm** (cấp ≥ 30) và LT tăng vọt gấp 6–28 lần. Boss chiếm 77–92% LT ngày 1.
- Võ công / công pháp: công pháp đạt **10 (tối đa) ngay ngày 1** ở mức ≥ 12h.

## 4. Tuần 1 (ngày 7)

| Giờ/ngày | Cấp | Tỉ lệ ĐP | Tụt cấp | Boss | Tháp | LT thu (boss %) | LT chi ĐP | MT còn | Linh căn | Thiên phú | Cường hóa | Linh sủng |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | 33 | 54% | 0,1 | 15 | 3 | 140K (44%) | 24K | 147 | – | – | +13 | – |
| 2h | 45 | 47% | 0,3 | 114 | 30 | 579K (80%) | 64K | 746 | 2 / bậc 0 | – | +25 | – |
| 4h | 66 | 38% | 0,6 | 404 | 50 | 1,95M (90%) | 236K | 1.928 | **90 / bậc 4 (đầy)** | – | +27 | phẩm 4 cấp 6 |
| 8h | 96 | 27% | 1,3 | 751 | 78 | 4,42M (91%) | 893K | 4.936 | 90 / 4 | phẩm 4 cấp 24 | +30 | phẩm 4 cấp 38 |
| 12h | 118 | 23% | 1,9 | 1.020 | 99 | 6,82M (90%) | 1,97M | 7.840 | 90 / 4 | 5 / 30 | +30 | phẩm 4 cấp 50 |
| 24h | 146 | 21% | 3,4 | 1.197 | 133 | 9,03M (85%) | 4,78M | 9.825 | 90 / 4 | 5 / 30 | +30 | phẩm 4 cấp 61 |

- **Từ 4h/ngày, linh căn đầy (cấp 90, bậc 4) trong tuần đầu.** Từ 8h, cường hóa +30 (trần hiệu quả) cũng đầy. Khớp nhận định "đầy quá nhanh" ở DOT_G mục 5.
- **Từ 16h/ngày, mở đủ 24/24 tính năng trong tuần đầu.**
- Từ 12h trở lên, cấp tuần 1 dồn về vùng 118–146: tỉ lệ đột phá tụt về 21–23% và LT chi đột phá tăng vọt (1,97M → 4,78M).

## 5. Tháng 1 (ngày 30)

| Giờ/ngày | Cấp | Tỉ lệ ĐP | Thử ĐP | Tụt cấp | Quái | Boss | LT thu | boss / quái / NV / tháp | LT chi ĐP | MT thu (boss / NV) | MT còn |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | 103 | 25% | 234 | 1,5 | 1.071 | 399 | 3,27M | 66 / 13 / 19 / 1% | 1,27M | 2.972 / 803 | 2.901 |
| 2h | 127 | 22% | 329 | 2,3 | 1.492 | 675 | 5,69M | 73 / 13 / 13 / 1% | 2,87M | 5.559 / 855 | 5.664 |
| 4h | 140 | 21% | 385 | 2,0 | 2.264 | 850 | 7,65M | 70 / 18 / 11 / 1% | 4,06M | 7.145 / 932 | 7.327 |
| 6h | 150 | 21% | 426 | 2,5 | 2.984 | 963 | 9,21M | 68 / 21 / 10 / 1% | 5,12M | 8.293 / 1.004 | 8.548 |
| 8h | 162 | 20% | 488 | 3,1 | 3.681 | 1.115 | 11,6M | 69 / 22 / 8 / 1% | 7,09M | 10,5K / 1.065 | 10,8K |
| 12h | 188 | 18% | 620 | 4,5 | 5.078 | 1.444 | 17,3M | 70 / 23 / 6 / 1% | 11,9M | 15,5K / 1.131 | 15,9K |
| 16h | 209 | 18% | 713 | 4,8 | 6.481 | 1.619 | 22,5M | 69 / 25 / 5 / 1% | 16,9M | 19,7K / 1.239 | 20,2K |
| 20h | 225 | 18% | 814 | 6,2 | 7.795 | 1.895 | 28,6M | 70 / 25 / 4 / 1% | 22,1M | 25,0K / 1.340 | 25,6K |
| 24h | 233 | 18% | 882 | 7,3 | 7.974 | 2.104 | 32,2M | 72 / 24 / 4 / 0% | 25,7M | 28,9K / 1.358 | 29,5K |

Tính năng ở ngày 30:

| Giờ/ngày | Trang bị (+ĐP / +TL) | Ô bậc hiện | Thiên phú (phẩm / cấp) | Huyết mạch | Thể chất | Cường hóa | Võ công | Linh sủng (phẩm / cấp) | Tháp |
|---|---|---|---|---|---|---|---|---|---|
| 1h | +15 / +20% | 14,6/15 | 4 / 24 | 2,2 | 0 | +27 | 23 | 4 / 45 | 90 |
| 2h | +15 / +21% | 7,5/15 | 5 / 28 | 4,7 | 1,8 | +28 | 27 | 4 / 55 | 115 |
| 4h | +15 / +21% | 9,0/15 | 5 / 30 | 4,9 | 1,9 | +30 | 35 | 4 / 65 | 129 |
| 8h | +15 / +21% | 12,8/15 | 5 / 31 | 5,0 | 4,1 | +30 | 43 | 4 / 79 | 144 |
| 12h | +15 / +21% | 14,8/15 | 5 / 32 | 5,2 | 5,0 | +30 | 46 | 4 / 91 | 150 (đỉnh) |
| 16h | +16 / +26% | 14,6/15 | 6 / 33 | 5,4 | 4,7 | +30 | 49 | 4 / 102 | 150 |
| 24h | +18 / +30% | 15/15 | 6 / 35 | 5,6 | 4,9 | +30 | 51 | 4 / 109 | 150 |

Tu vi tháng 1: tu luyện chiếm 60% (1h) đến 81% (24h), quái 12–15%, nhiệm vụ 23% (1h) giảm còn 6% (24h).

## 6. Nhận xét cân bằng

1. **Nút thắt dài hạn là đột phá, không phải tu vi.**
   - Từ khoảng cấp 150, tỉ lệ đột phá chạm sàn 5% (cộng trang bị +15–18% thì còn khoảng 18–21%).
   - Ở 24h/ngày, **80% LT thu được (25,7M / 32,2M) đổ vào đột phá**, với 882 lần thử và 7,3 lần tụt cấp trong tháng.
   - Đây là lý do 24h chỉ hơn 16h khoảng 11% cấp.
2. **Boss ≈ 66–73% LT tháng 1 ở mọi mức giờ, và 79% (1h) → 95% (24h) MT miễn phí.**
   - Mô hình coi máy chủ chỉ 1 người. Trên máy chủ đông người boss dùng chung, nên LT/MT thực tế và cấp từ tuần 1 trở đi sẽ **thấp hơn** bảng trên.
   - Mức chênh lệch phụ thuộc số người cùng tầm cấp. Mức 1–2h ít phụ thuộc boss nhất (44–80% LT tuần 1).
3. **MT miễn phí tháng 1: 2,9K (1h) → 29,5K (24h).**
   - Gần như toàn bộ đến từ boss (chặn chênh ≥ 35 cấp đã áp).
   - Người cày 24h tích khoảng 1K MT/ngày. Nếu 1 MT = 1 VNĐ (DOT_G), người chơi miễn phí cày ra lượng MT đáng kể so với gói nạp. Nên theo dõi khi mở server.
4. **Tính năng đầy quá sớm so với khung 4.300 cấp** (xác nhận lại DOT_G mục 5):
   - Linh căn 90/bậc 4 ở tuần 1 với mức ≥ 4h.
   - Cường hóa +30 ở tuần 1 với mức ≥ 8h.
   - Công pháp 10 ở ngày 1 với mức ≥ 12h.
   - Tháp 150 ở tháng 1 với mức ≥ 12h.
   - Linh sủng dừng ở phẩm 4 ở mọi mức giờ (phẩm 5 cần kỳ vọng MT vượt quỹ).
5. **Ngày 1 người chơi ít giờ thiếu LT.**
   - 1–8h cuối ngày chỉ còn 0,6–1,2K LT. Riêng tiền đột phá đã ăn 19–47% LT thu được.
   - Đây là cùng kết luận "LT là nút thắt ba ngày đầu" của báo cáo 2026-10-16, vẫn đúng với luật mới.
6. **Tụt cấp do đột phá tăng theo số lần thử**: 1,5 lần/tháng ở 1h, 7,3 lần ở 24h. Mỗi lần tụt mất 1 cấp và toàn bộ tu vi.

## 7. Sửa trong mô hình + giới hạn

**Hai lỗi mô hình đã sửa** (`tools/sim/econ_sim.py`, chỉ sửa công cụ, không đổi game):

| Lỗi | Phát hiện bằng | Sửa |
|---|---|---|
| Phí đột phá: mô hình chỉ tính `3·L²` ở mốc 50. Game thật tính `3·L²` khi **cấp kế chia hết cho 10** hoặc đang ở mốc 50 (`istupo()==1`) | nhân vật test cấp 149: game 66.603, mô hình 22.350; dò thêm cấp 8/9/10/19/20/29/48–51/99/100/109/149/150/199 | `tupo_cost()` theo đúng quy tắc trên |
| Tỉ lệ cấp > 93: lệch ±1% do game làm tròn 2 phần trừ tới 0,1 | cấp 127 (game 8%, mô hình 7%), cấp 140 (6% / 7%) | `rate()` làm tròn giống `tupo.php` |
| Online 24h vẫn cộng thêm 1 phiên tu luyện offline; bế quan cộng chung với tu luyện offline (code thật loại trừ, `online_reward.php`: đang tu luyện thì không tính bế quan) | đọc code | phiên offline dài tối đa bằng số giờ offline; chọn cái lợi hơn giữa bế quan và tu luyện offline |

Do sửa phí đột phá, kết quả DOT_G mục 6.2 (ngày 30, tự săn đồ) đổi:

| Kịch bản | Cũ | Mới |
|---|---|---|
| Nhẹ 2h | 130 | 127 |
| Vừa 5h | 148 | 144 |
| Cày 10h | 195 | 175 |

**Giới hạn — chưa mô phỏng / chưa kiểm chứng:**
- **Đối chiếu thật mới kiểm công thức tại đúng trạng thái từng mốc**: tỉ lệ, phí, thanh tu vi, 1 lượt tu luyện, thể lực, tỉ lệ đột phá thực. Chưa phải chuỗi 30 ngày chơi thật.
- **Chưa đưa vào mô hình:**
  - Vấn Đỉnh Đài (PvP, thưởng MT hạng 1–10)
  - Phó Bản, Hầm Mỏ
  - Thu nhập từ Luyện Đan / Chế Tạo
  - Boss Linh Căn / Huyết Mạch / Tông Môn
  - Thưởng Tông Môn (+10% EXP tu luyện)
  - Đột Phá Đan, sự kiện cuối tuần
  - Mua bán Phường Thị
- Máy chủ 1 người (mục 6.2): là **biên trên** của LT/MT/cấp từ tuần 1 trở đi.
- Thời gian thao tác cố định: 1,5 giây/đòn, quái cùng cấp 5,5 đòn, boss cùng cấp 50 đòn. Chưa tính đồ tốt hơn giúp đánh nhanh hơn.

**Chạy lại:**

```
python3 tools/sim/moc_online_sim.py              # bảng mục 2–5
bash tools/cloud/start_env.sh                   # server thử (BAN_GIAO_PHIEN.md mục 5)
php tools/sim/moc_crosscheck.php --bt 400       # 27 nhân vật test + bấm đột phá thật
php tools/sim/moc_crosscheck.php --clean        # dọn nhân vật test (token moctest_*)
```
