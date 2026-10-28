# -*- coding: utf-8 -*-
"""
Làn 4 — nhiệm vụ (L4-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2 / 3 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 34–43 (1 chương / khu, cấp 1500–2000), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 10 NPC khu (mã 69–78), mỗi NPC 3 nhiệm vụ (mã 157–186).
  3) sql/lan4_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan4_c.php).
Chạy: python3 tools/gen/lan4_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan4_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 34, 1500, 'lan4_c_data.json'
C.NPC_ID0, C.RW_ID0 = 69, 157
C.NPCS = [
    ('Linh Vực Dẫn Lộ Tiên Đồng', 'Nam', 'Tiên đồng dẫn đường cho người mới đặt chân vào Tiên Thiên Linh Vực, nơi linh khí đặc tới mức hóa sương.'),
    ('Vân Hải Độ Thuyền Tiên Ông', 'Nam', 'Lão tiên ông chèo thuyền mây qua Thiên Tiên Vân Hải, chỉ chở người có duyên.'),
    ('Đạo Trường Giảng Kinh Chân Nhân', 'Nam', 'Chân nhân giảng kinh mỗi sớm ở Chân Tiên Đạo Trường, một câu đạo ngữ đủ khai mở trăm năm tu vi.'),
    ('Bí Cảnh Thủ Trận Tiên Cơ', 'Nữ', 'Tiên cơ trấn giữ mê trận Huyền Tiên Bí Cảnh, biết lối ra của từng tầng huyễn cảnh.'),
    ('Thánh Địa Hộ Pháp Kim Cương', 'Nam', 'Kim cương hộ pháp canh cổng Kim Tiên Thánh Địa, thân như đúc bằng vàng ròng.'),
    ('Tinh Cung Quan Tinh Sứ', 'Nữ', 'Sứ giả quan sát tinh tú ở Thái Ất Tinh Cung, đọc vận mệnh người tu qua từng vì sao.'),
    ('Đại La Thiên Môn Thủ Vệ', 'Nam', 'Thủ vệ cổng Đại La Thiên, chỉ người có pháp thân bất diệt mới được hắn cho qua.'),
    ('Huyền Khung Tuần Thiên Sứ', 'Nam', 'Sứ giả tuần tra Cửu Thiên Huyền Khung, cưỡi gió cương phong đi khắp chín tầng trời.'),
    ('Tiên Quân Điện Chưởng Ấn Quan', 'Nam', 'Quan giữ ấn của Tiên Quân Điện, mọi chiếu lệnh tiên giới đều qua tay ông.'),
    ('Thánh Sơn Thủ Sơn Tiên Lão', 'Nam', 'Tiên lão giữ núi Tiên Tôn Thánh Sơn, đã ngồi dưới gốc linh thụ đếm mây vạn năm.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 4 (chuong 34-43):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
