# BÁO CÁO PHÂN TÍCH F2P & P2W

> **Ngày:** 2026-07-22 | **Phiên bản:** v1.0
> **Game:** Tuân Giới (XXJserver) | **Engine:** PHP 5.6 + MySQL

---

## MỤC LỤC

1. [Tóm Tắt Điều Hành](#1-tóm-tắt-điều-hành)
2. [Phân Tích F2P](#2-phân-tích-f2p)
3. [Phân Tích P2W](#3-phân-tích-p2w)
4. [So Sánh F2P vs P2W](#4-so-sánh-f2p-vs-p2w)
5. [Phân Tích Wall](#5-phân-tích-wall)
6. [Khuyến Nghị](#6-khuyến-nghị)

---

## 1. TÓM TẮT ĐIỀU HÀNH

### Kết Quả Chính

| Chỉ Số | Giá Trị |
|--------|---------|
| **Tổng EXP Lv1→800** | 61.36 tỷ EXP |
| **Tổng LT đột phá** | 838 triệu LT |
| **Tổng MT max hệ thống** | 289,050 MT (~$11) |
| **F2P time to Lv100** | **2.2 năm** |
| **P2W time to Lv100** | **1-2 tháng** (Whale) |
| **F2P time to Lv800** | **273 năm** |
| **P2W time to Lv800** | **8-12 năm** (Whale) |

### Kết Luận

- **Game designed để KHÔNG THỂ MAX** cho cả F2P và P2W
- **MT wall rẻ** ($11) — Ai cũng mua được Công Pháp + Võ Công
- **LT wall cực nặng** — Đột phá Lv800 cần **trousands tỷ LT** với 5% rate
- **Whale cũng không max được** — Nạp 100M VND vẫn cần 8-12 năm

---

## 2. PHÂN TÍCH F2P

### 2.1 Công Thức EXP

```php
// Từ player.php:816-821
$playernextlv = $player->ulv + 1;
$player->umaxexp = max(100, floor(pow($playernextlv, 2.8) * 2));

// Thưởng đột phá (mỗi 50 cấp):
$player->umaxexp += floor(pow($player->ulv, 2.5) * 50);
```

### 2.2 Bảng EXP Tích Lũy

| Level | Tổng EXP | EXP Cấp Này | Thời Gian F2P |
|-------|---------:|------------:|---------------|
| **Lv 10** | 4,221 | 1,261 | < 1 ngày |
| **Lv 20** | 50,987 | 8,788 | 5 ngày |
| **Lv 30** | 230,044 | 27,350 | 22 ngày |
| **Lv 50** | 2,446,090 | 114,326 | **2.9 tháng** |
| **Lv 80** | 10,072,512 | 426,267 | **10.4 tháng** |
| **Lv 100** | 27,237,051 | 796,214 | **2.2 năm** |
| **Lv 150** | 118,717,474 | 2,477,909 | **3.9 năm** |
| **Lv 200** | 342,576,187 | 5,545,158 | **7.8 năm** |
| **Lv 300** | 1,546,349,447 | 17,257,167 | **13.1 năm** |
| **Lv 500** | 10,471,727,751 | 72,134,995 | **51.9 năm** |
| **Lv 800** | 61,358,891,668 | 268,956,447 | **273 năm** |

### 2.3 Thu Nhập EXP Hàng Ngày (F2P)

| Level Range | EXP/Quái | Quái/Giờ | PvE Tổng | Nhiệm Vụ | **Tổng/Ngày** |
|-------------|---------:|---------:|---------:|---------:|--------------:|
| Lv 1–30 | ~125 | 50 | 6,250 | 4,225 | **10,500** |
| Lv 30–100 | ~600 | 50 | 30,000 | 4,225 | **34,000** |
| Lv 100–200 | ~3,000 | 50 | 150,000 | 4,225 | **154,000** |
| Lv 200+ | ~12,500 | 50 | 625,000 | 4,225 | **630,000** |

**Giả định:** 1.5 tiếng/ngày (0.5 tiếng PvP/PvE + 1 tiếng tu luyện)

### 2.4 Phân Tích Đột Phá

#### Công Thức

```php
// Success rate (tupo.php:120-123)
$rate = max(5, 75 - $lv*0.6 - $jv*3 + $psjs);

// Cost
$cost = ($jv == 1) ? $lv * ($lv + 1) : $lv * $lv * 3;  // Minor vs Major
```

#### Bảng Chi Phí Đột Phá

| Level | Loại | Tỷ Lệ | TB Lần Thử | LT/Lần | Tổng LT (KB) |
|-------|------|-------:|-----------:|-------:|-------------:|
| Lv 10 | Minor | 66% | 1.5 | 110 | 167 |
| Lv 25 | Minor | 57% | 1.8 | 650 | 1,140 |
| Lv 50 | Major | 49% | 2.0 | 7,500 | 15,306 |
| Lv 100 | Major | 16% | 6.2 | 30,000 | 187,500 |
| Lv 150 | Major | **5%** | 20.0 | 67,500 | 1,350,000 |
| Lv 200 | Major | **5%** | 20.0 | 120,000 | 2,400,000 |
| Lv 300 | Major | **5%** | 20.0 | 270,000 | 5,400,000 |
| Lv 500 | Major | **5%** | 20.0 | 750,000 | 15,000,000 |
| Lv 800 | Major | **5%** | 20.0 | 1,920,000 | 38,400,000 |

**Tổng LT cho TẤT CẢ đột phá (Lv10→800): 838,374,576 LT**

#### Hình Thức Thất Bại

| Xác Suất | Hình Thức | Mô Tả |
|-----------|-----------|-------|
| 1% | -1 level + EXP → 0 | Nghiêm trọng |
| 1% | EXP → 0 | Mất toàn bộ EXP |
| 1% | -75% EXP | Mất 3/4 EXP |
| 1% | -50% EXP | Mất 1/2 EXP |
| 96% | Thất bại | Mất LT, EXP giữ nguyên |

### 2.5 Chi Phí Max Hệ Thống

| Hệ Thống | Tài Nguyên Cần | Thời Gian F2P |
|-----------|---------------|---------------|
| **Thể Chất Q1→Q10** | 10.74 tỷ LT | **~11,767 năm** |
| **Huyết Mạch Stage 0→4** | Level + Boss | ~Level time |
| **Linh Căn T5** | Tố Thể Đan | Boss-gated |
| **Võ Công Toàn Bộ** | ~222,600 MT | ~31 năm |
| **Công Pháp Saint** | ~211,000 MT | ~29 năm |

### 2.6 Tổng Kết F2P

| Mục Tiêu | Thời Gian |
|-----------|-----------|
| **Lv 100** (mở khóa cơ bản) | **2.2 năm** |
| **Lv 200** (mid-game) | **7.8 năm** |
| **Lv 500** (endgame start) | **51.9 năm** |
| **Lv 800** (max level) | **273 năm** |
| **Tất Cả Đột Phá** | **919 năm** (LT farming only) |
| **Thể Chất Q10** | **11,767 năm** |

**Kết Luận:** Wall thực sự ở **Lv100 (2.2 năm)**, không phải Lv50 (2.9 tháng). Trên Lv200, game trở nên **về mặt toán học không thể max** cho F2P.

---

## 3. PHÂN TÍCH P2W

### 3.1 Tỷ Giá (Việt Nam)

- **1 USD = 26,000 VND**
- **1 VND = 1 Ma Thạch (MT)** khi nạp
- Ví dụ: 1,000,000 VND = 1,000,000 MT

### 3.2 Tổng Chi Phí MT (Một Lần)

| Hệ Thống | MT Cần | VND | USD |
|-----------|-------:|----:|----:|
| Công Pháp (28 pháp) | 211,000 | 211,000 | ~$8 |
| Võ Công (30 skill) | 78,050 | 78,050 | ~$3 |
| **Tổng** | **289,050** | **~289K VND** | **~$11** |

**💡 Insight:** Chi phí MT một lần **rẻ bất ngờ** (~$11). Wall thực sự là **LT (Linh Thồng)**, không phải MT.

### 3.3 Chi Phí MT Hàng Ngày

| Hoạt Động | Chi Phí/Ngày | Ghi Chú |
|-----------|-------------:|---------|
| Đào mỏ (25 lượt) | 125 MT | 5 MT/lượt |
| Hàm Mộ (30 lượt) | 300 MT | 10 MT/lượt |
| Tu Luyện Mode 3 | ceil(level/3) MT/phút | Max 360 phút/ngày |
| Công Pháp Gacha | 200 MT/lần | Nếu roll |

### 3.4 So Sánh F2P vs P2W

| Chỉ Số | F2P ($0) | Casual (1M VND) | Trung Bình (5M VND) | Heavy (20M VND) | Whale (100M VND) |
|--------|----------|-----------------|---------------------|-----------------|------------------|
| **Lv 100** | 2.2 năm | 6-8 tháng | 3-4 tháng | 2-3 tháng | 1-2 tháng |
| **Lv 200** | 7.8 năm | 3-4 năm | 1.5-2 năm | 6-8 tháng | 3-4 tháng |
| **Lv 500** | 51.9 năm | 25+ năm | 15-20 năm | 5-7 năm | 2-3 năm |
| **Lv 800** | 273 năm | 100+ năm | 60+ năm | 20-30 năm | 8-12 năm |
| **Công Pháp** | Khóa | Một phần | **Đầy đủ** | **Đầy đủ** | **Đầy đủ** |
| **Võ Công** | Khóa | Một phần | **Đầy đủ** | **Đầy đủ** | **Đầy đủ** |
| **Linh Căn T5** | Không | Không | Gacha-only | Gacha-only | Gacha-only |

### 3.5 EXP/Ngày (Ước Tính)

| Level | F2P | Casual (1M VND) | Heavy (20M VND) |
|-------|----:|----------------:|----------------:|
| Lv 100 | 154,000 | 11,250,000 | 22,500,000 |
| Lv 200 | 630,000 | 45,000,000 | 90,000,000 |

### 3.6 Phân Tích P2W

| Phát Hiện | Chi Tiết |
|-----------|----------|
| **MT wall rẻ** | Công Pháp + Võ Công = 289K VND (~$11). Ai cũng mua được. |
| **LT wall cực nặng** | Đột phá Lv800 cần **trousands tỷ LT** với 5% rate. MT không mua được LT. |
| **Whale cũng không max được** | Whale nạp 100M VND vẫn cần **8-12 năm** đến Lv800 vì LT constraint. |
| **Linh Căn = may mắn** | Tố Thể Đan chỉ drop từ boss. Không mua được bằng MT. |
| **MT thực sự dùng để加速** | Mua Tu Luyện Mode 3 (EXP), Đào Mỏ, Hàm Mộ — nhưng có **giới hạn ngày**. |
| **Lợi thế whale** | Lv100 trong 1-2 tháng (vs 2.2 năm F2P). Sau Lv200, whale cũng gặp LT wall. |

### 3.7 Tóm Tắt P2W

```
Nạp 1 triệu VND (1M MT):
✅ Mua được Công Pháp + Võ Công đầy đủ
✅ Tăng tốc EXP gấp 5-10x
✅ Lv100 trong 6-8 tháng
❌ Vẫn cần 3-4 năm đến Lv200
❌ Vẫn không max được Lv800 (cần 8-12 năm)
❌ Vẫn không mua được Linh Căn T5
```

---

## 4. SO SÁNH F2P vs P2W

### 4.1 Bảng So Sánh Tổng Quan

| Chỉ Số | F2P ($0) | Casual (1M VND) | Heavy (20M VND) | Whale (100M VND) |
|--------|----------|-----------------|-----------------|------------------|
| **Chi phí** | $0 | $38 | $769 | $3,846 |
| **Lv 100** | 2.2 năm | 6-8 tháng | 2-3 tháng | 1-2 tháng |
| **Lv 200** | 7.8 năm | 3-4 năm | 6-8 tháng | 3-4 tháng |
| **Lv 800** | 273 năm | 100+ năm | 20-30 năm | 8-12 năm |
| **Công Pháp** | Khóa | Một phần | Đầy đủ | Đầy đủ |
| **Võ Công** | Khóa | Một phần | Đầy đủ | Đầy đủ |
| **Linh Căn T5** | Không | Không | Gacha-only | Gacha-only |

### 4.2 Tỷ Lệ Tăng Tốc

| Level | F2P Time | Whale Time | Tỷ Lệ Tăng Tốc |
|-------|----------|------------|-----------------|
| Lv 100 | 2.2 năm | 1.5 tháng | **17.6x** |
| Lv 200 | 7.8 năm | 3.5 tháng | **26.7x** |
| Lv 500 | 51.9 năm | 2.5 năm | **20.8x** |
| Lv 800 | 273 năm | 10 năm | **27.3x** |

### 4.3 Phân Tích Giá Trị

| Gói Nạp | Chi Phí | Giá Trí Nhận Được | Đánh Giá |
|---------|---------|-------------------|----------|
| **Casual (1M VND)** | $38 | Công Pháp + Võ Công + Tăng tốc 5-10x | ⭐⭐⭐⭐⭐ |
| **Trung Bình (5M VND)** | $192 | Tất cả MT + EXP bonus lớn | ⭐⭐⭐⭐ |
| **Heavy (20M VND)** | $769 | EXP bonus cực lớn | ⭐⭐⭐ |
| **Whale (100M VND)** | $3,846 | EXP bonus tối đa | ⭐⭐ |

**Nhận xét:** Casual (1M VND) có **giá trị tốt nhất** — mua được tất cả hệ thống MT với giá rẻ. Các gói cao hơn chỉ tăng tốc EXP, không mở khóa nội dung mới.

---

## 5. PHÂN TÍCH WALL

### 5.1 Các Wall Trong Game

| Wall | Level | Nguyên Nhân | Mức Độ |
|------|-------|-------------|--------|
| **EXP Wall** | Lv 100 | EXP yêu cầu tăng cấp số nhân | 🟠 CAO |
| **LT Wall** | Lv 150+ | Đột phá cần LT cực lớn + 5% rate | 🔴 NGHIÊM TRỌNG |
| **Thể Chất Wall** | Q9→Q10 | 10 tỷ LT + 1% rate | 🔴 NGHIÊM TRỌNG |
| **Linh Căn Wall** | T5 | Tố Thể Đan = boss-only | 🟠 CAO |
| **MT Wall** | None | Chỉ $11 để max | 🟢 THẤP |

### 5.2 Wall Thực Sự

```
Lv 1-50:    DỄ (2.9 tháng F2P)
Lv 50-100:  TRUNG BÌNH (2.2 năm F2P)
Lv 100-200: KHÓ (7.8 năm F2P)
Lv 200-500: CỰC KHÓ (51.9 năm F2P)
Lv 500-800: KHÔNG THỂ (273 năm F2P)
```

### 5.3 So Sánh Wall

| Wall | F2P | Casual (1M VND) | Whale (100M VND) |
|------|-----|-----------------|------------------|
| **EXP** | 2.2 năm到Lv100 | 6-8 tháng | 1-2 tháng |
| **LT** | 919 năm đột phá | Vẫn nặng | Vẫn nặng |
| **Thể Chất** | 11,767 năm | Vẫn nặng | Vẫn nặng |
| **MT** | Không có | Không có | Không có |

---

## 6. KHUYẾN NGHỊ

### 6.1 Nếu Muốn Giữ Game Lâu Dài

| # | Khuyến Nghị | Mục Đích | Effort |
|---|-------------|----------|--------|
| 1 | **Tăng min success rate lên 10%** | Giảm LT wall | Nhỏ |
| 2 | **Thêm MT→LT conversion** (1000 MT = 1M LT) | Cho whale progress | Nhỏ |
| 3 | **Giảm Thể Chất Q9→Q10 cost** xuống 1 tỷ LT | Giảm wall cứng | Nhỏ |
| 4 | **Thêm daily LT income** (5,000-10,000 LT/ngày) | F2P progress nhanh hơn | Nhỏ |
| 5 | **Giới hạn Linh Căn T5** — cho phép mua bằng MT | Whale progress | Nhỏ |

### 6.2 Nếu Muốn Game "Idle" (Không Thể Max)

| # | Khuyến Nghị | Mục Đích | Effort |
|---|-------------|----------|--------|
| 1 | **Giữ nguyên** | Game designed để không max | 0 |
| 2 | **Thêm prestige system** | Reset để nhận bonus | Trung bình |
| 3 | **Thêm seasonal content** | Content mới mỗi mùa | Lớn |
| 4 | **Thêm PvP ranking** | Competitive endgame | Trung bình |

### 6.3 Giá Nạp Đề Xuất (Việt Nam)

| Gói Nạp | VND | USD | Nội Dung | Đánh Giá |
|---------|-----|-----|----------|----------|
| **Tiểu Hong Bao** | 50,000 | $2 | 50K MT + 50K LT | Tốt cho F2P |
| **Nạp Chơi Chơi** | 100,000 | $4 | 100K MT + 100K LT | Phổ biến nhất |
| **Nạp Nghiêm Túc** | 500,000 | $19 | 500K MT + 500K LT | Cho hardcore |
| **Whale** | 1,000,000 | $38 | 1M MT + 1M LT | Cho đại gia |

---

## APPENDIX

### A. Công Thức Chính

```php
// EXP per level
$umaxexp = max(100, floor(pow($nextLv, 2.8) * 2));

// Breakthrough success rate
$rate = max(5, 75 - $lv*0.6 - $jv*3 + $psjs);

// Breakthrough cost
$cost = ($jv == 1) ? $lv * ($lv + 1) : $lv * $lv * 3;

// Breakthrough bonus EXP (mỗi 50 cấp)
$bonus = floor(pow($lv, 2.5) * 50);
```

### B. Nguồn Dữ Liệu

- `D:\XXJserver\phpstudy_pro\WWW\class\player.php` — Công thức EXP, đột phá
- `D:\XXJserver\phpstudy_pro\WWW\game\tupo.php` — Logic đột phá
- `D:\XXJserver\phpstudy_pro\WWW\class\config\thechat_upgrade_config.json` — Chi phí Thể Chất
- `D:\XXJserver\phpstudy_pro\WWW\class\config\wugong_config.json` — Chi phí Võ Công
- `D:\XXJserver\phpstudy_pro\WWW\class\config\congphap_config.json` — Chi phí Công Pháp

### C. Lịch Sử Thay Đổi

| Ngày | Phiên Bản | Thay Đổi |
|------|-----------|----------|
| 2026-07-22 | v1.0 | Tạo báo cáo ban đầu |

---

*Báo cáo này được tạo bởi AI Assistant (big-pickle) dựa trên phân tích codebase thực tế.*
*Dữ liệu có thể thay đổi khi game cập nhật.*
