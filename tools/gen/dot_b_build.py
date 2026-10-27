# -*- coding: utf-8 -*-
"""
Ghi dữ liệu đợt B (chạy lại nhiều lần cho cùng kết quả):
  1) class/config/guaiwu_config.json — thêm 84 quái (mã 200–283) + sửa danh sách rơi trang bị của quái cũ theo bậc.
  2) sql/dot_b_data.json — dữ liệu bản đồ / khu vực / boss cho sql/migrate_dot_b.php.
Chạy: python3 tools/gen/dot_b_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from dot_b_design import REGIONS, monsters, bosses, base_stats

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
GW = os.path.join(ROOT, 'class', 'config', 'guaiwu_config.json')

ELEM_TXT = {'kim': 'kim khí sắc lạnh', 'moc': 'mộc khí âm u', 'thuy': 'thủy khí lạnh buốt', 'hoa': 'hỏa khí hừng hực',
            'tho': 'thổ khí nặng nề', 'none': 'yêu khí hỗn tạp'}
ARCH_TXT = {'thuong': 'Thường đi theo bầy, gặp người tu luyện là lao vào tấn công.',
            'giap': 'Lớp vỏ cứng như giáp, đánh lâu mới phá được phòng ngự.',
            'cuong': 'Tính tình cuồng bạo, ra đòn rất mạnh nhưng phòng thủ sơ hở.',
            'tinh': 'Kẻ mạnh nhất vùng, sức lực gấp đôi đồng loại — cẩn thận khi khiêu chiến.'}

# Trang bị theo bậc (zblv) — dùng cho cả quái cũ: chỉ rơi đồ bậc ≤ cấp quái
TIERS = [(400, [23, 48, 68, 88, 103]), (300, [22, 47, 67, 87, 102]), (200, [21, 46, 66, 86, 101]),
         (120, [5, 10, 15, 20, 30]), (60, [4, 9, 14, 19, 29]), (30, [3, 8, 13, 18, 28]),
         (10, [2, 7, 12, 17, 27]), (1, [1, 6, 11, 16, 26])]
def zb_for(lv):
    for t, ids in TIERS:
        if lv >= t: return ','.join(str(i) for i in ids)
    return '1,6,11,16,26'

def build_guaiwu():
    data = json.load(open(GW, encoding='utf-8'))
    ms = monsters()
    new_ids = set(str(m['id']) for m in ms)
    fixed = 0
    for k, g in data.items():
        if k in new_ids: continue
        if g.get('gzb', '') != '':
            want = zb_for(int(g['glv']))
            if g['gzb'] != want:
                g['gzb'] = want
                fixed += 1
    for m in ms:
        data[str(m['id'])] = {
            'gname': m['name'], 'element': m['element'], 'glv': m['lv'], 'id': m['id'],
            'ginfo': '%s ẩn mình ở %s, toàn thân tỏa %s. %s' % (m['name'], m['mname'], ELEM_TXT[m['element']], ARCH_TXT[m['arch']]),
            'gsex': '', 'ghp': m['ghp'], 'ggj': m['ggj'], 'gfy': m['gfy'], 'gbj': m['gbj'], 'gxx': m['gxx'],
            'gzb': m['gzb'], 'dljv': m['dljv'], 'gdj': m['gdj'], 'djjv': m['djjv'], 'gyp': m['gyp'], 'ypjv': m['ypjv'],
            'ms': 0, 'gexp': None,
        }
    with open(GW, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        f.write('\n')
    return len(ms), fixed

def build_maps():
    """Bản đồ: map có sẵn (mid) chỉ ghi mgid; map mới tạo theo hình rắn (4 map/hàng)."""
    ms = monsters()
    by_map = {}
    for m in ms:
        key = (m['region'], m['mname'])
        by_map.setdefault(key, []).append('%d|%d' % (m['id'], m['count']))
    regions = []
    QY_NEW = 32
    for qy, qname, new, band, maps, boss in REGIONS:
        if qy is None:
            qy = QY_NEW
            QY_NEW += 1
        rmaps = []
        for i, (mid, mname, mobs) in enumerate(maps):
            rmaps.append({'mid': mid, 'mname': mname, 'mgid': ','.join(by_map[(qname, mname)]),
                          'midinfo': '%s thuộc %s — vùng luyện cấp %s.' % (mname, qname, band)})
        regions.append({'qyid': qy, 'qyname': qname, 'new': new, 'band': band, 'maps': rmaps})
    bs = []
    for b in bosses():
        bs.append({'bossname': b['name'], 'bosslv': b['lv'], 'bosshp': b['hp'], 'bossgj': b['gj'], 'bossfy': b['fy'],
                   'bossbj': b['bj'], 'bossxx': b['xx'], 'bosszb': b['zb'], 'bossdj': b['dj'], 'region': b['region'],
                   'bossinfo': '%s — bá chủ %s, cứ mỗi lần bị hạ lại trỗi dậy mạnh hơn.' % (b['name'].replace('Boss·', ''), b['region']),
                   'at_mid': b['mid']})
    out = {'_note': 'Sinh bởi tools/gen/dot_b_build.py — dùng cho sql/migrate_dot_b.php', 'regions': regions, 'bosses': bs}
    with open(os.path.join(ROOT, 'sql', 'dot_b_data.json'), 'w', encoding='utf-8') as f:
        json.dump(out, f, ensure_ascii=False, indent=1)
        f.write('\n')
    return len(regions), sum(len(r['maps']) for r in regions if r['new'])

if __name__ == '__main__':
    n, fixed = build_guaiwu()
    r, nm = build_maps()
    print('quai moi:', n, '| quai cu sua danh sach roi:', fixed, '| khu:', r, '| map moi:', nm)
