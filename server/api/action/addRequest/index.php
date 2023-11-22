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

 if(!empty($data["uuid"])) {
  foreach($data["uuid"] as &$uuid) {

	$select=mysqli_query($db, "SELECT id, url from url WHERE uuid='".sqlInject($uuid)."' limit 0,1");
	while($r=mysqli_fetch_array($select)) {		
  		$out['url'][]=$r['url'];
  		
  		$url=new Sql("request", $db, $data);
			$url->addRow("id_url", $r['id']);
			$url->addRow("date", date("Y-m-d H:i:s"));
			$url->addRow("finished", 0);

		$url->save();
  	}
 }

   if(!empty($out['url'])) { 
   	$out['status']=true; 
   }
 }
	


echo json_encode($out);

?>
