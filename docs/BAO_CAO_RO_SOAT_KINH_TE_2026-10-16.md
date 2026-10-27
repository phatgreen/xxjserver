# Báo cáo rà soát kinh tế & tiến trình nhân vật

Ngày đo: 2026-10-16 · Server local: `php -S 127.0.0.1:8091` + MySQL `game` · PHP 5.6.9

| Mục | Nội dung |
|---|---|
| 1 | Cách đo (harness thật, không tính tay) |
| 2 | Nhật ký 1 ngày đầu của nhân vật đăng ký mới |
| 3 | Phát hiện cốt lõi: tường chặn cấp 1 |
| 4 | Dự phóng 1 ngày / 1 tuần / 1 tháng / 1 năm (4 hồ sơ, trước & sau sửa) |
| 5 | Đường chi phí Linh Thạch / Ma Thạch từng hệ thống |
| 6 | Đề suất phát triển: nhiệm vụ + tính năng |
| 7 | Gói quà nạp theo giai đoạn (mở server → 1 tuần → 1 tháng → 1 năm) |
| 8 | Lỗi đã sửa & đã kiểm chứng / lỗi cần chủ chốt |
| 9 | Phần CHƯA kiểm chứng |

---

## 1. Cách đo

Tất cả số liệu mục 2–3 lấy bằng cách **thực sự chơi** qua route HTTP thật của game, không phải tính tay:

- `bak/tmp_analyze/probe_tuluyen.php` → **vòng lặp tu luyện thật 3 ngày** (mục 2). Đây là bản chuẩn.
- `bak/tmp_analyze/play_day1b.php` / `out_day1b.txt` → **đã thu hồi**: harness này để nhánh nhiệm vụ `continue` nuốt giờ chờ thể lực, bước tu luyện không bao giờ chạy, nên kết luận "lv1 sau 24,5h" là sai.
- `bak/tmp_analyze/probe_combat_curve.php` → sát thương/số đòn mỗi cấp (`lv1→16`).
- `bak/tmp_analyze/probe_nv42b.php` → 1 trận thật với đúng con quái nhiệm vụ NV42.

Nhân vật thật được tạo qua `cmd=cjplayer` (đường đăng ký gốc), mỗi lần đo tạo acc mới, không cộng lệnh bằng SQL trừ khi ghi rõ là "chỉ để đo".

Ràng buộc của môi trường đo (đã ghi trong trí nhớ dự án):
- Đồng hồ MySQL lệch -7 giờ so với PHP → mọi dịch thời gian phải tính bằng PHP.
- Thể lực chỉ hồi khi có một request HTTP chạy `settle_stamina()` (`game.php:450-466`).
- **Mọi request không phải `pve`/`pvegj` sẽ xóa quái nhân vật đang claim** (`game.php:402-406`) → giữa trận chỉ được nạp thể lực/máu bằng SQL.

## 2. Vòng lặp thật của nhân vật mới: tu luyện hấp thụ linh khí

> **Hiệu chỉnh lần 2 (cùng ngày):** bản trước của mục này lấy từ `out_day1b.txt` và kết luận "ngày đầu gần như không tiến triển". Kết luận đó **sai**, do harness: nhánh nhiệm vụ `continue` nuốt hết giờ chờ thể lực nên **bước tu luyện không bao giờ được chạy** (`play_day1b.php:188-247`). Đo lại bằng `bak/tmp_analyze/probe_tuluyen.php` (nhân vật `TuLuyen213`, tạo qua `cmd=cjplayer` thật, chỉ dịch `xiuliantime`, không cộng exp bằng SQL).

Cơ chế (`game/xiulian.php:112-197`): linh khí mỗi giây = `ulv × VIP × hệ số` (lv1 = 1,0/s), một lượt ngồi thiền miễn phí tối đa **30 phút**, phần thưởng `min(30 phút × tốc độ, umaxexp)` — **bar đầy là cắt**.

Đo thật, 3 ngày × 4 lượt đăng nhập (mỗi lượt: thiền 30' rồi bấm đột phá):

| lượt | linh khí/s | 30' sinh ra | bar hiện tại | thực nhận | đột phá | lv cuối lượt | LT còn |
|---|---|---|---|---|---|---|---|
| n1-l1 | 1,0 | 1.800 | 440 | 440 (đầy bar) | 2 | 3 | – |
| n1-l2 | 2,0 | 3.600 | 990 | 990 | 1 | 4 | – |
| n1-l3 | 3,0 | 5.400 | 1.760 | 1.760 | 2 | 5 (hết ngày 1) | 1.906 |
| n2-l4 | 8,0 | 14.400 | 8.910 | 8.910 | 2 | 9 (hết ngày 2) | 1.536 |
| n3-l3 | 11,0 | 19.800 | 15.840 | 15.840 | 3 | 11 → **tràn bar** | – |
| n3-l4 | 11,0 | 19.800 | 18.590 | 0 | 2 | 12 (hết ngày 3) | 523 |

Bốn hệ quả đo được, thay toàn bộ kết luận cũ:

1. **Ngày đầu không bị đứng.** 4 lượt đăng nhập = **lv1 → lv5**; ngày 2 lv9, ngày 3 lv12. Lv đi theo số lượt đăng nhập, không phụ thuộc giết quái.
2. **Một lượt thiền 30 phút luôn đầy bar cho tới ~lv14** (1800·lv ≥ 110·(lv+1)² đúng với lv ≤ 14) → **lượt thừa chỉ tổ xóa exp**: lượt n1-l1 sinh 1.800 nhưng chỉ nhận 440, mất 1.360; lượt n3-l3 mất 3.960.
3. **LT là nút thắt thật của 3 ngày đầu, không phải exp.** Từ 2.000 LT ban đầu, chỉ riêng tiền đột phá lv1→12 đã ngốn **1.477 LT** (định mức `lv·(lv+1)`), còn 523 LT. Người chơi mới vì vậy thiếu LT cho nhiệm vụ/cường hóa chứ không thiếu exp.
4. **Trần thể lực 100 điểm (77 đòn/ngày) không tăng theo cấp** vẫn đúng, và nay là ràng buộc chính của *nhiệm vụ giết quái* (mục 3), không phải của việc lên cấp.

## 3. Tường chặn thật: nhiệm vụ giết quái

Đo trận thật với đúng mục tiêu NV42 (`probe_nv42b.php`):

```
quai: Son Tri  hp=240  cong=10  thu=14  lv=1   (map 228, gyid 56)
nhan vat: lv1 cong 12  thu 5  hp 35/35
du doan: 1.8 sat thuong/don  =>  134 don/kill
ket qua: 120 don -> GIET DUOC
   sat thuong/don: 2
   LT nhan: 112 | exp nhan: 108 | hp toi thieu: 29
   1 ngay = 77 don => 1 con = 1,6 ngay => NV42 (8 con) = 12,5 ngay the luc
```

Vì sao sát thương bị nghiền xuống 2:

- Công thức gây sát thương: `max(0.15 * ugj, ugj - 0.75 * gfy)`.
- Cấp 1: `ugj = 12`, Sơn Trư `gfy = 14` → `12 - 10.5 = 1.5` < `0.15 * 12 = 1.8` → chỉ số 15% sàn được chọn, làm tròn thành **2/đòn**.
- Trần phòng thủ của người chơi (`ufy = 5`) không liên quan; sàn 15% mới là nguyên nhân.

Chu kỳ đòn/giết đo thật theo cấp (`probe_combat_curve.php`, 1 đòn = 5 thể lực):

| lv | công | thủ | hp tối đa | quái đo | đòn/kill | LT/con | exp/con |
|---|---|---|---|---|---|---|---|
| 1 | 12 | 5 | 35 | Triết Nhân Phong (map 228) | 14 | 132 | 108 |
| 1 | 12 | 5 | 35 | **Sơn Trư (NV42)** | **120** | 112 | 108 |
| 2 | 15 | 9 | 69 | Sơn Trư | 48 | 110 | 54 |
| 3 | 19 | 13 | 104 | Sơn Trư | 27 | 131 | 36 |
| 4 | 23 | 17 | 141 | Sơn Trư | 19 | 186 | 27 |
| 5 | 27 | 21 | 180 | Sơn Trư | 15 | 97 | 21 |
| 8 | 39 | 33 | 303 | Triết Nhân Phong | 4 | 23 | 13 |
| 12 | 60 | 56 | 486 | Triết Nhân Phong | 3 | 45 | 9 |

Bốn hệ quả dây chuyền:

1. **Tường chặn nằm ở nhiệm vụ giết quái, không ở việc lên cấp.** Người chơi mới vẫn lên lv5 sau ngày đầu (mục 2), nhưng NV42 đòi 8 Sơn Trư: ở lv5 hết `15 × 8 = 120 đòn` ≈ **1,6 ngày thể lực**, ở lv1 hết 12,5 ngày. Ai vừa đăng nhập vừa chăm chỉ thì tới NV42 khoảng ngày 2–3 và kẹt ở đó vì thể lực, không phải vì exp.
2. **Trần thể lực không tăng theo cấp** (`umaxstamina = 100` cố định) → từ cấp 12 trở lên, mỗi con quái chỉ cho 9–45 LT và 3–9 exp: giết quái gần như không còn là nguồn thu nhập, trong khi mỗi đòn vẫn tốn đúng 5 thể lực như nhau. Nguồn exp thực tế duy nhất là tu luyện + nhiệm vụ.
3. **exp từ quái bị vô hiệu** vì bar mỗi cấp tăng theo `(lv+1)^2 * 110` trong khi exp/con quái giảm theo hiệu cấp (`glv*107/(chenhLev+1)`). Ở hồ sơ A một năm chỉ 77,3 K exp từ quái so với 41,53 M từ tu luyện.
4. **LT từ quái bị bằng 0 với mọi quái `ggj <= 3`** (`pve.php:357-359, 424-430`): sát thương trả về của quái làm tròn xuống 0 → `$hurt = true` → `$yxb = 0`. Quái cấp 1 ở map đầu (Hồ Yêu `ggj=1`, Triết Nhân Phong `ggj=5`) nằm sát ngưỡng này; các map quái yếu sẽ nuôi người chơi bằng 0 LT mà không có thông báo nào giải thích.

## 4. Dự phóng 1 ngày / 1 tuần / 1 tháng / 1 năm

`bak/tmp_analyze/sim4.php` mô phỏng 4 hồ sơ chơi, đã trừ chi phí đột phá, cường hóa, mua trang bị. Cột "sau sửa" dùng `tupo.php` đã vá (bỏ lần quay thứ hai làm sai tỷ lệ thất bại — xem mục 8).

| Hồ sơ | Mô tả | 1 ngày | 1 tuần | 1 tháng | 1 năm (trước) | 1 năm (sau sửa) |
|---|---|---|---|---|---|---|
| A | F2P chăm chỉ, 4 lượt đăng nhập/ngày | lv2 | lv8 | lv31 | lv97 | **lv103** |
| B | F2P cày cật lực, 16 lượt + mua thẻ Tam Linh | lv2 | lv8 | lv31 | lv97 | **lv101** |
| C | P2W nhẹ, nạp ~500 K/tháng + thẻ ngày | lv2 | lv8 | lv31 | lv104 | **lv112** |
| D | Whale, nạp ~10 M/tháng + thiên phú Đột Phá tối đa | lv2 | lv8 | lv31 | lv149 | **lv183** |

Chi tiết hồ sơ A (F2P), nguồn `out_fixed.txt`:

| mốc | lv | LT | MT | thiên phú | exp tu luyện | exp nhiệm vụ | exp quái |
|---|---|---|---|---|---|---|---|
| 1 ngày | 2 | 12,6 K | 105 | 5 | 440 | 3,5 K | 428 |
| 1 tuần | 8 | 92,1 K | 135 | 35 | 22,3 K | 63,7 K | 5,1 K |
| 1 tháng | 31 | 551,8 K | 650 | 150 | 1,15 M | 725,1 K | 10,1 K |
| 1 năm | 103 | 38 | 6,7 K | 510 | 41,53 M | 8,81 M | 77,3 K |

Một năm của hồ sơ A: thu 4,25 M LT (nhiệm vụ + thành tựu 4,23 M; giết quái chỉ 26,3 K), chi 4,02 M LT (đột phá 4,01 M). **Exp bị xóa vì tràn bar khi đăng nhập thừa: 191,26 M** — gấp 4,6 lần toàn bộ exp tu luyện.

Cảnh báo độ tin cậy (đã hiệu chỉnh theo mục 2): mô phỏng cho ngày đầu **lv2 / 12,6 K LT**, chơi thật ra **lv5 / 1.906 LT**. Tức là mô hình **đánh giá thấp tốc độ lên cấp** (vì bỏ qua trần "1 lượt thiền = 1 bar") và **đánh giá quá cao thu nhập LT** (gấp 6,6 lần) ở tuần đầu. Các mốc dài hạn (1 tháng, 1 năm) vẫn dùng được vì nguồn LT chủ yếu là nhiệm vụ + thành tựu, nhưng nên hiểu là **biên trên** của LT, không phải giá trị kỳ vọng.

## 5. Đường chi phí theo hệ thống

**Bar exp mỗi cấp** = `(lv+1)^2 * 110` (`player.php:905`) → lv1 440, lv10 13,3 K, lv50 286 K, lv100 1,12 M, lv1000 110 M, lv4300 2,03 B.

**Phút tu luyện để đầy 1 bar** (trần 30 phút lượt miễn phí / 3 giờ thẻ x2 / 6 giờ thẻ x3):

| lv | miễn phí | x2 LT | x3 MT |
|---|---|---|---|
| 1 | 7 | 4 | 2 |
| 10 | 22 | 11 | 7 |
| 30 | 59 | 29 | 20 |
| 50 | 95 | 48 | 32 |
| 100 | 187 | 94 | 62 |
| 300 | 554 | 277 | 185 |

Từ ~lv16, một lượt đăng nhập 30 phút không còn đầy bar → lượt thừa chỉ tổ tràn và mất exp.

**LT để qua từng cấp** (đột phá, chưa tính thiên phú Đột Phá; bấm đúng kỳ vọng `1/tỷ_lệ_thật`):

| lv | bar exp | tỷ lệ thật | đòn/cấp | LT/cấp | LT tích lũy |
|---|---|---|---|---|---|
| 10 | 13,3 K | 66% | 22 | 110 | 220 |
| 30 | 105,7 K | 54% | 58 | 930 | 2,9 K |
| 50 | 286,1 K | 49% | 95 | 7,5 K | 30,3 K |
| 70 | 554,5 K | 27% | 132 | 5 K | 64,9 K |
| 100 | 1,12 M | 16% | 187 | 30 K | 364,6 K |
| 101 | 1,14 M | **5%** | 188 | 10,3 K | 570,6 K |
| 200 | 4,44 M | 5% | 370 | 120 K | 4,61 M |
| 500 | 27,6 M | 5% | 920 | 750 K | 25,0 M |
| 1000 | 110 M | 5% | 1837 | 3 M | 85,0 M |
| 4300 | 2,03 B | 5% | 7887 | 55,5 M | **1,43 B** |

Mốc lv101 là vách đá: tỷ lệ đột phá rơi về 5% và LT/cấp tăng gấp 3,4 lần ngay tại cấp đầu tiên của cõi tiếp theo. Với thu nhập 4,25 M LT/năm (hồ sơ A), **toàn bộ thang 4300 cấp cần 337 năm**; kể cả thiên phú Đột Phá +30% thì tổng LT tích lũy đến lv4300 vẫn là 550 B — không thể đạt.

**Cường hóa trang bị (15 ô):**

| mức | LT | đá Cường Hóa | số lần bấm |
|---|---|---|---|
| +5 | 435 | 1 K | 135 |
| +10 | 5,9 K | 4,3 K | 285 |
| +20 | 207,6 K | 31,1 K | 855 |
| +30 | 6,86 M | 351,9 K | 4,8 K |
| +40 | 14,42 M | 638,5 K | 8 K (dừng ở +31, p=0 từ +31) |

**Nguồn thu cố định** (nên dùng làm chuẩn khi đặt quà nạp):

| nguồn | exp | LT | MT |
|---|---|---|---|
| Nhiệm vụ chính 21 mốc (cap 1–50) | 43,6 K | 16,1 K | – |
| Nhiệm vụ ngày (5/8 mốc) | 488 | 238 | – |
| Thưởng 5 mốc ngày | 1000 | 500 | 5 |
| Nhiệm vụ tuần | 39 K | 16,2 K | 20 |
| Nhiệm vụ tháng | 460 K | 200 K | 400 |
| Nhiệm vụ ẩn (8) | 95 K | 46 K | – |
| Thành tựu 30 bậc (nhận 1 lần) | 5,02 M | 2,16 M | – |

## 6. Đề xuất phát triển

### 6.1 Nhiệm vụ — ưu tiên số 1: phá tường chặn ngày đầu

| # | Việc | Mốc | Vì sao (số đo được) |
|---|---|---|---|
| Q1 | **Tách NV42 thành 2 nhiệm vụ**: NV42a yêu cầu 3 Sơn Trư, NV42b 5 con; hoặc đổi đích sang Triết Nhân Phong (`gyid 55`, cùng map 228, 14 đòn/kill) | ngay | 8 Sơn Trư = 12,5 ngày thể lực ở lv1. Đổi sang Triết Nhân Phong = 1,1 ngày. |
| Q2 | **Thưởng trang bị ngay NV41** (`rwzb` đang trống ở chuỗi đầu) | ngay | Nhân vật lv1 công 12 / thủ 5, chưa có món nào. Một món vũ khí `realm_require<=1` đưa công lên ~20 → 120 đòn giảm còn ~30. |
| Q3 | **Thêm nhiệm vụ "hướng dẫn đột phá + tu luyện" trong 30 phút đầu**, thưởng 1 thẻ tu luyện miễn phí | tuần 1 | 440 exp bar lv1 = 7 phút tu luyện, nhưng ngày đầu thực tế 0 lượt đột phá vì người chơi không thấy đường nào ngoài giết quái. |
| Q4 | **Sửa trần thể lực**: `umaxstamina` tăng theo cấp (ví dụ 100 + 5·lv, hoặc +10 mỗi cõi) | 1 tuần | 100 điểm/77 đòn vĩnh viễn không đổi; từ lv12 mỗi con quái 3–9 exp, nghĩa là cày 1 ngày được exp của 1 nhiệm vụ. |
| Q5 | **Nhiệm vụ tuần cho người lv1–10** (chuỗi hiện gần như chỉ có ý nghĩa từ lv30+) | 2 tuần | Tuần 1 của hồ sơ A = lv8; không còn gì để làm sau khi ăn xong 8 nhiệm vụ ngày. |
| Q6 | Nối chuỗi chính từ lv50 → lv101 (hiện `renwu_config.json` chỉ có 21 mốc, hết ở cap 50) | 1 tháng | Sau lv50 người chơi chỉ còn tu luyện + thành tựu; thành tựu 30 bậc đã ăn hết trong năm đầu (10/30 ở hồ sơ A). |
| Q7 | **Bỏ điều kiện chế tạo ở lv300/350**, thay bằng điều kiện thiên phú/phu vị | 1 tháng | Không ai tới lv300 trong 1 năm ở mọi hồ sơ; toàn bộ hệ thống Luyện Đan/Luyện Khí là nội dung chết với 100% người chơi. |

### 6.2 Tính năng

| # | Việc | Tác động đo được |
|---|---|---|
| F1 | **Bar exp không được vượt trần khi chưa dùng**: thay vì xóa, cộng dồn vào "tu luyện tích lũy" tối đa 7 ngày | Hồ sơ A mất 191,26 M exp/năm; hồ sơ B mất 1,12 B. Đây là nguồn tăng lực lớn nhất mà game đang tự vứt. |
| F2 | **Tỷ lệ đột phá hiển thị đúng** (`tỷ lệ thật = hiển thị - 6%`, và rơi về 5% từ lv101) | Người chơi bị hụt chi phí LT ~6% mỗi lần bấm, và không có cảnh báo nào ở vách lv101. |
| F3 | **Sàn sát thương 15% cần thông báo**: khi đòn ra ≤ 2, hiện "không xuyên thủng phòng ngự" | Hiện người chơi chỉ thấy "gây 2 sát thương" và không hiểu vì sao 120 đòn không xong nhiệm vụ. |
| F4 | Quái có `ggj <= 3` trả 0 LT — hoặc nâng LT sàn, hoặc hiện dòng "quái quá yếu, không rơi linh thạch" | `pve.php:357-359, 424-430`; người chơi mất toàn bộ thu nhập mà không có phản hồi. |
| F5 | **Cường hóa dừng ở +31 (p = 0)** — đặt trần +25 và cho nguyên liệu/đan để mở trần | 15 ô × +30 là 6,86 M LT/ô, vượt thu nhập 1 năm của mọi hồ sơ; +31 trở lên chỉ đốt tài nguyên. |
| F6 | Thiên phú Đột Phá cần đạt được sớm hơn (hiện phụ thuộc phu vị/phạm vi) | Hồ sơ D đạt lv183/năm chủ yếu nhờ thiên phú; nếu F2P không bao giờ chạm tới thì khoảng cách nạp tiền nằm hoàn toàn ở một trục duy nhất. |
| F7 | Bản đồ: không còn quái nào trên lv100 | Cày từ lv101 trở đi không có đích; nhiệm vụ Q6 và quái cấp cao phải đi cùng nhau. |
| F8 | Vật phẩm `#100-108` chỉ mua bằng Ma Thạch ở shop | Nguồn LT dư ở cuối game không có chỗ tiêu → sink đề xuất: mở bán bằng LT cho 2–3 món. |

## 7. Gói quà nạp theo giai đoạn (tính từ lúc mở server mới)

### 7.1 Kênh giao hàng hiện có

| Kênh | Trạng thái | Dùng cho |
|---|---|---|
| `game/duihuan.php` + bảng `duihuan` (`dhm` mã, `dhzb` trang bị, `dhdj` `id\|số lượng`, `dhyp` đan dược, `dhyxb` LT, `dhczb` MT, `dhexp` exp, `dhname` tên) | **đang chạy được ngay**, chống nhận 2 lần bằng `duihuan_log` UNIQUE(sid,dhm) | toàn bộ mã quà mốc miễn phí + quy đổi gói nạp |
| `pay/index.php` (10 Ma Thạch / 1 đơn vị nạp, trần 100.000) | **CHƯA hoạt động** — cần chủ tạo `phpstudy_pro/pay_secret.key` và bảng đơn hàng trước khi production | gói trả tiền |

Nguyên tắc: **mọi gói nạp nên phát hàng qua mã `duihuan`**, để `pay` chỉ là cửa thu tiền. Như vậy thêm/bớt gói không phải sửa code nạp.

### 7.2 Chuỗi mã quà miễn phí (ngày 0 → 30) — giữ người chơi, không thu tiền

Chuỗi này nhắm thẳng vào 3 điểm nghẽn đo được ở mục 2–3 (không có trang bị, thiếu thể lực, không đột phá được).

| Mã | Mở bán / phát | Nội dung | Mua được gì (theo số đo) |
|---|---|---|---|
| `XAI-NGUYEN` | khi tạo nhân vật (ngày 0) | 1 vũ khí + 1 áo `realm_require<=1`, 2.000 LT, đan hồi thể lực ×5 | đủ công ~20 → Sơn Trư từ 120 đòn giảm còn ~30 đòn |
| `Khai-Phu-N1` | đăng nhập ngày 1 | thẻ tu luyện x2 (3 giờ), 1.000 LT, 5 MT | 1 thẻ = 1 bar ở lv1–10; hết đúng lúc NV42 vừa xong |
| `Nhip-Nhan-N3` | hoàn thành NV44 | 3 đan hồi thể lực, 5.000 LT, đá Cường Hóa ×50 | 1 ngày thể lực (+15 đòn); đủ +5 cho 1 ô |
| `Tan-Thu-N7` | ngày 7, lv≥8 | 20.000 LT, thẻ x2 ×3, 20 MT, đan Đột Phá ×5 | lv8 cần 7 K LT/cấp; gói này = ~2,5 cấp + 3 giờ tu luyện không xóa exp |
| `Bang-Nhieu-N14` | ngày 14, lv≥15 | 60.000 LT, thẻ x3 ×2, đá Cường Hóa ×300 | hết trần "30 phút không đủ bar" (từ lv16) → thẻ x3 là mặt hàng cần nhất từ đây |
| `Thang-Thu-N30` | ngày 30, lv≥31 | 150.000 LT, thẻ x3 ×5, 100 MT, 1 suất thiên phú Đột Phá | lv31: LT/cấp 1,9 K → ~78 cấp-phi-lệ-phí; thiên phú là trục whale đã chứng minh (D: lv183/năm) |

### 7.3 Gói trả tiền — chia theo 4 giai đoạn vòng đời người chơi

Giá ghi theo **đơn vị nạp** (×10 = Ma Thạch). Tỷ lệ "cấp mua được" lấy từ đường LT tích lũy mục 5.

**Giai đoạn 1 — Ngày 1–7 (tân thủ, mục tiêu: vượt tường lv1, không phải doanh thu)**

| Gói | Giá | Nội dung | Trục bán |
|---|---|---|---|
| Hành Lý Tân Thủ | 10 đv (100 MT) | 1 bộ trang bị 6 ô đúng cấp, đan thể lực ×20, thẻ x2 ×2, 10.000 LT | "đừng để tôi phải đánh 120 đòn" — bán **thể lực + sát thương**, không bán LT |
| Nguyệt Khế Đăng Nhập | 30 đv (300 MT) | 7 ngày: mỗi ngày 2 đan thể lực + 1 thẻ x2 + auto hoàn nhiệm vụ ngày | biến 4 lượt/ngày (hồ sơ A) thành 16 lượt (hồ sơ B) |

**Giai đoạn 2 — Ngày 8–30 (đang lên, mục tiêu: rút ngắn tuần cày)**

| Gói | Giá | Nội dung | Trục bán |
|---|---|---|---|
| Bao Quai Tam Linh | 50 đv | 30 ngày × 1.500 exp/giờ tu luyện không giới hạn 30 phút + 50.000 LT | trực tiếp bán hết 191 M exp đang bị xóa (hồ sơ A) — gói có ROI cao nhất game |
| Luyện Khí Hộ Thể | 80 đv | 1.000 đá Cường Hóa + 200.000 LT + 5 đan phá cấp | 1 ô +20 = 207 K LT/31 K đá → gói = ~1 ô +20, hoặc 2 ô +10 |
| Phủ Vị Lễ (thiên phú) | 120 đv | mở 1 công Thiên Phú (exp x2 / rơi đồ / đan / khí / thể lực / đột phá) + 200 MT | hệ Thiên Phú 18 phu/10 phạm/7 công đã xong local — đây là gói định vị whale |

**Giai đoạn 3 — Ngày 31–90 (vách lv50 → lv101)**

| Gói | Giá | Nội dung | Trục bán |
|---|---|---|---|
| Đột Phá Bảo Giám | 200 đv | 10 đan Đột Phá + thiên phú Đột Phá +3 + 500.000 LT | từ lv101 tỷ lệ rơi về 5% → gói bán **xác suất**, đúng chỗ đau |
| Ngũ Hành Toàn Bị | 250 đv | 5 kiện Ngũ Hành + 1 mạch huyệt cấp cao + 300 MT | đã nạp 49/49 bước Ngũ Hành, còn 2 lỗ hổng (mục 8) |
| Khố Nguyên Liệu | 150 đv/tháng | mỗi ngày 50 đan + 500 LT + vật phẩm #100–108 (đang chỉ bán bằng MT) | sink Ma Thạch cuối game (F8) |

**Giai đoạn 4 — Năm 1 trở đi (đỉnh, chỉ dành cho whale)**

| Gói | Giá | Nội dung | Trục bán |
|---|---|---|---|
| Tiền Đồ Đại Đạo | 1.000 đv | 5.000.000 LT + thiên phú Đột Phá +30 + 100 thẻ x3 | lv100→120 cần ~4,86 M LT → gói = đúng 1 chặng cõi |
| Vĩnh Viễn (one-time) | 2.000 đv | toàn bộ gói trên + auto-nhiệm vụ + không xóa exp tràn bar (mở khoá F1) | hồ sơ D chứng minh: cũng 1 năm, lv183 so với lv103 |

**Không nên bán** (theo số đo): LT thô cho người lv<30 (660 LT là xong 10 cấp — ai mua sẽ hỏng kinh tế); số lần bấm Đột Phá (chi phí đã là 4 M LT/năm, bán thêm chỉ phản hồi tiêu cực); trang bị cấp cao hơn cõi hiện tại (hệ thống `realm_require` tự khóa).

### 7.4 Lịch khuyến mãi theo server mới

| Mốc server | Ưu đãi | Căn cứ |
|---|---|---|
| Ngày 0–3 (khai mở) | mã `XAI-NGUYEN` + `Khai-Phu-N1`; giảm 50% Nguyệt Khế | 100% người chơi mới đang rơi ở NV42 |
| Ngày 4–7 | xếp hạng lv tuần + quà `Tan-Thu-N7` cho top | nhiệm vụ tuần 39 K exp/16,2 K LT hiện quá nhỏ so với thành tựu |
| Ngày 8–30 | gói Phủ Vị Lễ (thiên phú) mở bán lần đầu | trục thiên phú = khác biệt whale/F2P lớn nhất |
| Ngày 31 | sự kiện "Đột Phá Đại Tỉ", bán Đột Phá Bảo Giám | đúng vách lv50→lv101 (LT/cấp nhảy 7,5 K → 30 K → 10,3 K với p=5%) |
| Ngày 60/90 | hoàn 20% MT đã nạp thành thẻ x3 | giữ F1 (exp tràn bar) làm phần mềm, không phải phần cứng |

## 8. Lỗi đã sửa & kiểm chứng

| File | Lỗi | Đã sửa | Kiểm chứng |
|---|---|---|---|
| `game/tupo.php:156-195` | Bấm đột phá quay **hai lần** (`mt_rand` độc lập cho điều kiện và phần thưởng) → tỷ lệ thật = hiển thị − 6%, và "kỳ tích" nhân đôi sai | bỏ quay trùng, một lần quay duy nhất quyết định kết quả | `php -l` sạch; `bt_sample.php` chạy thật sau sửa: lv1 (hiển thị 70%) ra **74%/100 lần**, lv101 (hiển thị 5%) ra **6,7%/30 lần** → khớp tỷ lệ hiển thị trong sai số mẫu |
| `class/player.php` | `getplayer()` sinh `umaxexp`/`tuposjs` không bị chặn dải; exp thú nuôi không chặn; `changeyxb`/`changeczb` nhận số âm | kẹp dải (band clamp) + chặn âm ở hai hàm đổi tiền tệ | `php -l` sạch; chạy lại toàn bộ harness `play_day1b.php`/`probe_*` không ra giá trị âm hay bar > 100% |
| `game/pve.php:421-435` | Comment mô tả sai bản chất `$hurt` ( ghi là "người chơi không gây sát thương", thật ra là **sát thương của quái bị làm tròn về 0** → LT = 0) | viết lại comment + tách dòng thông báo thưởng | đọc lại `pve.php:357-359` xác nhận điều kiện là `$phurt <= 0`; chưa đổi hành vi (xem 8.2) |
| `game/boss.php:426-431` | Gộp chung một dòng thưởng khiến không phân biệt được LT/MT | tách dòng | `php -l` sạch |
| `pay/index.php` | Cửa gọi trả tiền mở, không chặn chữ ký, sổ đơn ghi trùng được | viết lại theo hướng **fail-closed**: thiếu khoá/chữ ký là từ chối, chống trùng đơn | `php -l` sạch; **CHƯA chạy được đầu cuối** vì `phpstudy_pro/pay_secret.key` chưa tồn tại (mục 9) |

Cột kiểm chứng "mô phỏng" ở mục 4 (A 97→103, B 97→101, C 104→112, D 149→183) là `sim4.php` chạy lại với tham số tỷ lệ đã sửa — **là mô hình, chưa phải chuỗi đột phá thật**.

### 8.1 Đã sửa nhưng chưa đủ bằng chứng để báo "xong"

- Tác động của bản sửa `tupo.php` lên cấp cao (lv101+): mới lấy mẫu 30 lần, sai số ±4%. Cần ≥500 lần nếu muốn kết luận cho kinh tế dài hạn.
- `class/player.php` band clamp: chưa có test đơn vị; mới dựa trên harness không còn in ra giá trị vô lý.

### 8.2 Cần chủ server chốt (không tự sửa vì đổi hành vi chơi)

| # | Hiện trạng | Lựa chọn |
|---|---|---|
| C1 | Quái `ggj <= 3` luôn trả **0 LT** (`pve.php:357-359, 424-430`) | hoặc bỏ luật, hoặc nâng sàn LT, hoặc chỉ thêm dòng thông báo |
| C2 | Boss: `DELETE ... WHERE sid=''` không bao giờ xóa → **farm vô hạn** | cần quyết định cơ chế claim boss đúng |
| C3 | Vách lv101: tỷ lệ 5% và LT/cấp nhảy gấp 3,4 lần; tổng 4300 cấp cần 1,43 B LT = 337 năm (hồ sơ A) | thiết kế lại đường cong, hoặc mặc định "trần server" ở lv~120 |
| C4 | `game.php` `parse_str($Dcmd)` → người chơi ghi đè được biến nội dung (mass assignment) | chuyển sang whitelist tham số — sửa rộng, ảnh hưởng mọi route |
| C5 | `tuposjs`/`tupoexp` = 0 trên **cả 180 trang bị** → `xiulian.php:113` là code chết | hoặc nạp số liệu thật, hoặc bỏ tính năng |
| C6 | Chuỗi `renwu_config` cũ + bảng `zhurenwu` mồ côi (nhiệm vụ thật nằm trong `renwu_config.json` + `playerrenwu`) | dọn dữ liệu hay giữ tương thích |
| C7 | Cường hóa **p = 0 từ +31** nhưng vẫn cho bấm đến +40 | đặt trần +30 hoặc mở trần có điều kiện |
| C8 | Không còn quái nào trên lv100; chế tạo khóa ở lv300/350; vật phẩm #100–108 chỉ bán bằng MT | cần bản đồ/nội dung cấp cao trước khi bán gói giai đoạn 4 |
| C9 | Production **chưa chạy** các migration còn nợ: sid schema (15 bảng), Ngũ Hành 2 lỗ hổng (boss thiếu cột `element`, ô 16 trống config), Mạch Huyệt, Thiên Phú, Nhục Thân | theo runbook, chờ chủ duyệt từng bước |

## 9. Phần CHƯA kiểm chứng

- `pay/index.php`: chưa từng được gọi đầu cuối thành công (thiếu `pay_secret.key`), production chưa có bảng đơn hàng. **Mọi gói nạp ở mục 7 hiện chỉ phát được qua mã `duihuan`.**
- Bảng dự phóng 4 hồ sơ (mục 4): là mô hình `sim4.php`, không phải chuỗi chơi thật nhiều ngày. Ngày đầu **đã được đo thật hai lần**: bản `out_day1b.txt` (lv1) **bị loại** vì harness bỏ qua bước tu luyện; bản chuẩn là `probe_tuluyen.php` → lv5/1.906 LT (mục 2).
- Harness `play_day1c.php` (168h) **không dùng được cho báo cáo**: nó chờ 0,5 giờ cho mỗi đòn (vì để thể lực cạn rồi mới chờ) nên chỉ ra 421 đòn/1 kill — thấp hơn xa trần 77 đòn/ngày. Cần viết lại lịch gom thể lực theo phiên nếu muốn đo chuỗi nhiệm vụ 7 ngày thật.
- Thiên phú Đột Phá +8/+30/+50 trong bảng chi phí: lấy từ config đã nạp local, **chưa kiểm chứng trên production** (migration Thiên Phú chưa chạy).
- Các con số "cấp mua được" của gói nạp ở mục 7: suy ra từ đường LT tích lũy mục 5, chưa test nghiệm thu từng gói.
- Chưa đo: PvP, Bang Hội, Linh Săn/đi cảnh, thị trường giao dịch — nằm ngoài phạm vi harness hiện tại.

## 10. Ba việc nên làm ngay (tóm tắt hành động)

1. **Sửa NV42 (Q1+Q2)**: người chơi tới được NV42 vào ngày 2–3 (lv5–9) nhưng kẹt ở đó 1,6–2,5 ngày chỉ vì thể lực; thêm thưởng trang bị ở NV41 để giảm đòn/kill. Song song, **giảm cơn khát LT ba ngày đầu**: đo thật cho thấy tiền đột phá lv1→12 đã ngốn 1.477 LT trong 2.000 LT khởi đầu (mục 2) — người chơi mới gần như không còn LT cho cường hóa hay đan.
2. **Cho thể lực tăng theo cấp (Q4)** — trần 77 đòn/ngày vĩnh viễn là nút thắt lớn thứ nhì, và cũng là mặt hàng nạp đầu tiên nên bán (Hành Lý Tân Thủ).
3. **Chốt C1/C2/C3** (LT quái = 0, boss farm vô hạn, vách lv101) rồi mới mở bán gói giai đoạn 3–4; nếu không, kinh tế dài hạn của server vẫn là 337 năm cho một nhân vật.




