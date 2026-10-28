# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 9 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2–8 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 102 quái mã 1490–1591.
  2) sql/lan9_data.json — 6 khu qy 107–112, 48 map id cố định 8001–8048, 6 boss, cho sql/migrate_lan9.php.
Cửa vào: map cuối làn 8 (7080 Vô Thượng Thánh Tọa) đi xuống.
Chạy: python3 tools/gen/lan9_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan9_design as D

MID_BASE = 8001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 7080    # Vô Thượng Thánh Tọa (map cuối làn 8)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan9_data.json')
    print('quai lan 9:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
