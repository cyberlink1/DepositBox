# DepositBox
Darkweb secure hidden file storage.

Im building this on a Raspberry Pi 3 with 1G of ram.
The OS installed is Ubuntu 22.04 LTS https://ubuntu.com/download/raspberry-pi/thank-you?version=22.04&architecture=server-arm64+raspi

The idea is to put together a small, ultra secure, file storage unit that can be placed anywhere and allow you to securly store files at a remote location. The Raspberry pi is small and uses very little power. You could ask a friend to put it at their house, or install it at a summer home, if you have a self storage unit with free wifi you could even put it in the unit. Where you put it is up to you, all you need is an internet connection and power.

At the moment, authentication is handled with tor Client keys. It runs on the Tor network (AKA DarkWeb) and only allows connections from registered keys.
You can upload and register a gpg key with the server \<filename\>.gpgkey To remove the gpg key you would have to go in via command line.
After the key is uploaded, any further uploads will be gpg encrypted before being stored on the local disk.
The file storage location will eventually be an encrypted usb drive.

This is something Ive been playing with and this is just a draft copy of the readme so more to come shortly.
  
  
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

You will then need to run the tor-client-key.py This will create a set of auth keys, it puts the public key in the correct postions
on the system. It creates the <name>.auth_private key in the directory you run the scritp in. You will need to copy the private key to your client and install it.
  
Some things to note:
  * to activate the gpg encryption you need to upload your gpg public key. The file must end with .gpgkey The server will reconize that file name and place the key in the /var/www/.gpg directory activating the auto-encrypt functions.
  * Once auto-encrypt is on, any file uploaded will be gpg encrypted with your public key. If you upload a file that ends with .gpg the system will assume it is already encrypted and not attempt to encrypt it.
  * The only way to remove your GPG key is to ssh into the host and remove it mannually. It is located in the /var/www/.gpg directory named public.key. Once removed, the system will nolonger encrypt uploads.
  * You have to remove the old key BEFORE uploading a new one, it will not overwrite the key.
  * The address to the site will be <address>.onion/DepositBox/ Just know that without the auth key, no one will be able to connect to your site.
