<?php
// Cấu hình trang GM (gm/gm.php). Kết nối DB dùng chung pdo.php của game — không ghi mật khẩu DB ở đây.
// Đổi mã GM: php -r "echo password_hash('ma_moi', PASSWORD_BCRYPT);" rồi dán chuỗi $2y$… vào dưới.
define('GM_CODE_HASH', '$2y$10$whFxGHlLxTokhVU1eDsNOeHPxHtoLaky81xfRb3qDFIXqZD32Vs22');
