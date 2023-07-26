<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "../../fce.php";
include "../../sqlClass.php";

 $out=array();

 $url=new Sql("url_group", $db);
 
 	if(!empty($_GET['group'])) { 
 		$where="groupname='".$url->sqlInject($_GET['group'])."'";
 		$data=$url->getData(0, 1, $where, "*", "groupname ASC");
		for($i=0; $i<count($data); $i++) {
	
			$out[$i]['name']=$data[$i]['groupname'];
			$out[$i]['check']=$data[$i]['autocheck'];
			$out[$i]['last']=$data[$i]['lastcheck'];
		}
 	
   	} else {
	 	$data=$url->getData(0, 1000, $where, "groupname", "groupname ASC", "GROUP BY groupname");
		for($i=0; $i<count($data); $i++) {
			
			$out[$i]=$data[$i]['groupname'];
		}
	}

echo json_encode($out);

?>
