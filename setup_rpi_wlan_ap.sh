#!/bin/bash

set -e

## This script will enable a WLAN AP hotspot on a Raspberry PI
## for access to Photobooth Gallery and Admin interface
## Inspired by https://github.com/idev1/rpihotspot

## no checks for root / device type - assume this script will be integrated in the RaspPi installer for Photobooth

## DEFAULTS

apIP="172.16.128.1"
apMaskBits="17"
apDHCPRange="172.16.129.0, 172.16.254.254"
apChannel="13"
apSSID="photobooth"
apPassword=""

dependencies="dhcpcd dnsmasq hostapd iptables"

#fileInterfaces="/etc/network/interfaces"
fileInterfaces="./interfaces"
cp $fileInterfaces.test $fileInterfaces # debugging only

fileDHCPCDConf="./dhcpcd.conf"
cp $fileDHCPCDConf.test $fileDHCPCDConf # debugging only

echo -e "##\n## Enable local WLAN Access Point for Photbooth access\n##\n"

##
## CHECK if systemd-networkd is used on this system, which currently is not supported in this script
##
if  $(systemctl is-active --quiet systemd-networkd) ; then
    echo "> ERROR: Looks like systemd-networkd is used on this host, currently not supported"
    exit
fi

##
## CHECK WLAN Interface to use
##
echo "## WLAN INTERFACE"
wlanInterface=`ifconfig -a -s|grep wlan| awk '{print $1}'`
wlanIfNumber=`echo wlanInterface|wc -l`

if [ $wlanIfNumber -eq 0 ]; then
    echo "> ERROR: no wlan interface found - exiting"
    exit
fi


if [ $wlanIfNumber -gt 1 ] ;then
    echo ">" $wlanIfNumber "WLAN interfaces found - please chose"
    echo ">" $wlanInterface
    read -e -p "> Select interface: " -i "wlan0"  wlanInterface
    
    if ! [[ `ifconfig -s -a| grep $wlanInterface|awk '{print $1}'` == "$wlanInterface" ]] ; then
	echo "> ERROR: wrong interface name - exiting"
	exit
    fi
    
fi


echo -e "> Using interface <"$wlanInterface">\n"

##
## CHECK if we are connected through this interface
##

connectionIP=`who -m| awk '{print $5}'|sed 's/[()]//g'`

if [[ $connectionIP =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}+\.[0-9]{1,3}+$ ]]; then    
    connectionInterface=`ip route get $connectionIP|awk '{print $3}'`

    if [[ $connectionInterface == $wlanInterface ]]; then
	echo "> WARNING: Looks like you are connected from $connectionIP through interface <$connectionInterface>"
	echo "> This script will make changes to the configuration of network interface <$wlanInterface>"
	echo -e "> If you proceed, likely you will lose connection and not being able to connect back.\n"
	
	read -e -p "> Do you want to continue [y/n]: " -i n answer
	
	if ! [[ $answer == 'y' ]]; then
	    echo "> Aborting script - good choice."
	    exit
	fi
    fi
fi

##
## CHECK if wlan interface is disabled by rfkill
## FIXME this logic may not work with multiple WLAN interfaces, not sure about rfkill enummeration of interfaces vs ifconfig
##

wlanIfId=`echo $wlanInterface|sed 's/[a-z]*//'`
output=( `rfkill --raw|grep "$wlanIfId wlan"` )

if ! [[ ${output[4]} == "unblocked" ]]; then
    echo "> ERROR: WLAN interface <$wlanInterface> hard blocked by RFKILL - aborting. Check for WLAN hardware switch on your device."
    exit
elif ! [[ ${output[3]} == "unblocked" ]]; then
    echo "> WLAN interface <$wlanInterface> soft blocked by RFKILL ...  unblocking"
    rfkill unblock wlanIfId
fi

##
## CHECK  /etc/network/interfaces setup
##
if $(grep "iface.*$wlanInterface" $fileInterfaces >/dev/null); then

    # static setup detected - remove configuration lines
    cp $fileInterfaces $fileInterfaces.tmp
    
    sed -i -e "/iface.$wlanInterface/,/iface/{/^$/!{/auto/!{/allow/!{/iface/!{/mapping/!{/source/!{/no-/!d}}}}}}}" -e "/$wlanInterface/d" $fileInterfaces.tmp

    echo "> WARNING: Detected configuration setting in file <$fileInterfaces> for interface <$wlanInterface>"
    echo "> For use with DHCPCD, this file should not have a configuration entry for <$wlanInterface>."
    echo -e "> If you proceed, a backup will be created and the following lines be removed from the original system file\n--------------"

    diff $fileInterfaces.tmp $fileInterfaces| sed -n '1!p'
    
    echo -e "--------------"
    read -e -p "> Do you want to continue [y/n]: " -i y answer
	
    if ! [[ $answer == 'y' ]]; then
	echo "> Aborting script - no system changes done."
	rm $fileInterfaces.tmp
	exit
    fi

    cp $fileInterfaces $fileInterfaces.backup
    mv $fileInterfaces.tmp $fileInterfaces
    echo
fi

##
## INSTALL dependencies
##
function installDependency
{
    echo -n "> Require package $1 ... "

    if ! dpkg -s $1 2>&1 | grep  -q '^Status:.* installed'; then
	echo "installing"
	# apt install -y $1 || echo "> ERROR: Package $1 installation failed - exiting" && exit
    else
	echo "already installed"
    fi
}

echo "## DEPENDENCIES"
for dep in $dependencies
do
    installDependency $dep
done
echo

##
## CONFIGURE IP addresses on wlan interface
## https://www.elektronik-kompendium.de/sites/raspberry-pi/1912151.htm
##

echo "## DHCPCD Configuration"

## CONFIGURE dhcpcd
if [ ! -f $fileDHCPCDConf ]; then
    echo "> ERROR:  File <$fileDHCPCDConf> not found - aborting"
    echo "> Please check whether dhcpcd package installation was successful"
    exit
fi

cp $fileDHCPCDConf $fileDHCPCDConf.backup

if $(grep "^[^#]*interface $wlanInterface" $fileDHCPCDConf >/dev/null); then
    # static setup detected - remove configuration
    cp $fileDHCPCDConf $fileDHCPCDConf.tmp
    sed -i -E -e "/[^#]*interface $wlanInterface/,/interface|profile|^#+|^$/d" $fileDHCPCDConf.tmp

    echo "> WARNING: Detected interface configuration in file <$fileDHCPCDConf> for interface <$wlanInterface>"
    echo -e "> If you proceed, a backup will be created and the following lines be removed from the configuration file\n--------------"

    diff $fileDHCPCDConf.tmp $fileDHCPCDConf| sed -n '1!p'
    
    echo -e "--------------\n"
    read -e -p "> Do you want to continue [y/n]: " -i y answer
	
    if ! [[ $answer == 'y' ]]; then
	echo "> Aborting script - please manually fix file <$fileDHCPCDConf> and re-run setup script"
	rm $fileDHCPCDConf.tmp
	rm $fileDHCPCDConf.backup
	exit
    fi

    mv $fileDHCPCDConf.tmp $fileDHCPCDConf
fi

# add dhcpcd wlan interface configuration
cat >> $fileDHCPCDConf <<EOF

    # --- Photobooth WLAN AP IF config
interface $wlanInterface
  static ip_address=$apIP/$apMaskBits
  static routers=$apIP
  static domain_name_servers=$apIP
  ipv4only
  noipv6rs
  nogateway
  nohook wpa_supplicant
# --- Photobooth WLAN AP IF config

EOF
echo

##
## CONFIGURE hostapd
##

## CONFIGURE dnsmasq

## CONFIGURE iptables

## start services
