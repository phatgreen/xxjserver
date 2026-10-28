# -*- coding: utf-8 -*-
"""
Làn 2 — nhiệm vụ (L2-C), chạy lại nhiều lần cho cùng kết quả:
  1) class/config/nhiemvu_main_config.json — giữ chương 1–13, thay chương 14–23 (1 chương / khu, cấp 500–1000).
  2) class/config/npc_config.json + renwu_config.json — 10 NPC khu (mã 49–58), mỗi NPC 3 nhiệm vụ (mã 97–126).
  3) sql/lan2_c_data.json — NPC đặt ở map cửa vào khu (dùng cho sql/migrate_lan2_c.php).
Cùng khuôn đợt C (tools/gen/dot_c_build.py). Map dùng id cố định của làn 2 (sql/lan2_data.json) ⇒ nút Truyền tống đúng mọi máy.
Chạy: python3 tools/gen/lan2_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from dot_c_build import q, dump_main, level_need, gname, CFG, ROOT

L2 = json.load(open(os.path.join(ROOT, 'sql', 'lan2_data.json'), encoding='utf-8'))
PILLS = json.load(open(os.path.join(CFG, 'tupo_bonus_config.json'), encoding='utf-8'))['pill']['items']
DJ = json.load(open(os.path.join(CFG, 'daoju_config.json'), encoding='utf-8'))
CH0 = 14
# Tham số làn (lan3_c_build.py gán lại các biến này rồi gọi build_main / build_npc)
LV0 = 500          # cấp đầu làn
OUT_DATA = 'lan2_c_data.json'

def pill_of_region(k):
    realm = LV0 // 50 + k              # khu k = cảnh giới floor(cấp/50) (làn 2: 10 + k = Nguyên Anh … Thánh Giả)
    for dj, r in PILLS.items():
        if int(r) == realm: return int(dj)
    return None

def chapter(k, r):
    cid, lo, hi = CH0 + k, LV0 + 50 * k, LV0 + 50 + 50 * k
    qy, name = r['qyid'], r['qyname']
    last = r['maps'][-1]['mgid'].split(',')
    elite = int(last[-1].split('|')[0])
    boss = [b['bossname'] for b in L2['bosses'] if b['qyid'] == qy][0]
    p = pill_of_region(k)
    if p:
        collect = q(cid, 4, 'Thu Thập ' + DJ[str(p)]['djname'], 'collect_item', lo, item_id=p, item_count=3)
    else:
        collect = q(cid, 4, 'Tích Trữ Cường Hóa Thạch', 'collect_item', lo, item_id=1, item_count=30)
    qs = [q(cid, 1, 'Đặt Chân ' + name, 'reach_region', lo, region=qy),
          q(cid, 2, 'Thanh Tẩy ' + name, 'kill', lo, target_monster_min=lo + 1, target_monster_max=hi, target_count=300),
          q(cid, 3, 'Trảm ' + gname(elite), 'kill_monster', lo, monster_ids=[elite], target_count=50),
          collect,
          q(cid, 5, 'Hạ ' + boss.replace('Boss·', ''), 'kill_boss_name', lo, boss_names=[boss], target_count=1),
          q(cid, 6, 'Đột Phá Cấp %d' % hi, 'breakthrough', lo, target_level=hi)]
    return {'id': cid, 'name': name, 'level_range': [lo, hi], 'quests': qs}

def build_main():
    p = os.path.join(CFG, 'nhiemvu_main_config.json')
    d = json.load(open(p, encoding='utf-8'))
    # Chỉ thay đúng dải chương của làn này (giữ chương làn khác, kể cả làn sau)
    mine = [chapter(k, r) for k, r in enumerate(L2['regions'])]
    ids = set(c['id'] for c in mine)
    d['chapters'] = sorted([c for c in d['chapters'] if c['id'] not in ids] + mine, key=lambda c: c['id'])
    with open(p, 'w', encoding='utf-8') as f:
        f.write(dump_main(d))
    return sum(len(c['quests']) for c in mine)

# (tên NPC, giới tính, giới thiệu) theo thứ tự khu 37–46
NPCS = [
    ('Cổ Mạch Khám Dư Sư', 'Nam', 'Thầy khám dư cả đời dò long mạch, biết rõ chỗ linh khí Linh Khư cuộn trào và chỗ yêu vật ẩn mình.'),
    ('Thủ Đăng Lão Ni', 'Nữ', 'Lão ni giữ ngọn thần đăng trên Tụ Thần Phong suốt ba trăm năm, đèn chưa từng tắt một đêm.'),
    ('Mộng Điệp Tiên Tử', 'Nữ', 'Tiên tử hóa thân từ cánh bướm trong mộng, dẫn người lạc lối ra khỏi Thái Hư Huyễn Cảnh.'),
    ('Cự Linh Hậu Duệ', 'Nam', 'Hậu duệ tộc Cự Linh từng chống trời ở Bất Chu, thân cao chín thước, giọng như sấm rền.'),
    ('Thiên Trì Tẩy Kiếm Nhân', 'Nam', 'Kiếm khách ngày ngày rửa kiếm bên Thiên Trì, nói nước trì có thể gột sạch sát khí trong kiếm.'),
    ('Hư Không Lữ Khách', 'Nam', 'Lữ khách lạc giữa loạn lưu không gian, đã đi qua trăm thế giới mà chưa tìm được đường về.'),
    ('Tàn Binh Lão Tướng', 'Nam', 'Lão tướng sống sót duy nhất của một đạo quân Tu La, vẫn đứng gác doanh trại đã đổ nát.'),
    ('Độ Kiếp Tán Nhân', 'Nam', 'Tán nhân từng chín lần độ kiếp thất bại, nay ở lại Lôi Kiếp Uyên chỉ đường cho kẻ đến sau.'),
    ('Thần Mộ Tế Ti', 'Nữ', 'Nữ tế ti canh giữ lăng mộ cổ thần, hiểu tiếng của âm binh và những lời nguyền trên bia đá.'),
    ('Thiên Cung Tiếp Dẫn Tiên Quan', 'Nam', 'Tiên quan trực Nam Thiên Môn, chỉ mở cổng Thiên Cung cho người đủ đạo hạnh thánh giả.'),
]
NPC_ID0, RW_ID0 = 49, 97

def npc_data():
    """Như đợt C: chuỗi 1 (quái map đầu, 30 con) → chuỗi 2 (quái map giữa, 40 con) + hằng ngày (Tinh Anh map cuối, 20 con)."""
    npcs, rws, place = {}, {}, []
    for i, ((nname, sex, info), r) in enumerate(zip(NPCS, L2['regions'])):
        lo = LV0 + 50 * i
        maps = r['maps']
        picks = [(maps[0], 0, 30, 'chain'), (maps[len(maps) // 2], 0, 40, 'chain'), (maps[-1], -1, 20, 'daily')]
        ids = []
        for k, (m, which, cnt, kind) in enumerate(picks):
            rwid = RW_ID0 + i * 3 + k
            gid = int(m['mgid'].split(',')[which].split('|')[0])
            pct = {0: 8, 1: 10, 2: 4}[k]
            rws[str(rwid)] = {
                'rwname': ('Trừ hại ' if k < 2 else 'Tuần tra hằng ngày: ') + gname(gid),
                'rwqy': str(m['mid']), 'rwzl': '2', 'rwlx': '3' if kind == 'chain' else '2',
                'lastrwid': str(RW_ID0 + i * 3) if k == 1 else '-1', 'rwid': str(rwid),
                'rwdj': '1|%d' % (3 if k < 2 else 1), 'rwzb': '',
                'rwexp': str(level_need(lo) * pct // 100), 'rwyxb': str(10 * lo if k < 2 else 5 * lo),
                'rwyq': str(gid),
                'rwinfo': '%s: "%s ở %s đang quấy nhiễu, đạo hữu hãy ra tay diệt %d con."' % (nname, gname(gid), m['mname'], cnt),
                'rwcount': str(cnt), 'rwyp': '2|2', 'rwjineng': ''}
            place.append({'rwid': rwid, 'mid': m['mid'], 'mname': m['mname'], 'qyid': r['qyid']})
            ids.append(str(rwid))
        nid = NPC_ID0 + i
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
    out = {'_note': 'Sinh bởi tools/gen/%s_build.py — dùng cho sql/migrate_%s.php' % (OUT_DATA[:-10], OUT_DATA[:-10]),
           'npcs': [{'nid': NPC_ID0 + i, 'qyid': r['qyid'], 'nname': n[0], 'mid': r['maps'][0]['mid']}
                    for i, (n, r) in enumerate(zip(NPCS, L2['regions']))],
           'task_maps': place}
    with open(os.path.join(ROOT, 'sql', OUT_DATA), 'w', encoding='utf-8') as f:
        json.dump(out, f, ensure_ascii=False, indent=1)
        f.write('\n')
    return len(npcs), len(rws)

if __name__ == '__main__':
    print('chinh tuyen lan 2 (chuong 14-23):', build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', build_npc())
