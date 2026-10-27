# PLAN: Hệ Thống Luyện Đan Tông Môn — Võ Lâm Truyền Kỳ

## Tổng Quan

Nâng cấp hệ thống luyện đan hiện tại (công thức cố định → thành công/thất bại) thành hệ thống chuyên sâu với:
- **Phân cấp Luyện Đan Sư** (cấp bậc người luyện)
- **Phân cấp Đan Dược** (Nhất phẩm → Đế phẩm)
- **Hệ thống Đan Văn** (chất lượng đan dược)
- **Quy trình luyện đan** (4 giai đoạn)
- **Hệ thống Đan Độc** (tác dụng phụ)
- **Bảo Khố Tông Môn** (đã có)

---

## 1. PHÂN CẤP LUYỆN ĐAN SƯ

### Bảng cấp bậc

| Cấp | Tên | Yêu cầu | Khả năng |
|-----|------|----------|----------|
| 1 | Luyện Đan Học Đồ | Lv 10, hoàn thành quest | Nhất phẩm (Dễ) |
| 2 | Luyện Đan Đệ Tử | Học Đồ + 100 đan thành công | Nhất phẩm (Khó) |
| 3 | Luyện Đan Sư | Đệ Tử + 500 đan, 30%成功率 80%+ | Nhị phẩm |
| 4 | Tam品 Luyện Đan Sư | Sư + 2000 đan, Tam phẩm | Tam phẩm |
| 5 | Thượng Sư | Sư + 5000 đan, Tứ phẩm | Tứ-Lục phẩm |
| 6 | Đại Sư | Thượng Sư + 10000 đan, Lục phẩm | Thất-Cửu phẩm |
| 7 | Tông Sư | Đại Sư + 20000 đan, Cửu phẩm | Tiên phẩm |
| 8 | Thánh Sư | Tông Sư + 50000 đan, Tiên phẩm | Thần phẩm |
| 9 | Đan Đế | Thánh Sư + 100000 đan, Thần phẩm | Đế phẩm |

### DB Table: `player_alchemist`

```sql
CREATE TABLE player_alchemist (
    sid         INT(11) PRIMARY KEY,
    alch_rank   INT(11) DEFAULT 1 COMMENT 'Cấp luyện đan sư (1-9)',
    alch_exp    INT(11) DEFAULT 0 COMMENT 'Kinh nghiệm luyện đan',
    total_craft INT(11) DEFAULT 0 COMMENT 'Tổng số lần luyện đan thành công',
    total_toxin INT(11) DEFAULT 0 COMMENT 'Tổng đan độc tích tụ',
    created_at  DATETIME,
    updated_at  DATETIME
);
```

---

## 2. PHÂN CẤP ĐAN DƯỢC

### Bảng phẩm cấp

| Phẩm | Tên | Cấp Sư | Mô tả |
|------|------|--------|--------|
| 1 | Nhất phẩm | Học Đồ | Đan dược cơ bản: hồi phục, chữa thương nhẹ |
| 2 | Nhị phẩm | Đệ Tử | Đan dược trung bình, hỗ trợ phá cảnh |
| 3 | Tam phẩm | Sư | Đan dược mạnh, bắt đầu có đan hương |
| 4 | Tứ phẩm | Thượng Sư | Dược hiệu mạnh, dị động linh khí |
| 5 | Ngũ phẩm | Thượng Sư | Đan dược cao cấp, bắt đầu có đan vân |
| 6 | Lục phẩm | Thượng Sư | Đan dược cực mạnh, phục vụ Kim Đan-Nguyên Anh |
| 7 | Thất phẩm | Đại Sư | Đan dược thành hình có linh tính |
| 8 | Bát phẩm | Đại Sư | Đan dược có thể sinh lôi kiếp |
| 9 | Cửu phẩm | Đại Sư | Đỉnh cao thế gian |
| 10 | Tiên phẩm | Tông Sư | Cải tử hoàn sinh |
| 11 | Thần phẩm | Thánh Sư | Giúp tu sĩ đột phá phi thăng |
| 12 | Đế phẩm | Đan Đế | Truyền thuyết, sức mạnh nghịch thiên |

### Phân loại chất lượng (Đan Văn)

| Chất lượng | Tên | Đan Văn | Hiệu quả |
|------------|------|---------|----------|
| 1 | Hạ phẩm | 0-2 văn | 40-60% dược hiệu, nhiều đan độc |
| 2 | Trung phẩm | 3-5 văn | 60-80% dược hiệu, ít đan độc |
| 3 | Thượng phẩm | 6-8 văn | 80-95% dược hiệu, gần hết đan độc |
| 4 | Cực phẩm | 9 văn / Đan Vân | 95-100% dược hiệu, hết đan độc |
| 5 | Siêu Cấp | Đan Lôi | Vượt qua thiên kiếp, dược hiệu 100%+ |
| 6 | Truyền Thuyết | Đan Tượng | Sinh dị tượng (Long, Phượng) |
| 7 | Tuyệt Phẩm | Đan Linh | Đan dược化 tinh linh |

---

## 3. HỆ THỐNG ĐAN VĂN

### Các cấp độ đan văn

| Cấp | Tên | Mô tả | Hiệu quả |
|-----|------|-------|----------|
| 0 | Không văn | Đan dược thô | 40% dược hiệu |
| 1 | Nhất Văn | 1 đường vân | 50% |
| 2 | Nhị Văn | 2 đường vân | 60% |
| 3 | Tam Văn | 3 đường vân | 70% |
| 4 | Tứ Văn | 4 đường vân | 75% |
| 5 | Ngũ Văn | 5 đường vân | 80% |
| 6 | Lục Văn | 6 đường vân | 85% |
| 7 | Thất Văn | 7 đường vân | 90% |
| 8 | Bát Văn | 8 đường vân | 95% |
| 9 | Cửu Văn | 9 đường vân | 100% (Đỉnh cao) |
| 10 | Đan Vân | Mây thuốc bao bọc | Giữ vẹn dược lực |
| 11 | Đan Lôi | Thu hút thiên lôi | Vượt qua = hoàn mỹ |
| 12 | Đan Tượng | Sinh dị tượng | Thần thú, hoa sen... |
| 13 | Đan Linh | Hóa tinh linh | Biết bay, biết trốn |

### Tính Đan Văn khi luyện

```
Số đan văn = f(alch_rank, recipe_difficulty, element_bonus, random)
- alch_rank: Cấp luyện đan sư越高, đan văn càng nhiều
- recipe_difficulty: Công thức càng khó, đan văn càng khó đạt
- element_bonus: Thuộc tính linh căn phù hợp +đan văn
- random: mt_rand(1, 100) →so sánh ngưỡng
```

### Công thức tính ví dụ:

```php
function calc_dan_van($alchRank, $recipeDifficulty, $elementBonus) {
    $base = $alchRank * 2; // 1-18
    $diffPenalty = $recipeDifficulty * 2; // 0-10
    $total = $base - $diffPenalty + $elementBonus;
    $roll = mt_rand(1, 100);
    
    if ($roll <= 2) return 13; // Đan Linh (0.02%)
    if ($roll <= 5) return 12; // Đan Tượng (0.03%)
    if ($roll <= 10) return 11; // Đan Lôi (0.05%)
    if ($roll <= 20) return 10; // Đan Vân (0.10%)
    
    $maxVan = min(9, max(0, $total));
    return $maxVan;
}
```

### Chi tiết Đan Lôi (Lôi Kiếp Đan Dược)

Đan Lôi là hiện tượng thiên lôi giáng xuống khi đan dược đạt đến cảnh giới cao. Đây là bước "thử lửa" quyết định chất lượng viên đan cuối cùng.

#### Phân cấp Đan Lôi (Dựa trên màu sắc và số lượng)

| Cấp | Tên | Màu sắc | Phẩm cấp | Mô tả |
|-----|------|---------|----------|-------|
| 1 | Nhất Sắc Lôi Kiếp | 1 màu | Thất phẩm | Cơ bản, pháp bảo thường chống đỡ được |
| 2 | Nhị Sắc Lôi Kiếp | 2 màu | Thất phẩm Cao | Cần phòng ngự tốt hơn |
| 3 | Tam Sắc Lôi Kiếp | 3 màu | Bát phẩm | Pháp bảo thường khó chống đỡ |
| 4 | Ngũ Sắc Lôi Kiếp | 5 màu | Bát phẩm Cao | Đánh nát Nguyên Anh nếu không chuẩn bị |
| 5 | Lục Sắc Lôi Kiếp | 6 màu | Cửu phẩm | Đan dược bắt đầu có linh tính sơ khai |
| 6 | Cửu Sắc Lôi Kiếp | 9 màu | Cửu phẩm Cực phẩm | Đỉnh cao phàm giới, cần cường giả hộ pháp |
| 7 | Cửu Thiên Huyền Lôi | Tím sậm | Tiên phẩm | Sức mạnh hủy diệt, xóa sổ tông môn |
| 8 | Tử Tiêu Thần Lôi | Vàng kim | Thần phẩm/Đế phẩm | Chỉ xuất hiện ở tiên giới |

#### Tác dụng đặc biệt của Đan Lôi

Mặc dù mang tính hủy diệt, Đan Lôi lại là bước then chốt:

| Tác dụng | Mô tả |
|----------|-------|
| **Tẩy Tủy Đan Dược** | Lôi đình thiêu rụi hoàn toàn tạp chất cuối cùng, giúp dược lực dung hợp hoàn hảo |
| **Khóa Chặt Đan Văn** | Sức mạnh lôi đình găm vào viên thuốc, tạo màng bảo vệ tự nhiên, lưu giữ hàng vạn năm |
| **Khai Mở Linh Trí** | Đan dược sau khi gột rửa dưới lôi kiếp chính thức "đắc đạo", biến đổi thành Đan Linh |

#### Cách các Đan Sư đối phó với Đan Lôi

| Phương pháp | Mô tả | Chi phí |
|-------------|-------|---------|
| **Đan Đỉnh Cấp Cao** | Lò luyện từ vật liệu quý hiếm, có trận pháp phòng ngự gánh bớt sát thương | Ma Thạch + Nguyên liệu quý |
| **Thuê Cường Giả Hộ Pháp** | Mời tu sĩ chiến đấu cường đại đến chịu đòn, đánh tan lôi kiếp | Tặng đan dược phẩm cao |
| **Trận Pháp Chống Lôi** | Đại trận hấp thụ/chuyển hướng dòng điện thiên lôi xuống lòng đất | Xây dựng tại đan tháp/tông môn |

#### Tính năng trong game

```php
// Xác định cấp Đan Lôi khi luyện đan
function calc_dan_loi($danVan, $recipeGrade) {
    // Chỉ trigger khi đan văn >= 10 (Đan Vân trở lên) VÀ phẩm cấp >= 7 (Thất phẩm)
    if ($danVan < 10 || $recipeGrade < 7) return 0;
    
    $roll = mt_rand(1, 100);
    
    if ($recipeGrade >= 11 && $roll <= 5) return 8; // Tử Tiêu Thần Lôi (0.05%)
    if ($recipeGrade >= 10 && $roll <= 10) return 7; // Cửu Thiên Huyền Lôi (0.05%)
    if ($recipeGrade >= 9 && $roll <= 20) return 6; // Cửu Sắc (0.10%)
    if ($recipeGrade >= 8 && $roll <= 40) return 5; // Lục Sắc (0.20%)
    if ($recipeGrade >= 8 && $roll <= 60) return 4; // Ngũ Sắc (0.20%)
    if ($recipeGrade >= 7 && $roll <= 80) return 3; // Tam Sắc (0.20%)
    if ($recipeGrade >= 7 && $roll <= 90) return 2; // Nhị Sắc (0.10%)
    if ($recipeGrade >= 7) return 1; // Nhất Sắc
    
    return 0;
}

// Tính hiệu quả Đan Lôi
function apply_dan_loi_effect($pillGrade, $loiLevel) {
    $effect = array(
        'toxin_removed' => 0,      // Đan độc bị loại bỏ
        'van_locked' => false,     // Đan văn bị khóa
        'spirit_awakened' => false // Linh trí được khai mở
    );
    
    if ($loiLevel <= 0) return $effect;
    
    // Tẩy tủy: loại bỏ đan độc
    $effect['toxin_removed'] = $loiLevel * 10;
    
    // Khóa đan văn: đan văn không bị mất theo thời gian
    if ($loiLevel >= 3) {
        $effect['van_locked'] = true;
    }
    
    // Khai mở linh trí: biến thành Đan Linh
    if ($loiLevel >= 6 && $pillGrade >= 9) {
        $effect['spirit_awakened'] = true;
    }
    
    return $effect;
}
```

### Chi tiết Đan Tượng (Dị Tượng Đan Dược)

Khi đan dược đạt đến cảnh giới cực cao, trong quá trình luyện sẽ xuất hiện dị tượng, biểu thị cho sự siêu phàm của viên đan.

#### Phân loại dị tượng

| Loại | Tên | Điều kiện | Hiệu ứng |
|------|------|-----------|----------|
| 1 | **Long Tượng** | Đan văn 12 + Thất phẩm trở lên | Rồng vàng quấn quanh đan dược, +20% dược hiệu, tăng giá trị gấp 3 |
| 2 | **Phượng Hoàng** | Đan văn 12 + Ngũ phẩm trở lên | Phượng đỏ rực, +30% dược hiệu, hồi phục 50% HP khi uống |
| 3 | **Huyền Vũ** | Đan văn 12 + Lục phẩm trở lên | Hộp rùa đen, +50% DEF tạm thời 1 giờ |
| 4 | **Băng Liên** | Đan văn 12 + Thất phẩm + thuộc tính Băng | Băng sen nở, đóng băng kẻ thù 3 lượt trong PvP |
| 5 | **Hỏa Phượng** | Đan văn 12 + Thất phẩm + thuộc tính Hỏa | Lửa thiêu đốt, +40% ATK 30 phút |
| 6 | **Lôi Đế** | Đan văn 12 + Đan Lôi Cửu Sắc | Sấm sét bao phủ, gây sát thương AoE khi uống |
| 7 | **Thiên Ma** | Đan văn 12 + Ma khí | Ma ảnh bao trùm, +60% sát thương nhưng -20% phòng ngự |

#### Điều kiện trigger

```php
function calc_dan_tuong($danVan, $recipeGrade, $element) {
    // Chỉ trigger khi đan văn = 12 (Đan Tượng)
    if ($danVan != 12) return 0;
    
    // Phẩm cấp tối thiểu: Thất phẩm (7)
    if ($recipeGrade < 7) return 0;
    
    $roll = mt_rand(1, 100);
    
    // Tỷ lệ xuất hiện theo phẩm cấp
    if ($recipeGrade >= 11 && $roll <= 30) return 7; // Thiên Ma (30%)
    if ($recipeGrade >= 10 && $roll <= 50) return 6; // Lôi Đế (20%)
    if ($recipeGrade >= 9 && $roll <= 70) return 5;  // Hỏa Phượng (20%)
    if ($recipeGrade >= 8 && $roll <= 80) return 4;  // Băng Liên (10%)
    if ($recipeGrade >= 7 && $roll <= 85) return 3;  // Huyền Vũ (5%)
    if ($recipeGrade >= 6 && $roll <= 90) return 2;  // Phượng Hoàng (5%)
    if ($recipeGrade >= 5) return 1;                  // Long Tượng
    
    return 0;
}
```

---

### Chi tiết Đan Linh (Đan Dược Tinh Linh)

Đan Linh là trạng thái cao nhất của đan dược, khi viên thuốc đã "đắc đạo" và trở thành tinh linh có tri giác.

#### Điều kiện hóa Đan Linh

| Điều kiện | Yêu cầu |
|-----------|----------|
| Phẩm cấp | Tiên phẩm (10) trở lên |
| Đan Văn | Đan Tượng (12) |
| Đan Lôi | Phải qua Lôi Kiếp cấp 5 trở lên |
| Nguyên liệu | Dùng "Huyết Đan Sâm" hoặc "Linh Thạch万 Năm" |

#### Đặc điểm Đan Linh

| Đặc điểm | Mô tả |
|-----------|-------|
| **Hình dáng** | Biến thành tinh linh nhỏ (long, phượng, quy, hổ...), bay xung quanh người luyện |
| **Thuộc tính** | Giữ nguyên dược hiệu, cộng thêm +10% stat cố định |
| **Bảo quản** | Không cần hộp, tự hồi phục dược lực theo thời gian |
| **Giao dịch** | Có thể bán/trao đổi như vật phẩm thường (giá gấp 10 lần đan thường) |
| **Chiến đấu** | Có thể gọi ra hỗ trợ chiến đấu (tối đa 1 Đan Linh cùng lúc) |
| **Nuôi dưỡng** | Cho ăn đan dược khác để tăng cấp, mở skill mới |

#### Bảng so sánh

| Loại | Đan Dược thường | Đan Linh |
|------|-----------------|----------|
| Dược hiệu | 100% | 110% |
| Thời hạn | 30 ngày (mất linh khí) | Vĩnh viễn |
| Lưu trữ | Túi đồ/hòm | Khu Đan Linh riêng |
| Giao dịch | Được | Được (giá cao hơn) |
| Chiến đấu | Không | Được (gọi ra hỗ trợ) |
| Nuôi dưỡng | Không | Được (cho ăn để tăng cấp) |

---

### Chi tiết Tích hợp PvP/Boss

Đan dược cao cấp là yếu tố quyết định trong PvP và Boss chiến.

#### Đan dược PvP

| Loại | Hiệu ứng | Thời gian |
|------|-----------|-----------|
| **Tăng Lực Đan** | +15% ATK | 30 phút |
| **Hộ Thể Đan** | +30% DEF | 30 phút |
| **Tốc Hành Đan** | +50% Tốc độ | 15 phút |
| **Giải Độc Đan** | Loại bỏ mọi debuff | Instant |
| **Phục Sinh Đan** | Hồi sinh 30% HP khi chết | 1 lần/trận |
| **Bất Tử Đan** | Miễn sát thương 5 giây | 1 lần/ngày |

#### Đan dược Boss

| Loại | Hiệu ứng | Thời gian |
|------|-----------|-----------|
| **Trọng Lực Đan** | +25% ATK boss | 1 giờ |
| **Thuốc Men** | Hồi phục 20% HP/giây | 10 phút |
| **Thánh Thuốc** | Miễn mọi debuff boss | 5 phút |

#### Đan Lôi trong PvP/Boss

| Kỹ năng | Hiệu ứng | Chi phí |
|---------|-----------|--------|
| **Lôi Đình Nhất Kích** | Gây sát thương 300% + stun 2 lượt | 1 Đan Lôi |
| **Thiên Lôi Vạn钧** | AoE 200% + giảm DEF 50% | 3 Đan Lôi |
| **Tử Tiêu Thần Lôi** | Bỏ qua mọi phòng ngự, gây sát thương tuyệt đối | 5 Đan Lôi |

---

### Chi tiết Đan Đỉnh (Lò Luyện Đan)

Đan Đỉnh là vật phẩm không thể thiếu khi luyện đan. Lò càng tốt, tỷ lệ thành công và chất lượng đan dược càng cao.

#### Phân loại Đan Đỉnh

| Loại | Tên | Thuộc tính | Nơi có được |
|------|------|-----------|-------------|
| 1 | **Đan Đỉnh Sắt** | Thành công +0%, Đan văn +0 | Ban đầu |
| 2 | **Đan Đỉnh Đồng** | Thành công +5%, Đan văn +0.5 | Mua từ NPC 10,000 Bạc |
| 3 | **Đan Đỉnh Bạc** | Thành công +10%, Đan văn +1 | Quest lvl 20 |
| 4 | **Đan Đỉnh Vàng** | Thành công +15%, Đan văn +2 | Đánh boss lvl 30 |
| 5 | **Đan Đỉnh Bạch Kim** | Thành công +20%, Đan văn +3 | Nhiệm vụ tông môn |
| 6 | **Đan Đỉnh Huyền Thạch** | Thành công +25%, Đan văn +4 | Luyện đan 1000 lần |
| 7 | **Đan Đỉnh Tử Kim** | Thành công +30%, Đan văn +5 | Nhiệm vụ đặc biệt |
| 8 | **Đan Đỉnh Long Phượng** | Thành công +35%, Đan văn +6 | Đánh boss Tiên phẩm |
| 9 | **Đan Đỉnh Thần** | Thành công +40%, Đan văn +8 | Nhiệm vụ Đan Đế |

#### Nâng cấp Đan Đỉnh

| Level | Yêu cầu | Hiệu quả |
|-------|----------|----------|
| +1 | 10 Ma Thạch + 5 Nguyên liệu quý | +2% thành công |
| +2 | 20 Ma Thạch + 10 Nguyên liệu quý | +4% thành công |
| +3 | 30 Ma Thạch + 15 Nguyên liệu quý | +6% thành công, +1 Đan văn |
| +4 | 40 Ma Thạch + 20 Nguyên liệu quý | +8% thành công, +1 Đan văn |
| +5 | 50 Ma Thạch + 25 Nguyên liệu quý | +10% thành công, +2 Đan văn |

#### Kỹ năng Đan Đỉnh

| Kỹ năng | Hiệu ứng | Điều kiện |
|---------|-----------|-----------|
| **Hỏa Thuật** | +15% thành công | Level 3 |
| **Linh Khí** | +20% đan văn | Level 5 |
| **Bảo Thân** | Giảm 50% đan độc khi thất bại | Level 7 |
| **Thiên Lôi** | Giảm 30% sát thương từ Đan Lôi | Level 10 |

---

### Chi tiết Dược Liệu (Nguyên Liệu Luyện Đan)

Dược liệu được phân loại theo thuộc tính và phẩm cấp.

#### Phân loại Dược Liệu

| Thuộc tính | Mô tả | Ví dụ |
|------------|-------|-------|
| **Hàn** | Tính lạnh, tốt cho đan dược hồi phục | Băng Liên, Hàn Ngọc |
| **Nhiệt** | Tính nóng, tốt cho đan dược tấn công | Hỏa Thạch, Lửa Địa |
| **Dương** | Tính dương, tăng đan văn | Dương Quả, Thái Dương Thạch |
| **Âm** | Tính âm, giảm đan độc | Âm Hoa, Nguyệt Quang Thạch |
| **Trung** | Không thuộc tính, dùng được mọi công thức | Linh Chi, Thảo Dược |

#### Phẩm cấp Dược Liệu

| Phẩm | Tên | Mô tả | Nơi thu thập |
|------|------|-------|--------------|
| 1 | **Thấp** | Nguyên liệu cơ bản | Đồng cỏ, rừng gần |
| 2 | **Trung** | Nguyên liệu phổ biến | Rừng sâu, hang động |
| 3 | **Cao** | Nguyên liệu quý | Núi cao, vực sâu |
| 4 | **Hiếm** | Nguyên liệu hiếm | Địa điểm ẩn, quest |
| 5 | **Cổ** | Nguyên liệu cổ đại | Lâu đài cổ, di tích |
| 6 | **Vạn Niên** | Nguyên liệu vạn năm | Rừng cổ, núi thiêng |
| 7 | **Thiên Niên** | Nguyên liệu ngàn năm | Địa điểm bí ẩn |
| 8 | **Tiên** | Nguyên liệu tiên giới | Nhiệm vụ đặc biệt |
| 9 | **Thần** | Nguyên liệu thần tiên | Nhiệm vụ Đan Đế |

#### Ảnh hưởng của Dược Liệu đến Đan Văn

| Số lượng Dược Liệu Thuộc Tính | Hiệu ứng |
|-------------------------------|----------|
| 1 loại | +0.5 Đan văn |
| 2 loại đối lập (Hàn + Nhiệt) | +1 Đan văn, +10% đan độc |
| 3 loại (Hàn + Nhiệt + Trung) | +1.5 Đan văn |
| 4 loại (Hàn + Nhiệt + Dương + Âm) | +2 Đan văn, +20% đan độc |

#### Nơi thu thập Dược Liệu

| Địa điểm | Loại Dược Liệu | Cấp độ |
|-----------|----------------|--------|
| Đồng Cỏ Bình Minh | Thấp | 1-10 |
| Rừng Sương Mù | Trung | 11-20 |
| Núi Băng Giá | Cao | 21-30 |
| Hang Rồng | Hiếm | 31-40 |
| Lâu Đài Cổ | Cổ | 41-50 |
| Rừng Nguyên Sơ | Vạn Niên | 51-60 |
| Núi Thần | Thiên Niên | 61-70 |
| Địa Ngục | Tiên | 71-80 |
| Tiên Giới | Thần | 81-90 |

---

### Chi tiết Bán/Trao đổi Đan Dược

Player có thể giao dịch đan dược với NPC và player khác.

#### Bán cho NPC

| Loại | Giá bán |
|------|---------|
| Đan dược phẩm thấp (1-3) | 50-200 Bạc |
| Đan dược phẩm trung (4-6) | 200-1000 Bạc |
| Đan dược phẩm cao (7-9) | 1000-5000 Bạc |
| Tiên đan (10) | 5000-20000 Bạc |
| Thần đan (11) | 20000-100000 Bạc |
| Đế đan (12) | Không bán được |

#### Bán cho Player khác

| Loại | Giá thị trường |
|------|----------------|
| Đan dược phẩm thấp (1-3) | 100-500 Bạc |
| Đan dược phẩm trung (4-6) | 500-2000 Bạc |
| Đan dược phẩm cao (7-9) | 2000-10000 Bạc |
| Tiên đan (10) | 10000-50000 Bạc |
| Thần đan (11) | 50000-200000 Bạc |
| Đế đan (12) | Không giao dịch được |

#### Ảnh hưởng của Đan Văn đến Giá

| Đan Văn | Hệ số giá |
|---------|-----------|
| 0-2 (Hạ phẩm) | x0.5 |
| 3-5 (Trung phẩm) | x1.0 |
| 6-8 (Thượng phẩm) | x1.5 |
| 9 (Cửu Văn) | x2.0 |
| 10 (Đan Vân) | x3.0 |
| 11 (Đan Lôi) | x5.0 |
| 12 (Đan Tượng) | x10.0 |
| 13 (Đan Linh) | x20.0 |

#### Cửa hàng Đan Dược

| Cửa hàng | Loại | Mua/Bán |
|----------|------|---------|
| **Thảo Đường** | Đan dược thường | Mua/Bán |
| **Bảo Khố** | Đan dược quý | Chỉ bán |
| **Chợ Đen** | Đan dược hiếm | Mua/Bán |
| **Thiên Môn** | Tiên đan | Chỉ mua |

---

### Chi tiết Đan Thư (Sách Luyện Đan)

Đan Thư là vật phẩm chứa công thức luyện đan, player cần học Đan Thư để mở khóa công thức mới.

#### Phân loại Đan Thư

| Loại | Tên | Nơi có được | Công thức |
|------|------|-------------|-----------|
| 1 | **Đan Thư Phàm** | NPC, Quest thường | Nhất-Nhị phẩm |
| 2 | **Đan Thư Linh** | Quest lvl 20-40 | Tam-Tứ phẩm |
| 3 | **Đan Thư Địa** | Quest lvl 41-60 | Ngũ-Lục phẩm |
| 4 | **Đan Thư Thiên** | Quest lvl 61-80 | Thất-Bát phẩm |
| 5 | **Đan Thư Tiên** | Quest lvl 81-90 | Cửu-Tiên phẩm |
| 6 | **Đan Thư Thần** | Nhiệm vụ Đan Đế | Thần-Đế phẩm |

#### Cách học Đan Thư

| Bước | Yêu cầu | Hiệu quả |
|------|----------|----------|
| 1 | Có Đan Thư trong túi | Học công thức |
| 2 | Đọc Đan Thư | Hiển thị công thức |
| 3 | Luyện đan thành công 1 lần | Công thức vĩnh viễn |
| 4 | Luyện đan thành công 10 lần | Tăng tỷ lệ thành công +5% |
| 5 | Luyện đan thành công 100 lần | Mở khóa biến thể mới |

#### Bán Đan Thư

| Loại | Giá bán |
|------|---------|
| Đan Thư Phàm | 1000 Bạc |
| Đan Thư Linh | 5000 Bạc |
| Đan Thư Địa | 20000 Bạc |
| Đan Thư Thiên | 100000 Bạc |
| Đan Thư Tiên | 500000 Bạc |
| Đan Thư Thần | Không bán được |

---

### Chi tiết Môi Trường Luyện Đan

Địa điểm luyện ảnh hưởng đến tỷ lệ thành công và chất lượng đan dược.

#### Phân loại Địa điểm

| Địa điểm | Thuộc tính | Hiệu ứng | Level |
|-----------|------------|----------|-------|
| **Nhà Riêng** | Trung | +0% thành công | 1+ |
| **Thảo Đường** | Trung | +5% thành công | 10+ |
| **Đan Tháp** | Cao | +15% thành công, +1 Đan văn | 30+ |
| **Cốc Luyện Đan** | Rất cao | +25% thành công, +2 Đan văn | 50+ |
| **Núi Thần** | Cực cao | +35% thành công, +3 Đan văn | 70+ |
| **Tiên Giới** | Hoàn hảo | +50% thành công, +5 Đan văn | 90+ |

#### Sự kiện Môi Trường

| Sự kiện | Tần suất | Hiệu ứng |
|---------|----------|----------|
| **Linh Khí Dồi Dào** | 10% | +20% thành công |
| **Địa Hỏa Bất Ổn** | 5% | -30% thành công, +đan độc |
| **Thiên Lôi** | 2% | Đan Lôi +1 cấp |
| **Địa Chấn** | 3% | -10% thành công |
| **Huyền Âm** | 1% | +50% đan văn |

#### Trận Pháp Luyện Đan

| Trận pháp | Hiệu ứng | Chi phí |
|-----------|----------|--------|
| **Trận Hỏa** | +10% thành công | 5 Ma Thạch/giờ |
| **Trận Thủy** | -20% đan độc | 5 Ma Thạch/giờ |
| **Trận Phong** | +15% đan văn | 10 Ma Thạch/giờ |
| **Trận Lôi** | Đan Lôi +1 cấp | 20 Ma Thạch/giờ |

---

## 4. QUY TRÌNH LUYỆN ĐAN (4 GIAI ĐOẠN)

### Giai đoạn 1: Chọn Dược Liệu
- Hiển thị danh sách nguyên liệu cần thiết
- Cho phép chọn thêm "Dược Liệu Phụ" (+thành công, +đan văn)
- Hiển thị tỷ lệ thành công dự kiến

### Giai đoạn 2: Khai Lò
- Animation/nhấn "Khai Lò"
- Random sự kiện:
  - **Bình thường**: Không gì xảy ra
  - **Linh Khí Dồi Dào**: +10% thành công
  - **Lò Bị Rò Rỉ**: -15% thành công
  - **Địa Hỏa Bất Ổn**: -20% thành công, +đan độc

### Giai đoạn 3: Dung Đan
- Mini-game hoặc auto (tùy chọn):
  - **Auto**: Hệ thống tự xử lý, dựa trên stat
  - **Mini-game**:玩家 thao tác (giữ lửa, thêm dược liệu đúng lúc)
- Mỗi thao tác ảnh hưởng đến đan văn và đan độc

### Giai đoạn 4: Ngưng Đan
- Random kết quả dựa trên các yếu tố trên
- Xác định: Thành công/Thất bại, Đan văn, Đan độc

---

## 5. HỆ THỐNG ĐAN Độc

### Nguồn đan độc
- Đan dược phẩm thấp luôn có đan độc
- Đan văn càng thấp, đan độc càng cao
- Luyện đan thất bại có thể tăng đan độc

### Tác hại đan độc
| Đan độc | Hiệu ứng |
|---------|----------|
| 0-100 | Không ảnh hưởng |
| 101-300 | Giảm 5% exp nhận được |
| 301-500 | Giảm 10% exp, -5% HP max |
| 501-800 | Giảm 20% exp, -10% HP max, -5% ATK |
| 801-1000 | Giảm 30% exp, -15% HP, -10% ATK, -10% DEF |
| 1000+ | Độc phát: mất HP mỗi lượt, không thể lên cấp |

### Triệt tiêu đan độc
- **Đan giải độc**: Vật phẩm giúp giảm đan độc
- **Tu luyện cao**: Tự giảm dần theo thời gian
- **Đan dược Đan Văn cao**: Uống không tăng đan độc

---

## 6. KHÔNG KHÁNG THUỐC

- Đan dược có **Đan Văn hoàn mỹ** (9 văn trở lên): Uống liên tục không bị lờn thuốc
- Đan dược có **Đan Văn thấp**: Giảm hiệu quả khi uống liên tiếp
  - Lần 1: 100% hiệu quả
  - Lần 2: 80%
  - Lần 3: 60%
  - Lần 4+: 40%

---

## 7. BẢNG THUỘC TÍNH ĐAN DƯỢC (MỚI)

```json
{
  "id": 1,
  "name": "Hồi Khí Đan",
  "grade": 1,
  "type": "yaodan",
  "base_effect": {"hp_restore": 100},
  "materials": [
    {"id": 30, "amount": 3},
    {"id": 31, "amount": 1}
  ],
  "level_required": 10,
  "alch_rank_required": 1,
  "base_success_rate": 85,
  "difficulty": 1,
  "toxin_base": 10,
  "sell_price": 500
}
```

---

## 8. UI/UX

### Trang Luyện Đan Mới
```
┌─────────────────────────────────────┐
│  🔥 Lò Luyện Đan                   │
│  Cấp Sư: Luyện Đan Sư (Lv 3)      │
│  EXP: 450/1000                      │
├─────────────────────────────────────┤
│  Chọn Công Thức:                   │
│  ┌─────────────────────────────┐   │
│  │ 💊 Hồi Khí Đan (Nhất phẩm) │   │
│  │ Nguyên liệu: 3x Linh Chi... │   │
│  │ Tỷ lệ: 85% | Đan văn: 3-6  │   │
│  └─────────────────────────────┘   │
│                                     │
│  [Chọn Dược Liệu Phụ]             │
│  [Khai Lò] → [Dung Đan] → [Ngưng] │
│                                     │
│  Kết quả:                          │
│  ✅ Thành công! Hồi Khí Đan x2    │
│  Đan văn: 5 (Trung phẩm)          │
│  Đan độc: +5                       │
└─────────────────────────────────────┘
```

---

## 9. DATABASE TABLES

```sql
-- Bảng cấp bậc luyện đan sư
CREATE TABLE player_alchemist (
    sid INT(11) PRIMARY KEY,
    alch_rank INT(11) DEFAULT 1,
    alch_exp INT(11) DEFAULT 0,
    total_craft INT(11) DEFAULT 0,
    total_toxin INT(11) DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME
);

-- Bảng đan dược mở rộng (thêm cột)
ALTER TABLE playeryaodan ADD COLUMN yd_van INT(11) DEFAULT 0;
ALTER TABLE playeryaodan ADD COLUMN yd_toxin INT(11) DEFAULT 0;
ALTER TABLE playeryaodan ADD COLUMN yd_quality INT(11) DEFAULT 1;

-- Bảng lịch sử luyện đan
CREATE TABLE player_alchemy_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sid INT(11),
    recipe_id INT(11),
    success TINYINT(1),
    dan_van INT(11),
    dan_toxin INT(11),
    alch_exp_gained INT(11),
    created_at DATETIME
);

-- Bảng công thức luyện đan mới
CREATE TABLE alchemy_recipe (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    grade INT(11) COMMENT 'Phẩm cấp 1-12',
    type VARCHAR(20) COMMENT 'yaodan/yaopin/daoju',
    result_id INT(11),
    result_amount INT(11),
    materials JSON,
    level_required INT(11),
    alch_rank_required INT(11),
    base_success_rate INT(11),
    difficulty INT(11) COMMENT '1-10',
    toxin_base INT(11),
    sell_price INT(11),
    is_active TINYINT(1) DEFAULT 1
);
```

---

## 10. CÁC LOẠI ĐAN DƯỢC (VÍ DỤ)

### Nhất phẩm (Cấp 1-3)
| Tên | Hiệu quả | Nguyên liệu |
|-----|----------|------------|
| Hồi Khí Đan | Hồi 100 HP | Linh Chi x3 |
| Hỏa Lộ Đan | Hồi 50 MP | Hỏa Thạch x2 |
| Giải Độc Đan | Giảm 50 đan độc | Thảo Dược x4 |
| Tăng Lực Đan | +5% ATK 30 phút | Huyết Cáp x2 |

### Nhị phẩm (Cấp 4-6)
| Tên | Hiệu quả | Nguyên liệu |
|-----|----------|------------|
| Phá Cảnh Đan | Giúp đột phá nhỏ | Kim Cương Thạch x3 |
| Bồi Nguyên Đan | Hồi 300 HP, 100 MP | Linh Chi Cổ x2 |
| Hộ Tâm Đan | Chống sát thương | Huyền Thạch x4 |

### Tam phẩm (Cấp 7-9)
| Tên | Hiệu quả | Nguyên liệu |
|-----|----------|------------|
| Kim Đan Luyện | Hỗ trợ ngưng kết Kim Đan | Vạn Niên Thạch x5 |
| Thiên Ma Đan | +15% ATK 1 giờ | Ma Huyết x3 |
| Bất Tử Đan | Hồi sinh 1 lần | Thụ Nguyên x2 |

---

## 11. TIẾN ĐỘ THỰC HIỆN

### Phase 1: Nền tảng (2-3 ngày)
- [ ] Tạo DB tables (player_alchemist, alchemy_recipe)
- [ ] Nâng cấp config đan dược (thêm grade, difficulty, toxin)
- [ ] Thêm cấp bậc luyện đan sư
- [ ] Tính đan văn khi luyện

### Phase 2: Quy trình luyện (2-3 ngày)
- [ ] UI chọn công thức + nguyên liệu
- [ ] Giai đoạn Khai Lò (random sự kiện)
- [ ] Giai đoạn Dung Đan (mini-game/auto)
- [ ] Giai đoạn Ngưng Đan (xác định kết quả)

### Phase 3: Đan Độc & Chất lượng (1-2 ngày)
- [ ] Hệ thống đan độc
- [ ] Hiệu ứng đan độc
- [ ] Triệt tiêu đan độc

### Phase 4: UI/UX (2-3 ngày)
- [ ] Trang luyện đan mới
- [ ] Hiển thị đan văn, đan độc
- [ ] Animation luyện đan

### Phase 5: Tích hợp (1-2 ngày)
- [ ] Kết nối với Bảo Khố Tông Môn
- [ ] Quest luyện đan mới
- [ ] Bảng xếp hạng luyện đan sư

---

## 12. LƯU Ý KỸ THUẬT

- PHP 5.6 compatible: Không dùng typed properties, no `??`
- MySQL 5.5 compatible: JSON → TEXT, không dùng JSON type
- Namespace: `player\` cho functions
- ob_start() trong game.php: Luôn `while (ob_get_level()) ob_end_clean();` trước khi echo JSON
- AJAX detection: `$isAjax = $isAjaxEarly`
- CSS: Dark navy/cyan/purple theme
