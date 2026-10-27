# -*- coding: utf-8 -*-
"""
Ghi dữ liệu đợt C (chạy lại nhiều lần cho cùng kết quả):
  1) class/config/nhiemvu_main_config.json — giữ chương 1–3, thay chương 4–13 (chính tuyến cấp 50–500).
  2) class/config/npc_config.json + renwu_config.json — 9 NPC khu mới, mỗi NPC 3 nhiệm vụ (mã NPC 40–48, NV 70–96).
  3) sql/dot_c_data.json — NPC đặt ở map nào (dùng cho sql/migrate_dot_c.php).
Chạy: python3 tools/gen/dot_c_build.py
"""
import json, os

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
DOTB = json.load(open(os.path.join(ROOT, 'sql', 'dot_b_data.json'), encoding='utf-8'))
GW = json.load(open(os.path.join(CFG, 'guaiwu_config.json'), encoding='utf-8'))

def gname(gid):
    return GW[str(gid)]['gname']

# % thanh cấp theo loại nhiệm vụ (mặc định main = 15 trong nhiemvu_reward_rules.json)
PCT = {'reach_region': 5, 'kill': 12, 'kill_monster': 12, 'learn_book': 8, 'use_move': 8, 'conghuong': 10,
       'upgrade_slot': 10, 'alchemy_count': 8, 'craft_count': 8, 'collect_item': 10, 'kill_boss_name': 25,
       'breakthrough': 25}

def q(cid, n, name, typ, lvmin, **kw):
    d = {'id': '%d.%d' % (cid, n), 'name': name, 'type': typ}
    d.update(kw)
    d['exp'] = 0
    d['lt'] = 10 * lvmin
    d['mt'] = 0
    d['exp_pct'] = PCT[typ]
    return d

# Tinh Anh (map cuối) mỗi khu mới = mã quái cuối của danh sách mgid map cuối
ELITE = {}
for r in DOTB['regions']:
    last = r['maps'][-1]['mgid'].split(',')[-1].split('|')[0]
    ELITE[r['qyid']] = int(last)
BOSS = {b['region']: b['bossname'] for b in DOTB['bosses']}
QYNAME = {r['qyid']: r['qyname'] for r in DOTB['regions']}

def region_chapter(cid, name, lr, qy, kill_n, elite_n, extra):
    """Khuôn chương cho 1 khu mới: đến khu → diệt quái → diệt Tinh Anh → (thêm) → hạ Boss → đột phá."""
    lo, hi = lr
    band = [int(x) for x in DOTB_BAND[qy]]
    qs = [q(cid, 1, 'Đặt Chân ' + QYNAME[qy], 'reach_region', lo, region=qy),
          q(cid, 2, 'Thanh Tẩy ' + QYNAME[qy], 'kill', lo, target_monster_min=band[0], target_monster_max=band[1], target_count=kill_n),
          q(cid, 3, 'Trảm ' + gname(ELITE[qy]), 'kill_monster', lo, monster_ids=[ELITE[qy]], target_count=elite_n)]
    n = 4
    for e in extra:
        e = dict(e)
        nm, typ = e.pop('name'), e.pop('type')
        qs.append(q(cid, n, nm, typ, lo, **e)); n += 1
    qs.append(q(cid, n, 'Hạ ' + BOSS[QYNAME[qy]].replace('Boss·', ''), 'kill_boss_name', lo, boss_names=[BOSS[QYNAME[qy]]], target_count=1)); n += 1
    qs.append(q(cid, n, 'Đột Phá Cấp %d' % hi, 'breakthrough', lo, target_level=hi))
    return {'id': cid, 'name': name, 'level_range': [lo, hi], 'quests': qs}

DOTB_BAND = {r['qyid']: r['band'].replace('–', '-').split('-') for r in DOTB['regions']}

def main_chapters():
    ch = []
    # Chương 4–5: khu cũ (quái cũ, chỉ có dải cấp)
    ch.append({'id': 4, 'name': 'Viêm Dương Khởi Hành', 'level_range': [50, 60], 'quests': [
        q(4, 1, 'Đến Viêm Dương Thành', 'reach_region', 50, region=21),
        q(4, 2, 'Trừ Yêu Viêm Dương', 'kill', 50, target_monster_min=45, target_monster_max=58, target_count=60),
        q(4, 3, 'Cầu Bí Tịch Thiên Cơ Các', 'learn_book', 50, count=1),
        q(4, 4, 'Thi Triển Tuyệt Kỹ', 'use_move', 50, tier=2, count=3),
        q(4, 5, 'Thông Thiên Phủ Hiểm Địa', 'reach_region', 50, region=22),
        q(4, 6, 'Luyện Kiếm Thông Thiên', 'kill', 50, target_monster_min=51, target_monster_max=60, target_count=80),
        q(4, 7, 'Đột Phá Cấp 60', 'breakthrough', 50, target_level=60)]})
    ch.append({'id': 5, 'name': 'Tịch Dương Phong Ba', 'level_range': [60, 78], 'quests': [
        q(5, 1, 'Đến Tịch Dương Thành', 'reach_region', 60, region=25),
        q(5, 2, 'Tịch Dương Trừ Hại', 'kill', 60, target_monster_min=61, target_monster_max=71, target_count=100),
        q(5, 3, 'Cường Hóa Trang Bị +8', 'upgrade_slot', 60, slot='any', target_plus=8),
        q(5, 4, 'Vào Thiên Thủy Cốc', 'reach_region', 60, region=26),
        q(5, 5, 'Thiên Thủy Cốc Luyện Thân', 'kill', 60, target_monster_min=65, target_monster_max=77, target_count=120),
        q(5, 6, 'Luyện Đan Hộ Thân', 'alchemy_count', 60, count=5),
        q(5, 7, 'Đột Phá Cấp 78', 'breakthrough', 60, target_level=78)]})
    # Chương 6–13: khu đợt B
    ch.append(region_chapter(6, 'Tây Lương Sa Mạc', (78, 92), 27, 120, 20, []))
    ch.append({'id': 7, 'name': 'Hoa Quả Sơn', 'level_range': [92, 106], 'quests': [
        q(7, 1, 'Lên Hoa Quả Sơn', 'reach_region', 92, region=24),
        q(7, 2, 'Hầu Vương Thử Thách', 'kill', 92, target_monster_min=92, target_monster_max=105, target_count=150),
        q(7, 3, 'Cộng Hưởng Sơ Thành', 'conghuong', 92, tiers=1),
        q(7, 4, 'Rèn Binh Khí', 'craft_count', 92, count=2),
        q(7, 5, 'Đột Phá Cấp 106', 'breakthrough', 92, target_level=106)]})
    ch.append(region_chapter(8, 'Hoang Thiên Lý', (106, 150), 28, 200, 30,
                             [{'name': 'Tuyệt Chiêu Khai Phong', 'type': 'use_move', 'tier': 3, 'count': 3}]))
    ch.append(region_chapter(9, 'Hồi Đầu Ngạn', (150, 200), 30, 250, 30,
                             [{'name': 'Tích Trữ Cường Hóa Thạch', 'type': 'collect_item', 'item_id': 1, 'item_count': 20}]))
    ch.append(region_chapter(10, 'Hồng Hoang Cổ Địa', (200, 250), 31, 300, 40,
                             [{'name': 'Cộng Hưởng Tam Trọng', 'type': 'conghuong', 'tiers': 3}]))
    ch.append(region_chapter(11, 'Bắc Minh Hải Vực', (250, 300), 32, 300, 40,
                             [{'name': 'Phá Thế Hải Triều', 'type': 'use_move', 'tier': 3, 'count': 10}]))
    c12 = region_chapter(12, 'Côn Lôn – Vạn Yêu', (300, 400), 33, 300, 40, [])
    c12b = region_chapter(12, '', (350, 400), 34, 300, 40, [{'name': 'Cộng Hưởng Ngũ Trọng', 'type': 'conghuong', 'tiers': 5}])
    c12['quests'] = c12['quests'][:-1] + c12b['quests']  # bỏ đột phá giữa chương
    for i, x in enumerate(c12['quests']): x['id'] = '12.%d' % (i + 1)
    c12['quests'][-1]['name'] = 'Đột Phá Cấp 400'
    c12['quests'][-1]['target_level'] = 400
    ch.append(c12)
    c13 = region_chapter(13, 'Cửu U – Thiên Ngoại', (400, 500), 35, 300, 40, [])
    c13b = region_chapter(13, '', (450, 500), 36, 300, 50, [])
    c13['quests'] = c13['quests'][:-1] + c13b['quests']
    for i, x in enumerate(c13['quests']): x['id'] = '13.%d' % (i + 1)
    ch.append(c13)
    return ch

def dump_main(d):
    """Giữ đúng định dạng file gốc: đầu chương 1 dòng, mỗi nhiệm vụ 1 dòng."""
    j = lambda x: json.dumps(x, ensure_ascii=False)
    chs = []
    for c in d['chapters']:
        head = ', '.join('%s: %s' % (j(k), j(v)) for k, v in c.items() if k != 'quests')
        qs = ',\n'.join('                ' + j(q) for q in c['quests'])
        chs.append('        {\n            %s,\n            "quests": [\n%s\n            ]\n        }' % (head, qs))
    return '{\n    "chapters": [\n' + ',\n'.join(chs) + '\n    ]\n}\n'

def build_main():
    p = os.path.join(CFG, 'nhiemvu_main_config.json')
    d = json.load(open(p, encoding='utf-8'))
    d['chapters'] = [c for c in d['chapters'] if c['id'] <= 3] + main_chapters()
    with open(p, 'w', encoding='utf-8') as f:
        f.write(dump_main(d))
    return sum(len(c['quests']) for c in d['chapters'] if c['id'] >= 4)

# ─── C3. NPC khu mới ────────────────────────────────────────────
# (qyid, tên NPC, giới tính, giới thiệu)
NPCS = [
    (27, 'Sa Mạc Lão Đạo', 'Nam', 'Lão đạo sĩ ẩn cư giữa cát vàng Tây Lương, nhiều năm canh giữ con đường tơ lụa trước yêu thú sa mạc.'),
    (28, 'Hoang Thành Tuần Thủ', 'Nam', 'Lính tuần thủ thành cổ Hoang Thiên Lý, đêm nào cũng nghe tiếng quỷ vệ gõ cửa vương lăng.'),
    (30, 'Bỉ Ngạn Độ Nhân', 'Nữ', 'Người chèo đò bên bờ Hồi Đầu Ngạn, đưa hồn lạc về bờ bên kia, biết rõ mọi yêu vật trong sương.'),
    (31, 'Thần Nông Thủ Mộ Nhân', 'Nam', 'Hậu duệ canh giữ mộ phần Viêm Đế, tay cầm cuốc thuốc, mắt nhìn thấu khí độc Hồng Hoang.'),
    (32, 'Bắc Minh Ngư Ông', 'Nam', 'Ông lão câu cá bên cửa biển Bắc Minh, tương truyền từng câu được vảy Côn khi còn trẻ.'),
    (33, 'Côn Lôn Thủ Sơn Đệ Tử', 'Nam', 'Đệ tử trực sơn môn Côn Lôn, phụng mệnh tông chủ thử thách người muốn lên tiên sơn.'),
    (34, 'Hàng Yêu Đạo Nhân', 'Nam', 'Đạo nhân mang hồ lô thu yêu, lang bạt Vạn Yêu Lâm đã trăm năm mà chưa thu đủ yêu vương.'),
    (35, 'Trấn Ma Tướng Quân', 'Nam', 'Tướng quân trấn giữ cửa Cửu U, giáp trụ đã đen kịt vì ma khí nhưng chưa từng lùi một bước.'),
    (36, 'Thiên Môn Tiếp Dẫn Sứ', 'Nữ', 'Sứ giả tiếp dẫn trước Thiên Môn, chỉ người đủ đạo hạnh mới được nàng mở đường lên Thiên Ngoại Thiên.'),
]
NPC_ID0, RW_ID0 = 40, 70

def mysql_rows(sql):
    import subprocess
    out = subprocess.run(['mysql', '-uroot', '--default-character-set=utf8mb4', '-N', 'game', '-e', sql],
                         capture_output=True, text=True, check=True).stdout
    return [l.split('\t') for l in out.strip().split('\n') if l]

def level_need(lv):
    return (lv + 1) * (lv + 1) * 110

def npc_data():
    """Mỗi NPC 3 nhiệm vụ: chuỗi 1 (quái map đầu) → chuỗi 2 (quái map giữa) + ngày (Tinh Anh map cuối)."""
    npcs, rws, place = {}, {}, []
    for i, (qy, nname, sex, info) in enumerate(NPCS):
        reg = [r for r in DOTB['regions'] if r['qyid'] == qy][0]
        lo = int(DOTB_BAND[qy][0])
        maps = reg['maps']
        picks = [(maps[0], 0, 30, 'chain'), (maps[len(maps) // 2], 0, 40, 'chain'), (maps[-1], -1, 20, 'daily')]
        nid = NPC_ID0 + i
        ids = []
        for k, (m, which, cnt, kind) in enumerate(picks):
            rwid = RW_ID0 + i * 3 + k
            gid = int(m['mgid'].split(',')[which].split('|')[0])
            row = mysql_rows("SELECT mid FROM mid WHERE mname = '%s' AND mqy = %d LIMIT 1" % (m['mname'].replace("'", "''"), qy))
            mid = int(row[0][0]) if row else 0
            pct = {0: 8, 1: 10, 2: 4}[k]
            yp = '9|3' if lo < 150 else ('1|2' if lo < 300 else '2|2')
            rws[str(rwid)] = {
                'rwname': ('Trừ hại ' if k < 2 else 'Tuần tra hằng ngày: ') + gname(gid),
                'rwqy': str(mid), 'rwzl': '2', 'rwlx': '3' if kind == 'chain' else '2',
                'lastrwid': str(RW_ID0 + i * 3) if k == 1 else '-1', 'rwid': str(rwid),
                'rwdj': '1|%d' % (3 if k < 2 else 1), 'rwzb': '',
                'rwexp': str(level_need(lo) * pct // 100), 'rwyxb': str(10 * lo if k < 2 else 5 * lo),
                'rwyq': str(gid),
                'rwinfo': '%s: "%s ở %s đang quấy nhiễu, đạo hữu hãy ra tay diệt %d con."' % (nname, gname(gid), m['mname'], cnt),
                'rwcount': str(cnt), 'rwyp': yp, 'rwjineng': ''}
            place.append({'rwid': rwid, 'mid': mid, 'mname': m['mname'], 'qyid': qy})
            ids.append(str(rwid))
        npcs[str(nid)] = {'id': nid, 'nname': nname, 'nsex': sex, 'ninfo': info, 'muban': '',
                          'taskid': ','.join(ids), 'mz': '', 'rwqy': ''}
    return npcs, rws, place

def build_npc():
    npcs, rws, place = npc_data()
    for fn, new in (('npc_config.json', npcs), ('renwu_config.json', rws)):
        p = os.path.join(CFG, fn)
        d = json.load(open(p, encoding='utf-8'))
        d.update(new)
        with open(p, 'w', encoding='utf-8') as f:
            json.dump(d, f, ensure_ascii=False, indent=2)
            f.write('\n')
    out = {'_note': 'Sinh bởi tools/gen/dot_c_build.py — dùng cho sql/migrate_dot_c.php',
           'npcs': [{'nid': NPC_ID0 + i, 'qyid': qy, 'nname': n} for i, (qy, n, s, t) in enumerate(NPCS)],
           'task_maps': place}
    with open(os.path.join(ROOT, 'sql', 'dot_c_data.json'), 'w', encoding='utf-8') as f:
        json.dump(out, f, ensure_ascii=False, indent=1)
        f.write('\n')
    return len(npcs), len(rws), [p for p in place if p['mid'] == 0]

if __name__ == '__main__':
    n = build_main()
    print('chinh tuyen moi (chuong 4-13):', n, 'nhiem vu')
    a, b, miss = build_npc()
    print('NPC:', a, '| nhiem vu NPC:', b, '| map chua co trong DB:', miss)
