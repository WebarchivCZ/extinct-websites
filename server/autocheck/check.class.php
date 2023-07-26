<?php

class URL {

	private $url;
	private $url_id;
	private $codes;
	private $redirects;
	private $redirectToAnotherDomain;
	private $pagedata;
	private $whois;

	function __construct($url=false, $url_id=false) {
		$this->url=$url;
		$this->url_id=$url_id;
		$this->codes=array();
		$this->redirects=array();
		$this->pagedata=array();
		$this->whois=array();
		$this->redirectToAnotherDomain=false;
	}
	
	function addCode($code) {
		$this->codes[]=$code;
	}

	function addRedirects($redirect) {
		$this->redirects[]=$redirect;
	}
	
	function addPagedata($pd, $type, $date) {
		$count=count($this->pagedata);
		if($count>0) {
			$last=($count-1);
			if($this->pagedata[$last]->getDate()==$date) {
				$this->pagedata[$last]->addMetadata($pd, $type);
			} else {
				$this->pagedata[]=new metadataByDate($date, $pd, $type);
			}
		
		} else {
			$this->pagedata[]=new metadataByDate($date, $pd, $type);
		}
	}
	
	
	function addWhois($pd, $type, $date) {
		$count=count($this->whois);
		if($count>0) {
			$last=($count-1);
			if($this->whois[$last]->getDate()==$date) {
				$this->whois[$last]->addMetadata($pd, $type);
			} else {
				$this->whois[]=new metadataByDate($date, $pd, $type);
			}
		
		} else {
			$this->whois[]=new metadataByDate($date, $pd, $type);
		}
	}
	
	//Return functions
	function getUrlId() { return $this->url_id; }
	function getLastCode() { return intval(end($this->codes)); }
	function getCode($indexFromLast=1) { 
		$count=count($this->codes);
		if($count-$indexFromLast>0) {
			return intval($this->codes[($count-1-$indexFromLast)]);
		}
		return false;
	}
	
	function isRedirect() { return intval(end($this->redirects)); }
	function isRedirectToAnotherDomain() { return $this->redirectToAnotherDomain; }
	
	function getPagedataCount() { return count($this->pagedata); }
	function getWhoisCount() { return count($this->whois); }
	function getPagedata($index=0) { 
		$count=count($this->pagedata);
		if($count-$index>0) {
			return $this->pagedata[($count-1-$index)]->getMetadata();
		}
		return false;
	}
	function getWhois($index=0) { 
		$count=count($this->whois);
		if($count-$index>0) {
			return $this->whois[($count-1-$index)]->getMetadata();
		}
		return false;
	}
	
	function getPagedataDate($index=0) { 
		$count=count($this->pagedata);
		if($count-$index>0) {
			return $this->pagedata[($count-1-$index)]->getDate();
		}
		return false;
	}
	function getWhoisDate($index=0) { 
		$count=count($this->whois);
		if($count-$index>0) {
			return $this->whois[($count-1-$index)]->getDate();
		}
		return false;
	}

	function replaceUrl($url) {
		$replace=array("https://", "http://", "'", '"'. "\\");
		$u=explode("/", str_replace($replace, "", $url));
		$u=explode("?", $u[0]);
		return $u[0];
	}

	function loadUrlId($db) {
		$select=mysqli_query($db, "SELECT id from url where url LIKE '%".$this->replaceUrl($this->url)."%' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			$this->url_id=$r['id'];
		}
		$this->id=false;
	}
	
	function loadUrlIdByUuid($db, $uuid) {
		$select=mysqli_query($db, "SELECT id, url from url where uuid LIKE '%".$this->replaceUrl($uuid)."%' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			$this->url_id=$r['id'];
			$this->url=$r['url'];
		}
		$this->id=false;
	}
	
	
	//Load Data
	function loadData($db, $uuid=false) {
		if($uuid) { $this->loadUrlIdByUuid($db, $uuid);  }
		if(!$this->url_id) { $this->loadUrlId($db); }
		$this->loadHarvest($db);
		$this->loadPagedata($db);
		$this->loadWhois($db);
	}
	
	function loadHarvest($db) {
		$select=mysqli_query($db, "select code, redirect_depth, redirect from harvest 
					inner join harvest_report on harvest_report.id_harvest=harvest.id
					where id_url=".intval($this->url_id)."
					order by Date ASC, harvest.id ASC");
		while($r=mysqli_fetch_array($select)) {	
			if($this->replaceUrl($url)!=$this->replaceUrl($r['redirect'])) { $this->redirectToAnotherDomain=true; }
			else { $this->redirectToAnotherDomain=false; }
			$this->addCode($r['code']);
			$this->addRedirects($r['redirect_depth']);
		}
	}
	
	
	function loadPagedata($db) {
		$select=mysqli_query($db, "select type, value, date from page_data 
					where id_url=".intval($this->url_id)."
					order by Date ASC, type ASC, id ASC");
		while($r=mysqli_fetch_array($select)) {	
			$this->addPagedata($r['value'], $r['type'], $r['date']);
		}
	}


	function loadWhois($db) {
		$select=mysqli_query($db, "select type, value, date from whois 
					where id_url=".intval($this->url_id)."
					order by Date ASC, type ASC, id ASC");
		while($r=mysqli_fetch_array($select)) {	
			$this->addWhois($r['value'], $r['type'], $r['date']);
		}
	}


}

class metadataByDate {
	private $date;
	private $metadata;

	function __construct($date, $metadata=false, $type=false) {
		$this->date=$date;
		$this->metadata=array();
		if($metadata) { $this->addMetadata($metadata, $type); }
	}
	
	function getDate() { return $this->date; }
	
	function addMetadata($metadata, $type) {
		$count=count($this->metadata);
		if($count>0) {
			$last=($count-1);
			if($this->metadata[$last]->getType()==$type) {
				$this->metadata[$last]->addMetadata($metadata);
			} else {
				$this->metadata[]=new metadata($type, $metadata);
			}
		} else {
			$this->metadata[]=new metadata($type, $metadata);
		}
	}
	
	function getMetadata() {
		$array=array();
		foreach($this->metadata as &$meta) {
			$array[$meta->getType()][]=$meta->getDataArray();
		}
		return $array;	
	}

}

class metadata {
	private $type;
	private $data;

	function __construct($type, $data=false) {
		$this->type=$type;
		$this->data=array();
		if($data) { $this->addMetadata($data); }
	}
	
	function getType() { return $this->type; }
	function getDataArray() { return $this->data; }
	
	function addMetadata($metadata) {
		$this->data[]=$metadata;
	}

}



?>
