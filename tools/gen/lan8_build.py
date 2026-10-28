# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 8 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2–7 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 1320–1489.
  2) sql/lan8_data.json — 10 khu qy 97–106, 80 map id cố định 7001–7080, 10 boss, cho sql/migrate_lan8.php.
Cửa vào: map cuối làn 7 (6080 Trận Tâm Chí Cao Đài) đi xuống.
Chạy: python3 tools/gen/lan8_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan8_design as D

MID_BASE = 7001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 6080    # Trận Tâm Chí Cao Đài (map cuối làn 7)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan8_data.json')
    print('quai lan 8:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
