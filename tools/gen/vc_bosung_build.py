# -*- coding: utf-8 -*-
"""
Bổ sung võ công lấp khoảng trống cảnh giới (chủ dự án 2026-10-29): 4 mốc mới × 6 hệ = 24 bộ, wgid 111–134.
  CG 48 (giữa CG 42 – 55), CG 60 (55 – 65), CG 69 (65 – 72): phẩm Hỗn Nguyên, đổi Hỗn Nguyên Tàn Quyển (djid 116).
  CG 78 (giữa CG 72 – 83): phẩm Đại Đạo, đổi Đại Đạo Tàn Quyển (djid 117).
  Số quyển / bộ ghi ở tools/gen/dot_d_build.py (15 / 25 / 35 / 45 — nằm giữa 2 mốc kề).
Chỉ số, hiệu ứng, hệ số thời gian tu luyện, giá trị thế võ = nội suy tuyến tính giữa 2 bộ CÙNG HỆ ở 2 mốc kề
(theo khoảng cách cảnh giới); loại hiệu ứng / số đòn thế võ lấy theo bộ mốc dưới; có trần như các làn trước.
Chạy lại nhiều lần cho cùng kết quả (ghi đè đúng wgid 111–134). Chạy sau lan9_a_build.py, trước dot_d_build.py:
  python3 tools/gen/vc_bosung_build.py && python3 tools/gen/dot_d_build.py
"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from lan3_a_build import load, save

ELEMS = ['kim', 'moc', 'thuy', 'hoa', 'tho', 'all']   # thứ tự wgid trong mỗi mốc (giống làn 5–9)
CAT = {'kim': 'ngoai_cong', 'hoa': 'ngoai_cong', 'tho': 'ngoai_cong', 'thuy': 'khinh_cong', 'moc': 'noi_cong', 'all': 'tap_ky'}
PURPOSE = {'kim': 'pk', 'hoa': 'pk', 'tho': 'farm', 'thuy': 'pk', 'moc': 'farm', 'all': 'farm'}
MOVE_CAP = {'guard': 90, 'stun': 90, 'pierce': 95}
EFFECT_CAP = {'escape': 99}
RARITY = {'hon_nguyen': ('#c084fc', 'Hỗn Nguyên'), 'dai_dao': ('#fde047', 'Đại Đạo')}

# (CG mới, wgid đầu, wgid đầu mốc dưới, CG mốc dưới, wgid đầu mốc trên, CG mốc trên, phẩm, nội dung 6 hệ)
# nội dung: (tên, loại, đặc điểm, hiệu quả, phong cách, mô tả, [(tên thế, văn) ×3]) theo thứ tự ELEMS
TIERS = [
 (48, 111, 81, 42, 87, 55, 'hon_nguyen', [
  ('Huyền Hoàng Liệt Không Kiếm', 'Kiếm Pháp', 'Kiếm xé hư không', 'Xuyên phá phòng ngự rất mạnh',
   'Sắc bén – thẳng tắp – xé toạc hư không',
   'Huyền Hoàng Liệt Không Kiếm lấy khí huyền hoàng của trời đất ngưng thành kiếm. Kiếm khí đi qua, hư không nứt toác như tấm lụa bị xé.',
   [('Huyền Hoàng Kiếm Khí', 'Kiếm khí huyền hoàng xé gió lao thẳng vào {mob}.'),
    ('Liệt Không Trảm', 'Hư không nứt toác theo đường kiếm, phòng ngự của {mob} vỡ vụn!'),
    ('Huyền Hoàng Phá Giới', 'Một kiếm phá tan giới hạn trời đất, chém xuyên qua {mob}!')]),
  ('Thanh Liên Tạo Hóa Kinh', 'Nội Công', 'Thanh liên tái sinh', 'Hồi phục khí huyết rất mạnh mỗi lượt',
   'Thanh tịnh – sinh sôi – sen nở không tàn',
   'Thanh Liên Tạo Hóa Kinh lấy hình tượng đóa thanh liên mọc từ hỗn độn. Sen nở một tầng, thương thế lành một phần; sen nở chín tầng, thân thể như mới.',
   [('Thanh Liên Chưởng', 'Chưởng lực mang hương sen xanh đánh trúng {mob}.'),
    ('Liên Khai Tục Mệnh', 'Thanh liên nở rộ trong kinh mạch, vết thương liền lại ngay trước mắt {mob}!'),
    ('Cửu Phẩm Thanh Liên', 'Chín tầng sen xanh nở rộ, vừa giam chặt {mob} vừa hồi phục toàn thân!')]),
  ('Huyền Minh Lưu Quang Bộ', 'Thân Pháp', 'Bước theo lưu quang', 'Né tránh gần như tuyệt đối, phong tỏa đối thủ',
   'Nhanh – lặng – như ánh sáng trôi trên mặt nước',
   'Huyền Minh Lưu Quang Bộ luyện thân pháp theo ánh sáng phản chiếu dưới đáy biển Huyền Minh. Bóng người lúc tỏ lúc mờ, đối thủ không biết đâu là thật.',
   [('Lưu Quang Chỉ', 'Thân hình hóa vệt sáng, một chỉ điểm thẳng vào {mob}.'),
    ('Huyền Minh Ảnh Độn', 'Bóng người tan vào ánh nước, {mob} đánh tới chỉ trúng khoảng không!'),
    ('Lưu Quang Tỏa Hải', 'Muôn vệt sáng đan thành lưới nước, giam chặt {mob} không lối thoát!')]),
  ('Cửu Dương Phần Hải Quyền', 'Quyền Pháp', 'Quyền nóng đốt biển', 'Bạo kích rất mạnh, thiêu đốt kéo dài',
   'Bá liệt – nóng bỏng – một quyền biển cạn',
   'Cửu Dương Phần Hải Quyền dồn chín luồng dương hỏa vào nắm đấm. Tương truyền người sáng tạo từng một quyền làm cạn nửa vùng biển.',
   [('Cửu Dương Quyền', 'Một quyền mang dương hỏa rực cháy đánh thẳng vào {mob}.'),
    ('Phần Hải Liệt Diễm', 'Dương hỏa bén vào thân {mob}, cháy mãi không tắt!'),
    ('Cửu Dương Quy Nhất', 'Chín luồng dương hỏa hợp làm một, nổ tung ngay giữa ngực {mob}!')]),
  ('Huyền Vũ Trấn Nhạc Thể', 'Hộ Thể', 'Thân như Huyền Vũ', 'Giảm sát thương nhận vào rất mạnh, trấn áp đối thủ',
   'Vững chãi – nặng nề – lấy thủ làm công',
   'Huyền Vũ Trấn Nhạc Thể rèn thân thể theo mai rùa thần Huyền Vũ cõng núi. Đòn đánh tới như gió thổi vào vách đá, một lần phản đòn đủ trấn cả ngọn núi.',
   [('Huyền Vũ Quyền', 'Một quyền nặng như núi đá giáng xuống {mob}.'),
    ('Huyền Giáp Hộ Thân', 'Mai Huyền Vũ hiện quanh người, đòn của {mob} như sóng vỗ vào đá!'),
    ('Trấn Nhạc Phong Sơn', 'Bóng núi đè xuống, {mob} bị trấn chặt không động đậy nổi!')]),
  ('Âm Dương Hỗn Độn Quyết', 'Tạp Kỹ', 'Âm dương xoay chuyển', 'Hút máu phản kích, dùng sức địch đánh địch',
   'Biến hóa – cân bằng – âm dương hợp nhất',
   'Âm Dương Hỗn Độn Quyết xoay hai khí âm dương trong thân như cối xay hỗn độn. Lực của đối thủ đánh vào bị nghiền nát, chuyển thành sinh lực cho chính mình.',
   [('Âm Dương Chưởng', 'Chưởng lực nửa âm nửa dương đánh thẳng vào {mob}.'),
    ('Hỗn Độn Hấp Nguyên', 'Âm dương xoay chuyển, rút sinh lực của {mob} về thân ngươi!'),
    ('Âm Dương Quy Nguyên', 'Hai khí hợp thành một đạo hỗn độn, xuyên thẳng qua {mob}!')]),
 ]),
 (60, 117, 87, 55, 93, 65, 'hon_nguyen', [
  ('Tinh Thần Toái Nguyệt Kiếm', 'Kiếm Pháp', 'Kiếm vỡ trăng sao', 'Xuyên phá phòng ngự rất mạnh',
   'Lạnh lùng – chính xác – một kiếm rụng sao',
   'Tinh Thần Toái Nguyệt Kiếm luyện dưới bầu trời không mây, lấy quỹ đạo tinh tú làm đường kiếm. Kiếm tới mức cao nhất, trăng trên trời cũng vỡ thành mảnh.',
   [('Tinh Thần Kiếm Khí', 'Kiếm khí lấp lánh như sao băng lao thẳng vào {mob}.'),
    ('Toái Nguyệt Liên Trảm', 'Kiếm quang như trăng vỡ rơi xuống, xé nát phòng ngự của {mob}!'),
    ('Tinh Vẫn Nhất Kiếm', 'Cả bầu trời sao rơi theo một kiếm, chém thẳng vào {mob}!')]),
  ('Bất Lão Trường Thanh Kinh', 'Nội Công', 'Xuân xanh bất lão', 'Hồi phục khí huyết rất mạnh mỗi lượt',
   'Bền bỉ – xanh tươi – càng đánh càng sung',
   'Bất Lão Trường Thanh Kinh lấy sinh khí của cây tùng ngàn năm không tàn. Người luyện thành bị thương bao nhiêu, sinh khí lại tuôn ra bấy nhiêu.',
   [('Trường Thanh Chưởng', 'Chưởng lực xanh ngát mang sinh khí ngàn năm đánh trúng {mob}.'),
    ('Bất Lão Hồi Xuân', 'Sinh khí trường thanh rót vào kinh mạch, vết thương liền lại trước mắt {mob}!'),
    ('Vạn Cổ Trường Thanh', 'Rừng tùng vạn cổ mọc quanh người, vừa trói chặt {mob} vừa hồi phục toàn thân!')]),
  ('Hàn Uyên Phiêu Tuyết Bộ', 'Thân Pháp', 'Bước như tuyết bay', 'Né tránh gần như tuyệt đối, phong tỏa đối thủ',
   'Nhẹ – lạnh – không dấu chân trên tuyết',
   'Hàn Uyên Phiêu Tuyết Bộ luyện trên vực băng vạn trượng, bước chân nhẹ như bông tuyết rơi. Đối thủ chưa kịp nhìn rõ đã bị hàn khí đóng băng.',
   [('Phiêu Tuyết Chỉ', 'Một chỉ lạnh buốt như tuyết rơi điểm thẳng vào {mob}.'),
    ('Hàn Uyên Tuyết Ảnh', 'Tuyết bay mù trời, {mob} đánh vào bóng nào cũng trượt!'),
    ('Băng Phong Vạn Lý', 'Hàn khí đóng băng cả vùng trời, giam chặt {mob} trong băng!')]),
  ('Phượng Hoàng Niết Bàn Quyền', 'Quyền Pháp', 'Lửa phượng hoàng', 'Bạo kích rất mạnh, thiêu đốt kéo dài',
   'Rực rỡ – bùng nổ – tàn rồi lại cháy',
   'Phượng Hoàng Niết Bàn Quyền mô phỏng phượng hoàng tự thiêu rồi tái sinh từ tro. Mỗi quyền như một lần niết bàn, lửa sau mạnh hơn lửa trước.',
   [('Phượng Hỏa Quyền', 'Một quyền mang lửa phượng hoàng đánh thẳng vào {mob}.'),
    ('Niết Bàn Liệt Diễm', 'Lửa niết bàn bén vào thân {mob}, cháy mãi không tắt!'),
    ('Phượng Hoàng Trùng Sinh', 'Phượng hoàng lửa tung cánh lao xuống, nổ tung trên người {mob}!')]),
  ('Thái Sơn Bất Hoại Thể', 'Hộ Thể', 'Thân như Thái Sơn', 'Giảm sát thương nhận vào rất mạnh, trấn áp đối thủ',
   'Trầm ổn – bất động – vạn đòn không lay',
   'Thái Sơn Bất Hoại Thể rèn thân thể theo thế núi Thái Sơn đứng giữa đất trời. Luyện thành thì đao kiếm chém vào chỉ tóe lửa, người vẫn đứng nguyên chỗ cũ.',
   [('Thái Sơn Quyền', 'Một quyền nặng như núi Thái Sơn giáng xuống {mob}.'),
    ('Bất Hoại Kim Thân', 'Thân thể rắn như đá núi, đòn của {mob} chỉ để lại vết xước!'),
    ('Thái Sơn Áp Đỉnh', 'Bóng núi Thái Sơn đè xuống, {mob} quỳ rạp không đứng dậy nổi!')]),
  ('Thiên Địa Vô Cực Quyết', 'Tạp Kỹ', 'Thiên địa vô cực', 'Hút máu phản kích, dùng sức địch đánh địch',
   'Bao la – vô tận – thu cả đất trời vào thân',
   'Thiên Địa Vô Cực Quyết coi thân người là một tiểu thiên địa. Đòn đánh tới bị cuốn vào vô cực, hóa thành sinh lực nuôi tiểu thiên địa ấy.',
   [('Vô Cực Chưởng', 'Chưởng lực mang khí thiên địa đánh thẳng vào {mob}.'),
    ('Thiên Địa Hấp Nguyên', 'Tiểu thiên địa xoay chuyển, rút sinh lực của {mob} về thân ngươi!'),
    ('Vô Cực Quy Tông', 'Thiên địa hợp nhất thành một đạo quang, xuyên thẳng qua {mob}!')]),
 ]),
 (69, 123, 93, 65, 99, 72, 'hon_nguyen', [
  ('Thất Sát Đoạt Mệnh Kiếm', 'Kiếm Pháp', 'Kiếm đoạt mệnh', 'Xuyên phá phòng ngự cực mạnh',
   'Sát khí – dứt khoát – kiếm ra là lấy mạng',
   'Thất Sát Đoạt Mệnh Kiếm mượn sát khí sao Thất Sát vào kiếm. Kiếm không có chiêu thủ, mỗi đường kiếm đều nhắm vào chỗ hiểm của đối thủ.',
   [('Thất Sát Kiếm Khí', 'Kiếm khí đỏ thẫm mang sát ý lao thẳng vào {mob}.'),
    ('Đoạt Mệnh Liên Hoàn', 'Bảy đường kiếm liên tiếp đâm vào chỗ hiểm, phòng ngự của {mob} tan rã!'),
    ('Thất Sát Tuyệt Mệnh', 'Sát tinh giáng thế, một kiếm chém thẳng vào mệnh môn của {mob}!')]),
  ('Thần Nông Bách Thảo Kinh', 'Nội Công', 'Bách thảo dưỡng thân', 'Hồi phục khí huyết cực mạnh mỗi lượt',
   'Ôn hòa – bền bỉ – trăm cỏ nuôi trăm mạch',
   'Thần Nông Bách Thảo Kinh gom dược tính của trăm loài linh thảo vào tâm pháp. Khí huyết vận một vòng là vết thương tự khép, độc tố tự tan.',
   [('Bách Thảo Chưởng', 'Chưởng lực thơm mùi linh thảo đánh trúng {mob}.'),
    ('Thần Nông Tục Mạch', 'Dược khí bách thảo tuôn trong kinh mạch, thương thế lành ngay trước mắt {mob}!'),
    ('Bách Thảo Quy Nguyên', 'Trăm loài linh thảo mọc quanh người, vừa trói chặt {mob} vừa hồi phục toàn thân!')]),
  ('Bích Lạc Hoàng Tuyền Bộ', 'Thân Pháp', 'Đi giữa trời và đất', 'Né tránh gần như tuyệt đối, phong tỏa đối thủ',
   'Quỷ mị – khó lường – lên trời xuống đất',
   'Bích Lạc Hoàng Tuyền Bộ tương truyền lên tới tầng trời Bích Lạc, xuống tới suối Hoàng Tuyền mà không để lại dấu chân. Đối thủ chỉ thấy bóng người lúc trên lúc dưới.',
   [('Hoàng Tuyền Chỉ', 'Một chỉ lạnh lẽo từ dưới đất điểm thẳng vào {mob}.'),
    ('Bích Lạc Huyễn Ảnh', 'Bóng người lúc trên trời lúc dưới đất, {mob} đánh vào khoảng không!'),
    ('Hoàng Tuyền Phong Lộ', 'Suối Hoàng Tuyền dâng lên thành ngục, giam chặt {mob} không lối thoát!')]),
  ('Lôi Hỏa Phá Thiên Quyền', 'Quyền Pháp', 'Sấm lửa phá trời', 'Bạo kích cực mạnh, thiêu đốt kéo dài',
   'Cuồng bạo – chớp nhoáng – sấm đi kèm lửa',
   'Lôi Hỏa Phá Thiên Quyền hòa sấm sét và lửa trời vào một quyền. Quyền ra có sấm mở đường, lửa theo sau thiêu sạch những gì sấm chưa đánh nát.',
   [('Lôi Hỏa Quyền', 'Một quyền mang sấm lửa đánh thẳng vào {mob}.'),
    ('Thiên Hỏa Phần Thân', 'Lửa trời bén vào thân {mob}, cháy mãi không tắt!'),
    ('Lôi Hỏa Phá Thiên', 'Sấm sét và lửa trời cùng nổ, nuốt trọn {mob}!')]),
  ('Bàn Cổ Kim Cương Thể', 'Hộ Thể', 'Thân Bàn Cổ', 'Giảm sát thương nhận vào cực mạnh, trấn áp đối thủ',
   'Nguyên thủy – nặng nề – thân cứng hơn đá',
   'Bàn Cổ Kim Cương Thể rèn thân thể theo truyền thuyết Bàn Cổ đội trời đạp đất. Thân thể luyện thành cứng như kim cương, đòn đánh tới chỉ như mưa rơi vào đá.',
   [('Bàn Cổ Quyền', 'Một quyền nặng như trời sập giáng xuống {mob}.'),
    ('Kim Cương Hộ Thể', 'Thân thể sáng như kim cương, đòn của {mob} chỉ để lại vết xước!'),
    ('Đội Thiên Trấn Địa', 'Uy lực Bàn Cổ đè xuống, {mob} bị trấn chặt không động đậy nổi!')]),
  ('Ngũ Hành Luân Chuyển Quyết', 'Tạp Kỹ', 'Ngũ hành tương sinh', 'Hút máu phản kích, dùng sức địch đánh địch',
   'Tuần hoàn – tương sinh – sức địch thành sức ta',
   'Ngũ Hành Luân Chuyển Quyết cho năm khí kim, mộc, thủy, hỏa, thổ chạy vòng tương sinh trong thân. Đòn đánh tới bị cuốn vào vòng ấy, đi hết một vòng đã thành sinh lực.',
   [('Ngũ Hành Chưởng', 'Chưởng lực mang đủ ngũ hành đánh thẳng vào {mob}.'),
    ('Tương Sinh Hấp Nguyên', 'Ngũ hành xoay vòng, rút sinh lực của {mob} về thân ngươi!'),
    ('Ngũ Hành Quy Nhất', 'Năm khí hợp thành một đạo quang, xuyên thẳng qua {mob}!')]),
 ]),
 (78, 129, 99, 72, 105, 83, 'dai_dao', [
  ('Đại Đạo Vô Thượng Kiếm', 'Kiếm Pháp', 'Kiếm vô thượng', 'Xuyên phá phòng ngự tuyệt đối',
   'Vô thượng – vô địch – kiếm ra trời đất lặng',
   'Đại Đạo Vô Thượng Kiếm là kiếm của người đã chạm tới đại đạo. Kiếm không cần nhanh, cũng không cần mạnh, chỉ cần đã ra là không gì cản nổi.',
   [('Vô Thượng Kiếm Quang', 'Kiếm quang đại đạo lao thẳng vào {mob}.'),
    ('Đại Đạo Phá Pháp', 'Kiếm ý chém đứt pháp tắc hộ thân, phòng ngự của {mob} sụp đổ!'),
    ('Vô Thượng Nhất Kiếm', 'Trời đất lặng đi trước một kiếm, {mob} không còn chỗ dung thân!')]),
  ('Đại Đạo Sinh Sinh Bất Tức Kinh', 'Nội Công', 'Sinh sinh bất tức', 'Hồi phục khí huyết tuyệt đối mỗi lượt',
   'Bất tận – tuần hoàn – sống mãi không ngừng',
   'Đại Đạo Sinh Sinh Bất Tức Kinh lấy lẽ sinh sôi không ngừng của đại đạo làm gốc. Sinh khí trong thân như dòng sông không bao giờ cạn.',
   [('Sinh Sinh Chưởng', 'Chưởng lực mang sinh khí đại đạo đánh trúng {mob}.'),
    ('Bất Tức Tục Mệnh', 'Sinh khí tuôn chảy không ngừng, thương thế lành ngay trước mắt {mob}!'),
    ('Đại Đạo Sinh Sinh', 'Đại đạo sinh sôi bao trùm, vừa giam chặt {mob} vừa hồi phục toàn thân!')]),
  ('Đại Đạo Hư Không Bộ', 'Thân Pháp', 'Bước trong hư không', 'Né tránh gần như tuyệt đối, phong tỏa đối thủ',
   'Hư vô – tĩnh lặng – bước đi không chạm đất',
   'Đại Đạo Hư Không Bộ đưa thân thể đi giữa các tầng hư không. Người ngoài chỉ thấy một bước đã xa nghìn trượng, một bước nữa đã đứng sau lưng.',
   [('Hư Không Chỉ', 'Một chỉ từ giữa hư không điểm thẳng vào {mob}.'),
    ('Hư Không Độn Ảnh', 'Thân hình tan vào hư không, {mob} đánh tới chỉ trúng khoảng không!'),
    ('Hư Không Phong Ấn', 'Hư không khép lại thành ngục, giam chặt {mob} không lối thoát!')]),
  ('Đại Đạo Diệt Tinh Quyền', 'Quyền Pháp', 'Quyền diệt tinh tú', 'Bạo kích tuyệt đối, thiêu đốt vạn vật',
   'Hủy diệt – rực cháy – một quyền tắt sao',
   'Đại Đạo Diệt Tinh Quyền dồn sức nóng của trăm vì sao vào nắm đấm. Quyền ra như sao băng rơi, nơi nào trúng phải chỉ còn lại tro bụi.',
   [('Diệt Tinh Quyền', 'Một quyền nóng như lõi sao đánh thẳng vào {mob}.'),
    ('Tinh Hỏa Phần Thân', 'Lửa sao bén vào thân {mob}, cháy mãi không tắt!'),
    ('Đại Đạo Diệt Tinh', 'Trăm vì sao cùng nổ tung, biển lửa nuốt trọn {mob}!')]),
  ('Đại Đạo Bất Diệt Kim Thân', 'Hộ Thể', 'Kim thân bất diệt', 'Giảm sát thương nhận vào tuyệt đối, trấn áp đối thủ',
   'Bất diệt – trấn áp – thân là đạo',
   'Đại Đạo Bất Diệt Kim Thân đúc thân thể thành kim thân theo khuôn đại đạo. Đòn đánh tới chưa chạm da đã tan, uy áp kim thân đủ khiến đối thủ quỳ xuống.',
   [('Kim Thân Quyền', 'Một quyền nặng như núi vàng nện xuống {mob}.'),
    ('Bất Diệt Hộ Thể', 'Kim thân phát sáng, đòn của {mob} chỉ để lại vết xước!'),
    ('Kim Thân Trấn Áp', 'Uy áp kim thân đè xuống, {mob} quỳ rạp không đứng dậy nổi!')]),
  ('Đại Đạo Thái Cực Quyết', 'Tạp Kỹ', 'Thái cực sinh lưỡng nghi', 'Hút máu phản kích, chuyển đòn địch thành sinh lực',
   'Mềm mà mạnh – tròn mà sắc – lấy nhu chế cương',
   'Đại Đạo Thái Cực Quyết đưa mọi lực vào vòng thái cực. Đòn mạnh tới đâu cũng bị xoay tròn, hóa giải rồi trả về thành sinh lực cho người luyện.',
   [('Thái Cực Chưởng', 'Chưởng lực xoay tròn như vòng thái cực đánh thẳng vào {mob}.'),
    ('Lưỡng Nghi Hấp Nguyên', 'Âm dương xoay chuyển, rút sinh lực của {mob} về thân ngươi!'),
    ('Thái Cực Quy Đạo', 'Vòng thái cực hóa thành một đạo quang, xuyên thẳng qua {mob}!')]),
 ]),
]

def lerp(a, b, t):
    return a + (b - a) * t

def build():
    wg, sk = load('wugong_config.json'), load('wugong_skill_config.json')
    old_names = set(v['wgname'] for k, v in wg.items() if not (111 <= int(k) <= 134))
    out = []
    for cg, base, lo_base, lo_cg, hi_base, hi_cg, rarity, content in TIERS:
        t = float(cg - lo_cg) / (hi_cg - lo_cg)
        color, rname = RARITY[rarity]
        for i, el in enumerate(ELEMS):
            wid = base + i
            lo, hi = wg[str(lo_base + i)], wg[str(hi_base + i)]
            assert lo['element'] == el and hi['element'] == el, (wid, el)
            name, kind, dd, hq, pc, mt, moves = content[i]
            assert name not in old_names, name
            stat = {}
            for k in ('add_gj', 'add_fy', 'add_hp', 'add_bj', 'add_xx'):
                v = lerp(lo[k], hi[k], t)
                stat[k] = int(round(v / 10.0)) * 10 if k == 'add_hp' else int(round(v))
            et = lo['effect_type']
            ev = int(round(lerp(lo['effect_value'], hi['effect_value'], t)))
            if et in EFFECT_CAP: ev = min(ev, EFFECT_CAP[et])
            wg[str(wid)] = dict({'id': wid, 'wgname': name, 'wgys': color,
                                 'wginfo': '|'.join([kind + ' / ' + rname, dd, hq, pc, mt]),
                                 'wglx': 1, 'wgsum': 1, 'wgid': wid}, **stat)
            wg[str(wid)].update({
                'effect_type': et, 'effect_value': ev,
                'element': el, 'category': CAT[el], 'rarity': rarity, 'purpose': PURPOSE[el],
                'realm_require': cg,
                'training_time_mult': round(lerp(lo['training_time_mult'], hi['training_time_mult'], t), 1),
                'price_linhthong': 0, 'price_masthach': 0})
            ml, mh = sk['moves'][str(lo_base + i)], sk['moves'][str(hi_base + i)]
            mv = []
            for j in range(3):
                v = int(round(lerp(ml[j]['value'], mh[j]['value'], t)))
                if ml[j]['effect'] in MOVE_CAP: v = min(v, MOVE_CAP[ml[j]['effect']])
                mv.append(dict(tier=j + 1, name=moves[j][0], effect=ml[j]['effect'], value=v, hits=ml[j]['hits'], text=moves[j][1]))
            sk['moves'][str(wid)] = mv
            out.append((wid, cg, name, stat, et, ev, [(m['effect'], m['value']) for m in mv]))
    save('wugong_config.json', wg)
    save('wugong_skill_config.json', sk)
    return out

if __name__ == '__main__':
    for r in build():
        print(r)
