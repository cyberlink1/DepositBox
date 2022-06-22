#!/bin/bash
clear
USBDISK=`ls -l /dev/disk/by-path/*usb*  | grep -v "part" | awk '{print $NF}'|  awk -F "/" '{print $NF}' | sort`
if [ ! -z "$USBDISK" ]
	then
		echo -n " Would you like to set up an encrypted usb stick for storage? [y/n]"
		read SETUP
		if [[ ! -z "$SETUP" ]] && [[ $SETUP == "y" ]]
                   then
			   echo " USB Disks "
  		           echo "---------------------------"
		           A=0;
		           for I in $USBDISK
		           do
                              DISK=`lsblk  -n -d -o NAME,MODEL,VENDOR,SIZE,RM /dev/$I`
  			      echo "$A) $DISK"
			      let A=$A+1
		           done	
		    echo -n "Select your disk :"
		    read SELECTION
		    echo "You selected $SELECTION"
		    arr=($USBDISK)
		    echo "Selected Device /dev/${arr[$SELECTION]}"
		    echo -n "Enter a Password: "
		    read -s password
		    echo ""
		    echo -n "Enter your Password again:"
		    read -s password1
		    echo ""
		    if [ $password != $password1 ]
		    then
			    echo "Passwords do not match!"
   		            exit 2
		    fi

            parted --script --align optimal -- /dev/$USBDISK mklabel gpt mkpart primary 2048s 100%
            DISKPART=`fdisk -l /dev/sda | tail -1 | cut -d" " -f1`
            echo $password | cryptsetup -q luksFormat $DISKPART
            echo $password | cryptsetup luksOpen $DISKPART luksFormat
            mkfs.ext4 /dev/mapper/luksFormat
	    mount /dev/mapper/luksFormat /mnt
	    chown -R www-data.www-data /mnt
	    umount /mnt
            cryptsetup luksClose luksFormat
            echo "luksFormat $DISKPART none  noauto" >> /etc/crypttab
            echo "/dev/mapper/luksFormat   /var/www/html/DepositBox/public/uploads/  ext4  defaults,noauto,uid=33,gid=33,umask=077    0 0" >> /etc/fstab
	    touch /var/www/html/DepositBox/public/uploads/.missing-disk
 
	    fi
    else
	    exit 1
   fi
   exit 0
