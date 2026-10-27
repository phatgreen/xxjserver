<?php
// Phường thị / Giao dịch - giao diện mới
$player = \player\getplayer($sid, $dblj);
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");

if (!isset($yeshu)) {
    $yeshu = 0;
}

if (!isset($fangshi)) {
    $fangshi = 'daoju';
}

$fangshi = trim($fangshi);
$payhtml = '';
$noticeHtml = '';
$pdjcount = 0;


if (!function_exists('tcg_market_h')) {
    function tcg_market_h($str)
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('tcg_market_notice')) {
    function tcg_market_notice($type, $msg)
    {
        $type = ($type == 'success') ? 'success' : 'error';
        return '<div class="tcg-market-notice '.$type.'">'.tcg_market_h($msg).'</div>';
    }
}

if (!function_exists('tcg_market_item_daoju')) {
    function tcg_market_item_daoju($index, $name, $count, $price, $infoCmd, $buy1Cmd, $buy5Cmd, $buy10Cmd, $retractCmd = '')
    {
        $name  = tcg_market_h($name);
        $count = intval($count);
        $price = intval($price);
        $action = $retractCmd !== ''
            ? '<a class="tcg-market-retract" href="?cmd=' . $retractCmd . '">Thu hồi</a>'
            : '<a class="tcg-market-buy" href="?cmd=' . $buy1Cmd . '">Mua 1</a>';
        return <<<HTML
<div class="tcg-market-item">
    <div class="tcg-market-icon">📦</div>
    <div class="tcg-market-info">
        <a class="tcg-market-name" href="?cmd=$infoCmd">[$index] $name</a>
        <div class="tcg-market-meta">
            <span class="tcg-market-chip count">Số lượng: $count</span>
            <span class="tcg-market-chip price">Giá: $price Linh thạch</span>
        </div>
    </div>
    <div class="tcg-market-buybox">
        $action
        <!--<a class="tcg-market-buy alt" href="?cmd=$buy5Cmd">Mua 5</a>
        <a class="tcg-market-buy alt" href="?cmd=$buy10Cmd">Mua 10</a>-->
    </div>
</div>
HTML;
    }
}

if (!function_exists('tcg_market_item_zb')) {
    function tcg_market_item_zb($index, $name, $color, $qh, $price, $infoCmd, $buyCmd, $hot, $img = '', $retractCmd = '', $tipAttr = '')
    {
        $name  = tcg_market_h($name);
        $color = trim((string)$color);
        if ($color == '') {
            $color = '#ffffff';
        }
        $qhText = '';
        if ($qh !== '' && intval($qh) != 0) {
            $qhText = '<span class="tcg-market-chip qh">+'.intval($qh).'</span>';
        }
        $price = intval($price);
        $icon = ($img !== '' ? '<img src="'.$img.'" alt="">' : ($hot ? '🔥' : '⚔️'));
        $buyClass = $hot ? 'tcg-market-buy alt' : 'tcg-market-buy';
        // Tin do chính mình treo: cho thu hồi thay vì tự mua lại.
        $action = $retractCmd !== ''
            ? '<a class="tcg-market-retract" href="?cmd=' . $retractCmd . '">Thu hồi</a>'
            : '<a class="' . $buyClass . '" href="?cmd=' . $buyCmd . '">Mua</a>';
        return <<<HTML
<div class="tcg-market-item">
    <div class="tcg-market-icon">{$icon}</div>
    <div class="tcg-market-info">
        <a class="tcg-market-name"{$tipAttr} href="?cmd=$infoCmd" style="color:$color!important">[$index] $name</a>
        <div class="tcg-market-meta">
            $qhText
            <span class="tcg-market-chip price">Giá: $price Linh thạch</span>
        </div>
    </div>
    <div class="tcg-market-buybox">
        $action
    </div>
</div>
HTML;
    }
}

$daojuCmd     = $encode->encode("cmd=fangshi&fangshi=daoju&sid=$sid");
$zhuangbeiCmd = $encode->encode("cmd=fangshi&fangshi=zhuangbei&sid=$sid");
$shangdianCmd = $encode->encode("cmd=fangshi&fangshi=shangdian&sid=$sid");

switch ($fangshi) {
    case 'daoju':
        if (isset($canshu) && $canshu == 'buy') {
            $payid = isset($payid) ? intval($payid) : 0;
            $buycount = isset($buycount) ? max(1, intval($buycount)) : 1;
            $fsdj = \player\getfangshi_once('daoju', $payid, $dblj);
            try {
                if (!$fsdj) {
                    throw new PDOException('Đạo cụ đã bán sạch');
                }

                $playerdj = \player\getplayerdaoju($sid, $fsdj->djid, $dblj);
                if ($playerdj) {
                    $pdjcount = intval($playerdj->djsum);
                }

                $price = intval($buycount) * intval($fsdj->pay);

                // fangshi_dj là MyISAM nên lệnh DELETE không rollback được.
                // Kiểm tra chốt (chỉ đọc) trước để không bỏ dở giao dịch giữa chừng.
                $seller = $dblj->prepare("SELECT COUNT(*) FROM game1 WHERE uid = :uid");
                $seller->execute(array(':uid' => intval($fsdj->uid)));
                if (intval($seller->fetchColumn()) === 0) {
                    throw new PDOException('Tu sĩ treo đạo cụ này không còn tồn tại');
                }

                $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
                $dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dblj->beginTransaction();

                // MySQL prepare kiểu native không cho dùng lại cùng một tên tham số
                // trong một câu lệnh, nên cần :price / :price_min riêng biệt.
                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - :price WHERE uyxb >= :price_min AND sid = :sid");
                $stmt->execute(array(':price' => $price, ':price_min' => $price, ':sid' => $sid));
                if ($stmt->rowCount() == 0 && $price > 0) {
                    throw new PDOException('Linh thạch không đủ');
                }

                $stmt = $dblj->prepare("UPDATE fangshi_dj SET djcount = djcount - :cnt WHERE djcount >= :cnt_min AND payid = :payid");
                $stmt->execute(array(':cnt' => $buycount, ':cnt_min' => $buycount, ':payid' => intval($payid)));
                if ($stmt->rowCount() == 0) {
                    throw new PDOException('Trong phường thị đạo cụ này không đủ');
                }

                $priceAfterTax = floor($price * 0.9);
                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + :price WHERE uid = :uid");
                $stmt->execute(array(':price' => $priceAfterTax, ':uid' => intval($fsdj->uid)));
                if ($stmt->rowCount() == 0 && $priceAfterTax > 0) {
                    throw new PDOException('Tu sĩ treo đạo cụ này chưa nhận được Linh thạch');
                }

                $djsum = $pdjcount + $buycount;
                $djid = intval($fsdj->djid);
                // Không dùng REPLACE INTO: câu đó xoá rồi chèn nên ghi đè cả cột djused
                // (số lần đã dùng). Tự mua lại tin của mình sẽ reset giới hạn 2 lần
                // dùng của đan, thành chỗ farm đan miễn phí.
                $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = :djsum WHERE uid = :uid AND djid = :djid");
                $stmt->execute(array(':djsum' => $djsum, ':uid' => intval($player->uid), ':djid' => $djid));
                if ($stmt->rowCount() == 0) {
                    $ins = $dblj->prepare("INSERT INTO playerdaoju(djid, djsum, uid, sid, djname, djinfo, djzl) VALUES (:djid2, :djsum2, :uid2, :sid2, :djname, :djinfo, 0)");
                    $ins->execute(array(
                        ':djid2' => $djid, ':djsum2' => $djsum, ':uid2' => intval($player->uid), ':sid2' => $sid,
                        ':djname' => $fsdj->djname, ':djinfo' => $fsdj->djinfo
                    ));
                }
                $delivered = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE uid = :uid AND djid = :djid");
                $delivered->execute(array(':uid' => intval($player->uid), ':djid' => $djid));
                if (intval($delivered->fetchColumn()) !== $djsum) {
                    throw new PDOException('Trong lúc truyền tống đạo cụ, truyền tống thất bại');
                }

                $dblj->commit();
                $noticeHtml .= tcg_market_notice('success', 'Giao dịch thành công');
                \player\changerwyq1(1, $fsdj->djid, 1, $sid, $dblj);
            } catch (PDOException $e) {
                if ($dblj->inTransaction()) {
                    $dblj->rollBack();
                }
                $noticeHtml .= tcg_market_notice('error', $e->getMessage());
            }
            $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
            $dblj->exec("delete from `fangshi_dj` where djcount = 0");
        }

        if (isset($canshu) && $canshu == 'retract') {
            // Treo bán đạo cụ đã trừ djsum ngay lúc treo, nên nếu không có nút thu hồi
            // thì chủ sở hữu mất đạo cụ vĩnh viễn khi không ai mua.
            $payid = isset($payid) ? intval($payid) : 0;
            $fsdj = \player\getfangshi_once('daoju', $payid, $dblj);
            if (!$fsdj) {
                $noticeHtml .= tcg_market_notice('error', 'Tin treo bán không tồn tại');
            } elseif (intval($fsdj->uid) !== intval($player->uid)) {
                $noticeHtml .= tcg_market_notice('error', 'Đây không phải đạo cụ do ngươi treo bán');
            } else {
                $djid = intval($fsdj->djid);
                $djcount = intval($fsdj->djcount);

                // Xoá tin treo TRƯỚC khi trả đạo cụ. playerdaoju và fangshi_dj đều là
                // MyISAM (không rollback được), nên nếu làm ngược lại thì một lần lỗi
                // ở bước sau cho phép bấm thu hồi lặp lại để nhân đôi đạo cụ.
                $claim = $dblj->prepare("DELETE FROM fangshi_dj WHERE payid = :payid AND uid = :uid");
                $claim->execute(array(':payid' => $payid, ':uid' => intval($player->uid)));
                if ($claim->rowCount() == 0) {
                    $noticeHtml .= tcg_market_notice('error', 'Tin treo bán không còn tồn tại');
                } else {
                    $before = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE uid = :uid AND djid = :djid");
                    $before->execute(array(':uid' => intval($player->uid), ':djid' => $djid));
                    $row = $before->fetchColumn();
                    $now = ($row === false) ? 0 : intval($row);

                    $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + :cnt WHERE uid = :uid AND djid = :djid");
                    $stmt->execute(array(':cnt' => $djcount, ':uid' => intval($player->uid), ':djid' => $djid));
                    if ($stmt->rowCount() == 0) {
                        // Chủ sở hữu đã dùng hết nên dòng đạo cụ không còn trong túi: tạo lại.
                        $ins = $dblj->prepare("INSERT INTO playerdaoju(djid, djsum, uid, sid, djname, djinfo, djzl) VALUES (:djid2, :djsum, :uid2, :sid, :djname, :djinfo, 0)");
                        $ins->execute(array(
                            ':djid2' => $djid, ':djsum' => $djcount, ':uid2' => intval($player->uid), ':sid' => $sid,
                            ':djname' => $fsdj->djname, ':djinfo' => $fsdj->djinfo
                        ));
                    }

                    $back = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE uid = :uid AND djid = :djid");
                    $back->execute(array(':uid' => intval($player->uid), ':djid' => $djid));
                    if (intval($back->fetchColumn()) !== $now + $djcount) {
                        $noticeHtml .= tcg_market_notice('error', "Đã gỡ tin treo nhưng chưa trả đủ $djcount đạo cụ về túi đồ");
                    } else {
                        $noticeHtml .= tcg_market_notice('success', 'Đã thu hồi đạo cụ về túi đồ');
                    }
                }
            }
        }

        $fsdjall = \player\getfangshi_all($fangshi, $dblj);
        $index = 0;
        foreach ($fsdjall as $fsdj) {
            $index++;
            $djid = $fsdj['djid'];
            $djname = $fsdj['djname'];
            $djpay = $fsdj['pay'];
            $djcount = $fsdj['djcount'];
            $payid = $fsdj['payid'];
            $goumaidj1 = $encode->encode("cmd=fangshi&fangshi=daoju&canshu=buy&payid=$payid&buycount=1&sid=$sid");
            $goumaidj5 = $encode->encode("cmd=fangshi&fangshi=daoju&canshu=buy&payid=$payid&buycount=5&sid=$sid");
            $goumaidj10 = $encode->encode("cmd=fangshi&fangshi=daoju&canshu=buy&payid=$payid&buycount=10&sid=$sid");
            $djpaycmd = $encode->encode("cmd=djinfo&djid=$djid&sid=$sid");
            $thuhoijd = intval($fsdj['uid']) === intval($player->uid)
                ? $encode->encode("cmd=fangshi&fangshi=daoju&canshu=retract&payid=$payid&sid=$sid")
                : '';
            $payhtml .= tcg_market_item_daoju($index, $djname, $djcount, $djpay, $djpaycmd, $goumaidj1, $goumaidj5, $goumaidj10, $thuhoijd);
        }
        break;

    case 'zhuangbei':
        if (isset($canshu) && $canshu == 'buy') {
            $payid = isset($payid) ? intval($payid) : 0;
            try {
                $fszb = \player\getfangshi_once('zhuangbei', $payid, $dblj);
                if (!$fszb) {
                    throw new PDOException('Trang bị đã được bán ra');
                }

                $pay = intval($fszb->pay);
                $zbnowid = intval($fszb->zbnowid);
                $sellerUid = intval($fszb->uid);

                // fangshi_zb và playerzhuangbei đều là MyISAM: những câu DELETE/UPDATE
                // ở dưới commit ngay lập tức, rollBack() chỉ cứu được game1.
                // Vì vậy phải xác nhận trước (chỉ đọc) là mọi bước sẽ thành công.
                $chkSeller = $dblj->prepare("SELECT COUNT(*) FROM game1 WHERE uid = :uid");
                $chkSeller->execute(array(':uid' => $sellerUid));
                if (intval($chkSeller->fetchColumn()) === 0) {
                    throw new PDOException('Tu sĩ treo trang bị này không còn tồn tại');
                }

                // Khi treo bán, zbinfo.php detach vật phẩm (uid=0, sid=''),
                // nên chỉ cần xác nhận dòng vật phẩm còn tồn tại và chưa rơi vào
                // tay người thứ ba.
                $chkZb = $dblj->prepare("SELECT uid FROM playerzhuangbei WHERE zbnowid = :zbnowid");
                $chkZb->execute(array(':zbnowid' => $zbnowid));
                $itemUid = $chkZb->fetchColumn();
                if ($itemUid === false) {
                    throw new PDOException('Trang bị này không còn tồn tại');
                }
                if (intval($itemUid) !== 0 && intval($itemUid) !== $sellerUid) {
                    throw new PDOException('Trang bị này không còn thuộc về người bán');
                }

                $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
                $dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dblj->beginTransaction();

                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - :pay WHERE uyxb >= :pay_min AND sid = :sid");
                $stmt->execute(array(':pay' => $pay, ':pay_min' => $pay, ':sid' => $sid));
                if ($stmt->rowCount() == 0 && $pay > 0) {
                    throw new PDOException('Linh thạch không đủ');
                }

                $stmt = $dblj->prepare("DELETE FROM fangshi_zb WHERE payid = :payid");
                $stmt->execute(array(':payid' => intval($payid)));
                if ($stmt->rowCount() == 0) {
                    throw new PDOException('Trang bị xuất hàng thất bại');
                }

                $priceAfterTax = floor($pay * 0.9);
                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + :pay WHERE uid = :uid");
                $stmt->execute(array(':pay' => $priceAfterTax, ':uid' => $sellerUid));
                if ($stmt->rowCount() == 0 && $priceAfterTax > 0) {
                    throw new PDOException('Tu sĩ treo trang bị này chưa nhận được Linh thạch');
                }

                $stmt = $dblj->prepare("UPDATE playerzhuangbei SET sid = :sid, uid = :uid WHERE zbnowid = :zbnowid");
                $stmt->execute(array(':sid' => $sid, ':uid' => intval($player->uid), ':zbnowid' => $zbnowid));
                // Mua lại chính món mình treo thì UPDATE không đổi gì, rowCount = 0.
                // Xác nhận bằng truy vấn đọc thay vì đếm số dòng bị ảnh hưởng.
                $delivered = $dblj->prepare("SELECT COUNT(*) FROM playerzhuangbei WHERE zbnowid = :zbnowid AND uid = :uid AND sid = :sid");
                $delivered->execute(array(':zbnowid' => $zbnowid, ':uid' => intval($player->uid), ':sid' => $sid));
                if (intval($delivered->fetchColumn()) === 0) {
                    throw new PDOException('Trang bị truyền tống thất bại');
                }

                $dblj->commit();
                $noticeHtml .= tcg_market_notice('success', 'Giao dịch thành công');
            } catch (PDOException $e) {
                if ($dblj->inTransaction()) {
                    $dblj->rollBack();
                }
                $noticeHtml .= tcg_market_notice('error', $e->getMessage());
            }
            $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
        }

        if (isset($canshu) && $canshu == 'retract') {
            // Thu hồi tin treo bán: vật phẩm bị detach (uid=0, sid='') khi treo,
            // nên nếu không có nút này thì chủ sở hữu mất vật phẩm vĩnh viễn
            // khi không ai mua.
            $payid = isset($payid) ? intval($payid) : 0;
            $fszb = \player\getfangshi_once('zhuangbei', $payid, $dblj);
            if (!$fszb) {
                $noticeHtml .= tcg_market_notice('error', 'Tin treo bán không tồn tại');
            } elseif (intval($fszb->uid) !== intval($player->uid)) {
                $noticeHtml .= tcg_market_notice('error', 'Đây không phải trang bị do ngươi treo bán');
            } else {
                $zbnowid = intval($fszb->zbnowid);
                $stmt = $dblj->prepare("UPDATE playerzhuangbei SET sid = :sid, uid = :uid WHERE zbnowid = :zbnowid");
                $stmt->execute(array(':sid' => $sid, ':uid' => intval($player->uid), ':zbnowid' => $zbnowid));
                $back = $dblj->prepare("SELECT COUNT(*) FROM playerzhuangbei WHERE zbnowid = :zbnowid AND uid = :uid AND sid = :sid");
                $back->execute(array(':zbnowid' => $zbnowid, ':uid' => intval($player->uid), ':sid' => $sid));
                if (intval($back->fetchColumn()) === 0) {
                    $noticeHtml .= tcg_market_notice('error', 'Thu hồi thất bại');
                } else {
                    $stmt = $dblj->prepare("DELETE FROM fangshi_zb WHERE payid = :payid");
                    $stmt->execute(array(':payid' => $payid));
                    $noticeHtml .= tcg_market_notice('success', 'Đã thu hồi trang bị về túi đồ');
                }
            }
        }

        $fsdjall = \player\getfangshi_all($fangshi, $dblj);
        $index = 0;
        foreach ($fsdjall as $fsdj) {
            $index++;
            $zbnowid = $fsdj['zbnowid'];
            $zbname = $fsdj['zbname'];
            $zbys = $fsdj['zbys'];
            $zbqh = $fsdj['qianghua'];
            $zbpay = $fsdj['pay'];
            $payid = $fsdj['payid'];
            $goumaizb = $encode->encode("cmd=fangshi&fangshi=zhuangbei&canshu=buy&payid=$payid&sid=$sid");
            $thuhoizb = intval($fsdj['uid']) === intval($player->uid)
                ? $encode->encode("cmd=fangshi&fangshi=zhuangbei&canshu=retract&payid=$payid&sid=$sid")
                : '';
            $zbpaycmd = $encode->encode("cmd=zbinfo&zbnowid=$zbnowid&sid=$sid");
            $zbimg = \player\zhuangbei_image_path((object) array('zbid' => intval($fsdj['zbid'])), '');
            $zbTip = \player\itemtip_attr_for_listing($fsdj, $player, $dblj);
            $payhtml .= tcg_market_item_zb($index, $zbname, $zbys, $zbqh, $zbpay, $zbpaycmd, $goumaizb, false, $zbimg, $thuhoizb, $zbTip);
        }
        break;

    case 'shangdian':
        if (isset($canshu) && $canshu == 'buy') {
            $payid = isset($payid) ? intval($payid) : 0;
            try {
                $sd = \player\getfangshi_once('shangdian', $payid, $dblj);
                if (!$sd) {
                    throw new PDOException('Trang bị đã được bán ra');
                }

                $pay = intval($sd->pay);
                $sdZbnowid = intval($sd->zbnowid);
                $sdSellerUid = intval($sd->uid);

                $chkSeller = $dblj->prepare("SELECT COUNT(*) FROM game1 WHERE uid = :uid");
                $chkSeller->execute(array(':uid' => $sdSellerUid));
                if (intval($chkSeller->fetchColumn()) === 0) {
                    throw new PDOException('Tu sĩ treo trang bị này không còn tồn tại');
                }

                $chkZb = $dblj->prepare("SELECT uid FROM playerzhuangbei WHERE zbnowid = :zbnowid");
                $chkZb->execute(array(':zbnowid' => $sdZbnowid));
                $itemUid = $chkZb->fetchColumn();
                if ($itemUid === false) {
                    throw new PDOException('Trang bị này không còn tồn tại');
                }
                if (intval($itemUid) !== 0 && intval($itemUid) !== $sdSellerUid) {
                    throw new PDOException('Trang bị này không còn thuộc về người bán');
                }

                $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
                $dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $dblj->beginTransaction();

                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - :pay WHERE uyxb >= :pay_min AND sid = :sid");
                $stmt->execute(array(':pay' => $pay, ':pay_min' => $pay, ':sid' => $sid));
                if ($stmt->rowCount() == 0 && $pay > 0) {
                    throw new PDOException('Linh thạch không đủ');
                }

                $stmt = $dblj->prepare("DELETE FROM fangshi_sd WHERE payid = :payid");
                $stmt->execute(array(':payid' => intval($payid)));
                if ($stmt->rowCount() == 0) {
                    throw new PDOException('Trang bị xuất hàng thất bại');
                }

                $priceAfterTax = floor($pay * 0.9);
                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + :pay WHERE uid = :uid");
                $stmt->execute(array(':pay' => $priceAfterTax, ':uid' => intval($sd->uid)));
                if ($stmt->rowCount() == 0 && $priceAfterTax > 0) {
                    throw new PDOException('Tu sĩ bán trang bị này chưa nhận được linh thạch');
                }

                $stmt = $dblj->prepare("UPDATE playerzhuangbei SET sid = :sid, uid = :uid WHERE zbnowid = :zbnowid");
                $stmt->execute(array(':sid' => $sid, ':uid' => intval($player->uid), ':zbnowid' => intval($sd->zbnowid)));
                $delivered = $dblj->prepare("SELECT COUNT(*) FROM playerzhuangbei WHERE zbnowid = :zbnowid AND uid = :uid AND sid = :sid");
                $delivered->execute(array(':zbnowid' => intval($sd->zbnowid), ':uid' => intval($player->uid), ':sid' => $sid));
                if (intval($delivered->fetchColumn()) === 0) {
                    throw new PDOException('Trang bị truyền tống thất bại');
                }

                $dblj->commit();
                $noticeHtml .= tcg_market_notice('success', 'Giao dịch thành công');
            } catch (PDOException $e) {
                if ($dblj->inTransaction()) {
                    $dblj->rollBack();
                }
                $noticeHtml .= tcg_market_notice('error', $e->getMessage());
            }
            $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
        }

        $fsdjall = \player\getfangshi_all($fangshi, $dblj);
        $index = 0;
        foreach ($fsdjall as $fsdj) {
            $index++;
            $zbnowid = $fsdj['zbnowid'];
            $zbname = $fsdj['zbname'];
            $zbqh = $fsdj['qianghua'];
            $zbpay = $fsdj['pay'];
            $payid = $fsdj['payid'];
            $goumaisd = $encode->encode("cmd=fangshi&fangshi=shangdian&canshu=buy&payid=$payid&sid=$sid");
            $zbpaycmd = $encode->encode("cmd=zbinfo&zbnowid=$zbnowid&sid=$sid");
            $sdTip = \player\itemtip_attr_for_listing($fsdj, $player, $dblj);
            $payhtml .= tcg_market_item_zb($index, $zbname, '#f50808', $zbqh, $zbpay, $zbpaycmd, $goumaisd, true, \player\zhuangbei_image_path((object) array('zbid' => intval($fsdj['zbid'])), ''), '', $sdTip);
        }
        break;

    default:
        $noticeHtml .= tcg_market_notice('error', 'Danh mục giao dịch không tồn tại');
        break;
}

if ($payhtml == '') {
    $payhtml = '<div class="tcg-market-empty">Hiện chưa có vật phẩm nào đang được bày bán.</div>';
}

$daojuActive = ($fangshi == 'daoju') ? ' active' : '';
$zbActive    = ($fangshi == 'zhuangbei') ? ' active' : '';
$shopActive  = ($fangshi == 'shangdian') ? ' active' : '';

$fangshiNameMap = array(
    'daoju'     => 'Đạo Cụ',
    'zhuangbei' => 'Trang Bị',
    'shangdian' => 'Cửa Hàng'
);
$currentFangshiName = isset($fangshiNameMap[$fangshi]) ? $fangshiNameMap[$fangshi] : 'Phường Thị';

$walletLinh = isset($player->uyxb) ? $player->uyxb : 0;

$html = <<<HTML
<div class="tcg-market-page">
    <div class="tcg-market-hero">
        <div class="tcg-market-hero-bg"><img src="/images/market/ptgd.png" alt=""></div>
        <div class="tcg-market-hero-mask"></div>
        <div class="tcg-market-emblem">⚖️</div>
        <div class="tcg-market-head">
            <h1>Phường Thị Giao Dịch</h1>
            <div class="tcg-market-desc">Mua bán đạo cụ, trang bị và vật phẩm do tu sĩ khác bày bán.</div>
        </div>
    </div>

    <div class="tcg-market-tabs">
        <a class="tcg-market-tab$daojuActive" href="?cmd=$daojuCmd">📦 Đạo cụ</a>
        <a class="tcg-market-tab$zbActive" href="?cmd=$zhuangbeiCmd">⚔ Trang bị</a>
        <a class="tcg-market-tab$shopActive" href="?cmd=$shangdianCmd">🔥 Cửa hàng</a>
    </div>

    <div class="tcg-market-wallet">
        <div class="tcg-market-money gold">
            <span>Linh thạch hiện có</span>
            <strong>$walletLinh</strong>
        </div>
        <div class="tcg-market-money">
            <span>Danh mục</span>
            <strong>$currentFangshiName</strong>
        </div>
    </div>

    $noticeHtml

    <div class="tcg-market-list">
        $payhtml
    </div>

    <div class="tcg-market-bottom">
        <a class="tcg-market-back" href="#" onclick="history.back();return false;">← Trở Về</a>
        <a class="tcg-market-home" href="game.php?cmd=$gonowmid">Đại Sảnh →</a>
    </div>
</div>
HTML;

echo $html;
?>
