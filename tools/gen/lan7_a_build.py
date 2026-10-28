# -*- coding: utf-8 -*-
"""
Làn 7 — L7-A (docs/LAN7_CAP_3000_3500.md mục 4, 5):
  1) 6 võ công phẩm HỖN NGUYÊN (rarity hon_nguyen) yêu cầu CG 65 (Chu Thiên VIII, cấp 3200), wgid 93–98,
     đổi bằng Hỗn Nguyên Tàn Quyển 20 quyển / bộ (tiendao_config.json need_by_wgid — tools/gen/dot_d_build.py).
     Chỉ số = bộ Hỗn Nguyên CG 55 cùng hệ (wgid 87–92) ×1,2; hiệu ứng / thế võ tăng có trần.
  2) 4 công pháp giai 9 "Đạo Giai" CG 62 / 67, cpid 65–68, bán Ma Thạch.
Chạy sau tools/gen/lan6_a_build.py. Chạy lại nhiều lần cho cùng kết quả. Chạy: python3 tools/gen/lan7_a_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save
import lan4_a_build as L4
import lan5_a_build as L5

REALM, TMULT, COLOR, STAT_MULT = 65, 60.0, '#c084fc', 1.2
EFFECT_NEW = {'pierce_def': 70, 'hp_regen': 41, 'escape': 99, 'crit_dmg': 116, 'damage_reduce': 61, 'counter_lifesteal': 27}
# hệ -> (wgid, bộ Hỗn Nguyên CG 55 cùng hệ, tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3])
NEW = {
 'kim': (93, 87, 'Bắc Đẩu Phá Quân Kiếm', 'Kiếm Pháp / Hỗn Nguyên', 'Kiếm theo sao Phá Quân',
         'Xuyên phá phòng ngự tột cùng', 'Thất tinh liên hoàn – phá quân nhất kiếm',
         'Bắc Đẩu Phá Quân Kiếm lấy bảy ngôi sao Bắc Đẩu làm bảy bước kiếm. Đến sao Phá Quân, mọi phòng ngự đều vỡ nát.',
         [('Thất Tinh Kiếm Khí', 'Bảy đạo kiếm khí theo vị trí Bắc Đẩu lao thẳng vào {mob}.'),
          ('Phá Quân Tinh Mang', 'Tinh mang Phá Quân xuyên qua hộ thể, phòng ngự của {mob} vỡ vụn!'),
          ('Bắc Đẩu Liên Hoàn Trảm', 'Bảy ngôi sao cùng rơi xuống thành bảy kiếm, {mob} không còn đường lui!')]),
 'moc': (94, 88, 'Thái Âm Trường Sinh Kinh', 'Nội Công / Hỗn Nguyên', 'Trường sinh dưới ánh trăng',
         'Hồi phục khí huyết tột cùng mỗi lượt', 'Trăng khuyết lại tròn – thân suy lại thịnh',
         'Thái Âm Trường Sinh Kinh hấp thu tinh hoa Thái Âm. Như trăng khuyết rồi lại tròn, thương thế bao nhiêu cũng lành lại.',
         [('Nguyệt Hoa Chưởng', 'Chưởng lực mang ánh trăng lạnh lẽo đánh trúng {mob}.'),
          ('Thái Âm Hồi Sinh', 'Ánh trăng phủ khắp thân, thương thế lành lại ngay trước mắt {mob}!'),
          ('Nguyệt Mãn Trường Sinh', 'Trăng tròn giữa trời, vừa giam chặt {mob} vừa hồi phục toàn thân!')]),
 'thuy': (95, 89, 'Tinh Vân Huyễn Ảnh Bộ', 'Thân Pháp / Hỗn Nguyên', 'Huyễn ảnh trong tinh vân',
          'Né tránh gần như tuyệt đối, phong tỏa đối thủ', 'Ẩn trong sương sao – thật giả khó phân',
          'Tinh Vân Huyễn Ảnh Bộ hòa thân vào màn sương tinh vân, để lại vô số huyễn ảnh. Đối thủ không biết đâu là thân thật.',
          [('Tinh Vân Chỉ', 'Một chỉ từ giữa màn sương sao điểm thẳng vào {mob}.'),
           ('Vạn Huyễn Tinh Ảnh', 'Vạn huyễn ảnh cùng hiện, {mob} đánh vào ảnh nào cũng trượt!'),
           ('Tinh Vân Khốn Tỏa', 'Tinh vân cuộn lại thành kén, giam chặt {mob} giữa màn sương tím!')]),
 'hoa': (96, 90, 'Liệt Nhật Kim Ô Quyền', 'Quyền Pháp / Hỗn Nguyên', 'Lửa mặt trời',
         'Bạo kích tột cùng, thiêu đốt vạn vật', 'Kim ô giương cánh – liệt nhật thiêu không',
         'Liệt Nhật Kim Ô Quyền mượn lửa của Kim Ô – thần điểu cõng mặt trời. Mỗi quyền tung ra nóng như mười mặt trời cùng mọc.',
         [('Kim Ô Quyền', 'Một quyền mang lửa mặt trời đánh thẳng vào {mob}.'),
          ('Liệt Nhật Phần Thân', 'Lửa Kim Ô bén vào thân {mob}, cháy mãi không tắt!'),
          ('Thập Nhật Đồng Thiên', 'Mười mặt trời cùng hiện trên trời, thiêu rụi mọi thứ quanh {mob}!')]),
 'tho': (97, 91, 'Tử Vi Trấn Tinh Thể', 'Hộ Thể / Hỗn Nguyên', 'Thân trấn tinh thần',
         'Giảm sát thương nhận vào tột cùng, trấn áp đối thủ', 'Đế tinh hộ thể – vạn tinh quy phục',
         'Tử Vi Trấn Tinh Thể lấy uy Tử Vi Đế Tinh hộ thân. Thân như đế tinh giữa trời, vạn sao xoay quanh mà không chạm tới.',
         [('Tử Vi Quyền', 'Một quyền mang uy đế tinh nện xuống {mob}.'),
          ('Đế Tinh Hộ Thể', 'Tử khí đế tinh bao quanh thân, đòn của {mob} chỉ để lại vết xước!'),
          ('Tử Vi Trấn Tinh', 'Uy áp đế tinh đè xuống, {mob} quỳ rạp không đứng dậy nổi!')]),
 'all': (98, 92, 'Nhị Thập Bát Tú Quy Nguyên Quyết', 'Tạp Kỹ / Hỗn Nguyên', 'Hai mươi tám tú quy nguyên',
         'Hút máu phản kích, chuyển đòn địch thành sinh lực', 'Tứ tượng – bát tú – quy về một nguyên',
         'Nhị Thập Bát Tú Quy Nguyên Quyết dẫn sức hai mươi tám chòm sao về một thân. Đòn đánh tới bị tinh tú hấp thu, hóa thành sinh lực.',
         [('Bát Tú Chưởng', 'Chưởng lực mang ánh sáng hai mươi tám tú đánh thẳng vào {mob}.'),
          ('Tú Tinh Hấp Nguyên', 'Hai mươi tám tú xoay chuyển, rút tinh khí của {mob} về thân ngươi!'),
          ('Tứ Tượng Quy Nguyên', 'Tứ tượng hợp nhất thành một đạo tinh quang, xuyên thẳng qua {mob}!')]),
}
# cpid, tên, hệ, effect, Lv1, Lv10, CG, giá MT, loại, mô tả
CP_NEW = [
    (65, 'Bắc Đẩu Tru Tinh Quyết', 'kim', 'atk_bonus', 18, 72, 62, 280000, 'Sát Pháp', 'Bắc Đẩu tru tinh|Tăng % công kích|Bắc Đẩu Tru Tinh Quyết dẫn sát khí bảy ngôi sao Bắc Đẩu vào thân, một đòn đủ đánh rơi tinh tú.'),
    (66, 'Tử Vi Hộ Mệnh Kinh', 'tho', 'def_bonus', 18, 72, 62, 280000, 'Hộ Thể', 'Đế tinh hộ mệnh|Tăng % phòng ngự|Tử Vi Hộ Mệnh Kinh lấy Tử Vi Đế Tinh làm mệnh tinh, tử khí bao thân, đao kiếm khó phạm.'),
    (67, 'Thái Âm Hồi Xuân Công', 'moc', 'hp_regen', 16, 66, 67, 330000, 'Nội Công', 'Thái Âm hồi xuân|Hồi % khí huyết mỗi lượt|Thái Âm Hồi Xuân Công mượn ánh trăng tẩm bổ kinh mạch, thương thế lành lại như trăng khuyết hóa tròn.'),
    (68, 'Tinh Đấu Hóa Kiếp Quyết', 'thuy', 'dmg_reduce', 14, 56, 67, 330000, 'Hộ Thể', 'Tinh đấu hóa kiếp|Giảm % sát thương nhận vào|Tinh Đấu Hóa Kiếp Quyết chia sát thương ra khắp chu thiên tinh đấu, đòn đánh tới chỉ còn lại một phần nhỏ.'),
]
SUBTIER = {62: 1, 67: 2}   # Đạo Giai (giai 9): CG 62 = Sơ Phẩm, CG 67 = Trung Phẩm; Cao / Thượng Phẩm để làn 8

def build():
    # Võ công: dùng lại hàm làn 4 với tham số làn 7
    L4.NEW, L4.REALM, L4.TMULT, L4.COLOR, L4.STAT_MULT, L4.EFFECT_NEW = NEW, REALM, TMULT, COLOR, STAT_MULT, EFFECT_NEW
    out = L4.build()
    wg = load('wugong_config.json')
    for wid in range(93, 99):
        wg[str(wid)]['rarity'] = 'hon_nguyen'
    save('wugong_config.json', wg)
    # Công pháp Đạo Giai (cùng khuôn trường với làn 5–6)
    cp = load('congphap_config.json')
    for cpid, n, el, et, b, m, r, mt, srt, info in CP_NEW:
        cp[str(cpid)] = {
            'cpid': cpid, 'cpname': n, 'cpys': L5.ELEM_COLOR[el], 'cpsort': srt, 'cpinfo': info,
            'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
            'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
            'realm_require': r, 'tier_require': 0, 'quality': 5, 'tier': 9, 'subtier': SUBTIER[r],
            'training_time_mult': 50.0, 'price_linhthong': 0, 'price_masthach': mt,
        }
    save('congphap_config.json', cp)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
