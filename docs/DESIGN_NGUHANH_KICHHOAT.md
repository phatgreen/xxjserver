# DESIGN: NAP NGU HANH KICH HOAT TRANG BI (Da He)

- Ngay: 2026-10-04
- Trang thai: DA DUYET thiet ke voi chu server (Vu Tan Phat) — ghi spec theo phien ban duyet nay
- Pham vi: chi la phan B (nạp ngu hanh). Phan A (gian bac trang bi phu 86 canh gioi) tach spec khac, lam sau.

## 1. Van de va muc tieu

Hien trang: toan bo 180 mon trong `zhuangbei_config.json` co `element: "none"` (quyet dinh Plan Z 2026-10-04: ngu hanh khac che quy ve Linh Can). Pipeline "trang bi co he → khac che voi quai" da duoc noi day du o `class/function/zhuangbei.php` (element_map, element_khac, get_element_multiplier), `class/player.php:559-592` (chon `zb_element`), `game/pve.php:243-262`, `game/boss.php:245-258` — nhung khong bao gio chay vi du lieu deu la none. Tooltip `itemtip.php`/`itemtip.js` cung tuong hien tag he khi mon co he that.

Muc tieu:

1. Cho phep nguoi choi **nap (ket tinh) thuoc tinh ngu hanh vao tung mon vat ly** qua luyen ren, tich duoc NHIEU he cung luc (toi da 5), khong de len nhau.
2. Khi he cua trang bi **trung voi linh can nguoi mac**, mo **noi tai theo nấc** cong chi so (Kim cong, Moc phong, Thuy hut mau, Hoa bao kich, Tho huyet).
3. He tren trang bi van tham gia **khac che voi quai/boss** nhu pipeline san co.
4. Config JSON khong doi (giu Plan Z) — he la cua mon vat ly trong DB, khong phai cua loai trang bi.

## 2. Du lieu (schema)

- `ALTER TABLE playerzhuangbei ADD COLUMN element VARCHAR(24) NOT NULL DEFAULT 'none'`
  - Gia tri: `none` hoac danh sach phan tach dau phay theo thu tu chuan `kim,moc,thuy,hoa,tho` (vi du `thuy`, `kim,thuy`, toi da 5 he = 19 ky tu).
  - Luu thu tu chuan de so sanh/tim kiem nhat quan.
- `ALTER TABLE fangshi_zb ADD COLUMN element VARCHAR(24) NOT NULL DEFAULT 'none'` — phong cho mang theo he khi gui ban; mua ve ghi nguyen vao `playerzhuangbei.element`.
- `class/player.php`:
  - `zhuangbei_columns()` (~404-408): them cot `element`.
  - `merge_player_zhuangbei_with_config()` (~415-481): `element` cua dong DB **de len** gia tri config (`none`), khong phai nguoc lai; ep ve `none` neu rong.
  - `addzb()` (~1203-1231): INSERT khong can doi (mac dinh `none` tu DDL).
  - KHONG động vao whitelist so cua `changezbsx()` (1365) — element khong phai chi so cong dan.
- File `sql/`: them `sql/nguhanh_kichhoat.sql` chua 2 lenh ALTER tren de ap cho production (local ap truc tiep, co dump backup truoc nhu debt sid-schema).

## 3. Luong ren "Nap Hanh"

- Entry: nut **Nap Hanh** trong popup chi tiet trang bi `game/zbinfo.php` (canh nut Cuong hoa), route `cmd=nap_hanh` trong `game.php` (theo pattern `do_craft_chetao` 412-413).
- Engine moi: `class/function/nap_hanh.php`:
  - `nap_hanh_cost($zb, $element)`: so tinh thach = `1 + n_he_da_co` (tinh thach he tuong ung: daoju 32 Hoa, 33 Thuy, 34 Moc, 35 Kim, 36 Tho); phi linh thach `uyxb` = `500 * rarity * (1 + n_he_da_co)`.
  - `nap_hanh_rate($zb, $player)`: `90 - 10 * n_he_da_co + bonus_linhcan` (bonus map tai dung cong thuc `chetao_calc_success_rate` 57-70: hoa+10, kim+15, moc+5, tho+8, all+10), san 40%, tran 95%.
  - `nap_hanh_do($sid, $zbnowid, $element, $dblj)`:
    1. Kiem tra mon thuoc ve nguoi choi, chua co he do (neu co → bao "Mon da mang he X", khong tieu hao).
    2. Kiem tra du tinh thach (`playerdaoju`) + `uyxb`; tieu hao bang `deledjsum` + UPDATE tien nhu cuong hoa.
    3. Roll RAT; **that bai chi mat nguyen lieu**, trang bi y nguyen.
    4. Thanh cong: `UPDATE playerzhuangbei SET element = <danh_sach_moi_theo_thu_tu_chuan> WHERE zbnowid = ?` → `player_cache_clear($sid)`.
    5. Ghi log `player_chetao_log` (tam dung, them kieu `nap_hanh`) de chu server tra loi khach.
- Gioi han: 1 mon toi da 5 he; mon o trang thai dang mac hay trong tui deu nap duoc.

## 4. Noi tai hop linh can (getplayer)

- Config moi `class/config/nguhanh_config.json`:
  - `tiers`: [1, 3, 6, 9, 12] (so mon hop can dang mac)
  - `bonus_pct`: [3, 6, 10, 15, 22] (%) ap len chi so tuong ung cua he
  - `elements`: kim → `ugj` (cong), moc → `ufy` (phong), thuy → `uxx` (hut mau, % diem cong them), hoa → `ubj` (bao kich), tho → `umaxhp` (khi huyet).
- Ham moi `nguhanh_match_count($player, $zbs)`: dem so mon dang mac co `element` **chứa** he cua linh can. Linh can `all` hop moi he; linh can `none` khong hop he nao.
- Moc vao `getplayer()` ngay sau vong tong hop trang bi (class/player.php ~570-592): lay n = match_count, tim bac tiers cao nhat ≤ n, ap `bonus_pct` len chi so tuong ung cua TUNG he co trong n mon hop (ty le % tren tong chi so trang bi mon do dong gop, khong tren chi so goc cua nhan vat — de don gian va de can bang).
- Ket qua luu vao `$player->nguhanh_info` (so mon hop, bac, danh sach noi tai dang chay) de trang Trang thai hien.

## 5. Khac che voi quai/boss (da he)

- `class/player.php:559-592` hien chon 1 he tu trang bi (rarity cao nhat thang). Nay doi thanh: thu thap **tap hop toan bo he** cua 16 mon → `$player->zb_element_set` (mang). Giu `$player->zb_element` nhu cu (he "dai dien" rarity cao nhat) de tuong thich choi.
- Ham moi `zhuangbei_pick_element($set, $monsterElement)` trong `class/function/zhuangbei.php`: tra ve he trong set khac duoc monsterElement (uu tien theo thu tu chuan), neu khong co tra ve `zb_element` cu.
- `game/pve.php:243-262` va `game/boss.php:245-258`: goi pick_element truoc khi tinh `$elemMult`; moi khac van dung `zhuangbei_get_element_multiplier` (1.2/0.85) va fallback linh can nhu hien tai. Set rong (chua ai nap he) → hanh vi nguyen ban 1.0, khong doi can bang.

## 6. Hien thi

- `class/function/itemtip.php:116-119, 152-156`: `elem` thanh mang cac tag `[icon, name, color]` theo so he cua mon (doc tu `element` cua dong DB, khong doc config).
- `js/itemtip.js:69-82`: vong lap render nhieu tag he, moi tag dung mau he; `Vô Hành` thi an nhu cu.
- `game/zhuangtai.php`: khu vuc linh can (~433-435) them dong "Noi tai ngu hanh: Hop can x N mon — <ten noi tai bac hien tai>".
- `game/zbinfo.php` popup: hien danh sach he hien co cua mon + chi phi/ty le lan nap tiep (tinh bang text thuan viet, co the tap duoc theo thuan quen mobile).
- `tongquan.php`: them 1 muc nho trong nhom "nuoi-duong" ghi ro bang thong ke so mon da nap he trong DB (demo tinh nang, cap nhat theo memory ban do game).

## 7. Bien va xu ly loi

- Mon bi xoa/da ban khi dang mo popup nap → kiem tra quyen so la lan cuoi truoc khi tieu hao.
- `element` trong DB co gia tri la (tay xoa tay) → merge ep ve `none`, log canh bao.
- Mua phong cho: `fangshi_zb.element` copy nguyen ven; ban mon da nap he thi tien nap khong hoan lai (ghi chu UI).
- Cache: moi lan nap xong bat buoc `player_cache_clear($sid)`; pve/boss doc player sau khi clear nen khong doc phai set cu.

## 8. Kiem thu

- `php -l` (5.6.9 NTS) cho moi file sua.
- Test CLI tam thoi (files label `test_`, xoa sau): tao 2 ban choi gia dung, nap he qua `nap_hanh_do` voi RAT ep xung (rand fixed), doi loi: `playerzhuangbei.element`, `nguhanh_match_count`, `getplayer` bonus %, `zhuangbei_pick_element` khac/dung/khong co set.
- Test trong game that (nguoi dung thao tac): nap 1 he → tooltip hien tag; nap he trung → tu choi khong tieu hao; that bai → mat nguyen lieu giu nguyen mon; mac du bo 12 mon hop can → chi so Trang thai tang dung bac 22%; danh quai nguoi khac he → elemMult 1.2 hien dong chu nhu cu.
- **Chua test**: toan bo (feature moi). Sau khi code xong se danh dau muc nao da test CLI, muc nao can ban tay trong game.

## 9. Trien khai

1. Dump backup DB `game` (mysqldump) truoc khi ALTER.
2. Ap `sql/nguhanh_kichhoat.sql` len local; production cho den luot deploy (ghi chu giong debt sid-schema).
3. Deploy code PHP (khong can restart nginx/php-cgi, only opcache if enabled).
4. Cap nhat `docs/CHANGELOG.md` + `tongquan.php`.

## 10. File danh sach can sua

| File | Viec |
|---|---|
| sql/nguhanh_kichhoat.sql | MOI — 2 ALTER |
| class/config/nguhanh_config.json | MOI — tiers/bonus/elements |
| class/function/nap_hanh.php | MOI — engine ren |
| game.php | route cmd=nap_hanh |
| game/zbinfo.php | nut + form Nap Hanh |
| class/player.php | columns, merge, zb_element_set, getplayer noi tai |
| class/function/zhuangbei.php | pick_element, doc element da he |
| game/pve.php, game/boss.php | dung pick_element |
| class/function/itemtip.php, js/itemtip.js | tag he mang |
| game/zhuangtai.php | hien noi tai |
| game/fangshi*.php (ban/mua) | mang element qua fangshi_zb |
| tongquan.php | muc thong ke + hien cot he trong bang trang bi |
| docs/CHANGELOG.md | ghi chu |

