<?php
include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/connect.php";
include "../fce.php";
include "../sqlClass.php";
header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

$from=0;
$limit=100;
if(!empty($_GET['limit']) && $_GET['limit']>0 && $_GET['limit']<=5000) { $limit=intval($_GET['limit']); }
if(!empty($_GET['from'])) { $from=intval($_GET['from']); }
elseif(!empty($_GET['page'])) { $from=$limit*intval($_GET['page']); }

$out=array();
$out['sum']['total']=0;

$result = mysqli_query($db, "SELECT COUNT(id) as c from request WHERE finished='0'"); 
$row = mysqli_fetch_assoc($result); 
$out['sum']['url_request'] = intval($row['c']);

//požadavky domén
$select=mysqli_query($db, "SELECT request.*, url.url from request INNER JOIN url on url.id=request.id_url WHERE finished='0' order by id ASC limit ".$from.", ".$limit);
while($r=mysqli_fetch_array($select)) {		
	$date=strtotime($r['date']);
	$lastUpdate=false;
	//ověří, zda již nebylo dotazováno	
	$selectCh=mysqli_query($db, "SELECT timestamp from harvest WHERE id_url='".$r['id_url']."' order by id DESC limit 0,1");
	while($rCh=mysqli_fetch_array($selectCh)) { 
		if(strpos($rCh['timestamp'], " ")) { $lastUpdate=strtotime($rCh['timestamp']); }
		else { $lastUpdate=strtotime($rCh['timestamp']." 23:59:59"); }
	}
	if($lastUpdate && $date<$lastUpdate) {
		//bylo ověřeno -> zapíše dokončení
		mysqli_query($db, "UPDATE request SET finished='1' WHERE id='".$r['id']."' ");
		
	} else {
		//nebylo ověřeno -> vypíše na seznamu
		$out['data'][]="https://".$r['url'];
	}
}


//pravidelné kontroly dle skupin
$achck=array();
$m=date("n");
$d=date("j");
$h=date("G");
$achck[]='* * *';	$achck[]='*  ';	
$achck[]=$m.' '.$d.' '.$h;
$achck[]='* '.$d.' '.$h;
$achck[]=$m.' * '.$h;
$achck[]=$m.' '.$d.' *';
$achck[]='* * '.$h;
$achck[]=$m.' * *';
$achck[]='* '.$d.' *';

$where=" WHERE autocheck in ('".implode("','", $achck)."') and ((lastcheck NOT LIKE '".date("Y-m-d")." %' and lastcheck NOT LIKE '".date('Y-m-d',strtotime("-1 days"))." %') or lastcheck is NULL)";
$result = mysqli_query($db, "SELECT COUNT(id) as c from url_group".$where); 
$row = mysqli_fetch_assoc($result); 
$out['sum']['url_group'] = intval($row['c']);

$fromGroup=($out['sum']['url_request']-$from);
$limGroup=($limit-$fromGroup);
if($fromGroup<$limit) { $fromGroup=0; }


if($fromGroup>=0) {
	$sql="SELECT url_group.*, url.url from url_group INNER JOIN url on url.id=url_group.id_url ".$where." order by id ASC limit ".$fromGroup.", ".$limGroup;
	$select=mysqli_query($db, $sql);
	while($r=mysqli_fetch_array($select)) {	
		/*
		$check=explode(" ", $r['autocheck']);
		$lastcheck=explode(" ", $lastcheck);
		$date=explode("-", $lastcheck[0]);
		$time=explode(":", $lastcheck[1]);
		$hour=$time[0];
		$month=$date[1];
		$day=$date[2];

		if($r['autocheck']=="* * *" or $r['autocheck']!="") {	// - DODĚLAT kontrolu času
			$out['data'][]=$r['url'];
		} else {
			//pokud již proběhl -> aktualizuj čas update - dodělat
		
		}
		*/

		//datum poslední aktualizace
		$check=false;
		$selectCh=mysqli_query($db, "SELECT timestamp from harvest WHERE id_url='".$r['id_url']."' order by timestamp DESC limit 0,1");
		while($rCh=mysqli_fetch_array($selectCh)) {
			//zkontrolováno? - pokud je datum poslední kontroly vyšší -> zaznamenej a již nekontroluj	
			if(date('Y-m-d H:i:s', strtotime('-1 hour'))<$rCh['timestamp']) {
				$check=true;
				mysqli_query($db, "UPDATE url_group SET lastcheck='".$rCh['timestamp']."' WHERE id_url='".$r['id_url']."'"); 
			}
		}
		if(!$check) { $out['data'][]="https://".$r['url']; }
		
	}
}


$out['sum']['total']=($out['sum']['url_request']+$out['sum']['url_group']);
$out['sum']['pages']=ceil($out['sum']['total']/$limit);
if(!empty($_GET['sql'])) { $out['sum']['sql']=$sql; }
echo json_encode($out);

?>
