# -*- coding: utf-8 -*-
"""
Làn 5 — L5-A (docs/LAN5_CAP_2000_2500.md mục 4, 5):
  1) 6 võ công phẩm mới HỖN NGUYÊN (rarity hon_nguyen) yêu cầu CG 42 (Vô Thượng Tiên Vương, cấp 2050), wgid 81–86,
     đổi bằng Hỗn Nguyên Tàn Quyển 10 quyển / bộ (tiendao_config.json djid_by_wgid — tools/gen/dot_d_build.py).
     Chỉ số = bộ Tiên Đạo CG 36 cùng hệ (wgid 75–80) ×1,2; hiệu ứng / thế võ tăng có trần.
  2) 4 công pháp Đế Giai (giai 8) CG 45 / 48, cpid 57–60, bán Ma Thạch.
  3) Hệ số thế võ phẩm Hỗn Nguyên: rules.rarity_bonus.hon_nguyen = 0,25 (Tiên Đạo 0,2).
Chạy lại nhiều lần cho cùng kết quả. Chạy: python3 tools/gen/lan5_a_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save
import lan4_a_build as L4

REALM, TMULT, COLOR, STAT_MULT = 42, 40.0, '#c084fc', 1.2
EFFECT_NEW = {'pierce_def': 58, 'hp_regen': 33, 'escape': 98, 'crit_dmg': 92, 'damage_reduce': 55, 'counter_lifesteal': 21}
# hệ -> (wgid, bộ Tiên Đạo CG 36 cùng hệ, tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3])
NEW = {
 'kim': (81, 75, 'Hỗn Nguyên Khai Thiên Kiếm', 'Kiếm Pháp / Hỗn Nguyên', 'Kiếm khai hỗn độn',
         'Xuyên phá phòng ngự tột cùng', 'Khai thiên – phá địa – một kiếm phân âm dương',
         'Hỗn Nguyên Khai Thiên Kiếm mô phỏng nhát kiếm đầu tiên tách trời khỏi đất. Kiếm ý hỗn nguyên không gì cản nổi, mọi lớp phòng ngự đều như giấy mỏng.',
         [('Hỗn Nguyên Kiếm Khí', 'Một đạo kiếm khí hỗn độn xé toạc hư không, chém thẳng vào {mob}.'),
          ('Phân Âm Định Dương', 'Kiếm quang tách đôi âm dương, chém xuyên mọi tầng phòng ngự của {mob}!'),
          ('Khai Thiên Nhất Kiếm', 'Một kiếm tách trời đất, {mob} chỉ còn biết đứng nhìn kiếm quang ập tới!')]),
 'moc': (82, 76, 'Hỗn Nguyên Vạn Sinh Kinh', 'Nội Công / Hỗn Nguyên', 'Vạn vật sinh sôi',
         'Hồi phục khí huyết tột cùng mỗi lượt', 'Sinh sinh bất tức – vạn vật quy về',
         'Hỗn Nguyên Vạn Sinh Kinh lấy sinh khí thuở hồng hoang làm gốc. Người luyện thành, chỉ cần còn một hơi thở là vạn vật đều có thể hồi sinh.',
         [('Vạn Sinh Chưởng', 'Chưởng lực mang sinh khí hồng hoang đánh trúng {mob}.'),
          ('Hỗn Nguyên Tục Mệnh', 'Sinh khí hỗn nguyên tràn vào kinh mạch, vết thương liền lại trước mắt {mob}!'),
          ('Vạn Sinh Quy Nhất', 'Sinh khí vạn vật hội tụ về một thân, vừa trói chặt {mob} vừa hồi phục toàn thân!')]),
 'thuy': (83, 77, 'Hỗn Nguyên Thái Hư Bộ', 'Thân Pháp / Hỗn Nguyên', 'Bước giữa thái hư',
          'Né tránh gần như tuyệt đối, phong tỏa đối thủ', 'Hư vô – phiêu hốt – thân như không tồn tại',
          'Hỗn Nguyên Thái Hư Bộ đưa thân thể vào khoảng thái hư giữa các tầng không gian. Đối thủ đánh tới chỉ chạm vào hư không.',
          [('Thái Hư Chỉ', 'Thân ảnh lóe ra từ hư không, một chỉ điểm thẳng vào {mob}.'),
           ('Thái Hư Ảo Ảnh', 'Thân thể hòa vào thái hư, {mob} đánh vào đâu cũng chỉ trúng khoảng không!'),
           ('Hỗn Nguyên Phong Hư', 'Hư không cuộn lại thành lồng, khóa chặt {mob} giữa thái hư!')]),
 'hoa': (84, 78, 'Hỗn Nguyên Diệt Thế Quyền', 'Quyền Pháp / Hỗn Nguyên', 'Diệt thế hỏa quyền',
         'Bạo kích tột cùng, thiêu đốt vạn vật', 'Hủy diệt – thiêu đốt – một quyền diệt thế',
         'Hỗn Nguyên Diệt Thế Quyền dồn lửa hỗn độn vào nắm đấm, một quyền đủ thiêu rụi cả một phương thế giới.',
         [('Diệt Thế Quyền', 'Một quyền mang lửa hỗn độn đánh thẳng vào {mob}.'),
          ('Hỗn Độn Phần Thiên', 'Lửa hỗn độn bén vào thần hồn {mob}, thiêu đốt từ trong ra ngoài!'),
          ('Diệt Thế Hỏa Kiếp', 'Hỏa kiếp diệt thế giáng xuống, nuốt trọn {mob} trong biển lửa hỗn nguyên!')]),
 'tho': (85, 79, 'Hỗn Nguyên Bàn Thạch Thể', 'Hộ Thể / Hỗn Nguyên', 'Thân như bàn thạch',
         'Giảm sát thương nhận vào tột cùng, trấn áp đối thủ', 'Bất động – bất hoại – lấy thủ làm công',
         'Hỗn Nguyên Bàn Thạch Thể luyện thân thể như tảng đá đầu tiên của trời đất. Vạn kiếp đi qua, bàn thạch vẫn còn nguyên.',
         [('Bàn Thạch Quyền', 'Một quyền nặng như bàn thạch hồng hoang nện xuống {mob}.'),
          ('Hỗn Nguyên Hộ Thân', 'Thân thể hóa bàn thạch, đòn của {mob} chỉ làm rơi vài hạt bụi!'),
          ('Bàn Thạch Trấn Thế', 'Bàn thạch hỗn nguyên đè xuống, {mob} bị trấn chặt không cựa nổi!')]),
 'all': (86, 80, 'Hỗn Nguyên Tạo Hóa Quyết', 'Tạp Kỹ / Hỗn Nguyên', 'Tạo hóa vô cùng',
         'Hút máu phản kích, chuyển đòn địch thành sinh lực', 'Bao dung – biến hóa – tạo hóa trong tay',
         'Hỗn Nguyên Tạo Hóa Quyết nắm lấy quyền năng tạo hóa: mọi đòn đánh tới đều bị biến đổi, hóa thành sinh lực cho chính mình.',
         [('Tạo Hóa Chưởng', 'Chưởng lực mang sức tạo hóa đánh thẳng vào {mob}.'),
          ('Hỗn Nguyên Hấp Tinh', 'Tạo hóa xoay chuyển, rút tinh khí của {mob} về thân ngươi!'),
          ('Tạo Hóa Quy Nguyên', 'Ngũ hành hỗn nguyên hợp nhất thành một đạo quang, xuyên thẳng qua {mob}!')]),
}
# cpid, tên, hệ, effect, Lv1, Lv10, CG, giá MT, loại, mô tả
CP_NEW = [
    (57, 'Tiên Đế Trấn Thế Quyết', 'kim', 'atk_bonus', 14, 60, 45, 150000, 'Sát Pháp', 'Đế uy trấn thế|Tăng % công kích|Tiên Đế Trấn Thế Quyết là tâm pháp các đời tiên đế dùng để trấn áp thiên hạ, đế uy lan tới đâu kẻ địch run sợ tới đó.'),
    (58, 'Bất Hủ Kim Thân Kinh', 'tho', 'def_bonus', 14, 60, 45, 150000, 'Hộ Thể', 'Kim thân bất hủ|Tăng % phòng ngự|Bất Hủ Kim Thân Kinh rèn thân thể thành kim thân bất hủ, vạn kiếp không mài mòn.'),
    (59, 'Luyện Thần Phá Vọng Quyết', 'hoa', 'crit_dmg', 12, 58, 48, 180000, 'Sát Pháp', 'Thần hỏa phá vọng|Tăng % sát thương bạo kích|Luyện Thần Phá Vọng Quyết luyện thần thức trong lò lửa, mắt thấy ngay chỗ hiểm của đối thủ.'),
    (60, 'Nhập Thần Hồi Nguyên Công', 'moc', 'hp_regen', 14, 60, 48, 180000, 'Nội Công', 'Thần lực hồi nguyên|Tăng % hồi phục khí huyết|Nhập Thần Hồi Nguyên Công dẫn thần lực vào kinh mạch, thương thế tự lành theo từng nhịp thở.'),
]
ELEM_COLOR = {'kim': '#C0C0C0', 'moc': '#228B22', 'thuy': '#1E90FF', 'hoa': '#FF4500', 'tho': '#8B4513', 'all': '#FFD700'}

def build():
    # Võ công: dùng lại hàm làn 4 với tham số làn 5
    L4.NEW, L4.REALM, L4.TMULT, L4.COLOR, L4.STAT_MULT, L4.EFFECT_NEW = NEW, REALM, TMULT, COLOR, STAT_MULT, EFFECT_NEW
    out = L4.build()
    wg = load('wugong_config.json')
    for wid in range(81, 87):
        wg[str(wid)]['rarity'] = 'hon_nguyen'
    save('wugong_config.json', wg)
    sk = load('wugong_skill_config.json')
    sk['rules']['rarity_bonus']['hon_nguyen'] = 0.25
    save('wugong_skill_config.json', sk)
    # Công pháp Đế Giai
    cp = load('congphap_config.json')
    for cpid, n, el, et, b, m, r, mt, srt, info in CP_NEW:
        cp[str(cpid)] = {
            'cpid': cpid, 'cpname': n, 'cpys': ELEM_COLOR[el], 'cpsort': srt, 'cpinfo': info,
            'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
            'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
            'realm_require': r, 'tier_require': 0, 'quality': 5, 'tier': 8, 'subtier': 1 if r == 45 else 2,
            'training_time_mult': 30.0, 'price_linhthong': 0, 'price_masthach': mt,
        }
    save('congphap_config.json', cp)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
