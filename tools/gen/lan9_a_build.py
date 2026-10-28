# -*- coding: utf-8 -*-
"""
Làn 9 (làn cuối) — L9-A (docs/LAN9_CAP_4000_4300.md):
  1) 6 võ công phẩm ĐẠI ĐẠO (rarity dai_dao) yêu cầu CG 83 (Đại Đạo Thánh Nhân, cấp 4100), wgid 105–110,
     đổi bằng Đại Đạo Tàn Quyển (djid 117) 50 quyển / bộ (tiendao_config.json — tools/gen/dot_d_build.py).
     Chỉ số = bộ Đại Đạo CG 72 cùng hệ (wgid 99–104) ×1,2; hiệu ứng / thế võ tăng có trần.
  2) 4 công pháp giai 10 "Chí Tôn Giai" CG 82 / 85, cpid 77–80; giá theo tools/gen/cp_price_build.py.
Chạy sau tools/gen/lan8_a_build.py. Chạy lại nhiều lần cho cùng kết quả. Chạy: python3 tools/gen/lan9_a_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save
import lan4_a_build as L4
import lan5_a_build as L5
import cp_price_build as P

REALM, TMULT, COLOR, STAT_MULT = 83, 80.0, '#fde047', 1.2
EFFECT_NEW = {'pierce_def': 82, 'hp_regen': 49, 'escape': 99, 'crit_dmg': 140, 'damage_reduce': 67, 'counter_lifesteal': 33}
# hệ -> (wgid, bộ Đại Đạo CG 72 cùng hệ, tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3])
NEW = {
 'kim': (105, 99, 'Thánh Nhân Trảm Đạo Kiếm', 'Kiếm Pháp / Đại Đạo', 'Kiếm chém thiên đạo',
         'Xuyên phá phòng ngự tuyệt đối', 'Thánh nhân một kiếm – thiên đạo cũng đứt',
         'Thánh Nhân Trảm Đạo Kiếm là kiếm của bậc thánh nhân dám chém cả thiên đạo. Kiếm ra không còn phân biệt pháp tắc hay hộ thể, thứ gì cản đường đều bị chém đôi.',
         [('Trảm Đạo Kiếm Quang', 'Kiếm quang thánh nhân lao thẳng vào {mob}.'),
          ('Thánh Kiếm Đoạn Pháp', 'Một kiếm chém đứt pháp tắc hộ thân, phòng ngự của {mob} sụp đổ hoàn toàn!'),
          ('Nhất Kiếm Trảm Thiên Đạo', 'Thiên đạo cũng bị chém làm đôi, {mob} không còn chỗ dung thân!')]),
 'moc': (106, 100, 'Hồng Mông Sinh Diệt Kinh', 'Nội Công / Đại Đạo', 'Sinh diệt tuần hoàn',
         'Hồi phục khí huyết tuyệt đối mỗi lượt', 'Diệt rồi lại sinh – hồng mông bất tận',
         'Hồng Mông Sinh Diệt Kinh lấy khí hồng mông từ thuở chưa có trời đất làm gốc. Thân thể diệt rồi lại sinh, mỗi lần tái sinh lại mạnh hơn trước.',
         [('Hồng Mông Chưởng', 'Chưởng lực mang khí hồng mông đánh trúng {mob}.'),
          ('Sinh Diệt Tuần Hoàn', 'Khí hồng mông xoay chuyển, thương thế tái sinh ngay trước mắt {mob}!'),
          ('Hồng Mông Tái Tạo', 'Hồng mông bao trùm, vừa giam chặt {mob} vừa tái tạo toàn thân!')]),
 'thuy': (107, 101, 'Thái Hư Vô Tung Bộ', 'Thân Pháp / Đại Đạo', 'Bước giữa thái hư',
          'Né tránh gần như tuyệt đối, phong tỏa đối thủ', 'Thái hư vô tung – không dấu không bóng',
          'Thái Hư Vô Tung Bộ đưa thân thể vào thái hư, nơi không có trên dưới trước sau. Đối thủ không biết người đang ở đâu để mà đánh.',
          [('Thái Hư Chỉ', 'Một chỉ từ giữa thái hư điểm thẳng vào {mob}.'),
           ('Vô Tung Vô Ảnh', 'Thân hình tan vào thái hư, {mob} đánh tới chỉ trúng khoảng không!'),
           ('Thái Hư Phong Tỏa', 'Thái hư khép lại thành ngục, giam chặt {mob} không lối thoát!')]),
 'hoa': (108, 102, 'Khai Thiên Tịch Địa Quyền', 'Quyền Pháp / Đại Đạo', 'Quyền mở trời đất',
         'Bạo kích tuyệt đối, thiêu đốt vạn vật', 'Một quyền khai thiên – hai quyền tịch địa',
         'Khai Thiên Tịch Địa Quyền dồn sức mạnh thuở trời đất mới mở vào nắm đấm. Mỗi quyền như tách đôi hỗn độn, lửa sáng thế bùng lên theo.',
         [('Khai Thiên Quyền', 'Một quyền mang lực khai thiên đánh thẳng vào {mob}.'),
          ('Tịch Địa Hỏa Bạo', 'Lửa sáng thế bùng lên trong thân {mob}, cháy mãi không tắt!'),
          ('Khai Thiên Tịch Địa', 'Trời đất như bị tách đôi, biển lửa nuốt trọn {mob}!')]),
 'tho': (109, 103, 'Đạo Tổ Bất Diệt Thân', 'Hộ Thể / Đại Đạo', 'Thân Đạo Tổ bất diệt',
         'Giảm sát thương nhận vào tuyệt đối, trấn áp đối thủ', 'Đạo Tổ chi thân – vạn kiếp bất diệt',
         'Đạo Tổ Bất Diệt Thân rèn thân thể theo khuôn của Đạo Tổ, trải vạn kiếp mà không hư hao. Đòn đánh tới như mưa rơi vào núi thái cổ.',
         [('Đạo Tổ Quyền', 'Một quyền nặng như núi thái cổ nện xuống {mob}.'),
          ('Bất Diệt Đạo Thân', 'Đạo thân phát sáng, đòn của {mob} chỉ để lại vết xước!'),
          ('Đạo Tổ Trấn Áp', 'Uy áp Đạo Tổ đè xuống, {mob} quỳ rạp không đứng dậy nổi!')]),
 'all': (110, 104, 'Chí Tôn Vô Ngã Quyết', 'Tạp Kỹ / Đại Đạo', 'Vô ngã chí tôn',
         'Hút máu phản kích, chuyển đòn địch thành sinh lực', 'Quên ta mà thành đạo – chí tôn vô ngã',
         'Chí Tôn Vô Ngã Quyết là tâm pháp quên đi bản ngã để hòa vào đại đạo. Khi không còn "ta", mọi đòn đánh tới đều tan vào đạo và trở về thành sinh lực.',
         [('Vô Ngã Chưởng', 'Chưởng lực không mang sát ý mà vẫn đánh thẳng vào {mob}.'),
          ('Vô Ngã Hấp Nguyên', 'Đại đạo xoay chuyển, rút tinh khí của {mob} về thân ngươi!'),
          ('Chí Tôn Quy Đạo', 'Thân hóa thành đạo, một đạo quang xuyên thẳng qua {mob}!')]),
}
# cpid, tên, hệ, effect, Lv1, Lv10, CG, loại, mô tả
CP_NEW = [
    (77, 'Thiên Đạo Tru Thánh Quyết', 'kim', 'atk_bonus', 22, 88, 82, 'Sát Pháp', 'Thiên đạo tru thánh|Tăng % công kích|Thiên Đạo Tru Thánh Quyết mượn uy thiên đạo vào đòn đánh, thánh nhân trúng phải cũng bị thương.'),
    (78, 'Hỗn Nguyên Thánh Giáp Kinh', 'tho', 'def_bonus', 22, 88, 82, 'Hộ Thể', 'Hỗn nguyên thánh giáp|Tăng % phòng ngự|Hỗn Nguyên Thánh Giáp Kinh ngưng khí hỗn nguyên thành giáp, vạn pháp khó phá.'),
    (79, 'Đạo Tổ Diệt Thế Công', 'hoa', 'crit_dmg', 18, 78, 85, 'Sát Pháp', 'Đạo Tổ diệt thế|Tăng % sát thương bạo kích|Đạo Tổ Diệt Thế Công dồn lực một kiếp vào một đòn, bạo kích đủ diệt một phương thế giới.'),
    (80, 'Chí Tôn Vô Cực Hộ Thể', 'thuy', 'dmg_reduce', 18, 64, 85, 'Hộ Thể', 'Chí tôn vô cực|Giảm % sát thương nhận vào|Chí Tôn Vô Cực Hộ Thể đưa sát thương tan vào vô cực, đòn đánh tới chỉ còn lại rất ít.'),
]
SUBTIER = {82: 1, 85: 2}   # Chí Tôn Giai (giai 10): CG 82 Sơ Phẩm, CG 85 Trung Phẩm

def build():
    # Võ công: dùng lại hàm làn 4 với tham số làn 9
    L4.NEW, L4.REALM, L4.TMULT, L4.COLOR, L4.STAT_MULT, L4.EFFECT_NEW = NEW, REALM, TMULT, COLOR, STAT_MULT, EFFECT_NEW
    out = L4.build()
    wg = load('wugong_config.json')
    for wid in range(105, 111):
        wg[str(wid)]['rarity'] = 'dai_dao'
    save('wugong_config.json', wg)
    # Công pháp Chí Tôn Giai (cùng khuôn trường với làn 5–8)
    cp = load('congphap_config.json')
    for cpid, n, el, et, b, m, r, srt, info in CP_NEW:
        cp[str(cpid)] = P.apply({
            'cpid': cpid, 'cpname': n, 'cpys': L5.ELEM_COLOR[el], 'cpsort': srt, 'cpinfo': info,
            'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
            'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
            'realm_require': r, 'tier_require': 0, 'quality': 5, 'tier': 10, 'subtier': SUBTIER[r],
            'training_time_mult': 70.0, 'price_linhthong': 0, 'price_masthach': 0,
        })
    save('congphap_config.json', cp)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
