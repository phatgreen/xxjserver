# -*- coding: utf-8 -*-
"""
Làn 6 — L6-A (docs/LAN6_CAP_2500_3000.md mục 4, 5):
  1) 6 võ công phẩm HỖN NGUYÊN (rarity hon_nguyen) yêu cầu CG 55 (Đại Chủ Tể, cấp 2700), wgid 87–92,
     đổi bằng Hỗn Nguyên Tàn Quyển 15 quyển / bộ (tiendao_config.json need_by_wgid — tools/gen/dot_d_build.py).
     Chỉ số = bộ Hỗn Nguyên CG 42 cùng hệ (wgid 81–86) ×1,2; hiệu ứng / thế võ tăng có trần.
  2) 4 công pháp Đế Giai (giai 8) CG 52 / 57, cpid 61–64, bán Ma Thạch (giai 8 đủ 8 bộ).
Chạy sau tools/gen/lan5_a_build.py. Chạy lại nhiều lần cho cùng kết quả. Chạy: python3 tools/gen/lan6_a_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save
import lan4_a_build as L4
import lan5_a_build as L5

REALM, TMULT, COLOR, STAT_MULT = 55, 50.0, '#c084fc', 1.2
EFFECT_NEW = {'pierce_def': 64, 'hp_regen': 37, 'escape': 99, 'crit_dmg': 104, 'damage_reduce': 58, 'counter_lifesteal': 24}
# hệ -> (wgid, bộ Hỗn Nguyên CG 42 cùng hệ, tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3])
NEW = {
 'kim': (87, 81, 'Chủ Tể Đoạn Không Kiếm', 'Kiếm Pháp / Hỗn Nguyên', 'Kiếm chém hư không',
         'Xuyên phá phòng ngự tột cùng', 'Chủ tể – đoạn không – một kiếm cắt đứt pháp tắc',
         'Chủ Tể Đoạn Không Kiếm là kiếm đạo của các đời Chủ Tể. Kiếm quang không chém thân xác mà chém đứt pháp tắc bảo hộ đối thủ.',
         [('Đoạn Không Kiếm Khí', 'Kiếm khí cắt rách hư không, chém thẳng vào {mob}.'),
          ('Chủ Tể Phá Pháp', 'Kiếm ý chủ tể cắt đứt pháp tắc hộ thân, phòng ngự của {mob} tan như khói!'),
          ('Nhất Kiếm Đoạn Hư Không', 'Hư không vỡ thành hai nửa, {mob} kẹt giữa vết nứt không lối thoát!')]),
 'moc': (88, 82, 'Luân Hồi Bất Diệt Kinh', 'Nội Công / Hỗn Nguyên', 'Luân hồi bất diệt',
         'Hồi phục khí huyết tột cùng mỗi lượt', 'Chết rồi lại sinh – sinh sinh tử tử không dứt',
         'Luân Hồi Bất Diệt Kinh lấy vòng luân hồi làm gốc: mỗi lần thân thể suy kiệt lại là một lần tái sinh mạnh hơn trước.',
         [('Luân Hồi Chưởng', 'Chưởng lực mang khí tức luân hồi đánh trúng {mob}.'),
          ('Bất Diệt Tục Mệnh', 'Vòng luân hồi xoay chuyển, thương thế tái sinh ngay trước mắt {mob}!'),
          ('Lục Đạo Quy Sinh', 'Sáu đường luân hồi cùng mở, vừa trói chặt {mob} vừa hồi phục toàn thân!')]),
 'thuy': (89, 83, 'Tinh Hà Vô Ảnh Bộ', 'Thân Pháp / Hỗn Nguyên', 'Vô ảnh giữa tinh hà',
          'Né tránh gần như tuyệt đối, phong tỏa đối thủ', 'Vô hình – vô ảnh – thân hòa vào ánh sao',
          'Tinh Hà Vô Ảnh Bộ hòa thân thể vào dòng tinh hà, đến bóng cũng không để lại. Đối thủ chỉ thấy ánh sao lấp lánh.',
          [('Tinh Hà Chỉ', 'Một chỉ từ giữa ánh sao điểm thẳng vào {mob}.'),
           ('Vô Ảnh Tinh Bộ', 'Thân ảnh tan vào tinh hà, {mob} đánh tới chỉ trúng ánh sao!'),
           ('Tinh Hà Tỏa Không', 'Vạn ánh sao kết thành lưới, giam chặt {mob} giữa dòng tinh hà!')]),
 'hoa': (90, 84, 'Sáng Thế Phần Thiên Quyền', 'Quyền Pháp / Hỗn Nguyên', 'Lửa sáng thế',
         'Bạo kích tột cùng, thiêu đốt vạn vật', 'Khai thiên – phần thiên – một quyền nhóm lửa sáng thế',
         'Sáng Thế Phần Thiên Quyền dồn ngọn lửa đầu tiên thắp sáng thế giới vào nắm đấm. Lửa này đốt được cả trời cao.',
         [('Phần Thiên Quyền', 'Một quyền mang lửa sáng thế đánh thẳng vào {mob}.'),
          ('Sáng Thế Chân Hỏa', 'Lửa sáng thế bén vào thần hồn {mob}, cháy mãi không tắt!'),
          ('Phần Thiên Diệt Địa', 'Biển lửa sáng thế cuộn trào, thiêu rụi trời đất quanh {mob}!')]),
 'tho': (91, 85, 'Thần Vương Bất Động Thể', 'Hộ Thể / Hỗn Nguyên', 'Thân thần vương',
         'Giảm sát thương nhận vào tột cùng, trấn áp đối thủ', 'Bất động như núi – thần vương trấn thế',
         'Thần Vương Bất Động Thể rèn thân thể thành kim thân thần vương. Đứng yên một chỗ mà vạn quân không lay chuyển nổi.',
         [('Thần Vương Quyền', 'Một quyền nặng như thần sơn nện xuống {mob}.'),
          ('Bất Động Kim Thân', 'Kim thân thần vương hiện ra, đòn của {mob} chỉ để lại vết xước!'),
          ('Thần Vương Trấn Thế', 'Uy áp thần vương đè xuống, {mob} quỳ rạp không đứng dậy nổi!')]),
 'all': (92, 86, 'Chu Thiên Vạn Tượng Quyết', 'Tạp Kỹ / Hỗn Nguyên', 'Vạn tượng chu thiên',
         'Hút máu phản kích, chuyển đòn địch thành sinh lực', 'Bao la – vạn tượng – chu thiên quy về một thân',
         'Chu Thiên Vạn Tượng Quyết gom vạn tượng của chu thiên tinh đấu vào một thân. Mọi đòn đánh tới đều hóa thành sinh lực.',
         [('Vạn Tượng Chưởng', 'Chưởng lực mang ánh sáng chu thiên đánh thẳng vào {mob}.'),
          ('Chu Thiên Hấp Nguyên', 'Chu thiên tinh đấu xoay chuyển, rút tinh khí của {mob} về thân ngươi!'),
          ('Vạn Tượng Quy Nhất', 'Vạn tượng hợp nhất thành một đạo tinh quang, xuyên thẳng qua {mob}!')]),
}
# cpid, tên, hệ, effect, Lv1, Lv10, CG, giá MT, loại, mô tả
CP_NEW = [
    (61, 'Thần Đế Phá Quân Quyết', 'kim', 'atk_bonus', 16, 66, 52, 200000, 'Sát Pháp', 'Thần uy phá quân|Tăng % công kích|Thần Đế Phá Quân Quyết là sát pháp của Thần Đế, một niệm khởi lên vạn quân tan vỡ.'),
    (62, 'Thần Vương Hộ Thể Kinh', 'tho', 'def_bonus', 16, 66, 52, 200000, 'Hộ Thể', 'Thần thể bất hoại|Tăng % phòng ngự|Thần Vương Hộ Thể Kinh luyện thân thể theo khuôn thần vương, đao kiếm chém vào chỉ tóe lửa.'),
    (63, 'Luân Hồi Hóa Kiếp Công', 'thuy', 'dmg_reduce', 12, 52, 57, 240000, 'Hộ Thể', 'Luân hồi hóa kiếp|Giảm % sát thương nhận vào|Luân Hồi Hóa Kiếp Công đưa sát thương vào vòng luân hồi, đòn đánh tới bị hóa giải phần lớn.'),
    (64, 'Sáng Thế Diệt Tinh Quyết', 'hoa', 'crit_dmg', 14, 64, 57, 240000, 'Sát Pháp', 'Diệt tinh bạo kích|Tăng % sát thương bạo kích|Sáng Thế Diệt Tinh Quyết dồn lửa sáng thế vào một điểm, đòn bạo kích đủ đánh nát một vì sao.'),
]
SUBTIER = {52: 3, 57: 4}   # Đế Giai: CG 45 = 1, CG 48 = 2 (làn 5), CG 52 = 3, CG 57 = 4

def build():
    # Võ công: dùng lại hàm làn 4 với tham số làn 6
    L4.NEW, L4.REALM, L4.TMULT, L4.COLOR, L4.STAT_MULT, L4.EFFECT_NEW = NEW, REALM, TMULT, COLOR, STAT_MULT, EFFECT_NEW
    out = L4.build()
    wg = load('wugong_config.json')
    for wid in range(87, 93):
        wg[str(wid)]['rarity'] = 'hon_nguyen'
    save('wugong_config.json', wg)
    # Công pháp Đế Giai (cùng khuôn trường với làn 5)
    cp = load('congphap_config.json')
    for cpid, n, el, et, b, m, r, mt, srt, info in CP_NEW:
        cp[str(cpid)] = {
            'cpid': cpid, 'cpname': n, 'cpys': L5.ELEM_COLOR[el], 'cpsort': srt, 'cpinfo': info,
            'element': el, 'effect_type': et, 'effect_value': b, 'effect_value_max': m,
            'penalty_atk_percent': 0, 'penalty_def_percent': 0, 'max_level': 10,
            'realm_require': r, 'tier_require': 0, 'quality': 5, 'tier': 8, 'subtier': SUBTIER[r],
            'training_time_mult': 40.0, 'price_linhthong': 0, 'price_masthach': mt,
        }
    save('congphap_config.json', cp)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
