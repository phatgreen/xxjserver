# -*- coding: utf-8 -*-
"""
Khung tổng cấp 500 -> 4300 (docs/KHUNG_TONG_500_4300.md): in các bảng số để thiết kế / duyệt.
Dùng lại đường chỉ số gốc nhân vật của đợt F (dot_f_build.base_curve) kéo dài tới 4300.
Chạy: python3 tools/gen/khung_tong_design.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from dot_f_build import base_curve

LMAX = 4300
BASE = base_curve(LMAX)
SAFE = 1.0e9          # trần an toàn mọi giá trị số (PHP 5.6 Windows: số nguyên 32-bit, tối đa 2.147.483.647)

# ── Đường EXP ────────────────────────────────────────────────
def need_old(L):  return 110 * L * L                       # class/player.php: umaxexp
def mob_old(L):   return 107 * L                           # nowmid.php: exp quái lúc sinh
def need_new(L):  return need_old(L) if L <= 500 else 27.5e6 * (L / 500.0) ** 1.2
def mob_new(L):   return mob_old(L) if L <= 500 else 53500 * (L / 500.0) ** 1.45

# ── Nhân vật chuẩn: gốc + đồ đủ bộ (50% gốc ở bậc) cường hóa qh, nhân hệ số buff % ──
GEAR_TIERS = [500, 600, 800, 1000, 1250, 1500, 2000, 2500, 3000, 3500, 4000]
def qh_at(L):     return 10 if L <= 500 else min(30, 10 + (L - 500) // 190)   # cường hóa tăng dần +10 -> +30
def buff_at(L):   return 1.0 + 1.5 * min(1.0, (L - 500) / 3800.0) if L > 500 else 1.0  # trần ×2.5 ở 4300
def player(L, with_buff=True):
    T = max(t for t in GEAR_TIERS if t <= max(L, 500))
    g = 0.5 * (1 + 0.08 * qh_at(L))
    b = buff_at(L) if with_buff else 1.0
    return [(BASE[L][i] + g * BASE[T][i]) * b for i in range(3)]   # công, thủ, máu

# ── Quái chuẩn (Thường): người hạ quái ~5,5 đòn, quái hạ người ~50 đòn (công thức sát thương như sim_power.py) ──
def mob(L):
    tg, tf, th = player(L, with_buff=False)   # cân theo nhân vật CHƯA buff % ⇒ buff là phần thưởng thật
    fy = 0.4 * tg
    dmg = max(tg * 0.15, tg - 0.75 * fy)
    hp = 5.5 * dmg
    gj = th / 50.0 + 0.75 * tf
    return hp, gj, fy

def fmt(x):
    return '{:,.0f}'.format(x).replace(',', '.')

if __name__ == '__main__':
    pts = [500, 750, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4300]
    print('## EXP')
    print('| Cấp | EXP lên cấp (cũ) | EXP lên cấp (mới) | EXP/quái (cũ) | EXP/quái (mới) | Quái/cấp cũ | Quái/cấp mới |')
    print('|---|---|---|---|---|---|---|')
    for L in pts:
        print('| %d | %s | %s | %s | %s | %d | %d |' % (L, fmt(need_old(L)), fmt(need_new(L)), fmt(mob_old(L)),
              fmt(mob_new(L)), need_old(L) / mob_old(L), need_new(L) / mob_new(L)))
    for name, n, m in (('cũ', need_old, mob_old), ('mới', need_new, mob_new)):
        tot = sum(n(L) / m(L) for L in range(500, LMAX))
        print('Tổng quái 500->4300 (%s): %s' % (name, fmt(tot)))
    lanes = [(500, 1000), (1000, 1500), (1500, 2000), (2000, 2500), (2500, 3000), (3000, 3500), (3500, 4000), (4000, 4300)]
    print('\n| Làn | Quái cần (mới) |')
    for a, b in lanes:
        print('| %d–%d | %s |' % (a, b, fmt(sum(need_new(L) / mob_new(L) for L in range(a, b)))))

    print('\n## Sức mạnh nhân vật chuẩn / quái / boss')
    print('| Cấp | Cường hóa | Buff % | Công | Thủ | Máu | Quái HP | Quái Công | Quái Thủ | Boss HP (×12) | Boss HP / trần |')
    print('|---|---|---|---|---|---|---|---|---|---|---|')
    for L in pts:
        g, f, h = player(L)
        mh, mg, mf = mob(L)
        print('| %d | +%d | ×%.2f | %s | %s | %s | %s | %s | %s | %s | %.0f%% |' % (L, qh_at(L), buff_at(L), fmt(g), fmt(f), fmt(h),
              fmt(mh), fmt(mg), fmt(mf), fmt(mh * 12), mh * 12 / SAFE * 100))
    mx = max(max(player(L)) for L in range(500, LMAX + 1))
    print('\nGiá trị nhân vật lớn nhất: %s (%.1f%% trần an toàn)' % (fmt(mx), mx / SAFE * 100))
    print('EXP lên cấp lớn nhất (mới): %s (%.1f%% trần)' % (fmt(need_new(LMAX)), need_new(LMAX) / SAFE * 100))
