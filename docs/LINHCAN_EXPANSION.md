# Kế Hoạch Mở Rộng Hệ Thống Linh Căn

## Tổng Quan

Mở rộng hệ thống linh căn hiện có (22 loại, 5 bậc) với 6 tính năng mới:
1. Tương tác ngũ hành (element interaction)
2. Nâng cấp linh căn (evolution)
3. Kỹ năng linh căn (skill)
4. Tương tác sủng vật (pet synergy)
5. Bonus luyện đan (alchemy bonus)
6. Quest & Boss linh căn

---

## 1. Tương Tác Ngũ Hành

### Mục tiêu
Linh căn elementType ảnh hưởng đến sát thương khi chiến đấu với quái vật có element khác nhau.

### Logic tính toán
```
getElementMultiplier(linhCanElement, monsterElement):
  // Quy tắc: Kim khắc Mộc, Mộc khắc Thổ, Thổ khắc Thủy, Thủy khắc Hỏa, Hỏa khắc Kim
  if linhCanElement == monsterElement: return 1.0
  if linhCanElement khắc monsterElement: return 1.2 (+20% damage)
  if monsterElement khắc linhCanElement: return 0.85 (-15% damage)
  return 1.0
```

### Files cần sửa
- `class/config/linhcan_config.json`: Thêm `"element"` vào mỗi entry (đã có)
- `class/player.php`: Thêm property `$linhcan_element_damage_bonus` trong getplayer()
- `game/boss.php`: Áp dụng multiplier khi tính damage
- `game/pve.php`: Áp dụng multiplier khi tính damage
- `game/zhuangtai.php`: Hiển thị element bonus

### Hiển thị
- Trang trạng thái: "Hỏa hệ: +20%伤 lên Kim hệ quái"
- Khi đánh boss: "Linh căn Hỏa khắc Kim! +20%伤"

---

## 2. Nâng Cấp Linh Căn (Evolution)

### Mục tiêu
Cho phép người chơi nâng linh căn lên bậc cao hơn, tăng %bonus.

### Chi phí nâng cấp
| Bậc hiện tại | Bậc tiếp theo | Linh thạch | Đá ngũ hành | Tỷ lệ thành công |
|-------------|--------------|-----------|------------|-----------------|
| Phàm (0) | Huyền (1) | 500 | 10 | 100% |
| Huyền (1) | Địa (2) | 2000 | 30 | 80% |
| Địa (2) | Thiên (3) | 8000 | 80 | 60% |
| Thiên (3) | Thánh (4) | 25000 | 200 | 40% |
| Thánh (4) | Niết (5) | 80000 | 500 | 20% |

### Files cần tạo
- `game/linhcan_upgrade.php`: Trang nâng cấp

### Files cần sửa
- `class/player.php`: Thêm hàm `linhcan_upgrade($sid, $dblj)`
- `game.php`: Thêm route `'linhcan_upgrade'=>'game/linhcan_upgrade.php'`

### Logic
1. Kiểm tra bậc hiện tại < 5
2. Kiểm tra đủ linh thạch & đá ngũ hành
3. Random %thành công
4. Nếu thành công: `linhcan_id` = linh căn bậc cao hơn cùng element
5. Nếu thất bại: mất nguyên liệu, không mất linh căn

---

## 3. Kỹ Năng Linh Căn

### Mục tiêu
Mở khóa skill đặc biệt dựa trên element linh căn.

### Danh sách skill

| Element | Tên skill | Hiệu ứng | Level mở khóa |
|---------|----------|----------|--------------|
| Hỏa | Hỏa Diệm Chưởng | Damage AOE = 150% ATK | 30 |
| Thủy | Thủy Hộ Thuật | Hồi 8% HP mỗi turn | 25 |
| Mộc | Mộc Linh Thuật | +25% hút máu 3 turn | 20 |
| Kim | Kim Cương Chưởng | +40% bạo kích 1 turn | 35 |
| Thổ | Thổ Hộ Thuật | +30% phòng ngự 3 turn | 28 |
| Không | Hư Không Chưởng | Bỏ qua 50% phòng ngự | 40 |

### Files cần tạo
- `class/config/linhcan_skill.json`: Config skill
- `class/function/linhcan_skill.php`: Logic skill

### Files cần sửa
- `game/boss.php`: Áp dụng skill khi chiến đấu
- `game/pve.php`: Áp dụng skill khi chiến đấu
- `game/linhcan.php`: Hiển thị skill info

### Điều kiện kích hoạt
- Level nhân vật >= level yêu cầu
- Cooldown 5 phút mỗi lần dùng
- MP cost = 10% max MP

---

## 4. Tương Tác Sủng Vật

### Mục tiêu
Linh căn element ảnh hưởng đến stat growth của sủng vật.

### Logic
```
getPetBonusFromLinhCan(linhCanElement, petElement):
  if linhCanElement == petElement:
    return ['exp' => 0.20, 'atk' => 0.10, 'hp' => 0.10]  // +20% exp, +10% atk/hp
  if linhCanElement khắc petElement:
    return ['exp' => 0.10, 'atk' => 0.05, 'hp' => 0.05]  // +10% exp, +5% atk/hp
  return ['exp' => 0, 'atk' => 0, 'hp' => 0]
```

### Files cần sửa
- `class/player.php`: Hàm `getchongwu()` - áp dụng pet bonus
- `game/chongwu.php`: Hiển thị element synergy

### Hiển thị
- Trang sủng vật: "Linh căn Hỏa tương thích Hỏa hệ sủng vật: +20% exp"

---

## 5. Bonus Luyện Đan

### Mục tiêu
Linh căn element ảnh hưởng đến tỷ lệ thành công & chất lượng đan dược.

### Logic
```
getElementAlchemyBonus(linhCanElement):
  Hỏa: ['success_bonus' => 0.15, 'quality_bonus' => 0]      // +15% thành công
  Thủy: ['success_bonus' => 0, 'quality_bonus' => 0.10]      // +10% chất lượng
  Mộc: ['success_bonus' => 0.10, 'quality_bonus' => 0.05]    // +10% thành công, +5% chất lượng
  Kim: ['success_bonus' => 0, 'quality_bonus' => 0.15]       // +15% chất lượng
  Thổ: ['success_bonus' => 0.05, 'quality_bonus' => 0.10]    // +5% thành công, +10% chất lượng
```

### Files cần sửa
- `game/duocpham.php` hoặc file luyện đan tương ứng: Áp dụng bonus

---

## 6. Quest & Boss Linh Căn

### 6.1 Quest Hàng Tuần "Nghiệp Linh Căn"
- Nhận quest từ NPC "Linh Căn Sư"
- Hoàn thành 5 nhiệm vụ:
  1. Tu luyện 60 phút
  2. đánh bại 10 quái element khác
  3.炼 3 đan dược
  4. Nuôi pet 1 level
  5. Hoàn thành 1 boss
- Phần thưởng: 1 Đá Ngũ Hành + 100 Linh Thạch

### 6.2 Boss Linh Căn
- Spawn mỗi 2 giờ
- Element = element của người chơi có linh căn cao nhất online
-Rơi xuống: Đá Ngǔ Hành, nguyên liệu nâng cấp, rare linh căn item

### Files cần tạo
- `game/linhcan_quest.php`: Quest logic
- `game/linhcan_boss.php`: Boss spawn & fight

### Files cần sửa
- `game.php`: Thêm routes

---

## 7. Bảng Giá Nguyên Liệu

| Nguyên liệu | Giá | Nguồn |
|------------|-----|-------|
| Đá Kim Hành | 200 Linh Thạch | Shop / BossRơi xuống |
| Đá Mộc Hành | 200 Linh Thạch | Shop / BossRơi xuống |
| Đá Thủy Hành | 200 Linh Thạch | Shop / BossRơi xuống |
| Đá Hỏa Hành | 200 Linh Thạch | Shop / BossRơi xuống |
| Đá Thổ Hành | 200 Linh Thạch | Shop / BossRơi xuống |

---

## Thứ Tự Triển Khai

### Phase 1 (Ưu tiên cao)
1. ✅ Tương tác ngũ hành (combat effect)
2. ✅ Nâng cấp linh căn (progression)
3. ✅ Bonus luyện đan (crafting depth)

### Phase 2 (Ưu tiên trung bình)
4. ✅ Kỹ năng linh căn (excitement)
5. ✅ Tương tác sủng vật (system integration)

### Phase 3 (Ưu tiên thấp)
6. ✅ Quest hàng tuần (engagement)
7. ✅ Boss linh căn (endgame content)

---

## Database Changes

```sql
-- Thêm bảng lưu log nâng cấp linh căn
CREATE TABLE player_linhcan_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid INT NOT NULL,
  from_id INT NOT NULL,
  to_id INT NOT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Thêm bảng cooldown skill linh căn
CREATE TABLE player_linhcan_skill (
  sid INT NOT NULL,
  skill_id VARCHAR(50) NOT NULL,
  last_used DATETIME,
  PRIMARY KEY (sid, skill_id)
);

-- Thêm bảng quest linh căn
CREATE TABLE player_linhcan_quest (
  sid INT NOT NULL,
  quest_week INT NOT NULL,  -- YYYYWW format
  progress JSON,
  completed TINYINT(1) DEFAULT 0,
  PRIMARY KEY (sid, quest_week)
);
```

---

## Chi Tiết Từng File

### 1. `class/config/linhcan_config.json`
Thêm/sửa:
```json
{
  "id": 1,
  "name": "Kim Linh Căn",
  "element": "kim",  // ĐÃ CÓ
  "tier": 1,
  "buff": {...},
  "nerf": {...},
  "skill_id": "kim_cuong_chuong",  // MỚI
  "supported_cp": ["metal", "none"]
}
```

### 2. `class/config/linhcan_skill.json` (MỚI)
```json
{
  "fire_hoa_diem_chuong": {
    "name": "Hỏa Diệm Chưởng",
    "element": "fire",
    "type": "damage",
    "multiplier": 1.5,
    "aoe": true,
    "mp_cost_percent": 10,
    "cooldown_minutes": 5,
    "unlock_level": 30
  },
  "water_thuy_ho_thuat": {
    "name": "Thủy Hộ Thuật",
    "element": "water",
    "type": "heal",
    "heal_percent": 8,
    "duration_turns": 3,
    "mp_cost_percent": 10,
    "cooldown_minutes": 5,
    "unlock_level": 25
  }
}
```

### 3. `class/player.php`
Thêm vào getplayer():
```php
// Element damage bonus (đã plan ở trên)
$player->linhcan_element_damage_bonus = 0;

// Skill unlock check
$player->linhcan_skill_unlocked = false;
$player->linhcan_skill_id = '';
$player->linhcan_skill_cooldown = 0;
```

### 4. `game/boss.php` / `game/pve.php`
Thêm vào phần tính damage:
```php
// Element multiplier
$element_mult = getElementMultiplier($player->linhcan_element, $boss->element);
$gphurt = round($gphurt * $element_mult);
```

### 5. `game/linhcan.php`
Thêm section hiển thị:
```php
// Element interaction
echo "<div class='lc-section'>";
echo "<div class='lc-section-title'>Ngũ Hành Tương Khắc</div>";
// Hiển thị %bonus/skill info
echo "</div>";
```

---

## Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Linh căn quá mạnh | Game balance | Giới hạn max bonus tại 80% penalty |
| Nâng cấp quá dễ |flation | Tỷ lệ thành công giảm dần |
| Skill quáOP | Combat boring | Cooldown 5 phút, MP cost cao |
| Boss quá khó | Player frustration | Drop rate cao, nhiều phần thưởng |

---

## Testing Checklist

- [ ] Tính damage với element multiplier
- [ ] Nâng cấp linh căn thành công/thất bại
- [ ] Kích hoạt skill linh căn
- [ ] Pet bonus từ linh căn
- [ ] Bonus Luyện đan
- [ ] Quest hoàn thành
- [ ] Boss spawn &Rơi xuống
