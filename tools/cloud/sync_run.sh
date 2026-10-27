#!/bin/bash
# Đồng bộ code sang bản chạy thử /tmp/xxjrun (PHP 8.x) + vá parse_str 1 tham số (CHỈ ở bản sao, game thật là PHP 5.6)
REPO=$(cd "$(dirname "$0")/../.." && pwd)
R=/tmp/xxjrun
mkdir -p $R
rsync -a --exclude .git --exclude images --exclude docs "$REPO/" $R/
ln -sfn "$REPO/images" $R/images
sed -i -E 's/parse_str\((\$[A-Za-z_]+)\);/parse_str(\1, $__ps); extract($__ps);/' $R/game.php
