<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

include "../../../../connect.php";
include "../../../fce.php";


if(!empty($_GET['uuid'])) {
	$id=false;
	$uuid=sqlInject(strval($_GET['uuid']));
	$category=sqlInject(strval($_GET['category']));
	
	$select=mysqli_query($db, "select id from url WHERE uuid='".$uuid."' limit 0,1");
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
		}
		
	} elseif($id) {
		mysqli_query($db, "DELETE from url_group WHERE id_url=".intval($id));
	}
}
?>
