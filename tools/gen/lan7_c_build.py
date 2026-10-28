# -*- coding: utf-8 -*-
"""
Làn 7 — nhiệm vụ (L7-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2–6 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 64–73 (1 chương / khu, cấp 3000–3500), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 10 NPC khu (mã 99–108), mỗi NPC 3 nhiệm vụ (mã 247–276).
  3) sql/lan7_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan7_c.php).
Chạy: python3 tools/gen/lan7_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan7_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 64, 3000, 'lan7_c_data.json'
C.NPC_ID0, C.RW_ID0 = 99, 247
C.NPCS = [
    ('Thiên Lộ Dẫn Tinh Sứ', 'Nam', 'Sứ giả dẫn đường trên Kim Tinh Thiên Lộ, cầm ngọn tinh đăng soi lối giữa mưa sao băng.'),
    ('Tử Vi Viên Thủ Tinh Lão Nhân', 'Nam', 'Lão nhân giữ vườn Tử Vi Đế Tinh Viên, chăm từng ngôi sao như chăm hoa cỏ suốt vạn năm.'),
    ('Bạch Nguyên Tuần Tinh Thần Nữ', 'Nữ', 'Thần nữ tuần tra Băng Tinh Bạch Nguyên, bước qua đâu băng tinh cũng ngân lên tiếng chuông.'),
    ('Nguyệt Cung Chưởng Đăng Tiên Nữ', 'Nữ', 'Tiên nữ giữ đèn Thái Âm Nguyệt Cung, ánh đèn trong tay nàng chính là ánh trăng chiếu xuống nhân gian.'),
    ('Kim Ô Cốc Thủ Hỏa Lão Tướng', 'Nam', 'Lão tướng canh lửa ở Liệt Nhật Kim Ô Cốc, giáp trụ đã cháy sạm vì ở gần mặt trời quá lâu.'),
    ('Thất Tinh Đài Quan Tinh Đạo Đồng', 'Nam', 'Đạo đồng ngắm sao trên Bắc Đẩu Thất Tinh Đài, thuộc lòng vị trí bảy ngôi sao trong mọi canh giờ.'),
    ('Bát Tú Lộ Truyền Tin Sứ', 'Nam', 'Sứ giả chạy tin dọc Nhị Thập Bát Tú Lộ, một ngày đi qua đủ hai mươi tám chòm sao.'),
    ('Tinh Vân Lạc Lối Thần Quan', 'Nữ', 'Thần quan lạc giữa Tử Khí Tinh Vân đã ngàn năm, quen đường trong màn sương tím hơn bất kỳ ai.'),
    ('Tinh Nhãn Thủ Vọng Lão Nhân', 'Nam', 'Lão nhân canh giữ bờ Hư Không Tinh Nhãn, ngày đêm nhìn những vì sao bị nuốt chửng mà không chớp mắt.'),
    ('Đại Trận Thủ Trận Thần Tướng', 'Nam', 'Thần tướng trấn giữ Chu Thiên Tinh Đấu Trận, một bước sai của y đủ khiến cả đại trận đảo lộn.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 7 (chuong 64-73):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
