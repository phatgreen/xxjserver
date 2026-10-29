# -*- coding: utf-8 -*-
"""
Nhân vật test theo mốc thời gian: ngày 1 / tuần 1 / tháng 1, chơi hết tính năng online, 1h → 24h/ngày.
Dùng chung mô hình tools/sim/econ_sim.py (công thức lấy từ code). Chạy: python3 tools/sim/moc_online_sim.py
In bảng Markdown (trung bình 30 lần chạy / kịch bản).
"""
import json, os
from econ_sim import sim

HOURS = (1, 2, 4, 6, 8, 12, 16, 20, 24)
MARKS = (1, 7, 30)
RUNS = 30
ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', '..')
UNLOCK = json.load(open(os.path.join(ROOT, 'class', 'config', 'feature_unlock.json'), encoding='utf-8'))
UNLOCK = UNLOCK.get('features', UNLOCK)
UNLOCK = sorted((v['level'], v['name']) for v in UNLOCK.values() if isinstance(v, dict) and 'level' in v)

def fmt(v):
    v = float(v)
    if abs(v) >= 1e6: return '%.2fM' % (v / 1e6)
    if abs(v) >= 1e4: return '%.1fK' % (v / 1e3)
    return '%d' % round(v)

# 3 kịch bản: bản trước (chưa đủ tính năng) / đủ tính năng nhưng không bán Đột Phá Đan / đủ tính năng theo luật hiện tại (bán đan)
SCEN = (('cu', dict()), ('du_khong_ban_dan', dict(full=True, sell_pill=False)), ('du_luat_hien_tai', dict(full=True, sell_pill=True)))

def run(**kw):
    res = {}
    for h in HOURS:
        rs = [sim(30, h, seed=x, hunt=True, marks=MARKS, **kw) for x in range(RUNS)]
        res[h] = {}
        for d in MARKS:
            xs = [r[d] for r in rs]
            avg = dict((k, sum(x[k] for x in xs) / len(xs)) for k in xs[0])
            avg['Lmin'] = min(x['L'] for x in xs); avg['Lmax'] = max(x['L'] for x in xs)
            res[h][d] = avg
    return res

def table(title, cols, res, d):
    print('\n### %s — mốc %s\n' % (title, {1: 'ngày 1', 7: 'tuần 1 (ngày 7)', 30: 'tháng 1 (ngày 30)'}[d]))
    print('| Giờ/ngày | ' + ' | '.join(c[0] for c in cols) + ' |')
    print('|---|' + '---|' * len(cols))
    for h in HOURS:
        a = res[h][d]
        print('| %dh | ' % h + ' | '.join(c[1](a) for c in cols) + ' |')

if __name__ == '__main__':
    import sys
    allres = dict((k, run(**kw)) for k, kw in SCEN)
    res = allres['du_luat_hien_tai']
    if '--json' in sys.argv:
        # cho tools/sim/moc_crosscheck.php dựng nhân vật test đúng trạng thái từng mốc
        print(json.dumps(dict((h, dict((d, dict(L=round(res[h][d]['L']), lt=int(res[h][d]['lt']), mt=int(res[h][d]['mt'])))
                                       for d in MARKS)) for h in HOURS)))
        sys.exit(0)
    main =[('Cấp TB (min–max)', lambda a: '%d (%d–%d)' % (round(a['L']), a['Lmin'], a['Lmax'])),
            ('Tỉ lệ ĐP', lambda a: '%d%%' % round(a['rate'])),
            ('Thử ĐP', lambda a: fmt(a['tupo_try'])), ('Tụt cấp', lambda a: '%.1f' % a['lv_lost']),
            ('Quái', lambda a: fmt(a['kills'])), ('Boss', lambda a: fmt(a['boss'])), ('Tháp', lambda a: '%d' % round(a['tower'])),
            ('LT còn', lambda a: fmt(a['lt'])), ('MT còn', lambda a: fmt(a['mt']))]
    money = [('LT thu', lambda a: fmt(a['lt_in'])), ('boss', lambda a: fmt(a['lt_boss'])), ('quái', lambda a: fmt(a['lt_mob'])),
             ('nhiệm vụ', lambda a: fmt(a['lt_quest'])), ('tháp', lambda a: fmt(a['lt_tower'])),
             ('bán đan', lambda a: fmt(a['lt_pill'])), ('boss Linh Căn', lambda a: fmt(a['lt_lcb'])), ('đồ dư', lambda a: fmt(a['lt_sell'])),
             ('Hầm Mỏ', lambda a: fmt(a['lt_mine'])), ('Vấn Đỉnh Đài', lambda a: fmt(a['lt_vdd'])), ('Tông Môn', lambda a: fmt(a['lt_club'])),
             ('LT chi ĐP', lambda a: fmt(a['lt_tupo'])), ('chi tu luyện x2', lambda a: fmt(a['lt_cult'])), ('chi tính năng', lambda a: fmt(a['lt_feat'])),
             ('MT thu (boss / NV / VĐĐ / tông môn)', lambda a: '%s / %s / %s / %s' % (fmt(a['mt_boss']), fmt(a['mt_quest']), fmt(a['mt_vdd']), fmt(a['mt_club']))), ('MT chi linh sủng', lambda a: fmt(a['mt_pet']))]
    def exp_share(a):
        t = a['e_cult'] + a['e_mob'] + a['e_quest'] + a['e_tower']
        return ' / '.join('%d%%' % round(100 * a[k] / t) if t else '0%' for k in ('e_cult', 'e_mob', 'e_quest', 'e_tower'))
    feat = [('Tu vi: tu luyện / quái / NV / tháp', exp_share),
            ('Trang bị (ĐP/TL %)', lambda a: '+%d / +%d' % (round(a['gb']), round(a['gc']))),
            ('Ô bậc hiện', lambda a: '%.1f/15' % a['cur_n']),
            ('Tông Môn cấp', lambda a: '%.1f' % a['clv']), ('Boss LC hạ', lambda a: fmt(a['lcb'])), ('Đan rơi', lambda a: fmt(a['pills'])),
            ('Lò đan', lambda a: '%d' % round(a['ld'])),
            ('Linh căn (cấp/bậc)', lambda a: '%d / %d' % (round(a['lc']), round(a['lc_stage']))),
            ('Thiên phú (phẩm/cấp)', lambda a: '%d / %d' % (round(a['tf_q']), round(a['tf_lv']))),
            ('Huyết mạch', lambda a: '%.1f' % a['hm']), ('Thể chất', lambda a: '%.1f' % a['tc']),
            ('Cường hóa', lambda a: '+%d' % round(a['ch'])), ('Võ công', lambda a: '%d' % round(a['wg'])),
            ('Công pháp', lambda a: '%d' % round(a['cp'])),
            ('Linh sủng (phẩm/cấp)', lambda a: '%s / %d' % ('-' if a['pet_q'] < 0 else '%.1f' % a['pet_q'], round(a['pet_lv'])))]
    print('\n### So sánh 3 kịch bản — cấp TB (min–max)\n')
    print('| Giờ/ngày | ' + ' | '.join('%s %s' % (n, m) for m in ('ngày 1', 'tuần 1', 'tháng 1') for n in ('Cũ', 'Đủ (không bán đan)', 'Đủ (luật hiện tại)')) + ' |')
    print('|---|' + '---|' * 9)
    for h in HOURS:
        cells = []
        for d in MARKS:
            for k, _ in SCEN:
                a = allres[k][h][d]; cells.append('%d (%d–%d)' % (round(a['L']), a['Lmin'], a['Lmax']))
        print('| %dh | ' % h + ' | '.join(cells) + ' |')
    print('\n### So sánh 3 kịch bản — tháng 1: LT thu / MT còn / tỉ lệ ĐP\n')
    print('| Giờ/ngày | Cũ | Đủ (không bán đan) | Đủ (luật hiện tại) |')
    print('|---|---|---|---|')
    for h in HOURS:
        print('| %dh | ' % h + ' | '.join('%s / %s / %d%%' % (fmt(allres[k][h][30]['lt_in']), fmt(allres[k][h][30]['mt']), round(allres[k][h][30]['rate'])) for k, _ in SCEN) + ' |')
    for d in MARKS:
        table('Tiến trình', main, res, d)
        table('Linh Thạch / Ma Thạch (cộng dồn)', money, res, d)
        table('Tính năng', feat, res, d)
    print('\n### Mở khóa tính năng (cấp TB đạt được)\n')
    print('| Giờ/ngày | ngày 1 | tuần 1 | tháng 1 |')
    print('|---|---|---|---|')
    for h in HOURS:
        cells = []
        for d in MARKS:
            L = res[h][d]['L']
            got = [n for lv, n in UNLOCK if lv <= L]
            nxt = [('%s (%d)' % (n, lv)) for lv, n in UNLOCK if lv > L][:1]
            cells.append('%d/%d mở%s' % (len(got), len(UNLOCK), (', kế: ' + nxt[0]) if nxt else ''))
        print('| %dh | ' % h + ' | '.join(cells) + ' |')
