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

# ---- Cấu hình đổi / rơi ----
save('tiendao_config.json', {
    '_note': 'Sinh bởi tools/gen/dot_d_build.py — xem docs/DOT_D_TIENDAO_TANQUYEN.md',
    'djid': DJID,
    'need': NEED,
    'wgid': TIENDAO_WGID,
    'need_by_wgid': {str(k): v for k, v in sorted(NEED_BY_WGID.items())},
    'drop_pct': {str(k): v for k, v in sorted(DROP_PCT.items())},
}, indent=2)

# ---- Đạo cụ ----
# Chèn dạng văn bản để giữ nguyên định dạng các khối viết tay khác trong file.
item = {
    'djname': 'Tiên Đạo Tàn Quyển',
    'djzl': str(DJID),
    'djinfo': 'Tàn trang bí tịch Tiên Đạo, rơi từ Boss khu vực cấp 400 trở lên. '
              'Gom đủ %d quyển đổi 1 bộ võ công Tiên Đạo tại Thiên Cơ Các (cần cảnh giới thứ 30).' % NEED,
    'djid': DJID,
    'djyxb': 0,      # bán cho tiệm không được tiền
    'djjg': 0,
    'no_shop': 1,    # Thương Điếm không bày bán
}
path = os.path.join(CFG, 'daoju_config.json')
text = open(path, encoding='utf-8').read()
# Xóa khối cũ (nếu đã chạy trước đó) rồi chèn lại trước dấu } cuối file
text = re.sub(r',\s*"%d": \{.*?\n    \}(?=\s*\}\s*$)' % DJID, '', text, flags=re.S)
block = json.dumps(item, ensure_ascii=False, indent=4).replace('\n', '\n    ')
text = re.sub(r'\s*\}\s*$', ',\n    "%d": %s\n}\n' % (DJID, block), text, count=1)
json.loads(text)  # kiểm tra JSON hợp lệ trước khi ghi
open(path, 'w', encoding='utf-8').write(text)
print('OK: tiendao_config.json + daoju %d' % DJID)
