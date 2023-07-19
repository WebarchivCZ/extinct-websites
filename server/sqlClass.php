<?php
class Sql {
	private $table;
	private $connection;
	private $inputData;
	private $rows;
	private $exists;

	function __construct($table, $connection, $inputData=false) {
		$this->table=$table;
		$this->connection=$connection;
		$this->inputData=$inputData;
		$this->rows=array();
		$this->exists=false;
	}
	
	function addRow($name, $value=false, $unique=false) {
		if(!$value && is_bool($value) && $this->inputData) { $value=$this->inputData[$name]; }
		$this->rows[]=new SqlRow($name, $this->sqlInject($value));
		if($unique) { $this->checkIfExists($name, $value); }
	}
	
	function save() {
		$l="";
		foreach($this->rows as &$r) {
			if($l!="") { $l.=","; }
			$l.=$r->get();
		}
		//echo "INSERT into ".$this->table." SET ".$l."\n";
 		mysqli_query($this->connection, "INSERT into ".$this->table." SET ".$l);
	}
	
	function update($col, $val, $whereCol, $whereVal) {
		$set=$col."='".$this->sqlInject($val)."'";
		if($val=="") { $set=$col; }
		
		$where=$whereCol."='".$this->sqlInject($whereVal)."'";
		if($whereVal=="") { $where=$whereCol; }
		
 		mysqli_query($this->connection, "UPDATE ".$this->table." SET ".$set." WHERE ".$where);
	}
	
	function checkIfExists($name, $value) {
		$this->exists=false;
		$select=mysqli_query($this->connection, "select id from ".$this->table." WHERE ".$name."='".$this->sqlInject($value)."' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	$this->exists=true; }
		return $this->exists;
	}
	
	function getLastId($order="id") {
		$id=false;
		$select=mysqli_query($this->connection, "select id from ".$this->table." order by ".$order." DESC limit 0,1");
		while($r=mysqli_fetch_array($select)) {	$id=$r['id']; }
		return $id;
	}
	
	function getValue($value, $conditionName, $conditionValue) {
		$v=false;
		$select=mysqli_query($this->connection, "select ".$value." from ".$this->table." WHERE ".$conditionName."='".$this->sqlInject($conditionValue)."' limit 0,1");
		while($r=mysqli_fetch_array($select)) {	$v=$r[$value]; }
		return $v;
	}
	
	function isExists() { return $this->exists; }
	
	function sqlInject($value) {
		$value=str_replace('\\\\', '', $value);
		$value=str_replace("'", "\'", $value);
		$value=str_replace('"', '\"', $value);
		$value=stripslashes($value);
		return $value;
	}
	
	function getData($from=0, $to=100, $where=false, $rows="*", $order=false, $join=false) {
		$out=array(); $i=0;
		if($order) { $order=" order by ".$this->sqlInject($order); }
		if($where) { $where=" WHERE ".$where; }
		//echo "select ".$this->sqlInject($rows)." from ".$this->table." ".$join.$where.$order." limit ".intval($from).",".intval($to)."\n";
		$select=mysqli_query($this->connection, "select ".$this->sqlInject($rows)." from ".$this->table." ".$join.$where.$order." limit ".intval($from).",".intval($to));
		while($r=mysqli_fetch_array($select)) {	
			foreach($r as $k=>$v) { 
				if(!is_int($k)) { $out[$i][$k]=$v; } 
			}
			$i++;
		}
		return $out;
	}
}

class SqlRow {
	private $name;
	private $value;

	function __construct($name, $value) {
		$this->name=$name;
		$this->value=$value;
	}
	
	function get() {
		if(is_int($this->value)) { return $this->name."=".$this->value; }
		return $this->name."='".$this->value."'";
	}
}


?>
