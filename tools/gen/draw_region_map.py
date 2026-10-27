# -*- coding: utf-8 -*-
"""
Vẽ ảnh sơ đồ khu vực dt/ditu/<qyid>.png (cùng phong cách ảnh khu 27) từ bảng mid/qy trong DB.
Vị trí nút suy từ hướng nối (mup/mdown/mleft/mright).
Chạy: python3 tools/gen/draw_region_map.py 31 32 33   (cần mysql CLI kết nối được DB game)
"""
import os, sys, subprocess
from PIL import Image, ImageDraw, ImageFont

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
FONT = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'
FONTB = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'
W, H = 1448, 1086

def query(sql):
    out = subprocess.run(['mysql', '-uroot', '--default-character-set=utf8mb4', '-N', 'game', '-e', sql],
                         capture_output=True, text=True, check=True).stdout
    return [l.split('\t') for l in out.strip().split('\n') if l]

def wrap(text, font, maxw, draw):
    words, lines, cur = text.split(), [], ''
    for w in words:
        t = (cur + ' ' + w).strip()
        if draw.textlength(t, font=font) <= maxw: cur = t
        else: lines.append(cur); cur = w
    if cur: lines.append(cur)
    return lines

def draw_region(qyid):
    qy = query('SELECT qyname, mid FROM qy WHERE qyid = %d' % qyid)
    if not qy: print('khong co khu', qyid); return
    import re
    qname = re.sub('<[^>]+>', '', qy[0][0]).strip()
    entry = int(qy[0][1])
    rows = query('SELECT mid, mname, mup, mdown, mleft, mright, mgid, midboss FROM mid WHERE mqy = %d' % qyid)
    maps = {int(r[0]): dict(name=r[1], up=int(r[2]), down=int(r[3]), left=int(r[4]), right=int(r[5]), mgid=r[6], boss=int(r[7])) for r in rows}
    # Đặt toạ độ lưới bằng BFS từ cửa vào
    pos, queue = {entry: (0, 0)}, [entry]
    step = {'up': (0, -1), 'down': (0, 1), 'left': (-1, 0), 'right': (1, 0)}
    while queue:
        m = queue.pop(0)
        for d, (dx, dy) in step.items():
            n = maps[m][d]
            if n in maps and n not in pos:
                p = (pos[m][0] + dx, pos[m][1] + dy)
                while p in pos.values(): p = (p[0], p[1] + 1)
                pos[n] = p; queue.append(n)
    for m in maps:  # map không nối được vẫn vẽ ra
        if m not in pos: pos[m] = (max(x for x, y in pos.values()) + 1, 0)
    xs = [p[0] for p in pos.values()]; ys = [p[1] for p in pos.values()]
    cols, rws = max(xs) - min(xs) + 1, max(ys) - min(ys) + 1
    box = (70, 150, W - 70, H - 130)
    cw = (box[2] - box[0]) / max(cols, 1); ch = (box[3] - box[1]) / max(rws, 1)
    center = {m: (box[0] + (p[0] - min(xs) + 0.5) * cw, box[1] + (p[1] - min(ys) + 0.5) * ch) for m, p in pos.items()}

    im = Image.new('RGB', (W, H), '#ffffff')
    d = ImageDraw.Draw(im)
    d.ellipse((W - 380, -120, W + 200, 300), fill='#eef4fb')
    d.text((70, 40), qname, font=ImageFont.truetype(FONTB, 54), fill='#16213a')
    d.text((72, 108), 'Khu %d - sơ đồ đường đi' % qyid, font=ImageFont.truetype(FONTB, 24), fill='#1f6fd1')
    d.rounded_rectangle((55, 140, W - 55, H - 70), radius=26, outline='#9cc4ef', width=4, fill='#fbfdff')
    # Đường nối
    for m, info in maps.items():
        for dname in ('right', 'down'):
            n = info[dname]
            if n in maps: d.line([center[m], center[n]], fill='#f5a524', width=6)
            elif n: d.line([center[m], (center[m][0] + (60 if dname == 'right' else 0), center[m][1] + (60 if dname == 'down' else 0))], fill='#9aa5b1', width=5)
        if info['up'] and info['up'] not in maps: d.line([center[m], (center[m][0], center[m][1] - 70)], fill='#9aa5b1', width=5)
        if info['left'] and info['left'] not in maps: d.line([center[m], (center[m][0] - 80, center[m][1])], fill='#9aa5b1', width=5)
    f_name = ImageFont.truetype(FONTB, 22); f_id = ImageFont.truetype(FONTB, 16); f_sub = ImageFont.truetype(FONT, 16)
    for m, info in maps.items():
        cx, cy = center[m]
        bw = min(250, cw - 24)
        lines = wrap(info['name'], f_name, bw - 20, d)[:2]
        bh = 74 + 26 * (len(lines) - 1)
        isEntry = (m == entry)
        fill, out = ('#fff3c4', '#f0a500') if isEntry else (('#ffe4e1', '#e05a47') if info['boss'] else ('#eef5ff', '#5b9be0'))
        d.rounded_rectangle((cx - bw / 2, cy - bh / 2, cx + bw / 2, cy + bh / 2), radius=14, fill=fill, outline=out, width=3)
        ty = cy - 13 * len(lines) - (8 if info['mgid'] or info['boss'] else 0)
        for ln in lines:
            d.text((cx - d.textlength(ln, font=f_name) / 2, ty), ln, font=f_name, fill='#16213a'); ty += 26
        sub = ('☠ Boss' if info['boss'] else '') + ((' · ' if info['boss'] else '') + 'có quái' if info['mgid'] else '')
        if sub: d.text((cx - d.textlength(sub, font=f_sub) / 2, ty), sub, font=f_sub, fill='#a33' if info['boss'] else '#3b6')
        tag = str(m); tw = d.textlength(tag, font=f_id) + 16
        d.rounded_rectangle((cx - bw / 2 - 6, cy - bh / 2 - 14, cx - bw / 2 - 6 + tw, cy - bh / 2 + 10), radius=10, fill='#16213a')
        d.text((cx - bw / 2 + 2, cy - bh / 2 - 12), tag, font=f_id, fill='#ffffff')
    d.rounded_rectangle((70, H - 120, W - 70, H - 82), radius=12, fill='#f1f5fa', outline='#d5deea')
    leg = 'Nút vàng: cửa vào khu   |   Nút đỏ: có Boss   |   Đường cam: lối đi trong khu   |   Đường xám: lối sang khu khác'
    d.text((90, H - 112), leg, font=ImageFont.truetype(FONTB, 18), fill='#33475b')
    cnt = '%d địa điểm' % len(maps)
    d.text((W - 90 - d.textlength(cnt, font=f_id), H - 110), cnt, font=f_id, fill='#1f6fd1')
    path = os.path.join(ROOT, 'dt', 'ditu', '%d.png' % qyid)
    im.save(path, optimize=True)
    print('da ve', path, '(%d map)' % len(maps))

if __name__ == '__main__':
    for a in sys.argv[1:]: draw_region(int(a))
