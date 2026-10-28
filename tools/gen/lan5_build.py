# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 4 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2 / 3 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 810–979.
  2) sql/lan5_data.json — 10 khu qy 67–76, 80 map id cố định 4001–4080, 10 boss, cho sql/migrate_lan5.php.
Cửa vào: map cuối làn 4 (3080 Tiên Tôn Chi Đỉnh) đi xuống.
Chạy: python3 tools/gen/lan5_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan5_design as D

MID_BASE = 4001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 3080    # Tiên Tôn Chi Đỉnh (map cuối làn 4)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan5_data.json')
    print('quai lan 5:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
