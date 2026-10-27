# Nạp Ngũ Hành (Đa Hệ) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Cho phép rèn nạp tối đa 5 thuộc tính ngũ hành vào từng món trang bị vật lý; món hợp linh căn mở nội tại cộng chỉ số theo nấc.

**Architecture:** Cột `element` mới trên `playerzhuangbei`/`fangshi_zb` (data theo món, config JSON giữ nguyên Plan Z). Engine rèn mới `class/function/nap_hanh.php` nối vào popup `zbinfo.php`. `getplayer()` đếm món hợp căn và cộng % trên phần chỉ số trang bị đóng góp; combat reuse pipeline `zb_element` sẵn có với bộ chọn hệ khắc chế mới.

**Tech Stack:** PHP 5.6.9 NTS (phpStudy), MySQL PDO, JSON configs, template H5 + vanilla JS.

**Spec:** `docs/DESIGN_NGUHANH_KICHHOAT.md` (bắt buộc đọc trước khi làm task nào).

## Global Constraints

- PHP **5.6**: cấm `??`, spread `...`, arrow fn, typed properties. Dùng `array()`, `isset()` với `? :`.
- KHÔNG sửa `class/config/zhuangbei_config.json` — mọi 180 record giữ `element:"none"`.
- KHÔNG đụng whitelist số trong `changezbsx()` (`class/player.php:1365`).
- Thứ tự hệ chuẩn mọi nơi: `kim,moc,thuy,hoa,tho`. Chuỗi lưu: `none` hoặc các key nối dấu phẩy theo thứ tự chuẩn, vd `kim,thuy`.
- Ghi DB chỉ qua PDO prepared statements (pattern `class/player.php`).
- Repo git CHƯA có commit nào: KHÔNG tạo commit. Mỗi task kết thúc bằng lint + test + checkpoint backup (copy file đã sửa sang `bak/nguhanh/<ten>.taskN.php`). Nếu user bật git sau này mới đổi sang commit.
- Test chạy bằng: `"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php`; lint: `... php.exe -l <file>`.
- Sau mọi thao tác đổi chỉ số: `player_cache_clear($sid)`.

## File Structure

| File | Vai trò |
|---|---|
| `sql/nguhanh_kichhoat.sql` (new) | 2 ALTER TABLE, chạy local + sau này production |
| `class/config/nguhanh_config.json` (new) | tiers, bonus %, stone map, stat map, linhcan rate bonus |
| `class/function/nap_hanh.php` (new) | parse/join element, cost, rate, engine `nap_hanh_do` |
| `tests/test_nguhanh.php` (new) | CLI test thuần hàm + tích hợp DB |
| `class/player.php` (modify) | columns, merge per-item element, `zb_element_set`, passive trong `getplayer()` |
| `class/function/zhuangbei.php` (modify) | `zhuangbei_pick_element()` |
| `game/nap_hanh.php` (new) | route handler cmd=nap_hanh |
| `game.php` (modify) | đăng ký route |
| `game/zbinfo.php` (modify) | nút + form Nạp Hành trong popup |
| `game/pve.php`, `game/boss.php` (modify) | dùng pick_element |
| `class/function/itemtip.php`, `js/itemtip.js` (modify) | nhiều tag hệ |
| `game/zhuangtai.php` (modify) | dòng nội tại ngũ hành |
| `game/fangshi.php` (modify) | mang element khi gửi bán/mua |
| `tongquan.php`, `docs/CHANGELOG.md` (modify) | mục thống kê + ghi công |

---

### Task 1: Schema + config

**Files:**
- Create: `sql/nguhanh_kichhoat.sql`, `class/config/nguhanh_config.json`
- Modify: local MySQL `game` DB

**Interfaces:**
- Produces: cột `playerzhuangbei.element VARCHAR(24) NOT NULL DEFAULT 'none'`, cột `fangshi_zb.element` tương tự; `nguhanh_config.json` keys: `tiers`, `bonus_pct`, `stones`, `elements`, `linhcan_bonus`.

- [x] **Step 1: Backup DB local**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysqldump.exe" -uroot -p"www.aae.ink" game > "D:/XXJserver/phpstudy_pro/WWW/sql/backup_game_$(date +%Y%m%d_%H%M%S).sql"
```
(đã kiểm chứng: bản MySQL thật của phpStudy ở đây là `MySQL5.5.29`; mysqldump.exe và mysql.exe đều tồn tại trong `bin/`)

- [x] **Step 2: Viết `sql/nguhanh_kichhoat.sql`**

```sql
ALTER TABLE playerzhuangbei   ADD COLUMN element VARCHAR(24) NOT NULL DEFAULT 'none';
ALTER TABLE fangshi_zb        ADD COLUMN element VARCHAR(24) NOT NULL DEFAULT 'none';
ALTER TABLE player_chetao_log ADD COLUMN log_type VARCHAR(20) NOT NULL DEFAULT 'chetao';
```
(`log_type` hiện thực yêu cầu "ghi log player_chetao_log, thêm kiểu nap_hanh" của spec §3.5. INSERT cũ của `chetao.php:157` không nêu cột này nên tự nhận default 'chetao' — không cần sửa code chế tạo.)

- [x] **Step 3: Chạy trên DB local**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysql.exe" -uroot -p"www.aae.ink" game < "D:/XXJserver/phpstudy_pro/WWW/sql/nguhanh_kichhoat.sql"
```
Expected: không lỗi. (Đã kiểm tra trước khi viết plan: `playerzhuangbei`, `fangshi_zb` CHƯA có cột `element`; `player_chetao_log` CHƯA có `log_type`. Chạy lần 2 sẽ báo duplicate column — khi đó kiểm `SHOW COLUMNS ... LIKE 'element'` rồi bỏ qua.)

- [x] **Step 4: Viết `class/config/nguhanh_config.json`**

```json
{
    "tiers": [1, 3, 6, 9, 12],
    "bonus_pct": [3, 6, 10, 15, 22],
    "stones": {"hoa": 32, "thuy": 33, "moc": 34, "kim": 35, "tho": 36},
    "elements": {"kim": "ugj", "moc": "ufy", "thuy": "uxx", "hoa": "ubj", "tho": "umaxhp"},
    "linhcan_bonus": {"kim": 15, "moc": 5, "thuy": 0, "hoa": 10, "tho": 8, "all": 10, "none": 0},
    "base_rate": 90,
    "rate_step": 10,
    "rate_floor": 40,
    "rate_cap": 95,
    "thach_base": 500
}
```

- [x] **Step 5: Verify**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -r 'if(!file_exists("D:/XXJserver/phpstudy_pro/WWW/class/config/nguhanh_config.json"))die("NO CFG\n");$c=json_decode(file_get_contents("D:/XXJserver/phpstudy_pro/WWW/class/config/nguhanh_config.json"),true);if(!$c)die("BAD JSON\n");echo count($c["tiers"])."/".count($c["bonus_pct"])."\n";'
```
Expected: `5/5`. Kiểm `SHOW COLUMNS FROM playerzhuangbei LIKE 'element'` trả về 1 dòng.

- [x] **Step 6: Checkpoint** — copy 2 file mới vào `bak/nguhanh/`.

---

### Task 2: Engine `nap_hanh.php` (hàm thuần + DB)

**Files:**
- Create: `class/function/nap_hanh.php`, `tests/test_nguhanh.php`
- Read-first: `class/function/chetao.php:57-121` (rate + consume pattern), `class/player.php` (`deledjsum`, `player_cache_clear`, INSERT `player_chetao_log` tại `class/function/chetao.php:157`)

**Interfaces:**
- Consumes: config Task 1, cột `playerzhuangbei.element`.
- Produces (signature cố định, các task sau gọi đúng tên này):
  - `zhuangbei_elements_parse($str)` → array các key hợp lệ theo thứ tự chuẩn
  - `zhuangbei_elements_join($arr)` → chuỗi chuẩn hoặc `'none'`
  - `nguhanh_cfg()` → array config (static cache, có fallback hardcode nếu JSON thiếu)
  - `nap_hanh_cost($itemElementStr, $rarity, $he)` → `array('stone_djid'=>int,'stone_qty'=>int,'linhthach'=>int,'n'=>int)`; trả `stone_djid:0` khi `$he` lạ hoặc đã có trên món
  - `nap_hanh_rate($itemElementStr, $linhcanElement)` → int 40..95
  - `nguhanh_match_count($player, $zbs)` → int (số món `element` hợp linh căn; `all` hợp mọi hệ có, `none`/rỗng hợp 0)
  - `nguhanh_apply_passive($player, $zbs)` → void; cộng % theo nấc vào `$player->ugj/ufy/uxx/ubj/umaxhp` và ghi `$player->nguhanh_info` = `array('match_count','tier','pct','gains')`
  - `nap_hanh_do($sid, $zbnowid, $he, $dblj)` → `array('ok'=>bool,'msg'=>string,'rate'=>int,'element'=>string)`; hook test `$GLOBALS['nguhanh_force_roll']` (int 1..100) ép kết quả roll

- [x] **Step 1: Viết test failing trước — `tests/test_nguhanh.php` (phần A: hàm thuần)**

```php
<?php
/**
 * test_nguhanh.php — CLI test tính năng Nạp Ngũ Hành, chạy trên DB local `game`.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
 * Dùng sid giả $TESTSID (32 ký tự f) — không đụng nhân vật thật.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/nap_hanh.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: ham thuan =====
tassert('parse none',  \player\zhuangbei_elements_parse('none'),        array());
tassert('parse junk',  \player\zhuangbei_elements_parse('foo,bar'),     array());
tassert('parse order', \player\zhuangbei_elements_parse('thuy,kim'),    array('kim', 'thuy'));
tassert('parse dup',   \player\zhuangbei_elements_parse('kim,kim,moc'), array('kim', 'moc'));
tassert('join empty',  \player\zhuangbei_elements_join(array()),        'none');
tassert('join order',  \player\zhuangbei_elements_join(array('tho', 'kim')), 'kim,tho');
tassert('join junk',   \player\zhuangbei_elements_join(array('nuoc', 'kim')), 'kim');

$cfg = \player\nguhanh_cfg();
tassert('cfg tiers', count($cfg['tiers']), 5);
tassert('cfg pct',   count($cfg['bonus_pct']), 5);

// cost: thach_base 500 * rarity * (1 + n); đá = 1 + n viên
tassert('cost n0', \player\nap_hanh_cost('none', 3, 'thuy'),
    array('stone_djid' => 33, 'stone_qty' => 1, 'linhthach' => 1500, 'n' => 0));
tassert('cost n1', \player\nap_hanh_cost('thuy', 2, 'hoa'),
    array('stone_djid' => 32, 'stone_qty' => 2, 'linhthach' => 2000, 'n' => 1));
tassert('cost bad he', \player\nap_hanh_cost('none', 1, 'nuoc'),
    array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 0));
tassert('cost dup he', \player\nap_hanh_cost('thuy', 1, 'thuy'),
    array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 1));

// rate: 90 - 10n + bonus linh can, san 40 tran 95
tassert('rate n0',        \player\nap_hanh_rate('none', ''),               90);
tassert('rate n0 kim lc', \player\nap_hanh_rate('none', 'kim'),            95);
tassert('rate n1 hoa lc', \player\nap_hanh_rate('kim', 'hoa'),             90);
tassert('rate n4 moc lc', \player\nap_hanh_rate('kim,moc,thuy,hoa', 'moc'), 55);
```

- [x] **Step 2: Chạy test — phải FAIL**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
```
Expected: `Fatal error: Failed opening required '.../class/function/nap_hanh.php'` (chưa tồn tại file engine).

- [x] **Step 3: Nối tiếp vào CÙNG file test — phần A2 (passive không DB) và phần B (tích hợp DB)**

```php
// ===== A2: nguhanh_apply_passive tren object gia =====
function fakeZb($element, $gj, $fy, $hp, $bj, $xx) {
    $o = new stdClass();
    $o->element = $element; $o->zbgj = $gj; $o->zbfy = $fy;
    $o->zbhp = $hp; $o->zbbj = $bj; $o->zbxx = $xx;
    return $o;
}
function fakePlayer($lc) {
    $o = new stdClass();
    $o->linhcan_element = $lc;
    $o->ugj = 1000; $o->ufy = 1000; $o->umaxhp = 1000; $o->ubj = 1000; $o->uxx = 1000;
    return $o;
}
$zbsTest = array(
    fakeZb('thuy', 100, 0, 0, 0, 400),      // hop (chua thuy)
    fakeZb('kim,thuy', 50, 0, 0, 0, 0),     // hop (chua thuy)
    fakeZb('kim', 999, 0, 0, 0, 0),         // khong hop voi thuy
    fakeZb('none', 500, 0, 0, 0, 0)         // khong he
);
$plThuy = fakePlayer('thuy');
tassert('match thuy=2',  \player\nguhanh_match_count($plThuy, $zbsTest), 2);
\player\nguhanh_apply_passive($plThuy, $zbsTest);
// bac 1 (n=2 < 3): +3% tren chi so trang bi dong gop; elsInPlay = {thuy, kim}
tassert('passive uxx +12', $plThuy->uxx, 1012); // (400 + 0) * 3% = 12
tassert('passive ugj +4',  $plThuy->ugj, 1004); // (100 + 50) * 3% = 4 (int)
tassert('info tier', $plThuy->nguhanh_info['tier'], 1);
tassert('info pct',  $plThuy->nguhanh_info['pct'], 3);

$plAll = fakePlayer('all');
tassert('match all=3', \player\nguhanh_match_count($plAll, $zbsTest), 3);

$plNone = fakePlayer('none');
tassert('match none=0', \player\nguhanh_match_count($plNone, $zbsTest), 0);
\player\nguhanh_apply_passive($plNone, $zbsTest);
tassert('none khong cong', $plNone->ugj, 1000);

// ===== B: tich hop DB (nhan vat gia TESTSID) =====
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$TESTSID = 'ffffffffffffffffffffffffffffffff';
$TESTUID = 987654;

function nh_cleanup($db, $TESTSID) {
    $db->exec("DELETE FROM playerzhuangbei WHERE sid = '$TESTSID'");
    $db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID'");
    $db->exec("DELETE FROM player_chetao_log WHERE sid = '$TESTSID'");
    $db->exec("DELETE FROM game1 WHERE sid = '$TESTSID'");
    \player\player_cache_clear($TESTSID);
    \player\zhuangbei_cache_clear();
}
nh_cleanup($db, $TESTSID);

// game1: nhiet du toan bo cot NOT NULL khong default da kiem tra bang
// information_schema (uid, sid, token, endtime, nowguaiwu, tool1..tool6, tool8..tool16,
// yp1..yp3, cw, jn1..jn3, congphap — tool7 CO default nen bo qua).
$db->exec("INSERT INTO game1 (sid, uid, token, endtime, nowguaiwu,
    tool1, tool2, tool3, tool4, tool5, tool6, tool8, tool9, tool10, tool11, tool12,
    tool13, tool14, tool15, tool16, yp1, yp2, yp3, cw, jn1, jn2, jn3, congphap,
    uname, uyxb, ulv) VALUES ('$TESTSID', $TESTUID, 'test', NOW(), 0,
    0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0, 0,0,0, '', 'test_nguhanh', 100000, 100)");

$insZb = $db->prepare("INSERT INTO playerzhuangbei (zbid, uid, sid, qianghua, zbgj, zbfy, zbhp, zbbj, zbxx) VALUES (1, ?, ?, 0, 100, 50, 300, 10, 20)");
$insZb->execute(array($TESTUID, $TESTSID));
$zbnowid = intval($db->lastInsertId());
$insDj = $db->prepare("INSERT INTO playerdaoju (djid, djsum, sid, uid) VALUES (33, 10, ?, ?)");
$insDj->execute(array($TESTSID, $TESTUID));

$zbCfg1 = \player\getzbkzb(1, $db);
$expLinh1 = 500 * max(1, intval($zbCfg1->rarity)) * 1; // lan 1: n=0, 1 da

// 1) Ep roll FAIL (91 > rate 90): van mat 1 da + linh thach, element khong doi
$GLOBALS['nguhanh_force_roll'] = 91;
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'thuy', $db);
tassert('do fail ok=false',   $r['ok'], false);
tassert('do fail element',    $r['element'], 'none');
tassert('do fail rate 90',    $r['rate'], 90);
tassert('fail van mat 1 da',
    intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=33")->fetchColumn()), 9);
tassert('fail van tru LT',
    intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 100000 - $expLinh1);

// 2) Ep roll SUCCESS
$GLOBALS['nguhanh_force_roll'] = 50;
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'thuy', $db);
tassert('do success ok=true', $r['ok'], true);
tassert('do success element', $r['element'], 'thuy');
tassert('db element raw',
    (string)$db->query("SELECT element FROM playerzhuangbei WHERE zbnowid = $zbnowid")->fetchColumn(), 'thuy');

// 3) Nap trung he: tu choi, KHONG tieu hao them (da van la 8 sau 2 lan burn)
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'thuy', $db);
tassert('dup reject', $r['ok'], false);
tassert('dup msg',    strpos($r['msg'], 'đã mang hệ') !== false, true);
tassert('dup khong mat da',
    intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=33")->fetchColumn()), 8);

// 4) He la + quyen so
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'nuoc', $db);
tassert('bad he msg', strpos($r['msg'], 'không hợp lệ') !== false, true);
$r = \player\nap_hanh_do(str_repeat('0', 32), $zbnowid, 'kim', $db);
tassert('not owner msg', strpos($r['msg'], 'không thuộc về') !== false, true);

// 5) Log nap_hanh (1 fail + 1 success)
tassert('log rows',
    intval($db->query("SELECT COUNT(*) FROM player_chetao_log WHERE sid='$TESTSID' AND log_type='nap_hanh'")->fetchColumn()), 2);
```

- [x] **Step 4: Thêm vào CUỐI `tests/test_nguhanh.php` — block ket thuc**

```php
unset($GLOBALS['nguhanh_force_roll']);
nh_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [x] **Step 5: Viết `class/function/nap_hanh.php` (engine đầy đủ)**

```php
<?php
namespace player;

/**
 * nap_hanh.php — Engine rèn nạp ngũ hành cho trang bị (đa hệ tối đa 5, không đè).
 * Spec: docs/DESIGN_NGUHANH_KICHHOAT.md
 */

if (!function_exists(__NAMESPACE__ . '\\zhuangbei_elements_parse')) {
    function zhuangbei_elements_parse($str) {
        if (!is_string($str) || $str === '' || $str === 'none') return array();
        $parts = array_map('trim', explode(',', $str));
        $out = array();
        foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
            if (in_array($el, $parts, true)) $out[] = $el;
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\zhuangbei_elements_join')) {
    function zhuangbei_elements_join($arr) {
        if (!is_array($arr)) return 'none';
        $out = array();
        foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
            if (in_array($el, $arr, true)) $out[] = $el;
        }
        return empty($out) ? 'none' : implode(',', $out);
    }
}

if (!function_exists(__NAMESPACE__ . '\\nguhanh_cfg')) {
    function nguhanh_cfg() {
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/nguhanh_config.json');
        $cfg = $json ? json_decode($json, true) : false;
        if (!is_array($cfg)) {
            // Fallback đúng giá trị trong class/config/nguhanh_config.json
            $cfg = array(
                'tiers' => array(1, 3, 6, 9, 12),
                'bonus_pct' => array(3, 6, 10, 15, 22),
                'stones' => array('hoa' => 32, 'thuy' => 33, 'moc' => 34, 'kim' => 35, 'tho' => 36),
                'elements' => array('kim' => 'ugj', 'moc' => 'ufy', 'thuy' => 'uxx', 'hoa' => 'ubj', 'tho' => 'umaxhp'),
                'linhcan_bonus' => array('kim' => 15, 'moc' => 5, 'thuy' => 0, 'hoa' => 10, 'tho' => 8, 'all' => 10, 'none' => 0),
                'base_rate' => 90, 'rate_step' => 10, 'rate_floor' => 40, 'rate_cap' => 95, 'thach_base' => 500,
            );
        }
        $cache = $cfg;
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_cost')) {
    // $he là hệ sắp nạp thêm. Đá = 1 + n hệ hiện có; linh thạch = thach_base * rarity * (1 + n).
    // Trả stone_djid = 0 khi $he không hợp lệ hoặc món đã mang hệ đó.
    function nap_hanh_cost($itemElementStr, $rarity, $he) {
        $cfg = nguhanh_cfg();
        $cur = zhuangbei_elements_parse($itemElementStr);
        $n = count($cur);
        $zero = array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => $n);
        if (!isset($cfg['stones'][$he])) return $zero;
        if (in_array($he, $cur, true)) return $zero;
        $qty = 1 + $n;
        return array(
            'stone_djid' => intval($cfg['stones'][$he]),
            'stone_qty'  => $qty,
            'linhthach'  => intval($cfg['thach_base']) * max(1, intval($rarity)) * $qty,
            'n'          => $n,
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_rate')) {
    function nap_hanh_rate($itemElementStr, $linhcanElement) {
        $cfg = nguhanh_cfg();
        $n = count(zhuangbei_elements_parse($itemElementStr));
        $bonus = isset($cfg['linhcan_bonus'][$linhcanElement]) ? intval($cfg['linhcan_bonus'][$linhcanElement]) : 0;
        $rate = intval($cfg['base_rate']) - intval($cfg['rate_step']) * $n + $bonus;
        return min(intval($cfg['rate_cap']), max(intval($cfg['rate_floor']), $rate));
    }
}

if (!function_exists(__NAMESPACE__ . '\\nguhanh_match_count')) {
    // Đếm số món đang mặc có element chứa hệ linh căn. 'all' hợp mọi hệ; 'none'/rỗng: 0.
    function nguhanh_match_count($player, $zbs) {
        $lc = (is_object($player) && isset($player->linhcan_element)) ? $player->linhcan_element : '';
        if ($lc === '' || $lc === 'none' || !is_array($zbs)) return 0;
        $n = 0;
        foreach ($zbs as $zb) {
            $els = zhuangbei_elements_parse(isset($zb->element) ? $zb->element : '');
            if (empty($els)) continue;
            if ($lc === 'all' || in_array($lc, $els, true)) $n++;
        }
        return $n;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nguhanh_apply_passive')) {
    // Cộng % chỉ số theo nấc tiers. % áp trên TỔNG chỉ số trang bị của các món hop can
    // (không phải chỉ số gốc nhân vật) — spec §4. Ghi $player->nguhanh_info để Trạng thái show.
    function nguhanh_apply_passive($player, $zbs) {
        $player->nguhanh_info = array('match_count' => 0, 'tier' => 0, 'pct' => 0, 'gains' => array());
        $lc = isset($player->linhcan_element) ? $player->linhcan_element : '';
        if ($lc === '' || $lc === 'none' || !is_array($zbs) || empty($zbs)) return;
        $matched = array();
        foreach ($zbs as $zb) {
            $els = zhuangbei_elements_parse(isset($zb->element) ? $zb->element : '');
            if (empty($els)) continue;
            if ($lc === 'all' || in_array($lc, $els, true)) $matched[] = array($zb, $els);
        }
        $n = count($matched);
        if ($n === 0) return;
        $cfg = nguhanh_cfg();
        $tierIdx = -1;
        for ($t = 0; $t < count($cfg['tiers']); $t++) {
            if ($n >= intval($cfg['tiers'][$t])) $tierIdx = $t;
        }
        if ($tierIdx < 0) return;
        $pct = intval($cfg['bonus_pct'][$tierIdx]);
        $statOf = $cfg['elements']; // kim => ugj, thuy => uxx...
        $srcOf = array('ugj' => 'zbgj', 'ufy' => 'zbfy', 'umaxhp' => 'zbhp', 'ubj' => 'zbbj', 'uxx' => 'zbxx');
        $sums = array('zbgj' => 0, 'zbfy' => 0, 'zbhp' => 0, 'zbbj' => 0, 'zbxx' => 0);
        $elsInPlay = array();
        foreach ($matched as $m) {
            foreach ($sums as $k => $v) $sums[$k] += intval($m[0]->$k);
            foreach ($m[1] as $el) if (!in_array($el, $elsInPlay, true)) $elsInPlay[] = $el;
        }
        foreach ($elsInPlay as $el) {
            if (!isset($statOf[$el])) continue;
            $field = $statOf[$el];
            if (!isset($player->$field)) continue;
            $add = intval($sums[$srcOf[$field]] * $pct / 100);
            if ($add !== 0) {
                $player->$field += $add;
                $player->nguhanh_info['gains'][] = \player\zhuangbei_element_name($el) . ' +' . $add;
            }
        }
        $player->nguhanh_info['match_count'] = $n;
        $player->nguhanh_info['tier'] = $tierIdx + 1;
        $player->nguhanh_info['pct'] = $pct;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nap_hanh_do')) {
    function nap_hanh_do($sid, $zbnowid, $he, $dblj) {
        $out = array('ok' => false, 'msg' => '', 'rate' => 0, 'element' => 'none');
        $cfg = nguhanh_cfg();
        if (!isset($cfg['stones'][$he])) { $out['msg'] = 'Hệ ngũ hành không hợp lệ.'; return $out; }

        $zbnowid = intval($zbnowid);
        $stmt = $dblj->prepare("SELECT sid, element FROM playerzhuangbei WHERE zbnowid = ? LIMIT 1");
        $stmt->execute(array($zbnowid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        // Món detach khi treo bán có sid='' — không bao giờ khớp sid người mua.
        if (!$row || $row['sid'] !== $sid) {
            $out['msg'] = 'Trang bị không tồn tại hoặc không thuộc về ngươi.';
            return $out;
        }
        $cur = zhuangbei_elements_parse($row['element']);
        $out['element'] = zhuangbei_elements_join($cur);
        $elName = \player\zhuangbei_element_name($he);
        if (in_array($he, $cur, true)) {
            $out['msg'] = "Món đã mang hệ {$elName}, không thể nạp trùng.";
            return $out;
        }
        if (count($cur) >= 5) { $out['msg'] = 'Trang bị đã đủ 5 hệ ngũ hành.'; return $out; }

        $player = \player\getplayer($sid, $dblj);
        $zb = \player\getzb($zbnowid, $dblj);
        if (!is_object($zb)) { $out['msg'] = 'Không đọc được trang bị (config thiếu?).'; return $out; }
        $cost = nap_hanh_cost($row['element'], intval($zb->rarity), $he);

        $stone = \player\getplayerdaoju($sid, $cost['stone_djid'], $dblj);
        $have = ($stone && isset($stone->djsum)) ? intval($stone->djsum) : 0;
        if ($have < $cost['stone_qty']) {
            $out['msg'] = "Không đủ Tinh Thạch hệ {$elName}: cần {$cost['stone_qty']}, có {$have}.";
            return $out;
        }
        if (intval($player->uyxb) < $cost['linhthach']) {
            $out['msg'] = "Linh thạch không đủ: cần {$cost['linhthach']}.";
            return $out;
        }

        // Spec §3.5: thất bại CHỈ mất nguyên liệu → trừ đá + linh thạch TRƯỚC khi roll.
        if (!\player\deledjsum($cost['stone_djid'], $cost['stone_qty'], $sid, $dblj)) {
            $out['msg'] = 'Tinh thạch đã bị tiêu hao ở thao tác khác, vui lòng thử lại.';
            return $out;
        }
        if (!\player\changeyxb(2, $cost['linhthach'], $sid, $dblj)) {
            \player\adddj($sid, $cost['stone_djid'], $cost['stone_qty'], $dblj); // hoàn đá, không mất kép
            $out['msg'] = 'Linh thạch không đủ.';
            return $out;
        }

        $rate = nap_hanh_rate($row['element'], isset($player->linhcan_element) ? $player->linhcan_element : '');
        $roll = isset($GLOBALS['nguhanh_force_roll']) ? intval($GLOBALS['nguhanh_force_roll']) : mt_rand(1, 100);
        $success = ($roll <= $rate);
        $out['rate'] = $rate;

        $dblj->prepare("INSERT INTO player_chetao_log (sid, recipe_id, success, created_at, log_type) VALUES (?, 0, ?, NOW(), 'nap_hanh')")
             ->execute(array($sid, $success ? 1 : 0));

        if (!$success) {
            $out['msg'] = "Nạp hệ {$elName} thất bại, đã mất nguyên liệu.";
            return $out;
        }

        $cur[] = $he;
        $newStr = zhuangbei_elements_join($cur);
        $upd = $dblj->prepare("UPDATE playerzhuangbei SET element = ? WHERE zbnowid = ? AND sid = ?");
        $upd->execute(array($newStr, $zbnowid, $sid));
        if ($upd->rowCount() === 0) {
            // Đổi chủ giữa lúc rèn (hiếm): hoàn đủ đá + linh thạch.
            \player\adddj($sid, $cost['stone_djid'], $cost['stone_qty'], $dblj);
            \player\changeyxb(1, $cost['linhthach'], $sid, $dblj);
            $out['msg'] = 'Trang bị đổi chủ giữa lúc rèn, đã hoàn nguyên liệu.';
            return $out;
        }
        \player\zhuangbei_cache_clear($zbnowid);
        \player\player_cache_clear($sid);
        $out['ok'] = true;
        $out['element'] = $newStr;
        $out['msg'] = "Nạp hệ {$elName} thành công!";
        return $out;
    }
}
?>
```

- [x] **Step 6: Đăng ký engine vào bootstrap — `class/player.php`**

Sau dòng `require_once __DIR__ . '/function/chetao.php';` (dòng 21) thêm:

```php
require_once __DIR__ . '/function/nap_hanh.php';
```

- [x] **Step 7: Chạy test — phải PASS toàn bộ**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
```
Expected: dòng cuối `ALL PASS`, exit code 0.

- [x] **Step 8: Lint + checkpoint**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l class/function/nap_hanh.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l tests/test_nguhanh.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l class/player.php
mkdir -p bak/nguhanh
cp class/function/nap_hanh.php bak/nguhanh/nap_hanh.task2.php
cp tests/test_nguhanh.php bak/nguhanh/test_nguhanh.task2.php
```

---

### Task 3: Lồng `class/player.php` + `zhuangbei_pick_element`

**Files:**
- Modify: `class/player.php` (4 chỗ), `class/function/zhuangbei.php` (thêm 1 hàm)
- Test: nối vào `tests/test_nguhanh.php`

**Interfaces:**
- Consumes: `zhuangbei_elements_parse/join`, `nguhanh_apply_passive` (Task 2), cột `element` (Task 1).
- Produces: `$zb->element` = hệ DB thật trên object merge; `$player->zb_element_set` (array); `$player->nguhanh_info`; `zhuangbei_pick_element($set, $monsterElement)` → string hệ hoặc `''`.

- [x] **Step 1: `zhuangbei_columns()` — thêm cột `element`** (class/player.php:407)

```php
    return array('zbid','uid','zbnowid','sid','qianghua','zbgj','zbfy','zbhp','zbbj','zbxx','element');
```

- [x] **Step 2: `merge_player_zhuangbei_with_config()` — element DB đè config**

Thay khối cũ (class/player.php:469-472):

```php
    // Đảm bảo trường element/rarity luôn tồn tại (tương thích dữ liệu cũ thiếu JSON).
    if (!isset($zb->element) || $zb->element === '') {
        $zb->element = 'none';
    }
```

bằng:

```php
    // element là dữ liệu RIÊNG theo món (nạp ngũ hành) → giá trị DB đè config.
    // Config luôn 'none' (Plan Z), DB mới là nguồn hệ thật.
    $dbElemRaw = isset($row['element']) ? trim((string)$row['element']) : '';
    if ($dbElemRaw !== '' && $dbElemRaw !== 'none' && zhuangbei_elements_parse($dbElemRaw) === array()) {
        error_log('nguhanh: element la, ep ve none (zbnowid=' . $zb->zbnowid . ', value=' . $dbElemRaw . ')');
    }
    $zb->element = zhuangbei_elements_join(zhuangbei_elements_parse($dbElemRaw));
```

(khối rarity bên dưới giữ nguyên).

- [x] **Step 3: Vòng trang bị trong `getplayer()` — thu `zb_element_set`**

class/player.php:559-592. Thay phần khởi tạo:

```php
    // Element tấn công ưu tiên từ trang bị (cụ thể kim/moc/thuy/hoa/tho), chọn rarity cao nhất.
    $zbElementAttack = '';
    $zbElementBestRarity = 0;
```

bằng:

```php
    // Hệ đã nạp trên trang bị: gom set toàn bộ 16 ô; zb_element giữ "hệ đại diện"
    // (hệ đầu theo thứ tự chuẩn của món rarity cao nhất có hệ) để tương thích cũ.
    $zbElementAttack = '';
    $zbElementBestRarity = 0;
    $zbElementSet = array();
```

Thay khối chọn element trong loop (dòng 578-586):

```php
                // Chọn element tấn công từ trang bị: ưu tiên element cụ thể, rarity cao nhất.
                $zbEl = isset($zhuangbei->element) ? $zhuangbei->element : '';
                $zbRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
                if (in_array($zbEl, array('kim', 'moc', 'thuy', 'hoa', 'tho'))) {
                    if ($zbRa > $zbElementBestRarity) {
                        $zbElementBestRarity = $zbRa;
                        $zbElementAttack = $zbEl;
                    }
                }
```

bằng:

```php
                $zbEls = zhuangbei_elements_parse(isset($zhuangbei->element) ? $zhuangbei->element : '');
                if (!empty($zbEls)) {
                    foreach ($zbEls as $el) {
                        if (!in_array($el, $zbElementSet, true)) $zbElementSet[] = $el;
                    }
                    $zbRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
                    if ($zbRa > $zbElementBestRarity) {
                        $zbElementBestRarity = $zbRa;
                        $zbElementAttack = $zbEls[0];
                    }
                }
```

Và sau `$player->zb_element = $zbElementAttack;` (dòng 592) thêm:

```php
    $player->zb_element_set = $zbElementSet;
```

- [x] **Step 4: Nối passive nạp hành vào `getplayer()` — SAU khối linh căn**

`$player->linhcan_element` chỉ được gán ở dòng ~713 (trong khối `linhcan_calc_bonuses`), nên passive phải chạy sau. Chèn NGAY TRƯỚC comment `// ===== BUFF THỂ CHẤT (Passive) =====` (dòng 720):

```php
    // ===== NOI TAI NAP HANH (spec DESIGN_NGUHANH_KICHHOAT.md §4) =====
    // Doc linhcan_element vua gap o tren; cong % chi so trang bi theo nac hop can.
    if (function_exists(__NAMESPACE__ . '\\nguhanh_apply_passive')) {
        nguhanh_apply_passive($player, $equippedZbs);
    }
```

($equippedZbs đã có sẵn trong scope từ dòng 558.)

- [x] **Step 5: Thêm `zhuangbei_pick_element()` vào `class/function/zhuangbei.php`**

Chèn ngay sau hàm `zhuangbei_get_element_multiplier()` (kết thúc ~dòng 216):

```php
/**
 * Chọn hệ trong set đã nạp mà KHẮC mục tiêu (ưu tiên theo thứ tự chuẩn kim,moc,thuy,hoa,tho).
 * Trả '' khi không hệ nào khắc — caller tự fallback zb_element đại diện / linh căn.
 */
function zhuangbei_pick_element($set, $monsterElement) {
    if (!is_array($set) || empty($set) || $monsterElement === '' || $monsterElement === null) return '';
    foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
        if (in_array($el, $set, true) && zhuangbei_element_khac($el, $monsterElement)) return $el;
    }
    return '';
}
```

- [x] **Step 6: Nối test — chèn TRƯỚC block `unset($GLOBALS['nguhanh_force_roll']);` cuối file test**

```php
// ===== (Task 3) element DB de config qua merge =====
$db->exec("UPDATE playerzhuangbei SET element = 'kim,thuy' WHERE zbnowid = $zbnowid");
\player\zhuangbei_cache_clear($zbnowid);
tassert('merge kim,thuy', \player\getzb($zbnowid, $db)->element, 'kim,thuy');

$db->exec("UPDATE playerzhuangbei SET element = 'lum' WHERE zbnowid = $zbnowid");
\player\zhuangbei_cache_clear($zbnowid);
tassert('merge junk -> none', \player\getzb($zbnowid, $db)->element, 'none');

// ===== (Task 3) zb_element_set + dai dien khi dang mac =====
$db->exec("UPDATE playerzhuangbei SET element = 'thuy' WHERE zbnowid = $zbnowid");
$db->exec("UPDATE game1 SET tool1 = $zbnowid WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);
\player\zhuangbei_cache_clear($zbnowid);
$plT3 = \player\getplayer($TESTSID, $db);
tassert('set thuy',   $plT3->zb_element_set, array('thuy'));
tassert('dai dien',   $plT3->zb_element, 'thuy');
tassert('info ton tai', isset($plT3->nguhanh_info), true);
tassert('info match 0 (linh can rong)', intval($plT3->nguhanh_info['match_count']), 0);
$db->exec("UPDATE game1 SET tool1 = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

// ===== (Task 3) zhuangbei_pick_element =====
tassert('pick khac',          \player\zhuangbei_pick_element(array('kim', 'thuy'), 'moc'), 'kim');
tassert('pick thu tu chuan',  \player\zhuangbei_pick_element(array('thuy', 'hoa'), 'hoa'), 'thuy');
tassert('pick sau khi quet',  \player\zhuangbei_pick_element(array('kim', 'hoa'), 'kim'), 'hoa');
tassert('pick khong he nao',  \player\zhuangbei_pick_element(array('kim'), 'tho'), '');
tassert('pick set rong',      \player\zhuangbei_pick_element(array(), 'hoa'), '');
tassert('pick mc rong',       \player\zhuangbei_pick_element(array('kim'), ''), '');
```

- [x] **Step 7: Chạy test + lint + checkpoint**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l class/player.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l class/function/zhuangbei.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l tests/test_nguhanh.php
cp class/player.php bak/nguhanh/player.task3.php
cp class/function/zhuangbei.php bak/nguhanh/zhuangbei.task3.php
```
Expected: `ALL PASS`. Nếu FAIL 'dai dien' — kiểm Step 3 có bỏ sót `$zbEls[0]` không.

---

### Task 4: Combat đa hệ trong `game/pve.php` + `game/boss.php`

**Files:**
- Modify: `game/pve.php:247-251`, `game/boss.php:245-249`

**Interfaces:**
- Consumes: `$player->zb_element_set`, `zhuangbei_pick_element()` (Task 3), `zhuangbei_get_element_multiplier()` (đã có).
- Produces: `$elemMult` ưu tiên hệ khắc chế từ set; giữ nguyên message "Trang bị khắc X!" hiện có.

- [x] **Step 1: `game/pve.php` — thay khối chọn `$zbElement`** (dòng 247)

```php
    $zbElement = '';
    $zbElemSet = isset($player->zb_element_set) ? $player->zb_element_set : array();
    if (!empty($zbElemSet) && function_exists('player\\zhuangbei_pick_element')) {
        $zbElement = \player\zhuangbei_pick_element($zbElemSet, $monsterElement);
    }
    if ($zbElement === '') {
        $zbElement = isset($player->zb_element) ? $player->zb_element : '';
    }
```

(hai dòng `if (!empty($zbElement) && function_exists(...))` phía dưới giữ nguyên — fallback linh căn cũng giữ nguyên).

- [x] **Step 2: `game/boss.php` — y hệt, đổi `$monsterElement` thành `$bossElement`** (dòng 245)

```php
    $zbElement = '';
    $zbElemSet = isset($player->zb_element_set) ? $player->zb_element_set : array();
    if (!empty($zbElemSet) && function_exists('player\\zhuangbei_pick_element')) {
        $zbElement = \player\zhuangbei_pick_element($zbElemSet, $bossElement);
    }
    if ($zbElement === '') {
        $zbElement = isset($player->zb_element) ? $player->zb_element : '';
    }
```

- [x] **Step 3: Verify**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l game/pve.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l game/boss.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
```
Logic pick đã được unit test ở Task 3 Step 6; đường render pve/boss CHỈ kiểm được bằng tay trong game (đánh quái lệch hệ, xem dòng "Trang bị khắc …"). Đánh dấu chưa test trong Task 7.

- [x] **Step 4: Checkpoint** — `cp game/pve.php bak/nguhanh/pve.task4.php && cp game/boss.php bak/nguhanh/boss.task4.php`

---

### Task 5: Route `cmd=nap_hanh` + nút trong `zbinfo.php` + mang element qua phường thị

**Files:**
- Create: `game/nap_hanh.php`
- Modify: `game.php` (cmdMap), `game/zbinfo.php` (INSERT fangshi_zb, badge, section Nạp Hành)

**Interfaces:**
- Consumes: `nap_hanh_do`, `nap_hanh_cost`, `nap_hanh_rate`, `zhuangbei_elements_parse` (Task 2), `$zhuangbei->element` thật từ DB (Task 3).
- Produces: link `cmd=nap_hanh&zbnowid=N&he=X`; section "Nạp Ngũ Hành" trong popup chi tiết trang bị.

- [x] **Step 1: Đăng ký route — `game.php`**

Trong `$cmdMap`, ngay sau dòng `'do_craft_chetao'=>'game/chetao.php',` (dòng 413) thêm:

```php
			'nap_hanh'=>'game/nap_hanh.php',
```

Kiểm `chakanzb` đã có trong cmdMap trỏ `game/zbinfo.php` (grep `chakanzb` trong game.php) — link "Về trang trang bị" cần nó.

- [x] **Step 2: Viết `game/nap_hanh.php`**

```php
<?php
/**
 * game/nap_hanh.php — route cmd=nap_hanh: rèn nạp 1 hệ ngũ hành vào 1 món trang bị.
 * Biến $he / $zbnowid / $sid do game.php parse_str từ chuỗi cmd đã mã hoá
 * (KHÔNG đọc $_GET vì tham số nằm trong cmd đã mã hoá — đã kiểm chứng roundtrip).
 */
$he = isset($he) ? strtolower(trim($he)) : '';
$nhZbnowid = isset($zbnowid) ? intval($zbnowid) : 0;
$backCmd = $encode->encode("cmd=chakanzb&zbnowid=$nhZbnowid&uid=$player->uid&sid=$sid");

$result = \player\nap_hanh_do($sid, $nhZbnowid, $he, $dblj);
$nhColor = $result['ok'] ? 'green' : 'red';
$nhMsg = $result['msg'];
$nhRate = intval($result['rate']);
$nhElemOut = htmlspecialchars($result['element'], ENT_QUOTES, 'UTF-8');

$html = <<<HTML
<div class="pve-notice-wrap">
    <div class="pve-notice-card">
        <font color="$nhColor">$nhMsg</font><br/>
        Tỉ lệ rèn: $nhRate% — hệ hiện có: $nhElemOut<br/>
        <a href="?cmd=$backCmd">Về trang trang bị</a>
    </div>
</div>
HTML;
echo $html;
```

(`$sid` đã được game.php kiểm hex-32 trước khi include trang này; engine tự chặn quyền sở hữu.)

- [x] **Step 3: `game/zbinfo.php` — INSERT treo bán mang theo `element`** (dòng 59-66)

Cột list trong `INSERT INTO \`fangshi_zb\`(zbname, ..., zbys)` thêm `element` ở cuối, và giá trị tương ứng thêm `$zhuangbei->element`:

```php
            $sql = "INSERT INTO `fangshi_zb`(zbname, zbinfo, zbgj, zbfy, zbbj, zbxx, tuposjs, tupoexp, zbid, uid, zbnowid, sid, zbhp, qianghua, zblv, pay, zbys, element) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $dblj->prepare($sql);
            $stmt->execute(array(
                $zhuangbei->zbname, $zhuangbei->zbinfo, $zhuangbei->zbgj, $zhuangbei->zbfy,
                $zhuangbei->zbbj, $zhuangbei->zbxx, $zhuangbei->tuposjs, $zhuangbei->tupoexp,
                $zhuangbei->zbid, $player->uid, $zbnowid, $sid, $zhuangbei->zbhp,
                $zhuangbei->qianghua, $zhuangbei->zblv, $pay, $zhuangbei->zbys, $zhuangbei->element
            ));
```

(Mua lại không cần sửa: `fangshi.php:315` chỉ UPDATE `sid/uid` trên đúng dòng `playerzhuangbei` cũ nên `element` tự được mang về.)

- [x] **Step 4: `game/zbinfo.php` — badge + thẻ "Nguyên tố" hiển thị NHIỀU hệ**

Thay khối dòng 336-341:

```php
$zbBadgeHtml = '<span class="zb-badge rarity" style="color:'.$zbRarityColor.'">'.$zbRarityName.'</span>';
$zbElemCard = '';
if (in_array($zbElem, array('kim', 'moc', 'thuy', 'hoa', 'tho', 'all'))) {
    $zbBadgeHtml = '<span class="zb-badge elem" style="color:'.$zbElemColor.'">'.$zbElemIcon.' '.$zbElemName.'</span>' . $zbBadgeHtml;
    $zbElemCard = '<div class="zb-meta-card"><span>Nguyên tố</span><b style="color:'.$zbElemColor.'">'.$zbElemIcon.' '.$zbElemName.'</b></div>';
}
```

bằng:

```php
$zbBadgeHtml = '<span class="zb-badge rarity" style="color:'.$zbRarityColor.'">'.$zbRarityName.'</span>';
$zbElemCard = '';
$zbElsList = \player\zhuangbei_elements_parse($zbElem);
$zbElemBadges = '';
$zbElemNames = array();
foreach ($zbElsList as $zbElOne) {
    $zbElemBadges .= '<span class="zb-badge elem" style="color:' . \player\zhuangbei_element_color($zbElOne) . '">'
        . \player\zhuangbei_element_icon($zbElOne) . ' ' . \player\zhuangbei_element_name($zbElOne) . '</span>';
    $zbElemNames[] = \player\zhuangbei_element_name($zbElOne);
}
if ($zbElemBadges !== '') {
    $zbBadgeHtml = $zbElemBadges . $zbBadgeHtml;
    $zbElemCard = '<div class="zb-meta-card"><span>Nguyên tố</span><b>' . implode(' · ', $zbElemNames) . '</b></div>';
}
```

(3 biến `$zbElemIcon/$zbElemName/$zbElemColor` dòng 331-333 không còn dùng — xóa luôn.)

- [x] **Step 5: `game/zbinfo.php` — section "Nạp Ngũ Hành" (chỉ chủ sở hữu thấy)**

Chèn khối dựng HTML NGAY TRƯỚC `$html = <<<HTML` (dòng 345):

```php
// ===== Section Nap Ngu Hanh =====
$nhSectionHtml = '';
if ($owner) {
    $nhCurEls = \player\zhuangbei_elements_parse($zbElem);
    $nhCurText = array();
    foreach ($nhCurEls as $nhEl) {
        $nhCurText[] = \player\zhuangbei_element_icon($nhEl) . ' ' . \player\zhuangbei_element_name($nhEl);
    }
    $nhLinks = '';
    foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $nhEl) {
        if (in_array($nhEl, $nhCurEls, true)) continue;
        $nhC = \player\nap_hanh_cost($zbElem, $zbRarity, $nhEl);
        if ($nhC['stone_djid'] <= 0) continue;
        $nhRt = \player\nap_hanh_rate($zbElem, isset($player->linhcan_element) ? $player->linhcan_element : '');
        $nhSt = \player\getplayerdaoju($sid, $nhC['stone_djid'], $dblj);
        $nhHave = ($nhSt && isset($nhSt->djsum)) ? intval($nhSt->djsum) : 0;
        $nhEn = \player\zhuangbei_element_name($nhEl);
        $nhCmd = $encode->encode("cmd=nap_hanh&zbnowid=$zhuangbei->zbnowid&he=$nhEl&sid=$sid");
        $nhLinks .= "<a class='zb-up-btn' href='?cmd=$nhCmd'>Nạp " . $nhEn
            . ' — ' . $nhC['stone_qty'] . ' đá (có ' . $nhHave . ') · '
            . $nhC['linhthach'] . ' LT · ' . $nhRt . '%</a>';
    }
    if (count($nhCurEls) >= 5) {
        $nhLinks = "<div class='zb-notice'>Trang bị đã đủ 5 hệ ngũ hành.</div>";
    }
    $nhCurStr = empty($nhCurText) ? 'Chưa có (Vô Hành)' : implode(' · ', $nhCurText);
    $nhSectionHtml = <<<HTML
<div class="zb-section">
    <div class="zb-section-title">Nạp Ngũ Hành</div>
    <div class="zb-row"><span>Hệ hiện có</span><b>$nhCurStr</b></div>
    <div class="zb-upgrade-grid">$nhLinks</div>
    <div class="zb-desc">Thất bại chỉ mất nguyên liệu. Treo bán/phân giải không hoàn linh thạch đã nạp.</div>
</div>
HTML;
}
```

Và trong heredoc `$html`, chèn `$nhSectionHtml` giữa section Cường hóa và section thao tác — sửa cụm (dòng 398-401):

```php
            $up_buttons
        </div>

        $setzbwz_html
```

thành:

```php
            $up_buttons
        </div>

        $nhSectionHtml

        $setzbwz_html
```

- [x] **Step 6: Verify + checkpoint**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l game/nap_hanh.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l game/zbinfo.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l game.php
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
cp game/nap_hanh.php bak/nguhanh/nap_hanh_route.task5.php
cp game/zbinfo.php bak/nguhanh/zbinfo.task5.php
```

Luồng popup (nút Nạp hiện đúng chi phí/tỉ lệ, roll thật) là UI — chưa test tự động, ghi vào bảng Task 7.

---

### Task 6: Hiển thị nhiều hệ — tooltip, túi, Trạng thái, tongquan

**Files:**
- Modify: `class/function/itemtip.php`, `js/itemtip.js`, `game/bagzb.php`, `game/zhuangtai.php`, `tongquan.php`

**Interfaces:**
- Consumes: `$zb->element` chuỗi đa hệ (Task 3), `nguhanh_info` (Task 3).
- Produces: payload `elem` là MẢNG các tag `[icon, name, color]` (rỗng = chưa nạp); dòng nội tại trong Trạng thái; mục thống kê "Nạp Ngũ Hành" + cột cảnh giới đúng trong tongquan.

- [x] **Step 1: `class/function/itemtip.php` — payload `elem` thành danh sách tag**

Thay dòng 116-119:

```php
    $element = (string) itemtip_field($zb, 'element', 'none');
    if ($element === '') {
        $element = 'none';
    }
```

bằng:

```php
    $element = (string) itemtip_field($zb, 'element', 'none');
    $elemTags = array();
    foreach (zhuangbei_elements_parse($element) as $el) {
        $elemTags[] = array(
            zhuangbei_element_icon($el),
            zhuangbei_element_name($el),
            zhuangbei_element_color($el)
        );
    }
```

Và trong `$payload` thay mục `'elem' => array(...)` (dòng 152-156) thành:

```php
        'elem'   => $elemTags,
```

- [x] **Step 2: `js/itemtip.js` — render vòng lặp tag hệ**

Thay trong `render(data, href)` (dòng 69-71):

```js
        var elem = data.elem || ['', '', ''];
        var rar = data.rar || ['', ''];
        var showElem = elem[1] && elem[1] !== 'Vô Hành';
```

bằng:

```js
        var elems = data.elem || [];
        var rar = data.rar || ['', ''];
        var elemTags = '';
        for (var ei = 0; ei < elems.length; ei++) {
            elemTags += tag('elem', elems[ei][0] + ' ' + elems[ei][1], elems[ei][2]);
        }
```

Và thay cụm (dòng 82):

```js
            (showElem ? tag('elem', elem[0] + ' ' + elem[1], elem[2]) : '') +
```

bằng:

```js
            elemTags +
```

Sau đó grep `itemtip.js` trong các template (`grep -rn "itemtip.js" --include=*.php .`) và bump query version (`?v=...`) để client không dùng JS cũ từ cache.

- [x] **Step 3: `game/bagzb.php` — badge nhiều hệ trong túi đồ** (dòng 217-223)

Thay:

```php
    $elemIcon = \player\zhuangbei_element_icon($zbElem);
    $elemName = \player\zhuangbei_element_name($zbElem);
    $rarityName = \player\zhuangbei_rarity_name($zbRarity);
    $rarityColor = \player\zhuangbei_rarity_color($zbRarity);
    $zbBadge = in_array($zbElem, array('kim', 'moc', 'thuy', 'hoa', 'tho', 'all'))
        ? '<span class="zb-badge elem" style="color:'.\player\zhuangbei_element_color($zbElem).'">'.$elemIcon.' '.$elemName.'</span>'
        : '';
```

bằng:

```php
    $rarityName = \player\zhuangbei_rarity_name($zbRarity);
    $rarityColor = \player\zhuangbei_rarity_color($zbRarity);
    $zbBadge = '';
    foreach (\player\zhuangbei_elements_parse($zbElem) as $nhEl) {
        $zbBadge .= '<span class="zb-badge elem" style="color:'.\player\zhuangbei_element_color($nhEl).'">'
            . \player\zhuangbei_element_icon($nhEl) . ' ' . \player\zhuangbei_element_name($nhEl) . '</span>';
    }
```

- [x] **Step 4: `game/zhuangtai.php` — badge nhiều hệ ở khung mặc đồ** (dòng 262-264)

Thay:

```php
        if (in_array($zbElem, array('kim', 'moc', 'thuy', 'hoa', 'tho', 'all'))) {
            $zbBadge = '<span class="zb-badge elem" style="color:'.\player\zhuangbei_element_color($zbElem).'">'.\player\zhuangbei_element_icon($zbElem).' '.\player\zhuangbei_element_name($zbElem).'</span>' . $zbBadge;
        }
```

bằng:

```php
        foreach (\player\zhuangbei_elements_parse($zbElem) as $nhEl) {
            $zbBadge = '<span class="zb-badge elem" style="color:'.\player\zhuangbei_element_color($nhEl).'">'
                . \player\zhuangbei_element_icon($nhEl) . ' ' . \player\zhuangbei_element_name($nhEl) . '</span>' . $zbBadge;
        }
```

- [x] **Step 5: `game/zhuangtai.php` — dòng nội tại ngũ hành trong khối linh căn**

Trong block `if (!empty($player->linhcan_id) && !empty($player->linhcan_name))` (dòng 433+), sau các dòng gán `$tierName/$tierIcon` (441-444), nối vào `$lcDetails`:

```php
    if (isset($player->nguhanh_info) && intval($player->nguhanh_info['match_count']) > 0) {
        $ngi = $player->nguhanh_info;
        $lcDetails .= '<div>✨ Nội tại ngũ hành: hợp căn ' . intval($ngi['match_count'])
            . ' món — bậc ' . intval($ngi['tier']) . ' +' . intval($ngi['pct'])
            . '% (' . implode(', ', $ngi['gains']) . ')</div>';
    }
```

(Khối này kết xuất qua `$lcDetails` đã hiển thị sẵn ở khu linh căn — kiểm bằng curl/lint vì render cần sid phiên.)

**Ghi chú thực thi:** dùng markup `profile-stat` đồng bộ các dòng khác trong khối (thay vì `<div>` trần), chèn sau vòng lặp `linhcan_stats`, chỉ hiện khi `match_count > 0`. Chưa xác nhận bằng mắt trong phiên game thật (Task 7).

- [x] **Step 6: `tongquan.php` — sửa cột cảnh giới trang bị + thêm mục thống kê Nạp Ngũ Hành**

6a. Sau hàm `tq_realm_name` (dòng 87) thêm hàm dịch ngưỡng ulv:

```php
// realm_require của trang bị là NGƯỠNG ulv (50 cấp/cảnh, rangeslv), không phải chỉ số rangesjj.
function tq_realm_req($ulvReq){ global $tqRealms; $ulvReq=(int)$ulvReq; if ($ulvReq<=0) return '—';
    $idx=(int)ceil($ulvReq/50); if ($idx>count($tqRealms)) return 'Cấp '.$ulvReq;
    return tq_realm_name($idx) . ' (cấp ' . $ulvReq . ')'; }
```

Đổi call-site bảng trang bị (dòng 214): `tq_realm_name($e['realm_require'])` → `tq_realm_req($e['realm_require'])`. (Hai call-site wugong/congphap dòng 237/254 giữ nguyên — đó là index cảnh thật.)

6b. Thống kê nạp hành — chèn mục mới vào nhóm `nuoi-duong` cạnh bảng trang bị:

```php
$tqNhName  = array('kim'=>'Kim','moc'=>'Mộc','thuy'=>'Thủy','hoa'=>'Hỏa','tho'=>'Thổ');
$tqNhColor = array('kim'=>'#C0C0C0','moc'=>'#228B22','thuy'=>'#1E90FF','hoa'=>'#FF4500','tho'=>'#8B4513');
$tqNhCount = array('kim'=>0,'moc'=>0,'thuy'=>0,'hoa'=>0,'tho'=>0);
$tqNhItems = 0;
$tqNhRes = $tqDb->query("SELECT element FROM playerzhuangbei WHERE element <> 'none'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tqNhRes as $tqNhRow) {
    $tqNhItems++;
    foreach (explode(',', $tqNhRow['element']) as $tqElOne) {
        $tqElOne = trim($tqElOne);
        if (isset($tqNhCount[$tqElOne])) $tqNhCount[$tqElOne]++;
    }
}
$tqNhRows = array();
foreach ($tqNhCount as $tqElKey => $tqElCnt) {
    $tqNhRows[] = array('<span style="color:' . $tqNhColor[$tqElKey] . '">● ' . $tqNhName[$tqElKey] . '</span>', tq_n($tqElCnt));
}
tq_sec('nuoi-duong', 'nap-hanh', '☯', 'Nạp Ngũ Hành (đang bật)', $tqNhItems,
    'Số món trang bị đã nạp hệ trong DB (playerzhuangbei.element, cột mới Task 1).',
    array('→ Rèn từ popup trang bị: nút Nạp <hệ>', 'Hợp linh căn: +3..22% chỉ số theo nấc 1/3/6/9/12 món', 'Thất bại chỉ mất nguyên liệu'),
    tq_searchable('tbl-nh', array('Hệ', 'Số món đã nạp'), $tqNhRows, ''));
```

**Ghi chú thực thi:** vòng query bọc trong `if ($tqDb){ try { … } catch (Exception $e) {} }` như phần đầu file (DB lỗi thì trang vẫn render, bảng hiện 0); nhãn rels đổi `nút Nạp <hệ>` → `nút Nạp [hệ]` vì `<hệ>` bị trình duyệt ăn như thẻ HTML.

- [x] **Step 7: Verify + checkpoint**

```bash
for f in class/function/itemtip.php game/bagzb.php game/zhuangtai.php tongquan.php; do "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l "$f"; done
curl -s http://localhost/tongquan.php | grep -c "nap-hanh"
cp class/function/itemtip.php bak/nguhanh/itemtip.task6.php
cp js/itemtip.js bak/nguhanh/itemtip.task6.js
cp game/bagzb.php bak/nguhanh/bagzb.task6.php
cp game/zhuangtai.php bak/nguhanh/zhuangtai.task6.php
cp tongquan.php bak/nguhanh/tongquan.task6.php
```
Expected: tongquan grep ≥ 1. Render tooltip nhiều tag + dòng Trạng thái cần mắt người — chưa test, ghi Task 7.

**Kết quả chạy thật:** lint sạch 12 file đã chạm (kể cả `game.php`; `js/itemtip.js` qua `node --check`); `curl /tongquan.php` → HTTP 200, grep `nap-hanh` = 2, bảng `tbl-nh` render đủ 5 hệ; cột cảnh yêu cầu trang bị giờ hiện `Nhập Môn (cấp 10)`, `Luyện Thể (cấp 60)`… thay vì index cảnh sai. Thêm 7 assert Task 6 vào `tests/test_nguhanh.php` (payload `elem` = 2 tag đúng thứ tự/icon/màu, rỗng khi `none`, rác bị lọc) → **52 assert, ALL PASS**. Checkpoint bổ sung: `tests/test_nguhanh.php` → `bak/nguhanh/test_nguhanh.task6.php`.

---

### Task 7: CHANGELOG + bàn giao kiểm thu

**Files:**
- Modify: `docs/CHANGELOG.md`
- Test: chạy lại toàn bộ + snapshot checkpoint cuối

- [x] **Step 1: append entry vào `docs/CHANGELOG.md`**

```markdown
## 2026-10-04 — Nạp Ngũ Hành (đa hệ) cho trang bị

- Cột mới `element` trên `playerzhuangbei` + `fangshi_zb`, `log_type` trên `player_chetao_log` (sql/nguhanh_kichhoat.sql — LOCAL ĐÃ AP, production chưa).
- Engine `class/function/nap_hanh.php`: parse/join đa hệ, cost (1+n đá tinh thạch hệ tương ứng, 500*rarity*(1+n) LT), rate 90-10n+bonus linh căn (san 40 tran 95), `nap_hanh_do` (that bai chi mat nguyen lieu).
- Popup trang bị: section "Nạp Ngũ Hành" + route `cmd=nap_hanh`; tooltip/túi/khung mặc đồ hiển thị NHIỀU tag hệ.
- Nội tại hợp linh căn trong `getplayer()`: nấc 1/3/6/9/12 món → +3/6/10/15/22% chỉ số trang bị đóng góp; hiển thị ở Trạng thái.
- Combat pve/boss: chọn hệ khắc chế tốt nhất trong set 16 món (`zhuangbei_pick_element`).
- tongquan.php: mục thống kê "Nạp Ngũ Hành" + sửa cột cảnh giới trang bị (trước hiển thị sai `realm_require` như index rangesjj).
- Config: class/config/nguhanh_config.json (không phải zhuangbei_config.json — Plan Z giữ nguyên).
```

- [x] **Step 2: Chạy lại toàn bộ verify**

```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
for f in class/player.php class/function/nap_hanh.php class/function/zhuangbei.php class/function/itemtip.php game.php game/nap_hanh.php game/zbinfo.php game/pve.php game/boss.php game/bagzb.php game/zhuangtai.php tongquan.php tests/test_nguhanh.php; do "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l "$f" || break; done
cp docs/CHANGELOG.md bak/nguhanh/CHANGELOG.final.md
```
Expected: `ALL PASS` + 13 dòng `No syntax errors`.

**Kết quả:** `ALL PASS` (52 assert) · `php -l` 13/13 sạch · e2e HTTP chạy lại sau Task 6 vẫn xanh (section popup, badge `💧 Thủy`, chặn trùng hệ, chặn thiếu đá) → file tạm `tests/_tmp_e2e_nguhanh.php` đã xoá · checkpoint `bak/nguhanh/CHANGELOG.final.md` + `bak/nguhanh/plan.final.md` (repo chưa có commit nên không tạo commit).

> **Đính chính + phát sinh sau bàn giao (cùng ngày):** (1) số liệu `52` ở trên là đếm sai của phiên, thực tế suite chạy **65 PASS / 0 FAIL**. (2) Chủ dự án phản hồi bằng ảnh: khu "Nạp Ngũ Hành" vỡ chữ → đã **thiết kế lại** thành danh sách một dòng mỗi hành (`.nh-*`), đổi nhãn thành **"Thuộc tính Ngũ Hành"**, chuyển logic render vào `nap_hanh_section_html()` để test CLI bao phủ, thêm `zhuangbei_element_color_ui()` cho màu sáng trên nền tối, bump `mystyle.css?v=2026100403`. Ảnh: `shot_nguhanh_ui.png`.

- [x] **Step 3: Bảng kiểm thu gửi user ( dán vào tin trả lời )**

| Hạng mục | Đã test CLI | Cần bấm tay trong game |
|---|---|---|
| parse/join/cost/rate/passive | ✔ test_nguhanh.php | — |
| nap_hanh_do success/fail/duplicate/owner/log | ✔ (ép force_roll) | roll thật ngẫu nhiên |
| merge element + zb_element_set | ✔ | — |
| pick_element khắc chế | ✔ | dòng chữ "Trang bị khắc X!" khi đánh quái |
| Nút Nạp trong popup + chi phí/tỉ lệ hiển thị | lint only | ✔ cần tài khoản test có Tinh Thạch (daoju 32-36) |
| Tooltip nhiều tag / túi / Trạng thái / tongquan | lint + curl grep | ✔ nhìn mắt (quy trình ảnh 1:1) |
| fangshi mang element | lint only | treo bán → mua lại món đã nạp |

- [x] **Step 4: Ghi chú production** — `sql/nguhanh_kichhoat.sql` phải chạy cùng đợt deploy với sid-schema debt (memory `project-sid-schema-debt`); chưa deploy, chưa test production. **Đã ghi** vào mục "CHƯA test" của entry CHANGELOG 04/10/2026.

---

## Tự-review (đã chạy khi viết plan)

- **Phủ spec:** §2 schema→Task 1; §3 luong ren→Task 2+5; §4 noi tai→Task 2 (ham)+3 (nut getplayer)+6 (hien thi); §5 khac che→Task 3 (pick)+4 (pve/boss); §6 hien thi→Task 5+6; §7 bien/xu ly loi→nap_hanh_do (quyen so lan cuoi, element la ep none, cache_clear, hoan khi doi chu); §8 kiem thu→Task 2/3/7; §9 trien khai→Task 1 (dump) + 7 Step 4.
- **Placeholder:** khong con TBD; duong dan MySQL that (5.5.29) da kiem bang `ls`; cot bat buoc game1 da truat information_schema.
- **Thong nhat ten:** `nap_hanh_cost($itemElementStr, $rarity, $he)` du dung Task 2/5; `zhuangbei_elements_parse/join` du dung Task 2/3/5/6; hook `$GLOBALS['nguhanh_force_roll']` chi xuat hien trong engine + test.

---

## Task 8 (vòng 2, sau phản hồi bằng ảnh của chủ dự án — 04/10/2026)

**Yêu cầu:** (1) "Các nút nạp 95% sửa thành kích hoạt, căn giữa dọc căn giữa ngang nữa"; (2) "Là phải thiết kế giao diện popup thành công đầy đủ chứ".

- [x] **Step 1: Nút `Kích hoạt`** — `nap_hanh_section_html()` đổi nhãn, dời `Tỉ lệ n%` xuống dòng chi phí (`<span class="nh-rate">`, vàng `#ffd86b`).
- [x] **Step 2: Căn giữa 2 trục** — `.nh-btn` bỏ `inline-flex`, thêm `display:flex; align-self:center; justify-self:center; text-align:center; line-height:1; white-space:nowrap`, cao 44px. Ảnh 390px xác nhận nút nguyên hàng vẫn giữa.
- [x] **Step 3: Thuật ngữ nhất quán** — mọi msg người dùng thấy đổi "nạp" → "kích hoạt" (`Kích hoạt hành Kim thành công!`, `… thất bại — đã mất nguyên liệu.`, `không thể kích hoạt trùng`, `Đã có n/5 hành`, `Chưa có hành nào`). Route/engine/spec vẫn `nap_hanh`.
- [x] **Step 4: Popup kết quả đầy đủ** — renderer mới `nap_hanh_result_html()` trong `class/function/nap_hanh.php`; `game/nap_hanh.php` còn 3 dòng gọi engine + `getplayer()` sau khi xoá cache rồi echo. Dùng `.pve-result-*`: head icon hành, pill `win/lose/block` (thêm `.pve-result-status.block`), dòng Trang bị / Hệ kích hoạt / Tỉ lệ rèn / Tinh thạch đã dùng (còn N) / Linh thạch đã dùng (còn N) / Hệ đang mang badge n/5 / Nội tại hợp linh căn, hint theo trạng thái, 2 nút "Xem lại trang bị" + "Túi trang bị". Hết in chuỗi DB thô `kim`.
- [x] **Step 5: Engine trả thêm dữ liệu** — `nap_hanh_do()` bổ sung `he`, `cost`, `zbname`, `zbys`; `rate > 0` = cờ "đã tiêu nguyên liệu" (phân biệt thất bại thật với chưa đủ đồ).
- [x] **Step 6: Verify** — `php -l` sạch; `tests/test_nguhanh.php` **82 PASS / 0 FAIL** (thêm 17 assert: nhãn nút, `nh-rate`, popup win/lose/block, số badge, không còn `>kim<`); e2e HTTP route thật với `force_roll=1` → `element` `thuy`→`kim,thuy`, đá 9→7, LT 100000→99000; `curl /tongquan.php` 200. Ảnh: `shot_nh_route_desktop.png`, `shot_nh_route_mobile.png`, `shot_nh_v4_mobile.png`. Bump `mystyle.css?v=2026100404`. Checkpoint `bak/nguhanh/*.task8.*`.

**Chưa test bằng mắt trong game thật:** popup khi roll ngẫu nhiên thật thất bại, và cảm giác chạm nút trên điện thoại thật (mới render 390px qua iframe headless).

---

## Task 9 (ngoài lề plan — nghệ thuật ô Phi Phong, 04/10/2026)

**Yêu cầu:** chủ dự án xem popup 【 Phàm Phi Sát Phong 】 và bắt lỗi: *"Phi phong là lớp kiểu áo choàng bên ngoài mà có phải cánh đâu"*. Khi trình 3 phương án, anh chốt: *"trong source linh kiếm cửu thiên như mình đã đưa rất nhiều mẫu phi phong bạn hãy kiểm tra và lấy hoặc vẽ art phi phong như bạn bảo. bạn có thể tra thêm tài liệu về phi phong trên google"*.

- [x] **Step 1: Truy nguồn gốc lỗi** — `zhuangbei_image_path()` lấy `images/{zbid}.png`, nên 10 món ô 8 (`16-20`, `91-95`) + icon `images/slot/zb_slot_08.png` đều là **đôi cánh** — art gốc game Trung vẽ vậy, code không sai. Đối chiếu `shot_slot8_family.png` (10 ảnh cánh, 80×80 RGBA) và `shot_zbid_art_1_25.png` (mỗi họ 5 món cùng hình khác màu).
- [x] **Step 2: Quét 4 source game tìm icon 披风** — `E:/9kiem` (20.205 ảnh): `assets/icon/skin` 534 ảnh theo dải id, lập bảng 21=thú cưng, 22=tọa kỵ, 23=vũ khí, **24=cánh**, 25=trang phục, 26=phụ kiện, 27=avatar, 28=giáp → không có ô áo choàng. `F:/MuH5`, `F:/TuTienPhucMa`, `E:/natramadongh5`: `find -iname "*pifeng*" / "*cloak*" / "*cape*"` rỗng; grep `披风` trong mọi file text/json = 0 kết quả (config LKCT nằm trong `configs.obj` nhị phân). **Kết luận: không có nguồn để copy → vẽ mới** (đúng nhánh "hoặc vẽ art phi phong" anh cho phép).
- [x] **Step 3: Sinh 3 master bằng ImageGen** — prompt yêu cầu áo choàng tu chân bay lơ lửng không có người mặc, nền **magenta phẳng #FF00FF** để chroma-key sạch, không dùng hồng/tím trên vật thể. Ba bậc: 1–3 áo vải thô khoá sắt (matte), 4–6 áo lụa thêu vân hạc khoá ngọc + mist, 7–10 áo thần tan thành tinh quang + khoá rồng (theo [[feedback-tiered-visual-effects]]).
- [x] **Step 4: Pipeline `bak/slot8/make_cloak_icons.py`** — key magenta (`alpha = 1 − clip((min(r,b) − g)/0.35)`, despill r/b về g) → cắt theo bbox alpha>16 → pad vuông → LANCZOS 80×80 → tint theo `zbys` từng zbid (ramp `dark=target*0.16 → target → white`, mốc 0.66).
- [x] **Step 5: Icon ô riêng** — `zb_slot_08.png` tint xám thép `#8f9aab` rồi hạ sáng ×0.42 để độ sáng trung bình 72.8 lọt vào dải 38.8–92.9 của 15 icon còn lại (đo bằng numpy, không đoán bằng mắt).
- [x] **Step 6: Chống cache** — thêm `const ZB_IMG_VER = '2026100405'` + `zhuangbei_img_ver()` vào `class/function/zhuangbei.php`; `zhuangbei_image_path()` và `zhuangbei_slot_icons()` nối `?v=` (bỏ qua nếu path đã có `?` hoặc rỗng). Ảnh PNG cũ có heuristic freshness rất dài nên điện thoại sẽ vẫn hiện cánh nếu không có bước này.
- [x] **Step 7: Verify** — `php -l` sạch toàn bộ `class/ game/ js/`; `tests/test_nguhanh.php` **82 PASS / 0 FAIL**; probe CLI `zhuangbei_image_path()` trả `./images/16.png?v=2026100405` và không nhân đôi query; screenshot headless `shot_slot8_cloak_desktop.png` (10 popup, CSS thật), `shot_slot8_mobile.png` (390px iframe), `shot_slot8_slots.png` (16 ô cạnh nhau). Backup `bak/slot8/wing_original/` (11 file).

**Chưa test:** chỉ mở qua trang harness tái hiện đúng markup `.zb-head` / `.equip-thumb` + `mystyle.css` thật; chưa mở bằng route đăng nhập thật (`cmd=chakanzb`, `cmd=trangbi`, túi đồ, Phương Thị, of hàng NPC).
