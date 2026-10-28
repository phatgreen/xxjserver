# -*- coding: utf-8 -*-
"""
Làn 4 — L4-A (docs/LAN4_CAP_1500_2000.md mục 4): 6 võ công Tiên Đạo yêu cầu cảnh giới 36 (Thái Ất Kim Tiên, cấp 1750),
1 bộ / hệ (gồm Ngũ Hành), đổi bằng Tiên Đạo Tàn Quyển 15 quyển / bộ (tiendao_config.json need_by_wgid, tools/gen/dot_d_build.py).
Chỉ số = bộ Tiên Đạo CG 30 cùng hệ ×1,2; hiệu ứng + thế võ tăng vừa phải, có trần (né / xuyên / chặn đòn ≤ 95–98%).
Chạy lại nhiều lần cho cùng kết quả (ghi đè đúng wgid 75–80). Chạy: python3 tools/gen/lan4_a_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save

REALM, TMULT, COLOR, STAT_MULT = 36, 30.0, '#ff3100', 1.2
EFFECT_NEW = {'pierce_def': 52, 'hp_regen': 29, 'escape': 97, 'crit_dmg': 80, 'damage_reduce': 50, 'counter_lifesteal': 18}
MOVE_MULT, MOVE_CAP = 1.1, {'guard': 90, 'stun': 90, 'pierce': 95}
# hệ -> (wgid, bộ Tiên Đạo CG 30 cùng hệ, tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3])
NEW = {
 'kim': (75, 64, 'Thái Ất Tru Tà Kiếm', 'Kiếm Pháp / Tiên Đạo', 'Kiếm tru tà ma',
         'Xuyên phá phòng ngự cực mạnh', 'Chính khí – tru tà – kiếm ra không thu',
         'Thái Ất Tru Tà Kiếm do Thái Ất Kim Tiên luyện thành giữa vạn tinh tú, kiếm quang mang chính khí trời đất, tà ma chạm vào lập tức tan biến.',
         [('Tru Tà Kiếm Khí', 'Kiếm khí thuần dương lao tới, chém thẳng vào {mob}.'),
          ('Thái Ất Tinh Kiếm', 'Muôn vì sao hóa kiếm rơi xuống như mưa, xuyên thủng phòng ngự của {mob}!'),
          ('Nhất Kiếm Tru Thiên Tà', 'Kiếm ý thông thiên, một kiếm chém xuống, {mob} không còn chỗ ẩn thân!')]),
 'moc': (76, 65, 'Đại La Trường Sinh Kinh', 'Nội Công / Tiên Đạo', 'Trường sinh bất diệt',
         'Hồi phục khí huyết cực mạnh mỗi lượt', 'Bất diệt – trường tồn – càng đánh càng sung',
         'Đại La Trường Sinh Kinh là tâm pháp trường sinh của Đại La Kim Tiên. Người luyện thành như cây đại thụ vạn năm, thương tích vừa xuất hiện đã tự lành.',
         [('Trường Sinh Chưởng', 'Chưởng lực xanh ngát mang sinh khí vạn năm, đánh trúng {mob}.'),
          ('Đại La Tục Mệnh', 'Sinh khí Đại La rót vào kinh mạch, vết thương liền lại ngay trước mắt {mob}!'),
          ('Trường Sinh Bất Diệt', 'Thân thể hóa linh mộc bất diệt, vừa trói chặt {mob} vừa hồi phục toàn thân!')]),
 'thuy': (77, 66, 'Cửu Thiên Ngân Hà Bộ', 'Thân Pháp / Tiên Đạo', 'Bước trên ngân hà',
          'Né tránh gần như tuyệt đối, phong tỏa đối thủ', 'Phiêu hốt – vô tung – một bước một vì sao',
          'Cửu Thiên Ngân Hà Bộ lấy dòng ngân hà chín tầng trời làm đường đi. Thân pháp nhẹ như ánh sao, đối thủ chỉ thấy vệt sáng lướt qua.',
          [('Ngân Hà Thích', 'Thân ảnh hóa vệt sáng ngân hà, một chỉ điểm thẳng vào {mob}.'),
           ('Cửu Thiên Tinh Ảnh', 'Chín bóng sao cùng hiện, {mob} đánh vào bóng nào cũng trượt!'),
           ('Ngân Hà Đảo Lưu', 'Ngân hà chảy ngược, cuốn {mob} vào giữa dòng sao không lối thoát!')]),
 'hoa': (78, 67, 'Tam Muội Chân Hỏa Quyết', 'Quyền Pháp / Tiên Đạo', 'Chân hỏa thiêu tâm',
         'Bạo kích cực mạnh, thiêu đốt tận gốc', 'Bạo liệt – thiêu đốt – lửa đốt cả nguyên thần',
         'Tam Muội Chân Hỏa Quyết luyện lửa từ tinh, khí, thần hợp nhất. Chân hỏa không đốt da thịt mà thiêu thẳng nguyên thần đối thủ.',
         [('Chân Hỏa Quyền', 'Một quyền mang tam muội chân hỏa đánh thẳng vào {mob}.'),
          ('Tam Muội Thiêu Thần', 'Chân hỏa bén vào nguyên thần {mob}, lửa cháy từ bên trong!'),
          ('Chân Hỏa Diệt Hồn', 'Tam muội chân hỏa bùng nổ thành biển lửa, thiêu rụi cả hồn phách {mob}!')]),
 'tho': (79, 68, 'Côn Lôn Định Hải Thể', 'Hộ Thể / Tiên Đạo', 'Thân như Côn Lôn',
         'Giảm sát thương nhận vào cực mạnh, trấn áp đối thủ', 'Vững như núi – định cả biển – lấy thủ làm công',
         'Côn Lôn Định Hải Thể rèn thân thể theo thế núi Côn Lôn trấn giữ tứ hải. Luyện thành thì vạn đòn không lay, một bước chân đủ làm biển lặng.',
         [('Côn Lôn Quyền', 'Một quyền nặng như núi Côn Lôn giáng xuống {mob}.'),
          ('Định Hải Hộ Thân', 'Thổ khí ngưng thành núi Côn Lôn quanh người, đòn của {mob} như sóng vỗ vào đá!'),
          ('Côn Lôn Trấn Hải', 'Bóng núi Côn Lôn đè xuống, {mob} bị trấn chặt không động đậy nổi!')]),
 'all': (80, 69, 'Tiên Tôn Vạn Pháp Công', 'Tạp Kỹ / Tiên Đạo', 'Vạn pháp quy tông',
         'Hút máu phản kích, dùng sức địch đánh địch', 'Bao dung – biến hóa – vạn pháp về một',
         'Tiên Tôn Vạn Pháp Công gom tinh hoa ngũ hành vạn pháp vào một thân. Đòn đánh của đối thủ bị hóa giải, chuyển thành sinh lực cho chính mình.',
         [('Vạn Pháp Chưởng', 'Chưởng lực mang đủ ngũ hành đánh thẳng vào {mob}.'),
          ('Tiên Tôn Hấp Nguyên', 'Vạn pháp xoay chuyển, rút sinh lực của {mob} về thân ngươi!'),
          ('Vạn Pháp Quy Tông', 'Ngũ hành hợp nhất thành một đạo tiên quang, xuyên thẳng qua {mob}!')]),
}
CAT = {'kim': 'ngoai_cong', 'hoa': 'ngoai_cong', 'tho': 'ngoai_cong', 'thuy': 'khinh_cong', 'moc': 'noi_cong', 'all': 'tap_ky'}
PURPOSE = {'kim': 'pk', 'hoa': 'pk', 'tho': 'farm', 'thuy': 'pk', 'moc': 'farm', 'all': 'farm'}

def build():
    wg, sk = load('wugong_config.json'), load('wugong_skill_config.json')
    out = []
    for el, (wid, src_id, name, kind, dd, hq, pc, mt, moves) in NEW.items():
        a = wg[str(src_id)]
        stat = {k: int(round(a[k] * STAT_MULT / (10.0 if k == 'add_hp' else 1.0))) * (10 if k == 'add_hp' else 1)
                for k in ('add_gj', 'add_fy', 'add_hp', 'add_bj', 'add_xx')}
        wg[str(wid)] = dict({'id': wid, 'wgname': name, 'wgys': COLOR, 'wginfo': '|'.join([kind, dd, hq, pc, mt]),
                             'wglx': 1, 'wgsum': 1, 'wgid': wid}, **stat)
        wg[str(wid)].update({
            'effect_type': a['effect_type'], 'effect_value': EFFECT_NEW[a['effect_type']],
            'element': el, 'category': CAT[el], 'rarity': 'tien_dao', 'purpose': PURPOSE[el],
            'realm_require': REALM, 'training_time_mult': TMULT, 'price_linhthong': 0, 'price_masthach': 0})
        ma = sk['moves'][str(src_id)]
        mv = []
        for i in range(3):
            v = int(round(ma[i]['value'] * MOVE_MULT))
            if ma[i]['effect'] in MOVE_CAP: v = min(v, MOVE_CAP[ma[i]['effect']])
            mv.append(dict(tier=i + 1, name=moves[i][0], effect=ma[i]['effect'], value=v, hits=ma[i]['hits'], text=moves[i][1]))
        sk['moves'][str(wid)] = mv
        out.append((wid, name, stat, a['effect_type'], EFFECT_NEW[a['effect_type']], [(m['effect'], m['value']) for m in mv]))
    save('wugong_config.json', wg)
    save('wugong_skill_config.json', sk)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
