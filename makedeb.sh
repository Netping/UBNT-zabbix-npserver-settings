#!/bin/bash
#need set version in next 3-th line
major=0
minor=0
path=1
apt-get install dpkg debconf debhelper lintian
cat <<EOF > ./UBNT-ZABBIX-NPSERVERSETTINGS/DEBIAN/control
Package: ubnt-zabbix-npser-set
Version: $major.$minor-$path
Maintainer: vv.lisyak@gmail.com
Architecture: all
Section: misc
Description: NetPing
 First deb build.
 Try nomber one.
EOF
fakeroot dpkg-deb --build UBNT*
mv ./UBNT-ZABBIX-NPSERVERSETTINGS.deb UBNT-ZABBIX-NPSERVERSETTINGS_$major.$minor-$path.deb
exit 0
