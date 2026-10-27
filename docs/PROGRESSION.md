# CHI TIET MO HINH PROGRESSION DAI HAN & HE THONG NHIEM VUT

> Tai lieu nay la phien ban day du, chi tiet cua PROGRESSION_LONG_TERM.md
> Muc dich: Doc xong co the trien khai code ngay
> Luu y: Khong dung chu Trung Quoc, chi dung tieng Viet

---

## PHAN 1: TONG QUAN MO HINH PROGRESSION

### 1.1 Nguyen Tac Cot Loi

```
1. VIP player toi thieu 2 NAM de dat endgame
2. Free player 3-5 NAM
3. Moi realm = 1 giai doan gameplay rieng
4. Nhiem vu la dong luc chinh, khong phai tu luyen AFK
5. Luyen dan + Che tao la phan KHONG THEU cua progression
6. Free player van progress duoc, chi cham hon 2-2.5 lan
```

### 1.2 Bang Du Bao Thoi Gian Tong Hop

| Giai Doan | Level | Realm | EXP/Level (TB) | Tong EXP Can | VIP (ngay) | Free (ngay) |
|---|---|---|---|---|---|---|
| Nhap Mon | 1-50 | Nhap Mon - Ngu Khi | 3,000 | 150,000 | 1 | 2 |
| Luyen The | 50-100 | Luyen The - Nguyen Anh | 30,000 | 1,500,000 | 4 | 8 |
| Tu Khi | 100-200 | Tu Khi | 250,000 | 25,000,000 | 60 | 120 |
| Kim Dan | 200-300 | Kim Dan | 1,200,000 | 120,000,000 | 130 | 260 |
| Nguyen Anh | 300-400 | Nguyen Anh | 4,000,000 | 400,000,000 | 270 | 540 |
| Hop The | 400-500 | Hop The - Hop Dao | 10,000,000 | 1,000,000,000 | 360 | 720 |
| Dai Thua | 500-700 | Luyen Hu - Dai Thua | 30,000,000 | 6,000,000,000 | endgame | endgame |
| Do Kiep | 700-1000 | Do Kiep - Thanh Ton | 100,000,000 | 30,000,000,000 | endgame | endgame |

**Tong thoi gian VIP (Lv0 -> Lv500):** ~825 ngay = **2.3 nam**
**Tong thoi gian Free (Lv0 -> Lv500):** ~1650 ngay = **4.5 nam**

### 1.3 Cong Thuc EXP Moi

```php
// ============================================================
// EXP CAN DE LEN CAP N (trong realm hien tai)
// ============================================================
// Cu (hien tai):
//   $umaxexp = $nextlv * ($nextlv + round($nextlv / 2)) * 10 * $rangesexp[$ncc] + $nextlv;
//
// Moi:
$nextlv = $ulv + 1;
$umaxexp = max(100, pow($nextlv, 2.8) * 2);

// ============================================================
// BREAKTHROUGH (moi 50 level) can them EXP
// ============================================================
// Khi ulv % 50 == 0 va ulv > 0:
$breakthrough_extra = pow($ulv, 2.5) * 50;
// Tong EXP can = $umaxexp + $breakthrough_extra;
```

### 1.4 Bang EXP Chi Tiet Tung Level

| Level | EXP Can Len Level | Tong EXP Tich Luy | Thoi Gian VIP (ngay) | Thoi Gian Free (ngay) |
|---|---|---|---|---|
| 10 | 318 | 1,700 | 0.01 | 0.01 |
| 20 | 1,860 | 15,000 | 0.05 | 0.1 |
| 50 | 25,300 | 150,000 | 1 | 2 |
| 75 | 82,000 | 680,000 | 2.5 | 5 |
| 100 | 195,000 | 1,650,000 | 5 | 10 |
| 150 | 630,000 | 8,500,000 | 25 | 50 |
| 200 | 1,400,000 | 26,650,000 | 65 | 130 |
| 250 | 2,600,000 | 70,000,000 | 120 | 240 |
| 300 | 4,300,000 | 146,650,000 | 195 | 390 |
| 350 | 6,600,000 | 280,000,000 | 325 | 650 |
| 400 | 9,500,000 | 466,650,000 | 455 | 910 |
| 450 | 13,200,000 | 720,000,000 | 625 | 1250 |
| 500 | 17,800,000 | 1,050,000,000 | 825 | 1650 |

---

## PHAN 2: HE THONG NHIEM VU CHI TIET

### 2.1 Cau Truc Tong Quan

```
NHIEM VU
|
+-- CHINH TUYEN (Main Quest Line)
|   +-- Chuong 1: Khoi Dau (Lv1-10, 7 NV)
|   +-- Chuong 2: Lam Quen (Lv10-25, 6 NV)
|   +-- Chuong 3: Tu Luyen (Lv25-50, 8 NV)
|   +-- Chuong 4: Trung Cap 1 (Lv50-75, 10 NV)
|   +-- Chuong 5: Trung Cap 2 (Lv75-100, 10 NV)
|   +-- Chuong 6: Cao Cap 1 (Lv100-150, 10 NV)
|   +-- Chuong 7: Cao Cap 2 (Lv150-200, 10 NV)
|   +-- Chuong 8: Tien Ki (Lv200-250, 10 NV)
|   +-- Chuong 9: Nguyen Anh 1 (Lv250-300, 10 NV)
|   +-- Chuong 10: Nguyen Anh 2 (Lv300-350, 10 NV)
|   +-- Chuong 11: Nguyen Anh 3 (Lv350-400, 10 NV)
|   +-- Chuong 12: Hop The 1 (Lv400-450, 10 NV)
|   +-- Chuong 13: Hop The 2 (Lv450-500, 10 NV)
|   +-- Chuong 14-20: Dai Thua+ (Lv500+, 10 NV moi chuong)
|
+-- PHU TUYEN (Side Quests)
|   +-- Nhiem Vu Ngay (Daily) - 5 NV/ngay, chon tu 8 loai
|   +-- Nhiem Vu Tuan (Weekly) - 10 NV/tuan
|   +-- Nhiem Vu Thang (Monthly) - 5 NV/thang
|   +-- Nhiem Vu Thanh Tuu (Achievement) - 8 nhom, 3 cap
|   +-- Nhiem Vu An (Hidden) - 8 NV kham pha
|
+-- NHIEM VU TUONG TAC (Interaction Quests)
    +-- Quest Line Luyen Dan (15 NV)
    +-- Quest Line Che Tao (15 NV)
    +-- Quest Line Thu Thap Nguyen Lieu (10 NV)
    +-- Quest Line Nang Cap Trang Bi (10 NV)
```

**Tong so Nhiem Vu:**
- Chinh tuyen: ~170 NV
- Phu tuyen: 5 (ngay) + 10 (tuan) + 5 (thang) + 24 (thanh tuu) + 8 (an) = 52 NV
- Tuong tac: 50 NV
- **Tong: ~272 Nhiem Vu**

---

## PHAN 3: CHI TIET CHUONG CHINH TUYEN

### Chuong 1: Khoi Dau (Lv1-10, ~2 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 1.1 | Huong Dan | Doi Thoai | Noi chuyen NPC Thuc Tap Sinh | 50 EXP, 20 LT | - |
| 1.2 | Bat Dau Tu Luyen | Kill | Giet 10 quai cap 1-5 | 100 EXP, 50 LT | - |
| 1.3 | Tich Luy Kinh Nghiem | Kill | Giet 20 quai cap 5-10 | 200 EXP, 100 LT | - |
| 1.4 | Tim Nguyen Lieu | Thu Thap | Thu thap 5 Thao Duoc Pho Thong | 300 EXP, 150 LT | Luyen Dan |
| 1.5 | Lan Dau Luyen Dan | Luyen Dan | Luyen thanh cong 1 Viên Thuoc Hoi Huyet | 500 EXP, 200 LT, 2 Cuong Hoa Thach | Luyen Dan |
| 1.6 | Reng Tay Che Tao | Che Tao | Che tao 1 Vu Khi Pho Thong | 800 EXP, 300 LT, Vu Khi Xanh Lv5 | Che Tao |
| 1.7 | Dot Pha Dau Tien | Breakthrough | Dot pha den Lv10 | 1000 EXP, 500 LT | - |

**Tong thuong Chuong 1:** 2950 EXP, 1320 LT
**Thoi gian:** ~2 ngay
**Yeu cau tuong tac:** Luyen dan 1 lan, Che tao 1 lan

---

### Chuong 2: Lam Quen (Lv10-25, ~5 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 2.1 | San Sang Vat Pham | Thu Thap | 10 Thao Duoc Pho Thong + 5 Da Hoa Tinh | 500 EXP | - |
| 2.2 | Luyen Dan Co Ban | Luyen Dan | Luyen 3 Viên Thuoc Hoi Huyet | 800 EXP, 200 LT | Luyen Dan |
| 2.3 | Tich Luy Sat Thu | Kill | Giet 50 quai cap 10-20 | 1000 EXP, 300 LT | - |
| 2.4 | Thu Thap Cuong Hoa | Thu Thap | 3 Cuong Hoa Thach | 600 EXP | - |
| 2.5 | Nang Cap Vu Khi | Che Tao | Nang cap Vu Khi len +2 | 1200 EXP, 500 LT | Che Tao |
| 2.6 | Dot Pha Level 25 | Breakthrough | Dot pha den Lv25 | 1500 EXP, 800 LT | - |

**Tong thuong Chuong 2:** 5600 EXP, 1800 LT
**Thoi gian:** ~5 ngay
**Yeu cau tuong tac:** Luyen dan 3 lan, Nang cap 1 lan

---

### Chuong 3: Tu Luyen (Lv25-50, ~10 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 3.1 | Tim Nguyen Lieu Hiem | Thu Thap | 20 Thao Duoc Quy Hiem + 10 Da Ngu Hanh | 2000 EXP | - |
| 3.2 | Luyen Dan Trung Binh | Luyen Dan | Luyen 5 Dan Duoc cap 5+ | 3000 EXP, 1000 LT | Luyen Dan |
| 3.3 | Che Tao Trang Bi Vang | Che Tao | Che tao 1 Trang Bi Mau Vang | 4000 EXP, 1500 LT | Che Tao |
| 3.4 | Tich Luy Sat Thu 2 | Kill | Giet 100 quai cap 25-40 | 5000 EXP, 2000 LT | - |
| 3.5 | Tim Boss Linh Can | Thu Thap | 5 Binh Anh Tinh Thach (Boss drop) | 3000 EXP | Boss |
| 3.6 | Nang Cap Trang Bi +5 | Che Tao | Nang cap Trang Bi den +5 (tong hop) | 6000 EXP, 2500 LT | Che Tao |
| 3.7 | Dau Boss Linh Can | Boss | Giet 5 Boss Linh Can | 4000 EXP, 1000 LT | - |
| 3.8 | Dot Pha Level 50 | Breakthrough | Dot pha den Lv50 (Major) | 8000 EXP, 5000 LT | - |

**Tong thuong Chuong 3:** 35,000 EXP, 12,000 LT
**Thoi gian:** ~10 ngay
**Yeu cau tuong tac:** Luyen dan 5 lan, Che tao 1 lan, Nang cap 1 lan, Boss 5 lan

---

### Chuong 4: Trung Cap 1 (Lv50-75, ~15 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 4.1 | Tim Nguyen Lieu Cap 2 | Thu Thap | 30 Thao Duoc Quy Hiem + 15 Da Hoa Tinh | 5000 EXP | - |
| 4.2 | Luyen Dan Cap 8 | Luyen Dan | Luyen 5 Dan Duoc cap 8+ | 6000 EXP, 2000 LT | Luyen Dan |
| 4.3 | Che Tao Trang Bi Vang 2 | Che Tao | Che tao 2 Trang Bi Mau Vang | 7000 EXP, 3000 LT | Che Tao |
| 4.4 | Nang Cap +6 | Che Tao | Nang cap 1 Trang Bi den +6 | 5000 EXP, 2000 LT | Che Tao |
| 4.5 | Tich Luy Sat Thu 3 | Kill | Giet 150 quai cap 50-70 | 8000 EXP, 3000 LT | - |
| 4.6 | Dau Boss 2 | Boss | Giet 10 Boss Linh Can | 7000 EXP, 3000 LT | - |
| 4.7 | Luyen Dan Cap 10 | Luyen Dan | Luyen 8 Dan Duoc cap 10+ | 8000 EXP, 3000 LT | Luyen Dan |
| 4.8 | Thu Thap Da Thuy Tinh | Thu Thap | 10 Da Thuy Tinh | 6000 EXP, 2500 LT | - |
| 4.9 | Che Tao Set 1 | Che Tao | Hoan thanh 1 phan cua Set Trang Bi | 10000 EXP, 4000 LT | Che Tao |
| 4.10 | Dot Pha Level 75 | Breakthrough | Dot pha den Lv75 | 12000 EXP, 5000 LT | - |

**Tong thuong Chuong 4:** 74,000 EXP, 27,500 LT
**Thoi gian:** ~15 ngay
**Yeu cau tuong tac:** Luyen dan 13 lan, Che tao 4 lan, Boss 10 lan

---

### Chuong 5: Trung Cap 2 (Lv75-100, ~20 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 5.1 | Nguyen Lieu Cao Cap | Thu Thap | 20 Thao Duoc Quy Hiem + 15 Da Moc Tinh | 10000 EXP | - |
| 5.2 | Luyen Dan Cap 15 | Luyen Dan | Luyen 10 Dan Duoc cap 15+ | 12000 EXP, 4000 LT | Luyen Dan |
| 5.3 | Che Tao 3 Vang | Che Tao | Che tao 3 Trang Bi Mau Vang | 12000 EXP, 5000 LT | Che Tao |
| 5.4 | Nang Cap +8 | Che Tao | Nang cap Trang Bi den +8 | 10000 EXP, 4000 LT | Che Tao |
| 5.5 | Tich Luy Sat Thu 4 | Kill | Giet 200 quai cap 75-95 | 15000 EXP, 5000 LT | - |
| 5.6 | Boss Trung Cap | Boss | Giet 15 Boss Linh Can | 12000 EXP, 5000 LT | - |
| 5.7 | Luyen Dan Cap 20 | Luyen Dan | Luyen 15 Dan Duoc cap 20+ | 15000 EXP, 6000 LT | Luyen Dan |
| 5.8 | Tim Binh Anh | Thu Thap | 8 Binh Anh Tinh Thach | 10000 EXP, 4000 LT | Boss |
| 5.9 | Che Tao Set 2 | Che Tao | Hoan thanh 2 phan cua Set Trang Bi | 18000 EXP, 7000 LT | Che Tao |
| 5.10 | Dot Pha Level 100 | Breakthrough | Dot pha den Lv100 (Major) | 20000 EXP, 8000 LT | - |

**Tong thuong Chuong 5:** 122,000 EXP, 48,000 LT
**Thoi gian:** ~20 ngay
**Yeu cau tuong tac:** Luyen dan 25 lan, Che tao 7 lan, Boss 15 lan

---

### Chuong 6: Cao Cap 1 (Lv100-150, ~30 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 6.1 | Nguyen Lieu Hiem | Thu Thap | 30 Da Kim Tinh + 20 Da Thuy Tinh | 20000 EXP | - |
| 6.2 | Luyen Dan Cap 25 | Luyen Dan | Luyen 20 Dan Duoc cap 25+ | 25000 EXP, 8000 LT | Luyen Dan |
| 6.3 | Che Tao Trang Bi Tim | Che Tao | Che tao 1 Trang Bi Mau Tim | 25000 EXP, 10000 LT | Che Tao |
| 6.4 | Nang Cap +10 | Che Tao | Nang cap Trang Bi den +10 | 20000 EXP, 8000 LT | Che Tao |
| 6.5 | Tich Luy Sat Thu 5 | Kill | Giet 300 quai cap 100-140 | 30000 EXP, 10000 LT | - |
| 6.6 | Boss Cao Cap | Boss | Giet 20 Boss Linh Can | 25000 EXP, 10000 LT | - |
| 6.7 | Luyen Dan Cap 30 | Luyen Dan | Luyen 25 Dan Duoc cap 30+ | 30000 EXP, 12000 LT | Luyen Dan |
| 6.8 | Hoan Thanh Set Linh | Che Tao | Hoan thanh Set "Linh" (5 phan) | 40000 EXP, 15000 LT | Che Tao |
| 6.9 | Nang Cap Linh Can | Linh Can | Nang cap Linh Can len cap 3 | 25000 EXP, 10000 LT | Linh Can |
| 6.10 | Dot Pha Level 150 | Breakthrough | Dot pha den Lv150 | 35000 EXP, 15000 LT | - |

**Tong thuong Chuong 6:** 250,000 EXP, 98,000 LT
**Thoi gian:** ~30 ngay

---

### Chuong 7: Cao Cap 2 (Lv150-200, ~30 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 7.1 | Nguyen Lieu Rare | Thu Thap | 40 Da Moc Tinh + 25 Da Hoa Tinh | 30000 EXP | - |
| 7.2 | Luyen Dan Cap 35 | Luyen Dan | Luyen 30 Dan Duoc cap 35+ | 35000 EXP, 12000 LT | Luyen Dan |
| 7.3 | Che Tao Tim 2 | Che Tao | Che tao 2 Trang Bi Mau Tim | 35000 EXP, 14000 LT | Che Tao |
| 7.4 | Nang Cap +12 | Che Tao | Nang cap Trang Bi den +12 | 30000 EXP, 12000 LT | Che Tao |
| 7.5 | Tich Luy Sat Thu 6 | Kill | Giet 400 quai cap 150-190 | 40000 EXP, 15000 LT | - |
| 7.6 | Boss Hiem | Boss | Giet 25 Boss Linh Can | 35000 EXP, 14000 LT | - |
| 7.7 | Luyen Dan Cap 40 | Luyen Dan | Luyen 35 Dan Duoc cap 40+ | 40000 EXP, 16000 LT | Luyen Dan |
| 7.8 | Nang Cap +15 | Che Tao | Nang cap Trang Bi den +15 | 35000 EXP, 14000 LT | Che Tao |
| 7.9 | Set 2 Hoan Thanh | Che Tao | Hoan thanh Set thu 2 | 50000 EXP, 20000 LT | Che Tao |
| 7.10 | Dot Pha Level 200 | Breakthrough | Dot pha den Lv200 (Major) | 45000 EXP, 20000 LT | - |

**Tong thuong Chuong 7:** 340,000 EXP, 137,000 LT

---

### Chuong 8: Tien Ki (Lv200-250, ~30 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 8.1 | Nguyen Lieu Cao Cap | Thu Thap | 50 Da Thuy Tinh + 30 Binh Anh | 50000 EXP | - |
| 8.2 | Luyen Dan Cap 45 | Luyen Dan | Luyen 40 Dan Duoc cap 45+ | 50000 EXP, 18000 LT | Luyen Dan |
| 8.3 | Che Tao Cam | Che Tao | Che tao 1 Trang Bi Mau Cam | 50000 EXP, 20000 LT | Che Tao |
| 8.4 | Nang Cap +16 | Che Tao | Nang cap Trang Bi den +16 | 45000 EXP, 18000 LT | Che Tao |
| 8.5 | Tich Luy Sat Thu 7 | Kill | Giet 500 quai cap 200-240 | 60000 EXP, 25000 LT | - |
| 8.6 | Boss Tien Ki | Boss | Giet 30 Boss | 55000 EXP, 22000 LT | - |
| 8.7 | Luyen Dan Cap 50 | Luyen Dan | Luyen 50 Dan Duoc cap 50+ | 60000 EXP, 25000 LT | Luyen Dan |
| 8.8 | Nang Cap +18 | Che Tao | Nang cap Trang Bi den +18 | 50000 EXP, 20000 LT | Che Tao |
| 8.9 | Linh Can Cap 5 | Linh Can | Nang cap Linh Can len cap 5 | 50000 EXP, 20000 LT | Linh Can |
| 8.10 | Dot Pha Level 250 | Breakthrough | Dot pha den Lv250 | 60000 EXP, 25000 LT | - |

**Tong thuong Chuong 8:** 480,000 EXP, 193,000 LT

---

### Chuong 9: Nguyen Anh 1 (Lv250-300, ~35 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 9.1 | Nguyen Lieu Epic | Thu Thap | 60 Da Kim Tinh + 40 Da Hoa Tinh | 70000 EXP | - |
| 9.2 | Luyen Dan Cap 55 | Luyen Dan | Luyen 60 Dan Duoc cap 55+ | 70000 EXP, 25000 LT | Luyen Dan |
| 9.3 | Che Tao Cam 2 | Che Tao | Che tao 2 Trang Bi Mau Cam | 70000 EXP, 28000 LT | Che Tao |
| 9.4 | Nang Cap +19 | Che Tao | Nang cap Trang Bi den +19 | 60000 EXP, 24000 LT | Che Tao |
| 9.5 | Tich Luy Sat Thu 8 | Kill | Giet 600 quai cap 250-290 | 80000 EXP, 32000 LT | - |
| 9.6 | Boss Nguyen Anh | Boss | Giet 35 Boss | 70000 EXP, 28000 LT | - |
| 9.7 | Luyen Dan Cap 60 | Luyen Dan | Luyen 70 Dan Duoc cap 60+ | 80000 EXP, 32000 LT | Luyen Dan |
| 9.8 | Nang Cap +20 | Che Tao | Nang cap Trang Bi den +20 | 70000 EXP, 28000 LT | Che Tao |
| 9.9 | Set 3 Hoan Thanh | Che Tao | Hoan thanh Set thu 3 | 90000 EXP, 36000 LT | Che Tao |
| 9.10 | Dot Pha Level 300 | Breakthrough | Dot pha den Lv300 (Major) | 80000 EXP, 32000 LT | - |

**Tong thuong Chuong 9:** 720,000 EXP, 289,000 LT

---

### Chuong 10: Nguyen Anh 2 (Lv300-350, ~45 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 10.1 | Nguyen Lieu Legend | Thu Thap | 80 Da Thuy Tinh + 50 Binh Anh | 100000 EXP | - |
| 10.2 | Luyen Dan Cap 65 | Luyen Dan | Luyen 80 Dan Duoc cap 65+ | 100000 EXP, 36000 LT | Luyen Dan |
| 10.3 | Che Tao Tim 3 | Che Tao | Che tao 3 Trang Bi Mau Tim cap cao | 100000 EXP, 40000 LT | Che Tao |
| 10.4 | Nang Cap +21 | Che Tao | Nang cap Trang Bi den +21 | 90000 EXP, 36000 LT | Che Tao |
| 10.5 | Tich Luy Sat Thu 9 | Kill | Giet 700 quai cap 300-340 | 110000 EXP, 44000 LT | - |
| 10.6 | Boss Legend | Boss | Giet 40 Boss | 100000 EXP, 40000 LT | - |
| 10.7 | Luyen Dan Cap 70 | Luyen Dan | Luyen 100 Dan Duoc cap 70+ | 110000 EXP, 44000 LT | Luyen Dan |
| 10.8 | Set 4 Hoan Thanh | Che Tao | Hoan thanh Set thu 4 | 120000 EXP, 48000 LT | Che Tao |
| 10.9 | Linh Can Cap 7 | Linh Can | Nang cap Linh Can len cap 7 | 100000 EXP, 40000 LT | Linh Can |
| 10.10 | Dot Pha Level 350 | Breakthrough | Dot pha den Lv350 | 110000 EXP, 44000 LT | - |

**Tong thuong Chuong 10:** 940,000 EXP, 372,000 LT

---

### Chuong 11: Nguyen Anh 3 (Lv350-400, ~55 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 11.1 | Nguyen Lieu Than Thai | Thu Thap | 100 Da Moc Tinh + 60 Da Kim Tinh | 140000 EXP | - |
| 11.2 | Luyen Dan Cap 75 | Luyen Dan | Luyen 120 Dan Duoc cap 75+ | 140000 EXP, 50000 LT | Luyen Dan |
| 11.3 | Che Tao Cam 3 | Che Tao | Che tao 3 Trang Bi Mau Cam cap cao | 140000 EXP, 56000 LT | Che Tao |
| 11.4 | Nang Cap +22 | Che Tao | Nang cap Trang Bi den +22 | 120000 EXP, 48000 LT | Che Tao |
| 11.5 | Tich Luy Sat Thu 10 | Kill | Giet 800 quai cap 350-390 | 150000 EXP, 60000 LT | - |
| 11.6 | Boss Than Thai | Boss | Giet 45 Boss | 140000 EXP, 56000 LT | - |
| 11.7 | Luyen Dan Cap 80 | Luyen Dan | Luyen 150 Dan Duoc cap 80+ | 150000 EXP, 60000 LT | Luyen Dan |
| 11.8 | Nang Cap +23 | Che Tao | Nang cap Trang Bi den +23 | 130000 EXP, 52000 LT | Che Tao |
| 11.9 | Set 5 Hoan Thanh | Che Tao | Hoan thanh Set thu 5 | 160000 EXP, 64000 LT | Che Tao |
| 11.10 | Dot Pha Level 400 | Breakthrough | Dot pha den Lv400 (Major) | 150000 EXP, 60000 LT | - |

**Tong thuong Chuong 11:** 1,280,000 EXP, 506,000 LT

---

### Chuong 12: Hop The 1 (Lv400-450, ~65 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 12.1 | Nguyen Lieu Hop The | Thu Thap | 120 Da Thuy Tinh + 80 Da Hoa Tinh | 180000 EXP | - |
| 12.2 | Luyen Dan Cap 85 | Luyen Dan | Luyen 180 Dan Duoc cap 85+ | 180000 EXP, 65000 LT | Luyen Dan |
| 12.3 | Che Tao Tim 4 | Che Tao | Che tao 4 Trang Bi Mau Tim cap cao | 180000 EXP, 72000 LT | Che Tao |
| 12.4 | Nang Cap +24 | Che Tao | Nang cap Trang Bi den +24 | 160000 EXP, 64000 LT | Che Tao |
| 12.5 | Tich Luy Sat Thu 11 | Kill | Giet 900 quai cap 400-440 | 200000 EXP, 80000 LT | - |
| 12.6 | Boss Hop The | Boss | Giet 50 Boss | 180000 EXP, 72000 LT | - |
| 12.7 | Luyen Dan Cap 90 | Luyen Dan | Luyen 200 Dan Duoc cap 90+ | 200000 EXP, 80000 LT | Luyen Dan |
| 12.8 | Nang Cap +25 | Che Tao | Nang cap Trang Bi den +25 | 170000 EXP, 68000 LT | Che Tao |
| 12.9 | Linh Can Cap 9 | Linh Can | Nang cap Linh Can len cap 9 | 180000 EXP, 72000 LT | Linh Can |
| 12.10 | Dot Pha Level 450 | Breakthrough | Dot pha den Lv450 | 190000 EXP, 76000 LT | - |

**Tong thuong Chuong 12:** 1,640,000 EXP, 649,000 LT

---

### Chuong 13: Hop The 2 (Lv450-500, ~80 ngay)

| STT | Ten NV | Loai | Yeu Cau | Phan Thuong | Tuong Tac |
|---|---|---|---|---|---|
| 13.1 | Nguyen Lieu Than Thai 2 | Thu Thap | 150 Da Moc Tinh + 100 Da Kim Tinh | 220000 EXP | - |
| 13.2 | Luyen Dan Cap 95 | Luyen Dan | Luyen 250 Dan Duoc cap 95+ | 220000 EXP, 80000 LT | Luyen Dan |
| 13.3 | Che Tao Cam 4 | Che Tao | Che tao 4 Trang Bi Mau Cam cap cao | 220000 EXP, 88000 LT | Che Tao |
| 13.4 | Nang Cap +26 | Che Tao | Nang cap Trang Bi den +26 | 200000 EXP, 80000 LT | Che Tao |
| 13.5 | Tich Luy Sat Thu 12 | Kill | Giet 1000 quai cap 450-490 | 250000 EXP, 100000 LT | - |
| 13.6 | Boss Cuoi Cung | Boss | Giet 60 Boss | 220000 EXP, 88000 LT | - |
| 13.7 | Luyen Dan Max | Luyen Dan | Luyen 300 Dan Duoc cap 98+ | 250000 EXP, 100000 LT | Luyen Dan |
| 13.8 | Set Cuoi | Che Tao | Hoan thanh Set cuoi cung | 280000 EXP, 112000 LT | Che Tao |
| 13.9 | Linh Can Max | Linh Can | Nang cap Linh Can len cap toi da | 250000 EXP, 100000 LT | Linh Can |
| 13.10 | Dot Pha Level 500 | Breakthrough | Dot pha den Lv500 (Major) | 240000 EXP, 96000 LT | - |

**Tong thuong Chuong 13:** 2,150,000 EXP, 844,000 LT

---

## PHAN 4: NHIEM VU PHU TUYEN CHI TIET

### 4.1 Nhiem Vu Ngay (Daily Quests)

**Cau truc:** Moi ngay he thong tao 5 NV ngau nhien tu pool 8 loai. Player chon hoan thanh tat ca de nhan bonus.

**Pool 8 loai Nhiem Vu:**

| Ma NV | Ten | Loai | Yeu Cau | Phan Thuong EXP | Phan Thuong LT | Tuong Tac |
|---|---|---|---|---|---|---|
| D1 | Tu Luyen Chan Thanh | Tu Luyen | Tu luyen 30 phut bat ky mode | 500 | 200 | - |
| D2 | Sat Thu Nhac Nho | Kill | Giet 30 quai bat ky cap | 400 | 150 | - |
| D3 | Luyen Dan Ngay | Luyen Dan | Luyen 3 vien dan bat ky | 600 | 300 | Luyen Dan |
| D4 | Che Tao Ngay | Che Tao | Che tao 1 trang bi bat ky | 500 | 250 | Che Tao |
| D5 | Nang Cap Ngay | Che Tao | Nang cap 1 trang bi bat ky | 400 | 200 | Che Tao |
| D6 | Thu Thap Ngay | Thu Thap | Thu thap 10 nguyen lieu bat ky | 300 | 100 | - |
| D7 | Dau Boss Ngay | Boss | Giet 1 Boss Linh Can | 700 | 400 | - |
| D8 | Chiem Linh | PVP | Chien thang 3 tran Dau Truong | 500 | 300 | - |

**Bonus hoan thanh 5/5 NV ngay:**
- 1,000 EXP
- 500 LT
- 5 Ma Thach (MT)

**Tong thu nhap ngay (neu hoan thanh tat ca 5 NV):**
- EXP: ~3,500 (tu NV) + 1,000 (bonus) = ~4,500 EXP
- LT: ~1,550 (tu NV) + 500 (bonus) = ~2,050 LT
- MT: 5

**Luu y:** NV ngay chi cho EXP nho, khong phai nguon EXP chinh. Muc dich la duy tri thoi quen choi moi ngay.

---

### 4.2 Nhiem Vu Tuan (Weekly Quests)

**Cau truc:** Moi tuan co 10 NV, duoc phan bo qua 7 ngay (T2-CN). Tung NV co ngay khoi dau cu the.

| Ma NV | Ngay Khoi Dau | Ten | Yeu Cau | EXP | LT | MT | Tuong Tac |
|---|---|---|---|---|---|---|---|
| W1 | Thu 2 | Luyen Dan Tuan | Luyen 10 dan bat ky | 3,000 | 1,000 | 0 | Luyen Dan |
| W2 | Thu 2 | Sat Thu Tuan | Giet 100 quai | 2,500 | 800 | 0 | - |
| W3 | Thu 3 | Che Tao Tuan | Che tao 3 trang bi | 3,000 | 1,200 | 0 | Che Tao |
| W4 | Thu 3 | Dau Boss Tuan | Giet 5 Boss Linh Can | 3,500 | 1,500 | 0 | Boss |
| W5 | Thu 4 | Nang Cap Tuan | Nang cap 5 trang bi | 2,500 | 1,000 | 0 | Che Tao |
| W6 | Thu 4 | Sat Thu Tuan 2 | Giet 200 quai | 3,000 | 1,200 | 0 | - |
| W7 | Thu 5 | Luyen Dan Tuan 2 | Luyen 20 dan | 4,000 | 1,500 | 0 | Luyen Dan |
| W8 | Thu 5 | Boss Tuan 2 | Giet 10 Boss | 4,500 | 2,000 | 0 | Boss |
| W9 | Thu 6 | Thu Thap Tuan | Thu thap 50 nguyen lieu | 3,000 | 1,000 | 0 | - |
| W10 | Chu Nhat | Tong Hop | Hoan thanh W1-W9 | 10,000 | 5,000 | 20 | - |

**Tong thuong tuan:**
- EXP: 39,000
- LT: 16,200
- MT: 20

**Luu y:** NV W10 chi nhan khi hoan thanh tat ca 9 NV truoc do. Day la phan thuong lon nhat trong thang cho free player.

---

### 4.3 Nhiem Vu Thang (Monthly Quests)

**Cau truc:** 5 NV moi thang, yeu cau cao, phan thuong lon.

| Ma NV | Ten | Yeu Cau | EXP | LT | MT | Tuong Tac |
|---|---|---|---|---|---|---|
| M1 | Luyen Dan Thang | Luyen 100 dan duoc cap 5+ | 50,000 | 20,000 | 50 | Luyen Dan |
| M2 | Che Tao Thang | Che tao 10 trang bi Mau Vang+ | 60,000 | 25,000 | 50 | Che Tao |
| M3 | Sat Thu Thang | Giet 50 Boss Linh Can | 80,000 | 30,000 | 50 | Boss |
| M4 | Nang Cap Thang | Nang cap tong cong +50 (tren tat ca trang bi) | 70,000 | 25,000 | 50 | Che Tao |
| M5 | Tong Hop Thang | Hoan thanh M1 + M2 + M3 + M4 | 200,000 | 100,000 | 200 | - |

**Tong thuong thang:**
- EXP: 460,000
- LT: 200,000
- MT: 400

**Luu y:** M5 la phan thuong lon nhat thang. Free player can nhan 400 MT/thang tu NV thang (neu hoan thanh du).

---

### 4.4 Nhiem Vu Thanh Tuu (Achievement Quests)

**Cau truc:** 8 nhom, moi nhom 3 cap. Hoan thanh 1 lan, khong reset.

#### Nhom 1: Sat Thu (Monster Slayer)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Sat Thu Thuong | Giet 1,000 quai | 10,000 | 5,000 |
| Sat Thu Luyen | Giet 5,000 quai | 50,000 | 20,000 |
| Sat Thu Vua | Giet 10,000 quai | 200,000 | 100,000 |

#### Nhom 2: Luyen Dan Sư (Alchemy Master)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Luyen Dan Sinh | Luyen 100 dan | 15,000 | 8,000 |
| Luyen Dan Su | Luyen 500 dan | 75,000 | 30,000 |
| Dai Luyen Dan Su | Luyen 1,000 dan | 300,000 | 120,000 |

#### Nhom 3: Tho Ren (Blacksmith)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Tho Ren So | Che tao 50 trang bi | 15,000 | 8,000 |
| Tho Ren Khoa | Che tao 200 trang bi | 75,000 | 30,000 |
| Dai Tho Ren | Che tao 500 trang bi | 300,000 | 120,000 |

#### Nhom 4: Thu Thap (Collector)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Nguoi Thu Thap | Thu thap 500 nguyen lieu | 10,000 | 5,000 |
| Dai Nguoi Thu Thap | Thu thap 2,000 nguyen lieu | 40,000 | 20,000 |
| Vua Thu Thap | Thu thap 5,000 nguyen lieu | 150,000 | 80,000 |

#### Nhom 5: Boss Hunter

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| San Boss | Giet 10 Boss | 20,000 | 10,000 |
| Thanh San Boss | Giet 50 Boss | 100,000 | 50,000 |
| Vua San Boss | Giet 100 Boss | 500,000 | 200,000 |

#### Nhom 6: Chien Binh (PVP Warrior)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Chien Binh So | Chien thang 10 tran PVP | 15,000 | 8,000 |
| Chien Binh Kinh Nghiem | Chien thang 500 tran PVP | 60,000 | 30,000 |
| Vo Dich | Chien thang 1,000 tran PVP | 250,000 | 100,000 |

#### Nhom 7: Tien Si (Cultivation Expert)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Tien Si So | Tu luyen 100 gio | 20,000 | 10,000 |
| Tien Si | Tu luyen 500 gio | 100,000 | 50,000 |
| Dai Tien Si | Tu luyen 1,000 gio | 400,000 | 200,000 |

#### Nhom 8: Dong Bo (Set Collector)

| Cap | Yeu Cau | EXP | LT |
|---|---|---|---|
| Thu Dong Bo | Hoan thanh 1 Set | 50,000 | 25,000 |
| Dong Bo Thu | Hoan thanh 3 Set | 200,000 | 100,000 |
| Bo Suu Tap Vang | Hoan thanh 5 Set | 1,000,000 | 500,000 |

**Tong EXP tu Thanh Tuu:** ~3,860,000
**Tong LT tu Thanh Tuu:** ~1,668,000

---

### 4.5 Nhiem Vu An (Hidden Quests)

**Cau truc:** Khong hien thi tren giao dien. Chi xuat hien khi player thuc hanh hanh dong cu the. La phan thuong cho viec kham pha game.

| Ma NV | Ten | Cach Kich Hoat | Yeu Cau Chi Tiet | EXP | LT |
|---|---|---|---|---|---|
| H1 | Dan Lien Tuc | Luyen 10 vien dan lien tuc (khong thoat giao dien) | Luyen 10 vien thanh cong | 5,000 | 2,000 |
| H2 | Tho Ren Khong Loi | Che tao 5 trang bi lien tuc khong fail | 5 lan che tao thanh cong lien tuc | 8,000 | 3,000 |
| H3 | Song Sot Ky Dieu | Thang Boss voi HP < 10% | Giet Boss khi HP < 10% | 10,000 | 5,000 |
| H4 | Ngu Hanh Duyen Day | Thu thap du 5 loai Da Nguyen To | Co du Da Hoa, Thuy, Moc, Kim, Tho | 15,000 | 8,000 |
| H5 | Cao Thu Nang Cap | Nang cap trang bi +15 khong fail | Nang cap thanh cong tu +14 len +15 | 20,000 | 10,000 |
| H6 | Bao Phong Sat Thu | Giet 100 quai trong 5 phut | Giet 100 quai trong 300 giay | 12,000 | 5,000 |
| H7 | Dai Luyen Dan | Luyen dan voi ty le < 20% thanh cong | Luyen thanh cong khi ty le < 20% | 15,000 | 8,000 |
| H8 | Lien Thang | Thang 5 tran PVP lien tiep | Win streak 5 | 10,000 | 5,000 |

**Tong EXP tu NV An:** 95,000
**Tong LT tu NV An:** 46,000

---

## PHAN 5: NHIEM VU TUONG TAC CHI TIET

### 5.1 Quest Line Luyen Dan (15 Nhiem Vu)

Day la quest line dai, bat dau tu Lv5 va keo dai den cuoi game. Moi NV yeu cau player thuc hanh luyen dan voi so luong va cap do tang dan.

| STT | Ten NV | Yeu Cau Chi Tiet | EXP | LT | Unlock |
|---|---|---|---|---|---|
| LD1 | Tim Nguyen Lieu | Thu thap 10 Thao Duoc Pho Thong | 500 | 0 | Lv5 |
| LD2 | Luyen Lan Dau | Luyen 5 Viên Thuoc Hoi Huyet | 1,000 | 50 | LD1 |
| LD3 | Tim Da Element | Thu thap 5 Da Hoa Tinh | 1,500 | 100 | LD2 |
| LD4 | Luyen Dan Co Ban | Luyen 3 Dan Duoc cap 5+ | 2,500 | 200 | LD3 |
| LD5 | Tim Thao Hiem | Thu thap 10 Thao Duoc Quy Hiem | 3,000 | 300 | LD4 |
| LD6 | Luyen Dan Trung Binh | Luyen 5 Dan Duoc cap 8+ | 4,000 | 500 | LD5 |
| LD7 | Tim Binh Anh | Thu thap 3 Binh Anh Tinh Thach | 5,000 | 800 | LD6 |
| LD8 | Luyen Dan Cao Cap | Luyen 10 Dan Duoc cap 15+ | 7,000 | 1,200 | LD7 |
| LD9 | Luyen Dan Binh Anh | Luyen 1 Dan Duoc Binh Anh cap 15+ | 10,000 | 2,000 | LD8 |
| LD10 | Luyen Dan So Luong | Luyen 20 Dan Duoc cap 20+ | 15,000 | 3,000 | LD9 |
| LD11 | Luyen Dan Ki Nang | Luyen 30 Dan Duoc cap 25+ | 20,000 | 5,000 | LD10 |
| LD12 | Luyen Dan Thanh Thuc | Luyen 50 Dan Duoc cap 30+ | 30,000 | 8,000 | LD11 |
| LD13 | Luyen Dan Chuyen Nghiep | Luyen 100 Dan Duoc cap 40+ | 50,000 | 15,000 | LD12 |
| LD14 | Luyen Dan Dai Su | Luyen 200 Dan Duoc cap 50+ | 80,000 | 25,000 | LD13 |
| LD15 | Luyen Dan Toa Thanh | Luyen 500 Dan Duoc cap 80+ | 150,000 | 50,000 | LD14 |

**Tong EXP:** 379,500
**Tong LT:** 111,150
**Tong so lan luyen dan can:** 954 vien dan (tich luy)

---

### 5.2 Quest Line Che Tao (15 Nhiem Vu)

Quest line che tao trang bi, bat dau tu Lv5. Yeu cau che tao va nang cap trang bi.

| STT | Ten NV | Yeu Cau Chi Tiet | EXP | LT | Unlock |
|---|---|---|---|---|---|
| CT1 | Tim Cuong Hoa | Thu thap 10 Cuong Hoa Thach | 500 | 0 | Lv5 |
| CT2 | Che Tao Dau Tien | Che tao 1 Vu Khi Pho Thong | 1,000 | 50 | CT1 |
| CT3 | Nang Cap Co Ban | Nang cap Vu Khi len +2 | 1,500 | 100 | CT2 |
| CT4 | Che Tao Giap | Che tao 1 Giap Pho Thong | 2,500 | 200 | CT3 |
| CT5 | Nang Cap Trung Binh | Nang cap tong +5 (tren tat ca trang bi) | 3,000 | 300 | CT4 |
| CT6 | Che Tao Vang | Che tao 1 Trang Bi Mau Vang | 4,000 | 500 | CT5 |
| CT7 | Nang Cap Cao | Nang cap 1 Trang Bi den +8 | 5,000 | 800 | CT6 |
| CT8 | Che Tao Dong Bo | Che tao 5 Trang Bi Mau Vang | 7,000 | 1,200 | CT7 |
| CT9 | Nang Cap Chuyen Nghiep | Nang cap 1 Trang Bi den +10 | 10,000 | 2,000 | CT8 |
| CT10 | Che Tao Tim | Che tao 1 Trang Bi Mau Tim | 15,000 | 3,000 | CT9 |
| CT11 | Nang Cap Cao Cap | Nang cap 1 Trang Bi den +12 | 20,000 | 5,000 | CT10 |
| CT12 | Che Tao Tim Nhieu | Che tao 3 Trang Bi Mau Tim | 30,000 | 8,000 | CT11 |
| CT13 | Nang Cap Dai Su | Nang cap 1 Trang Bi den +15 | 50,000 | 15,000 | CT12 |
| CT14 | Che Tao Cam | Che tao 1 Trang Bi Mau Cam | 80,000 | 25,000 | CT13 |
| CT15 | Nang Cap Toi Thieu | Nang cap 1 Trang Bi den +18 | 150,000 | 50,000 | CT14 |

**Tong EXP:** 379,500
**Tong LT:** 111,150
**Tong so lan che tao can:** ~75 lan che tao + ~30 lan nang cap (tich luy)

---

### 5.3 Quest Line Thu Thap Nguyen Lieu (10 Nhiem Vu)

Quest line thu thap nguyen lieu, ho tro ca Luyen Dan va Che Tao.

| STT | Ten NV | Yeu Cau Chi Tiet | EXP | LT | Unlock |
|---|---|---|---|---|---|
| TT1 | Thao Duoc Co Ban | Thu thap 20 Thao Duoc Pho Thong | 500 | 0 | Lv10 |
| TT2 | Da Hoa Tinh | Thu thap 10 Da Hoa Tinh | 1,000 | 100 | TT1 |
| TT3 | Thao Quy Hiem | Thu thap 5 Thao Duoc Quy Hiem | 2,000 | 200 | TT2 |
| TT4 | Da Thuy Tinh | Thu thap 10 Da Thuy Tinh | 3,000 | 500 | TT3 |
| TT5 | Da Moc Tinh | Thu thap 20 Da Moc Tinh | 5,000 | 800 | TT4 |
| TT6 | Da Kim Tinh | Thu thap 30 Da Kim Tinh | 8,000 | 1,200 | TT5 |
| TT7 | Binh Anh Tinh Thach | Thu thap 5 Binh Anh Tinh Thach | 12,000 | 2,000 | TT6 |
| TT8 | Da Ngu Hanh | Thu thap 50 Da Ngu Hanh (moi loai 10) | 20,000 | 5,000 | TT7 |
| TT9 | Nguyen Lieu Hiem | Thu thap 100 Nguyen Lieu cap 30+ | 40,000 | 10,000 | TT8 |
| TT10 | Nguyen Lieu Than Thai | Thu thap 200 Nguyen Lieu cap 50+ | 80,000 | 25,000 | TT9 |

**Tong EXP:** 171,500
**Tong LT:** 44,800
**Tong nguyen lieu can thu thap:** ~450 (tich luy)

---

### 5.4 Quest Line Nang Cap Trang Bi (10 Nhiem Vu)

Quest line nang cap trang bi, lien quan truc tiep den he thong cuong hoa.

| STT | Ten NV | Yeu Cau Chi Tiet | EXP | LT | Unlock |
|---|---|---|---|---|---|
| NC1 | Nang Cap Dau Tien | Nang cap 1 Trang Bi len +1 | 500 | 50 | Lv10 |
| NC2 | Nang Cap +3 | Nang cap 1 Trang Bi len +3 | 1,000 | 100 | NC1 |
| NC3 | Nang Cap +5 | Nang cap 1 Trang Bi len +5 | 2,000 | 200 | NC2 |
| NC4 | Nang Cap +7 | Nang cap 1 Trang Bi len +7 | 3,000 | 500 | NC3 |
| NC5 | Nang Cap +8 | Nang cap 1 Trang Bi len +8 | 4,000 | 800 | NC4 |
| NC6 | Nang Cap +10 | Nang cap 1 Trang Bi len +10 | 6,000 | 1,500 | NC5 |
| NC7 | Nang Cap +12 | Nang cap 1 Trang Bi len +12 | 10,000 | 3,000 | NC6 |
| NC8 | Nang Cap +15 | Nang cap 1 Trang Bi len +15 | 20,000 | 8,000 | NC7 |
| NC9 | Nang Cap +18 | Nang cap 1 Trang Bi len +18 | 40,000 | 20,000 | NC8 |
| NC10 | Nang Cap +20 | Nang cap 1 Trang Bi len +20 | 80,000 | 50,000 | NC9 |

**Tong EXP:** 166,500
**Tong LT:** 84,150
**Tong so lan nang cap can:** 20 lan (tich luy tren 1 trang bi)

---

## PHAN 6: NGUYEN LIEU VA CONG THUC

### 6.1 Bang Nguyen Lieu

| ID | Ten | Muc Do Hiem | Nguon Drop | Dung Cho |
|---|---|---|---|---|
| 1 | Cuong Hoa Thach | Pho Thong | PVE, Boss, NV | Che Tao, Nang Cap |
| 30 | Thao Duoc Pho Thong | Pho Thong | Map world, PVE | Luyen Dan cap 1-5 |
| 31 | Thao Duoc Quy Hiem | Hiem | Boss, map hiem | Luyen Dan cap 5-15 |
| 32 | Da Hoa Tinh | Trung Binh | Boss cap 10+ | Luyen Dan, Che Tao |
| 33 | Da Thuy Tinh | Trung Binh | Boss cap 20+ | Luyen Dan, Che Tao |
| 34 | Da Moc Tinh | Kha Hiem | Boss cap 30+ | Luyen Dan, Che Tao |
| 35 | Da Kim Tinh | Hiem | Boss cap 40+ | Luyen Dan, Che Tao |
| 36 | Da Tho Tinh | Kha Hiem | Boss cap 35+ | Luyen Dan, Che Tao |
| 37 | Binh Anh Tinh Thach | Rat Hiem | Boss cap 50+ | Luyen Dan cap 15+, Che Tao cap cao |
| 38 | Thao Duoc Than Thai | Cuc Hiem | Boss cap 80+ | Luyen Dan cap 50+ |

### 6.2 Bang Cong Thuc Luyen Dan Moi (Them)

Them 10 cong thuc moi vao he thong:

| ID | Ten | Cap Yeu Cau | Nguyen Lieu | Ty Le | Ket Qua |
|---|---|---|---|---|---|
| 18 | Thuoc Hoi Phuc cap 10 | 10 | 5 Thao Pho + 3 Da Hoa | 80% | 2x Thuoc Hoi Phuc |
| 19 | Dan Binh Anh cap 15 | 15 | 3 Binh Anh + 5 Thao QH | 40% | 1x Dan Binh Anh |
| 20 | Thuoc Tang Suc Manh | 20 | 10 Thao QH + 5 Da Thuy + 2 Binh Anh | 60% | 1x Thuoc Buff ATK 10% 30ph |
| 21 | Thuoc Tang Phong Thu | 20 | 10 Thao QH + 5 Da Tho + 2 Binh Anh | 60% | 1x Thuoc Buff DEF 10% 30ph |
| 22 | Dan Hoi Phuc cap 30 | 30 | 8 Thao QH + 5 Da Moc + 3 Binh Anh | 50% | 2x Dan Hoi Phuc |
| 23 | Dan Tang Kinh Nghiem | 35 | 10 Thao Than Thai + 5 Da Hoa + 3 Binh Anh | 35% | 1x Dan Tang EXP 20% 1h |
| 24 | Thuoc Giai Doc | 40 | 15 Thao QH + 10 Da Thuy + 5 Binh Anh | 45% | 3x Thuoc Giai Doc |
| 25 | Dan Binh Anh cap 50 | 50 | 5 Binh Anh + 10 Thao Than Thai + 5 Da Kim | 25% | 2x Dan Binh Anh cap 50 |
| 26 | Dan Tang Exp cap 60 | 60 | 15 Thao Than Thai + 8 Da Moc + 5 Binh Anh | 20% | 1x Dan Tang EXP 30% 2h |
| 27 | Dan Than Thai | 80 | 20 Thao Than Thai + 10 Binh Anh + 5 Da Kim | 15% | 1x Dan Than Thai (buff toean than) |

### 6.3 Bang Cong Thuc Che Tao Moi (Them)

Them 8 cong thuc che tao moi:

| ID | Ten | Cap Yeu Cau | Nguyen Lieu | Ty Le | Ket Qua |
|---|---|---|---|---|---|
| 9 | Vu Khi Tim cap 30 | 30 | 10 Cuong Hoa + 5 Da Kim + 2 Binh Anh | 60% | Vu Khi Mau Tim ATK+100 |
| 10 | Giap Tim cap 30 | 30 | 10 Cuong Hoa + 5 Da Tho + 2 Binh Anh | 60% | Giap Mau Tim DEF+80 HP+500 |
| 11 | Vu Khi Cam cap 50 | 50 | 20 Cuong Hoa + 10 Da Kim + 5 Binh Anh | 40% | Vu Khi Mau Cam ATK+200 |
| 12 | Giap Cam cap 50 | 50 | 20 Cuong Hoa + 10 Da Tho + 5 Binh Anh | 40% | Giap Mau Cam DEF+150 HP+1000 |
| 13 | Phu Kien Tim cap 40 | 40 | 15 Cuong Hoa + 8 Da Thuy + 3 Binh Anh | 50% | Phu Kien Mau Tim (chon loai) |
| 14 | Phu Kien Cam cap 60 | 60 | 25 Cuong Hoa + 12 Da Moc + 6 Binh Anh | 35% | Phu Kien Mau Cam (chon loai) |
| 15 | Vu Khi Than Thai cap 80 | 80 | 30 Cuong Hoa + 15 Da Kim + 8 Binh Anh + 3 Thao Than Thai | 25% | Vu Khi Than Thai ATK+350 |
| 16 | Set Hop The cap 100 | 100 | 50 Cuong Hoa + 20 moi Da + 10 Binh Anh + 5 Thao Than Thai | 20% | 1 phan Set Hop The |

---

## PHAN 7: THAY DOI CODE CAN THIET

### 7.1 EXP Formula (player.php)

```php
// ============================================================
// File: class/player.php
// Ham tinh EXP can de len cap
// ============================================================

// CU:
// $umaxexp = $nextlv * ($nextlv + round($nextlv / 2)) * 10 * $rangesexp[$ncc] + $nextlv;

// MOI:
$nextlv = $ulv + 1;
$umaxexp = max(100, pow($nextlv, 2.8) * 2);

// Neu la dot pha (moi 50 level):
if ($ulv > 0 && $ulv % 50 == 0) {
    $breakthrough_extra = pow($ulv, 2.5) * 50;
    $umaxexp += $breakthrough_extra;
}
```

### 7.2 Cap Absorb (xiulian.php)

```php
// ============================================================
// File: game/xiulian.php
// Cap absorb va exp bonus
// ============================================================

// Absorb tong toi da = 50%
$total_absorb = min(0.50, $lc_absorb + $cp_absorb);

// EXP bonus tong toi da = 40%
$total_exp_bonus = min(0.40, $lc_exp_bonus + $cp_exp_bonus);
```

### 7.3 Scale Chi Phi Tu Luyen (xiulian.php)

```php
// ============================================================
// File: game/xiulian.php
// Chi phi tu luyen theo cap
// ============================================================

// CU: $lt_cost = 1; $mt_cost = 1; (co dinh)

// MOI:
$lt_cost_per_min = max(1, ceil(pow($ulv, 1.15) / 5));
$mt_cost_per_min = max(1, ceil($ulv / 3));
```

### 7.4 Giam PVE LT Reward (pve.php)

```php
// ============================================================
// File: game/pve.php
// Giam LT tu PVE
// ============================================================

// CU: $yxb = round($glv * 22.5) + mt_rand(1, 200);

// MOI:
$yxb = round($glv * 5) + mt_rand(1, 50);
```

### 7.5 Giam Max Time Tu Luyen (xiulian.php)

```php
// ============================================================
// File: game/xiulian.php
// Giam thoi gian toi da tu luyen
// ============================================================

// CU:
//   Free: 60 phut
//   LT: 360 phut (6h)
//   MT: 720 phut (12h)

// MOI:
//   Free: 30 phut
//   LT: 180 phut (3h)
//   MT: 360 phut (6h)
```

### 7.6 Them He Thong Nhiem Vu (File Moi)

```php
// ============================================================
// File: class/function/nhiemvu.php (MOI)
// He thong Nhiem Vu Ngay/Tuan/Thang/Thanh Tuu/An
// ============================================================

// DB Tables can tao:
// 1. daily_quests (uid, quest_id, progress, target, reward_exp, reward_lt, reward_mt, reset_date, status)
// 2. weekly_quests (uid, quest_id, progress, target, reward_exp, reward_lt, reward_mt, week_number, status)
// 3. monthly_quests (uid, quest_id, progress, target, reward_exp, reward_lt, reward_mt, month, status)
// 4. achievements (uid, achievement_id, progress, target, claimed)
// 5. hidden_quests (uid, quest_id, discovered, completed)

// Daily reset:
function daily_quest_reset($uid) {
    // Kiem tra ngay hien tai voi ngay cuoi reset
    // Neu khac ngay: xoa NV cu, tao NV moi
    // Chon ngau nhien 5 NV tu pool 8 loai
}

// Weekly reset:
function weekly_quest_reset($uid) {
    // Kiem tra so tuan hien tai
    // Neu khac tuan: xoa NV cu, tao NV moi
}

// Monthly reset:
function monthly_quest_reset($uid) {
    // Kiem tra thang hien tai
    // Neu khac thang: xoa NV cu, tao NV moi
}

// Check progress:
function check_quest_progress($uid, $quest_id, $action, $amount) {
    // $action: 'kill', 'craft', 'alchemy', 'upgrade', 'collect', 'boss', 'pvp', 'cultivate'
    // Tang progress cua NV tuong ung
    // Kiem tra hoan thanh
}
```

### 7.7 Config JSON Moi

#### File: class/config/daily_quests.json

```json
{
    "daily_quest_pool": [
        {"id": "D1", "name": "Tu Luyen Chan Thanh", "type": "cultivate", "target": 30, "unit": "minutes", "exp": 500, "lt": 200, "mt": 0},
        {"id": "D2", "name": "Sat Thu Nhac Nho", "type": "kill", "target": 30, "unit": "monsters", "exp": 400, "lt": 150, "mt": 0},
        {"id": "D3", "name": "Luyen Dan Ngay", "type": "alchemy", "target": 3, "unit": "pills", "exp": 600, "lt": 300, "mt": 0},
        {"id": "D4", "name": "Che Tao Ngay", "type": "craft", "target": 1, "unit": "items", "exp": 500, "lt": 250, "mt": 0},
        {"id": "D5", "name": "Nang Cap Ngay", "type": "upgrade", "target": 1, "unit": "upgrades", "exp": 400, "lt": 200, "mt": 0},
        {"id": "D6", "name": "Thu Thap Ngay", "type": "collect", "target": 10, "unit": "materials", "exp": 300, "lt": 100, "mt": 0},
        {"id": "D7", "name": "Dau Boss Ngay", "type": "boss", "target": 1, "unit": "bosses", "exp": 700, "lt": 400, "mt": 0},
        {"id": "D8", "name": "Chiem Linh", "type": "pvp", "target": 3, "unit": "wins", "exp": 500, "lt": 300, "mt": 0}
    ],
    "daily_count": 5,
    "daily_bonus": {"exp": 1000, "lt": 500, "mt": 5},
    "daily_mt_reward": 5
}
```

#### File: class/config/weekly_quests.json

```json
{
    "weekly_quests": [
        {"id": "W1", "name": "Luyen Dan Tuan", "day": 1, "type": "alchemy", "target": 10, "exp": 3000, "lt": 1000},
        {"id": "W2", "name": "Sat Thu Tuan", "day": 1, "type": "kill", "target": 100, "exp": 2500, "lt": 800},
        {"id": "W3", "name": "Che Tao Tuan", "day": 2, "type": "craft", "target": 3, "exp": 3000, "lt": 1200},
        {"id": "W4", "name": "Dau Boss Tuan", "day": 2, "type": "boss", "target": 5, "exp": 3500, "lt": 1500},
        {"id": "W5", "name": "Nang Cap Tuan", "day": 3, "type": "upgrade", "target": 5, "exp": 2500, "lt": 1000},
        {"id": "W6", "name": "Sat Thu Tuan 2", "day": 3, "type": "kill", "target": 200, "exp": 3000, "lt": 1200},
        {"id": "W7", "name": "Luyen Dan Tuan 2", "day": 4, "type": "alchemy", "target": 20, "exp": 4000, "lt": 1500},
        {"id": "W8", "name": "Boss Tuan 2", "day": 4, "type": "boss", "target": 10, "exp": 4500, "lt": 2000},
        {"id": "W9", "name": "Thu Thap Tuan", "day": 5, "type": "collect", "target": 50, "exp": 3000, "lt": 1000},
        {"id": "W10", "name": "Tong Hop Tuan", "day": 6, "type": "complete_all", "target": 9, "exp": 10000, "lt": 5000, "mt": 20}
    ]
}
```

#### File: class/config/monthly_quests.json

```json
{
    "monthly_quests": [
        {"id": "M1", "name": "Luyen Dan Thang", "type": "alchemy", "target": 100, "min_level": 5, "exp": 50000, "lt": 20000, "mt": 50},
        {"id": "M2", "name": "Che Tao Thang", "type": "craft", "target": 10, "min_rarity": "yellow", "exp": 60000, "lt": 25000, "mt": 50},
        {"id": "M3", "name": "Sat Thu Thang", "type": "boss", "target": 50, "exp": 80000, "lt": 30000, "mt": 50},
        {"id": "M4", "name": "Nang Cap Thang", "type": "upgrade_total", "target": 50, "exp": 70000, "lt": 25000, "mt": 50},
        {"id": "M5", "name": "Tong Hop Thang", "type": "complete_all", "target": 4, "exp": 200000, "lt": 100000, "mt": 200}
    ]
}
```

#### File: class/config/achievements.json

```json
{
    "achievements": {
        "monster_slayer": {
            "name": "Sat Thu",
            "tiers": [
                {"id": "MS1", "name": "Sat Thu Thuong", "target": 1000, "exp": 10000, "lt": 5000},
                {"id": "MS2", "name": "Sat Thu Luyen", "target": 5000, "exp": 50000, "lt": 20000},
                {"id": "MS3", "name": "Sat Thu Vua", "target": 10000, "exp": 200000, "lt": 100000}
            ]
        },
        "alchemy_master": {
            "name": "Luyen Dan Su",
            "tiers": [
                {"id": "AM1", "name": "Luyen Dan Sinh", "target": 100, "exp": 15000, "lt": 8000},
                {"id": "AM2", "name": "Luyen Dan Su", "target": 500, "exp": 75000, "lt": 30000},
                {"id": "AM3", "name": "Dai Luyen Dan Su", "target": 1000, "exp": 300000, "lt": 120000}
            ]
        },
        "blacksmith": {
            "name": "Tho Ren",
            "tiers": [
                {"id": "BS1", "name": "Tho Ren So", "target": 50, "exp": 15000, "lt": 8000},
                {"id": "BS2", "name": "Tho Ren Khoa", "target": 200, "exp": 75000, "lt": 30000},
                {"id": "BS3", "name": "Dai Tho Ren", "target": 500, "exp": 300000, "lt": 120000}
            ]
        },
        "collector": {
            "name": "Thu Thap",
            "tiers": [
                {"id": "CL1", "name": "Nguoi Thu Thap", "target": 500, "exp": 10000, "lt": 5000},
                {"id": "CL2", "name": "Dai Nguoi Thu Thap", "target": 2000, "exp": 40000, "lt": 20000},
                {"id": "CL3", "name": "Vua Thu Thap", "target": 5000, "exp": 150000, "lt": 80000}
            ]
        },
        "boss_hunter": {
            "name": "San Boss",
            "tiers": [
                {"id": "BH1", "name": "San Boss", "target": 10, "exp": 20000, "lt": 10000},
                {"id": "BH2", "name": "Thanh San Boss", "target": 50, "exp": 100000, "lt": 50000},
                {"id": "BH3", "name": "Vua San Boss", "target": 100, "exp": 500000, "lt": 200000}
            ]
        },
        "pvp_warrior": {
            "name": "Chien Binh",
            "tiers": [
                {"id": "PV1", "name": "Chien Binh So", "target": 10, "exp": 15000, "lt": 8000},
                {"id": "PV2", "name": "Chien Binh Kinh Nghiem", "target": 500, "exp": 60000, "lt": 30000},
                {"id": "PV3", "name": "Vo Dich", "target": 1000, "exp": 250000, "lt": 100000}
            ]
        },
        "cultivation_expert": {
            "name": "Tien Si",
            "tiers": [
                {"id": "CE1", "name": "Tien Si So", "target": 100, "exp": 20000, "lt": 10000},
                {"id": "CE2", "name": "Tien Si", "target": 500, "exp": 100000, "lt": 50000},
                {"id": "CE3", "name": "Dai Tien Si", "target": 1000, "exp": 400000, "lt": 200000}
            ]
        },
        "set_collector": {
            "name": "Dong Bo",
            "tiers": [
                {"id": "SC1", "name": "Thu Dong Bo", "target": 1, "exp": 50000, "lt": 25000},
                {"id": "SC2", "name": "Dong Bo Thu", "target": 3, "exp": 200000, "lt": 100000},
                {"id": "SC3", "name": "Bo Suu Tap Vang", "target": 5, "exp": 1000000, "lt": 500000}
            ]
        }
    }
}
```

#### File: class/config/hidden_quests.json

```json
{
    "hidden_quests": [
        {"id": "H1", "name": "Dan Lien Tuc", "trigger": "alchemy_batch", "target": 10, "exp": 5000, "lt": 2000},
        {"id": "H2", "name": "Tho Ren Khong Loi", "trigger": "craft_streak", "target": 5, "exp": 8000, "lt": 3000},
        {"id": "H3", "name": "Song Sot Ky Dieu", "trigger": "boss_low_hp", "target": 10, "exp": 10000, "lt": 5000},
        {"id": "H4", "name": "Ngu Hanh Duyen Day", "trigger": "collect_all_elements", "target": 5, "exp": 15000, "lt": 8000},
        {"id": "H5", "name": "Cao Thu Nang Cap", "trigger": "upgrade_success_15", "target": 1, "exp": 20000, "lt": 10000},
        {"id": "H6", "name": "Bao Phong Sat Thu", "trigger": "kill_speed", "target": 100, "exp": 12000, "lt": 5000},
        {"id": "H7", "name": "Dai Luyen Dan", "trigger": "alchemy_low_rate", "target": 1, "exp": 15000, "lt": 8000},
        {"id": "H8", "name": "Lien Thang", "trigger": "pvp_win_streak", "target": 5, "exp": 10000, "lt": 5000}
    ]
}
```

#### File: class/config/main_quest_config.json

```json
{
    "chapters": [
        {
            "id": 1, "name": "Khoi Dau", "level_range": [1, 10], "est_days": 2,
            "quests": [
                {"id": "1.1", "name": "Huong Dan", "type": "dialogue", "npc": "thuc_tap_sinh", "exp": 50, "lt": 20},
                {"id": "1.2", "name": "Bat Dau Tu Luyen", "type": "kill", "target_monster": [1,5], "target_count": 10, "exp": 100, "lt": 50},
                {"id": "1.3", "name": "Tich Luy Kinh Nghiem", "type": "kill", "target_monster": [5,10], "target_count": 20, "exp": 200, "lt": 100},
                {"id": "1.4", "name": "Tim Nguyen Lieu", "type": "collect", "item_id": 30, "item_count": 5, "exp": 300, "lt": 150},
                {"id": "1.5", "name": "Lan Dau Luyen Dan", "type": "alchemy", "recipe_id": 1, "count": 1, "exp": 500, "lt": 200, "bonus": [{"item": 1, "count": 2}]},
                {"id": "1.6", "name": "Reng Tay Che Tao", "type": "craft", "category": "weapon", "rarity": "green", "count": 1, "exp": 800, "lt": 300, "bonus": [{"equipment": "green_weapon_lv5"}]},
                {"id": "1.7", "name": "Dot Pha Dau Tien", "type": "breakthrough", "target_level": 10, "exp": 1000, "lt": 500}
            ]
        },
        {
            "id": 2, "name": "Lam Quen", "level_range": [10, 25], "est_days": 5,
            "quests": [
                {"id": "2.1", "name": "San Sang Vat Pham", "type": "collect_multiple", "items": [{"id": 30, "count": 10}, {"id": 32, "count": 5}], "exp": 500},
                {"id": "2.2", "name": "Luyen Dan Co Ban", "type": "alchemy", "recipe_id": 1, "count": 3, "exp": 800, "lt": 200},
                {"id": "2.3", "name": "Tich Luy Sat Thu", "type": "kill", "target_monster": [10,20], "target_count": 50, "exp": 1000, "lt": 300},
                {"id": "2.4", "name": "Thu Thap Cuong Hoa", "type": "collect", "item_id": 1, "item_count": 3, "exp": 600},
                {"id": "2.5", "name": "Nang Cap Vu Khi", "type": "upgrade", "slot": "weapon", "target_plus": 2, "exp": 1200, "lt": 500},
                {"id": "2.6", "name": "Dot Pha Level 25", "type": "breakthrough", "target_level": 25, "exp": 1500, "lt": 800}
            ]
        },
        {
            "id": 3, "name": "Tu Luyen", "level_range": [25, 50], "est_days": 10,
            "quests": [
                {"id": "3.1", "name": "Tim Nguyen Lieu Hiem", "type": "collect_multiple", "items": [{"id": 31, "count": 20}, {"id": 32, "count": 10}], "exp": 2000},
                {"id": "3.2", "name": "Luyen Dan Trung Binh", "type": "alchemy", "min_level": 5, "count": 5, "exp": 3000, "lt": 1000},
                {"id": "3.3", "name": "Che Tao Trang Bi Vang", "type": "craft", "rarity": "yellow", "count": 1, "exp": 4000, "lt": 1500},
                {"id": "3.4", "name": "Tich Luy Sat Thu 2", "type": "kill", "target_monster": [25,40], "target_count": 100, "exp": 5000, "lt": 2000},
                {"id": "3.5", "name": "Tim Boss Linh Can", "type": "collect", "item_id": 37, "item_count": 5, "exp": 3000},
                {"id": "3.6", "name": "Nang Cap Trang Bi +5", "type": "upgrade", "target_plus": 5, "exp": 6000, "lt": 2500},
                {"id": "3.7", "name": "Dau Boss Linh Can", "type": "boss", "target_count": 5, "exp": 4000, "lt": 1000},
                {"id": "3.8", "name": "Dot Pha Level 50", "type": "breakthrough", "target_level": 50, "exp": 8000, "lt": 5000}
            ]
        }
    ]
}
```

---

## PHAN 8: TONG KET THAY DOI

### 8.1 Bang Tong Hop

| He Thong | Hien Tai | Moi | Anh Huong |
|---|---|---|---|
| EXP formula | Linear + rangesexp | pow(level, 2.8) * 2 | Progression cham 3-5 lan |
| Absorb cap | Vo han | 50% | Power ceiling ro rang |
| EXP bonus cap | Vo han | 40% | Power ceiling ro rang |
| Tu luyen LT cost | 1 LT/phut | Scale theo cap | LT tieu nhieu hon |
| Tu luyen MT cost | 1 MT/phut | Scale theo cap | MT tieu nhieu hon |
| PVE LT reward | glv * 22.5 | glv * 5 | -78% LT thu |
| Max time free | 60 phut | 30 phut | -50% AFK |
| Max time MT | 720 phut | 360 phut | -50% AFK |
| Nhiem vu chinh tuyen | 21 NV | ~170 NV | Progression dai hon |
| Nhiem vu phu tuyen | 5 NV/tuan | 5+10+5+24+8 = 52 NV | Hoat dong hang ngay |
| Nhiem vu tuong tac | 0 NV | 50 NV | Luyen dan + che Tao bat buoc |
| Cong thuc luyen dan | 17 | 27 | Nhieu lua chon hon |
| Cong thuc che tao | 8 | 16 | Nhieu lua chon hon |

### 8.2 Du Bao Thu Nhap Hang Ngay

| Nguon | Free (Lv100) | VIP (Lv100) | Free (Lv300) | VIP (Lv300) |
|---|---|---|---|---|
| Tu luyen EXP | 220,000 | 440,000 | 750,000 | 1,500,000 |
| PVE EXP | 50,000 | 100,000 | 150,000 | 300,000 |
| NV Ngay EXP | 4,500 | 4,500 | 4,500 | 4,500 |
| NV Tuan EXP (TB/ngay) | 5,570 | 5,570 | 5,570 | 5,570 |
| NV Thang EXP (TB/ngay) | 15,330 | 15,330 | 15,330 | 15,330 |
| **Tong EXP/ngay** | **295,400** | **565,400** | **925,400** | **1,825,400** |

| Nguon | Free (Lv100) | VIP (Lv100) | Free (Lv300) | VIP (Lv300) |
|---|---|---|---|---|
| PVE LT | 5,500 | 11,000 | 15,000 | 30,000 |
| Boss LT | 600 | 1,200 | 1,200 | 2,400 |
| NV Ngay LT | 2,050 | 2,050 | 2,050 | 2,050 |
| NV Tuan LT (TB/ngay) | 2,314 | 2,314 | 2,314 | 2,314 |
| NV Thang LT (TB/ngay) | 6,667 | 6,667 | 6,667 | 6,667 |
| **Tong LT/ngay** | **17,131** | **23,231** | **27,231** | **43,431** |

| Nguon | Free | VIP |
|---|---|---|
| NV Ngay MT | 5 | 5 |
| NV Tuan MT (TB/ngay) | 2.86 | 2.86 |
| NV Thang MT (TB/ngay) | 13.33 | 13.33 |
| **Tong MT/ngay** | **~21** | **~21** |

### 8.3 Du Bao Tich Luy 1 Nam

| Chi So | Free Player | VIP Player |
|---|---|---|
| Tong EXP nam | ~108 tr | ~206 tr |
| Tong LT nam | ~6.2 tr | ~8.5 tr |
| Tong MT nam | ~7,665 | ~7,665 |
| Cap du kien (nam 1) | ~Lv180 | ~Lv230 |
| Nam Nhi Cap du kien | ~Lv300 | ~Lv400 |
| Nam Ba Cap du kien | ~Lv400 | ~Lv500 (endgame) |

---

## PHAN 9: LICH SU THAY DOI

| Phien Ban | Noi Dung | File |
|---|---|---|
| v1.0 | Phan tich ban dau | GAME_BALANCE_ANALYSIS.md |
| v1.1 | Proposal 1 - Can bang kinh te | ECONOMY_BALANCE_PROPOSAL.md |
| v1.2 | Proposal 2 - Progression dai han | PROGRESSION_LONG_TERM.md |
| v2.0 | Proposal 3 - Chi tiet day du | PROGRESSION_DETAILED.md (file nay) |

---

*Tai lieu nay la ban day du, co the doc va trien khai code ngay.*
*Luu tai: D:\XXJserver\PROGRESSION_DETAILED.md*
