# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 6 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2–5 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 980–1149.
  2) sql/lan6_data.json — 10 khu qy 77–86, 80 map id cố định 5001–5080, 10 boss, cho sql/migrate_lan6.php.
Cửa vào: map cuối làn 5 (4080 Thần Tôn Chính Điện) đi xuống.
Chạy: python3 tools/gen/lan6_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan6_design as D

MID_BASE = 5001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 4080    # Thần Tôn Chính Điện (map cuối làn 5)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan6_data.json')
    print('quai lan 6:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
