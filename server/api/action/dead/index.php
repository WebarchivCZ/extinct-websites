<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

include "../../../../connect.php";
include "../../../fce.php";


if(!empty($_GET['uuid'])) {
	$id=false;
	$uuid=sqlInject(strval($_GET['uuid']));
	
	$select=mysqli_query($db, "select id from url WHERE uuid='".$uuid."' limit 0,1");
	while($r=mysqli_fetch_array($select)) {	
		$id=$r['id'];
	}

	if($id && !empty($_GET['dead'])) {
		$exists=false;
		$select=mysqli_query($db, "select id from status WHERE id_url='".$id."' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			$exists=true;
			mysqli_query($db, "UPDATE status SET dead=1, confirmed=1, requires=0, date='".date("Y-m-d H:i:s")."' WHERE id_url=".intval($id));
		}
	
		if(!$exists) {
			echo "INSERT into status SET dead=1, confirmed=1, requires=0, metadata=0, metadata_match=0, date='".date("Y-m-d H:i:s")."', id_url=".intval($id);
			mysqli_query($db, "INSERT into status SET dead=1, confirmed=1, requires=0, metadata=0, metadata_match=0, date='".date("Y-m-d H:i:s")."', id_url=".intval($id));
		}
		
	} elseif($id) {
		mysqli_query($db, "UPDATE status SET dead=0, date='".date("Y-m-d H:i:s")."' WHERE id_url=".intval($id));
	}
}
?>
