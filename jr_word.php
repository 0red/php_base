<?php
#include_once '../PHPWord/samples/Sample_Header.php';
require_once __DIR__ . '/../PHPWord/src/PhpWord/Autoloader.php';
require_once __DIR__ . '/../PHPExcel/Classes/PHPExcel.php';


date_default_timezone_set('UTC');

/**
 * Header file
 */
use PhpOffice\PhpWord\Autoloader;
use PhpOffice\PhpWord\Settings;

error_reporting(E_ALL);
define('CLI', (PHP_SAPI == 'cli') ? true : false);
define('EOL', CLI ? PHP_EOL : '<br />');
define('SCRIPT_FILENAME', basename($_SERVER['SCRIPT_FILENAME'], '.php'));
define('IS_INDEX', SCRIPT_FILENAME == 'index');

Autoloader::register();
Settings::loadConfig();
Settings::setPdfRenderer (Settings::PDF_RENDERER_TCPDF,'E:\php5\tcpdf');
// Set writers


// Set PDF renderer
if (null === Settings::getPdfRendererPath()) {
    $writers['PDF'] = null;
}
// New Word document
echo date('H:i:s'), ' Create new PhpWord object', EOL;
$phpWord = new \PhpOffice\PhpWord\PhpWord();



// Begin code
$section = $phpWord->addSection();


// Define the TOC font style
$fontStyle2 = array('size' => 8,
        'spaceBefore'=>'0',
        'spaceAfter'=>'0',
);
$fontStyle3 = array('size' => 8);
$phpWord->addNumberingStyle(
    'hNum',
    array('type' => 'multilevel', 'levels' => array(
        array('pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1'),
        array('pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2'),
        array('pStyle' => 'Heading3', 'format' => 'decimal', 'text' => '%1.%2.%3'),
        array('pStyle' => 'Heading4', 'format' => 'decimal', 'text' => '%1.%2.%3.%4'),
        array('pStyle' => 'Heading5', 'format' => 'decimal', 'text' => '%1.%2.%3.%4.%5'),
        )
    )
);
$phpWord->addTitleStyle(1, array('size' => 12), array('numStyle' => 'hNum', 'numLevel' => 0));
$phpWord->addTitleStyle(2, array('size' => 11), array('numStyle' => 'hNum', 'numLevel' => 1));
$phpWord->addTitleStyle(3, array('size' => 10), array('numStyle' => 'hNum', 'numLevel' => 2));
$phpWord->addTitleStyle(4, array('size' => 9), array('numStyle' => 'hNum', 'numLevel' => 3));
$phpWord->addTitleStyle(5, array('size' => 9), array('numStyle' => 'hNum', 'numLevel' => 4));

$properties = $phpWord->getDocInfo();
$properties->setCreator('Jacek RUsin');
$properties->setCompany('Kapsch CarrierCom Sp. z o.o.');
$properties->setTitle('Dokumentacja');
$properties->setDescription('Dokumentacja generowana automatycznie za pomocą PHPWord-a');
$properties->setCategory('KCC');
$properties->setLastModifiedBy("Jacek RUsin");
#$properties->setCreated(mktime(0, 0, 0, 3, 12, 2014));
$properties->setCreated(time());
#$properties->setModified(mktime(0, 0, 0, 3, 14, 2014));
#$properties->setSubject('My subject');
#$properties->setKeywords('my, key, word');




$phpXls = new PHPExcel();

$phpXls ->getProperties()->setCreator("Jacek Rusin")
							 ->setLastModifiedBy("Jacek Rusin")
							 ->setTitle("PHPExcel Test Document")
							 ->setSubject("PHPExcel Test Document")
							 ->setDescription("Test document for PHPExcel, generated using PHP classes.")
							 ->setKeywords("office PHPExcel php")
							 ->setCategory("Test result file");
							 
function jr_xls_save($_xls=null,$file_name='',$act_sheet=0) {
  global $phpxls;
  if (!$_xls) $_xls=$phpxls;
  if (!$file_name) $file_name=__FILE__;

  $_xls->setActiveSheetIndex($act_sheet);
  $objWriter = PHPExcel_IOFactory::createWriter($_xls, 'Excel2007');
  $objWriter->save(str_replace('.php', '.xlsx', $file_name));
}

function jr_word_add_footer($section,$foot='Strona {PAGE} z {NUMPAGES}.',$fontstyle=null) {
  if (!$fontstyle) $fontstyle=array('align' => 'right');
  $footer = $section->addFooter();
  $footer->addPreserveText(htmlspecialchars($foot), $fontstyle);
  return $footer;
}


function jr_word_add_toc($section,$_min=1,$_max=4,$spis='Spis treści',$fontstyle=null) {
  if (!$fontstyle) $fontstyle=array('size' => 8,'spaceBefore'=>'0','spaceAfter'=>'0',);  
// Add TOC #1
  $section->addText(htmlspecialchars($spis));
  $section->addTextBreak(1);
  $toc2 = $section->addTOC($fontstyle);
  $toc2->setMinDepth($_min);
  $toc2->setMaxDepth($_max);
  //$section->addTextBreak(1);
  $section->addPageBreak();
  echo date('H:i:s'), ' Note: Please refresh TOC manually.', EOL;
  return $toc2;
}

/*
$section->addTitle('Heading 1', 1);
$section->addTitle('Heading 2', 2);
$section->addTitle('Heading 3', 3);
*/


function jr_word_save($word_name='',$_phpWord=null,$writers=null) {
  global $phpWord;
  if (!$_phpWord) $_phpWord=$phpWord;
  if (!$word_name) $word_name=basename(__FILE__, '.php');
#  if (!$writers) $writers = array('Word2007' => 'docx', 'ODText' => 'odt', 'RTF' => 'rtf', 'HTML' => 'html', 'PDF' => 'pdf');
  if (!$writers) $writers = array('Word2007' => 'docx', 'HTML' => 'html', );


  // Save file
  
  echo jr_word_write($_phpWord, $word_name, $writers);
}

/**
 * Write documents
 *
 * @param \PhpOffice\PhpWord\PhpWord $phpWord
 * @param string $filename
 * @param array $writers
 *
 * @return string
 */

function jr_word_write($phpWord, $filename, $writers)
{
    $result = '';

    // Write documents
    foreach ($writers as $format => $extension) {
        $result .= date('H:i:s') . " Write to {$format} format";
        if (null !== $extension) {
            $targetFile = __DIR__ . "/word/{$filename}.{$extension}";
            $phpWord->save($targetFile, $format);
        } else {
            $result .= ' ... NOT DONE!';
        }
        $result .= EOL;
    }

    $result .= jr_word_getEndingNotes($writers);

    return $result;
}


/**
 * Get ending notes
 *
 * @param array $writers
 *
 * @return string
 */
function jr_word_getEndingNotes($writers)
{
    $result = '';

    // Do not show execution time for index
    if (!IS_INDEX) {
        $result .= date('H:i:s') . " Done writing file(s)" . EOL;
        $result .= date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB" . EOL;
    }

    // Return
    if (CLI) {
        $result .= 'The results are stored in the "results/word" subdirectory.' . EOL;
    } else {
        if (!IS_INDEX) {
            $types = array_values($writers);
            $result .= '<p>&nbsp;</p>';
            $result .= '<p>Results: ';
            foreach ($types as $type) {
                if (!is_null($type)) {
                    $resultFile = 'results/' . SCRIPT_FILENAME . '.' . $type;
                    if (file_exists($resultFile)) {
                        $result .= "<a href='{$resultFile}' class='btn btn-primary'>{$type}</a> ";
                    }
                }
            }
            $result .= '</p>';
        }
    }

    return $result;
}

function jr_xls_read($file_name,$worksheets=array()) {
  $t = PHPExcel_IOFactory::createReaderForFile($file_name);
  $t->setReadDataOnly(true);
  if ($worksheets) {
      if (!is_array($worksheets)) $worksheets=array($worksheets);
      $t->setLoadSheetsOnly($worksheets);
  } else {
      $t->setReadDataOnly(true);
  }
  $tt=$t->load($file_name);
  #echo date('H:i:s') , " Iterate worksheets" , EOL;
  $w=array();
  foreach ($tt->getWorksheetIterator() as $worksheet) {
  #	echo 'Worksheet - ' , $worksheet->getTitle() , EOL;
    $w[$w1=$worksheet->getTitle()]=array();

    foreach ($worksheet->getRowIterator() as $row) {
#		echo '    Row number - ' , $row->getRowIndex() , EOL;
      $w3=array();
			
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
      foreach ($cellIterator as $cell) {
        if (!is_null($cell)) {
#			  	echo '        Cell - ' , $cell->getCoordinate() , ' - ' , $cell->getCalculatedValue() , EOL;
          $w3[]=$cell->getCalculatedValue();
        }
      }
      $w[$w1][$row->getRowIndex()]=$w3;
    }
  }
  return ($w);
}



?>