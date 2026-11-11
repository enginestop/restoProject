#!/usr/bin/env bash
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive
test "$(id -u)" = 0
test -f /etc/os-release
. /etc/os-release
test "$ID" = ubuntu && test "$VERSION_ID" = 24.04
mkdir -p /var/log/restaurant-project
dpkg-query -W > /var/log/restaurant-project/packages-before.txt
apt-get update
apt-get install -y apache2 libapache2-mod-php8.3 php8.3-cli php8.3-mysql php8.3-mbstring php8.3-gd mysql-server acl poppler-utils python3-requests
systemctl enable mysql apache2
systemctl start mysql
dpkg-query -W apache2 php8.3-cli libapache2-mod-php8.3 php8.3-mysql php8.3-mbstring php8.3-gd mysql-server > /var/log/restaurant-project/versions.txt
cat /var/log/restaurant-project/versions.txt
