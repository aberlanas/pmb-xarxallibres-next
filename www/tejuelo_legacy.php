<?php

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$base_path=".";                            
$base_auth = "ADMINISTRATION_AUTH";  
$base_title = "\$msg[7]";
$class_path= "./classes";
$include_path= "./includes";

//---------------------LLIUREX 12/02/2015 -------------------------
require_once ("$base_path/includes/init.inc.php");
//--------------------- FIN LLIUREX 12/02/2015 -------------------------
require_once("$class_path/notice.class.php");
require_once("$class_path/expl.class.php");
require_once("$class_path/indexint.class.php");
require_once("$include_path/notice_authors.inc.php");
require_once("$include_path/notice_categories.inc.php");
//--------------------- LLIUREX 14/03/2016-----------------------
//require '/usr/share/php/fpdf/fpdf.php';
//--------------------- FIN LLIUREX 14/03/2016-------------------


define(CONVERSION,(1/25.4));//Conversión inch/mm
define(COLS_PER_PAGE, 3); // Nº columnas por página
define(ROWS_PER_PAGE, 8); // Nº filas por página
define(BARCODE_HEIGHT, 54); // en pixels
define(BARCODE_WIDTH, 175); // en pixels
define(ALIGN, 'C');
define(LABEL_WIDTH, (70.8*CONVERSION)); // en mm
define(LABEL_HEIGHT, (36.5*CONVERSION));  // en mm
define(H_MARGIN, (7*CONVERSION)); // horizontal (izquierda y derecha) margenes de la pagina, en mm
define(V_MARGIN, (12*CONVERSION)); // vertical (top & bottom) margins of page, in mm

//define(SPACE_BETWEEN_LABELS, ($parametros["SPACE_BETWEEN_LABELS"])*CONVERSION); // espacio horizontal entre etiquetas 
//define(SPACE_BETWEEN_LABELS_VERTICAL, ($parametros["SPACE_BETWEEN_LABELS_VERTICAL"])*CONVERSION); // espacio vertical entre etiquetas 

function f_rellena_ceros($as_dato) {
	if(strlen($as_dato)>0 && strlen($as_dato)<9){
		for($i=strlen($as_dato); $i<9; $i++)
			$as_dato="0".$as_dato;}
	
	return $as_dato; 
	
}

function str_squeeze($test) {
    return trim(ereg_replace( ' +', '', $test));
}

$codigos=$_GET['codigos'];
//$codigos=str_replace(" ", "", $codigos);


switch ($codigos){

case '':{
 //---------------------LLIUREX 12/02/2015 -------------------------	
       //require_once ("$base_path/includes/init.inc.php");
 //---------------------FIN LLIUREX 12/02/215 ----------------------
	echo "<center><form class='form-admin' name='form2' ENCTYPE=\"multipart/form-data\" method='get' action=\"./tejuelo_legacy.php?codigos=\".$codigos·\"\"><h1>TEJUELOS DE LEGADO</h1><hr/><b>$msg[tejuelo]&nbsp;</b><input name='codigos' accept='text/plain' type='text'  size='80'><input align='center' type='button' name='continuar' value='Continuar' onclick='form.submit()'></form></center>";
break;}



default:{
$matriz=array();
$base_noheader = 1;


$codigos=str_squeeze($codigos);

if((strpos($codigos, ",")!== false)) $aux=explode(",",$codigos);
else $aux=$codigos;

require("$base_path/includes/db_param.inc.php");

$link2 = @mysql_connect(SQL_SERVER, USER_NAME, USER_PASS) OR die("Error MySQL");


if (is_array($aux)) {
	foreach ($aux as $codi) {
		if(strpos($codi, "-") === false && strlen($codi) <= 9 ){
			if (is_numeric($codi)) $matriz[]= (int) $codi;
			else $matriz[]= $codi;
		}
		else if (strpos($codi, "-") !== false) { 
			$aux2=explode("-",$codi);
			if ( is_numeric($aux2[0]) && is_numeric($aux2[1]) ) {
				$aux2[0]= (int) $aux2[0];
				$aux2[1]= (int) $aux2[1];
				while($aux2[0] <= $aux2[1]){
						if (strlen($aux2[0]) <= 9) $matriz[] = (int) $aux2[0];
						$aux2[0]++;
					}
			  }
		}

	}
}
elseif ($aux === "*"){
		$q ='SELECT expl_cb FROM exemplaires';

		$resultData = @mysql_query($q, $link2);
		if (@mysql_num_rows($resultData) != 0) {

			while ($rowData = mysql_fetch_array($resultData)) {	
				if (strlen($rowData['expl_cb']) <= 9) $matriz[]= $rowData['expl_cb'];
			}
		}
		mysql_free_result($resultData);
}
else {
	if(strpos($aux, "-") === false && strlen($aux) <= 9 ){
			if (is_numeric($aux)) $matriz[]= (int) $aux;
			else $matriz[]= $aux;
		}
		else if (strpos($aux, "-") !== false) { 
			$aux2=explode("-",$aux);
			if ( is_numeric($aux2[0]) && is_numeric($aux2[1]) ) {
				$aux2[0]= (int) $aux2[0];
				$aux2[1]= (int) $aux2[1];
				while($aux2[0] <= $aux2[1]){
						if (strlen($aux2[0]) <= 9) $matriz[] = (int) $aux2[0];
						$aux2[0]++;
					}
			  }
		}
}

$size = count($matriz);

//Determinamos margenes de los barcode
$barcode_h_margin = ((LABEL_WIDTH-(BARCODE_WIDTH/72))/2); 
$barcode_v_margin = ((LABEL_HEIGHT-(BARCODE_HEIGHT/72))/2);

// Creamos objeto PDF
//-----------------------LLIUREX 14/03/2016------------------
require '/usr/share/php/fpdf/fpdf.php';
//----------------------- FIN LLIUREX 14/03/2016-------------
$pdf=new FPDF('P','in','A4');

// Metadata
$pdf->SetAuthor('PMB Xarxa Llibres');
$pdf->SetTitle('Tejuelo Legado');

//Otras características
$pdf->SetDisplayMode('real'); // Mostramos el zoom al 100%

// Añadimos una pagina al documento PDF
$pdf->AddPage();

// Fijamos los márgenes 
$pdf->SetMargins(H_MARGIN, V_MARGIN);

// Manejamos nosotros cuando la pagina debe acabar 
$pdf->SetAutoPageBreak(false);
$pdf->AddFont('barcode', '', "barcode.php");
$y = V_MARGIN; //Esta variable sigue la posición y (vertical)
$x = H_MARGIN; // Nueva fila, reseteamos x-position


$new_row=1;
$new_col=1;


foreach ($matriz as $exe_cote) {

	//if (is_numeric($exe_cote)) {
	//	$q ='SELECT expl_cote, expl_cb FROM exemplaires WHERE expl_cb="'.f_rellena_ceros($exe_cote).'"';
	//}else {
		//$q ='SELECT expl_cote, expl_cb FROM exemplaires WHERE expl_cb=\''.$exe_cote.'\' GROUP BY expl_cb';
		$q ='SELECT expl_cote, expl_cb FROM exemplaires WHERE expl_cb=\''.$exe_cote.'\'';
	//}
	
        //if ((strlen($exe_cote)==9) || $all == true){
	$resultData = @mysql_query($q, $link2);
       
	if (@mysql_num_rows($resultData) != 0) {

		//Recuperamos los datos de cada solicitud confirmada
				
		while ($rowData = mysql_fetch_array($resultData)) {	
					
		//dato cote
					
		$cote=$rowData['expl_cote'];
		if (is_numeric($exe_cote)) {
			if (is_numeric($rowData['expl_cb'])) {
				if ((int) $exe_cote === (int) $rowData['expl_cb']) $cb=(int)$rowData['expl_cb'];
				else continue;
			} else continue;
		} else $cb=$rowData['expl_cb'];
                

		if ($new_row > 8) // Nueva pagina, reseteamos x-position
		{
			
                       // Creamos una nueva pagina
			$pdf->AddPage();
			$pdf->SetMargins(H_MARGIN, V_MARGIN);
			$pdf->SetAutoPageBreak(false);
			$y = V_MARGIN;
			$x = H_MARGIN;
			$new_row=1;
			$new_col=1;
			} 

		$matriz2=explode(" ",$cote);			
		$pdf->SetFont('Arial','B',10);
		$pdf->SetY($y);
		$pdf->SetX($x);
		if (strlen($matriz2[0])>6) $align="";
		else $align="C";
		$pdf->Cell(LABEL_WIDTH/5, ($barcode_v_margin*1),$matriz2[0],"LT", 0, $align);
		$pdf->Cell(LABEL_WIDTH-1, ($barcode_v_margin*1),"","TR", 0,'C');
		$pdf->Ln();
		$pdf->SetX($x);
		$pdf->Cell(LABEL_WIDTH/5, ($barcode_v_margin*1),$matriz2[1],"L", 0,'C');
                $pdf->setFont('barcode',"",25);
		//$pdf->Cell(LABEL_WIDTH-1, ($barcode_v_margin*1),"*".f_rellena_ceros($cb)."*","R", 0,'C');
		$pdf->Cell(LABEL_WIDTH-1, ($barcode_v_margin*1),"*".$cb."*","R", 0,'C');
		$pdf->SetFont('Arial','B',10);
		$pdf->Ln();
		$pdf->SetX($x);
		$pdf->Cell(LABEL_WIDTH/5, ($barcode_v_margin*1),$matriz2[2],"LB", 0,'C');
		//$pdf->Cell(LABEL_WIDTH-1, ($barcode_v_margin*1),f_rellena_ceros($cb),"BR", 0,'C');
		$pdf->Cell(LABEL_WIDTH-1, ($barcode_v_margin*1),$cb,"BR", 0,'C');

		if (($new_col%COLS_PER_PAGE)==0) {
			$x = H_MARGIN;
			$y += LABEL_HEIGHT-0.03;
			$new_row++;
			$new_col=1;
		} else {
			$new_col++;
			$x += LABEL_WIDTH-0.10;
			}


		}
		@mysql_free_result($resultData);

	}else continue;
	//} else continue;

}



// Desconexión de la Base de Datos
mysql_close();

// Limpiar (ELIMINAR) el búfer de salida y Deshabilitar el Almacenamiento en El Mismo -12/02/2015
ob_end_clean();

$pdf->Output('tejuelo_legado.pdf', 'D'); // Salida es un pdf descargable 


}


}
?>
