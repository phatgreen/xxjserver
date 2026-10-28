# -*- coding: utf-8 -*-
"""
Làn 3 — nhiệm vụ (L3-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 24–33 (1 chương / khu, cấp 1000–1500), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 10 NPC khu (mã 59–68), mỗi NPC 3 nhiệm vụ (mã 127–156).
  3) sql/lan3_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan3_c.php).
Bước "thu thập": cảnh giới làn 3 không có Đột Phá Đan (từ Độ Kiếp đan không còn tác dụng) ⇒ 30 Cường Hóa Thạch.
Chạy: python3 tools/gen/lan3_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan3_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 24, 1000, 'lan3_c_data.json'
C.NPC_ID0, C.RW_ID0 = 59, 127
C.NPCS = [
    ('Sơn Mạch Hái Thuốc Lão Nhân', 'Nam', 'Lão nhân hái thuốc cả đời trong Vạn Thánh Sơn Mạch, biết từng khe núi nơi linh dược mọc và yêu thú rình rập.'),
    ('Cổ Thành Thủ Môn Tướng', 'Nam', 'Tướng giữ cổng Thánh Vương Cổ Thành, thành đã đổ nát vạn năm mà hắn vẫn chưa rời vị trí.'),
    ('Phong Thánh Đài Chấp Sự', 'Nữ', 'Nữ chấp sự ghi danh người lên Phong Thánh Đài, chỉ người vượt khảo nghiệm mới được khắc tên lên bia.'),
    ('Chiến Khư Nhặt Kiếm Nhân', 'Nam', 'Kẻ lang thang nhặt binh khí tàn trên Chuẩn Đế Chiến Khư, nghe được tiếng kiếm khóc của đế binh.'),
    ('Đế Lăng Thủ Mộ Lão Bộc', 'Nam', 'Lão bộc trung thành canh mộ cổ đế, sống nhờ một tia đế khí còn sót lại trong lăng.'),
    ('Thiên Lộ Tiếp Dẫn Đồng Tử', 'Nam', 'Đồng tử cầm đèn dẫn đường trên Phi Thăng Thiên Lộ, nụ cười không đổi suốt ngàn năm.'),
    ('Động Phủ Khí Linh', 'Nữ', 'Khí linh của động phủ tiên nhân đã tọa hóa, vẫn tận tụy chờ người kế thừa xứng đáng.'),
    ('Hải Đảo Tán Tiên', 'Nam', 'Tán tiên ẩn cư trên hải đảo, mỗi trăm năm lại phải đối mặt một lần thiên kiếp tán tiên.'),
    ('Tiên Môn Tạp Dịch', 'Nam', 'Đệ tử tạp dịch ngoại môn, chuyện lớn nhỏ trong Tiên Môn Ngoại Vực không gì hắn không biết.'),
    ('Linh Giới Thổ Địa', 'Nam', 'Thổ địa cai quản Địa Tiên Linh Giới, gốc rễ Mẫu Thụ đi tới đâu ông đều cảm nhận được.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 3 (chuong 24-33):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
