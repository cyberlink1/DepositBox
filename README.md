# DepositBox
Darkweb secure hidden file storage.

Im building this on a Raspberry Pi 3 with 1G of ram.
The OS installed is Ubuntu 22.04 LTS https://ubuntu.com/download/raspberry-pi/thank-you?version=22.04&architecture=server-arm64+raspi

The idea is to put together a small, ultra secure, file storage unit that can be placed anywhere and allow you to securly store files at a remote location. The Raspberry pi is small and uses very little power. You could ask a friend to put it at their house, or install it at a summer home, if you have a self storage unit with free wifi you could even put it in the unit. Where you put it is up to you, all you need is an internet connection and power.

  ## TODO
  * Build php based rsync config to allow secure automated rsync between different units.
  * Build hardening script to harden the system.
  
# How to use

* Download a copy of ubuntu 22.04 https://ubuntu.com/download/raspberry-pi/thank-you?version=22.04&architecture=server-arm64+raspi
* Write it to an SD card and boot your Raspberry Pi on it.
* Do a git clone of this repo
* chmod 755 install.sh
* run install.sh

The install script will update your image, install all the needed packages, configure lighthttpd and Tor, then start them up. 
When it ends, it will give you some information as well as the new onion address of the host. It will also cover how to set the tor auth keys.

You will then need to run the tor-client-key.py. This will need to run as admin so sudo the command.

sudo ./tor-client-key.py

* This will create a set of auth keys. 
* It will first display all active Tor Services and let you select the one to create the key pair for.
* It will create the \<name\>.auth key in the select tor service
* It creates the \<name\>.auth_private key in the directory you run the script in. 
* You will need to copy the private key to your client and install it.
* Once you copy the private key to your client and remove it from the server, you need to reboot the PI or restart the Tor service.
  
Some things to note:
  * to activate the gpg encryption you need to upload your gpg public key. The file must end with .gpgkey The server will reconize that file name and place the key in the /var/www/.gpg directory activating the auto-encrypt functions.
  * Once auto-encrypt is on, any file uploaded will be gpg encrypted with your public key. If you upload a file that ends with .gpg the system will assume it is already encrypted and not attempt to encrypt it.
  * The only way to remove your GPG key is to ssh into the host and remove it mannually. It is located in the /var/www/.gpg directory named public.key. Once removed, the system will nolonger encrypt uploads.
  * You have to remove the old key BEFORE uploading a new one, it will not overwrite the key.
  * The address to the site will be \<address\>.onion/DepositBox/ Just know that without the auth key installed, you will not be able to connect to your site.
  * If you want to activate the encrypted USB storage, cd to the encrypted-usb directory and run "sudo make-enc.sh"
    * after this is set up you can use "sudo ./mount-enc.sh" to mount the usb disk and "sudo ./umount-enc.sh" to unmount the disk
  * This unit activates ssh on the tor network. 
    * to ssh over tor you will need your auth tor keys set up
    * It is advized that you put your ssh public key on the server as well
    * It is advized that you change the /etc/ssh/sshd_config file entry "PasswordAuthentication" to "no" (must have set up your public key first)
 * to ssh over tor use the command "torify ssh -l <username> <address>.onion"
