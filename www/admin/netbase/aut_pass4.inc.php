<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: aut_pass4.inc.php,v 1.11 2017-06-07 12:27:03 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// la taille d'un paquet de notices
$lot = AUTHOR_PAQUET_SIZE; // defini dans ./params.inc.php

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

if(!$count) {
	$notices = pmb_mysql_query("SELECT count(1) FROM responsability where responsability_author<>0 ", $dbh);
	$count = pmb_mysql_result($notices, 0, 0) ;
}

print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_responsabilites"], ENT_QUOTES, $charset)." : 2</h2>";

$query = pmb_mysql_query("delete responsability from responsability left join authors on responsability_author=author_id where author_id is null ");
$affected += pmb_mysql_affected_rows();

$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_responsabilites"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_responsabilites"], ENT_QUOTES, $charset);
$opt = pmb_mysql_query('OPTIMIZE TABLE authors');
// mise � jour de l'affichage de la jauge
$spec = $spec - CLEAN_AUTHORS;
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec, $affected, '0');
