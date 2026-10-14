# THIEN PHU — PHU CAC SUU TAM: Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dung lại tính năng Thiên Phú từ 7 o chia diem thanh một bộ sưu tập 18 thiên phu bam sinh co ten, luyen bang diem `tf` + Linh Thạch, phát huy qua 4 Phu Vi, và cắm vào 7 cong van khi (toc do tu luyen / exp quai / rơi đồ / luyện đan / chi phí cường hóa / hồi thể lực / đột phá).

**Architecture:** Ba trục song song nhu Mach Huyet — **Lv thiên phu** (luyen 100% thanh cong), **pham chat 1..10** (chat tran Lv + he so hieu ung, leo bang dan trung hoac Dot Pham 100%), **tong Lv toan Cac** (mo milestone nhan vao ca 7 cong). Tang data nam trong `class/config/tianfu_config.json` + `class/function/tianfu.php` (namespace `\tianfu`) + bang `player_tianfu`; ba cot `game1.tianfu_id/_quality/_lv` chi la **ban sao cua Phu Vi 1 (chu phu)**, `game1.tianfu_power` la aggregate cho chien luc. Thien phu **khong** cong `ugj/ufy/umaxhp/ubj/uxx` — do la ranh gioi cua 3 he truoc.

**Tech Stack:** PHP 5.6.9 NTS (cli: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe`), MySQL 5.5 qua PDO, HTML + CSS trong `css/mystyle.css` (prefix `.tp-*` / `.tpc-*` / `.tpo-*`), test CLI theo kieu `tassert` cua `tests/test_nguhanh.php`, do/chup UI bang Chrome headless CLI.

**Spec:** `docs/superpowers/specs/2026-10-12-thien-phu-phu-cac-design.md`

## Global Constraints

- **PHP 5.6**: khong `??`, khong `<=>`, khong arrow fn, khong typed property, dung `array()` thay vi `[]` trong code PHP (JSON thi dung `{}`/`[]` binh thuong). Moi file PHP sau khi tao/sua: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l <file>` (`php.exe` KHONG co tren PATH). Ap dung skill `php56`.
- **MySQL 5.5**: `sid` phai la `VARCHAR(64)` (hex 32 ky tu), `DATETIME DEFAULT NULL` (khong `DEFAULT CURRENT_TIMESTAMP`), `ENGINE=InnoDB DEFAULT CHARSET=utf8`. Khuon: `sql/sql_huyetmach_vessel.sql`.
- **`game1` la bang MyISAM/InnoDB dung `sid` hex** — moi query moi phai `prepare` + `execute(array(...))`, khong noi chuoi SQL tu input nguoi dung. Ten cot dong trong `UPDATE` phai la hang so tu config, khong tu `$_POST`.
- **Landmine SELECT (spec chua nep, plan bo sung):** `class/player.php:302-311` `player_allowed_columns()` la **danh sach trang whitelist cho `SELECT $cols FROM game1`** o `getplayer()` (`:555-563`). Khong them 4 cot `tianfu_*` vao day thi `$player->tianfu_quality` MAI MAI NULL du migration da chay. Task 2 Step 4 xu ly, test schema bat buoc doi chieu.
- **`.main button` / `a:link` la landmine**: moi nut trong man phai co selector 2 lop `.main .tp-act*`, moi lien ket phai dang `.main a.tp-*` / `.main a.tpc-*` (0,2,1) de thang `css/gamecss.css:70 a:link` (0,1,1). Khuon: `css/mystyle.css:7555-7561` (`.hm-act`) va `7769-7775` (`.main a.lc-*`).
- **So tien hien cho nguoi choi: `number_format($x, 0, ',', '.')` — LUON du 4 tham so.** Goi 1 tham so thi PHP tra kieu My `50,000`, lech voi toan bo game (`game/djinfo.php:79`, `game/shangdian.php`) va lam test FAIL. Quet truoc khi commit: `grep -n "number_format(" game/tianfu*.php class/function/tianfu.php | grep -v "0, ',', '\\.'"` phai tra **0 dong**. Rieng so thuc (ty le %) dung `number_format($x, 2, ',', '.')`.
- **Khung noi dung**: mobile `max-width:660px`, desktop `@media(min-width:601px)` -> `840px` (dung `.tp-wrap{max-width:660px}` tu nhu `game/tianfu.php`? KHONG — dung `max-width` cua rieng `wrap` man Thien Phu giong het `.lcq-wrap`/`.lcb-wrap`, khong mo rong khung chung). Khong dat `max-width` len `.tp-stage` hay `.tp-grid`.
- **`body.bg-body{zoom:.75}`** tai `@media(min-width:1024px)` (`css/mystyle.css:5623-5628`) -> truoc khi do bat ky so nao trong iframe harness: `evaluate("document.body.style.zoom=1")`.
- **Moi chuoi `?cmd=` dung DUNG MOT LAN.** Test/banner that bai tra body 3 byte (chi BOM) + header `refresh:1;url=index.php`. Bao gio cung `$encode->encode(...)` lai truoc khi `curl`, va **assert `LEN > 500`** + assert chuoi `.tp-wrap` con ton tai truoc khi tin bat ky so nao.
- **Server dev**: Apache phpStudy port 80 CHET; `php -S` cong **8090 da bi `WsToastNotification.exe` chiem** (tra 501). Dung **8091**. MySQL khoi dong bang `Start-Process mysqld.exe -ArgumentList '--defaults-file=D:\XXJserver\phpstudy_pro\WWW..\mysql-5.5.29-winx64\my.ini' -WindowStyle Hidden` (xem `memory/project-dev-test-harness.md`), kiem tra `mysqladmin -uroot -pwww.aae.ink ping`.
- **Lech gio PHP +8 vs MySQL +7**: khi seed `endtime` cho nhan vat test phai dung `DATE_ADD(NOW(), INTERVAL 6 HOUR)`, khong dung chuoi `date()` cua PHP.
- **Edit/Write de chen ky tu loi** (smart quotes U+2018/2019/201C/201D, CJK lac cho). Sau MOI lan tao/sua file PHP/JSON/CSS phai chay buoc quet (Step kieu "Quet ky tu la") va KHONG duoc thay CJK trong file PHP/JSON — tieng Viet co dau chi hop le trong chuoi UI va JSON.
- **Tieng Viet trong comment PHP viet KHONG DAU** (le cua `game/thechat.php`, `game/huyetmach_boss.php`). Tieng Viet co dau chi xuat hien trong: chuoi JSON, nhan nut, chuoi `msg` tra ve cho nguoi dung.
- **Tai khoan test — HAI kieu, dung dung cai:**
  - Test thuan ham / test DB cua rieng he Thien Phu (`test_tianfu_bonuses`, `test_tianfu_gates`): dung cap `sid = str_repeat('f', 32)` + `uid = 990112`, giong le cua `tests/test_huyetmach_upgrade.php:42-52` va `tests/test_linhcan_boss.php:16-17`. `uid` nay do ca ba he Linh Can/Huyet Mach dung chung, **khong tao nhan vat thu hai co cung uid** khi test song song.
  - Test route that + do/chup UI (`test_tianfu_pages`): tai `tests/e2e_fixture.php` — `sid = e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2`, `uid = 987650`, cv1000; don bang `tests/e2e_fixture.php --clean`. **KHONG tu `INSERT INTO game1`** trong loai nay: fixture tu sinh du cot NOT NULL (`e2e_fixture.php:42-67`).
  - **`playerdaoju` co PK la `(uid, djid)`** — moi `INSERT INTO playerdaoju` phai ghi ro `uid`, neu khong se `Duplicate entry` giua hai nhan vat.
- **Git**: `git add <file cu the>`, khong dung `git add -A`. **Nang trong luong: repo KHONG co `.gitignore`** (do that 2026-10-14: `ls .gitignore` -> khong ton tai), nen `bak/`, `.idea/`, `.codegraph/`, `chajian/` va hon 300 anh `shot_*.png` deu dang `??` — `git add -A` se **commit ca dump DB chua du lieu nguoi choi that**. Chi `git add` duong dan cu the, **luon doc `git status --short` sau khi add truoc khi commit**. `tongquan.php` dang co thay doi khong thuoc lo nay. Commit tung task, message theo le repo: `feat(tianfu): ...` / `fix(tianfu): ...`. **Khong commit `tongquan.php` cung file khac** trong Task 1-11.
- **Git — repo chi track 98 file** (do that 2026-10-14, `git ls-files 2>/dev/null | wc -l` = 98). **Tam file ma plan nay sua CHUA duoc track:** `game/tianfu.php`, `game/tupo.php`, `game/zbinfo.php`, `game/xiulian.php`, `game/paihang.php`, `game/shangdian.php`, `class/function/luonden.php`, `cron/stamina_recovery.php`. Da track (dung `git diff` binh thuong): `class/player.php`, `css/mystyle.css`, `game.php`, `game/pve.php`, `game/boss.php`, `game/zhuangtai.php`, `game/use_dj.php`, `tongquan.php`, `class/config/daoju_config.json`, `tests/deploy_preflight.php`, `docs/UI_HUYETMACH.md`. Ba he qua bat buoc phai nho:
  - `git diff --stat -- <file untracked>` **in ra KHONG GI**. Moi cho trong plan ghi "Expected: N insertions(+), M deletions(-)" ma file nam trong danh sach untracked thi **thay bang thu tuc snapshot** ngay duoi.
  - `git show HEAD:<file untracked>` **bao `path ... does not exist in HEAD`** ⇒ khong co "nen" de doi chieu so ky tu CJK. Cung thay bang snapshot.
  - `git status --porcelain` hien `??` truoc khi add va `A` (khong phai `M`) sau khi add: **toan bo noi dung file** duoc ghi nhan o commit dau tien, khong phai chi phan sua.
  - **Thu tuc snapshot** (dung thay `git diff --stat` cho file untracked) — truoc khi sua:
    ```bash
    cp game/tupo.php E:/window/Temp/_base_tupo.php
    ```
    sau khi sua:
    ```bash
    diff -u E:/window/Temp/_base_tupo.php game/tupo.php > E:/window/Temp/_d_tupo.txt; echo "diff exit=$?"
    ADD=$(grep -c '^+[^+]' E:/window/Temp/_d_tupo.txt); DEL=$(grep -c '^-[^-]' E:/window/Temp/_d_tupo.txt)
    echo "ADD=$ADD DEL=$DEL"; cat E:/window/Temp/_d_tupo.txt
    ```
    `ADD`/`DEL` thay cho `insertions`/`deletions`. Doi chieu CJK thi dung `grep -cP '[\x{4E00}-\x{9FFF}]'` tren **ca hai** file `_base_*` va file hien tai — hai so phai bang nhau (tru khi chinh buoc do co chu dinh xoa dong CJK, nhu Task 11 Step 5d).
  - **Khong tu quyet track them file khac ngoai 8 file tren.** Chi `git add` dung nhung file ma task do sua (le `feedback-khong-mo-rong-pham-vi-sua`). Viec 8 file cu **lan dau tien vao git** la he qua phai **bao truoc** o ban giao Task 12 Step 13 — khong phai quyet dinh cua nguoi thuc thi.
- **Production KHONG duoc dong trong lan nay**: `docs/DEPLOY_PRODUCTION_THIENPHU.md` chi la runbook viet ra de do, khong chay lenh nao tren server that (ca 3 he truoc cung chua deploy).
- **Quy trinh kiem chung (spec §8) da duoc nap vao plan nay theo 3 tang**, khong tach thanh task rieng: (1) **moi task** co it nhat mot Step "Lint + quet ky tu la" va mot Step "DO SO THAT / GET that" dung nguyen 4 buoc §8 (server 8091 → lint PHP 5.6.9 → test tinh → chup anh Chrome CLI **khong dung browser-use MCP**, iframe 390px, `body.style.zoom=1`, `--user-data-dir` sach moi lan); (2) **Task 12 Step 10** chay lai §8 muc 1-2 tren **toan bo** file da dung; (3) **Task 12 Step 11** chay **toan bo** `tests/test_*.php` (20 file) kem vong `--live` cho 5 test can server. §8 muc 3 ("test live 7 cong truoc-vs-sau tren tai khoan trang") nam o **Task 11 Step 13d** — bang 10 dong `CU/MOI(combat)/MOI(paihang)/lech`, va ket qua phai duoc chep nguyen van vao `docs/UI_THIENPHU.md` muc 9.1 (Task 12 Step 13b). **Khong duoc bo qua bat cu Step kiem chung nao de "cho nhanh"** — le `verification-before-completion`: bang chung truoc, ket luan sau.

## File Structure

| File | Vai tro | Task |
|---|---|---|
| `class/config/tianfu_config.json` (moi) | 6 nhom + 18 thien phu + 7 cong + 10 pham chat (lv_cap/mult/weight) + `lv_cost`/`break_quality`/`milestones`/`slots`/`cap_percent` | 1 |
| `class/function/tianfu.php` (moi) | namespace `player` (lech #4); ham doc thuan tuy (Task 1), doc/ghi DB + `tianfu_bonuses` (3), `tianfu_open` (4), `tianfu_lvup`/`tianfu_break`/`tianfu_*_rows` (5), `tianfu_slot_set`/`tianfu_slot_conflict` (6), 3 helper cong (11) | 1,3,4,5,6,11 |
| `sql/sql_tianfu.sql` (moi) | 2 bang `player_tianfu` + `player_tianfu_log`, 4 cot `game1` (ALTER co guard idempotent) | 2 |
| `tests/deploy_preflight.php` (sua) | them 2 bang vao `$SID_VARCHAR` | 2 |
| `class/player.php` (sua) | `player_allowed_columns()` +4 cot (`:302-311`); `var $tianfu*` (`:158`); gan `$player->tianfu` sau khoi Huyết Mạch (`:859`); `combat_power()` boi trong so `tf/tfxy/tfsb` = `tianfu_power` (`:413-425`); xoa 5 dong cong chi so cu (`:575-580`) — **CHI khi chu du an chot phuong an A o Task 12 Step 1** | 2,3,11,12 |
| `class/config/daoju_config.json` (sua) | them `114 Khai Phu Dan` co `use_effect.type = tianfu_open` | 4 |
| `game/shangdian.php` (sua — UNTRACKED) | bo `114` khoi danh sach loc o cua hang (`:343`) | 4 |
| `game/use_dj.php` (sua) | nhanh `tianfu_open` redirect ve man Khai Phu (khuon `linhcan_wash` `:85-89`) | 4 |
| `game/tianfu.php` (VIET LAI toan bo, 174 -> ~330 dong — UNTRACKED, nen commit dau ghi nhan ca file) | man chinh `.tp-*` | 8 |
| `game/tianfu_collection.php` (moi) | PHU CAC — luoi 18 o + panel chi tiet + 3 nut hanh dong | 9 |
| `game/tianfu_open.php` (moi) | KHAI PHU — uong dan 114, bang ty le, ket qua theo pham | 10 |
| `game.php` (sua) | 2 route moi (`:349`), 2 goi `settle_stamina` (`:452`), `mystyle.css?v=` (`:502`) | 8,9,10,11 |
| `css/mystyle.css` (sua) | append block `.tp-*`/`.tpc-*`/`.tpo-*` + `tp-fx0..fx5` xuong **cuoi file** (sau `:7846`) | 7 |
| `images/tianfu/*` (moi, 7 file) | `phu-cac.jpg` + 6 anh nhom | 7 |
| `game/zbinfo.php`, `game/xiulian.php`, `game/pve.php`, `game/boss.php`, `game/tupo.php`, `class/function/luonden.php`, `cron/stamina_recovery.php` (sua) | 7 cong van khi — **5/7 file nay UNTRACKED** (chi `game/pve.php` va `game/boss.php` da track) ⇒ dung thu tuc snapshot, khong dung `git diff --stat` | 11 |
| `game/zhuangtai.php` (sua) | 2 o "Thiên phú"/"Vận may-Né tránh" -> chu phu + Phu Vi | 11 |
| `game/paihang.php` (sua — UNTRACKED) | `tcg_calc_chien_luc_rank()` boi `tf/tfxy/tfsb` | 11 |
| `tongquan.php` (sua) | muc `thien-phu` + 3 bang `tbl-tp*` | 11 |
| `tests/test_tianfu_config.php`, `_schema.php`, `_bonuses.php`, `_pages.php`, `_gates.php` (moi) | test CLI + live | 1,2,3,10,11 |
| `sql/migrate_tianfu.php` (moi, co `--dry-run`) | hoan diem `tf` + tang 1 dan 114 | 12 |
| `docs/UI_THIENPHU.md`, `docs/DEPLOY_PRODUCTION_THIENPHU.md` (moi) | tai lieu 12 muc + runbook | 12 |

**Thu tu task la bat buoc** (khong dao ngoc): Task 7 (CSS) PHAI chay truoc Task 8-10 (UI) de moi lan mo trang da co style; Task 12 (cat `game/tianfu.php` cu + `.talent-*`) chay CUOI cung vi `css/mystyle.css:4062-4486` (spec §6 ghi `4065-4500`, do that 2026-10-14 la `4062-4486` — 425 dong) van con du dung boi man cu cho den khi man moi thay the xong.

---

### Task 1: Config `tianfu_config.json` + ham doc thuan tuy

**Files:**
- Create: `class/config/tianfu_config.json`
- Create: `class/function/tianfu.php` (khoi `tianfu_config_*` + ham thuan tuy; cac task sau **append** them vao cung file)
- Create: `tests/test_tianfu_config.php`
- Modify: `class/player.php:26` (them `require_once __DIR__ . '/function/tianfu.php';` ngay sau dong `require_once __DIR__ . '/function/chatmsg.php';` o `:27` — chen sau `:27`)

**Interfaces:**
- Consumes: khong co.
- Produces (namespace `player, xem lech #4`, moi ham bọc `if (!function_exists(__NAMESPACE__ . '\\<ten>'))`):
  - `tianfu_config_path(): string`
  - `tianfu_config_all(): array` (static cache; file thieu -> `array()`)
  - `tianfu_groups(): array` (6), `tianfu_defs(): array` (18, key id), `tianfu_def($id): array|false`
  - `tianfu_gate_def($gate): array|false` (muc `gates`), `tianfu_gate_name($gate): string`
  - `tianfu_quality_def($q): array|false`, `tianfu_quality_name($q): string`, `tianfu_quality_color($q): string`, `tianfu_quality_icon($q): string`
  - `tianfu_lv_cap($q): int` (q ngoai 1..10 -> 0)
  - `tianfu_lv_cost($lv): array('tf'=>1,'linh_thach'=>200+25*lv)`
  - `tianfu_break_cost($q): array('tf'=>int,'linh_thach'=>int,'ulv_require'=>int)|false` (q=10 -> false)
  - `tianfu_slot_defs(): array` (4), `tianfu_slot_open($ulv): array` cac `i` da mo
  - `tianfu_contributions($def): array` — `gate => per_lv` (cong chinh + `per_lv_sub` cho `exp_monster`)
  - `tianfu_percent_calc($lv, $perLv, $mult, $milestonePct): float` — **cong thuc duy nhat**, clamp `cap_percent` SAU khi nhan milestone, `round(...,2)`
  - `tianfu_fx_class($quality): int` (0..5)
  - `tianfu_open_pill(): array('djid'=>114,'name'=>string)`

- [ ] **Step 1: Tao `class/config/tianfu_config.json`** (noi dung nguyen chan — day la file duyet tung o, khong tu y doi so):

```json
{
  "schema": "tianfu_v1",
  "groups": {
    "1": {"id": 1, "key": "ngo-tinh", "name": "Ngộ Tinh", "icon": "✨", "color": "#7dd3fc", "img": "images/tianfu/nhom-ngo-tinh.jpg", "desc": "Tố chất lãnh ngộ, pháp tắc tự thông — tu luyện nhanh gấp bội."},
    "2": {"id": 2, "key": "co-duyen", "name": "Cơ Duyên", "icon": "🍀", "color": "#4ade80", "img": "images/tianfu/nhom-co-duyen.jpg", "desc": "Cơ duyên thâm hậu, của ngon vật lạ tự tìm tới tay."},
    "3": {"id": 3, "key": "dan-dao", "name": "Đan Đạo", "icon": "⚗", "color": "#fb923c", "img": "images/tianfu/nhom-dan-dao.jpg", "desc": "Bên lò lửa, tay khuấy dược — tỉ lệ luyện đan tăng thêm."},
    "4": {"id": 4, "key": "tai-van", "name": "Tài Vận", "icon": "💰", "color": "#facc15", "img": "images/tianfu/nhom-tai-van.jpg", "desc": "Tiền đi theo người — cường hóa tốn ít Linh Thạch hơn."},
    "5": {"id": 5, "key": "canh-gioi", "name": "Cảnh Giới", "icon": "⛰", "color": "#c084fc", "img": "images/tianfu/nhom-canh-gioi.jpg", "desc": "Đạo tâm bất khuất, mỗi lần đột phá thêm một phần nắm chắc."},
    "6": {"id": 6, "key": "than-suat", "name": "Thần Suất", "icon": "⚡", "color": "#22d3ee", "img": "images/tianfu/nhom-than-suat.jpg", "desc": "Thân pháp như gió — thể lực hồi nhanh hơn thường nhân."}
  },
  "gates": {
    "exp_cultivate": {"name": "Tốc độ tu luyện", "unit": "%", "desc": "Nhân vào linh khí mỗi giây khi ngồi tu luyện."},
    "exp_monster": {"name": "Exp quái + boss", "unit": "%", "desc": "Nhân vào kinh nghiệm nhận được khi diệt quái và boss."},
    "drop": {"name": "Ngưỡng rơi đồ", "unit": "%", "desc": "Nới ngưỡng ngẫu nhiên rơi trang bị / vật phẩm / dược phẩm."},
    "alchemy": {"name": "Luyện đan", "unit": "điểm %", "desc": "Cộng thẳng vào tỉ lệ thành công khi luyện đan (trần 95%)."},
    "forge_cost": {"name": "Linh thạch cường hóa", "unit": "%", "desc": "GIẢM số Linh Thạch phải trả mỗi lần cường hóa trang bị."},
    "stamina": {"name": "Hồi thể lực", "unit": "%", "desc": "Rút ngắn chu kỳ hồi thể lực (gốc 300 giây)."},
    "breakthrough": {"name": "Đột phá", "unit": "điểm %", "desc": "Cộng thẳng vào tỉ lệ đột phá cảnh giới (trần mềm 99%)."}
  },
  "talents": {
    "1": {"id": 1, "name": "Ngộ Tính Nghịch Thiên", "short": "Nghịch Thiên", "group": 1, "gate": "exp_cultivate", "per_lv": 0.25, "per_lv_sub": 0.15, "icon": "✨", "color": "#7dd3fc", "weight": 5, "desc": "Pháp tắc tự đến trong một sát na, tu luyện một ngày bằng người một tháng."},
    "2": {"id": 2, "name": "Luyện Khí Kỳ Tài", "short": "Luyện Khí", "group": 1, "gate": "exp_cultivate", "per_lv": 0.25, "per_lv_sub": 0.15, "icon": "💠", "color": "#7dd3fc", "weight": 4, "desc": "Hít vào là linh khí ùa về kinh mạch, không cần khổ sở cầu đạo."},
    "3": {"id": 3, "name": "Tâm Thông Ngộ Đạo", "short": "Tâm Thông", "group": 1, "gate": "exp_cultivate", "per_lv": 0.25, "per_lv_sub": 0.15, "icon": "💡", "color": "#7dd3fc", "weight": 3, "desc": "Chữ đạo đọc một lần là hiểu, ngồi một khắc là nhập lưu."},
    "4": {"id": 4, "name": "Đại Khí Vận", "short": "Đại Vận", "group": 2, "gate": "drop", "per_lv": 0.25, "icon": "🍀", "color": "#4ade80", "weight": 5, "desc": "Trời thương kẻ khờ, của rơi trúng đầu."},
    "5": {"id": 5, "name": "Thiên Đạo Gia Hộ", "short": "Gia Hộ", "group": 2, "gate": "drop", "per_lv": 0.25, "icon": "🌠", "color": "#4ade80", "weight": 4, "desc": "Có một bàn tay vô hình sửa lại xác suất giúp ngươi."},
    "6": {"id": 6, "name": "Cơ Duyên Thâm Tự Hải", "short": "Cơ Duyên", "group": 2, "gate": "drop", "per_lv": 0.25, "icon": "🌊", "color": "#4ade80", "weight": 3, "desc": "Cơ duyên sâu như biển, bảo vật dưới vực cũng nổi lên."},
    "7": {"id": 7, "name": "Đan Đạo Kỳ Tài", "short": "Đan Đạo", "group": 3, "gate": "alchemy", "per_lv": 0.25, "icon": "⚗", "color": "#fb923c", "weight": 5, "desc": "Tay khuấy lò lửa, đan thành tám phần."},
    "8": {"id": 8, "name": "Lô Hỏa Thuần Thanh", "short": "Lô Hỏa", "group": 3, "gate": "alchemy", "per_lv": 0.25, "icon": "🔥", "color": "#fb923c", "weight": 4, "desc": "Lửa lò trong xanh, không còn cảnh đan hóa thành khói."},
    "9": {"id": 9, "name": "Bách Thảo Bất Nhiễm", "short": "Bách Thảo", "group": 3, "gate": "alchemy", "per_lv": 0.25, "icon": "🌿", "color": "#fb923c", "weight": 3, "desc": "Độc tính của dược thảo không dính được thân ngươi."},
    "10": {"id": 10, "name": "Phú Tài Lưu Thông", "short": "Phú Tài", "group": 4, "gate": "forge_cost", "per_lv": 0.25, "icon": "💰", "color": "#facc15", "weight": 5, "desc": "Tiền tự biết đường tới, cường hóa nhẹ ví hơn người thường."},
    "11": {"id": 11, "name": "Nhất Chỉ Thiên Kim", "short": "Thiên Kim", "group": 4, "gate": "forge_cost", "per_lv": 0.25, "icon": "☝", "color": "#facc15", "weight": 4, "desc": "Một ngón tay đáng giá nghìn vàng, sắt đá cũng hóa mềm."},
    "12": {"id": 12, "name": "Thâu Thiên Hoạt Thuật", "short": "Hoạt Thuật", "group": 4, "gate": "forge_cost", "per_lv": 0.25, "icon": "🎭", "color": "#facc15", "weight": 3, "desc": "Trò nhỏ thay trời, hóa một nửa Linh Thạch thành không khí."},
    "13": {"id": 13, "name": "Đột Phách Tâm Kinh", "short": "Tâm Kinh", "group": 5, "gate": "breakthrough", "per_lv": 0.25, "icon": "📜", "color": "#c084fc", "weight": 5, "desc": "Khai mạch đánh tan trùng chướng, đột phá thêm một phần nắm chắc."},
    "14": {"id": 14, "name": "Bất Khúc Đạo Ý", "short": "Đạo Ý", "group": 5, "gate": "breakthrough", "per_lv": 0.25, "icon": "⛰", "color": "#c084fc", "weight": 4, "desc": "Đạo tâm không cong, thiên địa cũng phải nhường lối."},
    "15": {"id": 15, "name": "Lăng Không Đích Bích", "short": "Lăng Không", "group": 5, "gate": "breakthrough", "per_lv": 0.25, "icon": "💨", "color": "#c084fc", "weight": 3, "desc": "Giáp cảnh như đi trên không, đâu cũng tới được."},
    "16": {"id": 16, "name": "Thần Suất Quá Nhân", "short": "Thần Suất", "group": 6, "gate": "stamina", "per_lv": 0.25, "icon": "⚡", "color": "#22d3ee", "weight": 5, "desc": "Tinh thần hơn người, nghỉ một khắc bằng người nửa canh giờ."},
    "17": {"id": 17, "name": "Khinh Như Yên Vụ", "short": "Yên Vụ", "group": 6, "gate": "stamina", "per_lv": 0.25, "icon": "🌁", "color": "#22d3ee", "weight": 4, "desc": "Thân nhẹ như khói, mệt mỏi tan trước khi kịp đọng."},
    "18": {"id": 18, "name": "Tịch Dương Bộ Pháp", "short": "Tịch Dương", "group": 6, "gate": "stamina", "per_lv": 0.25, "icon": "🌇", "color": "#22d3ee", "weight": 3, "desc": "Bộ pháp bước theo bóng mặt trời, khí lực luôn đầy."}
  },
  "qualities": {
    "1": {"q": 1, "key": "pham", "name": "Phàm Phẩm", "color": "#9e9e9e", "icon": "⚪", "weight": 40, "lv_cap": 10, "mult": 1.00},
    "2": {"q": 2, "key": "linh", "name": "Linh Phẩm", "color": "#4caf50", "icon": "🟢", "weight": 25, "lv_cap": 14, "mult": 1.15},
    "3": {"q": 3, "key": "huyen", "name": "Huyền Phẩm", "color": "#2196f3", "icon": "🔵", "weight": 15, "lv_cap": 18, "mult": 1.30},
    "4": {"q": 4, "key": "dia", "name": "Địa Phẩm", "color": "#9c27b0", "icon": "🟣", "weight": 10, "lv_cap": 24, "mult": 1.50},
    "5": {"q": 5, "key": "thien", "name": "Thiên Phẩm", "color": "#ff9800", "icon": "🟡", "weight": 6, "lv_cap": 30, "mult": 1.75},
    "6": {"q": 6, "key": "thanh", "name": "Thánh Phẩm", "color": "#ff5722", "icon": "🟠", "weight": 2.5, "lv_cap": 36, "mult": 2.00},
    "7": {"q": 7, "key": "tien", "name": "Tiên Phẩm", "color": "#f44336", "icon": "🔴", "weight": 1, "lv_cap": 42, "mult": 2.30},
    "8": {"q": 8, "key": "than", "name": "Thần Phẩm", "color": "#ffd700", "icon": "✨", "weight": 0.4, "lv_cap": 48, "mult": 2.60},
    "9": {"q": 9, "key": "de", "name": "Đế Phẩm", "color": "#7c4dff", "icon": "🌌", "weight": 0.09, "lv_cap": 54, "mult": 3.00},
    "10": {"q": 10, "key": "dao", "name": "Đạo Phẩm", "color": "#e0e0e0", "icon": "☯", "weight": 0.01, "lv_cap": 60, "mult": 3.50}
  },
  "lv_cost": {"tf": 1, "linh_thach_base": 200, "linh_thach_per_lv": 25},
  "break_quality": {
    "1": {"tf": 10, "linh_thach": 10000, "ulv_require": 0},
    "2": {"tf": 15, "linh_thach": 30000, "ulv_require": 0},
    "3": {"tf": 20, "linh_thach": 80000, "ulv_require": 0},
    "4": {"tf": 30, "linh_thach": 200000, "ulv_require": 100},
    "5": {"tf": 40, "linh_thach": 500000, "ulv_require": 150},
    "6": {"tf": 60, "linh_thach": 1200000, "ulv_require": 200},
    "7": {"tf": 80, "linh_thach": 3000000, "ulv_require": 280},
    "8": {"tf": 100, "linh_thach": 8000000, "ulv_require": 400},
    "9": {"tf": 150, "linh_thach": 20000000, "ulv_require": 500}
  },
  "milestones": {
    "1": {"total_lv": 60, "bonus_percent": 5, "name": "Khai Phú"},
    "2": {"total_lv": 150, "bonus_percent": 12, "name": "Thông Linh"},
    "3": {"total_lv": 300, "bonus_percent": 20, "name": "Hòa Đạo"},
    "4": {"total_lv": 500, "bonus_percent": 30, "name": "Nghịch Thiên"}
  },
  "slots": [
    {"i": 1, "ulv": 0, "name": "Chủ Phú", "hint": "Phú vị chính — tên và phẩm chất của nó hiển thị khắp nơi."},
    {"i": 2, "ulv": 100, "name": "Phú Vi Hai", "hint": "Mở khi đạt cấp 100."},
    {"i": 3, "ulv": 200, "name": "Phú Vi Ba", "hint": "Mở khi đạt cấp 200."},
    {"i": 4, "ulv": 280, "name": "Phú Vi Bốn", "hint": "Mở khi đạt cấp 280."}
  ],
  "cap_percent": 30,
  "open_pill": {"djid": 114, "name": "Khai Phú Đan"},
  "material_names": {"114": "Khai Phú Đan"}
}
```

> **BON LECH SO DA CHOT TRONG PLAN NAY (so voi spec, doc truoc khi doi):**
> 1. Spec §2.1 ke `quality_names/colors/icons/weights` nhu 4 map rieng cua Nhuc Than. Plan gộp hết vào `qualities` (1 map duy nhat, key `key` = ten cua thechat) de **khong duplicate nguon that**; test Step 3 bat buoc `qualities[q].weight` khop `thechat_config.json:quality_weights[qualities[q].key]`.
> 2. Spec §9.2 de trong 3 dong cuoi cua `break_quality` o `ulv_require = 280`. Plan nang hai dong cuoi len **400 va 500** sau khi tra bang canh gioi that bang chinh ham `tq_realm_req()` (`class/rangesjj.php`, 86 canh): `100 = Luyen The`, `150 = Thoi The`, `200 = Tu Than`, `280 = Ngu Khi`, `400 = Truc Co`, `500 = Ngu Anh`. Do la so `[CHOT]`, chu du an co the ha ve 280 neu muon F2P leo nhanh.
> 3. **Ky tu CJK co the bi chinh cong cu Write/Edit chen vao chuoi tieng Viet** — da phat hien va sua 6 cho trong JSON o tren (`nắm chắc`, `khổ sở`, `đan thành`, `hóa thành`) ngay khi viet plan. Step 2 la buoc bat buoc MOI lan sua file, khong phai buoc phong xa tha.
> 4. **Namespace:** spec §3 ghi `class/function/tianfu.php` dung namespace rieng. Plan dung **`namespace player;`** theo le cua 23 file `class/function/*` da do truc tiep (`head -2 class/function/huyetmach.php` → `namespace player;`) — xem note "[CHOT namespace]" o Task 3 Step 5. Keu goi: `class/player.php` + `class/function/*` goi khong tien to; `game/*.php` (global) va `tests/*.php` goi `\player\tianfu_...`.

- [ ] **Step 2: Quet CJK + validate JSON** (day la buoc thuc, khong phai phong xa — loi o muc 3 tren phai het truoc khi commit)

Run:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -r '
$f="class/config/tianfu_config.json";
$s=file_get_contents($f);
preg_match_all("/[\x{4e00}-\x{9fff}\x{3000}-\x{303f}\x{ff00}-\x{ffef}]/u",$s,$m);
echo $m[0] ? "CJK FOUND: ".implode(" ",$m[0])."\n" : "no CJK\n";
$d=json_decode($s,true);
echo $d===null ? ("JSON INVALID: ".json_last_error_msg()."\n") : ("json ok, groups=".count($d["groups"]).", talents=".count($d["talents"]).", gates=".count($d["gates"]).", qualities=".count($d["qualities"])."\n");
'
```
Expected: `no CJK` va `json ok, groups=6, talents=18, gates=7, qualities=10`. Neu con CJK: sua chuoi sang tieng Viet co dau, chay lai den khi sach.

- [ ] **Step 3: Viet test bai** `tests/test_tianfu_config.php`:

```php
<?php
/**
 * test_tianfu_config.php — cai dat Thien Phu (Phu Cac suu tam): config + ham thuan tuy.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_config.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/tianfu.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: dinh luong cau truc =====
$defs = \player\tianfu_defs();
tassert('18 thien phu', count($defs), 18);
tassert('6 nhom', count(\player\tianfu_groups()), 6);
tassert('7 cong', count(\player\tianfu_config_all()['gates']), 7);
tassert('10 pham', count(\player\tianfu_config_all()['qualities']), 10);
tassert('id 1..12 lien tuc', array_keys($defs), array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18));
tassert('def id le', \player\tianfu_def(19), false);
tassert('def id 0', \player\tianfu_def(0), false);

// moi nhom dung 3 cai
$byGroup = array();
foreach ($defs as $d) { $byGroup[$d['group']] = isset($byGroup[$d['group']]) ? $byGroup[$d['group']] + 1 : 1; }
tassert('moi nhom 3 cai', array_values($byGroup), array(3,3,3,3,3,3));

// weight 5/4/3 theo thu tu trong nhom -> tong 72
$sum = 0; $pat = array();
foreach ($defs as $d) { $sum += intval($d['weight']); }
tassert('tong weight = 72', $sum, 72);
foreach (array(1,2,3,4,5,6) as $g) {
    $w = array();
    foreach ($defs as $d) { if (intval($d['group']) === $g) $w[] = intval($d['weight']); }
    $pat[$g] = implode(',', $w);
}
tassert('weight pattern nhom 1', $pat[1], '5,4,3');
tassert('weight pattern nhom 6', $pat[6], '5,4,3');

// moi cong co it nhat 1 chu (ke ca chu phu exp_monster)
$owners = array();
foreach ($defs as $d) {
    foreach (array_keys(\player\tianfu_contributions($d)) as $g) { $owners[$g] = true; }
}
foreach (array_keys(\player\tianfu_config_all()['gates']) as $g) {
    tassert('co chu: ' . $g, isset($owners[$g]), true);
}

// ===== B: pham chat khop nguồn Nhuc Than =====
$tc = json_decode(file_get_contents($ROOT . '/class/config/thechat_config.json'), true);
foreach (\player\tianfu_config_all()['qualities'] as $q => $qd) {
    $k = $qd['key'];
    tassert('weight q' . $q . ' khop thechat', floatval($qd['weight']), floatval($tc['quality_weights'][$k]));
    tassert('name q' . $q . ' khop thechat', $qd['name'], $tc['quality_names'][$k]);
    tassert('color q' . $q . ' khop thechat', $qd['color'], $tc['quality_colors'][$k]);
}
// lv_cap tang dan, mult tang dan
$caps = array(); $mults = array();
foreach (\player\tianfu_config_all()['qualities'] as $qd) { $caps[] = intval($qd['lv_cap']); $mults[] = floatval($qd['mult']); }
tassert('lv_cap 10..60', $caps, array(10,14,18,24,30,36,42,48,54,60));
tassert('mult 1.00..3.50', $mults, array(1.0,1.15,1.3,1.5,1.75,2.0,2.3,2.6,3.0,3.5));
tassert('lv_cap q0', \player\tianfu_lv_cap(0), 0);
tassert('lv_cap q11', \player\tianfu_lv_cap(11), 0);
tassert('lv_cap q10', \player\tianfu_lv_cap(10), 60);

// ===== C: gia =====
tassert('lv cost 0', \player\tianfu_lv_cost(0), array('tf' => 1, 'linh_thach' => 200));
tassert('lv cost 7', \player\tianfu_lv_cost(7)['linh_thach'], 375);
tassert('lv cost 47', \player\tianfu_lv_cost(47)['linh_thach'], 1375);
tassert('break q1', \player\tianfu_break_cost(1), array('tf' => 10, 'linh_thach' => 10000, 'ulv_require' => 0));
tassert('break q9', \player\tianfu_break_cost(9)['linh_thach'], 20000000);
tassert('break q10 het', \player\tianfu_break_cost(10), false);

// ===== D: cong thuc tram — clamp PHAI binding =====
// 24 * 0.25 * 1.50 * 1.12 = 10.08
tassert('calc dia+moc12', \player\tianfu_percent_calc(24, 0.25, 1.50, 12), 10.08);
// 42 * 0.25 * 2.30 = 24.15 (duoi tram, chua clamped)
tassert('calc tien 42', \player\tianfu_percent_calc(42, 0.25, 2.30, 0), 24.15);
// 48 * 0.25 * 2.60 = 31.2 -> clamp 30 (day la khac biet voi stat_cap 15 cua Mach Huyet)
tassert('calc than 48 clamped', \player\tianfu_percent_calc(48, 0.25, 2.60, 0), 30.0);
tassert('calc dao 60 ms30 clamped', \player\tianfu_percent_calc(60, 0.25, 3.50, 30), 30.0);
tassert('calc lv0', \player\tianfu_percent_calc(0, 0.25, 3.50, 30), 0.0);
tassert('calc sub exp_monster', \player\tianfu_percent_calc(48, 0.15, 2.60, 0), 18.72);

// ===== E: contributions / fx =====
tassert('contrib id1', \player\tianfu_contributions($defs[1]), array('exp_cultivate' => 0.25, 'exp_monster' => 0.15));
tassert('contrib id7', \player\tianfu_contributions($defs[7]), array('alchemy' => 0.25));
tassert('fx q1', \player\tianfu_fx_class(1), 0);
tassert('fx q2', \player\tianfu_fx_class(2), 0);
tassert('fx q3', \player\tianfu_fx_class(3), 1);
tassert('fx q5', \player\tianfu_fx_class(5), 2);
tassert('fx q6', \player\tianfu_fx_class(6), 3);
tassert('fx q7', \player\tianfu_fx_class(7), 4);
tassert('fx q10', \player\tianfu_fx_class(10), 5);
tassert('fx q0', \player\tianfu_fx_class(0), 0);
tassert('slot mo cv1', \player\tianfu_slot_open(1), array(1));
tassert('slot mo cv100', \player\tianfu_slot_open(100), array(1,2));
tassert('slot mo cv280', \player\tianfu_slot_open(280), array(1,2,3,4));
tassert('ten gate drop', \player\tianfu_gate_name('drop'), 'Ngưỡng rơi đồ');
tassert('ten gate la', \player\tianfu_gate_name('zzz'), 'zzz');
tassert('open pill', \player\tianfu_open_pill(), array('djid' => 114, 'name' => 'Khai Phú Đan'));

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 4: Chay test, xac nhan FAIL**

Run: `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_tianfu_config.php`
Expected: Fatal error `Call to undefined function player\tianfu_defs()` (hoac `Class 'tianfu_config_path' not found` neu quen `<?php`). Ngu han: file config da doc duoc nhung ham chua ton tai.

- [ ] **Step 5: Viet `class/function/tianfu.php`** (khoi Task 1 — cac task sau append them, KHONG sua tron):

```php
<?php
namespace player;

/**
 * tianfu.php — Thien Phu: Phu Cac suu tam (18 thien phu / 6 nhom / 7 cong van khi).
 *
 * Thien phu KHONG cong chi so chien dau (ugj/ufy/umaxhp/ubj/uxx). No chi tac dong
 * 7 cong van khi trong §5 cua spec; do la ranh gioi voi thechat / huyetmach / linhcan.
 * Config: class/config/tianfu_config.json. UI: game/tianfu*.php (prefix .tp-*).
 */

if (!function_exists(__NAMESPACE__ . '\\tianfu_config_path')) {
    function tianfu_config_path(){
        $paths = array(
            __DIR__ . '/../config/tianfu_config.json',
            __DIR__ . '/tianfu_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_config_all')) {
    function tianfu_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = tianfu_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $raw = file_get_contents($file);
        $decoded = json_decode($raw, true);
        $cache = is_array($decoded) ? $decoded : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_cfg_key')) {
    /** Doc mot khoa top-level, bao dam mang. */
    function tianfu_cfg_key($key){
        $cfg = tianfu_config_all();
        return isset($cfg[$key]) && is_array($cfg[$key]) ? $cfg[$key] : array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_groups')) {
    function tianfu_groups(){ return tianfu_cfg_key('groups'); }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_defs')) {
    function tianfu_defs(){ return tianfu_cfg_key('talents'); }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_def')) {
    function tianfu_def($id){
        $id = intval($id);
        $defs = tianfu_defs();
        return isset($defs[$id]) ? $defs[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_gate_def')) {
    function tianfu_gate_def($gate){
        $gates = tianfu_cfg_key('gates');
        return isset($gates[$gate]) ? $gates[$gate] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_gate_name')) {
    function tianfu_gate_name($gate){
        $d = tianfu_gate_def($gate);
        return $d ? $d['name'] : (string)$gate;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_gate_unit')) {
    function tianfu_gate_unit($gate){
        $d = tianfu_gate_def($gate);
        return $d ? $d['unit'] : '%';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_def')) {
    function tianfu_quality_def($q){
        $q = intval($q);
        $qs = tianfu_cfg_key('qualities');
        return isset($qs[$q]) ? $qs[$q] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_name')) {
    function tianfu_quality_name($q){
        $d = tianfu_quality_def($q);
        return $d ? $d['name'] : '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_color')) {
    function tianfu_quality_color($q){
        $d = tianfu_quality_def($q);
        return $d ? $d['color'] : '#9e9e9e';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_quality_icon')) {
    function tianfu_quality_icon($q){
        $d = tianfu_quality_def($q);
        return $d ? $d['icon'] : '⚪';
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_cap_percent')) {
    function tianfu_cap_percent(){
        $cfg = tianfu_config_all();
        return isset($cfg['cap_percent']) ? floatval($cfg['cap_percent']) : 30.0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lv_cap')) {
    /** Tran cap cua mot thien phu tai pham chat $q. */
    function tianfu_lv_cap($q){
        $d = tianfu_quality_def($q);
        return $d ? intval($d['lv_cap']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lv_cost')) {
    function tianfu_lv_cost($lv){
        $c = tianfu_cfg_key('lv_cost');
        $base = isset($c['linh_thach_base']) ? intval($c['linh_thach_base']) : 200;
        $per  = isset($c['linh_thach_per_lv']) ? intval($c['linh_thach_per_lv']) : 25;
        $tf   = isset($c['tf']) ? intval($c['tf']) : 1;
        return array('tf' => $tf, 'linh_thach' => $base + $per * intval($lv));
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_break_cost')) {
    /** Gia Dot Pham tu pham $q len $q+1; $q = 10 -> false (da tran). */
    function tianfu_break_cost($q){
        $q = intval($q);
        $b = tianfu_cfg_key('break_quality');
        if (!isset($b[$q])) return false;
        return array(
            'tf' => intval($b[$q]['tf']),
            'linh_thach' => intval($b[$q]['linh_thach']),
            'ulv_require' => intval($b[$q]['ulv_require'])
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_defs')) {
    function tianfu_slot_defs(){ return tianfu_cfg_key('slots'); }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_open')) {
    /** Cac so thu tu Phu Vi da mo theo cap do nhan vat. */
    function tianfu_slot_open($ulv){
        $ulv = intval($ulv);
        $out = array();
        foreach (tianfu_slot_defs() as $s) {
            if ($ulv >= intval($s['ulv'])) $out[] = intval($s['i']);
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_contributions')) {
    /** gate => per_lv. exp_monster la PHU CONG cua nhom Ngo Tinh (per_lv_sub), khong cong gop. */
    function tianfu_contributions($def){
        $out = array();
        if (!is_array($def) || !isset($def['gate'])) return $out;
        $out[$def['gate']] = isset($def['per_lv']) ? floatval($def['per_lv']) : 0.0;
        if (isset($def['per_lv_sub']) && floatval($def['per_lv_sub']) > 0) {
            $out['exp_monster'] = floatval($def['per_lv_sub']);
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_percent_calc')) {
    /**
     | Cong thuc DUY NHAT cho moi cong:
     |   % = min(cap, lv * per_lv * mult * (1 + milestone/100))
     | Clamp dat SAU phep nhan milestone (khac loi `stat_cap` cua Mach Huyet
     | — o do tram 15% khong bao gio binding, `docs/UI_HUYETMACH.md` §4.4).
     */
    function tianfu_percent_calc($lv, $perLv, $mult, $milestonePct){
        $raw = floatval($lv) * floatval($perLv) * floatval($mult) * (1 + floatval($milestonePct) / 100);
        $cap = tianfu_cap_percent();
        return round(min($cap, $raw), 2);
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_fx_class')) {
    /** Bac hieu ung 0..5 theo pham chat chu phu (bac 0 van PHAI thay halo tinh). */
    function tianfu_fx_class($quality){
        $q = intval($quality);
        if ($q <= 2) return 0;
        if ($q <= 4) return 1;
        if ($q === 5) return 2;
        if ($q === 6) return 3;
        if ($q <= 8) return 4;
        return 5;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_open_pill')) {
    function tianfu_open_pill(){
        $c = tianfu_cfg_key('open_pill');
        return array(
            'djid' => isset($c['djid']) ? intval($c['djid']) : 114,
            'name' => isset($c['name']) ? $c['name'] : 'Khai Phú Đan'
        );
    }
}
```

- [ ] **Step 6: Them require vao `class/player.php`**

Mo `class/player.php`, sau dong `require_once __DIR__ . '/function/chatmsg.php';` (`:27`) them:

```php
require_once __DIR__ . '/function/tianfu.php';
```

Ly do nam o player.php chu khong phai tung man: `cron/stamina_recovery.php` va `luonden.php` (namespace `\player`) cung goi `\player\tianfu_...` o Task 11 — dua vao `player.php:4-27` la noi duy nhat moi dong chay duoc bao cam.

- [ ] **Step 7: Lint + quet + test**

Run:
```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/function/tianfu.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/player.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_tianfu_config.php
```
Expected: `No syntax errors` x2 va `ALL PASS`.

- [ ] **Step 8: Commit**

```bash
git add class/config/tianfu_config.json class/function/tianfu.php class/player.php tests/test_tianfu_config.php
git commit -m "feat(tianfu): config Phu Cac 18 thien phu/6 nhom/7 cong + ham doc thuan tuy, clamp 30 binding"
```

---

### Task 2: Schema — 2 bang `player_tianfu*` + 4 cot `game1` + whitelist SELECT

**Files:**
- Create: `sql/sql_tianfu.sql`
- Create: `sql/tianfu_columns.php`
- Create: `tests/test_tianfu_schema.php`
- Modify: `class/player.php:302-311` (`player_allowed_columns()`), `:313-319` (`game1_update_allowed_columns()`)
- Modify: `tests/deploy_preflight.php:19-40` (`$SID_VARCHAR`)

**Interfaces:**
- Consumes: `tianfu_config_all()` (Task 1).
- Produces: bang `player_tianfu (sid, tianfu_id, quality, lv, slot, obtained_at)` voi PK `(sid, tianfu_id)`; bang `player_tianfu_log (id, sid, kind, tianfu_id, from_q, to_q, detail, created_at)`; 4 cot `game1.tianfu_id/_quality/_lv/_power`; mang `\player\player_allowed_columns()` da co 4 cot nay (=> `$player->tianfu_id` hydrate duoc trong `getplayer()`).

- [ ] **Step 1: Tao `sql/sql_tianfu.sql`** — CHI 2 bang, `CREATE TABLE IF NOT EXISTS`, khong mot cau ALTER nao (ly do o Step 2):

```sql
-- =====================================================
-- SQL Script: Thien Phu — Phu Cac suu tam
-- MySQL 5.5 compatible — NO DEFAULT CURRENT_TIMESTAMP
-- 4 cot game1 do `sql/tianfu_columns.php` them (guard information_schema)
-- =====================================================

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
  kind       VARCHAR(20) NOT NULL,
  tianfu_id  TINYINT(4)  NOT NULL,
  from_q     TINYINT(4)  DEFAULT 0,
  to_q       TINYINT(4)  DEFAULT 0,
  detail     VARCHAR(200) DEFAULT NULL,
  created_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_sid (sid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

`kind` ghi mot trong: `open | lvup | break | slot | migrate`.

- [ ] **Step 2: Tao `sql/tianfu_columns.php`** — MySQL 5.5 KHONG co `ALTER TABLE ... ADD COLUMN IF NOT EXISTS`, nen guard dat trong PHP (spec §2.2 yeu cau guard; day la cach dung guard that su, chay lai khong loi):

```php
<?php
/**
 * tianfu_columns.php — them 4 cot Thien Phu vao game1, idempotent.
 * Chay: "...php.exe" sql/tianfu_columns.php         (chi in ke hoach)
 *       "...php.exe" sql/tianfu_columns.php --apply  (thuc su ALTER)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$apply = in_array('--apply', $argv, true);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$want = array(
    'tianfu_id'     => "ADD COLUMN `tianfu_id` TINYINT(4) NOT NULL DEFAULT 0",
    'tianfu_quality'=> "ADD COLUMN `tianfu_quality` TINYINT(4) NOT NULL DEFAULT 0",
    'tianfu_lv'     => "ADD COLUMN `tianfu_lv` INT(11) NOT NULL DEFAULT 0",
    'tianfu_power'  => "ADD COLUMN `tianfu_power` INT(11) NOT NULL DEFAULT 0",
);

$moved = 0;
foreach ($want as $col => $ddl) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA='game' AND TABLE_NAME='game1' AND COLUMN_NAME=?");
    $st->execute(array($col));
    $has = intval($st->fetch(PDO::FETCH_ASSOC)['c']) === 1;
    if ($has) { echo "CO SAN   $col\n"; continue; }
    echo $apply ? "ALTER      $col\n" : "SE ADD     $col\n";
    if ($apply) { $db->exec("ALTER TABLE game1 $ddl"); }
    $moved++;
}
echo $apply
    ? ("XONG: da them $moved cot.\n")
    : ("DRY-RUN: $moved cot se duoc them. Chay lai voi --apply.\n");
```

- [ ] **Step 3: Ap schema vao DB local**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe sql/tianfu_columns.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe sql/tianfu_columns.php --apply
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe sql/tianfu_columns.php --apply
```
Expected: lan 1 `DRY-RUN: 4 cot se duoc them`, lan 2 `XONG: da them 4 cot`, **lan 3 `CO SAN` x4 va `XONG: da them 0 cot`** (day la bai test idempotent that, khong phai doan).
Roi nap bang:
```bash
D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysql.exe -u root -pwww.aae.ink game < sql/sql_tianfu.sql
D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysql.exe -u root -pwww.aae.ink game < sql/sql_tianfu.sql
```
Expected: chay 2 lan khong mot dong loi nao. Duong dan `mysql.exe` da do that tai `Extensions/MySQL5.5.29` (chu hoa `M`) — `ls Extensions/` kiem tra lai neu may khac cau truc; neu khong tim thay `mysql.exe` thi nap bang bang `$db->exec()` trong mot script tam thoi (kung khuon Step 2).

- [ ] **Step 4: Sua whitelist cot — DAY LA LOI SPEC CHUA NEP, DUNG QUEN**

`class/player.php:555-563` `getplayer()` khong dung `SELECT *`, no noi `SELECT $cols FROM game1` voi `$cols = implode(',', player_allowed_columns())`. Khong them 4 cot vao `player_allowed_columns()` (`:302-311`) thi `$player->tianfu_quality` **mai mai NULL**, Task 8-11 doc ra 0 het ma khong bao loi.

Sua `class/player.php:309` — dong cuoi cua mang:

```php
        'huyetmach_id','huyetmach_quality','huyetmach_awaken_stage','huyetmach_transform_cd',
        'tianfu_id','tianfu_quality','tianfu_lv','tianfu_power'
```

KHONG them 4 cot vao `game1_update_allowed_columns()` (`:313-319`): `tianfu_recalc_mirror()` (Task 3) dung prepared UPDATE rieng, va danh sach nay la chan bar cho `addplayersx()` — them vao la mo rong mat duoc ghi cua he thong khong can thiet.

- [ ] **Step 5: Cap nhat `tests/deploy_preflight.php`** — trong `$SID_VARCHAR` (`:19-40`) them 2 dong cuoi:

```php
    'player_tianfu'                 => 'sid',
    'player_tianfu_log'             => 'sid',
```

- [ ] **Step 6: Viet `tests/test_tianfu_schema.php`**

```php
<?php
/**
 * test_tianfu_schema.php — schema Thien Phu (Phu Cac) da ap vao DB local `game` chua.
 * Chay SAU khi: php sql/tianfu_columns.php --apply  VA  mysql ... game < sql/sql_tianfu.sql
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

function col($db, $table, $column) {
    $st = $db->prepare("SELECT COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='game' AND TABLE_NAME=? AND COLUMN_NAME=?");
    $st->execute(array($table, $column));
    return $st->fetch(PDO::FETCH_ASSOC);
}
function tbl($db, $table) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA='game' AND TABLE_NAME=?");
    $st->execute(array($table));
    return intval($st->fetch(PDO::FETCH_ASSOC)['c']) === 1; }
function idx($db, $table, $key) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA='game' AND TABLE_NAME=? AND INDEX_NAME=?");
    $st->execute(array($table, $key));
    return intval($st->fetch(PDO::FETCH_ASSOC)['c']) > 0; }

tassert('bang player_tianfu', tbl($db, 'player_tianfu'), true);
tassert('bang player_tianfu_log', tbl($db, 'player_tianfu_log'), true);
tassert('sid la VARCHAR(64)', intval(col($db, 'player_tianfu', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('log sid VARCHAR(64)', intval(col($db, 'player_tianfu_log', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('tianfu_id tinyint', col($db, 'player_tianfu', 'tianfu_id')['COLUMN_TYPE'], 'tinyint(4)');
tassert('quality default 1', col($db, 'player_tianfu', 'quality')['COLUMN_DEFAULT'], '1');
tassert('lv default 0', col($db, 'player_tianfu', 'lv')['COLUMN_DEFAULT'], '0');
tassert('slot default 0', col($db, 'player_tianfu', 'slot')['COLUMN_DEFAULT'], '0');
tassert('obtained_at cho NULL', col($db, 'player_tianfu', 'obtained_at')['COLUMN_DEFAULT'], null);
tassert('PK (sid,tianfu_id)', idx($db, 'player_tianfu', 'PRIMARY'), true);
tassert('KEY idx_sid_slot', idx($db, 'player_tianfu', 'idx_sid_slot'), true);
tassert('log kind varchar20', col($db, 'player_tianfu_log', 'kind')['COLUMN_TYPE'], 'varchar(20)');
tassert('log id auto_inc', strpos(col($db, 'player_tianfu_log', 'id')['COLUMN_TYPE'], 'int') !== false, true);

foreach (array('tianfu_id','tianfu_quality','tianfu_lv','tianfu_power') as $c) {
    tassert('game1.' . $c . ' ton tai', col($db, 'game1', $c) !== false, true);
    tassert('game1.' . $c . ' default 0', col($db, 'game1', $c)['COLUMN_DEFAULT'], '0');
}

// LANDMINE: getplayer() chi SELECT cac cot trong whitelist nay.
$cols = \player\player_allowed_columns();
foreach (array('tianfu_id','tianfu_quality','tianfu_lv','tianfu_power') as $c) {
    tassert('whitelist SELECT co ' . $c, in_array($c, $cols, true), true);
}
tassert('whitelist UPDATE khong mo rong', in_array('tianfu_power', \player\game1_update_allowed_columns(), true), false);

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 7: Chay test + preflight + lint**

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_tianfu_schema.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/deploy_preflight.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l class/player.php
```
Expected: `ALL PASS`; preflight in `DA NONG (varchar(64))` cho 2 bang Thien Phu va khong tang `$todo` so voi truoc khi sua file.

- [ ] **Step 8: Commit**

```bash
git add sql/sql_tianfu.sql sql/tianfu_columns.php tests/test_tianfu_schema.php class/player.php tests/deploy_preflight.php
git commit -m "feat(tianfu): schema player_tianfu + 4 cot game1, bo whitelist SELECT cua getplayer"
```

### Task 3: Doc/ghi DB + `tianfu_bonuses()` + hydration `getplayer()`

**Files:**
- Modify: `class/function/tianfu.php` (append xuong DUOI CUNG file — file chi khai bao `namespace player;` roi tung ham bọc guard, khong co khoi `}` nao can tranh)
- Modify: `class/player.php:157` (them 8 `var $tianfu*` vao ngay sau `var $huyetmach_can_transform;`, truoc dong `}` cua class `:158`)
- Modify: `class/player.php:859` (chen khoi hydration Thien Phu NGAY SAU dong `}` dong lai khoi Huyết Mach, truoc `$expRateIndex = min($i, ...)` o `:861`)
- Create: `tests/tianfu_test_lib.php` (helper seed dung chung cho Task 3/5/6/11)
- Create: `tests/test_tianfu_bonuses.php`

**Interfaces:**
- Consumes (Task 1): `tianfu_defs()`, `tianfu_def($id)`, `tianfu_contributions($def)`, `tianfu_quality_def($q)`, `tianfu_percent_calc()`, `tianfu_cfg_key('milestones')`, `tianfu_cap_percent()`.
- Produces (namespace `player`):
  - `tianfu_owned($sid, $dblj): array` — 18 phan tu, key = id, moi phan tu `array('id','have','quality','lv','slot','name','short','group','gate','icon','color')`; chua so huu thi `have=0, quality=0, lv=0, slot=0`.
  - `tianfu_activated($sid, $dblj): array` — chi cac dong `slot > 0`, key = `slot` (1..4), gia tri nhu `tianfu_owned` phan tu.
  - `tianfu_total_lv($rows): int` — tong `lv` cua **moi thien phu da so huu** (khong rieng chu phu).
  - `tianfu_milestone($total_lv): array` — `array('name','percent','next_name','next_percent','next_lv')`; chua dat moc dau thi `name='', percent=0`.
  - **`tianfu_bonuses($sid, $dblj): array`** — **15 key**: `exp_cultivate exp_monster drop alchemy forge_cost stamina breakthrough` (float %), `total_lv` (int), `milestone_name milestone_percent next_milestone_name next_milestone_percent next_milestone_lv`, `activated_count` (int), `power` (int).
  - `tianfu_recalc_mirror($sid, $dblj): array` — viet lai 4 cot `game1` tu `player_tianfu`, tra chinh mang 4 gia tri `array('tianfu_id','tianfu_quality','tianfu_lv','tianfu_power')`.
  - `tianfu_log($sid, $kind, $tianfu_id, $from_q, $to_q, $detail, $dblj): void` — `kind` trong `open|lvup|break|slot|migrate`.

> **[CHOT] Bo so cong khi nhieu thien phu cung mot cong:** `tianfu_bonuses()` **cong raw** cua moi thien phu dang bat (`lv * per_lv * mult`), nhan milestone, roi **clamp 30 MOT LAN cho ca cong** — khong clamp tung thien phu. Ly do: nhom Ngo Tinh co 3 thien phu cung giong `exp_cultivate`, neu clamp tung cai thi 2 o chu phu cung bang 1 o, vô nghia. Day la cach doc duy nhat cua spec §3; Step 3 co test bat buoc churn minh (`14.0`, khong phai `7.5`).

> **[CHOT] `tianfu_bonuses()` chi query `player_tianfu` MOT LAN** (dung `WHERE sid = ?`), khong query trong vong lap — dung ranh gio cua spec §3. `tianfu_activated()` dung lai `tianfu_owned()` nen cung chi 1 query.

- [ ] **Step 1: Tao `tests/tianfu_test_lib.php`** (helper seed — Task 5, 6, 11 cung `require` file nay):

```php
<?php
/**
 * tianfu_test_lib.php — helper seed nhan vat test cho he Thien Phu.
 * Dung chung voi tests/test_tianfu_bonuses.php, _lvup, _slot, _gates.
 * Khong in URL, khong tao trang bi — chi game1 + player_tianfu + playerdaoju.
 */
if (!function_exists('tf_test_db')) {
    function tf_test_db() {
        static $db = null;
        if ($db === null) {
            $db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        return $db;
    }
}

if (!function_exists('tf_seed_game1')) {
    /** Tu sinh dong game1 du cot NOT NULL khong default (le cua tests/e2e_fixture.php:42-67). */
    function tf_seed_game1($db, $sid, $uid, $uname, $ulv, $tf = 0) {
        $colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = 'game' AND TABLE_NAME = 'game1'
                  AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
                  AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
        $names = array(); $vals = array();
        foreach ($colsNN as $c) {
            $n = $c['COLUMN_NAME'];
            $names[] = $n;
            if ($n === 'sid')     { $vals[] = $db->quote($sid); continue; }
            if ($n === 'token')   { $vals[] = $db->quote('tfetest'); continue; }
            if ($n === 'endtime') { $vals[] = "DATE_ADD(NOW(), INTERVAL 6 HOUR)"; continue; }
            $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext'));
            $vals[] = $isStr ? "''" : "0";
        }
        foreach (array('uid' => intval($uid), 'uname' => $db->quote($uname), 'ulv' => intval($ulv),
                       'uyxb' => 5000000, 'uczb' => 500000, 'tf' => intval($tf),
                       'shenfen' => 1, 'sfzx' => 1) as $k => $v) {
            if (!in_array($k, $names, true)) { $names[] = $k; $vals[] = $v; }
            else {
                // cot da co trong NOT NULL list thi ep gia tri bang UPDATE phia duoi
            }
        }
        $db->exec("INSERT INTO game1 (" . implode(', ', $names) . ") VALUES (" . implode(', ', $vals) . ")");
        $db->prepare("UPDATE game1 SET uid = ?, uname = ?, ulv = ?, tf = ?, uyxb = 5000000, uczb = 500000
                      WHERE sid = ?")->execute(array(intval($uid), $uname, intval($ulv), intval($tf), $sid));
    }
}

if (!function_exists('tf_give_daoju')) {
    /** playerdaoju co PK (uid, djid) — bat buoc ghi ro uid. */
    function tf_give_daoju($db, $sid, $uid, $djid, $sum) {
        $db->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE djsum = ?")->execute(
            array($sid, intval($uid), intval($djid), intval($sum), intval($sum)));
    }
}

if (!function_exists('tf_ins_owned')) {
    /** Nhap 1 thien phu da so huu. $slot = 0 -> dang nam trong Phu Cac, chua bat. */
    function tf_ins_owned($db, $sid, $id, $quality, $lv, $slot = 0) {
        $db->prepare("INSERT INTO player_tianfu (sid, tianfu_id, quality, lv, slot, obtained_at)
                      VALUES (?, ?, ?, ?, ?, NOW())
                      ON DUPLICATE KEY UPDATE quality = VALUES(quality), lv = VALUES(lv),
                                              slot = VALUES(slot)")->execute(
            array($sid, intval($id), intval($quality), intval($lv), intval($slot)));
    }
}

if (!function_exists('tf_cleanup')) {
    function tf_cleanup($db, $sid) {
        $db->prepare("DELETE FROM player_tianfu WHERE sid = ?")->execute(array($sid));
        $db->prepare("DELETE FROM player_tianfu_log WHERE sid = ?")->execute(array($sid));
        $db->prepare("DELETE FROM playerdaoju WHERE sid = ?")->execute(array($sid));
        $db->prepare("DELETE FROM game1 WHERE sid = ?")->execute(array($sid));
        if (function_exists('player\\player_cache_clear')) {
            \player\player_cache_clear($sid);
        }
    }
}
```

> **Helper KHONG duoc khai bao `tassert()`**: ca ba he truoc (`test_huyetmach_*.php`, `test_linhcan_*.php`, `test_nguhanh.php`) tu khai bao `tassert()` thuan tuy o dau file test. Neu helper cung khai bao (du co bọc `function_exists`) thi file test go `require_once` helper **truoc** khi khai bao cua chinh no → `Fatal error: tassert already declared`. Moi file test Task 3/5/6/11 PHAI tu viet `tassert()` nhu mau o Step 2.

- [ ] **Step 2: Viet test FAIL truoc** — `tests/test_tianfu_bonuses.php`:

```php
<?php
/**
 * test_tianfu_bonuses.php — kiem tra cong thuc 15 key cua \player\tianfu_bonuses()
 * va ban sao game1.tianfu_* do tianfu_recalc_mirror() viet ra.
 * Chay SAU Task 2 (schema) + Task 3 (ham). CLI:
 *   "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_bonuses.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/tests/tianfu_test_lib.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

$db   = tf_test_db();
$TESTSID = str_repeat('f', 32);   // sid rieng cua he Thien Phu
$TESTUID = 990112;

tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 1000, 500);

// ===== 1) Chua so huu gi: bonuses phai ra toan 0, khong fatal =====
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('bonus keys = 15', count($B), 15);
tassert('empty exp_cultivate', $B['exp_cultivate'], 0.0);
tassert('empty total_lv', $B['total_lv'], 0);
tassert('empty milestone_name', $B['milestone_name'], '');
tassert('empty milestone_percent', $B['milestone_percent'], 0);
tassert('empty next milestone lv', $B['next_milestone_lv'], 60);
tassert('empty activated_count', $B['activated_count'], 0);
tassert('empty power', $B['power'], 0);

// ===== 2) So huu + hai thien phu cung mot cong: CONG raw roi clamp =====
// id 1: lv 20, q 4 (mult 1.50) -> 20*0.25*1.50 = 7.5
// id 2: lv 20, q 3 (mult 1.30) -> 20*0.25*1.30 = 6.5  => 14.0
// tong lv so huu = 40 < 60 nen milestone = 0
$O = \player\tianfu_owned($TESTSID, $db);
tassert('owned du 18 o', count($O), 18);
tassert('o 4 chua so huu -> have 0', $O[4]['have'], 0);
tf_ins_owned($db, $TESTSID, 1, 4, 20, 1);
tf_ins_owned($db, $TESTSID, 2, 3, 20, 2);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('o 1 da so huu', $O[1]['have'], 1);
tassert('o 1 lay ten tu config', $O[1]['name'], 'Ngộ Tính Nghịch Thiên');
$A = \player\tianfu_activated($TESTSID, $db);
tassert('activated = 2 dong', count($A), 2);
tassert('activated key theo so thu tu slot', (isset($A[1]) ? $A[1]['id'] : -1), 1);
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('two talents sum 14.0 (khong clamp tung cai)', $B['exp_cultivate'], 14.0);
tassert('exp_monster sub 0.15 x2', $B['exp_monster'], 8.4);
tassert('total_lv = 40', $B['total_lv'], 40);
tassert('activated_count = 2', $B['activated_count'], 2);
tassert('power = slot1+slot2', $B['power'], (4 * 150 + 20 * 8) + (3 * 150 + 20 * 8));

// ===== 3) Clamp 30 SAU khi nhan milestone, do tren 1 thien phu =====
// Ngat id 2 khoi Phu Vi de do dung mot minh id 1.
$db->prepare("UPDATE player_tianfu SET slot = 0 WHERE sid = ? AND tianfu_id = 2")->execute(array($TESTSID));
tf_ins_owned($db, $TESTSID, 1, 5, 60, 1);   // q5 mult 1.75 -> raw 60*0.25*1.75 = 26.25
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('tong lv so huu van tinh id 2 (slot 0)', $B['total_lv'], 80);
tassert('milestone dat moc 60', $B['milestone_name'], 'Khai Phú');
tassert('milestone percent', $B['milestone_percent'], 5.0);
tassert('next milestone 150', $B['next_milestone_lv'], 150);
tassert('26.25 x 1.05 = 27.56 (chua clamp)', $B['exp_cultivate'], 27.56);
tassert('exp_monster 15.75 x 1.05 = 16.54', $B['exp_monster'], 16.54);
// Bop id 3 (q10 mult 3.50, lv 60 -> raw 52.5) vao slot 2: 26.25 + 52.5 = 78.75 -> clamp 30
tf_ins_owned($db, $TESTSID, 3, 10, 60, 2);
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('clamp 30 sau khi cong hai cai', $B['exp_cultivate'], 30.0);
tassert('exp_monster cung clamp (47.25 x 1.05)', $B['exp_monster'], 30.0);
tassert('tong lv 140 -> moc van 5%', $B['milestone_percent'], 5.0);
tassert('power 2 slot bat', $B['power'], (5 * 150 + 60 * 8) + (10 * 150 + 60 * 8));

// ===== 4) Thien phu chua bat (slot 0) van tinh total_lv, KHONG tinh cong =====
tf_ins_owned($db, $TESTSID, 7, 8, 40, 0);   // Dan Dao, chua bat vao Phu Vi
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('total_lv tinh ca chua bat', $B['total_lv'], 180);
tassert('milestone nhao len 150', $B['milestone_name'], 'Thông Linh');
tassert('alchemy = 0 vi chua bat vao Phu Vi', $B['alchemy'], 0.0);
tassert('moc ke la 300', $B['next_milestone_lv'], 300);
tassert('power khong doi (id 7 slot 0)', $B['power'], (5 * 150 + 60 * 8) + (10 * 150 + 60 * 8));

// ===== 5) recalc_mirror: game1.tianfu_* = ban sao slot 1 + power aggregate =====
$M = \player\tianfu_recalc_mirror($TESTSID, $db);
tassert('mirror id = slot 1', $M['tianfu_id'], 1);
tassert('mirror quality', $M['tianfu_quality'], 5);
tassert('mirror lv', $M['tianfu_lv'], 60);
tassert('mirror power = bonuses power', $M['tianfu_power'], $B['power']);
$row = $db->prepare("SELECT tianfu_id, tianfu_quality, tianfu_lv, tianfu_power FROM game1 WHERE sid = ?");
$row->execute(array($TESTSID));
$g = $row->fetch(PDO::FETCH_ASSOC);
tassert('game1 id', intval($g['tianfu_id']), 1);
tassert('game1 quality', intval($g['tianfu_quality']), 5);
tassert('game1 lv', intval($g['tianfu_lv']), 60);
tassert('game1 power', intval($g['tianfu_power']), $B['power']);

// ===== 6) Khong co chu phu (slot 1 trong) -> 3 cot dau ve 0, power van tinh =====
$db->prepare("UPDATE player_tianfu SET slot = 0 WHERE sid = ? AND tianfu_id = 1")->execute(array($TESTSID));
$M = \player\tianfu_recalc_mirror($TESTSID, $db);
tassert('no master -> id 0', $M['tianfu_id'], 0);
tassert('no master -> quality 0', $M['tianfu_quality'], 0);
tassert('no master -> lv 0', $M['tianfu_lv'], 0);
tassert('power chi con slot 2 (id 3)', $M['tianfu_power'], (10 * 150 + 60 * 8));

// ===== 7) tianfu_log khong dc, co dong migrate =====
\player\tianfu_log($TESTSID, 'migrate', 1, 0, 5, 'test', $db);
$lc = $db->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind = 'migrate'");
$lc->execute(array($TESTSID));
tassert('log migrate', intval($lc->fetchColumn()), 1);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 3: Chay test, xac nhan FAIL kieu "undefined function"**

Run:
```bash
cd /d/XXJserver/phpstudy_pro/WWW && "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l tests/test_tianfu_bonuses.php && "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_bonuses.php
```
Expected: `-l` OK, sau do `Fatal error: Call to undefined function player\tianfu_bonuses()` (namespace that la `player` — xem note "[CHOT namespace]" o Step 5). Neu báo `Table 'game.player_tianfu' doesn't exist` → Task 2 chua chay `sql/sql_tianfu.sql`, quay lai Task 2.

- [ ] **Step 4: Append ham DB vao `class/function/tianfu.php`** (nguyen chan, PHP 5.6, comment khong dau):

```php
// ======================================================================
// Task 3 — doc/ghi DB + tong hop hieu ung
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_owned')) {
    /** 18 o luon du; chua so huu => have 0. DUNG MOT query cho toan mang. */
    function tianfu_owned($sid, $dblj){
        $rows = array();
        foreach (tianfu_defs() as $id => $d) {
            $rows[$id] = array(
                'id'      => intval($id),
                'have'    => 0,
                'quality' => 0,
                'lv'      => 0,
                'slot'    => 0,
                'name'    => $d['name'],
                'short'   => $d['short'],
                'group'   => intval($d['group']),
                'gate'    => $d['gate'],
                'icon'    => $d['icon'],
                'color'   => $d['color'],
            );
        }
        $st = $dblj->prepare("SELECT tianfu_id, quality, lv, slot FROM player_tianfu WHERE sid = ?");
        $st->execute(array($sid));
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $id = intval($r['tianfu_id']);
            if (!isset($rows[$id])) continue;          // id da bi xoa khoi config: bo qua
            $rows[$id]['have']    = 1;
            $rows[$id]['quality'] = intval($r['quality']);
            $rows[$id]['lv']      = intval($r['lv']);
            $rows[$id]['slot']    = intval($r['slot']);
        }
        return $rows;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_activated')) {
    /** Chi cac o dang bat; key = so thu tu Phu Vi (1..4). */
    function tianfu_activated($sid, $dblj){
        $out = array();
        foreach (tianfu_owned($sid, $dblj) as $row) {
            if ($row['slot'] > 0) $out[$row['slot']] = $row;
        }
        ksort($out);
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_total_lv')) {
    /** Tong Lv cua MOI thien phu da so huu (ke ca o nam trong Phu Cac). */
    function tianfu_total_lv($rows){
        $sum = 0;
        foreach ($rows as $r) {
            if (intval($r['have']) === 1) $sum += intval($r['lv']);
        }
        return $sum;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_power_of')) {
    /** Trong so chien luc cua mot thien phu dang bat (spec §2.2). */
    function tianfu_power_of($quality, $lv){
        return intval($quality) * 150 + intval($lv) * 8;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_milestone')) {
    /**
     | Moc tong Lv hien tai + moc ke tiep.
     | Milestones doc tu config, sap xep tang dan theo total_lv de
     | chu du an sau co them moc ma khong phai sua ham.
     */
    function tianfu_milestone($total_lv){
        $total_lv = intval($total_lv);
        $list = array();
        foreach (tianfu_cfg_key('milestones') as $m) {
            $list[] = array(
                'lv'  => intval($m['total_lv']),
                'pct' => floatval($m['bonus_percent']),
                'name'=> $m['name'],
            );
        }
        usort($list, function($a, $b){ return $a['lv'] - $b['lv']; });

        $cur  = array('lv' => 0, 'pct' => 0.0, 'name' => '');
        $next = array('lv' => 0, 'pct' => 0.0, 'name' => '');
        foreach ($list as $m) {
            if ($total_lv >= $m['lv']) {
                $cur = $m;
            } elseif ($next['lv'] === 0) {
                $next = $m;
            }
        }
        return array(
            'name'        => $cur['name'],
            'percent'     => $cur['pct'],
            'next_name'   => $next['name'],
            'next_percent'=> $next['pct'],
            'next_lv'     => intval($next['lv']),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_bonuses')) {
    /**
     | HAM DUY NHAT tra hieu ung Thien Phu cho moi noi dung (UI, player, 7 cong).
     | 15 key. CHI query player_tianfu MOT LAN (via tianfu_owned).
     |
     | Cong thuc van la `lv * per_lv * mult * (1 + milestone/100)` roi clamp
     | `cap_percent` — nhung vi mot cong co the nhieu hon mot thien phu giong
     | nhau (nhom Ngo Tinh co 3 o cung exp_cultivate), phan bo theo kieu:
     |   raw[g] = tong(lv_i * per_lv_i * mult_i)   -> tianfu_percent_calc(1, raw, 1, ms)
     | Ba tham so dau cua `tianfu_percent_calc` khi nhan (1, raw, 1) cho ra
     | dung `raw * (1 + ms/100)` va clamp MOT LAN — khong tao cong thuc thu hai.
     */
    function tianfu_bonuses($sid, $dblj){
        $gates = array('exp_cultivate', 'exp_monster', 'drop', 'alchemy',
                       'forge_cost', 'stamina', 'breakthrough');
        $out = array();
        foreach ($gates as $g) $out[$g] = 0.0;

        $rows      = tianfu_owned($sid, $dblj);
        $total_lv  = tianfu_total_lv($rows);
        $ms        = tianfu_milestone($total_lv);
        $raw       = array();
        foreach ($gates as $g) $raw[$g] = 0.0;

        $power = 0;
        $count = 0;
        foreach ($rows as $r) {
            if (intval($r['slot']) <= 0) continue;
            $def = tianfu_def($r['id']);
            if (!$def) continue;
            $qd  = tianfu_quality_def($r['quality']);
            $mult = $qd ? floatval($qd['mult']) : 1.0;
            foreach (tianfu_contributions($def) as $g => $perLv) {
                if (!isset($raw[$g])) continue;
                $raw[$g] += floatval($r['lv']) * floatval($perLv) * $mult;
            }
            $power += tianfu_power_of($r['quality'], $r['lv']);
            $count++;
        }

        foreach ($gates as $g) {
            $out[$g] = tianfu_percent_calc(1, $raw[$g], 1, $ms['percent']);
        }

        $out['total_lv']               = $total_lv;
        $out['milestone_name']         = $ms['name'];
        $out['milestone_percent']      = $ms['percent'];
        $out['next_milestone_name']    = $ms['next_name'];
        $out['next_milestone_percent'] = $ms['next_percent'];
        $out['next_milestone_lv']      = $ms['next_lv'];
        $out['activated_count']        = $count;
        $out['power']                  = $power;
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_log')) {
    /** $kind: open | lvup | break | slot | migrate. Khong lam bai hoc fail — log la phu. */
    function tianfu_log($sid, $kind, $tianfu_id, $from_q, $to_q, $detail, $dblj){
        $st = $dblj->prepare("INSERT INTO player_tianfu_log
            (sid, kind, tianfu_id, from_q, to_q, detail, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $st->execute(array($sid, $kind, intval($tianfu_id), intval($from_q),
                           intval($to_q), $detail === null ? null : mb_substr($detail, 0, 200)));
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_recalc_mirror')) {
    /**
     | Viet lai 4 cot game1.tianfu_* tu player_tianfu.
     | Ba cot dau = ban sao Phu Vi 1 (chu phu); cot thu tu = tong power ca 4 o bat.
     | Goi sau moi lan open / lvup / break / slot va khi `getplayer()` phat hien lech.
     */
    function tianfu_recalc_mirror($sid, $dblj){
        $act   = tianfu_activated($sid, $dblj);
        $mid   = isset($act[1]) ? intval($act[1]['id'])      : 0;
        $mq    = isset($act[1]) ? intval($act[1]['quality']) : 0;
        $mlv   = isset($act[1]) ? intval($act[1]['lv'])      : 0;
        $power = 0;
        foreach ($act as $r) {
            $power += tianfu_power_of($r['quality'], $r['lv']);
        }
        $st = $dblj->prepare("UPDATE game1 SET tianfu_id = ?, tianfu_quality = ?,
                                     tianfu_lv = ?, tianfu_power = ? WHERE sid = ?");
        $st->execute(array($mid, $mq, $mlv, $power, $sid));
        return array('tianfu_id' => $mid, 'tianfu_quality' => $mq,
                     'tianfu_lv' => $mlv, 'tianfu_power' => $power);
    }
}
```

> **`usort` + closure la khuon co san cua repo** (`game/paihang.php:71`, `game/thiencoccac.php:382`, `game/bagdj.php:41`), con `create_function()` bi deprecated tu PHP 5.3 va de loi thoat dau — **KHONG dung `create_function` o bat ky task nao.**

- [ ] **Step 5: Hydration trong `class/player.php`**

5a. Chen 8 khai bao `var` vao `class/player.php` NGAY SAU `var $huyetmach_can_transform;` (`:157`), truoc dong dong class `:158`:

```php
    // Thien Phu: thuoc tinh tam tinh khi getplayer()
    var $tianfu_id;
    var $tianfu_quality;
    var $tianfu_lv;
    var $tianfu_power;
    var $tianfu_name;
    var $tianfu_color;
    var $tianfu_icon;
    var $tianfu_bonus;
```

5b. Chen khoi hydration **ngay sau dong `}` dong lai khoi Huyết Mach** (`class/player.php:859`, truoc `$expRateIndex = min($i, count($rangesexp) - 1);` o `:861`):

```php
    // Thien Phu: bonus 7 cong + anh xa chu phu. KHONG cong ugj/ufy/umaxhp/ubj/uxx
    // (ranh gioi cua 3 he truoc; thien phu chi lam viec o ti le, xem spec §1).
    $player->tianfu_bonus = array();
    $player->tianfu_name  = '';
    $player->tianfu_color = '#9e9e9e';
    $player->tianfu_icon  = '';
    if (function_exists(__NAMESPACE__ . '\\tianfu_bonuses')) {
        $tfB = tianfu_bonuses($player->sid, $dblj);
        $player->tianfu_bonus = $tfB;

        $tfMaster = intval($player->tianfu_id);
        if ($tfMaster > 0) {
            $tfDef = tianfu_def($tfMaster);
            if ($tfDef) {
                // Ten lay cua thien phu, mau + icon lay theo PHAM CHAT (giong cach
                // thechat/huyetmach doi mau pham chat), khong lay mau cua nhom.
                $player->tianfu_name  = $tfDef['name'];
                $player->tianfu_color = tianfu_quality_color(intval($player->tianfu_quality));
                $player->tianfu_icon  = tianfu_quality_icon(intval($player->tianfu_quality));
            }
        }

        // Tu canh minh: ban sao game1 lech so voi player_tianfu (thua tu he cu,
        // ghi that bai giua chung) -> viet lai de UI khong doc so cu.
        if ($tfB['power'] !== intval($player->tianfu_power)) {
            $mirror = tianfu_recalc_mirror($player->sid, $dblj);
            $player->tianfu_id      = $mirror['tianfu_id'];
            $player->tianfu_quality = $mirror['tianfu_quality'];
            $player->tianfu_lv      = $mirror['tianfu_lv'];
            $player->tianfu_power   = $mirror['tianfu_power'];
        }
    }
```

> **[CHOT namespace — LECH THU BA so voi spec §3]:** Spec ghi `class/function/tianfu.php` dung namespace rieng `tianfu`. **Plan dung `namespace player;`** vi da do that: toan bo 23 file trong `class/function/` (gom `huyetmach.php`, `thechat.php`, `linhcan.php`, `luonden.php`) mo dau bang `namespace player;`, va `class/player.php:830` goi `huyetmach_calc_bonuses(...)` **khong tien to** dua tren dieu do. Ngoai ra `game/*.php` la **global namespace**, nen trang UI phai goi day du `\player\tianfu_owned(...)`. **Quy tac cho ca plan:** file function khai bao `namespace player;` (Task 1 Step 5); cac ham trong `class/function/tianfu.php` goi nhau khong tien to; `class/player.php` goi khong tien to (cung namespace `player`); `game/*.php` va `tests/*.php` goi `\player\tianfu_...`. Guard `function_exists(__NAMESPACE__ . '\\tianfu_bonuses')` o `class/player.php` vi vay dang `\player\tianfu_bonuses` — dung het khuon Huyết Mach.

> **Tu canh minh chi ghi khi thuc su lech** (`$tfB['power'] !== intval($player->tianfu_power)`): nhan vat chua so huu thien phu nao thi `power = 0` va `game1.tianfu_power = 0` san → `getplayer()` khong phat sinh them UPDATE nao. Khi `lvup`/`break`/`slot` da goi `tianfu_recalc_mirror()` truc tiep thi den lan load sau gia tri khop, cung khong ghi lai.


- [ ] **Step 6: Quet ky tu la + lint + test + commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]|[\x{4e00}-\x{9fff}]' class/function/tianfu.php class/player.php tests/tianfu_test_lib.php tests/test_tianfu_bonuses.php
# Expected: KHONG co dong nao trong 4 file PHP tren (CJK + smart quotes = 0). Chu co dau
# trong chuoi UI la hop le, vi du 'Khai Phú' trong config JSON.
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l class/function/tianfu.php && "$P" -l class/player.php && "$P" -l tests/tianfu_test_lib.php && "$P" -l tests/test_tianfu_bonuses.php
"$P" tests/test_tianfu_bonuses.php
"$P" tests/test_tianfu_config.php     # khong duoc hong Regression
git add class/function/tianfu.php class/player.php tests/tianfu_test_lib.php tests/test_tianfu_bonuses.php
git commit -m "feat(tianfu): tianfu_bonuses + mirror game1 + hydration getplayer (Task 3)"
```
Expected: `ALL PASS` toan bo dong tassert trong file (khong con dong FAIL). Neu `FAIL exp_monster sub 0.15 x2` → kiem tra `tianfu_contributions()` co tra `exp_monster` cho ca 3 o nhom Ngo Tinh hay khong (Task 1 Step 6).

### Task 4: Khai Phú Đan (djid 114) + `tianfu_open()`

**Files:**
- Modify: `class/config/daoju_config.json` (them khoa `"114"` sau khoi `"113"`)
- Modify: `game/shangdian.php:343` (bo `114` khoi danh sach loc cua hang)
- Modify: `game/use_dj.php` (them nhanh `tianfu_open` NGAY TRUOC `} else {` cua nhanh `linhcan_wash` ket thuc o `:89`)
- Modify: `class/function/tianfu.php` (append `tianfu_weighted_pick` + `tianfu_open`)
- Create: `tests/test_tianfu_open.php`

**Interfaces:**
- Consumes (Task 1/2/3): `tianfu_open_pill()`, `tianfu_defs()`, `tianfu_quality_def()`, `tianfu_owned()`, `tianfu_log()`, bang `player_tianfu`, vat pham 114 trong `playerdaoju`.
- Produces:
  - `tianfu_weighted_pick(array $weights): int|string` — key duoc chon theo ti le (dung `mt_rand(1, $sum)`).
  - **`tianfu_open($sid, $dblj): array`** — `array('ok' => bool, 'talent' => array|false, 'quality' => int, 'is_new' => bool, 'msg' => string)`. `talent` = nguyen `tianfu_def($id)` (co `name/icon/color/short/group/gate`). Tieu 1 dan 114, `msg` co dau (tieng Viet that) vi hien truc tiep cho nguoi dung.
  - `tianfu_open_count($sid, $dblj): int` — dem so lan da khai mo (doc `player_tianfu_log WHERE kind='open'`), dung cho trang KHAI PHU.

**Data mau (nguyen chan, chen sau block `"113"`):**

```json
  "114": {
    "djname": "Khai Phú Đan",
    "djzl": "114",
    "djinfo": "Đan cô đúc từ thiên phú chi khí. Uống một lần là khai mở một thiên phú bẩm sinh; nếu thiên phú ấy đã có trong Phu Cac thì nó tăng thêm một phẩm chất — không lần nào uổng phí.",
    "djid": 114,
    "djyxb": 50000,
    "djjg": 0,
    "use_effect": { "type": "tianfu_open" }
  }
```

> `djyxb 50000` = bang `109/110/111` (`daoju_config.json`, da do: 109/110 = 50000, 111 = 30000, 113 = 50000) → kinh te khong lech (spec §2.3). **`111` la 30000 chu khong phai 50000** — do la muc cua Tẩy Linh Đan, khong dung theo.

- [ ] **Step 1: Viet test FAIL truoc** — `tests/test_tianfu_open.php`:

```php
<?php
/**
 * test_tianfu_open.php — Khai Phu Dan 114: tru dan, roll co trong, khong mat lan nao.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_open.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/tests/tianfu_test_lib.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

$db = tf_test_db();
$TESTSID = str_repeat('f', 32);
$TESTUID = 990112;
tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 1000, 500);

// ===== A: trong dan -> fail, khong ghi gi =====
$R = \player\tianfu_open($TESTSID, $db);
tassert('khong dan -> ok false', $R['ok'], false);
tassert('thong bao ro', $R['msg'] !== '', true);
tassert('khong co dong player_tianfu nao', \player\tianfu_total_lv(\player\tianfu_owned($TESTSID, $db)), 0);
tassert('log khong bi nhiem', \player\tianfu_open_count($TESTSID, $db), 0);

// ===== B: 300 lan khai mo -> 18 o deu bat duoc (weight > 0 ca 18) =====
tf_give_daoju($db, $TESTSID, $TESTUID, 114, 400);
$seenTalent = array(); $seenQuality = array();
for ($i = 0; $i < 300; $i++) {
    $R = \player\tianfu_open($TESTSID, $db);
    if (!$R['ok']) { echo "FAIL open lan $i: " . $R['msg'] . "\n"; $fails++; break; }
    $seenTalent[$R['talent']['id']] = true;
    $seenQuality[$R['quality']] = true;
}
tassert('18/18 thien phu deu co trong roll', count($seenTalent), 18);
tassert('pham chat 1..10 deu ra it nhat 1 lan', count($seenQuality), 10);
tassert('dan bi tru het 300', \player\tianfu_open_count($TESTSID, $db), 300);

$O = \player\tianfu_owned($TESTSID, $db);
$have = 0;
foreach ($O as $r) { if ($r['have'] === 1) $have++; }
tassert('du 18 o da so huu', $have, 18);

// ===== C: tru dan dung so con lai =====
$row = $db->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND uid = ? AND djid = 114");
$row->execute(array($TESTSID, $TESTUID));
tassert('con 100 dan', intval($row->fetchColumn()), 100);

// ===== D: khong mat lan — trung o thi +1 pham chat =====
// Xoa toan bo, dung 1 dan ep vao mot o cu the bang cach kiem tra ket qua vong lap.
$db->exec("DELETE FROM player_tianfu WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM player_tianfu_log WHERE sid = '$TESTSID'");
$first = \player\tianfu_open($TESTSID, $db);
tassert('lan dau = thien phu moi', $first['is_new'], true);
$again = \player\tianfu_open($TESTSID, $db);
while ($again['talent']['id'] !== $first['talent']['id']) {
    $again = \player\tianfu_open($TESTSID, $db);
}
tassert('lan trung van ok', $again['ok'], true);
tassert('lan trung is_new false', $again['is_new'], false);
$cur = \player\tianfu_owned($TESTSID, $db);
if ($first['is_new'] && $first['quality'] < 10) {
    tassert('pham chat tang dung 1', $cur[$first['talent']['id']]['quality'], $first['quality'] + 1);
} else {
    tassert('da tran pham thi giu nguyen', $cur[$first['talent']['id']]['quality'], 10);
}
$cnt = $db->query("SELECT COUNT(*) FROM player_tianfu WHERE sid = " . $db->quote($TESTSID))->fetchColumn();
tassert('chi con DUNG 1 dong player_tianfu sau 2 lan trung o', intval($cnt), 1);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 2: Chay, xac nhan FAIL `Call to undefined function player\tianfu_open()`**

Run: `P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"; "$P" -l tests/test_tianfu_open.php && "$P" tests/test_tianfu_open.php`

- [ ] **Step 3: Them 114 vao `class/config/daoju_config.json`** — chen block JSON o tren sau dong `"113": {...}` (nhớ thêm dấu `,` cuối block 113 nếu chưa có). Validate:

```bash
cd /d/XXJserver/phpstudy_pro/WWW && "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -r "\$j=json_decode(file_get_contents('class/config/daoju_config.json'),true); if(!\$j){echo 'JSON FAIL';exit(1);} echo count(\$j),' items; 114=',\$j['114']['djname'],' type=',\$j['114']['use_effect']['type'],PHP_EOL;"
```
Expected: `78 items; 114=Khai Phú Đan type=tianfu_open` (truoc do la 77 — da do `daoju_config.json` co 77 khoa).

- [ ] **Step 4: Sua `game/shangdian.php:343`** — `114` phai MUA duoc o cua hang:

```php
// CU:  if (intval($d['djid']) == 112 || intval($d['djid']) == 113 || intval($d['djid']) == 114) continue;
// MOI: 112/113 van an (chat loc cua hang), 114 hien ban:
        if (intval($d['djid']) == 112 || intval($d['djid']) == 113) continue;
```

- [ ] **Step 5: Nhanh `tianfu_open` trong `game/use_dj.php`** — chen TRUOC dong `} else {` cua nhanh `linhcan_wash` (khuon `game/use_dj.php:85-89`, da do):

```php
            } elseif ($effectType === 'tianfu_open') {
                // Khai Phu Dan: KHONG reroll o day — dan mo trang KHAI PHU (Task 10)
                // de nguoi choi xem bang ty le va nhan ket qua co hieu ung.
                $openCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
                header("Location: game.php?cmd=$openCmd");
                exit;
```

> **Ten route la `tianfu_open`** — dung spec §4.7 va dung convention cua `game.php:349-370` (`<feature>_<verb>`: `linhcan_wash`, `linhcan_quest`, `huyetmach_upgrade`, `thechat_upgrade`; trong map **khong co** route nao hau to `_page`). Ban dau plan nay viet `tianfu_open_page` vi lo "chung ten" voi `use_effect.type = tianfu_open` cua dan 114 va voi ham `\player\tianfu_open()` — ly do do **khong dung**: `cmd` (URL, decode roi `parse_str`) va `use_effect.type` (doc tu `daoju_config.json`) la hai khong gian ten khac nhau, con ham thi nam trong `namespace player`. Da doi lai cho khop spec. Kiem tra sau Task 10: `grep -n "'tianfu_open'" game.php` phai tra **dung 1 dong**.

- [ ] **Step 6: Append `tianfu_weighted_pick` + `tianfu_open` + `tianfu_open_count` vao `class/function/tianfu.php`**

```php
// ======================================================================
// Task 4 — Khai Phu Dan
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_weighted_pick')) {
    /**
     | Chon 1 key theo ti le. $weights: key => so duong (co the le).
     | Truong hop le: tong = 0 -> false (khong bao gio xay ra vi Task 1
     | test bat weight 18 o = 72 va 10 pham = 100.00).
     */
    function tianfu_weighted_pick($weights){
        if (!is_array($weights) || count($weights) === 0) return false;
        $scaled = array(); $sum = 0;
        foreach ($weights as $k => $w) {
            $w = floatval($w);
            if ($w <= 0) continue;
            $scaled[$k] = $w;
            $sum += $w;
        }
        if ($sum <= 0) return false;
        // Lam tron ve so nguyen de mt_rand khong bi mat do chinh xac: bac thap nhat
        // cua 10 pham chat la 0.09 -> nhan 100 = 9 lan trong 10009.
        $factor = 100;
        $pick = mt_rand(1, intval(round($sum * $factor)));
        $acc  = 0;
        foreach ($scaled as $k => $w) {
            $acc += intval(round($w * $factor));
            if ($pick <= $acc) return $k;
        }
        return array_key_last($scaled);
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_open')) {
    /**
     | Uong 1 Khai Phu Dan (114) -> mo 1 thien phu.
     |   - Chon thien phu theo weight cua 18 o (config.talents[*].weight).
     |   - Chon pham chat theo weight cua 10 bac (config.qualities[*].weight).
     |   - O CHUA so huu  -> INSERT voi pham chat vua roll (lan dau co the ra
     |     Phàm Phẩm; do la y nghia cua "khai mo", khong phai trung phat).
     |   - O DA so huu    -> +1 pham chat, tran 10 (spec §5: trung khong mat).
     | Tru dan bang UPDATE co dieu kien djsum >= 1 + rowCount() -> chong double-click
     | (route `?cmd=` da 1 lan, nhung cron/f2 ngam van co the gui POST trung).
     */
    function tianfu_open($sid, $dblj){
        $pill   = tianfu_open_pill();
        $djid   = $pill['djid'];
        $bad    = array('ok' => false, 'talent' => false, 'quality' => 0,
                        'is_new' => false, 'msg' => '');

        // 1) Tru dan truoc khi roll — neu khong co dan thi khong roll, khong ghi gi.
        $ded = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - 1
                               WHERE sid = ? AND djid = ? AND djsum >= 1");
        $ded->execute(array($sid, $djid));
        if ($ded->rowCount() !== 1) {
            $bad['msg'] = 'Không còn ' . $pill['name'] . ' trong túi.';
            return $bad;
        }

        // 2) Roll
        $talentWeights = array();
        foreach (tianfu_defs() as $id => $d) $talentWeights[$id] = floatval($d['weight']);
        $picked = tianfu_weighted_pick($talentWeights);
        if ($picked === false) {
            // Ly ra khong xay ra; hoan lai dan de nguoi choi khong mat oan.
            $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + 1 WHERE sid = ? AND djid = ?")
                 ->execute(array($sid, $djid));
            $bad['msg'] = 'Khai mở thất bại, đan đã được hoàn lại.';
            return $bad;
        }
        $id  = intval($picked);
        $def = tianfu_def($id);

        $qWeights = array();
        foreach (tianfu_cfg_key('qualities') as $q => $qd) $qWeights[$q] = floatval($qd['weight']);
        $rolledQ = intval(tianfu_weighted_pick($qWeights));

        // 3) Ghi — MOT lenh duoc, khong can transaction, khong the tao dong trung
        //    (PK (sid, tianfu_id)); `FOR UPDATE` da bi loai vi vo nghia voi dong chua ton tai.
        $pre = $dblj->prepare("SELECT quality FROM player_tianfu WHERE sid = ? AND tianfu_id = ?");
        $pre->execute(array($sid, $id));
        $curQ = $pre->fetch(PDO::FETCH_ASSOC);
        $isNew = ($curQ === false);

        $qIns = ($rolledQ >= 1 && $rolledQ <= 10) ? $rolledQ : 1;
        $dblj->prepare("INSERT INTO player_tianfu (sid, tianfu_id, quality, lv, slot, obtained_at)
                        VALUES (?, ?, ?, 0, 0, NOW())
                        ON DUPLICATE KEY UPDATE quality = LEAST(10, quality + 1)")
             ->execute(array($sid, $id, $qIns));

        // Pham chat THUC TE sau khi ghi (o da co san 10 thi van la 10, khong phai 11).
        $after = $dblj->prepare("SELECT quality FROM player_tianfu WHERE sid = ? AND tianfu_id = ?");
        $after->execute(array($sid, $id));
        $quality = intval($after->fetchColumn());
        $fromQ   = $isNew ? 0 : intval($curQ['quality']);

        tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'open', $id, $fromQ, $quality,
                   $isNew ? 'khai mo' : 'luyen pham', $dblj);

        $qdef = tianfu_quality_def($quality);
        return array(
            'ok'      => true,
            'talent'  => $def,
            'quality' => $quality,
            'is_new'  => $isNew,
            'msg'     => ($isNew ? 'Khai mở được ' : 'Luyện thêm phẩm chất cho ')
                        . $def['name'] . ' — ' . $qdef['name'],
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_open_count')) {
    function tianfu_open_count($sid, $dblj){
        $st = $dblj->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind = 'open'");
        $st->execute(array($sid));
        return intval($st->fetchColumn());
    }
}
```

> **`array_key_last()` la PHP 7.3+ — KHONG dung duoc tren PHP 5.6.** Thang fallback cuoi cung cua `tianfu_weighted_pick` phai viet:
> ```php
> $keys = array_keys($scaled);
> return $keys[count($keys) - 1];
> ```
> (do la dong DUY NHAT trong ca plan dung den thu tu mang; khi thay `array_key_last` trong bat ky task nao sau nay, coi nhu loi va sua ngay.)

> **Vi sao buoc 3 khong can `beginTransaction()`:** phep ghi la **mot lenh duoc** `INSERT ... ON DUPLICATE KEY UPDATE quality = LEAST(10, quality + 1)` — MySQL tu lock row theo `PK (sid, tianfu_id)`, nen double-click / f2 ngam khong the tao dong trung hay tang 2 pham. `deledjsum()` cua repo cung khong dung transaction (da do `game/use_dj.php:44-47`), ne than ma chep theo cach do: **tru dan bang `UPDATE ... WHERE djsum >= 1` + `rowCount() === 1`** (buoc 1), day la kiem soat duy nhat can thiet. Neu ve sau them mot phep ghi thu ba (vi du cong `linh_thach` vao cung luong), **luc do moi boc transaction** va ghi ro ly do trong comment.

- [ ] **Step 7: Lint + quet ky tu la + test + commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]|[\x{4e00}-\x{9fff}]|array_key_last' class/function/tianfu.php tests/test_tianfu_open.php game/use_dj.php
# Expected: 0 dong
for f in class/function/tianfu.php tests/test_tianfu_open.php game/use_dj.php game/shangdian.php; do "$P" -l $f; done
"$P" tests/test_tianfu_open.php && "$P" tests/test_tianfu_bonuses.php && "$P" tests/test_tianfu_config.php
git add class/config/daoju_config.json class/function/tianfu.php game/shangdian.php game/use_dj.php tests/test_tianfu_open.php
git commit -m "feat(tianfu): Khai Phu Dan 114 + tianfu_open() (Task 4)"
```
Expected: `ALL PASS` ca 3 file test. **Vong lap 300 lan trong test A/B co the mat ~2 giay — do la gia tri cua no (bao trum 18 o deu ra), dung rut xuong 30 lan.**

### Task 5: Luyen Lv (`tf` + Linh Thạch) va Dot Pham — `tianfu_lvup` / `tianfu_break` / `*_rows`

**Files:**
- Modify: `class/function/tianfu.php` (append)
- Create: `tests/test_tianfu_lvup.php`

**Interfaces:**
- Consumes (Task 1/2/3): `tianfu_def()`, `tianfu_lv_cap($q)`, `tianfu_lv_cost($lv)`, `tianfu_break_cost($q)`, `tianfu_quality_def()`, `tianfu_recalc_mirror()`, `tianfu_log()`.
- Produces:
  - `tianfu_lvup($sid, $dblj, $id): array('ok','lv','cap','cost','msg')` — +1 Lv, **100% thanh cong**, tieu `1` diem `tf` + `200 + 25*lv` Linh Thạch (`uyxb`).
  - `tianfu_break($sid, $dblj, $id): array('ok','quality','cost','msg')` — +1 pham chat, **100% thanh cong**, doi hoi `lv == lv_cap(quality)`, `quality < 10`, du `tf`/`uyxb`/`ulv`.
  - `tianfu_lvup_rows($sid, $dblj, $id): array` — 3 dong `{text, ok}` cho panel Nang Cap (diem tf / Linh Thạch / tran Lv).
  - `tianfu_break_rows($sid, $dblj, $id): array` — 4 dong `{text, ok}` cho panel Dot Pham (Lv tran / tf / Linh Thạch / canh gioi).
  - `tianfu_player_wallet($sid, $dblj): array('tf'=>int,'uyxb'=>int,'ulv'=>int)` — doc **truc tiep** `game1` (khong qua `$player`), dung cho CLI test va nut UI.

> **`tf` la diem Thien Phu con lai trong `game1.tf`** (cot da ton tai tu he cu, `class/player.php:308` whitelist co san `tf,tfgj,tfxy,tfsb,tfxx,tfhp,tffy,tfbj`). Task 12 hoan `tfgj+tfxy+...` ve `tf` va xoa 6 cot con lai. **Linh Thạch = `game1.uyxb`** — da do: `class/function/huyetmach.php:670` va `linhcan.php:462` deu doi `uyxb` ra "Linh Thạch".

- [ ] **Step 1: Viet test FAIL truoc** — `tests/test_tianfu_lvup.php`:

```php
<?php
/**
 * test_tianfu_lvup.php — Nang Cap (100%) va Dot Pham (100%) + hang dieu kien UI.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_lvup.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/tests/tianfu_test_lib.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

$db = tf_test_db();
$TESTSID = str_repeat('f', 32);
$TESTUID = 990112;
tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 1000, 500);   // cv1000, 500 diem tf

// ===== A: o chua so huu -> tu choi =====
$R = \player\tianfu_lvup($TESTSID, $db, 5);
tassert('chua so huu -> ok false', $R['ok'], false);
tassert('diem tf khong bi an', \player\tianfu_player_wallet($TESTSID, $db)['tf'], 500);

tf_ins_owned($db, $TESTSID, 1, 1, 0, 1);          // Phàm Phẩm, lv 0, slot 1
$O = \player\tianfu_owned($TESTSID, $db);
tassert('slot 1 = id 1 (chu phu)', $O[1]['slot'], 1);

// ===== B: lvup 1 -> tieu 1 tf + 200 LT, lv = 1 =====
$db->prepare("UPDATE game1 SET uyxb = 100000 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_lvup($TESTSID, $db, 1);
tassert('lvup ok', $R['ok'], true);
tassert('lv = 1', $R['lv'], 1);
tassert('cost dung cong thuc', $R['cost'], array('tf' => 1, 'linh_thach' => 200));
$W = \player\tianfu_player_wallet($TESTSID, $db);
tassert('tf 500 -> 499', $W['tf'], 499);
tassert('LT 100000 -> 99800', $W['uyxb'], 99800);

// ===== C: tran Lv cua pham chat 1 = 10 -> khong lvup duoc nua =====
for ($i = 1; $i < 10; $i++) { \player\tianfu_lvup($TESTSID, $db, 1); }
$R = \player\tianfu_lvup($TESTSID, $db, 1);
tassert('lv 10 la tran q1', $R['ok'], false);
tassert('msg bao tran', strpos($R['msg'], 'trần') !== false, true);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('lv dung bang 10', $O[1]['lv'], 10);

// ===== D: break khi chua dat tran -> bi chan =====
tf_ins_owned($db, $TESTSID, 4, 1, 5, 0);          // id 4, lv 5 < 10
$R = \player\tianfu_break($TESTSID, $db, 4);
tassert('break khi lv < tran -> false', $R['ok'], false);

// ===== E: break 1..3 khong doi hoi canh gioi (ulv 1000 du) =====
$R = \player\tianfu_break($TESTSID, $db, 1);
tassert('break q1 -> q2 ok', $R['ok'], true);
tassert('quality = 2', $R['quality'], 2);
tassert('cost break q1', $R['cost'], array('tf' => 10, 'linh_thach' => 10000, 'ulv_require' => 0));

// ===== F: break q4 can ulv 100 — nhan vat cv 50 phai bi chan =====
tf_ins_owned($db, $TESTSID, 5, 4, 24, 0);         // dia pham, lv = tran 24
$db->prepare("UPDATE game1 SET ulv = 50, uyxb = 900000, tf = 300 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_break($TESTSID, $db, 5);
tassert('cv 50 khong du Dot Pham len thien pham', $R['ok'], false);
tassert('msg canh gioi', strpos($R['msg'], 'cấp 100') !== false, true);
$db->prepare("UPDATE game1 SET ulv = 100 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_break($TESTSID, $db, 5);
tassert('cv 100 du', $R['ok'], true);
tassert('q4 -> q5', $R['quality'], 5);

// ===== G: break q9 -> q10 can cv 500; q10 het =====
tf_ins_owned($db, $TESTSID, 6, 9, 54, 0);
$R = \player\tianfu_break($TESTSID, $db, 6);
tassert('q9 can cv 500 -> false o cv 100', $R['ok'], false);
$db->prepare("UPDATE game1 SET ulv = 500, uyxb = 30000000, tf = 500 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_break($TESTSID, $db, 6);
tassert('q9 -> q10 ok o cv 500', $R['ok'], true);
$R = \player\tianfu_break($TESTSID, $db, 6);
tassert('q10 het pham de dot', $R['ok'], false);
// q10 co tran Lv = 60, id 6 dang lv 54 -> van con 6 cap de luyen
$R = \player\tianfu_lvup($TESTSID, $db, 6);
tassert('q10 lv 54 van luyen duoc', $R['ok'], true);
tassert('lv len 55', $R['lv'], 55);

// ===== H: thieu tien / thieu diem -> false va KHONG an tai san =====
tf_ins_owned($db, $TESTSID, 9, 1, 0, 0);
$db->prepare("UPDATE game1 SET tf = 0, uyxb = 50 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_lvup($TESTSID, $db, 9);
tassert('thieu diem tf -> false', $R['ok'], false);
$W = \player\tianfu_player_wallet($TESTSID, $db);
tassert('LT khong bi tr khi that bai', $W['uyxb'], 50);
tassert('lv khong tang khi that bai', \player\tianfu_owned($TESTSID, $db)[9]['lv'], 0);

// ===== I: rows cho UI =====
$db->prepare("UPDATE game1 SET tf = 500, uyxb = 5000000 WHERE sid = ?")->execute(array($TESTSID));
$LV = \player\tianfu_lvup_rows($TESTSID, $db, 1);
tassert('lvup rows = 3', count($LV), 3);
tassert('rows la mang {text,ok}', isset($LV[0]['text']) && isset($LV[0]['ok']), true);
$BR = \player\tianfu_break_rows($TESTSID, $db, 1);   // id 1: q2, lv 10 < tran 14
tassert('break rows = 4', count($BR), 4);
tassert('dong "cap tran" chua dat -> ok false', $BR[0]['ok'], false);
tassert('dong "diem tf" du -> ok true', $BR[1]['ok'], true);
$BRQ = \player\tianfu_break_rows($TESTSID, $db, 6);   // id 6 dang q10
tassert('q10 rows = 1 dong', count($BRQ), 1);
tassert('q10 rows ok false', $BRQ[0]['ok'], false);

// ===== J: log + mirror =====
$lc = $db->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind IN ('lvup','break')");
$lc->execute(array($TESTSID));
tassert('co log lvup/break', intval($lc->fetchColumn()) > 10, true);
$M = $db->prepare("SELECT tianfu_lv, tianfu_power FROM game1 WHERE sid = ?");
$M->execute(array($TESTSID));
$m = $M->fetch(PDO::FETCH_ASSOC);
tassert('mirror lv = lv chu phu', intval($m['tianfu_lv']), 10);
tassert('mirror power khong am', intval($m['tianfu_power']) > 0, true);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

- [ ] **Step 2: Chay, xac nhan FAIL `Call to undefined function player\tianfu_lvup()`**

- [ ] **Step 3: Append vao `class/function/tianfu.php`**

```php
// ======================================================================
// Task 5 — Luyen Lv va Dot Pham (ca hai 100% thanh cong, spec §5.2/§5.3)
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_player_wallet')) {
    /** Doc truc tiep game1: khong phu thuoc `$player` (CLI test + nut UI dung duoc). */
    function tianfu_player_wallet($sid, $dblj){
        $st = $dblj->prepare("SELECT tf, uyxb, ulv FROM game1 WHERE sid = ? LIMIT 1");
        $st->execute(array($sid));
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return array('tf' => 0, 'uyxb' => 0, 'ulv' => 0);
        return array('tf' => intval($r['tf']), 'uyxb' => intval($r['uyxb']),
                     'ulv' => intval($r['ulv']));
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_row_state')) {
    /** Dong UI: `ok` quyet nut co bam duoc khong; `text` co dau vi hien cho nguoi choi. */
    function tianfu_row_state($text, $ok){
        return array('text' => $text, 'ok' => (bool)$ok);
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lvup')) {
    function tianfu_lvup($sid, $dblj, $id){
        $empty = array('ok' => false, 'lv' => 0, 'cap' => 0, 'cost' => array(), 'msg' => '');
        $def = tianfu_def($id);
        if (!$def) { $empty['msg'] = 'Thiên phú không tồn tại.'; return $empty; }

        $O = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (intval($O[$id]['have']) !== 1) {
            $empty['msg'] = 'Chưa khai mở thiên phú này — cần Khai Phú Đan.';
            return $empty;
        }
        $lv  = intval($O[$id]['lv']);
        $cap = tianfu_lv_cap(intval($O[$id]['quality']));
        if ($lv >= $cap) {
            $empty['cap'] = $cap; $empty['lv'] = $lv;
            $empty['msg'] = $def['name'] . ' đã đạt trần ' . $cap . ' cấp — phải Dot Pham để luyện tiếp.';
            return $empty;
        }

        $cost = tianfu_lv_cost($lv);
        $W = tianfu_player_wallet($sid, $dblj);
        if ($W['tf'] < intval($cost['tf'])) {
            $empty['msg'] = 'Điểm Thiên Phú không đủ: cần ' . $cost['tf'] . ', có ' . $W['tf'] . '.';
            return $empty;
        }
        if ($W['uyxb'] < intval($cost['linh_thach'])) {
            $empty['msg'] = 'Linh Thạch không đủ: cần ' . number_format($cost['linh_thach'], 0, ',', '.')
                          . ', có ' . number_format($W['uyxb'], 0, ',', '.') . '.';
            return $empty;
        }

        $dblj->beginTransaction();
        try {
            // Mot lenh UPDATE dua tren chinh dieu kien du tr -> chong hai nut bam cung luc
            $st = $dblj->prepare("UPDATE game1 SET tf = tf - ?, uyxb = uyxb - ?
                                  WHERE sid = ? AND tf >= ? AND uyxb >= ?");
            $st->execute(array(intval($cost['tf']), intval($cost['linh_thach']), $sid,
                               intval($cost['tf']), intval($cost['linh_thach'])));
            if ($st->rowCount() !== 1) throw new Exception('thieu tf/linh thach');

            $dblj->prepare("UPDATE player_tianfu SET lv = lv + 1 WHERE sid = ? AND tianfu_id = ?")
                 ->execute(array($sid, $id));
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            $empty['msg'] = 'Luyện cấp thất bại, thử lại sau.';
            return $empty;
        }

        tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'lvup', $id, $lv, $lv + 1, 'cost tf=' . $cost['tf']
                  . ' lt=' . $cost['linh_thach'], $dblj);
        player_cache_clear($sid);   // ham cua class/player.php, cung namespace player

        return array('ok' => true, 'lv' => $lv + 1, 'cap' => $cap, 'cost' => $cost,
                     'msg' => 'Luyện ' . $def['name'] . ' thành công — cấp ' . ($lv + 1) . '.');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_break')) {
    function tianfu_break($sid, $dblj, $id){
        $empty = array('ok' => false, 'quality' => 0, 'cost' => array(), 'msg' => '');
        $def = tianfu_def($id);
        if (!$def) { $empty['msg'] = 'Thiên phú không tồn tại.'; return $empty; }

        $O  = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (intval($O[$id]['have']) !== 1) {
            $empty['msg'] = 'Chưa khai mở thiên phú này.';
            return $empty;
        }
        $q   = intval($O[$id]['quality']);
        $lv  = intval($O[$id]['lv']);
        $cap = tianfu_lv_cap($q);

        $cost = tianfu_break_cost($q);
        if ($cost === false) {
            $empty['quality'] = $q;
            $empty['msg'] = $def['name'] . ' đã ở phẩm chất cao nhất (Thần/Đế/… Phẩm 10).';
            return $empty;
        }
        if ($lv < $cap) {
            $empty['quality'] = $q;
            $empty['msg'] = 'Cần đạt trần cấp ' . $cap . ' trước khi Dot Pham — hiện ' . $lv . '.';
            return $empty;
        }

        $W = tianfu_player_wallet($sid, $dblj);
        if ($W['ulv'] < intval($cost['ulv_require'])) {
            $empty['quality'] = $q;
            $empty['msg'] = 'Cảnh giới chưa đủ: cần cấp ' . $cost['ulv_require'] . ', đang cấp ' . $W['ulv'] . '.';
            return $empty;
        }
        if ($W['tf'] < intval($cost['tf'])) {
            $empty['msg'] = 'Điểm Thiên Phú không đủ: cần ' . $cost['tf'] . ', có ' . $W['tf'] . '.';
            return $empty;
        }
        if ($W['uyxb'] < intval($cost['linh_thach'])) {
            $empty['msg'] = 'Linh Thạch không đủ: cần ' . number_format($cost['linh_thach'], 0, ',', '.')
                          . ', có ' . number_format($W['uyxb'], 0, ',', '.') . '.';
            return $empty;
        }

        $dblj->beginTransaction();
        try {
            $st = $dblj->prepare("UPDATE game1 SET tf = tf - ?, uyxb = uyxb - ?
                                  WHERE sid = ? AND tf >= ? AND uyxb >= ?");
            $st->execute(array(intval($cost['tf']), intval($cost['linh_thach']), $sid,
                               intval($cost['tf']), intval($cost['linh_thach'])));
            if ($st->rowCount() !== 1) throw new Exception('thieu tf/linh thach');

            $up = $dblj->prepare("UPDATE player_tianfu SET quality = quality + 1
                                  WHERE sid = ? AND tianfu_id = ? AND quality = ?");
            $up->execute(array($sid, $id, $q));
            if ($up->rowCount() !== 1) throw new Exception('quality bi doi');
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            $empty['msg'] = 'Đột phá phẩm chất thất bại, thử lại sau.';
            return $empty;
        }

        tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'break', $id, $q, $q + 1, 'cost tf=' . $cost['tf']
                   . ' lt=' . $cost['linh_thach'], $dblj);
        player_cache_clear($sid);   // ham cua class/player.php, cung namespace player

        return array('ok' => true, 'quality' => $q + 1, 'cost' => $cost,
                     'msg' => 'Đột phá ' . $def['name'] . ' lên '
                            . tianfu_quality_name($q + 1) . '.');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_lvup_rows')) {
    /** 3 dong cho panel Nang Cap (Task 8/9 dung de in va chan nut). */
    function tianfu_lvup_rows($sid, $dblj, $id){
        $O  = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (!isset($O[$id]) || intval($O[$id]['have']) !== 1) return array();
        $cost = tianfu_lv_cost(intval($O[$id]['lv']));
        $cap  = tianfu_lv_cap(intval($O[$id]['quality']));
        $W    = tianfu_player_wallet($sid, $dblj);
        return array(
            tianfu_row_state('Điểm Thiên Phú: ' . $W['tf'] . ' / ' . $cost['tf'],
                             $W['tf'] >= $cost['tf']),
            tianfu_row_state('Linh Thạch: ' . number_format($W['uyxb'], 0, ',', '.') . ' / '
                             . number_format($cost['linh_thach'], 0, ',', '.'),
                             $W['uyxb'] >= $cost['linh_thach']),
            tianfu_row_state('Cấp: ' . intval($O[$id]['lv']) . ' / ' . $cap . ' (trần '
                             . tianfu_quality_name(intval($O[$id]['quality'])) . ')',
                             intval($O[$id]['lv']) < $cap),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_break_rows')) {
    /** 4 dong cho panel Dot Pham. */
    function tianfu_break_rows($sid, $dblj, $id){
        $O  = tianfu_owned($sid, $dblj);
        $id = intval($id);
        if (!isset($O[$id]) || intval($O[$id]['have']) !== 1) return array();
        $q    = intval($O[$id]['quality']);
        $lv   = intval($O[$id]['lv']);
        $cap  = tianfu_lv_cap($q);
        $cost = tianfu_break_cost($q);
        $W    = tianfu_player_wallet($sid, $dblj);
        if ($cost === false) {
            return array(tianfu_row_state('Đã ở phẩm chất cao nhất', false));
        }
        return array(
            tianfu_row_state('Cấp trần: ' . $lv . ' / ' . $cap, $lv >= $cap),
            tianfu_row_state('Điểm Thiên Phú: ' . $W['tf'] . ' / ' . $cost['tf'],
                             $W['tf'] >= $cost['tf']),
            tianfu_row_state('Linh Thạch: ' . number_format($W['uyxb'], 0, ',', '.') . ' / '
                             . number_format($cost['linh_thach'], 0, ',', '.'),
                             $W['uyxb'] >= $cost['linh_thach']),
            tianfu_row_state('Cảnh giới: ' . $W['ulv'] . ' / ' . $cost['ulv_require'],
                             $W['ulv'] >= $cost['ulv_require']),
        );
    }
}
```

> **Vi sao `UPDATE ... WHERE quality = ?` + `rowCount() !== 1`:** day la optimistic lock — neu hai nut bam cung luc thi lenh thu hai thay `quality` da khop voi gia tri cu, `rowCount() = 0`, transaction rollback va tien duoc hoan. Dung `rowCount()` cua statement (khong dung `SELECT ROW_COUNT()`), dung nhu `class/function/huyetmach.php:700`.

- [ ] **Step 4: Lint + quet + test + commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]|[\x{4e00}-\x{9fff}]|array_key_last|SELECT ROW_COUNT' class/function/tianfu.php tests/test_tianfu_lvup.php
for f in class/function/tianfu.php tests/test_tianfu_lvup.php; do "$P" -l $f; done
"$P" tests/test_tianfu_lvup.php && "$P" tests/test_tianfu_open.php && "$P" tests/test_tianfu_bonuses.php
git add class/function/tianfu.php tests/test_tianfu_lvup.php
git commit -m "feat(tianfu): tianfu_lvup/break + rows UI (Task 5)"
```
Expected: `ALL PASS` ca 4 file test (them `test_tianfu_config.php` chay lai lan cuoi).

### Task 6: Phu Vi — `tianfu_slot_set()` + chan 2 o cung cong phia server

**Files:**
- Modify: `class/function/tianfu.php` (append)
- Create: `tests/test_tianfu_slot.php`

**Interfaces:**
- Consumes (Task 1/3): `tianfu_owned()`, `tianfu_activated()`, `tianfu_contributions($def)`, `tianfu_slot_open($ulv)`, `tianfu_slot_defs()`, `tianfu_recalc_mirror()`, `tianfu_log()`, `tianfu_player_wallet()` (Task 5), `tianfu_bonuses()`.
- Produces:
  - **`tianfu_slot_set($sid, $dblj, $id, $slot): array('ok','slot','moved','msg','mirror')`** — `id = 0` va `slot > 0`: xoa o dang dung trong `slot`; `id > 0` va `slot = 0`: thao thien phu do; `id > 0, slot 1..4`: bat vao Phu Vi `slot` (co hoan vi neu no dang nam o khac). `moved` = `array` cac `(id => slot)` bi doi choi, `mirror` = ket qua `tianfu_recalc_mirror()`.
  - `tianfu_slot_conflict($sid, $dblj, $id, $slot, array $act): string|false` — ten cong trung nhau hoac `false` (ham phu tro, test truc tiep duoc).

> **[CHOT] Quy tac chan trung cong (spec §5.4 "chan 2 cai cung 1 cong"):** hai thien phu **xung dot** neu tap `gate` cua chung **giao nhau**, ke ca cong PHU (`exp_monster` cua nhom Ngo Tinh). Kqua thuc te va doc duoc ngay cho nguoi choi: **4 Phu Vi phai den tu 4 nhom khac nhau**. Khong the bat 2 o cung nhom (vi du Ngộ Tính + Luyện Khí) vi ca hai cung dem % len `exp_cultivate` — cong do `tianfu_bonuses()` cong gop va clamp 30%, nen cho 2 o vao cung mot cong chi la lan tran tran, khong phai manh them.

- [ ] **Step 1: Viet test FAIL truoc** — `tests/test_tianfu_slot.php`:

```php
<?php
/**
 * test_tianfu_slot.php — mo Phu Vi theo canh gioi, chan 2 o cung cong, hoan vi, thao do.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_slot.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/tests/tianfu_test_lib.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

$db = tf_test_db();
$TESTSID = str_repeat('f', 32);
$TESTUID = 990112;
tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 100, 0);   // cv 100: moi slot 1 va 2

foreach (array(1, 2, 4, 7) as $i => $id) {                 // 4 o 4 nhom khac nhau
    tf_ins_owned($db, $TESTSID, $id, 3 + $i, 10 + $i, 0);
}

// ===== A: o chua so huu =====
$R = \player\tianfu_slot_set($TESTSID, $db, 18, 1);
tassert('chua so huu -> false', $R['ok'], false);

// ===== B: Phu Vi 3 can cv 200 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 3);
tassert('slot 3 chua mo o cv 100', $R['ok'], false);
tassert('msg bao canh gioi', strpos($R['msg'], 'cấp 200') !== false, true);

// ===== C: bat id 1 vao slot 1 -> duoc, mirror theo sau =====
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 1);
tassert('slot 1 ok', $R['ok'], true);
tassert('mirror id = 1', $R['mirror']['tianfu_id'], 1);
tassert('mirror quality', $R['mirror']['tianfu_quality'], 3);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('player_tianfu slot = 1', $O[1]['slot'], 1);
tassert('bonus exp_cultivate > 0', \player\tianfu_bonuses($TESTSID, $db)['exp_cultivate'] > 0, true);

// ===== D: 2 o cung nhom Ngo Tinh -> chan =====
$R = \player\tianfu_slot_set($TESTSID, $db, 2, 2);
tassert('id 2 cung cong voi id 1 -> false', $R['ok'], false);
tassert('msg neu ten cong', strpos($R['msg'], 'Tốc độ tu luyện') !== false, true);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('id 2 van o slot 0', $O[2]['slot'], 0);

// ===== E: khac nhom -> duoc =====
$R = \player\tianfu_slot_set($TESTSID, $db, 4, 2);
tassert('id 4 (khac nhom) vao slot 2 ok', $R['ok'], true);

// ===== F: hoan vi — id 4 dang slot 2, keo no len slot 1 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 4, 1);
tassert('hoan vi ok', $R['ok'], true);
tassert('id 4 -> slot 1', \player\tianfu_owned($TESTSID, $db)[4]['slot'], 1);
tassert('id 1 bi day xuong slot 2', \player\tianfu_owned($TESTSID, $db)[1]['slot'], 2);
tassert('slot 1 = id 4 sau hoan vi', \player\tianfu_activated($TESTSID, $db)[1]['id'], 4);

// ===== G: hoan vi tao xung dot thi PHAI bi chan =====
// Tra ve: slot1 = id 4 (Tài Vận), slot2 = id 1 (Ngộ Tinh). Bat id 2 (Ngộ Tinh) vao slot 3?
// (cv 100 khong mo slot 3) -> nen dung slot 4 cung bi chan; kiem tra bang cache:
$db->prepare("UPDATE game1 SET ulv = 280 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_slot_set($TESTSID, $db, 2, 3);
tassert('cv 280 mo slot 3 nhung id 2 xung dot id 1 -> false', $R['ok'], false);
$R = \player\tianfu_slot_set($TESTSID, $db, 7, 3);   // Dan Dao: khac cong
tassert('id 7 vao slot 3 ok', $R['ok'], true);
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 4);   // Ngộ Tinh khong the vao slot 4 vi id 1 dang slot 2? 
tassert('id 1 di chuyen tu slot 2 -> 4, slot 2 trong', $R['ok'], true);
$A = \player\tianfu_activated($TESTSID, $db);
tassert('slot 2 sau khi di chuyen: khong con id 1', isset($A[2]) ? $A[2]['id'] : 0, 0);
tassert('slot 4 = id 1', $A[4]['id'], 1);

// ===== H: thao do bang slot = 0 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 0);
tassert('thao ok', $R['ok'], true);
tassert('id 1 slot 0', \player\tianfu_owned($TESTSID, $db)[1]['slot'], 0);
tassert('khong con bonus cua id 1', \player\tianfu_bonuses($TESTSID, $db)['exp_cultivate'], 0.0);

// ===== I: xoa o dang dung bang id = 0 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 0, 3);
tassert('id 0 -> xoa slot 3 ok', $R['ok'], true);
$A = \player\tianfu_activated($TESTSID, $db);
tassert('slot 3 trong', isset($A[3]), false);

// ===== J: log + mirror cuoi cung =====
$lc = $db->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind = 'slot'");
$lc->execute(array($TESTSID));
tassert('co log slot', intval($lc->fetchColumn()) >= 5, true);
$M = $db->prepare("SELECT tianfu_id, tianfu_power FROM game1 WHERE sid = ?");
$M->execute(array($TESTSID));
$m = $M->fetch(PDO::FETCH_ASSOC);
tassert('chu phu sau khi thao het = id 4', intval($m['tianfu_id']), 4);
// Seed `foreach (array(1,2,4,7) as $i => $id)`: $i la KHOA mang 0..3
//   id 1 -> q3 lv10 | id 2 -> q4 lv11 | id 4 -> q5 lv12 | id 7 -> q6 lv13
// Den buoc J: slot 1 = id 4, slot 3 da bi xoa o buoc I -> chi con mot o dang bat.
tassert('power = id 4 (slot 3 da xoa)', intval($m['tianfu_power']), 5 * 150 + 12 * 8);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
```

> **So trong test J da tinh theo dung thu tu seed** (`$i` la KHOA mang, khong phai index 1..4): id 1 = q3/lv10, id 2 = q4/lv11, id 4 = q5/lv12, id 7 = q6/lv13. Ai doi `$i` sang `1..4` (dung `array(1,2,4,7)` theo kieu danh sach) thi phai doi luon so ky vong — **giu nguyen nhu tren, dung `foreach (array(1,2,4,7) as $i => $id)` la dung.**

- [ ] **Step 2: Chay, xac nhan FAIL `Call to undefined function player\tianfu_slot_set()`**

- [ ] **Step 3: Append vao `class/function/tianfu.php`**

```php
// ======================================================================
// Task 6 — Phu Vi (slot)
// ======================================================================

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_conflict')) {
    /**
     | Hai thien phu xung dot neu tap cong (gate chinh + gate phu) giao nhau.
     | $act: mang slot => row (nhu tianfu_activated()), LA BUOI DA QUYET
     | (goi truoc khi ghi). false = khong xung dot.
     */
    function tianfu_slot_conflict($sid, $dblj, $id, $slot, $act){
        $def = tianfu_def($id);
        if (!$def) return 'Thiên phú không tồn tại.';
        $gates = array_keys(tianfu_contributions($def));
        foreach ($act as $s => $row) {
            if (intval($s) === intval($slot)) continue;      // chinh cho do se bi ghi de
            if (intval($row['id']) === intval($id)) continue; // dung mot o nay
            $d2 = tianfu_def($row['id']);
            if (!$d2) continue;
            $share = array_intersect($gates, array_keys(tianfu_contributions($d2)));
            if (count($share) > 0) {
                return tianfu_gate_name(reset($share));
            }
        }
        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tianfu_slot_set')) {
    /**
     | $id = 0, $slot > 0 : xoa o dang dung trong $slot.
     | $id > 0,  $slot = 0: thao thien phu $id.
     | $id > 0,  $slot 1..4: bat $id vao $slot; neu $id dang o cho khac thi
     | hoan vi voi o dang nam trong $slot (neu hoan vi van khong xung dot).
     */
    function tianfu_slot_set($sid, $dblj, $id, $slot){
        $id   = intval($id);
        $slot = intval($slot);
        $bad  = array('ok' => false, 'slot' => $slot, 'moved' => array(), 'msg' => '',
                      'mirror' => array());

        if ($slot < 0 || $slot > 4) { $bad['msg'] = 'Phu Vi không hợp lệ.'; return $bad; }
        if ($id === 0 && $slot === 0) { $bad['msg'] = 'Không có thay đổi.'; return $bad; }

        $O   = tianfu_owned($sid, $dblj);
        $W   = tianfu_player_wallet($sid, $dblj);
        $act = tianfu_activated($sid, $dblj);

        if ($id > 0) {
            if (intval($O[$id]['have']) !== 1) {
                $bad['msg'] = 'Chưa khai mở thiên phú này — cần Khai Phú Đan.';
                return $bad;
            }
            if ($slot > 0) {
                if (!in_array($slot, tianfu_slot_open($W['ulv']), true)) {
                    $sd = array();
                    foreach (tianfu_slot_defs() as $s) $sd[intval($s['i'])] = intval($s['ulv']);
                    $bad['msg'] = 'Phu Vi ' . $slot . ' chưa mở — cần cấp ' . $sd[$slot]
                                . ', đang cấp ' . $W['ulv'] . '.';
                    return $bad;
                }
                $clash = tianfu_slot_conflict($sid, $dblj, $id, $slot, $act);
                if ($clash !== false) {
                    $bad['msg'] = 'Không thể kích hoạt hai thiên phú cùng cổng "'
                                . $clash . '" — chọn Phu Vi thuộc nhóm khác.';
                    return $bad;
                }
            }
        }

        // Tinh toan bo mapping slot => id moi (van trong bo nho, chua ghi)
        $target = intval($O[$id]['slot']);          // $id = 0 -> 0
        $plan   = array();                          // slot => id|0
        foreach ($act as $s => $row) $plan[$s] = intval($row['id']);

        if ($slot === 0) {                          // thao do
            if ($target === 0) { $bad['msg'] = 'Thiên phú này chưa được kích hoạt.'; return $bad; }
            $plan[$target] = 0;
        } elseif ($id === 0) {                      // xoa o
            if (!isset($plan[$slot])) { $bad['msg'] = 'Phu Vi này đang trống.'; return $bad; }
            $plan[$slot] = 0;
        } else {
            $prev = isset($plan[$slot]) ? intval($plan[$slot]) : 0;
            $plan[$slot] = $id;
            if ($target > 0) {
                $plan[$target] = $prev;             // hoan vi
                if ($prev === 0) unset($plan[$target]);
            }
        }
        // Don cac o = 0
        $final = array();
        foreach ($plan as $s => $v) { if ($v > 0) $final[$s] = $v; }

        $dblj->beginTransaction();
        try {
            // Xoa toan bo cac o cua nhan vat roi ghi lai theo $final: don gian,
            // khong lo thu tu UPDATE trung gate (MySQL 5.5 khong co "UPDATE ... SET slot
            // = CASE WHEN" de doc). Toi da 4 dong -> chi 2 cau lenh.
            $dblj->prepare("UPDATE player_tianfu SET slot = 0 WHERE sid = ?")
                 ->execute(array($sid));
            $ins = $dblj->prepare("UPDATE player_tianfu SET slot = ? WHERE sid = ? AND tianfu_id = ?");
            foreach ($final as $s => $v) $ins->execute(array($s, $sid, $v));
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            $bad['msg'] = 'Thay đổi Phu Vi thất bại, thử lại sau.';
            return $bad;
        }

        $moved = array();
        foreach ($act as $s => $row) {
            $nowIn = 0;
            foreach ($final as $fs => $fv) { if ($fv === intval($row['id'])) $nowIn = $fs; }
            if ($nowIn !== intval($s)) $moved[intval($row['id'])] = $nowIn;
        }
        if ($id > 0 && !isset($moved[$id])) {
            $nowIn = 0;
            foreach ($final as $fs => $fv) { if ($fv === $id) $nowIn = $fs; }
            if ($nowIn !== $target) $moved[$id] = $nowIn;
        }

        $mirror = tianfu_recalc_mirror($sid, $dblj);
        tianfu_log($sid, 'slot', $id, $target, $slot,
                   'activated=' . implode(',', $final), $dblj);
        player_cache_clear($sid);

        return array('ok' => true, 'slot' => $slot, 'moved' => $moved,
                     'msg' => $slot === 0 ? 'Đã thu hồi thiên phú.' : 'Đã kích hoạt thiên phú.',
                     'mirror' => $mirror);
    }
}
```

> **Vi sao "xoa het roi ghi lai":** `player_tianfu.slot` la `TINYINT` **khong co unique index** (Task 2), nen muon hoan vi 2 o thi phai co tam thoi 2 o trung slot hoac chay 3 lenh UPDATE theo thu tu tinh toc. Cach wipe-and-rewrite chi 2 cau lenh cho toi da 4 o, **va** khong the de lai trang thai trung slot neu transaction thanh cong. `slot = 0` nghia la "nam trong Phu Cac, chua bat" — dung voi `tianfu_activated()` (loc `slot > 0`).

- [ ] **Step 4: Lint + quet + test + commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]|[\x{4e00}-\x{9fff}]|array_key_last' class/function/tianfu.php tests/test_tianfu_slot.php
for f in class/function/tianfu.php tests/test_tianfu_slot.php; do "$P" -l $f; done
"$P" tests/test_tianfu_slot.php && "$P" tests/test_tianfu_lvup.php && "$P" tests/test_tianfu_open.php && "$P" tests/test_tianfu_bonuses.php
git add class/function/tianfu.php tests/test_tianfu_slot.php
git commit -m "feat(tianfu): tianfu_slot_set + chan 2 o cung cong (Task 6)"
```
Expected: `ALL PASS` ca 5 file test (them `test_tianfu_config.php`). Neu `FAIL msg bao canh gioi` → `tianfu_slot_defs()` khong tra `ulv` theo dung key (Task 1 Step 6).

### Task 7: CSS `.tp-*` / `.tpc-*` / `.tpo-*` + 6 bac hieu ung + anh

**Files:**
- Modify: `css/mystyle.css` (append **xuong duoi cung**, sau dong `7846` — block `.lcw-*` cua Linh Can)
- Create: `images/tianfu/phu-cac.jpg` + 6 anh nhom `images/tianfu/nhom-<key>.jpg` (`ngo-tinh`, `co-duyen`, `dan-dao`, `tai-van`, `canh-gioi`, `than-suat` — dung `groups[*].img` trong config Task 1)

**Interfaces:**
- Consumes: ten lop trong spec §4.1–§4.3 (Task 8/9/10 in ra dung cac lop nay).
- Produces: `--tp-tier --tp-glow --tp-dur --tp-aura --tp-color` do PHP in ra; 6 bac `tp-fx0..tp-fx5`; bien cache-bust anh la `$tpImgVer` khai bao dau `game/tianfu.php` (Task 8) — **khong dung `const Z_TP_IMG_VER`**, vi ca ba he anh em deu dung bien cuc bo (`game/huyetmach.php:11 $hmImgVer`, `game/thechat.php:9 $nbImgVer`, `game/linhcan.php:10 $lcImgVer`); `const ZB_IMG_VER` chi ton tai trong `class/function/zhuangbei.php:8` cho anh trang bi.

- [ ] **Step 1: Lay anh** — thu crop tu asset goc truoc (memory `reference-source-game-assets`: MuH5 / TuTienPhucMa / NaTra tren `F:` va `E:`), chi dung ImageGen khi khong tim thay. **Prompt ImageGen PHAI lay mau nen cua trang** (`#0b1024`→`#111a35`, memory `feedback-image-color-match-page`), kich thuoc 1024x1280 cho `phu-cac.jpg` (nha 3x4, khung `.tp-stage` la 4/5) va 768x1024 cho 6 anh nhom. Kiem tra:

```bash
cd /d/XXJserver/phpstudy_pro/WWW && ls -la images/tianfu/
# Expected: 7 file, moi file > 40 KB (anh < 20 KB la anh loi/transparent)
```

- [ ] **Step 2: Append block CSS** (nguyen chan — day la deliverable, KHONG rut gon):

```css
/* ==================== THIEN PHU — PHU CAC SUU TAM (.tp-* / .tpc-* / .tpo-*) ====================
   Khuon: .hm-* (7501+), .lc-* (7750+), .lcq-/.lcb-/.lcw- (7790+). Ba le phai gi:
   1) `a:link` trong css/gamecss.css = dac tinh (0,1,1) -> moi dieu khien dang <a>
      PHAI co them nhom `.main a.tp-*` (0,2,1), neu khong bi do mau + margin he thong.
   2) `.main button` = (0,1,1) -> moi nut submit PHAI dat `.main .tp-act*` (0,2,0).
   3) mobile 660px, desktop `@media(min-width:601px)` -> 840px; KHONG dat max-width
      len .tp-stage / .tp-grid (pham vi sua dung theo le `feedback-khong-mo-rong-pham-vi-sua`).

   Hieu ung phan tang theo 6 bac (le chu du an: bac THAP NHAT van phai thay quang mang tinh):
     --tp-tier = mau cua PHAM CHAT chu phu (tianfu_quality_color)
     --tp-glow = do to vong        (4 / 7 / 11 / 15 / 20 / 26 px)
     --tp-dur  = toc do dong       (0s / 11s / 9s / 6.5s / 4.4s / 3s)
     --tp-aura = do sang lop phu   (.05 / .07 / .10 / .14 / .18 / .23)
     --tp-color= mau cua NHOM dang xem (groups[*].color) — do mau rail + stage chip
   tp-fx0 that dong bang `animation:none`, KHONG dung duration:0s (Chrome giu keyframe 0%). */
.tp-fx0{--tp-dur:0s;--tp-aura:.05;--tp-glow:4px}
.tp-fx1{--tp-dur:11s;--tp-aura:.07;--tp-glow:7px}
.tp-fx2{--tp-dur:9s;--tp-aura:.10;--tp-glow:11px}
.tp-fx3{--tp-dur:6.5s;--tp-aura:.14;--tp-glow:15px}
.tp-fx4{--tp-dur:4.4s;--tp-aura:.18;--tp-glow:20px}
.tp-fx5{--tp-dur:3s;--tp-aura:.23;--tp-glow:26px}

/* --- khung --- */
.tp-wrap{max-width:660px;margin:0 auto;padding:10px;position:relative;--tp-tier:#9e9e9e;--tp-glow:4px;--tp-dur:0s;--tp-aura:.05;--tp-color:#7dd3fc}
.tp-card{background:linear-gradient(160deg,#111a35,#0b1024);border:1px solid rgba(148,163,184,.16);border-radius:14px;padding:14px 16px;margin:0 0 10px}
.tp-head{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px}
.tp-hrow{display:flex;align-items:baseline;gap:8px;min-width:0}
.tp-title{font-size:18px;font-weight:1000;color:var(--tp-tier,#9e9e9e);text-shadow:0 0 calc(var(--tp-glow,4px) * .7) rgba(0,0,0,.6);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tp-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:1000;color:#fff;background:rgba(255,255,255,.08);border:1px solid var(--tp-tier,#9e9e9e);box-shadow:0 0 var(--tp-glow,4px) var(--tp-tier,#9e9e9e);white-space:nowrap}
.tp-badge-ico{font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif}
.tp-msg{padding:10px 12px;border-radius:10px;margin:0 0 10px;font-size:13px;text-align:center}
.tp-msg.ok{background:rgba(76,175,80,.14);border:1px solid rgba(76,175,80,.5);color:#8bc34a}
.tp-msg.bad{background:rgba(244,67,54,.12);border:1px solid rgba(244,67,54,.45);color:#ef9a9a}

/* --- rail 6 nhom (khuon .hm-rail/.hm-tier/.hm-link, mystyle.css:7284-7304) ---
   .tp-tier  = <a> chip nhom (bam de xem nhom), .tp-link = <span> noi mong.
   .tp-link KHONG phai <a> nen vo nhiu le a:link (0,1,1). -->
.tp-rail{display:flex;gap:0;align-items:stretch;overflow-x:auto;padding-bottom:4px;margin:0 0 8px;-webkit-overflow-scrolling:touch}
.tp-tier{flex:1 1 0;min-width:0;box-sizing:border-box;display:flex;flex-direction:column;align-items:center;gap:2px;padding:8px 4px;margin:0;border-radius:12px;border:1px solid rgba(148,163,184,.16);background:rgba(0,0,0,.22);text-decoration:none;text-align:center;line-height:1.3}
.tp-tier b{font-size:11px;font-weight:1000;color:#cbd5e1}
.tp-tier span{font-size:16px;line-height:1.2;font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif;filter:grayscale(.55)}
.tp-tier em{display:block;font-style:normal;font-size:10px;color:#64748b}
.tp-tier.is-done{border-color:rgba(148,163,184,.4);background:rgba(148,163,184,.10)}
.tp-tier.is-done span{filter:none}
.tp-tier.is-view{outline:2px solid var(--tp-color,#7dd3fc);outline-offset:1px}
.tp-tier.is-view b{color:#fff}
.tp-link{flex:0 0 12px;align-self:center;height:3px;margin:0;border-radius:2px;background:rgba(148,163,184,.2);position:relative}
.tp-link:after{content:"";position:absolute;right:-1px;top:50%;transform:translateY(-50%);width:0;height:0;border-left:5px solid rgba(148,163,184,.34);border-top:4px solid transparent;border-bottom:4px solid transparent}
.tp-link.is-lit{background:linear-gradient(90deg,var(--tp-color,#7dd3fc),var(--tp-tier,#9e9e9e))}
.tp-link.is-lit:after{border-left-color:var(--tp-tier,#9e9e9e)}
.tp-viewline{font-size:12px;color:#9fb3c8;text-align:center;margin:6px 0 2px}
.tp-viewline b{font-weight:1000}
.tp-note{font-size:11.5px;color:#f87171;text-align:center;line-height:1.45;margin-top:4px}
.tp-note.dim{color:#9fb3c8}

/* --- san + aura + anh --- */
.tp-forge-wrap{position:relative;isolation:isolate;margin-top:10px}
.tp-stage{position:relative;border-radius:16px;overflow:hidden;border:1px solid var(--tp-tier,#9e9e9e);box-shadow:0 0 calc(var(--tp-glow,4px) * 1.6) rgba(0,0,0,.55);aspect-ratio:4/5;max-width:420px;margin:0 auto;background:#0b1024}
.tp-stage-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.92}
.tp-stage-chip{position:absolute;left:8px;top:8px;z-index:3;display:flex;flex-direction:column;align-items:center;gap:2px;padding:5px 8px;border-radius:10px;font-size:11px;color:#e2e8f0;background:rgba(4,11,30,.72);border:1px solid var(--tp-color,#7dd3fc);text-decoration:none}
.tp-aura{position:absolute;inset:0;z-index:2;pointer-events:none;background:radial-gradient(ellipse 58% 48% at 50% 46%,var(--tp-tier,#9e9e9e) 0%,rgba(0,0,0,0) 64%);box-shadow:inset 0 0 46px 4px var(--tp-tier,#9e9e9e);mix-blend-mode:screen;opacity:var(--tp-aura,.05);animation:tpAuraPulse var(--tp-dur,0s) ease-in-out infinite}
.tp-sweep{position:absolute;left:0;top:0;width:100%;height:100%;z-index:2;pointer-events:none;opacity:0;background:linear-gradient(115deg,rgba(0,0,0,0) 34%,var(--tp-tier,#9e9e9e) 50%,rgba(0,0,0,0) 66%);mix-blend-mode:screen;animation:tpSweepRun var(--tp-dur,0s) ease-in-out infinite}
.tp-forge{padding:12px 12px 14px;border-radius:0 0 16px 16px;background:linear-gradient(180deg,rgba(4,11,30,.2),rgba(4,11,30,.92));text-align:center}
.tp-forge-name{font-size:17px;font-weight:1000;color:#fff;text-shadow:0 0 calc(var(--tp-glow,4px) * .8) var(--tp-tier,#9e9e9e)}
.tp-forge-lv{font-size:13px;color:#ffd77a;font-weight:1000;margin-top:2px}
.tp-forge-pct{font-size:12.5px;color:#cbd5e1;margin-top:4px;line-height:1.5}
.tp-forge-cost{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin:8px 0 10px;font-size:12px;color:#9fb3c8}
.tp-forge-cost b{color:#e2e8f0}
.tp-forge-cost .ok{color:#6ee7b7}
.tp-forge-cost .no{color:#f87171}

/* --- 2 cot: ky nang + Phu Vi --- */
.tp-cols{display:block}
.tp-h3{font-size:14px;font-weight:1000;color:#ffd77a;letter-spacing:.4px;margin:2px 0 8px}
.tp-attr{display:flex;justify-content:space-between;align-items:baseline;gap:8px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.07);font-size:13px}
.tp-attr:last-child{border-bottom:0}
.tp-attr-name{color:#cbd5e1;min-width:0}
.tp-attr-val{font-weight:1000;color:#6ee7b7;white-space:nowrap}
.tp-attr-val.is-zero{color:#64748b}
.tp-attr-val.is-max{color:#fbbf24}
.tp-attr-cap{font-size:10.5px;color:#64748b;white-space:nowrap}
.tp-ms{margin-top:10px}
.tp-ms-label{display:flex;justify-content:space-between;font-size:11.5px;color:#9fb3c8;margin-bottom:4px}
.tp-ms-bar{position:relative;height:8px;border-radius:4px;background:rgba(255,255,255,.08);overflow:hidden}
.tp-ms-fill{position:absolute;left:0;top:0;height:100%;border-radius:4px;background:linear-gradient(90deg,var(--tp-tier,#9e9e9e),#ffd77a);transition:width .4s}
.tp-ms-fill:after{content:"";position:absolute;top:0;left:-40%;width:38%;height:100%;background:linear-gradient(90deg,rgba(255,255,255,0),rgba(255,255,255,.38),rgba(255,255,255,0));animation:tpMsFlow var(--tp-dur,0s) ease-in-out infinite}
.tp-ms-done{font-size:11.5px;color:#6ee7b7;text-align:center;margin-top:4px}
.tp-slot{padding:10px;border-radius:12px;border:1px dashed rgba(148,163,184,.28);background:rgba(0,0,0,.2);margin-bottom:8px}
.tp-slot.is-on{border-style:solid;border-color:var(--tp-color,#7dd3fc);box-shadow:0 0 12px -2px var(--tp-color,#7dd3fc)}
.tp-slot.is-locked{opacity:.6}
.tp-slot-head{display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#9fb3c8}
.tp-slot-head b{color:#e2e8f0;font-size:12.5px}
.tp-slot-name{font-size:14px;font-weight:1000;color:#fff;margin-top:4px}
.tp-slot-name.empty{color:#64748b;font-weight:700}
.tp-gate{display:inline-block;margin-top:4px;padding:3px 8px;border-radius:8px;font-size:11px;color:#0b1024;background:var(--tp-color,#7dd3fc);font-weight:1000}
.tp-reqs{margin-top:6px;font-size:11.5px;line-height:1.6;color:#9fb3c8}
.tp-reqs .ok{color:#6ee7b7}
.tp-reqs .no{color:#f87171}

/* --- tabbar duoi (4 nut): copy khuon .lc-tabbar vi .main a:link de (0,1,1) --- */
.tp-tabbar{display:flex;justify-content:center;gap:6px;margin:10px 0 2px;padding:7px 6px;border-radius:18px;border:1px solid rgba(255,215,122,.2);background:linear-gradient(180deg,rgba(20,14,4,.88),rgba(8,6,2,.94))}
.tp-tabbar a,.tp-tabbar a:link,.tp-tabbar a:visited,.tp-tabbar a:hover,.tp-tabbar a:active,.tp-tabbar a:focus{box-sizing:border-box;flex:1 1 auto;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:3px;min-height:56px;padding:7px 3px 6px;margin:0;border-radius:12px;border:1px solid transparent;background:none;color:#c8b28a;text-decoration:none;font-weight:1000}
.tp-tabbar a i{display:block;width:100%;height:22px;line-height:22px;overflow:hidden;font-style:normal;font-size:18px;text-align:center;font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif;filter:grayscale(.4)}
.tp-tabbar a b{display:block;width:100%;text-align:center;font-size:11px;letter-spacing:.2px}
.tp-tabbar a.on,.tp-tabbar a.on:link,.tp-tabbar a.on:visited{color:#ffd77a;border-color:rgba(255,205,120,.42);background:radial-gradient(circle at 50% 26%,rgba(120,74,14,.55),rgba(28,16,2,.35));box-shadow:0 0 18px rgba(255,178,66,.18)}
.tp-tabbar a.on i{filter:none}
.tp-tabbar a:hover,.tp-tabbar a:active,.tp-tabbar a:focus{background:rgba(255,255,255,.12);color:#e2e8f0}
@media(max-width:760px){.tp-tabbar a{min-height:52px;padding:6px 2px 5px}.tp-tabbar a b{font-size:10px}}

/* --- nut: .main button (0,1,1) danh thua -> dung nhom 2 lop --- */
.main .tp-act{padding:11px 18px;border:0;border-radius:12px;font-size:15px;font-weight:1000;color:#fff;box-sizing:border-box}
.main .tp-act-up{background:linear-gradient(135deg,#0ea5e9,#22d3ee)}
.main .tp-act-break{background:linear-gradient(135deg,#7c3aed,#db2777)}
.main .tp-act-open{background:linear-gradient(135deg,#f59e0b,#ef4444)}
.main .tp-act-slot{background:linear-gradient(135deg,#16a34a,#4ade80);padding:9px 14px;font-size:13px;width:100%;margin-top:6px}
.main .tp-act[disabled],.main .tp-act.is-off{opacity:.45;box-shadow:none}
.main .tp-act-ghost,.main a.tp-act-ghost{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:#cbd5e1}
.main a.tp-act-ghost,.main a.tp-act-ghost:link,.main a.tp-act-ghost:visited,.main a.tp-act-ghost:hover{background:rgba(255,255,255,.08);color:#cbd5e1;text-decoration:none}
/* reset <a> he thong: a:link = inline-block + margin 4/3/2/4 (gamecss.css:70) */
.main a.tp-tier,.main a.tp-act,.main a.tp-stage-chip,.main a.tp-act-ghost,.main a.tpc-cell,.main a.tpc-act,.main a.tpo-act{box-sizing:border-box;margin:0}
.main a.tp-tier{display:flex}
/* .tp-slot la DIV (co form ben trong), KHONG phai <a> -> khong can reset a:link */
.tp-slot form{margin:0}
/* emoji: moi icon do ra trong HTML deu bọc <i class="tp-emoji"> (le cua docs/UI_XIULIAN.md §9.1:
   ky tu BMP nhu ⚗ U+2697, ⛰ U+26F0 de Chrome desktop render thanh glyph trai lech o flex) */
.tp-emoji{font-style:normal;font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif}

@keyframes tpAuraPulse{0%,100%{opacity:var(--tp-aura,.05)}50%{opacity:calc(var(--tp-aura,.05) + .07)}}
@keyframes tpSweepRun{0%,100%{opacity:0;transform:translateX(-24%)}50%{opacity:calc(var(--tp-aura,.05) + .05);transform:translateX(24%)}}
@keyframes tpMsFlow{0%{left:-40%;opacity:0}12%{opacity:.9}46%{left:104%;opacity:0}100%{left:104%;opacity:0}}
@keyframes tpCellGlow{0%,100%{box-shadow:0 0 6px var(--tp-cell,#9e9e9e)}50%{box-shadow:0 0 calc(6px + var(--tp-glow,4px)) var(--tp-cell,#9e9e9e)}}
/* tp-fx0: CHI co quang mang tinh, tuyet doi khong dong nao */
.tp-fx0 .tp-aura,.tp-fx0 .tp-ms-fill:after{animation:none}
.tp-fx0 .tp-sweep{display:none}
@media(prefers-reduced-motion:reduce){
.tp-aura,.tp-ms-fill:after{animation:none}
.tp-sweep{display:none}
.tp-fx5 .tp-badge,.tp-cell.is-active{animation:none}
}
@media (min-width:601px){.tp-wrap{max-width:840px}.tp-cols{display:grid;grid-template-columns:1fr 1fr;gap:12px}.tp-stage{max-width:520px}}

/* --- PHU CAC (.tpc-*) --- */
.tpc-wrap{max-width:660px;margin:0 auto;padding:10px}
.tpc-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:15px;font-weight:1000;color:#ffd77a}
.tpc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.tpc-cell{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;min-height:96px;padding:9px 6px;border-radius:13px;border:1px solid rgba(148,163,184,.2);background:rgba(0,0,0,.24);text-decoration:none;color:#cbd5e1;cursor:pointer;--tp-cell:#9e9e9e}
.tpc-cell i{font-style:normal;font-size:24px;font-family:"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif}
.tpc-cell b{font-size:11px;font-weight:1000;text-align:center;line-height:1.25}
.tpc-cell span{font-size:10.5px;color:#94b8dd}
.tpc-cell.is-have{border-color:var(--tp-cell,#9e9e9e);box-shadow:0 0 10px -3px var(--tp-cell,#9e9e9e)}
.tpc-cell.is-active{border-color:var(--tp-cell,#9e9e9e);animation:tpCellGlow var(--tp-dur,11s) ease-in-out infinite}
.tpc-cell.is-lock{opacity:.5;border-style:dashed}
.tpc-cell.is-lock i{filter:grayscale(1)}
.tpc-cell.is-sel,.tpc-cell:hover{background:rgba(255,255,255,.1)}
.tpc-slot-tag{position:absolute;right:6px;top:6px;font-size:10px;font-weight:1000;color:#0b1024;background:#ffd77a;border-radius:6px;padding:1px 5px}
.tpc-detail{margin-top:10px;padding:12px;border-radius:13px;border:1px solid var(--tp-cell,#9e9e9e);background:linear-gradient(160deg,#141d3a,#0b1024)}
.tpc-detail-name{font-size:16px;font-weight:1000;color:#fff}
.tpc-detail-q{font-size:12px;color:var(--tp-cell,#9e9e9e);font-weight:1000;margin-left:6px}
.tpc-detail-desc{font-size:12.5px;color:#9fb3c8;line-height:1.55;margin:6px 0}
.tpc-detail-rows{margin:6px 0;font-size:12.5px;line-height:1.7;color:#cbd5e1}
.tpc-detail-rows .ok{color:#6ee7b7}
.tpc-detail-rows .no{color:#f87171}
.tpc-acts{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
.tpc-acts form{flex:1 1 120px;margin:0}
.main .tpc-act{width:100%;padding:10px 12px;font-size:13px}
@media (min-width:601px){.tpc-wrap{max-width:840px}.tpc-grid{grid-template-columns:repeat(6,1fr)}}

/* --- KHAI PHU (.tpo-*) --- */
.tpo-wrap{max-width:660px;margin:0 auto;padding:10px}
.tpo-card{background:linear-gradient(160deg,#171029,#0b1024);border:1px solid rgba(245,158,11,.3);border-radius:14px;padding:16px;text-align:center}
.tpo-title{font-size:18px;font-weight:1000;color:#ffd77a;letter-spacing:1px}
.tpo-pill{font-size:13px;color:#cbd5e1;margin:8px 0}
.tpo-pill b{color:#ffd77a}
.tpo-truth{margin:10px 0;padding:10px;border-radius:10px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.35);font-size:13px;color:#fde68a;line-height:1.5}
.tpo-result{margin-top:12px;padding:14px;border-radius:13px;border:1px solid var(--tp-tier,#9e9e9e);background:rgba(0,0,0,.3)}
.tpo-result-name{font-size:17px;font-weight:1000;color:var(--tp-tier,#9e9e9e);text-shadow:0 0 var(--tp-glow,4px) var(--tp-tier,#9e9e9e)}
.tpo-result-q{font-size:13px;color:#e2e8f0;margin-top:4px}
.tpo-result-new{font-size:12px;color:#6ee7b7;margin-top:4px}
.tpo-table{margin-top:12px;width:100%;border-collapse:collapse;font-size:12.5px}
.tpo-table th,.tpo-table td{padding:7px 6px;border-bottom:1px solid rgba(255,255,255,.07);text-align:center;color:#cbd5e1}
.tpo-table th{color:#ffd77a;font-weight:1000}
.tpo-table td.q b{font-weight:1000}
.main .tpo-act{width:100%;margin-top:12px;padding:13px;font-size:16px;background:linear-gradient(135deg,#f59e0b,#ef4444)}
.tpo-foot{margin-top:10px;font-size:11.5px;color:#9fb3c8;text-align:center;line-height:1.5}
@media (min-width:601px){.tpo-wrap{max-width:840px}}
```

- [ ] **Step 3: Kiem tra CSS duoc parse + khong pha block cu**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
# (a) can bang hoac hoa: so `{` = so `}`
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -r "\$s=file_get_contents('css/mystyle.css'); \$o=substr_count(\$s,'{'); \$c=substr_count(\$s,'}'); echo \"braces open=\$o close=\$c lines=\".count(file('css/mystyle.css')).PHP_EOL; if(\$o!==\$c){echo 'UNBALANCED'.PHP_EOL;exit(1);}"
# Expected: open == close, lines > 7846 (the huyetmach block da can bang truoc do)
# (b) CJK + smart quotes: 0 dong
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]|[\x{4e00}-\x{9fff}\x{3000}-\x{303f}]' css/mystyle.css | head
# (c) cac class plan se in ra co that trong CSS
for c in tp-wrap tp-card tp-badge tp-badge-ico tp-emoji tp-msg tp-rail tp-link tp-tier tp-viewline tp-note tp-forge-wrap tp-stage tp-stage-img tp-stage-chip tp-aura tp-sweep tp-forge tp-forge-name tp-forge-lv tp-forge-pct tp-forge-cost tp-cols tp-h3 tp-attr tp-attr-name tp-attr-val tp-attr-cap tp-ms tp-ms-label tp-ms-bar tp-ms-fill tp-ms-done tp-slot tp-slot-head tp-slot-name tp-gate tp-reqs tp-tabbar tp-act tp-act-up tp-act-break tp-act-open tp-act-slot tp-act-ghost tpc-wrap tpc-head tpc-grid tpc-cell tpc-slot-tag tpc-detail tpc-detail-name tpc-detail-q tpc-detail-desc tpc-detail-rows tpc-acts tpc-act tpo-wrap tpo-card tpo-title tpo-pill tpo-truth tpo-result tpo-result-name tpo-result-q tpo-result-new tpo-table tpo-act tpo-foot; do grep -q "\.$c" css/mystyle.css || echo "THIU CSS: $c"; done
# Expected: khong in ra dong nao
```

> **KHONG kiem tra thi giac bang con so** (memory `verify-visually-not-just-metrics`). Buoc nay chi bao dam CSS parse duoc; **do so that (chieu rong `tp-wrap` 660/840, chieu cao `tp-stage` theo aspect-ratio 4/5, chu trong `tp-tabbar` khong de ngan) lam o Task 8 Step 6** — luc do da co trang that de render.

- [ ] **Step 4: Commit**

```bash
git add css/mystyle.css images/tianfu/
git commit -m "feat(tianfu): CSS .tp-/.tpc-/.tpo- + 6 bac hieu ung + anh (Task 7)"
```


---

### Task 8: VIET LAI `game/tianfu.php` — man chinh `.tp-*`

**Files:**
- Modify (VIET LAI TOAN BO, 174 dong → ~300 dong): `game/tianfu.php`
- Modify: `game.php:502` — bump `css/mystyle.css?v=2026101201`
- San trung NGOAI repo (KHONG commit): `E:/window/Temp/_tp_seed.php` (seed + in URL), `D:/XXJserver/phpstudy_pro/WWW/_tpshot.html` (harness iframe 390px, `rm -f` ngay sau khi chụp)

**Interfaces:**
- Consumes:
  - Task 1: `tianfu_groups()`, `tianfu_def($id)`, `tianfu_gate_name($g)`, `tianfu_gate_unit($g)`, `tianfu_quality_def/name/color/icon($q)`, `tianfu_lv_cap($q)`, `tianfu_lv_cost($lv)`, `tianfu_cap_percent()`, `tianfu_fx_class($q)`, `tianfu_cfg_key('gates')`, `tianfu_slot_defs()`, `tianfu_slot_open($ulv)`, `tianfu_open_pill()`, `tianfu_contributions($def)`.
  - Task 3: `tianfu_owned($sid,$dblj)`, `tianfu_activated($sid,$dblj)` (key = slot 1..4), `tianfu_bonuses($sid,$dblj)`.
  - Task 5: `tianfu_lvup()`, `tianfu_break()`, `tianfu_break_rows()`, `tianfu_player_wallet()`.
  - Task 6: `tianfu_slot_set($sid,$dblj,$id,$slot)`.
  - Task 7: block CSS `.tp-*` (cuoi `css/mystyle.css`) + `images/tianfu/nhom-*.jpg`, `images/tianfu/phu-cac.jpg`.
- Produces:
  - Route `cmd=tianfu` (da co san o `game.php:349`, khong can sua map) — trang nhan bien GET `$grp` (1..6) do `parse_str($Dcmd)` cua `game.php:114` bien thanh bien toan cuc. **KHONG doc `$_GET` truc tiep** (le cua `game/linhcan.php:5`, `game/linhcan_quest.php:4`).
  - POST field `tp_action` ∈ `lvup|break|slot` + `tf_id` (int) + `tf_slot` (int, chi dung voi `slot`) — khuon `hm_action` (`game/huyetmach.php:19-32`) va `nb_action` (`game/thechat.php:16-22`).
  - `$tpImgVer` (bien cuc bo dau file) dung cho moi `?v=` cua `images/tianfu/`.
  - Chieu chuot cho Task 9/10: tabbar duoi in 2 URL `cmd=tianfu_collection` va `cmd=tianfu_open`; link trong nut trong khi chua co chu phu dung `cmd=tianfu_collection&id=<id>` va `?slot=<n>` — **Task 9 PHAI doc duoc `id` va `slot`**.

> **[CHOT] "Chủ phú" = thien phu dang nam o Phu Vi 1**, khong phai "mon khai mo dau tien" hay "mon Lv cao nhat". Day dung nghia cua cot mirror `game1.tianfu_id` ma Task 3 `tianfu_recalc_mirror()` ghi (slot 1 → 4 cot mirror), va khop voi spec §4.1 "CARD 1 — Chủ Phú". Khi Phu Vi 1 trong, CARD 1 o trang thai "chưa lập Chủ Phú" (in nut mo Phong Khai Phu, KHONG in nut Nang Cap).

> **[CHOT] Task 8 khong dang ký route mới.** `game.php:349` chi duoc them `'tianfu_collection'` va `'tianfu_open'` trong Task 9/10 — tung task dang ky route CUA NO ngay khi file cua no xuat hien, de khong co trang thai "route tro den file chua ton tai" (`include` se Fatál). Trong Task 8, hai tab Phu Cac / Khai Phu **van in ra duoc** (URL encode binh thuong) nhung bam se chua chay; Step 6 chi yeu cau kiem tra 2 tab `Thiên Phú` + `Nhân Vật` dan den trang that.

> **[CHOT] Ten bien trong trang bat dau bang `tp`** (`$tpOwn`, `$tpAct`, `$tpB`, `$tpView`…) vi `parse_str` cua `game.php:114` bien MOI cap trong chuoi `cmd=` thanh bien toan cuc — ten ngan nhu `$lv`, `$id`, `$q`, `$slot`, `$own` se de trung voi bien cua chuc nang khac hoac bi chinh chuoi cmd ghi de. Day la loi chi minh hoa bang `game/linhcan_wash.php:3`.

> **[CHOT] Emoji luon bọc `<i class="tp-emoji">`** (rule Task 7 Step 2). `⚗` (U+2697) va `⛰` (U+26F0) la ky tu BMP: Chrome desktop co the render glyph khong co mau, lech o flex — bai hoc `docs/UI_XIULIAN.md` §9.1 (muc "emoji BMP").

- [ ] **Step 1: Doc het file cu de biet minh XOA gi** (khong xoa gi o Task 8 — Task 12 moi cat CSS + ham cu; Task 8 chi ghi de file):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
grep -n "talentConfig\|talent_alert\|talent_amount_cmd\|talent_action_html\|ulv < 30\|wbts1\|\$nt\|cmd=tianfu" game/tianfu.php
# Expected (174 dong cu): talentConfig :12, talent_alert :22, talent_amount_cmd :26,
#   talent_action_html :30, khoa ulv<30 :45, wbts1 :63, nt :74, vong chia diem :88
grep -rn "talent_alert\|talent_amount_cmd\|talent_action_html" --include=*.php . | grep -v "^./docs/"
# Expected: chi game/tianfu.php — da do 2026-10-12, khong caller nao khac (spec §6)
```

- [ ] **Step 2: Ghi toan bo `game/tianfu.php`** — dung nguyen khoi logic sau (day la deliverable, KHONG rut gon, KHONG them chuc nang khac):

```php
<?php
// THIEN PHU — man chinh: Chủ Phu (Phu Vi 1) + rail 6 nhom + dai kich 4 Phu Vi + 7 cong.
// Logic: class/function/tianfu.php | Config: class/config/tianfu_config.json
// CSS: css/mystyle.css (block .tp-*) | Anh: images/tianfu/
// Khuon goi ham + POST: game/huyetmach.php:1-60. Trang cu (7 o chia diem) da cat — spec §6.

$tpImgVer = '2026101201'; // bump khi doi anh trong images/tianfu/

// ===== POST: Nang Cap / Dot Pham / Bat-Thao Phu Vi (khuon game/huyetmach.php:19-32) =====
$tpMsg = '';
$tpMsgOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tp_action'])) {
    $tpPid = isset($_POST['tf_id']) ? intval($_POST['tf_id']) : 0;
    if ($_POST['tp_action'] === 'lvup') {
        $tpR = \player\tianfu_lvup($sid, $dblj, $tpPid);
    } elseif ($_POST['tp_action'] === 'break') {
        $tpR = \player\tianfu_break($sid, $dblj, $tpPid);
    } elseif ($_POST['tp_action'] === 'slot') {
        $tpPslot = isset($_POST['tf_slot']) ? intval($_POST['tf_slot']) : 0;
        $tpR = \player\tianfu_slot_set($sid, $dblj, $tpPid, $tpPslot);
    } else {
        $tpR = false;
    }
    if (is_array($tpR)) {
        $tpMsg = isset($tpR['msg']) ? $tpR['msg'] : '';
        $tpMsgOk = !empty($tpR['ok']);
    }
}

// ===== DOC DU LIEU (sau POST de so lieu la gia tri moi) =====
$player   = \player\getplayer($sid, $dblj);
$tpGrps   = \player\tianfu_groups();
$tpGates  = \player\tianfu_cfg_key('gates');
$tpOwn    = \player\tianfu_owned($sid, $dblj);
$tpAct    = \player\tianfu_activated($sid, $dblj);
$tpB      = \player\tianfu_bonuses($sid, $dblj);
$tpW      = \player\tianfu_player_wallet($sid, $dblj);
$tpCap    = \player\tianfu_cap_percent();
$tpSlots  = \player\tianfu_slot_defs();
$tpOpened = \player\tianfu_slot_open(intval($player->ulv));

// Nhom dang xem: cmd=tianfu&grp=N > nhom cua chu phu > 1.  ($grp do parse_str sinh ra)
$tpView = isset($grp) ? intval($grp) : 0;
if (!isset($tpGrps[$tpView])) {
    $tpView = 1;
    $tpMid0 = intval($player->tianfu_id);
    if ($tpMid0 > 0 && isset($tpOwn[$tpMid0])) {
        $tpView = intval($tpOwn[$tpMid0]['group']);
    }
}
$tpG = $tpGrps[$tpView];

// Chu phu = o Phu Vi 1 (guong cot game1.tianfu_id — Task 3 Step "recalc_mirror")
$tpMasterId  = intval($player->tianfu_id);
$tpMaster    = ($tpMasterId > 0 && isset($tpOwn[$tpMasterId])) ? $tpOwn[$tpMasterId] : false;
$tpMasterDef = $tpMaster ? \player\tianfu_def($tpMasterId) : false;
$tpQ         = $tpMaster ? intval($tpMaster['quality']) : 0;
$tpLv        = $tpMaster ? intval($tpMaster['lv']) : 0;
$tpLvCap     = \player\tianfu_lv_cap($tpQ);
$tpFx        = \player\tianfu_fx_class($tpQ);
$tpTierColor = $tpQ > 0 ? \player\tianfu_quality_color($tpQ) : '#9e9e9e';
$tpQName     = $tpQ > 0 ? \player\tianfu_quality_name($tpQ) : 'Chưa khai phú';
$tpQIcon     = $tpQ > 0 ? \player\tianfu_quality_icon($tpQ) : '⚪';

// Dem so mon da so huu trong tung nhom — dung cho rail (is-done) + do sang cua noi (is-lit)
$tpGrpHave = array();
foreach ($tpOwn as $tpRow) {
    $tpGi = intval($tpRow['group']);
    if (!isset($tpGrpHave[$tpGi])) { $tpGrpHave[$tpGi] = 0; }
    if (intval($tpRow['have']) > 0) { $tpGrpHave[$tpGi]++; }
}

// Nang Cap: cong thuc phi doc tu config, tinh theo Lv HIEN TAI
$tpLvCost  = \player\tianfu_lv_cost($tpLv);
$tpCostTf  = intval($tpLvCost['tf']);
$tpCostLt  = intval($tpLvCost['linh_thach']);
$tpAtCap   = ($tpMaster && $tpLv >= $tpLvCap);
$tpTfOk    = ($tpW['tf'] >= $tpCostTf);
$tpLtOk    = ($tpW['uyxb'] >= $tpCostLt);
$tpCanLvup = ($tpMaster && !$tpAtCap && $tpTfOk && $tpLtOk);

// Cong chinh cua chu phu: %/cap (marginal) + gia tri gate dang chay (da cong moi o + clamp 30)
$tpGateKey  = $tpMasterDef ? $tpMasterDef['gate'] : '';
$tpGateName = $tpGateKey !== '' ? \player\tianfu_gate_name($tpGateKey) : '';
$tpGateUnit = $tpGateKey !== '' ? \player\tianfu_gate_unit($tpGateKey) : '%';
$tpPerTxt   = '';
if ($tpMasterDef) {
    $tpContr  = \player\tianfu_contributions($tpMasterDef);
    $tpPerTxt = '+' . floatval($tpContr[$tpGateKey]) . '%/cấp';
}
$tpNowPct = $tpGateKey !== '' ? floatval($tpB[$tpGateKey]) : 0.0;

// Moc tong Lv (thanh do)
$tpMsTotal = intval($tpB['total_lv']);
$tpMsNext  = intval($tpB['next_milestone_lv']);
$tpMsWidth = $tpMsNext > 0 ? min(100, round($tpMsTotal / $tpMsNext * 100, 1)) : 100;

// Dot Pham (chi khi co chu phu): 4 dong dieu kien + tinh tran cho nut
$tpBreakRows = array();
$tpBreakOk   = false;
$tpNextQd    = false;
if ($tpMaster && $tpQ < 10) {
    $tpBreakRows = \player\tianfu_break_rows($sid, $dblj, $tpMasterId);
    $tpBreakOk   = true;
    foreach ($tpBreakRows as $tpBr) { if (empty($tpBr['ok'])) { $tpBreakOk = false; } }
    $tpNextQd = \player\tianfu_quality_def($tpQ + 1);
}

// Dan 114 trong tui — in o trang thai "chua lap chu phu" (Task 4: djid tu config)
$tpPill    = \player\tianfu_open_pill();
$tpPillSum = 0;
$tpPst = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
$tpPst->execute(array($sid, intval($tpPill['djid'])));
$tpProw = $tpPst->fetch(PDO::FETCH_ASSOC);
if ($tpProw) { $tpPillSum = intval($tpProw['djsum']); }

// URL: MOI lan render phai encode lai (token tieu mot lan — Global Constraints)
$tpSelfCmd = $encode->encode("cmd=tianfu&sid=$sid");
$tpCollCmd = $encode->encode("cmd=tianfu_collection&sid=$sid");
$tpOpenCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
$tpZtCmd   = $encode->encode("cmd=zhuangtai&sid=$sid");
$tpPostCmd = $encode->encode("cmd=tianfu&grp=$tpView&sid=$sid");
```

Tiep theo trong CUNG file `game/tianfu.php` (thoat PHP sang template, dung khuon `game/huyetmach.php:162+`):

```php
?>
<div class="tp-wrap tp-fx<?= $tpFx ?>" style="--tp-tier:<?= $tpTierColor ?>;--tp-color:<?= $tpG['color'] ?>">
<?php if ($tpMsg !== ''): ?>
    <div class="tp-msg <?= $tpMsgOk ? 'ok' : 'bad' ?>"><?= htmlspecialchars($tpMsg) ?></div>
<?php endif; ?>

<!-- ===== CARD 1: chu phu + rail 6 nhom + dai kich ===== -->
<div class="tp-card tp-card-main">
    <div class="tp-head">
        <div class="tp-hrow">
            <span class="tp-title">THIÊN PHÚ</span>
            <span class="tp-badge" style="border-color:<?= $tpTierColor ?>66;color:<?= $tpTierColor ?>"><i class="tp-badge-ico"><?= $tpQIcon ?></i><?= htmlspecialchars($tpQName) ?> · trần <?= $tpLvCap ?></span>
        </div>
    </div>

    <div class="tp-rail">
<?php $tpGl = array_values($tpGrps); $tpLast = count($tpGl) - 1;
foreach ($tpGl as $tpKi => $tpGrp):
    $tpGi   = intval($tpGrp['id']);
    $tpHave = isset($tpGrpHave[$tpGi]) ? $tpGrpHave[$tpGi] : 0;
    $tpCls  = 'tp-tier' . ($tpHave > 0 ? ' is-done' : '') . ($tpGi === intval($tpG['id']) ? ' is-view' : '');
    $tpGcmd = $encode->encode("cmd=tianfu&grp=$tpGi&sid=$sid");
?>
        <a class="<?= $tpCls ?>" href="?cmd=<?= $tpGcmd ?>"><span><i class="tp-emoji"><?= $tpGrp['icon'] ?></i></span><b><?= htmlspecialchars($tpGrp['name']) ?></b><em><?= $tpHave ?>/3</em></a>
<?php if ($tpKi < $tpLast): ?>
        <span class="tp-link<?= $tpHave > 0 ? ' is-lit' : '' ?>" aria-hidden="true"></span>
<?php endif; ?>
<?php endforeach; ?>
    </div>

    <div class="tp-viewline">Đang xem: <span style="color:<?= $tpG['color'] ?>"><i class="tp-emoji"><?= $tpG['icon'] ?></i> <?= htmlspecialchars($tpG['name']) ?></span> · Chủ phú: <?php
        if ($tpMaster) {
            echo '<span style="color:' . $tpTierColor . '"><i class="tp-emoji">' . $tpMaster['icon'] . '</i> ' . htmlspecialchars($tpMaster['name']) . '</span>';
        } else {
            echo '<span style="color:#64748b">chưa lập</span>';
        }
    ?></div>
<?php if (!$tpMaster): ?>
    <div class="tp-note">Chưa có Chủ Phú — vào <b>Phú Các</b> để lập, hoặc <b>Khai Phú</b> để uống <?= htmlspecialchars($tpPill['name']) ?>.</div>
<?php elseif (count($tpOpened) < 4): ?>
    <div class="tp-note dim">Còn <?= 4 - count($tpOpened) ?> Phú Vi chưa mở — cần cấp <?= intval($tpSlots[count($tpOpened)]['ulv']) ?>.</div>
<?php endif; ?>

    <div class="tp-forge-wrap">
        <div class="tp-stage">
            <img class="tp-stage-img" src="./<?= $tpG['img'] ?>?v=<?= $tpImgVer ?>" alt="<?= htmlspecialchars($tpG['name']) ?>">
            <span class="tp-aura" aria-hidden="true"></span>
            <span class="tp-sweep" aria-hidden="true"></span>
<?php if ($tpMaster && intval($tpMaster['group']) === intval($tpG['id'])): ?>
            <a class="tp-stage-chip" style="--tp-color:<?= $tpTierColor ?>" href="?cmd=<?= $encode->encode("cmd=tianfu_collection&id=$tpMasterId&sid=$sid") ?>"><i class="tp-emoji"><?= $tpMaster['icon'] ?></i><b><?= htmlspecialchars($tpMaster['short']) ?></b></a>
<?php endif; ?>
        </div>

        <div class="tp-forge">
<?php if ($tpMaster): ?>
            <div class="tp-forge-name"><i class="tp-emoji"><?= $tpMaster['icon'] ?></i> <?= htmlspecialchars($tpMaster['name']) ?></div>
            <div class="tp-forge-lv">Cấp <b><?= $tpLv ?></b> / <?= $tpLvCap ?></div>
            <div class="tp-forge-pct"><?= $tpPerTxt ?> · <?= htmlspecialchars($tpGateName) ?>: <b><?= $tpNowPct ?><?= htmlspecialchars($tpGateUnit) ?></b> <span class="tp-attr-cap">(trần <?= $tpCap ?>%)</span></div>
<?php if ($tpAtCap): ?>
            <div class="tp-note dim">Trần <?= $tpLvCap ?> cấp — phải <b>Đột Phá</b> phẩm chất mới luyện tiếp được.</div>
<?php else: ?>
            <div class="tp-forge-cost">
                <span class="<?= $tpTfOk ? 'ok' : 'no' ?>"><i class="tp-emoji">🎯</i> Điểm tf <b><?= intval($tpW['tf']) ?>/<?= $tpCostTf ?></b></span>
                <span class="<?= $tpLtOk ? 'ok' : 'no' ?>"><i class="tp-emoji">💰</i> Linh Thạch <b><?= number_format($tpW['uyxb'], 0, ',', '.') ?>/<?= number_format($tpCostLt, 0, ',', '.') ?></b></span>
            </div>
            <form method="post" action="?cmd=<?= $tpPostCmd ?>">
                <input type="hidden" name="tp_action" value="lvup">
                <input type="hidden" name="tf_id" value="<?= $tpMasterId ?>">
                <button type="submit" class="tp-act tp-act-up<?= $tpCanLvup ? '' : ' is-off' ?>" <?= $tpCanLvup ? '' : 'disabled' ?>>Nâng Cấp</button>
            </form>
<?php endif; ?>
<?php else: ?>
            <div class="tp-forge-name">Chưa lập Chủ Phú</div>
            <div class="tp-forge-pct">Trong túi có <b><?= $tpPillSum ?></b> <?= htmlspecialchars($tpPill['name']) ?>. Mỗi lần khai mở là một thiên phú; trùng sẽ tăng thêm một phẩm chất.</div>
            <a class="tp-act tp-act-open" href="?cmd=<?= $tpOpenCmd ?>">Khai Phú</a>
<?php endif; ?>
        </div>
    </div>
</div>
```

Tiep noi vao cung file (CARD 2 + tabbar — day la doan ket thuc, file KHONG can dong `?>`):

```php
<!-- ===== CARD 2: 2 cot — ky nang kich hoat | 4 Phu Vi + Dot Pham ===== -->
<div class="tp-card tp-cols">
    <div class="tp-col">
        <div class="tp-h3">Kỹ Năng Kích Hoạt</div>
<?php foreach ($tpGates as $tpGkey => $tpGd):
    $tpVal = floatval($tpB[$tpGkey]);
    $tpVcls = $tpVal <= 0 ? ' is-zero' : ($tpVal >= $tpCap ? ' is-max' : '');
?>
        <div class="tp-attr">
            <span class="tp-attr-name"><?= htmlspecialchars($tpGd['name']) ?></span>
            <b class="tp-attr-val<?= $tpVcls ?>"><?= $tpVal ?><?= htmlspecialchars($tpGd['unit']) ?></b>
            <span class="tp-attr-cap">trần <?= $tpCap ?>%</span>
        </div>
<?php endforeach; ?>
        <div class="tp-ms">
            <div class="tp-ms-label"><span><?php
                    if ($tpB['milestone_name'] !== '') {
                        echo htmlspecialchars($tpB['milestone_name']) . ' +' . floatval($tpB['milestone_percent']) . '%';
                    } else {
                        echo 'Chưa đạt mốc nào';
                    }
                ?></span><span>Tổng cấp <?= $tpMsTotal ?><?php if ($tpMsNext > 0) echo ' / ' . $tpMsNext; ?></span></div>
            <div class="tp-ms-bar"><div class="tp-ms-fill" style="width:<?= $tpMsWidth ?>%"></div></div>
            <div class="tp-ms-done"><?php
                if ($tpB['next_milestone_name'] !== '') {
                    echo 'Mốc kế: ' . htmlspecialchars($tpB['next_milestone_name'])
                        . ' (+' . floatval($tpB['next_milestone_percent']) . '%) khi tổng cấp ' . $tpMsNext . '.';
                } else {
                    echo 'Toàn bộ mốc đã mở.';
                }
            ?></div>
        </div>
    </div>

    <div class="tp-col">
        <div class="tp-h3">4 Phú Vi</div>
<?php foreach ($tpSlots as $tpSd):
    $tpSi     = intval($tpSd['i']);
    $tpARow   = isset($tpAct[$tpSi]) ? $tpAct[$tpSi] : false;
    $tpSOpen  = in_array($tpSi, $tpOpened);
    $tpScls   = 'tp-slot' . ($tpARow ? ' is-on' : ($tpSOpen ? '' : ' is-locked'));
    $tpSColor = $tpARow ? \player\tianfu_quality_color(intval($tpARow['quality'])) : '#7dd3fc';
?>
        <div class="<?= $tpScls ?>" style="--tp-color:<?= $tpSColor ?>">
            <div class="tp-slot-head"><span><?= htmlspecialchars($tpSd['name']) ?></span><b>Ô <?= $tpSi ?></b></div>
<?php if ($tpARow): ?>
            <div class="tp-slot-name"><i class="tp-emoji"><?= $tpARow['icon'] ?></i> <?= htmlspecialchars($tpARow['name']) ?></div>
            <span class="tp-gate"><?= htmlspecialchars(\player\tianfu_gate_name($tpARow['gate'])) ?> · <?= htmlspecialchars(\player\tianfu_quality_name(intval($tpARow['quality']))) ?> · Lv <?= intval($tpARow['lv']) ?></span>
            <form method="post" action="?cmd=<?= $tpPostCmd ?>">
                <input type="hidden" name="tp_action" value="slot">
                <input type="hidden" name="tf_id" value="0">
                <input type="hidden" name="tf_slot" value="<?= $tpSi ?>">
                <button type="submit" class="tp-act tp-act-ghost">Bỏ</button>
            </form>
<?php elseif ($tpSOpen): ?>
            <div class="tp-slot-name empty">— trống —</div>
            <a class="tp-act tp-act-ghost" href="?cmd=<?= $encode->encode("cmd=tianfu_collection&slot=$tpSi&sid=$sid") ?>">Chọn ở Phú Các</a>
<?php else: ?>
            <div class="tp-slot-name empty">Chưa mở</div>
            <div class="tp-reqs"><span class="no">[Khóa]</span> Cần cấp <?= intval($tpSd['ulv']) ?>.</div>
<?php endif; ?>
        </div>
<?php endforeach; ?>

        <div class="tp-h3" style="margin-top:12px">Đột Phá Phẩm Chất</div>
<?php if (!$tpMaster): ?>
        <div class="tp-reqs">Chưa có Chủ Phú để đột phá.</div>
<?php elseif ($tpQ >= 10): ?>
        <div class="tp-gate" style="--tp-color:<?= $tpTierColor ?>"><?= $tpQIcon ?> <?= htmlspecialchars($tpQName) ?></div>
        <div class="tp-reqs">Đạo Phẩm là cực hạn của thiên phú.</div>
<?php else: ?>
        <div class="tp-gate" style="--tp-color:<?= $tpNextQd['color'] ?>"><i class="tp-emoji"><?= $tpQIcon ?></i> <?= htmlspecialchars($tpQName) ?> → <i class="tp-emoji"><?= $tpNextQd['icon'] ?></i> <?= htmlspecialchars($tpNextQd['name']) ?> · trần <?= intval($tpNextQd['lv_cap']) ?></div>
        <div class="tp-reqs">
<?php foreach ($tpBreakRows as $tpBr): ?>
            <div><span class="<?= $tpBr['ok'] ? 'ok' : 'no' ?>">[<?= $tpBr['ok'] ? 'Đủ' : 'Thiếu' ?>]</span> <?= htmlspecialchars($tpBr['text']) ?></div>
<?php endforeach; ?>
        </div>
        <form method="post" action="?cmd=<?= $tpPostCmd ?>">
            <input type="hidden" name="tp_action" value="break">
            <input type="hidden" name="tf_id" value="<?= $tpMasterId ?>">
            <button type="submit" class="tp-act tp-act-break<?= $tpBreakOk ? '' : ' is-off' ?>" <?= $tpBreakOk ? '' : 'disabled' ?>>Đột Phá</button>
        </form>
<?php endif; ?>
    </div>
</div>

<nav class="tp-tabbar">
    <a class="on" href="?cmd=<?= $tpSelfCmd ?>"><i>🌟</i><b>Thiên Phú</b></a>
    <a href="?cmd=<?= $tpCollCmd ?>"><i>🗄</i><b>Phú Các</b></a>
    <a href="?cmd=<?= $tpOpenCmd ?>"><i>⚗</i><b>Khai Phú</b></a>
    <a href="?cmd=<?= $tpZtCmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
</div>
```

> **Ba cat cu phai het khi ghi de file** (kiem tra bang `grep` o Step 4): `talentConfig`, `talent_alert()` / `talent_amount_cmd()` / `talent_action_html()`, khoa `if ($player->ulv < 30)`, nhanh `wbts1` (nut "Gợi ý" tieu 1 `uczb`), nhanh `$nt` ("Nghịch thiên cải mệnh" — `SET tf = ulv*5`, loi kinh te theo spec §6), vong chia diem 7 o `canshuhp/canshugj/...`, 7 o `.talent-stat`, nut "Trở Về"/"Đại Sảnh" (tabbar `Nhân Vật` thay the).

**Doi chieu spec §4.6 (chuoi UI) — 3 lech co y, phai bao lai chu du an o ban giao Task 12 Step 13:**

| spec §4.6 yeu cau | plan dung | Vi sao lech |
|---|---|---|
| nut `Tro Ve` | **khong co** — tabbar `Nhân Vật` thay the | Tabbar 4 nhan da la duong ve; them nut `Trở Về` la **trung duong di** (le `nut-gon-nhan-ngan`: nhan cat het phan thua) |
| nut `Dot Pham` | `Đột Phá` | "Đột Phẩm" khong phai tu nguoi Viet dung; hanh dong nay la **dot pha len pham cao hon** → `Đột Phá`. Da dong bo voi `.tp-act-break` (Task 7) va `break_quality` trong config |
| nut `Lap Chu Phu` | `PV1` / `PV2` / `PV3` / `PV4` (o Phu Cac, Task 9 `:3339`) va `Chọn ở Phú Các` (o trang chinh `:2797`) | `Lập Chủ Phú` = 11 ky tu, **tran nut 40px** o 390px. O Phu Cac thi nguoi choi chon **o nao** chu khong chi o 1 → nhan phai mang so o. Chu "Chủ Phú" van xuat hien day du o CARD 1 (`:2729` "Chưa lập Chủ Phú") va `.tp-note` (`:2695`) |

Con lai **dung spec**: `Nâng Cấp` (Task 8 `:2725`, Task 9 `:3307`) · `Đột Phá` (`:2821`, `:3313`) ·
`Khai Mở` (Task 10 `:3804`) · 18 ten thien phu co dau day du (config Task 1, dung nguyen van spec §4.6).

> **[CHOT] spec §4.6 con yeu cau nhan doi nghia `"Ưu thế" / "Nhược điểm"` trong `.tp-attr`,
> "KHONG dung `Phạt`".** Yeu cau nay **khong ap dung duoc cho Thien Phu**: ca **7 cong deu la
> loi** (config Task 1 `gates`: `forge_cost` cung viet "GIẢM số Linh Thạch phải trả" — loi, khong
> phai phat), nen **khong co cot "Nhược điểm" de dat ten**. Plan giu `.tp-attr-name` = ten cong
> (`Tốc độ tu luyện`, `Ngưỡng rơi đồ`, …) va `.tp-attr-cap` = tran (`30%`).
>
> **Neu sau nay them cong mang bat loi** (vi du "tang exp nhung giam drop") thi **bat buoc** dat
> cap nhan la `Ưu thế` / `Nhược điểm` — **tuyet doi khong** viet `Phạt`, `Bất lợi`, `Trừ`. Day la
> le da chot tu he Nhuc Than (`feedback-vietnamese-ui-terminology`): cap doi nghia phai tu nhien
> nhu tieng Viet, khong dich sat thuat ngu game Trung. Ghi lai o Task 12 Step 13 muc "no ky thuat".

- [ ] **Step 3: Bump CSS** — `game.php:502` `css/mystyle.css?v=2026101112` → `?v=2026101201`. Neu khong bump, trinh duyet dan `.tp-*` tu ban CSS cu va trang vo style (khong loi gi ca, rat kho nghi).

- [ ] **Step 4: Lint + que ky tu la + bao dong da cat het code cu**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l game/tianfu.php && "$P" -l game.php
# (a) khong con ma cu:
grep -n "talentConfig\|talent_alert\|talent_amount_cmd\|talent_action_html\|ulv < 30\|wbts1\|canshugj\|talent-" game/tianfu.php
# Expected: khong in ra dong nao
# (b) CJK + smart quotes trong file moi = 0 dong
grep -nP '[\x{2E80}-\x{9FFF}\x{3000}-\x{303F}\x{2018}\x{2019}\x{201C}\x{201D}]' game/tianfu.php | head
# Expected: khong in ra dong nao (dau · va — duoc phep: khong thuoi 2 dai tren)
# (c) dem class TP that duoc in ra:
grep -o 'class="[^"]*tp[^"]*"' game/tianfu.php | wc -l
# Expected >= 25
# (d) khong co ?? / <=> / array[] ngam dinh? PHP 5.6 van chap nhan [], nen chi can check 2 phep toan:
grep -n '??\|<=>' game/tianfu.php
# Expected: khong in ra dong nao
```

- [ ] **Step 5: Seed tai khoan e2e + sinh URL** (san trung ngoai repo, KHONG `git add`)

Chay `tests/e2e_fixture.php` truoc neu chua co nhan vat test (`sid=e2e2e2...`, `uid=987650`, cv1000, `uyxb=5.000.000` — fixture tu lo `endtime` dung lech gio). Roi ghi `E:/window/Temp/_tp_seed.php`:

```php
<?php
// _tp_seed.php — nap Phu Cac mau cho tai khoan e2e de chay/route + chup anh Task 8.
// Chay: php _tp_seed.php [--q=8 --lv=20]
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = 'D:/XXJserver/phpstudy_pro/WWW';
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/tests/tianfu_test_lib.php';

$sid  = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$uid  = 987650;
$q = 8; $lv = 20;
foreach ($argv as $a) {
    if (strpos($a, '--q=') === 0)  { $q  = intval(substr($a, 4)); }
    if (strpos($a, '--lv=') === 0) { $lv = intval(substr($a, 5)); }
}
$db = tf_test_db();
$n = $db->exec("DELETE FROM player_tianfu WHERE sid = '" . $sid . "'");
if (!$db->query("SELECT 1 FROM game1 WHERE sid = '" . $sid . "'")->fetch()) {
    echo "THIU NHAN VAT TEST — chay: php tests/e2e_fixture.php\n"; exit(1);
}
// 4 o dang bat = 4 nhom khac nhau (Task 6 chan trung cong): 1 / 4 / 7 / 16
tf_ins_owned($db, $sid, 1,  $q,  $lv, 1);   // Ngo Tinh    — CHỦ PHÚ (fx theo --q)
tf_ins_owned($db, $sid, 4,  5,   12,   2);  // Tai Van     — drop
tf_ins_owned($db, $sid, 7,  3,   10,   3);  // Dan Dao     — alchemy
tf_ins_owned($db, $sid, 16, 1,    4,   0);  // Than Suat   — chua bat (test o trong)
// 2 o duoc de o Lv cao cho moc tong Lv va milestone bar
tf_ins_owned($db, $sid, 13, 8,   40,   0);   // q8 lv_cap 48 >= 40 (q2 chi 14 — se bi chan)
tf_ins_owned($db, $sid, 18, 9,   30,   0);
$db->exec("UPDATE game1 SET tf = 250 WHERE sid = '" . $sid . "'");
tf_give_daoju($db, $sid, $uid, 114, 3);
\player\tianfu_recalc_mirror($sid, $db);
\player\player_cache_clear($sid);
$row = $db->query("SELECT tianfu_id, tianfu_quality, tianfu_lv, tianfu_power, tf FROM game1 WHERE sid = '" . $sid . "'")->fetch(PDO::FETCH_ASSOC);
echo "MIRROR: " . json_encode($row) . "  DELETE_ROWS=$n\n";
echo "TONG_LV=" . \player\tianfu_bonuses($sid, $db)['total_lv'] . "\n";
```

```bash
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" E:/window/Temp/_tp_seed.php --q=8 --lv=20
# Expected: MIRROR co tianfu_id=1, tianfu_quality=8, tianfu_lv=20, tianfu_power>0, tf=250
#           TONG_LV = 20+12+10+4+40+30 = 116   (total_lv tinh CA o slot=0 — xem Task 3 muc 4)
"$P" E:/window/Temp/_tp_seed.php --q=1 --lv=3
# Expected: fx0 — kiem tra "bac thap nhat van phai thay quang mang tinh" (Step 6)
```

In URL du dung (mot token chi dung DUOC MOT LAN — moi lan mo lai phai encode lai):

```php
<?php
// E:/window/Temp/_tp_url.php — _tp_url.php "cmd=tianfu&sid=..."  -> in URL day du
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/encode.php';
$enc = new \encode\encode();
echo 'http://127.0.0.1:8091/game.php?cmd=' . $enc->encode($argv[1]) . "\n";
```

```bash
SID=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2
U=$("$P" E:/window/Temp/_tp_url.php "cmd=tianfu&sid=$SID"); echo "$U"
curl -s "$U" | wc -c          # Expected: LEN > 500 (neu < 500 la trang chet/Fatal, khong phai "nho")
curl -s "$U" | grep -c 'class="tp-wrap'   # Expected: 1
```

POST smoke (moi lan mot token moi — dung `curl -s -X POST -d ... "$U"`):

```bash
for ACT in "tp_action=lvup&tf_id=1" "tp_action=break&tf_id=1" "tp_action=slot&tf_id=0&tf_slot=2"; do
  U=$("$P" E:/window/Temp/_tp_url.php "cmd=tianfu&grp=1&sid=$SID")
  echo "--- $ACT"
  curl -s -X POST -d "$ACT" "$U" | grep -o 'class="tp-msg \(ok\|bad\)"'
done
"$P" -r 'require_once "D:/XXJserver/phpstudy_pro/WWW/tests/tianfu_test_lib.php"; $db = tf_test_db();
$st = $db->prepare("SELECT tianfu_id, quality, lv, slot FROM player_tianfu WHERE sid = ? ORDER BY tianfu_id");
$st->execute(array("e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2"));
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { echo implode(" ", $r) . "\n"; }'
```
Expected: 3 dong `tp-msg` (lvup = ok, break = bad vi Lv 20 < tran 48 cua Pham 8 → "chua dat tran", slot = ok bo o 2) va bang `player_tianfu` co `slot=0` o id 4. Chay lai `"$P" E:/window/Temp/_tp_seed.php --q=8 --lv=20` de reset mau truoc khi sang Step 6.

- [ ] **Step 6: DO SO THAT + CHUP ANH (day la buoc that cua Task 8 — Step 7 Task 9/10 chi do lai sau khi co 3 trang)**

Chrome CLI, **KHONG dung browser-use MCP** (le `feedback-no-browser-mcp`), harness iframe vi san `--window-size` cua Chrome toi thieu ~500px (le `chrome-headless-screenshot`). Server dev chay o `127.0.0.1:8091` (cua 8090 bi `WsToastNotification.exe` chiem — le `project-dev-test-harness`).

`E:/window/Temp/_tp_harness.php`:

```php
<?php
// _tp_harness.php  <mobile|desktop> — viet _tpshot.html vao web root (iframe + do so)
$mode = isset($argv[1]) ? $argv[1] : 'mobile';
$w    = ($mode === 'desktop') ? 900 : 390;
$sid  = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/encode.php';
$enc = new \encode\encode();
$cmd = $enc->encode("cmd=tianfu&grp=1&sid=$sid");   // token dung MOT LAN — chay script nay moi lan
$html = '<!doctype html><meta charset="utf-8">'
    . '<style>body{margin:0;background:#222}iframe{width:' . $w . 'px;height:2400px;border:0;display:block}</style>'
    . '<iframe id="f" src="http://127.0.0.1:8091/game.php?cmd=' . $cmd . '"></iframe>'
    . '<pre id="out">PENDING</pre><script>'
    . 'var f=document.getElementById("f");f.onload=function(){setTimeout(function(){'
    . 'var d=f.contentDocument;d.body.style.zoom=1;'
    . 'var g=function(s){return d.querySelector(s)};'
    . 'var W=function(e){return e?Math.round(e.getBoundingClientRect().width):-1};'
    . 'var H=function(e){return e?Math.round(e.getBoundingClientRect().height):-1};'
    . 'var clip=[],tb=d.querySelectorAll(".tp-tabbar a b"),i;'
    . 'for(i=0;i<tb.length;i++){if(tb[i].scrollWidth>tb[i].clientWidth+1){clip.push(i+":"+tb[i].textContent)}}'
    . 'var im=d.querySelectorAll("img"),bad=0;for(i=0;i<im.length;i++){if(!im[i].complete||im[i].naturalWidth<1){bad++}}'
    . 'var st=g(".tp-stage");'
    . 'document.getElementById("out").textContent=['
    . '"WRAP="+W(g(".tp-wrap")),"STAGE="+W(st)+"x"+H(st),"RATIO="+(W(st)?(H(st)/W(st)).toFixed(3):"NA"),'
    . '"COLS="+W(g(".tp-cols")),"RAIL="+W(g(".tp-rail")),"TIERS="+d.querySelectorAll(".tp-tier").length,'
    . '"LINKS="+d.querySelectorAll(".tp-link").length,"SLOTS="+d.querySelectorAll(".tp-slot").length,'
    . '"ATTRS="+d.querySelectorAll(".tp-attr").length,"MSW="+(g(".tp-ms-fill")?g(".tp-ms-fill").style.width:"NA"),'
    . '"FX="+g(".tp-wrap").className,"IMG="+im.length+"/"+bad,"CLIP="+(clip.join(",")||"none"),'
    . '"BODYH="+H(d.body),"SCROLLW="+d.documentElement.scrollWidth].join("\\n");'
    . '},1800)};</script>';
file_put_contents('D:/XXJserver/phpstudy_pro/WWW/_tpshot.html', $html);
echo 'WROTE _tpshot.html iframe_w=' . $w . "\n";
```

`E:/window/Temp/_tpread.php` (đọc `<pre id="out">` — thieu `[^>]*` la khong khop du DOM da render, le `docs/UI_HUYETMACH.md` §9):

```php
<?php
$s = file_get_contents('E:/window/Temp/_tpdom.html');
if (preg_match('/<pre id="out"[^>]*>(.*?)<\/pre>/s', $s, $m)) {
    echo htmlspecialchars_decode($m[1]) . "\n";
} else {
    echo "THIU pre#out — Chrome tra 0 byte (user-data-dir cu) hoac iframe cross-origin (harness phai mo qua http://)\n";
    exit(1);
}
```

```bash
cd /d/XXJserver/phpstudy_pro/WWW
mkdir -p docs/img/thienphu
CHROME="C:/Program Files/Google/Chrome/Application/chrome.exe"; [ -x "$CHROME" ] || CHROME=$(which chrome)
SHOT="D:/XXJserver/phpstudy_pro/WWW/docs/img/thienphu"

"$P" E:/window/Temp/_tp_harness.php mobile
UDD="E:/window/Temp/tpshot_$$"
"$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
  --force-device-scale-factor=1 --user-data-dir="$UDD" \
  --window-size=820,2600 --virtual-time-budget=25000 \
  --screenshot="$SHOT/main-390-q8.png" "http://127.0.0.1:8091/_tpshot.html"
"$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
  --force-device-scale-factor=1 --user-data-dir="$UDD.2" \
  --window-size=820,2600 --virtual-time-budget=25000 \
  --dump-dom "http://127.0.0.1:8091/_tpshot.html" > E:/window/Temp/_tpdom.html
"$P" E:/window/Temp/_tpread.php
rm -f _tpshot.html
```

So phai dat (mobile 390, mau `--q=8 --lv=20`):

|bien|giá tri mong doi|vi sao|
|---|---|---|
|`WRAP`|`390`|`.tp-wrap{max-width:660px}` — viewport 390 nho hon nen bi viewport chan|
|`SCROLLW`|`390`|khong duoc tran ngang (rail 6 o + 5 noi co `overflow-x:auto`)|
|`STAGE` / `RATIO`|`~374x~468` / `1.250`|`aspect-ratio:4/5` trong `.tp-stage`|
|`TIERS` / `LINKS`|`6` / `5`|dung spec §4.1|
|`SLOTS` / `ATTRS`|`4` / `7`|4 Phu Vi, 7 cong|
|`CLIP`|`none`|4 nhan tabbar khong bi cat (le `nut-gon-nhan-ngan`)|
|`IMG`|`1/0`|1 anh nhom `nhom-ngo-tinh.jpg` tai `./images/tianfu/`|
|`FX`|chua `tp-fx4`|pham 8 → fx4 (bang `tianfu_fx_class`)|
|`MSW`|`77.3%`|`$tpMsWidth = min(100, round($tpMsTotal / $tpMsNext * 100, 1))` (`:2625`). Seed `--q=8 --lv=20` cho `total_lv = 20+12+10+4+40+30 = 116`; moc dat duoc 60 (Khai Phu +5%), moc ke **150** → `round(116/150*100,1) = 77.3`|
|`BODYH`|`> 900`|trang khong bị sụp (CSS chưa nạp thì BODYH < 400)|

> **Neu `MSW` ra `100%`** thi `next_milestone_lv` = 0 (tuc da qua het moc 500) → seed sai, kiem tra
> lai `_tp_seed.php`. **Neu ra `NA`** thi `.tp-ms-fill` khong ton tai → Task 7/8 thieu CSS hoac
> thieu markup, khong phai loi do luong. Nhan `.tp-ms-label` phai hien dung chu **`Tổng cấp 116 / 150`**.

Lan 2 — desktop: `"$P" E:/window/Temp/_tp_harness.php desktop` roi doi ten file thanh `$SHOT/main-840-q8.png`. **WRAP phai = 840** (`@media(min-width:601px)`) va `COLS` = 2 cot (do bang `.tp-cols` `getComputedStyle().display === 'grid'` — them dong `"DISPCS="+getComputedStyle(g(".tp-cols")).display` vao harness neu muon do).

Lan 3 — **fx0 (pham 1)**, de chung minh "bac thap nhat van phai thay duoc cai gi do" (le `feedback-tiered-visual-effects`): `"$P" E:/window/Temp/_tp_seed.php --q=1 --lv=3` roi `--q=1` trong ten anh (`$SHOT/main-390-q1.png`). Kiem tra bang mat: `.tp-aura` van la quang mau xam chung quanh chan dung, **khong dong nao**, khong co `.tp-sweep`.

**Bat buoc NHIN ANH, khong chi doc so** (le `verify-visually-not-just-metrics`): mo `$SHOT/main-390-q8.png` va doi chieu 1:1 voi `docs/img/huyetmach/` (cung khung 660/840, cung loai tabbar). Sau do **mo browser that** (`start chrome "http://127.0.0.1:8091/game.php?cmd=<token moi>"` — sinh token bang `_tp_url.php`) va bam thu: Nang Cap 1 lan, Do Pham khi chua du dieu kien (phai thấy `tp-msg bad`, khong phai trang trang), Bỏ 1 o, chuyen 2 tab roi quay lai. Le `feedback-careful-server-changes`: tat ca duong dan phai duoc bam bang tay it nhat 1 lan truoc khi ghi "xong".

- [ ] **Step 7: Commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
git status --porcelain | head        # kiem tra _tpshot.html KHONG con ton tai trong web root
git add game/tianfu.php game.php docs/img/thienphu/
git commit -m "feat(tianfu): trang chu Thien Phu .tp-* — chu phu, rail 6 nhom, 4 phu vi, dot pha (Task 8)"
```

> `game/tianfu.php` **chua tung duoc git theo doi** (repo chi track 13/69 file `game/`; do bang `git ls-files game`). Lenh `git add` o tren bat dau tracking file ghi de — dung tien le cua `game/huyetmach.php` (duoc them khi rework). **Van cam `git add -A`.**

---

### Task 9: PHU CAC — `game/tianfu_collection.php` (luoi 18 o + panel chi tiet + 3 nut)

**Files:**
- Create: `game/tianfu_collection.php` (~250 dong)
- Modify: `game.php:349` — them dong `'tianfu_collection'=>'game/tianfu_collection.php',` NGAY SAU `'tianfu'=>'game/tianfu.php',`
- Modify (3 selector Task 7 thieu/sai, phat hien khi viet markup Task 9 — xem Step 4): `css/mystyle.css` trong block `.tpc-*`
- San trung NGOAI repo (KHONG commit): `E:/window/Temp/_tpc_url.php` (in URL token moi), `D:/XXJserver/phpstudy_pro/WWW/_tpcshot.html` (harness iframe, `rm -f` ngay sau khi do)

**Interfaces:**
- Consumes:
  - Task 1: `tianfu_def($id)`, `tianfu_defs()`, `tianfu_groups()`, `tianfu_gate_name($g)`, `tianfu_gate_def($g)`, `tianfu_quality_name/color/icon($q)`, `tianfu_quality_def($q)`, `tianfu_lv_cap($q)`, `tianfu_lv_cost($lv)`, `tianfu_slot_defs()`, `tianfu_slot_open($ulv)`, `tianfu_contributions($def)`, `tianfu_cap_percent()`, `tianfu_fx_class($q)`, `tianfu_open_pill()`.
  - Task 3: `tianfu_owned($sid,$dblj)` (18 dong, key = id), `tianfu_activated($sid,$dblj)` (key = slot), `tianfu_bonuses($sid,$dblj)`.
  - Task 5: `tianfu_lvup()`, `tianfu_break()`, `tianfu_lvup_rows()`, `tianfu_break_rows()`, `tianfu_player_wallet()`.
  - Task 6: `tianfu_slot_set()`, `tianfu_slot_conflict($sid,$dblj,$id,$slot,$act)`.
  - Task 7: block CSS `.tpc-*` + cac class dung chung `.tp-msg` `.tp-note` `.tp-gate` `.tp-reqs` `.tp-tabbar` `.tp-emoji` `.tp-fx0..fx5`.
  - Task 8: POST **cung ten truong** `tp_action`/`tf_id`/`tf_slot`; GET `id` va `slot` ma cac link cua Task 8 sinh ra (`cmd=tianfu_collection&id=<id>`, `?slot=<n>`).
- Produces:
  - Route `cmd=tianfu_collection` (dang ky o chinh Task nay, Step 3). GET `id` (1..18) va `slot` (1..4) tro thanh bien toan cuc qua `parse_str` cua `game.php:114` — trang doc mot lan roi bien thanh `$tpcSel`/`$tpcPick`, **khong** dung `$_GET` (le `game/linhcan.php:5`).
  - Khong sinh ham moi, khong sinh cot moi, khong doi `class/function/tianfu.php`.
  - URL `cmd=tianfu_collection&id=<1..18>` cho Task 10 (nut ket qua Khai Phu tro ve panel cua mon vua so huu).

> **[CHOT] Panel chi tiet la server-render theo GET `id`, KHONG dung JS dong-goi va KHONG phu thuoc hover.** Ly do: (1) chuoi `?cmd=` tieu mot lan (Global Constraints) nen moi lan "mo panel" that su phai la mot request voi token moi — JS toggler se de lai link het han su dung khi nguoi choi bam nut Nang Cap; (2) hover bi cam lam dieu kien duy nhat (memory `feedback-hover-needs-tap`); (3) khuon cua repo chinh la view-state phia server: `game/huyetmach.php:150-200` (rail `?cmd=`) va `game/linhcan_quest.php`. Vi vay moi `.tpc-cell` la mot `<a href="?cmd=<token ...&id=N>`. `.tpc-cell:hover` trong CSS chi la phan phoi thi giac bo sung, xoa di van mo duoc bang cham.

> **[CHOT] O `is-lock` (chua so huu) VAN bam duoc va mo panel.** Panel in `desc` + dong "Chưa khai mở — cần Khai Phú Đan" + nut mo trang Khai Phu. KHONG in nut Nang Cap / Dot Pham / Bat vao Phu Vi cho mon chua so huu (du server tu choi `ok=false`, giao dien khong nen moi nguoi choi bam thu). Ly do: 18 ten thien phu la mot phan cua suc hut suu tam (spec §0 "Sưu tầm + chọn chủ phú"); an ten sau o xam thi khong con ly do mo Khai Phu. Doi chieu tien le: `docs/UI_HUYETMACH.md` cho xem huyet mach chua kich hoat.

> **[CHOT] Thu tu luoi = `id` 1..18 tang dan = thu tu 6 nhom (moi nhom 3 mon lien tiep) theo Task 1 config.** Mobile 3 cot -> 6 hang, moi hang **dung mot nhom** (hang 1 = id 1,2,3 = Ngo Tinh). Desktop 6 cot (spec §4.2) -> 3 hang, moi hang 2 nhom. **Khong them header nhom** vi block `.tpc-*` cua Task 7 khong co class do, va `icon` + `color` cua tung mon (Task 1) da du phan biet. Day la vi du chu y: `docs/img/thienphu/cac-390.png` se dung 6 hang = 6 nhom, de chu du an kiem tra ten khong dich.

> **[CHOT] `slot=N` (link "Chọn ở Phú Các" cua Task 8) = che do chon cho Phu Vi N, KHONG phai lenh ghi.** `$tpcPick` chi dung de (a) in chuoi chu `.tpc-head` "Chọn thiên phu cho Phú Vi N", (b) lam noi bat nut slot thu N trong panel. Moi lan ghi that su phai qua POST `tp_action=slot`; `tianfu_slot_set()` (Task 6) la noi duyet mo theo `ulv` va chan trung cong.

> **[CHOT] Khong hien "% dong gop cua rieng mot thien phu."** Cong thuc Task 3 la **cong raw ca cong, nhan milestone, clamp 30 mot lan cho ca cong** (lech Task 3 Step 3 co test `14.0`), nen "mon nay = X%" la so khong ton tai trong he. Panel chi in: `+<per_lv>%/cấp` (marginal, tu `tianfu_contributions()`), `trần 30%`, trang thai `Đang phát huy — Phú Vi N` / `Chưa phát huy — cần đặt vào Phú Vi`, va gia tri tong cua cong (`tianfu_bonuses()`) de nguoi choi tu so duong voi CARD 2 trang chu.

> **[CHOT] `fx` cua trang Phu Cac lay theo pham chat mon o Phu Vi 1 (chu phu), giong het Task 8**, khong lay theo mon dang mo panel — de hieu ung vien khung (`--tp-dur`, `--tp-glow`) khong doi lien tuc khi nguoi choi bam tung o (le `feedback-tiered-visual-effects`: phan tầng theo bật/chu phu, khong theo hover-state). Khi chua co chu phu (`activated_count = 0`) thi `fx0`.

- [ ] **Step 1: Tao `game/tianfu_collection.php` — khoi logic (PHP 5.6, moi bien bat dau bang `tpc`):**

```php
<?php
// THIEN PHU — PHU CAC: luoi 18 thien phu, panel chi tiet mo bang cham (server-render theo GET id),
// 3 nut hanh dong: Nang Cap / Dot Pham / Bat vao Phu Vi.
// Logic: class/function/tianfu.php | CSS: css/mystyle.css (block .tpc-*) | Anh: images/tianfu/
// Khuon POST + goi ham: game/tianfu.php (Task 8), game/huyetmach.php:19-32.
// Ten bien deu tpc* vi parse_str (game.php:114) bien moi cap trong chuoi cmd= thanh bien toan cuc.

$tpcImgVer = '2026101201'; // doi khi thay anh trong images/tianfu/ (giong $tpImgVer Task 8)

// ===== 1) GET (parse_str sinh bien toan cuc id/slot): mon dang mo + o dang chon =====
$tpcSel  = isset($id)   ? intval($id)   : 0;
$tpcPick = isset($slot) ? intval($slot) : 0;
if ($tpcPick < 1 || $tpcPick > 4) { $tpcPick = 0; }

// ===== 2) POST: dung 3 hanh dong cua Task 8, giu nguyen panel sau khi ghi =====
$tpcMsg = '';
$tpcMsgOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tp_action'])) {
    $tpcPid = isset($_POST['tf_id']) ? intval($_POST['tf_id']) : 0;
    if ($tpcPid > 0) { $tpcSel = $tpcPid; }
    if ($_POST['tp_action'] === 'lvup') {
        $tpcR = \player\tianfu_lvup($sid, $dblj, $tpcPid);
    } elseif ($_POST['tp_action'] === 'break') {
        $tpcR = \player\tianfu_break($sid, $dblj, $tpcPid);
    } elseif ($_POST['tp_action'] === 'slot') {
        $tpcPslot = isset($_POST['tf_slot']) ? intval($_POST['tf_slot']) : 0;
        $tpcR = \player\tianfu_slot_set($sid, $dblj, $tpcPid, $tpcPslot);
    } else {
        $tpcR = false;
    }
    if (is_array($tpcR)) {
        $tpcMsg = isset($tpcR['msg']) ? $tpcR['msg'] : '';
        $tpcMsgOk = !empty($tpcR['ok']);
    }
}

// ===== 3) DOC DU LIEU (SAU POST de con so la gia tri moi) =====
$tpcOwn      = \player\tianfu_owned($sid, $dblj);
$tpcAct      = \player\tianfu_activated($sid, $dblj);
$tpcW        = \player\tianfu_player_wallet($sid, $dblj);
$tpcB        = \player\tianfu_bonuses($sid, $dblj);
$tpcSlots    = \player\tianfu_slot_defs();
$tpcSlotOpen = \player\tianfu_slot_open(intval($tpcW['ulv']));
$tpcCap      = \player\tianfu_cap_percent();

// Chon mon mo panel: id hop le > mon dang nam o Phu Vi > mon dau tien da so huu > id 1
if ($tpcSel < 1 || !isset($tpcOwn[$tpcSel])) {
    $tpcSel = 0;
    foreach ($tpcOwn as $tpcR0) { if (intval($tpcR0['slot']) > 0) { $tpcSel = intval($tpcR0['id']); break; } }
    if ($tpcSel === 0) {
        foreach ($tpcOwn as $tpcR0) { if (intval($tpcR0['have']) > 0) { $tpcSel = intval($tpcR0['id']); break; } }
    }
    if ($tpcSel === 0) { $tpcSel = 1; }
}
$tpcDef   = \player\tianfu_def($tpcSel);
$tpcRow   = $tpcOwn[$tpcSel];
$tpcHave  = intval($tpcRow['have']) > 0;
$tpcQ     = intval($tpcRow['quality']);
$tpcLv    = intval($tpcRow['lv']);
$tpcSlot  = intval($tpcRow['slot']);
$tpcCapLv = \player\tianfu_lv_cap($tpcQ);
$tpcColor = $tpcHave ? \player\tianfu_quality_color($tpcQ) : '#9e9e9e';
$tpcGroupName = '';
if ($tpcDef) {
    $tpcGrps = \player\tianfu_groups();
    $tpcGid  = intval($tpcDef['group']);
    if (isset($tpcGrps[$tpcGid])) { $tpcGroupName = $tpcGrps[$tpcGid]['name']; }
}
$tpcMasterQ = (isset($tpcAct[1]) && is_array($tpcAct[1])) ? intval($tpcAct[1]['quality']) : 0;
$tpcFx      = \player\tianfu_fx_class($tpcMasterQ);
$tpcHaveCount = 0;
foreach ($tpcOwn as $tpcR0) { if (intval($tpcR0['have']) > 0) { $tpcHaveCount++; } }

// ===== 4) Panel: hang dieu kien Nang Cap (3 dong) + Dot Pham (4 dong) — chi khi da so huu =====
$tpcLvRows = array();
$tpcBrRows = array();
$tpcBrOk   = false;
$tpcNextQ  = false;
if ($tpcHave) {
    $tpcLvRows = \player\tianfu_lvup_rows($sid, $dblj, $tpcSel);
    if ($tpcQ < 10) {
        $tpcBrRows = \player\tianfu_break_rows($sid, $dblj, $tpcSel);
        $tpcBrOk = true;
        foreach ($tpcBrRows as $tpcBr) { if (empty($tpcBr['ok'])) { $tpcBrOk = false; } }
        $tpcNextQ = \player\tianfu_quality_def($tpcQ + 1);
    }
}
$tpcAtCap   = ($tpcLv >= $tpcCapLv);
$tpcCanLvup = ($tpcHave && !$tpcAtCap);
foreach ($tpcLvRows as $tpcLr) { if (empty($tpcLr['ok'])) { $tpcCanLvup = false; } }

// ===== 5) Cong + dong gop cua mon dang mo (cong chinh; nhom Ngo Tinh co ca cong phu) =====
$tpcGateKey  = $tpcDef ? $tpcDef['gate'] : '';
$tpcGateName = '';
$tpcGateUnit = '%';
$tpcContr    = array();
if ($tpcDef) {
    $tpcGateName = \player\tianfu_gate_name($tpcGateKey);
    $tpcGateDef  = \player\tianfu_gate_def($tpcGateKey);
    if ($tpcGateDef && isset($tpcGateDef['unit'])) { $tpcGateUnit = $tpcGateDef['unit']; }
    $tpcContr = \player\tianfu_contributions($tpcDef);
}
$tpcPerTxt  = isset($tpcContr[$tpcGateKey]) ? ('+' . floatval($tpcContr[$tpcGateKey]) . '%/cấp') : '';
$tpcSubTxt  = isset($tpcContr['exp_monster'])
    ? (' · +' . floatval($tpcContr['exp_monster']) . '%/cấp ' . \player\tianfu_gate_name('exp_monster')) : '';
$tpcGateNow = $tpcGateKey !== '' ? floatval($tpcB[$tpcGateKey]) : 0.0;
$tpcLvCost  = \player\tianfu_lv_cost($tpcLv);

// ===== 6) Dan 114 trong tui (in o panel mon chua so huu) — giong Task 8 Step 2 =====
$tpcPill = \player\tianfu_open_pill();
$tpcPillSum = 0;
$tpcPst = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
$tpcPst->execute(array($sid, intval($tpcPill['djid'])));
$tpcProw = $tpcPst->fetch(PDO::FETCH_ASSOC);
if ($tpcProw) { $tpcPillSum = intval($tpcProw['djsum']); }

// ===== 7) URL — MOI lan render phai encode lai (token tieu mot lan) =====
$tpcPostCmd    = $encode->encode("cmd=tianfu_collection&id=$tpcSel&sid=$sid");
$tpcMainCmd    = $encode->encode("cmd=tianfu&sid=$sid");
$tpcOpenPageCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
$tpcZtCmd      = $encode->encode("cmd=zhuangtai&sid=$sid");
```

> **Vi sao `action` cua form phai mang `id=$tpcSel`:** sau POST, `game.php:114` lai `parse_str` chuoi `cmd=` (khong doc `$_GET['id']` rieng), nen neu `action` chi la `cmd=tianfu_collection&sid=...` thi `$tpcSel` tu `$_POST['tf_id']` (dong `$tpcSel = $tpcPid` o muc 2) van giu duoc panel — dung ca hai duong. Giu `id` trong token de **link** cua `.tpc-cell` va **form** cua nut cung tro ve cung mot mon, tranh truong hop POST mon A xong trang quay ve mon mac dinh.

- [ ] **Step 2: Noi template vao cung file `game/tianfu_collection.php`** (thoat PHP, dung khuon `game/huyetmach.php:162+`; file ket thuc bang doan tabbar, **khong** can dong `?>` cuoi):

```php
?>
<div class="tpc-wrap tp-fx<?= $tpcFx ?>">
<?php if ($tpcMsg !== ''): ?>
    <div class="tp-msg <?= $tpcMsgOk ? 'ok' : 'bad' ?>"><?= htmlspecialchars($tpcMsg) ?></div>
<?php endif; ?>

    <div class="tpc-cover">PHÚ CÁC<i class="tp-emoji">🗄</i><span>18 thiên phú bẩm sinh</span></div>
    <div class="tpc-head">
        <span>Sưu tầm</span>
        <span>Đã có <b><?= $tpcHaveCount ?>/18</b> · tf <b><?= intval($tpcW['tf']) ?></b> · Linh Thạch <b><?= number_format($tpcW['uyxb'], 0, ',', '.') ?></b></span>
    </div>
<?php if ($tpcPick > 0): ?>
    <div class="tp-note">Đang chọn thiên phú cho <b>Phú Vi <?= $tpcPick ?></b> — chạm một ô trong lưới bên dưới.</div>
<?php endif; ?>

<!-- ===== LUOI 18 O (3 cot mobile / 6 cot desktop) ===== -->
    <div class="tpc-grid">
<?php for ($tpcI = 1; $tpcI <= 18; $tpcI++):
    $tpcR2  = $tpcOwn[$tpcI];
    $tpcHas = intval($tpcR2['have']) > 0;
    $tpcRq  = intval($tpcR2['quality']);
    $tpcRs  = intval($tpcR2['slot']);
    $tpcCls = 'tpc-cell' . ($tpcHas ? ' is-have' : ' is-lock')
              . ($tpcRs > 0 ? ' is-active' : '') . ($tpcI === $tpcSel ? ' is-sel' : '');
    $tpcSty = $tpcHas ? ' style="--tp-cell:' . \player\tianfu_quality_color($tpcRq) . '"' : '';
    $tpcCellCmd = $encode->encode("cmd=tianfu_collection&id=$tpcI&sid=$sid");
?>
        <a class="<?= $tpcCls ?>"<?= $tpcSty ?> href="?cmd=<?= $tpcCellCmd ?>">
<?php if ($tpcRs > 0): ?>
            <span class="tpc-slot-tag">PV<?= $tpcRs ?></span>
<?php endif; ?>
            <i class="tp-emoji"><?= $tpcR2['icon'] ?></i>
            <b><?= htmlspecialchars($tpcR2['short']) ?></b>
            <span><?php
                echo $tpcHas
                    ? ('Lv ' . intval($tpcR2['lv']) . ' · ' . \player\tianfu_quality_name($tpcRq))
                    : 'Chưa khai mở';
            ?></span>
        </a>
<?php endfor; ?>
    </div>

<!-- ===== PANEL CHI TIET (mo bang cham, server-render) ===== -->
    <div class="tpc-detail" style="--tp-cell:<?= $tpcColor ?>">
        <div>
            <span class="tpc-detail-name"><i class="tp-emoji"><?= $tpcRow['icon'] ?></i> <?= htmlspecialchars($tpcDef ? $tpcDef['name'] : '?') ?></span>
            <span class="tpc-detail-q"><?php
                echo $tpcHave
                    ? (htmlspecialchars(\player\tianfu_quality_name($tpcQ)) . ' · trần Lv ' . $tpcCapLv)
                    : 'Chưa khai mở';
            ?></span>
        </div>
        <div class="tpc-detail-desc"><?= htmlspecialchars($tpcDef ? $tpcDef['desc'] : '') ?></div>
        <div class="tpc-detail-rows">
            <div><i class="tp-emoji">🧩</i> <?= htmlspecialchars($tpcGroupName) ?> · <b><?= htmlspecialchars($tpcGateName) ?></b> <?= $tpcPerTxt ?><?= $tpcSubTxt ?> <span class="tp-attr-cap">(trần <?= $tpcCap ?>%)</span></div>
<?php if ($tpcHave): ?>
            <div><i class="tp-emoji">📈</i> Lv <b><?= $tpcLv ?></b>/<?= $tpcCapLv ?> · phí kế tiếp <b><?= intval($tpcLvCost["tf"]) ?></b> tf + <b><?= number_format(intval($tpcLvCost["linh_thach"]), 0, ',', '.') ?></b> Linh Thạch</div>
            <div class="<?= $tpcSlot > 0 ? 'ok' : 'no' ?>"><i class="tp-emoji"><?= $tpcSlot > 0 ? '✅' : '⭕' ?></i> <?php
                echo $tpcSlot > 0
                    ? ('Đang phát huy — Phú Vi ' . $tpcSlot)
                    : 'Chưa phát huy — phải đặt vào Phú Vi';
            ?></div>
            <div>Cộng <?= htmlspecialchars($tpcGateName) ?> hiện hưởng: <b><?= $tpcGateNow ?><?= htmlspecialchars($tpcGateUnit) ?></b> (xem trang Thiên Phú)</div>
<?php else: ?>
            <div class="no"><i class="tp-emoji">🔒</i> Chưa khai mở — cần <b><?= htmlspecialchars($tpcPill['name']) ?></b>. Trong túi có <b><?= $tpcPillSum ?></b> viên.</div>
<?php endif; ?>
        </div>
<?php if ($tpcHave && count($tpcLvRows) > 0): ?>
        <div class="tp-reqs"><?php foreach ($tpcLvRows as $tpcLr): ?>
            <div><span class="<?= empty($tpcLr['ok']) ? 'no' : 'ok' ?>"><?= empty($tpcLr['ok']) ? '✗' : '✓' ?></span> <?= htmlspecialchars($tpcLr['text']) ?></div>
<?php endforeach; ?>
        </div>
<?php endif; ?>
<?php if ($tpcHave && $tpcQ < 10 && count($tpcBrRows) > 0): ?>
        <div class="tp-gate" style="--tp-color:<?= $tpcColor ?>"><i class="tp-emoji"><?= \player\tianfu_quality_icon($tpcQ) ?></i> <?= htmlspecialchars(\player\tianfu_quality_name($tpcQ)) ?> → <i class="tp-emoji"><?= $tpcNextQ['icon'] ?></i> <?= htmlspecialchars($tpcNextQ['name']) ?> · trần <?= intval($tpcNextQ['lv_cap']) ?></div>
        <div class="tp-reqs"><?php foreach ($tpcBrRows as $tpcBr): ?>
            <div><span class="<?= empty($tpcBr['ok']) ? 'no' : 'ok' ?>"><?= empty($tpcBr['ok']) ? '✗' : '✓' ?></span> <?= htmlspecialchars($tpcBr['text']) ?></div>
<?php endforeach; ?>
        </div>
<?php elseif ($tpcHave && $tpcQ >= 10): ?>
        <div class="tp-reqs">Đạo Phẩm là cực hạn của thiên phú — không đột phá thêm được.</div>
<?php endif; ?>

<?php if ($tpcHave): ?>
        <div class="tpc-acts">
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="lvup">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <button type="submit" class="tpc-act<?= $tpcCanLvup ? '' : ' is-off' ?>" <?= $tpcCanLvup ? '' : 'disabled' ?>>Nâng Cấp</button>
            </form>
<?php if ($tpcQ < 10): ?>
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="break">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <button type="submit" class="tpc-act<?= $tpcBrOk ? '' : ' is-off' ?>" <?= $tpcBrOk ? '' : 'disabled' ?>>Đột Phá</button>
            </form>
<?php endif; ?>
<?php foreach ($tpcSlots as $tpcSd):
    $tpcSn = intval($tpcSd['i']);
    $tpcSOpen = in_array($tpcSn, $tpcSlotOpen);
    $tpcSHolder = isset($tpcAct[$tpcSn]) ? intval($tpcAct[$tpcSn]['id']) : 0;
    $tpcSConf = ($tpcSOpen && $tpcSHolder !== $tpcSel)
        ? \player\tianfu_slot_conflict($sid, $dblj, $tpcSel, $tpcSn, $tpcAct) : false;
?>
<?php if (!$tpcSOpen): ?>
            <div class="tp-reqs"><span class="no">[Khóa]</span> Phú Vi <?= $tpcSn ?> cần cấp <?= intval($tpcSd['ulv']) ?>.</div>
<?php elseif ($tpcSConf !== false): ?>
            <div class="tp-reqs"><span class="no">[Trùng]</span> Phú Vi <?= $tpcSn ?> trùng môn <?= htmlspecialchars($tpcSConf) ?>.</div>
<?php elseif ($tpcSHolder === $tpcSel): ?>
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="slot">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <input type="hidden" name="tf_slot" value="0">
                <button type="submit" class="tpc-act">Bỏ PV<?= $tpcSn ?></button>
            </form>
<?php else: ?>
            <form method="post" action="?cmd=<?= $tpcPostCmd ?>">
                <input type="hidden" name="tp_action" value="slot">
                <input type="hidden" name="tf_id" value="<?= $tpcSel ?>">
                <input type="hidden" name="tf_slot" value="<?= $tpcSn ?>">
                <button type="submit" class="tpc-act"><?= $tpcPick === $tpcSn ? '▶ ' : '' ?>PV<?= $tpcSn ?><?= $tpcSHolder > 0 ? ' (thay)' : '' ?></button>
            </form>
<?php endif; ?>
<?php endforeach; ?>
        </div>
<?php else: ?>
        <div class="tpc-acts">
            <a class="tpc-act" href="?cmd=<?= $tpcOpenPageCmd ?>">Khai Phú</a>
        </div>
<?php endif; ?>
    </div>

<!-- ===== TABBAR 4 NUT (nut thu 2 dang .on) ===== -->
<nav class="tp-tabbar">
    <a href="?cmd=<?= $tpcMainCmd ?>"><i class="tp-emoji">🌟</i><b>Thiên Phú</b></a>
    <a class="on" href="?cmd=<?= $encode->encode("cmd=tianfu_collection&id=$tpcSel&sid=$sid") ?>"><i class="tp-emoji">🗄</i><b>Phú Các</b></a>
    <a href="?cmd=<?= $tpcOpenPageCmd ?>"><i class="tp-emoji">⚗</i><b>Khai Phú</b></a>
    <a href="?cmd=<?= $tpcZtCmd ?>"><i class="tp-emoji">👤</i><b>Nhân Vật</b></a>
</nav>
</div>
```

- [ ] **Step 3: Dang ky route trong `game.php`** (day la lan dau tien `game/tianfu_collection.php` duoc goi — Task 8 da co chu [CHOT] "moi task dang ky route CUA NO"):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
grep -n "'tianfu'=>'game/tianfu.php'," game.php          # Expected: 349 (Task 8 khong dong map)
```
Dung Edit them dong moi **ngay sau** dong do (`game.php` dung **tab** o dau dong — copy nguyen tab, khong doi thanh space):

```php
			'tianfu'=>'game/tianfu.php',
			'tianfu_collection'=>'game/tianfu_collection.php',
```

```bash
grep -n "tianfu" game.php
# Expected: 2 dong map — 'tianfu' roi 'tianfu_collection' ('tianfu_open' la cua Task 10)
```

- [ ] **Step 4: Bo 4 selector CSS ma Task 7 thieu/sai** (phat hien khi viet markup Task 9; 4 dong nay thuoc block Task 7 da commit, sira tai cho, KHONG mo rong khung chung):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
grep -n "tpc-slot-tag{\|main .tp-act\[disabled\]\|tp-cell.is-active\|tp-fx0 .tp-aura" css/mystyle.css
```

| # | Hien trang (Task 7) | Vi sao sai | Sua thanh |
|---|---|---|---|
| a | `.tpc-slot-tag{...color:#0b1024;background:#ffd77a...}` = (0,1,0) | `.tpc-cell span` la (0,1,1) **de nhat** mau chu nhan PV -> chu trang vàng bi chuyen sang `#94b8dd` (xam tren nen vàng, mat tuong phan) | `.tpc-slot-tag,.tpc-cell .tpc-slot-tag{...}` = (0,2,0) thang (0,1,1) |
| b | `.main .tp-act[disabled],.main .tp-act.is-off{opacity:.45;box-shadow:none}` | nut Phu Cac mang class `.tpc-act` (khong phai `.tp-act`) nen `is-off`/`disabled` khong co tac dung thi giac | them `,.main .tpc-act[disabled],.main .tpc-act.is-off` vao cung danh sach selector. **Chu y dau `.` truoc `main`** — thieu no thi selector thanh `main .tpc-act[...]` (hop le ve cu phap nhung **khong bao gio khop**, vi game nay khong co the `<main>`): loi im lang, nut mo van nhin nhu bam duoc |
| c | `@media(prefers-reduced-motion:reduce){.tp-fx5 .tp-badge,.tp-cell.is-active{animation:none}}` | `.tp-cell` **khong ton tai** trong toan block (ten that `.tpc-cell`) -> reduced-motion khong tat duoc hieu ung o dang dung | `.tpc-cell.is-active` |
| d | `.tp-fx0 .tp-aura,.tp-fx0 .tp-ms-fill:after{animation:none}` | o bac fx0 (chua co chu phu) thi `.tpc-cell.is-active` van chay `tpCellGlow` vi `--tp-dur` mac dinh 11s — vi pham le "fx0 tuyet doi khong dong nao" | `.tp-fx0 .tp-aura,.tp-fx0 .tp-ms-fill:after,.tp-fx0 .tpc-cell.is-active{animation:none}` |

Va **them 3 rule moi** de tieu thu `images/tianfu/phu-cac.jpg` — anh Task 7 sinh ra nhung chua co chu nao dung: `grep -rn "phu-cac" css/*.css game/*.php` truoc Task 9 tra ve 0 ket qua su dung. Chen ngay truoc rule `.tpc-head{`:

```css
.tpc-cover{display:flex;align-items:center;justify-content:center;gap:8px;margin:0 0 10px;padding:14px 10px;border-radius:14px;border:1px solid rgba(255,215,122,.25);background:url(../images/tianfu/phu-cac.jpg) center 35%/cover no-repeat,linear-gradient(160deg,#171029,#0b1024);color:#ffd77a;font-size:17px;font-weight:1000;letter-spacing:1px;text-shadow:0 1px 8px #000}
.tpc-cover i{font-size:20px}
.tpc-cover span{font-size:11.5px;font-weight:700;color:#e8d9b5}
```

Duong dan `url(../images/...)` la khuon duy nhat da duoc chung minh trong file nay (`css/mystyle.css:3` dung `url(../images/bg/bg.png)`); CSS nam trong `/css/` nen bat buoc co `../`. Sau khi sua: `grep -c "tpc-cover" css/mystyle.css` = 3.

- [ ] **Step 5: Lint + quet ky tu la + doi chieu class-vs-CSS** (BAT BUOC chay, phai xanh):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
$P -l game/tianfu_collection.php && $P -l game.php
grep -nP "[\x{4E00}-\x{9FFF}]|[”“‘’]" game/tianfu_collection.php | head   # Expected: khong in ra dong nao
grep -n '??' game/tianfu_collection.php    # Expected: trang (PHP 5.6 khong co null-coalescing)
grep -n '<=>' game/tianfu_collection.php   # Expected: trang
grep -n '$_GET' game/tianfu_collection.php # Expected: trang — chi doc $id/$slot toan cuc + $_POST
```

```bash
for c in $(grep -o 'class="[^"]*"' game/tianfu_collection.php | sed 's/class="//; s/"$//' | tr ' ' '\n' | sed 's/<.*//' | sort -u | grep -v '^$'); do
  grep -q "\.$c" css/mystyle.css || echo "THIU CSS: $c"
done
# Expected: trang. Cac class trang thai (is-have, is-lock, is-active, is-sel, is-off) duoc
#   kiem tra bang chuoi gop nen lieu co trong Step 4; neu vong lap bao "THIU CSS: is-have"
#   thi DO truoc khi sua (no co trong .tpc-cell.is-have), dung them class moi.
grep -c 'class="tpc-cell' game/tianfu_collection.php              # Expected: 1 (vong for sinh 18 o luc chay)
grep -n 'for ($tpcI = 1; $tpcI <= 18' game/tianfu_collection.php  # Expected: 1 dong
grep -c 'tp-tabbar' game/tianfu_collection.php                    # Expected: >= 1
```

- [ ] **Step 6a: Tao `E:/window/Temp/_tpc_url.php` va seed lại nhan vat e2e** (ban sao `_tp_url.php` cua Task 8 — **KHONG doi file cua Task 8**, de hai task chay doc lap):

```php
<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// _tpc_url.php: in URL hoan chinh tu chuoi lenh ben trong
$ROOT = "D:/XXJserver/phpstudy_pro/WWW";
require_once $ROOT . "/class/encode.php";
$enc = new \encode\encode();
echo 'http://127.0.0.1:8091/game.php?cmd=' . $enc->encode($argv[1]) . "\n";
```

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
SID=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2
"$P" E:/window/Temp/_tp_seed.php     # seed Task 8: so huu 1,4,7,13,16,18 + PV1=id1 PV2=id4 PV3=id7, tf=250
$P -l E:/window/Temp/_tpc_url.php
"$P" E:/window/Temp/_tpc_url.php "cmd=tianfu_collection&sid=$SID"
```

- [ ] **Step 6b: 4 lan GET that — moi lan sinh token MOI, KHONG dung lai** (token cu tra ve body 3 byte):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
SID=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2
for Q in "cmd=tianfu_collection&sid=$SID" \
         "cmd=tianfu_collection&id=2&sid=$SID" \
         "cmd=tianfu_collection&id=1&sid=$SID" \
         "cmd=tianfu_collection&slot=3&sid=$SID"; do
  U=$("$P" E:/window/Temp/_tpc_url.php "$Q")
  B=$(curl -s "$U"); L=$(printf '%s' "$B" | wc -c)
  echo "== $Q LEN=$L cells=$(printf '%s' "$B" | grep -o 'class=\"tpc-cell' | wc -l) detail=$(printf '%s' "$B" | grep -o 'class=\"tpc-detail' | wc -l) forms=$(printf '%s' "$B" | grep -o 'name=\"tp_action\"' | wc -l)"
  printf '%s' "$B" | grep -o 'Chưa khai mở — cần <b>Khai Phú Đan</b>' | head -1
  printf '%s' "$B" | grep -o 'Chọn thiên phú cho <b>Phú Vi [0-9]' | head -1
  printf '%s' "$B" | grep -o 'Bỏ PV[0-9]' | head -1
  [ "$L" -gt 500 ] || echo "FAIL LEN — trang trang/BOM, dung tin bat ky so nao o tren"
done
```

| Lan GET | LEN | cells | detail | forms | chuoi phai thay |
|---|---|---|---|---|---|
| mac dinh (co chu phu id1) | > 500 | 18 | 1 | 6 | `Bỏ PV1` (panel tu dong mo = mon o Phu Vi) |
| `id=2` (chua so huu) | > 500 | 18 | 1 | **0** | `Chưa khai mở — cần <b>Khai Phú Đan</b>` + nut `.tpc-act` dan dan Khai Phu |
| `id=1` (chu phu, PV1) | > 500 | 18 | 1 | 6 | `Bỏ PV1` + `Đang phát huy — Phú Vi 1` |
| `slot=3` | > 500 | 18 | 1 | 6 | `Chọn thiên phú cho <b>Phú Vi 3` + nut `▶ PV3` |

`forms` dem so `<input type="hidden" name="tp_action">`. Voi `id=1` khi seed Task 8 (PV1=id1, PV2=id4, PV3=id7, PV4 trong, `ulv`=1000 nen ca 4 o da mo): lvup 1 + break 1 + 4 nut Phu Vi = **6**. `id=2` **bat buoc = 0** — day la test that cua quyet dinh "mon chua so huu khong co nut ghi".

- [ ] **Step 6c: POST smoke 5 truong hop + doi chieu DB** (moi lan 1 token moi; `curl -X POST` gui `application/x-www-form-urlencoded`):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
SID=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2
tpost() { # $1 = chuoi cmd goc (da co id), $2 = action, $3 = tf_slot, $4 = tf_id
  local U=$("$P" E:/window/Temp/_tpc_url.php "$1")
  curl -s -X POST "$U" -d "tp_action=$2" -d "tf_id=$4" -d "tf_slot=$3" \
    | grep -o 'class="tp-msg \(ok\|bad\)">[^<]*' | head -1
  echo "   ^ $1 | $2 | slot=$3 | id=$4"
}
"$P" E:/window/Temp/_tp_seed.php
tpost "cmd=tianfu_collection&id=4&sid=$SID" lvup  0 4    # Expected tp-msg OK (id4 lv12 -> 13; id4 dang PV2 nen van luyen duoc)
tpost "cmd=tianfu_collection&id=4&sid=$SID" break 0 4    # Expected tp-msg BAD (lv 13 < tran pham 5 = 30)
tpost "cmd=tianfu_collection&id=13&sid=$SID" slot  2 13  # Expected tp-msg OK — hoan vi: id4 bi day ve slot 0
tpost "cmd=tianfu_collection&id=16&sid=$SID" slot  3 16  # Expected tp-msg OK — BUOC DAM BAO: bat id16 (cong "Hồi thể lực") vao PV3 de do case sau thanh that bai
tpost "cmd=tianfu_collection&id=18&sid=$SID" slot  4 18  # Expected tp-msg BAD — trung cong "Hồi thể lực" voi id16 (Task 6 chan 2 o cung cong)
```

```bash
"$P" -r 'require_once "D:/XXJserver/phpstudy_pro/WWW/tests/tianfu_test_lib.php"; $db = tf_test_db();
$st = $db->prepare("SELECT tianfu_id, quality, lv, slot FROM player_tianfu WHERE sid = ? ORDER BY tianfu_id");
$st->execute(array("e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2"));
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { echo implode(" ", $r) . "\n"; }
$m = $db->query("SELECT tianfu_id, tianfu_quality, tianfu_lv, tianfu_power FROM game1 WHERE sid = " . $db->quote("e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2"))->fetch(PDO::FETCH_ASSOC);
echo "MIRROR " . implode(" ", $m) . "\n";'
```
Ky vong sau 5 POST (doc **invariant**, khong doc so tuyet doi - vi `--q/--lv` cua `_tp_seed.php` co the khac lan chay):
(1) dong `id 4`: `lv` tang **dung 1** so voi truoc POST, `quality` **khong doi** (break bi tu choi vi lv < tran pham 5), `slot` = 0 (bi id 13 day ra khi id 13 vao PV2).
(2) dong `id 13`: `slot` = 2. (3) dong `id 16`: `slot` = 3 (POST dam bao — no chan cua PV4 dung cong "Hồi thể lực"). (4) dong `id 18`: `slot` **van 0** + msg bad vi trung cong voi `id 16` da nam o PV3; PV4 von trong nen day **khong phai** loi "trung o", ma chinh xac la loi `Task 6 chan 2 o cung cong` — do dung thu tu 16 truoc 18 thi truong hop nay da thanh **tat yeu**, khong con phu thuoc seed.
(5) `MIRROR` van `1 8 20 <power>`: `slot 1` khong doi chu nen chu phu khong dao.
> Ghi chu de chu du an: so `lv` thay doi theo thu tu chay (POST lvup thanh cong 2 lan neu chay lai), nen **seed lại truoc moi dot do** (`_tp_seed.php` o dau Step 6c). Day la dong CHƯA test lai nhieu lan — xem Step 8.

> **Neu `tpost` in ra chuoi rong** (khong co `tp-msg`): dang co 2 nguyen nhan — token da bi dung truoc do, hoac POST bi `game.php:72` chan (`Ngươi nhấn quá nhanh!`). In `curl -i -X POST ...` de xem header `refresh:` + body 3 byte (le cua Global Constraints). KHONG duoc ket luan "nhan khong sao".

- [ ] **Step 7: DO SO THAT + CHUP ANH luoi 18 o (buoc that cua Task 9)**

Ba anh vao `docs/img/thienphu/`: `cac-390.png` (mobile, `id=1` = chu phu dang o PV1), `cac-840.png` (desktop 6 cot), `cac-390-lock.png` (mobile, `id=2` = mon CHUA so huu — panel bat buoc **0 nut ghi**). Cung frame voi Task 8: Chrome CLI, **KHONG dung browser-use MCP** (le `feedback-no-browser-mcp`), harness iframe vi `--window-size` cua Chrome toi thieu ~500px (le `chrome-headless-screenshot`), server `127.0.0.1:8091` (le `project-dev-test-harness`).

Harness MOI `E:/window/Temp/_tpc_harness.php` — **khong sua `_tp_harness.php` cua Task 8** (hai trang do hai tap phan tu khac nhau: `.tp-slot`/`.tp-tier` vs `.tpc-cell`/`.tpc-act`). Hai khac biet ky thuat so voi harness Task 8:

1. Do **CSS variable** (`--tp-cell`) va **dem `input[name=tp_action]`** de chung minh quyet dinh `[CHOT] 2` (mon chua so huu khong co nut ghi) bang so, khong chi bang mat.
2. Anh nen `.tpc-cover` la **CSS background**, KHONG nam trong `querySelectorAll("img")` — phai dung `new Image()` va **doi bat dong bo xong moi ghi `<pre>`**; ghi `<pre>` ngay thi `COVERNAT` luon ra `?` (bay nay da tung lam sai mot lan khi viet plan).

```php
<?php
// _tpc_harness.php <mobile|desktop|lock> — viet _tpcshot.html vao web root (trang Phu Cac)
$mode = isset($argv[1]) ? $argv[1] : 'mobile';
$w    = ($mode === 'desktop') ? 900 : 390;
$id   = ($mode === 'lock') ? 2 : 1;   // id=2: mon chua so huu -> panel phai co 0 nut ghi
$sid  = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/encode.php';
$enc = new \encode\encode();
$cmd = $enc->encode("cmd=tianfu_collection&id=$id&sid=$sid");  // token dung MOT LAN — chay lai moi lan
$js = '
var f=document.getElementById("f");
f.onload=function(){setTimeout(function(){
  var d=f.contentDocument;d.body.style.zoom=1;
  var g=function(s){return d.querySelector(s)};
  var W=function(e){return e?Math.round(e.getBoundingClientRect().width*10)/10:-1};
  var H=function(e){return e?Math.round(e.getBoundingClientRect().height):-1};
  var cells=d.querySelectorAll(".tpc-cell"),i,cw=[],clip=[];
  for(i=0;i<cells.length;i++){cw.push(W(cells[i]);
    var b=cells[i].querySelector("b");
    if(b&&b.scrollWidth>b.clientWidth+1){clip.push(i+":"+b.textContent)}}
  var grid=g(".tpc-grid");
  var cols=grid?getComputedStyle(grid).gridTemplateColumns.split(" ").length:-1;
  var det=g(".tpc-detail"),cov=g(".tpc-cover");
  var bg=cov?getComputedStyle(cov).backgroundImage:"none";
  var mm=bg.match(/url\((.*)\)/);
  var u=mm?mm[1].replace(/["\x27]/g,"").trim():"";
  function emit(nat){
    document.getElementById("out").textContent=[
      "WRAP="+W(g(".tpc-wrap")),"SCROLLW="+d.documentElement.scrollWidth,"BODYH="+H(d.body),
      "CELLS="+cells.length,"COLS="+cols,"CW0="+cw[0],"CW17="+cw[17],"CH="+H(cells[0]),
      "SEL="+d.querySelectorAll(".tpc-cell.is-sel").length,
      "HAVE="+d.querySelectorAll(".tpc-cell.is-have").length,
      "LOCK="+d.querySelectorAll(".tpc-cell.is-lock").length,
      "ACTIVE="+d.querySelectorAll(".tpc-cell.is-active").length,
      "PVTAGS="+d.querySelectorAll(".tpc-slot-tag").length,
      "DETAIL="+(det?H(det):-1),"ACTS="+d.querySelectorAll(".tpc-act").length,
      "FORMS="+d.querySelectorAll("input[name=tp_action]").length,
      "MSG="+d.querySelectorAll(".tp-msg").length,
      "FX="+(g(".tpc-wrap")?g(".tpc-wrap").className:"NA"),
      "CELLVAR0="+getComputedStyle(cells[0]).getPropertyValue("--tp-cell").trim(),
      "CELLVAR1="+getComputedStyle(cells[1]).getPropertyValue("--tp-cell").trim(),
      "COVER="+u,"COVERNAT="+nat,
      "CLIP="+(clip.join(",")||"none")].join("\n");
  }
  var done=0,fin=function(n){if(done)return;done=1;emit(n)};
  if(u){var im=new Image();
    im.onload=function(){fin(im.naturalWidth+"x"+im.naturalHeight)};
    im.onerror=function(){fin("LOI-404")};im.src=u;
    setTimeout(function(){fin("TIMEOUT")},1500);}
  else{fin("khong-co-bg");}
},1800)};
';
$html = '<!doctype html><meta charset="utf-8">'
      . '<style>body{margin:0;background:#222}iframe{width:' . $w . 'px;height:2600px;border:0;display:block}</style>'
      . '<iframe id="f" src="http://127.0.0.1:8091/game.php?cmd=' . $cmd . '"></iframe>'
      . '<pre id="out">PENDING</pre><script>' . $js . '</script>';
file_put_contents('D:/XXJserver/phpstudy_pro/WWW/_tpcshot.html', $html);
echo 'WROTE _tpcshot.html mode=' . $mode . ' iframe_w=' . $w . ' id=' . $id . "\n";
```

> `$js` dat trong chuoi PHP **mot dau nhay** (`'...'`) nen `"\n"` gi nguyen dang literal cho JS doc — neu doi sang chuoi hai dau nhay thi phai viet `"\n"` nhu harness Task 8. Trong `$js` **khong duoc xuat hien dau nhay don**; ky tu `'` trong regex da viet la `\x27` de tranh dung cu phap PHP.

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
CHROME="C:/Program Files/Google/Chrome/Application/chrome.exe"; [ -x "$CHROME" ] || CHROME=$(which chrome)
SHOT="D:/XXJserver/phpstudy_pro/WWW/docs/img/thienphu"; mkdir -p "$SHOT"
"$P" E:/window/Temp/_tp_seed.php --q=8 --lv=20   # reset mau nhu Task 8: so huu 1,4,7,13,16,18; PV1=1 PV2=4 PV3=7; ulv=1000; tf=250

CAP() {  # $1 = mode harness, $2 = ten anh
  "$P" E:/window/Temp/_tpc_harness.php "$1" >/dev/null
  local D="E:/window/Temp/tpcshot_$1_$RANDOM"
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
    --force-device-scale-factor=1 --user-data-dir="$D" \
    --window-size=940,2700 --virtual-time-budget=25000 \
    --screenshot="$SHOT/$2" "http://127.0.0.1:8091/_tpcshot.html"
  "$P" E:/window/Temp/_tpc_harness.php "$1" >/dev/null   # token moi cho lan dump-dom (token cu da bi dung)
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
    --force-device-scale-factor=1 --user-data-dir="${D}b" \
    --window-size=940,2700 --virtual-time-budget=25000 \
    --dump-dom "http://127.0.0.1:8091/_tpcshot.html" > E:/window/Temp/_tpdom.html
  echo "== $2 (mode=$1)"; "$P" E:/window/Temp/_tpread.php
}
CAP mobile  cac-390.png
CAP desktop cac-840.png
CAP lock    cac-390-lock.png
rm -f _tpcshot.html   # harness nam trong web root — xoa ngay, khong commit (Global Constraints)
```

> `_tpread.php` dung lai cua Task 8 (doc `<pre id="out">` tu `E:/window/Temp/_tpdom.html`; regex phai co `[^>]*`). `CAP` goi harness **hai lan** vi `?cmd=` la token mot lan — lan screenshot va lan dump-dom la hai request khac nhau; dung chung mot token thi lan hai tra body 3 byte + `refresh:` va `<pre>` se la `PENDING`.

So phai dat (mau seed `--q=8 --lv=20`; **moi con so deu suy ra duoc tu CSS Task 7 — khong co so nao doan**):

| bien | `cac-390.png` (mobile) | `cac-840.png` (desktop) | `cac-390-lock.png` (`id=2`) | cach suy ra |
|---|---|---|---|---|
| `WRAP` | `390` | `840` | `390` | `.tpc-wrap{max-width:660px;padding:10px}`; `@media(min-width:601px){max-width:840px}` |
| `SCROLLW` | `390` | `900` | `390` | = dung chieu rong iframe; lon hon la tran ngang |
| `CELLS` | `18` | `18` | `18` | `[CHOT] 3`: luoi id 1..18, khong loc theo nhom |
| `COLS` | `3` | `6` | `3` | `repeat(3,1fr)` + media query `repeat(6,1fr)` |
| `CW0` = `CW17` | `118.0` | `130.0` | `118.0` | mobile `(390-20-2*8)/3`; desktop `(840-20-5*8)/6` |
| `CH` | `>= 96` | `>= 96` | `>= 96` | `.tpc-cell{min-height:96px}` |
| `SEL` | `1` | `1` | `1` | `[CHOT] 1`: panel mo theo GET `id`, **khong can hover** |
| `HAVE` / `LOCK` | `6` / `12` | `6` / `12` | `6` / `12` | seed cho 6 mon: 1,4,7,13,16,18 |
| `ACTIVE` / `PVTAGS` | `3` / `3` | `3` / `3` | `3` / `3` | PV1=id1, PV2=id4, PV3=id7; PV4 trong |
| `DETAIL` | `> 0` | `> 0` | `> 0` | panel server-render luon ton tai (le `feedback-hover-needs-tap`) |
| `ACTS` | `6` | `6` | **`1`** | co: lvup + break + 4 nut Phu Vi; lock chi con link Khai Phu |
| `FORMS` | `6` | `6` | **`0`** | `[CHOT] 2`: mon chua so huu **khong co nut ghi nao** |
| `MSG` | `0` | `0` | `0` | GET thuan khong sinh `tp-msg`; chi POST moi co |
| `FX` | chua `tp-fx4` | chua `tp-fx4` | chua `tp-fx4` | `[CHOT] 6`: fx theo pham **chu phu** (q8 → fx4), khong theo o duoc cham |
| `CELLVAR0` | = `Q8COLOR` | = `Q8COLOR` | = `Q8COLOR` | o id1 co `style="--tp-cell:<mau pham 8>"` |
| `CELLVAR1` | `#9e9e9e` | `#9e9e9e` | `#9e9e9e` | o id2 chua so huu → khong co inline style → fallback cua `.tpc-cell` |
| `COVER` | ket thuc `images/tianfu/phu-cac.jpg` | nt | nt | lan dau asset nay co chu dung (Task 9 Step 4) |
| `COVERNAT` | `<so>x<so>` that | nt | nt | `LOI-404` = duong dan sai; `TIMEOUT` = doc khong dang tin, chay lai |
| `CLIP` | `none` | `none` | `none` | nhan `.tpc-cell b` khong bi cat (le `nut-gon-nhan-ngan`) |
| `BODYH` | `> 900` | `> 900` | `> 900` | trang khong sap (CSS chua nap thi BODYH < 400) |

Lay `Q8COLOR` de so sanh (khong nho tay mau pham 8 — no den tu `quality_colors` cua Nhuc Than, Task 3 co `tassert` doi chieu):

```bash
"$P" -r 'require "D:/XXJserver/phpstudy_pro/WWW/class/function/tianfu.php"; echo "Q8COLOR=" . \player\tianfu_quality_color(8) . "\n";'
```

> Neu `-r` bi Git Bash an dau nhay, ghi 3 dong do vao `E:/window/Temp/_tpq8.php` roi chay file (le `preg-delimiter-destroys-lines`: **tuyet doi khong dung `php -r` de sua file**).

**Bat buoc NHIN ANH, khong chi doc so** (le `verify-visually-not-just-metrics`): mo ca 3 PNG va doi chieu **1:1** voi `docs/img/huyetmach/` (cung khung 660/840, cung kieu tabbar 4 nut). Quet computed style **khong bat duoc** lech thi giac: o PV-tag de chu mau xanh nhat tren nen toi, `.tpc-cover` bi anh de len chu, hoac luoi 6 cot desktop keo gian o khong deu.

**Bam that trong browser** (le `feedback-careful-server-changes` + `flag-untested-in-handoff`): `start chrome "http://127.0.0.1:8091/game.php?cmd=<token moi>"` — token sinh bang `"$P" E:/window/Temp/_tpc_url.php "cmd=tianfu_collection&id=1&sid=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2"`, **moi lan quay lai trang phai sinh token moi**. Bam lan luot va ghi ket qua vao ban giao:

| # | thao tac | ket qua phai thay |
|---|---|---|
| 1 | cham o so huu khac (vd id13) | panel doi ten/pham/mo ta ngay trong cung trang (server-render), o do nhan `is-sel` |
| 2 | cham o `is-lock` (vd id2) | panel hien `desc` + "Chưa khai mở — cần Khai Phú Đan", **khong co nut ghi nao** |
| 3 | `Nâng Cấp` khi du tf + Linh Thach | `tp-msg ok`, Lv tren panel tang **dung 1**, tf/Linh Thach o `.tpc-head` giam tuong ung |
| 4 | `Nâng Cấp` khi thieu Linh Thach | `tp-msg bad` (khong phai trang trang, khong phai body 3 byte) |
| 5 | `Đột Phá` khi Lv < tran pham | `tp-msg bad` + pham **khong doi** |
| 6 | `PV4` (o trong) | `tp-msg ok`, o nhan `PV4` + `is-active`; `.tpc-head` doi "Đã có" khong doi |
| 7 | `PV2` khi dang co mon khac | hoan vi: mon cu ve `slot=0` mat nhan `PV2` (dung `tpost` Step 6c da doi chieu DB) |
| 8 | `Bỏ PV1` (chu phu) | `tp-msg ok`; **trang chu Thien Phu mat chu phu** — phai mo lai `cmd=tianfu` de xac nhan |
| 9 | `?slot=3` roi cham mot o | dong `.tp-note` "Đang chọn thiên phú cho Phú Vi 3" + nut `▶ PV3`; GET `slot` **khong ghi DB** |
| 10 | 4 nut tabbar | 🌟 Thien Phu / 🗄 Phu Cac (`.on`) / ⚗ Khai Phu / 👤 Nhan Vat — nhan khong bi cat |

> **No biet truoc cua Task 9 (KHONG phai loi):** thao tac 2 va nut ⚗ tabbar dan den `cmd=tianfu_open`, ma route nay **chi duoc dang ky o Task 10**. Trong Task 9, bam vao se ra dung chu ky that bai cua Global Constraints (body 3 byte + header `refresh:`). Chi can xac nhan **link co `href` khong rong va dung cmd**; **khong** dang ky route som trong Task 9 (file `game/tianfu_open.php` chua ton tai → se 500), va **khong** sua lai Task 9 vi dieu nay.

**CHƯA test o Task 9** (ghi ro vao ban giao, le `flag-untested-in-handoff`): (a) nut Khai Phu — doi Task 10; (b) hieu ung `tp-fx0..fx4` tren luoi o **chi xem bang anh**, chua bam doi chu phu de thay fx doi theo; (c) `phu-cac.jpg` o kich thuoc that tren mobile vat ly (390 CSS ≠ 390 vat ly); (d) Step 6c moi chay duoc **mot** lan duy nhat — chua lap lai de xac nhan tinh on dinh.

- [ ] **Step 8: Commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
git status --porcelain | head -20
#   phai thay: M game.php, M css/mystyle.css, ?? game/tianfu_collection.php, ?? docs/img/thienphu/cac-*.png
#   KHONG duoc thay: _tpcshot.html (da rm o Step 7)
git add game/tianfu_collection.php game.php css/mystyle.css \
        docs/img/thienphu/cac-390.png docs/img/thienphu/cac-840.png docs/img/thienphu/cac-390-lock.png
git diff --cached --name-status | head   # xac nhan DUNG 6 duong dan, khong co tongquan.php
git commit -m "feat(tianfu): trang Phu Cac .tpc-* — luoi 18 o, panel cham, 3 nut hanh dong (Task 9)"
```

> **Van cam `git add -A` / `git add .`** (Global Constraints): goc repo dang co 300+ file `shot_*.png` lang xang tu cac dot chup truoc, va `tongquan.php` da bi sua **tu truoc khi plan nay bat dau** — no thuoc Task 11 (commit rieng), khong duoc di kem commit Task 9. `git add` theo duong dan cu the roi **doc lai `git diff --cached --name-status`** truoc khi commit; thay duong dan la thi `git reset HEAD <file>` ngay.

> Neu Step 7 phat hien lech: sua `game/tianfu_collection.php` hoac khoi CSS `.tpc-*` trong `css/mystyle.css`, chay lai **ca Step 5 (lint/quet) lan Step 7 (do/chup)**, roi moi commit. Khong commit anh cu kem code moi — anh trong `docs/img/thienphu/` phai la anh cua dung ban code duoc commit.


---

### Task 10: KHAI PHU — `game/tianfu_open.php` (uong dan + bang ty le + ket qua theo pham)

**Files:**
- Create: `game/tianfu_open.php` (~140 dong)
- Modify: `game.php:350` — them dong `'tianfu_open'=>'game/tianfu_open.php',` NGAY SAU `'tianfu_collection'=>'game/tianfu_collection.php',` (Task 9 them)
- Modify: `css/mystyle.css` — **1 dong sua** trong block `.tp-*` cua Task 7 (them `.tpo-act` vao rule `[disabled]`); khong them rule moi
- Create: `tests/test_tianfu_pages.php` (test 3 route that qua HTTP, khong can browser)
- San trung NGOAI repo (KHONG commit): `E:/window/Temp/_tpo_seed.php`, `E:/window/Temp/_tpo_harness.php`, `D:/XXJserver/phpstudy_pro/WWW/_tposhot.html` (xoa o Step 8)

**Interfaces:**
- Consumes (Task 1/2/4/5): `\player\tianfu_open($sid,$dblj): array('ok','talent','quality','is_new','msg')`, `tianfu_open_count($sid,$dblj): int`, `tianfu_open_pill(): array('djid'=>114,'name'=>'Khai Phú Đan')`, `tianfu_cfg_key('qualities')`, `tianfu_quality_def/name/color/icon($q)`, `tianfu_fx_class($q): int`, `tianfu_player_wallet($sid,$dblj): array('tf','uyxb','ulv')`, `\player\getplayer`, `\player\getdaoju($djid,$dblj)`, `$encode`/`$sid`/`$dblj` (global tu `game.php`).
- Produces: trang `cmd=tianfu_open` (dich cua redirect `game/use_dj.php` o Task 4 Step 5, va cua nut ⚗ tren tabbar Task 8/9); POST contract **duy nhat** `tp_action=open` (khong co `tf_id`/`tf_slot`); `tests/test_tianfu_pages.php` — ham `tp_http($cmdString, $postFields = null)` tra `array('len','body','headers')`, dung cho ca Task 11/12.

> **[CHOT] Chi POST moi roll — GET khong bao gio goi `tianfu_open()`.** Ly do: `tianfu_open()` **tru dan** (`UPDATE playerdaoju SET djsum = djsum - 1 ... WHERE djsum >= 1`, Task 4 Step 6 buoc 1), nen mot cu F5/preload tren GET la **mot vien dan mat**. Day cung la ly do `game/use_dj.php` (Task 4 Step 5) chi `header("Location: ...")` ma khong tieu dan: viec tieu nam trong nut `Khai Mở`. Kiem tra bat buoc o Step 6: GET 2 lan lien tiep → so dan **khong doi**.

> **[CHOT] KHONG them nut "Mua dan" tren trang nay.** Nguon dan la **Thuong Diem**: Task 4 Step 4b da bo `114` khoi bo loc `game/shangdian.php:343` (chi giu `112/113` an), nen dan 114 hien trong tab Đao Cu va ban voi gia `max(50, djyxb)` = **50.000 LT** (`shangdian.php:309`) — dung muc spec §2.3. Trang Khai Phu chi **hien gia + dan link ve shop** (spec §4.3: "Hien so dan 114 trong tui, gia 50.000 LT"). Them nut mua o day la trung nguon va mo rong pham vi (le `feedback-khong-mo-rong-pham-vi-sua`).
> **Vi sao phai noi ro:** `docs/UI_HUYETMACH.md:504` ghi nhan **be tac khoi dau DA KIEM CHUNG** cua Huyet Mach — 112 Huyet Thach chi roi tu boss stage 2/3/4, ma boss stage 2 chi spawn khi `awaken_stage>=1`, va break 0→1 lai can `total_lv 32` (tuc phai luyen mach bang 112). Thien Phu **khong duoc lap lai**: spec §2.1 da bo khoa `cv30` chinh vi ly do nay ("buc tuong that la phai co dan 50.000 LT"). Step 6 co buoc **kiem chung bang tay** rang dan 114 mua duoc that, khong chi tin vao diff cua Task 4.

> **[CHOT] `.tpo-result` mang CA `--tp-tier` (mau pham vua roll) LAN class `tp-fx<N>` cua pham do** — vi `--tp-glow` chi duoc dinh nghia tren `.tp-fx0..fx5` (Task 7, spec §4.4), nen khong dat fx tren `.tpo-wrap`. He qua: pham 10 (fx5) ruc `--tp-glow:26px`, pham 1 (fx0) **tinh nhung van thay** (vien mau + text-shadow 4px), dung le `feedback-tiered-visual-effects`. Day la cho KHAC voi `[CHOT] 6` cua Task 9 (o do fx theo chu phu vi ca luoi 18 o dung chung mot lop); trang nay chi co **mot** ket qua nen fx theo ket qua.

> **[CHOT] Bang ty le in du 10 pham tu config, KHONG hardcode.** Doc `\player\tianfu_cfg_key('qualities')` (Task 2), ti le hien thi = `round(weight / tong_weight * 100, 2)`. Task 1 Step 3 da `tassert` weight khop `thechat_config.json:quality_weights` — nen trang nay tu dong dung khi Nhuc Than doi ty le. **Do that 2026-10-13:** tong 10 pham = `100.00` chinh xac (40+25+15+10+6+2.5+1+0.4+0.09+0.01), weight 18 thien phu = `72` (6 nhom x (5+4+3)). Van **bat buoc chia tong** thay vi in `weight` tho: hai con so trung nhau hom nay chi la ngau nhien cua config hien tai, va `.tpo-table` phai hien dung ty le ngay ca khi chu du an sua `quality_weights`. (Plan ban dau viet tong = 100.09 — **sai, da sua**; dung tin con so trong comment cu o Task 4 Step 6.)

> **[CHOT] Dong chu thang (spec §4.3) in NGUYEN VAN, co dau that, trong `.tpo-truth`:** "Trúng sẽ không mất — nó tăng 1 phẩm chất cho chính thiên phú ấy." Khong dien giai lai, khong them dieu kien, khong tach 2 cau. Day la cau chong hieu lam "khai phu co the mat thien phu" — loi noi dau tien cua chu du an ve he cu.

- [ ] **Step 1: Viet `game/tianfu_open.php`** — nguyen khoi sau (deliverable, KHONG rut gon). Khuon lay tu `game/huyetmach.php:1-35` (POST truoc, `getplayer` sau, URL encode cuoi) va Task 8/9 (moi bien local deu tien to `$tpo` vi `game.php:89/114` `parse_str($Dcmd)` bien moi cap URL thanh **bien global**).

```php
<?php
// KHAI PHU — uong Khai Phu Dan (114): 1 lan = 1 thien phu MOI, hoac +1 pham cho thien phu trung lai.
// Logic: class/function/tianfu.php (tianfu_open) | Config: class/config/tianfu_config.json
// CSS: css/mystyle.css (block .tpo-*) | Route: cmd=tianfu_open (dang ky o Step 2)
// GET khong bao gio roll — chi POST tp_action=open (F5 tren GET = mat them 1 vien dan).

$tpoImgVer = '2026101201'; // bump khi doi anh trong images/tianfu/

// ===== POST: Khai Mo (khuon game/huyetmach.php:19-32) =====
$tpoMsg   = '';
$tpoMsgOk = false;
$tpoRes   = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tp_action']) && $_POST['tp_action'] === 'open') {
    $tpoRes = \player\tianfu_open($sid, $dblj);
    if (is_array($tpoRes)) {
        $tpoMsg   = isset($tpoRes['msg']) ? $tpoRes['msg'] : '';
        $tpoMsgOk = !empty($tpoRes['ok']);
    }
}

$player = \player\getplayer($sid, $dblj);

// ===== Dan 114: so vien trong tui + gia that (dung cong thuc gia cua shop) =====
$tpoPill = \player\tianfu_open_pill();               // array('djid'=>114,'name'=>'Khai Phú Đan')
$tpoDjid = intval($tpoPill['djid']);
$tpoPst  = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
$tpoPst->execute(array($sid, $tpoDjid));
$tpoHave = intval($tpoPst->fetchColumn());           // khong co dong -> false -> 0
$tpoDj   = \player\getdaoju($tpoDjid, $dblj);
$tpoPrice = $tpoDj ? max(50, intval($tpoDj->djyxb)) : 50000;   // = game/shangdian.php:309
$tpoW     = \player\tianfu_player_wallet($sid, $dblj);
$tpoCount = \player\tianfu_open_count($sid, $dblj);  // so lan da khai mo (player_tianfu_log kind='open')

// ===== Bang ty le 10 pham (doc config, chia tong — xem [CHOT]) =====
$tpoQs  = \player\tianfu_cfg_key('qualities');
$tpoSum = 0.0;
foreach ($tpoQs as $tpoQd) { $tpoSum += floatval($tpoQd['weight']); }

// ===== Ket qua (chi ton tai sau POST thanh cong) =====
$tpoROk    = (is_array($tpoRes) && !empty($tpoRes['ok']) && is_array($tpoRes['talent']));
$tpoRQ     = $tpoROk ? intval($tpoRes['quality']) : 0;
$tpoRQd    = $tpoROk ? \player\tianfu_quality_def($tpoRQ) : false;
$tpoRColor = $tpoROk ? \player\tianfu_quality_color($tpoRQ) : '#9e9e9e';
$tpoRFx    = $tpoROk ? \player\tianfu_fx_class($tpoRQ) : 0;   // tra VE SO (0..5), markup noi "tp-fx"
$tpoRName  = $tpoROk ? $tpoRes['talent']['name'] : '';
$tpoRIcon  = $tpoROk ? $tpoRes['talent']['icon'] : '';
$tpoRQName = $tpoROk ? \player\tianfu_quality_name($tpoRQ) : '';
$tpoRCap   = ($tpoROk && $tpoRQd) ? intval($tpoRQd['lv_cap']) : 0;
$tpoRNew   = ($tpoROk && !empty($tpoRes['is_new']));

// ===== URL — token ?cmd= dung MOT LAN, encode lai moi lan render =====
$tpoPostCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
$tpoMainCmd = $encode->encode("cmd=tianfu&sid=$sid");
$tpoColCmd  = $encode->encode("cmd=tianfu_collection&sid=$sid");
$tpoZtCmd   = $encode->encode("cmd=zhuangtai&sid=$sid");
$tpoShopCmd = $encode->encode("cmd=shangdian&canshu2=gogoumai2&sid=$sid");
?>
```

> `$tpoPrice` lay tu `getdaoju()` (doc `daoju_config.json`) chu khong go cung `50000`: neu chu du an doi `djyxb` cua 114 thi trang nay tu dong dung, va gia hien ra **luon bang** gia ma `game/shangdian.php:309` that su thu (`max(50, djyxb)`). Fallback `50000` chi la luoi an toan khi config thieu khoa — **khong** them canh bao nao khac (le Global Constraints: khong xu ly loi cho truong hop khong the xay ra).

Markup (noi tiep, van trong Step 1):

```php
<div class="tpo-wrap">
<?php if ($tpoMsg !== ''): ?>
    <div class="tp-msg <?= $tpoMsgOk ? 'ok' : 'bad' ?>"><?= htmlspecialchars($tpoMsg) ?></div>
<?php endif; ?>

    <div class="tpo-card">
        <div class="tpo-title"><i class="tp-emoji">⚗</i> KHAI PHÚ</div>
        <div class="tpo-pill">
            <?= htmlspecialchars($tpoPill['name']) ?> trong túi: <b><?= $tpoHave ?></b>
            · đã khai mở <b><?= $tpoCount ?></b> lần
            · Linh Thạch <b><?= number_format($tpoW['uyxb'], 0, ',', '.') ?></b>
        </div>

<?php if ($tpoROk): ?>
        <div class="tpo-result tp-fx<?= $tpoRFx ?>" style="--tp-tier:<?= $tpoRColor ?>">
            <div class="tpo-result-name"><i class="tp-emoji"><?= $tpoRIcon ?></i> <?= htmlspecialchars($tpoRName) ?></div>
            <div class="tpo-result-q"><?= htmlspecialchars($tpoRQName) ?> · trần Lv <?= $tpoRCap ?></div>
            <div class="tpo-result-new"><?= $tpoRNew ? 'Thiên phú MỚI — đã vào Phú Các' : 'Đã có sẵn — tăng 1 phẩm chất' ?></div>
        </div>
<?php endif; ?>

        <div class="tpo-truth">Trúng sẽ không mất — nó tăng 1 phẩm chất cho chính thiên phú ấy.</div>

        <form method="post" action="?cmd=<?= $tpoPostCmd ?>">
            <input type="hidden" name="tp_action" value="open">
            <button type="submit" class="tpo-act<?= $tpoHave > 0 ? '' : ' is-off' ?>" <?= $tpoHave > 0 ? '' : 'disabled' ?>>Khai Mở</button>
        </form>

        <table class="tpo-table">
            <tr><th>Phẩm</th><th>Danh xưng</th><th>Trần Lv</th><th>Tỷ lệ</th></tr>
<?php foreach ($tpoQs as $tpoQi => $tpoQd):
    $tpoPct = $tpoSum > 0 ? round(floatval($tpoQd['weight']) / $tpoSum * 100, 2) : 0.0;
?>
            <tr>
                <td class="q" style="color:<?= htmlspecialchars($tpoQd['color']) ?>"><b><i class="tp-emoji"><?= $tpoQd['icon'] ?></i> <?= intval($tpoQi) ?></b></td>
                <td><?= htmlspecialchars($tpoQd['name']) ?></td>
                <td><?= intval($tpoQd['lv_cap']) ?></td>
                <td><?= number_format($tpoPct, 2, ',', '.') ?>%</td>
            </tr>
<?php endforeach; ?>
        </table>

        <div class="tpo-foot">
            <?= $tpoHave > 0
                ? ('Giá ' . number_format($tpoPrice, 0, ',', '.') . ' Linh Thạch / viên · mua ở Thương Điếm, tab Đạo Cụ.')
                : ('<b>Hết đan</b> — mua ở Thương Điếm, tab Đạo Cụ, giá ' . number_format($tpoPrice, 0, ',', '.') . ' Linh Thạch.') ?><br>
            <a class="tp-act tp-act-ghost" href="?cmd=<?= $tpoShopCmd ?>">Mua đan</a>
        </div>
    </div>

<!-- ===== TABBAR 4 NUT (nut thu 3 dang .on) ===== -->
<nav class="tp-tabbar">
    <a href="?cmd=<?= $tpoMainCmd ?>"><i class="tp-emoji">🌟</i><b>Thiên Phú</b></a>
    <a href="?cmd=<?= $tpoColCmd ?>"><i class="tp-emoji">🗄</i><b>Phú Các</b></a>
    <a class="on" href="?cmd=<?= $encode->encode("cmd=tianfu_open&sid=$sid") ?>"><i class="tp-emoji">⚗</i><b>Khai Phú</b></a>
    <a href="?cmd=<?= $tpoZtCmd ?>"><i class="tp-emoji">👤</i><b>Nhân Vật</b></a>
</nav>
</div>
```

> **Nhan nut `Khai Mở` KHONG doi theo trang thai** (khong noi thanh "Khai Mở — het dan"). Nhan 2 dong trong nut ~40px lam chu **lech xuong trong nut** — da tung bi chu du an bat o v13 Nhuc Than (le `feedback-lech-la-chu-trong-nut`, `nut-gon-nhan-ngan`). Trang thai "het dan" nam o `.tpo-foot` va o `disabled`/`is-off`.
> **Chi CO MOT link ghost o `.tpo-foot`** ("Mua dan"). Khong them link "Phu Cac" vi tabbar da co, va `.tpo-foot` khong phai flex — 2 nut padding 11x18 nam canh nhau trong footer 11.5px se de ma khong them CSS moi (le `feedback-khong-mo-rong-pham-vi-sua`).

- [ ] **Step 2: Dang ky route trong `game.php`** (dong thoi giai no cho nut ⚗ cua Task 8 va nut "Khai Phú" cua Task 9 — xem "no biet truoc" o Task 9 Step 7):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
grep -n "'tianfu_collection'=>'game/tianfu_collection.php'," game.php
# Expected: 350 (Task 9 them ngay sau 'tianfu'=>'game/tianfu.php', o 349)
```

Dung Edit them dong moi **ngay sau** dong do (`game.php` dung **tab** o dau dong — copy nguyen tab tu dong ke tren, khong doi thanh space):

```php
			'tianfu_open'=>'game/tianfu_open.php',
```

```bash
grep -n "'tianfu_open'" game.php    # Expected: DUNG 1 dong
grep -c "=>" game.php               # khong doi ngoai +1 so voi truoc Step 2
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -l game.php
```

> `game.php` chi co **mot** bang route (da grep xac nhan 2026-10-12: `:345-372`); khong co bang thu hai hay switch nao khac. Route tro den file **chua ton tai** thi `include` se Fatal — do do Task 8/9 khong dang ky som, va Task 10 dang ky **cung luc** tao file.

- [ ] **Step 3: Sua 1 dong CSS trong block `.tp-*` cua Task 7** — nut `Khai Mở` mang class `.tpo-act`, ma rule `[disabled]`/`.is-off` hien chi noi `.tp-act` (Task 7) va `.tpc-act` (Task 9):

| | CU (sau Task 9) | MOI |
|---|---|---|
| 1 dong duy nhat | `.main .tp-act[disabled],.main .tp-act.is-off,.main .tpc-act[disabled],.main .tpc-act.is-off{opacity:.45;box-shadow:none}` | `.main .tp-act[disabled],.main .tp-act.is-off,.main .tpc-act[disabled],.main .tpc-act.is-off,.main .tpo-act[disabled],.main .tpo-act.is-off{opacity:.45;box-shadow:none}` |

Khong them rule `.tpo-*` moi nao: Task 7 da dinh nghia du 12 class (`.tpo-wrap .tpo-card .tpo-title .tpo-pill .tpo-truth .tpo-result .tpo-result-name .tpo-result-q .tpo-result-new .tpo-table .tpo-act .tpo-foot`) va Step 4 co vong doi chieu 2 chieu. **Khong can** reset `.main button` cho nut `Khai Mở`: `.main .tpo-act` = (0,2,0) thang `.main button` = (0,1,1) — day chinh la ly do Task 7 viet `.main .tpo-act` thay vi `.tpo-act`.

- [ ] **Step 4: Lint + quet ky tu la + doi chieu class↔CSS**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l game/tianfu_open.php && "$P" -l game.php && "$P" -l tests/test_tianfu_pages.php
grep -nP '[\x{4E00}-\x{9FFF}]|[\x{2018}\x{2019}\x{201C}\x{201D}]|array_key_last|\?\?|<=>' game/tianfu_open.php tests/test_tianfu_pages.php
# Expected: 0 dong (emoji U+2697/U+1F31F/U+1F5C4/U+1F464 NAM NGOAI dai CJK 4E00-9FFF nen khong bi bat)
grep -n '\$_GET' game/tianfu_open.php
# Expected: 0 dong — trang nay khong doc $_GET; input chi den tu bien global do parse_str sinh ra va $_POST
for c in tpo-wrap tpo-card tpo-title tpo-pill tpo-truth tpo-result tpo-result-name tpo-result-q tpo-result-new tpo-table tpo-act tpo-foot tp-msg tp-emoji tp-tabbar tp-act tp-act-ghost; do grep -q "\.$c" css/mystyle.css || echo "THIU CSS: $c"; done
# Expected: 0 dong
grep -o '\.tpo-[a-z-]*' css/mystyle.css | sort -u | while read c; do n="${c#.}"; grep -q "$n" game/tianfu_open.php || echo "CSS KHONG AI DUNG: $c"; done
# Expected: 0 dong — moi rule .tpo-* deu phai co chu dung (bai hoc phu-cac.jpg: Task 2 sinh anh, den Task 9 moi co nguoi dung)
```

- [ ] **Step 5: Viet `tests/test_tianfu_pages.php`** — test 3 route that qua HTTP. **Khuon lay tu `tests/test_linhcan_pages.php`** (tien le da co, da doc 2026-10-13): `shell_exec('curl -s ...')`, encode lai token moi lan goi, helper `tok()` chan body < 500 byte, lay credential tu `pdo.php` thay vi ghi ra. **Doc file do truoc khi viet** — neu no da doi (cong, ten bien) thi theo ban trong repo, khong theo plan.

```php
<?php
/**
 * test_tianfu_pages.php — 3 route THAT cua he Thien Phu: tianfu / tianfu_collection / tianfu_open.
 * Can dev server dang chay (mac dinh http://127.0.0.1:8091, override bang bien moi truong TP_BASE)
 * va nhan vat e2e trong DB (chay tests/e2e_fixture.php truoc).
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_pages.php
 * Khong ghi credential: lay chuoi noi ket tu pdo.php (dung nguoi ma game.php dung).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else {
        $fails++;
        echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
// Body < 500 byte = bi da ve login (endtime het han) hoac blob cmd mot lan da bi dung.
function tok($name, $body) {
    if (strlen($body) < 500) { echo "!! $name body=" . strlen($body) . " byte — server chet hoac blob cmd da dung\n"; }
    tassert($name, strlen($body) > 500, true);
}

$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$ROOT = dirname(__DIR__);
$dsn = "mysql:host=localhost;dbname=game;charset=utf8";
$sqlname = null; $sqlpass = null;
include $ROOT . '/pdo.php';   // khai bao $sqlname/$sqlpass o pham vi toan cau
$db = new PDO($dsn, $sqlname, $sqlpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$db->exec("SET NAMES utf8mb4");

$enc = new \encode\encode();
$base = getenv('TP_BASE');
if (!is_string($base) || $base === '') { $base = 'http://127.0.0.1:8091'; }

function getpage($plain) {
    global $enc, $base;
    // Blob cmd DUNG MOT LAN (game.php chong replay) -> encode lai moi lan goi.
    return (string) shell_exec('curl -s ' . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain)));
}
function postpage($plain, $fields) {
    global $enc, $base;
    $args = '';
    foreach ($fields as $k => $v) { $args .= ' -d ' . escapeshellarg($k . '=' . $v); }
    return (string) shell_exec('curl -s -X POST' . $args . ' '
        . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain)));
}
function pillCount($db, $sid) {
    $st = $db->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = 114");
    $st->execute(array($sid));
    return intval($st->fetchColumn());
}
function ownedCount($db, $sid) {
    $st = $db->prepare("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?");
    $st->execute(array($sid));
    return intval($st->fetchColumn());
}

// ===== SETUP: nhan vat e2e phai con han, Thien Phu trang, khong co dan 114 =====
$db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
$db->exec("UPDATE game1 SET tf = 500, uyxb = 5000000, tianfu_id = 0, tianfu_quality = 0,
           tianfu_lv = 0, tianfu_power = 0 WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_tianfu WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_tianfu_log WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid = 114");
\player\player_cache_clear($E2ESID);
$E2EUID = intval($db->query("SELECT uid FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
tassert('fixture e2e ton tai (uid doc duoc)', $E2EUID > 0, true);

// ===== P1: trang chu Thien Phu (Task 8) =====
$g1 = getpage("cmd=tianfu&sid=$E2ESID");
tok('P1 route tianfu live', $g1);
tassert('P1 class tp-wrap + fx', strpos($g1, 'class="tp-wrap tp-fx') !== false, true);
tassert('P1 title THIEN PHU', strpos($g1, '<span class="tp-title">THIÊN PHÚ</span>') !== false, true);
tassert('P1 chua co chu phu (dang trang)', strpos($g1, 'Chưa có Chủ Phú') !== false, true);
tassert('P1 tabbar 4 nut', substr_count($g1, 'tp-tabbar') === 1 && substr_count($g1, '<nav class="tp-tabbar">') === 1, true);

// ===== P2: Phu Cac (Task 9) =====
$g2 = getpage("cmd=tianfu_collection&sid=$E2ESID");
tok('P2 route tianfu_collection live', $g2);
tassert('P2 luoi dung 18 o', substr_count($g2, 'class="tpc-cell') , 18);
tassert('P2 panel chi tiet co mat (khong can hover)', strpos($g2, 'tpc-detail') !== false, true);
tassert('P2 o id1 dang is-lock', strpos($g2, 'tpc-cell is-lock is-sel') !== false, true);
tassert('P2 GET thuan khong co nut ghi nao', strpos($g2, 'name="tp_action"') === false, true);
tassert('P2 co loi dan ve Khai Phu', strpos($g2, 'Khai Phú') !== false, true);

// ===== P3: Khai Phu (Task 10) — GET thuan, chua co dan =====
$g3 = getpage("cmd=tianfu_open&sid=$E2ESID");
tok('P3 route tianfu_open live', $g3);
tassert('P3 class tpo-wrap', strpos($g3, 'class="tpo-wrap"') !== false, true);
tassert('P3 dong chu thang nguyen van', strpos($g3, 'Trúng sẽ không mất — nó tăng 1 phẩm chất cho chính thiên phú ấy.') !== false, true);
tassert('P3 bang ty le = 10 pham + 1 dong tieu de', substr_count($g3, '</tr>'), 11);
tassert('P3 nut Khai Mo bi khoa khi het dan', strpos($g3, 'class="tpo-act is-off"') !== false && strpos($g3, 'disabled') !== false, true);
tassert('P3 GET khong tra ket qua roll', strpos($g3, 'tpo-result') === false, true);
tassert('P3 hien gia that cua dan (50.000)', strpos($g3, '50.000') !== false, true);
tassert('P3 tabbar nut 3 dang on', strpos($g3, '<a class="on" href="?cmd=') !== false, true);

// ===== P4: GET 2 lan lien tiep KHONG tieu dan (chung minh [CHOT] "chi POST moi roll") =====
$db->exec("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES ('$E2ESID', $E2EUID, 114, 3)
           ON DUPLICATE KEY UPDATE djsum = 3");
\player\player_cache_clear($E2ESID);
$g4a = getpage("cmd=tianfu_open&sid=$E2ESID");
$g4b = getpage("cmd=tianfu_open&sid=$E2ESID");
tok('P4 GET lan 1', $g4a); tok('P4 GET lan 2', $g4b);
tassert('P4 2 lan GET khong lam mat dan', pillCount($db, $E2ESID), 3);
tassert('P4 GET khong sinh ket qua', strpos($g4a, 'tpo-result') === false && strpos($g4b, 'tpo-result') === false, true);
tassert('P4 nut mo khoa khi co dan', strpos($g4b, 'class="tpo-act is-off"') === false, true);
tassert('P4 so lan khai mo van 0', strpos($g4b, 'đã khai mở <b>0</b>') !== false, true);

// ===== P5: POST tp_action=open — roll that, tru dung 1 dan =====
$p5 = postpage("cmd=tianfu_open&sid=$E2ESID", array('tp_action' => 'open'));
tok('P5 POST open', $p5);
tassert('P5 co panel ket qua', strpos($p5, 'tpo-result-name') !== false, true);
tassert('P5 ghi nhan thien phu moi', strpos($p5, 'Thiên phú MỚI') !== false, true);
tassert('P5 tru dung 1 dan (3 -> 2)', pillCount($db, $E2ESID), 2);
tassert('P5 tao dung 1 dong player_tianfu', ownedCount($db, $E2ESID), 1);
$slotSt = $db->prepare("SELECT slot, quality FROM player_tianfu WHERE sid = ?"); $slotSt->execute(array($E2ESID));
$slotRow = $slotSt->fetch(PDO::FETCH_ASSOC);
tassert('P5 khong tu dat lam chu phu (slot = 0)', intval($slotRow['slot']), 0);
tassert('P5 pham trong khoang 1..10', intval($slotRow['quality']) >= 1 && intval($slotRow['quality']) <= 10, true);
tassert('P5 log kind=open duoc ghi', intval($db->query("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = " . $db->quote($E2ESID) . " AND kind = 'open'")->fetchColumn()), 1);
tassert('P5 dem so lan khai mo tang len 1', strpos($p5, 'đã khai mở <b>1</b>') !== false, true);

// ===== P6: POST khi het dan — msg bad, khong ghi gi =====
$db->exec("UPDATE playerdaoju SET djsum = 0 WHERE sid = '$E2ESID' AND djid = 114");
\player\player_cache_clear($E2ESID);
$p6 = postpage("cmd=tianfu_open&sid=$E2ESID", array('tp_action' => 'open'));
tok('P6 POST khi het dan', $p6);
tassert('P6 msg bad', strpos($p6, 'class="tp-msg bad"') !== false, true);
tassert('P6 noi dung msg het dan', strpos($p6, 'Không còn Khai Phú Đan trong túi.') !== false, true);
tassert('P6 khong co panel ket qua', strpos($p6, 'tpo-result-name') === false, true);
tassert('P6 khong them dong nao', ownedCount($db, $E2ESID), 1);

// ===== P7: khong co loi PHP lo ra ngoai tren bat ky page nao =====
$gAll = $g1 . $g2 . $g3 . $g4a . $g4b . $p5 . $p6;
tassert('KHONG co loi PHP tren moi page', stripos($gAll, 'Fatal error') === false
    && stripos($gAll, 'Parse error') === false && stripos($gAll, 'Undefined ') === false
    && stripos($gAll, 'Warning</b>:') === false, true);

// ===== CLEANUP — chi xoa phan Thien Phu =====
// KHONG goi tf_cleanup($db, $E2ESID): ham do xoa HET playerdaoju cua sid, tuc la cuon luon
// dan Linh Can 32-37 ma tests/test_linhcan_pages.php dang dung (hai test chung 1 nhan vat e2e).
$db->exec("DELETE FROM player_tianfu WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_tianfu_log WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid = 114");
$db->exec("UPDATE game1 SET tianfu_id = 0, tianfu_quality = 0, tianfu_lv = 0, tianfu_power = 0
           WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
```

> **`tassert('P2 luoi dung 18 o', substr_count($g2, 'class="tpc-cell'), 18);`** — chuoi tim co y **khong** dong ngoac nhay: neu viet `'class="tpc-cell"'` thi se khop **0** lan, vi Task 9 in class day du la `class="tpc-cell is-have is-sel"`. Day la loi de mac nhat khi doi chieu markup tu test (tien le: `test_linhcan_pages.php` tim `lcq-wrap` khong co ngoac nhay).
> **`P3 hien gia that cua dan (50.000)`** — moi `number_format()` trong he Thien Phu **bat buoc viet day du 4 tham so** `number_format($x, 0, ',', '.')` theo kieu Viet Nam (tien le `game/djinfo.php:79`, `game/shangdian.php`): goi 1 tham so thi PHP tra `50,000` (dau phay), lech voi toan bo game va lam test P3 FAIL. Da quet va sua **15 cho** trong plan nay ngay 2026-10-13 (Task 5/8/9/10); khi viet code that, chay `grep -n "number_format(" game/tianfu*.php class/function/tianfu.php | grep -v "0, ',', '\\.'"` va mong **0 dong**.
> **`P4 so lan khai mo van 0`** phu thuoc `tianfu_open_count()` doc `player_tianfu_log WHERE kind='open'` (Task 4). Neu P5 chay truoc P4 thi so nay khac — **giu dung thu tu P1..P7** trong file.

- [ ] **Step 6: Chay test + xac nhan dan 114 MUA DUOC that**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
SID=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2

# 6a: server + fixture
curl -s -o /dev/null -w "server -> %{http_code}\n" http://127.0.0.1:8091/index.php
# Expected: 200. Neu 000/000-thi bat lai: "$P" -S 127.0.0.1:8091 -t "D:/XXJserver/phpstudy_pro/WWW" (Global Constraints: 8090 bi chiem)
"$P" tests/e2e_fixture.php | tail -3

# 6b: test 3 route that
"$P" tests/test_tianfu_pages.php
# Expected: tat ca PASS + dong cuoi "ALL PASS", exit code 0

# 6c: 6 test cu van xanh (Task 10 khong duoc pha Task 1-6)
for t in test_tianfu_config test_tianfu_schema test_tianfu_bonuses test_tianfu_open test_tianfu_lvup test_tianfu_slot; do
  printf -- "-- %s: " "$t"; "$P" tests/$t.php | tail -1
done
# Expected: 6 dong "ALL PASS"
```

- [ ] **Step 6d: CHUNG MINH khong co be tac khoi dau** (`docs/UI_HUYETMACH.md:504` — 112 Huyet Thach chi roi tu boss ma boss can 112; Thien Phu khong duoc lap lai):

```bash
U=$("$P" E:/window/Temp/_tpc_url.php "cmd=shangdian&canshu2=gogoumai2&kw=Khai&sid=$SID")
curl -s "$U" | grep -o "Khai Phú Đan" | head -2          # Expected: >= 1 lan
curl -s "$U" | grep -o "djid=114" | head -3               # Expected: >= 1 (link xem chi tiet + link thanh toan)
U2=$("$P" E:/window/Temp/_tpc_url.php "cmd=shangdian&canshu2=gogoumai2&kw=Khai&pay=1&djid=114&sid=$SID")
curl -s "$U2" | grep -o "50\.000" | head -2               # Expected: >= 1 — hop thoai thanh toan hien dung gia
```

> Dung `kw=Khai` (khong dau) vi `tcg_shop_filter_kw` loc bang `mb_stripos` (`game/shangdian.php:68`) va shop **phan trang 10 mon/trang** (`:17`) — 114 nam o trang cuoi nen `curl` trang 1 se khong thay no. Neu muon kiem tra khong dung bo loc: `&p=8` (78 mon - 2 mon bi an 112/113 = 76 mon -> trang 8). **Neu buoc nay FAIL** (dan khong hien trong shop) thi phai quay lai Task 4 Step 4b — **khong** them nut mua vao trang Khai Phu de "chua chay", vi do la mo rong pham vi va tao nguon dan thu hai ngoai spec.

> Sau Step 6d, **nhan vat e2e co the da bi tru 50.000 LT** neu bam that trong hop thoai thanh toan. Chay lai `"$P" tests/e2e_fixture.php` hoac `"$P" E:/window/Temp/_tpo_seed.php` truoc khi chup anh o Step 7.

- [ ] **Step 7: DO SO THAT + CHUP ANH man Khai Phu**

Bon anh: `docs/img/thienphu/open-390.png` (mobile, co dan, nut mo khoa=false), `open-840.png` (desktop), `open-result.png` (mobile, **sau khi submit that** — panel `.tpo-result` theo pham vua roll), `open-nodan.png` (mobile, `--dan=0` — nut mo **khoa**, `.tpo-foot` doi sang "Hết đan"; anh nay la bang chung duy nhat cua dong CSS sua o Step 3). San trung ngoai repo: `E:/window/Temp/_tpo_seed.php`, `E:/window/Temp/_tpo_harness.php`, `WWW/_tposhot.html` (xoa o Step 8).

`E:/window/Temp/_tpo_seed.php` — cap dan 114 + tien, khong dung de sua file nao:

```php
<?php
// _tpo_seed.php [--dan=5] — cap Khai Phu Dan + Linh Thach cho tai khoan e2e (chup anh Task 10).
// KHONG goi _tp_seed.php cua Task 8: man Khai Phu khong can chu phu/Phu Vi, chi can dan + tien.
$n = 5;
foreach (array_slice($argv, 1) as $a) { if (strpos($a, '--dan=') === 0) { $n = intval(substr($a, 6)); } }
$sid = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
              array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$uid = intval($db->query('SELECT uid FROM game1 WHERE sid = ' . $db->quote($sid))->fetchColumn());
if ($uid <= 0) { fwrite(STDERR, "ABORT: nhan vat e2e chua ton tai — chay tests/e2e_fixture.php truoc\n"); exit(1); }
// Lech gio PHP +8 / MySQL +7: seed endtime bang DATE_ADD cua MySQL (Global Constraints).
$db->prepare('UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 6 HOUR), uyxb = 5000000 WHERE sid = ?')
   ->execute(array($sid));
$db->prepare('INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES (?, ?, 114, ?)
              ON DUPLICATE KEY UPDATE djsum = ?')->execute(array($sid, $uid, $n, $n));
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/player.php';
\player\player_cache_clear($sid);
echo "SEEDED dan114=$n uid=$uid uyxb=5000000\n";
```

> `playerdaoju` co PK `(uid, djid)` — **bat buoc truyen `uid`** (Global Constraints). `--dan=0` dung de chup trang thai het dan (nut mo phai mo + `.tpo-foot` doi sang "Hết đan").

`E:/window/Temp/_tpo_harness.php` — harness MOI, **khong sua `_tp_harness.php` (Task 8) lan `_tpc_harness.php` (Task 9)**. Ba khac biet ky thuat so voi hai harness truoc:

1. **Mode `result` phai submit that ngay trong iframe** (`fo.submit()`), roi **do o lan `onload` thu hai**. Cờ `posted` dat o **cua so NGOAI** (`_tposhot.html`) vi no song qua lan dieu huong cua iframe; dat trong `f.contentDocument` thi no bi xoa ngay khi trang moi nap → vong lap submit vo han (va **dot het dan** cua nhan vat e2e).
2. **Tim form bang `input[name=tp_action]`, khong dung `forms[0]`** — `game.php` render them khoi ngoai (chat/daoju), thu tu `<form>` khong phai la hop dong va se dao khi them tinh nang.
3. **Do `opacity` cua `.tpo-act`** (0.45 khi khoa). Day la con so **duy nhat** chung minh dong CSS sua o Step 3 that su khop: `.tpo-act` la `<button>` nen rule `.main a.tpo-act{...}` cua Task 7 (`mystyle.css` block `.tp-*`, dong reset anchor) **khong ap dung** — chi co `.main .tpo-act[disabled]` vua them moi ap dung. Quet computed style khong co muc nay thi Step 3 sai van "PASS" het.

```php
<?php
// _tpo_harness.php <mobile|desktop|nodan|result> — viet _tposhot.html vao web root (trang Khai Phu)
$mode = isset($argv[1]) ? $argv[1] : 'mobile';
$w    = ($mode === 'desktop') ? 900 : 390;
$sid  = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
require_once 'D:/XXJserver/phpstudy_pro/WWW/class/encode.php';
$enc = new \encode\encode();
$cmd = $enc->encode("cmd=tianfu_open&sid=$sid");   // token dung MOT LAN — chay lai moi lan
$js = '
var f=document.getElementById("f"),MODE="__MODE__";
var posted=0,done=0;
f.onload=function(){setTimeout(function(){
  if(done)return;
  var d=f.contentDocument,i;
  var ins=d.getElementsByTagName("form"),fo=null;
  for(i=0;i<ins.length;i++){var h=ins[i].querySelector("input[name=tp_action]");
    if(h&&h.value==="open"){fo=ins[i];break}}
  if(MODE==="result"&&!posted){
    if(!fo){done=1;document.getElementById("out").textContent="LOI: khong thay form tp_action=open";return}
    posted=1;fo.submit();return;
  }
  d.body.style.zoom=1;
  var g=function(s){return d.querySelector(s)};
  var W=function(e){return e?Math.round(e.getBoundingClientRect().width*10)/10:-1};
  var H=function(e){return e?Math.round(e.getBoundingClientRect().height):-1};
  var T=function(e){return e?e.textContent.replace(/\s+/g," ").trim():"NA"};
  var res=g(".tpo-result"),act=g(".tpo-act"),gh=g(".tpo-foot a.tp-act-ghost");
  var rows=d.querySelectorAll(".tpo-table tr"),pct=[];
  for(i=1;i<rows.length;i++){pct.push(T(rows[i].cells[3]))}
  var clip=[],chk=function(e,tag){if(e&&e.scrollWidth>e.clientWidth+1){clip.push(tag+":"+T(e))}};
  chk(act,"ACT");chk(gh,"GHOST");
  var tabs=d.querySelectorAll(".tp-tabbar a");
  for(i=0;i<tabs.length;i++){chk(tabs[i],"TAB"+i)}
  done=1;
  document.getElementById("out").textContent=[
    "WRAP="+W(g(".tpo-wrap")),"SCROLLW="+d.documentElement.scrollWidth,"BODYH="+H(d.body),
    "CARD="+W(g(".tpo-card")),"TITLE="+T(g(".tpo-title")),"PILL="+T(g(".tpo-pill")),
    "TRUTH="+T(g(".tpo-truth")),
    "ROWS="+rows.length,"PCT1="+pct[0],"PCT9="+pct[8],"PCT10="+pct[9],
    "ACTW="+W(act),"ACTH="+H(act),"ACTDIS="+(act&&act.disabled?1:0),
    "ACTOFF="+d.querySelectorAll(".tpo-act.is-off").length,
    "OPAC="+(act?getComputedStyle(act).opacity:"NA"),
    "FOOT="+T(g(".tpo-foot")),"GHLEN="+(gh?(gh.getAttribute("href")||"").length:-1),
    "RES="+(res?1:0),"RCLS="+(res?res.className:"NA"),
    "TIER="+(res?getComputedStyle(res).getPropertyValue("--tp-tier").trim():"NA"),
    "GLOW="+(res?getComputedStyle(res).getPropertyValue("--tp-glow").trim():"NA"),
    "RNAME="+T(g(".tpo-result-name")),"RQ="+T(g(".tpo-result-q")),"RNEW="+T(g(".tpo-result-new")),
    "MSG="+d.querySelectorAll(".tp-msg").length,"MSGTXT="+T(g(".tp-msg")),
    "TABS="+tabs.length,"TABON="+d.querySelectorAll(".tp-tabbar a.on").length,
    "IMGS="+d.querySelectorAll(".tpo-wrap img").length,
    "CLIP="+(clip.join(",")||"none")].join("\n");
},1800)};
';
$js = str_replace('__MODE__', $mode, $js);
$html = '<!doctype html><meta charset="utf-8">'
      . '<style>body{margin:0;background:#222}iframe{width:' . $w . 'px;height:2600px;border:0;display:block}</style>'
      . '<iframe id="f" src="http://127.0.0.1:8091/game.php?cmd=' . $cmd . '"></iframe>'
      . '<pre id="out">PENDING</pre><script>' . $js . '</script>';
file_put_contents('D:/XXJserver/phpstudy_pro/WWW/_tposhot.html', $html);
echo 'WROTE _tposhot.html mode=' . $mode . ' iframe_w=' . $w . "\n";
```

> `$js` van la chuoi PHP **mot dau nhay** nhu harness Task 9 (de `"\n"` gi nguyen dang literal cho JS), va **khong duoc xuat hien dau nhay don** trong do: selector viet `input[name=tp_action]` **khong co dau nhay** (CSS cho phep identifier tran) chinh vi ly do nay. `str_replace('__MODE__', ...)` dung de nhay mode vao JS ma khong phai cat chuoi.

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
CHROME="C:/Program Files/Google/Chrome/Application/chrome.exe"; [ -x "$CHROME" ] || CHROME=$(which chrome)
SHOT="D:/XXJserver/phpstudy_pro/WWW/docs/img/thienphu"; mkdir -p "$SHOT"

CAPO() {  # $1 = mode harness, $2 = ten anh, $3 = so dan seed
  "$P" E:/window/Temp/_tpo_seed.php --dan=$3
  "$P" E:/window/Temp/_tpo_harness.php "$1" >/dev/null
  local D="E:/window/Temp/tposhot_$1_$RANDOM"
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
    --force-device-scale-factor=1 --user-data-dir="$D" \
    --window-size=940,2700 --virtual-time-budget=30000 \
    --screenshot="$SHOT/$2" "http://127.0.0.1:8091/_tposhot.html"
  "$P" E:/window/Temp/_tpo_harness.php "$1" >/dev/null   # token moi cho lan dump-dom
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --no-first-run \
    --force-device-scale-factor=1 --user-data-dir="${D}b" \
    --window-size=940,2700 --virtual-time-budget=30000 \
    --dump-dom "http://127.0.0.1:8091/_tposhot.html" > E:/window/Temp/_tpdom.html
  echo "== $2 (mode=$1 dan=$3)"; "$P" E:/window/Temp/_tpread.php
}
CAPO mobile  open-390.png    5
CAPO desktop open-840.png    5
CAPO nodan   open-nodan.png  0
CAPO result  open-result.png 5
rm -f _tposhot.html   # harness nam trong web root — xoa ngay, khong commit (Global Constraints)
```

> `--virtual-time-budget=30000` (cao hon 25000 cua Task 8/9) vi mode `result` co **hai** lan tai trang, moi lan cho 1800ms + thoi gian POST/roll that (mot `INSERT ... ON DUPLICATE KEY UPDATE` + `tianfu_recalc_mirror`). `_tpread.php` dung lai cua Task 8 (doc `<pre id="out">` tu `E:/window/Temp/_tpdom.html`, regex phai co `[^>]*`).
> **Neu `<pre>` van la `PENDING`** o mode `result`: nguyen nhan thuong gap la form chua kip render (1800ms la du, nhung neu server dang cham thi khong) hoac `posted` bi dat trong iframe. Chay lai `CAPO result ...` — **dung** tang timeout len qua 3000ms, vi Chrome headless cat `virtual-time` va `<pre>` se khong bao gio duoc ghi.

So phai dat (**moi con so deu suy ra duoc tu CSS Task 7 va config Task 1 — khong co so nao doan**):

| bien | `open-390.png` | `open-840.png` | `open-nodan.png` (`--dan=0`) | `open-result.png` (POST that) | cach suy ra |
|---|---|---|---|---|---|
| `WRAP` | `390` | `840` | `390` | `390` | `.tpo-wrap{max-width:660px;padding:10px}` + `@media(min-width:601px){max-width:840px}` |
| `SCROLLW` | `390` | `900` | `390` | `390` | = dung chieu rong iframe; lon hon = tran ngang |
| `CARD` | `370` | `820` | `370` | `370` | `WRAP - 2*padding(10)`; `.tpo-card` la block con, `box-sizing:border-box` (`mystyle.css:1 *{...}`) |
| `ACTW` | `336` | `786` | `336` | `336` | `.main .tpo-act{width:100%}` → `CARD - 2*padding(16) - 2*border(1)` |
| `ACTH` | `>= 40` | nt | nt | nt | `padding:13px` + `font-size:16px` (le `nut-gon-nhan-ngan`: nut ~40px) |
| `OPAC` | `1` | `1` | **`0.45`** | `1` | **dong CSS sua o Step 3** — `.main .tpo-act[disabled]{opacity:.45}` |
| `ACTDIS` / `ACTOFF` | `0` / `0` | `0` / `0` | **`1` / `1`** | `0` / `0` | `$tpoHave > 0` quyet dinh ca `disabled` lan `is-off` |
| `ROWS` | `11` | `11` | `11` | `11` | 1 dong `<th>` + 10 pham tu `tianfu_cfg_key('qualities')` |
| `PCT1` | `40,00%` | nt | nt | nt | `round(40/100*100,2)` = 40 → `number_format(...,2,',','.')` |
| `PCT9` / `PCT10` | `0,09%` / `0,01%` | nt | nt | nt | weight 0.09 / 0.01, tong that = 100.00 |
| `TRUTH` | dung nguyen van cau `[CHOT] 5` | nt | nt | nt | "Trúng sẽ không mất — nó tăng 1 phẩm chất cho chính thiên phú ấy." |
| `PILL` | chua `trong túi: 5` | chua `trong túi: 5` | chua `trong túi: 0` | chua `trong túi: 4` | `result` **tru 1 vien** (lan dump-dom la mot POST rieng) |
| `FOOT` | `Giá 50.000 Linh Thạch / viên ...` | nt | bat dau **`Hết đan`** | `Giá 50.000 ...` | `max(50, djyxb)` = 50.000 (Task 4 Step 4b), `number_format` 4 tham so |
| `GHLEN` | `> 40` | nt | nt | nt | token `?cmd=` that dai 60-120 ky tu; `= 5` nghia la `encode()` tra rong |
| `MSG` / `MSGTXT` | `0` / `NA` | `0` / `NA` | `0` / `NA` | **`1`** / msg cua `tianfu_open()` | GET thuan khong sinh `.tp-msg`; chi POST |
| `RES` | `0` | `0` | `0` | **`1`** | `[CHOT] 1`: GET khong bao gio roll |
| `IMGS` | `0` | `0` | `0` | `0` | spec §4.5 chi co 7 anh (1 `phu-cac.jpg` + 6 nhom) — **trang Khai Phu khong co anh**; xem "CHƯA test" ben duoi |
| `TABS` / `TABON` | `4` / `1` | nt | nt | nt | tabbar giong Task 8/9; `.on` o nut thu 3 (⚗ Khai Phu) |
| `BODYH` | `> 500` | `> 500` | `> 500` | `> 580` | bang 11 dong ≈ 11×31 = 341 + title/pill/truth/nut/foot ≈ 250; `< 400` = CSS khong nap |
| `CLIP` | `none` | `none` | `none` | `none` | nhan `Khai Mở`, 4 nhan tabbar va `Mua đan` khong bi cat (le `nut-gon-nhan-ngan`) |

Bon bien **phu thuoc lan roll** (khong co gia tri co dinh — doi chieu bang tra duoi): `TIER`, `GLOW`, `RCLS`, `RNAME`/`RQ`/`RNEW`.

| pham roll duoc | `TIER` (= `tianfu_quality_color`) | `RCLS` | `GLOW` (`--tp-glow` cua `.tp-fx<N>`) |
|---|---|---|---|
| 1 Phàm Phẩm | `#9e9e9e` | `tpo-result tp-fx0` | `4px` |
| 2 Linh Phẩm | `#4caf50` | `tpo-result tp-fx0` | `4px` |
| 3 Huyền Phẩm | `#2196f3` | `tpo-result tp-fx1` | `7px` |
| 4 Địa Phẩm | `#9c27b0` | `tpo-result tp-fx1` | `7px` |
| 5 Thiên Phẩm | `#ff9800` | `tpo-result tp-fx2` | `11px` |
| 6 Thánh Phẩm | `#ff5722` | `tpo-result tp-fx3` | `15px` |
| 7 Tiên Phẩm | `#f44336` | `tpo-result tp-fx4` | `20px` |
| 8 Thần Phẩm | `#ffd700` | `tpo-result tp-fx4` | `20px` |
| 9 Đế Phẩm | `#7c4dff` | `tpo-result tp-fx5` | `26px` |
| 10 Đạo Phẩm | `#e0e0e0` | `tpo-result tp-fx5` | `26px` |

> Cột `TIER` lay tu `qualities[*].color` cua Task 1, cột `GLOW` tu `.tp-fx0..fx5` cua Task 7, cột `RCLS` tu `tianfu_fx_class()` (Task 2: q≤2→0, 3-4→1, 5→2, 6→3, 7-8→4, 9-10→5). **Bat buoc doi chieu ca 3 cột trong CUNG mot lan chay** — chung la mot bo ba khop nhau, va `GLOW` sai lech khoi `RCLS` la bang chung `--tp-glow` khong duoc ke thua (tuc `.tp-fx<N>` bi dat sai cho).
> **Bay:** `TIER = #9e9e9e` vua la mau pham 1, vua la mau fallback khi **khong co** ket qua (`$tpoRColor = '#9e9e9e'`). Vi vay phai doc `RES` truoc: `RES=0` + `TIER=NA` moi la "chua roll"; `RES=1` + `TIER=#9e9e9e` la roll trung pham 1 (40% — kha nang cao nhat).
> **Hai lan Chrome = hai lan roll khac nhau** (moi `--user-data-dir` la mot phien rieng, va moi POST la mot vien dan). Nen `open-result.png` va bo so o lan dump-dom **co the khac pham nhau**. Muon anh va so khop nhau: chay `CAPO result open-result.png 5` roi doc `RQ`; neu muon anh pham cao de xem hieu ung (fx4/fx5 ruc `20px`/`26px`), chay them 1-2 lan nua (moi lan ton 1 vien dan va **ghi de** cung ten anh).

**Bat buoc NHIN ANH, khong chi doc so** (le `verify-visually-not-just-metrics`): mo ca 4 PNG va doi chieu **1:1** voi `docs/img/huyetmach/` (cung khung 660/840, cung kieu tabbar 4 nut, cung nen toi). Quet computed style **khong bat duoc** cac lech thi giac sau:

- `open-nodan.png`: nut `Khai Mở` mo (opacity .45) nhung **van phai doc duoc chu** — `.45` tren nen gradient cam→do la ranh gioi; neu chu nhu tan bien thi phai bao chu du an thay vi tu nang len `.6` (doi mau = doi thiet ke).
- `open-result.png`: vien `.tpo-result` theo mau pham + chu `.tpo-result-name` co `text-shadow` (`--tp-glow`). Voi pham 1 (`fx0`, glow `4px`) panel **gần như xám** — day la dung y (bac thap nhat van thay vien mau + text-shadow, le `feedback-tiered-visual-effects`), khong phai loi.
- `open-840.png`: bang 10 pham keo gian 786px, 4 cột `text-align:center` — kiem tra **khong co cot nao trong** (le `feedback-table-interactivity`: bang nay la bang tra cuu tinh, khong can loc/sap xep vi chi co 10 dong).
- `.tpo-foot` chu 11.5px — nho nhat trang; doc that tren mobile xem co qua nho khong (chu du an >50 tuoi, le `user-identity`).

**Bam that trong browser** (le `feedback-careful-server-changes` + `flag-untested-in-handoff`): `start chrome "http://127.0.0.1:8091/game.php?cmd=<token moi>"` — token sinh bang `"$P" E:/window/Temp/_tpc_url.php "cmd=tianfu_open&sid=e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2"` (dung lai script Task 9, **moi lan quay lai trang phai sinh token moi**). Bam lan luot va ghi ket qua vao ban giao:

| # | thao tac | ket qua phai thay |
|---|---|---|
| 1 | `Khai Mở` khi co dan | `tp-msg ok`, panel `.tpo-result` hien ten + pham + tran Lv, `RNEW` dung (MỚI / đã có sẵn), so dan o `.tpo-pill` **giam 1**, "đã khai mở" **tang 1** |
| 2 | **F5** ngay sau buoc 1 | Chrome hoi **"Xác nhận gửi lại biểu mẫu?"** — phai **huy**. Neu khong hoi ma roll luon thi `[CHOT] 1` that bai (GET dang roll) → dung lai, sua `game/tianfu_open.php` |
| 3 | F5 roi **dong y** gui lai | roll them 1 lan, dan giam them 1 — day la hanh vi dung (nguoi choi chu dong xac nhan), khong phai loi |
| 4 | `Khai Mở` khi het dan | nut **khoa**, bam khong co gi xay ra; `.tpo-foot` hien `Hết đan` + gia |
| 5 | `Mua đan` | sang Thương Điếm tab Đao Cu; **tim thay "Khai Phú Đan"** (Step 6d da chung minh bang curl; buoc nay chung minh bang mat — day chinh la cho Huyet Mach da ket o `docs/UI_HUYETMACH.md:504`) |
| 6 | mua 1 vien roi quay lai ⚗ Khai Phu | so dan tang 1, nut mo khoa=false |
| 7 | roll **trung lai** thien phu da co | `RNEW` = "Đã có sẵn — tăng 1 phẩm chất"; mo 🗄 Phú Các xac nhan **khong them o moi**, pham o cu **+1** |
| 8 | 4 nut tabbar | 🌟 Thiên Phú / 🗄 Phú Các / ⚗ Khai Phú (`.on`) / 👤 Nhân Vật — ca 4 mo duoc, nhan khong bi cat |

> **Quyet dinh co y (KHONG phai thieu sot): trang Khai Phu khong co hieu ung DONG.** `.tp-fx<N>` tren `.tpo-result` chi cap `--tp-glow` (cho `text-shadow` cua `.tpo-result-name`) va ke thua `--tp-tier`; **khong co** `.tp-aura`/`.tp-sweep` ben trong, ma hai keyframe `tpAuraPulse`/`tpSweepRun` (Task 7) chi chay tren hai phan tu do. Ly do khong them: `.tp-aura{position:absolute;inset:0;mix-blend-mode:screen}` bat buoc phai co to tien `position:relative;overflow:hidden;isolation:isolate` (xem `.tp-forge-wrap` + `.tp-stage` cua Task 8) — them vao `.tpo-result` la **doi thiet ke cua mot rule da co** va co that bao ve mat (lop `screen` de len chu). Hieu ung dong theo bac da duoc the hien o **hai man thuong truc** (`.tp-stage` man chính, `.tpc-cell.is-active` o Phú Các); `.tpo-result` chi la panel thong bao ket qua mot lan. **Neu chu du an muon ket qua roll cung nhay theo pham**, viec do la **mot task rieng sau nay** (them `position:relative;overflow:hidden;isolation:isolate` vao `.tpo-result` + 2 `<span>` trong markup), khong nam trong plan nay.

**CHƯA test o Task 10** (ghi ro vao ban giao, le `flag-untested-in-handoff`):
(a) **Trang Khai Phu khong co anh nen** — spec §4.5 chi cap 7 anh (1 `phu-cac.jpg` + 6 anh nhom), khong co anh nao cho man nay, trong khi Huyết Mạch (`hm-stage-img`) va Nhục Thân deu co anh san khau. **Cau hoi cho chu du an** (khong tu sinh anh: le `feedback-khong-mo-rong-pham-vi-sua` + `feedback-image-color-match-page`): co muon them `images/tianfu/khai-phu.jpg` (lo dan/dan dinh, mau lay tu `.tpo-card` `#171029→#0b1024`) khong? Plan nay **giu nguyen khong anh** cho dung pham vi spec da duyet.
(b) `RNEW` nhanh "Đã có sẵn — tăng 1 phẩm chất" **chua chac trung trong 1 lan chay** (40% pham 1, va trung lai phu thuoc 18 weight) — thao tac 7 o tren co the phai lap lai nhieu lan; neu chua gap thi ghi ro "chua quan sat duoc" thay vi suy ra.
(c) Phan hoi F5 (thao tac 2/3) la hanh vi **cua Chrome**, khong phai cua code — da kiem tra bang headless (`RES=0` tren GET, P4 trong `tests/test_tianfu_pages.php`), nhung hop thoai xac nhan thi chi kiem tra bang tay duoc.
(d) Anh `open-result.png` va bo so `TIER/GLOW/RCLS` co the **khac lan roll nhau** (xem note "Hai lan Chrome = hai lan roll"); chua dong bo hoa duoc hai lan trong cung mot phien headless.
(e) Chua do toc do: 1 POST khai mo = 1 `UPDATE playerdaoju` + 1 `INSERT ... ON DUPLICATE` + `tianfu_recalc_mirror` + 1 `INSERT log`; voi anti-flood 10ms (`game.php:48`) thi van de nam o DB, chua do bang `curl -w`.

- [ ] **Step 8: Commit**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
rm -f _tposhot.html
git status --porcelain | head -20
#   phai thay: M game.php, M css/mystyle.css, ?? game/tianfu_open.php, ?? tests/test_tianfu_pages.php, ?? docs/img/thienphu/open-*.png
#   KHONG duoc thay: _tposhot.html, _tpo_seed.php (nam ngoai repo), tongquan.php
git add game/tianfu_open.php game.php css/mystyle.css tests/test_tianfu_pages.php \
        docs/img/thienphu/open-390.png docs/img/thienphu/open-840.png \
        docs/img/thienphu/open-nodan.png docs/img/thienphu/open-result.png
git diff --cached --name-status | head -12   # xac nhan DUNG 8 duong dan, khong co tongquan.php
git commit -m "feat(tianfu): trang Khai Phu .tpo-* — uong dan 114, bang ty le 10 pham, ket qua theo pham (Task 10)"
```

> **Van cam `git add -A` / `git add .`** (Global Constraints): goc repo co 300+ `shot_*.png` lang xang va `tongquan.php` da bi sua tu truoc plan nay (thuoc Task 11, commit rieng). `git add` theo duong dan cu the, **doc lai `--cached` truoc khi commit**; thay duong dan la thi `git reset HEAD <file>` ngay.
> Sau commit, them lai sentinel `<!--PART11-->` vao cuoi file plan neu ban dang danh dau tien do thuc thi (le cua `docs/superpowers/plans/2026-10-09-huyetmach.md`).

> Neu Step 7 phat hien lech: sua `game/tianfu_open.php` hoac dong CSS o Step 3, chay lai **ca Step 4 (lint/quet), Step 6 (test HTTP) lan Step 7 (do/chup)** roi moi commit. Anh trong `docs/img/thienphu/` phai la anh cua dung ban code duoc commit — khong commit anh cu kem code moi.

---

<!--PART11-->

### Task 11: TICH HOP 7 CONG + noi vao cho khac (spec §5, §4.7)

**Files:**
- Modify: `class/function/tianfu.php` — them **4 helper** o CUOI file (trong namespace `player`, boc `function_exists`): `tianfu_luck_gate`, `tianfu_forge_cost`, `tianfu_stamina_interval`, `tianfu_bonus_pct`
- Modify: `game/xiulian.php:111-113` — cong 1 `exp_cultivate`
- Modify: `game/pve.php:700-708` (cong 2 `exp_monster`), `:469,489,518` (cong 3 `drop`)
- Modify: `game/boss.php:454,456` (XOA `tfxy`), `:458,485,503` (cong 3), `:596-604` (cong 2)
- Modify: `class/function/luonden.php:348` — cong 4 `alchemy`
- Modify: `game/zbinfo.php:40` va ban guong `:148` — cong 5 `forge_cost`
- Modify: `class/player.php:336` (`settle_stamina` — them tham so thu 6), `game.php:452-457`, `cron/stamina_recovery.php:51-57` — cong 6 `stamina`
- Modify: `game/tupo.php:123` — cong 7 `breakthrough`
- Modify: `game/zhuangtai.php:418-419`, `class/player.php:418-420` (`combat_power`), `game/paihang.php:17-19`
- Modify: `tongquan.php` — them muc `thien-phu` (**COMMIT RIENG, hoi chu du an truoc** — xem Step 15)
- Create: `tests/test_tianfu_gates.php` (2 phan: thuan + `--live`)

**Interfaces:**
- Consumes (Task 1/2/3): `tianfu_bonuses($sid,$dblj)` (15 key, 7 cong la **float %** da clamp 30), `tianfu_cfg_key('gates')` (7 khoa, moi khoa co `name`/`unit`/`desc`), `tianfu_defs()` (18), `tianfu_config_all()['qualities']` (10 dong) / `tianfu_quality_def($q)`, `$player->tianfu_bonus` (da hydrate o Task 3 Step 5b), `$player->tianfu_id/quality/lv/power/name/color/icon`, `tianfu_activated($sid,$dblj)`.
- Produces:
  - `tianfu_luck_gate($pct, $gate): int` — nhan tiep nguong roi (cung hinh dang `huyetmach_combat_luck_gate`, `class/function/huyetmach.php:1142`)
  - `tianfu_forge_cost($pct, $cost): int` — giam Linh Thach, **san 1**
  - `tianfu_stamina_interval($pct, $intervalSec = 300): int` — rut chu ky hoi, **san 30 giay**
  - `tianfu_bonus_pct($sid, $dblj, $gate): float` — doc 1 cong tu `tianfu_bonuses()`, tra `0.0` neu cong la/khong co
  - `settle_stamina(..., $staminaPct = 0)` — tham so thu 6, mac dinh 0 = hanh vi cu (khong vo hieu hoa `bak/nguhanh/*.php`)
  - `combat_power()` co khoa `'tianfu_power' => 1` va **het** `tf/tfxy/tfsb`; `$player->tianfu_power` dung cho `game/paihang.php`
  - `tests/test_tianfu_gates.php` — phan `--live` la **ve canh hoi quy** cua 7 cong (Step 10)

> **[CHOT] 4 helper la ham THUAN (khong DB), `$pct` dung dau** — dung hinh dang `huyetmach_combat_luck_gate($pct, $gate)` ma 6 cho trong `pve.php`/`boss.php` dang goi. He qua: (a) test duoc khong can DB, (b) clamp 30% nam **trong helper** nen khong caller nao quen clamp, (c) neu mot ngay nao do `tianfu_bonuses()` doi cach tra thi 7 cho gan van dung. Chi `tianfu_bonus_pct()` dong DB (1 query, dung cho cron va cho test).

> **[CHOT] Cong 3 `drop` NHAN TIEP sau Huyết Mạch → cong don toi da +49.5%.** `huyetmach_combat_luck_gate($pct<=15, $gate)` nhan truoc, `tianfu_luck_gate($pct<=30, ...)` nhan sau: `1.15 x 1.30 = 1.495`. Vi **hai lan `intval(round())`**, nguong 120 that te thanh `round(round(120*1.15)*1.30)` = `round(138*1.3)` = **179** (+49.17%), khong phai 179.4. **Bat buoc ghi ro ca hai con so vao `docs/UI_THIENPHU.md` (Task 12)** — spec §5 dong 3 yeu cau "phai ghi ro trong docs". Khong gop hai gate thanh mot ham: Huyết Mach da release, doi no la mo rong pham vi.

> **[CHOT] `game/boss.php:454 $xyz = $player->tfxy` PHAI XOA trong CUNG commit voi cong 3.** Spec §1.10: `tfxy` dang **nong** nguong roi do cua boss (`:456 mt_rand(1,120-$xyz)`). Neu chi them `tianfu_luck_gate` ma khong go `:454` thi nguoi choi duoc **nhan doi** uu dai (vua giam mau so ngau nhien, vua tang nguong) — loi kinh te giong "Nghich thien cai menh" o `game/tianfu.php:74-86` ma spec §6 bat xoa. `pve.php` **khong co** dong tuong tu (da grep `tfxy` toan `game/pve.php`: 0 ket qua) nen chi `boss.php` can xoa.

> **[CHOT] Cong 1 `exp_cultivate` dat NGOAI chuoi `min(1.3/1.5,...)` cua `game/xiulian.php:111`.** Bon thua so `min()` do la **tran cung cua tung he rieng** (Cong phap / Linh can hấp thu / Linh can exp / Thể chất exp). Thien Phu la he thu 5, tran cua no (30%) da nam trong `tianfu_bonuses()`; chen vao chuoi `min()` thi bi tran 1.3/1.5 cua he khac nuot mat (spec §5 dong 1). Vi tri gan: **`$exp_per_sec` o `:113`** (dong "linh khi thuc nhan moi giay"), KHONG phai `$base_exp_per_sec` o `:111` — vi `$base_exp_per_sec` con duoc dung de in "toc do goc" va de tinh `$base_exp_per_min` o `:114`; gan vao `:113` thi ca `$exp_per_min` (`:115`), `$real_exp_rate_text` (`:117`), `$xlexp` (`:175`) va `put('xlexp', ...)` (`:506`) deu tu dong dung.

> **[CHOT] Cong 6 `stamina`: truyen `$staminaPct` VAO `settle_stamina` (tham so thu 6), goi `tianfu_stamina_interval()` BEN TRONG ham.** Spec §5 dong 6 viet "goi `tianfu_stamina_interval()` trong `settle_stamina`" — plan giu dung y do (ca web lan cron di qua mot cho duy nhat) nhung **khong truyen `$sid`/`$dblj` vao**, vi `settle_stamina` dang la **ham thuan** (chi nhan scalar) va `bak/nguhanh/*.php` con goi no voi 5 tham so. Loi ich: (a) web path **khong ton them query** (`$player->tianfu_bonus` da hydrate o Task 3 Step 5b); (b) ham van test duoc khong DB; (c) `cron/stamina_recovery.php:18 $interval = 300;` **giu nguyen** dung nhu spec noi; cron chi them 1 loi goi `tianfu_bonus_pct($row['sid'], $dblj, 'stamina')` cho moi nhan vat (1 query/dong — chap nhan duoc vi vong lap do da co san 1 UPDATE/dong).

> **[CHOT] Cong 5 `forge_cost` CHI giam `$upls` (Linh Thach), san 1.** Khong dong `$updjsl` (Cuong Hoa Thach, `zbinfo.php:39`) vi cong nay ten la "Linh thach cuong hoa" (config `gates.forge_cost.name`); khong dong ti le cuong hoa o `class/player.php:1465` (`$sjs = mt_rand(1,35); if ($sjs <= $zbqh) return 0;`). **San 1 bat buoc**: `$upls` = 0 thi `changeyxb(2, 0, ...)` (`zbinfo.php:130`) tru 0 dong = cuong hoa mien phi vo han, va `$ls_need = 0` (`:283`) lam `$can_upgrade` luon true.

> **[CHOT] Cong 7 `breakthrough`: cong diem % TRUOC `max(5,...)` va THEM tran mem `min(99,...)`.** `game/tupo.php:123` hien tai la `$rate = max(5, intval($baseRate - $levelPenalty - $realmPenalty + $psjs + $zbsjs));` — **khong co tran tren**: `$zbsjs` (`tuposjs`, diem bao hiem that bai) cong don khong gioi han, nen `$rate` co the > 100 (chi thanh tien trinh bi chan o `:247 min(0,min(100,...))`, con chu in o `:353 <b>$rate%</b>` thi khong). Them `min(99,...)` la **thay doi hanh vi co san**, duoc spec §5 dong 7 yeu cau ("tran mem 99") — ghi ro vao ban giao va vao `docs/UI_THIENPHU.md`. `+5 tf` o `:209` **GIU NGUYEN** (do la diem Thien Phu de nang cap, khong phai chi so cu).

> **[CHOT] `tongquan.php` commit RIENG va PHAI HOI chu du an truoc.** File nay **dang co thay doi chua commit tu truoc khi plan nay bat dau** (4 dong ve mo ta Boss Linh Can + nhiem vu tuan Linh Can, khong lien quan Thien Phu). Xem Step 15.

> **[RANH GIOI — spec §10] 3 man KHONG duoc dong trong Task 11.** Day la quyet dinh cua spec,
> khong phai soc sot — **khong tu mo rong**:
>
> | Man | Vi sao khong cham | Lenh xac nhan (chay that, phai ra **0 dong**) |
> |---|---|---|
> | `game/hammo.php` (Ham Mo / dao bao tang) | Khong co nguong roi kieu `dljv`/`djjv`/`ypjv` → cong 3 `drop` **khong co cho de nhan vao**. Do that 2026-10-14: `grep -c` = **0** | `grep -n 'dljv\|djjv\|ypjv' game/hammo.php` |
> | `game/luonden.php` (man Luyen Dan, 554 dong) | Cung ly do: **0** nguong roi → cong 3 khong cham. **Phan biet ro**: 2 cho ma Task 11 dong nam o **`class/function/luonden.php`** (1363 dong) — `:348` `$finalRate = min(95, max(5, ...))` la so **hien thi**, `:898` `$finalSuccessRate = min(95, max(5, ...))` la so **roll that**. Do la cong 4 `alchemy`, **khong phai** cong 3 `drop` | `grep -c 'dljv\|djjv\|ypjv' game/luonden.php` |
> | `game/pvp.php` (PvP) | **PvP tinh bang chi so thuan**, khong nhan 7 cong. Neu cho thien phu vao PvP thi nguoi choi nap `tf` se **thang truc tiep** nguoi khong nap → pha can bang dau truong (spec §10). Do that 2026-10-14: `grep -c 'tianfu'` = **0** | `grep -n 'tianfu' game/pvp.php` |
>
> **Neu mot trong 3 lenh tren tra ve khac 0** → co nguoi da noi thien phu vao man cam. **Dung lai,
> hoi chu du an**, khong "sua lai cho khop".

- [ ] **Step 0: Snapshot 5 file UNTRACKED truoc khi sua — BAT BUOC, lam truoc tien**

`game/xiulian.php`, `game/zbinfo.php`, `game/tupo.php`, `game/paihang.php`, `class/function/luonden.php`, `cron/stamina_recovery.php` **chua duoc git track** (Global Constraints, muc Git). He qua: `git diff --stat` in ra **khong gi** va `git show HEAD:<file>` bao **`does not exist in HEAD`** ⇒ khong co "nen" de doi chieu so dong them/xoa hay so ky tu CJK. Snapshot la nen thay the:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
mkdir -p E:/window/Temp/_tp_base
for f in xiulian zbinfo tupo paihang; do cp "game/$f.php" "E:/window/Temp/_tp_base/$f.php"; done
cp class/function/luonden.php      E:/window/Temp/_tp_base/luonden.php
cp cron/stamina_recovery.php       E:/window/Temp/_tp_base/stamina_recovery.php
ls -la E:/window/Temp/_tp_base/          # Expected: 6 file, moi file > 1 KB
for f in E:/window/Temp/_tp_base/*.php; do
  echo "$(basename $f)  lines=$(wc -l < $f)  cjk=$(grep -cP '[\x{2E80}-\x{9FFF}]' $f)"
done
```

Expected (do that 2026-10-14, truoc khi Task 11 chay): `xiulian.php cjk>0` (co comment CJK goc), `paihang.php cjk>0`, con lai `cjk=0`. **Ghi lai bang so nay vao ban giao** — Step 10 muc 2 doi chieu voi no.

> Neu ban thuc thi tren may khac hoac `E:` khong ton tai: doi duong dan snapshot thanh `docs/superpowers/plans/_base/` va **xoa thu muc do truoc khi commit** (khong duoc de lot vao `git add`).

- [ ] **Step 1: Viet test FAIL truoc** — `tests/test_tianfu_gates.php`, **phan THUAN** (khong can DB). `tassert()` tu khai bao trong file (le o Task 3 Step 2: helper `tianfu_test_lib.php` **cam** khai bao `tassert`):

```php
<?php
/**
 * test_tianfu_gates.php — 4 helper cua 7 cong + (voi --live) 7 cong gan that vao code.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_gates.php
 *       them --live sau khi Step 3..9 da gan het 7 cong (can DB + nhan vat f*32)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT  = dirname(__DIR__);
$LIVE  = in_array('--live', array_slice($argv, 1), true);
require_once $ROOT . '/class/player.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
function tsection($t) { echo "\n--- $t ---\n"; }

// ===== 1) tianfu_luck_gate — cong 3 (drop): nhan tiep nguong roi =====
tsection('tianfu_luck_gate');
tassert('luck pct 0 giu nguyen',        \player\tianfu_luck_gate(0, 120), 120);
tassert('luck pct 30 gate 100',         \player\tianfu_luck_gate(30, 100), 130);
tassert('luck clamp 30 (truyen 99)',    \player\tianfu_luck_gate(99, 100), 130);
tassert('luck pct am -> coi nhu 0',     \player\tianfu_luck_gate(-5, 100), 100);
tassert('luck gate 0 -> 0',             \player\tianfu_luck_gate(30, 0), 0);
tassert('luck gate am -> giu nguyen',   \player\tianfu_luck_gate(30, -3), -3);
tassert('luck lam tron 15%/120 = 138',  \player\tianfu_luck_gate(15, 120), 138);
// Cong don toi da: Huyet Mach 15% (tran cua no) roi Thien Phu 30% -> spec §5 dong 3: +49.5%
tassert('luck ghep huyet mach 15%',
    \player\tianfu_luck_gate(30, \player\huyetmach_combat_luck_gate(15, 120)), 179);

// ===== 2) tianfu_forge_cost — cong 5: giam Linh Thach, san 1 =====
tsection('tianfu_forge_cost');
tassert('forge pct 0 giu nguyen',       \player\tianfu_forge_cost(0, 12345), 12345);
tassert('forge pct 30 / 1000 = 700',    \player\tianfu_forge_cost(30, 1000), 700);
tassert('forge clamp 30 (truyen 80)',   \player\tianfu_forge_cost(80, 1000), 700);
tassert('forge san 1 (cost 1)',         \player\tianfu_forge_cost(30, 1), 1);
tassert('forge san 1 (cost 2 -> 1.4)',  \player\tianfu_forge_cost(30, 2), 1);
tassert('forge cost 0 -> 0',            \player\tianfu_forge_cost(30, 0), 0);
tassert('forge cost am -> giu nguyen',  \player\tianfu_forge_cost(30, -5), -5);
tassert('forge lam tron 15%/1001 = 851', \player\tianfu_forge_cost(15, 1001), 851);

// ===== 3) tianfu_stamina_interval — cong 6: rut chu ky hoi, san 30s =====
tsection('tianfu_stamina_interval');
tassert('stamina pct 0 giu 300',        \player\tianfu_stamina_interval(0, 300), 300);
tassert('stamina pct 30 / 300 = 210',   \player\tianfu_stamina_interval(30, 300), 210);
tassert('stamina clamp 30 (truyen 75)', \player\tianfu_stamina_interval(75, 300), 210);
tassert('stamina mac dinh 300',         \player\tianfu_stamina_interval(30), 210);
tassert('stamina san 30 (iv 40 -> 28)', \player\tianfu_stamina_interval(30, 40), 30);
tassert('stamina iv 0 -> 0',            \player\tianfu_stamina_interval(30, 0), 0);

// ===== 4) settle_stamina tham so thu 6 — HAM THUAN, khong can DB =====
// [CHOT] cong 6: goi helper BEN TRONG settle_stamina de web (game.php:452) va cron
// (cron/stamina_recovery.php:51) di qua mot cho duy nhat, ma van giu ham thuan.
tsection('settle_stamina + staminaPct');
$now = 1700000000;
$t420 = date('Y-m-d H:i:s', $now - 420);
$A = \player\settle_stamina(0, 100, $t420, $now, 300);
tassert('khong pct: 420s/300 = 1 buoc', $A['steps'], 1);
tassert('khong pct: moc = lastTs+300',  $A['mark'], $now - 120);
$B = \player\settle_stamina(0, 100, $t420, $now, 300, 30.0);
tassert('pct 30: 420s/210 = 2 buoc',    $B['steps'], 2);
tassert('pct 30: gia tri = 2',          $B['value'], 2);
tassert('pct 30: moc = lastTs+420',     $B['mark'], $now);
$C = \player\settle_stamina(99, 100, $t420, $now, 300, 30.0);
tassert('pct 30: 99/100 + 2 = 100 (tran)', $C['value'], 100);
$D = \player\settle_stamina(0, 0, $t420, $now, 300, 30.0);
tassert('max 0: khong hoi, khong fatal', $D['steps'], 0);

echo "\n" . ($fails === 0 ? "ALL PASS (thuan)\n" : "FAILS: $fails\n");
if (!$LIVE) { exit($fails === 0 ? 0 : 1); }
```

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l tests/test_tianfu_gates.php && "$P" tests/test_tianfu_gates.php
```

Expected: `-l` OK, sau do `Fatal error: Call to undefined function player\tianfu_luck_gate()`. Neu thay `FAIL khong pct: ...` **truoc** khi Fatal thi `settle_stamina` da bi ai do sua — dung lai, `git diff class/player.php` kiem tra.

> `$A['mark'] === $now - 120` la `int` vi `settle_stamina` tra `$lastTs + $steps * $intervalSec` (so hoc int); `$C['value'] === 100` chung minh tran `umaxstamina` van giu khi rut chu ky (khong phai hoi qua tran).

- [ ] **Step 2: Them 4 helper vao CUOI `class/function/tianfu.php`** (file dung `namespace player;` khong co ngoac — da do `head -2 class/function/huyetmach.php`; vi vay **append o cuoi file la dung**, khong can chen truoc `}` nao):

```php
// =====================================================================
//  4 HELPER CUA 7 CONG (Task 11) — ham THUAN; clamp 30% nam TRONG nay
//  de khong caller nao quen clamp. Xem [CHOT] o Task 11.
// =====================================================================

if (!function_exists(__NAMESPACE__ . '\tianfu_luck_gate')) {
    /**
     | Cong 3 (drop): nong nguong roi do. Cung hinh dang huyetmach_combat_luck_gate()
     | (class/function/huyetmach.php:1142) de hai he ghep duoc: gate goc -> Huyet Mach -> Thien Phu.
     | Cong don toi da 1.15 x 1.30 = +49.5% (spec §5 dong 3); nguong 120 that te thanh 179
     | vi hai lan intval(round()) — 179/120 = +49.17%. Ghi ca hai con so vao docs.
     */
    function tianfu_luck_gate($pct, $gate){
        $pct  = min(tianfu_cap_percent(), max(0.0, floatval($pct)));
        $gate = intval($gate);
        if ($gate <= 0 || $pct <= 0) return $gate;
        return intval(round($gate * (100 + $pct) / 100));
    }
}

if (!function_exists(__NAMESPACE__ . '\tianfu_forge_cost')) {
    /**
     | Cong 5 (forge_cost): GIAM Linh Thach cuong hoa. SAN 1 — gia 0 thi
     | zbinfo.php:130 changeyxb(2, 0, ...) tru 0 dong (cuong hoa mien phi vo han)
     | va :289 $can_upgrade luon true. KHONG dung cho Cuong Hoa Thach ($updjsl).
     */
    function tianfu_forge_cost($pct, $cost){
        $pct  = min(tianfu_cap_percent(), max(0.0, floatval($pct)));
        $cost = intval($cost);
        if ($cost <= 0 || $pct <= 0) return $cost;
        $out = intval(round($cost * (100 - $pct) / 100));
        return ($out < 1) ? 1 : $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\tianfu_stamina_interval')) {
    /**
     | Cong 6 (stamina): rut chu ky hoi the luc (goc 300s, class/player.php:336).
     | SAN 30 GIAY: ngan hon thi cron 5 phut (cron/stamina_recovery.php) khong theo kip,
     | nguoi choi thay the luc nhay lien tuc giua hai lan cron chay.
     */
    function tianfu_stamina_interval($pct, $intervalSec = 300){
        $pct = min(tianfu_cap_percent(), max(0.0, floatval($pct)));
        $iv  = intval($intervalSec);
        if ($iv <= 0 || $pct <= 0) return $iv;
        $out = intval(round($iv * (100 - $pct) / 100));
        return ($out < 30) ? 30 : $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\tianfu_bonus_pct')) {
    /**
     | Doc 1 cong (float %) tu tianfu_bonuses(); tra 0.0 neu cong la.
     | DONG DB (1 query qua tianfu_owned) — chi dung cho cron va cho test.
     | Trang UI doc thang $player->tianfu_bonus[<gate>] (da hydrate, class/player.php Task 3 Step 5b)
     | de khong ton them query trong moi request.
     */
    function tianfu_bonus_pct($sid, $dblj, $gate){
        $B = tianfu_bonuses($sid, $dblj);
        return isset($B[$gate]) ? floatval($B[$gate]) : 0.0;
    }
}
```

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l class/function/tianfu.php && "$P" tests/test_tianfu_gates.php
```

Expected: `-l` OK roi **`ALL PASS (thuan)`** — **29 `PASS`, 0 `FAIL`** (8 `luck` + 8 `forge` + 6 `stamina` + 7 `settle_stamina`). (Phan `--live` chua ton tai nen `$LIVE` chi thoat som; Step 10 moi them.)

> Dung `tianfu_cap_percent()` (doc `cap_percent` tu config) thay vi go `30.0`: chu du an doi tran trong `tianfu_config.json` thi 4 helper tu dong theo, khong phai sua 3 cho. `min(cap, max(0.0, ...))` la **clamp kep** (du lieu tu `tianfu_bonuses()` da clamp roi) — co y, vi helper con duoc goi truc tiep tu test va tu bat ky cho nao chua co `$player`.

- [ ] **Step 3: Cong 1 `exp_cultivate` — `game/xiulian.php`**

3a. Them 1 dong **ngay sau** `$tc_exp_bonus = ...` (`:104`):

```php
$tp_exp_bonus = isset($player->tianfu_bonus['exp_cultivate']) ? floatval($player->tianfu_bonus['exp_cultivate']) : 0;
```

3b. Sua **dung 1 dong** `$exp_per_sec` (`:113` — sau khi them 3a no thanh `:114`):

| | CU | MOI |
|---|---|---|
| 1 dong | `$exp_per_sec = round($base_exp_per_sec * (1 + $equip_bonus_percent / 100), 4); // linh khí thực nhận mỗi giây` | `$exp_per_sec = round($base_exp_per_sec * (1 + $equip_bonus_percent / 100) * (1 + $tp_exp_bonus / 100), 4); // linh khí thực nhận mỗi giây (Thiên Phú nhân NGOÀI chuỗi min() — Task 11 [CHOT])` |

**KHONG sua `:111`** (`$base_exp_per_sec`): no la "linh khi goc", con duoc dung cho `$base_exp_per_min` (`:114`) va cho chu "Toc do goc" tren trang. Clamp 30% **khong lap lai o day** — no nam trong `tianfu_bonuses()` (Task 3); 4 thua so `min(1.3/1.5, ...)` cua he khac cung khong clamp lai trong trang nay, giu dung le cua file.

```bash
"$P" -l game/xiulian.php
grep -n "tp_exp_bonus" game/xiulian.php     # Expected: DUNG 2 dong (khai bao + su dung)
```

- [ ] **Step 4: Cong 2 `exp_monster` — `game/pve.php` + `game/boss.php`**

Hai cho **giong het nhau** (cung khuon `$finalExp` + phat đan độc). Chen **sau khoi phat đan độc, truoc `player\changeexp(...)`** — phep nhan giao hoan nen dat dau cung ra cung ket qua, dat cuoi de tranh lam tron hai lan tren cung mot so:

`game/pve.php` — chen vao **giua** `$finalExp = max(1, intval($finalExp * (1 - $toxinPenalty / 100)));` + `}` (`:705-706`) va `player\changeexp($sid,$dblj,$finalExp);` (`:708`), giu nguyen **12 space** thut le cua khoi:

```php
            // Thien Phu (exp_monster): nhan vao exp that, SAU phat dan doc, TRUOC changeexp.
            $tpExpPct = isset($player->tianfu_bonus['exp_monster']) ? floatval($player->tianfu_bonus['exp_monster']) : 0;
            if ($tpExpPct > 0) {
                $finalExp = max(1, intval(round($finalExp * (100 + $tpExpPct) / 100)));
            }
```

`game/boss.php` — chen vao giua `}` (`:602`) va `player\changeexp($sid,$dblj,$finalExp);` (`:604`), **cung 12 space**, cung 5 dong do (dung **cung ten bien** `$tpExpPct`: `game.php` chi `include` **mot** file cho moi `?cmd=`, nen `pve.php` va `boss.php` khong bao gio chay trong cung mot request — khong co trung bien):

```php
            // Thien Phu (exp_monster): nhan vao exp that, SAU phat dan doc, TRUOC changeexp.
            $tpExpPct = isset($player->tianfu_bonus['exp_monster']) ? floatval($player->tianfu_bonus['exp_monster']) : 0;
            if ($tpExpPct > 0) {
                $finalExp = max(1, intval(round($finalExp * (100 + $tpExpPct) / 100)));
            }
```

> `max(1, ...)` giu bat bien "exp it nhat 1" ma dong phat đan độc dang giu (`pve.php:705`, `boss.php:601`) — neu bo, quai 1 exp + thien phu 0% co the tra 0 va `changeexp(0)` ghi log rac.
> `$huode .= 'Nhận được: '.$finalExp.' tu vi'` (`boss.php:606`) va `$expDisplay` (`pve.php:728`) **tu dong hien so da nhan** vi nam sau cho chen — khong can sua.

```bash
"$P" -l game/pve.php && "$P" -l game/boss.php
grep -n "tianfu_bonus\['exp_monster'\]" game/pve.php game/boss.php   # Expected: 1 dong moi file
grep -c "player\\changeexp(\$sid,\$dblj,\$finalExp);" game/pve.php game/boss.php   # Expected: van 1 moi file
```

- [ ] **Step 5: Cong 3 `drop` — noi nguong roi do o 2 file chien dau**

**5a. `game/pve.php`** — them 1 dong **ngay sau** `$hmCs = \player\huyetmach_combat_stats(...)` (`:280`, indent 4 space):

```php
    $tpDropPct = isset($player->tianfu_bonus['drop']) ? floatval($player->tianfu_bonus['drop']) : 0;
```

**5b. `game/pve.php`** — boc **3** nguong roi bang `tianfu_luck_gate()`. Chi sua **tham so thu 2** cua `huyetmach_combat_luck_gate()`; **giu nguyen** moi thu khac tren dong (ke ca khoang trang `>= $sjjv` va chuoi `drop_bonus`):

| Dong | CU (tham so 2) | MOI (tham so 2) |
|---|---|---|
| `:469` (trang bi, goc 120) | `($yguaiwu->dljv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0))` | `\player\tianfu_luck_gate($tpDropPct, ($yguaiwu->dljv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0)))` |
| `:489` (vat pham, goc 180) | `($yguaiwu->djjv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0))` | `\player\tianfu_luck_gate($tpDropPct, ($yguaiwu->djjv + (($wgEffectType == 'drop_bonus') ? $wgEffectValue : 0)))` |
| `:518` (duoc pham, goc 100) | `$yguaiwu->ypjv` | `\player\tianfu_luck_gate($tpDropPct, $yguaiwu->ypjv)` |

> Them 5a xong thi 3 dong nay dich thanh `:470 / :490 / :519`. **Lam theo NOI DUNG dong, khong lam theo so dong.**

**5c. `game/boss.php`** — them 1 dong **ngay sau** `$hmCs = \player\huyetmach_combat_stats(...)` (`:286`, indent 4 space), giong het 5a:

```php
    $tpDropPct = isset($player->tianfu_bonus['drop']) ? floatval($player->tianfu_bonus['drop']) : 0;
```

**5d. `game/boss.php` — XOA dong `$xyz`** (`:454`, theo `[CHOT]` o dau Task). Dong do co **comment CJK** (`//天赋幸运加入`) nen **KHONG go lai bang tay** — dung `sed` theo so dong, co guard:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
grep -n 'tfxy' game/boss.php          # Expected: dung 1 ket qua, dong 454
sed -n '454p' game/boss.php           # Expected: in ra dong  $xyz =  $player->tfxy;//...
BEFORE=$(wc -l < game/boss.php); echo "BEFORE=$BEFORE"
sed -i '454d' game/boss.php
AFTER=$(wc -l < game/boss.php); echo "AFTER=$AFTER"
test "$AFTER" -eq $((BEFORE - 1)) && echo "OK: xoa dung 1 dong" || { echo "LOI so dong — hoi lai"; git checkout -- game/boss.php; exit 1; }
git diff --stat -- game/boss.php      # Expected: 1 file changed, 0 insertions(+), 1 deletion(-)
grep -n 'tfxy' game/boss.php          # Expected: KHONG co ket qua
```

> `git diff --stat` **bat buoc** phai la `0 insertions`. Neu thay `1 insertion` nghia la `sed` da de lai BOM/CRLF lac — `git checkout -- game/boss.php` roi lam lai tu dau.

**5e. `game/boss.php`** — sua dong `mt_rand` cua **trang bi** (cu `:456`, sau 5d la `:455`) ve nguong goc 120, de `tianfu_luck_gate()` thanh noi **DUY NHAT** nang nguong:

```php
        $sjjv = mt_rand(1,120);
```

(indent 8 space — giong cac dong `$sjjv` khac trong cung file.)

**5f. `game/boss.php`** — boc **3** nguong roi (cu `:458 / :485 / :503`; sau 5d–5e la `:457 / :484 / :502`). Dong trang bi viet `>=$sjjv` **khong co khoang trang** — giu nguyen:

| Dong | CU (tham so 2) | MOI (tham so 2) |
|---|---|---|
| `:458` (trang bi, goc 120) | `$boss->dljv` | `\player\tianfu_luck_gate($tpDropPct, $boss->dljv)` |
| `:485` (vat pham, goc 100) | `$boss->djjv` | `\player\tianfu_luck_gate($tpDropPct, $boss->djjv)` |
| `:503` (duoc pham, goc 100) | `$boss->ypjv` | `\player\tianfu_luck_gate($tpDropPct, $boss->ypjv)` |

**5g. Kiem chung:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l game/pve.php && "$P" -l game/boss.php
grep -c 'tianfu_luck_gate(' game/pve.php game/boss.php              # Expected: pve.php 3, boss.php 3
grep -n 'tianfu_luck_gate(' game/pve.php game/boss.php | grep -v 'huyetmach_combat_luck_gate'; echo "exit=$?"
#   ^ Expected: KHONG in gi, exit=1  → moi lan goi tianfu_luck_gate deu NAM TRONG huyetmach gate (khong cong 2 lan)
grep -c 'huyetmach_combat_luck_gate(' game/pve.php game/boss.php    # Expected: van 3 va 3
grep -rn 'tfxy' game/ class/ 2>/dev/null                            # Expected: 0 ket qua
grep -n 'mt_rand(1,120' game/pve.php game/boss.php                  # Expected: 2 ket qua (moi file 1)
```

Expected: `-l` OK cho ca hai file.

> **CHUA TEST** (Step 11 moi test `--live`, va phan roi do van la test **tay**): chua ai chay that 200 lan danh quai de do ty le roi truoc/sau. Day la con so **co huong** (gate 120 → 156 khi +30%), khong phai do dac. Ghi vao `docs/UI_THIENPHU.md` (Task 12) muc CHUA.

- [ ] **Step 6: Cong 4 `alchemy` — ty le luyen dan**

**6a. `class/function/luonden.php`** — them 1 dong **ngay truoc** `$finalRate = min(95, ...)` (`:348`, indent 8 space). Ham nay nhan `$player` la **object** (`:326` doc `$player->linhcan_element`) nen doc kieu object:

```php
        $tpAlchemy = isset($player->tianfu_bonus['alchemy']) ? floatval($player->tianfu_bonus['alchemy']) : 0;
```

**6b. `class/function/luonden.php`** — sua dung dong `:348`, chen `+ $tpAlchemy` **TRUOC** `- $backlashPenalty` (phan phe van tru sau cung, khong bi thien phu bu lai):

| | CU | MOI |
|---|---|---|
| 1 dong | `$finalRate = min(95, max(5, $baseRate + $bonus + $dinhBonus + $locationBonus + $eventBonus - $backlashPenalty));` | `$finalRate = min(95, max(5, $baseRate + $bonus + $dinhBonus + $locationBonus + $eventBonus + $tpAlchemy - $backlashPenalty));` |

> `min(95, ...)` o tran **giu nguyen** — thien phu day len 95% thi dung lai, giong Huyết Mạch/Linh Căn da lam.

**6c. KHONG DUNG `luonden.php:898`** (`$finalSuccessRate = min(95, max(5, $baseRate + $elementBonus + $dinhBonus + $eventSuccessMod));` trong `luonden_do_craft`). Day la **cong thuc thu hai, tach biet** voi `:348` — spec §5 chi goi ten `:348`.

> **NO CHU DANH CHO CHU DU AN (ghi vao `docs/UI_THIENPHU.md`, Task 12):** `:348` la ty le **hien thi** (goi tu `:594` va `:734`), `:898` la ty le **roll that** khi luyen. Chi sua `:348` theo spec thi man hinh co the bao 80% ma xuc xac van roll theo cong thuc cu. Hai kha nang: (1) chap nhan lech va ghi ro, (2) sua ca `:898` — nhung `:898` khong co `$player->tianfu_bonus` trong pham vi do, phai truyen them. **KHONG tu quyet** — dung lai hoi.

**6d. Kiem chung:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l class/function/luonden.php
grep -n 'tpAlchemy' class/function/luonden.php        # Expected: 2 ket qua (1 gan, 1 dung)
grep -c 'min(95, max(5,' class/function/luonden.php   # Expected: van 2 (:348 va :898 — khong ai bi xoa)
# luonden.php UNTRACKED -> dung snapshot cua Step 0, KHONG dung git diff --stat
diff -u E:/window/Temp/_tp_base/luonden.php class/function/luonden.php > E:/window/Temp/_d_luonden.txt
echo "ADD=$(grep -c '^+[^+]' E:/window/Temp/_d_luonden.txt) DEL=$(grep -c '^-[^-]' E:/window/Temp/_d_luonden.txt)   # Expected: ADD=2 DEL=1\"
```

- [ ] **Step 7: Cong 5 `forge_cost` — giam Linh Thach cuong hoa**

**7a. `game/zbinfo.php`** — them 1 dong **ngay truoc** `$updjsl = intval($zhuangbei->qianghua * 3 + 1);` (`:39`, indent **0** — file nay viet sat le):

```php
$tpForgePct = isset($player->tianfu_bonus['forge_cost']) ? floatval($player->tianfu_bonus['forge_cost']) : 0;
```

> `$player` da co tu `:3` (`$player = \player\getplayer($sid, $dblj);`) nen `tianfu_bonus` da hydrate (Task 3 Step 5b) — khong ton them query.

**7b. `game/zbinfo.php`** — boc **`$upls`** o **HAI** cho. `$upls` la Linh Thach; `$fjls` (`:41`, `:149`) la **Cuong Hoa Thach** va `$updjsl` (`:39`, `:147`) la **so luong daoju can** — spec §5 dong 5 chi cho phep giam Linh Thach, **cam** dung vao 2 cai con lai:

| Dong | Indent | CU | MOI |
|---|---|---|---|
| `:40` | 0 space | `$upls = intval(round($zhuangbei->qianghua / 2) * round($zhuangbei->qianghua / 3) * 2 * round($zhuangbei->qianghua / 4) + 1);` | `$upls = \player\tianfu_forge_cost($tpForgePct, intval(round($zhuangbei->qianghua / 2) * round($zhuangbei->qianghua / 3) * 2 * round($zhuangbei->qianghua / 4) + 1));` |
| `:148` | 16 space | (nhu tren, them 16 space dau dong) | (nhu tren, them 16 space dau dong) |

> `:148` nam trong nhanh **da cuong hoa thanh cong** (tinh lai gia cho lan ke tiep) va `:151` moi goi lai `getplayer()`. `$tpForgePct` khai o `:39` van con trong pham vi (cung mot file, khong co ham nao bao boc) nen **khong can khai lai** — nhung phai kiem `$tpForgePct` duoc dung **truoc khi bi ghi de**, tuc khong ai gan lai no o giua. Grep 7c se bat dieu do.

**7c. Kiem chung:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l game/zbinfo.php
grep -c 'tianfu_forge_cost(' game/zbinfo.php   # Expected: 2
grep -c 'tpForgePct' game/zbinfo.php           # Expected: 3 (1 gan + 2 dung) — khong ai gan lai
grep -n 'fjls = intval' game/zbinfo.php        # Expected: van 2 (:41 va :149), KHONG co tianfu_forge_cost
grep -n 'updjsl = intval' game/zbinfo.php      # Expected: van 2 (:39 va :147), KHONG co tianfu_forge_cost
# zbinfo.php UNTRACKED -> dung snapshot cua Step 0
diff -u E:/window/Temp/_tp_base/zbinfo.php game/zbinfo.php > E:/window/Temp/_d_zbinfo.txt
echo "ADD=$(grep -c '^+[^+]' E:/window/Temp/_d_zbinfo.txt) DEL=$(grep -c '^-[^-]' E:/window/Temp/_d_zbinfo.txt)   # Expected: ADD=3 DEL=2\"
grep -rn 'tianfu_forge_cost' game/ class/      # Expected: chi zbinfo.php + class/function/tianfu.php
```

> **Vi sao chi sua 2 cho ma ca "gia hien thi" lan "tien tru that" deu dung:** `:106 $can_ls = intval($upls);` (trong nhanh `$cmd == 'upzb'`) la so Linh Thach **tru that** qua `:130 \player\changeyxb(2, $can_ls, $sid, $dblj);`, con `:283 $ls_need = intval($upls);` la so **hien thi**. Ca hai doc cung mot `$upls` cua `:40` → boc `:40` la du, khong can va **khong duoc** them cho thu ba (them nua se thanh giam 2 lan). `:148` chi phu hien thi gia cua **cap ke tiep** sau khi cuong hoa thanh cong (`:283` chay lai o cuoi request).

> **KHONG dung `class/player.php:1458`** (`$djsum = $zbqh *3+1;` → `deledjsum(1,$djsum,...)`): do la **so Cuong Hoa Thach** phai nop, tuong duong `$updjsl`. Spec §5 dong 5 chi cho giam **Linh Thach**. Giam ca thach se pha can bang vat pham.

> **CHUA TEST:** chua co ai cuong hoa that voi thien phu de so sanh Linh Thach tru truoc/sau. Step 11 L5 chi do **chieu huong** tren trang `cmd=zbinfo` (chu "Linh thạch: cần N" giam khi `forge_cost > 0`), khong do dung so va khong xac nhan duong tru tien `:130`.

- [ ] **Step 8: Cong 6 `stamina` — rut chu ky hoi The luc**

**8a. `class/player.php:336`** — them tham so thu 6 vao chu ky ham (co gia tri mac dinh nen `bak/nguhanh/game.task5.php:505` goi 5 tham so van chay):

| | CU | MOI |
|---|---|---|
| 1 dong | `function settle_stamina($ustamina, $umaxstamina, $stamina_time, $nowTs = null, $intervalSec = 300)` | `function settle_stamina($ustamina, $umaxstamina, $stamina_time, $nowTs = null, $intervalSec = 300, $staminaPct = 0)` |

**8b. `class/player.php`** — them **3 dong ngay sau** `$nowTs = ($nowTs === null) ? time() : intval($nowTs);` (`:338`):

```php
    if (function_exists(__NAMESPACE__ . '\tianfu_stamina_interval')) {
        $intervalSec = tianfu_stamina_interval($staminaPct, $intervalSec);
    }
```

> **Co guard `function_exists`** vi day la quy uoc cua chinh `class/player.php` — 8 cho dang dung dung khuon nay (`:650`, `:688`, `:715`, `:767`, `:790`, `:830`, `:937`, `:2338`), trong do `:830` la khoi Huyết Mach ma thien phu bat chuoc. `settle_stamina` la ham top-level cung namespace `player` nen goi `tianfu_stamina_interval` **khong tien to**.
>
> Dat **sau** dong chuan hoa `$nowTs` va **truoc** 3 nhanh `return` som (`$max <= 0`, `$base >= $max`, `$lastTs <= 0`): 3 nhanh do tra `'mark' => $nowTs` va **khong dung** `$intervalSec`, nen vi tri nay la don gian nhat ma khong doi hanh vi cu. Con `:362 $steps = intval(floor($elapsed / $intervalSec));` va `:371 $mark = $lastTs + ($steps * $intervalSec);` tu dong dung chu ky moi — **khong can sua them dong nao trong ham**.

**8c. `class/player.php` doc block `:321-335`** — them 1 dong vao khoi comment, **ngay sau** dong `:324` (`| Mỗi 5 phút hồi 1 điểm. \`ustamina\` là giá trị gốc tại mốc \`stamina_time\`.`), dung chen giua chung:

```
| Thien Phu cong 6 co the rut chu ky (san 30 giay) — truyen $staminaPct, xem tianfu_stamina_interval().
```

**8d. `game.php:452-457`** — them 2 tham so. **File nay dung TAB** (1 tab cho `$settle`, 2 tab cho moi tham so) — kiem `cat -A` truoc khi sua, dung de Edit doi tab thanh space:

```php
	$settle = \player\settle_stamina(
		isset($player->ustamina)    ? $player->ustamina    : 0,
		isset($player->umaxstamina) ? $player->umaxstamina : 0,
		isset($player->stamina_time) ? $player->stamina_time : null,
		$nowTs,
		300,
		isset($player->tianfu_bonus['stamina']) ? floatval($player->tianfu_bonus['stamina']) : 0
	);
```

**8e. `game.php:447`** — sua **noi dung** dong comment nam giua `/*` (`:446`) va `*/` (`:450`). Dong bat dau bang 1 TAB roi den `|` — giu nguyen TAB, dung doi thanh space:

- CU (`:447`): `	| Thể lực tự hồi: mỗi 5 phút hồi 1 điểm, không vượt quá giới hạn.`
- MOI (`:447`): `	| Thể lực tự hồi: mỗi 5 phút hồi 1 điểm (Thiên Phú công 6 có thể rút còn 210 giây), không vượt quá giới hạn.`

> Cach lam an toan: `Read game.php` (offset 445, limit 8) roi `Edit` voi `old_string` **copy nguyen van** tu ket qua Read (dong do co dau tieng Viet — go lai bang tay de sinh ky tu loi, xem memory `edit-tool-vietnamese-smart-quotes`).

**8f. `cron/stamina_recovery.php:51-57`** — them 2 tham so (file nay dung **4 space**, args 12 space). `$row['sid']` da co trong SELECT (`:35`), `$dblj` den tu `pdo.php` (`:13`):

```php
        $settle = \player\settle_stamina(
            $row['ustamina'],
            $row['umaxstamina'],
            $row['stamina_time'],
            $nowTs,
            $interval,
            \player\tianfu_bonus_pct($row['sid'], $dblj, 'stamina')
        );
```

**8g. `cron/stamina_recovery.php:16`** — sua comment, **giu nguyen** `$interval = 300;` o `:17`:

- CU (`:16`): `// Mỗi 5 phút hồi 1 điểm`
- MOI (`:16`): `// Mỗi 5 phút hồi 1 điểm — chu kỳ gốc; Thiên Phú công 6 rút theo từng nhân vật`

> `$interval = 300;` **khong doi** — no chi la gia tri **goc** truyen vao; `tianfu_stamina_interval()` rut theo tung nhan vat ben trong `settle_stamina`. Neu sua no thanh 210 thi **moi** nhan vat (ke ca khong co thien phu) deu hoi nhanh — sai spec §5 dong 6.
>
> Gia: `tianfu_bonus_pct()` ton **1 query moi nhan vat** trong cron. Vong lap da co san 1 UPDATE/dong nen them 1 SELECT la chap nhan duoc (xem `[CHOT]` o dau Task). **KHONG** sua `bak/nguhanh/*.php` — do la ban chup luu tru, khong duoc nap.

**8h. Kiem chung:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l class/player.php && "$P" -l game.php && "$P" -l cron/stamina_recovery.php
grep -n 'tianfu_stamina_interval' class/player.php          # Expected: 2 (doc block + 1 loi goi)
grep -n 'settle_stamina(' game.php cron/stamina_recovery.php # Expected: 1 ket qua moi file
grep -n '\$interval = 300;' cron/stamina_recovery.php        # Expected: van con, dong 17
grep -c 'settle_stamina' bak/nguhanh/game.task5.php          # Expected: van 2 (khong ai sua bak/)
git diff --stat -- class/player.php game.php   # 2 file nay DA track
# cron/stamina_recovery.php UNTRACKED -> snapshot
diff -u E:/window/Temp/_tp_base/stamina_recovery.php cron/stamina_recovery.php > E:/window/Temp/_d_cron.txt
echo "ADD=$(grep -c '^+[^+]' E:/window/Temp/_d_cron.txt) DEL=$(grep -c '^-[^-]' E:/window/Temp/_d_cron.txt)   # Expected: ADD=2 DEL=1\"
```

Expected: `-l` OK ca ba file.

- [ ] **Step 9: Cong 7 `breakthrough` — ty le dot pha**

**9a. `game/tupo.php`** — them 1 dong **ngay sau** `$zbsjs = intval($player->tuposjs);` (`:117`, indent 0):

```php
$tpBreak = isset($player->tianfu_bonus['breakthrough']) ? floatval($player->tianfu_bonus['breakthrough']) : 0;
```

**9b. `game/tupo.php:123`** — sua dung 1 dong:

| | CU | MOI |
|---|---|---|
| 1 dong | `$rate = max(5, intval($baseRate - $levelPenalty - $realmPenalty + $psjs + $zbsjs));` | `$rate = min(99, max(5, intval($baseRate - $levelPenalty - $realmPenalty + $psjs + $zbsjs + $tpBreak)));` |

> **[CHOT] `min(99, ...)` la thay doi hanh vi CO Y, phai ghi vao docs.** Cu thi `$rate` khong co tran: mot nhan vat cap thap cong nhieu `tuposjs` co the dat 100%+ va luon dot pha thanh cong. Spec §5 dong 7 yeu cau tran 99% de "dot pha khong bao gio chac chan". Day la **that chat** doi voi nguoi choi cu (khong phai nerf thien phu) → ghi ro trong `docs/UI_THIENPHU.md` (Task 12) va bao cao chu du an o buoc ban giao.
>
> Giu nguyen `max(5, ...)` o trong: san 5% van dung khi dan doc/phan phe day `$rate` xuong. `:129` (`breakthrough_penalty` tu dan doc) va `:133` (`level_lock`) **khong dung** — chung sua `$rate` sau khi da clamp, tuc dan doc van co the pha tran 99% xuong duoi. Do la thu tu dung: thien phu khong duoc bu lai hinh phat dan doc.

**9c. `game/tupo.php:345`** — them 1 o vao luoi `.break-bless`, **ngay sau** o `Trang bị`:

```php
                <div><span>Thiên Phú</span><b>+$tpBreak%</b></div>
```

> **Bat buoc**, khong phai trang tri: 9b da cong `$tpBreak` vao `$rate` o vong tron (`:353`), ma luoi `.break-bless` (`:341-345`) la **bang giai thich** cac thanh phan. Khong them o nay thi 5 so cong lai khong bang so trong vong → nguoi choi thay "cong sai", va day dung la loai loi ma le `feedback-preserve-supplied-data-structure` canh bao.
>
> Hien **vo dieu kien** (ke ca `+0%`): o `Milestone` o `:344` cung dang hien `+0%` khi `$psjs = 0`, nen day la le cua chinh trang nay. Loi ve layout: `.break-bless` la `grid-template-columns:repeat(3,minmax(0,1fr))` (`css/mystyle.css:6356`) → 5 o dang de **thua 1 lo o hang 2**; them o thu 6 lap day thanh 3x2. Mobile (`max-width:760px`, `:6391`) la 1 cot nen khong anh huong. **Khong can sua CSS.**
>
> O nay nam trong heredoc nen `$tpBreak` tu dong noi chuoi — khong dung `<?php ?>` o day.

**9d. Giu nguyen `game/tupo.php:209`** `\player\addplayersx("tf", 5, $sid, $dblj);` — do la **+5 diem Thien Phu cu** (cot `game1.tf`) thuong khi dot pha thanh cong. Task 12 (migration) moi quyet dinh so phan cot `tf`; Task 11 **khong** dung vao, tranh lam huong migration.

**9e. Kiem chung:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l game/tupo.php
grep -n 'tpBreak' game/tupo.php                       # Expected: 3 (1 gan o 9a + 1 dung o 9b + 1 o o .break-bless 9c)
grep -c 'min(99, max(5,' game/tupo.php                # Expected: 1
grep -c 'break-bless' game/tupo.php                   # Expected: van 1 (chi them <div> con, khong them luoi moi)
grep -n 'addplayersx("tf", 5' game/tupo.php           # Expected: van 1 (dong 209, khong doi)
# tupo.php UNTRACKED -> dung snapshot cua Step 0
diff -u E:/window/Temp/_tp_base/tupo.php game/tupo.php > E:/window/Temp/_d_tupo.txt
echo "ADD=$(grep -c '^+[^+]' E:/window/Temp/_d_tupo.txt) DEL=$(grep -c '^-[^-]' E:/window/Temp/_d_tupo.txt)   # Expected: ADD=3 DEL=1\"
```

> **CHUA TEST:** chua dot pha that de xac nhan tran 99% (can mot nhan vat co `tuposjs` lon de day `$rate` qua 100). Step 11 L5 chi do **chieu huong** (ty le sau >= ty le truoc khi `breakthrough > 0`).

- [ ] **Step 10: Lint + quet ky tu la + hoi quy TOAN BO `tests/`**

Task 11 dung vao `class/player.php` va `game.php` — hai file ma **moi** trang di qua. Vay nen hoi quy khong chi la 7 file test cua Thien Phu:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"

# 1) Lint 11 file da sua (Step 2..9 + Step 1)
for f in class/function/tianfu.php class/function/luonden.php class/player.php \
         game.php cron/stamina_recovery.php \
         game/xiulian.php game/pve.php game/boss.php game/zbinfo.php game/tupo.php \
         tests/test_tianfu_gates.php; do
  "$P" -l "$f" || { echo "LINT FAIL $f"; break; }
done

# 2) Quet ky tu lac: CJK + smart quotes. Chi duoc phep trung o cho DA biet:
#    - class/function/tianfu.php: comment tieng Viet co dau (khong phai CJK) -> khong trung
#    - game/boss.php / game/pve.php: san co comment CJK cua ban goc -> dem SO DONG truoc va sau, phai BANG NHAU (trừ boss.php: -1 dong vi 5d xoa)
for f in class/function/tianfu.php class/function/luonden.php class/player.php \
         game.php cron/stamina_recovery.php game/zbinfo.php game/tupo.php \
         tests/test_tianfu_gates.php; do
  echo "--- $f"
  grep -nP '[\x{2E80}-\x{9FFF}\x{3000}-\x{303F}]' "$f" | head -3
  grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]' "$f" | head -3
done
echo "--- game/pve.php game/boss.php (DA track -> lay nen bang git show HEAD)"
for f in game/pve.php game/boss.php; do
  echo "$f  HIENTAI=$(grep -cP '[\x{2E80}-\x{9FFF}]' "$f")  GOC=$(git show HEAD:"$f" | grep -cP '[\x{2E80}-\x{9FFF}]')"
done
echo "--- game/xiulian.php (UNTRACKED -> nen la snapshot Step 0)"
echo "game/xiulian.php  HIENTAI=$(grep -cP '[\x{2E80}-\x{9FFF}]' game/xiulian.php)  GOC=$(grep -cP '[\x{2E80}-\x{9FFF}]' E:/window/Temp/_tp_base/xiulian.php)"
echo "--- 5 file UNTRACKED con lai: doi chieu so dong + CJK voi snapshot"
for b in zbinfo tupo paihang luonden stamina_recovery; do
  case "$b" in
    luonden)          f="class/function/luonden.php" ;;
    stamina_recovery) f="cron/stamina_recovery.php" ;;
    *)                f="game/$b.php" ;;
  esac
  echo "$f  lines=$(wc -l < $f)/nen=$(wc -l < E:/window/Temp/_tp_base/$b.php)  cjk=$(grep -cP '[\x{2E80}-\x{9FFF}]' $f)/nen=$(grep -cP '[\x{2E80}-\x{9FFF}]' E:/window/Temp/_tp_base/$b.php)"
done
```

Expected muc 2: 8 file dau **khong in ra dong nao**. Hai file `pve`/`boss` va khoi snapshot: `game/xiulian.php` va `game/pve.php` phai `HIENTAI == GOC`; `game/boss.php` phai `HIENTAI == GOC - 1` (dung 1 dong CJK bi xoa o 5d). Neu `HIENTAI > GOC` o bat ky file nao → Edit/Write da chen ky tu lac (le `edit-tool-vietnamese-smart-quotes`), sua lai truoc khi chay tiep.

```bash
# 3) Hoi quy: chay MOI test thuan (khong --live) trong tests/
for t in tests/test_*.php; do
  echo "=================== $t"
  "$P" "$t" 2>&1 | tail -6
done
```

Expected: **khong co dong `FAIL`** va khong co `Fatal error`. Nhung test can `--live` (`test_huyetmach_combat`, `test_linhcan_combat`, `test_linhcan_passive`, `test_linhcan_pages`, `test_tianfu_pages`) se in `SKIP` hoac bao thieu fixture — **chấp nhận được** ở bước này, Step 11 mới chạy `--live`.

> **Bat buoc chay that, khong duoc bo qua** (le `verification-before-completion`): Task 11 sua `settle_stamina()` (moi trang goi) va `combat_power()` (Step 13). Neu `test_huyetmach_*` hoac `test_nguhanh*` FAIL sau Step 8/13 thi do la **hoi qui that**, khong phai "test cu von dieu". Dung lai va tim nguyen nhan, khong sua test cho PASS.

- [ ] **Step 11: Them phan `--live` vao CUOI `tests/test_tianfu_gates.php`**

Noi tiep **ngay sau** dong `if (!$LIVE) { exit($fails === 0 ? 0 : 1); }` cua Step 1. Dung helper cua `tests/tianfu_test_lib.php` (Task 3 Step 1) — **khong** tu viet PDO/INSERT moi:

```php
// =====================================================================
//  PHAN --live: 7 cong gan THAT vao code. Chi chay sau khi Step 3..9 xong.
//  Can: MySQL local + dev server 8091 (doi cong bang bien moi truong TP_BASE).
//  Nhan vat test = sid 'f'x32 / uid 990112 (le cua he Thien Phu, khong dung
//  fixture e2e chung de khoi lam lech test cua Huyết Mạch / Linh Căn).
// =====================================================================
require_once $ROOT . '/tests/tianfu_test_lib.php';
require_once $ROOT . '/class/encode.php';

$db      = tf_test_db();
$TESTSID = str_repeat('f', 32);
$TESTUID = 990112;
tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFGATE', 1000, 500);

// ----- L1: tianfu_bonus_pct doc DB dung cong, clamp dung tran -----
tsection('L1 tianfu_bonus_pct (DB)');
// Chup TRUOC khi seed: nhan vat tf_seed_game1() co ugj/umaxhp = 0, nen khong the assert
// "> 0" — chi co the assert "KHONG DOI" (ranh gioi spec §1: thien phu khong cong 5 chi so).
$tpPl0  = \player\getplayer($TESTSID, $db);
$tpUgj0 = intval($tpPl0->ugj);
$tpHp0  = intval($tpPl0->umaxhp);
// id 16 = Than Suat Qua Nhan, gate stamina, per_lv 0.25; q10 mult 3.50; lv 60 = tran pham.
// raw = 60*0.25*3.50 = 52.5 ; tong lv 60 -> milestone 1 (+5%) -> 55.125 -> clamp cap_percent 30.
tf_ins_owned($db, $TESTSID, 16, 10, 60, 1);
\player\player_cache_clear($TESTSID);
tassert('L1 stamina cham tran 30.0', \player\tianfu_bonus_pct($TESTSID, $db, 'stamina'), 30.0);
tassert('L1 cong la tra 0.0',        \player\tianfu_bonus_pct($TESTSID, $db, 'khong_ton_tai'), 0.0);
tassert('L1 exp_cultivate van 0.0',  \player\tianfu_bonus_pct($TESTSID, $db, 'exp_cultivate'), 0.0);
$tpPl = \player\getplayer($TESTSID, $db);
tassert('L1 getplayer hydrate stamina', $tpPl->tianfu_bonus['stamina'], 30.0);
tassert('L1 khong cong ugj',            intval($tpPl->ugj),    $tpUgj0);
tassert('L1 khong cong umaxhp',         intval($tpPl->umaxhp), $tpHp0);

// ----- L2: settle_stamina lay pct that tu DB -----
tsection('L2 settle_stamina (DB)');
$tpNow  = 1700000000;
$tpT420 = date('Y-m-d H:i:s', $tpNow - 420);
$tpPct  = \player\tianfu_bonus_pct($TESTSID, $db, 'stamina');
$tpS    = \player\settle_stamina(0, 100, $tpT420, $tpNow, 300, $tpPct);
tassert('L2 pct 30: 420s/210 = 2 buoc', $tpS['steps'], 2);
tassert('L2 moc nhich dung 420s',       $tpS['mark'],  $tpNow);
$tpS0 = \player\settle_stamina(0, 100, $tpT420, $tpNow, 300, 0);
tassert('L2 khong pct van 1 buoc',      $tpS0['steps'], 1);

// ----- L3: luyen dan cong alchemy, tran 95 van giu -----
tsection('L3 luonden_calc_success_rate');
// KHONG dat ->sid: luonden.php:337-339 chi tinh phan phe khi $sid > 0, ma sid hex
// ep kieu int = 0 nen nhanh do luon bo qua. Dung stdClass khong sid de ket qua xac
// dinh va khong can $GLOBALS['dblj'].
// floatval() boc ngoai vi $tpAlchemy la float: 50 + 30.0 = 80.0 (float), tassert dung ===.
$tpAl = new stdClass();
$tpAl->linhcan_element = '';
$tpAl->tianfu_bonus    = array('alchemy' => 30.0);
$tpR50 = array('base_success_rate' => 50);
$tpR70 = array('base_success_rate' => 70);
tassert('L3 base 50 + 30 = 80',       floatval(\player\luonden_calc_success_rate($tpAl, $tpR50)), 80.0);
tassert('L3 base 70 + 30 -> tran 95', floatval(\player\luonden_calc_success_rate($tpAl, $tpR70)), 95.0);
$tpAl->tianfu_bonus = array();
tassert('L3 khong cong: base 50 = 50', floatval(\player\luonden_calc_success_rate($tpAl, $tpR50)), 50.0);
$tpAl2 = new stdClass();
tassert('L3 khong co tianfu_bonus van chay', floatval(\player\luonden_calc_success_rate($tpAl2, $tpR50)), 50.0);

// ----- L5: HTTP doi chieu TRUOC / SAU tren 2 trang that -----
tsection('L5 HTTP xiulian + tupo');
$enc  = new \encode\encode();
$base = getenv('TP_BASE');
if (!is_string($base) || $base === '') { $base = 'http://127.0.0.1:8091'; }
// Blob cmd DUNG MOT LAN (game.php chong replay) -> encode lai moi lan goi.
$tpget = function ($plain) use ($enc, $base, $TESTSID) {
    return (string) shell_exec('curl -s ' . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain . '&sid=' . $TESTSID)));
};
// \x27 = dau nhay don trong PCRE: chuoi PHP nay la single-quote nen khong dung ' truc tiep.
$tpReadXl = function ($html) {
    // game/xiulian.php:506  put('xlexp', (totalSec / 60 * <exp_per_min>).toLocaleString(...
    return preg_match('/put\(\x27xlexp\x27, \(totalSec \/ 60 \* ([0-9.]+)\)/', $html, $m) ? floatval($m[1]) : -1.0;
};
$tpReadTp = function ($html) {
    // game/tupo.php:353  <div class="break-ring-inner"><b>NN%</b>
    return preg_match('/break-ring-inner"><b>([0-9]+)%/', $html, $m) ? intval($m[1]) : -1;
};

// L5a — exp_cultivate: xoa sach -> do TRUOC; seed id 1 q10 lv60 -> do SAU (ky vong +30%)
$db->prepare("DELETE FROM player_tianfu WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$htmlA0 = $tpget('cmd=xiulian');
tassert('L5a trang xiulian tra ve that', strlen($htmlA0) > 500, true);
$xlBefore = $tpReadXl($htmlA0);
tassert('L5a doc duoc exp_per_min', $xlBefore > 0, true);

tf_ins_owned($db, $TESTSID, 1, 10, 60, 1);
\player\player_cache_clear($TESTSID);
$htmlA1 = $tpget('cmd=xiulian');
$xlAfter = $tpReadXl($htmlA1);
tassert('L5a exp_per_min TANG', $xlAfter > $xlBefore, true);
// Hai lan round (exp_per_sec 4 so, exp_per_min 2 so) -> dung dai +/-10%, khong dung ===
tassert('L5a tang ~30% (>=1.25)', $xlAfter >= $xlBefore * 1.25, true);
tassert('L5a tang ~30% (<=1.35)', $xlAfter <= $xlBefore * 1.35, true);

// L5b — breakthrough: TRUOC roi SAU voi id 13 q10 lv60 (+30 diem %, tran 99)
$db->prepare("DELETE FROM player_tianfu WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$htmlB0 = $tpget('cmd=tupo');
tassert('L5b trang tupo tra ve that', strlen($htmlB0) > 500, true);
$tpBefore = $tpReadTp($htmlB0);
tassert('L5b doc duoc ty le vong tron', $tpBefore >= 0, true);

tf_ins_owned($db, $TESTSID, 13, 10, 60, 1);
\player\player_cache_clear($TESTSID);
$htmlB1 = $tpget('cmd=tupo');
$tpAfter = $tpReadTp($htmlB1);
tassert('L5b ty le KHONG giam', $tpAfter >= $tpBefore, true);
tassert('L5b tran 99 duoc ton trong', $tpAfter <= 99, true);
tassert('L5b co o Thien Phu trong .break-bless',
    strpos($htmlB1, '<span>Thiên Phú</span><b>+30%</b>') !== false, true);
tassert('L5b bang giai thich khong thua o', substr_count($htmlB1, '<span>Thiên Phú</span>'), 1);

// ----- L9: don dep — tra lai DB sach cho lan chay ke tiep -----
tsection('L9 don dep');
tf_cleanup($db, $TESTSID);
$cnt = $db->prepare("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?");
$cnt->execute(array($TESTSID));
tassert('L9 player_tianfu sach', intval($cnt->fetchColumn()), 0);

echo "\n" . ($fails === 0 ? "ALL PASS (thuan + live)\n" : "FAILS: $fails\n");
exit($fails === 0 ? 0 : 1);
```

> **[CHOT] Khong co `L4` trong PHP** — 7 cho gan duoc canh bang **grep tinh** (nhanh hon, khong phu thuoc DB), dat o `Step 12` truoc khi commit. Danh so nhay tu L3 sang L5 la **co y** de khop voi 7 cong cua spec §5 (cong 4 = alchemy = L3, cong 5/6/7 = forge/stamina/breakthrough; `drop` va `exp_monster` chi co grep + bam tay).

**Chay:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l tests/test_tianfu_gates.php
curl -s -o /dev/null -w "server=%{http_code}\n" http://127.0.0.1:8091/index.php   # phai la 200 truoc
TP_BASE=http://127.0.0.1:8091 "$P" tests/test_tianfu_gates.php --live
```

Expected: `ALL PASS (thuan + live)` — **29 PASS thuan + 25 PASS live = 54 PASS, 0 FAIL** (L1 6 + L2 3 + L3 4 + L5a 5 + L5b 6 + L9 1).

> Neu `L5a trang xiulian tra ve that` FAIL voi `body=3 byte`: blob cmd da bi dung hoac `endtime` cua nhan vat test het han → chay lai `tf_cleanup` + `tf_seed_game1` (script tu lam o dau phan `--live`), hoac dev server chet (kiem `curl` o tren). **Khong** sua nguong 500 byte de "cho qua".
>
> **CHUA TEST / khong the tu dong hoa o buoc nay:** 3 cong `drop` (pve/boss), `forge_cost` (zbinfo), `exp_monster` (pve/boss) **khong co** test `--live`. Ly do: do ty le roi can hang tram lan danh quai (le `test_huyetmach_combat.php --live` E.3 phai seed quai tam thoi + quay 60 don), va `zbinfo` can mot trang bi that cua nhan vat test. **Bam tay bat buoc** (le `feedback-careful-server-changes`) — xem bang o Step 12.

- [ ] **Step 12: Grep tinh canh 7 cho gan + bam tay + commit A**

**12a. Grep tinh** (7 cong → 7 cho gan, khong thieu khong thua):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
echo "cong 1 exp_cultivate : $(grep -c 'tp_exp_bonus' game/xiulian.php)"                       # Expected: 2
echo "cong 2 exp_monster : $(grep -c 'tpExpPct' game/pve.php) / $(grep -c 'tpExpPct' game/boss.php)"   # Expected: 2 / 2
echo "cong 3 drop        : $(grep -c 'tianfu_luck_gate(' game/pve.php) / $(grep -c 'tianfu_luck_gate(' game/boss.php)"  # Expected: 3 / 3
echo "cong 4 alchemy     : $(grep -c 'tpAlchemy' class/function/luonden.php)"                  # Expected: 2
echo "cong 5 forge_cost  : $(grep -c 'tianfu_forge_cost(' game/zbinfo.php)"                     # Expected: 2
echo "cong 6 stamina     : $(grep -c 'staminaPct' class/player.php) / $(grep -c 'tianfu_bonus_pct' cron/stamina_recovery.php) / $(grep -c 'tianfu_bonus\[.stamina.\]' game.php)"  # Expected: 3 / 1 / 1
echo "cong 7 breakthrough: $(grep -c 'tpBreak' game/tupo.php)"                                 # Expected: 3
echo "tfxy da go         : $(grep -rn 'tfxy' game/ class/function/ cron/ | wc -l)"             # Expected: 0
echo "helper du 4        : $(grep -c '^    function tianfu_' class/function/tianfu.php)"       # Expected: >= 4 (khong dem thieu)
```

> **KHONG dung `grep -c 'tfxy' class/player.php`** — `class/player.php` van **giu** cot `tfxy` trong `player_allowed_columns()` va `var $tfxy;` cho den Task 12 (migration). Chi `game/` (noi dung nguong roi) la phai sach.

**12b. Bam tay trong browser that** (le `feedback-careful-server-changes`; **khong** dung browser-use MCP). Sinh token bang `_tp_url.php` cua Task 7 (moi lan vao trang phai sinh token **moi**):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
SID=ffffffffffffffffffffffffffffffff
for c in xiulian tupo paihang zhuangtai; do
  echo "$c -> http://127.0.0.1:8091/game.php?cmd=$("$P" _tp_url.php "cmd=$c&sid=$SID" 2>/dev/null)"
done
```

Mo tung URL va kiem **bang mat**:

| Trang | Phai thay | Neu khong thay |
|---|---|---|
| `xiulian` | toc do linh khí/giay **cao hon** luc chua seed thien phu | Step 3 sai cho (kiem `$exp_per_sec` co nhan `(1 + $tp_exp_bonus / 100)`) |
| `tupo` | luoi `.break-bless` co **6 o**, o cuoi `Thiên Phú +30%`; vong tron = tong 6 o (hoac 99 neu cham tran) | Step 9c thieu → nguoi choi thay "cong sai" |
| `paihang` (tab Chiến lực) | bang xep hang **van hien**, khong trang trang | Step 13a/13b sai (chay sau, co the bo qua o Step 12) |
| `zhuangtai` | o `🧬 Thiên phú` **van hien so cu** (Step 13 moi doi) | — |

Ghi ket qua bam tay vao ban giao (le `flag-untested-in-handoff`): cai nao **chua** bam thi ghi CHUA, khong ghi "xong".

**12c. Commit A** — helper + 7 cong + test. **Cam `git add -A` / `git add .`** (repo root co 300+ `shot_*.png` lac va 1 sua doi `tongquan.php` chua lien quan):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
git add class/function/tianfu.php class/function/luonden.php class/player.php \
        game.php cron/stamina_recovery.php \
        game/xiulian.php game/pve.php game/boss.php game/zbinfo.php game/tupo.php \
        tests/test_tianfu_gates.php
git diff --cached --name-status | head -20      # phai dung 11 dong, khong co tongquan.php / shot_*.png
git commit -m "feat(tianfu): gan 7 cong vao code — exp x2, drop, alchemy, forge, stamina, breakthrough (Task 11A)"
```

> `game/tupo.php:209` (`addplayersx("tf", 5, ...)`) van con → commit message **khong** noi "bo thuong tf cu"; do la viec cua Task 12.

- [ ] **Step 13: Chien luc + 2 o trong trang Trang thai**

**13a. `class/player.php` `combat_power()` — mang `$weights` (`:412-424`)**

XOA 3 dong `'tf' => 50,` (`:418`), `'tfxy' => 10,` (`:419`), `'tfsb' => 10,` (`:420`); THEM 1 dong `'tianfu_power' => 1,` ngay sau `'uxx' => 25,`. Mang tro thanh:

```php
    $weights = array(
        'ugj'                     => 10,
        'ufy'                     => 8,
        'ubj'                     => 30,
        'uxx'                     => 25,
        'tianfu_power'            => 1,
        'thechat_quality'         => 200,
        'thechat_awaken_stage'    => 500,
        'huyetmach_quality'       => 250,
        'huyetmach_awaken_stage'  => 600
    );
```

> **Trong so `1`, khong phai `50`:** `game1.tianfu_power` **da la tong hop** — `tianfu_power_of($q,$lv) = $q*150 + $lv*8` cong don qua moi Phu Vi (Task 3). Nhan them 50 nua se phong chien luc len hang chuc lan va day het bang xep hang. `isset($player->$col)` o `:429` tu dong coi `tianfu_power` chua hydrate = 0, khong can `??`.

**13b. `game/paihang.php` — chi thay 3 dong trong `tcg_calc_chien_luc_rank()` (`:10-21`)**

XOA `:17` `+ intval($rankPlayer->tf) * 50`, `:18` `+ intval($rankPlayer->tfxy) * 10`, `:19` `+ intval($rankPlayer->tfsb) * 10`; THEM 1 dong thay cho ca ba:

```php
        + intval(isset($rankPlayer->tianfu_power) ? $rankPlayer->tianfu_power : 0)
```

Ham tro thanh (chu y dau `+` o dau dong va **khong co** dau `;` cho den dong cuoi):

```php
function tcg_calc_chien_luc_rank($rankPlayer)
{
    // LECH co y: ham nay thieu 4 trong so thechat_*/huyetmach_* ma \player\combat_power()
    // (class/player.php:410) co. Giu nguyen de bang xep hang khong nhay bac — xem Task 12 handoff.
    return intval($rankPlayer->ugj) * 10
        + intval($rankPlayer->ufy) * 8
        + intval($rankPlayer->umaxhp)
        + intval($rankPlayer->ubj) * 30
        + intval($rankPlayer->uxx) * 25
        + intval(isset($rankPlayer->tianfu_power) ? $rankPlayer->tianfu_power : 0)
        + intval(isset($rankPlayer->shenzhuang_power) ? $rankPlayer->shenzhuang_power : 0);
}
```

> **[CHOT — CAU HOI CHO CHU DU AN, khong tu quyet.]** Do that khi tra `game/paihang.php:10-21` vs `class/player.php:409-433`: `tcg_calc_chien_luc_rank()` la **ban sao cu** cua `combat_power()` — giong 8 trong so dau nhung **thieu** `thechat_quality*200`, `thechat_awaken_stage*500`, `huyetmach_quality*250`, `huyetmach_awaken_stage*600`. Tuc **bang xep hang Chien luc hien dang bo qua Nhuc Than va Mach Huyet** — loi co san, khong phai do Task 11. Chi co **1** cho goi (`:67`), va `:57 $rankPlayer = \player\getplayer($rowSid, $dblj);` tra ve dung doi tuong ma `combat_power()` nhan — nen **co the** xoa ca ham va goi `\player\combat_power($rankPlayer)`.
>
> - **Phuong an A (plan dang lam — toi thieu, dung spec §1.12 "thay trong so"):** chi doi 3 dong. Bang xep hang van nhat quan voi chinh no truoc/sau, khong nhay bac vi Nhuc Than/Mach Huyet.
> - **Phuong an B (sua luon loi cu):** xoa ham, goi `\player\combat_power()`. Mot cong thuc duy nhat, bang xep hang khop con so dang hien o `zhuangtai`/`xiulian`. **Gia:** thu tu xep hang **doi ngay lap tuc** cho moi nhan vat co Nhuc Than/Mach Huyet — nguoi choi se thay va hoi.
>
> Plan chon **A** vi B mo rong pham vi ra ngoai spec (le `feedback-khong-mo-rong-pham-vi-sua`). **Phai hoi chu du an o buoc ban giao Task 12**, kem bang so cua 13d.

**13c. `game/zhuangtai.php` — 2 o trong luoi `.profile-stat` (`:418-419`)**

Chen 6 dong **ngay truoc** `$html = <<<HTML` (`:359`), sau `$ztAvatar = \player\player_avatar_img($player->usex);` (`:357`):

```php
// Thien Phu: thay 2 o tf/tfxy/tfsb cu (3 cot nay chet tu Task 12)
$tpIcon   = !empty($player->tianfu_icon) ? $player->tianfu_icon : '☯';
$tpColor  = !empty($player->tianfu_color) ? $player->tianfu_color : '#9e9e9e';
$tpMaster = ($player->tianfu_name !== '')
    ? $player->tianfu_name . ' · ' . \player\tianfu_quality_name(intval($player->tianfu_quality))
    : 'Chưa khai phú';
$tpSlots  = intval($player->tianfu_bonus['activated_count']) . ' / ' . count(\player\tianfu_slot_defs());
```

Roi thay **2 dong** `:418-419` (thanh `:424-425` sau khi chen 6 dong) bang:

```php
        <div class="profile-stat"><span>$tpIcon Chủ phú</span><strong style="color:$tpColor">$tpMaster</strong></div>
        <div class="profile-stat"><span>🎖 Phú Vị</span><strong>$tpSlots</strong></div>
```

> - **`Read` file truoc, copy nguyen van** 2 dong cu vao `old_string` cua Edit — chung co emoji **khong nam trong BMP** (`🧬` U+1F9EC, `🍀` U+1F340) va co dau tieng Viet. Go lai bang tay la cach de nhat de hong file (le `edit-tool-vietnamese-smart-quotes`).
> - `☯` (U+262F) va `🎖` (U+1F396) **da co san trong `game/zhuangtai.php`** — kiem bang `grep -oP '[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]' game/zhuangtai.php | sort -u`. Dung emoji da co de tranh mot ky tu chua ai kiem chung render.
> - **Khong `htmlspecialchars`** `$tpColor`/`$tpMaster`: ca hai den tu `class/config/tianfu_config.json` (file trong repo, khong phai nhap lieu cua nguoi dung), dung le cua chính trang nay o `:425 $lcColor = $player->linhcan_color;` va `:428 $lcIcon = ...` — Linh Căn cung noi thang vao `style="..."` ma khong escape. Neu sau nay co admin panel sua config thi phai escape lai **ca hai he**, khong rieng Thien Phu.
> - `$tpMaster` dai (`Ngộ Tính Nghịch Thiên · Đạo Phẩm` = 27 ky tu). O cu `🍀 Vận may / Né tránh` con dai hon nen **khong** sua CSS; nhung **phai nhin anh that** o 13d de xac nhan chu khong tran o.

**13d. Do that + kiem chung**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l class/player.php && "$P" -l game/paihang.php && "$P" -l game/zhuangtai.php
grep -c "tianfu_power'  *=> 1" class/player.php                          # Expected: 1
grep -n "'tf' *=> 50" class/player.php                                   # Expected: 0 ket qua
grep -c 'rankPlayer->tf)\|rankPlayer->tfxy\|rankPlayer->tfsb' game/paihang.php   # Expected: 0
grep -n 'player->tf</strong>' game/zhuangtai.php                         # Expected: 0 ket qua
grep -nP '[\x{2E80}-\x{9FFF}]' class/player.php game/paihang.php game/zhuangtai.php | head -3   # Expected: khong in gi
```

Roi **do chien luc that** de co so lieu bao cao chu du an (script tam, **khong commit**, dat ngoai repo):

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
cat > E:/window/Temp/_tp_power.php <<'PHPEOF'
<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$W = 'D:/XXJserver/phpstudy_pro/WWW';
require_once $W . '/class/player.php';
$dsn = "mysql:host=localhost;dbname=game;charset=utf8"; $sqlname = null; $sqlpass = null;
include $W . '/pdo.php';
$db = new PDO($dsn, $sqlname, $sqlpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$rows = $db->query("SELECT * FROM game1 ORDER BY ulv DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
printf("%-16s %5s %14s %14s %14s %8s\n", 'uname', 'ulv', 'CU(tf*50..)', 'MOI(combat)', 'MOI(paihang)', 'lech');
foreach ($rows as $r) {
    $iv = function ($k) use ($r) { return isset($r[$k]) ? intval($r[$k]) : 0; };
    $old = $iv('umaxhp') + $iv('ugj')*10 + $iv('ufy')*8 + $iv('ubj')*30 + $iv('uxx')*25
         + $iv('tf')*50 + $iv('tfxy')*10 + $iv('tfsb')*10 + $iv('shenzhuang_power');
    $p = new stdClass();
    foreach ($r as $k => $v) { $p->$k = $v; }
    $new   = \player\combat_power($p);
    $newPh = $old - $iv('tf')*50 - $iv('tfxy')*10 - $iv('tfsb')*10 + $iv('tianfu_power');
    $d = ($old > 0) ? round(($newPh - $old) * 100 / $old, 1) . '%' : '-';
    printf("%-16s %5d %14s %14s %14s %8s\n", $r['uname'], $iv('ulv'),
        number_format($old, 0, ',', '.'), number_format($new, 0, ',', '.'),
        number_format($newPh, 0, ',', '.'), $d);
}
PHPEOF
"$P" E:/window/Temp/_tp_power.php
rm -f E:/window/Temp/_tp_power.php
```

Expected: bang 10 dong. **Chep nguyen bang vao ban giao** — no la bang chung cho cau hoi 13b va cho muc "chien luc tut" o Task 12. `MOI(combat)` se **lon hon** `CU` o nhan vat co Nhuc Than/Mach Huyet (4 trong so moi); `MOI(paihang)` se **nho hon** `CU` vi `tianfu_power` = 0 cho den khi migration chay va nguoi choi khai phu.

> **Day la thay doi nguoi choi nhin thay ngay:** moi nhan vat **mat** `tf*50 + tfxy*10 + tfsb*10` chien luc cho den khi ho khai phu va luyen thien phu. Voi nhan vat cap cao (`tf` 500+) do la **hang chuc nghin** diem. Spec §1.12 da biet va chap nhan ("Migration phai thay trong so"), nhung **van phai bao truoc** — le `feedback-careful-server-changes`.

> **CHUA TEST:** chua chup anh trang `zhuangtai` sau 13c. **Bat buoc** mo browser that (`start chrome "http://127.0.0.1:8091/game.php?cmd=<token>"`, token sinh bang `_tp_url.php` cua Task 7) va nhin 2 o moi o **ca 390px lan 840px** — le `verify-visually-not-just-by-metrics`. Anh chup luu `docs/img/thienphu/zhuangtai-390.png` va commit o Step 14.

- [ ] **Step 14: Commit B — chien luc + trang thai**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
git add class/player.php game/paihang.php game/zhuangtai.php \
        docs/img/thienphu/zhuangtai-390.png docs/img/thienphu/zhuangtai-840.png
git diff --cached --name-status | head -10    # dung 5 dong; KHONG co tongquan.php, khong co shot_*.png
git commit -m "feat(tianfu): chien luc dung tianfu_power, trang Trang thai hien chu phu + Phu Vi (Task 11B)"
```

> **Cam `git add -A` / `git add .`.** Repo root dang co **300+ `shot_*.png`** lac va **`tongquan.php` da bi sua san** (noi dung Linh Căn: chu thich drop boss `:614` va cot nhiem vu tuan `:683-690`) — **khong lien quan** Thien Phu. `git add` theo **duong dan cu the** o moi commit, va `git diff --cached --name-status` truoc khi `git commit`.

- [ ] **Step 15: `tongquan.php` — muc Thien Phu (ban do game cho chu server)**

`[CHOT]` **BUOC NAY CAN CHU DU AN DUYET TRUOC KHI COMMIT.** Ly do: `tongquan.php` **dang co san 1 sua doi chua commit** cua Linh Căn (`git diff -- tongquan.php` = 4 dong, 2 khoi). Commit file nay se **kep theo** sua doi do. Thuc hien theo thu tu:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
git diff -- tongquan.php      # in ra cho chu du an XEM, giai thich: day la noi dung Linh Căn co san, khong phai cua Task 11
```

**Dung lai, hoi:** "`tongquan.php` đang có 4 dòng sửa chưa commit về Linh Căn (mô tả drop boss + cột nhiệm vụ tuần). Commit mục Thiên Phú sẽ gộp luôn phần đó. Gộp chung, hay tách thành 2 commit?" — **khong tu quyet** (le `feedback-khong-tu-thu-hep-pham-vi` + quy tac hanh dong kho hoan tac).

**15a. Chen muc Thien Phu** vao `tongquan.php` **ngay sau** dong `}` dong lai nhanh `else` cua khoi Mạch Huyết (`:445`), **truoc** `$petAllC = tq_cfg('pet_config');` (`:447`). Khuon lay nguyen tu khoi `mach-huyet` (`:402-445`):

```php
/* ================= THIỆN PHÚ (Phú Các) ================= */
$tpAll = tq_cfg('tianfu_config');
if (is_array($tpAll)) {
    $tpGates  = (array)(isset($tpAll['gates'])  ? $tpAll['gates']  : array());
    $tpGroups = (array)(isset($tpAll['groups']) ? $tpAll['groups'] : array());
    $tpBq     = (array)(isset($tpAll['break_quality']) ? $tpAll['break_quality'] : array());
    $tpLv     = (array)(isset($tpAll['lv_cost'])  ? $tpAll['lv_cost']  : array());
    $tpPill   = (array)(isset($tpAll['open_pill']) ? $tpAll['open_pill'] : array());

    $rowsT5 = array();
    foreach ((array)(isset($tpAll['talents']) ? $tpAll['talents'] : array()) as $e) {
        $g  = isset($tpGates[$e['gate']])   ? $tpGates[$e['gate']]   : array('name' => $e['gate'], 'unit' => '');
        $gr = isset($tpGroups[$e['group']]) ? $tpGroups[$e['group']] : array('name' => tq_h($e['group']));
        $sub = isset($e['per_lv_sub']) ? ' (không phải chủ phú: +' . tq_n($e['per_lv_sub']) . ')' : '';
        $rowsT5[] = array($e['icon'] . ' ' . tq_show($e['name']), tq_show($e['short']),
            tq_show($gr['name']), tq_show($g['name']) . ' (' . tq_show($g['unit']) . ')',
            '+' . tq_n($e['per_lv']) . '/cấp' . $sub, tq_n($e['weight']), tq_cut($e['desc'], 90));
    }

    $rowsQ5 = array();
    foreach ((array)(isset($tpAll['qualities']) ? $tpAll['qualities'] : array()) as $e) {
        $q  = intval($e['q']);
        $bq = isset($tpBq[$q]) ? $tpBq[$q] : null;   // dot pham q -> q+1, nen q10 khong co dong
        $rowsQ5[] = array(tq_dot($e['color']) . $e['icon'] . ' ' . tq_show($e['name']),
            tq_show($e['key']), 'cấp ' . tq_n($e['lv_cap']), 'x' . tq_n($e['mult']),
            tq_n($e['weight']) . '%',
            $bq ? (tq_n($bq['tf']) . ' điểm Thiên Phú + ' . tq_n($bq['linh_thach']) . ' Linh Thạch') : '— (trần)',
            $bq ? tq_realm_req($bq['ulv_require']) : '—');
    }

    $rowsG5 = array();
    foreach ($tpGates as $k => $e) {
        $rowsG5[] = array(tq_h($k), tq_show($e['name']), tq_show($e['unit']), tq_cut($e['desc'], 110));
    }

    $rowsM5 = array();
    foreach ((array)(isset($tpAll['milestones']) ? $tpAll['milestones'] : array()) as $e) {
        $rowsM5[] = array(tq_show($e['name']), tq_n($e['total_lv']), '+' . tq_n($e['bonus_percent']) . '%');
    }

    $rowsS5 = array();
    foreach ((array)(isset($tpAll['slots']) ? $tpAll['slots'] : array()) as $e) {
        $rowsS5[] = array(tq_n($e['i']), tq_show($e['name']),
            intval($e['ulv']) > 0 ? 'cấp ' . tq_n($e['ulv']) : 'mở sẵn',
            tq_realm_req($e['ulv']), tq_cut($e['hint'], 80));
    }

    tq_sec('nuoi-duong', 'thien-phu', '☯', 'Thiên Phú (18 phú · 10 phẩm · 7 công)',
        count($rowsT5) + count($rowsQ5) + count($rowsG5) + count($rowsM5) + count($rowsS5),
        'Lớp vận khí & tốc độ: khai Phú Đan nhận ngẫu nhiên 1 trong 18 thiên phú, xếp vào 4 Phú Vị. Thiên phú KHÔNG cộng Công/Thủ/Máu/Bạo/Hút — chỉ can thiệp tỉ lệ và giá.',
        array('UI: game/tianfu.php (cmd=tianfu) · game/tianfu_collection.php (cmd=tianfu_collection) · game/tianfu_open.php (cmd=tianfu_open)',
            'Số liệu: player_tianfu (sid, tianfu_id, quality, lv, slot) + player_tianfu_log',
            'Bản sao trong game1: tianfu_id / tianfu_quality / tianfu_lv = Phú Vị 1 (chủ phú); tianfu_power = chiến lực tổng',
            'Trần mọi công: ' . tq_n($tpAll['cap_percent']) . '% — cộng dồn từng thiên phú xong mới kẹp một lần',
            'Công "Ngưỡng rơi đồ" NHÂN TIẾP sau Huyết Mạch: 1,15 x 1,30 = +49,5% danh nghĩa; ngưỡng 120 thật tế thành 179 (+49,17%) vì hai lần làm tròn',
            'Công "Đột phá" có trần mềm 99% — bản cũ KHÔNG có trần, đây là thay đổi hành vi',
            'Khai Phú: ' . tq_show($tpPill['name']) . ' (djid ' . tq_n($tpPill['djid']) . ') — migration hoàn 7 cột thiên phú cũ thành 1 viên'),
        tq_searchable('tbl-tp', array('Thiên phú', 'Viết tắt', 'Nhóm', 'Công', 'Mỗi cấp', 'Trọng số khai', 'Mô tả'), $rowsT5,
            '<b>18 thiên phú:</b> luyện mỗi cấp tốn ' . tq_n($tpLv['tf']) . ' điểm Thiên Phú + ' . tq_n($tpLv['linh_thach_base'])
            . ' Linh Thạch (cộng ' . tq_n($tpLv['linh_thach_per_lv']) . '/cấp). Luyện luôn thành công — chỉ phẩm chất mới cần đột phá.')
        . tq_searchable('tbl-tpq', array('Phẩm', 'Khóa', 'Trần cấp', 'Hệ số', 'Tỉ lệ khai', 'Đột phá lên phẩm kế', 'Cần cảnh giới'), $rowsQ5, '<b>10 phẩm chất:</b>')
        . tq_searchable('tbl-tpg', array('Khóa', 'Công', 'Đơn vị', 'Ý nghĩa'), $rowsG5, '<b>7 công:</b>')
        . tq_searchable('tbl-tpm', array('Mốc', 'Tổng cấp 4 Phú Vị', 'Nhân chỉ số'), $rowsM5, '<b>Mốc tổng cấp:</b>')
        . tq_searchable('tbl-tps', array('Phú Vị', 'Tên', 'Mở khi', 'Cảnh giới', 'Ghi chú'), $rowsS5, '<b>4 Phú Vị:</b>'));
} else {
    tq_sec('nuoi-duong', 'thien-phu', '☯', 'Thiên Phú (chưa cài đặt)', 0,
        'class/config/tianfu_config.json chưa tồn tại — Task 1 chưa chạy.',
        array('Chưa deploy'), '');
}
```

> - **Ten bien hau to `5`** (`$rowsT5`, `$rowsQ5`, ...): `tongquan.php` la **mot file tuong duy nhat**, `$rows`/`$rowsT`/`$rowsM`/`$rows2`/`$rows3` da bi cac khoi truoc ghi de (`:305`, `:368`, `:429`, `:680`). Trung ten la mat du lieu cua khoi khac.
> - `$e['icon']` **khong** qua `tq_show()` — dung le cua khoi Mạch Huyết `:420` (`tq_dot($e['color']) . $e['icon'] . ' ' . tq_show($e['name'])`). `tq_show()` goi `tq_txt()` co `strip_tags`, van giu emoji, nhung khoi goc viet vay thi cu viet vay cho nhat quan.
> - `tq_realm_req()` (`:89`) doi nguong `ulv` thanh ten canh gioi that (86 canh, `class/rangesjj.php`) — dung cho ca cot `break_quality.ulv_require` lan `slots.ulv`.
> - **Khong them `$GLOBALS['TQ_GROUPS']`** (`:109-116`): nhom `nuoi-duong` da dang ky, va nav tu sinh tu `TQ_SECS` (`:117-122`) nen **khong** co danh sach nav rieng de sua.
> - Doc `tongquan.php` **truoc** khi chen (le `feedback-read-full-source-before-cloning`): neu khoi `mach-huyet` da doi so dong thi chen theo **noi dung** (`}` dong nhanh `else` + dong trong + `$petAllC = tq_cfg('pet_config');`), khong theo so dong.

**15b. Kiem that trong browser** — `tongquan.php` **khong** di qua `game.php`/`?cmd=`, no la trang doc lap:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P="D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe"
"$P" -l tongquan.php
curl -s -o E:/window/Temp/_tq.html -w "http=%{http_code} bytes=%{size_download}\n" http://127.0.0.1:8091/tongquan.php
grep -c 'thien-phu\|tbl-tpq\|tbl-tpg\|tbl-tpm\|tbl-tps' E:/window/Temp/_tq.html   # Expected: >= 6
grep -o 'Thiên Phú (18 phú · 10 phẩm · 7 công)' E:/window/Temp/_tq.html | head -1  # Expected: 1 ket qua
grep -c '<tr>' E:/window/Temp/_tq.html                                              # ghi lai so dong TONG de so sanh truoc/sau
rm -f E:/window/Temp/_tq.html
```

Expected: `http=200`, `bytes` lon hon **100000** (trang nay rat dai; neu nho hon thi `tq_cfg('tianfu_config')` tra `null` va nhanh `else` dang chay — kiem lai `class/config/tianfu_config.json` ton tai).

**Mo browser that** (`start chrome "http://127.0.0.1:8091/tongquan.php"`) va bam: (1) nav nhom "Nuôi dưỡng nhân vật" co the "Thiên Phú" voi dem **43** (18 thien phu + 10 pham + 7 cong + 4 moc + 4 Phu Vi — con so nay la `count()` that, dem lai bang tay truoc khi ghi vao ban giao), (2) 5 bang deu co o "Tìm trong bảng này..." va go thu `Ngộ Tính` / `Đạo Phẩm` / `stamina` loc duoc, (3) sort cot "Trọng số khai" va "Hệ số" dung thu tu so (khong phai chuoi). Le `feedback-table-interactivity`: bang ma khong loc/sort duoc la **chua xong**.

> **CHUA TEST cho den khi 15b chay:** con so dem ở nav, kha nang loc/sort cua 5 bang, va cach `.tbl-wrap` hien thi khi ten thien phu dai (`Cơ Duyên Thâm Tự Hải` = 20 ky tu) — ca ba deu phai **nhin anh**, khong suy ra tu grep.

**15c. Commit C** — **chi sau khi chu du an tra loi cau hoi o dau Step 15.**

Neu chu du an chon **gop chung**:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
git add tongquan.php
git diff --cached --name-status          # phai dung 1 dong: A/M tongquan.php
git commit -m "docs(tongquan): muc Thien Phu 18 phu / 10 pham / 7 cong; cap nhat mo ta boss + nhiem vu tuan Linh Căn"
```

Neu chon **tach 2 commit**: commit noi dung Linh Căn truoc (`git add tongquan.php` + `git commit -m "docs(tongquan): cap nhat mo ta drop boss Linh Căn va cot nhiem vu tuan"` — **khong the**, vi ca hai nam chung mot file va `git add` theo file). Tach that su can `git add -p tongquan.php` (interactive — **khong dung duoc** trong moi truong nay). **Bao lai cho chu du an:** tach doi hoi ho tu chay `git add -p`, hoac chap nhan gop.

> `git commit --amend` **cam** (le git safety). Neu commit sai noi dung: tao commit **moi** sua lai.


---

<!--PART12-->

### Task 12: CAT CODE CU + MIGRATION + TAI LIEU + SO NO CHU DU AN

**Files:**
- Create: `sql/migrate_tianfu.php` (co `--dry-run` / `--apply` / `--sid=`)
- Create: `tests/test_tianfu_migration.php`
- Create: `docs/UI_THIENPHU.md` (12 muc, khung `docs/UI_HUYETMACH.md`)
- Create: `docs/DEPLOY_PRODUCTION_THIENPHU.md` (runbook — **viet ra, KHONG chay**)
- Modify: `css/mystyle.css` (xoa 425 dong `.talent-*`, do that nam o `4062-4486`)
- Modify: `class/player.php:576-580` (xoa 5 dong — **CHI sau khi chu du an chot phuong an o Step 1**)
- Modify: `docs/UI_HUYETMACH.md` (1 dong cross-reference o muc "No con lai")

**Interfaces:**
- Consumes: `tianfu_recalc_mirror($sid,$dblj)` va `tianfu_log($sid,$kind,$tianfu_id,$from_q,$to_q,$detail,$dblj)` (Task 3); `adddj($sid,$djid,$djsum,$dblj)` (`class/player.php:1305`); `player_cache_clear($sid)` (`class/player.php:392`); 2 bang `player_tianfu` / `player_tianfu_log` (Task 2); `tests/tianfu_test_lib.php` (Task 3); daoju `114` (Task 4).
- Produces: **khong them ham nao vao game**. `sql/migrate_tianfu.php` la script chay 1 lan. `docs/UI_THIENPHU.md` tro thanh **nguon chinh** cua he (dung nhu `docs/UI_HUYETMACH.md` voi Mach Huyet) — moi lan sua Thien Phu sau nay phai cap nhat no.

#### 12.0 Vi sao task nay nam cuoi, va vi sao no khong phai "viet document cho xong"

Ba ly do, dung khuon `2026-10-09-huyetmach-mang-mach.md` Task 12.0:

1. **Task 11 da bac bo / sua mot phan spec.** Cong 7 (`breakthrough`) them tran `min(99,...)` ma code cu **khong co** (spec §5 chi noi "tran mem 99", khong noi day la thay doi hanh vi co san). Cong 3 (`drop`) nhan tiep Huyết Mạch thanh **+49.5% danh nghia / +49.17% that** (hai lan `intval(round())`). `paihang.php` van giu cong thuc chien luc **rieng** va thieu 4 trong so Nhuc Than/Mach Huyet. Ba dieu nay phai duoc chep ve `docs/UI_THIENPHU.md` de lan sau khong ai "thiet ke lai dung theo spec cu".
2. **Mot so quyet dinh CO Y khong lam**: boss thien phu rieng (phase 2), `luonden.php:898` (ty le roll that) khong duoc sua, 7 cot `game1.tf*` khong DROP, `upplayerlv()` van cap 6 cot cu moi lan len cap. Chung khong phai bug — chung la **no chu du an phai chot**.
3. **Production chua migration** — 4 cot `game1` + 2 bang moi + no schema sid 15 bang (`docs/DEPLOY_PRODUCTION_NGUHANH.md`). Khong co runbook thi lan sau bo qua mat.

> **NGUYEN TAC CUA TASK NAY:** Step 1 co mot cau hoi **[CHOT]** bat buoc. **Khong duoc** chay `--apply` (Step 6) khi chua co cau tra loi. Moi con so trong task nay deu **da do that tren DB local ngay 2026-10-14** — neu ban chay o thoi diem khac va ra so khac, **dung tu sua so trong plan**, hay ghi so moi vao `docs/UI_THIENPHU.md` va danh dau lech.

- [ ] **Step 1: [CHOT] Do thiet hai cua viec xoa `class/player.php:576-580` — DUNG LAI HOI CHU DU AN**

**1a. Van de spec chua thay.** Spec §6 dong 7 ghi "`class/player.php:576-580` | **XOA** 5 dong cong chi so tu 7 o", va §7 hoan 7 cot ve `tf`. Spec **khong he nhac** den `upplayerlv()` (`class/player.php:1161-1215`): ham nay **moi lan len cap** deu cong them vao 6 cot cu dua theo `shenfen`:

```
class/player.php:1169-1177   shenfen==1 -> $zs=1 ; ==2 -> $xs=1 ; ==3 -> $ys=1
class/player.php:1189-1194   $rangesbj/$rangesxx/$rangesxy/$tfhp1/$tffy1/$tfgj1  (gia tri 1..5 moi cap)
class/player.php:1199-1215   UPDATE game1 SET ... tfbj = tfbj + :bj, tfxx = tfxx + :xx,
                             tfxy = tfxy + :xy, tfhp = tfhp + :tfhp, tffy = tffy + :tffy, tfgj = tfgj + :tfgj ...
```

Nghia la `:576-580` **khong phai code chet** — no la **noi duy nhat** bien diem Than Phan thanh chi so that (`ugj += tfgj*5`, `ubj += tfbj*1.5`, `uxx += tfxx*2`, `umaxhp += tfhp*10`, `ufy += tffy*5`). Xoa no = **Than Phan ngung cho chi so vinh vien**, vi 7 cong cua he moi (`exp_cultivate / exp_monster / drop / alchemy / forge_cost / stamina / breakthrough`) **khong co cong nao** tra lai `ugj/ubj/uxx/umaxhp/ufy`.

**1b. Do that.** Chep nguyen khoi duoi thanh `E:/window/Temp/_tp_legacy.php` roi chay (**khong** chay `php -r` inline qua Git Bash — shell lam hong byte tieng Viet, le da ghi o `docs/UI_HUYETMACH.md` §9):

```php
<?php
// Do tac dong cua viec xoa class/player.php:576-580 + hoan diem tf (spec §6, §7)
$ROOT = 'D:/XXJserver/phpstudy_pro/WWW';
include $ROOT . '/pdo.php';
$db = create_pdo_connection($dbhost, $dbname, $sqlname, $sqlpass);

$cols = array('tf','tfgj','tfbj','tfxx','tfhp','tffy','tfxy','tfsb');
$sql = "SELECT sid, uname, ulv, shenfen, ugj, ufy, ubj, uxx, umaxhp, "
     . implode(',', $cols) . " FROM game1 ORDER BY ulv DESC";
$rs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

printf("%-10s %-14s %5s %2s %8s %8s %8s %8s %8s | %6s %6s %6s %6s %6s %6s %6s %6s | %8s %9s\n",
    'sid', 'uname', 'ulv', 'sf', 'ugj', 'ufy', 'ubj', 'uxx', 'umaxhp',
    'tf','tfgj','tfbj','tfxx','tfhp','tffy','tfxy','tfsb', 'hoan', 'tf_moi');
$totUgj = $totUfy = $totUbj = $totUxx = $totHp = 0;
$totHoan = 0;
foreach ($rs as $r) {
    $hoan = intval($r['tfhp']) + intval($r['tfgj']) + intval($r['tffy'])
          + intval($r['tfbj']) + intval($r['tfxx']) + intval($r['tfxy']) + intval($r['tfsb']);
    $totHoan += $hoan;
    // :576-580 — chi so se MAT khi xoa
    $dGj = intval($r['tfgj']) * 5;
    $dBj = intval($r['tfbj']) * 1.5;
    $dXx = intval($r['tfxx']) * 2;
    $dHp = intval($r['tfhp']) * 10;
    $dFy = intval($r['tffy']) * 5;
    $totUgj += $dGj; $totUfy += $dFy; $totUbj += $dBj; $totUxx += $dXx; $totHp += $dHp;
    printf("%-10s %-14s %5s %2s %8s %8s %8s %8s %8s | %6s %6s %6s %6s %6s %6s %6s %6s | %8d %9d\n",
        substr($r['sid'],0,8), $r['uname'], $r['ulv'], $r['shenfen'],
        $r['ugj'], $r['ufy'], $r['ubj'], $r['uxx'], $r['umaxhp'],
        $r['tf'],$r['tfgj'],$r['tfbj'],$r['tfxx'],$r['tfhp'],$r['tffy'],$r['tfxy'],$r['tfsb'],
        $hoan, intval($r['tf']) + $hoan);
    $effGj = intval($r['ugj']) + $dGj; $effFy = intval($r['ufy']) + $dFy;
    $effBj = floatval($r['ubj']) + $dBj; $effXx = intval($r['uxx']) + $dXx;
    $effHp = intval($r['umaxhp']) + $dHp;
    printf("           -> MAT khi xoa :576-580 (%% cua GIA TRI THUC sau hydration): ugj %d/%d (%.1f%%), ufy %d/%d (%.1f%%), ubj %.1f/%.1f (%.1f%%), uxx %d/%d (%.1f%%), umaxhp %d/%d (%.1f%%)\n",
        $dGj, $effGj, $effGj > 0 ? $dGj / $effGj * 100 : 0,
        $dFy, $effFy, $effFy > 0 ? $dFy / $effFy * 100 : 0,
        $dBj, $effBj, $effBj > 0 ? $dBj / $effBj * 100 : 0,
        $dXx, $effXx, $effXx > 0 ? $dXx / $effXx * 100 : 0,
        $dHp, $effHp, $effHp > 0 ? $dHp / $effHp * 100 : 0);
}
echo str_repeat('-', 100), "\n";
printf("TONG %d nhan vat | hoan = %d | MAT: ugj %d, ufy %d, ubj %.1f, uxx %d, umaxhp %d\n",
    count($rs), $totHoan, $totUgj, $totUfy, $totUbj, $totUxx, $totHp);

echo "\n=== upplayerlv van cap tf* moi lan len cap (class/player.php:1199-1215) ===\n";
echo "shenfen 1 -> \$zs=1 (tfgj1, rangesbj); 2 -> \$xs=1 (rangesxx, tffy1); 3 -> \$ys=1 (rangesxy, tfhp1)\n";
$n = $db->query("SELECT COUNT(*) c FROM game1 WHERE shenfen IN (1,2,3)")->fetch(PDO::FETCH_ASSOC);
echo "nhan vat co shenfen 1/2/3 (se tiep tuc duoc cap tf* sau migration): " . $n['c'] . "\n";
```

```bash
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe -l E:/window/Temp/_tp_legacy.php
D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe E:/window/Temp/_tp_legacy.php
```

**Ket qua do that 2026-10-14 (DB local, 14 nhan vat)** — chi 4 nhan vat co gia tri cu, 10 nhan vat con lai deu 0:

```
sid        uname            ulv sf      ugj      ufy      ubj      uxx   umaxhp |     tf   tfgj   tfbj   tfxx   tfhp   tffy   tfxy   tfsb |     hoan    tf_moi
8d032975   PhátĐây        93  1     7153     4898        7        9    34964 |    180     36     36      0    102      0      0    188 |      362       542
           -> MAT khi xoa :576-580 (% cua GIA TRI THUC sau hydration): ugj 180/7333 (2.5%), ufy 0/4898 (0.0%), ubj 54.0/61.0 (88.5%), uxx 0/9 (0.0%), umaxhp 1020/35984 (2.8%)
40e5df66   tttttt            12  2       60       56        0        0      417 |     55      0      0     11      0     11      0      0 |       22        77
           -> MAT khi xoa :576-580 (% cua GIA TRI THUC sau hydration): ugj 0/60 (0.0%), ufy 55/111 (49.5%), ubj 0.0/0.0 (0.0%), uxx 22/22 (100.0%), umaxhp 0/417 (0.0%)
fb36ea13   Tiên Nhân        9  3       66       73        0        0      338 |     40      0      0      0     16      0      8      0 |       24        64
           -> MAT khi xoa :576-580 (% cua GIA TRI THUC sau hydration): ugj 0/66 (0.0%), ufy 0/73 (0.0%), ubj 0.0/0.0 (0.0%), uxx 0/0 (0.0%), umaxhp 160/498 (32.1%)
dc4996fe   LouLxGM            2  1       17       12        0        0       70 |      5      1      1      0      0      0      0      0 |        2         7
           -> MAT khi xoa :576-580 (% cua GIA TRI THUC sau hydration): ugj 5/22 (22.7%), ufy 0/12 (0.0%), ubj 1.5/1.5 (100.0%), uxx 0/0 (0.0%), umaxhp 0/70 (0.0%)
----------------------------------------------------------------------------------------------------
TONG 14 nhan vat | hoan = 410 | MAT: ugj 185, ufy 55, ubj 55.5, uxx 22, umaxhp 1180

=== upplayerlv van cap tf* moi lan len cap (class/player.php:1199-1215) ===
shenfen 1 -> $zs=1 (tfgj1, rangesbj); 2 -> $xs=1 (rangesxx, tffy1); 3 -> $ys=1 (rangesxy, tfhp1)
nhan vat co shenfen 1/2/3 (se tiep tuc duoc cap tf* sau migration): 14
```

**Doc bang nay the nao:** cot `ubj 54.0/61.0 (88.5%)` cua `PhátĐây` (nhan vat cua chinh chu du an) nghia la **bao kich that te tụt tu 61 xuong 7 — mat 88.5%**. Do la **chi so dau duy nhat** ma nguoi choi nhin thay ngay khi vao tran. `uxx 22/22 (100.0%)` cua `tttttt` nghia la hut mau ve **dung 0**.

<!--T12A-->

**1c. Hai phuong an — trinh bay dung bang nay cho chu du an, khong tom tat:**

| | Phuong an A — **dung nhu spec §6** | Phuong an C — **giu `:576-580`** |
|---|---|---|
| Hanh dong | Xoa 5 dong `class/player.php:576-580`; hoan 7 cot ve `tf` | **Giu nguyen** 5 dong; van hoan 7 cot ve `tf` (hoan la thuong them) |
| Chi so that | `PhátĐây` ubj 61→7 (**−88.5%**), umaxhp 35984→34964 (−2.8%), ugj 7333→7153 (−2.5%); `tttttt` uxx 22→0, ufy 111→56; `Tiên Nhân` umaxhp 498→338 | **Khong doi** mot chi so nao |
| Than Phan (`shenfen`) | Tro thanh **chi trang tri**: `upplayerlv` van cap 6 cot cu nhung khong ai doc → diem tang ma vo tac dung | Van hoat dong dung nhu hom nay (cap theo cap, doi ra chi so) |
| Bu lai bang gi | `tf` hoan 410 diem → nguoi choi tu mua thien phu; 7 cong deu la **ti le**, khong co cong nao tra `ubj/uxx` | Khong can bu — hoan diem la **loc tinh khong** |
| Rui ro | Nerf bao kich/hut mau rat nang voi nhan vat cu; **khong the lay lai** sau khi nguoi choi da tieu `tf` (tru khi restore dump) | Deviation 1 dong khoi spec §6; 7 cot cu van "song" (khong dung y "chi ngung doc") |
| Rollback | Restore `bak/tianfu_pre_<...>.sql` + `git revert` | Khong can |

**1d. DUNG LAI VA HOI.** In ra bang 1c va cau hoi nguyen van:

> "Xoa `class/player.php:576-580` (spec §6) se lam **bao kich cua `PhátĐây` tut tu 61 xuong 7 (−88.5%)** va **hut mau cua `tttttt` ve 0**, vi Than Phan (`shenfen`) hien la nguon duy nhat cua 5 chi so do va he Thien Phu moi **khong co cong nao tra lai**. Chon **A** (xoa, dung spec, chap nhan nerf) hay **C** (giu 5 dong, hoan diem thanh thuong them)?"

**Khong duoc tu chon.** Day la thay doi kinh te nguoi choi nhin thay ngay — le `feedback-careful-server-changes` va `feedback-khong-tu-thu-hep-pham-vi`. Ghi cau tra loi vao `docs/UI_THIENPHU.md` muc 8 va vao ban giao Step 13.

**Trong khi cho tra loi:** Step 2 (xoa CSS `.talent-*`) **doc lap** — lam duoc ngay. Step 3 **phai doi**. Step 4/5 (viet script + test) **viet duoc ca hai nhanh** A va C: script migration **khong phu thuoc** vao `:576-580` (no chi hoan diem va phat dan), nhanh A/C chi khac nhau o Step 3. Step 6 (`--apply`) **phai doi** Step 3 xong.

**1e. Mot he qua bat buoc phai ghi, du chon A hay C.** `upplayerlv()` van cap 6 cot cu sau migration ⇒ **migration khong idempotent theo tu nhien**: neu chay `--apply` lan 2, `hoan` se tinh ca phan moi cap va **hoan trung**. Vi vay Step 4 **bat buoc** co guard: bo qua moi `sid` da co dong `player_tianfu_log` voi `kind = 'migrate'`. Guard nay cung la ly do `tests/test_tianfu_migration.php` (Step 5) co test "chay 2 lan khong hoan trung".

<!--T12A2-->

- [ ] **Step 2: Xoa 425 dong `.talent-*` khoi `css/mystyle.css`** (spec §6 dong 9 — "xoa **sau khi** `.tp-*` chay duoc", tuc la sau Task 8/9/10 da commit)

**2a. Kiem tra dieu kien truoc — bat buoc, khong bo qua:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
# (i) khong con ai dung lop .talent-* trong code song
grep -rn 'talent-' --include='*.php' --include='*.js' --include='*.html' . 2>/dev/null | grep -v '^\./bak/' | wc -l
#   Expected: 0    (nguon duy nhat la game/tianfu.php cu — Task 8 da viet lai het)
# (ii) .tp-* that su da co trong CSS
grep -c '\.tp-wrap\|\.tpc-wrap\|\.tpo-wrap' css/mystyle.css
#   Expected: >= 3
# (iii) 3 trang moi dang chay (khong phai trang trang vi thieu CSS)
grep -c 'tp-wrap' game/tianfu.php; grep -c 'tpc-wrap' game/tianfu_collection.php; grep -c 'tpo-wrap' game/tianfu_open.php
#   Expected: 1  1  1
```

Neu (i) khac 0: **dung xoa** — tim dong con dung `.talent-*` va xu ly truoc. Neu (ii)/(iii) khong dat: Task 8/9/10 chua xong, quay lai.

**2b. Xoa theo neo ASCII, khong xoa theo so dong cung.** Spec §6 ghi `4065-4500`; **do that 2026-10-14 la `4062-4486`** (425 dong: 3 dong comment `/* ==== Thiên phú ==== */` o `4062-4064`, rule dau `.talent-page{` o `4065`, `}` dong media query o `4485`, 1 dong trong o `4486`; `.qydt-page{` bat dau o `4487`). Vi Task 7/9/10 **append** xuong cuoi file nen `4062-4486` van dung, nhung **khong tin so** — dung neo va guard:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
F=css/mystyle.css
BEFORE=$(wc -l < "$F")
test "$(grep -c '^\.talent-page{$' "$F")" = "1" \
  || { echo "LOI: neo dau .talent-page{ khong duy nhat"; exit 1; }
test "$(grep -c '^    \.talent-actions\.fast{grid-template-columns:1fr!important}$' "$F")" = "1" \
  || { echo "LOI: neo cuoi .talent-actions.fast khong duy nhat"; exit 1; }
FIRST=$(grep -n '^\.talent-page{$' "$F" | cut -d: -f1)
LAST=$(grep -n '^    \.talent-actions\.fast{grid-template-columns:1fr!important}$' "$F" | cut -d: -f1)
START=$((FIRST-3)); ENDB=$((LAST+2))
sed -n "${START}p"   "$F" | grep -q '^/\* =========================$' || { echo "LOI: $START khong phai mo comment"; exit 1; }
sed -n "$((LAST+1))p" "$F" | grep -q '^}$' || { echo "LOI: $((LAST+1)) khong phai } dong media query"; exit 1; }
sed -n "$((LAST+2))p" "$F" | grep -q '^$'  || { echo "LOI: $((LAST+2)) khong phai dong trong"; exit 1; }
echo "START=$START ENDB=$ENDB SE_XOA=$((ENDB-START+1)) BEFORE=$BEFORE"
sed -i "${START},${ENDB}d" "$F"
AFTER=$(wc -l < "$F")
echo "AFTER=$AFTER DA_XOA=$((BEFORE-AFTER))"
test "$((BEFORE-AFTER))" = "$((ENDB-START+1))" || { echo "LOI: xoa sai so dong"; exit 1; }
```

Expected voi so do 2026-10-14: `START=4062 ENDB=4486 SE_XOA=425 BEFORE=7846` (+ so dong Task 7/9/10 da append) → `DA_XOA=425`.

> **Vi sao phai guard nhieu lop:** `sed -i 'Nd'` tren file 7800+ dong ma lech 1 dong la **mat mot rule cua he khac** (`.qydt-*` nam ngay ben duoi). Le `preg-delimiter-destroys-lines` trong memory la cung mot lop loi: dung cu xoa theo dong ma khong assert = xoa im lang.

**2c. Kiem tra sau khi xoa:**

```bash
cd /d/XXJserver/phpstudy_pro/WWW
F=css/mystyle.css
grep -c '\.talent' "$F"                                   # Expected: 0
grep -n '^\.qydt-page{$' "$F" | head -1                   # Expected: con, o dong START (4062)
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" -r "\$s=file_get_contents('css/mystyle.css'); \$o=substr_count(\$s,'{'); \$c=substr_count(\$s,'}'); echo \"braces open=\$o close=\$c\n\"; if(\$o!==\$c){echo 'UNBALANCED'.PHP_EOL;exit(1);}"
git diff --stat -- css/mystyle.css                        # Expected: 0 insertions(+), 425 deletions(-)
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]|[\x{4e00}-\x{9fff}\x{3000}-\x{303f}]' css/mystyle.css | head
                                                          # Expected: khong in ra dong nao
```

**`0 insertions(+)` la bat buoc** — neu co du 1 insertion thi `sed` da chen ky tu la (le `edit-tool-vietnamese-smart-quotes`). Comment bi xoa co chu `Thiên phú` (co dau) nen day la cho de sinh byte loi nhat.

**2d. Mo that 3 trang va trang ke ben de chac khong vo style:**

```bash
start chrome "http://127.0.0.1:8091/game.php?cmd=<token tianfu>"
start chrome "http://127.0.0.1:8091/game.php?cmd=<token qydt>"
```

Token sinh bang `E:/window/Temp/_tp_url.php` cua Task 7. Trang `qydt` (`game/qydt.php:170 <div class="qydt-page">`, route `game.php:342`) nam **ngay sau** khoi bi xoa — phai nhin no truoc khi commit. **CHUA TEST** cho den khi ban tu nhin; ghi vao `docs/UI_THIENPHU.md` muc 10 neu chua kip nhin.

<!--T12B-->

- [ ] **Step 3: Xoa khoi thien phu cu trong `class/player.php` — CHI KHI chu du an chon Phuong an A**

> **CHOT** o Step 1. Neu chu du an chon **Phuong an C** → **BO QUA TOAN BO Step 3**, ghi vao
> Step 13 "Phuong an C: giu `class/player.php:575-580`, spec §6 lech 1 dong (khong xoa)".

3a. Xac dinh vi tri bang **anchor ASCII duy nhat**. Khong duoc dung so dong cung (575/576) vi Task 1
(`:27`), Task 3 (`:157`), Task 11 Step 8 (`:324-338`) va Step 13a (`:412-424`) deu chen/xoa dong
**phia tren** 575 — sau 4 task do, block nay da troi xuong.

```bash
P=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
A='(int)$player->tfgj != 0'
echo "so lan anchor xuat hien (ky vong 1): $(grep -cF "$A" class/player.php)"
FIRST=$(grep -nF "$A" class/player.php | head -1 | cut -d: -f1)
echo "FIRST=$FIRST"
```

Expected: `so lan anchor xuat hien (ky vong 1): 1` va `FIRST` in ra mot so (hien tai `576`, sau
Task 11 co the `580+`). Neu khac 1 → **DUNG**, co nguoi da sao chep block, tim thu cong.

3b. Kiem tra 3 moc bao quanh truoc khi xoa (neu 1 trong 3 sai → **DUNG**, khong xoa):

```bash
echo "--- dong FIRST-1 (phai bat dau bang 4 space + '// ')"
sed -n "$((FIRST-1))p" class/player.php | cat -A | head -1
echo "--- dong FIRST+4 (phai la dong tffy)"
sed -n "$((FIRST+4))p" class/player.php
echo "--- dong FIRST+5 (phai RONG)"
sed -n "$((FIRST+5))p" class/player.php | cat -A
echo "--- dong FIRST+6 (phai la comment 'Gom toan bo trang bi')"
sed -n "$((FIRST+6))p" class/player.php
```

Expected: `FIRST-1` = `    // Thiên phú tăng thêm vào thuộc tính tổng.$` ; `FIRST+4` = dong
`if ((int)$player->tffy != 0) $player->ufy    += (int)$player->tffy * 5;` ; `FIRST+5` = `$` (rong) ;
`FIRST+6` bat dau bang `    // Gom toàn bộ trang bị`.

3c. Xoa 6 dong (`FIRST-1` .. `FIRST+4`) — **comment mo coi + 5 dong `if`**, khong giu comment lai:

```bash
cp class/player.php E:/window/Temp/_player_before_step3.php
sed -i "$((FIRST-1)),$((FIRST+4))d" class/player.php
```

> **Lech 1 dong so voi spec §6** (`576-580`): spec chi tinh 5 dong `if`, nhung xoa chung thi dong
> comment `:575` (`// Thiên phú tăng thêm vào thuộc tính tổng.`) thanh **comment mo coi** mo ta mot
> khoi code khong con. Xoa ca 6. Day la lech duoc phep, ghi lai o Step 13.

3d. Kiem tra sau xoa — `class/player.php` **DA duoc git track** nen dung duoc `git diff --stat`:

```bash
git diff --stat -- class/player.php
echo "--- khong con tham chieu tf* nao trong getplayer:"
grep -nF '$player->tfgj' class/player.php; grep -nF '$player->tfhp' class/player.php
grep -nF '$player->tffy' class/player.php; grep -nF '$player->tfbj' class/player.php
grep -nF '$player->tfxx' class/player.php
echo "(5 grep tren deu phai KHONG in ra gi)"
echo "--- comment mo coi phai het:"
grep -cF 'Thiên phú tăng thêm vào thuộc tính tổng' class/player.php
"$P" -l class/player.php
```

Expected: `git diff --stat -- class/player.php` ket thuc bang **`0 insertions(+), 6 deletions(-)`**
(neu Task 11 Step 8/13a chua commit thi so se la tong cua ca 3 — lay `git stash` hoac so sanh
`diff -u E:/window/Temp/_player_before_step3.php class/player.php | grep -c '^+[^+]'` = **0** va
`grep -c '^-[^-]'` = **6**). 5 grep `$player->tf*` in ra **khong co gi**. Grep comment = **0**.
`php -l` = `No syntax errors detected`.

3e. **Xac nhan hoi quy chien luc**: `combat_power()` (Task 11 Step 13a) da **khong con** doc
`tf`/`tfxy`/`tfsb` qua `$weights`, nen xoa block nay **khong** doi chien luc — chi doi
`ugj/ubj/uxx/umaxhp/ufy`. Do lai bang `_tp_legacy.php` (Step 1b) de in ra **dung con so da bao
truoc** cho chu du an:

```bash
"$P" E:/window/Temp/_tp_legacy.php | tail -3
```

Expected: `MAT: ugj 185, ufy 55, ubj 55.5, uxx 22, umaxhp 1180` (14 nhan vat — se khac sau khi
Task 11 Step 14 chay, vi `combat_power` moi). Neu chu du an thay so nay **lon qua** va doi y →
quay lai Step 1, chuyen sang Phuong an C, `cp E:/window/Temp/_player_before_step3.php class/player.php`.

- [ ] **Step 4: Viet `sql/migrate_tianfu.php` — hoan diem + phat Khai Phu Dan**

4a. **Toan bo noi dung file** (da `php -l` OK bang PHP 5.6.9 NTS, 0 ky tu CJK lac, 0 smart quote):

```php
<?php
/**
 * migrate_tianfu.php — hoan 7 cot game1.tf* cu ve `tf`, phat 1 Khai Phu Dan (djid 114),
 * ghi player_tianfu_log(kind='migrate'), dong bo 4 cot game1.tianfu_*.
 *
 *   php sql/migrate_tianfu.php --dry-run            in bang, KHONG ghi DB
 *   php sql/migrate_tianfu.php --apply              ghi that
 *   php sql/migrate_tianfu.php --apply --sid=<hex>  chi 1 nhan vat (dung cho test)
 *
 * Idempotent: bo qua moi sid da co dong player_tianfu_log(kind='migrate'). Bat buoc,
 * vi upplayerlv() (class/player.php:1199-1215) van cap 6 cot tf* cu moi lan len cap —
 * khong guard thi chay --apply lan 2 se hoan trung.
 * Mot nhan vat FAIL => rollBack nhan vat do va DUNG HET (spec §7.4).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/tianfu.php';
require_once $ROOT . '/pdo.php';   // -> $dblj (global)

// pdo.php dat ATTR_ERRMODE = ERRMODE_WARNING: PDO khong nem exception, chi canh bao
// roi tra false => moi loi DB se bi NUOT IM. Bat buoc doi sang EXCEPTION de try/catch
// ben duoi that su bat duoc (le silent-failure).
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
$ONLY  = null;
foreach ($argv as $a) {
    if (strpos($a, '--sid=') === 0) { $ONLY = substr($a, 6); }
}
if ($APPLY === $DRY) {
    echo "DUNG: chon dung mot co — --dry-run hoac --apply\n";
    exit(2);
}

$LEGACY    = array('tfhp', 'tfgj', 'tffy', 'tfbj', 'tfxx', 'tfxy', 'tfsb');
$PILL_DJID = 114;

echo $APPLY ? "=== migrate_tianfu.php --apply (GHI DB) ===\n"
            : "=== migrate_tianfu.php --dry-run (KHONG ghi DB) ===\n";

// ---- 0) Schema phai co san (Task 2) ----
foreach (array('player_tianfu', 'player_tianfu_log') as $t) {
    $c = $dblj->query("SELECT COUNT(*) FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $dblj->quote($t))->fetchColumn();
    if (intval($c) !== 1) {
        echo "LOI: bang $t chua ton tai — chay sql/sql_tianfu.sql truoc (Task 2)\n";
        exit(3);
    }
}
foreach (array('tianfu_id', 'tianfu_quality', 'tianfu_lv', 'tianfu_power') as $col) {
    $c = $dblj->query("SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'game1'
              AND COLUMN_NAME = " . $dblj->quote($col))->fetchColumn();
    if (intval($c) !== 1) {
        echo "LOI: game1.$col chua ton tai — chay sql/sql_tianfu.sql truoc (Task 2)\n";
        exit(3);
    }
}
$pill = \player\getdaoju($PILL_DJID, $dblj);
if (!$pill) {
    echo "LOI: daoju $PILL_DJID (Khai Phu Dan) chua co trong class/config/daoju_config.json — Task 4\n";
    exit(3);
}
echo "Schema OK: 2 bang + 4 cot game1 + daoju $PILL_DJID ({$pill->djname})\n\n";

// ---- 1) Danh sach nhan vat ----
$sql = "SELECT sid, uname, ulv, tf, " . implode(',', $LEGACY) . " FROM game1";
if ($ONLY !== null) { $sql .= " WHERE sid = " . $dblj->quote($ONLY); }
$sql .= " ORDER BY ulv DESC, sid ASC";
$rows = $dblj->query($sql)->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "Khong tim thay nhan vat nao" . ($ONLY !== null ? " voi sid=$ONLY" : "") . "\n";
    exit(0);
}

// ---- 2) Guard idempotent ----
$done = array();
foreach ($dblj->query("SELECT DISTINCT sid FROM player_tianfu_log WHERE kind = 'migrate'")
             ->fetchAll(PDO::FETCH_COLUMN) as $s) {
    $done[(string)$s] = true;
}

printf("%-34s %-16s %5s %8s %7s %8s  %s\n",
    'sid', 'uname', 'ulv', 'tf_cu', 'hoan', 'tf_moi', '7 cot cu');
$ok = 0; $skip = 0; $totHoan = 0; $totPill = 0;

foreach ($rows as $r) {
    $sid   = (string)$r['sid'];
    $hoan  = 0;
    $parts = array();
    foreach ($LEGACY as $c) {
        $v = intval($r[$c]);
        $hoan += $v;
        $parts[] = $c . '=' . $v;
    }
    if (isset($done[$sid])) {
        $skip++;
        printf("%-34s %-16s %5s %8s %7s %8s  SKIP (da co log kind=migrate)\n",
            $sid, $r['uname'], $r['ulv'], $r['tf'], '-', '-');
        continue;
    }
    $totHoan += $hoan;
    $totPill++;
    printf("%-34s %-16s %5s %8s %7d %8d  %s\n",
        $sid, $r['uname'], $r['ulv'], $r['tf'], $hoan, intval($r['tf']) + $hoan,
        implode(' ', $parts));

    if (!$APPLY) { continue; }

    // ---- 3) Ghi that: mot transaction cho mot nhan vat ----
    try {
        $dblj->beginTransaction();
        \player\player_cache_clear($sid);
        $dblj->prepare("UPDATE game1 SET tf = tf + ?,
                tfhp = 0, tfgj = 0, tffy = 0, tfbj = 0, tfxx = 0, tfxy = 0, tfsb = 0
                WHERE sid = ?")->execute(array($hoan, $sid));
        \player\tianfu_log($sid, 'migrate', 0, 0, 0,
            'hoan ' . $hoan . ' diem tu 7 cot cu + 1 Khai Phu Dan 114', $dblj);
        \player\adddj($sid, $PILL_DJID, 1, $dblj);
        \player\tianfu_recalc_mirror($sid, $dblj);
        $dblj->commit();
        \player\player_cache_clear($sid);
        $ok++;
    } catch (Exception $e) {
        if ($dblj->inTransaction()) { $dblj->rollBack(); }
        \player\player_cache_clear($sid);
        echo "\nFAIL $sid: " . $e->getMessage() . "\n";
        echo "DUNG HET (spec §7.4): OK=$ok FAIL=1 — khong chay tiep nhan vat con lai.\n";
        echo "Nhan vat nay da rollBack; cac nhan vat OK truoc do VAN da commit.\n";
        echo "Restore toan bo: mysql -u root -p game < bak/tianfu_pre_<nhan>.sql\n";
        exit(1);
    }
}

echo str_repeat('-', 100), "\n";
if ($APPLY) {
    echo "OK=$ok FAIL=0 SKIP=$skip TONG_HOAN=$totHoan TONG_DAN_114=$totPill\n";
} else {
    echo "SE_MIGRATE=$totPill SKIP=$skip TONG_HOAN=$totHoan TONG_DAN_114=$totPill (KHONG ghi DB)\n";
}

// ---- 4) Kiem tra sau (spec §7.5) ----
$c1 = intval($dblj->query("SELECT COUNT(*) FROM game1
        WHERE tfhp+tfgj+tffy+tfbj+tfxx+tfxy+tfsb > 0")->fetchColumn());
$c2 = intval($dblj->query("SELECT COUNT(*) FROM game1 WHERE tianfu_power > 0")->fetchColumn());
$c3 = intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log WHERE kind = 'migrate'")->fetchColumn());
$c4 = intval($dblj->query("SELECT COUNT(*) FROM playerdaoju WHERE djid = " . intval($PILL_DJID))->fetchColumn());
$c5 = intval($dblj->query("SELECT COUNT(*) FROM game1")->fetchColumn());
echo "\n=== Kiem tra sau ===\n";
printf("game1 co 7 cot cu > 0            : %d   (ky vong 0 NGAY SAU apply; len cap se lam no > 0 lai — binh thuong)\n", $c1);
printf("game1 co tianfu_power > 0         : %d   (ky vong 0 — chua ai bat Phu Vi)\n", $c2);
printf("player_tianfu_log kind=migrate    : %d   (ky vong = so nhan vat da migrate)\n", $c3);
printf("playerdaoju djid=114              : %d   (ky vong = so nhan vat da migrate)\n", $c4);
printf("tong nhan vat game1               : %d\n", $c5);
if ($APPLY && ($c1 !== 0 || $c2 !== 0 || $c3 !== $totPill + $skip || $c4 < $totPill)) {
    echo "LOI: kiem tra sau khong dat — dung ban giao, restore dump.\n";
    exit(1);
}
exit(0);
```

> **Vi sao phai `setAttribute(ERRMODE_EXCEPTION)`**: `pdo.php:29` dat `ERRMODE_WARNING`. Neu khong
> doi, mot cau UPDATE sai cot se chi in `PHP Warning` roi tra `false`, `try/catch` **khong bat duoc**,
> va script bao `OK=14` tren mot DB da hong — day chinh la loai **silent failure** nguy hiem nhat
> trong migration. Doi sang EXCEPTION o **dau file**, khong doi trong `pdo.php` (se anh huong
> toan bo game).

> **Vi sao transaction an toan**: `adddj()` (`class/player.php:1305-1325`) **khong mo transaction**
> (da doc het 20 dong — chi co `getplayer()`, `INSERT playerdaoju`, `changerwyq1()`,
> `nhiemvu_update_all()`). Nen `beginTransaction()` ben ngoai khong gap "nested transaction".
> `player_cache_clear($sid)` goi **2 lan** (truoc khi ghi va sau `commit()`) vi `adddj()` goi
> `getplayer()` giua chung — khong clear lan 2 thi cache giu ban `tf` cu.

4b. Kiem tra cu phap:

```bash
"$P" -l sql/migrate_tianfu.php
grep -nP '[\x{2E80}-\x{9FFF}\x{3000}-\x{303F}]' sql/migrate_tianfu.php; echo "cjk_exit=$?"
grep -nP '[\x{2018}\x{2019}\x{201C}\x{201D}]' sql/migrate_tianfu.php; echo "sq_exit=$?"
```

Expected: `No syntax errors detected`; 2 grep **khong in ra gi** (exit 1 → `cjk_exit=1`, `sq_exit=1`).

- [ ] **Step 5: Viet `tests/test_tianfu_migration.php`**

Test nay **chay `sql/migrate_tianfu.php` bang subprocess** (`exec()`), vi script goi `exit()` —
`require` truc tiep se ket thuc luon ca trinh test.

5a. Tao file `tests/test_tianfu_migration.php` voi toan bo noi dung sau:

```php
<?php
/**
 * test_tianfu_migration.php — kiem migrate_tianfu.php: hoan diem, phat dan 114, idempotent.
 *   "...php.exe" tests/test_tianfu_migration.php
 * Tu tao nhan vat gia (sid MIGTP...), tu xoa khi xong. KHONG dung nhan vat that.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/pdo.php';
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
function ttrue($name, $cond, $note) {
    global $fails;
    if ($cond) { echo "PASS $name ($note)\n"; }
    else { $fails++; echo "FAIL $name ($note)\n"; }
}

$P   = defined('PHP_BIN') ? PHP_BIN : 'D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe';
$SID = 'MIGTP' . substr(md5(uniqid('', true)), 0, 27);
$LEG = array('tfhp' => 7, 'tfgj' => 5, 'tffy' => 3, 'tfbj' => 2, 'tfxx' => 4, 'tfxy' => 6, 'tfsb' => 1);
$HOAN = array_sum($LEG);   // 28

// ---- A: don dep + tao fixture ----
function cleanup($db, $sid) {
    $db->exec("DELETE FROM player_tianfu      WHERE sid = " . $db->quote($sid));
    $db->exec("DELETE FROM player_tianfu_log  WHERE sid = " . $db->quote($sid));
    $db->exec("DELETE FROM playerdaoju        WHERE sid = " . $db->quote($sid));
    $db->exec("DELETE FROM game1              WHERE sid = " . $db->quote($sid));
    \player\player_cache_clear($sid);
}
cleanup($dblj, $SID);

$cols = array('sid', 'uname', 'ulv', 'tf', 'shenfen');
$vals = array($dblj->quote($SID), $dblj->quote('TP_MIGTEST'), 40, 12, 1);
foreach ($LEG as $c => $v) { $cols[] = $c; $vals[] = intval($v); }
$dblj->exec("INSERT INTO game1 (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")");
tassert('fixture da chen', intval($dblj->query("SELECT COUNT(*) FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 1);

function mig($P, $ROOT, $args) {
    $cmd = '"' . $P . '" "' . $ROOT . '/sql/migrate_tianfu.php" ' . $args . ' 2>&1';
    $out = array(); $code = 0;
    exec($cmd, $out, $code);
    return array('code' => $code, 'out' => implode("\n", $out));
}

// ---- B: khong co co nao => exit 2 ----
$r = mig($P, $ROOT, '');
tassert('B: thieu co => exit 2', $r['code'], 2);
ttrue('B: bao chon dung mot co', strpos($r['out'], 'chon dung mot co') !== false, $r['out']);

// ---- C: dry-run khong ghi DB ----
$r = mig($P, $ROOT, '--dry-run --sid=' . $SID);
tassert('C: dry-run exit 0', $r['code'], 0);
ttrue('C: in ra SE_MIGRATE', strpos($r['out'], 'SE_MIGRATE=') !== false, 'co dong tong ket');
ttrue('C: tinh dung hoan=' . $HOAN, strpos($r['out'], 'TONG_HOAN=' . $HOAN) !== false, $r['out']);
tassert('C: tf khong doi', intval($dblj->query("SELECT tf FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 12);
tassert('C: tfgj khong bi xoa', intval($dblj->query("SELECT tfgj FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 5);
tassert('C: chua co log migrate', intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log
    WHERE sid = " . $dblj->quote($SID) . " AND kind = 'migrate'")->fetchColumn()), 0);

// ---- D: apply that ----
$r = mig($P, $ROOT, '--apply --sid=' . $SID);
tassert('D: apply exit 0', $r['code'], 0);
tassert('D: tf = 12 + ' . $HOAN, intval($dblj->query("SELECT tf FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 12 + $HOAN);
$zero = intval($dblj->query("SELECT tfhp+tfgj+tffy+tfbj+tfxx+tfxy+tfsb FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn());
tassert('D: 7 cot cu = 0', $zero, 0);
tassert('D: co 1 log migrate', intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log
    WHERE sid = " . $dblj->quote($SID) . " AND kind = 'migrate'")->fetchColumn()), 1);
tassert('D: co Khai Phu Dan 114 = 1', intval($dblj->query("SELECT djsum FROM playerdaoju
    WHERE sid = " . $dblj->quote($SID) . " AND djid = 114")->fetchColumn()), 1);
tassert('D: mirror tianfu_id = 0 (chua khai phu)', intval($dblj->query("SELECT tianfu_id
    FROM game1 WHERE sid = " . $dblj->quote($SID))->fetchColumn()), 0);

// ---- E: chay lan 2 phai SKIP, khong hoan trung ----
$tfTruoc = intval($dblj->query("SELECT tf FROM game1 WHERE sid = " . $dblj->quote($SID))->fetchColumn());
$r = mig($P, $ROOT, '--apply --sid=' . $SID);
tassert('E: apply lan 2 exit 0', $r['code'], 0);
ttrue('E: bao SKIP', strpos($r['out'], 'SKIP (da co log kind=migrate)') !== false, 'co chu SKIP');
tassert('E: tf khong doi lan 2', intval($dblj->query("SELECT tf FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), $tfTruoc);
tassert('E: van chi 1 log migrate', intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log
    WHERE sid = " . $dblj->quote($SID) . " AND kind = 'migrate'")->fetchColumn()), 1);
tassert('E: dan 114 van = 1', intval($dblj->query("SELECT djsum FROM playerdaoju
    WHERE sid = " . $dblj->quote($SID) . " AND djid = 114")->fetchColumn()), 1);

// ---- F: sid khong ton tai => exit 0, khong crash ----
$r = mig($P, $ROOT, '--apply --sid=00000000000000000000000000000000');
tassert('F: sid la exit 0', $r['code'], 0);
ttrue('F: bao khong tim thay', strpos($r['out'], 'Khong tim thay nhan vat nao') !== false, $r['out']);

// ---- G: don dep ----
cleanup($dblj, $SID);
tassert('G: fixture da xoa', intval($dblj->query("SELECT COUNT(*) FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 0);

echo "\n" . ($fails === 0 ? "TAT CA PASS" : "CO $fails FAIL") . "\n";
exit($fails === 0 ? 0 : 1);
```

5b. Chay test — **ky vong FAIL** vi chua viet `sql/migrate_tianfu.php` (Step 4):

```bash
"$P" tests/test_tianfu_migration.php 2>&1 | tail -12
```

Expected: dong `CO N FAIL` va `exit 1`.

5c. Lam Step 4 (viet `sql/migrate_tianfu.php`) roi chay lai:

```bash
"$P" tests/test_tianfu_migration.php 2>&1 | tail -30
```

Expected: `TAT CA PASS`, khong co dong `FAIL`, khong `PHP Warning` / `Fatal error`.

> **Neu muc F (`sid la`) FAIL**: do la loi that — `--sid=<hex khong co>` phai in
> `Khong tim thay nhan vat nao` va `exit(0)`, khong duoc nem exception. Khong sua test cho PASS.

> **Neu muc E FAIL** (hoan trung o lan 2): guard `player_tianfu_log(kind='migrate')` hong. Day la
> **hoi qui nghiem trong** — len cap se tu cap lai 7 cot cu (`upplayerlv`), nen nguoi choi nao
> len cap sau migration roi bi migrate lan 2 se **nhan diem mien phi vo han**. Dung ban giao.

- [ ] **Step 6: Dump phong ve → dry-run toan bo → apply that (chi local)**

6a. Dump truoc khi migrate — **bat buoc**, day la duong lui duy nhat:

```bash
mkdir -p bak
MY=D:/XXJserver/phpstudy_pro/Extensions/MySQL5.5.29/bin/mysqldump.exe
ls -la "$MY" 2>/dev/null || MY=$(ls D:/XXJserver/phpstudy_pro/Extensions/MySQL*/bin/mysqldump.exe | head -1)
echo "dung: $MY"
"$MY" -u root -pwww.aae.ink --default-character-set=utf8 game \
  game1 playerdaoju player_tianfu player_tianfu_log > bak/tianfu_pre_$(date +%Y%m%d_%H%M).sql
ls -la bak/tianfu_pre_*.sql | tail -1
wc -l bak/tianfu_pre_*.sql | tail -1
```

Expected: file dump **lon hon 100 KB** va co **hang nghin dong**. Neu file ~0 byte → dump that bai
(sai mat khau hoac ten MySQL), **DUNG**, khong migrate.

> **[RANH GIOI] Repo nay KHONG co `.gitignore`** — do that 2026-10-14: `ls .gitignore` →
> `No such file or directory`, va `git check-ignore -v bak/anycthing.sql` → **khong bi ignore**.
> `git status` hien `?? bak/`, `?? .idea/`, `?? .codegraph/`, `?? chajian/`, 300+ `?? shot_*.png`.
> He qua: **`git add -A` / `git add .` se commit toan bo dump DB chua du lieu nguoi choi that.**
> Do la le doan tuyet doi, khong phai loi thong bao.
>
> Cach xu ly an toan nhat cho Task 12 — **bao lac cua may, khong commit**:
>
> ```bash
> # Tao/bo sung .git/info/exclude (CHỈ co hieu luc dia phuong, KHONG duoc commit,
> # khong doi repo cua nguoi khac — an toan hon tao .gitignore moi o buoc nay)
> grep -qxF 'bak/' .git/info/exclude || printf 'bak/\n' >> .git/info/exclude
> git check-ignore -v bak/tianfu_pre_test.sql   # Expected: in ra dong match, khong phai rong
> ```
>
> Kiem tra lan 2 truoc commit Step 12: `git status --short -- bak/` phai **in ra rong**.
>
> **KHONG tu tao `.gitignore` goc.** File do anh huong toan repo (se an `.idea/`, `chajian/`,
> `class/config/*.json` dang `??` — co the vo tinh loai khoi git nhung thu dang can), va **nam
> ngoai spec**. Thay vao do, ghi vao ban giao Task 12 Step 13 nhu **mon no #9**: "Repo khong co
> `.gitignore`; dump DB duoc bao lac bang `.git/info/exclude` (local-only). An nghia muon co
> `.gitignore` that thi chu du an tu quyet danh sach."

6b. Dry-run toan bo, doi chieu voi `_tp_legacy.php`:

```bash
"$P" sql/migrate_tianfu.php --dry-run | tail -8
"$P" E:/window/Temp/_tp_legacy.php | tail -2
```

Expected: 2 dong `TONG_HOAN=…` **BANG NHAU** (hien tai `410` voi 14 nhan vat). Day la phep doi
chieu doc lap: `_tp_legacy.php` doc truc tiep tu DB bang SQL thuan, con migration doc qua
`\player\` namespace — bang nhau thi cong thuc `hoan = tong 7 cot` dung o ca 2 noi.

6c. Apply that:

```bash
"$P" sql/migrate_tianfu.php --apply | tail -14
```

Expected: dong `OK=14 FAIL=0 SKIP=0 TONG_HOAN=410 TONG_DAN_114=14` (so nhan vat co the khac 14 —
lay theo `SELECT COUNT(*) FROM game1`), va khoi `=== Kiem tra sau ===` in:
```
game1 co 7 cot cu > 0            : 0
game1 co tianfu_power > 0         : 0
player_tianfu_log kind=migrate    : 14
playerdaoju djid=114              : 14
```
`exit 0`. **Neu exit 1** → script da tu kiem tra sau va bao `LOI` — restore:
```bash
mysql -u root -pwww.aae.ink --default-character-set=utf8 game < bak/tianfu_pre_<nhan>.sql
```

6d. Chay `--apply` lan 2 ngay (kiem idempotent tren DB that):

```bash
"$P" sql/migrate_tianfu.php --apply | tail -3
"$P" -e 2>/dev/null; echo "exit=$?"
mysql -u root -pwww.aae.ink --default-character-set=utf8 -N -B game -e \
  "SELECT COUNT(*) FROM game1 WHERE tf > 0; SELECT COUNT(*) FROM player_tianfu_log WHERE kind='migrate';"
```

Expected: lan 2 in `OK=0 FAIL=0 SKIP=14`, so dong `kind='migrate'` **khong doi** (van 14).

6e. Kiem tra truc tiep DB — 3 cau hoi cua spec §7.5:

```bash
mysql -u root -pwww.aae.ink --default-character-set=utf8 game -e "
SELECT uname, ulv, shenfen, tf, tfhp, tfgj, tffy, tfbj, tfxx, tfxy, tfsb,
       tianfu_id, tianfu_quality, tianfu_lv, tianfu_power
FROM game1 ORDER BY ulv DESC;"
mysql -u root -pwww.aae.ink --default-character-set=utf8 game -e "
SELECT sid, djid, djsum FROM playerdaoju WHERE djid = 114;"
mysql -u root -pwww.aae.ink --default-character-set=utf8 game -e "
SELECT kind, COUNT(*) FROM player_tianfu_log GROUP BY kind;"
```

Expected: **moi dong** `game1` co `tf > 0` va **7 cot cu deu 0**; `tianfu_id/quality/lv/power` deu
`0` (chua ai khai phu); `playerdaoju` co **1 dong moi nhan vat** voi `djsum = 1`.

- [ ] **Step 7: Nhin bang mat — 14 nhan vat phai thay doi that trong game**

Khong chi tin so lieu DB. Mo game that:

7a. Kiem server dev dang chay (cong **8091** — 8090 bi `WsToastNotification.exe` chiem):

```bash
curl -s -o /dev/null -w "index.php HTTP=%{http_code} SIZE=%{size_download}\n" http://127.0.0.1:8091/index.php
```

Expected: `HTTP=200 SIZE=4148` (hoac SIZE > 1000). Neu HTTP khac 200 → khoi dong lai:
```bash
cd D:/XXJserver/phpstudy_pro/WWW && "$P" -S 127.0.0.1:8091 >/dev/null 2>&1 &
```

7b. Lay `sid` cua nhan vat **PhatDay** (nhan vat bi anh huong nang nhat — `ubj` −88.5% o Phuong an A):

```bash
mysql -u root -pwww.aae.ink --default-character-set=utf8 -N -B game -e \
  "SELECT sid, uname, ulv, tf FROM game1 WHERE uname LIKE 'Phat%' ORDER BY ulv DESC LIMIT 3;"
```

7c. Tao `cmd` cho `tianfu_open.php` va mo trinh duyet. **`?cmd=` dung mot lan** → phai encode lai
moi request (xem §1 muc 7):

```bash
SID=<sid lay tu 7b>
ENC=$("$P" -r '$R="D:/XXJserver/phpstudy_pro/WWW"; require $R."/class/encode.php";
$e = new encode(); echo $e->encode("cmd=tianfu_open&sid=" . $argv[1]);' "$SID")
echo "http://127.0.0.1:8091/game.php?cmd=$ENC"
```

7d. Mo bang Chrome **truc tiep qua CLI** (khong dung browser-use MCP):

```bash
powershell -Command "Start-Process 'chrome.exe' -ArgumentList 'http://127.0.0.1:8091/game.php?cmd=<ENC>'"
```

Expected thay bang mat:
- Trang **Khai Phu** hien thi, co nut "Dung Khai Phu Dan", so dan hien **1**.
- Bam khai phu → duoc 1 thien phu ngau nhien (khong phai trang trang).
- Man **Phu Cac** (`cmd=tianfu`) hien 4 o, o 1 da bat sang.
- Man **Trang thai** (`cmd=zhuangtai`) hien chu phu + Phu Vi.
- Khong co chu CJK lac, khong nut bi `.main button` de bet mau, khong layout vo o 390px.

**Chup anh lam bang chung** (luu vao `docs/img/thienphu/`, duoc commit o Step 12):

```bash
mkdir -p docs/img/thienphu
for W in 390 840; do
  powershell -Command "& 'C:\Program Files\Google\Chrome\Application\chrome.exe' --headless=new \
    --disable-gpu --window-size=$W,1400 --hide-scrollbars \
    --screenshot='D:/XXJserver/phpstudy_pro/WWW/docs/img/thienphu/tianfu_open-$W.png' \
    'http://127.0.0.1:8091/game.php?cmd=<ENC>'"
  ls -la docs/img/thienphu/tianfu_open-$W.png
done
```

> **Do chinh xac**: dat `body.style.zoom = 1` truoc khi do (trang co `zoom:.75` tai ≥1024px —
> `css/mystyle.css:5623-5628`). Chrome headless `--window-size` **toi thieu 500px** chieu ngang;
> 390px se render trong iframe. Dung `Range` header de do noi dung dai (scrollWidth noi doi).
> **Anh phai la tieng Viet** — neu tren anh xuat hien chu tieng Anh/Trung thi do la loi, khong
> phai "chua dich xong".

Expected: 2 file PNG **lon hon 30 KB**. Mo ra xem bang mat, doi chieu voi cac muc tren. **Neu khong
the mo duyet bang mat** (khong co man hinh) → ghi ro vao Step 13: *"Step 7 CHUA kiem chung thi
giac — chi kiem bang so lieu DB va `curl LEN > 500`."* **Khong duoc noi "da test" khi chua nhin.**

- [ ] **Step 8: Viet `docs/UI_THIENPHU.md` — tai lieu nguon chinh**

Lay khung **12 muc** cua `docs/UI_HUYETMACH.md` (553 dong). Day la tai lieu se duoc dung lai
6 thang sau, khong phai bao cao nop bai — **moi thu chua do bang so thi phai ghi chu `CHƯA`**,
theo le "danh dau ro CHUA test".

Cau truc bat buoc:

| Muc | Noi dung | Nguon so lieu |
|---|---|---|
| `## 0. Tra loi nhanh` | Thien Phu de lam gi, co tac dung that khong | spec §0 |
| `### Co tac dung that — da do` | Bang: chi so → cong vao dau → do bang lenh nao | Step 6e, Step 13b cua Task 11 |
| `### Phan phai noi thang` | Gi **gan nhu vo dung / chua co bang chung** | xem 4.1 duoi |
| `## 1. Boi canh` | Vi sao lam lai; thien phu cu 2006-style bi gi | spec §1 |
| `## 2. File da sua / da tao` | Bang: duong dan → vai tro → tracked/untracked | File Structure cua plan nay |
| `## 3. Cau truc trang` | 4 man: Khai Phu / Phu Cac / Phu Vi / Suu Tam; ban do selector → file:dong | Task 5-9 |
| `## 4. Logic server-side` | 4.1 chu ky ham da produce; 4.2 `tianfu_percent_calc`; 4.3 duong cong vao nhan vat; 4.4 **tran that ≠ con so trong config** | Task 2-3, Task 11 |
| `## 5. Anh da sinh` | Bang: ten file → kich thuoc → dung o dau → md5 | `docs/img/thienphu/` |
| `## 6. Rang buoc CSS phai giu (MOI)` | `a:link`, `.main button`, `.break-bless`, `zoom:.75` | spec §1 muc 4-6 |
| `## 7. Bug da tim ra va cach sua` | Moi bug: trieu chung → nguyen nhan → file:dong → cach sua | nhat ky thuc hien |
| `## 8. Bug CU co san — CHUA sua` | Chu du an quyet | xem 4.2 duoi |
| `## 9. Quy trinh kiem chung (lap lai duoc)` | Khoi lenh day du: server, lint, test, chup anh; **9.1 so da do** | Step 6, Step 7, Step 11 |
| `## 10. No con lai — CHUA test / CHUA lam` | 10.1 chua test trong game that; 10.2 chua lam / chua chot | Step 13 cua Task 12 |
| `## 11. Bang tra nhanh` | Hoi → dap → cho mo ra xem | tu viet |
| `## 12. Thang hieu ung phan bac` | `tp-fx0…fx4` theo pham chat; **bac thap nhat van phai thay gi do** | Task 7 |

**4.1 — 4 diem phai noi thang o muc 0** (da co so lieu, khong duoc lam mo):

1. **Cong roi do nhan doi**: `tfxy` bi `game/boss.php:454` doc de tinh `mt_rand(1, 120 - $xyz)`;
   thien phu moi cung nhan roi do. Con that: `round(round(120 * 1.15) * 1.30) = 179` tu goc 120
   → **+49.17%**, khong phai `1.15 × 1.30 = +49.5%` nhu tinh nhan (2 lan `round()` an bot).
   **CHƯA do trong game that** — ghi ro day la tinh toan, khong phai ket qua combat log.
2. **Tran dot pha moi 99%**: `cap_percent = 30` trong config, nhung `luonden.php:348` va
   `:898` dung 2 nguong khac nhau → tran that co the khong phai 30%. **CHƯA do** — phai chay
   `--measure` de ra so.
3. **Phu Vi (`tianfu_power`) khong doi chien luc**: `combat_power()` chi doc 4 cot mirror; can
   xac nhan `tianfu_power` co nam trong `$weights` hay khong. Ghi ro ket qua Task 11 Step 13.
4. **`tfsb` van la cot chet**: 0 noi doc. Da quyet **khong noi vao van khi** (Ne Tranh thuoc
   Am Kieu Mach — `class/function/huyetmach.php:1112`). Giu cot, khong drop.

**4.2 — it nhat 3 muc cho `## 8. Bug CU co san — CHUA sua`**:

| Bug cu | Cho | Vi sao chua sua |
|---|---|---|
| `upplayerlv()` van cap 6 cot `tf*` theo `shenfen` | `class/player.php:1199-1215` | Sau Phuong an A, 6 cot nay **khong con tac dung** nhung van ton tai → ton DB, gay hieu nham. Can chu du an quyet: xoa han hay giu de tich luy `tf` cho dot hoan sau |
| `use_dj()` cu cap 2 lan dung/loai | `class/player.php:2058-2087` | Can cot `djuse` ma `daoju_config.json` (109-113) khong co → luong Khai Phu Dan thuc te dang **bich**. Da lach qua bang `deledjsum(114, 1, $sid, $dblj)` truc tiep trong `tianfu_open()` (spec §1 muc 13) |
| `paihang.php` xep hang theo chien luc cu | `game/paihang.php:17-19` | Task 11 Step 14 da doi trong so; **bang xep hang sat tuc ngay migration** → nguoi choi thay thu tu dao lon. Can bao truoc |

**Yeu cau hinh thuc**:
- File viet **tieng Viet co dau** (khac voi plan nay — plan khong dau de tranh Edit chen ky tu loi).
  Sau khi viet, **bat buoc** chay quet ky tu o Step 10 (file `.md` cho phep CJK = 0).
- Moi bang phai co **cot `file:dong`** de nguoi doc 6 thang sau con tim duoc.
- `## 9.1 So da do` phai la **bang so that copy tu terminal**, khong phai mo ta. Mau:
  ```
  | Chi so | Gia tri | Ngay do | Lenh |
  |---|---|---|---|
  | tong nhan vat game1 | 14 | 2026-10-14 | mysql ... "SELECT COUNT(*) FROM game1" |
  | tong diem hoan | 410 | 2026-10-14 | php sql/migrate_tianfu.php --dry-run |
  ```

> **KHONG duoc viet "da test day du"** neu Step 7 chua nhin bang mat. Ghi: *"CHƯA kiem chung thi
> giac — moi chi kiem bang so lieu DB va `curl LEN > 500`."* Mot mon no chua test duoc ghi nhan
> la no, khong phai la "thieu sot nho".

- [ ] **Step 9: Viet `docs/DEPLOY_PRODUCTION_THIENPHU.md` — chi viet, KHONG chay**

Lay khung `docs/DEPLOY_PRODUCTION_HUYETMACH.md` (242 dong, 7 muc). **Vong nay khong deploy
production** — file nay la runbook de chu server tu chay khi san sang.

7 muc bat buoc:

1. **`## 1. Bang/cot CHUA ton tai o production`** — liet ke `player_tianfu`, `player_tianfu_log`,
   4 cot `game1.tianfu_*`, daoju `114`. Ghi ro: **`sql/sql_tianfu.sql` dung `CREATE TABLE IF NOT
   EXISTS`** nen chay lan 2 khong loi; MySQL 5.5 **khong ho tro** `DEFAULT CURRENT_TIMESTAMP` cho
   DATETIME → dung `DEFAULT NULL` + ghi `date()` phia PHP.
2. **`## 2. Viec can chu server quyet TRUOC khi bam chay`** — 4 cau hoi:
   - Phuong an **A hay C** cho `class/player.php:575-580` (Step 1). Production se co **con so khac
     local** (local: 14 nhan vat, `ubj` −88.5% cho PhatDay) → phai chay `_tp_legacy.php` tren
     production truoc de ra so that.
   - Co **bao truoc** bang xep hang sat tuc khong (`paihang.php` doi trong so).
   - `tf` hoan co **tran** khong — production co the co nhan vat `tf` hang chuc nghin.
   - `tfsb` co duoc phep **noi vao van khi** trong tuong lai khong (hien: khong, do trung Ne Tranh).
3. **`## 3. Trinh tu chay`** — khoi lenh day du: `0) SAO LƯU` (dump 3 bang + `game1`, **khong**
   dump ca DB) → `1) KIEM HIEN TRANG` (chi doc: `information_schema` cho 2 bang + 4 cot,
   `SELECT VERSION(), @@sql_mode`) → `2) TAO 2 BANG MOI` → `3) KIEM TRA LAI` (bat buoc ra
   `Hang schema con thieu/sai: 0`) → `4) dry-run` → `5) apply` → `6) kiem tra sau`.
4. **`## 4. Copy code (PHP 5.6, khong build)`** — bang: file → duong dan production → co can
   sua config khong. Nhan manh: **`class/config/daoju_config.json` phai duoc copy**, neu khong
   `\player\getdaoju(114)` tra `false` va migration `exit(3)`.
5. **`## 5. Nghiem thu sau deploy (bat buoc bam tay)`** — checklist: dang nhap 1 nhan vat that →
   mo `cmd=tianfu_open` → thay so dan = 1 → khai phu → mo `cmd=tianfu` → 4 o, o 1 sang →
   `cmd=zhuangtai` thay chu phu → **kiem chien luc truoc/sau** phai khong doi qua ±5%.
6. **`## 6. Rollback`** — `mysql ... game < bak/tianfu_pre_<nhan>.sql` + **hoan tac code**
   (`git revert <commit>`), ghi ro: **khong duoc** rollback code ma giu DB da migrate (7 cot da
   ve 0 → nhan vat mat het thien phu cu).
7. **`## 7. Nhung diem trong phat hien khi lam tai lieu`** — it nhat 3 muc, lay tu Step 8 muc 8.

> **Tuyet doi khong chay bat cu lenh nao trong file nay tren production o vong nay.** Chi viet.
> Ghi dau file: `> TRANG THAI: RUNBOOK — CHƯA CHAY. Cap nhat lan cuoi: 2026-10-14.`

- [ ] **Step 10: Quet ky tu lac + lint TOAN BO file da dung**

**Bat buoc chay that, khong duoc bo qua.** Edit/Write de chen smart quote va CJK lac cho.

10a. Lint moi file PHP da tao/sua:

```bash
P=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
for f in class/function/tianfu.php class/player.php game/tianfu.php game/tianfu_open.php \
         game/tianfu_collection.php game.php game/xiulian.php game/zbinfo.php game/tupo.php \
         game/paihang.php game/zhuangtai.php game/pve.php game/boss.php \
         class/function/luonden.php cron/stamina_recovery.php \
         sql/migrate_tianfu.php tests/test_tianfu_gates.php tests/test_tianfu_migration.php; do
  printf "%-42s " "$f"
  [ -f "$f" ] && "$P" -l "$f" || echo "THIEU FILE"
done
```

Expected: **moi dong** `No syntax errors detected in <file>`. Bat ky `Parse error` / `THIEU FILE`
nao → dung, sua.

> **Lam bang chinh `php5.6.9nts`**, khong dung `php` tren PATH (co the la 8.x — se chap nhan
> `??`, `<=>`, arrow fn ma production se chet).

10b. Quet ky tu lac — 2 nhom file, ky vong khac nhau:

```bash
echo "=== NHOM 1: file MOI/da viet lai — ky vong 0 ky tu CJK, 0 smart quote"
for f in class/function/tianfu.php sql/migrate_tianfu.php \
         tests/test_tianfu_gates.php tests/test_tianfu_migration.php \
         game/tianfu.php game/tianfu_open.php game/tianfu_collection.php \
         css/mystyle.css docs/UI_THIENPHU.md docs/DEPLOY_PRODUCTION_THIENPHU.md; do
  printf "%-42s cjk=%s sq=%s\n" "$f" \
    "$(grep -cP '[\x{2E80}-\x{9FFF}\x{3000}-\x{303F}]' "$f")" \
    "$(grep -cP '[\x{2018}\x{2019}\x{201C}\x{201D}]' "$f")"
done
```

Expected: **moi cot `cjk=0` va `sq=0`**. Neu khac 0 → in ra dong do (`grep -nP …`), tim ky tu Edit
da chen sai, sua lai **bang tay** (dung Edit de go lai chu dung — Edit chinh la thu gay loi, nen
phai doc lai sau khi sua).

```bash
echo "=== NHOM 2: file CO SAN comment CJK goc — so dong phai BANG baseline"
for f in game/xiulian.php game/pve.php game/boss.php; do
  if git ls-files --error-unmatch "$f" >/dev/null 2>&1; then
    BASE=$(git show HEAD:"$f" | grep -cP '[\x{2E80}-\x{9FFF}]')
  else
    BASE=$(grep -cP '[\x{2E80}-\x{9FFF}]' "E:/window/Temp/_tp_base/$(basename $f)")
  fi
  NOW=$(grep -cP '[\x{2E80}-\x{9FFF}]' "$f")
  printf "%-24s HIENTAI=%s  BASELINE=%s\n" "$f" "$NOW" "$BASE"
done
```

Expected: `game/xiulian.php` va `game/pve.php` → `HIENTAI == BASELINE`.
`game/boss.php` → `HIENTAI == BASELINE - 1` (Task 5d xoa dung 1 dong CJK).
`HIENTAI > BASELINE` o bat ky file nao → **Edit da chen ky tu lac**, sua truoc khi chay tiep.

10c. Kiem UTF-8 hop le (file hu encoding se lam Apache tra trang trang):

```bash
for f in class/function/tianfu.php game/tianfu.php docs/UI_THIENPHU.md; do
  printf "%-40s " "$f"
  iconv -f UTF-8 -t UTF-8 "$f" >/dev/null 2>&1 && echo "UTF-8 OK" || echo "UTF-8 HU"
done
```

Expected: 3 dong `UTF-8 OK`.

- [ ] **Step 11: Chay TOAN BO test suite — khong duoc bo qua bat cu test nao**

```bash
P=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
for t in tests/test_*.php; do
  echo "=================== $t"
  "$P" "$t" 2>&1 | tail -6
done
```

Expected: **khong co dong `FAIL`**, khong `Fatal error`, khong `Parse error`. 20 file test.

> **Cac test can `--live`** (`test_huyetmach_combat`, `test_linhcan_combat`, `test_linhcan_passive`,
> `test_linhcan_pages`, `test_tianfu_pages`) se in `SKIP` hoac bao thieu fixture — **chap nhan
> duoc** o buoc nay. Sau do chay rieng voi `--live` khi server 8091 dang song:
> ```bash
> for t in tests/test_huyetmach_combat.php tests/test_linhcan_combat.php \
>          tests/test_linhcan_passive.php tests/test_linhcan_pages.php tests/test_tianfu_pages.php; do
>   echo "=========== $t --live"
>   "$P" "$t" --live 2>&1 | tail -8
> done
> ```

> **Neu `test_huyetmach_*` hoac `test_nguhanh*` FAIL** sau Step 3 (xoa `:575-580`) hoac Step 6
> (migration) → do la **hoi qui that**, khong phai "test cu von dieu". Task 11 sua
> `settle_stamina()` (moi trang goi) va `combat_power()`; Task 12 Step 3 sua `getplayer()`.
> **Dung lai, tim nguyen nhan. Khong sua test cho PASS.**

> **Neu test FAIL vi thieu fixture** (khong phai hoi qui code) → ghi vao Step 13:
> *"`<ten test>` FAIL do thieu fixture `<ten>`, khong phai loi code — CHƯA kiem chung."*
> **Khong duoc im lang bo qua.**

- [ ] **Step 12: Commit D — migration + tai lieu**

```bash
git status --short
```

**Doc ky output truoc khi add.** Ky vong thay:
- `?? sql/migrate_tianfu.php`, `?? tests/test_tianfu_migration.php`, `?? docs/UI_THIENPHU.md`,
  `?? docs/DEPLOY_PRODUCTION_THIENPHU.md`, `?? docs/img/thienphu/tianfu_open-390.png`,
  `?? docs/img/thienphu/tianfu_open-840.png`
- ` M class/player.php` (**chi khi Phuong an A**)
- `?? game/tianfu.php`, `?? game/tupo.php`, `?? game/zbinfo.php`, `?? game/xiulian.php`,
  `?? game/paihang.php`, `?? game/shangdian.php`, `?? class/function/luonden.php`,
  `?? cron/stamina_recovery.php` — **8 file nay lan dau vao git**, da bao o Step 13

**Tuyet doi khong:**
- `git add -A` / `git add .` — repo root co **33 file `shot_*.png` rao** khong lien quan
- add `bak/tianfu_pre_*.sql` — **du lieu nguoi choi that**, khong bao gio commit
- add `tongquan.php` — thuoc commit C cua Task 11 Step 15, va **chi sau khi hoi chu du an**

```bash
git add sql/migrate_tianfu.php tests/test_tianfu_migration.php \
        docs/UI_THIENPHU.md docs/DEPLOY_PRODUCTION_THIENPHU.md \
        docs/img/thienphu/tianfu_open-390.png docs/img/thienphu/tianfu_open-840.png
# CHI khi Phuong an A:
git add class/player.php
git status --short   # doc lai lan 2 — xac nhan KHONG co bak/, KHONG co shot_*.png
git commit -m "$(cat <<'EOF'
feat(tianfu): migration hoan diem + phat Khai Phu Dan + tai lieu (Task 12)

- sql/migrate_tianfu.php: hoan 7 cot game1.tf* cu ve `tf`, phat 1 Khai Phu Dan
  (djid 114), ghi player_tianfu_log(kind='migrate') lam guard idempotent —
  bat buoc vi upplayerlv() van cap 6 cot cu moi lan len cap.
- Mot transaction moi nhan vat; FAIL => rollBack + DUNG HET (spec §7.4).
- Doi ERRMODE_WARNING -> ERRMODE_EXCEPTION vi pdo.php nuot im moi loi DB.
- tests/test_tianfu_migration.php: 7 muc A-G, gom "chay 2 lan khong hoan trung".
- docs/UI_THIENPHU.md: 12 muc theo khung UI_HUYETMACH.md.
- docs/DEPLOY_PRODUCTION_THIENPHU.md: RUNBOOK — CHƯA CHAY.
EOF
)"
git log --oneline -1
git status --short
```

Expected: commit tao thanh cong; `git status --short` **van con** `?? game/tianfu.php` va 7 file
untracked kia (chung da duoc add o Task 11 Step 14/15 — neu van con `??` o day thi Task 11 chua
commit du, quay lai kiem). Con lai chi `?? shot_*.png` va ` M tongquan.php`.

- [ ] **Step 13: SỔ NỢ CHỜ CHỦ DỰ ÁN + bàn giao**

Day la **san pham chinh cua Task 12**, ngang hang voi migration. Khong phai "tom tat da lam gi" —
ma la **bang nhung gi con no**, moi dong co nguoi phai quyet va hau qua neu quyet sai.
Theo le "danh dau ro CHUA test": **mot mon no chua test duoc ghi nhan la no**, khong phai thieu sot nho.

**13a. In ra ban giao** — viet vao `docs/UI_THIENPHU.md`, muc `## So no cho chu du an`
(dung khung `docs/UI_HUYETMACH.md:471-527`, co san "Cach dung bang nay").

Bang bat buoc, **5 cot**:

```
| # | Mon no | Cho (file:dong) | Ai quyet | Hau qua neu quyet sai / neu khong quyet |
```

**Noi dung toi thieu — 9 dong, khong duoc bot:**

| # | Mon no | Cho | Ai quyet | Hau qua |
|---|---|---|---|---|
| 1 | **Phuong an A hay C** cho khoi thien phu cu | `class/player.php:575-580` | Chu du an (Step 1) | **A**: `PhátĐây` ubj 61→7 (−88.5%), `tttttt` uxx 22→0 (−100%) — bao kich/hut mau tut nang, **khong lay lai duoc** sau khi nguoi choi tieu `tf`. **C**: 7 cot cu van "song", trai y spec §6 |
| 2 | **`paihang.php` A hay B** — giu ham rieng hay goi `\player\combat_power()` | `game/paihang.php:10-21`, goi duy nhat `:67` | Chu du an (Task 11 Step 13b) | **A** (plan chon): bang xep hang **van thieu** 4 trong so Nhuc Than/Mach Huyet → top rank sai so voi chien luc that. **B**: dung mot cong thuc, nhung **mo rong pham vi** ngoai spec |
| 3 | **`luonden.php` hien thi ≠ roll that** | `:348` (hien thi ty le) vs `:898` (roll that) | Chu du an | Nguoi choi thay "30%" nhung roll that khac → **cam giac bi lua**. Khong tu sua vi phai chon: sua hien thi theo roll, hay sua roll theo hien thi (doi can bang kinh te) |
| 4 | **Bang xep hang sat tuc ngay migration** | `game/paihang.php:17-19` | Chu du an | Chien luc tinh lai theo trong so moi → thu tu dao lon, nguoi choi thay "mat hang". Can **bao truoc** trong game |
| 5 | **`upplayerlv()` van cap 6 cot `tf*` cu** | `class/player.php:1199-1215` | Chu du an | Sau Phuong an A: diem tang ma **vo tac dung** → nguoi choi tuong duoc thuong. Sau Phuong an C: song binh thuong. Chua sua vi nam ngoai spec |
| 6 | **`use_dj()` cu dang bich Khai Phu Dan** | `class/player.php:2058-2087` (can cot `djuse`, `daoju_config.json:109-113` khong co) | Chu du an | Da lach qua bang `deledjsum(114,1,…)` truc tiep. Neu sau nay them dan khac qua `use_dj` → **lai bich**. No ky thuat, chua tra |
| 7 | **Boss thien phu rieng (phase 2)** | chua viet | Chu du an | Thien phu **khong co duong nao de danh dau** nhu Mach Huyet co (`huyetmach_boss`) → nguon `tianfu_id` cao cap chi tu `Khai Phu Dan`. Chua lam vi spec chi dinh 1 phase |
| 8 | **Production CHƯA migration** | `docs/DEPLOY_PRODUCTION_THIENPHU.md` | Chu server | 4 cot `game1` + 2 bang moi + daoju 114 + **no schema sid 15 bang** (`docs/DEPLOY_PRODUCTION_NGUHANH.md`). Deploy ma bo qua → trang Thien Phu trang tinh |
| 9 | **Repo khong co `.gitignore`** | goc repo (`ls .gitignore` -> khong ton tai), do that 2026-10-14 | Chu du an | Dump DB (`bak/tianfu_pre_*.sql`) chua **du lieu nguoi choi that** va **khong bi ignore**. Task 12 Step 6a bao lac bang `.git/info/exclude` (chi local, khong commit). Muon co `.gitignore` that thi chu du an tu chon danh sach — ke hoach nay **khong tu tao** vi anh huong toan repo (nam ngoai spec)

**13b. Bang chien luc 13d — chep nguyen van vao ban giao**

Task 11 Step 13d in ra bang 10 dong (`uname / ulv / CU(tf*50..) / MOI(combat) / MOI(paihang) / lech`).
**Chep nguyen bang do** vao muc `## 9.1 So da do` cua `docs/UI_THIENPHU.md`, kem 3 cau ket luan
bat buoc:

1. `MOI(combat)` **lon hon** `CU` o nhan vat co Nhuc Than/Mach Huyet — vi `combat_power()` them
   4 trong so (`thechat_quality*200`, `thechat_awaken_stage*500`, `huyetmach_quality*250`,
   `huyetmach_awaken_stage*600`). Day la **sua loi co san**, khong phai buff.
2. `MOI(paihang)` **nho hon** `CU` vi `tianfu_power = 0` cho den khi migration chay **va** nguoi
   choi khai phu. Tuc: **giua luc migrate va luc khai phu, chien luc tren bang xep hang tut.**
   → phai bao truoc (mon no #4).
3. Con so **lech lon nhat** trong bang = bao nhieu %. Neu **> 20%** → dung ban giao, hoi chu
   du an co chap nhan duoc khong. **Khong tu quet duoi tham.**

**13c. Thong bao: 8 file LAN DAU vao git**

> **BAO TRUOC CHO CHU DU AN — day la he qua, khong phai quyet dinh cua nguoi thuc thi.**
>
> Repo nay **chi track 98 file** (`git ls-files | wc -l`). Tam file ma plan nay sua **chua tung duoc
> track**, va Task 11 Step 14/15 + Task 12 Step 12 se **lan dau tien dua chung vao git**:
>
> | File | Vi sao phai sua | Task |
> |---|---|---|
> | `game/tianfu.php` | viet lai hoan toan thanh man Phu Cac | 8 |
> | `game/xiulian.php` | cong `stamina` vao man Tu Luyen | 11 |
> | `game/zbinfo.php` | hien `%` thien phu tren tooltip trang bi | 11 |
> | `game/tupo.php` | tran dot pha `min(99, …)` | 11 |
> | `game/paihang.php` | trong so chien luc | 11 |
> | `game/shangdian.php` | ban Khai Phu Dan | 4 |
> | `class/function/luonden.php` | cong 3 (drop) + cong 7 (breakthrough) | 11 |
> | `cron/stamina_recovery.php` | hoi `stamina` theo thien phu | 11 |
>
> **He qua**: lich su git cua 8 file nay **bat dau tu con so 0** — khong co `git log`/`git blame`
> truoc do, va **khong co "nen" de `git diff`**. Vi vay plan dung **thu tuc snapshot**
> (`E:/window/Temp/_tp_base/`) thay cho `git diff --stat` o Task 11 Step 0 va Task 12 Step 2.
>
> **Khong tu quyet track them file khac ngoai 8 file nay.** Neu ban thay file khac can `git add`,
> **dung lai va hoi** (le `feedback-khong-mo-rong-pham-vi-sua`).

**13d. Cross-reference vao `docs/UI_HUYETMACH.md`**

Them **dung 1 dong** vao muc `## 10. No con lai` cua `docs/UI_HUYETMACH.md` (khong sua gi khac —
file nay da track, da ban giao, **khong mo rong pham vi**):

```markdown
- 2026-10-14: Hệ **Thiên Phú** đã làm lại xong local — xem `docs/UI_THIENPHU.md`. Ảnh hưởng chéo
  tới MẠCH HUYẾT: công 3 (drop) cộng dồn `+49.5%` danh nghĩa / `+49.17%` thật (2 lần `round`),
  và `combat_power()` đã bỏ `tf/tfxy/tfsb` khỏi `$weights`.
```

```bash
cp docs/UI_HUYETMACH.md E:/window/Temp/_uihm_before.md
# Edit tay bang Edit tool, KHONG dung sed (file nay co dau tieng Viet — sed de lam hu encoding)
git diff --stat -- docs/UI_HUYETMACH.md
diff -u E:/window/Temp/_uihm_before.md docs/UI_HUYETMACH.md | grep -c '^+[^+]'
diff -u E:/window/Temp/_uihm_before.md docs/UI_HUYETMACH.md | grep -c '^-[^-]'
iconv -f UTF-8 -t UTF-8 docs/UI_HUYETMACH.md >/dev/null && echo "UTF-8 OK"
```

Expected: `git diff --stat` = `3 insertions(+), 0 deletions(-)` (3 dong = 1 bullet 3 dong);
`grep -c '^+[^+]'` = **3**; `grep -c '^-[^-]'` = **0**; `UTF-8 OK`.

> **Neu Edit chen ky tu loi** (smart quote, CJK lac) → `docs/UI_HUYETMACH.md` **hong encoding**,
> mo tren web se thay chu oi. Luon chay `iconv` sau khi Edit file co dau.

**13e. Kiem tra cuoi cung truoc khi ban giao** — 6 cau lenh, **chay that, in that**:

```bash
cd /d/XXJserver/phpstudy_pro/WWW
P=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe
echo "1) git log 5 commit gan nhat:";          git log --oneline -5
echo "2) file con chua commit:";               git status --short | grep -v 'shot_.*\.png'
echo "3) schema 2 bang + 4 cot:"
mysql -u root -pwww.aae.ink --default-character-set=utf8 -N -B game -e "
  SELECT TABLE_NAME FROM information_schema.TABLES
   WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE 'player_tianfu%';
  SELECT COLUMN_NAME FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='game1' AND COLUMN_NAME LIKE 'tianfu_%';"
echo "4) migration da chay:";                  mysql -u root -pwww.aae.ink -N -B game -e \
  "SELECT COUNT(*) FROM player_tianfu_log WHERE kind='migrate'"
echo "5) Khai Phu Dan da phat:";               mysql -u root -pwww.aae.ink -N -B game -e \
  "SELECT COUNT(*) FROM playerdaoju WHERE djid=114"
echo "6) toan bo test:"
for t in tests/test_*.php; do printf "%-40s " "$t"; "$P" "$t" 2>&1 | tail -1; done
```

Expected:
1. **4 commit** Thien Phu (Task 11A, 11B, 11C/15, Task 12) — ten commit bat dau `feat(tianfu)`
   hoac `docs(tongquan)`.
2. Chi con `?? shot_*.png` (da loc ra) va **co the** ` M tongquan.php` neu chu du an chua cho
   commit C. **Khong con** file nao khac.
3. **2 bang** (`player_tianfu`, `player_tianfu_log`) + **4 cot** (`tianfu_id`, `tianfu_quality`,
   `tianfu_lv`, `tianfu_power`).
4. `= so nhan vat` (14 o local ngay 2026-10-14).
5. `= so nhan vat` (14).
6. **Khong dong nao** chua `FAIL` / `Fatal` / `Parse error`.

**Neu bat cu muc nao sai → KHONG ban giao.** Tim nguyen nhan. `verification-before-completion`:
**bang chung truoc, ket luan sau.**

**13f. Noi dung tin nhan ban giao cho chu du an** (nguyen van, khong tom tat):

```
Thiên Phú — làm lại xong trên local (CHƯA deploy production).

ĐÃ LÀM (4 commit):
- 18 thiên phú / 10 phẩm / 7 cống hiến / 4 mốc / 4 ô Phú Vị — config + DB + 4 màn hình
- Chiến lực: combat_power() bỏ tf/tfxy/tfsb, thêm tianfu_power
- Trang Trạng thái hiện chủ phú + Phú Vị
- tongquan.php: mục Thiên Phú, nav đếm 43
- Migration đã chạy trên 14 nhân vật local: hoàn 410 điểm, phát 14 Khai Phú Đan

CHƯA KIỂM CHỨNG THỊ GIÁC (nói thẳng):
- <ghi đúng những gì Step 7 chưa nhìn bằng mắt được>

8 FILE LẦN ĐẦU VÀO GIT: game/tianfu.php, xiulian.php, zbinfo.php, tupo.php, paihang.php,
shangdian.php, class/function/luonden.php, cron/stamina_recovery.php — lịch sử git của chúng
bắt đầu từ 0. Tôi KHÔNG tự track thêm file nào khác.

4 CÂU HỎI CẦN ANH QUYẾT (chi tiết + hậu quả trong docs/UI_THIENPHU.md mục "Sổ nợ"):
1. Phương án A (xoá class/player.php:575-580 đúng spec) hay C (giữ)?
   A làm PhátĐây mất 88.5% bạo kích (ubj 61→7), KHÔNG lấy lại được.
2. paihang.php: giữ hàm riêng (bảng xếp hạng vẫn thiếu Nhục Thân/Mạch Huyết) hay gọi
   combat_power() (đúng nhưng mở rộng phạm vi)?
3. luonden.php:348 (hiển thị) ≠ :898 (roll thật) — sửa bên nào?
4. Bảng xếp hạng sẽ xáo trộn ngay khi deploy — có báo trước trong game không?

Production: docs/DEPLOY_PRODUCTION_THIENPHU.md là RUNBOOK, tôi CHƯA chạy.
Repo: KHÔNG có .gitignore (tôi không tự tạo — sẽ ảnh hưởng toàn repo). Dump DB
ở bak/ được bọc bằng .git/info/exclude, chỉ có hiệu lực máy này.
Đường lui: bak/tianfu_pre_<nhân>.sql + git revert.
```

> **Khong duoc viet "da test day du"** neu Step 7 chua nhin bang mat. **Khong duoc** om mon no
> #1 (Phuong an A/C) vao commit — Step 3 **phai** cho cau tra loi truoc khi xoa.
