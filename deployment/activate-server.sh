#!/usr/bin/env bash
set -euo pipefail
test "$(id -u)" = 0
project="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
server_name="${RESTAURANT_SERVER_NAME:-localhost}"
test "$project" != / && test -f "$project/index.php"
if [[ ! "$server_name" =~ ^[a-zA-Z0-9.-]+$ ]]; then
  echo 'RESTAURANT_SERVER_NAME must be a hostname or IPv4 address.' >&2
  exit 1
fi
chown -R root:www-data "$project"
find "$project" -type d -exec chmod 750 {} +
find "$project" -type f -exec chmod 640 {} +
python3 - "$project" "$server_name" <<'PY'
from pathlib import Path
import re, sys
project, server_name = sys.argv[1:]
template = (Path(project) / 'deployment/apache-restaurant.conf').read_text()
rendered = template.replace('__PROJECT_ROOT__', project).replace('__PROJECT_REGEX__', re.escape(project)).replace('__SERVER_NAME__', server_name)
Path('/etc/apache2/sites-available/restaurant.conf').write_text(rendered)
Path('/etc/apache2/conf-available/restaurant-servername.conf').write_text('ServerName ' + server_name + '\n')
PY
a2dissite 000-default || true
a2ensite restaurant
a2enconf restaurant-servername
apache2ctl configtest
systemctl enable apache2 mysql
systemctl restart apache2
systemctl is-active apache2 mysql
