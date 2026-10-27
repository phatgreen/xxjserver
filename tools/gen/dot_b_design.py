# -*- coding: utf-8 -*-
"""
Thiết kế đợt B — quái theo cảnh giới tới cấp 500.
Chỉ số quái theo công thức khớp từ 87 quái cũ (cấp 10-105, bỏ quái đặc biệt):
    HP  = 9.250 * lv^1.652      Công = 0.461 * lv^1.745      Thủ = 3.704 * lv^1.180
Chạy: python3 tools/gen/dot_b_design.py  -> in bảng markdown.
"""
import json, os

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

def base_stats(lv):
    return 9.25 * lv ** 1.652, 0.461 * lv ** 1.745, 3.704 * lv ** 1.18

# Kiểu quái: hệ số (HP, Công, Thủ, bạo, hút, tỉ lệ rơi đồ)
ARCH = {
    'thuong': ('Thường',   1.00, 1.00, 1.00, 5,  5,  20),
    'giap':   ('Giáp',     1.20, 0.85, 1.30, 3,  3,  20),
    'cuong':  ('Cuồng',    0.85, 1.25, 0.80, 10, 5,  20),
    'tinh':   ('Tinh Anh', 2.00, 1.20, 1.10, 10, 10, 32),
}

# Trang bị rơi theo bậc (zblv) — chọn 5 món của bậc ≤ cấp quái
ZB_TIER = [(400, [23, 48, 68, 88, 103]), (300, [22, 47, 67, 87, 102]), (200, [21, 46, 66, 86, 101]),
           (120, [5, 10, 15, 20, 30]), (60, [4, 9, 14, 19, 29])]
def zb_for(lv):
    for t, ids in ZB_TIER:
        if lv >= t: return ','.join(str(i) for i in ids)
    return '4,9,14,19,29'
def yp_for(lv):  # 9 Phục Thương Đan · 1 Bách Niên Tiên Đào · 2 Thiên Niên Nhân Tham
    return '9' if lv < 150 else ('1' if lv < 300 else '2')
def dj_for(lv):  # 1 Cường Hóa Thạch · 20 Trúc Cơ Đột Phá Đan · 21 Kim Đan Đột Phá Đan
    if 330 <= lv < 400: return '1,20'
    if 400 <= lv <= 500: return '1,21'
    return '1'

# Khu vực: (qyid, tên, mới?, dải cấp, [(mid, tên map, [(tên quái, hệ, kiểu, cấp, số lượng)])], boss)
# mid = None => map mới (tạo ở migration).
REGIONS = [
 (27, 'Tây Lương Địa', False, '78–92', [
   (333, 'Đăng Vân Lộc',          [('Sa Mạc Hạt Vương', 'tho', 'giap', 78, 3)]),
   (338, 'Hà Nguyên',             [('Hà Nguyên Thủy Quái', 'thuy', 'thuong', 80, 3)]),
   (335, 'Lục La Chướng',         [('Lục La Độc Chướng Xà', 'moc', 'cuong', 82, 3)]),
   (337, 'Thắng Châu',            [('Thắng Châu Loạn Binh', 'none', 'thuong', 84, 3)]),
   (340, 'Thạch Thôn',            [('Thạch Thôn Nham Khôi', 'tho', 'giap', 86, 3)]),
   (339, 'Dương Danh Bách Nạp',   [('Bách Nạp Hồ Tiên', 'hoa', 'cuong', 88, 3)]),
   (336, 'Thục Sơn Ngoại Thành',  [('Thục Sơn Phản Đồ', 'kim', 'thuong', 90, 2), ('Thục Sơn Kiếm Ma', 'kim', 'tinh', 91, 1)]),
  ], ('Tây Lương Sa Vương', 'tho', 92, 336)),
 (28, 'Hoang Thiên Lý', False, '106–150', [
   (341, 'Song Khê',              [('Song Khê Thủy Mãng', 'thuy', 'thuong', 106, 3)]),
   (342, 'Thục Sơn Cố Đạo',       [('Cố Đạo U Hồn', 'none', 'cuong', 110, 3)]),
   (343, 'Tỏa Yêu Tháp',          [('Tỏa Yêu Tháp Thủ Vệ', 'kim', 'giap', 114, 3)]),
   (345, 'Thạch Trầm Khê Động',   [('Khê Động Thạch Long', 'tho', 'giap', 118, 3)]),
   (347, 'Sào Hồ',                [('Sào Hồ Độc Phong', 'moc', 'cuong', 122, 3)]),
   (346, 'Tử Vân Giá',            [('Tử Vân Lôi Điểu', 'hoa', 'thuong', 126, 3)]),
   (348, 'Bát Công Sơn',          [('Bát Công Sơn Yêu Viên', 'moc', 'thuong', 132, 3)]),
   (349, 'Nữ La Nham',            [('Nữ La Huyết Yêu', 'hoa', 'cuong', 138, 3)]),
   (344, 'Bàn Cổ Chi Tâm',        [('Bàn Cổ Thạch Linh', 'tho', 'giap', 144, 3)]),
   (350, 'Hoài Nam Vương Lăng',   [('Vương Lăng Thi Tướng', 'none', 'thuong', 147, 2), ('Vương Lăng Quỷ Vệ', 'none', 'tinh', 149, 1)]),
  ], ('Hoài Nam Vương', 'none', 150, 350)),
 (30, 'Hồi Đầu Ngạn', False, '152–200', [
   (351, 'Oản Khâu Sơn',          [('Oản Khâu Sơn Quỷ', 'none', 'thuong', 152, 3)]),
   (352, 'Thiên Phật Tháp',       [('Thiên Phật Tháp Tà Tăng', 'kim', 'giap', 158, 3)]),
   (353, 'Tử Vi Đạo',             [('Tử Vi Tinh Quái', 'hoa', 'cuong', 164, 3)]),
   (354, 'Bạch Hạo Đạo',          [('Bạch Hạo Băng Lang', 'thuy', 'thuong', 170, 3)]),
   (355, 'Tịch Huyền Đạo',        [('Tịch Huyền Ảnh Yêu', 'none', 'cuong', 176, 3)]),
   (356, 'Nguyệt Nha Hà Cốc',     [('Nguyệt Nha Hà Giao', 'thuy', 'giap', 182, 3)]),
   (358, 'Thanh Phong Giản',      [('Thanh Phong Kiếm Linh', 'kim', 'cuong', 188, 3)]),
   (357, 'Túy Hoa Ấm',            [('Túy Hoa Mị Yêu', 'moc', 'thuong', 194, 3)]),
   (359, 'Tiên Hồ Cư',            [('Tiên Hồ Cư Hỏa Hồ', 'hoa', 'thuong', 197, 2), ('Cửu Vĩ Tiên Hồ', 'hoa', 'tinh', 199, 1)]),
  ], ('Bỉ Ngạn Minh Vương', 'none', 200, 359)),
 (31, 'Hồng Hoang Cổ Địa', False, '202–250', [
   (360, 'Viêm Đế Thần Nông Động',[('Thần Nông Dược Linh', 'moc', 'thuong', 202, 3)]),
   (361, 'Bách Linh Châu',        [('Bách Linh Dị Thú', 'none', 'cuong', 208, 3)]),
   (362, 'Bất Chu Sơn',           [('Bất Chu Sơn Cự Nhân', 'tho', 'giap', 214, 3)]),
   (363, 'Bàn Long Trấn Trụ',     [('Bàn Long Thủ Trụ', 'kim', 'giap', 220, 3)]),
   (364, 'Vô Thường Điện',        [('Vô Thường Câu Hồn Sứ', 'none', 'cuong', 226, 3)]),
   (365, 'Hoàng Tuyền Quỷ Đạo',   [('Hoàng Tuyền Quỷ Binh', 'thuy', 'thuong', 232, 3)]),
   (366, 'Phong Thần Lăng',       [('Phong Thần Hộ Lăng Vệ', 'kim', 'thuong', 238, 3)]),
   (367, 'Toàn Mộng',             [('Toàn Mộng Huyễn Yêu', 'moc', 'cuong', 244, 3)]),
   (368, 'Quỳnh Hoa Phái',        [('Quỳnh Hoa Phản Đồ', 'hoa', 'thuong', 247, 2), ('Quỳnh Hoa Kiếm Quỷ', 'hoa', 'tinh', 249, 1)]),
  ], ('Cộng Công Tàn Hồn', 'thuy', 250, 368)),
 (None, 'Bắc Minh Hải Vực', True, '252–300', [
   (None, 'Bắc Minh Hải Khẩu',    [('Bắc Minh Hải Xà', 'thuy', 'thuong', 252, 3)]),
   (None, 'Băng Hải Tiều Thạch',  [('Băng Hải Giải Tướng', 'thuy', 'giap', 258, 3)]),
   (None, 'Côn Bằng Sào',         [('Côn Bằng Ấu Điểu', 'none', 'cuong', 264, 3)]),
   (None, 'Hắc Thủy Uyên',        [('Hắc Thủy Huyền Quy', 'thuy', 'giap', 270, 3)]),
   (None, 'Bắc Minh Long Cung',   [('Long Cung Dạ Xoa', 'thuy', 'cuong', 276, 3)]),
   (None, 'Hải Nhãn Tuyền',       [('Hải Nhãn Thủy Mẫu', 'thuy', 'thuong', 282, 3)]),
   (None, 'Huyền Băng Đảo',       [('Huyền Băng Hải Yêu', 'thuy', 'thuong', 288, 3)]),
   (None, 'Bắc Minh Thâm Uyên',   [('Thâm Uyên Cự Kình', 'thuy', 'giap', 294, 2), ('Thâm Uyên Hải Long', 'thuy', 'tinh', 298, 1)]),
  ], ('Bắc Minh Côn Vương', 'thuy', 300, -1)),
 (None, 'Côn Lôn Tiên Sơn', True, '302–350', [
   (None, 'Côn Lôn Sơn Môn',      [('Côn Lôn Thủ Sơn Thú', 'tho', 'giap', 302, 3)]),
   (None, 'Ngọc Hư Phong',        [('Ngọc Hư Kiếm Khôi', 'kim', 'thuong', 308, 3)]),
   (None, 'Dao Trì',              [('Dao Trì Tiên Hạc', 'none', 'cuong', 314, 3)]),
   (None, 'Kỳ Lân Nhai',          [('Kỳ Lân Ấu Thú', 'hoa', 'thuong', 320, 3)]),
   (None, 'Ngọc Châu Phong',      [('Ngọc Châu Thạch Tinh', 'tho', 'giap', 326, 3)]),
   (None, 'Tuyết Liên Cốc',       [('Tuyết Liên Băng Linh', 'thuy', 'cuong', 332, 3)]),
   (None, 'Thiên Trụ Sơn',        [('Thiên Trụ Cự Viên', 'tho', 'thuong', 338, 3)]),
   (None, 'Côn Lôn Đỉnh',         [('Côn Lôn Kim Giáp Vệ', 'kim', 'giap', 344, 2), ('Côn Lôn Kiếm Thánh Tàn Ảnh', 'kim', 'tinh', 348, 1)]),
  ], ('Côn Lôn Kiếm Tôn', 'kim', 350, -1)),
 (None, 'Vạn Yêu Lâm', True, '352–400', [
   (None, 'Vạn Yêu Lâm Ngoại Vi', [('Vạn Yêu Lâm Lang Vương', 'moc', 'thuong', 352, 3)]),
   (None, 'Độc Chướng Đầm',       [('Độc Chướng Thiềm Thừ', 'moc', 'giap', 358, 3)]),
   (None, 'Cổ Thụ Yêu Thành',     [('Cổ Thụ Yêu Tinh', 'moc', 'thuong', 364, 3)]),
   (None, 'Huyết Đằng Cốc',       [('Huyết Đằng Thực Nhân Hoa', 'moc', 'cuong', 370, 3)]),
   (None, 'Bách Hoa Yêu Cốc',     [('Bách Hoa Điệp Yêu', 'hoa', 'cuong', 376, 3)]),
   (None, 'Thanh Khâu Hồ Quốc',   [('Thanh Khâu Hồ Vệ', 'hoa', 'thuong', 382, 3)]),
   (None, 'Yêu Hoàng Điện Ngoại', [('Yêu Hoàng Cấm Vệ', 'none', 'giap', 388, 3)]),
   (None, 'Vạn Yêu Lâm Tâm',      [('Thiên Niên Thụ Yêu', 'moc', 'giap', 394, 2), ('Vạn Yêu Lâm Thủ Hộ Giả', 'moc', 'tinh', 398, 1)]),
  ], ('Vạn Yêu Hoàng', 'moc', 400, -1)),
 (None, 'Cửu U Ma Vực', True, '402–450', [
   (None, 'Ma Vực Nhập Khẩu',     [('Ma Vực Tuần Binh', 'none', 'thuong', 402, 3)]),
   (None, 'Hắc Phong Hiệp',       [('Hắc Phong Ma Ưng', 'none', 'cuong', 408, 3)]),
   (None, 'Huyết Trì',            [('Huyết Trì Ma Giao', 'thuy', 'thuong', 414, 3)]),
   (None, 'Cốt Hải',              [('Cốt Hải Bạch Cốt Tướng', 'tho', 'giap', 420, 3)]),
   (None, 'Ma Viêm Cốc',          [('Ma Viêm Hỏa Kỳ Lân', 'hoa', 'cuong', 426, 3)]),
   (None, 'U Minh Phủ',           [('U Minh Quỷ Tướng', 'none', 'thuong', 432, 3)]),
   (None, 'Thiên Ma Tế Đàn',      [('Thiên Ma Tế Ti', 'hoa', 'cuong', 438, 3)]),
   (None, 'Cửu U Ma Uyên',        [('Cửu U Ma Long', 'none', 'giap', 444, 2), ('Cửu U Ma Tướng', 'none', 'tinh', 448, 1)]),
  ], ('Cửu U Ma Tôn', 'none', 450, -1)),
 (None, 'Thiên Ngoại Thiên', True, '452–500', [
   (None, 'Thiên Môn',            [('Thiên Môn Thần Tướng', 'kim', 'giap', 452, 3)]),
   (None, 'Tinh Hà Đạo',          [('Tinh Hà Tinh Thú', 'none', 'thuong', 458, 3)]),
   (None, 'Lôi Kiếp Đài',         [('Lôi Kiếp Lôi Linh', 'hoa', 'cuong', 464, 3)]),
   (None, 'Tử Vi Tinh Cung',      [('Tử Vi Tinh Quân Vệ', 'kim', 'thuong', 470, 3)]),
   (None, 'Hỗn Độn Hải',          [('Hỗn Độn Cự Thú', 'tho', 'giap', 476, 3)]),
   (None, 'Thái Hư Cảnh',         [('Thái Hư Kiếm Linh', 'kim', 'cuong', 482, 3)]),
   (None, 'Cửu Thiên Huyền Cung', [('Huyền Cung Thiên Binh', 'thuy', 'thuong', 488, 3)]),
   (None, 'Thiên Ngoại Thiên Đỉnh',[('Thiên Ngoại Tà Thần', 'none', 'giap', 494, 2), ('Thiên Ngoại Thần Vệ', 'none', 'tinh', 498, 1)]),
  ], ('Thiên Ngoại Thiên Chủ', 'none', 500, -1)),
]

GYID_START = 200  # id quái mới: 200, 201, ...

def monsters():
    out, gid = [], GYID_START
    for qy, qname, new, band, maps, boss in REGIONS:
        for mid, mname, mobs in maps:
            for name, el, arch, lv, cnt in mobs:
                hp, gj, fy = base_stats(lv)
                a = ARCH[arch]
                out.append(dict(id=gid, name=name, element=el, arch=arch, archname=a[0], lv=lv, count=cnt, region=qname,
                                mid=mid, mname=mname, ghp=int(round(hp * a[1], -1)), ggj=int(round(gj * a[2])),
                                gfy=int(round(fy * a[3])), gbj=a[4], gxx=a[5], dljv=a[6],
                                gzb=zb_for(lv), gdj=dj_for(lv), djjv=5, gyp=yp_for(lv), ypjv=15))
                gid += 1
    return out

def bosses():
    out = []
    for qy, qname, new, band, maps, boss in REGIONS:
        name, el, lv, at = boss
        hp, gj, fy = base_stats(lv)
        out.append(dict(name='Boss·' + name, element=el, lv=lv, region=qname, mid=at,
                        hp=int(round(hp * 12, -2)), gj=int(round(gj * 1.6)), fy=int(round(fy * 1.3)),
                        bj=15, xx=10, zb=zb_for(lv), dj='1,21' if lv >= 400 else ('1,20' if lv >= 300 else '1')))
    return out

if __name__ == '__main__':
    ms = monsters()
    print('### Quái mới (%d loại, mã %d–%d)\n' % (len(ms), ms[0]['id'], ms[-1]['id']))
    cur = None
    for m in ms:
        if m['region'] != cur:
            cur = m['region']
            print('\n**%s**\n' % cur)
            print('| Mã | Quái | Cấp | Hệ | Kiểu | HP | Công | Thủ | Bản đồ | SL |')
            print('|---|---|---|---|---|---|---|---|---|---|')
        print('| %d | %s | %d | %s | %s | %s | %s | %s | %s | %d |' % (m['id'], m['name'], m['lv'], m['element'], m['archname'],
              format(m['ghp'], ','), format(m['ggj'], ','), format(m['gfy'], ','), m['mname'], m['count']))
    print('\n### Boss khu vực (9)\n')
    print('| Boss | Cấp | Hệ | HP | Công | Thủ | Khu |')
    print('|---|---|---|---|---|---|---|')
    for b in bosses():
        print('| %s | %d | %s | %s | %s | %s | %s |' % (b['name'], b['lv'], b['element'], format(b['hp'], ','),
              format(b['gj'], ','), format(b['fy'], ','), b['region']))
