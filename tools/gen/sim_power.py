# -*- coding: utf-8 -*-
"""Mô phỏng sức mạnh nhân vật chuẩn (đủ bộ trang bị, cường hóa N) vs quái thật cùng cấp. Chạy: python3 tools/gen/sim_power.py 10"""
import json,sys
sys.path.insert(0,'tools/gen'); from dot_f_build import BASE
z=json.load(open('class/config/zhuangbei_config.json',encoding='utf-8'))
gw=json.load(open('class/config/guaiwu_config.json',encoding='utf-8'))
TI=sorted({v['zblv'] for v in z.values()})
def gear(T,qh):
    best={}
    for v in sorted(z.values(),key=lambda v:v['zbid']):
        if v['zblv']==T and v['zbtool'] not in best: best[v['zbtool']]=v
    m=1+0.08*qh
    return [sum(v[k] for v in best.values())*m for k in ('zbgj','zbfy','zbhp')]
qh=int(sys.argv[1]) if len(sys.argv)>1 else 10
# lấy quái thật trong config theo cấp gần nhất (loại Thường: chọn con HP thấp nhất trong ±3 cấp)
mons=[(int(v['glv']),int(v['ghp']),int(v['ggj']),int(v['gfy']),v['gname']) for v in gw.values() if str(v.get('ghp','')).isdigit()]
print('cap | cong/phong/mau nhan vat | quai (ten lv) mau/cong/thu | don ha quai | quai can don')
for L in (5,10,20,30,45,60,80,100,120,150,200,250,300,350,400,450,500):
    T=max(t for t in TI if t<=L); g=gear(T,qh); b=BASE[L]
    tg,tf,th=b[0]+g[0],b[1]+g[1],b[2]+g[2]
    near=[m for m in mons if abs(m[0]-L)<=3] or [min(mons,key=lambda m:abs(m[0]-L))]
    m=min(near,key=lambda m:m[1])
    d=max(tg*0.15,tg-0.75*m[3]); t=max(m[2]*0.15,m[2]-0.75*tf)
    print('%3d | %6d %6d %7d | %-22s %7d %6d %5d | %5.1f | %6.1f'%(L,tg,tf,th,'%s lv%d'%(m[4][:14],m[0]),m[1],m[2],m[3],m[1]/d,th/t))
