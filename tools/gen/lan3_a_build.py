# -*- coding: utf-8 -*-
"""
Làn 3 — L3-A (docs/LAN3_CAP_1000_1500.md mục 4): 5 võ công Tuyệt Thế (rarity than_cong) yêu cầu cảnh giới 25 (cấp 1250),
1 bộ / hệ, bán ở Thiên Cơ Các 60.000 Ma Thạch (bằng bộ CG 25 có sẵn "Canh Dư Thặng Kỹ").
Chỉ số / hiệu ứng / thế võ nằm giữa bộ Tuyệt Thế CG 23 và Tiên Đạo CG 30 cùng hệ (2/7 quãng — CG 25 trên thang 23 → 30).
Chạy lại nhiều lần cho cùng kết quả (ghi đè đúng wgid 70–74).
Chạy: python3 tools/gen/lan3_a_build.py
"""
import json, os

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CFG = os.path.join(ROOT, 'class', 'config')
def load(n): return json.load(open(os.path.join(CFG, n), encoding='utf-8'))
def save(n, d):
    with open(os.path.join(CFG, n), 'w', encoding='utf-8') as f:
        json.dump(d, f, ensure_ascii=False, indent=2)
        f.write('\n')

T = 2.0 / 7.0   # CG 25 trên thang CG 23 -> 30
REALM, PRICE_MT, TMULT, COLOR = 25, 60000, 18.0, '#f9c508'
# hệ -> (wgid, bộ CG 23 cùng hệ, bộ Tiên Đạo cùng hệ, tên, loại hiển thị, đặc điểm, hiệu quả, phong cách, mô tả,
#        [(tên thế 1..3, văn)])  — hiệu ứng / số đòn thế võ lấy theo bộ cùng hệ, giá trị nội suy
NEW = {
 'kim': (70, 58, 64, 'Thiên Đế Phá Quân Kiếm', 'Kiếm Pháp / Ngoại Công', 'Kiếm khí phá quân',
         'Xuyên phá phòng ngự, kiếm sau mạnh hơn kiếm trước', 'Bá kiếm – phá trận – một kiếm định càn khôn',
         'Thiên Đế Phá Quân Kiếm tương truyền do một vị đế giả sáng tạo khi một mình phá vỡ thiên quân vạn mã. Kiếm ý thẳng tắp, không vòng vo, mọi lớp phòng ngự đều bị chém toạc.',
         [('Phá Quân Kiếm Khí', 'Một đạo kiếm khí vàng rực xé gió lao thẳng vào {mob}.'),
          ('Đế Uy Liệt Trận', 'Kiếm khí hóa thành đế uy, liên tiếp xé nát thế trận phòng thủ của {mob}!'),
          ('Thiên Đế Nhất Kiếm', 'Đế giả chỉ cần một kiếm — kiếm quang xuyên thủng trời đất, chém thẳng vào {mob}!')]),
 'moc': (71, 59, 65, 'Vạn Mộc Hồi Xuân Quyết', 'Nội Công / Tâm Pháp', 'Vạn mộc sinh sôi',
         'Hồi phục khí huyết mạnh mỗi lượt, đánh lâu không mệt', 'Sinh sôi – bền bỉ – cây đổ lại mọc',
         'Vạn Mộc Hồi Xuân Quyết lấy sinh khí của vạn cây rừng nuôi dưỡng thân thể. Người luyện thành như cổ thụ ngàn năm, chặt một cành lại đâm mười chồi.',
         [('Vạn Mộc Chưởng', 'Chưởng lực xanh biếc như rễ cây đâm xuyên mặt đất, quật trúng {mob}.'),
          ('Hồi Xuân Tục Mạch', 'Sinh khí vạn mộc rót vào kinh mạch, vết thương liền lại ngay trước mắt {mob}!'),
          ('Vạn Mộc Triều Tông', 'Cả khu rừng hướng về ngươi, rễ cây trói chặt {mob} và tuôn sinh lực vào thân ngươi!')]),
 'thuy': (72, 60, 66, 'Bích Hải Triều Sinh Khúc', 'Thân Pháp / Khinh Công', 'Sóng biếc khó lường',
          'Né tránh rất cao, thủy triều phong tỏa đối thủ', 'Phiêu hốt – triều dâng – lấy nhu khắc cương',
          'Bích Hải Triều Sinh Khúc mô phỏng nhịp thủy triều biển biếc: lúc lên lúc xuống, không ai đoán được. Thân pháp theo nhịp sóng, đối thủ đánh vào chỉ trúng bọt nước.',
          [('Bích Hải Thích', 'Thân hình lướt trên sóng biếc, một chỉ điểm thẳng vào {mob}.'),
           ('Triều Sinh Huyễn Ảnh', 'Thủy triều dâng lên, bóng người tan vào sóng, {mob} đánh vào khoảng không!'),
           ('Thương Hải Phong Tỏa', 'Biển biếc cuộn trào thành tường nước, khóa chặt {mob} giữa vạn tầng sóng!')]),
 'hoa': (73, 61, 67, 'Phần Thiên Liệt Diễm Chưởng', 'Chưởng Pháp / Ngoại Công', 'Liệt diễm thiêu trời',
         'Bạo kích cực mạnh, lửa thiêu đốt đối thủ', 'Bạo liệt – thiêu đốt – một chưởng cháy trời',
         'Phần Thiên Liệt Diễm Chưởng dồn hỏa khí tới mức có thể đốt cháy cả tầng mây. Chưởng phong đi qua đâu, không khí nơi đó đều méo mó vì nóng.',
         [('Liệt Diễm Chưởng', 'Một chưởng lửa đỏ rực đánh thẳng vào ngực {mob}.'),
          ('Phần Thiên Hỏa Võng', 'Lưới lửa giăng kín bầu trời rồi sập xuống, thiêu đốt {mob}!'),
          ('Liệt Diễm Phần Thiên', 'Hỏa khí bùng nổ thành mặt trời thứ hai, nuốt trọn {mob} trong biển lửa!')]),
 'tho': (74, 62, 68, 'Thái Sơn Áp Đỉnh Công', 'Hộ Thể / Ngoại Công', 'Thân nặng như núi',
         'Giảm sát thương nhận vào cực mạnh, chấn choáng đối thủ', 'Vững chãi – đè ép – lấy thế núi áp người',
         'Thái Sơn Áp Đỉnh Công luyện thân thể nặng như núi Thái, đứng yên thì vạn đòn không lay, ra tay thì như núi đổ ập xuống đầu đối thủ.',
         [('Thái Sơn Quyền', 'Một quyền nặng như núi giáng thẳng xuống {mob}.'),
          ('Sơn Nhạc Hộ Thân', 'Thổ khí ngưng thành núi đá bao quanh, đòn của {mob} chỉ làm rơi vài hạt bụi!'),
          ('Thái Sơn Áp Đỉnh', 'Bóng núi Thái khổng lồ ập xuống, {mob} bị đè chặt không cựa nổi!')]),
}
CAT = {'kim': 'ngoai_cong', 'hoa': 'ngoai_cong', 'tho': 'ngoai_cong', 'thuy': 'khinh_cong', 'moc': 'noi_cong'}
PURPOSE = {'kim': 'pk', 'hoa': 'pk', 'tho': 'farm', 'thuy': 'pk', 'moc': 'farm'}

def lerp(a, b):
    return a + (b - a) * T

def build():
    wg, sk = load('wugong_config.json'), load('wugong_skill_config.json')
    out = []
    for el, (wid, a_id, b_id, name, kind, dd, hq, pc, mt, moves) in NEW.items():
        a, b = wg[str(a_id)], wg[str(b_id)]
        stat = {k: int(round(lerp(a[k], b[k]) / (10.0 if k == 'add_hp' else 1.0))) * (10 if k == 'add_hp' else 1)
                for k in ('add_gj', 'add_fy', 'add_hp', 'add_bj', 'add_xx')}
        wg[str(wid)] = dict({
            'id': wid, 'wgname': name, 'wgys': COLOR, 'wginfo': '|'.join([kind, dd, hq, pc, mt]),
            'wglx': 1, 'wgsum': 1, 'wgid': wid}, **stat)
        wg[str(wid)].update({
            'effect_type': a['effect_type'], 'effect_value': int(round(lerp(a['effect_value'], b['effect_value']))),
            'element': el, 'category': CAT[el], 'rarity': 'than_cong', 'purpose': PURPOSE[el],
            'realm_require': REALM, 'training_time_mult': TMULT, 'price_linhthong': 0, 'price_masthach': PRICE_MT})
        ma, mb = sk['moves'][str(a_id)], sk['moves'][str(b_id)]
        sk['moves'][str(wid)] = [dict(tier=i + 1, name=moves[i][0], effect=ma[i]['effect'],
                                      value=int(round(lerp(ma[i]['value'], mb[i]['value']))), hits=ma[i]['hits'], text=moves[i][1])
                                 for i in range(3)]
        out.append((wid, name, stat, wg[str(wid)]['effect_type'], wg[str(wid)]['effect_value']))
    save('wugong_config.json', wg)
    save('wugong_skill_config.json', sk)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
