# -*- coding: utf-8 -*-
"""
Làn 9 (làn cuối) — nhiệm vụ (L9-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2–8 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 84–89 (1 chương / khu, cấp 4000–4300), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 6 NPC khu (mã 119–124), mỗi NPC 3 nhiệm vụ (mã 307–324).
  3) sql/lan9_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan9_c.php).
Chạy: python3 tools/gen/lan9_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan9_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 84, 4000, 'lan9_c_data.json'
C.NPC_ID0, C.RW_ID0 = 119, 307
C.NPCS = [
    ('Pháp Đài Thủ Đạo Sứ', 'Nam', 'Sứ giả giữ luật trước Thiên Đạo Pháp Đài, lời y nói ra chính là một điều thiên quy.'),
    ('Thánh Sơn Âm Dương Đạo Nhân', 'Nam', 'Đạo nhân tu hành dưới chân Hỗn Nguyên Thánh Sơn, nửa thân sáng nửa thân tối như thái cực.'),
    ('Bản Nguyên Hải Lão Ngư Ông', 'Nam', 'Ông lão buông câu bên Đại Đạo Bản Nguyên Hải, mỗi lần kéo cần là một đạo văn trồi lên mặt nước.'),
    ('Tử Tiêu Cung Thủ Môn Đạo Đồng', 'Nam', 'Đạo đồng giữ cửa Tử Tiêu Cung, từng nghe Đạo Tổ giảng đạo suốt vạn kiếp.'),
    ('Thiên Khư Thủ Mộ Lão Nhân', 'Nam', 'Lão nhân giữ mộ ở Chí Tôn Thiên Khư, thuộc tên từng vị chí tôn đã vẫn lạc nơi đây.'),
    ('Vô Ngã Cảnh Dẫn Độ Thánh Nữ', 'Nữ', 'Thánh nữ dẫn độ người tu vào Vô Ngã Chung Cực Cảnh, ánh mắt nàng phản chiếu chân ngã của mỗi người.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 9 (chuong 84-89):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
