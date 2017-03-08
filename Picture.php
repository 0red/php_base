<?php
/**
 * Plik pomocniczy do obslugi funkcji zwiazanych z rysunkami
 *
 * @filesource
 * @author Jacek Rusin
 * @link http://www.jr.pl
 * @package tools
 *
 * class Picture - tworzy rysunek
 * class PictureFile - tworzy rysunek z pliku
 * class PictureTextBox - tworzy rysunek z textu
 * class PictureTTFBox - brak jeszcze unicode w polskich znakach
 * function PictureTextMultiBox - dopisuje text wielowierszowy do rysunku
 *
*/

#ob_start();
#error_reporting(E_ALL);
define("PIC_GIF",1);
define("PIC_JPG",2);
define("PIC_PNG",3);

define("BG_BLACK",-1);
define("BG_WHITE",-2);

define("TTF_DIR","./");

function print_iso() {
$win = "êó¹œ³¿ŸæñÊÓ¥Œ£¯ÆÑ";
$iso = "êó±¶³¿¼æñÊÓ¡¦£¯¬ÆÑ";
$i=18;
while ($i--)
	if (ord($win{$i})<>ord($iso{$i})) {
		$wii[$i]="chr(".ord($win{$i}).")";
		$iss[$i]="chr(".ord($iso{$i}).")";
		print ord($win{$i})."-".ord($iso{$i})." $i \n";
	}
	print "\$win=".join($wii,".").";\n";
	print "\$iso=".join($iss,".").";\n";
}


function win_to_iso($text) {
$win=chr(143).chr(140).chr(165).chr(159).chr(156).chr(185);
$iso=chr(172).chr(166).chr(161).chr(188).chr(182).chr(177);
return strtr($text,$win,$iso);
}
#Sometimes this function gives ugly/dull colors 
#(especially when ncolors < 256). Here is a 
#replacement that uses a temporary image and 
#ImageColorMatch() to match the colors more 
#accurately. It might be a hair slower, but the file size ends up the same:


/**
* class Picture
*
* Postawowa klasa rysunku
*
*/
class Picture {
	/**
 * Handler rysunku
 *
 * 
 *
 */
 var $image;
	/**
 * Wysokosc rysunku
 *
 * 
 *
 */
 var $wys;
	/**
 * Szerokosc rysunku
 *
 * 
 *
 */
 var $szer;


	/**
 * Tworzy rysunek o wymiarach ($x,$y)
 *
 * 
 *
 */
 function Picture($x,$y,$trueColor=true) {
    if ($trueColor) {
			if (!$this->image = imagecreatetruecolor($x,$y))
				$this->error("::Picture() Cannot Initialize new GD image stream");
		} else {
			if (!$this->image = imagecreate($x,$y))
				$this->error("::Picture() Cannot Initialize new GD image stream");
		}
		$this->szer=$x;
		$this->wys=$y;
 	}

/**
* Picture::mergePix()
*
* Funkcja ³¹czy dwa obrazki ze sob¹ na okreslonej pozycji
* Mozliwe takze ustawienie parametru przenikalnosci $transition
* ktory jednak nie dziala na wszystkich platformach (np PHP WIN32)
* <pre>
* $pos  = Position where $insertfile will be inserted in $sourcefile
* 0 = middle
* 1 = top left
* 2 = top right
* 3 = bottom right
* 4 = bottom left
* 5 = top middle
* 6 = middle right
* 7 = bottom middle
* 8 = middle left
* 17 = 7+ 5pix
* array ("x"=>xpos,"y"=>ypos)
* </pre>
*
* @param [type] $insertfilename Nazwa pliku lub object Picture
* @param integer $pos Position where $insertfile will be inserted in image (see above)
* @param integer $transition Intensity of the transition (in percent)
* @return  
*/
	function mergePix($insertfilename,$pos=0,$transition=50){

	//Get the resource id´s of the pictures
	    if (is_a($insertfilename,"Picture"))
	        $insertpic=@ $insertfilename;
		 else
			$insertpic=new PictureFile($insertfilename);
		if (!$insertpic) return
			$this->error("mergePix() - nie ma pliku '$insertfilename'");;
		$insertfile_id = $insertpic->image;
		$sourcefile_id = @ $this->image;
	//Get the sizes of both pix
		$sourcefile_width=$this->szer;
		$sourcefile_height=$this->wys;
		$insertfile_width=$insertpic->szer;
		$insertfile_height=$insertpic->wys;

	    if (is_array($pos)) {
	    	$dest_x=$pos["x"];
	    	$dest_y=$pos["y"];
	    }

	//middle
		if( $pos == 0 ) {
			$dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
			$dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
		}
	//top left
		if( $pos == 1 ) {
			$dest_x = 0;
			$dest_y = 0;
		}
	//top right
		if( $pos == 2 ) {
			$dest_x = $sourcefile_width - $insertfile_width;
			$dest_y = 0;
		}
	//bottom right
		if( $pos == 3 ) {
			$dest_x = $sourcefile_width - $insertfile_width;
			$dest_y = $sourcefile_height - $insertfile_height;
		}
	//bottom left
		if( $pos == 4 ) {
			$dest_x = 0;
			$dest_y = $sourcefile_height - $insertfile_height;
		}
	//top middle
		if( $pos == 5 ) {
			$dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
			$dest_y = 0;
		}
	//middle right
		if( $pos == 6 ) {
			$dest_x = $sourcefile_width - $insertfile_width;
			$dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
		}

	//bottom middle
		if( $pos == 7 ) {
			$dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
			$dest_y = $sourcefile_height - $insertfile_height;
		}
		if( $pos == 17 ) {
			$dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
			$dest_y = $sourcefile_height - $insertfile_height-7;
		}


	//middle left
		if( $pos == 8 ) {
			$dest_x = 0;
			$dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
		}

	//The main thing : merge the two pix
	    imageCopyMerge($sourcefile_id, $insertfile_id,$dest_x,$dest_y,
			0,0,$insertfile_width,$insertfile_height,$transition);

		$this->image= @ $sourcefile_id;
		return @$sourcefile_id;;
	}


/**
 * Picture::mergePixTransparent()
 *
 * £aczy ze soba rysunki z utworzeniem ciemnej lub jasnej otoczki
 * otoczka oraz rysunek moga byc przezroczyste
 *
 * @see Picture::mergePix() 
 * @see Picture::set_color() 
 * @param [type] $insertfilename nazwa pliku lub objekt Picture zobacz mergePix()
 * @param integer $pos pozycja zobacz mergePix()
 * @param integer $box_transition
 * @param integer $source_transition
 * @param [type] $box_color BG_BLACK,BG_WHITE lub zgodnie ze skladnia set_color
 * @return  
 */
 	function mergePixTransparent($insertfilename,$pos=0,$box_transition=20
		,$source_transition=100,$box_color=BG_BLACK){
		#	print "\nbox color $box_color\n";

	//Get the resource id´s of the pictures
	    if (is_a($insertfilename,"Picture"))
	        $insertpic=@ $insertfilename;
		 else
			$insertpic=new PictureFile($insertfilename);
		if (!$insertpic) return
			$this->error("mergePixTransparent() - nie ma pliku '$insertfilename'");;

		$blackbox=new Picture($insertpic->szer,$insertpic->wys);
		$black = $blackbox->set_color($box_color);

    	imagefilledrectangle ($blackbox->image,0,0,
				$blackbox->szer,$blackbox->wys,$black);
		#$black = $blackbox->to_png_file("black$pos.png");
		$this->mergePix($blackbox,$pos,$box_transition);
		$this->mergePix($insertpic,$pos,$source_transition);
	}


 	function to_jpg_file($name,$quality=90) {
 		if (!function_exists("imagejpeg")) return $this->error(" Brak obs³ugi JPG !!");
 		imagejpeg($this->image,$name,$quality);
 		return PIC_JPG;
 	}

 	function to_png_file($name,$compression=-1) {
 		if (!function_exists("imagepng")) return $this->error(" Brak obs³ugi PNG !!");
 		imagepng($this->image,$name,$compression);
 		//-1 default
 		//0 no compression
 		//9 max compression
 		return PIC_PNG;
 	}
	function to_gif_file($name) {
 		if (!function_exists("imagegif")) return $this->to_png_file($name.".png");
 		imagegif($this->image,$name);
 		return PIC_GIF;
 	}

	function output_png() {
		if (!function_exists("imagepng")) return $this->error(" Brak obs³ugi PNG !!");
 		header ("Content-type: image/png");
   		imagepng ($this->image);
   	}

	function output_jpg($quality=90) {
		if (!function_exists("imagejpeg")) return $this->error(" Brak obs³ugi JPG !!");
 		header ("Content-type: image/jpeg");
   		imagepng ($this->image);
   	}

    function output_gif() {
    	if (!function_exists("imagegif")) return $this->output_png();
		header ("Content-type: image/jpeg");
   		imagepng ($this->image);
   	}


 /**
  * Picture::rotate()
  *
  * Obraca obraz o 90,180,270 stopni
  *
  * @see Picture::rotate_left()
  * @see Picture::rotate_right()
  * @param integer $degrees 0,90,180,270
  * @return
  */
 function rotate($degrees = 90) {
     $src_img=$this->image;
      $degrees %= 360;
       if ($degrees == 0) {
              $dst_img = $src_img;
       } Elseif ($degrees == 180) {
              $dst_img = imagerotate($src_img, $degrees, 0);
       } Else {
               $width  = imagesx($src_img);
               $height = imagesy($src_img);
               if ($width > $height) {
                       $size = $width;
               } Else {
                       $size = $height;
               }
              $dst_img = imagecreatetruecolor($size, $size);
             imagecopy($dst_img, $src_img, 0, 0, 0, 0, $width, $height);
             $dst_img = imagerotate($dst_img, $degrees, 0);
              $src_img = $dst_img;
               $dst_img = imagecreatetruecolor($height, $width);
               if ((($degrees == 90) && ($width > $height)) || (($degrees == 270) && ($width < $height))) {
                       imagecopy($dst_img, $src_img, 0, 0, 0, 0, $size, $size);
               }
             if ((($degrees == 270) && ($width > $height)) || (($degrees == 90) && ($width < $height))) {
                      imagecopy($dst_img, $src_img, 0, 0, $size - $height, $size - $width, $size, $size);
               }
       }
       $this->image=$dst_img;
       return @ $dst_img;
	}

	function set_interlace($interlace) {
		imageinterlace($this->image,$interlace);
	}

	function clear_interlace() {
		$this->set_interlace(0);
 	}

 	/**
 	  * Picture::resize()
 	  *
 	  * Zmienia wielkosc rysunku
 	  *
 	  * @param int $to_x
 	  * @param int $to_y
 	  * @return
 	  */
 	 function resize($to_x,$to_y) {
 		$im=ImageCreateTrueColor($to_x,$to_y);
		imagecopyresized($im,$this->image,0,0,0,0,$to_x,$to_y,
			$this->szer,$this->wys);
		$this->image=@ $im;
		$this->szer=$to_x;
		$this->wys=$to_y;
		return @ $im;
	}


	/**
	 * Picture::resize_to_x()
	 *
	 * Zmienia proporcjonalnie rozmiary rysunku do szerokosci $to_x
	 *
	 * @see Picture::resize()
	 * @see Picture::resize_to_width()
	 * @param int $to_x
	 * @return
	 */
	function resize_to_x($to_x) {
		$to_y=$this->wys*$to_x/$this->szer;
		return $this->resize($to_x,$to_y);
	}

	/**
	 * Picture::resize_to_y()
	 *
	 * Zmienia proporcjonalnie rozmiary rysunku do wysokosci $to_y
	 *
	 * @see Picture::resize()
	 * @see Picture::resize_to_height()
	 * @param int $to_y
	 * @return
	 */
	function resize_to_y($to_y) {
		$to_x=$this->szer*$to_y/$this->wys;
		return $this->resize($to_x,$to_y);
	}

	/**
	 * Picture::resize_to_width()
	 *
	 * Zmienia proporcjonalnie rozmiary rysunku do szerokosci $to_x
	 *
	 * @see Picture::resize()
	 * @see Picture::resize_to_x()
	 * @param int $to_x
	 * @return
	 */
	function resize_to_width($to_x) {
		return $this->resize_to_x($to_x);
	}

	/**
	 * Picture::resize_to_height()
	 *
	 * Zmienia proporcjonalnie rozmiary rysunku do wysokosci $to_y
	 *
	 * @see Picture::resize()
	 * @see Picture::resize_to_y()
	 * @param int $to_y
	 	 * @return
	 */
	function resize_to_height($to_y) {
		return $this->resize_to_y($to_y);
	}

	/**
	 * Picture::rotate_left()
	 *
	 * Obraca rysunek w lewo
	 *
	 * @see Picture::rotate()
	 * @return
	 */
	function rotate_left() {
	    return $this->rotate(90);
	}

    /**
        * Picture::rotate_right()
        *
        * Obraca rysunek w prawo
	    *
	    * @see Picture::rotate()
	    * @return
        */
       function rotate_right() {
	    return $this->rotate(270);
	}

	/**
	 * Picture::set_color()
	 *
	 * Zwraca ustawienie koloru rysunku.
	 *
	 * Dostepne formaty to:
	 * <pre>
	 * set_color(BG_BLACK) ustawia na czarny
	 * set_color(BG_WHITE) ustawia na bialy
	 * set_color(r,g,b) np.: (255,255,255) w dec
	 * set_color("r,g,b") np.: ("255,255,255") w dec
	 * set_color("RRGGBB") np.: ("af2234") w hex
     * set_color("#RRGGBB") np.: ("#af2234") w hex
     * set_color(array("1"=>red(dec),"2"=>green,"3"=>blue)) np.: (array("1"=>255,"2"=>255,"3"=>255))
     * </pre>
	 *
	 * @param array_string $color
	 * @param dec $c2
	 * @param dec $c3
	 * @return int
	 */
	function set_color($color,$c2=NULL,$c3=NULL) {
        if ($color==BG_BLACK || $color===NULL)
			$color="0,0,0";
		elseif ($color==BG_WHITE)
			$color="255,255,255";

		if ($c2!==NULL && $c3!==NULL) {
		    $s[1]=$color;
		    $s[2]=$c2;
		    $s[3]=$c3;
		   } else
		if (!is_array($color)) {
			if (!preg_match("/(\d{1,3}),(\d{1,3}),(\d{1,3})/",$color,$s))
			    if (preg_match("/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/",$color,$s))
			        foreach ($s as $s1=>$s2) if ($s1) $s[$s1]=hexdec($s2);
		} else $s=$color;

		#print_r($s);
		return (isset($s) ?
			$this->color($s[1],$s[2],$s[3]): NULL );
			//imagecolorallocate($this->image,$s[1],$s[2],$s[3]): NULL );
	}

	/**
	 * Picture::error()
	 *
	 * Oooops wystapil blad krytyczny
	 *
	 * @param string $text
	 * @return
	 */
	function error($text) {
		user_error(get_class($this)." {$text}",E_USER_ERROR);
	}
	
	var $colors=array();
	function color($red,$green,$blue) {
		$a=array($red,$green,$blue);
		foreach ($this->colors as $a1=>$a2) if ($a2==$a) return $a1;
		$c= imagecolorallocate ($this->image,$red,$green,$blue);
		$this->colors[$c]=$a;
		return $c;
	}

 }




class PictureTextBox extends Picture {
	
 	/**
 	  * PictureTextBox::PictureTextBox()
 	  *
 	  * tworzy objet Textu rysunkowego z wykorzystaniem standardowych fontow
 	  *
 	  * @param string $text Text do wyswietlenia
 	  * @param integer $font nr fontu GD 1-5 (4)
 	  * @param integer $border_size wielkosc obramowania (1)
 	  * @param integer $distance_from_border odleglosc textu od ramki (3)
 	  * @param piccolor $text_color kolor tekstu (BG_WHITE)
 	  * @param piccolor $bg_color kolor t³a (BG_WHITE)
 	  * @param piccolor $border_color kolor ramki (BG_WHITE)
 	  * @param boolean $transparent czy ma byc przezroczysty jak tak ro do $bg+color
 	  * @param boolean $interlace czy ma miec przeplot
 	  * @return
 	  */
 	 function PictureTextBox($text,$font=4,$border_size=1,$distance_from_border=3,
		$text_color=BG_WHITE,$bg_color=BG_BLACK,$border_color=BG_WHITE,
	    $transparentbg=TRUE,$interlace=TRUE) {;

		#$text="±¶æ¿¼³óñ¡¦Æ¯¬£ÓÑ";
		$x=1;
  		$y=($font==1? 8 : ($font==2 ? 14 :
  			($font==3 ?14: ($font==4 ? 16 :
  			($font==5 ? 16 : 40)))));

  		$w=$font+4;
  		$x=(strlen($text)*$w>$x ? strlen($text)*$w :$x);

  		$narzut=2*($border_size+$distance_from_border);

		  parent::Picture($x+$narzut, $y+$narzut);

    	$text_color 	= $this->set_color($text_color);
    	$border 		= $this->set_color($border_color);
    	$background 	= $this->set_color($bg_color);

    	imagefilledrectangle ($this->image,0,0,$narzut+$x-1,$narzut+$y-1,$border);
    	imagefilledrectangle ($this->image,$border_size,$border_size,
		$x+$narzut-$border_size-1,$y+$narzut-$border_size-1,$background);
    	imagestring ($this->image, $font, $narzut/2, $narzut/2,  $text, $text_color);
        if ($interlace)
    		$this->set_interlace(3);

		if ($transparentbg)
    		imagecolortransparent($this->image,$background);
    
	}
}


 	/**
 	  * PictureTextBox::PictureTextBox()
 	  *
 	  * dopisuje tekst ($text) wieloliniowy (nowa linia \n) do rysunku ($owner)
	  * z wykorzystaniem standardowych fontow na gorze lub na dole ($position).
	  * text dopasowywany jest do wielkosci rysunku (na szerokosc) i w razie
	  * potrzeby dzielony see -wordwrap.
 	  *
 	  * @param Picture $owner Object picture do ktorego zostanie dopisane
 	  * @param string $text Text do wyswietlenia
 	  * @param boolean $position czy na dole(-) czy na gorze (+) - odleglosc w pixelach
 	  * @param integer $font nr fontu GD 1-5 (4)
 	  * @param integer $border_size wielkosc obramowania (1)
 	  * @param integer $distance_from_border odleglosc textu od ramki (3)
 	  * @param piccolor $text_color kolor tekstu (BG_WHITE)
 	  * @param piccolor $bg_color kolor t³a (BG_WHITE)
 	  * @param piccolor $border_color kolor ramki (BG_WHITE)
 	  * @param boolean $transparent czy ma byc przezroczysty jak tak ro do $bg+color
 	  * @param boolean $interlace czy ma miec przeplot
 	  * @return
 	  */
 	 function PictureTextMultiBox($owner,$text,$position=-10,$font=4,
	  	$border_size=0,$distance_from_border=3,
		$text_color=BG_WHITE,$bg_color=BG_BLACK,$border_color=BG_WHITE,
	    $transparentbg=TRUE,$interlace=TRUE) {

	#print "<pre>";
		if (!$text) return NULL;
		#$text="±¶æ¿¼\n³óñ¡¦Æ¯¬£ÓÑ";
  		$y=($font==1? 8 : ($font==2 ? 14 :
  			($font==3 ?14: ($font==4 ? 16 :
  			($font==5 ? 16 : 40)))));

  		$w=$font+4;
  		$x=strlen($text)*$w;
  		
		$maxx=(int) ($owner->szer-2*($border_size+$distance_from_border)) / $w;
		$te=split("\n",$text);
  		foreach($te as $te1=>$te2)
  				$te[$te1]=wordwrap($te2,$maxx);
  		$text=join($te,"\n");
  		
  		$text=split("\n",$text);
  		
  		#print_r($text);
  		
  		$rys=array();
  		foreach($text as $te)
			array_push($rys,new PictureTextBox($te,$font,
					$border_size,$distance_from_border,
					$text_color,$bg_color,$border_color,
	    			$transparentbg,$interlace));

		//wymiary
		$size_x=0;
		$size_y=0;
		$size_y1=0;

	    foreach ($rys as $r) {
	        $size_x=max($size_x,$r->szer);
	        $size_y+=$r->wys;
	       }
	    $size_y+=abs($position);
	     
	     $x2=(int) $owner->szer/2;
	     //dodajemy do rysunku
	     $up=0; #nie wiem do czego to s³u¿y³o
	     foreach ($rys as $r) {
	        $owner->mergePixTransparent($r,array("x"=>$x2-($r->szer/2),
	            "y"=>($up>0 ? $position+ $size_y1 :$owner->wys-$size_y)),20);
	            $size_y-=$r->wys;
	            $size_y1+=$r->wys;
	           }
	         
  		
	}


class PictureTTFBox extends Picture {

 	/**
 	  * PictureTTFBox::PictureTTFBox()
 	  *
 	  * tworzy objet Textu rysunkowego z wykorzystaniem standardowych fontow
 	  *
 	  * @todo UNICODE i polskie znaki nie dzia³aj¹
 	  * @todo na Ostrau nie ma imagettfbbox() !!!
 	  * @param string $text Text do wyswietlenia
	  * @param integer $size wysokosc textu
	  * @param integer $font nazwa lub sciezka do fontu TTF
 	  * @param integer $kat kat textu
 	  * @param integer $border_size wielkosc obramowania (1)
 	  * @param integer $distace_from_border odleglosc textu od ramki (3)
 	  * @param piccolor $text_color kolor tekstu (BG_WHITE)
 	  * @param piccolor $bg_color kolor t³a (BG_WHITE)
 	  * @param piccolor $border_color kolor ramki (BG_WHITE)
 	  * @param boolean $transparent czy ma byc przezroczysty jak tak ro do $bg+color
 	  * @param boolean $interlace czy ma miec przeplot
 	  * @return
 	  */
 	 function PictureTTFBox($text,$size=12,$font="arial.ttf",$kat=0,
	  		$border_size=1,$distace_from_border=3,
			$text_color=BG_WHITE,$bg_color=BG_BLACK,$border_color=BG_WHITE,
	    	$transparentbg=TRUE,$interlace=TRUE) {;

		if (!file_exists($font)) $font=TTF_DIR.$font;
		if (!file_exists($font)) $this->error("Nie znaleziono fontu $font !!");


		$text="±¶æ¿¼³óñ¡¦Æ¯¬£ÓÑ";
		
		$text="al¹ i kot¹";
		 $s = imagettfbbox($size, $kat, $font, $text);
         $x = abs($s[2] - $s[0]);
         $y = abs($s[5] - $s[3]);
		
  		$narzut=2*($border_size+$distace_from_border);

		#print "x$x y$y n$narzut\n";
  			parent::Picture($x+$narzut, $y+$narzut);

    	$text_color 	= $this->set_color($text_color);
    	$border 		= $this->set_color($border_color);
    	$background 	= $this->set_color($bg_color);

    	imagefilledrectangle ($this->image,0,0,$narzut+$x-1,$narzut+$y-1,$border);
    	imagefilledrectangle ($this->image,$border_size,$border_size,
		$x+$narzut-$border_size-1,$y+$narzut-$border_size-1,$background);
    	ImageTTFText($this->image,$size,$kat,(int) ($narzut/2)-1, $y+(int) ($narzut/2)-1,
				$text_color,$font,win_to_iso($this->utf8_encode($text)) );
        if ($interlace)
    		$this->set_interlace(3);

		if ($transparentbg)
    		imagecolortransparent($this->image,$background);
	}

}



class PictureFile extends Picture {
    var $filename;
	var $mime;
	var $extensions=array("",".jpg",".JPG",".gif",
		 	".GIF",".png",".PNG",".jpeg",".JPEG");

	/**
	 * PictureFile::PictureFile()
	 *
	 * Inteligentna funkcja wczytywania z pliku
	 * jak nie ma rozszerzenia do dodaje z listy $extension
	 *
	 * @param filename $name
	 * @return
	 */
	function PictureFile($name) {
	#	$name=str_replace(" ","%20",$name);
	    $im=NULL;
 		foreach($this->extensions as $ext) {
 		#	print "<BR>".$name.$ext."\n";
   			
			   if (!file_exists($name.$ext)) continue;
   			 #  if (!getimagesize($name.$ext)) continue;
   			
   			$size = getimagesize ($name.$ext);
   			#print_r($size);
			switch ($size[2]) {
				case PIC_GIF: #GIF
					$im=@imagecreatefromgif($name.$ext);
					break;
  				case PIC_JPG: #JPEG
  					$im=imageCreateFromJPEG($name.$ext);
					break;
  				case PIC_PNG: #PNG
  					$im=@imagecreatefrompng($name.$ext);
					break;
  				}
  			 if ($im) break;
  			 #print "--";
			 }
			 
  			if (!$im)
  			    $this->error("::PictureFile() Nie potrafie otworzyc pliku {$name}{$ext} !!");

			$this->szer=$size[0];
			$this->wys=$size[1];
			$this->mime=$size["mime"];
			$this->filename=$name.$ext;
			$this->image=@ $im;
			#print $this->filename." created \n";
   		}


}

class PictureFileCheck extends PictureFile {
var $_erroor=FALSE;

	function error($text) {
  		$this->_erroor=$text;
	}
}


/*
# $pic1= new Picture("1");
# $pic1= new Picture("3");
$pic= new PictureFile("2");
#$pic->resize_to_width(100);
#$pic->rotate_left();
$pic->mergePix("aosign",1,85);
$pic->mergePix("sign",4,15);
$pic->mergePixTransparent("sign",6,15,25,BG_WHITE);
$pic1= new PictureTextBox("ala ma kota");
$pic->mergePixTransparent($pic1,2,20);

$pic->to_jpg_file("4.jpg");
print $pic1->to_jpg_file("5.jpg");
if (!isset($_SERVER["argv"])) $pic->output_jpg();
#
#print $pic->set_color("255,255,255")."\n";
#print $pic->set_color("#ffff00")."\n";
#print $pic->set_color(255,255,255)."\n";

#$pic1= new PictureTextBox("ala ma kota");
#print $pic->to_jpg_file("5.jpg");
#print $pic->to_png_file("5.png");
#print $pic->to_gif_file("5.gif")
#print_iso();
*/
?>
