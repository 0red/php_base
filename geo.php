<?php
/*
//svg double lines
https://stackoverflow.com/questions/31096994/google-map-polyline-dashed-and-double-lines
https://codepen.io/hanger/pen/OXMXXP?editors=1000
//


http://www.jeasyui.com/demo/main/index.php?plugin=ComboTree&theme=default&dir=ltr&pitem=



//geo.php based on https://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php


https://stackoverflow.com/questions/20231258/minimum-distance-between-a-point-and-a-line-in-latitude-longitude
*/

/*
const base62 = {
  //https://lowrey.me/encoding-decoding-base-62-in-es6-javascript/
  charset: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    .split(''),
  encode: integer => {
    if (integer === 0) {
      return 0;
    }
    let s = [];
    while (integer > 0) {
      console.log(integer+" "+base62.charset[integer % 62]);
      s = [base62.charset[integer % 62], ...s];
      integer = Math.floor(integer / 62);
    }
    return s.join('');
  },
  decode: chars => chars.split('').reverse().reduce((prev, curr, i) =>
    prev + (base62.charset.indexOf(curr) * (62 ** i)), 0)
};
*/

class base62 {
  private static $charset='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  public function encode($i,$base=62) {
    if (!$i) return 0;
    $s=array();
    while ($i>0) {
      array_push($s,self::$charset[$i % $base]);
      $i=floor($i/$base);
    }
    return strrev(join("",$s));
  }
  public function decode($s,$base=62) {
    $i=strlen($s)-1;$w=0;$s=strrev($s);
    while ($i>=0) {
      $w= $w+ strpos (self::$charset,$s[$i])*pow($base,$i--);
    }
   return $w; 
  }  
};

//$x=10;
//echo base62::encode(1000,$x)."-enc\n";
//echo base62::decode(base62::encode(1000,$x),$x)."-dec\n";

$__GEOLINE=array(9,1000,700000); array_push ($__GEOLINE,pow(10,$__GEOLINE[0]));

function geoline_encode($lat,$lon,$linia,$km) {
  global $__GEOLINE;
  $dec=9;
  $linia=str_pad(""+$linia,3,"0",STR_PAD_LEFT);
  $km=($km+10)*1000;
  $km=str_pad(""+$km,6,"0",STR_PAD_LEFT);
  $lat=explode(".",""+$lat);
  $lon=explode(".",""+$lon);
  if (!isset($lat[1])) $lat[1]=0;
  if (!isset($lon[1])) $lon[1]=0;
  $lat[0]=($lat[0]+180-43) % 180;
  $lon[0]=($lon[0]+360-13) % 360;
  $lat[1]=strrev(str_pad($lat[1],$dec,"0"));
  $lon[1]=strrev(str_pad($lon[1],$dec,"0"));
  //$x=$linia.
  
  
  print "linia $linia km:$km\n"; print_r($lat);print_r($lon);print_r($__GEOLINE);
}
//geoline_encode(54.22,33,986,-1.456);

function parseFloat($ptString) { 
            if (strlen($ptString) == 0) { 
                    return false; 
            } 

            $pString = str_replace(" ", "", $ptString); 

            if (substr_count($pString, ",") > 1) 
                $pString = str_replace(",", "", $pString); 

            if (substr_count($pString, ".") > 1) 
                $pString = str_replace(".", "", $pString); 

            $pregResult = array(); 

            $commaset = strpos($pString,','); 
            if ($commaset === false) {$commaset = -1;} 

            $pointset = strpos($pString,'.'); 
            if ($pointset === false) {$pointset = -1;} 

            $pregResultA = array(); 
            $pregResultB = array(); 

            if ($pointset < $commaset) { 
                preg_match('#(([-]?[0-9]+(\.[0-9])?)+(,[0-9]+)?)#', $pString, $pregResultA); 
            } 
            preg_match('#(([-]?[0-9]+(,[0-9])?)+(\.[0-9]+)?)#', $pString, $pregResultB); 
            if ((isset($pregResultA[0]) && (!isset($pregResultB[0]) 
                    || strstr($preResultA[0],$pregResultB[0]) == 0 
                    || !$pointset))) { 
                $numberString = $pregResultA[0]; 
                $numberString = str_replace('.','',$numberString); 
                $numberString = str_replace(',','.',$numberString); 
            } 
            elseif (isset($pregResultB[0]) && (!isset($pregResultA[0]) 
                    || strstr($pregResultB[0],$preResultA[0]) == 0 
                    || !$commaset)) { 
                $numberString = $pregResultB[0]; 
                $numberString = str_replace(',','',$numberString); 
            } 
            else { 
                return false; 
            } 
            $result = (float)$numberString; 
            return $result; 
}   

function xrange($start, $limit, $step = 1) {
    if ($start < $limit) {
        if ($step <= 0) {
            throw new LogicException('Step must be +ve');
        }

        for ($i = $start; $i <= $limit; $i += $step) {
            yield $i;
        }
    } else {
        if ($step >= 0) {
            throw new LogicException('Step must be -ve');
        }

        for ($i = $start; $i >= $limit; $i += $step) {
            yield $i;
        }
    }
}

function modf($x) {
    $m = fmod($x, 1);
    return [$m, $x - $m];
}

$JR_PROMIEN_ZIEMI=6371.0; //km

function getPathLength($lat1,$lng1,$lat2,$lng2)
{
	global $JR_PROMIEN_ZIEMI;
    $R = $JR_PROMIEN_ZIEMI*1000; // '6371000'; //# radius of earth in m
    $lat1rads = deg2rad($lat1);
    $lat2rads = deg2rad($lat2);
    $deltaLat = deg2rad(($lat2 - $lat1));
    $deltaLng = deg2rad(($lng2 - $lng1));
    $a = sin($deltaLat/2) * sin($deltaLat/2) + cos($lat1rads) * cos($lat2rads) * sin($deltaLng/2) * sin($deltaLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $d = $R * $c;
    return $d;
}

function getDestinationLatLong($lat,$lng,$azimuth,$distance_meter,$radians=0){
	global $JR_PROMIEN_ZIEMI;
	//	die ("($lat,$lng,$azimuth,$distance)\n");
    //$R = 6378.1; //#Radius of the Earth in km
    $R = 6371.0; //#Radius of the Earth in km
    $R= $JR_PROMIEN_ZIEMI;
    $brng = ($radians)?$azimuth:deg2rad($azimuth); #Bearing is degrees converted to radians.
    $d = $distance_meter / 1000; #Distance m converted to km
    $lat1 = deg2rad($lat); #Current dd lat point converted to radians
    $lon1 = deg2rad($lng); #Current dd long point converted to radians
    $lat2 = asin(sin($lat1) * cos($d/$R) + cos($lat1)* sin($d/$R)* cos($brng));
    $lon2 = $lon1 + atan2(sin($brng) * sin($d/$R)* cos($lat1), cos($d/$R)- sin($lat1)* sin($lat2));
    #convert back to degrees
    $lat2 = rad2deg($lat2);
    $lon2 = rad2deg($lon2);

    return [$lat2, $lon2];  
}

function calculateBearing($lat1,$lng1,$lat2,$lng2,$radians=0){
   // '''calculates the azimuth in degrees from start point to end point'''
    $startLat = deg2rad($lat1);
    $startLong = deg2rad($lng1);
    $endLat = deg2rad($lat2);
    $endLong = deg2rad($lng2);
    $dLong = $endLong - $startLong;
    $dPhi = log(tan($endLat / 2 + pi() / 4) / tan($startLat / 2 + pi() / 4));
    if (abs($dLong) > pi()) {
        if ($dLong > 0) {
            $dLong = -(2 * pi() - $dLong);
        } else {
            $dLong = 2 * pi() + $dLong;
        }
    }
    $bearing = atan2($dLong, $dPhi);
    if (!$radians) $bearing = (rad2deg($bearing) + 360) % 360;
//    $bearing = (rad2deg(atan2($dLong, $dPhi)) + 360) % 360;
    return $bearing;
}

function getDestinationLatLong2($lat1,$lng1,$lat2,$lng2,$distance_meter){
	$azimuth=calculateBearing($lat1,$lng1,$lat2,$lng2,1);
	return getDestinationLatLong($lat1,$lng1,$azimuth,$distance_meter,1);
}


function main($interval, $azimuth, $lat1, $lng1, $lat2, $lng2) 
{
    $d = getPathLength($lat1, $lng1, $lat2, $lng2);
    $rapydUnpack = modf($d / $interval);
    $remainder = $rapydUnpack[0];
    $dist = $rapydUnpack[1];
    $counter = parseFloat($interval);
    $coords = [];
    array_push($coords, [ $lat1, $lng1 ]);

    $xRange = xrange(0, intval($dist));
    print_r($xRange);
		print "\n".__LINE__." d:$d interval:$interval azimuth:$azimuth dist:$dist counter:$counter\n";
    foreach ($xRange as $rapydIndex => $value) 
    {
				print $value.";";
        $distance =$value;
        $coord = getDestinationLatLong($lat1, $lng1, $azimuth, $counter);
        $counter = $counter + parseFloat($interval);
        array_push($coords, $coord);
    }
    array_push($coords, [ $lat2, $lng2]);

    return $coords;
}

function get_lanlng($km_szukany, $lat1, $lng1,$km1, $lat2, $lng2, $km2,$debug=0) {
		if ($km1>$km2) {
				// swap (1,2)
				list($km1, $km2, $lat1, $lat2, $lng1, $lng2) = array($km2, $km1, $lat2, $lat1, $lng2, $lng1);
		}
		$d = getPathLength($lat1, $lng1, $lat2, $lng2);
    $azimuth = calculateBearing($lat1,$lng1,$lat2,$lng2);
    //$rapydUnpack = modf($d * ($km_szukany-$km1)/($km2-$km1));
    //$remainder = $rapydUnpack[0];
    //$dist = $rapydUnpack[1];
    //$counter = parseFloat($interval);
    
    $distance1=parseFloat( ($km_szukany-$km1)/($km2-$km1));
    $distance=parseFloat($d * ($km_szukany-$km1)/($km2-$km1));
    if ($debug) {print "get_lanlng szu:$km_szukany [$km1:$km2] \nd:$d a:$azimuth \ndist:$distance   dist1:$distance1\n";}
    return getDestinationLatLong($lat1, $lng1, $azimuth, $distance);
    
}


function getPointLineDistance($lat1,$lng1,$lat2,$lng2,$latP,$lngP)
{
    $R = '6371000'; //# radius of earth in m
    $bearingAC = calculateBearing($lat1,$lng1,$latP,$lngP);
    $bearingAB = calculateBearing($lat2,$lng2,$latP,$lngP);
		$distAC=getPathLength($lat1,$lng1,$latP,$lngP);
		$d=	asin(sin($distAC/ $R) * sin($bearingAC - $bearingAB)) * $R;
    //https://stackoverflow.com/questions/20231258/minimum-distance-between-a-point-and-a-line-in-latitude-longitude 
    return $d;
}



if (0) {
  #point interval in meters
    $interval = 10;
    #direction of line in degrees
    #start point
    $lat1 = 43.97076;
    $lng1 = 12.72543;
    #end point
    $lat2 = 43.969730;
    $lng2 = 12.728294;
    $azimuth = calculateBearing($lat1,$lng1,$lat2,$lng2);
    print $azimuth;
    $coords = main($interval,$azimuth,$lat1,$lng1,$lat2,$lng2);
#    print_r($coords);
		print_r (get_lanlng(10,1,1,100,0,0,0));
}
?>