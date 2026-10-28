# -*- coding: utf-8 -*-
"""
Phàm Giai (giai 0, chủ dự án 2026-10-28): 4 công pháp nhập môn đứng dưới Hoàng Giai, cpid 73–76, không yêu cầu cảnh giới.
Hiệu ứng nhỏ hơn Hoàng Giai cùng loại; giá theo tools/gen/cp_price_build.py (Phàm = 0,1 tháng tích lũy, mua bằng LT hoặc MT).
Chạy lại nhiều lần cho cùng kết quả. Chạy: python3 tools/gen/cp_pham_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save
import lan5_a_build as L5
import cp_price_build as P

# cpid, tên, hệ, effect, Lv1, Lv10, phẩm, loại, mô tả
CP_PHAM = [
    (73, 'Phàm Thể Luyện Lực Quyết', 'kim', 'atk_bonus', 2, 10, 1, 'Sát Pháp', 'Luyện lực phàm thể|Tăng % công kích|Phàm Thể Luyện Lực Quyết là khẩu quyết rèn sức mà người phàm nào cũng học được, giúp mỗi đòn đánh nặng thêm đôi phần.'),
    (74, 'Phàm Thể Hộ Thân Kinh', 'tho', 'def_bonus', 2, 10, 2, 'Hộ Thể', 'Hộ thân phàm thể|Tăng % phòng ngự|Phàm Thể Hộ Thân Kinh dạy cách vận khí bảo vệ thân thể, là bài học đầu tiên của người mới bước vào tu luyện.'),
    (75, 'Dưỡng Khí Tĩnh Tâm Pháp', 'moc', 'hp_regen', 1, 8, 3, 'Nội Công', 'Dưỡng khí tĩnh tâm|Hồi % khí huyết mỗi lượt|Dưỡng Khí Tĩnh Tâm Pháp giúp tâm bình khí hòa, thương nhẹ tự lành dần trong lúc giao chiến.'),
    (76, 'Nạp Khí Nhập Môn Thuật', 'all', 'absorb_speed', 3, 15, 4, 'Tâm Pháp', 'Nạp khí nhập môn|Tăng tốc độ hấp thu linh khí|Nạp Khí Nhập Môn Thuật là cách hít thở thu nạp linh khí đơn giản nhất, nền móng cho mọi công pháp về sau.'),
]

def build():
    cp = load('congphap_config.json')
    for cpid, n, el, et, b, m, st, srt, info in CP_PHAM:
        cp[str(cpid)] = P.apply({
            'cpid': cpid, 'cpname': n, 'cpys': L5.ELEM_COLOR[el] if el in L5.ELEM_COLOR else '#9e9e9e', 'cpsort': srt, 'cpinfo': info,
            'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
            'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
            'realm_require': 0, 'tier_require': 0, 'quality': 1, 'tier': 0, 'subtier': st,
            'training_time_mult': 1.0, 'price_linhthong': 0, 'price_masthach': 0,
        })
    save('congphap_config.json', cp)
    return [(c[0], c[1]) for c in CP_PHAM]

if __name__ == '__main__':
    print(build())
