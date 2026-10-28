# -*- coding: utf-8 -*-
"""
Làn 5 — nhiệm vụ (L5-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2–4 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 44–53 (1 chương / khu, cấp 2000–2500), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 10 NPC khu (mã 79–88), mỗi NPC 3 nhiệm vụ (mã 187–216).
  3) sql/lan5_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan5_c.php).
Chạy: python3 tools/gen/lan5_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan5_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 44, 2000, 'lan5_c_data.json'
C.NPC_ID0, C.RW_ID0 = 79, 187
C.NPCS = [
    ('Cương Vực Tuần Biên Tướng', 'Nam', 'Tướng tuần biên của Tiên Vương Cương Vực, giáp trụ phủ đầy bụi chiến trường ngàn năm.'),
    ('Tiên Đình Truyền Chỉ Quan', 'Nam', 'Quan truyền chỉ của Vô Thượng Tiên Đình, mỗi đạo thánh chỉ ông đọc đều chấn động tam giới.'),
    ('Tiên Lộ Hộ Đạo Nhân', 'Nam', 'Người hộ đạo trên Chuẩn Đế Tiên Lộ, đã tiễn vô số thiên kiêu leo lên rồi ngã xuống.'),
    ('Hoàng Thành Lễ Quan', 'Nam', 'Lễ quan của Tiên Đế Hoàng Thành, thuộc làu mọi quy củ triều nghi tiên giới.'),
    ('Đế Khư Khảo Cổ Lão Nhân', 'Nam', 'Lão nhân cả đời đào bới Vô Thượng Đế Khư, tìm dấu vết các đời đế giả đã vẫn lạc.'),
    ('Tiên Mộ Thủ Linh Nhân', 'Nữ', 'Người giữ linh vị trong Bất Hủ Tiên Mộ, hát khúc an hồn cho những tiên đế không chịu yên nghỉ.'),
    ('Thần Lô Khống Hỏa Sư', 'Nam', 'Hỏa sư điều khiển ngọn lửa của Luyện Thần Lô, một niệm có thể khiến lò bùng hay tắt.'),
    ('Thiên Môn Tiếp Dẫn Thần Sứ', 'Nữ', 'Thần sứ đứng trước Nhập Thần Thiên Môn, chỉ dẫn người phá cảnh tiên bước vào thần đạo.'),
    ('Thánh Vực Truyền Đạo Thần Quan', 'Nam', 'Thần quan truyền giảng thần đạo ở Thánh Vực, đạo văn trên người ông tự phát sáng.'),
    ('Thần Điện Thủ Ấn Thần Quan', 'Nam', 'Thần quan giữ ấn của Thần Tôn Thần Điện, chỉ trao ấn cho người hạ được thần long hộ điện.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 5 (chuong 44-53):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
