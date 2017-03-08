<?php

$jrSQL_REPLACE=1;
$jrSQL_IGNORE=-1;
/*	na start:
 *  $path = 'C:\Users\rusin\Documents\sqlite\teletransmisja.db3';
 $  db = new SQLite3($path);
 */

//PRAGMA empty_result_callbacks = ON;
//.headers on
/*
sqlite> .header on
sqlite> .mode column
sqlite> create table ABC(A TEXT, B VARCHAR);
sqlite> pragma table_info(ABC);
cid         name        type        notnull     dflt_value  pk
----------  ----------  ----------  ----------  ----------  ----------
0           A           TEXT        0                       0
1           B           VARCHAR     0                       0
*/


// $path = 'C:\Users\rusin\Documents\sqlite\teletransmisja.db3';
// $db = new SQLite3($path);
// print_r(sq_get_schema());
// print_r(sq_get_table("alu_vc12"));

// select * from (select "") left join my_table_to_test b on -1 = b.rowid;


# sq_query('select * from int_aters');
#die();



 function sq_get_schema() {
 //pobiera liste tabel
 /**
     [35] => Array
        (
            [type] => table
            [name] => alu_vc12
            [tbl_name] => alu_vc12
            [rootpage] => 30
            [sql] => CREATE TABLE [alu_vc12] ([type] VARCHAR, [name] VARCHAR, [r
oute] VARCHAR, [no] INTEGER, [endpoint] VARCHAR, [r] VARCHAR, [s] VARCHAR, [b] V
ARCHAR, [p] VARCHAR, [c] VARCHAR, [au4] INT, [vc3] INT, [vc2] INT, [vc1] INT, [k
lm] VARCHAR, [alu_vc] VARCHAR, [alu_loc] VARCHAR, [raw] VARCHAR)
        )
 */
  $q="select * from sqlite_master";
	$w=sq_query($q);
	$a=array();
	foreach ($w as $w1) {
    if (!isset ($a[$w1['type']])) $a[$w1['type']]=array();
    $a[$w1['type']][$w1['tbl_name']]=$w1;
	}
	return $a;
 }



 
 function sq_get_table($table) {
 // podbiera list� p�l tabeli
 /**
 
   [13] => Array
       (
           [cid] => 13
           [name] => vc1
           [type] => INT
           [notnull] => 0
           [dflt_value] =>
           [pk] => 0
       )

   [14] => Array
       (
           [cid] => 14
           [name] => klm
           [type] => VARCHAR
           [notnull] => 0
           [dflt_value] =>
           [pk] => 0
 */
  $q="pragma table_info($table)";
  return sq_query($q);
 }
 
 function sq_Quote($Str) // Double-quoting only
    {
    $Str=str_replace('"','\"',$Str);
    return '"'.$Str.'"';
    } // Quote
 
 function sq_last() {
	 global $db;
	 return $db->lastInsertRowID();
 }

 function sq_insert($table,$arr,$updateignore=0,$no_keys=0) {
 global $db;
	$va=$ke=array_keys($arr);
	foreach ($ke as $k1=>$k2) {
			$ke[$k1]="\"$k2\"";
			$va[$k1]=($arr[$k2]===NULL ||$arr[$k2]==="NULL" )?"NULL":'"'.addcslashes($arr[$k2],'"').'"';
	}
	if ($no_keys) {
		$se="INSERT ".($updateignore>0?"OR REPLACE ":"").($updateignore<0?"OR IGNORE ":"")."INTO $table VALUES (".join(",",$va).");";
	} else {
		$se="INSERT ".($updateignore>0?"OR REPLACE ":"").($updateignore<0?"OR IGNORE ":"")."INTO $table(".join(",",$ke).") VALUES (".join(",",$va).");";
	}

//	print_r($se);print"\n";
	//die();

	return $db->query($se);
#	print_r($se);die();
#	$in=$db->prepare($se);
#	foreach ($arr as $k=>$v) {
#		$in->bindValue(":".($k+1),$v);
#	}
#	$in->execute();
#	print_r($arr);
 }
 
 function sq_query($query,$key=false,$as=SQLITE3_ASSOC) {
	global $db;
#	print "DB:$query\n";
	$rs= $db->query($query);
	if ($rs===TRUE || $rs===FALSE) return array();
	$a=sq_table($rs,$as);
	if ($key!==false) {
		$b=array();
		foreach ($a as $c) {
			if (isset($b[$key])) die ("B��d primary key $key='".$c[$key]."' w zapytaniu $query\n\n!!!\n");
			$b[$c[$key]]=$c;
		}
		return $b;
	}
	return $a;
 }
 
 function sq_table($rs,$as=SQLITE3_ASSOC) {
 /*
 SQLITE3_ASSOC: returns an array indexed by column name as returned in the corresponding result set
 SQLITE3_NUM: returns an array indexed by column number as returned in the corresponding result set, starting at column 0
 SQLITE3_BOTH: returns an array indexed by both column name and number as returned in the corresponding result set, starting at column 0
 */
 
  $a=array();
  while ($row = $rs->fetchArray($as)) {
    $a[]=$row;
  }
  return $a;
 }
 
 function fetchObject($sqlite3result, $objectType = NULL) { 
    $array = $sqlite3result->fetchArray(); 

    if(is_null($objectType)) { 
        $object = new stdClass(); 
    } else { 
        // does not call this class' constructor 
        $object = unserialize(sprintf('O:%d:"%s":0:{}', strlen($objectType), $objectType)); 
    } 
    
    $reflector = new ReflectionObject($object); 
    for($i = 0; $i < $sqlite3result->numColumns(); $i++) { 
        $name = $sqlite3result->columnName($i); 
        $value = $array[$name]; 
        
        try { 
            $attribute = $reflector->getProperty($name); 
            
            $attribute->setAccessible(TRUE); 
            $attribute->setValue($object, $value); 
        } catch (ReflectionException $e) { 
            $object->$name = $value; 
        } 
    } 
    
    return $object; 
}
?>
