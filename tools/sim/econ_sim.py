# -*- coding: utf-8 -*-
"""
Mô phỏng người chơi mới F2P — luật 2026-10-28 (thể lực 100+cấp-1, hồi 1/phút, đòn thường 1; MT boss chặn chênh >= 35).
Công thức lấy từ code — xem docs/DOT_G_KINH_TE_THE_LUC_TRANG_BI.md mục 6. Chạy: python3 tools/sim/econ_sim.py
Gồm: tu luyện, quái, boss, đột phá, nhiệm vụ, Tỏa Yêu Tháp, săn trang bị (quái 9 ô thường / boss 6 ô quý), linh sủng,
võ công / công pháp lên cấp theo quái, chi tiền dư cho linh căn → thiên phú → huyết mạch → thể chất → cường hóa (giữ quỹ đột phá).
In so sánh: giả định đủ bộ (cũ) và tự săn đồ (không mua Phường Thị), 30 lần chạy / kịch bản.
"""
import random, math

BOSS = {30:(29600,180),45:(50700,600),60:(72200,1200),70:(95700,600),92:(272700,600),150:(611400,600),200:(983500,600),250:(1421800,600),
 300:(1921500,600),350:(2479000,600),400:(3090600,600),450:(3754500,600),500:(4468900,600),550:(5192900,600),600:(7097500,600)}
BREAK_PCT = {1:0,10:1,30:1,60:2,120:2,200:3,300:3,400:4,500:4,600:5}
CULT_PCT = {1:1,10:2,30:2,60:4,120:4,200:6,300:6,400:8,500:8,600:10}
CLICK, TRAVEL, FIND = 1.5, 15, 2
HITS_MOB = 5.5

def need(L): return max(50, (L + 1) ** 2 * 110)
def tier(L, tab): return tab[max(t for t in tab if t <= L)]
def boss_hp(L):
    ks = sorted(BOSS)
    if L <= ks[0]: return BOSS[ks[0]][0]
    for a, b in zip(ks, ks[1:]):
        if L <= b: return BOSS[a][0] * (BOSS[b][0] / BOSS[a][0]) ** ((L - a) / (b - a))
    return BOSS[ks[-1]][0]
SPECIAL_BREAK, SPECIAL_CULT = (9, 10, 11), (8, 13, 14)
NORMAL_GROUPS = ((1, 2, 3), (4, 5, 6), (7, 12, 15))
MOB_DROP, BOSS_DROP = 21.0 / 120, 15.0 / 120   # dljv trung bình quái bậc >= 120 / boss (DB server thử)
def tkey(L): return max(t for t in BREAK_PCT if t <= L)   # số bậc trang bị <= cấp
def slot_val(T, s):
    # tuposjs / tupoexp của 1 món (dot_f_build.apply_mystic): ô quý đủ, ô thường max(1, //3) từ bậc 60
    if T <= 0: return 0, 0
    b, c = BREAK_PCT[T], CULT_PCT[T]
    if s in SPECIAL_BREAK: return b, 0
    if s in SPECIAL_CULT: return 0, c
    return (max(1, b // 3), max(1, c // 3)) if T >= 60 else (0, 0)
def rate(L, wd, gb=None):
    if L <= 93: base = 75 - 0.6 * L - 3 * (L // 50 + 1)
    else:
        pts = [(93, 13.2), (100, 10), (500, -20), (1000, -40)]
        for (a, va), (b, vb) in zip(pts, pts[1:]):
            if L <= b: base = va + (vb - va) * (L - a) / (b - a); break
    return min(99, max(5, int(base + (10 if L % 50 == 0 else 0) + (3 * tier(L, BREAK_PCT) if gb is None else gb) + {5: 5, 6: 10}.get(wd, 0))))
def mob_exp(L): return 107 * L if L <= 500 else 110 * L * L / 400
def tupo_cost(L): return L * L * 3 if L % 50 == 0 else L * (L + 1)

# ---- chi phí tính năng (báo cáo agent, file:line trong đó) ----
def lc_cost(lv): return min(7, 1 + lv // 15) * 50 + 100 + 15 * lv          # 1 cấp linh căn 1 hệ (đá mua tiệm 50)
LC_STAGE = [(15, 5000 + 100), (30, 20000 + 200), (50, 80000 + 400), (70, 250000 + 800), (90, 0)]  # trần, phí đột phá bậc (LT + Bình Anh)
TF_CAP = [10, 14, 18, 24, 30, 36, 42]
TF_BREAK = [(10, 10000, 0), (15, 30000, 0), (20, 80000, 0), (30, 200000, 100), (40, 500000, 150)]  # (tf, LT, cấp cần)
TC_STEPS = [(1000, 1.0, 2500), (5000, .8, 12500), (20000, .6, 270000), (80000, .4, 0), (300000, .25, 0), (1e6, .15, 0), (5e6, .08, 0)]  # thể chất (LT, tỉ lệ, vật phẩm tiệm)
HM_STEPS = [(1000, .95), (5000, .9), (20000, .85), (80000, .75), (300000, .65), (1e6, .55), (5e6, .45)]
HM_LV = [0, 0, 100, 100, 150, 150, 200]
GEAR_SET = {1: 2100, 10: 4200, 30: 6300, 60: 8400, 120: 10500, 200: 11400, 300: 13300, 400: 15200, 500: 13500, 600: 17100}  # trọn bộ ở tiệm (LT)
PET_MAPS = [(0, 1000, 20, .80), (1, 2500, 50, .65), (2, 5000, 80, .50), (3, 7000, 100, .35), (4, 10000, 150, .20)]  # (phẩm, bùa LT, MT, tỉ lệ)
PET_K = [2, 4, 6, 9, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55]
def pet_need(lv):
    n = lv + 1
    return n * (n + round(n / 2)) * 50 * PET_K[min(lv // 50, len(PET_K) - 1)] + n
def ch_cost(q): return (3 * q + 1) * 50 + round(q / 2) * round(q / 3) * 2 * round(q / 4) + 1
def ch_p(q): return max(0.001, (1 - q / 35) * min(1, (31 - q) / 30))

def sim(days, online_h, seed=1, hunt=True):
    random.seed(seed)
    owned = dict((s, 0) for s in range(1, 16))   # bậc trang bị đang mặc theo ô
    def gb():
        if not hunt: return None
        return sum(slot_val(owned[s], s)[0] for s in owned)
    def gc():
        if not hunt: return min(30, 3 * tier(L, CULT_PCT))
        return min(30, sum(slot_val(owned[s], s)[1] for s in owned))
    def drop_mob(n):
        T = tkey(L)
        if not hunt or T < 60: return
        k = int(n) + (1 if random.random() < n - int(n) else 0)
        for _ in range(k):
            if random.random() < MOB_DROP:
                sl = random.choice(random.choice(NORMAL_GROUPS))
                owned[sl] = max(owned[sl], T)
    def drop_boss(n, bl):
        T = tkey(bl)
        if not hunt or T < 60: return
        for _ in range(int(n)):
            if random.random() < BOSS_DROP:
                sl = random.choice(SPECIAL_BREAK + SPECIAL_CULT)
                owned[sl] = max(owned[sl], T)
    L, exp, lt, mt = 1, 0.0, 2000.0, 0
    S = dict(mt_pet=0, lt_in=0, lt_boss=0, lt_mob=0, lt_quest=0, lt_tower=0, lt_tupo=0, lt_cult=0, lt_feat=0, mt_boss=0, mt_quest=0,
             e_cult=0, e_mob=0, e_quest=0, e_tower=0, tupo_try=0, tupo_ok=0, lv_lost=0, kills=0, boss=0, tf=0)
    F = dict(gear=0, pet_q=-1, pet_lv=1, pet_x=0.0, lc=[0] * 5, lc_stage=0, tf_lv=0, tf_q=0, tf_open=0, hm=0, tc=0, ch=0, wg_dj=0, wg_x=0.0, wg_max=0.0, cp=0)
    stamina, tower_best, tower_now, ch_done = 100, 0, 0, 0
    out = {}
    def add_lt(v, k): nonlocal lt; lt += v; S[k] += v; S['lt_in'] += v
    def add_mt(v, k): nonlocal mt; mt += v; S[k] += v
    def add_exp(v, k):
        nonlocal exp
        if exp >= need(L): return
        if k == 'e_cult': v = min(v, need(L))
        exp += v; S[k] += v
    def try_tupo(wd):
        nonlocal L, exp, lt
        while exp >= need(L):
            c = tupo_cost(L)
            if lt < c: return
            lt -= c; S['lt_tupo'] += c; S['tupo_try'] += 1
            a, b = random.randint(1, 100), random.randint(1, 100)
            if b <= rate(L, wd, gb()):
                exp -= need(L); L += 1; S['tupo_ok'] += 1; S['tf'] += 5
                inner = L % 50
                if inner == 1 and L > 1: add_exp(need(L) * .15, 'e_quest'); add_lt(10 * L * (1 + L / 100), 'lt_quest'); add_mt(10 * (L / 10 + 3), 'mt_quest')
                elif inner in (10, 20, 30, 40): add_exp(need(L) * .05, 'e_quest'); add_lt(2 * L * (1 + L / 100), 'lt_quest')
            elif a <= 1: exp = 0; L = max(1, L - 1); S['lv_lost'] += 1; return
            elif a <= 2: exp = 0; return
            elif a <= 3: exp /= 4; return
            elif a <= 4: exp /= 2; return
    def cult_tick(minutes, wd, allow_lt=True):
        nonlocal lt
        per_min = math.ceil(L ** 1.15 / 5)
        mult = 1 + gc() / 100
        if exp >= need(L): try_tupo(wd)
        if exp >= need(L): return
        if allow_lt and lt >= per_min * minutes * 3 + tupo_cost(L) * 3:
            lt -= per_min * minutes; S['lt_cult'] += per_min * minutes; add_exp(L * 2 * mult * minutes * 60, 'e_cult')
        else:
            add_exp(L * mult * minutes * 60, 'e_cult')
    def kill(n):
        nonlocal stamina
        S['kills'] += n
        drop_mob(n)
        pet_gain(n, n * mob_exp(L))
        add_exp(n * mob_exp(L), 'e_mob'); add_lt(n * (5 * L + 100), 'lt_mob')
        F['cp'] += n * mob_exp(L) * 0.02
        # võ công: kinh nghiệm/con = max(5, min(cấp quái, 1% wgxlmax)); lên cấp cần đủ wgxlmax + (cấp+1) bí tịch (mua 500 LT)
        F['wg_x'] += n * max(5, min(L, round(F['wg_max'] * 0.01)))
        while L >= 10 and F['wg_x'] >= F['wg_max'] and lt > (F['wg_dj'] + 1) * 500:
            F['wg_x'] -= F['wg_max']; F['wg_dj'] += 1; spend((F['wg_dj']) * 500)
            F['wg_max'] += max(10, round(0.2 * F['wg_max']))
    def pet_gain(events, e):
        # linh sủng nhận cùng tu vi; mỗi lần hạ quái / boss lên tối đa 1 cấp (player.php:2581)
        if F['pet_q'] < 0: return
        F['pet_x'] += e; ups = 0
        while F['pet_x'] >= pet_need(F['pet_lv']) and ups < events:
            F['pet_x'] -= pet_need(F['pet_lv']); F['pet_lv'] += 1; ups += 1
    def spend(v):
        nonlocal lt
        lt -= v; S['lt_feat'] += v
    def features():
        nonlocal mt
        reserve = tupo_cost(L) * 20
        # trang bị: mua trọn bộ bậc mới ở Thương Điếm khi đủ cấp
        t = max(k for k in GEAR_SET if k <= L)
        if t > F['gear'] and lt >= GEAR_SET[t] and (t <= 30 or not hunt):   # tiệm chỉ bán bậc <= 30
            spend(GEAR_SET[t]); F['gear'] = t
            for s_ in owned: owned[s_] = max(owned[s_], t)
        # linh sủng (Sào Thú, cấp 60): bắt phẩm cao nhất mà kỳ vọng MT <= 1/2 số MT đang có; đổi khi bắt được phẩm cao hơn
        if L >= 60:
            for q, blt, bmt, r in reversed(PET_MAPS):
                if q <= F['pet_q']: break
                if mt * 0.5 >= bmt / r and lt - reserve >= blt / r:
                    mt -= bmt / r; S['mt_pet'] += bmt / r; spend(blt / r)
                    F['pet_q'] = q; F['pet_lv'] = 1; F['pet_x'] = 0
                    break
        # linh căn (51+): 5 hệ đều nhau, đột phá bậc khi chạm trần
        if L >= 51:
            while True:
                i = min(range(5), key=lambda k: F['lc'][k]); lv = F['lc'][i]
                cap, brk = LC_STAGE[F['lc_stage']]
                if lv >= 90: break
                if lv >= cap:
                    if lt - reserve < brk: break
                    spend(brk); F['lc_stage'] += 1; continue
                c = lc_cost(lv)
                if lt - reserve < c: break
                spend(c); F['lc'][i] += 1
        # thiên phú (75+): mở 1 thiên phú (Khai Phú Đan 50k), lên cấp bằng điểm thiên phú + LT, phá phẩm
        if L >= 75:
            if not F['tf_open'] and lt - reserve >= 50000: spend(50000); F['tf_open'] = 1
            while F['tf_open']:
                q = F['tf_q']
                if F['tf_lv'] < TF_CAP[q]:
                    c = 200 + 25 * F['tf_lv']
                    if S['tf'] < 1 or lt - reserve < c: break
                    S['tf'] -= 1; spend(c); F['tf_lv'] += 1
                elif q < len(TF_BREAK):
                    t, c, lvn = TF_BREAK[q]
                    if S['tf'] < t or lt - reserve < c or L < lvn: break
                    S['tf'] -= t; spend(c); F['tf_q'] += 1
                else: break
        # huyết mạch (101+) / thể chất (120+): kỳ vọng chi phí = phí / tỉ lệ
        if L >= 101:
            while F['hm'] < len(HM_STEPS) and L >= HM_LV[F['hm']]:
                c, p = HM_STEPS[F['hm']]
                if lt - reserve < c / p: break
                spend(c / p); F['hm'] += 1
        if L >= 120:
            while F['tc'] < len(TC_STEPS):
                c, p, it = TC_STEPS[F['tc']]
                if lt - reserve < (c + it) / p: break
                spend((c + it) / p); F['tc'] += 1
        # cường hóa: 1 món chính, kỳ vọng chi phí mỗi bậc = phí / tỉ lệ
        while F['ch'] < min(L - 1, 30):
            c = ch_cost(F['ch']) / ch_p(F['ch'])
            if lt - reserve < c: break
            spend(c); F['ch'] += 1

    for day in range(days):
        wd = day % 7
        bar = need(L)
        add_exp(bar * .20, 'e_quest'); add_lt(5 * 240 * (1 + L / 100) + 500, 'lt_quest'); add_mt(5, 'mt_quest')
        if wd == 6: add_exp(bar * 1.4, 'e_quest'); add_lt(16200 * (1 + L / 100), 'lt_quest'); add_mt(20, 'mt_quest')
        if day == 29: add_exp(bar * 3.5, 'e_quest'); add_lt(200000 * (1 + L / 100), 'lt_quest'); add_mt(400, 'mt_quest')
        mins = online_h * 60
        add_lt(sum(m for t, m in ((10, 2), (30, 4), (60, 6), (120, 8), (240, 10)) if mins >= t) * L, 'lt_quest')
        if mins >= 240: add_mt(3, 'mt_quest')
        if mins >= 60: stamina += 20
        # Tỏa Yêu Tháp: thứ 2 về 80% đỉnh; leo tới tầng cấp-8 (≤150)
        if wd == 0: tower_now = int(tower_best * 0.8)
        top = max(0, min(150, L - 8)) if L >= 30 else 0
        for f in range(tower_now + 1, top + 1):
            add_exp(140 + 42 * f + 2.2 * f * f, 'e_tower'); add_lt(50 + 5 * f + (50 if f % 2 == 0 else 0) + (200 if f % 10 == 0 else 0), 'lt_tower')
        tower_now = max(tower_now, top); tower_best = max(tower_best, tower_now)
        # offline: thể lực hồi tới trần; bế quan; 1 phiên tu luyện LT 180' hoặc thường 30'
        off_h = 24 - online_h
        stamina = min(100 + L - 1, stamina + off_h * 60)
        bq = max(0, min(8, off_h - 3)) if L >= 10 else 0
        add_exp(need(L) * 0.02 * bq, 'e_quest')
        per_min = math.ceil(L ** 1.15 / 5)
        if lt >= per_min * 180 * 3 + tupo_cost(L) * 3:
            cult_tick(180, wd)
        else:
            cult_tick(30, wd, False)
        try_tupo(wd)
        for h in range(int(online_h)):
            left = 3600
            # boss cùng tầm cấp (chênh < 35) — LT + MT
            own = boss_hp(L)
            for bl, (hp, ms) in BOSS.items():
                if bl > L or L - bl >= 35: continue
                t = max(1, math.ceil(50 * hp / own)) * CLICK + TRAVEL
                n = min(3600 // ms, left // t)
                if n > 0:
                    left -= n * t; S['boss'] += n
                    drop_boss(n, bl)
                    pet_gain(n, n * ((hp / 50) * 0.02))   # tu vi boss nhỏ (công boss/2 + công/thủ người chơi/2) — ước lượng
                    add_lt(n * (90 * bl + 1330), 'lt_boss'); add_mt(n * (3 + round(bl / 10)), 'mt_boss')
            # quái cùng cấp: 1 thể lực / đòn, 5,5 đòn / con
            stamina = min(100 + L - 1 + 40, stamina + 60)
            for _t in range(12):
                n = min(stamina / HITS_MOB, (left / 12) / (HITS_MOB * CLICK + FIND))
                stamina -= n * HITS_MOB
                kill(n)
                cult_tick(5, wd)
            try_tupo(wd)
        for chL in (10, 25, 50, 60, 78, 92, 106, 150, 200, 250, 300, 400, 500, 550, 600):
            if L >= chL and chL > ch_done: add_exp(need(L) * 0.9, 'e_quest'); add_lt(10 * chL * 6 * (1 + L / 100), 'lt_quest'); ch_done = chL
        try_tupo(wd)
        features()
        if day + 1 in (1, 3, 7, 14, 30):
            cpq = 0; x = F['cp']; nd = 100
            while cpq < 10 and x >= nd: x -= nd; cpq += 1; nd *= 1.3
            out[day + 1] = dict(L=L, lt=int(lt), mt=int(mt), rate=rate(L, 0, gb()), gb=gb() if hunt else 3 * tier(L, BREAK_PCT), gc=gc(), cur_t=tkey(L), cur_n=sum(1 for s_ in owned if owned[s_] >= tkey(L)) if hunt else 15, spec_n=sum(1 for s_ in SPECIAL_BREAK + SPECIAL_CULT if owned[s_] >= 60) if hunt else 6, lc=min(F['lc']), lc_stage=F['lc_stage'], tf_lv=F['tf_lv'], tf_q=F['tf_q'] + 1 if F['tf_open'] else 0,
                                hm=F['hm'] + 1 if L >= 101 else 0, gear=F['gear'], pet_q=F['pet_q'], pet_lv=F['pet_lv'] if F['pet_q'] >= 0 else 0, tc=F['tc'] + 1 if L >= 120 else 0, ch=F['ch'], wg=F['wg_dj'], cp=cpq, tower=tower_best,
                                **{k: int(v) for k, v in S.items()})
    return out

if __name__ == '__main__':
    for name, h in (('Nhe 2h', 2), ('Vua 5h', 5), ('Cay 10h', 10)):
        for hunt in (False, True):
            res = [sim(30, h, seed=x, hunt=hunt) for x in range(30)]
            for d in (7, 14, 30):
                rs = [r[d] for r in res]
                a = lambda k: sum(x[k] for x in rs) / len(rs)
                print('%-7s %-9s ngay %2d: cap %4.0f (%d-%d) | ty le DP %4.1f%% | do: +%4.1f%% dot pha, +%4.1f%% tu luyen | bac hien %4.0f co %4.1f/15 o | o quy (>=bac60) %3.1f/6 | thu DP %4.0f tut %.1f | LT con %8.0f MT %5.0f' % (
                    name, 'san do' if hunt else 'du bo(cu)', d, a('L'), min(x['L'] for x in rs), max(x['L'] for x in rs), a('rate'), a('gb'), a('gc'), a('cur_t'), a('cur_n'), a('spec_n'),
                    a('tupo_try'), a('lv_lost'), a('lt'), a('mt')))
