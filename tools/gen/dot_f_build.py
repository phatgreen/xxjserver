# -*- coding: utf-8 -*-
"""
Đợt F — trang bị (chạy lại nhiều lần cho cùng kết quả, luôn tính từ bản gốc tools/gen/data/zhuangbei_dot_f_src.json):
  1) class/config/zhuangbei_config.json
     - Chỉ số mỗi bậc: tổng đủ bộ 15 ô ≈ 50% chỉ số gốc nhân vật ở cấp đó (đường lên cấp + đột phá cố định của đợt F),
       không giảm so với bản gốc. Bậc 1/10/30 giữ nguyên chỉ số cũ (chỉ bổ sung ô thiếu).
     - Đủ 15 ô mọi bậc (bổ sung Thiên Thuẫn / Thiên Quan / Hộ Thủ / Linh Đai cho bậc 10, 60, 120, 300, 600).
     - Bậc 500 mới (Hồng Mông), Thần Trang chung bộ 9 với bậc 600.
     - Phẩm chất = bậc (Phàm → Đạo).
  2) class/config/chetao_config.json — cấp yêu cầu công thức = bậc đồ làm ra.
  3) class/config/guaiwu_config.json — quái cấp ≥ 450 rơi thêm đồ bậc 500; quái đợt B (mã 200–283) cân lại theo
     đường sức mạnh mới: máu ×1,4, công đủ để quái Thường cần ~45 đòn hạ nhân vật chuẩn (đủ bộ, cường hóa +10).
  4) sql/dot_f_data.json — chỉ số 9 boss khu vực mới (sql/migrate_dot_f.php ghi vào bảng boss).
Chạy: python3 tools/gen/dot_f_build.py
"""
import json, os, collections

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
SRC = os.path.join(ROOT, 'tools', 'gen', 'data', 'zhuangbei_dot_f_src.json')

TIERS = [1, 10, 30, 60, 120, 200, 300, 400, 500, 600, 800, 1000, 1250, 1500, 2000, 2500, 3000]   # 1000/1250: làn 3 · 1500: làn 4 · 2000: làn 5 · 2500: làn 6 · 3000: làn 7 (docs/LAN*_CAP_*.md)
KEEP_TIERS = (1, 10, 30)          # giữ chỉ số cũ
PROFILE_TIERS = (30, 200, 400, 800)  # bậc đủ 15 ô, dùng làm tỉ lệ giữa các ô
GEAR_SHARE = 0.5                  # tổng đồ đủ bộ = 50% chỉ số gốc ở cấp = bậc
RARITY = {1: 1, 10: 2, 30: 3, 60: 4, 120: 5, 200: 6, 300: 7, 400: 8, 500: 9, 600: 9, 800: 10, 1000: 11, 1250: 11, 1500: 12, 2000: 12, 2500: 13, 3000: 13}
RNAME = {1: 'Pham', 2: 'Linh', 3: 'Huyen', 4: 'Dia', 5: 'Thien', 6: 'Thanh', 7: 'Tien', 8: 'Than', 9: 'De', 10: 'Dao', 11: 'HonDon', 12: 'ThaiSo', 13: 'VinhHang'}
RVI = {1: 'Phàm', 2: 'Linh', 3: 'Huyền', 4: 'Địa', 5: 'Thiên', 6: 'Thánh', 7: 'Tiên', 8: 'Thần', 9: 'Đế', 10: 'Đạo', 11: 'Hỗn Độn', 12: 'Thái Sơ', 13: 'Vĩnh Hằng'}
RCOLOR = {1: '#9e9e9e', 2: '#4caf50', 3: '#2196f3', 4: '#795548', 5: '#ff9800', 6: '#ffeb3b', 7: '#e040fb', 8: '#f44336', 9: '#b71c1c', 10: '#d4af37', 11: '#7e57c2', 12: '#26c6da', 13: '#ffb300'}
PREFIX = {1: 'Phàm', 10: 'Linh', 30: 'Huyền', 60: 'Địa', 120: 'Thiên', 200: 'Thánh', 300: 'Tiên', 400: 'Thần', 500: 'Hồng Mông', 600: 'Đế', 800: 'Đạo',
          1000: 'Hỗn Độn', 1250: 'Thái Hư Hỗn Độn', 1500: 'Thái Sơ', 2000: 'Thái Sơ Hồng Hoang', 2500: 'Vĩnh Hằng', 3000: 'Vạn Cổ Vĩnh Hằng'}
SET_OF = {500: 9, 1000: 11, 1250: 11, 1500: 12, 2000: 12, 2500: 13, 3000: 13}   # Thần Trang của bậc mới (bộ 11 Hỗn Độn: 2 bậc làn 3 · bộ 12 Thái Sơ: 1500 + 2000 · bộ 13 Vĩnh Hằng: 2500 + 3000)
SLOT_NAME = {1: 'Vũ Khí', 2: 'Thiên Thuẫn', 3: 'Thiên Quan', 4: 'Giáp Bào', 5: 'Hộ Thủ', 6: 'Giới Chỉ', 7: 'Yêu Đai', 8: 'Phi Phong',
             9: 'Linh Phù', 10: 'Ngọc Nhãn', 11: 'Cổ Ngọc', 12: 'Vai Khôi', 13: 'Linh Đai', 14: 'Linh Bội', 15: 'Tọa Kỵ'}
NEW_ID0 = 181

# Chỉ số huyền theo ô (mỗi món chỉ mang 1 loại) — cân 2026-10-28, xem docs/KHUNG_TONG_500_4300.md mục 4
BREAK_SLOTS = (9, 10, 11)    # Linh Phù · Ngọc Nhãn · Cổ Ngọc  -> tuposjs (tỉ lệ đột phá +%)
CULT_SLOTS = (8, 13, 14)     # Phi Phong · Linh Đai · Linh Bội -> tupoexp (tốc độ tu luyện +%)
BREAK_PCT = {1: 0, 10: 1, 30: 1, 60: 2, 120: 2, 200: 3, 300: 3, 400: 4, 500: 4, 600: 5, 800: 5, 1000: 6, 1250: 6, 1500: 7, 2000: 7, 2500: 8, 3000: 8}
CULT_PCT = {1: 1, 10: 2, 30: 2, 60: 4, 120: 4, 200: 6, 300: 6, 400: 8, 500: 8, 600: 10, 800: 10, 1000: 12, 1250: 12, 1500: 14, 2000: 14, 2500: 16, 3000: 16}

def apply_mystic(v):
    s, T = v['zbtool'], v['zblv']
    v['tuposjs'] = BREAK_PCT[T] if s in BREAK_SLOTS else 0
    v['tupoexp'] = CULT_PCT[T] if s in CULT_SLOTS else 0

# ─── Đường chỉ số gốc nhân vật (game/tupo.php + upplayerlv sau đợt F) ───
GJ = [2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55]
HP = [30, 50, 70, 90, 110, 130, 170, 210, 250, 300, 350, 400, 450, 500, 550]
def base_curve(maxlv=800):
    out, g, f, h = {}, 12.0, 5.0, 35.0
    for L in range(1, maxlv + 1):
        out[L] = (g, f, h)
        i = min(L // 50, 14)
        g += 2 + round(L / 2.5) + GJ[i]
        f += 3 + round(L / 2) + GJ[i]
        h += 4 + round(L / 1.2) + HP[i]
    return out
BASE = base_curve(3500)

STATS = ('zbgj', 'zbfy', 'zbhp')

def load_src():
    return json.load(open(SRC, encoding='utf-8'))

def first_by_slot(items, T):
    d = {}
    for v in sorted(items.values(), key=lambda v: v['zbid']):
        if v['zblv'] == T and v['zbtool'] not in d:
            d[v['zbtool']] = v
    return d

def slot_profile(src):
    """Tỉ lệ mỗi ô trong tổng chỉ số của bậc (trung bình các bậc đủ 15 ô)."""
    acc = collections.defaultdict(lambda: collections.defaultdict(list))
    for T in PROFILE_TIERS:
        d = first_by_slot(src, T)
        tot = {k: sum(v[k] for v in d.values()) for k in STATS}
        for s, v in d.items():
            for k in STATS:
                acc[s][k].append(v[k] / tot[k] if tot[k] else 0)
    return {s: {k: sum(l) / len(l) for k, l in ks.items()} for s, ks in acc.items()}

def tier_targets(src, prof):
    """Tổng chỉ số đủ bộ mỗi bậc: max(bản gốc quy về 15 ô, 50% chỉ số gốc nhân vật)."""
    out = {}
    for T in TIERS:
        d = first_by_slot(src, T)
        tgt = {}
        for k in STATS:
            have = sum(v[k] for v in d.values())
            share = sum(prof[s][k] for s in d) if d else 0
            cur15 = have / share if share else 0
            want = GEAR_SHARE * BASE[T][('zbgj', 'zbfy', 'zbhp').index(k)]
            tgt[k] = cur15 if T in KEEP_TIERS else max(cur15, want)
        out[T] = tgt
    return out

def slot_label(src, s):
    """Tên ô theo bậc 400 (bỏ tiền tố 'Thần ')."""
    if s == 13:
        return 'Ngọc Đai'  # tránh trùng tên "<bậc> Đai" của ô 7 Yêu Đai ở bậc cũ
    d = first_by_slot(src, 400)
    return d[s]['zbname'].replace('Thần ', '', 1) if s in d else SLOT_NAME[s]

def build_items():
    src = load_src()
    prof = slot_profile(src)
    tgt = tier_targets(src, prof)
    out = {}
    for k, v in src.items():
        v = dict(v)
        T = v['zblv']
        if T not in KEEP_TIERS:
            for st in STATS:
                v[st] = int(round(prof[v['zbtool']][st] * tgt[T][st]))
        r = RARITY[T]
        v.update({'rarity': r, 'rarity_name': RNAME[r], 'zbys': RCOLOR[r]})
        apply_mystic(v)
        out[k] = v
    nid = NEW_ID0
    added = []
    for T in TIERS:
        have = first_by_slot(src, T)
        ref = first_by_slot(src, 400)
        for s in range(1, 16):
            if s in have:
                continue
            base_item = ref[s]
            r = RARITY[T]
            name = PREFIX[T] + ' ' + (base_item['zbname'].replace('Thần ', '', 1) if T == 500 else slot_label(src, s))
            v = {'zbname': name, 'zbinfo': 'Trang bị bậc %s, gắn vào ô %s.' % (PREFIX[T], SLOT_NAME[s]),
                 'zbgj': int(round(prof[s]['zbgj'] * tgt[T]['zbgj'])), 'zbfy': int(round(prof[s]['zbfy'] * tgt[T]['zbfy'])),
                 'zbbj': base_item['zbbj'], 'zbxx': base_item['zbxx'], 'tuposjs': 0, 'tupoexp': 0, 'zbid': nid,
                 'zbhp': int(round(prof[s]['zbhp'] * tgt[T]['zbhp'])), 'zblv': T, 'zbtool': s, 'zbys': RCOLOR[r],
                 'taozhuang': SET_OF[T] if T in SET_OF else list(have.values())[0]['taozhuang'],
                 'element': 'none', 'rarity': r, 'rarity_name': RNAME[r], 'realm_require': T}
            apply_mystic(v)
            out[str(nid)] = v
            added.append(v)
            nid += 1
    return out, added, tgt

def dump_zb(d):
    """Giữ đúng định dạng file gốc (mỗi món 1 dòng gọn)."""
    parts = []
    for k in sorted(d, key=int):
        body = ','.join('%s: %s' % (json.dumps(a, ensure_ascii=False), json.dumps(b, ensure_ascii=False)) for a, b in d[k].items())
        parts.append('    %s: {\n        %s\n    }' % (json.dumps(k), body))
    return '{\n' + '\n,\n'.join(parts) + '\n}'

def build_chetao(items):
    """Sửa thẳng trên văn bản để giữ định dạng viết tay của file."""
    import re
    p = os.path.join(CFG, 'chetao_config.json')
    raw = open(p, encoding='utf-8').read()
    d = json.loads(raw)
    n = 0
    for r in d['recipes'].values():
        if r.get('type') == 'equipment' and str(r['result_zbid']) in items:
            need = items[str(r['result_zbid'])]['zblv']
            pat = re.compile(r'("id": %d,\s*"type": "equipment".*?"level_required": )(\d+)' % r['id'], re.S)
            raw, k = pat.subn(lambda m: m.group(1) + str(need), raw, count=1)
            if k and r['level_required'] != need:
                n += 1
    with open(p, 'w', encoding='utf-8') as f:
        f.write(raw)
    return n

def build_drops(items):
    p = os.path.join(CFG, 'guaiwu_config.json')
    g = json.load(open(p, encoding='utf-8'))
    t500 = sorted([v for v in items.values() if v['zblv'] == 500 and v['zbtool'] in (1, 4, 6, 7, 9)], key=lambda v: v['zbtool'])
    ids = [str(v['zbid']) for v in t500]
    n = 0
    for m in g.values():
        if 450 <= int(m['glv']) <= 500 and m.get('gzb'):   # chỉ quái làn 1; làn 2+ có bảng rơi riêng (lan*_design.py)
            cur = m['gzb'].split(',')
            add = [i for i in ids if i not in cur]
            if add:
                m['gzb'] = ','.join(cur + add)
                n += 1
    with open(p, 'w', encoding='utf-8') as f:
        json.dump(g, f, ensure_ascii=False, indent=2)
        f.write('\n')
    return n

# ─── Cân lại quái đợt B ─────────────────────────────────────────
MON_HP_MULT = 1.4
MON_HITS_TO_KILL = 45   # số đòn quái Thường cần để hạ nhân vật chuẩn
MODEL_QH = 10

def player_model(items, L):
    T = max(t for t in TIERS if t <= L)
    d = {}
    for v in sorted(items.values(), key=lambda v: v['zbid']):
        if v['zblv'] == T and v['zbtool'] not in d:
            d[v['zbtool']] = v
    m = 1 + 0.08 * MODEL_QH
    b = BASE[L]
    return [b[i] + sum(v[k] for v in d.values()) * m for i, k in enumerate(STATS)]

def atk_mult(items, L):
    """Hệ số nhân công quái so với công thức đợt B: đòn quái chủ yếu ăn sàn 15% công (phòng người > công quái)."""
    import sys
    sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
    from dot_b_design import base_stats
    hp = player_model(items, L)[2]
    return max(1.0, hp / MON_HITS_TO_KILL / 0.15 / base_stats(L)[1])

def build_monsters(items):
    import sys
    sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
    from dot_b_design import monsters, bosses
    p = os.path.join(CFG, 'guaiwu_config.json')
    g = json.load(open(p, encoding='utf-8'))
    for m in monsters():
        v = g[str(m['id'])]
        v['ghp'] = int(round(m['ghp'] * MON_HP_MULT, -1))
        v['ggj'] = int(round(m['ggj'] * atk_mult(items, m['lv'])))
    with open(p, 'w', encoding='utf-8') as f:
        json.dump(g, f, ensure_ascii=False, indent=2)
        f.write('\n')
    bs = [{'bossname': b['name'], 'bosslv': b['lv'], 'bosshp': int(round(b['hp'] * MON_HP_MULT, -2)),
           'bossgj': int(round(b['gj'] * atk_mult(items, b['lv'])))} for b in bosses()]
    with open(os.path.join(ROOT, 'sql', 'dot_f_data.json'), 'w', encoding='utf-8') as f:
        json.dump({'_note': 'Sinh bởi tools/gen/dot_f_build.py — dùng cho sql/migrate_dot_f.php', 'bosses': bs}, f, ensure_ascii=False, indent=1)
        f.write('\n')
    return len(monsters()), bs

if __name__ == '__main__':
    items, added, tgt = build_items()
    with open(os.path.join(CFG, 'zhuangbei_config.json'), 'w', encoding='utf-8') as f:
        f.write(dump_zb(items))
    print('trang bi:', len(items), '| them moi:', len(added))
    for T in TIERS:
        print('  bac %3d tong du bo: cong %7d phong %7d mau %8d' % (T, tgt[T]['zbgj'], tgt[T]['zbfy'], tgt[T]['zbhp']))
    print('che tao sua cap yeu cau:', build_chetao(items))
    print('quai >=450 roi them do bac 500:', build_drops(items))
    n, bs = build_monsters(items)
    print('quai dot B can lai:', n, '| boss:', ', '.join('%s %d/%d' % (b['bossname'], b['bosshp'], b['bossgj']) for b in bs))
    for L in (80, 150, 200, 300, 400, 500):
        print('  he so cong quai cap %d: x%.2f' % (L, atk_mult(items, L)))
