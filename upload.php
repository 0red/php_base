<?php
$ds          = DIRECTORY_SEPARATOR;  //1
$storeFolder = 'uploads';   //2
if (!empty($_FILES)) {   
    $tempFile = $_FILES['file']['tmp_name'];          //3             
    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
    $targetFile =  $targetPath. $_FILES['file']['name'];  //5
    move_uploaded_file($tempFile,$targetFile); //6
}


/*
b) Edit php.ini via SSH

Edit your php.ini file (usually stored in /etc/php.ini or /etc/php.d/cgi/php.ini or /usr/local/etc/php.ini):

# vi /etc/php.ini
Sample settings:
memory_limit = 128M
upload_max_filesize = 20M
post_max_size = 30M


Method #2: Edit .htaccess

Edit .htaccess file in the root directory of your website. This is useful when you do not have access to the php.ini on a server. You can create the .htaccess file locally and upload it via FTP.
Append / Modify settings:
php_value upload_max_filesize 20M
php_value post_max_size 30M
php_value memory_limit 128M

*/
?>     