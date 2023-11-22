<?php
include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "../../../fce.php";
include "../../../sqlClass.php";
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

$out=array();
$out['status']=false;

$get = file_get_contents('php://input');
$data = json_decode($get, true, 512,JSON_OBJECT_AS_ARRAY);	

$id=false;

 if(!empty($data["group"])) {
 
 	$autocheck=$data["month"]." ".$data["day"]." ".$data["hour"];
 	if($data["month"]=="-1") { $autocheck=""; } 
	mysqli_query($db, "UPDATE url_group SET autocheck='".$autocheck."' WHERE groupname='".sqlInject($data["group"])."' ");
   	$out['status']=true; 
 }
	


echo json_encode($out);

?>
