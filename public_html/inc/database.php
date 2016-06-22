<?php
$database = new mysqli("localhost", "root", "pass", "main", 3306);


if ($database->connect_errno) {
    echo "Failed to connect to MySQL: (" . $database->connect_errno . ") " . $database->connect_error;
}

# Create tables if they don't exist.  For now this check happens every page refresh 
# There should be a seperate 'install' procedure to setup tables instead
if ($database->query("SHOW TABLES LIKE 'programmes'")->num_rows==0) { 
	$sql = <<<SQL
CREATE TABLE `programmes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `image_url` text NOT NULL,
  `image_wide_url` text CHARACTER SET utf32 NOT NULL,
  `description` text NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `type` text NOT NULL,
  `rating` decimal(10,1) NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
SQL;

	$database->query($sql);
}

if ($database->query("SHOW TABLES LIKE 'user_votes'")->num_rows==0) { 
	$sql = <<<SQL
CREATE TABLE `user_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `IP` text NOT NULL,
  `programme_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;
SQL;

	$database->query($sql);
}

#https://stackoverflow.com/questions/15699101/get-the-client-ip-address-using-php
function get_client_ip() {
    $ipaddress = NULL;
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    return $ipaddress;
}
?>