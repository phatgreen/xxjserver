# -*- coding: utf-8 -*-
"""
Làn 6 (cấp 2501 -> 3000) — docs/LAN6_CAP_2500_3000.md. Cùng công thức làn 2–5 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 2500 (phẩm Vĩnh Hằng).
10 khu (qy 77–86) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 170 quái mã 980–1149, 1 boss/khu (2550 … 3000).
Chạy: python3 tools/gen/lan6_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2
import lan3_design as L3
import lan4_design as L4
import lan5_design as L5

QY_START = 77
GYID_START = 980
LV_START = 2500

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Thần Vương Lĩnh Vực', ('kim', 'tho'),
  ['Lĩnh Vực Biên Quan', 'Kim Giáp Thành Lũy', 'Thần Vương Kỳ Đài', 'Cự Nhân Thạch Cốc', 'Thiết Bích Hùng Quan', 'Vương Đình Ngoại Viện', 'Kim Giáp Luyện Binh Trường', 'Thần Vương Ngự Điện'],
  ('Thần Vương Kim Giáp Cự Nhân', 'kim'), 'Lĩnh Vực Kim Giáp Thống Soái'),
 ('Thần Đế Thiên Cung', ('kim', 'hoa'),
  ['Thiên Cung Kim Kiều', 'Ngọc Giai Đan Bệ', 'Kỳ Lân Các', 'Thụy Thú Viên', 'Thần Đế Thư Khố', 'Cửu Long Bích', 'Tử Kim Hành Cung', 'Thần Đế Ngự Tọa Điện'],
  ('Thần Đế Ngự Tọa Kỳ Lân', 'hoa'), 'Thiên Cung Ngự Lâm Thần Tướng'),
 ('Đế Chủ Cổ Thành', ('none', 'tho'),
  ['Cổ Thành Phế Môn', 'Hoang Phế Phố Thị', 'Đế Chủ Cổ Miếu', 'Tàn Hồn Hẻm', 'Cổ Thành Tường Thành', 'Đoạn Kiếm Quảng Trường', 'Huyết Nguyệt Lâu', 'Đế Chủ Tàn Điện'],
  ('Cổ Thành Đế Chủ Tàn Hồn', 'none'), 'Cổ Thành Oán Linh Tướng Quân'),
 ('Chủ Tể Thần Sơn', ('tho', 'moc'),
  ['Thần Sơn Cước Lộ', 'Vạn Nhận Thạch Bích', 'Cự Viên Động', 'Thanh Tùng Lĩnh', 'Sơn Thần Miếu', 'Vân Đỉnh Thạch Kiều', 'Thần Sơn Bí Cốc', 'Chủ Tể Phong Đỉnh'],
  ('Thần Sơn Chủ Tể Cự Viên', 'tho'), 'Thần Sơn Bàn Thạch Viên Vương'),
 ('Đại Chủ Tể Vực', ('none', 'kim'),
  ['Chủ Tể Vực Môn', 'Pháp Tắc Hành Lang', 'Quyền Bính Đài', 'Chủ Tể Ấn Đường', 'Vực Ngoại Hư Không', 'Thiên Đạo Bia Lâm', 'Chúng Sinh Đỉnh', 'Đại Chủ Tể Pháp Đàn'],
  ('Đại Chủ Tể Pháp Thân', 'none'), 'Chủ Tể Vực Pháp Tắc Hộ Vệ'),
 ('Luân Hồi Minh Hà', ('thuy', 'none'),
  ['Minh Hà Bến Đò', 'Vong Xuyên Bờ', 'Nại Hà Kiều', 'Tam Sinh Thạch', 'Mạnh Bà Đình', 'Lục Đạo Luân Bàn', 'Minh Hà Long Uyên', 'Luân Hồi Đại Điện'],
  ('Luân Hồi Minh Hà Long', 'thuy'), 'Minh Hà Quỷ Long Tướng'),
 ('Sáng Thế Hồng Lô', ('hoa', 'tho'),
  ['Hồng Lô Khai Thiên Môn', 'Hỗn Mang Hỏa Hải', 'Tạo Vật Đài', 'Phượng Hoàng Sào', 'Thái Dương Chân Hỏa Trì', 'Địa Phế Nham Hà', 'Niết Bàn Hỏa Sơn', 'Sáng Thế Lô Tâm'],
  ('Sáng Thế Hỏa Phượng', 'hoa'), 'Hồng Lô Niết Bàn Phượng Vệ'),
 ('Tinh Hải Chu Thiên', ('thuy', 'kim'),
  ['Tinh Hải Bờ Cát', 'Ngân Sa Than', 'Kình Ngư Hải Câu', 'Tinh Triều Đảo', 'Bích Lạc Hải Uyên', 'Lam Tinh San Hô Lâm', 'Cự Kình Hải Mộ', 'Tinh Hải Trung Tâm'],
  ('Tinh Hải Cự Kình', 'thuy'), 'Tinh Hải Kình Long Hộ Vệ'),
 ('Tinh Đấu Thần Cung', ('kim', 'moc'),
  ['Thần Cung Tinh Môn', 'Thất Diệu Hành Lang', 'Nhị Thập Bát Tú Đài', 'Tinh Quân Phủ', 'Thiên Xu Các', 'Dao Quang Lâu', 'Tinh Bàn Chuyển Luân', 'Tinh Đấu Chủ Điện'],
  ('Tinh Đấu Thần Quân', 'kim'), 'Thần Cung Tinh Tú Đại Tướng'),
 ('Chu Thiên Tinh Khung', ('kim', 'none'),
  ['Tinh Khung Ngoại Hoàn', 'Bạch Hổ Lĩnh', 'Tây Phương Canh Kim Địa', 'Sát Phạt Tinh Lộ', 'Chu Thiên Đại Trận', 'Tinh Khung Liệt Phong', 'Thần Tướng Diễn Võ Đài', 'Tinh Khung Chí Cao Điện'],
  ('Tinh Khung Bạch Hổ Thần Tướng', 'kim'), 'Tinh Khung Canh Kim Thần Vệ'),
]

def monsters():
    used = set(m['name'] for mod in (L2, L3, L4, L5) for m in mod.monsters())   # tránh trùng tên các làn trước
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    for L in (2500, 2502, 2600, 2750, 2900, 3000):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('Quái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s · %.0f đòn / %.0f đòn · rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), bh, bg, b['zb'], b['dj']))
