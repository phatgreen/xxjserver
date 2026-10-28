# -*- coding: utf-8 -*-
"""
Giá công pháp (chủ dự án 2026-10-28) — chạy lại nhiều lần cho cùng kết quả:
  Giá Ma Thạch = 5 × MT tích lũy 1 tháng ở cấp yêu cầu × số tháng theo giai / phẩm.
    - Tích lũy 1 ngày ≈ BOSS_PER_DAY lần giết boss × (cấp/10 + 3) MT (game/boss.php) ⇒ 1 tháng = 30 × (3 × cấp + 90) với 30 boss/ngày.
    - Cấp yêu cầu = 50 × (CG − 1) (CG 0 = cấp 1).
    - Số tháng: giai 1–7 = 1; Đế Giai Sơ / Trung / Cao / Thượng = 1 / 1,25 / 1,5 / 2; Đạo Giai = 3 / 4 / 5 / 6; Phàm Giai = 0,1 (nhập môn).
  Phàm, Hoàng, Huyền, Địa Giai (giai 0–3) mua được bằng cả hai: Linh Thạch = 3 × giá Ma Thạch.
  Làm tròn: ≥ 100.000 tới hàng chục nghìn, dưới đó tới hàng nghìn.
Đổi BOSS_PER_DAY rồi chạy lại để nâng / hạ cả bảng. Chạy: python3 tools/gen/cp_price_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save

BOSS_PER_DAY = 30
PRICE_MULT = 5
LT_MULT = 3
DUAL_MAX_TIER = 3            # giai 0–3: bán cả Linh Thạch lẫn Ma Thạch
MONTHS = {(8, 1): 1, (8, 2): 1.25, (8, 3): 1.5, (8, 4): 2, (9, 1): 3, (9, 2): 4, (9, 3): 5, (9, 4): 6}
MONTHS_PHAM = 0.1

def month_mt(cg):
    lv = max(1, 50 * (cg - 1))
    return 30 * BOSS_PER_DAY * (lv / 10.0 + 3)

def price_mt(tier, subtier, cg):
    m = MONTHS_PHAM if tier == 0 else MONTHS.get((tier, subtier), 1)
    v = PRICE_MULT * month_mt(cg) * m
    return int(round(v, -4) if v >= 100000 else round(v, -3))

def apply(cfg):
    # Ghi giá vào một công pháp (dict của congphap_config.json)
    t, st, cg = int(cfg['tier']), int(cfg['subtier']), int(cfg['realm_require'])
    mt = price_mt(t, st, cg)
    cfg['price_masthach'] = mt
    cfg['price_linhthong'] = mt * LT_MULT if t <= DUAL_MAX_TIER else 0
    return cfg

def build():
    cp = load('congphap_config.json')
    for v in cp.values():
        apply(v)
    save('congphap_config.json', cp)
    return cp

if __name__ == '__main__':
    for k, v in sorted(build().items(), key=lambda x: int(x[0])):
        print(k, v['tier'], v['subtier'], 'CG', v['realm_require'], v['cpname'], '| MT', v['price_masthach'], '| LT', v['price_linhthong'])
