# -*- coding: utf-8 -*-
"""
Làn 4 — L4-F (docs/LAN4_CAP_1500_2000.md mục 3): công thức chế tạo bậc 1500 (phẩm Thái Sơ), mã 13–14.
Trang bị bậc 1500 sinh bởi tools/gen/dot_f_build.py (TIERS). Dùng lại cách ghi của lan3_f_build.py (chạy lại không trùng).
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan4_f_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan3_f_build as F

# 1 Cường Hóa Thạch · 32 Hỏa / 33 Thủy / 36 Thổ Tinh Thạch · 37 Bình Anh Tinh Thạch
F.RECIPES = [
    (13, F.zbid(1500, 7), [(1, 8), (36, 6), (37, 3)], 65, 1500),
    (14, F.zbid(1500, 8), [(1, 8), (33, 6), (37, 3)], 65, 1500),
]

if __name__ == '__main__':
    print('cong thuc lan 4:', F.build())
