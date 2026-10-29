<?php
// Cấu hình trang GM (gm/gm.php). Kết nối DB dùng chung pdo.php của game — không ghi mật khẩu DB ở đây.
// Để trống (mặc định): lần đầu mở gm/gm.php tại máy chủ (localhost) sẽ hiện form "Tạo mã GM"; mã lưu bảng gm_setting, đổi ở tab "Mã GM".
// Quên mã: chạy  php gm/reset_code.php  trên máy chủ rồi mở lại trang để tạo mã mới.
// (Tuỳ chọn) điền chuỗi bcrypt $2y$… vào đây để cố định mã, khi đó mã trong DB bị bỏ qua.
define('GM_CODE_HASH', '');
