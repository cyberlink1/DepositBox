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
sudo sed -i '/^server.port.*/i server.bind                 = "127.0.0.1"' /etc/lighttpd/lighttpd.conf
sudo sed -i '/^#HiddenServicePort\ 22\ 127.0.0.1:22/a \\nHiddenServiceDir /var/lib/tor/store_service/\nHiddenServicePort 80 127.0.0.1:80\nHiddenServicePort 22 127.0.0.1:22' /etc/tor/torrc
sudo systemctl stop lighttpd
sudo systemctl start lighttpd
sudo systemctl stop tor
sudo systemctl start tor
#start tor will create /var/lib/hidden_service/authorized_clients

#On Client Side
#ClientOnionAuthDir /var/lib/tor/onion_auth to /etc/tor/torrc
#create directory /var/lib/tor/onion_auth

systemctl enable tor
