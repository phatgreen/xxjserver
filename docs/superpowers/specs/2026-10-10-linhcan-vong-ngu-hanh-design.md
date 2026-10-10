# SPEC: LINH CAN — VONG NGU HANH (2026-10-10)

> Duoc chu du an duyet chieu 2026-10-10 (brainstorm 4 cau hoi: pham vi DAI TU,
> concept VONG NGU HANH, BO 22 linh can random, giu ca 4 subsystem: quest + boss the gioi +
> boss dot pha ca nhan + skill vao combat).
> Ke hoach thuc thi se tai `docs/superpowers/plans/2026-10-10-linhcan-vong-ngu-hanh.md`.
> Khuon tai lieu: `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md`.

---

## 0. Pham vi va cac quyet dinh da chot

| Cau hoi | Tra loi da chot |
|---|---|
| Pham vi | **Dai tu he thong**, khong chi do UI. 3 route trang cu dung lai (dung lai toan bo UI), khai tu `linhcan_upgrade` |
| Concept | **Vong Ngu Hanh**: 5 ngoc Kim/Moc/Thuy/Hoa/Tho quanh tam, luyen tung hanh nhu 6 ngoc Nhuc Than |
| 22 linh can cu | **Bo han, bo random**. Moi nguoi nhu nhau tu dau (tinh than chot khoi diem trang 06/10) |
| Cau truc bac | **Moi hanh tu dot pha** (5 hanh × 5 bac Pham→Huyen→Dia→Thien→Thanh), khong co bac toan vong |
| Danh hieu (tam vong) | **Suy ra tu hinh thai 5 hanh**, khong phai vat pham rut duoc |
| Combat | Hanh co Lv cao nhat = hanh hoat dong (hoa giai → bac cao hon → thu tu co dinh Kim>Moc>Thuy>Hoa>Tho); giu nguyen khac che ±20%/−15% |
| Quest tuan | Cai bien 5 muc tieu gan he moi, **sua track du 5/5** (dang chi 2/5) |
| Boss the gioi 2h | Giu, doi thanh **nguon da hiem** (Binh Anh Tinh Thach + Tinh Thach hanh cua no) |
| Boss dot pha | **Boss ca nhan rieng tung hanh** (khuon boss Mach Huyet): muon len bac hanh nao phai thang boss hanh do |
| Skill | 5 skill hanh mo theo bac, **tich that vao combat** pve/boss (ham `linhcan_skill_*` da co, 0 caller) |
| So lieu kinh te | De xuat duoi day = **phong an**, cot `[]` la cho chu du an duyet tung o truoc khi nap |

Ba truc van dong song song (khong tron lan, hoc khuon Mach Huyet):

1. **Lv hanh** (0..tran cua bac) — luyen 100% thanh cong, chiem panel giua man.
2. **Bac hanh** (0..4) — dot pha can Lv tran + boss ca nhan + nguyen lieu hiem. Day la "tram" cua he.
3. **Tong Lv 5 hanh** — mo milestone nhan vao chi so + quyet dinh DANH HIEU o tam vong.

---

## 1. LO NGU CUNG PHAI GIU (bat buoc)

1. **`da_ngu_hanh_ids` trong `linhcan_upgrade_config.json` dang SAI NGHIEM TRONG**: no map
   `kim→1, moc→2...` nhung daoju 1 = **Cuong Hoa Thach**, 2 = Phu Luc Tan Hiet. Da that la
   **32 Hoa / 33 Thuy / 34 Moc / 35 Kim / 36 Tho / 37 Binh Anh** (`daoju_config.json:163-206`).
   → `linhcan_upgrade()` hien tru nham Cuong Hoa Thach cua nguoi choi. Phai sua bang `da_map`
   moi trong config moi va **hoi quy so da bi tru oan** (kiem `player_linhcan_log` +
   `playerdaoju`, bao cao chu du an truoc khi chay).
2. **Da ngu hanh chua bao gio duoc ROI that**: `linhcan_boss_drops()` (linhcan_boss.php:192) chi
   in chu "3 Da" khong cong vao inventory; `linhcan_quest_claim()` cung vay. Moi cho cap phai
   dung `addplayerdaoju`/UPDATE `playerdaoju` that.
3. **Hai duong dan o cung mot luc, de len nhau**: `game/use_dj.php:85` xu ly
   `reroll_linhcan` (Tay Linh Dan 111) trong khi `class/player.php:2062` xu ly `djuse=='linhcan'`
   (thong bao trung ten dan da lac choi — goi Tay Linh Dan la "To The Dan" nguoc lai). Dai tu
   xong **chi con DUNG MOT duong dan** cho he moi; xoa nhanh `reroll` khoi ca hai cho.
4. **`linhcan_boss_get_dominant_element()` doc `game1.linhcan_id`** (linhcan_boss.php:40) — cot nay
   se ngung dung. Boss the gioi phai chon hanh theo nguon moi (muc 3.4).
5. **`nguhanh_apply_passive()` doc `$player->linhcan_element`** (`class/player.php:758`): he moi
   phai ghi tiep `linhcan_element` = hanh chu dao de no Nap Hanh khong bi chet im.
6. **`linhcan_calc_bonuses()` la hop dong voi 6+ noi dung** (`player.php:712-755`,
   `xiulian.php` absorb/exp, `zhuangtai.php`, pet synergy `getchongwu`). Duoc viet lai ben trong,
   **cam doi ten/ kieu cac key dau ra**.
7. **`css/gamecss.css:122` `.main button`** (0-1-1) an gradient nut: moi nut dung selector 2 lop
   `.main .lc-btn*`. Tuong tu `a` padding + `a:hover` toan cuc o `gamecss.css:85/95/226` va
   `mystyle.css @media(max-width:760px)` `.tcg-main-panel *{max-width:100%!important}`.
8. **`sid` hex 32 ky tu** → bang moi `VARCHAR(64)` (no `project-sid-schema-debt`).
9. **PHP 5.6**: khong `??`, khong `<=>`, khong return type, `array()`. Ap dung skill `php56`.
10. **Panel chung 660px khong duoc net**; `.lc-wrap{margin:0}`; breakpoint layout `min-width:601px`;
    so do wrap desktop chan ~840px nhu hai man truoc.

---

## 2. DATA LAYER

### 2.1 `class/config/linhcan_element_config.json` (MOI — thay `linhcan_config.json`)

`linhcan_config.json` (22 loai) **ngung dung**, doi ten thanh `bak/` khi nghiem thu xong.

Cau truc: `schema`, `elements` (5 entry), `stages` (bac 0..4), `milestones`, `train_cost`,
`da_map`, `title_rules`, `stat_labels`.

**5 hanh — chi so chinh moi Lv (de xuat, cot `[]` = cho duyet):**

| id | Hanh | Mau | Stat chinh /Lv | Stat phu dac trung /Lv (tinh thang, khong theo bac) |
|---|---|---|---|---|
| kim | Kim | #C0C0C0 | `atk` +2 [] | Cang hard: cong them +0,5% `atk` moi bac [] |
| moc | Moc | #228B22 | `hp` +14 [] | Linh thu: `pet_atk` +0,4%/Lv va `pet_hp` +0,4%/Lv [] |
| thuy | Thuy | #1E90FF | `absorb_bonus` +0,18% [] | Tu luyen: them `exp_bonus` +0,08%/Lv [] |
| hoa | Hoa | #FF4500 | `crit` +0,12 [] | Bao liet: them `atk` +0,6/Lv [] |
| tho | Tho | #8B4513 | `def` +1,2 [] | Vung chai: them `hp` +6/Lv [] |

Quy uoc: chi so diem (atk/def/hp) cong thang vao `ugj/ufy/umaxhp` nhu `player.php:725-729` dang lam;
chi so % (absorb/exp/pet/crit) giuan nguyen kieu cua `linhcan_calc_bonuses()`. **Khong con nerf
vinh vien** — huong "uu/nhuoc" the hien qua viec chay du 5 hanh rat long, khong phat nguoi choi.

**5 bac hanh (de xuat `[]`):**

| Bac | Ten | Tran Lv | Boss ca nhan | Binh Anh (37) | Linh Thach | Linh khi (uexp) |
|---|---|---|---|---|---|---|
| 0 | Pham Can | 15 | — | — | — | — |
| 1 | Huyen Can | 30 | "Kim Linh Toc Truong" bac 0 | 1 [] | 5.000 [] | 0 |
| 2 | Dia Can | 50 | "Huyen Long" bac 1 | 2 [] | 20.000 [] | 5.000 [] |
| 3 | Thien Can | 70 | boss bac 2 | 4 [] | 80.000 [] | 20.000 [] |
| 4 | Thanh Can | 90 | boss bac 3 | 8 [] | 250.000 [] | 80.000 [] |

Ten boss dot pha lay theo hanh tu `linhcan_boss_config.json` (5 con `element_bosses` da co san,
bo con "Hu Khong Ma Vuong" — hanh `none` khong ton tai trong he moi).
Dieu kien dot pha: Hanh dang o tran Lv cua bac hien + thang boss ca nhan HANH DO cua bac sap
dat + du nguyen lieu. Dot pha **100% thanh cong** (khong quay may rua — bai hoc tay chan
`success_rate` 20% o he cu).

**Chi phi luyen 1 lan tai Lv L cua hanh (`train_cost`, de xuat `[]`):**
`tinh_thach = 1 + floor(L/15)` (tran 7) — dung dung `da_map[hanh]` (32..36)
`linh_thach = 100 + L*15` (tran 1.435 lan cuoi)
→ Day 1 hanh len 90 ton ~315 da + ~69k linh thach; full 5 hanh ~1.575 da + ~345k linh thach.

**Milestone tong Lv 5 hanh (de xuat `[]`, nhan vao 4 chi so diem + % cua hanh):**
60 → +5% · 150 → +10% · 270 → +15% · 400 → +20%.

**Danh hieu o tam (`title_rules`, danh sach uu tien, de xuat `[]`):**

| Uu tien | Dieu kien | Danh hieu |
|---|---|---|
| 1 | 5 hanh ≥ 70 | Vo Cuc Linh The |
| 2 | 5 hanh ≥ 30 | Ngu Hanh Vien Man |
| 3 | 1 hanh ≥ 40, 4 hanh con lai < 15 | Chan Linh Can · <hanh> |
| 4 | 2 hanh ≥ 30 | Song Nghi Linh Can |
| 5 | tong Lv < 25 | Pham Can |
| 6 | con lai | Linh Can Da Chac Loc |

### 2.2 `class/config/linhcan_boss_config.json` (SUA)

- `element_bosses`: xoa key `none`, giu 5.
- `drops`: `binh_anh_min/max` 1/2 [], `tinh_thach_min/max` 2/4 [], `linh_thach_min/max` 100/500
  (giu). **Drop phai vao inventory that** (lo ngi muc 1.2).
- Them `world_element_pick`: `random_of_5` (khong doc `game1.linhcan_id` nua — lo ngi 1.4).

### 2.3 `class/config/linhcan_skill_config.json` (SUA nho)

Xoa `hu_khong_chuong` + map `none`. 5 skill con giu nguyen so hieu cu (150% AOE, 8% heal/turn,
+25% hut mau 3 turn, +40% bao 1 turn, +30% phong 3 turn; CD 5 phut; "MP" = 10% max HP nhu ham cu).
Doi `unlock_level` → `unlock_stage: 2` (mo khi hanh dat Dia Can) — nhat quan voi khuon bac.

### 2.4 Schema — `sql/sql_linhcan_element.sql` (MOI, MySQL cu, sid VARCHAR(64))

```sql
CREATE TABLE IF NOT EXISTS player_linhcan_node (
  sid        VARCHAR(64) NOT NULL,
  element    VARCHAR(10) NOT NULL,
  level      INT DEFAULT 0,
  stage      TINYINT DEFAULT 0,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (sid, element)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS linhcan_boss_personal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid VARCHAR(64) NOT NULL DEFAULT '',
  element VARCHAR(10) NOT NULL,
  to_stage TINYINT NOT NULL,
  name VARCHAR(100) NOT NULL,
  level INT DEFAULT 1, hp INT DEFAULT 0, maxhp INT DEFAULT 0,
  atk INT DEFAULT 0, def INT DEFAULT 0, crit INT DEFAULT 0,
  spawned_at DATETIME DEFAULT NULL, killed_at DATETIME DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'active',
  INDEX idx_sid (sid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS player_linhcan_break_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid VARCHAR(64) NOT NULL,
  element VARCHAR(10) NOT NULL,
  from_stage TINYINT NOT NULL, to_stage TINYINT NOT NULL,
  created_at DATETIME DEFAULT NULL,
  INDEX idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

`player_linhcan_quest`, `player_linhcan_skill`, `player_linhcan_log`, `linhcan_boss` (the gioi)
giu nguyen. `game1.linhcan_id/linhcan_quality`: **khong xoa cot**, chi ngung doc/ghi tu day
(tranh pha backup va cac trang khac con tham chieu — kiem lai `grep linhcan_id` truoc khi khai tu).

### 2.5 Vat pham

- `da_map` moi: `kim→35, moc→34, thuy→33, hoa→32, tho→36` (luu y dao nguoc so voi ten — djem ky).
- **Tay Linh Dan (111) doi vai tro**: `use_effect.type` `reroll_linhcan` → `linhcan_wash`: chon 1
  hanh, reset hanh do ve Lv 0 (giu bac), hoan 80% so da + 100% linh thach da nan vao hanh ay [].
  Bo gioi han "2 lan/vien" o `player.php:2039` cho rieng dan nay (no la dan reset, dung bao nhieu
  lan tuy ton kho).
- Nguon da 32..36 hien tai: kiem tra `guaiwu_config.json` roi do — bo sung nguon: quest tuan,
  boss the gioi, shop (gia `[]` cho chu du an chot; de xuat 200 linh thach/vien nhu docs cu).

---

## 3. FUNCTION LAYER

### 3.1 `class/function/linhcan.php` (VIET LAI BEN TRONG — giu hop dong dau ra)

| Ham | Trach nhiem |
|---|---|
| `linhcan_element_config_all()` | static cache JSON 2.1 |
| `linhcan_node_rows($sid,$dblj)` | 5 dong element→(lv,stage), thieu thi coi nhu 0/0 (man song du chua bao gio mo he — lo ngi Nhuc Than) |
| `linhcan_node_set($sid,$element,$lv,$stage,$dblj)` | upsert + `player_cache_clear()` |
| `linhcan_train_cost($lv)` | `train_cost` 2.1 |
| `linhcan_train($sid,$dblj,$element)` | +1 Lv, 100% thanh cong, tru da + linh thach, tran = `stage_cap` |
| `linhcan_stage_cap($stage)` / `linhcan_stage_def($stage)` | tra bang 2.1 |
| `linhcan_break_requirements($sid,$dblj,$element)` | danh sach `ok/text` (Lv tran, boss, Binh Anh, LT, uexp) de render checklist |
| `linhcan_break($sid,$dblj,$element)` | doi chieu checklist + `linhcan_boss_personal` killed → tang stage, ghi `player_linhcan_break_log` |
| `linhcan_dominant($nodes)` | hanh hoat dong: Lv cao nhat → stage cao nhat → thu tu co dinh |
| `linhcan_title($nodes)` | theo `title_rules` |
| `linhcan_calc_bonuses($sid,$dblj)` | **giu nguyen ten + day du key cu** (stats/absorb/exp/pet/penalty_*), tinh tu node + milestone; cac key `penalty_*` tra 0 (he moi khong nerf) |
| `linhcan_get_absorb` / `linhcan_get_exp_bonus` / `linhcan_element_khac` / `linhcan_get_element_multiplier` / `linhcan_element_name/color/icon` | giu nguyen chu ky; `element` lay tu `linhcan_dominant()` |

Xoa/bo dung: `linhcan_random*`, `linhcan_assign` (duong dan o `player.php:2062` +
`use_dj.php:85` cung bo — lo ngi 1.3), `linhcan_config_get/all`, `linhcan_find_next_tier_id`,
`linhcan_upgrade()` cu, `linhcan_get_upgrade_info`. `linhcan_quality_name` chuyen nghia:
ten stage hanh (Pham/Huyen/Dia/Thien/Thanh).

### 3.2 `class/function/linhcan_node.php` (MOI, neu `linhcan.php` vuot ~700 dong)

Tam doc quyet dinh ghe vao `linhcan.php` cho gan khuon `huyetmach_vessel_*` (tung khoi ham rieng
de sau nay tim nhanh); neu file dai qua thi tach — Task trong plan se quyet.

### 3.3 `class/function/linhcan_quest.php` (SUA)

- Quest moi (5/5 co hook that): **luyen hanh 10 lan** (hook trong `linhcan_train`),
  **dot pha 1 lan** (hook trong `linhcan_break`), **diet quai 10** (hook san co `pve.php:352`),
  **diet boss 1** (hook san co `boss.php:342` + them khi diet boss the gioi/ca nhan),
  **nhan 5 da ngu hanh** (hook trong ham cap da tap trung `linhcan_give_stone()`).
- `linhcan_quest_claim()`: cap Linh Thach + **Binh Anh Tinh Thach 1-2** qua inventory that.

### 3.4 `class/function/linhcan_boss.php` (SUA)

- Boss the gioi: `element` = random 5 hanh; `drops` cong **that** vao `playerdaoju`
  (Binh Anh 1-2 + da hanh cua no 2-4 + LT). Sua `linhcan_boss_get_dominant_element` → xoa, thay
  `linhcan_boss_world_element()`.
- Boss ca nhan (khuon `huyetmach_boss_*`): `lcboss_check_spawn($sid,$element,$toStage,$dblj)`,
  `lcboss_spawn`, `lcboss_calc_damage` (dung `linhcan_dominant` cho khac che), `lcboss_drops`
  (1-2 da hanh do []), `lcboss_mark_killed` — giu `to_stage` de `linhcan_break` doi chieu.
  HP/ATK: `hp = 8000 + level*220`, `atk = 260 + level*7` [] — **doi chieu `linhcan_boss` dang
  chay truoc khi khoa so** (dung lo ngi nhu huyetmach spec muc 8.3).

### 3.5 `class/function/linhcan_skill.php` ( NOI COMBAT)

`linhcan_skill_can_use` doi dieu kien: stage hanh ≥ 2 (khong can `ulv`).
`game/pve.php` + `game/boss.php`: them nut "Ky Nang Linh Can" khi hanh chu dao co skill;
kich hoat → `linhcan_skill_apply` + `record_use`; buff theo turn phai **tu khuyen sau
`duration_turns`** (them mang `lc_buffs` vao player tam thoi, khong luu DB).
Ky nang Hu Khong (element none) xoa theo 2.3.

---

## 4. UI LAYER — `game/linhcan.php` (dung lai, prefix `lc-`)

Cau truc 2 card / 3 dai giong `.nb-*`/`.hm-*` de ba man cung ngon ngu:

```
.lc-wrap (class lc-fx<maxStage>, --lc-tier, --lc-color)
├─ .lc-card.lc-card-ring
│   ├─ .lc-head: .lc-title "LINH CAN" + .lc-badge (danh hieu · tong Lv)
│   ├─ .lc-rail: 5 .lc-stage-chip (bac hanh, is-done/is-cur/is-lock/is-view)
│   ├─ .lc-viewline: "<hanh dang xem> · bac X — Lv a/tran — skill ..."
│   ├─ .lc-forge-wrap
│   │   ├─ .lc-stage: img vong ngu hanh + 5 a.lc-gem.slot-<pentagon> + .lc-core (danh hieu)
│   │   └─ .lc-forge: ten hanh · Lv x/tran · chi phi · nut LUYEN <HANH> · nut DOT PHA
│   └─ nav.lc-tabbar: Luyen · Nhiem Vu · Boss The Gioi · Nhan Vat   (.xl-tabbar 80px)
├─ .lc-card.lc-cols (2 cot ≥601px)
│   ├─ Cot trai: "Thuoc Tinh Kich Hoat" — 5 hanh × (Lv, stat dong gop) + .lc-ms milestone
│   └─ Cot phai: "Dot Pha <hanh>" .lc-reqs [Du/Thieu] + link boss ca nhan + skill hanh
└─ (het card)
```

- **Toa do 5 ngoc = ngu giac deu** tren anh vong tron (Kim dinh, Moc/Hoa phai, Tho/Thuy trai —
  thu tu thuan chieu). Do truc that cua buc anh, khong lay 50% (bai hoc v13 Nhuc Than).
- Anh vong ngu hanh: thu tim `F:\TuTienPhucMa` / `E:\natramadongh5` / `F:\MuH5` theo
  `reference-source-game-assets`; khong co → ImageGen nen #0f173e, 663×467 nhu `nhuc-than.jpg`,
  5 ngai sang mau hanh ro rang. `images/linhcan/vong-ngu-hanh.jpg`, bump `lcImgVer`.
- `game/linhcan_upgrade.php`: **xoa file + xoa route** (dot pha nam trong trang chinh).
  `game/linhcan_quest.php` + `game/linhcan_boss.php`: dung lai theo khuon `.lcq-`/`.lcb-` moi,
  cung panel 660 + tab bar; boss the gioi co them nut vao "Boss ca nhan <hanh>" khi `.lc-reqs`
  can — boss ca nhan mo trong modal/`game/linhcan_boss.php?element=` (quyet chi tiet o plan).
- `game/zhuangtai.php`: dong "Linh Can" hien **danh hieu + hanh chu dao + bac cao nhat**
  (giu URL `cmd=linhcan`).
- `game.php`: giu route `linhcan`, `linhcan_quest`, `linhcan_boss`; xoa `linhcan_upgrade`.
- CSS `.lc-*` **duoi** khoi `.hm-*` trong `mystyle.css` (sau moi `@media`) + bump `?v=`.
- **Hieu ung phan tang `lc-fx0..fx4`** theo bac hanh cao nhat: fx0 gradient tinh + halo danh
  hieu (vẫn phai thay gi do — bai hoc fx0), fx1 nhip tho, fx2 sweep vong, fx3 + 5 duong tia mau
  hanh nhip nho, fx4 ru toan vong; **chu cau vong chi khi danh hieu = Vo Cuc Linh The**.
  Ràng buộc §12.4 `UI_THECHAT.md`: `isolation:isolate` cho blend screen; tat animation bang
  `animation:none`; `background-clip:text` dat sau shorthand; 0px hinh hoc — so do wrap khong doi.
  Mau tu data: `--lc-color` = mau hanh chu dao, `--glow = round(min(1, lv/cap)*16)` per ngoc.

---

## 5. DI TRU (nhan vat cu da co 1 trong 22 linh can)

- Duong dan: `sql/backup_game_*` moi → script `lc_migrate.php` (ngoai repo, E:/window/Temp)
  doc `game1.linhcan_id/linhcan_quality` + config cu, ghi `player_linhcan_node`.
- **Cong quy doi de xuat `[]`** (chu du an duyet bang truoc khi chay):
  `element` = hanh cua linh căn cu; `stage = floor((tier-1)/2)` (tier 1-2→0, 3-4→1, 5→2);
  `level = min(cap(stage), 3*tier + quality)`; Nghich Thien (id 22, element none) → hanh tho,
  stage 2, level 30 (quy uoc rieng — linh can dac biet nhat).
- `linhcan_id/quality` **giu nguyen trong DB** (khong xoa) de rollback; `getplayer()` ngung doc
  `linhcan_config_get` (block `player.php:746-754` thay bang doc node).
- 11 nhan vat local co thien phu random cu (chot 06/10 giu nguyen) — van di qua cong quy doi tren.
- Production: **chua bao gio chay migration nao trong 3 lan gan day** (sid-debt, huyetmach) →
  `docs/DEPLOY_PRODUCTION_LINHCAN.md` phai ghi ro thu tu chay ca 3, lan nay lam local truoc.

---

## 6. SUA BUG CU (cung lot trong dem nay)

1. `da_ngu_hanh_ids` sai → muc 1.1 (kem hoi quy da bi tru oan).
2. Quest track 2/5 → 5/5 (muc 3.3).
3. Da/Thuong boss-quest khong vao inventory → muc 1.2/3.3/3.4.
4. `linhcan_upgrade.php` hien "?" so da → man moi hien so that (file bi xoa, muc 4).
5. Typo `element_bones` trong docs cu — code thuc te doc dung ten, kiem lai khi sua 3.4.
6. `.main button` an gradient o 3 trang anh em (no `huyetmach_awakening/transform/quydoi`) —
   rieng `linhcan*` lam dung selector ngay tu dau; **khong tu sua 3 trang kia** (pham vi chot).
7. `game/use_dj.php` an hanh dong `reroll_linhcan` cu, route Tay Linh Dan sang `linhcan_wash`.
8. `tongquan.php` + `docs/PHOBAN_SYSTEM.md`/`GAME_MECHANICS.md`/`LINHCAN_DEV.md`: cap nhat mo ta
   he moi (bat buoc theo `project-tongquan-overview-page`).

---

## 7. QUY TRINH KIEM CHUNG

- `Extensions/php/php5.6.9nts/php.exe -l` moi file sua; ap dung skill `php56`.
- `tests/test_linhcan_element.php`: config kiem du 5 hanh/5 bac/da_map; train tran; break thieu
  boss bi chan; bonus keys duc day du nhu hop dong cu; title_rules tung hinh thai. Test live
  qua HTTP nhu `test_huyetmach_combat.php --live` (pve khac che + skill cooldown).
- Seed helper `E:/window/Temp/`: `lcseed.php` (node lv/stage + da 32-37), `lcempty.php` (5 node 0),
  `lcstage.php <element> <0..4>`, `lcmigtest.php` (11 nhan vat cu → bang quy doi), `lc_post.php`,
  `lc_sweep.php` (14 trang thai, port 8199).
- Harness: nhan `_nb*/_hm*` thanh `_lc*.html` (`_lcb/_lcm/_lcshot/_lcf`), do viewport
  **390/601/840/1337**, ket qua trong `<title>`, dat `zoom=1`, `--hide-scrollbars`,
  **xoa khoi web root ngay sau khi xong**; tai khoan E2E co `endtime` trong 3600s.
- Anh chung minh `docs/img/linhcan/`: 5 bac fx, 5 ngoc khoa/sang/day, checklist thieu boss,
  danh hieu doi theo hinh thai, 390px + 1337px, **doi chieu 1:1 anh mock/live** (so do khong thay
  duoc mat).
- Ban giao phai ghi ro phan nao **CHUA** test trong game that (theo `flag-untested-in-handoff`).

---

## 8. RUI RO VAN CON MO (cho chu du an chot / kiem chung sau)

1. **Du bao thoi gian**: full 5 hanh = 450 lan luyen, ~1.575 da + ~345k LT; nguon da hien rat
   mong (chua co roi that). Chap nhan throttle 2-3 thang, hay tang roi shop/mon? **Quyét định cân
   bằng — chờ anh.**
2. **Bang quy doi di tr**u muc 5: co the nguoi choi coi la "mat linh can hiem" → can dong bao
   cao trong game khi migration chay.
3. **Skill combat**: `linhcan_skill_apply` hien sua truc tiep `$player->uxx/ubj/ufy` ma khong co
   tui; phai thiet ke vong doi buff theo turn cho dung `pve.php` (moi turn 1 lan `$phurt`?) —
   kiem chung thuc te truoc khi khoa so hieu ung.
4. **Anh vong ngu hanh**: chua biet source nao co san → kha nang ImageGen + do lai toa do ngu
   giac theo truc anh that.
5. **`game1.linhcan_id` con bi doc o dau nua**: `grep -rn linhcan_id` ra `paihang`? chua kiem —
   Task 1 cua plan bat buoc liet ke het consumer truoc khi ngung doc.
