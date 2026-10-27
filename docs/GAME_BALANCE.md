# PHÂN TÍCH TOÀN BỘ GAME BALANCE - Xích Diệm Thiên

> Ngày phân tích: 20/07/2026
> Nguồn: Đọc trực tiếp từ source code PHP + JSON config

---

## MỤC LỤC

1. [Hệ Thống Cảnh Giới](#i-hệ-thống-cảnh-giới)
2. [Hệ Thống Linh Căn](#ii-hệ-thống-linh-căn)
3. [Tu Luyện](#iii-tu-luyện)
4. [Đánh Quái PVE](#iv-đánh-quái-pve)
5. [Boss Linh Căn](#v-boss-linh-căn)
6. [Tỏa Yêu Tháp](#vi-tỏa-yêu-tháp)
7. [Đấu Trường PVP](#vii-đấu-trường-pvp)
8. [Nhiệm Vụ](#viii-nhiệm-vụ)
9. [Đột Phá](#ix-đột-phá)
10. [Trang Bị & Cường Hóa](#x-trang-bị--cường-hóa)
11. [Võ Công](#xi-võ-công)
12. [Công Pháp](#xii-công-pháp)
13. [Luyện Đan & Chế Tạo](#xiii-luyện-đan--chế-tạo)
14. [Sủng Vật](#xiv-sủng-vật)
15. [Kỹ Năng Linh Căn](#xv-kỹ-năng-linh-căn)
16. [Thiên Phú](#xvi-thiên-phú)
17. [Tổng Thu Nhập Tối Đa Mỗi Ngày](#xvii-tổng-thu-nhập-tối-đa-mỗi-ngày)
18. [Phân Tích Tốc Độ Lên Cảnh Giới](#xviii-phân-tích-tốc-độ-lên-cảnh-giới)
19. [So Sánh Linh Căn](#xix-so-sánh-linh-căn)
20. [Lưu Ý Quan Trọng](#xx-lưu-ý-quan-trọng)

---

## I. HỆ THỐNG CẢNH GIỚI

### Cấu trúc

- **Tổng số:** 86 cảnh giới
- **Mỗi cảnh giới:** 50 level (0-49)
- **Tổng:** 4,300 level (range 0-4300, step 50)
- **Mỗi 5 level:** 1 tiểu giai (10 tiểu giai/cảnh giới)
- **Tiểu giai:** Giai 1-9 + Đỉnh Phong

### Công thức EXP cần cho mỗi level

```
umaxexp = nextlv × (nextlv + round(nextlv/2)) × 10 × rangesexp[realm_index] + nextlv
```

### Bảng rangesexp theo cảnh giới

| Cảnh Giới | Level | rangesexp | VD: EXP cần cho level tiếp |
|---|---|---|---|
| Nhập Môn | 0-49 | 2.5 | level 10: 370 EXP |
| Luyện Thể | 50-99 | 5.0 | level 60: 15,030 EXP |
| Thối Thể | 100-149 | 7.5 | level 110: 49,610 EXP |
| Tụ Thân | 150-199 | 10.0 | level 160: 114,560 EXP |
| Tụ Khí | 200-249 | 12.5 | level 210: 226,410 EXP |
| Ngự Khí | 250-299 | 15.0 | level 260: 391,060 EXP |
| Luyện Khí | 300-349 | 17.5 | level 310: 615,110 EXP |
| Trúc Cơ | 350-399 | 20.0 | level 360: 905,760 EXP |
| Kim Đan | 400-449 | 25.0 | level 410: 1,428,410 EXP |
| Ngưng Anh | 450-499 | 30.0 | level 460: 2,107,260 EXP |
| Nguyên Anh | 500-549 | 35.0 | level 510: 2,955,110 EXP |
| Tụ Thần | 550-599 | 40.0 | level 560: 3,985,160 EXP |
| Hóa Thần | 600-649 | 45.0 | level 610: 5,211,210 EXP |
| Hợp Thể | 650-699 | 50.0 | level 660: 6,657,660 EXP |
| Hợp Đạo | 700-749 | 55.0 | level 710: 8,338,110 EXP |

### Danh sách 86 cảnh giới

| # | Level | Tên Cảnh Giới | Nhóm |
|---|---|---|---|
| 0 | 0-49 | Nhập Môn | Phàm |
| 1 | 50-99 | Luyện Thể | Phàm |
| 2 | 100-149 | Thối Thể | Phàm |
| 3 | 150-199 | Tụ Thân | Phàm |
| 4 | 200-249 | Tụ Khí | Phàm |
| 5 | 250-299 | Ngự Khí | Phàm |
| 6 | 300-349 | Luyện Khí | Luyện Khí |
| 7 | 350-399 | Trúc Cơ | Luyện Khí |
| 8 | 400-449 | Kim Đan | Luyện Khí |
| 9 | 450-499 | Ngưng Anh | Luyện Khí |
| 10 | 500-549 | Nguyên Anh | Luyện Khí |
| 11 | 550-599 | Tụ Thần | Cao Giai |
| 12 | 600-649 | Hóa Thần | Cao Giai |
| 13 | 650-699 | Hợp Thể | Cao Giai |
| 14 | 700-749 | Hợp Đạo | Cao Giai |
| 15 | 750-799 | Luyện Hư | Cao Giai |
| 16 | 800-849 | Đại Thừa | Cao Giai |
| 17 | 850-899 | Độ Kiếp | Thánh Cảnh |
| 18 | 900-949 | Tôn Giả | Thánh Cảnh |
| 19 | 950-999 | Thánh Giả | Thánh Cảnh |
| 20 | 1000-1049 | Thánh Tôn | Thánh Cảnh |
| 21 | 1050-1099 | Thánh Vương | Thánh Cảnh |
| 22 | 1100-1149 | Thánh Nhân | Thánh Cảnh |
| 23 | 1150-1199 | Chuẩn Đế | Thánh Cảnh |
| 24 | 1200-1249 | Đại Đế | Thánh Cảnh |
| 25 | 1250-1299 | Phi Thăng | Thánh Cảnh |
| 26 | 1300-1349 | Tiên Nhân | Tiên Giới |
| 27 | 1350-1399 | Tán Tiên | Tiên Giới |
| 28 | 1400-1449 | Nhập Tiên | Tiên Giới |
| 29 | 1450-1499 | Địa Tiên | Tiên Giới |
| 30 | 1500-1549 | Tiên Thiên | Tiên Giới |
| 31 | 1550-1599 | Thiên Tiên | Tiên Giới |
| 32 | 1600-1649 | Chân Tiên | Tiên Giới |
| 33 | 1650-1699 | Huyền Tiên | Tiên Giới |
| 34 | 1700-1749 | Kim Tiên | Tiên Giới |
| 35 | 1750-1799 | Thái Ất Kim Tiên | Kim Tiên |
| 36 | 1800-1849 | Đại La Kim Tiên | Kim Tiên |
| 37 | 1850-1899 | Cửu Thiên Huyền Tiên | Kim Tiên |
| 38 | 1900-1949 | Tiên Quân | Kim Tiên |
| 39 | 1950-1999 | Tiên Tôn | Kim Tiên |
| 40 | 2000-2049 | Tiên Vương | Kim Tiên |
| 41 | 2050-2099 | Vô Thượng Tiên Vương | Kim Tiên |
| 42 | 2100-2149 | Chuẩn Tiên Đế | Kim Tiên |
| 43 | 2150-2199 | Tiên Đế | Kim Tiên |
| 44 | 2200-2249 | Vô Thượng Tiên Đế | Kim Tiên |
| 45 | 2250-2299 | Bất Hủ Tiên Đế | Kim Tiên |
| 46 | 2300-2349 | Luyện Thần | Thần Giới |
| 47 | 2350-2399 | Nhập Thần | Thần Giới |
| 48 | 2400-2449 | Thần Đạo | Thần Giới |
| 49 | 2450-2499 | Thần Tôn | Thần Giới |
| 50 | 2500-2549 | Thần Vương | Thần Giới |
| 51 | 2550-2599 | Thần Đế | Thần Giới |
| 52 | 2600-2649 | Đế Chủ | Thần Giới |
| 53 | 2650-2699 | Chủ Tể | Thần Giới |
| 54 | 2700-2749 | Đại Chủ Tể | Thần Giới |
| 55 | 2750-2799 | Luân Hồi | Thần Giới |
| 56 | 2800-2849 | Sáng Thế | Thiên Đạo |
| 57 | 2850-2899 | Chu Thiên I | Thiên Đạo |
| 58 | 2900-2949 | Chu Thiên II | Thiên Đạo |
| 59 | 2950-2999 | Chu Thiên III | Thiên Đạo |
| 60 | 3000-3049 | Chu Thiên IV | Thiên Đạo |
| 61 | 3050-3099 | Chu Thiên V | Thiên Đạo |
| 62 | 3100-3149 | Chu Thiên VI | Thiên Đạo |
| 63 | 3150-3199 | Chu Thiên VII | Thiên Đạo |
| 64 | 3200-3249 | Chu Thiên VIII | Thiên Đạo |
| 65 | 3250-3299 | Chu Thiên IX | Thiên Đạo |
| 66 | 3300-3349 | Chu Thiên X | Thiên Đạo |
| 67 | 3350-3399 | Chu Thiên XI | Thiên Đạo |
| 68 | 3400-3449 | Chu Thiên XII-XV | Thiên Đạo |
| 69 | 3450-3499 | Thần Mệnh / Sáng Thế Chủ Tể | Thiên Đạo |
| 70 | 3500-3549 | Sáng Thế Thần Mệnh | Thiên Đạo |
| 71 | 3550-3599 | Nhập Thánh | Thánh Nhân |
| 72 | 3600-3649 | Chuẩn Thánh | Thánh Nhân |
| 73 | 3650-3699 | Thánh Nhân | Thánh Nhân |
| 74 | 3700-3749 | Hư Vô Thánh Nhân | Thánh Nhân |
| 75 | 3750-3799 | Vô Thượng Thánh Nhân | Thánh Nhân |
| 76 | 3800-3849 | Thiên Đạo Thánh Nhân | Thánh Nhân |
| 77 | 3850-3899 | Hỗn Nguyên Thánh Nhân | Thánh Nhân |
| 78 | 3900-3949 | Đại Đạo Thánh Nhân | Thánh Nhân |
| 79 | 3950-3999 | Đạo Tổ | Đạo Tối Cao |
| 80 | 4000-4049 | Chí Tôn | Đạo Tối Cao |
| 81-85 | 4050-4299 | Vô Ngã | Đạo Tối Cao |

### Stat tăng mỗi lần đột phá

| Cảnh Giới | HP | Công | Phòng | Ngũ Hành |
|---|---|---|---|---|
| Nhập Môn (0) | 30 | 2.5 | 2.5 | 2 |
| Luyện Thể (1) | 50 | 5 | 5 | 4 |
| Thối Thể (2) | 70 | 7.5 | 7.5 | 6 |
| Tụ Thân (3) | 90 | 10 | 10 | 8 |
| Tụ Khí (4) | 110 | 12.5 | 12.5 | 10 |
| Ngự Khí (5) | 130 | 15 | 15 | 12 |
| Luyện Khí (6) | 170 | 17.5 | 17.5 | 14 |
| Trúc Cơ (7) | 210 | 20 | 20 | 16 |
| Kim Đan (8) | 250 | 25 | 25 | 18 |
| Ngưng Anh (9) | 300 | 30 | 30 | 20 |
| Nguyên Anh (10) | 350 | 35 | 35 | 22 |
| Tụ Thần (11) | 400 | 40 | 40 | 24 |
| Hóa Thần (12) | 450 | 45 | 45 | 26 |
| Hợp Thể (13) | 500 | 50 | 50 | 28 |
| Hợp Đạo (14) | 550 | 55 | 55 | 30 |

---

## II. HỆ THỐNG LINH CĂN

### 22 loại linh căn đầy đủ

| ID | Tên | Phẩm | Hệ | ATK | DEF | HP | Chí Mạng | Hút Máu | Absorb % | Exp % | Pet ATK % | Pet HP % | Pet Exp % | Trọng Lượng |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Phàm Kim | Phàm | kim | 8 | 0 | 0 | 0 | 0 | -8 | 5 | 0 | 0 | 0 | 8 |
| 2 | Phàm Mộc | Phàm | moc | 0 | 0 | 15 | 0 | 0 | 10 | -5 | 0 | 10 | 0 | 8 |
| 3 | Phàm Thủy | Phàm | thuy | 2 | 2 | 2 | 0 | 0 | 12 | 8 | 0 | 0 | 0 | 8 |
| 4 | Phàm Hỏa | Phàm | hoa | 5 | 0 | 0 | 5 | 0 | -12 | 12 | 0 | 0 | 0 | 8 |
| 5 | Phàm Thổ | Phàm | tho | 0 | 10 | 8 | 0 | 0 | -3 | 3 | 0 | 0 | 0 | 8 |
| 6 | Kim Mộc | Nhị | kim | 10 | 0 | 12 | 0 | 0 | 5 | 10 | 0 | 0 | 0 | 5 |
| 7 | Thủy Hỏa | Nhị | thuy | 0 | 0 | 0 | 8 | 0 | 18 | -10 | 0 | 0 | 15 | 5 |
| 8 | Hỏa Thổ | Nhị | hoa | 12 | 5 | 0 | 0 | 0 | -15 | 15 | 0 | 0 | 0 | 5 |
| 9 | Mộc Thủy | Nhị | moc | 0 | 5 | 20 | 0 | 0 | 15 | 5 | 10 | 0 | 0 | 5 |
| 10 | Kim Hỏa | Nhị | kim | 15 | 0 | 0 | 3 | 0 | -10 | 8 | 0 | 0 | 0 | 5 |
| 11 | Kim Mộc Thủy | Tam | kim | 8 | 5 | 15 | 0 | 0 | 8 | 12 | 0 | 0 | 0 | 3 |
| 12 | Hỏa Thổ Kim | Tam | hoa | 18 | 10 | 0 | 0 | 0 | -18 | 20 | 20 | 0 | 0 | 3 |
| 13 | Thủy Mộc Hỏa | Tam | thuy | 0 | 0 | 25 | 5 | 0 | 20 | -12 | 0 | 0 | 15 | 3 |
| 14 | Kim Thủy Hỏa | Tam | kim | 12 | 0 | 0 | 8 | 0 | 10 | 10 | 0 | 0 | 0 | 3 |
| 15 | Mộc Thổ Thủy | Tam | moc | 0 | 15 | 20 | 0 | 0 | 12 | 8 | 0 | 25 | 0 | 3 |
| 16 | Kim Mộc Thủy Hỏa | Tứ | kim | 10 | 0 | 10 | 0 | 0 | 12 | 15 | 0 | 0 | 0 | 2.4 |
| 17 | Thổ Kim Mộc Hỏa | Tứ | tho | 20 | 15 | 15 | 0 | 0 | -20 | 25 | 25 | 0 | 0 | 2.4 |
| 18 | Thủy Mộc Hỏa Thổ | Tứ | thuy | 0 | 10 | 30 | 0 | 0 | 22 | -15 | 0 | 15 | 20 | 2.4 |
| 19 | Kim Thủy Hỏa Thổ | Tứ | kim | 15 | 8 | 0 | 10 | 0 | 8 | 12 | 0 | 0 | 0 | 2.4 |
| 20 | Kim Mộc Thổ Thủy | Tứ | moc | 8 | 8 | 10 | 0 | 0 | 15 | 10 | 10 | 10 | 0 | 2.4 |
| **21** | **Ngũ Hành Vô Cực** | **Niết** | **all** | 15 | 15 | 15 | 0 | 0 | **30** | **30** | 25 | 25 | 0 | 0.8 |
| **22** | **Ngũ Hành Nghịch Thiên** | **Niết** | none | 25 | 25 | 25 | 0 | 0 | **-30** | **-20** | 50 | 0 | 0 | 0.8 |

### Phân loại theo tier

| Tier | Tên Tier | Số loại | Trọng lượng (tỉ lệ roll) |
|---|---|---|---|
| 1 | Phàm | 5 | 8 mỗi loại |
| 2 | Nhị | 5 | 5 mỗi loại |
| 3 | Tam | 5 | 3 mỗi loại |
| 4 | Tứ | 5 | 2.4 mỗi loại |
| 5 | Niết | 2 | 0.8 mỗi loại |

### Phân loại theo mục đích tối ưu

| Mục đích | Linh căn tối ưu | Lý do |
|---|---|---|
| Tu luyện nhanh nhất | #21 Ngũ Hành Vô Cực | absorb +30%, exp +30% |
| Công kích mạnh nhất | #17 Thổ Kim Mộc Hỏa | ATK 20, exp +25% |
| Phòng thủ / HP cao | #18 Thủy Mộc Hỏa Thổ | HP +30, DEF +10, absorb +22% |
| Bạo kích | #14 Kim Thủy Hỏa | Crit +8% |
| Sủng vật mạnh | #22 Nghịch Thiên | Pet ATK +50% |
| Pet cứng cáp | #15 Mộc Thổ Thủy | Pet HP +25% |
| Hút máu pet | #7 Thủy Hỏa | Pet Exp +15% |

### Chi phí nâng linh căn

| Cấp | Từ → Đến | Linh Thạch | Đá Ngũ Hành | UEXP | Tỷ Lệ Thành Công |
|---|---|---|---|---|---|
| 1 | Phàm → Huyền | 500 | 10 | 1,000 | 100% |
| 2 | Huyền → Địa | 2,000 | 30 | 5,000 | 80% |
| 3 | Địa → Thiên | 8,000 | 80 | 20,000 | 60% |
| 4 | Thiên → Thánh | 25,000 | 200 | 80,000 | 40% |
| 5 | Thánh → Niết | 80,000 | 500 | 300,000 | 20% |
| **TỔNG** | | **115,500** | **820** | **406,000** | |

### Chi phí nâng linh căn thực tế (có thất bại)

| Cấp | Tỷ lệ thành công | Số lần thử trung bình | LT thực tế | Đá thực tế |
|---|---|---|---|---|
| 1 | 100% | 1.0 | 500 | 10 |
| 2 | 80% | 1.25 | 2,500 | 38 |
| 3 | 60% | 1.67 | 13,360 | 134 |
| 4 | 40% | 2.5 | 62,500 | 500 |
| 5 | 20% | 5.0 | 400,000 | 2,500 |
| **TỔNG TB** | | | **478,860** | **3,182** |

### Hệ tương khắc ngũ hành

| Tấn công | Khắc | Sát thương |
|---|---|---|
| Kim | Mộc | x1.2 (+20%) |
| Mộc | Thổ | x1.2 (+20%) |
| Thổ | Thủy | x1.2 (+20%) |
| Thủy | Hỏa | x1.2 (+20%) |
| Hỏa | Kim | x1.2 (+20%) |
| Bị khắc | | x0.85 (-15%) |
| Cùng hệ | | x1.0 |

---

## III. TU LUYỆN

### Công thức EXP/giây

```
base_exp_per_sec = ulv × vip_bonus × exp_multiplier × (1 + cp_absorb/100) × (1 + lc_absorb/100) × (1 + lc_exp_bonus/100)

exp_per_sec = base_exp_per_sec × (1 + tupoexp/100)
```

Trong đó:
- `ulv`: Cấp hiện tại
- `vip_bonus`: 1.20 nếu VIP, 1.00 nếu thường
- `exp_multiplier`: 1 (thường), 2 (Linh Thạch), 3 (Ma Thạch)
- `cp_absorb`: Tốc độ hấp thu từ Công Pháp (hiệu ứng absorb_speed)
- `lc_absorb`: Tốc độ hấp thu từ Linh Căn
- `lc_exp_bonus`: % exp bonus từ Linh Căn
- `tupoexp`: % bonus từ trang bị / cường hóa

### 3 chế độ tu luyện

| Chế Độ | Hệ Số EXP | Chi Phí/Phút | Thời Gian Max | Loại Tiền |
|---|---|---|---|---|
| Thường | x1 | 0 | 60 phút | Miễn phí |
| Linh Thạch x2 | x2 | 1 LT/phút | 360 phút (6h) | Linh thạch |
| Ma Thạch x3 | x3 | 1 MT/phút | 720 phút (12h) | Ma thạch |

### Tính toán thực tế

**Ví dụ: Level 100, VIP, Linh Căn absorb +15%, exp +10%, Công Pháp absorb +10%**

| Chế Độ | EXP/giây | EXP/phút | EXP/giờ | Max EXP/ngày |
|---|---|---|---|---|
| Thường (60 phút) | 100×1.2×1×1.15×1.1×1.1 = 166.4 | 9,986 | 599,160 | 599,160 |
| Linh Thạch (6h) | 100×1.2×2×1.15×1.1×1.1 = 332.8 | 19,970 | 1,198,320 | 7,189,920 |
| **Ma Thạch (12h)** | 100×1.2×3×1.15×1.1×1.1 = **499.2** | 29,955 | 1,797,320 | **21,567,840** |

**Ví dụ: Level 500, VIP, Linh Căn #21 Vô Cực (absorb +30%, exp +30%), Công Pháp Đại Đạo Thiên Thư (exp +45%)**

| Chế Độ | EXP/giây | EXP/giờ | Max EXP/ngày |
|---|---|---|---|
| Ma Thạch (12h) | 500×1.2×3×1.30×1.30×1.45 = **3,550** | 21,300,000 | **255,600,000** |

### Chi phí tu luyện Ma Thạch

| Thời Gian | Chi Phí MT |
|---|---|
| 1 giờ | 60 MT |
| 6 giờ | 360 MT |
| 12 giờ (max) | 720 MT |

### Chi phí tu luyện Linh Thạch

| Thời Gian | Chi Phí LT |
|---|---|
| 1 giờ | 60 LT |
| 6 giờ (max) | 360 LT |

---

## IV. ĐÁNH QUÁI PVE

### Công thức phần thưởng

```
linh_thach = round(glv × 22.5) + mt_rand(1,200)
```

- Nếu không gây được sát thương: LT = 0
- Nếu chênh lệch cấp >= 30: LT = 0

### EXP từ quái vật

```
final_exp = gexp / (lvc + 1)
```
Trong đó `gexp` từ config quái vật, `lvc` = chênh lệch cấp.

**LƯU Ý: Phần lớn quái vật trong config có `gexp = null` → KHÔNG rơi EXP.**

### Bảng rơi vật phẩm

| Loại | Tỉ lệ rơi | Điều kiện |
|---|---|---|
| Trang bị | dljv/120 (từ 0-120%) | Quái có gzb |
| Vật phẩm | djjv/180 (từ 0-180%) | Quái có gdj |
| Thuốc | ypjv/100 (từ 0-100%) | Quái có gyp |
| Võ công EXP | 5 EXP (min), max theo cấp quái | Có wugong trang bị |

### Ví dụ掉落 từ một số quái

| Quái | Cấp | HP | ATK | DEF |掉落 LT |掉落 Trang Bị |
|---|---|---|---|---|---|---|
| Giả Thôn Trường | 3 | 500 | 5 | 2 | ~118 | Không |
| Hồ Yêu | 1 | 500 | 1 | 1 | ~90 | Không |
| Sơn Trư | 1 | 240 | 10 | 14 | ~93 | #24 |
| Oán Linh | 30 | 1,900 | 968 | 625 | ~1,843 | #39 |
| Lục Nhĩ Mi Hầu (Boss) | 100 | 100,000 | 1,800 | 1,200 | ~4,250 | #2 |

---

## V. BOSS LINH CĂN

### Cấu hình spawn

| Thông số | Giá trị |
|---|---|
| Thời gian spawn lại | 120 phút |
| Số lần spawn/ngày | ~12 lần |

### Tính stats boss

```
Level = TB cấp tất cả người chơi có linh căn
HP = 5000 + avgLevel × 500
ATK = 200 + avgLevel × 20
DEF = 100 + avgLevel × 10
Crit = 20
Hút máu = 5
```

### Ví dụ stats theo cấp TB

| Cấp TB | HP | ATK | DEF |
|---|---|---|---|
| 10 | 10,000 | 400 | 200 |
| 50 | 30,000 | 1,200 | 600 |
| 100 | 55,000 | 2,200 | 1,100 |
| 200 | 105,000 | 4,200 | 2,100 |

### Tính sát thương lên boss

```
baseDmg = max(1, player_ATK - boss_DEF × 0.75)
damage = baseDmg × element_multiplier
```

Nếu bạo kích: `damage × 1.72`

### Phần thưởng boss

| Phần thưởng | Số lượng |
|---|---|
| Linh Thạch | 100-500 (random) |
| Đá Ngũ Hành | 3 (theo hệ boss) |
| Vật phẩm hiếm | 10% tỉ lệ rơi |

### 6 loại boss theo hệ

| Hệ | Tên Boss | Mô Tả |
|---|---|---|
| Kim | Kim Linh Tộc Trưởng | Phòng thủ cao, bạo kích mạnh |
| Mộc | Mộc Linh Vương | HP cao, hút máu mạnh |
| Thủy | Thủy Long Vương | Phòng thủ cao, hồi phục nhanh |
| Hỏa | Hỏa Phượng Hoàng | Sát thương cao, bạo kích mạnh |
| Thổ | Thổ Lân Thú | HP cực cao, phòng thủ vững |
| Không | Hư Không Ma Vương | Cân bằng mọi stats |

### Tổng boss linh căn/ngày

| | Min | Max |
|---|---|---|
| Linh Thạch | 1,200 | 6,000 |
| Đá Ngũ Hành | 36 | 36 |

---

## VI. TỎA YÊU THÁP

### Cấu trúc

- **Tổng:** 150 tầng
- **Boss mỗi tầng:** Tên random theo vòng lặp 6 boss
- **Boss names:** Yêu Linh Canh Cổng, Hắc Viêm Xà Vương, Thạch Cốt Ma Tướng, U Minh Quỷ Tướng, Xích Luyện Hồ Ly, Thiên Độc Ma Tôn

### Stats boss theo tầng

```
Level = floor + 8
HP = 520 + (floor × 170) + (floor² × 18)
ATK = 42 + (floor × 15) + (floor^1.25 × 4)
DEF = 18 + (floor × 9) + (floor^1.18 × 3)
Crit = min(35, 3 + floor/8)
Hút máu = min(25, floor/10)
```

### Bảng stats boss mẫu

| Tầng | Level | HP | ATK | DEF | Crit | Hút Máu |
|---|---|---|---|---|---|---|
| 1 | 9 | 708 | 61 | 30 | 3% | 0% |
| 10 | 18 | 2,400 | 206 | 118 | 4% | 1% |
| 25 | 33 | 16,445 | 497 | 292 | 6% | 2% |
| 50 | 58 | 58,220 | 1,007 | 553 | 9% | 5% |
| 75 | 83 | 131,795 | 1,604 | 831 | 12% | 7% |
| 100 | 108 | 237,120 | 2,278 | 1,125 | 15% | 10% |
| 125 | 133 | 384,095 | 3,019 | 1,434 | 18% | 12% |
| 150 | 158 | 572,720 | 3,825 | 1,759 | 21% | 15% |

### Phần thưởng mỗi tầng thắng

```
EXP = 140 + (floor × 42) + (floor² × 2.2)
Võ Công EXP = 8 + (floor × 1.6)
```

### Bảng phần thưởng

| Tầng | EXP Nhận | Võ Công EXP |
|---|---|---|
| 1 | 184 | 10 |
| 10 | 740 | 24 |
| 25 | 2,665 | 48 |
| 50 | 7,650 | 88 |
| 75 | 16,340 | 128 |
| 100 | 29,200 | 168 |
| 125 | 46,390 | 208 |
| 150 | 68,960 | 248 |

### Tổng phần thưởng 150 tầng

| Phần thưởng | Tổng |
|---|---|
| EXP | ~3,152,500 |
| Võ Công EXP | ~19,200 |
| Chiến đấu | 150 trận |

### Công thức combat Tỏa Yêu Tháp

```
// Player tấn công:
damage = max(1, round(player_ATK - boss_DEF × 0.62))
if (damage < player_ATK × 0.12) damage = max(1, round(player_ATK × 0.12))
// Crit: mt_rand(1,100) <= min(75, player_CRIT)
if (crit) damage = round(damage × 1.65)
// Hút máu:
heal = max(0, round(damage × player_LIFESTEAL / 220))

// Boss tấn công:
bossDamage = max(1, round(boss_ATK - player_DEF × 0.68))
if (bossDamage < boss_ATK × 0.12) bossDamage = max(1, round(boss_ATK × 0.12))
// Boss crit: mt_rand(1,100) <= boss_CRIT
if (bossCrit) bossDamage = round(bossDamage × 1.5)
// Boss hút máu:
bossHeal = max(0, round(bossDamage × boss_LIFESTEAL / 240))
boss_hp += bossHeal (capped tại max HP)
```

**Giới hạn:** Tối đa 40 hiệp

---

## VII. ĐẤU TRƯỜNG PVP

### Cấu trúc

- Điểm khởi đầu: 1,000
- Đối thủ: score ± 200
- Reset hàng ngày lúc 21:00

### Bảng phần thưởng hạng/ngày

| Hạng | Linh Thạch | Ma Thạch |
|---|---|---|
| **#1** | **10,000** | **100** |
| #2 | 8,500 | 85 |
| #3 | 7,200 | 72 |
| #4 | 6,000 | 60 |
| #5 | 5,000 | 50 |
| #6 | 4,200 | 42 |
| #7 | 3,500 | 35 |
| #8 | 2,800 | 28 |
| #9 | 2,200 | 22 |
| #10 | 1,600 | 16 |

### Tổng phần thưởng Top 10

| | Linh Thạch | Ma Thạch |
|---|---|---|
| Tổng Top 10/ngày | 51,000 | 510 |

---

## VIII. NHIỆM VỤ

### Nhiệm Vụ Tuần Linh Căn (5 nhiệm)

| # | Tên Nhiệm | Điều Kiện | LT | Đá |
|---|---|---|---|---|
| 1 | Tu luyện cần mẫn | Tu luyện 60 phút | 100 | 2 |
| 2 | Diệt trừ yêu ma | Giết 10 quái | 100 | 2 |
| 3 | Luyện đan tu thân | Sử dụng 3 đạo cụ | 100 | 2 |
| 4 | Nuôi thú thành tài | Nhận EXP sủng vật | 100 | 2 |
| 5 | Vượt ải diệt boss | Đánh bại 1 boss | 100 | 2 |
| **TỔNG** | | | **500** | **10** |

### Nhiệm Vụ Chính Tuyến (một số nhiệm vụ tiêu biểu)

| ID | Tên Nhiệm | EXP | LT | Trang Bị | Yêu Cầu |
|---|---|---|---|---|---|
| 13 | Sơn trư họa loạn | 100 | 120 | #24 | Lv 56 |
| 14 | Thu tập phong mật | 200 | 100 | #23 | Lv 8 |
| 19 | Man! | 500 | 500 | #38 | Lv 76 |
| 20 | Sát! | 600 | 400 | #39 | Lv 77 |
| 21 | Xích lân thú bì | 800 | 350 | #39 | Lv 82 |
| 27 | Đồ tận yêu vương | 2,000 | 2,000 | #45 | Lv 12 |
| 31 | Tàn mặc thu tập | 2,500 | 1,500 | #49 | Lv 14 |
| 32 | Thủy quỷ nháo sự | 1,000 | 1,500 | #51 | Lv 54 |
| 36 | Mua dược phẩm | 800 | 1,800 | - | Lv 9 |
| 37 | Vương lão ngũ | 1,000 | 1,500 | - | Lv 15 |
| 38 | Môn phái | 1,000 | 1,500 | - | Lv 13 |
| 40 | Phù lục đoái hoán | 2,000 | 2,000 | #34 | Lv 6 |

---

## IX. ĐỘT PHÁ

### Chi phí linh thạch

```
// Tiểu cảnh giới (level % 50 != 0):
tpls = ulv × (ulv + 1) × 2

// Đại cảnh giới (level % 50 == 0):
tpls = ulv³ × 2
```

### Ví dụ chi phí

| Level | Loại Đột Phá | Chi Phí LT |
|---|---|---|
| 10 | Tiểu | 220 |
| 25 | Tiểu | 1,300 |
| 50 | **Đại** | **250,000** |
| 100 | **Đại** | **2,000,000** |
| 200 | **Đại** | **16,000,000** |
| 500 | **Đại** | **250,000,000** |

### Tỷ lệ thành công

```
basesjs = 100 - ulv × (jingjieValue + 1)
sjs = 100 (ban phước thiên địa)
psjs = 10 (nếu level % 50 == 0)
zbsjs = bonus từ trang bị
rate = basesjs + sjs + psjs + zbsjs
```

### Bảng kết quả đột phá

| Kết Quả | Tỉ Lệ | Mô Tả |
|---|---|---|
| **Thành công** | ~85-99% | Level +1, nhận Công/Phòng/HP/Thiên phú +5 |
| Thất bại | 1-15% | Mất linh thạch, giữ nguyên level |
| Đạo tâm dao động | 1% | EXP × 0.5 |
| Đạo tâm không vững | 1% | EXP × 0.25 |
| Thiên lôi giáng xuống | 1% | EXP = 0, giữ nguyên level |
| Dục tốc bất đạt | 1% | **Level - 1**, EXP = 0 |
| Kỳ tích xuất hiện | 3% | Hiển thị Công +999, Phòng +999, HP +999 (chỉ thông báo, KHÔNG ghi DB — troll player) |

### Stat tăng khi đột phá thành công

**Tiểu cảnh giới:**
```
HP = 2 + round(uhp/20)
Công = 1 + round(ugj/100)
Phòng = 1 + round(ufy/100)
```

**Đại cảnh giới:**
```
HP = 4 + round(ulv/1.2)
Công = 2 + round(ulv/2.5)
Phòng = 3 + round(ulv/2)
```

---

## X. TRANG BỊ & CƯỜNG HÓA

### Thuộc tính trang bị từ config

| Thuộc Tính | Ký Hiệu | Mô Tả |
|---|---|---|
| Công kích | zbgj | ATK cộng thêm |
| Phòng ngự | zbfy | DEF cộng thêm |
| Khí huyết | zbhp | HP cộng thêm |
| Bạo kích | zbbj | Crit cộng thêm |
| Hút máu | zbxx | Lifesteal cộng thêm |

### Thần Trang Set Bonus

| Số Mảnh | Danh Hiệu | HP% | ATK% | DEF% | Crit% |
|---|---|---|---|---|---|
| 4 | Sơ thành | 0% | 0% | 0% | 0% |
| 8 | Trung thành | 1% | 1% | 0% | 0% |
| 12 | Đại thành | 2% | 2% | 1% | 0% |
| 16 | Hoàn chỉnh | 3% | 3% | 1% | 1% |

### Công thức tính Thần Trang power

```
shenzhuang_power = addHp + (addGj × 10) + (addFy × 8)
```

### Cường Hóa

**Chi phí:**
```
cost_items = qianghua × 3 + 1 (type 1 = linh thạch)
```

**Tỷ lệ thành công:**
```
upint = round(zbsx × 0.08) (min 1)
// Kiểm tra 1: mt_rand(1,35) <= qianghua → THẤT BẠI
// Kiểm tra 2: mt_rand(1,30) >= qianghua → THÀNH CÔNG
```

**Giới hạn:** Cường hóa không vượt cấp người chơi

---

## XI. VÕ CÔNG

### Hệ thống

- Trang bị 1 võ công tại một thời điểm
- Kinh nghiệm nhận từ đánh quái
- Tốc độ: max(intval(guaiwu_glv), round(wgxlmax × 0.01)) mỗi quái

### Các loại hiệu ứng võ công

| Hiệu ứng | Mô Tả |
|---|---|
| damage_reduce | Giảm sát thương nhận vào |
| control_reduce_damage | Kiểm soát + giảm sát thương |
| pierce_def / break_armor | Xuyên phòng ngự |
| combo_hit | Liên kích (tỷ lệ %) |
| counter_lifesteal | Thêm hút máu theo % sát thương |
| steal_reward | Tỷ lệ nhận thêm linh thạch |
| drop_bonus | Tăng tỉ lệ rơi vật phẩm |
| exp_bonus | Tăng EXP nhận được |

---

## XII. CÔNG PHÁP

### 12 loại công pháp

| ID | Tên | Hiệu ứng | Giá trị | Max Level | Yêu Cầu |
|---|---|---|---|---|---|
| 1 | Hỗn Nguyên Tâm Pháp | absorb_speed | +5% / lvl | 10 | Lv 0 |
| 2 | Thiên Địa Quyết | exp_bonus | +3% / lvl | 10 | Realm 6 |
| 3 | Vạn Vật Quy Linh | drop_rate | +3% / lvl | 10 | Realm 7 |
| 4 | Tụ Bảo Huyền Công | stone_bonus | +5% / lvl | 10 | Realm 5 |
| 5 | Khoáng Mạch Thần Quyết | mining_bonus | +5% / lvl | 10 | Realm 5 |
| 6 | Hồi Xuân Quyết | heal_bonus | +3% / lvl | 10 | Realm 6 |
| 7 | Phá Thiên Quyết | crit_dmg | +2% / lvl | 10 | Realm 8 |
| 8 | Bất Động Minh Vương | def_bonus | +3% / lvl | 10 | Realm 11 |
| 9 | Mệnh Huyết Quyết | hp_regen | +1% / lvl | 10 | Realm 10 |
| 10 | Vô Cực Tản | dmg_reduce | +2% / lvl | 10 | Realm 13 |
| 11 | Thần Hành Bách Biến | absorb_speed | +8% / lvl | 10 | Realm 15 |
| 12 | Đại Đạo Thiên Thư | exp_bonus | +5% / lvl | 10 | Realm 25 |

### Chi phí gacha công pháp

- **200 Ma Thạch** mỗi lần quay

### Công thức level up công pháp

```
cpexpmax = max(100, prev_max × 1.3)
```

### Số slot công pháp theo cảnh giới

| Cảnh Giới | Số Slot |
|---|---|
| 0-6 (Nhập Môn → Ngự Khí) | 1 |
| 7-11 (Luyện Khí → Nguyên Anh) | 2 |
| ≥12 (Tụ Thần → Đại Thừa) | 3 |

### Phạt khi dùng absorb_speed

```
penalty_atk_percent = 0.4-0.5 per level (capped at 80%)
penalty_def_percent = 0.4-0.5 per level (capped at 80%)
```

---

## XIII. LUYỆN ĐAN & CHẾ TÁO

### Tỷ lệ thành công luyện đan

```
finalRate = min(95, max(5, base_success_rate + element_bonus))
```

### Bonus hệ linh căn khi luyện đan

| Hệ | Bonus |
|---|---|
| Hỏa | +10% |
| Kim | +15% |
| Mộc | +5% |
| Thổ | +8% |
| Tất cả (all) | +10% |

### Quy tắc

- Thất bại → **KHÔNG** mất nguyên liệu
- Thành công → Mất nguyên liệu, nhận sản phẩm

---

## XIV. Sủng Vật

### Stats tạo mới

```
HP: 100
ATK: 6 + random(0-15)
DEF: random(0-15)
```

### Stats mỗi lần lên level

```
HP: random(8-25)
ATK: random(2-5)
DEF: random(3-8)
```

### Phẩm chất sủng vật (cwpz)

| Điểm | Phẩm Chất | Hệ Số Stats |
|---|---|---|
| < 200 | Bình thường (0) | x1.0 |
| 200-349 | Lãng phí (1) | x1.1 |
| 350-429 | Phàm (2) | x1.2 |
| 430-469 | Nhị (3) | x1.3 |
| 470-494 | Tam (4) | x1.4 |
| 495+ | Nghịch thiên (5) | x1.5 |

### EXP cần cho sủng vật

```
cwmaxexp = cwnextlv × (cwnextlv + round(cwnextlv/2)) × 50 × rangesexp[realm_index] + cwnextlv
```

> **Lưu ý:** Hệ số 50× so với người chơi là 10× → sủng vật cần **5 lần** EXP người chơi.

### Chi phí quay sủng vật

- **200 Linh Thạch** mỗi lần

### Equipment sủng vật

- 7 slot: tool1-tool7

---

## XV. KỸ NĂNG LINH CĂN

| ID | Tên Kỹ Năng | Hệ | Loại | Hệ Số | Thời Gian | Yêu Cầu Level | Cooldown | MP Cost |
|---|---|---|---|---|---|---|---|---|
| 1 | Hỏa Diệm Chưởng | hoa | Damage | 150% ATK | Instant | 30 | 5 phút | 10% HP |
| 2 | Thủy Hộ Thuật | thuy | Heal | 8% HP/lượt | 3 lượt | 30 | 5 phút | 10% HP |
| 3 | Mộc Linh Thuật | moc | Buff | +25% hút máu | 3 lượt | 35 | 5 phút | 10% HP |
| 4 | Kim Cương Chưởng | kim | Damage+Debuff | +40% chí mạng | Instant | 40 | 5 phút | 10% HP |
| 5 | Thổ Hộ Thuật | tho | Buff | +30% phòng ngự | 3 lượt | 45 | 5 phút | 10% HP |

---

## XVI. THIÊN PHÚ

### 7 chỉ số thiên phú và hệ số

| Thiên Phú | Trường | Hệ Số → Stat |
|---|---|---|
| Công Kích | tfgj | ×5 → ugj |
| Chí Mạng | tfbj | ×1.5 → ubj |
| Hút Máu | tfxx | ×2 → uxx |
| Khí Huyết | tfhp | ×10 → umaxhp |
| Phòng Ngự | tffy | ×5 → ufy |
| May Mắn | tfxy | Dùng cho rơi vật phẩm |
| Né Tránh | tfsb | Dùng cho né tránh |

**Mỗi lần đột phá:** +5 thiên phú điểm

---

## XVII. TỔNG THU NHẬP TỐI ĐA MỖI NGÀY

### Người Chơi Free (Không nạp tiền)

| Nguồn | Linh Thạch | Ma Thạch | EXP |
|---|---|---|---|
| Tu luyện thường (60 phút, Lv100) | 0 | 0 | ~600,000 |
| Đánh quái (50 quái Lv50) | ~66,250 | 0 | ~0 (gexp null) |
| Boss linh căn (6 boss, 12h) | 600-3,000 | 0 | 0 |
| Nhiệm vụ tuần | ~71/ngày | 0 | ~700/ngày |
| **TỔNG/ngày** | **~67,000-70,000** | **0** | **~600,700** |

### Người Chơi VIP + Ma Thạch (Tối đa)

| Nguồn | Linh Thạch | Ma Thạch | EXP |
|---|---|---|---|
| Tu luyện MT×3 (12h, Lv100) | 0 | -720 | **~21,500,000** |
| Đánh quái (200 quái Lv100) | ~490,000 | 0 | ~0 |
| Boss linh căn (12 boss) | 1,200-6,000 | 0 | 0 |
| Hạng #1 Đấu trường | 10,000 | 100 | 0 |
| Nhiệm vụ tuần | ~71/ngày | 0 | ~700/ngày |
| **TỔNG/ngày** | **~501,300-506,100** | **-620** | **~21,500,700** |

### Người Chơi VIP + Ma Thạch (Lv500, Linh Căn #21, Công Pháp Đại Đạo)

| Nguồn | Linh Thạch | Ma Thạch | EXP |
|---|---|---|---|
| Tu luyện MT×3 (12h) | 0 | -720 | **~255,600,000** |
| Đánh quái (200 quái Lv500) | ~2,250,000 | 0 | ~0 |
| Boss linh căn | 1,200-6,000 | 0 | 0 |
| Hạng #1 Đấu trường | 10,000 | 100 | 0 |
| **TỔNG/ngày** | **~2,261,200-2,266,000** | **-620** | **~255,600,000** |

---

## XVIII. PHÂN TÍCH TỐC ĐỘ LÊN CẢNH GIỚI

### EXP cần thiết tích lũy từ Lv0 đến các mốc

| Đến Level | EXP Tích Lũy Ước Tính | Ghi Chú |
|---|---|---|
| Lv 50 | ~2,000,000 | Nhập Môn → Luyện Thể |
| Lv 100 | ~20,000,000 | Thối Thể |
| Lv 200 | ~200,000,000 | Tụ Khí |
| Lv 300 | ~1,000,000,000 | Luyện Khí |
| Lv 500 | ~15,000,000,000 | Nguyên Anh |
| Lv 1000 | ~500,000,000,000 | Thánh Tôn |

### Thời gian ước tính (12h Ma Thạch/ngày, Lv100, TB absorb/exp)

| Linh Căn | EXP/giây | EXP/ngày | Thời Gian Lv0→100 | Thời Gian Lv0→500 |
|---|---|---|---|---|
| #21 Vô Cực | 499 | 21.5M | ~1 ngày | ~35 ngày |
| #17 Thổ Kim Mộc Hỏa | 396 | 17.1M | ~1.2 ngày | ~44 ngày |
| #12 Hỏa Thổ Kim | 376 | 16.3M | ~1.3 ngày | ~46 ngày |
| #8 Hỏa Thổ | 353 | 15.3M | ~1.4 ngày | ~49 ngày |
| #4 Phàm Hỏa | 333 | 14.4M | ~1.5 ngày | ~52 ngày |
| #3 Phàm Thủy | 319 | 13.8M | ~1.5 ngày | ~54 ngày |
| #1 Phàm Kim | 266 | 11.5M | ~1.8 ngày | ~65 ngày |
| #22 Nghịch Thiên | 239 | 10.3M | ~2 ngày | ~73 ngày |

> **Lưu ý:** Đây là ước tính lý thuyết, thực tế còn phụ thuộc vào trang bị, công pháp, cường hóa, v.v.

---

## XIX. SO SÁNH LINH CĂN

### Bảng xếp hạng theo từng mục đích

#### 1. Tu luyện nhanh (EXP/giây cao nhất)

| Hạng | Linh Căn | EXP Bonus | Absorb | EXP/giây (Lv100, MT×3) |
|---|---|---|---|---|
| 1 | #21 Ngũ Hành Vô Cực | +30% | +30% | 499 |
| 2 | #17 Thổ Kim Mộc Hỏa | +25% | -20% | 396 |
| 3 | #12 Hỏa Thổ Kim | +20% | -18% | 376 |
| 4 | #8 Hỏa Thổ | +15% | -15% | 353 |
| 5 | #3 Phàm Thủy | +8% | +12% | 319 |
| 6 | #9 Mộc Thủy | +5% | +15% | 303 |
| 7 | #6 Kim Mộc | +10% | +5% | 300 |
| 8 | #20 Kim Mộc Thổ Thủy | +10% | +15% | 300 |
| 9 | #4 Phàm Hỏa | +12% | -12% | 299 |
| 10 | #14 Kim Thủy Hỏa | +10% | +10% | 293 |

#### 2. Công kích cao nhất (PVE/PVP)

| Hạng | Linh Căn | ATK Base | Chí Mạng | Ghi Chú |
|---|---|---|---|---|
| 1 | #17 Thổ Kim Mộc Hỏa | 20 | 0 | ATK cao nhất |
| 2 | #12 Hỏa Thổ Kim | 18 | 0 | ATK + Pet ATK |
| 3 | #10 Kim Hỏa | 15 | 3 | ATK + Crit |
| 4 | #19 Kim Thủy Hỏa Thổ | 15 | 10 | ATK + Crit cao |
| 5 | #1 Kim Mộc Thủy Hỏa | 10 | 0 | Cân bằng |
| 6 | #22 Nghịch Thiên | 25 | 0 | ATK base cao nhất! |

#### 3. Phòng thủ / Tank

| Hạng | Linh Căn | DEF | HP | Absorb | Ghi Chú |
|---|---|---|---|---|---|
| 1 | #18 Thủy Mộc Hỏa Thổ | 10 | 30 | +22% | HP +30, Trâu nhất |
| 2 | #15 Mộc Thổ Thủy | 15 | 20 | +12% | DEF + Pet HP |
| 3 | #2 Phàm Mộc | 0 | 15 | +10% | Pet HP +10% |
| 4 | #5 Phàm Thổ | 10 | 8 | -3% | DEF cao tier 1 |
| 4 | #9 Mộc Thủy | 5 | 20 | +15% | Absorb tốt |

#### 4. Bạo kích

| Hạng | Linh Căn | Chí Mạng | ATK | Ghi Chú |
|---|---|---|---|---|
| 1 | #19 Kim Thủy Hỏa Thổ | 10 | 15 | Crit cao nhất |
| 2 | #14 Kim Thủy Hỏa | 8 | 12 | Crit cao + absorb |
| 3 | #7 Thủy Hỏa | 8 | 0 | Crit + Pet Exp |
| 4 | #4 Phàm Hỏa | 5 | 5 | Crit tier 1 |
| 5 | #10 Kim Hỏa | 3 | 15 | Crit + ATK |

#### 5. Sủng vật mạnh

| Hạng | Linh Căn | Pet ATK | Pet HP | Pet Exp | Ghi Chú |
|---|---|---|---|---|---|
| 1 | #22 Nghịch Thiên | +50% | 0 | 0 | Pet ATK cực mạnh |
| 2 | #17 Thổ Kim Mộc Hỏa | +25% | 0 | 0 | Pet ATK cao |
| 3 | #12 Hỏa Thổ Kim | +20% | 0 | 0 | Pet ATK + Player ATK |
| 4 | #15 Mộc Thổ Thủy | 0 | +25% | 0 | Pet HP cao |
| 5 | #7 Thủy Hỏa | 0 | 0 | +15% | Pet lên nhanh |
| 6 | #18 Thủy Mộc Hỏa Thổ | 0 | +15% | +20% | Pet HP + Exp |
| 7 | #9 Mộc Thủy | +10% | 0 | 0 | Pet ATK nhỏ |

---

## XX. LƯU Ý QUAN TRỌNG

### 1. EXP Quái Vật

Phần lớn quái vật trong `guaiwu_config.json` có `gexp = null` → **KHÔNG rơi EXP** khi đánh quái. Chỉ có một số boss特别 (Hồ Yêu id=53 có gexp=9000, Lục Nhĩ Mi Hầu id=101 gexp=null).

**Nguồn EXP chính:**
- Tu luyện (chế độ ngồi thiền)
- Tỏa Yêu Tháp
- Nhiệm vụ chính tuyến
- Công Pháp exp_bonus

### 2. Tu Luyện Free Hạn Chế

- Tối đa 60 phút/ngày cho chế độ miễn phí
- Phải dùng Linh Thạch/Ma Thạch để tu luyện dài hơn
- VIP bonus +20% là rất quan trọng

### 3. Boss Linh Căn

- Spawn 2h/lần → tối đa 12 boss/ngày
- Mỗi boss chỉ cho 100-500 LT + 3 đá ngũ hành
- Boss damage formula: `max(1, player_ATK - boss_DEF × 0.75) × element_multiplier`

### 4. Đột Phá

- Càng lên cao, tỷ lệ thành công càng thấp
- Đại cảnh giới (level % 50 == 0) tốn rất nhiều LT
- Có 3% tỷ lệ "Kỳ tích" (+999 stat) — chỉ hiển thị, không ghi DB (troll event)
- Có 4% tỷ lệ thất bại nghiêm trọng (mất level/exp)

### 5. Nghịch Thiên Linh Căn

- Pet ATK +50% nhưng bản thân suffer nặng
- Absorb -30%, Exp -20% → tu luyện rất chậm
- Chỉ phù hợp build宠物 mạnh

### 6. Kim Thủy Hỏa Thổ (Crit Build)

- Crit +10%, ATK +15 → damage cao nhất
- Nhưng HP penalty -15% → rất yếu máu
- Phù hợp cho người chơi biết né tránh

### 7. Chi phí ẩn

- Teleport: `ulv × 12 + 500` LT
- Công Pháp gacha: 200 MT/lần
- Sủng vật gacha: 200 LT/lần
- Cường hóa: `qianghua × 3 + 1` items

### 8. Gacha (Vòng Quay May Mắn)

- Chi phí: `czsl = 100 + uyxb × (7-sjs)/10 + 2000`
- Chỉ hoạt động khi `uyxb <= 20,000` (tức là phải nghèo mới quay được)

### 9. Đăng Ký

- Hệ thống `qiandao.php` (đăng nhập hàng ngày) **chưa được triển khai** (file rỗng)

### 10. offline Tu Luyện

- Thời gian offline tối đa: 480 phút (8 giờ)
- EXP công thức: `exp_per_min = 10 + ulv × 2` (theo code)

---

## BẢNG TÓM TẮT TOÀN DIỆN

| Hệ Thống | Nguồn Thu Nhập Chính | Tối Đa/Ngày (Ước Tính) |
|---|---|---|
| Tu Luyện | EXP | 21.5M-255M (tùy level/linh căn) |
| Đánh Quái | Linh Thạch, Vật phẩm | 50K-2.2M LT |
| Boss Linh Căn | LT, Đá Ngũ Hành | 1.2K-6K LT, 36 đá |
| Tỏa Yêu Tháp | EXP, Võ Công | 3.15M EXP (1 lần phá 150 tầng) |
| Đấu Trường | LT, MT | 1.6K-10K LT, 16-100 MT |
| Nhiệm Vụ Tuần | LT, Đá | 500 LT, 10 đá/tuần |
| Nhiệm Vụ Chính Tuyến | EXP, LT, Trang Bị | Tùy nhiệm vụ |
| Luyện Đan | Thuốc, Đan Dược | Tùy nguyên liệu |
| Chế Tạo | Trang Bị | Tùy nguyên liệu |
| Sủng Vật | Combat Support | - |

---

*File này được tạo tự động từ phân tích source code game Xích Diệm Thiên.*
*Đường dẫn: D:\XXJserver\GAME_BALANCE_ANALYSIS.md*
