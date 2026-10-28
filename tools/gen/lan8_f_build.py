# -*- coding: utf-8 -*-
"""
Làn 8 — L8-F (docs/LAN8_CAP_3500_4000.md mục 3): công thức chế tạo bậc 3500 (phẩm Chí Tôn), mã 21–22.
Trang bị bậc 3500 sinh bởi tools/gen/dot_f_build.py (TIERS). Dùng lại cách ghi của lan3_f_build.py (chạy lại không trùng).
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan8_f_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan3_f_build as F

F.RECIPES = [
    (21, F.zbid(3500, 7), [(1, 12), (36, 10), (37, 5)], 55, 3500),
    (22, F.zbid(3500, 8), [(1, 12), (33, 10), (37, 5)], 55, 3500),
]

if __name__ == '__main__':
    print('cong thuc lan 8:', F.build())
