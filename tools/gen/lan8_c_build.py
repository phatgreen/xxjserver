# -*- coding: utf-8 -*-
"""
Làn 8 — nhiệm vụ (L8-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2–7 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 74–83 (1 chương / khu, cấp 3500–4000), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 10 NPC khu (mã 109–118), mỗi NPC 3 nhiệm vụ (mã 277–306).
  3) sql/lan8_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan8_c.php).
Chạy: python3 tools/gen/lan8_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan8_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 74, 3500, 'lan8_c_data.json'
C.NPC_ID0, C.RW_ID0 = 109, 277
C.NPCS = [
    ('Ngân Hà Độ Tinh Sứ', 'Nam', 'Sứ giả chèo thuyền sao ở Ngân Hà Tận Đầu, đưa người qua chỗ dòng ngân hà đổ xuống hư không.'),
    ('Viên Mãn Đài Thủ Tọa Lão Nhân', 'Nam', 'Lão nhân ngồi giữ bậc đài Chu Thiên Viên Mãn, đã thấy vô số người tu chu thiên đến rồi đi.'),
    ('Thiên Thư Các Thủ Thư Thần Nữ', 'Nữ', 'Thần nữ giữ sách trong Thần Mệnh Thiên Thư Các, từng trang sách nàng lật đều ghi một số mệnh.'),
    ('Thần Quốc Biên Cảnh Sứ Giả', 'Nam', 'Sứ giả canh biên giới Sáng Thế Thần Quốc, chỉ cho kẻ đủ tư cách bước vào thần quốc.'),
    ('Trường Hà Lái Đò Thần Nhân', 'Nam', 'Thần nhân lái đò trên Vận Mệnh Trường Hà, đưa người ngược dòng quá khứ hay xuôi về tương lai.'),
    ('Thánh Lộ Dẫn Đường Đạo Đồng', 'Nam', 'Đạo đồng dẫn đường trên Nhập Thánh Lộ, tuổi nhỏ nhưng đã nhìn thấu tâm ma của người qua đường.'),
    ('Thí Luyện Tháp Thủ Tháp Lão Nhân', 'Nam', 'Lão nhân giữ cửa Chuẩn Thánh Thí Luyện Tháp, ghi tên từng người vượt qua mỗi tầng tháp.'),
    ('Thánh Cảnh Hỗn Độn Đạo Nhân', 'Nam', 'Đạo nhân sống giữa Hỗn Độn Thánh Cảnh, thân hình mờ ảo như hòa vào màn hỗn mang.'),
    ('Thánh Vực Thủ Vọng Sứ', 'Nữ', 'Sứ giả canh gác Hư Vô Thánh Vực, đôi mắt nhìn xuyên qua hư vô thấy được ma thánh ẩn mình.'),
    ('Thánh Điện Thủ Môn Thánh Tướng', 'Nam', 'Thánh tướng trấn giữ cửa Vô Thượng Thánh Điện, kim giáp trên người phát ra thánh quang chói lòa.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 8 (chuong 74-83):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
