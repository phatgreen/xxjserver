# -*- coding: utf-8 -*-
"""
Làn 4 (cấp 1501 -> 2000) — docs/LAN4_CAP_1500_2000.md. Cùng công thức làn 2 / 3 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 1500 (phẩm Thái Sơ).
10 khu (qy 57–66) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 170 quái mã 640–809, 1 boss/khu (1550 … 2000).
Chạy: python3 tools/gen/lan4_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2
import lan3_design as L3

QY_START = 57
GYID_START = 640
LV_START = 1500

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Tiên Thiên Linh Vực', ('moc', 'thuy'),
  ['Linh Vực Sơ Khai', 'Tiên Thiên Linh Tuyền', 'Hồng Mông Thảo Nguyên', 'Linh Bảo Cốc', 'Tiên Thiên Thạch Trận', 'Ngưng Khí Đàm', 'Linh Vực Tâm Hồ', 'Tiên Thiên Linh Mạch'],
  ('Tiên Thiên Linh Bảo Thú', 'moc'), 'Linh Vực Thủ Hộ Linh'),
 ('Thiên Tiên Vân Hải', ('thuy', 'kim'),
  ['Vân Hải Bến Đò', 'Phiêu Miểu Vân Đảo', 'Tường Vân Lộ', 'Vân Trung Tiên Các', 'Ngũ Sắc Vân Lâu', 'Vân Hải Phong Nhãn', 'Bằng Sào Vân Phong', 'Vân Hải Chi Đỉnh'],
  ('Vân Hải Thiên Bằng', 'kim'), 'Vân Hải Bằng Vương'),
 ('Chân Tiên Đạo Trường', ('kim', 'none'),
  ['Đạo Trường Sơn Môn', 'Luận Đạo Bình', 'Kiếm Lâm Đạo Bích', 'Ngộ Đạo Thạch Thất', 'Chân Tiên Giảng Đường', 'Đạo Trường Diễn Kiếm Đài', 'Tàng Kiếm Các', 'Chân Tiên Bảo Điện'],
  ('Đạo Trường Kiếm Linh', 'kim'), 'Đạo Trường Kiếm Thị'),
 ('Huyền Tiên Bí Cảnh', ('none', 'hoa'),
  ['Bí Cảnh Huyễn Môn', 'Thanh Khâu Cổ Lâm', 'Hồ Tiên Động', 'Mê Hồn Trận', 'Bí Cảnh Linh Trì', 'Cửu Vĩ Tế Đàn', 'Huyền Tiên Mộng Cảnh', 'Bí Cảnh Chi Tâm'],
  ('Huyền Tiên Cửu Vĩ Hồ', 'hoa'), 'Bí Cảnh Hồ Vệ'),
 ('Kim Tiên Thánh Địa', ('kim', 'tho'),
  ['Thánh Địa Kim Môn', 'Kim Quang Đại Đạo', 'La Hán Đường', 'Kim Thân Tháp', 'Phật Quang Đài', 'Bồ Đề Lâm', 'Kim Liên Trì', 'Thánh Địa Đại Hùng Điện'],
  ('Kim Tiên Kim Giáp Thần', 'kim'), 'Thánh Địa Kim Cương Vệ'),
 ('Thái Ất Tinh Cung', ('none', 'thuy'),
  ['Tinh Cung Ngoại Viên', 'Chu Thiên Tinh Đạo', 'Bắc Đẩu Thất Tinh Đài', 'Tinh Hà Quan Trắc Lâu', 'Tử Vi Tinh Điện', 'Tinh Thần Đồ Lục Các', 'Thái Ất Tinh Bàn', 'Tinh Cung Chủ Điện'],
  ('Thái Ất Tinh Quân', 'none'), 'Tinh Cung Thất Tinh Sứ'),
 ('Đại La Thiên', ('tho', 'kim'),
  ['Đại La Thiên Ngoại', 'Pháp Tướng Thạch Lâm', 'Bất Diệt Kim Thân Lĩnh', 'Đại La Phong Ấn Địa', 'Vạn Pháp Đài', 'Đại La Hư Không Đạo', 'Pháp Thân Luyện Ngục', 'Đại La Thiên Đình'],
  ('Đại La Pháp Thân', 'tho'), 'Đại La Hộ Pháp'),
 ('Cửu Thiên Huyền Khung', ('hoa', 'none'),
  ['Huyền Khung Cửa Gió', 'Cửu Thiên Cương Phong', 'Huyền Điểu Sào', 'Thiên Hỏa Lưu Tinh', 'Huyền Khung Vân Điện', 'Phượng Minh Đài', 'Cửu Thiên Lôi Vực', 'Huyền Khung Chi Đỉnh'],
  ('Cửu Thiên Huyền Điểu', 'hoa'), 'Huyền Khung Hỏa Điểu'),
 ('Tiên Quân Điện', ('kim', 'moc'),
  ['Điện Tiền Quảng Trường', 'Ngự Lâm Quân Doanh', 'Tiên Quân Thư Phòng', 'Ngọc Bích Hành Lang', 'Tiên Quân Hậu Uyển', 'Chưởng Ấn Các', 'Thiên Binh Diễn Võ Trường', 'Tiên Quân Bảo Tọa'],
  ('Tiên Quân Hộ Điện Thần Tướng', 'kim'), 'Tiên Quân Ngự Tiền Vệ'),
 ('Tiên Tôn Thánh Sơn', ('tho', 'moc'),
  ['Sơn Cước Tiên Trấn', 'Vạn Tiên Bậc Đá', 'Linh Thụ Nhai', 'Cổ Bi Tiên Lâm', 'Trấn Sơn Thú Cốc', 'Vân Đài Phong', 'Bế Quan Thạch Động', 'Tiên Tôn Chi Đỉnh'],
  ('Tiên Tôn Trấn Sơn Thần Thú', 'tho'), 'Thánh Sơn Trấn Thú'),
]

def monsters():
    used = set(m['name'] for m in L2.monsters()) | set(m['name'] for m in L3.monsters())   # tránh trùng tên làn trước
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    for L in (1500, 1502, 1600, 1750, 1900, 2000):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('Quái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s · %.0f đòn / %.0f đòn · rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), bh, bg, b['zb'], b['dj']))
