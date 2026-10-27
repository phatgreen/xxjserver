# PROPOSAL CÂN BẰNG KINH TẾ GAME Xích Diệm Thiên

> Mục tiêu: Kéo dài quá trình tu luyện từ ~1 tháng → 6-12 tháng
> Nguyên tắc: Free player progress CHẬM hơn, không phải KHÔNG progress
> Ma Thạch = TIỆN LỢI + TỐC ĐỘ, không phải BẮT BUỘC

---

## I. TÓM TẮT VẤN ĐỀ

### Mất cân bằng hiện tại

| Vấn Đề | Hiện Tại | Mục Tiêu |
|---|---|---|
| EXP/ngày (Lv100, MT×3) | ~21.5M EXP | ~2-4M EXP |
| Linh Thạch thu/ngày | ~500K-2.2M LT | ~50K-200K LT |
| Chi phí tu luyện MT | 1 MT/phút (cố định) | Scale theo cấp |
| Chi phí tu luyện LT | 1 LT/phút (cố định) | Scale theo cấp |
| Tỷ lệ LT thu/chi | ~200:1 | ~5-10:1 |
| Thời gian max Lv100 | ~1-2 ngày | ~2-4 tuần |
| Thời gian max Lv500 | ~1 tháng | ~3-6 tháng |
| MT cần/ngày (VIP) | ~720 MT | ~500-1000 MT |

### Nguyên nhân gốc

1. **Hệ số nhân EXP quá cao:** vip(1.2) × mode(3) × absorb(1.3) × exp(1.3) × cp(1.45) = **7.35x**
2. **Chi phí tu luyện cố định:** 1 LT/phút và 1 MT/phút không scale → quá rẻ
3. **Linh Thạch thu quá nhiều:** PVE cho `level × 22.5` LT/quái → quá cao
4. **EXP requirement quá thấp:** Công thức linear, không đủ steep
5. **Không có LT sink:** Chi tiêu LT chỉ có đột phá (một lần)
6. **Free player quá dễ progress:** Tu luyện free 60 phút/ngày đủ EXP cho 2-3 level

---

## II. NGUYÊN TẮC THIẾT KẾ

### Triết lý F2P đúng

```
Free Player = NỘI DUNG GAME (đối thủ, bạn bè, người giao dịch)
Free Player = TIẾP CẬN DỄ, TIẾN BỘ CHẬM
Payer = TIẾN BỘ NHANH, TIỆN LỢI HƠN, KHÔNG BẮT BUỘC
```

### Quy tắc vàng

1. **Mọi hệ thống đều có đường free** → nhưng chậm hơn 3-5 lần
2. **Ma Thạch mua TỐC ĐỘ, không mua SỨC MẠNH độc quyền**
3. **Linh Thạch là đơn vị chính** → phải khan hiếm, có bồn chứa (sink)
4. **Mỗi realm mất tối thiểu 1-2 tuần** để đạt (free player) — **⚠ SAI:** Thực tế mất 2.2 năm đến Lv100, không phải 1-2 tuần
5. **Không có wall cứng** → mọi thứ đều progress được,只是 thời gian — **⚠ SAI:** Thể Chất Q9→Q10 = 11,767 năm F2P = wall cứng thực sự

---

## III. THAY ĐỔI CỤ THỂ

### 1. GIẢM EXP RATE + TĂNG EXP YÊU CẦU

#### 1a. Giảm hệ số nhân EXP tổng

**Hiện tại:**
```
exp = ulv × 1.2 × 3 × 1.30 × 1.30 × 1.45 = ulv × 7.35
```

**Đề xuất:**
```
// Giữ base rate = ulv
// Giảm bonuses:
vip = 1.10 (thay vì 1.20)
mode_3 = 2.5 (thay vì 3.0, và đổi tên thành "Ma Thạch ×2.5")
absorb_cap = 0.50 (tổng absorb tối đa = 50%, thay vì vô hạn)
exp_bonus_cap = 0.40 (tổng exp bonus tối đa = 40%)
cp_absorb_max = 0.30 (công pháp absorb tối đa = 30%)

// New max: ulv × 1.10 × 2.5 × 1.50 × 1.40 × 1.30 = ulv × 7.50
// Nhưng thực tế hấp thu bị cap → hiệu quả thực ~ulv × 4-5
```

#### 1b. Tăng hàm số EXP requirement

**Hiện tại:**
```
umaxexp = nextlv × (nextlv + round(nextlv/2)) × 10 × rangesexp[realm] + nextlv
```

**Đề xuất:** Thay `rangesexp` bằng dãy tăng mạnh hơn

```php
// rangesexp mới (gây bottleneck ở mỗi realm)
$rangesexp = array(
    2.5,    // 0: Nhập Môn (giữ nguyên)
    4.0,    // 1: Luyện Thể
    6.0,    // 2: Thối Thể
    8.5,    // 3: Tụ Thân
    12,     // 4: Tụ Khí
    16,     // 5: Ngự Khí
    22,     // 6: Luyện Khí
    30,     // 7: Trúc Cơ
    40,     // 8: Kim Đan
    55,     // 9: Ngưng Anh
    75,     // 10: Nguyên Anh
    100,    // 11: Tụ Thần
    135,    // 12: Hóa Thần
    180,    // 13: Hợp Thể
    240,    // 14: Hợp Đạo
    320,    // 15: Luyện Hư
    420,    // 16: Đại Thừa
    550,    // 17: Độ Kiếp
    720,    // 18: Tôn Giả
    940,    // 19: Thánh Giả
    1200,   // 20-85: tăng dần...
);
// So sánh: cũ 2.5→55 (22x), mới 2.5→550 (220x)
```

#### 1c. Thêm cơ chế "Linh Khí Giới Hạn"

Mỗi realm có giới hạn EXP nhận từ tu luyện (cultivation cap):

```
cultivation_exp_cap = umaxexp × realm_cultivation_ratio
```

| Realm | Cultivation Ratio | Nghĩa Là |
|---|---|---|
| 0-5 (Phàm) | 0.80 | Tu luyện cung cấp 80% EXP cần |
| 6-10 (Luyện Khí→Nguyên Anh) | 0.50 | Tu luyện chỉ cung cấp 50% |
| 11-16 (Tụ Thần→Đại Thừa) | 0.30 | Tu luyện chỉ 30%, phải hoạt động |
| 17+ (Độ Kiếp+) | 0.15 | Tu luyện rất ít, phải cày phó bản/boss |

→ Khi đạt cap, tu luyện **vẫn chạy nhưng EXP = 0**. Phải làm nhiệm vụ, phó bản, boss để nhận EXP.

---

### 2. GIẢM THU NHẬP LINH THÁCH

#### 2a. Giảm LT từ PVE

**Hiện tại:**
```
yxb = round(glv × 22.5) + mt_rand(1,200)
```

**Đề xuất:**
```
yxb = round(glv × 5) + mt_rand(1,50)
```

| Cấp quái | LT CŨ | LT MỚI | Giảm |
|---|---|---|---|
| Lv 10 | ~425 | ~100 | -76% |
| Lv 50 | ~1,325 | ~300 | -77% |
| Lv 100 | ~2,450 | ~550 | -78% |
| Lv 200 | ~4,700 | ~1,050 | -78% |

#### 2b. Giảm LT từ Boss Linh Căn

**Hiện tại:** 100-500 LT/boss

**Đề xuất:** 50-150 LT/boss

| | CŨ | MỚI |
|---|---|---|
| Min | 100 | 50 |
| Max | 500 | 150 |
| TB/ngày (12 boss) | 3,600 | 1,200 |

#### 2c. Giảm LT từ Nhiệm Vụ Chính Tuyến

Giảm ~50% LT奖励 từ mỗi nhiệm vụ:

| Nhiệm Vụ | LT CŨ | LT MỚI |
|---|---|---|
| Sơn trư họa loạn | 120 | 60 |
| Thu tập phong mật | 100 | 50 |
| Man! | 500 | 250 |
| Đồ tận yêu vương | 2,000 | 1,000 |

#### 2d. Tổng thu LT/ngày sau khi sửa

**Free Player Lv50:**

| Nguồn | CŨ | MỚI |
|---|---|---|
| PVE (50 quái) | ~66,250 | ~15,000 |
| Boss (6) | ~1,800 | ~600 |
| Nhiệm vụ | ~500 | ~250 |
| **TỔNG** | **~68,550** | **~15,850** |

**VIP Player Lv100:**

| Nguồn | CŨ | MỚI |
|---|---|---|
| PVE (200 quái) | ~490,000 | ~110,000 |
| Boss (12) | ~3,600 | ~1,200 |
| Nhiệm vụ | ~1,000 | ~500 |
| Đấu trường #1 | 10,000 | 10,000 |
| **TỔNG** | **~504,600** | **~121,700** |

---

### 3. SCALE CHI PHÍ TU LUYỆN THEO CẤP

#### 3a. Chi phí Linh Thạch x2

**Hiện tại:** 1 LT/phút (cố định)

**Đề xuất:**
```
cost_per_min = max(1, ceil(pow(ulv, 1.15) / 5))
```

| Level | LT/phút (MỚI) | 6 giờ (360 phút) |
|---|---|---|
| 10 | 3 | 1,080 |
| 25 | 10 | 3,600 |
| 50 | 25 | 9,000 |
| 100 | 63 | 22,680 |
| 200 | 150 | 54,000 |
| 300 | 260 | 93,600 |

#### 3b. Chi phí Ma Thạch x3 (hoặc x2.5)

**Hiện tại:** 1 MT/phút (cố định)

**Đề xuất:**
```
cost_per_min = max(1, ceil(ulv / 3))
```

| Level | MT/phút (MỚI) | 12 giờ (720 phút) | MT/ngày |
|---|---|---|---|
| 10 | 4 | 2,880 | 2,880 |
| 25 | 9 | 6,480 | 6,480 |
| 50 | 17 | 12,240 | 12,240 |
| 100 | 34 | 24,480 | 24,480 |
| 200 | 67 | 48,240 | 48,240 |

#### 3c. Giảm multiplier từ Ma Thạch

**Hiện tại:** Ma Thạch = x3 EXP

**Đề xuất:** Ma Thạch = x2 EXP (giảm từ 3 xuống 2)

Lý do:
- EXP rate đã giảm (xem mục 1)
- Chi phí tăng → cần giữ multiplier hợp lý
- Vẫn đáng giá vì free player chỉ có x1 trong 60 phút

---

### 4. GIỚI HẠN THỜI GIAN TU LUYỆN

#### 4a. Giảm max time theo mode

| Mode | Max Time Hiện Tại | Max Time Mới |
|---|---|---|
| Thường (Free) | 60 phút | 30 phút |
| Linh Thạch x2 | 360 phút (6h) | 180 phút (3h) |
| Ma Thạch x2 | 720 phút (12h) | 360 phút (6h) |

#### 4b. Thêm cơ chế "Kiến Túc"

Thay vì AFK 12h, chia thành nhiều session:
- Mỗi session tu luyện: tối đa 30-60 phút
- Giữa các session: phải hoạt động (đánh quái, làm nhiệm vụ) 15 phút
-→ Tạo thói quen chơi game thay vì AFK

---

### 5. TẠO LINH THÁCH SINK (BỒN CHỨA)

#### 5a. Cường Hóa Trang Bị - Scale theo cấp

**Hiện tại:** `cost = qianghua × 3 + 1` items

**Đề xuất:**
```
cost_linhthach = base_cost × pow(1.5, qianghua) × (1 + ulv/100)
```

| Cường Hóa | Level 50 | Level 100 | Level 200 |
|---|---|---|---|
| +1 | 10 | 15 | 30 |
| +5 | 75 | 120 | 240 |
| +10 | 1,500 | 2,500 | 5,000 |
| +15 | 15,000 | 25,000 | 50,000 |
| +20 | 100,000 | 170,000 | 340,000 |

#### 5b. Nâng Linh Căn - Tăng chi phí

**Hiện tại:** Phàm→Huyền = 500 LT

**Đề xuất:**

| Cấp | LT MỚI | Đá MỚI | UEXP MỚI | Thành Công |
|---|---|---|---|---|
| Phàm→Huyền | 1,000 | 20 | 2,000 | 100% |
| Huyền→Địa | 5,000 | 60 | 10,000 | 80% |
| Địa→Thiên | 20,000 | 150 | 40,000 | 60% |
| Thiên→Thánh | 80,000 | 500 | 150,000 | 40% |
| Thánh→Niết | 300,000 | 1,500 | 500,000 | 20% |

#### 5c. Thêm "Thiên Địa Bảo Tháp" - LT Sink hàng ngày

Hệ thống phó bản hàng ngày tốn LT để vào:

| Phó Bản | Chi Phí LT | Phần Thưởng | Giới Hành/Ngày |
|---|---|---|---|
| Tầm Bảo Động | 5,000 LT | EXP + Đá | 3 lần |
| Ma Vương Điện | 10,000 LT | EXP + Trang Bị | 2 lần |
| Tiên Cảnh | 20,000 LT | EXP + Công Pháp EXP | 1 lần |

**Tổng LT tiêu/ngày từ phó bản:** 35,000-55,000 LT

#### 5d. Hối Đoái Vật Phẩm - LT Sink vĩnh viễn

Thêm NPC hối đoái cho phép đổi LT lấy vật phẩm:

| Vật Phẩm | Giá LT | Tần Suất |
|---|---|---|
| Đá Ngũ Hành | 2,000 | 10/ngày |
| Thuốc Hồi HP | 100 | Vô hạn |
| Thuốc Tạm Buff | 500 | 5/ngày |
| Bùa May Mắn | 5,000 | 1/ngày |

---

### 6. MA THẠCH SINK (KHÔNG BẮT BUỘC)

#### 6a. Ma Thạch Shop - Vật phẩm tiện lợi

| Vật Phẩm | Giá MT | Mô Tả | Bắt Buộc? |
|---|---|---|---|
| EXP Booster 50% (2h) | 100 | Tăng EXP 50% trong 2h | KHÔNG |
| LT Booster 50% (2h) | 50 | Tăng LT 50% trong 2h | KHÔNG |
| Tái Sinh tức thì | 200 | Hồi phục ngay lập tức | KHÔNG (có free path) |
| Dịch Chuyển nhanh | 30 | Teleport miễn phí 1h | KHÔNG |
| Slots Kho +10 | 500 | Mở rộng kho đồ | KHÔNG |
| Refresh Nhiệm Vụ | 50 | Reset 1 nhiệm vụ | KHÔNG |
| Draw Công Pháp | 200 | Quay công pháp | KHÔNG (có farm) |
| Draw Sủng Vật | 300 | Quay sủng vật | KHÔNG (có farm) |

#### 6b. Season Pass (Bản Season)

- **Free:** Phần thưởng cơ bản mỗi season (30 ngày)
- **Premium (500 MT):** Phần thưởng premium + thêm 50% EXP trong season
- Không mua vẫn progress được,只是 chậm hơn

#### 6c. Ma Thạch từ Game (Free Player nhận được)

Free player CÓ THỂ nhận MT từ:

| Nguồn | Số Lượng | Tần Suất |
|---|---|---|
| Nhiệm Vụ Tuần | 10 MT | 1/tuần |
| Đạt mốc cảnh giới | 50 MT | Mỗi đại cảnh giới |
| Đấu Hạng #10 | 16 MT | Hàng ngày |
| Achievement | 10-100 MT | Mỗi achievement |
| Boss Rare Drop | 5-20 MT | Rất hiếm (1%) |

**Tổng MT free/ngày:** ~5-15 MT (đủ để mua 1 EXP booster nhỏ)

---

### 7. TĂNG TỶ LỆ ĐỘT PHÁ

#### 7a. Công thức mới

**Hiện tại:**
```
rate = (100 - ulv × (jingjieValue + 1)) + 100 + equipment_bonus
```

**Đề xuất:**
```
rate = max(5, min(95, 100 - ulv × 2 + equipment_bonus + blessing))
```

| Level | Realm | CŨ | MỚI |
|---|---|---|---|
| 10 | 1 | 180%→100% | 85% |
| 25 | 1 | 150%→100% | 55% |
| 50 | 2 | 60% | 10% + equipment |
| 100 | 3 | -200%→0% | 5% + equipment |

**Thêm "Pháp Tắc Hộ Thuẫn"** - Bonus mỗi 50 level:
- Level % 50 == 0: +10% tỷ lệ
- Level % 50 == 25: +5% tỷ lệ

**Thêm "Bảo Thạch May Mắn"** - Item mua bằng LT:
- Tăng 5% tỷ lệ đột phá
- Tối đa 3 viên/breakthrough

---

### 8. GIỚI HẠN Absorb BONUS

#### 8a. Cap absorb speed

**Hiện tại:** absorb không có cap, có thể stack vô hạn

**Đề xuất:**
```
total_absorb = min(50, lc_absorb + cp_absorb)
// Absorb tổng tối đa = 50%
```

| Nguồn | Absorb Max |
|---|---|
| Linh Căn | 30% (Vô Cực) |
| Công Pháp | 30% (Thần Hành Bách Biến max) |
| **Tổng** | **50% (cap)** |

#### 8b. Cap exp bonus

```
total_exp_bonus = min(40, lc_exp_bonus + cp_exp_bonus)
// EXP bonus tổng tối đa = 40%
```

---

## IV. BẢNG TỔNG HỢP THAY ĐỔI

| Hệ Thống | Hiện Tại | Đề Xuất | Tác Động |
|---|---|---|---|
| EXP rate max | ulv × 7.35 | ulv × 4-5 (capped) | -35% EXP/ngày |
| rangesexp max | 55 | 550+ | EXP requirement x10 |
| PVE LT | glv × 22.5 | glv × 5 | -78% LT thu |
| Boss LT | 100-500 | 50-150 | -70% LT thu |
| Tu luyện LT cost | 1 LT/phút | Scale theo cấp | LT tiêu x5-10 |
| Tu luyện MT cost | 1 MT/phút | Scale theo cấp | MT tiêu x10-30 |
| Max time free | 60 phút | 30 phút | Giảm 50% |
| Max time MT | 12 giờ | 6 giờ | Giảm 50% |
| Absorb cap | Vô hạn | 50% | Giảm progression |
| Exp bonus cap | Vô hạn | 40% | Giảm progression |
| Linh căn upgrade | 500-80K LT | 1K-300K LT | LT sink x2-4 |
| Cường hóa | Rẻ | Scale theo cấp | LT sink mới |

---

## V. DỰ BÁO THỜI GIAN PROGRESSION

### Free Player (không nạp tiền)

| Mốc | Hiện Tại | Đề Xuất |
|---|---|---|
| Lv 0→50 (Nhập Môn→Ngự Khí) | ~2 giờ | ~3-5 ngày |
| Lv 50→100 (Luyện Khí→Nguyên Anh) | ~1 ngày | ~1-2 tuần |
| Lv 100→200 (Tụ Thần) | ~3 ngày | ~1-2 tháng |
| Lv 200→500 (Nguyên Anh+) | ~1 tuần | ~3-6 tháng |
| Lv 500→1000 (Thánh Tôn) | ~2 tuần | ~6-12 tháng |
| Lv 1000+ | ~1 tháng | >12 tháng |

### VIP Player (nạp tiền, MT×2 hàng ngày)

| Mốc | Hiện Tại | Đề Xuất |
|---|---|---|
| Lv 0→50 | ~30 phút | ~1-2 ngày |
| Lv 50→100 | ~6 giờ | ~3-5 ngày |
| Lv 100→200 | ~1 ngày | ~2-3 tuần |
| Lv 200→500 | ~3 ngày | ~1.5-3 tháng |
| Lv 500→1000 | ~1 tuần | ~3-6 tháng |
| Lv 1000+ | ~2 tuần | ~6-12 tháng |

### Tỷ lệ Free:Paid progression

| | Hiện Tại | Đề Xuất |
|---|---|---|
| Tốc độ Free/Paid | ~1:3 | ~1:2-2.5 |
| Gap tuyệt đối | Nhỏ (vì cả hai đều nhanh) | Lớn (vì cả hai đều chậm) |
| Free player feel | Chán (max nhanh quá) | Hài lòng (progress đều đặn) |

---

## VI. TÁC ĐỘNG ĐẾN KINH DOANH

### Doanh thu dự kiến

| Chỉ Số | Hiện Tại | Đề Xuất |
|---|---|---|
| MT cần/ngày (VIP) | ~720 | ~2,000-5,000 |
| Chi phí nạp/tháng | ~$5-10 | ~$15-50 |
| Tỷ lệ nạp | Thấp (không cần) | Cao hơn (cần MT) |
| Thời gian chơi/ngày | AFK 12h | Chơi activ 1-2h |
| Retention (giữ chân) | Thấp (max nhanh) | Cao (progress dài) |

### Free Player Contribution

| Yếu Tố | Tác Động |
|---|---|
| Số lượng online | Tăng (vẫn progress được) |
| Giao dịch | Tăng (cần trao đổi vật phẩm) |
| PVP/Arena | Tăng (còn lâu mới max) |
| Guild activity | Tăng (cùng nhau cày) |
| Content creation | Tăng (chia sẻ build, strategy) |

---

## VII. IMPLEMENTATION PRIORITY

### Phase 1 (Ưu tiên cao - Thực hiện ngay)

1. **Scale tu luyện cost theo cấp** (xiulian.php)
   - LT: `max(1, ceil(pow(ulv, 1.15) / 5))`
   - MT: `max(1, ceil(ulv / 3))`
   - Giảm MT multiplier từ 3→2

2. **Giảm PVE LT reward** (pve.php)
   - `yxb = round(glv × 5) + mt_rand(1,50)`

3. **Giảm max time tu luyện**
   - Free: 60→30 phút
   - LT: 360→180 phút
   - MT: 720→360 phút

4. **Cap absorb/exp_bonus**
   - Absorb: min(50, total)
   - Exp bonus: min(40, total)

### Phase 2 (Ưu tiên trung bình - Tuần 2-3)

5. **Tăng rangesexp** (rangesjj.php)
6. **Tăng linh căn upgrade cost** (linhcan_upgrade_config.json)
7. **Thêm LT sink** (Phó bản hàng ngày, Hối đoái)
8. **Sửa breakthrough formula** (tupo.php)

### Phase 3 (Ưu tiên thấp - Tháng 2)

9. **Ma Thạch Shop** (thêm shop mới)
10. **Season Pass**
11. **Achievement System cho MT**
12. **Cultivation cap theo realm**

---

## VIII. RISK & MITIGATION

| Risk | Impact | Mitigation |
|---|---|---|
| Free player bỏ game vì progress chậm | Cao | Đảm bảo luôn có mục tiêu nhỏ để đạt |
| VIP player bỏ game vì tốn quá nhiều MT | Trung bình | Đảm bảo MT mua được time worth it |
| LT inflation vẫn cao | Trung bình | Thêm LT sink định kỳ |
| Breakthrough quá khó → chán | Cao | Balance giữa challenge và frustration |
| Bot/AFK farming | Thấp | Giới hạn time + anti-bot |

---

## IX. KẾT LUẬN

### Tóm tắt thay đổi chính

1. **Giảm 35% EXP rate** (cap absorb + giảm multiplier)
2. **Giảm 78% LT thu** (PVE + boss rewards)
3. **Tăng 5-10x LT tiêu** (scale tu luyện cost + thêm sinks)
4. **Tăng 10x EXP requirement** (rangesexp mới)
5. **Ma Thạch = convenience** (không bắt buộc, nhưng đáng giá)
6. **Free player progress chậm hơn 3-5 lần** nhưng vẫn progress

### Kết quả mong đợi

- **Free player:** 6-12 tháng để reach endgame — **⚠ SAI:** Thực tế 2.2 năm đến Lv100, không phải endgame
- **VIP player:** 3-6 tháng để reach endgame — **⚠ SAI:** Thực tế 6-8 tháng đến Lv100, không phải endgame
- **Doanh thu:** Tăng 3-5x do MT sink hợp lý
- **Retention:** Tăng do progression dài hơn
- **Community:** Sôi động hơn do free player vẫn active

---

## IV. PHÂN TÍCH P2W — NGƯỜI CHƠI NẠP TIỀN (VIỆT NAM)

### Tỷ Giá

- **1 USD = 26,000 VND**
- **1 VND = 1 Ma Thạch (MT)** khi nạp
- Ví dụ: 1,000,000 VND = 1,000,000 MT

### Tổng Chi Phí MT (Một Lần)

| Hệ Thống | MT Cần | VND | USD |
|-----------|-------:|----:|----:|
| Công Pháp (28 pháp) | 211,000 | 211,000 | ~$8 |
| Võ Công (30 skill) | 78,050 | 78,050 | ~$3 |
| **Tổng** | **289,050** | **~289K VND** | **~$11** |

**💡 Insight:** Chi phí MT một lần **rẻ bất ngờ** (~$11). Wall thực sự là **LT (Linh Thồng)**, không phải MT.

### So Sánh F2P vs P2W

| Chỉ Số | F2P ($0) | Casual (1M VND) | Trung Bình (5M VND) | Heavy (20M VND) | Whale (100M VND) |
|--------|----------|-----------------|---------------------|-----------------|------------------|
| **Lv 100** | 2.2 năm | 6-8 tháng | 3-4 tháng | 2-3 tháng | 1-2 tháng |
| **Lv 200** | 7.8 năm | 3-4 năm | 1.5-2 năm | 6-8 tháng | 3-4 tháng |
| **Lv 500** | 51.9 năm | 25+ năm | 15-20 năm | 5-7 năm | 2-3 năm |
| **Lv 800** | 273 năm | 100+ năm | 60+ năm | 20-30 năm | 8-12 năm |
| **Công Pháp** | Khóa | Một phần | **Đầy đủ** | **Đầy đủ** | **Đầy đủ** |
| **Võ Công** | Khóa | Một phần | **Đầy đủ** | **Đầy đủ** | **Đầy đủ** |
| **Linh Căn T5** | Không | Không | Gacha-only | Gacha-only | Gacha-only |

### Tóm Tắt P2W

```
Nạp 1 triệu VND (1M MT):
✅ Mua được Công Pháp + Võ Công đầy đủ
✅ Tăng tốc EXP gấp 5-10x
✅ Lv100 trong 6-8 tháng
❌ Vẫn cần 3-4 năm đến Lv200
❌ Vẫn không max được Lv800 (cần 8-12 năm)
❌ Vẫn không mua được Linh Căn T5
```

### Kết Luận P2W

1. **MT wall rẻ** — Ai cũng mua được Công Pháp + Võ Công ($11)
2. **LT wall cực nặng** — Đột phá Lv800 cần **trousands tỷ LT** với 5% rate
3. **Whale cũng không max được** — Nạp 100M VND vẫn cần 8-12 năm đến Lv800
4. **Linh Căn = may mắn** — Tố Thể Đan chỉ drop từ boss, không mua được
5. **MT thực sự dùng để加速** — Tu Luyện Mode 3, Đào Mỏ, Hàm Mộ (có giới hạn ngày)

---

*File proposal này là design document, không phải code.*
*Yêu cầu: D:\XXJserver\ECONOMY_BALANCE_PROPOSAL.md*
