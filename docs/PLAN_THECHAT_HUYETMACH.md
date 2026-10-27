# PLAN: Thể Chất + Huyết Mạch — Triển Khai Đầy Đủ

> **Ngày**: 2026-07-20  
> **Phạm vi**: 2 hệ thống mới, đầy đủ (config + function + UI + upgrade + awakening)  
> **Ràng buộc**: PHP 5.6, MySQL 5.5, `namespace player`, static cache, dual-path config  
> **Template**: Linh Căn (5 config JSON, 4 function PHP, 4 game PHP, 1 DB column)

---

## MỤC LỤC

1. [Tổng quan thiết kế](#1-tổng-quan-thiết-kế)
2. [Hệ thống Thể Chất](#2-hệ-thống-thể-chất)
3. [Hệ thống Huyết Mạch](#3-hệ-thống-huyết-mạch)
4. [Cấu trúc File](#4-cấu-trúc-file)
5. [Database Schema](#5-database-schema)
6. [Tích hợp Player](#6-tích-hợp-player)
7. [Tương tác Element](#7-tương-tác-element)
8. [UI Pages](#8-ui-pages)
9. [Thứ tự triển khai](#9-thứ-tự-triển-khai)
10. [Lưu ý kỹ thuật](#10-lưu-ý-kỹ-thuật)

---

## 1. Tổng quan thiết kế

### 1.1 Hai hệ thống mới

| Hệ thống | Vai trò | Ảnh hưởng chính |
|---|---|---|
| **Thể Chất** | Thiên phú thân thể bẩm sinh | HP, DEF, hồi phục, miễn dịch, tiềm năng |
| **Huyết Mạch** | Huyết thống cổ đại | ATK, crit, element bonus, biến thân, thần thông |

### 1.2 Mối quan hệ với hệ thống hiện có

```
Linh Căn (element absorb + exp bonus)
    ↕ ngũ hành tương khắc
Thể Chất (HP/DEF/recovery/passive)
    ↕ stat bonus + passive
Huyết Mạch (ATK/crit/element dmg/transform)
    ↕ element + awakening
Võ Công ← element matching
Công Pháp ← element matching + training_time
```

### 1.3 Phẩm chất chung (dùng chung cho cả 2 hệ)

| # | Phẩm chất | Icon | Màu | Độ hiếm |
|---|---|---|---|---|
| 1 | Phàm Phẩm | ⚪ | `#9e9e9e` | 40% |
| 2 | Linh Phẩm | 🟢 | `#4caf50` | 25% |
| 3 | Huyền Phẩm | 🔵 | `#2196f3` | 15% |
| 4 | Địa Phẩm | 🟣 | `#9c27b0` | 10% |
| 5 | Thiên Phẩm | 🟡 | `#ff9800` | 6% |
| 6 | Thánh Phẩm | 🟠 | `#ff5722` | 2.5% |
| 7 | Tiên Phẩm | 🔴 | `#f44336` | 1% |
| 8 | Thần Phẩm | ✨ | `#ffd700` | 0.4% |
| 9 | Đế Phẩm | 🌌 | `#7c4dff` | 0.09% |
| 10 | Đạo Phẩm | ☯ | `#e0e0e0` | 0.01% |

---

## 2. Hệ thống Thể Chất

### 2.1 Giới thiệu

Thể Chất là thiên phú thân thể bẩm sinh, quyết định tiềm năng phát triển, sức mạnh thể chất, khả năng hồi phục và chống chịu. Thể Chất càng cao thì HP/DEF càng lớn, khả năng miễn nhiễm trạng thái càng tốt, tốc độ hồi phục càng nhanh.

### 2.2 12 loại Thể Chất

| # | Tên | Element | Icon | Phẩm đề xuất | Mô tả |
|---|---|---|---|---|---|
| 1 | Phàm Thể | none | 🧑 | Phàm | Thể chất bình thường, tiềm năng hạn chế |
| 2 | Linh Thể | all | ✨ | Linh | Thân thể linh hoạt, hấp thu linh khí tốt |
| 3 | Bảo Thể | kim | 💎 | Huyền | Thân thể như bảo vật, phòng ngự cao |
| 4 | Vương Thể | tho | 👑 | Địa | Vương giả chi thể, toàn diện |
| 5 | Thần Thể | hoa | 🔥 | Thiên | Thần linh chi thể, sức mạnh kinh thiên |
| 6 | Thánh Thể | moc | 🌿 | Thánh | Thanh tịnh bất phàm, hồi phục siêu việt |
| 7 | Tiên Thể | thuy | 💧 | Tiên | Tiên nhân chi thể, trường thọ |
| 8 | Đạo Thể | all | ☯ | Thần |契合 đại đạo,悟tính siêu phàm |
| 9 | Kiếm Thể | kim | ⚔ | Đế | Kiếm đạo chi thể, crit + xuyên giáp |
| 10 | Bá Thể | hoa | 💪 | Đế | Bá vương chi thể, ATK + HP khổng lồ |
| 11 | Ma Thể | none | 👁 | Đạo | Ma vương chi thể, lifesteal + debuff |
| 12 | Yêu Thể | moc | 🐉 | Đạo | Yêu tộc chi thể, biến thân + element bonus |

### 2.3 Stat bonus theo phẩm chất

**Base stats** (tại realm step = 0, sẽ nhân với `rangesjj` step):

| Phẩm | HP | ATK | DEF | Crit | Hồi HP/lượt | Miễn nhiễm |
|---|---|---|---|---|---|---|
| Phàm Phẩm | +50 | +10 | +15 | +2 | 0% | 0% |
| Linh Phẩm | +120 | +25 | +35 | +5 | 1% | 2% |
| Huyền Phẩm | +250 | +50 | +70 | +10 | 2% | 5% |
| Địa Phẩm | +500 | +100 | +140 | +18 | 3% | 8% |
| Thiên Phẩm | +1000 | +200 | +280 | +30 | 5% | 12% |
| Thánh Phẩm | +2500 | +500 | +700 | +55 | 8% | 18% |
| Tiên Phẩm | +6000 | +1200 | +1600 | +100 | 12% | 25% |
| Thần Phẩm | +15000 | +3000 | +4000 | +200 | 18% | 35% |
| Đế Phẩm | +40000 | +8000 | +10000 | +400 | 25% | 50% |
| Đạo Phẩm | +100000 | +20000 | +25000 | +800 | 40% | 75% |

**Công thức scale theo realm**: `finalStat = base × (1 + jingjieValue × 0.15)`

**Passive effect theo phẩm** (cấp cao hơn có thêm):

| Phẩm | Passive |
|---|---|
| Phàm~Huyền | Chỉ stat bonus |
| Địa Phẩm | +3% exp tu luyện |
| Thiên Phẩm | +5% exp + 2% hồi HP mỗi lượt |
| Thánh Phẩm | +8% exp + 3% hồi HP + 5% miễn nhiễm tâm ma |
| Tiên Phẩm | +12% exp + 5% hồi HP + 10% miễn nhiễm + 5% giảm dmg |
| Thần Phẩm | +18% exp + 8% hồi HP + 15% miễn nhiễm + 10% giảm dmg + mở Thần Thông |
| Đế Phẩm | +25% exp + 12% hồi HP + 20% miễn nhiễm + 15% giảm dmg + Thần Thông mạnh |
| Đạo Phẩm | +40% exp + 20% hồi HP + 30% miễn nhiễm + 25% giảm dmg + Thần Thông tối thượng |

### 2.4 Nâng cấp Thể Chất

**Cơ chế**: Tiêu hao vật phẩm + linh thạch → tăng phẩm chất hiện tại (không thay loại).

| Cấp upgrade | Từ → Đến | Linh Thạch | Vật phẩm | Tỷ lệ thành công |
|---|---|---|---|---|
| 1 | Phàm → Linh | 1,000 | 5x Tinh Thể Đá | 100% |
| 2 | Linh → Huyền | 5,000 | 10x Tinh Thể Đá + 3x Huyền Thạch | 80% |
| 3 | Huyền → Địa | 20,000 | 8x Huyền Thạch + 5x Địa Linh Ngọc | 60% |
| 4 | Địa → Thiên | 80,000 | 10x Địa Linh Ngọc + 5x Thiên Cơ Đá | 40% |
| 5 | Thiên → Thánh | 300,000 | 8x Thiên Cơ Đá + 3x Thánh Thạch | 25% |
| 6 | Thánh → Tiên | 1,000,000 | 10x Thánh Thạch + 5x Tiên Lộ | 15% |
| 7 | Tiên → Thần | 5,000,000 | 8x Tiên Lộ + 3x Thần Thạch | 8% |
| 8 | Thần → Đế | 20,000,000 | 10x Thần Thạch + 5x Đế Lệnh | 3% |
| 9 | Đế → Đạo | 100,000,000 | 8x Đế Lệnh + 1x Đạo Nguyên | 1% |

**Vật phẩm nâng cấp** (thêm vào `daoju_config.json`):

| ID | Tên | Mô tả | Giá NPC |
|---|---|---|---|
| 100 | Tinh Thể Đá | Đá tinh thể cơ bản, dùng nâng Thể Chất | 500 |
| 101 | Huyền Thạch | Huyền thạch quý, nâng Thể Chất cấp trung | 2,500 |
| 102 | Địa Linh Ngọc | Ngọc linh địa, nâng Thể Chất cấp cao | 10,000 |
| 103 | Thiên Cơ Đá | Đá thiên cơ, nâng Thể Chất cấp rất cao | 50,000 |
| 104 | Thánh Thạch | Thạch thánh, nâng Thể Chất cấp thần | 200,000 |
| 105 | Tiên Lộ | Tiên lộ hiếm có, nâng Thể Chất lên tiên | 1,000,000 |
| 106 | Thần Thạch | Thạch thần, nâng Thể Chất lên thần | 5,000,000 |
| 107 | Đế Lệnh | Lệnh đế, nâng Thể Chất lên đế | 25,000,000 |
| 108 | Đạo Nguyên | Nguyên Đạo, nâng Thể Chất tối thượng | 100,000,000 |

### 2.5 Thức tỉnh (Awakening)

**Điều kiện**: Kết hợp cảnh giới + vật phẩm + boss.

| Stage | Tên | Yêu cầu | Hiệu ứng |
|---|---|---|---|
| 0 | Chưa thức tỉnh | — | Base stats only |
| 1 | Sơ Thức Tỉnh | Realm ≥ Kim Đan + 10x Tinh Thể Đá | +20% stat bonus |
| 2 | Trung Thức Tỉnh | Realm ≥ Nguyên Anh + 5x Thánh Thạch + Đánh bại Boss Thể Chất LV2 | +50% stat + unlock passive mới |
| 3 | Thượng Thức Tỉnh | Realm ≥ Đại Thừa + 5x Tiên Lộ + Hoàn thành Quest Thức Tỉnh + Đánh bại Boss Thể Chất LV3 | +100% stat + unlock Thần Thông |

**Passive mới khi thức tỉnh**:

| Stage | Passive mới |
|---|---|
| Sơ | +5% HP max hồi phục mỗi lượt |
| Trung | +10% miễn nhiễm debuff |
| Thượng | +15% giảm sát thương khi HP < 30% |

### 2.6 Thần Thông mở khóa (theo phẩm + thức tỉnh)

| Phẩm | Thần Thông |
|---|---|
| Tiên Phẩm + Thượng Thức Tỉnh | **Bất Tử Chi Thân**: Khi HP < 1%, giữ 1 HP trong 1 lượt (CD: 5 phút) |
| Thần Phẩm + Thượng Thức Tỉnh | **Thánh Thể Hộ Thuẫn**: Tạo shield吸收 30% damage trong 3 lượt (CD: 3 phút) |
| Đế Phẩm + Thượng Thức Tỉnh | **Đạo Thể Vô Song**: Tăng 50% tất cả stats trong 5 lượt (CD: 10 phút) |
| Đạo Phẩm + Thượng Thức Tỉnh | **Hư Vô Chi Thân**: Miễn nhiễm tất cả damage 2 lượt (CD: 15 phút) |

---

## 3. Hệ thống Huyết Mạch

### 3.1 Giới thiệu

Huyết Mạch là huyết thống cổ đại truyền lại, quyết định sức mạnh bẩm sinh, khả năng thức tỉnh thần thông, biến thân và tương tác ngũ hành. Huyết Mạch càng hiếm thì sát thương element càng cao, khả năng biến thân càng mạnh.

### 3.2 11 loại Huyết Mạch

| # | Tên | Element | Icon | Phẩm đề xuất | Mô tả |
|---|---|---|---|---|---|
| 1 | Chân Long | thuy | 🐉 | Thiên | Long tộc chi huyết, HP + ATK cao |
| 2 | Chân Phượng | hoa | 🔥 | Thiên | Phượng tộc chi huyết, hồi phục + dmg |
| 3 | Kỳ Lân | tho | 🦌 | Thánh | Kỳ lân chi huyết, phòng ngự + crit |
| 4 | Kim Ô | hoa | ☀ | Thánh | Kim ô chi huyết, ATK + exp bonus |
| 5 | Côn Bằng | thuy | 🌊 | Tiên | Côn bằng chi huyết, tốc độ + lifesteal |
| 6 | Huyền Vũ | thuy | 🐢 | Tiên | Huyền vũ chi huyết, DEF + HP cực cao |
| 7 | Bạch Hổ | kim | 🐯 | Thần | Bạch hổ chi huyết, crit + xuyên giáp |
| 8 | Chu Tước | hoa | 🐦 | Thần | Chu tước chi huyết, dmg + hồi phục |
| 9 | Bàn Cổ | all | 💎 | Đế | Bàn cổ chi huyết, toàn diện |
| 10 | Hỗn Độn | all | 🌌 | Đế | Hỗn Độn chi huyết, random buff |
| 11 | Hồng Mông | all | ✨ | Đạo | Hồng Mông chi huyết, buff tối thượng |

### 3.3 Stat bonus theo phẩm chất

**Base stats** (cộng thêm, scale theo realm):

| Phẩm | ATK | HP | Crit | Element Dmg | Lifesteal |
|---|---|---|---|---|---|
| Phàm Phẩm | +8 | +40 | +3 | +0% | +1% |
| Linh Phẩm | +20 | +100 | +7 | +3% | +2% |
| Huyền Phẩm | +45 | +220 | +14 | +6% | +4% |
| Địa Phẩm | +90 | +450 | +25 | +10% | +7% |
| Thiên Phẩm | +180 | +900 | +42 | +15% | +11% |
| Thánh Phẩm | +450 | +2200 | +75 | +22% | +16% |
| Tiên Phẩm | +1100 | +5500 | +130 | +30% | +22% |
| Thần Phẩm | +2800 | +14000 | +250 | +40% | +30% |
| Đế Phẩm | +7500 | +38000 | +500 | +55% | +40% |
| Đạo Phẩm | +19000 | +95000 | +1000 | +75% | +55% |

**Công thức scale**: `finalStat = base × (1 + jingjieValue × 0.18)`

**Element Dmg**: `% bonus damage element tương ứng` (VD: Chân Long → +X% Thủy damage)

### 3.4 Passive effect theo phẩm

| Phẩm | Passive |
|---|---|
| Phàm~Huyền | Chỉ stat bonus |
| Địa Phẩm | +5% element damage |
| Thiên Phẩm | +8% element + 3% crit rate |
| Thánh Phẩm | +12% element + 5% crit + 5% exp |
| Tiên Phẩm | +18% element + 8% crit + 10% exp + 5% drop rate |
| Thần Phẩm | +25% element + 12% crit + 15% exp + 10% drop + mở Thần Thông |
| Đế Phẩm | +35% element + 18% crit + 22% exp + 15% drop + Thần Thông mạnh |
| Đạo Phẩm | +50% element + 25% crit + 30% exp + 20% drop + Thần Thông tối thượng |

### 3.5 Thức tỉnh Huyết Mạch

**4 giai đoạn thức tỉnh**:

| Stage | Tên | Điều kiện | Hiệu ứng |
|---|---|---|---|
| 0 | Vĩnh Viễn Trầm Mệnh | — | Base stats |
| 1 | Sơ Giác | Realm ≥ Trúc Cơ + vật phẩm | +15% stat + element dmg +5% |
| 2 | Thức Tỉnh | Realm ≥ Kim Đan + boss drop + quest | +40% stat + element dmg +12% + passive mới |
| 3 | Chân Thân | Realm ≥ Nguyên Anh + boss drop + quest + vật phẩm hiếm | +80% stat + element dmg +22% + unlock Biến Thân |
| 4 | Huyết Thống Viên Mãn | Realm ≥ Đại Thừa + boss cuối + quest cuối | +150% stat + element dmg +35% + Biến Thân mạnh + Thần Thông |

**Điều kiện chi tiết theo loại huyết mạch**:

| Huyết Mạch | Boss drop (Stage 2) | Quest (Stage 3) | Boss cuối (Stage 4) |
|---|---|---|---|
| Chân Long | Rồng Ngàn Tuổi | Long Cung Bí Cảnh | Huyễn Long Vương |
| Chân Phượng | Phượng Hoàng Lông | Núi Phượng Hoàng | Phượng Hoàng Cổ |
| Kỳ Lân | Kỳ Lân Giác | Kỳ Lân Đống | Kỳ Lân Vương |
| Kim Ô | Kim Ô Lông | Kim Ô Sào Huyệt | Tam족 Kim Ô |
| Côn Bằng | Côn Bằng Vảy | Biển Bắc Bí Cảnh | Côn Bằng Tổ |
| Huyền Vũ | Huyền Vũ Giáp | Huyền Vũ Động | Huyền Vũ Cổ |
| Bạch Hổ | Bạch Hổ Răng | Bạch Hổ Sơn | Bạch Hổ Vương |
| Chu Tước | Chu Tước Lông | Chu Tước Đỉnh | Chu Tước Tổ |
| Bàn Cổ | Bàn Cổ Huyết | Hỗn Độn Chi Địa | Bàn Cổ Phân Thân |
| Hỗn Độn | Hỗn Độn Thạch | Hư Không Bí Cảnh | Hỗn Độn Thú |
| Hồng Mông | Hồng Mông Khí | Hồng Mông Chi Môn | Hồng Mông Tổ Long |

### 3.6 Biến Thân (Transformation)

**Kích hoạt**: Stage 3+ Thức Tỉnh, tiêu hao 20% HP max.

| Huyết Mạch | Hình dạng | Hiệu ứng biến thân | Thời gian | CD |
|---|---|---|---|---|
| Chân Long | 🐉 Long hình | +50% ATK/HP, +20% Thủy dmg,免疫 vật lý 30% | 5 lượt | 10 phút |
| Chân Phượng | 🔥 Phượng hình | +40% ATK, hồi 10% HP/lượt, +30% Hỏa dmg | 5 lượt | 10 phút |
| Kỳ Lân | 🦌 Kỳ Lân hình | +60% DEF, +25% Thổ dmg, +15% crit | 4 lượt | 12 phút |
| Kim Ô | ☀ Kim Ô hình | +55% ATK, +35% Hỏa dmg, bỏ qua 20% DEF | 4 lượt | 12 phút |
| Côn Bằng | 🌊 Côn Bằng hình | +45% ATK, +30% Thủy dmg, +20% né tránh | 5 lượt | 10 phút |
| Huyền Vũ | 🐢 Huyền Vũ hình | +80% DEF, +25% Thủy dmg, shield 20% HP | 6 lượt | 8 phút |
| Bạch Hổ | 🐯 Bạch Hổ hình | +60% ATK, +30% Kim dmg, +25% crit dmg | 4 lượt | 12 phút |
| Chu Tước | 🐦 Chu Tước hình | +50% ATK, +35% Hỏa dmg, hồi 8% HP/lượt | 5 lượt | 10 phút |
| Bàn Cổ | 💎 Bàn Cổ hình | +40% tất cả stats, +20% element dmg | 6 lượt | 8 phút |
| Hỗn Độn | 🌌 Hỗn Độn hình | Random buff mạnh trong 5 lượt | 5 lượt | 10 phút |
| Hồng Mông | ✨ Hồng Mông hình | +70% ATK/DEF/HP, +40% element dmg, miễn nhiễm 1 debuff | 7 lượt | 6 phút |

### 3.7 Thần Thông mở khóa

| Phẩm + Stage | Thần Thông |
|---|---|
| Tiên Phẩm + Stage 2 | **[Element] Chi Thân**: Tăng 30% element damage vĩnh viễn |
| Thần Phẩm + Stage 3 | **Huyết Thống Phẫn Nộ**: Khi HP < 20%, ATK ×2 trong 3 lượt (CD: 5 phút) |
| Đế Phẩm + Stage 3 | **Cổ Tổ Chi Thuật**: Gọi 1 phân thân giúp đỡ 2 lượt (CD: 8 phút) |
| Đạo Phẩm + Stage 4 | **Huyết Thống Đại Viên Mãn**: Biến thân không tiêu hao HP, +100% stats 5 lượt (CD: 15 phút) |

---

## 4. Cấu trúc File

### 4.1 Thể Chất

```
class/config/
  thechat_config.json          — 12 loại thể chất (id, name, element, stats, passive, weight...)
  thechat_upgrade_config.json  — 9 cấp upgrade (cost, success_rate, material_ids)
  thechat_awakening_config.json — 3 stage thức tỉnh (realm_require, items, boss...)

class/function/
  thechat.php                  — Config loader, assign, calc bonuses, upgrade, awakening

game/
  thechat.php                  — UI chính: hiển thị thể chất hiện tại, stats, passive
  thechat_upgrade.php          — UI nâng cấp phẩm chất
  thechat_awakening.php        — UI thức tỉnh
```

### 4.2 Huyết Mạch

```
class/config/
  huyetmach_config.json        — 11 loại huyết mạch (id, name, element, stats, passive, transform...)
  huyetmach_awakening_config.json — 4 stage thức tỉnh (realm_require, boss_drop, quest_id, items)
  huyetmach_transform_config.json — Biến thân (effect, duration, cooldown, visual)

class/function/
  huyetmach.php                — Config loader, assign, calc bonuses, awakening, transform

game/
  huyetmach.php                — UI chính: hiển thị huyết mạch hiện tại, stats, thức tỉnh
  huyetmach_awakening.php      — UI thức tỉnh (boss + quest + vật phẩm)
  huyetmach_transform.php      — UI biến thân (hiển thị form, activate)
```

### 4.3 Tổng cộng

| Loại | File mới | File sửa |
|---|---|---|
| Config JSON | 5 | 1 (daoju_config.json — thêm vật phẩm) |
| Function PHP | 2 | 1 (player.php — tích hợp stat) |
| Game PHP | 6 | 3 (xiulian.php, pve.php, boss.php — element bonus) |
| CSS | 0 | 1 (mystyle.css — thêm class) |
| Route | 0 | 1 (game.php — thêm routes) |
| Navigation | 0 | 1 (nowmid.php — thêm nút) |
| **Tổng** | **13 file mới** | **8 file sửa** |

---

## 5. Database Schema

### 5.1 Cột mới trong `game1`

```sql
ALTER TABLE game1 ADD COLUMN thechat_id INT DEFAULT 0;
ALTER TABLE game1 ADD COLUMN thechat_quality INT DEFAULT 1;    -- phẩm hiện tại (1-10)
ALTER TABLE game1 ADD COLUMN thechat_awaken_stage TINYINT DEFAULT 0; -- 0-3

ALTER TABLE game1 ADD COLUMN huyetmach_id INT DEFAULT 0;
ALTER TABLE game1 ADD COLUMN huyetmach_quality INT DEFAULT 1;  -- phẩm hiện tại (1-10)
ALTER TABLE game1 ADD COLUMN huyetmach_awaken_stage TINYINT DEFAULT 0; -- 0-4
ALTER TABLE game1 ADD COLUMN huyetmach_transform_cd DATETIME DEFAULT NULL; -- cooldown biến thân
```

### 5.2 Bảng mới

```sql
-- Log nâng cấp thể chất
CREATE TABLE player_thechat_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  sid         INT NOT NULL,
  from_quality INT NOT NULL,
  to_quality  INT NOT NULL,
  success     TINYINT(1) DEFAULT 0,
  created_at  DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Log thức tỉnh thể chất
CREATE TABLE player_thechat_awaken_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  sid         INT NOT NULL,
  from_stage  TINYINT NOT NULL,
  to_stage    TINYINT NOT NULL,
  success     TINYINT(1) DEFAULT 0,
  created_at  DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Log thức tỉnh huyết mạch
CREATE TABLE player_huyetmach_awaken_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  sid         INT NOT NULL,
  from_stage  TINYINT NOT NULL,
  to_stage    TINYINT NOT NULL,
  boss_killed VARCHAR(100) DEFAULT NULL,
  success     TINYINT(1) DEFAULT 0,
  created_at  DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tracking boss huyết mạch đã đánh bại
CREATE TABLE player_huyetmach_boss (
  sid         INT NOT NULL,
  boss_id     VARCHAR(50) NOT NULL,
  killed_at   DATETIME DEFAULT NULL,
  PRIMARY KEY (sid, boss_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Quest thức tỉnh huyết mạch
CREATE TABLE player_huyetmach_quest (
  sid         INT NOT NULL,
  quest_id    VARCHAR(50) NOT NULL,
  progress    TEXT,  -- JSON: {objective: current}
  completed   TINYINT(1) DEFAULT 0,
  claimed     TINYINT(1) DEFAULT 0,
  created_at  DATETIME DEFAULT NULL,
  PRIMARY KEY (sid, quest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

---

## 6. Tích hợp Player

### 6.1 `class/player.php` — Thêm vào `getplayer()`

**Thứ tự tích hợp** (sau Linh Căn, trước EXP formula):

```
Layer 1: DB load (base stats)
Layer 2: Thiên Phú multipliers
Layer 3: Equipment bonuses
Layer 4: Võ Công bonuses
Layer 5: Set Bonus (Tạo Trang)
Layer 6: Công Pháp passive
Layer 7: Linh Căn passive + element
Layer 8: [MỚI] Thể Chất passive + element
Layer 9: [MỚI] Huyết Mạch passive + element
Layer 10: EXP formula
```

### 6.2 Code tích hợp (pseudo-code)

```php
// === THỂ CHẤT ===
if ($player->thechat_id > 0) {
    $tcCfg = thechat_config_get($player->thechat_id);
    $tcQuality = $player->thechat_quality;  // 1-10
    $tcStage = $player->thechat_awaken_stage; // 0-3

    // Tính stat bonus (base × quality_mult × realm_scale × awaken_mult)
    $qualityMult = $tcQuality;  // 1-10 mapping
    $realmScale = 1 + $player->jingjieValue * 0.15;
    $awakenMult = 1 + $tcStage * 0.2;  // +20% per stage

    $tcHP = $tcCfg['base_hp'] * $qualityMult * $realmScale * $awakenMult;
    $tcDEF = $tcCfg['base_def'] * $qualityMult * $realmScale * $awakenMult;
    // ... other stats

    // Áp dụng vào player
    $player->umaxhp += round($tcHP);
    $player->ufy += round($tcDEF);

    // Passive effect (chỉ phẩm cao)
    if ($tcQuality >= 4) {  // Địa Phẩm trở lên
        $player->exp_bonus += $tcCfg['passive_exp'] ?? 0;
        $player->hp_regen_percent += $tcCfg['passive_regen'] ?? 0;
        $player->debuff_resist += $tcCfg['passive_resist'] ?? 0;
        $player->damage_reduce += $tcCfg['passive_reduce'] ?? 0;
    }

    // Lưu display info
    $player->thechat_name = $tcCfg['name'];
    $player->thechat_element = $tcCfg['element'];
    $player->thechat_color = $tcCfg['color'];
    $player->thechat_icon = $tcCfg['icon'];
}

// === HUYẾT MẠCH ===
if ($player->huyetmach_id > 0) {
    $hmCfg = huyetmach_config_get($player->huyetmach_id);
    $hmQuality = $player->huyetmach_quality;
    $hmStage = $player->huyetmach_awaken_stage;

    $qualityMult = $hmQuality;
    $realmScale = 1 + $player->jingjieValue * 0.18;
    $awakenMult = 1 + $hmStage * 0.25;

    $hmATK = $hmCfg['base_atk'] * $qualityMult * $realmScale * $awakenMult;
    $hmHP = $hmCfg['base_hp'] * $qualityMult * $realmScale * $awakenMult;
    $hmCrit = $hmCfg['base_crit'] * $qualityMult * $realmScale * $awakenMult;

    $player->ugj += round($hmATK);
    $player->umaxhp += round($hmHP);
    $player->ubj += round($hmCrit);

    // Element damage bonus (chỉ apply trong combat, không apply vào stat trực tiếp)
    $player->huyetmach_element_dmg = $hmCfg['element_dmg_base'] * $hmQuality * $awakenMult;

    // Passive
    if ($hmQuality >= 4) {
        $player->huyetmach_element_dmg += $hmCfg['passive_element'] ?? 0;
        $player->huyetmach_crit_rate += $hmCfg['passive_crit'] ?? 0;
        $player->huyetmach_exp_bonus += $hmCfg['passive_exp'] ?? 0;
        $player->huyetmach_drop_bonus += $hmCfg['passive_drop'] ?? 0;
    }

    // Display
    $player->huyetmach_name = $hmCfg['name'];
    $player->huyetmach_element = $hmCfg['element'];
    $player->huyetmach_color = $hmCfg['color'];
    $player->huyetmach_icon = $hmCfg['icon'];
}
```

### 6.3 Combat power (zhuangtai.php)

Thêm vào tính chiến lực:

```php
// Thể Chất
$chienLuc += $player->thechat_quality * 200 + $player->thechat_awaken_stage * 500;
// Huyết Mạch
$chienLuc += $player->huyetmach_quality * 250 + $player->huyetmach_awaken_stage * 600;
```

---

## 7. Tương tác Element

### 7.1 Thể Chất ↔ Element

Thể Chất **không có element trực tiếp** (trừ 12 loại có element riêng), nhưng:

| Thể Chất Element | Tương tác |
|---|---|
| Kim (Bảo Thể) | +10% Kim damage, +5% chống Mộc damage |
| Mộc (Thánh Thể) | +10% Mộc damage, +5% chống Hỏa damage |
| Thủy (Tiên Thể) | +10% Thủy damage, +5% chống Thổ damage |
| Hỏa (Thần Thể/Bá Thể) | +10% Hỏa damage, +5% chống Kim damage |
| Thổ (Vương Thể) | +10% Thổ damage, +5% chống Thủy damage |
| All (Linh Thể/Đạo Thể) | +3% tất cả element damage |
| None (Phàm Thể/Ma Thể) | Không bonus element |

### 7.2 Huyết Mạch ↔ Element

Huyết Mạch **có element rõ ràng**, áp dụng ngũ hành tương khắc:

```php
// Trong pve.php / boss.php
$hmElement = $player->huyetmach_element;
$monsterElement = $monster->element;
$hmElemMult = linhcan_get_element_multiplier($hmElement, $monsterElement);
// Bonus từ huyết mạch = base × hmElemMult
$bonusDmg = round($player->huyetmach_element_dmg * $hmElemMult);
```

### 7.3 Thể Chất + Huyết Mạch tương tác

| Combination | Bonus |
|---|---|
| Thần Thể (Hỏa) + Chu Tước (Hỏa) | +15% Hỏa damage (same element synergy) |
| Bảo Thể (Kim) + Bạch Hổ (Kim) | +15% Kim damage |
| Tiên Thể (Thủy) + Huyền Vũ (Thủy) | +15% Thủy damage |
| Ma Thể (None) + Hỗn Độn (All) | +10% lifesteal + random element bonus |
| Bá Thể (Hỏa) + Chân Long (Thủy) | -5% element damage (conflict) |

---

## 8. UI Pages

### 8.1 Thể Chất — `game/thechat.php`

```
┌──────────────────────────────────────┐
│  [Hero Banner: "Thể Chất"]           │
│  "Thiên phú thân thể bẩm sinh"      │
├──────────────────────────────────────┤
│  ┌─ Card Thể Chất Hiện Tại ────────┐│
│  │ [Icon] Tên Thể Chất              ││
│  │ [Element Badge] [Phẩm Badge]     ││
│  │ [Awaken Stage Badge]             ││
│  │                                  ││
│  │ ✨ Ưu thế (Stats):              ││
│  │   +HP / +ATK / +DEF / +Crit     ││
│  │                                  ││
│  │ 💫 Passive:                      ││
│  │   +X% exp / +X% hồi HP / ...    ││
│  │                                  ││
│  │ ⚠️ Phạt:                         ││
│  │   (nếu có penalty từ config)    ││
│  └──────────────────────────────────┘│
│                                      │
│  [⬆ Nâng Cấp] [🌟 Thức Tỉnh]       │
│  [Nhân Vật] [Trở Về]               │
└──────────────────────────────────────┘
```

### 8.2 Thể Chất Nâng Cấp — `game/thechat_upgrade.php`

```
┌──────────────────────────────────────┐
│  [Hero: "Nâng Cấp Thể Chất"]        │
├──────────────────────────────────────┤
│  Phẩm hiện tại: [Phẩm Badge]        │
│  Phẩm tiếp theo: [Next Badge]       │
│                                      │
│  Yêu cầu:                           │
│  💎 Linh Thạch: 20,000              │
│  📦 Vật phẩm: 8x Huyền Thạch       │
│  Tỷ lệ: 60%                         │
│                                      │
│  [Nâng Cấp]                         │
│                                      │
│  Nhật ký nâng cấp gần đây:          │
│  ✅ Thành công: Phàm → Linh         │
│  ❌ Thất bại: Linh → Huyền          │
└──────────────────────────────────────┘
```

### 8.3 Huyết Mạch — `game/huyetmach.php`

```
┌──────────────────────────────────────┐
│  [Hero Banner: "Huyết Mạch"]        │
│  "Huyết thống cổ đại"              │
├──────────────────────────────────────┤
│  ┌─ Card Huyết Mạch Hiện Tại ─────┐│
│  │ [Icon] Tên Huyết Mạch            ││
│  │ [Element Badge] [Phẩm Badge]     ││
│  │ [Awaken Stage] [Transform Ready] ││
│  │                                  ││
│  │ ✨ Ưu thế:                      ││
│  │   +ATK / +HP / +Crit / Elem Dmg  ││
│  │                                  ││
│  │ 💫 Passive:                      ││
│  │   +X% element dmg / +X% crit     ││
│  │                                  ││
│  │ 🔥 Biến Thân:                    ││
│  │   [Hình dạng] [Hiệu ứng]        ││
│  │   CD: XX:XX                      ││
│  └──────────────────────────────────┘│
│                                      │
│  [🌟 Thức Tỉnh] [🔥 Biến Thân]     │
│  [Nhân Vật] [Trở Về]               │
└──────────────────────────────────────┘
```

### 8.4 Huyết Mạch Thức Tỉnh — `game/huyetmach_awakening.php`

```
┌──────────────────────────────────────┐
│  [Hero: "Thức Tỉnh Huyết Mạch"]     │
├──────────────────────────────────────┤
│  Stage hiện tại: [Badge]            │
│  Stage tiếp theo: [Next Badge]      │
│                                      │
│  Yêu cầu thức tỉnh:                 │
│  🏔 Cảnh giới: ≥ Kim Đan           │
│  📦 Vật phẩm: 5x Thánh Thạch       │
│  👹 Boss: Đánh bại [Boss Name]     │
│  📜 Quest: [Quest Name]             │
│                                      │
│  [Thức Tỉnh]                         │
│                                      │
│  ┌─ Quest Progress ────────────────┐│
│  │ Objective 1: ✅/❌               ││
│  │ Objective 2: ✅/❌               ││
│  └──────────────────────────────────┘│
└──────────────────────────────────────┘
```

### 8.5 Routes trong `game.php`

```php
'thechat'           => 'game/thechat.php',
'thechat_upgrade'   => 'game/thechat_upgrade.php',
'thechat_awakening' => 'game/thechat_awakening.php',
'huyetmach'         => 'game/huyetmach.php',
'huyetmach_awakening' => 'game/huyetmach_awakening.php',
'huyetmach_transform' => 'game/huyetmach_transform.php',
```

### 8.6 Nút trong `nowmid.php`

```html
<a class="tcg-action-btn" href="?cmd=thechat">
  <span class="tcg-action-icon">🛡</span>
  <span class="tcg-action-label">Thể Chất</span>
</a>
<a class="tcg-action-btn" href="?cmd=huyetmach">
  <span class="tcg-action-icon">🩸</span>
  <span class="tcg-action-label">Huyết Mạch</span>
</a>
```

---

## 9. Thứ tự triển khai

### Phase 1: Config + DB (làm trước)
1. Tạo `thechat_config.json` — 12 items
2. Tạo `thechat_upgrade_config.json` — 9 cấp
3. Tạo `thechat_awakening_config.json` — 3 stage
4. Tạo `huyetmach_config.json` — 11 items
5. Tạo `huyetmach_awakening_config.json` — 4 stage
6. Tạo `huyetmach_transform_config.json` — 11 biến thân
7. Chạy SQL tạo cột + bảng mới

### Phase 2: Function Layer
8. Tạo `class/function/thechat.php` — config loader + assign + calc + upgrade + awakening
9. Tạo `class/function/huyetmach.php` — config loader + assign + calc + awakening + transform

### Phase 3: Player Integration
10. Sửa `class/player.php` — thêm properties + getplayer() stat application
11. Sửa `game/zhuangtai.php` — hiển thị thể chất + huyết mạch + combat power

### Phase 4: UI Pages
12. Tạo `game/thechat.php`
13. Tạo `game/thechat_upgrade.php`
14. Tạo `game/thechat_awakening.php`
15. Tạo `game/huyetmach.php`
16. Tạo `game/huyetmach_awakening.php`
17. Tạo `game/huyetmach_transform.php`

### Phase 5: Routes + Navigation
18. Sửa `game.php` — thêm 6 routes
19. Sửa `game/nowmid.php` — thêm 2 nút

### Phase 6: Combat Integration
20. Sửa `game/pve.php` — element bonus từ huyết mạch
21. Sửa `game/boss.php` — element bonus + transform trigger
22. Sửa `game/vandinhdai.php` — element bonus trong arena
23. Sửa `game/toayeuthap.php` — element bonus khi leo tháp
24. Sửa `game/xiulian.php` — exp bonus từ thể chất + huyết mạch

### Phase 7: CSS + Polish
25. Sửa `css/mystyle.css` — thêm class cho UI mới
26. Sửa `class/config/daoju_config.json` — thêm 9 vật phẩm nâng cấp

---

## 10. Lưu ý kỹ thuật

### PHP 5.6 Constraints
- KHÔNG dùng `??` → dùng `isset($x) ? $x : default`
- KHÔNG dùng typed properties → `var $thechat_id;`
- KHÔNG dùng return types
- KHÔNG dùng ` <=> `
- Heredoc: KHÔNG dùng ternary `? :` bên trong `{$}` → gán biến trước

### MySQL 5.5 Constraints
- KHÔNG dùng `JSON` type → dùng `TEXT` cho progress column
- KHÔNG dùng `DEFAULT CURRENT_TIMESTAMP` → dùng `NOW()` trong PHP
- Engine: `InnoDB` cho foreign key support

### Static Cache Pattern
```php
function thechat_config_all() {
    static $cache = null;
    if ($cache !== null) return $cache;
    // load JSON...
    $cache = $data;
    return $cache;
}
```

### Dual Path Fallback
```php
function thechat_config_path() {
    $base = dirname(__DIR__);
    $p1 = $base . '/config/thechat_config.json';
    if (file_exists($p1)) return $p1;
    $p2 = dirname($base) . '/config/thechat_config.json';
    if (file_exists($p2)) return $p2;
    return $p1;
}
```

### Namespace Guard
```php
namespace player;
if (!function_exists(__NAMESPACE__ . '\\thechat_config_get')) {
    function thechat_config_get($id) { ... }
}
```

### Penalty Cap
```php
// Tất cả % penalty capped at 80%
$player->ufy = max(1, round($player->ufy * (100 - min(80, $penalty)) / 100));
```

---

## Tổng kết

| Metric | Giá trị |
|---|---|
| File mới | 13 |
| File sửa | 8 |
| Config items | 12 (TC) + 11 (HM) = 23 |
| DB columns mới | 6 |
| DB tables mới | 5 |
| Routes mới | 6 |
| UI pages mới | 6 |
| Vật phẩm mới | 9 |
| Thần Thông mới | 8 |
| Biến thân forms | 11 |
