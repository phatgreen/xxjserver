# -*- coding: utf-8 -*-
"""
Ghi dữ liệu đợt A vào config (chạy lại nhiều lần vẫn cho cùng kết quả — xóa id cũ rồi ghi lại).
Chạy: python3 tools/gen/dot_a_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from dot_a_design import wugong_new, CP_NEW, GIAI
from dot_a_content import WUGONG, CONGPHAP

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
def load(n): return json.load(open(os.path.join(CFG, n), encoding='utf-8'))
def save(n, d):
    with open(os.path.join(CFG, n), 'w', encoding='utf-8') as f:
        json.dump(d, f, ensure_ascii=False, indent=2)
        f.write('\n')

ELEM_COLOR = {'kim': '#C0C0C0', 'moc': '#228B22', 'thuy': '#1E90FF', 'hoa': '#FF4500', 'tho': '#8B4513', 'all': '#FFD700', 'none': '#8B0000'}
GIAI_TMULT = {1: 1.0, 2: 1.5, 3: 2.5, 4: 4.0, 5: 7.0, 6: 12.0, 7: 20.0}

# ---- Võ công ----
wg = load('wugong_config.json')
new = wugong_new()
ids = set(w['wgid'] for w in new)
wg = {k: v for k, v in wg.items() if int(v.get('wgid', -1)) not in ids}
for w in new:
    dd, hq, pc, mt, _ = WUGONG[w['wgid']]
    wg[str(w['wgid'])] = {
        'id': w['wgid'], 'wgname': w['name'], 'wgys': w['color'],
        'wginfo': '|'.join([w['kind'], dd, hq, pc, mt]),
        'wglx': 1, 'wgsum': 1, 'wgid': w['wgid'],
        'add_gj': w['gj'], 'add_fy': w['fy'], 'add_hp': w['hp'], 'add_bj': w['bj'], 'add_xx': w['xx'],
        'effect_type': w['effect'], 'effect_value': w['value'],
        'element': w['element'], 'category': w['category'], 'rarity': w['rarity'], 'purpose': w['purpose'],
        'realm_require': w['realm'], 'training_time_mult': w['tmult'],
        'price_linhthong': w['lt'], 'price_masthach': w['mt'],
    }
save('wugong_config.json', wg)

# ---- Thế võ ----
sk = load('wugong_skill_config.json')
for w in new:
    moves = WUGONG[w['wgid']][4]
    sk['moves'][str(w['wgid'])] = [dict(tier=i + 1, name=n, effect=e, value=v, hits=h, text=t)
                                   for i, (n, e, v, h, t) in enumerate(moves)]
sk['rules']['rarity_bonus'].update({'ha_thua': 0.02, 'tien_dao': 0.2})
save('wugong_skill_config.json', sk)

# ---- Công pháp ----
cp = load('congphap_config.json')
for i, c in enumerate(CP_NEW):
    g, p, n, el, et, b, m, r, q, lt, mt, srt = c
    cpid = 29 + i
    cp[str(cpid)] = {
        'cpid': cpid, 'cpname': n, 'cpys': ELEM_COLOR[el], 'cpsort': srt, 'cpinfo': CONGPHAP[cpid],
        'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
        'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
        'realm_require': r, 'tier_require': 0, 'quality': q, 'tier': g, 'subtier': p,
        'training_time_mult': GIAI_TMULT[g], 'price_linhthong': lt, 'price_masthach': mt,
    }
save('congphap_config.json', cp)
print('vo cong:', len(wg), '| the vo:', len(sk['moves']), '| cong phap:', len(cp))
