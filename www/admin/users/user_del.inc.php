<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: user_del.inc.php,v 1.7 2015-04-03 11:16:23 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$user_encours=$_COOKIE["PhpMyBibli-LOGIN"];

if($id && $id !=1) {
	$requete = "select username from users where userid=$id ";
	$res=pmb_mysql_fetch_row( pmb_mysql_query($requete, $dbh));
	$username_del=$res[0];
	$requete = "DELETE FROM users WHERE userid=$id and username<>'".$user_encours."'";
	$res = pmb_mysql_query($requete, $dbh);
	if ($res) {
		$requete = "DELETE FROM sessions WHERE login='".$username_del."'";
		$res = pmb_mysql_query($requete, $dbh);
		$requete = "DELETE FROM es_methods_users WHERE num_user=$id ";
		$res = pmb_mysql_query($requete, $dbh);
	}
	$requete = "OPTIMIZE TABLE users ";
	$res = pmb_mysql_query($requete, $dbh);
}
show_users($dbh);
