<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "../../../fce.php";

$status=false;
$id=false;
$uuid=array();
if(!empty($_GET['uuid'])) { $uuid[]=strval($_GET['uuid']); }
else {
	$get = file_get_contents('php://input');
	$data = json_decode($get, true, 512, JSON_OBJECT_AS_ARRAY);
	$uuid[] = $data['uuid'];
}

foreach($uuid as $u) {

	$select=mysqli_query($db, "select id from url WHERE uuid='".sqlInject($u)."' limit 0,1");
	while($r=mysqli_fetch_array($select)) {	
		$id=$r['id'];
	}

	if($id && !empty($_GET['dead'])) {
		//status table
		$exists=false;
		$select=mysqli_query($db, "select id from status WHERE id_url='".$id."' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			$exists=true;
			mysqli_query($db, "UPDATE status SET dead=1, confirmed=1, requires=0, date='".date("Y-m-d H:i:s")."' WHERE id_url=".intval($id));
		}
	
		if(!$exists) {
			mysqli_query($db, "INSERT into status SET dead=1, confirmed=1, requires=0, metadata=0, metadata_match=0, date='".date("Y-m-d H:i:s")."', id_url=".intval($id));
			$status=true;
		}
		//exticint table
		$exists=false;
		$select=mysqli_query($db, "select id from exticint WHERE id_url='".$id."' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			$exists=true;
			//mysqli_query($db, "UPDATE exticint SET exticintDate='".date("Y-m-d H:i:s")."' WHERE id_url=".intval($id));
		}
	
		if(!$exists) {
			mysqli_query($db, "INSERT exticint SET exticintDate='".date("Y-m-d H:i:s")."', id_url=".intval($id));
			$status=true;
		}
		
	} elseif($id) {
		mysqli_query($db, "UPDATE status SET dead=0, date='".date("Y-m-d H:i:s")."' WHERE id_url=".intval($id));
		mysqli_query($db, "DELETE FROM exticint WHERE id_url=".intval($id));
		$status=true;
	}
}

echo json_decode($status);

?>
