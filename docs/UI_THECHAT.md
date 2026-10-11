# Tài liệu: Giao diện Nhục Thân (game/thechat.php)

> Trang **Thể Chất** cũ đã được dựng lại thành **Nhục Thân** theo mẫu mobile của Linh Kiếm Cửu Thiên
> (màn Nhục Thân: hàng tab Cốt Cách trên cùng + thân hình ngồi thiền vây bởi 6 viên ngọc + bảng
> "Thuộc Tính Kích Hoạt" + nút "Luyện hóa" kèm đếm vật liệu).
> Bản này làm theo đúng quy trình đã lập ở `docs/UI_XIULIAN.md` — **đọc §11 của file đó trước khi sửa trang này**.

---

## 1. Bối cảnh

**Yêu cầu của chủ dự án (09/10/2026):**
"Tiếp tục dựa và tài nguyên source linh kiếm cửu thiên để làm tính năng Tu Luyện như hôm trước,
hãy xây dựng lại tính năng Thể chất theo mẫu bên phải và cập nhập lại logic tương ứng"
(kèm 4 ảnh: trang `thechat` desktop hiện tại + 3 ảnh tham khảo LKCT: Nhục Thân / Đan Điền / Kinh Mạch).

**Ba quyết định đã chốt qua AskUserQuestion:**

| Câu hỏi | Trả lời |
|---|---|
| Hàng tab trên cùng lấy dữ liệu từ đâu? | **Gộp Thức Tỉnh vào đây** — tab = 6 bậc Cốt Cách, không còn trang `thechat_awakening` riêng |
| 6 viên ngọc quanh thân hình? | **6 bộ phận, 6 chỉ số khác nhau** (không phải 6 cấp của cùng 1 chỉ số) |
| Luyện hóa tốn gì? | **Tinh Thể Đá + Linh Thạch**, giữ nguyên 2 hệ cũ (thăng phẩm vẫn đi `thechat_upgrade`) |

**Hệ quả quan trọng nhất:** Nhục Thân **không cần có Thể Chất vẫn tu được**. Nhân vật mới tạo theo quyết
định "Khởi đầu nhân vật trắng" (`docs` + memory `project-khoi-diem-trang`) có `thechat_id = 0`, nên
trang phải sống được khi `thechat_info()` trả `false`. Đây là nguồn của con bug ở §7.1.

---

## 2. File đã sửa / đã tạo

| File | Vai trò |
|---|---|
| `class/config/thechat_body_config.json` | **MỚI** — 6 node, 6 tier Cốt Cách, công thức luyện hóa, 5 mốc tổng cấp, tên vật liệu |
| `sql/sql_thechat_body.sql` | **MỚI** — bảng `player_thechat_node (sid, node_id, node_lv, updated_at)` |
| `class/function/thechat.php` | Khối `thechat_body_*` (dòng 667→1006) — toàn bộ logic server-side |
| `game/thechat.php` | **VIẾT LẠI HOÀN TOÀN** (327 dòng) — 1/2 logic + 1/2 template, template **2 card** từ dòng 172 |
| `css/mystyle.css` | **MỚI** khối `.nb-*` dòng **6977→7259** (cuối file), đặt **sau mọi `@media`**. Dòng **7194→7259** = hiệu ứng phân tầng (§12). Dòng **7043→7052** = comment trục + toạ độ 6 ngọc, **đã sửa trục ở v13 Nhục Thân** (§3.1) |
| `game/thechat_upgrade.php` | Trang **Nâng Phẩm** riêng: thêm lớp hiệu ứng `.tcq-*` 10 bậc trong `<style>` của chính trang (§13) + sửa đặc cách nút bị `.main button` ăn mất màu (§13.4) |
| `images/thechat/` | `nhuc-than.jpg` (663×467) + 18 ngọc `ngoc-{stat}-{khoa,sang,day}.png` |
| `game.php` | **Bỏ route** `'thechat_awakening'`; bump `?v=` (`mystyle.css?v=2026100929`, dòng 501) |
| `game/thechat_awakening.php` | → `bak/thechat_awakening.20261009_truoc_nhucthan.php` (repo 0 commit, **không xoá**) |
| `class/player.php` | `getplayer()` nạp bonus Nhục Thân; chú thích dòng ~762 cập nhật |
| `tongquan.php` | Mục mới **"Nhục Thân (6 bộ phận + Cốt Cách)"** (`the-chat-nhuc-than`), 3 bảng 6+6+5 dòng; mục "Thệ chất - thăng phẩm & thức tỉnh" → bỏ bảng thức tỉnh |

---

## 3. Cấu trúc trang (2 card / 3 dải — đã nén từ 6 card)

> Chủ dự án phàn nàn "giao diện chưa tối ưu và quá dài" (09/10/2026) → **gộp 6 card còn 2 card**:
> dải 1 = định danh + rail Cốt Cách + **thân hình full-box** + **lò luyện đứng cạnh**;
> dải 2 = Thuộc Tính | Đột Phá (2 cột). Số đo ở §3.2.

```
.nb-wrap
├─ .nb-msg                thông báo POST (thành công / thất bại)
├─ CARD 1 .nb-card.nb-card-body      (padding:0 — hai khối con tự đệm)
│   ├─ .nb-head           "NHỤC THÂN" + .nb-badge "<icon> <Tier> · trần X"
│   │   ├─ .nb-rail       **CHUỖI 6 bậc**: 5 × `<span class="nb-link">` xen 6 `<a class="nb-tier">`
│   │   │                 (tab: is-done / is-cur / is-lock / is-view · link: is-lit khi bậc bên trái đã đạt)
│   │   └─ .nb-viewline   .nb-phys (phẩm chất HOẶC "Chưa có Thể Chất") · <dòng bậc đang $nbView>
│   └─ .nb-forge-wrap     desktop ≥861px: flex row (stage 380px | forge 1fr)
│       ├─ .nb-stage      ảnh nhuc-than.jpg + 6 <a class="nb-gem slot-…">
│       │                 mỗi gem: .nb-gem-disc (ngoc-{stat}-{khoa|sang|day}.png) + .nb-gem-tag "Tên Lv"
│       └─ .nb-forge      tên bộ phận + "Cấp X / <trần>" + "+n/cấp · đang +n <Label>"
│                         2 chip .nb-cost-item (có / cần) · nút POST "Luyện Hóa"
│                         (đụng trần → .nb-capnote thay 2 chip, nút disabled)
├─ CARD 2 .nb-card.nb-cols           grid 2 cột ≥861px / 1 cột ≤860px
│   ├─ .nb-col            "Thuộc Tính Kích Hoạt": 6 dòng .nb-attr + .nb-ms (tên mốc + bar + mốc kế)
│   └─ .nb-col            "Đột Phá Cốt Cách": .nb-gate (<bậc hiện> → <bậc kế> · trần Y)
│                         + 4 dòng .nb-req [Đủ]/[Thiếu] + nút POST "Đột Phá"
│                         (bậc cực hạn → "Cốt cách đã đạt cực hạn", không có nút)
└─ .nb-tabbar             BAR dưới cùng, full chiều ngang + mỗi nút = 1 ô như .xl-tabbar (§3.4)
                          [🔼 Nâng Phẩm Chất] (chỉ khi thechat_id > 0) · [👤 Nhân Vật] (.on)
```

**URL của trang:** `cmd=thechat&node=<1..6>&view=<0..5>&sid=<sid>` (encode bằng `$encode->encode()`).
`node` = bộ phận đang chọn, `view` = bậc Cốt Cách đang xem. Cả hai đều có fallback:
`POST > GET > mặc định` (dòng 43–65 của `game/thechat.php`).

### 3.1 Ảnh thân hình full-box + 6 ngọc đúng giải phẫu

`.nb-stage` **phải giữ đúng tỉ lệ ảnh gốc 663×467 = 1.420** thì 6 ngọc mới nằm trên người:

```css
.nb-stage{position:relative;overflow:hidden;height:268px;max-width:420px!important;margin:0 auto}
@supports (aspect-ratio:1/1){.nb-stage{height:auto;aspect-ratio:663/467}}
.nb-figure{position:absolute;inset:0;width:100%;height:100%!important;object-fit:cover}
```

- `padding-bottom:70.44%` **không dùng được**: padding % resolve theo **bề rộng containing block**,
  không theo `flex-basis` của chính phần tử → desktop ra `380×420` (dọc), vỡ hết vị trí ngọc.
  Chuyển sang `aspect-ratio` + fallback `height` cứng.
- `.nb-figure` phải `height:100%!important` — `mystyle.css:5035-5040` có
  `.tcg-main-panel img{max-width:100%!important;height:auto!important}` làm ảnh co lại, chừa dải trống.
- `max-width` phải `!important` vì `.tcg-main-panel *{max-width:100%!important}` (≤760px) đè rule 1 lớp.

Vị trí ngọc tính theo **% tâm** của ảnh (`left`/`top` + `transform:translate(-50%,-50%)`), đo từ
`nhuc-than.jpg` thật chứ không đoán:

| Ô | % (trái, cao) | Bộ phận | Ghi chú |
|---|---|---|---|
| `top` | 51.3, 21 | Bì Phủ | **đảo tag lên trên đĩa** (`bottom:100%`) — để tag dưới thì đè đầu nhân vật |
| `left-up` | 35.3, 41 | Cơ Nhục | vai/ngực trái |
| `right-up` | 67.3, 41 | Cân Cốt | vai/ngực phải |
| `left-down` | 25.4, 69 | Cốt Tủy | gối trái |
| `right-down` | 75.4, 69 | Huyết Khí | gối phải |
| `bottom` | 50.4, 80 | Thần Thức | đế ngồi thiền, tag dưới đĩa (mặc định `top:100%`) |

`slot-top` ban đầu để 13% → tag bị **lọt ra ngoài khung** (`!TAGt`); phải lùi xuống 21%.
`slot-bottom` 81% → 80% cho khỏi chạm mép dưới.

**Trục của ảnh KHÔNG phải trục giữa khung (v13 Nhục Thân).** Chủ dự án báo "6 điểm tròn lệch trái một chút".
Đo bằng cách lấy từng cột so với cột đối xứng qua ứng viên trục (`nbaxis3.py`), kết quả trên
`nhuc-than.jpg` 663px: **đầu + vai đối xứng quanh 51.28%**, **mông + đài sen quanh 50.38%** —
tức hoạ sĩ vẽ nhân vật nhích sang phải, còn mình đặt ngọc theo 50%/34%/66% (đối xứng giữa khung)
nên cả 6 ngọc lệch trái ~1%. Sửa **theo từng dải** chứ không cộng đều một số: cặp trên 51.3,
đài sen 50.4. Đo lại trong ảnh render: trục ảnh 51.34/51.29/50.37/50.37 so với tâm ngọc
51.30/51.30/50.40/50.40 (lệch ≤0.07%). Desktop `row = 45 + top%*377`, mobile `row = top%*467`
nhưng cả hai đều rơi đúng các hàng 98/191/322/373 → **một bộ `left%` dùng chung cho hai khổ**.

### 3.2 Số đo trước / sau khi nén

Chụp bằng harness `_nbm.html` (iframe, `zoom=1`), trạng thái hero, `wrap` = chiều cao `.nb-wrap`:

| Ngang | Trước (6 card) | Sau (2 card) | card1 | card2 | `stage` | ngọc |
|---|---|---|---|---|---|---|
| 390 (mobile) | 1205 | **1083** | 334×603 | 334×412 | 332×234 | 44px, 6/6 khớp %, không clip |
| 480 | — | **1145** | 416×661 | 416×416 | 414×292 | 46px, ổn |
| 600 (tablet) | — | **1111** | 536×627 | 536×416 | 420×296 | 46px, ổn |
| 1337 (desktop) | 957 | **739** | 598×440 | 598×231 | 380×268 | 46px, lò luyện cạnh ảnh |

Tỉ lệ `stage` ở **cả bốn** = 1.420 (= 663/467) và `crop=0` → ảnh không bị cắt, không bị dải đen.

### 3.3 Chuỗi Cốt Cách (6 bậc liên kết nhau)

Chủ dự án (09/10, xem bản desktop): *"dãy phẩm cấp từ Phàm Cốt đến Tiên Cốt tôi muốn nó liên kết với nhau"*.

Markup trong `game/thechat.php` (vòng lặp rail): giữa hai tab là một **`<span class="nb-link">`**
rỗng, `aria-hidden="true"`, sáng lên khi bậc bên trái đã đạt:

```php
<?php if ($nbT > 0): ?>
    <span class="nb-link<?= $nbT <= $nbStage ? ' is-lit' : '' ?>" aria-hidden="true"></span>
<?php endif; ?>
```

`$nbT <= $nbStage` là **đúng chủ ý**: link số `k` nối bậc `k-1 → k`, nên nó sáng khi bậc `k-1` đã đạt
(`k-1 < stage`). stage=1 → 1/5 link sáng; stage=0 → 0; stage=5 → 5/5. Đã chụp đủ 3 trạng thái
(`crop_v5_rail_3stage.png`).

CSS: `.nb-rail{gap:0}` (trước là 6px) + `.nb-link{flex:0 0 14px;align-self:center;height:3px}` với
mũi tên `::after` (border triangle) chỉ sang bậc kế; `is-lit` = gradient `xanh #6ee7b7 → vàng #f5d18a`
khớp đúng cặp màu "Đã đạt"/"đang chọn" của tab.

**Vì sao `span` chứ không phải `a`:** connector phải nằm **giữa** hai tab nên nếu là `<a>` nó ăn luôn
`a{padding:1px 10px}` + `a:hover{background:#0093e8}` toàn cục (§6.2) và thành ô bấm vô nghĩa.

**Breakpoint mới:** 5 link chiếm 70px nên tab desktop còn 75px (vẫn 1 dòng, `nameH=17`).
Ở ≤860px link rút còn **10px**; ở **≤560px** rail chuyển sang tab cố định 76px + quét ngang
(trước đó rule này nằm ở ≤430px) — **không** thu chữ để nhét đủ 6 tab, vì §6 đã cấm và vì
chính rule cũ từng để `width:86px` cứng làm cắt tab thứ 6.

| Ngang | rail `clientWidth` / `scrollWidth` | tab | link | kết luận |
|---|---|---|---|---|
| 390 | 308 / 548 (quét) | 76×71 | 10px | 1 dòng, không vỡ |
| 480 | 382 / 548 (quét) | 76×71 | 10px | 1 dòng |
| 600 | 502 / **502** | 75–78 | 10px | đủ 6 tab, không quét |
| 700 | 602 / 602 | 85 | 10px | rộng rãi |
| 1337 | 564 / 564 | 75×71 | 14px | `nameH=17` = 1 dòng |

Không tab nào `!WRAP` / `!NARROW`; chiều cao trang **không đổi** ở 390 (1083) và 1337 (739).
Lưu ý khi đo: **phải truyền `--hide-scrollbars`**, vì rail quét ngang sinh scrollbar 15px làm
`wrap` ở 390 đọc nhầm thành 1098.

*(Bảng trên chốt ở bản v5; từ v6 `.nb-foot` được thay bằng `.nb-tabbar` cao hơn → 390 = **1111**,
1337 = **767**, cộng thêm **28px**. Chi tiết + số đo ở §3.4.)*

### 3.4 Bar dưới cùng `.nb-tabbar` (thay nút "← Nhân Vật")

Chủ dự án (09/10, đưa ảnh màn Tu Luyện): *"Cái quay về nhân vật ấy bỏ, sung bằng cái bar dưới cùng
bên Tu Luyện xong để đơn lẻ 1 nút Nhân Vật cho đẹp."* → **bỏ** `.nb-foot` + `.nb-btn-ghost`,
**clone** cấu trúc `.xl-tabbar` (`game/xiulian.php:418`, CSS `css/mystyle.css:824`) chứ không tự
chế kiểu nút mới, để hai màn cùng một ngôn ngữ.
Xem ảnh đối chiếu: `crop_v7_bar_vs_xiulian.png` (bar Nhục Thân đặt ngay trên bar Tu Luyện, cả 2 khổ).

```php
<nav class="nb-tabbar">
<?php if ($nbPhysiqueId > 0): ?>
    <a href="?cmd=<?= $upcmd ?>"><i>🔼</i><b>Nâng Phẩm Chất</b></a>
<?php endif; ?>
    <a class="on" href="?cmd=<?= $ztcmd ?>"><i>👤</i><b>Nhân Vật</b></a>
</nav>
```

- **1 nút hay 2 nút do dữ liệu quyết định**: `$nbPhysiqueId = intval($player->thechat_id)`
  (`game/thechat.php:42`). Chưa có Thể Chất → chỉ còn pill "Nhân Vật" `.on`; đã có → thêm pill
  "Nâng Phẩm Chất" bên trái. **Không cần class trạng thái** — bar là flex căn giữa nên tự co giãn.
- `👤`/`🔼` là emoji **tầng astral** (U+1F464 / U+1F53C) — `⬆` U+2B06 và `❤️` là BMP nên từng bị
  lệch ô ở §6, không dùng lại.
- Nút `.on` vẫn là link thật sang `zhuangtai`, giống hệt cách `.xl-tabbar` đánh dấu tab Tu Luyện.

**Bản v6 đầu tiên đã bị bác** vì hai lỗi nhận dạng: bar bị `max-width:220px` bó hẹp (desktop ra chip
nhỏ, mobile lại giãn full nhờ `!important` của panel — không nhất quán), và pill bị kéo dài hết bar
thay vì bằng một ô Tu Luyện. Chủ dự án chốt: *"bar dài như bar gốc bên Tu Luyện, còn nút Nhân vật
cũng bằng các nút như bên Tu Luyện"*.

**Bài học quy trình: phải đọc HẾT source của thứ mình định copy.** `.xl-tabbar` không chỉ có 7 dòng
ở 824-830 — nó còn **hai override responsive** mà bản v6 không lấy theo:

| Dòng | Media | Nội dung |
|---|---|---|
| 856-859 | `max-width:760px` | `gap:4px` · `padding:6px 5px` · `a{min-height:52px;padding:6px 2px 5px}` · `i{height/line-height:22px;font-size:18px}` · `b{font-size:10px}` |
| 876 | `max-width:430px` | `b{font-size:9.5px;letter-spacing:0;`**`white-space:nowrap`**`}` |

`white-space:nowrap` chính là lý do nhãn Tu Luyện **không bao giờ xuống dòng**. Bản v6 thiếu nó nên
"Nhân Vật" vỡ 3 dòng ở 390px và "Nâng Phẩm Chất" vỡ 3 dòng ở 1337px (`pageH` 1111 → 1135).

**Công thức ô** (`.nb-tabbar` dùng flex chứ không grid 6 cột, vì ở đây chỉ có 1–2 nút):
`flex:0 1 auto` + `min-width:calc((100% - 30px)/6)` (≥761px) / `calc((100% - 20px)/6)` (≤760px,
gap 4px) — đúng bằng bề rộng một ô của grid `repeat(6,minmax(0,1fr))` bên Tu Luyện. Nhờ `auto` basis
mà ô **không bao giờ hẹp hơn 1 ô Tu Luyện**, nhưng vẫn tự nong ra khi nhãn dài hơn ("Nâng Phẩm Chất")
thay vì vỡ dòng; `max-width:100%` chặn trường hợp nhãn quá dài.

Số đo harness `_nbb.html` — **so trực tiếp với trang Tu Luyện thật**, không so với trí nhớ:

| Ngang | ô `.nb-tabbar` | ô `.xl-tabbar` | bar nb | bar xl | dòng nhãn |
|---|---|---|---|---|---|
| 390 | 50×60 | 53×62 | 334×74 | 350×76 | 1/1 |
| 480 | 64×60 | 68×62 | 416×74 | 440×76 | 1/1 |
| 760 | 111×60 | 115×62 | 696×74 | 720×76 | 1/1 |
| 1337 | 92×64 | 96×64 | 598×80 | 622×80 | 1/1 |

Lệch 2–4px còn lại **không phải của bar**: `.nb-wrap` hẹp hơn container của xl 16px (`.nb-wrap{margin:8px}`
ở ≤430px), bar nào nằm trong đó cũng bị trừ theo. *(→ v8 đã bỏ margin này, xem §3.5: lệch về 0.)*
Cùng harness còn phát hiện `.xl-tabbar` **của nó**
bị `!OVER` 1px ở nhãn "Tông Môn"/"Nhiệm Vụ" tại 390px — bug của trang Tu Luyện, không sửa (ngoài phạm vi).

Trạng thái 2 nút (`thechat_id=1` seed tạm): 390px → "Nâng Phẩm Chất" `84×60` + "Nhân Vật" `50×60`;
480 → `91×60` + `64×60`; 1337 → `101×64` + `92×64`. **Bar vẫn 74/80px cao** — nhãn dài không làm bar lồi.

`legacy=gone` + `grep` toàn repo (trừ `bak/`) 0 kết quả cho `.nb-foot` / `.nb-btn-ghost` / `has-up`.

**Chiều cao trang:** 390px `wrap` **1111 → 1135 → 1111** (v5 → v6 lỗi vỡ dòng → v7), 1337px **739 → 791 → 767**.
So với v5 vẫn **+28px** ở desktop và **+0px** ở mobile — pill cao `min-height:58` (52 ở ≤760) + đệm
chính bar, đó là giá của việc khớp `.xl-tabbar`, chấp nhận có chủ ý.

**Chưa test:** chưa bấm thật hai pill trên điện thoại thật — ảnh là Chrome headless.


---

### 3.5 v8: content phải chiếm HẾT khung như `.xl-page` (`.nb-wrap` margin 0)

Chủ dự án (09/10, kèm ảnh desktop 1337px):

> "box so với chiều dài trang thì chưa hết tôi muốn cải thiện giao diện cho hết chiều dài trang
> như box bên tu luyện ấy. Vậy cho tối ưu hơn"

**Đo trước khi sửa** (`_nbw.html`, cả hai trang cùng panel `.tcg-main-panel` 660px):

| viewport | `.nb-wrap` | `.xl-page` | lệch |
|---|---|---|---|
| 390 | 334 | 350 | **−16** |
| 760 | 696 | 720 | **−24** |
| 1337 | 598 | 622 | **−24** |

Nguyên nhân: **không phải panel** (2 trang dùng chung 660px), mà do `.nb-wrap{margin:12px}`
(+ `margin:8px` ở ≤430px) tự thụt vào trong padding của panel, còn `.xl-page{margin:0 auto}`.
Vì vậy mọi card/bar của Nhục Thân luôn hẹp hơn Tu Luyện một dải mà mắt nhìn thấy được.

**Đã sửa (3 điểm, không đụng rule chung nào):**
1. `.nb-wrap{margin:12px}` → `margin:0`, và **xoá** bản `@media(max-width:430px){.nb-wrap{margin:8px}}`
   (bài học §6.1: mọi selector trùng phải quét hết file).
2. `.nb-stage` bỏ cap `max-width:420px` → `max-width:100%!important` — đúng brief gốc
   "ảnh thân hình phủ full khung"; cap 420 để lại hai dải đen 270px ở khổ 760px.
3. Breakpoint layout 2 cột `@media(min-width:861px)` → **`min-width:601px`**. Không có bước này thì
   ở 760px ảnh thành 688×486 một mình một cột: `wrap` **1135 → 1345** (phình thêm 210px).
   Có bước này: **1030** — vừa kín ngang vừa thấp hơn bản cũ.

**Đo lại sau khi sửa:**

| viewport | `.nb-wrap` | `.xl-page` | `wrap` cao (trước → sau) |
|---|---|---|---|
| 390 | **350** | 350 | 1107 → 1119 |
| 480 | 440 | — | — |
| 600 | 560 | — | — |
| 760 | **720** | 720 | 1135 → **1030** |
| 1337 | **622** | 622 | 767 → 803 (Tu Luyện 846) |

Bây giờ `panel=660 / content=622` **khớp tuyệt đối** với Tu Luyện ở desktop và 760; ảnh thân hình
380→408px ở desktop, card1 440→474. Trang cao thêm 36px ở desktop (803 so với 846 của Tu Luyện)
là đi đúng hướng "cho hết chiều dài" chứ không phải tác dụng phụ.

**Bonus — lệch 2–4px của §3.4 về 0.** Đo lại bằng `_nbb.html`: ô `.nb-tabbar` giờ là
`53×60` ở 390px (xl `53×62`) và **`96×64` ở 1337px — đúng từng pixel** so với ô `.xl-tabbar`,
thanh bar `622×80` = `622×80`. Bảng số cũ ở §3.4 (50/64/111/92) là của lúc `.nb-wrap` còn thụt.

**Việc đã loại trừ:** không nới `.tcg-main-panel` (vẫn 660px chung — xem §6.3, v13 từng bị bác vì
nới 660→800). Nếu chủ dự án muốn hộp **rộng hơn cả khung 660** thì có sẵn tiền lệ trong repo:
`mystyle.css:1751` `.tcg-main-panel:has(.ydinfo-page){width:min(760px,...)!important}` — làm theo
cho `.nb-wrap`, nhưng **phải hỏi trước**.

**Đã test:** `_nbw.html` 5 khổ (bảng trên), `php -l` sạch, `nb_sweep.php` **14/14 clean**, CSS đuôi
174 dòng cân ngoặc 0, 0 ký tự non-ASCII, `grep` không còn `margin:12px`/`420px` ở `.nb-*`,
web root sạch harness. Ảnh: `v8_after_vs_xl.png` (ghép 1:1 cạnh Tu Luyện), `v8b_nb_390.png`.

**CHƯA test:** chỉ Chrome headless — chưa xem 481–600px trên thiết bị thật, nơi ảnh giờ full-bleed
và trang dài hơn bản trước ~110px.


---

### 3.6 v9: "chiều dài" là CHIỀU DỌC — ảnh phủ trọn card, lò luyện thành 1 dải ngang

Chủ dự án bác cách hiểu của v8:

> "tôi đang nói chiều dài mài"

Tức là **chiều cao trang**, không phải bề rộng. Chụp cả hai màn đúng khổ trình duyệt thật
(1920×945, `_nbshot.html`) rồi nhìn cạnh nhau mới thấy chỗ anh ấy nói:

| | Tu Luyện | Nhục Thân (v8) |
|---|---|---|
| `wrap` | **846px** — chạm mép dưới màn hình | 803px — dừng sớm hơn |
| ảnh trung tâm | cao ~300px, là nhân vật chính | **287px**, bị nhốt trong cột 69% |

**Sửa:** ở `@media(min-width:601px)` bỏ layout "ảnh 69% + cột luyện 31%", đổi thành
**ảnh `width:100%` của card** (590×416 ở desktop — đúng brief gốc "ảnh phủ full khung") và
`.nb-forge` thành **1 dải ngang** `grid-template-columns:minmax(0,1fr) 190px`:
trái = tên bộ phận + dòng chỉ số + 2 chip chi phí, phải = nút Luyện Hóa `190px`.

**Số đo sau khi sửa** (`_nbw.html`): `wrap` 1337/1920 = **1051** (v8 803), `card1` 474 → **722**;
760 = 1306, 601 = 1216. Ảnh 590×416, 6 ngọc vẫn đúng giải phẫu (đã nhìn ảnh, không đoán theo %).
Dải luyện ở khổ hẹp nhất của breakpoint (601px) vẫn đúng 1 hàng: 2 chip `288px` lọt cột trái `321px`,
nút phải không bị đẩy xuống dòng.

**Đánh đổi nói thẳng:** 1051px **dài hơn** viewport 945px khoảng 106px → ở desktop phải cuộn một
chút mới tới `.nb-tabbar`. Đó là hệ quả trực tiếp của "cho hết chiều dài" + "ảnh phủ full khung";
muốn **không cuộn** thì chỉ cần một dòng cap `.nb-stage{max-width:...}` (ảnh ~434×306) — chưa tự làm
vì đi ngược đúng cái anh vừa đòi.

**Bẫy harness mới gặp (lặp lại được):** tài khoản E2E bị đá về màn Đăng nhập giữa hai lần chụp —
`game.php:477` kick khi `game1.endtime` cũ hơn 3600s, còn `nbseed2.php` seed `endtime = NOW() + 4h`
mà MySQL `NOW()` lệch PHP 1 giờ → thực tế chỉ còn 3 giờ. Cách mở khoá:
```
UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 6 HOUR) WHERE sid='e2e…e2';
```
(chạy bằng `Extensions/php/php5.6.9nts/php.exe`, `require` tuyệt đối `class/player.php` + `pdo.php`).
**Ảnh chụp ra màn Đăng nhập mà nhìn nhanh không để ý = đo sai hết.** Harness phải kiểm tra trang
còn là trang game trước khi tin số.

**Đã test:** `php -l` sạch · `nb_sweep.php` 14/14 clean · CSS đuôi 180 dòng cân ngoặc, 0 non-ASCII ·
`_nbw.html` 4 khổ · ảnh 1920×945 ghép cạnh Tu Luyện. Ảnh: `crop_v9_length_vs_xiulian.png`,
`shot_v9_desktop_1337.png`, `crop_v9_forge_601.png`.

**CHƯA test:** 1051px chưa bấm thử trên màn 1366×768 thật (laptop phổ thông) — ở khổ đó sẽ cuộn
nhiều hơn hẳn.


### 3.7 v10: "hơi bị dài 1 chút" — cắt chiều cao ở các dải KHÔNG phải ảnh

Chủ dự án xem hai ảnh 1920×1080 (đầu trang + cuối trang) rồi duyệt:

> "đẹp luôn rồi nhưng mà lại hơi bị dài 1 chút"

**Làm sai trước, sửa lại trong cùng lần này.** Bản đầu của v10 cắt ảnh:
`.nb-stage{max-width:84%}` → `wrap` 1051 → **949**, nhưng ảnh lọt thỏm giữa card, **hai lề nền tối
trái phải** — đúng cái anh đã bác ở bản nén ("nền ảnh phải full box"). Nhìn `shot_v10_desktop_1920.png`
bản đó là thấy ngay. Đã bỏ, ảnh về `max-width:100%`.

**Vì sao KHÔNG cắt dọc ảnh được (tính đủ, đừng thử lại):** muốn ngắn đi 70px thì box phải thành
663/392, `object-fit:cover` sẽ pha **74.8px ảnh**. Ngọc `Da` ở `top:21%` có **nhãn đặt PHÍA TRÊN
đỉnh ngọc** (`.nb-gem.slot-top .nb-gem-tag{bottom:100%}` — đảo ngược so với 5 ngọc còn lại), nên
phần pha ở đỉnh không được vượt **16.3px** (tính ở khổ mobile 350px: nhãn 18px + 3px hở + bán kính
ngọc 22px). Phần còn lại **58.5px dồn xuống đáy**, mà bóng đen của ảnh bắt đầu từ dòng 83 và **kéo
tới tận dòng 466** (đo bằng GD: `first dark row=83 last dark row=466`) → mất nguyên bệ sen. Không có
cửa sổ nào cắt vừa. Kết luận: ảnh 663×467 là **không co được**, chiều cao phải lấy chỗ khác.

**Bốn chỗ lấy 62px** (tất cả trong `@media(min-width:601px)`, mobile giữ nguyên 1119):

| Chỉnh | Trước → sau | Tiết kiệm |
|---|---|---|
| `.nb-forge` thành grid 2 hàng: hàng 1 = tên+dòng chỉ số `span 2`, hàng 2 = 2 chip **ngang 1 dòng** + nút | dải 109 → 78 | **−30** |
| `.nb-cost-item{white-space:nowrap;flex:0 1 auto;font-size:12.5px}` (chip "Linh Thạch 900,000/1,300" trước đây tự vỡ 2 dòng) | chip 56 → 30 | (đã tính ở trên) |
| `.nb-tier{padding:6→4}` + `.nb-tier-ico{font-size:18→16;line-height:1.15}` | ô bậc 71 → 61 | **−10** |
| `.nb-attr{padding:2px 0→0;line-height:20}` (6 dòng) + `.nb-req{margin:2→1px;line-height:19}` (5 dòng) | `card2` 219 → 200 | **−19** |
| `.nb-head{padding:10 14 8 → 8 14 6}` | | **−4** |

**Không đụng `.nb-tabbar`** — phải giữ 80px để bằng chim `.xl-tabbar` của Tu Luyện (yêu cầu từ v7).

**Số đo cuối** (`_nbw.html`, `zoom=1`):

| khổ | v9 | v10 (84% — đã bác) | **v10 chốt** | Tu Luyện |
|---|---|---|---|---|
| 390 | 1119 | 1119 | **1119** (không đổi) | 1036 |
| 601 | 1216 | 1113 | **1111** | 994 |
| 760 | 1306 | 1185 | **1223** | 994 |
| 1337 / 1920 | 1051 | 949 | **957** | 846 |

`card1` 722 → **663**, `card2` 219 → **200**, `bar` 622×80 (bằng Tu Luyện từng px).
760 tăng so với bản 84% là **đúng**: bỏ cap thì ảnh lại rộng hết cột ở khổ đó.

**Bẫy specificity mới (may chưa đo):** `.nb-capnote` là con trực tiếp của `.nb-forge`, nên khi
`.nb-forge` đổi sang grid phải cho nó `grid-column:1/span 2;grid-row:3` — rule cũ
`.nb-capnote{flex:1 1 100%}` là **thuộc tính flex, vô tác dụng trong grid**.

**Đã test:** `php -l` sạch · `nb_sweep.php` **14/14 clean** · đuôi CSS cân ngoặc, 0 non-ASCII ·
`_nbw.html` 4 khổ · nhìn ảnh 1337 + 601 + 1920 ghép cạnh Tu Luyện (6 ngọc vẫn đúng Da/Thịt/Gân cốt/
Tủy/Thần/Máu, chip không vỡ dòng, không tràn ngang: `scrollW == vw` cả 4 khổ).
Ảnh: `crop_v10_trim_vs_xiulian.png`, `shot_v10_desktop_1920.png`, `shot_v10_desktop_1337.png`,
`shot_v10_desktop_601.png`, `shot_v10_mobile_390.png`.

**CHƯA test:** trạng thái `nb-capnote` (bộ phận chạm trần) ở khổ 601 — chưa seed được cấp 20 để
chụp; và 957px vẫn **dài hơn** viewport 945px ~12px nên desktop vẫn phải cuộn một chút tới cái bar.
Nút chỉnh duy nhất nếu anh muốn ngắn nữa: các dòng trong bảng trên, **không phải** `.nb-stage`.

> ⚠️ **Kết luận "ảnh không cắt dọc được" chỉ ĐÚNG ở mobile.** Toàn bộ phép tính ở trên lấy số ở khổ
> 350px, nơi ngọc cố định 46px chiếm tới 87px ảnh. Ở desktop tỉ lệ đó lật ngược, và chỉ cần **đảo
> nhãn ngọc Thần lên trên đĩa** là cắt được. Xem **§3.8** — bản v11 cắt dọc ảnh thật, −101px.

### 3.8 v11: "vẫn bị dài 1 chút" — CẮT DỌC ẢNH ở desktop (−109px), mobile giữ nguyên

Ảnh chứng minh (2 ảnh 1920 của tài khoản thật `tttttt`, ID 548, Phàm Cốt · trần 10, 6 bộ phận cấp 0):
trang vẫn phải cuộn mới tới cái bar.

**Bước 1 — cắt chữ nghĩa: chỉ ăn 8px.** `.nb-reqs` (danh sách điều kiện đột phá) thành grid 2 cột +
nền các dòng 19/18px → `card2` 192 → 173, nhưng `wrap` desktop chỉ 957 → **949**. Lý do đo bằng
`_nbd.html`: `.nb-cols` là **grid 2 cột**, rút ngắn cột PHAI trong khi cột TRÁI (`nb-h3` 22 +
`nb-attrs` 60 + `nb-ms` 75) mới là cột **binding** — hai cột bằng nhau thì cắt một cột = không cắt gì.
**Bài học: trước khi cắt trong grid 2 cột, đo cả hai cột.**

**Bước 2 — thử thu nhỏ ảnh + nền phủ hai lề: BÁC.** `.nb-stage{max-width:520px}` + một `<img>`
cùng file `object-fit:cover` `blur(15px) brightness(.62)` phủ kín bề rộng card để "không còn hai lề
đen". Số đo đẹp (850px) nhưng nhìn ảnh `crop_v11_rejected_backdrop_1920.png` thì ra **hai dải xám
đục hai bên bức hình sắc** — về bản chất vẫn là lề nền tối đã bị bác ở v10, chỉ khác màu. Đã bỏ toàn
bộ (không để lại markup). *Kèm bug tự phạm: quên đóng `<div>` của band → `.nb-forge` chui vào trong
band và bị ảnh nền phủ mất, dải Luyện Hóa biến khỏi trang. Ảnh chụp mới phát hiện ra, số đo không.*

**Bước 3 — đường thật: cắt 45px đỉnh + 45px đáy của ảnh, CHỈ ở desktop.**
`.nb-stage{aspect-ratio:663/377}` (base vẫn `663/467` cho mobile). Với `object-fit:cover` +
`object-position:50% 50%`, box 663/377 pha đúng **90px ảnh, chia đều mỗi đầu 45px** — không cần crop
JPEG, không đụng file gốc, thân hình vẫn **full chiều ngang card**.

Vì sao desktop làm được còn mobile không:

| | mobile 350px | desktop 561px (601) | desktop 622px |
|---|---|---|---|
| scale `s = W/663` | 0.53 | 0.85 | 0.94 |
| 46px box (nhãn+ngọc) tính ra ảnh | **87px** | 54px | 49px |
| trần cắt đỉnh (ngọc Da ở dòng 98) | 11px → chết | 44px | 49px |
| trần cắt đáy | — | 68px* | 68px* |

\* Trần đáy nhảy từ ~46px lên **68px** là nhờ **đảo nhãn ngọc Thần lên trên đĩa ngọc**
(`.nb-gem.slot-bottom .nb-gem-tag{bottom:100%}` ở `@media(min-width:601px)`), nên phía dưới chỉ còn
bán kính 23px. Nhãn này không đụng nhãn Tủy/Máu vì ba ngọc ở `left:50%/25%/75%`.

**6 toạ độ ngọc phải tính lại theo box mới** — công thức `% = (dòng ảnh − 45) / 377`:

| Ngọc | dòng ảnh | % cũ (467) | % mới (377) | ghi chú |
|---|---|---|---|---|
| Da | 98 | 21 | **15** | 14.1 là đúng tâm; làm tròn lên 15 để nhãn không bị `overflow:hidden` cắt ở khổ 601 (lệch 3px) |
| Thịt · Gân cốt | 191 | 41 | **38.9** | |
| Tủy · Máu | 322 | 69 | **73.5** | |
| Thần | 374 | 80 | **87.2** | nhãn lật lên trên |

**Ăn gì của bức ảnh:** 45px đỉnh = chóp vòng pháp trận (vòng này đã tràn khung hai bên sẵn nên không
ai nhận ra bị cắt); 45px đáy = nền tối phía dưới mép trước của bệ sen — **bệ vẫn còn**, thân không
lơ lửng. Đã nhìn cả ba khổ ảnh.

**Bước 4 — sửa dòng vỡ:** ở trạng thái đủ 6 điều kiện (seed `stage=4`), ô "[Thiếu] Đế Lệnh: 0 / 8"
vỡ làm hai dòng trong cột 135px → `.nb-req{font-size:12.5px}` ở `@media(min-width:601px)`, về một
dòng, không đổi chiều cao. Bằng chứng: `crop_v11_card2_stage4.png`.

**Số đo cuối** (`_nbw.html`):

| khổ | v10 | **v11** | Tu Luyện |
|---|---|---|---|
| 390 | 1119 | **1119** (không đổi — ảnh mobile vẫn 663:467) | 1036 |
| 601 | 1111 | **956** | 994 |
| 760 | 1223 | **1047** | 994 |
| 1337 / 1920 | 957 | **840** | **846** |

Desktop giờ **ngắn hơn cả Tu Luyện 6px** — hai màn bằng nhau, và ở cửa sổ 1080px toàn bộ trang
(gồm cả bar + đồng hồ cuối trang) nằm gọn không phải cuộn. `card1` 663 → **573**, `card2` 200 → **173**.

**Đã test:** `php -l` sạch · `nb_sweep.php` **14/14 clean** · khối `.nb-*` cân ngoặc, 0 non-ASCII ·
`mystyle.css?v=2026100927` đủ 3 file · `_nbw.html` 5 khổ, `scrollW == vw` (không tràn ngang) ·
ảnh 1920 (nhân vật trắng) + 601 + 390 + trạng thái `stage=4` đầy số liệu · ghép 1:1 cạnh Tu Luyện
(`crop_v11_vs_xiulian.png`) và **nhìn**: 6 ngọc vẫn đúng Da/Thịt/Gân cốt/Tủy/Thần/Máu, nhãn Da và
nhãn Thần không dính mép khung.

**CHƯA test:** chưa có điện thoại thật để chạm kiểm tra 6 ngọc trên ảnh đã cắt (desktop + tablet
thì ảnh chụp chứng minh đúng vị trí); khổ **601–700px** nhãn ngọc Da chỉ còn 2px hở trên đỉnh — nếu
sau này đổi cỡ nhãn/đĩa ngọc phải đo lại; `nb-capnote` ở ≥601 vẫn chưa chụp được.


---

## 4. Logic server-side (`class/function/thechat.php`)
### 4.1 Chuỗi chỉ số

```
thechat_body_bonuses($sid, $dblj)      → levels / total_lv / cap / stage / stats / milestone
        ↓
thechat_calc_bonuses()                 → hợp nhất Nhục Thân + thể chất + 'awaken_stage'
        ↓
class/player.php  getplayer()          → umaxhp / ugj / ufy / ubj / uxx / thechat_passive_exp
        ↓
game/pve.php      ubj >= mt_rand(1,200)  (bạo kích)
                  uxx/200                (hút máu)
game/xiulian.php  thechat_passive_exp → min(1.5, 1 + tc/100)
```

### 4.2 Công thức đang dùng (nguồn: `thechat_body_config.json`)

**Luyện hóa** (`thechat_body_refine_cost($lv)` — lv là cấp **hiện tại** của bộ phận):
```
Tinh Thể Đá = material_base + floor(lv / material_per_lv) = 1 + floor(lv/5)
Linh Thạch  = linh_thach_base + lv * linh_thach_per_lv   = 100 + lv*100
```
→ lv0 = 1 đá + 100 LT · lv1 = 1 + 200 · lv2 = 1 + 300 · **lv5 = 2 + 600** · lv10 = 3 + 1100.

**Trần cấp** theo bậc Cốt Cách: `Phàm 10 · Kim 20 · Ngọc 30 · Linh 40 · Kiếp 50 · Tiên 60`
(áp cho **cả 6** bộ phận, không tính riêng từng cái).

**Đột phá** (`thechat_body_break`) — cần **đồng thời** 4 điều kiện của bậc kế:
```
Kim   : tổng 30 · ulv 150 · Tinh Thể Đá ×20 · 20 000 LT
Ngọc  : tổng 80 · ulv 200 · Thánh Thạch ×5   · 100 000 LT
Linh  : tổng 150· ulv 280 · Tiên Lộ ×5      · 500 000 LT
Kiếp  : tổng 210· ulv 360 · Thần Thạch ×6   · 2 000 000 LT
Tiên  : tổng 270· ulv 450 · Đế Lệnh ×8      · 8 000 000 LT
```
Đột phá **không tốn cấp bộ phận** — chỉ nâng trần. Cột ghi `game1.thechat_awaken_stage`.

**Mốc tổng cấp** (`thechat_body_milestone`) — % cộng vào **toàn bộ** chỉ số Nhục Thân:
`20 → Thiết Thân +5% · 60 → Cương Thân +12% · 120 → Kim Cương Thân +22% · 200 → Bất Hoại Thân +35% · 300 → Luân Hồi Thân +50%`

**6 bộ phận → 6 chỉ số** (`per_lv` là mỗi cấp):

| # | Bộ phận | Chỉ số | /cấp | Ô |
|---|---|---|---|---|
| 1 | Bì Phủ (Da) | Phòng Thủ | +4 | top |
| 2 | Cơ Nhục (Thịt) | Khí Huyết | +20 | left-up |
| 3 | Cân Cốt (Gân cốt) | Công Kích | +3 | right-up |
| 4 | Cốt Tủy (Tủy) | Bạo Kích | +0.2 | left-down |
| 5 | Huyết Khí (Máu) | Hút Máu | +0.5 | right-down |
| 6 | Thần Thức (Thần) | Tu Luyện | +0.4 | bottom |

### 4.3 Cache phải invalidatable

`thechat_body_levels()` và `thechat_body_bonuses()` **không** dùng `static` (static không xoá được
trong cùng request) mà dùng `$GLOBALS[...]`. Sau khi luyện hóa/đột phá thành công,
`thechat_body_levels_flush()` (dòng 939) được gọi để lần đọc kế tiếp trong cùng request thấy số mới —
đây là lý do trang vừa POST xong đã hiện cấp mới mà không cần redirect.

---

## 5. Ảnh đã sinh

`images/thechat/`:
- `nhuc-than.jpg` 663×467 — bóng ngồi thiền, nền xanh lạnh (lấy màu theo nền trang, theo memory *ảnh nền phải theo màu trang*).
- 18 ngọc = 6 stat × 3 trạng thái. **Tint theo chỉ số** (không dùng 3 file chung):
  `def #4dabf7 · hp #ff6b6b · atk #ffa94d · crit #ffd43b · lifesteal #da77f2 · exp #63e6be`.
  - `khoa` = blend 72% về xám(116,124,140) + brightness .72
  - `sang` = gốc
  - `day` = brightness 1.3 + quầng ấm
  Chọn file theo cấp bộ phận: `lv == 0 → khoa`, `lv < cap → sang`, `lv >= cap → day`.

> BẪY ĐÃ GẶP: bản đầu `ngoc-sang.png` và `ngoc-day.png` **trùng md5** (`017f32d9…`) vì script tint không
> đổi gì. Phải kiểm md5 từng cặp sau khi sinh ảnh.

---

## 6. Ràng buộc CSS phải giữ (MỒI — dễ tái phạm)

1. **Khối `.nb-*` nằm ở dòng 6977→7259, SAU toàn bộ `@media`** của `mystyle.css`. Rule cùng specificity
   đặt trước sẽ bị đè (memory *CSS phải sửa hết selector trùng*).
2. **`<a>` toàn trang có padding/margin mặc định.** `css/gamecss.css:85` định nghĩa
   `a{padding:1px 10px;margin-right:3px;border-radius:3px}`, và `@media(max-width:480px)` dòng 226 là
   `a,.btn-login,#btn{padding:5px 4px;margin:5px 2px;display:inline-block}`. Vì `.nb-tier`/`.nb-gem`
   đều là `<a>` **không khai báo padding/margin**, ngọc bị thụt vào trong và **lệch +2% so với điểm đã
  declare**, đĩa đo được 26px/36px thay vì 46/44. Sửa: `.nb-gem{padding:0;margin:0;border:0;border-radius:0}`,
   và mọi `a.nb-*` state phải có `background:none` — kể cả `:focus`, vì `gamecss.css:95`
   `a:hover,a:active,a:focus{color:#FFF;background:#0093e8}` sẽ sơn xanh lên tab/ngọc khi chạm.
   Tương tự: `.nb-tier`/`.nb-btn` phải `margin:0`, và mỗi nền nút phải khai báo **riêng**
   `:hover/:active/:focus` (plain / `is-done` / `is-cur` / `.nb-btn-refine` / `.nb-btn-break`
   / `.nb-tabbar a` + `.nb-tabbar a.on`) để màu xanh toàn cục không đè.
3. **`.tcg-main-panel` can thiệp bằng `!important`.** `css/mystyle.css:5035-5040`
   `@media(max-width:760px){.tcg-main-panel *{max-width:100%!important;box-sizing:border-box!important}}`
   và `.tcg-main-panel img{max-width:100%!important;height:auto!important}`. Hệ quả: `max-width:420px`
   trên `.nb-stage` **bị nuốt** (tablet 600px ra `534×376`), và ảnh bị co chiều cao → phải viết
   `max-width:420px!important` / `380px!important` (desktop) và `height:100%!important` trên `.nb-figure`.
   **Không được bỏ `!important`** — đo lại `stage` phải ra tỉ lệ 1.420.
4. **Thứ tự rule trong `@media(max-width:430px)`.** `.nb-card{padding:12px}` đặt **sau**
   `.nb-card-body{padding:0}` làm card 1 bị đệm 12px → `stage` 308px thay vì 332px, ngọc dồn giữa.
   Muốn reset padding cho biến thể, phải đặt biến thể **sau** rule chung.
5. **Tab rail là `<a>`**, nên màu phải viết `a.nb-tier.is-done:link, a.nb-tier.is-done:visited`
   — selector một lớp `.nb-tier.is-done` bị `a:link` của trang (0,1,1) đè mất. Viết sai thành
   `.nb-tier.is-done a.nb-tier:link` là **không khớp phần tử nào** (đã phạm và đã sửa).
6. **Rail desktop:** `.nb-tier{flex:1 1 0;min-width:0;box-sizing:border-box}`. Để `width:86px` cứng
   thì tab thứ 6 **bị cắt ngay trên desktop** (đo được `rail=564 < 588`). Từ bản có chuỗi liên kết
   (§3.3), chế độ quét ngang `flex:0 0 auto;width:76px` hạ từ ≤430px xuống **≤560px** — 5 connector
   chiếm thêm 50–70px, nếu không hạ breakpoint thì tên bậc **rơi xuống 2 dòng** ở 480–560px.
7. **Không nới khung chung.** Panel vẫn `min(660px, …)` — chủ dự án đã bác v13 của trang Tu Luyện vì
   nới 660→800px (memory *không nới khung chung*). Mọi overflow phải giải trong phần tử đó.
8. **Emoji chỉ dùng cung astral** (💗🗡️💥🌀📦). BMP (❤️️✨✔️) render lệch ô flex nhỏ.
9. **Đĩa ngọc `.nb-gem{width:46px;height:46px}`** (≤430px: 44px) **nhỏ hơn nhãn** — `.nb-gem-tag`
   (`white-space:nowrap`, "Gân cốt 12") tràn ra hai bên đĩa; đó là **chủ ý**. Ràng buộc thật là tag
   **không được tràn ra ngoài `.nb-stage`**: harness in `!TAGl/r/t/b` nếu tag lọt khung.
   `slot-top` vì thế mới đảo tag lên trên (`bottom:100%`).
10. Ảnh **full-box**: `object-fit:cover` + `overflow:hidden` trên `.nb-stage`, không để dải nền thừa
    quanh thân hình (đây là yêu cầu trực tiếp của chủ dự án ngày 09/10/2026).
11. **Chuỗi Cốt Cách:** `.nb-rail{gap:0}` và **mỗi cặp tab phải có đúng 1 `.nb-link`** — vòng lặp rail
    sinh link khi `$nbT > 0`, đừng đổi thành `gap` (sẽ mất mũi tên) và đừng biến `.nb-link` thành `<a>`
    (§3.3). Sau khi sửa rail, đo lại `nameH` của tab phải = **17** (1 dòng) ở cả 390/480/600/1337.
12. **Bar `.nb-tabbar`:** mọi state của `<a>` phải nằm trong **một** selector nhóm có `.nb-tabbar`
    đứng đầu (`.nb-tabbar a, .nb-tabbar a:link, … a:focus`) — thiếu `:link/:visited` là `a:link`
    toàn trang (0,1,1) ăn màu; thiếu `background` ở `:hover` là `gamecss.css:95` sơn `#0093e8`.
    Ô emoji `<i>` phải có `font-family:"Segoe UI Emoji",…` + `font-style:normal` + `line-height`
    khớp `height` + `overflow:hidden`, nếu không glyph astral kéo giãn hàng và bar lồi 2px.
    **Bẫy riêng của bar này:** bề rộng ô là `min-width:calc((100% - 30px)/6)` (≤760px đổi thành
    `- 20px` vì `gap` giảm còn 4px) — hai con số **phải đi cặp** với `gap`, sửa một trong hai là ô
    lệch khỏi Tu Luyện. Nhãn bắt buộc `white-space:nowrap` ở ≤430px (copy từ `.xl-tabbar` dòng 876),
    thiếu nó là "Nhân Vật" vỡ 3 dòng và bar lồi 24px.
13. **ẢNH NHỤC THÂN: cắt dọc được ở DESKTOP, KHÔNG cắt được ở MOBILE** (v10→v11). Đĩa ngọc
    `.nb-gem` cố định 46px nên ràng buộc nhãn-đính-box tính ra **số px ảnh** đổi theo bề rộng:
    `px ảnh = 46 × 663/W`. Ở 350px (mobile) một nhãn ngọc ngốn tới **87px ảnh** → đỉnh chỉ pha được
    11px → bất khả thi, nên base vẫn `aspect-ratio:663/467`. Ở ≥601px (W ≥ 561) chỉ ngốn 54px → cắt
    được 45 đỉnh + 45 đáy, thành `aspect-ratio:663/377` **và 6 toạ độ ngọc phải đổi theo**
    (`% = (dòng ảnh − 45)/377`) — đổi box mà quên đổi `%` là ngọc lệch giải phẫu ngay.
    Đã thử và **bác** hai đường: (a) `max-width:520px` + ảnh nền blur phủ hai lề → ra hai dải xám đục
    hai bên, về bản chất vẫn là "lề nền tối" đã bị bác (`crop_v11_rejected_backdrop_1920.png`);
    (b) crop file JPEG → mất file gốc, được mỗi tỉ lệ mới. Số học đầy đủ: §3.7 + §3.8.
14. **`.nb-forge` là grid từ v10** → mọi con trực tiếp của nó phải khai báo `grid-column`/`grid-row`
    rõ ràng. `.nb-capnote{flex:1 1 100%}` cũ **vô tác dụng trong grid** (đó là thuộc tính flex), phải
    thành `grid-column:1/span 2;grid-row:3`. Thêm dải mới vào `.nb-forge` mà quên khai báo ô thì
    auto-placement sẽ đẩy nút Luyện Hóa sang cột khác.
15. **Trước khi cắt chiều cao trong grid 2 cột, đo cả HAI cột** (v11). `.nb-cols` ngắn bằng cột dài
    nhất; bản v11 rút danh sách điều kiện ở cột phải 19px mà `wrap` chỉ giảm 8px vì cột trái mới là
    cột binding. Dùng `_nbd.html` (in chiều cao từng khối + con của nó) rồi hãy sửa.
16. **Khi "clone cho giống" một khối CSS có sẵn: `grep -n "<class>" css/mystyle.css` rồi đọc hết mọi
    dòng khớp, kể cả nằm trong `@media`.** Khối `.xl-tabbar` có 7 dòng chính + 4 dòng override
    ≤760px + 1 dòng ≤430px; chỉ đọc 7 dòng đầu là bản v6 ra đời với nhãn vỡ dòng và ô sai cỡ.

---

## 7. Bug đã tìm ra và cách sửa

### 7.1 Cốt Cách vô hình với nhân vật chưa có Thể Chất  *(đã sửa)*

`thechat_info()` trả `false` khi `thechat_id = 0`, nên `thechat_calc_bonuses()` rơi vào nhánh `$empty`
và gán cứng `'awaken_stage' => 0`. Kết quả: sau khi đột phá, tên bậc trong card bộ phận **vẫn đọc
"Phàm Cốt"**, và rail luôn sáng bậc 0. Với chính sách "Khởi đầu nhân vật trắng" thì đây là **mọi**
nhân vật mới.

Sửa: `thechat_body_bonuses()` giờ trả `'stage'` (dòng 834) và nhánh `$empty` lấy `$body['stage']`
(dòng 283); trang dùng `$nbStage = intval($nbBody['stage'])` thay vì ăn qua `$player->thechat_awaken_stage`.

### 7.2 URL encode bị cắt một ký tự  *(bẫy harness, không phải bug game)*

Nhiều ảnh chụp "ra trang trắng / giống hệt nhau" không phải cache: chuỗi `cmd=` tự sinh thủ công bị
thiếu 1 ký tự cuối → `decode()` ra rác → `$cmd` không set → `game.php` trả `refresh:1;url=index.php`
với body 3 byte. **Luôn** verify round-trip `decode(encode($q)) === $q` trong PHP, không tự gõ chuỗi.

---

## 8. Bug CŨ có sẵn — phát hiện nhưng **CHƯA sửa** (chủ dự án quyết)

1. **`class/function/huyetmach.php:543-546`** — `huyetmach_activate_transform()` dùng `$player->uxx`
   ở chỗ phải là `uhp`, và `UPDATE game1 SET uxx = ?`. Nghĩa là khi **biến thân** nó ghi đè **chỉ số
   Hút Máu** bằng HP, và không trừ HP như mô tả. Liên quan trực tiếp tới Nhục Thân vì `uxx` giờ là
   kênh hút máu có cộng từ bộ phận Huyết Khí.
2. **`thechat_realm_scale()`** đọc `$GLOBALS['player']->jingjieValue`, nhưng biến đó chưa set ở lần gọi
   đầu tiên **bên trong** `getplayer()` → hệ số cảnh giới có thể tính bằng nền.
3. (nhỏ) **`game.php:33`** `$Dcmd = $_SERVER['QUERY_STRING'];` — sinh Notice `Undefined index` khi request
   vào `game.php` **không kèm query string** (thấy trong `php_error.log`). Chỉ là Notice, không đổi hành vi.

Cả ba nằm ngoài phạm vi "dựng lại Thể Chất" nên để nguyên; sửa 1 và 2 là đổi hành vi chiến đấu/Huyết Mạch.

---

## 9. Quy trình kiểm chứng (lặp lại được)

Helper nằm ở `E:/window/Temp/` (**ngoài repo**, không commit):

| File | Tác dụng |
|---|---|
| `nbseed2.php` | Seed E2E về **trạng thái hero**: node 12/9/14/6/8/11, stage 1, 500 Tinh Thể Đá, 6 Thánh Thạch, 900 000 LT, ulv 1000 |
| `nbempty.php` | Seed E2E về **trạng thái trắng**: 6 node = 0, stage 0 |
| `nbstage.php <0..5>` | **Chỉ đổi `thechat_awaken_stage`** của tài khoản E2E → chụp rail ở mọi bậc (0 = không link nào sáng, 5 = sáng hết). Chạy xong **nhớ** `nbseed2.php` để trả về hero |
| `nbseed.php <mode>` | `demo` / `empty` / `rich` (cũ) |
| `nbenc3.php` | Sinh + **verify round-trip** chuỗi `cmd=` cho hero và tài khoản chủ dự án |
| `nb_post.php` | **POST thật qua route**: refine 2 node, break khi thiếu cấp, `nb_action` lạ |
| `nb_break.php` | Seed tổng 84 → **POST break thành công** → kiểm stage/Linh Thạch/Thánh Thạch/log/refine sau đột phá |
| `tc_body_smoke.php` | Smoke test logic: chi phí lv0/1/2, chặn trần, đột phá, mốc % |
| `_nb_shell.html` | Harness iframe 390px **để chụp ảnh** (copy ra web root thành `_nb.html`, **xoá ngay sau khi chụp**) |
| `_nbr.html` | Harness **đo riêng rail Cốt Cách**: `rail clientWidth/scrollWidth` + từng tab (`w/h/nameH`, cờ `!WRAP` `!NARROW`) + từng `.nb-link`. Dùng khi sửa §3.3. |
| `_nbt.html` | Harness **đo riêng `.nb-tabbar`** (bản v6, grid 1 cột): `wrapH` + `bar` (rộng × cao, số cột grid, `max-width`, `margin`) + từng `<a>` (ô, `line-height`, glyph đo bằng `Range` + cờ `!OFF`/`!CLIP`, nhãn, màu, bo góc) + `legacy=` để chắc `.nb-foot`/`.nb-btn-ghost` đã sạch. |
| `_nbb.html` | **Harness chuẩn cho bar từ v7** — đo được **cả hai** bar: `.xl-tabbar` (màn Tu Luyện) hoặc `.nb-tabbar`. In `bar` (rộng×cao, `pad`, `gap`, `margin`) + từng ô `<a>` kèm `lines=` (số dòng nhãn, đo bằng `Range`/`line-height`) và cờ `!OVER`. Mở `?w=<ngang>&u=<url>` → **chạy đủ 390/480/760/1337 cho CẢ HAI trang** rồi đối chiếu số, đừng chỉ đo trang mình. |
| `_nbp.html` | Trả `barTop` / `barH` / `docH` để **crop ảnh chụp đúng chỗ** (tự reset `zoom=1`). |
| `_nbw.html` | **Harness so khung liên-trang (v8)** — mở `?w=<ngang>&u=<url>` trên **cả** `thechat` lẫn `xiulian`, in `panel=WxH`, `pad`, `root=.nb-wrap/.xl-page WxH`, từng `cardN`, `bar`, `docH`, `vw/scrollW`. Dùng khi câu hỏi là "hai trang có cùng bề rộng không" — `_nbm.html` chỉ đo trong một trang nên không phát hiện ra `.nb-wrap` thụt 24px. |
| `_nbshot.html` | Harness **chụp ảnh** theo tham số: `?w=<ngang>&h=<cao>&u=<url>` → iframe đúng cỡ, không tự cân giữa (crop từ `x=0`), **tự reset `zoom=1`** sau 900ms. Copy ra web root, chụp xong **xoá ngay**. Bẫy: thiếu reset zoom thì desktop vẽ ở 0.75 → crop theo số đo thật sẽ ra nền trống (đã mất 2 vòng vì điều này). |
| `nb_sweep.php` | **Quét 14 trạng thái URL** (node 1-6, view 0-5, trống, rác) × verify `decode(encode())`: đếm byte, có `.nb-stage`, số tab / số `.nb-link`, và `substr_count` Warning/Notice/Fatal/Undefined/Deprecated trong HTML |
| `_nbm.html` | Harness **đo số**: `?w=<viewport>&u=<url game.php>` → `<title>` chứa `wrap / card1 / card2 / stage (ratio) / img (crop) / từng ngọc (kích thước + % tâm + cờ !BOX/!TAG)`. Copy ra web root, chạy `--dump-dom`, **xoá ngay**. |
| `_nbd.html` | Harness **đo fat theo khối** (dùng cho v10 + v11) — `?w=&sel=.nb-head,.nb-forge,.nb-cost-item&u=` in tối đa 3 lớp con, mỗi khối kèm `padding/margin/line-height/font-size`. Đây là công cụ trả lời "70px nằm ở đâu", thay vì đoán. **Chính nó bắt được bẫy v11**: `.nb-cols` là grid 2 cột nên rút ngắn cột phải 19px mà `wrap` chỉ giảm 8px — cột trái mới là cột binding. `_nbf.html` là bản 2 lớp tương tự, có thêm `ov=<CSS>` để thử override tại chỗ mà không sửa file. |

```bash
# Server: Apache phpStudy KHÔNG bật → dùng built-in PHP 5.6
"D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" \
  -S 127.0.0.1:8199 -t D:/XXJserver/phpstudy_pro/WWW

# Desktop
chrome --headless=new --disable-gpu --hide-scrollbars --window-size=1337,1500 \
  --virtual-time-budget=20000 --screenshot=<abs path> \
  "http://127.0.0.1:8199/game.php?cmd=$(cat cmd_hero.txt)"

# Mobile THẬT (sàn --window-size ~500px → phải nhét iframe 390px)
chrome --headless=new --window-size=500,1400 ... "http://127.0.0.1:8199/_nb.html"

# ĐO 4 viewport (kết quả nằm trong <title>) — xem số ở §3.2
CMD=$(cat cmd_hero.txt)   # chuỗi encode, KHÔNG có ký tự & → nhét thẳng vào u= được
for W in 390 480 600 1337; do
  chrome --headless=new --window-size=1400,1600 --virtual-time-budget=15000 --dump-dom \
    "http://127.0.0.1:8199/_nbm.html?w=$W&u=http://127.0.0.1:8199/game.php?cmd=$CMD" \
    | grep -o '<title>[^<]*</title>'
done
```

> **BẪY HARNESS (đã mất 1 vòng debug):** phải **copy `_nbm.html` vào web root** rồi mở qua
> `http://127.0.0.1:8199/`. Mở bằng `file:///…` thì iframe `http://127.0.0.1:8199` là **cross-origin**,
> `contentDocument` = `null` → `ERR Cannot read properties of null`. Trang game có `zoom:.75` →
> harness phải đặt `body.style.zoom=1` trước khi đo (memory *chrome-headless-screenshot*).

### 9.1 Bằng chứng đã lưu

`shot_nb_v1_*` = **trước khi nén** (6 card) — giữ lại để so. `shot_v4_*` = **bản hiện tại** (2 card).

| File | Nội dung |
|---|---|
| `docs/img/thechat/shot_nb_v1_desktop.png` | Hero, desktop 1337px |
| `docs/img/thechat/crop_nb_v1_desktop.png` | Crop hero desktop (đọc từng chữ) |
| `docs/img/thechat/shot_nb_v1_desktop_trong.png` | **Trắng** (6 node Lv.0, Phàm Cốt), desktop |
| `docs/img/thechat/shot_nb_v1_mobile.png` | Hero, iframe 390px |
| `docs/img/thechat/shot_nb_v1_mobile_trong.png` | Trắng, iframe 390px |
| `docs/img/thechat/shot_nb_v1_mobile_cot2.png` | **Sau đột phá thật** → Ngọc Cốt: 2 tab "Đã đạt" (xanh), tab hiện tại viền cyan, `Trần mỗi bộ phận: 30 cấp`, `+67 Phòng Thủ` = 15×4×1.12 → **mốc Cương Thân +12% chạy đúng trên UI** |
| `docs/img/thechat/shot_v4_mobile_390.png` | **Bản nén (2 card)**, hero, 390px — `wrap=1083`, 6 ngọc nằm trên người, `stage` 332×234 |
| `docs/img/thechat/shot_v4_mobile_trong.png` | Bản nén, **tài khoản trắng** (`thechat_id=0`, 6 node Lv.0), 390px — trang vẫn đủ 2 card + 6 ngọc `khoa`, nút Đột Phá disabled |
| `docs/img/thechat/shot_v4_tablet_600.png` | Bản nén, 600px — `max-width:420px!important` có tác dụng, `stage` 420×296 (trước khi vá `!important` là 534×376 = vỡ tỉ lệ) |
| `docs/img/thechat/shot_v4_desktop_1337.png` | Bản nén, desktop — ảnh thân hình + lò luyện **2 cột**, `wrap=739` (trước 957) |
| `docs/img/thechat/shot_v5_desktop_1337.png` | Rail 6 bậc **linked bằng 5 mũi tên**, link đầu sáng (xanh→vàng) |
| `docs/img/thechat/shot_v5_mobile_390.png` | Rail 390px quét ngang, `wrap` vẫn 1083 |
| `docs/img/thechat/crop_v5_rail_3stage.png` | **Chuỗi ở 3 bậc**: stage 5 (5/5 sáng) / stage 0 (0/5) / stage 3 (3/5) — chứng minh logic `is-lit` |
| `docs/img/thechat/crop_v5_rail_mobile.png` | Crop rail 390px: tab 76px 1 dòng, tab thứ 4 ló ra = mồi quét |
| `docs/img/thechat/shot_v6_mobile_390.png` | **Bản v6 (đã bác)**, hero 390px — bar `max-width:220px` bị `!important` của panel nuốt nên giãn full hàng, pill "Nhân Vật" **dài gấp 6 lần** ô Tu Luyện |
| `docs/img/thechat/shot_v6_desktop_1337.png` | Bản v6, desktop — bar chỉ 220px, **không** dài như bar gốc → chủ dự án yêu cầu sửa (§3.4) |
| `docs/img/thechat/crop_v6_bar_1btn_mobile.png` | Crop bar v6 1 nút ở 390px |
| `docs/img/thechat/crop_v6_bar_2btn_mobile.png` | Crop bar v6 2 nút ở 390px (`thechat_id=1` seed tạm) |
| `docs/img/thechat/crop_v6_bar_2btn_desktop.png` | Crop bar v6 2 nút desktop |
| `docs/img/thechat/shot_v7_mobile_390.png` | **Hiện tại**, hero 390px — bar full ngang, pill "Nhân Vật" đúng 1 ô Tu Luyện (`50×60`) |
| `docs/img/thechat/shot_v7_desktop_1337.png` | Hiện tại, desktop — bar `598×80`, pill `92×64` (ô Tu Luyện: `96×64`) |
| `docs/img/thechat/crop_v7_bar_vs_xiulian.png` | **Bằng chứng đối chiếu 1:1**: bar Nhục Thân chụp ngay trên bar Tu Luyện, khổ 390 (dưới) và 1337 (trên) — cùng shell, cùng cỡ ô |
| `docs/img/thechat/crop_v7_bar_2btn_mobile.png` | Trạng thái 2 nút ở 390px: "Nâng Phẩm Chất" tự nong `84×60` (không vỡ dòng), "Nhân Vật" vẫn `50×60` |
| `docs/img/thechat/crop_v8_content_vs_xiulian.png` | v8, ghép 1:1 cạnh Tu Luyện ở 1337px: hai hộp cùng `panel 660 / content 622`, không còn dải trống trong khung |
| `docs/img/thechat/shot_v8_desktop_1337.png` | Nguyên trang 1337px sau v8 (`wrap` 803, ảnh 408px) |
| `docs/img/thechat/shot_v8_mobile_390.png` | Nguyên trang 390px sau v8 (`wrap` 1119, ảnh full-bleed 326px, `+54 Phòng Thủ` về 1 dòng) |
| `docs/img/thechat/crop_v9_length_vs_xiulian.png` | **v9**, ghép 1:1 đúng khổ trình duyệt thật 1920×945: Nhục Thân giờ tràn xuống hết màn (trước v9 dừng sớm hơn Tu Luyện) |
| `docs/img/thechat/shot_v9_desktop_1337.png` | Nguyên trang 1337px sau v9: ảnh 590×416 phủ trọn card, 6 ngọc đúng giải phẫu, `wrap` 1051 |
| `docs/img/thechat/crop_v9_forge_601.png` | Dải luyện hoá ngang ở khổ hẹp nhất của breakpoint (601px): 2 chip + nút vẫn 1 hàng |
| `docs/img/thechat/crop_v10_trim_vs_xiulian.png` | **v10 chốt**, ghép 1:1 đúng khổ thật 1920×945: Nhục Thân `wrap` 957 cạnh Tu Luyện 846 — ảnh vẫn **full-bleed**, không lề tối hai bên |
| `docs/img/thechat/shot_v10_desktop_1920.png` | Nguyên trang 1920×945 sau v10 (6 ngọc đúng giải phẫu, dải luyện 2 hàng) |
| `docs/img/thechat/shot_v10_desktop_1337.png` | 1337px — crop cột panel: chip "Linh Thạch 900,000/1,300" về **1 dòng**, nút Luyện Hóa cùng hàng |
| `docs/img/thechat/shot_v10_desktop_601.png` | Khổ hẹp nhất của breakpoint: `wrap` 1111, chip + nút vẫn 1 hàng, không tràn ngang |
| `docs/img/thechat/shot_v10_mobile_390.png` | 390px **không đổi** (`wrap` 1119) — mọi chỉnh sửa v10 nằm trong `@media(min-width:601px)` |
| `docs/img/thechat/crop_v11_vs_xiulian.png` | **v11 chốt**, ghép 1:1 cột panel 660px của hai màn: Nhục Thân `wrap` **840** cạnh Tu Luyện **846** — hai trang giờ **ngắn bằng nhau**, cùng kết ở một độ cao |
| `docs/img/thechat/shot_v11_desktop_1920.png` | Nguyên trang 1920×1080, tài khoản **trắng** (như ảnh chủ dự án gửi): cả trang + bar + đồng hồ nằm gọn, không cuộn. Ảnh đã cắt 45px đỉnh/đáy, 6 ngọc vẫn đúng người |
| `docs/img/thechat/shot_v11_desktop_601.png` | 601px — khổ **hẹp nhất** của breakpoint, cũng là khổ gắt nhất của phép cắt ảnh (nhãn ngọc Da còn 2px hở đỉnh): không clipping |
| `docs/img/thechat/shot_v11_mobile_390.png` | 390px **không đổi** (`wrap` 1119) — ảnh mobile vẫn `663:467`, mọi thứ v11 nằm trong `@media(min-width:601px)` |
| `docs/img/thechat/shot_v11_desktop_stage4.png` | Trạng thái **đầy số liệu nhất** (`Kiếp Cốt`, 6 node 6/8/9/11/12/14) — rail 5/6 "Đã đạt", 6 điều kiện đột phá, mốc `Tổng cấp 60 / 120` |
| `docs/img/thechat/crop_v11_card2_stage4.png` | Crop card2 ở trạng thái đó: `[Thiếu] Đế Lệnh: 0 / 8` về **1 dòng** nhờ `.nb-req{font-size:12.5px}`, hai cột cân nhau |
| `docs/img/thechat/crop_v11_rejected_backdrop_1920.png` | **BẰNG CHỨNG ĐƯỜNG ĐÃ BÁC** (bước 2 §3.8): `max-width:520px` + ảnh nền blur phủ hai lề → hai dải xám đục hai bên bức hình sắc. Đừng làm lại |
| `docs/img/thechat/shot_xiulian_1920.png` | Màn Tu Luyện chụp cùng harness/cùng khổ, để ghép 1:1 |
| `docs/img/thechat/shot_fx_s0_d1337.png` | **fx0 Phàm Cốt trên tài khoản hero** (6 node vẫn 12/9/14/6/8/11, chỉ Cốt Cách bị ép về 0): quầng **tĩnh** nhạt màu xám trên thân, badge có halo, **không** sweep/không nhấp nháy (§12.5) |
| `docs/img/thechat/shot_fx_s2_d1337.png` | **fx2 Ngọc Cốt** — wash cyan phủ thân, badge + tab `Ngọc Cốt` halo cyan, ngọc viền cyan. **Sweep là giải 8s nên một ảnh tĩnh không chắc bắt được** — đừng đòi thấy nó trong still |
| `docs/img/thechat/shot_fx_s5_d1337.png` | **fx5 Tiên Cốt** — rực nhất, badge + tab `Tiên Cốt` chữ cầu vồng, 🐉 vẫn hiện (§12.4.3) |
| `docs/img/thechat/crop_fx_aura_stack.png` | **Bằng chứng quyết định thiết kế §12.3**: crop `.nb-stage` 3 bậc ghép chồng — cho thấy vầng sáng đặt ở **đáy** bị đốm trắng sẵn có của ảnh nuốt mất, nên mới đổi sang lớp phủ toàn thân |
| `docs/img/thechat/crop_fx_rainbow_phases.png` | **Cầu vồng chạy thật**: 3 pha hoạt ảnh (virtual-time 11.0 / 12.4 / 13.8 s) — vàng → magenta/tím → vàng |
| `docs/img/thechat/crop_fx_s0_vs_s1.png` | **fx0 cạnh fx1** (trái: tài khoản trắng Phàm Cốt; phải: Kim Cốt, ngọc `Da` đã đụng trần 20 → nhãn xanh + vòng `is-max`) — thang bậc nhìn thấy được, và hình học hai bản **giống hệt nhau** |
| `docs/img/thechat/crop_fx_mobile_390.png` | fx0 vs fx5 trong iframe 390px — hiệu ứng không phá khổ mobile (`wrap` vẫn 1119) |
| `docs/img/thechat/crop_gem_axis_guides.png` | **v13 Nhục Thân — bằng chứng lệch trục**: ảnh thân hình kèm kẻ dọc tại 50% và tại trục đối xứng đo được (51.28% đầu-vai, 50.38% đài sen); 6 tâm ngọc nằm hẳn về trái đường trục thật |
| `docs/img/thechat/shot_gem_shift_desktop_1337.png` | Sau khi sửa, desktop 1337px: ngọc bám đúng trục của ảnh ở cả ba dải |
| `docs/img/thechat/shot_gem_shift_mobile_390.png` | Sau khi sửa, mobile 390px — cùng một bộ `left%` đúng cho cả hai khổ (§3.1) |
| `docs/img/thechat/crop_tcq_ladder10.png` | **Trang Nâng Phẩm §13: 10 phẩm cấp ghép lưới 2×5** (q1→q10) — khác nhau cả hue lẫn cường độ; q10 là thẻ "tối đa" chữ cầu vồng |
| `docs/img/thechat/shot_tcq_mobile_390.png` | q1 / q5 / q10 trong iframe 390px — bố cục không đổi, không tràn ngang |
| `docs/img/thechat/crop_tcq_msg_fail.png` | Banner kết quả **thất bại** (POST thật, thiếu Thiên Cơ Đá): chữ đỏ trên quầng đỏ |
| `docs/img/thechat/crop_tcq_msg_ok.png` | Banner **thành công** — CHƯA POST thật, chỉ nạp đúng markup `.tcq-msg` mà PHP sinh vào trang thật (tránh sửa số liệu DB) |
| `docs/img/thechat/crop_tcq_reduced_motion_q5.png` | `--force-prefers-reduced-motion` ở q5: aura + halo **tĩnh** còn nguyên, sweep/glint biến mất |

**Đọc trên ảnh v4 (hero 390px, phần nội dung — bar dưới cùng xem §3.4):** badge `🟡 Kim Cốt · trần 20`;
rail `Phàm Cốt Đã đạt`
(xanh) / `Kim Cốt Trần 20` (đang xem, viền vàng) / `Ngọc Cốt Cấp 200` / `Linh Cốt…` (phải quét ngang);
dòng `Chưa có Thể Chất · 🟡 Kim Cốt — đang tu · trần 20/bộ phận`; 6 ngọc 6 màu khác nhau, nhãn
`Da 12 · Thịt 9 · Gân cốt 14 · Tủy 6 · Máu 8 · Thần 11`; lò luyện `Bì Phủ Cấp 12 / 20`,
`+4/cấp · đang +54 Phòng Thủ` (= 12 × 4 × 1.12 → **mốc Cương Thân +12% nhân đúng**), chip
`📦 Tinh Thể Đá 500/3` + `💰 Linh Thạch 900,000/1,300`; `Cương Thân +12%` với bar `Tổng cấp 60 / 120`;
đột phá `🟡 Kim Cốt → 💎 Ngọc Cốt · trần 30`, `[Thiếu] Tổng cấp Lục Bộ Phận: 60 / 80` và nút
**đúng là disabled**. Ảnh v1 (trước nén) còn giữ nhãn cũ `Trần mỗi bộ phận: 30 cấp` — **nhãn đã đổi**
thành `trần 30/bộ phận` khi gộp card, đừng lấy ảnh v1 làm chuẩn chữ.

### 9.2 Bốn kiểm tra bắt buộc sau khi sửa

```bash
php.exe -l game/thechat.php && php.exe -l class/function/thechat.php && php.exe -l class/player.php && php.exe -l tongquan.php
grep -n "mystyle.css?v=" game.php gm/gm.php gm/player.php     # bump CẢ 3, hiện 2026100929
grep -n "nbImgVer\s*=" game/thechat.php                       # bump khi đổi ảnh trong images/thechat/, hiện 2026100901
# đếm ngoặc nhọn khối .nb-* và quét non-ASCII (xem §11 UI_XIULIAN)
# đo lại 4 viewport → stage phải ra tỉ lệ 1.420, không có !BOX/!TAG (§3.2)
```

### 9.3 Quét trạng thái URL + trạng thái đụng trần (làm lại được)

* **14 trạng thái URL** (`nb_sweep.php`) × 2 tài khoản seed (hero / trắng): `node=1..6`, `view=0..5`,
  không tham số, và tham số rác `node=99&view=42`. Kết quả: **0 Warning / 0 Notice / 0 Fatal**,
  `.nb-stage` có mặt ở **mọi** trang, và (từ bản chuỗi liên kết) mỗi trang đếm đúng **6 tab + 5
  `.nb-link`** + `decode(encode($q)) === $q` cho cả 14. Đây là cách duy nhất bắt được nhánh
  `thechat_info() === false` (bug §7.1).
* **Trạng thái đụng trần** (node 1 = 20, trần 20): nhãn `.nb-gem.is-max` màu vàng, ngọc chuyển art
  `-day`, `.nb-capnote` thay cho 2 chip vật liệu, nút Luyện Hóa `disabled`, `+90 Phòng Thủ`
  = 20 × 4 × 1.12 → **mốc Cương Thân +12% nhân đúng trên UI**, tổng cấp 68/120.
* **Smoke hồi quy 6 trang khác** (`xiulian`, `bag`, `equip`, `hongniau`, `daily`, `tongquan`) → tất cả
  vẫn render sau khi thay khối `.nb-*` (khối này đặt cuối file nên không thể đè trang khác, nhưng vẫn phải đo).

### 9.4 POST test qua route thật (`nb_post.php`, `nb_break.php`)

Không chỉ gọi hàm nội bộ — cURL form thẳng vào `game.php?cmd=<thechat>`:

| Tình huống | Kết quả mong | Đã thấy |
|---|---|---|
| `refine` node 4 (Lv.6) | +1 cấp | "Cốt Tủy lên cấp 7" |
| `refine` node 3 (Lv.14, trần 20) | +1 cấp | "Cân Cốt lên cấp 15" |
| `break` khi tổng cấp < 80 | **chặn + không trừ gì** | "Tổng cấp Lục Bộ Phận chưa đủ. Cần 80, đang có 62" |
| `nb_action` lạ (`boom`) | không phá trang | không msg, HTML vẫn 12.512 byte |
| `break` khi đủ (tổng 84, ulv 1000, 5 Thánh Thạch, 900k LT) | lên bậc + trừ đúng | "Đột phá thành công! Cốt Cách đạt Ngọc Cốt, trần mỗi bộ phận là 30 cấp" |

Sau đột phá: `thechat_awaken_stage 1→2` · Linh Thạch **900.000 → 800.000** · Thánh Thạch **5 → 0** ·
**6 bộ phận giữ nguyên cấp** · `player_thechat_awaken_log` ghi `1 → 2` · `refine` chạy được tới trần mới.

---

## 10. Nợ còn lại — **CHƯA test / CHƯA làm**

- 🔴 **Production chưa chạy `sql/sql_thechat_body.sql`.** Local đã áp (có dump). **Không tự chạy trên production.**
- 🔴 **`thechat_body_break()` còn ghi log vào `player_thechat_awaken_log`** (bảng của hệ cũ, nằm trong
  `sql/deploy_production_20261004.sql`). **CHƯA xác nhận production có bảng này** — nếu không có thì
  đột phá sẽ roll-back và báo "Đột phá thất bại, tài nguyên chưa bị trừ". Kiểm tra trước khi deploy.
- 🔴 **Chưa test cảm ứng điện thoại thật.** Mới có iframe 390px trên desktop. Tab rail quét ngang và
  6 ngọc chạm được hay không chưa có máy thật xác nhận.
- 🔴 **Không so 1:1 với ảnh mẫu được.** 4 ảnh chủ dự án gửi **không còn trên đĩa** (đã quét
  `E:/window/Temp` và `%LOCALAPPDATA%/Temp`), nên bản này bám theo **mô tả cấu trúc** của mẫu,
  không phải đo ảnh. Muốn chốt đúng/mẫu thì gửi lại ảnh.
- 🟠 `passive_regen` / `resist` / `reduce` của thể chất vẫn **chỉ hiển thị**, chưa nối vào chiến đấu.
- 🟠 Hai bug cũ ở §8 chưa sửa.
- 🟠 `thechat_awaken()` (dòng 531) và `class/config/thechat_awakening_config.json` giờ là **code chết**
  — không còn route nào gọi. Chưa xoá (repo 0 commit). **Cẩn thận: hàm này vẫn ghi vào cột
  `thechat_awaken_stage`** là cột Nhục Thân đang dùng — nếu ai nối lại route cũ sẽ phá bậc Cốt Cách.
- 🟠 **Ba trang anh em vẫn còn đúng lỗi nút trắng §13.4**: `game/huyetmach_awakening.php`,
  `game/huyetmach_transform.php`, `game/quydoi.php` đều có `<button class="tc-btn …">` và `<style>`
  riêng không có tiền tố `.main ` → `.main button` (gamecss.css:122) vẫn ăn mất gradient của chúng.
  Chưa sửa vì ngoài phạm vi "chỉ trang Thể Chất"; sửa hàng loạt thì làm cả 4 cùng một lúc.
- 🟡 **Tài khoản E2E (`e2e2e2…`) đã seed lại về trạng thái hero** khớp ảnh nghiệm thu: Cốt Cách Kim Cốt
  (stage 1), 6 bộ phận 12/9/14/6/8/11, 500 Tinh Thể Đá, 6 Thánh Thạch, 900.000 Linh Thạch, `endtime` +4 giờ.
  Tài khoản này **không có Thể Chất** (`thechat_id = 0`) nên ảnh chụp luôn test cả nhánh §7.1.
- 🟡 **Harness đã dọn khỏi web root**: `_nb.html`, `_nbm.html`, `_nbr.html`, `_nbt.html`, `_nbb.html`, `_nbp.html`, `_nbw.html`, `_nbd.html`, `_nbf.html`, `_nbshot.html`, `_nbfx.html`, `_tcqm.html`, `_tcqshot.html`, `_tcqpost.html`, `_tcqmsg.html` đều đã xoá
  (repo 0 commit — để lại là thành file "chết" trong dự án). Cần đo lại thì copy từ `E:/window/Temp/`
  vào, đo xong xoá ngay. `_nbfx.html` = bản có probe computed-style của khối hiệu ứng (in `nm/disp/opacity`
  của `.nb-aura`/`.nb-sweep` vào `<title>`), dùng với `--dump-dom`; `_nbshot.html` = bản chụp ảnh;
  `_tcqshot.html`/`_tcqpost.html`/`_tcqmsg.html` + `tcqseed.php.txt` = bộ của trang Nâng Phẩm (§13.5.4, §13.5.5).
- 🟡 **Bar `.nb-tabbar` mới có headless**, chưa bấm thật trên điện thoại. Quan trọng hơn: yêu cầu
  "bar dưới cùng bên Tu Luyện" được hiểu là **1 nút đơn lẻ** (đúng chữ "để đơn lẻ 1 nút Nhân vật cho
  đẹp"), **không** phải bê nguyên 6 tab Tu Luyện/Nhân Vật/Bản Đồ/Tông Môn/Nhiệm Vụ/Cửa Hàng sang đây.
  Nếu chủ dự án muốn 6 tab thật thì chỉ thêm 4 `<a>` + 4 chuỗi `cmd=` — đã hỏi lại trước khi làm.
- 🟡 **Bản nén (2 card) mới nghiệm thu bằng headless**, chưa có người thật xem trên điện thoại.
  Chủ dự án xem qua `qoder.com` trên điện thoại. **Desktop đã hết dài**: `wrap` 840 vs Tu Luyện 846,
  cả trang nằm gọn trong cửa sổ 1080px (ảnh chụp để `zoom=1`, tức là khổ **to hơn** cái anh ấy thấy
  vì `body.bg-body` zoom .75 từ 1024px — nếu còn kêu dài thì là **mobile**).
  Nút chỉnh còn lại nếu anh ấy vẫn muốn ngắn (theo thứ tự đáng làm):
  (1) 390px vẫn 1119px — chưa có bản cắt dọc cho mobile vì §3.8 chứng minh desktop cắt được còn
  mobile thì nhãn ngọc ăn mất 87px ảnh; muốn ngắn trên điện thoại phải **đổi tỉ lệ ảnh** hoặc
  **bỏ nhãn chữ ra khỏi box** (nhãn thành cột số riêng), không phải chỉnh padding;
  (2) gộp 2 card thành 1; (3) thu `.nb-rail` thành 1 dòng cuộn; (4) bỏ `.nb-viewline` (24px) vì badge
  trên đầu đã chứa cùng thông tin.
- 🟡 **601px là khổ gắt nhất của phép cắt ảnh**: nhãn ngọc Da chỉ còn ~2px hở trên đỉnh (`top:15%`
  thay vì 14.1% đúng tâm để lấy chỗ đó). Đổi cỡ đĩa ngọc hoặc cỡ nhãn là phải đo lại §3.8.

---

## 12. Hiệu ứng phân tầng theo Cốt Cách (`nb-fx0 … nb-fx5`)

**Yêu cầu (09/10/2026):** "ok đẹp rồi bổ sung hiệu ứng đi" — kèm ảnh chủ dự án chụp tài khoản **thật**
của chính mình (`tttttt`, ID 548, **Phàm Cốt · trần 10**, 6 node Lv.0). Ảnh đó là ràng buộc quan trọng
nhất: tài khoản của anh ấy ở **bậc thấp nhất**, nên hiệu ứng **không được phép** vô hình ở bậc 1.

**Khuôn đã chốt từ trước** (memory *hiệu ứng phân tầng theo bậc*, đã duyệt "rất chuẩn" ở trang tổng
quan — nguồn từ vựng bậc xem `tongquan.php:740-774`): không hiệu ứng → gradient tĩnh nhạt → sweep 8s →
7s/5.5s/4.5s/3.6s → **rainbow 2.8s + glow** ở bậc chót. Rainbow đặt trên **chữ** của nhóm bậc chót
(anh ấy đã bác phương án chuyển cầu vồng sang **viền**).

### 12.1 Cách nối dữ liệu → CSS (không hard-code màu)

PHP chỉ sinh **một class bậc** trên `.nb-wrap`, mọi thứ khác do CSS lo:

| Nối | Nguồn | Chỗ dùng |
|---|---|---|
| `nb-fx<N>` (`game/thechat.php:140`, `max(0,min(5,$nbStage))`) | bậc Cốt Cách thật | bậc thang `--nb-dur/--nb-aura/--nb-glow/--nb-btn-dur` |
| `--nb-tier` (inline trên `.nb-wrap`) | `thechat_awaken_color($nbStage)` | vòng quanh thân, sweep, chữ `nb-title`, `box-shadow` badge |
| `--tcolor` (inline mỗi tab rail) | `thechat_awaken_color($nbT)` — **màu của chính tab đó** | `nb-tier-ico` / `nb-tier-name` / `nbTierGlow` |
| `nb-t0 … nb-t5` | vị trí tab trong chuỗi | `--t-glow` 4→24px, mỗi tab sáng theo **bậc của nó** chứ không theo bậc nhân vật |
| `--ring` (inline mỗi ngọc) | màu chỉ số của bộ phận đó | viền + quầng đĩa ngọc |
| `--glow` (inline mỗi ngọc) | `round(min(1, node_lv / lv_cap) * 16)` | độ dày quầng — ngọc **của riêng bộ phận đó** mạnh lên theo cấp của nó |

`$nbGglow` = 0 khi `lv_cap = 0`, nên tài khoản trắng không chia 0.

### 12.2 Bảng bậc (CSS dòng 7215→7226)

| Bậc | Tên | `--nb-dur` | `--nb-aura` | `--nb-glow` | `--nb-btn-dur` | Cảm nhận được |
|---|---|---|---|---|---|---|
| fx0 | Phàm Cốt | — (đóng bằng `animation:none`) | .04 | 4px | 6s | **gradient tĩnh nhạt**, không một chuyển động |
| fx1 | Kim Cốt | 10s | .06 | 7px | 5s | vòng thở rất chậm |
| fx2 | Ngọc Cốt | 8s | .09 | 10px | 4.2s | + sweep ngang |
| fx3 | Linh Cốt | 5.5s | .13 | 14px | 3.4s | rõ hơn |
| fx4 | Kiếp Cốt | 3.6s | .17 | 19px | 2.8s | rực |
| fx5 | Tiên Cốt | 2.8s | .22 | 24px | 2.2s | **+ chữ chạy cầu vồng** ở badge và tab `Tiên Cốt` |

Bảy thành phần được hiệu ứng hoá: `.nb-aura` (phủ toàn thân), `.nb-sweep`, `.nb-title`, `.nb-badge`,
`.nb-tier.is-cur` + `.nb-tier-ico/-name`, `.nb-gem.is-max` (+ nhãn `is-max`), `.nb-ms-fill:after`,
`.nb-btn-refine/.nb-btn-break:after`.

### 12.3 Đường đã thử và **BÁC**: vầng sáng dưới bệ sen

Bản đầu đặt ellipse `filter:blur` ở `bottom:2%` (định mô phỏng "bệ sen phát sáng" của LKCT).
**Không bao giờ thấy được**, vì ảnh `nhuc-than.jpg` **đã có sẵn đốm sáng trắng/cyan** ở đúng chỗ đó —
vầng mới bị nền nuốt. Chứng minh bằng cách ghép crop `.nb-stage` của 3 bậc
(`docs/img/thechat/crop_fx_aura_stack.png`; bản lỗi còn ở `E:/window/Temp/fx_stack_aura.png`).
Bài học: **muốn thấy hiệu ứng trên ảnh có sẵn thì phải phủ lên toàn bộ chủ thể**, không cộng sáng
vào chỗ ảnh đã sáng. Bản thay thế = `radial-gradient` tâm thân hình + `inset box-shadow` bốn viền.

### 12.4 Bốn MỒI riêng của khối hiệu ứng

1. **`mix-blend-mode:screen` bắt buộc `.nb-stage{isolation:isolate}`** — thiếu thì lớp wash blend
   với cả nền trang, ánh sáng **chảy ra ngoài** khung ảnh.
2. **fx0 không được đóng bằng `--nb-dur:0s`.** Chrome vẫn giữ keyframe 0% (với `nbAuraPulse` 0% là
   `opacity:.04` — vẫn nhấp nháy). Phải có rule `animation:none` tường minh ở cuối khối (dòng 7252)
   và `.nb-sweep{display:none}` cho fx0.
3. **Chữ cầu vồng = `background-clip:text`, ba điều kiện đi cùng nhau:** shorthand `background:` phải
   đặt **trước** `-webkit-background-clip:text` trong cùng rule (shorthand reset `background-clip` —
   chính cái bug đã làm chữ ẩn ở trang khác); `color:transparent` (PHP gắn màu inline nên phải chắc
   chắn ăn); và **emoji phải rời khỏi phần tử clip** — 🐉/🌑… bọc trong `<i class="nb-badge-ico">`
   mang `-webkit-text-fill-color` riêng, nếu không glyph biến mất. Trên rail thì tên bậc ở trong
   phần tử clip nên `.nb-t5 .nb-tier-name` phải `text-shadow:none` (bóng loang sẽ bị clip cắt mất).
4. **Không đổi hình học.** Toàn bộ hiệu ứng chỉ dùng `box-shadow` / `text-shadow` / pseudo-element
   `position:absolute` / `opacity` / `transform` — **không** thêm `padding`/`margin`/`width`, vì v11 đã
   chốt số đo và bị ràng buộc cắt dọc ảnh (§3.8, §6.13).

### 12.5 Nghiệm thu (đã **nhìn** ảnh, không chỉ đo số)

* **Không hồi quy hình học**: `wrap` = **840** @1337/1920 · **956** @601 · **1047** @760 · **1119** @390 —
  **khớp v11 từng px**; 6 toạ độ ngọc không đổi (`top:46x46@50,15`, `left-up@34,39`, `right-up@66,39`,
  `left-down@25,73`, `right-down@75,73`, `bottom@50,87`).
* **fx0 có hiệu ứng thật** (đúng ràng buộc từ ảnh tài khoản của chủ dự án): bản **trắng hoàn toàn**
  (6 node Lv.0, `nbempty.php`) ở nửa trái `crop_fx_s0_vs_s1.png` cho thấy quầng tĩnh nhạt + halo badge
  + ngọc viền xám mờ, trong khi sweep/rail-pulse/ms-flow tắt; bản **hero nhưng bậc 0**
  (`shot_fx_s0_d1337.png`) chứng minh ngọc vẫn sáng theo **cấp của chính nó** (`--glow`) dù bậc là thấp nhất.
* **Cầu vồng chạy thật**: chụp 3 pha hoạt ảnh (`--virtual-time-budget` 11.000/12.400/13.800ms) →
  chữ badge đổi `vàng → magenta/tím → vàng`, ghép 3 pha ở `crop_fx_rainbow_phases.png`.
* **`prefers-reduced-motion`** (chụp với `--force-prefers-reduced-motion`): `.nb-aura` `nm=none`
  (**giữ** lớp wash tĩnh), `.nb-sweep` `disp=none`, nút/ms/rail/badge/tên-tab đều `none`.
* `php -l` sạch · `nb_sweep.php` **14/14 ALL CLEAN** · khối `.nb-*` cân **212/212** ngoặc, **0 non-ASCII**
  · `mystyle.css?v=2026100928` đủ 3 file · web root đã xoá `_nbfx.html`/`_nbshot.html`.

Bằng chứng: `shot_fx_s0_d1337.png` · `shot_fx_s2_d1337.png` · `shot_fx_s5_d1337.png` ·
`crop_fx_aura_stack.png` · `crop_fx_rainbow_phases.png` · `crop_fx_s0_vs_s1.png` (fx0 tài khoản trắng
cạnh fx1 hero) · `crop_fx_mobile_390.png`.

### 12.6 Nợ riêng của phần hiệu ứng — **CHƯA test**

- 🔴 **Mượt hay không chưa ai xem bằng mắt thường.** Headless chỉ chứng minh *có* khung hình khác nhau
  giữa các pha, không chứng minh 60fps trên máy anh ấy.
- 🔴 **`mix-blend-mode:screen` + `isolation:isolate` trên WebView điện thoại thật CHƯA kiểm.** nếu
  WebView không hỗ trợ blend, lớp wash sẽ hiện thành **mảng đặc** thay vì ánh sáng — phải xem trên
  chính điện thoại của chủ dự án (anh ấy xem qua `qoder.com`).
- 🟠 **fx0 và fx1 trên ảnh tĩnh gần như không phân biệt được** (khác .04 vs .06 opacity, halo 4 vs 7px).
  Bậc 1 chỉ khác khi xem chuyển động. Nếu anh ấy thấy "bậc thấp vô dụng" thì hướng sửa đúng là **tăng
  `--nb-aura` của fx0/fx1**, không phải thêm hiệu ứng mới.
- 🟠 **Chưa kiểm fx3/fx4** bằng ảnh (mới chụp fx0/fx1/fx2/fx5) — chỉ nội suy từ bảng biến.
- 🟡 **Chưa quay lại `.nb-gem.is-sel`** (ngọc đang chọn vẫn dùng `nbBreath` 2.4s **cố định**, không theo
  bậc) — chủ ý để trạng thái chọn luôn nổi; nếu muốn nó phân tầng thì đổi `2.4s` → `var(--nb-dur)`.

## 13. Hiệu ứng phân tầng trang **Nâng Phẩm** (`game/thechat_upgrade.php`) — v13

Chủ dự án: *"còn hiệu ứng cho cả giao diện nữa chứ, thể chất nữa chứ"*. Hai câu hỏi chốt phạm vi:

* **Phạm vi = "Chỉ trang Thể Chất"** → màn Nhục Thân (§12) giữ nguyên từng pixel, chỉ trang
  `thechat_upgrade.php` được thêm lớp hiệu ứng. Trước v13 trang chỉ **88 dòng**; sau khi cộng
  khối `.tcq-*` thì **186 dòng**, toàn bộ CSS nằm trong `<style>` của chính trang.
* **Cầu vồng = "Chỉ Đạo Phẩm"** → 10 phẩm cấp đều có màu riêng trong config; **riêng bậc 10** mới
  đổi chữ sang cầu vồng. Thần Phẩm (8) và Đế Phẩm (9) chỉ được glow mạnh.

### 13.1 Vì sao phải đặt tên class mới (`.tcq-*`)

`.tc-card` / `.tc-badge` / `.tc-btn` là **họ tên dùng chung bởi 5 trang** (`huyetmach`,
`huyetmach_awakening`, `huyetmach_transform`, `quydoi`, `thechat_upgrade`), mỗi trang tự khai
`<style>` riêng. Viết rule mới vào các tên đó thì chỉ trang này hưởng, nhưng người đọc sau sẽ
tưởng là sửa chung. Nên toàn bộ lớp hiệu ứng mang tiền tố **`.tcq-`** và nằm gọn trong
`<style>` của trang — không rơi vào `mystyle.css`, **không cần bump `?v=`**.

### 13.2 Nối dữ liệu → CSS

PHP chỉ xuất **một class bậc** + biến tuỳ chỉnh; mọi cường độ nằm trong CSS (đúng kiến trúc §12.1):

```php
$tcqFx = 'tcq-q' . max(1, min(10, $thechatQuality));                 // tcq-q1 .. tcq-q10
$tcqColor = \player\thechat_quality_color($thechatQuality);           // '#9e9e9e' .. '#e0e0e0'
$tcqToColor = $upgradeInfo ? thechat_quality_color(to_quality) : $tcqColor;
list($r,$g,$b) = sscanf($tcqColor, '#%02x%02x%02x');  // -> --tcq-rgb: 158,158,158
```

Phải tách thành **`--tcq-rgb`** (ba số) chứ không dán hex thẳng vào `rgba()`: `rgba(#9e9e9e,.3)` là
CSS sai, còn hex 8 số (`#9e9e9e4d`) thì WebView cũ có thể không đọc. Định nghĩa đặt **sau** khối
POST để bậc đổi ngay khi thăng phẩm.

### 13.3 Bảng bậc (toàn bộ trong `<style>` của trang)

| Bậc | Phẩm | Màu | aura | nhịp | glow | halo | quét | lat nút | mũi tên |
|---|---|---|---|---|---|---|---|---|---|
| q1 | Phàm | `#9e9e9e` | .24 **tĩnh** | — | 8px | 0 | — | — | — |
| q2 | Linh | `#4caf50` | .30 | 10s | 10px | 5px | — | — | — |
| q3 | Huyền | `#2196f3` | .36 | 8s | 13px | 7px | — | — | — |
| q4 | Địa | `#9c27b0` | .44 | 6.5s | 16px | 9px | — | 4.5s | — |
| q5 | Thiên | `#ff9800` | .52 | 5s | 19px | 11px | 4.6s | 4s | — |
| q6 | Thánh | `#ff5722` | .60 | 4.2s | 22px | 13px | 4s | 3.6s | — |
| q7 | Tiên | `#f44336` | .66 | 3.4s | 25px | 15px | 3.4s | 3.2s | 5s |
| q8 | Thần | `#ffd700` | .74 | 2.8s | 30px | 18px | 3s | 2.8s | 4.2s |
| q9 | Đế | `#7c4dff` | .82 | 2.4s | 34px | 20px | 2.6s | 2.5s | 3.6s |
| q10 | **Đạo** | `#e0e0e0` | .92 | 2s | 40px | 24px | 2.2s | 2.1s | 3s | **+ chữ cầu vồng** |

Tắt theo nhóm phải viết `animation:none` từng bậc (§12.4.2): q1 tắt hết chuyển động, quét ngang từ
q5, lat nút từ q4, mũi tên nhấp nháy từ q7. **q1 tĩnh nhưng vẫn phải thấy gì đó** — quầng xám +
viền sáng, vì tài khoản thật của chủ dự án đang ở đúng bậc này.

### 13.4 Phát hiện ngoài phạm vi nhưng phải sửa: nút "Nâng Cấp" bị ẩn màu

`css/gamecss.css:122` có `.main button{background:#fff;border:1px solid #e3e3e3;…;color:#0093E8}` —
đặc cách **0-1-1** thắng `.tc-btn-primary` (**0-1-0**). Kết quả: từ trước tới nay nút nâng phẩm
in **trắng như nút mặc định của trình duyệt**, đoạn gradient cam-vàng trong trang không bao giờ
hiện. Đã nâng thành `.main .tc-btn*` (0-2-0) → đúng thiết kế. **Ba trang anh em vẫn còn lỗi này**:
`huyetmach_awakening.php`, `huyetmach_transform.php`, `quydoi.php` (đều dùng `<button>` + `.tc-btn`)
— chưa sửa vì ngoài phạm vi, ghi ở §10.

### 13.5 MỒI riêng của trang này

1. **Đừng clip cầu vồng lên `.tcq-card`.** Thẻ có `background:linear-gradient(...)` riêng;
   `-webkit-background-clip:text` trên chính nó sẽ **nuốt luôn nền thẻ** (thẻ thành trong suốt).
   Phải clip lên `<span class="tcq-max-in">` bên trong. Đạo Phẩm là phẩm cuối nên `$upgradeInfo`
   trả `false` → thẻ hiển thị là thẻ "Đã đạt phẩm chất tối đa!", không phải thẻ nâng cấp.
2. **Màu chữ đặt trong class, đừng đặt inline.** `style="color:#c0a050"` thắng `color:transparent`
   của khối clip → chữ mất màu cầu vồng. Đây là lý do `⬆ Nâng cấp phẩm chất` và badge `→`
   chuyển từ inline sang `.tcq-head` / `.tcq-badge-to`.
3. **`.tcq-cost` dùng `box-shadow:inset 0 0 0 1px` chứ không dùng `border`** — border cộng 2px
   chiều cao panel, tức là dời hình dáng cũ. Mũi tên cũng không được thêm `display:inline-block`
   (đổi baseline của cả dòng).
4. **Bẫy harness khi test bậc:** `thechat_info()` trả `false` khi `thechat_id = 0`, nên
   `thechat_calc_bonuses()` **ép quality về 1** bất kể `game1.thechat_quality`. Seed bậc phải set
   **cả `thechat_id`** (`tcqseed.php <1..10>` set `thechat_id=1`), không là chụp 10 lần ra một ảnh.
5. **Bẫy harness URL:** `location.search.split('&')` + `split('=')` cắt URL bên trong iframe ở dấu
   `=` thứ hai (`…game.php?cmd`) → bị đá về màn Đăng nhập, ảnh chụp chỉ có nền đen. Phải lấy
   nguyên chuỗi sau `u=` rồi `decodeURIComponent`.
6. `class/encode.php:26` bắn Notice `Uninitialized string offset: 1` khi cmd thiếu hậu tố guard
   (tức cmd bị cắt) — **lỗi có sẵn của encode**, chỉ harness mới đụng; không phải bug của trang này.

### 13.6 Nghiệm thu (đã nhìn ảnh, không chỉ đo số)

* `php -l` sạch · **0 ký tự non-ASCII lạc chỗ** (quét smart-quote + CJK) · `php_error.log` không có
  Notice nào từ `thechat_upgrade.php`.
* Hình học **không đổi một px**: `cardH = 309` so với bản mô phỏng nút trắng cũ `297` → +12px, và
  đúng bằng chênh lệch nút (`btnH 39` vs `27`) đến từ sửa lỗi §13.4. Lớp hiệu ứng tự thân = **0px**.
  `arrowDisplay = inline` (giữ baseline), `btnBg = linear-gradient(135deg, rgb(24…`.
* `auraOp` đo ở q5 = `0.2184` trong khi `--aura-op:.52` → chứng minh `tcqBreath` **đang chạy**
  (đúng pha đáy .52×.42).
* `crop_tcq_ladder10.png`: 10 bậc ghép lưới — phân biệt được bằng **cả hue lẫn cường độ** bằng mắt.
* `shot_tcq_mobile_390.png`: q1/q5/q10 ở 390px, không tràn ngang, bố cục như bản chưa hiệu ứng.
* `crop_tcq_msg_fail.png`: POST **thất bại thật** (thiếu Thiên Cơ Đá) — banner đỏ có quầng đỏ,
  DB không đổi (`thechat_quality` vẫn 5 sau POST).
* `crop_tcq_reduced_motion_q5.png`: kill switch `prefers-reduced-motion` — aura/halo giữ nguyên
  dạng tĩnh, sweep + glint biến mất.
* Tài khoản E2E đã trả về `thechat_id=0, thechat_quality=1, stage=1` (trạng thái hero §9.1);
  web root đã xoá `_tcqm.html`, `_tcqshot.html`, `_tcqpost.html`, `_tcqmsg.html`; chỉ tắt server
  8199 của mình, **8123 và 8090 của chủ dự án còn sống** (kiểm tra `netstat` trước và sau).

### 13.7 Nợ riêng phần này — **CHƯA test**

- 🔴 **Banner THÀNH CÔNG chưa từng do POST thật sinh ra.** Ảnh `crop_tcq_msg_ok.png` là markup
  `.tcq-msg` mà PHP sinh, nạp vào trang thật bằng JS — phần **màu/CSS đã kiểm**, còn chuỗi
  `Thức tỉnh/Nâng phẩm thành công!` đi kèm `thechat_upgrade()` trả về thì **chưa đi hết route thật**
  (muốn test phải cấp nguyên liệu cho tài khoản E2E, đang để nguyên).
- 🔴 **Chưa xem trên điện thoại thật** — cùng nợ với §12.6.
- 🟠 **q4 và q5 trên ảnh tĩnh rất dễ nhầm** (chỉ hơn nhau chỗ quét ngang); khác biệt thật sự nằm ở
  chuyển động. Nếu chủ dự án thấy "hai bậc gần giống" thì hướng sửa là kéo giãn `--aura-op`,
  không phải thêm hiệu ứng mới.
- 🟡 **Chưa chụp q2/q3/q4/q6/q7 ở khổ mobile** (mới 3 bậc) — nội suy từ bảng biến.
- 🟡 **Ba trang anh em vẫn còn lỗi nút trắng** (§13.4) — chờ chủ dự án chốt có sửa hàng loạt không.

---

## 14. Test chi tiết Nhục Thân trên tài khoản trắng — 2026-10-11 (HTTP thật, cổng 8199)

Đi qua `game.php` thật bằng `E:/window/Temp/qa_http.php` + `qa_nbtrain.php` (POST loop). Tài khoản QA `2b06d0ff…c044`. 20 ảnh dump `E:/window/Temp/qa_nb_*.html` — tất cả UTF-8 hợp lệ, **0 warning/notice/fatal/SQLSTATE**.

| # | Hành vi | Cách test | Kết quả |
|---|---|---|---|
| 1 | Màn trắng | GET `thechat` (acc `thechat_id=0`, 6 node Lv0) | 2 card + 6 ngọc `khoa`, nút Luyện `is-off disabled`, Đột Phá disabled |
| 2 | Chặn server bypass nút | POST `nb_action=refine&nb_node=1` (0 vật liệu) | "Tinh Thể Đá không đủ. Cần 1, có 0." |
| 3 | Node sai | POST `nb_node=99` / `=0` | "Không có bộ phận này." |
| 4 | Mua vật liệu trong game | shop `djid=100` (500 LT) | Vào túi thật, chứng minh nguồn mở |
| 5 | Công thức luyện | 15 bước refine | khớp `material_base + floor(lv/per)` và `LT 100·(lv+1)`; chặn trần không trừ tài nguyên |
| 6 | Chỉ số khi đọc | `qa_nb.php gp` | def 4→42→50, hp 210, atk 32, crit 0.63, lifesteal→XX=2, passive_exp 1.26, mốc Thiết Thân +5% |
| 7 | Break chặn cảnh giới | POST break ở realm 1 | "[Thiếu] Cảnh giới: cấp 1 / 150" |
| 8 | Break thành công | `ulv=150` → POST break | stage 0→1, LT −20.000, mat −20, cap 10→20, `nb-fx0`→`nb-fx1`, badge "🌕Kim Cốt · trần 20", `player_thechat_awaken_log` ghi dòng |
| 9 | Luyện qua trần cũ | refine sau break | vượt trần cũ 10→12, `nb-cost-no`→`nb-cost-ok`, nút bật lại khi nạp LT |
| 10 | Xem bậc khoá | GET `view=3` / `view=9` | preview Linh Cốt; `view=9` fallback an toàn |
| 11 | Khai mở thể chất | mua+dùng Tẩy Tủy Đan 109 | gán id18 "Thôn Thiên Ma Thể" q2; bonus hợp nhất hp 60210/atk 12032/def 14450/crit 600.63 **với** body; tab "Nâng Phẩm Chất" hiện; zhuangtai 👁️Thể Chất + 🔵Huyền Phẩm + 🌕Kim Cốt |
| 12 | Nâng phẩm qua POST THẬT | `qa_http.php thechat_upgrade action=upgrade` | **§13.7 nợ "banner chưa từng do POST thật" nay ĐÃ TRẢ**: 1 lần chặn đúng khi thiếu LT (không tốn), 1 lần roll 95% thành công q2→q3 (`tcq-q3` áp dụng, `player_thechat_log` ghi dòng), các lần sau chặn đúng vì LT 20.000 |

**Phát hiện ghi vào sổ nợ (không tự sửa — "không nới khung chung" + "cẩn thận server-side"):**
- **Xem bậc khoá → panel mâu thuẫn**: chọn `view=3` thì `nb-viewline` ghi yêu cầu của bậc 3, nhưng gate + các dòng `[Đủ]/[Thiếu]` vẫn mô tả lần Đột Phá kế tiếp THẬT (Ngọc Cốt) → trên màn hình hai khối cãi nhau.
- **Nút Nâng Phẩm không bao giờ disable** theo túi/vật liệu (chỉ chặn server), khác pattern `[Đủ]/[Thiếu]` của panel Nhục Thân.
- **Đột Phá Cốt Cách KHÔNG cần boss lẫn nhiệm vụ** (khác Linh Căn) — khớp mục "Awakening system gaps" đã chốt.
- **`realm_min_level` bậc 1 = 150** ⇒ tài khoản trắng không thể đột phá sớm.
- **`thechat_awaken()`/`thechat_get_awakening_info()`/`thechat_awakening_config_all()` + `thechat_awakening_config.json` là code chết** (không caller thật; file vẫn khai `boss_require: thechat_boss_lv2/lv3` không ai thực thi).
- **`game1.thechat_awaken_stage` nay do Cốt Cách ghi (0-5) nhưng vẫn nhân `base_stats` thể chất cũ qua `thechat_awaken_mult = 1 + 0.2·stage`** (docblock giả định 0-3) → tới Tiên Cốt là ×2.0 trên chỉ số thể chất cũ.

**Chưa kiểm chứng:** (a) chưa xem trên điện thoại thật (mới headless 500px); (b) production **chưa migration** cho Nhục Thân.
