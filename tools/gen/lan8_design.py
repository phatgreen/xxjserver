# -*- coding: utf-8 -*-
"""
Làn 8 (cấp 3501 -> 4000) — docs/LAN8_CAP_3500_4000.md. Cùng công thức làn 2–7 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 3500 (phẩm Chí Tôn).
10 khu (qy 97–106) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 170 quái mã 1320–1489, 1 boss/khu (3550 … 4000).
Chạy: python3 tools/gen/lan8_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2
import lan3_design as L3
import lan4_design as L4
import lan5_design as L5
import lan6_design as L6
import lan7_design as L7

QY_START = 97
GYID_START = 1320
LV_START = 3500

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Ngân Hà Tận Đầu', ('thuy', 'kim'),
  ['Tận Đầu Bến Sao', 'Ngân Lưu Thác Đổ', 'Tinh Sa Bãi Bồi', 'Long Ngâm Hà Khúc', 'Băng Tinh Hà Đạo', 'Cổ Long Hài Cốt', 'Đoạn Lưu Hà Cốc', 'Cự Long Thủ Hộ Đàm'],
  ('Ngân Hà Thủ Hộ Cự Long', 'thuy'), 'Tận Đầu Ngân Lân Long Vệ'),
 ('Chu Thiên Viên Mãn Đài', ('kim', 'none'),
  ['Viên Mãn Đài Cơ', 'Tinh Hoàng Ngự Đạo', 'Luân Tọa Tinh Bàn', 'Quy Vị Tinh Các', 'Vạn Tượng Hồi Lang', 'Thiên Cương Trận Bích', 'Hoàng Tọa Tinh Đài', 'Chí Tôn Viên Mãn Đàn'],
  ('Chu Thiên Viên Mãn Tinh Hoàng', 'kim'), 'Viên Mãn Đài Tinh Hoàng Cận Vệ'),
 ('Thần Mệnh Thiên Thư Các', ('none', 'moc'),
  ['Thiên Thư Ngoại Viện', 'Mệnh Bạ Hành Lang', 'Chấp Bút Thư Phòng', 'Vận Số Kinh Lâu', 'Nhân Quả Bia Lâm', 'Thần Mệnh Ấn Đường', 'Cổ Quyển Mật Thất', 'Bí Lục Chủ Điện'],
  ('Thần Mệnh Chấp Bút Thần Quân', 'none'), 'Thiên Thư Các Hộ Quyển Thần Tướng'),
 ('Sáng Thế Thần Quốc', ('tho', 'kim'),
  ['Thần Quốc Biên Thành', 'Cự Thần Quan Ải', 'Thần Dân Phố Thị', 'Tạo Hóa Quảng Trường', 'Hoàng Lộ Thần Quốc', 'Luyện Binh Cự Đài', 'Nội Thành Thần Cung', 'Thủ Hộ Cự Điện'],
  ('Thần Quốc Thủ Hộ Cự Thần', 'tho'), 'Thần Quốc Thạch Cự Thần Vệ'),
 ('Vận Mệnh Trường Hà', ('thuy', 'none'),
  ['Trường Hà Thượng Nguồn', 'Quá Khứ Nhánh Sông', 'Hiện Tại Bãi Cạn', 'Tương Lai Hà Khẩu', 'Thần Kình Hồi Lưu', 'Vạn Mệnh Lốc Xoáy', 'Hà Để Thâm Uyên', 'Kình Miên Thần Đàm'],
  ('Vận Mệnh Trường Hà Thần Kình', 'thuy'), 'Trường Hà Vận Mệnh Kình Vệ'),
 ('Nhập Thánh Lộ', ('kim', 'moc'),
  ['Thánh Lộ Khởi Bước', 'Vấn Tâm Thạch Giai', 'Phá Chấp Kiều', 'Thánh Huy Lâm', 'Tâm Ma Khảo Đài', 'Đoạn Trần Nhai', 'Bán Sơn Thánh Đình', 'Siêu Phàm Thánh Môn'],
  ('Thánh Lộ Thủ Quan Thánh Linh', 'kim'), 'Thánh Lộ Thánh Quang Hộ Vệ'),
 ('Chuẩn Thánh Thí Luyện Tháp', ('hoa', 'tho'),
  ['Tháp Hạ Quảng Trường', 'Nhất Trọng Thí Luyện', 'Tam Trọng Hỏa Ngục', 'Ngũ Trọng Kiếm Trận', 'Thất Trọng Huyễn Cảnh', 'Cửu Trọng Lôi Ngục', 'Thập Nhị Trọng Đài', 'Tháp Đỉnh Thánh Đài'],
  ('Thí Luyện Tháp Chủ', 'hoa'), 'Thí Luyện Tháp Thủ Tầng Tướng'),
 ('Hỗn Độn Thánh Cảnh', ('none', 'thuy'),
  ['Thánh Cảnh Hỗn Mang', 'Lục Túc Sơn Khâu', 'Tứ Dực Vân Hải', 'Vô Diện Linh Trì', 'Đế Giang Ca Vũ Đài', 'Khí Hỗn Nguyên Cốc', 'Huyền Vụ Thánh Lâm', 'Thánh Sào Hỗn Mang'],
  ('Hỗn Độn Thánh Thú Đế Giang', 'none'), 'Thánh Cảnh Hỗn Độn Thú Vương'),
 ('Hư Vô Thánh Vực', ('none', 'hoa'),
  ['Hư Môn Thánh Vực', 'Vô Tướng Hành Lang', 'Tịch Diệt Sa Mạc', 'Thôn Thiên Huyết Khẩu', 'Ma Thánh Tế Đài', 'Không Vô Liệt Cốc', 'Tịch Ảnh U Điện', 'Phệ Thiên Ma Điện'],
  ('Hư Vô Thôn Thiên Ma Thánh', 'none'), 'Thánh Vực Hư Vô Ma Vệ'),
 ('Vô Thượng Thánh Điện', ('kim', 'tho'),
  ['Thánh Điện Ngọc Giai', 'Hộ Pháp Thần Đạo', 'Thánh Nhân Giảng Đường', 'Triều Bái Vạn Thánh Đài', 'Kim Huy Hành Lang', 'Thánh Tướng Diễn Pháp Trường', 'Nội Đình Thánh Viện', 'Vô Thượng Thánh Tọa'],
  ('Thánh Điện Hộ Pháp Thánh Tướng', 'kim'), 'Thánh Điện Kim Giáp Hộ Pháp'),
]

def monsters():
    used = set(m['name'] for mod in (L2, L3, L4, L5, L6, L7) for m in mod.monsters())   # tránh trùng tên các làn trước
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    for L in (3500, 3502, 3600, 3750, 3900, 4000):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('Quái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s · %.0f đòn / %.0f đòn · rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), bh, bg, b['zb'], b['dj']))
