# -*- coding: utf-8 -*-
"""
Làn 5 (cấp 2001 -> 2500) — docs/LAN5_CAP_2000_2500.md. Cùng công thức làn 2–4 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 2000 (phẩm Thái Sơ).
10 khu (qy 67–76) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 170 quái mã 810–979, 1 boss/khu (2050 … 2500).
Chạy: python3 tools/gen/lan5_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2
import lan3_design as L3
import lan4_design as L4

QY_START = 67
GYID_START = 810
LV_START = 2000

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Tiên Vương Cương Vực', ('kim', 'tho'),
  ['Cương Vực Biên Ải', 'Phong Hỏa Đài', 'Tiên Vương Trường Thành', 'Thiết Kỵ Bình Nguyên', 'Chiến Tượng Doanh', 'Cương Vực Phong Tháp', 'Vương Kỳ Sơn Lĩnh', 'Tiên Vương Điểm Tướng Đài'],
  ('Tiên Vương Chiến Tượng', 'tho'), 'Cương Vực Thiết Kỵ Thống Lĩnh'),
 ('Vô Thượng Tiên Đình', ('kim', 'hoa'),
  ['Tiên Đình Ngoại Môn', 'Thiên Tướng Phủ', 'Lăng Tiêu Hành Lang', 'Thiên Binh Giáo Trường', 'Thông Minh Điện', 'Tiên Đình Thiên Lao', 'Bích Du Cung', 'Vô Thượng Tiên Điện'],
  ('Vô Thượng Thiên Tướng', 'kim'), 'Tiên Đình Thiên Binh Thống Lĩnh'),
 ('Chuẩn Đế Tiên Lộ', ('hoa', 'none'),
  ['Tiên Lộ Khởi Nguyên', 'Đế Hỏa Sơn Đạo', 'Kim Ô Sào Huyệt', 'Phù Tang Thần Thụ', 'Liệt Nhật Bình Nguyên', 'Tiên Lộ Đoạn Nhai', 'Đế Hỏa Tế Đài', 'Chuẩn Đế Đăng Thiên Thê'],
  ('Chuẩn Đế Kim Ô', 'hoa'), 'Tiên Lộ Hỏa Ô Vương'),
 ('Tiên Đế Hoàng Thành', ('kim', 'none'),
  ['Hoàng Thành Ngoại Quách', 'Chu Tước Đại Nhai', 'Ngự Thư Phòng', 'Hoàng Thành Cấm Vệ Doanh', 'Kim Loan Điện Tiền', 'Thái Miếu', 'Hoàng Cung Hậu Hoa Viên', 'Tiên Đế Kim Loan Điện'],
  ('Tiên Đế Cấm Vệ Thống Lĩnh', 'kim'), 'Hoàng Thành Ngự Tiền Đới Đao'),
 ('Vô Thượng Đế Khư', ('none', 'tho'),
  ['Đế Khư Tàn Môn', 'Phế Đế Đô', 'Đế Huyết Khe Nứt', 'Cổ Ma Phong Ấn Đài', 'Đế Cốt Lâm', 'Vạn Ma Quật', 'Đế Khư Hắc Uyên', 'Cổ Ma Trấn Áp Điện'],
  ('Đế Khư Cổ Ma', 'none'), 'Đế Khư Ma Tướng'),
 ('Bất Hủ Tiên Mộ', ('none', 'thuy'),
  ['Tiên Mộ Thần Đạo', 'Bất Hủ Bi Đình', 'Âm Hà Độ Khẩu', 'Thi Đế Tùy Táng Hố', 'Bất Hủ Quan Tài Điện', 'U Minh Hồi Lang', 'Tiên Mộ Địa Cung', 'Bất Hủ Đế Lăng'],
  ('Bất Hủ Thi Đế', 'none'), 'Tiên Mộ Thi Vương'),
 ('Luyện Thần Lô', ('hoa', 'kim'),
  ['Thần Lô Khẩu', 'Địa Hỏa Mạch', 'Luyện Khí Đỉnh', 'Thần Hỏa Hồ', 'Đan Hỏa Hành Lang', 'Lô Đỉnh Phong Nhãn', 'Thần Lô Hạch Tâm', 'Luyện Thần Hỏa Điện'],
  ('Luyện Thần Hỏa Linh', 'hoa'), 'Thần Lô Hỏa Tinh Vương'),
 ('Nhập Thần Thiên Môn', ('kim', 'thuy'),
  ['Thiên Môn Vân Bộ', 'Nhập Thần Kiều', 'Thần Môn Thạch Trụ', 'Thiên Hà Thủy Môn', 'Thần Thú Viên', 'Thiên Môn Thủ Quan Đài', 'Thần Quang Lộ', 'Nhập Thần Chính Môn'],
  ('Thiên Môn Thần Thú', 'kim'), 'Thiên Môn Thủ Quan Thần Tướng'),
 ('Thần Đạo Thánh Vực', ('moc', 'kim'),
  ['Thánh Vực Ngoại Hoàn', 'Thần Đạo Bi Lâm', 'Thần Thụ Hải', 'Pháp Tướng Sơn', 'Thần Đạo Giảng Đàn', 'Thánh Vực Linh Tuyền', 'Đạo Văn Thạch Bích', 'Thần Đạo Thánh Đài'],
  ('Thần Đạo Pháp Tướng', 'moc'), 'Thánh Vực Pháp Tướng Hộ Vệ'),
 ('Thần Tôn Thần Điện', ('tho', 'kim'),
  ['Thần Điện Bậc Thềm', 'Thần Long Trụ Hành Lang', 'Tế Thần Quảng Trường', 'Thần Tôn Bảo Khố', 'Long Mạch Thần Trì', 'Thần Điện Hậu Viện', 'Thần Tôn Thư Các', 'Thần Tôn Chính Điện'],
  ('Thần Tôn Hộ Điện Thần Long', 'tho'), 'Thần Điện Long Vệ'),
]

def monsters():
    used = set(m['name'] for mod in (L2, L3, L4) for m in mod.monsters())   # tránh trùng tên các làn trước
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    for L in (2000, 2002, 2100, 2250, 2400, 2500):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('Quái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s · %.0f đòn / %.0f đòn · rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), bh, bg, b['zb'], b['dj']))
