# PHÂN TÍCH TOÀN BỘ CƠ CHẾ GAME - Tâm Tiên Ký

---

## 1. CÔNG THỨC TU LUYỆN EXP PER SECOND

**Nguồn:** `xiulian.php:57-59`

```
base_exp_per_sec = round(
    ulv × vip_bonus × exp_multiplier × (1 + cp_absorb/100) × (1 + lc_absorb/100) × (1 + lc_exp_bonus/100),
    4
)

exp_per_sec = round(base_exp_per_sec × (1 + equip_bonus_percent/100), 4)
```

### Các biến số:

| Biến | Giá trị | Nguồn |
|------|---------|-------|
| `ulv` | Level hiện tại (1-4300) | game1.ulv |
| `vip_bonus` | VIP=1.20, thường=1.00 | `xiulian.php:23` |
| `exp_multiplier` | Thường=1, Linh thạch=2, Ma thạch=3 | `xiulian.php:27-51` |
| `cp_absorb` | Tổng % absorb từ công pháp equipped | `player.php:606` → `congphap_calc_buffs()` |
| `lc_absorb` | Bonus absorb từ linh căn (đã trừ penalty) | `player.php:633` |
| `lc_exp_bonus` | Bonus exp từ linh căn | `player.php:634` |
| `equip_bonus_percent` | Tổng tupoexp từ trang bị | `xiulian.php:58` |

### Mode Multiplier & Chi phí:

| Mode | Multiplier | Chi phí/phút | Max thời gian |
|------|-----------|-------------|---------------|
| Thường (canshu=1) | ×1 | 0 (Miễn phí) | 60 phút |
| Linh thạch (canshu=2) | ×2 | 1 LT/phút | 360 phút (6h) |
| Ma thạch (canshu=3) | ×3 | 1 MT/phút | 720 phút (12h) |

**Nguồn:** `xiulian.php:32-51, 66-74`

### Bảng tính cụ thể theo Realm:

**Công thức exp cần thiết cho level N:**
```
umaxexp = playernextlv × (playernextlv + round(playernextlv/2)) × 10 × rangesexp[i] + playernextlv
```

**rangesexp (hệ số theo realm):**
| Index (i) | Level Range | rangesexp | Realm |
|-----------|------------|-----------|-------|
| 0 | 0-49 | 2.5 | Nhập Môn |
| 1 | 50-99 | 5.0 | Luyện Thể |
| 2 | 100-149 | 7.5 | Thối Thể |
| 3 | 150-199 | 10.0 | Tụ Thân |
| 4 | 200-249 | 12.5 | Tụ Khí |
| 5 | 250-299 | 15.0 | Ngự Khí |
| 6 | 300-349 | 17.5 | Luyện Khí |
| 7 | 350-399 | 20.0 | Trúc Cơ |
| 8 | 400-449 | 25.0 | Kim Đan |
| 9 | 450-499 | 30.0 | Ngưng Anh |
| 10 | 500-549 | 35.0 | Nguyên Anh |
| 11 | 550-599 | 40.0 | Tụ Thần |
| 12 | 600-649 | 45.0 | Hóa Thần |
| 13 | 650-699 | 50.0 | Hợp Thể |
| 14 | 700-749 | 55.0 | Hợp Đạo |
| ... | ... | ... | continues |

**Nguồn:** `player.php:575`

### VÍ DỤ TÍNH TOÁN:

**Level 100, VIP, Ma thạch, không có bonus khác:**
- base_exp_per_sec = 100 × 1.20 × 3 × 1.0 × 1.0 × 1.0 = **360 exp/s**
- exp_per_min = 360 × 60 = **21,600 exp/min**
- exp_per_hour = 21,600 × 60 = **1,296,000 exp/hour**
- exp_per_day (24h) = 1,296,000 × 24 = **31,104,000 exp/day**

**Level 500, VIP, Ma thạch, 50% equip bonus, 20% CP absorb, 10% LC absorb:**
- base_exp_per_sec = 500 × 1.20 × 3 × 1.20 × 1.10 × 1.10 = **2,613.6**
- exp_per_sec = 2,613.6 × 1.50 = **3,920.4**
- exp_per_hour = **14,113,440**

**Level 1000, VIP, Ma thạch, max bonuses:**
- base_exp_per_sec = 1000 × 1.20 × 3 × 1.60 × 1.30 × 1.30 = **9,738.24**
- exp_per_sec = 9,738.24 × 1.50 = **14,607.36**
- exp_per_hour = **52,586,496**

---

## 2. THỜI GIAN LÊN CẤP (TIME TO LEVEL UP)

### Exp cần cho level tiếp theo (examples):

| Level | rangesexp | Exp cần (umaxexp) |
|-------|-----------|-------------------|
| 1 | 2.5 | 2×(2+1)×10×2.5+2 = **152** |
| 50 | 5.0 | 51×(51+26)×10×5+51 = **195,756** |
| 100 | 7.5 | 101×(101+51)×10×7.5+101 = **115,006** |
| 200 | 12.5 | 201×(201+101)×10×12.5+201 = **758,452** |
| 500 | 35.0 | 501×(501+251)×10×35+501 = **13,176,236** |
| 1000 | 35.0+ | 1001×(1001+501)×10×45+1001 = **676,267,551** |
| 2000 | 55.0 | 2001×(2001+1001)×10×55+2001 = **3,303,635,255** |
| 3000 | 55.0 | 3001×(3001+1501)×10×55+3001 = **7,429,505,355** |
| 4300 | 55.0 | 4301×(4301+2151)×10×55+4301 = **15,347,047,155** |

**Thuật toán cụ thể:**
```php
// player.php:677
$playernextlv = $player->ulv + 1;
$i = floor(max(0, $player->ulv) / 50);
$player->umaxexp = $playernextlv * ($playernextlv + round($playernextlv/2)) * 10 * $rangesexp[$expRateIndex] + $playernextlv;
```

### Thời gian ước tính (Với VIP + Ma thạch ×3, không bonus khác):

| Level | Exp cần | Exp/hr (×3 VIP) | Thời gian |
|-------|---------|-----------------|-----------|
| 1→50 | ~196K | 1×1.2×3×60² = 12,960/hr | ~15 giờ |
| 50→100 | ~115K→1.2M | 50×3.6×3600 = 648,000/hr | ~10 giờ |
| 100→200 | 1.2M→758K | 100×3.6×3600 = 1,296,000/hr | ~5 giờ |
| 200→500 | ~13M | 200×3.6×3600 = 2,592,000/hr | ~5 giờ |
| 500→1000 | ~676M | 500×3.6×3600 = 6,480,000/hr | ~104 giờ (4.3 ngày) |
| 1000→2000 | ~3.3B | 1000×3.6×3600 = 12,960,000/hr | ~255 giờ (10.6 ngày) |
| 2000→3000 | ~7.4B | 2000×3.6×3600 = 25,920,000/hr | ~286 giờ (12 ngày) |
| 3000→4300 | ~7.9B | 3000×3.6×3600 = 38,880,000/hr | ~203 giờ (8.5 ngày) |

**Tổng thời gian ước tính từ Lv1 → Lv4300: ~53 ngày (chỉ tu luyện, không tính downtime)**

---

## 3. ĐỘT PHÁ (BREAKTHROUGH)

**Nguồn:** `tupo.php:114-119, 125-130`

### Chi phí Linh thạch đột phá:

```
// Cảnh giới lớn (level % 50 == 0):
$tupo_cost = ulv × ulv × ulv × 2;

// Cấp độ thường:
$tupo_cost = ulv × (ulv + 1) × 2;
```

### Tỷ lệ thành công:

```
$rate = 100 + (level % 50 == 0 ? 10 : 0) + tupo_broken_equips_bonus - ulv × (jingjieValue + 1)
```

| Thành phần | Giá trị |
|-----------|---------|
| Base | 100% |
| Đột phá cảnh giới | +10% (khi level chia hết 50) |
| Equipment tupojs bonus | +$zbsjs% (từ trang bị) |
| Level penalty | -$ulv × ($jingjieValue + 1)% |

### Kết quả đột phá:

| Rand | Kết quả |
|------|---------|
| 1 | **-1 level, exp về 0** (1% chance) |
| 2-3 | **Exp về 0** (2% chance) |
| 4 | **Exp -75%** (2% chance) |
| 5-6 | **Exp -50%** (2% chance) |
| 7-8 | **Kỳ tích** (Công +999, Phòng +999, HP +999) (2% chance) — display only, NOT written to DB |
| >rate | **Thất bại** |
| ≤rate | **Thành công** |

**Thuật toán thành công:**
```php
// tupo.php:172-180
if ($tupo == 1) { // Cảnh giới lớn
    $uphp = 4 + round($player->ulv / 1.2);
    $upgj = 2 + round($player->ulv / 2.5);
    $upfy = 3 + round($player->ulv / 2);
} else { // Cấp thường
    $uphp = 2 + round($player->uhp / 20);
    $upgj = 1 + round($player->ugj / 100);
    $upfy = 1 + round($player->ufy / 100);
}
```

### Chi phí đột phá (Ví dụ):

| Level | Loại | Linh thạch cần |
|-------|------|----------------|
| 50 | Cảnh giới | 50³×2 = **250,000** |
| 100 | Cảnh giới | 100³×2 = **2,000,000** |
| 500 | Cảnh giới | 500³×2 = **250,000,000** |
| 100 | Cấp | 100×101×2 = **20,200** |
| 500 | Cấp | 500×501×2 = **501,000** |
| 1000 | Cấp | 1000×1001×2 = **2,002,000** |

---

## 4. THU NHẬP ĐỒNG (CURRENCY EARNING)

### 4.1. Từ Đánh Quái (PVE)

**Nguồn:** `pve.php:249-261`

```
Linh thạch = round(guaiwu.glv × 22.5) + rand(1,200)
// Nếu level gap ≥ 30: Linh thạch = 0
```

### 4.2. Từ Đánh Boss

**Nguồn:** `boss.php:283-296`

```
Linh thạch = round(boss.bosslv × rand(1,5) × 30) + rand(1,5) × 110 + rand(1,2000)
// Nếu level gap ≥ 35: Linh thạch = 0

Ma thạch = rand(1,5) + round(boss.bosslv / 10)
```

**Boss growth (sau mỗi lần bị giết):**
```
bossmaxhp += 100
bossgj += 5
```

### 4.3. Từ Nhiệm vụ (Quests)

**Nguồn:** `renwu_config.json` (quest rewards từ config)

Quests rewards là cố định theo config, includes:
- `rwexp`: Exp points
- `rwyxb`: Linh thạch
- Items (`rwdj`): daoju
- Medicines (`rwyp`): yaopin

### 4.4. Đào mỏ (Mining)

Chưa có mining.php cụ thể, nhưng có effect_type:
- `mining_bonus` từ Công Pháp: `Khoáng Mạch Thần Quyết` (effect: +5-50% linh thạch khi đào mỏ)

### 4.5. Tổng thu nhập ước tính/ngày:

| Nguồn | Ước tính/ngày |
|-------|---------------|
| Tu luyện (chỉ exp, không có currency) | 0 |
| Đánh quái (giả sử 100 quái lv500/ngày) | ~100 × (11,250 + 100) ≈ **1,135,000 LT** |
| Đánh boss (giả sử 5 boss lv500/ngày) | ~5 × (75,000 + 300) ≈ **376,500 LT + 35 MT** |
| Nhiệm vụ daily | Config-dependent |
| Luyện đan (bán thành phẩm) | Config-dependent |

---

## 5. CHIẾN ĐẤU (COMBAT FORMULAS)

### 5.1. PVE - Đánh Quái

**Nguồn:** `pve.php:149-189`

```
// Sát thương quái vật gây cho nhân vật:
phurt = round(guaiwu.ggj - (player.ufy × 0.75), 0)
if (phurt < guaiwu.ggj × 0.15):
    phurt = round(guaiwu.ggj × 0.15)

// Bạo kích (crit):
// Random 1-200, nếu crit >= random:
player.ugj = round(player.ugj × 1.72)

// Sát thương nhân vật gây cho quái:
gphurt = round(cwgj + player.ugj - (guaiwu.gfy × 0.75), 0)
if (gphurt < player.ugj × 0.15):
    gphurt = round(player.ugj × 0.15)

// Hút máu:
pvexx = ceil(gphurt × (player.uxx / 200))

// Né tránh:
// Random 1-100, nếu > 80: gphurt = 0, pvexx = 0

// Sát thương lên sủng vật:
cwxl = round(phurt × 2.2)
```

### 5.2. PVP - Đánh Người

**Nguồn:** `pvp.php:91-103`

```
// Bạo kích:
ran = rand(1,100)
if (player.ubj >= ran):
    player.ugj = round(player.ugj × 1.82)  // ×1.82 thay vì ×1.72

// Sát thương:
hurtNum = round(player.ugj - pvper.ufy × 0.75, 0)
if (hurtNum < player.ugj × 0.05):  // Floor thấp hơn PVE
    hurtNum = round(player.ugj × 0.05)

// Hút máu PVP ( khác PVE!):
pvpxx = round(hurtNum × (player.uxx / 100))  // /100 thay vì /200
```

### 5.3. Exp từ đánh quái

**Nguồn:** `pve.php:359-362`

```
gexp = round(guaiwu.gexp / (level_gap + 1), 0)
if (gexp < 3): gexp = 3
```

### 5.4. Exp từ đánh Boss

**Nguồn:** `boss.php:391-394`

```
bossexp = round(boss.bossgj / 2 + player.ugj / 2 + player.ufy / 2, 0)
if (bossexp < 3): bossexp = 3
```

### 5.5. Element Multiplier (Ngũ Hành)

**Nguồn:** `linhcan.php:282-297`

| Tương quan | Multiplier |
|-----------|-----------|
| Cùng element | ×1.0 |
| Linh căn khắc mục tiêu | ×1.2 (+20% damage) |
| Mục tiêu khắc linh căn | ×0.85 (-15% damage) |
| Không tương khắc | ×1.0 |

**Thứ tự khắc:** Kim→Mộc→Thổ→Thủy→Hỏa→Kim

---

## 6. THUỘC TÍNH NHÂN VẬT (STAT CALCULATIONS)

### 6.1. Thiên phú points

**Nguồn:** `tianfu.php:73-78, player.php:513-517`

```
// Level up nhận tf = ulv × 5 (khi reset)
// Phân phối: 1 tf = +1 stat point vào hp/gj/fy/bj/xx/xy/sb

// Conversion rate (player.php:513-517):
tfgj → ugj += tfgj × 5
tfbj → ubj += tfbj × 1.5
tfxx → uxx += tfxx × 2
tfhp → umaxhp += tfhp × 10
tffy → ufy += tffy × 5
```

### 6.2. Level Up Stats

**Nguồn:** `player.php:992-1017`

```php
$rangesgj = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
$rangesfy = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
$rangeshp = array(30, 50, 70, 90, 110, 130, 170, 210, 250, 300, 350, 400, 450, 500, 550);
$rangeswx = array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34);
```

### 6.3. Cường hóa trang bị

**Nguồn:** `player.php:1248-1267`

```
djsum (chi phí) = qianghua × 3 + 1
upint (stat tăng) = round(zbsx × 0.08)  // 8% stat hiện tại
// Nếu upint < 1: upint = 1

// Tỷ lệ thất bại:
// Random 1-35, nếu <= qianghua: THẤT BẠI
// Random 1-30, nếu qianghua <= random: THÀNH CÔNG
```

### 6.4. Thần Trang (Set Bonus)

**Nguồn:** `taozhuang.php:120-128, player.php:405-460`

```
// Set bonus calculation:
percent = base_percent[tier] + extra_percent[stage]
```

| Stage | Items | HP/Công bonus | Phòng bonus | Bạo bonus |
|-------|-------|--------------|------------|----------|
| Sơ thành | 4+ | +tier% | 0% | 0% |
| Trung thành | 8+ | +(tier+1)% | 0% | 0% |
| Đại thành | 12+ | +(tier+2)% | +1% | 0% |
| Hoàn chỉnh | 16+ | +(tier+3)% | +1% | +1% |

---

## 7. LINH CĂN - SO SÁNH 22 LOẠI

**Nguồn:** `linhcan_config.json`

### 7.1. Bảng so sánh đầy đủ:

| ID | Tên | Tier | Element | ATK | DEF | HP | Crit | Absorb% | Exp% | Pet ATK% | Pet HP% | Pet Exp% | Weight |
|----|-----|------|---------|-----|-----|-----|------|---------|------|----------|---------|----------|--------|
| 1 | Phàm Kim | 1 | Kim | 8 | 0 | 0 | 0 | -8 | +5 | 0 | 0 | 0 | 8 |
| 2 | Phàm Mộc | 1 | Mộc | 0 | 0 | 15 | 0 | +10 | -5 | 0 | +10 | 0 | 8 |
| 3 | Phàm Thủy | 1 | Thủy | 2 | 2 | 2 | 0 | +12 | +8 | 0 | 0 | 0 | 8 |
| 4 | Phàm Hỏa | 1 | Hỏa | 5 | 0 | 0 | 5 | -12 | +12 | 0 | 0 | 0 | 8 |
| 5 | Phàm Thổ | 1 | Thổ | 0 | 10 | 8 | 0 | -3 | +3 | 0 | 0 | 0 | 8 |
| 6 | Kim Mộc | 2 | Kim | 10 | 0 | 12 | 0 | +5 | +10 | 0 | 0 | 0 | 5 |
| 7 | Thủy Hỏa | 2 | Thủy | 0 | 0 | 0 | 8 | +18 | -10 | 0 | 0 | +15 | 5 |
| 8 | Hỏa Thổ | 2 | Hỏa | 12 | 5 | 0 | 0 | -15 | +15 | 0 | 0 | 0 | 5 |
| 9 | Mộc Thủy | 2 | Mộc | 0 | 5 | 20 | 0 | +15 | +5 | +10 | 0 | 0 | 5 |
| 10 | Kim Hỏa | 2 | Kim | 15 | 0 | 0 | 3 | -10 | +8 | 0 | 0 | 0 | 5 |
| 11 | Kim Mộc Thủy | 3 | Kim | 8 | 5 | 15 | 0 | +8 | +12 | 0 | 0 | 0 | 3 |
| 12 | Hỏa Thổ Kim | 3 | Hỏa | 18 | 10 | 0 | 0 | -18 | +20 | +20 | 0 | 0 | 3 |
| 13 | Thủy Mộc Hỏa | 3 | Thủy | 0 | 0 | 25 | 5 | +20 | -12 | 0 | 0 | +15 | 3 |
| 14 | Kim Thủy Hỏa | 3 | Kim | 12 | 0 | 0 | 8 | +10 | +10 | 0 | 0 | 0 | 3 |
| 15 | Mộc Thổ Thủy | 3 | Mộc | 0 | 15 | 20 | 0 | +12 | +8 | 0 | +25 | 0 | 3 |
| 16 | Kim Mộc Thủy Hỏa | 4 | Kim | 10 | 0 | 10 | 0 | +12 | +15 | 0 | 0 | 0 | 2.4 |
| 17 | Thổ Kim Mộc Hỏa | 4 | Thổ | 20 | 15 | 15 | 0 | -20 | +25 | +25 | 0 | 0 | 2.4 |
| 18 | Thủy Mộc Hỏa Thổ | 4 | Thủy | 0 | 10 | 30 | 0 | +22 | -15 | 0 | +15 | +20 | 2.4 |
| 19 | Kim Thủy Hỏa Thổ | 4 | Kim | 15 | 8 | 0 | 10 | +8 | +12 | 0 | 0 | 0 | 2.4 |
| 20 | Kim Mộc Thổ Thủy | 4 | Mộc | 8 | 8 | 10 | 0 | +15 | +10 | +10 | +10 | 0 | 2.4 |
| 21 | **Ngũ Hành Vô Cực** | **5** | **All** | **15** | **15** | **15** | **0** | **+30** | **+30** | **+25** | **+25** | **0** | **0.8** |
| 22 | **Ngũ Hành Nghịch Thiên** | **5** | **None** | **25** | **25** | **25** | **0** | **-30** | **-20** | **+50** | **0** | **0** | **0.8** |

### 7.2. Xếp hạng tổng thể:

| Hạng | ID | Tên | Lý do |
|------|----|----|-------|
| 🥇 S+ | 21 | Ngũ Hành Vô Cực | Absorb +30%, Exp +30%, Stats toàn diện, hỗ trợ mọi công pháp |
| 🥈 S | 22 | Ngũ Hành Nghịch Thiên | ATK/DEF/HP +25, Pet ATK +50%, nhưng Absorb -30%, không dùng CP |
| 🥉 A+ | 17 | Thổ Kim Mộc Hỏa | ATK +20, DEF +15, HP +15, Exp +25%, Pet ATK +25% |
| 4 | 12 | Hỏa Thổ Kim | ATK +18, DEF +10, Exp +20%, Pet ATK +20% |
| 5 | 18 | Thủy Mộc Hỏa Thổ | Absorb +22%, HP +30, DEF +10, Pet HP +20% |
| 6 | 13 | Thủy Mộc Hỏa | Absorb +20%, HP +25, Pet Exp +15% |
| 7 | 7 | Thủy Hỏa | Absorb +18%, Pet Exp +15% |
| 8 | 9 | Mộc Thủy | Absorb +15%, HP +20, Pet ATK +10% |
| 9 | 15 | Mộc Thổ Thủy | DEF +15, HP +20, Pet HP +25% |
| 10 | 19 | Kim Thủy Hỏa Thổ | ATK +15, Crit +10 |

### 7.3. Xếp hạng theo mục đích:

** cho Tu luyện nhanh (Absorb + Exp cao nhất):**
1. Ngũ Hành Vô Cực: +30% absorb, +30% exp
2. Thủy Mộc Hỏa Thổ: +22% absorb
3. Thủy Mộc Hỏa: +20% absorb, nhưng exp -12%
4. Thủy Hỏa: +18% absorb
5. Mộc Thủy: +15% absorb

** cho Combat mạnh nhất (ATK + Crit):**
1. Thổ Kim Mộc Hỏa: ATK +20, DEF +15
2. Ngũ Hành Nghịch Thiên: ATK/DEF/HP +25
3. Hỏa Thổ Kim: ATK +18, DEF +10
4. Kim Hỏa: ATK +15, Crit +3
5. Kim Thủy Hỏa Thổ: ATK +15, Crit +10

** cho Pet mạnh nhất:**
1. Ngũ Hành Nghịch Thiên: Pet ATK +50%
2. Thổ Kim Mộc Hỏa: Pet ATK +25%
3. Hỏa Thổ Kim: Pet ATK +20%
4. Thủy Mộc Hỏa Thổ: Pet HP +20%, Pet Exp +20%

---

## 8. CÔNG PHÁP - SO SÁNH 12 LOẠI

**Nguồn:** `congphap_config.json`

### 8.1. Bảng so sánh:

| ID | Tên | Loại | Effect | Value (Lv1→10) | Penalty | Realm Required |
|----|-----|------|--------|----------------|---------|----------------|
| 1 | Hỗn Nguyên Tâm Pháp | Nội Công | absorb_speed | 5%→50% | -40% ATK/DEF | 0 |
| 2 | Thiên Địa Quyết | Tâm Pháp | exp_bonus | 3%→30% | 0% | 6 |
| 3 | Vạn Vật Quy Linh | Tâm Pháp | drop_rate | 3%→25% | 0% | 7 |
| 4 | Tụ Bảo Huyền Công | Huyền Công | stone_bonus | 5%→40% | 0% | 5 |
| 5 | Khoáng Mạch Thần Quyết | Thần Quyết | mining_bonus | 5%→50% | 0% | 5 |
| 6 | Hồi Xuân Quyết | Nội Công | heal_bonus | 3%→30% | 0% | 6 |
| 7 | Phá Thiên Quyết | Sát Pháp | crit_dmg | 2%→20% | 0% | 8 |
| 8 | Bất Động Minh Vương | Hộ Thể | def_bonus | 3%→25% | 0% | 11 |
| 9 | Mệnh Huyết Quyết | Nội Công | hp_regen | 1→10 HP/round | 0% | 10 |
| 10 | Vô Cực Tản | Tâm Pháp | dmg_reduce | 2%→20% | 0% | 13 |
| 11 | Thần Hành Bách Biến | Thân Pháp | absorb_speed | 8%→60% | -50% ATK/DEF | 15 |
| 12 | Đại Đạo Thiên Thư | Thiên Thư | exp_bonus | 5%→45% | -20% ATK/DEF | 25 |

### 8.2. Công pháp max equip slots:

```php
// congphap.php:292-297
if ($jingjieValue <= 6)  return 1;  // Nhập Môn → Ngự Khí
if ($jingjieValue <= 11) return 2;  // Luyện Khí → Nguyên Anh
return 3;                           // Tụ Thần → Đại Thừa
```

---

## 9. LINH CĂN UPGRADE SYSTEM

**Nguồn:** `linhcan_upgrade_config.json`

| Từ → Đến | Linh thạch | Đá Ngũ Hành | Tu Vi | Tỷ lệ thành công |
|-----------|-----------|-------------|-------|-------------------|
| Phàm → Huyền | 500 | 10 | 1,000 | 100% |
| Huyền → Địa | 2,000 | 30 | 5,000 | 80% |
| Địa → Thiên | 8,000 | 80 | 20,000 | 60% |
| Thiên → Thánh | 25,000 | 200 | 80,000 | 40% |
| Thánh → Niết | 80,000 | 500 | 300,000 | 20% |

---

## 10. LINH CĂN BOSS

**Nguồn:** `linhcan_boss_config.json`

| Stat | Giá trị |
|------|---------|
| Spawn interval | 120 phút |
| Base HP | 5,000 |
| Base ATK | 200 |
| Base DEF | 100 |
| HP/level | +500 |
| ATK/level | +20 |
| DEF/level | +10 |
| Linh thạch rơi | 100-500 |
| Đá ngũ hành rơi | 3 |

---

## 11. VÕ CÔNG (Martial Arts)

**Nguồn:** `wugong_config.json`, `wugong_utils.php`

### Hiệu ứng võ công trong chiến đấu:

| Effect | Mô tả | Giá trị |
|--------|-------|---------|
| damage_reduce | Giảm sát thương nhận | % value |
| pierce_def / break_armor | Xuyên phòng ngự | % value |
| combo_hit | Liên kích (% chance) | % chance → +35% ATK damage |
| steal_reward | Ăn trộm linh thạch | % chance → +12% linh thạch |
| exp_bonus | Tăng exp sau thắng | % value |
| drop_bonus | Tăng rơi vật phẩm | +value vào roll |
| counter_lifesteal | Hút máu thêm | % damage |

### EXP Võ công từ đánh quái/boss:

```
wgExp = max(5, min(bossLv, round(wgxlMax × 0.01)))
```

---

## 12. ĐƯỜNG ĐI TIẾN TRIỂN TỐI ƯU

### 12.1. Giai đoạn đầu (Lv1-100): ~3-5 ngày
1. **Tu luyện thường** (miễn phí) mỗi ngày 60 phút
2. Đánh quái trên map để lấy linh thạch
3. Làm nhiệm vụ chính tuyến
4. Không phí linh thạch vào đột phá sớm
5. Mở thiên phú: phân phối đều vào HP/Công/Phòng

### 12.2. Giai đoạn giữa (Lv100-500): ~5-7 ngày
1. **Tu luyện Ma thạch ×3** khi có đủ resource
2. Trang bị đầy đủ 16 ô
3. Nâng cấp công pháp: ưu tiên **Thiên Địa Quyết** (exp_bonus) hoặc **Đại Đạo Thiên Thư**
4. Đánh boss thường xuyên
5. Bắt đầu炼 đan để kiếm linh thạch

### 12.3. Giai đoạn muộn (Lv500-2000): ~20 ngày
1. Tu luyện Ma thạch ×3 liên tục
2. Đánh linh căn boss để nâng cấp linh căn
3. Thu thập thần trang set bonus
4. ưu tiên linh căn **Ngũ Hành Vô Cực** (tier 5) nếu có
5. Công pháp: **Thần Hành Bách Biến** (absorb +8→60%)

### 12.4. Tối đa hóa thu nhập hàng ngày:

| Hoạt động | Thời gian | Thu nhập |
|-----------|-----------|----------|
| Tu luyện Ma thạch ×3 | 12h | ~1.5B-50B exp |
| Đánh quái (PVE) | 2-4h | ~1-5M LT |
| Đánh Boss | 1-2h | ~500K-1M LT, 35 MT |
| Luyện đan | 1h | Variable |
| Nhiệm vụ | 30 phút | Config-dependent |

### 12.5. Xây nhân vật mạnh nhất:

**Ưu tiên stats (từ code):**
1. **HP** (umaxhp): +10 per tf point
2. **Công** (ugj): +5 per tf point
3. **Phòng** (ufy): +5 per tf point
4. **Bạo kích** (ubj): +1.5 per tf point
5. **Hút máu** (uxx): +2 per tf point
6. **May mắn** (tfxy): affects drop rate
7. **Né tránh** (tfsb): affects dodge

**Max level:** 4300

**Optimal build:**
- Spirit root: Ngũ Hành Vô Cực (ID 21)
- Công pháp: 3 equipped (Thần Hành Bách Biến + Đại Đạo Thiên Thư + Phá Thiên Quyết)
- Thần trang: 16-piece tier 5 set = 8% HP/Công + extra bonuses
- Talent: Full HP/Công depending on build

---

## 13. MAX STATS ACHIEVABLE (ƯỚC TÍNH)

### Tại Level 4300:

**Level up stats (accumulated):**
- ATK from level: 55 × 86 = **4,730** (55 per 50 levels × 86 realm groups)
- DEF from level: 55 × 86 = **4,730**
- HP from level: 550 × 86 = **47,300**

**Talent points:** 4300 × 5 = **21,500 points**
- Full ATK: +21,500 × 5 = **+107,500 ATK**
- Full HP: +21,500 × 10 = **+215,000 HP**

**Thiên phú từ level up (inherent):**
- rangesbj, rangesxx, etc. từ `upplayerlv()` = depends on shenfen (identity)

**Equipment (16 slots):** Varies by gear

**Linh căn (Ngũ Hành Vô Cực):** ATK +15, DEF +15, HP +15

**Thần Trang (16-set tier 5):** +8% HP, +8% ATK, +1% DEF

**Ước tính MAX:**
```
HP ≈ 47,300 (level) + 215,000 (talent) + 200,000 (equip) + bonuses ≈ 500,000+
ATK ≈ 4,730 (level) + 107,500 (talent) + 50,000 (equip) + bonuses ≈ 165,000+
DEF ≈ 4,730 (level) + 21,500 (talent) + 30,000 (equip) + bonuses ≈ 56,000+
```

*Lưu ý: Đây là ước tính rất thô, thực tế phụ thuộc nhiều vào trang bị cụ thể.*
