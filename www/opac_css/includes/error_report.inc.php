<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: error_report.inc.php,v 1.7.26.1 2018-01-09 14:02:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// fichier de configuration g�n�rale pour les rapports d'erreur PHP

//ini_set('display_errors', 1);
error_reporting (E_ERROR | E_PARSE);
//error_reporting (E_ALL);
