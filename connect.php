<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR);
date_default_timezone_set('Europe/Prague');

//define("DEBUG", true);

$table="extinctWebsites";


$db = @mysqli_connect("localhost", "root", "password", $table);
if (!$db) {
	echo '<br /><br /><div style="font-size: 20px; color:red; text-align:center;">Chyba při připojení k databázi.</div>';
	exit();
}
mysqli_set_charset($db, "utf8");


?>
