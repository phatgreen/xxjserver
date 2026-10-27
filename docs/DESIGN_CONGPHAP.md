# Thiết Kế Hệ Thống Công Pháp (Passive Buff System)

## 1. Phân Biệt Võ Công vs Công Pháp

| | Võ Công (hiện tại) | Công Pháp (mới) |
|---|---|---|
| **Bản chất** | Kỹ năng chiến đấu chủ động | Phương pháp tu luyện bị động |
| **Tác dụng** | Tăng ATK/DEF/HP/CRIT + hiệu ứng chiến đấu | Tăng tốc độ hấp thụ linh khí, % EXP, % drops, % linh thạch... |
| **Trang bị** | 1 vortex đang trang bị | 1-3 vortex cùng lúc (tùy cảnh giới) |
| **Hiệu lực** | Chỉ trong chiến đấu | Luôn luôn (passive) |
| **Trade-off** | Không có | absorb_speed bị giảm %công, %thủ |

---

## 2. Các Loại Buff Công Pháp (Effect Types)

```
absorb_speed    → Tăng tốc độ hấp thụ linh khí khi ngồi thiền (%)
                  ⚠ CÓ TRADE-OFF: Giảm %công và %thủ
exp_bonus       → Tăng % tu vi nhận được từ mọi nguồn (PVE, task, boss...)
                  ⚠ Level cao có thể có penalty nhẹ
drop_rate       → Tăng % tỉ lệ rơi vật phẩm khi đánh quái
stone_bonus     → Tăng % linh thạch nhận được khi đánh quái / đào mỏ
mining_bonus    → Tăng % linh thạch khi đào mỏ
heal_bonus      → Tăng % hiệu quả hồi phục HP từ potion / trị liệu
crit_dmg        → Tăng % sát thương bạo kích
def_bonus       → Tăng % phòng ngự tổng
hp_regen        → Hồi HP mỗi round chiến đấu (%max HP)
dmg_reduce      → Giảm % sát thương nhận vào
```

---

## 3. Cơ chế Trade-off absorb_speed

### Nguyên tắc
Khi công pháp `absorb_speed` đang equipped → **giảm % công (ugj) và % thủ (ufy)**

```
penalty_ratio = absorb_speed_value × penalty_coefficient
giảm_ugj = ugj × penalty_ratio / 100
giảm_ufy = ufy × penalty_ratio / 100
```

### Ví dụ

| Công pháp | absorb_speed | penalty_coeff | Giảm Công | Giảm Thủ |
|---|---|---|---|---|
| Hỗn Nguyên Tâm Pháp Lv1 | +5% | 0.4 | -2% | -2% |
| Hỗn Nguyên Tâm Pháp Lv5 | +27.5% | 0.4 | -11% | -11% |
| Hỗn Nguyên Tâm Pháp Lv10 (max) | +50% | 0.4 | -20% | -20% |
| Thần Hành Bách biến Lv10 (max) | +60% | 0.5 | -30% | -30% |

**=> Càng buff tu luyện nhanh, càng yếu khi đánh quái.**

---

## 4. Cấu Trúc JSON `congphap_config.json`

```json
{
  "1": {
    "cpid": 1,
    "cpname": "Hỗn Nguyên Tâm Pháp",
    "cpys": "#f9c508",
    "cpsort": "Nội Công",
    "cpinfo": "Pháp môn hỗn nguyên|Tăng tốc độ hấp thu linh khí|...",
    "effect_type": "absorb_speed",
    "effect_value": 5,
    "effect_value_max": 50,
    "penalty_atk_percent": 0.4,
    "penalty_def_percent": 0.4,
    "max_level": 10,
    "realm_require": 0,
    "tier_require": 0,
    "quality": 2
  }
}
```

### Giải thích trường mới
- `effect_value_max`: Giá trị buff tối đa khi max level
- `max_level`: Tầng tối đa của công pháp này
- `penalty_atk_percent`: Hệ số giảm công (0.4 = cứ +1% absorb_speed thì mất 0.4% công)
- `penalty_def_percent`: Hệ số giảm thủ
- `realm_require`: Cảnh giới tối thiểu để học (index trong `$rangesjj`)
- `tier_require`: Tầng trong cảnh giới (0 = bất kỳ tầng nào)
- `quality`: Phẩm chất (1-6) → màu sắc hiển thị

---

## 5. Bảng DB `playercongphap`

```sql
CREATE TABLE playercongphap (
  cpid        INT NOT NULL,
  sid         VARCHAR(32) NOT NULL,
  cpdj        INT DEFAULT 1,       -- cấp hiện tại
  cpexp       INT DEFAULT 0,       -- exp tích lũy của công pháp
  cpexpmax    INT DEFAULT 100,     -- exp cần để lên cấp
  cpequipped  TINYINT DEFAULT 0,   -- 1 = đang trang bị (active)
  PRIMARY KEY (cpid, sid)
);
```

---

## 6. Cách Hoạt Động

### 6a. Học Công Pháp
- Player nhận bí tịch từ:抽 (gacha), boss drop, event, shop
- Kiểm tra `realm_require` vs `$player->jingjie` / `$rangesjj` index
- Insert vào `playercongphap` với level = 1

### 6b. Lên Cấp Công Pháp
- Đánh quái / ngồi thiền → tích `cpexp` vào `playercongphap`
- Khi `cpexp >= cpexpmax` → tăng `cpdj`, reset exp
- `effect_value` tính theo công thức:
  ```
  real_value = base_effect_value + (cpdj - 1) * (effect_value_max - base_effect_value) / (max_level - 1)
  ```

### 6c. Trang bị (Equip)
- `cpequipped = 1` → buff luôn luôn active
- Số lượng slot equip:
  - Cảnh giới 0-5 (Nhập Môn → Ngự Khí): **1 slot**
  - Cảnh giới 6-10 (Luyện Khí → Nguyên Anh): **2 slot**
  - Cảnh giới 11+ (Tụ Thần → Đại Thừa): **3 slot**

### 6d. Áp dụng Buff

**Trong `getplayer()` (player.php)** - SAU khi cộng buff Thần Trang:

```php
// ===== BUFF CÔNG PHÁP (Passive) =====
$cp_absorb_speed = 0;
$cp_penalty_atk = 0;
$cp_penalty_def = 0;
$playerCp = getplayercongphap_equipped($player->sid, $dblj);
if ($playerCp) {
    foreach ($playerCp as $cp) {
        $cfg = congphap_config_get($cp->cpid);
        if (!$cfg) continue;
        $base = floatval($cfg['effect_value']);
        $max  = floatval($cfg['effect_value_max']);
        $lvl  = max(1, intval($cp->cpdj));
        $mLvl = max(1, intval($cfg['max_level']));
        $realValue = $base + ($lvl - 1) * ($max - $base) / max(1, $mLvl - 1);
        
        $type = $cfg['effect_type'];
        if ($type === 'absorb_speed') {
            $cp_absorb_speed += $realValue;
            $cp_penalty_atk += $realValue * floatval($cfg['penalty_atk_percent']);
            $cp_penalty_def += $realValue * floatval($cfg['penalty_def_percent']);
        }
        // ... các effect khác (không có penalty)
    }
}
$player->cp_absorb_speed = $cp_absorb_speed;

// Áp dụng penalty cuối cùng (sau TẤT CẢ buffs)
if ($cp_penalty_atk > 0) {
    $player->ugj = max(1, intval($player->ugj * (100 - $cp_penalty_atk) / 100));
}
if ($cp_penalty_def > 0) {
    $player->ufy = max(1, intval($player->ufy * (100 - $cp_penalty_def) / 100));
}
```

**Trong `xiulian.php`** (sửa tính exp_per_sec):

```php
$cp_absorb = floatval($player->cp_absorb_speed ?? 0);
$base_exp_per_sec = round($player->ulv * $vip_bonus * $exp_multiplier * (1 + $cp_absorb / 100), 4);
```

---

## 7. Danh Sách Công Pháp (12 loại ban đầu)

| # | Tên | Effect | Absorb Speed | Penalty ATK% | Penalty DEF% | Cảnh giới |
|---|---|---|---|---|---|---|
| 1 | Hỗn Nguyên Tâm Pháp | absorb_speed | +5~50% | 0.4 | 0.4 | Nhập Môn |
| 2 | Thiên Địa Quyết | exp_bonus | +3~30% | 0 | 0 | Luyện Khí |
| 3 | Vạn Vật Quy Linh | drop_rate | +3~25% | 0 | 0 | Trúc Cơ |
| 4 | Tụ Bảo Huyền Công | stone_bonus | +5~40% | 0 | 0 | Tụ Khí |
| 5 | Khoáng Mạch Thần Quyết | mining_bonus | +5~50% | 0 | 0 | Tụ Khí |
| 6 | Hồi Xuân Quyết | heal_bonus | +3~30% | 0 | 0 | Luyện Khí |
| 7 | Phá Thiên Quyết | crit_dmg | +2~20% | 0 | 0 | Kim Đan |
| 8 | Bất Động Minh Vương | def_bonus | +3~25% | 0 | 0 | Tụ Thần |
| 9 | Mệnh Huyết Quyết | hp_regen | +1~10% | 0 | 0 | Nguyên Anh |
| 10 | Vô Cực Tản | dmg_reduce | +2~20% | 0 | 0 | Hợp Thể |
| 11 | Thần Hành Bách Biến | absorb_speed | +8~60% | 0.5 | 0.5 | Hóa Thần |
| 12 | Đại Đạo Thiên Thư | exp_bonus | +5~45% | 0.2 | 0.2 | Đại Thừa |

**Chú ý:** Chỉ `absorb_speed` và `exp_bonus` cấp cao mới có penalty. Các effect khác không có penalty.

---

## 8. Flow Tổng Quan

```
[Player ngồi thiền]
  → check absorb_speed từ công pháp equipped
  → exp_per_sec = base * (1 + absorb_speed%)
  → Player nhận tu vi nhanh hơn
  
[Player đánh quái]
  → getplayer() đã trừ penalty công/thủ nếu đang equip absorb_speed
  → Damage = ugj * (100 - penalty%) / 100 → yếu hơn
  → Reward = base * (1 + bonus từ các effect khác)

[Player level up công pháp]
  → cpexp += (exp gain từ combat)
  → if cpexp >= cpexpmax → cpdj++, cpexp = 0
  → recalculate effect_value và penalty
```

---

## 9. Files Cần Tạo/Sửa

| File | Action | Mô tả |
|---|---|---|
| `class/config/congphap_config.json` | **Tạo mới** | Config 12 công pháp |
| `class/function/congphap.php` | **Tạo mới** | Hàm CRUD công pháp |
| `class/player.php` → `getplayer()` | **Sửa** | Thêm block công pháp + penalty |
| `game/congphap.php` | **Sửa lại** | UI quản lý công pháp |
| `game/xiulian.php` | **Sửa** | Áp dụng `absorb_speed` |
| `game/pve.php` | **Sửa** | Áp dụng `drop_rate`, `stone_bonus`, `exp_bonus` |
| `game/boss.php` | **Sửa** | Áp dụng buff khi đánh boss |
| DB `playercongphap` | **Tạo mới** | Bảng lưu data công pháp player |

---

## 10. Cảnh Giới (rangesjj)

```
0-5:   Nhập Môn → Ngự Khí         → 1 slot equip
6-10:  Luyện Khí → Nguyên Anh      → 2 slot equip
11+:   Tụ Thần → Đại Thừa          → 3 slot equip
```

---

*Nội dung thiết kế: 2026-07-13*
*Trạng thái: Draft - Chưa triển khai*
