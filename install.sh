!#/bin/bash
echo "not working yet!"
exit
sudo apt-get install lighttpd php-cgi git pip tor php-pear php8.1-dev libgpgme-dev -y
sudo pip install pynacl
sudo pecl channel-update pecl.php.net
sudo pecl install gnupg
sudo lighty-enable-mod fastcgi 
sudo lighty-enable-mod fastcgi-php
sudo echo "extension=gnupg.so" >> /etc/php/8.1/cgi/conf.d/20-gnupg.ini
sudo service lighttpd force-reload
sudo cp DepositBox /var/www/html
sudo mkdir /var/www/.gpg
cd /var/www/html
sudo chown -R www-data:www-data DepositBox

#set lighttpd to service.bind = 127.0.0.1

#edit torrc to turn on hidden service for 80 and 22
#start tor will create /var/lib/hidden_service/authorized_clients
#create file in above dir <name>.auth (contains descriptor:x25519:<public-key>

#On Client Side
#ClientOnionAuthDir /var/lib/tor/onion_auth to /etc/tor/torrc
#create directory /var/lib/tor/onion_auth
#create file in directory <name>.auth_private
systemctl enable tor
