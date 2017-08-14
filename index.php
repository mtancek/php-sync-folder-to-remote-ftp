<?php
// PHP configuration
ini_set("max_execution_time", 3600);

// General
$backup_folder = '/local_forder_path/'; // folder to backup

// Remote FTP configuration
$host     = ''; // FTP IP address/hostname
$username = 'uvozftp'; // FTP username
$password = 'asdf1234'; // FTP password
$remote_backup = '/remote_folder_path/'; // folder on remote server to upload to
$sync_only_new_files = true; // Set to false for the first run so all files to be copy to a remote server
$sync_period = '24 hours'; // Time period between synchronizations

$time_format = 'Y-m-d H:i:s';
$last_sync_time = strtotime('-' . $sync_period);

// Process
echo "<!-- Starting sync - ".date("Y-m-d H:i:s")." -->";

$ftp = ftp_connect($host); // connect to the ftp server
ftp_login($ftp, $username, $password); // login to the ftp server
ftp_chdir($ftp, $remote_backup); // cd into the remote backup folder

// copy files from folder to remote folder
$files = glob($backup_folder . '*');
$c = 0;
$allc = count($files);
foreach($files as $file) {
  $c++;

  $file_name = basename($file);
  echo "\n $c/$allc: $file_name";
  
  $filemtime = filemtime($file);
	if ($sync_only_new_files && $filemtime < $last_sync_time) {
		echo " - file has already been synchronized. Update time: " . date($time_format, $filemtime);
		continue;
	}
  
  $upload = ftp_nb_put($ftp, $file_name, $file, FTP_BINARY); // non-blocking put, uploads the local backup onto the remote server
  while ($upload == FTP_MOREDATA) {
    // Continue uploading...
    $upload = ftp_nb_continue($ftp);
  }
  if($upload != FTP_FINISHED) {
    echo " ... ERROR";
  }else{
    echo " ... OK";
  }
}

ftp_close($ftp); // closes the connection

echo "<!-- Ending sync - ".date("Y-m-d H:i:s")." -->";
?>
