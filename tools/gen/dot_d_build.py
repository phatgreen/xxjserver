# -*- coding: utf-8 -*-
"""
Ghi dữ liệu đợt D (Tiên Đạo Tàn Quyển) vào config — chạy lại nhiều lần vẫn cho cùng kết quả.
Thiết kế: docs/DOT_D_TIENDAO_TANQUYEN.md
Chạy: python3 tools/gen/dot_d_build.py
"""
import json, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
def save(n, d, indent=4):
    with open(os.path.join(CFG, n), 'w', encoding='utf-8') as f:
        json.dump(d, f, ensure_ascii=False, indent=indent)
        f.write('\n')

DJID = 115          # Tiên Đạo Tàn Quyển
NEED = 10           # số quyển đổi 1 bộ
DROP_PCT = {400: 3, 450: 4, 500: 5}   # % rơi theo cấp boss khu vực
# Làn 2 (docs/LAN2_CAP_500_1000.md L6): boss 550–700 5%, 750–900 6%, 950–1000 7%
DROP_PCT.update({lv: (5 if lv <= 700 else 6 if lv <= 900 else 7) for lv in range(550, 1001, 50)})
# Làn 3 (docs/LAN3_CAP_1000_1500.md mục 6, chủ dự án: rơi vừa phải): boss 1050–1500 5%
DROP_PCT.update({lv: 5 for lv in range(1050, 1501, 50)})
# Làn 4: boss 1550–2000 5%
DROP_PCT.update({lv: 5 for lv in range(1550, 2001, 50)})
TIENDAO_WGID = [64, 65, 66, 67, 68]
# Làn 4 (docs/LAN4_CAP_1500_2000.md mục 4): 6 bộ Tiên Đạo CG 36 (wgid 75–80, tools/gen/lan4_a_build.py) đổi 15 quyển / bộ
TIENDAO_WGID += [75, 76, 77, 78, 79, 80]
NEED_BY_WGID = {wid: 15 for wid in range(75, 81)}
# Làn 5 (docs/LAN5_CAP_2000_2500.md, phương án A): 6 bộ Hỗn Nguyên CG 42 (wgid 81–86, lan5_a_build.py) đổi bằng
# HỖN NGUYÊN Tàn Quyển (djid 116) 10 quyển / bộ; boss làn 5 (2050–2500) rơi Hỗn Nguyên Tàn Quyển 5% thay Tiên Đạo.
HN_DJID, HN_NEED = 116, 10
HN_WGID = list(range(81, 87))
TIENDAO_WGID += HN_WGID
NEED_BY_WGID.update({wid: HN_NEED for wid in HN_WGID})
DJID_BY_WGID = {wid: HN_DJID for wid in HN_WGID}
DROP_PCT.update({lv: 5 for lv in range(2050, 2501, 50)})
DROP_DJID_BY_LV = {lv: HN_DJID for lv in range(2050, 2501, 50)}
# Làn 6 (docs/LAN6_CAP_2500_3000.md mục 4): 6 bộ Hỗn Nguyên CG 55 (wgid 87–92, lan6_a_build.py) đổi 15 quyển 116 / bộ (bộ CG 42 giữ 10);
# boss làn 6 (2550–3000) rơi Hỗn Nguyên Tàn Quyển 5%.
HN2_WGID = list(range(87, 93))
TIENDAO_WGID += HN2_WGID
NEED_BY_WGID.update({wid: 20 for wid in HN2_WGID})   # chủ dự án 2026-10-28: 15 → 20 (hiếm dần theo làn)
DJID_BY_WGID.update({wid: HN_DJID for wid in HN2_WGID})
DROP_PCT.update({lv: 4 for lv in range(2550, 3001, 50)})   # 5% → 4%
DROP_DJID_BY_LV.update({lv: HN_DJID for lv in range(2550, 3001, 50)})
# Làn 7 (docs/LAN7_CAP_3000_3500.md mục 4, phương án B): 6 bộ Hỗn Nguyên CG 65 (wgid 93–98, lan7_a_build.py) đổi 20 quyển 116 / bộ
# (bộ CG 55 giữ 15, CG 42 giữ 10); boss làn 7 (3050–3500) rơi Hỗn Nguyên Tàn Quyển 5%.
HN3_WGID = list(range(93, 99))
TIENDAO_WGID += HN3_WGID
NEED_BY_WGID.update({wid: 30 for wid in HN3_WGID})   # 20 → 30
DJID_BY_WGID.update({wid: HN_DJID for wid in HN3_WGID})
DROP_PCT.update({lv: 3 for lv in range(3050, 3501, 50)})   # 5% → 3%
DROP_DJID_BY_LV.update({lv: HN_DJID for lv in range(3050, 3501, 50)})
# Chủ dự án 2026-10-28: Tàn Quyển hiếm dần theo làn — làn 6 rơi 4% / bộ CG 55 cần 20, làn 7 rơi 3% / bộ CG 65 cần 30 (đã sửa ở trên),
# làn 8 (docs/LAN8_CAP_3500_4000.md): phẩm mới ĐẠI ĐẠO — 6 bộ CG 72 (wgid 99–104, lan8_a_build.py) đổi 40 Đại Đạo Tàn Quyển (djid 117) / bộ;
# boss làn 8 (3550–4000) rơi Đại Đạo Tàn Quyển 2% thay Hỗn Nguyên.
DD_DJID, DD_NEED = 117, 40
DD_WGID = list(range(99, 105))
TIENDAO_WGID += DD_WGID
NEED_BY_WGID.update({wid: DD_NEED for wid in DD_WGID})
DJID_BY_WGID.update({wid: DD_DJID for wid in DD_WGID})
DROP_PCT.update({lv: 2 for lv in range(3550, 4001, 50)})
DROP_DJID_BY_LV.update({lv: DD_DJID for lv in range(3550, 4001, 50)})
# Làn 9 (docs/LAN9_CAP_4000_4300.md): 6 bộ Đại Đạo CG 83 (wgid 105–110, lan9_a_build.py) đổi 50 quyển 117 / bộ (CG 72 giữ 40);
# boss làn 9 (4050–4300) rơi Đại Đạo Tàn Quyển 1% — tiếp nhịp hiếm dần 5 → 4 → 3 → 2 → 1%.
DD2_WGID = list(range(105, 111))
TIENDAO_WGID += DD2_WGID
NEED_BY_WGID.update({wid: 50 for wid in DD2_WGID})
DJID_BY_WGID.update({wid: DD_DJID for wid in DD2_WGID})
DROP_PCT.update({lv: 1 for lv in range(4050, 4301, 50)})
DROP_DJID_BY_LV.update({lv: DD_DJID for lv in range(4050, 4301, 50)})

# ---- Cấu hình đổi / rơi ----
save('tiendao_config.json', {
    '_note': 'Sinh bởi tools/gen/dot_d_build.py — xem docs/DOT_D_TIENDAO_TANQUYEN.md',
    'djid': DJID,
    'need': NEED,
    'wgid': TIENDAO_WGID,
    'need_by_wgid': {str(k): v for k, v in sorted(NEED_BY_WGID.items())},
    'djid_by_wgid': {str(k): v for k, v in sorted(DJID_BY_WGID.items())},
    'drop_djid_by_lv': {str(k): v for k, v in sorted(DROP_DJID_BY_LV.items())},
    'drop_pct': {str(k): v for k, v in sorted(DROP_PCT.items())},
}, indent=2)

# ---- Đạo cụ ----
# Chèn dạng văn bản để giữ nguyên định dạng các khối viết tay khác trong file.
items = [{
    'djname': 'Tiên Đạo Tàn Quyển',
    'djzl': str(DJID),
    'djinfo': 'Tàn trang bí tịch Tiên Đạo, rơi từ Boss khu vực cấp 400 trở lên. '
              'Gom đủ %d quyển đổi 1 bộ võ công Tiên Đạo tại Thiên Cơ Các (cần cảnh giới thứ 30).' % NEED,
    'djid': DJID,
    'djyxb': 0,      # bán cho tiệm không được tiền
    'djjg': 0,
    'no_shop': 1,    # Thương Điếm không bày bán
}, {
    'djname': 'Hỗn Nguyên Tàn Quyển',
    'djzl': str(HN_DJID),
    'djinfo': 'Tàn trang bí tịch Hỗn Nguyên, rơi từ Boss khu vực cấp 2050 trở lên. '
              'Gom đủ %d–30 quyển (tùy bộ) đổi 1 bộ võ công Hỗn Nguyên tại Thiên Cơ Các (cần cảnh giới thứ 42 trở lên).' % HN_NEED,
    'djid': HN_DJID,
    'djyxb': 0,
    'djjg': 0,
    'no_shop': 1,
}, {
    'djname': 'Đại Đạo Tàn Quyển',
    'djzl': str(DD_DJID),
    'djinfo': 'Tàn trang bí tịch Đại Đạo, rơi rất hiếm từ Boss khu vực cấp 3550 trở lên. '
              'Gom đủ %d–50 quyển (tùy bộ) đổi 1 bộ võ công Đại Đạo tại Thiên Cơ Các (cần cảnh giới thứ 72 trở lên).' % DD_NEED,
    'djid': DD_DJID,
    'djyxb': 0,
    'djjg': 0,
    'no_shop': 1,
}]
path = os.path.join(CFG, 'daoju_config.json')
text = open(path, encoding='utf-8').read()
# Xóa các khối cũ (nếu đã chạy trước đó, ở bất kỳ vị trí nào) rồi chèn lại theo thứ tự trước dấu } cuối file
for it in items:
    text = re.sub(r',\s*"%d": \{.*?\n    \}(?=\s*(,|\}\s*$))' % it['djid'], '', text, flags=re.S)
for it in items:
    block = json.dumps(it, ensure_ascii=False, indent=4).replace('\n', '\n    ')
    text = re.sub(r'\s*\}\s*$', ',\n    "%d": %s\n}\n' % (it['djid'], block), text, count=1)
d = json.loads(text)  # kiểm tra JSON hợp lệ trước khi ghi
assert all(text.count('"%d": {' % i) == 1 for i in (DJID, HN_DJID, DD_DJID))
open(path, 'w', encoding='utf-8').write(text)
print('OK: tiendao_config.json + daoju %d, %d, %d' % (DJID, HN_DJID, DD_DJID))
