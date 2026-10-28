# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 4 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2 / 3 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 640–809.
  2) sql/lan4_data.json — 10 khu qy 57–66, 80 map id cố định 3001–3080, 10 boss, cho sql/migrate_lan4.php.
Cửa vào: map cuối làn 3 (2080 Mẫu Thụ Hạ) đi xuống.
Chạy: python3 tools/gen/lan4_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan4_design as D

MID_BASE = 3001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 2080    # Mẫu Thụ Hạ (map cuối làn 3)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan4_data.json')
    print('quai lan 4:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
