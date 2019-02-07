<?php

$jrSQL_REPLACE=1;
$jrSQL_IGNORE=-1;
$jrSQL_DEBUG=0;

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



 function sg_get_schema() {   //DO UZUPELNIENIA
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



 
 function sq_get_table($table) { //DO UZUPELNIENIA
 // podbiera liste pol tabeli
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
    global $db;
    $Str=pg_escape_string ($db,$Str);
    //$Str=str_replace('"','\"',$Str);
    return "'".$Str."'";
    } // Quote
 
  function sq_Quote_Array($arr) // Double-quoting only
    {
		global $db;
    	foreach ($arr as $a1=>$a2) $arr[$a1]="'".sq_Quote(trim($a2))."'";
			return $arr;
    } // Quote
 
 function sq_last() {
	 global $db;
	 return pg_last_oid ($db);
 }
 

 function sq_insert($table,$arr,$updateignore=0,$no_keys=0,$print_only=0) {
	global $db,$jrSQL_DEBUG;
	$va=$ke=array_keys($arr);
	foreach ($ke as $k1=>$k2) {
			$ke[$k1]="\"$k2\"";
			//$va[$k1]=($arr[$k2]===NULL ||$arr[$k2]==="NULL" ||$arr[$k2]==="" )?"NULL":'"'.addcslashes($arr[$k2],'"').'"';
			$va[$k1]=($arr[$k2]===NULL ||$arr[$k2]==="NULL" ||$arr[$k2]==="" )?"NULL":"'".$db->escapeString($arr[$k2])."'";
			
	}
	if ($no_keys) {
		$se="INSERT ".($updateignore>0?"OR REPLACE ":"").($updateignore<0?"OR IGNORE ":"")."INTO $table VALUES (".join(",",$va).");";
	} else {
		$se="INSERT ".($updateignore>0?"OR REPLACE ":"").($updateignore<0?"OR IGNORE ":"")."INTO $table(".join(",",$ke).") VALUES (".join(",",$va).");";
	}

	if ($jrSQL_DEBUG) {
		foreach ($se as $se1)
			print "DB:".__LINE__.":$se1\n";
		//die();
	}

	return ($print_only)? $se : $db->query($se);
 }
 
 
 function sq_query($query,$key=false,$as=SQLITE3_ASSOC) {
	global $db,$jrSQL_DEBUG;
	#	print "DB:$query\n";
	if ($jrSQL_DEBUG) {
		print "DB:".__LINE__.":$query\n";
		//die();
	}
	$rs = pg_query($db, $query);
  if (!$rs) {
    die ("An error occurred.\n");
    exit;
  }
	if ($rs===TRUE || $rs===FALSE) return array();
	$a=sq_table($rs,$as);
	if ($key!==false) {
		$b=array();
		foreach ($a as $c) {
			if (isset($b[$key])) die ("Błąd primary key $key='".$c[$key]."' w zapytaniu $query\n\n!!!\n");
			$b[$c[$key]]=$c;
		}
		return $b;
	}
	return $a;
 }
 
 function sq_table($rs,$as=1) {
 /*
 SQLITE3_ASSOC: returns an array indexed by column name as returned in the corresponding result set
 SQLITE3_NUM: returns an array indexed by column number as returned in the corresponding result set, starting at column 0
 SQLITE3_BOTH: returns an array indexed by both column name and number as returned in the corresponding result set, starting at column 0
 */
 
  $a=array();
  while ($row = pg_fetch_assoc($rs)) {
    $a[]=$row;
  }
  return $a;
 }
 


 function jr_table($tab,$show_var_name=1,$show_line_num=1,$header="",$footer=""){
		$th=($show_line_num)?"<th>No.</th>":"";
		$tf='';
		$colspan=($show_line_num)?1:0;
		foreach ($tab as $a) {
			foreach ($a as $k=>$v) {
				$th.="<th>$k</th>";
				$colspan++;
				}
			break;
		}
		if ($show_var_name) {
			$th="<tr>$th</tr>";
		} else $th='';
		
		if ($header) $th="<tr><th colspan='$colspan'>$header</th></tr>$th";
		$th="<thead>$th</thead>\n";
		if ($footer) $tf="<tfoot><tr><td align='right' colspan='$colspan'>$footer</td></tr></tfoot>\n";
		
		$tr="";$i=0;
		foreach ($tab as $r) {
			$i++;$tb=($show_line_num)?"<td>$i</td>":"";
			foreach ($r as $v) $tb.="<td>$v</td>";
			$tr.="<tr>$tb</tr>\n";
		}
		$tb="\n<tbody>\n$tr\n</tbody>\n";
		
		return "<table border='1'>\n$th\n$tb\n$tf\n</table>";
		
 }
 
 	function db_arrayquery($q,$foo,$show_only=0) {
	/*
		db_arrayquery($q,function (&$int,$v,$k) {
		//if (!isset($int)) $int=array();
		if (!isset($int[$v['id_linia']])) $int[$v['id_linia']]=array();
		array_push($int[$v['id_linia']],$v);  //important $int will be the return
	});
	*/
		$internal=array();
		$g=sq_query($q);
		if ($show_only) {
			foreach ($g as $k=>$v) {
				print_r($v);die();
			}
		}
		if ($foo) foreach ($g as $k=>$v) {
			if ($foo($internal,$v,$k,$g)) break ;
		//	print "$k;";
		}
		return $internal;
	}

 
 
?>
