# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 2 (chạy lại nhiều lần cho cùng kết quả):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 300–469 (ghi đè đúng các mã này, không đụng quái cũ).
  2) sql/lan2_data.json — khu / map / boss cho sql/migrate_lan2.php.
Chạy: python3 tools/gen/lan2_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan2_design import REGIONS, QY_START, monsters, bosses
from dot_b_build import ELEM_TXT, ARCH_TXT

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
GW = os.path.join(ROOT, 'class', 'config', 'guaiwu_config.json')

# Id map cố định (không tự tăng) để NPC / nhiệm vụ (renwu.rwqy — nút Truyền tống) / ảnh khu khớp trên mọi máy chủ.
# Quy ước: làn N dùng mid 1000×(N−1)+1 trở đi ⇒ làn 2 = 1001–1080.
MID_BASE = 1001

def band(k):
    return '%d–%d' % (501 + 50 * k, 550 + 50 * k)

def build_guaiwu():
    data = json.load(open(GW, encoding='utf-8'))
    ms = monsters()
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
    return len(ms)

def build_data():
    ms = monsters()
    regions = []
    for k, (rname, els, maps, boss, tinh) in enumerate(REGIONS):
        rmaps = []
        for i, mname in enumerate(maps):
            mgid = ','.join('%d|%d' % (m['id'], m['count']) for m in ms if m['qy'] == QY_START + k and m['mi'] == i)
            rmaps.append({'mid': MID_BASE + 8 * k + i, 'mname': mname, 'mgid': mgid,
                          'midinfo': '%s thuộc %s — vùng luyện cấp %s.' % (mname, rname, band(k))})
        regions.append({'qyid': QY_START + k, 'qyname': rname, 'band': band(k), 'maps': rmaps})
    bs = [{'bossname': b['name'], 'bosslv': b['lv'], 'bosshp': b['hp'], 'bossgj': b['gj'], 'bossfy': b['fy'],
           'bossbj': b['bj'], 'bossxx': b['xx'], 'bosszb': b['zb'], 'bossdj': b['dj'], 'qyid': b['qy'],
           'bossinfo': '%s — bá chủ %s, cứ mỗi lần bị hạ lại trỗi dậy mạnh hơn.' % (b['name'].replace('Boss·', ''), b['region'])}
          for b in bosses()]
    out = {'_note': 'Sinh bởi tools/gen/lan2_build.py — dùng cho sql/migrate_lan2.php', 'entry_from_mid': 408,
           'regions': regions, 'bosses': bs}
    with open(os.path.join(ROOT, 'sql', 'lan2_data.json'), 'w', encoding='utf-8') as f:
        json.dump(out, f, ensure_ascii=False, indent=1)
        f.write('\n')
    return len(regions), sum(len(r['maps']) for r in regions), len(bs)

if __name__ == '__main__':
    n = build_guaiwu()
    r, nm, nb = build_data()
    print('quai lan 2:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
