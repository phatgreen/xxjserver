# XXJ - TONG HOP PHAN TICH & KE HOACH PHAT TRIEN

> Tai lieu tong hop tat ca phan tich va ke hoach
> Muc dich: Doc qua truoc khi trien khai code
> Phien ban: v1.0 | Cap nhat: 2026-07-14

---

## MUC LUC

1. [Tong Quan Game](#1-tong-quan-game)
2. [Van De Can Ban](#2-van-de-can-ban)
3. [Muc Tieu Phat Trien](#3-muc-tieu-phat-trien)
4. [He Thong EXP Moi](#4-he-thong-exp-moi)
5. [He Thong Nhiem Vu](#5-he-thong-nhiem-vu)
6. [Tich Hop He Thong](#6-tich-hop-he-thong)
7. [Bang Du Bao Thoi Gian](#7-bang-du-bao-thoi-gian)
8. [Thay Doi Code Can Lam](#8-thay-doi-code-can-lam)
9. [Lich Su Phien Ban](#9-lich-su-phien-ban)

---

## 1. TONG QUAN GAME

### 1.1 Thong So Ky Thuat

| Thong So | Gia Tri |
|---|---|
| Server | PhpStudy (Nginx 1.15.11 + PHP 5.6 + MySQL 5.5) |
| Database | root / www.aae.ink |
| Ma GM | loulxgame |
| PHP Constraints | Khong ??, khong <=>, khong typed properties |
| MySQL Constraints | Khong JSON column (dung TEXT) |

### 1.2 Cau Truc Game

| He Thong | So Luong | Mo Ta |
|---|---|---|
| Canh Giong | 86 (Lv0-4300) | 86 cap do nang len |
| Linh Can | 22 loai | 22 nguyen to / phan loai |
| Cong Phap | 12 | Phuong phap tu luyen |
| Tang Tho | 150 tang | Pho ban doc lap |
| Dau Truong | PVP | Xep hang hang ngay |
| Boss | Linh Can Boss | 12 boss/ngay |
| Luyen Dan | 27 cong thuc | Che tao thuoc + dan |
| Che Tao | 16 cong thuc | Che tao trang bi |
| Trang Bi | 94 mon | Phan theo mau sac/hiem |
| Vat Pham | 30 loai | Nguyen lieu + vu khi |
| Nhiem Vu | ~272 NV | Chinh tuyen + phu tuyen + tuong tac |

---

## 2. VAN DE CAN BAN

### 2.1 Mat Can Bang Hien Tai

| Van De | Hien Trang | Muc Do |
|---|---|---|
| EXP qua nhanh | Max Lv100 trong 1-2 ngay | Nghiem trong |
| Linh Thach qua nhieu | Thu ~500K-2M/ngay, chi tieu ~360/ngay | Nghiem trong |
| Ma Thach khong can thiet | Free player van progress tot | Nghiem trong |
| Tu luyen AFK qua manh | 12h/ngay cho EXP toi da | Trung binh |
| Nhiem vu qua it | Chi 21 NV, khong tuong tac | Trung binh |
| Khong co sink cho LT | Chi dot pha tieu LT | Nghiem trong |

### 2.2 Nguyen Nhan Goc

```
1. He so nhan EXP qua cao: vip(1.2) x mode(3) x absorb(1.3) x exp(1.3) x cp(1.45) = 7.35x
2. Chi phi tu luyen co dinh: 1 LT/phut, 1 MT/phut (khong scale)
3. PVE LT qua cao: glv x 22.5 (nen la glv x 5)
4. EXP requirement qua thap: Cong thuc linear, khong du steep
5. Khong co LT sink: Chi tieu LT chi co dot pha (1 lan)
6. Free player qua de progress: Tu luyen free 60 phut du EXP cho 2-3 level
```

---

## 3. MUC TIEU PHAT TRIEN

### 3.1 Nguyen Tac Cot Loi

```
1. VIP player toi thieu 2 NAM de dat endgame
2. Free player 3-5 nam (hoac hon)
3. Moi realm = 1 giai doan gameplay rieng
4. Nhiem vu la dong luc chinh, khong phai tu luyen AFK
5. Luyen dan + Che tao la phan KHONG THEU cua progression
6. Free player van progress duoc, chi cham hon 2-2.5 lan
```

### 3.2 Triet Ly F2P

```
Free Player = NOI DUNG GAME (doi thu, ban be, giao dich)
Free Player = TIEP CAN DE, TIEN BO CHAM
Payer = TIEN BO NHANH, TIEN LOI HON, KHONG BAT BUOC
```

### 3.3 Quy Tac Vang

1. **Moi he thong deu co duong free** → nhung cham hon 3-5 lan
2. **Ma Thach mua TOC DO, khong mua SUC MANH doc quyen**
3. **Linh Thach la don vi chinh** → phai khan hiem, co bồn chua (sink)
4. **Moi realm mat toi thieu 1-2 tuan** de dat (free player)
5. **Khong co wall cung** → moi thay deu progress duoc, chi thoi gian

---

## 4. HE THONG EXP MOI

### 4.1 Cong Thuc EXP

```php
// CU:
// $umaxexp = $nextlv * ($nextlv + round($nextlv / 2)) * 10 * $rangesexp[$ncc] + $nextlv;

// MOI:
$nextlv = $ulv + 1;
$umaxexp = max(100, pow($nextlv, 2.8) * 2);

// Dot pha (moi 50 level):
if ($ulv > 0 && $ulv % 50 == 0) {
    $breakthrough_extra = pow($ulv, 2.5) * 50;
    $umaxexp += $breakthrough_extra;
}
```

### 4.2 He So Nhan EXP

```php
// MOI (giam tu 7.35x xuong):
$vip = 1.10;           // cu: 1.20
$mode_3 = 1.5;         // cu: 3.0 (giam tu 3x xuong 1.5x)
$total_absorb = min(1.3, $lc_absorb + $cp_absorb);  // cu: vo han
$total_exp_bonus = min(1.3, $lc_exp_bonus + $cp_exp_bonus);  // cu: vo han

// EXP/sec Free: ulv × 0.8 (cu: ulv × 1.0)
// EXP/sec LT:  ulv × 1.2 (cu: ulv × 2.0)
// EXP/sec MT:  ulv × 1.5 (cu: ulv × 2.5)
```

### 4.3 Chi Phi Tu Luyen

```php
// CU: 1 LT/phut, 1 MT/phut (co dinh)

// MOI (scale theo cap):
$lt_cost_per_min = max(1, ceil(pow($ulv, 1.15) / 5));
$mt_cost_per_min = max(1, ceil($ulv / 3));

// Gioi han thoi gian:
// Free: 30 phut (cu: 60)
// LT:   180 phut (cu: 360)
// MT:   360 phut (cu: 720)
```

### 4.4 Bang EXP Chi Tiet

| Level | EXP Can Len Level | Tong EXP Tich Luy | Thoi Gian VIP | Thoi Gian Free |
|---|---|---|---|---|
| 10 | 318 | 1,700 | 0.01 ngay | 0.01 ngay |
| 50 | 25,300 | 150,000 | 0.05 ngay | 0.1 ngay |
| 100 | 195,000 | 1,650,000 | 5 ngay | 10 ngay |
| 200 | 1,400,000 | 26,650,000 | 65 ngay | 130 ngay |
| 300 | 4,300,000 | 146,650,000 | 195 ngay | 390 ngay |
| 400 | 9,500,000 | 466,650,000 | 455 ngay | 910 ngay |
| 500 | 17,800,000 | 1,050,000,000 | 825 ngay | 1,650 ngay |

---

## 5. HE THONG NHIEM VU

### 5.1 Cau Truc Tong Quan

```
NHIEM VU (~272 NV)
|
+-- CHINH TUYEN (~170 NV)
|   +-- Chuong 1: Khoi Dau (Lv1-10, 7 NV)
|   +-- Chuong 2: Lam Quen (Lv10-25, 6 NV)
|   +-- Chuong 3: Tu Luyen (Lv25-50, 8 NV)
|   +-- Chuong 4-13: Cao Cap (Lv50-500, 10 NV/chuong)
|   +-- Chuong 14-20: Endgame (Lv500+, 10 NV/chuong)
|
+-- PHU TUYEN (~52 NV)
|   +-- Ngay: 5 NV + 1 bonus = 6 NV/ngay
|   +-- Tuan: 10 NV/tuan
|   +-- Thang: 5 NV/thang
|   +-- Thanh Tuu: 8 nhom x 3 cap = 24 NV
|   +-- An: 8 NV kham pha
|
+-- TUONG TAC (~50 NV)
    +-- Luyen Dan Quest Line: 15 NV
    +-- Che Tao Quest Line: 15 NV
    +-- Thu Thap Quest Line: 10 NV
    +-- Nang Cap Quest Line: 10 NV
```

### 5.2 Nhiem Vu Chinh Tuyen - Mau

#### Chuong 1: Khoi Dau (Lv1-10, ~2 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 1.1 | Huong Dan | Doi Thoai | Noi chuyen NPC | 50 EXP, 20 LT | - |
| 1.2 | Bat Dau Tu Luyen | Kill | Giet 10 quai Lv1-5 | 100 EXP, 50 LT | - |
| 1.3 | Tich Luy Kinh Nghiem | Kill | Giet 20 quai Lv5-10 | 200 EXP, 100 LT | - |
| 1.4 | Tim Nguyen Lieu | Thu Thap | 5 Thao Duoc Pho Thong | 300 EXP, 150 LT | **Luyen Dan** |
| 1.5 | Lan Dau Luyen Dan | Luyen Dan | Luyen 1 Viên Thuoc | 500 EXP, 200 LT, 2 Cuong Hoa | **Luyen Dan** |
| 1.6 | Reng Tay Che Tao | Che Tao | Che tao 1 Vu Khi | 800 EXP, 300 LT, Vu Khi Xanh | **Che Tao** |
| 1.7 | Dot Pha Dau Tien | Breakthrough | Dot pha Lv10 | 1000 EXP, 500 LT | - |

#### Chuong 3: Tu Luyen (Lv25-50, ~10 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 3.1 | Tim Nguyen Lieu Hiem | Thu Thap | 20 Thao QH + 10 Da Ngu Hanh | 2000 EXP | - |
| 3.2 | Luyen Dan Trung Binh | Luyen Dan | Luyen 5 Dan Duoc cap 5+ | 3000 EXP, 1000 LT | **Luyen Dan** |
| 3.3 | Che Tao Trang Bi Vang | Che Tao | Che tao 1 TB Vang | 4000 EXP, 1500 LT | **Che Tao** |
| 3.4 | Tich Luy Sat Thu | Kill | Giet 100 quai Lv25-40 | 5000 EXP, 2000 LT | - |
| 3.5 | Tim Boss Linh Can | Thu Thap | 5 Binh Anh Tinh Thach | 3000 EXP | **Boss** |
| 3.6 | Nang Cap Trang Bi +5 | Che Tao | Nang cap TB +5 | 6000 EXP, 2500 LT | **Che Tao** |
| 3.7 | Dau Boss Linh Can | Boss | Giet 5 Boss | 4000 EXP, 1000 LT | - |
| 3.8 | Dot Pha Level 50 | Breakthrough | Dot pha Lv50 | 8000 EXP, 5000 LT | - |

### 5.3 Nhiem Vu Phu Tuyen

#### Nhiem Vu Ngay (Daily)

| NV | Loai | Yeu Cau | EXP | LT | Tuong Tac |
|---|---|---|---|---|---|
| D1 | Tu Luyen | Tu luyen 30 phut | 500 | 200 | - |
| D2 | Kill | 30 quai bat ky | 400 | 150 | - |
| D3 | Luyen Dan | Luyen 3 dan | 600 | 300 | **Luyen Dan** |
| D4 | Che Tao | Che tao 1 TB | 500 | 250 | **Che Tao** |
| D5 | Nang Cap | Nang cap 1 TB | 400 | 200 | **Che Tao** |
| D6 | Thu Thap | 10 nguyen lieu | 300 | 100 | - |
| D7 | Boss | 1 Boss Linh Can | 700 | 400 | - |
| D8 | PVP | 3 tran thang | 500 | 300 | - |

**Bonus 5/5:** 1,000 EXP + 500 LT + 5 MT

#### Nhiem Vu Tuần (Weekly)

| NV | Yeu Cau | EXP | LT | Tuong Tac |
|---|---|---|---|---|
| W1 | Luyen 10 dan | 3,000 | 1,000 | Luyen Dan |
| W2 | Giet 100 quai | 2,500 | 800 | - |
| W3 | Che tao 3 TB | 3,000 | 1,200 | Che Tao |
| W4 | Giet 5 Boss | 3,500 | 1,500 | Boss |
| W5 | Nang cap 5 TB | 2,500 | 1,000 | Che Tao |
| W6 | Giet 200 quai | 3,000 | 1,200 | - |
| W7 | Luyen 20 dan | 4,000 | 1,500 | Luyen Dan |
| W8 | Giet 10 Boss | 4,500 | 2,000 | Boss |
| W9 | Thu thap 50 NL | 3,000 | 1,000 | - |
| W10 | Hoan thanh W1-W9 | 10,000 | 5,000, 20 MT | - |

#### Nhiem Vu Thang (Monthly)

| NV | Yeu Cau | EXP | LT | MT | Tuong Tac |
|---|---|---|---|---|---|
| M1 | Luyen 100 dan cap 5+ | 50,000 | 20,000 | 50 | Luyen Dan |
| M2 | Che tao 10 TB Vang+ | 60,000 | 25,000 | 50 | Che Tao |
| M3 | Giet 50 Boss | 80,000 | 30,000 | 50 | Boss |
| M4 | Nang cap tong +50 | 70,000 | 25,000 | 50 | Che Tao |
| M5 | Hoan thanh M1-M4 | 200,000 | 100,000 | 200 | - |

### 5.4 Nhiem Vu Tuong Tac

#### Quest Line Luyen Dan (15 NV)

| STT | Yeu Cau | EXP | LT | Unlock |
|---|---|---|---|---|
| LD1 | 10 Thao Duoc Pho Thong | 500 | 0 | Lv5 |
| LD2 | Luyen 5 Thuoc Hoi Huyet | 1,000 | 50 | LD1 |
| LD3 | 5 Da Hoa Tinh | 1,500 | 100 | LD2 |
| LD4 | Luyen 3 Dan cap 5+ | 2,500 | 200 | LD3 |
| LD5 | 10 Thao Quy Hiem | 3,000 | 300 | LD4 |
| LD6 | Luyen 5 Dan cap 8+ | 4,000 | 500 | LD5 |
| LD7 | 3 Binh Anh Tinh Thach | 5,000 | 800 | LD6 |
| LD8 | Luyen 10 Dan cap 15+ | 7,000 | 1,200 | LD7 |
| LD9 | Luyen 1 Dan Binh Anh cap 15+ | 10,000 | 2,000 | LD8 |
| LD10 | Luyen 20 Dan cap 20+ | 15,000 | 3,000 | LD9 |
| LD11 | Luyen 30 Dan cap 25+ | 20,000 | 5,000 | LD10 |
| LD12 | Luyen 50 Dan cap 30+ | 30,000 | 8,000 | LD11 |
| LD13 | Luyen 100 Dan cap 40+ | 50,000 | 15,000 | LD12 |
| LD14 | Luyen 200 Dan cap 50+ | 80,000 | 25,000 | LD13 |
| LD15 | Luyen 500 Dan cap 80+ | 150,000 | 50,000 | LD14 |

**Tong:** 379,500 EXP + 111,150 LT
**Tong so lan luyen dan can:** 954 vien (tich luy)

#### Quest Line Che Tao (15 NV)

| STT | Yeu Cau | EXP | LT | Unlock |
|---|---|---|---|---|
| CT1 | 10 Cuong Hoa Thach | 500 | 0 | Lv5 |
| CT2 | Che tao 1 Vu Khi Pho Thong | 1,000 | 50 | CT1 |
| CT3 | Nang cap Vu Khi +2 | 1,500 | 100 | CT2 |
| CT4 | Che tao 1 Giap Pho Thong | 2,500 | 200 | CT3 |
| CT5 | Nang cap tong +5 | 3,000 | 300 | CT4 |
| CT6 | Che tao 1 TB Vang | 4,000 | 500 | CT5 |
| CT7 | Nang cap 1 TB +8 | 5,000 | 800 | CT6 |
| CT8 | Che tao 5 TB Vang | 7,000 | 1,200 | CT7 |
| CT9 | Nang cap 1 TB +10 | 10,000 | 2,000 | CT8 |
| CT10 | Che tao 1 TB Tim | 15,000 | 3,000 | CT9 |
| CT11 | Nang cap 1 TB +12 | 20,000 | 5,000 | CT10 |
| CT12 | Che tao 3 TB Tim | 30,000 | 8,000 | CT11 |
| CT13 | Nang cap 1 TB +15 | 50,000 | 15,000 | CT12 |
| CT14 | Che tao 1 TB Cam | 80,000 | 25,000 | CT13 |
| CT15 | Nang cap 1 TB +18 | 150,000 | 50,000 | CT14 |

**Tong:** 379,500 EXP + 111,150 LT

#### Quest Line Thu Thap (10 NV)

| STT | Yeu Cau | EXP | LT |
|---|---|---|---|
| TT1 | 20 Thao Duoc Pho Thong | 500 | 0 |
| TT2 | 10 Da Hoa Tinh | 1,000 | 100 |
| TT3 | 5 Thao Quy Hiem | 2,000 | 200 |
| TT4 | 10 Da Thuy Tinh | 3,000 | 500 |
| TT5 | 20 Da Moc Tinh | 5,000 | 800 |
| TT6 | 30 Da Kim Tinh | 8,000 | 1,200 |
| TT7 | 5 Binh Anh Tinh Thach | 12,000 | 2,000 |
| TT8 | 50 Da Ngu Hanh | 20,000 | 5,000 |
| TT9 | 100 NL cap 30+ | 40,000 | 10,000 |
| TT10 | 200 NL cap 50+ | 80,000 | 25,000 |

**Tong:** 171,500 EXP + 44,800 LT

#### Quest Line Nang Cap (10 NV)

| STT | Yeu Cau | EXP | LT |
|---|---|---|---|
| NC1 | Nang cap 1 TB +1 | 500 | 50 |
| NC2 | Nang cap 1 TB +3 | 1,000 | 100 |
| NC3 | Nang cap 1 TB +5 | 2,000 | 200 |
| NC4 | Nang cap 1 TB +7 | 3,000 | 500 |
| NC5 | Nang cap 1 TB +8 | 4,000 | 800 |
| NC6 | Nang cap 1 TB +10 | 6,000 | 1,500 |
| NC7 | Nang cap 1 TB +12 | 10,000 | 3,000 |
| NC8 | Nang cap 1 TB +15 | 20,000 | 8,000 |
| NC9 | Nang cap 1 TB +18 | 40,000 | 20,000 |
| NC10 | Nang cap 1 TB +20 | 80,000 | 50,000 |

**Tong:** 166,500 EXP + 84,150 LT

### 5.5 Nhiem Vu Thanh Tuu (8 Nhom x 3 Cap)

| Nhom | Cap 1 | Cap 2 | Cap 3 |
|---|---|---|---|
| Sat Thu | 1,000 quai | 5,000 quai | 10,000 quai |
| Luyen Dan | 100 dan | 500 dan | 1,000 dan |
| Tho Ren | 50 TB | 200 TB | 500 TB |
| Thu Thap | 500 NL | 2,000 NL | 5,000 NL |
| Boss Hunter | 10 Boss | 50 Boss | 100 Boss |
| Chien Binh PVP | 10 tran | 500 tran | 1,000 tran |
| Tien Si | 100 gio | 500 gio | 1,000 gio |
| Dong Bo | 1 Set | 3 Set | 5 Set |

**Tong EXP:** ~3,860,000 | **Tong LT:** ~1,668,000

### 5.6 Nhiem Vu An (8 NV)

| NV | Cach Kich Hoat | EXP | LT |
|---|---|---|---|
| H1 | Luyen 10 dan lien tuc | 5,000 | 2,000 |
| H2 | Che tao 5 TB khong fail | 8,000 | 3,000 |
| H3 | Thang Boss HP < 10% | 10,000 | 5,000 |
| H4 | Thu thap du 5 Da Nguyen To | 15,000 | 8,000 |
| H5 | Nang cap +15 khong fail | 20,000 | 10,000 |
| H6 | Giet 100 quai trong 5 phut | 12,000 | 5,000 |
| H7 | Luyen dan ty le < 20% | 15,000 | 8,000 |
| H8 | Thang 5 tran PVP lien tiep | 10,000 | 5,000 |

**Tong:** 95,000 EXP + 46,000 LT

---

## 6. TICH HOP HE THONG

### 6.1 Toa Yeu Thap (150 Tang)

**Hien tai:** Chi EXP, khong LT, khong vat pham, khong reset.

**Thay doi:**
- Them LT reward: 50 + f×5 LT/tang
- Them vat pham: Cuong Hoa, Da Element theo tang
- Reset hang tuan (moi T2 hoa lai tang 1)
- Bonus tang 10/20/30: 200 LT + 1 Da
- Bonus tang 50/100/150: 1,000 LT + 5 Da

**Tong Thu Nhap (clear 150 tang):**
- EXP: ~2,200,000
- LT: ~50,000-80,000
- Vat Pham: ~30 Cuong Hoa + ~15 Da + 15 Da Ngau Nhien

### 6.2 Van Dinh Dau (10 Tran/Ngay)

**Hien tai:** Chi Top 10 moi duoc Thuong.

**Thay doi:** Mo rong phan thuong:

| Hang | LT | MT | EXP Bonus |
|---|---|---|---|
| #1 | 10,000 | 100 | 10,000 |
| #2-5 | 5,000-8,500 | 50-85 | 5,000-8,500 |
| #6-10 | 1,600-4,200 | 16-42 | 1,600-4,200 |
| #11-20 | 400-800 | 0 | 400-800 |
| #21-50 | 100-200 | 0 | 100-200 |
| #51-100 | 20-50 | 0 | 20-50 |

### 6.3 Ham Mo (30 Lan/Ngay)

**Hien tai:** 10 MT/lan, chi LT.

**Thay doi:**
- 5 lan mien phi/ngay (khong tien)
- 25 lan tra phi: 5 MT/lan (giam tu 10)
- Them vat pham: Nguyen lieu Luyen Dan + Che Tao

**Bang Roi Moi:**

| Roll | Ket Qua | LT | Vat Pham | Ty Le |
|---|---|---|---|---|
| 1-50 | Mach ma thach | 25-55 | - | 0.5% |
| 51-150 | Linh thach tinh | 900-1600 | - | 1.0% |
| 151-550 | Linh thach nguyen khoi | 350-750 | - | 4.0% |
| 551-3550 | Linh thach tho | 80-220 | - | 30.0% |
| 3551-6550 | Manh linh quang | 20-80 | 1 Nguyen Lieu | 30.0% |
| 6551-8500 | Nguyen lieu hiem | 0 | 1 Da Element | 19.5% |
| 8501-9500 | Cuong Hoa Thach | 0 | 1-3 Cuong Hoa | 10.0% |
| 9501-10000 | Da vun | 0 | 0 | 5.0% |

### 6.4 Nguyen Lieu Can Thiet

| Realm | Cuong Hoa | Da Element | Binh Anh |
|---|---|---|---|
| Lv50 | 50 | 20 | 0 |
| Lv100 | 500 | 100 | 5 |
| Lv150 | 2,000 | 300 | 20 |
| Lv200 | 5,000 | 500 | 50 |
| Lv250 | 10,000 | 800 | 100 |
| Lv300 | 20,000 | 1,200 | 200 |
| Lv350 | 30,000 | 1,800 | 350 |
| Lv400 | 50,000 | 2,500 | 500 |
| Lv450 | 80,000 | 3,500 | 700 |
| Lv500 | 120,000 | 5,000 | 1,000 |

---

## 7. BANG DU BAO THOI GIAN

### 7.1 Free Player

| Giai Doan | Level | EXP Time | Bottleneck | Tong | Tich Luy |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | 4 ngay | 10 ngay | 14 ngay | 0.04 nam |
| Luyen The | 50-100 | 16 ngay | 30 ngay | 46 ngay | 0.17 nam |
| Tu Khi | 100-200 | 147 ngay | 120 ngay | 267 ngay | 0.90 nam |
| Kim Dan | 200-300 | 480 ngay | 240 ngay | 720 ngay | 2.87 nam |
| Nguyen Anh | 300-400 | 1,212 ngay | 360 ngay | 1,572 ngay | 7.17 nam |
| Hop The | 400-500 | 2,439 ngay | 480 ngay | 2,919 ngay | 15.2 nam |

**Tong Free: 5,538 ngay = 15.2 nam**

### 7.2 VIP Player

| Giai Doan | Level | EXP Time | Bottleneck | Tong | Tich Luy |
|---|---|---|---|---|---|
| Nhap Mon | 1-50 | 2 ngay | 5 ngay | 7 ngay | 0.02 nam |
| Luyen The | 50-100 | 2 ngay | 15 ngay | 17 ngay | 0.06 nam |
| Tu Khi | 100-200 | 8 ngay | 60 ngay | 68 ngay | 0.25 nam |
| Kim Dan | 200-300 | 19 ngay | 120 ngay | 139 ngay | 0.63 nam |
| Nguyen Anh | 300-400 | 40 ngay | 180 ngay | 220 ngay | 1.24 nam |
| Hop The | 400-500 | 74 ngay | 240 ngay | 314 ngay | 2.1 nam |

**Tong VIP: 765 ngay = 2.1 nam**

### 7.3 Ty Le Free:VIP

| Level | Ty Le | Ghi Chu |
|---|---|---|
| 100 | 1:0.4 | VIP nhanh hon 2.5 lan |
| 200 | 1:0.28 | VIP nhanh hon 3.5 lan |
| 300 | 1:0.22 | VIP nhanh hon 4.5 lan |
| 400 | 1:0.17 | VIP nhanh hon 5.8 lan |
| 500 | 1:0.14 | VIP nhanh hon 7.2 lan |

---

## 8. THAY DOI CODE DA LAM

### 8.1 Hoan Thanh (Phase 1)

#### 8.1.1 EXP Formula (player.php:677) ✅

```php
// File: class/player.php (line 677)
// DA LAM: Thay EXP formula

$player->umaxexp = max(100, pow($playernextlv, 2.8) * 2);
if ((int)$player->ulv > 0 && (int)$player->ulv % 50 == 0) {
    $breakthrough_extra = pow((int)$player->ulv, 2.5) * 50;
    $player->umaxexp += $breakthrough_extra;
}
```

#### 8.1.2 Cap Absorb + Scale Chi Phi + Giam Max Time (xiulian.php) ✅

```php
// File: game/xiulian.php

// Line 23: VIP bonus 1.20 -> 1.10
$vip_bonus = ($player->vip > 0) ? 1.10 : 1.00;

// Line 35: LT cost (uncommented + scaled)
$cost_per_min = ceil(max(1, ceil(pow($player->ulv, 1.15) / 5)) * (1 - $pb_reduce));

// Line 41: MT cost (uncommented + scaled)
$cost_per_min = max(1, ceil($player->ulv / 3));

// Line 57: Cap absorb/exp bonus at 1.3
$base_exp_per_sec = round($player->ulv * $vip_bonus * $exp_multiplier
    * min(1.3, 1 + $cp_absorb / 100)
    * min(1.3, 1 + $lc_absorb / 100)
    * min(1.3, 1 + $lc_exp_bonus / 100), 4);

// Line 66-74: Giam max time
// Free: 30 phut (cu: 60)
// LT: 180 phut (cu: 360)
// MT: 360 phut (cu: 720)
```

#### 8.1.3 Giam PVE LT (pve.php:250) ✅

```php
// File: game/pve.php (line 250)
// CU: $yxb = round($guaiwu->glv*22.5) + mt_rand(1,200);
// DA LAM:
$yxb = round($guaiwu->glv*5) + mt_rand(1,200);
```

### 8.2 Can Lam Tiep (Phase 2 - Chua Lam)

#### 8.2.1 Toa Yeu Thap (toayeuthap.php)

```php
// Them LT reward
$lt_reward = 50 + ($floor * 5);

// Them vat pham reward
if ($floor % 10 == 0) {
    // Tang 10, 20, 30...: 200 LT + 1 Da Element
    $lt_reward += 200;
    // Drop 1 Da Element
} elseif ($floor % 50 == 0) {
    // Tang 50, 100, 150: 1000 LT + 5 Da Element
    $lt_reward += 1000;
    // Drop 5 Da Element
} elseif ($floor % 2 == 0) {
    // Tang chan: +50 LT + 1 Cuong Hoa
    $lt_reward += 50;
    // Drop 1 Cuong Hoa
}

// Reset hang tuan (T2)
if ($day_of_week == 2 && !$weekly_reset_done) {
    // Hoa lai tang 1
    $floor = 1;
}
```

#### 8.2.2 Van Dinh Dau Thuong (vandinhdai.php)

```php
// Them Thuong cho #11-100
function vdd_daily_reward_table() {
    $table = array();
    // #1-10: Giu nguyen
    $table[1] = array('yxb' => 10000, 'czb' => 100);
    $table[2] = array('yxb' => 8500, 'czb' => 85);
    // ... giu nguyen ...

    // #11-20: Mo rong
    for ($i = 11; $i <= 20; $i++) {
        $table[$i] = array(
            'yxb' => 800 - (($i - 11) * 40),  // 800 -> 400
            'czb' => 0
        );
    }

    // #21-50
    for ($i = 21; $i <= 50; $i++) {
        $table[$i] = array(
            'yxb' => 200 - (($i - 21) * 3),  // 200 -> 100
            'czb' => 0
        );
    }

    // #51-100
    for ($i = 51; $i <= 100; $i++) {
        $table[$i] = array(
            'yxb' => 50 - (($i - 51) * 0.6),  // 50 -> 20
            'czb' => 0
        );
    }

    return $table;
}
```

#### 8.2.3 Ham Mo (hammo.php)

```php
// 5 lan mien phi/ngay
$free_digs = 5;
$paid_dig_cost = 5; // giam tu 10

// Them vat pham reward
function get_mine_loot($level) {
    $lvBonus = min(20, floor(sqrt($level)));
    $roll = mt_rand(1, 10000);

    if ($roll <= 50) {
        return array('yxb' => mt_rand(25,55) + $lvBonus, 'czb' => 1);
    } elseif ($roll <= 150) {
        return array('yxb' => mt_rand(900,1600) + $lvBonus*2);
    } elseif ($roll <= 550) {
        return array('yxb' => mt_rand(350,750) + $lvBonus);
    } elseif ($roll <= 3550) {
        return array('yxb' => mt_rand(80,220) + $lvBonus);
    } elseif ($roll <= 6550) {
        // Manh linh quang + 1 Nguyen Lieu
        return array('yxb' => mt_rand(20,80) + floor($lvBonus/2), 'item' => 'nguyen_lieu');
    } elseif ($roll <= 8500) {
        // Nguyen lieu hiem + 1 Da Element
        return array('item' => 'da_element');
    } elseif ($roll <= 9500) {
        // Cuong Hoa Thach
        return array('item' => 'cuong_hoa', 'count' => mt_rand(1,3));
    } else {
        return array(); // Da vun
    }
}
```

### 8.3 Uu Tien Thap (Phase 3)

#### 8.3.1 He Thong Nhiem Vu Moi

```php
// File: class/function/nhiemvu.php (MOI)

// DB Tables:
// daily_quests (uid, quest_id, progress, target, reward_exp, reward_lt, reward_mt, reset_date, status)
// weekly_quests (uid, quest_id, progress, target, reward_exp, reward_lt, reward_mt, week_number, status)
// monthly_quests (uid, quest_id, progress, target, reward_exp, reward_lt, reward_mt, month, status)
// achievements (uid, achievement_id, progress, target, claimed)
// hidden_quests (uid, quest_id, discovered, completed)

// Daily reset
function daily_quest_reset($uid) { ... }

// Weekly reset
function weekly_quest_reset($uid) { ... }

// Monthly reset
function monthly_quest_reset($uid) { ... }

// Check progress
function check_quest_progress($uid, $quest_id, $action, $amount) { ... }
```

#### 8.3.2 Config JSON Moi

Can tao cac file config:
- `class/config/daily_quests.json`
- `class/config/weekly_quests.json`
- `class/config/monthly_quests.json`
- `class/config/achievements.json`
- `class/config/hidden_quests.json`
- `class/config/main_quest_config.json`

### 8.5 Tong Hop Thay Doi

| # | File | Thay Doi | Trang Thai |
|---|---|---|---|---|
| 1 | player.php | EXP formula moi | ✅ Da lam |
| 2 | xiulian.php | Cap absorb, scale cost, giam time, giam VIP bonus | ✅ Da lam |
| 3 | pve.php | Giam PVE LT 22.5 -> 5 | ✅ Da lam |
| 4 | toayeuthap.php | Them LT + vat pham + weekly reset + auto-skip + popup thuong tang | ✅ Da lam |
| 5 | vandinhdai.php | Mo rong Thuong #11-100 | ✅ Da lam |
| 6 | hammo.php | 5 free digs + giam gia 5MT + them item drop + modal ti le mo | ✅ Da lam |
| 7 | nhiemvu.php (MOI) | He thong NV moi | ✅ Da lam |
| 8 | 6 config JSON | Cau hinh NV | ✅ Da lam |
| 9 | mystyle.css | Them .tyt-btn.auto, modal CSS chung, ribbon hover, hm-layout block | ✅ Da lam |
| 10 | game.php | Them route cmd=nhiemvu | ✅ Da lam |

---

## 9. LICH SU PHIEN BAN

| Phien Ban | Ngay | Noi Dung |
|---|---|---|---|
| v1.0 | 2026-07-14 | Phan tich ban dau (GAME_BALANCE_ANALYSIS.md) |
| v1.1 | 2026-07-14 | Proposal 1 - Can bang kinh te (ECONOMY_BALANCE_PROPOSAL.md) |
| v1.2 | 2026-07-14 | Proposal 2 - Progression dai han (PROGRESSION_LONG_TERM.md) |
| v2.0 | 2026-07-14 | Proposal 3 - Chi tiet 272 NV (PROGRESSION_DETAILED.md) |
| v2.1 | 2026-07-14 | Tong hop tai nguyen (RESOURCE_ECONOMY_COMPLETE.md) |
| v3.0 | 2026-07-14 | Docs note tong hop (file nay) |
| v3.1 | 2026-07-20 | **Code changes** - player.php, xiulian.php, pve.php, hammo.php, vandinhdai.php, toayeuthap.php |
| v3.2 | 2026-07-20 | **Quest system** - nhiemvu.php + 6 config JSON + DB tables |
| v3.3 | 2026-07-20 | **UI tweaks** - hammo modal popup, bo grid, fix layout |

---

## 10. DANH SACH FILE

| File | Mo Ta | Trang Thai |
|---|---|---|---|
| GAME_BALANCE_ANALYSIS.md | Phan tich can ban | Da hoan thanh |
| ECONOMY_BALANCE_PROPOSAL.md | Proposal 1 | Da hoan thanh |
| PROGRESSION_LONG_TERM.md | Proposal 2 | Da hoan thanh |
| PROGRESSION_DETAILED.md | Proposal 3 - Chi tiet | Da hoan thanh |
| RESOURCE_ECONOMY_COMPLETE.md | Tong hop tai nguyen | Da hoan thanh |
| **DOCS_NOTE.md** | **Docs note tong hop** | **File nay** |
| class/player.php | EXP formula (pow 2.8 + breakthrough) | ✅ Da sua |
| game/xiulian.php | VIP 1.10, cap absorb 1.3, scale cost, giam time | ✅ Da sua |
| game/pve.php | PVE LT glv*5 | ✅ Da sua |
| game/toayeuthap.php | Them LT 50+tang*5, vat pham tang chan/x10/x50, weekly reset, auto-skip 80% | ✅ Da sua |
| game/vandinhdai.php + reward function | Mo rong thuong #11-100 (LT giam dan, khong MT) | ✅ Da sua |
| game/hammo.php | 5 free digs, giam 5MT, them Thao Duoc + Cuong Hoa + Da Nguyen To + modal ti le mo | ✅ Da sua |
| game/hammo.php (layout) | Bo box ti le ben phai, .hm-layout block full width | ✅ Da sua |
| class/function/nhiemvu.php (MOI) | Backend xu ly NV ngay/tuan/thang/thanh tuu/an/chinh tuyen | ✅ Da tao |
| game/nhiemvu.php (MOI) | Giao dien quest hub 6 tab | ✅ Da tao |
| game.php | Them route cmd=nhiemvu | ✅ Da sua |
| class/config/daily_quests.json (MOI) | 8 loai NV ngay, chon 5 | ✅ Da tao |
| class/config/weekly_quests.json (MOI) | 10 NV tuan | ✅ Da tao |
| class/config/monthly_quests.json (MOI) | 5 NV thang | ✅ Da tao |
| class/config/achievements.json (MOI) | 10 bo thanh tuu x 3 bac | ✅ Da tao |
| class/config/hidden_quests.json (MOI) | 8 NV an | ✅ Da tao |
| class/config/nhiemvu_main_config.json (MOI) | 3 chuong NV chinh tuyen | ✅ Da tao |
| css/mystyle.css | Them .tyt-btn.auto, modal CSS (.vdd-reward-overlay, .vdd-reward-popup...), ribbon hover, hm-layout block | ✅ Da sua |

---

*Tai lieu nay la ban tong hop de doc truoc khi trien khai code.*
*Doc xong bao toi de bat dau implementation.*

## DB Migration

```sql
-- toayeuthap: them cot last_reset_week
ALTER TABLE player_toayeuthap ADD COLUMN last_reset_week INT NOT NULL DEFAULT 0 AFTER wins;

-- nhiemvu (quest system): 6 tables moi
CREATE TABLE IF NOT EXISTS player_nhiemvu_daily (
  sid varchar(64) NOT NULL,
  quest_id varchar(10) NOT NULL,
  reset_date date NOT NULL,
  progress int(11) NOT NULL DEFAULT 0,
  completed tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (sid, quest_id, reset_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_nhiemvu_weekly (
  sid varchar(64) NOT NULL,
  quest_id varchar(10) NOT NULL,
  week_number int(11) NOT NULL,
  progress int(11) NOT NULL DEFAULT 0,
  completed tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (sid, quest_id, week_number)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_nhiemvu_monthly (
  sid varchar(64) NOT NULL,
  quest_id varchar(10) NOT NULL,
  month varchar(7) NOT NULL,
  progress int(11) NOT NULL DEFAULT 0,
  completed tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (sid, quest_id, month)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_nhiemvu_achievements (
  sid varchar(64) NOT NULL,
  achievement_id varchar(20) NOT NULL,
  progress int(11) NOT NULL DEFAULT 0,
  claimed tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (sid, achievement_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_nhiemvu_hidden (
  sid varchar(64) NOT NULL,
  quest_id varchar(10) NOT NULL,
  discovered tinyint(1) NOT NULL DEFAULT 0,
  completed tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (sid, quest_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_nhiemvu_main (
  sid varchar(64) NOT NULL,
  quest_id varchar(10) NOT NULL,
  status tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (sid, quest_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```
