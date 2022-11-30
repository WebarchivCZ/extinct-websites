<?php
include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "./check.php";

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$status=array();
$i=0;

$select=mysqli_query($db, "SELECT url from url order by id ASC");
while($r=mysqli_fetch_array($select)) {	
	$status[$i]['url']=$r['url'];
	$status[$i]['status']=check($r['url']);
	$i++;
}

echo json_encode($status);

?>
