# -*- coding: utf-8 -*-
"""
Làn 9 (cấp 4001 -> 4300, làn cuối) — docs/LAN9_CAP_4000_4300.md. Cùng công thức làn 2–8 (tools/gen/lan2_design.py):
quái / boss khó dần theo cấp, nhân vật chuẩn mặc đồ bậc 4000 (phẩm Chí Tôn).
6 khu (qy 107–112) × 8 map, 2 loại quái/map + Tinh Anh map cuối ⇒ 102 quái mã 1490–1591, 1 boss/khu (4050 … 4300).
Chạy: python3 tools/gen/lan9_design.py  -> in bảng kiểm tra.
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_design as L2
import lan3_design as L3
import lan4_design as L4
import lan5_design as L5
import lan6_design as L6
import lan7_design as L7
import lan8_design as L8

QY_START = 107
GYID_START = 1490
LV_START = 4000

# (tên khu, hệ chủ đạo ×2, 8 map, (boss, hệ), tên tinh anh)
REGIONS = [
 ('Thiên Đạo Pháp Đài', ('kim', 'none'),
  ['Pháp Đài Cửu Giai', 'Thiên Quy Bia Lâm', 'Luật Lệnh Hành Lang', 'Thẩm Phán Thạch Đài', 'Thiên Võng Khôi Khôi', 'Thiên Bình Nhân Quả', 'Hóa Thân Tĩnh Thất', 'Thiên Đạo Pháp Tọa'],
  ('Thiên Đạo Hóa Thân', 'kim'), 'Pháp Đài Thiên Quy Chấp Pháp'),
 ('Hỗn Nguyên Thánh Sơn', ('tho', 'moc'),
  ['Cổ Đạo Thánh Sơn', 'Thái Cực Lưỡng Nghi Phong', 'Tứ Linh Thạch Lâm', 'Bát Quái Vân Đài', 'Âm Dương Song Tuyền', 'Nguyên Khí Linh Mạch', 'Thánh Thú Huyệt Động', 'Tuyệt Đỉnh Thánh Phong'],
  ('Hỗn Nguyên Thái Cực Thánh Thú', 'tho'), 'Thánh Sơn Lưỡng Nghi Thủ Hộ'),
 ('Đại Đạo Bản Nguyên Hải', ('thuy', 'none'),
  ['Bản Nguyên Hải Ngạn', 'Văn Triều Đạo Hải', 'Hải Lưu Pháp Tắc', 'Nguyên Sơ Đảo Tự', 'Đạo Long Thâm Câu', 'Hải Nhãn Vạn Pháp', 'Triều Tịch Lốc Xoáy', 'Long Cung Hải Điện'],
  ('Bản Nguyên Đạo Long', 'thuy'), 'Bản Nguyên Hải Đạo Giao Vương'),
 ('Đạo Tổ Tử Tiêu Cung', ('hoa', 'kim'),
  ['Cung Môn Tử Tiêu', 'Giảng Đạo Hồng Mông Đường', 'Tam Thanh Bồ Đoàn', 'Khí Hải Vân Đài', 'Phân Bảo Nhai', 'Luyện Đan Tổ Phòng', 'Huyền Môn Tàng Kinh', 'Hồng Quân Pháp Tọa'],
  ('Tử Tiêu Cung Đạo Tổ Tàn Niệm', 'hoa'), 'Tử Tiêu Cung Hồng Mông Đạo Đồng'),
 ('Chí Tôn Thiên Khư', ('none', 'hoa'),
  ['Thiên Khư Phế Thành', 'Chí Tôn Cốt Hải', 'Vẫn Đế Mộ Địa', 'Đoạn Thiên Kiếm Trủng', 'Phong Ma Huyết Trì', 'Cửu U Liệt Khẩu', 'Chí Tôn Ma Lăng', 'Tận Cùng Hư Khư'],
  ('Thiên Khư Chí Tôn Ma Ảnh', 'none'), 'Thiên Khư Vẫn Đế Ma Tướng'),
 ('Vô Ngã Chung Cực Cảnh', ('none', 'kim'),
  ['Vô Ngã Hư Lộ', 'Quên Thân Kiều', 'Diệt Tướng Nhai', 'Vô Niệm Hồ', 'Kính Điện Vấn Tâm', 'Chân Ngã Tế Đàn', 'Chân Viên Chi Môn', 'Chung Cực Vô Ngã Đài'],
  ('Chung Cực Vô Ngã Tâm Ma', 'none'), 'Chung Cực Tâm Ma Hộ Vệ'),
]

def monsters():
    used = set(m['name'] for mod in (L2, L3, L4, L5, L6, L7, L8) for m in mod.monsters())   # tránh trùng tên các làn trước
    return L2.monsters(REGIONS, GYID_START, QY_START, LV_START, used)

def bosses():
    return L2.bosses(REGIONS, QY_START, LV_START)

if __name__ == '__main__':
    for L in (4000, 4002, 4100, 4200, 4300):
        h, g, f = L2.thuong_stats(L)
        a, b = L2.hits(L, h, g, f)
        print('| %d | %d / %d / %d | %.1f | %.1f |' % (L, h, g, f, a, b))
    ms = monsters()
    print('Quái: %d loại (mã %d–%d)' % (len(ms), ms[0]['id'], ms[-1]['id']))
    for b in bosses():
        bh, bg = L2.hits(b['lv'], b['hp'], b['gj'], b['fy'])
        print('%s cấp %d máu %s · %.0f đòn / %.0f đòn · rơi %s / %s' % (b['name'], b['lv'], format(b['hp'], ','), bh, bg, b['zb'], b['dj']))
