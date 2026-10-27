# -*- coding: utf-8 -*-
"""
==========================================================================
  BÁO CÁO CHI TIẾT 200 NHÂN VẬT - TAM TIỀN KÝ
  Trải nghiệm MAX TẤT CẢ TÍNH NĂNG từ lúc tạo nhân vật
  Tiếng Việt có dấu - Chi tiết từng nhân vật
==========================================================================
"""

import math
import random
from dataclasses import dataclass, field
from typing import List, Dict

# =========================================================================
# HẰNG SỐ - DỮ LIỆU GAME
# =========================================================================

TONG_CANH_GIOI = 87
GIAI_DOAN_MOI_CANH = 10
CAP_MOI_CANH = 50
CAP_TOI_DA = TONG_CANH_GIOI * CAP_MOI_CANH  # = 4350
THAP_TANG_TOI_DA = 150
SO_O_TRANG_BI = 16
SO_KY_NANG_VG = 20
SO_MON_PHAI = 7
NGUYEN_TO = ["Kim", "Mộc", "Thủy", "Hỏa", "Thổ"]
TANG_LINH_CAN = ["Đơn Hợp", "Song Hợp", "Tam Hợp", "Tứ Hợp", "Ngũ Hành"]
PHAM_CHAT_VG = ["Phàm", "Lâm", "Lục", "Linh", "Hoàng Kim", "Đỏ"]
PHAM_CHAT_THE_CHAT = ["Tạp Phẩm", "Linh Phẩm", "Quang Phẩm", "Thanh Phâm", "Tiên Thiên"]
PHAM_CHAT_HUYET_MACH = ["Tạp Phẩm", "Linh Phẩm", "Quang Phẩm", "Thanh Phâm", "Tiên Thiên"]
PHAM_CHAT_PET = ["Bình thường", "Khá", "Tốt", "Xuất sắc", "Hài Phẩm", "Nghịch Thiên"]
TEN_VU_KHI = ["Kiếm", "Đao", "Thương", "Cung", "Phủ", "Chùy", "Thiết Chùy", "Song Hoàn"]
TEN_PHU_KIEN = ["Nhẫn", "Dây Chuyền", "Lưu Ly", "Nón", "Áo", "Quần", "Giày", "Bội"]
TEN_PET = ["Hỏa Tinh Viên", "Lưu Ly Man", "Quái Quao", "Suai Suai Hầu", "Thiên Cẩu", "Phượng Hoàng"]
TEN_MON_PHAI = ["Thiên Sơn Tông", "Vạn Đao Môn", "Cổ Hoa Phái", "Huyền Thiên Cung", "Bát Hoang Đế", "Thái Thanh Cung", "Vô Cực Điện"]

# AFK
AFK_TIERS = {
    "free": {"multiplier": 1, "max_hours": 0.5, "cost": 0},
    "lt": {"multiplier": 2, "max_hours": 3, "cost": 5000},
    "mt": {"multiplier": 3, "max_hours": 6, "cost": 200},
}
MT_TO_LT = 1000


# =========================================================================
# CẤU TRÚC DỮ LIỆU NHÂN VẬT
# =========================================================================

@dataclass
class ThongSoNhanVat:
    cap_do: int = 1
    canh_gioi: int = 1
    giai_doan: int = 1
    hp: float = 100
    atk: float = 10
    defn: float = 5
    crit: float = 0
    hut_mau: float = 0
    may_man: float = 0
    ne_tranh: float = 0
    diem_thien_phu: int = 0
    phan_thien_phu: Dict[str, int] = field(default_factory=lambda: {
        "HP": 0, "ATK": 0, "DEF": 0, "CRIT": 0, "Hút Mau": 0, "May Mắn": 0, "Né Tránh": 0
    })


@dataclass
class LinhCan:
    nguyen_to: str = "Không"
    tang: int = 0
    pham_chat: int = 0
    absorb: float = 0
    boss_da_danh: int = 0
    nhiem_vu_tuan: int = 0


@dataclass
class TheChat:
    pham_chat: int = 0
    khai_mach: int = 0
    passives: Dict[str, float] = field(default_factory=dict)


@dataclass
class HuyetMach:
    pham_chat: int = 0
    bien_hinh: int = 0
    cooldown: int = 0
    passives: Dict[str, float] = field(default_factory=dict)


@dataclass
class TrangBi:
    vi_tri: str = ""
    loai: str = ""
    pham_chat: int = 0
    pham_chat_ten: str = ""
    cap_nang: int = 0
    nguyen_to: str = ""
    stats: Dict[str, float] = field(default_factory=dict)


@dataclass
class VoCong:
    ten: str = ""
    pham_chat: int = 0
    pham_chat_ten: str = ""
    cap_do: int = 1
    bi_tich: int = 0


@dataclass
class CongPhap:
    ten: str = ""
    cap_do: int = 1
    absorb_speed: float = 0


@dataclass
class ThuCung:
    ten: str = ""
    pham_chat: int = 0
    pham_chat_ten: str = ""
    cap_do: int = 1
    exp: float = 0


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
    mt_thu: float = 0
    mt_chi: float = 0
    tf_thu: int = 0
    tf_chi: int = 0


@dataclass
class ThoiGian:
    tong_phut: float = 0
    afk_phut: float = 0
    active_phut: float = 0
    chien_dau_phut: float = 0
    nhiem_vu_phut: float = 0


@dataclass
class NhanVat:
    id: int = 0
    ten: str = ""
    gioi_tinh: str = ""
    tien_tri: str = ""
    thong_so: ThongSoNhanVat = field(default_factory=ThongSoNhanVat)
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
    lich_su: List[str] = field(default_factory=list)
    than_trang: int = 0
    dau_truong_elo: int = 1000
    mon_phai: str = ""
    cap_mon_phai: int = 0
    so_ban_be: int = 0
    so_tin_nhan: int = 0
    so_dot_pha_thanh_cong: int = 0
    so_dot_pha_that_bai: int = 0
    tang_thap: int = 0


# =========================================================================
# HÀM MÔ PHỎNG
# =========================================================================

def tao_nhan_vat(id: int) -> NhanVat:
    """Tạo nhân vật mới"""
    gioi_tinh = random.choice(["Nam", "Nữ"])
    tien_tri = random.choice(["Chiến Sĩ", "Thiếu Hiểm", "Ẩn Sĩ"])
    ten = f"Game thủ {id:04d}"
    
    nv = NhanVat(id=id, ten=ten, gioi_tinh=gioi_tinh, tien_tri=tien_tri)
    nv.lich_su.append(f"Tạo nhân vật: {ten} - Giới tính: {gioi_tinh} - Tiền truyện: {tien_tri}")
    nv.thoi_gian.active_phut += 2
    return nv


def mo_phong_len_cap(nv: NhanVat, cap_muc_tieu: int = CAP_TOI_DA):
    """Mô phỏng lên cấp từ 1 đến max"""
    cap_hien_tai = nv.thong_so.cap_do
    tong_afk_gio = 0
    tong_lt_chi = 0

    while cap_hien_tai < cap_muc_tieu:
        canh = (cap_hien_tai - 1) // CAP_MOI_CANH + 1
        
        if nv.kinh_te.lt_thu - nv.kinh_te.lt_chi > AFK_TIERS["lt"]["cost"]:
            multiplier = AFK_TIERS["lt"]["multiplier"]
            chi_phi_gio = AFK_TIERS["lt"]["cost"] / 3
            gio_toi_da = 3
        else:
            multiplier = AFK_TIERS["free"]["multiplier"]
            chi_phi_gio = 0
            gio_toi_da = 0.5

        toc_do = cap_hien_tai * multiplier * min(1.3, 1 + nv.the_chat.khai_mach * 10 / 100) * min(1.3, 1 + nv.linh_can.absorb / 100)
        
        cap_can_lay = min(50, cap_muc_tieu - cap_hien_tai)
        exp_can = sum((cap_hien_tai + i) * 100 * canh for i in range(cap_can_lay))
        gio_can = exp_can / max(toc_do, 1)
        gio_can = min(gio_can, gio_toi_da)

        lt_chi = gio_can * chi_phi_gio
        nv.kinh_te.lt_chi += lt_chi
        tong_lt_chi += lt_chi

        exp_nhan = toc_do * gio_can
        cap_tang = int(exp_can / max(cap_hien_tai * 100 * canh, 1))
        cap_tang = max(1, min(cap_tang, cap_can_lay))

        cap_hien_tai += cap_tang
        tong_afk_gio += gio_can

        nv.thoi_gian.chien_dau_phut += cap_tang * 0.5

        if cap_hien_tai % CAP_MOI_CANH == 0 and cap_hien_tai > 0:
            so_canh = cap_hien_tai // CAP_MOI_CANH
            nv.lich_su.append(f"Đột phá lớn - Cảnh giới {so_canh}")
            nv.thoi_gian.active_phut += 5

    nv.thong_so.cap_do = cap_hien_tai
    nv.thoi_gian.afk_phut = tong_afk_gio * 60
    nv.kinh_te.lt_chi = tong_lt_chi
    return nv


def mo_phong_dot_pha(nv: NhanVat):
    """Mô phỏng đột phá"""
    for canh in range(1, TONG_CANH_GIOI + 1):
        for giai_doan in range(1, GIAI_DOAN_MOI_CANH + 1):
            cap = (canh - 1) * CAP_MOI_CANH + giai_doan * 5
            ty_le = max(5, 75 - cap * 0.6 - canh * 3 + 5 + 10)
            chi_phi = canh * 100 + giai_doan * 50

            if random.random() * 100 < ty_le:
                nv.so_dot_pha_thanh_cong += 1
                nv.kinh_te.lt_chi += chi_phi
            else:
                nv.so_dot_pha_that_bai += 1
                nv.kinh_te.lt_chi += chi_phi * 2
                nv.thoi_gian.active_phut += 2

    nv.thoi_gian.active_phut += nv.so_dot_pha_thanh_cong * 2
    nv.lich_su.append(f"Đột phá: {nv.so_dot_pha_thanh_cong} thành công, {nv.so_dot_pha_that_bai} thất bại")
    return nv


def mo_phong_linh_can(nv: NhanVat):
    """Mô phỏng Linh Căn"""
    nguyen_to = random.choice(NGUYEN_TO)
    nv.linh_can.nguyen_to = nguyen_to

    for tang in range(1, 6):
        ty_le = max(20, 80 - tang * 10)
        chi_phi = tang * 500

        if random.random() * 100 < ty_le:
            nv.linh_can.tang = tang
            nv.linh_can.absorb = tang * 15

        nv.kinh_te.lt_chi += chi_phi
        nv.thoi_gian.active_phut += 3

    for i in range(5):
        nv.thoi_gian.chien_dau_phut += 15
        nv.kinh_te.lt_thu += 2000
        nv.linh_can.boss_da_danh += 1

    for tuan in range(12):
        nv.thoi_gian.nhiem_vu_phut += 30
        nv.kinh_te.lt_thu += 500
        nv.linh_can.nhiem_vu_tuan += 1

    nv.lich_su.append(f"Linh Căn: {nguyen_to} - Tầng {nv.linh_can.tang}/{TANG_LINH_CAN[nv.linh_can.tang-1] if nv.linh_can.tang > 0 else 'Chưa có'}")
    return nv


def mo_phong_the_chat(nv: NhanVat):
    """Mô phỏng Thể Chất"""
    for pq in range(5):
        ty_le = max(15, 70 - pq * 12)
        chi_phi = (pq + 1) * 300

        if random.random() * 100 < ty_le:
            nv.the_chat.pham_chat = pq + 1

        nv.kinh_te.lt_chi += chi_phi
        nv.thoi_gian.active_phut += 5

    for buoc in range(1, 7):
        chi_phi = buoc * 1000
        nv.kinh_te.lt_chi += chi_phi
        nv.the_chat.khai_mach = buoc
        nv.thoi_gian.active_phut += 10

    ten_pq = PHAM_CHAT_THE_CHAT[nv.the_chat.pham_chat - 1] if nv.the_chat.pham_chat > 0 else "Chưa có"
    nv.lich_su.append(f"Thể Chất: {ten_pq} - Khai Mạch: {nv.the_chat.khai_mach}/6")
    return nv


def mo_phong_huyet_mach(nv: NhanVat):
    """Mô phỏng Huyết Mạch"""
    for pq in range(5):
        ty_le = max(10, 65 - pq * 10)
        chi_phi = (pq + 1) * 400

        if random.random() * 100 < ty_le:
            nv.huyet_mach.pham_chat = pq + 1

        nv.kinh_te.lt_chi += chi_phi
        nv.thoi_gian.active_phut += 5

    for buoc in range(1, 6):
        chi_phi = buoc * 800
        nv.kinh_te.lt_chi += chi_phi
        nv.huyet_mach.bien_hinh = buoc
        nv.thoi_gian.active_phut += 8

    for i in range(10):
        nv.huyet_mach.bien_hinh += 1
        nv.thoi_gian.chien_dau_phut += 10
        nv.thoi_gian.active_phut += 2

    ten_pq = PHAM_CHAT_HUYET_MACH[nv.huyet_mach.pham_chat - 1] if nv.huyet_mach.pham_chat > 0 else "Chưa có"
    nv.lich_su.append(f"Huyết Mạch: {ten_pq} - Biến Hình: {nv.huyet_mach.bien_hinh} lần")
    return nv


def mo_phong_trang_bis(nv: NhanVat):
    """Mô phỏng Trang Bị 16 ô"""
    vi_tri_names = []
    for i in range(1, 5):
        vi_tri_names.append(f"Vũ khí {i}")
    for i in range(1, 5):
        vi_tri_names.append(f"Phụ kiện {i}")
    for i in range(1, 5):
        vi_tri_names.append(f"Phòng hộ {i}")
    for i in range(1, 5):
        vi_tri_names.append(f"Đặc biệt {i}")

    for i in range(SO_O_TRANG_BI):
        pq = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]
        cap_nang = random.randint(10, 20)
        nguyen_to = random.choice(NGUYEN_TO)
        
        tb = TrangBi(
            vi_tri=vi_tri_names[i],
            loai="Vũ khí" if i < 4 else "Phụ kiện" if i < 8 else "Phòng hộ" if i < 12 else "Đặc biệt",
            pham_chat=pq + 1,
            pham_chat_ten=PHAM_CHAT_VG[pq],
            cap_nang=cap_nang,
            nguyen_to=nguyen_to,
            stats={
                "ATK": random.randint(100, 500) * (pq + 1),
                "DEF": random.randint(50, 300) * (pq + 1),
                "HP": random.randint(500, 2000) * (pq + 1),
                "CRIT": random.randint(5, 30) * (pq + 1),
            }
        )
        nv.trang_bis.append(tb)
        
        chi_phi = random.choice([500, 1000, 2000, 5000])
        nv.kinh_te.lt_chi += chi_phi
        
        for nang in range(1, 20):
            ty_le = max(10, 80 - nang * 3)
            if random.random() * 100 < ty_le:
                break
            nv.kinh_te.lt_chi += nang * 100

        nv.thoi_gian.active_phut += 3

    nv.lich_su.append(f"Trang Bị: {len(nv.trang_bis)}/16 ô đã trang bị")
    return nv


def mo_phong_vo_congs(nv: NhanVat):
    """Mô phỏng Võ Công"""
    ten_vg = [
        "Thiên Sơn Lục Dương Chưởng", "Vạn Đao Quyết", "Cổ Hoa Kiếm Pháp",
        "Huyền Thiên Chưởng", "Bát Hoang Quyền", "Thái Thanh Kiếm",
        "Vô Cực Công", "Lưu Tinh Chưởng", "Phượng Hoàng Vũ",
        "Thiên Cẩu Phệ Nhật", "Hỏa Tinh Thân Pháp", "Lưu Ly Thần Công",
        "Quái Quao Chưởng", "Suai Suai Quyền", "Thiên Sơn Lục Dương Kiếm",
        "Vạn Đao Loạn Vũ", "Cổ Hoa Tiên Pháp", "Huyền Thiên Kiếm",
        "Bát Hoang Chưởng", "Thái Thanh Tiên Pháp"
    ]

    for i in range(SO_KY_NANG_VG):
        pq = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]
        cap = random.randint(1, 100)
        bi_tich = cap * 2

        vg = VoCong(
            ten=ten_vg[i] if i < len(ten_vg) else f"Võ Công bí tịch {i+1}",
            pham_chat=pq + 1,
            pham_chat_ten=PHAM_CHAT_VG[pq],
            cap_do=cap,
            bi_tich=bi_tich
        )
        nv.vo_congs.append(vg)
        nv.kinh_te.mt_chi += bi_tich
        nv.thoi_gian.active_phut += cap * 0.5

    tong_atk_vg = sum(vg.cap_do * 0.5 for vg in nv.vo_congs)
    tong_def_vg = sum(vg.cap_do * 0.3 for vg in nv.vo_congs)
    nv.thong_so.atk += tong_atk_vg
    nv.thong_so.defn += tong_def_vg

    nv.lich_su.append(f"Võ Công: {len(nv.vo_congs)} kỹ năng - Tổng cấp: {sum(vg.cap_do for vg in nv.vo_congs)}")
    return nv


def mo_phong_cong_phap(nv: NhanVat):
    """Mô phỏng Công Pháp"""
    ten_cp = [
        "Thiên Long Bát Đại Công", "Vạn Pháp T帰 Tông", "Cổ Hoa Tiên Pháp",
        "Huyền Thiên Quyết", "Bát Hoang Chưởng Pháp", "Thái Thanh Tiên Pháp",
        "Vô Cực Công Pháp", "Lưu Tinh Công", "Phượng Hoàng Tiên Pháp",
        "Thiên Cẩu Hấp Nhật"
    ]

    for i in range(10):
        chi_phi = (i + 1) * 200
        nv.kinh_te.lt_chi += chi_phi
        nv.thoi_gian.active_phut += 5

        cp = CongPhap(
            ten=ten_cp[i] if i < len(ten_cp) else f"Công Pháp {i+1}",
            cap_do=i + 1,
            absorb_speed=(i + 1) * 10
        )
        nv.cong_phaps.append(cp)

        nv.thong_so.atk += (i + 1) * 2
        nv.thong_so.hp += (i + 1) * 10

    nv.lich_su.append(f"Công Pháp: {len(nv.cong_phaps)} kỹ năng")
    return nv


def mo_phong_thien_phu(nv: NhanVat):
    """Mô phỏng Thiên Phúc"""
    chi_so = ["HP", "ATK", "DEF", "CRIT", "Hút Mau", "May Mắn", "Né Tránh"]
    hieu_ung = {"HP": 10, "ATK": 5, "DEF": 5, "CRIT": 1.5, "Hút Mau": 2, "May Mắn": 1, "Né Tránh": 1}

    tong_diem = (CAP_TOI_DA // CAP_MOI_CANH) * 5
    diem_moi_cs = tong_diem // 7

    for cs in chi_so:
        diem = diem_moi_cs
        nv.thong_so.phan_thien_phu[cs] = diem

        if cs == "HP":
            nv.thong_so.hp += diem * hieu_ung[cs]
        elif cs == "ATK":
            nv.thong_so.atk += diem * hieu_ung[cs]
        elif cs == "DEF":
            nv.thong_so.defn += diem * hieu_ung[cs]
        elif cs == "CRIT":
            nv.thong_so.crit += diem * hieu_ung[cs]
        elif cs == "Hút Mau":
            nv.thong_so.hut_mau += diem * hieu_ung[cs]
        elif cs == "May Mắn":
            nv.thong_so.may_man += diem * hieu_ung[cs]
        elif cs == "Né Tránh":
            nv.thong_so.ne_tranh += diem * hieu_ung[cs]

    nv.thong_so.diem_thien_phu = tong_diem
    nv.thoi_gian.active_phut += 30
    nv.lich_su.append(f"Thiên Phúc: {tong_diem} điểm đã phân")
    return nv


def mo_phong_thap(nv: NhanVat):
    """Mô phỏng Tháp Yêu Thư"""
    for tang in range(1, THAP_TANG_TOI_DA + 1):
        thoi_gian = 5 + tang * 0.2
        nv.thoi_gian.chien_dau_phut += thoi_gian / 60
        nv.kinh_te.lt_thu += tang * 100
        nv.thong_so.hp += tang * 2

        if tang % 10 == 0:
            nv.thoi_gian.active_phut += 2

    nv.tang_thap = THAP_TANG_TOI_DA
    nv.lich_su.append(f"Tháp Yêu Thư: {THAP_TANG_TOI_DA} tầng")
    return nv


def mo_phong_thu_cung(nv: NhanVat):
    """Mô phỏng Thú Cưng"""
    ten = random.choice(TEN_PET)
    pq = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]

    nv.thu_cung = ThuCung(
        ten=ten,
        pham_chat=pq + 1,
        pham_chat_ten=PHAM_CHAT_PET[pq],
        cap_do=nv.thong_so.cap_do,
        exp=nv.thong_so.cap_do * 100
    )

    nv.thoi_gian.chien_dau_phut += nv.thong_so.cap_do * 0.1
    for lvl in range(1, nv.thong_so.cap_do + 1):
        nv.kinh_te.lt_thu += 10
        if lvl % 10 == 0:
            nv.thoi_gian.active_phut += 1

    nv.lich_su.append(f"Thú Cưng: {ten} - {PHAM_CHAT_PET[pq]} - Cấp {nv.thu_cung.cap_do}")
    return nv


def mo_phong_dau_truong(nv: NhanVat):
    """Mô phỏng Đấu Trường"""
    for ngay in range(30):
        for luot in range(5):
            nv.thoi_gian.chien_dau_phut += 5
            if random.random() > 0.4:
                nv.dau_truong_elo += random.randint(10, 30)
            else:
                nv.dau_truong_elo -= random.randint(5, 20)

    nv.kinh_te.lt_thu += 5000
    nv.kinh_te.mt_thu += 100
    nv.lich_su.append(f"Đấu Trường: ELO {nv.dau_truong_elo}")
    return nv


def mo_phong_nhiem_vu(nv: NhanVat):
    """Mô phỏng Nhiệm Vụ"""
    for ngay in range(30):
        nv.thoi_gian.nhiem_vu_phut += 30
        nv.kinh_te.lt_thu += 500
        nv.nhiem_vu.ngay += 5

    for tuan in range(12):
        nv.thoi_gian.nhiem_vu_phut += 60
        nv.kinh_te.lt_thu += 2000
        nv.nhiem_vu.tuan += 3

    for thang in range(3):
        nv.thoi_gian.nhiem_vu_phut += 120
        nv.kinh_te.lt_thu += 10000
        nv.nhiem_vu.thang += 2

    for thanh_tuu in range(50):
        nv.thoi_gian.nhiem_vu_phut += 10
        nv.kinh_te.lt_thu += 1000
        nv.nhiem_vu.thanh_tuu += 1

    for chuong in range(TONG_CANH_GIOI):
        nv.thoi_gian.nhiem_vu_phut += 20
        nv.kinh_te.lt_thu += 3000
        nv.nhiem_vu.chinh_tuyen += 1

    tong_nv = nv.nhiem_vu.ngay + nv.nhiem_vu.tuan + nv.nhiem_vu.thang + nv.nhiem_vu.thanh_tuu + nv.nhiem_vu.chinh_tuyen
    nv.lich_su.append(f"Nhiệm Vụ: {tong_nv} nhiệm vụ hoàn thành")
    return nv


def mo_phong_cua_hang(nv: NhanVat):
    """Mô phỏng Cửa Hàng & Phương Thị"""
    nv.thoi_gian.active_phut += 30
    nv.kinh_te.lt_chi += 10000

    nv.thoi_gian.active_phut += 60
    nv.kinh_te.lt_chi += 5000
    nv.kinh_te.lt_thu += 8000

    nv.lich_su.append("Cửa Hàng & Phương Thị")
    return nv


def mo_phong_che_tao(nv: NhanVat):
    """Mô phỏng Chế Tạo & Luyện Đan"""
    for i in range(30):
        nv.thoi_gian.active_phut += 5
        nv.kinh_te.lt_chi += 200

    for i in range(20):
        nv.thoi_gian.active_phut += 8
        nv.kinh_te.lt_chi += 300
        if random.random() > 0.3:
            nv.kinh_te.lt_thu += 500

    nv.lich_su.append("Chế Tạo & Luyện Đan")
    return nv


def mo_phong_ham_mo(nv: NhanVat):
    """Mô phỏng Hầm Mỏ"""
    for ngay in range(30):
        nv.thoi_gian.active_phut += 10
        nv.kinh_te.lt_thu += random.randint(100, 500)
        nv.kinh_te.mt_thu += random.randint(1, 5)

    nv.lich_su.append("Hầm Mỏ")
    return nv


def mo_phong_qua_tang(nv: NhanVat):
    """Mô phỏng Đổi Quà"""
    for i in range(10):
        nv.thoi_gian.active_phut += 1
        nv.kinh_te.lt_thu += 5000
        nv.kinh_te.mt_thu += 50

    nv.lich_su.append(f"Đổi Quà: 10 giftcode")
    return nv


def mo_phong_quy_doi(nv: NhanVat):
    """Mô phỏng Quy Đổi"""
    for i in range(5):
        so_mt = min(100000, nv.kinh_te.mt_thu - nv.kinh_te.mt_chi)
        lt_nhan = so_mt * MT_TO_LT
        nv.kinh_te.mt_chi += so_mt
        nv.kinh_te.lt_thu += lt_nhan
        nv.thoi_gian.active_phut += 1

    nv.lich_su.append("Quy Đổi MT -> LT")
    return nv


def mo_phong_mon_phai(nv: NhanVat):
    """Mô phỏng Môn Phái"""
    ten = random.choice(TEN_MON_PHAI)
    nv.mon_phai = ten
    nv.cap_mon_phai = SO_MON_PHAI

    for cap in range(1, SO_MON_PHAI + 1):
        nv.thoi_gian.active_phut += 10
        nv.kinh_te.lt_chi += cap * 500

    nv.lich_su.append(f"Môn Phái: {ten} - Cấp bậc: {SO_MON_PHAI}")
    return nv


def mo_phong_hao_huu(nv: NhanVat):
    """Mô phỏng Hảo Hữu"""
    nv.so_ban_be = 50
    nv.thoi_gian.active_phut += 50 * 0.5
    nv.lich_su.append(f"Hảo Hữu: {nv.so_ban_be} bạn bè")
    return nv


def mo_phong_lieu_thien(nv: NhanVat):
    """Mô phỏng Liêu Thiên"""
    nv.so_tin_nhan = 100
    nv.thoi_gian.active_phut += 100 * 0.5
    nv.lich_su.append(f"Liêu Thiên: {nv.so_tin_nhan} tin nhắn")
    return nv


def mo_phong_tui_do(nv: NhanVat):
    """Mô phỏng Túi Đồ"""
    nv.thoi_gian.active_phut += 60
    nv.lich_su.append("Túi Đồ: Quản lý 5 loại túi")
    return nv


def mo_phong_bang_xep_hang(nv: NhanVat):
    """Mô phỏng Bảng Xếp Hạng"""
    nv.thoi_gian.active_phut += 10
    nv.lich_su.append("Bảng Xếp Hạng: Xem 5 loại")
    return nv


# =========================================================================
# TÍNH TOÁN SỐ CUỐI CÙNG
# =========================================================================

def tinh_toan_cuoi_cung(nv: NhanVat):
    """Tính toán các chỉ số cuối cùng"""
    nv.thong_so.atk += nv.thong_so.cap_do * 2
    nv.thong_so.defn += nv.thong_so.cap_do * 1
    nv.thong_so.hp += nv.thong_so.cap_do * 50

    # Tính Chien Lực
    chien_luc = (nv.thong_so.atk * 10 + nv.thong_so.defn * 8 + nv.thong_so.hp +
                 nv.thong_so.crit * 30 + nv.thong_so.hut_mau * 25 +
                 nv.thong_so.diem_thien_phu * 50 + nv.thong_so.may_man * 10 +
                 nv.thong_so.ne_tranh * 10 + nv.than_trang)
    
    nv.thoi_gian.tong_phut = (nv.thoi_gian.afk_phut + nv.thoi_gian.active_phut +
                              nv.thoi_gian.chien_dau_phut + nv.thoi_gian.nhiem_vu_phut)
    
    return nv, chien_luc


# =========================================================================
# MÔ PHỎNG 1 NHÂN VẬT
# =========================================================================

def mo_phong_nhan_vat(id: int) -> tuple:
    """Mô phỏng 1 nhân vật qua tất cả tính năng"""
    nv = tao_nhan_vat(id)
    
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
    mo_phong_cua_hang(nv)
    mo_phong_che_tao(nv)
    mo_phong_ham_mo(nv)
    mo_phong_qua_tang(nv)
    mo_phong_quy_doi(nv)
    mo_phong_mon_phai(nv)
    mo_phong_hao_huu(nv)
    mo_phong_lieu_thien(nv)
    mo_phong_tui_do(nv)
    mo_phong_bang_xep_hang(nv)
    
    nv, chien_luc = tinh_toan_cuoi_cung(nv)
    return nv, chien_luc


# =========================================================================
# XUẤT BÁO CÁO
# =========================================================================

def xuat_bao_cao_chi_tiet(danh_sach_nv: list):
    """Xuất báo cáo chi tiết từng nhân vật"""
    
    report = []
    report.append("=" * 100)
    report.append("  BÁO CÁO CHI TIẾT 200 NHÂN VẬT - TAM TIỀN KÝ")
    report.append("  Trải nghiệm MAX TẤT CẢ TÍNH NĂNG từ lúc tạo nhân vật")
    report.append("  Ngày tạo báo cáo: 22/07/2026")
    report.append("=" * 100)
    report.append("")
    
    # =========================================================================
    # BÁO CÁO TỪNG NHÂN VẬT
    # =========================================================================
    
    for idx, (nv, chien_luc) in enumerate(danh_sach_nv, 1):
        report.append("")
        report.append("=" * 100)
        report.append(f"  NHÂN VẬT {idx}: {nv.ten}")
        report.append("=" * 100)
        
        # --- Thông tin cơ bản ---
        report.append("")
        report.append("  1. THÔNG TIN CƠ BẢN")
        report.append("  " + "-" * 60)
        report.append(f"     Tên: {nv.ten}")
        report.append(f"     Giới tính: {nv.gioi_tinh}")
        report.append(f"     Tiền truyện: {nv.tien_tri}")
        report.append(f"     Cấp độ: {nv.thong_so.cap_do}/{CAP_TOI_DA}")
        report.append(f"     Cảnh giới: {nv.thong_so.canh_gioi}/{TONG_CANH_GIOI}")
        report.append(f"     Môn Phái: {nv.mon_phai} (Cấp {nv.cap_mon_phai})")
        
        # --- Chỉ số chiến đấu ---
        report.append("")
        report.append("  2. CHỈ SỐ CHIẾN ĐẤU")
        report.append("  " + "-" * 60)
        report.append(f"     ATK (Công Kích): {nv.thong_so.atk:,.0f}")
        report.append(f"     DEF (Phòng Thủ): {nv.thong_so.defn:,.0f}")
        report.append(f"     HP (Sinh Lực): {nv.thong_so.hp:,.0f}")
        report.append(f"     CRIT (Chi Mạng): {nv.thong_so.crit:,.1f}%")
        report.append(f"     Hút Mau: {nv.thong_so.hut_mau:,.1f}%")
        report.append(f"     May Mắn: {nv.thong_so.may_man:,.1f}")
        report.append(f"     Né Tránh: {nv.thong_so.ne_tranh:,.1f}")
        report.append(f"     ★ CHIẾN LỰC: {chien_luc:,.0f}")
        
        # --- Thiên Phúc ---
        report.append("")
        report.append("  3. THIÊN PHÚC")
        report.append("  " + "-" * 60)
        report.append(f"     Tổng điểm: {nv.thong_so.diem_thien_phu}")
        for cs, diem in nv.thong_so.phan_thien_phu.items():
            report.append(f"     {cs}: {diem} điểm")
        
        # --- Linh Căn ---
        report.append("")
        report.append("  4. LINH CĂN (NGUYÊN TỐ)")
        report.append("  " + "-" * 60)
        report.append(f"     Nguyên tố: {nv.linh_can.nguyen_to}")
        tang_text = TANG_LINH_CAN[nv.linh_can.tang - 1] if nv.linh_can.tang > 0 else "Chưa có"
        report.append(f"     Tầng: {nv.linh_can.tang}/5 ({tang_text})")
        report.append(f"     Absorb Speed: +{nv.linh_can.absorb}%")
        report.append(f"     Boss đã đánh: {nv.linh_can.boss_da_danh}/5")
        report.append(f"     Nhiệm vụ tuần: {nv.linh_can.nhiem_vu_tuan}")
        
        # --- Thể Chất ---
        report.append("")
        report.append("  5. THỂ CHẤT")
        report.append("  " + "-" * 60)
        pq_text = PHAM_CHAT_THE_CHAT[nv.the_chat.pham_chat - 1] if nv.the_chat.pham_chat > 0 else "Chưa có"
        report.append(f"     Phẩm Chất: {nv.the_chat.pham_chat}/5 ({pq_text})")
        report.append(f"     Khai Mạch: {nv.the_chat.khai_mach}/6 giai đoạn")
        report.append(f"     Passives: HP +{nv.the_chat.khai_mach * 10}%, ATK +{nv.the_chat.khai_mach * 5}%")
        report.append(f"              DEF +{nv.the_chat.khai_mach * 5}%, CRIT +{nv.the_chat.khai_mach * 2}%")
        report.append(f"              EXP Boost +{nv.the_chat.khai_mach * 5}%, Hồi Máu +{nv.the_chat.khai_mach * 1}%")
        
        # --- Huyết Mạch ---
        report.append("")
        report.append("  6. HUYẾT MẠCH")
        report.append("  " + "-" * 60)
        pq_text = PHAM_CHAT_HUYET_MACH[nv.huyet_mach.pham_chat - 1] if nv.huyet_mach.pham_chat > 0 else "Chưa có"
        report.append(f"     Phẩm Chất: {nv.huyet_mach.pham_chat}/5 ({pq_text})")
        report.append(f"     Biến Hình: {nv.huyet_mach.bien_hinh} lần")
        report.append(f"     Passives: ATK +{nv.huyet_mach.pham_chat * 5}%, HP +{nv.huyet_mach.pham_chat * 10}%")
        report.append(f"              CRIT +{nv.huyet_mach.pham_chat * 3}%, Hút Mau +{nv.huyet_mach.pham_chat * 2}%")
        report.append(f"              Nguyên Tố +{nv.huyet_mach.pham_chat * 10}%, EXP +{nv.huyet_mach.pham_chat * 3}%")
        
        # --- Trang Bị ---
        report.append("")
        report.append("  7. TRANG BỊ (16 Ô)")
        report.append("  " + "-" * 60)
        for tb in nv.trang_bis:
            report.append(f"     [{tb.vi_tri}] {tb.pham_chat_ten} +{tb.cap_nang} | Nguyên Tố: {tb.nguyen_to}")
            report.append(f"        ATK: {tb.stats.get('ATK', 0):,.0f} | DEF: {tb.stats.get('DEF', 0):,.0f} | HP: {tb.stats.get('HP', 0):,.0f} | CRIT: {tb.stats.get('CRIT', 0):,.0f}")
        
        # --- Võ Công ---
        report.append("")
        report.append("  8. VÕ CÔNG (20 KỸ NĂNG)")
        report.append("  " + "-" * 60)
        for vg in nv.vo_congs:
            report.append(f"     [{vg.pham_chat_ten}] {vg.ten} - Cấp {vg.cap_do} | Bí Tịch: {vg.bi_tich} MT")
        
        # --- Công Pháp ---
        report.append("")
        report.append("  9. CÔNG PHÁP (10 KỸ NĂNG)")
        report.append("  " + "-" * 60)
        for cp in nv.cong_phaps:
            report.append(f"     {cp.ten} - Cấp {cp.cap_do} | Absorb: +{cp.absorb_speed}%")
        
        # --- Thú Cưng ---
        report.append("")
        report.append("  10. THÚ CƯNG")
        report.append("  " + "-" * 60)
        report.append(f"     Tên: {nv.thu_cung.ten}")
        report.append(f"     Phẩm Chất: {nv.thu_cung.pham_chat}/6 ({nv.thu_cung.pham_chat_ten})")
        report.append(f"     Cấp Độ: {nv.thu_cung.cap_do}")
        report.append(f"     EXP: {nv.thu_cung.exp:,.0f}")
        
        # --- Nhiệm Vụ ---
        report.append("")
        report.append("  11. NHIỆM VỤ")
        report.append("  " + "-" * 60)
        report.append(f"     Nhiệm Vụ Ngày: {nv.nhiem_vu.ngay}")
        report.append(f"     Nhiệm Vụ Tuần: {nv.nhiem_vu.tuan}")
        report.append(f"     Nhiệm Vụ Tháng: {nv.nhiem_vu.thang}")
        report.append(f"     Thành Tựu: {nv.nhiem_vu.thanh_tuu}")
        report.append(f"     Chính Tuyến: {nv.nhiem_vu.chinh_tuyen}")
        tong_nv = nv.nhiem_vu.ngay + nv.nhiem_vu.tuan + nv.nhiem_vu.thang + nv.nhiem_vu.thanh_tuu + nv.nhiem_vu.chinh_tuyen
        report.append(f"     ★ TỔNG: {tong_nv} nhiệm vụ")
        
        # --- Đột Phá ---
        report.append("")
        report.append("  12. ĐỘT PHÁ")
        report.append("  " + "-" * 60)
        report.append(f"     Đột Phá Thành Công: {nv.so_dot_pha_thanh_cong}")
        report.append(f"     Đột Phá Thất Bại: {nv.so_dot_pha_that_bai}")
        ty_le = nv.so_dot_pha_thanh_cong / max(nv.so_dot_pha_thanh_cong + nv.so_dot_pha_that_bai, 1) * 100
        report.append(f"     Tỷ Lệ Thành Công: {ty_le:.1f}%")
        
        # --- Đấu Trường ---
        report.append("")
        report.append("  13. ĐẤU TRƯỜNG")
        report.append("  " + "-" * 60)
        report.append(f"     ELO: {nv.dau_truong_elo}")
        report.append(f"     Thứ Hạng: {'Thiên Hạ Vô Song' if nv.dau_truong_elo > 1500 else 'Đệ Nhất Vô Song' if nv.dau_truong_elo > 1200 else 'Bình Thường'}")
        
        # --- Tháp Yêu Thư ---
        report.append("")
        report.append("  14. THÁP YÊU THƯ")
        report.append("  " + "-" * 60)
        report.append(f"     Tầng Đã Leo: {nv.tang_thap}/{THAP_TANG_TOI_DA}")
        
        # --- Thẩm Tra (Thanh Trang) ---
        report.append("")
        report.append("  15. THANH TRANG (SET)")
        report.append("  " + "-" * 60)
        report.append(f"     Set Power: {nv.than_trang}")
        
        # --- Kinh Tế ---
        report.append("")
        report.append("  16. KINH TẾ")
        report.append("  " + "-" * 60)
        report.append(f"     Linh Thạch Thu: {nv.kinh_te.lt_thu:,.0f} LT")
        report.append(f"     Linh Thạch Chi: {nv.kinh_te.lt_chi:,.0f} LT")
        report.append(f"     Ma Thạch Thu: {nv.kinh_te.mt_thu:,.0f} MT")
        report.append(f"     Ma Thạch Chi: {nv.kinh_te.mt_chi:,.0f} MT")
        du_lt = nv.kinh_te.lt_thu - nv.kinh_te.lt_chi
        report.append(f"     Dư LT: {du_lt:,.0f} LT {'(THIẾU)' if du_lt < 0 else '(DƯ)'}")
        
        # --- Thời Gian ---
        report.append("")
        report.append("  17. THỜI GIAN")
        report.append("  " + "-" * 60)
        report.append(f"     AFK Tu Luyện: {nv.thoi_gian.afk_phut/60:,.1f} giờ ({nv.thoi_gian.afk_phut/nv.thoi_gian.tong_phut*100:.1f}%)")
        report.append(f"     Active Chơi: {nv.thoi_gian.active_phut/60:,.1f} giờ ({nv.thoi_gian.active_phut/nv.thoi_gian.tong_phut*100:.1f}%)")
        report.append(f"     Chiến Đấu: {nv.thoi_gian.chien_dau_phut/60:,.1f} giờ ({nv.thoi_gian.chien_dau_phut/nv.thoi_gian.tong_phut*100:.1f}%)")
        report.append(f"     Nhiệm Vụ: {nv.thoi_gian.nhiem_vu_phut/60:,.1f} giờ ({nv.thoi_gian.nhiem_vu_phut/nv.thoi_gian.tong_phut*100:.1f}%)")
        report.append(f"     ★ TỔNG: {nv.thoi_gian.tong_phut/60:,.1f} giờ = {nv.thoi_gian.tong_phut/1440:,.1f} ngày")
        
        # --- Hảo Hữu & Liêu Thiên ---
        report.append("")
        report.append("  18. HẢO HỮU & LIÊU THIÊN")
        report.append("  " + "-" * 60)
        report.append(f"     Số Bạn Bè: {nv.so_ban_be}")
        report.append(f"     Số Tin Nhắn: {nv.so_tin_nhan}")
        
        report.append("")
        report.append("  " + "=" * 60)
        report.append(f"  KẾT THÚC NHÂN VẬT {idx}")
        report.append("  " + "=" * 60)
    
    # =========================================================================
    # TỔNG KẾT 200 NHÂN VẬT
    # =========================================================================
    
    report.append("")
    report.append("=" * 100)
    report.append("  TỔNG KẾT 200 NHÂN VẬT")
    report.append("=" * 100)
    
    # Thống kê thời gian
    tong_thoi_gian = [nv.thoi_gian.tong_phut for nv, _ in danh_sach_nv]
    afk_thoi_gian = [nv.thoi_gian.afk_phut for nv, _ in danh_sach_nv]
    active_thoi_gian = [nv.thoi_gian.active_phut for nv, _ in danh_sach_nv]
    chien_dau_thoi_gian = [nv.thoi_gian.chien_dau_phut for nv, _ in danh_sach_nv]
    nhiem_vu_thoi_gian = [nv.thoi_gian.nhiem_vu_phut for nv, _ in danh_sach_nv]
    
    tb_tong = sum(tong_thoi_gian) / len(tong_thoi_gian)
    tb_afk = sum(afk_thoi_gian) / len(afk_thoi_gian)
    tb_active = sum(active_thoi_gian) / len(active_thoi_gian)
    tb_chien_dau = sum(chien_dau_thoi_gian) / len(chien_dau_thoi_gian)
    tb_nhiem_vu = sum(nhiem_vu_thoi_gian) / len(nhiem_vu_thoi_gian)
    
    report.append("")
    report.append("  A. THỐNG KÊ THỜI GIAN (TRUNG BÌNH/NHÂN VẬT)")
    report.append("  " + "-" * 60)
    report.append(f"     Thời gian trung bình: {tb_tong:,.0f} phút = {tb_tong/60:,.1f} giờ = {tb_tong/1440:,.1f} ngày")
    report.append(f"     AFK Tu Luyện: {tb_afk:,.0f} phút ({tb_afk/tb_tong*100:.1f}%)")
    report.append(f"     Active Chơi: {tb_active:,.0f} phút ({tb_active/tb_tong*100:.1f}%)")
    report.append(f"     Chiến Đấu: {tb_chien_dau:,.0f} phút ({tb_chien_dau/tb_tong*100:.1f}%)")
    report.append(f"     Nhiệm Vụ: {tb_nhiem_vu:,.0f} phút ({tb_nhiem_vu/tb_tong*100:.1f}%)")
    
    # Thống kê kinh tế
    lt_thu = [nv.kinh_te.lt_thu for nv, _ in danh_sach_nv]
    lt_chi = [nv.kinh_te.lt_chi for nv, _ in danh_sach_nv]
    mt_thu = [nv.kinh_te.mt_thu for nv, _ in danh_sach_nv]
    mt_chi = [nv.kinh_te.mt_chi for nv, _ in danh_sach_nv]
    
    report.append("")
    report.append("  B. THỐNG KÊ KINH TẾ (TRUNG BÌNH/NHÂN VẬT)")
    report.append("  " + "-" * 60)
    report.append(f"     Linh Thạch Thu: {sum(lt_thu)/len(lt_thu):,.0f} LT")
    report.append(f"     Linh Thạch Chi: {sum(lt_chi)/len(lt_chi):,.0f} LT")
    report.append(f"     Dư LT: {(sum(lt_thu)-sum(lt_chi))/len(lt_thu):,.0f} LT")
    report.append(f"     Ma Thạch Thu: {sum(mt_thu)/len(mt_thu):,.0f} MT")
    report.append(f"     Ma Thạch Chi: {sum(mt_chi)/len(mt_chi):,.0f} MT")
    
    # Thống kê chỉ số
    atks = [nv.thong_so.atk for nv, _ in danh_sach_nv]
    defs = [nv.thong_so.defn for nv, _ in danh_sach_nv]
    hps = [nv.thong_so.hp for nv, _ in danh_sach_nv]
    crits = [nv.thong_so.crit for nv, _ in danh_sach_nv]
    chien_lucs = [cl for _, cl in danh_sach_nv]
    
    report.append("")
    report.append("  C. THỐNG KÊ CHỈ SỐ (TRUNG BÌNH/NHÂN VẬT)")
    report.append("  " + "-" * 60)
    report.append(f"     ATK: {sum(atks)/len(atks):,.0f}")
    report.append(f"     DEF: {sum(defs)/len(defs):,.0f}")
    report.append(f"     HP: {sum(hps)/len(hps):,.0f}")
    report.append(f"     CRIT: {sum(crits)/len(crits):,.1f}%")
    report.append(f"     ★ CHIẾN LỰC: {sum(chien_lucs)/len(chien_lucs):,.0f}")
    
    # Thống kê Linh Căn
    nguyen_to_count = {}
    for nv, _ in danh_sach_nv:
        nt = nv.linh_can.nguyen_to
        nguyen_to_count[nt] = nguyen_to_count.get(nt, 0) + 1
    
    report.append("")
    report.append("  D. THỐNG KÊ LINH CĂN")
    report.append("  " + "-" * 60)
    for nt, sl in sorted(nguyen_to_count.items(), key=lambda x: -x[1]):
        report.append(f"     {nt}: {sl} nhân vật ({sl/200*100:.1f}%)")
    
    # Thống kê Thể Chất
    the_chat_count = {}
    for nv, _ in danh_sach_nv:
        pq = PHAM_CHAT_THE_CHAT[nv.the_chat.pham_chat - 1] if nv.the_chat.pham_chat > 0 else "Chưa có"
        the_chat_count[pq] = the_chat_count.get(pq, 0) + 1
    
    report.append("")
    report.append("  E. THỐNG KÊ THỂ CHẤT")
    report.append("  " + "-" * 60)
    for pq, sl in sorted(the_chat_count.items(), key=lambda x: -x[1]):
        report.append(f"     {pq}: {sl} nhân vật ({sl/200*100:.1f}%)")
    
    # Thống kê Huyết Mạch
    huyet_mach_count = {}
    for nv, _ in danh_sach_nv:
        pq = PHAM_CHAT_HUYET_MACH[nv.huyet_mach.pham_chat - 1] if nv.huyet_mach.pham_chat > 0 else "Chưa có"
        huyet_mach_count[pq] = huyet_mach_count.get(pq, 0) + 1
    
    report.append("")
    report.append("  F. THỐNG KÊ HUYẾT MẠCH")
    report.append("  " + "-" * 60)
    for pq, sl in sorted(huyet_mach_count.items(), key=lambda x: -x[1]):
        report.append(f"     {pq}: {sl} nhân vật ({sl/200*100:.1f}%)")
    
    # Thống kê Võ Công
    vg_pham_chat_count = {}
    for nv, _ in danh_sach_nv:
        for vg in nv.vo_congs:
            vg_pham_chat_count[vg.pham_chat_ten] = vg_pham_chat_count.get(vg.pham_chat_ten, 0) + 1
    
    report.append("")
    report.append("  G. THỐNG KÊ VÕ CÔNG")
    report.append("  " + "-" * 60)
    for pq, sl in sorted(vg_pham_chat_count.items(), key=lambda x: -x[1]):
        report.append(f"     {pq}: {sl} kỹ năng ({sl/(200*20)*100:.1f}%)")
    
    # Thống kê Thú Cưng
    pet_pham_chat_count = {}
    for nv, _ in danh_sach_nv:
        pet_pham_chat_count[nv.thu_cung.pham_chat_ten] = pet_pham_chat_count.get(nv.thu_cung.pham_chat_ten, 0) + 1
    
    report.append("")
    report.append("  H. THỐNG KÊ THÚ CƯNG")
    report.append("  " + "-" * 60)
    for pq, sl in sorted(pet_pham_chat_count.items(), key=lambda x: -x[1]):
        report.append(f"     {pq}: {sl} thú cưng ({sl/200*100:.1f}%)")
    
    # Thống kê Nhiệm Vụ
    tong_nv_ngay = sum(nv.nhiem_vu.ngay for nv, _ in danh_sach_nv)
    tong_nv_tuan = sum(nv.nhiem_vu.tuan for nv, _ in danh_sach_nv)
    tong_nv_thang = sum(nv.nhiem_vu.thang for nv, _ in danh_sach_nv)
    tong_nv_thanh_tuu = sum(nv.nhiem_vu.thanh_tuu for nv, _ in danh_sach_nv)
    tong_nv_chinh_tuyen = sum(nv.nhiem_vu.chinh_tuyen for nv, _ in danh_sach_nv)
    
    report.append("")
    report.append("  I. THỐNG KÊ NHIỆM VỤ (TỔNG 200 NHÂN VẬT)")
    report.append("  " + "-" * 60)
    report.append(f"     Nhiệm Vụ Ngày: {tong_nv_ngay:,}")
    report.append(f"     Nhiệm Vụ Tuần: {tong_nv_tuan:,}")
    report.append(f"     Nhiệm Vụ Tháng: {tong_nv_thang:,}")
    report.append(f"     Thành Tựu: {tong_nv_thanh_tuu:,}")
    report.append(f"     Chính Tuyến: {tong_nv_chinh_tuyen:,}")
    report.append(f"     ★ TỔNG: {tong_nv_ngay+tong_nv_tuan+tong_nv_thang+tong_nv_thanh_tuu+tong_nv_chinh_tuyen:,}")
    
    # Thống kê Đột Phá
    tong_dot_pha_tc = sum(nv.so_dot_pha_thanh_cong for nv, _ in danh_sach_nv)
    tong_dot_pha_tb = sum(nv.so_dot_pha_that_bai for nv, _ in danh_sach_nv)
    
    report.append("")
    report.append("  J. THỐNG KÊ ĐỘT PHÁ (TỔNG 200 NHÂN VẬT)")
    report.append("  " + "-" * 60)
    report.append(f"     Đột Phá Thành Công: {tong_dot_pha_tc:,}")
    report.append(f"     Đột Phá Thất Bại: {tong_dot_pha_tb:,}")
    ty_le_tc = tong_dot_pha_tc / max(tong_dot_pha_tc + tong_dot_pha_tb, 1) * 100
    report.append(f"     Tỷ Lệ Thành Công: {ty_le_tc:.1f}%")
    
    # Tổng kết
    report.append("")
    report.append("=" * 100)
    report.append("  KẾT LUẬN")
    report.append("=" * 100)
    report.append("")
    report.append(f"  Thời gian MAX 1 nhân vật: ~{tb_tong/1440:,.1f} ngày ({tb_tong/60:,.1f} giờ)")
    report.append(f"  Chi phí MAX 1 nhân vật: ~{(sum(lt_chi)/len(lt_chi) + sum(mt_chi)/len(mt_chi)*MT_TO_LT)/1000:,.0f}K LT")
    report.append(f"  200 nhân vật đồng thời: ~{max(tong_thoi_gian)/1440:,.1f} ngày")
    report.append(f"  Tổng chi phí 200 NV: ~{(sum(lt_chi)+sum(mt_chi)*MT_TO_LT)/1000000:,.1f}M LT")
    report.append("")
    report.append("  Ghi chú: Đây là kết quả mô phỏng, thực tế có thể thay đổi")
    report.append("  tùy theo mức độ nạp tiền, mức độ chơi, và RNG (may mắn).")
    report.append("")
    report.append("=" * 100)
    report.append("  HOÀN THÀNH BÁO CÁO!")
    report.append("=" * 100)
    
    return "\n".join(report)


# =========================================================================
# CHAY MO PHONG
# =========================================================================

import sys
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

if __name__ == "__main__":
    print("Dang mo phong 200 nhan vat...")
    
    danh_sach_nv = []
    for i in range(1, 201):
        nv, chien_luc = mo_phong_nhan_vat(i)
        danh_sach_nv.append((nv, chien_luc))
        if i % 50 == 0:
            print(f"  Da hoan thanh {i}/200 nhan vat...")
    
    print("Dang xuat bao cao...")
    
    bao_cao = xuat_bao_cao_chi_tiet(danh_sach_nv)
    
    # Luu file
    import os
    file_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "BAOCAO-CHITIET-200NHANVAT.txt")
    with open(file_path, "w", encoding="utf-8") as f:
        f.write(bao_cao)
    
    print(f"Da luu file: {file_path}")
    print("Hoan thanh!")
