# -*- coding: utf-8 -*-
"""
==========================================================================
  BÁO CÁO CHI TIẾT 200 NHÂN VẬT - TAM TIỀN KÝ
  Phiên bản 2.0 - Tính toán THỰC TẾ hơn
  - Chi phí MT đúng cho Bí Tịch Võ Công, Công Pháp
  - Thời gian chơi thực tế hơn
  Tiếng Việt có dấu
==========================================================================
"""

import math
import random
from dataclasses import dataclass, field
from typing import List, Dict

# =========================================================================
# HẰNG SỐ GAME
# =========================================================================

TONG_CANH_GIOI = 87
GIAI_DOAN_MOI_CANH = 10
CAP_MOI_CANH = 50
CAP_TOI_DA = TONG_CANH_GIOI * CAP_MOI_CANH  # 4350
THAP_TANG_TOI_DA = 150
SO_O_TRANG_BI = 16
SO_KY_NANG_VG = 20
SO_CONG_PHAP = 10
NGUYEN_TO = ["Kim", "Moc", "Thuy", "Hoa", "Tho"]
TANG_LINH_CAN = ["Don Hop", "Song Hop", "Tam Hop", "Tu Hop", "Ngu Hanh"]
PHAM_CHAT_VG = ["Pham", "Lam", "Luc", "Linh", "Hoang Kim", "Do"]
PHAM_CHAT_THE_CHAT = ["Tap Pham", "Linh Pham", "Quang Pham", "Thanh Pham", "Tien Thien"]
PHAM_CHAT_PET = ["Binh Thuong", "Kha", "Tot", "Xuat Sac", "Hai Pham", "Nghich Thien"]
TEN_MON_PHAI = ["Thien Son Tong", "Van Dao Mon", "Co Hoa Phai", "Huyen Thien Cung", "Bat Hoang De", "Thai Thanh Cung", "Vo Cuc Dien"]

# GIÁ THỊ TRƯỜNG (thực tế game)
GIA_BI_TICH_VG = {
    "Pham": 50,           # 50 MT/cuốn
    "Lam": 150,           # 150 MT
    "Luc": 400,           # 400 MT
    "Linh": 1000,         # 1000 MT
    "Hoang Kim": 3000,    # 3000 MT
    "Do": 8000,           # 8000 MT
}

GIA_CONG_PHAP = {
    1: 100,    # Cấp 1: 100 MT
    2: 250,
    3: 500,
    4: 1000,
    5: 2000,
    6: 4000,
    7: 8000,
    8: 15000,
    9: 30000,
    10: 60000,  # Cấp 10: 60,000 MT
}

# Tỷ lệ drops Bí Tịch từ quái (rất thấp)
TY_LE_DROP_BI_TICH = {
    "Pham": 15,      # 15%
    "Lam": 8,        # 8%
    "Luc": 4,        # 4%
    "Linh": 1.5,     # 1.5%
    "Hoang Kim": 0.5, # 0.5%
    "Do": 0.1,       # 0.1%
}

# EXP cần mỗi cấp - Cong thuc can cap^2 (khong nhan realm)
# muc tieu: ~32 nam Free, ~8 nam MT, ~16 nam LT
def exp_can_moi_cap(cap, canh):
    return max(50, int(cap * cap * 0.03))

# Tốc độ AFK
def toc_do_afk(cap, multiplier=1, absorb=0):
    return cap * multiplier * min(1.3, 1 + absorb / 100)

# Tỷ lệ đột phá
def ty_le_dot_pha(cap, canh):
    return max(5, 75 - cap * 0.6 - canh * 3 + 5 + 10)

# Chi phí đột phá
def chi_phi_dot_pha(canh, giai_doan):
    return canh * 100 + giai_doan * 50  # LT


# =========================================================================
# CẤU TRÚC DỮ LIỆU
# =========================================================================

@dataclass
class ThongSo:
    cap_do: int = 1
    hp: float = 100
    atk: float = 10
    defn: float = 5
    crit: float = 0
    hut_mau: float = 0
    may_man: float = 0
    ne_tranh: float = 0
    diem_thien_phu: int = 0
    phan_thien_phu: Dict[str, int] = field(default_factory=dict)

@dataclass
class LinhCan:
    nguyen_to: str = ""
    tang: int = 0
    absorb: float = 0

@dataclass
class TheChat:
    pham_chat: int = 0
    khai_mach: int = 0

@dataclass
class HuyetMach:
    pham_chat: int = 0
    bien_hinh: int = 0

@dataclass
class TrangBi:
    vi_tri: str = ""
    pham_chat: int = 0
    pham_chat_ten: str = ""
    cap_nang: int = 0
    nguyen_to: str = ""
    atk: float = 0
    defn: float = 0
    hp: float = 0
    crit: float = 0

@dataclass
class VoCong:
    ten: str = ""
    pham_chat: int = 0
    pham_chat_ten: str = ""
    cap_do: int = 1
    la_mua: bool = False  # Mua bằng MT hay drop
    gia_mua: float = 0    # Giá MT khi mua

@dataclass
class CongPhap:
    ten: str = ""
    cap_do: int = 1
    gia_mua: float = 0    # Giá MT khi mua
    la_mua: bool = False

@dataclass
class ThuCung:
    ten: str = ""
    pham_chat: int = 0
    pham_chat_ten: str = ""
    cap_do: int = 1

@dataclass
class NhiemVu:
    ngay: int = 0
    tuan: int = 0
    thang: int = 0
    thanh_tuu: int = 0
    chinh_tuyen: int = 0

@dataclass
class KinhTe:
    lt_thu: float = 0
    lt_chi: float = 0
    mt_thu: float = 0     # Nhận từ giftcode, event
    mt_chi: float = 0     # Chi cho Bí Tịch, Công Pháp, AFK
    mt_nap: float = 0     # Nạp tiền thật

@dataclass
class ThoiGian:
    tong_ngay: float = 0
    afk_ngay: float = 0
    active_ngay: float = 0
    chien_dau_ngay: float = 0
    nhiem_vu_ngay: float = 0

@dataclass
class NhanVat:
    id: int = 0
    ten: str = ""
    gioi_tinh: str = ""
    tien_tri: str = ""
    thong_so: ThongSo = field(default_factory=ThongSo)
    linh_can: LinhCan = field(default_factory=LinhCan)
    the_chat: TheChat = field(default_factory=TheChat)
    huyet_mach: HuyetMach = field(default_factory=HuyetMach)
    trang_bis: List[TrangBi] = field(default_factory=list)
    vo_congs: List[VoCong] = field(default_factory=list)
    cong_phaps: List[CongPhap] = field(default_factory=list)
    thu_cung: ThuCung = field(default_factory=ThuCung)
    nhiem_vu: NhiemVu = field(default_factory=NhiemVu)
    kinh_te: KinhTe = field(default_factory=KinhTe)
    thoi_gian: ThoiGian = field(default_factory=ThoiGian)
    so_dot_pha_tc: int = 0
    so_dot_pha_tb: int = 0
    tang_thap: int = 0
    dau_truong_elo: int = 1000
    mon_phai: str = ""
    cap_mon_phai: int = 0
    so_ban_be: int = 0


# =========================================================================
# MÔ PHỎNG
# =========================================================================

def mo_phong_nhan_vat(id: int) -> NhanVat:
    nv = NhanVat(id=id, ten=f"GameThu {id:04d}")
    nv.gioi_tinh = random.choice(["Nam", "Nu"])
    nv.tien_tri = random.choice(["Chien Si", "Thieu Hiem", "An Si"])
    return nv


def mo_phong_len_cap(nv: NhanVat, target: int = CAP_TOI_DA):
    """Lên cấp - mô phỏng AFK lien tuc, chi phi theo session"""
    cap = nv.thong_so.cap_do
    tong_afk_gio = 0.0
    tong_lt_chi = 0.0
    tong_mt_chi = 0.0

    mt_con = nv.kinh_te.mt_thu - nv.kinh_te.mt_chi
    lt_con = nv.kinh_te.lt_thu - nv.kinh_te.lt_chi

    while cap < target:
        canh = (cap - 1) // CAP_MOI_CANH + 1
        absorb = nv.the_chat.khai_mach * 10 + nv.linh_can.absorb

        # Lua chon AFK tier
        if mt_con >= 200:
            multiplier = 3
            ph_session = 6
            mt_chi_session = 200
        elif lt_con >= 15000:
            multiplier = 2
            ph_session = 3
            mt_chi_session = 0
        else:
            multiplier = 1
            ph_session = 0.5
            mt_chi_session = 0

        speed = toc_do_afk(cap, multiplier, absorb)
        exp_1_cap = exp_can_moi_cap(cap, canh)

        # So gio can de len 1 cap
        gio_can_1 = exp_1_cap / max(speed, 1)

        # So session can (moi session = ph_session gio)
        so_session = max(1, int((gio_can_1 + ph_session - 0.001) / ph_session))

        # Thoi gian thuc te AFK
        gio_thuc_te = so_session * ph_session

        # Chi phi
        if mt_chi_session > 0:
            tong_mt_chi += mt_chi_session * so_session
            mt_con -= mt_chi_session * so_session
        if multiplier == 2:
            tong_lt_chi += 15000 * so_session
            lt_con -= 15000 * so_session

        tong_afk_gio += gio_thuc_te
        cap += 1
        nv.thoi_gian.chien_dau_ngay += 0.5 / 1440

    nv.thong_so.cap_do = cap
    nv.thoi_gian.afk_ngay = tong_afk_gio / 24
    nv.kinh_te.lt_chi += tong_lt_chi
    nv.kinh_te.mt_chi += tong_mt_chi
    return nv


def mo_phong_dot_pha(nv: NhanVat):
    """Đột phá - cần LT"""
    for canh in range(1, TONG_CANH_GIOI + 1):
        for giai in range(1, GIAI_DOAN_MOI_CANH + 1):
            cap = (canh - 1) * CAP_MOI_CANH + giai * 5
            ty_le = ty_le_dot_pha(cap, canh)
            chi_phi = chi_phi_dot_pha(canh, giai)

            if random.random() * 100 < ty_le:
                nv.so_dot_pha_tc += 1
                nv.kinh_te.lt_chi += chi_phi
            else:
                nv.so_dot_pha_tb += 1
                nv.kinh_te.lt_chi += chi_phi * 2

    nv.thoi_gian.active_ngay += nv.so_dot_pha_tc * 2 / 1440
    return nv


def mo_phong_linh_can(nv: NhanVat):
    """Linh Căn"""
    nv.linh_can.nguyen_to = random.choice(NGUYEN_TO)
    for tang in range(1, 6):
        if random.random() * 100 < max(20, 80 - tang * 10):
            nv.linh_can.tang = tang
            nv.linh_can.absorb = tang * 15
        nv.kinh_te.lt_chi += tang * 500
    return nv


def mo_phong_the_chat(nv: NhanVat):
    """Thể Chất"""
    for pq in range(5):
        if random.random() * 100 < max(15, 70 - pq * 12):
            nv.the_chat.pham_chat = pq + 1
        nv.kinh_te.lt_chi += (pq + 1) * 300
    for buoc in range(1, 7):
        nv.kinh_te.lt_chi += buoc * 1000
        nv.the_chat.khai_mach = buoc
    return nv


def mo_phong_huyet_mach(nv: NhanVat):
    """Huyết Mạch"""
    for pq in range(5):
        if random.random() * 100 < max(10, 65 - pq * 10):
            nv.huyet_mach.pham_chat = pq + 1
        nv.kinh_te.lt_chi += (pq + 1) * 400
    for buoc in range(1, 6):
        nv.kinh_te.lt_chi += buoc * 800
        nv.huyet_mach.bien_hinh = buoc
    return nv


def mo_phong_trang_bis(nv: NhanVat):
    """Trang bị 16 ô - cần LT nâng"""
    vi_tri = [f"VK{i+1}" for i in range(4)] + [f"PK{i+1}" for i in range(4)] + \
             [f"PB{i+1}" for i in range(4)] + [f"DB{i+1}" for i in range(4)]
    
    for i in range(SO_O_TRANG_BI):
        pq = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]
        cap_nang = random.randint(10, 20)
        
        tb = TrangBi(
            vi_tri=vi_tri[i],
            pham_chat=pq + 1,
            pham_chat_ten=PHAM_CHAT_VG[pq],
            cap_nang=cap_nang,
            nguyen_to=random.choice(NGUYEN_TO),
            atk=random.randint(100, 500) * (pq + 1),
            defn=random.randint(50, 300) * (pq + 1),
            hp=random.randint(500, 2000) * (pq + 1),
            crit=random.randint(5, 30) * (pq + 1),
        )
        nv.trang_bis.append(tb)
        
        # Chi phí mua + nâng
        nv.kinh_te.lt_chi += random.choice([500, 1000, 2000, 5000])
        for nang in range(1, 20):
            if random.random() * 100 < max(10, 80 - nang * 3):
                break
            nv.kinh_te.lt_chi += nang * 100

    return nv


def mo_phong_vo_congs(nv: NhanVat):
    """
    Võ Công - 20 kỹ năng
    - Phàm: Drop từ quái (15%) hoặc mua 50 MT
    - Lâm: Drop (8%) hoặc mua 150 MT
    - Lục: Drop (4%) hoặc mua 400 MT
    - Linh: Drop (1.5%) hoặc mua 1000 MT
    - Hoàng Kim: Drop (0.5%) hoặc mua 3000 MT
    - Đỏ: Drop (0.1%) hoặc mua 8000 MT
    
    Thực tế: Đa số phải MUA bằng MT vì drop rất hiếm
    """
    ten_vg = [
        "Thien Son Luc Duong Chuong", "Van Dao Quyet", "Co Hoa Kiem Phap",
        "Huyen Thien Chuong", "Bat Hoang Quyen", "Thai Thanh Kiem",
        "Vo Cuc Cong", "Luu Tinh Chuong", "Phuong Hoang Vu",
        "Thien Cau Phet Nhat", "Hoa Tinh Than Phap", "Luu Ly Than Cong",
        "Quai Quao Chuong", "Suai Suai Quyen", "Thien Son Luc Duong Kiem",
        "Van Dao Luan Vu", "Co Hoa Tien Phap", "Huyen Thien Kiem",
        "Bat Hoang Chuong", "Thai Thanh Tien Phap"
    ]

    for i in range(SO_KY_NANG_VG):
        pq = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]
        cap = random.randint(1, 100)
        
        # Kiểm tra drop từ quái
        ty_le = TY_LE_DROP_BI_TICH[PHAM_CHAT_VG[pq]]
        da_drop = random.random() * 100 < ty_le
        
        gia_mua = GIA_BI_TICH_VG[PHAM_CHAT_VG[pq]]
        
        if da_drop:
            # Drop免费, không tốn MT
            la_mua = False
            gia = 0
        else:
            # PHẢI MUA bằng MT
            la_mua = True
            gia = gia_mua
        
        vg = VoCong(
            ten=ten_vg[i] if i < len(ten_vg) else f"VG Bi Tich {i+1}",
            pham_chat=pq + 1,
            pham_chat_ten=PHAM_CHAT_VG[pq],
            cap_do=cap,
            la_mua=la_mua,
            gia_mua=gia
        )
        nv.vo_congs.append(vg)
        nv.kinh_te.mt_chi += gia
        
        # Chi phí tập luyện (MT cho Bí Tịch)
        bich_tich_chi_phi = cap * 2  # MT
        nv.kinh_te.mt_chi += bich_tich_chi_phi

    # Tính bonus ATK/DEF từ Võ Công
    for vg in nv.vo_congs:
        nv.thong_so.atk += vg.cap_do * 0.5
        nv.thong_so.defn += vg.cap_do * 0.3

    return nv


def mo_phong_cong_phap(nv: NhanVat):
    """
    Công Pháp - 10 kỹ năng
    - Cấp 1: 100 MT
    - Cấp 2: 250 MT
    - Cấp 3: 500 MT
    - Cấp 4: 1,000 MT
    - Cấp 5: 2,000 MT
    - Cấp 6: 4,000 MT
    - Cấp 7: 8,000 MT
    - Cấp 8: 15,000 MT
    - Cấp 9: 30,000 MT
    - Cấp 10: 60,000 MT
    Tổng: 120,850 MT để mua hết cấp 1-10
    """
    ten_cp = [
        "Thien Long Bat Dai Cong", "Van Phap Quy Tung", "Co Hoa Tien Phap",
        "Huyen Thien Quyet", "Bat Hoang Chuong Phap", "Thai Thanh Tien Phap",
        "Vo Cuc Cong Phap", "Luu Tinh Cong", "Phuong Hoang Tien Phap",
        "Thien Cau Hap Nhat"
    ]

    for i in range(SO_CONG_PHAP):
        cap = i + 1
        gia = GIA_CONG_PHAP[cap]
        
        cp = CongPhap(
            ten=ten_cp[i] if i < len(ten_cp) else f"Cong Phap {i+1}",
            cap_do=cap,
            gia_mua=gia,
            la_mua=True
        )
        nv.cong_phaps.append(cp)
        nv.kinh_te.mt_chi += gia

        # Bonus từ Công Pháp
        nv.thong_so.atk += cap * 2
        nv.thong_so.hp += cap * 10

    return nv


def mo_phong_thien_phu(nv: NhanVat):
    """Thiên Phúc"""
    chi_so = ["HP", "ATK", "DEF", "CRIT", "HutMau", "MayMan", "NeTranh"]
    hieu_ung = {"HP": 10, "ATK": 5, "DEF": 5, "CRIT": 1.5, "HutMau": 2, "MayMan": 1, "NeTranh": 1}
    
    tong_diem = (CAP_TOI_DA // CAP_MOI_CANH) * 5
    diem_moi = tong_diem // 7
    
    for cs in chi_so:
        nv.thong_so.phan_thien_phu[cs] = diem_moi
        if cs == "HP":
            nv.thong_so.hp += diem_moi * hieu_ung[cs]
        elif cs == "ATK":
            nv.thong_so.atk += diem_moi * hieu_ung[cs]
        elif cs == "DEF":
            nv.thong_so.defn += diem_moi * hieu_ung[cs]
        elif cs == "CRIT":
            nv.thong_so.crit += diem_moi * hieu_ung[cs]
        elif cs == "HutMau":
            nv.thong_so.hut_mau += diem_moi * hieu_ung[cs]
        elif cs == "MayMan":
            nv.thong_so.may_man += diem_moi * hieu_ung[cs]
        elif cs == "NeTranh":
            nv.thong_so.ne_tranh += diem_moi * hieu_ung[cs]
    
    nv.thong_so.diem_thien_phu = tong_diem
    return nv


def mo_phong_thap(nv: NhanVat):
    """Tháp Yêu Thư"""
    nv.tang_thap = THAP_TANG_TOI_DA
    for tang in range(1, THAP_TANG_TOI_DA + 1):
        nv.kinh_te.lt_thu += tang * 100
        nv.thong_so.hp += tang * 2
    return nv


def mo_phong_thu_cung(nv: NhanVat):
    """Thú Cưng"""
    pq = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]
    nv.thu_cung = ThuCung(
        ten=random.choice(["Hoa Tinh Vien", "Luu Ly Man", "Quai Quao", "Suai Suai Hau"]),
        pham_chat=pq + 1,
        pham_chat_ten=PHAM_CHAT_PET[pq],
        cap_do=nv.thong_so.cap_do
    )
    return nv


def mo_phong_dau_truong(nv: NhanVat):
    """Đấu Trường"""
    for ngay in range(30):
        for luot in range(5):
            if random.random() > 0.4:
                nv.dau_truong_elo += random.randint(10, 30)
            else:
                nv.dau_truong_elo -= random.randint(5, 20)
    nv.kinh_te.lt_thu += 5000
    nv.kinh_te.mt_thu += 100
    return nv


def mo_phong_nhiem_vu(nv: NhanVat):
    """Nhiệm Vụ"""
    for ngay in range(30):
        nv.kinh_te.lt_thu += 500
        nv.nhiem_vu.ngay += 5
    for tuan in range(12):
        nv.kinh_te.lt_thu += 2000
        nv.nhiem_vu.tuan += 3
    for thang in range(3):
        nv.kinh_te.lt_thu += 10000
        nv.nhiem_vu.thang += 2
    for tt in range(50):
        nv.kinh_te.lt_thu += 1000
        nv.nhiem_vu.thanh_tuu += 1
    for chuong in range(TONG_CANH_GIOI):
        nv.kinh_te.lt_thu += 3000
        nv.nhiem_vu.chinh_tuyen += 1
    return nv


def mo_phong_khac(nv: NhanVat):
    """Các tính năng khác"""
    # Cửa hàng
    nv.kinh_te.lt_chi += 10000
    nv.kinh_te.lt_thu += 8000
    
    # Hầm Mỏ
    for ngay in range(30):
        nv.kinh_te.lt_thu += random.randint(100, 500)
        nv.kinh_te.mt_thu += random.randint(1, 5)
    
    # Giftcode
    for i in range(10):
        nv.kinh_te.lt_thu += 5000
        nv.kinh_te.mt_thu += 50
    
    # Mon Phai
    nv.kinh_te.lt_chi += 21000
    nv.cap_mon_phai = 7
    nv.mon_phai = random.choice(TEN_MON_PHAI)
    
    # Mua trang bi từ shop
    nv.kinh_te.lt_chi += 50000
    
    # Che tao
    nv.kinh_te.lt_chi += 6000
    
    # Hao huu
    nv.so_ban_be = 50
    
    # Thi chat, huyet mach boss
    for i in range(5):
        nv.kinh_te.lt_thu += 2000
    
    return nv


def tinh_toan_cuoi(nv: NhanVat):
    """Tính toán cuối cùng"""
    # Bonus từ cấp độ
    nv.thong_so.atk += nv.thong_so.cap_do * 2
    nv.thong_so.defn += nv.thong_so.cap_do * 1
    nv.thong_so.hp += nv.thong_so.cap_do * 50

    # Chi Lực
    chien_luc = (nv.thong_so.atk * 10 + nv.thong_so.defn * 8 + nv.thong_so.hp +
                 nv.thong_so.crit * 30 + nv.thong_so.hut_mau * 25 +
                 nv.thong_so.diem_thien_phu * 50 + nv.thong_so.may_man * 10 +
                 nv.thong_so.ne_tranh * 10)
    
    # Tổng thời gian
    nv.thoi_gian.tong_ngay = (nv.thoi_gian.afk_ngay + nv.thoi_gian.active_ngay + 
                              nv.thoi_gian.chien_dau_ngay + nv.thoi_gian.nhiem_vu_ngay)
    
    return nv, chien_luc


def mo_phong_1_nv(id: int):
    """Mô phỏng 1 nhân vật"""
    nv = mo_phong_nhan_vat(id)
    mo_phong_len_cap(nv)
    mo_phong_dot_pha(nv)
    mo_phong_linh_can(nv)
    mo_phong_the_chat(nv)
    mo_phong_huyet_mach(nv)
    mo_phong_trang_bis(nv)
    mo_phong_vo_congs(nv)
    mo_phong_cong_phap(nv)
    mo_phong_thien_phu(nv)
    mo_phong_thap(nv)
    mo_phong_thu_cung(nv)
    mo_phong_dau_truong(nv)
    mo_phong_nhiem_vu(nv)
    mo_phong_khac(nv)
    return tinh_toan_cuoi(nv)


# =========================================================================
# XUẤT BÁO CÁO
# =========================================================================

def xuat_bao_cao(danh_sach):
    r = []
    r.append("=" * 100)
    r.append("  BAO CAO CHI TIET 200 NHAN VAT - TAM TIEN KY")
    r.append("  Phien ban 2.0 - Tinh toan THUC TE hon")
    r.append("  Ngay: 22/07/2026")
    r.append("=" * 100)
    r.append("")
    r.append("  GHI QUAN TRONG:")
    r.append("  - Ma Thach (MT) chi co tu NAP TIEN (cash shop) va giftcode")
    r.append("  - Bi Tich Vo Cong: Drop rat hiem, phai MUA bang MT")
    r.append("  - Cong Phap: BAT BUOC mua bang MT (cap 1-10)")
    r.append("  - AFK tier MT: 200 MT/6 gio")
    r.append("")

    # Xuất từng nhân vật
    for idx, (nv, cl) in enumerate(danh_sach, 1):
        r.append("")
        r.append("=" * 100)
        r.append(f"  NHAN VAT {idx}: {nv.ten}")
        r.append("=" * 100)
        
        r.append("")
        r.append("  1. THONG TIN CO BAN")
        r.append("  " + "-" * 60)
        r.append(f"     Ten: {nv.ten} | Gioi tinh: {nv.gioi_tinh} | Tien truyen: {nv.tien_tri}")
        r.append(f"     Cap do: {nv.thong_so.cap_do}/{CAP_TOI_DA}")
        r.append(f"     Mon Phai: {nv.mon_phai} (Cap {nv.cap_mon_phai})")
        
        r.append("")
        r.append("  2. CHI SO CHIEN DAU")
        r.append("  " + "-" * 60)
        r.append(f"     ATK: {nv.thong_so.atk:,.0f}")
        r.append(f"     DEF: {nv.thong_so.defn:,.0f}")
        r.append(f"     HP: {nv.thong_so.hp:,.0f}")
        r.append(f"     CRIT: {nv.thong_so.crit:,.1f}%")
        r.append(f"     Hut Mau: {nv.thong_so.hut_mau:,.1f}%")
        r.append(f"     May Man: {nv.thong_so.may_man:,.1f}")
        r.append(f"     Ne Tranh: {nv.thong_so.ne_tranh:,.1f}")
        r.append(f"     ★ CHIEN LUC: {cl:,.0f}")
        
        r.append("")
        r.append("  3. THIEN PHUC")
        r.append("  " + "-" * 60)
        r.append(f"     Tong diem: {nv.thong_so.diem_thien_phu}")
        for cs, diem in nv.thong_so.phan_thien_phu.items():
            r.append(f"     {cs}: {diem} diem")
        
        r.append("")
        r.append("  4. LINH CAN (NGUYEN TO)")
        r.append("  " + "-" * 60)
        tang_text = TANG_LINH_CAN[nv.linh_can.tang - 1] if nv.linh_can.tang > 0 else "Chua co"
        r.append(f"     Nguyen to: {nv.linh_can.nguyen_to}")
        r.append(f"     Tang: {nv.linh_can.tang}/5 ({tang_text})")
        r.append(f"     Absorb Speed: +{nv.linh_can.absorb}%")
        
        r.append("")
        r.append("  5. THE CHAT")
        r.append("  " + "-" * 60)
        pq_text = PHAM_CHAT_THE_CHAT[nv.the_chat.pham_chat - 1] if nv.the_chat.pham_chat > 0 else "Chua co"
        r.append(f"     Pham Chat: {nv.the_chat.pham_chat}/5 ({pq_text})")
        r.append(f"     Khai Mach: {nv.the_chat.khai_mach}/6")
        
        r.append("")
        r.append("  6. HUYET MACH")
        r.append("  " + "-" * 60)
        pq_text = PHAM_CHAT_THE_CHAT[nv.huyet_mach.pham_chat - 1] if nv.huyet_mach.pham_chat > 0 else "Chua co"
        r.append(f"     Pham Chat: {nv.huyet_mach.pham_chat}/5 ({pq_text})")
        r.append(f"     Bien Hinh: {nv.huyet_mach.bien_hinh} lan")
        
        r.append("")
        r.append("  7. TRANG BI (16 O)")
        r.append("  " + "-" * 60)
        for tb in nv.trang_bis:
            r.append(f"     [{tb.vi_tri}] {tb.pham_chat_ten} +{tb.cap_nang} | {tb.nguyen_to}")
            r.append(f"        ATK:{tb.atk:,.0f} DEF:{tb.defn:,.0f} HP:{tb.hp:,.0f} CRIT:{tb.crit:,.0f}")
        
        r.append("")
        r.append("  8. VO CONG (20 KY NANG)")
        r.append("  " + "-" * 60)
        for vg in nv.vo_congs:
            mua_text = f"MUA {vg.gia_mua:,.0f} MT" if vg.la_mua else "DROP"
            r.append(f"     [{vg.pham_chat_ten}] {vg.ten} - Cap {vg.cap_do} | {mua_text}")
        
        # Thong ke MT cho Vo Cong
        mt_vg_drop = sum(vg.gia_mua for vg in nv.vo_congs if not vg.la_mua)
        mt_vg_mua = sum(vg.gia_mua for vg in nv.vo_congs if vg.la_mua)
        so_vg_drop = sum(1 for vg in nv.vo_congs if not vg.la_mua)
        so_vg_mua = sum(1 for vg in nv.vo_congs if vg.la_mua)
        r.append(f"     → Vo Cong DROP: {so_vg_drop}/20 | MUA: {so_vg_mua}/20")
        r.append(f"     → Tong MT chi cho Vo Cong: {mt_vg_mua:,.0f} MT")
        
        r.append("")
        r.append("  9. CONG PHAP (10 KY NANG)")
        r.append("  " + "-" * 60)
        tong_mt_cp = 0
        for cp in nv.cong_phaps:
            r.append(f"     {cp.ten} - Cap {cp.cap_do} | MUA: {cp.gia_mua:,.0f} MT")
            tong_mt_cp += cp.gia_mua
        r.append(f"     → Tong MT chi cho Cong Phap: {tong_mt_cp:,.0f} MT")
        
        r.append("")
        r.append("  10. THU CUNG")
        r.append("  " + "-" * 60)
        r.append(f"     Ten: {nv.thu_cung.ten}")
        r.append(f"     Pham Chat: {nv.thu_cung.pham_chat}/6 ({nv.thu_cung.pham_chat_ten})")
        r.append(f"     Cap Do: {nv.thu_cung.cap_do}")
        
        r.append("")
        r.append("  11. NHIEM VU")
        r.append("  " + "-" * 60)
        r.append(f"     Ngay: {nv.nhiem_vu.ngay} | Tuan: {nv.nhiem_vu.tuan} | Thang: {nv.nhiem_vu.thang}")
        r.append(f"     Thanh Tuu: {nv.nhiem_vu.thanh_tuu} | Chinh Tuyen: {nv.nhiem_vu.chinh_tuyen}")
        tong = nv.nhiem_vu.ngay + nv.nhiem_vu.tuan + nv.nhiem_vu.thang + nv.nhiem_vu.thanh_tuu + nv.nhiem_vu.chinh_tuyen
        r.append(f"     ★ TONG: {tong} nhiem vu")
        
        r.append("")
        r.append("  12. DOT PHA")
        r.append("  " + "-" * 60)
        ty_le = nv.so_dot_pha_tc / max(nv.so_dot_pha_tc + nv.so_dot_pha_tb, 1) * 100
        r.append(f"     Thanh cong: {nv.so_dot_pha_tc} | That bai: {nv.so_dot_pha_tb}")
        r.append(f"     Ty le: {ty_le:.1f}%")
        
        r.append("")
        r.append("  13. DAU TRUONG & THAP")
        r.append("  " + "-" * 60)
        r.append(f"     ELO: {nv.dau_truong_elo}")
        r.append(f"     Thap: {nv.tang_thap}/{THAP_TANG_TOI_DA}")
        
        r.append("")
        r.append("  14. KINH TE - CHI PHI CHI TIET")
        r.append("  " + "-" * 60)
        r.append(f"     Linh Thach (LT):")
        r.append(f"       Thu: {nv.kinh_te.lt_thu:,.0f} LT")
        r.append(f"       Chi: {nv.kinh_te.lt_chi:,.0f} LT")
        du_lt = nv.kinh_te.lt_thu - nv.kinh_te.lt_chi
        r.append(f"       Du: {du_lt:,.0f} LT {'(THIEU)' if du_lt < 0 else '(DU)'}")
        r.append(f"     Ma Thach (MT):")
        r.append(f"       Thu tu giftcode/event: {nv.kinh_te.mt_thu:,.0f} MT")
        r.append(f"       Chi cho Vo Cong: {sum(vg.gia_mua for vg in nv.vo_congs):,.0f} MT")
        r.append(f"       Chi cho Cong Phap: {sum(cp.gia_mua for cp in nv.cong_phaps):,.0f} MT")
        r.append(f"       Chi cho AFK: {nv.kinh_te.mt_chi - sum(vg.gia_mua for vg in nv.vo_congs) - sum(cp.gia_mua for cp in nv.cong_phaps):,.0f} MT")
        r.append(f"       Tong MT chi: {nv.kinh_te.mt_chi:,.0f} MT")
        r.append(f"       Can NAP THEM: {max(0, nv.kinh_te.mt_chi - nv.kinh_te.mt_thu):,.0f} MT")
        
        r.append("")
        r.append("  15. THOI GIAN")
        r.append("  " + "-" * 60)
        r.append(f"     AFK: {nv.thoi_gian.afk_ngay:,.1f} ngay")
        r.append(f"     Active: {nv.thoi_gian.active_ngay:,.1f} ngay")
        r.append(f"     Chien dau: {nv.thoi_gian.chien_dau_ngay:,.1f} ngay")
        r.append(f"     ★ TONG: {nv.thoi_gian.tong_ngay:,.1f} ngay")
        
        r.append("")
        r.append("  " + "=" * 60)
        r.append(f"  KET THUC NHAN VAT {idx}")
        r.append("  " + "=" * 60)

    # =========================================================================
    # TỔNG KẾT
    # =========================================================================
    
    r.append("")
    r.append("=" * 100)
    r.append("  TONG KET 200 NHAN VAT")
    r.append("=" * 100)
    
    tb_tong_ngay = sum(nv.thoi_gian.tong_ngay for nv, _ in danh_sach) / len(danh_sach)
    tb_mt_chi = sum(nv.kinh_te.mt_chi for nv, _ in danh_sach) / len(danh_sach)
    tb_lt_chi = sum(nv.kinh_te.lt_chi for nv, _ in danh_sach) / len(danh_sach)
    
    # Phân tích MT
    tong_mt_vg = sum(sum(vg.gia_mua for vg in nv.vo_congs) for nv, _ in danh_sach)
    tong_mt_cp = sum(sum(cp.gia_mua for cp in nv.cong_phaps) for nv, _ in danh_sach)
    tong_mt_afk = sum(nv.kinh_te.mt_chi - sum(vg.gia_mua for vg in nv.vo_congs) - sum(cp.gia_mua for cp in nv.cong_phaps) for nv, _ in danh_sach)
    
    so_vg_drop = sum(sum(1 for vg in nv.vo_congs if not vg.la_mua) for nv, _ in danh_sach)
    so_vg_mua = sum(sum(1 for vg in nv.vo_congs if vg.la_mua) for nv, _ in danh_sach)
    
    r.append("")
    r.append("  A. THOI GIAN (TRUNG BINH/NHAN VAT)")
    r.append("  " + "-" * 60)
    r.append(f"     Tong: {tb_tong_ngay:,.1f} ngay = {tb_tong_ngay * 24:,.1f} gio")
    r.append(f"     Neu choi 8h/ngay: {tb_tong_ngay * 24 / 8:,.1f} ngay = {tb_tong_ngay * 24 / 8 / 30:,.1f} thang")
    r.append(f"     Neu choi 4h/ngay: {tb_tong_ngay * 24 / 4:,.1f} ngay = {tb_tong_ngay * 24 / 4 / 30:,.1f} thang")
    
    r.append("")
    r.append("  B. CHI PHI MA THACH (TRUNG BINH/NHAN VAT)")
    r.append("  " + "-" * 60)
    r.append(f"     Tong MT can chi: {tb_mt_chi:,.0f} MT")
    r.append(f"       + Vo Cong: {tong_mt_vg / 200:,.0f} MT ({tong_mt_vg / 200 / tb_mt_chi * 100:.1f}%)")
    r.append(f"       + Cong Phap: {tong_mt_cp / 200:,.0f} MT ({tong_mt_cp / 200 / tb_mt_chi * 100:.1f}%)")
    r.append(f"       + AFK: {tong_mt_afk / 200:,.0f} MT ({tong_mt_afk / 200 / tb_mt_chi * 100:.1f}%)")
    r.append(f"     MT nhan duoc tu giftcode: ~690 MT")
    r.append(f"     ★ CAN NAP THEM: {tb_mt_chi - 690:,.0f} MT")
    r.append(f"     ★ GIA TRI: {(tb_mt_chi - 690) * 100:,.0f} VND (~{(tb_mt_chi - 690) * 100 / 1000000:,.1f} trieu VND)")
    
    r.append("")
    r.append("  C. PHAN BO VO CONG")
    r.append("  " + "-" * 60)
    r.append(f"     Vo Cong DROP tu quai: {so_vg_drop}/{200*20} ({so_vg_drop/(200*20)*100:.1f}%)")
    r.append(f"     Vo Cong MUA bang MT: {so_vg_mua}/{200*20} ({so_vg_mua/(200*20)*100:.1f}%)")
    r.append(f"     → {100 - so_vg_drop/(200*20)*100:.1f}% Vo Cong phai MUA!")
    
    r.append("")
    r.append("  D. CHI PHI CONG PHAP")
    r.append("  " + "-" * 60)
    r.append(f"     Tong MT mua Cong Phap: {tong_mt_cp:,.0f} MT (200 NV)")
    r.append(f"     Trung binh/NV: {tong_mt_cp/200:,.0f} MT")
    r.append(f"     Chi phi 10 cap Cong Phap:")
    for cap in range(1, 11):
        r.append(f"       Cap {cap}: {GIA_CONG_PHAP[cap]:,} MT")
    r.append(f"       TONG: {sum(GIA_CONG_PHAP.values()):,} MT")
    
    r.append("")
    r.append("  E. SO SANH VOI THUC TE")
    r.append("  " + "-" * 60)
    r.append(f"     Neu khong nap tien:")
    r.append(f"       - Chi co ~690 MT tu giftcode")
    r.append(f"       - Chi mua duoc Vo Cong Pham/Lam (50-150 MT)")
    r.append(f"       - KHONG mua duoc Cong Phap cap cao")
    r.append(f"       - AFK chi dung tier Free (x1)")
    r.append(f"     Neu nap 500K VND (= 5,000 MT):")
    r.append(f"       - Mua duoc Vo Cong Luc/Linh (400-1000 MT)")
    r.append(f"       - Mua duoc Cong Phap cap 1-5")
    r.append(f"     Neu nap 2M VND (= 20,000 MT):")
    r.append(f"       - Mua duoc Vo Cong Hoang Kim (3000 MT)")
    r.append(f"       - Mua duoc Cong Phap cap 1-7")
    r.append(f"     Neu nap 5M VND (= 50,000 MT):")
    r.append(f"       - Mua duoc tat ca Vo Cong")
    r.append(f"       - Mua duoc Cong Phap cap 1-9")
    r.append(f"     ★ De MAX: can nap ~{tb_mt_chi * 100 / 1000000:,.1f} TRIEU VND/NV")
    
    r.append("")
    r.append("=" * 100)
    r.append("  KET LUAN")
    r.append("=" * 100)
    r.append("")
    r.append(f"  1. THOI GIAN MAX:")
    r.append(f"     - Choi 24/7: {tb_tong_ngay:,.1f} ngay")
    r.append(f"     - Choi 8h/ngay: {tb_tong_ngay * 3:,.1f} ngay = {tb_tong_ngay * 3 / 30:,.1f} thang")
    r.append(f"     - Choi 4h/ngay: {tb_tong_ngay * 6:,.1f} ngay = {tb_tong_ngay * 6 / 30:,.1f} thang")
    r.append("")
    r.append(f"  2. CHI PHI (per nhan vat):")
    r.append(f"     - MT can nap: {tb_mt_chi - 690:,.0f} MT")
    r.append(f"     - Gia tri VND: ~{(tb_mt_chi - 690) * 100 / 1000000:,.1f} TRIEU VND")
    r.append(f"     - 200 NV: ~{(tb_mt_chi - 690) * 100 * 200 / 1000000000:,.2f} TY VND")
    r.append("")
    r.append(f"  3. PHAN TICH KINH DOANH:")
    r.append(f"     - Neu 10,000 nguoi choi, moi nguoi nap 1M VND")
    r.append(f"     → Doanh thu: 10 TY VND/thang")
    r.append(f"     - Game can NHIEU tinh nang kho hon de keo dai")
    r.append(f"     - Hien tai MAX qua nhanh -> Can dieu chinh:")
    r.append(f"       + Tang EXP can moi cap")
    r.append(f"       + Giam ty le dot pha")
    r.append(f"       + Tang gia Vo Cong/Cong Phap")
    r.append(f"       + Them tinh nang moi")
    r.append("")
    r.append("=" * 100)
    r.append("  HOAN THANH!")
    r.append("=" * 100)
    
    return "\n".join(r)


# =========================================================================
# CHAY
# =========================================================================

import sys
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

if __name__ == "__main__":
    print("Dang mo phong 200 nhan vat (Phien ban 2.0)...")
    
    danh_sach = []
    for i in range(1, 201):
        nv, cl = mo_phong_1_nv(i)
        danh_sach.append((nv, cl))
        if i % 50 == 0:
            print(f"  Da hoan thanh {i}/200...")
    
    print("Dang xuat bao cao...")
    bao_cao = xuat_bao_cao(danh_sach)
    
    import os
    file_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "BAOCAO-CHITIET-200NV-V2.txt")
    with open(file_path, "w", encoding="utf-8") as f:
        f.write(bao_cao)
    
    print(f"Da luu: {file_path}")
    print("Hoan thanh!")
