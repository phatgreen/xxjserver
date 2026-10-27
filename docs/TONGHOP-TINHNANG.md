# TONG HOP TINH NANG GAME - TAM TIEN KY

> Phien ban: 1.1  
> Ngay tong hop: 22/07/2026  
> Cap nhat gan nhat: 04/10/2026 (muc 6 Trang Bi: Tham luan Ngu Hanh, 16 o theo ten that, cache anh; chi tiet trong `docs/CHANGELOG.md`)  
> Tong so he thong lon: ~35  
> Tong so file PHP: 100+  
> Tong so dong code (player.php): 2800+

---

## MUC LUC

1. [Tai Khoan & Dang Nhap](#1-tai-khoan--dang-nhap)
2. [Nhan Vat & Chi So](#2-nhan-vat--chi-so)
3. [Cap Do & Dot Pha](#3-cap-do--dot-pha)
4. [Ban Do & Di Chuyen](#4-ban-do--di-chuyen)
5. [He Thong Chien Dau](#5-he-thong-chien-dau)
6. [Trang Bi](#6-trang-bi)
7. [Vo Cong](#7-vo-cong)
8. [Cong Phap](#8-cong-phap)
9. [Thien Phu](#9-thien-phu)
10. [Linh Can (Nguyen To)](#10-linh-can-nguyen-to)
11. [The Chat](#11-the-chat)
12. [Huyet Mach](#12-huyet-mach)
13. [Tu Luyen (AFK)](#13-tu-luyen-afk)
14. [Cua Hang](#14-cua-hang)
15. [Phuong Thi (Market)](#15-phuong-thi-market)
16. [Che Tao & Luyen Dan](#16-che-tao--luyen-dan)
17. [Nhiem Vu](#17-nhiem-vu)
18. [Sunh Vat (Pet)](#18-sung-vat-pet)
19. [Mon Phai (Clan)](#19-mon-phai-clan)
20. [Hao Huu (Ban Be)](#20-hao-huu-ban-be)
21. [Lieu Thien (Chat)](#21-lieu-thien-chat)
22. [Bang Xep Hang](#22-bang-xep-hang)
23. [Doi Qua (Giftcode)](#23-doi-qua-giftcode)
24. [Quy Doi](#24-quy-doi)
25. [Ham Mo (Mining)](#25-ham-mo-mining)
26. [Tui Do (Inventory)](#26-tui-do-inventory)
27. [He Thong Tien Te](#27-he-thong-tien-te)
28. [GM Panel](#28-gm-panel)
29. [Cau Truc Du Lieu](#29-cau-truc-du-lieu)
30. [Cong Thuc Tinh Toan](#30-cong-thuc-tinh-toan)

---

## 1. Tai Khoan & Dang Nhap

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Dang ky | `index.php`, `reguser.php` | Dang ky tai khoan voi username/password |
| Dang nhap | `index.php` | Dang nhap, tao session token |
| Tao nhan vat | `cj.php` | Chon ten (Dao Hieu), gioi tinh (Nam/Nu), tien tri (Chien si/Thieu hiem/An si) |
| Chong Spam | `game.php` | delay toi thieu 1 giay giua cac request |
| Quan Ly Session | `game.php` | Token-based, luu trong bang `game1` |

### 3 Tien Tri Khoi Dau

| Tien Tri | Diem Manh |
|----------|-----------|
| Chien si | Can bang (ATK/DEF on dinh) |
| Thieu hiem | Nhanh nhe (CRIT/Nhanh cao) |
| An si | Phong thu (HP/DEF cao) |

---

## 2. Nhan Vat & Chi So

| Thuoc Tinh | Mo Ta |
|------------|-------|
| HP | Sinh luc |
| ATK | Cong kich |
| DEF | Phong thu |
| CRIT | Chi mang |
| Hut Mau | Phan tram hoi mau khi danh |
| Ngua Hanh | 5 nguyen to: Kim/Moc/Thuy/Hoa/Tho |
| Diem Thien Phu | Phan diem nang cap |
| Linh Can | Goc nguyen to an dinh |
| The Chat | Tinh chat than the |
| Huyet Mach | Mach mau (co bien hinh) |

- Tong so thuoc tinh: **90+**
- Xem thong tin nhan vat khac: `otherzhuangtai.php`

---

## 3. Cap Do & Dot Pha

### He Thong Canh Gioi

- Tong so canh gioi: **15+** (Nhap Mon -> Tien Canh...)
- Moi canh gioi: **10 giai doan**
- Tong so cap do toi da: **4300+** (87 canh gioi x 50 cap)

### Dot Pha Nho

- Tang cap trong canh gioi
- Chi phi: Linh Thach
- Ty le thanh cong: **~75%** (giam theo cap)

### Dot Pha Lon

- Moi **50 cap** la dot pha lon
- Hieu ung animation dac biet

### That Bai Dot Pha

| Loai That Bai | Hau Qua |
|---------------|---------|
| Giam cap | Mat 1 cap |
| Mat EXP 50% | Mat 50% EXP hien tai |
| Mat EXP 75% | Mat 75% EXP hien tai |
| Reset EXP | Reset EXP ve 0 |
| Set EXP | Reset EXP can de dot pha |

### Phuc Loi Dot Pha

| Su Kien | Xac Suat | Mo Ta |
|---------|----------|-------|
| Phap Ma | 1% | Thuoc tinh than ky |
| Seth Danh Chet | 1% | Bi set danh chet |

---

## 4. Ban Do & Di Chuyen

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Dieu huong 4 huong | `nowmid.php` | Bac/Tay/Dong/Nam voi link ban do |
| Ban do the gioi | `allmap.php` | Xem tat ca vung va dich chuyen |
| Ban do vung | `qydt.php` | Ban do chi tiet: quai, NPC, tuyen duong |
| Khu vuc PvP | `nowmid.php` | Cam danh dau PvP/An toan |
| Thong tin nguoi choi | `nowmid.php` | Hien thi ai dang o ban do |
| Trang thai thuong | `nowmid.php` | HP=0 bat buoc ve thanh pho, gap NPC tri lieu |
| Dich chuyen | `taskguide.php` | Dich chuyen toi NPC, chi phi: `cap*12 + 500` LT |
| Boss tu hoi sinh | `nowmid.php` | Boss tu hoi sinh sau thoi gian cau hinh |

### Cau Truc Ban Do

Moi ban do co:
- Ten
- Quai vat
- Boss
- NPC
- Co PvP
- Link 4 huong

---

## 5. He Thong Chien Dau

### 5a. PvE - Danh Quai

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Chien dau PvE | `pve.php` | HP bar, thuoc (3 o), cuon (3 o) |
| Thong tin quai | `ginfo.php` | Xem stats, drops (trang bi + vat pham + thuoc) |
| Tinh toan chien dau | `pve.php` | ATK vs DEF, crit, hut mau, nguyen to |
| Thu cung chien dau | `pve.php` | Pet danh cung nguoi choi, co HP rieng |
| Drop loot | `pve.php` | Trang bi, vat pham, thuoc, EXP |
| He thong Ngua Hanh | `class/player.php` | Kim/Moc/Thuy/Hoa/Tho voi multiplier loi/thiet |
| Chong gian lan | `pve.php` | Xac thuc vi tri ban do, khoa quai voi nguoi danh |

### 5b. Boss

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Boss don | `boss.php` | HP bar, 3 o thuoc, ky nang, tinh toan sat thuong |
| Thong tin boss | `bossinfo.php` | Xem stats boss truoc khi danh |
| Boss hoi sinh | `nowmid.php` | Hoi sinh sau thoi gian (theo ngay) |
| Phan thuong boss | `boss.php` | Trang bi, vat pham, Linh Thach, EXP |
| Boss Linh Can | `linhcan_boss.php` | Boss nguyen to toan server, chien dau theo nguyen to |

### 5c. PvP

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| PvP/PK | `pvp.php` | Choi nguoi voi gioi han khu vuc |
| Khu vuc PvP | `nowmid.php` | Ban do danh dau PvP vs an toan |
| Dau truong | `vandinhdai.php` | Xep hang ELO, 5 luot/ngay, giai dau |
| Phan thuong dau truong | `class/function/vandinhdai_reward.php` | Phan hang: Thien Ha Vo Song, De Nhat Vo Song... |

### 5d. Thap Yeu Thu

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Thach thuc thap | `toayeuthap.php` | **150 tang**, boss moi tang |
| Phan thuong | `toayeuthap.php` | EXP, Linh Thach, trang bi |
| Reset | `toayeuthap.php` | Reset hang tuan |

---

## 6. Trang Bi

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Hien thi | `game/trangbi.php` | **16 o trang bi** (tool1-tool16), chia 2 cột moi cột 8 o theo nhom |
| Mac do | `game/trangbi.php` | Lenh `setzbwz`, xac thuc loai o; co nut "Tu mac" cham diem cao nhat tung o |
| Thao do | `game/trangbi.php` | Lenh `xxzb` |
| Thong tin so huu | `game/zbinfo.php` | Popup chi tiet trang bi cua nguoi choi (co section Thuoc tinh Ngu Hanh) |
| Thong tin he thong | `game/zbinfo_sys.php` | Xem trang bi tu quai/cua hang |
| Nang cap | `game/trangbi.php` | He thong Cuong hoa; o item chi hien ten + "Cap X" + "+N" mau xanh la #4ade80 |
| Pham chat | `class/player.php` | He thong pham chat (rarity 1-10) voi ten va mau sac |
| **Ngu Hanh (da he)** | `class/function/nap_hanh.php` | **Rèn toi da 5 hanh len MOT mon**, khong de len nhau — xem 6a duoi |
| Cauchinh JSON | `class/config/zhuangbei_config.json` | 180 mon; `element: "none"` mac dinh (hanh do nguoi choi ren vao) |
| Anh trang bi | `class/function/zhuangbei.php` | `zhuangbei_image_path()`: truong anh trong config → `images/{zbid}.png` → icon o `images/slot/zb_slot_XX.png` |
| Cache anh | `class/function/zhuangbei.php` | `const ZB_IMG_VER` + `zhuangbei_img_ver()` noi `?v=` — **PHAI bump khi thay anh** |
| Tooltip | `class/function/itemtip.php` + `js/itemtip.js` | Cham/hover vao ANH item; so voi mon dang mac cung o; tag hien thi da hanh |

### 16 O Trang Bi (ten that — `zhuangbei_tool_names()`)

| tool | Ten o | tool | Ten o |
|------|-------|------|-------|
| 1 | Vu Khi | 9 | Linh Phu |
| 2 | Thien Thuan | 10 | Ngoc Nhan |
| 3 | Thien Quan | 11 | Co Ngoc |
| 4 | Giap Bao | 12 | Vai Khoi |
| 5 | Ho Thu | 13 | Linh Dai |
| 6 | Gioi Chi | 14 | Linh Boi |
| 7 | Yeu Dai | 15 | Toa Ky |
| 8 | **Phi Phong** | 16 | Bao Vat |

Nhom hien thi: **"Trang bi"** = 2, 3, 4, 5, 12, 8, 7, 13 — **"Vu khi – Phu – Phap bao"** = 1, 9, 16, 6, 10, 11, 14, 15 (`zhuangbei_equip_groups()`).

> **Lo o 16 "Bao Vat" (phat hien khi test 04/10/2026, CHUA SUA):** `class/config/zhuangbei_config.json` **khong co bat ky item nao `zbtool=16`** → o 16 khong the mac mon nao, du nut "Trang bi" van hien. Mot dong cu (zbid 55) nay map sang tool 11. Theo Plan Z (config la master, sua config phai duyet), can chu du an chot danh sach mon cho o 16.

> **Nghiep vu o 8 (Phi Phong):** chu du an chi ra "Phi Phong = ao choang mac ngoai, khong phai canh". Toan bo anh goc cua o 8 (zbid 16-20, 91-95 + icon o) la **doi canh** do game Trung ve san. **Ca 4 source game chu du an so huu (LKCT `E:/9kiem`, MuH5, TuTienPhucMa, NaTra) deu KHONG co icon `披风` roi** — dung tim nua, ve moi bang ImageGen nen magenta phang roi chroma-key.
>
> **Vong 1 (04/10):** 3 master tint theo mau `zbys` → **LOI**: 19==20 va 93==94==95 la file giong het nhau tung byte (vi 93/94/95 cung mau vang `#d4af37`), 10 mon chi co 6 hinh khac nhau. Anh cu: `bak/slot8/wing_original/` (canh) + `bak/slot8/v1_cloak/` (vong 1), script: `bak/slot8/make_cloak_icons.py`.
>
> **Vong 2 (05/10, CHU DU AN DUYET "moi mon mot dang, sang tang dan"):** chu du an gui 6 anh mau trong game goc (`vibe_images/phi_phong_ref/ref1..6.png`) → so 1:1 (`shot_cloak_v2_vs_ref.png`) roi ve **10 master rieng biet, BO TINH**, giu silhouette ao choang doc duoc o bac cao. Script `bak/slot8/make_cloak_icons_v2.py` co **chan md5 trung** (`TRUNG FILE`) va do do sang de bac cao sang hon bac thap. Ket qua: 10 icon 10 md5 khac nhau; `ZB_IMG_VER` = `2026100406`. Anh kiem route that: `shot_cloak_v2_trangbi.png`, `shot_cloak_v2_popup95.png`, `shot_cloak_v2_bag_mobile.png`. **CHUA** xem tren dien thoai that cua chu du an.

### 6a. Thuoc tinh Ngu Hanh (ren da hanh) — 04/10/2026

**Spec:** `docs/DESIGN_NGUHANH_KICHHOAT.md` · **Engine:** `class/function/nap_hanh.php` (namespace `player`) · **Route:** `cmd=nap_hanh` · **Schema:** `sql/nguhanh_kichhoat.sql`

| Khia canh | Chi tiet |
|-----------|----------|
| Luu tru | `playerzhuangbei.element` / `fangshi_zb.element` = chuoi `kim,moc,thuy,hoa,tho` (toi da 5), hoac `none` |
| Nguyen lieu | `(1+n)` Tinh Thach dung hanh + `500 × rarity × (1+n)` Linh Thach (n = so hanh da co) |
| Ty le | `90 − 10n + thuong linh can` (kim +15, hoa/all +10, tho +8, moc +5, thuy 0), san 40 tran 95 |
| That bai | **Chi mat nguyen lieu**, khong mat hanh da co |
| Noi tai hop linh can | 1/3/6/9/12 mon trung hanh voi linh can → +3/6/10/15/22% chi so: kim→Cong kich, moc→Phong thu, thuy→Hut mau, hoa→Bao kich, tho→Khi huyet |
| Chien dau | `zhuangbei_pick_element()` chon hanh khac tot nhat trong bo 16 mon dang mac; `game/pve.php` dung duoc voi QUAI HOANG. **BOSS CHUA KHAC CHE DUOC**: bang `boss` khong co cot `element` nen nhanh `game/boss.php:241-274` la code cho — can chu du an quyet |
| Treo ban | `fangshi_zb.element` mang theo hanh; mua lai va thu hoi deu giu nguyen chuoi hanh (da test) |
| Hien thi | Badge moi hanh o tui do / o dang mac / popup `cmd=chakanzb`; tooltip `js/itemtip.js` (`data-tip` JSON, hover may tinh + cham dien thoai), day du 7 chi so + dong "So voi mon dang mac" |
| Log | `player_chetao_log.log_type = 'nap_hanh'` |
| Cau hinh | `class/config/nguhanh_config.json` |
| Test | `tests/test_nguhanh.php` 82 assert (hook tat dinh `$GLOBALS['nguhanh_force_roll']`) · `tests/test_nguhanh_random.php` 23 assert + **2600 roll that** `mt_rand` · `tests/test_nguhanh_combat.php` 25 assert (ca danh qua HTTP) · `tests/test_nguhanh_market.php` 24 assert (treo ban → mua lai → thu hoi) · **ALL PASS**. Fixture: `tests/e2e_fixture.php` |
| Anh kiem | `shot_e2e_*.png` (route that, desktop + 390px), `shot_e2e_tip_many.png` (tooltip 5 he), `shot_e2e_tip_three.png` (3 he + dong so sanh). Dung cu: `tests/capture_tip_390.php` |
| Production | **CHUA CHAY 3 ALTER tren production** (da dien tap tren DB tam `game_sqltest`: 18 ALTER mot lan, 0 loi, preflight 0). Runbook + SQL gop (18 ALTER, 0 DELETE, kem no schema sid): `docs/DEPLOY_PRODUCTION_NGUHANH.md`, `sql/deploy_production_20261004.sql`, kiem truoc/sau bang `tests/deploy_preflight.php` (chi doc) |

**Ten hien thi trong game la "Thuoc tinh Ngu Hanh"** (khong phai "Nap Ngu Hanh"); nut tren popup ghi **`Kich hoat`**. Ten ham/route/spec van giu `nap_hanh`.

---

## 7. Vo Cong

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| He thong Vo Cong | `wugong.php` | Hoc, trang bi, thang cap vo cong |
| Thu vien | `xxwg.php` | Duyet tat ca vo cong voi chi tiet |
| Trang bi/Quen | `xxwg.php` | Hoc (xuexi), Quen (biguan), Xoa (fangsheng) |
| Luyen tap | `wugong.php` | Luyen tap voi EXP, can bi tich (bi tat) |
| Mua bi tich | `wugong.php` | Mua bi tich thieu 400 Ma Thach moi cuon |
| Pham chat | `wugong_utils.php` | **6 pham chat**: Pham/Lam/Luc/Linh/Hoang Kim/Do |
| Cauchinh | `wugong_config.json` | Du lieu tinh trong JSON |
| Thuoc tinh | `class/player.php` | Moi cap tang ATK/DEF/HP/CRIT/Hut Mau |

### 6 Pham Chat Vo Cong

| Pham Chat | Mau Sac |
|-----------|---------|
| Pham | Xam |
| Lam | Xanh Duong |
| Luc | Xanh La |
| Linh | Tim |
| Hoang Kim | Vang |
| Do | Do |

---

## 8. Cong Phap

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| He thong | `congphap.php` | Trang bi/Thao/Xoa cong phap |
| Buff thu dong | `class/player.php` | Tinh `absorb_speed`, phat huy sat thuong |
| So o toi da | `class/player.php` | Tang theo cap do |
| Cauchinh | `class/function/congphap.php` | Logic tinh buff thu dong |

---

## 9. Thien Phu

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Diem Thien Phu | `tianfu.php` | Phan diem vao 7 chi so |
| Mo khoa | `tianfu.php` | Mo khoa o cap **30** |
| Nang cap nhanh | `tianfu.php` | +1/+10/+50/+100/Toi da |
| Hieu qua | `class/player.php` | Moi diem: ATK+5, CRIT+1.5, Hut Mau+2, HP+10, DEF+5 |
| Diem nhan | `tupo.php` | **+5 diem** moi lan dot pha |

### 7 Chi So Thien Phu

| Chi So | Hieu Ung/1 Diem |
|--------|-----------------|
| HP | +10 |
| ATK | +5 |
| DEF | +5 |
| CRIT | +1.5 |
| Hut Mau | +2 |
| May Man | +1 |
| Ne Tranh | +1 |

---

## 10. Linh Can (Nguyen To)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tong quan | `linhcan.php` | Xem linh can voi bonus nguyen to |
| 5 nguyen to | `linhcan.php` | Kim/Moc/Thuy/Hoa/Tho, 5 tang (Don He -> Ngu Hanh) |
| 5 pham chat | `linhcan.php` | Tap Pham -> Tien Thien voi bonus stat |
| Nang cap | `linhcan_upgrade.php` | Tang tang voi ty le thanh cong, chi phi LT + Da Ngu Hanh |
| Nhiem vu tuan | `linhcan_quest.php` | He thong nhiem vu tuan de nang cap |
| Boss Linh Can | `linhcan_boss.php` | Boss toan server, chien dau theo nguyen to |
| Passives | `class/player.php` | Toc do hap thu, bonus EXP, stat, pet bonus |
| **Hop linh can (Ngu Hanh)** | `class/function/nap_hanh.php` | So mon dang mac trung hanh voi linh can → +3/6/10/15/22% chi so theo nac 1/3/6/9/12; ket qua nam trong `$player->nguhanh_info`, xem muc 6a |
| Vat pham tay | `use_dj.php` | Tay Linh Dan de randomize |
| Cauchinh | `linhcan_config.json` | Cau hinh loai/tang/nguyen to trong JSON |

### 5 Tang Linh Can

| Tang | Mo Ta |
|------|-------|
| Don He | 1 nguyen to |
| Song He | 2 nguyen to |
| Tam He | 3 nguyen to |
| Tu He | 4 nguyen to |
| Ngu Hanh | 5 nguyen to |

### 5 Pham Chat

| Pham Chat | Mo Ta |
|-----------|-------|
| Tap Pham | Co ban |
| Linh Pham | Trung binh |
| Quang Pham | Kha |
| Thanh Pham | Tot |
| Tien Thien | Xuat sac |

---

## 11. The Chat

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tong quan | `thechat.php` | Xem the chat voi passives |
| Nang cap pham chat | `thechat_upgrade.php` | Tang pham chat voi ty le thanh cong |
| He thong Khai Mach | `thechat_awakening.php` | Khai mach da cap voi yeu cau canh gioi |
| Passives | `class/player.php` | HP, ATK, DEF, CRIT + EXP boost, hoi mau, chong don, giam sat thuong |
| Vat pham tay | `use_dj.php` | Tay Tuy de randomize (pham chat >= hien tai) |
| Cauchinh | `thechat_config.json` | Cau hinh tat ca loai the chat trong JSON |

### Passives The Chat

| Passive | Hieu Qua |
|---------|----------|
| HP Bonus | Tang sinh luc |
| ATK Bonus | Tang cong kich |
| DEF Bonus | Tang phong thu |
| CRIT Bonus | Tang chi mang |
| EXP Boost | Tang toc EXP |
| HP Regen | Hoi mau |
| Resist | Chong don |
| Damage Reduction | Giam sat thuong |

---

## 12. Huyet Mach

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tong quan | `huyetmach.php` | Xem huyet mach voi passives nguyen to |
| Khai Mach | `huyetmach_awakening.php` | Khai mach da cap voi yeu cau canh gioi + boss + nhiem vu |
| He thong Bien Hinh | `huyetmach_transform.php` | Bien hinh voi nhan ATK/DEF/HP, sat thuong nguyen to, hoi mau, giam sat vat ly |
| Thoi gian hoi | `huyetmach_transform.php` | Cooldown giua cac lan bien hinh |
| Passives | `class/player.php` | ATK, HP, CRIT, Hut Mau + sat thuong nguyen to, EXP boost, ty le drop |
| Vat pham tay | `use_dj.php` | To The Dan de randomize (pham chat >= hien tai) |
| Cauchinh | `huyetmach_config.json` | Cau hinh loai/bien hinh trong JSON |

### Passives Huyet Mach

| Passive | Hieu Qua |
|---------|----------|
| ATK | Tang cong kich |
| HP | Tang sinh luc |
| CRIT | Tang chi mang |
| Hut Mau | Hoi mau khi danh |
| Element Damage | Sat thuong nguyen to |
| EXP Boost | Tang toc EXP |
| Drop Boost | Tang ty le rung |

---

## 13. Tu Luyen (AFK)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tu luyen | `xiulian.php` | 3 tang: Free (x1, 30p), Linh Thach (x2, 3h), Ma Thach (x3, 6h) |
| VIP Bonus | `xiulian.php` | **+10% toc do** cho VIP |
| Toc do | `xiulian.php` | Cong thuc: `cap * VIP * multiplier * min(1.3, 1 + bonus)` |
| Bonus trang bi | `xiulian.php` | Trang bi co tupoexp tang % bonus |
| Hap thu | `xiulian.php` | Cong Phap + Linh Can tang toc do hap thu |
| Dung tu luyen | `xiulian.php` | Ket thuc va nhan EXP tich luy |

### 3 Tang Tu Luyen

| Tang | Ti Le | Thoi Luong Toi Da | Chi Phi |
|------|-------|-------------------|---------|
| Free | x1 | 30 phut | Mien phi |
| Linh Thach | x2 | 3 gio | LT |
| Ma Thach | x3 | 6 gio | MT |

---

## 14. Cua Hang

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Cua hang da tab | `shangdian.php` | Tabs: Linh Thach, Ma Thach, Dao Cu, Trang Bi |
| Loai tien | `shangdian.php` | Linh Thach (uyxb) va Ma Thach (uczb) |
| Tim kiem/Loc | `shangdian.php` | Tim kiem tu khoa voi phan trang |
| Cua hang trang bi | `shangdian.php` | Mua trang bi voi yeu cau cap do |
| Cua hang vat pham | `shangdian.php` | Mua vat pham tieu hao |
| Xac nhan mua | `shangdian.php` | Modal xac nhan truoc khi mua |

---

## 15. Phuong Thi (Market)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Thi truong | `fangshi.php` | Mua/ban trang bi + vat pham tu nguoi choi |
| Dau gia vat pham | `djinfo.php` | Dau gia vat pham tieu hao voi so luong + gia |
| Dau gia trang bi | `zbinfo.php` | Dau gia trang bi voi gia |
| Mua so luong | `fangshi.php` | Mua 1/5/10 cung luc |

---

## 16. Che Tao & Luyen Dan

### Che Tao

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Che tao | `chetao.php` | Che tao vat pham tu cong thuc + nguyen lieu |
| Log che tao | `player_chetao_log` | Theo doi lich su che tao |
| Bonus nguyen to | `chetao.php` | Bonus theo nguyen to |
| Cauchinh | `chetao_config.json` | Du lieu cong thuc trong JSON |

### Luyen Dan

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Luyen dan | `luonden.php` | Nau thuoc tu cong thuc + nguyen lieu |
| Ty le thanh cong | `luonden.php` | Ty le bien doi theo cong thuc |
| Log luyen dan | `player_luonden_log` | Theo doi lich su |
| Cauchinh | `luonden_config.json` | Du lieu cong thuc trong JSON |

---

## 17. Nhiem Vu

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tong quan | `nhiemvu.php` | Tabs: Ngay/Tuan/Thang/Thanh Tuu/An/Giai Chinh |
| Theo doi | `playertask.php` | Nhiem vu dang hoat dong voi tien do |
| Huong dan | `taskguide.php` | Huong dan tien tri voi dich chuyen NPC |
| Nhan/Nop | `task.php` | Nhan nhiem vu, nop khi dieu kien dat |
| Loai | `task.php` | Danh quai, thu thap vat pham, dat cap do |
| Phan thuong | `task.php` | EXP, vat pham, ky nang, trang bi |
| Tien do | `class/player.php` | Theo doi tu dong tien do kill/item/cap |
| Thuong ngay | `nhiemvu.php` | Nhan thuong hoan thanh ngay |
| Thanh tuu | `nhiemvu.php` | Theo doi thanh tuu dai han |
| Chinh tuyen | `nhiemvu.php` | Tuyen co "Chinh Tuyen" tien tri cau chuyen |

### 5 Loai Nhiem Vu

| Loai | Mo Ta |
|------|-------|
| Nhiem Vu Ngay | Lam moi moi ngay |
| Nhiem Vu Tuan | Lam moi moi tuan |
| Nhiem Vu Thang | Lam moi moi thang |
| Thanh Tuu | Dai han, hoan thanh 1 lan |
| Chinh Tuyen | Tuyen co chinh, tien tri cau chuyen |

---

## 18. Sunh Vat (Pet)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tong quan | `chongwu.php` | Xem pet dang trang bi voi stats |
| Gacha | `chongwu.php` | Rut pet ngau nhien |
| Pham chat | `chongwu.php` | **6 pham chat**: Binh thuong -> Nghich thien |
| EXP | `chongwu.php` | Pet tang cap voi EXP bar |
| Chien dau | `pve.php` | Pet danh cung nguoi choi trong PvE |
| Loai pet | `game.sql` | Chay tai, Luu lu ma, Quai quau, Suai suai hau... |
| Linh Can Pet | `class/player.php` | Linh Can tang ATK/HP/EXP/CRIT cho pet |

### 6 Pham Chat Pet

| Pham Chat | Mo Ta |
|-----------|-------|
| Binh thuong | Co ban |
| Kha | Trung binh |
| Tot | Kha |
| Xuat sac | Tot |
| Hai Pham | Rat tot |
| Nghich thien | Xuat sac nhat |

---

## 19. Mon Phai (Clan)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| He thong | `club.php` | Mon phai voi **11 cap bac**: Chuong Mon -> De Tu Tap Dich |
| Danh sach | `clublist.php` | Xem tat ca mon phai |
| Boss Tông Môn | `club_boss.php` | Raid boss theo khung gio, toan bo thanh vien cung tan cong |
| Boss JS | `js/club_boss.js` | AJAX attack, auto-refresh HP/ranking, damage float |
| Nhiem vu | `class/function/club_quest.php` | Nhiem vu hang ngay + quest hook (boss_damage, boss_kill, donate_lt, v.v.) |
| Bao kho | `class/function/club_treasury.php` | Kho bau tong mon, phan phat vat pham |
| Config NV | `class/config/club_quest_config.json` | 18 loai quest voi 3 tang phan thuong |

### 11 Cap Bac Mon Phai

| Cap | Vi Tri | Mo Ta |
|-----|--------|-------|
| 1 | Chuong Mon | Chu tich uy, quyen upgrade |
| 2 | Pho Chuong Mon | Pho chu, quyen ren chuc |
| 3 | Truong Lao | Cao |
| 4 | Chap Su | Trung cao |
| 5 | Thanh Tu | Trung |
| 6 | Chan Truyen | Trung thap |
| 7 | Thien Kieu | Thap |
| 8 | Tinh Anh | Thap |
| 9 | Noi Mon De Tu | Co ban |
| 10 | Ngoai Mon De Tu | Co ban |
| 11 | De Tu Tap Dich | Co ban |

### Boss Tông Môn

| Tinh Nang | Mo Ta |
|-----------|-------|
| Khung gio | Mo cua 24h/ngay (config open_hour/duration_min) |
| Stats | HP, ATK, DEF, Crit theo cap tong mon |
| Tan cong | Player + Pet gop damage, atomic UPDATE boss_hp |
| Phan don | Boss danh lai player, tinh theo ATK/DEF |
| Hut mau | Lifesteal ty le voi x x |
| Bao kich | Player crit 1.65x, Boss crit 1.5x |
| Ne tranh | 15% ca hai ben |
| Log | INSERT club_boss_log moi turn, hien thi trong tab Lich Su |
| Ranking | club_boss_damage, top 10 theo total_damage |
| Thuong | EXP + LT + MT cho tat ca, bonus top 1-3, bonus ket lieu |
| EXP tong mon | 500 + 50 × so nguoi tham gia |

### De Nang Cap Tông Môn

| Yeu Cau | Mo Ta |
|---------|-------|
| Chi Chuong Mon | uclv == 1 |
| Linh thach | Theo config club_upgrade_config |
| Ma thach | Theo config |
| EXP tong mon | Theo config |
| Loi ich | HP%, ATK%, DEF%, EXP%, Max member tang theo cap |

---

## 20. Hao Huu (Ban Be)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Danh sach | `im.php` | Xem ban be voi trang thai online |
| Them/Xoa | `im.php` | Them khi xem profile, xoa voi xac nhan |
| Thong tin | `im.php` | Xem cap, ATK, DEF, HP cua tung ban |

---

## 21. Lieu Thien (Chat)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Chat | `liaotian.php` | Chat toan server voi lich su tin nhan |
| Tin nhan he thong | `liaotian.php` | Tin nhan he thong voi HTML an toan (drop, link) |
| Chong XSS | `liaotian.php` | Whitelist: `<a>`, `<font>`, `<img>` |

---

## 22. Bang Xep Hang

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| 5 loai | `paihang.php` | Cap, Chien Luc, ATK, DEF, EXP |
| Cong thuc Chien Luc | `paihang.php` | `ATK*10 + DEF*8 + HP + CRIT*30 + HutMau*25 + ThienPhu*50 + MayMan*10 + NeTranh*10 + ThanTrang*Power` |
| Hien thi | `paihang.php` | Top nguoi choi voi stats |

### 5 Loai Xep Hang

| Loai | Chi So |
|------|--------|
| Cap Do | Cap do cao nhat |
| Chien Luc | Tong luc chien dau |
| ATK | Cong kich cao nhat |
| DEF | Phong thu cao nhat |
| EXP | Tong EXP |

---

## 23. Doi Qua (Giftcode)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Doi qua | `duihuan.php` | Nhap code de nhan thuong |
| Chong nhan trung | `duihuan.php` | UNIQUE KEY + INSERT IGNORE |
| Loai phan thuong | `duihuan.php` | Trang bi, vat pham, Linh Thach, Ma Thach, EXP |
| Log | `duihuan_log` | Theo doi tat ca code da nhan |

---

## 24. Quy Doi

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| MT -> LT | `quydoi.php` | **1 Ma Thach = 1,000 Linh Thach** |
| Gioi han | `quydoi.php` | Toi da 100,000 MT/lan, 5 lan/ngay |
| Log | `exchange_log` | Lich su quy doi day du |

---

## 25. Ham Mo (Mining)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Dao mo | `hammo.php` | Dao hang ngay nhan nguyen lieu + tien |
| Phan thuong | `hammo.php` | Ngau nhien: Mach ma thach, Linh thach tinh, menh ghep trang bi |
| Gioi han ngay | `hammo.php` | Theo doi so luot dao |
| Thong ke | `mine_player` | Tong luot dao, tong MT, tong CZB |

---

## 26. Tui Do (Inventory)

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Tui trang bi | `bagzb.php` | Xem tat ca trang bi so huu voi mac/thao |
| Tui vat pham | `bagdj.php` | Xem vat pham tieu hao, ban |
| Tui thuoc | `bagyp.php` | Xem thuoc hoi mau |
| Tui cuon | `bagjn.php` | Xem cuon ky nang (phu luc) |
| Tui nguyen lieu | `bagyd.php` | Xem nguyen lieu che tao |
| Thong tin vat pham | `djinfo.php` | Xem chi tiet voi tuy chon dau gia |
| Thong tin cuon | `jninfo.php` | Xem chi tiet cuon ky nang |
| Dung vat pham | `use_dj.php` | Dung voi `use_effect` (vat pham tay gioi han 2 lan) |

### 5 Loai Tui

| Tui | Noi Dung |
|-----|----------|
| Tui Trang Bi | Trang bi da so huu |
| Tui Vat Pham | Vat pham tieu hao |
| Tui Thuoc | Thuoc hoi mau |
| Tui Cuon | Cuon ky nang (phu luc) |
| Tui Nguyen Lieu | Nguyen lieu che tao |

---

## 27. He Thong Tien Te

| Tien | Bien | Nguon |
|------|------|-------|
| Linh Thach | uyxb | Farm quai, tu luyen, nhiem vu |
| Ma Thach | uczb | Nap tien (cash shop) |
| Diem Thien Phu | tf | Dot pha |

### Ty Gia Quy Doi

| Quy Doi | Ty Le |
|---------|-------|
| Ma Thach -> Linh Thach | 1 MT = 1,000 LT |

---

## 28. GM Panel

| Tinh Nang | File | Mo Ta |
|-----------|------|-------|
| Dashboard | `gm/gm.php` | Bang dieu khien voi **15+ thao tac** |
| Charge Linh Thach | `gm/gm.php` | Them LT cho nhan vat |
| Charge Ma Thach | `gm/gm.php` | Them MT cho nhan vat |
| Set Cap | `gm/gm.php` | Dat cap do nhan vat |
| Set EXP | `gm/gm.php` | Dat EXP nhan vat |
| Set HP | `gm/gm.php` | Dat HP hien tai va toi da |
| Set ATK | `gm/gm.php` | Dat cong kich |
| Set DEF | `gm/gm.php` | Dat phong thu |
| Bao mat | `gm/gm.php` | Bao mat bang mat khau (code: `loulxgame`) |
| GM Query | `gm/gmquery.php` | Backend AJAX xu ly thao tac GM |

---

## 29. Cau Truc Du Lieu

### Cau Hinh DB

| File | Mo Ta |
|------|-------|
| `pod.php` | Ket noi MySQL: root / www.aae.ink / game |
| `gm/config.php` | Cau hinh cho GM Panel |

### Bang Chinh

| Bang | Mo Ta |
|------|-------|
| `game1` | Du lieu nguoi choi chinh |
| `mid` | Du lieu ban do |
| `boss` | Du lieu boss |
| `playerzhuangbei` | Trang bi nguoi choi |
| `playerdaoju` | Vat pham nguoi choi |
| `player_chetao_log` | Log che tao |
| `player_luonden_log` | Log luyen dan |
| `exchange_log` | Log quy doi |
| `duihuan_log` | Log giftcode |
| `mine_player` | Thong ke dao mo |

### JSON Config Files

| File | Noi Dung |
|------|----------|
| `wugong_config.json` | Vo cong |
| `zhuangbei_config.json` | Trang bi |
| `guaiwu_config.json` | Quai vat |
| `linhcan_config.json` | Linh can |
| `thechat_config.json` | The chat |
| `huyetmach_config.json` | Huyet mach |
| `congphap_config.json` | Cong phap |
| `taozhuang_config.json` | Than trang (set) |
| `luonden_config.json` | Luyen dan |
| `chetao_config.json` | Che tao |

### SQL Files

| File | Mo Ta |
|------|-------|
| `database_json_cleanup.sql` | Chuyen du lieu tu DB sang JSON |
| `sql_thechat_huyetmach.sql` | Them cot the chat/huyet mach |
| `fix_quest.sql` | Bang nhiem vu tuan linh can |
| `luonden_chetao_db.sql` | Bang log luyen dan va che tao |
| `create_exchange_log.sql` | Bang log quy doi |

---

## 30. Cong Thuc Tinh Toan

### 30.1 EXP Rate

```
EXP = cap * VIP * multiplier * min(1.3, 1 + cp_absorb/100) * min(1.3, 1 + lc_absorb/100)
```

### 30.2 Ty Le Dot Pha

```
ty_le = max(5, 75 - cap*0.6 - canh*3 + giai_doan_bonus + trang_bi_bonus)
```

### 30.3 Cong Thuc Chien Luc

```
Chien Luc = ATK*10 + DEF*8 + HP + CRIT*30 + HutMau*25 + ThienPhu*50 + MayMan*10 + NeTranh*10 + ThanTrang*Power
```

### 30.4 Toc Do Tu Luyen

```
toc_do = cap * VIP * multiplier * min(1.3, 1 + cong_phap_absorb/100) * min(1.3, 1 + linh_can_absorb/100)
```

### 30.5 Chi Phi Dich Chuyen

```
chi_phi = cap * 12 + 500 (Linh Thach)
```

---

## Tong Ket

| Chi So | Gia Tri |
|--------|---------|
| Tong he thong lon | ~35 |
| Tong file PHP game | 64 |
| Tong file class | 4 |
| Tong file function | ~15 |
| Tong file JSON config | 10 |
| Tong file SQL | 7 |
| Dong code player.php | 2,800+ |
| So o trang bi | 16 |
| So cap do toi da | 4,300+ |
| So canh gioi | 87 |
| Tang thap | 150 |
| Pham chat vo cong | 6 |
| Pham chat pet | 6 |
| Cap bac mon phai | 7 |
| Loai tien te | 3 |
| Loai nhiem vu | 5 |
| Loai bang xep hang | 5 |

---

> **Ghi chu**: Game duoc trien khai tren nen web PHP, su dung giao dien text-based voi CSS stylize va JavaScript animation. Khong su dung frontend framework. Tat ca cau hinh tinh da duoc chuyen tu MySQL sang JSON de hieu nang.
