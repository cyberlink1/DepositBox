#!/bin/sh
cryptdisks_start luksFormat
mount /dev/mapper/luksFormat /var/www/html/DepositBox/public/uploads/
