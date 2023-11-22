<?php

class Api {
	private $mainSqlTable;
	private $primaryKey;
	private $uniqueKey;
	private $hashColumn;
	private $items;
	private $groups;
	private $lastGroup;
	private $extensions;
	private $data;
	
	function __construct($mainSqlTable="url", $primaryKey="id", $uniqueKey=false, $hashColumn=false) {
		$this->mainSqlTable=$mainSqlTable;
		$this->primaryKey=$primaryKey;
		$this->uniqueKey=$uniqueKey;
		$this->hashColumn=$hashColumn;
		$this->items=array();
		$this->groups=array();
		$this->extensions=array();
		$this->data=array();
		$this->$lastGroup=false;
   	}
	
	function addItem($name, $importName=false, $sqlName=false) {
		$this->items[]=new ApiItem($name, $importName, $sqlName);
	}
	
	
	function addGroup($name, $importArray=false, $sqlTable=false) {
		$this->groups[]=new ApiGroup($name, $importArray, $sqlTable);
	}
	
	function addExtension($name, $table, $whereColumn=false, $importArray=false, $toSubGroup=true, $listOfValues=false, $idInArray="id", $orderby="id ASC") {
		if(!$whereColumn) { $whereColumn="id_".$this->mainSqlTable; }
		if($toSubGroup) { $toSubGroup=$this->$lastGroup; }
		$id=count($this->extensions);
		$this->extensions[]=new ApiExtension($id, $name, $table, $whereColumn, $importArray, $toSubGroup, $listOfValues, $idInArray, $orderby);
		$this->$lastGroup=++$id;
	}
	
	function addExtensionItem($name, $importName=false, $sqlName=false) {
		$id=count($this->extensions)-1;
		if($id>=0) {
			$this->extensions[$id]->addItem($name, $importName, $sqlName);
		}
	}
	
	function setExtensionDate($date="0000-00-00", $column="date") {
		$count=count($this->extensions);
		if($count>0) { $this->extensions[($count-1)]->setExtensionDate($date, $column); }
	}
	
	function getLastGroup() {
		$prev=count($this->groups)-1;
		if($prev<0) { $prev=0; }
		return $this->groups[$prev];
	}
	
	
	
	function addSubItem($name, $importName=false, $sqlName=false) {
		$group=$this->getLastGroup();
		$group->addItem($name, $importName, $sqlName);
	}
	
	function addSubGroup($name, $importArray=false, $sqlTable=false)  {
		$group=$this->getLastGroup();
		$group->addGroup($name, $importArray, $sqlTable);
	}
	
	
	function getSqlQuery($where="", $order="", $from=0, $limit=100, $join="") {
		$columns=""; 
		//položky
		foreach($this->items as &$item) {
			if($columns!="") { $columns.=", "; }
			$columns.=$this->mainSqlTable.'.'.$item->getSqlName();
		}
		//skupiny
		foreach($this->groups as &$group) {
			$groupCol=$group->getSqlColumns();
			if($groupCol!="") { 
				if($columns!="") { $columns.=", "; }
				$columns.=$groupCol;
			}
			$join.=" ".$group->getSqlJoin($this->mainSqlTable);
		}
		
		if($where!="") { $where=" WHERE ".$where; }
		if($order!="") { $order=" order by ".$this->sqlInject($order); }
		return "SELECT ".$columns." FROM ".$this->mainSqlTable." ".$join.$where.$order." limit ".intval($from).",".intval($limit);
	}
	
	
	function getDataArray($connection, $where="", $order="", $from=0, $limit=100, $join="") {
		$arr=array();
		$i=0;
		$select=mysqli_query($connection, $this->getSqlQuery($where, $order, $from, $limit, $join));
		while($r=mysqli_fetch_array($select)) {	
			foreach($this->items as &$item) {
				$arr[$i][$item->getName()]=$r[$item->getSqlName()];
			}
			//skupiny
			foreach($this->groups as &$group) {
				$arr[$i][$group->getName()]=$group->getArrayColumns($r);
			}
			$i++;
		}
		

		$arr=$this->getExtensionData($connection, $arr, 0);
		
		return $arr;
	}
	
	
	function getSum($connection, $where="", $join="") { 
		$sum=0;
		//skupiny
		foreach($this->groups as &$group) {
			$join.=" ".$group->getSqlJoin($this->mainSqlTable);
		}
		if($where!="") { $where=" WHERE ".$where; }
		$sql="SELECT count(".$this->mainSqlTable.".id) FROM ".$this->mainSqlTable." ".$join.$where;
		$select=mysqli_query($connection, $sql);
		while($r=mysqli_fetch_array($select)) {	
			$sum=$r[0];
		}
		return intval($sum);
	}
	
	
	//rozšiřující tabulky
	function getExtensionData($connection, $arr, $idExtension=0, $subItem=false) {
		foreach($this->extensions as &$ext) {
			$ids=$ext->getIdInArray();
			$idArray=array();
			//načtení všech ID
			foreach($arr as &$item) {
				if($subItem) { 
					foreach($item[$subItem] as &$item2) {
						$idArray[]=$item2[$ids]; 
					}
				} else { $idArray[]=$item[$ids]; }
			}
			
			if($ext->getSubGroupId()==$idExtension) {
				if($ext->isListOfValues()) {
					$i=-1; $lastId=0;
					$select=mysqli_query($connection, "SELECT * from ".$ext->getTable()." where ".$ext->getWhereColumn()." IN(".implode(',',$idArray).") order by date DESC, ".$ext->getWhereColumn()." ASC");
					while($r=mysqli_fetch_array($select)) {	
							if($lastId!=$r[$ext->getWhereColumn()]) { $i++; }
							$date=$r['date'];
							if(empty($date)) { $date="0000-00-00"; }
							$arr[$i][$ext->getName()][$r[1]][$date][]=$r[2];
							$lastId=$r[$ext->getWhereColumn()];
					}
					/*
					//bez data
					$select=mysqli_query($connection, "SELECT * from ".$ext->getTable()." where ".$ext->getWhereColumn()." IN(".implode(',',$idArray).") order by ".$ext->getWhereColumn()." ASC");
					while($r=mysqli_fetch_array($select)) {	
							if($lastId!=$r[$ext->getWhereColumn()]) { $i++; }
							$arr[$i][$ext->getName()][$r[1]][]=$r[2];
							$lastId=$r[$ext->getWhereColumn()];
					}*/
							
				} else {
					//$arr['sql'][]=$ext->getSelect($idArray);
					$i=0; $x=-1; $lastId=-1;
									//$ext->getSelect($idArray)
					$select=mysqli_query($connection, $ext->getSelect($idArray));
					
					if($subItem) {
						$i=0;
						while($r=mysqli_fetch_array($select)) {	
							$id=$r[$ext->getWhereColumn()];
							if($lastId!=$id) { $i=0; }
							//procházení položek
							foreach($ext->getItems() as &$item) {
								foreach($arr as &$a) {								
									foreach($a[$subItem] as &$b) {
										if($b["id"]==$id) {
											$count=count($b[$ext->getName()]);
											$b[$ext->getName()][$i][$item->getName()]=$r[$item->getSqlName()]; 
										}
									}
								}
							}
							$lastId=$id;
							$i++;
						}
						$arr=$this->getExtensionData($connection, $arr, ($idExtension+1), $subItem);
					
					} else { 
						while($r=mysqli_fetch_array($select)) {	
							//najde pořadí dle ID prvku
							for($z=0; $z<count($arr); $z++) {
								if(intval($arr[$z]['id'])==$r[$ext->getWhereColumn()]) {
									$x=$z;
									$i=count($arr[$z][$ext->getName()]);
								}
							}
							//procházení položek
							foreach($ext->getItems() as &$item) { 
								$arr[$x][$ext->getName()][$i][$item->getName()]=$r[$item->getSqlName()]; 
							}
							$i++;
						}
						//rozšiřující tabulky pod touto rozšiřující tabulkou...			
						$arr=$this->getExtensionData($connection, $arr, ($ext->getId()+1), $ext->getName());
					}
				}
			} 
		}
		return $arr;
	}
	
	//odstraní z výstupu ID řádků
	function array_unset_recursive(&$array, $remove) {

		foreach($array as $key => &$value) {
			if(is_array($value)) {
			    $array[$key]=$this->array_unset_recursive($value, $remove);
			} else if($key==$remove) {
			    unset($array[$key]);
			}
		}
		return $array;
	}


	function loadData($connection, $where="", $order="", $from=0, $limit=100, $join="") { $this->data=$this->getDataArray($connection, $where, $order, $from, $limit, $join); }
	function getData() { return $this->data; }
	function getDataJson($showIds=false) { 
	
		if(!$showIds) {
			return json_encode($this->array_unset_recursive($this->data, "id"));
		} 
		return json_encode($this->data); 
	}
	
	//přesune pole pod nový název
	function dataMoveArray($name, $to, $replace=false) {
		for($i=0;$i<count($this->data); $i++) {
			$var=$this->data[$i][$name];
			if(!empty($var)) {
				if($replace) {
					$this->data[$i][$to]=$var;
				} else {
					$this->data[$i][$to][$name]=$var;
				}
				unset($this->data[$i][$name]);
			}
		}
	}	
	
	function getLastId($connection, $table=false) {
		if(!$table) { $table=$this->mainSqlTable; }
		$select=mysqli_query($connection, "SELECT ".$this->primaryKey." from ".$table." ORDER BY ".$this->primaryKey." DESC limit 0,1");
		while($r=mysqli_fetch_array($select)) {	
			return $r[$this->primaryKey];
		}
		return false;
	}
	
	function removeCharsFromHash($value) {
		$value=str_replace("https://", "", $value);
		$value=str_replace("http://", "", $value);
		$value=rtrim($value, "/");
		return $value;
	}
	
	//importování dat - JSON vstup
	function import($connection) {
		$import=array();
		$get = file_get_contents('php://input');
		if(substr($get, 0, 1)!="[") { $get="[".$get."]"; }
		$data = json_decode($get, true, 512, JSON_OBJECT_AS_ARRAY);
		if(!empty($data)) {
			for($i=0; $i<count($data); $i++) {
				$v=$data[$i];
				if(!empty($v[0])) { $v=$v[0]; }
				$lastId=false;
				$hashValue=false;
				
	 			//items
	 			$columns="";
				foreach($this->items as &$item) {
					if($item->getSqlName()!="id" && $item->getSqlName()!="uuid") {
						$value=$v[$item->getImportName()];
						if(empty($value)) { 
							$vex=explode(".", $item->getImportName());
							$value=$v[$vex[0]][$vex[1]];
							if(empty($value)) { $value=$v[$vex[1]]; }
						}
						if($item->getSqlName()==$this->hashColumn) { $value=$this->removeCharsFromHash($value); }

						$selCol="";
						if($columns!="") { $columns.=", "; }
						$columns.=$this->mainSqlTable.'.'.$item->getSqlName()."='".$this->sqlInject($value)."'";
						//generate hash
						if($this->hashColumn && $item->getSqlName()==$this->hashColumn) { 
							$selCol=",".$this->hashColumn; 
							if($value) { $hashValue=hashUrl($value); }
							if($columns!="") { $columns.=", "; }
							$columns.=$this->mainSqlTable.".uuid='".$this->sqlInject($hashValue)."'";
						} 
						//finds if it already exists with the same unique key
						if($this->uniqueKey && $this->uniqueKey==$item->getSqlName()) {
							$select=mysqli_query($connection, "SELECT ".$this->primaryKey.$selCol." from ".$this->mainSqlTable." where ".$this->uniqueKey."='".$this->sqlInject($value)."'");
							while($r=mysqli_fetch_array($select)) {	
								$lastId=$r[$this->primaryKey];
								if(!empty($r['uuid'])) { $hashValue=$r['uuid']; }
								elseif($this->hashColumn) { $hashValue=hashUrl($this->removeCharsFromHash($r[$this->hashColumn])); }
								else { $hashValue=hashUrl($r[$this->primaryKey]); }
							}
						}
					}
				}
				if(!$lastId) {
					$sql="INSERT INTO ".$this->mainSqlTable." SET ".$columns.";\n";
					mysqli_query($connection, $sql);
					$lastId=$this->getLastId($connection);
					if(DEBUG) { echo $sql."\n"; }
				}
				
				//groups
				foreach($this->groups as &$group) {
					$columns="";
					foreach($group->getItems() as &$item) {
						$grImpNam=explode(".", $group->getImportName());
						$value=$v[$grImpNam[0]];
						if(!empty($value)) { $value=$v[$grImpNam]; }
						if(!empty($value)) { 
							if($columns!="") { $columns.=", "; }	
							if(count($grImpNam)>1) { $columns.=$item->getSqlName()."='".$this->sqlInject($value[$grImpNam[1]][$item->getImportName()])."'"; }
							else { $columns.=$item->getSqlName()."='".$this->sqlInject($v[$group->getImportName()][$item->getImportName()])."'"; }
						}
					}					
					if(!empty($columns)) {
						$sql="INSERT INTO ".$group->getSqlTable()." SET ".$columns.", id_".$this->mainSqlTable."=".$lastId.";";
						mysqli_query($connection, $sql);
						if(DEBUG) { echo $sql."\n"; }
						$lastGroupId=$this->getLastId($connection, $group->getSqlTable());
					}
					//subgroups
					$sql="";
					foreach($group->getGroups() as &$subGroup) {
						$columns="";
						foreach($groups->getItems() as &$item) {
							if(!empty($v[$group->getImportName()][$subGroup->getImportName()])) {
								if($columns!="") { $columns.=", "; }
								$columns.=$subGroup->getSqlTable().".".$item->getSqlName()."='".$this->sqlInject($v[$group->getImportName()][$subGroup->getImportName()][$item->getImportName()])."'";
							}
						}
						if(!empty($columns)) {
							$sql.="INSERT INTO ".$subGroup->getSqlTable()." SET ".$columns.", id_".$group->getSqlTable()."=".$lastGroupId.";";
						}
					}
					if(!empty($sql)) {  
						mysqli_query($connection, $sql);
						if(DEBUG) { echo $sql."\n"; }
					}	
				}
			
				//extensions
				$lastExtension=false;
				$lastExtensionId=false;
	 			foreach($this->extensions as &$ext) {
	 				$columns="";
	 				$value=$v[$ext->getImportArray()][0];
	 				if(empty($value)) { $value=$v[$ext->getImportArray()]; }
	 				if(empty($value)) { 
	 					$imp=explode(".", $ext->getImportArray()); 
	 					$value=$v[$imp[0]][0][$imp[1]];
	 					if(empty($value)) { $value=$v[$imp[0]][$imp[1]]; }
	 				}

	 				if($ext->isListOfValues()) {
	 					//skupiny hodnot
	 					foreach($value as $key=>$values) {
	 						$col=array();
	 						foreach($ext->getItems() as &$item) {	
	 							if($item->getSqlName()!="id") { $col[]=$item->getSqlName(); }
	 						}
	 						if(!is_array($values) && $values!="None" && $values!="NaN") {
	 							$sql="INSERT INTO ".$ext->getTable()." SET ".$col[0]."='".$this->sqlInject($key)."', ".$col[1]."='".$this->sqlInject($values)."', ".$ext->getWhereColumn()."=".intval($lastId).$ext->getDateSet().";";
								mysqli_query($connection, $sql);
								if(DEBUG) { echo $sql."\n"; }
	 						} else {
		 						foreach($values as &$val) {
		 							if(!empty($val)) {
										if(is_array($val)) {
											//pokud je vložená hodnota pole
			 								foreach($val as &$v) {
			 									if($v!="None" && $v!="NaN") {
							 						$sql="INSERT INTO ".$ext->getTable()." SET ".$col[0]."='".$this->sqlInject($key)."', ".$col[1]."='".$this->sqlInject($v)."', ".$ext->getWhereColumn()."=".intval($lastId).$ext->getDateSet().";";
													mysqli_query($connection, $sql);
													if(DEBUG) { echo $sql."\n"; }
												}
											}
										} elseif($val!="None" && $val!="NaN") {
					 						$sql="INSERT INTO ".$ext->getTable()." SET ".$col[0]."='".$this->sqlInject($key)."', ".$col[1]."='".$this->sqlInject($val)."', ".$ext->getWhereColumn()."=".intval($lastId).$ext->getDateSet().";";
											mysqli_query($connection, $sql);
											if(DEBUG) { echo $sql."\n"; }
										}
									}
								}
							}
	 					}			
	 				
	 				} elseif(!$ext->getSubGroupId()) {
	 					//extensions
						foreach($ext->getItems() as &$item) {	
							if(empty($value)) { $value=$v[$ext->getImportArray()]; }
							if($item->getSqlName()!="id" && !empty($value)) {
								$value2=$value[$item->getImportName()];
								if(empty($value2)) { 
									$vex=explode(".", $item->getImportName());
									$value2=$value[$vex[0]][$vex[1]];
								}
								if($columns!="") { $columns.=", "; }
								if(!empty(addExtension)) { $columns.=$ext->getTable().".".$item->getSqlName()."='".$this->sqlInject($value2)."'"; }
							}
						}
						if(!empty($columns)) { 
							$sql=$ext->getInsert($lastId).$columns.";";
							mysqli_query($connection, $sql);
							if(DEBUG) { echo $sql."\n"; }
							$lastExtension=$ext->getImportArray(); 
							$lastExtensionId=$this->getLastId($connection, $ext->getTable()); 
						}
					} else {
						//subextension
						$columns=array();
						if((empty($value) || is_array($value)) && !empty($imp)) { 
							$val=$v[$imp[0]][0][$imp[1]][0]; 
							if(empty($val)) { $val=$v[$imp[0]][$imp[1]][0]; }
							if(empty($val)) { $val=$v[$imp[0]][$imp[1]]; }
						}
						
						foreach($ext->getItems() as &$item) {	
							$value2=$v[$lastExtension][0][$ext->getImportArray()];
							if((empty($value2) || is_array($value)) && !empty($imp)) { 
								$value2=$val[$item->getImportName()]; 
							}
							
							if($item->getSqlName()!="id" && !empty($value2) && !is_array($value2)) {
								$maxC=1;	
								if(empty($value2)) { 
									$vex=explode(".", $item->getImportName());
									if(count($vex)==3) { $value2=$value[$vex[0]][$vex[1]][$vex[2]]; }
									elseif(count($vex)==2) { $value2=$value[$vex[0]][$vex[1]]; }
									else { 
										$value2=$value[0][$item->getImportName()]; 
										if(!empty($value[1])) { $maxC=count($value); }
									}
								} 
								for($c=0; $c<$maxC; $c++) {
									if($c>0) { $value2=$value[$c][$item->getImportName()]; }
									if(!empty($value2)) {
										if($columns[$c]!="") { $columns[$c].=", "; }
										if(!empty(addExtension)) { $columns[$c].=$ext->getTable().".".$item->getSqlName()."='".$this->sqlInject($value2)."'"; }
									}
								}
								
							} elseif($item->getSqlName()!="id") { 
								$vex=explode(".", $ext->getImportArray());
								if(count($vex)>1) { 
									$d=$v[$lastExtension][0][$vex[0]][$vex[1]];
									if(empty($d)) { $d=$v[$lastExtension][$vex[0]][$vex[1]]; }
									if(empty($d)) { 
										//addExtensionItem with array value
										$count=count($columns)-1;
										if($count>=0) { $count=0; }
										if($columns[$count]!="") { $columns[$count].=", "; }
										$columns[$count].=$item->getSqlName()."='".$this->sqlInject(implode(" / ", $value2))."'";									
									}											
									
									foreach($d as &$v2) { 
										$sql=$ext->getInsert($lastExtensionId).$item->getSqlName()."='".$this->sqlInject($v2)."';";
										mysqli_query($connection, $sql);
										if(DEBUG) { echo $sql."\n"; }
									}
								
								
								} elseif(is_array($value2)) {

									for($c=0; $c<count($value2); $c++) {
										if($columns[$c]!="") { $columns[$c].=", "; }
										if(!empty($value2[$c][$item->getImportName()])) { $columns[$c].=$item->getSqlName()."='".$this->sqlInject($value2[$c][$item->getImportName()])."'"; }
									}
								}	
							}
						} 
						if(!empty($columns)) { 
							foreach($columns as &$col) {
								$sql=$ext->getInsert($lastExtensionId).$col.";";
								mysqli_query($connection, $sql);
								if(DEBUG) { echo $sql."\n"; }
							}
						} 
					}
					
	 			}
				$import[]=$hashValue;
			}
		}
		return $import;
	}
	
	function sqlInject($value) {
		$value=str_replace('\\\\', '', $value);
		$value=str_replace("'", "\'", $value);
		$value=str_replace('"', '\"', $value);
		$value=stripslashes($value);
		return $value;
	}
	
}


class ApiItem {
	private $name;
	private $importName;
	private $sqlName;
	private $group;
	
	function __construct($name, $importName=false, $sqlName=false, $group=false) {
		if(!$importName) { $importName=$name; }
		if(!$sqlName) { $sqlName=$name; }
		$this->name=$name;
		$this->importName=$importName;
		$this->sqlName=$sqlName;
		$this->group=$group;
	
	}

	function getName() { return $this->name; }
	function getSqlName() { return $this->sqlName; }
	function getImportName() { return $this->importName; }
}



class ApiGroup {
	private $name;
	private $importArray;
	private $sqlTable;
	private $items;
	private $groups;
	
	function __construct($name, $importArray=false, $sqlTable=false) {
		if(!$importArray) { $importArray=$name; }
		if(!$sqlTable) { $sqlTable=$name; }
		$this->name=$name;
		$this->importArray=$importArray;
		$this->sqlTable=$sqlTable;
		$this->items=array();
		$this->groups=array();	
	}
	
	function addItem($name, $importName=false, $sqlName=false) {
		$this->items[]=new ApiItem($name, $importName, $sqlName);
	}
	
	function addGroup($name, $importArray=false, $sqlTable=false) {
		$this->groups[]=new ApiGroup($name, $importArray, $sqlTable);
	}
	
	function getName() { return $this->name; }
	function getImportName() { return $this->importArray; }
	function getSqlTable() { return $this->sqlTable; }
	function getItems() { return $this->items; }
	function getGroups() { return $this->groups; }
	
	
	function getSqlColumns() {
		$columns="";
		foreach($this->items as &$item) {
			if($columns!="") { $columns.=", "; }
			$columns.=$this->sqlTable.".".$item->getSqlName();
		}
		
		//podskupiny
		foreach($this->groups as &$group) {
			if($columns!="") { $columns.=", "; }
			$columns.=$group->getSqlColumns();
		}
		return $columns;
	}
	
	function getSqlJoin($parentTable="") {
		//$join="LEFT JOIN ".$this->sqlTable." ON ".$this->sqlTable.".id_".$parentTable."=".$parentTable.".id";
		$join="LEFT JOIN ".$this->sqlTable." ON ".$this->sqlTable.".id_".$parentTable."=".$parentTable.".id 
			AND NOT EXISTS (
			     SELECT 1 FROM ".$this->sqlTable." t2
			     WHERE t2.id_".$parentTable."=".$parentTable.".id
			     AND t2.id >  ".$this->sqlTable.".id
			   )";
		foreach($this->groups as &$group) {
			$join.=" ".$group->getSqlJoin($this->sqlTable);
		}
		return $join;
	}
	
	function getArrayColumns($r) {
		$arr=array();
		foreach($this->items as &$item) {
			$arr[$item->getName()]=$r[$item->getSqlName()];
		}
		//podskupiny
		foreach($this->groups as &$group) {
			$arr[$group->getName()]=$group->getArrayColumns();
		}
		return $arr;
	}

}


class ApiExtension {
	private $id;
	private $name;
	private $table;
	private $whereColumn;
	private $importArray;
	private $subGroupId;
	private $listOfValues;
	private $idInArray;
	private $orderby;
	private $date;
	private $dateColumn;
	private $items;
	
	function __construct($id, $name, $table, $whereColumn=false, $importArray=false, $subGroupId=false, $listOfValues=false, $idInArray="id", $orderby="id ASC") {
		$this->id=$id;
		$this->name=$name;
		$this->table=$table;
		$this->whereColumn=$whereColumn;
		$this->importArray=$importArray;
		$this->subGroupId=$subGroupId;
		$this->listOfValues=$listOfValues;
		$this->idInArray=$idInArray;
		$this->orderby=$orderby;
		$this->items=array();
		$this->addItem("id");	//přidá povinné pole ID - pro zařazení podskupin
	}
	
	function addItem($name, $importName=false, $sqlName=false) {
		$this->items[]=new ApiItem($name, $importName, $sqlName);
	}
	
	function setExtensionDate($date, $column) {
		$this->date=$date;
		$this->dateColumn=$column;
	}
	
	function getId() { return $this->id; }
	function getName() { return $this->name; }
	function getWhereColumn() { return $this->whereColumn; }
	function getIdInArray() { return $this->idInArray; }
	function getTable() { return $this->table; }
	function getImportArray() { return $this->importArray; }
	function getItems() { return $this->items; }
	function getSubGroupId() { return $this->subGroupId; }
	function isListOfValues() { return $this->listOfValues; }
	
	function sqlInject($value) {
		$value=str_replace('\\\\', '', $value);
		$value=str_replace("'", "\'", $value);
		$value=str_replace('"', '\"', $value);
		$value=stripslashes($value);
		return $value;
	}
	
	function getDateSet() {
		$date="";
		if(!empty($this->dateColumn)) { $date=", ".$this->dateColumn."='".$this->sqlInject($this->date)."'"; }
		return $date;
	}
	
	function getInsert($id) {
		return "INSERT INTO ".$this->table." SET ".$this->whereColumn."=".intval($id).$this->getDateSet().", ";
	}
	
	function getSelect($whereArray) {
		$columns=$this->whereColumn;
		//položky
		foreach($this->items as &$item) {
			if($columns!="") { $columns.=", "; }
			$columns.=$item->getSqlName();
		}
		$sql="SELECT ".$columns." from ".$this->table." WHERE ".$this->whereColumn." IN(".implode(',',$whereArray).") ORDER BY ".$this->whereColumn." ASC, ".$this->orderby;
		//echo $sql."\n"; 
		return $sql;
	}
}
?>
