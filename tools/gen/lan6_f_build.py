# -*- coding: utf-8 -*-
"""
Làn 6 — L6-F (docs/LAN6_CAP_2500_3000.md mục 3): công thức chế tạo bậc 2500 (phẩm Vĩnh Hằng), mã 17–18.
Trang bị bậc 2500 sinh bởi tools/gen/dot_f_build.py (TIERS). Dùng lại cách ghi của lan3_f_build.py (chạy lại không trùng).
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan6_f_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan3_f_build as F

F.RECIPES = [
    (17, F.zbid(2500, 7), [(1, 12), (36, 10), (37, 5)], 55, 2500),
    (18, F.zbid(2500, 8), [(1, 12), (33, 10), (37, 5)], 55, 2500),
]

if __name__ == '__main__':
    print('cong thuc lan 6:', F.build())
