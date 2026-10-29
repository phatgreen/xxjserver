<?php
namespace player;

/**
 * trade_tax.php — thuế giao dịch Phường Thị 5–15% theo giá trị + phẩm cấp (class/config/trade_tax_config.json).
 * Dùng ở game/fangshi.php (3 đường mua: đạo cụ / trang bị / trang bị cửa hàng).
 */

if (!function_exists(__NAMESPACE__ . '\\trade_tax_config')) {
    function trade_tax_config(){
        static $cache = null;
        if ($cache === null) {
            $file = __DIR__ . '/../config/trade_tax_config.json';
            $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
            $cache = is_array($data) ? $data : array();
        }
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\trade_tax_step')) {
    // Bậc cộng thêm cao nhất có ngưỡng <= $value trong bảng [[ngưỡng, % cộng], ...].
    function trade_tax_step($steps, $value){
        $add = 0;
        if (!is_array($steps)) return 0;
        foreach ($steps as $s) {
            if (is_array($s) && count($s) >= 2 && $value >= $s[0]) $add = max($add, intval($s[1]));
        }
        return $add;
    }
}

if (!function_exists(__NAMESPACE__ . '\\trade_tax_pct')) {
    // $kind: 'daoju' ($quality = djyxb giá gốc) hoặc 'zb' ($quality = phẩm rarity). $total: tổng LT người mua trả.
    function trade_tax_pct($kind, $quality, $total){
        $cfg = trade_tax_config();
        $base = isset($cfg['base_pct']) ? intval($cfg['base_pct']) : 10;
        $max = isset($cfg['max_pct']) ? intval($cfg['max_pct']) : $base;
        $pct = $base + trade_tax_step(isset($cfg['price_steps']) ? $cfg['price_steps'] : array(), intval($total));
        $qKey = ($kind === 'zb') ? 'rarity_steps' : 'item_value_steps';
        $pct += trade_tax_step(isset($cfg[$qKey]) ? $cfg[$qKey] : array(), intval($quality));
        return max($base, min($max, $pct));
    }
}

if (!function_exists(__NAMESPACE__ . '\\trade_tax_after')) {
    // Số LT người bán thực nhận sau thuế.
    function trade_tax_after($total, $pct){
        return intval(floor(intval($total) * (100 - intval($pct)) / 100));
    }
}
