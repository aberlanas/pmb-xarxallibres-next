<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: retour.inc.php,v 1.33 2010-07-06 10:08:48 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$class_path/emprunteur.class.php");
require_once("$class_path/serial_display.class.php");
require_once("$class_path/comptes.class.php");
require_once("$class_path/amende.class.php");
require_once("$include_path/resa.inc.php");
require_once("$class_path/expl_to_do.class.php");

// gestion des retours
if ($_GET['id_empr']) {
	$form_id_empr=$_GET['id_empr'];
	$_GET['id_empr']='';
	$confirmed=1;
} else {
	if ($pmb_confirm_retour) $confirmed=0;
		else $confirmed=1;
}	

//LA SENIA
echo("<hr>");
echo("<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>");


require("$base_path/includes/db_param.inc.php");

$link2 = @mysql_connect(SQL_SERVER, USER_NAME, USER_PASS) OR die("Error MySQL");

$q ='SELECT expl_cb FROM exemplaires';
$q = 'select exemplaires.expl_cb , notices.tit1 from pret, exemplaires,empr, notices where notices.notice_id=exemplaires.expl_notice and pret.pret_idempr=empr.id_empr and pret.pret_idexpl=exemplaires.expl_id and empr.id_empr='.$form_id_empr.' order by pret.pret_date desc';

$q = 'select exemplaires.expl_cb , notices.tit1, docs_statut.statut_libelle from pret, exemplaires,empr, notices,docs_statut where notices.notice_id=exemplaires.expl_notice and pret.pret_idempr=empr.id_empr and pret.pret_idexpl=exemplaires.expl_id and exemplaires.expl_statut=docs_statut.idstatut and empr.id_empr='.$form_id_empr.' and pret.pret_date < \'2019-06-20\' order by pret.pret_date desc ';

echo "<table id=\"tDev\" ><thead><th>ID</th><th>Titulo</th><th>Estado</th><th>Devolver</th><th>Retirar</th><th>No Devolver</th></thead><tbody>";

	$resultData = @mysql_query($q, $link2);
		if (@mysql_num_rows($resultData) != 0) {
			while ($rowData = mysql_fetch_array($resultData)) {	
				echo "<tr>";
				echo "<td name=\"valor\"> <a href=http://biblioteca.ieslasenia.org/circ.php?categ=retour&cb_expl=".$rowData[0].">".$rowData[0]."</a></td><td>".$rowData[1]."</td>";
				echo "<td> ".$rowData[2]." </td>";
				echo "<td> <input class=devolucion type=radio name=devolucion".$rowData[0]." value=devolver checked> Devolver</td>";
				echo "<td> <input class=devolucion type=radio name=devolucion".$rowData[0]." value=retirar> Retirar </td>";
				echo "<td> <input class=devolucion type=radio name=devolucion".$rowData[0]." value=nodevolver> No Devolver </td>";
				echo "</tr>";


//$form_id_empr=$rowData[0];
//$expl=new expl_to_do($form_id_empr);

//if($form_id_empr) {
//	$expl->do_form_retour($action_piege,$piege_resa);
//}
//print $expl->cb_tmpl.$expl->expl_form;
			}
		}
	mysql_free_result($resultData);


echo "</tbody></table>";

echo "<script src=senia.js></script> ";

echo("<button id=butDevuelve> Devuelve ya </button>"); 



