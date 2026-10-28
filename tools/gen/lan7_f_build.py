# -*- coding: utf-8 -*-
"""
Làn 7 — L7-F (docs/LAN7_CAP_3000_3500.md mục 3): công thức chế tạo bậc 3000 (phẩm Vĩnh Hằng), mã 19–20.
Trang bị bậc 3000 sinh bởi tools/gen/dot_f_build.py (TIERS). Dùng lại cách ghi của lan3_f_build.py (chạy lại không trùng).
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan7_f_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan3_f_build as F

F.RECIPES = [
    (19, F.zbid(3000, 7), [(1, 12), (36, 10), (37, 5)], 55, 3000),
    (20, F.zbid(3000, 8), [(1, 12), (33, 10), (37, 5)], 55, 3000),
]

if __name__ == '__main__':
    print('cong thuc lan 7:', F.build())
