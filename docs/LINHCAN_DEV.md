# Notes Phát Triển Hệ Thống Linh Căn

## Ngày tạo: 19/07/2026
## Cập nhật: 19/07/2026 - Thêm hệ thống Luyện Đan & Chế Tạo

---

## Tổng Quan Hệ Thống

- **22 loại linh căn**, 5 bậc (Phàm → Huyền → Địa → Thiên → Thánh → Niết)
- **5 element**: Kim, Mộc, Thủy, Hỏa, Thổ (+ None/All cho bậc cao)
- **6 tính năng mở rộng**: Ngũ hành, Nâng cấp, Skill, Sủng vật, Luyện đan, Quest/Boss

---

## Ghi Chú Kỹ Thuật

- **PHP version**: 5.6 - KHÔNG dùng `??`, `<=>`, typed properties, return types
- Dùng `isset() ? x : y` thay vì `??`
- MySQL version cũ: KHÔNG dùng JSON type → dùng TEXT
- Heredoc PHP: KHÔNG dùng `<?= ?>` bên trong heredoc → phải khai biến trước

---

## Tính Năng Đã Triển Khai

### 1. Tương Tác Ngũ Hành ✅
- **File**: `class/function/linhcan.php`
- **Hàm**: `linhcan_element_khac()`, `linhcan_get_element_multiplier()`
- **Logic**: Kim khắc Mộc, Mộc khắc Thổ, Thổ khắc Thủy, Thủy khắc Hỏa, Hỏa khắc Kim
- **Bonus**: +20% damage khi khắc, -15% khi bị khắc
- **Tích hợp**: boss.php, pve.php (đã hoạt động)

### 2. Nâng Cấp Linh Căn ✅ (có bug)
- **File**: `class/function/linhcan.php`, `game/linhcan_upgrade.php`
- **Config**: `class/config/linhcan_upgrade_config.json`
- **Bậc**: Phàm → Huyền → Địa → Thiên → Thánh → Niết
- **Tỷ lệ**: 100% → 80% → 60% → 40% → 20%
- **Chi phí**: Linh thạch + Đá ngũ hành + Linh khí (uexp)

### 3. Kỹ Năng Linh Căn ⚠️ (chưa tích hợp combat)
- **File**: `class/function/linhcan_skill.php`, `class/config/linhcan_skill_config.json`
- **6 skill**: Hỏa Diệm Chưởng, Thủy Hộ Thuật, Mộc Linh Thuật, Kim Cương Chưởng, Thổ Hộ Thuật, Hư Không Chưởng
- **Cooldown**: 5 phút, MP cost: 10% max HP (tạm dùng HP thay MP)
- **⚠️ CHƯA GỌI** từ boss.php/pve.php → skill không thể dùng được

### 4. Tương Tác Sủng Vật ✅
- **Logic**: `class/player.php` (getchongwu)
- **Bonus**: +20% exp, +10% atk/hp khi element giống nhau
- **UI**: Chưa hiển thị synergy trên trang chongwu.php

### 5. Bonus Luyện Đan ❌ CHƯA TRIỂN KHAI
- **Chưa sửa** `game/duocpham.php`
- Cần thêm logic theo element

### 6. Quest Hàng Tuần ⚠️ (1/5 quest hoạt động)
- **File**: `class/function/linhcan_quest.php`, `game/linhcan_quest.php`
- **Quest hoạt động**: kill_monsters (từ pve.php), kill_boss (từ boss.php)
- **Quest CHƯA hoạt động**: xiulian_minutes, use_items, pet_exp
- **Phần thưởng Đá ngũ hành**: Trả về số lượng nhưng KHÔNG thêm vào inventory

### 7. Boss Linh Căn ⚠️ (có bug)
- **File**: `class/function/linhcan_boss.php`, `game/linhcan_boss.php`
- **Spawn**: Mỗi 2 giờ
- **⚠️ BUG**: Typo `$bossConfig['element_bosses']` → tên boss element không hoạt động
- **⚠️ BUG**: Query SQL `linhcan_config` như bảng MySQL nhưng thực tế là JSON file

---

## Danh Sách File

### Config
| File | Trạng thái |
|------|-----------|
| `class/config/linhcan_config.json` | ✅ 22 loại, nhưng thiếu `rarity`, `buff`, `nerf` |
| `class/config/linhcan_upgrade_config.json` | ✅ Đã thêm uexp_cost |
| `class/config/linhcan_skill_config.json` | ✅ 6 skill |
| `class/config/linhcan_quest_config.json` | ✅ 5 quest |
| `class/config/linhcan_boss_config.json` | ✅ 6 element boss |

### Logic
| File | Trạng thái |
|------|-----------|
| `class/function/linhcan.php` | ⚠️ TODO: trừ đá ngũ hành chưa implement |
| `class/function/linhcan_skill.php` | ⚠️ Buff không có expiration, chưa gọi từ combat |
| `class/function/linhcan_quest.php` | ⚠️ Đá ngũ hành reward chưa thêm vào inventory |
| `class/function/linhcan_boss.php` | ❌ BUG: typo `element_bosses`, query JSON như DB |

### UI
| File | Trạng thái |
|------|-----------|
| `game/linhcan.php` | ⚠️ Đọc field không tồn tại: rarity, buff, nerf, supported_cp |
| `game/linhcan_upgrade.php` | ✅ Hiển thị đầy đủ (trừ số đá ngũ hành) |
| `game/linhcan_quest.php` | ✅ Hoạt động |
| `game/linhcan_boss.php` | ✅ Hoạt động |

### Integration
| File | Trạng thái |
|------|-----------|
| `class/player.php` | ✅ Properties + bonuses |
| `game.php` | ✅ Routes + random assign |
| `game/boss.php` | ✅ Element multiplier |
| `game/pve.php` | ✅ Element multiplier |
| `game/xiulian.php` | ✅ Absorb & exp bonus |
| `game/zhuangtai.php` | ✅ Hiển thị stat |
| `game/duocpham.php` | ❌ Chưa tích hợp bonus Luyện đan |
| `game/chongwu.php` | ❌ Chưa hiển thị synergy |

---

## Database

```sql
-- Cột trong game1
ALTER TABLE game1 ADD COLUMN linhcan_id INT DEFAULT 0;

-- Log nâng cấp
CREATE TABLE player_linhcan_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid INT NOT NULL,
  from_id INT NOT NULL,
  to_id INT NOT NULL,
  success TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Cooldown skill (dùng TEXT thay vì JSON cho MySQL cũ)
CREATE TABLE player_linhcan_skill (
  sid INT NOT NULL,
  skill_id VARCHAR(50) NOT NULL,
  last_used DATETIME,
  PRIMARY KEY (sid, skill_id)
);

-- Quest progress (dùng TEXT thay vì JSON)
CREATE TABLE player_linhcan_quest (
  sid INT NOT NULL,
  quest_week VARCHAR(10) NOT NULL,
  progress TEXT,
  completed TINYINT(1) DEFAULT 0,
  PRIMARY KEY (sid, quest_week)
);
```

---

## Chi Phí Nâng Cấp

| Bậc | Linh thạch | Đá ngũ hành | Linh khí (uexp) | Tỷ lệ |
|-----|-----------|------------|----------------|-------|
| Phàm → Huyền | 500 | 10 | 1,000 | 100% |
| Huyền → Địa | 2,000 | 30 | 5,000 | 80% |
| Địa → Thiên | 8,000 | 80 | 20,000 | 60% |
| Thiên → Thánh | 25,000 | 200 | 80,000 | 40% |
| Thánh → Niết | 80,000 | 500 | 300,000 | 20% |

---

## TODO Ưu Tiên Cao

### Bugs cần sửa
1. **linhcan_boss.php**: Typo `element_bosses` — ĐÃ SỬA xong (code hiện đọc đúng key; Task 5 plan Vòng Ngũ Hành đã viết lại hàm drop)
2. **linhcan_boss.php**: Sửa query `linhcan_config` (JSON không phải DB table)
3. **linhcan.php (UI)**: Sửa xóa/sửa field `rarity`, `buff`, `nerf`, `supported_cp` không tồn tại

### Chưa implement
4. **Đá ngũ hành inventory**: Tạo system inventory cho đá ngũ hành (shop + boss drop + upgrade deduct)
5. **Skill tích hợp combat**: Gọi `linhcan_skill_can_use()` + `linhcan_skill_apply()` từ boss.php/pve.php
6. **Skill buff expiration**: Thêm cơ chế xóa buff sau N turns
7. **Quest tracking**: Thêm call `linhcan_quest_update()` từ xiulian.php, duocpham.php
8. **Bonus Luyện đan**: Thêm logic element bonus vào duocpham.php
9. **Tier 5 upgrade**: `linhcan_find_next_tier_id()` cần xử lý element "all"/"none" để cho phép upgrade lên bậc 5

### Chưa hiển thị
10. **Trang chongwu.php**: Hiển thị linh căn synergy
11. **Đá ngũ hành count**: Hiển thị số lượng thay vì "?" trên trang upgrade

### Tùy chọn
12. **Achievement**: Thêm achievement cho linh căn
13. **Balance test**: Test lại tỷ lệ nâng cấp, damage multiplier

---

## Bảng Nguyên Liệu

| Nguyên liệu | Giá | ID | Nguồn |
|------------|-----|-----|-------|
| Đá Kim Hành | 200 Linh Thạch | 1 | Shop / Boss Rơi xuống |
| Đá Mộc Hành | 200 Linh Thạch | 2 | Shop / Boss Rơi xuống |
| Đá Thủy Hành | 200 Linh Thạch | 3 | Shop / Boss Rơi xuống |
| Đá Hỏa Hành | 200 Linh Thạch | 4 | Shop / Boss Rơi xuống |
| Đá Thổ Hành | 200 Linh Thạch | 5 | Shop / Boss Rơi xuống |

---

## Tóm Tắt Trạng Thái

| Tính năng | Trạng thái |
|-----------|-----------|
| Ngũ hành combat | ✅ Hoạt động |
| Nâng cấp | ⚠️ Thiếu trừ đá ngũ hành |
| Skill | ⚠️ Code xong, chưa gọi |
| Sủng vật bonus | ✅ Backend OK, thiếu UI |
| Luyện đan bonus | ✅ Đã tích hợp element linh căn |
| Luyện đan hệ thống | ✅ 10 recipes, UI đầy đủ |
| Chế tạo hệ thống | ✅ 8 recipes, UI đầy đủ |
| Quest | ⚠️ 2/5 quest hoạt động |
| Boss | ⚠️ Có bug element |

---

## Hệ Thống Luyện Đan & Chế Tạo (Mới)

### Files đã tạo
| File | Mô tả |
|------|-------|
| `class/config/luonden_config.json` | Config 10 recipes Luyện đan + element bonus |
| `class/config/chetao_config.json` | Config 8 recipes chế tạo |
| `class/function/luonden.php` | Logic Luyện đan (config, craft, success rate) |
| `class/function/chetao.php` | Logic chế tạo (config, craft, enhance) |
| `game/luonden.php` | UI Luyện đan |
| `game/chetao.php` | UI chế tạo |
| `luonden_chetao_db.sql` | SQL tạo bảng log |

### Files đã sửa
| File | Thay đổi |
|------|----------|
| `class/config/daoju_config.json` | Thêm 8 nguyên liệu Luyện đan (ID 30-37) |
| `class/player.php` | Thêm require luonden.php + chetao.php |
| `game.php` | Thêm routes luonden + chetao |

### Routes mới
- `luonden` → `game/luonden.php` (Luyện Đan)
- `chetao` → `game/chetao.php` (Chế Tạo)

### DB Tables cần tạo
```sql
CREATE TABLE player_luonden_log (...);
CREATE TABLE player_chetao_log (...);
```

### Tác động element linh căn
| Element | Luyện đan Bonus | Chế tạo Bonus |
|---------|-----------|--------------|
| Hỏa | +15% thành công | +10% thành công |
| Thủy | +10% chất lượng | +0% |
| Mộc | +10% thành công, +5% chất lượng | +5% thành công |
| Kim | +15% chất lượng | +15% thành công |
| Thổ | +5% thành công, +10% chất lượng | +8% thành công |
| All (Niết) | +10% thành công, +10% chất lượng | +10% thành công |
| None (Nghịch) | -5% thành công, +20% chất lượng | +0% |

### Nguyên liệu Luyện đan (ID 30-37)
| ID | Tên | Nguồn |
|----|-----|-------|
| 30 | Thảo Dược Phổ Thông | Shop 100 LT / Rơi xuống quái |
| 31 | Thảo Dược Quý Hiếm | Shop 500 LT / Rơi xuống boss |
| 32 | Hỏa Tinh Thạch | Rơi xuống quái Hỏa hệ |
| 33 | Thủy Tinh Thạch | Rơi xuống quái Thủy hệ |
| 34 | Mộc Tinh Thạch | Rơi xuống quái Mộc hệ |
| 35 | Kim Tinh Thạch | Rơi xuống quái Kim hệ |
| 36 | Thổ Tinh Thạch | Rơi xuống quái Thổ hệ |
| 37 | Bình Anh Tinh Thạch | Rơi xuống Boss Linh Căn |
