# -*- coding: utf-8 -*-
"""
Làn 7 (cấp 3001 -> 3500) — docs/LAN7_CAP_3000_3500.md. Cùng công thức làn 2–6 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 3000 (phẩm Vĩnh Hằng).
10 khu (qy 87–96) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 170 quái mã 1150–1319, 1 boss/khu (3050 … 3500).
Chạy: python3 tools/gen/lan7_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2
import lan3_design as L3
import lan4_design as L4
import lan5_design as L5
import lan6_design as L6

QY_START = 87
GYID_START = 1150
LV_START = 3000

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Kim Tinh Thiên Lộ', ('kim', 'hoa'),
  ['Dẫn Tinh Khởi Điểm', 'Kim Tinh Thạch Đạo', 'Thôn Nhật Hãm Khanh', 'Lưu Tinh Vẫn Địa', 'Lưu Quang Hành Lang', 'Tinh Thiết Khoáng Mạch', 'Nhật Thực Hắc Cốc', 'Nhật Diệu Thiên Môn'],
  ('Kim Tinh Thôn Nhật Thú', 'kim'), 'Thiên Lộ Kim Tinh Thú Vương'),
 ('Tử Vi Đế Tinh Viên', ('none', 'kim'),
  ['Tử Vi Viên Môn', 'Đế Tinh Hoa Viên', 'Thiên Thị Trường Nhai', 'Đế Ảnh Tinh Các', 'Tử Khí Đông Lai Lâu', 'Bắc Cực Tinh Đài', 'Thái Vi Hành Lang', 'Tử Vi Tinh Quân Điện'],
  ('Tử Vi Tinh Quân Hư Ảnh', 'none'), 'Đế Tinh Viên Ảnh Vệ Thống Lĩnh'),
 ('Băng Tinh Bạch Nguyên', ('thuy', 'kim'),
  ['Bạch Nguyên Tuyết Lộ', 'Băng Tinh Lâm', 'Hàn Sương Hồ', 'Bạch Phượng Sào', 'Vĩnh Đông Băng Xuyên', 'Tuyết Ảnh Hẻm Núi', 'Băng Phách Tinh Động', 'Hàn Phách Băng Điện'],
  ('Băng Tinh Bạch Phượng', 'thuy'), 'Bạch Nguyên Băng Phượng Hộ Vệ'),
 ('Thái Âm Nguyệt Cung', ('thuy', 'moc'),
  ['Nguyệt Cung Ngân Kiều', 'Quế Thụ Lâm', 'Ngọc Thố Đảo Dược Đài', 'Quảng Hàn Hành Lang', 'Nguyệt Lộ Linh Tuyền', 'Nguyệt Hoa Viên', 'Hằng Nga Vọng Nguyệt Lâu', 'Thái Âm Nguyệt Điện'],
  ('Thái Âm Ngọc Thố Thần Thú', 'moc'), 'Nguyệt Cung Ngọc Thố Hộ Pháp'),
 ('Liệt Nhật Kim Ô Cốc', ('hoa', 'tho'),
  ['Xích Dương Cốc Khẩu', 'Phù Mộc Thần Thụ', 'Thang Cốc Nhiệt Tuyền', 'Liệt Dương Sa Mạc', 'Xích Viêm Thạch Lâm', 'Dực Hỏa Thần Sào', 'Thập Nhật Tế Đàn', 'Đại Nhật Thần Điện'],
  ('Liệt Nhật Kim Ô', 'hoa'), 'Kim Ô Cốc Viêm Dực Thống Soái'),
 ('Bắc Đẩu Thất Tinh Đài', ('kim', 'none'),
  ['Thất Tinh Đài Hạ', 'Xu Tinh Thạch Giai', 'Thiên Toàn Thạch Trụ', 'Thiên Cơ Trận Nhãn', 'Thiên Quyền Đài', 'Ngọc Hành Lầu', 'Khai Dương Tháp', 'Phá Quân Tinh Đài'],
  ('Thất Tinh Phá Quân Thần Tướng', 'kim'), 'Thất Tinh Đài Tham Lang Tướng'),
 ('Nhị Thập Bát Tú Lộ', ('moc', 'kim'),
  ['Bát Tú Lộ Khẩu', 'Giác Tú Lâm', 'Cang Tú Sơn Đạo', 'Đê Tú Hồ', 'Phòng Tú Thạch Ốc', 'Tâm Tú Đài', 'Vĩ Tú Hẻm', 'Thanh Long Tú Điện'],
  ('Thanh Long Tú Tinh Thần Thú', 'moc'), 'Bát Tú Lộ Thanh Long Tinh Vệ'),
 ('Tử Khí Tinh Vân', ('none', 'thuy'),
  ['Tinh Vân Ngoại Duyên', 'Tử Vụ Mê Lộ', 'Tinh Trần Hải', 'Cự Mãng Thoát Xác Động', 'Huyễn Quang Tinh Đảo', 'Tử Khí Lốc Xoáy', 'Huyễn Vân Tâm Nhãn', 'Tử Khí Tinh Vân Hạch'],
  ('Tinh Vân Tử Khí Cự Mãng', 'none'), 'Tinh Vân Tử Lân Mãng Vương'),
 ('Hư Không Tinh Nhãn', ('none', 'hoa'),
  ['Tinh Nhãn Biên Giới', 'Hắc Ám Tinh Hà', 'Thôn Tinh Huyệt', 'Tử Tinh Mộ Địa', 'Hư Không Liệt Phùng', 'Ma Tinh Tế Đàn', 'Thôn Phệ Lốc Xoáy', 'Hư Không Tinh Nhãn Tâm'],
  ('Thôn Tinh Ma Chủ', 'none'), 'Tinh Nhãn Thôn Tinh Ma Tướng'),
 ('Chu Thiên Tinh Đấu Trận', ('kim', 'tho'),
  ['Trận Ngoại Sơn Môn', 'Chu Thiên Trận Kỳ', 'Tinh Đấu Trận Lộ', 'Tứ Tượng Trận Nhãn', 'Cửu Cung Bát Quái Đài', 'Trận Linh Hành Lang', 'Vạn Tinh Quy Vị Đài', 'Trận Tâm Chí Cao Đài'],
  ('Tinh Đấu Đại Trận Trận Linh', 'kim'), 'Đại Trận Tinh Đấu Hộ Trận Thần'),
]

def monsters():
    used = set(m['name'] for mod in (L2, L3, L4, L5, L6) for m in mod.monsters())   # tránh trùng tên các làn trước
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    for L in (3000, 3002, 3100, 3250, 3400, 3500):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('Quái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s · %.0f đòn / %.0f đòn · rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), bh, bg, b['zb'], b['dj']))
