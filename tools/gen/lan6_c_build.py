# -*- coding: utf-8 -*-
"""
Làn 6 — nhiệm vụ (L6-C), chạy lại nhiều lần cho cùng kết quả. Cùng khuôn làn 2–5 (tools/gen/lan2_c_build.py):
  1) nhiemvu_main_config.json — chương 54–63 (1 chương / khu, cấp 2500–3000), giữ nguyên chương làn khác.
  2) npc_config.json + renwu_config.json — 10 NPC khu (mã 89–98), mỗi NPC 3 nhiệm vụ (mã 217–246).
  3) sql/lan6_c_data.json — NPC đặt ở map cửa vào khu (sql/migrate_lan6_c.php).
Chạy: python3 tools/gen/lan6_c_build.py
"""
import json, os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_c_build as C

C.L2 = json.load(open(os.path.join(C.ROOT, 'sql', 'lan6_data.json'), encoding='utf-8'))
C.CH0, C.LV0, C.OUT_DATA = 54, 2500, 'lan6_c_data.json'
C.NPC_ID0, C.RW_ID0 = 89, 217
C.NPCS = [
    ('Lĩnh Vực Tuần Thần Sứ', 'Nam', 'Thần sứ tuần tra Thần Vương Lĩnh Vực, bước chân đi qua đâu kim giáp cự nhân cũng cúi đầu.'),
    ('Thiên Cung Chấp Sự Thần Quan', 'Nữ', 'Thần quan chấp sự của Thần Đế Thiên Cung, giữ sổ ghi tên mọi kẻ được phép bước qua kim kiều.'),
    ('Cổ Thành Thủ Môn Lão Tướng', 'Nam', 'Lão tướng canh cổng Đế Chủ Cổ Thành, đã giữ cửa thành từ khi đế chủ còn sống đến nay.'),
    ('Thần Sơn Dẫn Lộ Đạo Đồng', 'Nam', 'Đạo đồng dẫn đường lên Chủ Tể Thần Sơn, tuổi trẻ nhưng thuộc từng khe đá vách núi.'),
    ('Chủ Tể Vực Truyền Lệnh Sứ', 'Nam', 'Sứ giả truyền lệnh trong Đại Chủ Tể Vực, lời nói ra mang theo một phần pháp tắc thiên đạo.'),
    ('Minh Hà Bãi Đò Lão Nhân', 'Nam', 'Ông lão chèo đò trên Luân Hồi Minh Hà, đã đưa vô số linh hồn qua sông mà chưa từng quay đầu.'),
    ('Hồng Lô Thủ Hỏa Thần Nữ', 'Nữ', 'Thần nữ giữ lửa Sáng Thế Hồng Lô, ngọn lửa trong tay nàng từng thắp sáng cả một phương thế giới.'),
    ('Tinh Hải Quan Tinh Lão Nhân', 'Nam', 'Lão nhân ngồi bên bờ Tinh Hải Chu Thiên, đếm sóng sao mà đoán vận mệnh chúng sinh.'),
    ('Thần Cung Chưởng Tinh Sứ', 'Nữ', 'Sứ giả nắm giữ tinh bàn của Tinh Đấu Thần Cung, một tay xoay chuyển hai mươi tám vì tinh tú.'),
    ('Tinh Khung Thủ Môn Thần Tướng', 'Nam', 'Thần tướng canh giữ cửa Chu Thiên Tinh Khung, sát khí canh kim trên người lạnh buốt như sương.'),
]

if __name__ == '__main__':
    print('chinh tuyen lan 6 (chuong 54-63):', C.build_main(), 'nhiem vu')
    print('NPC / nhiem vu NPC:', C.build_npc())
