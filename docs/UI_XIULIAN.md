# Tài liệu: Giao diện Tu Luyện (game/xiulian.php)

**Ngày:** 09/10/2026 · **Trạng thái:** đã nghiệm thu thị giác trên desktop + mobile, CHƯA test cảm ứng thật
**Phạm vi:** chỉ giao diện + một nhánh chọn link của orb. **Không đổi** logic tu luyện, công thức tu vi, chi phí, thời lượng.

---

## 1. Bối cảnh

Chủ dự án yêu cầu dựng lại màn Tu Luyện theo ảnh tham khảo của game Linh Kiếm Cửu Thiên, dùng tài nguyên chuyển sinh đã có, và siết lại thành **một màn hình hub** chạm được vào mọi hệ thống tu luyện thay vì phải bấm qua nhiều menu chữ.

Chỉ đạo xuyên suốt (đã chốt):

> "Chỉ gắn hệ thống THẬT đang có."

Toàn bộ link trong trang trỏ tới cmd có thật, đã kiểm chứng bằng `--dump-dom` là trả về nội dung. Không có nút "sắp mở".

Ràng buộc cứng: **PHP 5.6** (không `??`, không arrow fn, không type hint, `array()`/`isset()`/`empty()`), **không hạ cỡ chữ** để tiết kiệm chỗ (chủ dự án >50 tuổi), mọi hover/tooltip phải mở được bằng chạm.

---

## 2. File đã sửa

| File | Dòng | Nội dung |
|---|---|---|
| `game/xiulian.php` | cả file (523 dòng) | markup hub mới + nhánh `$orb_href` / `$orb_break`; `xl_realm_label()` (dòng 32, gọi ở 293) tách nhãn cảnh giới; `fitRealm()` (dòng 455) giữ nhãn 1 dòng bằng cách đo `Range` |
| `css/mystyle.css` | 748–878 | khối `.xl-*` (nền tảng + `@media(min-width:761px)` + `@media(max-width:760px)` + `@media(max-width:430px)`) |
| `class/player.php` | 405 | tách `combat_power($player)` dùng chung (trang khác gọi được) |
| `game.php` | 592 | chặn not `.tcg-tip .btn-break` khi `$ym == 'game/xiulian.php'` để không nhân đôi nút Đột Phá toàn trang; `$tpts` vẫn được gán (dòng 435) nên logic cũ không đổi |
| `game.php` 502, `gm/gm.php` 9, `gm/player.php` 9 | — | `css/mystyle.css?v=2026100910` (bump mỗi lần sửa CSS, tránh cache) |
| `images/xiulian/` | — | `nen-ngoi-thien.jpg` (101KB), `banner-lua.png` (15KB) — ảnh lấy từ source LKCT, không sinh bằng AI |

---

## 3. Cấu trúc trang

```
.xl-page  (max-width 760px)
├── .xl-stage        ảnh ngồi thiền + vòng hào quang
│   ├── .xl-rail-left   Công Pháp · Linh Căn · Thể Chất · Huyết Mạch · Luyện Đan
│   ├── .xl-rail-right  Trang Bị · Chế Tạo · Võ Công · Bí Tịch · Thiên Phú
│   ├── .xl-power      Chiến Lực (combat_power)
│   └── .xl-res        Linh thạch · Ma thạch
├── .xl-grid  (2 cột)
│   ├── .xl-realm     nhãn cảnh giới (1 dòng) + .xl-realm-note + thanh tu vi   |  .xl-modes  Thường / Linh Thạch x2 / Ma Thạch x3
│   └── .xl-stats     9 ô số: Tu luyện / Đột phá / Tấn Công / Phòng Thủ / Khí Huyết / Bạo Kích / Hút Máu / Trang bị / Hấp thụ
├── .xl-action       ← 1fr auto 1fr :  [chip trái] [ORB] [chip phải]
├── .xl-status       Đã ngồi · trạng thái · tiêu hao · giới hạn
└── .xl-tabbar       6 nút: Tu Luyện · Nhân Vật · Bản Đồ · Tông Môn · Nhiệm Vụ · Cửa Hàng
```

Thứ tự DOM của `.xl-action` **bắt buộc** là trái → orb → phải, vì lưới `1fr auto 1fr` mới tự canh orb vào giữa.

---

## 4. Orb: một nút ba vai

`$orb_break = (!$running && $break_ready)`; `$break_ready = ($player->umaxexp > 0 && $player->uexp >= $player->umaxexp)`.

| Điều kiện | nhãn | phụ đề | href | màu |
|---|---|---|---|---|
| `$running` (`sfxl > 0`) | DỪNG | thu hồi linh khí | `cmd=endxiulian` | xanh dương |
| `$orb_break` | ĐỘT PHÁ | tu vi viên mãn | `cmd=tupo` | vàng + `xlPulse` 1.6s |
| còn lại | TU LUYỆN | hấp thu linh khí | `cmd=startxiulian&canshu=1\|2\|3` theo chế độ đang chọn | đỏ cam |

Pill `0.00 tu vi` (`.xl-orb-meta`) **chỉ hiện khi không phải state ĐỘT PHÁ**; `#xlexp` bị xoá khỏi DOM nên JS ticker phải guard `if (el)` (đã guard ở `put()`).

**Orb ĐỘT PHÁ chỉ mở trang `cmd=tupo`, không tự roll.** Trang đó tự tính chi phí và tỉ lệ, và thất bại có thể mất tu vi → phải giữ bước xác nhận.

### Chip hai bên orb

```php
if ($orb_break) { left = [Dùng Đan];            right = [Lò Đan]; }
else            { left = [Dùng Đan, Lò Đan];    right = [Đột Phá]; }
```

Khi orb đã là Đột Phá thì tách đôi 2 nút đan sang mỗi bên một cái để hàng cân — không để lệch 2-1.

---

## 5. Vì sao gộp Đột Phá vào orb (đã trace server-side)

Ở 100% tu vi, bấm TU LUYỆN là **đốt tài nguyên mà không nhận thêm điểm nào**:

1. `game.php:432-439` đổi cưỡng bức `startxiulian` → `endxiulian` khi `uexp >= umaxexp && istupo() != 8`.
2. `game/xiulian.php:182` `min($real_min * ($exp_per_sec * 60), $player->umaxexp)` chặn tu vi vượt nấc.

Nên chi Linh thạch/Ma thạch cho cả một chu kỳ chỉ để bị clamp. Chuyển orb sang ĐỘT PHÁ mất không chức năng nào.

**Bẫy:** `\player\istupo()` (class/player.php:1058) chỉ trả về **1** (đại cảnh giới), **2** (cấp độ), **0** hoặc `null` — **không bao giờ trả 8**, nên mọi guard `!= 8` đều luôn đúng.

`umaxexp` không phải cột DB: `class/player.php:844` → `max(50, floor($nlv * $nlv * 110))` với `nlv = ulv + 1`.

---

## 6. Công thức đang hiển thị (nguồn: game/xiulian.php)

| Chỉ số | Công thức | Dòng |
|---|---|---|
| Linh khí/giây | `ulv × vip(1.10\|1.00) × hệ_số_chế_độ × min(1.3, 1+cp_absorb/100) × min(1.3, 1+linhcan_absorb/100) × min(1.3, 1+linhcan_exp_bonus/100) × (1 + tupoexp/100)` | 97–99 |
| Hệ số chế độ | Thường ×1, Linh Thạch ×2, Ma Thạch ×3 | 66–85 |
| Chi phí/phút | Linh Thạch: `ceil(max(1, ceil(ulv^1.15/5)) × (1 - pb_reduce))`; Ma Thạch: `max(1, ceil(ulv/3))`; Thường: 0 | 69–75 |
| Trần thời lượng | Thường 30', Linh Thạch 180', Ma Thạch 360' (và bị chặn bởi số tài nguyên đang có) | 106–114 |
| ETA Đột phá | `ceil((umaxexp - uexp) / exp_per_sec)` | 225–227 |

Khi kết thúc: trừ tài nguyên, cộng tu vi (bị giảm bởi đan độc qua `luonden_get_stat_penalty`), cộng 5% tu vi cho Tông Môn, hook nhiệm vụ `meditate` / `cultivate`. **Chặn dừng non 5 giây** (dòng 172).

---

## 7. Ràng buộc CSS phải giữ (MỒI — dễ tái phạm)

1. **`css/gamecss.css:70` có `a:link{display:inline-block;margin:4px 3px 2px 4px}`** — specificity `0,1,1` **đè mọi rule một lớp** kiểu `.xl-mode{display:flex}`. Dấu hiệu: rule đã parse đúng trong CSSOM nhưng computed `display` vẫn là inline-block, chữ căn trái. chữa: viết selector `a.xl-xxx` (rule gốc dùng dạng nào thì rule trong `@media` phải dùng đúng dạng đó) + reset `.xl-page a,.xl-page a:link,…{margin:0}` ở đầu khối (dòng 749).
2. **Không dùng emoji nằm trong BMP** (⚔️ U+2694, ✨, ⚡) làm icon trong ô flex nhỏ — Segoe UI Emoji cho advance width lệch khiến icon + nhãn dồn một hàng. Chỉ dùng emoji astral-plane. Ô icon phải có `display:block; width:100%; height:…; line-height:…; overflow:hidden` + font stack emoji tường minh.
3. **Ô chứa emoji không được `padding:0`.** Glyph emoji cao hơn nhiều so với `font-size`/`line-height` nên dù `overflow:hidden` vẫn chạm mép viền (lỗi "icon đè vào viền" của hàng 6 nút, v12). chữa: ô có `min-height` đủ lớn **cộng** padding đứng `7px 3px 6px`; tăng chiều cao chứ không giảm chữ.
4. **Chip phải đặt `width` cố định, không `min-width`.** Chip auto-width co theo nội dung nên "Dùng Đan" luôn dài hơn "Lò Đan". Hiện: `126px` desktop / `112px` ≤760 / `width:100%` ≤430 (ăn đúng bề rộng cột 98px đã đo).
5. **Cột hai bên orb tự canh giữa**, không neo vào phía orb: `.xl-action-side{flex-direction:column;align-items:center;justify-content:center}` + `.xl-action{gap:18px}`. Kiểu cũ `justify-content:flex-end`/`flex-start` kéo chip áp sát orb và bỏ trống hai mép ngoài card.
6. **Đổi CSS phải quét hết selector trùng trong file** (rule sau đè rule trước) — khối `.xl-*` có 3 tầng breakpoint, mỗi selector nút thường xuất hiện 3 lần.
7. Ở ≤430px **giữ `gap:8px`** cho `.xl-action`: cột hai bên chỉ còn `(350−138−16)/2 = 98px` đúng bằng bề rộng chip đã đo; nới gap thêm là tràn hoặc phải thu chữ.
8. `.tcg-topbar` cao ~217px ở viewport 390px (menu 5 mục xếp chồng) — nằm ngoài khối `.xl-*`, nên trang tu luyện trên điện thoại luôn cần cuộn. Muốn ngắn hơn phải sửa topbar toàn cục (**chưa được duyệt**).
9. **Nhãn cảnh giới 1 dòng — KHÔNG được nới bề rộng giao diện** (v13 → **v14 sửa lại**). Bài học: nguyên nhân KHÔNG phải "khung trang hẹp" như kết luận v13 (đã bác bỏ, xem §8 v14). Ba việc phải giữ cùng lúc:
   - `xl_realm_label()` (dòng 32) cắt chuỗi tại `<br/>`: `getJingJieHtml()` ở trạng thái **Viên Mãn** (`innerLv == 0`) trả HTML có `<br/> ( Pháp Tắc Hộ Thuẫn Nhập Cảnh - Tỉ Lệ Đột Phá +10% )` — đó mới là thứ làm vỡ dòng. `main` giữ nguyên tag màu vào `.xl-realm-name`; `note` strip tag + bỏ ngoặc đơn → `.xl-realm-note` (12.5px, được phép quấn). **Không** sửa `getJingJieHtml()` — nhiều trang khác dùng chung.
   - **Chặn thẻ tự phình:** `.xl-realm` là flex item trong cột `align-items:center` nên `min-width:auto` cho phép nó lớn theo nội dung; `white-space:nowrap` đẩy nó từ 323px → 411px và **lấn sang cột chỉ số**. chữa: `.xl-realm{min-width:0;max-width:100%}` + `.xl-col{min-width:0}`. Panel giữ nguyên `min(660px,…)` của `css/mystyle.css:199`.
   - `fitRealm()` (JS, dòng ~454) **phải đo bằng `Range.getBoundingClientRect()`**, vì `scrollWidth − clientWidth` **báo 0** dù chữ nowrap tràn ra ngoài (đã kiểm chứng) → dùng `scrollWidth` thì hàm không bao giờ chạy. Thuật: hạ dần `font-size` tới sàn **13px**, vẫn không đủ thì trả `white-space:normal` (thà 2 dòng còn hơn chữ 12px hoặc tràn thẻ). Chạy lại khi `resize`.
   - Đệm nhãn `padding:3px 6px` (từ 10px) để dành 8px cho tên dài — chỉ là đệm trong pill, không đổi cỡ chữ.
10. `body.bg-body{zoom:.75}` trong `@media (min-width:1024px)` (dòng 5623–5628) — **`getBoundingClientRect()` trả px đã nhân zoom, còn `clientWidth`/`offsetWidth` trả px CSS chưa nhân.** Trộn hai loại là ra số vô nghĩa (đây là cách tôi kết luận sai ở v13). Khi đo số liệu nhỏ: đặt `document.body.style.zoom = 1` trong harness rồi đo, mọi số cùng một hệ.

---

## 8. Nhật ký phản hồi của chủ dự án

| Bản | Câu nói | Vấn đề thật | Cách sửa |
|---|---|---|---|
| v5 | "các nút chữ chưa được căn giữa theo dọc và ngang" | `a:link` đè display | selector `a.xl-*` (§7.1) |
| v6 | "giao diện vẫn bị dài quá" | nhiều khối chồng | gom về 1 màn, `.xl-grid` 2 cột |
| v7 | "gộp chung 2 nút thành 1 có phải hay hơn không" | orb + nút Đột Phá tách đôi | orb 3 vai (§4) |
| v8 | "để 3 nút Dùng Đan, Lò Đan Và Đột Phá dưới chấn sát quá không đẹp" | chip nằm dưới orb | chip sang hai bên, grid `1fr auto 1fr` |
| v9 | "sao bạn cứ đặt dính sát 3 nút dính sát nút tu luyện thế" | chip bị neo vào orb | cột tự canh giữa + gap 18 (§7.5) |
| v10 | "nút dùng đan và lò đan cho width bằng nhau đi" | chip auto-width lệch nhau | `width` cố định (§7.4) |
| v11 | "0.00 Tu vi cho về vị trí cũ như nãy đi, dịch cái bar là được rồi" | **tôi sửa nhầm khối**: "cái bar" là thẻ `.xl-status`, không phải pill `.xl-orb-meta` | revert pill về `bottom:-4px`; chỉ giữ `.xl-action{padding-bottom:16px}` |
| v12 | "cái bar ở cuối 6 nút hơi dẹt… icon đè vào viền kìa" | ô tab `padding:0` + emoji overflow | ô 58px + padding đứng (§7.3) |
| v13 | "Nhập Môn Sơ kỳ 2 Giai đang bị thành 2 dòng kìa sửa thành 1 dòng đi" | nhãn 1 dòng | `xl_realm_label()` + nowrap + `fitRealm()` — **nhưng kèm sửa sai: nới `.tcg-main-panel:has(.xl-page)` lên 800px** |
| v14 | "Bảo bạn sửa chữ nhập môn 2 giai thì 2 dòng về 1 dòng chứ bảo bạn tăng chiều rộng toàn giao diện đâu" | **v13 đã vượt phạm vi**: panel 660px chưa bao giờ là nguyên nhân (chuỗi "Nhập Môn Sơ kỳ 2 Giai" = 214px trong chỗ trống 271px → vẫn 1 dòng khi đo lại bằng tài khoản thật ulv=17) | **revert** rule `:has(.xl-page)` (panel về 660px như v12); thay bằng `.xl-realm{min-width:0}` để thẻ không phình; `fitRealm()` đo bằng `Range` (không dùng `scrollWidth`), sàn 13px, hết cỡ thì mới quấn dòng (§7.9) |

**Rút kinh nghiệm:** (1) chủ dự án gọi phần tử theo **cảm nhận thị giác trong ảnh** ("cái bar", "chân nút"), không theo tên class — phải khớp khối trong ảnh ↔ khối trong mã trước khi sửa, và nói rõ tên khối đã hiểu trong câu trả lời để bị bác sớm. (2) **Sửa đúng phạm vi được yêu cầu**: muốn đổi một khối nhỏ thì không được nới khung chung của cả trang; nếu thấy "cần" nới, phải hỏi trước. (3) Kết luận nguyên nhân bằng **tài khoản thật ở đúng trạng thái báo lỗi** (seed ulv=17), không bằng chuỗi tự dựng trong harness — v13 đã đoán sai vì đo trên state Viên Mãn.

---

## 9. Quy trình kiểm chứng (lặp lại được)

```bash
# 1. Seed tài khoản E2E (dev helper, KHÔNG nằm trong repo game)
php E:/window/Temp/xlhelp.php link|idle|mid|full|run
#    full = tu vi 100% (state ĐỘT PHÁ) · mid = 55% · run = đang tu luyện
#    link in ra cmd đã encode: cmd=goxiulian&sid=<e2e sid>

# 2. Chụp desktop
chrome.exe --headless=new --disable-gpu --hide-scrollbars --no-first-run \
  --force-device-scale-factor=1 --user-data-dir="$(mktemp -d)" \
  --window-size=1337,1200 --virtual-time-budget=12000 \
  --screenshot="<abs>/shot.png" "http://127.0.0.1:8090/game.php?cmd=$CMD"

# 3. Chụp mobile THẬT: --window-size có sàn ~500px nên phải render trong iframe 390px
#    (file harness đặt tạm trong web root, XOÁ NGAY sau khi chụp)
```

- `--screenshot` phải là **đường dẫn tuyệt đối**; `--virtual-time-budget` bắt buộc để font/emoji load xong.
- **Không** dùng `--force-prefers-reduced-motion` (chặn load `<img>` → ảnh nền trắng).
- Kết quả đo trong iframe phải ghi ra `document.title`, **không** ghi vào phần tử `left:-9999px` (nó làm tài liệu cha rộng ra, iframe `margin:0 auto` bị đẩy lệch → tưởng nhầm là tràn lề).
- Crop bằng PIL để **nhìn**, không chỉ đọc số: `scrollWidth < innerWidth` không bắt được lỗi icon chạm viền.
- Sau mỗi lần sửa CSS: `php -l`, đếm cặp ngoặc nhọn, quét non-ASCII trong khối `.xl-*`, bump `?v=` ở **cả 3 file**.

### 9.1 Playbook copy-paste (đỡ phải nghĩ lại từ đầu)

Helper dev nằm ở `E:/window/Temp/` — **NGOÀI repo game**, không commit. Tài liệu ghi lại lệnh + SQL để dựng lại trên máy khác.

```bash
PHP=D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe   # php không có trên PATH

# --- 1. Seed trạng thái tài khoản E2E (sid e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2) ---
$PHP E:/window/Temp/xlhelp.php link     # gia hạn endtime +4h, in ra cmd đã encode
$PHP E:/window/Temp/xlhelp.php idle     # sfxl=0
$PHP E:/window/Temp/xlhelp.php mid      # uexp = 55% umaxexp
$PHP E:/window/Temp/xlhelp.php full     # uexp = umaxexp  -> state DOT PHA
$PHP E:/window/Temp/xlhelp.php run      # sfxl=1 + xiuliantime = now-240s
$PHP E:/window/Temp/xlset.php 17 7128   # ve dung canh gioi chu du an bao loi
$PHP E:/window/Temp/xllv.php low        # backup ulv/uexp vao e2e_backup TRUOC khi seed
$PHP E:/window/Temp/xllv.php restore    # tra tai khoan ve trang thai cu

# --- 2. Sau khi sua: 4 kiem tra bat buoc ---
$PHP -l game/xiulian.php
grep -c "{" css/mystyle.css && grep -c "}" css/mystyle.css    # phai bang nhau (hien 3879/3879)
awk 'NR>=748 && NR<=878' css/mystyle.css | grep -nP "[^\x00-\x7F]"   # 0 ket qua
sed -i "s/mystyle.css?v=[0-9]\+/mystyle.css?v=2026100911/" game.php gm/gm.php gm/player.php
# ^ so bump = YYYYMMDD + so lan sua trong ngay; hien tai dang 2026100910 nen vi du tren la 11
```

SQL tuong duong (khi phai tu viet helper):

```sql
-- umaxexp KHONG phai cot: max(50, FLOOR((ulv+1)*(ulv+1)*110))  (class/player.php:844)
UPDATE game1 SET ulv=17, uexp=7128, sfxl=0 WHERE sid='e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 4 HOUR) WHERE sid='...';  -- MySQL +7 lech PHP +8
-- jieduan CUNG khong phai cot (dan xuat tu uexp/umaxexp) -> UPDATE no thi loi #1054
```

Harness do kieu long (mobile that + so do px CSS):

```html
<!-- _xlm.html dat tam TRONG web root, XOÁ NGAY sau khi do xong -->
<iframe id="fr" src="http://127.0.0.1:8090/game.php?cmd=<CMD>" style="width:390px;height:1500px"></iframe>
<script>
setTimeout(function(){
  try {
    var d = document.getElementById('fr').contentDocument;
    d.body.style.zoom = 1;                 // BAT BUOC: huy body.bg-body{zoom:.75}
    var el = d.querySelector('.xl-realm');
    document.title = 'W=' + Math.round(el.getBoundingClientRect().width);
  } catch (e) { document.title = 'ERR:' + e.message; }   // khong co try thi silently dead
}, 1600);
</script>
```

```bash
# Truyen kich thuoc qua document.title (khong dung phan tu left:-9999px)
chrome.exe --headless=new --disable-gpu --hide-scrollbars --no-first-run \
  --window-size=1400,1500 --virtual-time-budget=15000 --dump-dom "http://127.0.0.1:8090/_xlm.html" \
  | grep -o "<title>[^<]*</title>"
```

Bang cua `E:/window/Temp/`: `xlhelp.php` (seed 5 kieu), `xlset.php` (seed ulv/uexp), `xllv.php` (backup/restore), `jj.json` (85 ten canh gioi trich tu `class/rangesjj.php`), `mk_xl*.py` (generator harness + sweep 340 nhan). Windows `python -c "print(...)"` in tieng Viet se loi `charmap cp1252` -> ghi file bang `io.open(..., encoding="utf-8")`; Git Bash `/tmp` khong nhin thay tu Python.

**Bằng chứng nghiệm thu** — lưu trong `docs/img/xiulian/` (không để ở web root vì docroot truy cập được công khai):

| File | Nội dung |
|---|---|
| `shot_xl_dai_nhat.png` | ghép 1:1 mock (ảnh tham khảo LKCT) ↔ bản đã dựng |
| `shot_xl_v6_desktop.png` / `_idle.png` / `_mobile.png` | bản nén còn 1 màn hình (v6) |
| `shot_xl_v12_tabbar_desktop.png` | bản v12 ở 1337px: orb + chip hai bên + tabbar 6 nút |
| `shot_xl_v12_tabbar_mobile.png` | bản v12 trong viewport 390px thật |
| `shot_xl_v13_vienman_desktop.png` / `_mobile.png` + `crop_xl_v13_vienman_*.png` | bản v13 (panel 800px — **đã bị bác ở v14**), state **Viên Mãn**: nhãn 1 dòng + `.xl-realm-note` bên dưới |
| `shot_xl_v14_width_restored_desktop.png` | bản v14 ở 1337px — panel về đúng 660px như v12 |
| `shot_xl_v14_ownerstate_desktop.png` / `_mobile.png` + `crop_xl_v14_ownerstate_*.png` | **nghiệm thu thật**: seed tài khoản E2E về `ulv=17` đúng cảnh giới chủ dự án báo lỗi → "Nhập Môn Sơ kỳ 7 Giai" 1 dòng ở cả 1337px và 390px, bề rộng giao diện như v12 |
| `shot_xl_v14_vienman_desktop.png` + `crop_xl_v14_vienman_desktop.png` | state Viên Mãn (`ulv=1000`) sau khi panel về 660px |

**Số đo nghiệm thu v14** (harness iframe, đã đặt `body.style.zoom = 1` để mọi số cùng hệ px CSS):

| Viewport | `.tcg-main-panel` | `.xl-realm` | chỗ trống cho chữ | kết quả quét 85 tên × 4 hậu tố = **340 nhãn** |
|---|---|---|---|---|
| 1337px | **660px** (bằng v12) | 323px | 271px | **0 nhãn phải quấn dòng**; 314/340 giữ 16.5–17px, chỉ 26 nhãn cảnh giới cuối tụt 13–16px |
| 390px | 372px | 350px | ~330px | **0 nhãn quấn dòng**; 176/340 ở 16px, 163 ở 15.5px, 1 ở 15px |

Trước khi chặn phình thẻ, nhãn "Hỗn Nguyên Thánh Nhân Trung kỳ 10 Giai" (354px) làm `.xl-realm` **nở tới 411px** và lấn cột chỉ số → đó là lý do phải có `.xl-realm{min-width:0}`. Cao trang desktop state Viên Mãn ~638px, mobile ~1036px; `scrollWidth == innerWidth` ở cả hai (không tràn ngang).

---

## 10. Nợ còn lại

**CHƯA test**
- Cảm ứng thật trên điện thoại (mới giả lập viewport 390px).
- Nhân vật thường / người chơi thật — hub mới chạy trên tài khoản E2E `e2e2e2e2…`.
- Không kiểm chứng trong phiên có tên nhân vật hoặc số tài nguyên dài bất thường.
- **v13 (đã bác ở v14):** nới `.tcg-main-panel:has(.xl-page)` lên 800px **không nằm trong yêu cầu** → đã revert. Quy tắc còn lại: trang này **không được đổi bề rộng khung chung**; muốn nới phải xin ý kiến trước.
- **v14:** nhãn "Nhập Môn Sơ kỳ 7 Giai" và state Viên Mãn đã nghiệm thu bằng **tài khoản thật seed đúng cảnh giới** (`ulv=17` / `ulv=1000`, đã trả về `ulv=1000` sau khi chụp). Đường tụt chữ của `fitRealm()` (13–16px) vẫn mới đo bằng chuỗi dựng trong harness cho 26 nhãn cảnh giới cuối — **chưa** có nhân vật thật ở các cảnh giới đó.
- **ĐÃ đóng một phần (09/10/2026):** chủ dự án tự chụp **tài khoản thật của mình** (`tttttt`, ID 548, màn hình 1920px) sau bản v14 → "Nhập Môn Sơ kỳ 2 Giai" hiển thị **1 dòng** trong khung 660px. Nghĩa là nợ "nhân vật thật" của **nhãn cảnh giới** đã có bằng chứng ngoài E2E; các phần còn lại ở mục trên vẫn mở.

**Chờ chủ dự án quyết**
- Rail trái "Luyện Đan" và chip "Lò Đan" **cùng trỏ `cmd=luonden`** — trùng link thật, nên bỏ một bên hay đổi một bên sang cmd khác?
- `.tcg-topbar` ~217px trên mobile (sửa toàn cục, ngoài phạm vi đã duyệt).

**Ghi chú môi trường dev**
- `php` không có trên PATH → `D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe`.
- Server dev `php -S 127.0.0.1:8090 -t WWW`; Apache của chủ dự án `127.0.0.1:8123` (cùng docroot).
- Bảng người chơi là `game1`; MySQL (+7) lệch PHP (+8) nên seed `endtime` phải `INTERVAL 4 HOUR`.

---

## 11. Bảng tra nhanh: lỗi đã gặp → cách tránh (v5 → v14)

Mục đích: gặp triệu chứng là tìm ra nguyên nhân **trong 10 giây**, không phải dò lại từ đầu. Mỗi dòng dưới đây là một lỗi **đã thật sự xảy ra** ở trang này.

### A. CSS / layout — thấy bằng mắt trên ảnh

| # | Triệu chứng | Nguyên nhân thật | Cách tránh |
|---|---|---|---|
| A1 | Chữ trong nút không căn giữa, `display:flex` "không ăn" | `css/gamecss.css:70` `a:link{display:inline-block;margin:…}` specificity `0,1,1` đè rule một lớp | Mọi selector nút `<a>` viết `a.xl-*` (cả trong `@media`) + reset `.xl-page a,.xl-page a:link{margin:0}` (§7.1) |
| A2 | Icon + nhãn dồn một hàng, lệch trái | Emoji BMP (⚔️ U+2694, ✨, ⚡) có advance width lệch trong Segoe UI Emoji | Chỉ emoji astral-plane; ô icon `display:block;width:100%;height:…;line-height:…;overflow:hidden` (§7.2) |
| A3 | "Icon đè vào viền" (hàng 6 nút, v12) | Ô chứa emoji `padding:0`; glyph cao hơn `font-size` nên `overflow:hidden` không cứu | Ô có `min-height` **cộng** padding đứng `7px 3px 6px`; tăng chiều cao, **không** giảm chữ (§7.3) |
| A4 | Hai chip hai bên orb dài ngắn lệch nhau | `min-width` + width auto → chip co theo nội dung | `width` cố định: 126px desktop / 112px ≤760 / 100% ≤430 (§7.4) |
| A5 | Chip dính sát orb, hai mép card trống hoang | Cột dùng `justify-content:flex-end`/`flex-start` (neo vào orb) | `align-items:center;justify-content:center` + `.xl-action{gap:18px}` (§7.5) |
| A6 | Orb không nằm giữa hàng action | Thứ tự DOM sai với lưới `1fr auto 1fr` | DOM bắt buộc: trái → orb → phải (§3) |
| A7 | Trang phải cuộn 2–3 màn | Nhiều khối chồng dọc | Gom về `.xl-grid` 2 cột, nén `.xl-status` (§8 v6) |
| A8 | Đã sửa CSS mà computed style vẫn giá trị cũ | Cùng selector xuất hiện 3 lần (nền tảng / ≤760 / ≤430), rule sau đè rule trước | `grep -n "ten.selector" css/mystyle.css` **trước khi** kết luận (§7.6) |
| A9 | 390px tràn ngang hoặc chữ bị thu | `gap` ở ≤430 lớn hơn chỗ còn lại (cột hai bên chỉ 98px) | Giữ `gap:8px` ở ≤430, đã tính theo bề rộng chip đo được (§7.7) |
| A10 | Nhãn cảnh giới làm **thẻ phình**, lấn cột chỉ số bên phải | `white-space:nowrap` + flex item mặc định `min-width:auto` → `.xl-realm` 323 → **411px** | `.xl-realm{min-width:0;max-width:100%}` + `.xl-col{min-width:0}` (§7.9) |
| A11 | Nhãn vỡ 2 dòng **chỉ ở state Viên Mãn** | `getJingJieHtml()` (`class/player.php:2984`) nhét `'<br/> ( Pháp Tắc Hộ Thuẫn … )'` khi `innerLv==0` | Cắt tại `<br/>` bằng `xl_realm_label()` (`game/xiulian.php:32`), chú thích xuống `.xl-realm-note`; **không** sửa hàm dùng chung |
| A12 | JS ticker lỗi / pill hiện `0.00 tu vi` sai chỗ | `#xlexp` bị **xoá khỏi DOM** ở state ĐỘT PHÁ | Guard `if (el)` trong `put()`; pill chỉ render khi không phải ĐỘT PHÁ (§4) |

### B. Đo đạc / JavaScript — số sai hơn cả không đo

| # | Triệu chứng | Nguyên nhân thật | Cách tránh |
|---|---|---|---|
| B1 | Hai phép đo cùng một phần tử cho hai số khác nhau, không suy ra được gì | `body.bg-body{zoom:.75}` (`css/mystyle.css:5623`): `getBoundingClientRect()` trả px **đã nhân zoom**, `clientWidth/offsetWidth` trả px **CSS** — trộn hai loại là vô nghĩa (đây là nguồn sai lầm v13) | Trong harness: `iframeDoc.body.style.zoom = 1` **trước** khi đo; chỉ dùng một hệ px |
| B2 | Hàm "thu chữ cho vừa" không bao giờ chạy | `scrollWidth − clientWidth` **báo 0** dù chữ nowrap tràn ra ngoài thẻ | Đo bề rộng chữ bằng `document.createRange(); range.selectNodeContents(el); range.getBoundingClientRect().width` (`fitRealm()`, `game/xiulian.php:455`) |
| B3 | Harness chạy im lặng, `--dump-dom` không có `<title>` | Chuỗi JS tiêm vào bị chưa đóng ngoặc (dùng `repr(RAW).replace("'", '"')` mà RAW chứa `"`) | Sinh bằng `json.dumps()`; bọc `run()` trong `try/catch` và ghi `'ERR:' + e.message` ra title |
| B4 | `avail=NaN`, vòng lặp không chạy | Quên `parseFloat()` quanh `paddingLeft/Right` khi trừ đệm | Luôn `parseFloat(x) \|\| 0` cho mọi giá trị computed style |

### C. Harness / công cụ chụp ảnh

| # | Triệu chứng | Nguyên nhân thật | Cách tránh |
|---|---|---|---|
| C1 | Không chụp được màn 390px, ảnh tối thiểu ~500px | `chrome --window-size` có sàn | Render trong `<iframe width:390px>` cùng origin |
| C2 | Ảnh nền trắng, mất hết `<img>` | `--force-prefers-reduced-motion` chặn load ảnh | Không dùng cờ đó; dùng `--virtual-time-budget=12000..15000` |
| C3 | Tưởng trang tràn lề trái | Kết quả đo ghi vào phần tử `left:-9999px` → tài liệu cha giãn, iframe `margin:0 auto` lệch | Ghi số đo ra `document.title` |
| C4 | `--screenshot` chạy xong không thấy file | Đường dẫn tương đối bị resolve theo vị trí khác | Luôn dùng **đường dẫn tuyệt đối** |
| C5 | Python in tiếng Việt sập `UnicodeEncodeError: charmap cp1252` | Console Windows không phải UTF-8 | Ghi file bằng `io.open(path, "w", encoding="utf-8")`, đừng `print()` |
| C6 | Python không đọc được file tôi vừa ghi ở `/tmp` | `/tmp` của Git Bash không phải đường dẫn Windows | Làm việc trong `E:/window/Temp/` |
| C7 | Ảnh nghiệm thu / harness truy cập được từ ngoài | Docroot Apache phục vụ công khai | Ảnh → `docs/img/xiulian/`; harness trong web root thì **xoá ngay** sau khi đo |
| C8 | Khối vừa sửa chứa ký tự lạ (smart quote, chữ Hoa lạc chỗ) | Edit/Write chèn ký tự lỗi khi nội dung dài | Quét non-ASCII (`grep -nP "[^\x00-\x7F]"`) trên khối CSS; PHP/JS thì chạy `php -l` |

### D. Dữ liệu / server-side

| # | Triệu chứng | Nguyên nhân thật | Cách tránh |
|---|---|---|---|
| D1 | Seed xong vào trang vẫn bị đá về login | `endtime` ghi theo giờ PHP (+8), MySQL chạy (+7) | `DATE_ADD(NOW(), INTERVAL 4 HOUR)` |
| D2 | `#1054 Unknown column 'jieduan'` (và `'umaxexp'`) | Cả hai là **dẫn xuất**, không phải cột DB | Chỉ UPDATE `ulv`, `uexp`; `umaxexp = max(50, floor((ulv+1)^2*110))` (§5) |
| D3 | Guard `istupo() != 8` luôn đúng, tưởng có nhánh an toàn | `\player\istupo()` (`class/player.php:1058`) chỉ trả `1 \| 2 \| 0 \| null` | Đừng viết điều kiện dựa trên giá trị 8; đọc hàm trước khi tin (§5) |
| D4 | Bấm TU LUYỆN ở 100% tu vi vẫn trừ Linh thạch mà không thêm điểm | `game.php:432` đổi cưỡng bức sang `endxiulian`; `game/xiulian.php:182` clamp tu vi | Orb phải chuyển sang ĐỘT PHÁ khi `uexp >= umaxexp` (§4/§5) |
| D5 | `git diff` / `git log` trả rỗng khi cần truy dấu "ai sửa cái này" | Repo **0 commit** | Đọc file trực tiếp + dựa vào `?v=` và CHANGELOG, không trông vào git |
| D6 | Sửa một hàm để tiện cho trang này, trang khác vỡ | `getJingJieHtml()`, `combat_power()`… nhiều trang dùng chung | Hàm chung: chỉ **thêm** hàm mới hoặc tách hàm, không đổi hành vi cũ |

### E. Quy trình / phạm vi — nhóm lỗi đắt nhất (toàn bộ do chủ dự án bác)

| # | Triệu chứng | Nguyên nhân thật | Cách tránh |
|---|---|---|---|
| E1 | v11: sửa nhầm pill `.xl-orb-meta` khi ý chủ dự án là thẻ `.xl-status` | Chủ dự án gọi khối bằng **mắt nhìn** ("cái bar", "chân nút"), không gọi tên class | Khớp khối trong ảnh ↔ khối trong mã **trước khi** sửa; nêu lại tên khối đã hiểu trong câu trả lời để bị bác sớm |
| E2 | v13: xin sửa nhãn 1 dòng rồi **nới cả khung trang** 660 → 800px | Thu hẹp triệu chứng bằng cách đổi container chung | **Không đổi bề rộng/khung chung của cả trang** để cứu một phần tử; hết chỗ thì chặn thẻ phình (A10) + cắt chuỗi (A11) + `fitRealm()`. Muốn nới phải hỏi trước |
| E3 | v13: kết luận "cột chỉ còn 242px nên không đủ chỗ" là **sai** | Đo trên state Viên Mãn + trộn đơn vị zoom (B1), không tái hiện đúng state báo lỗi | Kết luận nguyên nhân bằng **tài khoản thật seed đúng cảnh giới** (`xlset.php 17 7128`), rồi mới sửa |
| E4 | Báo "xong" nhưng chủ dự án mở lên vẫn lỗi | Test trên state/viewport khác với state báo lỗi | Seed đúng state → chụp lại → **nhìn crop**, không chỉ đọc số |
| E5 | Chủ dự án xem trang vẫn là bản cũ | Bump `?v=` thiếu 1 trong 3 file nạp CSS | Bump **cả** `game.php:502`, `gm/gm.php:9`, `gm/player.php:9` (§9.1) |
| E6 | "Đã test" bị hiểu là "chạy được trong game thật" | Mới chỉ nghiệm thu bằng ảnh headless | Ghi rõ **CHƯA test** vào §10 + CHANGELOG mỗi lần bàn giao |

### Checklist 6 dòng trước khi báo xong trang này

1. `php -l game/xiulian.php` sạch.
2. `grep -c "{"` == `grep -c "}"` trong `css/mystyle.css`; khối `.xl-*` 0 ký tự non-ASCII.
3. Bump `?v=` ở **cả 3 file**.
4. Seed **đúng state vừa báo lỗi**, chụp desktop 1337px + harness 390px, **mở crop lên nhìn**.
5. Xoá file harness khỏi web root; trả tài khoản E2E về trạng thái cũ (`xllv.php restore`).
6. Ghi `docs/CHANGELOG.md` + §8/§9/§10 của file này, phần nào chưa test phải ghi chưa test.
