# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 7 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2–6 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 1150–1319.
  2) sql/lan7_data.json — 10 khu qy 87–96, 80 map id cố định 6001–6080, 10 boss, cho sql/migrate_lan7.php.
Cửa vào: map cuối làn 6 (5080 Tinh Khung Chí Cao Điện) đi xuống.
Chạy: python3 tools/gen/lan7_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan7_design as D

MID_BASE = 6001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 5080    # Tinh Khung Chí Cao Điện (map cuối làn 6)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan7_data.json')
    print('quai lan 7:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
