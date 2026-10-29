<?php
$gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$ydaoju = \player\getdaoju($djid,$dblj);
$daoju = \player\getdaoju($djid,$dblj);
$chushou = $encode->encode("cmd=djinfo&canshu=chushou&djid=$djid&sid=$sid");
$daoju = \player\getplayerdaoju($sid,$djid,$dblj);
$player = \player\getplayer($sid,$dblj);
$djhtml = '';
$notice = '';
$noticeType = 'ok';
function djinfo_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
$djCfgTrade = \player\daoju_config_get($djid);
$noTrade = ($djCfgTrade && !empty($djCfgTrade['no_trade']));   // vật phẩm khóa (quà tân thủ): không treo Phường Thị
if ($daoju && !$noTrade){
    $self = $_SERVER['PHP_SELF'];
    $maxCount = intval($daoju->djsum);
    $djhtml =<<<HTML
    <form class="djinfo-auction-form" action="$self">
    <input type="hidden" name="cmd" value="djinfo">
    <input type="hidden" name="canshu" value="chushou">
    <input type="hidden" name="sid" value='$sid'>
    <input type="hidden" name="djid" value="$djid">
        <label>
            <span>Đấu giá số lượng</span>
            <input type="number" name="djcount" min="1" max="$maxCount" placeholder="Tối đa $maxCount" required>
        </label>
        <label>
            <span>Đấu giá đơn giá</span>
            <input type="number" name="pay" min="1" placeholder="Linh thạch / món" required>
        </label>
        <button type="submit">Đấu Giá</button>
    </form>
HTML;

}
if(isset($canshu))
    if ($canshu == "chushou"){
        $djid     = intval($djid);
        $djcount  = max(1, intval($djcount));
        $pay      = max(1, intval($pay));
        if ($noTrade) {
            $notice = 'Vật phẩm này bị khóa, không thể giao dịch';
            $noticeType = 'bad';
        } elseif (!$daoju || !$ydaoju) {
            $notice = 'Trên người không đủ đạo cụ';
            $noticeType = 'bad';
        } else try{
            $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            $dblj->setAttribute(PDO::ATTR_ERRMODE,  PDO::ERRMODE_EXCEPTION);
            $dblj->beginTransaction();
            $stmt = $dblj->prepare("update `playerdaoju` set djsum = djsum - :cnt WHERE djid = :djid AND djsum >= :cnt_min AND uid = :uid AND sid = :sid");
            $stmt->execute(array(':cnt' => $djcount, ':cnt_min' => $djcount, ':djid' => $djid, ':uid' => intval($player->uid), ':sid' => $sid));
            if (!$stmt->rowCount()){
                throw new PDOException("Trên người không đủ đạo cụ<br/>");//那个错误抛出异常
            }
            $stmt = $dblj->prepare("insert into `fangshi_dj`(djid,djcount,uid,pay,djname,djinfo) VALUES (:djid2,:djcount,:uid2,:pay,:djname,:djinfo)");
            $stmt->execute(array(
                ':djid2' => $djid, ':djcount' => $djcount, ':uid2' => intval($player->uid),
                ':pay' => $pay, ':djname' => $ydaoju->djname, ':djinfo' => $ydaoju->djinfo
            ));
            if (!$stmt->rowCount()){
                throw new PDOException("Bán thất bại<br/>");//那个错误抛出异常
            }
            $notice = "Bán thành công!";
            $noticeType = 'ok';
            $dblj->commit();//交易成功就提交
        }catch (PDOException $e){
            $notice = strip_tags($e->getMessage());
            $noticeType = 'bad';
            if ($dblj->inTransaction()) {
                $dblj->rollBack();
            }
        }
        $dblj->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);//关闭
        $daoju = \player\getplayerdaoju($sid,$djid,$dblj);
        \player\changerwyq1(1,$djid,1,$sid,$dblj);
    }
    $gonowmid = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$player->sid");
$name = $ydaoju ? djinfo_h($ydaoju->djname) : 'Đạo cụ không tồn tại';
$info = $ydaoju ? djinfo_h($ydaoju->djinfo) : 'Mã đạo cụ này không có trong cấu hình.';
$price = $ydaoju ? number_format(intval($ydaoju->djyxb), 0, ',', '.') : '0';
$sellUnit = $ydaoju ? \player\daoju_sell_price($djid, $ydaoju->djyxb) : 0;
$sellText = $sellUnit > 0 ? number_format($sellUnit, 0, ',', '.') . ' Linh thạch' : 'Không bán NPC';
$owned = $daoju ? intval($daoju->djsum) : 0;
$noticeHtml = $notice != '' ? '<div class="djinfo-notice '.$noticeType.'">'.djinfo_h($notice).'</div>' : '';

// Kiểm tra có use_effect không
$hasUseEffect = isset($ydaoju->use_effect) && !empty($ydaoju->use_effect);
$useBtnHtml = '';
if ($hasUseEffect && $daoju && intval($daoju->djsum) > 0) {
    $useCmd = $encode->encode("cmd=use_dj&djid=$djid&sid=$sid");
    $effect = $ydaoju->use_effect;
    $statNames = array('ugj' => 'Công Kích', 'ufy' => 'Phòng Thủ', 'umaxhp' => 'Sinh Mệnh', 'ubj' => 'Bạo Kích', 'uxx' => 'Hút Máu');
    $statKey = isset($effect['stat']) ? $effect['stat'] : '';
    $statLabel = isset($statNames[$statKey]) ? $statNames[$statKey] : $statKey;
    $amount = isset($effect['amount']) ? intval($effect['amount']) : 0;
    $useBtnHtml = '<a class="djinfo-btn use-btn" href="?cmd='.$useCmd.'" onclick="return confirm(\'Dùng vật phẩm? '.$statLabel.' +'.$amount.'\')">Dùng</a>';
}

$formSection = $daoju
    ? '<section class="djinfo-section"><div class="djinfo-section-title">Đưa lên phường thị</div>'.$djhtml.'</section>'
    : '<section class="djinfo-section"><div class="djinfo-empty">Bạn không còn đạo cụ này trong túi.</div></section>';

?>

<div class="djinfo-page">
    <section class="djinfo-hero">
        <div class="djinfo-icon">📦</div>
        <div class="djinfo-head">
            <div class="djinfo-eyebrow">Thông tin đạo cụ</div>
            <h1><?php echo $name; ?></h1>
            <p><?php echo $info; ?></p>
        </div>
    </section>

    <?php echo $noticeHtml; ?>

    <section class="djinfo-stats">
        <div class="djinfo-stat"><span>Số lượng đang có</span><b><?php echo number_format($owned, 0, ',', '.'); ?></b></div>
        <div class="djinfo-stat"><span>Giá tham khảo</span><b><?php echo $price; ?> Linh thạch</b></div>
        <div class="djinfo-stat"><span>Bán cho NPC</span><b><?php echo $sellText; ?></b></div>
    </section>

    <?php echo $formSection; ?>

    <div class="djinfo-bottom">
        <?php echo $useBtnHtml; ?>
        <a class="djinfo-btn" href="#" onClick="javascript:history.back(-1);return false;">Trở Về</a>
        <a class="djinfo-btn primary" href="game.php?cmd=<?php echo $gonowmid; ?>">Đại Sảnh</a>
    </div>
</div>

