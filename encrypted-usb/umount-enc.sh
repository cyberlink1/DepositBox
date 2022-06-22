#!/bin/sh
umount /var/www/html/DepositBox/public/uploads/
cryptdisks_stop luksFormat
