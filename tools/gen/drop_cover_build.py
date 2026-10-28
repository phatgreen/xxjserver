# -*- coding: utf-8 -*-
"""
Nguồn rơi đủ 15 ô trang bị cho bậc >= 60 (chủ dự án 2026-10-28: Thương Điếm chỉ bán bậc <= 30, đồ bậc cao do người săn đem lên Phường Thị).
  - 9 ô thường (không cộng đột phá / tu luyện chính): QUÁI rơi. Quái cùng bậc chia 3 nhóm theo thứ tự (cấp, mã), mỗi nhóm 3 ô.
  - 6 ô quý (8/13/14 tu luyện · 9/10/11 đột phá): chỉ BOSS rơi.
Ghi: class/config/guaiwu_config.json (gzb), sql/dot_b_data.json + sql/lan*_data.json (bosszb — để migrate_lan* --apply không ghi đè).
Boss đang có trong DB: chạy sql/migrate_drop_cover.php (cùng quy tắc, đọc zhuangbei_config.json).
Chạy SAU mọi bộ sinh khác (dot_b_build, dot_f_build, lan*_build): python3 tools/gen/drop_cover_build.py
"""
import json, os, glob

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
MIN_TIER = 60
SPECIAL_SLOTS = (8, 9, 10, 11, 13, 14)
NORMAL_GROUPS = ((1, 2, 3), (4, 5, 6), (7, 12, 15))

ZB = json.load(open(os.path.join(CFG, 'zhuangbei_config.json'), encoding='utf-8'))
TIERS = sorted({int(v['zblv']) for v in ZB.values()})

def tier_of(lv):
    return max(t for t in TIERS if t <= lv)

def ids(T, slots):
    return ','.join(str(v['zbid']) for v in sorted(ZB.values(), key=lambda v: int(v['zbid']))
                    if int(v['zblv']) == T and int(v['zbtool']) in slots)

def build_monsters():
    p = os.path.join(CFG, 'guaiwu_config.json')
    g = json.load(open(p, encoding='utf-8'))
    by_tier = {}
    for k, m in g.items():
        lv = int(m['glv'])
        if lv >= MIN_TIER and m.get('gzb'):
            by_tier.setdefault(tier_of(lv), []).append(k)
    n = 0
    for T, keys in by_tier.items():
        for i, k in enumerate(sorted(keys, key=lambda k: (int(g[k]['glv']), int(k)))):
            want = ids(T, NORMAL_GROUPS[i % 3])
            if g[k]['gzb'] != want:
                g[k]['gzb'] = want
                n += 1
    with open(p, 'w', encoding='utf-8') as f:
        json.dump(g, f, ensure_ascii=False, indent=2)
        f.write('\n')
    return n

def build_boss_data():
    n = 0
    for p in [os.path.join(ROOT, 'sql', 'dot_b_data.json')] + sorted(glob.glob(os.path.join(ROOT, 'sql', 'lan[0-9]_data.json'))):
        d = json.load(open(p, encoding='utf-8'))
        changed = False
        for b in d.get('bosses', []):
            if int(b['bosslv']) >= MIN_TIER:
                want = ids(tier_of(int(b['bosslv'])), SPECIAL_SLOTS)
                if b.get('bosszb') != want:
                    b['bosszb'] = want
                    changed = True
                    n += 1
        if changed:
            raw = open(p, encoding='utf-8').read()
            indent = 1 if '\n "' in raw else 2
            with open(p, 'w', encoding='utf-8') as f:
                json.dump(d, f, ensure_ascii=False, indent=indent)
                f.write('\n')
    return n

if __name__ == '__main__':
    print('quai doi gzb:', build_monsters())
    print('boss (file du lieu) doi bosszb:', build_boss_data())
