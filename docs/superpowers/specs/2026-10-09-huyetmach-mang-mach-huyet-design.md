# SPEC: HUYET MACH — MANG MACH HUYET (2026-10-09)

> Duoc chu du an duyet ngay 2026-10-09. Tai lieu thiet ke cho viec dung lai tinh nang Huyet Mach
> theo cong thuc cua Nhuc Than (`docs/UI_THECHAT.md`).
> Ke hoach thuc thi: `docs/superpowers/plans/2026-10-09-huyetmach-mang-mach.md`.

---

## 0. Pham vi va cac quyet dinh da chot

| Cau hoi | Tra loi da chot |
|---|---|
| Pham vi | **Tai cau truc full** nhu Nhuc Than: gop 3 trang ve 1 man, thiet ke lai truc nang cap, sua lien the deadlock boss va nut trang |
| Truc luyen dan | **Mang mach huyet 8 mach**, moi mach co cap rieng, moc tong cap mo thu huyet, thuc tinh nang tran |
| Phan bo chi so | 4 mach duong dung 4 chi so huyet mach cu (Cong/HP/Bao/Hut) + 4 mach am chi so moi, cap ~15%, cong vao field dang ton tai |
| Mach thu 8 | **Van May** (`uwx`), khong phai Toc Do. Ly do: game nay khong co turn-order, `usex` la gioi tinh; lam Toc Do thi phai them cot va sua thu tu ra don o pve/pvp/boss |
| Boss thuc tinh | **Boss mach huyet rieng cho tung nguoi**, theo khuon `game/linhcan_boss.php` (bang spawn + config + tu spawn khi vao man) |
| Chi phi | Luyen mach = **Huyet Thach** (vat pham moi, roi tu boss mach huyet va nhiem vu, khong ban shop) + Linh Thach |

Ba truc van dong song song, khong duoc tron lan:

1. **Cap mach** (8 mach, moi mach mot so cap) — luyen dan, chiem panel giua man.
2. **Tran cap mach** — nang theo **stage thuc tinh** (0..4). Day la "tram" cua he.
3. **Pham chat** (1..10) — nang qua `game/huyetmach_upgrade.php` (khuon `thechat_upgrade`), nhan vao stats.

---

## 1. LO NGU CUNG PHAI GIU (bat buoc)

1. **`game1.huyetmach_awaken_stage` la cua rieng Huyet Mach.** Khong bao gio dong nhat hay doc
   `thechat_awaken_stage` — Nhuc Than dang dung cot do cho bac Cot Cach, noi chum hai he la pha bac
   Cot Cach cua nguoi choi.
2. **Man phai song khi chua co Huyet Mach** (`huyetmach_id = 0`). `huyetmach_info()` tra `false`
   va `huyetmach_calc_bonuses()` tra mang doi tuong rong (`class/function/huyetmach.php:206-226`),
   nen khong duoc lay pham chat/stage tu doi tuong rong do.
3. **`css/gamecss.css:122` `.main button{background:#fff; ...; color:#0093E8}`** (dac cach 0-1-1)
   an mat gradient cua `.tc-btn*` tren `huyetmach_transform.php`. Phai sua bang `.main .hm-btn*`
   nhu da lam voi `.main .tc-btn*` trong `thechat_upgrade.php`.
4. **Bug co san `class/function/huyetmach.php:520` va `:544-547`**: `huyetmach_activate_transform()`
   tru chi phi HP vao **`uxx` (Hut Máu)** chu khong phai `uhp`, va `huyetmach_can_transform()` cung
   doi `uxx >= $hpCost`. Mo Bien Than = **mat Hut Máu vin vien**, HP khong he suy suyeng. Phai doi
   sang `uhp` (xem muc 6.4).
5. **Dac cach CSS cua repo**: `css/gamecss.css:85/95/226` cho `<a>` padding + `a:hover{background:#0093e8}`
   toan cuc; `css/mystyle.css` `@media(max-width:760px)` co
   `.tcg-main-panel *{max-width:100%!important}` va `.tcg-main-panel img{height:auto!important}`
   (0-1-1, thang rule mot lop). Moi thanh dinh vi tuyet doi phai reset padding/margin/background o
   moi state va dung `!important`.
6. **`sid` la hex 32 ky tu** → moi bang/cot moi phai la `VARCHAR(64)` (no `project-sid-schema-debt`).
7. **PHP 5.6**: khong `??`, khong `<=>`, khong return type, dung `array()`. Ap dung skill `php56`.
8. **Khong net `.tcg-main-panel`** (van 660px chung); `.hm-wrap` desktop 840px nhu Nhuc Than.

---

## 2. DATA LAYER

### 2.1 `class/config/huyetmach_vessel_config.json` (MOI)

Cau truc: `schema`, `vessels` (8 entry), `tiers` (stage 0..4), `milestones`, `refine_cost`,
`stat_labels`, `element`. Moi vessel: `id, name, short, stat, per_lv, slot, group, desc`.
Moi tier: `stage, name, lv_cap, total_lv_require, realm_require, realm_min_level, boss_require,
material_id, material_amount, linh_thach_cost, color, icon`.

**8 mach — toa do slot theo buc anh kinh mach (that lung + 2 tay + 2 chan):**

| id | Ten mach | Ngan | Stat | `per_lv` | slot | Nhom |
|---|---|---|---|---|---|---|
| 1 | Doc Mach | Doc | `atk` | 3 | `spine-top` | Duong |
| 2 | Nhan Mach | Nhan | `hp` | 24 | `spine-bottom` | Duong |
| 3 | Sung Mach | Sung | `crit` | 0.2 | `chest-right` | Duong |
| 4 | Dai Mach | Dai | `lifesteal` | 0.4 | `waist` | Duong |
| 5 | Tay Mach Trai | Tay trái | `dodge` | 0.25 | `arm-left` | Am |
| 6 | Tay Mach Phai | Tay phai | `pen` | 0.25 | `arm-right` | Am |
| 7 | Chan Mach Trai | Chan trái | `reduce` | 0.25 | `leg-left` | Am |
| 8 | Chan Mach Phai | Chan phai | `luck` | 0.25 | `leg-right` | Am |

`per_lv` bon mach duong **thap hon** Nhuc Than mot nhat (Doc 3 vs Can Cot 3; Nhan 24 vs Co Nhuc 20)
vi huyet mach da duoc nhan bon phat `base_stats × pham chat × canh gioi × thuc tinh` trong
`huyetmach_calc_bonuses()`. Bon mach am la **percent**, cap **15.0%**.

**Tran cap theo stage va dieu kien thuc tinh:**

| Stage | Ten | Tran/mach | Tong cap can (sang stage ke) | Canh gioi | Boss | Vat lieu |
|---|---|---|---|---|---|---|
| 0 | Vinh Vien Tram Menh | 8 | 32 | Truc Co (100) | — | — |
| 1 | So Giac | 16 | 80 | Kim Dan (150) | stage_2 | — |
| 2 | Thuc Tinh | 24 | 152 | Nguyen Anh (200) | stage_3 | — |
| 3 | Chan Than | 36 | 240 | Dai Thua (280) | stage_4 | 1 × Huyet Mach Dan (113) |
| 4 | Huyet Thong Vien Man | 48 | — | — | — | — |

`total_lv_require` cua stage ke tiep = 8 × tran cap stage hien tai, cong them dam dao
(32 → 80 → 152 → 240; tong toi da khi day 8 mach o tran 48 = 384).
Milestone tong cap: 40 → +5%, 88 → +10%, 160 → +15%, 248 → +20%, 340 → +25% (nhan vao bon chi so
duong cua mach, lay guong `thechat_body` milestone).

**Chi phi luyen mot lan** (`refine_cost`): `huyet_thach = 1 + floor(lv/8)` (tai tran 48 → 7),
`linh_thach = 200 + lv*25`. Ty le thanh cong **100%** — khong random hoa, theo `thechat_body_refine()`.

### 2.2 `class/config/huyetmach_upgrade_config.json` (MOI)

9 bang `1 → 10` theo dung khuon nang pham cua Nhuc Than (`thechat_upgrade`): `material_id` (Huyet Thach 112 va da pham cap
101..108), `material_amount`, `linh_thach_cost`, `success_rate` (95/90/85/75/65/55/45/35/25),
`to_name`, `unlock_realm_min_level`. That bai mat vat lieu, khong giam pham, ghi
`player_huyetmach_upgrade_log`.

### 2.3 `class/config/huyetmach_boss_config.json` (MOI)

Sinh tu `boss_list` trong `huyetmach_awakening_config.json` (33 con = 11 huyet mach × stage 2, 3, 4).
Moi con: `cfg_key = "<bloodline_key>|stage_<n>"`, `bloodline_key`, `stage`, `name`, `level`,
`element`, `icon`, `color`, `drops.huyet_thach`. HP/ATK tinh theo level:
`hp = 8000 + level*220`, `atk = 260 + level*7` — **phai doi chieu so voi `linhcan_boss` dang chay
truoc khi khoa so**. Sau khi config nay song, xoa `boss_list` khoi `huyetmach_awakening_config.json`
de chi con MOT nguon master.

### 2.4 Schema — `sql/sql_huyetmach_vessel.sql` (MOI, MySQL 5.5, khong `DEFAULT CURRENT_TIMESTAMP`)

```sql
CREATE TABLE player_huyetmach_vessel (
  sid        VARCHAR(64) NOT NULL,
  vessel_id  TINYINT     NOT NULL,
  level      INT         DEFAULT 0,
  updated_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, vessel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE huyetmach_boss (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid VARCHAR(64) NOT NULL DEFAULT '',
  cfg_key VARCHAR(64) NOT NULL,
  name VARCHAR(100) NOT NULL, element VARCHAR(20) NOT NULL,
  level INT DEFAULT 1, hp INT DEFAULT 0, maxhp INT DEFAULT 0,
  atk INT DEFAULT 0, def INT DEFAULT 0, crit INT DEFAULT 0,
  spawned_at DATETIME DEFAULT NULL, killed_at DATETIME DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'active',
  INDEX idx_sid (sid), INDEX idx_key (cfg_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE player_huyetmach_upgrade_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sid VARCHAR(64) NOT NULL,
  from_quality INT NOT NULL, to_quality INT NOT NULL,
  success TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT NULL,
  INDEX idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

`player_huyetmach_boss` (da ton tai, khoa (sid, boss_id)) tiep tuc la **trang thai vin vien**:
`boss_id` = ten boss trong config. Bang `huyetmach_boss` chi la xac boss hien tai cua tung nguoi.
Chay local truoc; **production chua chay** — phai co file deploy rieng nhu
`docs/DEPLOY_PRODUCTION_NGUHANH.md`.

### 2.5 Vat pham — `class/config/daoju_config.json`

- `112` **Huyet Thach** — `djyxb` 800, khong co `use_effect` (chi vat lieu tieu hao).
- `113` **Huyet Mach Dan** — vat lieu dot pha stage 4, cung khong co `use_effect`.
- ID 110/111 **da lap** (Tố Thể Đan / Tẩy Linh Đan), vi vay dung 112/113.
- `game/use_dj.php` phai an nut "Su dung" cho vat pham khong co `use_effect`, neu khong tu nhien
  hien nut bam vo nghia (kiem tra hien trang 100→108 dang xu ly the nao).
- Nguon Huyet Thach: boss mach huyet (8–14/cai), them moc "luyen mach" vao nhiem vu
  (`sql/nhiemvu_quests.sql`), su kien. **Khong them vao shop.**

---

## 3. FUNCTION LAYER

### 3.1 Them vao `class/function/huyetmach.php` (khoi `huyetmach_vessel_*`)

| Ham | Trach nhiem |
|---|---|
| `huyetmach_vessel_config_all()` | static cache, doc `class/config/huyetmach_vessel_config.json` |
| `huyetmach_vessel_defs()` | 8 entry |
| `huyetmach_vessel_tier($stage)` | row tier hoac false |
| `huyetmach_vessel_cap($stage)` | tran cap moi mach cua stage |
| `huyetmach_vessel_levels($sid,$dblj)` | array id→lv, mac dinh 0, khong nem loi ra ngoai |
| `huyetmach_vessel_total($levels)` | tong cap |
| `huyetmach_vessel_milestone($total)` | ten + percent dang dat + moc ke tiep |
| `huyetmach_vessel_refine_cost($lv)` | Huyet Thach + Linh Thach |
| `huyetmach_vessel_daoju_have($sid,$dblj,$djid)` | dem `playerdaoju.djsum` |
| `huyetmach_vessel_refine($sid,$dblj,$vesselId)` | +1 cap, thanh cong 100%, tru vat lieu, `player_cache_clear()` |
| `huyetmach_vessel_bonuses($sid,$dblj)` | 8 chi so + `total_lv`, `cap`, `stage`, `milestone_*` |
| `huyetmach_vessel_break_rows($sid,$dblj,$nextTier)` | danh sach `ok/text` de render checklist |
| `huyetmach_vessel_stat_label($stat)` | nhan tieng Viet cua chi so |

### 3.2 Sua ham cu

- `huyetmach_calc_bonuses()`: nhan `milestone_percent` vao 4 chi so duong; them key
  `vessel_stats` gom `dodge/pen/reduce/luck` da cap 15; giu `can_transform` nguon stage nhu hien tai.
- `huyetmach_awaken()`: bo qua cach kiem tra roi rac hien tai, goi `huyetmach_vessel_break_rows()`
  lam cua duyet — tong cap + canh gioi + vat lieu + **boss dung theo
  `huyetmach_get_key_by_id($currentId)`**; bo han `quest_require` (chua tung co ai ghi progress —
  `huyetmach_quest_update()` co 0 caller).

### 3.3 `class/function/huyetmach_boss.php` (MOI, khuon `linhcan_boss.php`)

`huyetmach_boss_config_all()`, `huyetmach_boss_required($sid,$dblj)` (con thieu con nao theo stage
ke), `huyetmach_boss_check_spawn($sid,$dblj)`, `huyetmach_boss_spawn($sid,$dblj,$cfgKey)`,
`huyetmach_boss_calc_damage($player,$bossRow,$dblj)`, `huyetmach_boss_drops($player,$bossRow,$dblj)`,
`huyetmach_boss_mark_killed($sid,$bossName,$dblj)` — day chinh la `huyetmach_boss_mark()`
(`class/function/huyetmach.php:321`, dang 0 caller) duoc noi lai.

### 3.4 `class/function/huyetmach.php` — pham chat

`huyetmach_upgrade_config_all()`, `huyetmach_get_upgrade_info($quality)`,
`huyetmach_upgrade($sid,$dblj)` (tru vat lieu, quay `success_rate`, ghi log, `player_cache_clear()`).

---

## 4. UI LAYER — `game/huyetmach.php` (dung lai, prefix `hm-`)

Cau truc 2 card / 3 dai, **giong het** `.nb-*` cua Nhuc Than de hai man cung ngon ngu thiét ke:

```
.hm-wrap  (class hm-fx<stage>, --hm-tier, --hm-color)
├─ .hm-card.hm-card-body
│   ├─ .hm-head: .hm-title "HUYET MACH" + .hm-badge (pham chat · tran cap)
│   ├─ .hm-rail: 5 .hm-tier (stage 0..4, is-done/is-cur/is-lock/is-view) + 4 .hm-link
│   ├─ .hm-viewline: "<ten huyet mach> · <stage dang xem> — dang tu / can ..."
│   ├─ .hm-forge-wrap
│   │   ├─ .hm-stage: img.hm-figure + span.hm-aura + span.hm-sweep + 8 a.hm-vessel.slot-*
│   │   └─ .hm-forge: ten mach · cap x/tran · chi phi · nut Luyen Hoa
├─ .hm-card.hm-cols  (2 cot tren desktop >=601px)
│   ├─ Cot trai: "Thuoc Tinh Kich Hoat" 8 dong + .hm-ms (moc tong cap + bar)
│   └─ Cot phai: "Thuc Tinh Huyet Mach" .hm-gate (stage → stage) + .hm-reqs [Đủ/Thiếu]
│                + nut Thuc Tinh  +  .hm-transform (tom tat Bien Than, chi hien khi stage >= 3)
└─ nav.hm-tabbar: Nang Pham (neu co huyet mach) · Boss Mach Huyet (neu con thieu boss) · Nhan Vat
```

- Routes trong `game.php`: giu `'huyetmach'`; **xoa** `'huyetmach_awakening'`; giu
  `'huyetmach_transform'`; them `'huyetmach_boss'` va `'huyetmach_upgrade'`.
- Xoa file `game/huyetmach_awakening.php` (man moi da bao trum toan bo).
- `game/zhuangtai.php:371` giu nguyen URL tab "Huyet Mach".
- CSS: them khoi `.hm-*` **duoi** khoi `.nb-*` trong `css/mystyle.css` (sau moi `@media`),
  va bump `?v=` cua `mystyle.css` (lam theo `game/thechat.php:9` — `nbImgVer`).
- Lua chon anh (thu tu uu tien, theo `reference-source-game-assets`):
  1. Tim "kinh mach do"/meridian body trong `F:\TuTienPhucMa\...\assets`,
     `E:\natramadongh5\...\assets`, `F:\MuH5\www\entry`.
  2. Khong co → ImageGen: nhan vat ngoi toi, anh doc 663×467 nhu `nhuc-than.jpg`, nen lay mau dung
     cua trang (#0f173e), **thay ro that lung + 2 tay + 2 chan** de 8 ngọc dat dung giai phau.
  3. `images/huyetmach/kinh-mach.jpg` + `ngoc-<stat>-{khoa,sang,day}.png` (8 × 3), bump `hmImgVer`.
     Do truc that cua buc anh (bai hoc v13 cua Nhuc Than: khong lay 50% lam chuan).
- **Hieu ung phan tang `hm-fx0..fx4`** theo stage (5 bac): fx0 gradient tinh nhat + halo badge
  (khong duoc bang "khong co gi"), fx1 nhip tho, fx2 sweep, fx3 + mach mau nhip nho, fx4 ru nhat;
  chu cau vong chi dung cho **Dao Pham** (pham chat 10).
  Ràng buộc ky thuat (nguon `docs/UI_THECHAT.md` §12.4): `mix-blend-mode:screen` can
  `isolation:isolate`; dong animation phai `animation:none` tuong minh, khong dung `--dur:0s`;
  `background-clip:text` dat SAU shorthand `background:`, emoji phai nam ngoai phan tu clip;
  hieu ung chi dung shadow/pseudo/opacity/transform — **0px hinh hoc**, so do `.hm-wrap` khong duoc doi.
  Mau luon lay tu data: `--hm-tier`, `--hm-color`, `--ring`/`--glow` per ngọc
  (`--glow = round(min(1, lv/cap)*16)`).

---

## 5. COMBAT INTEGRATION (4 chi so moi, cap 15%)

| Stat | Ap vao | Ghi chu |
|---|---|---|
| `dodge` | cong vao `tfsb` trong `getplayer()` | da co co che "Né tránh" tai `game/boss.php:291` |
| `reduce` | cong vao `ufy` (Phong Thu) | giam vat ly = phong thu, khong them cong thuc |
| `pen` | bien phu `->hmPen` (% xuyen `def` doi phuong) | **khong luu DB**; ap vao cong thuc sat thuong o `boss.php`, `pve.php` (`$guaiwuFyForDamage`), `pvp.php` |
| `luck` | cong vao `uwx` va `huyetmach_passive_drop` | dung trong `huyetmach_boss_drops()` va roi do |

Trien khai **tang dou**: (a) `class/player.php` (sau dong 839) cong `dodge/reduce/luck` vao field;
(b) ap `pen` vao cong thuc sat thuong. Neu (b) lam sat thuong trung binh tang > 8% tren tai khoan
test thi dung o (a), tooltip ghi ro "chưa áp vào đòn đánh", va **bao cao chu du an** — khong tu
y thu bac bo.

**Trang thai ban giao phai ghi ro phan nao CHUA test trong game that.**

---

## 6. SUA BUG CU (cung lot trong dem nay)

1. Deadlock thuc tinh stage 2+ (muc 3.2).
2. `.main button` an gradient nut o `game/huyetmach_transform.php` (muc 1.3).
3. Nut POST o man moi phai co `.is-off` + `disabled` khi thieu dieu kien (dang nay `huyetmach_awakening.php`
   chi `pointer-events:none` bang CSS inline).
4. `class/function/huyetmach.php:520` va `:544-547`: doi `uxx` → `uhp` cho chi phi Bien Than (muc 1.4).
5. Cap nhat `tongquan.php` (them muc "Mang mach huyet") va `docs/PHOBAN_SYSTEM.md`/`GAME_MECHANICS.md`
   — bat buoc theo `project-tongquan-overview-page`.

---

## 7. QUY TRINH KIEM CHUNG

- `Extensions/php/php5.6.9nts/php.exe -l` cho moi file sua; ap dung skill `php56`.
- Seed helper ngoai repo, `E:/window/Temp/`: `hmseed.php` (cap mach + stage + Huyet Thach),
  `hmempty.php` (tai khoan trang `huyetmach_id = 0`), `hmstage.php <0..4>`, `hmpen.php <1..10>`,
  `hm_post.php` (form auto-POST), `hm_break.php`, `hm_sweep.php` (14 trang thai, port 8199).
- Harness: nhan `_nb*.html` thanh `_hm*.html` dat trong web root; do 4 viewport
  **390 / 601 / 840 / 1337**, ket qua nam trong `<title>`; `_hmshot.html` (`?w=&h=&u=`) de chup;
  **xoa khoi web root ngay sau khi xong** (bai hoc: quen copy vao web root → chup trang 404,
  dau hieu la 6 anh 15KB giong het nhau). Dat `zoom=1` truoc khi do; tai khoan E2E phai co
  `game1.endtime` trong 3600s neu khong bi da ve Dang Nhap (`game.php:477`).
- Apache phpStudy thuong khong bat → `php.exe -S 127.0.0.1:8199 -t WWW`.
- Anh chung minh luu `docs/img/huyetmach/`: 5 bac fx, 8 ngọc khoa/sang/day, man that bai khi thieu
  Huyet Thach, boss spawn + kill, 390px va 1337px. **Doi chieu 1:1 anh mock/live**, khong tin so do
  computed style mot minh.

---

## 8. RUI RO VAN CON MO (phan chu du an chot hoac kiem chung sau)

1. **Thoi gian dao tao**: 8 mach × tran 48 = 384 lan luyen, can tich luy ~1.500 Huyet Thach
   (boss roi 8–14). Chap nhan throttle nay, gat bo, hay ha tran/mach? Day la quyet dinh can bang.
2. **`pen` va `dodge` trong `pvp.php` / `pve.php`**: chua doc het hai file nay de xac dinh dung
   truong nao dang duoc dung (`tfsb` chi thay o `boss.php:291` va thien phu). Phai kiem chung
   truoc khi sua cong thuc sat thuong.
3. **So HP/ATK boss config moi** chi la dai so phong, chua doi chieu `linhcan_boss` dang chay.
4. **Anh kinh mach**: chua biet source nao co san → co kha nang phai ImageGen va phai do lai toa do
   8 ngọc theo truc that cua buc anh.
5. **Vat pham 112/113 khong co `use_effect`**: `game/use_dj.php` (dòng 22+ xu ly reroll) co the van
   hien nut "Su dung" → kiem cach 100→108 dang xu ly va an nut neu can.
