<?php
include __DIR__."/../connect.php";
include __DIR__."/check.php";

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$status=array();

$select=mysqli_query($db, "SELECT url from url order by id ASC");
while($r=mysqli_fetch_array($select)) {	
	if(!empty($r['url'])) {
		$status[]=check($r['url']);
	}
}

echo json_encode($status);

?>
