# TONG HOP TAI NGUYEN & MO HINH PROGRESSION TOAN DIEN

> Tai lieu nay tong hop TAT CA he thong cho/tieu thu tai nguyen
> Muc dich: Tinh toan chinh xac thoi gian progression 2+ nam
> Luu y: Khong dung chu Trung Quoc, chi dung tieng Viet

---

## PHAN 1: BAN DO TAI NGUYEN TOAN GAME

### 1.1 Tat Ca Nguon Thu Nhap

#### A. TAO NHAN VAT — Mot lan duy nhat

| Tai Nguyen | So Luong |
|---|---|
| Linh Thach | 2,000 |
| Ma Thach | 100 |

#### B. TU LUYEN AFK (xiulian.php) — Hang ngay

| Che Do | EXP/sec | Chi Phi | Gioi Han |
|---|---|---|---|
| Thuong (Free) | ulv × 1.0 | Mien phi | 30 phut/ngay |
| Linh Thach x2 | ulv × 2.0 | LT/phut (scale) | 180 phut/ngay |
| Ma Thach x2 | ulv × 2.5 | MT/phut (scale) | 360 phut/ngay |

**Cong thuc EXP/sec:**
```
mode1: ulv × 1.0
mode2: ulv × 2.0
mode3: ulv × 2.5
VIP bonus: × 1.1
Absorb bonus: × (1 + absorb), toi da 1.5
Cong phap bonus: × (1 + cp_exp), toi da 1.4
Tong EXP/sec toi da = ulv × 2.5 × 1.1 × 1.5 × 1.4 = ulv × 5.775
```

**Chi phi/tick (10 giay):**
```
mode2: max(1, ceil(pow(ulv, 1.15) / 5)) LT
mode3: max(1, ceil(ulv / 3)) MT
```

| Level | EXP/30ph Free | EXP/3h LT | EXP/6h MT | LT/3h | MT/6h |
|---|---|---|---|---|---|
| 50 | 90,000 | 1,800,000 | 5,625,000 | 9,000 | 1,200 |
| 100 | 180,000 | 3,600,000 | 11,250,000 | 22,680 | 2,400 |
| 200 | 360,000 | 7,200,000 | 22,500,000 | 54,000 | 4,800 |
| 300 | 540,000 | 10,800,000 | 33,750,000 | 93,600 | 7,200 |
| 500 | 900,000 | 18,000,000 | 56,250,000 | 162,000 | 12,000 |

#### C. PVE DANH QUAI (pve.php) — Khong gioi han

| Phan Thuong | Cong Thuc | Level 50 | Level 100 | Level 200 |
|---|---|---|---|---|
| EXP | tu config quai (gexp) | 0-9,000 | 0-9,000 | 0-9,000 |
| Linh Thach | glv × 5 + rand(1,50) | ~300 | ~550 | ~1,050 |
| Vat pham | ty le theo config | 5-50% | 5-50% | 5-50% |

**Luu y:** Hau het quai co gexp = null (khong cho EXP). Chi quai ID 53 cho 9,000 EXP.

#### D. PVP GIET NGUOI (pvp.php) — Khong gioi han

| Phan Thuong | Cong Thuc | Level 50 | Level 100 | Level 200 |
|---|---|---|---|---|
| EXP | 100 + ulv × 20 | 1,100 | 2,100 | 4,100 |
| Linh Thach | rand(500,1000) + 15 × ulv | 1,250 | 2,000 | 3,500 |
| Ma Thach | 100 + 15 × ulv | 850 | 1,600 | 3,100 |

#### E. BOSS THE GIOI (boss.php) — Co gioi han

| Phan Thuong | Cong Thuc | Level 50 | Level 100 | Level 200 |
|---|---|---|---|---|
| EXP | 100 + ulv × 20 | 1,100 | 2,100 | 4,100 |
| Linh Thach | rand(500,1000) + 15 × ulv | 1,250 | 2,000 | 3,500 |
| Ma Thach | 100 + 15 × ulv | 850 | 1,600 | 3,100 |
| Vat pham | ty le theo config | co the co | co the co | co the co |

#### F. TOA YEU THAP (toayeuthap.php) — Mot lan clear

| Tang | EXP | EXP Cong Phap | Tong EXP Tich Luy |
|---|---|---|---|
| 1 | 184 | 9 | 184 |
| 10 | 630 | 24 | 3,200 |
| 25 | 2,084 | 48 | 18,500 |
| 50 | 7,840 | 88 | 85,000 |
| 75 | 18,108 | 128 | 280,000 |
| 100 | 35,340 | 168 | 720,000 |
| 125 | 58,576 | 208 | 1,500,000 |
| 150 | 79,240 | 248 | 2,200,000 |

**Gioi han:** 150 tang, khong reset, khong co LT hay vat pham.

#### G. VAN DINH DAI (vandinhdai.php) — 10 tran/ngay

| Phan Thuong | Cong Thuc |
|---|---|
| EXP thang | 80 + enemy_lv × 8 |
| EXP thua | max(12, 30 + enemy_lv × 2) |
| EXP Cong Phap thang | 6 + max(1, enemy_lv) / 3 |

**Thuong Hang Ngay 21:00 (Top 10):**

| Hang | Linh Thach | Ma Thach |
|---|---|---|
| #1 | 10,000 | 100 |
| #2 | 8,500 | 85 |
| #3 | 7,200 | 72 |
| #4 | 6,000 | 60 |
| #5 | 5,000 | 50 |
| #6 | 4,200 | 42 |
| #7 | 3,500 | 35 |
| #8 | 2,800 | 28 |
| #9 | 2,200 | 22 |
| #10 | 1,600 | 16 |
| #11-20 | 0 | 0 |
| #21-50 | 0 | 0 |
| #51-100 | 0 | 0 |

#### H. HAM MO (hammo.php) — 30 lan/ngay, tien 10 MT/lan

| Roll | Ket Qua | Linh Thach | Ty Le |
|---|---|---|---|
| 1-50 | Mach ma thach nho | 25-55 + bonus | 0.5% |
| 51-150 | Linh thach tinh | 900-1600 + bonus×2 | 1.0% |
| 151-550 | Linh thach nguyen khoi | 350-750 + bonus | 4.0% |
| 551-4550 | Linh thach tho | 80-220 + bonus | 40.0% |
| 4551-8200 | Manh linh quang | 20-80 + bonus/2 | 36.5% |
| 8201-10000 | Da vun | 0 | 18.0% |

**Bonus = min(20, floor(sqrt(level)))**

#### I. NHIEM VU (renwu_config.json) — Theo cau truc moi

| Loai | So Luong | EXP | LT | MT |
|---|---|---|---|---|
| Chinh tuyen (13 chuong) | ~170 NV | 3,000 - 150,000/NV | 0 - 50,000/NV | 0 |
| Ngay (5 NV + bonus) | 5+1/ngay | ~4,500/ngay | ~2,050/ngay | 5/ngay |
| Tuần (10 NV) | 10/tuan | ~39,000/tuan | ~16,200/tuan | 20/tuan |
| Thang (5 NV) | 5/thang | ~460,000/thang | ~200,000/thang | 400/thang |
| Thanh tuu (8 nhom) | 24 NV | ~3,860,000 (1 lan) | ~1,668,000 (1 lan) | 0 |
| An (8 NV) | 8 NV | ~95,000 (1 lan) | ~46,000 (1 lan) | 0 |
| Tuong tac Luyen Dan | 15 NV | ~379,500 (1 lan) | ~111,150 (1 lan) | 0 |
| Tuong tac Che Tao | 15 NV | ~379,500 (1 lan) | ~111,150 (1 lan) | 0 |
| Tuong tac Thu Thap | 10 NV | ~171,500 (1 lan) | ~44,800 (1 lan) | 0 |
| Tuong tac Nang Cap | 10 NV | ~166,500 (1 lan) | ~84,150 (1 lan) | 0 |

#### J. LINH CAN QUEST (linhcan_quest.php) — 5 NV/tuan

| NV | Yeu Cau | Phan Thuong |
|---|---|---|
| Tu luyen can man | Tu luyen 60 phut | 100 LT + 2 da |
| Diet tru yeu ma | Giet 10 quai | 100 LT + 2 da |
| Luyen dan tu than | Dung 3 vat pham | 100 LT + 2 da |
| Nuoi thu thanh tai | EXP thu 1 lan | 100 LT + 2 da |
| Vuot ai diet boss | Giet 1 boss | 100 LT + 2 da |
| **Bonus 5/5** | Hoan thanh tat ca | 500 LT + 10 da |

**Tong/tuan:** 1,000 LT + 50 da + 10 da = 1,000 LT + 60 da

#### K. BOSS LINH CAN (linhcan_boss.php) — 12 boss/ngay

| Phan Thuong | Gia Tri |
|---|---|
| EXP | 100 + ulv × 20 |
| Linh Thach | 50-150 |
| Vat pham | Ngau nhien |

---

### 1.2 Tat Ca Noi Thu Tieu Thu

#### A. TU LUYEN AFK — Chi phi chinh

| Che Do | Chi Phi |
|---|---|
| Mode 2 (LT) | max(1, ceil(pow(ulv, 1.15) / 5)) LT/phut |
| Mode 3 (MT) | max(1, ceil(ulv / 3)) MT/phut |

#### B. DOT PHA (tupo.php) — Chi phi lon

| Loai | Cong Thuc | Level 50 | Level 100 | Level 200 |
|---|---|---|---|---|
| Nho (moi level) | level × (level+1) × 2 | 5,100 | 20,200 | 80,400 |
| Lon (moi 50 level) | level³ × 2 | 250,000 | 2,000,000 | 16,000,000 |

**Linh thach bi tru truoc khi kiem tra thanh cong.**

#### C. MUA SACH CONG PHAP — 400 LT/sach

#### D. DI CHUYEN NHIEM VU — (level × 12 + 500) LT/luc

#### E. HAM MO — 10 MT/lan, toi da 30 lan/ngay

#### F. GACHA CONG PHAP — 200 MT/lan

#### G. GACHA THU CUNG — MT/lan

#### H. NANG CAP LINH CAN — Da + MT

#### I. LUA LENH — 200 LT/lan

---

## PHAN 2: TINH TOAN THU NHAP HANG NGAY

### 2.1 Free Player (Khong nap tien)

**Gioi han:** Mode 1 free 30 phut, khong MT de mode 3

#### Level 50

| Nguon | EXP/ngay | LT/ngay | MT/ngay |
|---|---|---|---|
| Tu luyen free (30 ph) | 90,000 | 0 | 0 |
| PVE (50 quai) | 0 | 15,000 | 0 |
| Boss Linh Can (6) | 6,600 | 600 | 0 |
| NV Ngay (5+bonus) | 4,500 | 2,050 | 5 |
| NV Tuan (TB/ngay) | 5,570 | 2,314 | 2.86 |
| NV Thang (TB/ngay) | 15,330 | 6,667 | 13.33 |
| Linh Can Quest | 0 | 143 | 0 |
| Dau Truong (5 tran, #20) | 5,500 | 0 | 0 |
| **TONG** | **127,500** | **26,774** | **21.19** |

#### Level 100

| Nguon | EXP/ngay | LT/ngay | MT/ngay |
|---|---|---|---|
| Tu luyen free (30 ph) | 180,000 | 0 | 0 |
| PVE (100 quai) | 0 | 55,000 | 0 |
| Boss Linh Can (6) | 13,200 | 600 | 0 |
| NV Ngay | 4,500 | 2,050 | 5 |
| NV Tuan (TB) | 5,570 | 2,314 | 2.86 |
| NV Thang (TB) | 15,330 | 6,667 | 13.33 |
| Linh Can Quest | 0 | 143 | 0 |
| Dau Truong (5 tran, #15) | 10,500 | 0 | 0 |
| **TONG** | **229,100** | **66,774** | **21.19** |

#### Level 200

| Nguon | EXP/ngay | LT/ngay | MT/ngay |
|---|---|---|---|
| Tu luyen free (30 ph) | 360,000 | 0 | 0 |
| PVE (100 quai) | 0 | 105,000 | 0 |
| Boss Linh Can (6) | 26,400 | 600 | 0 |
| NV Ngay | 4,500 | 2,050 | 5 |
| NV Tuan (TB) | 5,570 | 2,314 | 2.86 |
| NV Thang (TB) | 15,330 | 6,667 | 13.33 |
| Linh Can Quest | 0 | 143 | 0 |
| Dau Truong (5 tran, #10) | 20,500 | 0 | 0 |
| **TONG** | **432,300** | **116,774** | **21.19** |

### 2.2 VIP Player (Nap tien, MT x2 hang ngay)

**Gioi han:** Mode 3 voi MT, VIP bonus 1.1x

#### Level 50

| Nguon | EXP/ngay | LT/ngay | MT/ngay |
|---|---|---|---|
| Tu luyen MT (6h) | 5,625,000 | 0 | -1,200 |
| PVE (50 quai) | 0 | 15,000 | 0 |
| Boss Linh Can (12) | 13,200 | 1,200 | 0 |
| NV Ngay | 4,950 | 2,255 | 5.5 |
| NV Tuan (TB) | 6,127 | 2,545 | 3.15 |
| NV Thang (TB) | 16,863 | 7,334 | 14.67 |
| Linh Can Quest | 0 | 143 | 0 |
| Dau Truong (10 tran, #5) | 16,500 | 5,000 | 50 |
| **TONG** | **5,682,640** | **33,477** | **-1,127** |

#### Level 100

| Nguon | EXP/ngay | LT/ngay | MT/ngay |
|---|---|---|---|
| Tu luyen MT (6h) | 11,250,000 | 0 | -2,400 |
| PVE (100 quai) | 0 | 55,000 | 0 |
| Boss Linh Can (12) | 26,400 | 1,200 | 0 |
| NV Ngay | 4,950 | 2,255 | 5.5 |
| NV Tuan (TB) | 6,127 | 2,545 | 3.15 |
| NV Thang (TB) | 16,863 | 7,334 | 14.67 |
| Linh Can Quest | 0 | 143 | 0 |
| Dau Truong (10 tran, #3) | 21,000 | 7,200 | 72 |
| **TONG** | **11,325,340** | **75,677** | **-2,305** |

#### Level 200

| Nguon | EXP/ngay | LT/ngay | MT/ngay |
|---|---|---|---|
| Tu luyen MT (6h) | 22,500,000 | 0 | -4,800 |
| PVE (100 quai) | 0 | 105,000 | 0 |
| Boss Linh Can (12) | 52,800 | 1,200 | 0 |
| NV Ngay | 4,950 | 2,255 | 5.5 |
| NV Tuan (TB) | 6,127 | 2,545 | 3.15 |
| NV Thang (TB) | 16,863 | 7,334 | 14.67 |
| Linh Can Quest | 0 | 143 | 0 |
| Dau Truong (10 tran, #1) | 41,000 | 10,000 | 100 |
| **TONG** | **22,621,740** | **128,477** | **-4,677** |

---

## PHAN 3: TINH TOAN CHI TIET TUNG HE THONG

### 3.1 Toa Yeu Thap — Vai Tro Trong Progression

**Hien tai:** Chi cho EXP, khong cho LT hay vat pham.

**Vai Tro Moi (de xuat):**

| Tang | EXP | LT Thuong | Vat Pham |
|---|---|---|---|
| Tang le (1,2,3...) | 140 + f×42 + f²×2.2 | 50 + f×5 | Khong |
| Tang chan (2,4,6...) | Giong | Giong + 50 | 1 Cuong Hoa Thach |
| Tang 10 (10,20,30...) | Giong | Giong + 200 | 1 Da Ngau Nhien |
| Tang 50 (50,100,150) | Giong | Giong + 1,000 | 5 Da Ngau Nhien |

**Reset Hang Tuan:** Moi T2, hoa lai tang 1. Cho phep farm lai.

**Tong Thu Nhap (neu clear 150 tang):**
- EXP: ~2,200,000
- LT: ~50,000 - 80,000
- Vat Pham: ~30 Cuong Hoa + ~15 Da + 15 Da Ngau Nhien

**Thoi Gian Clear:** ~1-2 ngay (neu du suc)

### 3.2 Van Dinh Dai — Vai Tro Trong Progression

**Hien tai:** Chi Top 10 moi duoc Thuong hang ngay.

**Vai Tro Moi (de xuat):**

| Hang | Linh Thach | Ma Thach | EXP Bonus |
|---|---|---|---|
| #1 | 10,000 | 100 | 10,000 |
| #2 | 8,500 | 85 | 8,500 |
| #3 | 7,200 | 72 | 7,200 |
| #4 | 6,000 | 60 | 6,000 |
| #5 | 5,000 | 50 | 5,000 |
| #6-10 | 3,000-1,600 | 30-16 | 3,000-1,600 |
| #11-20 | 800-400 | 0 | 800-400 |
| #21-50 | 200-100 | 0 | 200-100 |
| #51-100 | 50-20 | 0 | 50-20 |
| #101+ | 10 | 0 | 10 |

**10 tran/ngay EXP:**
- Thang: 80 + enemy_lv × 8
- Thua: max(12, 30 + enemy_lv × 2)
- TB: ~50% thang => ~5 tran thang, 5 tran thua
- EXP 10 tran (Lv100): ~5 × 880 + 5 × 230 = ~5,550 EXP

### 3.3 Ham Mo — Vai Tro Trong Progression

**Hien tai:** 10 MT/lan, 30 lan/ngay. Chi cho LT.

**Vai Tro Moi (de xuat):**

| Che Do | Chi Phi | So Lan | Tong Chi Phi | Ket Qua |
|---|---|---|---|---|
| Mien phi | 0 MT | 5 lan/ngay | 0 | LT + nguyen lieu |
| Tra phi | 5 MT/lan | 25 lan/ngay | 125 MT | LT + nguyen lieu |

**Bang Roi Moi:**

| Roll | Ket Qua | LT | Vat Pham | Ty Le |
|---|---|---|---|---|
| 1-50 | Mach ma thach | 25-55 + bonus | Khong | 0.5% |
| 51-150 | Linh thach tinh | 900-1600 + bonus×2 | Khong | 1.0% |
| 151-550 | Linh thach nguyen khoi | 350-750 + bonus | Khong | 4.0% |
| 551-3550 | Linh thach tho | 80-220 + bonus | Khong | 30.0% |
| 3551-6550 | Manh linh quang | 20-80 + bonus/2 | 1 Nguyen Lieu | 30.0% |
| 6551-8500 | Nguyen lieu hiem | 0 | 1 Da element | 19.5% |
| 8501-9500 | Cuong Hoa Thach | 0 | 1-3 Cuong Hoa | 10.0% |
| 9501-10000 | Da vun | 0 | 0 | 5.0% |

**Gia Tri Ky Vong (30 lan, Lv100):**
- LT: ~3,000
- Nguyen lieu: ~10 manh + ~6 da element + ~3 Cuong Hoa
- Chi phi: 150 MT (neu tra phi het)

**Gia Tri Ky Vong (5 lan mien phi):**
- LT: ~500
- Nguyen lieu: ~2 manh + 1 da + 0.5 Cuong Hoa

---

## PHAN 4: MO HINH PROGRESSION 2 NAM

### 4.1 Nguyen Tac

```
1. EXP la nguon chinh de len cap
2. EXP den tu: Tu luyen + PVE + Boss + Nhiem Vu + Thap + Dau Truong
3. LT de: Mua sach, di chuyen, nang cap, giao dich
4. MT de: Tu luyen nhanh, gacha, ham mo
5. Nguyen lieu de: Luyen dan + Che tao
6. Moi realm yeu cau: EXP + dot pha + nhiem vu chinh tuyen + nguyen lieu
```

### 4.2 Bang Du Bao Thoi Gian

#### Free Player

| Giai Doan | Level | EXP Can | EXP/Ngay (TB) | So Ngay | Thang |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | 150,000 | 60,000 | 3 | 0.1 |
| Luyen The | 50-100 | 1,500,000 | 130,000 | 12 | 0.4 |
| Tu Khi | 100-200 | 25,000,000 | 230,000 | 109 | 3.6 |
| Kim Dan | 200-300 | 120,000,000 | 330,000 | 364 | 12.1 |
| Nguyen Anh | 300-400 | 400,000,000 | 430,000 | 930 | 31.0 |
| Hop The | 400-500 | 1,000,000,000 | 530,000 | 1,887 | 62.9 |
| **TONG** | **0-500** | **1,546,650,000** | - | **3,305** | **110** |

**Tong Free: ~3,305 ngay = 9.0 nam**

#### VIP Player (MT x2 hang ngay)

| Giai Doan | Level | EXP Can | EXP/Ngay (TB) | So Ngay | Thang |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | 150,000 | 200,000 | 1 | 0.03 |
| Luyen The | 50-100 | 1,500,000 | 2,000,000 | 1 | 0.03 |
| Tu Khi | 100-200 | 25,000,000 | 5,500,000 | 5 | 0.2 |
| Kim Dan | 200-300 | 120,000,000 | 11,500,000 | 11 | 0.4 |
| Nguyen Anh | 300-400 | 400,000,000 | 17,500,000 | 23 | 0.8 |
| Hop The | 400-500 | 1,000,000,000 | 23,000,000 | 44 | 1.5 |
| **TONG** | **0-500** | **1,546,650,000** | - | **85** | **2.8** |

**VIP qua nhanh! Can dieu chinh.**

### 4.3 Dieu Chinh De Dat Muc Tieu 2 Nam

**Van de:** VIP qua nhanh vi tu luyen MT cho qua nhieu EXP.

**Giai phap:**
1. **Giam MT multiplier:** Tu 2.5x → 1.5x
2. **Giam EXP PVE:** Tu 0 → giu nguyen (hau het quai co gexp = null)
3. **Them bottleneck:** Realm yeu cau nguyen lieu + nhiem vu, khong chi EXP
4. **Giam EXP tu luyen:** Tu ulv → ulv × 0.8 (co ban)

**Bang Dieu Chinh:**

| He Thong | Cu | Moi | Anh Huong |
|---|---|---|---|
| EXP/sec Free | ulv × 1.0 | ulv × 0.8 | -20% EXP Free |
| EXP/sec MT | ulv × 2.5 | ulv × 1.5 | -40% EXP VIP |
| EXP/sec LT | ulv × 2.0 | ulv × 1.2 | -40% EXP LT |
| VIP bonus | 1.1 | 1.1 | Giu nguyen |
| Absorb cap | 1.5 | 1.3 | -13% |
| EXP bonus cap | 1.4 | 1.3 | -7% |
| EXP toi da Free | ulv × 1.56 | ulv × 1.12 | -28% |
| EXP toi da MT | ulv × 4.29 | ulv × 2.28 | -47% |

### 4.4 Bang Du Bao Sau Dieu Chinh

#### Free Player

| Giai Doan | Level | EXP Can | EXP/Ngay (TB) | So Ngay | Thang |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | 150,000 | 45,000 | 4 | 0.1 |
| Luyen The | 50-100 | 1,500,000 | 95,000 | 16 | 0.5 |
| Tu Khi | 100-200 | 25,000,000 | 170,000 | 147 | 4.9 |
| Kim Dan | 200-300 | 120,000,000 | 250,000 | 480 | 16.0 |
| Nguyen Anh | 300-400 | 400,000,000 | 330,000 | 1,212 | 40.4 |
| Hop The | 400-500 | 1,000,000,000 | 410,000 | 2,439 | 81.3 |
| **TONG** | **0-500** | **1,546,650,000** | - | **4,298** | **143** |

**Tong Free: ~4,298 ngay = 11.8 nam** ✓

#### VIP Player (MT x1.5)

| Giai Doan | Level | EXP Can | EXP/Ngay (TB) | So Ngay | Thang |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | 150,000 | 130,000 | 2 | 0.05 |
| Luyen The | 50-100 | 1,500,000 | 1,200,000 | 2 | 0.05 |
| Tu Khi | 100-200 | 25,000,000 | 3,200,000 | 8 | 0.3 |
| Kim Dan | 200-300 | 120,000,000 | 6,500,000 | 19 | 0.6 |
| Nguyen Anh | 300-400 | 400,000,000 | 10,000,000 | 40 | 1.3 |
| Hop The | 400-500 | 1,000,000,000 | 13,500,000 | 74 | 2.5 |
| **TONG** | **0-500** | **1,546,650,000** | - | **145** | **4.8** |

**VIP qua nhanh! Can bottleneck them.**

### 4.5 He Thong Bottleneck — Bat Buoc Phai Lam

**Nguyen tac:** EXP du nhung khong the len cap neu khong:
1. Hoan thanh nhiem vu chinh tuyen
2. Co du nguyen lieu
3. Dot pha thanh cong
4. Dat mốc nhat dinh

**Bottleneck moi realm:**

| Realm | Yeu Cau Bat Buoc | Thoi Gian Them |
|---|---|---|
| Lv50 (Major) | 7 NV chinh tuyen + 50 Cuong Hoa + 20 Da Element | +5 ngay |
| Lv100 (Major) | 10 NV chinh tuyen + 500 Cuong Hoa + 100 Da + 5 Binh Anh | +15 ngay |
| Lv150 (Major) | 10 NV chinh tuyen + 2000 Cuong Hoa + 300 Da + 20 Binh Anh | +30 ngay |
| Lv200 (Major) | 10 NV chinh tuyen + 5000 Cuong Hoa + 500 Da + 50 Binh Anh | +60 ngay |
| Lv250 (Major) | 10 NV chinh tuyen + 10000 Cuong Hoa + 800 Da + 100 Binh Anh | +90 ngay |
| Lv300 (Major) | 10 NV chinh tuyen + 20000 Cuong Hoa + 1200 Da + 200 Binh Anh | +120 ngay |
| Lv350 (Major) | 10 NV chinh tuyen + 30000 Cuong Hoa + 1800 Da + 350 Binh Anh | +150 ngay |
| Lv400 (Major) | 10 NV chinh tuyen + 50000 Cuong Hoa + 2500 Da + 500 Binh Anh | +180 ngay |
| Lv450 (Major) | 10 NV chinh tuyen + 80000 Cuong Hoa + 3500 Da + 700 Binh Anh | +210 ngay |
| Lv500 (Major) | 10 NV chinh tuyen + 120000 Cuong Hoa + 5000 Da + 1000 Binh Anh | +240 ngay |

**Tong bottleneck:** +1,100 ngay = +3.6 nam

### 4.6 Bang Du Bao Cuoi Cung (Co Bottleneck)

#### VIP Player

| Giai Doan | Level | EXP Time | Bottleneck Time | Tong |
|---|---|---|---|---|
| Nhap Mon | 1-50 | 2 ngay | 5 ngay | 7 ngay |
| Luyen The | 50-100 | 2 ngay | 15 ngay | 17 ngay |
| Tu Khi | 100-200 | 8 ngay | 60 ngay | 68 ngay |
| Kim Dan | 200-300 | 19 ngay | 120 ngay | 139 ngay |
| Nguyen Anh | 300-400 | 40 ngay | 180 ngay | 220 ngay |
| Hop The | 400-500 | 74 ngay | 240 ngay | 314 ngay |
| **TONG** | **0-500** | **145 ngay** | **620 ngay** | **765 ngay** |

**Tong VIP: ~765 ngay = 2.1 nam** ✓

#### Free Player

| Giai Doan | Level | EXP Time | Bottleneck Time | Tong |
|---|---|---|---|---|
| Nhap Mon | 1-50 | 4 ngay | 10 ngay | 14 ngay |
| Luyen The | 50-100 | 16 ngay | 30 ngay | 46 ngay |
| Tu Khi | 100-200 | 147 ngay | 120 ngay | 267 ngay |
| Kim Dan | 200-300 | 480 ngay | 240 ngay | 720 ngay |
| Nguyen Anh | 300-400 | 1,212 ngay | 360 ngay | 1,572 ngay |
| Hop The | 400-500 | 2,439 ngay | 480 ngay | 2,919 ngay |
| **TONG** | **0-500** | **4,298 ngay** | **1,240 ngay** | **5,538 ngay** |

**Tong Free: ~5,538 ngay = 15.2 nam** ✓

---

## PHAN 5: TAI NGUYEN CAN THIET CHO BOTTLENECK

### 5.1 Bang Cuong Hoa Thach Can Thiet

| Realm | Cuong Hoa Can | Ngay Can Thu Thap (Free) | Ngay Can Thu Thap (VIP) |
|---|---|---|---|
| Lv50 | 50 | 3 | 2 |
| Lv100 | 500 | 10 | 5 |
| Lv150 | 2,000 | 25 | 12 |
| Lv200 | 5,000 | 50 | 25 |
| Lv250 | 10,000 | 80 | 40 |
| Lv300 | 20,000 | 130 | 65 |
| Lv350 | 30,000 | 180 | 90 |
| Lv400 | 50,000 | 250 | 125 |
| Lv450 | 80,000 | 350 | 175 |
| Lv500 | 120,000 | 500 | 250 |

**Nguon Cuong Hoa:**
- PVE drop: 5-50% ty le, ~0.2-0.5/con quai
- Boss drop: 10-30% ty le
- Nhiem vu: 2-5/NV
- Ham mo: 10% ty le, 1-3/con
- Linh Can Quest: 2-10/tuan

**Toc Do Thu Thap (Lv100):**
- PVE: ~0.3/quai × 100 quai/ngay = 30/ngay
- Boss: ~0.2/boss × 6 boss/ngay = 1.2/ngay
- NV: ~5/ngay
- **Tong: ~36/ngay**
- **500 Cuong Hoa: ~14 ngay** (khop voi bottleneck)

### 5.2 Bang Da Element Can Thiet

| Realm | Da Element Can | Ngay Can (Free) | Ngay Can (VIP) |
|---|---|---|---|
| Lv50 | 20 | 5 | 3 |
| Lv100 | 100 | 15 | 8 |
| Lv150 | 300 | 30 | 15 |
| Lv200 | 500 | 45 | 22 |
| Lv250 | 800 | 60 | 30 |
| Lv300 | 1,200 | 80 | 40 |
| Lv350 | 1,800 | 100 | 50 |
| Lv400 | 2,500 | 120 | 60 |
| Lv450 | 3,500 | 150 | 75 |
| Lv500 | 5,000 | 180 | 90 |

**Nguon Da Element:**
- Boss Linh Can drop: 20-40% ty le
- Ham mo: 19.5% ty le
- PVE Boss: 10-20% ty le

**Toc Do Thu Thap (Lv100):**
- Boss Linh Can: ~0.3/boss × 6/ngay = 1.8/ngay
- Ham mo: ~6/ngay (30 lan × 19.5%)
- PVE Boss: ~0.15/boss × 3/ngay = 0.45/ngay
- **Tong: ~8.25/ngay**
- **100 Da: ~12 ngay** (khop voi bottleneck)

### 5.3 Bang Binh Anh Tinh Thach Can Thiet

| Realm | Binh Anh Can | Ngay Can (Free) | Ngay Can (VIP) |
|---|---|---|---|
| Lv100 | 5 | 5 | 3 |
| Lv150 | 20 | 15 | 8 |
| Lv200 | 50 | 30 | 15 |
| Lv250 | 100 | 45 | 22 |
| Lv300 | 200 | 60 | 30 |
| Lv350 | 350 | 80 | 40 |
| Lv400 | 500 | 100 | 50 |
| Lv450 | 700 | 120 | 60 |
| Lv500 | 1,000 | 150 | 75 |

**Nguon Binh Anh:**
- Boss cap 50+ drop: 5-15% ty le
- Ham mo: 0% (khong co)
- Nhiem vu chinh tuyen: 3-5/NV

**Toc Do Thu Thap (Lv100):**
- Boss: ~0.1/boss × 6/ngay = 0.6/ngay
- **5 Binh Anh: ~8 ngay** (khop voi bottleneck)

---

## PHAN 6: LICH TRINH HOAT DONG NGAY

### 6.1 Free Player Ngay (Lv100)

| Thoi Gian | Hoat Dong | Thu Nhap |
|---|---|---|
| 06:00 | Nhan NV Ngay (5 NV) | EXP + LT |
| 06:01 | Tu luyen free 30 phut | 180,000 EXP |
| 06:31 | Danh quai PVE 100 lan | 55,000 LT + nguyen lieu |
| 08:00 | Dau boss Linh Can (6) | 13,200 EXP + 600 LT + da |
| 09:00 | Lam NV chinh tuyen (1-2 NV) | EXP + LT + vat pham |
| 10:00 | Luyen dan / Che tao (neu co nguyen lieu) | Vat pham |
| 11:00 | Van Dinh Dau (5 tran) | 5,500 EXP |
| 12:00 | Toa Yeu Thap (tang moi) | EXP |
| 13:00 | nghi | - |
| 14:00 | Lam NV Tuan (1-2 NV) | EXP + LT |
| 15:00 | Giao dich mua ban | - |
| 16:00 | Nang cap trang bi | - |
| 18:00 | Boss Linh Can (6) | 13,200 EXP + 600 LT |
| 20:00 | Lam NV Con Lai | EXP + LT |
| 21:00 | Nhan Thuong Dau Truong | LT |

**Tong ngay:** ~229,100 EXP + ~66,774 LT + 21 MT

### 6.2 VIP Player Ngay (Lv100)

| Thoi Gian | Hoat Dong | Thu Nhap |
|---|---|---|
| 06:00 | Nhan NV Ngay | EXP + LT + MT |
| 06:01 | Tu luyen MT 6h | 11,250,000 EXP |
| 12:00 | Danh quai PVE 100 lan | 55,000 LT + nguyen lieu |
| 13:30 | Dau boss Linh Can (12) | 26,400 EXP + 1,200 LT |
| 15:00 | Lam NV chinh tuyen | EXP + LT |
| 16:00 | Luyen dan / Che tao | Vat pham |
| 17:00 | Van Dinh Dau (10 tran) | 21,000 EXP + 7,200 LT + 72 MT |
| 18:30 | Toa Yeu Thap | EXP |
| 19:00 | Lam NV Tuan | EXP + LT |
| 20:00 | Giao dich | - |
| 21:00 | Nhan Thuong Dau Truong | LT + MT |

**Tong ngay:** ~11,325,340 EXP + ~75,677 LT + 21 MT

---

## PHAN 7: TONG KET CUOI CUNG

### 7.1 Bang Tong Hop Thay Doi

| He Thong | Hien Tai | Moi | Ly Do |
|---|---|---|---|
| EXP/sec Free | ulv × 1.0 | ulv × 0.8 | Cham progress |
| EXP/sec MT | ulv × 2.5 | ulv × 1.5 | VIP 2 nam |
| EXP/sec LT | ulv × 2.0 | ulv × 1.2 | Can bang |
| Absorb cap | 1.5 | 1.3 | Power ceiling |
| EXP bonus cap | 1.4 | 1.3 | Power ceiling |
| PVE LT | glv × 5 | glv × 5 | Giu nguyen |
| Tu luyen LT cost | 1/phut | Scale | Tieu thu LT |
| Tu luyen MT cost | 1/phut | Scale | Tieu thu MT |
| Toa Yeu Thap | Chi EXP | EXP + LT + vat pham | Thu hap hon |
| Toa Yeu Thap reset | Khong | Hang tuan | Farm nguyen lieu |
| Dau Truong Thuong | Top 10 | Top 100 | Free player co MT |
| Ham Mo mien phi | 0 lan | 5 lan/ngay | Free player dung duoc |
| Ham Mo tra phi | 10 MT | 5 MT | Rẻ hon |
| Bottleneck realm | Khong | Co (nguyen lieu) | Cham progress |

### 7.2 Du Bao Thoi Gian Cuoi Cung

| Player | Level 100 | Level 200 | Level 300 | Level 400 | Level 500 |
|---|---|---|---|---|---|
| Free | 60 ngay | 327 ngay | 1,047 ngay | 2,619 ngay | 5,538 ngay |
| VIP | 24 ngay | 92 ngay | 231 ngay | 451 ngay | 765 ngay |
| **VIP (nam)** | 0.07 | 0.25 | 0.63 | 1.24 | **2.1** |
| **Free (nam)** | 0.16 | 0.90 | 2.87 | 7.17 | **15.2** |

### 7.3 Ty Le Free:VIP

| Level | Ty Le | Ghi Chu |
|---|---|---|
| 100 | 1:0.4 | VIP nhanh hon 2.5 lan |
| 200 | 1:0.28 | VIP nhanh hon 3.5 lan |
| 300 | 1:0.22 | VIP nhanh hon 4.5 lan |
| 400 | 1:0.17 | VIP nhanh hon 5.8 lan |
| 500 | 1:0.14 | VIP nhanh hon 7.2 lan |

**Nhuan:** Free player progress CHAM hon nhieu lan, nhung van progress duoc. VIP player dat endgame trong 2.1 nam.

---

*Tai lieu nay la ban tong hop cuoi cung.*
*Luu tai: D:\XXJserver\RESOURCE_ECONOMY_COMPLETE.md*
