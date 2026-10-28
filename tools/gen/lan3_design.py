# -*- coding: utf-8 -*-
"""
Làn 3 (cấp 1001 -> 1500) — docs/LAN3_CAP_1000_1500.md. Cùng công thức làn 2 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 1000 (1001–1249) / 1250 (1250–1500, phẩm Hỗn Độn).
10 khu (qy 47–56) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 170 quái mã 470–639, 1 boss/khu (1050 … 1500).
Từ Độ Kiếp trở đi Đột Phá Đan không còn tác dụng ⇒ quái / boss chỉ rơi Cường Hóa Thạch (dj_for tự xử lý).
Chạy: python3 tools/gen/lan3_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2

QY_START = 47
GYID_START = 470
LV_START = 1000

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Vạn Thánh Sơn Mạch', ('tho', 'kim'),
  ['Thánh Sơn Cước Lộ', 'Vạn Thánh Bi Lâm', 'Thánh Tích Cổ Đạo', 'Quần Thánh Phong', 'Thánh Huyết Khe', 'Thánh Cốt Nhai', 'Sơn Mạch Linh Khẩu', 'Vạn Thánh Đỉnh'],
  ('Vạn Thánh Sơn Quân', 'tho'), 'Vạn Thánh Hộ Sơn Thú'),
 ('Thánh Vương Cổ Thành', ('kim', 'none'),
  ['Cổ Thành Ngoại Quách', 'Phế Đô Trường Nhai', 'Thánh Vương Phủ Khố', 'Cấm Vệ Doanh', 'Hoàng Thành Lâu', 'Cổ Thành Thủy Đạo', 'Vương Tọa Tiền Điện', 'Thánh Vương Điện'],
  ('Thánh Vương Tàn Hồn', 'none'), 'Thánh Vương Cấm Vệ Trưởng'),
 ('Phong Thánh Đài', ('hoa', 'kim'),
  ['Đài Hạ Quảng Trường', 'Vấn Đạo Giai', 'Phong Thánh Bi', 'Thiên Hỏa Tế Đàn', 'Kim Quang Hành Lang', 'Thánh Ấn Các', 'Thiên Lôi Pháp Trận', 'Phong Thánh Đài Đỉnh'],
  ('Thánh Nhân Pháp Tướng', 'hoa'), 'Phong Thánh Thủ Đài Nhân'),
 ('Chuẩn Đế Chiến Khư', ('none', 'kim'),
  ['Chiến Khư Biên Hoang', 'Đoạn Kích Nguyên', 'Đế Huyết Sa Mạc', 'Tàn Giáp Khâu', 'Vạn Cốt Hố', 'Chiến Hồn Lĩnh', 'Đế Binh Trủng', 'Chuẩn Đế Chiến Đài'],
  ('Chuẩn Đế Thi Vương', 'none'), 'Chiến Khư Thi Tướng'),
 ('Đế Lăng Hoang Nguyên', ('tho', 'none'),
  ['Hoang Nguyên Phong Sa', 'Thạch Mã Đạo', 'Táng Đế Bình Nguyên', 'Đế Lăng Ngoại Viên', 'Hoàng Thổ Mộ Đạo', 'Kim Quan Điện', 'Đế Lăng Địa Cung', 'Cổ Đế Chủ Mộ'],
  ('Cổ Đế Thủ Lăng Thú', 'tho'), 'Đế Lăng Thạch Tượng'),
 ('Phi Thăng Thiên Lộ', ('kim', 'hoa'),
  ['Thiên Lộ Khởi Điểm', 'Vân Thê Bậc', 'Cương Phong Tầng', 'Lôi Vân Hải', 'Tiếp Dẫn Quang Trụ', 'Thiên Lộ Trạm Dịch', 'Phi Thăng Đài', 'Thiên Lộ Tận Đầu'],
  ('Tiếp Dẫn Kim Long', 'kim'), 'Thiên Lộ Thủ Quan Giả'),
 ('Tiên Nhân Động Phủ', ('moc', 'thuy'),
  ['Động Phủ Thạch Môn', 'Linh Dược Viên', 'Đan Phòng Phế Tích', 'Khôi Lỗi Trận', 'Linh Tuyền Trì', 'Tàng Thư Thạch Thất', 'Luyện Khí Thất', 'Động Phủ Chủ Điện'],
  ('Động Phủ Khôi Lỗi Tiên', 'moc'), 'Động Phủ Thủ Hộ Khôi Lỗi'),
 ('Tán Tiên Hải Đảo', ('thuy', 'moc'),
  ['Hải Đảo Sa Ngạn', 'San Hô Thạch Lâm', 'Tán Tiên Thảo Lư', 'Triều Âm Động', 'Kình Cốt Loan', 'Hải Thị Thận Lâu', 'Kiếp Vân Tiêu', 'Tán Tiên Độ Kiếp Đài'],
  ('Tán Tiên Kiếp Ma', 'thuy'), 'Hải Đảo Kiếp Linh'),
 ('Tiên Môn Ngoại Vực', ('kim', 'moc'),
  ['Tiên Môn Sơn Đạo', 'Ngoại Môn Diễn Võ Trường', 'Tạp Dịch Viện', 'Linh Điền', 'Thí Luyện Tháp Hạ', 'Tiên Hạc Nhai', 'Tiên Môn Bài Lâu', 'Nội Môn Quan Ải'],
  ('Tiên Môn Trấn Thủ', 'kim'), 'Tiên Môn Hộ Pháp'),
 ('Địa Tiên Linh Giới', ('moc', 'tho'),
  ['Linh Giới Môn Hộ', 'Vạn Linh Thảo Nguyên', 'Linh Mạch Địa Huyệt', 'Thụ Hải Mê Cung', 'Địa Tiên Linh Điền', 'Linh Khí Hồ', 'Mẫu Thụ Căn Hệ', 'Mẫu Thụ Hạ'],
  ('Địa Tiên Mẫu Thụ', 'moc'), 'Mẫu Thụ Thủ Hộ Linh'),
]

def monsters():
    used = set(m['name'] for m in L2.monsters())   # tránh trùng tên với làn 2
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    print('| Cấp | Quái Thường máu/công/thủ | Đòn hạ quái | Đòn quái hạ người |')
    for L in (1000, 1002, 1100, 1249, 1250, 1400, 1500):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('\nQuái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s công %s · %.0f đòn hạ boss / boss hạ người %.0f đòn · rơi %s / %s' % (
            b['name'], b['lv'], format(b['hp'], ','), format(b['gj'], ','), bh, bg, b['zb'], b['dj']))
