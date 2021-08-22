if ls /cdrom/netping/zabbix/ | grep NpServerSettings
then
cp -r /cdrom/netping/zabbix/NpServerSettings /target/usr/share/zabbix/modules/
else echo "no NpServerSettings"
fi
