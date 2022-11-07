<?php
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include "../../connect.php";
include "../fce.php";
include "./api.class.php";


$api=new Api("url", "id", "url", "url");


if($_GET['type']=="app") {
	$api->addItem("id");
	$api->addItem("UUID");
	$api->addItem("url");
	
	$api->addGroup("contract");
		$api->addSubItem("contracted");
		$api->addSubItem("from");
		$api->addSubItem("to");
		
	//stav webu
	$api->addGroup("status");
		$api->addSubItem("dead");
		$api->addSubItem("confirmed");
		$api->addSubItem("requires");	
		$api->addSubItem("metadata_match");	
		$api->addSubItem("date");	
		
	$api->dataMoveArray("url", "seeds_info");
	$api->dataMoveArray("contract", "seeds_info");

} else {
	$api->addItem("id", "urlid");
	$api->addItem("UUID", "uuid", "uuid");
	$api->addItem("url", "seeds_info.url");

	$api->addGroup("contract", "seeds_info.contract");
		$api->addSubItem("contracted", "contracted");
		$api->addSubItem("from");
		$api->addSubItem("to");

 
 	$api->addExtension("harvest_metadata", "harvest", "id_url", "harvest_metadata", false, false, "id", "id ASC");
 		$api->addExtensionItem("timestamp");
 		$api->addExtensionItem("crawler");
 		$api->addExtensionItem("harvest"); 		
 		
 		$api->addExtension("seeds_report", "harvest_report", "id_harvest", "harvest_metadata.seeds_report", true);
 			$api->addExtensionItem("code");
 			$api->addExtensionItem("status");
 			$api->addExtensionItem("seed");
 			$api->addExtensionItem("redirect", "redirect_last");
			$api->addExtensionItem("UUID_final_url");
 			$api->addExtensionItem("redirect_depth");
 			//nové
 			$api->addExtensionItem("contentType", "Content-Type");
 			$api->addExtensionItem("Encoding");
 			$api->addExtensionItem("contentLength", "Content-Length");
 			$api->addExtensionItem("Date");
 			$api->addExtensionItem("serverEngine", "server-engine");
 			$api->addExtensionItem("serverVersion", "server-version");
 			$api->addExtensionItem("Error");	
 
	$api->dataMoveArray("url", "seeds_info");
	$api->dataMoveArray("contract", "seeds_info");


	//webbeat
	$api->addExtension("whois", "whois", "id_url", "whois", false, false, "id", "id ASC");
		$api->addExtensionItem("domain_name");
 		$api->addExtensionItem("registrant_name");
 		$api->addExtensionItem("registrar");
 		$api->addExtensionItem("creation_date", "times.creation_date");
 		$api->addExtensionItem("expiration_date", "times.expiration_date");
 		
 			$api->addExtension("name_servers", "name_servers", "id_whois", "name_servers", true);
 				$api->addExtensionItem("srv");
 				$api->addExtensionItem("srv_ip");
 		
 			$api->addExtension("updated_date", "updated_date", "id_whois", "times.updated_date", true);
 				$api->addExtensionItem("date");
	
	
	$api->addExtension("page_data", "page_data", "id_url", "page_data", false, true, "id", "id ASC");
		$api->addExtensionItem("type");
 		$api->addExtensionItem("value");
 	
}

	//echo $api->getSqlQuery()."\n\n";

$limit=100;
$from=0;
$where="";
$join="";
if(!empty($_GET['limit']) && $_GET['limit']>0 && $_GET['limit']<=5000) { $limit=intval($_GET['limit']); }
if(!empty($_GET['from'])) { $from=intval($_GET['from']); }
elseif(!empty($_GET['page'])) { $from=$limit*intval($_GET['page']); }
if(!empty($_GET['uuid'])) { $where=" uuid='".$api->sqlInject($_GET['uuid'])."'"; }
elseif(!empty($_GET['search'])) { $where=" url LIKE '%".$api->sqlInject($_GET['search'])."%'"; }
if(!empty($_GET['filter'])) { 
	if($where!="") { $where.=" and"; }
	if($_GET['filter']=="unknown") { $where.=" status.requires=true and status.confirmed=false"; }
	elseif($_GET['filter']=="dead") { $where.=" status.dead=1"; }
	elseif($_GET['filter']=="live") { $where.=" (status.dead=0 or status.dead IS NULL)"; }
}

if(!empty($_GET['kat'])) { 
	$join.="INNER JOIN url_group on url_group.id_url=url.id "; 
	if($where!="") { $where.=" and"; }
	$where.=" url_group.groupname='".sqlInject($_GET['kat'])."'"; 
}

$api->loadData($db, $where, '', $from, $limit, $join);
$import=$api->import($db);	
if($import) { $data=$import; }
else { $data=$api->getData(); }

$out["data"]=$data;
$out["stats"]["sum"]=$api->getSum($db, $where, $join);
$out["stats"]["query"]=$api->getSqlQuery($where, $order, $from, $limit, $join);

echo json_encode($out);

?>
