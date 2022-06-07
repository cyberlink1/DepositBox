<?php

$options = [
  'app_name'           => "Deposit Box",

  'base_path'          => "/var/www/html/DepositBox/",
  'base_uri'           => "/DepositBox",
  'max_space'          => 90, // in percent of the partition usage

  #'base_uploads'       => "/var/spool/piratebox/public/uploads/",
  #'base_chat'          => "/var/spool/piratebox/public/chat/",

  'allow_renaming'     => true,
  'allow_deleting'     => true,
  'allow_newfolders'   => true,

  'enable_chat'        => false,
  'default_pseudo'     => "anonymous",

  'time_format'        => "d/m/y H:i",
  'fancyurls'          => true,
];
?>
