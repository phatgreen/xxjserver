# SPEC: THIEN PHU — PHU CAC SUU TAM (2026-10-12)

> Chu du an duyet ngay 2026-10-12 qua brainstorm 5 cau hoi: (1) vai tro = **danh xung
> bam sinh co ten**, diem `tf` chuyen thanh nguyen lieu nang cap; (2) cach co duoc =
> **suu tam + chon chu phu**, roll trung khong bao gio mat; (3) ky nang danh vao
> **lop van khi & toc do** (khong cong chi so chien dau); (4) du lieu cu = **hoan diem
> + tang 1 lan khai phu**; (5) ba quyet dinh cuoi theo khuyen nghi cua agent.
> Ke hoach thuc thi tai `docs/superpowers/plans/2026-10-12-thien-phu-phu-cac.md` (chua viet).
> Khuon: `docs/superpowers/specs/2026-10-09-huyetmach-mang-mach-huyet-design.md`.
> **File nay viet khong dau** theo le cua 2 spec truoc; **ngoai tru** §4.5/§4.6 (chuoi UI that
> phai co dau) va ky tu ve khung cay §4.1. Khi nap code phai quet non-ASCII that nghiem (Muc 1.9).

---

## 0. Pham vi va cac quyet dinh da chot

| Cau hoi | Tra loi da chot |
|---|---|
| Pham vi | **Dai tu he thong**, khong chi do UI. `game/tianfu.php` dung lai toan bo UI + them 2 man moi |
| Vai tro | Thang thu 4 trong `WORLD_LORE.md` §IV.4: **thien phu bam sinh co ten**, khong phai bang chia diem |
| 7 o chia diem cu | **Bo han**. 7 cot `tfhp/tfgj/tffy/tfbj/tfxx/tfxy/tfsb` KHONG drop, chi ve 0 va ngung doc |
| So luong noi dung | **18 thien phu / 6 nhom × 3**, moi cai an dung **1 cong** |
| Cach co duoc | **Khai Phu Dan (daoju 114)** → roll 1/18 theo weight. **Trung = cai dang co +1 pham chat** (khong mat) |
| So pham chat | **10 bac Pham→Dao**, dung lai nguyen `quality_names/colors/icons/weights` cua Nhuc Than |
| Nang cap do | Bang **diem `tf` + Linh thach, 100% thanh cong** (diem `tf` van tu dot pha, `tupo.php:209`) |
| Leo pham chat | **2 duong**: (a) dan trung = duong tat; (b) **Dot Pham** 100% thanh cong ton `tf` + Linh thach + canh gioi. Chot vay de F2P leo duoc — xem §9 no 1 |
| Phat huy | **4 Phu Vi (slot)** mo theo `ulv` 0/100/200/280. **Cam bat 2 thien phu cung 1 cong** (chan o server, khong phai UI) |
| Khoa cv30 | **BO**. Nguoi moi vao duoc ngay, buc tuong that la phai co dan 50.000 LT |
| Boss thien phu rieng | **Phase 2**, khong lam lan nay |
| Chi so chien dau | Thien phu **khong cong** `ugj/ufy/umaxhp/ubj/uxx`. Do la ranh gioi 3 he truoc |
| Tran hieu ung | **+30%** moi cong, clamp **sau** khi nhan moc (khong de lap lai loi `stat_cap 15` cua Mach Huyet) |

Ba truc van dong song song (hoc khuon Mach Huyet):

1. **Lv thien phu** (0..tran cua pham) — nang 100% thanh cong, chiem panel giua man.
2. **Pham chat** (1..10) — quyet dinh tran Lv va he so hieu ung. Leo bang Dot Pham hoac dan trung.
3. **Tong Lv ca Cac** — mo milestone nhan vao ca 7 cong.

---

## 1. LO NGU CUNG PHAI GIU (bat buoc)

1. **`sid` la hex 32 ky tu, khong duoc dung cot INT.** `game1.sid` dang kieu VARCHAR — schema moi
   phai `VARCHAR(64)` giong `sql/sql_huyetmach_vessel.sql:7`. No schema debt 15 bang o
   `docs/` (ap local 03/10, production chua) — dung noi bo qua le nay.
2. **MySQL 5.5**: khong `DEFAULT CURRENT_TIMESTAMP` cho DATETIME, dung `DEFAULT NULL` + ghi `date()` phia PHP.
3. **PHP 5.6.9**: khong `??`, khong short array `[]` trong code moi (JSON file thi duoc), khong arrow fn,
   khong typed property. `class/function/tianfu.php` phai chay `php -l` bang chinh `php5.6.9nts`.
4. **`.main button` la landmine**: moi nut trong man phai dat selector `.main .tp-act*` (2 lop) de thang
   rule chung, dung nhu `mystyle.css:7555-7561` cua `.hm-act`.
5. **`a:link` (0,1,1) de rule 1 lop** o man Lien Can — moi selector lien ket dung dang `a.tp-tab:*`.
6. **`body.bg-body{zoom:.75}`** tai `@media(min-width:1024px)` (`css/mystyle.css:5623-5628`) → khi do layout
   phai dat `body.style.zoom=1` trong iframe harness truoc khi do.
7. **Moi chuoi `?cmd=` dung DUNG MOT LAN** (chong replay). Request that bai tra ve body 3 byte (chi BOM)
   + header `refresh:1;url=index.php`. Bao gio cung encode lai va **assert `LEN > 500`**.
8. **Server dev**: Apache phpStudy port 80 CHET; `php -S` cong **8090 da bi `WsToastNotification.exe` chiem**
   (tra 501, de ngu ban la app loi) → dung **8091** hoac cong khac, luon `curl` kiem tra truoc khi do.
9. **Edit/Write de chen ky tu loi** (smart quotes, CJK lac cho) → sau moi lan sua file phai chay
   `grep -nP '[\x{2E80}-\x{9FFF}\x{3000}-\x{303F}]'` va `grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]'`.
10. **`tfxy` KHONG phai cot chet**: `game/boss.php:454` doc no roi `:456` `mt_rand(1,120-$xyz)` → no dang
    nong nguong roi do cua boss. Neu thien phu cung nhan cong roi do ma khong go dong 454 la **roi dup**.
11. **`tfsb` dung la chet** (0 noi doc), nhung **khong duoc** noi no vao van khi: Ne Tranh da thuoc
    Am Kieu Mach (`class/function/huyetmach.php:1112`). De trong, khong drop cot.
12. **Diem da chia con am anh chien luc**: `class/player.php:418-420` va `game/paihang.php:17-19` cong
    `tf×50 + tfxy×10 + tfsb×10`. Migration phai thay trong so, khong bang xep hang sat tuc.
13. **Di qua luong `use_dj` cu** o `class/player.php:2058-2087`: no cap 2 lan dung/loai va can cot
    `djuse` (109-113 trong `daoju_config.json` khong co `djuse` → luong nay thuc te dang bich).
    Khai Phu Dan tieu bang `deledjsum(114,1,$sid,$dblj)` truc tiep trong `tianfu_open()`, kieu
    `class/function/linhcan.php:610,620` (`linhcan_wash()`).
14. **`getdaoju()` doc JSON chu khong doc bang `daoju`** (`class/player.php:1416-1420` goi
    `daoju_config_get()`), nen them daoju 114 chi can sua `class/config/daoju_config.json`, khong INSERT.

---

## 2. DATA LAYER

### 2.1 `class/config/tianfu_config.json` (MOI, schema `tianfu_v1`)

Top-level keys: `schema`, `groups`, `talents`, `gates`, `qualities`, `quality_weights`,
`quality_names`, `quality_colors`, `quality_icons`, `lv_cost`, `break_quality`, `milestones`,
`slots`, `cap_percent`, `material_names`.

**7 cong** (`gates`) — ten key la ten truong trong `tianfu_bonuses()`:

| key | nhan | don vi |
|---|---|---|
| `exp_cultivate` | Toc do tu luyen | % nhan vao `exp_per_sec` |
| `exp_monster` | Exp quai + boss | % nhan vao `finalExp` |
| `drop` | Nguong roi do | % nhan vao nguong `dljv/djjv/ypjv` |
| `alchemy` | Luyen dan | diem % cong vao ti le 5..95 |
| `forge_cost` | Linh thach cuong hoa | % giam gia (khong phai tang) |
| `stamina` | Hoi the luc | % giam chu ky 300s |
| `breakthrough` | Dot pha | diem % cong vao ti le 5..99 |

**18 thien phu** (`talents`, id 1..18). Moi muc: `id, name, short, group, gate, icon, color,
weight, per_lv, desc`. `per_lv` = % moi cap, **0.25 deu** (don gian, de kiem). Ten (khong dau
trong spec; ban co dau that nap vao JSON, doi chieu §UI ở muc 4.5):

| id | Ten | Nhom | Cong |
|---|---|---|---|
| 1 | NGO TINH NGHICH THIEN | Ngo tinh | exp_cultivate |
| 2 | LUYEN KHI KY TAI | Ngo tinh | exp_cultivate |
| 3 | TAM THONG NGO DAO | Ngo tinh | exp_cultivate |
| 4 | DAI KHI VAN | Co duyen | drop |
| 5 | THIEN DAO GIA HO | Co duyen | drop |
| 6 | CO DUEN THAM TU HAI | Co duyen | drop |
| 7 | DAN DAO KY TAI | Dan dao | alchemy |
| 8 | LO HOA THUAN THANH | Dan dao | alchemy |
| 9 | BACH THAO BAO NHIEM | Dan dao | alchemy |
| 10 | PHU TAI LUU THONG | Tai van | forge_cost |
| 11 | NHAT CHICH THIEN KIM | Tai van | forge_cost |
| 12 | THU THIEN HOAT THUAT | Tai van | forge_cost |
| 13 | DOT PHACH TAM KINH | Canh gioi | breakthrough |
| 14 | BAT KHUC DAO Y | Canh gioi | breakthrough |
| 15 | LANG KHONG DICH BICH | Canh gioi | breakthrough |
| 16 | THAN SUAT QUA NHAN | Than suat | stamina |
| 17 | KHINH NHU YEN VU | Than suat | stamina |
| 18 | TICH DUONG BO PHAP | Than suat | stamina |

`exp_monster` **khong co thien phu rieng** — no la **phu cong** cua nhom Ngo tinh: id 1/2/3 co them
truong `per_lv_sub = 0.15` tinh rieng cho `exp_monster` (**khong** cong 0.25 + 0.15 lai). Ly do:
18 chia het cho 7 cong thi le, va 6 nhom × 3 la cam ket voi chu du an. `per_lv_sub` duoc kiem tra
bang test "moi cong co it nhat 1 chu".

`weight` theo nhom: 3 muc 5/4/3 (id dau nhom = 5, thu 2 = 4, thu 3 = 3) → tong 72, de test tong weight.

**10 pham chat** (`qualities`, dung nguyen ten/mau/icon/weight cua `thechat_config.json`):

| q | ten | weight | tran Lv | he so | % toi da (Lv tran × 0.25 × he so) |
|---|---|---|---|---|---|
| 1 | Pham Pham | 40 | 10 | 1.00 | 2.50% |
| 2 | Linh Pham | 25 | 14 | 1.15 | 4.03% |
| 3 | Huyen Pham | 15 | 18 | 1.30 | 5.85% |
| 4 | Dia Pham | 10 | 24 | 1.50 | 9.00% |
| 5 | Thien Pham | 6 | 30 | 1.75 | 13.13% |
| 6 | Thanh Pham | 2.5 | 36 | 2.00 | 18.00% |
| 7 | Tien Pham | 1 | 42 | 2.30 | 24.15% |
| 8 | Than Pham | 0.4 | 48 | 2.60 | **30.00%** (clamp) |
| 9 | De Pham | 0.09 | 54 | 3.00 | **30.00%** (clamp) |
| 10 | Dao Pham | 0.01 | 60 | 3.50 | **30.00%** (clamp) |

Cot cuoi la **so phai kiem chung bang test** — clamp 30% that su binding tu pham 8 (khac
`stat_cap 15` cua Mach Huyet khong bao gio cham, `docs/UI_HUYETMACH.md` §4.4).

`lv_cost`: `tf = 1`, `linh_thach = 200 + 25*lv` (nguyen van cong thuc `huyetmach_vessel_refine_cost`).

`break_quality` (Dot Pham, 100% thanh cong, **khong co nan nhan vat pham**):

| q→q+1 | tf | Linh thach | `ulv` toi thieu |
|---|---|---|---|
| 1→2 | 10 | 10.000 | 0 |
| 2→3 | 15 | 30.000 | 0 |
| 3→4 | 20 | 80.000 | 0 |
| 4→5 | 30 | 200.000 | 100 |
| 5→6 | 40 | 500.000 | 150 |
| 6→7 | 60 | 1.200.000 | 200 |
| 7→8 | 80 | 3.000.000 | 280 |
| 8→9 | 100 | 8.000.000 | 280 |
| 9→10 | 150 | 20.000.000 | 280 |

`ulv` 100/150/200/280 la 4 moc **da duoc chung minh ten trong game** (Luyen The / Theu The /
Tu Than / Ngu Khi, `docs/UI_HUYETMACH.md` §2). Ba dong cuoi de 280 vi chua doc bang 86 canh gioi
→ no §9.2.

`milestones` (tong Lv toan bo thien phu **da so huu**, khong tinh rieng chu phu):
60 → +5% (Khai Phu) · 150 → +12% (Thong Linh) · 300 → +20% (Hoa Dao) · 500 → +30% (Nghich Thien).
Nhan vao % cong truoc, **clamp 30 sau**.

`slots`: `[{i:1,ulv:0},{i:2,ulv:100},{i:3,ulv:200},{i:4,ulv:280}]`.
`cap_percent`: 30. `material_names`: `{"114":"Khai Phu Dan"}`.

### 2.2 Schema — `sql/sql_tianfu.sql` (MOI)

```
CREATE TABLE IF NOT EXISTS player_tianfu (
  sid         VARCHAR(64) NOT NULL,
  tianfu_id   TINYINT(4)  NOT NULL,
  quality     TINYINT(4)  NOT NULL DEFAULT 1,
  lv          INT(11)     NOT NULL DEFAULT 0,
  slot        TINYINT(4)  NOT NULL DEFAULT 0,
  obtained_at DATETIME    DEFAULT NULL,
  PRIMARY KEY (sid, tianfu_id),
  KEY idx_sid_slot (sid, slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS player_tianfu_log (
  id         INT(11) NOT NULL AUTO_INCREMENT,
  sid        VARCHAR(64) NOT NULL,
  kind       VARCHAR(20) NOT NULL,   -- open | lvup | break | slot | migrate
  tianfu_id  TINYINT(4)  NOT NULL,
  from_q     TINYINT(4)  DEFAULT 0,
  to_q       TINYINT(4)  DEFAULT 0,
  detail     VARCHAR(200) DEFAULT NULL,
  created_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id), KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

`game1` them **4 cot** (ALTER co guard `information_schema.COLUMNS`, chay lai khong loi):
`tianfu_id TINYINT(4) DEFAULT 0`, `tianfu_quality TINYINT(4) DEFAULT 0`,
`tianfu_lv INT(11) DEFAULT 0`, `tianfu_power INT(11) DEFAULT 0`.

Ba cot dau la **ban sao cua slot 1** (de `zhuangtai`/huy hieu doc nhanh, giong cach 3 he kia
giu `*_id/*_quality` trong `game1`). `tianfu_power` = tong `(quality*150 + lv*8)` cua **ca 4 slot
dang bat**, dung cho chien luc. Ca 4 cot duoc **tinh lai trong cung mot transaction** moi lan
thay doi slot/lv/pham — test §8.3 bat buoc doi chieu `player_tianfu` vs `game1`.

### 2.3 Vat pham — `class/config/daoju_config.json`

Them `114`: `djname "Khai Phu Dan"`, `djzl "114"`, `djyxb 50000`, `djjg 0`,
`djinfo` co dau that: "Dan coc tu thien phu chi khi. Uong mot lan la khai mo mot thien phu
bam sinh; neu da co thi thien phu ay tang mot pham chat." Gia 50.000 = bang `109/110/113`
(`daoju_config.json:366+`), de kinh te khong lech.
Sua `game/shangdian.php:343`: hien loai 112/113/**114** khoi cua hang → **bo 114 ra khoi
danh sach loai** (114 phai mua duoc, 112/113 giu nguyen).

---

## 3. FUNCTION LAYER — `class/function/tianfu.php` (MOI)

Moi ham bo trong `if (!function_exists(__NAMESPACE__ . '\\...'))` nhu 3 he kia. Namespace `\tianfu`.

| Ham | Tra ve / tac dung |
|---|---|
| `tianfu_config_all()` | decode JSON, guard `isset` |
| `tianfu_defs()` / `tianfu_def($id)` | 18 thien phu / 1 thien phu |
| `tianfu_gate_def($gate)` | ten + don vi cua 1 cong |
| `tianfu_quality_def($q)` | 1 trong 10 bac (lv_cap, mult, color, icon, name) |
| `tianfu_lv_cap($q)` | tran Lv cua pham |
| `tianfu_lv_cost($lv)` | `array('tf'=>1,'linh_thach'=>200+25*lv)` |
| `tianfu_slot_open($ulv)` | mang slot duoc mo theo `ulv` |
| `tianfu_owned($sid,$dblj)` | 18 dong: `id, have, quality, lv, slot` (thieu = have 0) |
| `tianfu_activated($sid,$dblj)` | chi dong `slot>0` (toi da 4) |
| `tianfu_milestone($total_lv)` | moc hien tai + moc ke + % |
| **`tianfu_bonuses($sid,$dblj)`** | **15 key**: 7 cong + `total_lv milestone_name milestone_percent next_milestone_name next_milestone_percent next_milestone_lv activated_count` |
| `tianfu_open($sid,$dblj)` | tieu 114 → roll → INSERT hoac +1 pham; transaction; tra `array('ok','talent','quality','is_new','msg')` |
| `tianfu_lvup($sid,$dblj,$id)` | +1 Lv, 100% thanh cong, kiem tran pham + tf + LT |
| `tianfu_break($sid,$dblj,$id)` | Dot Pham, 100% thanh cong, kiem Lv tran + tf + LT + ulv |
| `tianfu_slot_set($sid,$dblj,$id,$slot)` | bat/tat slot, **chan 2 cai cung 1 cong**, viet lai 4 cot `game1` |
| `tianfu_recalc_mirror($sid,$dblj)` | tinh lai `tianfu_id/quality/lv/power` tu `player_tianfu` |
| `tianfu_luck_gate($pct,$gate)` | `round($gate*(100+$pct)/100)` — guong `huyetmach.php:1142` |
| `tianfu_forge_cost($pct,$lt)` | `max(1, round($lt*(100-$pct)/100))` |
| `tianfu_stamina_interval($pct,$sec=300)` | `max(60, round($sec*100/(100+$pct)))` |

`tianfu_bonuses()` — cong thuc **duy nhat** cho moi cong:
`% = min(30, lv * per_lv * mult * (1 + milestone/100))`
trong do `per_lv = 0.25` (cong chinh cua thien phu), `exp_monster` dung `per_lv = per_lv_sub = 0.15`,
`mult` = he so pham chat (§2.1), `milestone` = % moc tong Lv (§2.1). Clamp 30 **sau** phep nhan moc.
`round(...,2)` giu so le; 3 cong truyen diem `%` (alchemy/forge_cost/breakthrough) **lam tron `int`**
tr khi cong vao ti le san co.
**Khong query trong vong lap** — 1 query `player_tianfu WHERE sid=?` duy nhat.

---

## 4. UI LAYER

### 4.1 `game/tianfu.php` (VIET LAI het, 174 dong → ~330)

```
.tp-wrap (tp-fx0..fx4, --tp-tier/--tp-glow/--tp-dur/--tp-aura/--tp-color)
├── .tp-msg                       thong bao Khai Phu / Nang Cap / Dot Pham
├── .tp-card.tp-card-main         CARD 1 — Chủ Phú
│   ├── .tp-head > .tp-hrow > .tp-title + .tp-badge (pham chat · tran Lv)
│   ├── .tp-rail                  6 nhom: .tp-tier × 6, noi bang .tp-link.is-lit
│   ├── .tp-viewline + .tp-note   "Dang xem nhom X" + ghi chu cho o chua mo
│   └── .tp-forge-wrap
│       ├── .tp-stage             anh nen + huy hieu + .tp-aura/.tp-sweep
│       └── .tp-forge             ten, Lv/tran, +%/cap, phi tf+LT, nut .tp-act-up
├── .tp-card.tp-cols              CARD 2 — 2 cot
│   ├── trai  .tp-h3 "Ky Nang Kich Hoat"   7 dong .tp-attr (ten cong, gia tri, tran 30%)
│   │                                    + .tp-ms (thanh tong Lv → moc)
│   └── phai  .tp-h3 "4 Phu Vi"            4 .tp-slot (trong / da bat) + .tp-gate + .tp-reqs
└── .tp-tabbar                    Thien Phu · Phu Cac · Khai Phu · Nhan Vat
```

Khong con: `talent_alert()`, `talent_amount_html()`, `talent_action_html()`, 7 nut
`+1/+10/+50/+100/Max`, `talentConfig`, nut "Gợi ý" (`wbts1`) va "Nghịch thiên cải mệnh" (`$nt`) —
xem §6.

### 4.2 `game/tianfu_collection.php` (MOI) — PHU CAC

Luoi 18 o `.tpc-cell` (3 cot mobile / 6 cot desktop), trang thai `is-have / is-lock / is-active-slot1..4`.
Cham o → `.tpc-detail` (panel ngay duoi, **khong dung hover** — phai mo duoc bang cham, xem §1 R8
cua `UI_HUYETMACH`). Trong panel: ten, pham, Lv/tran, ky nang, 3 nut `.tpc-act`:
`Nang Cap` (tf+LT) · `Dot Pham` (tf+LT+canh gioi) · `Bat vao Phu Vi` (chon slot).
`is-lock` in "Chua khai mo — can Khai Phu Dan".

### 4.3 `game/tianfu_open.php` (MOI) — KHAI PHU

Hien so dan 114 trong tui, gia 50.000 LT, nut `.tpo-act` "Khai mo" (1 lan/req, encode lai sau moi lan),
bang ty le 10 pham chat (`quality_weights`), va **dòng chữ thẳng**: "Trung se khong mat — no tang
1 pham chat cho chinh thien phu ay." Ket qua tra ve `.tpo-result` theo pham chat (mau + icon tu config).

### 4.4 CSS — `css/mystyle.css`

Xoa khoi `.talent-*` (82 rule, `4065-4500`), thay bang block `.tp-*` + `.tpc-*` + `.tpo-*` (~420 dong),
cuoi file sau block `.hm-*`. Thang hieu ung **bat buoc phai co du 6 bac va bac thap nhat van phai
thay duoc cai gi do** (le cua chu du an):

```
.tp-fx0{--tp-dur:0s;--tp-aura:.05;--tp-glow:4px;--tp-act-dur:6s}
.tp-fx1{--tp-dur:10s;--tp-aura:.07;--tp-glow:7px;--tp-act-dur:5s}
.tp-fx2{--tp-dur:8s;--tp-aura:.10;--tp-glow:10px;--tp-act-dur:4.2s}
.tp-fx3{--tp-dur:6s;--tp-aura:.13;--tp-glow:14px;--tp-act-dur:3.4s}
.tp-fx4{--tp-dur:4s;--tp-aura:.16;--tp-glow:18px;--tp-act-dur:2.9s}
.tp-fx5{--tp-dur:3s;--tp-aura:.20;--tp-glow:22px;--tp-act-dur:2.4s}
```
`fx` theo **pham chat chu phu** (1-2→fx0, 3-4→fx1, 5→fx2, 6→fx3, 7-8→fx4, 9-10→fx5).
`.tp-stage` phai co `isolation:isolate` (neu khong lop `mix-blend-mode:screen` chay ra ngoai,
bai hoc `mystyle.css:7528`).

### 4.5 Anh — `images/tianfu/` (MOI, 7 file)

1 anh nen `phu-cac.jpg` (khu cac dang treo, **ton mau theo trang hien tai** — lay hex tu
`css/mystyle.css` cua `.tp-wrap`, khong tu chon) + 6 anh nhom (`nhom-ngo-tinh.jpg` v.v.).
18 huy hieu dung `icon` emoji + `color` trong config, **khong sinh 18 anh rieng**.
Nguon anh game goc (MuH5/TuTienPhucMa tren `E:`/`F:`) duoc uu tien truoc ImageGen.

### 4.6 Chuoi UI (ban co dau that, nap vao JSON/PHP)

Nhan nut ngan (~40px, cat phan thua): `Nang Cap` · `Dot Pham` · `Khai Mo` · `Lap Chu Phu` ·
`Tro Ve`. Ten 18 thien phu co dau day du: Nghich Thien Ngo Tinh → "Ngộ Tính Nghịch Thiên",
"Đại Khí Vận", "Thiên Đạo Gia Hộ", "Cơ Duyên Thâm Tự Hải", "Đan Đạo Kỳ Tài", "Lô Hỏa Thuần Thanh",
"Bách Thảo Bất Nhiễm", "Phú Tài Lưu Thông", "Nhất Chỉ Thiên Kim", "Thâu Thiên Hoạt Thuật",
"Đột Phách Tâm Kinh", "Bất Khúc Đạo Ý", "Lăng Không Đích Bích", "Thần Suất Quá Nhân",
"Khinh Như Yên Vụ", "Tịch Dương Bộ Pháp", "Tâm Thông Ngộ Đạo", "Luyện Khí Kỳ Tài".
Nhan doi nghia tu nhien trong `.tp-attr`: "Ưu thế" / "Nhược điểm" (KHONG dung "Phạt").

### 4.7 Noi vao cho khac

| Noi | Sua |
|---|---|
| `game.php:349` | them `'tianfu_collection'=>'game/tianfu_collection.php'`, `'tianfu_open'=>'game/tianfu_open.php'` |
| `game.php:502` | bump `?v=2026101201` |
| `game/zhuangtai.php:418-419` | thay 2 o "Thiên phú / Vận may-Né tránh" = "Chủ phú: <ten> · <pham>" + "Phu Vi: <n>/4" |
| `game/xiulian.php:281` | giu nguyen o menu `tianfu` |
| `tongquan.php` | them muc `thien-phu` (nhom `nuoi-duong`) sau muc `mach-huyet`: 3 bang `tbl-tp` (18 thien phu) / `tbl-tpq` (10 pham) / `tbl-tpg` (7 cong). Cot "Can canh gioi" phai qua `tq_realm_req()` |
| `class/player.php:148,814,827` | them truong `$tianfu` + reset + gan tu `tianfu_bonuses()` (guong 3 he kia) |
| `class/player.php:418-420` | xoa `tf/tfxy/tfsb` khoi `combat_power`, them `'tianfu_power'=>1` |
| `game/paihang.php:17-19` | tuong tu, dung `$rankPlayer->tianfu_power` |

---

## 5. TICH HOP 7 CONG (chi dan file:line that, da do 2026-10-12)

| # | Cong | Gan vao | Ghi chu rang buoc |
|---|---|---|---|
| 1 | exp_cultivate | `game/xiulian.php:113` | dat **ngoai** chuoi `min(1.3/1.5,...)` cua `cp_absorb/linhcan/thechat` (`:101-104`) de khong bi nuot |
| 2 | exp_monster | `game/pve.php:698-700`, `game/boss.php:594-596` | nhan `$finalExp` **truoc** `changeexp()` (`pve.php:708`, `boss.php:604`) |
| 3 | drop | `game/pve.php:469,489,518`, `game/boss.php:458,485,503` | `tianfu_luck_gate()` **nhan tiep** sau `huyetmach_combat_luck_gate()` (`class/function/huyetmach.php:1142`) → toi da 1.15×1.30 = **+49.5%**, phai ghi ro trong docs |
| 4 | alchemy | `class/function/luonden.php:348` | cong diem % vao `$finalRate`, **giu nguyen** `min(95,max(5,...))` → clamp 95 binding |
| 5 | forge_cost | `game/zbinfo.php:39-41` (guong `:147-149`) | chi giam `$upls` (Linh thach); **khong dong** `$updjsl` (Cường Hóa Thạch), **khong dong** ti le `class/player.php:1465` |
| 6 | stamina | `class/player.php:336` (`settle_stamina`, `$intervalSec=300`) | goi `tianfu_stamina_interval()` trong `settle_stamina` de **ca cron lan call truyen thong** cung dung; `cron/stamina_recovery.php:18` khong can sua |
| 7 | breakthrough | `game/tupo.php:122` | cong diem % vao `$rate` **truoc** `max(5,...)`; tran mem 99; **giu** `+5 tf` tai `:209` |

---

## 6. CAT / SUA CODE CU (cung lot trong dem nay)

| O | Hanh dong |
|---|---|
| `game/tianfu.php:12-20` `talentConfig` 7 o | **XOA** |
| `game/tianfu.php:22-43` `talent_alert/talent_amount_cmd/talent_action_html` | **XOA** (khong co caller khac — da grep `talent_` toan repo) |
| `game/tianfu.php:45-61` khoa `ulv < 30` | **XOA** — vao man duoc tu cv1 |
| `game/tianfu.php:63-72` nut "Gợi ý" (`$wbts1`, tieu 1 `uczb`) | **XOA** — no chi in ra mot doan van |
| `game/tianfu.php:74-86` "Nghịch thiên cải mệnh" (`$nt`) | **XOA** — day la **BUG**: `:75-77` roll `mt_rand(1, max(1,30-tfxy))` roi `:79` `SET tf = ulv*5`, tuc la **nhan lai nhieu hon so da bo** khi cv cao, va co the **reset ve 0 toan bo 7 o** neu roll truot. Xoa la cat mot loi kinh te |
| `game/tianfu.php:88-114` vong chia diem 7 o | **XOA** |
| `class/player.php:576-580` | **XOA** 5 dong cong chi so tu 7 o |
| `game/boss.php:454` `$xyz = $player->tfxy` | **XOA**, `:456` thanh `mt_rand(1,120)` (xem §1.10) |
| `css/mystyle.css:4065-4500` 82 rule `.talent-*` | **XOA** sau khi `.tp-*` chay duoc (thu tu: lam CSS moi truoc, xoa cu sau, de khong mat trang trung gian) |
| `game/zhuangtai.php:418-419` | **SUA** theo §4.7 |

7 cot `game1.tf*` **khong DROP** — giu lai de rollback, chi ngung doc.

---

## 7. MIGRATION — `sql/migrate_tianfu.php` (MOI, co DRY-RUN)

Chay **local truoc**, production chua bao gio duoc chay trong lan nay (3 he truoc cung chua deploy).

1. `mysqldump -u root -pwww.aae.ink game > bak/tianfu_pre_<Y-m-d_His>.sql` — in ra lenh restore.
2. `mysql ... < sql/sql_tianfu.sql` (2 bang + 4 cot `game1`, idempotent).
3. `php sql/migrate_tianfu.php --dry-run` → in bang 14 nhan vật: `hoan = tfhp+tfgj+tffy+tfbj+tfxx+tfxy+tfsb`,
   `tf_moi = tf + hoan`, so luong 114 se phat. **Khong ghi DB.**
4. `php sql/migrate_tianfu.php --apply`: tung nhan vật trong 1 transaction —
   `tf += hoan`; 7 cot ve 0; `adddj($sid,114,1,$dblj)`; `player_tianfu_log(kind='migrate')`;
   `tianfu_recalc_mirror()`. Xong phai in `OK=<n> FAIL=<0>` va **fail la dung het** (rollback), khong noi tiep.
5. Kiem tra sau: `SELECT COUNT(*) FROM game1 WHERE tfhp+... +tfsb > 0` phai = 0;
   `SELECT COUNT(*) FROM game1 WHERE tianfu_power > 0` phai = 0 (chua ai bat slot).

Nhan vat cu cung duoc tang 1 dan — **khong** tu dong gán thien phu (giu dung quyet dinh
"khoi dau trang" 06/10: khong cho mien phi mot cai ngau nhien).

---

## 8. QUY TRINH KIEM CHUNG (lap lai duoc)

```
# 0) Server dev (8090 bi chiem -> 8091)
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -S 127.0.0.1:8091 -t D:/XXJserver/phpstudy_pro/WWW

# 1) Lint + quet ky tu la (BAT BUOC)
php -l class/function/tianfu.php && php -l game/tianfu*.php
grep -nP '[\x{2E80}-\x{9FFF}\x{3000}-\x{303F}\x{2018}\x{2019}\x{201C}\x{201D}]' class game css

# 2) Test tinh
php tests/test_tianfu_schema.php
php tests/test_tianfu_config.php
php tests/test_tianfu_bonuses.php
php tests/test_tianfu_pages.php

# 3) Test live 7 cong (truoc-vs-sau tren tai khoan trang)
php tests/test_tianfu_gates.php --live

# 4) Chup anh (Chrome CLI, KHONG dung browser-use MCP), iframe 390px cho mobile,
#    dat body.style.zoom=1 truoc khi do, --user-data-dir sach moi lan
```

**Tai khoan test**: `sid=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2` (`E2E Test`, cv1000) va mot tai khoan
trang cv1 de test duong F2P. **Luon seed `endtime = DATE_ADD(NOW(), INTERVAL 6 HOUR)`** truoc khi
chup (lech gio PHP +8 vs MySQL +7 — xem `project-dev-test-harness`), va assert `<title>`/`.tp-wrap`
con ton tai truoc khi tin bat ky so nao.

Test phai bao trum:
- **schema**: 4 cot `game1` + 2 bang ton tai, kieu `sid` la VARCHAR, chay `sql_tianfu.sql` 2 lan khong loi.
- **config**: 18 muc du field, `weight` tong = 72, moi cong co >= 1 chu, `lv_cap` tang dan,
  `quality_weights` khop `thechat_config.json`.
- **bonuses**: 1.000 vong/cong, **assert clamp 30 binding o pham >= 8** va **khong bao gio > 30**;
  assert `game1` mirror khop `player_tianfu` sau moi hanh vi doi slot.
- **slot**: bat 2 thien phu cung 1 cong → server tu choi + thong bao.
- **pages**: 3 route that, moi request encode lai, `assert LEN > 500`.
- **gates --live**: do **trước** (chua bat slot) va **sau** (bat slot) tren cung 1 trang thai:
  `exp_per_sec` (`xiulian.php:113`), exp quai (`pve.php:708`), so luong roi (`pve.php:469/489/518`),
  ti le luyen dan (`luonden.php:348`), gia cuong hoa (`zbinfo.php:39-41`), chu ky the luc
  (`player.php:336`), `rate` dot pha (`tupo.php:122`). **Moi cong phai thay doi duoc do luong**,
  khong dung "code da goi" lam bang chung.
- **migration**: chay dry-run → apply tren ban dump, doi chieu tong diem `tf` truoc/sau = tong 7 o.

---

## 9. RUI RO / NO CHO CHU DU AN CHOT

1. **F2P leo pham** — da chot: them duong `Dot Pham` 100% thanh cong (§2.1 `break_quality`) thay vi
   phu thuoc hoan toan vao dan trung. Rui ro van con: `tf` chi tu dot pha (+5/lan), nen muon Dot
   Pham 6→7 can 60 diem = **12 lan dot pha**. Neu qua doc, chi so can nhe la `tf` trong
   `break_quality`, khong phai them nguyen lieu.
2. **3 moc canh gioi cuoi cua `break_quality` de 280** vi chua doc bang 86 canh gioi. Khi viet
   config phai tra `tq_realm_req()`/bang canh gioi va nang len moc dung (day la so `[CHOT KHI NAP]`).
3. **Boss thien phu rieng = phase 2** (da chot khong lam). Khi do nguon `114` chi la cua hang +
   nhiem vu, co the kham kinh te → phase 2 them nguon roi tu boss the gioi co san.
4. **Tang chong 3 tang van khi**: Huyết Mạch (+15% roi do) × Thiên Phú (+30%) = +49.5%, cong them
   wugong `drop_bonus` (`pve.php:469,489` cong truc tiep). Phase 1 **chap nhan**, do bang
   `test_tianfu_gates.php --live` va ghi so that vao `docs/UI_THIENPHU.md`; neu lech qua xa thi
   chu du an quyet ha `cap_percent`.
5. **14 nhan vat local sau migration se co 1 dan trong tui** — nguoi dung kiem tra bang mat
   (chup `game/tianfu_open.php`) chu khong phai chi so.
6. **CHUA test trong game that** cho den khi `test_tianfu_gates.php --live` chay xong; moi muc
   chua do duoc in hoa CHUA trong `docs/UI_THIENPHU.md` (le cua chu du an: danh dau ro no).
7. **Production chua deploy** — ca 3 he truoc cung chua. `docs/DEPLOY_PRODUCTION_THIENPHU.md` viet
   runbook nhung **khong chay**.

---

## 10. BUOC TIEP THEO

Spec nay duoc duyet → goi `superpowers:writing-plans` de sinh
`docs/superpowers/plans/2026-10-12-thien-phu-phu-cac.md` (du kien 12 task, theo khung
`2026-10-09-huyetmach-mang-mach.md`: config → schema → function → man chinh → Phu Cac → Khai Phu
→ CSS/fx → anh → 7 cong → zhuangtai/tongquan/chieu luc → migration → test + docs).
Tai lieu sau khi xong: `docs/UI_THIENPHU.md` (12 muc, copy khung `docs/UI_HUYETMACH.md`)
va `docs/DEPLOY_PRODUCTION_THIENPHU.md`.

`game/luonden.php` / `game/hammo.php` **khong co** nguong roi kieu `dljv/djjv/ypjv` → thien phu
khong cham 2 man do. `game/pvp.php` **khong** nhan 7 cong nay (PvP tinh bang chi so thuan).
