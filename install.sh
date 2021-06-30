#/bin/sh

clear
echo "Installing BVCP into your FreeBSD Installation within seconds ..."
echo ""
echo "Press [CTRL] + [C] to Abort !"
sleep 5

mkdir -p /usr/local/etc/rc.d
mkdir -p /var/lib/nPulse/BVCP
cp -R ./API/ /var/lib/nPulse/BVCP/API/
cp -R ./Backend/ /var/lib/nPulse/BVCP/Backend/
cp -R ./Frontend/ /var/lib/nPulse/BVCP/Frontend/
cp -R ./Helper/ /var/lib/nPulse/BVCP/Helper/

cp ./utils/bvcp-backend /usr/local/etc/rc.d/bvcp-backend
cp ./utils/bvcp-frontend /usr/local/etc/rc.d/bvcp-frontend
cp ./utils/bvcp-helper /usr/local/etc/rc.d/bvcp-helper
sysrc bvcp_enable="YES"

cd /var/lib/nPulse/BVCP/Helper
clang -o vmctl vmctl.c

cd ../Backend/
./vmm setup

service bvcp-backend restart
service bvcp-helper restart
service bvcp-frontend restart

clear
/var/lib/nPulse/BVCP/Backend/vmm reset_password
echo "Installation Finished!"
echo "Navigate: https://[your-ip]:8086"
sleep 10
