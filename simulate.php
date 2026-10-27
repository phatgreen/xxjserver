<?php
/**
 * GAME SIMULATION — 200 Players
 * Uses EXACT formulas from game code (player.php, tupo.php, xiulian.php)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

define('TOTAL_PLAYERS', 200);
define('SIM_DAYS', 365);

// ═══════════════════════════════════════════════════════════════════════
// EXACT FORMULAS FROM GAME CODE
// ═══════════════════════════════════════════════════════════════════════

// From player.php line 148
$GLOBALS['rangeslv'] = range(0, 4300, 50); // [0,50,100,...,4300]

// From player.php lines 1025-1034 — stat gains per level bracket
$GLOBALS['rangesgj'] = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
$GLOBALS['rangesfy'] = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
$GLOBALS['rangeshp'] = array(30, 50, 70, 90, 110, 130, 170, 210, 250, 300, 350, 400, 450, 500, 550);
$GLOBALS['rangeswx'] = array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34);

// EXP formula from player.php line 719
function expNeeded($nextLevel) {
    $base = max(100, floor(pow($nextLevel, 2.8) * 2));
    // Breakthrough bonus every 50 levels (line 721-723)
    $currentLevel = $nextLevel - 1;
    if ($currentLevel > 0 && $currentLevel % 50 == 0) {
        $bonus = floor(pow($currentLevel, 2.5) * 50);
        $base += $bonus;
    }
    return $base;
}

// Breakthrough cost from tupo.php lines 131-135
function breakthroughCost($level, $isMajor) {
    if ($isMajor) return $level * $level * 3;
    return $level * ($level + 1);
}

// Breakthrough rate from tupo.php lines 120-123
function breakthroughRate($level, $jingjieValue, $psjs = 0, $tuposjs = 0) {
    $baseRate = 75;
    $levelPenalty = $level * 0.6;
    $realmPenalty = $jingjieValue * 3;
    $rate = intval($baseRate - $levelPenalty - $realmPenalty + $psjs + $tuposjs);
    return max(5, $rate);
}

// Meditation EXP/sec from xiulian.php line 57
function meditationExpPerSec($level, $vipBonus, $cpAbsorb, $lcAbsorb, $lcExpBonus, $tupoexp) {
    $base = $level * $vipBonus * min(1.3, 1 + $cpAbsorb / 100) * min(1.3, 1 + $lcAbsorb / 100) * min(1.3, 1 + $lcExpBonus / 100);
    $real = $base * (1 + $tupoexp / 100);
    return $real;
}

// Cost per minute for x2 meditation (xiulian.php line 35)
function meditationCostPerMin($level, $pbReduce) {
    return ceil(max(1, ceil(pow($level, 1.15) / 5)) * (1 - $pbReduce));
}

// Cost per minute for x3 meditation (xiulian.php line 41)
function meditationMTPerMin($level) {
    return max(1, ceil($level / 3));
}

// ═══════════════════════════════════════════════════════════════════════
// PLAYER CLASS
// ═══════════════════════════════════════════════════════════════════════

class SimPlayer {
    public $id, $name, $type, $vip;
    public $level = 1, $exp = 0, $maxExp = 0;
    public $lt = 0, $mt = 0, $czb = 0; // uyxb=LT, uczb=MaThach
    public $hp = 100, $maxHp = 100, $atk = 10, $def = 10, $bj = 0, $xx = 0;
    public $jingjieValue = 0;
    public $tuposjs = 0, $tupoexp = 0;
    public $cpAbsorb = 0, $lcAbsorb = 0, $lcExpBonus = 0;
    public $linhcanLv = 0, $thechatLv = 0, $huyetmachLv = 0;

    // Counters
    public $daysActive = 0;
    public $totalLtSpent = 0, $totalMtSpent = 0, $totalExpGained = 0;
    public $ltFromPve = 0, $ltFromBoss = 0, $ltFromQuest = 0, $ltFromMedit = 0, $ltFromExchange = 0;
    public $expFromMedit = 0, $expFromPve = 0, $expFromBoss = 0, $expFromQuest = 0;
    public $bossKills = 0, $pveKills = 0;
    public $btAttempts = 0, $btSuccess = 0, $btFails = 0;
    public $btMajorAttempts = 0, $btMajorSuccess = 0;
    public $btMinorAttempts = 0, $btMinorSuccess = 0;
    public $eqDropped = 0, $eqCrafted = 0, $eqEnhanced = 0, $eqEnhFail = 0;
    public $alchemyOk = 0, $alchemyFail = 0;
    public $miningSessions = 0, $miningStones = 0, $miningMat = 0;
    public $questsDone = 0;
    public $potionsUsed = 0, $pillsUsed = 0;
    public $lcUpgrades = 0, $tcUpgrades = 0, $hmAwakenings = 0;
    public $stonesOwned = 0, $materialsOwned = 0;
    public $meditFreeMin = 0, $meditLtMin = 0, $meditMtMin = 0;

    public function __construct($id, $name, $type) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        switch ($type) {
            case 'f2p': $this->vip = 0; break;
            case 'light': $this->vip = 1; $this->mt = rand(500, 2000); break;
            case 'mid': $this->vip = 3; $this->mt = rand(5000, 20000); break;
            case 'heavy': $this->vip = 5; $this->mt = rand(50000, 200000); break;
            case 'whale': $this->vip = 10; $this->mt = rand(500000, 2000000); break;
        }
        $this->maxExp = expNeeded(2);
    }

    // Get EXP/sec based on current stats
    public function getExpPerSec($expMult = 1) {
        $vipBonus = ($this->vip > 0) ? 1.10 : 1.00;
        return meditationExpPerSec($this->level, $vipBonus, $this->cpAbsorb, $this->lcAbsorb, $this->lcExpBonus, $this->tupoexp) * $expMult;
    }

    // Daily login reward
    public function dailyLogin() {
        $dailyMt = min(50, 5 + $this->vip * 2);
        $this->mt += $dailyMt;
        $dailyLt = floor($this->level * rand(10, 30));
        $this->lt += $dailyLt;
        $this->ltFromQuest += $dailyLt;
    }

    // Meditation — EXACT formula from xiulian.php
    public function meditate($minutes, $mode) {
        // mode: 1=free(30min max), 2=LT x2(3h max), 3=MT x3(6h max)
        $maxMin = ($mode == 1) ? 30 : (($mode == 2) ? 180 : 360);
        $minutes = min($minutes, $maxMin);

        $expPerSec = $this->getExpPerSec($mode);
        $expPerMin = $expPerSec * 60;
        $totalExp = $minutes * $expPerMin;

        $ltCost = 0;
        $mtCost = 0;

        if ($mode == 2) {
            $pbReduce = 0; // assuming no tool11
            $costPerMin = meditationCostPerMin($this->level, $pbReduce);
            $ltCost = $minutes * $costPerMin;
            if ($this->lt >= $ltCost) {
                $this->lt -= $ltCost;
                $this->totalLtSpent += $ltCost;
                $this->ltFromMedit += $ltCost; // meditation LT goes back into system
            } else {
                $actualMin = floor($this->lt / max(1, $costPerMin));
                $ltCost = $actualMin * $costPerMin;
                $this->lt -= $ltCost;
                $this->totalLtSpent += $ltCost;
                $totalExp = $actualMin * $expPerMin;
                $minutes = $actualMin;
            }
            $this->meditLtMin += $minutes;
        } elseif ($mode == 3) {
            $mtCostPerMin = meditationMTPerMin($this->level);
            $mtCost = $minutes * $mtCostPerMin;
            if ($this->mt >= $mtCost) {
                $this->mt -= $mtCost;
                $this->totalMtSpent += $mtCost;
            } else {
                $actualMin = floor($this->mt / max(1, $mtCostPerMin));
                $mtCost = $actualMin * $mtCostPerMin;
                $this->mt -= $mtCost;
                $this->totalMtSpent += $mtCost;
                $totalExp = $actualMin * $expPerMin;
                $minutes = $actualMin;
            }
            $this->meditMtMin += $minutes;
        } else {
            $this->meditFreeMin += $minutes;
        }

        $totalExp = min($totalExp, $this->maxExp - $this->exp);
        $this->exp += $totalExp;
        $this->expFromMedit += $totalExp;
        $this->totalExpGained += $totalExp;
        return array('exp' => $totalExp, 'lt' => $ltCost, 'mt' => $mtCost, 'min' => $minutes);
    }

    // PvE fight — simplified simulation
    public function pveFight($monsterLv) {
        $monsterAtk = $monsterLv * rand(3, 8);
        $monsterDef = $monsterLv * rand(1, 3);
        $monsterHp = $monsterLv * rand(10, 30);
        $monsterExp = floor(pow($monsterLv, 1.5) * rand(2, 5));

        $damage = max(1, $this->atk - $monsterDef * 0.75);
        if (rand(1, 200) <= $this->bj) $damage = floor($damage * 1.72);
        if ($damage < 1) $damage = 1;

        $turns = max(1, ceil($monsterHp / $damage));
        $dmgReceived = max(1, $monsterAtk - $this->def * 0.75) * $turns;

        if ($dmgReceived < $this->hp) {
            $this->pveKills++;
            $lt = floor($monsterLv * rand(5, 15));
            $this->lt += $lt;
            $this->ltFromPve += $lt;
            $this->exp += $monsterExp;
            $this->expFromPve += $monsterExp;
            $this->totalExpGained += $monsterExp;
            if (rand(1, 100) <= rand(3, 8)) $this->eqDropped++;
            if (rand(1, 100) <= 15) $this->materialsOwned++;
            return true;
        } else {
            $this->potionsUsed++;
            return false;
        }
    }

    // Boss fight — simplified
    public function bossFight($bossLevel) {
        $bossHp = 5000 + $bossLevel * 500;
        $bossAtk = 200 + $bossLevel * 20;
        $bossDef = 100 + $bossLevel * 10;

        $damage = max(1, $this->atk - $bossDef * 0.75);
        if (rand(1, 100) <= $this->bj) $damage = floor($damage * 2.25);
        if ($damage < 1) $damage = 1;

        $turns = max(1, ceil($bossHp / $damage));
        $dmgReceived = max(1, $bossAtk - $this->def * 0.75) * $turns;

        if ($dmgReceived < $this->hp) {
            $this->bossKills++;
            $lt = floor(pow($bossLevel, 2.2) * rand(80, 150));
            $this->lt += $lt;
            $this->ltFromBoss += $lt;
            $exp = floor(pow($bossLevel, 2.5) * 5);
            $this->exp += $exp;
            $this->expFromBoss += $exp;
            $this->totalExpGained += $exp;
            if (rand(1, 100) <= min(30, 10 + intval($bossLevel / 100))) $this->eqDropped++;
            if (rand(1, 100) <= 5) $this->stonesOwned += rand(1, 3);
            if (rand(1, 100) <= 15) $this->materialsOwned++;
            return true;
        }
        return false;
    }

    // Try breakthrough — EXACT formula from tupo.php
    public function tryBreakthrough() {
        global $rangeslv, $rangesgj, $rangesfy, $rangeshp;
        $isMajor = ($this->level > 0 && $this->level % 50 == 0);
        $cost = breakthroughCost($this->level, $isMajor);
        if ($this->lt < $cost) return false;

        // Milestone bonus (tupo.php line 118)
        $psjs = ($this->level % 50 == 0) ? 10 : 0;
        $rate = breakthroughRate($this->level, $this->jingjieValue, $psjs, $this->tuposjs);
        $this->lt -= $cost;
        $this->totalLtSpent += $cost;
        $this->btAttempts++;
        if ($isMajor) $this->btMajorAttempts++;
        else $this->btMinorAttempts++;

        $rand = mt_rand(1, 100);

        // Failure effects from tupo.php lines 144-170
        if ($rand <= 1) {
            // 1%: level -1, exp = 0
            $this->level = max(1, $this->level - 1);
            $this->exp = 0;
            $this->btFails++;
        } elseif ($rand <= 2) {
            // 1%: exp = 0
            $this->exp = 0;
            $this->btFails++;
        } elseif ($rand <= 3) {
            // 1%: exp / 4
            $this->exp = intval($this->exp / 4);
            $this->btFails++;
        } elseif ($rand <= 4) {
            // 1%: exp / 2
            $this->exp = intval($this->exp / 2);
            $this->btFails++;
        } elseif ($rand > $rate) {
            // Fail (no penalty beyond LT cost)
            $this->btFails++;
        } else {
            // SUCCESS — from tupo.php lines 180-202
            if ($isMajor) {
                // Major: fixed stat gains
                $uphp = 4 + round($this->level / 1.2);
                $upgj = 2 + round($this->level / 2.5);
                $upfy = 3 + round($this->level / 2);
                $this->maxHp += $uphp;
                $this->hp = $this->maxHp;
                $this->atk += $upgj;
                $this->def += $upfy;
            } else {
                // Minor: percentage stat gains
                $uphp = 2 + round($this->maxHp / 20);
                $upgj = 1 + round($this->atk / 100);
                $upfy = 1 + round($this->def / 100);
                $this->maxHp += $uphp;
                $this->hp = $this->maxHp;
                $this->atk += $upgj;
                $this->def += $upfy;
            }

            // Level up — from upplayerlv
            $this->level++;
            $this->exp -= $this->maxExp;
            if ($this->exp < 0) $this->exp = 0;

            // Stat gains from level bracket
            $nextLv = $this->level + 1;
            for ($i = 0; $i < count($rangeslv) - 1; $i++) {
                if ($nextLv >= $rangeslv[$i] && $nextLv < $rangeslv[$i + 1]) {
                    $this->maxHp += $rangeshp[min($i, count($rangeshp) - 1)];
                    $this->hp = $this->maxHp;
                    $this->atk += $rangesgj[min($i, count($rangesgj) - 1)];
                    $this->def += $rangesfy[min($i, count($rangesfy) - 1)];
                    break;
                }
            }

            // Update maxExp for next level
            $this->maxExp = expNeeded($this->level + 1);

            // Update jingjieValue based on realm
            $realmIdx = min(floor($this->level / 50), 86);
            $this->jingjieValue = $realmIdx;

            $this->btSuccess++;
            if ($isMajor) $this->btMajorSuccess++;
            else $this->btMinorSuccess++;
        }
        return true;
    }

    // Mine
    public function mine() {
        $this->miningSessions++;
        $s = rand(0, 3);
        $this->miningStones += $s;
        $this->stonesOwned += $s;
        if (rand(1, 100) <= 10) $this->materialsOwned++;
    }

    // Daily activities
    public function dailyActivities() {
        $this->daysActive++;
        $this->dailyLogin();

        // Meditation: random type and duration
        // F2P: mostly free (30min), sometimes save LT for breakthrough
        // P2W: mix of free + LT + MT
        if ($this->type == 'whale' || $this->type == 'heavy') {
            // Whale/Heavy: 60% free 30min + 30% LT x2 + 10% MT x3
            $this->meditate(rand(25, 30), 1);
            if (rand(1, 100) <= 30 && $this->lt > 100) {
                $this->meditate(rand(30, 120), 2);
            }
            if (rand(1, 100) <= 10 && $this->mt > 50) {
                $this->meditate(rand(30, 60), 3);
            }
        } elseif ($this->type == 'mid') {
            // Mid: 70% free + 25% LT x2
            $this->meditate(rand(25, 30), 1);
            if (rand(1, 100) <= 25 && $this->lt > 100) {
                $this->meditate(rand(30, 90), 2);
            }
        } elseif ($this->type == 'light') {
            // Light: 80% free + 15% LT x2
            $this->meditate(rand(25, 30), 1);
            if (rand(1, 100) <= 15 && $this->lt > 100) {
                $this->meditate(rand(30, 60), 2);
            }
        } else {
            // F2P: 90% free + 10% LT x2
            $this->meditate(rand(25, 30), 1);
            if (rand(1, 100) <= 10 && $this->lt > 100) {
                $this->meditate(rand(30, 45), 2);
            }
        }

        // PvE 5-20 fights
        for ($i = 0; $i < rand(5, 20); $i++) {
            $mLv = min($this->level, rand(max(1, $this->level - 10), $this->level + 5));
            $this->pveFight($mLv);
        }

        // Boss 1-3
        for ($i = 0; $i < rand(1, 3); $i++) {
            $bLv = min($this->level, rand(max(1, $this->level - 20), $this->level + 10));
            $this->bossFight($bLv);
        }

        // Quests 2-5
        for ($i = 0; $i < rand(2, 5); $i++) {
            $this->questsDone++;
            $lt = floor($this->level * rand(50, 200));
            $this->lt += $lt;
            $this->ltFromQuest += $lt;
            $exp = floor(pow($this->level, 2) * rand(2, 5));
            $this->exp += $exp;
            $this->expFromQuest += $exp;
            $this->totalExpGained += $exp;
            if (rand(1, 100) <= 30) $this->mt += rand(1, 10);
        }

        // Mining 1-3
        for ($i = 0; $i < rand(1, 3); $i++) $this->mine();

        // Enhance 1-2
        for ($i = 0; $i < rand(1, 2); $i++) {
            $cost = rand(1, 5);
            if ($this->stonesOwned >= $cost) {
                $this->stonesOwned -= $cost;
                $this->eqEnhanced++;
                if (rand(1, 35) >= 3) {
                    $this->atk += rand(1, 5);
                    $this->def += rand(1, 5);
                } else {
                    $this->eqEnhFail++;
                }
            }
        }

        // Crafting 20%
        if (rand(1, 100) <= 20 && $this->level >= 300 && $this->materialsOwned >= 2) {
            $this->materialsOwned -= 2;
            if (rand(75, 85) <= 85) {
                $this->eqCrafted++;
                $this->atk += rand(10, 50);
                $this->def += rand(10, 50);
            }
        }

        // Alchemy 15%
        if (rand(1, 100) <= 15) {
            if (rand(1, 100) <= 65) $this->alchemyOk++;
            else $this->alchemyFail++;
        }

        // Pills 10%
        if (rand(1, 100) <= 10) {
            $this->pillsUsed++;
            $this->atk += rand(1, 5);
            $this->def += rand(1, 5);
            $this->maxHp += rand(10, 50);
            $this->hp = $this->maxHp;
        }

        // Spirit root upgrade
        if ($this->linhcanLv > 0 && $this->linhcanLv < 5) {
            $cost = 500 * pow(4, $this->linhcanLv);
            if ($this->lt >= $cost && rand(1, 100) <= max(20, 100 - $this->linhcanLv * 20)) {
                $this->lt -= $cost;
                $this->totalLtSpent += $cost;
                $this->linhcanLv++;
                $this->lcUpgrades++;
                $this->lcAbsorb += 5;
                $this->lcExpBonus += 3;
            }
        }

        // Body constitution upgrade
        if ($this->thechatLv > 0 && $this->thechatLv < 9) {
            $cost = 1000 * pow(3, $this->thechatLv);
            if ($this->lt >= $cost && rand(1, 100) <= max(10, 100 - $this->thechatLv * 10)) {
                $this->lt -= $cost;
                $this->totalLtSpent += $cost;
                $this->thechatLv++;
                $this->tcUpgrades++;
            }
        }

        // Bloodline awakening
        if ($this->huyetmachLv > 0 && $this->huyetmachLv < 4 && $this->level >= 100 * ($this->huyetmachLv + 1)) {
            if (rand(1, 100) <= 50) {
                $this->huyetmachLv++;
                $this->hmAwakenings++;
            }
        }

        // MT→LT exchange for heavy/whale
        if (($this->type == 'whale' || $this->type == 'heavy') && $this->mt > 1000 && $this->lt < 10000) {
            $exMt = min($this->mt, 10000);
            $this->mt -= $exMt;
            $this->totalMtSpent += $exMt;
            $this->lt += $exMt * 1000;
            $this->ltFromExchange += $exMt * 1000;
        }

        // Breakthrough when EXP full
        if ($this->exp >= $this->maxExp) {
            $this->tryBreakthrough();
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════
// GENERATE PLAYERS
// ═══════════════════════════════════════════════════════════════════════
$typePool = array();
for ($i = 0; $i < 40; $i++) $typePool[] = 'f2p';
for ($i = 0; $i < 25; $i++) $typePool[] = 'light';
for ($i = 0; $i < 18; $i++) $typePool[] = 'mid';
for ($i = 0; $i < 12; $i++) $typePool[] = 'heavy';
for ($i = 0; $i < 5; $i++) $typePool[] = 'whale';

$surnames = array('Nguyễn','Trần','Lê','Phạm','Hoàng','Hồ','Ngô','Dương','Lý','Vũ','Đặng','Bùi','Đỗ','Hồng','Mã','Tạ','Lương','Trương','Cao','Hà','La','Lưu','Sầm','Tô','Âu','Bành','Cung','Doãn','Hứa','Kiều','Lã','Lục','Mạc','Nghiêm','Nhâm','Phó','Phùng','Quách','Tăng','Thân','Thôi','Tiêu','Tôn','Trình','Từ','Việt');
$names = array('An','Bình','Cường','Dũng','Phúc','Giang','Hùng','Khang','Linh','Minh','Nam','Phương','Quân','Sơn','Thảo','Uyên','Vinh','Xuân','Yên','Bảo','Chi','Đông','Gia','Hải','Kiên','Lan','Mai','Ngân','Phong','Quỳnh','Trang','Vy','Yến','Anh','Bách','Cẩm','Dương','Hạnh','Khoa','Loan','Nghĩa','Sinh','Thành','Trung','Vân','Đan','Đình','Giao','Hòa','Kỳ','Long','Mỹ','Nhân','Phú','Tú','Đức','Cát','Dung','Hiếu','Lệ','Nga','Phi','Quyên','Thùy','Đài','Cúc','Hằng','Mai','Nhiên','Sương','Thơ','Tây','Bình','Duyên','Hương','Nhung','Oanh','Violet');

$players = array();
for ($i = 0; $i < TOTAL_PLAYERS; $i++) {
    $type = $typePool[array_rand($typePool)];
    $name = $surnames[array_rand($surnames)] . ' ' . $names[array_rand($names)];
    $p = new SimPlayer($i + 1, $name, $type);
    // Random spirit root 30%
    if (rand(1, 100) <= 30) { $p->linhcanLv = 1; $p->lcAbsorb = 5; $p->lcExpBonus = 3; }
    // Random body constitution 20%
    if (rand(1, 100) <= 20) { $p->thechatLv = 1; }
    // Random bloodline 15%
    if (rand(1, 100) <= 15) { $p->huyetmachLv = 1; }
    $players[] = $p;
}

$typeCounts = array();
foreach ($players as $p) { if (!isset($typeCounts[$p->type])) $typeCounts[$p->type] = 0; $typeCounts[$p->type]++; }
echo "Generated " . TOTAL_PLAYERS . " players: " . implode(', ', array_map(function($k,$v) { return "$k:$v"; }, array_keys($typeCounts), $typeCounts)) . "\n";

// ═══════════════════════════════════════════════════════════════════════
// SIMULATION
// ═══════════════════════════════════════════════════════════════════════
echo "Simulating " . SIM_DAYS . " days...\n";

for ($day = 1; $day <= SIM_DAYS; $day++) {
    if ($day % 60 == 0) echo "  Day $day\n";
    foreach ($players as $p) {
        $chance = 60 + ($p->vip * 3);
        if ($day > 1 && rand(1, 100) > $chance) continue;
        $p->dailyActivities();
    }
}
echo "Done.\n";

// ═══════════════════════════════════════════════════════════════════════
// STATISTICS
// ═══════════════════════════════════════════════════════════════════════
echo "Collecting stats...\n";

$types = array('f2p','light','mid','heavy','whale');
$byType = array();
foreach ($types as $t) {
    $byType[$t] = array(
        'count'=>0,'lvSum'=>0,'ltSum'=>0,'mtSum'=>0,
        'boss'=>0,'pve'=>0,'bt'=>0,'btMajor'=>0,'btMinor'=>0,
        'eqDrop'=>0,'eqCraft'=>0,'alchemy'=>0,
        'stones'=>0,'mat'=>0,'quests'=>0,'pot'=>0,'pill'=>0,
        'ltSpent'=>0,'mtSpent'=>0,'expGained'=>0,
        'lcUp'=>0,'tcUp'=>0,'hmAwk'=>0,
        'ltPve'=>0,'ltBoss'=>0,'ltQuest'=>0,'ltMedit'=>0,'ltExch'=>0,
        'expMedit'=>0,'expPve'=>0,'expBoss'=>0,'expQuest'=>0,
        'maxLv'=>0,'minLv'=>999999,
        'eqEnh'=>0,'eqEnhFail'=>0,'alchemyFail'=>0,
        'stonesUsed'=>0,'btFails'=>0,
        'meditFreeMin'=>0,'meditLtMin'=>0,'meditMtMin'=>0,
    );
}

$realmDist = array();
$allLtSpent = 0; $allMtSpent = 0; $allExp = 0;
$allBossKills = 0; $allPveKills = 0;
$allBtAttempts = 0; $allBtSuccess = 0; $allBtFails = 0;
$allBtMajorAttempts = 0; $allBtMajorSuccess = 0;
$allBtMinorAttempts = 0; $allBtMinorSuccess = 0;
$allEqDrop = 0; $allEqCraft = 0; $allEqEnh = 0; $allEqEnhFail = 0;
$allAlchemyOk = 0; $allAlchemyFail = 0;
$allMineStones = 0; $allMineMat = 0;
$allQuests = 0; $allPot = 0; $allPill = 0;
$allLcUp = 0; $allTcUp = 0; $allHmAwk = 0;
$allStonesUsed = 0; $allMaterialsOwned = 0;

$ltSrc = array('pve'=>0,'boss'=>0,'quest'=>0,'medit'=>0,'exchange'=>0);
$expSrc = array('medit'=>0,'pve'=>0,'boss'=>0,'quest'=>0);

$maxLv = 0; $maxP = null; $minLv = 999999; $minP = null; $lvSum = 0;

foreach ($players as $p) {
    $realm = $p->level < 50 ? 'Phàm Nhân' : ($p->level < 100 ? 'Phàm Nhân Đệ Nhất' : ($p->level < 150 ? 'Phàm Nhân Đệ Nhị' : ($p->level < 200 ? 'Phàm Nhân Đệ Tam' : ($p->level < 250 ? 'Luyện Khí' : ($p->level < 300 ? 'Trúc Cơ' : ($p->level < 350 ? 'Kim Đan' : 'Nguyên Anh'))))));
    if (!isset($realmDist[$realm])) $realmDist[$realm] = 0;
    $realmDist[$realm]++;

    $bt = &$byType[$p->type];
    $bt['count']++;
    $bt['lvSum'] += $p->level;
    $bt['ltSum'] += $p->lt;
    $bt['mtSum'] += $p->mt;
    $bt['boss'] += $p->bossKills;
    $bt['pve'] += $p->pveKills;
    $bt['bt'] += $p->btSuccess;
    $bt['btMajor'] += $p->btMajorSuccess;
    $bt['btMinor'] += $p->btMinorSuccess;
    $bt['btFails'] += $p->btFails;
    $bt['eqDrop'] += $p->eqDropped;
    $bt['eqCraft'] += $p->eqCrafted;
    $bt['alchemy'] += $p->alchemyOk;
    $bt['alchemyFail'] += $p->alchemyFail;
    $bt['stones'] += $p->miningStones;
    $bt['mat'] += $p->miningMat;
    $bt['quests'] += $p->questsDone;
    $bt['pot'] += $p->potionsUsed;
    $bt['pill'] += $p->pillsUsed;
    $bt['ltSpent'] += $p->totalLtSpent;
    $bt['mtSpent'] += $p->totalMtSpent;
    $bt['expGained'] += $p->totalExpGained;
    $bt['lcUp'] += $p->lcUpgrades;
    $bt['tcUp'] += $p->tcUpgrades;
    $bt['hmAwk'] += $p->hmAwakenings;
    $bt['ltPve'] += $p->ltFromPve;
    $bt['ltBoss'] += $p->ltFromBoss;
    $bt['ltQuest'] += $p->ltFromQuest;
    $bt['ltMedit'] += $p->ltFromMedit;
    $bt['ltExch'] += $p->ltFromExchange;
    $bt['expMedit'] += $p->expFromMedit;
    $bt['expPve'] += $p->expFromPve;
    $bt['expBoss'] += $p->expFromBoss;
    $bt['expQuest'] += $p->expFromQuest;
    $bt['eqEnh'] += $p->eqEnhanced;
    $bt['eqEnhFail'] += $p->eqEnhFail;
    $bt['meditFreeMin'] += $p->meditFreeMin;
    $bt['meditLtMin'] += $p->meditLtMin;
    $bt['meditMtMin'] += $p->meditMtMin;
    if ($p->level > $bt['maxLv']) $bt['maxLv'] = $p->level;
    if ($p->level < $bt['minLv']) $bt['minLv'] = $p->level;

    $allLtSpent += $p->totalLtSpent;
    $allMtSpent += $p->totalMtSpent;
    $allExp += $p->totalExpGained;
    $allBossKills += $p->bossKills;
    $allPveKills += $p->pveKills;
    $allBtAttempts += $p->btAttempts;
    $allBtSuccess += $p->btSuccess;
    $allBtFails += $p->btFails;
    $allBtMajorAttempts += $p->btMajorAttempts;
    $allBtMajorSuccess += $p->btMajorSuccess;
    $allBtMinorAttempts += $p->btMinorAttempts;
    $allBtMinorSuccess += $p->btMinorSuccess;
    $allEqDrop += $p->eqDropped;
    $allEqCraft += $p->eqCrafted;
    $allEqEnh += $p->eqEnhanced;
    $allEqEnhFail += $p->eqEnhFail;
    $allAlchemyOk += $p->alchemyOk;
    $allAlchemyFail += $p->alchemyFail;
    $allMineStones += $p->miningStones;
    $allMineMat += $p->miningMat;
    $allQuests += $p->questsDone;
    $allPot += $p->potionsUsed;
    $allPill += $p->pillsUsed;
    $allLcUp += $p->lcUpgrades;
    $allTcUp += $p->tcUpgrades;
    $allHmAwk += $p->hmAwakenings;
    $allStonesUsed += $p->stonesOwned;

    $ltSrc['pve'] += $p->ltFromPve;
    $ltSrc['boss'] += $p->ltFromBoss;
    $ltSrc['quest'] += $p->ltFromQuest;
    $ltSrc['medit'] += $p->ltFromMedit;
    $ltSrc['exchange'] += $p->ltFromExchange;
    $expSrc['medit'] += $p->expFromMedit;
    $expSrc['pve'] += $p->expFromPve;
    $expSrc['boss'] += $p->expFromBoss;
    $expSrc['quest'] += $p->expFromQuest;

    $lvSum += $p->level;
    if ($p->level > $maxLv) { $maxLv = $p->level; $maxP = $p; }
    if ($p->level < $minLv) { $minLv = $p->level; $minP = $p; }
}

// ═══════════════════════════════════════════════════════════════════════
// REPORT
// ═══════════════════════════════════════════════════════════════════════
$avgLv = round($lvSum / TOTAL_PLAYERS, 1);
$btRate = $allBtAttempts > 0 ? round($allBtSuccess / $allBtAttempts * 100, 1) : 0;
$btMajorRate = $allBtMajorAttempts > 0 ? round($allBtMajorSuccess / $allBtMajorAttempts * 100, 1) : 0;
$btMinorRate = $allBtMinorAttempts > 0 ? round($allBtMinorSuccess / $allBtMinorAttempts * 100, 1) : 0;

$r = "";
$r .= "# BÁO CÁO MÔ PHỎNG TRÒ CHƠI\n\n";
$r .= "200 Nhân vật × " . SIM_DAYS . " ngày — Dùng công thức gốc từ game code\n\n";

// 1. Distribution
$r .= "## 1. PHÂN BỔ NGƯỜI CHƠI\n\n";
$r .= "| Loại | SL | % |\n|------|-----|---|\n";
foreach ($typeCounts as $t => $c) $r .= "| $t | $c | " . round($c / TOTAL_PLAYERS * 100, 1) . "% |\n";
$r .= "\n";

// 2. Overall
$r .= "## 2. TỔNG QUAN PROGRESSION\n\n";
$r .= "| Chỉ số | Tổng | TB/Người |\n|--------|------|----------|\n";
$r .= "| Level TB | $avgLv | $avgLv |\n";
$r .= "| Level max | $maxLv | " . ($maxP ? $maxP->name . ' (' . $maxP->type . ')' : 'N/A') . " |\n";
$r .= "| Level min | $minLv | " . ($minP ? $minP->name . ' (' . $minP->type . ')' : 'N/A') . " |\n";
$r .= "| LT tiêu | " . number_format($allLtSpent) . " | " . number_format(round($allLtSpent / TOTAL_PLAYERS)) . " |\n";
$r .= "| MT tiêu | " . number_format($allMtSpent) . " | " . number_format(round($allMtSpent / TOTAL_PLAYERS)) . " |\n";
$r .= "| EXP nhận | " . number_format($allExp) . " | " . number_format(round($allExp / TOTAL_PLAYERS)) . " |\n";
$r .= "| Boss diệt | " . number_format($allBossKills) . " | " . number_format(round($allBossKills / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| PvE diệt | " . number_format($allPveKills) . " | " . number_format(round($allPveKills / TOTAL_PLAYERS)) . " |\n";
$r .= "| Đột Phá thử | " . number_format($allBtAttempts) . " | " . number_format(round($allBtAttempts / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Đột Phá thành công | " . number_format($allBtSuccess) . " | " . number_format(round($allBtSuccess / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Tỷ lệ ĐP tổng | **$btRate%** | - |\n";
$r .= "| Tỷ lệ ĐP Major | **$btMajorRate%** | " . number_format($allBtMajorAttempts) . " lần thử |\n";
$r .= "| Tỷ lệ ĐP Minor | **$btMinorRate%** | " . number_format($allBtMinorAttempts) . " lần thử |\n";
$r .= "| ĐP thất bại | " . number_format($allBtFails) . " | " . number_format(round($allBtFails / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Trang bị rớt | " . number_format($allEqDrop) . " | " . number_format(round($allEqDrop / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Trang bị chế tạo | " . number_format($allEqCraft) . " | " . number_format(round($allEqCraft / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Cường hóa | " . number_format($allEqEnh) . " (TB: $allEqEnhFail) | " . ($allEqEnh > 0 ? round(($allEqEnh - $allEqEnhFail) / $allEqEnh * 100, 1) : 0) . "% |\n";
$r .= "| Luyện đan | " . number_format($allAlchemyOk) . " (TB: $allAlchemyFail) | " . (($allAlchemyOk + $allAlchemyFail) > 0 ? round($allAlchemyOk / ($allAlchemyOk + $allAlchemyFail) * 100, 1) : 0) . "% |\n";
$r .= "| Đá đào | " . number_format($allMineStones) . " | " . number_format(round($allMineStones / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Nhiệm vụ | " . number_format($allQuests) . " | " . number_format(round($allQuests / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Thuốc dùng | " . number_format($allPot) . " | " . number_format(round($allPot / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Đan dược dùng | " . number_format($allPill) . " | " . number_format(round($allPill / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| LC thăng cấp | " . number_format($allLcUp) . " | " . number_format(round($allLcUp / TOTAL_PLAYERS, 2)) . " |\n";
$r .= "| TC thăng cấp | " . number_format($allTcUp) . " | " . number_format(round($allTcUp / TOTAL_PLAYERS, 2)) . " |\n";
$r .= "| HM khai mở | " . number_format($allHmAwk) . " | " . number_format(round($allHmAwk / TOTAL_PLAYERS, 2)) . " |\n\n";

// 3. Realm distribution
$r .= "## 3. PHÂN BỔ CẢNH GIỚI\n\n";
$r .= "| Cảnh Giới | SL | % |\n|------------|-----|---|\n";
arsort($realmDist);
foreach ($realmDist as $realm => $cnt) $r .= "| $realm | $cnt | " . round($cnt / TOTAL_PLAYERS * 100, 1) . "% |\n";
$r .= "\n";

// 4. Type comparison
$r .= "## 4. SO SÁNH THEO LOẠI (TB/Người)\n\n";
$r .= "| Chỉ số | F2P | Light | Mid | Heavy | Whale |\n|--------|-----|-------|-----|-------|-------|\n";
$rows = array(
    'Level TB' => function($b) { return $b['count'] > 0 ? round($b['lvSum'] / $b['count'], 1) : '-'; },
    'Level max' => function($b) { return $b['maxLv']; },
    'LT tiêu TB' => function($b) { return $b['count'] > 0 ? number_format(round($b['ltSpent'] / $b['count'])) : '-'; },
    'MT tiêu TB' => function($b) { return $b['count'] > 0 ? number_format(round($b['mtSpent'] / $b['count'])) : '-'; },
    'Boss/Người' => function($b) { return $b['count'] > 0 ? round($b['boss'] / $b['count'], 1) : '-'; },
    'PvE/Người' => function($b) { return $b['count'] > 0 ? round($b['pve'] / $b['count']) : '-'; },
    'ĐP thành công' => function($b) { return $b['count'] > 0 ? round($b['bt'] / $b['count'], 1) : '-'; },
    'ĐP Major' => function($b) { return $b['count'] > 0 ? round($b['btMajor'] / $b['count'], 1) : '-'; },
    'ĐP Minor' => function($b) { return $b['count'] > 0 ? round($b['btMinor'] / $b['count'], 1) : '-'; },
    'Trang bị rớt' => function($b) { return $b['count'] > 0 ? round($b['eqDrop'] / $b['count'], 1) : '-'; },
    'Nhiệm vụ' => function($b) { return $b['count'] > 0 ? round($b['quests'] / $b['count'], 1) : '-'; },
    'Thuốc' => function($b) { return $b['count'] > 0 ? round($b['pot'] / $b['count'], 1) : '-'; },
    'Đan dược' => function($b) { return $b['count'] > 0 ? round($b['pill'] / $b['count'], 1) : '-'; },
    'TL miễn phí (phút)' => function($b) { return $b['count'] > 0 ? number_format(round($b['meditFreeMin'] / $b['count'])) : '-'; },
    'TL LT x2 (phút)' => function($b) { return $b['count'] > 0 ? number_format(round($b['meditLtMin'] / $b['count'])) : '-'; },
    'TL MT x3 (phút)' => function($b) { return $b['count'] > 0 ? number_format(round($b['meditMtMin'] / $b['count'])) : '-'; },
);
foreach ($rows as $label => $fn) {
    $r .= "| $label |";
    foreach ($types as $t) $r .= " " . $fn($byType[$t]) . " |";
    $r .= "\n";
}
$r .= "\n";

// 5. LT Sources
$r .= "## 5. NGUỒN LT\n\n";
$r .= "| Nguồn | Tổng | % |\n|--------|------|---|\n";
$totalLt = array_sum($ltSrc);
foreach ($ltSrc as $src => $amt) $r .= "| " . ucfirst($src) . " | " . number_format($amt) . " | " . ($totalLt > 0 ? round($amt / $totalLt * 100, 1) : 0) . "% |\n";
$r .= "| **TỔNG** | **" . number_format($totalLt) . "** | 100% |\n\n";

// 6. EXP Sources
$r .= "## 6. NGUỒN EXP\n\n";
$r .= "| Nguồn | Tổng | % |\n|--------|------|---|\n";
$totalExp = array_sum($expSrc);
foreach ($expSrc as $src => $amt) $r .= "| " . ucfirst($src) . " | " . number_format($amt) . " | " . ($totalExp > 0 ? round($amt / $totalExp * 100, 1) : 0) . "% |\n";
$r .= "| **TỔNG** | **" . number_format($totalExp) . "** | 100% |\n\n";

// 7. Resource consumption
$r .= "## 7. TIÊU HÀM NGUYÊN LIỆU\n\n";
$r .= "| Nguyên liệu | Tổng | TB/Người |\n|-------------|------|----------|\n";
$r .= "| LT tiêu | " . number_format($allLtSpent) . " | " . number_format(round($allLtSpent / TOTAL_PLAYERS)) . " |\n";
$r .= "| MT tiêu | " . number_format($allMtSpent) . " | " . number_format(round($allMtSpent / TOTAL_PLAYERS)) . " |\n";
$r .= "| Đá cường hóa | " . number_format($allMineStones) . " | " . number_format(round($allMineStones / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Nhiệm vụ | " . number_format($allQuests) . " | " . number_format(round($allQuests / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Thuốc hồi máu | " . number_format($allPot) . " | " . number_format(round($allPot / TOTAL_PLAYERS, 1)) . " |\n";
$r .= "| Đan dược | " . number_format($allPill) . " | " . number_format(round($allPill / TOTAL_PLAYERS, 1)) . " |\n\n";

// 8. Breakthrough cost table (from game code)
$r .= "## 8. BẢNG CHI PHÍ ĐỘT PHÁ (Công thức gốc)\n\n";
$r .= "| Level | Major (LT) | Minor (LT) | Tỷ lệ (jv=0) | Tỷ lệ (jv=5) |\n";
$r .= "|-------|-----------|------------|---------------|---------------|\n";
for ($lv = 50; $lv <= 500; $lv += 50) {
    $major = number_format(breakthroughCost($lv, true));
    $minor = number_format(breakthroughCost($lv, false));
    $rate0 = breakthroughRate($lv, 0, ($lv % 50 == 0) ? 10 : 0);
    $rate5 = breakthroughRate($lv, 5, ($lv % 50 == 0) ? 10 : 0);
    $r .= "| $lv | $major | $minor | $rate0% | $rate5% |\n";
}
$r .= "\n";

// 9. Top 10
$r .= "## 9. TOP 10\n\n";
$r .= "| # | Tên | Loại | Level | LT tiêu | Boss |\n|---|------|------|-------|---------|------|\n";
usort($players, function($a, $b) { return $b->level - $a->level; });
for ($i = 0; $i < min(10, count($players)); $i++) {
    $p = $players[$i];
    $r .= "| " . ($i + 1) . " | " . $p->name . " | " . $p->type . " | " . $p->level . " | " . number_format($p->totalLtSpent) . " | " . $p->bossKills . " |\n";
}
$r .= "\n";

// 10. Bottom 10
$r .= "## 10. BOTTOM 10\n\n";
$r .= "| # | Tên | Loại | Level | Ngày đăng nhập |\n|---|------|------|-------|----------------|\n";
usort($players, function($a, $b) { return $a->level - $b->level; });
for ($i = 0; $i < min(10, count($players)); $i++) {
    $p = $players[$i];
    $r .= "| " . ($i + 1) . " | " . $p->name . " | " . $p->type . " | " . $p->level . " | " . $p->daysActive . " |\n";
}
$r .= "\n";

// 11. P2W analysis
$f2pAvgLv = $byType['f2p']['count'] > 0 ? round($byType['f2p']['lvSum'] / $byType['f2p']['count'], 1) : 0;
$whaleAvgLv = $byType['whale']['count'] > 0 ? round($byType['whale']['lvSum'] / $byType['whale']['count'], 1) : 0;
$p2wRatio = $f2pAvgLv > 0 ? round($whaleAvgLv / $f2pAvgLv, 2) : 'N/A';

$r .= "## 11. PHÂN TÍCH P2W\n\n";
$r .= "| Loại | Level TB | LT tiêu TB | MT tiêu TB |\n|------|----------|------------|------------|\n";
foreach ($types as $t) {
    $bt = $byType[$t];
    $avgLvT = $bt['count'] > 0 ? round($bt['lvSum'] / $bt['count'], 1) : 0;
    $avgLtT = $bt['count'] > 0 ? number_format(round($bt['ltSpent'] / $bt['count'])) : '0';
    $avgMtT = $bt['count'] > 0 ? number_format(round($bt['mtSpent'] / $bt['count'])) : '0';
    $r .= "| $t | $avgLvT | $avgLtT | $avgMtT |\n";
}
$r .= "\n**P2W Ratio: {$p2wRatio}x** (Whale/F2P)\n\n";

// 12. Economy health
$totalLtInEconomy = 0;
foreach ($players as $p) $totalLtInEconomy += $p->lt;
$r .= "## 12. SỨC KHỎE KINH TẾ\n\n";
$r .= "- LT tồn cuối: " . number_format($totalLtInEconomy) . "\n";
$r .= "- LT đã tiêu: " . number_format($allLtSpent) . "\n";
$r .= "- LT tạo ra: " . number_format($totalLt) . "\n";
$r .= "- Tỷ lệ Tiêu/Tạo: " . ($totalLt > 0 ? round($allLtSpent / $totalLt * 100, 1) : 0) . "%\n";
$r .= "- ĐP thành công: $btRate% (Major: $btMajorRate%, Minor: $btMinorRate%)\n\n";

$r .= "---\n*Mô phỏng dùng công thức gốc từ game code.*\n";

file_put_contents('D:\\XXJserver\\phpstudy_pro\\WWW\\docs\\SIMULATION_REPORT.md', $r);
echo "\n=== BÁO CÁO ĐÃ LƯU ===\n";
echo "Avg Level: $avgLv | Max: $maxLv | BT Rate: $btRate% | Major: $btMajorRate% | Minor: $btMinorRate%\n";
