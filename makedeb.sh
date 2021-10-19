#!/bin/bash
#need set version in next 3-th line
version=1.1
apt-get install dpkg debconf debhelper lintian
cat <<EOF > ./UBNT-ZABBIX-NPSERVERSETTINGS/DEBIAN/control
Package: ubnt-zabbix-npser-set
Version: $version
Maintainer: vv.lisyak@gmail.com
Architecture: all
Section: misc
Description: NetPing
 First deb build.
 Try nomber one.
EOF
fakeroot dpkg-deb --build UBNT*
mv ./UBNT-ZABBIX-NPSERVERSETTINGS.deb ubnt-zabbix-npserversetings_$version.deb
exit 0
