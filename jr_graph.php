<?php


function do_graph ($name,$data,$dir='pic') {
$template="digraph prof {
graph [ratio=fill,overlap=prism]	;
edge [dir=none,labelfontsize=7];
node [shape=box];
%%
}";
ob_start();
print str_replace ("%%",join("\n",$data),$template);
file_put_contents("_siec.txt",ob_get_contents());
$fil=strtr($name,
  "#$%^&*()!@#$%^<>?:{}| =+-",
  "_________________________");
print "dograph:$name $fil\n";

#file_put_contents($dir."\\".$fil.".txt",ob_get_contents());
passthru('e:\graphviz\bin\dot.exe _siec.txt -Tpng -o_siec.png');
rename ("_siec.png",$dir."\\".$fil.".png");
//passthru('e:\graphviz\bin\dot.exe _siec.txt -Teps -o_siec.eps');
//rename ("_siec.eps",$dir."\\".$fil.".eps");
#die();
rename ("_siec.txt",$dir."\\".$fil.".txt");
return $dir."\\".$fil.".png";

}


function subGraph($label,$body,$numb=0) {
static $c=1;
if (!$numb) $numb=$c++;
if (!is_array($label)) $label=array($label);
if (!is_array($body)) $body=array($body);

$a="
 subgraph cluster%1% {
 label=\"%2%\"
 %3%
 }
";
 $a=str_replace ("%2%",join("\n",$label),$a);
 $a=str_replace ("%3%",join("\n",$body),$a);
 $a=str_replace ("%1%",$numb,$a);
#print"\n\n\n-------\n";var_dump($label);var_dump ($body);var_dump ($a);die();

 return $a;
}
 

?>