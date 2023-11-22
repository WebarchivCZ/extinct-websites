<?php
include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "../../../fce.php";
include "../../../sqlClass.php";
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

function removeCharsFromHash($value) {
	$value=str_replace("https://", "", $value);
	$value=str_replace("http://", "", $value);
	$value=rtrim($value, "/");
	return $value;
}


$out=array();
$out['status']=false;

$get = file_get_contents('php://input');
$data = json_decode($get, true, 512,JSON_OBJECT_AS_ARRAY);	

$id=false;

 if(!empty($data["url"])) {
  
  $url=explode(",", str_replace("\n", ",", str_replace(", ", ",", $data["url"])));

  foreach($url as &$u) {

  	if($u!="") { 
  		$u=removeCharsFromHash($u);
  		$hash=hashUrl($u);
  		$sql=new Sql("url", $db, $data);
			$sql->addRow("url", $u);
			$sql->addRow("uuid", $hash);

		if(!$sql->checkIfExists("uuid", $hash)) {
			$sql->save();

			if(!empty($data["group"]) && $data["group"]!="VŠE") {
				$gr=new Sql("url_group", $db, $data);
					$gr->addRow("id_url", $sql->getLastId());
					$gr->addRow("groupname", $data["group"]);
					$gr->addRow("autocheck", "");
				$gr->save();
			}
			$out['url'][]=$u;
			$out['status']=true;
  		} else {
  			$sql->update("url", $u, "uuid", $hash);
  		}
  	}
  }
 }
	


echo json_encode($out);

?>
