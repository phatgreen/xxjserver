# Báo cáo nhân vật test theo mốc thời gian — ngày 1 / tuần 1 / tháng 1, 1h → 24h/ngày

> Ngày đo: 2026-10-29 · Nhánh `claude/tong-quan-du-an` (code mới nhất) · Môi trường cloud: MariaDB 10.11 + `docs/db/game.sql` + 30 migration (`kt0` → `lan9`, `drop_cover`, `tongmon`, 4 migration 10-29), server thử `127.0.0.1:8091`.
> Cách làm giống `DOT_G_KINH_TE_THE_LUC_TRANG_BI.md` mục 6 (mô hình `tools/sim/econ_sim.py`, công thức lấy từ code), mở rộng ra 9 mức giờ chơi, **đủ mọi tính năng online**, và **đối chiếu bằng nhân vật test thật trên server**.
> **Bản 2 (cùng ngày):** thêm các tính năng bản 1 chưa mô phỏng: Boss Linh Căn, Hầm Mỏ, Vấn Đỉnh Đài, Tông Môn, Luyện Đan, Chế Tạo, bán đồ dư, Đột Phá Đan, sự kiện cuối tuần. Kèm theo là **5 lỗi kinh tế / cơ chế** phát hiện được trong lúc làm (mục 6).

| Mục | Nội dung |
|---|---|
| 1 | Cách đo + đối chiếu thật |
| 2 | So sánh 3 kịch bản: cấp theo mốc |
| 3 | Ngày 1 |
| 4 | Tuần 1 |
| 5 | Tháng 1 |
| 6 | Lỗi kinh tế / cơ chế phát hiện được |
| 7 | Nhận xét cân bằng |
| 8 | Mô hình: những gì đã thêm / sửa, giới hạn, cách chạy lại |

---

## 1. Cách đo

**Mô hình:** `tools/sim/moc_online_sim.py` gọi `econ_sim.sim()`.
- 9 mức giờ online/ngày: **1, 2, 4, 6, 8, 12, 16, 20, 24h**. Ghi số ở **ngày 1, 7, 30**. Mỗi kịch bản chạy 30 lần, lấy trung bình (kèm min–max cấp).
- 3 kịch bản:
  - **Cũ:** bản 1, chưa có các tính năng bổ sung.
  - **Đủ (không bán đan):** đủ tính năng, nhưng không bán Đột Phá Đan.
  - **Đủ (luật hiện tại):** đủ tính năng, bán Đột Phá Đan cho NPC như game đang cho phép.
- Chơi hết tính năng online theo giới hạn của từng mốc. Các tính năng có từ bản 1:
  - tu luyện liên tục (thường 30 phút/lượt, LT ×2 khi đủ quỹ);
  - quái (thể lực 100 + cấp − 1, hồi 1/phút);
  - boss thế giới (chênh < 35 cấp mới nhận LT/MT);
  - đột phá (cả sự kiện thứ 7 +5% / chủ nhật +10%);
  - nhiệm vụ ngày / tuần / tháng / chính tuyến, thưởng trực tuyến, bế quan, Lộ Trình Cảnh Giới;
  - Tỏa Yêu Tháp, săn đồ 15 ô, linh sủng, võ công / công pháp, linh căn, thiên phú, thể chất, cường hóa.
- **Thêm ở bản 2:**

| Tính năng | Mở | Mô phỏng | Nguồn code |
|---|---|---|---|
| Boss Linh Căn thế giới | 51 | Dùng **hết thời gian online còn lại** sau boss + quái. Hồi sinh ngay khi chết, không tốn thể lực. Mỗi con: 300 LT + 3 đá hệ + 1,5 Bình Anh | `linhcan_boss.php:63-79,176-193`, `game/linhcan_boss.php:114` |
| Đột Phá Đan rơi từ boss | 30 | Boss 30 / 45 / 60 / 70 rơi đan djid 20 / 21 / 22, trung bình 0,375 viên/lần hạ, **không bị chặn chênh cấp**. Bán NPC 50k / 150k / 200k LT. Với cấp ≤ 250, đan không cộng % đột phá (chỉ hợp cảnh giới 7+) | `boss.php:624-636`, `bagdj.php:57`, `tupo.php:142-191` |
| Hầm Mỏ | 60 | 5 lượt miễn phí/ngày; lượt 6–30 tốn 5 MT nên không đào | `hammo.php:33-134` |
| Vấn Đỉnh Đài | 80 | Máy chủ 1 người: không có đối thủ (0 trận) nhưng **giữ hạng 1**, được 10.000 LT + 100 MT/ngày. Nhiệm vụ ngày D8 "thắng 3 PvP" không làm được (5/8 số ngày mất 8% thanh) | `vandinhdai.php:61-73,160`, `vandinhdai_reward.php` |
| Tông Môn tự lập | 30 | Lập bằng 200k LT. Boss tông môn đánh 1 mình nếu hạ được trong 25% thời gian online (4.700 tu vi + 1.750 LT + 65 MT/ngày). Nâng cấp bằng quyên góp LT + MT. **+cấp% tu vi tu luyện** | `club.php:17-29,208-242,599-607`, `club_boss.php:329-372`, `xiulian.php:106-115` |
| Luyện Đan | 15 | Khoảng 4 lò/ngày (đủ nhiệm vụ), ~404 LT/lò. **Dừng ở khoảng 115 lò** vì độc tính luyện đan không bao giờ giảm (mục 6) | `luonden.php:194-212,899,1085` |
| Chế Tạo | 200 | Thánh Phi Phong ô 8 bậc 200 (250 LT, hỏng không mất nguyên liệu) | `chetao.php`, `chetao_config.json` #4 |
| Bán / phân giải đồ dư | – | Đồ rơi không hơn đồ đang mặc thì bán NPC hoặc phân giải ra đá (10 LT/viên), chọn cái lợi hơn | `bagzb.php:102-152` |
| Cơ Duyên lần đầu ghé | 30/60/80 | Phó Bản 2% thanh + 200 LT, Hầm Mỏ 3% + 400, Vấn Đỉnh Đài 3% + 500 | `phoban.php`, `hammo.php`, `vandinhdai.php` |
| Nhiệm vụ tuần Linh Căn | 51 | 500 LT + 1,5 Bình Anh, cần có 1 lần đột phá linh căn trong tuần | `linhcan_quest.php:139-148` |
| Phó Bản | 30 | **Chỉ là trang menu** (dẫn tới Tháp, Vấn Đỉnh Đài, Hầm Mỏ, Sào Thú), không có lượt hay thưởng riêng | `game/phoban.php` |

- **Sửa mô hình ở bản 2:**
  - Đột phá linh căn tính **theo từng hệ** (Bình Anh + LT + tu vi × 5 hệ; bản 1 chỉ tính 1 lần).
  - **Huyết Mạch không nâng được** với F2P (mục 6).
- **Giả định:**
  - F2P, máy chủ chỉ 1 người (không ai tranh boss), không mua bán giữa người chơi.
  - Thời gian thao tác: 1,5 giây/đòn, 15 giây đi đường, quái cùng cấp 5,5 đòn, boss cùng cấp 50 đòn.
  - Công dùng cho boss Linh Căn / tông môn được ước lượng theo đúng giả định "boss cùng cấp 50 đòn".

**Đối chiếu thật** (`tools/sim/moc_crosscheck.php`): nhân vật test được INSERT với giá trị khởi tạo y hệt `cmd=cjplayer`, rồi đưa về đúng trạng thái mô hình ra (chỉ để đo). Số đọc qua route HTTP thật.

*(a) 27 nhân vật test, 9 mức giờ × 3 mốc* (cấp của kịch bản "luật hiện tại"):

| Hạng mục so sánh | Kết quả |
|---|---|
| Tỉ lệ đột phá hiển thị = mô hình | 27/27 |
| Phí LT đột phá = mô hình | 27/27 |
| Thanh tu vi `(cấp+1)²×110` | 27/27 |
| Linh khí/s = cấp | 27/27 |
| 1 lượt tu luyện 30' thật = `min(1800×cấp, thanh)` | 27/27 |
| Trần thể lực = 100 + cấp − 1 | 27/27 |

Bấm đột phá **thật** (không tính lần bị anti-flood chặn):

| Cấp | Tỉ lệ hiển thị | Bấm thật | Tỉ lệ đo |
|---|---|---|---|
| 12 | 64% | 1.000 (3 lần đo) | 62,4% |
| 96–97 | 11% | 900 (3 lần đo) | 10,0% |
| 229–346 | 5% | 900 (3 lần đo) | 5,4% |

Cả 3 nằm trong sai số mẫu.

*(b) Cơ chế mới, 1 nhân vật test cấp 150* (`--features`):

| Cơ chế | Mô hình | Đo thật | Khớp |
|---|---|---|---|
| Bán 1 Đột Phá Đan djid 20 / 21 / 22 cho NPC | 50k / 150k / 200k LT | 50.000 / 150.000 / 200.000 LT | OK |
| Boss Linh Căn có boss mới ngay sau khi bị hạ | có | id 41 → 42 | OK |
| Máu boss Linh Căn = 5000 + 500 × cấp | 65.500 (cấp 121) | 65.500 | OK |
| Hầm Mỏ: LT / lượt (cấp 150) | 101,6 | 97–113 (5 lần đo × 500–1.000 lượt) | OK |
| Hầm Mỏ: Cường Hóa Thạch / lượt | 0,255 | 0,17–0,27 (trung bình ~0,24) | OK |
| Hầm Mỏ: 5 lượt/ngày miễn phí | 1.000/1.000 lượt khi có 0 MT | 1.000/1.000 | OK |
| Vấn Đỉnh Đài thưởng hạng 1 | 10.000 LT + 100 MT | `{"yxb":10000,"czb":100}` | OK |
| Tiệm không bán Huyết Thạch (112) | không có | không có | OK |

## 2. So sánh 3 kịch bản — cấp trung bình (min–max)

| Giờ/ngày | Ngày 1 (cả 3 như nhau) | Tuần 1: Cũ / Đủ không bán đan / Luật hiện tại | Tháng 1: Cũ | Tháng 1: Đủ không bán đan | Tháng 1: Luật hiện tại |
|---|---|---|---|---|---|
| 1h | 12 (9–12) | 33 / 33 / 33 | 103 (97–108) | 102 (97–109) | 102 (96–107) |
| 2h | 16 (13–17) | 45 / 45 / 45 | 127 (118–132) | 129 (120–135) | 128 (116–137) |
| 4h | 19 (17–21) | 66 / 66 / 66 | 140 (129–148) | 162 (153–174) | **170** (160–178) |
| 6h | 22 (19–25) | 83 / 82 / 82 | 150 (139–179) | 191 (182–199) | **200** (191–210) |
| 8h | 25 (22–28) | 96 / 96 / 97 | 162 (144–191) | 211 (199–224) | **225** (216–236) |
| 12h | 29 (27–32) | 118 / 118 / 119 | 188 (159–202) | 252 (238–266) | **269** (260–276) |
| 16h | 35 (29–40) | 136 / 138 / 138 | 209 (190–249) | 287 (260–303) | **305** (287–319) |
| 20h | 42 (35–46) | 142 / 152 / 154 | 225 (189–248) | 303 (291–319) | **337** (326–349) |
| 24h | 48 (41–54) | 146 / 163 / 163 | 233 (199–257) | 320 (298–339) | **346** (333–360) |

Tháng 1 — LT thu / MT còn / tỉ lệ đột phá:

| Giờ/ngày | Cũ | Đủ (không bán đan) | Đủ (luật hiện tại) |
|---|---|---|---|
| 1h | 3,27M / 2.901 / 25% | 3,76M / 3.575 / 25% | 27,7M / 2.951 / 25% |
| 2h | 5,69M / 5.664 / 22% | 7,06M / 7.270 / 22% | 66,4M / 2.460 / 22% |
| 4h | 7,65M / 7.327 / 21% | 12,8M / 12,0K / 20% | 150M / 6.244 / 19% |
| 8h | 11,6M / 10,8K / 20% | 26,6M / 20,1K / 18% | 327M / 3.545 / 18% |
| 12h | 17,3M / 15,9K / 18% | 43,9M / 28,4K / 16% | 510M / 5.164 / 15% |
| 16h | 22,5M / 20,2K / 18% | 65,8M / 41,5K / 13% | 695M / 14,4K / 12% |
| 24h | 32,2M / 29,5K / 18% | 101M / 57,0K / 11% | **1.066M** / 8.323 / 10% |

- **Ngày 1 và tuần 1 gần như không đổi**: các tính năng mới mở từ cấp 30–80.
- **Tháng 1 ở mức ≥ 4h tăng 20–50% cấp**, chủ yếu nhờ **Boss Linh Căn hồi sinh ngay** (LT dồi dào nên tu luyện LT ×2 gần như liên tục) và Tông Môn (+cấp% tu vi tu luyện).
- **Bán đan làm LT tăng 7–10 lần nhưng cấp chỉ thêm khoảng 5–11%.** Khi LT không còn là nút thắt, cấp bị chặn bởi tỉ lệ đột phá và tu vi.
- MT còn ở kịch bản "luật hiện tại" thấp hơn vì LT dư nên quyên góp nâng Tông Môn nhanh hơn (tốn MT).

## 3. Ngày 1 (luật hiện tại)

| Giờ/ngày | Cấp | Tỉ lệ ĐP | Thử ĐP | Quái | Boss | LT thu | trong đó bán đan | LT chi ĐP | LT còn | MT còn | Tu vi: tu luyện / quái / NV |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | 12 | 65% | 15 | 25 | 0 | 5,2K | 0 | 1,0K | 1,0K | 5 | 75 / 7 / 18% |
| 2h | 16 | 62% | 23 | 36 | 0 | 6,9K | 0 | 2,5K | 0,9K | 5 | 77 / 9 / 14% |
| 4h | 19 | 60% | 28 | 58 | 0 | 10,9K | 0 | 4,3K | 0,7K | 8 | 79 / 13 / 7% |
| 8h | 25 | 57% | 37 | 101 | 0 | 20,5K | 0 | 9,7K | 1,2K | 8 | 79 / 15 / 7% |
| 12h | 29 | 54% | 46 | 145 | 0 | 31,7K | 0 | 17,0K | 1,8K | 8 | 80 / 15 / 6% |
| 16h | 35 | 53% | 56 | 189 | 35 | 848K | 663K | 29,2K | 102K | 220 | 79 / 13 / 7% |
| 20h | 42 | 50% | 70 | 232 | 113 | 2,62M | 2,11M | 55,3K | 829K | 684 | 80 / 13 / 7% |
| 24h | 48 | 46% | 83 | 276 | 202 | 5,05M | 4,15M | 85,6K | 3,01M | 1.245 | 81 / 13 / 6% |

- **Dưới 12h: LT là nút thắt**, chỉ có quái + nhiệm vụ. Cuối ngày còn 0,7–1,8K LT.
- **Từ 16h: lên cấp 30 ngay ngày đầu ⇒ boss cấp 30 rơi Đột Phá Đan.** Ngày đầu đã bán được 0,66–4,15M LT tiền đan.

## 4. Tuần 1 (luật hiện tại)

| Giờ/ngày | Cấp | Tỉ lệ ĐP | Boss | Boss LC | LT thu | bán đan | boss LC | LT chi ĐP | LT còn | MT còn | Tông Môn | Linh căn | Thiên phú | Cường hóa |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | 33 | 54% | 15 | 0 | 428K | 288K | 0 | 24K | 46K | 147 | – | – | – | +20 |
| 2h | 45 | 47% | 114 | 0 | 2,87M | 2,29M | 0 | 64K | 976K | 757 | 0,9 | 2 / bậc 0 | – | +30 |
| 4h | 66 | 38% | 423 | 328 | 13,0M | 11,0M | 98K | 240K | 9,37M | 2.126 | 1 | 75 / bậc 3 | – | +30 |
| 8h | 97 | 27% | 1.321 | 1.753 | 49,2M | 44,2M | 526K | 908K | 42,9M | 3.272 | 2 | 90 / bậc 4 | phẩm 4 cấp 25 | +30 |
| 12h | 119 | 23% | 2.409 | 3.687 | 89,1M | 81,1M | 1,11M | 2,05M | 48,6M | 2.343 | 3 | 90 / 4 | 5 / 30 | +30 |
| 16h | 138 | 22% | 3.422 | 6.168 | 129M | 119M | 1,85M | 3,54M | 46,2M | 3.202 | 3 | 90 / 4 | 5 / 30 | +30 |
| 24h | 163 | 20% | 5.528 | 11,3K | 209M | 194M | 3,38M | 6,82M | 122M | 6.199 | 3,1 | 90 / 4 | 6 / 36 | +30 |

- **Từ 2h/ngày, LT dư hàng triệu ngay tuần 1** (gần như toàn bộ là tiền đan). Vì vậy cường hóa +30 đạt ngay tuần 1 ở mức ≥ 2h.
- Linh căn đầy (90 / bậc 4, đủ 5 hệ) ở tuần 1 với mức ≥ 8h, kể cả khi đã tính đột phá theo từng hệ.
- Thể chất đầy 7 bậc ở tuần 1 với mức ≥ 16h.

## 5. Tháng 1 (ngày 30, luật hiện tại)

| Giờ/ngày | Cấp | Tỉ lệ ĐP | Thử ĐP | Tụt cấp | Boss | Boss LC | LT thu | LT chi ĐP | LT chi tu luyện ×2 | LT còn | MT thu (boss / NV / VĐĐ) | MT còn | Tông Môn |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | 102 | 25% | 233 | 1,5 | 725 | 854 | 27,7M | 1,28M | 148K | 20,8M | 2.969 / 779 / 667 | 2.951 | 2 |
| 2h | 128 | 22% | 340 | 2,2 | 1.762 | 2.658 | 66,4M | 3,04M | 255K | 47,2M | 5.547 / 794 / 1.370 | 2.460 | 3 |
| 4h | 170 | 19% | 514 | 3,0 | 3.932 | 7.596 | 150M | 7,85M | 520K | 62,1M | 9.314 / 1.063 / 1.937 | 6.244 | 3,2 |
| 6h | 200 | 19% | 670 | 4,7 | 6.178 | 13,3K | 237M | 14,2M | 830K | 139M | 13,5K / 1.163 / 2.190 | 5.817 | 4 |
| 8h | 225 | 18% | 804 | 5,8 | 8.526 | 19,0K | 327M | 21,6M | 1,19M | 216M | 19,9K / 1.314 / 2.307 | 3.545 | 5 |
| 12h | 269 | 15% | 1.053 | 7,6 | 13,1K | 31,8K | 510M | 40,3M | 2,02M | 372M | 31,9K / 1.579 / 2.493 | 5.164 | 5,8 |
| 16h | 305 | 12% | 1.304 | 10,4 | 17,8K | 45,2K | 695M | 65,6M | 3,00M | 528M | 45,2K / 1.871 / 2.580 | 14,4K | 6,1 |
| 20h | 337 | 10% | 1.558 | 11,8 | 22,5K | 59,2K | 885M | 97,3M | 4,11M | 676M | 62,3K / 1.921 / 2.600 | 15,9K | 7 |
| 24h | 346 | 10% | 1.687 | 12,9 | 27,1K | 73,4K | 1.066M | 115M | 4,52M | 827M | 74,5K / 2.022 / 2.647 | 8.323 | 7,8 |

**Nguồn LT tháng 1:**

| Giờ/ngày | Luật hiện tại: bán đan | boss | boss LC | quái | nhiệm vụ | khác (đồ dư, mỏ, VĐĐ, tông môn) | Không bán đan: LT thu | boss / boss LC / quái / NV / khác |
|---|---|---|---|---|---|---|---|---|
| 1h | 87% | 8% | 1% | 2% | 2% | 1% | 3,76M | 57 / 9 / 11 / 16 / 3% |
| 2h | 90% | 6% | 1% | 1% | 1% | 1% | 7,06M | 58 / 15 / 10 / 10 / 4% |
| 4h | 92% | 5% | 2% | 1% | 1% | 1% | 12,8M | 51 / 24 / 11 / 6 / 5% |
| 8h | 91% | 5% | 2% | 1% | 0% | 1% | 26,6M | 49 / 28 / 11 / 4 / 5% |
| 12h | 91% | 5% | 2% | 1% | 0% | 1% | 43,9M | 50 / 28 / 11 / 2 / 7% |
| 24h | 90% | 6% | 2% | 1% | 0% | 1% | 101M | 53 / 27 / 9 / 1 / 7% |

**Tính năng ở ngày 30** (luật hiện tại):

| Giờ/ngày | Trang bị (+ĐP / +TL) | Linh căn | Thiên phú | Thể chất | Huyết mạch | Cường hóa | Võ công | Công pháp | Linh sủng | Tháp | Lò đan |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | +15 / +21% | 90 / bậc 4 | 5 / 29 | – | phẩm 1 (kẹt) | +30 | 23 | 10 | phẩm 4 cấp 46 | 90 | 94 |
| 2h | +15 / +21% | 90 / 4 | 5 / 30 | 6,8/8 | kẹt | +30 | 27 | 10 | 4 / 55 | 117 | 103 |
| 4h | +15 / +21% | 90 / 4 | 6 / 36 | 8/8 (đầy) | kẹt | +30 | 35 | 10 | 4 / 67 | 150 | 111 |
| 8h | +18 / +30% | 90 / 4 | 6 / 36 | 8/8 | kẹt | +30 | 44 | 10 | 4 / 85 | 150 | 115 (trần) |
| 24h | +18 / +30% | 90 / 4 | 6 / 36 | 8/8 | kẹt | +30 | 53 | 10 | 4 / 118 | 150 | 115 |

Mở khóa tính năng: ở mức ≥ 2h, đủ 24/24 trong tháng 1; ở mức ≥ 16h, đủ trong tuần 1. Riêng mức 1h còn thiếu Thể Chất (cấp 120).

## 6. Lỗi kinh tế / cơ chế phát hiện được

| # | Mức | Lỗi | Bằng chứng | Hệ quả đo được | Hướng sửa (chờ chủ dự án chốt, chưa sửa) |
|---|---|---|---|---|---|
| E1 | **Nặng** | **Đột Phá Đan rơi từ boss bán lại NPC đúng 100% giá tiệm** (50k / 150k / 200k LT); rơi đồ boss **không bị chặn chênh cấp** | `boss`: `bossdj` 1,20 / 1,21 / 1,22, `djjv` 10; `bagdj.php:57` (bán = `djyxb`); **đo thật** 50.000 / 150.000 / 200.000 | 87–92% LT tháng 1 là tiền bán đan; 24h/ngày ≈ **960M LT/tháng** (tổng LT gấp ~10 lần) | Giá bán đan ≈ 5–10% `djyxb`, hoặc `no_sell`; và/hoặc chặn rơi đan khi chênh ≥ 35 cấp như LT/MT |
| E2 | **Nặng** | **Chế tạo + phân giải sinh LT vô hạn từ cấp 200**: Thánh Phi Phong (#4: 3 Cường Hóa Thạch + 2 đá Mộc = 250 LT) phân giải ra `round((0+589)/2)` ≈ 295 đá × 10 LT | `chetao_config.json` #4, `zhuangbei` 91 `zbfy` 589, `bagzb.php:122-152` | Lãi ~2.700 LT/vòng, vài giây/vòng (**đọc code, chưa bấm thật**; chưa đưa vào mô hình vì không có trần) | Phân giải theo phẩm / bậc chứ không theo chỉ số; hoặc đá phân giải không bán được |
| E3 | Vừa | **Boss Linh Căn thế giới hồi sinh ngay khi chết** (120 phút chỉ là thời gian tồn tại), không tốn thể lực | `linhcan_boss.php:63-79`, `game/linhcan_boss.php:114`; **đo thật** id 41 → 42 | Boss LC cho 1–2% LT khi có bán đan, **24–28% LT khi không bán đan** (mức ≥ 4h); 73K lần hạ/tháng ở 24h | Hồi sinh theo `spawn_interval_minutes` tính từ `killed_at` |
| E4 | Vừa | **Huyết Mạch bế tắc với F2P**: Huyết Thạch (112) tiệm loại trừ, không quái / boss thường nào rơi, chỉ boss Huyết Mạch rơi; mà boss đó cần thức tỉnh bậc 1 (32 cấp mạch, mỗi cấp tốn Huyết Thạch) | `shangdian.php:352`; dò `guaiwu_config` + bảng `boss`; **đo thật** tiệm không có | Huyết Mạch kẹt ở phẩm 1 mãi mãi; nhiệm vụ NPC 62 "giao 3 Huyết Thạch" không làm được | Phát 1 lượng Huyết Thạch khi mở tính năng (cấp 101), hoặc cho boss thường cấp ≥ 100 rơi |
| E5 | Nhẹ | **Độc tính luyện đan không bao giờ giảm**: `luonden_reduce_toxin_hourly` không có chỗ nào gọi; trên 1.000 thì chặn luyện | `luonden.php:1085,1277` | Cả đời nhân vật luyện được khoảng 115 lò ⇒ nhiệm vụ tháng M1 (100 lò) chỉ làm được 1 lần, D3 / W1 / W7 kẹt sau tháng 1 | Gọi hàm giảm độc theo giờ (cron hoặc khi mở trang Luyện Đan) |
| – | Nhẹ | Máy chủ 1 người: Vấn Đỉnh Đài không có đối thủ nhưng vẫn giữ hạng 1 (10.000 LT + 100 MT/ngày); D8 "thắng 3 PvP" không làm được. Nhiệm vụ Tông Môn chỉ cộng EXP tông môn, không thưởng người chơi | `vandinhdai.php:61-73,176`, `club_quest.php:255-258` | +2,6K MT/tháng ở 24h | Ghi nhận; chỉ ảnh hưởng máy chủ ít người |

## 7. Nhận xét cân bằng

1. **Theo luật hiện tại, LT không còn là tiền tệ khan hiếm từ cấp 30** (E1).
   - Mức 1h/ngày cũng thu 27,7M LT/tháng, cuối tháng còn dư 20,8M.
   - Mọi bồn tiêu LT (linh căn, cường hóa, thể chất, thiên phú, tu luyện ×2) đều đầy trong tháng 1.
   - **E1 nên sửa trước khi mở server.** Nếu không, giá trị LT của mọi gói nạp hay phần thưởng đều mất ý nghĩa.
2. **Nút thắt thật là tỉ lệ đột phá.**
   - Tháng 1, ở mức ≥ 12h, nhân vật ở vùng cấp 270–350 với tỉ lệ 10–15%.
   - Có 1.000–1.700 lần thử và 8–13 lần tụt cấp trong tháng.
   - Vì vậy 24h chỉ hơn 16h khoảng 13% cấp, và bán đan (LT ×7–10) chỉ thêm 5–11% cấp.
3. **Kể cả khi sửa E1, Boss Linh Căn (E3) vẫn là nguồn LT thứ 2** (24–28%) và đẩy cấp tháng 1 lên 20–50% so với bản 1.
   - Nên sửa E3 cùng đợt.
   - Mức chơi ít (1–2h) gần như không bị ảnh hưởng (cấp 102 / 129 so với 103 / 127).
4. **Tính năng đầy quá sớm** (xác nhận lại DOT_G mục 5, nay rõ hơn vì đủ nguồn LT):
   - Cường hóa +30 ở tuần 1 với mức ≥ 2h.
   - Linh căn 90 / bậc 4 ở tuần 1 với mức ≥ 8h.
   - Thể chất đầy ở tuần 1 với mức ≥ 16h.
   - Công pháp 10 ở ngày 1 với mức ≥ 12h.
   - Tháp 150 ở tháng 1 với mức ≥ 4h.
5. **MT miễn phí tháng 1:** thu 4,4K (1h) → 79K (24h), chủ yếu từ boss.
   - Phần lớn đã quyên góp lên Tông Môn (cấp 2 → 7,8), nên số dư còn 2,5K–14K.
   - Người cày nhiều vẫn tích khoảng 2,5K MT/ngày. Nên đối chiếu với giá gói nạp.
6. **Ngày 1 vẫn thiếu LT với người chơi dưới 12h** (0,7–1,8K LT cuối ngày). Đây là cùng kết luận của báo cáo 2026-10-16.

## 8. Mô hình: đã thêm / sửa, giới hạn, chạy lại

**Sửa mô hình** (`tools/sim/econ_sim.py`; chỉ sửa công cụ, không đổi game). Các tính năng bổ sung bật bằng `sim(..., full=True, sell_pill=True|False)`; mặc định `full=False` cho kết quả như bản 1.

| Sửa | Phát hiện bằng |
|---|---|
| Phí đột phá `3·L²` khi **cấp kế chia hết cho 10** hoặc đang ở mốc 50 (`istupo()==1`), bản cũ chỉ ở mốc 50 | nhân vật test cấp 149: game 66.603, mô hình 22.350; dò thêm 16 cấp biên |
| Tỉ lệ cấp > 93: làm tròn 2 phần trừ tới 0,1 như `tupo.php` | cấp 127 / 140 lệch ±1% |
| Online 24h không còn phiên tu luyện offline; bế quan và tu luyện offline loại trừ nhau | đọc `online_reward.php:137` |
| (full) Đột phá linh căn theo từng hệ | `linhcan.php:537-583` |
| (full) Huyết Mạch không nâng được với F2P | E4 |

Vì sửa phí đột phá, kết quả DOT_G mục 6.2 (ngày 30, tự săn đồ, bản 1) đổi:

| Kịch bản | Cũ | Mới |
|---|---|---|
| Nhẹ 2h | 130 | 127 |
| Vừa 5h | 148 | 144 |
| Cày 10h | 195 | 175 |

**Giới hạn — chưa mô phỏng / chưa kiểm chứng:**
- **Đối chiếu thật mới kiểm công thức tại đúng trạng thái từng mốc** và từng cơ chế riêng lẻ, chưa phải chuỗi 30 ngày chơi thật.
- Máy chủ 1 người: **biên trên** của LT / MT / cấp. Máy chủ đông người chia boss, bán đan cũng chia theo số người săn boss 30–70.
- Chưa mô hình hóa:
  - E2 (vòng chế tạo – phân giải, không có trần);
  - máu người chơi khi đánh boss Linh Căn (boss đánh trả 200 + 20·cấp);
  - chỉ số từ đan Luyện Đan (công +40/viên), vì mô hình không có công thức sát thương: tốc độ đánh cố định theo giả định 50 đòn / 5,5 đòn;
  - mua bán Phường Thị giữa người chơi;
  - Đột Phá Đan dùng để đột phá (chỉ có tác dụng từ cấp 350, ngoài vùng tháng 1).
- Công boss Linh Căn / tông môn: ước lượng `boss_hp(L)/50 + 10,5·L`, không đo từ nhân vật thật.

**Chạy lại:**

```
python3 tools/sim/moc_online_sim.py              # bảng mục 2–5 (3 kịch bản)
bash tools/cloud/start_env.sh                   # server thử (BAN_GIAO_PHIEN.md mục 5)
php tools/sim/moc_crosscheck.php --bt 400       # 27 nhân vật test + bấm đột phá thật
php tools/sim/moc_crosscheck.php --features     # kiểm thật cơ chế mới (đan, boss LC, Hầm Mỏ, VĐĐ, Huyết Thạch)
php tools/sim/moc_crosscheck.php --clean        # dọn nhân vật test (token moctest_*)
```
