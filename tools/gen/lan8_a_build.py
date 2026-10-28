# -*- coding: utf-8 -*-
"""
Làn 8 — L8-A (docs/LAN8_CAP_3500_4000.md mục 4, 5):
  1) Phẩm võ công mới ĐẠI ĐẠO (rarity dai_dao, màu #fde047): 6 bộ yêu cầu CG 72 (Chu Thiên XV, cấp 3550), wgid 99–104,
     đổi bằng Đại Đạo Tàn Quyển (djid 117) 40 quyển / bộ (tiendao_config.json — tools/gen/dot_d_build.py).
     Chỉ số = bộ Hỗn Nguyên CG 65 cùng hệ (wgid 93–98) ×1,2; hiệu ứng / thế võ tăng có trần.
     Hệ số thế võ phẩm Đại Đạo: rules.rarity_bonus.dai_dao = 0,3 (Hỗn Nguyên 0,25).
  2) 4 công pháp Đạo Giai (giai 9) Cao / Thượng Phẩm CG 74 / 78, cpid 69–72; giá theo tools/gen/cp_price_build.py.
Chạy sau tools/gen/lan7_a_build.py. Chạy lại nhiều lần cho cùng kết quả. Chạy: python3 tools/gen/lan8_a_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save
import lan4_a_build as L4
import lan5_a_build as L5
import cp_price_build as P

REALM, TMULT, COLOR, STAT_MULT = 72, 70.0, '#fde047', 1.2
EFFECT_NEW = {'pierce_def': 76, 'hp_regen': 45, 'escape': 99, 'crit_dmg': 128, 'damage_reduce': 64, 'counter_lifesteal': 30}
# hệ -> (wgid, bộ Hỗn Nguyên CG 65 cùng hệ, tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3])
NEW = {
 'kim': (99, 93, 'Đại Đạo Tru Tiên Kiếm', 'Kiếm Pháp / Đại Đạo', 'Kiếm chém đại đạo',
         'Xuyên phá phòng ngự tuyệt đối', 'Một kiếm đoạn đạo – tiên thánh đều tru',
         'Đại Đạo Tru Tiên Kiếm lấy đại đạo làm lưỡi kiếm. Kiếm ý chạm tới đâu, đạo tắc hộ thân của đối thủ đứt tới đó, tiên thánh cũng không ngoại lệ.',
         [('Tru Tiên Kiếm Khí', 'Kiếm khí đại đạo xé gió lao thẳng vào {mob}.'),
          ('Đoạn Đạo Nhất Kiếm', 'Một kiếm chém đứt đạo tắc hộ thân, phòng ngự của {mob} tan thành hư vô!'),
          ('Vạn Đạo Quy Kiếm', 'Vạn đạo tắc hóa thành vạn thanh kiếm cùng chém xuống, {mob} không còn chỗ dung thân!')]),
 'moc': (100, 94, 'Đại Đạo Vạn Mộc Trường Xuân Kinh', 'Nội Công / Đại Đạo', 'Trường xuân bất lão',
         'Hồi phục khí huyết tuyệt đối mỗi lượt', 'Vạn mộc trường xuân – đạo thể bất diệt',
         'Đại Đạo Vạn Mộc Trường Xuân Kinh lấy sinh cơ của vạn mộc trong trời đất nuôi đạo thể. Thương thế nặng mấy cũng như cây mùa xuân, đâm chồi trở lại.',
         [('Trường Xuân Chưởng', 'Chưởng lực mang sinh cơ vạn mộc đánh trúng {mob}.'),
          ('Vạn Mộc Hồi Xuân', 'Sinh cơ vạn mộc tràn vào kinh mạch, thương thế lành lại ngay trước mắt {mob}!'),
          ('Đạo Thể Trường Xuân', 'Đạo thể hóa thành cổ thụ vạn năm, vừa trói chặt {mob} vừa hồi phục toàn thân!')]),
 'thuy': (101, 95, 'Đại Đạo Vô Cực Thủy Bộ', 'Thân Pháp / Đại Đạo', 'Như nước vô cực',
          'Né tránh gần như tuyệt đối, phong tỏa đối thủ', 'Thượng thiện nhược thủy – vô cực vô hình',
          'Đại Đạo Vô Cực Thủy Bộ học theo nước: không hình, không cố định, chảy qua mọi khe hở. Đối thủ đánh vào chỉ như đánh vào dòng nước.',
          [('Vô Cực Thủy Chỉ', 'Một chỉ mềm như nước mà sắc như kim điểm thẳng vào {mob}.'),
           ('Thủy Ảnh Vô Hình', 'Thân hình tan như nước, {mob} đánh tới chỉ trúng gợn sóng!'),
           ('Vô Cực Thủy Lao', 'Nước vô cực cuộn thành ngục, giam chặt {mob} không lối thoát!')]),
 'hoa': (102, 96, 'Đại Đạo Phần Thánh Quyền', 'Quyền Pháp / Đại Đạo', 'Lửa đốt thánh nhân',
         'Bạo kích tuyệt đối, thiêu đốt vạn vật', 'Đạo hỏa phần thánh – một quyền thiêu thiên',
         'Đại Đạo Phần Thánh Quyền nhóm lửa từ chính đại đạo. Lửa này đốt được cả thánh thể, mỗi quyền như một vầng thái dương nổ tung.',
         [('Phần Thánh Quyền', 'Một quyền mang đạo hỏa đánh thẳng vào {mob}.'),
          ('Đạo Hỏa Thiêu Thần', 'Đạo hỏa bén vào thần hồn {mob}, cháy mãi không tắt!'),
          ('Phần Thánh Diệt Thiên', 'Biển đạo hỏa cuộn trào, thiêu rụi cả bầu trời quanh {mob}!')]),
 'tho': (103, 97, 'Đại Đạo Thánh Thể', 'Hộ Thể / Đại Đạo', 'Thân thánh bất hoại',
         'Giảm sát thương nhận vào tuyệt đối, trấn áp đối thủ', 'Thánh thể bất hoại – đại đạo trấn thân',
         'Đại Đạo Thánh Thể rèn thân theo đạo tắc của đại địa. Thân thể cứng hơn thần kim, đứng yên mà trời đất không lay chuyển nổi.',
         [('Thánh Thể Quyền', 'Một quyền nặng như đại địa nện xuống {mob}.'),
          ('Bất Hoại Thánh Thân', 'Thánh thể phát sáng, đòn của {mob} chỉ để lại vết xước!'),
          ('Đại Đạo Trấn Thế', 'Uy áp đại đạo đè xuống, {mob} quỳ rạp không đứng dậy nổi!')]),
 'all': (104, 98, 'Đại Đạo Quy Nhất Quyết', 'Tạp Kỹ / Đại Đạo', 'Vạn đạo quy nhất',
         'Hút máu phản kích, chuyển đòn địch thành sinh lực', 'Vạn pháp quy nhất – đạo sinh vạn vật',
         'Đại Đạo Quy Nhất Quyết gom ngũ hành, âm dương, vạn pháp về một gốc. Mọi đòn đánh tới đều bị đại đạo hấp thu, hóa thành sinh lực.',
         [('Quy Nhất Chưởng', 'Chưởng lực mang khí tức đại đạo đánh thẳng vào {mob}.'),
          ('Đại Đạo Hấp Nguyên', 'Đại đạo xoay chuyển, rút tinh khí của {mob} về thân ngươi!'),
          ('Vạn Đạo Quy Nhất', 'Vạn đạo hợp thành một đạo quang, xuyên thẳng qua {mob}!')]),
}
# cpid, tên, hệ, effect, Lv1, Lv10, CG, loại, mô tả
CP_NEW = [
    (69, 'Thần Mệnh Phá Đạo Quyết', 'kim', 'atk_bonus', 20, 80, 74, 'Sát Pháp', 'Thần mệnh phá đạo|Tăng % công kích|Thần Mệnh Phá Đạo Quyết viết lại thần mệnh bằng sát ý, một đòn đủ phá vỡ đạo tắc của đối thủ.'),
    (70, 'Sáng Thế Thánh Giáp Kinh', 'tho', 'def_bonus', 20, 80, 74, 'Hộ Thể', 'Thánh giáp sáng thế|Tăng % phòng ngự|Sáng Thế Thánh Giáp Kinh ngưng lực sáng thế thành giáp trụ vô hình, đao kiếm chém vào chỉ tóe lửa.'),
    (71, 'Hỗn Độn Diệt Thánh Công', 'hoa', 'crit_dmg', 16, 70, 78, 'Sát Pháp', 'Hỗn độn diệt thánh|Tăng % sát thương bạo kích|Hỗn Độn Diệt Thánh Công dồn lực hỗn độn vào một điểm, đòn bạo kích đủ đánh vỡ cả thánh thể.'),
    (72, 'Thánh Nhân Bất Hoại Quyết', 'thuy', 'dmg_reduce', 16, 60, 78, 'Hộ Thể', 'Thánh nhân bất hoại|Giảm % sát thương nhận vào|Thánh Nhân Bất Hoại Quyết dẫn sát thương tan vào đạo tắc trời đất, đòn đánh tới chỉ còn lại một phần nhỏ.'),
]
SUBTIER = {74: 3, 78: 4}   # Đạo Giai (giai 9): CG 62 Sơ, 67 Trung (làn 7), 74 Cao, 78 Thượng

def build():
    # Võ công: dùng lại hàm làn 4 với tham số làn 8
    L4.NEW, L4.REALM, L4.TMULT, L4.COLOR, L4.STAT_MULT, L4.EFFECT_NEW = NEW, REALM, TMULT, COLOR, STAT_MULT, EFFECT_NEW
    out = L4.build()
    wg = load('wugong_config.json')
    for wid in range(99, 105):
        wg[str(wid)]['rarity'] = 'dai_dao'
    save('wugong_config.json', wg)
    sk = load('wugong_skill_config.json')
    sk['rules']['rarity_bonus']['dai_dao'] = 0.3
    save('wugong_skill_config.json', sk)
    # Công pháp Đạo Giai Cao / Thượng Phẩm (cùng khuôn trường với làn 5–7)
    cp = load('congphap_config.json')
    for cpid, n, el, et, b, m, r, srt, info in CP_NEW:
        cp[str(cpid)] = P.apply({
            'cpid': cpid, 'cpname': n, 'cpys': L5.ELEM_COLOR[el], 'cpsort': srt, 'cpinfo': info,
            'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
            'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
            'realm_require': r, 'tier_require': 0, 'quality': 5, 'tier': 9, 'subtier': SUBTIER[r],
            'training_time_mult': 60.0, 'price_linhthong': 0, 'price_masthach': 0,
        })
    save('congphap_config.json', cp)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
