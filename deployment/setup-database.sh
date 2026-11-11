#!/usr/bin/env bash
# Fresh installation only. Import the empty schema and generate a local DB secret.
set -euo pipefail
test "$(id -u)" = 0
project="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
cd "$project"
if mysql -Nse "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='restaurantdb'" | grep -q restaurantdb; then
  echo 'Refusing to overwrite an existing restaurantdb database.' >&2
  exit 1
fi
if test -e /etc/restaurant-project/database.php; then
  echo 'Database configuration already exists; preserve it before a fresh installation.' >&2
  exit 1
fi
mysql -e 'CREATE DATABASE restaurantdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
mysql --default-character-set=utf8mb4 restaurantdb < database/schema.sql
mysql --default-character-set=utf8mb4 restaurantdb < deployment/migrations/001-integrity.sql
mysql restaurantdb -e 'CREATE TABLE schema_migrations (version VARCHAR(100) PRIMARY KEY, applied_at DATETIME NOT NULL); INSERT INTO schema_migrations VALUES ("001-integrity",NOW())'
install -d -o root -g www-data -m 750 /etc/restaurant-project
python3 - <<'PY'
import secrets, subprocess
from pathlib import Path
password = secrets.token_hex(32)
subprocess.run(['mysql'], input=f"CREATE USER 'restaurant_app'@'localhost' IDENTIFIED BY '{password}'; GRANT SELECT,INSERT,UPDATE,DELETE ON restaurantdb.* TO 'restaurant_app'@'localhost';", text=True, check=True)
path = Path('/etc/restaurant-project/database.php')
with path.open('x') as output:
    output.write("<?php\nreturn ['host'=>'localhost','user'=>'restaurant_app','password'=>'" + password + "','database'=>'restaurantdb'];\n")
PY
chown root:www-data /etc/restaurant-project/database.php
chmod 640 /etc/restaurant-project/database.php
echo 'Empty database ready. Run: sudo php bin/create-manager.php'
