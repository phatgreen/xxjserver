# -*- coding: utf-8 -*-
"""
Ghi dữ liệu làn 3 (chạy lại nhiều lần cho cùng kết quả) — cùng cách làn 2 (tools/gen/lan2_build.py):
  1) class/config/guaiwu_config.json — thêm 170 quái mã 470–639 (ghi đè đúng các mã này).
  2) sql/lan3_data.json — 10 khu qy 47–56, 80 map id cố định 2001–2080, 10 boss, cho sql/migrate_lan3.php.
Cửa vào: map cuối làn 2 (1080 Thiên Cung Chính Điện) đi xuống.
Chạy: python3 tools/gen/lan3_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import lan2_build as B
import lan3_design as D

MID_BASE = 2001          # quy ước làn N: 1000×(N−1)+1
ENTRY_FROM_MID = 1080    # Thiên Cung Chính Điện (map cuối làn 2)

if __name__ == '__main__':
    ms, bs = D.monsters(), D.bosses()
    n = B.build_guaiwu(ms)
    r, nm, nb = B.build_data(ms, bs, D.REGIONS, D.QY_START, MID_BASE, D.LV_START, ENTRY_FROM_MID, 'lan3_data.json')
    print('quai lan 3:', n, '| khu:', r, '| map:', nm, '| boss:', nb)
