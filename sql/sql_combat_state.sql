-- Trạng thái trận PVE cho Thế Võ (lượt, hồi chiêu Tuyệt Kỹ / Tuyệt Chiêu).
-- class/function/combat.php tự tạo bảng nếu chưa có; file này dùng khi user DB không có quyền CREATE.
CREATE TABLE IF NOT EXISTS player_combat_state (
  sid     VARCHAR(32) NOT NULL PRIMARY KEY,
  gid     INT NOT NULL DEFAULT 0,
  turn    INT NOT NULL DEFAULT 0,
  cd2     INT NOT NULL DEFAULT 0,
  cd3     INT NOT NULL DEFAULT 0,
  updated INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
