# BÁO CÁO KIỂM THỬ TOÀN BỘ GAME Tu Chân Giới

**Ngày:** 2026-07-24  
**Phạm vi:** 31 hệ thống, 70+ file PHP, 34 config JSON  
**Phương pháp:** Code review tĩnh (static analysis), kiểm tra logic, bảo mật, cân bằng

---

## TỔNG QUAN

| Mức độ | Số lượng | Mô tả |
|--------|----------|-------|
| 🔴 **CRITICAL** | 18 | SQL injection, lỗ hổng bảo mật nghiêm trọng, race condition có thể exploit |
| 🟠 **HIGH** | 15 | Logic error, mất dữ liệu, balance exploit |
| 🟡 **MEDIUM** | 16 | XSS, thiếu validation, PHP notice, bất tiện UX |
| 🟢 **LOW** | 11 | Code smell, dead code, debug left-in |
| **TỔNG** | **60** | |

---

## PHẦN 1: LỖ HỔNG BẢO MẬT 🔴

### 1.1 SQL Injection — Hệ thống

**`gm/gmquery.php` — 16 câu query bị inject**

Đây là lỗ hổng nghiêm trọng nhất. **Mọi case** trong GM panel đều dùng string interpolation:

```php
// Dòng 63
$sql = "SELECT * FROM game1 WHERE uname = '{$uid}'";
// Dòng 75
$sql = "update game1 set uyxb=uyxb+'{$num}' where uname='{$uid}'";
```

`$uid` đến từ `$_POST['uid']` — không sanitize, không prepared statement.

**Tấn công:** Gửi `uid = ' OR 1=1 --` → đọc toàn bộ DB. Gửi `uid = '; DROP TABLE game1; --` → xóa bảng.

---

**`game.php:445,450,504` — Raw `$sid` trong SQL**

```php
$sql = "delete from midguaiwu where sid='$sid'";   // dòng 445
$sql = "delete from boss where sid='$sid'";          // dòng 450
```

`$sid` từ query string, chạy trên **mọi request** (không chỉ PVE). Attacker craft SID chứa `' OR 1=1` → xóa toàn bộ dữ liệu quái/boss.

---

**`game/zbinfo.php:42,48` — Tên vật phẩm inject**

```php
$sql = "insert into fangshi_zb(...) VALUES ('$zhuangbei->zbname',...)";
```

Nếu tên vật phẩm chứa `'` → SQL injection hoặc lỗi query.

---

### 1.2 Mật khẩu Plaintext

**`index.php:21`** — So sánh mật khẩu trực tiếp:
```php
if (!$user || $user['userpass'] != $userpass) {
```

**`reguser.php:31`** — Lưu mật khẩu thô:
```php
$stmt->execute(array($username, $userpass, $token));
```

**Hậu quả:** Nếu DB bị lộ, toàn bộ tài khoản bị hack. `password_hash()` đã được comment ra nhưng chưa bật lại.

---

### 1.3 GM Password Hardcode

**`gm/gmquery.php:8`**:
```php
if($gmcode!='vingame'){
```

Mật khẩu `vingame` nằm trực tiếp trong source code. Không rate limit, không session, không IP whitelist. Bất kỳ ai đọc được source đều có quyền admin.

---

### 1.4 XSS (Cross-Site Scripting)

| Vị trí | Vấn đề |
|---------|--------|
| `game/luonden.php:285` | Craft message chứa HTML inject vào `<script>` qua `addslashes()` — không chống `</script>` |
| `game/zbinfo.php:311` | Tên vật phẩm xuất trực tiếp ra HTML |
| `ydinfo.php:251` | Tên đan dược + màu sắc xuất trực tiếp |
| `game.php:160` | Tên linh căn/thể chất/huyết mạch từ config xuất trực tiếp |
| `reguser.php:23` | Tên username trong lỗi register inject vào JS |

---

## PHẦN 2: RACE CONDITION — MẤT DỮ LIỆU 🔴

### 2.1 Currency Exchange Double-Spend

**`game/quydoi.php:42-66`** — Đổi tiền MT→LT:

```
Bước 1: Đếm số lần đổi hôm nay
Bước 2: Kiểm tra số dư MT
Bước 3: Trừ MT (query riêng)
Bước 4: Cộng LT (query riêng)
Bước 5: Ghi log (query riêng)
```

Không có transaction. 5 request đồng thời → cả 5 đều pass kiểm tra → giới hạn 5 lần/ngày trở thành vô hạn.

---

### 2.2 Club Treasury — Mất/Vduplicate vật phẩm

**`class/function/club_treasury.php:57-103`**:

```
SELECT item WHERE claimed_by IS NULL  →  Cả 2 request đều thấy item
UPDATE SET claimed_by = sid          →  Cả 2 đều claim
INSERT vào inventory                 →  Item bị duplicate
```

**Thêm:** Không kiểm tra `$claimerSid` có thuộc club không → player lạ có thể cướp vật phẩm từ kho club khác (chỉ cần biết ID).

---

### 2.3 Boss HP Double-Write

**`game/club_boss.php:133-258`**:

```
Read boss HP → 100
Player A: 100 - 50 = 50
Player B: 100 - 60 = 40
Player A: UPDATE SET hp = 50
Player B: UPDATE SET hp = 40  → HP của A bị overwrite, 10 sát thương bị mất
```

---

### 2.4 PvE Combat Double-Kill

**`game/pve.php:80-259`**: Đọc HP quái → tính damage → ghi HP. Không lock. 2 request cùng kill → cả 2 nhận reward.

---

### 2.5 Club Upgrade Double-Click

**`game/club.php:732-751`**: Kiểm tra resources → trừ resources. Double-click → resources âm.

---

## PHẦN 3: LOGIC ERROR NGHIÊM TRỌNG 🟠

### 3.1 Vòng lặp vô hạn Pet Level

**`class/player.php:2134`**:
```php
for ($i=0;$i<$rangeslv;$i++)  // $rangeslv là ARRAY, không phải int
```

PHP cast array → int = 1. Vòng lặp chạy 1 lần, chỉ xử lý level 0-50. Pet level ≥50 → `cwmaxexp` không được tính → = null → pet level up ngay lập tức với mọi EXP → **vòng lặp vô hạn**.

**Fix:** `$i < count($rangeslv)-1`

---

### 3.2 Level 0 từ Breakthrough

**`game/tupo.php:163`**:
```php
$player->ulv = $player->ulv - 1;  // Level 1 → Level 0
```

Level 0 phá vỡ: EXP formula, realm calculation, stat calculation, và nhiều system khác giả định `ulv >= 1`.

---

### 3.3 GM "Set" Thực Chất Là "Add"

**`gm/gmquery.php:163,339,383`**:
```php
$sql = "update game1 set ulv=ulv+'{$num}' where uname='{$uid}'";
```

Label nói "Set Level" nhưng code thực chất là **cộng thêm**. Set level 10 khi đang level 50 → thành level 60.

---

### 3.4 Master Rời Club Không Transfer

**`game/club.php:343-350`**: `outclub` chỉ xóa dòng clubplayer, không kiểm tra master. Master rời → club không có chủ.

---

### 3.5 Club Không Có Giới Hạn Thành Viên

**`game/club.php:299,319`**: `joinclub` và `approveclub` không kiểm tra `$memberCount < maxMembers`. Club level 1 (tối đa 8) có thể có 100+ thành viên.

---

### 3.6 Boss Reward Task Undefined

**`game/boss.php:374`**: `$taskarr` được dùng trong vòng lặp for nhưng dòng code khai báo đã bị comment hết → PHP Notice, vòng lặp không bao giờ chạy → item rơi từ boss không được tính cho quest.

---

### 3.7 Meditation Breakthrough Reminder Không Hiển Thị

**`game/xiulian.php:264`**: Biến `$tpts` không bao giờ được khai báo → luôn = null → điều kiện `null != ''` = false → thông báo "Tu vi đã đạt giới hạn cần đột phá" **không bao giờ hiển thị**.

---

### 3.8 Operator Precedence Bug

**`game.php:601`**:
```php
if (!$ym==''){
```

PHP eval `!$ym` trước (→ false nếu $ym non-empty), rồi `false == ''` → true. Điều kiện **luôn đúng**, include block luôn chạy.

---

## PHẦN 4: BALANCE EXPLOIT 🟠

### 4.1 Tower Auto-Skip Free Reward

**`game/toayeuthap.php:233-278`**: Auto-skip cho phép nhảy qua các tầng đã qua mà **không cần combat**, nhận toàn bộ EXP + vật phẩm. Sau khi reaching floor 100, mỗi tuần auto-skip từ floor 1 lên 80 → hàng triệu EXP miễn phí.

---

### 4.2 Arena Win-Trading

**`game/vandinhdai.php:186-192`**: Thắng +10 phút, thua -3 phút. 2 account alternate wins → net +7 score/cycle + EXP mỗi trận. Trận thua vẫn cho EXP → farming reliability.

---

### 4.3 Lifesteal Vô Hạn

**`game/pve.php:233`**: `$pvexx = ceil($gphurt * ($player->uxx/200))`. Với `uxx >= 200`, player heal nhiều hơn sát thương gây ra → bất tử trước quái yếu.

---

### 4.4 No Pill Cooldown

**`game/ydinfo.php`**: Không có cooldown giữa các lần dùng đan. Macro/spam dùng đan → tích lũy stats hoặc toxin cực nhanh.

---

### 4.5 High Luck = No Boss Drop

**`game/boss.php:332`**:
```php
$sjjv = mt_rand(1, 120-$xyz)
```

Nếu `$xyz` (lucky stat) > 120 → `mt_rand(1, negative)` → PHP 5.6 trả 0 → boss không bao giờ rơi equipment cho player may mắn cao.

---

## PHẦN 5: RACE CONDITION KHÁC 🔴

### 5.1 Pet Damage Không Áp Dụng (Club Boss)

**`game/club_boss.php:196-201`**: `$petDamage` được tính toán nhưng **không bao giờ cộng vào damage**. Pet vô dụng trong club boss.

### 5.2 Boss Damage Không Trừ HP Player

**`game/club_boss.php:239-253`**: Boss damage được tính và trả về JSON nhưng **không trừ HP player**. Player không bao giờ chết khi đánh boss club.

### 5.3 Session-Based Claims Dễ Bypass

**`game/club.php:767-781`**: Online EXP dùng `$_SESSION`. Xóa cookie → claim lại._same với boss kill (dòng 984) và quest claim.

---

## PHẦN 6: TOÀN BỘ BUG THEO FILE

### `game.php` (Router)
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 48 | 🟡 | Anti-flood 10ms quá ngắn, gần như vô dụng |
| 175 | 🟡 | SID = md5 predictable, không regenerate sau login |
| 445,450,504 | 🔴 | SQL injection qua $sid |
| 601 | 🟠 | `!$ym==''` operator precedence bug |

### `class/player.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 2134 | 🔴 | Vòng lặp vô hạn pet level |
| 763,851,900,1187+ | 🔴 | 20+ hàm SQL injection legacy |
| 1002-1063 | 🟠 | Race condition upplayerlv (double level-up) |
| 1772-1773 | 🟠 | Boss column binding overwrite ypjv |
| 2169-2382 | 🟠 | Các hàm getchongwuall, getplayerjineng... inject |

### `game/pve.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 255,259,393,398,580-582 | 🔴 | SQL injection |
| 80-259 | 🔴 | Race condition combat |
| 233 | 🟠 | Lifesteal exploit |
| 377 | 🟡 | EXP farming exploit |

### `game/boss.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 374 | 🟠 | $taskarr undefined (boss drops không track quest) |
| 332 | 🟠 | mt_rand(1, negative) khi luck > 120 |
| 543-546 | 🟡 | Boss grows additive, có thể thành unkillable |

### `game/tupo.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 163 | 🟠 | Level 1 → Level 0 |
| 175,181 | 🟡 | Float truncation khi chia EXP |

### `game/xiulian.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 264 | 🟠 | $tpts undefined, breakthrough reminder không hiển thị |
| 313 | 🟢 | console.log debug left in production |

### `game/club.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 299,319 | 🔴 | Không kiểm tra max members |
| 343-350 | 🔴 | Master rời club không transfer |
| 356-360 | 🟠 | Disband không cleanup related tables |
| 560-594 | 🟠 | Donation race condition |
| 732-751 | 🟠 | Upgrade race condition, resources âm |
| 767-781 | 🟡 | Session bypass online EXP |

### `game/club_boss.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 133-258 | 🔴 | Boss HP race condition |
| 196-201 | 🟠 | Pet damage không apply |
| 239-253 | 🟠 | Boss damage không trừ HP player |
| 262 | 🟡 | Deprecated MySQL VALUES() |

### `class/function/luonden.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 572-641 | 🔴 | Material check-then-act race condition |
| 68-78 | 🟡 | INSERT on first call có thể duplicate key |
| 1284 | 🟡 | Hourly toxin reduction quá chậm (200-1000 giờ cho 1000 toxin) |

### `class/function/club_treasury.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 57-103 | 🔴 | Claim race condition + không check club membership |

### `class/function/club_quest.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 65 | 🟠 | mt_rand inverted range khi types < minQ |
| 158-176 | 🟠 | Quest claim không atomic |

### `class/function/nhiemvu.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 694-707 | 🟠 | Quest reward double-claim |

### `game/shangdian.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 207-218 | 🟠 | Shop purchase race condition |

### `game/quydoi.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 42-66 | 🔴 | Currency exchange double-spend |

### `game/zbinfo.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 42,48 | 🔴 | SQL injection |
| 205 | 🟠 | $sellzb undefined (typo: $selllb) |

### `game/ydinfo.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 171 | 🟡 | Null dereference on $yaodan |
| 251 | 🟡 | XSS qua tên đan dược |

### `game/zhuangtai.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 5 | 🟡 | $player used before definition |
| 340 | 🟡 | Array index out of bounds cho shenfen |

### `gm/gm.php` + `gm/gmquery.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 8 | 🔴 | Hardcoded password |
| 63-383 | 🔴 | 16 SQL injection |
| 163,339,383 | 🟠 | Set = Add logic error |

### `game/toayeuthap.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 233-278 | 🟠 | Free auto-skip reward exploit |
| 330-331 | 🟢 | Double state fetch |

### `game/vandinhdai.php`
| Dòng | Mức | Vấn đề |
|------|-----|--------|
| 186-192 | 🟠 | Win-trading exploit |

---

## PHẦN 7: KHUYẾN NGHỊ ƯU TIÊN SỬA

### Ưu Tiên 1 — SQL Injection (🔴 CRITICAL)

| File | Hành động |
|------|-----------|
| `gm/gmquery.php` | Chuyển toàn bộ 16 query sang prepared statements |
| `game.php:445,450,504` | Chuyển sang prepared statements |
| `game/zbinfo.php:42,48` | Chuyển sang prepared statements |
| `class/player.php` (20+ hàm legacy) | Audit và migrate dần |

### Ưu Tiên 2 — Authentication (🔴 CRITICAL)

| File | Hành động |
|------|-----------|
| `index.php` + `reguser.php` | Bật `password_hash()`/`password_verify()` |
| `gm/gmquery.php` | Di chuyển password ra DB, thêm session auth |
| `class/encode.php` | Không thay đổi (chỉ chống casual tampering, adequate) |

### Ưu Tiên 3 — Race Conditions (🔴 CRITICAL)

| File | Hành động |
|------|-----------|
| `game/quydoi.php` | Thêm transaction: `BEGIN` → check → deduct → add → `COMMIT` |
| `class/function/club_treasury.php` | Transaction + membership check |
| `game/club_boss.php` | `UPDATE SET boss_hp = GREATEST(0, boss_hp - ?)` atomic |
| `game/pve.php` | Thêm `SELECT ... FOR UPDATE` trước khi ghi damage |
| `game/club.php` | Transaction cho upgrade + donation |

### Ưu Tiên 4 — Critical Logic (🟠 HIGH)

| File | Hành động |
|------|-----------|
| `class/player.php:2134` | Sửa `for ($i=0;$i<$rangeslv;$i++)` → `count($rangeslv)-1` |
| `game/tupo.php:163` | Thêm check `if ($player->ulv <= 1) return` |
| `game/club.php:299,319` | Thêm member count check |
| `game/club.php:343` | Thêm master check + transfer logic |
| `game/boss.php:374` | Uncomment task tracking hoặc xóa code dead |
| `game/xiulian.php:264` | Khai báo `$tpts` |
| `game/club_boss.php:196` | Cộng pet damage vào player damage |
| `game/club_boss.php:239` | Trừ HP player từ boss damage |

### Ưu Tiên 5 — XSS (🟡 MEDIUM)

| File | Hành động |
|------|-----------|
| `game/luonden.php:285` | Dùng `json_encode()` thay `addslashes()` cho JS string |
| `game/zbinfo.php`, `ydinfo.php`, `game.php` | Thêm `htmlspecialchars()` cho output |

---

## PHẦN 8: BẢNG TỔNG HỢP THEO HỆ THỐNG

| Hệ thống | File chính | CRITICAL | HIGH | MEDIUM | LOW |
|-----------|-----------|----------|------|--------|-----|
| **Bảo mật/Auth** | game.php, index.php, reguser.php, gm/ | 4 | 0 | 2 | 0 |
| **SQL Injection** | gmquery, game.php, player.php, zbinfo | 4 | 0 | 0 | 0 |
| **Race Condition** | quydoi, treasury, club_boss, pve, club | 5 | 0 | 0 | 0 |
| **Combat** | pve.php, boss.php, club_boss | 2 | 3 | 3 | 1 |
| **Progression** | tupo.php, xiulian.php, player.php | 1 | 2 | 2 | 0 |
| **Guild** | club.php, club_boss, treasury, quest | 2 | 4 | 2 | 1 |
| **Alchemy** | luonden.php (class + game) | 0 | 1 | 3 | 2 |
| **Economy** | shangdian, quydoi, zbinfo | 1 | 2 | 1 | 1 |
| **Quest/Mission** | nhiemvu.php, club_quest | 0 | 2 | 1 | 0 |
| **Items** | ydinfo.php | 0 | 0 | 2 | 0 |
| **UI/Display** | zhuangtai.php, various | 0 | 0 | 2 | 4 |
| **Tổng** | | **19** | **14** | **18** | **9** |

---

## PHẦN 9: KẾT LUẬN

### Điểm mạnh
- Hệ thống gameplay phong phú (31 hệ thống)
- Config-driven design (34 JSON files) — dễ mở rộng
- Hầu hết code mới dùng PDO prepared statements
- Alchemy system (1362 dòng) rất chi tiết với nhiều cơ chế

### Điểm yếu nghiêm trọng
- **SQL Injection systemic** trong code legacy (player.php, gmquery, game.php)
- **Mật khẩu plaintext** — rủi ro bảo mật cao
- **Race conditions** ở hầu hết giao dịch kinh tế — có thể exploit để lấy resources/items miễn phí
- **Pet loop bug** có thể crash server
- **Không có transaction** ở bất kỳ giao dịch tài nguyên nào

### Ưu tiên sửa: Bảo mật > Data Integrity > Balance > UX
