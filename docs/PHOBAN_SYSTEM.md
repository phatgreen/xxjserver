# HE THONG PHO BAN - DUNGEON SYSTEM

> Phien ban: 1.0  
> Ngay tao: 22/07/2026  
> Tong so loai pho ban: 4

---

## TONG QUAN

He thong pho ban gom 4 loai chinh, truy cap qua nut **Pho Ban** tren giao dien chinh.

| Loai | File | Mo Ta |
|------|------|-------|
| **Sao Thu** | `sauthu.php` | San yeu thu, thu phuc pet moi |
| **Toa Yeu Thap** | `toayeuthap.php` | Leo thap vuot tang |
| **Van Dinh Dai** | `vandinhdai.php` | PVP dau truong |
| **Ham Mo** | `hammo.php` | Dao khoang san |

---

## 1. SAO THU (PET HUNTING DUNGEON)

### 1.1 Cau truc file

| File | Mo Ta |
|------|-------|
| `game/sauthu.php` | Giao dien chinh + logic battle |
| `class/player.php` | Ham xu ly: `sauthu_state()`, `sauthu_battle()`, `sauthu_get_monsters()`, `sauthu_get_capture_rate()` |
| `class/config/yeuthu_config.json` | Cau hinh map (10 map) |
| `class/config/pet_config.json` | Stat thu cung (30 pet) |
| `class/config/bua_config.json` | Cau hinh bua (djid 70-79) |
| `class/config/daoju_config.json` | Gia bua trong cua hang |

### 1.2 Database

```sql
CREATE TABLE IF NOT EXISTS `player_sauthu` (
    `sid` varchar(64) PRIMARY KEY,
    `current_map` int DEFAULT 1,
    `best_map` int DEFAULT 0,
    `total_fights` int DEFAULT 0,
    `total_captures` int DEFAULT 0,
    `updated_at` datetime
);
```

### 1.3 Config format

**yeuthu_config.json:**
```json
{
    "maps": {
        "1": {
            "name": "Rung Truoc Lang",
            "quality_tier": 0,
            "bua_id": 70,
            "mt_cost": 5000,
            "level_range": [1, 30],
            "monsters": [1, 2, 3],
            "drops": [1, 2]
        }
    }
}
```

**pet_config.json:**
```json
{
    "1": {
        "name": "Ho Da",
        "element": "fire",
        "quality_tier": 0,
        "base_hp": 100,
        "base_atk": 15,
        "base_def": 5,
        "base_crit": 5
    }
}
```

**bua_config.json:**
```json
{
    "70": {
        "name": "Bua Thu Phuc",
        "lt_cost": 10000,
        "map_id": 1
    }
}
```

### 1.4 Logic chinh

**Vao pho ban:**
- Can `bua_count >= 1` (djid 70-79 tuy map)
- Can `mt_cost <= player.uczb` (Ma Thach)
- Level check: `player.ulv >= minLevel` (khong gioi han tren)

**Battle:**
- `sauthu_battle($player, $monster)` tinh damage
- HP giam ve 0 = thang
- Thu phuc: `sauthu_get_capture_rate($quality_tier)` = ty le%

**Thu phuc:**
- Goi `addchongwu_from_pet()` de them vao danh sach
- Cho phep trung lap (cot rieng)

### 1.5 API endpoints

| Command | Mo Ta |
|---------|-------|
| `cmd=sauthu` | Hien thi giao dien chinh |
| `cmd=sauthu&canshu=enter&map=X` | Vao map X |
| `cmd=sauthu&canshu=pay_bua&pay_bua_id=X` | Hien modal mua bua |
| `cmd=sauthu&canshu=buy_bua&buy_bua_id=X&buy_qty=Y` | Mua bua so luong Y |

---

## 2. TOA YEU THAP (MONSTER TOWER)

### 2.1 Cau truc

| File | Mo Ta |
|------|-------|
| `game/toayeuthap.php` | Giao dien + logic leo thap |
| `class/player.php` | Ham lien quan |

### 2.2 Mo ta
- 150 tang, moi tang co quai vat
- Vuot qua tang = nhan thuong
- Bi rot tang khi thua
- Gioi han so luot/ngay

---

## 3. VAN DINH DAI (ARENA)

### 3.1 Cau truc

| File | Mo Ta |
|------|-------|
| `game/vandinhdai.php` | Giao dien + PVP logic |

### 3.2 Mo ta
- PVP voi nguoi choi khac
- Xep hang theo MMR
- Thuong theo hang

---

## 4. HAM MO (MINING)

### 4.1 Cau truc

| File | Mo Ta |
|------|-------|
| `game/hammo.php` | Giao dien + dao khoang |

### 4.2 Mo ta
- Tu dong dao theo luot
- Nhan nguyen lieu, khoang san
- Co ti le may man

---

## 5. PHO BAN HUB (DUNGEON MENU)

### 5.1 Cau truc

| File | Mo Ta |
|------|-------|
| `game/phoban.php` | Trang tong hop 4 pho ban |

### 5.2 Giao dien

```
[Pho Ban]
├── Toa Yeu Thap (PVE) - Leo thap vuot tang
├── Van Dinh Dai (PVP) - Dau truong
├── Ham Mo (PVE) - Dao khoang san
└── Sao Thu (PVE) - San yeu thu
```

### 5.3 Styling
- Hero section: gradient + glow effects
- Card layout: CSS Grid (icon 50px | content | arrow)
- Type badge: PVE (xanh) / PVP (do)
- Responsive, dark theme

---

## 6. LUU Y PHAT TRIEN

### 6.1 Command encoding
- Su dung `$encode->encode("cmd=...&sid=$sid")` cho URL
- Khong dung `encodeURIComponent` trong JS (gay loi redirect)

### 6.2 CSS specificity
- Global CSS `a` tag co the override style
- Can them `!important` cho cac property quan trong

### 6.3 Format chung
- Hero section: gradient background + badge + title + stats
- Cards: grid layout, icon + content + arrow
- Footer: nut "Dai Sanh" thay cho "Quay lai"
- Colors: #00C000 (PVE), #ec0909 (PVP), #f16613 (Sao Thu), #06b6d4 (Ham Mo)

---

## 7. FILES LIEN QUAN

| File | Vi tri |
|------|--------|
| `game/phoban.php` | Dungeon hub |
| `game/sauthu.php` | Pet hunting |
| `game/toayeuthap.php` | Tower |
| `game/vandinhdai.php` | Arena |
| `game/hammo.php` | Mining |
| `class/player.php` | Backend logic |
| `class/config/*.json` | Config files |
| `css/mystyle.css` | Global styles |
| `css/gamecss.css` | Game-specific styles |
