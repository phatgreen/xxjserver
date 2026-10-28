# -*- coding: utf-8 -*-
"""
Làn 9 — L9-F (docs/LAN9_CAP_4000_4300.md mục 3): công thức chế tạo bậc 4000 (phẩm Chí Tôn), mã 23–24.
Trang bị bậc 4000 sinh bởi tools/gen/dot_f_build.py (TIERS). Dùng lại cách ghi của lan3_f_build.py (chạy lại không trùng).
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan9_f_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan3_f_build as F

F.RECIPES = [
    (23, F.zbid(4000, 7), [(1, 12), (36, 10), (37, 5)], 55, 4000),
    (24, F.zbid(4000, 8), [(1, 12), (33, 10), (37, 5)], 55, 4000),
]

if __name__ == '__main__':
    print('cong thuc lan 9:', F.build())
