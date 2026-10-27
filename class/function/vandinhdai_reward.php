<?php
if (!function_exists('vdd_daily_reward_table')) {
    function vdd_daily_reward_table()
    {
        $table = array(
            1 => array('yxb' => 10000, 'czb' => 100),
            2 => array('yxb' => 8500, 'czb' => 85),
            3 => array('yxb' => 7200, 'czb' => 72),
            4 => array('yxb' => 6000, 'czb' => 60),
            5 => array('yxb' => 5000, 'czb' => 50),
            6 => array('yxb' => 4200, 'czb' => 42),
            7 => array('yxb' => 3500, 'czb' => 35),
            8 => array('yxb' => 2800, 'czb' => 28),
            9 => array('yxb' => 2200, 'czb' => 22),
            10 => array('yxb' => 1600, 'czb' => 16)
        );

        for ($i = 11; $i <= 20; $i++) {
            $table[$i] = array(
                'yxb' => intval(800 - (($i - 11) * 40)),
                'czb' => 0
            );
        }

        for ($i = 21; $i <= 50; $i++) {
            $table[$i] = array(
                'yxb' => intval(200 - (($i - 21) * 3)),
                'czb' => 0
            );
        }

        for ($i = 51; $i <= 100; $i++) {
            $table[$i] = array(
                'yxb' => intval(50 - (($i - 51) * 0.6)),
                'czb' => 0
            );
        }

        return $table;
    }
}

if (!function_exists('vdd_issue_daily_rewards')) {
    function vdd_issue_daily_rewards($dblj, $force = false)
    {
        if (!$force && date('H:i:s') < '21:00:00') {
            return array('issued' => false, 'reason' => 'not_time', 'count' => 0);
        }

        $rewardDate = date('Y-m-d');
        $stmt = $dblj->prepare("SELECT COUNT(*) FROM arena_daily_reward WHERE reward_date = ?");
        $stmt->execute(array($rewardDate));
        if (intval($stmt->fetchColumn()) > 0) {
            return array('issued' => false, 'reason' => 'already_issued', 'count' => 0);
        }

        $top = $dblj->query("
            SELECT ap.sid, ap.score
            FROM arena_player ap
            INNER JOIN game1 g ON g.sid = ap.sid
            WHERE ap.score > 0
            ORDER BY ap.score DESC, ap.wins DESC
            LIMIT 100
        ");
        if (!$top) {
            return array('issued' => false, 'reason' => 'no_top', 'count' => 0);
        }

        $rewards = vdd_daily_reward_table();
        $rank = 0;
        $issued = 0;
        $updatePlayer = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ?, uczb = uczb + ? WHERE sid = ?");
        $insertReward = $dblj->prepare("INSERT IGNORE INTO arena_daily_reward(reward_date, sid, rank_no, score, yxb_reward, czb_reward, created_at) VALUES(?, ?, ?, ?, ?, ?, NOW())");

        foreach ($top->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rank++;
            if (!isset($rewards[$rank])) {
                continue;
            }

            $yxb = intval($rewards[$rank]['yxb']);
            $czb = intval($rewards[$rank]['czb']);
            $insertReward->execute(array($rewardDate, $row['sid'], $rank, intval($row['score']), $yxb, $czb));
            if ($insertReward->rowCount() > 0) {
                $updatePlayer->execute(array($yxb, $czb, $row['sid']));
                $issued++;
            }
        }

        return array('issued' => $issued > 0, 'reason' => $issued > 0 ? 'ok' : 'empty', 'count' => $issued);
    }
}

if (!function_exists('vdd_my_daily_reward')) {
    function vdd_my_daily_reward($sid, $dblj)
    {
        $stmt = $dblj->prepare("SELECT * FROM arena_daily_reward WHERE reward_date = CURDATE() AND sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : false;
    }
}
?>
