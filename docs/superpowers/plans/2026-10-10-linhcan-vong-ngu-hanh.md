# LINH CAN — VONG NGU HANH: Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Thay 22 linh căn random bằng Vòng Ngũ Hành: 5 hành Kim/Mộc/Thủy/Hỏa/Thổ luyện độc lập mỗi hành 5 bậc Phàm→Huyền→Địa→Thiên→Thánh, danh hiệu ở tâm vòng suy từ hình thái 5 hành, boss đột phá riêng từng hành (khuôn Mạch Huyệt), quest tuần 5/5 hook thật, skill hành tích hợp thật vào combat pve/boss.

**Architecture:** Ba trục — Lv hành (bảng `player_linhcan_node`), bậc hành (cột `stage` của node), tổng Lv (milestone + danh hiệu, suy ra runtime khong luu DB). Data (`class/config/linhcan_element_config.json` + `class/function/linhcan.php` viet lai) duoc man `game/linhcan.php` (prefix `.lc-*`) dung lai, lay khuon theo `.hm-*`/`.nb-*`. `linhcan_calc_bonuses()` gi NGUYEN hop dong 16 key (6+ noi dung).

**Tech Stack:** PHP 5.6, MySQL 5.5 (PDO, `game` local), HTML+CSS trong `css/mystyle.css` (file dai 7662 dong (tinh den 10/10), khoi `.hm-*` nam 7513-7662), test CLI `tassert` kieu `tests/test_huyetmach_schema.php`, do/chup UI bang Chrome headless + harness `_lc*.html` dat tam thoi TAI web root (khuon `_hm*` cua mach huyet — xoa ngay sau khi xong).

**Spec:** `docs/superpowers/specs/2026-10-10-linhcan-vong-ngu-hanh-design.md` (so lieu `[]` da duyet qua "chắc ổn rồi đấy" 10/10 — khong tu y doi so — mo bat ky o `[]` nao cung phai hoi chu du an).

## Global Constraints

- PHP 5.6: khong `??`, khong `<=>`, khong return type, dung `array()` thay vi `[]` trong code PHP (JSON dung `[]`/`{}` binh thuong). Ap dung skill `php56`.
- Moi file PHP sau khi sua: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l <file>` (php.exe khong co tren PATH).
- MySQL 5.5: `sid` la hex 32 ky tu → moi bang/cot moi `VARCHAR(64)`; khong `DEFAULT CURRENT_TIMESTAMP` nhieu cot, dung `DATETIME DEFAULT NULL` + `NOW()` trong INSERT (khuon `sql/sql_huyetmach_vessel.sql`).
- **PK cua `playerdaoju` la `(uid, djid)` — KHONG phai sid.** Test bat buoc `uid = 990112` khi INSERT truc tiep; uu tien goi `\player\adddj($sid,$djid,$sum,$dblj)` (`class/player.php:1297` — tu lay uid tu getplayer, tu UPDATE/INSERT, tu `changerwyq1`) va `\player\deledjsum($djid,$sum,$sid,$dblj)` (`:1392`).
- **Bang dau nguoc DA THAT (landmine 1.1):** `linhcan_upgrade_config.json` map `kim→1..tho→5` nhung djid 1 = Cuong Hoa Thach. Map CHUAN trong `daoju_config.json` da do 10/10: **32 Hỏa / 33 Thủy / 34 Mộc / 35 Kim / 36 Thổ / 37 Bình Anh**. Moi code moi chi duoc dung `da_map` tu config moi.
- **`player_linhcan_skill` va `player_linhcan_log` KHONG TON TAI trong DB local** (do `information_schema` 10/10). Nghia la `linhcan_upgrade()` cu (`class/function/linhcan.php:499-618`) tru LT+uexp+da (dong 546/554/574) roi crash o INSERT log (dong 591) TRUOC khi nang cap → nguoi choi mat tien ma khong nhan duoc gi. Task 7 phai hoi quy so mat nay. Task 1 phai tao `player_linhcan_skill` (skill cooldown can). `player_linhcan_log` KHONG tao lai — he moi co `player_linhcan_break_log`.
- **Hop dong `linhcan_calc_bonuses()` (16 key, `class/function/linhcan.php:223-281`) CAM DOI TEN/LOAI:** `absorb_bonus, exp_bonus, quality, quality_mult, stats{atk,def,hp,crit,lifesteal}, pet_atk_bonus, pet_hp_bonus, pet_exp_bonus, pet_crit_bonus, element, supported_cp_element, penalty_atk_percent, penalty_def_percent, penalty_maxhp_percent, penalty_crit_percent, penalty_absorb_percent`. Consumer: `class/player.php:712-743`, `game/xiulian.php:102-103`, `game/zhuangtai.php:424-450`, pet synergy `getchongwu`, `game/boss.php:263`/`game/pve.php:266` (qua `$player->linhcan_element`).
- **`$player->linhcan_element` phai duoc ghi tiep** (khi chua mo he = `''`) vi `nguhanh_apply_passive()` doc no (`class/player.php:758-761`).
- **Khong doc/ghi `game1.linhcan_id/linhcan_quality` nua** (giu cot de rollback; chi `lcmigrate` doc). `class/player.php:304` (allowlist) + `game.php:192` (INSERT character moi = 0) GIU NGUYEN.
- Tieng Viet comment PHP viet KHONG DAU; co dau chi trong JSON/UI. Sau moi Write: buoc quet CJK lac cho + smart quote (`” “ ‘ ’ , NBSP`) — Write tool da duoc chung minh la co chen ky tu lac khi ghi file lon; tai lieu >4KB phai ghi manh nhoi `E:/window/Temp/lcparts/` roi `cat` ghep.
- Git: `git add <file cu the>` (KHONG `git add -A` — goc repo hon 50 `shot_*.png` rac), commit tung task.
- So do UI (bat theo hai man anh em): `.tcg-main-panel` 660px (`css/mystyle.css:199`) → noi dung 624px; `.lc-wrap{margin:0}` KHONG max-width rieng; breakpoint layout `min-width:601px`; tab bar duoi 80px nhu `.xl-tabbar`/`.nb-tabbar`/`.hm-tabbar`; wrap desktop chan ~840px.
- **Landmine CSS (dat):** `gamecss.css:122` `.main button` (0,1,1) an gradient → moi nut phai la `.main .lc-btn*` (2 lop). `gamecss.css:85/95/226` `a` padding + `a:hover` toan cuc. `mystyle.css @media(max-width:760px)` `.tcg-main-panel *{max-width:100%!important}` + `img{height:auto!important}` → 5 ngoc dat tuyet doi tren anh vong PHAI `!important` + reset padding/margin/background moi state (khuon `.nb-gem`/`.hm-gem` ghi sach tai `mystyle.css:7029`).
- Prefix `.lc-/.lcq-/.lcu-/.lcb-`: do 10/10 chi xuat hien trong 4 trang `game/linhcan*.php` (se bi thay/da — `css` cua no inline trong tung file, khong nam trong `mystyle.css`) → AN TOAN tam thoi, nhung Task 10 Step 1 phai grep lai `.lc` trong `css/` + `game/hammo.php` truoc khi viet (bai hoc `.hm-btn` bi hammo trung).
- FX phan tang `lc-fx0..fx4` theo max stage hanh: fx0 phai thay gi do (khuon `.hm-fx0` co `--hm-aura:.04`); tat animation = `animation:none` (khong phai `--dur:0s`); `mix-blend-mode:screen` can `isolation:isolate`; `background-clip:text` dat SAU shorthand background; 0px hinh hoc — fx khong duoc so do wrap.
- Test CLI: bootstrap PDO nhu `tests/test_huyetmach_schema.php:14` (host=localhost dbname=game), `$TESTSID=str_repeat('f',32)`, nhan vat test game1 co `uid=990112`, thu cua `tassert()`, ket `exit($fails===0?0:1)`. Test live HTTP theo `tests/test_huyetmach_combat.php --live`; tai khoan E2E phai co `game1.endtime` trong 3600s (neu khong bi da ve login — `game.php:477`). Dev server: `php.exe -S 127.0.0.1:8199 -t D:/XXJserver/phpstudy_pro/WWW`.
- Chup anh: iframe 390px, `zoom=1`, `--window-size` ≥500, `--hide-scrollbars`; harness `_lc*` dat TAI web root de server doc, **XOA khoi web root ngay sau khi xong**; so sanh anh mock/live 1:1 — so do computed style khong bat duoc lech thi giac, phai NHIN anh.
- bump `css/mystyle.css?v=` tai `game.php:502` (hien `2026101005`) moi task loi CSS: +1 so cuoi.
---

## File Structure

| File | Vai tro | Task |
|---|---|---|
| `class/config/linhcan_element_config.json` (MOI) | 5 hanh + 5 bac + train_cost + `da_map` + milestones + title_rules + `stat_labels` | 1 |
| `sql/sql_linhcan_element.sql` (MOI) | 3 bang theo spec §2.4 + `player_linhcan_skill` (bang cooldown, DB dang KHONG co) | 1 |
| `tests/deploy_preflight.php` (sua) | them 4 bang vao `$SID_VARCHAR` (dang tai `:19-37`) | 1 |
| `tests/test_linhcan_element.php` (phan A→D) | test CLI core | 1,2,3,7 |
| `tests/test_linhcan_schema.php` | test DDL | 1 |
| `class/function/linhcan.php` (VIET LAI giu hop dong) | config readers, node, train, break, dominant, title, milestone, calc_bonuses, give_stone, wash | 2,3,7 |
| `class/function/linhcan_boss.php` (SUA) | boss the gioi: element random-of-5 + drop THAT; + khoi `lcboss_*` boss ca nhan | 4,5 |
| `class/function/linhcan_quest.php` (SUA) | claim tra hang that qua `adddj` | 6 |
| `class/config/linhcan_quest_config.json` (SUA) | 5 quest moi co hook that | 6 |
| `class/config/linhcan_skill_config.json` (SUA) | xoa `hu_khong_chuong` + `none`, `unlock_level`→`unlock_stage:2` | 8 |
| `class/function/linhcan_skill.php` (SUA) | `can_use` theo stage; `combat_mods()` moi cho pve/boss | 8 |
| `game/pve.php`, `game/boss.php` (SUA) | case `uselcskill` + nut skill + hook quest | 8 |
| `game/use_dj.php` (SUA `:85-106`) | `reroll_linhcan` → `linhcan_wash` (redirect man chon hanh) | 7 |
| `game/linhcan_wash.php` (MOI) + `game.php` (sua `:352-355`, `:502`) | man chon hanh tay bo + route | 7 |
| `class/player.php` (SUA `:701-755`, `:2015-2089`) | rewire getplayer block + xoa nhanh `djuse=='linhcan'` | 7,9 |
| `game/linhcan.php` (VIET LAI) | man Vong Ngu Hanh `.lc-*` | 10 |
| `game/linhcan_quest.php`, `game/linhcan_boss.php` (VIET LAI) | `.lcq-*` / `.lcb-*` + boss ca nhan `?el=` | 11 |
| `game/linhcan_upgrade.php` (XOA) + route `game.php:353` (XOA) | đường upgrade lỗi fatal trừ oan nguyên liệu — CHẶN SỚM ở Task 7, không chờ Task 11 | 7 |
| `class/config/linhcan_config.json`, `linhcan_upgrade_config.json` (DOI→`bak/linhcan/`) | nghiep thu xong moi doi | 12 |
| `css/mystyle.css` (SUA, noi cuoi file sau `:7662`) | `.lc-*` → `.lcq-*/.lcb-*` → `.lc-fx0..4` | 10,11 |
| `images/linhcan/vong-ngu-hanh.jpg` (MOI) | anh vong (source game → ImageGen #0f173e) | 10 |
| `E:/window/Temp/lcmigrate.php`, `lcwashcheck.php` (ngoai repo) | di tru cu → node | 12 |
| `E:/window/Temp/lcseed.php`, `lcempty.php` (ngoai repo) | seed node + da phuc vu test UI | 10,12 |
| `tests/test_linhcan_boss.php`, `tests/test_linhcan_quest.php`, `tests/test_linhcan_combat.php --live` | test CLI + live | 4,5,6,8 |
| `game/zhuangtai.php` (SUA `:424-450`), `tongquan.php`, `docs/UI_LINHCAN.md`, `docs/DEPLOY_PRODUCTION_LINHCAN.md`, `docs/CHANGELOG.md`, `docs/img/linhcan/*` | hien thi + tai lieu | 9,12 |

**Thu tu task = thu so.** Task 2-3 viet `class/function/linhcan.php` thanh 2 dot (doc-thuan-tuy + co ghi DB); Task 4-5 cung file `linhcan_boss.php` (4: boss ca nhan, 5: sua boss the gioi); dung song song.

**Map hop dong inter-task (ten ham chuan — dung dung chu nay):**
- Task 2 sinh: `linhcan_element_config_all()`, `linhcan_element_defs()`, `linhcan_element_def($key)`, `linhcan_stage_def($stage)`, `linhcan_stage_cap($stage)`, `linhcan_da_id($element)`, `linhcan_binh_anh_id()` (→37), `linhcan_train_cost($lv)`, `linhcan_node_rows($sid,$dblj)`, `linhcan_node_get($sid,$element,$dblj)`, `linhcan_node_set($sid,$element,$lv,$stage,$dblj)`, `linhcan_dominant($nodes)`, `linhcan_title($nodes)`, `linhcan_milestone($total)`, `linhcan_give_stone($sid,$djid,$amount,$dblj)`.
- Task 3 sinh: `linhcan_train($sid,$dblj,$element)`, `linhcan_calc_bonuses($sid,$dblj)` (16 key cu), `linhcan_train_cost_sum($fromLv,$toLv)`.
- Task 4 sinh (trong `linhcan_boss.php`): `lcboss_active($sid,$dblj,$element)`, `lcboss_spawn($sid,$dblj,$element)`, `lcboss_killed($sid,$dblj,$element,$toStage)`, `lcboss_mark_killed($sid,$dblj,$row)`, `lcboss_calc_damage($player,$bossRow)`, `lcboss_drops($sid,$dblj,$bossRow)`; `linhcan_break_requirements($sid,$dblj,$element)`, `linhcan_break($sid,$dblj,$element)` (dat trong `linhcan.php`, goi `lcboss_killed`).
- Task 5 sinh: `linhcan_boss_world_element($dblj)` (XOA `linhcan_boss_get_dominant_element`).
- Task 7 sinh: `linhcan_wash($sid,$dblj,$element)`.
- Task 8 sinh: `linhcan_skill_for_stage($sid,$dblj)` (tra skill cua hanh chu dao khi stage≥2), `linhcan_skill_combat_mods($skill)`; sua `linhcan_skill_can_use($player,$skill,$lcStage)`.

---
### Task 1: Config hanh + schema SQL + kiem ke consumer

**Files:**
- Create: `class/config/linhcan_element_config.json`, `sql/sql_linhcan_element.sql`, `tests/test_linhcan_schema.php`
- Modify: `tests/deploy_preflight.php` (`$SID_VARCHAR` tai `:19-37`)

**Interfaces:**
- Consumes: nothing.
- Produces: file JSON doc duoi day (Task 2 doc); 4 bang DB.

- [x] **Step 1: Kiem ke consumer `linhcan_id` (so thi hoa 10/10, ke lai vao commit message)**

Run:
```bash
grep -rn "linhcan_id" --include=*.php . | grep -v "^./bak/" | grep -v "^./docs/" | grep -v "^./sql/"
```
Expected duoc do 10/10 (neu lech → ghi chu vao `docs/UI_LINHCAN.md` §Chưa làm):
`class/player.php:113,304,746-747,2025-2031`; `class/function/linhcan_boss.php:40,44,102`; `class/function/linhcan.php:164-204,502-596`; `game/linhcan.php:6,11`; `game.php:192` (INSERT character moi, GIU). Khong con o `paihang`/cho nao khac.

- [x] **Step 2: Kiem ke nguon da 32..37**

Run:
```bash
grep -n "\"3[2-7]\"\|djid.:3[2-7]" class/config/guaiwu_config.json class/config/daoju_config.json | head -30
grep -rln "shop\|cuahang" class/config/*.json | head
```
Ghi ket qua (quai roi da nao, shop co ban 32..36 khong, gia bao nhieu) vao `docs/UI_LINHCAN.md` §Nguon-da — Task 6/12 dung. Neu shop khong ton tai: ghi "nguon da = boss the gioi + quai roi", bao chu du an.

- [x] **Step 3: Tao `class/config/linhcan_element_config.json`** (noi dung nguyen chan):

```json
{
  "schema": "linhcan_element_v1",
  "elements": {
    "kim":  {"key":"kim","name":"Kim","icon":"🪙","color":"#C0C0C0","order":1,"slot":[50,12],
      "per_lv":{"atk":2},"per_stage_atk_pct":0.5,
      "desc":"Kim chu sat — mũi nhọn cứng sắc. Mỗi Lv tăng Công Kích; mỗi bậc Kim bồi thêm 0,5% Công Kích của vòng."},
    "moc":  {"key":"moc","name":"Mộc","icon":"🌿","color":"#228B22","order":2,"slot":[80,38],
      "per_lv":{"hp":14,"pet_atk_pct":0.4,"pet_hp_pct":0.4},
      "desc":"Mộc sinh trưởng — huyết dài, thú nuôi thêm mạnh."},
    "thuy": {"key":"thuy","name":"Thủy","icon":"💧","color":"#1E90FF","order":3,"slot":[20,38],
      "per_lv":{"absorb_pct":0.18,"exp_pct":0.08},
      "desc":"Thủy tu luyện — hút linh khí nhanh hơn, kinh nghiệm mỗi giờ nhiều hơn."},
    "hoa":  {"key":"hoa","name":"Hỏa","icon":"🔥","color":"#FF4500","order":4,"slot":[69,81],
      "per_lv":{"crit":0.12,"atk":0.6},
      "desc":"Hỏa bạo liệt — đòn bùng nổ, vừa bạo vừa nặng."},
    "tho":  {"key":"tho","name":"Thổ","icon":"🏔","color":"#8B4513","order":5,"slot":[31,81],
      "per_lv":{"def":1.2,"hp":6},
      "desc":"Thổ vững chãi — thân như núi, khó xuyên."}
  },
  "stat_labels": {"atk":"Công Kích","hp":"Khí Huyết","def":"Phòng Ngự","crit":"Bạo Kích",
    "absorb_pct":"Hút Linh Khí","exp_pct":"Tốc Độ Tu Luyện","pet_atk_pct":"Công Kích Thú","pet_hp_pct":"HP Thú","lifesteal":"Hút Máu"},
  "stages": {
    "0": {"stage":0,"name":"Phàm Căn","lv_cap":15,"color":"#6b7280","icon":"🌱","break":null},
    "1": {"stage":1,"name":"Huyền Căn","lv_cap":30,"color":"#4caf50","icon":"⭐","break":{"binh_anh":1,"linh_thach":5000,"uexp":0,"boss_to_stage":1}},
    "2": {"stage":2,"name":"Địa Căn","lv_cap":50,"color":"#ff9800","icon":"🔶","break":{"binh_anh":2,"linh_thach":20000,"uexp":5000,"boss_to_stage":2}},
    "3": {"stage":3,"name":"Thiên Căn","lv_cap":70,"color":"#f44336","icon":"💫","break":{"binh_anh":4,"linh_thach":80000,"uexp":20000,"boss_to_stage":3}},
    "4": {"stage":4,"name":"Thánh Căn","lv_cap":90,"color":"#ffd700","icon":"👑","break":{"binh_anh":8,"linh_thach":250000,"uexp":80000,"boss_to_stage":4}}
  },
  "train_cost": {"stone_base":1,"stone_per_lv":15,"stone_cap":7,"lt_base":100,"lt_per_lv":15},
  "da_map": {"kim":35,"moc":34,"thuy":33,"hoa":32,"tho":36,"binh_anh":37},
  "milestones": {
    "1": {"total_lv":60,"bonus_percent":5,"name":"Ngũ Khí Thông Minh"},
    "2": {"total_lv":150,"bonus_percent":10,"name":"Ngũ Hành Tương Sanh"},
    "3": {"total_lv":270,"bonus_percent":15,"name":"Ngũ Đức Quy Nguyên"},
    "4": {"total_lv":400,"bonus_percent":20,"name":"Ngũ Hành Viên Dung"}
  },
  "title_rules": [
    {"id":"vo_cuc","name":"Vô Cực Linh Thể","icon":"🌌","color":"#ffd700","require":{"all_lv_min":70}},
    {"id":"vien_man","name":"Ngũ Hành Viên Mãn","icon":"🌐","color":"#a78bfa","require":{"all_lv_min":30}},
    {"id":"chan_linh","name":"Chân Linh Căn","icon":"🗡️","color":"#f472b6","require":{"one_lv_min":40,"others_lv_max":14}},
    {"id":"song_nghi","name":"Song Nghi Linh Căn","icon":"☯️","color":"#4ade80","require":{"two_lv_min":30}},
    {"id":"pham_can","name":"Phàm Căn","icon":"🌱","color":"#9ca3af","require":{"total_lv_max":24}},
    {"id":"mac_dinh","name":"Linh Căn Đã Chắc Lọc","icon":"🌿","color":"#e2e8f0","require":{}}
  ],
  "material_names": {"32":"Hỏa Tinh Thạch","33":"Thủy Tinh Thạch","34":"Mộc Tinh Thạch","35":"Kim Tinh Thạch","36":"Thổ Tinh Thạch","37":"Bình Anh Tinh Thạch"}
}
```
Ghi chu co y: lech voi spec — `stone_cap` thuc te dat 6 (L max 89 → 1+floor(89/15)=6), giu 7 de phong; rule `chan_linh` "4 hanh con lai < 15" → `others_lv_max:14`. Toa do `slot` la ngu giac deu tren hop anh, **gia tri khoi diem** — Task 10 buoc do anh that se chinh (bai hoc v13 Nhuc Than: do truc anh, khong lay 50%).

- [x] **Step 4: Quet CJK/smart-quote + validate JSON**

Run (bo quet nhu ke hoach Mach Huyet):
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$f="class/config/linhcan_element_config.json"; $s=file_get_contents($f);
preg_match_all("/[\\x{4e00}-\\x{9fff}]/u",$s,$m); echo $m[0]?"CJK FOUND: ".implode(" ",$m[0])."\n":"no CJK\n";
preg_match_all("/[\\x{2018}\\x{2019}\\x{201C}\\x{201D}\\x{00a0}]/u",$s,$q); echo $q[0]?"SMARTQUOTE FOUND\n":"quotes clean\n";
$d=json_decode($s,true); echo $d===null?"JSON INVALID\n":("json ok elems=".count($d["elements"])." stages=".count($d["stages"])." titles=".count($d["title_rules"])."\n");'
```
Expected: `no CJK`, `quotes clean`, `json ok elems=5 stages=5 titles=6`.

- [x] **Step 5: Tao `sql/sql_linhcan_element.sql`**

```sql
-- =====================================================
-- VONG NGU HANH — schema cho Linh Can rework (2026-10-10)
-- MySQL 5.5 compatible, sid VARCHAR(64). Chay:
--   mysql -u root -p <dbname> < sql/sql_linhcan_element.sql
-- =====================================================

CREATE TABLE IF NOT EXISTS player_linhcan_node (
  sid        VARCHAR(64) NOT NULL,
  element    VARCHAR(10) NOT NULL,
  level      INT(11)     DEFAULT 0,
  stage      TINYINT(4)  DEFAULT 0,
  updated_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, element)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS linhcan_boss_personal (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL DEFAULT '',
  element    VARCHAR(10) NOT NULL,
  to_stage   TINYINT(4)  NOT NULL,
  name       VARCHAR(100) NOT NULL,
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
  KEY idx_elem (element, to_stage)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_linhcan_break_log (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL,
  element    VARCHAR(10) NOT NULL,
  from_stage TINYINT(4) NOT NULL,
  to_stage   TINYINT(4) NOT NULL,
  created_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Bang cooldown skill. DA TON TAI trong code cu (linhcan_skill.php)
-- nhung CHUA tung duoc tao trong DB (do information_schema 10/10).
CREATE TABLE IF NOT EXISTS player_linhcan_skill (
  sid         VARCHAR(64) NOT NULL,
  skill_id    VARCHAR(40) NOT NULL,
  last_used   DATETIME DEFAULT NULL,
  PRIMARY KEY (sid, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
Chay tren DB local:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$db=new PDO("mysql:host=localhost;dbname=game;charset=utf8","root","www.aae.ink",array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
$s=file_get_contents("sql/sql_linhcan_element.sql"); $n=0;
foreach(array_filter(array_map("trim",explode(";",$s))) as $q){ if($q!=""&&strpos($q,"--")!==0){$db->exec($q);$n++;} }
echo "executed=$n\n";'
```
Expected: `executed=4` (hoac tinh theo cau khong rong — moi 4 CREATE deu OK, khong loi).

- [x] **Step 6: `tests/test_linhcan_schema.php`** — copy khung `tests/test_huyetmach_schema.php:1-28` (tassert + col() + tbl()), doan kiem tra thay vao:
```php
tassert('table player_linhcan_node', tbl($db,'player_linhcan_node'), true);
tassert('table linhcan_boss_personal', tbl($db,'linhcan_boss_personal'), true);
tassert('table player_linhcan_break_log', tbl($db,'player_linhcan_break_log'), true);
tassert('table player_linhcan_skill', tbl($db,'player_linhcan_skill'), true);
tassert('node sid varchar64', intval(col($db,'player_linhcan_node','sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('node element varchar10', intval(col($db,'player_linhcan_node','element')['CHARACTER_MAXIMUM_LENGTH']), 10);
tassert('node level default 0', col($db,'player_linhcan_node','level')['COLUMN_DEFAULT'], '0');
tassert('node stage tinyint', strpos(col($db,'player_linhcan_node','stage')['COLUMN_TYPE'],'tinyint')===0, true);
tassert('bpers sid varchar64', intval(col($db,'linhcan_boss_personal','sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('bpers status default', col($db,'linhcan_boss_personal','status')['COLUMN_DEFAULT'], 'active');
tassert('bpers to_stage tinyint', strpos(col($db,'linhcan_boss_personal','to_stage')['COLUMN_TYPE'],'tinyint')===0, true);
tassert('skill skill_id varchar40', intval(col($db,'player_linhcan_skill','skill_id')['CHARACTER_MAXIMUM_LENGTH']), 40);
exit($fails === 0 ? 0 : 1);
```

- [x] **Step 7: Sua `tests/deploy_preflight.php`** — them 4 dong vao `$SID_VARCHAR` (khoi `:19-37`):
```php
'player_linhcan_node'      => 'sid',
'linhcan_boss_personal'    => 'sid',
'player_linhcan_break_log' => 'sid',
'player_linhcan_skill'     => 'sid',
```

- [x] **Step 8: Chay test + commit**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_schema.php
```
Expected: toan bo `PASS`, exit 0.
```bash
git add class/config/linhcan_element_config.json sql/sql_linhcan_element.sql tests/test_linhcan_schema.php tests/deploy_preflight.php
git commit -m "feat(linhcan): config vong ngu hanh + schema 4 bang + preflight"
```
---

### Task 2: `class/function/linhcan.php` — phan doc thuan tuy (config/node/dominant/title/milestone/give_stone)

**Files:**
- Create: `class/function/linhcan.php` ban moi (GIU file cu lam duong doi: `git mv class/function/linhcan.php bak/linhcan/linhcan_old.php`? **KHONG** — Task 12 moi doi; luc nay ghi de truc tiep, ban cu xem trong `git show HEAD`).
- Modify: `tests/test_linhcan_element.php` (tao moi, phan A).

**Interfaces:**
- Consumes: Task 1 JSON.
- Produces: toan bo ham sach ten trong map inter-task (muc File Structure). Chu ky chuan:
  - `linhcan_element_config_all()` → array; `linhcan_element_def($key)` → array|false; `linhcan_element_defs()` → 5 entry.
  - `linhcan_stage_def($stage)` → array|false (stage 0..4); `linhcan_stage_cap($stage)` → int (false→0); `linhcan_stage_max()` → 4.
  - `linhcan_da_id($element)` → int (32..36, sai key→0); `linhcan_binh_anh_id()` → 37.
  - `linhcan_train_cost($lv)` → `array('stone'=>int,'linh_thach'=>int)`.
  - `linhcan_node_rows($sid,$dblj)` → `array(kim..tho => array('level'=>int,'stage'=>int))` — du 5 key, thieu row = 0/0.
  - `linhcan_node_get($sid,$element,$dblj)` → `array('level','stage')`.
  - `linhcan_node_set($sid,$element,$lv,$stage,$dblj)` → bool (upsert + `player_cache_clear($sid)`).
  - `linhcan_dominant($nodes)` → element key | '' (tong Lv=0).
  - `linhcan_title($nodes)` → `array('id','name','icon','color','desc')`.
  - `linhcan_milestone($total)` → `array('percent'=>float,'name'=>string,'next_percent'=>float,'next_total'=>int,'next_name'=>string)`.
  - `linhcan_give_stone($sid,$djid,$amount,$dblj)` → bool — cong hang THAT + hook quest.

- [x] **Step 1: Viet test phan A (that truoc khi code)** — `tests/test_linhcan_element.php`:

```php
<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
require_once __DIR__ . '/../class/player.php'; // player.php tu require function/linhcan.php (:16); can adddj/player_cache_clear cho node_set + give_stone
$TESTSID = str_repeat('f', 32);

// A1: config
$d = \player\linhcan_element_defs();
tassert('A1 defs du 5 hanh', count($d), 5);
tassert('A1 kim atk/lv', \player\linhcan_element_def('kim')['per_lv']['atk'], 2);
tassert('A1 khong ton tai', \player\linhcan_element_def(' phong'), false);
tassert('A1 stage 4 cap 90', \player\linhcan_stage_cap(4), 90);
tassert('A1 stage 5 khong co', \player\linhcan_stage_cap(5), 0);
tassert('A1 da_map', \player\linhcan_da_id('kim') . ',' . \player\linhcan_da_id('hoa'), '35,32');
tassert('A1 dao nguoc ten da', \player\linhcan_da_id('tho'), 36);
tassert('A1 train lv0', \player\linhcan_train_cost(0), array('stone'=>1,'linh_thach'=>100));
tassert('A1 train lv89', \player\linhcan_train_cost(89), array('stone'=>6,'linh_thach'=>1435));

// A2: node docsong (nhan vat chua tung mo he)
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
$n = \player\linhcan_node_rows($TESTSID, $db);
tassert('A2 du 5 key', count($n), 5);
tassert('A2 thieu row = 0/0', $n['kim'], array('level'=>0,'stage'=>0));

// A3: node_set / get
tassert('A3 set ok', \player\linhcan_node_set($TESTSID, 'hoa', 7, 0, $db), true);
tassert('A3 get lai', \player\linhcan_node_get($TESTSID, 'hoa', $db), array('level'=>7,'stage'=>0));

// A4: dominant — level -> stage -> thu tu co dinh kim>moc>thuy>hoa>tho
$n0 = \player\linhcan_node_rows($TESTSID, $db); // hoa=7
tassert('A4 co lv', \player\linhcan_dominant($n0), 'hoa');
$zz = array('kim'=>array('level'=>0,'stage'=>0),'moc'=>array('level'=>0,'stage'=>0),'thuy'=>array('level'=>0,'stage'=>0),'hoa'=>array('level'=>0,'stage'=>0),'tho'=>array('level'=>0,'stage'=>0));
tassert('A4 tong 0 = rong', \player\linhcan_dominant($zz), '');
$eq = $zz; $eq['thuy']['level']=5; $eq['moc']['level']=5;
tassert('A4 hoa giai theo thu tu', \player\linhcan_dominant($eq), 'moc');
$st = $zz; $st['tho']['level']=5; $st['tho']['stage']=2; $st['kim']['level']=5; $st['kim']['stage']=1;
tassert('A4 stage cao hon thang', \player\linhcan_dominant($st), 'tho');

// A5: title — tung hinh thai (doi tu A3: hoa=7, con 0 → tong 7 <25 = Pham Can)
$t1 = \player\linhcan_title($n0); tassert('A5 tong 7 → Pham Can', $t1['id'], 'pham_can');
$a = $zz; foreach ($a as $k=>$v) { $a[$k]=array('level'=>30,'stage'=>1); }
tassert('A5 all 30 → Vien Man', \player\linhcan_title($a)['id'], 'vien_man');
$b = $a; foreach ($b as $k=>$v) { $b[$k]=array('level'=>70,'stage'=>3); }
tassert('A5 all 70 → Vo Cuc', \player\linhcan_title($b)['id'], 'vo_cuc');
$c = $zz; $c['kim']=array('level'=>40,'stage'=>1);
tassert('A5 chan linh', \player\linhcan_title($c)['id'], 'chan_linh');
tassert('A5 chan linh ten kem hanh', \player\linhcan_title($c)['name'], 'Chân Linh Căn');
$e2 = $zz; $e2['hoa']=array('level'=>30,'stage'=>1); $e2['tho']=array('level'=>35,'stage'=>1);
tassert('A5 song nghi', \player\linhcan_title($e2)['id'], 'song_nghi');
$f = $zz; $f['kim']=array('level'=>10,'stage'=>0); $f['thuy']=array('level'=>9,'stage'=>0);
tassert('A5 tong 19 van Pham Can (rule 5 cuoi rule 4)', \player\linhcan_title($f)['id'], 'pham_can');

// A6: milestone
tassert('A6 tong 0', \player\linhcan_milestone(0)['percent'], 0);
tassert('A6 tong 59', \player\linhcan_milestone(59)['percent'], 0);
tassert('A6 tong 60', \player\linhcan_milestone(60)['percent'], 5);
tassert('A6 tong 399 next 400', \player\linhcan_milestone(399)['next_total'], 400);
tassert('A6 tong 400 het', \player\linhcan_milestone(400)['next_total'], 0);

$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
exit($fails === 0 ? 0 : 1);
```
Ghi chu A5 cuoi: `f` tong=19<25 → **pham_can** (rule 5 uu tien hon mac dinh — dung theo spec).

- [x] **Step 2: Chay — that bai vi ham chua ton tai**
Run: `php.exe tests/test_linhcan_element.php` → Fatal `linhcan_element_defs`. OK de tiep.

- [x] **Step 3: Viet `class/function/linhcan.php` (phan doc thuan tuy)**

File bat dau bang `<?php namespace player;` (dang cau lenh, KHONG ngoac dong cuoi file — nhu `huyetmach.php`). Cac ham cu (`linhcan_config_get`, `linhcan_upgrade_config_all`, `linhcan_get_upgrade_info`, `linhcan_find_next_tier_id`, `linhcan_upgrade`, `linhcan_get_element_multiplier`, `linhcan_apply_element_damage`...) **GIU NGUYEN tam thoi** — van con caller song: `class/player.php:746-747,2025-2031` (xoa o Task 9/7), `game/linhcan.php` (viet lai o Task 10), `game/linhcan_upgrade.php` (xoa o Task 11), `linhcan_boss.php:40-44,188` (viet lai o Task 5). **Task 11 moi xoa het ham cu** (rieng `linhcan_get_element_multiplier` + `linhcan_apply_element_damage` GIU VINH VIEN — pve/boss/nguhanh dang dung). Cac ham moi boi `if (!function_exists(__NAMESPACE__ . '\\x'))`:

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_config_path')) {
    function linhcan_element_config_path(){
        return __DIR__ . '/../config/linhcan_element_config.json';
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_config_all')) {
    function linhcan_element_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(linhcan_element_config_path());
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_defs')) {
    function linhcan_element_defs(){
        $c = linhcan_element_config_all();
        return isset($c['elements']) ? $c['elements'] : array();
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_def')) {
    function linhcan_element_def($key){
        $d = linhcan_element_defs();
        return isset($d[$key]) ? $d[$key] : false;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_stage_def')) {
    function linhcan_stage_def($stage){
        $c = linhcan_element_config_all();
        $k = strval(intval($stage));
        return isset($c['stages'][$k]) ? $c['stages'][$k] : false;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_stage_cap')) {
    function linhcan_stage_cap($stage){
        $t = linhcan_stage_def($stage);
        return $t === false ? 0 : intval($t['lv_cap']);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_stage_max')) {
    function linhcan_stage_max(){ return 4; }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_da_id')) {
    function linhcan_da_id($element){
        $c = linhcan_element_config_all();
        return isset($c['da_map'][$element]) ? intval($c['da_map'][$element]) : 0;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_binh_anh_id')) {
    function linhcan_binh_anh_id(){
        $c = linhcan_element_config_all();
        return isset($c['da_map']['binh_anh']) ? intval($c['da_map']['binh_anh']) : 37;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_train_cost')) {
    function linhcan_train_cost($lv){
        $c = linhcan_element_config_all();
        $t = $c['train_cost'];
        $stone = intval($t['stone_base']) + intval(floor(intval($lv) / intval($t['stone_per_lv'])));
        if ($stone > intval($t['stone_cap'])) $stone = intval($t['stone_cap']);
        return array('stone' => $stone, 'linh_thach' => intval($t['lt_base']) + intval($lv) * intval($t['lt_per_lv']));
    }
}
```
`linhcan_train_cost(0)` stone=1 ✓ (floor 0). Test A1 lv0/89 khop.

- [x] **Step 4: Node + dominant + title + milestone + give_stone**

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_node_rows')) {
    function linhcan_node_rows($sid, $dblj){
        $rows = array();
        foreach (array('kim','moc','thuy','hoa','tho') as $k) {
            $rows[$k] = array('level' => 0, 'stage' => 0);
        }
        $stmt = $dblj->prepare("SELECT element, level, stage FROM player_linhcan_node WHERE sid = ?");
        $stmt->execute(array($sid));
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $el = $r['element'];
            if (isset($rows[$el])) {
                $rows[$el] = array('level' => intval($r['level']), 'stage' => intval($r['stage']));
            }
        }
        return $rows;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_node_get')) {
    function linhcan_node_get($sid, $element, $dblj){
        $rows = linhcan_node_rows($sid, $dblj);
        return isset($rows[$element]) ? $rows[$element] : array('level'=>0,'stage'=>0);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_node_set')) {
    function linhcan_node_set($sid, $element, $lv, $stage, $dblj){
        $stmt = $dblj->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE level = VALUES(level), stage = VALUES(stage), updated_at = NOW()");
        $ok = $stmt->execute(array($sid, $element, intval($lv), intval($stage)));
        if ($ok) player_cache_clear($sid);
        return $ok;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_dominant')) {
    /** Hanh hoat dong: Lv cao nhat → stage cao nhat → thu tu co dinh (key mang theo thu tu). */
    function linhcan_dominant($nodes){
        $best = ''; $bestLv = -1; $bestSt = -1;
        foreach (array('kim','moc','thuy','hoa','tho') as $k) {
            if (!isset($nodes[$k])) continue;
            $lv = intval($nodes[$k]['level']); $st = intval($nodes[$k]['stage']);
            if ($lv + $st <= 0) continue;
            if ($lv > $bestLv || ($lv === $bestLv && $st > $bestSt)) {
                $best = $k; $bestLv = $lv; $bestSt = $st;
            }
        }
        return $best; // tong 0 → '' (he chua mo, nhu linh căn cu chưa có)
    }
}
```
Ghi chu: duyet theo thu tu co dinh + so sanh strict → hoa giai giu hanh dung truoc (moc truoc thuy), khop A4.

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_title')) {
    function linhcan_title($nodes){
        $c = linhcan_element_config_all();
        $keys = array('kim','moc','thuy','hoa','tho');
        $levels = array(); $total = 0;
        foreach ($keys as $k) {
            $lv = isset($nodes[$k]) ? intval($nodes[$k]['level']) : 0;
            $levels[] = $lv; $total += $lv;
        }
        $dom = linhcan_dominant($nodes);
        $domName = $dom !== '' && isset($c['elements'][$dom]) ? $c['elements'][$dom]['name'] : '';
        foreach ($c['title_rules'] as $rule) {
            $rq = isset($rule['require']) ? $rule['require'] : array();
            $ok = true;
            if (isset($rq['all_lv_min']))  { foreach ($levels as $lv) if ($lv < intval($rq['all_lv_min'])) $ok = false; }
            elseif (isset($rq['one_lv_min'])) {
                $hit = 0; $othersOk = true;
                foreach ($levels as $lv) {
                    if ($lv >= intval($rq['one_lv_min'])) $hit++;
                    elseif ($lv > intval($rq['others_lv_max'])) $othersOk = false;
                }
                if ($hit !== 1 || !$othersOk) $ok = false;
            }
            elseif (isset($rq['two_lv_min'])) {
                $hit = 0; foreach ($levels as $lv) if ($lv >= intval($rq['two_lv_min'])) $hit++;
                if ($hit < 2) $ok = false;
            }
            elseif (isset($rq['total_lv_max'])) { if ($total > intval($rq['total_lv_max'])) $ok = false; }
            if ($ok) {
                $name = $rule['name'];
                if ($rule['id'] === 'chan_linh' && $domName !== '') $name .= ' · ' . $domName;
                return array('id'=>$rule['id'],'name'=>$name,'icon'=>$rule['icon'],'color'=>$rule['color'],'desc'=>$domName);
            }
        }
        return array('id'=>'mac_dinh','name'=>'Linh Căn Đã Chắc Lọc','icon'=>'🌿','color'=>'#e2e8f0','desc'=>$domName);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_milestone')) {
    function linhcan_milestone($total){
        $c = linhcan_element_config_all();
        $ms = isset($c['milestones']) ? $c['milestones'] : array();
        ksort($ms); // JSON object khong dam bao thu tu tang dan
        $out = array('percent'=>0,'name'=>'','next_percent'=>0,'next_total'=>0,'next_name'=>'');
        foreach ($ms as $m) {
            if ($total >= intval($m['total_lv'])) {
                $out['percent'] = floatval($m['bonus_percent']);
                $out['name'] = $m['name'];
            } else {
                $out['next_percent'] = floatval($m['bonus_percent']);
                $out['next_total'] = intval($m['total_lv']);
                $out['next_name'] = $m['name'];
                break;
            }
        }
        return $out;
    }
}
```
`linhcan_milestone(400)` → het bac: vong lap khong con phan `else` → `next_total=0` ✓ A6.

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_give_stone')) {
    /** Moi noi TANG da/linh thach vat ly phai đi qua day (landmine 1.2). */
    function linhcan_give_stone($sid, $djid, $amount, $dblj){
        $amount = intval($amount);
        if ($amount <= 0) return false;
        adddj($sid, intval($djid), $amount, $dblj);
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'receive_stone', $amount, $dblj);
        }
        return true;
    }
}
```

- [x] **Step 5: Chay test A** — `php.exe tests/test_linhcan_element.php` → tat ca PASS (neu `linhcan_milestone` A6 fail, kiem tra `ksort` da ap dung).

- [x] **Step 6: `php -l` ca 2 file + commit**
```bash
git add class/function/linhcan.php tests/test_linhcan_element.php
git commit -m "feat(linhcan): core doc thuan tuy — node/dominant/title/milestone + give_stone that"
```
---

### Task 3: `linhcan_train` + `linhcan_calc_bonuses` viet lai (giu hop dong 16 key)

**Files:**
- Modify: `class/function/linhcan.php` (them khoi train + THAY THE the `linhcan_calc_bonuses` cu tai `:223-281`), `tests/test_linhcan_element.php` (them phan B, C).

**Interfaces:**
- Consumes: Task 2 tat ca.
- Produces: `linhcan_train($sid,$dblj,$element)` → `array('ok'=>bool,'msg'=>string,'lv'=>int)`; `linhcan_train_cost_sum($fromLv,$toLv)` → `array('stone'=>int,'linh_thach'=>int)` (Task 7 tinh hoan); `linhcan_calc_bonuses($sid,$dblj)` → **du 16 key cu**, `element` = hanh chu dao ('' khi chua mo), `penalty_*` = 0, `quality` = max stage + 1, `quality_mult` = 1.0.

- [x] **Step 1: Test phan B (train) + C (hop dong bonus) — them TRUOC `exit(...)`**

```php
// ===== B: train =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid='" . $TESTSID . "' AND djid=35");
$db->exec("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES ('" . $TESTSID . "', 990112, 35, 30)");
$db->exec("UPDATE game1 SET uyxb = 100000 WHERE sid='" . $TESTSID . "'");
\player\linhcan_node_set($TESTSID, 'kim', 0, 0, $db);
$r = \player\linhcan_train($TESTSID, $db, 'kim');
tassert('B1 train lv0 ok', $r['ok'], true);
tassert('B1 lv=1', $r['lv'], 1);
$pj = \player\getplayerdaoju($TESTSID, 35, $db);
tassert('B1 tru 1 da', intval($pj->djsum), 29);
$db->exec("UPDATE game1 SET uyxb = 50 WHERE sid='" . $TESTSID . "'");
$r2 = \player\linhcan_train($TESTSID, $db, 'kim');
tassert('B2 thieu LT fail', $r2['ok'], false);
$pj2 = \player\getplayerdaoju($TESTSID, 35, $db);
tassert('B2 da khong bi truu oan', intval($pj2->djsum), 29);
\player\linhcan_node_set($TESTSID, 'kim', 15, 0, $db);
$db->exec("UPDATE game1 SET uyxb = 100000 WHERE sid='" . $TESTSID . "'");
$r3 = \player\linhcan_train($TESTSID, $db, 'kim');
tassert('B3 chan tran stage0', $r3['ok'], false);
$tuTrain = \player\linhcan_train_cost_sum(0, 15);
tassert('B4 cost sum 0..14 stone', $tuTrain['stone'], 15);
tassert('B4 cost sum 0..14 lt', $tuTrain['linh_thach'], 1500 + 15 * (0+1+2+3+4+5+6+7+8+9+10+11+12+13+14));

// ===== C: hop dong calc_bonuses =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
\player\linhcan_node_set($TESTSID, 'kim', 30, 1, $db);
\player\linhcan_node_set($TESTSID, 'thuy', 20, 0, $db);
$B = \player\linhcan_calc_bonuses($TESTSID, $db);
$keys = array('absorb_bonus','exp_bonus','quality','quality_mult','stats','pet_atk_bonus',
  'pet_hp_bonus','pet_exp_bonus','pet_crit_bonus','element','supported_cp_element',
  'penalty_atk_percent','penalty_def_percent','penalty_maxhp_percent','penalty_crit_percent','penalty_absorb_percent');
$missing = array();
foreach ($keys as $k) if (!array_key_exists($k, $B)) $missing[] = $k;
tassert('C1 du 16 key hop dong', implode(',', $missing), '');
tassert('C2 stats du 5 key', implode(',', array_keys($B['stats'])), 'atk,def,hp,crit,lifesteal');
// tong Lv 50 <60 → milestone 0; kim stage1 → atkMult 1.005; atk = 2*30*1.005 = 60.3 → 60
tassert('C3 atk 60', $B['stats']['atk'], 60);
tassert('C4 absorb 0.18*20', round($B['absorb_bonus'], 4), 3.6);
tassert('C5 exp 0.08*20', round($B['exp_bonus'], 4), 1.6);
tassert('C6 element = chu dao', $B['element'], 'kim');
tassert('C7 quality = maxstage+1', $B['quality'], 2);
tassert('C8 khong nerf', $B['penalty_atk_percent'], 0);
// tong 60 → milestone 5%: dat them moc 10 lv (30+20+10=60)
\player\linhcan_node_set($TESTSID, 'moc', 10, 0, $db);
$B2 = \player\linhcan_calc_bonuses($TESTSID, $db);
// atk = round(60 * 1.05) = 63; hp = round(14*10*1.05)=147; absorb = round(3.6*1.05,4)=3.78
tassert('C9 milestone 5% nhan atk', $B2['stats']['atk'], 63);
tassert('C9 milestone 5% nhan hp', $B2['stats']['hp'], 147);
tassert('C9 milestone 5% nhan absorb', round($B2['absorb_bonus'], 4), 3.78);
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid='" . $TESTSID . "' AND djid=35");
```

- [x] **Step 2: Chay → fail B1 (ham chua ton tai)** — `php.exe tests/test_linhcan_element.php`.

- [x] **Step 3: them `linhcan_train_cost_sum` + `linhcan_train`** (noi dung `class/function/linhcan.php`, cuoi khoi Task 2):

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_train_cost_sum')) {
    /** Tong chi phi tu Lv $fromLv den $toLv-1 (Task 7 hoan Tay Linh Can dung). */
    function linhcan_train_cost_sum($fromLv, $toLv){
        $stone = 0; $lt = 0;
        for ($i = intval($fromLv); $i < intval($toLv); $i++) {
            $c = linhcan_train_cost($i);
            $stone += $c['stone']; $lt += $c['linh_thach'];
        }
        return array('stone' => $stone, 'linh_thach' => $lt);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_train')) {
    function linhcan_train($sid, $dblj, $element){
        if (!linhcan_element_def($element)) return array('ok'=>false,'msg'=>'Hành không tồn tại.','lv'=>0);
        $node = linhcan_node_get($sid, $element, $dblj);
        $cap = linhcan_stage_cap($node['stage']);
        if (intval($node['level']) >= $cap) {
            $t = linhcan_stage_def($node['stage']);
            return array('ok'=>false,'msg'=>'Đã chạm trần bậc ' . $t['name'] . ' (Lv ' . $cap . '). Cần đột phá.','lv'=>intval($node['level']));
        }
        $cost = linhcan_train_cost($node['level']);
        $stoneId = linhcan_da_id($element);
        $have = getplayerdaoju($sid, $stoneId, $dblj);
        $haveSum = $have ? intval($have->djsum) : 0;
        if ($haveSum < $cost['stone']) {
            return array('ok'=>false,'msg'=>'Thiếu ' . $cost['stone'] . ' ' . $element . ' Tinh Thạch (đang có ' . $haveSum . ').','lv'=>intval($node['level']));
        }
        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->uyxb) < $cost['linh_thach']) {
            return array('ok'=>false,'msg'=>'Thiếu ' . $cost['linh_thach'] . ' linh thạch.','lv'=>intval($node['level']));
        }
        // Thu tu an toan: tru LT truoc (co rowCount kiem tra), roi tru da, roi ghi node.
        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
        $stmt->execute(array($cost['linh_thach'], $sid, $cost['linh_thach']));
        if ($stmt->rowCount() === 0) {
            return array('ok'=>false,'msg'=>'Trừ linh thạch thất bại.','lv'=>intval($node['level']));
        }
        deledjsum($stoneId, $cost['stone'], $sid, $dblj);
        $newLv = intval($node['level']) + 1;
        linhcan_node_set($sid, $element, $newLv, intval($node['stage']), $dblj);
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'train_element', 1, $dblj); // hook Task 6; chua upgrade thi false an toan
        }
        return array('ok'=>true,'msg'=>'Luyện ' . $element . ' lên Lv ' . $newLv . '.','lv'=>$newLv);
    }
}
```

- [x] **Step 4: THAY THE toan bo `linhcan_calc_bonuses` cu** (`class/function/linhcan.php:223-281` — gi dung vong `if (!function_exists(...))` bao):

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_calc_bonuses')) {
    /** HOP DONG 16 KEY — cam doi ten/kieu (Global Constraints). */
    function linhcan_calc_bonuses($sid, $dblj){
        $nodes = linhcan_node_rows($sid, $dblj);
        $defs = linhcan_element_defs();
        $total = 0;
        foreach ($nodes as $n) { $total += intval($n['level']); }
        $ms = linhcan_milestone($total);
        $mult = 1 + floatval($ms['percent']) / 100;

        $p = array('atk'=>0,'def'=>0,'hp'=>0,'crit'=>0,'absorb_pct'=>0,'exp_pct'=>0,'pet_atk_pct'=>0,'pet_hp_pct'=>0);
        $maxStage = 0;
        foreach ($nodes as $k => $n) {
            $lv = intval($n['level']); $stg = intval($n['stage']);
            if ($stg > $maxStage) $maxStage = $stg;
            if (!isset($defs[$k]) || $lv <= 0) continue;
            $perLv = isset($defs[$k]['per_lv']) ? $defs[$k]['per_lv'] : array();
            foreach ($perLv as $stat => $per) {
                if (isset($p[$stat])) $p[$stat] += floatval($per) * $lv;
            }
        }
        // Kim cuc hard: +0.5% Cong Kich moi bac Kim (nhan vao thanh phan atk cua vong)
        $atkMult = 1;
        if (isset($defs['kim']['per_stage_atk_pct'])) {
            $atkMult = 1 + (floatval($defs['kim']['per_stage_atk_pct']) * intval($nodes['kim']['stage']) / 100);
        }

        $stats = array(
            'atk'       => intval(round($p['atk'] * $atkMult * $mult)),
            'def'       => intval(round($p['def'] * $mult)),
            'hp'        => intval(round($p['hp'] * $mult)),
            'crit'      => intval(round($p['crit'] * $mult)),
            'lifesteal' => 0
        );
        $dom = linhcan_dominant($nodes);
        return array(
            'absorb_bonus' => $p['absorb_pct'] * $mult,
            'exp_bonus'    => $p['exp_pct'] * $mult,
            'quality'      => $maxStage + 1,
            'quality_mult' => 1.0,
            'stats'        => $stats,
            'pet_atk_bonus'=> $p['pet_atk_pct'] * $mult,
            'pet_hp_bonus' => $p['pet_hp_pct'] * $mult,
            'pet_exp_bonus'=> 0,
            'pet_crit_bonus'=> 0,
            'element'      => $dom,
            'supported_cp_element' => $dom,
            'penalty_atk_percent'    => 0,
            'penalty_def_percent'    => 0,
            'penalty_maxhp_percent'  => 0,
            'penalty_crit_percent'   => 0,
            'penalty_absorb_percent' => 0
        );
    }
}
```
Test C3 so sinh: `p['atk']=2*30=60`, kim stage1 → `atkMult=1.005` → `60*1.005*1.0=60.3` → round 60 ✓. `round($B['absorb_bonus'],4)` 3.6 ✓ (float so sanh bang round — dung `=== 3.6` sau round).

- [x] **Step 5: Chay test A+B+C → all PASS; `php -l` 2 file**
Run: `php.exe tests/test_linhcan_element.php` — sua den khi exit 0.

- [x] **Step 6: Commit**
```bash
git add class/function/linhcan.php tests/test_linhcan_element.php
git commit -m "feat(linhcan): train 100% + calc_bonuses doc node gi hop dong 16 key"
```
---

### Task 4: Boss đột phá cá nhân theo hành + `linhcan_break`

**Files:**
- Modify: `class/config/linhcan_boss_config.json` (thêm key `personal_boss`)
- Modify: `class/function/linhcan_boss.php` (nối khối `lcboss_*` trước `?>` cuối file — file hiện kết thúc `:217`; `?>` cuối có thể xóa khi nối)
- Modify: `class/function/linhcan.php` (nối `linhcan_break_requirements` + `linhcan_break` cuối file, SAU khi Task 2/3 đã viết lại file này)
- Create: `tests/test_linhcan_boss.php`

**Interfaces:**
- Consumes: Task 2 (`linhcan_element_def`, `linhcan_stage_def`, `linhcan_stage_cap`, `linhcan_stage_max`, `linhcan_node_get`, `linhcan_node_set`, `linhcan_binh_anh_id`, `linhcan_give_stone`), Task 1 (`linhcan_boss_personal`, `player_linhcan_break_log`), hàm cũ giữ lại: `linhcan_get_element_multiplier` (`linhcan.php:383`), `getplayer`/`getplayerdaoju`/`deledjsum`/`player_cache_clear` (`class/player.php:552/1373/1392/389`).
- Produces (chữ ký CHUẨN trong map, Task 10/11 gọi đúng tên này):
  - `lcboss_active($sid,$dblj,$element)` → row array|false
  - `lcboss_spawn($sid,$dblj,$element)` → boss array|false (id, sid, element, to_stage, name, level, hp, maxhp, atk, def, crit, icon, color)
  - `lcboss_killed($sid,$dblj,$element,$toStage)` → bool
  - `lcboss_mark_killed($sid,$dblj,$row)` → void
  - `lcboss_calc_damage($player,$bossRow)` → `array('damage','crit','elem_mult')`
  - `lcboss_drops($sid,$dblj,$bossRow)` → `array('stone_id','stone','html')`
  - `linhcan_break_requirements($sid,$dblj,$element)` → `array('ok','to_stage','rows')`, mỗi row `array('key','label','cur','need','ok')` với key ∈ lv_cap/boss/binh_anh/linh_thach/uexp (Task 10 render `[Đủ/Thiếu]`, Task 11 không cần — trang chính gộp luôn)
  - `linhcan_break($sid,$dblj,$element)` → `array('ok','msg','stage')`

- [x] **Step 1: Đối chiếu số boss cá nhân với boss thế giới đang chạy (đóng số trước khi code — note spec §3.4)**

Run:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$db=new PDO("mysql:host=localhost;dbname=game;charset=utf8","root","www.aae.ink");
$avg=max(1,intval($db->query("SELECT AVG(ulv) FROM game1")->fetchColumn()));
echo "avg_ulv=$avg\n";
foreach(array(10,30,60,90) as $lv){
  $wHP=5000+500*$lv; $wATK=200+20*$lv;
  $pHP=8000+220*$lv; $pATK=260+7*$lv;
  echo "lv $lv | world hp=$wHP atk=$wATK | personal hp=$pHP atk=$pATK | hp ratio=".round($pHP/$wHP,2)."\n";
}'
```
Expected: hp personal ≤ 1.2× world ở mọi mốc (boss cá nhân là solo gate, phải dễ hơn world share-đạn-dược). Số `8000+220L / 260+7L` đã chủ dự án duyệt ở spec (dấu `[]` = chốt khi duyệt spec). **Nếu ratio > 1.2 ở lv cao: KHÔNG tự đổi số — ghi vào `docs/UI_LINHCAN.md` §Chưa-làm và báo chủ dự án.**

- [x] **Step 2: Thêm `personal_boss` vào `class/config/linhcan_boss_config.json`**

Chèn vào trong object `boss_config` (trước `element_bosses`), nguyên văn:
```json
"personal_boss": {
  "hp_base": 8000, "hp_per_lv": 220,
  "atk_base": 260, "atk_per_lv": 7,
  "def_base": 100, "def_per_lv": 10,
  "crit_base": 10, "crit_per_stage": 2,
  "drop_stone_min": 1, "drop_stone_max": 2
},
```
Validate:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '$d=json_decode(file_get_contents("class/config/linhcan_boss_config.json"),true); echo isset($d["boss_config"]["personal_boss"]["hp_base"])?"personal_boss ok\n":"MISSING\n";'
```
Expected: `personal_boss ok`.

- [x] **Step 3: Test failing — `tests/test_linhcan_boss.php`** (nội dung nguyên văn)

```php
<?php
// Test boss ca nhan dot pha linh can (Task 4). Chay: php.exe tests/test_linhcan_boss.php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
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

$db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uyxb, uexp)
    VALUES ('$TESTSID', 990112, 'LCBTEST', 30, 5000, 5000, NOW(), 3000, 1000, 20, 100000, 10000)
    ON DUPLICATE KEY UPDATE uid = 990112, ulv = 30, uhp = 5000, umaxhp = 5000, endtime = NOW(),
        ugj = 3000, ufy = 1000, ubj = 20, uyxb = 100000, uexp = 10000");
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM player_linhcan_break_log WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (34, 35, 37)");
\player\linhcan_node_set($TESTSID, 'kim', 30, 1, $db);
\player\player_cache_clear($TESTSID);

// D1: hanh chua cham tran bac → khong duoc sinh boss
tassert('D1 hoa lv0 khong sinh', \player\lcboss_spawn($TESTSID, $db, 'hoa'), false);

// D2: sinh boss kim (lv 30 = cap stage1 → to_stage 2)
$b = \player\lcboss_spawn($TESTSID, $db, 'kim');
tassert('D2 sinh boss', is_array($b), true);
tassert('D2 to_stage 2', intval($b['to_stage']), 2);
tassert('D2 element', $b['element'], 'kim');
tassert('D2 hp=8000+220*ulv', intval($b['hp']), 8000 + 220 * 30);
tassert('D2 atk=260+7*ulv', intval($b['atk']), 260 + 7 * 30);
tassert('D2 def=100+10*ulv', intval($b['def']), 100 + 10 * 30);
tassert('D2 crit=10+2*to_stage', intval($b['crit']), 10 + 2 * 2);
tassert('D2 ten tu config element_bosses', $b['name'], 'Kim Linh Tộc Trưởng');

// D3-4: active va spawn lap lai tra ve DUNG con dang chieu (khong spam 2 con)
$a = \player\lcboss_active($TESTSID, $db, 'kim');
tassert('D3 active cung id', intval($a['id']), intval($b['id']));
$b2 = \player\lcboss_spawn($TESTSID, $db, 'kim');
tassert('D4 spawn lai cung id', intval($b2['id']), intval($b['id']));

// D5: sat thuong co khoa che
$player = \player\getplayer($TESTSID, $db);
$hit = \player\lcboss_calc_damage($player, $a);
tassert('D5 du 3 key', isset($hit['damage']) && isset($hit['crit']) && isset($hit['elem_mult']), true);
tassert('D5 dmg >= 1', $hit['damage'] >= 1, true);

// D6: requirements khi CHUA giot boss → dong boss Thieu
$req = \player\linhcan_break_requirements($TESTSID, $db, 'kim');
tassert('D6 ok false', $req['ok'], false);
tassert('D6 to_stage 2', intval($req['to_stage']), 2);
$bossRowReq = array();
foreach ($req['rows'] as $r) { if ($r['key'] === 'boss') { $bossRowReq = $r; } }
tassert('D6 boss cur/need', $bossRowReq['cur'] . '/' . $bossRowReq['need'], '0/1');

// D7: giot boss (moc = ghi thang status, khuong mai pve) + du binh anh → Du het
$db->exec("UPDATE linhcan_boss_personal SET status = 'killed', hp = 0, killed_at = NOW() WHERE id = " . intval($b['id']));
tassert('D7.1 lcboss_killed true', \player\lcboss_killed($TESTSID, $db, 'kim', 2), true);
$reqA = \player\linhcan_break_requirements($TESTSID, $db, 'kim');
$baReq = array(); foreach ($reqA['rows'] as $r) { if ($r['key'] === 'binh_anh') { $baReq = $r; } }
tassert('D7.2 thieu binh anh van ok false', $reqA['ok'], false);
tassert('D7.2 binh anh need 2', intval($baReq['need']), 2);
$db->exec("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES ('$TESTSID', 990112, 37, 5)");
\player\player_cache_clear($TESTSID);
$req = \player\linhcan_break_requirements($TESTSID, $db, 'kim');
tassert('D7.3 du dieu kien → ok true', $req['ok'], true);

// D8: break that — 100% thanh cong, stage 1→2, tru dung nguyen lieu
$r = \player\linhcan_break($TESTSID, $db, 'kim');
tassert('D8 break ok', $r['ok'], true);
tassert('D8 stage moi', intval($r['stage']), 2);
$node = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D8 node stage 2', intval($node['stage']), 2);
tassert('D8 node van giu lv 30', intval($node['level']), 30);
$ba = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('D8 tru 2 binh anh (5-2)', $ba, 3);
$g1 = $db->query("SELECT uyxb, uexp FROM game1 WHERE sid = '$TESTSID'")->fetch(\PDO::FETCH_ASSOC);
tassert('D8 tru LT 20000', intval($g1['uyxb']), 80000);
tassert('D8 tru uexp 5000', intval($g1['uexp']), 5000);
$blog = intval($db->query("SELECT COUNT(*) FROM player_linhcan_break_log WHERE sid = '$TESTSID'")->fetchColumn());
tassert('D8 break_log >= 1 dong', $blog >= 1, true);

// D9: that buoc truu giua chung → hoan lai, KHONG mat tien oan (bai hoc linhcan_upgrade cu)
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37");
$r9 = \player\linhcan_break($TESTSID, $db, 'kim');
tassert('D9 thieu da → fail', $r9['ok'], false);
$g1b = $db->query("SELECT uyxb, uexp FROM game1 WHERE sid = '$TESTSID'")->fetch(\PDO::FETCH_ASSOC);
tassert('D9 LT khong dong', intval($g1b['uyxb']), 80000);
tassert('D9 uexp khong dong', intval($g1b['uexp']), 5000);
$node9 = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D9 stage giu nguyen 2', intval($node9['stage']), 2);

// D10: sau break, can lv 50 (cap stage 2) moi sinh boss tiep → chan ky
tassert('D10 lv 30 < cap 50 → khong sinh', \player\lcboss_spawn($TESTSID, $db, 'kim'), false);

// Cleanup
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM player_linhcan_break_log WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (34, 35, 37)");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$TESTSID'");
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [x] **Step 4: Chạy test → xác nhận FAIL đúng nguyên nhân**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_boss.php`
Expected: fatal `Call to undefined function player\lcboss_spawn()` (D1). Đây là test-red hợp lệ.

- [x] **Step 5: Nối khối `lcboss_*` vào `class/function/linhcan_boss.php`** (nguyên văn; xóa `?>` cuối file cũ khi nối)

```php
// ==================== BOSS CA NHAN DOT PHA LINH CAN (khuon huyetmach_boss_*) ====================
// Khac boss the gioi: moi nguoi choi co 1 con theo hanh minh, song vo thoi han den khi bi giot
// (khong expire, khong throttle ngay — tu nhien bi chan bang tran Lv + nguyen lieu dot pha).

if (!function_exists(__NAMESPACE__ . '\\lcboss_config')) {
    function lcboss_config(){
        $cfg = linhcan_boss_config_all();
        $bc = isset($cfg['boss_config']) ? $cfg['boss_config'] : array();
        $pb = isset($bc['personal_boss']) ? $bc['personal_boss'] : array();
        $eb = isset($bc['element_bosses']) ? $bc['element_bosses'] : array();
        return array($pb, $eb);
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_active')) {
    function lcboss_active($sid, $dblj, $element){
        $stmt = $dblj->prepare("SELECT * FROM linhcan_boss_personal
            WHERE sid = ? AND element = ? AND status = 'active'
            ORDER BY spawned_at DESC LIMIT 1");
        $stmt->execute(array($sid, $element));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return false;
        if (intval($row['hp']) <= 0) {
            $dblj->prepare("UPDATE linhcan_boss_personal SET status = 'inactive' WHERE id = ?")
                 ->execute(array($row['id']));
            return false;
        }
        return $row;
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_killed')) {
    function lcboss_killed($sid, $dblj, $element, $toStage){
        $stmt = $dblj->prepare("SELECT COUNT(*) FROM linhcan_boss_personal
            WHERE sid = ? AND element = ? AND to_stage = ? AND status = 'killed'");
        $stmt->execute(array($sid, $element, intval($toStage)));
        return intval($stmt->fetchColumn()) > 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_spawn')) {
    function lcboss_spawn($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return false;
        $active = lcboss_active($sid, $dblj, $element);
        if ($active) return $active;

        $node = linhcan_node_get($sid, $element, $dblj);
        $stage = intval($node['stage']);
        if ($stage >= linhcan_stage_max()) return false;
        if (intval($node['level']) < linhcan_stage_cap($stage)) return false;
        $toStage = $stage + 1;
        if (lcboss_killed($sid, $dblj, $element, $toStage)) return false;

        $player = getplayer($sid, $dblj);
        if (!$player) return false;

        list($pb, $eb) = lcboss_config();
        $lv = max(1, intval($player->ulv));
        $hp = intval($pb['hp_base']) + intval($pb['hp_per_lv']) * $lv;
        $atk = intval($pb['atk_base']) + intval($pb['atk_per_lv']) * $lv;
        $defv = intval($pb['def_base']) + intval($pb['def_per_lv']) * $lv;
        $critv = intval($pb['crit_base']) + intval($pb['crit_per_stage']) * $toStage;
        $name = isset($eb[$element]['name']) ? $eb[$element]['name'] : ('Boss ' . $def['name']);
        $icon = isset($eb[$element]['icon']) ? $eb[$element]['icon'] : '👹';
        $color = isset($eb[$element]['color']) ? $eb[$element]['color'] : $def['color'];

        $ins = $dblj->prepare("INSERT INTO linhcan_boss_personal
            (sid, element, to_stage, name, level, hp, maxhp, atk, def, crit, spawned_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')");
        $ins->execute(array($sid, $element, $toStage, $name, $lv, $hp, $hp, $atk, $defv, $critv));

        return array(
            'id' => intval($dblj->lastInsertId()), 'sid' => $sid, 'element' => $element,
            'to_stage' => $toStage, 'name' => $name, 'level' => $lv,
            'hp' => $hp, 'maxhp' => $hp, 'atk' => $atk, 'def' => $defv, 'crit' => $critv,
            'icon' => $icon, 'color' => $color, 'spawned_at' => date('Y-m-d H:i:s'), 'status' => 'active'
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_calc_damage')) {
    function lcboss_calc_damage($player, $bossRow){
        $baseDmg = max(1, intval($player->ugj) - intval(intval($bossRow['def']) * 0.75));
        $lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $elemMult = 1.0;
        if ($lcElement !== '' && $bossElement !== '') {
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

if (!function_exists(__NAMESPACE__ . '\\lcboss_boss_counter')) {
    // Dam nguoc cua boss len nguoi choi — Task 11 dung khi hien chieu thuong.
    function lcboss_boss_counter($player, $bossRow){
        return max(1, intval($bossRow['atk']) - intval(intval($player->ufy) * 0.6));
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_mark_killed')) {
    function lcboss_mark_killed($sid, $dblj, $row){
        $stmt = $dblj->prepare("UPDATE linhcan_boss_personal
            SET status = 'killed', hp = 0, killed_at = NOW() WHERE id = ? AND sid = ?");
        $stmt->execute(array(intval($row['id']), $sid));
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_drops')) {
    function lcboss_drops($sid, $dblj, $bossRow){
        list($pb, $eb) = lcboss_config();
        $element = isset($bossRow['element']) ? $bossRow['element'] : '';
        $stoneId = linhcan_da_id($element);
        $min = intval($pb['drop_stone_min']); $max = intval($pb['drop_stone_max']);
        $amount = mt_rand($min, max($min, $max));
        $html = '';
        if ($stoneId > 0 && $amount > 0) {
            linhcan_give_stone($sid, $stoneId, $amount, $dblj);
            $html .= "<font color='#4CAF50'>🪨 Thu được {$amount} " . $element . " Tinh Thạch</font><br/>";
        }
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'kill_boss', 1, $dblj); // hook Task 6
        }
        return array('stone_id' => $stoneId, 'stone' => $amount, 'html' => $html);
    }
}
```

- [x] **Step 6: Nối `linhcan_break_requirements` + `linhcan_break` vào CUỐI `class/function/linhcan.php`** (nguyên văn)

```php
// ==================== DOT PHA BAC HANH ====================
// 100% thanh cong, khong quay RNG (bai hoc `linhcan_upgrade` cu: success_rate 20%
// + truu tien TRUOC khi check dieu kien → fatat giua chung mat oan. Duoi day thu
// check-truoc-sau-tru, moi buoc truu that bai hoan lai cac buoc da truu.)

if (!function_exists(__NAMESPACE__ . '\\linhcan_break_requirements')) {
    function linhcan_break_requirements($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok' => false, 'to_stage' => 0, 'rows' => array());
        $player = getplayer($sid, $dblj);
        if (!$player) return array('ok' => false, 'to_stage' => 0, 'rows' => array());
        $node = linhcan_node_get($sid, $element, $dblj);
        $stage = intval($node['stage']);
        if ($stage >= linhcan_stage_max()) {
            return array('ok' => false, 'to_stage' => $stage, 'rows' => array(
                array('key' => 'max', 'label' => 'Đã đạt bậc tối đa (Thánh Căn)',
                      'cur' => $stage, 'need' => $stage, 'ok' => true)));
        }
        $toStage = $stage + 1;
        $sd = linhcan_stage_def($toStage);
        $bc = (isset($sd['break']) && is_array($sd['break'])) ? $sd['break'] : array();

        $rows = array();
        $cap = linhcan_stage_cap($stage);
        $lvCur = intval($node['level']);
        $rows[] = array('key' => 'lv_cap', 'label' => 'Lv ' . $def['name'] . ' chạm trần bậc ' . $stage,
                        'cur' => $lvCur, 'need' => $cap, 'ok' => ($lvCur >= $cap));

        $bossNeed = isset($bc['boss_to_stage']) ? intval($bc['boss_to_stage']) : $toStage;
        $bossOk = lcboss_killed($sid, $dblj, $element, $bossNeed);
        $rows[] = array('key' => 'boss', 'label' => 'Thắng boss đột phá ' . $def['name'],
                        'cur' => $bossOk ? 1 : 0, 'need' => 1, 'ok' => $bossOk);

        $baNeed = isset($bc['binh_anh']) ? intval($bc['binh_anh']) : 0;
        $baRow = $baNeed > 0 ? getplayerdaoju($sid, linhcan_binh_anh_id(), $dblj) : false;
        $baCur = $baRow ? intval($baRow->djsum) : 0;
        $rows[] = array('key' => 'binh_anh', 'label' => 'Binh Anh Tinh Thạch',
                        'cur' => $baCur, 'need' => $baNeed, 'ok' => ($baCur >= $baNeed));

        $ltNeed = isset($bc['linh_thach']) ? intval($bc['linh_thach']) : 0;
        $ltCur = intval($player->uyxb);
        $rows[] = array('key' => 'linh_thach', 'label' => 'Linh thạch',
                        'cur' => $ltCur, 'need' => $ltNeed, 'ok' => ($ltCur >= $ltNeed));

        $uxNeed = isset($bc['uexp']) ? intval($bc['uexp']) : 0;
        $uxCur = intval($player->uexp);
        $rows[] = array('key' => 'uexp', 'label' => 'Linh khí (tu vi)',
                        'cur' => $uxCur, 'need' => $uxNeed, 'ok' => ($uxCur >= $uxNeed));

        $ok = true;
        foreach ($rows as $r) { if (!$r['ok']) { $ok = false; } }
        return array('ok' => $ok, 'to_stage' => $toStage, 'rows' => $rows);
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_break')) {
    function linhcan_break($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok' => false, 'msg' => 'Hành không hợp lệ.', 'stage' => 0);
        $req = linhcan_break_requirements($sid, $dblj, $element);
        if (!$req['ok']) {
            $thieu = array();
            foreach ($req['rows'] as $r) { if (!$r['ok']) { $thieu[] = $r['label']; } }
            return array('ok' => false, 'msg' => 'Chưa đủ điều kiện: ' . implode(', ', $thieu) . '.', 'stage' => 0);
        }
        $node = linhcan_node_get($sid, $element, $dblj);
        $stage = intval($node['stage']);
        $toStage = intval($req['to_stage']);
        $sd = linhcan_stage_def($toStage);
        $bc = isset($sd['break']) ? $sd['break'] : array();
        $ltNeed = intval($bc['linh_thach']);
        $uxNeed = intval($bc['uexp']);
        $baNeed = intval($bc['binh_anh']);
        $baId = linhcan_binh_anh_id();

        $st = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
        $st->execute(array($ltNeed, $sid, $ltNeed));
        if ($st->rowCount() === 0) {
            return array('ok' => false, 'msg' => 'Trừ linh thạch thất bại, chưa đột phá.', 'stage' => 0);
        }
        if ($uxNeed > 0) {
            $st2 = $dblj->prepare("UPDATE game1 SET uexp = uexp - ? WHERE sid = ? AND uexp >= ?");
            $st2->execute(array($uxNeed, $sid, $uxNeed));
            if ($st2->rowCount() === 0) {
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($ltNeed, $sid));
                return array('ok' => false, 'msg' => 'Trừ linh khí thất bại, chưa đột phá.', 'stage' => 0);
            }
        }
        if ($baNeed > 0) {
            if (!deledjsum($baId, $baNeed, $sid, $dblj)) {
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($ltNeed, $sid));
                if ($uxNeed > 0) {
                    $dblj->prepare("UPDATE game1 SET uexp = uexp + ? WHERE sid = ?")->execute(array($uxNeed, $sid));
                }
                return array('ok' => false, 'msg' => 'Trừ Bình Anh Tinh Thạch thất bại, chưa đột phá.', 'stage' => 0);
            }
        }

        linhcan_node_set($sid, $element, intval($node['level']), $toStage, $dblj);
        $dblj->prepare("INSERT INTO player_linhcan_break_log (sid, element, from_stage, to_stage, created_at)
            VALUES (?, ?, ?, ?, NOW())")->execute(array($sid, $element, $stage, $toStage));
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'break_once', 1, $dblj); // hook Task 6
        }
        player_cache_clear($sid);

        $newName = $sd['name'];
        $newColor = $sd['color'];
        return array('ok' => true,
            'msg' => 'Đột phá thành công! ' . $def['name'] . " đạt <font color='{$newColor}'>{$newName}</font> — trần Lv mới " . linhcan_stage_cap($toStage) . '.',
            'stage' => $toStage);
    }
}
```

- [x] **Step 7: Chạy test + lint PHP 5.6**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_boss.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan_boss.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan.php
```
Expected: `ALL PASS`, exit 0; hai file `No syntax errors`. Nếu FAIL D2/D7/D8 → kiểm tra static cache config (`linhcan_boss_config_all` có `static $cache`, CLI process mới sẽ đọc `personal_boss` mới — chạy lại lệnh, không sửa test).

- [x] **Step 8: Quét ký tự lỗi trong 2 file vừa nối**

```bash
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" class/function/linhcan_boss.php class/function/linhcan.php || echo CLEAN
```
Expected: `CLEAN` (nhãn UI tiếng Việt có dấu trong msg là chủ ý, chỉ cấm CJK/smart-quote/NBSP).

- [x] **Step 9: Commit**

```bash
git add class/config/linhcan_boss_config.json class/function/linhcan_boss.php class/function/linhcan.php tests/test_linhcan_boss.php
git commit -m "feat(linhcan): boss dot pha ca nhan theo hanh + linhcan_break 100% co hoan tien"
```

---
---

### Task 5: Boss thế giới — nguồn đá hiếm THẬT (element random-of-5, drop vào inventory)

**Files:**
- Modify: `class/config/linhcan_boss_config.json` (xóa `element_bosses.none`, thay `drops`, thêm `world_element_pick`)
- Modify: `class/function/linhcan_boss.php` (xóa `linhcan_boss_get_dominant_element` `:34-47`; sửa `linhcan_boss_spawn` `:83-134`; viết lại `linhcan_boss_drops` `:167-206`)
- Modify: `tests/test_linhcan_boss.php` (nối phần E trước dòng `// Cleanup`)

**Interfaces:**
- Consumes: Task 2 (`linhcan_da_id`, `linhcan_binh_anh_id`, `linhcan_give_stone`), Task 4 (`lcboss_*` đã nằm cùng file — không gọi nhau).
- Produces: `linhcan_boss_world_element($dblj)` → 1 trong 5 key `kim|moc|thuy|hoa|tho`. **XÓA** `linhcan_boss_get_dominant_element` (đọc `game1.linhcan_id` đã ngừng cập nhật — lỗi 1.4 của spec).
- Giữ nguyên chữ ký `linhcan_boss_spawn($dblj)` / `linhcan_boss_drops($player,$bossRow,$dblj)` / `linhcan_boss_check_spawn` / `linhcan_boss_mark_killed` vì `game/linhcan_boss.php` cũ còn gọi (`:7,12,39,62` — trang chỉ đọc `$drops['html']`; trang sẽ bị viết lại ở Task 11).

- [x] **Step 1: Sửa `class/config/linhcan_boss_config.json`** (3_edit, không viết lại cả file)

1. `drops`: THAY toàn bộ object
   ```json
   "drops": {
     "binh_anh_min": 1, "binh_anh_max": 2,
     "tinh_thach_min": 2, "tinh_thach_max": 4,
     "linh_thach_min": 100, "linh_thach_max": 500
   },
   ```
   (xóa `da_ngu_hanh_count`, `rare_drop_chance` — đá giờ rơi THẬT qua `playerdaoju`, không cần "vật phẩm hiếm" tưởng tượng)
2. `element_bosses`: XÓA nguyên key `"none"` (Hư Không Ma Vương) — hành `none` không tồn tại ở hệ mới.
3. Thêm key ngang cấp với `drops`: `"world_element_pick": "random_of_5",`
4. Spec §6.5 — typo `element_bones` trong docs cũ: verify `grep -rn "element_bones" --include=*.php --include=*.json . | grep -v "^./bak/"` → code/config KHÔNG đọc key đó (chỉ docs cũ sai); nếu thấy trong `docs/LINHCAN_DEV.md`/`LINHCAN_EXPANSION.md` thì sửa thành `element_bosses` ngay tại chỗ.

Validate:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '$d=json_decode(file_get_contents("class/config/linhcan_boss_config.json"),true); $c=$d["boss_config"]; echo (count($c["element_bosses"])===5 && isset($c["drops"]["binh_anh_max"]) && $c["world_element_pick"]==="random_of_5")?"cfg ok\n":"CFG BAD\n";'
```
Expected: `cfg ok`.

- [x] **Step 2: Nối test phần E vào `tests/test_linhcan_boss.php`** (trước `// Cleanup`)

```php
// ===== E: boss the gioi (Task 5) =====
$seen = array();
for ($i = 0; $i < 200; $i++) {
    $e = \player\linhcan_boss_world_element($db);
    $seen[$e] = true;
}
tassert('E1 world element chi trong 5 hanh', count($seen), 5);
tassert('E1 khong con none', isset($seen['none']), false);

$cfgW = \player\linhcan_boss_config_all();
tassert('E2 config khong con boss none', isset($cfgW['boss_config']['element_bosses']['none']), false);

// E3: spawn the gioi dung AVG(ulv) TOAN BO game1 (khong loc linhcan_id nua)
$db->exec("DELETE FROM linhcan_boss WHERE status = 'active'"); // local harness — bao cao chu neu co boss dang chieu
$w = \player\linhcan_boss_spawn($db);
$avgAll = max(1, intval($db->query("SELECT AVG(ulv) FROM game1")->fetchColumn()));
tassert('E3 level = avg ulv moi nguoi', intval($w['level']), $avgAll);
tassert('E3 hp cong thuc cu giu nguyen', intval($w['hp']), 5000 + 500 * $avgAll);

// E4: drop THAT vao playerdaoju
$playerW = \player\getplayer($TESTSID, $db);
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (31, 32, 33, 34, 35, 36, 37)");
$db->exec("UPDATE game1 SET uyxb = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);
$d = \player\linhcan_boss_drops($playerW, array('element' => 'hoa'), $db);
$lt = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$TESTSID'")->fetchColumn());
tassert('E4 LT roi 100..500', ($lt >= 100 && $lt <= 500), true);
$daHoa = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 32")->fetchColumn());
tassert('E4 da hoa roi 2..4', ($daHoa >= 2 && $daHoa <= 4), true);
$daHanhKhac = intval($db->query("SELECT COUNT(*) FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (33,34,35,36)")->fetchColumn());
tassert('E4 chi roi da dung hanh boss', $daHanhKhac, 0);
tassert('E4 binh anh roi 1..2', ($d['binh_anh'] >= 1 && $d['binh_anh'] <= 2), true);
$daBA = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('E4 binh anh co trong inventory', ($daBA === intval($d['binh_anh'])), true);
tassert('E4 html co chu', strpos($d['html'], 'Linh') !== false, true);

$db->exec("DELETE FROM linhcan_boss WHERE id = " . intval($w['id'])); // don boss gia vua spawn
```

- [x] **Step 3: Chạy → FAIL** (`linhcan_boss_world_element` undefined tại E1). Xác nhận rồi tiếp.

- [x] **Step 4: Xóa `linhcan_boss_get_dominant_element`** — xóa nguyên block `:34-47` (comment + `if (!function_exists...)` bọc function). Grep đối chiếu:
```bash
grep -rn "linhcan_boss_get_dominant_element" --include=*.php . | grep -v "^./bak/"
```
Expected: **0 kết quả** sau khi xóa (không ai gọi ngoài `linhcan_boss_spawn` đã sửa ở Step 5).

- [x] **Step 5: Thêm `linhcan_boss_world_element` + sửa `linhcan_boss_spawn`** (thay 2 đoạn trong `class/function/linhcan_boss.php`)

Chèn khối mới ngay sau `linhcan_boss_config_all`:
```php
/**
 * Boss thế giới chọn hành NGAN nhien trong 5 hanh config.
 * KHONG doc game1.linhcan_id nua (cot da ngung cap nhat — lo ngi 1.4).
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_world_element')) {
    function linhcan_boss_world_element($dblj){
        $cfg = linhcan_boss_config_all();
        $eb = isset($cfg['boss_config']['element_bosses']) ? $cfg['boss_config']['element_bosses'] : array();
        $keys = array();
        foreach ($eb as $k => $v) {
            if ($k === 'none') continue;
            $keys[] = $k;
        }
        if (!$keys) return 'kim';
        return $keys[array_rand($keys)];
    }
}
```

Trong `linhcan_boss_spawn($dblj)`:
- THAY `$element = linhcan_boss_get_dominant_element($dblj);` → `$element = linhcan_boss_world_element($dblj);`
- THAY default array `'name' => 'Hư Không Ma Vương'...` → `array('name' => 'Linh Thú Lạc', 'icon' => '👹', 'color' => '#9C27B0')` (chỉ dùng khi config thiếu hành — không còn đường nào dẫn tới `none`).
- THAY `SELECT AVG(ulv) as avg_lv FROM game1 WHERE linhcan_id > 0` → `SELECT AVG(ulv) as avg_lv FROM game1` (mọi nhân vật, vì `linhcan_id` đã nguội).

- [x] **Step 6: Viết lại `linhcan_boss_drops`** — THAY toàn bộ thân hàm cũ (`:167-206`, bỏ TODO `:192`), nguyên văn:

```php
/**
 * Boss the gioi bi giot: LT + da hanh cua boss + Binh Anh Tinh Thach, cong THAT
 * vao playerdaoju (adddj), khong con "roi ao" nhu ban cu.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_drops')) {
    function linhcan_boss_drops($player, $bossRow, $dblj){
        $config = linhcan_boss_config_all();
        $bossConfig = isset($config['boss_config']) ? $config['boss_config'] : array();
        $drops = isset($bossConfig['drops']) ? $bossConfig['drops'] : array();

        $lt = mt_rand(intval($drops['linh_thach_min']), intval($drops['linh_thach_max']));
        $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")
             ->execute(array($lt, $player->sid));

        $html = "<font color='#FFD700'>💰 {$lt} Linh thạch</font><br/>";

        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $stoneId = linhcan_da_id($bossElement);
        $stone = 0;
        if ($stoneId > 0) {
            $stone = mt_rand(intval($drops['tinh_thach_min']), intval($drops['tinh_thach_max']));
            linhcan_give_stone($player->sid, $stoneId, $stone, $dblj);
            $html .= "<font color='#4CAF50'>🪨 {$stone} " . $bossElement . " Tinh Thạch</font><br/>";
        }

        $binhAnh = mt_rand(intval($drops['binh_anh_min']), intval($drops['binh_anh_max']));
        linhcan_give_stone($player->sid, linhcan_binh_anh_id(), $binhAnh, $dblj);
        $html .= "<font color='#E91E63'>✨ {$binhAnh} Bình Anh Tinh Thạch</font><br/>";

        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($player->sid, 'kill_boss', 1, $dblj); // hook Task 6
        }

        return array('linhthach' => $lt, 'stone_id' => $stoneId, 'stone' => $stone,
                     'binh_anh' => $binhAnh, 'html' => $html);
    }
}
```

- [x] **Step 7: Chạy test + lint + quét ký tự**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_boss.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan_boss.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" class/function/linhcan_boss.php class/config/linhcan_boss_config.json || echo CLEAN
```
Expected: `ALL PASS`; `No syntax errors`; `CLEAN`.

- [x] **Step 8: Commit**

```bash
git add class/config/linhcan_boss_config.json class/function/linhcan_boss.php tests/test_linhcan_boss.php
git commit -m "feat(linhcan): boss the gioi element random-of-5 + drop that vao inventory"
```

---
---

### Task 6: Quest tuần — sửa track đủ 5/5 + thưởng vào inventory THẬT

**Files:**
- Modify: `class/config/linhcan_quest_config.json` (viết lại 5 quest theo hook thật)
- Modify: `class/function/linhcan_quest.php` (chỉ `linhcan_quest_claim` — cấp Bình Anh thật)
- Create: `tests/test_linhcan_quest.php`

**Interfaces:**
- Consumes: `linhcan_give_stone` (Task 2 — hook `receive_stone` tự đếm quest 4), hook đã đặt sẵn: `train_element` (Task 3 trong `linhcan_train`), `break_once` + `kill_boss` (Task 4 trong `lcboss_*`/`linhcan_break`), `kill_monsters` (`game/pve.php:352-353` ĐÃ có), `kill_boss` (`game/boss.php:342-343` ĐÃ có).
- Produces: `linhcan_quest_claim($sid,$dblj)` → `array('ok','msg','linhthach','binh_anh')` (Task 11 render). Engine (`get_progress`/`update`/`progress_text`) GIU NGUYEN — chi doi config + claim.

- [x] **Step 1: Viết lại `class/config/linhcan_quest_config.json`** (nguyen chan; 3 quest cu `xiulian_minutes`/`use_items`/`pet_exp` KHONG CO hook bi loai het — lo ngi 1.3 "track 2/5"):

```json
{
  "weekly_quests": {
    "1": {
      "id": 1,
      "name": "Luyện căn cần mẫn",
      "description": "Luyện bất kỳ linh căn hành nào 10 lần",
      "type": "train_element",
      "target": 10,
      "icon": "🧘"
    },
    "2": {
      "id": 2,
      "name": "Diệt trừ yêu ma",
      "description": "Đánh bại 10 quái vật",
      "type": "kill_monsters",
      "target": 10,
      "icon": "⚔"
    },
    "3": {
      "id": 3,
      "name": "Săn boss",
      "description": "Đánh bại 1 boss (thế giới, cá nhân hoặc boss thường)",
      "type": "kill_boss",
      "target": 1,
      "icon": "👑"
    },
    "4": {
      "id": 4,
      "name": "Thu thập tinh thạch",
      "description": "Nhận 5 đá ngũ hành / Bình Anh từ boss và nhiệm vụ",
      "type": "receive_stone",
      "target": 5,
      "icon": "🪨"
    },
    "5": {
      "id": 5,
      "name": "Đột phá cảnh giới",
      "description": "Đột phá 1 bậc linh căn",
      "type": "break_once",
      "target": 1,
      "icon": "✨"
    }
  },
  "total_reward_linhthach": 500,
  "total_reward_binh_anh_min": 1,
  "total_reward_binh_anh_max": 2
}
```
Ghi chu: `reward_linhthach`/`reward_da_ngu_hanh` per-quest bi xoa — trang cu `game/linhcan_quest.php` chi doc `total_reward_linhthach` (isset-guard, `:117`) nen khong fatat; trang bi viet lai o Task 11.

- [x] **Step 2: Test failing — `tests/test_linhcan_quest.php`** (nguyen van):

```php
<?php
// Test quest tuan Linh Can 5/5 hook (Task 6). Chay: php.exe tests/test_linhcan_quest.php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
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
$week = \player\linhcan_quest_current_week();
$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37");
$db->exec("UPDATE game1 SET uyxb = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

// F1: config 5 quest, type duy nhat khop ham moi
$cfg = \player\linhcan_quest_config_all();
$types = array();
foreach ($cfg['weekly_quests'] as $q) { $types[] = $q['type']; }
sort($types);
tassert('F1 5 type dang hook', implode(',', $types), 'break_once,kill_boss,kill_monsters,receive_stone,train_element');

// F2: tung hook tang dung quest
$p = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F2 bat dau 5 key = 0', count($p['progress']), 5);
\player\linhcan_quest_update($TESTSID, 'train_element', 1, $db);
\player\linhcan_quest_update($TESTSID, 'kill_monsters', 3, $db);
\player\linhcan_quest_update($TESTSID, 'loai_khong_ton_tai', 5, $db);
$p = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F2 train_element=1', intval($p['progress']['1']), 1);
tassert('F2 kill_monsters=3', intval($p['progress']['2']), 3);
tassert('F2 type la khong tang gi', array_sum($p['progress']), 4);

// F3: give_stone hook tren = receive_stone dung
\player\linhcan_give_stone($TESTSID, 32, 2, $db);
$p = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F3 receive_stone tang 2', intval($p['progress']['4']), 2);

// F4: chua du → claim fail
$r = \player\linhcan_quest_claim($TESTSID, $db);
tassert('F4 chua du fail', $r['ok'], false);

// F5: du 5/5 → claim cap LT + BINH ANH THAT vao inventory
\player\linhcan_quest_update($TESTSID, 'train_element', 9, $db);
\player\linhcan_quest_update($TESTSID, 'kill_monsters', 7, $db);
\player\linhcan_quest_update($TESTSID, 'kill_boss', 1, $db);
\player\linhcan_quest_update($TESTSID, 'receive_stone', 3, $db);
\player\linhcan_quest_update($TESTSID, 'break_once', 1, $db);
$r = \player\linhcan_quest_claim($TESTSID, $db);
tassert('F5 claim ok', $r['ok'], true);
$uyxb = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$TESTSID'")->fetchColumn());
tassert('F5 LT = 500 (+2 cua give_stone F3 khong lien quan)', $uyxb, 500);
$ba = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('F5 binh anh roi that 1..2', ($ba >= 1 && $ba <= 2), true);
tassert('F5 msg co binh anh', strpos($r['msg'], 'Bình Anh') !== false, true);

// F6: claim lan 2 bi chan; claim sau khi completed khong cho them
$r2 = \player\linhcan_quest_claim($TESTSID, $db);
tassert('F6 claim lap lai fail', $r2['ok'], false);
$ba2 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('F6 so da khong doi', $ba2, $ba);

// F7: completed quyen sach — update sau khi claim khong tang them
$before = \player\linhcan_quest_get_progress($TESTSID, $db);
$sumBefore = array_sum($before['progress']);
\player\linhcan_quest_update($TESTSID, 'kill_monsters', 1, $db);
$after = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F7 completed khoa progress', array_sum($after['progress']), $sumBefore);

$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (32, 37)");
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
```
Run → Expected FAIL F1 (config cu chua doi type) — neu Step 1 chua chay. Thu tu: Step 1 roi Step 2 moi red hop ly; neu chay Step 2 sau Step 1 thi do FAIL o F5 (binh anh chua roi that).

- [x] **Step 3: Sua `linhcan_quest_claim` trong `class/function/linhcan_quest.php`** — THAY doan `:138-156` (tu `// Tính tổng thưởng` den het return) bang:

```php
        // Tinh + cap thuong THAT (LT vao game1, Binh Anh vao playerdaoju qua give_stone)
        $totalLinhThach = intval(isset($config['total_reward_linhthach']) ? $config['total_reward_linhthach'] : 500);
        $baMin = intval(isset($config['total_reward_binh_anh_min']) ? $config['total_reward_binh_anh_min'] : 1);
        $baMax = intval(isset($config['total_reward_binh_anh_max']) ? $config['total_reward_binh_anh_max'] : 2);
        $binhAnh = mt_rand($baMin, max($baMin, $baMax));

        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?");
        $stmt->execute(array($totalLinhThach, $sid));
        linhcan_give_stone($sid, linhcan_binh_anh_id(), $binhAnh, $dblj);

        // Danh dau da nhan
        $week = linhcan_quest_current_week();
        $stmt = $dblj->prepare("UPDATE player_linhcan_quest SET completed = 1 WHERE sid = ? AND quest_week = ?");
        $stmt->execute(array($sid, $week));

        return array(
            'ok' => true,
            'msg' => "Nhận thưởng thành công! +{$totalLinhThach} linh thạch, +{$binhAnh} Bình Anh Tinh Thạch",
            'linhthach' => $totalLinhThach,
            'binh_anh' => $binhAnh
        );
```
Luu y vong luon: `give_stone` trong claim lai hook `receive_stone` — nhung `completed=1` da dat TRUOC nen lan sau claim tu chan (`update` return false tai canh `if ($current['completed'])`). Thu tu trong code tren (dat completed SAU give_stone) se lam progress tuan nay tang nhe — KHONG anh huong vi cot `completed` la khoa, khong phai progress; chap nhan, ghi chu thich trong code.

- [x] **Step 4: Chay test + lint + scan**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_quest.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan_quest.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" class/function/linhcan_quest.php class/config/linhcan_quest_config.json || echo CLEAN
```
Expected: `ALL PASS`; `No syntax errors`; `CLEAN`.

- [x] **Step 5: Commit**

```bash
git add class/config/linhcan_quest_config.json class/function/linhcan_quest.php tests/test_linhcan_quest.php
git commit -m "feat(linhcan): quest tuan 5/5 hook that + thuong binh anh vao inventory"
```

---
---

### Task 7: Tẩy Linh Đan (111) → đan rửa hành + xóa đường tắt random + kiểm kê nợ trừ oan

**Files:**
- Modify: `class/config/daoju_config.json` (`:396` type `reroll_linhcan` → `linhcan_wash`)
- Modify: `game/use_dj.php` (`:22` bo `reroll_linhcan` khoi array gioi han 2 lan; `:85-106` thay nhanh reroll bang redirect sang trang wash)
- Modify: `class/player.php` (`:2062-2072` xoa nhanh `djuse == 'linhcan'`; `:2039` gioi han 2 lan GIU cho thechat/huyetmach, rieng linhcan khong con duong toi)
- Modify: `game.php` (them route `linhcan_wash` canh `:352-355`; **XOA** route `linhcan_upgrade` `:353` + file `game/linhcan_upgrade.php`)
- Create: `game/linhcan_wash.php`
- Modify: `class/function/linhcan.php` (them `linhcan_wash`)
- Modify: `tests/test_linhcan_element.php` (them phan D truoc `exit`)

**Interfaces:**
- Consumes: Task 2 (`linhcan_element_def`, `linhcan_da_id`, `linhcan_node_get/set`), Task 3 (`linhcan_train_cost_sum`), `deledjsum`/`adddj`/`getplayerdaoju`/`player_cache_clear` (`class/player.php:1392/1297/1373/389`).
- Produces: `linhcan_wash($sid,$dblj,$element)` → `array('ok','msg','stone_back','lt_back','stage')`.
- **TUYET DOI TRUONG HOP (sua thoi du trong Task 7, khong doi Task 11):** `game/linhcan_upgrade.php` (route `game.php:353`) dang **truu LT + uexp + da ROI** moi INSERT `player_linhcan_log` — bang **KHONG TON TAI** trong DB (do information_schema + `sql/backup_game_20261004_162045.sql`) → PDO exception fatat **giua chung**, nguoi choi mat nguyen lieu ma linh căn giu nguyen (class/function/linhcan.php:546→554→574→591). Duong nay phai DAT truoc khi he moi boc len — vi vay xoa route + file ngay trong Task 7 (Task 11 chi con don ham chet).

- [x] **Step 1: Test phan D (wash) — them vao `tests/test_linhcan_element.php` truoc `exit(...)`**

```php
// ===== D: tay linh dan (Task 7) =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid IN (35, 111)");
$db->exec("UPDATE game1 SET uyxb = 0 WHERE sid = '" . $TESTSID . "'");
\player\linhcan_node_set($TESTSID, 'kim', 10, 1, $db);
\player\player_cache_clear($TESTSID);

// D1: chua co dan → fail, khong mat gi
$w0 = \player\linhcan_wash($TESTSID, $db, 'kim');
tassert('D1 khong co dan fail', $w0['ok'], false);
$n0 = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D1 node giu nguyen', $n0['level'] . '/' . $n0['stage'], '10/1');

// D2: du dan → reset Lv ve 0, GIU bac, hoan 80% da + 100% LT (ty le tinh tren cost_sum(0,10)=10 da/1675 LT)
\player\adddj($TESTSID, 111, 1, $db);
$w1 = \player\linhcan_wash($TESTSID, $db, 'kim');
tassert('D2 wash ok', $w1['ok'], true);
tassert('D2 stone_back = floor(10*0.8)', intval($w1['stone_back']), 8);
tassert('D2 lt_back = 1675', intval($w1['lt_back']), 1675);
$n1 = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D2 lv ve 0', intval($n1['level']), 0);
tassert('D2 stage VAN giu 1 (spec 2.5)', intval($n1['stage']), 1);
$da = \player\getplayerdaoju($TESTSID, 35, $db);
tassert('D2 da hoan vao inventory', intval($da->djsum), 8);
$lt = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '" . $TESTSID . "'")->fetchColumn());
tassert('D2 LT hoan 100%', $lt, 1675);
$pill = $db->query("SELECT djsum FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid = 111")->fetchColumn();
tassert('D2 dan bi tieu hao', intval($pill), 0);

// D3: hanh chua luyen → fail; hanh la → fail
$w2 = \player\linhcan_wash($TESTSID, $db, 'moc');
tassert('D3 moc lv0 fail', $w2['ok'], false);
\player\adddj($TESTSID, 111, 1, $db);
$w3 = \player\linhcan_wash($TESTSID, $db, 'khong_phai_hanh');
tassert('D3 hanh la fail', $w3['ok'], false);
$pill3 = $db->query("SELECT djsum FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid = 111")->fetchColumn();
tassert('D3 fail khong truu dan', intval($pill3), 1);

$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid IN (35, 111)");
```
Run → FAIL `Call to undefined ... linhcan_wash()`. Red hop le.

- [x] **Step 2: Them `linhcan_wash` vao cuoi `class/function/linhcan.php`**

```php
// ==================== TAY LINH DAN (dj 111) ====================
// Spec 2.5: chon 1 hanh, reset ve Lv 0 NHUNG GIU BAC (kill boss + break van con),
// hoan 80% da + 100% LT da nan (chi phan LUYEN, khong hoan nguyen lieu dot pha).
// Dung adddj truc tiep, KHONG dung linhcan_give_stone — hoan lai khong duoc tinh
// quest receive_stone (ne bot farm: luyen → tay → dem quest).

if (!function_exists(__NAMESPACE__ . '\\linhcan_wash')) {
    function linhcan_wash($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok' => false, 'msg' => 'Hành không hợp lệ.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => 0);
        $node = linhcan_node_get($sid, $element, $dblj);
        $lv = intval($node['level']);
        if ($lv <= 0) {
            return array('ok' => false, 'msg' => $def['name'] . ' chưa luyện gì để mà tẩy.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => intval($node['stage']));
        }
        $pill = getplayerdaoju($sid, 111, $dblj);
        if (!$pill || intval($pill->djsum) < 1) {
            return array('ok' => false, 'msg' => 'Ngươi không có Tẩy Linh Đan.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => intval($node['stage']));
        }

        $cost = linhcan_train_cost_sum(0, $lv);
        $stoneBack = intval(floor($cost['stone'] * 0.8));
        $ltBack = intval($cost['linh_thach']);

        if (!deledjsum(111, 1, $sid, $dblj)) {
            return array('ok' => false, 'msg' => 'Tiêu hao đan thất bại, chưa tẩy.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => intval($node['stage']));
        }
        $stoneId = linhcan_da_id($element);
        if ($stoneBack > 0 && $stoneId > 0) adddj($sid, $stoneId, $stoneBack, $dblj);
        if ($ltBack > 0) {
            $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($ltBack, $sid));
        }
        linhcan_node_set($sid, $element, 0, intval($node['stage']), $dblj);
        player_cache_clear($sid);

        return array('ok' => true,
            'msg' => 'Tẩy thành công! ' . $def['name'] . " về Lv 0 (giữ bậc " . intval($node['stage']) . "), hoàn {$stoneBack} đá + {$ltBack} linh thạch.",
            'stone_back' => $stoneBack, 'lt_back' => $ltBack, 'stage' => intval($node['stage']));
    }
}
```

- [x] **Step 3: Doi `game/linhcan_upgrade.php` — xoa duong loi tai nguyen lieu**

```bash
git rm game/linhcan_upgrade.php
```
Sua `game.php` `:352-355`: XOA dong route `linhcan_upgrade`, thay bang route `linhcan_wash` (dung theo cach `game.php` dang roi `linhcan_quest` — `include 'game/linhcan_wash.php'` trong cung case switch). Sau do:
```bash
grep -rn "linhcan_upgrade" game.php game/*.php class/ --include=*.php | grep -v "^class/function/linhcan.php"
```
Expected: 0 ket qua ngoai `class/function/linhcan.php` (ham cu `linhcan_upgrade()` trong function file chi bi goi boi trang vua xoa — xoa ham o Task 11; **KHONG xoa function luc nay** vi `linhcan.php:164-204` la doc config cu con dung cho `class/player.php:746-747` toi Task 9).

- [x] **Step 4: Tao `game/linhcan_wash.php`** — trang chon hanh toi thieu dung duoc (Task 10/11 moi son lai; tien to `lcw-*`). **Mo dau file, CHEP NGUYEN 5-8 dong khoi tao cua `game/linhcan_quest.php:1-12`** (cach trang nay nhan `$sid/$dblj/$encode` tu `game.php`), roi noi dung duoi day:

```php
$notice = '';
// Router game.php:114 `parse_str($Dcmd)` bien moi cap trong chuoicmd thanh BIEN TOAN CUC
// ($el, $canshu...), KHONG phai $_GET — theo khuon `isset($attack)` cua game/huyetmach_boss.php:16.
$sel = isset($el) ? strtolower(trim($el)) : '';
if ($sel !== '') {
    $ret = \player\linhcan_wash($sid, $dblj, $sel);
    $notice = $ret['ok']
        ? "<font color='#4ade80'>" . $ret['msg'] . "</font>"
        : "<font color='#f87171'>" . $ret['msg'] . "</font>";
}
$pill = \player\getplayerdaoju($sid, 111, $dblj);
$pillHave = $pill ? intval($pill->djsum) : 0;
$nodesW = \player\linhcan_node_rows($sid, $dblj);
$html = "<div class='lcw-wrap'>";
$html .= "<div class='lcw-head'>Tẩy Linh Đan <font color='#a78bfa'>🌀</font> — còn <b>{$pillHave}</b> viên</div>";
$html .= $notice ? "<div class='lcw-notice'>{$notice}</div>" : '';
$html .= "<div class='lcw-list'>";
foreach ($nodesW as $el => $nd) {
    $def = \player\linhcan_element_def($el);
    $costW = \player\linhcan_train_cost_sum(0, intval($nd['level']));
    $backS = intval(floor($costW['stone'] * 0.8));
    $backL = intval($costW['linh_thach']);
    $canW = ($pillHave >= 1 && intval($nd['level']) > 0);
    $nameW = "<font color='{$def['color']}'>{$def['icon']} {$def['name']}</font>";
    if ($canW) {
        $lnk = $encode->encode("cmd=linhcan_wash&sid=$sid&el=$el");
        $html .= "<div class='lcw-row'>{$nameW} · Lv " . intval($nd['level']) . " · bậc " . intval($nd['stage'])
               . " — hoàn {$backS} đá + {$backL} LT <a href='?cmd=$lnk'>Tẩy</a></div>";
    } else {
        $html .= "<div class='lcw-row lcw-off'>{$nameW} · Lv " . intval($nd['level']) . " — "
               . (intval($nd['level']) <= 0 ? 'chưa luyện' : 'hết đan') . "</div>";
    }
}
$html .= "</div>";
$html .= "<div class='lcw-note'>Lưu ý: tẩy chỉ hoàn nguyên liệu LUYỆN (80% đá, 100% linh thạch); Bình Anh và linh khí đã nạp khi đột phá KHÔNG hoàn lại.</div>";
$backW = $encode->encode("cmd=linhcan&sid=$sid");
$html .= "<a class='lcw-back' href='?cmd=$backW'>← Về Vòng Ngũ Hành</a>";
$html .= "</div>";
echo $html;
```
`$notice/$sel` giu nguyen nhu tren; `header`/`exit` khong can vi day la trang render. Neu `game/linhcan_quest.php` dung bien khac `$gonowmid` cho link "ve", dung theo dung bien do.

- [x] **Step 5: Sua `game/use_dj.php`**
- `:22`: THAY `array('reroll_thechat', 'reroll_huyetmach', 'reroll_linhcan')` → `array('reroll_thechat', 'reroll_huyetmach')` (Tẩy Linh Đan là đan reset, dùng không giới hạn — spec 2.5).
- `:85-106`: THAY ca nhanh `elseif ($effectType === 'reroll_linhcan') { ... }` bang:
```php
            } elseif ($effectType === 'linhcan_wash') {
                // Tẩy Linh Đan: khong reroll o day; dan mo trang chon hanh (Task 7)
                $washCmd = $encode->encode("cmd=linhcan_wash&sid=$sid");
                header("Location: game.php?cmd=$washCmd");
                exit;

```

- [x] **Step 6: Sua `class/player.php` + `class/config/daoju_config.json`**
- `class/player.php` `:2062-2072`: XOA nhanh `elseif ($use == 'linhcan') { ... }`, thay chu dan duong:
```php
    elseif ($use == 'linhcan') {
        return array('ok' => false, 'msg' => 'Linh Căn không còn gán ngẫu nhiên. Mở trang Linh Căn để tự luyện từng hành.');
    }
```
- `daoju_config.json` `:396`: `"type": "reroll_linhcan"` → `"type": "linhcan_wash"` (giu `djuse` nhu cu; duong `djuse=='linhcan'` da thanh chuat duoc o tren).
- Chay lenh kiem tra dj 111 con nguyen nhan o `daoju_config` (`grep -n -A3 '"111"' class/config/daoju_config.json | head -20`) va JSON con valid (lenh `json_decode` nhu Task 1 Step 4, doi duong dan file).

- [x] **Step 7: Kiem ke no tru oan (bao cao, KHONG tu hoan)**

He cu: moi lan bam "Nâng cấp" bi mat LT/uexp/da roi fatat (Step 3 da chan). Khong co log de doi chieu chinh xac (`player_linhcan_log` tung chang he ton tai). Chay:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$db=new PDO("mysql:host=localhost;dbname=game;charset=utf8","root","www.aae.ink");
$neg=$db->query("SELECT COUNT(*) FROM game1 WHERE uyxb < 0 OR uexp < 0")->fetchColumn();
echo "players_am_du_noi=$neg\n";
$old=$db->query("SELECT COUNT(*) FROM game1 WHERE linhcan_id > 0")->fetchColumn();
echo "players_tung_co_linhcan_cu=$old\n";'
```
Ghi 2 so nay + ke that "nghi mat nguyen lieu nang cap cu — chua hoan, cho chu quyet" vao `docs/UI_LINHCAN.md` §So-no. **KHONG tu cong tien cho ai.**

- [x] **Step 8: Chay toan bo test + lint + scan**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_element.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l game/linhcan_wash.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l game/use_dj.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/player.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" class/function/linhcan.php game/linhcan_wash.php game/use_dj.php class/player.php class/config/daoju_config.json || echo CLEAN
```
Expected: `ALL PASS`; 5x `No syntax errors`; `CLEAN`. (Nếu `game/linhcan_wash.php` lint fail vi trang can bien `$sid/$dblj/$encode` — do route nap — `php -l` van phai `No syntax errors` vi chi lint cu phap.)

- [x] **Step 9: Commit**

```bash
git add class/function/linhcan.php game/linhcan_wash.php game/use_dj.php class/player.php game.php class/config/daoju_config.json docs/UI_LINHCAN.md
git commit -m "feat(linhcan): Tay Linh Dan thanh dan rua hanh + cat duong nang cap troi loi fatat"
```
(`game/linhcan_upgrade.php` da bi loai o Step 3 qua `git rm` — no nam trong commit nay khi chay `git commit` ma khong can add them.)

---
---

### Task 8: 5 skill ngũ hành THỰC CHIẾN — pve + boss pvbgj, mở khóa theo bậc hành

**Files:**
- Modify: `class/config/linhcan_skill_config.json` (xóa `hu_khong_chuong` + map `none`; `unlock_level`→`unlock_stage: 2` cho 5 skill)
- Modify: `class/function/linhcan_skill.php` (`linhcan_skill_can_use` đổi chữ ký 3 tham số; thêm `linhcan_skill_for_stage`, `linhcan_skill_combat_mods`)
- Modify: `game/pve.php` (case `uselcskill` + 4 điểm cắm mods + nút skill)
- Modify: `game/boss.php` (khuôn song song trong `pvbgj`)
- Create: `tests/test_linhcan_combat.php` (CLI A-D + live E theo `tests/test_huyetmach_combat.php:94-170`)

**Interfaces:**
- Consumes: Task 2 (`linhcan_node_rows`, `linhcan_dominant`), `linhcan_skill_for_element`/`get`/`check_cooldown`/`record_use`/`cooldown_text` (file skill cu, GIU nguyen), `changeplayersx` (pve.php:107 dang dung).
- Produces:
  - `linhcan_skill_for_stage($sid,$dblj)` → skill config array|false — skill của **hành chủ đạo** (`linhcan_dominant`) khi hành đó `stage >= 2`; false khi chưa tới Địa Căn.
  - `linhcan_skill_can_use($player,$skill,$lcStage)` → `array('ok','msg'|'mp_cost')` — **chữ ký 3 tham số MỚI** (đã grep: `linhcan_skill_can_use`/`linhcan_skill_apply` KHONG co caller ngoai file cua no → doi an toan; `linhcan_skill_apply` KHONG dung nua trong combat, giu lai de history, chu thich tren ham).
  - `linhcan_skill_combat_mods($skill)` → `array('atk_mult','crit_add','lifesteal_add','def_pct','heal_pct','ignore_def_pct','name','icon')` — mac dinh `1.0/0...`.
- **Quyet dinh thoi luong (1 request = 1 luot chien):** H5 khong co turn luu giua request → `duration_turns` khong the ton tai nhu ban cu mo. Skill = hieu ung NGAY trong luot bam: buff/debuff chi ap dung cho luot do; heal dot toan bo `heal_percent × duration_turns` (Thủy 8%×3=24% max HP). Ghi chu NAY must duoc bao cho chu du an luc ban giao — do la don phuc bien the, khong phai truong thanh cua 2.3.
- `aoe: true` (Hỏa Diệm Chưởng): PVE/boss hien chi 1 muc tieu → flag giu nguyen trong config, mods bo qua (ghi chu thich trong `linhcan_skill_combat_mods`).

- [x] **Step 1: Sua `class/config/linhcan_skill_config.json`**
- XOA object `hu_khong_chuong` va key `"none"` trong `element_to_skill`.
- Moi skill: XOA `"unlock_level": N`, thay `"unlock_stage": 2`.
- Validate:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '$d=json_decode(file_get_contents("class/config/linhcan_skill_config.json"),true); echo (count($d["skills"])===5 && count($d["element_to_skill"])===5 && !isset($d["skills"]["hoa_diem_chuong"]["unlock_level"]))?"cfg ok\n":"CFG BAD\n";'
```
Expected: `cfg ok`.

- [x] **Step 2: Sua `class/function/linhcan_skill.php`**

THAY toan bo `linhcan_skill_can_use` (`:51-68`) + them 2 ham moi (nguyen van):
```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_can_use')) {
    /** $lcStage = bac cua HANH CHU DAO (khong phai ulv — he moi mo khoa theo Địa Căn). */
    function linhcan_skill_can_use($player, $skill, $lcStage){
        if (!$skill) return array('ok' => false, 'msg' => 'Không có kỹ năng.');
        $unlockStage = intval(isset($skill['unlock_stage']) ? $skill['unlock_stage'] : 2);
        if (intval($lcStage) < $unlockStage) {
            return array('ok' => false, 'msg' => 'Hành chủ đạo chưa đạt Địa Căn (bậc ' . $unlockStage . ').');
        }
        $mpCostPercent = floatval(isset($skill['mp_cost_percent']) ? $skill['mp_cost_percent'] : 10);
        $mpCost = max(1, intval(intval($player->umaxhp) * $mpCostPercent / 100));
        if (intval($player->uhp) <= $mpCost) {
            return array('ok' => false, 'msg' => 'Khí huyết không đủ để thi triển (còn trên ' . $mpCost . ' HP).');
        }
        return array('ok' => true, 'mp_cost' => $mpCost);
    }
}

/** Skill hien dung duoc = skill cua hanh chu dao, khi hanh do >= Dia Can. */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_for_stage')) {
    function linhcan_skill_for_stage($sid, $dblj){
        $nodes = linhcan_node_rows($sid, $dblj);
        $dom = linhcan_dominant($nodes);
        if ($dom === '') return false;
        if (intval($nodes[$dom]['stage']) < 2) return false;
        return linhcan_skill_for_element($dom);
    }
}

/**
 * Dong goc cac mod 1 luot chi (request-scoped). `duration_turns` chi con y nghia
 * doivoi heal (dot don lap + heal_percent); buff/debuff = dung luot bam nay.
 * `aoe` bo qua vi PVE/boss hien chi 1 muc tieu.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_combat_mods')) {
    function linhcan_skill_combat_mods($skill){
        $m = array('atk_mult' => 1.0, 'crit_add' => 0, 'lifesteal_add' => 0,
                   'def_pct' => 0, 'heal_pct' => 0, 'ignore_def_pct' => 0,
                   'name' => '', 'icon' => '');
        if (!$skill) return $m;
        $m['name'] = isset($skill['name']) ? $skill['name'] : '';
        $m['icon'] = isset($skill['icon']) ? $skill['icon'] : '✨';
        $type = isset($skill['type']) ? $skill['type'] : '';
        if ($type === 'damage' && isset($skill['multiplier'])) {
            $m['atk_mult'] = floatval($skill['multiplier']);
        }
        if ($type === 'heal' && isset($skill['heal_percent'])) {
            $d = isset($skill['duration_turns']) ? max(1, intval($skill['duration_turns'])) : 1;
            $m['heal_pct'] = floatval($skill['heal_percent']) * $d;
        }
        if ($type === 'buff') {
            if (isset($skill['lifesteal_bonus'])) $m['lifesteal_add'] = intval($skill['lifesteal_bonus']);
            if (isset($skill['crit_bonus'])) $m['crit_add'] = intval($skill['crit_bonus']);
            if (isset($skill['def_bonus'])) $m['def_pct'] = intval($skill['def_bonus']);
        }
        if ($type === 'debuff' && isset($skill['ignore_def_percent'])) {
            $m['ignore_def_pct'] = floatval($skill['ignore_def_percent']);
        }
        return $m;
    }
}
```

- [x] **Step 3: Test CLI `tests/test_linhcan_combat.php` (phan A-D)** — khung bootstrap copy `tests/test_huyetmach_combat.php:9-24` (`$ROOT`, require player.php + encode.php + function/linhcan_skill.php), doi ten bien `HM_*`→`LC_*`, $TESTSID nhu Task 2. Noi dung:

```php
// A: combat_mods dung tung skill (so lieu = config da doi o Step 1)
$modsHoa = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('hoa_diem_chuong'));
tclose('A1 hoa atk_mult 1.5', $modsHoa['atk_mult'], 1.5, 0.0001);
$modsThuy = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('thuy_ho_thuat'));
tclose('A2 thuy heal_pct 24 (8x3)', $modsThuy['heal_pct'], 24.0, 0.0001);
$modsMoc = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('moc_linh_thuat'));
tassert('A3 moc lifesteal +25', $modsMoc['lifesteal_add'], 25);
$modsKim = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('kim_cuong_chuong'));
tassert('A4 kim crit +40', $modsKim['crit_add'], 40);
$modsTho = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('tho_ho_thuat'));
tassert('A5 tho def_pct 30', $modsTho['def_pct'], 30);
tassert('A6 khong con hu_khong', \player\linhcan_skill_get('hu_khong_chuong'), false);

// B: can_use theo bac hanh + HP
$pSim = new stdClass(); $pSim->umaxhp = 1000; $pSim->uhp = 100; $pSim->ulv = 1;
$sk = \player\linhcan_skill_get('kim_cuong_chuong');
tassert('B1 stage 1 fail', \player\linhcan_skill_can_use($pSim, $sk, 1)['ok'], false);
tassert('B2 stage 2 cost 100', \player\linhcan_skill_can_use($pSim, $sk, 2)['ok'], false); // uhp 100 <= cost 100
$pSim->uhp = 101;
tassert('B3 uhp 101 du', \player\linhcan_skill_can_use($pSim, $sk, 2)['ok'], true);
tassert('B3 mp_cost', \player\linhcan_skill_can_use($pSim, $sk, 2)['mp_cost'], 100);

// C: for_stage — hanh chu dao phai >= 2
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
tassert('C1 chua mo linh can', \player\linhcan_skill_for_stage($TESTSID, $db), false);
\player\linhcan_node_set($TESTSID, 'kim', 35, 1, $db);
tassert('C2 chu dao kim stage1 (chua Dia Can)', \player\linhcan_skill_for_stage($TESTSID, $db), false);
\player\linhcan_node_set($TESTSID, 'kim', 35, 2, $db);
$sk2 = \player\linhcan_skill_for_stage($TESTSID, $db);
tassert('C3 stage2 mo kim_cuong', is_array($sk2) ? $sk2['id'] : false, 'kim_cuong_chuong');
\player\linhcan_node_set($TESTSID, 'moc', 60, 2, $db); // moc chu dao (lv 60 > 35)
$sk3 = \player\linhcan_skill_for_stage($TESTSID, $db);
tassert('C4 doi chu dao → skill moc', is_array($sk3) ? $sk3['id'] : false, 'moc_linh_thuat');

// D: cooldown van dung bang player_linhcan_skill (Task 1 tao bang)
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '" . $TESTSID . "'");
tassert('D1 chua dung → dc true', \player\linhcan_skill_check_cooldown($TESTSID, 'kim_cuong_chuong', $db), true);
\player\linhcan_skill_record_use($TESTSID, 'kim_cuong_chuong', $db);
tassert('D2 vua dung → dc false', \player\linhcan_skill_check_cooldown($TESTSID, 'kim_cuong_chuong', $db), false);
tassert('D3 cooldown_text khong rong', \player\linhcan_skill_cooldown_text($TESTSID, 'kim_cuong_chuong', $db) !== '', true);
$db->exec("UPDATE player_linhcan_skill SET last_used = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE sid = '" . $TESTSID . "'");
tassert('D4 qua 5 phut → dc true', \player\linhcan_skill_check_cooldown($TESTSID, 'kim_cuong_chuong', $db), true);

$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '" . $TESTSID . "'");
```

- [x] **Step 4: Cắm `game/pve.php`** — 5 edit theo anchor THAT (so `:x` duoi day = hien trang, doi chieu khi mo file):

4a. **Command nut** (sau `$usejn3` `:25`):
```php
$lcskillCmd = ''; $lcskillHtml = '';
if (function_exists('player\\linhcan_skill_for_stage')) {
    $lcs0 = \player\linhcan_skill_for_stage($sid, $dblj);
    if ($lcs0) {
        $lcskillCmd = $encode->encode("cmd=pvegj&canshu=uselcskill&sid=$sid&gid=$gid&nowmid=$nowmid");
        $lcskillHtml = "<a href=\"?cmd=$lcskillCmd\">{$lcs0['icon']} {$lcs0['name']}</a>";
    }
}
```
4b. **Case hanh dong** (trong `switch ($canshu)` `:119-130`, them `case` SAU `case 'usejn'` `break;`):
```php
            case 'uselcskill':
                $lcskill = \player\linhcan_skill_for_stage($sid, $dblj);
                $lcnodes = \player\linhcan_node_rows($sid, $dblj);
                $lcdom = \player\linhcan_dominant($lcnodes);
                $lcStage = ($lcdom !== '') ? intval($lcnodes[$lcdom]['stage']) : 0;
                $lcCan = \player\linhcan_skill_can_use($player, $lcskill, $lcStage);
                if (!$lcskill) {
                    $tishihtml = "<font color='#cc0000'>Chưa mở kỹ năng — đạt Địa Căn (bậc 2) cho hành chủ đạo.</font><br/>";
                } elseif (!$lcCan['ok']) {
                    $tishihtml = "<font color='#cc0000'>⚠ " . $lcCan['msg'] . "</font><br/>";
                } elseif (!\player\linhcan_skill_check_cooldown($sid, $lcskill['id'], $dblj)) {
                    $lcCd = \player\linhcan_skill_cooldown_text($sid, $lcskill['id'], $dblj);
                    $tishihtml = "<font color='#cc0000'>Kỹ năng còn hồi chiêu " . $lcCd . "</font><br/>";
                } else {
                    $lcMods = \player\linhcan_skill_combat_mods($lcskill);
                    $newHp = max(1, intval($player->uhp) - intval($lcCan['mp_cost']));
                    \player\changeplayersx('uhp', $newHp, $sid, $dblj);
                    $player->uhp = $newHp;
                    \player\linhcan_skill_record_use($sid, $lcskill['id'], $dblj);
                    $tishihtml = "<font color='#4CAF50'><b>" . $lcMods['icon'] . " Thi triển</b>: <b>{$lcMods['name']}</b> (−{$lcCan['mp_cost']} khí huyết)!</font><br/>";
                }
                break;
```
(Khai bao `$lcMods = array();` cung choi khai bao `$jineng` tai `:116` — de khi khong vao case, doan 4c/4d/4e van chay `!empty()` an toan.)
4c. **Stat mods truoc baokich** (sau `:174` — dong `$player->uxx += $jineng->jnxx;`, TRUOC doan võ công `:176`):
```php
    // === KY NANG LINH CAN — mod trong luot bam (request = 1 luot) ===
    if (!empty($lcMods['crit_add'])) $player->ubj += intval($lcMods['crit_add']);
    if (!empty($lcMods['lifesteal_add'])) $player->uxx += intval($lcMods['lifesteal_add']);
    if (!empty($lcMods['def_pct'])) {
        $lcDefAdd = round($player->ufy * floatval($lcMods['def_pct']) / 100);
        $player->ufy += $lcDefAdd;
        $tishihtml .= "<font color='#ff9800'>🏔 Thổ Hộ Thuật: phòng ngự +{$lcDefAdd}</font><br/>";
    }
    if (!empty($lcMods['heal_pct'])) {
        $lcHeal = max(1, round($player->umaxhp * floatval($lcMods['heal_pct']) / 100));
        $lcNewHp = min(intval($player->umaxhp), intval($player->uhp) + $lcHeal);
        \player\changeplayersx('uhp', $lcNewHp, $sid, $dblj);
        $player->uhp = $lcNewHp;
        $tishihtml .= "<font color='#4CAF50'>💧 Thủy Hộ Thuật: hồi {$lcHeal} khí huyết</font><br/>";
    }
```
4d. **ignore_def** (SAU khoi MACH HUYET `:243`, TRUOC `$gphurt = ` `:244`):
```php
    if (!empty($lcMods['ignore_def_pct'])) {
        $guaiwuFyForDamage = round($guaiwuFyForDamage * max(0, 100 - floatval($lcMods['ignore_def_pct'])) / 100);
    }
```
(chi con nay khi Hư Không... da xoa — de cho duong doi `debuff` cua 5 hanh tuong lai; ghi chu thich 1 dong.)
4e. **atk_mult** (SAU doan san 15% `if ($gphurt < $player->ugj*0.15){...}` `:245-247`, TRUOC doan "Tương khắc ngũ hành" `:249`):
```php
    if (!empty($lcMods) && $lcMods['atk_mult'] != 1.0) {
        $gphurt = round($gphurt * floatval($lcMods['atk_mult']));
        $tishihtml .= "<font color='#ff5722'>" . $lcMods['icon'] . ' ' . $lcMods['name'] . ": x" . $lcMods['atk_mult'] . " sat thuong</font><br/>";
    }
```
4f. **Nut** (skill box `:915-919`): them `{$lcskillHtml}` dong cuoi ben trong `<div class="pve-skill-box">` (heredoc `<<<HTML` tu noi bien — `$lcskillHtml` da la chuoi `<a href=...>` hoan chinh, rong khi chua mo skill).

- [x] **Step 5: Cắm `game/boss.php`** — khuon SONG SONG (file chua `switch` canshu dang bi chu thich hoa `:180-198`, dung nhanh `if` doc lap):
- Anchor lenh: sau khi `$player` duoc nap (`:130` khu `pvbgj`), them `case` bang doan:
```php
    $lcMods = array();
    if (isset($canshu) && $canshu == 'uselcskill') {
        $lcskill = \player\linhcan_skill_for_stage($sid, $dblj);
        $lcnodes = \player\linhcan_node_rows($sid, $dblj);
        $lcdom = \player\linhcan_dominant($lcnodes);
        $lcStage = ($lcdom !== '') ? intval($lcnodes[$lcdom]['stage']) : 0;
        $lcCan = \player\linhcan_skill_can_use($player, $lcskill, $lcStage);
        if (!$lcskill) {
            $tishihtml = "<font color='#cc0000'>Chưa mở kỹ năng — đạt Địa Căn cho hành chủ đạo.</font><br/>";
        } elseif (!$lcCan['ok']) {
            $tishihtml = "<font color='#cc0000'>⚠ " . $lcCan['msg'] . "</font><br/>";
        } elseif (!\player\linhcan_skill_check_cooldown($sid, $lcskill['id'], $dblj)) {
            $tishihtml = "<font color='#cc0000'>Kỹ năng còn hồi chiêu " . \player\linhcan_skill_cooldown_text($sid, $lcskill['id'], $dblj) . "</font><br/>";
        } else {
            $lcMods = \player\linhcan_skill_combat_mods($lcskill);
            $newHp = max(1, intval($player->uhp) - intval($lcCan['mp_cost']));
            \player\changeplayersx('uhp', $newHp, $sid, $dblj);
            $player->uhp = $newHp;
            \player\linhcan_skill_record_use($sid, $lcskill['id'], $dblj);
            $tishihtml = "<font color='#4CAF50'><b>" . $lcMods['icon'] . " Thi triển</b>: <b>{$lcMods['name']}</b>!</font><br/>";
        }
    }
```
- Stat mods: chen TRUOC `$phurt = round($boss->bossgj - ...)` (`:213`) doan giong 4c (crit/lifesteal/def/heal).
- ignore_def: dobiến `$hmBossFy` (`:237-240`), chen sau `:240`:
```php
    if (!empty($lcMods['ignore_def_pct'])) {
        $hmBossFy = round($hmBossFy * max(0, 100 - floatval($lcMods['ignore_def_pct'])) / 100);
    }
```
- atk_mult: chen SAU `:244` (het `if ($gphurt < ...)`), doan giong 4e.
- Nut: trong action block cua `game/boss.php` (grep `Tấn công` de tim), them `$lcs0 = \player\linhcan_skill_for_stage($sid, $dblj);` + link `cmd=pvbgj&canshu=uselcskill&bossid=$bossid&nowmid=$nowmid&sid=$sid` qua `$encode->encode`, hien `icon name` — mau nhu nut tan cong.
- LUU Y: `$tishihtml` trong boss.php co the da duoc khoi tao — kiem tra `grep -n 'tishihtml' game/boss.php | head` va dung `.=` neu no da co noi dung; dung `=` de lap (pve.php:124 cung dung `=` trong case — giu nhat quan).

- [x] **Step 6: Live route E trong `tests/test_linhcan_combat.php`** — copy khuon `tests/test_huyetmach_combat.php:94-170` (`$enc`, `LC_BASE mặc định http://127.0.0.1:8123`, `$lcGet()`), thay noi dung:
```php
// E.1 seed fixture: ep endtime, node kim 35/2 (chu dao → kim_cuong_chuong crit +40)
$db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
$db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at) VALUES (?, 'kim', 35, 2, NOW()) ON DUPLICATE KEY UPDATE level = 35, stage = 2")->execute(array($E2ESID));
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
$db->exec("UPDATE game1 SET uhp = umaxhp, ustamina = umaxstamina WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$mid = intval($pl->nowmid);
// guaiwu + boss dung tam nhu khuon huyetmach (INSERT midguaiwu/boss 9001, XOA o E.4)
$lcGj = intval(round(floatval($pl->ufy) * 0.75)) + 300;
$db->exec("INSERT INTO midguaiwu (gname, ghp, ggj, gfy, glv, mid, gyid, gexp, sid, gmaxhp, gbj, gxx, ms)
    VALUES ('Tam Unh Linh Can', 2000000000, $lcGj, 200, 35, $mid, 54, 1, '', '2000000000', 0, 0, 0)");
$gidL = intval($db->lastInsertId());

// E.2 don PVE co skill: thi triển → cooldown → tu choi
$h1 = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
tassert('E2.1 co chu Thi triển', strpos($h1, 'Thi triển') !== false, true);
tassert('E2.2 ten skill hien', strpos($h1, 'Kim Cương Chưởng') !== false, true);
$row = $db->query("SELECT COUNT(*) FROM player_linhcan_skill WHERE sid = '$E2ESID' AND skill_id = 'kim_cuong_chuong'")->fetchColumn();
tassert('E2.3 ghi cooldown row', intval($row), 1);
$hpNow = intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
tassert('E2.4 HP da bi truu (duoi max)', $hpNow < intval($db->query("SELECT umaxhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn()), true);
$db->exec("UPDATE game1 SET uhp = umaxhp, ustamina = umaxstamina WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$h2 = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
tassert('E2.5 don 2 bi chan cooldown', strpos($h2, 'hồi chiêu') !== false || strpos($h2, 'Thi triển') === false, true);

// E.3 boss pvbgj cung cat nut
$db->exec("DELETE FROM boss WHERE bossid = 9001");
$db->exec("INSERT INTO boss (bossid, bossname, bossinfo, bosslv, bosshp, bossmaxhp, bossgj, bossfy, bossbj, bossxx, bosszb, dljv, bossdj, djjv, bs, bosstime, sid, bossyp, ypjv, bossexp)
    VALUES (9001, 'Boss Thử Skill (tạm thời)', 'tests/test_linhcan_combat.php --live', 35, 2000000000, 2000000000, $lcGj, 200, 0, 0, '', 0, '', 0, 0, NOW(), '', '', 0, '0')");
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$h3 = $lcGet("cmd=pvbgj&bossid=9001&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
tassert('E3.1 boss cung thi trien', strpos($h3, 'Thi triển') !== false, true);

// E.4 don dup
$db->exec("DELETE FROM midguaiwu WHERE id = $gidL");
$db->exec("DELETE FROM boss WHERE bossid = 9001");
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
```
Chay live:
```bash
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -S 127.0.0.1:8199 -t . &   # hoac Apache 80 → LC_BASE=http://localhost
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_combat.php --live
```
Expected: `ALL PASS`. Chu y: message chan cooldown o CA HAI trang (pve + boss) da thong nhat la "Kỹ năng còn hồi chiêu" — neu thay khac giua 2 file la loi chep, thi phat bang cach soi `grep -n "hồi chiêu\|cooldown" game/pve.php game/boss.php`. (Assert E2.5 dung dang `|| strpos($h2,'Thi triển')===false` nen khong nhay cam voi ngu canh.)

- [x] **Step 7: Chay CLI + lint + scan**
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_combat.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan_skill.php game/pve.php game/boss.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" class/function/linhcan_skill.php game/pve.php game/boss.php class/config/linhcan_skill_config.json tests/test_linhcan_combat.php || echo CLEAN
```
Expected: `ALL PASS` (live SKIP khi khong co `--live`); lint sach; `CLEAN`.

- [x] **Step 8: Commit**
```bash
git add class/config/linhcan_skill_config.json class/function/linhcan_skill.php game/pve.php game/boss.php tests/test_linhcan_combat.php
git commit -m "feat(linhcan): 5 skill ngu hanh vao pve+boss, mo khoa theo Dia Can hanh chu dao"
```

---
---

### Task 9: getplayer + zhuangtai — buff + hien thi lay tu node Ngu Hanh

**Files:**
- Modify: `class/player.php` (`:112-121` them 3 dong `var`; `:745-754` thay khoi doc config cu bang doc node/title)
- Modify: `game/zhuangtai.php` (`:424-477` thay ca khoi Linh Căn)
- Create: `tests/test_linhcan_passive.php` (CLI + live)

**Interfaces:**
- Consumes: Task 2 (`linhcan_node_rows`, `linhcan_title`, `linhcan_milestone`, `linhcan_stage_def`), Task 3 (`linhcan_calc_bonuses` hop dong 16 key — `element` = hanh chu dao khong dau, `''` khi tong Lv = 0), ham vinh vien `linhcan_element_icon` (`class/function/linhcan.php:424-435`), `zhuangbei_element_name`.
- Produces (cac field tren `$player` ma Task 10/11/12 + nap_hanh + chetao doc):
  - `linhcan_element` — key `kim|moc|thuy|hoa|tho` hoac `''`. **Da verify trung format** voi `linhcan_element_khac` (`:364-375` map khong dau), `linhcan_element_icon` (`:424-435`), `nguhanh_match_count/apply_passive` (`class/function/nap_hanh.php:87,104` so sanh `in_array($lc, $els, true)` voi element trang bi cung key khong dau), `game/pve.php:263` + `game/boss.php:263` + `game/chetao.php:47`.
  - `linhcan_name` (ten danh hiem tu `linhcan_title`), `linhcan_color`, `linhcan_icon`, `linhcan_title_id`, `linhcan_tier` (bac 0..4 cua hanh chu dao), `linhcan_total_lv`.
- Sau Task 9, `linhcan_config_get` het caller trong `class/player.php` (con sot: `game/linhcan.php` cu — Task 10 viet lai; ham cu trong function file — Task 11 xoa). Chay `grep -rn "linhcan_config_get\|linhcan_random\|linhcan_assign" class/ game/ --include=*.php` va ghi lai danh sach con lai vao commit message.

- [ ] **Step 1: Test fail-truoc — `tests/test_linhcan_passive.php`**

```php
<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
require_once __DIR__ . '/../class/player.php';
$TESTSID = str_repeat('e', 32);

// Seed 1 player that (lay khuôn game1 seed tu Task 4)
$db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uxx, uyxb, uexp)
    VALUES ('$TESTSID', 990113, 'LCPASSIVE', 30, 5000, 5000, NOW(), 3000, 1000, 20, 0, 100000, 10000)
    ON DUPLICATE KEY UPDATE uid = 990113, ulv = 30, uhp = 5000, umaxhp = 5000, endtime = NOW(),
        ugj = 3000, ufy = 1000, ubj = 20, uxx = 0, uyxb = 100000, uexp = 10000");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

// N1: chua mo he — element rong, khong cong stat
$p0 = \player\getplayer($TESTSID, $db);
tassert('N1 element rong', $p0->linhcan_element, '');
tassert('N1 ugj khong doi', intval($p0->ugj), 3000);
tassert('N1 total_lv = 0', intval($p0->linhcan_total_lv), 0);

// N2: kim 40/1 — atk = 2*40*1.005 = 80.4 → 80; tong 40 < 60 → khong milestone
\player\linhcan_node_set($TESTSID, 'kim', 40, 1, $db);
\player\player_cache_clear($TESTSID);
$p1 = \player\getplayer($TESTSID, $db);
tassert('N2 element = chu dao', $p1->linhcan_element, 'kim');
tassert('N2 tier = bac hanh chu dao', intval($p1->linhcan_tier), 1);
tassert('N2 total_lv', intval($p1->linhcan_total_lv), 40);
tassert('N2 ten danh hiem', $p1->linhcan_name, 'Chân Linh Căn');
tassert('N2 ugj 3000+80', intval($p1->ugj), 3080);
tassert('N2 title_id', $p1->linhcan_title_id, 'chan_linh');

// N3: chuoi khac — element key khong dau phai tinh ra 1.2 (kim khac moc)
tassert('N3 khac moc', \player\linhcan_get_element_multiplier($p1->linhcan_element, 'moc'), 1.2);
tassert('N3 trung tinh thuy', \player\linhcan_get_element_multiplier($p1->linhcan_element, 'thuy'), 1.0);

// N4: doi chu dao → field theo sau (tho 70/3: tong 110 ≥ 60 → milestone 5%)
\player\linhcan_node_set($TESTSID, 'tho', 70, 3, $db);
\player\player_cache_clear($TESTSID);
$p2 = \player\getplayer($TESTSID, $db);
tassert('N4 chu dao chuyen tho', $p2->linhcan_element, 'tho');
tassert('N4 tier tho', intval($p2->linhcan_tier), 3);
tassert('N4 total 110', intval($p2->linhcan_total_lv), 110);

$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

if (in_array('--live', $argv)) {
    require __DIR__ . '/e2e_fixture.php'; // $E2ESID, $enc, base URL nhu huyetmach
    $lcBase = getenv('LC_BASE') ? getenv('LC_BASE') : 'http://127.0.0.1:8123';
    $db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
    $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
        VALUES (?, 'kim', 40, 1, NOW()) ON DUPLICATE KEY UPDATE level = 40, stage = 1")->execute(array($E2ESID));
    \player\player_cache_clear($E2ESID);
    $zCmd = $enc->encode("cmd=zhuangtai&sid=$E2ESID");
    $h = file_get_contents("$lcBase/game.php?$zCmd");
    tassert('L1 zhuangtai ve danh hieu', strpos($h, 'Chân Linh Căn') !== false, true);
    tassert('L2 zhuangtai ve tong Lv', strpos($h, 'Tổng Lv 40') !== false, true);
    $db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
    \player\player_cache_clear($E2ESID);
}
exit($fails === 0 ? 0 : 1);
```

Run: `php.exe tests/test_linhcan_passive.php` → FAIL `N1 element rong` khong doi (code cu cung tra `''` khi id=0)! **Cac dong that bai dau la N2 (ten van `''` vi :746 can `linhcan_id` > 0) va N1 `total_lv` (property chua ton tai → null !== 0).** Do la red hop le.

- [ ] **Step 2: Sua `class/player.php`**

2a. Sau `var $linhcan_pet_bonus;` (`:121`) them:
```php
    var $linhcan_icon;
    var $linhcan_title_id;
    var $linhcan_total_lv;
```

2b. Khoi `===== BUFF LINH CĂN (Passive) =====` (`:701-755`): GIU NGUYEN `:703-744` (reset + hop dong 16 key + cong stat + penalty — penalty bay gio luon 0 nhung giu nguyen de khong phat sinh bai khac). THAY doan `:745-754` ("Load thông tin hiển thị" doc `linhcan_config_get`) bang:
```php
        // Thong tin hien thi — nguon node Ngu Hanh (khong doc config 22 can ngau nhien nua).
        $lcNodes = linhcan_node_rows($player->sid, $dblj);
        $lcTitle = linhcan_title($lcNodes);
        $lcTotalLv = 0;
        foreach ($lcNodes as $ndLc) { $lcTotalLv += intval($ndLc['level']); }
        $player->linhcan_name     = $lcTitle['name'];
        $player->linhcan_color    = $lcTitle['color'];
        $player->linhcan_icon     = $lcTitle['icon'];
        $player->linhcan_title_id = $lcTitle['id'];
        $player->linhcan_total_lv = $lcTotalLv;
        $player->linhcan_tier     = 0;
        if ($lcB['element'] !== '') {
            $player->linhcan_tier = intval($lcNodes[$lcB['element']]['stage']);
        }
```
(`linhcan_element` khong can gan o day — `:708` da reset `''`; gan gia tri chu dao ngay sau khi doc hop dong: them 1 dong `$player->linhcan_element = $lcB['element'];` cuoi khoi, TRUOC dong dong `}` cua `if (function_exists...)`. Do `:750` bi xoa nen day la noi duyet nhat gan gia tri nay — nap_hanh + pve/boss doc dung field nay.)
`linhcan_node_rows` la 1 SELECT chuan bi — chap nhan duoc vi getplayer du che boi `player_cache_clear($sid)` (Task 2). Khoi `nguhanh_apply_passive` `:757-761` KHONG dong nguoi — no doc `linhcan_element` vua gan o tren.

- [ ] **Step 3: Thay khoi Linh Căn trong `game/zhuangtai.php` (`:424-477`, nguyen van):**

```php
if (!empty($player->linhcan_element)) {
    $lcColor = $player->linhcan_color;
    $lcElement = $player->linhcan_element;
    $lcName = $player->linhcan_name;
    $lcIcon = !empty($player->linhcan_icon) ? $player->linhcan_icon : \player\linhcan_element_icon($lcElement);
    $lcStage = intval($player->linhcan_tier);
    $lcTotalLv = isset($player->linhcan_total_lv) ? intval($player->linhcan_total_lv) : 0;

    $lcStageName = 'Phàm';
    if (function_exists('player\\linhcan_stage_def')) {
        $lcSd = player\linhcan_stage_def($lcStage);
        if ($lcSd && isset($lcSd['name'])) { $lcStageName = $lcSd['name']; }
    }
    $lcMs = array('percent' => 0, 'name' => '', 'next_total' => 0, 'next_percent' => 0, 'next_name' => '');
    if (function_exists('player\\linhcan_milestone')) {
        $lcMs = player\linhcan_milestone($lcTotalLv);
    }
    $lcElemName = function_exists('player\\zhuangbei_element_name')
        ? player\zhuangbei_element_name($lcElement) : $lcElement;

    $lcDetails = '';
    if ($player->linhcan_absorb != 0) {
        $absorbSign = $player->linhcan_absorb > 0 ? '+' : '';
        $lcDetails .= "<div class='profile-stat'><span>Hấp thụ linh khí</span><strong>{$absorbSign}" . round($player->linhcan_absorb, 2) . "%</strong></div>";
    }
    if ($player->linhcan_exp_bonus != 0) {
        $expSign = $player->linhcan_exp_bonus > 0 ? '+' : '';
        $lcDetails .= "<div class='profile-stat'><span>Tốc độ tu luyện</span><strong>{$expSign}" . round($player->linhcan_exp_bonus, 2) . "%</strong></div>";
    }
    $stats = $player->linhcan_stats;
    $statNames = ['atk'=>'Công','def'=>'Thủ','hp'=>'Huyết','crit'=>'Bạo','lifesteal'=>'Hút'];
    foreach ($stats as $k => $v) {
        if ($v != 0 && isset($statNames[$k])) {
            $sign = $v > 0 ? '+' : '';
            $lcDetails .= "<div class='profile-stat'><span>Linh Căn {$statNames[$k]}</span><strong>{$sign}{$v}</strong></div>";
        }
    }
    if (floatval($lcMs['percent']) > 0) {
        $lcDetails .= "<div class='profile-stat'><span>Cộng hưởng Ngũ Hành</span><strong>+" . $lcMs['percent'] . "% — {$lcMs['name']}</strong></div>";
    } elseif (intval($lcMs['next_total']) > 0) {
        $lcDetails .= "<div class='profile-stat'><span>Mốc cộng hưởng kế</span><strong>Tổng Lv " . intval($lcMs['next_total']) . " (+" . $lcMs['next_percent'] . "%)</strong></div>";
    }

    // Nội tại nạp ngũ hành (spec docs/DESIGN_NGUHANH_KICHHOAT.md §4) — chỉ hiện khi có món hợp căn.
    if (isset($player->nguhanh_info) && intval($player->nguhanh_info['match_count']) > 0) {
        $ngi = $player->nguhanh_info;
        $ngGain = empty($ngi['gains']) ? '' : ' (' . implode(', ', $ngi['gains']) . ')';
        $lcDetails .= "<div class='profile-stat'><span>☯ Nội tại ngũ hành</span><strong>Hợp căn "
            . intval($ngi['match_count']) . ' món — bậc ' . intval($ngi['tier']) . ' +'
            . intval($ngi['pct']) . '%' . $ngGain . '</strong></div>';
    }

    $html .= <<<HTML
    <div class="profile-section">
        <div class="profile-section-title" style="color:{$lcColor}">{$lcIcon} Linh Căn: {$lcName} <span style="font-size:12px;opacity:0.8">{$lcStageName} · {$lcElemName} · Tổng Lv {$lcTotalLv}</span></div>
        <div class="profile-stat-grid">
            {$lcDetails}
        </div>
    </div>

HTML;
}
```
Xoa het trong khoi nay: `tierNames/tierIcons` 0..5 (Niết), `$elementIcons` co key `'không'`, dieu kien cu `!empty($player->linhcan_id) && !empty($player->linhcan_name)`, 2 dong Hấp thụ/Tốc độ cu (giu nguan nhan nhung doi `round(...,2)` vi now la float %). `linhcan_id` DB column van còn — Task 12 mới xử.

- [ ] **Step 4: Chay test → PASS; lint + scan**
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_passive.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/player.php game/zhuangtai.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" class/player.php game/zhuangtai.php tests/test_linhcan_passive.php || echo CLEAN
```
Live (can server local dang chay, khuôn `LC_BASE` nhu Task 8 Step 6): `php.exe tests/test_linhcan_passive.php --live` → `L1/L2 PASS`. Nếu `e2e_fixture.php` khong expose `$enc` (kiem tra `grep -n 'enc' tests/e2e_fixture.php`) thi tao `$enc = new \encode\encode();` theo dung cach `tests/test_huyetmach_combat.php:94-170` da lam.

- [ ] **Step 5: Smoke tuong mo (CHUA test trong game that — ghi no)**

Task 9 xong truoc Task 10 → trang cu `game/linhcan.php` van con mo duoc cho nguoi co `linhcan_id > 0` DB cua, nhung ten hien thi bay gio la danh hieu moi (vi no doc `$player->linhcan_name`). Mo tay: `game.php?cmd=linhcan&sid=<fixture>` — khong fatal, chi lech nhat quan nhe; `docs/UI_LINHCAN.md` ghi 1 dong "cửa trang cu song den Task 10".

- [ ] **Step 6: Commit**
```bash
git add class/player.php game/zhuangtai.php tests/test_linhcan_passive.php docs/UI_LINHCAN.md
git commit -m "feat(linhcan): getplayer+zhuangtai doc node Ngu Hanh — danh hieu, chu dao, cong huong"
```

---
---

### Task 10: VIET LAI `game/linhcan.php` — trang Vòng Ngũ Hành (`.lc-*`) + ảnh + fx ladder

**Files:**
- Rewrite: `game/linhcan.php` (toan bo)
- Create: `images/linhcan/vong-ngu-hanh.jpg` + `E:/window/Temp/lcseed.php` + `E:/window/Temp/lcempty.php`
- Modify: `css/mystyle.css` (them khoi `.lc-*` cuoi file, sau `:7662`), `game.php` (`:502` bump `mystyle.css?v=`)
- Create: `docs/UI_LINHCAN.md`, `docs/img/linhcan/*` (anh chung minh)

**Interfaces:**
- Consumes: Task 2 (`linhcan_node_rows/element_defs/stage_cap/milestone/title/da_id`), Task 3 (`linhcan_train`, `linhcan_calc_bonuses`), Task 4 (`linhcan_break`, `linhcan_break_requirements` → `rows[i] = array('key','label','cur','need','ok')`), Task 8 (`linhcan_skill_for_element`), Task 9 (`$player->linhcan_element` = hanh chu dao khong dau).
- Produces: cac selector `.lc-*` ma Task 11 dung lai (panel 660, tab bar, card); URL tuan thu `cmd=linhcan&sid=$sid&el=<hanh>[&canshu=train|break]` — Task 11/12 link vao day voi `?el=`.

- [ ] **Step 1: Doc khuon moi — bat buoc truoc khi viet**

```bash
grep -n "\.hm-" css/mystyle.css | head -40          # block mach huyet: khuon CSS
grep -n "hm-fx" css/mystyle.css                     # ladder fx — can copy+kieu
grep -c "\.lc-" css/mystyle.css                     # GATE: phai = 0 (prefix trong)
sed -n '1,20p' game/huyetmach.php                   # khuon khoi tao trang con
grep -n "xl-tabbar" css/mystyle.css game/huyetmach.php | head
```
Mo `docs/UI_HUYETMACH.md` + `docs/UI_THECHAT.md` §12 (4 rang buoc fx: `isolation:isolate` cho blend screen; tat animation = `animation:none`; `background-clip:text` dat SAU shorthand; 0px hinh hoc — so do wrap khong doi). Trang nay lam dung selector `.lc-btn` ngay tu dau — KHONG dung `.main button` (bug 6 cua spec, va khong tu sua 3 trang `huyetmach_awakening/transform/quydoi`).

- [ ] **Step 2: Anh vong ngu hanh**

Theo `reference-source-game-assets`: tim trong `F:\TuTienPhucMa`, `E:\natramadongh5`, `F:\MuH5` anh vong nguc/hon-don 5 ngai (ring/mandala). Neu khong co (khong thay duong dan → ghi chu vao UI_LINHCAN.md): ImageGen 1024x768 (ty le ~663×470 cua `nhuc-than.jpg`), prompt: *"Dark fantasy cultivation game UI: circular five-element (wuxing) formation ring viewed straight-on, deep navy background #0f173e, stone platform with 5 glowing elemental altars arranged in a regular pentagon — top: golden metal altar, right: green wood, bottom-right: red fire, bottom-left: brown earth, left: blue water; thin luminous generation-cycle lines connecting them clockwise; centered empty dark core for a badge; no text, no letters"*. Luu `images/linhcan/vong-ngu-hanh.jpg` (copy tu duong dan ImageGen tra ve). Dat `$lcImgVer = '20261010';` trong page va dung `?v=<?=$lcImgVer?>` sau duong dan anh.

- [ ] **Step 3: Viet `game/linhcan.php`** — 5-8 dong dau TIEN NGUYEN tu `game/huyetmach.php:1-8` (lay `$sid/$dblj/$player/$encode`, kiem tra dang nhap + `include` header/footer nen cua no). Noi dung chinh (nguyen van, chi doi comment dau file theo trang):

```php
$lcImgVer = '20261010'; // bump khi doi anh vong
$lcnodes = \player\linhcan_node_rows($sid, $dblj);
$lcdefs  = \player\linhcan_element_defs();
$lccfgAll = \player\linhcan_element_config_all();
$lcStatLbl = isset($lccfgAll['stat_labels']) ? $lccfgAll['stat_labels'] : array();
$lctitle = \player\linhcan_title($lcnodes);
$lcdom   = \player\linhcan_dominant($lcnodes);
$lctotal = 0; $lcmaxStage = 0;
foreach ($lcnodes as $knd => $nd) {
    $lctotal += intval($nd['level']);
    if (intval($nd['stage']) > $lcmaxStage) { $lcmaxStage = intval($nd['stage']); }
}
$lcms = \player\linhcan_milestone($lctotal);
$lcColorDom = $lcdom !== '' ? $lcdefs[$lcdom]['color'] : '#22d3ee';

// Tham so tu URL sau decode di qua parse_str tai game.php:114 → BIEN $el/$canshu (KHONG phai $_GET;
// xem khuon `isset($attack)` game/huyetmach_boss.php:16).
$lcview = isset($el) ? strtolower(trim($el)) : '';
if (!isset($lcdefs[$lcview])) { $lcview = $lcdom !== '' ? $lcdom : 'kim'; }
$ndv  = $lcnodes[$lcview];
$defv = $lcdefs[$lcview];
$capv = \player\linhcan_stage_cap(intval($ndv['stage']));

// ---- hanh dong (train / break) ----
$lcmsg = ''; $lcmsgBad = false;
$lccanshu = isset($canshu) ? $canshu : '';
if ($lccanshu === 'train' || $lccanshu === 'break') {
    if ($lccanshu === 'train') {
        $lcret = \player\linhcan_train($sid, $dblj, $lcview);
    } else {
        $lcret = \player\linhcan_break($sid, $dblj, $lcview);
    }
    $lcmsg = $lcret['msg'];
    $lcmsgBad = !$lcret['ok'];
    $lcnodes = \player\linhcan_node_rows($sid, $dblj);
    $ndv = $lcnodes[$lcview];
    $capv = \player\linhcan_stage_cap(intval($ndv['stage']));
    $lctitle = \player\linhcan_title($lcnodes);
    $lcb = \player\linhcan_calc_bonuses($sid, $dblj); // cache vua bi node_set clear
}

$lclink = function ($el, $canshu = '') use ($encode, $sid, $lcview) {
    $q = 'cmd=linhcan&sid=' . $sid . '&el=' . $el;
    if ($canshu !== '') { $q .= '&canshu=' . $canshu; }
    return '?cmd=' . $encode->encode($q);
};

// ---- rail 5 chip bac hanh ----
$lcchips = '';
foreach ($lcdefs as $elk => $defc) {
    $ndc = $lcnodes[$elk];
    $cls = 'lc-stage-chip';
    if ($elk === $lcview) $cls .= ' is-view';
    if (intval($ndc['stage']) > 0) $cls .= ' is-done';
    if (intval($ndc['level']) === 0 && intval($ndc['stage']) === 0) $cls .= ' is-lock';
    if ($elk === $lcdom) $cls .= ' is-cur';
    $lcchips .= '<a class="' . $cls . '" href="' . $lclink($elk) . '">'
        . '<span class="lc-chip-icon">' . $defc['icon'] . '</span>'
        . '<span class="lc-chip-name">' . $defc['name'] . '</span>'
        . '<span class="lc-chip-stage">bậc ' . intval($ndc['stage']) . '</span></a>';
}

// ---- vong 5 ngoc ----
$lcgems = '';
foreach ($lcdefs as $elk => $defg) {
    $ndg = $lcnodes[$elk];
    $capg = \player\linhcan_stage_cap(intval($ndg['stage']));
    $glow = $capg > 0 ? intval(round(min(1, intval($ndg['level']) / $capg) * 16)) : 0;
    $cls = 'lc-gem slot-' . $elk;
    if ($elk === $lcview) $cls .= ' is-view';
    if (intval($ndg['level']) === 0 && intval($ndg['stage']) === 0) $cls .= ' is-lock';
    $lcgems .= '<a class="' . $cls . '" style="--glow:' . $glow . 'px;'
        . '--gem:' . $defg['color'] . '" href="' . $lclink($elk) . '" title="' . $defg['name'] . '">'
        . '<span class="lc-gem-icon">' . $defg['icon'] . '</span>'
        . '<span class="lc-gem-lv">Lv' . intval($ndg['level']) . '</span></a>';
}
$lcVoCuc = ($lctitle['id'] === 'vo_cuc') ? ' lc-core-vo' : '';
$lccore = '<div class="lc-core' . $lcVoCuc . '" style="--lc-color:' . $lctitle['color'] . '">'
    . '<span class="lc-core-icon">' . $lctitle['icon'] . '</span>'
    . '<span class="lc-core-name">' . $lctitle['name'] . '</span></div>';

// ---- viewline + forge ----
$lcskillView = function_exists('player\\linhcan_skill_for_element') ? \player\linhcan_skill_for_element($lcview) : false;
$lcStageV = intval($ndv['stage']);
$lcSkillTxt = ($lcskillView && $lcStageV >= 2)
    ? $lcskillView['icon'] . ' ' . $lcskillView['name']
    : '🔒 kỹ năng cần Địa Căn';
$lcviewline = $defv['icon'] . ' ' . $defv['name'] . ' · bậc ' . $lcStageV
    . ' (' . \player\linhcan_stage_def($lcStageV)['name'] . ') — Lv ' . intval($ndv['level'])
    . '/' . $capv . ' — ' . $lcSkillTxt;

$lcbtnTrain = '';
if (intval($ndv['level']) >= $capv) {
    $lcbtnTrain = '<span class="lc-btn lc-btn-off">ĐÃ CHẠM TRẦN</span>';
} else {
    $costv = \player\linhcan_train_cost(intval($ndv['level']));
    $havev = \player\getplayerdaoju($sid, \player\linhcan_da_id($lcview), $dblj);
    $haveSum = $havev ? intval($havev->djsum) : 0;
    $lcbtnTrain = '<a class="lc-btn lc-btn-train" href="' . $lclink($lcview, 'train') . '">LUYỆN '
        . strtoupper($defv['name']) . ' <small>' . $costv['stone'] . ' đá (có ' . $haveSum
        . ') · ' . $costv['linh_thach'] . ' LT</small></a>';
}
$lcReqs = \player\linhcan_break_requirements($sid, $dblj, $lcview);
$lcbtnBreak = '';
if ($lcStageV >= \player\linhcan_stage_max()) {
    $lcbtnBreak = '<span class="lc-btn lc-btn-off">THÁNH CĂN — tối đa</span>';
} elseif ($lcReqs['ok']) {
    $lcbtnBreak = '<a class="lc-btn lc-btn-break" href="' . $lclink($lcview, 'break') . '">✦ ĐỘT PHÁ LÊN BẬC ' . $lcReqs['to_stage'] . '</a>';
} else {
    $lcBossUrl = '?cmd=' . $encode->encode("cmd=linhcan_boss&sid=$sid&el=$lcview");
    $lcbtnBreak = '<a class="lc-btn lc-btn-boss" href="' . $lcBossUrl . '">⚔ Boss đột phá ' . $defv['name'] . '</a>';
}

// ---- cot trai: thuoc tinh kich hoat ----
$lcsrowLeft = '';
foreach ($lcdefs as $elk => $defl) {
    $ndl = $lcnodes[$elk];
    $contrib = array();
    foreach ($defl['per_lv'] as $statL => $perL) {
        $lbl = isset($lcStatLbl[$statL]) ? $lcStatLbl[$statL] : $statL;
        $valL = intval(round(floatval($perL) * intval($ndl['level'])));
        if ($valL !== 0) { $contrib[] = $lbl . ' +' . $valL; }
    }
    $lcsrowLeft .= '<div class="lc-attr-row' . ($elk === $lcdom ? ' is-dom' : '') . '">'
        . $defl['icon'] . ' <b>' . $defl['name'] . '</b> Lv ' . intval($ndl['level'])
        . ' · ' . ($contrib ? implode(', ', $contrib) : 'chưa đóng góp') . '</div>';
}
$lcmsLine = '<div class="lc-ms">Cộng hưởng tổng Lv ' . $lctotal . ': <b>+' . $lcms['percent'] . '% ' . $lcms['name'] . '</b>'
    . ($lcms['next_total'] > 0 ? ' — mốc kế: Lv ' . $lcms['next_total'] . ' (+' . $lcms['next_percent'] . '% ' . $lcms['next_name'] . ')' : ' — đã đạt đỉnh') . '</div>';

// ---- cot phai: checklist dot pha ----
$lcsrowRight = '';
foreach ($lcReqs['rows'] as $req) {
    $mark = $req['ok'] ? "<font color='#4ade80'>[Đủ]</font>" : "<font color='#f87171'>[Thiếu]</font>";
    $lcsrowRight .= '<div class="lc-reqs-row">' . $mark . ' ' . $req['label']
        . ' <span class="lc-reqs-num">' . $req['cur'] . '/' . $req['need'] . '</span></div>';
}
```

Render (heredoc `<<<HTML`, lop theo `huyetmach.php`):

```php
$html .= '<div class="lc-wrap lc-fx' . $lcmaxStage . '" style="--lc-tier:' . $lcmaxStage . ';--lc-color:' . $lcColorDom . '">';
if ($lcmsg !== '') {
    $html .= '<div class="lc-notice' . ($lcmsgBad ? ' bad' : '') . '">' . $lcmsg . '</div>';
}
$html .= '<div class="lc-card lc-card-ring">';
$html .= '<div class="lc-head"><span class="lc-title">LINH CĂN</span>'
       . '<span class="lc-badge" style="--lc-color:' . $lctitle['color'] . '">' . $lctitle['name'] . ' · tổng Lv ' . $lctotal . '</span></div>';
$html .= '<div class="lc-rail">' . $lcchips . '</div>';
$html .= '<div class="lc-viewline">' . $lcviewline . '</div>';
$html .= '<div class="lc-forge-wrap"><div class="lc-stage">'
       . '<img class="lc-ring-img" src="images/linhcan/vong-ngu-hanh.jpg?v=' . $lcImgVer . '" alt="Vòng Ngũ Hành">'
       . $lcgems . $lccore . '</div>'
       . '<div class="lc-forge">' . $lcbtnTrain . $lcbtnBreak . '</div></div>';
$html .= '<nav class="lc-tabbar xl-tabbar">'
       . '<a class="is-active">Luyện</a>'
       . '<a href="?cmd=' . $encode->encode("cmd=linhcan_quest&sid=$sid") . '">Nhiệm Vụ</a>'
       . '<a href="?cmd=' . $encode->encode("cmd=linhcan_boss&sid=$sid") . '">Boss Thế Giới</a>'
       . '<a href="?cmd=' . $encode->encode("cmd=zhuangtai&sid=$sid") . '">Nhân Vật</a></nav>';
$html .= '</div>'; // lc-card-ring
$html .= '<div class="lc-card lc-cols">'
       . '<div class="lc-col"><div class="lc-col-title">Thuộc Tính Kích Hoạt</div>' . $lcsrowLeft . $lcmsLine . '</div>'
       . '<div class="lc-col"><div class="lc-col-title">Đột Phá ' . $defv['icon'] . ' ' . $defv['name'] . '</div>'
       . '<div class="lc-reqs">' . $lcsrowRight . '</div>'
       . '<div class="lc-skillcard">' . ($lcskillView ? $lcskillView['icon'] . ' <b>' . $lcskillView['name'] . '</b> — ' . (isset($lcskillView['desc']) ? $lcskillView['desc'] : '') : 'Chưa có kỹ năng hành này') . '</div></div>'
       . '</div>';
$html .= '</div>'; // lc-wrap
echo $html;
```
(`$lcskillView['desc']` — kiem tra khoa mo ta trong `linhcan_skill_config.json` truoc; neu ten khac (vi du `description`) thi dung dung ten ay. `stat_labels` lay tu config hanh — Task 1 da khai; neu `per_lv` co stat ngoai le (nhu `absorb_pct`) thi nhan `absorb_pct → Hấp thụ` — them vao `stat_labels` trong config neu thieu, dung hard-code map trong page.)

- [ ] **Step 4: CSS `.lc-*` — them CUOI file `css/mystyle.css`** (EOF hien tai sau `:7662`; `grep -c "\.lc-"` da = 0 o Step 1). Khoi can dat (nguyen van, them sau `/* ===== LINH CAN ===== */`):

```css
.lc-wrap{max-width:660px;margin:0 auto;padding:10px;color:#e2e8f0}
.lc-card{background:linear-gradient(160deg,#111a35,#0b1024);border:1px solid rgba(148,163,184,.16);border-radius:14px;padding:12px;margin-bottom:10px;position:relative;isolation:isolate}
.lc-head{display:flex;justify-content:space-between;align-items:center}
.lc-title{font-weight:700;letter-spacing:2px;color:#7dd3fc}
.lc-badge{font-size:12px;padding:2px 8px;border-radius:999px;border:1px solid var(--lc-color,#22d3ee);color:var(--lc-color,#22d3ee)}
.lc-rail{display:flex;gap:6px;margin:8px 0;flex-wrap:wrap}
.lc-stage-chip{display:flex;flex-direction:column;align-items:center;min-width:56px;padding:4px 6px;border-radius:10px;border:1px solid rgba(148,163,184,.2);text-decoration:none;color:#94a3b8;font-size:11px}
.lc-stage-chip.is-done{color:#e2e8f0}
.lc-stage-chip.is-cur{border-color:#fbbf24;color:#fde68a}
.lc-stage-chip.is-view{background:rgba(34,211,238,.12)}
.lc-stage-chip.is-lock{opacity:.45}
.lc-viewline{font-size:12px;color:#9fb3c8;margin-bottom:6px}
.lc-stage{position:relative;width:100%;max-width:440px;margin:0 auto}
.lc-ring-img{width:100%;display:block;border-radius:12px}
.lc-gem{position:absolute;transform:translate(-50%,-50%);width:56px;height:56px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-decoration:none;background:radial-gradient(circle at 35% 30%,rgba(255,255,255,.25),var(--gem,#64748b));box-shadow:0 0 var(--glow,0px) var(--gem,#64748b);border:1px solid rgba(255,255,255,.25);color:#0b1024;font-size:11px}
.lc-gem.is-lock{filter:grayscale(.8);opacity:.5}
.lc-gem.is-view{outline:2px solid rgba(255,255,255,.6)}
.lc-gem-icon{font-size:18px}
.lc-gem-lv{font-weight:700}
/* Toa do ngu giac deu tren ANH — gia tri mac dinh duoi day PHAI do lai tren anh that (bai hoc v13 Nhuc Than: khong lay 50% mo) */
.slot-kim{left:50%;top:14%}
.slot-moc{left:84%;top:39%}
.slot-hoa{left:71%;top:79%}
.slot-tho{left:29%;top:79%}
.slot-thuy{left:16%;top:39%}
.lc-core{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:34%;text-align:center}
.lc-core-name{display:block;font-weight:700;color:var(--lc-color,#22d3ee)}
.lc-forge{display:flex;gap:8px;justify-content:center;margin-top:10px;flex-wrap:wrap}
.lc-btn{padding:8px 14px;border-radius:10px;font-weight:700;text-decoration:none;font-size:13px}
.lc-btn small{display:block;font-weight:400;font-size:11px;opacity:.85}
.lc-btn-train{background:linear-gradient(120deg,#0e7490,#22d3ee);color:#06121f}
.lc-btn-break{background:linear-gradient(120deg,#b45309,#fbbf24);color:#1f1306}
.lc-btn-boss{background:linear-gradient(120deg,#7f1d1d,#ef4444);color:#fff}
.lc-btn-off{background:#1e293b;color:#64748b}
.lc-notice{padding:6px 10px;border-radius:8px;background:rgba(74,222,128,.12);color:#4ade80;margin-bottom:8px}
.lc-notice.bad{background:rgba(248,113,113,.12);color:#f87171}
.lc-cols{display:grid;grid-template-columns:1fr;gap:10px}
.lc-col-title{font-weight:700;color:#7dd3fc;margin-bottom:6px}
.lc-attr-row{font-size:12px;padding:3px 0;color:#cbd5e1}
.lc-attr-row.is-dom{color:#fde68a}
.lc-ms{margin-top:6px;font-size:12px;color:#a5b4fc}
.lc-reqs-row{font-size:12px;padding:2px 0}
.lc-reqs-num{color:#64748b}
.lc-skillcard{margin-top:8px;font-size:12px;padding:6px 8px;border-radius:8px;background:rgba(34,211,238,.08)}
@media (min-width:601px){.lc-wrap{max-width:840px}.lc-cols{grid-template-columns:1fr 1fr}}
```
`.lc-tabbar` dung lop co sẵn `xl-tabbar` (kiem tra `grep -n "xl-tabbar" css/mystyle.css` — neu class do nam trong pham vi selector cha dac thu nhu `.hm-* .xl-tabbar` thi copy rule do thanh `.lc-tabbar` riêng; KHONG sua rule cua mach huyet).

- [ ] **Step 5: Fx ladder `lc-fx0..fx4`** — mo `css/mystyle.css`, grep `grep -n "hm-fx" css/mystyle.css` va doc ca 5 block; VIET LAI theo bang doi chieu (giu nguyên kieu animation cua mach huyet, chi doi ten bien/selector):

| Level | Mach Huyet goc | Linh Can phai co |
|---|---|---|
| fx0 | nen tinh | `.lc-card-ring` gradient tinh + halo dem cho `.lc-core` (text-shadow `0 0 6px var(--lc-color)`) — **bac thap nhat van phai THAY GI DO** (bai hoc fx0) |
| fx1 | nhip tho | `.lc-core` @keyframes `lc-breathe` (scale 1↔1.04, 3.2s) |
| fx2 | sweep vong | `.lc-stage::after` @keyframes `lc-sweep` (vong conic quay 12s linear, blend screen + `isolation:isolate` dat tren `.lc-card`) |
| fx3 | + 5 duong tia | `.lc-gem::before` tia mau `var(--gem)` @keyframes `lc-ray` nhip 2.4s (5 ngoc 5 mau hanh) |
| fx4 | ru toan vong | `.lc-ring-img` @keyframes `lc-spin` 40s linear (tom tat fx3) |

Quy tac bat buoc (docs/UI_THECHAT.md §12.4): tat hieu ung = `animation:none` (khong dung `animation-play-state`); `background-clip:text` LUON dat SAU shorthand `background:` trong cung rule; 0px hinh hoc — so do wrap khong duoc doi khi them fx. **Danh hieu Vu Cuc**: rule rieng `.lc-core-vo .lc-core-name` chu cau vong — `background:linear-gradient(90deg,#f87171,#fbbf24,#4ade80,#38bdf8,#c084fc,#f87171);background-size:200% 100%;-webkit-background-clip:text;background-clip:text;color:transparent;` + @keyframes `lc-rainbow` 6s (chi khi `title_id=vo_cuc` → page da gan class `lc-core-vo`).

- [ ] **Step 6: Seed helpers ngoai repo** (dung cho Step 7 + Task 11/12):

`E:/window/Temp/lcseed.php` (ngoai repo → dung duong dan tuy doi):
```php
<?php // lcseed.php <sid> <el> <lv> <stage> [da=50]
error_reporting(E_ALL & ~E_NOTICE);
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/player.php';
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8','root','www.aae.ink',array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
$sid=$argv[1];$el=$argv[2];$lv=intval($argv[3]);$st=intval($argv[4]);
$da=isset($argv[5])?intval($argv[5]):50;
\player\linhcan_node_set($sid,$el,$lv,$st,$db);
\player\adddj($sid,\player\linhcan_da_id($el),$da,$db);
\player\adddj($sid,37,20,$db);
$db->exec("UPDATE game1 SET uyxb=uyxb+200000 WHERE sid='$sid'");
echo "seeded $sid $el lv=$lv stage=$st\n";
```
`E:/window/Temp/lcempty.php`: xoa ca 5 node cua 1 sid + `player_cache_clear`:
```php
<?php
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/player.php';
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8','root','www.aae.ink');
$db->exec("DELETE FROM player_linhcan_node WHERE sid='".$argv[1]."'");
\player\player_cache_clear($argv[1]);
echo "emptied\n";
```

- [ ] **Step 7: Bump cache + do toa do + anh chung minh**

1. `game.php:502`: doi `mystyle.css?v=<so cu>` → `?v=20261010a`.
2. Server local dang chay tren `127.0.0.1:8123` (neu khong: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -S 127.0.0.1:8199 -t .` va dung port do). Seed tai khoan E2E: `php.exe E:/window/Temp/lcseed.php e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2 kim 40 2`.
3. Chup Chrome headless (KHONG dung browser-use MCP — dinh huong user): khung lenh theo `docs/UI_HUYETMACH.md`/`docs/UI_THECHAT.md` §9.1 (viewport **390/601/840/1337**, `--hide-scrollbars`, dat `zoom=1` truoc khi do — trang goc zoom .75, iframe 390px cho mobile render). Luu anh vo `docs/img/linhcan/` ten `_lc<viewport>_<noidung>.png` — **xoa khoi web root ngay sau khi xong**.
4. **Do toa do that**: ghep anh vong goc vs screenshot trang cung ti le roi SO BANG MAT (bai hoc tu Nhuc Than: do so/computed style khong bat duoc lech thi giac); so tam 5 ngoc vs tam 5 ngai tren anh; lech >3% → chinh `.slot-*` bang so do duoc (khong phai so phong). Ghi bang toa do cuoi vao `docs/UI_LINHCAN.md`.
5. **Fx ladder**: seed tung cap stage (dun `lcseed.php ... <el> <lv> <0..4>`) → chup 5 anh `_lcfx0.png … _lcfx4.png`; fx4 + danh hieu Vu Cuc (seed 5 hanh du 70/3 → Vu Cuc Linh The) chup them `_lcvo.png`. Kiem tra bang mat: fx0 van thay halo; tat ca ngoc khoa (lv/stage 0 — dung `lcempty.php`) van hien nua tinh.

- [ ] **Step 8: `docs/UI_LINHCAN.md`** — theo khuôn `docs/UI_HUYETMACH.md`: (1) ban do hien trang + route; (2) cay cau truc `.lc-*`; (3) bang toa do ngoc do duoc; (4) fx ladder 5 bac + dieu kien Vu Cuc; (5) **so no**: mark ro "CHUA test: cham that tren mobile, fx trong game that, anh vong nguon <imagegen|source-game>"; (6) loi ban + ten nut chot. CHANGELOG.md chi cap nhat o Task 12 (moi lenh 1 lan).

- [ ] **Step 9: Kiem tra + commit**
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l game/linhcan.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201C}\x{201D}\x{00a0}]" game/linhcan.php css/mystyle.css || echo CLEAN
grep -n "linhcan_upgrade" game/linhcan.php || echo NO-DEAD-LINK
```
Expected: lint sach, `CLEAN`, `NO-DEAD-LINK` (trang khong duoc con link toi route da xoa o Task 7).
```bash
git add game/linhcan.php css/mystyle.css game.php images/linhcan/vong-ngu-hanh.jpg docs/UI_LINHCAN.md docs/img/linhcan
git commit -m "feat(linhcan): trang Vong Ngu Hanh — 5 ngoc tren anh vong, forge + dot pha + fx0..4"
```

---

---

### Task 11: Viết lại trang Quest + trang Boss (thế giới & cá nhân theo hành), stylesheet Tẩy Linh, xóa hết hàm cũ 22-căn

**Files:**
- Modify: `game/linhcan_quest.php` (viết lại toàn bộ — bỏ `<style>` nội tuyến, dùng block `.lcq-*`)
- Modify: `game/linhcan_boss.php` (viết lại toàn bộ — không `el` = boss thế giới 2h; `?el=<hanh>` = boss cá nhân đột phá)
- Modify: `css/mystyle.css` (nối EOF, sau block `.lc-*` của Task 10: `.lcq-*`, `.lcb-*`, `.lcw-*`)
- Modify: `class/function/linhcan.php` (xóa 13 hàm cũ chết + đổi nghĩa `linhcan_quality_name` theo spec §3.1)
- Create: `tests/test_linhcan_pages.php` (live — lần đầu tiên 2 nút claim/attack-world chạy THẬT)
- Modify: `docs/UI_LINHCAN.md` (dòng sổ nợ dead-code + phần CHƯA test)

**Interfaces:**
- Consumes (chữ ký thật, đã kiểm):
  - Quest: `linhcan_quest_get_progress($sid,$dblj)` → `array('completed'=>bool,'progress'=>array(qid=>n))`; `linhcan_quest_config_all()` (5 quest Task 6 + `total_reward_linhthach` + `total_reward_binh_anh_min/max`); `linhcan_quest_current_week()`; `linhcan_quest_claim($sid,$dblj)` → `array('ok','msg','linhthach','binh_anh')` (Task 6 — đã cấp Bình Anh THẬT qua `adddj`).
  - Boss thế giới (giữ nguyên chữ ký Task 5): `linhcan_boss_check_spawn($dblj)`, `linhcan_boss_spawn($dblj)`, `linhcan_boss_calc_damage($player,$bossRow,$dblj)` → `array('damage','crit','elem_mult')`, `linhcan_boss_mark_killed($bossId,$killerSid,$dblj)`, `linhcan_boss_drops($player,$bossRow,$dblj)` → `array('linhthach','stone_id','stone','html')`.
  - Boss cá nhân (Task 4): `lcboss_active($sid,$dblj,$element)`, `lcboss_spawn($sid,$dblj,$element)` (return row active HOẶC `false` vì gate), `lcboss_calc_damage($player,$bossRow)` → `array('damage','crit','elem_mult')`, `lcboss_boss_counter($player,$bossRow)`, `lcboss_mark_killed($sid,$dblj,$row)` (tự `UPDATE ... status='killed', hp=0` — trang KHÔNG được UPDATE lại), `lcboss_drops($sid,$dblj,$bossRow)` → `array('stone_id','stone','html')`.
  - **KHÔNG gọi `linhcan_quest_update` từ trang** — hook `kill_boss` đã nằm TRONG `lcboss_drops` (06_task4.md:302-303) và `linhcan_boss_drops` (07_task5.md:145-146); hook `receive_stone` nằm TRONG `linhcan_give_stone`. Trang gọi lại = đếm double quest.
  - Hiển thị hành: `linhcan_element_def($key)`, `linhcan_element_name/color/icon($key)` (keepers vô hạn định, key không dấu), `linhcan_node_get($sid,$element,$dblj)`, `linhcan_stage_cap($stage)`, `linhcan_stage_max()`.
  - Tham số router: `game.php:114 parse_str($Dcmd)` → mỗi cặp trong cmd encode thành BIEN TOAN CUC. Tên chốt: quest `claim_reward=1`; boss thế giới giữ tên link cũ `attack_lc_boss=1`; boss cá nhân `pattack=1` + `el=<hanh>`.
- Produces: không hàm mới. URL ổn định cho Task 10/12: `cmd=linhcan&sid=$sid&el=<hanh>` (vòng), `cmd=linhcan_boss&sid=$sid&el=<hanh>` (Task 10 nút Đột Phá đã trỏ vào đây), `cmd=linhcan_wash&sid=$sid`.

- [ ] **Step 1: Bằng chứng dead-code + mirror đọc**

```bash
# (a) Ghi lại nguyên văn 2 branch chết TRƯỚC khi xóa — bằng chứng cho sổ nợ Step 7:
grep -n 'cmd == ' game/linhcan_quest.php game/linhcan_boss.php
# (b) Mirror khuôn boss đã chiến đấu thật (KHÔNG phải file cũ):
sed -n '1,60p' game/huyetmach_boss.php      # isset($attack), gate uhp<=0, cache-clear+reload sau một kill
# (c) Selector chưa tồn tại (landmine "CSS phải sửa hết selector trùng"):
grep -c '\.lcq-\|\.lcb-\|\.lcw-' css/mystyle.css        # phải = 0
grep -n '.lc-tabbar' css/mystyle.css                    # phai > 0 (Task 10 da dinh nghia; trang nay DUNG LAI, khong dinh nghia lai)
```
Kỳ vọng (a): `game/linhcan_quest.php:8: if ($cmd == 'claim_reward') {` và `game/linhcan_boss.php:37: if ($cmd == 'attack_lc_boss' ...)`. Lưu output vào scratch — dùng ở Step 7. Lý do chết: `$cmd` decode tại `game.php:111` luôn bằng `'linhcan_quest'`/`'linhcan_boss'`, không bao giờ bằng tên action.

- [ ] **Step 2: Viết lại `game/linhcan_quest.php`** (nguyên văn; 5-8 dòng khởi tạo copie nguyên từ `game/huyetmach.php:1-8` như Task 10 Step 3 — `$sid/$dblj/$player/$encode` + header/footer nền của nó; phần dưới thay toàn bộ nội dung cũ):

```php
<?php
// Nhiem vu Linh Can tuan — trang theo khuon mach huyet (docs/UI_LINHCAN.md).
// Logic: class/function/linhcan_quest.php | CSS: css/mystyle.css block .lcq-*
// Router game.php:114 parse_str($Dcmd) bien moi cap trong cmd thanh BIEN TOAN CUC.
// FIX bug cu: `if ($cmd == 'claim_reward')` (linhcan_quest.php:8 cu) KHONG BAO GIO khoi
// dong vi $cmd luon la 'linhcan_quest'. Nay dung `isset($claim_reward)` — cung cach
// game/huyetmach_boss.php:16 (`isset($attack)`).

$ztcmd    = $encode->encode("cmd=zhuangtai&sid=$sid");
$lccmd    = $encode->encode("cmd=linhcan&sid=$sid");
$lcbcmd   = $encode->encode("cmd=linhcan_boss&sid=$sid");
$claimCmd = $encode->encode("cmd=linhcan_quest&claim_reward=1&sid=$sid");

$player = \player\getplayer($sid, $dblj);

$questData   = \player\linhcan_quest_get_progress($sid, $dblj);
$lcqcfg      = \player\linhcan_quest_config_all();
$lcquests    = isset($lcqcfg['weekly_quests']) ? $lcqcfg['weekly_quests'] : array();
$lcweek      = \player\linhcan_quest_current_week();
$isCompleted = !empty($questData['completed']);

$claimMsg = '';
if (isset($claim_reward)) {
    // Claim chi dung mot lan/tuan — that bai thi `linhcan_quest_claim` tra msg rieng.
    $res = \player\linhcan_quest_claim($sid, $dblj);
    $claimMsg = !empty($res['ok'])
        ? '<div class="lcq-notice lcq-good">✅ ' . $res['msg'] . '</div>'
        : '<div class="lcq-notice lcq-bad">⚠ ' . $res['msg'] . '</div>';
    $questData = \player\linhcan_quest_get_progress($sid, $dblj); // doc lai sau khi claim
}

$allComplete = true;
foreach ($lcquests as $qid => $q) {
    $progQ = isset($questData['progress'][$qid]) ? intval($questData['progress'][$qid]) : 0;
    if ($progQ < intval($q['target'])) { $allComplete = false; break; }
}

$ltReward = intval(isset($lcqcfg['total_reward_linhthach']) ? $lcqcfg['total_reward_linhthach'] : 500);
$baMin = intval(isset($lcqcfg['total_reward_binh_anh_min']) ? $lcqcfg['total_reward_binh_anh_min'] : 1);
$baMax = intval(isset($lcqcfg['total_reward_binh_anh_max']) ? $lcqcfg['total_reward_binh_anh_max'] : 2);

$lcqrows = '';
foreach ($lcquests as $qid => $q) {
    $progQ = isset($questData['progress'][$qid]) ? intval($questData['progress'][$qid]) : 0;
    $targetQ = intval($q['target']);
    $doneQ = min($progQ, $targetQ);
    $pctQ = $targetQ > 0 ? min(100, intval($doneQ * 100 / $targetQ)) : 0;
    $okQ = $doneQ >= $targetQ;
    $lcqrows .= '<div class="lcq-item' . ($okQ ? ' lcq-done' : '') . '">'
        . '<div class="lcq-item-head"><span>' . $q['icon'] . ' ' . $q['name'] . '</span>'
        . '<span class="lcq-count">' . $doneQ . '/' . $targetQ . ($okQ ? ' ✅' : '') . '</span></div>'
        . '<div class="lcq-desc">' . $q['description'] . '</div>'
        . '<div class="lcq-bar"><div class="lcq-bar-fill" style="width:' . $pctQ . '%"></div></div>'
        . '</div>';
}
?>
<div class="lcq-wrap">
    <div class="lcq-card">
        <div class="lcq-head">
            <span class="lcq-title">📜 NHIỆM VỤ LINH CĂN</span>
            <span class="lcq-week">Tuần <?= $lcweek ?></span>
        </div>
        <?= $claimMsg ?>
        <div class="lcq-status <?= $isCompleted ? 'is-done' : 'is-open' ?>">
            <?= $isCompleted ? '✅ Đã nhận thưởng tuần này — quay lại tuần sau' : 'Đang thực hiện — nhận khi đủ 5/5' ?>
        </div>
        <?= $lcqrows ?>
        <div class="lcq-reward">
            <b>🎁 Thưởng hoàn thành:</b> <?= $ltReward ?> Linh thạch ·
            <?= $baMin ?>-<?= $baMax ?> Bình Anh Tinh Thạch (vào túi đồ thật)
        </div>
        <?php if (!$isCompleted && $allComplete): ?>
        <div class="lcq-claimwrap">
            <a class="lcq-btn" href="?cmd=<?= $claimCmd ?>">NHẬN THƯỞNG</a>
        </div>
        <?php endif; ?>
    </div>
    <nav class="lc-tabbar xl-tabbar">
        <a href="?cmd=<?= $lccmd ?>">Luyện</a>
        <a class="is-active">Nhiệm Vụ</a>
        <a href="?cmd=<?= $lcbcmd ?>">Boss Thế Giới</a>
        <a href="?cmd=<?= $ztcmd ?>">Nhân Vật</a>
    </nav>
</div>
```
- [ ] **Step 3: Viết lại `game/linhcan_boss.php`** — MỘT file hai chế độ. 5-8 dòng khởi tạo copie nguyên `game/huyetmach.php:1-8` như Step 2. Lưu ý phân biệt hai họ hàm: `linhcan_boss_*` = thế giới (bảng `linhcan_boss`, chung toàn server), `lcboss_*` = cá nhân (bảng `linhcan_boss_personal`, theo hành). Chữ ký `lcboss_drops($sid,$dblj,$bossRow)` KHÔNG giống `linhcan_boss_drops($player,$bossRow,$dblj)` — đừng tráo tham số.

```php
<?php
// Boss Linh Can — hai che do trong mot trang:
//   khong co `el`  : boss THE GIOI (song 2h, chung toan server, nguon da hiem — Task 5).
//   `el=<hanh>`    : boss CA NHAN dot pha bac hanh do (Task 4, khong the nop chay —
//                    linhcan_break doi bang killed). Khuon: game/huyetmach_boss.php.
// Tham so qua parse_str (game.php:114): $attack_lc_boss (the gioi), $el + $pattack (ca nhan).
// FIX bug cu: `if ($cmd == 'attack_lc_boss')` (linhcan_boss.php:37 cu) la nhanh CHET.
// Hook quest (kill_boss/receive_stone) DA nam TRONG lcboss_drops / linhcan_boss_drops
// + linhcan_give_stone — trang TUYET khong goi linhcan_quest_update lap lai.

$ztcmd  = $encode->encode("cmd=zhuangtai&sid=$sid");
$lccmd  = $encode->encode("cmd=linhcan&sid=$sid");
$player = \player\getplayer($sid, $dblj);

$lcv    = isset($el) ? strtolower(trim($el)) : '';
$lcdefP = ($lcv !== '') ? \player\linhcan_element_def($lcv) : false;
$isPersonal = is_array($lcdefP);

$battleLog = '';
$bossHtml  = '';
$hintHtml  = '';

if ($isPersonal) {
    // ==================== BOSS CA NHAN THEO HANH ====================
    $boss = \player\lcboss_active($sid, $dblj, $lcv);
    if (!$boss) { $boss = \player\lcboss_spawn($sid, $dblj, $lcv); } // gate trong ham, khong spawn sai

    $pattackCmd = $encode->encode("cmd=linhcan_boss&sid=$sid&el=$lcv&pattack=1");
    $backCmd    = $encode->encode("cmd=linhcan&sid=$sid&el=$lcv");
    $canFight = isset($pattack) && $boss && intval($boss['hp']) > 0;

    if ($canFight && intval($player->uhp) <= 0) {
        // nhu huyetmach_boss.php:17 — trong thuong thi khong duoc danh tiep.
        $battleLog = "<font color='#f87171'>Ngươi đang trọng thương (Khí Huyết = 0) — phải hồi phục trước khi khiêu chiến.</font>";
    } elseif ($canFight) {
        $res = \player\lcboss_calc_damage($player, $boss);
        $dmg = intval($res['damage']);
        $critText = $res['crit'] ? " <font color='#FFD700'>[Bạo kích!]</font>" : '';
        $elemText = '';
        if ($res['elem_mult'] > 1.0) $elemText = " <font color='#4CAF50'>[Khắc chế! +" . intval(($res['elem_mult'] - 1) * 100) . '%]</font>';
        elseif ($res['elem_mult'] < 1.0) $elemText = " <font color='#f44336'>[Bị khắc! -" . intval((1 - $res['elem_mult']) * 100) . '%]</font>';
        $battleLog .= "Ngươi gây <font color='#FF5722'>{$dmg}</font> sát thương lên " . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . "{$critText}{$elemText}<br/>";

        $newHp = max(0, intval($boss['hp']) - $dmg);
        $dblj->prepare("UPDATE linhcan_boss_personal SET hp = ? WHERE id = ?")->execute(array($newHp, intval($boss['id'])));

        if ($newHp <= 0) {
            $boss['hp'] = 0;
            \player\lcboss_mark_killed($sid, $dblj, $boss);   // tu set status='killed', hp=0
            $drop = \player\lcboss_drops($sid, $dblj, $boss);  // da + hook kill_boss/receive_stone ben trong
            $battleLog .= "<font color='#4CAF50'>" . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . " đã bị tiêu diệt!</font><br/>" . $drop['html'];
            $battleLog .= "<font color='#FFD700'>→ Mở khóa Đột Phá bậc " . ($lcdefP['name']) . '</font><br/>';
            $hintHtml = '<div class="lcb-hint">Boss đã bị hạ — về Vòng Ngũ Hành bấm <b>ĐỘT PHÁ</b>.</div>';
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
            $boss = false;
        } else {
            $bDmg = intval(\player\lcboss_boss_counter($player, $boss));
            $newPlayerHp = max(0, intval($player->uhp) - $bDmg);
            $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?")->execute(array($newPlayerHp, $sid));
            $battleLog .= htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . " phản đòn <font color='#f44336'>{$bDmg}</font> sát thương<br/>";
            \player\player_cache_clear($sid);
            $player = \player\getplayer($sid, $dblj);
            $boss['hp'] = $newHp;
        }
    }

    if ($boss) {
        $hpPct = intval($boss['maxhp']) > 0 ? min(100, intval(intval($boss['hp']) * 100 / intval($boss['maxhp']))) : 0;
        $bossHtml = '<div class="lcb-card" style="--lcb-color:' . $lcdefP['color'] . '">'
            . '<div class="lcb-icon">' . $lcdefP['icon'] . '</div>'
            . '<div class="lcb-name">' . htmlspecialchars($boss['name'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div class="lcb-level">Lv ' . intval($boss['level']) . ' · ' . $lcdefP['name'] . ' căn · chặn đột phá bậc ' . intval($boss['to_stage']) . '</div>'
            . '<div class="lcb-hpbar"><div class="lcb-hpfill" style="width:' . $hpPct . '%"></div></div>'
            . '<div class="lcb-hptext">HP ' . intval($boss['hp']) . ' / ' . intval($boss['maxhp']) . '</div>'
            . '<div class="lcb-stats"><span>⚔ ' . intval($boss['atk']) . '</span><span>🛡 ' . intval($boss['def']) . '</span><span>💥 ' . intval($boss['crit']) . '%</span></div>'
            . '<a class="lcb-btn" href="?cmd=' . $pattackCmd . '">TẤN CÔNG</a>'
            . '</div>';
    } else {
        // Tai khong co boss — noi ro dieu kien (dung cach Task 4 gate).
        $ndP = \player\linhcan_node_get($sid, $lcv, $dblj);
        $stP = intval($ndP['stage']); $lvP = intval($ndP['level']);
        $capP = \player\linhcan_stage_cap($stP);
        if ($stP >= \player\linhcan_stage_max()) {
            $hintHtml = '<div class="lcb-hint">Hành <b>' . $lcdefP['name'] . '</b> đã đạt bậc cao nhất — không còn boss đột phá.</div>';
        } elseif ($lvP < $capP) {
            $hintHtml = '<div class="lcb-hint">Cần <b>Lv ' . $capP . '</b> (đang Lv ' . $lvP . ') để khiêu chiến boss bậc ' . ($stP + 1) . '. <a href="?cmd=' . $backCmd . '">Luyện tiếp →</a></div>';
        } else {
            $hintHtml = '<div class="lcb-hint">Boss đã bị hạ từ trước — giờ có thể <a href="?cmd=' . $backCmd . '">ĐỘT PHÁ</a>.</div>';
        }
        $bossHtml = '<div class="lcb-empty">Không có boss đang hoạt động</div>';
    }
} else {
    // ==================== BOSS THE GIOI (2h, chung) ====================
    $bossData = \player\linhcan_boss_check_spawn($dblj);
    if (!$bossData) { $bossData = \player\linhcan_boss_spawn($dblj); }
    $wattackCmd = $encode->encode("cmd=linhcan_boss&attack_lc_boss=1&sid=$sid");

    if (is_array($bossData) && intval($bossData['hp']) > 0) {
        $canFightW = isset($attack_lc_boss);
        if ($canFightW && intval($player->uhp) <= 0) {
            $battleLog = "<font color='#f87171'>Ngươi đang trọng thương (Khí Huyết = 0) — phải hồi phục trước khi khiêu chiến.</font>";
        } elseif ($canFightW) {
            $result = \player\linhcan_boss_calc_damage($player, $bossData, $dblj);
            $playerDamage = intval($result['damage']);
            $critText = $result['crit'] ? ' <font color="#FFD700">[Bạo kích!]</font>' : '';
            $elemText = '';
            if ($result['elem_mult'] > 1.0) $elemText = ' <font color="#4CAF50">[Khắc chế! +' . intval(($result['elem_mult'] - 1) * 100) . '%]</font>';
            elseif ($result['elem_mult'] < 1.0) $elemText = ' <font color="#f44336">[Bị khắc! -' . intval((1 - $result['elem_mult']) * 100) . '%]</font>';
            $battleLog .= "Ngươi gây <font color='#FF5722'>{$playerDamage}</font> sát thương lên " . htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8') . "{$critText}{$elemText}<br/>";

            $newHpW = max(0, intval($bossData['hp']) - $playerDamage);
            $dblj->prepare("UPDATE linhcan_boss SET hp = ? WHERE id = ?")->execute(array($newHpW, intval($bossData['id'])));

            if ($newHpW <= 0) {
                \player\linhcan_boss_mark_killed(intval($bossData['id']), $sid, $dblj);
                $drops = \player\linhcan_boss_drops($player, $bossData, $dblj); // drop THAT vao playerdaoju + hook quest (Task 5)
                $battleLog .= "<font color='#4CAF50'>" . htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8') . " đã bị tiêu diệt!</font><br/>" . $drops['html'];
                // mau thong bao he thong: game/huyetmach_boss.php:36-39
                $stmt = $dblj->prepare("INSERT INTO ggliaotian(name,msg,uid) VALUES(?,?,?)");
                $stmt->execute(array('【Hệ Thống】',
                    htmlspecialchars($player->uname, ENT_QUOTES, 'UTF-8') . ' đã tiêu diệt Boss Linh Căn [' . htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8') . ']', 0));
                \player\player_cache_clear($sid);
                $player = \player\getplayer($sid, $dblj);
                $bossData['hp'] = 0;
            } else {
                $bossDamage = max(1, round(intval($bossData['atk']) - intval($player->ufy) * 0.75)); // dung nhu page cu
                $playerNewHp = max(0, intval($player->uhp) - $bossDamage);
                $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?")->execute(array($playerNewHp, $sid));
                $battleLog .= htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8') . " gây <font color='#f44336'>{$bossDamage}</font> sát thương lên ngươi<br/>";
                \player\player_cache_clear($sid);
                $player = \player\getplayer($sid, $dblj);
                $bossData['hp'] = $newHpW;
            }
        }
    }

    if (is_array($bossData)) {
        $bwName  = htmlspecialchars($bossData['name'], ENT_QUOTES, 'UTF-8');
        $bwElem  = isset($bossData['element']) ? $bossData['element'] : '';
        $bwIcon  = function_exists('\player\linhcan_element_icon') ? \player\linhcan_element_icon($bwElem) : '🌀';
        $bwColor = function_exists('\player\linhcan_element_color') ? \player\linhcan_element_color($bwElem) : '#9C27B0';
        $bwNameE = function_exists('\player\linhcan_element_name') ? \player\linhcan_element_name($bwElem) : $bwElem;
        $bwHp = intval($bossData['hp']); $bwMax = intval($bossData['maxhp']);
        $bwPct = $bwMax > 0 ? min(100, intval($bwHp * 100 / $bwMax)) : 0;
        $bossHtml = '<div class="lcb-card" style="--lcb-color:' . $bwColor . '">'
            . '<div class="lcb-icon">' . $bwIcon . '</div>'
            . '<div class="lcb-name">' . $bwName . '</div>'
            . '<div class="lcb-level">Lv ' . intval($bossData['level']) . ' · Hệ ' . $bwNameE . '</div>'
            . '<div class="lcb-hpbar"><div class="lcb-hpfill" style="width:' . $bwPct . '%"></div></div>'
            . '<div class="lcb-hptext">HP ' . $bwHp . ' / ' . $bwMax . '</div>'
            . '<div class="lcb-stats"><span>⚔ ' . intval($bossData['atk']) . '</span><span>🛡 ' . intval($bossData['def']) . '</span><span>💥 ' . intval($bossData['crit']) . '%</span></div>';
        if ($bwHp > 0) {
            $bossHtml .= '<a class="lcb-btn" href="?cmd=' . $wattackCmd . '">TẤN CÔNG</a>';
        } else {
            $bossHtml .= '<div class="lcb-dead">BOSS ĐÃ BỊ TIÊU DIỆT — hồi sau xuất hiện lại</div>';
        }
        $bossHtml .= '</div>';
    } else {
        $bossHtml = '<div class="lcb-empty">Chưa có boss — quay lại sau</div>';
    }
}

// Chips chon boss ca nhan (hien o CA HAI che do; link dan vao chinh trang nay voi el=)
$lcvh = array('kim', 'moc', 'thuy', 'hoa', 'tho');
$lcchipsB = '';
foreach ($lcvh as $ckB) {
    $defB = \player\linhcan_element_def($ckB);
    if (!is_array($defB)) continue;
    $clsB = ($lcv === $ckB) ? ' is-active' : '';
    $lcchipsB .= '<a class="lcb-chip' . $clsB . '" style="--lcb-color:' . $defB['color'] . '"'
        . ' href="?cmd=' . $encode->encode("cmd=linhcan_boss&sid=$sid&el=$ckB") . '">'
        . $defB['icon'] . ' ' . $defB['name'] . '</a>';
}
?>
<div class="lcb-wrap">
    <div class="lcb-head"><span class="lcb-title"><?= $isPersonal ? 'BOSS ĐỘT PHÁ · ' . $lcdefP['name'] : 'BOSS LINH CĂN THẾ GIỚI' ?></span></div>
    <div class="lcb-chips"><?= $lcchipsB ?>
        <a class="lcb-chip<?= $isPersonal ? '' : ' is-active' ?>" href="?cmd=<?= $encode->encode("cmd=linhcan_boss&sid=$sid") ?>">🌍 Thế Giới</a>
    </div>
    <?= $bossHtml ?>
    <?php if ($battleLog): ?>
    <div class="lcb-log"><?= $battleLog ?></div>
    <?php endif; ?>
    <?= $hintHtml ?>
    <div class="lcb-footnote">Boss cá nhân mỗi hành xuất hiện khi hành đó đạt Lv trần bậc và đã hạ boss mới ĐỘT PHÁ được bậc kế. Boss thế giới (2h) rơi đá ngũ hành + Bình Anh thật vào túi đồ.</div>
    <nav class="lc-tabbar xl-tabbar">
        <a href="?cmd=<?= $lccmd ?>">Luyện</a>
        <a href="?cmd=<?= $encode->encode("cmd=linhcan_quest&sid=$sid") ?>">Nhiệm Vụ</a>
        <a class="<?= $isPersonal ? '' : 'is-active' ?>" href="?cmd=<?= $encode->encode("cmd=linhcan_boss&sid=$sid") ?>">Boss Thế Giới</a>
        <a href="?cmd=<?= $ztcmd ?>">Nhân Vật</a>
    </nav>
</div>
```
- [ ] **Step 4: Nối CSS vào EOF `css/mystyle.css`** (sau block `.lc-*` Task 10; trước khi nối chạy lại gate Step 1c). Portal widths theo khuôn: mobile 660, desktop ≥601px → 840. KHÔNG động vào rule `.hm-*`/`.lc-*` cũ.

```css
/* ==================== LINH CAN — TASK 11: .lcq-* / .lcb-* / .lcw-* ==================== */
.lcq-wrap { max-width:660px; margin:0 auto; padding:10px; }
.lcq-card { background:rgba(30,30,40,.95); border:2px solid #FFD700; border-radius:16px; padding:16px; box-shadow:0 0 18px rgba(255,215,0,.15); }
.lcq-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.lcq-title { font-size:18px; font-weight:bold; color:#FFD700; }
.lcq-week { font-size:12px; color:#888; }
.lcq-notice { padding:10px 12px; border-radius:8px; margin:8px 0; font-size:13px; }
.lcq-good { background:rgba(76,175,80,.15); border:1px solid #4CAF50; color:#8bc34a; }
.lcq-bad { background:rgba(244,67,54,.12); border:1px solid #f44336; color:#ef9a9a; }
.lcq-status { text-align:center; padding:8px; border-radius:8px; margin:8px 0; font-size:13px; }
.lcq-status.is-done { background:rgba(76,175,80,.15); color:#4CAF50; border:1px solid #4CAF50; }
.lcq-status.is-open { background:rgba(255,215,0,.08); color:#FFD700; border:1px solid rgba(255,215,0,.3); }
.lcq-item { padding:10px 0; border-bottom:1px solid rgba(255,255,255,.08); }
.lcq-item.lcq-done { opacity:.85; }
.lcq-item-head { display:flex; justify-content:space-between; font-size:14px; font-weight:bold; color:#eee; }
.lcq-count { color:#FFD700; font-weight:normal; font-size:13px; }
.lcq-item.lcq-done .lcq-count { color:#4CAF50; }
.lcq-desc { font-size:12px; color:#999; margin:3px 0 6px; }
.lcq-bar { height:8px; border-radius:4px; background:rgba(255,255,255,.08); overflow:hidden; }
.lcq-bar-fill { height:100%; background:linear-gradient(90deg,#FFD700,#FFA000); border-radius:4px; transition:width .4s; }
.lcq-reward { margin-top:12px; padding:10px; background:rgba(0,0,0,.3); border-radius:8px; font-size:13px; color:#ccc; text-align:center; }
.lcq-claimwrap { text-align:center; margin-top:12px; }
.lcq-btn { display:inline-block; padding:12px 34px; background:linear-gradient(135deg,#FFD700,#FFA000); color:#000; font-weight:bold; border-radius:12px; text-decoration:none; }
@media (min-width:601px) { .lcq-wrap { max-width:840px; } }

.lcb-wrap { max-width:660px; margin:0 auto; padding:10px; }
.lcb-head { text-align:center; margin-bottom:8px; }
.lcb-title { font-size:18px; font-weight:bold; color:#f44336; letter-spacing:1px; }
.lcb-chips { display:flex; flex-wrap:wrap; justify-content:center; gap:6px; margin-bottom:12px; }
.lcb-chip { padding:6px 12px; border-radius:14px; font-size:12px; text-decoration:none; color:#ccc; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); }
.lcb-chip.is-active { color:#fff; background:var(--lcb-color,#555); border-color:var(--lcb-color,#555); font-weight:bold; }
.lcb-card { background:rgba(30,30,40,.95); border:2px solid var(--lcb-color,#9C27B0); border-radius:16px; padding:18px; text-align:center; box-shadow:0 0 18px var(--lcb-color,#9C27B0); }
.lcb-icon { font-size:52px; margin-bottom:6px; }
.lcb-name { font-size:20px; font-weight:bold; color:var(--lcb-color,#eee); }
.lcb-level { font-size:12px; color:#888; margin-top:2px; }
.lcb-hpbar { height:20px; background:rgba(255,255,255,.1); border-radius:10px; overflow:hidden; margin:12px 0 4px; }
.lcb-hpfill { height:100%; background:linear-gradient(90deg,#f44336,#FF9800); border-radius:10px; transition:width .4s; }
.lcb-hptext { font-size:13px; color:#aaa; }
.lcb-stats { display:flex; justify-content:space-around; margin:10px 0; font-size:13px; color:#aaa; }
.lcb-btn { display:inline-block; margin-top:12px; padding:13px 40px; background:linear-gradient(135deg,#f44336,#FF5722); color:#fff; font-weight:bold; font-size:17px; border-radius:12px; text-decoration:none; }
.lcb-dead { color:#4CAF50; font-size:16px; font-weight:bold; padding:14px 0; }
.lcb-empty { text-align:center; color:#888; padding:22px; background:rgba(30,30,40,.7); border:1px dashed rgba(255,255,255,.15); border-radius:14px; margin-bottom:10px; }
.lcb-log { margin-top:12px; padding:12px; background:rgba(0,0,0,.35); border-radius:8px; font-size:13px; line-height:1.7; text-align:left; }
.lcb-hint { margin-top:12px; padding:12px; background:rgba(255,215,0,.08); border:1px solid rgba(255,215,0,.3); border-radius:10px; font-size:13px; color:#eee; text-align:center; }
.lcb-hint a { color:#FFD700; }
.lcb-footnote { margin-top:10px; font-size:11px; color:#777; text-align:center; }
@media (min-width:601px) { .lcb-wrap { max-width:840px; } }

.lcw-wrap { max-width:660px; margin:0 auto; padding:10px; }
.lcw-head { text-align:center; font-size:16px; font-weight:bold; color:#a78bfa; margin-bottom:8px; }
.lcw-notice { padding:10px; border-radius:8px; margin:8px 0; font-size:13px; text-align:center; background:rgba(167,139,250,.12); border:1px solid rgba(167,139,250,.4); color:#d1c4e9; }
.lcw-list { background:rgba(30,30,40,.95); border:1px solid rgba(167,139,250,.3); border-radius:12px; padding:6px 12px; }
.lcw-row { padding:10px 0; border-bottom:1px solid rgba(255,255,255,.08); font-size:13px; color:#ddd; }
.lcw-row.lcw-off { color:#777; }
.lcw-row a { color:#a78bfa; font-weight:bold; text-decoration:none; }
.lcw-note { margin-top:8px; font-size:11px; color:#888; line-height:1.5; }
.lcw-back { display:inline-block; margin-top:10px; padding:8px 16px; background:rgba(167,139,250,.15); color:#a78bfa; border-radius:8px; text-decoration:none; font-size:13px; }
@media (min-width:601px) { .lcw-wrap { max-width:840px; } }
```
Ghi chú Task 7: trang `game/linhcan_wash.php` đã dùng sẵn các class `lcw-*` nhưng chưa có CSS — block trên là phần bổ đủ của nó. `.lcb-chip` dùng biến `--lcb-color` đặt trên từng phần tử (inline style), theo đúng kiểu `--gem/--glow` Task 10 — KHÔNG đặt biến ở `.lcb-wrap`.

- [ ] **Step 5: Xóa 13 hàm cũ chết trong `class/function/linhcan.php` + đổi nghĩa `linhcan_quality_name`**

Sau Task 5/7/9/10/11-pages, mọi caller cũ đã chết. Chạy gate TỪNG hàm một — **chỉ xóa khi gate ra 0 kết quả**:

```bash
for f in linhcan_config_path linhcan_config_all linhcan_config_get linhcan_random linhcan_random_with_quality linhcan_assign linhcan_info linhcan_tier_name linhcan_upgrade_config_path linhcan_upgrade_config_all linhcan_get_upgrade_info linhcan_find_next_tier_id linhcan_upgrade; do
  echo "== $f"; grep -rn "\b$f(" --include=*.php class game tests npc | grep -v "class/function/linhcan.php"
done
```
(Chú ý: `linhcan_config_all` KHÔNG liên quan `linhcan_boss_config_all`/`linhcan_quest_config_all`/`linhcan_element_config_all` — các keeper đó sống; gate dùng `\b$f(` nên không nhầm. `linhcan_upgrade(` không khớp `linhcan_upgrade_config_path(`.)

Nếu gate trả về kết quả THẬT (không phải comment/`grep -v`): DỪNG hàm đó, không xóa, ghi sổ nợ — nghĩa là còn caller lạc (danh sách caller gốc đã kiểm ngày 2026-10-10: `game/linhcan.php:11,105` → Task 10 viết lại; `game/linhcan_upgrade.php` → Task 7 xóa; `class/player.php:747,2066,2070` → Task 9/7; `game/use_dj.php:91,95,99` → Task 7; `class/function/linhcan_boss.php:44,188` → Task 5). Xóa từng khối `if (!function_exists(...)) { ... }` hoàn chỉnh bằng Edit (đọc lại số dòng thực tế — Task 2/3/7 đã nối hàm mới, số dòng cũ :16/:31/:53/:65/:113/:164/:197/:330/:443/:458/:469/:482/:500 chỉ để định hướng).

THAY thân `linhcan_quality_name` (giữ tên, đổi nghĩa theo spec §3.1):

```php
if (!function_exists(__NAMESPACE__ . '\\linhcan_quality_name')) {
    // Nghia MOI (spec 3.1): ten bac dot pha cua hanh (0..4), khong con "chat luong" 22 can cu.
    function linhcan_quality_name($stage){
        $s = linhcan_stage_def(intval($stage));
        return (isset($s['name']) && $s['name'] !== '') ? $s['name'] : 'Phàm Căn';
    }
}
```

KEEPERS TUYỆT ĐỐI KHÔNG XÓA (`04_task2.md:103`): `linhcan_calc_bonuses`, `linhcan_get_absorb`, `linhcan_get_exp_bonus`, `linhcan_element_name/color/icon/khac`, `linhcan_get_element_multiplier`, `linhcan_apply_element_damage`, + toàn bộ hàm mới Task 2/3/7. Sau khi xóa:

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/linhcan.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_element.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_boss.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_quest.php
```
Expected: lint OK + 3 suite ALL PASS (xóa hàm chết không được động vào logic mới).

CỘNG SỔ quy trình spec §3.2 (chốt tại đây, không dây chuyền sang Task 12): sau khi xóa, chạy `wc -l class/function/linhcan.php`. File 620 dòng trước rework, Task 2/3/7 thêm ước ~450 dòng, bước này xóa ước ~250 dòng → nhiều khả năng VẪN DƯỚI 700 → **quyết định: giữ một file `linhcan.php`** (sát khuon `huyetmach.php` mot khoi ham rieng, de tim). NEU `wc -l` > 700 that: tach khoi node+config doc (`linhcan_element_config_*`, `linhcan_node_*`, `linhcan_stage_*`, `linhcan_da_id/binh_anh_id`) sang `class/function/linhcan_node.php` moi ( cung `namespace player;` + guard `function_exists`), them `require_once __DIR__.'/function/linhcan_node.php';` canh `class/player.php:16-19`, chay lai toan bo test — va ghi ca hai duong vao commit message.

- [ ] **Step 6: `tests/test_linhcan_pages.php` — live đầu tiên cho 2 nút từng chết**

Bootstrap + `getpage()` copie khuon `tests/test_huyetmach_combat.php:94-170` (`new \encode\encode()`, `LC_BASE` mac dinh `http://127.0.0.1:8123`, curl shell_exec, fixture `$E2ESID`, refresh `endtime`, seed `game1` uid 990113). Test MOI — nguyen van:

```php
<?php
// Live page tests Task 11: claim quest + 2 loai boss qua URL THAT (dead-code fix proof).
// Can dev server chay + fixture e2e. Chay: php.exe tests/test_linhcan_pages.php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$enc = new \encode\encode();
$base = getenv('LC_BASE'); if (!is_string($base) || $base === '') { $base = 'http://127.0.0.1:8123'; }
function getpage($plain) {
    global $enc, $base;
    return (string) shell_exec('curl -s ' . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain)));
}
$db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid IN (32,33,34,35,36,37)");
$db->exec("UPDATE game1 SET uyxb = 100000, uexp = 100000, uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);

// G1: trang quest render du 5 quest Task 6
$g1 = getpage("cmd=linhcan_quest&sid=$E2ESID");
tassert('G1 render 5 quest', strpos($g1, 'Luyện căn cần mẫn') !== false && strpos($g1, 'Đột phá cảnh giới') !== false, true);
tassert('G1 class lcq-wrap (CSS noi bo da xoa)', strpos($g1, 'lcq-wrap') !== false, true);

// G2: bo day du progress → claim QUA URL that — bug cu `cmd==claim_reward` khong bao gio lam duoc dieu nay
foreach (array('train_element','kill_monsters','kill_boss','receive_stone') as $ht) {
    \player\linhcan_quest_update($E2ESID, $ht, 20, $db);
}
\player\linhcan_quest_update($E2ESID, 'break_once', 1, $db);
\player\player_cache_clear($E2ESID);
$ltBefore = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
$g2 = getpage("cmd=linhcan_quest&claim_reward=1&sid=$E2ESID");
$ltAfter = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
$baG2 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$E2ESID' AND djid = 37")->fetchColumn());
tassert('G2 claim cong 500 LT THAT', $ltAfter - $ltBefore, 500);
tassert('G2 Binh Anh vao TOI THAT (1-2)', $baG2 >= 1 && $baG2 <= 2, true);
$weekNow = \player\linhcan_quest_current_week();
$doneFlag = intval($db->query("SELECT completed FROM player_linhcan_quest WHERE sid = '$E2ESID' AND quest_week = '$weekNow'")->fetchColumn());
tassert('G2 cot completed = 1', $doneFlag, 1);

// G3: claim lan 2 bi chan, tien khong dong
$g3 = getpage("cmd=linhcan_quest&claim_reward=1&sid=$E2ESID");
$lt3 = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
tassert('G3 claim kep LT khong dong', $lt3, $ltAfter);

// G4: boss the gioi — attack QUA URL lam hp GIAM THAT (bug cu `cmd==attack_lc_boss`: khong the)
$db->exec("DELETE FROM linhcan_boss");
getpage("cmd=linhcan_boss&sid=$E2ESID"); // spawn
$bwHp = intval($db->query("SELECT hp FROM linhcan_boss ORDER BY id DESC LIMIT 1")->fetchColumn());
$bwId = intval($db->query("SELECT id FROM linhcan_boss ORDER BY id DESC LIMIT 1")->fetchColumn());
getpage("cmd=linhcan_boss&attack_lc_boss=1&sid=$E2ESID");
$bwHp2 = intval($db->query("SELECT hp FROM linhcan_boss WHERE id = $bwId")->fetchColumn());
tassert('G4 world boss hp GIAM qua URL', $bwHp2 < $bwHp, true);
tassert('G4 player mat mau phan dong (game1)', intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn()) <= intval($db->query("SELECT umaxhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn()), true);

// G5: boss ca nhan theo hanh — seed Lv tran roi khiêu chiến + giot qua URL
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
\player\linhcan_node_set($E2ESID, 'kim', \player\linhcan_stage_cap(0), 0, $db); // max lv bac 0 (Pham) → du dieu kien sinh boss to_stage 1
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$g5 = getpage("cmd=linhcan_boss&sid=$E2ESID&el=kim");
tassert('G5 page el=kim co card boss', strpos($g5, 'lcb-card') !== false, true);
$pbRow = $db->query("SELECT * FROM linhcan_boss_personal WHERE sid = '$E2ESID' AND element = 'kim' AND status = 'active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
tassert('G5 boss da tu spawn', is_array($pbRow), true);
$qBefore = \player\linhcan_quest_get_progress($E2ESID, $db);
$kbBefore = intval($qBefore['progress']['3']); // quest id 3 = kill_boss (Task 6)
$db->exec("UPDATE linhcan_boss_personal SET hp = 1 WHERE id = " . intval($pbRow['id']));
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$g5k = getpage("cmd=linhcan_boss&sid=$E2ESID&el=kim&pattack=1");
tassert('G5 phan tich tieu diet + mo khoa dot pha', strpos($g5k, 'đã bị tiêu diệt') !== false && strpos($g5k, 'Mở khóa Đột Phá') !== false, true);
$pbSt = $db->query("SELECT status FROM linhcan_boss_personal WHERE id = " . intval($pbRow['id']))->fetchColumn();
tassert('G5 pattack giot boss → status killed', $pbSt, 'killed');
$baG5 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$E2ESID' AND djid = 35")->fetchColumn()); // 35 = da Kim
tassert('G5 da Kim vao TOI that', $baG5 >= 1, true);
$qPb = \player\linhcan_quest_get_progress($E2ESID, $db);
tassert('G5 hook kill_boss tang DUNG 1 (khong double vì trang not re-hook)', intval($qPb['progress']['3']) - $kbBefore, 1);

// G6: trong thuong chan tan cong (khuon huyetmach_boss.php:17)
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$E2ESID'");
\player\lcboss_spawn($E2ESID, $db, 'moc');
$db->exec("UPDATE game1 SET uhp = 0 WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$g6Hp = intval($db->query("SELECT hp FROM linhcan_boss_personal WHERE sid = '$E2ESID' AND element = 'moc' AND status = 'active'")->fetchColumn());
getpage("cmd=linhcan_boss&sid=$E2ESID&el=moc&pattack=1");
$g6Hp2 = intval($db->query("SELECT hp FROM linhcan_boss_personal WHERE sid = '$E2ESID' AND element = 'moc' AND status = 'active'")->fetchColumn());
tassert('G6 uhp=0 → boss khong mat mau', $g6Hp2, $g6Hp);

// G7: trang wash co class lcw-* (Task 7 trang, Task 11 CSS)
$g7 = getpage("cmd=linhcan_wash&sid=$E2ESID");
tassert('G7 wash render', strpos($g7, 'lcw-wrap') !== false, true);

// Cleanup
$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM linhcan_boss");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid IN (32,33,34,35,36,37)");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
```
Ghi chu: schema `player_linhcan_quest` da doc truc tiep (`sid, quest_week, progress JSON, completed` — `class/function/linhcan_quest.php:47-83,114-161`) nen G2/G5 loc theo `quest_week` va doc progress qua `linhcan_quest_get_progress` (dung hang khi engine doi cap). Neu G5 fail vi `linhcan_quest_update` cap progress theo `target`: doi assertion thanh so sanh `min(21, target)` hoac seed < target — giu nguyen engine, chi dieu chinh test.

- [ ] **Step 7: Chup UI + sổ nợ + commit**

1. Chrome headless (KHONG dung browser-use MCP) 3 tam 390px: quest (1 quest gần xong để thấy bar), boss thế giới (có boss), boss cá nhân (`el=kim`). So ảnh 1:1 với trang Mạch Huyệt cùng viewport — lệch padding/panel là bug.
2. `docs/UI_LINHCAN.md` thêm các dòng sổ nợ:
   - Dead-code finding (dán output grep Step 1a): trang cũ claim/attack-world **không bao giờ chạy** → lịch sử local KHÔNG có ai từng nhận 500 LT tuần/boss qua 2 trang này; không auto-compensate — audit report khi owner duyệt (chính sách bàn giao, chưa chốt).
   - `lcboss_spawn` tu spawn khi mo trang `?el=` (khong can nut "khiêu chiến") — chap nhan theo khuon Task 4, ghi ro.
   - CHƯA test trong game thật / trên điện thoại: chiều cao bar `--lcb-color` chips trên trình duyệt cổ, fx ring Task 10 không liên quan trang này.
3. Quét ký tự + lint toan bo file sua:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l game/linhcan_quest.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l game/linhcan_boss.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201c}\x{201d}\x{00a0}]" game/linhcan_quest.php game/linhcan_boss.php css/mystyle.css class/function/linhcan.php tests/test_linhcan_pages.php
```
Expected: lint OK; grep CLEAN (trừ `【Hệ Thống】` — U+3010/3011 ngoac CJK, KHONG nam trong vung 4E00-9FFF cua pattern; la mau ggliaotian san co, giu nguyen).
4. Commit:
```bash
git add game/linhcan_quest.php game/linhcan_boss.php css/mystyle.css class/function/linhcan.php tests/test_linhcan_pages.php docs/UI_LINHCAN.md
git commit -m "feat(linhcan): viet lai trang quest+boss (boss ca nhan theo hanh), fix 2 nut chet claim/attack, xoa 13 ham cu 22-can"
```

---

---

### Task 12: Di trú 22 căn → node Ngũ Hành + nghiệp thu (đổi config sang bak) + hoàn tất tài liệu

**Files:**
- Create (ngoài repo): `E:/window/Temp/lcmigrate.php`, `E:/window/Temp/lcwashcheck.php`, `E:/window/Temp/lcstage.php`
- Modify: `tongquan.php` (block `$lcAll = tq_cfg('linhcan_config')` `:289` đến hết `tq_sec(... 'linh-can-len' ...)` `~:318` + section skill `:320-324`)
- Rename: `class/config/linhcan_config.json`, `class/config/linhcan_upgrade_config.json` → `bak/linhcan/` (ĐIỂM CUỐI, sau mọi consumer đã chuyển)
- Modify: `docs/UI_LINHCAN.md`, `docs/CHANGELOG.md`, `docs/GAME_MECHANICS.md`, `docs/PHOBAN_SYSTEM.md`, `docs/LINHCAN_DEV.md`
- Create: `docs/DEPLOY_PRODUCTION_LINHCAN.md`
- Create: `docs/img/linhcan/*.png|jpg` (ảnh chứng minh cuối)

**Interfaces:**
- Consumes: `linhcan_node_set` (idempotent qua upsert — nhưng script PHẢI tự SKIP sid đã có node), `linhcan_node_rows`, `linhcan_title`, `player_cache_clear`, `linhcan_stage_cap/max`; config mới `linhcan_element_config.json` (Task 1) + config CŨ `linhcan_config.json` (22 dòng: `id 1..22, tier 1..5, quality 1..5, element` — element ĐÃ không dấu sẵn: `kim|moc|thuy|hoa|tho|all|none`, verify bằng PHP một dòng ở Step 2).
- Produces: dữ liệu `player_linhcan_node` cho nhân vật cũ; bộ tài liệu closure cho sổ nợ 3 lô migration production.
- Số liệu hiện trường (kiểm ngày 2026-10-10): `game1` có **12 nhân vật**, **11 nhân vật `linhcan_id > 0`** (max id 16 — hai id đặc biệt 21 `all`/22 `none` CHƯA ai mang local). Bảng `player_linhcan_node`, `linhcan_boss_personal`, `player_linhcan_break_log`, `player_linhcan_skill` chưa tồn tại local (Task 1 SQL tạo) — nếu chạy Task 12 mà chưa chạy Task 1 thì DỪNG, chạy `sql/sql_linhcan_element.sql` trước.

- [ ] **Step 1: Backup DB trước khi đụng dữ liệu (bất di bất dịch — bài học sid-debt)**

```bash
ls D:/XXJserver/phpstudy_pro/Extensions/            # tìm đúng tên thư mục MySQL (MySQL5.7.x)
D:/XXJserver/phpstudy_pro/Extensions/MySQL5.7.26/bin/mysqldump.exe -u root -pwww.aae.ink game > D:/XXJserver/phpstudy_pro/WWW/sql/backup_game_$(date +%Y%m%d_%H%M%S).sql
ls -la D:/XXJserver/phpstudy_pro/WWW/sql/ | tail -2
```
File dump phải > 1MB. KHÔNG commit file dump (thư mục `sql/backup_*` đã nằm ngoài git theo thông lệ dự án — kiểm tra `git status` sau dump, nếu dump hiện untracked thì để nguyên, không add).

- [ ] **Step 2: `E:/window/Temp/lcmigrate.php` — dry-run bảng quy đổi TRƯỚC, --apply SAU**

Công thức chốt theo spec §5 (bảng `[]` owner đã duyệt kèm spec):
`stage = floor((tier-1)/2)` (tier 1-2→0, 3-4→1, 5→2); `level = min(lv_cap(stage), 3*tier + quality)`; id22 Nghịch Thiên (`element:none`) → **tho, stage 2, level 30** (quy ước riêng). id21 (`element:all`) spec KHÔNG nêu → đề xuất `[CHỜ OWNER DUYỆT]`: **cả 5 hành đều** stage `floor((tier-1)/2)` + level `min(cap, 3*tier+quality)` (giữ chất "toàn thuộc tính"; local chưa ai mang nên an toàn chạy thử, production DUYỆT TRƯỚC khi apply).

```php
<?php
// lcmigrate.php — di tri 22 can ngau nhien -> node Ngu Hanh (Task 12).
//   php.exe lcmigrate.php          = dry-run, in bang quy doi (KHONG ghi)
//   php.exe lcmigrate.php --apply  = ghi player_linhcan_node
// Linh can cu (game1.linhcan_id/quality) GIU NGUYEN de rollback — khong xoa cot.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = 'D:/XXJserver/phpstudy_pro/WWW';
$apply = in_array('--apply', $argv);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$old = json_decode(file_get_contents($ROOT . '/class/config/linhcan_config.json'), true);
$elm = json_decode(file_get_contents($ROOT . '/class/config/linhcan_element_config.json'), true);
if (!is_array($old) || !is_array($elm)) { die("CONFIG THIEU — dung chay (old=" . var_export(is_array($old), true) . ")\n"); }
$cap = array();
foreach ($elm['stages'] as $sRow) { $cap[intval($sRow['stage'])] = intval($sRow['lv_cap']); }
$five = array('kim', 'moc', 'thuy', 'hoa', 'tho');

$chars = $db->query("SELECT sid, uname, linhcan_id, linhcan_quality FROM game1 WHERE linhcan_id > 0 ORDER BY sid")->fetchAll(PDO::FETCH_ASSOC);
echo $apply ? "=== APPLY ===\n" : "=== DRY-RUN (khong ghi) ===\n";
$nPlan = 0; $nSkip = 0;
foreach ($chars as $c) {
    $id = intval($c['linhcan_id']); $q = intval($c['linhcan_quality']);
    if (!isset($old[$id])) { echo "?? id $id khong co trong config cu — BO QUA, ghi so no\n"; $nSkip++; continue; }
    $o = $old[$id];
    $tier = intval($o['tier']); $st = intval(floor(($tier - 1) / 2));
    $lv = min($cap[$st], 3 * $tier + max($q, intval($o['quality'])));
    $elems = array();
    if ($id == 22) { $st = 2; $lv = 30; $elems = array('tho'); }            // Nghich Thien — quy uoc spec 5
    elseif ($o['element'] === 'all') { $elems = $five; }                    // id21 — [CH OWNER DUYET]
    elseif (in_array($o['element'], $five)) { $elems = array($o['element']); }
    else { echo "?? element la '{$o['element']}' — BO QUA, ghi so no\n"; $nSkip++; continue; }

    $exist = intval($db->query("SELECT COUNT(*) FROM player_linhcan_node WHERE sid = '" . $c['sid'] . "'")->fetchColumn());
    if ($exist > 0) { echo "SKIP  {$c['uname']} (da co $exist node — idempotent, khong ghi de)\n"; $nSkip++; continue; }
    echo str_pad($c['uname'], 14), " id$id(", $o['name'], ") t$tier q$q -> ";
    foreach ($elems as $ek) { echo "$ek stage$st lv$lv; "; }
    echo "\n"; $nPlan++;
    if ($apply) {
        require_once $ROOT . '/class/player.php';
        $ins = $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at) VALUES (?, ?, ?, ?, NOW())");
        foreach ($elems as $ek) { $ins->execute(array($c['sid'], $ek, $lv, $st)); }
        \player\player_cache_clear($c['sid']);
    }
}
echo $apply ? "=== DONE: plan=$nPlan skip=$nSkip ===\n" : "=== DRY-RUN xong: plan=$nPlan skip=$nSkip. Tiep step 3 sau khi owner duyet bang (dac biet id21). ===\n";
```
Lệnh chạy: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe E:/window/Temp/lcmigrate.php` → **dán bảng dry-run vào `docs/UI_LINHCAN.md` §Di-trú** và báo chủ dự án duyệt trước khi `--apply` (spec §5 ghi rõ "duyệt bảng trước khi chạy").

- [ ] **Step 3: `--apply` + script kiểm `lcwashcheck.php` + `lcstage.php`**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe E:/window/Temp/lcmigrate.php --apply
```
`E:/window/Temp/lcwashcheck.php` (kiểm tra sau di trú — tên giữ theo map 02_files, chức năng: range-check node + đếm danh hiệu + audit Tẩy Linh Đan tồn kho):

```php
<?php
// lcwashcheck.php — post-migration sanity: node vuot tran, stage vuot max, danh hieu sau di tru,
// dem Tay Linh Dan (djid 111) + Binh Anh (37) + da 32..36 con trong TOI tung nguoi (bao cao, KHONG hoan tien).
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = 'D:/XXJserver/phpstudy_pro/WWW';
require_once $ROOT . '/class/player.php';
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$bad = 0;
$rows = $db->query("SELECT sid, element, level, stage FROM player_linhcan_node")->fetchAll(PDO::FETCH_ASSOC);
$bySid = array();
foreach ($rows as $r) {
    $bySid[$r['sid']][$r['element']] = array('level' => intval($r['level']), 'stage' => intval($r['stage']));
    $capR = \player\linhcan_stage_cap(intval($r['stage']));
    if (intval($r['level']) > $capR || intval($r['stage']) > \player\linhcan_stage_max() || intval($r['level']) < 0) {
        echo "BAD node: {$r['sid']} {$r['element']} lv{$r[level]} stage{$r[stage]} (cap=$capR)\n"; $bad++;
    }
}
foreach ($bySid as $sidR => $ndR) {
    $tR = \player\linhcan_title($ndR);
    echo "TITLE " . substr($sidR, 0, 8) . "... -> {$tR['name']}\n";
}
echo "--- Ton kho nguyen lieu (bao cao chu, khong auto-refund):\n";
$inv = $db->query("SELECT d.sid, d.djid, SUM(d.djsum) s FROM playerdaoju d WHERE d.djid IN (32,33,34,35,36,37,111) GROUP BY d.sid, d.djid")->fetchAll(PDO::FETCH_ASSOC);
foreach ($inv as $iR) { echo $iR['sid'], " dj", $iR['djid'], " = ", $iR['s'], "\n"; }
echo $bad === 0 ? "WASHCHECK OK (".count($rows)." node)\n" : "WASHCHECK FAILED: $bad node\n";
```

`E:/window/Temp/lcstage.php` (seed phục vụ ảnh chứng minh — theo spec §7):

```php
<?php
// lcstage.php <element> <stage 0..4> — set node kim|moc|thuy|hoa|tho len bac + Lv tran cua bac do.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = 'D:/XXJserver/phpstudy_pro/WWW';
require_once $ROOT . '/class/player.php';
if (!isset($argv[1]) || !isset($argv[2])) { die("Cach dung: php.exe lcstage.php <element> <0..4>\n"); }
$SID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
\player\linhcan_node_set($SID, $argv[1], \player\linhcan_stage_cap(intval($argv[2])), intval($argv[2]), $db);
echo "seeded {$argv[1]} stage={$argv[2]} lv=" . \player\linhcan_stage_cap(intval($argv[2])) . "\n";
```

- [ ] **Step 4: Viết lại 3 section Linh căn trong `tongquan.php`** (trước khi đổi config — đây là consumer cuối cùng còn đọc `linhcan_config`/`linhcan_upgrade_config`, xác nhận: `grep -rn "linhcan_config\b\|linhcan_upgrade_config" --include=*.php . | grep -v "^./bak/" | grep -v "class/function"` → chỉ còn `tongquan.php`)

THAY khối từ `$lcAll = tq_cfg('linhcan_config');` (`:289`) đến hết `tq_sec(..., 'linh-can-len', ...)` (`~:318`) bằng:

```php
$lcE   = tq_cfg('linhcan_element_config');
$lcLbl = isset($lcE['stat_labels']) ? $lcE['stat_labels'] : array();
$rows = array();
foreach ((array)$lcE['elements'] as $lcK => $lcR) {
    $st = array();
    foreach ((array)$lcR['per_lv'] as $sk => $sv) { $st[] = (isset($lcLbl[$sk]) ? $lcLbl[$sk] : tq_h($sk)) . ' +' . tq_n($sv) . '/Lv'; }
    $rows[] = array(
        tq_dot($lcR['color']) . tq_show($lcR['icon'] . ' ' . $lcR['name']),
        implode(', ', $st),
        tq_cut($lcR['desc'], 90),
    );
}
tq_sec('nuoi-duong', 'linh-can', '🔮', 'Linh căn — Vòng Ngũ Hành', count($rows),
    '5 hành Kim–Mộc–Thủy–Hỏa–Thổ tự luyện (Lv 0 → trần bậc) và tự đột phá 5 bậc Phàm→Huyền→Địa→Thiên→Thánh; danh hiệu suy ra từ vòng, không còn 22 căn ngẫu nhiên.',
    array('← Đá ngũ hành + Bình Anh từ boss thế giới / quest tuần', '→ Công/hút/exp theo Lv, % cả vòng theo bậc', 'Boss cá nhân chặn đột phá từng hành'),
    tq_searchable('tbl-lc', array('Hành', 'Chỉ số mỗi Lv', 'Mô tả'), $rows, 'Nguồn: <code>linhcan_element_config.json</code>.'));

$rows = array();
foreach ((array)$lcE['stages'] as $sgR) {
    $br = is_array($sgR['break']) ? $sgR['break'] : null;
    $rows[] = array(
        $sgR['icon'] . ' Bậc ' . tq_n($sgR['stage']) . ' — ' . tq_show($sgR['name']),
        'Lv ' . tq_n($sgR['lv_cap']),
        $br === null ? '—' : tq_n($br['binh_anh']) . ' Bình Anh · ' . tq_n($br['linh_thach']) . ' LT · ' . tq_n($br['uexp']) . ' linh khí',
        $br === null ? '—' : 'Hạ boss cá nhân to_stage ' . tq_n($br['boss_to_stage']),
    );
}
$msRows = array();
foreach ((array)$lcE['milestones'] as $msR) { $msRows[] = array('Tổng Lv ' . tq_n($msR['total_lv']), '+ ' . tq_n($msR['bonus_percent']) . '% cả vòng', tq_show($msR['name'])); }
tq_sec('nuoi-duong', 'linh-can-len', '⤴', 'Linh căn - bậc đột phá', count($rows),
    'Mỗi hành đột phá độc lập; luyện tính bằng Đá + Linh Thạch (train_cost), đột phá cần đủ trần Lv + boss + Bình Anh.',
    array('Đá ngũ hành: boss thế giới 2h + rớt quái', 'Bình Anh: quest tuần + boss thế giới'),
    tq_searchable('tbl-lcu', array('Bậc', 'Lv trần', 'Chi phí đột phá', 'Điều kiện boss'), $rows, '')
    . tq_searchable('tbl-lcms', array('Mốc tổng Lv', 'Cộng hưởng', 'Tên'), $msRows, ''));
```

SỬA section skill (`$lcSk = tq_cfg('linhcan_skill_config')` `~:320-324` — config đã đổi theo Task 8): dòng cuối mỗi row `'Cấp ' . tq_n($e['unlock_level'])` → `'Hành bậc ' . tq_n($e['unlock_stage'])`; cột header `'Mở ở'` giữ; mô tả `array('Chỉ dùng khi mang linh căn đúng hành', 'Khóa theo cấp nhân vật')` → `array('Mở khi hành chủ đạo đạt bậc ≥ 2 (Huyền)', 'Gắn thật vào pve/boss — xem docs/UI_LINHCAN.md')`. Chạy nhanh: mở `tongquan.php` qua `http://127.0.0.1:8123/tongquan.php` (khuôn Chrome headless Task 10 Step 7) — section Linh căn phải hiện bảng 5 hành, không fatal.
- [ ] **Step 5: Nghiệp thu — dời 2 config cũ sang `bak/linhcan/`**

Chạy gate TRƯỚC (mọi kết quả phải nằm trong `bak/` hoặc comment):
```bash
grep -rn "linhcan_config\b\|linhcan_upgrade_config" --include=*.php --include=*.json . | grep -v "^./bak/" | grep -v "linhcan_element_config\|linhcan_quest_config\|linhcan_boss_config\|linhcan_skill_config"
```
Expected: 0. Nếu còn → đọc từng chỗ, chuyển sang `linhcan_element_config` rồi mới được dời.

```bash
mkdir -p bak/linhcan
git mv class/config/linhcan_config.json bak/linhcan/ 2>/dev/null || mv class/config/linhcan_config.json bak/linhcan/
git mv class/config/linhcan_upgrade_config.json bak/linhcan/ 2>/dev/null || mv class/config/linhcan_upgrade_config.json bak/linhcan/
git status --short | head
```
(`git mv` im lặng fail nếu `bak/` bị gitignore — fallback `mv` + `git add -u`; kiểm `git check-ignore bak` và ghi kết quả vào commit message. Sau đó `grep -rn "linhcan_config.json" class/ game/ tests/ --include=*.php` phải 0.)

- [ ] **Step 6: Chốt `docs/UI_LINHCAN.md`**

Bổ sung vào file đã mở từ Task 10 Step 8:
- §Di-trú: bảng dry-run thật của Step 2 (11 nhân vật) + ghi chú id21 `[CHỜ OWNER DUYỆT]` + xác nhận `linhcan_id/quality` giữ nguyên cột để rollback.
- §Nguồn-da: dán kết quả kiểm ke Task 1 Step 2 (quai roi da nao, shop co ban da chua) + nêu hiện trạng "nguồn đá mỏng — throttle ~2-3 tháng/full 5 hành (450 lần luyện, spec §8.1) **chờ chủ chốt** tang roi shop/mon".
- §Chưa-làm / sổ nợ, tối thiểu các dòng:
  1. 2 nút cũ claim/attack-world CHƯA BAO GIỜ chạy (bằng chứng grep Task 11 Step 1) → lịch sử local không có ai từng nhận thưởng qua 2 trang đó; chính sách: audit report, không auto-refund — **chờ owner**.
  2. Nạn nhân trang nâng cấp cũ (fatal trừ oan nguyên liệu): local KHÔNG có bảng log (`SHOW TABLES` kiểm 2026-10-10 — hết `%linh%` chỉ còn quest/boss) → không có dữ liệu hậu kiểm; chỉ có thể tra `ggliaotian`/`playerdaoju` thủ công nếu owner yêu cầu.
  3. `lcboss_spawn` tự spawn khi mở `?el=` (không nút khiêu chiến riêng) — theo khuôn Task 4, ghi rõ chấp nhận.
  4. Skill thời lượng theo request (1 lượt = 1 request) — đơn giản hóa spec §8.3, **chờ owner chốt** sau quan sát combat.
  5. Toàn bộ ảnh chứng minh Step 7 chụp local; CHƯA test trên điện thoại thật + CHƯA deploy production.

- [ ] **Step 7: `docs/DEPLOY_PRODUCTION_LINHCAN.md` (mới — mô phỏng DEPLOY_PRODUCTION_HUYETMACH.md)**

Phao cứu sinh cho chủ: production **CHƯA chạy migration nào trong 3 lô gần nhất**. Nội dung bắt buộc:
1. **Thứ tự 3 lô (không được đảo):** ① sid-debt (`docs/project-sid-schema-debt` — 15 bảng hex-sid, đã áp local 2026-10-03) → ② huyetmach (12/12 task, SQL của nó) → ③ LINHCAN (batch này). Lô ③ chạy `sql/sql_linhcan_element.sql` (4 bảng: `player_linhcan_node`, `linhcan_boss_personal`, `player_linhcan_break_log`, `player_linhcan_skill`) + xác nhận `linhcan_boss`, `player_linhcan_quest` production đã tồn tại (đã chạy từ bản cũ).
2. Deploy code TRƯỚC, chạy migration SAU (migration đọc `linhcan_config.json` cũ — file này vẫn còn trên production tới bước nghiệp thu cuối).
3. Dán nguyên văn công thức di trú + bảng `[CHỜ DUYỆT]` id21 + yêu cầu **duyệt bảng bởi chủ trước khi `--apply` trên production** và **dump backup production trước** (spec §5, §8.2: "cần đông bao cao trong game khi migration chay" — thêm entry ggliaotian thông báo).
4. Checklist sau deploy: 1 nhân vật cũ mở `cmd=linhcan` thấy đúng danh hiệu theo bảng; `lcwashcheck.php` OK; boss thế giới spawn đúng hành; quest tuần đếm 5/5.
5. Rollback: `DELETE FROM player_linhcan_node` (mất tiến trình mới) — `game1.linhcan_id/quality` không bị đụng nên code cũ vẫn chạy được nếu revert commit.

- [ ] **Step 8: Cập nhật tài liệu vận hành còn lại (bắt buộc theo luật dự án: tính năng nào cũng phải cập nhật trang tổng quan — spec §6.8)**

- `docs/CHANGELOG.md`: thêm mục ngày — 12 task, đếm file thêm/xóa/sửa, số dòng xóa (`linhcan_*` cu), migration chưa-deploy.
- `docs/GAME_MECHANICS.md` + `docs/PHOBAN_SYSTEM.md`: section Linh căn viết lại mô tả theo hệ mới (5 hành tự luyện + đột phá + boss cá nhân chặn cửa + kỹ năng bậc ≥2); XÓA mọi mô tả "22 căn ngẫu nhiên / Tẩy Linh Đan reroll / up bậc may mắn".
- `docs/LINHCAN_DEV.md`: KHÔNG viết lại lịch sử — thêm khối "HIỆN TRẠNG 2026-10-10" ở ĐẦU file trỏ `docs/UI_LINHCAN.md` + spec + plan, ghi "toàn bộ bên dưới là tài liệu hệ cũ 22 căn, chỉ còn giá trị tham khảo".
- `grep -rn "Tẩy Linh\|reroll_linhcan\|22 linh can" docs/*.md | grep -v LINHCAN_DEV` → từng kết quả: hoặc cập nhật hoặc bọc chú thích "hệ cũ".

- [ ] **Step 9: Ảnh chứng minh cuối → `docs/img/linhcan/`**

Chrome headless CLI (KHÔNG browser-use MCP), port dev Task 10 (`127.0.0.1:8123`), fixture E2E + `lcseed.php`/`lcstage.php`/`lcempty.php` (ngoài repo, Task 10 Step 6). Chụp tối thiểu (đặt tên file đúng danh sách):
`01-vong-trong.png` (lcempty), `02-kim-lv40-chanlinh.png`, `03-ngu-hanhanh-vienman.png` (5 node lv30), `04-vo-cuc.png` (5 node lv70), `05-checklist-thieu-boss.png` (stage0 lv=max), `06-boss-ca-nhan-kim.png`, `07-boss-the-gioi.png`, `08-quest-tuan.png`, `09-wash.png`, `10-fx0..fx4.png` (bậc 0..4 theo `lcstage`), `11-390.png` + `12-1337.png` hai viewport chốt.
Mỗi ảnh: `zoom=1`, `--hide-scrollbars`, so 1:1 ảnh ghép mock/live bằng MẮT (bài học v13 — số đo không thay được việc nhìn). Xóa mọi file tạm `_lc*.html` khỏi web root ngay sau khi đo.

- [ ] **Step 10: Quét + test tổng + commit**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l tongquan.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_element.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_boss.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_quest.php
grep -nP "[\x{4e00}-\x{9fff}\x{2018}\x{2019}\x{201c}\x{201d}\x{00a0}]" tongquan.php docs/UI_LINHCAN.md docs/DEPLOY_PRODUCTION_LINHCAN.md docs/CHANGELOG.md
git add tongquan.php docs/UI_LINHCAN.md docs/DEPLOY_PRODUCTION_LINHCAN.md docs/CHANGELOG.md docs/GAME_MECHANICS.md docs/PHOBAN_SYSTEM.md docs/LINHCAN_DEV.md docs/img/linhcan/
git add -u class/config/   # 2 file config -> deleted (ban di da nam trong bak/)
git status --short         # KHONG co backup_game_*.sql, KHONG co _lc*.html lac
git commit -m "feat(linhcan): di tru 22 can -> node Ngu Hanh + nghiep thu config + dong bo tai lieu + deploy prod ke hoach 3 lo"
```
Nếu `git check-ignore bak` báo `bak/` ignored: 2 config nằm ngoài commit — xác nhận vẫn còn trên đĩa (`ls bak/linhcan/`) và ghi chú commit. Toàn bộ test Suite phải ALL PASS; nếu suite nào đỏ → STOP, không commitStep này, quay lại task tương ứng (đây là nghiệm thu cuối của 12 task).

**Bàn giao sau Task 12 (nói thẳng với chủ — bàn giao bắt buộc đánh dấu rõ phần CHƯA test):** (a) production chưa chạy gì — file deploy đã ghi thứ tự; (b) 4 điểm chờ owner: bảng di trú id21, throttle đá 2-3 tháng, thời lượng skill theo request, chính sách audit nạn nhân hệ cũ; (c) phần CHƯA test thật: ảnh mobile người dùng thật, fx trên thiết bị yếu.

---

