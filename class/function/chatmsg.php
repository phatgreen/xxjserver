<?php
// Ham render tin nhan chat dung chung: nowmid.php (Trang Chu) + liaotian.php (trang chat).
// Global scope (khong namespace) vi ca 2 trang goi ham khong ten mien.

if (!function_exists('tcg_chat_escape')) {
    function tcg_chat_escape($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tcg_chat_format_msg')) {
    /**
     * Format nội dung chat.
     * - Tin người chơi: escape toàn bộ, không cho HTML.
     * - Tin hệ thống: cho phép một số HTML an toàn: a, font, img.
     *   Dùng cho log rơi đồ như: Nhận được <a ...><font color="...">[Tên đồ]</font></a>
     */
    function tcg_chat_format_msg($str, $allowHtml)
    {
        $str = (string)$str;

        if (!$allowHtml) {
            return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        }

        // Chuẩn hóa entity trước, vì một số log có thể đã bị lưu dạng <a ...>.
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

        $tokens = array();

        // Giữ icon ảnh nội bộ ./images/*.png|gif|jpg|webp.
        $str = preg_replace_callback('/<img\b[^>]*src=["\']?([^"\' >]+)["\']?[^>]*>/i', function ($m) use (&$tokens) {
            $src = trim($m[1]);
            $src = str_replace('\\', '/', $src);

            if (!preg_match('#^(\./)?images/[A-Za-z0-9_./-]+\.(png|gif|jpg|jpeg|webp)$#i', $src)) {
                return '';
            }

            $key = '###TCG_CHAT_TOKEN_'.count($tokens).'###';
            $tokens[$key] = '<img class="tcg-chat-inline-img" src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'" alt="">';
            return $key;
        }, $str);

        // Giữ thẻ mở <a href="?cmd=..."> hoặc <a href='?cmd=...'>.
        $str = preg_replace_callback('/<a\b[^>]*href=["\']?([^"\' >]+)["\']?[^>]*>/i', function ($m) use (&$tokens) {
            $href = trim($m[1]);
            $href = html_entity_decode($href, ENT_QUOTES, 'UTF-8');

            // Chỉ cho phép link nội bộ game theo dạng ?cmd=..., chặn javascript/http/style/onerror.
            if (strpos($href, '?cmd=') !== 0) {
                return '';
            }
            if (preg_match('/[\x00-\x1F\x7F"\'<>]/', $href)) {
                return '';
            }

            $key = '###TCG_CHAT_TOKEN_'.count($tokens).'###';
            $tokens[$key] = '<a class="tcg-chat-item-link" href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'">';
            return $key;
        }, $str);

        // Giữ thẻ đóng </a>.
        $str = preg_replace_callback('/<\/a\s*>/i', function () use (&$tokens) {
            $key = '###TCG_CHAT_TOKEN_'.count($tokens).'###';
            $tokens[$key] = '</a>';
            return $key;
        }, $str);

        // Giữ màu từ <font color="#xxxxxx"> hoặc <font style="color:#xxxxxx">.
        // Log rơi đồ cũ thường lưu: <font style="color:#09e0b0">[Tên đồ]</font>
        $str = preg_replace_callback('/<font\b([^>]*)>/i', function ($m) use (&$tokens) {
            $attrs = isset($m[1]) ? $m[1] : '';
            $color = '';

            if (preg_match('/\bcolor\s*=\s*["\']?\s*(#[0-9a-fA-F]{3,6}|[a-zA-Z]+)\s*["\']?/i', $attrs, $cm)) {
                $color = trim($cm[1]);
            } elseif (preg_match('/\bstyle\s*=\s*["\'][^"\']*color\s*:\s*(#[0-9a-fA-F]{3,6}|[a-zA-Z]+)[^"\']*["\']/i', $attrs, $sm)) {
                $color = trim($sm[1]);
            }

            if ($color == '' || !preg_match('/^(#[0-9a-fA-F]{3,6}|red|green|blue|yellow|white|black|cyan|magenta|orange|purple)$/i', $color)) {
                $color = '#ffffff';
            }

            $key = '###TCG_CHAT_TOKEN_'.count($tokens).'###';
            $tokens[$key] = '<span class="tcg-chat-color" style="color:'.htmlspecialchars($color, ENT_QUOTES, 'UTF-8').'">';
            return $key;
        }, $str);

        // Giữ thẻ đóng </font> bằng </span>.
        $str = preg_replace_callback('/<\/font\s*>/i', function () use (&$tokens) {
            $key = '###TCG_CHAT_TOKEN_'.count($tokens).'###';
            $tokens[$key] = '</span>';
            return $key;
        }, $str);

        // Escape phần còn lại để HTML lạ không chạy và không hiện thẻ rác.
        $safe = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');

        foreach ($tokens as $key => $html) {
            $safe = str_replace($key, $html, $safe);
        }

        return $safe;
    }
}

if (!function_exists('tcg_chat_player_link')) {
    function tcg_chat_player_link($cmd, $name)
    {
        $safeName = tcg_chat_escape($name);
        return '<a class="tcg-chat-name" href="?cmd='.$cmd.'">'.$safeName.'</a>';
    }
}
