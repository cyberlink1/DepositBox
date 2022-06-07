<?php

/* php-piratebox
 * Copyright (C) 2015 Julien Vaubourg <julien@vaubourg.com>
 * Contribute at https://github.com/jvaubourg/php-piratebox
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('functions.php');

dispatch('/', function() {
  set('files', getFiles('/'));
  set('tab', 'files');
  set('locked', true);
  set('cdir', '/');

  header('Content-Type: text/html; charset=utf-8');

  return render('home.html.php');
});

dispatch('/get', function() {
  $dir = sanitizeDirpath(urldecode($_GET['dir']));
  $ajax = isset($_GET['ajax']) && $_GET['ajax'];

  if($ajax) {
    header('Content-Type: text/plain; charset=utf-8');
  } else {
    header('Content-Type: text/html; charset=utf-8');
  }

  if(empty($dir) || !is_dir(UPLOADS_PATH.$dir)) {
    if($ajax) {
      exit('ERR:'._("Invalid destination.".UPLOADS_PATH.$dir));

    } else {
      header('Location: '.ROOT_DIR);
      exit();
    }
  }

  if($ajax) {
    return getFiles($dir, true);
  }

  $perms = fileperms(UPLOADS_PATH.$dir);
  $locked = !($perms & 0x0080) || $dir == '/';

  set('files', getFiles($dir));
  set('tab', 'files');
  set('locked', $locked);
  set('cdir', $dir);

  return render('home.html.php');
});

dispatch_post('/rename', function() {
  $cdir = sanitizeDirpath($_POST['cdir']);
  $oldName = sanitizeFilename($_POST['oldName']);
  $newName = sanitizeFilename($_POST['newName']);

  $oldFilePath = UPLOADS_PATH."$cdir/$oldName";
  $newFilePath = UPLOADS_PATH."$cdir/$newName";

  header('Content-Type: text/plain; charset=utf-8');

  if(!option('allow_renaming')) {
    exit('ERR:'._("Unauthorized."));
  }

  if(empty($oldName) || empty($newName)) {
    exit('ERR:'._("Invalid filename."));
  }

  if(!file_exists($oldFilePath)) {
    exit('ERR:'._("File not found."));
  }

  if(file_exists($newFilePath)) {
    exit('ERR:'._("File already exists."));
  }

  if(!rename($oldFilePath, $newFilePath)) {
    exit('ERR:'._("Renaming failed."));
  }

  if(is_dir($newFilePath)) {
    $folder = array(
      'name' => $newName,
      'dir'  => "$cdir/$newName",
    );

    set('folder', $folder);
    set('locked', false);
    set('newfolder', true);

    echo partial('_folder.html.php');

  } else {
    $file = array(
      'filename'  => UPLOADS_DIR.str_replace('%2F', '/', rawurlencode($cdir)).'/'.rawurlencode($newName),
      'name'      => $newName,
      'shortname' => getShortname($newName),
      'img'       => getExtensionImage($newFilePath),
      'size'      => fileSizeConvert(filesize($newFilePath)),
      'date'      => dateConvert(filemtime($newFilePath)),
    );

    set('file', $file);
    set('locked', false);
    set('newfile', true);

    echo partial('_file.html.php');
  }
});

dispatch_post('/delete', function() {
  $cdir = sanitizeDirpath($_POST['cdir']);
  $name = sanitizeFilename($_POST['name']);

  $filePath = UPLOADS_PATH."$cdir/$name";

  header('Content-Type: text/plain; charset=utf-8');

  if(!option('allow_deleting')) {
    exit('ERR:'._("Unauthorized."));
  }

  if(!file_exists($filePath)) {
    exit('ERR:'._("File not found."));
  }

  if(is_dir($filePath)) {
    $files = scandir($filePath);

    if(!$files) {
      exit('ERR:'._("Cannot read this directory."));
    }

    if(count($files) > 2) {
      exit('ERR:'._("Not empty directory."));
    }

    if(!rmdir($filePath)) {
      exit('ERR:'._("Cannot delete this directory."));
    }

  } else {
    if(!unlink($filePath)) {
      exit('ERR:'._("Cannot delete this file."));
    }
  }
});

dispatch_post('/upload', function() {
  $gpgkeyfile="/var/www/.gpg/public.key";
  $encrypt_file=false;
  if (file_exists($gpgkeyfile)){
   	$encrypt_file=true;
	putenv("GNUPGHOME=/var/www/.gpg");
  }
  $cdir = sanitizeDirpath(urldecode($_GET['cdir']));
  $name = sanitizeFilename(urldecode(@$_SERVER['HTTP_X_FILE_NAME']));
  $dirpath = UPLOADS_PATH."$cdir";
  if(str_ends_with($name, ".gpgkey"))
  {
	  $filename = "/var/www/.gpg/public.key";
	  $gpgkey = True;
  }elseif ((str_ends_with($name, ".gpg")) || (!$encrypt_file)){
          $filename = "$dirpath/$name";
  }else{ 
	  $filename = "/var/www/tmp/$name";
	  $needsenc = true;
  }

  header('Content-Type: text/plain; charset=utf-8');

  if(empty($cdir) || !is_dir($dirpath)) {
    exit('ERR:'._("Invalid directory."));
  }

  if(!hasAvailableSpace()) {
    exit('ERR:'._("The file system has reached the maximum limit of space."));
  }

  if(empty($name)) {
    exit('ERR:'._("Invalid filename."));
  }

  if(file_exists($filename)) {
    exit('ERR:'._("File already exists."));
  }

  $src = fopen('php://input', 'r');
  
  if(!$src ) {
    exit('ERR:'._("Uploading failed."));
  }

  if ($needsenc){
    $publicKey = file_get_contents(getenv('GNUPGHOME') . '/public.key');
    $gpg = new gnupg();
    $gpg->seterrormode(gnupg::ERROR_EXCEPTION);
    $info = $gpg->import($publicKey);
    $gpg->addencryptkey($info['fingerprint']);
    $data1=stream_get_contents($src);
    $enc = $gpg->encrypt($data1);
    file_put_contents("$dirpath/$name.gpg", $enc);
    $name .=".gpg"; 
  }else{
    $dst = fopen($filename, 'w');
    stream_copy_to_stream($src, $dst);
  }

  $file = array(
    'filename'  => UPLOADS_DIR.str_replace('%2F', '/', rawurlencode($cdir)).'/'.rawurlencode($name),
    'name'      => $name,
    'shortname' => getShortname($name),
    'img'       => getExtensionImage($filename),
    'size'      => fileSizeConvert(filesize($filename)),
    'date'      => dateConvert(filemtime($filename)),
  );

  set('file', $file);
  set('locked', false);
  set('newfile', true);
  if (!$gpgkey)
  {
   echo partial('_file.html.php');
  }
 $gpgkey=True;


});

dispatch_post('/createfolder', function() {
  $name = sanitizeDirname($_POST['name']);
  $cdir = sanitizeDirpath($_POST['cdir']);

  $dirpath = UPLOADS_PATH."$cdir";
  $filename = "$dirpath/$name";

  header('Content-Type: text/plain; charset=utf-8');

  if(!option('allow_newfolders')) {
    exit('ERR:'._("Unauthorized."));
  }

  if(!hasAvailableSpace()) {
    exit('ERR:'._("The file system has reached the maximum limit of space."));
  }

  if(empty($cdir) || !is_dir($dirpath)) {
    exit('ERR:'._("Invalid directory."));
  }

  if(empty($name)) {
    exit('ERR:'._("Invalid directory name."));
  }

  if(file_exists($filename)) {
    exit('ERR:'._("File already exists."));
  }

  if(!mkdir(UPLOADS_PATH."$cdir/$name")) {
    exit('ERR:'._("Creating folder failed."));
  }

  $folder = array(
    'name' => $name,
    'dir'  => "$cdir/$name",
  );

  set('folder', $folder);
  set('locked', false);
  set('newfolder', true);

  echo partial('_folder.html.php');
});

dispatch('/chat', function() {
  header('Content-Type: text/html; charset=utf-8');

  if(!option('enable_chat')) {
    exit('ERR:'._("Unauthorized."));
  }

  set('files', getFiles('/'));
  set('tab', 'chat');
  set('locked', true);
  set('cdir', '/');

  return render('home.html.php');
});

dispatch_post('/chat', function() {
  $action = $_POST['action'];
  $logpath = CHAT_PATH.'log.html';

  header('Content-Type: text/plain; charset=utf-8');

  if(!option('enable_chat')) {
    exit('ERR:'._("Unauthorized."));
  }

  switch($action) {
    case 'getLog':
      if(file_exists($logpath)) {
        $count = intval($_POST['count']);

        $log = file($logpath);
        $logSize = count($log);

        if(!$log) {
          exit('ERR:'._("Failed to open chat log."));
        }

        if($count > $logSize) {
          exit('ERR:'._("Invalid count number."));
        }

        if(!empty($log) && $count != $logSize) {
          $logDiff = array();

          if($count < 1) {
            $logDiff = $log;
  
          } else {
            for($i = $count; $i < $logSize; $i++) {
              array_push($logDiff, $log[$i]);
            }
          }

          echo implode($logDiff);
        }
      }
    break;

    case 'post':
      $pseudo = substr(trim($_POST['pseudo']), 0, 12);
      $pseudo = htmlentities($pseudo);
      $comment = htmlentities(trim($_POST['comment']));
      $date = date('d/m/y H:i');

      if(empty($pseudo) || empty($comment)) {
        exit();
      }

      $comment = preg_replace('/([\w\d]+\:\/\/(?:[\w\-\d]+\.)+[\w\-\d]+(?:\/[\w\-\d]+)*(?:\/|\.[\w\-\d]+)?(?:\?[\w\-\d]+\=[\w\-\d]+\&?)?(?:\#[\w\-\d]*)?)/i', '<a href="$1">$1</a>', $comment);

      $line = "<p data-title='$date'><span>$pseudo</span> $comment</p>\n";
      $flog = fopen($logpath, 'a') or die("Can't open chat log.");

      fwrite($flog, $line);
      fclose($flog);
    break;

    case 'getLineCount':
      $count = intval(exec('wc -l '.escapeshellarg($logpath).' 2> /dev/null'));

      echo ($count >= 0) ? $count : 0;
    break;
  }
});

dispatch('/lang/:locale', function($locale = 'en') {
  switch($locale) {
    case 'fr':
      $_SESSION['locale'] = 'fr';
    break;

    default:
      $_SESSION['locale'] = 'en';
  }

  redirect_to('/');
});
