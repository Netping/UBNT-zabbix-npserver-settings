cd /tmp
rsync -vr /tmp/isogit/iso/ /tmp/iso/netping/zabbix/
rsync -vr /tmp/isogit/iso/config/prenps.sh /tmp/iso/netping/zabbix/config/
rm -rf ./isogit
