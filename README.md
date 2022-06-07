# DepositBox
Darkweb secure hidden file storage.

Im building this on a Raspberry Pi 3 with 1G of ram.
The OS installed is Ubuntu 22.04 LTS https://ubuntu.com/download/raspberry-pi/thank-you?version=22.04&architecture=server-arm64+raspi

The idea is to put together a small, ultra secure, file storage unit that can be placed anywhere and allow you to securly store files at a remote location. The Raspberry pi is small and uses very little power. You could ask a friend to put it at their house, or install it at a summer home, if you have a self storage unit with free wifi you could even put it in the unit. Where you put it is up to you, all you need is an internet connection and power.

At the moment, authentication is handled with tor Client keys. It runs on the Tor network (AKA DarkWeb) and only allows connections from registered keys.
You can upload and register a gpg key with the server <filename>.gpgkey To remove the gpg key you would have to go in via command line.
After the key is uploaded, any further uploads will be gpg encrypted before being stored on the local disk.
The file storage location will eventually be an encrypted usb drive.

This is something Ive been playing with and this is just a draft copy of the readme so more to come shortly.
  
  
  TODO
  * Finish install.sh
  * Build php based rsync config to allow secure automated rsync between different units.
  * Build hardening script to harden the system.
  
