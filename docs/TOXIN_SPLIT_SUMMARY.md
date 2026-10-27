# Tổng Hợp Thay Đổi - Hệ Thống Đan Độc & Phản Phệ Luyện Đan

## Ngày cập nhật: 2026-07-24

---

## Mục Tiêu
Tách hệ thống **Đan Độc** (sử dụng đan) và **Phản Phệ Luyện Đan** (luyện đan) thành 2 hệ thống riêng biệt, hoạt động độc lập.

---

## Cấu Trúc Database

### Bảng `player_alchemist`
| Cột | Mô tả |
|-----|--------|
| `sid` | ID người chơi (PK) |
| `alch_rank` | Cấp luyện đan sư (1-9) |
| `alch_exp` | Kinh nghiệm luyện đan |
| `total_craft` | Tổng số lần luyện đan thành công |
| `total_toxin` | **Phản Phệ Luyện Đan** - Tích lũy khi LUYỆN ĐAN |
| `pill_toxin` | **Đan Độc** - Tích lũy khi SỬ DỤNG ĐAN |
| `created_at` | Ngày tạo |
| `updated_at` | Ngày cập nhật |

### Migration
```sql
-- Thêm cột pill_toxin
ALTER TABLE player_alchemist 
ADD COLUMN pill_toxin INT(11) DEFAULT 0 COMMENT 'Đan độc từ sử dụng đan';
```

---

## Hai Hệ Thống

### 1. Phản Phệ Luyện Đan (`total_toxin`)
- **Kích hoạt**: Khi luyện đan thành công
- **Hiển thị**: Panel `luonden.php` (màu amber/cảnh báo)
- **Icon**: ⚠️
- **Mức**: 0-1000

### 2. Đan Độc (`pill_toxin`)
- **Kích hoạt**: Khi sử dụng/thôn phệ đan
- **Hiển thị**: Panel `zhuangtai.php` (màu đỏ)
- **Icon**: ☠️
- **Mức**: 0-2000

---

## Bảng Hiệu Ứng Đan Độc (`pill_toxin`)

| Mức | Hiệu ứng |
|-----|-----------|
| 0-100 | Không có hiệu ứng |
| 101-500 | -0.1% stats mỗi 10 điểm (tối đa -50%) |
| 501-1000 | -1% hiệu quả đan dược mỗi 10 điểm |
| 1001-2000 | -1% tỷ lệ đột phá mỗi 100 điểm |
| 2001+ | **Khóa level** - không thể lên cấp |

### Công thức tính
```php
// Stat penalty (từ 100 điểm)
$statPenalty = min(50, intval($pillToxin / 10) * 0.1);

// Pill effectiveness penalty (từ 500 điểm)
$pillPenalty = min(50, intval(($pillToxin - 500) / 10));

// Breakthrough penalty (từ 1000 điểm)
$breakPenalty = min(10, intval(($pillToxin - 1000) / 100));

// Level lock (từ 2000 điểm)
$isLocked = $pillToxin > 2000;
```

---

## Bảng Hiệu Ứng Phản Phệ (`total_toxin`)

| Mức | Hiệu ứng |
|-----|-----------|
| 0-100 | Không có hiệu ứng |
| 101-300 | -5% thành công luyện đan |
| 301-500 | -10% thành công, -1 Đan Văn |
| 501-800 | -15% thành công, -2 Đan Văn |
| 801-1000 | -20% thành công, -3 Đan Văn |
| 1001+ | **Không thể luyện đan** |

---

## Các File Đã Chỉnh Sửa

### Database
- `sql/alchemy_system.sql` - Cấu trúc bảng gốc
- `sql/add_pill_toxin.sql` - Migration thêm cột `pill_toxin`
- `sql/migrate_pill_toxin.php` - Script migration an toàn (UTF-8)

### Backend (PHP)

#### `class/function/luonden.php`
**Hàm mới:**
```php
// Cập nhật đan độc (pill_toxin) - khi dùng đan
luonden_update_pill_toxin($sid, $toxinGain, $dblj)

// Giảm đan độc (pill_toxin) - khi dùng giải độc
luonden_reduce_pill_toxin($sid, $amount, $dblj)

// Lấy stat penalty từ pill_toxin
luonden_get_stat_penalty($sid, $dblj)

// Kiểm tra level lock từ pill_toxin
luonden_is_level_locked($sid, $dblj)
```

**Hàm cập nhật:**
```php
// Áp dụng hiệu ứng - giờ dùng pill_toxin
luonden_apply_toxin_effects($sid, $dblj)

// Giảm toxin theo giờ - giảm cả 2 trường
luonden_reduce_toxin_hourly($dblj)

// Kiểm tra có hiệu ứng không - giờ dùng pill_toxin
luonden_has_toxin_penalty($sid, $dblj)

// Sử dụng đan giải độc - giờ gọi luonden_reduce_pill_toxin()
luonden_use_detox_pill($sid, $dblj)
```

#### `class/player.php`
**Hàm `upplayerlv()`**: Thêm kiểm tra level lock
```php
if (\player\luonden_is_level_locked($sid, $dblj)) {
    return false; // Không thể lên cấp
}
```

**Hàm `useyaopin()`**: Thêm penalty hiệu quả đan dược
```php
$toxinEffects = \player\luonden_apply_toxin_effects($sid, $dblj);
// Áp dụng multiplier = (100 - $pillPenalty) / 100
```

### Frontend (Game Pages)

#### `game/ydinfo.php`
- **Thay đổi**: Dùng `luonden_update_pill_toxin()` thay vì `luonden_update_toxin()`
- **Áp dụng**: Case `useyd` và `daoli` (sử dụng/thôn phệ đan)

#### `game/zhuangtai.php`
- **Thay đổi**: Query `pill_toxin` thay vì `total_toxin`
- **Hiển thị**: Panel Đan Độc với màu đỏ, bar progress, và mô tả hiệu ứng

#### `game/luonden.php`
- **Hiển thị**: Panel Phản Phệ với `total_toxin` (màu amber)

#### `game/pve.php`
```php
// Stat penalty trong combat
$toxinPenalty = \player\luonden_get_stat_penalty($sid, $dblj);
if ($toxinPenalty > 0) {
    $player->ugj = round($player->ugj * (1 - $toxinPenalty / 100));
    $player->ufy = round($player->ufy * (1 - $toxinPenalty / 100));
    $player->umaxhp = round($player->umaxhp * (1 - $toxinPenalty / 100));
}

// EXP penalty
$finalExp = max(1, intval($finalExp * (1 - $toxinPenalty / 100)));
```

#### `game/boss.php`
- EXP penalty khi đánh boss

#### `game/club_boss.php`
- Stat penalty trong combat club boss
- EXP penalty cho thành viên

#### `game/xiulian.php`
- EXP penalty khi tu luyện

#### `game/tupo.php`
```php
// Breakthrough penalty
$toxinEffects = \player\luonden_apply_toxin_effects($sid, $dblj);
foreach ($toxinEffects as $eff) {
    if ($eff['type'] === 'breakthrough_penalty') {
        $rate = max(1, $rate - $eff['value']);
    }
    if ($eff['type'] === 'level_lock') {
        $rate = 0; // Không thể đột phá
    }
}
```

---

## Luồng Dữ Liệu

### Khi Luyện Đan
```
luonden_craft() → luonden_update_toxin() → total_toxin += toxin_base
```

### Khi Sử Dụng Đan
```
ydinfo.php (useyd/daoli) → luonden_update_pill_toxin() → pill_toxin += toxin_base
```

### Khi Tính Hiệu Ứng
```
Combat/EXP/Breakthrough → luonden_apply_toxin_effects() → đọc pill_toxin
                          luonden_get_stat_penalty() → đọc pill_toxin
                          luonden_is_level_locked() → đọc pill_toxin
```

### Khi Giảm Toxin
```
Giải độc → luonden_reduce_pill_toxin() → pill_toxin -= amount
Theo giờ → luonden_reduce_toxin_hourly() → giảm cả 2 trường
```

---

## Config `yaodan_config.json`
Tất cả 16 loại đan đều có `toxin_base`:
| Loại đan | toxin_base | Ghi chú |
|-----------|------------|---------|
| Hồi Khí Đan | 2 | Giảm từ 5 |
| Hỏa Lộ Đan | 3 | Giảm từ 8 |
| Giải Độc Đan | 1 | Giảm từ 3 |
| Các đan khác | 5-25 | Tùy cấp độ |

### Config `luonden_config.json`
```json
"toxin_mult": {
    "Không văn": 0.5,
    "Phàm văn": 1.0,
    "Linh văn": 1.5,
    "Huyền văn": 2.0,
    "Thiên văn": 2.5
}
```

---

## Test Cases

### Test 1: Hai hệ thống độc lập
```
Initial: Phản Phệ=0, Đan Độc=0
Sau luyện đan: Phản Phệ=50, Đan Độc=0
Sau dùng đan: Phản Phệ=50, Đan Độc=30
✅ Đúng
```

### Test 2: Hiệu ứng Đan Độc
```
pill_toxin=150 → Stat penalty = -1.5%
pill_toxin=600 → Stat -6%, Pill penalty = -10%
pill_toxin=1500 → Stat -15%, Pill -10%, Breakthrough -5%
pill_toxin=2500 → Level lock
✅ Đúng
```

### Test 3: PHP Syntax
```
luonden.php: OK
zhuangtai.php: OK
ydinfo.php: OK
pve.php: OK
club_boss.php: OK
xiulian.php: OK
tupo.php: OK
boss.php: OK
player.php: OK
✅ Không có lỗi syntax
```

---

## Lưu Ý Khi Phát Triển

1. **KHÔNG** dùng `luonden_update_toxin()` cho việc sử dụng đan
2. **LUÔN** dùng `luonden_update_pill_toxin()` cho việc sử dụng đan
3. **Hiệu ứng gameplay** (combat/EXP/breakthrough) chỉ dựa trên `pill_toxin`
4. **Hiệu ứng luyện đan** (success rate, dan van) dựa trên `total_toxin`
5. **Giải độc** chỉ giảm `pill_toxin`, không giảm `total_toxin`

---

## File Quan Trọng

```
D:\XXJserver\phpstudy_pro\WWW\
├── sql\
│   ├── alchemy_system.sql          # Cấu trúc gốc
│   ├── add_pill_toxin.sql          # Migration
│   └── migrate_pill_toxin.php      # Script migration
├── class\
│   ├── player.php                  # upplayerlv(), useyaopin()
│   └── function\
│       └── luonden.php             # Tất cả hàm liên quan
├── game\
│   ├── ydinfo.php                  # Tích lũy pill_toxin
│   ├── zhuangtai.php               # Hiển thị Đan Độc
│   ├── luonden.php                 # Hiển thị Phản Phệ
│   ├── pve.php                     # Combat penalty
│   ├── boss.php                    # EXP penalty
│   ├── club_boss.php               # Combat + EXP penalty
│   ├── xiulian.php                 # EXP penalty
│   └── tupo.php                    # Breakthrough penalty
└── class\config\
    ├── yaodan_config.json          # toxin_base cho mỗi đan
    └── luonden_config.json         # toxin_mult theo phẩm chất
```
