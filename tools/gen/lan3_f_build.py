# -*- coding: utf-8 -*-
"""
Làn 3 — L3-F (docs/LAN3_CAP_1000_1500.md mục 3): công thức chế tạo bậc 1000 / 1250 (phẩm Hỗn Độn).
Trang bị bậc 1000/1250 sinh bởi tools/gen/dot_f_build.py (TIERS). Chạy lại nhiều lần cho cùng kết quả:
xóa công thức mã 9–12 (nếu có) rồi chèn lại, giữ nguyên định dạng viết tay của class/config/chetao_config.json.
Chạy: python3 tools/gen/dot_f_build.py && python3 tools/gen/lan3_f_build.py
"""
import json, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
ZB = json.load(open(os.path.join(CFG, 'zhuangbei_config.json'), encoding='utf-8'))

def zbid(tier, slot):
    return min(v['zbid'] for v in ZB.values() if v['zblv'] == tier and v['zbtool'] == slot)

# Cùng kiểu công thức bậc 600/800: Yêu Đai (ô 7) + Phi Phong (ô 8); 32 Hỏa · 33 Thủy · 36 Thổ Tinh Thạch · 37 Bình Anh Tinh Thạch
RECIPES = [
    (9,  zbid(1000, 7), [(1, 6), (36, 4), (37, 1)], 75, 1000),
    (10, zbid(1000, 8), [(1, 6), (33, 4), (37, 1)], 75, 1000),
    (11, zbid(1250, 7), [(1, 7), (36, 5), (37, 2)], 70, 1250),
    (12, zbid(1250, 8), [(1, 7), (32, 5), (37, 2)], 70, 1250),
]

def block(rid, zid, mats, rate, lv):
    m = ',\n'.join('        {"id": %d, "amount": %d}' % x for x in mats)
    return ('    "%d": {\n      "id": %d,\n      "type": "equipment",\n      "result_zbid": %d,\n      "materials": [\n%s\n      ],\n'
            '      "base_success_rate": %d,\n      "level_required": %d\n    }') % (rid, rid, zid, m, rate, lv)

def build():
    p = os.path.join(CFG, 'chetao_config.json')
    text = open(p, encoding='utf-8').read()
    for rid, _, _, _, _ in RECIPES:
        text = re.sub(r',\s*"%d": \{.*?\n    \}(?=\s*[,}])' % rid, '', text, flags=re.S)
    new = ',\n'.join(block(*r) for r in RECIPES)
    text = re.sub(r'\n  \}\s*\}\s*$', ',\n' + new + '\n  }\n}\n', text, count=1)
    d = json.loads(text)  # kiểm tra JSON hợp lệ trước khi ghi
    open(p, 'w', encoding='utf-8').write(text)
    return [(r, ZB[str(d['recipes'][str(r)]['result_zbid'])]['zbname']) for r, _, _, _, _ in RECIPES]

if __name__ == '__main__':
    print('cong thuc lan 3:', build())
