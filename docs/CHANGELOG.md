# XXJserver - Nhật ký thay đổi

## Kiểm tra căn chỉnh + test chi tiết 3 tính năng trên tài khoản trắng (11/10/2026)

**Yêu cầu của chủ dự án:** "làm xong kiểm tra căn chỉnh giao diện lại 1 lượt, sau đó tạo acc vào test lại chi tiết 3 tính năng mới làm lại thể chất, huyết mạch và linh căn. Sau khi hoàn tất tổng hợp chi tiết và ghi docs."

**Căn chỉnh (1 lượt, screenshot mobile 500px qua Chrome headless, cổng 8199):** Linh Căn sạch. Nhục Thân và Mạch Huyệt có rail thức tỉnh tràn ngang trên mobile — **đây là chủ ý đã ghi** (UI_THECHAT §rail: ≤560px chuyển tab cố định + quét ngang; `.hm-rail{overflow-x:auto}` cùng khuôn), không phải lỗi mới. **Một lỗi thật tìm thấy và đã sửa:** dòng forge của Mạch Huyệt in `"++54 Công Kích"` (dấu `+` nhân đôi vì `hmAttrRows['text']` đã mang `+` sẵn mà template `game/huyetmach.php:241` lại thêm `+` nữa) → bỏ `+` thừa, render lại thành `+54`. Nhục Thân không dính lỗi này.

**Test chi tiết (HTTP thật, cùng một tài khoản trắng `2b06d0ff…c044`):** cả ba tính năng đi hết vòng đời trên cùng một nhân vật.
- **Nhục Thân** (UI_THECHAT §14): 15 bước luyện khớp công thức, chặn trần không tốn tài nguyên, break 0→1 cần cảnh giới 150, mua+dùng Tẩy Tủy Đan gán thể chất, **nâng phẩm bằng POST thật** (trả nợ §13.7 "banner chưa từng do POST thật"). Ghi 6 phát hiện vào sổ nợ (mâu thuẫn panel bậc khoá, nút Nâng Phẩm không disable, break không cần boss/nhiệm vụ, code chết `thechat_awakening_*`, `awaken_stage` nhân `base_stats` cũ).
- **Mạch Huyệt** (UI_HUYETMACH §15 + Sổ nợ dòng 28-31): chuỗi bootstrap **THẬT** chạy được — mua Tố Thể Đan 110 trong shop → `use_dj` gán huyết mạch → boss stage 2 spawn → hạ boss rơi 112 + ghi checklist **theo tên boss** → luyện mạch → break 0→1 (không boss) → 1→2, 2→3 (chặn boss) → mở Biến Thân ở stage 3 (gate HP + happy path + hồi chiêu) → nâng phẩm q1→q3. **Xác nhận bằng thực nghiệm bế tắc dòng 2**: phải `adddj 112` thủ công mới luyện được mạch đầu tiên, vì 112 chỉ rơi từ boss stage 2/3/4 mà boss lại cần đã break tới stage 1 (cần 32 cấp mạch = cần 112). Thêm 3 dòng nợ: trừ Linh Thạch thiếu guard `uyxb >= ?` (race), 3 chuỗi lỗi ASCII không dấu, `can_transform` hardcode 20%.
- **Linh Căn**: đã test ở Task 11 (`test_linhcan_pages.php` 40/40 ALL PASS qua cổng 8199), Task 12 (di trú) vẫn **chờ chủ dự án duyệt bảng rồi `--apply`**.

**Chưa kiểm chứng (ghi rõ):** cả ba **chưa xem trên điện thoại thật** (mới render headless); production **chưa migration** cho cả Nhục Thân lẫn Mạch Huyệt; các bước luyện/break của Mạch Huyệt từ mục 5 trở đi **không tái hiện được trong game thật** cho tới khi chủ dự án mở nguồn 112 sớm (dòng 28).

## Thể Chất v13: 6 ngọc về đúng trục bức hình + hiệu ứng 10 phẩm cấp cho trang Nâng Phẩm (09/10/2026)

**Chủ dự án** (kèm ảnh 6 viên ngọc gắn trên thân hình đang ngồi thiền): "còn hiệu ứng cho cả giao diện nữa chứ, thể chất nữa chứ, ngoài ra tôi nhìn có vẻ 6 điểm tròn có vẻ lệch về bên trái 1 chút nên dịch sang phải 1 chút xíu cho cân giữa".

**Anh nhìn đúng, và đó là số chứ không phải cảm giác.** Test đối xứng gương trên `nhuc-than.jpg` (663px, `nbaxis3.py`): trục thật của bức hình là **51.28% ở đầu–vai** và **50.38% ở hông–đài sen**, không phải 50% — hoạ sĩ vẽ nhân vật nhích sang phải. 6 ngọc đặt theo 50/34/66/25/75/50 (đối xứng **giữa khung**) nên cả cụm lệch trái ~1%. Sửa **theo từng dải** (cặp trên 51.3, đài sen 50.4) chứ không cộng đều một số cho tất cả. Đo lại trong ảnh render: trục ảnh 51.34/51.29/50.37/50.37 so với tâm ngọc 51.30/51.30/50.40/50.40 → lệch ≤0.07%. Vì desktop `row = 45 + top%×377` còn mobile `row = top%×467` nhưng **cả hai cùng rơi vào các hàng 98/191/322/373**, một bộ `left%` dùng được cho cả hai khổ (đã chụp kiểm cả 2 khổ).

**Phạm vi do anh chốt, không phải do tôi đoán:** "Chỉ trang Thể Chất" → màn Nhục Thân giữ nguyên từng pixel của §12, chỉ `game/thechat_upgrade.php` được thêm hiệu ứng; "Chỉ Đạo Phẩm" → **duy nhất bậc 10** đổi chữ sang cầu vồng, Thần Phẩm (8) và Đế Phẩm (9) chỉ glow mạnh.

**10 bậc `tcq-q1…q10`, PHP chỉ xuất một class + biến màu.** `thechat_quality_color()` đưa hex, PHP tách thành `--tcq-rgb: 158,158,158` bằng `sscanf` (không dán hex thẳng vào `rgba()` — CSS sai; cũng không dùng hex 8 số — WebView cũ có thể không đọc). Bậc định nghĩa đủ 8 biến mỗi dòng: aura `.24→.92`, nhịp `10s→2s`, glow `8px→40px`, halo `0→24px`, quét ngang từ q5, lat nút từ q4, mũi tên nhấp nháy từ q7, cầu vồng q10. **q1 tĩnh nhưng vẫn phải thấy** — tài khoản thật của anh đang ở đúng bậc này (bài học từ fx0 Nhục Thân). Toàn bộ rule đặt trong `<style>` của trang và mang tiền tố **`.tcq-`**, vì `.tc-card`/`.tc-badge`/`.tc-btn` là họ tên **dùng chung 5 trang**; không đụng `mystyle.css` nên **không cần bump `?v=`**.

**Bất ngờ ngoài phạm vi: nút "Nâng Cấp" chưa bao giờ có màu.** `css/gamecss.css:122` có `.main button{background:#fff;…;color:#0093E8}` đặc cách **0-1-1**, thắng `.tc-btn-primary` **0-1-0** → gradient cam-vàng viết trong trang chưa từng hiển thị, nút in trắng như nút mặc định của trình duyệt. Đã nâng sang `.main .tc-btn*` (0-2-0). `huyetmach_awakening.php`, `huyetmach_transform.php`, `quydoi.php` **vẫn còn nguyên lỗi này** — chưa sửa vì ngoài phạm vi, đã ghi §10/§13.4.

**Hình học không đổi một px — có bằng chứng:** `cardH = 309` so với bản mô phỏng nút trắng cũ `297` → +12px, và **đúng bằng** chênh lệch chiều cao nút (`btnH 39` vs `27`) đến từ sửa lỗi ở trên; lớp hiệu ứng tự thân = 0px. Vì vậy `.tcq-cost` dùng `box-shadow:inset 0 0 0 1px` thay vì `border` (border sẽ cộng 2px chiều cao panel), và mũi tên `→` không được thêm `display:inline-block` (đổi baseline cả dòng). Cầu vồng clip lên `<span class="tcq-max-in">` **chứ không clip lên `.tcq-card`** — thẻ có `background:linear-gradient` riêng, clip vào là mất nền.

**Nghiệm thu bằng ảnh, không bằng số:** lưới 10 bậc ghép 2×5 (`crop_tcq_ladder10.png`) — phân biệt được bằng cả hue lẫn cường độ; `shot_tcq_mobile_390.png` (q1/q5/q10 ở 390px, không tràn ngang); `crop_tcq_msg_fail.png` = POST **thất bại thật** (thiếu Thiên Cơ Đá, DB không đổi); `crop_tcq_msg_ok.png` = banner thành công **mô phỏng** (nạp đúng markup PHP sinh, không POST để khỏi sup số liệu); `crop_tcq_reduced_motion_q5.png` = kill switch giữ aura/halo tĩnh, sweep/glint biến mất. `auraOp` đo giữa chừng ở q5 = `.2184` với `--aura-op:.52` → `tcqBreath` đang chạy thật.

**Đã kiểm:** `php -l` sạch · **0 ký tự non-ASCII lạc chỗ** (quét smart-quote + CJK — Edit đã chèn nhầm một chữ CJK vào docs, đã bắt được và xoá) · `php_error.log` không có Notice nào từ `thechat_upgrade.php` · `mystyle.css?v=2026100929` đủ 3 file · web root đã xoá 4 harness · tài khoản E2E trả về `thechat_id=0, thechat_quality=1, stage=1` · **chỉ tắt server 8199 của mình, 8123 và 8090 của anh còn sống** (kiểm `netstat` + curl trước/sau).

**Bẫy harness mới (đã ghi §13.5):** ① `thechat_info()` trả `false` khi `thechat_id = 0` nên `thechat_calc_bonuses()` **ép quality về 1** bất kể `game1.thechat_quality` — seed bậc phải set cả `thechat_id`, không thì chụp 10 lần ra một ảnh (đã mất một vòng vì điều này); ② `location.search.split('&')` + `split('=')` cắt URL trong iframe ở dấu `=` thứ hai (`…game.php?cmd`) → bị đá về màn Đăng nhập, ảnh chỉ có nền đen.

**CHƯA test:** ① banner **thành công chưa từng do POST thật sinh ra** (mới mô phỏng markup — phần màu/CSS đã kiểm, phần `thechat_upgrade()` trả về chưa đi hết route); ② **chưa xem trên điện thoại thật**; ③ **q4 và q5 trên ảnh tĩnh rất dễ nhầm** (chỉ hơn nhau chỗ quét ngang), khác biệt thật nằm ở chuyển động; ④ **q2/q3/q4/q6/q7 chưa chụp ở khổ mobile** (mới 3 bậc); ⑤ production vẫn **chưa chạy** `sql/sql_thechat_body.sql` (nợ cũ §10).

## Nhục Thân v12: "bổ sung hiệu ứng đi" — 6 bậc hiệu ứng `nb-fx0…fx5`, KHÔNG đổi một px hình học (09/10/2026)

**Chủ dự án** (kèm ảnh 1920 chụp tài khoản **thật** của anh `tttttt`, ID 548, **Phàm Cốt · trần 10**, 6 bộ phận cấp 0): "ok đẹp rồi bổ sung hiệu ứng đi".

**Ảnh anh gửi chính là ràng buộc kỹ thuật, không phải minh hoạ.** Tài khoản của anh ở **bậc thấp nhất**, nên nếu fx0 = "không hiệu ứng" thì tính năng anh vừa duyệt **vô hình trên máy anh**. Vì vậy thang được dịch lại một nấc so với khuôn cũ: **fx0 = gradient tĩnh nhạt** (có quầng, có halo, tuyệt đối không động) → fx1 mới là nhịp thở 10s.

**Khuôn đã chốt từ trước** (memory *hiệu ứng phân tầng theo bậc*, từ vựng bậc lấy ở `tongquan.php:740-774`): không hiệu ứng → gradient tĩnh nhạt → sweep 8s → 7/5.5/4.5/3.6s → **rainbow 2.8s + glow** bậc chót; rainbow đặt trên **chữ** (anh đã bác phương án chuyển cầu vồng sang **viền**); `prefers-reduced-motion` phải tắt hết.

**Bốn biến, một class, không hard-code màu nào trong CSS.** PHP chỉ sinh `nb-fx<N>` trên `.nb-wrap` (`game/thechat.php:140`) + `--nb-tier` = màu bậc hiện tại; mỗi tab rail mang `--tcolor` = **màu của chính tab đó** (`thechat_awaken_color($nbT)`) và `nb-t0…nb-t5`; mỗi ngọc mang `--ring` theo chỉ số của bộ phận đó và `--glow = round(min(1, node_lv/lv_cap) * 16)` — **độ mạnh của ngọc tính từ cấp của chính nó**, không phải từ bậc nhân vật. `$nbGglow = 0` khi `lv_cap = 0` nên tài khoản trắng không chia 0.

**7 thành phần được hiệu ứng hoá:** `.nb-aura` (quầng phủ toàn thân) · `.nb-sweep` · `.nb-title` · `.nb-badge` · `.nb-tier.is-cur` + icon/tên tab · `.nb-gem.is-max` + nhãn · `.nb-ms-fill:after` · `.nb-btn-refine/.nb-btn-break:after`.

**Đường đã thử và BÁC: vầng sáng dưới bệ sen.** Bản đầu đặt ellipse `filter:blur` ở `bottom:2%` để mô phỏng bệ sen phát sáng của LKCT — **không bao giờ thấy**, vì `nhuc-than.jpg` đã có sẵn đốm sáng trắng/cyan đúng chỗ đó, lớp mới bị nền nuốt. Ghép crop `.nb-stage` ba bậc là thấy ngay (`crop_fx_aura_stack.png`). Thay bằng `radial-gradient` tâm thân hình + `inset box-shadow` bốn viền, và **hạ** alpha thang xuống `.04→.22` vì blend `screen` cộng sáng mạnh hơn dự tính.

**Bốn MỒI mới (đã ghi §12.4):** ① `mix-blend-mode:screen` bắt buộc `.nb-stage{isolation:isolate}`, thiếu thì ánh sáng chảy ra ngoài khung ảnh; ② **fx0 không được đóng bằng `--nb-dur:0s`** — Chrome vẫn giữ keyframe 0% nên `nbAuraPulse` vẫn nhấp nháy, phải `animation:none` tường minh + `.nb-sweep{display:none}`; ③ chữ cầu vồng cần **3 thứ đi cùng nhau**: shorthand `background:` đặt TRƯỚC `-webkit-background-clip:text` (shorthand reset `background-clip`, chính cái bug làm chữ ẩn trước đây), `color:transparent`, và **emoji phải rời khỏi phần tử clip** (🐉/🌑 bọc `<i class="nb-badge-ico">` mang `-webkit-text-fill-color` riêng — nếu không glyph biến mất); trên rail thì tên bậc nằm trong phần tử clip nên `.nb-t5 .nb-tier-name` phải `text-shadow:none`; ④ toàn bộ hiệu ứng chỉ dùng `box-shadow`/`text-shadow`/pseudo `absolute`/`opacity`/`transform` — **không** `padding`/`margin`/`width`, vì v11 đã chốt số đo và ảnh đang bị cắt dọc (§3.8).

**Nghiệm thu bằng ảnh, không bằng số:** `wrap` = **840** @1337/1920 · **956** @601 · **1047** @760 · **1119** @390 — **khớp v11 từng px**, 6 toạ độ ngọc không đổi. fx0 có hiệu ứng thật (quầng tĩnh + halo badge, sweep/rail/flow tắt). Cầu vồng **chạy** thật: chụp 3 pha bằng `--virtual-time-budget` 11.0/12.4/13.8s → vàng → magenta/tím → vàng (`crop_fx_rainbow_phases.png`), 🐉 vẫn hiện (`ico fill=rgb(255,243,176)`). `--force-prefers-reduced-motion`: aura `nm=none` (giữ wash tĩnh), sweep `disp=none`.

**Đã kiểm:** `php -l` sạch (thechat.php, function/thechat.php) · `nb_sweep.php` **14/14 ALL CLEAN** · khối `.nb-*` cân **212/212** ngoặc, **0 non-ASCII** · `mystyle.css?v=2026100928` đủ 3 file · web root đã xoá `_nbfx.html`/`_nbshot.html` · tài khoản E2E đã trả về trạng thái hero (stage 1, 6 bộ phận 12/9/14/6/8/11; **đã undo** `node1=20` của phép thử đụng trần). Ảnh: `shot_fx_s0/s2/s5_d1337.png`, `crop_fx_aura_stack.png`, `crop_fx_rainbow_phases.png`, `crop_fx_s0_vs_s1.png`, `crop_fx_mobile_390.png` — đã mở từng ảnh lên **nhìn**.

**CHƯA test:** ① **mượt hay không chưa ai xem mắt thường** — headless chỉ chứng minh các pha khác nhau, không chứng minh 60fps trên máy anh ấy; ② **`mix-blend-mode:screen` + `isolation` trên WebView điện thoại thật CHƯA kiểm** — nếu WebView không hỗ trợ blend, lớp wash sẽ hiện thành **mảng đặc** thay vì ánh sáng; ③ **fx0 vs fx1 trên ảnh tĩnh gần như không phân biệt được** (opacity .04 vs .06, halo 4 vs 7px) — hai bậc này chỉ khác nhau khi xem chuyển động; ④ **fx3/fx4 chưa chụp ảnh** (mới fx0/fx1/fx2/fx5), chỉ nội suy từ bảng biến; ⑤ ngọc đang chọn (`.nb-gem.is-sel`) vẫn dùng `nbBreath` 2.4s **cố định** không theo bậc — chủ ý để trạng thái chọn luôn nổi, muốn phân tầng thì đổi thành `var(--nb-dur)`.

## Nhục Thân v11: "vẫn bị dài 1 chút" — cắt dọc ảnh ở desktop, −109px, hai màn giờ NGẮN BẰNG NHAU (09/10/2026)

**Chủ dự án** (kèm 2 ảnh 1920 của tài khoản thật `tttttt`, ID 548, Phàm Cốt · trần 10, 6 bộ phận cấp 0): "vẫn bị dài 1 chút".

**Kết quả:** `wrap` desktop 957 → **840**, Tu Luyện là **846** — hai màn giờ bằng nhau tới từng px, và toàn bộ trang (kể cả bar + đồng hồ) nằm gọn trong cửa sổ 1080px không phải cuộn. Mobile 390px giữ nguyên 1119 (mọi thứ nằm trong `@media(min-width:601px)`).

**v10 kết luận "ảnh không cắt dọc được" là SAI một nửa — và nửa sai chính là nút thắt.** Toàn bộ phép tính của v10 lấy ở khổ **350px**, nơi đĩa ngọc cố định 46px chiếm tới **87px ảnh**. Ở desktop `scale = W/663 ≥ .85` nên cùng ràng buộc chỉ còn **54px**, và chỉ cần **đảo nhãn ngọc Thần lên trên đĩa ngọc** (`.nb-gem.slot-bottom .nb-gem-tag{bottom:100%}`) thì phía dưới chỉ còn đòi bán kính 23px → trần cắt đáy nhảy từ ~46px lên **68px**. Cắt được **45px đỉnh + 45px đáy**.

**Cách cắt: không đụng file ảnh.** `.nb-stage{aspect-ratio:663/377}` — với `object-fit:cover` + `object-position:50% 50%`, browser tự pha đúng 90px ảnh chia đều hai đầu. Thân hình vẫn **full chiều ngang card**, JPEG gốc còn nguyên. **6 toạ độ ngọc phải tính lại** theo `% = (dòng ảnh − 45)/377`: Da 21→**15** (14.1 là đúng tâm, nới lên 15 để nhãn không bị `overflow:hidden` cắt ở khổ 601), Thịt/Gân cốt 41→**38.9**, Tủy/Máu 69→**73.5**, Thần 80→**87.2**. Đã nhìn ảnh ở 1920/601/390: 6 ngọc vẫn đúng giải phẫu.

**Bị ăn mất gì:** 45px đỉnh = chóp vòng pháp trận (vòng này đã tràn khung hai bên sẵn), 45px đáy = nền tối dưới mép trước bệ sen — **bệ vẫn còn**, thân không lơ lửng.

**Đã thử và BÁC một đường trước đó:** `max-width:520px` + một `<img>` cùng file `blur(15px) brightness(.62)` phủ hai lề. Số đo còn đẹp hơn (850px) nhưng nhìn ảnh thì ra **hai dải xám đục hai bên bức hình sắc** — về bản chất vẫn là "lề nền tối" anh đã bác, chỉ khác màu. Bằng chứng giữ ở `crop_v11_rejected_backdrop_1920.png`, markup đã xoá sạch.

**Hai bài học đo, không phải bài học CSS:**
1. **Bước 1 chỉ ăn 8px.** `.nb-reqs` (danh sách điều kiện đột phá) thành grid 2 cột + siết dòng 19/18px làm `card2` 192 → 173, nhưng `wrap` chỉ 957 → 949. `_nbd.html` trả lời tại sao: `.nb-cols` là **grid 2 cột** và cột TRÁI (`h3` 22 + `attrs` 60 + `ms` 75) mới là cột binding — cắt một cột khi hai cột bằng nhau = không cắt gì. **Trước khi cắt chiều cao trong grid 2 cột, đo cả hai cột.**
2. **Quên đóng một `<div>`** khi thêm band ảnh nền → `.nb-forge` chui vào trong band và bị ảnh nền phủ mất: **dải Luyện Hóa biến khỏi trang**, số đo vẫn đẹp. Chỉ ảnh chụp mới bắt được.

**Thêm một chữ:** ở trạng thái đầy 6 điều kiện (seed `stage=4`), `[Thiếu] Đế Lệnh: 0 / 8` vỡ 2 dòng trong cột 135px → `.nb-req{font-size:12.5px}` ở ≥601, về 1 dòng, không đổi chiều cao.

**Số đo:** 390 = 1119 (không đổi) · 601 = 1111 → **956** · 760 = 1223 → **1047** · 1337/1920 = 957 → **840**. `card1` 663 → **573**, `card2` 200 → **173**, `bar` 80 (bằng `.xl-tabbar` từng px).

**Đã kiểm:** `php -l` sạch · `nb_sweep.php` **14/14 clean** · khối `.nb-*` cân ngoặc, 0 non-ASCII · `mystyle.css?v=2026100927` đủ 3 file · `scrollW == vw` cả 5 khổ (không tràn ngang) · ghép 1:1 cạnh Tu Luyện và **nhìn** (`crop_v11_vs_xiulian.png`) · web root sạch harness. Ảnh: `shot_v11_desktop_1920.png`, `shot_v11_desktop_601.png`, `shot_v11_mobile_390.png`, `shot_v11_desktop_stage4.png`, `crop_v11_card2_stage4.png`.

**CHƯA test:** chưa có điện thoại thật để chạm kiểm tra 6 ngọc trên ảnh đã cắt · khổ **601–700px nhãn ngọc Da chỉ còn ~2px hở đỉnh** — đổi cỡ nhãn/đĩa ngọc là phải đo lại §3.8 · `nb-capnote` (bộ phận chạm trần) ở ≥601 vẫn chưa chụp được · **mobile vẫn 1119px**, nếu anh ấy kêu dài trên điện thoại thì phải đi đường khác (đổi tỉ lệ ảnh hoặc bỏ nhãn chữ khỏi box), không phải siết padding.

## Nhục Thân v10: "hơi bị dài 1 chút" — cắt 62px chiều cao, nhưng KHÔNG cắt ảnh (09/10/2026)

**Chủ dự án** (kèm 2 ảnh 1920×1080, đầu trang + cuối trang): "đẹp luôn rồi nhưng mà lại hơi bị dài 1 chút"

**Đi đường vòng trước khi đúng.** Bản đầu của v10 hạ `.nb-stage{max-width:84%}` → `wrap` 1051 → 949, nhưng ảnh lọt thỏm giữa card với **hai lề nền tối trái phải** — đúng cái anh đã bác từ bản nén ("nền ảnh phải full box", §6 ràng buộc 10). Đã bỏ, ảnh về `max-width:100%`.

**Vì sao không cắt dọc ảnh (đã tính đủ ba đường, ghi lại để lần sau đừng thử):** muốn ngắn 70px thì box phải thành 663/392 và `object-fit:cover` sẽ pha **74.8px** của ảnh. Ngọc `Da` ở `top:21%` có **nhãn nằm trên đỉnh đầu**, nên đỉnh chỉ pha được ≤16.3px (tính ở khổ 350px); 58.5px còn lại dồn xuống đáy trong khi bóng đen của thân hình kéo tới **dòng 466/467** (đo GD: `first dark row=83 last dark row=466`) → mất bệ sen. Crop luôn file JPEG thì cùng số học và còn mất gốc. **Ảnh 663×467 là không co được.**

**Nên chiều cao lấy từ 3 dải không phải ảnh**, tất cả trong `@media(min-width:601px)`:

| Chỉnh | Trước → sau |
|---|---|
| `.nb-forge` → grid 2 hàng (hàng 1 tên+dòng chỉ số `span 2`; hàng 2 hai chip **1 dòng** + nút) | dải 109 → 78 |
| `.nb-cost-item{white-space:nowrap;font-size:12.5px}` — chip "Linh Thạch 900,000/1,300" trước đây tự vỡ 2 dòng | chip 56 → 30 |
| `.nb-tier{padding:6→4}` + `.nb-tier-ico{18→16px}` | ô bậc 71 → 61 |
| `.nb-attr{padding:0;line-height:20}` (6 dòng) + `.nb-req{margin:1px;line-height:19}` (5 dòng) | `card2` 219 → 200 |
| `.nb-head{padding:10 14 8 → 8 14 6}` | |

**Không đụng `.nb-tabbar`** — phải giữ 80px để bằng chim `.xl-tabbar` của Tu Luyện (yêu cầu từ v7).

**Số đo chốt** (`_nbw.html`, `zoom=1`) — 390 **không đổi** vì mọi thứ nằm trong breakpoint desktop:

| khổ | v9 | **v10** | Tu Luyện |
|---|---|---|---|
| 390 | 1119 | **1119** | 1036 |
| 601 | 1216 | **1111** | 994 |
| 760 | 1306 | **1223** | 994 |
| 1337 / 1920 | 1051 | **957** | 846 |

`card1` 722 → **663**. Ở 760px tăng so với bản 84% là **đúng** — bỏ cap thì ảnh lại phủ hết cột.

**Bẫy mới gặp:** `.nb-capnote` là con trực tiếp của `.nb-forge`; rule cũ `.nb-capnote{flex:1 1 100%}` là **thuộc tính flex, vô tác dụng sau khi `.nb-forge` thành grid** → phải khai báo `grid-column:1/span 2;grid-row:3`.

**Đã kiểm:** `php -l game/thechat.php` sạch · `nb_sweep.php` **14/14 clean** · đuôi CSS cân ngoặc, 0 non-ASCII · `mystyle.css?v=2026100922` cả 3 file (`game.php`, `gm/gm.php`, `gm/player.php`) · `scrollW == vw` cả 4 khổ (chip nowrap không tràn ngang) · đã **nhìn** ảnh 1920×945 ghép cạnh Tu Luyện + ảnh 1337 + 601 + 390. Ảnh: `docs/img/thechat/crop_v10_trim_vs_xiulian.png`, `shot_v10_desktop_1920.png`, `shot_v10_desktop_1337.png`, `shot_v10_desktop_601.png`, `shot_v10_mobile_390.png`.

**CHƯA test:** (1) trạng thái `.nb-capnote` (bộ phận chạm trần 20 cấp) ở khổ 601 — chưa seed để chụp; (2) 957px vẫn dài hơn viewport 945px ~12px nên desktop còn cuộn một chút tới cái bar; (3) tất cả vẫn chỉ là Chrome headless, chưa có người thật xem trên điện thoại.

**Nút chỉnh nếu anh còn muốn ngắn nữa:** các dòng trong bảng ở `docs/UI_THECHAT.md` §3.7 — **không phải** `.nb-stage`.

## Nhục Thân v9: "chiều dài" là CHIỀU DỌC — ảnh phủ trọn card, trang dài hết màn như Tu Luyện (09/10/2026)

**Chủ dự án bác cách hiểu v8:** "tôi đang nói chiều dài mài" → anh ấy nói **chiều cao trang**, không phải bề rộng.

**Nhận ra bằng ảnh, không bằng đoán:** chụp cả hai màn đúng khổ trình duyệt thật (1920×945) rồi ghép cạnh nhau — Tu Luyện `wrap` **846px** chạm mép dưới, Nhục Thân **803px** dừng sớm hơn, và thủ phạm là ảnh thân hình chỉ cao 287px vì bị nhốt trong cột `69%`.

**Sửa** (`@media(min-width:601px)` của khối `.nb-*`): bỏ layout "ảnh 69% + cột luyện 31%" → **ảnh `width:100%` của card** (590×416 ở desktop, đúng brief gốc "ảnh phủ full khung"), `.nb-forge` đổi thành **1 dải ngang** `grid-template-columns:minmax(0,1fr) 190px` (trái: tên bộ phận + chỉ số + 2 chip chi phí; phải: nút Luyện Hóa 190px).

**Số đo sau sửa:** `wrap` 1337/1920 = **803 → 1051**, `card1` 474 → **722**; 760 = 1306; 601 = 1216. Đã nhìn ảnh: 6 ngọc vẫn đúng giải phẫu trên ảnh to, dải luyện ở 601px vẫn gọn 1 hàng (2 chip `288px` trong cột trái `321px`).

**Đánh đổi khai báo thẳng:** 1051px dài hơn viewport 945px ~106px → desktop phải cuộn chút mới tới `.nb-tabbar`. Muốn không cuộn thì chỉ cần cap `.nb-stage{max-width:…}` (ảnh ~434×306) nhưng chưa tự làm vì nó đi ngược đúng yêu cầu vừa nhận.

**Bẫy harness mới:** tài khoản E2E bị đá về màn Đăng nhập giữa hai lần chụp — `game.php:477` kick khi `game1.endtime` cũ hơn 3600s, và `nbseed2.php` seed `+4 HOUR` theo `NOW()` của MySQL (lệch PHP 1 giờ) nên thực còn 3 giờ. Mở khoá: `UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 6 HOUR) WHERE sid='e2e…e2'`. **Ảnh ra màn Đăng nhập mà không để ý là mọi số đo đều sai.**

**Đã kiểm:** `php -l` sạch · `nb_sweep.php` 14/14 clean · CSS đuôi 180 dòng cân ngoặc, 0 non-ASCII · `mystyle.css?v=2026100919` cả 3 file · web root sạch harness. Ảnh: `docs/img/thechat/crop_v9_length_vs_xiulian.png`, `shot_v9_desktop_1337.png`, `crop_v9_forge_601.png`.

**CHƯA test:** chưa xem trên laptop 1366×768 thật — ở đó lượng cuộn sẽ lớn hơn hẳn.

## Nhục Thân v8: content chiếm HẾT khung, khớp bề rộng với màn Tu Luyện (09/10/2026)

> ⚠️ **Đúng một phần:** phần bỏ `.nb-wrap{margin}` để content sát khung thì giữ nguyên (vẫn đúng,
> vẫn đang dùng). Nhưng "chiều dài" chủ dự án nói là **chiều dọc** — xem v9.

**Chủ dự án** (kèm ảnh desktop 1337px): "box so với chiều dài trang thì chưa hết tôi muốn cải thiện giao diện cho hết chiều dài trang như box bên tu luyện ấy. Vậy cho tối ưu hơn"

**Đo trước khi sửa** (harness mới `_nbw.html`, mở cả hai trang): panel `.tcg-main-panel` của hai trang **bằng nhau** (660px) — vấn đề không nằm ở panel. Phần content thì hơn nhau một dải nhìn thấy được:

| viewport | `.nb-wrap` | `.xl-page` | lệch |
|---|---|---|---|
| 390 | 334 | 350 | −16 |
| 760 | 696 | 720 | −24 |
| 1337 | 598 | 622 | −24 |

**Nguyên nhân:** `.nb-wrap{margin:12px}` (kèm bản `margin:8px` ở ≤430px) tự thụt vào trong padding của panel, trong khi `.xl-page{margin:0 auto}` bám sát padding. Mọi card + bar của Nhục Thân vì thế luôn hẹp hơn Tu Luyện đúng 24px.

**Sửa 3 điểm, không đụng rule chung:**
1. `.nb-wrap` → `margin:0`, **xoá** selector trùng trong `@media(max-width:430px)` (theo bài học "sửa hết selector trùng").
2. `.nb-stage` bỏ cap `max-width:420px` → `100%!important` — cap cũ để lại hai dải đen 270px ở khổ 760px, trái brief gốc "ảnh phủ full khung".
3. Breakpoint layout 2 cột `@media(min-width:861px)` → **`min-width:601px`**. Thiếu bước này thì ở 760px ảnh thành 688×486 nằm một mình: `wrap` **1135 → 1345** (tôi đã gặp và đã bắt buộc phải có bước 3). Có bước 3: **1030**, thấp hơn cả bản trước khi sửa.

**Đo lại:** `content` = **350 / 720 / 622** ở 390 / 760 / 1337 — **khớp tuyệt đối** với `.xl-page`. Chiều cao `wrap`: 390px 1107→1119, 760px 1135→**1030**, 1337px 767→803 (Tu Luyện 846). Ảnh thân hình desktop 380→408px, `card1` 440→474.

**Đã kiểm:** `php -l game/thechat.php` sạch · `nb_sweep.php` **14/14 clean** · CSS đuôi 174 dòng cân ngoặc (0), 0 ký tự non-ASCII · `grep` không còn `margin:12px`/`420px` trong khối `.nb-*` · web root sạch harness. Ảnh: `docs/img/thechat/crop_v8_content_vs_xiulian.png` (ghép 1:1 cạnh Tu Luyện), `shot_v8_desktop_1337.png`, `shot_v8_mobile_390.png`.

**Không làm (chờ chủ dự án):** không nới `.tcg-main-panel` khỏi 660px — v13 từng bị bác vì nới 660→800. Nếu muốn hộp rộng hơn cả khung, tiền lệ có sẵn ở `css/mystyle.css:1751` (`.tcg-main-panel:has(.ydinfo-page){width:min(760px,…)}`), áp cùng kiểu cho `.nb-wrap`.

**CHƯA test:** chỉ Chrome headless. Dải 481–600px giờ ảnh full-bleed và trang dài hơn ~110px so với bản cũ — chưa xem trên thiết bị thật.

## Nhục Thân v7: bar `.nb-tabbar` sửa lại cho KHỚP 1:1 bar Tu Luyện (v6 bị bác) (09/10/2026)

**Chủ dự án:** "cần sửa lại cho bar dài như bar gốc bên tu luyện còn nút Nhân vật cũng bằng các nút như bên tu luyện thôi chứ không phải dài vậy" + nhắc **"sao không xem lại source css gốc mà kiểm tra cho dễ"**

**Hai lỗi của bản v6** (chính chủ chỉ ra bằng ảnh chụp cạnh nhau): bar bị `max-width:220px` bó hẹp ở desktop nhưng lại giãn full ở mobile (vì `!important` của panel nuốt), và pill "Nhân Vật" **dài hết bar** thay vì bằng một ô Tu Luyện.

**Nguyên nhân gốc — và là bài học quy trình:** tôi chỉ đọc **7 dòng chính** của `.xl-tabbar` (dòng 824-830) rồi tự chế, **không grep hết class**. Source thật còn 2 override responsive:
- `css/mystyle.css:856-859` trong `@media(max-width:760px)`: `gap:4px` · `padding:6px 5px` · `a{min-height:52px;padding:6px 2px 5px}` · `i{22px/18px}` · `b{font-size:10px}`
- `css/mystyle.css:876` trong `@media(max-width:430px)`: `b{font-size:9.5px;letter-spacing:0;`**`white-space:nowrap`**`}`

Thiếu `nowrap` nên v6 vỡ dòng: "Nhân Vật" **3 dòng** ở 390px (bar lồi 102px, `pageH` 1111→1135) và "Nâng Phẩm Chất" 3 dòng ở 1337px.

**Sửa:** `.nb-tabbar` bỏ grid + bỏ `max-width` + bỏ luôn class `.has-up` (không còn cần) → **flex căn giữa**, full chiều ngang panel; mỗi ô `flex:0 1 auto; min-width:calc((100% - 30px)/6)` (≤760px: `- 20px` vì `gap` giảm còn 4px) — **đúng bề rộng một ô** của grid `repeat(6,minmax(0,1fr))` bên Tu Luyện, nhưng vẫn tự nong ra khi nhãn dài thay vì vỡ dòng; `max-width:100%` chặn nhãn quá dài. Copy nguyên cả 2 cụm override vào `@media(max-width:760px)` và `@media(max-width:430px)` mới của khối `.nb-*`.

**Số đo — lần này so trực tiếp với trang Tu Luyện thật bằng harness mới `_nbb.html`, không so với trí nhớ:**

| Ngang | ô `.nb-tabbar` | ô `.xl-tabbar` | bar nb | bar xl | dòng nhãn |
|---|---|---|---|---|---|
| 390 | 50×60 | 53×62 | 334×74 | 350×76 | 1/1 |
| 480 | 64×60 | 68×62 | 416×74 | 440×76 | 1/1 |
| 760 | 111×60 | 115×62 | 696×74 | 720×76 | 1/1 |
| 1337 | 92×64 | 96×64 | 598×80 | 622×80 | 1/1 |

Lệch 2–4px còn lại **không phải tại bar**: `.nb-wrap` hẹp hơn container của xl đúng 16px (`.nb-wrap{margin:8px}` ở ≤430px). 2 nút ở 390px: "Nâng Phẩm Chất" `84×60` + "Nhân Vật" `50×60`, bar **vẫn 74px** không lồi. `pageH` 390px về lại **1111** (bằng v5), 1337px **767**.

**Hai harness mới + một cái bẫy:** `_nbb.html` (đo bar, in `lines=` và cờ `!OVER`, chạy được cho cả 2 trang), `_nbp.html` (trả `barTop`/`barH`/`docH` để crop đúng chỗ). `_nbshot.html` **chưa reset `zoom`** → desktop render ở 0.75 nên crop theo số đo thật ra nền trống, **đã mất 2 vòng chụp**; nay harness tự đặt `zoom=1`. Cũng nhờ `_nbb.html` mà phát hiện `.xl-tabbar` **của trang Tu Luyện** bị `!OVER` 1px ở nhãn "Tông Môn"/"Nhiệm Vụ" tại 390px — bug ngoài phạm vi, **chưa sửa**, ghi ở §3.4.

**Đã test:** `php -l game/thechat.php` sạch · `nb_sweep.php` **14/14** URL state → 0 Warning/Notice/Fatal · khối `.nb-*` (6978→7148) cân **134/134** ngoặc, **0 non-ASCII** · `grep` toàn repo (trừ `bak/`) **0** kết quả `.nb-foot`/`.nb-btn-ghost`/`has-up` · trạng thái 2 nút seed `thechat_id=1` rồi **trả về 0** · bump `mystyle.css?v=2026100917` cả 3 file. Ảnh: `docs/img/thechat/shot_v7_{mobile_390,desktop_1337}.png` + **`crop_v7_bar_vs_xiulian.png`** (bar Nhục Thân đặt ngay trên bar Tu Luyện, 2 khổ) + `crop_v7_bar_2btn_mobile.png` — đã mở từng ảnh lên nhìn.

**CHƯA test:** chưa bấm thật trên **điện thoại thật**; bar vẫn là **1–2 nút** (đúng chữ "đơn lẻ 1 nút"), chưa phải 6 tab như Tu Luyện. Nợ production không đổi.

---

## Nhục Thân v6: nút "← Nhân Vật" thành bar dưới cùng giống màn Tu Luyện (09/10/2026)

> ⚠️ **Đã bị bác một phần ở v7:** bar bị bó `max-width:220px` và pill kéo dài hết bar, không khớp cỡ ô
> của `.xl-tabbar`. Toàn bộ phần CSS của mục này đã được thay bằng v7 — giữ lại để hiểu vì sao không
> được chỉ đọc vài dòng đầu của khối CSS định clone.

**Chủ dự án:** "Cái quay về nhân vật ấy bỏ sung bằng cái bar dưới cùng bên Tu Luyện xong để đơn lẻ 1 nút Nhân vật cho đẹp" (kèm ảnh màn Nhục Thân + ảnh bar 6 nút của màn Tu Luyện)

- **`game/thechat.php`** — xoá `.nb-foot`; thay bằng **`<nav class="nb-tabbar">`** đúng cấu trúc `.xl-tabbar` (`game/xiulian.php:418`): mỗi nút là `<a><i>emoji</i><b>nhãn</b></a>`. **1 nút hay 2 nút do dữ liệu quyết định**: `$nbPhysiqueId > 0` (đã có Thể Chất) → thêm pill "🔼 Nâng Phẩm Chất" + `.has-up`; chưa có → chỉ còn pill "👤 Nhân Vật" `.on`, đúng nghĩa "đơn lẻ 1 nút".
- **`css/mystyle.css`** — xoá **3 rule `.nb-btn-ghost`** + `.nb-foot`; thêm 8 rule `.nb-tabbar` (dòng 7104-7112) clone grid/pill/radial-vàng của `.xl-tabbar` (dòng 824-830). Khai báo **đủ mọi state** `a, a:link, a:visited, a:hover, a:active, a:focus` trong cùng một selector nhóm, nếu thiếu là `gamecss.css:95` sơn `#0093e8` lên pill khi chạm.
- **Không tự chế kiểu nút mới** — mục tiêu yêu cầu là hai màn cùng một ngôn ngữ, nên bar ở đây khớp `.xl-tabbar` từng thông số (`min-height:58`, ô emoji 24px, bo góc 12px, `.on` = radial `rgba(120,74,14,.55)` + `#ffd77a`).
- **Giá phải trả: +28px chiều cao** (bar 80px vs `.nb-foot` cũ 52px) → `wrap` 390px **1083 → 1111**, 1337px **739 → 767**. Không rút ngắn để đổi lấy chiều cao. Bù lại **1 nút và 2 nút cao bằng nhau** (đều 1111/767), nên thêm nút "Nâng Phẩm Chất" không làm dài trang.
- **`max-width:220px` chỉ sống ở ≥761px.** Ở ≤760px `.tcg-main-panel *{max-width:100%!important}` (mồi §6.3) nuốt mất → bar giãn hết hàng (334px ở 390px). **Chấp nhận, đã nhìn ảnh**: bar mobile đầy ngang như Tu Luyện, desktop là chip 220px cân giữa; không chống lại `!important`.
- **Đo được từng con số** (harness mới `_nbt.html`: grid, ô, glyph 👤 đo bằng `Range`, cờ `!OFF`/`!CLIP`, `legacy=`): 4 phép đo (1 nút / 2 nút × 390 / 1337) → glyph **luôn giữa** (`off=143/143`, `86/86`, `61/61`, `127/127`), `legacy=gone` tức DOM không còn `.nb-foot`/`.nb-btn-ghost`; `grep` toàn repo (trừ `bak/`) **0 kết quả**.
- **Đã test:** `php -l game/thechat.php` sạch; `nb_sweep.php` **14/14 trạng thái URL** → 0 Warning / 0 Notice / 0 Fatal, mỗi trang 6 tab + 5 `.nb-link`; khối `.nb-*` (dòng 6978→7141) cân **129/129** ngoặc, **0 non-ASCII**; emoji astral còn nguyên (`\U0001f53c`, `\U0001f464`); trạng thái 2 nút tạo bằng cách seed tạm `game1.thechat_id = 1` cho tài khoản E2E rồi **trả về 0 ngay sau khi chụp**. Bump `mystyle.css?v=2026100916` ở **cả 3** file. Ảnh: `docs/img/thechat/shot_v6_{mobile_390,desktop_1337}.png` + `crop_v6_bar_{1btn_mobile,2btn_mobile,2btn_desktop}.png` (đã mở từng ảnh lên nhìn).
- **CHƯA test:** ① chưa bấm thật hai pill trên **điện thoại thật** (mới headless 390px); ② chủ dự án nói "bar dưới cùng bên Tu Luyện" — bản này hiểu đúng chữ "đơn lẻ 1 nút" nên chỉ có **1–2 nút**, **không** bê nguyên 6 tab (Tu Luyện/Nhân Vật/Bản Đồ/Tông Môn/Nhiệm Vụ/Cửa Hàng) sang; muốn 6 tab là sửa 6 dòng markup. Nợ production (migration `sql/sql_thechat_body.sql` + xác nhận bảng `player_thechat_awaken_log`) **không đổi** so với v4/v5.

---

## Nhục Thân v5: dãy Cốt Cách Phàm→Tiên thành một chuỗi liên kết (09/10/2026)

**Chủ dự án:** "Đẹp đấy nhưng dãy phẩm cấp từ Phàm Cốt đến Tiên Cốt tôi muốn nó liên kết với nhau"

- **`game/thechat.php`** — vòng lặp rail sinh thêm **5 `<span class="nb-link" aria-hidden="true">`** chen giữa 6 tab, `is-lit` khi `$nbT <= $nbStage` (link số k nối bậc k-1→k nên sáng khi bậc k-1 đã đạt: stage 0 → 0/5 sáng, stage 1 → 1/5, stage 5 → 5/5).
- **`css/mystyle.css`** — `.nb-rail{gap:6px → 0}`; `.nb-link{flex:0 0 14px;align-self:center;height:3px}` + mũi tên `::after` (border triangle); `is-lit` = gradient `#6ee7b7 → #f5d18a` **đúng cặp màu** "Đã đạt"/"đang chọn" của tab. **Dùng `<span>` chứ không phải `<a>`**: nếu là `<a>` thì connector ăn `a{padding:1px 10px}` + `a:hover{background:#0093e8}` toàn cục (`gamecss.css:85/95`) và thành ô bấm vô nghĩa.
- **Breakpoint rail hạ từ ≤430px xuống ≤560px** (tab cố định 76px + quét ngang), và link rút còn 10px ở ≤860px. **Lý do thật:** bản đầu chỉ để ≤430px quét → ở 480px 6 tab flex còn **52px** nên tên bậc **rơi 2 dòng**, `wrap` 390→480 tăng từ 1145 lên **1161**. Sau khi hạ breakpoint: 480 = **1128**, 600 = **1111**, 390 = **1083**, 1337 = **739** → **không tốn thêm chiều cao ở 2 kích thước chính**.
- **Đo được từng con số** (`_nbr.html` + `_nbm.html`, luôn kèm `--hide-scrollbars` — thiếu flag là rail cuộn sinh scrollbar 15px làm `wrap` ở 390 đọc nhầm thành 1098): 390/480/600/700/861/1337 → **không tab nào `!WRAP` / `!NARROW`**, `nameH=17` (1 dòng) mọi cỡ, tab desktop 75px, rail `sw==cw` ở 600/700/861/1337 (không phải quét), chỉ 390/480/560 quét.
- **Đã test:** `php -l` sạch; `nb_sweep.php` **14/14 trạng thái URL** (node 1-6, view 0-5, trống, rác `node=99&view=42`) → **0 Warning / 0 Notice / 0 Fatal**, mỗi trang đúng **6 tab + 5 `.nb-link`**, verify round-trip `decode(encode())` đủ 14; chụp rail ở **stage 5 / 0 / 3** bằng `nbstage.php` để chứng minh logic sáng-tắt (không phải chỉ stage 1); khối `.nb-*` cân ngoặc, **0 non-ASCII**. Bump `mystyle.css?v=2026100915` ở **cả 3** file. Ảnh: `docs/img/thechat/shot_v5_{desktop_1337,mobile_390}.png` + `crop_v5_rail_{3stage,mobile}.png`. **Tài khoản E2E đã trả về trạng thái hero** (stage 1) sau khi chụp xong.
- **CHƯA test:** rail vẫn mới chỉ có headless — chưa ai **quẹt thật** trên điện thoại để biết 5 connector có làm điểm chạm giữa 2 tab gây nhầm không. Nợ production (migration + `player_thechat_awaken_log`) **không đổi** so với v4.

---

## Nhục Thân v4: nén 6 card còn 2 card + ảnh thân hình full-box đúng giải phẫu (09/10/2026)

**Chủ dự án:** "Giao diện đẹp đấy nhưng có 2 vấn đề 1. Giao diện Chưa được tối ưu và quá dài 2. Như phần 6 vị trí trên cơ thể cái nền ảnh phải full box chứ (hoặc thiết kế cho tối ưu hơn)"

**Làm gì:** gộp 6 card → **2 card / 3 dải**: dải 1 = `.nb-head` (title + badge + rail Cốt Cách + dòng phẩm chất gộp inline) rồi `.nb-forge-wrap` = **ảnh thân hình full-box bên trái + lò luyện bên phải** (desktop ≥861px là 2 cột, dưới đó về 1 cột); dải 2 = `.nb-cols` **Thuộc Tính | Đột Phá** 2 cột. Nhãn rút gọn: `Trần mỗi bộ phận: X cấp` → `trần X/bộ phận`, `.nb-phys` tách thành `.nb-viewline` riêng.

**Ảnh full-box + ngọc đúng cơ thể:** `.nb-stage{aspect-ratio:663/467}` (fallback `height:268px`) + `overflow:hidden`, `.nb-figure{position:absolute;inset:0;width:100%;height:100%!important;object-fit:cover}`. Toạ độ 6 ngọc = **% tâm đo từ ảnh thật**, không đoán: `top 50,21 · left-up 34,41 · right-up 66,41 · left-down 25,69 · right-down 75,69 · bottom 50,80`; ngọc đầu đảo nhãn lên trên (`bottom:100%`) vì sát mép.

**Bốn cái bẫy CSS phải nhớ (mồi, đã vào §6 UI_THECHAT):**
1. `padding-bottom:70.44%` **sai hoàn toàn** — padding % resolve theo bề rộng containing block chứ không theo `flex-basis` của chính nó → desktop ra `380×420` (dọc), vỡ vị trí ngọc. Phải dùng `aspect-ratio`.
2. **`<a>` toàn trang có padding/margin**: `css/gamecss.css:85` `a{padding:1px 10px;margin-right:3px}` + `@media(max-width:480px)` dòng 226 `a{padding:5px 4px;margin:5px 2px}` → ngọc **lệch +2%** và đĩa đo được 26/36px thay vì 46/44. Phải `.nb-gem{padding:0;margin:0;border:0;border-radius:0}` và khai báo `background:none` cho **mọi** state `:link/:visited/:hover/:active/:focus` (dòng 95 `a:hover,a:active,a:focus{background:#0093e8}` sơn xanh lên ngọc/tab khi chạm). Nút cũng phải khai báo `:hover/:active/:focus` riêng từng nền.
3. `css/mystyle.css:5035-5040` `.tcg-main-panel *{max-width:100%!important}` (≤760px) **nuốt** `max-width` một lớp → tablet 600px ra `534×376`; và `.tcg-main-panel img{height:auto!important}` co ảnh. Sửa bằng `max-width:420px!important` (desktop `380px!important`) + `height:100%!important`.
4. Trong `@media(max-width:430px)`, `.nb-card{padding:12px}` đặt **sau** `.nb-card-body{padding:0}` làm card 1 bị đệm 12px → `stage` 308px thay vì 332px, ngọc dồn giữa. Biến thể phải đặt sau rule chung.

**Số đo (harness `_nbm.html` đo qua `--dump-dom`, zoom=1):** `wrap` mobile 390px **1205 → 1083**, desktop 1337px **957 → 739**; tablet 600px 1111; desktop card1 598×440 + card2 598×231. **Cả 4 viewport (390/480/600/1337) `stage` ra tỉ lệ đúng 1.420 = 663/467, `crop=0`, 6/6 ngọc khớp % declared, không cờ `!BOX`/`!TAG`.**

**Đã test:** `php -l` sạch; khối `.nb-*` cân ngoặc + **0 non-ASCII**; **14 trạng thái URL** (`node=1..6`, `view=0..5`, không tham số, rác `node=99&view=42`) × **2 seed (hero / trắng)** → **0 Warning / 0 Notice / 0 Fatal**, `.nb-stage` có mặt ở mọi trang; **trạng thái đụng trần** (node 1 = 20/20): nhãn `.is-max` vàng, art `-day`, `.nb-capnote` thay chip, nút disabled, `+90 Phòng Thủ` = 20×4×1.12; **POST E2E qua route thật** vẫn đủ: refine 6→7, refine 14→15, break bị chặn "Cần 80, đang có 62" không trừ gì, `nb_action` rác không phá trang, break thành công stage 1→2 + LT 900.000→800.000 + Thánh Thạch 5→0 + log 1→2 + refine tới trần mới; smoke hồi quy 6 trang khác vẫn render. Ảnh: `docs/img/thechat/shot_v4_{mobile_390,mobile_trong,tablet_600,desktop_1337}.png` (đã mở từng ảnh lên nhìn). Bump `mystyle.css?v=2026100914` ở **cả 3** file (`game.php:501`, `gm/gm.php:9`, `gm/player.php:9`).

**CHƯA test / nợ:** ① bản nén **chưa có người thật xem trên điện thoại** — mới có headless; ② production **vẫn chưa chạy** `sql/sql_thechat_body.sql` và **chưa xác nhận** có bảng `player_thechat_awaken_log`; ③ **không so 1:1 với ảnh mẫu được** (4 ảnh chủ dự án gửi đã mất); ④ harness `_nb.html`/`_nbm.html` **đã xoá** khỏi web root, cần đo lại thì copy từ `E:/window/Temp/`. `docs/UI_THECHAT.md` cập nhật §2/§3 (+§3.1 toạ độ ngọc, §3.2 số đo)/§6/§9/§10.

---

## Trang Thể Chất → NHỤC THÂN: dựng lại theo mẫu mobile LKCT + viết lại logic (09/10/2026)

**Chủ dự án:** "Tiếp tục dựa và tài nguyên source linh kiếm cửu thiên để làm tính năng Tu Luyện như hôm trước, hãy xây dựng lại tính năng Thể chất theo mẫu bên phải và cập nhập lại logic tương ứng" (kèm 4 ảnh: trang `thechat` desktop cũ + 3 ảnh mẫu LKCT Nhục Thân / Đan Điền / Kinh Mạch)

**Ba quyết định chốt qua AskUserQuestion:** (1) hàng tab trên cùng = **gộp Thức Tỉnh vào đây** — 6 bậc Cốt Cách, bỏ trang `thechat_awakening` riêng; (2) 6 viên ngọc = **6 bộ phận, 6 chỉ số khác nhau**; (3) Luyện hóa = **Tinh Thể Đá + Linh Thạch**, giữ nguyên 2 hệ cũ.

- **`class/config/thechat_body_config.json` (MỚI)** — 6 node (Bì Phủ→def +4 · Cơ Nhục→hp +20 · Cân Cốt→atk +3 · Cốt Tủy→crit +0.2 · Huyết Khí→lifesteal +0.5 · Thần Thức→exp +0.4, mỗi cái một ô quanh thân hình), 6 bậc Cốt Cách (Phàm trần 10 → Kim 20 → Ngọc 30 → Linh 40 → Kiếp 50 → Tiên 60), công thức luyện hóa `đá = 1 + floor(lv/5)`, `LT = 100 + lv*100`, 5 mốc tổng cấp (Thiết +5% / Cương +12% / Kim Cương +22% / Bất Hoại +35% / Luân Hồi +50%).
- **`sql/sql_thechat_body.sql` (MỚI)** — bảng `player_thechat_node (sid, node_id, node_lv, updated_at)`, MySQL 5.5 (không `DEFAULT CURRENT_TIMESTAMP`). **Local đã áp, production CHƯA.**
- **`class/function/thechat.php`** — khối `thechat_body_*` dòng 667→1006: `refine` / `break` / `bonuses` / `refine_cost` / `milestone` / `tier` / `levels_flush`. Cache đặt ở `$GLOBALS` (không `static`) để invalidatable trong request. Đột phá dùng transaction + `WHERE … >= ?` + `rowCount()` để không bao giờ trừ tài nguyên khi thất bại.
- **`game/thechat.php` VIẾT LẠI (303 dòng)** — 6 khối đúng mẫu: rail tab Cốt Cách → thân hình + 6 ngọc chạm được → card bộ phận (2 chip "có/cần" + nút Luyện Hóa) → Thuộc Tính Kích Hoạt + thanh mốc → check-list Đột Phá `[Đủ]/[Thiếu]` → foot. Tham số `node` / `view`, ưu tiên `POST > GET > mặc định`.
- **`css/mystyle.css`** — khối `.nb-*` dòng 6978+ để **sau mọi `@media`**. Bump `mystyle.css?v=2026100912` ở `game.php:501`, `gm/gm.php:9`, `gm/player.php:9`.
- **`images/thechat/`** — `nhuc-than.jpg` (663×467) + **18 ngọc tint theo từng chỉ số** (`ngoc-{stat}-{khoa,sang,day}`), không dùng 3 file chung.
- **`game/thechat_awakening.php`** → `bak/thechat_awakening.20261009_truoc_nhucthan.php`; **bỏ route** `thechat_awakening` khỏi `game.php` (repo 0 commit nên không xoá file).
- **`tongquan.php`** — thêm mục **"Nhục Thân (6 bộ phận + Cốt Cách)"** (`the-chat-nhuc-than`, 3 bảng 6+6+5 dòng); mục "Thệ chất - thăng phẩm & thức tỉnh" → **"Thệ chất - thăng phẩm"** (bỏ bảng thức tỉnh vì hệ đó đã gộp vào Cốt Cách).
- **Bug thật tìm ra khi test — Cốt Cách vô hình với nhân vật chưa có Thể Chất:** `thechat_info()` trả `false` khi `thechat_id = 0`, nên nhánh `$empty` của `thechat_calc_bonuses()` gán cứng `'awaken_stage' => 0` → sau đột phá, card vẫn đọc "Phàm Cốt" và rail luôn sáng bậc 0. Với chính sách "Khởi đầu nhân vật trắng" thì đây là **mọi nhân vật mới**. Sửa: `thechat_body_bonuses()` trả `'stage'` (dòng 834), nhánh `$empty` lấy `$body['stage']` (dòng 283), trang dùng `$nbBody['stage']`.
- **Bug harness — chuỗi `cmd=` bị cắt 1 ký tự** làm `decode()` ra rác → `$cmd` không set → `game.php` trả `refresh:1;url=index.php` body 3 byte, biểu hiện là "ảnh chụp giống hệt nhau / trang trắng" và bị nghi oan là cache. Từ nay **luôn** verify `decode(encode($q)) === $q` trong PHP, không tự gõ chuỗi encode.
- **Rail tràn ngay trên desktop:** để `.nb-tier{width:86px}` cứng thì tab thứ 6 bị cắt (đo `rail=564 < 588`). Sửa thành `flex:1 1 0;min-width:0;box-sizing:border-box` → `rail=564<564` khớp; mobile ≤430px mới dùng `width:76px` quét ngang. **Không nới khung 660px** (giữ đúng ràng buộc đã bác v13 của trang Tu Luyện).
- **Selector sai khi rail là `<a>`:** viết `.nb-tier.is-done a.nb-tier:link` không khớp phần tử nào; phải là `a.nb-tier.is-done:link, a.nb-tier.is-done:visited` (màu `#6ee7b7` đã đạt / `#f5d18a` đang chọn).
- **Ảnh trùng md5:** bản đầu `ngoc-sang.png` và `ngoc-day.png` **byte-identical** (`017f32d9…`) vì script tint không đổi gì → phải kiểm md5 từng cặp sau khi sinh ảnh.
- **Đã test:** `php -l` sạch cho `game/thechat.php`, `class/function/thechat.php`, `class/player.php`, `game/xiulian.php`, `game.php`, `tongquan.php`; khối `.nb-*` cân **78/78** ngoặc nhọn và **0 ký tự non-ASCII**; quét smart-quote/CJK trong 2 file PHP → sạch. Smoke logic: chi phí lv0/1/2 = 1 đá + 100/200/300 LT, chặn trần theo bậc, đột phá trừ đúng 20 đá + 20 000 LT và nâng trần 10→20, mốc % áp đúng. `getplayer()` truyền đủ 6 chỉ số (`zhuangtai.php` hiện Bạo kích 122.34, Hút máu 76). `tongquan.php` render đủ **25/25** thuật ngữ mới. Ảnh nghiệm thu: `docs/img/thechat/shot_nb_v1_{desktop,desktop_trong,mobile,mobile_trong}.png` + `crop_nb_v1_desktop.png` — đã **mở từng ảnh lên nhìn**, cả trạng thái hero (Lv.12/9/14/6/8/11, Kim Cốt, Cương Thân +12%, nút Đột Phá đúng disabled) lẫn trạng thái trắng (6 ngọc xám Lv.0, Phàm Cốt, trần 10).
- **Đã test qua POST thật trên route `cmd=thechat`** (không chỉ hàm nội bộ) — seed E2E rồi cURL form vào `game.php`: `refine` node 4 → "Cốt Tủy lên cấp 7" · node 3 → "Cân Cốt lên cấp 15" · `break` khi thiếu cấp → **"Tổng cấp Lục Bộ Phận chưa đủ. Cần 80, đang có 62"** và **không trừ tài nguyên** · `nb_action` lạ (`boom`) → không msg, trang vẫn render đủ 12.512 byte. Seed lại tổng 84 rồi `break` thành công: msg "Đột phá thành công! Cốt Cách đạt Ngọc Cốt, trần mỗi bộ phận là 30 cấp", `thechat_awaken_stage 1→2`, Linh Thạch **900.000→800.000** (trừ đúng 100.000), Thánh Thạch **5→0** (trừ đúng 5), **cấp 6 bộ phận giữ nguyên**, `player_thechat_awaken_log` ghi `1 → 2`, và `refine` sau đột phá chạy được tới trần mới ("Huyết Khí lên cấp 13"). Hai trang `tongquan.php` + `game/thechat.php` trả về **0 Warning / 0 Notice / 0 Undefined**.
- **CHƯA test:** ① **production chưa migration** (`player_thechat_node`), và `thechat_body_break()` còn ghi log vào `player_thechat_awaken_log` — **chưa xác nhận production có bảng này**, thiếu thì đột phá sẽ roll-back; ② **cảm ứng điện thoại thật** (mới có iframe 390px trên desktop); ③ **không so 1:1 với ảnh mẫu được** — 4 ảnh chủ dự án gửi không còn trên đĩa (đã quét `E:/window/Temp` + `%LOCALAPPDATA%/Temp`), bản này bám mô tả cấu trúc chứ không đo ảnh; ④ `passive_regen/resist/reduce` vẫn **chỉ hiển thị** ở `zhuangtai.php:514-521`, chưa nối vào chiến đấu.
- **Ba điểm CŨ phát hiện nhưng KHÔNG sửa** (ngoài phạm vi, cần chủ dự án quyết): `class/function/huyetmach.php:543-546` — `huyetmach_activate_transform()` dùng `$player->uxx` ở chỗ phải là `uhp` và `UPDATE game1 SET uxx = ?`, tức **biến thân ghi đè chỉ số Hút Máu bằng HP và không trừ HP**; `thechat_realm_scale()` đọc `$GLOBALS['player']->jingjieValue` chưa set ở lần gọi đầu trong `getplayer()`; `game.php:33` `$_SERVER['QUERY_STRING']` sinh Notice khi request không kèm query string (thấy trong `php_error.log`).
- **Code chết còn giữ:** `thechat_awaken()` (dòng 531) + `thechat_awakening_config.json` không còn route nào gọi, nhưng **vẫn ghi vào cột `thechat_awaken_stage`** là cột Nhục Thân đang dùng — nối lại route cũ sẽ phá bậc Cốt Cách.

---

## Trang Tu Luyện: hợp nhất bài học v5→v14 thành tài liệu tra nhanh (09/10/2026)

**Chủ dự án:** "ok rồi tổng hợp và lưu vào docs và memory đi. Tất cả những vấn đề sửa cần phải nhớ để lần sau không lặp lại và thao tác cho nhanh"

- `docs/UI_XIULIAN.md` thêm **§11 "Bảng tra nhanh: lỗi đã gặp → cách tránh"** — **36 dòng A1–E6**, mỗi dòng *triệu chứng → nguyên nhân thật → cách tránh một dòng*, chia 5 nhóm: A CSS/layout (`a:link` đè display, emoji BMP, `padding:0` làm icon chạm viền, chip auto-width, nowrap làm thẻ phình 323→411px, `<br/>` của state Viên Mãn…), B đo đạc/JS (trộn `getBoundingClientRect()` đã nhân zoom với `clientWidth` px CSS; `scrollWidth` báo 0 dù chữ nowrap tràn), C harness (sàn 500px của `--window-size`, `--force-prefers-reduced-motion` chặn load ảnh, kết quả đo ghi vào `left:-9999px` làm giãn tài liệu cha, `print()` tiếng Việt lỗi cp1252, `/tmp` của Git Bash), D dữ liệu/server (`endtime` lệch giờ, `jieduan` và `umaxexp` không phải cột, `istupo()` không bao giờ trả 8, repo 0 commit), **E quy trình/phạm vi** — 6 lỗi bị chủ dự án bác, đắt nhất.
- `docs/UI_XIULIAN.md` thêm **§9.1 "Playbook copy-paste"**: lệnh seed (`xlhelp.php link|idle|mid|full|run`, `xlset.php <ulv> <uexp>`, `xllv.php low|restore`), SQL tương đương + công thức `umaxexp`, mẫu harness iframe có `body.style.zoom = 1`, lệnh Chrome `--dump-dom`/`--screenshot`, và 4 kiểm tra bắt buộc sau khi sửa (`php -l`, đếm ngoặc nhọn, quét non-ASCII, bump `?v=` **cả 3 file**). Helper nằm ở `E:/window/Temp/` (ngoài repo) + `jj.json` (85 tên cảnh giới để sweep nhãn).
- `docs/UI_XIULIAN.md` thêm **Checklist 6 dòng cuối §11** = định nghĩa "xong" của trang này (seed đúng state vừa báo lỗi → chụp 1337px + 390px → **mở crop lên nhìn** → xoá harness → trả tài khoản → ghi nợ CHƯA test).
- §10 ghi nhận **nợ "nhân vật thật" của nhãn cảnh giới đã đóng một phần**: chủ dự án tự chụp tài khoản thật của mình (`tttttt`, ID 548, màn 1920px) sau v14 → "Nhập Môn Sơ kỳ 2 Giai" **1 dòng** trong khung 660px. Vẫn mở: cảm ứng điện thoại thật, nhân vật có tên/số tài nguyên dài bất thường, đường tụt chữ 13–16px của `fitRealm()`.
- `game/xiulian.php`: sửa chú thích `fitRealm()` "San 13.5px" → đúng mã là **13px** (chỉ là comment, không đổi hành vi; `php -l` sạch, file vẫn 523 dòng).
- Memory: `project-xiulian-ui-rework.md` trỏ thẳng vào §11 + §9.1; `project-dev-test-harness.md` đổi cách đo zoom thành "đặt `zoom = 1` trước khi đo" thay vì chia 0.75 tay; `chrome-headless-screenshot.md` (user-scope) thêm **BẪY THỨ NĂM** (zoom làm lệch hệ px + `scrollWidth` nói dối + nowrap làm phình flex item); giữ nguyên `feedback-khong-mo-rong-pham-vi-sua.md` đã lập khi bác v13.

---

## Trang Tu Luyện v14: thu lại đúng phạm vi — mặt bằng giao diện về 660px như v12 (09/10/2026)

**Chủ dự án bác bản v13:** "Bảo bạn sửa chữ nhập môn 2 giai thì 2 dòng về 1 dòng chứ bảo bạn tăng chiều rộng toàn giao diện đâu"

- **Đo lại có kiểm chứng, kết luận v13 sai:** seed tài khoản E2E về đúng cảnh giới được báo lỗi (`UPDATE game1 SET ulv=17, uexp=7128` → "Nhập Môn Sơ kỳ 7 Giai") rồi đo với panel **660px cũ**: nhãn = **214px** trong chỗ trống **271px** → **tự nó đã 1 dòng**, kể cả khi tắt `nowrap`. Vậy bề rộng khung trang chưa bao giờ là nguyên nhân; nới panel lên 800px là sửa vượt phạm vi.
- `css/mystyle.css`: **xoá** rule `.tcg-main-panel:has(.xl-page){width:min(800px,…)}` → panel về `min(660px,…)` của dòng 199, toàn bộ mặt bằng trang đúng như bản v12 đã duyệt. Giữ nguyên `xl_realm_label()` + `.xl-realm-note` (đây mới là phần giải quyết nhánh Viên Mãn có `<br/>`).
- `css/mystyle.css`: thêm `.xl-realm{min-width:0;max-width:100%}` và `.xl-col{min-width:0}` (desktop). Không có hai dòng này thì nhãn dài nhất ("Hỗn Nguyên Thánh Nhân Trung kỳ 10 Giai" = 354px) làm **thẻ cảnh giới nở 323→411px và lấn cột chỉ số** — do `.xl-realm` là flex item `align-items:center` có `min-width:auto`.
- `css/mystyle.css`: đệm nhãn `padding:3px 10px → 3px 6px` (và bản ≤760 `3px 8px → 3px 6px`) để dành 8px cho tên dài; **cỡ chữ không đổi** (17px desktop / 16px ≤760).
- `game/xiulian.php`: viết lại `fitRealm()` (dòng 455) — bản cũ dùng `scrollWidth − clientWidth` và **đo ra 0 dù chữ nowrap tràn**, tức hàm không bao giờ chạy; nay đo bằng `Range.getBoundingClientRect()` (đúng cả khi tràn), hạ dần 0.5px tới **sàn 13px**, vẫn không đủ thì trả `white-space:normal` để quấn dòng (thà 2 dòng còn hơn chữ 12px hoặc tràn thẻ). Chạy lại khi `resize`.
- Bump `css/mystyle.css?v=2026100910` ở `game.php:502`, `gm/gm.php:9`, `gm/player.php:9`.
- **Đã test:** `php -l` sạch; CSS cân **3879/3879** ngoặc nhọn, khối `.xl-*` 0 ký tự non-ASCII, không còn `:has(.xl-page)`. Quét toàn bộ **85 tên cảnh giới × 4 hậu tố = 340 nhãn** trong harness (đã đặt `body.style.zoom=1` để số đo cùng hệ px CSS): desktop 1337px **0 nhãn quấn dòng**, `.xl-realm` giữ 323px, 314/340 nhãn còn 16.5–17px; mobile 390px **0 nhãn quấn dòng**, `.xl-realm` 350px, 176/340 giữ 16px. Ảnh thật ở 660px: `crop_xl_v14_ownerstate_desktop.png` + `crop_xl_v14_ownerstate_mobile.png` (1 dòng, đúng cảnh giới chủ dự án báo), `crop_xl_v14_vienman_desktop.png` (Viên Mãn: nhãn 1 dòng + chú thích bên dưới), `shot_xl_v14_width_restored_desktop.png`. Đã trả tài khoản E2E về `ulv=1000, uexp=60621060`; file harness đã xoá khỏi web root.
- **CHƯA test:** đường tụt chữ 13–16px mới mô phỏng bằng chuỗi dựng (chưa có nhân vật thật ở 26 cảnh giới cuối); cảm ứng điện thoại thật; nhân vật thường.

---

## Trang Tu Luyện v13: nhãn cảnh giới về 1 dòng (09/10/2026)

> ⚠️ **Đã bị bác một phần ở v14:** phần nới `.tcg-main-panel:has(.xl-page)` lên 800px là sửa vượt yêu cầu và **đã xoá**; kết luận "thẻ chỉ 242px nên không đủ chỗ" là **sai do đo lộn hệ px khi trang có `zoom:.75`**. Phần `xl_realm_label()` / `.xl-realm-note` / `fitRealm()` vẫn đúng và được giữ.

**Chủ dự án báo:** "à bị lỗi: Nhập Môn Sơ kỳ 2 Giai đang bị thành 2 dòng kìa sửa thành 1 dòng đi"

- Nguyên nhân (đo bằng harness iframe, không đoán): trên desktop `.xl-page` chỉ **466px** vì `css/mystyle.css:199` giới hạn `.tcg-main-panel{width:min(660px,…)}`, chia 2 cột nên thẻ `.xl-realm` chỉ còn **242px**; `.xl-realm-name` lại có `max-width:94%` → chuỗi "Nhập Môn Sơ kỳ 2 Giai" (~200px) không đủ chỗ nên đứt 2 dòng.
- `css/mystyle.css`: thêm `.tcg-main-panel:has(.xl-page){width:min(800px,calc(100vw - 24px))}` trong `@media(min-width:761px)` (mượn precedent `:has(.ydinfo-page)` ở dòng 1750) → `.xl-page` 466→**570px**, `.xl-realm` 242→**297px**; `.xl-realm-name` bỏ `max-width:94%` → `max-width:100%` + `white-space:nowrap`, padding ngang `12px→10px` (desktop) / `12px→8px` (≤760), `.xl-realm` padding ngang `16→12` (desktop) / `14→10` (≤760), `letter-spacing .3→.2`. **Không đổi cỡ chữ.**
- `game/xiulian.php`: thêm `xl_realm_label()` (dòng 32) — `getJingJieHtml()` ở trạng thái **Viên Mãn** trả về chuỗi có `<br/> ( Pháp Tắc Hộ Thuẫn Nhập Cảnh - Tỉ Lệ Đột Phá +10% )`, nên cắt tại `<br/>`: phần trước vào `.xl-realm-name` (giữ nguyên tag màu), phần sau strip tag + bỏ ngoặc đơn → `.xl-realm-note` mới (12.5px, được phép quấn). **Không sửa `getJingJieHtml()`** vì nhiều trang khác đang dùng.
- `game/xiulian.php`: thêm `fitRealm()` (dòng 454, ES5) — chỉ hạ `font-size` khi `scrollWidth > clientWidth`, bước 0.5px, **sàn 13.5px**, chạy lại khi `resize`. Tên thường gặp giữ nguyên 16/17px.
- Bump `css/mystyle.css?v=2026100908` ở `game.php:502`, `gm/gm.php:9`, `gm/player.php:9`.
- **Đã test:** `php -l` sạch; CSS cân 3880/3880 ngoặc nhọn, khối `.xl-*` 0 ký tự non-ASCII. Đo 8 nhãn cảnh giới dài nhất game (ghép từ 86 tên trong `class/rangesjj.php` + "Trung kỳ/Viên Mãn" + "10 Giai"): ở 1337px tất cả ≤ 283.9px trong thẻ 297px → **1 dòng, giữ 17px**; ở 390px tất cả ≤ 330px → 1 dòng, 3/6 tên dài nhất tự tụt 16→15–15.5px. Chuỗi đúng báo cáo "Nhập Môn Sơ kỳ 2 Giai" = 160px/198px → 1 dòng cả hai breakpoint (`crop_xl_v13_owner_desktop.png`, `crop_xl_v13_owner_mobile.png`), state Viên Mãn của tài khoản E2E (`crop_xl_v13_vienman_*.png`). `scrollWidth == innerWidth` ở cả 390 và 1337 (không tràn ngang); cao trang desktop 608→638px (+30 do dòng chú thích Viên Mãn).
- **CHƯA test:** đường tụt chữ của `fitRealm()` mới mô phỏng bằng cách chèn chuỗi giả vào DOM, chưa có tài khoản thật ở cảnh giới Thánh Nhân; chưa bấm thử trên điện thoại thật.

---

## Trang Tu Luyện: dựng lại thành hub 1 màn + gộp nút Đột Phá vào orb (07–09/10/2026)

**Yêu cầu của chủ dự án:** "dựa vào giao diện chuyển sinh và source tài nguyên của Linh Kiếm Cửu Thiên, tôi muốn xây dựng lại 1 giao diện tu luyện" → sau đó siết dần qua 8 vòng phản hồi (chi tiết + ảnh nghiệm thu: `docs/UI_XIULIAN.md`).

- `game/xiulian.php` dựng lại markup: **hub một màn** = sân khấu ngồi thiền + 2 rail hệ thống (trái: Công Pháp/Linh Căn/Thể Chất/Huyết Mạch/Luyện Đan — phải: Trang Bị/Chế Tạo/Võ Công/Bí Tịch/Thiên Phú), lưới 2 cột (cảnh giới + thanh tu vi | 9 ô số), hàng hành động, thẻ trạng thái, tabbar 6 nút. **Logic tu luyện, công thức tu vi, chi phí, thời lượng giữ nguyên 100%.**
- **Orb một nút ba vai** theo `$running` / `$orb_break`: **TU LUYỆN** (`startxiulian&canshu=1|2|3`) ↔ **DỪNG** (`endxiulian`) ↔ **ĐỘT PHÁ** (`cmd=tupo`, vàng + nhấp nhịp). Gộp vì đã trace server-side: ở 100% tu vi thì `game.php:432-439` đổi cưỡng bức `startxiulian`→`endxiulian` và `xiulian.php:182` clamp tu vi ở `umaxexp`, nên tu luyện tiếp **chỉ đốt Linh thạch/Ma thạch mà không nhận thêm điểm**. Orb Đột Phá chỉ **mở** trang `cmd=tupo` để giữ bước xác nhận (đột phá có thể thất bại, mất tu vi/văng cấp).
- 3 chip phụ (Dùng Đan / Lò Đan / Đột Phá) đặt **hai bên orb** bằng `.xl-action{display:grid;grid-template-columns:1fr auto 1fr}`; khi orb đã là Đột Phá thì tách 2 nút đan sang mỗi bên một cái cho cân.
- `css/mystyle.css` khối `.xl-*` (748–876) + 2 breakpoint 760/430. **Không hạ cỡ chữ** ở bất kỳ bước nào.
- `class/player.php:405` tách `combat_power($player)` dùng chung. `game.php:592` chặn not `.tcg-tip .btn-break` riêng cho trang này để không nhân đôi nút Đột Phá.
- Ảnh `images/xiulian/nen-ngoi-thien.jpg` + `banner-lua.png` lấy từ source LKCT (không sinh AI).
- Bump `css/mystyle.css?v=2026100905` ở `game.php:502`, `gm/gm.php:9`, `gm/player.php:9`.
- **Đã test:** `php -l` sạch cho mọi file sửa; CSS cân 3878/3878 ngoặc nhọn, khối `.xl-*` không còn ký tự lỗi; chụp thật **3 trạng thái orb** ở 1337px và 390px (mobile render trong iframe 390px vì `--window-size` có sàn ~500px) — `shot_xl_v12_tabbar_desktop.png`, `shot_xl_v12_tabbar_mobile.png`; đo `scrollWidth 375 < innerWidth 390` → không tràn; mọi link đã xác minh trả về nội dung thật qua `--dump-dom`.
- **CHƯA test:** cảm ứng thật trên điện thoại; chạy bằng nhân vật thường (mới dùng tài khoản E2E `e2e2e2e2…`); tên nhân vật/số tài nguyên dài bất thường.
- **Chờ chủ chốt:** rail "Luyện Đan" và chip "Lò Đan" cùng trỏ `cmd=luonden` (trùng link thật).

---

## Header: cân lại cụm nút điều hướng (06/10/2026)

**Chủ dự án báo:** "Cụm nút chức năng ở header đang bị lệch sang trái 1 chút."

- Nguyên nhân (đo thật bằng Chrome headless trên trang giả dùng đúng CSS): `.tcg-topbar` là flex + `space-between`, nên nav chỉ cân đối trong **phần còn lại** giữa logo và userbox. Logo 143px < userbox 227px ⇒ nav lệch trái đúng `(143-227)/2 = -42px` (tâm nav 909 so với tâm màn hình 951 ở 1920px).
- `css/mystyle.css` (khối mới cuối file, `@media(min-width:761px)`): cho `.tcg-logo` và `.tcg-userbox` `flex:1 1 0` để hai bên rộng bằng nhau → nav nằm đúng tâm; `.tcg-userbox` thêm `justify-content:flex-end` để dính mép phải.
- `min-width:max-content` ở cả hai bên là sàn cho màn hẹp: dưới ~1050px hai cột không thể co dưới kích thước nội dung, nav tự co lại theo cách cũ, **không** chữ chồng chữ.
- Không đổi gì ở ≤760px (header mobile dùng grid riêng ở khối `body.tcg-bg-body .tcg-topbar`).
- Bump `css/mystyle.css?v=2026100602` (gộp luôn lần bump 2026100601 của mục dưới, tránh CSS cũ trong cache).
- **Đã test:** đo `getBoundingClientRect()` ở 1920 / 1440 / 1280 / 1200 / 1180 / 1100 / 1000 / 900 / 820 / 780 / 761px. Từ 1100px trở lên: delta tâm nav = **0px**, khoảng hở logo-nav và nav-userbox đều dương. Từ 780-1000px: delta -52px = y hệt trước khi sửa (không thoái lui). CSS cân bằng ngoặc nhọn, UTF-8 sạch.
- **CHƯA test:** phiên bản có tên nhân vật / số tiền dài hơn bình thường (userbox rộng hơn ⇒ ngưỡng hết cân hạ xuống, desktop rộng vẫn đúng tâm).

---

## Trang chủ: đưa Tu Luyện và Võ Công lên cụm nút chính (06/10/2026)

**Yêu cầu của chủ dự án:** hai nút quan trọng nhất phải nằm ở hàng nút lớn, không chìm trong lưới menu phụ.

- `game/nowmid.php`: `.tcg-actions` có 6 nút theo thứ tự Tu Luyện - Võ Công - Phó Bản - Nhiệm Vụ - Bản Đồ - Thiên Cơ Các. Desktop là lưới 3 cột (2 hàng); mobile xếp 1 cột nên hai nút quan trọng nằm trên cùng.
- Bỏ 2 mục "🧘 Tu Luyện" và "⚔ Võ Công" khỏi `.tcg-menu-grid`; lưới phụ còn 15 nút = 3 hàng x 5 cột vừa khít, không còn hàng lẻ 2 nút.
- `css/mystyle.css`: thêm biến thể `.tcg-action.cyan` (Tu Luyện) và `.tcg-action.gold` (Võ Công) cạnh green/purple/red sẵn có.
- Bump `css/mystyle.css?v=2026100601` ở `game.php`, `gm/gm.php`, `gm/player.php` để tránh CSS cũ trong cache.
- **Đã test:** `php -l` sạch; dựng trang giả (HTML + CSS thật của project) chụp ở 1280px và 500px, kiểm bố cục 2x3 / 1 cột và 15 nút menu phụ.
- **CHƯA test:** bấm thật hai nút trong phiên đăng nhập (đường dẫn `$xiuliancmd` / `$wgxlcmd` giữ nguyên như cũ, chỉ đổi vị trí).

---

## Tạo nhân vật: bỏ mọi ngẫu nhiên thiên phú — mọi tân thủ khởi đầu trắng (06/10/2026)

**Quyết định của chủ dự án:** nhân vật mới không còn nhận ngẫu nhiên thiên phú, tất cả vào game xuất phát như nhau.

- `game.php` (`case cjplayer`): xoá 3 lần roll lúc tạo — Linh Căn 70%, Thể Chất 60%, Huyết Mạch 50%. `INSERT game1` giờ ghi cứng `linhcan_id/linhcan_quality/thechat_id/thechat_quality/huyetmach_id/huyetmach_quality = 0`.
- Xoá luôn 3 khối `echo` hiển thị thiên phú nhận được; chỉ còn dòng chào mừng + tin hệ thống "Chào mừng … bước lên tiên đồ".
- **Giữ nguyên** hai lựa chọn chủ động trên `game/cj.php`: Giới tính và Thân phận (Chiến sĩ/Thiếu hiệp/Ẩn sĩ — vẫn khác nhau về chỉ số cộng theo cấp trong `upplayerlv()`). Chủ dự án chọn "giữ chọn Thân phận".
- Không đụng đường đạt về sau: `game/use_dj.php` (Tố Thể Đan / Tẩy Tủy Đan / Tẩy Linh Đan), `linhcan_quest`, `linhcan_boss`, đột phá — vẫn roll bình thường.
- **Hệ quả với nhân vật đã tồn tại:** chỉ đổi lúc tạo, 11 nhân vật đang có trong DB giữ thiên phú cũ.
- **Đã test thật** (nginx + php-cgi 9002 + MySQL local): 3 luồng đăng ký → đăng nhập → tạo nhân vật qua route thật, cả 3 ra `0/0/0/0/0/0`, chỉ số giống hệt nhau; trang Trạng thái / Linh Căn / Thể Chất / Huyết Mạch render đúng ô trống "chưa có" cho nhân vật đầu trắng; `php -l` sạch. Tài khoản + nhân vật test đã xoá khỏi DB.
- **CHƯA test:** nhân vật đầu trắng đánh quái/lên cấp trong phiên thật (chỉ mới kiểm tra render trang).

---

## Trang bị: Nạp Ngũ Hành (đa hệ) — rèn tối đa 5 hành lên một món (04/10/2026)

**Spec:** `docs/DESIGN_NGUHANH_KICHHOAT.md` · **Plan:** `docs/superpowers/plans/2026-10-04-nguhanh-kichhoat.md`
**Tên hiển thị trong game: "Thuộc tính Ngũ Hành"** (chủ dự án duyệt 04/10/2026 — "Nạp" chỉ là động từ trên nút; spec/code vẫn giữ tên `nap_hanh`).

### Schema & config (Task 1)
- `sql/nguhanh_kichhoat.sql` — **LOCAL ĐÃ ÁP DỤNG** (đã `mysqldump` backup trước khi chạy), **production CHƯA chạy**:
  - `playerzhuangbei.element VARCHAR(24) NOT NULL DEFAULT 'none'`
  - `fangshi_zb.element VARCHAR(24) NOT NULL DEFAULT 'none'` (treo bán mang theo hệ)
  - `player_chetao_log.log_type VARCHAR(20) NOT NULL DEFAULT 'chetao'` (log nạp ghi `nap_hanh`)
- `class/config/nguhanh_config.json` — nấc hợp căn 1/3/6/9/12 món → +3/6/10/15/22%; đá tinh thạch theo hệ (id 32–36); tỉ lệ gốc 90 − 10·n + thưởng linh căn (sàn 40, trần 95); `thach_base 500`. **Không đụng `zhuangbei_config.json`** (Plan Z giữ nguyên).

### Engine (Task 2) — `class/function/nap_hanh.php` (namespace `player`)
- `zhuangbei_elements_parse/join`: chuẩn hoá chuỗi đa hệ theo thứ tự `kim,moc,thuy,hoa,tho`; giá trị rác → `none`.
- `nap_hanh_cost` = `(1+n)` đá hệ tương ứng + `500 × rarity × (1+n)` linh thạch. `nap_hanh_rate` = 90 − 10·n + thưởng theo linh căn (kim +15, hỏa/all +10, thổ +8, mộc +5, thủy 0).
- `nap_hanh_do()`: kiểm chủ sở hữu bằng `sid` đọc từ DB (không tin tham số URL), chặn trùng hệ / đã đủ 5 hệ, trừ nguyên liệu, `UPDATE ... WHERE zbnowid AND sid` đòi `rowCount`, **thất bại chỉ mất nguyên liệu**, hoàn nguyên liệu khi đua tranh đổi chủ, ghi `player_chetao_log`, xoá cache trang bị + nhân vật.
- Hook test tất định: `$GLOBALS['nguhanh_force_roll']`.

### Đường rèn & hiển thị (Task 3–6)
- `class/player.php`: thêm `element` vào `zhuangbei_columns()`; merge DB đè config (element "la" → ép về `none` + `error_log`); `getplayer()` gom `zb_element_set` (bộ hành của 16 món đang mặc) và `zb_element` (hành đại diện, giữ nguyên hành vi cũ cho người chưa nạp); gọi `nguhanh_apply_passive()` **sau** khối linh căn vì `linhcan_element` chỉ được gán ở đó.
- Nội tại hợp căn: mỗi món trùng hành với linh căn cộng `%` lên **tổng chỉ số do nhóm món hợp căn đóng góp** (kim→công, mộc→thủ, thủy→hút máu, hỏa→bạo, thổ→máu); kết quả ghi `$player->nguhanh_info`.
- Combat: `zhuangbei_pick_element($set, $monsterElement)` chọn hành khắc tốt nhất trong bộ đang mặc; `game/pve.php` và `game/boss.php` dùng thay cho `zb_element` đơn hệ.
- Route `cmd=nap_hanh` (`game/nap_hanh.php`, đăng ký trong `$cmdMap`) + section **"Thuộc tính Ngũ Hành"** trong popup `game/zbinfo.php` (chỉ chủ sở hữu thấy): nút Nạp từng hệ kèm chi phí đá/linh thạch, tỉ lệ, số đá đang có, cảnh báo "đủ 5 hệ", ghi chú mất nguyên liệu khi bại.
- Đa hệ ở mọi nơi dùng cùng dữ liệu: tooltip `class/function/itemtip.php` đổi payload `elem` thành **danh sách tag**, `js/itemtip.js` render vòng lặp; túi `game/bagzb.php` và khung mặc đồ `game/zhuangtai.php` gắn badge từng hệ; Trạng thái thêm dòng "☯ Nội tại ngũ hành". Bump `js/itemtip.js?v=2026100402`.
- `tongquan.php`: mục thống kê "Thuộc tính Ngũ Hành" đếm món đã nạp theo từng hệ; **sửa cột cảnh giới của bảng trang bị** — `realm_require` là ngưỡng `ulv` (50 cấp/cảnh) chứ không phải index `rangesjj`, thêm `tq_realm_req()`.

### Thiết kế lại khu "Thuộc tính Ngũ Hành" (04/10/2026, sau phản hồi bằng ảnh của chủ dự án)
- **Lỗi cũ:** 5 nút nhét vào `.zb-upgrade-grid` (5 cột, vốn dành cho hàng nút Cường hóa nhãn ngắn) → chữ vỡ 4–5 dòng, `line-height:35px` làm nút cao lênh khênh.
- **Mới:** mỗi hành là **một dòng** — tên hành (màu sáng) + chip `hợp linh căn` khi trùng linh căn, dưới là `2 đá (có 0) · 3000 LT · Tỉ lệ 95%` (đỏ khi thiếu đá, vàng phần tỉ lệ), bên phải là nút **`Kích hoạt`**. Header `Đã có n/5 hành` kèm danh sách hành đã nạp; khi đủ 5 thì thay bằng khối xanh "Đã đủ 5 hành".
- **CSS riêng `.nh-*`** trong `css/mystyle.css` (không đụng `.zb-upgrade-grid` của Cường hóa) + breakpoint ≤430px dồn nút xuống nguyên dòng. Bump `css/mystyle.css?v=2026100401 → 2026100404` ở `game.php`, `gm/gm.php`, `gm/player.php`.
- **Gộp nguồn:** logic render chuyển từ `game/zbinfo.php` sang **`nap_hanh_section_html()`** trong `class/function/nap_hanh.php` → popup và test CLI dùng chung một nguồn.
- **Màu ngũ hành bản sáng:** thêm `zhuangbei_element_color_ui()` (Kim `#E2E8F0`, Mộc `#4ADE80`, Thủy `#38BDF8`, Hỏa `#FB923C`, Thổ `#D6A863`) vì `#228B22`/`#8B4513` quá tối trên nền navy; dùng cho badge túi đồ, khung mặc đồ, popup, tooltip và bảng tongquan. `zhuangbei_element_color()` giữ nguyên cho dữ liệu config.
- Ảnh kiểm: `shot_nguhanh_ui.png` (3 trạng thái 0/5, 2/5, 5/5).

### Vòng 2: nút "Kích hoạt" + popup kết quả rèn đầy đủ (04/10/2026, tiếp tục phản hồi bằng ảnh)
- **Nút đổi thành `Kích hoạt`** (bỏ hẳn chữ `Nạp 95%`); tỉ lệ dời xuống dòng chi phí, màu vàng `#ffd86b`. `.nh-btn` chuyển sang `display:flex` + `align-self/justify-self:center` + `line-height:1` + `white-space:nowrap` để chữ chắc chắn giữa dọc và giữa ngang; cao 44px.
- **Thuật ngữ nhất quán:** mọi dòng chữ người dùng thấy đổi "nạp" → "kích hoạt" (`Kích hoạt hành Kim thành công!`, `… thất bại — đã mất nguyên liệu.`, `không thể kích hoạt trùng`, `Đã có n/5 hành`, `Chưa có hành nào`, `không kích hoạt thêm được nữa`). Tên hàm/route/spec vẫn là `nap_hanh`.
- **Popup kết quả viết lại** (`game/nap_hanh.php` → renderer mới `nap_hanh_result_html()` trong `class/function/nap_hanh.php`, dùng bộ `.pve-result-*` có sẵn): head tròn màu theo hành + icon hành, tiêu đề `Rèn Ngũ Hành — Kim`, pill trạng thái 3 mức (`win` xanh / `lose` đỏ / `block` xám — thêm `.pve-result-status.block`), bảng dòng: Trang bị · Hệ kích hoạt · Tỉ lệ rèn · Tinh thạch đã dùng (kèm "còn N") · Linh thạch đã dùng (kèm số dư) · Hệ đang mang dạng badge `n/5` · Nội tại hợp linh căn, rồi dòng gợi ý theo từng trạng thái, cuối cùng 2 nút **Xem lại trang bị** / **Túi trang bị**.
- **Hết lộ chuỗi DB thô:** trang cũ in `hệ hiện có: kim`; nay mọi hệ hiển thị qua `zhuangbei_element_name()` + icon + màu `_ui`.
- Engine bổ sung dữ liệu cho popup: `nap_hanh_do()` trả thêm `he`, `cost`, `zbname`, `zbys`; `rate > 0` được dùng làm cờ "đã tiêu nguyên liệu" để phân biệt rèn thất bại với chưa đủ nguyên liệu.
- Ảnh kiểm: `shot_nh_route_desktop.png` + `shot_nh_route_mobile.png` (render từ route thật, fixture sid giả), `shot_nh_v4_mobile.png` (section 1/5 và 5/5 ở 390px).

### Ô Phi Phong: ảnh cánh → áo choàng (04/10/2026, chủ dự án chỉ ra "Phi Phong là áo choàng chứ không phải cánh")
- **Nguyên nhân:** cả 10 ảnh trang bị của ô 8 (`images/16..20.png`, `images/91..95.png`) và icon ô (`images/slot/zb_slot_08.png`) đều là **cánh** — ảnh gốc của game Trung, đặt tên theo `zbid` nên không có gì sai ở lớp code, chỉ là nghệ thuật gốc vẽ cánh.
- **Tìm nguồn thay:** đã quét `E:/9kiem` (Linh Kiếm Cửu Thiên: 20.205 ảnh, `assets/icon/skin` 534 ảnh chia theo dải id — 21 thú cưng, 22 tọa kỵ, 23 vũ khí, 24 **cánh**, 25 trang phục, 26 phụ kiện, 27 avatar, 28 giáp) và 3 source game gốc (`F:/MuH5`, `F:/TuTienPhucMa`, `E:/natramadongh5`) — **không có icon 披风 rời nào**; tên file `*pifeng*/*cloak*/*cape*` và chuỗi `披风` đều trống (config LKCT đóng gói trong `configs.obj` nhị phân). Chủ dự án cho phép vẽ mới nên chuyển sang ImageGen.
- **Art mới:** 3 bản master sinh bằng ImageGen trên nền magenta phẳng → chroma-key RGBA → cắt vuông → 80×80, rồi tint theo màu `zbys` của từng món. Chia bậc theo rarity (đúng nguyên tắc "hiệu ứng phân tầng"): 1–3 áo vải thô khoá sắt, 4–6 áo lụa thêu vân hạc khoá ngọc, 7–10 áo thần tan thành tinh quang khoá rồng.
  - `zbid 16,17,18` rarity 1–3 (xám / xanh lá / tím) · `19,20,91` rarity 4–6 (cam / cam / xanh dương) · `92,93,94,95` rarity 7–10 (đỏ cam / vàng / vàng / vàng).
  - `images/slot/zb_slot_08.png`: cùng áo lụa nhưng tint xám thép và hạ sáng để đứng cạnh 15 icon ô khác (độ sáng trung bình 72.8, dải của 16 icon là 38.8–92.9).
- **Chống cache ảnh:** `class/function/zhuangbei.php` thêm `const ZB_IMG_VER` + `zhuangbei_img_ver()`; `zhuangbei_image_path()` và `zhuangbei_slot_icons()` giờ trả `./images/16.png?v=2026100405`. Đổi ảnh lần sau chỉ cần bump hằng số này. Đường dẫn tuyệt đối/URL ngoài giữ nguyên, không thêm query.
- **Backup:** ảnh cánh cũ ở `bak/slot8/wing_original/` (11 file), script sinh ảnh `bak/slot8/make_cloak_icons.py`, 3 master ở `vibe_images/cloak_master_*.png`.
- **Ảnh kiểm:** `shot_slot8_cloak_desktop.png` (10 popup thật, CSS thật), `shot_slot8_mobile.png` (390px), `shot_slot8_slots.png` (16 icon ô cạnh nhau), `shot_slot8_family.png` (cánh cũ để đối chiếu).

### Ô Phi Phong vòng 2: mỗi món một dáng, sáng tăng dần (05/10/2026, chủ dự án gửi 6 ảnh mẫu trong game gốc)
- **Chủ dự án gửi 6 ảnh mẫu** phi phong của game gốc → lưu tại `vibe_images/phi_phong_ref/ref1..6.png`. Các mẫu này **vẫn là áo choàng** (dáng choàng vai, cổ dựng, gấu bay), có điều mỗi bậc một dáng khác nhau rõ rệt — không phải cùng một hình đổi màu.
- **So 1:1 ảnh cũ với mẫu** (`bak/slot8/compare_refs.py` → `shot_cloak_v2_vs_ref.png`, 6 mẫu + 10 icon cũ phóng 3× kèm `zbid`): phát hiện **lỗi nghiêm trọng của art vòng 1** — script cũ chỉ có 3 master rồi **tint theo màu `zbys`**, nên những món trùng màu cho ra **file giống hệt nhau từng byte**:
  - `images/19.png` == `images/20.png` (md5 `c78b17f3…`) và `images/93.png` == `94.png` == `95.png` (md5 `47261e4d…`) → **10 món chỉ có 6 hình khác nhau**, và 3 món **quý hiếm nhất** game (rarity 8/9/10) hiện y hệt nhau.
  - Nguyên nhân gốc: `zbys` của 93/94/95 đều `#d4af37` (vàng kim), tint cùng một master bằng cùng một màu thì không thể khác.
- **Hướng chủ dự án duyệt:** "Mỗi món một dáng, sáng tăng dần" — 10 master **mỗi món một hình riêng**, lấy dáng từ 6 mẫu, bật sáng theo bậc nhưng **giữ silhouette áo choàng đọc được** (không tan thành khối sáng như vòng 1).
- **Pipeline mới** `bak/slot8/make_cloak_icons_v2.py`: 10 master ImageGen nền magenta phẳng → chroma-key → cắt vuông → 74×74 đặt trong 80×80. **Bỏ hẳn bước tint** (đây là chỗ gây trùng file). Thêm 2 lớp chống tái phạm: chặn md5 trùng (`SystemExit("TRUNG FILE: …")`) và đo độ sáng trung bình để xác nhận bậc cao sáng hơn bậc thấp.
  - 16 áo len xám nơ vải · 17 lụa lục khoá ngọc · 18 cờ tím thêu hạc · 19 hổ phách gấu phân tầng · 20 choàng da cừu + lông trắng · 91 pha băng xanh · 92 đỏ than + lông đen · 93 cổ lông vũ xanh hoàng gia · 94 dải vàng nóng chảy · 95 rồng vàng toả sáng.
  - Kết quả: **10 icon, 10 md5 khác nhau**; icon ô `images/slot/zb_slot_08.png` dẫn từ master 17 (tint xám thép, hạ sáng) → độ sáng 42.4, nằm trong dải 8.6–103.9 của 16 icon ô khác.
- **Ảnh cũ đã backup** nguyên bộ 11 file sang `bak/slot8/v1_cloak/`. Bump `ZB_IMG_VER` `2026100405` → **`2026100406`**.
- **Ảnh kiểm bằng route thật** (nhân vật fixture, không phải harness): `shot_cloak_v2_trangbi.png` (lưới 16 ô — ảnh mới, và cũng thấy rõ **"Đã mặc 15/16" + "Bảo Vật — Chưa trang bị"** tức lỗ hổng ô 16), `shot_cloak_v2_popup95.png` (popup: head icon là áo rồng vàng + badge Kim·Mộc·Thủy·Hỏa·Thổ·Đạo), `shot_cloak_v2_bag_mobile.png` (túi 390px: 6 badge tự xuống dòng, không tràn), `shot_cloak_v2_vs_ref.png` (đối chiếu 1:1 với mẫu). **CHƯA** xem trên điện thoại thật của chủ dự án.

### Kiểm chứng — đợt đóng backlog (04/10/2026, route thật + nhân vật fixture đã đăng nhập)

Fixture: `tests/e2e_fixture.php` dựng nhân vật `sid=e2e2…e2` / `uid=987650` với **10 món ô Phi Phong mang 0→5 hệ** (`none`, `kim`, `thuy`, `kim,thuy`, `hoa`, `moc`, `kim,moc,thuy`, 4 hệ, 5 hệ), mặc món 5 hệ, đặt linh căn theo 3 bộ `kim/tho/moc`. Dựng xong in ra URL encode của 9 route thật → bấm trực tiếp bằng Chrome headless, **không dùng trang harness nữa**.

- `tests/test_nguhanh.php` — **82 assert, ALL PASS**: hàm thuần, passive trên object giả, vòng DB thật cho thành công / thất bại / trùng hệ / thiếu đá / sai chủ / log / hoàn khi đua tranh, **markup section** (4 dòng `Kích hoạt`, chip hợp căn, tỉ lệ gắn vào dòng chi phí) và **popup kết quả** (3 trạng thái win/lose/block, badge theo số hành, không còn chuỗi `kim` thô).
- `tests/test_nguhanh_random.php` — **ALL PASS, 2.600 LẦN ROLL THẬT** (`mt_rand`, bỏ hẳn `force_roll`): A tỉ lệ n=0 đo được 89.5% vs 90; B n=4 (5 đá, LT tăng theo `1+n`) 52.1% vs 50; C linh căn kim 96.7% vs trần 95 → đúng sàn/chặn; D món đã đủ 5 hệ bị chặn, **không mất đá, không mất linh thạch, `element` không đổi**. Mỗi lần roll xong reset `element` để tỉ lệ/chi phí không trôi.
- `tests/test_nguhanh_combat.php` — **ALL PASS (25 assert)**: `zhuangbei_pick_element()` chọn đúng hành khắc cho cả 5 hành quái + tập rỗng + quái `none`/null; hệ số 1.2 / 0.85 / 1.0; **đánh thật qua HTTP** `cmd=pvegj` → dòng `Trang bị khắc Thủy!` `+20%` xuất hiện, **không** có dòng `Linh căn khắc` (đúng: chỉ tính một nguồn). Kèm ghi chú lỗ hổng boss (xem mục "Cần chủ dự án quyết").
- `tests/test_nguhanh_market.php` — **ALL PASS (24 assert)**: treo bán → `fangshi_zb.element` mang nguyên chuỗi → trang Phương Thị hiển thị badge → **người khác mua vẫn giữ hệ** (`kim,thuy`), trừ 1000 LT phía mua / cộng 900 LT phía bán (10% phí) → **thu hồi cũng giữ hệ** → chủ mới còn rèn tiếp được (n=2, 3 đá).
- `tests/deploy_preflight.php` — **CHỈ ĐỌC**, in từng bảng: đã có `element`/`log_type` chưa, 15 cột `sid` là số hay đã nòng. Local: `Hang schema con thieu/sai: 0` (84 bảng).
- `tests/capture_tip_390.php` — dựng iframe 390px quanh route `cmd=getbagzb` thật, `scrollIntoView` đúng món rồi dispatch `mouseover` (vì `itemtip.js` `onClick` only-mở-khi-không-hover), in số đo `vw/sw/bw/bh` ngay trong ảnh để kiểm tràn ngang.
- **Ảnh kiểm route thật** (desktop + 390px): `shot_e2e_trangbi.png`, `shot_e2e_bagzb.png`, `shot_e2e_chakanzb95.png`, `shot_e2e_chakanzb16.png`, `shot_e2e_zhuangtai.png`, `shot_e2e_fangshi.png`, `shot_e2e_shangdian.png`, bản mobile `shot_e2e_*_mobile.png`, và tooltip `shot_e2e_tip_many.png` (5 hệ: badge `Phi Phong · Kim · Mộc · Thủy · Hỏa · Thổ`, đủ 7 dòng chỉ số, nút *Xem chi tiết*), `shot_e2e_tip_three.png` (3 hệ + dòng **"So với món đang mặc: Đạo Phi Phong"** + delta đỏ `−110` / `−1347`). Số đo: `vw=390 sw=375 bw=378` → **không tràn ngang**.
- Ảnh ô Phi Phong mới đã xác nhận **hiện đúng trong route thật** (túi đồ, ô đang mặc, popup chi tiết, Phương Thị, cửa hàng) — thay cho bằng chứng harness cũ.
- `php -l` sạch toàn bộ file đã chạm; checkpoint: `bak/nguhanh/*.task{1..8}.*` (repo chưa có commit nên không tạo commit).
- **Chạy lại toàn bộ trên 05/10/2026** sau khi thay art ô 8 + dựng lại fixture: 4 suite vẫn **154 assert, 0 FAIL** (82 / 23 / 25 / 24) và `test_nguhanh_random` vẫn đủ **2.600 roll thật**.

### Cần chủ dự án quyết (phát hiện khi test — chưa tự sửa)
1. **Boss không thể khắc chế bằng trang bị**: bảng `boss` **không có cột `element`** (`getboss()` chỉ đọc `$row['element']` khi cột tồn tại) → khối `game/boss.php:241-274` là code chết. Quái hoang chạy đúng. Cần: ALTER thêm cột + điền hành cho từng boss.
2. **Ô 16 "Bảo Vật" không mặc được gì**: `class/config/zhuangbei_config.json` **không có item nào `zbtool=16`**, một dòng cũ (zbid 55) nay map sang tool 11. Theo Plan Z, sửa config phải được duyệt.
3. **Deploy production**: runbook + file SQL gộp (18 ALTER, **0 DELETE**) đã sẵn ở `docs/DEPLOY_PRODUCTION_NGUHANH.md` + `sql/deploy_production_20261004.sql`. **CHƯA chạy trên production.** Đã **diễn tập trên DB tạm** `game_sqltest` (84 bảng, đặt lại đúng trạng thái chưa migrate): preflight báo 18 lỗi → chạy file một lần không lỗi → preflight báo 0, định nghĩa 3 cột mới khớp với DB `game` thật; DB tạm đã drop.

### Ghi chú nhỏ (không ảnh hưởng chơi)
- Nhánh `'Trang bị đã đủ 5 hệ ngũ hành.'` trong `nap_hanh_do()` **không tới được**: đủ 5 hệ hợp lệ thì kiểm tra "trùng hệ" chặn trước. Đã ghi chú ngay trong `tests/test_nguhanh_random.php`.
- `game/trangbi.php:37` và `game/zhuangtai.php:90` nối `&v=filemtime(...)` vào URL đã có `?v=ZB_IMG_VER` → thành `?v=X&v=Y`. Hợp lệ, chỉ thừa.
- `tests/e2e_fixture.php` **không** `DELETE FROM fangshi_zb WHERE sid='<hex>'`: cột `sid` bảng đó vẫn là INT (nợ schema nhóm B), so hex với int sẽ so với 0 và **xoá nhầm tin treo bán thật**. Đã để comment chốt lại.


## Trang bị: nhãn cường hóa "+N" + sửa render khung chat (04/10/2026)

### Ô trang bị (`game/trangbi.php` + `css/mystyle.css`)
- Mỗi ô chỉ hiện: tên món + dòng phụ "Cấp X" + **dấu "+N"** bám ngay sau tên khi có cường hóa. **Bỏ hẳn chữ "Cường hóa"** (trước ghi "· Cường hóa +N").
- Tooltip (hover + chạm) mở khi rê vào **ẢNH item** (thẻ `<a>` bọc `<img>` mang `data-tip`), KHÔNG phải box tên.
- Trang "Trang bị" chia **2 cột 8/8** theo nhóm: trái "Trang bị", phải "Vũ khí – Phù – Pháp bảo".

### Màu cường hóa: ĐỎ → XANH LÁ (chủ dự án chọn)
- `.equip-qh` đổi từ `#ff3b5f` (đỏ) sang **`#4ade80`** (xanh lá) + glow `rgba(74,222,128,.6)`. Đây là ngoại lệ có chủ đích so với bảng màu Hỏa/Thổ (đỏ/cam) ưu tiên của dự án — đừng tự đổi về đỏ.
- Sửa ở **cả 3 rule** `.equip-qh` trong `css/mystyle.css` (bare ~621 + 2 rule `.profile-page` ~6734/~6743).
- Bump version cache `?v=2026100310` → `2026100401` ở `game.php` (×3), `gm/player.php`, `gm/gm.php` để trình duyệt không ăn CSS/JS cache cũ.

### Sửa lỗi khung chat Trang Chủ (`game/nowmid.php`)
- **Lỗi**: Trang Chủ chạy `htmlspecialchars()` thô lên tin hệ thống → icon `<IMG>` jisha và link vật phẩm boss (`<a><font>`) hiện thành **chữ raw** (`<IMG width='15'...>`). Trang chat đầy đủ (`liaotian.php`) đã render đúng từ trước.
- **Nguyên nhân sâu**: bảng `ggliaotian` chứa CẢ player chat (input người dùng, phải escape) lẫn tin hệ thống (HTML tin cậy, phải render) → không thể escape thô cả msg.
- **Sửa**: tách helper `tcg_chat_format_msg($msg, $allowHtml)` (+ `tcg_chat_escape`, `tcg_chat_player_link`) sang **`class/function/chatmsg.php`** (nguồn duy nhất, global scope), `require_once` trong `class/player.php`. `nowmid.php`: tin hệ thống (uid=0) → `(...,true)`, tin người chơi → `(...,false)`. **Xoá** khối định nghĩa trùng trong `liaotian.php`.
- **An toàn XSS giữ nguyên**: `allowHtml=true` chỉ whitelist `<img>` nội bộ `./images/*.{png,gif,jpg,jpeg,webp}`, `<a>` chỉ href bắt đầu `?cmd=` (chặn `javascript:`/`http:`), `<font>` chỉ màu hex/tên màu hợp lệ; mọi phần còn lại vẫn escape.

### Kiểm chứng
- `php -l` sạch 4 file (`chatmsg.php`, `player.php`, `liaotian.php`, `nowmid.php`).
- Harness render: `<IMG>` → `<img class="tcg-chat-inline-img">`; player chat vẫn chặn `onerror`/`javascript:`.
- curl Trang Chủ + trang chat: render icon, **0** raw `<IMG>`, không Fatal/Warning. Ảnh: `shot_home_chat.png`, `shot_qh_green.png`.

### Còn treo (CHƯA test trong combat thật)
- Giá trị cường hóa **test** `qianghua` = +5 (zbnowid 77300) / +9 (77299) của uid547 **vẫn còn trong DB** (để chủ dự án xem trên điện thoại) → cần đặt lại 0 khi duyệt xong.
- Vòng PvP; gamnol/wugong/playertaskinfo; boss rơi đan hoàn; Huyết Mạch bậc 2+; Thể Chất theo boss/nhiệm vụ; `⛩ Môn phái` hiển thị "Π"; migration sid production (chỉ chạy ALTER, bỏ DELETE).

## Sửa lỗi Tông Môn & Boss Tông Môn (28/07/2026)

### Lỗi giao diện Tông Môn
- `game/club.php` — Trang chi tiết tông môn bị đè lên các trang con (quyên góp, nâng cấp, v.v.)
- **Nguyên nhân**: Sau `switch ($canshu)`, code fallthrough render trang chính mà không có `else`
- **Sửa**: Bọc trang chi tiết trong `else { ... }` chỉ chạy khi `$canshu` rỗng

### Thêm nút "Trở Về"
- `game/club.php` — Giao diện nâng cấp (`case "upgrade"`) thêm nút "← Trở Về" link về trang tông môn
- `game/club.php` — Giao diện quyên góp (`case "donate"`) thêm nút "← Troved ve" link về trang tông môn

### Lỗi Boss Tông Môn - Lịch sử tấn công
- `game/club_boss.php` — `$actualHp` được dùng trong INSERT `club_boss_log` **trước khi** được định nghĩa
- **Nguyên nhân**: Log INSERT ở dòng 291 dùng `$actualHp` nhưng query SELECT boss_hp ở dòng 296 mới gán giá trị
- **Sửa**: Đọc HP thực tế từ DB **trước** khi INSERT log

### Lỗi Boss Tông Môn - Combat Feed bị xóa
- `js/club_boss.js` — `buildBattleLog()` dùng `feed.innerHTML = ''` xóa toàn bộ lịch sử combat mỗi lần tấn công
- **Sửa**: Dùng `insertBefore` để thêm entry mới vào đầu, giữ tối đa 20 entries

### Quest "Luyện Đan Đóng Góp" không track progress
- `class/function/club_quest.php` — `$typeMap` thiếu `'alchemy_donate'` → `club_quest_update()` trả về false
- `game/luonden.php` — Không gọi `club_quest_update($sid, 'alchemy_donate', ...)` khi luyện đan thành công
- **Sửa**: Thêm `'alchemy_donate'` vào `$typeMap` + gọi update trong `luonden.php`

### Quest "Online" không tự động track
- `game/club.php` — Quest online chỉ cập nhật khi bấm nút "⏰ Online" nhận EXP
- **Sửa**: Thêm auto-track trong trang club — mỗi lần load trang, kiểm tra thời gian online và tự động cập nhật progress quest

### Quest Boss → Tấn công thay vì Giết
- `class/config/club_quest_config.json` — Đổi quest "Tiêu Diệt Boss" (1 tier, target=1, reward=500) thành "Tấn Công Boss" (3 tiers: 1/3/5 lần, reward 200/400/800)
- `class/function/club_quest.php` — Thêm `'boss_attack'` vào `$typeMap`
- `game/club_boss.php` — Thêm hook `club_quest_update($sid, 'boss_attack', 1)` mỗi lần tấn công boss
- `sql/migrate_boss_attack.php` — Migration: UPDATE quest cũ + INSERT 2 tier mới (dùng PHP để giữ UTF-8)

### Lỗi "Lỗi kết nối" khi nhận thưởng nhiệm vụ
- `game/club.php` — Dùng `$_GET['pqid']` và `$_GET['bonus']` nhưng `parse_str($Dcmd)` không populate `$_GET`
- **Nguyên nhân**: `parse_str(string)` chỉ tạo local variables, không thêm vào `$_GET`. PQid nằm trong encoded `cmd` nên không có trong raw query string
- **Sửa**: Dùng `$pqid` (local var) thay vì `$_GET['pqid']`, tương tự `$bonus` và `$tid`

---

## Thiên Cơ Các — Hệ Thống Mua Võ Công / Công Pháp

### Tổng quan
- `game/thiencoccac.php` — Thiên Cơ Các: shop mua Võ Công + Công Pháp theo cảnh giới
- Tabs Võ Công / Công Pháp với CSS redesigned (border-radius, gradient, glow effect)
- Popup thanh toán xác nhận trước khi mua (dùng `wg_confirm_modal()` từ `wugong_utils.php`)
- Kiểm tra cảnh giới yêu cầu + số dư tiền tệ trước khi mua

### Võ Công — 30 bí tịch, phân loại 3 chiều

**Loại** (category):
- `khinh_cong` — Khinh Công (6 items)
- `noi_cong` — Nội Công / Tâm Pháp (14 items)
- `ngoai_cong` — Ngoại Công / Chiêu Thức (9 items)
- `tap_ky` — Tuyệt Kỹ / Tạp Kỹ (1 item)

**Nguyên tố** (element): Kim / Mộc / Thủy / Hỏa / Thổ / Ngũ Hành (all) / Vô Hành (none)
- Tương tác ngũ hành: Kim khắc Mộc → Mộc khắc Thổ → Thổ khắc Thủy → Thủy khắc Hỏa → Hỏa khắc Kim
- Combat bonus: +20% damage khi khắc, -15% khi bị khắc

**Cấp bậc** (rarity) + **Màu phẩm chất** (`wgys`):
| Cấp bậc | `wgys` | `wg_color_name()` |
|---|---|---|
| Nhập Môn | `#647b5d` | Phàm |
| Trung Cấp | `#288dc7` | Lam |
| Thượng Thừa | `#39b512` | Lục |
| Thần Công | `#f9c508` | Hoàng Kim |

**Mục đích** (purpose): PK / Farm / Tu Luyện / Tiện Ích

**Hiệu ứng chiến đấu** (effect_type):
- `pierce_def` — Xuyên phá phòng ngự
- `crit_dmg` — Tăng sát thương chí mạng
- `combo_hit` — Đòn liên hoàn
- `damage_reduce` — Giảm sát thương nhận vào
- `hp_regen` — Hồi HP mỗi lượt
- `escape` — Tỷ lệ chạy trốn
- `poison_immune` — Miễn nhiễm độc
- `steal_reward` — Ăn cắp vật phẩm
- `drop_bonus` — Tăng rơi vật phẩm
- `exp_bonus` — Tăng kinh nghiệm
- `control_reduce_damage` — Giảm sát thương khi bị khống chế

**Thống kê phân phối**:
| Cấp | Nhập Môn | Trung Cấp | Thượng Thừa | Thần Công | Tổng |
|---|---|---|---|---|---|
| Khinh Công | 3 | 2 | 1 | 1 | **7** |
| Nội Công | 0 | 4 | 4 | 4 | **12** |
| Ngoại Công | 3 | 3 | 3 | 0 | **9** |
| Tuyệt Kỹ | 0 | 0 | 0 | 1 | **1** |
| **Tổng** | **6** | **9** | **8** | **6** | **29** |

### Công Pháp — 28 bí kíp, 7 giai × 4 phẩm

**7 đại giai × 4 phẩm**:
| Đại Giai | Tiền | training_time_mult | Số items |
|---|---|---|---|
| Hoàng Giai | Linh Thạch | 1.0x | 4 (Sơ/Trung/Cao/Thượng) |
| Huyền Giai | Linh Thạch | 1.5x | 4 |
| Địa Giai | Ma Thạch | 2.5x | 4 |
| Thiên Giai | Ma Thạch | 4.0x | 4 |
| Tiên Giai | Ma Thạch | 7.0x | 4 |
| Thần Giai | Ma Thạch | 12.0x | 4 |
| Thánh Giai | Ma Thạch | 20.0x | 4 |

**Nguyên tố** (element): Kim / Mộc / Thủy / Hỏa / Thổ / Ngũ Hành / Vô Hành

**Hiệu ứng** (effect_type):
- `absorb_speed` — Tăng tốc độ hấp thu linh khí (có penalty ATK/DEF)
- `exp_bonus` — Tăng kinh nghiệm
- `hp_regen` — Hồi HP mỗi lượt
- `drop_rate` — Tăng tỉ lệ rơi vật phẩm
- `dmg_reduce` — Giảm sát thương nhận vào
- `heal_bonus` — Tăng hiệu quả hồi phục
- `crit_dmg` — Tăng sát thương chí mạng
- `def_bonus` — Tăng phòng ngự
- `mining_bonus` — Tăng linh thạch khi đào mỏ
- `atk_bonus` — Tăng sát thương vật lý
- `stone_bonus` — Tăng linh thạch nhận được

**Hiển thị**: Badge nguyên tố + Tên phẩm chất (Hoàng Giai Sơ Phẩm...) + Giá tiền

### UI/UX thiencoccac.php

**Tab CSS**:
- Border-radius 14px, gradient background, glow effect khi active
- Hiệu ứng `::before` radial gradient khi hover/active
- Box-shadow phát sáng

**Popup thanh toán**:
- Dùng `wg_confirm_modal()` từ `wugong_utils.php` (vanilla JS, no Zepto)
- Nút "Mua" dùng `data-wg-click` attribute → `wgConfirm()` → `window.location.href`
- Hiển thị: tên vật phẩm, giá tiền, icon ⚔/📖
- Hai nút: Hủy + Mua ngay

**Badge hiển thị trên mỗi item**:
- Wugong: rarity badge + element badge + purpose badge + phẩm chất
- Congphap: phẩm chất (Hoàng Giai Sơ Phẩm...) + element badge + cpsort

### Config fields mới

**wugong_config.json** — mỗi item có:
- `element` (kim/moc/thuy/hoa/tho/all/none)
- `category` (khinh_cong/noi_cong/ngoai_cong/tap_ky)
- `rarity` (nhap_mon/trung_cap/thuong_thua/than_cong)
- `purpose` (pk/farm/train/utility)
- `realm_require`, `training_time_mult`, `price_linhthong`, `price_masthach`

**congphap_config.json** — mỗi item có:
- `element` (kim/moc/thuy/hoa/tho/all/none)
- `tier` (1-7 giai), `subtier` (1-4 phẩm)
- `realm_require`, `training_time_mult`, `price_linhthong`, `price_masthach`

### Xóa random draw
- `game/xxwg.php` — Xóa nút "Tân thủ bí tịch" + logic random draw (case chouqu)
- `game/congphap.php` — Xóa nút "Quất Công Pháp" + logic random draw (case chouqu)

### Routes & UI
- `game.php` — Route `thiencoccac` → `game/thiencoccac.php`
- `game/nowmid.php` — Nút "📚 Thiên Cơ Các" trong tcg-actions

---

## CodeGraph Setup
- Cài đặt, cấu hình cho opencode
- Index 162 files, 1298 nodes, 4300 edges

## SQL Injection Fix
- `wugong.php:179-187` — chuyển sang prepared statements

## Shared Utilities (`wugong_utils.php`)
- `wg_safe_text()`, `wg_color_name()`, `wg_popup_html()`, `wg_info_field()`
- `wg_quality_badge_html()`, `wg_progress_bar_html()`
- `wg_confirm_modal()` — custom confirm modal (themed, backdrop blur, gradient)

## Refactor
- `wugong.php`, `xxwg.php`, `congphap.php` — dùng shared utils

## UI/UX
- Progress bar shimmer animation
- Button glow effects, quality tooltips
- Card redesign `tcg-wg-card` trong `xxwg.php` (phẩm chất + trạng thái)

## Route Changes
- Xóa Công Pháp tab khỏi `xiulian.php`
- Xóa route `gocp` khỏi `game.php`
- Thêm lại route `gocp` → `game/congphap.php` (hệ thống Công Pháp mới)
- Thêm lại tab "Công Pháp" trong `xiulian.php` (Động Thiên Linh Khí)

## Vấn Đỉnh Đài (`vandinhdai.php`)
- "TRANH TOP" → "THƯỞNG TOP"
- Reward table chuyển sang popup modal
- Ribbon "THƯỞNG TOP" trở thành nút mở popup (cursor:pointer, hover glow)
- Xóa nút "Thưởng Top" thừa trong actions area
- **CSS fix**: `.vdd-reward-overlay` — bỏ `align-items:center`, dùng `padding-top:10%` để popup không bị lệch xuống quá xa

## Files đã sửa
- `game/wugong_utils.php` (tạo mới)
- `game/wugong.php` (SQL fix + shared utils)
- `game/xxwg.php` (card redesign + shared utils)
- `game/congphap.php` (shared utils)
- `game/xiulian.php` (xóa tab Công Pháp → thêm lại tab Công Pháp)
- `game.php` (xóa route gocp → thêm lại route gocp)
- `game/vandinhdai.php` (reward popup + ribbon button + CSS fix)

## Hệ Thống Công Pháp (Mới)
- `class/config/congphap_config.json` — 28 công pháp (7 giai × 4 phẩm)
- `class/function/congphap.php` — CRUD: config, cqcp, add_exp, equip, unequip, delete, calc_buffs, effect_name
- DB `playercongphap` — (cpid, sid, cpdj, cpexp, cpexpmax, cpequipped)
- `class/player.php` — getplayer() cộng buff công pháp + penalty (absorb_speed giảm %công/%thủ)
- `game/xiulian.php` — áp dụng absorb_speed vào exp_per_sec
- `game/pve.php` — áp dụng exp_bonus khi đánh quái
- `game/boss.php` — áp dụng exp_bonus khi đánh boss
- `game/toayeuthap.php` — áp dụng exp_bonus khi leo tháp
- `game/vandinhdai.php` — áp dụng exp_bonus khi đấu arena
- `game/congphap.php` — UI quản lý: rút, trang bị, bỏ trang bị, xóa, xem chi tiết

## CSS Fix - `.cp-name` (congphap)
- `css/mystyle.css` — sửa `.cp-name` xóa viền trắng mặc định, thêm glow effect (`text-shadow` dùng `currentColor`), hover glow mạnh hơn, smooth transition, padding mở rộng vùng hover
- Responsive `.cp-name` (mobile) — cập nhật font-size + padding cho đồng bộ

---

# Hệ Thống Linh Căn & Mở Rộng

## Linh Căn (22 loại, 5 bậc)
- Bậc 1-4: Kim/Mộc/Thủy/Hỏa/Thổ + Bậc 4 thêm "All" (Ngũ Hành)
- Bậc 5: "None" (Vô Hành) - sức mạnh tuyệt đối
- `linhcan_config.json` - cấu hình chi tiết: element, stats, absorb_bonus, exp_bonus, pet bonuses
- Tương tác ngũ hành: Kim khắc Mộc → Mộc khắc Thổ → Thổ khắc Thủy → Thủy khắc Hỏa → Hỏa khắc Kim
- Combat bonus: +20% damage khi khắc, -15% khi bị khắc

## Nâng Cấp Linh Căn
- `linhcan_upgrade.php` - UI nâng cấp linh căn
- `linhcan_boss.php` - Boss linh căn

## Skill Linh Căn (6 skills)
- `linhcan.php` - quản lý skills

## Sủng Vật & Linh Căn
- Tương tác giữa sủng vật và linh căn

## Quest Linh Căn
- `linhcan_quest.php` - nhiệm vụ linh căn

---

# Hệ Thống Luyện Đan

## Files
- `game/luonden.php` - UI luyện đan (đã redesign v2 - match event design system)
- `class/function/luonden.php` - logic luyện đan
- `class/config/luonden_config.json` - 17 recipes

## Routes
- `cmd=luonden` → `game/luonden.php`
- `cmd=do_craft_luonden` → `class/function/luonden.php` (route riêng, tránh conflict)

## Logic
- 3 loại recipe: `yaopin` (thuốc), `yaodan` (đan dược), `daoju` (thẻ cường hóa)
- **Chỉ trừ nguyên liệu KHI THÀNH CÔNG** (không trừ khi fail)
- `luonden_consume_materials()` - trừ nguyên liệu
- `luonden_add_result()` - thêm kết quả vào inventory
- `luonden_success_text()` - hiển thị tỷ lệ thành công

## Bugs đã fix
- **`playerdaoju` cột**: Sửa `ypsum` → `djsum` (8 chỗ trong luonden.php và chetao.php)
- MySQL cũ không dùng `DEFAULT CURRENT_TIMESTAMP` → dùng `NOW()` trong PHP

---

# Hệ Thống Chế Tạo

## Files
- `game/chetao.php` - UI chế tạo (đã redesign - hero banner + tab navigation + accordion cards)
- `class/function/chetao.php` - logic chế tạo
- `class/config/chetao_config.json` - 8 recipes (7 equipment + 1 special)

## Routes
- `cmd=chetao` → `game/chetao.php`
- `cmd=do_craft_chetao` → `class/function/chetao.php` (route riêng)

## Logic
- Type `equipment`: dùng `addzb($sid, $zbid, $dblj)` thêm trang bị trực tiếp
- Type `special`: vật phẩm đặc biệt
- **Chỉ trừ nguyên liệu KHI THÀNH CÔNG**
- `chetao_consume_materials()` - trừ nguyên liệu
- `chetao_craft()` - logic chế tạo

## Trang bị mới (zbid 88-94)
- Hỏa Viếm Kiếm, Thạch Linh Quán, Huyền Vũ Giáp, Phong Hành Thân Giày, Hổ Huyết Quền, Linh Lung Thạch, Hỏa Tinh Giới
- Thêm vào `zhuangbei_config.json`

---

# Hệ Thống Thẻ Cường Hóa

## Items (ID 38-44)
- 7 thẻ cường hóa trong `daoju_config.json`
- Có `use_effect` trong config

## Sử dụng
- `game/djinfo.php` - thêm nút "Dùng" cho items có use_effect
- `game/use_dj.php` - handler sử dụng đạo cụ
- `css/mystyle.css` - thêm `.djinfo-btn.use-btn`

---

# Hệ Thống Trang Bị

## Bảng `playerzhuangbei`
- Cột: `zbgj/zbfy/zbhp/zbbj/zbxx`
- Thêm bằng `addzb($sid, $zbid, $dblj)`
- Config trong `zhuangbei_config.json`

---

# UI/UX Updates

## Design System (Event Page Pattern)
- Hero banner với background image + gradient overlay
- Tab navigation (active state glow)
- Accordion cards với status badges
- Footer buttons (ghost/primary)
- CSS classes: `.event-hero`, `.event-task-card`, `.event-btn`, `.event-banner`, `.event-divider`

## Luyện Đan (luonden.php) - v2
- Match design system "Nhiệm Vụ Tu Chân"
- Background image `mission-banner-v1.png` merged vào hero (opacity .25 + gradient overlay)
- Event-task-card cho mỗi recipe
- Detail box khi click recipe
- Craft button vàng gradient

## Chế Tạo (chetao.php)
- Hero banner + tab navigation (Chế Tạo / Luyện Đan / Nhân Vật)
- Accordion recipe cards
- Modern gradient CSS

## Đại Sảnh (nowmid.php)
- Thêm nút "🔥 Luyện Đan" và "⚒ Chế Tạo"

## Túi Vật Phẩm (bagyd.php)
- Thêm tab Luyện đan + chế tạo

---

# Header Navigation Updates

## Tabs
- "✬ Sự Kiện" → giờ mở `allmap` (Cẩm Nang Tu Chân - Bản đồ thế giới)
- "▤ Cẩm Nang" → giờ mở `gamnol` (Tổng Quan giới thiệu game)

## Active State Fix
- `allmap` → `$tcg_active_nav = 'event'` (thay vì 'guide')
- `gamnol` → `$tcg_active_nav = 'guide'`

## Routes mới
- `cmd=allmap` → `game/allmap.php` (Cẩm Nang Tu Chân)
- `cmd=gamnol` → `game/gamnol.php` (Tổng Quan)

## Cẩm Nang Tổng Quan (gamnol.php)
- Hero banner với background `tu-chan-banner-v2.png`
- **Hệ Thống Linh Căn**: 5 nguyên tố Kim/Mộc/Thủy/Hỏa/Thổ + mô tả ngũ hành
- **Các Hệ Thống Tính Năng**: 8 feature cards (Luyện Đan, Chế Tạo, Thú Cưng, Võ Công, Xếp Hạng, Tu Luyện, Môn Phái, Tỏa Yêu Tháp)
- **Phó Bản & Khu Vực**: giới thiệu bản đồ thế giới
- **Hướng Dẫn Cơ Bản**: 5 bước cho người mới

## NHIỆM VỤ Ribbon
- `taskguide.php` - `<div>` → `<a>` link đến `cmd=mytask`
- CSS: thêm `text-decoration:none`, `cursor:pointer`, hover effect

---

# Files Đã Sửa/Tạo

## Files mới
- `game/gamnol.php` - Cẩm Nang Tổng Quan
- `game/use_dj.php` - Handler dùng đạo cụ

## Files đã sửa
- `game/luonden.php` - Redesign v2 (match event design)
- `game/chetao.php` - Redesign (hero + tabs + cards)
- `game/nowmid.php` - Thêm nút Luyện đan/chế tạo
- `game/bagyd.php` - Thêm tab Luyện đan/chế tạo
- `game/djinfo.php` - Thêm nút "Dùng" cho use_effect
- `game/taskguide.php` - NHIỆM VỤ ribbon → link
- `game.php` - Routes mới (do_craft_luonden, do_craft_chetao, use_dj, gamnol) + nav active fix
- `class/function/luonden.php` - Fix playerdaoju cột
- `class/function/chetao.php` - Fix playerdaoju cột
- `class/config/luonden_config.json` - Tên lấy từ config tương ứng
- `class/config/chetao_config.json` - Tên lấy từ zhuangbei_config
- `class/config/daoju_config.json` - Thêm use_effect cho thẻ cường hóa
- `css/mystyle.css` - Thêm `.djinfo-btn.use-btn`, `.news-task-ribbon` hover

---

# PHP 5.6 & MySQL Cũ Constraints

## PHP 5.6
- KHÔNG dùng `??`, `<=>`, typed properties, return types
- Dùng `isset() ? x : y`
- Heredoc: KHÔNG dùng ternary `? :` bên trong `{$}` → gán biến trước

## MySQL cũ
- KHÔNG dùng `JSON` type → dùng `TEXT`
- KHÔNG dùng `DEFAULT CURRENT_TIMESTAMP` → dùng `NOW()` trong PHP
- Bảng `playerdaoju` dùng cột `djsum` (KHÔNG PHẢI `ypsum`)
- Bảng `playeryaopin` dùng cột `ypsum`

---

# Hệ Thống Thể Chất

## Tổng quan
- 12 loại Thể Chất, 10 phẩm chất, 3 giai đoạn thức tỉnh
- Scale: `1 + jingjieValue × 0.15` (realm), `1 + stage × 0.2` (awaken)
- Player realm: `$player->jingjieValue` (0-indexed vào `rangesjj` array)

## Config
- `class/config/thechat_config.json` — 12 Thể Chất: element, stats, passives, weight, description
- `class/config/thechat_upgrade_config.json` — 9 cấp nâng cấp: material, success_rate, cost, bonus
- `class/config/thechat_awakening_config.json` — 3 giai đoạn thức tỉnh: realm, material, boss, quest requirements

## Phẩm chất (10 bậc)
| Phẩm chất | Tỷ lệ | Bonus |
|---|---|---|
| Phàm Thể | 40% | 1.0x |
| Linh Thể | 25% | 1.15x |
| Huyền Thể | 15% | 1.3x |
| Địa Thể | 10% | 1.5x |
| Thiên Thể | 6% | 1.8x |
| Thánh Thể | 2.5% | 2.2x |
| Tiên Thể | 1% | 2.8x |
| Thần Thể | 0.4% | 3.5x |
| Đế Thể | 0.09% | 5.0x |
| Đạo Thể | 0.01% | 8.0x |

## Function Layer (`class/function/thechat.php`)
- `thechat_config_get($id)` — Load config theo ID
- `thechat_random($sid, $dblj)` — Random Thể Chất + phẩm chất
- `thechat_assign($sid, $dblj, $id, $quality)` — Gán cho player
- `thechat_info($sid, $dblj)` — Lấy thông tin
- `thechat_calc_bonuses($sid, $dblj)` — Tính stats + passives
- `thechat_upgrade($sid, $dblj)` — Nâng cấp (trừ vật phẩm, roll, log)
- `thechat_awaken($sid, $dblj)` — Thức tỉnh (kiểm tra realm, material, boss, quest)
- `thechat_quality_name/icon($quality)` — Display helpers
- `thechat_awaken_name/icon($stage)` — Display helpers

## DB (thêm vào `game1`)
- `thechat_id` INT — ID Thể Chất hiện tại
- `thechat_quality` INT — Phẩm chất (0-9)
- `thechat_awaken_stage` INT — Giai đoạn thức tỉnh (0-3)

## Bảng mới
- `player_thechat_log` — Lịch sử nâng cấp/thức tỉnh
- `player_thechat_awaken_log` — Lịch sử thức tỉnh

## UI Pages
- `game/thechat.php` — Chi tiết Thể Chất: stats, passives, nút nâng cấp/thức tỉnh
- `game/thechat_upgrade.php` — Nâng cấp: danh sách vật phẩm cần, tỷ lệ thành công, POST handler
- `game/thechat_awakening.php` — Thức tỉnh: yêu cầu realm/material/boss/quest, POST handler

## Routes (thêm vào `game.php`)
- `thechat` → `game/thechat.php`
- `thechat_upgrade` → `game/thechat_upgrade.php`
- `thechat_awakening` → `game/thechat_awakening.php`

## Player Integration (`class/player.php`)
- 24 properties mới: `thechat_id`, `thechat_quality`, `thechat_awaken_stage`, `thechat_name`, `thechat_icon`, `thechat_color`, `thechat_element`, `thechat_base_hp`, `thechat_base_atk`, `thechat_base_def`, `thechat_base_crit`
- 7 allowed columns: `thechat_id`, `thechat_quality`, `thechat_awaken_stage`
- 2 stat blocks trong `getplayer()`: applies thechat stats → HP/ATK/DEF/CRIT

## Combat Power (`zhuangtai.php`)
- `thechat_quality × 200 + thechat_stage × 500`

## Profile Tab (`zhuangtai.php`)
- Tab "Thể Chất" trong profile-tabs
- Hiển thị: stats + passives + awaken stage

---

# Hệ Thống Huyết Mạch

## Tổng quan
- 11 loại Huyết Mạch, 10 phẩm chất, 4 giai đoạn thức tỉnh, biến thân (transform)
- Scale: `1 + jingjieValue × 0.18` (realm), `1 + stage × 0.25` (awaken)
- Biến thân: Stage 3, tốn 20% HP, cooldown
- Stats: ATK/HP/CRIT/LIFESTEAL

## Config
- `class/config/huyetmach_config.json` — 11 Huyết Mạch: element, stats, passives, transform config
- `class/config/huyetmach_awakening_config.json` — 4 giai đoạn thức tỉnh + boss_list (11 types × 3 bosses)
- `class/config/huyetmach_transform_config.json` — 11 biến thân: effects, duration, cooldown, HP cost

## Phẩm chất (10 bậc, giống Thể Chất)
- Same tiers and multipliers as Thể Chất

## Function Layer (`class/function/huyetmach.php`)
- `huyetmach_config_get($id)` — Load config theo ID
- `huyetmach_random($sid, $dblj)` — Random Huyết Mạch + phẩm chất
- `huyetmach_assign($sid, $dblj, $id, $quality)` — Gán cho player
- `huyetmach_info($sid, $dblj)` — Lấy thông tin
- `huyetmach_calc_bonuses($sid, $dblj)` — Tính stats + passives + element_dmg + can_transform
- `huyetmach_awaken($sid, $dblj)` — Thức tỉnh (kiểm tra realm, material, boss, quest)
- `huyetmach_activate_transform($sid, $dblj)` — Biến thân (cooldown + HP cost + effects)
- `huyetmach_quality_name/icon($quality)` — Display helpers
- `huyetmach_awaken_name/icon($stage)` — Display helpers
- `huyetmach_element_name($element)` — Element display
- `huyetmach_get_material_name($djid)` — Material name lookup
- `huyetmach_get_transform($bloodlineId)` — Transform config
- `huyetmach_get_awakening_info($stage)` — Awakening requirements

## DB (thêm vào `game1`)
- `huyetmach_id` INT — ID Huyết Mạch hiện tại
- `huyetmach_quality` INT — Phẩm chất (0-9)
- `huyetmach_awaken_stage` INT — Giai đoạn thức tỉnh (0-4)
- `huyetmach_transform_cd` INT — Cooldown biến thân (timestamp)

## Bảng mới
- `player_huyetmach_awaken_log` — Lịch sử thức tỉnh
- `player_huyetmach_boss` — Boss đã đánh bại
- `player_huyetmach_quest` — Quest đã hoàn thành

## UI Pages
- `game/huyetmach.php` — Chi tiết Huyết Mạch: stats, passives, transform info, links
- `game/huyetmach_awakening.php` — Thức tỉnh: realm/material/boss/quest requirements
- `game/huyetmach_transform.php` — Biến thân: effects, duration, cooldown, HP cost

## Routes (thêm vào `game.php`)
- `huyetmach` → `game/huyetmach.php`
- `huyetmach_awakening` → `game/huyetmach_awakening.php`
- `huyetmach_transform` → `game/huyetmach_transform.php`

## Player Integration (`class/player.php`)
- 24 properties mới: `huyetmach_id`, `huyetmach_quality`, `huyetmach_awaken_stage`, `huyetmach_transform_cd`, `huyetmach_name`, `huyetmach_icon`, `huyetmach_color`, `huyetmach_element`, `huyetmach_base_atk`, `huyetmach_base_hp`, `huyetmach_base_crit`, `huyetmach_base_lifesteal`
- 7 allowed columns: `huyetmach_id`, `huyetmach_quality`, `huyetmach_awaken_stage`, `huyetmach_transform_cd`
- 2 stat blocks trong `getplayer()`: applies huyetmach stats → ATK/HP/CRIT/LIFESTEAL

## Combat Power (`zhuangtai.php`)
- `huyetmach_quality × 250 + huyetmach_stage × 600`

## Profile Tab (`zhuangtai.php`)
- Tab "Huyết Mạch" trong profile-tabs
- Hiển thị: stats + passives + element + transform status

## Upgrade Materials (daoju_config.json)
- djid 100-108: Tinh Thể Đá, Linh Huyết Đá, Huyền Phách Đá, Địa Nguyên Đá, Thiên Quang Đá, Thánh Tinh Đá, Tiên Huyết Đá, Thần Nguyên Đá, Đạo Nguyên Đá

## Biến Thân (Transform)
- Yêu cầu thức tỉnh Stage 3 (Chân Thân)
- Tốn 20% HP max khi kích hoạt
- Cooldown theo config (phút)
- Stats boost: ATK/DEF/HP multiplier + Element DMG + HP regen + Phys reduce
- Thời gian: số lượt

---

# HỆ THỐNG TRANG BỊ (REDESIGN — đồng bộ ngũ hành & phẩm chất)

## Tổng quan (làm lại toàn bộ)
- File: `class/config/zhuangbei_config.json` — **160 trang bị** (16 slot × 5 ngũ hành × 2 phẩm chất).
- Mỗi slot có đủ 5 nguyên tố (Kim/Mộc/Thủy/Hỏa/Thổ) để player chọn theo hệ mình thích → đa dạng.
- Mỗi (slot, element) có 2 phẩm chất: phẩm chất cơ bản (theo tier slot) + 1 phẩm chất cao hơn (+4 bậc, cap 10).

## Chuẩn field mới (mỗi item)
- `element`: `kim|moc|thuy|hoa|tho|all|none` — tham gia tương khắc combat.
- `rarity` (1-10): Phàm/Linh/Huyền/Địa/Thiên/Thánh/Tiên/Thần/Đế/Đạo — hệ số nhân chỉ số 1.0→8.0.
- `rarity_name`, `zbys` (màu phẩm chất): đồng bộ map `zhuangbei_rarity_map()`.
- `realm_require`: cấp độ (ulv) yêu cầu mặc (1/10/30/60/120/200/300/400/600/800 theo phẩm chất).
- `taozhuang`: nhóm thần trang theo nguyên tố (kim=1, moc=2, thuy=3, hoa=4, tho=5).
- Giữ nguyên: `zbgj/zbfy/zbhp/zbbj/zbxx`, `zbtool` (slot 1-16), `zblv`, `zbinfo`, `tuposjs`, `tupoexp`.

## Helper functions (`class/function/zhuangbei.php`)
- `zhuangbei_rarity_map()` / `zhuangbei_rarity_name()` / `zhuangbei_rarity_color()` / `zhuangbei_rarity_mult()`
- `zhuangbei_element_map()` / `zhuangbei_element_name()` / `zhuangbei_element_color()` / `zhuangbei_element_icon()`
- `zhuangbei_element_khac($a,$b)` — Kim khắc Mộc → Mộc khắc Thổ → Thổ khắc Thủy → Thủy khắc Hỏa → Hỏa khắc Kim
- `zhuangbei_get_element_multiplier($zbElement, $targetElement)` — khắc +20% (1.2), bị khắc -15% (0.85), cùng/trung lập 1.0

## Player Integration (`class/player.php`)
- `merge_player_zhuangbei_with_config()` — đảm bảo `element` (mặc định `none`), `rarity` (mặc định 1), `rarity_name` luôn tồn tại.
- Trong `getplayer()`: duyệt 16 ô trang bị, chọn `zb_element` = element của trang bị có rarity cao nhất (trong số kim/moc/thuy/hoa/tho) → `$player->zb_element`.

## Combat — Tương khắc trang bị (CÓ trong PVE & Boss)
- `game/pve.php` (block tính element multiplier): ưu tiên `$player->zb_element`, fallback `$player->linhcan_element`.
- `game/boss.php` (block tính element multiplier): tương tự.
- Dùng `zhuangbei_get_element_multiplier()` cho trang bị, `linhcan_get_element_multiplier()` cho linh căn.
- Thông báo combat: "Trang bị khắc {element}!" / "Trang bị bị khắc bởi {element}!" (màu xanh/đỏ).

## UI Hiển thị
- `game/bagzb.php` — badge phẩm chất (màu) + nguyên tố (icon+tên) dưới tên trang bị.
- `game/zbinfo.php` — badge trong zb-head + meta: Yêu cầu cảnh giới, Nguyên tố.
- `game/zhuangtai.php` — badge nguyên tố + phẩm chất dưới tên trang bị đã mặc (16 ô).
- CSS: `.zb-badges` / `.bag-item-badges` / `.zb-badge` (elem/rarity) — thêm vào `css/mystyle.css`.

## Lưu ý
- Trang bị cũ của player (zbid cũ) giờ map sang config mới (tên/chỉ số đổi) — chấp nhận vì "làm lại toàn bộ". `merge_player_zhuangbei_with_config` có fallback DB cũ nếu JSON thiếu.
- `realm_require` hiện chỉ hiển thị, CHƯA chặn mặc trang bị (có thể bổ sung sau nếu cần).

---

# BUGS & SỬA ĐỔI GẦN ĐÂY (Thể Chất / Huyết Mạch / Thiên Phú)

## BUG 1: Memory exhaustion (FATAL) — ĐÃ SỬA
- **Triệu chứng**: Mở trang nhân vật / thechat / huyetmach báo `Allowed memory size of 268435456 bytes exhausted` (256MB) tại `player.php` trong `getplayer()`.
- **Nguyên nhân**: `huyetmach_calc_bonuses()` gọi `getplayer($sid, $dblj)` — trong khi chính nó được gọi TỪ `getplayer()` → đệ quy vô hạn → tràn bộ nhớ.
- **Sửa**: `class/function/huyetmach.php` (trong `huyetmach_calc_bonuses`) đổi:
  ```php
  // TRƯỚC:
  $player = getplayer($sid, $dblj);
  $realmScale = huyetmach_realm_scale(isset($player->jingjieValue) ? $player->jingjieValue : 0);
  // SAU:
  $realmScale = huyetmach_realm_scale(isset($GLOBALS['player']) ? $GLOBALS['player']->jingjieValue : 0);
  ```
  (Pattern này `thechat_calc_bonuses` đã dùng từ trước — truy cập `$GLOBALS['player']` thay vì gọi lại `getplayer()`).
- **Lưu ý**: Các hàm `getplayer()` khác trong `thechat.php`, `linhcan.php`, `congphap.php`, `huyetmach.php` (upgrade/awaken/transform/quest) KHÔNG đệ quy vì chúng được gọi từ page handler, không từ trong `getplayer()`.

## BUG 2: Tab Thiên phú reload trang thông tin nhân vật — ĐÃ SỬA
- **Triệu chứng**: Bấm "Thiên phú" chỉ load lại trang zhuangtai, không vào được trang cộng điểm thiên phú (`game/tianfu.php`).
- **Nguyên nhân kép**:
  1. (Chính) Do BUG 1 — `tianfu.php` gọi `getplayer()` nên khi vào trang này bị crash, trình duyệt fallback về trang profile đã render.
  2. Thiên phú chỉ nằm ở block `profile-actions` dưới cùng, không có trong `profile-tabs` đầu trang.
- **Sửa**: Thêm tab "Thiên phú" vào `profile-tabs` của `game/zhuangtai.php`:
  - Thêm biến `$menuTianfuClass = ($cmd == 'tianfu') ? ' active' : '';`
  - Thêm `<a class="profile-tab$menuTianfuClass" href="?cmd=$tianfu">Thiên phú</a>` vào block tabs (giữa Thần trang và Linh Căn).
- **Logic link Thiên phú** (`zhuangtai.php` đầu file):
  ```php
  if ($player->ulv >= 30){
      $tianfu = $encode->encode("cmd=tianfu&sid=$sid");
  } else {
      $tianfu = $encode->encode("cmd=zhuangtai&jzjr=xy&sid=$sid"); // reload + thông báo cấp < 30
  }
  ```

## BUG 3: Xóa profile-actions thừa — ĐÃ XÓA
- **Yêu cầu**: User muốn xóa block `profile-actions` (Túi đồ / Thiên phú / Tu luyện) cho gọn code.
- **Xóa**:
  - Block HTML `<div class="profile-actions">...</div>` trong `zhuangtai.php`.
  - Biến `$getbagzbcmd` (line 23) và `$xiuliancmd` (line 292) — trở nên không dùng nữa.
- **Giữ lại**: `$tianfu` vì vẫn dùng cho tab Thiên phú.
- **An toàn**: Túi đồ (`cmd=getbagzb`) và Tu luyện (`cmd=goxiulian`) vẫn truy cập được từ menu Đại Sảnh (`game/nowmid.php`), KHÔNG mất chức năng.
- **Lint**: `php -l zhuangtai.php` → No syntax errors.

## Trạng thái kiểm tra
- ✅ Tất cả file đã sửa lint PHP 5.6: không lỗi cú pháp (`zhuangtai.php`, `huyetmach.php`).
- ✅ Không còn đệ quy `getplayer()` trong chuỗi load `getplayer()`.
- ✅ Tab Thiên phú hoạt động (cần F5 refresh sau khi sửa BUG 1).

---

# PHIÊN LÀM VIỆC — POPUP & UX TRANG BỊ (tiếp theo redesign trang bị)

## 1. Tên trang bị tu chân + fix BOM + fix data player
- Redesign `zhuangbei_config.json` (160 items) với tên chuẩn theo slot (mũ=Quan, bracer=Hộ Thủ, cổ=Cổ Ngọc, vai=Vai Khôi...). Tên cũ giữ đúng slot (Kim Cô Thiết Bổng=vũ khí, Thánh Thuẫn Thuật=khiên, Hộ Pháp Diệt Ma=mũ, Ngũ Đinh Khai Sơn=giáp, Nguyên Thần Cấm Cố=giày).
- **BUG đã sửa**: file JSON bị gắn BOM (`efbbbf`) đầu file → `json_decode` báo "Syntax error". Đã gỡ BOM.
- **BUG data player**: làm lại config gây `zbid` đánh số lại → `player.tool{i}` (ô mũ) trỏ nhầm sang `zbid` cũ thuộc slot khác (vd ô mũ hiện "Hộ Uyển"). Đã chạy script PHP dọn 16 ô lệch slot của player (set tool=0).

## 2. Chặn mặc trang bị theo cảnh giới (realm_require)
- `zhuangtai.php` handler `setzbwz`: thay điều kiện cũ `zblv - ulv > 5` bằng check `realm_require > ulv`. Nếu không đủ → chặn + popup lỗi.
- `zbinfo.php`: thêm nút "Trang bị" (xanh) khi đủ cảnh giới, hoặc nhãn đỏ "Cần cảnh giới cấp X mới có thể trang bị" khi chưa đủ.
- `merge_player_zhuangbei_with_config` / `hydrate_zhuangbei_config_to_object` đã copy `realm_require` từ config (không cần sửa thêm).

## 3. Popup hiện TẠI trang túi đồ (không chuyển sang nhân vật)
- `bagzb.php`: kiểm tra `realm_require` ngay trong túi. Đủ → link `setzbwz`; **không đủ** → nút "Trang bị" (màu xám `locked`) bấm vào hiện popup tại chỗ, không redirect.
- Thêm `$dialogLib` (css + zepto + dialog.min.js) vào trang túi. CSS `.bag-btn.equip.locked`.

## 4. Chân trang zhuangtai.php
- Nút "← Trở về" → **"← Đại Sảnh"** (`game.php?cmd=$gonowmid`), mũi tên quay trái.
- Nút "Đại Sảnh" cũ → **"Túi đồ"** (`?cmd=$getbagzbcmd`). Thêm `$getbagzbcmd`.

## 5. Làm đẹp popup (dialog.css)
- Overwrite `chajian/tishikuang/style/dialog.css` theme tu chân (glassmorphism tím/xanh, glow, icon tròn, animation pop-in). Backup cũ: `dialog.old2.css`.
- **LƯU Ý QUAN TRỌNG**: `dialog.min.js` (`initBox`) set **inline** `width/height/margin/left/top` từ config → CSS KHÔNG được đụng các thuộc tính đó (bị ghi đè). CSS chỉ style nền/viền/chữ/icon. Kích thước truyền qua lời gọi popup (`width/height`).
- Phải có `position: fixed; left:50%; top:50%` trong `#animationTipBox` (JS chỉ set margin, không set position) → nếu thiếu popup rớt khỏi màn hình.

## 6. Nút Xác nhận / Hủy trong popup
- **LỖI ĐÃ TRÁNH**: không được nhét HTML thô (`<div class=...>`) vào tham số `msg` của `dialog.min.js` — thư viện in `msg` dạng text → chuỗi JS vỡ, toàn bộ code tràn ra ngoài trang ("loạn HTML").
- Tự tạo popup DOM qua zepto thay vì dùng `msg`:
  - `tcgConfirm(msgHtml, yesHref)` — popup hỏi, icon "?" vàng, 2 nút **Xác nhận** (chuyển trang) / **Hủy bỏ** (đóng). Dùng cho nút **"Phân Giải"** (thay thế `confirm()` native xấu).
  - `tcgAlert(msgHtml)` — popup 1 nút **Xác nhận**, **không tự đóng** (chỉ đóng khi bấm nút/nền). Dùng cho popup lỗi cảnh giới (`setzbwz` + túi đồ). Icon "!" đỏ.
- CSS: `.tcg-confirm-icon` (vàng), `.tcg-alert-icon` (đỏ), `.tcg-popup-btns`, `.tcg-popup-ok` (xanh), `.tcg-popup-cancel` (xám).
- `tcgConfirm`/`tcgAlert` định nghĩa trong `$dialogLib` (bagzb) và `$tssb` (zhuangtai). `tcg_error_popup()` trong zhuangtai gọi `tcgAlert`.

## 7. Trạng thái hiện tại
- ✅ Túi đồ: bấm "Trang bị" item đủ cấp → mặc bình thường; item thiếu cảnh giới → popup đỏ "Cảnh giới không đủ! ... Không thể trang bị!" + nút Xác nhận (không tự đóng).
- ✅ Nút "Phân Giải" → popup xác nhận 2 nút (Xác nhận/Hủy bỏ).
- ✅ Chân trang nhân vật: Đại Sảnh (trái) / Túi đồ.
- ✅ Popup style đồng bộ theme game, căn giữa, nền mờ nhẹ.
- ✅ Lint PHP 5.6: `bagzb.php`, `zhuangtai.php` không lỗi.

## File đã sửa trong phiên này
- `class/config/zhuangbei_config.json` (tên tu chân + gỡ BOM)
- `game/bagzb.php` (check realm tại túi, tcgConfirm, tcgAlert, dialogLib, chân trang không đổi ở đây)
- `game/zbinfo.php` (nút Trang bị / nhãn cảnh giới)
- `game/zhuangtai.php` (setzbwz check realm_require + popup, chân trang Đại Sảnh/Túi đồ, tcg_error_popup, tcgAlert trong $tssb)
- `chajian/tishikuang/style/dialog.css` (theme tu chân + .tcg-* classes)
- `css/mystyle.css` (.bag-btn.equip.locked, .bag-btn.use-pill)
- Script DB: reset tool player lệch slot (chạy 1 lần)

---

## Đan Tẩy Tủy / Tố Thể / Tẩy Linh — Roll Hệ Từ Đạo Cụ

### Mục đích
- Cho phép người chơi CHƯA có Thể Chất / Linh Căn / Huyết Mạch có cơ hội mở hệ bằng đan dược (đạo cụ).
- Phẩm chất giới hạn 1-3 (thường ~ tốt 1 chút) để không phá cân bằng.

### Config đạo cụ (class/config/daoju_config.json)
- `djid=112` Tẩy Tủy Đan — `djuse=thechat`
- `djid=113` Tố Thể Đan — `djuse=linhcan`
- `djid=114` Tẩy Linh Đan — `djuse=huyetmach`
- Field `djuse` nhận diện loại đan; `hydrate_daoju_config_to_object()` tự copy mọi key config → object `$daoju->djuse` dùng được.

### Hàm `usedaoju($djid,$djsum,$sid,$dblj)` (class/player.php)
- Lấy `getdaoju()`; nếu không có `djuse` → trả lỗi "không thể sử dụng".
- **Giới hạn phẩm chất**: roll ra hệ chỉ được phẩm **1-3** (Phàm/Linh/Huyền), không quá 3 (`min(q,3)` cho thechat/huyetmach; linhcan random type, mặc định phẩm 1).
- **Giới hạn số lần dùng**: đọc `playerdaoju.djused`, nếu `>= 2` → báo "đã dùng tối đa 2 lần".
- `thechat`: nếu `thechat_id>0` → báo đã có. `thechat_roll()` → clamp `quality = min(q,3)` → `thechat_assign($sid,$dblj,$id,$quality)`.
- `linhcan`: nếu `linhcan_id>0` → báo đã có. `linhcan_random()` → `linhcan_assign($sid,$dblj,$id)` (KHÔNG quality).
- `huyetmach`: nếu `huyetmach_id>0` → báo đã có. `huyetmach_roll()` → clamp `quality=min(q,3)` → `huyetmach_assign($sid,$dblj,$id,$quality)`.
- Trừ 1 đạo cụ + tăng `djused`: `UPDATE playerdaoju SET djsum=djsum-1, djused=djused+1 WHERE sid=? AND djid=? AND djsum>=1`.
- Trả `array('ok'=>bool,'msg'=>string,'name'=>..,'quality'=>..)`.

### Giao diện (game/bagdj.php)
- Loop túi đạo cụ: nếu `getdaoju()->djuse != ''` → hiện nút **Dùng** (class `.bag-btn.use-pill`, màu hổ phách).
- Cmd `usedaoju`: gọi `\player\usedaoju()`, hiện popup **tcgAlert** (tự tạo DOM qua zepto, không nhét HTML thô vào dialog.min.js) — thành công class `success` (✓), thất bại class `error` (!).

### Lưu ý quan trọng
- KHÔNG dùng `dialog.min.js` initBox cho popup này (nó set inline size + in text → loạn HTML). Tự tạo `#animationTipBox` qua zepto, bắt buộc inline `width/height/margin-left/margin-top`, CSS chỉ trang trí.
- `tcgAlert` popup không tự đóng, có nút Xác nhận + click mask để tắt.

### Giới hạn sử dụng (mới)
- Mỗi đan (Tẩy Tủy/Tố Thể/Tẩy Linh) chỉ dùng **tối đa 2 lần** (đếm bằng cột `playerdaoju.djused`, khóa sid+djid).
- **Giới hạn phẩm chất**: hệ roll ra chỉ phẩm **1-3 (Phàm/Linh/Huyền)**, không quá 3.
- Nút "Dùng" hiển thị số lần còn lại `(n)`; khi hết 2/2 hiện nhãn "Đã dùng 2/2".

### DB
- `ALTER TABLE playerdaoju ADD COLUMN djused int(11) NOT NULL DEFAULT 0;`

### Lint
- ✅ PHP 5.6: `class/player.php`, `game/bagdj.php` không lỗi.

---

## Cửa Hàng (shangdian.php) — Chia 3 Mục

### Tổng quan
- Refactor `game/shangdian.php` thành **3 tab**: **Đan Dược / Đạo Cụ / Trang Bị**.
- Tiền tệ: vật phẩm **thường (phẩm thấp) = Linh thạch**, **phẩm cao = Ma thạch**.

### Đan Dược (giữ nguyên)
- 2 sub-flow cũ: Linh thạch (`canshu=gogoumai`) / Ma thạch (`canshu1=gogoumai1`), lấy từ `getyaodan()`, giá `ydjg`/`ydjgm`.

### Đạo Cụ
- 2 sub-tab **Linh Thạch** (`canshu2=gogoumai2`) / **Ma Thạch** (`canshu21=gogoumai2ma`), lấy từ `daoju_config_all()`, **loại trừ 3 đan** (djid 112/113/114).
- Giống đan dược: CÙNG danh sách đạo cụ hiện trong cả 2 sub-tab, chỉ đổi tiền tệ. Giá = `max(50, djyxb)`, trả Linh (`changeyxb`) hoặc Ma (`changeczb`) theo sub-tab đã chọn.
- Mua: `adddj($sid,$djid,1,$dblj)`.

### Trang Bị
- 2 sub-tab **Linh Thạch** (`canshu3=gogoumai3`) / **Ma Thạch** (`canshu31=gogoumai3ma`), lấy từ `zhuangbei_config_all()` (160 items).
- Giống đan dược: CÙNG danh sách trang bị hiện trong cả 2 sub-tab, đổi tiền tệ. Giá: Linh = `rarity*100`, Ma = `(rarity-3)*50+200`.
- Mua: `changeyxb/changeczb` rồi `addzb($sid,$zbid,$dblj)`.

### Lưu ý
- Các hàm dùng: `getyaodan`, `getyaodanonce`, `daoju_config_all`, `zhuangbei_config_all`, `getzbkzb`, `adddj`, `addzb`, `changeyxb`, `changeczb`.
- Popup thanh toán giữ nguyên `tcg_shop_payment_modal` (class gold/purple theo loại tiền).

### Thanh tìm kiếm & bỏ box số dư
- **Bỏ** `.tcg-shop-wallet` (box số dư Linh/Ma thạch) — không cần thiết.
- Thêm form tìm kiếm `tcg-shop-search` (GET, `name=kw`), submit về cmd của mục/sub-tab đang chọn (`$searchBaseCmd`).
- Lọc qua `tcg_shop_filter_kw($list, $nameKey)` (mb_stripos, không phân biệt hoa/thường) áp dụng cho đan dược(`ydname`)/đạo cụ(`djname`)/trang bị(`zbname`).
- `kw` được truyền tiếp qua phân trang (`tcg_shop_page_cmd`) và các link pay/info/confirm (`$kwParam`) để giữ filter sau khi mua/xem.
- Khi search không khớp → hiện thông báo "Không tìm thấy vật phẩm..." (guard `$shopEntered` để không bị ghi đè bởi list mặc định).
- **FIX bug "nhấn Tìm bị thoát về đăng nhập"**: form dùng `action="?cmd=..."` (relative) → khi game được phục vụ tại `/` (index.php include game.php) thì relative resolve thành `/?cmd=...` → index.php → login. Sửa bằng: action = `$_SERVER['REQUEST_URI']` đã strip `kw` (đường dẫn tuyệt đối, giữ script path) + JS `tcgShopSearch()` navigate qua `window.location.href` (giữ nguyên full URL, xóa/set param `kw`). Link "Xóa" cũng dùng `$searchAction` tuyệt đối.

### Lint
- ✅ PHP 5.6: `game/shangdian.php` không lỗi.

---

## BUG FIX — Nút "Dùng" trong túi đạo cụ không hoạt động + icon → số lượng

### Triệu chứng
- User phản ánh: trong túi đạo cụ (Đạo Cụ) cứ phải nhấn vào tên → Thông tin đạo cụ mới dùng được; nút "Dùng" cạnh "Bán" như không có tác dụng.
- Icon `<div class="bag-item-icon">✦</div>` chỉ hiện ký tự ✦, không cho biết số lượng.

### Nguyên nhân (2 lỗi)
1. **Thiếu route `usedaoju` trong `game.php`**: nút "Dùng" link `?cmd=usedaoju&djid=...` nhưng map routing không có key `usedaoju` → game.php fallback về scene chính (toàn bộ hành động dùng đan bị nuốt). Đã thêm:
   ```php
   'usedaoju'=>'game/bagdj.php', // Dùng đạo cụ (đan) từ túi
   ```
   (ngay sau `'getbagdj'=>'game/bagdj.php'`).
2. **Thứ tự query `$retdj`**: handler `if ($cmd == 'usedaoju')` (đầu file) chạy TRƯỚC câu `SELECT djid,djsum,djused FROM playerdaoju` → `$retdj` rỗng → `$djsum=0` → `usedaoju()` báo "Không có đạo cụ này". Đã dời query `$retdj` lên ngay sau `getplayer()` (trước các handler `maichu`/`usedaoju`) và xóa query trùng ở cuối.

### Sửa đổi `game/bagdj.php`
- Dời block query `playerdaoju` lên đầu (sau load player).
- `<div class="bag-item-icon">✦</div>` → `<div class="bag-item-icon">'.$djsum.'</div>` (hiện số lượng vật phẩm ngay trên icon box, đỡ phải vào chi tiết xem có bao nhiêu). Đồng thời bỏ "...Số lượng xN" thừa trong meta (đã hiện trên icon).
- Nút "Dùng" (`.use-pill`, chỉ cho đan có `djuse`) nằm cạnh "Bán 1" — giờ đã thực sự gọi được `usedaoju`.

### Kiểm tra thực tế
- Test curl: thêm djid 112 (Tẩy Tủy Đan) cho player test, gọi `cmd=usedaoju&djid=112` → popup "May mắn! Người đạt được Linh Thể (Huyền Phẩm)". DB: `djsum=0, djused=1` ✅.
- Bag render đúng: icon box hiện `1`, nút `Dùng (2)` cạnh `Bán 1`.

### Files
- `game.php` (thêm route `usedaoju`)
- `game/bagdj.php` (thứ tự query + icon số lượng)

### Lint
- ✅ PHP 5.6: `game/bagdj.php`, `game.php` không lỗi.

---

## TÚI ĐẠO CỤ — Nút "Dùng" cho MỌI vật phẩm có thể dùng

### Bối cảnh
- User thấy item `djid=8` (Ngũ Sắc Phong Mật — vật liệu bán thô) chỉ có nút "Bán 1", không có "Dùng". Đúng: item này không có `djuse` cũng không `use_effect` → chỉ bán.
- Yêu cầu: nút "Dùng" cạnh "Bán" cho **mọi vật phẩm dùng được**, dùng nhanh không cần vào Thông tin.

### Mở rộng `game/bagdj.php` (item loop)
- Trước: chỉ hiện "Dùng" khi `\player\getdaoju()->djuse != ''` (3 đan: Tẩy Tủy/Tố Thể/Tẩy Linh → `cmd=usedaoju`).
- Nay: thêm phân nhánh `use_effect` (thẻ cường hóa / vật phẩm tăng stat, xử bởi `game/use_dj.php`):
  ```php
  $canUseDjuse   = ($daojuCfg && isset($daojuCfg->djuse) && $daojuCfg->djuse != '');
  $canUseEffect  = ($daojuCfg && isset($daojuCfg->use_effect) && !empty($daojuCfg->use_effect));
  // djuse  -> cmd=usedaoju  (hiện "Dùng (n)" / "Dùng (2/2)")
  // use_effect -> cmd=use_dj (hiện "Dùng")
  ```
- `hydrate_daoju_config_to_object()` (class/function/daoju.php) copy mọi key config → object, nên `$daojuCfg->use_effect` truy cập được.
- Bỏ gọi `getdaoju()` 2 lần (tái dùng `$daojuConfig` đã lấy tên).

### Hiển thị notice từ `use_dj.php`
- `use_dj.php` redirect về `cmd=getbagdj&use_notice=...&use_type=...`. `bagdj.php` đọc `$_GET['use_notice']` → gán `$tishi` → render `<div class="bag-notice">...</div>`.

### Kiểm tra thực tế
- Test player: thêm djid 38 (thẻ cường hóa ATK, có `use_effect`). Bag render đúng: djid 38 có nút "Dùng" (`cmd=use_dj`), djid 8 KHÔNG có.
- Gọi `cmd=use_dj&djid=38` → redirect notice "Sử dụng Thẻ Cường Hóa ATK +5 thành công! Công Kích +5" (type ok). Bag hiển thị notice ✅.
- djid 112 (Tẩy Tủy Đan, `djuse`) → nút "Dùng (2)" → `usedaoju` (đã test phiên trước).

### Lưu ý
- Vật liệu/thuốc không có `djuse`/`use_effect` → chỉ "Bán 1" (đúng thiết kế).
- `use_dj` đã có route `'use_dj'=>'game/use_dj.php'` từ trước.

### Files
- `game/bagdj.php` (phân nhánh djuse/use_effect + đọc use_notice)

### Lint
- ✅ PHP 5.6: `game/bagdj.php` không lỗi.

---

## TÚI ĐẠO CỤ — SẮP XẾP VẬT PHẨM DÙNG ĐƯỢC LÊN ĐẦU

### Yêu cầu
- Các vật phẩm **có thể dùng** (có nút "Dùng": `djuse` hoặc `use_effect`) ưu tiên hiển thị lên đầu danh sách, vật liệu/bán thô nằm dưới.

### Triển khai (`game/bagdj.php`)
- Thêm hàm `is_daoju_usable($djid, $dblj)`: trả true nếu `\player\getdaoju()->djuse != ''` hoặc `->use_effect` không rỗng.
- Sau khi fetch `$retdj`, `usort` với khóa: usable = 0, không = 1 (0 lên trước).
- Sort ổn định: gắn `$retdj[$k]['_idx'] = $k` trước sort, dùng làm tiebreaker khi cùng nhóm → giữ thứ tự nguyên bản trong mỗi nhóm.

### Kiểm tra
- Test player có Tố Thể Đan (djuse, usable) + Ngũ Sắc Phong Mật (material): bag render #1 = Tố Thể Đan (có use-pill), #2 = Ngũ Sắc Phong Mật (không) ✅.

### Lint
- ✅ PHP 5.6: `game/bagdj.php` không lỗi.

---

## BUG NGHIÊM TRỌNG — Dùng đan TẨY TỦY luôn bị "tiêu hao + ghi đè", và có thể thoát login

### Triệu chứng (user)
- Dùng 1 viên Tẩy Tủy Đan trong túi → đan **mất**, **không thấy thay đổi** (hoặc bị đẩy về trang đăng nhập).

### Nguyên nhân gốc (QUAN TRỌNG)
- `getplayer()` (class/player.php ~dòng 738) **luôn reset** `$player->thechat_id = 0` (cùng `thechat_quality`, `huyetmach_id`, ...) trước khi tính buff hiển thị qua `thechat_calc_bonuses()`.
- Hàm `usedaoju()` (class/player.php) kiểm tra "đã có hệ chưa" bằng `if (intval($player->thechat_id) > 0)` → do `getplayer` vừa reset về 0 → **luôn sai** → luôn vào nhánh roll + `thechat_assign()` → **luôn tiêu hao đan và ghi đè Thể Chất cũ** (mất đan, "không đổi" vì roll ra hệ tương tự / hoặc user đã có).
- Tương tự `linhcan_id` / `huyetmach_id` cũng bị reset → cùng lỗi cho Tố Thể Đan / Tẩy Linh Đan.

### Sửa (`class/player.php` — `usedaoju()`)
- Sau `getplayer()`, đọc giá trị thật từ DB:
  ```php
  $stmt = $dblj->prepare("SELECT thechat_id, linhcan_id, huyetmach_id FROM game1 WHERE sid = ?");
  $stmt->execute(array($sid));
  $realIds = $stmt->fetch(\PDO::FETCH_ASSOC);
  $realThechat = $realIds ? intval($realIds['thechat_id']) : 0;
  $realLinhcan = $realIds ? intval($realIds['linhcan_id']) : 0;
  $realHuyetmach = $realIds ? intval($realIds['huyetmach_id']) : 0;
  ```
- Đổi 3 chỗ check "đã có": `$realThechat > 0` / `$realLinhcan > 0` / `$realHuyetmach > 0` (thay vì `$player->thechat_id`...).

### Sửa phòng thủ thêm (`usedaoju()`)
- Chỉ `UPDATE ... djsum=djsum-1` sau khi `thechat_assign/linhcan_assign/huyetmach_assign` trả về `true` (capture `$assigned`). Nếu gán thất bại → trả lỗi "Gán hệ thất bại, đan không bị tiêu hao", **không trừ đan**. (Tránh đan mất mà không gán được.)

### Sửa link túi đạo cụ (`game/bagdj.php`)
- Đổi các link hành động (Xem/Tên, Bán, Dùng) từ relative `?cmd=...` → absolute `$self.'?cmd=...'` với `$self = $_SERVER['SCRIPT_NAME']` (mặc định `game.php`). Phòng trường hợp game phục vụ tại `/` (root) mà relative `?cmd=` resolve thành `/?cmd=` → `index.php` (trang login) → bị đẩy về đăng nhập. Khớp với cách fix shangdian (dùng `$_SERVER['REQUEST_URI']`).

### LƯU Ý — timeout login (game.php ~dòng 455)
- `game.php` có check `$second = floor((strtotime($now)-strtotime($player->endtime))%1000); if ($second>=900) header('refresh:1;url=index.php')`. Modulo `%1000` là logic lạ (chỉ kick trong cửa sổ [900,1000) mỗi 1000s). **KHÔNG sửa thành `$second = now-endtime`** vì sẽ kick nghiêm ngặt sau 15 phút idle (nhiều hơn). Giữ nguyên `%1000` để không đổi behaviour. Nếu user hay bị logout do idle, cân nhắc nới ngưỡng riêng.
- Test env: PHP `Asia/Shanghai` (+8) còn MySQL `NOW()` lệch ~1h → test set `endtime = DATE_ADD(NOW(), INTERVAL 2 HOUR)` để bypass (do `endtime` thực tế được game ghi bằng PHP `date()` nên môi trường thật đồng bộ, không bị ảnh hưởng).

### Kiểm tra thực tế (test player)
- **Đã có Thể Chất (thechat_id=5)**: dùng Tẩy Tủy Đan → popup "Ngươi đã có Thể Chất", **đan KHÔNG tiêu hao** (djsum giữ nguyên), thechat_id=5 giữ nguyên ✅.
- **Chưa có Thể Chất**: dùng → đan tiêu hao (djsum 1→0, djused 0→1), thechat_id gán mới (vd id 1, quality 2) ✅.
- Link "Dùng" render absolute `/game.php?cmd=...` ✅.

### Files
- `class/player.php` (`usedaoju()`: đọc ID thật từ DB + guard tiêu hao)
- `game/bagdj.php` (link absolute `$self`)

### Lint
- ✅ PHP 5.6: `class/player.php`, `game/bagdj.php` không lỗi.

---

## BUG HIỂN THỊ — Trang Thể Chất luôn báo "Bạn chưa có Thể Chất" dù đã dùng đan

### Triệu chứng (user)
- Dùng Tẩy Tủy Đan xong, thoát/đăng nhập lại → vào tab Thể Chất vẫn thấy "Bạn chưa có Thể Chất" (dù đan đã tiêu, DB đã gán `thechat_id`).

### Nguyên nhân gốc
- `getplayer()` (class/player.php) **reset `$player->thechat_id = 0`** (và `huyetmach_id = 0`) trong block BUFF, trước khi `thechat_calc_bonuses()` tính display.
- `thechat_calc_bonuses()` đọc `thechat_id` TỪ DB (qua `thechat_info`) nên việc reset `$player->thechat_id` là **thừa và có hại**.
- Các trang `game/thechat.php`, `game/thechat_upgrade.php`, `game/thechat_awakening.php` (và hàm nâng cấp/thức tỉnh trong `class/function/thechat.php`) đọc `$player->thechat_id` để biết "đã có hay chưa" → vì luôn = 0 → luôn hiển thị "chưa có", dù DB có thật.
- (Trang `zhuangtai.php` dùng `$player->thechat_name` nên hiển thị đúng; user hay xem tab Thể Chất riêng nên thấy sai.)

### Sửa (`class/player.php` — `getplayer()`)
- **XÓA** 2 dòng reset id: `$player->thechat_id = 0;` (block Thể Chất) và `$player->huyetmach_id = 0;` (block Huyết Mạch). Giữ nguyên reset `thechat_quality/awaken_stage/name/...` (các field đó được ghi đè từ DB qua `*_calc_bonuses`).
- `linhcan_id` vốn không bị reset → giữ nguyên.
- Sau sửa: `$player->thechat_id`/`huyetmach_id` giữ giá trị thật từ `player_allowed_columns` → các trang hiển thị và hàm nâng cấp/thức tỉnh đọc đúng.

### Kiểm tra thực tế (test player)
- `thechat_id=0` → `game/thechat.php` hiện "Bạn chưa có Thể Chất" ✅.
- `thechat_id=1` → `game/thechat.php` hiện đầy đủ thẻ Thể Chất (`tc-name`) ✅.
- `thechat_upgrade.php` / `thechat_awakening.php` render đúng (không lỗi) ✅.
- Dùng đan vẫn đúng: không Thể Chất → gán+tiêu hao; đã có → không tiêu, giữ nguyên ✅.

### Lint
- ✅ PHP 5.6: `class/player.php` không lỗi.

---

## BUG TIMEOUT — Thoát/login giữa chừng do logic `%1000` sai

### Triệu chứng (user)
- Đang dùng đan / thao tác → bị đẩy về `index.php` (login) bất thường.

### Nguyên nhân
- `game.php` ~dòng 453: `$second = floor((strtotime($now)-strtotime($player->endtime)) % 1000); if ($second >= 900) kick;`
- Modulo `%1000` khiến kick chỉ fire trong cửa sổ [900,1000) của mỗi 1000s → hành vi không nhất quán, khó đoán, và có thể "nuốt" action của user (kick xảy ra TRƯỚC khi cmd được xử lý vì check nằm đầu request).

### Sửa (`game.php`) — LẦN 2 (theo phản hồi user)
- User phản hồi: "đang chơi vẫn bị đẩy login, timeout cái gì" → ngưỡng 15 phút (900s) quá nghiêm ngặt: ngồi trong túi đồ đọc/chọn đan 15+ phút rồi bấm "Dùng" → đúng lúc đó timeout fire, **đá ra login TRƯỚC khi `usedaoju` chạy** → đan không dùng, về login thấy "chưa có Thể Chất".
- Đổi thành:
  - `endtime` được **làm mới trên MỌI request hợp lệ** (không nằm trong khối `else` nữa) → người chơi đang hoạt động (kể cả chỉ ngồi đọc túi đồ) **không bao giờ bị kick**.
  - Chỉ kick khi `$second >= 3600` (1 tiếng rảnh thực sự, không có request nào). Ngưỡng này đủ rộng để thao tác bình thường không bao giờ bị đá; vẫn dọn session bỏ rơi sau 1 tiếng.

### Lint
- ✅ PHP 5.6: `game.php` không lỗi.

### Verify
- Set `endtime='2026-07-21 03:00:00'`, gọi `getbagdj` (token hợp lệ) → `kicked? False`, `endtime` làm mới thành thời gian hiện tại (03:05:24) ✅.
- Tăng ngưỡng từ 900s → 3600s; active/pause <1h không bị đá.

### LƯU Ý test env
- PHP `date()` = `Asia/Shanghai` (+8), MySQL `NOW()` = +7 (VN). Khi test set `endtime` bằng MySQL `DATE_SUB/NOW()` thì PHP đọc lệch +1h → `$second` tính sai. Dùng token hợp lệ (file `gtok.php` tạm) để test refresh; set `endtime` bằng chuỗi cố định (`'2026-07-21 03:00:00'`) rồi gọi request → phải thấy `endtime` đổi thành giờ hiện tại. Môi trường thật endtime ghi bằng PHP `date()` nên đồng bộ.

### LƯU Ý test env
- PHP `date()` = `Asia/Shanghai` (+8), MySQL `NOW()` = +7 (VN). Khi test set `endtime` bằng MySQL `DATE_SUB/NOW()` thì PHP đọc lệch +1h → `$second` tính sai (lớn hơn thực tế). Test kick bằng cách set `endtime = DATE_ADD(NOW(), INTERVAL 2650 SECOND)` (tương đương +8 now trừ 950s) để rơi vào vùng kick. Môi trường thật endtime ghi bằng PHP `date()` nên đồng bộ, không lệch.

---

## XSS FIX SESSION — 2026-07-21

### Tổng quan
- Fix **Stored XSS** trên toàn bộ chat system (`ggliaotian` table) + battle heredocs + UI display
- Pattern: `$player->uname`, `$boss->bossname`, `$cw->cwname`, `$umsg` → `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` trước khi chèn vào HTML/DB

### Files đã sửa (7 files)

#### 1. `game/boss.php`
- **4 heredocs**: escape `$player->uname`, `$boss->bossname`, `$cw->cwname` (victory, defeat, battle arena)
- **3 chat inserts** (ggliaotian): escape player/boss name trong tin nhắn hệ thống
- Thêm `$playerSafe/$bossSafe/$playerNameSafe/$bossNameSafe/$playerMsgSafe/$bossMsgSafe/$cwNameSafe` aliases

#### 2. `game/pve.php`
- **1 chat insert**: escape `$player->uname` trong reward notification
- **1 heredoc**: escape `$player->uname` trong battle display

#### 3. `game/pvp.php`
- **1 SQL injection + Stored XSS**: raw `$msg` trong `INSERT INTO ggliaotian` → escaped `$safeP1/$safeP2` + prepared statement
- Regex: `$dieinfo` array dùng `$player->uname`/`$pvper->uname` → escape trước khi insert DB

#### 4. `game/nowmid.php`
- **2 heredocs**: escape `$boss->bossname` (boss pill links trên bản đồ)
- **2 chat display**: escape `$umsg` (tin nhắn chat world)
- **1 DB write**: escape `$player->uname` trong `playerinfo` (movement notification)
- **1 heredoc**: escape `$club->clubname` (bang hội display)

#### 5. `game/linhcan_boss.php`
- **1 chat insert**: escape `$player->uname` trong linh căn boss kill notification

#### 6. `game/zhuangtai.php`
- **1 heredoc**: escape `$player->uname` trong profile display

#### 7. `game/tupo.php`
- **`tupo_result()` function**: added `htmlspecialchars()` cho `$type`, `$title`, `$body` — fix stored XSS từ breakthrough results
- Affects: `$player->uname` trong failure messages (lines 142, 177) — now auto-escaped via function

### XSS Risk Assessment (Before → After)

| Attack Vector | Risk Before | Risk After |
|---------------|-------------|------------|
| Player register `<script>alert(1)</script>` as name | **CRITICAL** — executes in every chat, battle, profile page | **MITIGATED** — escaped at output layer |
| Boss name crafted XSS | N/A (admin set) | Mitigated |
| Chat message stored XSS | **HIGH** — persists in ggliaotian | **MITIGATED** — both display + DB insert escaped |
| Pet name XSS | Low (player set) | Mitigated |
| Club name XSS | Low (admin set) | Mitigated |

### Files NOT modified (already safe)
- `game/liaotian.php` — uses `tcg_chat_player_link()` → `tcg_chat_escape()`
- `game/ltmsg.php` — `$uname` escaped, `$umsg` escaped
- `game/bossinfo.php` — uses `tcg_bossinfo_e()`
- `game/otherzhuangtai.php` — uses `tcg_other_e()`
- `game/im.php` — uses `htmlspecialchars()`
- `game/paihang.php` — uses `htmlspecialchars()`
- `game/wugong.php` — uses `wg_safe_text()`
- `game/vandinhdai.php` — uses `vdd_h()`
- `game/xiulian.php` — uses `htmlspecialchars()`

### Remaining XSS (Low Priority)
- `xiulian - Copy.php` — backup file, not in production
- `boss - 0.php` — backup file, not in production

### Lint
- ✅ PHP 5.6: all 7 files pass `php -l`

### Revert — Miracle Event (tupo.php)
- **Reverted** `addplayersx()` calls trong sự kiện Kỳ Tích (lines 174-177)

### F2P Time Calculation Update (v3.0 Final)
- **Updated** PRERELEASE_REPORT.md section 4.2 với tính toán chính xác
- **New data:** Tổng EXP từ Lv1→800 = 61.36 tỷ
- **New data:** F2P time to Lv100 = 2.2 năm (not 60-120 ngày như ước tính cũ)
- **New data:** F2P time to Lv800 = 273 năm
- **New data:** Thể Chất Q1→Q10 = 11,767 năm (F2P)
- **New data:** Tổng LT cho tất cả đột phá = 838 triệu LT = 919 năm
- **Conclusion:** Wall thực sự ở Lv100 (2.2 năm), không phải Lv50 (2.9 tháng)
- **Kết luận:** Game designed để **không thể max** cho F2P — đây là thiết kế có chủ ý của thể loại idle game

### P2W Analysis Update (v3.0 Final)
- **New section** 4.2b: Phân tích người chơi nạp tiền (Việt Nam)
- **New data:** Tỷ giá 1 VND = 1 MT (1 USD = 26,000 VND)
- **New data:** Công Pháp + Võ Công = 289,050 MT = ~289K VND (~$11)
- **New data:** P2W time to Lv100 = 1-2 tháng (Whale) vs 2.2 năm (F2P)
- **New data:** P2W time to Lv800 = 8-12 năm (Whale) vs 273 năm (F2P)
- **Key insight:** MT wall rẻ ($11), LT wall cực nặng (trillions LT). Whale cũng không max được.
- **Key insight:** Linh Căn T5 = gacha-only, không mua được bằng MT

### Standalone Report Created
- **New file:** `docs/F2P_P2W_ANALYSIS.md` — Báo cáo tổng hợp F2P & P2W
- **Content:** Phân tích chi tiết F2P, P2W, so sánh, wall analysis, khuyến nghị
- **Purpose:** Để người dùng suy nghĩ thêm trước khi quyết định thay đổi game

### Price Update & Exchange System (v3.1)
- **Updated** `wugong_config.json` — Tăng giá Võ Công cấp cao (Tier 4-7):
  - Tier 4 (Thần Công): 8,000-10,000 MT → 25,000-35,000 MT
  - Tier 5 (Đế Công): 20,000 MT → 60,000 MT
- **Updated** `congphap_config.json` — Tăng giá Công Pháp cấp cao (Tier 5-7):
  - Tier 5: 5,000-8,000 MT → 15,000-25,000 MT
  - Tier 6: 10,000-18,000 MT → 35,000-60,000 MT
  - Tier 7: 20,000-40,000 MT → 70,000-120,000 MT
- **New file:** `game/quydoi.php` — Hệ thống quy đổi MT → LT
  - Tỷ giá: 1,000 MT = 1,000,000 LT (1:1000)
  - Giới hạn: Tối đa 100,000 MT/lần, tối đa 5 lần/ngày
  - UI: Form nhập số MT, hiển thị LT nhận được, nút nhanh
- **New file:** `docs/create_exchange_log.sql` — SQL tạo bảng exchange_log
- **Updated** `game.php` — Thêm route `quydoi` vào cmdMap
- Đây là **thiết kế có chủ ý**: hiển thị "+999 Công, +999 Phòng, +999 Huyết" nhưng **KHÔNG ghi DB** — purpose: troll/chọc người chơi khi đột phá xịt (~2% chance)
- Updated docs: `GAME_MECHANICS.md`, `GAME_BALANCE.md`, `TEST_REPORT.md` — ghi chú rõ "display only, NOT written to DB"

---

### GameAlert Popup System — Dark Gaming Theme (v2.0)

**Bug fix — Tông môn tạo không hiển thị lỗi:**
- **Root cause:** `game.php` in ra toàn bộ HTML template trước khi `include "club.php"`. Khi `ajaxJson()` được gọi, `ob_end_clean()` chỉ xóa 1 cấp buffer → response là HTML + JSON → `JSON.parse()` thất bại → popup hiện "Lỗi: [HTML rác]" thay vì thông báo lỗi thật
- **Fix `game.php`:** Thêm `ob_start()` ở đầu file để đảm bảo output buffering luôn bật
- **Fix `club.php`:** Đổi `ob_end_clean()` thành `while (ob_get_level()) ob_end_clean();` để xóa toàn bộ buffer levels trước khi gửi JSON

**Redesign UI — Đồng bộ visual style với game:**
- **`game.php`:** Thay thế minified inline `GameAlert` bằng external `<script src="js/game-alert.js">` — easier to maintain
- **`js/game-alert.js`:** Viết lại với CSS classes (`ga-{type}`) thay vì inline styles
  - Mỗi type (success/error/warning/info) có class riêng
  - Thêm label type: "Thành công" / "Lỗi" / "Cảnh báo" / "Thông báo"
  - Icon, glow effects, button colors điều khiển qua CSS
  - Giữ nguyên API: `GameAlert.show()`, `.success()`, `.error()`, `.info()`, `.warning()`, `.ajax()`, `.confirm()`, `.action()`, `.form()`
- **`css/game-alert.css`:** Redesign hoàn toàn theo phong cách dark gaming:
  - Overlay: `rgba(3,6,20,0.72)` + `backdrop-filter: blur(4px)`
  - Box: Gradient navy (`rgba(16,28,68,0.96)`) + border glow cyan
  - Type-specific glow: success = green, error = pink, warning = gold, info = cyan
  - Icon: Circle gradient + pulse animation per type
  - Button: Gradient matching game buttons (green/red/gold/blue)
  - Label: Uppercase + letter-spacing + text-shadow glow
  - Responsive cho mobile

**Files changed:**
| File | Thay đổi |
|---|---|
| `game.php` | +`ob_start()`, thay inline JS → `<script src>` |
| `game/club.php` | Fix `ajaxJson()` xóa all buffer levels |
| `js/game-alert.js` | Viết lại với CSS classes |
| `css/game-alert.css` | Redesign dark gaming theme |

---

## Boss Tông Môn — Auto-Sync Config Pattern

### Nguyên tắc
Khi sửa config trong PHP (ví dụ `base_hp`, `open_hour`, `duration_min`...) rồi **Ctrl+S**, chỉ cần **reload trang** là tự cập nhật DB. Không cần chạy SQL thủ công.

### Cách hoạt động
1. Code load row boss hiện tại từ DB (`SELECT * FROM club_boss WHERE clubid=? AND boss_date=?`)
2. Nếu boss đã tồn tại → tính lại stats từ config: `newMaxHp = base_hp + (clubLv-1) * hp_per_lv`
3. So sánh stats mới vs stats DB
4. Nếu khác → `UPDATE` tự động (giữ nguyên HP đã mất, chỉ cập nhật maxHP/stats)
5. `$hpPercent` tính lại từ giá trị mới → HP bar cập nhật đúng

### Ưu điểm
- **Sửa config → Ctrl+S → Reload** = xong, không cần SQL
- Không mất progress HP đã đánh
- Áp dụng cho mọi tính năng có config: boss, NPC, event, reward...

### Lưu ý
- Boss **đã chết** (status=2) hoặc **HP=0** thì không sync — cần xóa DB để tạo boss mới
- Muốn reset hoàn toàn: `DELETE FROM club_boss WHERE boss_date='YYYY-MM-DD'`

---

## Boss Tông Môn — Hệ Thống Đánh Boss Tông Môn

### Tổng quan
Hệ thống boss Tông Môn cho phép thành viên cùng tông môn đánh boss theo khung giờ, nhận thưởng cá nhân + thưởng tông môn.

### Files
| File | Vai trò |
|---|---|
| `game/club_boss.php` | Trang chính: logic boss, combat, rewards, UI |
| `js/club_boss.js` | AJAX attack, HP bar, combat log, auto-refresh |
| `sql/club_boss.sql` | DDL 3 bảng: `club_boss`, `club_boss_damage`, `club_boss_log` |

### Routes
- `game.php` dòng 386: `'clubboss_fight'=>'game/club_boss.php'`
- `club.php`: 3 nút boss → link `clubboss_fight`

### Config (inline trong `club_boss.php`)
```php
$bossCfg = [
    'open_hour'      => 0,       // Giờ mở cửa (0-23)
    'open_minute'    => 0,       // Phút mở cửa (0-59)
    'duration_min'   => 1440,    // Thời gian mở (phút) — 1440 = cả ngày
    'base_hp'        => 100000,  // Máu boss cơ bản
    'hp_per_lv'      => 500000,  // Máu thêm mỗi cấp tông môn
    'base_atk'       => 800,     // Công boss
    'atk_per_lv'     => 15,
    'base_def'       => 1500,    // Thủ boss
    'def_per_lv'     => 5,
    'base_crit'      => 12,      // Tỷ lệ bạo boss (%)
    'crit_per_lv'    => 1,
    'reward_exp'     => 300,     // EXP nhân vật cơ bản
    'reward_lt'      => 150,     // Linh thạch cơ bản
    'reward_mt'      => 15,      // Ma thạch cơ bản
    'kill_bonus_exp' => 2000,    // Bonus kết liễu: EXP
    'kill_bonus_lt'  => 500,     // Bonus kết liễu: LT
    'top1_bonus_exp' => 1500,    // Top 1 bonus: EXP
    'top1_bonus_lt'  => 800,     // Top 1 bonus: LT
    'top1_bonus_mt'  => 50,      // Top 1 bonus: MT
    'top2_bonus_exp' => 1000,    // Top 2 bonus: EXP
    'top2_bonus_lt'  => 500,
    'top2_bonus_mt'  => 30,
    'top3_bonus_exp' => 500,     // Top 3 bonus: EXP
    'top3_bonus_lt'  => 250,
    'top3_bonus_mt'  => 15,
    'cooldown_sec'   => 3,       // Cooldown giữa 2 lần đánh (giây)
];
```

### Combat Formulas
| Hành động | Công thức |
|---|---|
| Player → Boss | `max(1, player_ugj - bossDef × 0.75)` |
| Player bạo kích | `× 1.65` (tỷ lệ theo `player.ubj`, cap 75%) |
| Boss né tránh | 15% chance |
| Pet attack | `max(1, round(wgjl × 0.3))` |
| Hút máu | `max(0, damage × uxx / 220)` |
| Boss → Player | `max(round(bossAtk × 0.12), bossAtk - playerDef × 0.65)` |
| Boss bạo kích | `× 1.5` |
| Player né boss | 15% chance |
| Cooldown | 3s (configurable) |

### DB Tables (MySQL 5.5 compatible)
```sql
-- club_boss: 1 row boss/ngày
CREATE TABLE club_boss (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clubid INT NOT NULL,
    boss_name VARCHAR(50) NOT NULL DEFAULT 'Boss Tông Môn',
    boss_lv INT NOT NULL DEFAULT 1,
    boss_hp INT NOT NULL DEFAULT 0,
    boss_maxhp INT NOT NULL DEFAULT 100000,
    boss_atk INT NOT NULL DEFAULT 800,
    boss_def INT NOT NULL DEFAULT 1500,
    boss_crit INT NOT NULL DEFAULT 12,
    boss_date DATE NOT NULL,
    open_time DATETIME DEFAULT NULL,
    close_time DATETIME DEFAULT NULL,
    status TINYINT NOT NULL DEFAULT 0,  -- 0=open, 1=in_progress, 2=dead
    killed_by INT DEFAULT NULL,
    killed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT NULL,
    UNIQUE KEY uk_club_date (clubid, boss_date)
);

-- club_boss_damage: tracking sát thương từng player
CREATE TABLE club_boss_damage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boss_id INT NOT NULL,
    clubid INT NOT NULL,
    uid INT NOT NULL,
    sid VARCHAR(50) NOT NULL,
    total_damage INT NOT NULL DEFAULT 0,
    hit_count INT NOT NULL DEFAULT 0,
    crit_count INT NOT NULL DEFAULT 0,
    dodge_count INT NOT NULL DEFAULT 0,
    pet_damage INT NOT NULL DEFAULT 0,
    last_hit_at DATETIME DEFAULT NULL,
    UNIQUE KEY uk_boss_uid (boss_id, uid)
);

-- club_boss_log: chi tiết từng đòn đánh (battle log)
CREATE TABLE club_boss_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boss_id INT NOT NULL,
    uid INT NOT NULL,
    uname VARCHAR(50) NOT NULL DEFAULT '',
    damage INT NOT NULL DEFAULT 0,
    is_crit TINYINT NOT NULL DEFAULT 0,
    is_dodge TINYINT NOT NULL DEFAULT 0,
    pet_damage INT NOT NULL DEFAULT 0,
    boss_hp_after INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL
);
```

**LƯU Ý MySQL 5.5:**
- KHÔNG dùng `DEFAULT CURRENT_TIMESTAMP` → dùng `DEFAULT NULL` + insert `NOW()` từ PHP
- Dùng `UNIQUE KEY` thay vì `UNIQUE INDEX`

### Reward System khi Boss chết
1. **Sort theo `total_damage` giảm dần** → xác định Top 1/2/3
2. **Base reward**: `reward_exp + (dmgRatio × reward_exp × 3)` EXP + `reward_lt + (dmgRatio × reward_lt × 2)` LT + `reward_mt` MT
3. **Top 1/2/3 bonus**: cộng thêm EXP/LT/MT theo config
4. **Kết liễu (killing blow)**: cộng thêm `kill_bonus_exp + kill_bonus_lt`
5. **EXP Tông Môn**: `500 + (50 × số người tham gia)` EXP tông môn (`clubexp` trong bảng `club`)
6. **Global chat**: thông báo `[Player] đã tiêu diệt Boss Tông Môn! Tông môn nhận +X EXP`

### Ví dụ Reward
```
🥇 Top 1: +3300 EXP, +1800 LT, +65 MT (🥇 Top 1 🎯 Kết Liễu +2000EXP +500LT)
🥈 Top 2: +1800 EXP, +900 LT, +45 MT (🥈 Top 2)
🥉 Top 3: +1200 EXP, +650 LT, +30 MT (🥉 Top 3)
🏛 Tông môn: +750 EXP tông môn (500 + 50 × 5)
```

### Auto-Sync Config Pattern
- PHP load config → so sánh vs DB → auto UPDATE nếu khác
- User sửa config trong PHP → Ctrl+S → reload page = xong
- Áp dụng cho: maxHP, ATK, DEF, CRIT, level
- Boss đã chết (status=2) hoặc HP=0 → không sync

### Combat Log — RPG Battle Flow
Mỗi lần tấn công hiện 1 box duy nhất (thay thế box cũ):
1. ⚔ Player tấn công Boss — gây X sát thương [Bạo Kích!]
2. ↳ Boss mất X HP → còn Y/Z (P%)
3. 🐾 Thú Cưng tấn công Boss — gây X sát thương
4. ↳ Boss mất thêm X HP
5. 🔥 Boss tấn công bạn — gây X sát thương [Bạo!]
6. 💚 Hút máu hồi phục X HP

**Tab "Lịch sử":** `cb-combat-feed` — chỉ hiển thị đòn cuối (không flickering)
**Tab "Xếp hạng":** Top 10 sát thương

### Crit Badge Animation
```css
.cb-crit-badge {
    animation: cbCritPop .4s ease, textShadowGlow .6s ease .1s;
}
@keyframes cbCritPop { 0%{transform:scale(.5)} 50%{transform:scale(1.3)} 100%{transform:scale(1)} }
@keyframes textShadowGlow { 0%{text-shadow:0 0 2px rgba(251,191,36,.4)} 40%{text-shadow:0 0 12px rgba(251,191,36,.9)} 100%{text-shadow:0 0 2px rgba(251,191,36,.4)} }
```

### Bugs đã Fix
1. **Blank page** — `echo <<<HTML` thay vì `$html = <<<HTML`
2. **Undefined `$isAjax`** — thêm `$isAjax = $isAjaxEarly;` ở đầu file
3. **JSON parse error** — thêm `while (ob_get_level()) ob_end_clean();` trước cả 2 endpoint JSON (attack + status)
4. **INSERT IGNORE race condition** — dùng `INSERT IGNORE` + re-SELECT thay vì `lastInsertId()`
5. **Boss counter-attack floor** — `max(1,...)` → `max(round(bossAtk × 0.12),...)` (min 12% boss ATK thay vì 1)
6. **Combat log flickering** — tách `cb-combat-feed` (attack results) vs `cb-server-log` (DB logs), auto-refresh chỉ update bảng log
7. **GameAlert MutationObserver crash** — `document.body` null khi script load trong `<head>` → defer `obs.observe()` qua DOMContentLoaded

### GameAlert v2.0 Fix
- `js/game-alert.js`: `MutationObserver.observe(document.body)` → defer qua `startObs()` + DOMContentLoaded
- Cache bust: `game.php` version param → `?v=2026072401`

### Files thay đổi trong phiên Boss Tông Môn
| File | Thay đổi |
|---|---|
| `game.php` | Route `clubboss_fight`, `ob_start()`, GameAlert external script |
| `game/club.php` | 3 nút boss, `ajaxJson()` fix buffer, XSS escape |
| `game/club_boss.php` | **MỚI** — Full combat page ~695 lines |
| `js/club_boss.js` | **MỚI** — AJAX attack, auto-refresh, battle log |
| `sql/club_boss.sql` | **MỚI** — DDL 3 bảng |
| `css/mystyle.css` | `.cb-*` responsive styles |
| `js/game-alert.js` | Fix MutationObserver document.body crash |

### Lint
- ✅ PHP 5.6: `club_boss.php`, `club.php`, `game.php` không lỗi
- ✅ JS: `club_boss.js` không lỗi
