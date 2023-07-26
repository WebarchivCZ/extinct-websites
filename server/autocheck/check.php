<?php
include $_SERVER['CONTEXT_DOCUMENT_ROOT']."/autocheck/check.class.php";

function check($url, $uuid=false) {
	$status=array();
	$status['url']=$url;
	$status['dead']=false;
	$status['deadIndex']=0;		// >100 mrtvé, >50 kontrola
	$status['manualCheck']=false;

	$url=new URL($status['url']);
	$url->loadData($GLOBALS['db'], $uuid);
	$status['code']=$url->getLastCode();


	if($status['code']>=400) {
		$code2=$url->getCode(1);
		$code3=$url->getCode(2);
		if((is_int($code2) && $code2>=400 || !is_int($code2)) && (is_int($code3) && $code3>=400 || !is_int($code3))) { 
			$status['deadIndex']=100;
		} else {
			$status['deadIndex']=50;
		}

	} else {
		$status['redirect']=$url->isRedirect();
		$status['redirectToAnotherDomain']=$url->isRedirectToAnotherDomain();
		
		if($status['redirect']>1) { $status['deadIndex']+=(5*($status['redirect']-1)); }
		if($status['redirectToAnotherDomain']) { $status['deadIndex']+=50; }
		
		//page data
		$data1=$url->getPagedata(0);
		$data2=$url->getPagedata(1);
		$data3=$url->getPagedata(2);
		/*
		$status['pagedata'][]=$data1;
		$status['pagedata'][]=$data2;
		$status['pagedata'][]=$data3;
		*/
		
		foreach($data1 as $key=>$d) {
			if(!empty($data2)) {
				$status['pagedata_diff'][$key]=array_diff($data1[$key][0], $data2[$key][0]);
				//$status['pagedata_diff'][$key]+=array_diff($data2[$key][0], $data1[$key][0]);
			}
			if(!empty($data3)) {
				$status['pagedata_diff'][$key]+=array_diff($data1[$key][0], $data3[$key][0]);
				//$status['pagedata_diff'][$key]+=array_diff($data3[$key][0], $data1[$key][0]);
			}
			$status['pagedata_count'][$key]=count($data1[$key][0]);
			//definice skóre
			$maxScore=5;
			switch ($key) {
			case "h1_titles":
				$maxScore=70; break;
			case "h2_titles":
				$maxScore=60; break;
			case "met_author":
				$maxScore=40; break;
			case "met_description":
				$maxScore=20; break;
			case "met_keywords":
				$maxScore=30; break;		
			}
			//přičtení indexu
			if(!empty($status['pagedata_count'][$key])) {
				$percent=$maxScore/intval($status['pagedata_count'][$key]);
			} else {
				$percent=0;
			}
			$status['deadIndex']+=round(count($status['pagedata_diff'][$key])*$percent);
			
			
			/*
			//alternative
			$score=1;
			switch ($key) {
			case "h1_titles":
				$score=20; break;
			case "h2_titles":
				$score=2; break;
			case "met_author":
				$score=10; break;
			case "met_description":
				$score=10; break;
			case "met_keywords":
				$score=5; break;		
			}
			//přičtení indexu
			$percent=100/$status['pagedata_count'][$key];
			$status['deadIndex']+=round(count($status['pagedata_diff'][$key])*$percent*$score);
			*/
		}
		if(DEBUG) { 
			$status['pagedata'][]=$data1; 
			$status['pagedata'][]=$data2; 
			$status['pagedata'][]=$data3; 
		}
		
		//whois
		$whois1=$url->getWhois(0);
		$whois2=$url->getWhois(1);
		$whois3=$url->getWhois(2);
		foreach($whois1 as $key=>$w) {
			if(!empty($whois2)) {
				$status['whois_diff'][$key]=array_diff($whois1[$key][0], $whois2[$key][0]);
				$status['whois_diff'][$key]=array_diff($whois2[$key][0], $whois1[$key][0]);
			}
			if(!empty($whois3)) {
				$status['whois_diff'][$key]=array_diff($whois1[$key][0], $whois3[$key][0]);
				$status['whois_diff'][$key]=array_diff($whois3[$key][0], $whois1[$key][0]);
			}
			$status['whois_count'][$key]=count($whois1[$key][0]);
			
			//přičtení indexu
			$status['deadIndex']+=count($status['whois_diff'][$key])*5;
		}
		if(DEBUG) { 
			$status['whois'][]=$whois1; 
			$status['whois'][]=$whois2; 
			$status['whois'][]=$whois3; 
		}

	}

	//uložení výsledku do databáze
	$url_id=$url->getUrlId();
	if(!empty($url->getUrlId())) {
		if($status['deadIndex']>=100) { $status['dead']=true; }
		elseif($status['deadIndex']>=50) { $status['manualCheck']=true; }
		
		$col="id_url=".$url->getUrlId().", ";
		$col.="dead=".intval($status['dead']).", ";
		$col.="confirmed=0, ";
		$col.="requires=".intval($status['manualCheck']).", ";
		if(!empty($data1)) { 
			$col.="metadata=1, "; 
			$col.="metadata_match=".$status['deadIndex'].", ";
		
		} else { 
			$col.="metadata=0, "; 
			$col.="metadata_match=0, ";
		}
		if(!empty($whois1)) { 
			$col.="whois=1, "; 
		
		} else { 
			$col.="whois=0, "; 
		}
		$col.="date='".date("Y-m-d H:i:s")."' ";
		
		mysqli_query($GLOBALS['db'], "INSERT INTO status SET ".$col);
		
		//uložení do databáze mrtvých webů
		if($status['dead']) {
			$exists=false;
			$select=mysqli_query($db, "select id from exticint where id_url=".$url->getUrlId());
			while($r=mysqli_fetch_array($select)) {
				$exists=$r['id'];
			}
			if(empty($exists)) {
				mysqli_query($GLOBALS['db'], "INSERT INTO exticint SET exticintDate='".date("Y-m-d H:i:s")."', id_url=".$url->getUrlId());
			}
		}
	}


	return $status;
}

?>
