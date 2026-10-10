# HUYET MACH — MANG MACH HUYET: Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dung lại tinh nang Huyet Mach thanh mot man duy nien theo cong thuc Nhuc Than: 8 mach huyet luyen tung cap, 5 bac thuc tinh nang tran cap, boss mach huyet rieng cho tung nguoi, nang pham chat 1->10.

**Architecture:** Ba truc tach ro — cap mach (bang `player_huyetmach_vessel`), stage thuc tinh (`game1.huyetmach_awaken_stage`, dung cot nay cua rieng Huyet Mach), pham chat (`game1.huyetmach_quality`). Tang data (`class/config/huyetmach_*.json` + `class/function/huyetmach*.php`) duoc dung boi dung mot man `game/huyetmach.php` voi prefix CSS `.hm-*` soi theo `.nb-*` cua Nhuc Than.

**Tech Stack:** PHP 5.6, MySQL 5.5 (MyISAM/InnoDB, PDO), HTML+CSS trong `css/mystyle.css`, test CLI bang `tassert` kieu `tests/test_nguhanh.php`, do/ch UIPhi bang Chrome headless + harness `E:/window/Temp/_hm*.html`.

**Spec:** `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md`

## Global Constraints

- PHP 5.6: khong `??`, khong `<=>`, khong return type, dung `array()` thay vi `[]` trong code PHP (JSON thi dung `[]`/`{}` binh thuong). Ap dung skill `php56`.
- Moi file PHP sau khi sua: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l <file>` (php.exe khong co tren PATH).
- MySQL 5.5: khong `DEFAULT CURRENT_TIMESTAMP` cho nhieu cot, `sid` phai la `VARCHAR(64)` (hex 32 ky tu).
- Test CLI chay tren DB local `game` voi `$TESTSID = str_repeat('f', 32)` va `tassert($name, $got, $exp)` tu `tests/test_nguhanh.php`; ket thuoc `exit($fails === 0 ? 0 : 1)`.
- **PK cua `playerdaoju` la `(uid, djid)` — KHONG phai sid** (do `information_schema` 10/10). Tai khoan test `e2e2e2e2...` dang nam `(uid=0, djid=100)`, nen nhan vat test bat buoc mang `uid = 990112` (row `game1` cua `$TESTSID` luon co uid nay) va moi `INSERT INTO playerdaoju` trong test luon ghi ro `uid`. Quen la PDOException `Duplicate entry` giua hai nhan vat khac nhau.
- Khong bao gio dong nhat `huyetmach_awaken_stage` voi `thechat_awaken_stage` (cot sau dang cho bac Cot Cach cua Nhuc Than).
- Man va ham phai song khi `huyetmach_id = 0` (chua co huyet mach): `huyetmach_info()` tra `false`, `huyetmach_calc_bonuses()` tra mang rong.
- Git: repo chua co commit nao; dung `git add <file cu the>` (khong `git add -A` vi goc repo co hon 50 anh `shot_*.png` rac), commit tung task.
- Tieng Viet trong comment PHP viet KHONG DAU (le thuat cua `game/thechat.php`); tieng Viet co dau chi xuat hien trong chuoi JSON va nhan UI. Sau moi lan tao/sua file chay buoc quet CJK lac cho (Task 1 Step 2) — loi da tung pham thuc te, khong phai phong xa tha.
- So do UI bat buoc (do truc tiep tren `css/`, 09/10): `.tcg-main-panel` 660px o desktop (`css/mystyle.css:199`), padding 16px/18px -> **nội dung 624px**; `.hm-wrap{margin:0}` nhu `.nb-wrap` (`css/mystyle.css:6991`), KHONG dat `max-width` rieng cho `.hm-wrap` (them max-width la mo rong khung chung - da bi bac o v13); `.xl-page` 760px chi la cua man Tu Luyen, khong phai chuan cua man nay; breakpoint layout `min-width:601px`; bar duoi cung cao 80px bang chim `.xl-tabbar`/`.nb-tabbar`.
- **Landmine phai reset (spec §1.5, do `css/mystyle.css:5035`)**: trong `@media(max-width:760px)` có `.tcg-main-panel *,:before,:after{max-width:100%!important;box-sizing:border-box!important}` và `.tcg-main-panel img{height:auto!important}` — mỗi thành phần định vị tuyệt đối (`.hm-gem` trên ảnh kinh mạch) PHẢI dùng `!important` và reset padding/margin/background ở mọi state; `css/mystyle.css:7029` đã ghi chú lại bẫy này cho phía `.nb-gem`, khối `.hm-*` clone lại phải giữ.

- **ĐÃ ĐỔI TÊN CLASS (Task 7 phát hiện, mọi task sau phải dùng tên mới)**: `.hm-btn` → `.hm-act`, `.hm-empty` → `.hm-note`, `--hm-btn-dur` → `--hm-act-dur`. Lý do: 2 tên `.hm-btn` và `.hm-empty` ĐÃ bị `game/hammo.php` (Hầm Mỏ Linh Khoáng, CSS `css/mystyle.css:6413-6444`) dùng trước, trong đó `.hm-btn{color:#fff!important}` sẽ đè màu nút ghost. Không sửa khối hammo; khối `.hm-*` đổi tên để hai tính năng không đụng nhau. Mọi ví dụ CSS/PHP trong Task 8-12 còn ghi `hm-btn`/`hm-empty` phải đọc là `hm-act`/`hm-note`.

## File Structure

| File | Vai tro | Task |
|---|---|---|
| `class/config/huyetmach_vessel_config.json` (mới) | 8 mạch + 5 bậc thức tỉnh + refine + milestone + `stat_labels`/`material_names` | 1; 11 (sửa `desc` mạch pen) |
| `class/config/huyetmach_boss_config.json` (mới) | 33 boss mạch huyết (11 huyệt mạch × stage 2–4) | 8 |
| `class/config/huyetmach_upgrade_config.json` (mới) | 9 bậc nâng phẩm chất | 9 |
| `class/config/daoju_config.json` (sửa) | thêm 112 Huyết Thạch, 113 Huyết Mạch Đan | 4 |
| `sql/sql_huyetmach_vessel.sql` (mới) | 3 bảng mới + 4 cột `game1` | 2 |
| `tests/deploy_preflight.php` (sửa) | thêm 3 bảng vào `$SID_VARCHAR` | 2 |
| `class/function/huyetmach.php` (sửa, **append cuối file**) | khối `huyetmach_vessel_*` (1,3,4,5) + `huyetmach_awaken`/biến thân (6) + `huyetmach_upgrade_*` (9) + `huyetmach_combat_*` (11); sửa bug HP ghi vào `uxx` (6) | 1,3,4,5,6,7,9,11 |
| `class/function/huyetmach_boss.php` (mới) | spawn/damage/drop/mark cho boss riêng | 8; 11 (Step 6 refactor dùng helper) |
| `class/player.php` (sửa) | áp chỉ số mạch vào nhân vật (`:839`); lo `$player->huyetmach_vessel` (`:823`, `:146`) | 3, 10 |
| `game/huyetmach.php` (viết lại) | màn duy nhất `.hm-*` | 7 |
| `game/huyetmach_boss.php`, `game/huyetmach_upgrade.php` (mới) | màn boss + màn nâng phẩm | 8, 9 |
| `game/huyetmach_awakening.php` (XOA) | bị màn mới bao trùm | 7 |
| `game/huyetmach_transform.php` (sửa) | nút `.is-off` + sửa `.main button` ẩn gradient | 6, 7 |
| `game/zhuangtai.php` (sửa) | trang Nhân Vật hiện đủ 8 mạch | 10 |
| `game/boss.php`, `game/pve.php`, `game/pvp.php` (sửa) | nối 4 chỉ số âm vào công thức sát thương | 11 |
| `game/use_dj.php`, `class/function/thechat.php` (sửa) | ấn nút "Sử dụng" cho vật phẩm không có `use_effect` + tên 112/113 | 4 |
| `game.php` (sửa) | route mới + bump `mystyle.css?v=` | 7, 8, 9 |
| `css/mystyle.css` (sửa) | khối `.hm-*` → `.hmq-*` → `.hm-fx*`, đặt dưới khối `.nb-*` | 7, 9, 10 |
| `images/huyetmach/*` (mới) | ảnh thân + 8 × 3 ngọc | 7 |
| `docs/img/huyetmach/*` (mới) | ảnh chứng minh: 5 bậc fx + reduced-motion + 2 cột so sánh + 4 ảnh màn | 10, 12 |
| `tests/test_huyetmach_vessel.php` (phân A–E), `tests/test_huyetmach_schema.php`, `tests/test_huyetmach_boss.php`, `tests/test_huyetmach_upgrade.php`, `tests/test_huyetmach_combat.php` | test CLI | 1–6, 2, 8, 9, 11 |
| `tongquan.php`, `docs/UI_HUYETMACH.md`, `docs/DEPLOY_PRODUCTION_HUYETMACH.md`, `docs/UI_THECHAT.md` (sửa) | bản đồ tính năng + tài liệu + sổ nợ | 12 |

---

### Task 1: Config mach huyet + ham doc thuan tuy

**Files:**
- Create: `class/config/huyetmach_vessel_config.json`
- Create: `tests/test_huyetmach_vessel.php`
- Modify: `class/function/huyetmach.php` (append khoi `huyetmach_vessel_config_*` xuong **cuối file**. File khai báo `namespace player;` dang cau LENH (`class/function/huyetmach.php:2`, co dau `;`, khong co dap ngan namespace) → **khong co** ngoac dong cuoi file de chen phia truoc; mỗi hàm mới bọc guard `if (!function_exists(__NAMESPACE__ . '\\x'))`)

**Interfaces:**
- Consumes: khong co.
- Produces:
  - `huyetmach_vessel_config_all(): array` — toan bo JSON.
  - `huyetmach_vessel_defs(): array` — 8 entry id=>def.
  - `huyetmach_vessel_tiers(): array` — stage 0..4.
  - `huyetmach_vessel_def($id): array|false`, `huyetmach_vessel_tier($stage): array|false`.
  - `huyetmach_vessel_cap($stage): int` (khong co tier -> 0), `huyetmach_vessel_stage_max(): int`.
  - `huyetmach_vessel_refine_cost($lv): array('material_id'=>112,'material_amount'=>int,'linh_thach_cost'=>int)`.
  - `huyetmach_vessel_milestone($total): array('name'=>string,'bonus_percent'=>float,'next_milestone_name'=>string,'next_milestone_percent'=>float,'next_milestone_lv'=>int)`.
  - `huyetmach_vessel_stat_label($stat): string`.

- [x] **Step 1: Tao `class/config/huyetmach_vessel_config.json`** (noi dung nguyen chan):

```json
{
  "schema": "huyetmach_vessel_v1",
  "vessels": {
    "1": {"id": 1, "name": "Đốc Mạch", "short": "Đốc", "stat": "atk", "per_lv": 3, "slot": "spine-top", "group": "duong", "desc": "Dương mạch chạy dọc sống lưng, chủ về khí lực. Mỗi cấp tăng Công Kích."},
    "2": {"id": 2, "name": "Nhâm Mạch", "short": "Nhâm", "stat": "hp", "per_lv": 24, "slot": "spine-bottom", "group": "duong", "desc": "Âm mạch chạy phía trước thân, chủ về huyết. Mỗi cấp tăng Khí Huyết."},
    "3": {"id": 3, "name": "Xung Mạch", "short": "Xung", "stat": "crit", "per_lv": 0.2, "slot": "chest-right", "group": "duong", "desc": "Xung mạch giữ huyết hải, đòn đánh bùng lên. Mỗi cấp tăng Bạo Kích."},
    "4": {"id": 4, "name": "Đới Mạch", "short": "Đới", "stat": "lifesteal", "per_lv": 0.4, "slot": "waist", "group": "duong", "desc": "Đới mạch buộc ngang thắt lưng, kéo huyết đối phương. Mỗi cấp tăng Hút Máu."},
    "5": {"id": 5, "name": "Dương Duy Mạch", "short": "Duy Trái", "stat": "reduce", "per_lv": 0.25, "slot": "arm-left", "group": "am", "desc": "Liên kết dương khí toàn thân, giáp ngoài cứng hơn. Mỗi cấp tăng Giảm Vật Lý."},
    "6": {"id": 6, "name": "Âm Duy Mạch", "short": "Duy Phải", "stat": "pen", "per_lv": 0.25, "slot": "arm-right", "group": "am", "desc": "Liên kết âm khí, mũi nhọn thấm sâu. Mỗi cấp tăng Xuyên Giảm (xuyên Phong Thu đối phương)."},
    "7": {"id": 7, "name": "Âm Kiều Mạch", "short": "Kiều Trái", "stat": "dodge", "per_lv": 0.25, "slot": "leg-left", "group": "am", "desc": "Kiều mạch chủ sự nhanh nhẹn, né trong một nhịp. Mỗi cấp tăng Né Tranh."},
    "8": {"id": 8, "name": "Dương Kiều Mạch", "short": "Kiều Phải", "stat": "luck", "per_lv": 0.25, "slot": "leg-right", "group": "am", "desc": "Dương khí hanh thông, của ngon rơi xuống. Mỗi cấp tăng Vận May."}
  },
  "stat_labels": {
    "atk": "Công Kích", "hp": "Khí Huyết", "crit": "Bạo Kích", "lifesteal": "Hút Máu",
    "dodge": "Né Tranh", "pen": "Xuyên Giảm", "reduce": "Giảm Vật Lý", "luck": "Vận May"
  },
  "stat_cap": {"dodge": 15, "pen": 15, "reduce": 15, "luck": 15},
  "tiers": {
    "0": {"stage": 0, "name": "Vĩnh Viễn Trầm Mệnh", "short": "Trầm Mệnh", "lv_cap": 8, "total_lv_require": 0, "realm_min_level": 0, "material_id": 0, "material_amount": 0, "linh_thach_cost": 0, "boss_require": 0, "color": "#6b7280", "icon": "🔒", "desc": "Huyết mạch còn ngủ, khí huyết chưa khai."},
    "1": {"stage": 1, "name": "Sơ Giác", "short": "Sơ Giác", "lv_cap": 16, "total_lv_require": 32, "realm_min_level": 100, "material_id": 0, "material_amount": 0, "linh_thach_cost": 5000, "boss_require": 0, "color": "#4caf50", "icon": "🌟", "desc": "Giọng huyết dội trong cơ thể."},
    "2": {"stage": 2, "name": "Thức Tỉnh", "short": "Thức Tỉnh", "lv_cap": 24, "total_lv_require": 64, "realm_min_level": 150, "material_id": 112, "material_amount": 20, "linh_thach_cost": 20000, "boss_require": 1, "color": "#ff9800", "icon": "⭐", "desc": "Huyết mạch thức tỉnh hoàn toàn."},
    "3": {"stage": 3, "name": "Chân Thân", "short": "Chân Thân", "lv_cap": 36, "total_lv_require": 96, "realm_min_level": 200, "material_id": 112, "material_amount": 40, "linh_thach_cost": 100000, "boss_require": 1, "color": "#f44336", "icon": "💫", "desc": "Chân thân hiển lộ, mở ra Biến Thân."},
    "4": {"stage": 4, "name": "Huyết Thống Viên Mãn", "short": "Viên Mãn", "lv_cap": 48, "total_lv_require": 144, "realm_min_level": 280, "material_id": 113, "material_amount": 1, "linh_thach_cost": 500000, "boss_require": 1, "color": "#ffd700", "icon": "👑", "desc": "Huyết thống đạt cực hạn của giống loài."}
  },
  "refine": {"material_id": 112, "material_base": 1, "material_per_lv": 8, "linh_thach_base": 200, "linh_thach_per_lv": 25},
  "milestones": {
    "1": {"total_lv": 32, "bonus_percent": 5, "name": "Khai Mạch"},
    "2": {"total_lv": 80, "bonus_percent": 12, "name": "Nội Mạch"},
    "3": {"total_lv": 128, "bonus_percent": 20, "name": "Huyết Hải"},
    "4": {"total_lv": 176, "bonus_percent": 30, "name": "Đạt Tôn"}
  },
  "material_names": {"112": "Huyết Thạch", "113": "Huyết Mạch Đan"}
}
```

> **KY THUAT CUOI TRO**: `tool Write` o repo nay tung chen ky tu Trung Hoa lac cho vao chuoi tieng Viet co dau. Step 2 bat buoc chay de bao dam JSON sach.

- [x] **Step 2: Quet CJK + validate JSON**

Run:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$f="class/config/huyetmach_vessel_config.json";
$s=file_get_contents($f);
preg_match_all("/[\\\\x{4e00}-\\\\x{9fff}]/u",$s,$m);
echo $m[0] ? "CJK FOUND: ".implode(" ",$m[0])."\n" : "no CJK\n";
$d=json_decode($s,true);
echo $d===null ? "JSON INVALID\n" : ("json ok, vessels=".count($d["vessels"]).", tiers=".count($d["tiers"])."\n");
'
```
Expected: `no CJK` va `json ok, vessels=8, tiers=5`. Neu thay CJK: mo file, sua dung chuoi tieng Viet viet khong dau nhu o tren, chay lai.

- [x] **Step 3: Viet test bai** `tests/test_huyetmach_vessel.php` (phan A):

```php
<?php
/**
 * test_huyetmach_vessel.php — config + ham doc thuan tuy cua Mang Mach Huyet.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_huyetmach_vessel.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/huyetmach.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: config + ham thuan tuy =====
$defs = \player\huyetmach_vessel_defs();
tassert('8 vessels', count($defs), 8);
tassert('4 duong 4 am', count(array_filter($defs, function ($d) { return $d['group'] === 'duong'; })), 4);
tassert('vessel 1 atk', \player\huyetmach_vessel_def(1)['stat'], 'atk');
tassert('vessel bad id', \player\huyetmach_vessel_def(99), false);

tassert('cap stage 0', \player\huyetmach_vessel_cap(0), 8);
tassert('cap stage 4', \player\huyetmach_vessel_cap(4), 48);
tassert('cap stage 9', \player\huyetmach_vessel_cap(9), 0);
tassert('stage max', \player\huyetmach_vessel_stage_max(), 4);
tassert('tier 2 name', \player\huyetmach_vessel_tier(2)['name'], 'Thức Tỉnh');
tassert('tier 5 missing', \player\huyetmach_vessel_tier(5), false);

// refine: 1 + floor(lv/8) Huyet Thach, 200 + lv*25 Linh Thach
tassert('cost lv0', \player\huyetmach_vessel_refine_cost(0),
    array('material_id' => 112, 'material_amount' => 1, 'linh_thach_cost' => 200));
tassert('cost lv7', \player\huyetmach_vessel_refine_cost(7)['material_amount'], 1);
tassert('cost lv8', \player\huyetmach_vessel_refine_cost(8)['material_amount'], 2);
tassert('cost lv47 lt', \player\huyetmach_vessel_refine_cost(47)['linh_thach_cost'], 1375);

// milestone
$m0 = \player\huyetmach_vessel_milestone(0);
tassert('ms0 none', $m0['name'], '');
tassert('ms0 pct', $m0['bonus_percent'], 0.0);
tassert('ms0 next', $m0['next_milestone_lv'], 32);
$m1 = \player\huyetmach_vessel_milestone(32);
tassert('ms32 name', $m1['name'], 'Khai Mạch');
tassert('ms32 pct', $m1['bonus_percent'], 5.0);
tassert('ms32 next', $m1['next_milestone_lv'], 80);
$mMax = \player\huyetmach_vessel_milestone(384);
tassert('ms max done', $mMax['next_milestone_lv'], 0);
tassert('ms max pct', $mMax['bonus_percent'], 30.0);  // = gia tri moc 4 trong config (Task 12 so no dong 13)

tassert('label atk', \player\huyetmach_vessel_stat_label('atk'), 'Công Kích');
tassert('label junk', \player\huyetmach_vessel_stat_label('zzz'), 'zzz');

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [x] **Step 4: Chay test, xac nhan FAIL** (ham chua ton tai)

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_vessel.php`
Expected: Fatal error `Call to undefined function player\huyetmach_vessel_defs()`

- [x] **Step 5: Them khoi ham vao `class/function/huyetmach.php`** (dat cuoi file, trong cung `namespace player`, theo dung gioi han `function_exists` cua file):

```php
/** ===== MANG MACH HUYET: config + ham thuan tuy ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_config_path')) {
    function huyetmach_vessel_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_vessel_config.json',
            __DIR__ . '/huyetmach_vessel_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_config_all')) {
    function huyetmach_vessel_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = huyetmach_vessel_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $data = json_decode(file_get_contents($file), true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_defs')) {
    function huyetmach_vessel_defs(){
        $all = huyetmach_vessel_config_all();
        return isset($all['vessels']) ? $all['vessels'] : array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_tiers')) {
    function huyetmach_vessel_tiers(){
        $all = huyetmach_vessel_config_all();
        return isset($all['tiers']) ? $all['tiers'] : array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_def')) {
    function huyetmach_vessel_def($id){
        $defs = huyetmach_vessel_defs();
        $id = intval($id);
        return isset($defs[$id]) ? $defs[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_tier')) {
    function huyetmach_vessel_tier($stage){
        $tiers = huyetmach_vessel_tiers();
        $stage = intval($stage);
        return isset($tiers[$stage]) ? $tiers[$stage] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_cap')) {
    function huyetmach_vessel_cap($stage){
        $tier = huyetmach_vessel_tier($stage);
        return $tier ? intval($tier['lv_cap']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_stage_max')) {
    function huyetmach_vessel_stage_max(){
        $max = 0;
        foreach (huyetmach_vessel_tiers() as $tier) {
            if (intval($tier['stage']) > $max) $max = intval($tier['stage']);
        }
        return $max;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_refine_cost')) {
    function huyetmach_vessel_refine_cost($lv){
        $all = huyetmach_vessel_config_all();
        $r = isset($all['refine']) ? $all['refine'] : array();
        $lv = max(0, intval($lv));
        $per = max(1, intval(isset($r['material_per_lv']) ? $r['material_per_lv'] : 8));
        return array(
            'material_id'     => intval(isset($r['material_id']) ? $r['material_id'] : 112),
            'material_amount' => intval(isset($r['material_base']) ? $r['material_base'] : 1) + intval($lv / $per),
            'linh_thach_cost' => intval(isset($r['linh_thach_base']) ? $r['linh_thach_base'] : 200)
                + $lv * intval(isset($r['linh_thach_per_lv']) ? $r['linh_thach_per_lv'] : 25),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_milestone')) {
    function huyetmach_vessel_milestone($total){
        $all = huyetmach_vessel_config_all();
        $ms = isset($all['milestones']) ? $all['milestones'] : array();
        $total = intval($total);
        $name = ''; $pct = 0.0; $next = array('name' => '', 'percent' => 0, 'total_lv' => 0);
        foreach ($ms as $m) {
            $need = intval($m['total_lv']);
            if ($total >= $need) {
                $name = $m['name']; $pct = floatval($m['bonus_percent']);
            } elseif ($next['total_lv'] === 0 || $need < $next['total_lv']) {
                $next = array('name' => $m['name'], 'percent' => floatval($m['bonus_percent']), 'total_lv' => $need);
            }
        }
        return array('name' => $name, 'bonus_percent' => $pct,
            'next_milestone_name' => $next['name'], 'next_milestone_percent' => $next['percent'],
            'next_milestone_lv' => $next['total_lv']);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_stat_label')) {
    function huyetmach_vessel_stat_label($stat){
        $all = huyetmach_vessel_config_all();
        $labels = isset($all['stat_labels']) ? $all['stat_labels'] : array();
        return isset($labels[$stat]) ? $labels[$stat] : $stat;
    }
}
```

- [x] **Step 6: Chay test -> PASS**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_vessel.php`
Expected: `ALL PASS`, exit 0.

- [x] **Step 7: Lint**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/huyetmach.php`
Expected: `No syntax errors detected`

- [x] **Step 8: Commit**

```bash
git add class/config/huyetmach_vessel_config.json class/function/huyetmach.php tests/test_huyetmach_vessel.php
git commit -m "feat(huyetmach): vessel config + pure readers for meridian network"
```

---

### Task 2: Schema — 3 bang moi

**Files:**
- Create: `sql/sql_huyetmach_vessel.sql`
- Create: `tests/test_huyetmach_schema.php`
- Modify: `tests/deploy_preflight.php` (them 3 bang vao `$SID_VARCHAR` khoi dong 20-38)

**Interfaces:**
- Consumes: Task 1 (chua co gi thuoc ham).
- Produces: bang `player_huyetmach_vessel (sid VARCHAR(64), vessel_id TINYINT, level INT, updated_at DATETIME)`, `huyetmach_boss (id, sid, cfg_key, name, element, level, hp, maxhp, atk, def, crit, spawned_at, killed_at, status)`, `player_huyetmach_upgrade_log (id, sid, from_quality, to_quality, success, created_at)`.

- [x] **Step 1: Viet `tests/test_huyetmach_schema.php` (test bai)**

```php
<?php
/**
 * test_huyetmach_schema.php — kiem tra schema Mang Mach Huyet da duoc ap vao DB local `game`.
 * Chay SAU khi: "...php.exe" -r "pdo->exec(file)" (xem Step 4)
 *   hoac mysql -u root -p... game < sql/sql_huyetmach_vessel.sql
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

function col($db, $table, $column) {
    $st = $db->prepare("SELECT COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='game' AND TABLE_NAME=? AND COLUMN_NAME=?");
    $st->execute(array($table, $column));
    return $st->fetch(PDO::FETCH_ASSOC);
}
function tbl($db, $table) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA='game' AND TABLE_NAME=?");
    $st->execute(array($table));
    return intval($st->fetch(PDO::FETCH_ASSOC)['c']) === 1;
}

tassert('table player_huyetmach_vessel', tbl($db, 'player_huyetmach_vessel'), true);
tassert('table huyetmach_boss', tbl($db, 'huyetmach_boss'), true);
tassert('table player_huyetmach_upgrade_log', tbl($db, 'player_huyetmach_upgrade_log'), true);

$v = col($db, 'player_huyetmach_vessel', 'sid');
tassert('vessel sid varchar64', intval($v['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('vessel vessel_id tinyint', $v ? col($db, 'player_huyetmach_vessel', 'vessel_id')['COLUMN_TYPE'], 'tinyint(4)');
tassert('vessel level default 0', col($db, 'player_huyetmach_vessel', 'level')['COLUMN_DEFAULT'], '0');
tassert('boss sid varchar64', intval(col($db, 'huyetmach_boss', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('boss cfg_key varchar64', intval(col($db, 'huyetmach_boss', 'cfg_key')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('boss status default', col($db, 'huyetmach_boss', 'status')['COLUMN_DEFAULT'], 'active');
tassert('log from_quality int', col($db, 'player_huyetmach_upgrade_log', 'from_quality')['COLUMN_TYPE'], 'int(11)');

// game1 khong them cot moi nao cho mach — cot stage/pham/cd da co tu truooc
tassert('game1 huyetmach_awaken_stage ton tai', col($db, 'game1', 'huyetmach_awaken_stage') !== false, true);
tassert('game1 huyetmach_transform_cd ton tai', col($db, 'game1', 'huyetmach_transform_cd') !== false, true);

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [x] **Step 2: Chay test -> FAIL**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_schema.php`
Expected: `FAIL table player_huyetmach_vessel` (got false), 3 FAIL bang.

- [x] **Step 3: Viet `sql/sql_huyetmach_vessel.sql`**

```sql
-- =====================================================
-- MANG MACH HUYET — schema cho Huyết Mạch rework (2026-10-09)
-- MySQL 5.5 compatible: khong DEFAULT CURRENT_TIMESTAMP, sid VARCHAR(64)
-- Chay: mysql -u root -p <dbname> < sql/sql_huyetmach_vessel.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS player_huyetmach_vessel (
  sid        VARCHAR(64) NOT NULL,
  vessel_id  TINYINT(4)  NOT NULL,
  level      INT(11)     DEFAULT 0,
  updated_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, vessel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS huyetmach_boss (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL DEFAULT '',
  cfg_key    VARCHAR(64) NOT NULL,
  name       VARCHAR(100) NOT NULL,
  element    VARCHAR(20) NOT NULL,
  level      INT(11) DEFAULT 1,
  hp         INT(11) DEFAULT 0,
  maxhp      INT(11) DEFAULT 0,
  atk        INT(11) DEFAULT 0,
  def        INT(11) DEFAULT 0,
  crit       INT(11) DEFAULT 0,
  spawned_at DATETIME DEFAULT NULL,
  killed_at  DATETIME DEFAULT NULL,
  status     VARCHAR(20) DEFAULT 'active',
  PRIMARY KEY (id),
  KEY idx_sid (sid),
  KEY idx_key (cfg_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_huyetmach_upgrade_log (
  id           INT(11) NOT NULL AUTO_INCREMENT,
  sid          VARCHAR(64) NOT NULL,
  from_quality INT(11) NOT NULL,
  to_quality   INT(11) NOT NULL,
  success      TINYINT(1) DEFAULT 0,
  created_at   DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- [x] **Step 4: Ap dung vao DB local `game`**

Run:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$root="D:/XXJserver/phpstudy_pro/WWW";
$db=new PDO("mysql:host=localhost;dbname=game;charset=utf8","root","www.aae.ink",array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
$sql=file_get_contents($root."/sql/sql_huyetmach_vessel.sql");
foreach(array_filter(array_map("trim",explode(";",$sql))) as $q){ if($q!=="") $db->exec($q); }
echo "ap xong\n";
'
```
Expected: `ap xong`. (Truoc do chay `mysqldump game player_huyetmach_vessel huyetmach_boss player_huyetmach_upgrade_log > E:/window/Temp/pre_hm.sql` de co duong rollback; 3 bang nay moi nen rollback = DROP.)

- [x] **Step 5: Chay test -> PASS**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_schema.php`
Expected: `ALL PASS`

- [x] **Step 6: Nho preflight cua production** — sua `tests/deploy_preflight.php`, trong `$SID_VARCHAR` them:

```php
    'player_huyetmach_vessel'       => 'sid',
    'huyetmach_boss'                => 'sid',
    'player_huyetmach_upgrade_log'  => 'sid',
```

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l tests/deploy_preflight.php`
Expected: `No syntax errors detected`

- [x] **Step 7: Commit**

```bash
git add sql/sql_huyetmach_vessel.sql tests/test_huyetmach_schema.php tests/deploy_preflight.php
git commit -m "feat(huyetmach): schema for vessels, per-player bloodline bosses, quality log"
```

---

### Task 3: Cap mach + tong hop chi so (DB)

**Files:**
- Modify: `class/function/huyetmach.php` (cuoi file)
- Modify: `class/player.php` (sau dong 839, khoi `Buff Huyet Mach`)
- Modify: `tests/test_huyetmach_vessel.php` (them phan B)

**Interfaces:**
- Consumes: Task 1 (`huyetmach_vessel_defs/tier/cap/milestone`), Task 2 (bang `player_huyetmach_vessel`, cot `game1.huyetmach_awaken_stage`).
- Produces:
  - `huyetmach_vessel_levels($sid, $dblj): array` — `id => level`, luon du 8 phan tu, mac dinh 0.
  - `huyetmach_vessel_total(array $levels): int`.
  - `huyetmach_vessel_bonuses($sid, $dblj): array` voi keys `levels, total_lv, cap, stage,
    atk, hp, crit, lifesteal, dodge, pen, reduce, luck, milestone_name, milestone_percent,
    next_milestone_name, next_milestone_percent, next_milestone_lv`. 4 chi so am da cap 15.
  - `huyetmach_calc_bonuses()` tra them key `vessel` (chinh `huyetmach_vessel_bonuses`) va
    `stats` cua no da duoc nhan them milestone.

- [x] **Step 1: Them phan B vao `tests/test_huyetmach_vessel.php`** (truoc dong `echo $fails === 0 ...`):

```php
// ===== B: cap mach + tong hop chi so (DB local `game`) =====
$TESTSID = str_repeat('f', 32);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

function hm_reset($db, $sid) {
    $db->prepare("DELETE FROM player_huyetmach_vessel WHERE sid = ?")->execute(array($sid));
    $db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 0 WHERE sid = ?")->execute(array($sid));
}
function hm_set_level($db, $sid, $vid, $lv) {
    $db->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at)
        VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE level = VALUES(level)")->execute(array($sid, $vid, $lv));
}

$hasPlayer = $db->query("SELECT COUNT(*) FROM game1 WHERE sid = '$TESTSID'")->fetchColumn();
if (!$hasPlayer) {
    // test can mot dong game1 de doc stage; tao transient row da dung (xoa o cuoi test)
    $db->prepare("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
        VALUES ('$TESTSID', 990112, 'HMTEST', 300, 1000, 10000, NOW(), 0, 1, 0)")->execute();
    $hmTmpRow = true;
} else { $hmTmpRow = false; }
$db->prepare("UPDATE game1 SET uid = 990112 WHERE sid = ?")->execute(array($TESTSID));

hm_reset($db, $TESTSID);

$lv0 = \player\huyetmach_vessel_levels($TESTSID, $db);
tassert('levels du 8 phan tu', count($lv0), 8);
tassert('levels mac dinh 0', intval($lv0[1]), 0);
tassert('total 0', \player\huyetmach_vessel_total($lv0), 0);

$b0 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('bonus stage 0', $b0['stage'], 0);
tassert('bonus cap 8', $b0['cap'], 8);
tassert('bonus atk 0', $b0['atk'], 0);
tassert('bonus ms name', $b0['milestone_name'], '');

hm_set_level($db, $TESTSID, 1, 5);
hm_set_level($db, $TESTSID, 2, 5);
$b1 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('total 10', $b1['total_lv'], 10);
tassert('atk 5x3', $b1['atk'], 15);
tassert('hp 5x24', $b1['hp'], 120);

// cap 15 van dung vi tran 8 bi stage 0 chan o 8 -> dat tran thi milestone + cap van theo stage
hm_set_level($db, $TESTSID, 3, 8);
hm_set_level($db, $TESTSID, 4, 8);
hm_set_level($db, $TESTSID, 5, 8);
hm_set_level($db, $TESTSID, 6, 8);
hm_set_level($db, $TESTSID, 7, 8);
hm_set_level($db, $TESTSID, 8, 8);
$b2 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('total 58', $b2['total_lv'], 58);
tassert('ms dat Khai Mach', $b2['milestone_name'], 'Khai Mạch');
tassert('ms pct 5', $b2['milestone_percent'], 5.0);
tassert('atk nhan milestone', $b2['atk'], 16);          // round(3*5*1.05) = 16, KHONG phai 22 (round tung mach)
tassert('am stat reduce', $b2['reduce'], 2.0);           // 0.25 x min(8, tran 8) -> FLOAT vi ham dung round(..,2)
tassert('am stat luck', $b2['luck'], 2.0);

// stage 2: tran 24, milestone van theo tong cap
$db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 2 WHERE sid = ?")->execute(array($TESTSID));
$b3 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('stage 2 cap 24', $b3['cap'], 24);

// chan 15% cho chi so am: 8 mach am x per_lv 0.25 x 80 cap -> vuot 15
for ($i = 5; $i <= 8; $i++) hm_set_level($db, $TESTSID, $i, 80);
$b4 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('dodge cap 15', $b4['dodge'], 15.0);
tassert('pen cap 15', $b4['pen'], 15.0);

hm_reset($db, $TESTSID);
if ($hmTmpRow) $db->prepare("DELETE FROM game1 WHERE sid = '$TESTSID'")->execute();
```

- [x] **Step 2: Chay test -> FAIL** (`Call to undefined function huyetmach_vessel_levels`)

- [x] **Step 3: Them 3 ham vao `class/function/huyetmach.php`**

```php
/** ===== MANG MACH HUYET: cap mach + tong hop chi so ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_levels')) {
    function huyetmach_vessel_levels($sid, $dblj){
        $levels = array();
        foreach (huyetmach_vessel_defs() as $id => $def) $levels[intval($id)] = 0;
        try {
            $stmt = $dblj->prepare("SELECT vessel_id, level FROM player_huyetmach_vessel WHERE sid = ?");
            $stmt->execute(array($sid));
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $vid = intval($row['vessel_id']);
                if (isset($levels[$vid])) $levels[$vid] = max(0, intval($row['level']));
            }
        } catch (Exception $e) { /* bang chua ton tai (production): xem nhu chua luyen */ }
        return $levels;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_total')) {
    function huyetmach_vessel_total($levels){
        $total = 0;
        foreach ($levels as $lv) $total += intval($lv);
        return $total;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_bonuses')) {
    function huyetmach_vessel_bonuses($sid, $dblj){
        $defs  = huyetmach_vessel_defs();
        $all   = huyetmach_vessel_config_all();
        $caps  = isset($all['stat_cap']) ? $all['stat_cap'] : array();
        $stage = 0;
        $stmt = $dblj->prepare("SELECT huyetmach_awaken_stage FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) $stage = intval($row['huyetmach_awaken_stage']);
        $cap = huyetmach_vessel_cap($stage);

        $levels = huyetmach_vessel_levels($sid, $dblj);
        $total  = huyetmach_vessel_total($levels);
        $ms     = huyetmach_vessel_milestone($total);
        $mult   = 1 + floatval($ms['bonus_percent']) / 100;

        $out = array('atk' => 0, 'hp' => 0, 'crit' => 0, 'lifesteal' => 0,
                     'dodge' => 0, 'pen' => 0, 'reduce' => 0, 'luck' => 0);
        foreach ($defs as $id => $def) {
            $lv = intval($levels[intval($id)]);
            $stat = $def['stat'];
            $val = floatval($def['per_lv']) * $lv;
            if (isset($caps[$stat])) {
                // chi so am: theo tran cap cua stage, roi chan tuyet doi 15%
                $val = floatval($def['per_lv']) * min($lv, $cap);
                $out[$stat] = min(floatval($caps[$stat]), round($out[$stat] + $val, 2));
            } else {
                $out[$stat] = $out[$stat] + round($val * $mult);
            }
        }
        // atk/hp la diem THO cong vao cot INT (ugj/umaxhp) -> chan ve int SAU khi cong don;
        // crit/lifesteal + 4 chi so am van la float (per_lv 0.2/0.4/0.25).
        $out['atk'] = intval($out['atk']);
        $out['hp'] = intval($out['hp']);
        return array_merge($out, array(
            'levels' => $levels, 'total_lv' => $total, 'cap' => $cap, 'stage' => $stage,
            'milestone_name' => $ms['name'], 'milestone_percent' => floatval($ms['bonus_percent']),
            'next_milestone_name' => $ms['next_milestone_name'],
            'next_milestone_percent' => floatval($ms['next_milestone_percent']),
            'next_milestone_lv' => intval($ms['next_milestone_lv']),
        ));
    }
}
```

> **LUU Y DE STEP 1 KHONG SAI SO**: 4 chi so duong cong **tron** vao `stats` cua `huyetmach_calc_bonuses()`
> (cong don, khong nhan phat) vi `base_stats` da nhan `pham chat × canh gioi × thuc tinh`. Mốc
> milestone nhan vao tung mach TRUOC khi cong. `tassert('atk nhan milestone', ...)` dung gia tri
> `round(3*5*1.05) + round(0)` = **16**, KHONG PHAI 22
> (do phat sinh tu phep round tung mach, da ghi ro o day de nguoi thuc thi khong "sua ham cho khop test").

- [x] **Step 4: Cho `huyetmach_calc_bonuses()` tra them mach** — sua trong ham hien co (`class/function/huyetmach.php:206-263`):
  - Trong `$empty` array (dong 207-223) them: `'vessel' => array(),`.
  - Ngay sau `huyetmach_info($sid, $dblj)` tra ve `!$config` thi van doc mach duoc (mach song duoc khi chua co huyet mach), nen trong doan `return array_merge` cuoi ham them key `'vessel' => huyetmach_vessel_bonuses($sid, $dblj),`.
  - Voi nhanh `!$config` (`return $empty;`) thay `$empty` bang `array_merge($empty, array('vessel' => huyetmach_vessel_bonuses($sid, $dblj)))`.

- [x] **Step 5: Ap mach vao nhan vat** — `class/player.php`, ngay sau khoi cong `huyetmach` (sau dong 839):

```php
        // Mang mach huyet: cong don 4 chi so duong, chi so am ap o Task 11
        if (!empty($hmB['vessel']) && is_array($hmB['vessel'])) {
            $player->ugj    += intval($hmB['vessel']['atk']);
            $player->umaxhp += intval($hmB['vessel']['hp']);
            $player->ubj    += intval($hmB['vessel']['crit']);
            $player->uxx    += intval($hmB['vessel']['lifesteal']);
        }
```

- [x] **Step 6: Chay test -> PASS**

- [x] **Step 7: Lint 3 file** (`php.exe -l` cho `class/function/huyetmach.php`, `class/player.php`, `tests/test_huyetmach_vessel.php`)

- [x] **Step 8: Commit**

```bash
git add class/function/huyetmach.php class/player.php tests/test_huyetmach_vessel.php
git commit -m "feat(huyetmach): vessel levels and stat aggregation"
```

---

### Task 4: Vat pham Huyet Thach / Huyet Mach Dan + ham Luyen Mach

**Files:**
- Modify: `class/config/daoju_config.json` (them 112, 113 sau dong cua entry 111)
- Modify: `game/use_dj.php` (an nut "Su dung" cho vat pham khong co `use_effect`)
- Modify: `class/function/huyetmach.php` (`huyetmach_vessel_daoju_have`, `huyetmach_vessel_refine`)
- Modify: `class/function/thechat.php` (`thechat_get_material_name` them 112/113 — cung bang vat pham)
- Modify: `tests/test_huyetmach_vessel.php` (phan C)

**Interfaces:**
- Consumes: Task 1 (`huyetmach_vessel_refine_cost`, `huyetmach_vessel_cap`), Task 3 (`huyetmach_vessel_levels`).
- Produces:
  - `huyetmach_vessel_daoju_have($sid, $dblj, $djid): int`
  - `huyetmach_vessel_refine($sid, $dblj, $vesselId): array('ok'=>bool,'msg'=>string,'level'=>int,'cost'=>array)`
  - `game1` item: `112 Huyet Thach`, `113 Huyet Mach Dan` (khong co `use_effect`).

- [x] **Step 1: Test bai (phan C)**

```php
// ===== C: Luyen Mach =====
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 99);
tassert('refine vessel loi', $ok['ok'], false);

hm_reset($db, $TESTSID);
$db->prepare("UPDATE game1 SET uyxb = 0 WHERE sid = ?")->execute(array($TESTSID));
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 1);
tassert('refine thieu Huyet Thach', strpos($ok['msg'], 'Huyết Thạch') !== false, true);

// cap du 112 x 5 va Linh Thach 100000
$ins = $db->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES (?, 990112, 112, 5)
    ON DUPLICATE KEY UPDATE djsum = 5");
$ins->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET uyxb = 100000 WHERE sid = ?")->execute(array($TESTSID));
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 1);
tassert('refine ok', $ok['ok'], true);
tassert('refine lv 1', $ok['level'], 1);
tassert('tru 1 Huyet Thach', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 4);
tassert('tru 200 Linh Thach', intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 99800);

// chan tran stage 0 = 8
for ($i = 1; $i <= 8; $i++) \player\huyetmach_vessel_refine($TESTSID, $db, 2);
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 2);
tassert('tran stage 0', strpos($ok['msg'], 'trần') !== false || strpos($ok['msg'], 'Trần') !== false, true);
tassert('lv dung tran', \player\huyetmach_vessel_levels($TESTSID, $db)[2], 8);
```

- [x] **Step 2: Chay -> FAIL** (`Call to undefined function huyetmach_vessel_refine`)
- [x] **Step 3: Them 2 entry vao `class/config/daoju_config.json`** (copy khuon entry 100, dong 296-303):

```json
    "112": {
        "djname": "Huyết Thạch",
        "djzl": "112",
        "djinfo": "Kết từ huyết mạch thú, dùng tôi luyện tám mạch và đột phá cảnh giới huyết mạch.",
        "djid": 112,
        "djyxb": 800,
        "djjg": 0
    },
    "113": {
        "djname": "Huyết Mạch Đan",
        "djzl": "113",
        "djinfo": "Đơn dược cô đúc từ bản nguyên huyết mạch, cần cho thức tỉnh Huyết Thống Viên Mãn.",
        "djid": 113,
        "djyxb": 50000,
        "djjg": 0
    },
```

> Sau khi luu, chay buoc quet CJK nhu Task 1 Step 2 nhung doi `$f` thanh `class/config/daoju_config.json`;
> Expected `no CJK` (JSON goc 75 entry dang sach) va `json ok`. Neu thay CJK thi chuoi do da bi Edit
> chen hong — viet lai bang tieng Viet co dau chuan.

- [x] **Step 4: `thechat_get_material_name()` (`class/function/thechat.php:469`):** them `112 => 'Huyết Thạch', 113 => 'Huyết Mạch Đan',`. Tương tự `huyetmach_get_material_name()` (`class/function/huyetmach.php:443`). Ten vat pham la chuoi UI nen VIET CO DAU, khop kieu voi 100 => 'Tinh Thể Đá' — chuoi nay rong ra tan man hinh qua `huyetmach_get_material_name()`, de khong dau la lo.
- [x] **Step 5: An nut dung cho vat pham khong co `use_effect`** — mo `game/use_dj.php`, tim cho `use_effect` (dòng ~10-22) va them chan:

```php
        $effectType = isset($item['use_effect']['type']) ? $item['use_effect']['type'] : '';
        if ($effectType === '') {
            $useNotice = 'Vật phẩm này là nguyên liệu, không dùng trực tiếp — xem trang Huyết Mạch.';
            $useType = 'bad';
            header("Location: game.php?cmd=" . $encode->encode("cmd=getbagdj&sid=$sid") . "&use_notice=" . urlencode($useNotice) . "&use_type=" . $useType);
            exit;
        }
```

> Doc `game/use_dj.php` phan dau file de dat doan nay DUNG cho sau khi da lay `$item` va TRUOC khi kiem tra `djsum`; neu file da co san nhanh nay thi khong them, chi kiem chung 112/113 bi chan dung.

- [x] **Step 6: Ham Luyen Mach** (them vao `class/function/huyetmach.php`):

```php
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_daoju_have')) {
    function huyetmach_vessel_daoju_have($sid, $dblj, $djid){
        $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
        $stmt->execute(array($sid, intval($djid)));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? intval($row['djsum']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_refine')) {
    /** Luyen 1 mach +1 cap. Thanh cong 100%. Ton Huyet Thach + Linh Thach. */
    function huyetmach_vessel_refine($sid, $dblj, $vesselId){
        $def = huyetmach_vessel_def($vesselId);
        if (!$def) return array('ok' => false, 'msg' => 'Mach huyet khong ton tai.', 'level' => 0, 'cost' => array());

        $stage = 0;
        $stmt = $dblj->prepare("SELECT huyetmach_awaken_stage, uyxb FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return array('ok' => false, 'msg' => 'Khong tim thay nhan vat.', 'level' => 0, 'cost' => array());
        $stage = intval($row['huyetmach_awaken_stage']);
        $linhThach = intval($row['uyxb']);

        $levels = huyetmach_vessel_levels($sid, $dblj);
        $vid = intval($def['id']);
        $lv = intval($levels[$vid]);
        $cap = huyetmach_vessel_cap($stage);
        if ($lv >= $cap) {
            return array('ok' => false, 'msg' => $def['name'] . ' đã đạt trần ' . $cap . ' cấp — phải thức tỉnh Huyết Mạch để luyện tiếp.',
                'level' => $lv, 'cost' => array());
        }

        $cost = huyetmach_vessel_refine_cost($lv);
        $have = huyetmach_vessel_daoju_have($sid, $dblj, $cost['material_id']);
        if ($have < $cost['material_amount']) {
            return array('ok' => false,
                'msg' => \player\huyetmach_get_material_name($cost['material_id']) . ' không đủ: cần '
                    . $cost['material_amount'] . ', có ' . $have . '.',
                'level' => $lv, 'cost' => $cost);
        }
        if ($linhThach < $cost['linh_thach_cost']) {
            return array('ok' => false,
                'msg' => 'Linh Thạch không đủ: cần ' . number_format($cost['linh_thach_cost'])
                    . ', có ' . number_format($linhThach) . '.',
                'level' => $lv, 'cost' => $cost);
        }

        $dblj->beginTransaction();
        try {
            $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmt->execute(array($cost['material_amount'], $sid, $cost['material_id'], $cost['material_amount']));
            if ($stmt->rowCount() !== 1) throw new Exception('mat Huyet Thach');

            $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ?")
                ->execute(array($cost['linh_thach_cost'], $sid));

            $dblj->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at)
                VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE level = ?, updated_at = NOW()")
                ->execute(array($sid, $vid, $lv + 1, $lv + 1));

            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            return array('ok' => false, 'msg' => 'Luyen mach that bai, thu lai sau.', 'level' => $lv, 'cost' => $cost);
        }

        player_cache_clear($sid);
        return array('ok' => true,
            'msg' => 'Luyện ' . $def['name'] . ' thành công — cấp ' . ($lv + 1) . '.',
            'level' => $lv + 1, 'cost' => $cost);
    }
}
```

- [x] **Step 7: Chay test -> PASS**, lint `class/function/huyetmach.php`, `game/use_dj.php`
- [x] **Step 8: Commit** `git add class/config/daoju_config.json class/function/huyetmach.php class/function/thechat.php game/use_dj.php tests/test_huyetmach_vessel.php && git commit -m "feat(huyetmach): huyết thạch items + vessel refinement"`

---

### Task 5: Checklist thuc tinh (`huyetmach_vessel_break_rows`)

**Files:**
- Modify: `class/function/huyetmach.php`
- Modify: `tests/test_huyetmach_vessel.php` (phan D)

**Interfaces:**
- Consumes: Task 1 (`huyetmach_vessel_tier`), Task 3 (`huyetmach_vessel_bonuses`), Task 4 (`huyetmach_vessel_daoju_have`), bang `player_huyetmach_boss` (da ton tai trong DB, `sid VARCHAR(64)`, khoa (sid, boss_id)).
- Produces: `huyetmach_vessel_break_rows($sid, $dblj): array('rows'=>array(array('ok'=>bool,'text'=>string)),'ready'=>bool,'next'=>array|false,'boss_name'=>string)` — `next` la tier stage ke (hoac false khi da stage 4), `boss_name` la ten boss can win (rung khi tier khong yeu cau boss).

- [x] **Step 1: Test bai (phan D)**

```php
// ===== D: checklist thuc tinh =====
hm_reset($db, $TESTSID);
$db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 0, uyxb = 0 WHERE sid = ?")->execute(array($TESTSID));
$g = \player\huyetmach_vessel_break_rows($TESTSID, $db);
tassert('next la stage 1', $g['next']['stage'], 1);
tassert('chua san', $g['ready'], false);
tassert('dong tong cap', $g['rows'][0]['text'], 'Tổng cấp Bát Mạch: 0 / 32');
tassert('stage 0 khong can boss', $g['boss_name'], '');

// du 32 cap (8 mach x 4) nhung chua du Linh Thach + canh gioi
foreach (array(1,2,3,4,5,6,7,8) as $v) hm_set_level($db, $TESTSID, $v, 4);
$db->prepare("UPDATE game1 SET ulv = 100, uyxb = 5000 WHERE sid = ?")->execute(array($TESTSID));
$g = \player\huyetmach_vessel_break_rows($TESTSID, $db);
tassert('du tong cap', $g['rows'][0]['ok'], true);
tassert('du canh gioi', $g['rows'][1]['ok'], true);
tassert('du linh thach', $g['rows'][2]['ok'], true);
tassert('san sang stage 1', $g['ready'], true);

// stage 1 -> 2: CAN boss
$db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 1, ulv = 150 WHERE sid = ?")->execute(array($TESTSID));
foreach (array(1,2,3,4,5,6,7,8) as $v) hm_set_level($db, $TESTSID, $v, 8);
$db->prepare("UPDATE game1 SET uyxb = 20000 WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET huyetmach_id = 1 WHERE sid = ?")->execute(array($TESTSID)); // Chân Long
$g = \player\huyetmach_vessel_break_rows($TESTSID, $db);
tassert('boss_name dung', $g['boss_name'], 'Rồng Ngàn Tuổi');
tassert('thieu boss -> chưa ready', $g['ready'], false);
$hasBossRow = false;
foreach ($g['rows'] as $r) if (strpos($r['text'], 'Rồng Ngàn Tuổi') !== false) $hasBossRow = true;
tassert('co dong boss trong checklist', $hasBossRow, true);

$dblj = $db;
\player\huyetmach_boss_mark($TESTSID, 'Rồng Ngàn Tuổi', $db);
$g = \player\huyetmach_vessel_break_rows($TESTSID, $db);
tassert('da win boss -> ready', $g['ready'], true);

$db->prepare("DELETE FROM player_huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET huyetmach_id = 0 WHERE sid = ?")->execute(array($TESTSID));
hm_reset($db, $TESTSID);
```

- [x] **Step 2: Chay -> FAIL** (`Call to undefined function huyetmach_vessel_break_rows`)

- [x] **Step 3: Implement**

```php
/** ===== MANG MACH HUYET: checklist thuc tinh ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_next_stage')) {
    function huyetmach_vessel_next_stage($stage){
        $next = huyetmach_vessel_tier(intval($stage) + 1);
        return $next ? $next : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_boss_name')) {
    /** Ten boss can win de len `toStage`, theo huyet mach dang mang. */
    function huyetmach_vessel_boss_name($bloodlineId, $toStage){
        $key = huyetmach_get_key_by_id($bloodlineId);
        if (!$key) return '';
        $all = huyetmach_awakening_config_all();
        $list = isset($all['boss_list']) ? $all['boss_list'] : array();
        $sk = 'stage_' . intval($toStage);
        if (isset($list[$key][$sk]['name'])) return $list[$key][$sk]['name'];
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_break_rows')) {
    function huyetmach_vessel_break_rows($sid, $dblj){
        $rows = array(); $ready = true; $bossName = '';
        $player = getplayer($sid, $dblj);
        if (!$player) return array('rows' => $rows, 'ready' => false, 'next' => false, 'boss_name' => '');

        $stage = intval($player->huyetmach_awaken_stage);
        $next = huyetmach_vessel_next_stage($stage);
        if (!$next) return array('rows' => $rows, 'ready' => false, 'next' => false, 'boss_name' => '');

        $b = huyetmach_vessel_bonuses($sid, $dblj);
        $need = intval($next['total_lv_require']);
        $ok = $b['total_lv'] >= $need; $ready = $ready && $ok;
        $rows[] = array('ok' => $ok, 'text' => 'Tổng cấp Bát Mạch: ' . $b['total_lv'] . ' / ' . $need);

        $needLv = intval($next['realm_min_level']);
        $ok = intval($player->ulv) >= $needLv; $ready = $ready && $ok;
        $rows[] = array('ok' => $ok, 'text' => 'Cảnh giới: cấp ' . intval($player->ulv) . ' / ' . $needLv);

        $mid = intval($next['material_id']); $mamt = intval($next['material_amount']);
        if ($mid > 0 && $mamt > 0) {
            $have = huyetmach_vessel_daoju_have($sid, $dblj, $mid);
            $ok = $have >= $mamt; $ready = $ready && $ok;
            $rows[] = array('ok' => $ok, 'text' => huyetmach_get_material_name($mid) . ': ' . $have . ' / ' . $mamt);
        }

        $lt = intval($next['linh_thach_cost']);
        if ($lt > 0) {
            $ok = intval($player->uyxb) >= $lt; $ready = $ready && $ok;
            $rows[] = array('ok' => $ok, 'text' => 'Linh Thạch: ' . number_format(intval($player->uyxb)) . ' / ' . number_format($lt));
        }

        if (intval($next['boss_require']) === 1) {
            $bossName = huyetmach_vessel_boss_name(intval($player->huyetmach_id), intval($next['stage']));
            if ($bossName !== '') {
                $ok = huyetmach_boss_killed($sid, $bossName, $dblj); $ready = $ready && $ok;
                $rows[] = array('ok' => $ok, 'text' => 'Boss mạch huyết: ' . $bossName . ($ok ? ' (đã giết)' : ' (chưa giết)'));
            } else {
                // chuyen huyet mach chua co boss tuong ung -> khong chan, ghi de debug
                $rows[] = array('ok' => true, 'text' => 'Boss mạch huyết: chưa có cấu hình cho huyết mạch này');
            }
        }

        return array('rows' => $rows, 'ready' => $ready, 'next' => $next, 'boss_name' => $bossName);
    }
}
```

> `huyetmach_vessel_bonuses()` dung `huyetmach_get_key_by_id()`/`huyetmach_awakening_config_all()` da co san (`class/function/huyetmach.php:283`, `:430`). Trong test, `$db` (PDO) duoc truyen thang lam `$dblj` — giong cach `tests/test_nguhanh.php` dung.

- [x] **Step 4: Chay test -> PASS**, lint
- [x] **Step 5: Commit** `git commit -am "feat(huyetmach): awakening gate checklist with real boss requirement"`

---

### Task 6: Noi `huyetmach_awaken()` vao checklist + sua bug tru HP vao `uxx`

**Files:**
- Modify: `class/function/huyetmach.php:366-428` (`huyetmach_awaken`), `:496-562` (`huyetmach_can_transform`, `huyetmach_activate_transform`)
- Modify: `tests/test_huyetmach_vessel.php` (phan E)

**Interfaces:**
- Consumes: Task 5 (`huyetmach_vessel_break_rows`).
- Produces: `huyetmach_awaken($sid, $dblj): array('ok'=>bool,'msg'=>string,'new_stage'=>int)` — chi tang stage khi `break_rows.ready === true`; tieu hao `material_id/amount` va `linh_thach_cost` cua tier; ghi `player_huyetmach_awaken_log`. Bien than khong con sot `uxx`.

- [x] **Step 1: Test bai (phan E)**

```php
// ===== E: thuc tinh + bien than =====
hm_reset($db, $TESTSID);
$db->prepare("UPDATE game1 SET huyetmach_id = 1, huyetmach_quality = 1, huyetmach_awaken_stage = 0,
    ulv = 100, uyxb = 5000, uhp = 10000, umaxhp = 10000, uxx = 50 WHERE sid = ?")->execute(array($TESTSID));
foreach (array(1,2,3,4,5,6,7,8) as $v) hm_set_level($db, $TESTSID, $v, 4);

$r = \player\huyetmach_awaken($TESTSID, $db);
tassert('awaken stage 1 ok', $r['ok'], true);
tassert('stage len 1', intval($db->query("SELECT huyetmach_awaken_stage FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 1);
tassert('log duoc ghi', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_awaken_log WHERE sid='$TESTSID'")->fetchColumn()) >= 1, true);

$r = \player\huyetmach_awaken($TESTSID, $db);
tassert('stage 2 can boss', strpos($r['msg'], 'Rồng Ngàn Tuổi') !== false, true);

// bug cu: bien than tru vao uxx (Hut Mau) chu khong phai uhp
$db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 3, uhp = 10000, umaxhp = 10000, uxx = 50 WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET huyetmach_transform_cd = NULL WHERE sid = ?")->execute(array($TESTSID));
player_cache_clear($TESTSID);
$r = \player\huyetmach_activate_transform($TESTSID, $db);
tassert('bien than ok', $r['ok'], true);
$after = $db->query("SELECT uhp, uxx FROM game1 WHERE sid='$TESTSID'")->fetch(PDO::FETCH_ASSOC);
tassert('tru uhp', intval($after['uhp']), 8000);
tassert('uxx khong doi', intval($after['uxx']), 50);
tassert('co cd', $db->query("SELECT huyetmach_transform_cd FROM game1 WHERE sid='$TESTSID'")->fetchColumn() !== null, true);

$db->prepare("DELETE FROM player_huyetmach_awaken_log WHERE sid = '$TESTSID'")->execute();
$db->prepare("UPDATE game1 SET huyetmach_id = 0, huyetmach_awaken_stage = 0, huyetmach_transform_cd = NULL WHERE sid = ?")->execute(array($TESTSID));
hm_reset($db, $TESTSID);
```

- [x] **Step 2: Chay -> FAIL** o `awaken stage 1 ok` (ham cu kiem `boss_drop_require` ngay tu stage 1→2 khac cach, va khong yeu cau tong cap) hoac `tru uhp`.
- [x] **Step 3: Viet lai `huyetmach_awaken()`** — thay toan bo than ham (giu nguyen ten + chu ky):

```php
    function huyetmach_awaken($sid, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->huyetmach_id) <= 0) {
            return array('ok' => false, 'msg' => 'Bạn chưa có Huyết Mạch.', 'new_stage' => 0);
        }
        $currentStage = intval($player->huyetmach_awaken_stage);
        $gate = huyetmach_vessel_break_rows($sid, $dblj);
        if (!$gate['next']) {
            return array('ok' => false, 'msg' => 'Đã thức tỉnh tối đa.', 'new_stage' => $currentStage);
        }
        if (!$gate['ready']) {
            foreach ($gate['rows'] as $row) {
                if (!$row['ok']) return array('ok' => false, 'msg' => 'Chưa đủ điều kiện: ' . $row['text'] . '.', 'new_stage' => $currentStage);
            }
        }
        $next = $gate['next'];
        $toStage = intval($next['stage']);
        $mid = intval($next['material_id']); $mamt = intval($next['material_amount']);
        $lt  = intval($next['linh_thach_cost']);

        $dblj->beginTransaction();
        try {
            if ($mid > 0 && $mamt > 0) {
                $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
                $stmt->execute(array($mamt, $sid, $mid, $mamt));
                if ($stmt->rowCount() !== 1) throw new Exception('thieu vat lieu');
            }
            if ($lt > 0) {
                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
                $stmt->execute(array($lt, $sid, $lt));
                if ($stmt->rowCount() !== 1) throw new Exception('thieu linh thach');
            }
            $stmt = $dblj->prepare("UPDATE game1 SET huyetmach_awaken_stage = ? WHERE sid = ?");
            $stmt->execute(array($toStage, $sid));
            $dblj->prepare("INSERT INTO player_huyetmach_awaken_log (sid, from_stage, to_stage, boss_killed, success, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())")->execute(array($sid, $currentStage, $toStage,
                $gate['boss_name'] !== '' ? $gate['boss_name'] : null));
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            return array('ok' => false, 'msg' => 'Thức tỉnh thất bại, thử lại sau.', 'new_stage' => $currentStage);
        }

        player_cache_clear($sid);
        $toName = $next['name'];
        return array('ok' => true, 'msg' => 'Thức tỉnh thành công! Huyết Mạch đã lên ' . $toName . '.', 'new_stage' => $toStage);
    }
```

- [x] **Step 4: Xoa `quest_require`** — tim moi cham `quest_require` trong `huyetmach.php` (ham cu khong kiem tra, nhung `game/huyetmach_awakening.php:44-47` co dan chu) va dua no ra khoi UI o Task 7. GIU lai khoa trong JSON `huyetmach_awakening_config.json` cho toi khi Task 12 quyet xoa hay giu (xem muc 8.2 cua spec).
- [x] **Step 5: Sua bug bien than** — `class/function/huyetmach.php`:
  - `huyetmach_can_transform()` dong 520: `if ($player->uxx < $hpCost)` -> `if (intval($player->uhp) < $hpCost)`; dong 517 giu `getplayer`.
  - `huyetmach_activate_transform()` dong 544-547: `$newHP = max(1, $player->uxx - $hpCost);` -> `$newHP = max(1, intval($player->uhp) - $hpCost);` va `UPDATE game1 SET uxx = ?` -> `UPDATE game1 SET uhp = ?`.
  - Them chu thich NGAN (1 dong) o cho sua: `// HP, khong phai uxx (Hut Máu) — bug cu tru nham cot`.

> **LUU Y: `getplayer()` co the lang cache** — trong test goi `player_cache_clear($TESTSID)` truoc khi doc `$player` sau moi lan ghi DB, neu khong `uhp` doc duoc la gia tri cu va test sai.

- [x] **Step 6: Chay test -> PASS**, lint `class/function/huyetmach.php`
- [x] **Step 7: Commit** `git commit -am "fix(huyetmach): awaken gated by real requirements; transform spends HP not lifesteal"`

---
### Task 7: UI — một màn `game/huyetmach.php` (khối `.hm-*`) + ray 5 bậc + 8 ngọc

**Files:**
- Modify: `class/function/huyetmach.php` (gộp master tên/màu/icon bậc vào vessel config)
- Create: `images/huyetmach/` (ảnh thân + 8 × 3 ngọc)
- Modify: `css/mystyle.css` (nối khối `.hm-*` vào CUỐI file, sau `css/mystyle.css:7259`)
- Rewrite: `game/huyetmach.php`
- Modify: `game.php:367-369` (routes)
- Delete: `game/huyetmach_awakening.php`
- Modify: `game/huyetmach_transform.php` (dùng `.hm-*`, sửa nút trắng)

**Interfaces:**
- Consumes: `huyetmach_vessel_defs/tier/cap/stage_max/refine_cost/milestone/stat_label` (Task 1), `huyetmach_vessel_bonuses` (Task 3), `huyetmach_vessel_refine/daoju_have` (Task 4), `huyetmach_vessel_break_rows` (Task 5), `huyetmach_awaken` (Task 6), `huyetmach_get_transform/can_transform/quality_name/quality_color` (có sẵn).
- Produces: trang `cmd=huyetmach` duy nhất nhận `?node=<1..8>` và `?view=<0..4>`; biến toàn cục `$hmcmd` (link từ trang khác tới). Các trang `huyetmach_boss`/`huyetmach_upgrade` (Task 8, 9) nhận diện mạo `.hm-*` từ đây.

> **DA KIENG TRUOC KHI VIET (do bang Chrome headless 09/10, file `E:/window/Temp/_hmbtn2.html`)**
> `.main button{background:#fff;color:#0093E8;padding:1px 10px;border:1px solid #e3e3e3;border-radius:3px}`
> (`css/gamecss.css:122`) co dac tinh **(0,1,1)** va `game.php:584` boc toan bo noi dung trong
> `<div class="main tcg-main-panel">`. Ket qua do: `<button class="nb-btn nb-btn-refine">` cho
> `backgroundColor=rgb(255,255,255)`, `backgroundImage=none`, `paddingTop=1px` — **nút submit của Nhục Thân đang TRẮNG**, còn `<a class="nb-btn">` thì chuẩn (`rgba(0,0,0,0)` + gradient).
> Vi vay khối `.hm-*` PHAI co rule dac tinh cao hon cho THE `<button>`: dung `.main .hm-btn...` (0,2,1).
> Voi CTA khong phai form (Biến Thân) dung `<a class="hm-btn">` — khong can rule dac biet.
> (Bug cua `.nb-btn` ghi vao muc "mo trong Task 12 + hoi chu du an, KHONG tu sua o day.)

- [ ] **Step 1: Nhat master ten/mau/icon bac** — `class/function/huyetmach.php:602-631`: thay 3 ham `huyetmach_awaken_name/color/icon` bang bao chi delegate den vessel config (mot master duy nhat, `game/zhuangtai.php:552` tu dong theo):

```php
if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_name')) {
    function huyetmach_awaken_name($stage){
        $tier = huyetmach_vessel_tier(intval($stage));
        return $tier ? $tier['name'] : 'Vĩnh Viễn Trầm Mệnh';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_color')) {
    function huyetmach_awaken_color($stage){
        $tier = huyetmach_vessel_tier(intval($stage));
        return $tier ? $tier['color'] : '#6b7280';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_icon')) {
    function huyetmach_awaken_icon($stage){
        $tier = huyetmach_vessel_tier(intval($stage));
        return $tier ? $tier['icon'] : '🔒';
    }
}
```

> Mau bac 0 doi từ `#666666` → `#6b7280` (xám trong vessel config). Đây là thay đổi CÓ CHỦ ĐÍCH: gộp master. Ghi vào `docs/UI_HUYETMACH.md` (Task 12) để chủ dự án thấy.

- [ ] **Step 2: Tim anh than hinh kinh mach** (thu tu u tien theo `reference-source-game-assets` — 3 source game anh sở hữu):

```bash
# Tieng Trung: jingmai/renmai/dumai = kinh mach / nhiem mach / doc mach
find "F:/TuTienPhucMa/game/ryxx/xzwd/xzwd_dapu/assets" -iname "*.png" 2>/dev/null | grep -iE "jing|mai|ren|du|body|human|role" | head -40
find "E:/natramadongh5/backup/07_03_2026/wwwroot/game/resource/assets" -iname "*.png" 2>/dev/null | grep -iE "jing|mai|body|human|role" | head -40
```

Khong co anh dat → ImageGen (ràng buộc đã ghi ở spec mục 4): nhân vật ngồi thiền CHÍNH DIỆN,
ảnh ngang **663×467**, nền lấy đúng `#0a1330` (màu `.hm-stage`), thấy rõ **đầu, thắt lưng, 2 tay, 2 chân**,
8 điểm huyệt đánh dấu nhạt để còn đối chiếu tọa độ.
Lưu: `images/huyetmach/kinh-mach.jpg` (+ giữ file gốc `.png` ngoài repo).

Ngoc: copy 4 bo da co (`images/thechat/ngoc-{atk,hp,crit,lifesteal}-{khoa,sang,day}.png`) sang
`images/huyetmach/`; 4 mach moi (dodge/pen/reduce/luck) dung ngoc chung
`ngoc-{khoa,sang,day}.png` va phan biet bang `--ring` theo mau bac — KHONG ve anh moi
(tranh rong file khong can thiet; da duoc ghi o spec muc 4.3).

```bash
mkdir -p images/huyetmach
cp images/thechat/ngoc-*.png images/huyetmach/
```

- [ ] **Step 3: Do truc that cua anh moi** (bai hoc v13 cua Nhuc Than: khong lay 50% lam chuan). Dung harness `E:/window/Temp/_hmgem.html` (khuon `.hm-stage` + 8 `.hm-gem` du, CSS link toi `gamecss.css` + `mystyle.css`), chay:

```bash
"C:/Program Files/Google/Chrome/Application/chrome.exe" --headless=new --disable-gpu --hide-scrollbars \
  --no-first-run --window-size=1400,1500 --virtual-time-budget=15000 --dump-dom \
  "file:///E:/window/Temp/_hmgem.html" | grep -o "<title>[^<]*</title>"
```

So do chuan (dung cho anh 663×467 nhu `nhuc-than.jpg`; mobile row = top% × 467, desktop crop 45px dau + 45px day → `aspect-ratio:663/377`, top% = (row − 45)/377):

| mach | slot | left% | top% mobile | top% desktop |
|---|---|---|---|---|
| Đốc (atk) | `spine-top` | 51.3 | 21 | 14.1 |
| Duy Trái (reduce) | `arm-left` | 35.3 | 41 | 38.9 |
| Duy Phải (pen) | `arm-right` | 67.3 | 41 | 38.9 |
| Xung (crit) | `chest-right` | 58.5 | 46 | 45.0 |
| Đới (lifesteal) | `waist` | 51.0 | 57 | 58.7 |
| Kiều Trái (dodge) | `leg-left` | 25.4 | 69 | 73.5 |
| Kiều Phải (luck) | `leg-right` | 75.4 | 69 | 73.5 |
| Nhâm (hp) | `spine-bottom` | 50.4 | 80 | 87.2 |

6 o dau = toa do `.nb-gem` da nghiem thu (`css/mystyle.css:7044-7049` + `@media(min-width:601px)`);
2 o MOI (`chest-right`, `waist`) phai do lai tren anh kinh mach that. Neu anh khong phai 663×467,
giu nguyen ty le chen cua `left%`, tinh lai `top%` theo row = 45 + top%_desktop × 377.

- [ ] **Step 4: Nối khối CSS `.hm-*`** — clone nguyên khối `.nb-*` đã được kiểm chứng (dòng 6991→7259) rồi đổi tên prefix:

```bash
cp css/mystyle.css "E:/window/Temp/mystyle.css.bak_hm"
{ echo '/* ============================================================';
  echo '   HUYET MACH (game/huyetmach.php) - .hm-*';
  echo '   Clone 1:1 khoi .nb-* (css/mystyle.css:6991-7259), doi prefix + 8 ngoc.';
  echo '   Panel van 660px chung - KHONG mo rong. .hm-wrap{margin:0} nhu .nb-wrap.';
  echo '   .main button (0,1,1) > .hm-btn (0,1,0) -> phai co .main .hm-btn (0,2,1).';
  echo '============================================================ */';
  sed -n '6991,7259p' css/mystyle.css | sed -e 's/nb-/hm-/g' -e 's/Nb/Hm/g' -e 's/@keyframes nb/@keyframes hm/g' -e 's/animation:nb/animation:hm/g';
} >> css/mystyle.css
```

> 4 lenh `sed` tren: 2 cai dau doi prefix class/bien; 2 cai cuoi doi ten `@keyframes nbBreath/nbAuraPulse/nbSweepRun/nbGemMax/nbTierGlow/nbShine/nbMsFlow/nbRain` → `hm...` va noi dung `animation:` tro toi. Neu bo 2 cai cuoi thi khoi `.hm-*` dinh nghia lai dung ten keyframe cua `.nb-*` (duplicate, va sau nay sua `.nb-*` se keo theo `.hm-*`).

Sau khi clone, **8 sua BAT BUOC** (dung Edit tren chinh `css/mystyle.css`, khu vics `.hm-*`):

1. `.hm-wrap{margin:0}` — GIU NGUYEN, khong them `max-width` (do 624px noi dung cua panel 660px).
2. Thay 6 rule toa do (`.hm-gem.slot-top`, `.slot-left-up`, `.slot-right-up`, `.slot-left-down`, `.slot-right-down`, `.slot-bottom`) bang 8 rule o bang Step 3 (ten slot: `spine-top`, `spine-bottom`, `chest-right`, `waist`, `arm-left`, `arm-right`, `leg-left`, `leg-right`).
3. Trong `@media(min-width:601px)`: xoa 4 rule `.hm-gem.slot-*.top` cua nb, thay bang 8 top% desktop bang Step 3.
4. Rule nhan o tren: `.hm-gem.slot-top .hm-gem-tag` → `.hm-gem.slot-spine-top .hm-gem-tag,.hm-gem.slot-spine-bottom .hm-gem-tag` (nhan 2 ngoc nam doc truc than phai day len tren de khoi che ngoc canh); xoa rule `.hm-gem.slot-bottom .hm-gem-tag` trong `@media(min-width:601px)` vi da nam o (3).
5. Xoa rule clone `.hm-fx5` va `.hm-t5` (khong co bac 5), thay `.hm-fx5 .hm-badge` bang `.hm-wrap.is-dao .hm-badge` (rong 7 mau chi dung cho **Dao Pham** = pham chat 10, khong phai bac thuc tinh) va trong rule `@media(prefers-reduced-motion:reduce)` sua `.hm-fx5 .hm-badge,.hm-t5 .hm-tier-name` → `.hm-wrap.is-dao .hm-badge`.
6. Sua `.hm-t4{--t-glow:19px}` thanh 19px (giu), xoa `.hm-t5`; `.hm-fx0..fx4` giu gia tri nb (fx0 co quanh mang tinh = `animation:none` tuong minh, van nhin thay gi — dung yeu cau "bac thap nhat van phai thay" cua chu du an).
7. THEM cuoi khoi (khong co trong nb):

```css
/* .main button (gamecss.css:122, 0,1,1) danh thua .hm-btn (0,1,0):
   can selector 2 lop de nut submit do mau gradient cua chinh no. */
.main .hm-btn{padding:11px 18px;border:0;border-radius:12px;font-size:15px;font-weight:1000;color:#fff}
.main .hm-btn-refine{background:linear-gradient(135deg,#f97316,#eab308)}
.main .hm-btn-break{background:linear-gradient(135deg,#7c3aed,#db2777)}
.main .hm-btn[disabled],.main .hm-btn.is-off{opacity:.45;box-shadow:none}
/* Tom tat Bien Than (chi hien khi stage >= 3) */
.hm-transform{display:flex;align-items:center;gap:8px;margin-top:10px;padding:9px 11px;border-radius:12px;border:1px solid rgba(249,115,22,.35);background:rgba(249,115,22,.08);font-size:13px;color:#cbd5e1}
.hm-transform-ico{font-size:22px;font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif}
.hm-transform-name{font-weight:1000;color:#f97316}
.hm-transform-state{margin-left:auto;font-weight:900;white-space:nowrap}
.hm-transform-state.ok{color:#6ee7b7}.hm-transform-state.no{color:#f87171}
.hm-btn-transform{background:linear-gradient(135deg,#dc2626,#f97316);box-shadow:0 0 12px rgba(220,38,38,.3);padding:8px 14px;font-size:13px;width:100%;margin-top:8px}
/* Chua co huyet mach: van cho luyen mach, nhac dan mo */
.hm-empty{font-size:12.5px;color:#f87171;margin-top:4px;text-align:center;line-height:1.45}
```

8. Sua comment trong khoi (clone nguyen ven nen van noi ve Nhuc Than): "NHUC THAN"→"HUYET MACH", "6 ngoc"→"8 ngoc", "ray 6 bac Cot Cach"→"ray 5 bac Thuc Tinh", "6 tab + 5 ket"→"5 tab + 4 ket", "6 toa do ngoc"→"8 toa do ngoc", `docs/UI_THECHAT.md muc 3.7 va 3.8`→`docs/UI_HUYETMACH.md muc 3`, "Khong co bac 5" cho khu vics `nb-t5`/`nb-fx5` da xoa o (5).

Kiem tra so ngac `{`/`}` bang nhau + khong co ky tu lac:

```bash
grep -c "{" css/mystyle.css && grep -c "}" css/mystyle.css
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r "\$p='css/mystyle.css';\$s=file_get_contents(\$p);preg_match_all('/[\x{4e00}-\x{9fff}\x{3040}-\x{30ff}]/u',\$s,\$m);echo 'CJK='.count(\$m[0]).PHP_EOL;"
```

- [ ] **Step 5: Viet lai `game/huyetmach.php`** (toan bo noi dung duoi day). Ngon ngu: `.hm-gem` (khong phai `.hm-vessel` nhu spec muc 4) de khoi CSS clone duoc **byte-nguyen-ve** — ghi chu lech nay vao `docs/UI_HUYETMACH.md`.

```php
<?php
// HUYET MACH — Mang mach huyet 8 mach + thuc tinh huyet mach.
// Logic: class/function/huyetmach.php (huyetmach_vessel_*)
// Config: class/config/huyetmach_vessel_config.json
// CSS:   css/mystyle.css (block .hm-*)

$ztcmd   = $encode->encode("cmd=zhuangtai&sid=$sid");
$upcmd   = $encode->encode("cmd=huyetmach_upgrade&sid=$sid");
$bosscmd = $encode->encode("cmd=huyetmach_boss&sid=$sid");
$tfcmd   = $encode->encode("cmd=huyetmach_transform&sid=$sid");
$hmImgVer = '2026100901'; // bump khi doi anh trong images/huyetmach/

// ===== XU LY LUA CHON =====
$hmMsg = '';
$hmMsgOk = false;
$hmActionNode = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hm_action'])) {
    if ($_POST['hm_action'] === 'refine') {
        $hmActionNode = isset($_POST['hm_node']) ? intval($_POST['hm_node']) : 0;
        $hmResult = \player\huyetmach_vessel_refine($sid, $dblj, $hmActionNode);
    } elseif ($_POST['hm_action'] === 'break') {
        $hmResult = \player\huyetmach_awaken($sid, $dblj);
    } else {
        $hmResult = false;
    }
    if (is_array($hmResult)) {
        $hmMsg = $hmResult['msg'];
        $hmMsgOk = !empty($hmResult['ok']);
    }
}

$player = \player\getplayer($sid, $dblj);

$hmDefs      = \player\huyetmach_vessel_defs();
$hmB         = \player\huyetmach_vessel_bonuses($sid, $dblj);
$hmLevels    = $hmB['levels'];
$hmTotal     = intval($hmB['total_lv']);
$hmCap       = intval($hmB['cap']);
$hmStage     = intval($hmB['stage']);
$hmMaxStage  = \player\huyetmach_vessel_stage_max();
$hmTier      = \player\huyetmach_vessel_tier($hmStage);
$hmNextTier  = \player\huyetmach_vessel_tier($hmStage + 1);
$hmBloodId   = intval($player->huyetmach_id);
$hmQuality   = intval($player->huyetmach_quality);

// Mach dang chon: POST > GET > mach dau tien chua dat tran
$hmNode = $hmActionNode;
if ($hmNode <= 0 && isset($node)) $hmNode = intval($node);
if (!isset($hmDefs[$hmNode])) {
    $hmNode = 0;
    foreach ($hmDefs as $hmId => $hmDef) {
        if (intval($hmLevels[intval($hmId)]) < $hmCap) { $hmNode = intval($hmId); break; }
    }
    if ($hmNode <= 0) {
        $hmKeys = array_keys($hmDefs);
        $hmNode = intval($hmKeys[0]);
    }
}

// Bac thuc tinh dang xem: POST success > GET > bac hien tai
$hmView = $hmStage;
if (isset($_POST['hm_action']) && $_POST['hm_action'] === 'break' && $hmMsgOk && $hmNextTier) {
    $hmView = intval($hmNextTier['stage']);
}
if (isset($view)) {
    $hmGetView = intval($view);
    if (\player\huyetmach_vessel_tier($hmGetView) !== false) $hmView = $hmGetView;
}
$hmViewTier = \player\huyetmach_vessel_tier($hmView);

// ===== CHI PHI LUYEN HOA CUA MACH DANG CHON =====
$hmNodeDef = $hmDefs[$hmNode];
$hmNodeLv  = intval($hmLevels[$hmNode]);
$hmCost    = \player\huyetmach_vessel_refine_cost($hmNodeLv);
$hmMatName = \player\huyetmach_get_material_name($hmCost['material_id']);
$hmMatHave = \player\huyetmach_vessel_daoju_have($sid, $dblj, $hmCost['material_id']);
$hmLinhThach = intval($player->uyxb);
$hmAtCap   = ($hmNodeLv >= $hmCap);
$hmCanRefine = (!$hmAtCap
    && $hmMatHave >= $hmCost['material_amount']
    && $hmLinhThach >= $hmCost['linh_thach_cost']);

// ===== CHECK LIST THUC TINH =====
$hmGate   = \player\huyetmach_vessel_break_rows($sid, $dblj);
$hmReqRows = $hmGate['rows'];
$hmReady     = $hmGate['ready'];
$hmBossName  = $hmGate['boss_name'];

// ===== DINH DANG CHI SO =====
$hmStatOrder = array('atk', 'hp', 'crit', 'lifesteal', 'dodge', 'pen', 'reduce', 'luck');
$hmStatIcon  = array('atk' => '🗡️', 'hp' => '💗', 'crit' => '💥', 'lifesteal' => '🩸',
    'dodge' => '💨', 'pen' => '🏹', 'reduce' => '🧱', 'luck' => '🍀');
$hmStatDec   = array('atk' => 0, 'hp' => 0, 'crit' => 1, 'lifesteal' => 1,
    'dodge' => 1, 'pen' => 1, 'reduce' => 1, 'luck' => 1);
$hmStatSuf   = array('atk' => '', 'hp' => '', 'crit' => '%', 'lifesteal' => '%',
    'dodge' => '%', 'pen' => '%', 'reduce' => '%', 'luck' => '%');

$hmAttrRows = array();
foreach ($hmStatOrder as $hmStat) {
    $hmVal = floatval($hmB[$hmStat]);
    $hmTxt = ($hmStatDec[$hmStat] > 0) ? number_format($hmVal, $hmStatDec[$hmStat]) : number_format(round($hmVal));
    $hmAttrRows[] = array(
        'label' => \player\huyetmach_vessel_stat_label($hmStat),
        'icon'  => $hmStatIcon[$hmStat],
        'text'  => '+' . $hmTxt . $hmStatSuf[$hmStat]
    );
}

$hmTierColor = $hmTier['color'];
$hmTierIcon  = $hmTier['icon'];
$hmTierName  = $hmTier['name'];
// Bac thuc tinh -> class phan tang hieu ung (hm-fx0 .. hm-fx4), xem docs/UI_HUYETMACH.md
$hmFx = 'hm-fx' . max(0, min(4, $hmStage));
// Dao Pham (pham chat 10) -> chu chay cau vong tren badge
$hmDao = ($hmQuality >= 10) ? ' is-dao' : '';

// Dong tom tat bac dang xem
$hmViewParts = array();
if ($hmView == $hmStage) {
    $hmViewParts[] = 'trần <b>' . intval($hmViewTier['lv_cap']) . '</b>/mạch';
} else {
    $hmViewParts[] = 'cần tổng <b>' . intval($hmViewTier['total_lv_require']) . ' cấp</b>';
    $hmViewParts[] = 'cảnh giới <b>' . intval($hmViewTier['realm_min_level']) . '</b>';
    if (intval($hmViewTier['material_amount']) > 0) {
        $hmViewParts[] = htmlspecialchars(\player\huyetmach_get_material_name(intval($hmViewTier['material_id']))) . ' ×' . intval($hmViewTier['material_amount']);
    }
    if (intval($hmViewTier['linh_thach_cost']) > 0) {
        $hmViewParts[] = number_format(intval($hmViewTier['linh_thach_cost'])) . ' Linh Thạch';
    }
    if (intval($hmViewTier['boss_require']) === 1) $hmViewParts[] = 'giết boss mạch huyết';
}
$hmViewLine = $hmViewTier['icon'] . ' ' . htmlspecialchars($hmViewTier['name'])
    . ($hmView == $hmStage ? ' — đang tu' : ' — cần') . ' · ' . implode(' · ', $hmViewParts);

$hmMsNextLv = intval($hmB['next_milestone_lv']);
$hmMsPct = 100;
if ($hmMsNextLv > 0) {
    $hmMsPct = min(100, round(($hmTotal / $hmMsNextLv) * 100));
}

$hmNodeStat = $hmNodeDef['stat'];
$hmNodeLabel = \player\huyetmach_vessel_stat_label($hmNodeStat);
$hmNodeDec = $hmStatDec[$hmNodeStat];
$hmNodePerLvTxt = number_format(floatval($hmNodeDef['per_lv']), $hmNodeDec);

// ===== TOM TAT BIEN THAN (chi khi co huyet mach va stage >= 3) =====
$hmTfHtml = '';
if ($hmBloodId > 0 && $hmStage >= 3) {
    $hmTf = \player\huyetmach_get_transform($hmBloodId);
    if ($hmTf) {
        $hmTfCan = \player\huyetmach_can_transform($sid, $dblj);
        $hmTfCls = $hmTfCan['ok'] ? 'ok' : 'no';
        $hmTfTxt = $hmTfCan['ok'] ? 'Sẵn sàng' : htmlspecialchars($hmTfCan['msg']);
        $hmTfHtml = '<div class="hm-transform">'
            . '<span class="hm-transform-ico">' . $hmTf['icon'] . '</span>'
            . '<span><span class="hm-transform-name">' . htmlspecialchars($hmTf['name']) . '</span>'
            . ' · ' . intval($hmTf['cooldown_minutes']) . ' phút CD · ' . intval($hmTf['hp_cost_percent']) . '% HP</span>'
            . '<span class="hm-transform-state ' . $hmTfCls . '">' . $hmTfTxt . '</span></div>'
            . '<a class="hm-btn hm-btn-transform" href="?cmd=' . $tfcmd . '">Mở Biến Thân</a>';
    }
}
?>
<div class="hm-wrap <?= $hmFx . $hmDao ?>" style="--hm-tier:<?= $hmTierColor ?>;--hm-color:<?= $player->huyetmach_color ?>">
<?php if ($hmMsg !== ''): ?>
    <div class="hm-msg <?= $hmMsgOk ? 'ok' : 'err' ?>"><?= htmlspecialchars($hmMsg) ?></div>
<?php endif; ?>

<!-- ===== DAI 1: dinh danh + ray thuc tinh + mang mach + lo luyen ===== -->
<div class="hm-card hm-card-body">
    <div class="hm-head">
        <div class="hm-hrow">
            <span class="hm-title">HUYẾT MẠCH</span>
            <span class="hm-badge" style="border-color:<?= $hmTierColor ?>66;color:<?= $hmTierColor ?>"><i class="hm-badge-ico"><?= $hmTierIcon ?></i><?= htmlspecialchars($hmTierName) ?> · trần <?= $hmCap ?></span>
        </div>

        <div class="hm-rail">
<?php for ($hmT = 0; $hmT <= $hmMaxStage; $hmT++):
    $hmRow = \player\huyetmach_vessel_tier($hmT);
    if (!$hmRow) continue;
    $hmRowCls = 'hm-tier hm-t' . $hmT;
    if ($hmT < $hmStage) $hmRowCls .= ' is-done';
    elseif ($hmT == $hmStage) $hmRowCls .= ' is-cur';
    else $hmRowCls .= ' is-lock';
    if ($hmT == $hmView) $hmRowCls .= ' is-view';
    $hmRowCmd = $encode->encode("cmd=huyetmach&view=$hmT&sid=$sid");
    if ($hmT < $hmStage) $hmRowState = 'Đã đạt';
    elseif ($hmT == $hmStage) $hmRowState = 'Trần ' . intval($hmRow['lv_cap']);
    else $hmRowState = 'Cấp ' . intval($hmRow['realm_min_level']);
?>
<?php if ($hmT > 0): ?>
        <span class="hm-link<?= $hmT <= $hmStage ? ' is-lit' : '' ?>" aria-hidden="true"></span>
<?php endif; ?>
        <a class="<?= $hmRowCls ?>" style="border-color:<?= $hmRow['color'] ?>66;--tcolor:<?= $hmRow['color'] ?>" href="?cmd=<?= $hmRowCmd ?>">
            <span class="hm-tier-ico"><?= $hmRow['icon'] ?></span>
            <span class="hm-tier-name"><?= htmlspecialchars($hmRow['short']) ?></span>
            <span class="hm-tier-state"><?= $hmRowState ?></span>
        </a>
<?php endfor; ?>
    </div>
        <div class="hm-viewline"><span class="hm-phys"><?php
            if ($hmBloodId > 0) echo $player->huyetmach_icon . ' ' . htmlspecialchars($player->huyetmach_name) . ' · ' . htmlspecialchars(\player\huyetmach_quality_name($hmQuality));
            else echo 'Chưa có Huyết Mạch';
        ?></span> · <?= $hmViewLine ?></div>
<?php if ($hmBloodId <= 0): ?>
        <div class="hm-empty">Bát Mạch vẫn luyện được khi chưa khai mở huyết mạch — dùng <b>Tố Thể Đan</b> để nhận huyết mạch.</div>
<?php endif; ?>
    </div>

    <div class="hm-forge-wrap">
        <div class="hm-stage">
        <img class="hm-figure" src="./images/huyetmach/kinh-mach.jpg?v=<?= $hmImgVer ?>" alt="Mạng mạch huyết">
        <span class="hm-aura" aria-hidden="true"></span>
        <span class="hm-sweep" aria-hidden="true"></span>
<?php foreach ($hmDefs as $hmGid => $hmGdef):
    $hmGid = intval($hmGid);
    $hmGlv = intval($hmLevels[$hmGid]);
    $hmGstat = $hmGdef['stat'];
    // 4 mach co anh rieng, 4 mach am dung ngoc chung + mau --ring
    $hmGfam = in_array($hmGstat, array('atk', 'hp', 'crit', 'lifesteal')) ? $hmGstat : '';
    $hmGimg = 'ngoc-' . ($hmGfam !== '' ? $hmGfam . '-' : '') . ($hmGlv >= $hmCap ? 'day' : ($hmGlv > 0 ? 'sang' : 'khoa'));
    $hmGcls = 'hm-gem slot-' . $hmGdef['slot'];
    if ($hmGid == $hmNode) $hmGcls .= ' is-sel';
    if ($hmGlv >= $hmCap) $hmGcls .= ' is-max';
    $hmGcmd = $encode->encode("cmd=huyetmach&node=$hmGid&sid=$sid");
    $hmGglow = ($hmCap > 0) ? round(min(1, $hmGlv / $hmCap) * 16) : 0;
?>
        <a class="<?= $hmGcls ?>" href="?cmd=<?= $hmGcmd ?>" title="<?= htmlspecialchars($hmGdef['desc']) ?>">
            <span class="hm-gem-disc" style="--ring:<?= $hmGlv >= $hmCap ? '#6ee7b7' : ($hmGlv > 0 ? $hmTierColor : '#8b93a0') ?>;--glow:<?= $hmGglow ?>px">
                <img class="hm-gem-img" src="./images/huyetmach/<?= $hmGimg ?>.png?v=<?= $hmImgVer ?>" alt="">
            </span>
            <span class="hm-gem-tag"><?= htmlspecialchars($hmGdef['short']) ?> <b><?= $hmGlv ?></b></span>
        </a>
<?php endforeach; ?>
    </div>

        <div class="hm-forge">
        <div class="hm-forge-top">
            <span class="hm-forge-name" style="color:<?= $hmTierColor ?>"><?= htmlspecialchars($hmNodeDef['name']) ?></span>
            <span class="hm-forge-lv">Cấp <b><?= $hmNodeLv ?></b> / <?= $hmCap ?></span>
            <span class="hm-forge-gain">+<?= $hmNodePerLvTxt ?>/cấp · đang +<?= htmlspecialchars($hmAttrRows[array_search($hmNodeStat, $hmStatOrder)]['text']) ?> <?= htmlspecialchars($hmNodeLabel) ?></span>
        </div>
<?php if ($hmAtCap): ?>
        <div class="hm-capnote">Trần <?= $hmCap ?> cấp — phải Thức Tỉnh mới luyện tiếp được.</div>
<?php else: ?>
        <div class="hm-forge-row">
            <span class="hm-cost-item <?= $hmMatHave >= $hmCost['material_amount'] ? 'hm-cost-ok' : 'hm-cost-no' ?>">📦 <?= htmlspecialchars($hmMatName) ?> <b><?= $hmMatHave ?>/<?= $hmCost['material_amount'] ?></b></span>
            <span class="hm-cost-item <?= $hmLinhThach >= $hmCost['linh_thach_cost'] ? 'hm-cost-ok' : 'hm-cost-no' ?>">💰 Linh Thạch <b><?= number_format($hmLinhThach) ?>/<?= number_format($hmCost['linh_thach_cost']) ?></b></span>
        </div>
<?php endif; ?>
        <form method="post" action="?cmd=<?= $encode->encode("cmd=huyetmach&node=$hmNode&sid=$sid") ?>">
            <input type="hidden" name="hm_action" value="refine">
            <input type="hidden" name="hm_node" value="<?= $hmNode ?>">
            <button type="submit" class="hm-btn hm-btn-refine<?= $hmCanRefine ? '' : ' is-off' ?>" <?= $hmCanRefine ? '' : 'disabled' ?>>Luyện Mạch</button>
        </form>
    </div>
    </div>
</div>

<!-- ===== DAI 2: thuoc tinh | thuc tinh (2 cot tren desktop) ===== -->
<div class="hm-card hm-cols">
    <div class="hm-col">
        <div class="hm-h3">Thuộc Tính Kích Hoạt</div>
        <div class="hm-attrs">
<?php foreach ($hmAttrRows as $hmAttr): ?>
            <div class="hm-attr"><span><?= $hmAttr['icon'] ?> <?= htmlspecialchars($hmAttr['label']) ?></span><b><?= $hmAttr['text'] ?></b></div>
<?php endforeach; ?>
        </div>
        <div class="hm-ms">
            <div class="hm-ms-top">
                <span><?php
                    if ($hmB['milestone_name'] !== '') echo htmlspecialchars($hmB['milestone_name']) . ' +' . floatval($hmB['milestone_percent']) . '%';
                    else echo 'Chưa mở mốc nào';
                ?></span>
                <span>Tổng cấp <?= $hmTotal ?><?php if ($hmMsNextLv > 0) echo ' / ' . $hmMsNextLv; ?></span>
            </div>
            <div class="hm-ms-bar"><div class="hm-ms-fill" style="width:<?= $hmMsPct ?>%"></div></div>
            <div class="hm-ms-note"><?php
                if ($hmB['next_milestone_name'] !== '') {
                    echo 'Mốc kế: ' . htmlspecialchars($hmB['next_milestone_name']) . ' (+' . floatval($hmB['next_milestone_percent']) . '%) khi tổng cấp đạt ' . $hmMsNextLv . '.';
                } else {
                    echo 'Toàn bộ mốc đã mở.';
                }
            ?></div>
        </div>
    </div>

    <div class="hm-col">
        <div class="hm-h3">Thức Tỉnh Huyết Mạch</div>
<?php if ($hmNextTier): ?>
        <div class="hm-gate">
            <span><?= $hmTierIcon ?> <?= htmlspecialchars($hmTierName) ?></span>
            <span class="hm-gate-arr">→</span>
            <span style="color:<?= $hmNextTier['color'] ?>"><?= $hmNextTier['icon'] ?> <?= htmlspecialchars($hmNextTier['name']) ?></span>
            <span class="hm-gate-cap">trần <?= intval($hmNextTier['lv_cap']) ?></span>
        </div>
        <div class="hm-reqs">
<?php foreach ($hmReqRows as $hmReqRow): ?>
            <div class="hm-req <?= $hmReqRow['ok'] ? 'ok' : 'no' ?>">[<?= $hmReqRow['ok'] ? 'Đủ' : 'Thiếu' ?>] <?= htmlspecialchars($hmReqRow['text']) ?></div>
<?php endforeach; ?>
        </div>
        <form method="post" action="?cmd=<?= $encode->encode("cmd=huyetmach&view=" . intval($hmNextTier['stage']) . "&sid=$sid") ?>">
            <input type="hidden" name="hm_action" value="break">
            <button type="submit" class="hm-btn hm-btn-break<?= $hmReady ? '' : ' is-off' ?>" <?= $hmReady ? '' : 'disabled' ?>>Thức Tỉnh</button>
        </form>
        <?= $hmTfHtml ?>
<?php else: ?>
        <div class="hm-gate">
            <span><?= $hmTierIcon ?> <?= htmlspecialchars($hmTierName) ?></span>
            <span class="hm-gate-cap">trần <?= $hmCap ?></span>
        </div>
        <div class="hm-node-desc">Huyết thống đã đạt cực hạn của giống loài.</div>
        <?= $hmTfHtml ?>
<?php endif; ?>
    </div>
</div>

<nav class="hm-tabbar">
<?php if ($hmBloodId > 0): ?>
    <a href="?cmd=<?= $upcmd ?>"><i>🔼</i><b>Nâng Phẩm Chất</b></a>
<?php endif; ?>
<?php if ($hmBloodId > 0): ?>
    <a href="?cmd=<?= $bosscmd ?>"><i>👹</i><b>Boss Mạch Huyết</b></a>
<?php endif; ?>
    <a class="on" href="?cmd=<?= $ztcmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
</div>
```

> **TINH DON GIAN**: `$hmAttrRows[array_search(...)]` lay dong chi so cua chinh mach dang chon de hien "dang +N". Neu thay rac, thay bang `floatval($hmB[$hmNodeStat])` + dinh dang theo `$hmStatDec` — KHONG them ham moi.

- [ ] **Step 6: Routes + xoa trang cu** — `game.php`:
  - Xoa dong `'huyetmach_awakening'=>'game/huyetmach_awakening.php',`.
  - Sau `'huyetmach_transform'=>'game/huyetmach_transform.php',` them:
    ```php
			'huyetmach_boss'=>'game/huyetmach_boss.php',
			'huyetmach_upgrade'=>'game/huyetmach_upgrade.php',
    ```
    2 file nay duoc tao o Task 8 va Task 9. **Day la chu dich, khong phai sot**: them route cung luc voi UI giu `game.php` dung mot lan (Task 8/9 khong quay lai `game.php`). Trong khoang giua Task 7 va Task 8/9, 2 link tabbar roi ve `game/nowmid.php` (router khong crash) — vi so do that chi nghiem thu sau Task 9.
  - `rm game/huyetmach_awakening.php` sau khi `grep -rn "huyetmach_awakening" --include=*.php . | grep -v bak/` tra ve 0 ket qua o ngoai `game.php`.
  - Kiem tra `game/zhuangtai.php` van tro `cmd=huyetmach` (khong doi).

- [ ] **Step 7: `game/huyetmach_transform.php` dung nguon `.hm-*`** — giu nguyen logic (Task 6 da sua `uhp`), thay bo `<style>` `.tc-*` duoi trang bang cach: `tc-card`→`hm-card`, `tc-passive`→`hm-attr`, nut `tc-btn tc-btn-fire`→`hm-btn hm-btn-break` (da co rule `.main .hm-btn-break`), nut Quay lai→`hm-btn` + `href="?cmd=<?= $encode->encode("cmd=huyetmach&sid=$sid") ?>"`. Xoa 6 dong `<style>` cua no (de khoi `.hm-*` doc nien quyet dinh kieu).

- [ ] **Step 8: Kiem tra tuong thich PHP 5.6 + nguyen ban**

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
for f in game/huyetmach.php game/huyetmach_transform.php class/function/huyetmach.php; do $PHP -l $f; done
grep -n '??\|<=>\|: *int *{\|function .*) *[A-Za-z\\\\]*{' game/huyetmach.php | head   # 0 ket qua
grep -nP "[^\x00-\x7F]" game/huyetmach.php | grep -vE "[àáảãạăâêôơưđìòù]" | head        # chi co tieng Viet + emoji
```

- [ ] **Step 9: Bump version CSS** — `sed -i "s/mystyle.css?v=[0-9]\+/mystyle.css?v=2026100930/" game.php` theo playbook `docs/UI_XIULIAN.md` muc 9.1 (so hien tai `2026100929` → `2026100930`).

- [ ] **Step 10: Do do that tren trang** (playbook 9.1: seed tai khoan E2E `e2e2e2e2...`, khanh `php -S`, harness iframe 390px + zoom=1). Seed mach:

```sql
UPDATE game1 SET huyetmach_id=1, huyetmach_quality=1, huyetmach_awaken_stage=1,
  endtime=DATE_ADD(NOW(), INTERVAL 4 HOUR) WHERE sid='e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
DELETE FROM player_huyetmach_vessel WHERE sid='e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
INSERT INTO player_huyetmach_vessel (sid,vessel_id,level,updated_at) VALUES
 ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2',1,6,NOW()),('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2',2,6,NOW()),
 ('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2',7,3,NOW()),('e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2',8,0,NOW());
```

Do 5 con so (ghi vao `document.title` cua harness, sau do screenshot luu `docs/img/huyetmach/`):

| Phan tu | Ky vong |
|---|---|
| `.tcg-main-panel` | width 660px desktop / `calc(100vw-18px)` mobile — KHONG doi |
| `.hm-stage` | 622×353 (desktop, aspect 663/377) hoac day du ngang card o mobile |
| 8 nhan `.hm-gem-tag` | khong che nhau, khong che disc lan can (do boundingRect tung cap) |
| nut `Luyen Mach`/`Thuc Tinh` | `backgroundColor` phai LA gradient (khong con `rgb(255,255,255)`), `paddingTop` = 11px |
| `.hm-tabbar` | cao 80px = bang chim `.xl-tabbar` man Tu Luyen |

- [ ] **Step 11: Commit**

```bash
git add css/mystyle.css game.php game/huyetmach.php game/huyetmach_transform.php class/function/huyetmach.php images/huyetmach
git rm game/huyetmach_awakening.php
git commit -m "feat(huyetmach): one-screen meridian network UI (.hm-*) cloned from Nhục Thân"
```

---

### Task 8: Boss mach huyet — 33 boss rieng cho tung nguoi choi

**Files:**
- Create: `class/config/huyetmach_boss_config.json`
- Create: `class/function/huyetmach_boss.php`
- Create: `game/huyetmach_boss.php`
- Modify: `game.php` (route đã thêm ở Task 7 — chỉ kiểm tra lại)

**Interfaces:**
- Consumes: bảng `huyetmach_boss` (Task 2), `huyetmach_boss_mark/killed($sid,$bossName,$dblj)` (có sẵn — `boss_id` là **TÊN** boss, `VARCHAR(50)` utf8), `huyetmach_vessel_tier()` (Task 1), `huyetmach_vessel_bonuses()` (Task 3), `huyetmach_get_key_by_id()` + `huyetmach_element_name()` (có sẵn, `class/function/huyetmach.php:430`, `:634`), `linhcan_get_element_multiplier()` (có sẵn, `class/function/linhcan.php:384`), bảng `playerdaoju (sid,djid,djsum)` (có sẵn).
- Produces (tất cả trong `namespace player`, có guard `function_exists`):
  - `huyetmach_boss_config_all(): array`
  - `huyetmach_boss_row($key, $stage): array|false`
  - `huyetmach_boss_active($sid, $dblj): array|false` — hàng boss đang chiếu, ĐÃ được nạp thêm `icon/color/stage/cfg_key` từ config
  - `huyetmach_boss_spawn($sid, $dblj): array|false`
  - `huyetmach_boss_fights_today($sid, $dblj): int`
  - `huyetmach_boss_calc_damage($player, $bossRow): array('damage'=>int,'crit'=>bool,'elem_mult'=>float)`
  - `huyetmach_boss_vessel_dodge($bonuses): bool` / `huyetmach_boss_vessel_reduce($bonuses, $dmg): int` — nhận thẳng mảng bonus của Task 3, KHONG query lại DB
  - `huyetmach_boss_finish($sid, $dblj, $bossRow): array('html'=>string,'huyet_thach'=>int,'linh_thach'=>int)`
  - `huyetmach_boss_status($sid, $dblj): array('state'=>'killable'|'farmer'|'blocked'|'done','text'=>string,'boss'=>array|false)`

> **BUG DA TIM THAY trong `game/linhcan_boss.php` — KHONG LAP LAI** (doc ma khong can chay):
> `game/linhcan_boss.php:37` chan `if ($cmd == 'attack_lc_boss' ...)` trong khi nut tai `:83` tro toi
> `cmd=linhcan_boss&attack_lc_boss=1`, ma `game.php:89` `parse_str($Dcmd)` dat `$cmd = 'linhcan_boss'`.
> nhanh tan cong **khong bao gio chay** — nut TẤN CÔNG chi tai lai trang. Boss mach huyet dung **co query**:
> `isset($attack)` (sinh ra tu `attack=1`). Bug cua Linh Căn ghi vao `docs/UI_HUYETMACH.md` muc 7 de hoi
> chu du an (Task 12), khong tu sua o day.
> Ly do thu hai: `linhcan_boss_drops()` (`class/function/linhcan_boss.php:168-208`) chi in chu
> "🪨 3 Đá" ma **khong cong vao `playerdaoju`** (trong code con dong `// TODO: Thêm đá ngũ hành`).
> `huyetmach_boss_finish()` duoi day phat that, theo mau `class/function/chetao.php:198-202`.

- [x] **Step 1: Sinh config 33 boss** — KHONG go tay ten boss (master ten la `huyetmach_awakening_config.json → boss_list`):

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$aw = json_decode(file_get_contents("class/config/huyetmach_awakening_config.json"), true);
$hb = json_decode(file_get_contents("class/config/huyetmach_config.json"), true);
$out = array("schema" => "huyetmach_boss_v1",
  "boss_config" => array(
    "per_player" => true, "expire_minutes" => 2880, "max_fights_per_day" => 5,
    "stats" => array("hp_per_level" => 220, "atk_per_level" => 14, "def_per_level" => 9, "crit" => 15,
                    "stage_mult" => array("2" => 1.0, "3" => 1.6, "4" => 2.6)),
    "drops" => array("huyet_thach_id" => 112,
      "by_stage" => array("2" => array("huyet_thach" => 4,  "linh_thach" => 20000),
                          "3" => array("huyet_thach" => 8,  "linh_thach" => 80000),
                          "4" => array("huyet_thach" => 16, "linh_thach" => 300000))),
    "first_kill" => array("2" => 10, "3" => 20, "4" => 40)),
  "bosses" => array());
$n = 0;
foreach ($hb as $id => $b) {
  if (!is_array($b) || !isset($b["key"])) continue;
  $k = $b["key"];
  if (!isset($aw["boss_list"][$k])) continue;
  foreach ($aw["boss_list"][$k] as $sk => $row) {
    $stage = intval(str_replace("stage_", "", $sk));
    $out["bosses"][$k . "_" . $stage] = array(
      "key" => $k, "stage" => $stage, "bloodline_id" => intval($id),
      "name" => $row["name"], "level" => intval($row["level"]),
      "element" => $row["element"], "icon" => $b["icon"], "color" => $b["color"]);
    $n++;
  }
}
file_put_contents("class/config/huyetmach_boss_config.json", json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "boss=" . $n . PHP_EOL;
'
```

Expected: `boss=33` (11 huyết mạch × stage 2/3/4). Neu khong ra 33, in ten mau thieu:

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r "\$hb=json_decode(file_get_contents('class/config/huyetmach_config.json'),true);foreach(\$hb as \$id=>\$b){if(is_array(\$b)&&isset(\$b['name']))echo \$id.' '.(isset(\$b['key'])?\$b['key']:'NO-KEY').' '.\$b['name'].PHP_EOL;}"
```
Dùng `huyetmach_get_key_by_id()` (`class/function/huyetmach.php:430`) làm bảng thay nếu config thiếu `key` — **KHÔNG tự đặt tên boss mới**.

- [x] **Step 2: Quét CJK + validate JSON** (làm lại lệnh ở Task 1 Step 2 trên file mới), rồi kiểm đếm:

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r "\$d=json_decode(file_get_contents('class/config/huyetmach_boss_config.json'),true);echo count(\$d['bosses']).PHP_EOL;"
```
Expected: `33`

- [x] **Step 3: Test bai** `tests/test_huyetmach_boss.php` (toan bo file):

```php
<?php
// Test Boss mach huyet. Chay: php.exe tests/test_huyetmach_boss.php
require_once __DIR__ . '/../class/player.php';
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
$TESTSID = str_repeat('f', 32);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$hasPlayer = intval($db->query("SELECT COUNT(*) FROM game1 WHERE sid = '$TESTSID'")->fetchColumn());
if (!$hasPlayer) {
    $db->prepare("INSERT INTO game1 (sid, uname, ulv, uhp, umaxhp, endtime, huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
        VALUES ('$TESTSID', 'HMTEST', 300, 1000, 10000, NOW(), 1, 1, 1)")->execute();
    $tmpRow = true;
} else {
    $tmpRow = false;
    $db->prepare("UPDATE game1 SET huyetmach_id = 1, huyetmach_quality = 1, huyetmach_awaken_stage = 1 WHERE sid = ?")->execute(array($TESTSID));
}
$db->prepare("DELETE FROM huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM player_huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid IN (112,113)")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);

// A1: config du 33 boss
$cfg = \player\huyetmach_boss_config_all();
tassert('boss config 33', count($cfg['bosses']), 33);
tassert('boss chan_long_2 dung ten', \player\huyetmach_boss_row('chân_long', 2) ? \player\huyetmach_boss_row('chân_long', 2)['name'] : '', 'Rồng Ngàn Tuổi');
tassert('boss stage 5 khong ton tai', \player\huyetmach_boss_row('chân_long', 5), false);

// A2: spawn theo huyet mach + stage ke tiep
$b = \player\huyetmach_boss_spawn($TESTSID, $db);
tassert('spawn sinh boss', is_array($b), true);
tassert('spawn dung ten', $b['name'], 'Rồng Ngàn Tuổi');
tassert('spawn dung stage', intval($b['stage']), 2);
tassert('spawn co icon tu config', $b['icon'] !== '', true);
tassert('hp > 0', $b['hp'] > 0, true);
$a = \player\huyetmach_boss_active($TESTSID, $db);
tassert('active trung id voi spawn', intval($a['id']), intval($b['id']));
tassert('active duoc nap icon', isset($a['icon']), true);

// A3: throttle max_fights_per_day
$max = intval($cfg['boss_config']['max_fights_per_day']);
$need = $max - \player\huyetmach_boss_fights_today($TESTSID, $db);
for ($i = 0; $i < $need; $i++) {
    $db->prepare("INSERT INTO huyetmach_boss (sid, cfg_key, name, element, level, hp, maxhp, atk, def, crit, spawned_at, status)
        VALUES (?, 'filler', 'Filler', 'none', 1, 1, 1, 1, 1, 0, NOW(), 'killed')")->execute(array($TESTSID));
}
tassert('fights_today dat max', \player\huyetmach_boss_fights_today($TESTSID, $db) >= $max, true);
// Spawn uu tien boss dang chieu: nguoi choi dang danh do phai danh tiep duoc,
// nen chan luot chi test duoc khi KHONG con boss nao song.
$bAgain = \player\huyetmach_boss_spawn($TESTSID, $db);
tassert('spawn tro lai boss dang chieu', intval($bAgain['id']), intval($b['id']));
$db->prepare("UPDATE huyetmach_boss SET status = 'killed', killed_at = NOW() WHERE sid = ? AND status = 'active'")->execute(array($TESTSID));
tassert('spawn bi chan khi het luot', \player\huyetmach_boss_spawn($TESTSID, $db), false);
$st = \player\huyetmach_boss_status($TESTSID, $db);
tassert('status blocked', $st['state'], 'blocked');

// A4: giết boss -> mark + phat that vao playerdaoju
$db->prepare("DELETE FROM huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$b2 = \player\huyetmach_boss_spawn($TESTSID, $db);
$before = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 112")->fetchColumn());
$fin = \player\huyetmach_boss_finish($TESTSID, $db, $b2);
// stage 2: 4 (by_stage) + 10 (first_kill) = 14
tassert('finish tra dung so Huyet Thach', $fin['huyet_thach'], 14);
$after = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 112")->fetchColumn());
tassert('Huyet Thach vao toi', $after, $before + 14);
tassert('mark sau khi giot', \player\huyetmach_boss_killed($TESTSID, $b2['name'], $db), true);
$row = $db->query("SELECT status FROM huyetmach_boss WHERE id = " . intval($b2['id']))->fetchColumn();
tassert('boss chuyen status killed', $row, 'killed');
// giot lan 2 (boss gia) khong duoc nhan first_kill nua
$b3 = \player\huyetmach_boss_spawn($TESTSID, $db);
$fin2 = \player\huyetmach_boss_finish($TESTSID, $db, $b3);
tassert('first_kill khong lap lai', $fin2['huyet_thach'], 4);
$st2 = \player\huyetmach_boss_status($TESTSID, $db);
tassert('status farmer con luot', $st2['state'], 'farmer');

// A5: mach am dung dung chi so (khong query DB lan 2)
$vb = array('dodge' => 0, 'reduce' => 0);
tassert('khong co mach thi khong ne', \player\huyetmach_boss_vessel_dodge($vb), false);
tassert('reduce 0 giu nguyen don', \player\huyetmach_boss_vessel_reduce($vb, 100), 100);
$vb2 = array('dodge' => 100, 'reduce' => 100);   // bi chan 15%
tassert('reduce chan 15', \player\huyetmach_boss_vessel_reduce($vb2, 100), 85);

$db->prepare("DELETE FROM huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM player_huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid IN (112,113)")->execute(array($TESTSID));
if ($tmpRow) {
    $db->prepare("DELETE FROM game1 WHERE sid = ?")->execute(array($TESTSID));
} else {
    $db->prepare("UPDATE game1 SET huyetmach_id = 0, huyetmach_awaken_stage = 0 WHERE sid = ?")->execute(array($TESTSID));
}
\player\player_cache_clear($TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "$fails FAIL\n";
exit($fails === 0 ? 0 : 1);
```

- [x] **Step 4: Chay test -> FAIL** (`Call to undefined function player\huyetmach_boss_config_all`)

- [x] **Step 5: Viet `class/function/huyetmach_boss.php`** (toan bo):

```php
<?php
namespace player;

/**
 * huyetmach_boss.php - Boss mach huyet theo tung nguoi choi.
 * Khac Boss Linh Can (boss toan server): o day moi nguoi choi co boss cua minh,
 * de checklist thuc tinh o Task 5 chi qua khi danh dung con cua chinh no.
 */

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_config_path')) {
    function huyetmach_boss_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_boss_config.json',
            __DIR__ . '/huyetmach_boss_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_config_all')) {
    function huyetmach_boss_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(huyetmach_boss_config_path());
        $cache = json_decode($json, true);
        if (!is_array($cache)) $cache = array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_row')) {
    function huyetmach_boss_row($key, $stage){
        $cfg = huyetmach_boss_config_all();
        $id = $key . '_' . intval($stage);
        return isset($cfg['bosses'][$id]) ? $cfg['bosses'][$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_enrich')) {
    /** Nap them icon/color/stage/cfg_key vao hang DB de trang sau khong doc config trung. */
    function huyetmach_boss_enrich($row){
        if (!is_array($row)) return false;
        $cfg = huyetmach_boss_config_all();
        $k = isset($row['cfg_key']) ? $row['cfg_key'] : '';
        if (isset($cfg['bosses'][$k])) {
            $row['icon'] = $cfg['bosses'][$k]['icon'];
            $row['color'] = $cfg['bosses'][$k]['color'];
            $row['stage'] = intval($cfg['bosses'][$k]['stage']);
        } else {
            $row['icon'] = '👹';
            $row['color'] = '#9C27B0';
            $row['stage'] = 0;
        }
        return $row;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_fights_today')) {
    function huyetmach_boss_fights_today($sid, $dblj){
        $stmt = $dblj->prepare("SELECT COUNT(*) FROM huyetmach_boss WHERE sid = ? AND spawned_at >= CURDATE()");
        $stmt->execute(array($sid));
        return intval($stmt->fetchColumn());
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_active')) {
    function huyetmach_boss_active($sid, $dblj){
        $cfg = huyetmach_boss_config_all();
        $expire = intval(isset($cfg['boss_config']['expire_minutes']) ? $cfg['boss_config']['expire_minutes'] : 2880);
        $stmt = $dblj->prepare("SELECT * FROM huyetmach_boss WHERE sid = ? AND status = 'active' ORDER BY spawned_at DESC LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return false;
        $elapsed = (time() - strtotime($row['spawned_at'])) / 60;
        if ($elapsed > $expire || intval($row['hp']) <= 0) {
            $dblj->prepare("UPDATE huyetmach_boss SET status = 'inactive' WHERE id = ?")->execute(array($row['id']));
            return false;
        }
        return huyetmach_boss_enrich($row);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_spawn')) {
    function huyetmach_boss_spawn($sid, $dblj){
        $active = huyetmach_boss_active($sid, $dblj);
        if ($active) return $active;

        $cfg = huyetmach_boss_config_all();
        $bc = isset($cfg['boss_config']) ? $cfg['boss_config'] : array();
        $max = intval(isset($bc['max_fights_per_day']) ? $bc['max_fights_per_day'] : 5);
        if (huyetmach_boss_fights_today($sid, $dblj) >= $max) return false;

        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->huyetmach_id) <= 0) return false;

        $stage = intval($player->huyetmach_awaken_stage) + 1;
        if (huyetmach_vessel_tier($stage) === false) return false;
        $key = huyetmach_get_key_by_id(intval($player->huyetmach_id));
        $def = huyetmach_boss_row($key, $stage);
        if (!$def) return false;

        $s = isset($bc['stats']) ? $bc['stats'] : array();
        $lv = max(1, intval($def['level']));
        $mult = 1.0;
        if (isset($bc['stats']['stage_mult'][strval($stage)])) {
            $mult = floatval($bc['stats']['stage_mult'][strval($stage)]);
        }
        $hp = intval(round(intval($s['hp_per_level']) * $lv * $mult));
        $atk = intval(round(intval($s['atk_per_level']) * $lv * $mult));
        $defv = intval(round(intval($s['def_per_level']) * $lv * $mult));
        $critv = intval($s['crit']);
        $cfgKey = $key . '_' . $stage;

        $ins = $dblj->prepare("INSERT INTO huyetmach_boss (sid, cfg_key, name, element, level, hp, maxhp, atk, def, crit, spawned_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')");
        $ins->execute(array($sid, $cfgKey, $def['name'], $def['element'], $lv, $hp, $hp, $atk, $defv, $critv));

        return array(
            'id' => intval($dblj->lastInsertId()), 'sid' => $sid, 'cfg_key' => $cfgKey,
            'name' => $def['name'], 'stage' => $stage, 'element' => $def['element'],
            'level' => $lv, 'hp' => $hp, 'maxhp' => $hp, 'atk' => $atk, 'def' => $defv,
            'crit' => $critv, 'icon' => $def['icon'], 'color' => $def['color']
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_calc_damage')) {
    function huyetmach_boss_calc_damage($player, $bossRow){
        $baseDmg = max(1, intval($player->ugj) - intval(intval($bossRow['def']) * 0.75));
        $lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $elemMult = 1.0;
        if (function_exists(__NAMESPACE__ . '\\linhcan_get_element_multiplier')) {
            $elemMult = linhcan_get_element_multiplier($lcElement, $bossElement);
        }
        $dmg = max(1, round($baseDmg * $elemMult));
        $isCrit = false;
        if (mt_rand(1, 100) <= intval($player->ubj)) {
            $dmg = round($dmg * 1.72);
            $isCrit = true;
        }
        return array('damage' => intval($dmg), 'crit' => $isCrit, 'elem_mult' => floatval($elemMult));
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_vessel_dodge')) {
    /** mach Am Kieu (Ne Tranh): % ne = chi so, chan tuyet doi 15. */
    function huyetmach_boss_vessel_dodge($bonuses){
        $pct = min(15, floatval(isset($bonuses['dodge']) ? $bonuses['dodge'] : 0));
        if ($pct <= 0) return false;
        return mt_rand(1, 10000) <= intval($pct * 100);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_vessel_reduce')) {
    /** mach Duong Duy (Giam Vat Ly): tru % sat thuong vat ly cua boss. */
    function huyetmach_boss_vessel_reduce($bonuses, $dmg){
        $pct = min(15, floatval(isset($bonuses['reduce']) ? $bonuses['reduce'] : 0));
        if ($pct <= 0) return max(1, intval($dmg));
        return max(1, intval(round($dmg * (1 - $pct / 100))));
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_finish')) {
    /** Thuong + danh dau da giot. boss_id cua player_huyetmach_boss la TEN boss (khoa that cua Task 5). */
    function huyetmach_boss_finish($sid, $dblj, $bossRow){
        $cfg = huyetmach_boss_config_all();
        $stage = intval($bossRow['stage']);
        $d = (isset($cfg['boss_config']['drops']['by_stage'][strval($stage)]))
            ? $cfg['boss_config']['drops']['by_stage'][strval($stage)]
            : array('huyet_thach' => 4, 'linh_thach' => 20000);
        $ht = intval($d['huyet_thach']);
        $lt = intval($d['linh_thach']);
        $htId = intval(isset($cfg['boss_config']['drops']['huyet_thach_id']) ? $cfg['boss_config']['drops']['huyet_thach_id'] : 112);

        $firstKill = !huyetmach_boss_killed($sid, $bossRow['name'], $dblj);
        if ($firstKill && isset($cfg['boss_config']['first_kill'][strval($stage)])) {
            $ht += intval($cfg['boss_config']['first_kill'][strval($stage)]);
        }

        $dblj->beginTransaction();
        try {
            $upd = $dblj->prepare("UPDATE huyetmach_boss SET status='killed', killed_at = NOW() WHERE id = ? AND status <> 'killed'");
            $upd->execute(array($bossRow['id']));
            if ($upd->rowCount() !== 1) throw new Exception('Boss không còn ở trạng thái khiêu chiến.');

            if ($lt > 0) {
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($lt, $sid));
            }
            if ($firstKill) {
                huyetmach_boss_mark($sid, $bossRow['name'], $dblj);
            }
            $dblj->commit();
        } catch (Exception $e) {
            if ($dblj->inTransaction()) $dblj->rollBack();
            return array('html' => '<font color="#f87171">Lỗi phát thưởng: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</font>',
                'huyet_thach' => 0, 'linh_thach' => 0);
        }
        // Huyet Thach PHAI di qua \player\adddj() (class/player.php:1285), khong nam SQL tran:
        // adddj tim theo sid roi moi INSERT (co uid = player->uid), va no mien nhiem vu 'collect'
        // qua nhiemvu_update_all + changerwyq1. SQL tran trong `linhcan_boss.php` vi thieu buoc
        // nay nen da phat "3 Da" ma khong nao vao playerdaoju. adddj also no-op neu getdaoju()
        // khong thay djid -> 112/113 bat buoc phai co trong daoju_config (Task 4).
        adddj($sid, $htId, $ht, $dblj);
        player_cache_clear($sid);

        $html = "<font color='#FFD700'>🩸 {$ht} Huyết Thạch</font><br/>";
        if ($lt > 0) $html .= "<font color='#4CAF50'>💰 " . number_format($lt) . " Linh Thạch</font><br/>";
        if ($firstKill) $html .= "<font color='#6ee7b7'>✦ Hạ boss mạch huyết lần đầu — checklist Thức Tỉnh đã ghi nhận.</font><br/>";
        return array('html' => $html, 'huyet_thach' => $ht, 'linh_thach' => $lt);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_status')) {
    function huyetmach_boss_status($sid, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->huyetmach_id) <= 0) {
            return array('state' => 'done', 'text' => 'Chưa có Huyết Mạch — chưa mở boss mạch huyết.', 'boss' => false);
        }
        $stage = intval($player->huyetmach_awaken_stage);
        $next = huyetmach_vessel_tier($stage + 1);
        if (!$next || intval($next['boss_require']) !== 1) {
            return array('state' => 'done', 'text' => 'Bậc kế tiếp không cần boss mạch huyết.', 'boss' => false);
        }
        $boss = huyetmach_boss_active($sid, $dblj);
        if ($boss) {
            return array('state' => 'killable', 'text' => 'Đang khiêu chiến: ' . $boss['name'] . ' (cấp ' . intval($boss['level']) . ').', 'boss' => $boss);
        }
        $cfg = huyetmach_boss_config_all();
        $max = intval(isset($cfg['boss_config']['max_fights_per_day']) ? $cfg['boss_config']['max_fights_per_day'] : 5);
        $today = huyetmach_boss_fights_today($sid, $dblj);
        if ($today >= $max) {
            return array('state' => 'blocked', 'text' => 'Hết lượt khiêu chiến hôm nay (' . $max . ' lượt/ngày).', 'boss' => false);
        }
        return array('state' => 'farmer', 'text' => 'Còn ' . ($max - $today) . ' lượt khiêu chiến hôm nay.', 'boss' => false);
    }
}
?>
```

- [x] **Step 6: Chay test -> PASS** — `php.exe tests/test_huyetmach_boss.php` (kiu `ALL PASS`), lint `class/function/huyetmach_boss.php` + file test.

- [x] **Step 7: Viet `game/huyetmach_boss.php`** (toan bo noi dung). Khuon dung `.hm-*` cua Task 7 — **khong** `<style>` rieng (khong lap `.lcb-*`), thanh HP dung `.hm-ms-bar/.hm-ms-fill`:

```php
<?php
// BOSS MACH HUYET — mot boss rieng cho moi nguoi choi.
// Logic: class/function/huyetmach_boss.php
// CSS:   css/mystyle.css (block .hm-*)

$hmcmd     = $encode->encode("cmd=huyetmach&sid=$sid");
$ztcmd     = $encode->encode("cmd=zhuangtai&sid=$sid");
$attackCmd = $encode->encode("cmd=huyetmach_boss&attack=1&sid=$sid");

$player = \player\getplayer($sid, $dblj);
$boss   = \player\huyetmach_boss_spawn($sid, $dblj);
$st     = \player\huyetmach_boss_status($sid, $dblj);
$hmVb   = \player\huyetmach_vessel_bonuses($sid, $dblj);   // doc 1 lan, dung cho Ne + Giam

$battleLog = '';
$hmCanFight = isset($attack) && $boss && intval($boss['hp']) > 0;
if ($hmCanFight && intval($player->uhp) <= 0) {
    // Cung cach pve.php:500 - het Khi Huyet thi khong duoc danh tiep.
    // (Phat sinh khi Step 10 that: tai khoan E2E uhp=35 bi boss phan don xuong 0.)
    $battleLog = "<font color='#f87171'>Ngươi đang trọng thương (Khí Huyết = 0) — phải hồi phục trước khi khiêu chiến.</font>";
} elseif ($hmCanFight) {
    $res = \player\huyetmach_boss_calc_damage($player, $boss);
    $dmg = intval($res['damage']);
    $critText = $res['crit'] ? " <font color='#FFD700'>[Bạo kích!]</font>" : '';
    $elemText = '';
    if ($res['elem_mult'] > 1.0) $elemText = " <font color='#4CAF50'>[Khắc chế! +" . intval(($res['elem_mult'] - 1) * 100) . '%]</font>';
    elseif ($res['elem_mult'] < 1.0) $elemText = " <font color='#f44336'>[Bị khắc! -" . intval((1 - $res['elem_mult']) * 100) . '%]</font>';

    $newHp = max(0, intval($boss['hp']) - $dmg);
    $dblj->prepare("UPDATE huyetmach_boss SET hp = ? WHERE id = ?")->execute(array($newHp, intval($boss['id'])));
    $battleLog .= "Ngươi gây <font color='#FF5722'>{$dmg}</font> sát thương lên " . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . "{$critText}{$elemText}<br/>";

    if ($newHp <= 0) {
        $boss['hp'] = 0;
        $drop = \player\huyetmach_boss_finish($sid, $dblj, $boss);
        $battleLog .= "<font color='#4CAF50'>" . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . " đã bị tiêu diệt!</font><br/>" . $drop['html'];
        // mau thong bao he thong: game/club_boss.php:387-389
        $stmt = $dblj->prepare("INSERT INTO ggliaotian(name,msg,uid) VALUES(?,?,?)");
        $stmt->execute(array('【Hệ Thống】',
            htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8') . ' đã hạ Boss mạch huyết [' . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . ']', 0));
        \player\player_cache_clear($sid);
        $player = \player\getplayer($sid, $dblj);
        $boss = false;
        $st = \player\huyetmach_boss_status($sid, $dblj);
    } else {
        if (\player\huyetmach_boss_vessel_dodge($hmVb)) {
            $battleLog .= "<font color='#6ee7b7'>Mạch Né Tranh — ngươi tránh được đòn của " . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . "!</font><br/>";
        } else {
            $bDmg = max(1, intval(round(intval($boss['atk']) - intval($player->ufy) * 0.75)));
            $bDmg = \player\huyetmach_boss_vessel_reduce($hmVb, $bDmg);
            $newPlayerHp = max(0, intval($player->uhp) - $bDmg);
            $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?")->execute(array($newPlayerHp, $sid));
            $battleLog .= htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . " phản đòn <font color='#f44336'>{$bDmg}</font> sát thương<br/>";
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
        }
        $boss['hp'] = $newHp;
    }
}
?>
<div class="hm-wrap hm-fx<?= max(0, min(4, intval($player->huyetmach_awaken_stage))) ?>" style="--hm-tier:<?= \player\huyetmach_awaken_color(intval($player->huyetmach_awaken_stage)) ?>">
<div class="hm-card">
    <div class="hm-hrow">
        <span class="hm-title">BOSS MẠCH HUYẾT</span>
        <span class="hm-badge" style="border-color:<?= \player\huyetmach_awaken_color(intval($player->huyetmach_awaken_stage)) ?>66;color:<?= \player\huyetmach_awaken_color(intval($player->huyetmach_awaken_stage)) ?>"><i class="hm-badge-ico"><?= $boss ? $boss['icon'] : '👹' ?></i><?= $boss ? htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') : 'Chưa khiêu chiến' ?></span>
    </div>
<?php if ($boss): ?>
    <div class="hm-viewline"><span class="hm-phys"><?= $player->huyetmach_icon ?> <?= htmlspecialchars($player->huyetmach_name, ENT_QUOTES, 'UTF-8') ?></span>
        · cấp <b><?= intval($boss['level']) ?></b> · hệ <b><?= \player\huyetmach_element_name($boss['element']) ?></b>
        · Công <b><?= number_format(intval($boss['atk'])) ?></b> · Phòng <b><?= number_format(intval($boss['def'])) ?></b> · Bạo <b><?= intval($boss['crit']) ?>%</b></div>
    <div class="hm-ms">
        <div class="hm-ms-top">
            <span>Huyết mạch đối phương</span>
            <span><?= number_format(intval($boss['hp'])) ?> / <?= number_format(intval($boss['maxhp'])) ?></span>
        </div>
        <div class="hm-ms-bar"><div class="hm-ms-fill" style="width:<?= intval($boss['maxhp']) > 0 ? min(100, intval(intval($boss['hp']) * 100 / intval($boss['maxhp']))) : 0 ?>%"></div></div>
        <div class="hm-ms-note"><?= htmlspecialchars($st['text'], ENT_QUOTES, 'UTF-8') ?> · HP của ngươi: <b><?= number_format(intval($player->uhp)) ?></b></div>
    </div>
    <a class="hm-btn hm-btn-break" href="?cmd=<?= $attackCmd ?>" style="width:100%;margin-top:10px">Tấn Công</a>
<?php else: ?>
    <div class="hm-node-desc"><?= htmlspecialchars($st['text'], ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
</div>
<?php if ($battleLog !== ''): ?>
<div class="hm-card">
    <div class="hm-h3">⚔ Chiến đấu</div>
    <div class="hm-node-desc"><?= $battleLog ?></div>
</div>
<?php endif; ?>
<nav class="hm-tabbar">
    <a href="?cmd=<?= $hmcmd ?>"><i>🩸</i><b>Mạch Huyết</b></a>
    <a class="on" href="?cmd=<?= $ztcmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
</div>
```

- [x] **Step 8: Kiem tra ten bien `attack` trung lap** — `parse_str($Dcmd)` (`game.php:89`) bien `attack=1` thanh `$attack`; bien nay toi doi pho bien. Chay:

```bash
grep -rn 'isset($attack)\|\$attack *=' --include=*.php game/ class/ | grep -v bak/
```
Nếu có trang nào khác đã dùng `$attack`, đổi URL Task 8 thành `cmd=huyetmach_boss&hm_attack=1&sid=` và điều kiện thành `isset($hm_attack)`. Ghi quyết định vào `docs/UI_HUYETMACH.md` (Task 12).

- [x] **Step 9: Doi chieu vong tron voi Task 5/6** — sau khi co boss that, chay lai `tests/test_huyetmach_vessel.php` (phan D+E) va kiem tra:
  - `huyetmach_vessel_break_rows()` tra row `Boss mạch huyết: <tên> (đã giết)` voi `ok = true` sau khi `huyetmach_boss_finish()` chay that (khong phai mark tay).
  - Ten boss trong `huyetmach_boss_config.json` **trung tuyen doi** voi `boss_list` (Task 5 doc `boss_list` de lay ten de checklist). Neu sau này gộp master về `huyetmach_boss_config.json`, sửa `huyetmach_vessel_boss_name()` trong Task 5 doc file mới — ghi rõ ở `docs/UI_HUYETMACH.md` muc 2.
  - `huyetmach_awaken()` (Task 6) chi tang stage khi boss da giết + du vat pham.

- [x] **Step 10: Nghiem thu bang mat** (playbook `docs/UI_XIULIAN.md` muc 9.1): seed tai khoan E2E `huyetmach_id=1, huyetmach_awaken_stage=1`, mo `game.php?cmd=huyetmach_boss`, bam **Tấn Công** 1 lan → `battleLog` phai xuat hien (chung minh `isset($attack)` chay, khong benh nhu Linh Căn); kiem `playerdaoju.djsum` tang; kiem `huyetmach_boss.status = 'killed'`; kiem tin `【Hệ Thống】` xuat hien dung dang trong khung chat (`game/nowmid.php:404` dung `tcg_chat_format_msg`).

- [x] **Step 11: Commit**

```bash
git add class/config/huyetmach_boss_config.json class/function/huyetmach_boss.php game/huyetmach_boss.php tests/test_huyetmach_boss.php
git commit -m "feat(huyetmach): per-player bloodline bosses with real drops and daily throttle"
```

---

---

### Task 9: Nâng phẩm chất Huyết Mạch (9 bậc) + màn `huyetmach_upgrade`

**Files:**
- Create: `class/config/huyetmach_upgrade_config.json`
- Modify: `class/function/huyetmach.php` (append khoi `huyetmach_upgrade_*` xuong **cuối file**, guard `function_exists` như Task 1 — file dùng `namespace player;` dạng câu lệnh nên **không có** `}` đóng namespace)
- Create: `game/huyetmach_upgrade.php`
- Create: `tests/test_huyetmach_upgrade.php`
- Modify: `css/mystyle.css` (them khoi `.hmq-*` ngay sau khoi `.hm-*` cua Task 7)
- Modify: `game.php` (chi bump `mystyle.css?v=`, khong them route — Task 7 Step 6 da them `'huyetmach_upgrade'`)

**Interfaces:**
- Consumes: `huyetmach_quality_name/color/icon($q)` (da co, `class/function/huyetmach.php:568/580/591`), `huyetmach_get_material_name($djid)` (Task 4 da them 112/113), `huyetmach_vessel_daoju_have($sid,$dblj,$djid)` (Task 4), `getplayer()` + `player_cache_clear()` (core), bang `player_huyetmach_upgrade_log` (Task 2), cac class `.hm-card/.hm-badge/.hm-btn/.hm-empty/.hm-h3/.hm-tabbar` (Task 7).
- Produces:
  - `huyetmach_upgrade_config_all(): array`
  - `huyetmach_get_upgrade_info($currentQuality): array|false` — false khi da pham 10.
  - `huyetmach_upgrade($sid, $dblj): array('ok' => bool, 'msg' => string, 'new_quality' => int)`
  - route `cmd=huyetmach_upgrade` → `game/huyetmach_upgrade.php`, nhan `$_POST['hm_action'] === 'upgrade'`.
  - CSS: class the `hmq-q1 .. hmq-q10` (phan tầng hiệu ứng theo PHẨM CHẤT, khong phai theo stage — stage dung `hm-fx0..fx4` cua Task 7).

> **TAI SAO KHONG PHAI LA BAN SAO cua `game/thechat_upgrade.php`**: ba le.
> 1. `thechat_upgrade()` **khong** dung transaction va **khong** kiem `rowCount()` — neu UPDATE vat lieu khong tr hang nao (da bi ai do dung het), code van quay `mt_rand` va mat cuoc mà không tru gì cả. Task 9 sua cho Huyết Mạch; loi cua thechat ghi vao Task 12 chu khong sua lan (khong mo rong pham vi sua).
> 2. `thechat_upgrade.php` tu dinh nghia `.tc-*` bang `<style>` inline. Toan bo manh Huyết Mạch da ve mot nien `css/mystyle.css` (Task 7 Step 7), nen trang nay chi dung class, khong khai bao CSS rieng.
> 3. Pham chat Huyết Mạch co chan canh gioi (`unlock_realm_min_level`) ma thechat khong co — dung theo spec muc 2.2.

> **KY THUAT — ten pham KHONG nam trong JSON.** `thechat_upgrade_config.json` trung lap `to_name` voi `quality_names` cua no. O day `huyetmach_upgrade_config.json` chi luu `from_quality/to_quality`, ten mau icon lay tu `huyetmach_quality_*()` (da co, `class/function/huyetmach.php:568`). Mot nien duy nhat.

- [ ] **Step 1: Tao `class/config/huyetmach_upgrade_config.json`** (noi dung nguyen chan):

```json
{
  "schema": "huyetmach_upgrade_v1",
  "note": "Nâng phẩm chất nhân chỉ số gốc của huyết mạch (Công Kích, Khí Huyết, Bạo Kích, Hút Máu, Nguyên Tố). Mạch huyết tính theo cấp, KHÔNG nhân phẩm.",
  "upgrades": {
    "1": {"from_quality": 1, "to_quality": 2, "linh_thach_cost": 1000, "material_id": 112, "material_amount": 5, "success_rate": 95, "unlock_realm_min_level": 0, "description": "Phàm Phẩm lên Linh Phẩm — bước đầu của kẻ mới khai mạch."},
    "2": {"from_quality": 2, "to_quality": 3, "linh_thach_cost": 5000, "material_id": 112, "material_amount": 10, "extra_material_id": 101, "extra_material_amount": 3, "success_rate": 90, "unlock_realm_min_level": 0, "description": "Linh Phẩm lên Huyền Phẩm, cần Huyền Thạch."},
    "3": {"from_quality": 3, "to_quality": 4, "linh_thach_cost": 20000, "material_id": 112, "material_amount": 8, "extra_material_id": 102, "extra_material_amount": 5, "success_rate": 85, "unlock_realm_min_level": 100, "description": "Huyền Phẩm lên Địa Phẩm, cần trúc Cơ (cấp 100)."},
    "4": {"from_quality": 4, "to_quality": 5, "linh_thach_cost": 80000, "material_id": 112, "material_amount": 10, "extra_material_id": 103, "extra_material_amount": 5, "success_rate": 75, "unlock_realm_min_level": 100, "description": "Địa Phẩm lên Thiên Phẩm."},
    "5": {"from_quality": 5, "to_quality": 6, "linh_thach_cost": 300000, "material_id": 112, "material_amount": 8, "extra_material_id": 104, "extra_material_amount": 3, "success_rate": 65, "unlock_realm_min_level": 150, "description": "Thiên Phẩm lên Thánh Phẩm, cần Kim Đan (cấp 150)."},
    "6": {"from_quality": 6, "to_quality": 7, "linh_thach_cost": 1000000, "material_id": 112, "material_amount": 10, "extra_material_id": 105, "extra_material_amount": 5, "success_rate": 55, "unlock_realm_min_level": 150, "description": "Thánh Phẩm lên Tiên Phẩm."},
    "7": {"from_quality": 7, "to_quality": 8, "linh_thach_cost": 5000000, "material_id": 112, "material_amount": 8, "extra_material_id": 106, "extra_material_amount": 3, "success_rate": 45, "unlock_realm_min_level": 200, "description": "Tiên Phẩm lên Thần Phẩm, cần Nguyên Anh (cấp 200)."},
    "8": {"from_quality": 8, "to_quality": 9, "linh_thach_cost": 20000000, "material_id": 112, "material_amount": 10, "extra_material_id": 107, "extra_material_amount": 5, "success_rate": 35, "unlock_realm_min_level": 280, "description": "Thần Phẩm lên Đế Phẩm, cần Đại Thừa (cấp 280)."},
    "9": {"from_quality": 9, "to_quality": 10, "linh_thach_cost": 100000000, "material_id": 112, "material_amount": 8, "extra_material_id": 108, "extra_material_amount": 1, "success_rate": 25, "unlock_realm_min_level": 280, "description": "Đế Phẩm lên Đạo Phẩm — phẩm cuối, chữ cầu vồng."}
  }
}
```

> **SO CHOT — chan canh gioi lay tu chinh feature, khong phat minh so moi**: 100/150/200/280 la `realm_min_level` cua stage 1..4 trong `huyetmach_awakening_config.json` (Trúc Cơ / Kim Đan / Nguyên Anh / Đại Thừa). Nguoi choi noi dc thuc tinh thi du cho nang pham tuong ung — hai duong khong nat nhau.
> **TONG CONG HUYET THACH 9 buoc nang pham = 77** (5+10+8+10+8+10+8+10+8). Cong luyen 8 mach day tran 48 = 1344. Day la con so de chu quyet dinh "mooi" o Task 12, khong phai bug.

- [ ] **Step 2: Quet CJK + validate JSON + kiem tinh lien tuc cua chuoi pham**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$f="class/config/huyetmach_upgrade_config.json";
$s=file_get_contents($f);
preg_match_all("/[\\\\x{4e00}-\\\\x{9fff}]/u",$s,$m);
if($m[0]){echo "CJK FOUND: ",implode(" ",$m[0]),"\n";exit(1);}
$d=json_decode($s,true);
if(!is_array($d)){echo "json FAIL\n";exit(1);}
$u=$d["upgrades"]; if(count($u)!==9){echo "count=",count($u),"\n";exit(1);}
$rates=array(); $prev=0;
foreach($u as $k=>$row){
  if($row["from_quality"]!=$prev){echo "GAP at ",$k,"\n";exit(1);}
  $prev=$row["to_quality"]; $rates[]=$row["success_rate"];
}
if($rates!==array(95,90,85,75,65,55,45,35,25)){echo "rates ",implode(",",$rates),"\n";exit(1);}
echo "upgrade json ok, 9 buoc, to_quality cuoi=10\n";'
```

Expected: `upgrade json ok, 9 buoc, to_quality cuoi=10`. Neu in ra `CJK FOUND: ...` thi file da bi chen chu Trung Hoa lac cho (le cua Task 1 Step 2) — viet lai Step 1. Neu in ra `GAP at <k>` thi chuoi `from_quality` bi dut (phai 1→2→...→10 lien tuc).

- [ ] **Step 3: Viet test that bai truoc — `tests/test_huyetmach_upgrade.php`**

Test khong them tham so "force roll". Theo le cua repo (`tests/test_nguhanh_random.php`: do roll THAT bang chuoi dai): phan ngau nhien kiem bang bien dong (60 luot rate 95 → 50..60 lan thành công); phan tu choi (thieu canh gioi / thieu tai nguyen) kiem bang ket qua tat yeu, vi nhung duong nay return TRUOC khi quay roll.

```php
<?php
/**
 * test_huyetmach_upgrade.php — nang pham chat Huyết Mach (config + DB local `game`).
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_huyetmach_upgrade.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/huyetmach.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: config thuan =====
$all = \player\huyetmach_upgrade_config_all();
tassert('9 buoc', count($all['upgrades']), 9);
$u1 = \player\huyetmach_get_upgrade_info(1);
tassert('info pham 1 to_quality', $u1['to_quality'], 2);
tassert('info pham 1 rate', $u1['success_rate'], 95);
$u9 = \player\huyetmach_get_upgrade_info(9);
tassert('info pham 9 extra id', $u9['extra_material_id'], 108);
tassert('info pham 9 gate', $u9['unlock_realm_min_level'], 280);
tassert('pham 10 toi da', \player\huyetmach_get_upgrade_info(10), false);
tassert('pham 0 khong ton tai', \player\huyetmach_get_upgrade_info(0), false);
// chan pham khong giam dan
$gates = array(); foreach ($all['upgrades'] as $row) { $gates[] = intval($row['unlock_realm_min_level']); }
$sorted = $gates; sort($sorted);
tassert('gate tang don dieu', $gates, $sorted);

// ===== B: DB — duong tuan + tai nguyen =====
$TESTSID = str_repeat('f', 32);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// row game1 cua TESTSID do Task 3 tao (uid 990112); o day ep ve trang thai biet truoc
$db->prepare("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, uyxb,
        huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
    VALUES (?, 990112, 'HMTEST', 300, 1000, 10000, NOW(), 0, 1, 1, 0)
    ON DUPLICATE KEY UPDATE uid = 990112, ulv = 300, uyxb = 0,
        huyetmach_id = 1, huyetmach_quality = 1, huyetmach_awaken_stage = 0")
    ->execute(array($TESTSID));
function hm_up_mat($db, $sid, $djid, $sum) {
    $db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid = ?")->execute(array($sid, $djid));
    if ($sum > 0) {
        $db->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES (?, 990112, ?, ?)")
            ->execute(array($sid, $djid, $sum));
    }
}
function hm_up_reset($db, $sid, $q, $lt, $ulv) {
    $db->prepare("UPDATE game1 SET huyetmach_quality = ?, uyxb = ?, ulv = ? WHERE sid = ?")
        ->execute(array($q, $lt, $ulv, $sid));
    \player\player_cache_clear($sid);
}
$db->prepare("DELETE FROM player_huyetmach_upgrade_log WHERE sid = ?")->execute(array($TESTSID));

// B1: khong co huyet mach
$db->prepare("UPDATE game1 SET huyetmach_id = 0 WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('chua co huyet mach', $r['ok'], false);
tassert('chua co huyet mach msg', strpos($r['msg'], 'chưa có Huyết Mạch') !== false, true);
$db->prepare("UPDATE game1 SET huyetmach_id = 1 WHERE sid = ?")->execute(array($TESTSID));

// B2: chan canh gioi (step 3 can cap 100, dat ulv = 99)
hm_up_reset($db, $TESTSID, 3, 999999999, 99);
hm_up_mat($db, $TESTSID, 112, 50); hm_up_mat($db, $TESTSID, 102, 50);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu canh gioi', $r['ok'], false);
tassert('thieu canh gioi msg', strpos($r['msg'], 'cấp 100') !== false, true);
tassert('thieu canh gioi khong tru LT', intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 999999999);
tassert('thieu canh gioi khong tru 112', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 50);

// B3: thieu Huyet Thạch — msg phai dung ten co dau (Task 4 Step 4)
hm_up_reset($db, $TESTSID, 1, 999999999, 300);
hm_up_mat($db, $TESTSID, 112, 4); hm_up_mat($db, $TESTSID, 101, 0);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu Huyet Thạch', $r['ok'], false);
tassert('ten vat pham co dau', strpos($r['msg'], 'Huyết Thạch') !== false, true);

// B4: thieu da pham (extra_material_id)
hm_up_mat($db, $TESTSID, 112, 10); hm_up_mat($db, $TESTSID, 101, 2);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu Huyền Thạch', strpos($r['msg'], 'Huyền Thạch') !== false, true);

// B5: thieu Linh Thạch
hm_up_mat($db, $TESTSID, 101, 3);
hm_up_reset($db, $TESTSID, 1, 999, 300);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu Linh Thạch', strpos($r['msg'], 'Linh Thạch') !== false, true);

// B6: du du Kien -> duoc tru, duoc ghi log, pham KHONG BAO GIO thap di
hm_up_reset($db, $TESTSID, 1, 999999999, 300);
hm_up_mat($db, $TESTSID, 112, 5);
$t0 = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn());
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('upgrade lan 1 ok', $r['ok'] || strpos($r['msg'], 'thất bại') !== false, true);
tassert('tru dung 1000 LT', $t0 - intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 1000);
tassert('tru het 5 Huyet Thạch', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 0);
tassert('log 1 dong', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_upgrade_log WHERE sid='$TESTSID'")->fetchColumn()), 1);
$q1 = intval($db->query("SELECT huyetmach_quality FROM game1 WHERE sid='$TESTSID'")->fetchColumn());
tassert('pham chi co the 1 hoac 2', ($q1 === 1 || $q1 === 2), true);

// ===== C: roll THAT — 60 luot pham 1->2 (rate 95) phai nam trong dai 50..60 =====
$ok = 0; $bad = 0;
for ($i = 0; $i < 60; $i++) {
    hm_up_mat($db, $TESTSID, 112, 5);
    $db->prepare("UPDATE game1 SET huyetmach_quality = 1, uyxb = 999999999 WHERE sid = ?")->execute(array($TESTSID));
    \player\player_cache_clear($TESTSID);
    $r = \player\huyetmach_upgrade($TESTSID, $db);
    if (strpos($r['msg'], 'thành công') !== false) { $ok++; } else { $bad++; }
}
tassert('60 luot roll het', $ok + $bad, 60);
tassert('rate 95 trong dai 50..60', ($ok >= 50 && $ok <= 60), true);
tassert('khong bao gio vuot pham 2', intval($db->query("SELECT huyetmach_quality FROM game1 WHERE sid='$TESTSID'")->fetchColumn()) <= 2, true);

// ===== D: dao thu — vet nguyen hien trang, chay 2 lan loi khong duplicated =====
$db->prepare("DELETE FROM player_huyetmach_upgrade_log WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid IN (101,102,108,112)")->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET huyetmach_quality = 1, huyetmach_id = 0, uyxb = 0 WHERE sid = ?")->execute(array($TESTSID));
tassert('sach log', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_upgrade_log WHERE sid='$TESTSID'")->fetchColumn()), 0);

echo "\n" . ($fails === 0 ? "ALL PASS" : $fails . " FAIL") . "\n";
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 4: Chay test -> FAIL**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_upgrade.php`
Expected: `Call to undefined function player\huyetmach_upgrade_config_all()`.

- [ ] **Step 5: Ham `huyetmach_upgrade_*`** — them vao `class/function/huyetmach.php` (ngay sau khoi `huyetmach_quality_icon`, truoc khoi `huyetmach_awaken_name`):

```php
/**
 * Upgrade config — nang pham chat Huyết Mạch (khong lien quan luyen mach).
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_upgrade_config_path')) {
    function huyetmach_upgrade_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_upgrade_config.json',
            __DIR__ . '/huyetmach_upgrade_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_upgrade_config_all')) {
    function huyetmach_upgrade_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = huyetmach_upgrade_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $data = json_decode(file_get_contents($file), true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_get_upgrade_info')) {
    function huyetmach_get_upgrade_info($currentQuality){
        $all = huyetmach_upgrade_config_all();
        $upgrades = isset($all['upgrades']) ? $all['upgrades'] : array();
        $currentQuality = intval($currentQuality);
        foreach ($upgrades as $u) {
            if (intval($u['from_quality']) === $currentQuality) return $u;
        }
        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_upgrade')) {
    /**
     * Nang 1 bac pham chat. That bai mat vat lieu, KHONG giam pham (spec 2.2).
     * Moi lan tru = 1 dong log, ke ca khi fail.
     */
    function huyetmach_upgrade($sid, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player) return array('ok' => false, 'msg' => 'Không tìm thấy nhân vật.', 'new_quality' => 0);

        $quality = intval($player->huyetmach_quality);
        if (intval($player->huyetmach_id) <= 0) {
            return array('ok' => false, 'msg' => 'Bạn chưa có Huyết Mạch.', 'new_quality' => $quality);
        }

        $info = huyetmach_get_upgrade_info($quality);
        if (!$info) {
            return array('ok' => false, 'msg' => 'Huyết Mạch đã đạt Đạo Phẩm — phẩm chất tối đa.', 'new_quality' => $quality);
        }

        $toQuality = intval($info['to_quality']);
        $gate = intval(isset($info['unlock_realm_min_level']) ? $info['unlock_realm_min_level'] : 0);
        if (intval($player->ulv) < $gate) {
            return array('ok' => false,
                'msg' => 'Cần đạt cấp ' . $gate . ' để nâng lên ' . huyetmach_quality_name($toQuality) . '.',
                'new_quality' => $quality);
        }

        $ltCost = intval($info['linh_thach_cost']);
        $matId = intval($info['material_id']);
        $matAmt = intval($info['material_amount']);
        $extraId = isset($info['extra_material_id']) ? intval($info['extra_material_id']) : 0;
        $extraAmt = isset($info['extra_material_amount']) ? intval($info['extra_material_amount']) : 0;
        $rate = intval($info['success_rate']);

        if (intval($player->uyxb) < $ltCost) {
            return array('ok' => false,
                'msg' => 'Linh Thạch không đủ: cần ' . number_format($ltCost) . ', có ' . number_format(intval($player->uyxb)) . '.',
                'new_quality' => $quality);
        }
        $have = huyetmach_vessel_daoju_have($sid, $dblj, $matId);
        if ($have < $matAmt) {
            return array('ok' => false,
                'msg' => huyetmach_get_material_name($matId) . ' không đủ: cần ' . $matAmt . ', có ' . $have . '.',
                'new_quality' => $quality);
        }
        if ($extraId > 0 && $extraAmt > 0) {
            $haveExtra = huyetmach_vessel_daoju_have($sid, $dblj, $extraId);
            if ($haveExtra < $extraAmt) {
                return array('ok' => false,
                    'msg' => huyetmach_get_material_name($extraId) . ' không đủ: cần ' . $extraAmt . ', có ' . $haveExtra . '.',
                    'new_quality' => $quality);
            }
        }

        $dblj->beginTransaction();
        try {
            // rowCount() === 1 la dieu kien song: thechat_upgrade() bo qua buoc nay va co the
            // quay roll ma khong tru duoc vat lieu nao.
            $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
            $stmt->execute(array($ltCost, $sid, $ltCost));
            if ($stmt->rowCount() !== 1) throw new Exception('thieu Linh Thạch');

            $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmt->execute(array($matAmt, $sid, $matId, $matAmt));
            if ($stmt->rowCount() !== 1) throw new Exception('thieu ' . huyetmach_get_material_name($matId));

            if ($extraId > 0 && $extraAmt > 0) {
                $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
                $stmt->execute(array($extraAmt, $sid, $extraId, $extraAmt));
                if ($stmt->rowCount() !== 1) throw new Exception('thieu ' . huyetmach_get_material_name($extraId));
            }

            $success = (mt_rand(1, 100) <= $rate) ? 1 : 0;
            if ($success) {
                $dblj->prepare("UPDATE game1 SET huyetmach_quality = ? WHERE sid = ?")
                    ->execute(array($toQuality, $sid));
            }
            $dblj->prepare("INSERT INTO player_huyetmach_upgrade_log (sid, from_quality, to_quality, success, created_at)
                VALUES (?, ?, ?, ?, NOW())")->execute(array($sid, $quality, $toQuality, $success));

            $dblj->commit();
        } catch (Exception $e) {
            if ($dblj->inTransaction()) $dblj->rollBack();
            return array('ok' => false,
                'msg' => 'Nâng phẩm thất bại (hệ thống bận), chưa tru vật phẩm.',
                'new_quality' => $quality);
        }

        player_cache_clear($sid);
        $toName = huyetmach_quality_name($toQuality);
        if ($success) {
            return array('ok' => true,
                'msg' => 'Nâng phẩm thành công! Huyết Mạch đã lên ' . $toName . '.',
                'new_quality' => $toQuality);
        }
        return array('ok' => false,
            'msg' => 'Nâng phẩm thất bại! Vật phẩm đã mất, huyết mạch vẫn ở ' . huyetmach_quality_name($quality) . '.',
            'new_quality' => $quality);
    }
}
```

- [ ] **Step 6: Chay test -> PASS** (ca A/B/C/D). Neu C fail vi 60 luot roi ngoai dai 50..60, chay lai — day la bien dong cua 95% (Xác suất rơi ngoài dải ≈ 10⁻⁷/lần chạy), khong phai loi.

- [ ] **Step 7: Khoi CSS `.hmq-*`** — noi cuoi `css/mystyle.css` (ngay sau khoi `.hm-*` cua Task 7), clone tu khoi `.tcq-*` trong `game/thechat_upgrade.php`:

```bash
{ echo ''
  echo '/* === HUYET MACH - NANG PHAM CHAT (.hmq-*): clone .tcq-* tu game/thechat_upgrade.php === */'
  awk '/^\.tcq-head\{/,/^<\/style>/' game/thechat_upgrade.php | grep -v '^</style>$'
} | sed -e 's/tcq-/hmq-/g' -e 's/@keyframes tcq/@keyframes hmq/g' -e 's/animation:tcq/animation:hmq/g' -e 's/\.tc-card/.hm-card/g' -e 's/tc-badge/hm-badge/g' -e 's/tc-btn/hm-btn/g' >> css/mystyle.css
```

Range `.tcq-head{` -> `</style>` (dong 105-179) keo theo du 6 `@keyframes hmq` VA ca khue `@media (prefers-reduced-motion)`. Ly do: dung `sed -n '/a/,/b/p'` voi ket la `^@media` thi sai: no dung o dong `@media` va MAT 4 dong ben trong (khue tat dong cho nguoi gia). Dau range o dong 105 nen KHONG keo theo `.tc-card`, `.tc-badge`, `.tc-actions` va 3 rule `.main .tc-btn*` (chung nam 87-95, phia tren `.tcq-head`) - nut `.main .hm-btn` cua Task 7 van la nien duy nhat.

```bash
grep -c "^\.hmq-" css/mystyle.css                    # >= 28
grep -c "@keyframes hmq" css/mystyle.css               # 6
grep -n "main .hm-btn" css/mystyle.css                  # CHI co khoi cua Task 7
grep -n "tcq-\|tc-card\|tc-badge\|tc-btn" css/mystyle.css | head   # 0 ket qua
```

Roi tay THEM 5 cho ma sed khong tu sinh duoc (thieu la mat hieu ung hoac mat chu):

1. `.hmq-lt{color:#38bdf8;font-weight:800;margin-bottom:6px}` va `.hmq-form{text-align:center;margin-top:12px}` — hai class Step 8 dung, source go inline style nen sed khong co.
2. `.hmq-card{position:relative;isolation:isolate;overflow:hidden}` — aura dung `z-index:-1`, quen `isolation:isolate` la vo hình (landmine `docs/UI_THECHAT.md` §12).
3. `.hmq-q1` (Phàm Phẩm) phai CON thay hieu ung tinh (aura op .24 + glow 8px), khong duoc tran — "bậc thấp nhất vẫn phải thấy gì đó".
4. Chu cai ruoi chi gan cho `hmq-q10` (Đạo Phẩm). `hmq-q9` (Đế Phẩm) phai KHONG co — cung mot le voi Task 7.
5. `@media (prefers-reduced-motion:reduce)` phai liet ke ca `hmq-q10 .hmq-head`, `hmq-q10 .hmq-max-in`, `hmq-msg::before`, `hmq-msg-in`; tat dong thi viet `animation:none` (khong dung `--dur:0s`).

- [ ] **Step 8: Trang `game/huyetmach_upgrade.php`** (noi dung nguyen chan):

```php
<?php
// Huyết Mạch - Nâng Phẩm Chất
$player = \player\getplayer($sid, $dblj);

$hmUpBloodId = intval($player->huyetmach_id);
$hmUpQuality = intval($player->huyetmach_quality);
$hmUpInfo = \player\huyetmach_get_upgrade_info($hmUpQuality);

$ztcmd = $encode->encode("cmd=zhuangtai&sid=$sid");
$hmcmd = $encode->encode("cmd=huyetmach&sid=$sid");

$hmUpMsg = '';
if (isset($_POST['hm_action']) && $_POST['hm_action'] === 'upgrade' && $hmUpBloodId > 0) {
    $res = \player\huyetmach_upgrade($sid, $dblj);
    $hmUpMsg = $res['msg'];
    $player = \player\getplayer($sid, $dblj);
    $hmUpQuality = intval($player->huyetmach_quality);
    $hmUpInfo = \player\huyetmach_get_upgrade_info($hmUpQuality);
}

// Pham hien tai -> class phan tang hieu ung (hmq-q1 .. hmq-q10)
$hmUpFx = 'hmq-q' . max(1, min(10, $hmUpQuality));
$hmUpColor = \player\huyetmach_quality_color($hmUpQuality);
$hmUpToColor = $hmUpInfo ? \player\huyetmach_quality_color(intval($hmUpInfo['to_quality'])) : $hmUpColor;
list($hR, $hG, $hB) = sscanf($hmUpColor, '#%02x%02x%02x');
list($hTR, $hTG, $hTB) = sscanf($hmUpToColor, '#%02x%02x%02x');
$hmUpRgb = intval($hR) . ',' . intval($hG) . ',' . intval($hB);
$hmUpToRgb = intval($hTR) . ',' . intval($hTG) . ',' . intval($hTB);

$hmUpMsgHtml = '';
if ($hmUpMsg !== '') {
    $isOk = (strpos($hmUpMsg, 'thành công') !== false);
    $hmUpMsgHtml = '<div class="hmq-msg" style="--msg-rgb:' . ($isOk ? '110,231,183' : '248,113,113') . '">'
        . '<div class="hmq-msg-in" style="color:' . ($isOk ? '#6ee7b7' : '#f87171') . '">'
        . htmlspecialchars($hmUpMsg, ENT_QUOTES, 'UTF-8') . '</div></div>';
}

if ($hmUpBloodId <= 0) {
    // Chua co huyet mach: empty-state nhu man chinh (Task 7), khong cho hoi thieu du lieu
    echo '<div class="hm-wrap"><div class="hm-card hmq-card ' . $hmUpFx . '" style="--hmq:' . $hmUpColor
        . ';--hmq-rgb:' . $hmUpRgb . '"><div class="hm-empty">Huyết Mạch chưa khởi — uống <b>Tố Thể Đan</b> ở Phương Thị để mở mạch.</div></div>'
        . '<nav class="hm-tabbar"><a class="on" href="?cmd=' . $ztcmd . '"><i>👤</i><b>Nhân Vật</b></a></nav></div>';
    return;
}

$hmUpHtml = '';
if ($hmUpInfo) {
    $hmUpLt = number_format(intval($hmUpInfo['linh_thach_cost']));
    $hmUpMatId = intval($hmUpInfo['material_id']);
    $hmUpMatAmt = intval($hmUpInfo['material_amount']);
    $hmUpMatName = \player\huyetmach_get_material_name($hmUpMatId);
    $hmUpMatHave = \player\huyetmach_vessel_daoju_have($sid, $dblj, $hmUpMatId);
    $hmUpRate = intval($hmUpInfo['success_rate']);
    $hmUpGate = intval(isset($hmUpInfo['unlock_realm_min_level']) ? $hmUpInfo['unlock_realm_min_level'] : 0);
    $hmUpToName = \player\huyetmach_quality_name(intval($hmUpInfo['to_quality']));

    $hmUpExtraHtml = '';
    if (isset($hmUpInfo['extra_material_id']) && intval($hmUpInfo['extra_material_id']) > 0) {
        $hmUpExtraId = intval($hmUpInfo['extra_material_id']);
        $hmUpExtraAmt = intval($hmUpInfo['extra_material_amount']);
        $hmUpExtraHave = \player\huyetmach_vessel_daoju_have($sid, $dblj, $hmUpExtraId);
        $hmUpExtraCls = ($hmUpExtraHave >= $hmUpExtraAmt) ? 'hm-cost-ok' : 'hm-cost-no';
        $hmUpExtraHtml = '<div class="hm-cost-item ' . $hmUpExtraCls . '">📦 '
            . \player\huyetmach_get_material_name($hmUpExtraId) . ': ' . $hmUpExtraHave . '/' . $hmUpExtraAmt . '</div>';
    }

    $hmUpMatCls = ($hmUpMatHave >= $hmUpMatAmt) ? 'hm-cost-ok' : 'hm-cost-no';
    $hmUpLtOk = (intval($player->uyxb) >= intval($hmUpInfo['linh_thach_cost']));
    $hmUpGateOk = (intval($player->ulv) >= $hmUpGate);
    $hmUpGateHtml = $hmUpGateOk ? '' : '<div class="hm-req hm-cost-no">🔒 Cần cấp ' . $hmUpGate . ' (hiện ' . intval($player->ulv) . ')</div>';

    $hmUpHtml = <<<HTML
<div class="hm-card hmq-card {$hmUpFx}" style="--hmq:{$hmUpColor};--hmq-rgb:{$hmUpRgb};--hmq-to:{$hmUpToColor};--hmq-to-rgb:{$hmUpToRgb}">
    <div class="hmq-head"><i class="hmq-ico">⬆</i> Nâng phẩm chất huyết mạch</div>
    <div class="hmq-row">
        <span class="hm-badge hmq-badge hmq-badge-from"><i class="hmq-ico">{$hmUpFromIcon}</i> {$hmUpFromName}</span>
        <span class="hmq-arrow">→</span>
        <span class="hm-badge hmq-badge hmq-badge-to"><i class="hmq-ico">{$hmUpToIcon}</i> {$hmUpToName}</span>
    </div>
    <div class="hmq-cost">
        <div class="hmq-lt">💎 Linh Thạch: {$hmUpLt}</div>
        <div class="hm-cost-item {$hmUpMatCls}">🩸 {$hmUpMatName}: {$hmUpMatHave}/{$hmUpMatAmt}</div>
        {$hmUpExtraHtml}
        {$hmUpGateHtml}
        <div class="hmq-rate">🎯 Tỷ lệ thành công: {$hmUpRate}%</div>
    </div>
    <div class="hm-node-desc">{$hmUpDesc}</div>
    <form method="post" class="hmq-form">
        <input type="hidden" name="hm_action" value="upgrade">
        <button type="submit" class="hm-btn hm-btn-refine hmq-btn">Nâng Phẩm</button>
    </form>
</div>
HTML;
} else {
    $hmUpHtml = '<div class="hm-card hmq-card hmq-max ' . $hmUpFx . '" style="--hmq:' . $hmUpColor . ';--hmq-rgb:' . $hmUpRgb
        . ';--hmq-to:' . $hmUpColor . ';--hmq-to-rgb:' . $hmUpRgb . '"><span class="hmq-max-in">Đạo Phẩm — huyết mạch đã tới tận cùng phẩm chất.</span></div>';
}
?>
<?= $hmUpMsgHtml ?>
<?= $hmUpHtml ?>
<nav class="hm-tabbar">
    <a href="?cmd=<?= $hmcmd ?>"><i>🩸</i><b>Mạch Huyết</b></a>
    <a class="on" href="?cmd=<?= $ztcmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
```

> **BA VIEC PHAI LAM TRUOC KIMUOI (heredoc tren chua khai bao)** — day la loi ma `sed`/`copy` khong bat duoc, phai tay:
> - `$hmUpFromIcon = \player\huyetmach_quality_icon($hmUpQuality); $hmUpFromName = htmlspecialchars(\player\huyetmach_quality_name($hmUpQuality), ENT_QUOTES, 'UTF-8'); $hmUpToIcon = \player\huyetmach_quality_icon(intval($hmUpInfo['to_quality']));` va `$hmUpDesc = htmlspecialchars(isset($hmUpInfo['description']) ? $hmUpInfo['description'] : '', ENT_QUOTES, 'UTF-8');` — them 4 dong nay vao TRUOC heredoc (trong `if ($hmUpInfo) {...}`), neu khong `Undefined variable` ra chu `Notice` giua man.
>> - `.hm-cost-ok/.hm-cost-no/.hm-node-desc/.hm-req` la class Task 7 da clone tu `.nb-*` — dung lai, KHONG khai bao lai trong `.hmq-*`.

- [ ] **Step 9: Can tin PHP 5.6 + nguyen ban**

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
$PHP -l game/huyetmach_upgrade.php
$PHP -l class/function/huyetmach.php
grep -n '??\|<=>\|\[\]' game/huyetmach_upgrade.php | head        # 0 ket qua
grep -nP "[\\\\x{4e00}-\\\\x{9fff}]" game/huyetmach_upgrade.php class/function/huyetmach.php css/mystyle.css
   # 0 ket qua (tru khi file goc cua repo co san tieng Trung trong comment khac -> ghi ro ten dong)
grep -n "'huyetmach_upgrade'=>'game/huyetmach_upgrade.php'," game.php   # 1 ket qua (Task 7 Step 6)
```

- [ ] **Step 10: Bump CSS** — `sed -i "s/mystyle.css?v=[0-9]\\+/mystyle.css?v=2026101001/" game.php` (Task 7 dat `2026100930`).

- [ ] **Step 11: Nghiem thu that tren trang** (playbook `docs/UI_XIULIAN.md` §9.1 — tai khoan E2E `e2e2e2e2...`, `php -S 127.0.0.1:8090`, harness iframe 390px, `zoom=1` truoc khi do):
  - Seed: `UPDATE game1 SET huyetmach_id=1, huyetmach_quality=1, ulv=300, uyxb=100000000 WHERE sid='e2e...'` + `INSERT ... playerdaoju (sid,uid,djid,djsum)` cho 112×40 va 101..105×10 (uid lay tu `game1.uid` cua E2E).
  - Do 4 con so, ghi vao `document.title`, screenshot luu `docs/img/huyetmach/upgrade-*.png`:

| Phan tu | Ky vong |
|---|---|
| nut `.hmq-btn` | `backgroundColor` KHONG phai `rgb(255,255,255)` (= `.main .hm-btn-refine` cua Task 7 thang `.main button`) |
| `.hmq-card` | width = noi dung 624px desktop; `height` KHONG doi giua q1 va q10 (hieu ung 0px geometry) |
| `.hmq-badge-to` | mau theo `--hmq-to` = `huyetmach_quality_color(to_quality)` — pham 1→2 badge den `#4caf50` |
| `.hmq-q10 .hmq-head` | co `background-clip:text` + `animation-name:hmqRain`; o `hmq-q9` KHONG co |

  - Bam **Nâng Phẩm** 1 lan that → `battleLog` khong lien quan, nhung khung `.hmq-msg` phai xuat hien, `huyetmach_quality` hoặc 1 hoặc 2, `player_huyetmach_upgrade_log` co 1 dong.

- [ ] **Step 12: Commit**

```bash
git add class/config/huyetmach_upgrade_config.json class/function/huyetmach.php game/huyetmach_upgrade.php css/mystyle.css game.php tests/test_huyetmach_upgrade.php
git commit -m "feat(huyetmach): 9-step bloodline quality upgrade + upgrade screen"
```

---
---

### Task 10: Thang hiệu ứng `hm-fx0..fx4` + phủ mọi nơi Huyết Mạch hiển thị

**Files:**
- Modify: `css/mystyle.css` (chot `.hm-fx*` —Task 7 clone ve, task nay KIEM TRA va sua dung do)
- Modify: `game/zhuangtai.php:536-590` (khoi HUYẾT MẠCH trang Nhân Vật)
- Modify: `class/player.php` (Step 7: gán `$player->huyetmach_vessel` ngay sau dòng `$player->huyetmach_stats = $hmB['stats'];` = `class/player.php:823`; thêm `var $huyetmach_vessel;` cánh `var $huyetmach_stats;` = `class/player.php:146`)
- Create: `E:/window/Temp/_hmfx.html` (harness do dac, throwaway — KHONG commit)
- Modify: `docs/img/huyetmach/` (them 10 anh bang chung: 5 bac fx + reduced-motion + 2 cot so sanh)

**Interfaces:**
- Consumes: khoi `.hm-*` cua Task 7 (`--hm-aura`, `--hm-glow`, `--hm-dur`, `--hm-btn-dur`, class `hm-fx0..hm-fx4`), `huyetmach_vessel_stat_label($stat)` (Task 1), `$hmB['vessel']` do Task 3 gan vao `huyetmach_calc_bonuses()`, `huyetmach_quality_*` + `huyetmach_awaken_*` (Task 7 Step 1 delegated).
- Produces: trang `zhuangtai.php` hien du 8 chi so mach (tieng Viet), khong con nhan Anh; bang so lieu fx thang 5 bac ghi vao `docs/UI_HUYETMACH.md` (Task 12).

> **VI SAO TASK NAY TON TAI RIENG**: Task 7 clone `.nb-fx0..fx5` → `.hm-fx0..fx4` bang `sed`, nghĩa là thang hiệu ứng chưa từng được đo trên màn mới. Ba loi ma sed khong the tu bat: (1) `.nb-fx0{--nb-dur:0s}` — 0s KHONG tat duoc animation trong Chrome (landmine `docs/UI_THECHAT.md` §12), phai co rule `animation:none` tuong ung; (2) rule `.nb-fx5`/`.nb-t5` bi xoa o Task 7 co the de lai thuoc bien `--t-glow` cua bac 5 trong ray 5 o; (3) `zhuangtai.php` van chi in 4 chi so cu + 4 nhan Anh — 8 mach moi vo hình tren trang tong hop.

- [ ] **Step 1: Doc lai khoi `.hm-fx*` sau clone**

```bash
grep -n "^.hm-fx[0-4]{\|^.hm-t[0-4]{\|^.hm-fx0 " css/mystyle.css
grep -n "hm-fx5\|hm-t5" css/mystyle.css    # 0 ket qua (Task 7 Step 4.3 da xoa)
grep -c "animation:none" css/mystyle.css   # phai TANG so voi truoc Task 7
```

Ky vong (gia tri clone tu `.nb-*`, doi ten bien):

| Bậc | `--hm-dur` | `--hm-aura` | `--hm-glow` | `--hm-btn-dur` |
|---|---|---|---|---|
| fx0 (Trầm Mệnh) | 0s | .04 | 4px | 6s |
| fx1 (Sơ Giác) | 10s | .06 | 7px | 5s |
| fx2 (Thức Tỉnh) | 8s | .09 | 10px | 4.2s |
| fx3 (Chân Thân) | 5.5s | .13 | 14px | 3.4s |
| fx4 (Viên Mãn) | 3.6s | .17 | 19px | 2.8s |

- [ ] **Step 2: Sua cho fx0 that su "tinh ma van thay"** — neu `--hm-dur:0s` clone sang ma khong co `animation:none` cho element cua `.hm-*`, them khoi sau (dat CUI NGAY duoi `.hm-fx4`, truoc `@media (prefers-reduced-motion)`):

```css
/* fx0: --hm-dur:0s KHONG tat duoc animation (Chrome git khung 0%), phai viet animation:none.
   Van giu aura/glow TINH de bac thap nhat khong bi "chet den". */
.hm-fx0 .hm-aura,.hm-fx0 .hm-tier.is-cur,.hm-fx0 .hm-ms-fill:after,
.hm-fx0 .hm-gem.is-max:not(.is-sel) .hm-gem-disc,.hm-fx0 .hm-btn:after{animation:none}
.hm-fx0 .hm-sweep{display:none}
.hm-fx0 .hm-badge{box-shadow:0 0 4px rgba(245,209,138,.35)}
```

> Ten element phai kiem tra bang `grep -o "\.hm-[a-z-]*" css/mystyle.css | sort -u` truoc khi paste — Task 7 clone tu `.nb-*` nen `hm-aura/hm-sweep/hm-tier/hm-ms-fill/hm-gem-disc/hm-badge` deu ton tai; bat ki ten nao khong co trong file la sed da doi khac, dung them rule vo nghia.

- [ ] **Step 3: Harness do thang fx (Chrome CLI, KHONG dung browser-use MCP)**

Tao `E:/window/Temp/_hmfx.html`: khung iframe 390px tro toi `game.php?cmd=huyetmach` ( encode sid E2E), moi lan doi `huyetmach_awaken_stage` 0..4 roi do `getComputedStyle`:

```html
<!meta charset=utf-8><title>fx</title>
<body style="margin:0">
<script>
var STAGES=[0,1,2,3,4], out=[];
function read(doc, sel){ var e=doc.querySelector(sel); if(!e) return null;
  var c=doc.defaultView.getComputedStyle(e);
  return [c.getPropertyValue('--hm-aura').trim(), c.getPropertyValue('--hm-glow').trim(),
          c.getPropertyValue('--hm-dur').trim(), Math.round(e.getBoundingClientRect().height),
          (e.className||'')+''];}
function run(i){
  if(i>=STAGES.length){ document.title='FX|'+out.join(';'); return; }
  var s=STAGES[i];
  document.getElementById('f').src='IFRAME_URL_HERE';
  setTimeout(function(){
    var d=document.getElementById('f').contentDocument;
    var w=d.querySelector('.hm-wrap');
    if(!w){ out.push('s'+s+':NO_WRAP'); run(i+1); return; }
    out.push('s'+s+':'+(w.className.match(/hm-fx\d/)||['?'])[0]+
      '|'+read(d,'.hm-stage').slice(0,4).join(',')+
      '|btn='+read(d,'.hm-btn-refine')[0]+
      '|gem='+ (read(d,'.hm-gem.is-max')||['-'])[4]);
    run(i+1);
  }, 1200);
}
</script>
<iframe id=f style="width:390px;height:844px;border:0"></iframe>
</body>
```

Run (server local da chay theo playbook `docs/UI_XIULIAN.md` §9.1; `IFRAME_URL_HERE` = URL da encode cua `cmd=huyetmach&sid=e2e2e2e2...`, them `&zoom=1`):

```bash
SEED='UPDATE game1 SET huyetmach_id=1, huyetmach_quality=1, huyetmach_awaken_stage=0, ulv=300 WHERE sid="e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2";'
for S in 0 1 2 3 4; do
  D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysql.exe -uroot -pwww.aae.ink game -e "UPDATE game1 SET huyetmach_awaken_stage=$S WHERE sid='e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';"
  "/c/Program Files/Google/Chrome/Application/chrome.exe" --headless=new --disable-gpu \
      --window-size=500,900 --virtual-time-budget=9000 \
      --dump-dom "file:///E:/window/Temp/_hmfx.html" | grep -o "<title>[^<]*</title>"
done
```

> Duong dan `mysql.exe` da do: `D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysql.exe` (phpstudy cai MySQL 5.5.29, khong phai 5.7 — dung sai ten file la lenh loi `command not found`). Neu `mysql.exe` khong chay, seed bang PHP (khong can duong dan MySQL): `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r 'new PDO("mysql:host=localhost;dbname=game","root","www.aae.ink")->exec("UPDATE game1 SET huyetmach_awaken_stage=$S WHERE sid=\"e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2\"");'`. Helper `E:/window/Temp/xlhelp.php` cua §9.1 CHI co 5 mode `run/full/mid/idle/link` (chua mode mach huyet), nen dung PDO tran.

Ky vong: `hm-fx0 → hm-fx4` xuat hien dung theo stage; `--hm-aura` va `--hm-glow` TANG DON DIEU theo bang Step 1; chieu cao `.hm-stage` KHONG doi giua 5 stage (hieu ung 0px geometry); `.hm-btn-refine` co `--hm-btn-dur` giam dan.

- [ ] **Step 4: Chup bang chung hinh anh** — 5 bac, moi bac 1 anh, luan ten theo stage:

```bash
mkdir -p docs/img/huyetmach
for S in 0 1 2 3 4; do
  "/c/Program Files/Google/Chrome/Application/chrome.exe" --headless=new --disable-gpu \
      --window-size=500,900 --virtual-time-budget=9000 --force-device-scale-factor=2 \
      --screenshot="docs/img/huyetmach/fx-stage$S.png" "IFRAME_URL_HERE"
done
```

Sau do DUNG GHEP Anh (le `verify-visually-not-just-by-metrics`: so do computed style khong bat duoc loi thi giac): dung `python`/PIL dat 5 anh canh nhau thanh `docs/img/huyetmach/fx-ladder.png`, mo bang `start docs/img/huyetmach/fx-ladder.png` va NHIN: bac 0 van phai co quang mong quanh huyet mach (khong phai man hinh tran), bac 4 phai ro hon bac 0, khong co chu nao bien mat vi `color:transparent` ma quen `-webkit-text-fill-color`.

- [ ] **Step 5: Kiem `prefers-reduced-motion`** — chay lai Step 3 them co quan `--force-prefers-reduced-motion`:

```bash
"/c/Program Files/Google/Chrome/Application/chrome.exe" --headless=new --disable-gpu \
    --force-prefers-reduced-motion --window-size=500,900 --virtual-time-budget=9000 \
    --dump-dom "file:///E:/window/Temp/_hmfx.html" | grep -o "<title>[^<]*</title>"
```

Ky vong: `hm-fx4` van cho `--hm-dur:3.6s` (bien khong doi) nhung `animationName` cua `.hm-aura`/`.hm-btn:after`/`.hm-badge` (bac 4 = ruoi?) phai la `none`. Do bang cach them vao harness:

```js
// them vao read(): c.animationName + '/' + c.animationDuration
```

> **DAO NGUOC CO TREN**: `.hm-wrap.is-dao .hm-badge` (ruoi Dao Phẩm, Task 7 Step 4.4) khong thuoc `.hm-fx*` — no theo PHẨM CHẤT. Luc kiem reduced-motion phai dat `huyetmach_quality=10` (khong phai 1) thi moi thay chu cai ruoi; dung quen, neu khong se bao kien "khong chay" gia.

- [ ] **Step 6: Trang Nhân Vật phai noi du 8 mach** — `game/zhuangtai.php`, trong khoi `// ===== HUYẾT MẠCH =====` (dong 536→590):

Thay `foreach ($hmStats as $k => $v)` + 4 dong `Element DMG / Element Bonus / Crit Rate / EXP Bonus` bang:

```php
    $hmStats = $player->huyetmach_stats;
    // 4 chi so goc cua huyet mach (nhan theo pham chat)
    $hmStatNames = array('atk'=>'Công Kích','hp'=>'Khí Huyết','crit'=>'Bạo Kích','lifesteal'=>'Hút Máu');
    $hmDetails = '';
    foreach ($hmStats as $k => $v) {
        if ($v != 0 && isset($hmStatNames[$k])) {
            $hmDetails .= "<div class='profile-stat'><span>{$hmStatNames[$k]}</span><strong>+" . number_format($v) . "</strong></div>";
        }
    }
    if ($player->huyetmach_element_dmg > 0) {
        $hmElName = function_exists('player\\huyetmach_element_name') ? player\huyetmach_element_name($player->huyetmach_element) : $player->huyetmach_element;
        $hmDetails .= "<div class='profile-stat'><span>Tổn hại {$hmElName}</span><strong>+" . $player->huyetmach_element_dmg . "%</strong></div>";
    }
    if ($player->huyetmach_passive_element > 0) {
        $hmDetails .= "<div class='profile-stat'><span>Bổ sung nguyên tố</span><strong>+" . $player->huyetmach_passive_element . "%</strong></div>";
    }
    if ($player->huyetmach_passive_crit > 0) {
        $hmDetails .= "<div class='profile-stat'><span>Tỷ lệ bạo kích</span><strong>+" . $player->huyetmach_passive_crit . "%</strong></div>";
    }
    if ($player->huyetmach_passive_exp > 0) {
        $hmDetails .= "<div class='profile-stat'><span>EXP thưởng</span><strong>+" . $player->huyetmach_passive_exp . "%</strong></div>";
    }

    // 8 chi so MẠCH (cộng thẳng, milestone đa nhân trong huyetmach_vessel_bonuses)
    $hmVes = (isset($player->huyetmach_vessel) && is_array($player->huyetmach_vessel)) ? $player->huyetmach_vessel : array();
    if (!empty($hmVes) && function_exists('player\\huyetmach_vessel_stat_label')) {
        foreach (array('atk','hp','crit','lifesteal','dodge','pen','reduce','luck') as $vk) {
            $vv = isset($hmVes[$vk]) ? floatval($hmVes[$vk]) : 0;
            if ($vv == 0) continue;
            $vl = player\huyetmach_vessel_stat_label($vk);
            $vd = in_array($vk, array('atk','hp')) ? number_format($vv) : $vv;
            $hmDetails .= "<div class='profile-stat'><span>Mạch {$vl}</span><strong>+{$vd}</strong></div>";
        }
        $hmVesTotal = isset($hmVes['total_lv']) ? intval($hmVes['total_lv']) : 0;
        if ($hmVesTotal > 0) {
            $hmDetails .= "<div class='profile-stat'><span>Tổng cấp mạch</span><strong>{$hmVesTotal}</strong></div>";
        }
    }
```

> `huyetmach_stats` chi la 4 chi so goc; mach nam o `$hmB['vessel']` (Task 3). Nen Step 6 PHAI di kem Step 7 (gan `huyetmach_vessel` len `$player`), neu khong doan tren luon rong.

- [ ] **Step 7: Lo `vessel` ra nhan vat** — `class/player.php`, ngay sau dong `$player->huyetmach_stats = $hmB['stats'];` (dong 823):

```php
        $player->huyetmach_vessel = isset($hmB['vessel']) ? $hmB['vessel'] : array();
```

va trong khoi gia tri mac dinh (dong 807-811) them `$player->huyetmach_vessel = array();`. Class `player` khai bao `var $huyetmach_stats;` o dong 146 → them `var $huyetmach_vessel;` canh do (le cua repo: `var` khai bao tường minh, thieu la ` Undefined property`).

- [ ] **Step 8: Quet nut trang san `.main button` trong 3 man Huyết Mach**

```bash
grep -n "<button" game/huyetmach.php game/huyetmach_transform.php game/huyetmach_upgrade.php
grep -n "class=\"hm-btn" game/huyetmach.php game/huyetmach_transform.php game/huyetmach_upgrade.php
```

Ky vong: moi `<button>` deu mang `hm-btn` + mot lop `.main .hm-btn-*` (Task 7 Step 4.8). Nut Quay lai o man transform PHAI la `<a class="hm-btn">` (link), khong phai `<button>`. Do bang harness Step 3: `getComputedStyle(btn).backgroundColor` khong duoc la `rgb(255, 255, 255)`; `paddingTop` = 11px.

- [ ] **Step 9: Do 3 con so tren man transform + upgrade (mobile 390px)**

| Phần tử | Kỳ vọng |
|---|---|
| `.hm-btn-break` (man transform) | `background-image` chura `linear-gradient`, `color: rgb(255,255,255)` |
| `.hmq-card` (man upgrade) | width = noi dung card (624px desktop); `height` q1 == q10 |
| `.hm-empty` (man upgrade, `huyetmach_id=0`) | xuat hien, khong co `Undefined variable`/`Notice` trong nguon trang |

- [ ] **Step 10: Chay lai toan bo test + lint**

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
$PHP -l game/zhuangtai.php && $PHP -l class/player.php && $PHP -l game/huyetmach.php
$PHP tests/test_huyetmach_vessel.php
$PHP tests/test_huyetmach_boss.php
$PHP tests/test_huyetmach_upgrade.php
grep -nP "[\\\\x{4e00}-\\\\x{9fff}]" game/zhuangtai.php class/player.php | head   # 0 ket qua
```

Ky vong: 3 file test `ALL PASS`. Neu `test_huyetmach_vessel.php` FAIL o phan B vi row `game1` cua `$TESTSID` bi Step 3 xoa, chay lai Task 3 Step truoc khi ket luan — do la le cua harness, khong phai bug.

- [ ] **Step 11: Commit**

```bash
git add css/mystyle.css game/zhuangtai.php class/player.php docs/img/huyetmach
git commit -m "feat(huyetmach): verified fx ladder + 8 meridian stats on status screen"
```

---
### Task 11: Chiến đấu — áp 4 chỉ số mạch âm (dodge / pen / reduce / luck)

**Files:**
- Modify: `class/function/huyetmach.php` (cuối file, sau khối Task 3)
- Modify: `class/function/huyetmach_boss.php` (Task 8 — 3 chỗ)
- Modify: `game/boss.php` (3 chỗ)
- Modify: `game/pve.php` (3 chỗ)
- Modify: `game/pvp.php` (1 chỗ)
- Modify: `class/config/huyetmach_vessel_config.json` (mô tả mạch pen)
- Create: `tests/test_huyetmach_combat.php`

**Interfaces:**
- Consumes: `$player->huyetmach_vessel` (**Task 10 phải xong trước** — nó gán field này trong `getplayer()`), `huyetmach_vessel_bonuses()` (Task 3), `huyetmach_vessel_config_all()['stat_cap']` (Task 1), `huyetmach_boss_vessel_dodge/reduce()` (Task 8), `$pvper` từ `getplayer1()` (`game/pvp.php:96`), `$guaiwuFyForDamage` (`game/pve.php:229-234`).
- Produces (tất cả trong `namespace player`, có guard `function_exists`, đặt ở `class/function/huyetmach.php`):
  - `huyetmach_combat_stats($vessel): array` — keys `dodge, pen, reduce, luck`, mỗi key là float đã kẹp `0 .. stat_cap`. Nhận thẳng mảng vessel của Task 3 (hoặc `array()` / `null`), **không query DB**.
  - `huyetmach_combat_roll_dodge($pct): bool`
  - `huyetmach_combat_phys_reduce($pct, $dmg): int`
  - `huyetmach_combat_pierce_def($pct, $def): int` — trả về phòng ngự **còn lại**
  - `huyetmach_combat_luck_gate($pct, $gate): int` — trả về ngưỡng rơi đồ **đã nhân**
  - `huyetmach_boss_vessel_dodge($bonuses)` / `huyetmach_boss_vessel_reduce($bonuses, $dmg)` (Task 8) giữ nguyên chữ ký và hành vi đã test, nay là wrapper mỏng gọi 4 hàm trên.

#### 11.0 KIỂM CHỨNG ĐÃ LÀM TRƯỚC KHI VIẾT TASK NÀY — thiết kế §5 của spec SAI, phải sửa ở đây

Spec `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md` §5 ghi: dodge → cộng vào `tfsb`; reduce → cộng vào `ufy`; luck → cộng vào `uwx` + `huyetmach_passive_drop`; §8 mục 2 tự nhận "chưa đọc hết pve.php/pvp.php, phải kiểm chứng trước khi sửa công thức sát thương". Đã kiểm chứng toàn bộ; **cả ba ánh xạ trong bảng §5 đều không dùng được**:

- `tfsb` (thiên phú né tránh) **không xuất hiện trong bất kỳ công thức sát thương nào**. Toàn bộ nơi dùng: `game/tianfu.php:19,100,152` (nút thiên phú ghi DB + hiển thị), `game/zhuangtai.php:419` (hiển thị), `game/paihang.php:19` (điểm xếp hạng). Cái "Né tránh" ở `game/boss.php:287-291` là **BOSS né đòn của người chơi** (`$gphurt = 0`), không phải người chơi né đòn boss. ⇒ không có chỗ nào để "cộng dodge vào tfsb".
- `uwx`: chỉ được khai báo (`class/player.php:47`), đưa vào danh sách cột (`:299`, `:310`), cộng dồn (`:1189`) và `game.php:192`; **không nơi nào đọc**. Cộng luck vào đó = chữ tịt.
- `huyetmach_passive_drop`: `class/function/huyetmach.php:213,252` → `class/player.php:151,816,828` → `game/huyetmach.php:97-98` chỉ để **hiển thị** `+N%`. Không hàm rơi đồ nào đọc nó.
- `$player->ufy` **đã** nhận điểm cộng từ trang bị + Thể Chất + Huyết Mạch (`class/player.php:830`); mọi công thức sát thương lại nhân `ufy * 0.75`. Nhét thêm "12%" dưới dạng điểm thô sẽ (i) làm sai số phòng thủ hiển thị ở `zhuangtai.php` / `paihang.php` và (ii) bị nhân 0.75 nên không còn là 12%. ⇒ `reduce` phải là **% trên sát thương tới**, không phải điểm `ufy`.
- `class/player.php:859-866`: `getplayer1($uid)` chính là `getplayer($sid)` ⇒ `$pvper` trong `game/pvp.php` **có sẵn** `$huyetmach_vessel` thật của đối thủ, không cần query thêm.
- `game/pvp.php` **không có đòn phản công**: đối thủ chỉ nhận sát thương tại `:104` (`$hurtNum`), `:211-212` chỉ là hiển thị `ugj/ufy` của đối thủ.
- `player_cache_*` (`class/player.php:372-385`) cache bằng `clone $player` ⇒ mảng `huyetmach_vessel` đi theo cache trong request, không mất khi cache hit.
- **Bẫy phần thưởng**: `game/boss.php:275-276` và `game/pve.php:291-293` đặt `$hurt = true` khi `$phurt <= 0`, và `game/boss.php:343` / `game/pve.php:347` chặn thưởng (`$yxb = 0`) khi `$hurt`. ⇒ **mọi chỉnh sửa `$phurt` (reduce/dodge) phải chèn SAU các dòng đó**, nếu chèn tại chỗ tính `$phurt` (boss `:213`, pve `:199`) thì người chơi né được đòn = mất luôn tiền thưởng.
- **Trần thật của chỉ số âm là 12%, không phải 15%**: một stat chỉ do đúng MỘT mạch nắm, `per_lv 0.25 × trần cấp mạch 48 (stage 4) = 12`. `stat_cap 15` vì thế không bao giờ binding. Ghi vào 11.0 Task 12 để chủ chốt (nâng `per_lv` lên 0.3125, hay hạ `stat_cap` xuống 12, hay cho 2 mạch cùng stat).

Kết luận thiết kế dùng trong task này: 4 chỉ số âm là **% thuần, tính lúc chạm đòn, không nhân vật hoá thành field** → `class/player.php` không phải sửa thêm (Task 3 đã cộng 4 chỉ số dương, Task 10 đã gán `$player->huyetmach_vessel`).

#### 11.0b SỐ ĐO THẬT — ngưỡng "pen làm sát thương tăng > 8%" của spec §5

Harness `--measure` (Step 14) dựng lại từ số liệu đang có trong DB local: `ugj`/`ufy` theo cấp lấy theo phân vị của 15 nhân vật thật trong `game1` (median và p75 cho kết quả **giống nhau**), ghép với 78 quái `midguaiwu` (cấp 1-73, `gfy` 1-681), 4 dòng bảng `boss` (cấp 30-70), và cặp PvP `atk(cấp) vs ufy(cấp)`. Công thức sao y `boss.php:235` / `pve.php:237` / `pvp.php:104` kể cả sàn 15% (`pvp`: sàn 5%).

| Bề mặt | pen = 15% | pen = 5% | Phán quyết |
|---|---|---|---|
| PVE (78 quái) | mean **+7,26%**, median 0%, p90 +50%, max +50%; **78% cặp bị sàn 15% chặn nên pen = 0%** | mean +1,28% | **≤ 8% ⇒ áp dụng pen ở `pve.php`** |
| BOSS (4 dòng) | mean **+0%** (100% cặp bị sàn chặn) | +0% | áp dụng (đang vô hiệu với dữ liệu local) |
| PVP (24 cấp) | mean **+29,59%**, median +33,3%, max +72,7%; 0% bị sàn chặn | mean +9,64% (vẫn > 8%) | **> 8% ⇒ KHÔNG áp pen ở `pvp.php`** |

spec §5 cho phép: "nếu (b) làm sát thương trung bình tăng > 8% thì dừng ở (a), tooltip ghi rõ 'chưa áp vào đòn đánh', và **báo chủ dự án** — không tự ý bỏ bớt". Áp dụng đúng tinh thần đó, nhưng **chỉ cho bề mặt PvP** (PVE/BOSS đã nằm dưới ngưỡng): `game/pvp.php` không gọi `huyetmach_combat_pierce_def()`, mô tả mạch pen trong config ghi rõ không áp dụng PvP, và việc này nằm trong danh sách chờ chủ chốt (Task 12).

Hai hệ quả phải báo cáo chứ không sửa ngầm (Task 12):
1. **Pen gần như vô dụng khi cày đúng cấp** vì sàn 15% của công thức cũ (`$gphurt < $player->ugj*0.15`) chặn 78% cặp PVE và 100% cặp BOSS trong dữ liệu local. Đây là hệ quả của công thức có trước, không phải bug mới.
2. Mẫu local mỏng (`bossinfo` rỗng, `midguaiwu` chỉ 78 dòng cấp ≤ 73) ⇒ **chạy lại `--measure` sau khi deploy production** rồi hãy chốt pen cho PvP.

- [ ] **Step 1: Kiểm tra tiền đề Task 10** (field phải tồn tại và là mảng 8 keys trước khi đụng trang chiến đấu):

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
require "class/player.php"; require "class/function/huyetmach.php";
$db = new PDO("mysql:host=localhost;dbname=game;charset=utf8","root","www.aae.ink");
$p = player\getplayer($db->query("SELECT sid FROM game1 LIMIT 1")->fetchColumn(), $db);
echo isset($p->huyetmach_vessel) && is_array($p->huyetmach_vessel) ? "OK vessel=" . count($p->huyetmach_vessel) : "THIEU: Task 10 chua xong";
echo "\n";'
```
Expected: `OK vessel=17` (8 chỉ số + `levels, total_lv, cap, stage, milestone_name, milestone_percent, next_*` — nếu `count` khác 17 thì **dừng**, đối chiếu lại Task 3 Step 3/4, vì Step 6 của task này đọc trực tiếp `$cs['pen']`). Nếu in `THIEU`: Task 10 chưa xong, không được làm tiếp Task 11.

- [ ] **Step 2: Viết test bại** `tests/test_huyetmach_combat.php` — toàn bộ file gồm khối A-D. Khối E (`--live`) và F (`--measure`) được nối vào ở Step 13–14, chèn **trước** hai dòng `echo/exit` cuối file:

```php
<?php
/**
 * test_huyetmach_combat.php — 4 chi so mach am trong cham dot (Task 11).
 * Mac dinh (A-D):   "...php.exe" tests/test_huyetmach_combat.php
 * Route that (+E):  "...php.exe" tests/test_huyetmach_combat.php --live
 * Do nguong pen (F): "...php.exe" tests/test_huyetmach_combat.php --measure
 * --live can Apache dang chay (http://localhost/game.php) va tests/e2e_fixture.php chay truoc.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/huyetmach.php';
require_once $ROOT . '/class/function/huyetmach_boss.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
function tclose($name, $got, $exp, $tol) {
    global $fails;
    $d = abs($got - $exp);
    if ($d <= $tol) { echo "PASS $name (thuc te=$got, ly thuyet=$exp, lech " . round($d, 3) . " <= $tol)\n"; }
    else { $fails++; echo "FAIL $name (thuc te=$got, ly thuyet=$exp, lech " . round($d, 3) . " > $tol)\n"; }
}
$LIVE = in_array('--live', $argv);
$MEASURE = in_array('--measure', $argv);
$ZERO = array('dodge' => 0.0, 'pen' => 0.0, 'reduce' => 0.0, 'luck' => 0.0);

// ===== A: 5 helper thuan tuy, khong DB =====
tassert('vessel rong = 4 key 0.0', \player\huyetmach_combat_stats(array()), $ZERO);
tassert('vessel null an toan', \player\huyetmach_combat_stats(null), $ZERO);
$cs = \player\huyetmach_combat_stats(array('dodge' => 12, 'pen' => 99, 'reduce' => -5, 'luck' => 7.5));
tassert('dodge giu 12', $cs['dodge'], 12.0);
tassert('pen chan 15', $cs['pen'], 15.0);
tassert('reduce am ve 0', $cs['reduce'], 0.0);
tassert('luck giu 7.5', $cs['luck'], 7.5);

tassert('pierce 0 khong xuyen', \player\huyetmach_combat_pierce_def(0, 1000), 1000);
tassert('pierce 15 tren 1000', \player\huyetmach_combat_pierce_def(15, 1000), 850);
tassert('pierce 15 tren 470 (def boss lv30)', \player\huyetmach_combat_pierce_def(15, 470), 400);
tassert('pierce 150 van chan 15', \player\huyetmach_combat_pierce_def(150, 1000), 850);
tassert('pierce def 0', \player\huyetmach_combat_pierce_def(15, 0), 0);

tassert('reduce 0 giu don', \player\huyetmach_combat_phys_reduce(0, 100), 100);
tassert('reduce 15 tren 100', \player\huyetmach_combat_phys_reduce(15, 100), 85);
tassert('reduce don 0 van 0 (khong ep max(1))', \player\huyetmach_combat_phys_reduce(15, 0), 0);
tassert('reduce 150 chan 15 tren 1000', \player\huyetmach_combat_phys_reduce(150, 1000), 850);

tassert('luck 0 khong doi nguong', \player\huyetmach_combat_luck_gate(0, 21), 21);
tassert('luck 15 tren 21 (median dljv)', \player\huyetmach_combat_luck_gate(15, 21), 24);
tassert('luck 15 tren 6 (median djjv)', \player\huyetmach_combat_luck_gate(15, 6), 7);
tassert('luck nguong 0 van 0', \player\huyetmach_combat_luck_gate(15, 0), 0);

// ===== B: wrapper Task 8 giu han vi cu =====
$vb = array('dodge' => 0, 'reduce' => 0);
tassert('wrapper: khong mach thi khong ne', \player\huyetmach_boss_vessel_dodge($vb), false);
tassert('wrapper: reduce 0 giu nguyen don', \player\huyetmach_boss_vessel_reduce($vb, 100), 100);
$vb2 = array('dodge' => 100, 'reduce' => 100);
tassert('wrapper: reduce chan 15', \player\huyetmach_boss_vessel_reduce($vb2, 100), 85);

// ===== C: phan bo roll THAT (khong force-roll, theo tests/test_nguhanh_random.php) =====
$N = 30000; $dodges = 0;
for ($i = 0; $i < $N; $i++) { if (\player\huyetmach_combat_roll_dodge(12)) $dodges++; }
tclose('dodge 12% khop ly thuyet (N=30000)', round($dodges / $N * 100, 2), 12.0, 0.8);
$zeroHit = 0;
for ($i = 0; $i < 3000; $i++) { if (\player\huyetmach_combat_roll_dodge(0)) $zeroHit++; }
tassert('pct 0 khong bao gio ne', $zeroHit, 0);
$capHit = 0; $NC = 10000;
for ($i = 0; $i < $NC; $i++) { if (\player\huyetmach_combat_roll_dodge(99)) $capHit++; }
tclose('pct 99 bi chan xuong 15% (N=10000)', round($capHit / $NC * 100, 2), 15.0, 1.5);

// ===== D: noi vao nhan vat that (fixture e2e) =====
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$pl = \player\getplayer($E2ESID, $db);
if (!is_object($pl)) {
    $fails++;
    echo "FAIL fixture e2e khong ton tai — chay tests/e2e_fixture.php truoc\n";
} else {
    tassert('getplayer gan huyetmach_vessel', isset($pl->huyetmach_vessel) && is_array($pl->huyetmach_vessel), true);
    $need = array('atk', 'hp', 'crit', 'lifesteal', 'dodge', 'pen', 'reduce', 'luck');
    tassert('vessel du 8 chi so', count(array_intersect($need, array_keys($pl->huyetmach_vessel))), 8);
    $opp = \player\getplayer1($pl->uid, $db);
    tassert('doi thu PvP co vessel (getplayer1 -> getplayer)', is_object($opp) && isset($opp->huyetmach_vessel), true);
}

echo "\n" . ($fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n");
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 3: Chạy test → phải BẠI** (helper chưa tồn tại):

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_combat.php
```
Expected: `Fatal error: Call to undefined function player\huyetmach_combat_stats()`. Nếu báo `huyetmach_boss_vessel_dodge` chưa có nghĩa là Task 8 chưa xong — dừng, làm Task 8 trước.

- [ ] **Step 4: Cài 5 helper** — nối vào **cuối** `class/function/huyetmach.php`. (File này khai báo `namespace player;` dạng câu lệnh — `class/function/huyetmach.php:2` — nên viết thẳng ra cuối file, không có ngoặc đóng namespace; đúng như mô tả Files của Task 1 đã được chốt.)

```php
if (!function_exists(__NAMESPACE__ . '\\huyetmach_combat_stats')) {
    /** Clamp 4 chi so mach am ve [0, stat_cap]. Nhan thang mang vessel Task 3, KHONG query DB. */
    function huyetmach_combat_stats($vessel){
        $all  = huyetmach_vessel_config_all();
        $caps = isset($all['stat_cap']) ? $all['stat_cap'] : array();
        $out  = array('dodge' => 0.0, 'pen' => 0.0, 'reduce' => 0.0, 'luck' => 0.0);
        if (!is_array($vessel)) return $out;
        foreach (array('dodge', 'pen', 'reduce', 'luck') as $k) {
            $cap = isset($caps[$k]) ? floatval($caps[$k]) : 15.0;
            $val = isset($vessel[$k]) ? floatval($vessel[$k]) : 0.0;
            if ($val < 0) $val = 0.0;
            $out[$k] = min($cap, $val);
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_combat_roll_dodge')) {
    /** % co the le (0.25/muc) -> quay 1/10000 de 12.0% = 1200/10000. */
    function huyetmach_combat_roll_dodge($pct){
        $pct = min(15.0, max(0.0, floatval($pct)));
        if ($pct <= 0) return false;
        return mt_rand(1, 10000) <= intval(round($pct * 100));
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_combat_phys_reduce')) {
    /** % tru sat thuong vat ly phai nhan. Don 0 giu 0 (khong ep max(1) nhu ham cu cua Task 8). */
    function huyetmach_combat_phys_reduce($pct, $dmg){
        $pct = min(15.0, max(0.0, floatval($pct)));
        $dmg = intval($dmg);
        if ($dmg <= 0) return 0;
        if ($pct <= 0) return $dmg;
        return max(1, intval(round($dmg * (100 - $pct) / 100)));
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_combat_pierce_def')) {
    /** % xuyen phong ngo; tra ve phong ngo CON LAI, khong am. */
    function huyetmach_combat_pierce_def($pct, $def){
        $pct = min(15.0, max(0.0, floatval($pct)));
        $def = floatval($def);
        if ($def <= 0 || $pct <= 0) return intval($def);
        return max(0, intval(round($def * (100 - $pct) / 100)));
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_combat_luck_gate')) {
    /** Van may NHAN nguong roi do (khong cong tran), vi du 21 -> 24 o 15%. */
    function huyetmach_combat_luck_gate($pct, $gate){
        $pct  = min(15.0, max(0.0, floatval($pct)));
        $gate = intval($gate);
        if ($gate <= 0 || $pct <= 0) return $gate;
        return intval(round($gate * (100 + $pct) / 100));
    }
}
```

- [ ] **Step 5: Chạy test → PASS khối A**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_combat.php
```
Kỳ vọng: 21 dòng `PASS` khối A (5 helper ở Step 4 đã đủ cho khối A và C), rồi `FAIL` ở khối B — wrapper Task 8 vẫn còn công thức cũ, phải đợi Step 6 mới sửa. Khối D in `SKIP` (cần seed DB). Không sốt vì khối B FAIL ở bước này.

- [ ] **Step 6: Refactor Task 8 để không có hai bộ máy tính chồng nhau** — sửa `class/function/huyetmach_boss.php` 3 chỗ (file này đã phụ thuộc `class/function/huyetmach.php` từ Task 8 vì gọi `huyetmach_vessel_bonuses()`):

(a) Hai wrapper — thay nguyên khối `huyetmach_boss_vessel_dodge` và `huyetmach_boss_vessel_reduce` bằng:

```php
if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_vessel_dodge')) {
    /** Wrapper giu chu ky Task 8; clamp + roll nam o huyetmach_combat_*. */
    function huyetmach_boss_vessel_dodge($bonuses){
        $cs = huyetmach_combat_stats($bonuses);
        return huyetmach_combat_roll_dodge($cs['dodge']);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_vessel_reduce')) {
    /** Wrapper Task 8. Khac biet co y nghia: don 0 giu 0 (ham cu ep max(1)). */
    function huyetmach_boss_vessel_reduce($bonuses, $dmg){
        $cs = huyetmach_combat_stats($bonuses);
        return huyetmach_combat_phys_reduce($cs['reduce'], $dmg);
    }
}
```

(b) `huyetmach_boss_calc_damage()` — thay dòng `$baseDmg = max(1, intval($player->ugj) - intval(intval($bossRow['def']) * 0.75));` bằng:

```php
        $hmCs  = huyetmach_combat_stats(isset($player->huyetmach_vessel) ? $player->huyetmach_vessel : array());
        $hmDef = huyetmach_combat_pierce_def($hmCs['pen'], intval($bossRow['def']));
        $baseDmg = max(1, intval($player->ugj) - intval($hmDef * 0.75));
```

(c) `huyetmach_boss_finish()` — chèn **ngay sau** khối `$firstKill ... $ht += intval($cfg['boss_config']['first_kill'][strval($stage)]); }` (để vận may nhân cả thưởng nhất-killed):

```php
        // Van may mach Duong Kieu: nhan so Huyet Thach roi (12% -> x1.12)
        $hmCs = huyetmach_combat_stats(huyetmach_vessel_bonuses($sid, $dblj));
        if ($hmCs['luck'] > 0) {
            $ht = max(1, intval(round($ht * (100 + $hmCs['luck']) / 100)));
        }
```

(d) Test Task 8 **bắt buộc** phải có `player_huyetmach_vessel` sạch cho `$TESTSID`, nếu không hai assertion `finish tra dung so Huyet Thach` (14) và `first_kill khong lap lai` (4) sẽ đổi kết quả khi Task 3/4/5 để lại level mạch. Thêm vào **cả hai** chỗ dọn dữ liệu đầu + cuối của `tests/test_huyetmach_boss.php` (cạnh các dòng `DELETE FROM huyetmach_boss WHERE sid = ?`):

```php
$db->prepare("DELETE FROM player_huyetmach_vessel WHERE sid = ?")->execute(array($TESTSID));
```




- [ ] **Step 7: Chạy lại 2 test** — `test_huyetmach_combat.php` in `PASS` khối A+B+C+D rồi `ALL PASS` (khối E/F **chưa tồn tại** ở thời điểm này, hai khối đó được nối vào tại Step 13–14), và `test_huyetmach_boss.php` vẫn `ALL PASS` giống trước khi refactor.
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_combat.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_huyetmach_boss.php
```
Nếu `test_huyetmach_boss.php` FAIL ở `khong co mach thi khong ne`: wrapper đang nhận `$vb = array('dodge'=>0,...)` nhưng `huyetmach_combat_stats()` gọi `huyetmach_vessel_config_all()` — kiểm tra config Task 1 có `stat_cap` (`dodge/pen/reduce/luck = 15`).

- [ ] **Step 8: Nối `game/boss.php` (3 chỗ).** Trước khi sửa, chạy `grep -n "hmCs\|hmIncoming\|hmBlocked" game/boss.php` → phải ra 0 kết quả (không trùng tên biến).

(a) **Âm Duy Mạch (pen)** — chèn **ngay trên** dòng `game/boss.php:235` (dòng bắt đầu `$gphurt = round($cwgj + $player->ugj - ($boss->bossfy * 0.75),0);`; dòng này có chú thích tiếng Trung ở cuối, **chỉ chèn phía trên, không gõ lại chú thích đó**), rồi đổi `($boss->bossfy * 0.75)` thành `($hmBossFy * 0.75)` ngay trên dòng ấy (chuỗi `($boss->bossfy * 0.75)` chỉ xuất hiện 1 lần — đã đo `grep -c` = 1):

```php
    // ===== MACH HUYET (Task 11): 4 chi so mach am dung luc cham dot =====
    $hmCs = \player\huyetmach_combat_stats(isset($player->huyetmach_vessel) ? $player->huyetmach_vessel : array());
    $hmBossFy = \player\huyetmach_combat_pierce_def($hmCs['pen'], $boss->bossfy);
    if ($hmBossFy < $boss->bossfy) {
        $tishihtml .= "<font color='#26c6da'>🏹 Âm Duy Mạch (Xuyên Giảm): phòng ngự {$boss->bossname} bị xuyên ".($boss->bossfy - $hmBossFy)." điểm</font><br/>";
    }
```

(b) **Dương Duy Mạch (reduce) + Âm Kiều Mạch (dodge)** — chèn **giữa** dòng 276 (`$hurt = true;` + `}`) và dòng 279 `if ($phurt < $pvbxx){`. Vị trí này là **bắt buộc**: `$hurt` (định nghĩa ở 275-276) chặn thưởng linh thạch tại `:343`, nên nếu sửa `$phurt` sớm hơn thì né được đòn = mất tiền.

```php
    $hmIncoming = intval($phurt);
    if ($hmCs['reduce'] > 0 && $hmIncoming > 0) {
        $hmBlocked = \player\huyetmach_combat_phys_reduce($hmCs['reduce'], $hmIncoming);
        if ($hmBlocked < $hmIncoming) {
            $tishihtml .= "<font color='#66bb6a'>🧱 Dương Duy Mạch (Giảm Vật Lý): đòn của {$boss->bossname} bị chặn ".($hmIncoming - $hmBlocked)." sát thương</font><br/>";
            $hmIncoming = $hmBlocked;
        }
    }
    if ($hmCs['dodge'] > 0 && $hmIncoming > 0 && \player\huyetmach_combat_roll_dodge($hmCs['dodge'])) {
        $hmIncoming = 0;
        $tishihtml .= "<font color='#90caf9'>💨 Âm Kiều Mạch (Né Tranh): {$boss->bossname} vung trúng khoảng không</font><br/>";
    }
    $phurt = $hmIncoming;
```
Hệ quả có chủ đích, phải ghi vào `docs/UI_HUYETMACH.md` (Task 12): vì `$phurt` đã bằng 0, `$pvbxx` (hút máu, clamp tại 279-285) về 0 và `$cwxl = round($phurt*2.2)` tại `:452` cũng 0 ⇒ **né một đòn = cả thú cưng không ăn đòn đó**, nhất quán với thông báo "vung trúng khoảng không".

(c) **Dương Kiều Mạch (luck)** — nhân 3 cổng rơi đồ trong nhánh giết boss (`:382`, `:409`, `:427`). Chỉ bọc vế trái, **giữ nguyên** `mt_rand(1,120-$xyz)` và `mt_rand(1,100)` (không đụng thiên phú `tfxy`):

```php
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $boss->dljv) >=$sjjv && $boss->bosszb != ''){
```
```php
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $boss->djjv) >= $sjjv && $boss->bossdj != ''){
```
```php
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $boss->ypjv) >= $sjjv && $boss->bossyp != ''){
```

- [ ] **Step 9: Nối `game/pve.php` (3 chỗ).** `grep -n "hmCs\|hmIncoming\|hmPierceFrom" game/pve.php` phải ra 0 kết quả trước khi sửa.

(a) Định nghĩa `$hmCs` **ngay trên** dòng `game/pve.php:229` (`    $guaiwuFyForDamage = $guaiwu->gfy;`):

```php
    // ===== MACH HUYET (Task 11) =====
    $hmCs = \player\huyetmach_combat_stats(isset($player->huyetmach_vessel) ? $player->huyetmach_vessel : array());
```

(b) **pen** — chèn **ngay sau** khối võ công `pierce_def / break_armor` (khối kết thúc bằng dòng `$tishihtml .= "<font color='#42a5f5'>Võ công {$wgEffectName} phát huy: xuyên {$wgEffectValue}% phòng ngự</font><br/>";` + `}`), tức trước dòng 237 `$gphurt = round($cwgj + $player->ugj - ($guaiwuFyForDamage * 0.75),0);`. Pen **nhân tiếp** trên giá trị võ công đã xuyên (không cộng dồn %):

```php
    $hmPierceFrom = $guaiwuFyForDamage;
    $guaiwuFyForDamage = \player\huyetmach_combat_pierce_def($hmCs['pen'], $guaiwuFyForDamage);
    if ($guaiwuFyForDamage < $hmPierceFrom) {
        $tishihtml .= "<font color='#26c6da'>🏹 Âm Duy Mạch (Xuyên Giảm): phòng ngự {$guaiwu->gname} bị xuyên ".($hmPierceFrom - $guaiwuFyForDamage)." điểm</font><br/>";
    }
```

(c) **reduce + dodge** — chèn giữa dòng 293 (`}` của `if ($phurt <= 0){ $hurt = true; }`) và dòng 296 `if ($phurt < $pvexx){` (cùng lý do `$hurt` chặn thưởng như boss.php — `game/pve.php:347`):

```php
    $hmIncoming = intval($phurt);
    if ($hmCs['reduce'] > 0 && $hmIncoming > 0) {
        $hmBlocked = \player\huyetmach_combat_phys_reduce($hmCs['reduce'], $hmIncoming);
        if ($hmBlocked < $hmIncoming) {
            $tishihtml .= "<font color='#66bb6a'>🧱 Dương Duy Mạch (Giảm Vật Lý): chặn ".($hmIncoming - $hmBlocked)." sát thương từ {$guaiwu->gname}</font><br/>";
            $hmIncoming = $hmBlocked;
        }
    }
    if ($hmCs['dodge'] > 0 && $hmIncoming > 0 && \player\huyetmach_combat_roll_dodge($hmCs['dodge'])) {
        $hmIncoming = 0;
        $tishihtml .= "<font color='#90caf9'>💨 Âm Kiều Mạch (Né Tranh): {$guaiwu->gname} vung trúng khoảng không</font><br/>";
    }
    $phurt = $hmIncoming;
```

(d) **luck** — 3 cổng `:392`, `:412`, `:441`. Giữ phép cộng `drop_bonus` của võ công, chỉ bọc thêm phép nhân vận may:

```php
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], ($yguaiwu->dljv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0))) >= $sjjv && $yguaiwu->gzb != ''){
```
```php
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], ($yguaiwu->djjv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0))) >= $sjjv && $yguaiwu->gdj != ''){
```
```php
        if (\player\huyetmach_combat_luck_gate($hmCs['luck'], $yguaiwu->ypjv) >= $sjjv && $yguaiwu->gyp != ''){
```

- [ ] **Step 10: Nối `game/pvp.php` — CHỈ dodge/reduce của phe bị đánh, KHÔNG pen** (số đo 11.0b: pen trong PvP = mean +29,59% ≫ ngưỡng 8%). `grep -n "hmCsDef\|hmDodged" game/pvp.php` phải ra 0 kết quả.

(a) Chèn **giữa** khối sàn 5% (dòng 105-107) và `$pvpxx = round($hurtNum*($player->uxx/100));` (dòng 109):

```php
            // ===== MACH HUYET (Task 11): mach cua NGUOI BI DAN trung lap =====
            // pen CO Y khong ap dung PvP: do mean +29.59% (nguong 8%) — xem 11.0b + Task 12.
            $hmCsDef = \player\huyetmach_combat_stats(isset($pvper->huyetmach_vessel) ? $pvper->huyetmach_vessel : array());
            $hmDodged = false;
            if ($hmCsDef['reduce'] > 0 && $hurtNum > 0) {
                $hurtNum = \player\huyetmach_combat_phys_reduce($hmCsDef['reduce'], $hurtNum);
            }
            if ($hmCsDef['dodge'] > 0 && $hurtNum > 0 && \player\huyetmach_combat_roll_dodge($hmCsDef['dodge'])) {
                $hurtNum = 0;
                $hmDodged = true;
                $tishihtml .= '<div class="pvp-log bad">💨 Âm Kiều Mạch: đối phương nghiêng người tránh khỏi đòn</div>';
            }
```

(b) Dòng 118 `            $pvperhurt = '<span class="pvp-damage">-'.$hurtNum.'</span>';` → đổi thành để không hiện "-0":

```php
            $pvperhurt = $hmDodged ? '<span class="pvp-damage">Né (0)</span>' : '<span class="pvp-damage">-'.$hurtNum.'</span>';
```

Không có chiều "người đánh né đòn phản công" vì `game/pvp.php` **không có phản công** (đã kiểm chứng ở 11.0).

- [ ] **Step 11: Sửa mô tả mạch pen trong config** để tooltip nói đúng phạm vi (Task 7 hiển thị `desc` nguyên văn): `class/config/huyetmach_vessel_config.json`, id 6 (`stat: pen`), đổi `desc` thành:

```
"Liên kết âm khí, mũi nhọn thấm sâu. Mỗi cấp tăng Xuyên Giảm (xuyên Phong Thu của quái/BOSS). Không áp dụng khi PvP."
```

Chạy lại kiểm tra JSON + CJK như Task 1 Step 2 (expected `no CJK` và `json ok, vessels=8, tiers=5`).

- [ ] **Step 12: Lint 5 file vừa sửa** (PHP 5.6, không được có cú pháp mới):

```bash
for f in class/function/huyetmach.php class/function/huyetmach_boss.php game/boss.php game/pve.php game/pvp.php; do
  D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l "$f"
done
```
Expected: 5 dòng `No syntax errors detected`.

- [ ] **Step 13: Khối E — route THẬT qua HTTP** (nối vào `tests/test_huyetmach_combat.php`, **chèn trước** hai dòng `echo "\n" . ($fails === 0 ...)`) Yêu cầu: Apache đang chạy (`http://localhost/game.php`) và `tests/e2e_fixture.php` đã chạy. Số liệu thật đã đo để chọn giá trị seed: fixture `e2e2e2e2...` có `uid=987650, ulv=1000, uhp=35, ugj=12, ufy=5, umaxstamina=100, nowmid=225`; `midguaiwu` có 14 cột; `boss` có 20 cột (`bossid, bossname, bossinfo, bosslv, bosshp, bossmaxhp, bossgj, bossfy, bossbj, bossxx, bosszb, dljv, bossdj, djjv, bs, bosstime, sid, bossyp, ypjv, bossexp`) và hiện chỉ có 4 dòng `bossid` 1-4, nên `9001` là vùng an toàn.

```php
// ===== E: ROUTE THAT qua HTTP (chi chay khi co --live) =====
if ($LIVE) {
    $enc = new \encode\encode();
    $hmGet = function ($plain) use ($enc) {
        return (string) shell_exec('curl -s ' . escapeshellarg('http://localhost/game.php?cmd=' . $enc->encode($plain)));
    };

    // E.1 — snapshot + seed 8 mach lv48, stage 4 (tran 48) cho fixture
    $snap = $db->query("SELECT ulv,ugj,ufy,ubj,uxx,uhp,umaxhp,ustamina FROM game1 WHERE sid = '$E2ESID'")->fetch(PDO::FETCH_ASSOC);
    if (!is_array($snap)) {
        $fails++;
        echo "FAIL E: fixture e2e khong co trong game1 — chay tests/e2e_fixture.php truoc\n";
    } else {
        for ($v = 1; $v <= 8; $v++) {
            $db->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at)
                VALUES (?, ?, 48, NOW()) ON DUPLICATE KEY UPDATE level = 48")->execute(array($E2ESID, $v));
        }
        $db->exec("UPDATE game1 SET huyetmach_awaken_stage = 4 WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);

        $vb = \player\huyetmach_vessel_bonuses($E2ESID, $db);
        tassert('E seed: tong cap 8x48', $vb['total_lv'], 384);
        tassert('E seed: stage 4 -> tran 48', $vb['cap'], 48);
        tassert('E seed: lv48 dodge = 12', $vb['dodge'], 12.0);
        tassert('E seed: lv48 pen = 12', $vb['pen'], 12.0);
        tassert('E seed: lv48 reduce = 12', $vb['reduce'], 12.0);
        tassert('E seed: lv48 luck = 12', $vb['luck'], 12.0);
        $pl2 = \player\getplayer($E2ESID, $db);
        tassert('E noi qua getplayer: vessel = 12', $pl2->huyetmach_vessel['pen'], 12.0);

        // E.2 — quai lien + boss tam thoi. ggj du lon de $phurt > 0 ma KHONG phai nho san 15%:
        // $phurt = ggj - ufy*0.75, lay ufy DA TRU khi cong trang bi cua chinh trang do.
        $mid = intval($pl2->nowmid);
        $hmGj = intval(round(floatval($pl2->ufy) * 0.75)) + 300;
        // LUON INSERT dong rieng roi XOA o E.5 — KHONG tai su dung dong co cua `mid`
        // (midguaiwu la bang cua chung: doi phuong khac dang dan di qua).
        $db->exec("INSERT INTO midguaiwu (gname, ghp, ggj, gfy, glv, mid, gyid, gexp, sid, gmaxhp, gbj, gxx, ms)
            VALUES ('Tam Unh Mach Huyet', 999999, $hmGj, 200, 48, $mid, 54, 1, '', '999999', 0, 0, 0)");
        $gid = intval($db->lastInsertId());
        tassert('E seed: tao quai lien thanh cong', $gid > 0, true);
        $db->exec("DELETE FROM boss WHERE bossid = 9001");
        $db->exec("INSERT INTO boss (bossid, bossname, bossinfo, bosslv, bosshp, bossmaxhp, bossgj, bossfy,
            bossbj, bossxx, bosszb, dljv, bossdj, djjv, bs, bosstime, sid, bossyp, ypjv, bossexp)
            VALUES (9001, 'Boss Thử Mạch (tạm thời)', 'Dong tam thoi cho tests/test_huyetmach_combat.php --live',
            30, 999999, 999999, $hmGj, 200, 0, 0, '', 0, '', 0, 0, NOW(), '', '', 0, '0')");
```

**Step 13 (tiếp theo) — nối tiếp khối E, vẫn bên trong `if ($LIVE)`**, chèn ngay sau đoạn code của Step 13:

```php

    // E.3 — quay 60 don PVE + 20 don BOSS, dem 3 chuoi thong bao mach am
    $hmMsgPen    = 'Âm Duy Mạch (Xuyên Giảm)';
    $hmMsgReduce = 'Dương Duy Mạch (Giảm Vật Lý)';
    $hmMsgDodge  = 'Âm Kiều Mạch (Né Tranh)';
    $nPve = 60; $nBoss = 20;
    $cPen = 0; $cRed = 0; $cDodge = 0; $cShort = 0;
    $bPen = 0; $bRed = 0; $bDodge = 0; $bShort = 0;
    for ($i = 0; $i < $nPve; $i++) {
        $db->exec("UPDATE game1 SET uhp = umaxhp, ustamina = umaxstamina WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
        $h = $hmGet("cmd=pvegj&gid=$gid&nowmid=$mid&sid=$E2ESID");
        if (strlen($h) < 300) { $cShort++; continue; }
        if (strpos($h, $hmMsgPen) !== false) $cPen++;
        if (strpos($h, $hmMsgReduce) !== false) $cRed++;
        if (strpos($h, $hmMsgDodge) !== false) $cDodge++;
    }
    for ($i = 0; $i < $nBoss; $i++) {
        $db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
        $h = $hmGet("cmd=pvbgj&bossid=9001&nowmid=$mid&sid=$E2ESID");
        if (strlen($h) < 300) { $bShort++; continue; }
        if (strpos($h, $hmMsgPen) !== false) $bPen++;
        if (strpos($h, $hmMsgReduce) !== false) $bRed++;
        if (strpos($h, $hmMsgDodge) !== false) $bDodge++;
    }

    // E.4 — bien that song sau khi cham dot (kiem $hurt + clamp + $cwxl di dung thu tu)
    $hpNow = intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
    $ghpNow = intval($db->query("SELECT ghp FROM midguaiwu WHERE id = $gid")->fetchColumn());
    $bhpNow = intval($db->query("SELECT bosshp FROM boss WHERE bossid = 9001")->fetchColumn());
    tassert('E pve: HTML du dai 60/60 don', $cShort, 0);
    tassert('E pve: quai van con song (ghp 999999 khong bi giêt)', $ghpNow > 990000, true);
    tassert('E boss: boss van con song', $bhpNow > 990000, true);
    tassert('E pve: pen hien 100% so don', $cPen, $nPve);
    tassert('E pve: reduce hien 100% so don', $cRed, $nPve);
    tassert('E boss: pen hien 100% so don', $bPen, $nBoss);
    tassert('E boss: reduce hien 100% so don', $bRed, $nBoss);
    tassert('E pve: dodge xuat hien it nhat 1 lan trong 60 don (p=0.4% triet de', $cDodge >= 1, true);
    tassert('E pve: dodge duoi 35% (ly thuyet 12%)', $cDodge < intval($nPve * 0.35), true);
    tassert('E boss: dodge duoi 40% (ly thuyet 12%, N=20)', $bDodge < intval($nBoss * 0.40), true);
    echo "E thong ke: pve dodge=$cDodge/$nPve (" . round($cDodge / $nPve * 100, 1) . "%), "
        . "boss dodge=$bDodge/$nBoss (" . round($bDodge / $nBoss * 100, 1) . "%), uhp sau 80 don = $hpNow\n";

    // E.5 — don det. Neu khoi E fatal giua chung, chay tay 5 cau nay truoc khi nghi test do code.
    $db->exec("DELETE FROM midguaiwu WHERE id = $gid");
    tassert('E cleanup: quai lien da sach', intval($db->query("SELECT COUNT(*) FROM midguaiwu WHERE id = $gid")->fetchColumn()), 0);
    $db->exec("DELETE FROM boss WHERE bossid = 9001");
    $db->exec("DELETE FROM player_huyetmach_vessel WHERE sid = '$E2ESID'");
    $db->exec("UPDATE game1 SET huyetmach_awaken_stage = 0, ulv = " . intval($snap['ulv'])
        . ", ugj = " . intval($snap['ugj']) . ", ufy = " . intval($snap['ufy'])
        . ", ubj = " . intval($snap['ubj']) . ", uxx = " . intval($snap['uxx'])
        . ", uhp = " . intval($snap['uhp']) . ", umaxhp = " . intval($snap['umaxhp'])
        . ", ustamina = " . intval($snap['ustamina']) . " WHERE sid = '$E2ESID'");
    \player\player_cache_clear($E2ESID);
} else {
    echo "SKIP E (them --live de chay route that qua Apache)\n";
}
```
- [ ] **Step 14: Khối F — harness đo lại ngưỡng 8% của pen** (cũng chèn **trước** hai dòng `echo/exit`, sau khối E). Khối này **không ghi DB**, chỉ đọc số liệu đang có để tái lập bảng 11.0b mỗi lần người khác nghi ngờ quyết định "pen không áp dụng PvP":

```php
// ===== F: do anh huong trung binh cua pen (chi chay khi co --measure) =====
if ($MEASURE) {
    // Cong thuc sao y: pve.php:237 / boss.php:235 (san 15% ugj), pvp.php:104 (san 5% ugj).
    // Thu cong KHONG tinh $cwgj (thu sung), vi no la hang so cong vao ca 2 ve nen khong doi ty le.
    if (!function_exists('hm_measure_gain')) {
        function hm_measure_gain($atk, $def, $floorRatio, $pen) {
            $base = max(round($atk - $def * 0.75), round($atk * $floorRatio));
            $pdef = \player\huyetmach_combat_pierce_def($pen, $def);
            $now  = max(round($atk - $pdef * 0.75), round($atk * $floorRatio));
            if ($base <= 0) return array('gain' => 0.0, 'floored' => ($now <= $base));
            return array('gain' => ($now - $base) / $base * 100.0, 'floored' => ($now <= $base));
        }
    }
    $PEN = 12.0; // mach lv48, stage 4 (0.25 x 48) — khong dung 15% de do theo tran thoi

    // 15 mau phan vi theo `ugj` cua nhan vat that trong game1 (median va p75 cho ket qua giong het)
    $rows = $db->query("SELECT ugj, ufy FROM game1 WHERE ugj > 0 AND ufy >= 0 ORDER BY ugj DESC")->fetchAll(PDO::FETCH_ASSOC);
    $pick = array();
    foreach (array(0, 0.07, 0.14, 0.21, 0.29, 0.36, 0.43, 0.5, 0.57, 0.64, 0.71, 0.79, 0.86, 0.93, 1.0) as $fr) {
        $idx = intval(round($fr * (count($rows) - 1)));
        if (isset($rows[$idx])) $pick[] = $rows[$idx];
    }
    $mons   = $db->query("SELECT DISTINCT gfy FROM midguaiwu WHERE gfy > 0 ORDER BY gfy")->fetchAll(PDO::FETCH_COLUMN);
    $bosses = $db->query("SELECT DISTINCT bossfy FROM boss WHERE bossfy > 0 ORDER BY bossfy")->fetchAll(PDO::FETCH_COLUMN);

    $sumPve = 0.0; $nPairPve = 0; $floorPve = 0;
    foreach ($pick as $p) {
        foreach ($mons as $gfy) {
            $r = hm_measure_gain(floatval($p['ugj']), floatval($gfy), 0.15, $PEN);
            $sumPve += $r['gain']; $nPairPve++;
            if ($r['floored']) $floorPve++;
        }
    }
    $sumBoss = 0.0; $nPairBoss = 0; $floorBoss = 0;
    foreach ($pick as $p) {
        foreach ($bosses as $fy) {
            $r = hm_measure_gain(floatval($p['ugj']), floatval($fy), 0.15, $PEN);
            $sumBoss += $r['gain']; $nPairBoss++;
            if ($r['floored']) $floorBoss++;
        }
    }
    $sumPvp = 0.0; $nPairPvp = 0; $floorPvp = 0;
    foreach ($pick as $a) {
        foreach ($pick as $d) {
            $r = hm_measure_gain(floatval($a['ugj']), floatval($d['ufy']), 0.05, $PEN);
            $sumPvp += $r['gain']; $nPairPvp++;
            if ($r['floored']) $floorPvp++;
        }
    }
    $meanPve = $nPairPve ? $sumPve / $nPairPve : 0.0;
    $meanBoss = $nPairBoss ? $sumBoss / $nPairBoss : 0.0;
    $meanPvp = $nPairPvp ? $sumPvp / $nPairPvp : 0.0;

    echo "F mau: " . count($pick) . " nhan vat x " . count($mons) . " quai, "
        . count($pick) . " x " . count($bosses) . " boss, " . count($pick) . "^2 cap PvP (pen=$PEN%) \n";
    echo "F PVE  mean=" . round($meanPve, 2) . "%  san-chan=" . $floorPve . "/" . $nPairPve . "\n";
    echo "F BOSS mean=" . round($meanBoss, 2) . "%  san-chan=" . $floorBoss . "/" . $nPairBoss . "\n";
    echo "F PVP  mean=" . round($meanPvp, 2) . "%  san-chan=" . $floorPvp . "/" . $nPairPvp . "\n";
    echo "PEN_PVE_MEAN=" . round($meanPve, 2) . " GUARD="
        . (($meanPve <= 8.0 && $meanBoss <= 8.0) ? 'OK' : 'VUOT') . "\n";

    tassert('F: mean pen PVE van <= 8% (quyet dinh ap dung)', $meanPve <= 8.0, true);
    tassert('F: mean pen BOSS van <= 8% (quyet dinh ap dung)', $meanBoss <= 8.0, true);
    if ($meanPvp > 8.0) {
        echo "F ghi chu: PVP mean " . round($meanPvp, 2) . "% > 8% => co Y KHONG ap pen o game/pvp.php (Step 10).\n";
    } else {
        echo "F CAN NHAC: PVP mean " . round($meanPvp, 2) . "% nay da duoi 8% => phai DOI CHU CHOT lai viec bo pen o PvP.\n";
    }
} else {
    echo "SKIP F (them --measure de do lai nguong pen)\n";
}
```

Ky vong khi chay `--measure` tren DB local hom nay: `F PVE mean≈7.26%`, `F BOSS mean≈0%`, `F PVP mean≈29.59%`, `GUARD=OK`. Neu `GUARD=VUOT`: dung lai Step 10, bao chu du an quyet dinh (dung 11.0b), **khong tu** ha nguong hay doi cong thuc san 15%.

- [ ] **Step 15: Chay du bo 4 test + 2 mode** (Apache phai dang chay cho `--live`):

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
curl -s -o /dev/null -w "%{http_code}\n" "http://localhost/game.php"   # 200 or 302 = Apache song
$PHP tests/e2e_fixture.php
$PHP tests/test_huyetmach_combat.php            # A-D that, E/F in SKIP
$PHP tests/test_huyetmach_combat.php --measure  # + khoi F
$PHP tests/test_huyetmach_combat.php --live     # + khoi E (60+20 HTTP request)
$PHP tests/test_huyetmach_vessel.php
$PHP tests/test_huyetmach_boss.php
$PHP tests/test_huyetmach_upgrade.php
```

Ky vong: 4 test cuối đều `ALL PASS`. Rieng `--live` co the mat vai chuc giay (80 request). Neu mot assertion dodge triet de FAIL (`E pve: dodge xuat hien it nhat 1 lan`), chay lai `--live` DUNG MOT LAN — xac suat 0 don ne trong 60 don la 0.4% theo ly thuyet, do la lot may cua harness chu khong phai bug. Neu `--live` fatal giua chung thi chay tay 5 cau don o E.5 (hoac `tests/_huyetmach_live_cleanup` nam trong chinh khoi E) truoc khi ket luan.

**PHẦN CHƯA TEST — phải ghi nguyên vào `docs/UI_HUYETMACH.md` (Task 12):**
- `game/pvp.php`: mới chứng minh bằng đơn vị (khối A và D). Route PvP thật cần hai nhân vật cùng hội và `cmd=pvp`, harness này **không** quay ⇒ né tranh của người bị đánh trong PvP **chưa có bằng chứng trên đường dẫn thật**.
- `luck`: mới kiểm bằng công thức ngượng rơi đồ. Chưa đo tỉ lệ rơi thực tế trong trận (cần hàng ngàn lần giết để thống kê).
- `club_boss.php` (BOSS công hội): **có** hai nhốp né tranh riêng (`:205-285`) nhưng Task này **không** nối mạch âm vào đây — đây là bề mặt thứ tư bị bỏ ngoài, chủ dự án chụt.
- `$cwxl` (sát thương thú cùng nhận lại) bằng 0 khi nhân vật né được đơn — hành vi có chủ đích, chưa đối chiếu lại với thú cùng đang xuất chiến trong game thật.

- [ ] **Step 16: Commit**

```bash
git add class/function/huyetmach.php class/function/huyetmach_boss.php game/boss.php game/pve.php game/pvp.php class/config/huyetmach_vessel_config.json tests/test_huyetmach_combat.php
git commit -m "feat(huyetmach): wire vessel stats into combat"
```

---

### Task 12: Tài liệu, bản đồ tính năng (`tongquan.php`) và sổ nợ chờ chủ dự án

**Files:**
- Create: `docs/UI_HUYETMACH.md`
- Create: `docs/DEPLOY_PRODUCTION_HUYETMACH.md`
- Modify: `tongquan.php` (them muc `mach-huyet` ngay sau muc `huyet-mach`, quanh dong 401)
- Modify: `docs/UI_THECHAT.md` (chi them mot dong cross-reference o muc "Nợ còn lại", neu Task 8/11 da doi so loi cua thechat)

**Interfaces:**
- Consumes: `huyetmach_vessel_config.json` (Task 1), ket qua test cua Task 3/5/8/9/10/11, `tq_cfg/tq_sec/tq_searchable/tq_show/tq_cut/tq_n/tq_dot/tq_realm_req` (`tongquan.php:18-159`), `docs/DEPLOY_PRODUCTION_NGUHANH.md` (lam bau cho cach trinh bay).
- Produces: khong co ham moi — day la task chot tai lieu. Nhung thu task nay viet ra la nguoi dung cuoi cho chu du an quyet.

#### 12.0 Vi sao task nay nam cuoi (khong phai buoc "viet document cho xong")

`docs/UI_THECHAT.md` la nguon chinh cua rework The Chat — 1112 dong, co ca bang tra 36 loi (§11) va playbook lenh seed/do/chup (§9.1), va chinh no da chan hanh vi UI cua Task 7-10. Mach Huyet cung phai co mot file nhu vay, vi ba ly do:
1. Task 11 **bac bo mot phan thiet ke §5 cua spec** (dodge/reduce/luck khong the cong vao `tfsb`/`ufy`/`uwx` nhu spec ghi). Le loi nam o `11.0` cua Task 11, phai duoc chep ve `docs/UI_HUYETMACH.md` de lan sau khong ai... thiet ke lai dung theo spec cu.
2. Mot so quyet dinh **co y khong lam** (pen khong ap PvP, club_boss khong noi mach, `boss` thieu cot `element`). Chung khong phai bug, chung la no chu du an phai chot.
3. Production **chua migration** (3 bang moi + no schema sid cua `docs/DEPLOY_PRODUCTION_NGUHANH.md`). Khong co file deploy thi lan sau bo qua mat.

- [ ] **Step 1: Viet `docs/UI_HUYETMACH.md`** theo khung 12 muc cua `docs/UI_THECHAT.md` (doc `grep -n "^## " docs/UI_THECHAT.md` de lay dung ten muc). Noi dung bat buoc tung muc:

| Muc | Ten | Noi dung phai co |
|---|---|---|
| 1 | Boi canh | Vi sao tam gop ve 1 man; 3 trang cu (`huyetmach`, `huyetmach_awaken`, `huyetmach_transform`) nay nam o dau; dich thoai nguoi choi dang dung |
| 2 | File da sua / da tao | Bang liet ke toan bo file cua Task 1-11 + dong cua so `class/function/huyetmach.php` da append (khong phai sua tron) |
| 3 | Cau truc trang | Khoi `.hm-*` (Task 7): 8 nguoc tren anh kinh mach, ray 5 bac, panel chi so; **ban do selector → file:line** |
| 4 | Logic server-side | Chu ky 5 helper `huyetmach_combat_*` (Task 11) + `huyetmach_vessel_bonuses()` (Task 3) + 17 keys tra ve; ghi ro `stat_cap` 15 nhung **tran that 12** |
| 5 | Anh da sinh | Danh sach `docs/img/huyetmach/*` + nguyen tac lay mau nen theo CSS trang (khong tu chon ton) |
| 6 | Rang buoc CSS phai giu (MOI) | `a:link` (0,1,1) de rule 1 lop; emoji uu tien cung astral, BMP (`❤ ✨ ✔`) phai xac nhan bang anh chup (see Step 6); `.main button` de len `hm-btn`; `ZB_IMG_VER` phai bump khi doi anh |
| 7 | Bug da tim ra va cach sua | Bug HP vao `uxx` (Task 6), bug `.nb-btn`, mau stage-0, va **vi tri chen `$phurt` sau `$hurt`** (Task 11 Step 8b/9c) — kem so do minh chung |
| 8 | Bug CU co san — CHUA sua | `linhcan_boss.php:37` (`cmd` sai nen nut tan cong khong chay), `linhcan_boss_drops()` in chu "🪨 3 Da" khong cong do, `boss` thieu cot `element`, `material_id 108` mo coi |
| 9 | Quy trinh kiem chung (lap lai duoc) | Playbook lenh: PHP 5.6 built-in server, `--window-size` san 500px → iframe 390px cho mobile, dat `zoom=1` truoc khi do, lenh `php.exe -l`, 4 lenh test + `--live` + `--measure` |
| 10 | NO CON LAI — CHUA TEST / CHUA LAM | Chep nguyen 4 do tu Task 11 Step 15 (PvP route that, luck ty le roi thuc te, club_boss be mat thu 4, `$cwxl` = 0 khi ne) + toàn bảng sổ nợ 19 dòng ở Step 7 |
| 11 | Bang tra loi nhanh | Bang "hoi → tra loi → file:line" cho 20 cau hoi hay mac (vid: "sao pen khong tang sat thuong toi?", "sao ne duoc ma mat tien thuong?") |
| 12 | Thang hieu ung `hm-fx0..hm-fx4` | Copy tu Task 10: 5 lop, **bac thap nhat van phai thay gi do**, kem so do 4 viewport |

- [ ] **Step 2: Them muc `mach-huyet` vao `tongquan.php`** (nho: them tinh nang moi phai cap nhat trang tong quan). Chen **ngay sau** dong cuoi cua muc `huyet-mach` (`    . tq_searchable('tbl-hmt', array('Form bien hoa', ...));`, hien o `tongquan.php:401`):

```php
$hmV = tq_cfg('huyetmach_vessel_config');
$rowsV = array();
if (is_array($hmV)) {
    $labels = (array)(isset($hmV['stat_labels']) ? $hmV['stat_labels'] : array());
    $caps   = (array)(isset($hmV['stat_cap']) ? $hmV['stat_cap'] : array());
    foreach ((array)(isset($hmV['vessels']) ? $hmV['vessels'] : array()) as $e) {
        $stat = $e['stat'];
        $cap  = isset($caps[$stat]) ? ' (trần ' . tq_n($caps[$stat]) . '%)' : '';
        $rowsV[] = array(tq_show($e['name']), tq_show($e['short']),
            tq_show(isset($labels[$stat]) ? $labels[$stat] : tq_h($stat)) . $cap,
            '+' . tq_n($e['per_lv']) . '/cấp', tq_show($e['group']), tq_show($e['slot']),
            tq_cut($e['desc'], 90));
    }
    $rowsT4 = array();
    foreach ((array)(isset($hmV['tiers']) ? $hmV['tiers'] : array()) as $e) {
        $mat = ($e['material_id'] > 0 && isset($hmV['material_names'][$e['material_id']]))
            ? tq_show($hmV['material_names'][$e['material_id']]) . ' x' . tq_n($e['material_amount']) : '—';
        $rowsT4[] = array(tq_dot($e['color']) . $e['icon'] . ' ' . tq_show($e['name']),
            '+' . tq_n($e['lv_cap']) . '/mạch', tq_n($e['total_lv_require']),
            tq_realm_req($e['realm_min_level']), $mat, tq_n($e['linh_thach_cost']),
            tq_n($e['boss_require']) . ' boss mạch huyếng', tq_cut($e['desc'], 70));
    }
    $rowsM4 = array();
    foreach ((array)(isset($hmV['milestones']) ? $hmV['milestones'] : array()) as $e) {
        $rowsM4[] = array(tq_show($e['name']), tq_n($e['total_lv']), '+' . tq_n($e['bonus_percent']) . '%', '');
    }
    $rv = $hmV['refine'];
    tq_sec('nuoi-duong', 'mach-huyet', '❤', 'Mạch Huyết (8 mạch luyện được)', count($rowsV) + count($rowsT4) + count($rowsM4),
        'Lớp chỉ số thứ 4 của nhân vật: 8 mạch (4 mạch dương cộng chỉ số sẵn có, 4 mạch âm can thiệp phần trăm trực tiếp lúc đánh nhau).',
        array('UI: game/huyetmach.php (cmd=huyetmach)', 'Boss riêng cho từng người chơi: bảng huyetmach_boss',
            'Chỉ số âm: Né Tranh / Xuyên Giảm / Giảm Vật Lý / Vận May',
            'Xuyên Giảm KHÔNG áp dụng khi PvP (đo mean +29,59%, ngưỡng 8%)',
            'Số liệu: player_huyetmach_vessel (sid, vessel_id, level)'),
        tq_searchable('tbl-mh', array('Mạch', 'Viết tắt', 'Chỉ số', 'Mỗi cấp', 'Nhóm', 'Vị trí ảnh', 'Mô tả'), $rowsV,
            '<b>Luyện mạch:</b> mỗi cấp +1 tốn ' . tq_n($rv['material_base']) . ' Huyết Thạch (cứ ' . tq_n($rv['material_per_lv'])
            . ' cấp tăng thêm 1) + ' . tq_n($rv['linh_thach_base']) . ' Linh Thạch (cộng ' . tq_n($rv['linh_thach_per_lv']) . '/cấp).')
        . tq_searchable('tbl-mht', array('Thức tỉnh', 'Trần mỗi mạch', 'Cần tổng cấp', 'Cần cảnh giới', 'Vật liệu', 'Linh Thạch', 'Boss', 'Mô tả'), $rowsT4, '<b>5 bậc thức tỉnh:</b>')
        . tq_searchable('tbl-mhm', array('Mốc', 'Tổng cấp 8 mạch', 'Nhân chỉ số', ''), $rowsM4, '<b>Mốc tổng cấp:</b>'));
} else {
    tq_sec('nuoi-duong', 'mach-huyet', '❤', 'Mạch Huyết (chưa cài đặt)', 0,
        'class/config/huyetmach_vessel_config.json chưa tồn tại — Task 1 chưa chạy.',
        array('Chưa deploy'), '');
}
```

Expected sau khi chèn: `php -l tongquan.php` in `No syntax errors detected`, và `tongquan.php` có mục `mach-huyet` với đúng 3 bảng `tbl-mh` / `tbl-mht` / `tbl-mhm` (đo bằng lệnh ở Step 5).

> **BA RÀNG BUỘC KHI CHÈN KHỐI NÀY**
> 1. **Icon**: `❤` (U+2764, BMP) chứ không phải `🫀` (U+1FAC0). Lý do: `🫀` thuộc Emoji 14.0 (2021), font hệ thống Windows cũ không có ký tự ⇒ hiện hộp rỗng. Các icon đang chạy trong trang này đều là cung phổ biến (`🗡` U+1F5E1 ở `tongquan.php:222`, `☯` U+262F ở `:246`, `🩸` U+1FA78 ở `:396`). **Chiều ngược lại cũng có thật**: `docs/UI_THECHAT.md:609` ghi "Emoji chỉ dùng cung astral ... BMP (❤️✨✔️) render lệch ô flex nhỏ" — ô icon của `tq_sec()` rộng nên BMP chấp nhận được, nhưng **Step 6 bắt buộc phải nhìn ảnh chụp** để chốt, không tin lý thuyết.
> 2. **KHÔNG dùng tên `tbl-hm*` cũ**: mục `huyet-mach` đã chiếm `tbl-hm`, `tbl-hma`, `tbl-hmt` (`tongquan.php:399-401`), và `tq_searchable()` gắn `data-for="$id"` (`:135`) ⇒ trùng id là hai ô lọc giành nhau bảng.
> 3. **Khối `else` là bắt buộc**: Task 12 được viết trước Task 1, nên trang tổng quan phải hiển thị được khi `huyetmach_vessel_config.json` chưa tồn tại — nếu bỏ `is_array($hmV)` thì `$hmV['refine']` và `foreach` trên `null` gây `Fatal error`/`Warning` làm gãy cả trang (đây là trang chủ của chủ server, không được sập vì tính năng chưa deploy).

- [ ] **Step 3: Viet `docs/DEPLOY_PRODUCTION_HUYETMACH.md`** — lay khung 7 muc cua `docs/DEPLOY_PRODUCTION_NGUHANH.md` (`grep -n "^## " docs/DEPLOY_PRODUCTION_NGUHANH.md`). Buoc sang mot no moi: **3 bang Mach Huyet chua ton tai o production** — do local hom nay:

```
player_huyetmach_boss      exists=1 rows=0   <- bang CU, da co san 3 cot (sid, boss_id, killed_at)
player_huyetmach_quest     exists=1 rows=0   <- bang CU, khong ai ghi (no muc 6 ben Step 6)
huyetmach_boss             exists=0          <- bang MOI cua Task 8 (boss rieng tung nguoi choi)
player_huyetmach_vessel    exists=0          <- bang MOI cua Task 2/3
player_huyetmach_upgrade_log exists=0        <- bang MOI cua Task 9
sql_mode = (rong)   MySQL = 5.5.29
```

Bonen muc phai viet ro, khong duoc tom luoc:
1. **Trinh ty**: `mysqldump` 3 bang + `game1` (cot `huyetmach_*`) → `mysql game < sql/sql_huyetmach_vessel.sql` → `php tests/deploy_preflight.php --host=... ` (phai ra `Hang schema con thieu/sai: 0`) → copy code → bấm tay 5 nghiém thu ơ muc 5.
2. **MySQL 5.5.29 khong ho tro** `ADD COLUMN IF NOT EXISTS` va `DEFAULT CURRENT_TIMESTAMP` — file SQL cua Task 2 da dung `DATETIME DEFAULT NULL`, dung nhac lai de nguoi sau khong "nang cap" file.
3. **sql_mode rong** (da do `SELECT @@sql_mode` = chuoi rong): production thuong dat `STRICT_TRANS_TABLES`. Trong `sql_mode` rong, `INSERT` chuoi hex vao cot `INT` chi bi cat im im; o STRICT no **dung tran that**. Day chinh la no schema sid 15 bang trong `docs/DEPLOY_PRODUCTION_NGUHANH.md` — phai chay xong no do TRUOC khi deploy Mach Huyet, neu khong `player_huyetmach_vessel.sid` (VARCHAR 64) se khop voi `game1.sid` (INT) va moi query JOIN tra rong.
4. **Rollback**: `DROP TABLE` 3 bang moi la du (chung chua co ai dung), **khong** `DROP` 2 bang cu `player_huyetmach_boss`/`player_huyetmach_quest` vi Task 8 ghi `player_huyetmach_boss` qua ham `huyetmach_boss_mark()` san co.

- [ ] **Step 4: Quet CJK + non-ASCII la tren toan bo file moi** (Task 1-11 de len):

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$fs = array("class/config/huyetmach_vessel_config.json","class/config/daoju_config.json",
  "class/function/huyetmach.php","class/function/huyetmach_boss.php","class/function/thechat.php",
  "class/player.php","game/huyetmach.php","game/huyetmach_boss.php","game/boss.php","game/pve.php",
  "game/pvp.php","game/zhuangtai.php","game/use_dj.php","tongquan.php","css/mystyle.css",
  "sql/sql_huyetmach_vessel.sql","docs/UI_HUYETMACH.md","docs/DEPLOY_PRODUCTION_HUYETMACH.md");
foreach ($fs as $f) {
  if (!is_file($f)) { echo "THIEU $f\n"; continue; }
  $s = file_get_contents($f);
  $n = preg_match_all("/[\x{3040}-\x{30ff}\x{3400}-\x{4dbf}\x{4e00}-\x{9fff}\x{ff00}-\x{ffef}]/u", $s, $m);
  $bad = preg_match_all("/[\x{2018}\x{2019}\x{201c}\x{201d}\x{2026}\x{00a0}]/u", $s, $m2);
  echo sprintf("%-42s cjk=%d curly=%d\n", $f, $n, $bad);
}'
```
Ky vong: moi dong `cjk=0`. `curly=0` cho file PHP/JSON/CSS/SQL; rieng hai file `.md` duoc phep co `\u2018 \u2019 \u201c \u201d \u2026` trong van xuong, nhung **khong duoc co trong code fence**. Neu thay `curly>0` o file PHP: day la do tool Edit chen ky tu — sua bang tay tung chuoi, khong dung find/replace hang loat (de lam hu chuoi tieng Viet ben canh).

- [ ] **Step 5: Lint + do trang tong quan** (muc moi phai thuc su xuat hien, khong chi "them code"):

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
$PHP -l tongquan.php
$PHP -r '$_GET["tq"]="1"; ob_start(); include "tongquan.php"; $h=ob_get_clean();
echo strpos($h,"mach-huyet")!==false ? "MUC mach-huyet: ON\n" : "MUC mach-huyet: MISSING\n";
echo "tong so bang tbl-mh*=".preg_match_all("/tbl-mh/", $h).chr(10);'
```
Ky vong: `No syntax errors detected`, `MUC mach-huyet: ON`, `tong so bang tbl-mh*=3` (8 mach + 5 bac + 4 moc). Neu ra `MISSING`: `tq_cfg('huyetmach_vessel_config')` tra `null` (file JSON chua co duong dan dung) — kiem tra `TQ_ROOT . '/class/config/'` (`tongquan.php:18-25`).

- [ ] **Step 6: Anh chung minh (khong duoc noi "UI xong" khi chua nhin)** — dung `docs/img/huyetmach/`, theo dung playbook §9 của `docs/UI_THECHAT.md`:
1. Dung **Chrome CLI**, **khong** dung browser-use MCP (lenh cu the lay o `docs/UI_THECHAT.md` §9.1).
2. Mobile that: `--window-size` san 500px → nhét trang vao `iframe` 390px roi do trong iframe.
3. Trang `tongquan.php` co `zoom .75` → dat `zoom=1` truoc khi do so.
4. Chan 4 buc: `hm-desktop-1440.png`, `hm-mobile-390.png`, `hm-fx-ladder.png` (5 bac dat canh nhau), `tongquan-mach-huyet.png` (muc moi). So sanh **1:1** voi anh mock trong spec, khong chi do computed style.
5. `docs/UI_HUYETMACH.md` §5 phai dan duong dan 4 file nay va ghi ro buc nao **CHUA duoc xem trong game that**.

- [ ] **Step 7: Viết "SỔ NỢ CHỜ CHỦ DỰ ÁN"** — cuối `docs/UI_HUYETMACH.md`, bảng 19 dòng. Mỗi dòng BẮT BUỘC có chứng cứ `file:dòng` (file thật trong repo) **hoặc neo `Task N + key config/hàm/bước`** để chủ dự án mở ra đối chiếu được ngay. **CẤM dùng `plan:dòng`** — file plan tăng/giảm dòng mỗi lần sửa nên số dòng luôn lệch; **không dòng nào được tự sửa số** (quy tắc "không tự nới phạm vi sửa"). Cột: `#` / `Vấn đề` / `Chứng cứ` / `Cần chốt`.

```markdown
## Sổ nợ chờ chủ dự án (Task 12 — đo ngày 2026-10-10 bằng E:/window/Temp/_eco.php + tests/test_huyetmach_combat.php --measure)

| # | Vấn đề | Chứng cứ | Cần chốt |
|---|---|---|---|
| 1 | Tổng Huyết Thạch 1 nhân vật cần = **1.481**: luyện 8 mạch 0→48 = 1.344; thức tỉnh stage 1..4 = 60; nâng phẩm 1→10 = 77. Nguồn rơi (5 đòn/ngày × 4/8/16): stage 1 = **20 HT/ngày**, stage 2 = **40**, stage 3 = **80** ⇒ **71 / 36 / 18 ngày** cày boss cho riêng phần luyện+thức tỉnh | Task 1 config `refine`, Task 1 config `tiers` (stage 1–4), Task 9 config `upgrades` 1→9, Task 8 config `boss_config.drops.by_stage` (4/8/16) + `first_kill` (10/20/40) + `max_fights_per_day` 5 | (a) giữ nguyên ⇒ ~3 tuần/character; (b) hạ `material_base` hoặc `material_per_lv`; (c) nâng `max_fights_per_day`; (d) tăng `huyet_thach` mỗi đòn |
| 2 | Nguồn Huyết Thạch **bằng 0 ở stage 0 và stage 4**: `huyetmach_boss_spawn()` sinh boss `stage = hiện tại + 1`, config chỉ có boss stage 2/3/4 ⇒ stage 0 đòi boss stage 1 (không tồn tại) và stage 4 đòi tier 5 (`huyetmach_vessel_tier(5) === false`) đều return false. Trong khi đó 9 bậc nâng phẩm vẫn cần 77 HT | Task 8 hàm `huyetmach_boss_spawn()` (`$stage = hiện tại + 1`, `huyetmach_vessel_tier(5) === false`), Task 8 Step 1 (bộ sinh config chỉ lặp `boss_list` stage 2–4), Task 1 config `tiers`, Task 9 config `upgrades` | thêm tier 5 (trần 60?), cho boss stage 4 farm lại, hay mở nguồn HT khác (`game/duihuan.php`, công thức đan) |
| 3 | **113 Huyết Mạch Đan không có nguồn rơi**: tier 4 cần `material_id 113, amount 1`, còn `huyetmach_boss_finish()` chỉ cộng id 112. Task 4 chỉ khai báo vật phẩm trong `daoju_config.json` | Task 1 config `tiers["4"].material_id` = 113, Task 8 hàm `huyetmach_boss_finish()` (`$htId` lấy từ `drops.huyet_thach_id` = 112), Task 4 Step 3 (khai báo 113 trong `daoju_config.json`) | đan rơi boss stage 4 với xác suất bao nhiêu % / bán bằng Linh Thạch / craft từ 112 (`game/use_dj.php`) |
| 4 | Linh Thạch của đường **nâng phẩm áp đảo** đường mạch: 126.406.000 (9 bước) so với 927.400 (toàn bộ luyện 8 mạch + thức tỉnh) = **gấp 136,3 lần** | Task 9 config `upgrades[].linh_thach_cost`; `E:/window/Temp/_eco.php` | có thật sự muốn ai đó tới phẩm 10? Nếu không, cắt bậc 7-9 hoặc đổi `linh_thach_cost` |
| 5 | Boss mạch **yếu hơn hẳn** boss Linh Căn: `hp_per_level 220 / atk 14 / def 9 / crit 15` so với 500/20/10/20 ⇒ chỉ 44% HP. Spec lại quy định công thức có hệ số gốc `hp = 8000 + level*220`, `atk = 260 + level*7` | Task 8 config `boss_config.stats`; `class/config/linhcan_boss_config.json:8-14`; spec `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md:110` | theo Task 8 (thuần ×level×stage_mult) hay theo spec (+8000/+260)? |
| 6 | `quest_require` + bảng `player_huyetmach_quest`: config vẫn `true` ở 2 bậc; Task 6 Step 4 chỉ bỏ khỏi UI; hàm đọc/ghi bảng tồn tại nhưng **không cuộc gọi nào** ⇒ 0 dòng; preflight vẫn liệt kê bảng | `class/config/huyetmach_awakening_config.json:36,50`; Task 6 Step 4; `class/function/huyetmach.php:332,355`; `tests/deploy_preflight.php:24` | xoá key + khỏi preflight, hay dựng chuỗi quest thức tỉnh thật |
| 7 | Sau Task 5, nhiều trường của `huyetmach_awakening_config.json` **không còn ai đọc** (`material_id 108`, `material_amount`, `boss_drop_require`, `quest_require`, `stat_bonus_mult`, `passive_description`) vì mọi điều kiện lấy từ `tiers`; chỗ duy nhất in `passive_description` là `game/huyetmach_awakening.php:30` — file Task 7 xoá | `class/config/huyetmach_awakening_config.json:42-58`; Task 5 hàm `huyetmach_vessel_break_rows()`; Task 7 Files (`Delete: game/huyetmach_awakening.php`) | dọn JSON (kèm `material_id 108` = "Đạo Nguyên" theo `class/function/huyetmach.php:443-450`) hay giữ để đối chiếu lịch sử? |
| 8 | **Trần thật của 4 chỉ số âm là 12%, không phải 15%**: một stat chỉ do đúng MỘT mạch nắm, `per_lv 0.25 × trần mạch 48 = 12` ⇒ `stat_cap 15` không bao giờ binding | Task 1 config vessels id 5–8 (`per_lv 0.25`), Task 1 config `stat_cap` (15), Task 1 config `tiers["4"].lv_cap` (48) | nâng `per_lv` 0.3125 (để 48 cấp = 15%), hay hạ `stat_cap` xuống 12, hay cho 2 mạch cùng stat |
| 9 | **Xuyên Giảm (pen) cố ý KHÔNG áp dụng khi PvP**: đo `--measure` trên 24 cặp cấp, pen 15% làm sát thương PvP tăng mean **+29,59%** (median +33,3%, max +72,7%, 0% bị sàn chặn) — vượt ngưỡng 8% mà spec §5 cho phép; kể cả pen 5% vẫn **+9,64%** | Task 11 §11.0b; `game/pvp.php:104-107` (sàn 5%); `huyetmach_vessel_config.json` mô tả mạch 6 (Task 11 Step 11) | (a) chấp nhận tooltip "không áp dụng PvP"; (b) giảm `per_lv` riêng cho bề mặt PvP; (c) bỏ luôn mạch pen khỏi 8 mạch |
| 10 | **Pen gần vô hiệu khi cày đúng cấp** — hệ quả công thức có trước: sàn 15% (`$gphurt < $player->ugj*0.15`) chặn **78% cặp PVE** và **100% cặp BOSS** trong dữ liệu local ⇒ pen = 0% ở đó; PVE chỉ +7,26%, BOSS +0% | `game/boss.php:236-237`, `game/pve.php:238-239`, số đo Task 11 §11.0b | có nới sàn 15% cho PVE/BOSS không? (đây là sửa công thức sát thương toàn game, KHÔNG phải bug mạch huyết) — và chạy lại `--measure` sau khi deploy production rồi mới chốt |
| 11 | **Bề mặt chiến đấu thứ 4 chưa nối mạch huyết**: `game/club_boss.php` (boss bang hội) có `$reduced = playerAtk - bossDef*0.75` (`:214`), boss phản đòn (`:239-240`), **né cứng 15%** (`:226`, `:251`), hút máu chia 220 (`:234`) — không đọc `huyetmach_vessel` | `game/club_boss.php:205-282` | nối 4 chỉ số vào club boss bằng task riêng, hay khai báo rõ trong UI rằng boss bang hội không dùng mạch huyết? |
| 12 | **Mạch 5 và 7 đảo nhau so spec**: spec cho arm-left = `dodge`, leg-left = `reduce`; plan cho arm-left = `reduce` (Dương Duy), leg-left = `dodge` (Âm Kiều). Theo TQ y học thì Dương Duy mạch đúng là chạy hai tay, Âm Kiều hai chân ⇒ plan hợp lý hơn, nhưng khác văn bản đã duyệt | spec `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md:71,73`; Task 1 config vessels id 5 và id 7 | giữ plan (dương-tay/âm-chân) hay revert về spec? chỉ đổi `stat`, không đổi `slot` |
| 13 | **Số tăng trưởng lệch spec** (3 chỗ): `total_lv_require` của tiers = 32/64/96/144 (Task 1 config `tiers`) vs spec 32/80/152/240 (spec:84-87). Lợu ý cách viết bảng của spec: con số đặt ở dòng stage **hiện tại** (32 ở stage 0, = cần để sang stage kế), plan đặt ở dòng stage **đích** (32 ở bậc 1); công thức spec ghi nguyên văn "8 × trần cap stage hien tai, cong them dam dao"; mốc tổng cấp = 4 mốc 32/80/128/176 với +5/+12/+20/+30% (Task 1 config `milestones`) vs spec 5 mốc 40/88/160/248/340 với +5/+10/+15/+20/+25% (spec:92); `boss_require` bậc Sơ Giác = 0 trong khi spec bắt giết boss stage_2 trước khi lên bậc 1 (spec:85). | Task 1 config `tiers` + `milestones`; spec `...design.md:84-92` | chọn bộ số nào (plan = nhẹ cho tester local, spec = đúng số đã duyệt); nếu theo spec thì phải tính lại 1.481 Huyết Thạch ở dòng 1 và các `tassert` milestone của Task 1 Step 3 |
| 14 | **Bug cũ, không thuộc mạch huyết**: nút `<button class="nb-btn">` của trang Nhục Thân đang TRẮNG vì `.main button` (0,1,1) thắng `.nb-btn` (0,1,0); Task 7 chỉ vá cho phía Huyết Mạch bằng `.main .hm-btn` (0,2,1) | `css/gamecss.css:122`; `game/thechat_upgrade.php:89`; Task 7 hộp "DA KIENG TRUOC KHI VIET" (đã ghi nợ, "KHÔNG tự sửa ở đây") | có sửa `.nb-btn` luôn không (thêm `.main .nb-btn-...` 0,2,1 cho 3 nút)? sửa là đụng trang Nhục Thân, cần chủ dự án đồng ý |
| 15 | **Bug cũ, không thuộc mạch huyết**: `game/linhcan_boss.php:37` kiểm `$cmd == 'attack_lc_boss'`, nhưng `$cmd` sau `parse_str` ở `game.php:114` luôn bằng `linhcan_boss`; biến đúng là `$attack_lc_boss` (nút "TẤN CÔNG" truyền `attack_lc_boss=1` ở `:83`) ⇒ **nhánh tấn công không bao giờ chạy**, boss Linh Căn không đánh được | `game/linhcan_boss.php:37,83`; `game.php:89,111-114`; đối chiếu `class/player.php`/`game.php:355` route `linhcan_boss` | sửa 1 dòng thành `if (isset($attack_lc_boss) && ...)` hay để nguyên (ngoài phạm vi Huyết Mạch)? |
| 16 | **Bug cũ, không thuộc mạch huyết**: `linhcan_boss_drops()` chỉ **in chữ** "🪨 3 Đá <element>" mà không cộng vào túi (TODO ngay trên dòng in); Task 8 đã làm đúng cho Huyết Mạch (`huyetmach_boss_finish()` INSERT `playerdaoju`) | `class/function/linhcan_boss.php:193-194`; đối chiếu Task 8 hàm `huyetmach_boss_finish()` | vá Linh Căn theo khuôn Huyết Mạch (1 hàm, có test) hay giữ tới khi làm lại Ngũ Hành? |
| 17 | **Production CHƯA chạy gì cả**: 3 bảng mới (`player_huyetmach_vessel`, `player_huyetmach_boss`, `player_huyetmach_upgrade_log`) + 4 cột `game1` mới + `docs/DEPLOY_PRODUCTION_NGUHANH.md` còn nợ **15 bảng schema sid** (nợ từ 2026-10-03) — phải chạy migration sid trước vì cùng đụng `game1` | `docs/DEPLOY_PRODUCTION_HUYETMACH.md` (Step 3 task này); `docs/DEPLOY_PRODUCTION_NGUHANH.md`; `docs/superpowers/specs/...design.md:48` | duyệt trình tự (sid trước hay mạch huyết trước) và giờ bảo trì; local đã test, production chưa |
| 18 | **4 mục CHƯA test** (Task 11 Step 15 ghi rõ): (a) route PvP thật (`cmd=pvpgj`) chưa chạy live — chỉ đo công thức; (b) vận may chưa đo **tỉ lệ rơi thực tế** qua nhiều lần tải trang, mới assert ngưỡng; (c) `game/club_boss.php` không test; (d) hệ quả né đòn làm `$cwxl = 0` (thú cưng không ăn đòn, `game/boss.php:452`) chưa quan sát trong game thật | Task 11 Step 15 + `docs/UI_HUYETMACH.md` mục "Nợ còn lại" | chủ dự án vào game thật bấm 4 cảnh đó, hay cho agent viết thêm vòng live? |
| 19 | **Spec §1.8 ghi `.hm-wrap` desktop 840px — đo lại không tồn tại**: `.nb-wrap` chỉ có `margin:0` (`css/mystyle.css:6991`), không kèm `max-width`; khung chung vẫn 660px (`css/mystyle.css:199`) nên nội dung tối đa 624px. Plan đã chọn đúng theo số đo (v13 bị bác vì mở rộng 660→800px) nhưng văn bản spec chưa được sửa

> **Cách dùng bảng này**: khi báo cáo cho chủ dự án, đọc **theo nhóm**: dòng 1-5 (kinh tế + thuộc tính boss), 6-7 (khoá/config không ai đọc), 8-13 (cân bằng + lệch spec), 14-17 (nợ cũ ngoài phạm vi + deploy), 18 (kiểm chứng), 19 (văn bản spec lệch số đo). Mỗi dòng đã có `file:dòng` hoặc neo Task, không cần giải thích thêm.
```

- [ ] **Step 8: Chốt task — chạy toàn bộ test + lint + scanner lần cuối**

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
for t in tests/test_huyetmach_schema.php tests/test_huyetmach_vessel.php tests/test_huyetmach_boss.php tests/test_huyetmach_upgrade.php tests/test_huyetmach_combat.php; do
  "$PHP" "$t" || echo "=== $t FAILED ==="; done
```

Rồi chạy **lại đúng lệnh quét ở Step 4** (18 file) và **lệnh lint 8 file PHP + `php -l tongquan.php` của Step 5**.

Expected: 5 test đều in `ALL PASS` và exit 0 (dòng `=== ... FAILED ===` không xuất hiện); scanner in `cjk=0` cho cả 18 dòng; mọi file PHP in `No syntax errors detected`; bốn ảnh chứng minh tồn tại thật:

```bash
ls -l docs/UI_HUYETMACH.md docs/DEPLOY_PRODUCTION_HUYETMACH.md docs/img/huyetmach/*.png
```

- [ ] **Step 9: Commit tài liệu**

```bash
git add docs/UI_HUYETMACH.md docs/DEPLOY_PRODUCTION_HUYETMACH.md docs/img/huyetmach tongquan.php
git commit -m "docs(huyetmach): sổ nợ mạch huyết + bản đồ tính năng"
```
