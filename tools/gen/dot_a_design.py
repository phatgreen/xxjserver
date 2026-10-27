# -*- coding: utf-8 -*-
"""
Thiết kế đợt A — sinh bảng võ công / công pháp mới theo công thức (dùng chung cho tài liệu và dữ liệu JSON).
Chạy: python3 tools/gen/dot_a_design.py  -> in bảng markdown.
"""
import json, os
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

# ---- Võ công: phẩm ----
PHAM = [  # key, nhãn, realm (jingjieValue), ngân sách chỉ số/cấp, hệ số thời gian luyện, giá LT, giá MT, màu
    # Ngân sách = gj + fy + hp/10 + bj + xx, hiệu chỉnh theo bộ cũ: Nhập Môn ~15, Trung Cấp ~26, Thượng Thừa ~38, Thần Công ~55
    ('ha_thua',     'Hạ Thừa',     3,  20, 1.2,  3000, 0,     '#6aa84f'),
    ('trung_cap',   'Trung Cấp',   9,  30, 1.8,  0,    700,   '#288dc7'),
    ('thuong_thua', 'Thượng Thừa', 15, 45, 3.0,  0,    2800,  '#39b512'),
    ('than_cong',   'Tuyệt Thế',   23, 62, 14.0, 0,    45000, '#f9c508'),
    ('tien_dao',    'Tiên Đạo',    30, 80, 25.0, 0,    0,     '#e040fb'),  # không bán: rơi từ Boss
]
# Hồ sơ chỉ số theo hệ: tỉ lệ ngân sách cho (gj, fy, hp/10, bj, xx) — lấy trung bình bộ cũ cùng hệ
PROFILE = {
    'kim':  (0.40, 0.15, 0.25, 0.20, 0.00),
    'hoa':  (0.50, 0.08, 0.20, 0.22, 0.00),
    'tho':  (0.11, 0.44, 0.45, 0.00, 0.00),
    'thuy': (0.22, 0.32, 0.34, 0.04, 0.08),
    'moc':  (0.12, 0.28, 0.48, 0.00, 0.12),
    'all':  (0.25, 0.25, 0.25, 0.15, 0.10),
}
# Hiệu ứng theo hệ, giá trị theo 5 phẩm mới (đặt giữa các bộ cũ cùng loại)
EFFECT = {
    'kim':  ('pierce_def',     (15, 20, 28, 35, 45)),
    'hoa':  ('crit_dmg',       (20, 30, 40, 55, 70)),
    'tho':  ('damage_reduce',  (12, 18, 26, 36, 45)),
    'thuy': ('escape',         (40, 55, 70, 85, 95)),
    'moc':  ('hp_regen',       (6, 10, 14, 20, 25)),
}
EFFECT_ALL = [('exp_bonus', 8), ('drop_bonus', 10), ('combo_hit', 22), ('steal_reward', 18), ('counter_lifesteal', 15)]
CAT = {'kim': 'ngoai_cong', 'hoa': 'ngoai_cong', 'tho': 'ngoai_cong', 'thuy': 'khinh_cong', 'moc': 'noi_cong', 'all': 'tap_ky'}
PURPOSE = {'kim': 'pk', 'hoa': 'pk', 'tho': 'farm', 'thuy': 'pk', 'moc': 'farm', 'all': 'farm'}
NAMES = {  # (phẩm, hệ) -> (tên, loại hiển thị)
 ('ha_thua','kim'):('Phá Giáp Kim Cương Đao','Đao Pháp / Ngoại Công'),
 ('ha_thua','moc'):('Thanh Đằng Dưỡng Khí Quyết','Nội Công / Luyện Thể'),
 ('ha_thua','thuy'):('Lưu Vân Phiêu Bộ','Thân Pháp / Khinh Công'),
 ('ha_thua','hoa'):('Xích Diễm Liên Quyền','Quyền Pháp / Ngoại Công'),
 ('ha_thua','tho'):('Hậu Thổ Thiết Bố Sam','Hộ Thể / Ngoại Công'),
 ('ha_thua','all'):('Bách Nghệ Tạp Thuật','Tạp Kỹ / Thực Dụng'),
 ('trung_cap','kim'):('Truy Phong Kiếm Quyết','Kiếm Pháp / Ngoại Công'),
 ('trung_cap','moc'):('Xuân Phong Hóa Vũ Công','Nội Công / Tâm Pháp'),
 ('trung_cap','thuy'):('Phi Yến Đạp Thủy','Thân Pháp / Khinh Công'),
 ('trung_cap','hoa'):('Viêm Long Phá Thiên Quyền','Quyền Pháp / Ngoại Công'),
 ('trung_cap','tho'):('Bàn Thạch Hộ Thể Công','Hộ Thể / Ngoại Công'),
 ('trung_cap','all'):('Ngũ Hành Luân Chuyển Thủ','Tạp Kỹ / Thực Dụng'),
 ('thuong_thua','kim'):('Thái Bạch Kinh Thiên Kiếm','Kiếm Pháp / Ngoại Công'),
 ('thuong_thua','moc'):('Cửu Mộc Trường Sinh Kinh','Nội Công / Tâm Pháp'),
 ('thuong_thua','thuy'):('Huyền Băng Hàn Ảnh Bộ','Thân Pháp / Khinh Công'),
 ('thuong_thua','hoa'):('Chu Tước Phần Thiên Chưởng','Chưởng Pháp / Ngoại Công'),
 ('thuong_thua','tho'):('Trấn Nhạc Kim Thân Quyết','Hộ Thể / Ngoại Công'),
 ('thuong_thua','all'):('Âm Dương Liên Hoàn Thủ','Tạp Kỹ / Cầm Nã'),
 ('than_cong','kim'):('Tru Tiên Kiếm Trận','Kiếm Pháp / Ngoại Công'),
 ('than_cong','moc'):('Thanh Đế Trường Xuân Công','Nội Công / Tâm Pháp'),
 ('than_cong','thuy'):('Huyền Minh Vô Lượng Bộ','Thân Pháp / Khinh Công'),
 ('than_cong','hoa'):('Kim Ô Đại Nhật Quyền','Quyền Pháp / Ngoại Công'),
 ('than_cong','tho'):('Hỗn Độn Kim Thân Quyết','Hộ Thể / Ngoại Công'),
 ('than_cong','all'):('Thiên Thủ Vạn Tượng','Tạp Kỹ / Thực Dụng'),
 ('tien_dao','kim'):('Thái Thượng Trảm Đạo Kiếm','Kiếm Pháp / Tiên Đạo'),
 ('tien_dao','moc'):('Kiến Mộc Thông Thiên Kinh','Nội Công / Tiên Đạo'),
 ('tien_dao','thuy'):('Thiên Hà Đảo Quyển Bộ','Thân Pháp / Tiên Đạo'),
 ('tien_dao','hoa'):('Cửu Thiên Huyền Hỏa Quyết','Quyền Pháp / Tiên Đạo'),
 ('tien_dao','tho'):('Bàn Cổ Khai Thiên Thể','Hộ Thể / Tiên Đạo'),
 ('tien_dao','all'):('Hồng Mông Tạo Hóa Công','Tạp Kỹ / Tiên Đạo'),
}
ELEMS = ['kim', 'moc', 'thuy', 'hoa', 'tho', 'all']

def wugong_new(start_id=40):
    out, wid = [], start_id
    for pi, (pk, plabel, realm, budget, tmult, plt, pmt, color) in enumerate(PHAM):
        for el in ELEMS:
            f = PROFILE[el]
            gj, fy, hp10, bj, xx = [int(round(budget * r)) for r in f]
            if el == 'all':
                et, ev = EFFECT_ALL[pi]
            else:
                et, ev = EFFECT[el][0], EFFECT[el][1][pi]
            name, kind = NAMES[(pk, el)]
            out.append(dict(wgid=wid, name=name, kind=kind, rarity=pk, rlabel=plabel, element=el, category=CAT[el],
                            purpose=PURPOSE[el], gj=gj, fy=fy, hp=hp10 * 10, bj=bj, xx=xx, effect=et, value=ev,
                            realm=realm, tmult=tmult, lt=plt, mt=pmt, color=color))
            wid += 1
    return out

# ---- Công pháp: giữ hệ Giai × Phẩm có sẵn, thêm 4 bộ / giai ----
GIAI = {1: 'Hoàng Giai', 2: 'Huyền Giai', 3: 'Địa Giai', 4: 'Thiên Giai', 5: 'Tiên Giai', 6: 'Thần Giai', 7: 'Thánh Giai'}
CP_NEW = [  # giai, phẩm(subtier), tên, hệ, effect, base, max, realm, quality, giá LT, giá MT, loại
 (1,1,'Liệt Hỏa Tâm Kinh','hoa','atk_bonus',3,20,0,1,1000,0,'Sát Pháp'),
 (1,2,'Thủy Kính Hộ Tâm Pháp','thuy','def_bonus',3,20,0,1,1000,0,'Hộ Thể'),
 (1,3,'Thảo Mộc Dưỡng Sinh Thuật','moc','heal_bonus',4,22,0,1,1200,0,'Nội Công'),
 (1,4,'Kim Thạch Tụ Tài Quyết','kim','stone_bonus',4,22,0,1,1200,0,'Huyền Công'),
 (2,1,'Canh Kim Duệ Khí Quyết','kim','atk_bonus',4,24,3,2,2200,0,'Sát Pháp'),
 (2,2,'Hỏa Nha Sát Quyết','hoa','crit_dmg',4,24,3,2,2500,0,'Sát Pháp'),
 (2,3,'Thủy Nguyệt Tầm Bảo Pháp','thuy','drop_rate',4,25,4,2,2800,0,'Tâm Pháp'),
 (2,4,'Hậu Thổ Dưỡng Nguyên Công','tho','hp_regen',4,25,5,2,2800,0,'Nội Công'),
 (3,1,'Huyền Thủy Giáp Pháp','thuy','def_bonus',5,30,7,2,0,900,'Hộ Thể'),
 (3,2,'Địa Mạch Hộ Thân Quyết','tho','dmg_reduce',4,24,8,2,0,1000,'Hộ Thể'),
 (3,3,'Mộc Linh Ngộ Đạo Kinh','moc','exp_bonus',5,32,9,2,0,1100,'Tâm Pháp'),
 (3,4,'Hỏa Luyện Kim Thạch Quyết','hoa','stone_bonus',6,35,10,2,0,1200,'Huyền Công'),
 (4,1,'Thiên Cương Phá Quân Quyết','kim','atk_bonus',6,32,12,3,0,2200,'Sát Pháp'),
 (4,2,'Vạn Mộc Tầm Linh Pháp','moc','drop_rate',6,34,14,3,0,2600,'Tâm Pháp'),
 (4,3,'Thủy Linh Hồi Xuân Quyết','thuy','heal_bonus',7,36,16,3,0,3000,'Nội Công'),
 (4,4,'Thiên Diễn Ngộ Đạo Thuật','all','exp_bonus',7,40,18,3,0,3500,'Thiên Thư'),
 (5,1,'Tiên Thủy Linh Lung Giáp','thuy','def_bonus',8,42,18,4,0,16000,'Hộ Thể'),
 (5,2,'Tiên Hỏa Phần Tâm Quyết','hoa','crit_dmg',8,40,20,4,0,19000,'Sát Pháp'),
 (5,3,'Kim Tiên Bất Hoại Quyết','kim','dmg_reduce',7,36,22,4,0,21000,'Hộ Thể'),
 (5,4,'Tiên Mộc Thông Huyền Kinh','moc','exp_bonus',9,45,24,4,0,24000,'Tâm Pháp'),
 (6,1,'Thần Kiếm Tru Ma Quyết','kim','crit_dmg',10,48,25,5,0,38000,'Kiếm Pháp'),
 (6,2,'Thần Mộc Bất Diệt Kinh','moc','hp_regen',10,50,28,5,0,42000,'Nội Công'),
 (6,3,'Thần Hỏa Tầm Bảo Quyết','hoa','drop_rate',11,50,30,5,0,48000,'Tâm Pháp'),
 (6,4,'Thần Thủy Huyền Minh Công','thuy','dmg_reduce',9,44,32,5,0,55000,'Huyền Thuật'),
 (7,1,'Thánh Hỏa Diệt Thế Quyết','hoa','atk_bonus',12,55,35,5,0,75000,'Sát Pháp'),
 (7,2,'Thánh Sơn Trấn Ngục Kinh','tho','def_bonus',12,55,38,5,0,90000,'Hộ Thể'),
 (7,3,'Thánh Mộc Hồi Thiên Kinh','moc','hp_regen',12,55,40,5,0,105000,'Nội Công'),
 (7,4,'Thánh Thủy Tụ Bảo Quyết','thuy','stone_bonus',12,55,43,5,0,125000,'Huyền Công'),
]

if __name__ == '__main__':
    print('### Võ công mới (30 bộ)\n')
    print('| wgid | Tên | Phẩm | Hệ | Loại | Công/Thủ/HP/Bạo/Hút mỗi cấp | Hiệu ứng | Yêu cầu CG | Giá |')
    print('|---|---|---|---|---|---|---|---|---|')
    for w in wugong_new():
        price = ('%s LT' % format(w['lt'], ',')) if w['lt'] else (('%s MT' % format(w['mt'], ',')) if w['mt'] else 'Rơi từ Boss')
        print('| %d | %s | %s | %s | %s | %d / %d / %d / %d / %d | %s %d | %d | %s |' % (
            w['wgid'], w['name'], w['rlabel'], w['element'], w['kind'], w['gj'], w['fy'], w['hp'], w['bj'], w['xx'],
            w['effect'], w['value'], w['realm'], price))
    print('\n### Công pháp mới (28 bộ, 4 bộ / giai)\n')
    print('| cpid | Tên | Giai · Phẩm | Hệ | Hiệu ứng (Lv1 → Lv10) | Yêu cầu CG | Giá |')
    print('|---|---|---|---|---|---|---|')
    pn = {1: 'Sơ', 2: 'Trung', 3: 'Cao', 4: 'Thượng'}
    for i, c in enumerate(CP_NEW):
        g, p, n, el, et, b, m, r, q, lt, mt, srt = c
        price = ('%s LT' % format(lt, ',')) if lt else '%s MT' % format(mt, ',')
        print('| %d | %s | %s · %s Phẩm | %s | %s %d%% → %d%% | %d | %s |' % (29 + i, n, GIAI[g], pn[p], el, et, b, m, r, price))
