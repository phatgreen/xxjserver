# Tên trang bị gốc từng lưu trong database (trước khi đổi sang zhuangbei_config.json)

Trích từ `playerzhuangbei.zbname` ngày 2026-10-03, trước khi `zbname` trong config được viết lại.
Đây là tên do người chơi đang thực sở hữu, `class/player.php:405` merge_player_zhuangbei_with_config() chỉ dùng khi config để trống,
nên các tên này hiện KHÔNG còn hiển thị. Lưu lại để không mất dữ liệu.

| zbid | Tên gốc trong DB | Ô DB (zbtool) | Tên config hiện tại | Ô config | Số món |
|---|---|---|---|---|---|
| 23 | Tân Thủ Mộc Kiếm | 1 Vũ Khí | Thien Lam Tinh Kiem | 1 Vũ Khí | 1 |
| 24 | Tân Thủ Bố Y | 3 Thiên Quan | Thien Huyet Diem Kiem | 1 Vũ Khí | 5 |
| 25 | Minh Nguyệt Chi Kiếm | 1 Vũ Khí | Thien Hoang Tho Kiem | 1 Vũ Khí | 1 |
| 27 | Bách Luyện Thanh Cương Kiếm | 1 Vũ Khí | Thien Moc Linh Phu | 9 Linh Phù | 1 |
| 28 | Bách Luyện Khinh Nhị Giáp | 3 Thiên Quan | Thien Thuy Linh Phu | 9 Linh Phù | 1 |
| 29 | Sơ Cấp Thị Huyết Kiếm | 1 Vũ Khí | Thien Hoa Linh Phu | 9 Linh Phù | 1 |
| 31 | Lôi Ưng Hộ Giáp | 3 Thiên Quan | Thien Kim Ngoc Nhan | 10 Ngọc Nhãn | 4 |
| 32 | Huyết Ưng Hạng Liên | 0 Không giới hạn | Thien Moc Ngoc Nhan | 10 Ngọc Nhãn | 6 |
| 33 | Hắc Ma Chủy Thủ | 1 Vũ Khí | Thien Thuy Ngoc Nhan | 10 Ngọc Nhãn | 2 |
| 53 | Bạo Vũ Lê Hoa Châm | 13 Linh Đai | Thanh Thuy Co Ngoc | 11 Cổ Ngọc | 3 |
| 54 | Bạo Vũ Lê Hoa Lệnh | 14 Linh Bội | Thanh Hoa Co Ngoc | 11 Cổ Ngọc | 2 |
| 55 | Bạo Vũ Truy Phong | 16 Bảo Vật | Thanh Tho Co Ngoc | 11 Cổ Ngọc | 3 |
| 56 | Bạo Vũ Bí Pháp | 15 Tọa Kỵ | Thanh Kim Vai Khoi | 12 Vai Khôi | 3 |
| 57 | Bạo Vũ Huyền Quan | 2 Thiên Thuẫn | Thanh Moc Vai Khoi | 12 Vai Khôi | 3 |
| 58 | Bạo Vũ Thiết Giáp | 3 Thiên Quan | Thanh Thuy Vai Khoi | 12 Vai Khôi | 2 |
| 60 | Bạo Vũ Bá Vương Thương | 1 Vũ Khí | Thanh Tho Vai Khoi | 12 Vai Khôi | 3 |
| 61 | Bạo Vũ Phong Dực | 11 Cổ Ngọc | Thanh Kim Bao | 4 Giáp Bào | 3 |
| 62 | Bạo Vũ Huyết Phong | 10 Ngọc Nhãn | Thanh Moc Bao | 4 Giáp Bào | 3 |
| 63 | Bạo Vũ Trấn Linh Đai | 6 Giới Chỉ | Thanh Thuy Bao | 4 Giáp Bào | 3 |
| 64 | Bạo Vũ Thiên Lôi Ấn | 12 Vai Khôi | Thanh Hoa Bao | 4 Giáp Bào | 2 |
| 65 | Bạo Vũ Hồn Ngọc Liên | 7 Yêu Đai | Thanh Tho Bao | 4 Giáp Bào | 3 |
| 66 | Bạo Vũ Liệt Phong Trảo | 5 Hộ Thủ | Tien Kim Gioi Chi | 6 Giới Chỉ | 3 |
| 67 | Bạo Vũ Hộ Thể Phù | 9 Linh Phù | Tien Moc Gioi Chi | 6 Giới Chỉ | 2 |

Tổng cộng 23 dòng có tên (trên 73 dòng phân biệt theo zbid+zbname+zbtool); các dòng còn lại có zbname rỗng.

## Tên trong `fangshi_zb` (tin treo bán Phường thị)

| zbname | số tin |
|---|---|
| Bạo Vũ Huyền Quan | 2 |
| Bạo Vũ Lê Hoa Châm | 1 |
| Hắc Ma Chủy Thủ | 1 |
| Ngũ Độc Phích Lịch Châu | 1 |
| Thái Ất Thần Câu | 1 |
| Tiểu Lý Đích Phi Đao | 1 |
