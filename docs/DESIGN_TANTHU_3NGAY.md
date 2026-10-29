# Thiết kế: Quà Tân Thủ 3 ngày đầu — đẩy cấp bằng quà, không đổi luật game

> 2026-10-29 · Trạng thái: **CHỜ CHỦ DỰ ÁN DUYỆT** (chưa sửa code game) · Mô phỏng: `tools/sim/econ_sim.py` `sim(..., newbie=..., start_wd=...)` trên nền luật sau sửa E1–E5 (`docs/BAO_CAO_MOC_THOI_GIAN_2026-10-29.md` mục 9).

## 1. Mục tiêu

| Ngày tuổi nhân vật (tính từ lúc tạo) | Chơi ít (1–2h) | Chơi nhiều (12h+) |
|---|---|---|
| Ngày 1 | ~100 | ~150 |
| Ngày 2 | ~150 | ~250 |
| Ngày 3 | ~250 | ~300 |
| Từ ngày 4 | tiến độ tự nhiên của luật hiện tại ở cấp đó | |

Ràng buộc chủ dự án chốt:
- **Chỉ đẩy bằng quà:** giftcode tân thủ, giftcode nhóm (Tele), quà điểm danh / mốc đăng nhập, thẻ ×2 hấp thụ linh khí 3 ngày…
- **Không** bỏ hay giảm LT đột phá, **không** tự tăng tỉ lệ đột phá.
- Thứ 7 / Chủ nhật: dùng sự kiện đột phá cuối tuần **đã có** (+5% / +10%, `tupo_bonus_config.json`).

## 2. Ba con số quyết định thiết kế

Chi phí lên cấp, tính theo đúng luật hiện tại:

| Đoạn cấp | Lần bấm đột phá | LT phí đột phá | Tu vi | Lần bại mất tu vi |
|---|---|---|---|---|
| 1 → 150 | 320–990 | 3,9–15,5M | 125M | 7–34 |
| 150 → 250 | 370–1.970 | 19–99M | 451M | 11–75 |
| 250 → 300 | 230–990 | 22–93M | 419M | 7–38 |

(số nhỏ: Chủ nhật +10% và có đồ +15% đột phá; số lớn: ngày thường, không đồ)

1. **Không tặng tu vi một cục.** Mỗi lần đột phá bại có 4% bị mất tu vi (về 0 / còn ¼ / ½ / tụt 1 cấp). Một cục 1 tỷ tu vi sẽ mất sạch sau vài lần bại xui. Quà phải là **vật phẩm dùng dần: mỗi viên lấp đầy 1 thanh tu vi**. Số viên ≈ số cấp, nên mốc cấp điều khiển được chính xác.
2. **LT phí đột phá rất lớn:** 64–124M / nhân vật trong 3 ngày, trong khi người cày 24h kiếm 45M cả tháng 1. Nếu phát LT thường, LT sẽ tràn sang linh căn, thiên phú, Phường Thị, acc phụ… Mô phỏng cho thấy ngày 3 có 78M LT quà đổ vào tính năng khác. ⇒ Đề xuất **túi LT chỉ dùng trả phí đột phá**. Mỗi lần bấm vẫn trừ đủ LT như luật, chỉ là trừ từ túi.
3. **Số lần bấm đột phá:** 1.500–2.500 lần trong 3 ngày (người 1–2h: ~250–300 lần/giờ online). Không đổi luật thì không giảm được số này. Đề xuất phụ: nút **"Đột phá liên tục"** (tự bấm lại tới khi thành công / hết thanh / hết LT / tối đa N lần). Chỉ là giao diện, không đổi tỉ lệ hay phí.

## 3. Phương án

### 3.1 Vật phẩm mới (khóa: không bán NPC, không treo Phường Thị, không chuyển)

| Vật phẩm | Tác dụng | Điều kiện dùng |
|---|---|---|
| **Tân Thủ Tu Vi Đan** | Lấp đầy thanh tu vi cấp hiện tại (`uexp = umaxexp`) | Nhân vật ≤ 3 ngày tuổi **và** cấp < trần của ngày đó: ngày 1 **150**, ngày 2 **250**, ngày 3 **300**. Qua ngày 3: hết tác dụng. |
| **Túi Linh Thạch Tân Thủ** | Quỹ LT riêng; trang Đột Phá trừ phí từ túi trước, thiếu mới trừ LT thường | Chỉ dùng cho phí đột phá; không rút ra LT thường |
| **Thẻ ×2 Hấp Thụ Linh Khí** | Linh khí tu luyện ×2 (nhân ngoài, giống Thiên Phú) | 72 giờ kể từ khi dùng; 1 thẻ / nhân vật |

### 3.2 Nguồn phát (đúng các kênh chủ dự án nêu)

| Nguồn | Khi nào | Ngày 1 | Ngày 2 | Ngày 3 |
|---|---|---|---|---|
| **Giftcode Tân Thủ** (`duihuan`, 1 lần / nhân vật) | ngày 1 | 50 Tu Vi Đan + túi 1,5M | – | – |
| **Giftcode Nhóm Tele** (`duihuan`, 1 lần / nhân vật) | ngày 1 | **Thẻ ×2 Hấp Thụ 72h** + 30 Tu Vi Đan + túi 1M | – | – |
| **Điểm danh tân thủ** (mới: nhận 1 lần / ngày tuổi 1–3) | đăng nhập | 30 Tu Vi Đan + túi 0,5M | 60 Tu Vi Đan + túi 15M | 110 Tu Vi Đan + túi 40M |
| **Mốc online tân thủ** (mới: chỉ ngày tuổi 1–3; 1/2/4/6/8/12h online trong ngày) | theo giờ chơi, tối đa 12h | 6 đan + túi 0,5M **mỗi giờ** | 6 đan + túi 2M mỗi giờ | 3 đan + túi 3M mỗi giờ |
| **Tổng cố định / tối đa (12h)** | | 110 → 182 đan; 3M → 9M | 60 → 132 đan; 15M → 39M | 110 → 146 đan; 40M → 76M |
| Thứ 7 / CN | sự kiện đột phá đã có | +5% / +10% tỉ lệ | | |

Mã giftcode dùng bảng `duihuan` sẵn có (một mã nhận 1 lần / nhân vật), thêm 2 điều kiện: **ngày tuổi ≤ 3** và **cấp ≤ trần**, để acc cũ không nhận.

## 4. Kết quả mô phỏng (trung bình 8 lần chạy; F2P, máy chủ 1 người, luật sau sửa E1–E5)

| Giờ/ngày | Tạo nv | Ngày 1 | Ngày 2 | Ngày 3 | Ngày 4 | Ngày 7 | Ngày 14 | Ngày 30 | Không quà (ngày 1 / 3 / 7 / 30) | Đan dùng | Túi LT phát | Lần bấm ĐP 3 ngày |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1h | Thứ 2 | 105 | 155 | 228 | 229 | 234 | 244 | 258 | 12 / 20 / 33 / 103 | 295 | 64M | 1.711 |
| 1h | Thứ 7 | 109 | 170 | 245 | 246 | 249 | 257 | 270 | | 294 | 64M | 1.481 |
| 2h | Thứ 2 | 110 | 167 | 251 | 252 | 256 | 264 | 277 | 16 / 25 / 44 / 129 | 309 | 69M | 1.715 |
| 2h | Thứ 7 | 115 | 181 | 264 | 264 | 268 | 276 | 292 | | 308 | 69M | 1.499 |
| 4h | Thứ 2 | 119 | 189 | 279 | 280 | 285 | 292 | 301 | 19 / 32 / 65 / 155 | 337 | 80M | 1.831 |
| 8h | Thứ 2 | 127 | 217 | 300 | 302 | 307 | 318 | 332 | 25 / 52 / 96 / 195 | 372 | 102M | 2.078 |
| 8h | Thứ 7 | 139 | 240 | 294 | 294 | 296 | 300 | 313 | | 354 | 102M | 1.891 |
| 12h | Thứ 2 | 133 | 230 | 301 | 303 | 312 | 323 | 332 | 29 / 67 / 118 / 206 | 404 | 124M | 2.454 |
| 12h | Thứ 7 | **150** | **258** | **306** | 310 | 319 | 343 | 366 | | 341 | 124M | 1.592 |
| 24h | Thứ 7 | 154 | 265 | 312 | 317 | 332 | 362 | 385 | 47 / 101 / 150 / 256 | 334 | 124M | 1.555 |

- **Đạt mục tiêu:**
  - Ngày 1: 105–154.
  - Ngày 2: 155–265.
  - Ngày 3: 228–312.
  - Tạo nhân vật thứ 7 (ngày 1 = T7, ngày 2 = CN) lên nhanh hơn 5–25 cấp và ít lần bấm hơn 10–35%, đúng ý "ngày đặc biệt".
- **"Giảm dần về tiến độ hiện tại":** từ ngày 4 không còn quà, nhân vật đi theo đường cong tự nhiên ở cấp ~300, tức chỉ +5 đến +30 cấp/tuần. Đây đúng là tiến độ của người chơi thường khi họ tới cấp 300, khoảng tháng 2 trở đi.
- ⚠ **Cảm giác "đụng tường" ở ngày 4:** từ +80–100 cấp/ngày tụt xuống +1–5 cấp/ngày. Nếu muốn mềm hơn, có thể nối thêm ngày 4–7 với quà nhỏ dần, ví dụ trần 310 / 320 / 330 / 340 và 10 → 5 đan/ngày. Chủ dự án chọn (mục 7).

## 5. Ảnh hưởng kinh tế & chống lạm dụng

| Rủi ro | Xử lý |
|---|---|
| LT quà 64–124M / nhân vật lọt ra kinh tế | Túi LT chỉ trả phí đột phá; không bán, không chuyển. Phần thừa sau ngày 3 vẫn chỉ dùng cho đột phá về sau |
| Acc phụ cày quà đẩy cho acc chính | Mọi vật phẩm tân thủ **khóa** (không bán NPC, không treo Phường Thị); giftcode 1 lần / nhân vật; điều kiện ngày tuổi ≤ 3 |
| Tân Thủ Tu Vi Đan tích trữ dùng sau | Hết tác dụng sau ngày 3 và bị chặn bởi trần cấp ngày |
| Mất tu vi khi bại | Không cần xử lý: mất thì dùng viên đan khác (đã tính vào số đan, ~25–35 viên dự phòng) |
| MT | Không phát MT ⇒ không ảnh hưởng nạp |
| Người chơi cũ (đã qua 3 ngày) | Không nhận gì (ngày tuổi). Có thể cân nhắc 1 giftcode "hồi quy" riêng sau |

## 6. Việc phải làm (sau khi duyệt)

| # | Việc | File |
|---|---|---|
| 1 | Migration: `game1.created_at DATETIME` (nhân vật cũ = `2000-01-01` ⇒ không nhận quà tân thủ); `cmd=cjplayer` ghi thời điểm tạo | `sql/migrate_tanthu.php`, `game.php` |
| 2 | 3 vật phẩm mới + cờ khóa (`no_sell`, `no_trade`) | `daoju_config.json`, `daoju_sell_config.json`, `game/fangshi.php` / `djinfo.php` (chặn treo bán) |
| 3 | Dùng Tân Thủ Tu Vi Đan (kiểm ngày tuổi + trần) | `game/use_dj.php` |
| 4 | Túi LT: cột `game1.tanthu_lt` (hoặc bảng riêng); `tupo.php` trừ túi trước, LT sau, hiển thị số dư túi | `game/tupo.php`, migration |
| 5 | Thẻ ×2: cột hết hạn `tanthu_x2_until`; `xiulian.php` nhân ×2 khi còn hạn | `game/xiulian.php`, migration |
| 6 | Điểm danh + mốc online tân thủ ngày 1–3 | `class/function/online_reward.php`, config `tanthu_config.json`, tab Nhiệm Vụ → Trực Tuyến |
| 7 | Giftcode: thêm điều kiện ngày tuổi / cấp; tạo 2 mã TANTHU, NHOMTELE | `game/duihuan.php`, `duihuan` |
| 8 | (đề xuất) nút "Đột phá liên tục" | `game/tupo.php` |
| 9 | Test route thật: tạo nv → nhận quà → lên cấp theo ngày (dịch giờ), khóa giao dịch, hết hạn, trần cấp | `tests/test_tanthu.php` |

Mọi con số (đan, túi, trần, giờ) để trong `class/config/tanthu_config.json` để chỉnh không cần sửa code.

## 7. Cần chủ dự án chốt

1. **Túi LT chỉ cho đột phá** (đề xuất) hay phát LT thường?
2. **Ngày 4 trở đi:** dừng hẳn như yêu cầu, hay thêm ngày 4–7 quà nhỏ dần để đỡ "đụng tường"?
3. **Nút "Đột phá liên tục"** có làm không? Nếu không, người chơi 1–2h phải bấm ~250–300 lần/giờ.
4. Số lượng đan / túi ở bảng 3.2 (chỉnh trong config sau cũng được).
