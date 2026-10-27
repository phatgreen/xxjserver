# ĐỢT B — Quái theo cảnh giới tới cấp 500 (bản thiết kế chờ duyệt)

> Lập 2026-10-27. Bảng số sinh bằng `tools/gen/dot_b_design.py`. Thuộc kế hoạch tổng `PLAN_MO_RONG_NOI_DUNG.md`.

## 1. Cơ chế đã đo trong code / DB thật

| Hạng mục | Thực tế |
|---|---|
| Sinh quái | `game/nowmid.php`: map có `mid.mgid = "mã quái|số lượng,..."`; khi còn ≤ 3 con và quá `mid.ms` giây thì sinh lại |
| Kinh nghiệm quái | Tính lúc sinh: `cấp quái × (106–108)` — không đọc từ config |
| Kinh nghiệm lên cấp | `cấp² × 110` (`class/player.php:923`) |
| Rơi đồ | `gzb` (trang bị, tỉ lệ `dljv`/120), `gdj` (đạo cụ, `djjv`/180), `gyp` (dược, `ypjv`) |
| Boss bản đồ | `mid.midboss` → bảng `boss`; chết thì hồi đầy sau `mid.ms` giây, mỗi lần bị hạ +100 HP / +5 Công |
| Khu vực | bảng `qy` (+ ảnh `dt/ditu/<qyid>.png`); map nối nhau qua `mup/mdown/mleft/mright` |

## 2. Công thức chỉ số quái

Khớp hồi quy từ 87 quái cũ (cấp 10–105, bỏ quái đặc biệt) ⇒ quái mới **nối tiếp đúng nhịp khó hiện tại**:

```
HP   = 9.250 × cấp^1.652
Công = 0.461 × cấp^1.745
Thủ  = 3.704 × cấp^1.180
```

Kiểu quái nhân thêm hệ số (HP / Công / Thủ): **Thường** 1/1/1 · **Giáp** 1.2/0.85/1.3 · **Cuồng** 0.85/1.25/0.8 · **Tinh Anh** 2/1.2/1.1 (map cuối mỗi khu, rơi đồ cao hơn).
Boss khu vực: HP ×12, Công ×1.6, Thủ ×1.3 so với quái cùng cấp.

Rơi đồ theo đúng bậc: trang bị bậc ≤ cấp quái (60 / 120 / 200 / 300 / 400); dược Phục Thương Đan → Bách Niên Tiên Đào (≥150) → Thiên Niên Nhân Tham (≥300);
đạo cụ Cường Hóa Thạch, thêm Trúc Cơ Đột Phá Đan (330–399) và Kim Đan Đột Phá Đan (400–500).

## 3. Khu vực & đường đi

| Khu | Dải cấp | Bản đồ | Ghi chú |
|---|---|---|---|
| Thiên Thủy Cốc (có sẵn) | 57–77 | — | nối sẵn sang Tây Lương Địa |
| **Tây Lương Địa** (qy 27) | 78–92 | 7 / 9 map có quái | lấp khoảng trống 78–91; Bát Hoang Thành, Thục Sơn Phái giữ làm thành/môn phái (không quái) |
| Hoa Quả Sơn (có sẵn) | 92–105 | — | |
| **Hoang Thiên Lý** (qy 28) | 106–150 | 10 / 11 | đi từ Thục Sơn Phái (đã nối sẵn); Cương Thiết Các giữ làm thành |
| **Hồi Đầu Ngạn** (qy 30) | 152–200 | 9 / 10 | đi từ Bát Công Sơn (đã nối sẵn); Bỉ Ngạn Hoa giữ làm thành |
| **Hồng Hoang Cổ Địa** (qy 31 — đặt tên mới) | 202–250 | 9 / 9 | 9 map đang **không nối với đâu** ⇒ nối thành chuỗi, cửa vào từ Tiên Hồ Cư |
| **Bắc Minh Hải Vực** (mới) | 252–300 | 8 map mới | cửa vào từ Quỳnh Hoa Phái |
| **Côn Lôn Tiên Sơn** (mới) | 302–350 | 8 map mới | |
| **Vạn Yêu Lâm** (mới) | 352–400 | 8 map mới | |
| **Cửu U Ma Vực** (mới) | 402–450 | 8 map mới | |
| **Thiên Ngoại Thiên** (mới) | 452–500 | 8 map mới | |

Tổng: **84 loại quái mới** (mã 200–283), **40 bản đồ mới**, **5 khu vực mới**, **9 boss khu vực**. Mỗi map một bậc cấp, cách nhau 2–6 cấp
(quá cấp quái nhiều thì kinh nghiệm chia cho chênh lệch — `pve.php` — nên cần quái dày đặc).

Triển khai bằng `sql/migrate_dot_b.php` (chạy lại không trùng): ghi `mid.mgid`, nối map, tạo map/khu/boss mới; quái thêm vào `guaiwu_config.json`.

## 4. Bảng chi tiết

### Quái mới (84 loại, mã 200–283)


**Tây Lương Địa**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 200 | Sa Mạc Hạt Vương | 78 | tho | Giáp | 14,830 | 785 | 823 | Đăng Vân Lộc | 3 |
| 201 | Hà Nguyên Thủy Quái | 80 | thuy | Thường | 12,880 | 965 | 652 | Hà Nguyên | 3 |
| 202 | Lục La Độc Chướng Xà | 82 | moc | Cuồng | 11,410 | 1,260 | 537 | Lục La Chướng | 3 |
| 203 | Thắng Châu Loạn Binh | 84 | none | Thường | 13,970 | 1,051 | 691 | Thắng Châu | 3 |
| 204 | Thạch Thôn Nham Khôi | 86 | tho | Giáp | 17,420 | 931 | 923 | Thạch Thôn | 3 |
| 205 | Bách Nạp Hồ Tiên | 88 | hoa | Cuồng | 12,820 | 1,425 | 584 | Dương Danh Bách Nạp | 3 |
| 206 | Thục Sơn Phản Đồ | 90 | kim | Thường | 15,650 | 1,185 | 749 | Thục Sơn Ngoại Thành | 2 |
| 207 | Thục Sơn Kiếm Ma | 91 | kim | Tinh Anh | 31,880 | 1,450 | 835 | Thục Sơn Ngoại Thành | 1 |

**Hoang Thiên Lý**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 208 | Song Khê Thủy Mãng | 106 | thuy | Thường | 20,510 | 1,577 | 909 | Song Khê | 3 |
| 209 | Cố Đạo U Hồn | 110 | none | Cuồng | 18,530 | 2,103 | 760 | Thục Sơn Cố Đạo | 3 |
| 210 | Tỏa Yêu Tháp Thủ Vệ | 114 | kim | Giáp | 27,750 | 1,522 | 1,288 | Tỏa Yêu Tháp | 3 |
| 211 | Khê Động Thạch Long | 118 | tho | Giáp | 29,380 | 1,616 | 1,341 | Thạch Trầm Khê Động | 3 |
| 212 | Sào Hồ Độc Phong | 122 | moc | Cuồng | 21,990 | 2,519 | 858 | Sào Hồ | 3 |
| 213 | Tử Vân Lôi Điểu | 126 | hoa | Thường | 27,290 | 2,132 | 1,115 | Tử Vân Giá | 3 |
| 214 | Bát Công Sơn Yêu Viên | 132 | moc | Thường | 29,470 | 2,313 | 1,177 | Bát Công Sơn | 3 |
| 215 | Nữ La Huyết Yêu | 138 | hoa | Cuồng | 26,960 | 3,124 | 993 | Nữ La Nham | 3 |
| 216 | Bàn Cổ Thạch Linh | 144 | tho | Giáp | 40,830 | 2,288 | 1,696 | Bàn Cổ Chi Tâm | 3 |
| 217 | Vương Lăng Thi Tướng | 147 | none | Thường | 35,200 | 2,790 | 1,337 | Hoài Nam Vương Lăng | 2 |
| 218 | Vương Lăng Quỷ Vệ | 149 | none | Tinh Anh | 71,990 | 3,428 | 1,494 | Hoài Nam Vương Lăng | 1 |

**Hồi Đầu Ngạn**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 219 | Oản Khâu Sơn Quỷ | 152 | none | Thường | 37,200 | 2,958 | 1,391 | Oản Khâu Sơn | 3 |
| 220 | Thiên Phật Tháp Tà Tăng | 158 | kim | Giáp | 47,590 | 2,690 | 1,892 | Thiên Phật Tháp | 3 |
| 221 | Tử Vi Tinh Quái | 164 | hoa | Cuồng | 35,850 | 4,222 | 1,217 | Tử Vi Đạo | 3 |
| 222 | Bạch Hạo Băng Lang | 170 | thuy | Thường | 44,760 | 3,596 | 1,587 | Bạch Hạo Đạo | 3 |
| 223 | Tịch Huyền Ảnh Yêu | 176 | none | Cuồng | 40,290 | 4,776 | 1,323 | Tịch Huyền Đạo | 3 |
| 224 | Nguyệt Nha Hà Giao | 182 | thuy | Giáp | 60,110 | 3,443 | 2,236 | Nguyệt Nha Hà Cốc | 3 |
| 225 | Thanh Phong Kiếm Linh | 188 | kim | Cuồng | 44,920 | 5,358 | 1,430 | Thanh Phong Giản | 3 |
| 226 | Túy Hoa Mị Yêu | 194 | moc | Thường | 55,670 | 4,528 | 1,855 | Túy Hoa Ấm | 3 |
| 227 | Tiên Hồ Cư Hỏa Hồ | 197 | hoa | Thường | 57,100 | 4,651 | 1,889 | Tiên Hồ Cư | 2 |
| 228 | Cửu Vĩ Tiên Hồ | 199 | hoa | Tinh Anh | 116,110 | 5,680 | 2,102 | Tiên Hồ Cư | 1 |

**Hồng Hoang Cổ Địa**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 229 | Thần Nông Dược Linh | 202 | moc | Thường | 59,510 | 4,859 | 1,945 | Viêm Đế Thần Nông Động | 3 |
| 230 | Bách Linh Dị Thú | 208 | none | Cuồng | 53,090 | 6,392 | 1,611 | Bách Linh Châu | 3 |
| 231 | Bất Chu Sơn Cự Nhân | 214 | tho | Giáp | 78,550 | 4,568 | 2,707 | Bất Chu Sơn | 3 |
| 232 | Bàn Long Thủ Trụ | 220 | kim | Giáp | 82,230 | 4,793 | 2,797 | Bàn Long Trấn Trụ | 3 |
| 233 | Vô Thường Câu Hồn Sứ | 226 | none | Cuồng | 60,890 | 7,388 | 1,777 | Vô Thường Điện | 3 |
| 234 | Hoàng Tuyền Quỷ Binh | 232 | thuy | Thường | 74,800 | 6,187 | 2,291 | Hoàng Tuyền Quỷ Đạo | 3 |
| 235 | Phong Thần Hộ Lăng Vệ | 238 | kim | Thường | 78,030 | 6,469 | 2,361 | Phong Thần Lăng | 3 |
| 236 | Toàn Mộng Huyễn Yêu | 244 | moc | Cuồng | 69,110 | 8,445 | 1,945 | Toàn Mộng | 3 |
| 237 | Quỳnh Hoa Phản Đồ | 247 | hoa | Thường | 82,960 | 6,902 | 2,466 | Quỳnh Hoa Phái | 2 |
| 238 | Quỳnh Hoa Kiếm Quỷ | 249 | hoa | Tinh Anh | 168,150 | 8,399 | 2,739 | Quỳnh Hoa Phái | 1 |

**Bắc Minh Hải Vực**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 239 | Bắc Minh Hải Xà | 252 | thuy | Thường | 85,750 | 7,147 | 2,525 | Bắc Minh Hải Khẩu | 3 |
| 240 | Băng Hải Giải Tướng | 258 | thuy | Giáp | 106,980 | 6,330 | 3,375 | Băng Hải Tiều Thạch | 3 |
| 241 | Côn Bằng Ấu Điểu | 264 | none | Cuồng | 78,710 | 9,690 | 2,134 | Côn Bằng Sào | 3 |
| 242 | Hắc Thủy Huyền Quy | 270 | thuy | Giáp | 115,330 | 6,853 | 3,561 | Hắc Thủy Uyên | 3 |
| 243 | Long Cung Dạ Xoa | 276 | thuy | Cuồng | 84,710 | 10,471 | 2,249 | Bắc Minh Long Cung | 3 |
| 244 | Hải Nhãn Thủy Mẫu | 282 | thuy | Thường | 103,270 | 8,697 | 2,884 | Hải Nhãn Tuyền | 3 |
| 245 | Huyền Băng Hải Yêu | 288 | thuy | Thường | 106,920 | 9,023 | 2,956 | Huyền Băng Đảo | 3 |
| 246 | Thâm Uyên Cự Kình | 294 | thuy | Giáp | 132,750 | 7,950 | 3,938 | Bắc Minh Thâm Uyên | 2 |
| 247 | Thâm Uyên Hải Long | 298 | thuy | Tinh Anh | 226,240 | 11,492 | 3,386 | Bắc Minh Thâm Uyên | 1 |

**Côn Lôn Tiên Sơn**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 248 | Côn Lôn Thủ Sơn Thú | 302 | tho | Giáp | 138,770 | 8,332 | 4,065 | Côn Lôn Sơn Môn | 3 |
| 249 | Ngọc Hư Kiếm Khôi | 308 | kim | Thường | 119,460 | 10,144 | 3,200 | Ngọc Hư Phong | 3 |
| 250 | Dao Trì Tiên Hạc | 314 | none | Cuồng | 104,830 | 13,115 | 2,619 | Dao Trì | 3 |
| 251 | Kỳ Lân Ấu Thú | 320 | hoa | Thường | 127,250 | 10,844 | 3,348 | Kỳ Lân Nhai | 3 |
| 252 | Ngọc Châu Thạch Tinh | 326 | tho | Giáp | 157,460 | 9,521 | 4,448 | Ngọc Châu Phong | 3 |
| 253 | Tuyết Liên Băng Linh | 332 | thuy | Cuồng | 114,940 | 14,454 | 2,797 | Tuyết Liên Cốc | 3 |
| 254 | Thiên Trụ Cự Viên | 338 | tho | Thường | 139,290 | 11,931 | 3,571 | Thiên Trụ Sơn | 3 |
| 255 | Côn Lôn Kim Giáp Vệ | 344 | kim | Giáp | 172,070 | 10,457 | 4,740 | Côn Lôn Đỉnh | 2 |
| 256 | Côn Lôn Kiếm Thánh Tàn Ảnh | 348 | kim | Tinh Anh | 292,320 | 15,064 | 4,066 | Côn Lôn Đỉnh | 1 |

**Vạn Yêu Lâm**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 257 | Vạn Yêu Lâm Lang Vương | 352 | moc | Thường | 148,950 | 12,806 | 3,746 | Vạn Yêu Lâm Ngoại Vi | 3 |
| 258 | Độc Chướng Thiềm Thừ | 358 | moc | Giáp | 183,800 | 11,211 | 4,968 | Độc Chướng Đầm | 3 |
| 259 | Cổ Thụ Yêu Tinh | 364 | moc | Thường | 157,430 | 13,578 | 3,897 | Cổ Thụ Yêu Thành | 3 |
| 260 | Huyết Đằng Thực Nhân Hoa | 370 | moc | Cuồng | 137,480 | 17,463 | 3,179 | Huyết Đằng Cốc | 3 |
| 261 | Bách Hoa Điệp Yêu | 376 | hoa | Cuồng | 141,180 | 17,960 | 3,240 | Bách Hoa Yêu Cốc | 3 |
| 262 | Thanh Khâu Hồ Vệ | 382 | hoa | Thường | 170,490 | 14,771 | 4,126 | Thanh Khâu Hồ Quốc | 3 |
| 263 | Yêu Hoàng Cấm Vệ | 388 | none | Giáp | 209,930 | 12,901 | 5,463 | Yêu Hoàng Điện Ngoại | 3 |
| 264 | Thiên Niên Thụ Yêu | 394 | moc | Giáp | 215,320 | 13,251 | 5,563 | Vạn Yêu Lâm Tâm | 2 |
| 265 | Vạn Yêu Lâm Thủ Hộ Giả | 398 | moc | Tinh Anh | 364,900 | 19,040 | 4,763 | Vạn Yêu Lâm Tâm | 1 |

**Cửu U Ma Vực**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 266 | Ma Vực Tuần Binh | 402 | none | Thường | 185,490 | 16,146 | 4,382 | Ma Vực Nhập Khẩu | 3 |
| 267 | Hắc Phong Ma Ưng | 408 | none | Cuồng | 161,570 | 20,712 | 3,567 | Hắc Phong Hiệp | 3 |
| 268 | Huyết Trì Ma Giao | 414 | thuy | Thường | 194,730 | 16,997 | 4,537 | Huyết Trì | 3 |
| 269 | Cốt Hải Bạch Cốt Tướng | 420 | tho | Giáp | 239,290 | 14,815 | 5,999 | Cốt Hải | 3 |
| 270 | Ma Viêm Hỏa Kỳ Lân | 426 | hoa | Cuồng | 173,520 | 22,332 | 3,754 | Ma Viêm Cốc | 3 |
| 271 | U Minh Quỷ Tướng | 432 | none | Thường | 208,910 | 18,307 | 4,770 | U Minh Phủ | 3 |
| 272 | Thiên Ma Tế Ti | 438 | hoa | Cuồng | 181,670 | 23,441 | 3,879 | Thiên Ma Tế Đàn | 3 |
| 273 | Cửu U Ma Long | 444 | none | Giáp | 262,300 | 16,323 | 6,405 | Cửu U Ma Uyên | 2 |
| 274 | Cửu U Ma Tướng | 448 | none | Tinh Anh | 443,690 | 23,408 | 5,477 | Cửu U Ma Uyên | 1 |

**Thiên Ngoại Thiên**

| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |
|---|---|---|---|---|---|---|---|---|---|
| 275 | Thiên Môn Thần Tướng | 452 | kim | Giáp | 270,150 | 16,840 | 6,542 | Thiên Môn | 3 |
| 276 | Tinh Hà Tinh Thú | 458 | none | Thường | 230,090 | 20,273 | 5,111 | Tinh Hà Đạo | 3 |
| 277 | Lôi Kiếp Lôi Linh | 464 | hoa | Cuồng | 199,820 | 25,923 | 4,152 | Lôi Kiếp Đài | 3 |
| 278 | Tử Vi Tinh Quân Vệ | 470 | kim | Thường | 240,130 | 21,209 | 5,269 | Tử Vi Tinh Cung | 3 |
| 279 | Hỗn Độn Cự Thú | 476 | tho | Giáp | 294,260 | 18,431 | 6,953 | Hỗn Độn Hải | 3 |
| 280 | Thái Hư Kiếm Linh | 482 | kim | Cuồng | 212,790 | 27,703 | 4,343 | Thái Hư Cảnh | 3 |
| 281 | Huyền Cung Thiên Binh | 488 | thuy | Thường | 255,510 | 22,646 | 5,508 | Cửu Thiên Huyền Cung | 3 |
| 282 | Thiên Ngoại Tà Thần | 494 | none | Giáp | 312,870 | 19,664 | 7,265 | Thiên Ngoại Thiên Đỉnh | 2 |
| 283 | Thiên Ngoại Thần Vệ | 498 | none | Tinh Anh | 528,440 | 28,154 | 6,206 | Thiên Ngoại Thiên Đỉnh | 1 |

### Boss khu vực (9)

| Boss | Cấp | Hệ | HP | Công | Thủ | Khu |
|---|---|---|---|---|---|---|
| Boss·Tây Lương Sa Vương | 92 | tho | 194,800 | 1,971 | 1,000 | Tây Lương Địa |
| Boss·Hoài Nam Vương | 150 | none | 436,700 | 4,625 | 1,780 | Hoang Thiên Lý |
| Boss·Bỉ Ngạn Minh Vương | 200 | none | 702,500 | 7,640 | 2,499 | Hồi Đầu Ngạn |
| Boss·Cộng Công Tàn Hồn | 250 | thuy | 1,015,600 | 11,278 | 3,252 | Hồng Hoang Cổ Địa |
| Boss·Bắc Minh Côn Vương | 300 | thuy | 1,372,500 | 15,502 | 4,033 | Bắc Minh Hải Vực |
| Boss·Côn Lôn Kiếm Tôn | 350 | kim | 1,770,600 | 20,287 | 4,837 | Côn Lôn Tiên Sơn |
| Boss·Vạn Yêu Hoàng | 400 | moc | 2,207,600 | 25,610 | 5,663 | Vạn Yêu Lâm |
| Boss·Cửu U Ma Tôn | 450 | none | 2,681,800 | 31,454 | 6,507 | Cửu U Ma Vực |
| Boss·Thiên Ngoại Thiên Chủ | 500 | none | 3,191,700 | 37,803 | 7,369 | Thiên Ngoại Thiên |

## 5. Vấn đề phát hiện — cần chủ dự án quyết

| # | Vấn đề | Đề xuất |
|---|---|---|
| V1 | **Danh sách rơi đồ của quái cũ sai bậc**: `gzb` ghi theo mã liên tiếp (vd. quái cấp 100 có `21,22,23,24,25` = Thánh/Tiên/Thần/Đế/Đạo Kiếm, tức vũ khí cấp 200–800, tỉ lệ ~38%; quái cấp 1 rơi Thiên Hoàng Kiếm cấp 120) | Sửa luôn trong đợt B theo cùng quy tắc "bậc ≤ cấp quái" |
| V2 | **Kinh nghiệm quái tăng tuyến tính** (cấp × 107) trong khi lên cấp cần cấp² × 110 ⇒ số quái phải hạ để lên 1 cấp ≈ bằng cấp: cấp 100 ~100 con, cấp 300 ~310 con, cấp 500 ~510 con | Cho phép quái có `gexp` riêng trong config (code `nowmid.php` dùng nếu có). Quái mới đặt `gexp ≈ 1.1 × cấp²` ⇒ giữ ~100 con/cấp ở mọi dải. Quái cũ giữ nguyên |
| V3 | Khu mới chưa có ảnh bản đồ khu vực (`dt/ditu/<qyid>.png`) | Tạm dùng `default.png`; bạn bổ sung ảnh sau |
