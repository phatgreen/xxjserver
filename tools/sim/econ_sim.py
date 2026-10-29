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
            if L <= b:
                # tupo.php làm tròn 2 phần trừ tới 0,1 (90% cấp độ / 10% cảnh giới) — giữ đúng để khớp % hiển thị
                pen = 75 - (va + (vb - va) * (L - a) / (b - a)); lp = round(pen * 0.9, 1)
                base = 75 - lp - round(pen - lp, 1); break
    return min(99, max(5, int(base + (10 if L % 50 == 0 else 0) + (3 * tier(L, BREAK_PCT) if gb is None else gb) + {5: 5, 6: 10}.get(wd, 0))))
def mob_exp(L): return 107 * L if L <= 500 else 110 * L * L / 400
# tupo.php: istupo()==1 (đổi cảnh giới/giai: cấp kế chia hết cho 10, hoặc đang ở mốc 50) ⇒ 3·L²; còn lại L·(L+1) — đo thật moc_crosscheck.php
def tupo_cost(L): return L * L * 3 if L % 50 == 0 or (L + 1) % 10 == 0 else L * (L + 1)

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

# ---- tính năng bổ sung (full=True) — docs/BAO_CAO_MOC_THOI_GIAN_2026-10-29.md mục 8 ----
BOSS_PILL = {30: 50000, 45: 150000, 60: 200000, 70: 200000}   # boss → giá NPC 1 Đột Phá Đan rơi ra (bossdj 1,20/1,21/1,22; bagdj.php bán = djyxb)
PILL_PER_KILL = 0.10 * 0.5 * 7.5        # djjv 10% × chọn 1/2 món × 3–12 viên (boss.php) — rơi đồ KHÔNG bị chặn chênh cấp
LC_BRK = [(1, 5000, 0), (2, 20000, 5000), (4, 80000, 20000), (8, 250000, 80000)]   # đột phá linh căn MỖI HỆ: Bình Anh, LT, tu vi (linhcan.php:537-583)
GEAR_RAR = {1: 1, 10: 2, 30: 3, 60: 4, 120: 5, 200: 6, 300: 7, 400: 8, 500: 9, 600: 9}
GEAR_HALF = {1: 1.7, 10: 3.8, 30: 12.3, 60: 56.3, 120: 201.6, 200: 501.8, 300: 1104.3, 400: 1940.9, 500: 2154.5, 600: 4379.8}   # (gj+fy)/2 trung bình
def gear_salvage(T): return max(GEAR_RAR[T] * 50, 10 * (GEAR_HALF[T] + 0.18) - 20)   # đồ dư: bán NPC hoặc phân giải ra đá (10 LT/viên), lấy cái lợi hơn (bagzb.php)
def ugj_est(L): return boss_hp(L) / 50 + 10.5 * L   # công ước lượng khớp giả định "boss cùng cấp 50 đòn" (boss DEF ≈ 14·cấp)

def sim(days, online_h, seed=1, hunt=True, marks=(1, 3, 7, 14, 30), cult_bonus=0, full=False, sell_pill=True):
    # marks: ngày ghi số liệu; cult_bonus: % EXP tu luyện cộng thêm (Tông Môn)
    # full: thêm Boss Linh Căn, Hầm Mỏ, Vấn Đỉnh Đài, Tông Môn tự lập, Luyện Đan / Chế Tạo, bán đồ dư, Đột Phá Đan rơi từ boss,
    #       sửa linh căn đột phá theo từng hệ, Huyết Mạch bế tắc với F2P; sell_pill: bán Đột Phá Đan cho NPC (luật hiện tại)
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
                if full and owned[sl] >= T: add_lt(gear_salvage(T), 'lt_sell')
                owned[sl] = max(owned[sl], T)
    def drop_boss(n, bl):
        T = tkey(bl)
        if not hunt or T < 60: return
        for _ in range(int(n)):
            if random.random() < BOSS_DROP:
                sl = random.choice(SPECIAL_BREAK + SPECIAL_CULT)
                if full and owned[sl] >= T: add_lt(gear_salvage(T), 'lt_sell')
                owned[sl] = max(owned[sl], T)
    L, exp, lt, mt = 1, 0.0, 2000.0, 0
    S = dict(mt_pet=0, lt_in=0, lt_boss=0, lt_mob=0, lt_quest=0, lt_tower=0, lt_tupo=0, lt_cult=0, lt_feat=0, mt_boss=0, mt_quest=0,
             e_cult=0, e_mob=0, e_quest=0, e_tower=0, tupo_try=0, tupo_ok=0, lv_lost=0, kills=0, boss=0, tf=0,
             lt_pill=0, lt_sell=0, lt_mine=0, lt_vdd=0, lt_lcb=0, lt_club=0, mt_vdd=0, mt_club=0, e_club=0, lcb=0, pills=0, cboss=0, ld=0)
    F = dict(gear=0, pet_q=-1, pet_lv=1, pet_x=0.0, lc=[0] * 5, lc_stage=0, tf_lv=0, tf_q=0, tf_open=0, hm=0, tc=0, ch=0, wg_dj=0, wg_x=0.0, wg_max=0.0, cp=0,
             lc_st=[0] * 5, st=0.0, ba=0.0, lcw=0, brk_week=0, club=0, clv=0, cexp=0.0, cd=set())
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
    def cb(): return cult_bonus + (F['clv'] if full else 0)   # Tông Môn: +cấp% EXP tu luyện (xiulian.php)
    def cult_tick(minutes, wd, allow_lt=True):
        nonlocal lt
        per_min = math.ceil(L ** 1.15 / 5)
        mult = (1 + gc() / 100) * (1 + cb() / 100)
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
        nonlocal mt, exp
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
        if L >= 51 and full:
            # đột phá theo TỪNG hệ (Bình Anh + LT + tu vi), đá hệ / Bình Anh từ Boss Linh Căn dùng trước, thiếu mua tiệm (50 / 100 LT)
            while True:
                i = min(range(5), key=lambda k: F['lc'][k]); lv = F['lc'][i]
                if lv >= 90: break
                stg = F['lc_st'][i]
                if lv >= LC_STAGE[stg][0]:
                    ba, blt, bx = LC_BRK[stg]; bu = min(ba, F['ba']); c = blt + (ba - bu) * 100
                    if lt - reserve < c or exp < bx: break
                    F['ba'] -= bu; spend(c); exp -= bx; F['lc_st'][i] += 1; F['brk_week'] = 1; continue
                ns = min(7, 1 + lv // 15); use = min(ns, F['st']); c = (ns - use) * 50 + 100 + 15 * lv
                if lt - reserve < c: break
                F['st'] -= use; spend(c); F['lc'][i] += 1
        elif L >= 51:
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
        if L >= 101 and not full:   # full: Huyết Thạch (112) không mua được, chỉ boss Huyết Mạch rơi mà boss cần thức tỉnh bằng Huyết Thạch ⇒ F2P kẹt
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
        if full:
            # Luyện Đan (15+): ~4 lò/ngày (đủ nhiệm vụ ngày/tuần), kỳ vọng 404 LT/lò; dừng ở ~115 lò vì độc tính luyện đan không giảm
            if L >= 15:
                k = min(4, 115 - S['ld'])
                if k > 0 and lt - reserve >= 404 * k: spend(404 * k); S['ld'] += k
            # Chế Tạo (200+): Thánh Phi Phong ô 8 bậc 200, 250 LT, hỏng không mất nguyên liệu
            if L >= 200 and owned[8] < 200 and lt - reserve >= 250: spend(250); owned[8] = 200
            # Tông Môn tự lập (30+, 200k LT) — ưu tiên sau mọi tính năng; nâng cấp khi đủ LT + MT + EXP tông môn (quyên góp 10 LT / 1 MT = 1 EXP)
            if L >= 30 and not F['club'] and lt - reserve >= 200000: spend(200000); F['club'] = 1; F['clv'] = 1
            if F['club'] and F['clv'] < 10:
                up_lt, up_mt, up_ex = CLUB_UP[F['clv']]
                if lt - reserve >= up_lt and mt - 1500 >= up_mt and F['cexp'] + up_lt / 10 + up_mt >= up_ex:
                    spend(up_lt); mt -= up_mt; S['mt_club'] -= up_mt
                    F['cexp'] += up_lt / 10 + up_mt - up_ex; add_exp(up_lt / 10 + up_mt, 'e_club'); F['clv'] += 1

    for day in range(days):
        wd = day % 7
        bar = need(L)
        if full and L >= 80 and random.random() < 5 / 8:
            # D8 (thắng PvP 3) vào bộ 5/8 nhưng máy chủ 1 người không có đối thủ ⇒ mất 1 nhiệm vụ + thưởng đủ 5
            add_exp(bar * .12, 'e_quest'); add_lt(4 * 240 * (1 + L / 100), 'lt_quest')
        else:
            add_exp(bar * .20, 'e_quest'); add_lt(5 * 240 * (1 + L / 100) + 500, 'lt_quest'); add_mt(5, 'mt_quest')
        e_cult0 = S['e_cult']
        if full:
            for key, lv_, pct, lt_ in (('phoban', 30, .02, 200), ('hammo', 60, .03, 400), ('vdd', 80, .03, 500)):   # Cơ Duyên lần đầu ghé
                if L >= lv_ and key not in F['cd']: F['cd'].add(key); add_exp(need(L) * pct, 'e_quest'); add_lt(lt_, 'lt_quest')
            if L >= 60:   # Hầm Mỏ: 5 lượt miễn phí (lượt 6–30 tốn 5 MT, không đáng) — LT + Cường Hóa Thạch (quy 50 LT) + đá hệ
                add_lt(5 * (96 + 0.465 * min(20, int(math.sqrt(L)))) + 5 * 0.255 * 50, 'lt_mine'); add_mt(5 * 0.005, 'mt_quest'); F['st'] += 5 * 0.06
            if L >= 80 and F.get('vdd_day', day) < day:   # Vấn Đỉnh Đài: 1 người ⇒ không có trận, nhưng giữ hạng 1 ⇒ 10.000 LT + 100 MT/ngày (21:00)
                add_lt(10000, 'lt_vdd'); add_mt(100, 'mt_vdd')
            if L >= 80 and 'vdd_day' not in F: F['vdd_day'] = day
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
        # bế quan và tu luyện offline loại trừ nhau (online_reward.php: đang tu luyện thì không tính bế quan) ⇒ chọn cái lợi hơn;
        # phiên tu luyện offline dài tối đa bằng số giờ offline (online 24h thì không còn phiên offline)
        bq = max(0, min(8, off_h - 3)) if L >= 10 else 0
        per_min = math.ceil(L ** 1.15 / 5)
        use_lt = lt >= per_min * 180 * 3 + tupo_cost(L) * 3
        off_min = min(180 if use_lt else 30, off_h * 60)
        mult = (1 + gc() / 100) * (1 + cb() / 100)
        if off_min > 0 and min(need(L), L * (2 if use_lt else 1) * mult * off_min * 60) > need(L) * 0.02 * bq:
            cult_tick(off_min, wd, use_lt)
        else:
            add_exp(need(L) * 0.02 * bq, 'e_quest')
        try_tupo(wd)
        for h in range(int(online_h)):
            left = 3600
            # boss cùng tầm cấp (chênh < 35) — LT + MT
            own = boss_hp(L)
            for bl, (hp, ms) in BOSS.items():
                far = L - bl >= 35
                if bl > L or (far and not (full and sell_pill and bl in BOSS_PILL)): continue   # chênh >= 35: chỉ còn đáng săn để lấy đan
                t = max(1, math.ceil(50 * hp / own)) * CLICK + TRAVEL
                n = min(3600 // ms, left // t)
                if n > 0:
                    left -= n * t; S['boss'] += n
                    drop_boss(n, bl)
                    pet_gain(n, n * ((hp / 50) * 0.02))   # tu vi boss nhỏ (công boss/2 + công/thủ người chơi/2) — ước lượng
                    if not far: add_lt(n * (90 * bl + 1330), 'lt_boss'); add_mt(n * (3 + round(bl / 10)), 'mt_boss')
                    if full and bl in BOSS_PILL:
                        S['pills'] += n * PILL_PER_KILL
                        if sell_pill: add_lt(n * PILL_PER_KILL * BOSS_PILL[bl], 'lt_pill')
            # quái cùng cấp: 1 thể lực / đòn, 5,5 đòn / con
            stamina = min(100 + L - 1 + 40, stamina + 60)
            used = 0
            for _t in range(12):
                n = min(stamina / HITS_MOB, (left / 12) / (HITS_MOB * CLICK + FIND))
                stamina -= n * HITS_MOB; used += n * (HITS_MOB * CLICK + FIND)
                kill(n)
                cult_tick(5, wd)
            if full and L >= 51:
                # Boss Linh Căn thế giới: chết là hồi ngay (linhcan_boss_check_spawn), không tốn thể lực ⇒ dùng hết thời gian còn lại
                d_ = max(1, ugj_est(L) - 0.75 * (100 + 10 * L))
                t_ = math.ceil((5000 + 500 * L) / d_) * CLICK + TRAVEL
                n = int(max(0, left - used) // t_)
                S['lcb'] += n; F['lcw'] += n; add_lt(n * 300, 'lt_lcb'); F['st'] += n * 3; F['ba'] += n * 1.5
            try_tupo(wd)
        for chL in (10, 25, 50, 60, 78, 92, 106, 150, 200, 250, 300, 400, 500, 550, 600):
            if L >= chL and chL > ch_done: add_exp(need(L) * 0.9, 'e_quest'); add_lt(10 * chL * 6 * (1 + L / 100), 'lt_quest'); ch_done = chL
        try_tupo(wd)
        if full and F['club']:
            # boss tông môn 1 lần/ngày, tự đánh 1 mình (3 giây/đòn): được nếu hạ trong 25% thời gian online
            chp = 100000 + 500000 * (F['clv'] - 1); dmg = 0.85 * max(1, ugj_est(L) - 0.75 * (1500 + 5 * (F['clv'] - 1)))
            if chp / dmg * 3 <= online_h * 3600 * 0.25:
                S['cboss'] += 1; add_exp(4700, 'e_club'); add_lt(1750, 'lt_club'); add_mt(65, 'mt_club'); F['cexp'] += 550
            F['cexp'] += 0.01 * (S['e_cult'] - e_cult0) + 1200 + 2144   # 1% tu vi tu luyện + online 24h + nhiệm vụ tông môn (chỉ cho EXP tông môn)
        if full and wd == 6:   # nhiệm vụ tuần Linh Căn: cần đột phá 1 bậc trong tuần + hạ boss
            if L >= 51 and F['brk_week'] and F['lcw'] >= 1: add_lt(500, 'lt_quest'); F['ba'] += 1.5
            F['brk_week'] = 0; F['lcw'] = 0
        features()
        if day + 1 in marks:
            cpq = 0; x = F['cp']; nd = 100
            while cpq < 10 and x >= nd: x -= nd; cpq += 1; nd *= 1.3
            out[day + 1] = dict(L=L, lt=int(lt), mt=int(mt), rate=rate(L, 0, gb()), gb=gb() if hunt else 3 * tier(L, BREAK_PCT), gc=gc(), cur_t=tkey(L), cur_n=sum(1 for s_ in owned if owned[s_] >= tkey(L)) if hunt else 15, spec_n=sum(1 for s_ in SPECIAL_BREAK + SPECIAL_CULT if owned[s_] >= 60) if hunt else 6, lc=min(F['lc']), lc_stage=min(F['lc_st']) if full else F['lc_stage'], clv=F['clv'], tf_lv=F['tf_lv'], tf_q=F['tf_q'] + 1 if F['tf_open'] else 0,
                                hm=F['hm'] + 1 if L >= 101 else 0, gear=F['gear'], pet_q=F['pet_q'], pet_lv=F['pet_lv'] if F['pet_q'] >= 0 else 0, tc=F['tc'] + 1 if L >= 120 else 0, ch=F['ch'], wg=F['wg_dj'], cp=cpq, tower=tower_best,
                                **{k: int(v) for k, v in S.items()})
    return out

# ---- Tông Môn (docs/DESIGN_TONGMON_CANBANG.md) — chi phí nâng cấp + nguồn EXP lấy từ game/club.php ----
CLUB_UP = {1: (1000000, 2000, 50000), 2: (2000000, 4000, 150000), 3: (3500000, 6000, 400000), 4: (5500000, 9000, 1000000),
           5: (8000000, 13000, 2000000), 6: (11000000, 18000, 4000000), 7: (15000000, 24000, 7000000), 8: (20000000, 32000, 11000000),
           9: (27000000, 42000, 16000000)}
CLUB_QUEST_AVG, CLUB_QUEST_N = 427, 5      # trung bình reward_exp bảng club_quest_config, 4–6 nhiệm vụ/ngày
def club_sim(mix, days, give_lt=0.2, give_mt=0.3, meditate=0.01, seed=1):
    # mix: danh sách giờ online mỗi thành viên. Thành viên góp give_lt LT + give_mt MT thu được mỗi ngày (giả định: góp
    # không làm chậm lên cấp của họ — lạc quan). EXP tông môn/ngày = meditate × tu vi tu luyện + nhiệm vụ + online 24h×50
    # + boss (500 + 50×N) + quyên góp (10 LT / 1 MT = 1 EXP).
    runs = [sim(days, h, seed=seed + i, hunt=True, marks=tuple(range(1, days + 1))) for i, h in enumerate(mix)]
    n = len(mix); lv, lt, mt, ex = 1, 0.0, 0.0, 0.0; prev = [dict(lt_in=0, mt=0, e_cult=0) for _ in mix]; at = {}
    for d in range(1, days + 1):
        for i, r in enumerate(runs):
            x = r[d]; m_in = x['mt_boss'] + x['mt_quest']
            dlt, dmt, dcu = x['lt_in'] - prev[i]['lt_in'], m_in - prev[i]['mt'], x['e_cult'] - prev[i]['e_cult']
            prev[i] = dict(lt_in=x['lt_in'], mt=m_in, e_cult=x['e_cult'])
            lt += dlt * give_lt; mt += dmt * give_mt
            ex += dcu * meditate + CLUB_QUEST_AVG * CLUB_QUEST_N + 24 * 50 + dlt * give_lt * 0.1 + dmt * give_mt
        ex += 500 + 50 * n
        while lv < 10 and lt >= CLUB_UP[lv][0] and mt >= CLUB_UP[lv][1] and ex >= CLUB_UP[lv][2]:
            lt -= CLUB_UP[lv][0]; mt -= CLUB_UP[lv][1]; ex -= CLUB_UP[lv][2]; lv += 1; at[lv] = d
    return lv, at

if __name__ == '__main__':
    import sys
    if '--tongmon' in sys.argv:
        # 10 thành viên: 3 nhẹ 2h, 5 vừa 5h, 2 cày 10h; 180 ngày
        mix = [2, 2, 2, 5, 5, 5, 5, 5, 10, 10]
        lv, at = club_sim(mix, 180)
        print('Tong mon 10 nguoi (3x2h, 5x5h, 2x10h), gop 20%% LT + 30%% MT: cap %d sau 180 ngay; ngay dat cap: %s' % (lv, at))
        for b in (0, 5, 10):
            rs = [sim(30, 5, seed=x, hunt=True, cult_bonus=b) for x in range(10)]
            print('Vua 5h, EXP tu luyen +%2d%%: cap ngay 30 = %.1f' % (b, sum(r[30]['L'] for r in rs) / len(rs)))
        sys.exit(0)
    for name, h in (('Nhe 2h', 2), ('Vua 5h', 5), ('Cay 10h', 10)):
        for hunt in (False, True):
            res = [sim(30, h, seed=x, hunt=hunt) for x in range(30)]
            for d in (7, 14, 30):
                rs = [r[d] for r in res]
                a = lambda k: sum(x[k] for x in rs) / len(rs)
                print('%-7s %-9s ngay %2d: cap %4.0f (%d-%d) | ty le DP %4.1f%% | do: +%4.1f%% dot pha, +%4.1f%% tu luyen | bac hien %4.0f co %4.1f/15 o | o quy (>=bac60) %3.1f/6 | thu DP %4.0f tut %.1f | LT con %8.0f MT %5.0f' % (
                    name, 'san do' if hunt else 'du bo(cu)', d, a('L'), min(x['L'] for x in rs), max(x['L'] for x in rs), a('rate'), a('gb'), a('gc'), a('cur_t'), a('cur_n'), a('spec_n'),
                    a('tupo_try'), a('lv_lost'), a('lt'), a('mt')))
