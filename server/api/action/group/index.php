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
	if(is_array($data['uuid'])) { $uuid = $data['uuid']; }
	else { $uuid[] = $data['uuid']; }
}

foreach($uuid as &$u) {	
	$select=mysqli_query($db, "select id from url WHERE uuid='".sqlInject($u)."' limit 0,1");
	while($r=mysqli_fetch_array($select)) {	
		$id=$r['id'];
	}

	if($id && !empty($_GET['category'])) {
		$exists=false;
		$select=mysqli_query($db, "select id from url_group WHERE groupname='".$category."' and id_url=".intval($id)." limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			$exists=$r['id'];
		}
		if(!$exists) { 
			mysqli_query($db, "INSERT into url_group SET groupname='".$category."', id_url=".intval($id)); 
			$status=true;
		}
		
	} elseif($id) {
		mysqli_query($db, "DELETE from url_group WHERE id_url=".intval($id));
		$status=true;
	}
}

echo json_decode($status);

?>
