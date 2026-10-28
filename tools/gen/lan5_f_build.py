# -*- coding: utf-8 -*-
"""
Làn 5 — L5-F (docs/LAN5_CAP_2000_2500.md mục 3): công thức chế tạo bậc 2000 (phẩm Thái Sơ), mã 15–16.
Trang bị bậc 2000 sinh bởi tools/gen/dot_f_build.py (TIERS). Dùng lại cách ghi của lan3_f_build.py (chạy lại không trùng).
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan5_f_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan3_f_build as F

F.RECIPES = [
    (15, F.zbid(2000, 7), [(1, 10), (36, 8), (37, 4)], 60, 2000),
    (16, F.zbid(2000, 8), [(1, 10), (32, 8), (37, 4)], 60, 2000),
]

if __name__ == '__main__':
    print('cong thuc lan 5:', F.build())
