# -*- coding: utf-8 -*-
"""
Làn 2 (cấp 501 -> 1000) — docs/KHUNG_TONG_500_4300.md + docs/LAN2_CAP_500_1000.md.
10 khu (qy 37–46) × 8 map, mỗi map 2 loại quái (Thường / Giáp / Cuồng xoay vòng), map cuối thêm Tinh Anh ⇒ 17 loại/khu,
1 boss/khu ở map cuối (cấp = cấp cuối cảnh giới).

Chỉ số quái (tính ngược từ nhân vật chuẩn đợt F, nối liền quái cấp 500 đang có):
  - Thủ   = đường đợt B (3,704 × cấp^1,18) × hệ số kiểu       (giữ thấp như quái cũ)
  - Máu   = HITS × (công nhân vật chuẩn − 0,75 × thủ quái)    HITS đo ở cấp 500 từ quái hiện có ⇒ nhịp đòn không đổi tới 1000
  - Công  = max(đường đợt B, máu nhân vật chuẩn ÷ 45 ÷ 0,15)  (như đợt F: ~45 đòn quái Thường hạ nhân vật chuẩn)
  Nhân vật chuẩn = chỉ số gốc (base_curve) + đủ bộ đồ bậc ≤ cấp (500/600/800) cường hóa theo khung (qh_at), chưa buff %.
Boss: máu ×12, công ×1,6, thủ ×1,3 so quái Thường cùng cấp.
Chạy: python3 tools/gen/lan2_design.py  -> in bảng kiểm tra.
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from dot_f_build import base_curve
from dot_b_design import base_stats, ARCH
from khung_tong_design import qh_at

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
BASE = base_curve(1000)
ZB = json.load(open(os.path.join(CFG, 'zhuangbei_config.json'), encoding='utf-8'))
GEAR_TIERS = (500, 600, 800)
HITS_TO_KILL_PLAYER = 45
MON_HP_MULT_B = 1.4            # đợt F nhân máu quái đợt B
GYID_START = 300               # mã quái làn 2: 300…
QY_START = 37

def gear(T):
    best = {}
    for v in sorted(ZB.values(), key=lambda v: v['zbid']):
        if v['zblv'] == T and v['zbtool'] not in best:
            best[v['zbtool']] = v
    return best

def player(L):
    T = max(t for t in GEAR_TIERS if t <= L)
    m = 1 + 0.08 * qh_at(L)
    g = gear(T)
    return [BASE[L][i] + sum(v[k] for v in g.values()) * m for i, k in enumerate(('zbgj', 'zbfy', 'zbhp'))]

def _hits_at_500():
    hp_old = base_stats(500)[0] * MON_HP_MULT_B
    pg = player(500)[0]
    return hp_old / (pg - 0.75 * base_stats(500)[2])
HITS = _hits_at_500()

def thuong_stats(L):
    pg, pf, ph = player(L)
    fy = base_stats(L)[2]
    hp = HITS * (pg - 0.75 * fy)
    gj = max(base_stats(L)[1], ph / HITS_TO_KILL_PLAYER / 0.15)
    return hp, gj, fy

# Trang bị rơi: 5 món (Vũ Khí, Giáp Bào, Giới Chỉ, Yêu Đai, Linh Phù) của bậc ≤ cấp quái — như đợt F build_drops
def zb_for(L):
    T = max(t for t in GEAR_TIERS if t <= L)
    g = gear(T)
    return ','.join(str(g[s]['zbid']) for s in (1, 4, 6, 7, 9) if s in g)

# Đột Phá Đan rơi theo cảnh giới (cùng bảng class/config/tupo_bonus_config.json)
PILL_CFG = json.load(open(os.path.join(CFG, 'tupo_bonus_config.json'), encoding='utf-8'))['pill']['items']
def pill_for(L):
    realm = (L - 1) // 50 if L % 50 == 0 else L // 50   # quái cấp 550 (Viên Mãn) vẫn thuộc cảnh giới trước
    for dj, r in PILL_CFG.items():
        if int(r) == realm: return int(dj)
    return None
def dj_for(L):
    p = pill_for(L)
    return '1,%d' % p if p else '1'

# Đuôi tên quái theo (kiểu, hệ) — 2 lựa chọn, tránh trùng trong cùng map
SUFFIX = {
 'thuong': {'kim': ('Kiếm Linh', 'Kim Ưng'), 'moc': ('Thụ Yêu', 'Đằng Xà'), 'thuy': ('Thủy Giao', 'Băng Lang'),
            'hoa': ('Hỏa Hồ', 'Viêm Điểu'), 'tho': ('Thạch Viên', 'Sa Hạt'), 'none': ('U Hồn', 'Yêu Lang')},
 'giap':   {'kim': ('Thiết Giáp Vệ', 'Kim Giáp Thú'), 'moc': ('Mộc Giáp Quy', 'Đằng Giáp Binh'), 'thuy': ('Huyền Quy', 'Giáp Giải'),
            'hoa': ('Dung Nham Khôi', 'Hỏa Lân Thú'), 'tho': ('Nham Giáp Thú', 'Thạch Khôi'), 'none': ('Cốt Giáp Tướng', 'Thiết Thi')},
 'cuong':  {'kim': ('Cuồng Kiếm Ma', 'Huyết Đao Khách'), 'moc': ('Cuồng Mộc Yêu', 'Độc Phong'), 'thuy': ('Cuồng Lãng Giao', 'Hàn Băng Ma'),
            'hoa': ('Liệt Diễm Ma', 'Hỏa Kỳ Lân'), 'tho': ('Cuồng Sa Thú', 'Địa Long'), 'none': ('Huyết Ma', 'Ảnh Yêu')},
}
ROT = (('thuong', 'giap'), ('giap', 'cuong'), ('cuong', 'thuong'))

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Linh Khư Cổ Mạch', ('tho', 'moc'),
  ['Cổ Mạch Nhập Khẩu', 'Linh Khư Thạch Lâm', 'Địa Mạch Huyệt', 'Cổ Thụ Linh Nguyên', 'Long Cốt Nhai', 'Linh Tuyền Động', 'Khư Hải Tàn Thành', 'Cổ Mạch Long Trì'],
  ('Linh Khư Cổ Long', 'tho'), 'Long Trì Thủ Hộ Giả'),
 ('Tụ Thần Phong', ('kim', 'none'),
  ['Phong Cước Trấn', 'Thần Đăng Lĩnh', 'Tụ Hồn Đài', 'Vạn Kiếm Bích', 'Thanh Vân Sạn Đạo', 'Ngưng Thần Các', 'Phong Đỉnh Vân Hải', 'Tụ Thần Điện'],
  ('Tụ Thần Phong Chủ', 'kim'), 'Tụ Thần Điện Hộ Pháp'),
 ('Thái Hư Huyễn Cảnh', ('none', 'thuy'),
  ['Huyễn Cảnh Môn', 'Kính Hoa Thủy Nguyệt', 'Mê Vụ Lâm', 'Hư Ảnh Hồ', 'Phù Sinh Mộng Trạch', 'Huyễn Nguyệt Cung', 'Thái Hư Tinh Lộ', 'Huyễn Cảnh Trung Tâm'],
  ('Thái Hư Huyễn Ma', 'none'), 'Huyễn Cảnh Mộng Yểm'),
 ('Bất Chu Thiên Trụ', ('tho', 'kim'),
  ['Chu Sơn Cổ Đạo', 'Đoạn Nhạc Nhai', 'Cự Nhân Mộ Địa', 'Thiên Khuyết Thạch Lâm', 'Phá Thiên Phong', 'Lôi Hỏa Cốc', 'Trụ Thiên Thạch Giai', 'Bất Chu Đỉnh'],
  ('Bất Chu Sơn Thần', 'tho'), 'Bất Chu Cự Linh'),
 ('Đạo Nguyên Thiên Trì', ('thuy', 'moc'),
  ['Thiên Trì Bắc Ngạn', 'Vân Mộng Trạch', 'Bích Thủy Đàm', 'Đạo Nguyên Tuyền', 'Hàn Băng Thủy Cung', 'Ngân Hà Lạc Bộc', 'Thủy Tinh Cung Khuyết', 'Thiên Trì Tâm'],
  ('Thiên Trì Thủy Mẫu', 'thuy'), 'Thiên Trì Giao Long'),
 ('Hư Không Loạn Lưu', ('none', 'kim'),
  ['Không Gian Liệt Khẩu', 'Toái Tinh Đới', 'Loạn Lưu Phong Bạo', 'Hư Không Phù Đảo', 'Vẫn Thạch Hải', 'Thời Không Liệt Cốc', 'Hắc Động Biên Duyên', 'Hư Không Thâm Xứ'],
  ('Hư Không Thôn Thiên Thú', 'none'), 'Hư Không Liệt Ảnh'),
 ('Tu La Chiến Trường', ('hoa', 'kim'),
  ['Tu La Doanh Trại', 'Huyết Sắc Bình Nguyên', 'Bạch Cốt Lũy', 'Chiến Hồn Đài', 'Tàn Kiếm Trủng', 'Huyết Hà Độ Khẩu', 'Tu La Vương Kỳ', 'Tu La Chiến Điện'],
  ('Tu La Chiến Thần', 'hoa'), 'Tu La Huyết Tướng'),
 ('Lôi Kiếp Uyên', ('hoa', 'none'),
  ['Lôi Vân Nhai', 'Kiếp Hỏa Cốc', 'Cửu Tiêu Lôi Trì', 'Lôi Đình Thạch Lâm', 'Thiên Phạt Đài', 'Tử Tiêu Lôi Hải', 'Lôi Kiếp Thâm Uyên', 'Cửu Thiên Lôi Đàn'],
  ('Cửu Thiên Lôi Tổ', 'hoa'), 'Lôi Đàn Kiếp Linh'),
 ('Thượng Cổ Thần Mộ', ('none', 'tho'),
  ['Thần Mộ Lăng Môn', 'Thạch Tượng Thần Đạo', 'Táng Thần Cốc', 'Cổ Thần Bi Lâm', 'Âm Binh Mộ Huyệt', 'Thần Huyết Trì', 'Vạn Cổ Tế Đàn', 'Thần Mộ Chủ Lăng'],
  ('Thần Mộ Thủ Lăng Nhân', 'none'), 'Chủ Lăng Thần Vệ'),
 ('Thánh Vực Thiên Cung', ('kim', 'hoa'),
  ['Nam Thiên Môn', 'Thánh Vực Vân Lộ', 'Dao Quang Điện', 'Quỳnh Lâu Ngọc Vũ', 'Thánh Quang Hành Lang', 'Thiên Cung Ngự Uyển', 'Thánh Đình Kim Khuyết', 'Thiên Cung Chính Điện'],
  ('Thiên Cung Thánh Chủ', 'kim'), 'Thiên Cung Thánh Vệ'),
]

def prefix(mname, rname=''):
    # 2 chữ đầu tên map; trùng 2 chữ đầu tên khu (Tu La…, Thiên Cung…) thì lấy 2 chữ cuối để tên quái không lặp
    w = mname.split()
    return ' '.join(w[-2:]) if w[:2] == rname.split()[:2] else ' '.join(w[:2])

def monsters():
    out, gid, used = [], GYID_START, set()
    for k, (rname, els, maps, boss, tinh) in enumerate(REGIONS):
        lv0 = 502 + 50 * k
        for i, mname in enumerate(maps):
            pair = ROT[i % 3]
            for j, arch in enumerate(pair):
                el = els[(i + j) % 2]
                lv = lv0 + 6 * i + 2 * j
                cnt = 2 if i < 7 else (2 if j == 0 else 1)
                opts = SUFFIX[arch][el]
                name = None
                for o in (opts[(i + j) % 2], opts[(i + j + 1) % 2]):
                    if '%s %s' % (prefix(mname, rname), o) not in used:
                        name = '%s %s' % (prefix(mname, rname), o); break
                assert name, (mname, arch, el)
                used.add(name)
                out.append(dict(id=gid, name=name, element=el, arch=arch, lv=lv, count=cnt, region=rname, qy=QY_START + k,
                                mi=i, mname=mname)); gid += 1
            if i == 7:
                lv = lv0 + 46
                assert tinh not in used
                used.add(tinh)
                out.append(dict(id=gid, name=tinh, element=els[0], arch='tinh', lv=lv, count=1, region=rname, qy=QY_START + k,
                                mi=i, mname=mname)); gid += 1
    for m in out:
        a = ARCH[m['arch']]
        hp, gj, fy = thuong_stats(m['lv'])
        m.update(archname=a[0], ghp=int(round(hp * a[1], -1)), ggj=int(round(gj * a[2])), gfy=int(round(fy * a[3])),
                 gbj=a[4], gxx=a[5], dljv=a[6], gzb=zb_for(m['lv']), gdj=dj_for(m['lv']), djjv=5, gyp='2', ypjv=15)
    return out

def bosses():
    out = []
    for k, (rname, els, maps, (bname, bel), tinh) in enumerate(REGIONS):
        lv = 550 + 50 * k
        hp, gj, fy = thuong_stats(lv)
        out.append(dict(name='Boss·' + bname, element=bel, lv=lv, region=rname, qy=QY_START + k,
                        hp=int(round(hp * 12, -2)), gj=int(round(gj * 1.6)), fy=int(round(fy * 1.3)),
                        bj=15, xx=10, zb=zb_for(lv), dj=dj_for(lv)))
    return out

def hits(L, hp, gj, fy):
    pg, pf, ph = player(L)
    return hp / max(pg * 0.15, pg - 0.75 * fy), ph / max(gj * 0.15, gj - 0.75 * pf)

if __name__ == '__main__':
    print('HITS (đòn người hạ quái Thường, đo ở cấp 500) = %.2f' % HITS)
    print('| Cấp | Người công/thủ/máu | Quái Thường máu/công/thủ | Đòn hạ quái | Đòn quái hạ người |')
    print('|---|---|---|---|---|')
    for L in (500, 501, 550, 599, 600, 700, 799, 800, 900, 1000):
        p = player(L); h, g, f = thuong_stats(L)
        a, b = hits(L, h, g, f)
        print('| %d | %d / %d / %d | %d / %d / %d | %.1f | %.1f |' % (L, p[0], p[1], p[2], h, g, f, a, b))
    ms = monsters()
    print('\nQuái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        print('%s cấp %d máu %s công %s thủ %s rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), format(b['gj'], ','),
              format(b['fy'], ','), b['zb'], b['dj']))
