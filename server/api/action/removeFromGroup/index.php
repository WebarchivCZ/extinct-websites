<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "../../../fce.php";


$status=false;
$id=false;
$category=sqlInject(strval($_GET['category']));

$uuid=array();
if(!empty($_GET['uuid'])) { $uuid[]=strval($_GET['uuid']); }
else {
	$get = file_get_contents('php://input');
	$data = json_decode($get, true, 512, JSON_OBJECT_AS_ARRAY);
	$uuid[] = $data['uuid'];
}

foreach($uuid as &$u) {	
	$select=mysqli_query($db, "select id from url WHERE uuid='".sqlInject($u)."' limit 0,1");
	while($r=mysqli_fetch_array($select)) {	
		$id=$r['id'];
	}

	if($id) {
		mysqli_query($db, "DELETE from url_group WHERE groupname='".$category."' and id_url=".intval($id));
		$status=true;
	}
}

echo json_decode($status);

?>
