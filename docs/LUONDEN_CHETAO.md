# Plan: Hệ Thống Luyện Đan & Chế Tạo

## Ngày tạo: 19/07/2026

---

## Hiện Trạng

| Hệ thống | Trạng thái |
|-----------|-----------|
| Đan dược (yaodan) | Mua từ shop, dùng trực tiếp, KHÔNG Luyện đan |
| Thuốc (yaopin) | Mua từ shop, hồi HP, KHÔNG chế tạo |
| Vật liệu (daoju) |Rơi xuống từ PVE, bán lấy linh thạch, CHƯA dùng để chế tạo |
| Luyện đan | ❌ KHÔNG CÓ |
| Chế tạo trang bị | ❌ KHÔNG CÓ |
| Bonus Luyện đan từ linh căn | ❌ KHÔNG CÓ |

**=> Hệ thống hiện tại: Mua potion → Dùng. Không có crafting.**

---

## Tổng Quan Plan

Xây dựng 2 hệ thống mới:
1. **Luyện Đan** - Chế tạo đan dược từ nguyên liệu, có tỷ lệ thành công, chất lượng ngẫu nhiên
2. **Chế Tạo** - Nâng cấp/trộn trang bị từ nguyên liệu, có tier

Liên kết với **Linh Căn**: Element linh căn ảnh hưởng đến tỷ lệ thành công & chất lượng Luyện đan.

---

## Phần 1: Hệ Thống Nguyên Liệu (Mở Rộng)

### 1.1 Thêm nguyên liệu Luyện đan vào daoju_config.json

| ID | Tên | Loại | Giá mua | Mô tả |
|----|-----|------|---------|--------|
| 20 | Thảo Dược Phổ Thông | nguyenlieu | 100 | Nguyên liệu cơ bản |
| 21 | Thảo Dược Quý Hiếm | nguyenlieu | 500 | Nguyên liệu hiếm |
| 22 | Hỏa Tinh Thạch | nguyenlieu | 300 | Bếp đan Hỏa hệ |
| 23 | Thủy Tinh Thạch | nguyenlieu | 300 | Bếp đan Thủy hệ |
| 24 | Mộc Tinh Thạch | nguyenlieu | 300 | Bếp đan Mộc hệ |
| 25 | Kim Tinh Thạch | nguyenlieu | 300 | Bếp đan Kim hệ |
| 26 | Thổ Tinh Thạch | nguyenlieu | 300 | Bếp đan Thổ hệ |
| 27 | Binh Anh Tinh Thạch | nguyenlieu | 1000 | Nâng cấp chất lượng |

### 1.2 Nguồn nguyên liệu
- **Mua từ shop**: Thảo Dược Phổ Thông, Thảo Dược Quý Hiếm
- **Rơi xuống từ PVE**: Tất cả tinh thạch (tùy element quái vật)
- **Rơi xuống từ boss Linh Căn**: Tinh Thạch + Thảo Dược Quý
- **Quest linh căn**: Thêm nguyên liệu làm phần thưởng

---

## Phần 2: Hệ Thống Luyện Đan

### 2.1 Config Recipes

**File mới:** `class/config/luonden_config.json`

```json
{
  "recipes": {
    "1": {
      "id": 1,
      "name": "Hoàn Nguyên Đan",
      "type": "heal",
      "materials": [
        {"daoju_id": 20, "amount": 3},
        {"daoju_id": 22, "amount": 1}
      ],
      "result_yaodan_id": 0,
      "result_yaopin_id": 6,
      "result_amount": 1,
      "base_success_rate": 80,
      "base_quality": "common",
      "level_required": 1,
      "description": "Đan dược hồi phục 100 HP"
    },
    "2": {
      "id": 2,
      "name": "Dưỡng Khí Đan",
      "type": "stat",
      "materials": [
        {"daoju_id": 20, "amount": 5},
        {"daoju_id": 23, "amount": 2}
      ],
      "result_yaodan_id": 1,
      "result_amount": 1,
      "base_success_rate": 70,
      "base_quality": "common",
      "level_required": 5,
      "description": "Tăng ATK +10 tạm thời"
    },
    "3": {
      "id": 3,
      "name": "Cựu Âm Chân Đan",
      "type": "stat",
      "materials": [
        {"daoju_id": 21, "amount": 2},
        {"daoju_id": 26, "amount": 3},
        {"daoju_id": 24, "amount": 2}
      ],
      "result_yaodan_id": 3,
      "result_amount": 1,
      "base_success_rate": 50,
      "base_quality": "rare",
      "level_required": 20,
      "description": "Tăng ATK +100, giảm DEF -100"
    }
  },
  "quality_multipliers": {
    "common": 1.0,
    "uncommon": 1.2,
    "rare": 1.5,
    "epic": 2.0,
    "legendary": 3.0
  },
  "element_bonus": {
    "hoa": {"success_bonus": 0.15, "quality_bonus": 0},
    "thuy": {"success_bonus": 0, "quality_bonus": 0.10},
    "moc": {"success_bonus": 0.10, "quality_bonus": 0.05},
    "kim": {"success_bonus": 0, "quality_bonus": 0.15},
    "tho": {"success_bonus": 0.05, "quality_bonus": 0.10}
  }
}
```

### 2.2 Logic Luyện Đan

**File mới:** `class/function/luonden.php`

```
luonden_craft($sid, $recipeId, $amount, $dblj):
  1. Kiểm tra recipe tồn tại
  2. Kiểm tra level >= level_required
  3. Kiểm tra đủ nguyên liệu (daoju inventory)
  4. Tính tỷ lệ thành công:
     - base_success_rate
     + linh_can_element_bonus (nếu element match recipe type)
     + (cong_phap bonus nếu có)
     - (level gap penalty)
  5. Random thành công/thất bại
  6. Nếu thành công:
     a. Trừ nguyên liệu
     b. Tính chất lượng (common → legendary) dựa trên:
        - base_quality
        + linh_can_element quality_bonus
        + luck random roll
     c. Thêm kết quả vào yaodan/yaopin inventory
     d. Log Luyện đan history
     e. Cập nhật quest progress (nếu có)
  7. Nếu thất bại:
     a. Trừ 50% nguyên liệu
     b. Không nhận kết quả
```

### 2.3 UI Luyện Đan

**File mới:** `game/luonden.php`

Giao diện:
- Chọn recipe từ danh sách (hiển thị tên, mô tả, nguyên liệu cần)
- Hiển thị tỷ lệ thành công (có +/- từ linh căn element)
- Nút "Luyện Đan" với confirm dialog
- Kết quả: thành công/thất bại + chất lượng nhận được
- Hiển thị nguyên liệu hiện có / cần

### 2.4 Routes

```php
// game.php thêm:
'luonden' => 'game/luonden.php',
```

---

## Phần 3: Hệ Thống Chế Tạo Trang Bị

### 3.1 Concept

Cho phép玩家 kết hợp nguyên liệu + trang bị hiện tại để nâng cấp trang bị.

### 3.2 Config

**File mới:** `class/config/chetao_config.json`

```json
{
  "recipes": {
    "1": {
      "id": 1,
      "name": "Nâng Cấp Kiếm Phàm",
      "type": "upgrade_zb",
      "materials": [
        {"daoju_id": 20, "amount": 5},
        {"daoju_id": 22, "amount": 2}
      ],
      "required_zb_type": "sword",
      "required_zb_tier": 1,
      "result_zb_tier": 2,
      "base_success_rate": 90,
      "level_required": 5
    },
    "2": {
      "id": 2,
      "name": "Rèn Thanh Kiếm Cao Cấp",
      "type": "forge",
      "materials": [
        {"daoju_id": 21, "amount": 3},
        {"daoju_id": 25, "amount": 5}
      ],
      "result_zb_id": 10,
      "result_amount": 1,
      "base_success_rate": 60,
      "level_required": 15
    }
  }
}
```

### 3.3 Logic

**File mới:** `class/function/chetao.php`

```
chetao_craft($sid, $recipeId, $dblj):
  1. Kiểm tra recipe tồn tại
  2. Kiểm tra level >= level_required
  3. Kiểm tra đủ nguyên liệu
  4. Kiểm tra trang bị đầu vào (nếu type = upgrade_zb)
  5. Tính tỷ lệ thành công (tương tự Luyện đan)
  6. Random thành công/thất bại
  7. Nếu thành công:
     - Trừ nguyên liệu + trang bị đầu vào
     - Thêm trang bị kết quả vào inventory
     - Log lịch sử
  8. Nếu thất bại:
     - Trừ 50% nguyên liệu
     - Trang bị đầu vào bị giảm 1 tier (hoặc mất)
```

### 3.4 UI

**File mới:** `game/chetao.php`

Giao diện:
- Chọn recipe
- Hiển thị nguyên liệu cần / đang có
- Hiển thị trang bị đầu vào (nếu cần)
- Tỷ lệ thành công
- Nút "Chế Tạo"

---

## Phần 4: Tích Hợp Linh Căn

### 4.1 Bonus Luyện đan từ Element

| Element | Bonus Thành Công | Bonus Chất Lượng |
|---------|-----------------|-----------------|
| Hỏa | +15% | +0% |
| Thủy | +0% | +10% |
| Mộc | +10% | +5% |
| Kim | +0% | +15% |
| Thổ | +5% | +10% |
| All (Niết) | +10% | +10% |
| None (Nghịch) | -5% | +20% |

### 4.2 Code tích hợp

```php
// Trong luonden.php hoặc chetao.php
function getLinhCanAlchemyBonus($player, $dblj) {
    $element = $player->linhcan_element;
    $config = linhcan_config_get($player->linhcan_id);
    
    // Đọc element_bonus từ luonden_config.json
    $bonusConfig = getLuondenElementBonus();
    $bonus = isset($bonusConfig[$element]) ? $bonusConfig[$element] : array('success_bonus' => 0, 'quality_bonus' => 0);
    
    return $bonus;
}
```

---

## Phần 5: Mở Rộng Đan Dược Hiện Tại

### 5.1 Thêm Phẩm chất cho yaodan

Thêm field vào `yaodan_config.json`:
```json
{
  "quality": "common",
  "quality_multipliers": {
    "common": 1.0,
    "uncommon": 1.2,
    "rare": 1.5
  }
}
```

### 5.2 Mở rộng shop bán nguyên liệu

Thêm tab "Nguyên Liệu" trong shop:
- Thảo Dược Phổ Thông: 100 linh thạch
- Thảo Dược Quý Hiếm: 500 linh thạch
- Tinh Thạch element: 300 linh thạch (5 loại)

---

## Phần 6: Database

```sql
-- Bảng lịch sử Luyện đan
CREATE TABLE player_luonden_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid INT NOT NULL,
  recipe_id INT NOT NULL,
  success TINYINT(1) DEFAULT 0,
  quality VARCHAR(20),
  amount INT DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng lịch sử chế tạo
CREATE TABLE player_chetao_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid INT NOT NULL,
  recipe_id INT NOT NULL,
  success TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## Phần 7: Files Cần Tạo

| File | Mô tả |
|------|-------|
| `class/config/luonden_config.json` | Config recipes Luyện đan |
| `class/config/chetao_config.json` | Config recipes chế tạo |
| `class/function/luonden.php` | Logic Luyện đan |
| `class/function/chetao.php` | Logic chế tạo |
| `game/luonden.php` | UI Luyện đan |
| `game/chetao.php` | UI chế tạo |

## Files Cần Sửa

| File | Thay đổi |
|------|----------|
| `class/config/daoju_config.json` | Thêm nguyên liệu Luyện đan |
| `class/config/yaodan_config.json` | Thêm Phẩm chất field |
| `class/config/yaopin_config.json` | Thêm Phẩm chất field |
| `class/player.php` | Thêm require luonden.php, chetao.php |
| `game.php` | Thêm routes luonden, chetao |
| `game/linhcan.php` | Thêm link đến Luyện đan (element bonus) |
| `linhcan_expansion_plan.md` | Cập nhật Luyện đan đã triển khai |
| `linhcan_dev_notes.md` | Cập nhật |

---

## Thứ Tự Triển Khai

### Phase 1: Luyện đan cơ bản
1. Tạo `luonden_config.json` (5-10 recipes)
2. Tạo `luonden.php` logic (config loader + craft function)
3. Thêm nguyên liệu vào `daoju_config.json`
4. Tạo `game/luonden.php` UI
5. Thêm route vào `game.php`
6. Thêm `require_once` vào `player.php`
7. Tích hợp linh căn element bonus

### Phase 2: Chế tạo trang bị
8. Tạo `chetao_config.json` (3-5 recipes)
9. Tạo `chetao.php` logic
10. Tạo `game/chetao.php` UI
11. Thêm route

### Phase 3: Mở rộng & Balance
12. Thêm Phẩm chất cho yaodan/yaopin
13. Mở rộng shop bán nguyên liệu
14. Thêm recipes cao cấp
15. Balance tỷ lệ thành công

### Phase 4: Database & Logging
16. Tạo bảng `player_luonden_log`
17. Tạo bảng `player_chetao_log`

---

## Testing Checklist

- [ ] Luyện đan thành công với đủ nguyên liệu
- [ ] Luyện đan thất bại (mất 50% nguyên liệu)
- [ ]Tỷ lệ thành công đúng theo config
- [ ]Linh căn element bonus đúng (+15% Hỏa, etc)
- [ ]Chất lượng output đúng (common → legendary)
- [ ]Chế tạo trang bị thành công
- [ ]Chế tạo trang bị thất bại
- [ ]UI hiển thị đúng nguyên liệu cần/có
- [ ]Quest linh căn cập nhật khi Luyện đan
- [ ]Nguyên liệu bị trừ đúng
- [ ]Kết quả vào inventory đúng
