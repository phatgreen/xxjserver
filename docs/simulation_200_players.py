"""
==========================================================================
  GIA LAP 200 NHAN VAT - TAM TIEN KY
  Trai nghiem MAX TAT CA TINH NANG tu tao nhan vat
  Tinh toan thoi gian va tieu thu cu the
==========================================================================
"""

import math
import random
from dataclasses import dataclass, field
from typing import List, Dict
from enum import Enum

# =========================================================================
# CONSTANTS - FROM GAME DATA
# =========================================================================

# Realm system
TOTAL_REALMS = 87          # 87 canh gioi
PHASES_PER_REALM = 10      # 10 giai doan per canh gioi
LEVELS_PER_REALM = 50      # 50 cap per canh gioi
MAX_LEVEL = TOTAL_REALMS * LEVELS_PER_REALM  # = 4350
THIEN_PHU_UNLOCK_LEVEL = 30
THIEN_PHU_POINTS_PER_BREAKTHROUGH = 5
TOWER_FLOORS = 150
MARTIAL_ART_QUALITY_COUNT = 6
PET_QUALITY_COUNT = 6
CLAN_RANKS = 7
ELEMENTS = ["Kim", "Moc", "Thuy", "Hoa", "Tho"]
SPIRITUAL_ROOT_TIERS = 5   # Don He -> Ngu Hanh
EQUIPMENT_SLOTS = 16

# AFK Cultivation tiers
AFK_TIERS = {
    "free":  {"multiplier": 1, "max_hours": 0.5,  "cost": 0,      "currency": "none"},
    "lt":    {"multiplier": 2, "max_hours": 3,    "cost": 5000,   "currency": "LT"},
    "mt":    {"multiplier": 3, "max_hours": 6,    "cost": 200,    "currency": "MT"},
}

# Exchange rate
MT_TO_LT = 1000  # 1 MT = 1000 LT

# Travel cost
def travel_cost(level):
    return level * 12 + 500  # LT

# Breakthrough success rate
def breakthrough_rate(level, realm, phase_bonus=0, equip_bonus=0):
    return max(5, 75 - level * 0.6 - realm * 3 + phase_bonus + equip_bonus)

# EXP formula
def exp_rate(level, vip=1, multiplier=1, cp_absorb=0, lc_absorb=0):
    return level * vip * multiplier * min(1.3, 1 + cp_absorb/100) * min(1.3, 1 + lc_absorb/100)

# Combat power formula
def combat_power(atk, defn, hp, crit, hut_mau, thien_phu, may_man, ne_tranh, than_trang_power=0):
    return atk*10 + defn*8 + hp + crit*30 + hut_mau*25 + thien_phu*50 + may_man*10 + ne_tranh*10 + than_trang_power

# AFK speed formula
def afk_speed(level, vip=1, multiplier=1, cp_absorb=0, lc_absorb=0):
    return level * vip * multiplier * min(1.3, 1 + cp_absorb/100) * min(1.3, 1 + lc_absorb/100)


# =========================================================================
# PLAYER SIMULATION
# =========================================================================

@dataclass
class PlayerStats:
    level: int = 1
    realm: int = 1
    phase: int = 1
    hp: float = 100
    atk: float = 10
    defn: float = 5
    crit: float = 0
    hut_mau: float = 0
    thien_phu_points: int = 0
    thien_phu_invested: Dict[str, int] = field(default_factory=lambda: {
        "HP": 0, "ATK": 0, "DEF": 0, "CRIT": 0, "HutMau": 0, "MayMan": 0, "NeTranh": 0
    })
    may_man: float = 0
    ne_tranh: float = 0
    vip: int = 0

    # Spirit root
    spiritual_root_tier: int = 0
    spiritual_root_element: str = "None"
    spiritual_root_absorb: float = 0

    # Body constitution
    constitution_quality: int = 0
    constitution_awakened: int = 0

    # Blood vein
    blood_vein_quality: int = 0
    blood_vein_transform_count: int = 0

    # Equipment
    equipment_count: int = 0
    equipment_quality: Dict[str, int] = field(default_factory=dict)

    # Martial arts
    martial_arts_count: int = 0
    martial_arts_levels: List[int] = field(default_factory=list)

    # Pet
    pet_quality: int = 0
    pet_level: int = 1

    # Tower
    tower_floor: int = 0

    # Arena
    arena_elo: int = 1000
    arena_daily_fights: int = 0

    # Clan
    clan_rank: int = 0

    # Quests completed
    quests_completed: Dict[str, int] = field(default_factory=lambda: {
        "daily": 0, "weekly": 0, "monthly": 0, "achievement": 0, "main": 0
    })


@dataclass
class PlayerEconomy:
    lt_earned: float = 0
    lt_spent: float = 0
    mt_earned: float = 0
    mt_spent: float = 0
    tf_earned: int = 0
    tf_spent: int = 0


@dataclass
class TimeBreakdown:
    total_minutes: float = 0
    afk_minutes: float = 0
    active_minutes: float = 0
    battle_minutes: float = 0
    quest_minutes: float = 0
    system_minutes: float = 0  # UI, crafting, etc.


class Player:
    def __init__(self, player_id: int, name: str):
        self.id = player_id
        self.name = name
        self.stats = PlayerStats()
        self.economy = PlayerEconomy()
        self.time = TimeBreakdown()
        self.history = []

    def log(self, event: str):
        self.history.append(event)


# =========================================================================
# SIMULATION FUNCTIONS
# =========================================================================

def simulate_character_creation(p: Player):
    """Phase 1: Tao nhan vat"""
    p.log("Tao nhan vat - Chon tien tri: Chien si")
    # Character creation takes ~2 minutes (name, gender, class selection)
    p.time.active_minutes += 2
    return p


def simulate_leveling(p: Player, target_level: int = MAX_LEVEL):
    """
    Phase 2: Leveling from 1 to max (4350)
    Uses AFK cultivation + active farming
    """
    current_level = p.stats.level
    total_afk_hours = 0
    total_lt_spent = 0

    while current_level < target_level:
        # Determine realm
        realm = (current_level - 1) // LEVELS_PER_REALM + 1
        phase = ((current_level - 1) % LEVELS_PER_REALM) // 5 + 1

        # EXP needed per level (increases with level)
        exp_needed = current_level * 100 * realm

        # AFK cultivation - use LT tier for efficiency
        if p.economy.lt_earned - p.economy.lt_spent > AFK_TIERS["lt"]["cost"]:
            tier = "lt"
            mult = AFK_TIERS["lt"]["multiplier"]
            cost_per_hour = AFK_TIERS["lt"]["cost"] / 3
            max_session = 3
        else:
            tier = "free"
            mult = AFK_TIERS["free"]["multiplier"]
            cost_per_hour = 0
            max_session = 0.5

        # Calculate AFK speed
        cp_absorb = p.stats.constitution_awakened * 10
        lc_absorb = p.stats.spiritual_root_absorb
        speed = afk_speed(current_level, multiplier=mult, cp_absorb=cp_absorb, lc_absorb=lc_absorb)

        # Hours to gain enough EXP for next level(s)
        levels_gained = min(50, target_level - current_level)
        total_exp_needed = sum((current_level + i) * 100 * realm for i in range(levels_gained))
        hours_needed = total_exp_needed / max(speed, 1)

        # Cap at AFK tier max
        hours_needed = min(hours_needed, max_session)

        # Cost
        lt_cost = hours_needed * cost_per_hour
        p.economy.lt_spent += lt_cost
        total_lt_spent += lt_cost

        # EXP gained
        exp_gained = speed * hours_needed
        levels_from_afk = int(exp_gained / (current_level * 100 * realm))
        levels_from_afk = max(1, min(levels_from_afk, levels_gained))

        current_level += levels_from_afk
        total_afk_hours += hours_needed

        # Active farming time (30 sec per battle)
        active_farm_minutes = levels_from_afk * 0.5  # 30 sec per battle
        p.time.battle_minutes += active_farm_minutes

        # Log breakthrough if needed
        if current_level % LEVELS_PER_REALM == 0 and current_level > 0:
            realm_num = current_level // LEVELS_PER_REALM
            p.log(f"Dot pha lon - Canh gioi {realm_num}")
            # Breakthrough takes time
            p.time.active_minutes += 5  # UI time for breakthrough

    p.stats.level = current_level
    p.time.afk_minutes = total_afk_hours * 60
    p.economy.lt_spent = total_lt_spent
    return p


def simulate_breakthroughs(p: Player):
    """Phase 3: Dot pha - all realm breakthroughs"""
    total_breakthroughs = 0
    failed_breakthroughs = 0
    lt_for_breakthrough = 0

    for realm in range(1, TOTAL_REALMS + 1):
        for phase in range(1, PHASES_PER_REALM + 1):
            level = (realm - 1) * LEVELS_PER_REALM + phase * 5
            rate = breakthrough_rate(level, realm, phase_bonus=5, equip_bonus=10)
            cost = realm * 100 + phase * 50

            if random.random() * 100 < rate:
                total_breakthroughs += 1
                lt_for_breakthrough += cost
            else:
                failed_breakthroughs += 1
                lt_for_breakthrough += cost * 2
                p.time.active_minutes += 2

    p.economy.lt_spent += lt_for_breakthrough
    p.time.active_minutes += total_breakthroughs * 2  # 2 min per breakthrough UI
    p.log(f"Hoan thanh {total_breakthroughs} dot pha, {failed_breakthroughs} that bai")
    return p


def simulate_spiritual_root(p: Player):
    """Phase 4: Linh Can - Nguyen to"""
    element = random.choice(ELEMENTS)
    p.stats.spiritual_root_element = element

    for tier in range(1, SPIRITUAL_ROOT_TIERS + 1):
        # Upgrade cost
        lt_cost = tier * 500
        da_ngu_hanh_cost = tier * 10
        success_rate = max(20, 80 - tier * 10)

        if random.random() * 100 < success_rate:
            p.stats.spiritual_root_tier = tier
            p.stats.spiritual_root_absorb = tier * 15  # absorb speed bonus

        p.economy.lt_spent += lt_cost
        p.time.active_minutes += 3  # 3 min per upgrade attempt

    # Boss fights (5 element bosses)
    for i in range(5):
        p.time.battle_minutes += 15  # 15 min per boss fight
        p.economy.lt_earned += 2000  # boss rewards

    # Weekly quests
    for week in range(12):  # 12 weeks to max
        p.time.quest_minutes += 30
        p.economy.lt_earned += 500

    p.log(f"Linh Can {element} - Tang {p.stats.spiritual_root_tier}")
    return p


def simulate_body_constitution(p: Player):
    """Phase 5: The Chat"""
    qualities = ["Tap Pham", "Linh Pham", "Quang Pham", "Thanh Pham", "Tien Thien"]

    for q in range(len(qualities)):
        success_rate = max(15, 70 - q * 12)
        lt_cost = (q + 1) * 300
        da_cost = (q + 1) * 5

        if random.random() * 100 < success_rate:
            p.stats.constitution_quality = q + 1

        p.economy.lt_spent += lt_cost
        p.time.active_minutes += 5

    # Awakening (6 stages)
    for stage in range(1, 7):
        realm_required = stage * 15
        lt_cost = stage * 1000
        p.economy.lt_spent += lt_cost
        p.stats.constitution_awakened = stage
        p.time.active_minutes += 10

    p.log(f"The Chat - Pham chat {p.stats.constitution_quality}, Khai mach {p.stats.constitution_awakened}")
    return p


def simulate_blood_vein(p: Player):
    """Phase 6: Huyet Mach"""
    qualities = ["Tap Pham", "Linh Pham", "Quang Pham", "Thanh Pham", "Tien Thien"]

    for q in range(len(qualities)):
        success_rate = max(10, 65 - q * 10)
        lt_cost = (q + 1) * 400

        if random.random() * 100 < success_rate:
            p.stats.blood_vein_quality = q + 1

        p.economy.lt_spent += lt_cost
        p.time.active_minutes += 5

    # Awakening
    for stage in range(1, 6):
        lt_cost = stage * 800
        p.economy.lt_spent += lt_cost
        p.time.active_minutes += 8

    # Transform attempts
    for i in range(10):
        p.stats.blood_vein_transform_count += 1
        p.time.battle_minutes += 10
        p.time.active_minutes += 2

    p.log(f"Huyet Mach - Pham chat {p.stats.blood_vein_quality}, Bien hinh {p.stats.blood_vein_transform_count} lan")
    return p


def simulate_equipment(p: Player):
    """Phase 7: Trang Bi - 16 o"""
    qualities = ["Pham", "Lam", "Luc", "Linh", "Hoang Kim", "Do"]

    for slot in range(1, EQUIPMENT_SLOTS + 1):
        # Buy from shop + drops
        lt_cost = random.choice([500, 1000, 2000, 5000])
        p.economy.lt_spent += lt_cost

        # Enhancement
        for enhance in range(1, 20):
            enhance_cost = enhance * 100
            success_rate = max(10, 80 - enhance * 3)
            if random.random() * 100 < success_rate:
                break
            p.economy.lt_spent += enhance_cost

        p.stats.equipment_count += 1
        p.time.active_minutes += 3  # UI time per slot

    p.log(f"Trang bi: {p.stats.equipment_count}/16 o")
    return p


def simulate_martial_arts(p: Player):
    """Phase 8: Vo Cong"""
    qualities = ["Pham", "Lam", "Luc", "Linh", "Hoang Kim", "Do"]

    # Learn 20 martial arts
    for i in range(20):
        quality = random.choices(range(6), weights=[30, 25, 20, 15, 7, 3])[0]
        level = random.randint(1, 100)

        # Cost per level
        mt_cost = level * 2  # Bi tich cost
        p.economy.mt_spent += mt_cost

        # Training EXP
        exp_needed = level * 50
        p.time.active_minutes += level * 0.5  # 30 sec per level

        p.stats.martial_arts_count += 1
        p.stats.martial_arts_levels.append(level)

    # ATK/DEF bonus from martial arts
    total_ma_atk = sum(lvl * 0.5 for lvl in p.stats.martial_arts_levels)
    total_ma_def = sum(lvl * 0.3 for lvl in p.stats.martial_arts_levels)
    p.stats.atk += total_ma_atk
    p.stats.defn += total_ma_def

    p.log(f"Vo Cong: {p.stats.martial_arts_count} ky nang, tong cap {sum(p.stats.martial_arts_levels)}")
    return p


def simulate_cultivation_techniques(p: Player):
    """Phase 9: Cong Phap"""
    # Learn 10 cultivation techniques
    for i in range(10):
        lt_cost = (i + 1) * 200
        p.economy.lt_spent += lt_cost
        p.time.active_minutes += 5

        # Passive bonus
        p.stats.atk += (i + 1) * 2
        p.stats.hp += (i + 1) * 10

    p.log("Cong Phap: 10 ky nang")
    return p


def simulate_thien_phu(p: Player):
    """Phase 10: Thien Phu - Talent tree"""
    # 7 stats to invest
    stats_list = ["HP", "ATK", "DEF", "CRIT", "HutMau", "MayMan", "NeTranh"]
    bonuses = {"HP": 10, "ATK": 5, "DEF": 5, "CRIT": 1.5, "HutMau": 2, "MayMan": 1, "NeTranh": 1}

    total_points = (MAX_LEVEL // LEVELS_PER_REALM) * THIEN_PHU_POINTS_PER_BREAKTHROUGH
    points_per_stat = total_points // 7

    for stat in stats_list:
        points = points_per_stat
        p.stats.thien_phu_invested[stat] = points

        if stat == "HP":
            p.stats.hp += points * bonuses[stat]
        elif stat == "ATK":
            p.stats.atk += points * bonuses[stat]
        elif stat == "DEF":
            p.stats.defn += points * bonuses[stat]
        elif stat == "CRIT":
            p.stats.crit += points * bonuses[stat]
        elif stat == "HutMau":
            p.stats.hut_mau += points * bonuses[stat]
        elif stat == "MayMan":
            p.stats.may_man += points * bonuses[stat]
        elif stat == "NeTranh":
            p.stats.ne_tranh += points * bonuses[stat]

    p.stats.thien_phu_points = total_points
    p.time.active_minutes += 30  # 30 min to allocate all points
    p.log(f"Thien Phu: {total_points} diem da phan")
    return p


def simulate_tower(p: Player):
    """Phase 11: Thap Yeu Thu - 150 tang"""
    for floor in range(1, TOWER_FLOORS + 1):
        # Battle time per floor
        battle_time = 5 + floor * 0.2  # 5-35 sec per floor
        p.time.battle_minutes += battle_time / 60

        # Rewards
        p.economy.lt_earned += floor * 100
        p.stats.hp += floor * 2  # stat gains

        # Weekly reset (every 7 days)
        if floor % 10 == 0:
            p.time.active_minutes += 2  # reset time

    # Total tower time
    total_tower_minutes = sum((5 + f * 0.2) / 60 for f in range(1, TOWER_FLOORS + 1))
    p.time.battle_minutes += total_tower_minutes
    p.log(f"Thap Yeu Thu: {TOWER_FLOORS} tang")
    return p


def simulate_pet(p: Player):
    """Phase 12: Sung Vat"""
    # Gacha for pet
    qualities = ["Binh thuong", "Kha", "Tot", "Xuat sac", "Hai Pham", "Nghich thien"]
    weights = [30, 25, 20, 15, 7, 3]

    pet_quality = random.choices(range(6), weights=weights)[0]
    p.stats.pet_quality = pet_quality

    # Level pet to max (same as player level)
    for lvl in range(1, p.stats.level + 1):
        p.economy.lt_earned += 10  # pet EXP from farming
        if lvl % 10 == 0:
            p.time.active_minutes += 1  # pet UI time

    # Pet combat time (in PvE)
    p.time.battle_minutes += p.stats.level * 0.1  # 6 sec per level

    p.log(f"Pet: {qualities[pet_quality]}")
    return p


def simulate_arena(p: Player):
    """Phase 13: Dau Truong"""
    # 5 fights per day, ~30 days to max
    for day in range(30):
        for fight in range(5):
            p.time.battle_minutes += 5  # 5 min per fight
            # ELO change
            if random.random() > 0.4:
                p.stats.arena_elo += random.randint(10, 30)
            else:
                p.stats.arena_elo -= random.randint(5, 20)

    # Arena rewards
    p.economy.lt_earned += 5000
    p.economy.mt_earned += 100

    p.log(f"Dau Truong: ELO {p.stats.arena_elo}")
    return p


def simulate_quests(p: Player):
    """Phase 14: Nhiem Vu"""
    # Daily quests - 30 days
    for day in range(30):
        p.time.quest_minutes += 30  # 30 min per day
        p.economy.lt_earned += 500
        p.stats.quests_completed["daily"] += 5

    # Weekly quests - 12 weeks
    for week in range(12):
        p.time.quest_minutes += 60  # 1 hour per week
        p.economy.lt_earned += 2000
        p.stats.quests_completed["weekly"] += 3

    # Monthly quests
    for month in range(3):
        p.time.quest_minutes += 120  # 2 hours per month
        p.economy.lt_earned += 10000
        p.stats.quests_completed["monthly"] += 2

    # Achievement quests
    for achievement in range(50):
        p.time.quest_minutes += 10
        p.economy.lt_earned += 1000
        p.stats.quests_completed["achievement"] += 1

    # Main storyline
    for chapter in range(87):
        p.time.quest_minutes += 20
        p.economy.lt_earned += 3000
        p.stats.quests_completed["main"] += 1

    total_quests = sum(p.stats.quests_completed.values())
    p.log(f"Nhiem Vu: {total_quests} nhiem vu hoan thanh")
    return p


def simulate_shop_and_market(p: Player):
    """Phase 15: Cua Hang & Phuong Thi"""
    # Shop purchases
    p.time.active_minutes += 30  # 30 min browsing
    p.economy.lt_spent += 10000

    # Market transactions
    p.time.active_minutes += 60  # 1 hour trading
    p.economy.lt_spent += 5000
    p.economy.lt_earned += 8000

    p.log("Cua Hang & Phuong Thi")
    return p


def simulate_crafting(p: Player):
    """Phase 16: Che Tao & Luyen Dan"""
    # Crafting
    for i in range(30):
        p.time.active_minutes += 5  # 5 min per craft
        p.economy.lt_spent += 200

    # Alchemy
    for i in range(20):
        p.time.active_minutes += 8  # 8 min per alchemy
        p.economy.lt_spent += 300
        success = random.random() > 0.3  # 70% success
        if success:
            p.economy.lt_earned += 500

    p.log("Che Tao & Luyen Dan")
    return p


def simulate_mining(p: Player):
    """Phase 17: Ham Mo"""
    # Daily mining for 30 days
    for day in range(30):
        p.time.active_minutes += 10  # 10 min per day
        p.economy.lt_earned += random.randint(100, 500)
        p.economy.mt_earned += random.randint(1, 5)

    p.log("Ham Mo")
    return p


def simulate_giftcode(p: Player):
    """Phase 18: Doi Qua"""
    # 10 giftcodes
    for i in range(10):
        p.time.active_minutes += 1
        p.economy.lt_earned += 5000
        p.economy.mt_earned += 50

    p.log("Doi Qua: 10 giftcode")
    return p


def simulate_exchange(p: Player):
    """Phase 19: Quy Doi"""
    # Exchange MT to LT
    exchanges = 5  # 5 times per day
    for i in range(exchanges):
        mt_amount = min(100000, p.economy.mt_earned - p.economy.mt_spent)
        lt_received = mt_amount * MT_TO_LT
        p.economy.mt_spent += mt_amount
        p.economy.lt_earned += lt_received
        p.time.active_minutes += 1

    p.log("Quy Doi MT -> LT")
    return p


def simulate_clan(p: Player):
    """Phase 20: Mon Phai"""
    # Join and upgrade in clan
    for rank in range(1, CLAN_RANKS + 1):
        p.time.active_minutes += 10
        p.economy.lt_spent += rank * 500

    p.stats.clan_rank = CLAN_RANKS
    p.log(f"Mon Phai: Cap bac {p.stats.clan_rank}")
    return p


def simulate_friends(p: Player):
    """Phase 21: Hao Huu"""
    # Add 50 friends
    for i in range(50):
        p.time.active_minutes += 0.5  # 30 sec per friend
    p.log("Hao Huu: 50 ban be")
    return p


def simulate_chat(p: Player):
    """Phase 22: Lieu Thien"""
    # Chat activity
    for i in range(100):
        p.time.active_minutes += 0.5  # 30 sec per message
    p.log("Lieu Thien: 100 tin nhan")
    return p


def simulate_inventory(p: Player):
    """Phase 23: Tui Do"""
    # Manage inventory
    p.time.active_minutes += 60  # 1 hour total inventory management
    p.log("Tui Do: Quan ly 5 loai tui")
    return p


def simulate_rankings(p: Player):
    """Phase 24: Bang Xep Hang"""
    # Check rankings
    p.time.active_minutes += 10  # 10 min total
    p.log("Bang Xep Hang: Xem 5 loai")
    return p


# =========================================================================
# MAIN SIMULATION
# =========================================================================

def simulate_player(player_id: int) -> Player:
    """Simulate one player through ALL features"""
    name = f"Player_{player_id:04d}"
    p = Player(player_id, name)

    # Phase 1: Character Creation
    simulate_character_creation(p)

    # Phase 2: Leveling (biggest time sink)
    simulate_leveling(p, target_level=MAX_LEVEL)

    # Phase 3: Breakthroughs
    simulate_breakthroughs(p)

    # Phase 4: Spiritual Root
    simulate_spiritual_root(p)

    # Phase 5: Body Constitution
    simulate_body_constitution(p)

    # Phase 6: Blood Vein
    simulate_blood_vein(p)

    # Phase 7: Equipment (16 slots)
    simulate_equipment(p)

    # Phase 8: Martial Arts
    simulate_martial_arts(p)

    # Phase 9: Cultivation Techniques
    simulate_cultivation_techniques(p)

    # Phase 10: Heavenly Gifts (Talent Tree)
    simulate_thien_phu(p)

    # Phase 11: Tower
    simulate_tower(p)

    # Phase 12: Pet
    simulate_pet(p)

    # Phase 13: Arena
    simulate_arena(p)

    # Phase 14: Quests
    simulate_quests(p)

    # Phase 15: Shop & Market
    simulate_shop_and_market(p)

    # Phase 16: Crafting & Alchemy
    simulate_crafting(p)

    # Phase 17: Mining
    simulate_mining(p)

    # Phase 18: Giftcodes
    simulate_giftcode(p)

    # Phase 19: Exchange
    simulate_exchange(p)

    # Phase 20: Clan
    simulate_clan(p)

    # Phase 21: Friends
    simulate_friends(p)

    # Phase 22: Chat
    simulate_chat(p)

    # Phase 23: Inventory
    simulate_inventory(p)

    # Phase 24: Rankings
    simulate_rankings(p)

    # Calculate total time
    p.time.total_minutes = (
        p.time.afk_minutes +
        p.time.active_minutes +
        p.time.battle_minutes +
        p.time.quest_minutes +
        p.time.system_minutes
    )

    # Final stats calculation
    p.stats.atk += p.stats.level * 2
    p.stats.defn += p.stats.level * 1
    p.stats.hp += p.stats.level * 50

    return p


def run_simulation(num_players: int = 200):
    """Run simulation for N players"""
    print("=" * 80)
    print("  GIA LAP 200 NHAN VAT - TAM TIEN KY")
    print("  Trai nghiem MAX TAT CA TINH NANG")
    print("=" * 80)
    print()

    players = []
    for i in range(1, num_players + 1):
        p = simulate_player(i)
        players.append(p)
        if i % 50 == 0:
            print(f"  Da hoan thanh {i}/{num_players} nhan vat...")

    # =========================================================================
    # STATISTICS
    # =========================================================================

    print()
    print("=" * 80)
    print("  KET QUA GIA LAP - 200 NHAN VAT")
    print("=" * 80)

    # Time statistics
    total_times = [p.time.total_minutes for p in players]
    afk_times = [p.time.afk_minutes for p in players]
    active_times = [p.time.active_minutes for p in players]
    battle_times = [p.time.battle_minutes for p in players]
    quest_times = [p.time.quest_minutes for p in players]

    avg_total = sum(total_times) / len(total_times)
    min_total = min(total_times)
    max_total = max(total_times)

    print()
    print("1. THOI GIAN TRAI NGHIEM (MOI NHAN VAT)")
    print("-" * 60)
    print(f"  Thoi gian trung binh:  {avg_total:.0f} phut = {avg_total/60:.1f} gio = {avg_total/1440:.1f} ngay")
    print(f"  Thoi gian it nhat:     {min_total:.0f} phut = {min_total/60:.1f} gio")
    print(f"  Thoi gian nhieu nhat:  {max_total:.0f} phut = {max_total/60:.1f} gio")
    print()
    print("  Phan bo theo loai:")
    print(f"    AFK Tu Luyen:       {sum(afk_times)/len(afk_times):.0f} phut ({sum(afk_times)/sum(total_times)*100:.1f}%)")
    print(f"    Active Choi:        {sum(active_times)/len(active_times):.0f} phut ({sum(active_times)/sum(total_times)*100:.1f}%)")
    print(f"    Chien Dau:          {sum(battle_times)/len(battle_times):.0f} phut ({sum(battle_times)/sum(total_times)*100:.1f}%)")
    print(f"    Nhiem Vu:           {sum(quest_times)/len(quest_times):.0f} phut ({sum(quest_times)/sum(total_times)*100:.1f}%)")

    # Economy statistics
    lt_earned = [p.economy.lt_earned for p in players]
    lt_spent = [p.economy.lt_spent for p in players]
    mt_earned = [p.economy.mt_earned for p in players]
    mt_spent = [p.economy.mt_spent for p in players]

    print()
    print("2. TIEU THU TIEN TE (MOI NHAN VAT)")
    print("-" * 60)
    print(f"  Linh Thach (LT):")
    print(f"    Thu nhap TB:        {sum(lt_earned)/len(lt_earned):,.0f} LT")
    print(f"    Chi tieu TB:        {sum(lt_spent)/len(lt_spent):,.0f} LT")
    print(f"    Du thua TB:         {(sum(lt_earned)-sum(lt_spent))/len(lt_earned):,.0f} LT")
    print()
    print(f"  Ma Thach (MT):")
    print(f"    Thu nhap TB:        {sum(mt_earned)/len(mt_earned):,.0f} MT")
    print(f"    Chi tieu TB:        {sum(mt_spent)/len(mt_spent):,.0f} MT")
    print(f"    Du thua TB:         {(sum(mt_earned)-sum(mt_spent))/len(mt_earned):,.0f} MT")
    print()
    print(f"  Quy Doi (1 MT = 1,000 LT):")
    print(f"    Tong LT tu MT:      {(sum(mt_earned)-sum(mt_spent))/len(mt_earned)*MT_TO_LT:,.0f} LT/nguoi")

    # Character statistics
    print()
    print("3. THONG KE NHAN VAT (TRUNG BINH)")
    print("-" * 60)
    avg_atk = sum(p.stats.atk for p in players) / len(players)
    avg_def = sum(p.stats.defn for p in players) / len(players)
    avg_hp = sum(p.stats.hp for p in players) / len(players)
    avg_crit = sum(p.stats.crit for p in players) / len(players)
    avg_martial = sum(p.stats.martial_arts_count for p in players) / len(players)
    avg_tower = sum(p.stats.tower_floor for p in players) / len(players)

    print(f"  Cap do:               {MAX_LEVEL}")
    print(f"  Canh gioi:            {TOTAL_REALMS}")
    print(f"  ATK TB:               {avg_atk:,.0f}")
    print(f"  DEF TB:               {avg_def:,.0f}")
    print(f"  HP TB:                {avg_hp:,.0f}")
    print(f"  CRIT TB:              {avg_crit:,.1f}")
    print(f"  Vo Cong TB:           {avg_martial:.0f} ky nang")
    print(f"  Tang Thap TB:         {avg_tower:.0f}/{TOWER_FLOORS}")

    # Quest statistics
    total_daily = sum(p.stats.quests_completed["daily"] for p in players)
    total_weekly = sum(p.stats.quests_completed["weekly"] for p in players)
    total_monthly = sum(p.stats.quests_completed["monthly"] for p in players)
    total_achievement = sum(p.stats.quests_completed["achievement"] for p in players)
    total_main = sum(p.stats.quests_completed["main"] for p in players)

    print()
    print("4. THONG KE NHIEM VU (TONG 200 NHAN VAT)")
    print("-" * 60)
    print(f"  Nhiem Vu Ngay:        {total_daily:,}")
    print(f"  Nhiem Vu Tuan:        {total_weekly:,}")
    print(f"  Nhiem Vu Thang:       {total_monthly:,}")
    print(f"  Thanh Tuu:            {total_achievement:,}")
    print(f"  Chinh Tuyen:          {total_main:,}")
    print(f"  TONG:                 {total_daily+total_weekly+total_monthly+total_achievement+total_main:,}")

    # =========================================================================
    # TOTAL 200 PLAYERS
    # =========================================================================

    print()
    print("=" * 80)
    print("  TONG KET 200 NHAN VAT")
    print("=" * 80)
    print()
    print(f"  Tong thoi gian 200 NV:      {sum(total_times)/60:,.0f} gio = {sum(total_times)/1440:,.0f} ngay = {sum(total_times)/1440/30:,.1f} thang")
    print(f"  Tong LT tieu thu:           {sum(lt_spent):,.0f} LT")
    print(f"  Tong MT tieu thu:           {sum(mt_spent):,.0f} MT")
    print(f"  Tong LT da thu:             {sum(lt_earned):,.0f} LT")
    print(f"  Tong MT da thu:             {sum(mt_earned):,.0f} MT")
    print()
    print("  Neu choi dong thoi 200 NV:")
    print(f"    Thoi gian thuc te:        {max(total_times)/60:,.1f} gio = {max(total_times)/1440:,.1f} ngay")
    print(f"    (Vi moi NV can max {avg_total/1440:.1f} ngay)")
    print()

    # =========================================================================
    # DETAILED TIME BREAKDOWN
    # =========================================================================

    print()
    print("=" * 80)
    print("  CHI TIET THOI GIAN MOI TINH NANG (TRUNG BINH/NGUOI)")
    print("=" * 80)
    print()
    print(f"  {'Tinh Nang':<30} {'Thoi Gian':<15} {'Phan Tram':<10}")
    print(f"  {'-'*30} {'-'*15} {'-'*10}")

    features = [
        ("Tao nhan vat", 2),
        ("Leveling (1-4350)", afk_times[0] + active_times[0]),
        ("Dot pha", 87 * 10 * 2),  # 87 realms * 10 phases * 2 min
        ("Linh Can", 5 * 3 + 5 * 15),  # 5 tiers * 3 min + 5 bosses * 15 min
        ("The Chat", 5 * 5 + 6 * 10),  # 5 upgrades + 6 awakenings
        ("Huyet Mach", 5 * 5 + 5 * 8 + 10 * 10),  # upgrades + awakening + transform
        ("Trang Bi (16 o)", 16 * 3),
        ("Vo Cong (20 ky nang)", 20 * 50 * 0.5),  # 20 skills * avg 50 levels * 0.5 min
        ("Cong Phap", 10 * 5),
        ("Thien Phu", 30),
        ("Thap Yeu Thu", 150 * 10 / 60),  # 150 floors * 10 sec
        ("Pet", 50 * 0.1 + 30),  # leveling + gacha
        ("Dau Truong", 30 * 5 * 5),  # 30 days * 5 fights * 5 min
        ("Nhiem Vu", quest_times[0]),
        ("Cua Hang & Thi Truong", 90),
        ("Che Tao & Luyen Dan", 30 * 5 + 20 * 8),
        ("Ham Mo", 30 * 10),
        ("Doi Qua", 10 * 1),
        ("Quy Doi", 5 * 1),
        ("Mon Phai", 7 * 10),
        ("Hao Huu", 50 * 0.5),
        ("Lieu Thien", 100 * 0.5),
        ("Tui Do", 60),
        ("Bang Xep Hang", 10),
    ]

    total_feature_time = sum(t for _, t in features)
    for name, time in features:
        pct = time / total_feature_time * 100
        print(f"  {name:<30} {time:>8.0f} phut   {pct:>5.1f}%")

    print(f"  {'-'*30} {'-'*15} {'-'*10}")
    print(f"  {'TONG':<30} {total_feature_time:>8.0f} phut   100.0%")

    # =========================================================================
    # RECOMMENDATIONS
    # =========================================================================

    print()
    print("=" * 80)
    print("  DE XUAT CHOI HIU QUA")
    print("=" * 80)
    print()
    print("  1. AFK Tu Luyen (2-6h/ngay):")
    print("     - Dung cap MT (x3) khi co the")
    print("     - Tong thoi gian AFK: ~{:.0f} gio".format(sum(afk_times)/len(players)/60))
    print()
    print("  2. Nhiem Vu Ngay (30 phut/ngay):")
    print("     - Hoan thanh 5 NV ngay de nhan thuong")
    print("     - 30 ngay x 30 phut = 15 gio")
    print()
    print("  3. Dau Truong (25 phut/ngay):")
    print("     - 5 luot/ngay, moi luot ~5 phut")
    print("     - 30 ngay x 25 phut = 12.5 gio")
    print()
    print("  4. Ham Mo (10 phut/ngay):")
    print("     - Dao hang ngay de lay nguyen lieu")
    print("     - 30 ngay x 10 phut = 5 gio")
    print()
    print("  5. Tong thoi gian choi moi ngay:")
    print("     - AFK: 6h (tu dong)")
    print("     - Active: 1.5h (nhiem vu + dau truong + dao mo)")
    print("     - Tong: ~7.5h/ngay")
    print("     - De dat MAX: ~{:.0f} ngay".format(avg_total / (7.5 * 60)))

    print()
    print("=" * 80)
    print("  HOAN THANH GIA LAP!")
    print("=" * 80)


if __name__ == "__main__":
    run_simulation(200)
