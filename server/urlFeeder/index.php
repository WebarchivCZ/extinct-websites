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
$out['sum']=0;
$out['data'][0]="idnes.cz";
$out['data'][1]="mzk.cz";
$out['data'][2]="nkp.cz";


//požadavky domén
$select=mysqli_query($db, "SELECT request.*, url.url from request INNER JOIN url on url.id=request.id_url WHERE finished='0' order by id ASC limit ".$from.", ".$limit);
while($r=mysqli_fetch_array($select)) {		
	$date=strtotime($r['date']);
	$lastUpdate=false;
	//ověří, zda již nebylo dotazováno	
	$selectCh=mysqli_query($db, "SELECT timestamp from harvest WHERE id_url='".$r['id_url']."' order by id DESC limit 0,1");
	while($rCh=mysqli_fetch_array($selectCh)) { 
		if(strpos($r['timestamp'], " ")) { $lastUpdate=strtotime($r['timestamp']); }
		else { $lastUpdate=strtotime($r['timestamp']." 23:59:59"); }
	}
	
	if(!$lastUpdate && $date<$lastUpdate) {
		//bylo ověřeno -> zapíše dokončení
		mysqli_query($db, "UPDATE request SET finished='1' WHERE id='".$r['id']."' ");
		
	} else {
		//nebylo ověřeno -> vypíše na seznamu
		$out['data'][]=$r['url'];
	}
}


//pravidelné kontroly dle skupin
$achck=array();
$m=date("n");
$d=date("j");
$h=date("G");
$achck[]='* * *';		
$achck[]=$m.' '.$d.' '.$h;
$achck[]='* '.$d.' '.$h;
$achck[]=$m.' * '.$h;
$achck[]=$m.' '.$d.' *';
$achck[]='* * '.$h;
$achck[]=$m.' * *';
$achck[]='* '.$d.' *';

$select=mysqli_query($db, "SELECT * from url_group.*, url.url INNER JOIN url on url.id=url_group.id_url WHERE autocheck in ('".implode("','", $achck)."') and lastcheck NOT LIKE '".date("Y-m-d")." %' order by id ASC limit ".$from.", ".$limit);
while($r=mysqli_fetch_array($select)) {	
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
}



$out['sum']=count($out['data']);
echo json_encode($out);

?>
