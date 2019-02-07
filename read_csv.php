<?php
date_default_timezone_set ("Europe/Warsaw"); 
$__normalized_fields=false;
$__normalized_fields_file="jr_normalized_fields.txt";
$__pola=array();

function __get_normalized_fields($fn=null) {
	global $__normalized_fields,$__normalized_fields_file;
	$t=file($fn?$fn:$__normalized_fields_file);
	$__normalized_fields=array();
	foreach ($t as $f) {
		if (preg_match('/^(.+?)\\.(.+?)\\s+(.+?)$/m', $f, $w)) {
			if (!isset($__normalized_fields[$w[1]])) $__normalized_fields[$w[1]]=array();
			$__normalized_fields[$w[1]][$w[2]]=$w[3];
		}
	}
}

function __put_normalized_fields($tab_name=null,$tab=null,$fn=null) {
	global $__normalized_fields,$__normalized_fields_file;
	$fn=$fn?$fn:$__normalized_fields_file;
	if ($__normalized_fields===false) __get_normalized_fields($fn);
	$w=array();
	if ($tab_name && $tab) {
		if (!isset($__normalized_fields[$tab_name])) $__normalized_fields[$tab_name]=array();
		foreach ($tab as $v) {
			if (!isset ($__normalized_fields[$tab_name][$v['field']])) $__normalized_fields[$tab_name][$v['field']]=$v['field'];
		}
	}
//	print_r($__normalized_fields);
	foreach ($__normalized_fields as $t=>$t1) {
		foreach ($t1 as $t2=>$t3) array_push($w,"$t.$t2\t$t3");
	}
	file_put_contents($fn, join ("\n",$w));	
//	die();
}

function __norm($tab_name,$field) {
	global $__normalized_fields;
	if ($__normalized_fields===false) return $field;
	if (!isset($__normalized_fields[$tab_name])) $__normalized_fields[$tab_name]=array();	
	if (!isset($__normalized_fields[$tab_name][$field])) $__normalized_fields[$tab_name][$field]=$field;
	return $__normalized_fields[$tab_name][$field];
	
}

//die(normalize("żźćńąśłóęŻŹĆŃĄŚŁÓĘ"));

function read_txt_file($file_name,$foo,$foo_fields=null,$csv=1) {
/* Example:
	$w=read_txt_file($file_name,function (&$int,$data_arr,$row,$line,$no_columns,$raw_line) {
		print "$row:$line:$no_columns ".substr($raw_line,0,30)."\n";
		array_push($int,$dane_arr);		// important if return array is needed !!! $int is the return table
	});
	print_r($w[40]);
*/
	$internal=array();$column=0;$line=0;$count=0;
	$fopen_=fopen ($file_name,"r");
	if ($fopen_) {
		 while (($buffer = fgets($fopen_, 4096)) !== false) {		
			$line++;
			if (!$column) $column=count(explode("\t",$buffer));
			while (true) {
				$act=count(explode("\t",$buffer));$tmp=true;
				if ($act<$column) { 
					//echo "$column:$act:$line:\t".substr($buffer,1, 40)."\n";
					if (($tmp=fgets($fopen_, 4096)) === false) break;
					$line++;
					$buffer.=$tmp;
					
				} else break;
			}
			$count++;
			if ($foo && $tmp) {  //czy jest funkcja i czy nie pusta linia
				$tmp=explode("\t",$buffer);
				if ($csv) foreach ($tmp as $t1=>$t2) if (preg_match('/^\\".+\\"$/sm',$t2)) {
					$t2=substr($t2,1,-1);
					$t2=str_replace('""','"',$t2);
					$tmp[$t1]=$t2;
				}
				if ($foo_fields) foreach ($tmp as $t1=>$t2) $tmp[$t1]=$foo_fields($t2,$t1,$count);
				if ($foo($internal,$tmp,$count,$line,$column,$buffer)) break ;
			}
		}
		if (!feof($fopen_)) {
				echo "Error: unexpected fgets() fail\n";
			}
		fclose($fopen_);
	}
	return $internal;
}


function utf2en ($string){ 
 	$utf8  =array("ą","ć","ę","ł","ń","ó","ś","ż","ź","Ą","Ć","Ę","Ł","Ń","Ó","Ś","Ż","Ź","-");
	$normal=array("a","c","e","l","n","o","s","z","z","A","C","E","L","N","O","S","Z","Z","-");//"–"
    return str_replace($utf8, $normal, $string); 
} 

function normalize ($string,$init_if_number="f_"){ 
    $a = "\"\\/',.;:[](){}+-";
    $b =   '                '; 
    //$string = utf8_decode(utf2en($string));     
    $string = utf2en($string);  
   
    //$string = strtr($string, utf8_decode($a), $b); 
    $string = preg_replace('/[^a-zA-Z0-9]/', ' ',trim(strtolower($string)));
    $string = preg_replace('/\s+/', '_',trim(strtolower($string)));
    if (preg_match("/^[0-9]/",$string)) $string=$init_if_number.$string;
    return $string;
   // return utf8_encode($string); 
} 


function check_type($w,$start=1,$kolumny=null) {
    $a=array();$nul=array();$len=array();$wyn=array();
	foreach ($w as $k=>$v) if ($k>=$start) {
		if (!$a) {
			//ustaw puste
			foreach ($v as $k1=>$v2) {
				$a[$k1]="";
				$nul[$k1]="";
				$len[$k1]="";
				$wyn[$k1]=array();
			}
		}
		foreach ($v as $k1=>$v2) {  
			$len[$k1]=max($len[$k1],strlen($v2));$v2=trim($v2);
			if ($v2 && !in_array($v2,$wyn[$k1])) array_push($wyn[$k1],$v2);
			if (!$nul[$k1] && (!$v2 || in_array(strtolower($v2),array('nil','nul','null')))) {
				$nul[$k1]="N";
				continue;
			}
			if (preg_match("/^\d{4}[\/-]\d{2}[\/-]\d{2}$|^\d{2}[\/-]\d{2}[\/-]\d{4}$|^\d{2}[\/-]\d{2}[\/-]\d{2}$/",$v2)) {
				switch ($a[$k1]) { 	//date
					case "":
					case "D":
						$a[$k1]="D";
						break;
					case "I":
					case "N":
					case "R":
						$a[$k1]="T";
						break;
				}
				continue;
			}
			if (preg_match("/^-?\d+[\.]\d+[\,]\d+$|^-?\d+[\.\, ]\d+$|^-?\d+[ ]\d+[\.\,]\d+$/",$v2)) {
				switch ($a[$k1]) { 	//real
					case "":
					case "R":
						$a[$k1]="R";
						break;
					case "I":
					case "N":
					case "D":
						$a[$k1]="T";
						break;
				}
				continue;
			}
			if (preg_match("/^\d+$/",$v2)) {
				switch ($a[$k1]) { 	//integer
					case "":
					case "N":
						$a[$k1]="N";
						break;
					case "I":
						$a[$k1]="I";
						break;
					case "R":
						$a[$k1]="R";
						break;
					case "D":
						$a[$k1]="T";
						break;
				}
				continue;
			}
			if (preg_match("/[a-zA-Z]/",trim(utf2en($v2)))) {
					$a[$k1]="T";
			}
		}
	
	}
	$w=array();
	foreach ($a as $k=>$v) {
		$w[$k]=array('poz'=>$k,'type'=>$v,"null"=>$nul[$k],'length'=>$len[$k],'values'=>count($wyn[$k]));
		if (count($wyn[$k])<4) $w[$k]['val']=join("+||+",$wyn[$k]);
		if (isset($kolumny) && isset($kolumny[$k])) $w[$k]['field']=$kolumny[$k];
	}
	return $w;
}

function csv_sql_header($text) {
	return	
	"\n\n\n-- #######################################################".
	"\n-- ## $text".
	"\n-- #######################################################\n";
}

function csv_create_table($table_name,$tab,$pk='') {
  global $__pola;
	$typy=array("D"=>"DATE","I"=>"INTEGER","N"=>"NUMERIC","R"=>"REAL","T"=>"TEXT","G"=>"GEOMETRY","DO"=>"double precision");
	foreach ($tab as $v) print "-- ".join (":",$v)."\n";


	$create=array(); $pola=array();$field_create=array();
	foreach ($tab as $v) {
	//	print_r($v);die();
    $_nam=__norm($table_name,$v["field"]);
    if (in_array($_nam,array("lat","lon"))) $v['type']="DO";
    
		array_push($create,"\t".$_nam."\t".$typy[$v['type']?$v['type']:"T"].(($v["field"]==$pk)?" PRIMARY KEY":""));
		array_push($pola,$_nam);
		array_push($field_create,'INSERT INTO history.fields ("schemat","tabela","pole") VALUES ('."'ipm','".$table_name."','".$_nam."');");
	}
	array_push($create,"\t__last_touch BIGINT");
	array_push($pola,"__last_touch");
	array_push($create,"\t__last_update BIGINT");
	array_push($create,"\t__user_update BIGINT");
	array_push($pola,"__user_update");

	array_push($create,"\t__geom GEOMETRY");
	$create=str_replace("%%",join(",\n",$create),"CREATE TABLE IF NOT EXISTS ipm.$table_name  (
	%%
	);");
	$create.="\n".join("\n",$field_create);
	$create.=str_replace(array("%%%%","@@"),array($table_name,$pk),
	'
 DROP TRIGGER ipm.%%%%_tr_change ON ipm.%%%%;

CREATE TRIGGER %%%%_change 
    BEFORE UPDATE 
    ON ipm.%%%%
    FOR EACH ROW
    EXECUTE PROCEDURE history.tr_change("@@");
    ');
    
	if (1) $create.=str_replace(array("%%%%","@@"),array($table_name,$pk),
	'
 DROP TRIGGER %%%%_geom ON ipm.%%%%;
CREATE TRIGGER %%%%_geom
    BEFORE INSERT OR UPDATE OF lat, lon
    ON ipm.%%%%
    FOR EACH ROW
    EXECUTE PROCEDURE ipm.update_geom_point();
');
	
	$create="\n".$create.csv_sql_header("CREATE $table_name FIELDS")."--".join(",",$pola)."\n";
	$__pola=$pola;
	return csv_sql_header("CREATE $table_name").$create;
}

/*
https://stackoverflow.com/questions/23788530/detecting-column-changes-in-a-postgres-update-trigger

1

http://www.postgresql.org/docs/9.3/static/plpython-trigger.html

TD["table_name"]
I do exactly the same type of notify, I loop through all of the columns like this:

    for k in TD["new"]:
        if TD["old"][k] != TD["new"][k]:
            changed.append(k)
changed.append(k) builds my notification string. Somewhere else I do a listen, then broadcast the results out pub/sub to web socket clients.

-g

https://www.postgresql.org/docs/9.4/plpython-util.html
*/


function csv_insert_table($table_name,$tab,$dane,$from=1,$pk='',$__jrtrack='',$__jruser=1) {
  global $__pola,$__normalized_fields;
  if (!$__jrtrack) $__jrtrack=date("YmdHis");
	$w=array();$wk=array();  #$w wartości $wk klucze
	foreach ($dane as $l=>$r) if ($l>=$from) {
    array_push($r,$__jrtrack);
    array_push($r,$__jruser?$__jruser:1);
    $wupt=array();
		foreach ($r as $k=>$v) {
      #print_r($__normalized_fields);die();
      #print_r($__pola);die();
      if ($pk) $pkn=array_search($pk,$__pola);
      #print_r($r);die("$pk==$pkn;");
      
      if ($v=="ND") {
        
      }
			if (!in_array($v,array("NULL","TRUE","FALSE"))) $r[$k]="'".str_replace("'","''",trim($v))."'";
			$wk[$k]='"'.$__pola[$k].'"';
			$wupt[$k]=$wk[$k]."=".$r[$k];
		}
		if ($pk) {
        array_push($w,"UPDATE ipm.$table_name SET ".join(",",$wupt)." WHERE \"$pk\"=".$r[$pkn].";");
		}
		array_push($w,"INSERT INTO ipm.$table_name (".join(",",$wk).") VALUES (".join(",",$r).");");
	}
	return csv_sql_header("INSERT $table_name").join("\n",$w);
}

function csv_find_column_names($dane,$row=0) {
	$kolumny=array();
	if (!isset($dane[$row])) return false;
	foreach ($dane[$row] as $k=>$v) {
		$v=normalize($v);
		if (!$v) $v="f_".$k;
		$kolumny[$k]=$v;
	}
	return $kolumny;
}

function csv_normalize_date($v) {
	//z yyyy-dd-mm lub mm-dd-yyyy robi yyyy-dd-mm
	// - może być .,/-
	$v = preg_replace('%^(\\d{1,2})[\\.\\,\\/-](\\d{1,2})[\\.\\,\\/-](\\d{4})$|^(\\d{4})[\\.\\,\\/-](\\d{2})[\\.\\,\\/-](\\d{2})$%sm', '\\3\\4-\\2\\5-\\1\\6', $v);
	$v = preg_replace('%^(\\d{4})[\\.\\,\\/-](\\d{1})[\\.\\,\\/-]%sm', '\\1-0\\2-', $v); // jak miesiąc z jedną cyferką
	$v = preg_replace('%^(\\d{4})[\\.\\,\\/-](\\d{2})[\\.\\,\\/-](\\d{1})$%sm', '\\1-\\2-0\\3', $v); // jak dzień z jedną cyferką
	return $v;
}
function csv_normalize_date_us($v) {
	//z yyyy-dd-mm lub mm-dd-yyyy robi yyyy-dd-mm
	// - może być .,/-
	$v = preg_replace('%^(\\d{1,2})[\\.\\,\\/-](\\d{1,2})[\\.\\,\\/-](\\d{4})$%sm', '\\3-\\1-\\2', $v);
	$v = preg_replace('%^(\\d{4})[\\.\\,\\/-](\\d{1})[\\.\\,\\/-]%sm', '\\1-0\\2-', $v); // jak miesiąc z jedną cyferką
	$v = preg_replace('%^(\\d{4})[\\.\\,\\/-](\\d{2})[\\.\\,\\/-](\\d{1})$%sm', '\\1-\\2-0\\3', $v); // jak dzień z jedną cyferką
	
	return $v;
}



function csv_normalize_real($v) {
	//z 00,0 robi 00.0
	$v = preg_replace('/^(-?\d+)[\.\,](\d+)$/sm', '\1.\2', $v);
	return $v;
}
		
?>