# PROPOSAL MÔ HÌNH PROGRESSION DÀI HẠN & HỆ THỐNG NHIỆM VỤ

> **Mục tiêu:** VIP player tối thiểu 2 năm để đạt endgame
> **Free player:** 3-5 năm
> **Nhiệm vụ:** Phân bổ đều, tương tác với炼 đan + chế tạo
> **Triết lý:** Mỗi realm = 1 giai đoạn gameplay riêng

---

## I. PHÂN TÍCH THỜI GIAN PROGRESSION

### Mô hình EXP requirement mới

Thay vì dùng `rangesexp` multiplier, dùng hàm số bậc 2.8:

```php
// EXP cần để lên cấp N
$exp_for_level = max(100, pow($nextlv, 2.8) * 2);

// Breakthrough (mỗi 50 level) cần thêm:
$breakthrough_exp = pow($level, 2.5) * 50;
```

### Bảng dự báo thời gian

| Giai Đoạn | Level | EXP/Level (TB) | Tổng EXP | VIP (ngày) | Free (ngày) |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | ~3K | ~150K | 1 | 2 |
| Luyen The | 50-100 | ~30K | ~1.5M | 4 | 8 |
| Tu Khi | 100-200 | ~250K | ~25M | 60 | 120 |
| Kim Dan | 200-300 | ~1.2M | ~120M | 130 | 260 |
| Nguyen Anh | 300-400 | ~4M | ~400M | 270 | 540 |
| Hop The | 400-500 | ~10M | ~1B | 360 | 720 |
| Dai Thua | 500-700 | ~30M | ~6B | endgame | endgame |
| Do Kiep | 700-1000 | ~100M | ~30B | endgame | endgame |

**VIP (Lv0->500):** ~825 ngay = **2.3 nam** ✓
**Free (Lv0->500):** ~1650 ngay = **4.5 nam** ✓

---

## II. HE THONG NHIEM VU MOI

### Tong quan cau truc

```
NHIEM VU
├── CHINH TUYEN (Main Quest Line)
│   ├── Chuong 1-5: Nhap Mon (Lv1-50)
│   ├── Chuong 6-10: Luyen The (Lv50-100)
│   ├── Chuong 11-20: Tu Khi (Lv100-200)
│   ├── Chuong 21-35: Kim Dan (Lv200-300)
│   ├── Chuong 36-55: Nguyen Anh (Lv300-400)
│   ├── Chuong 56-80: Hop The (Lv400-500)
│   └── Chuong 81-100: Dai Thua+ (Lv500+)
│
├── PHU TUYEN (Side Quests)
│   ├── Nhiem Vu Ngay (Daily) - 5-8 NV/ngay
│   ├── Nhiem Vu Tuan (Weekly) - 10 NV/tuan
│   ├── Nhiem Vu Thang (Monthly) - 5 NV/thang
│   ├── Nhiem Vu Thanh Tuu (Achievement) - vinh vien
│   └── Nhiem Vu An (Hidden) - kham pha
│
└── NHIEM VU TUONG tac (Interaction Quests)
    ├── Luyen Dan Quest Line
    ├── Che Tao Quest Line
    ├── Nang Cap Equipment Quest Line
    └── Thu Thap Nguyen Lieu Quest Line
```

---

### A. CHINH TUYEN - Moi chuong = 1 giai doan game

#### Chuong 1: Khoi Dau (Lv1-10, ~2 ngay)

| NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|
| 1.1 | Dialogue | Noi chuyen NPC | 50 EXP, 20 LT | - |
| 1.2 | Kill | 10 quai Lv1-5 | 100 EXP, 50 LT | - |
| 1.3 | Kill | 20 quai Lv5-10 | 200 EXP, 100 LT | - |
| 1.4 | Thu thap | 5 Thao Duoc Pho Thong | 300 EXP, 150 LT | Luyen Dan |
| 1.5 | Luyen Dan | Luyen thanh cong 1 thuoc | 500 EXP, 200 LT, Da Cuong Hoa | Luyen Dan |
| 1.6 | Che tao | Che tao 1 vu khi cap 1 | 800 EXP, 300 LT, Vu khi xanh | Che Tao |
| 1.7 | Breakthrough | Dot pha Lv10 | 1000 EXP, 500 LT | - |

#### Chuong 2: Lam Quen (Lv10-25, ~5 ngay)

| NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|
| 2.1 | Thu thap | 10 Thao Duoc + 5 Da Hoa | 500 EXP | - |
| 2.2 | Luyen Dan | Luyen 3 Thuoc Hoi Huyet | 800 EXP, 200 LT | Luyen Dan |
| 2.3 | Kill | 50 quai Lv10-20 | 1000 EXP, 300 LT | - |
| 2.4 | Thu thap | 3 Cuong Hoa Thach | 600 EXP | - |
| 2.5 | Che tao | Nang cap vu khi +2 | 1200 EXP, 500 LT | Che Tao |
| 2.6 | Breakthrough | Dot pha Lv25 | 1500 EXP, 800 LT | - |

#### Chuong 3: Tu Luyen (Lv25-50, ~10 ngay)

| NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|
| 3.1 | Thu thap | 20 Thao Q.Hiem + 10 Da Ngu Hanh | 2000 EXP | - |
| 3.2 | Luyen Dan | Luyen 5 Dan Duoc (lv5+) | 3000 EXP, 1000 LT | Luyen Dan |
| 3.3 | Che tao | Che tao 1 trang bi Vang | 4000 EXP, 1500 LT | Che Tao |
| 3.4 | Kill | 100 quai Lv25-40 | 5000 EXP, 2000 LT | - |
| 3.5 | Thu thap | 5 Binh Anh Tinh Thach (Boss) | 3000 EXP | Boss |
| 3.6 | Che tao | Nang cap trang bi +5 (tong) | 6000 EXP, 2500 LT | Che Tao |
| 3.7 | Boss | 5 boss Linh Can | 4000 EXP, 1000 LT | - |
| 3.8 | Breakthrough | Dot pha Lv50 (Major) | 8000 EXP, 5000 LT | - |

#### Chuong 4-5: Trung Cap (Lv50-100, ~1 thang)

Moi chuong 8-10 NV, yeu cau:
- Luyen Dan: 10-20 lan luyen cac loai dan duoc khac nhau
- Che tao: 5-10 lan che tao equipment moi
- Nang cap: Dat +8, +10 cho equipment
- Thu thap: 50-100 nguyen lieu hiem
- Kill: 200-500 quai cac cap
- Boss: 10-20 boss
- Breakthrough: Dat Lv100

#### Chuong 6-10: Cao Cap (Lv100-200, ~2 thang)

Yeu cau tang dan:
- Luyen Dan: Luyen 50+ dan duoc cap cao
- Che tao: Che tao equipment Tim
- Nang cap: Dat +12, +15
- Thu thap: Nguyen lieu tu boss hiem
- Set equipment: Hoan thanh 1 set co ban
- Breakthrough: Dat Lv200

#### Chuong 11-20: Cao Cap (Lv200-300, ~4 thang)

- Luyen Dan: Luyen 100+ dan duoc rare
- Che tao: Che tao equipment Cam
- Set equipment: Hoan thanh 2 set
- Linh can: Nang cap linh can 3 cap
- Breakthrough: Dat Lv300

#### Chuong 21-35: Nguyen Anh (Lv300-400, ~9 thang)

- Luyen Dan: Luyen 200+ dan duoc epic
- Che tao: Che tao equipment dac biet
- Set: Hoan thanh set "Linh"
- Linh can: Nang cap linh can 5 cap
- Boss: 50+ boss hiem
- Breakthrough: Dat Lv400

#### Chuong 36-55: Hop The (Lv400-500, ~12 thang)

- Luyen Dan: Luyen 500+ dan duoc legendary
- Che tao: Che tao equipment set hoan chinh
- Set: Hoan thanh 3+ sets
- Linh can: Max linh can
- Boss: 100+ boss
- Breakthrough: Dat Lv500

---

### B. PHU TUYEN - Nhiem Vu Hang Ngay/Tuan/Thang

#### 1. Nhiem Vu Ngay (Daily Quests)

**Moi ngay 5 NV, chon 3 trong 8 loai:**

| NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|
| D1 | Tu luyen | Tu luyen 30 phut | 500 EXP, 200 LT | - |
| D2 | Kill | 30 quai bat ky | 400 EXP, 150 LT | - |
| D3 | Luyen Dan | Luyen 3 dan duoc | 600 EXP, 300 LT | Luyen Dan |
| D4 | Che tao | Che tao 1 trang bi | 500 EXP, 250 LT | Che Tao |
| D5 | Nang cap | Nang cap 1 trang bi | 400 EXP, 200 LT | Che Tao |
| D6 | Thu thap | Thu thap 10 nguyen lieu | 300 EXP, 100 LT | - |
| D7 | Boss | 1 boss Linh Can | 700 EXP, 400 LT | - |
| D8 | Dau truong | 3 tran PVP | 500 EXP, 300 LT | - |

**Bonus:** Hoan thanh 5/5 -> 1000 EXP, 500 LT, 5 MT
**Tong EXP/ngay:** ~3500 EXP + ~1550 LT + 5 MT

#### 2. Nhiem Vu Tuan (Weekly Quests)

**Moi tuan 10 NV:**

| NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|
| W1 | Luyen Dan | Luyen 10 dan duoc | 3000 EXP, 1000 LT | Luyen Dan |
| W2 | Kill | 100 quai | 2500 EXP, 800 LT | - |
| W3 | Che tao | Che tao 3 trang bi | 3000 EXP, 1200 LT | Che Tao |
| W4 | Boss | 5 boss | 3500 EXP, 1500 LT | - |
| W5 | Nang cap | Nang cap 5 trang bi | 2500 EXP, 1000 LT | Che Tao |
| W6 | Kill | 200 quai | 3000 EXP, 1200 LT | - |
| W7 | Luyen Dan | Luyen 20 dan duoc | 4000 EXP, 1500 LT | Luyen Dan |
| W8 | Boss | 10 boss | 4500 EXP, 2000 LT | - |
| W9 | Thu thap | Thu thap 50 nguyen lieu | 3000 EXP, 1000 LT | - |
| W10 | All | Hoan thanh W1-W9 | 10000 EXP, 5000 LT, 20 MT | - |

**Tong EXP/tuan:** ~39,000 EXP + ~16,200 LT + 20 MT

#### 3. Nhiem Vu Thang (Monthly Quests)

**5 NV moi thang:**

| NV | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|
| M1 | Luyen 100 dan cap 5+ | 50000 EXP, 20000 LT, 50 MT | Luyen Dan |
| M2 | Che tao 10 trang bi Vang+ | 60000 EXP, 25000 LT, 50 MT | Che Tao |
| M3 | 50 boss | 80000 EXP, 30000 LT, 50 MT | - |
| M4 | Nang cap tong +50 | 70000 EXP, 25000 LT, 50 MT | Che Tao |
| M5 | Hoan thanh M1-M4 | 200000 EXP, 100000 LT, 200 MT | - |

**Tong EXP/thang:** ~460,000 EXP + ~200,000 LT + 400 MT

#### 4. Nhiem Vu Thanh Tuu (Achievement Quests)

**Vinh vien, hoan thanh 1 lan:**

| Nhom | Yeu Cau | Phan Thuong |
|---|---|---|
| Sat Thu | Giet 1000/5000/10000 quai | 10K/50K/200K EXP + 5K/20K/100K LT |
| Luyen Dan Su | Luyen 100/500/1000 dan | 15K/75K/300K EXP + 8K/30K/120K LT |
| Tho Ren | Che tao 50/200/500 trang bi | 15K/75K/300K EXP + 8K/30K/120K LT |
| Thu Thap | Thu thap 500/2000/5000 nguyen lieu | 10K/40K/150K EXP + 5K/20K/80K LT |
| Boss Hunter | 10/50/100 boss | 20K/100K/500K EXP + 10K/50K/200K LT |
| Dau Truong | 100/500/1000 tran | 15K/60K/250K EXP + 8K/30K/100K LT |
| Tu Luyen | 100/500/1000 gio | 20K/100K/400K EXP + 10K/50K/200K LT |
| Set Hoan Thanh | 1/3/5 set equipment | 50K/200K/1M EXP + 25K/100K/500K LT |

#### 5. Nhiem Vu An (Hidden Quests)

**Khong hien thi, kham pha bang cach:**

| NV | Cach Kich Hoat | Yeu Cau | Phan Thuong |
|---|---|---|---|
| H1 | Luyen 10 dan cung luc | Luyen 10 dan thanh cong | 5000 EXP, 2000 LT |
| H2 | Che tao 5 trang bi lien tiep | Che tao 5 khong fail | 8000 EXP, 3000 LT |
| H3 | Danh boss voi HP < 10% | Thang boss HP < 10% | 10000 EXP, 5000 LT |
| H4 | Thu thap du 5 da nguyen to | Co du 5 loai da | 15000 EXP, 8000 LT |
| H5 | Nang cap +15 khong fail | Nang cap +15 thanh cong | 20000 EXP, 10000 LT |
| H6 | Giet 100 quai trong 5 phut | Hoan thanh trong 5 phut | 12000 EXP, 5000 LT |
| H7 | Luyen dan ty le < 20% | Luyen thanh cong khi ty le < 20% | 15000 EXP, 8000 LT |
| H8 | 5 tran PVP thang lien tiep | Thang 5 tran LQ | 10000 EXP, 5000 LT |

---

### C. NHIEM VU TUONG TAC - Moi tinh nang = 1 quest line

#### 1. Quest Line Luyen Dan (15 Nhiem Vu)

| NV | Yeu Cau | Phan Thuong | Unlock |
|---|---|---|---|
| LD1 | Thu thap 10 Thao Duoc Pho Thong | 500 EXP | Lv5 |
| LD2 | Luyen 5 Thuoc Hoi Huyet | 1000 EXP, 50 LT | LD1 |
| LD3 | Thu thap 5 Da Hoa Tinh | 1500 EXP, 100 LT | LD2 |
| LD4 | Luyen 3 Dan Duoc (lv5+) | 2500 EXP, 200 LT | LD3 |
| LD5 | Thu thap 10 Thao Q.Hiem | 3000 EXP, 300 LT | LD4 |
| LD6 | Luyen 5 Dan Duoc (lv8+) | 4000 EXP, 500 LT | LD5 |
| LD7 | Thu thap 3 Binh Anh Tinh Thach | 5000 EXP, 800 LT | LD6 |
| LD8 | Luyen 10 Dan Duoc (lv15+) | 7000 EXP, 1200 LT | LD7 |
| LD9 | Luyen 1 Dan Binh Anh | 10000 EXP, 2000 LT | LD8 |
| LD10 | Luyen 20 Dan Duoc (lv20+) | 15000 EXP, 3000 LT | LD9 |
| LD11 | Luyen 30 Dan Duoc (lv25+) | 20000 EXP, 5000 LT | LD10 |
| LD12 | Luyen 50 Dan Duoc (lv30+) | 30000 EXP, 8000 LT | LD11 |
| LD13 | Luyen 100 Dan Duoc (lv40+) | 50000 EXP, 15000 LT | LD12 |
| LD14 | Luyen 200 Dan Duoc (lv50+) | 80000 EXP, 25000 LT | LD13 |
| LD15 | Luyen 500 Dan Duoc (lv80+) | 150000 EXP, 50000 LT | LD14 |

#### 2. Quest Line Che Tao (15 Nhiem Vu)

| NV | Yeu Cau | Phan Thuong | Unlock |
|---|---|---|---|
| CT1 | Thu thap 10 Cuong Hoa Thach | 500 EXP | Lv5 |
| CT2 | Che tao 1 Vu khi xanh | 1000 EXP, 50 LT | CT1 |
| CT3 | Nang cap vu khi +2 | 1500 EXP, 100 LT | CT2 |
| CT4 | Che tao 1 Giap xanh | 2500 EXP, 200 LT | CT3 |
| CT5 | Nang cap tong +5 | 3000 EXP, 300 LT | CT4 |
| CT6 | Che tao 1 trang bi Vang | 4000 EXP, 500 LT | CT5 |
| CT7 | Nang cap trang bi +8 | 5000 EXP, 800 LT | CT6 |
| CT8 | Che tao 5 trang bi Vang | 7000 EXP, 1200 LT | CT7 |
| CT9 | Nang cap trang bi +10 | 10000 EXP, 2000 LT | CT8 |
| CT10 | Che tao 1 trang bi Tim | 15000 EXP, 3000 LT | CT9 |
| CT11 | Nang cap trang bi +12 | 20000 EXP, 5000 LT | CT10 |
| CT12 | Che tao 3 trang bi Tim | 30000 EXP, 8000 LT | CT11 |
| CT13 | Nang cap trang bi +15 | 50000 EXP, 15000 LT | CT12 |
| CT14 | Che tao 1 trang bi Cam | 80000 EXP, 25000 LT | CT13 |
| CT15 | Nang cap trang bi +18 | 150000 EXP, 50000 LT | CT14 |

#### 3. Quest Line Thu Thap (10 Nhiem Vu)

| NV | Yeu Cau | Phan Thuong | Unlock |
|---|---|---|---|
| TT1 | Thu thap 20 Thao Duoc Pho Thong | 500 EXP | Lv10 |
| TT2 | Thu thap 10 Da Hoa Tinh | 1000 EXP, 100 LT | TT1 |
| TT3 | Thu thap 5 Thao Q.Hiem | 2000 EXP, 200 LT | TT2 |
| TT4 | Thu thap 10 Da Thuy Tinh | 3000 EXP, 500 LT | TT3 |
| TT5 | Thu thap 20 Da Moc Tinh | 5000 EXP, 800 LT | TT4 |
| TT6 | Thu thap 30 Da Kim Tinh | 8000 EXP, 1200 LT | TT5 |
| TT7 | Thu thap 5 Binh Anh | 12000 EXP, 2000 LT | TT6 |
| TT8 | Thu thap 50 Da Ngu Hanh | 20000 EXP, 5000 LT | TT7 |
| TT9 | Thu thap 100 Nguyen Lieu Hiem | 40000 EXP, 10000 LT | TT8 |
| TT10 | Thu thap 200 Nguyen Lieu Cao Cap | 80000 EXP, 25000 LT | TT9 |

---

## III. THAY DOI CODE CAN THIET

### 1. EXP Formula moi (player.php)

```php
// CU
$umaxexp = $nextlv * ($nextlv + round($nextlv / 2)) * 10 * $rangesexp[$ncc] + $nextlv;

// MOI
$umaxexp = max(100, pow($nextlv, 2.8) * 2);
```

### 2. Cap absorb/exp bonus (xiulian.php)

```php
// Absorb tong toi da = 50%
$total_absorb = min(0.50, $lc_absorb + $cp_absorb);

// EXP bonus tong toi da = 40%
$total_exp_bonus = min(0.40, $lc_exp_bonus + $cp_exp_bonus);
```

### 3. Scale tu luyen cost (xiulian.php)

```php
// CU: 1 LT/phut, 1 MT/phut
// MOI:
$lt_cost_per_min = max(1, ceil(pow($ulv, 1.15) / 5));
$mt_cost_per_min = max(1, ceil($ulv / 3));
```

### 4. Giam PVE LT reward (pve.php)

```php
// CU: $yxb = round($glv * 22.5) + mt_rand(1, 200);
// MOI:
$yxb = round($glv * 5) + mt_rand(1, 50);
```

### 5. Giam max time tu luyen (xiulian.php)

```php
// CU: Free=60, LT=360, MT=720 phut
// MOI: Free=30, LT=180, MT=360 phut
```

### 6. Them Daily/Weekly/Monthly Quest System

```php
// DB table: player_daily_quests
// Fields: uid, quest_id, progress, status, reset_date

// Daily reset logic
if ($today != $last_reset) {
    // Mark all incomplete daily quests as expired
    // Generate new daily quests (random 5 from pool of 8)
    // Reset daily quest counter
}

// Weekly reset logic
if ($week_number != $last_week) {
    // Reset all weekly quests
    // Generate new weekly quests
}
```

---

## IV. TONG KET

### Thay doi chinh

1. **EXP curve moi:** `pow(level, 2.8) * 2` - VIP 2+ nam, Free 4+ nam
2. **5 loai nhiem vu:** Chinh tuyen, Ngay, Tuan, Thang, An
3. **Nhiem vu tuong tac:** 40+ NV lien quan den Luyen Dan + Che Tao
4. **Cap absorb/exp bonus:** 50% va 40%
5. **Scale chi phi tu luyen:** Theo cap
6. **Giam PVE LT:** 78%

### Ket qua mong doi

- **VIP player:** 2-3 nam de dat Lv500
- **Free player:** 4-5 nam de dat Lv500
- **Free:VIP ratio:** ~1:2 (cong bang)
- **Nhiem vu:** Chinh tuyen ~100 NV, Phu tuyen 5+30+5+成就+An = 100+ NV
- **Tuong tac:** 40+ NV lien quan den Luyen Dan va Che Tao
- **Retention:** Cao vi luon co muc tieu nho de dat
- **Doanh thu:** Tang vi MT can cho tien loi

---

*Proposal nay thay the ECONOMY_BALANCE_PROPOSAL.md*
*File: D:\XXJserver\PROGRESSION_LONG_TERM.md*
