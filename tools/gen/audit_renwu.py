# -*- coding: utf-8 -*-
"""Rà nhiệm vụ NPC (renwu): NPC giao/trả, quái/vật phẩm/map đích. Cần mysql CLI. Chạy: python3 tools/gen/audit_renwu.py 41 60"""
import json,subprocess,sys
C='class/config/'
r=json.load(open(C+'renwu_config.json',encoding='utf-8'))
n=json.load(open(C+'npc_config.json',encoding='utf-8'))
gw=json.load(open(C+'guaiwu_config.json',encoding='utf-8'))
dj=json.load(open(C+'daoju_config.json',encoding='utf-8'))
yd=json.load(open(C+'yaodan_config.json',encoding='utf-8'))
def q(sql):
    o=subprocess.run(['mysql','-uroot','--default-character-set=utf8mb4','-N','game','-e',sql],capture_output=True,text=True,check=True).stdout
    return [l.split('\t') for l in o.strip().split('\n') if l]
maps={int(a):(b,c,int(d)) for a,b,c,d in q("select mid,mname,mgid,mqy from mid")}
npcmap={}
for a,b in q("select mid,mnpc from mid where mnpc<>''"):
    for x in b.split(','):
        if x.strip(): npcmap.setdefault(int(x),[]).append(int(a))
spawn={}
for mid,(nm,mg,qy) in maps.items():
    for part in mg.split(','):
        if '|' in part: spawn.setdefault(int(part.split('|')[0]),[]).append(mid)
drops={}
for k,g in gw.items():
    for part in str(g.get('gdj','')).split(','):
        if part.strip(): drops.setdefault(int(part.split('|')[0]),[]).append(int(k))
giver={}
for k,v in n.items():
    for t in str(v['taskid']).split(','):
        if t.strip(): giver.setdefault(int(t),[]).append(int(k))
LX={'1':'Thường','2':'Ngày','3':'Chính'}; ZL={'1':'Thu thập','2':'Diệt quái','3':'Tìm NPC','4':'Luyện đan'}
lo=int(sys.argv[1]) if len(sys.argv)>1 else 0; hi=int(sys.argv[2]) if len(sys.argv)>2 else 69
for k in sorted(r,key=int):
    if not lo<=int(k)<=hi: continue
    t=r[k]; iss=[]; info=''
    zl=t['rwzl']; yq=int(t['rwyq'] or 0); cnt=int(t['rwcount'] or 0)
    g=giver.get(int(k),[])
    if zl=='2':
        if str(yq) not in gw: iss.append('quái %d không có'%yq)
        else:
            info='%s lv%s x%d'%(gw[str(yq)]['gname'],gw[str(yq)]['glv'],cnt)
            if yq not in spawn: iss.append('quái không xuất hiện ở map nào')
            elif int(t['rwqy'] or 0) not in spawn[yq]: iss.append('map đích %s không có quái (quái ở %s)'%(t['rwqy'],spawn[yq][:3]))
    if zl=='1':
        if str(yq) not in dj: iss.append('vật phẩm %d không có'%yq)
        else:
            info='%s x%d'%(dj[str(yq)]['djname'],cnt)
            if yq not in drops: iss.append('không quái nào rơi')
    if zl=='3':
        info='%s → %s'%(n.get(str(yq),{}).get('nname','#%d'%yq), n.get(str(cnt),{}).get('nname','#%d'%cnt))
        if str(yq) not in n: iss.append('NPC giao #%d không có'%yq)
        elif yq not in g: iss.append('NPC giao (rwyq) không có NV này trong taskid')
        if str(cnt) not in n: iss.append('NPC đích #%d không có'%cnt)
        else:
            if cnt not in npcmap: iss.append('NPC đích không ở map nào')
            if cnt not in g: iss.append('NPC đích không có NV này trong taskid (không trả được)')
    if zl=='4':
        info='%s x%d'%(dj.get(str(yq),yd.get(str(yq),{})).get('djname',yd.get(str(yq),{}).get('ydname','?')),cnt)
    if not g: iss.append('KHÔNG NPC nào giao')
    for gg in g:
        if gg not in npcmap: iss.append('NPC %s không ở map nào'%n[str(gg)]['nname'])
    lr=int(t['lastrwid'])
    if lr>0 and str(lr) not in r: iss.append('NV trước %d không có'%lr)
    if lr>0 and t['rwlx']!='3': iss.append('có NV trước nhưng loại %s'%LX[t['rwlx']])
    rq=int(t['rwqy'] or 0)
    if rq not in maps: iss.append('map đích %d không có'%rq)
    gname=','.join(n[str(x)]['nname'] for x in g)
    print('%s [%s|%s] %s {%s} ← %s | prev %s | exp %s lt %s\n      → %s'%(k,LX[t['rwlx']],ZL[zl],t['rwname'],info,gname,lr,t['rwexp'],t['rwyxb'],'; '.join(iss) or 'OK'))
