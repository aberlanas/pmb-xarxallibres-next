<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_perso.class.php,v 1.13.2.1 2017-08-22 09:20:30 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classes de gestion des recherches personnalis�es

// inclusions principales
require_once("$include_path/templates/search_perso.tpl.php");
require_once("$class_path/search.class.php");
require_once("$class_path/searcher_tabs.class.php");
require_once("$class_path/users.class.php");

class search_perso {

	public $type;
	public $name;
	public $shortname;
	public $comment;
	public $query;
	public $human;
	public $directlink;
	public $autorisations;
	public $search_perso_user;
	public $directlink_user;
	public $order;
	
	// constructeur
	public function __construct($id=0, $type='RECORDS') {
		$this->id = $id;
		$this->type = $type;
		$this->fetch_data();
	}
    
	// r�cup�ration des infos en base
	protected function fetch_data() {
		global $PMBuserid;
		
		$this->name='';
		$this->shortname='';
		$this->comment='';
		$this->query='';
		$this->human='';
		$this->directlink='';
		$this->autorisations=$PMBuserid;
		$this->order = 0;
		if($this->id) {
			$query = "SELECT * FROM search_perso WHERE search_id='".$this->id."'";
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result)) {
				$row = pmb_mysql_fetch_object($result);
				$this->type=$row->search_type;
				$this->name=$row->search_name;
				$this->shortname=$row->search_shortname;
				$this->comment=$row->search_comment;
				$this->query=$row->search_query;
				$this->human=$row->search_human;
				$this->directlink=$row->search_directlink;
				$this->autorisations=$row->autorisations;
				$this->order = $row->search_order;
			}
		}
		//On r�cup�re �galement ses recherches pr�d�finies
		$this->fetch_search_perso_user();
	}
	
	protected function fetch_search_perso_user() {
		global $PMBuserid;
		
		$query = "SELECT * FROM search_perso WHERE search_type = '".$this->type."'";
		if ($PMBuserid!=1) $query .= " AND (autorisations='$PMBuserid' or autorisations like '$PMBuserid %' or autorisations like '% $PMBuserid %' or autorisations like '% $PMBuserid') ";
		$query .= " order by search_order, search_name ";
		$result = pmb_mysql_query($query);
		$this->search_perso_user=array();
		$link="";
		if(pmb_mysql_num_rows($result)){
			$i=0;
			while($row = pmb_mysql_fetch_object($result)) {
				if($row->search_directlink) {
					if($row->search_shortname)$libelle=$row->search_shortname;
					else $libelle=$row->search_name;
					$link.="
						<span>
							<a href=\"javascript:document.forms['search_form".$row->search_id."'].submit();\">$libelle</a>
									</span>
									";
				}
				$this->search_perso_user[$i]= new stdClass();
				$this->search_perso_user[$i]->id=$row->search_id;
				$this->search_perso_user[$i]->type=$row->search_type;
				$this->search_perso_user[$i]->name=$row->search_name;
				$this->search_perso_user[$i]->comment=($row->search_comment?"<br />(".$row->search_comment.")":"");
				$this->search_perso_user[$i]->shortname=$row->search_shortname;
				$this->search_perso_user[$i]->query=$row->search_query;
				$this->search_perso_user[$i]->human=$row->search_human;
				$this->search_perso_user[$i]->directlink=$row->search_directlink;
				$this->search_perso_user[$i]->order=$row->search_order;
				$i++;
			}
		}
		$this->directlink_user=$link;
	}
	
	public function set_properties_form_form() {
		global $name, $shortname, $query, $human, $directlink, $autorisations, $comment;
		
		$this->name = stripslashes($name);
		$this->shortname = stripslashes($shortname);
		$this->comment = stripslashes($comment);
		$this->query = stripslashes($query);
		$this->human=stripslashes($human);
		$this->directlink=($directlink ? 1 : 0);
		if (is_array($autorisations)) {
			$this->autorisations = implode(" ",$autorisations);
		}else {
			$this->autorisations = "1";
		}
	}
	
	public function set_order($order=0) {
		$order += 0;
		if(!$order) {
			$query = "select max(search_order) as max_order from search_perso";
			$result = pmb_mysql_query($query);
			$order = pmb_mysql_result($result, 0)+1;
		}
		$this->order = $order;
	}
	
	public function save() {
		if($this->id) {
			$query = 'update search_perso set ';
			$where = 'where search_id = '.$this->id;
		} else {
			$query = 'insert into search_perso set ';
			$where = '';
			$this->set_order(0);
		}
		$query .= '
				search_type = "'.$this->type.'",
				search_name = "'.addslashes($this->name).'",
				search_shortname = "'.addslashes($this->shortname).'",
				search_comment = "'.addslashes($this->comment).'",
				search_query = "'.addslashes($this->query).'",
				search_human = "'.addslashes($this->human).'",
				search_directlink = "'.$this->directlink.'",
				autorisations = "'.$this->autorisations.'",
				search_order = "'.$this->order.'"
				'.$where;
		$result = pmb_mysql_query($query);
		if($result) {
			$indice = 0;
			if(!$this->id) {
				$this->id = pmb_mysql_insert_id();
			}
			$this->fetch_search_perso_user();
			return true;
		} else {
			if($this->id) {
				error_message($msg["search_perso_form_edit"], $msg["search_perso_form_add_error"],1);
			} else {
				error_message($msg["search_perso_form_add"], $msg["search_perso_form_add_error"],1);
			}
			return false;
		}
	}
	
	// fonction g�n�rant le form de saisie 
	public function do_form() {
		global $msg,$tpl_search_perso_form,$charset;	
		
		// titre formulaire
		if($this->id) {
			$libelle=$msg["search_perso_form_edit"];
			$link_delete="<input type='button' class='bouton' value='".$msg[63]."' onClick=\"confirm_delete();\" />";
			
		} else {
			$libelle=$msg["search_perso_form_add"];
			$link_delete="";
			if($this->type == 'AUTHORITIES') {
				$my_search=new search_authorities(true, 'search_fields_authorities');
			} else {
				$my_search=new search();
			}
			$this->query=$my_search->serialize_search();
			$this->human = $my_search->make_human_query();		
		}
		// Champ �ditable
		$tpl_search_perso_form = str_replace('!!id!!', htmlentities($this->id,ENT_QUOTES,$charset), $tpl_search_perso_form);
		$tpl_search_perso_form = str_replace('!!name!!', htmlentities($this->name,ENT_QUOTES,$charset), $tpl_search_perso_form);
		$tpl_search_perso_form = str_replace('!!shortname!!', htmlentities($this->shortname,ENT_QUOTES,$charset), $tpl_search_perso_form);
		$tpl_search_perso_form = str_replace('!!comment!!', htmlentities($this->comment,ENT_QUOTES,$charset), $tpl_search_perso_form);
		if($this->directlink) $checked= " checked='checked' ";
		else $checked= "";
		$tpl_search_perso_form = str_replace('!!directlink!!', $checked, $tpl_search_perso_form);
	
		if ($this->id) {
			$tpl_search_perso_form = str_replace('!!autorisations_users!!', users::get_form_autorisations($this->autorisations,0), $tpl_search_perso_form);
		} else {
			$tpl_search_perso_form = str_replace('!!autorisations_users!!', users::get_form_autorisations("",1), $tpl_search_perso_form);
		}
	
		$tpl_search_perso_form = str_replace('!!query!!', htmlentities($this->query,ENT_QUOTES,$charset), $tpl_search_perso_form);
		$tpl_search_perso_form = str_replace('!!human!!', htmlentities($this->human,ENT_QUOTES,$charset), $tpl_search_perso_form);
		
		$tpl_search_perso_form = str_replace('!!delete!!', $link_delete, $tpl_search_perso_form);
		$tpl_search_perso_form = str_replace('!!libelle!!',htmlentities($libelle,ENT_QUOTES,$charset) , $tpl_search_perso_form);
		
		$link_annul = "onClick=\"unload_off();history.go(-1);\"";
		$tpl_search_perso_form = str_replace('!!annul!!', $link_annul, $tpl_search_perso_form);
		
		return $tpl_search_perso_form;	
	}

	// fonction g�n�rant le form de saisie 
	public function do_list() {
		global $tpl_search_perso_liste_tableau,$tpl_search_perso_liste_tableau_ligne;	
		
		if($this->type == 'AUTHORITIES') {
			$searcher_tabs = new searcher_tabs();
			$target_url = "./autorites.php?categ=search&mode=".$searcher_tabs->get_mode_multi_search_criteria();
			$my_search=new search_authorities(true, 'search_fields_authorities');
		} else {
			$target_url = "./catalog.php?categ=search&mode=6";
			$my_search=new search();
		}
		// liste des lien de recherche directe
		$tpl_search_perso_liste_tableau = str_replace('!!preflink!!',$this->directlink_user , $tpl_search_perso_liste_tableau);
		$tpl_search_perso_liste_tableau = str_replace('!!link_add!!',$target_url."&search_perso=add" , $tpl_search_perso_liste_tableau);
		$forms_search="";
		$liste="";
		// pour toute les recherche de l'utilisateur
		for($i=0;$i<count($this->search_perso_user);$i++) {
			if ($i % 2) $pair_impair = "even"; else $pair_impair = "odd";
			
			if($this->type == 'AUTHORITIES') {
				$target_url = "./autorites.php?categ=search&mode=".$searcher_tabs->get_mode_multi_search_criteria($this->search_perso_user[$i]->id);
			} else {
				$target_url = "./catalog.php?categ=search&mode=6";
			}
			
			//composer le formulaire de la recherche
			$my_search->unserialize_search($this->search_perso_user[$i]->query);
			$forms_search.= $my_search->make_hidden_search_form($target_url,"search_form".$this->search_perso_user[$i]->id);
			
			
	        $td_javascript="  onmousedown=\"document.forms['search_form".$this->search_perso_user[$i]->id."'].submit();\" ";
	        $tr_surbrillance = "onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";
	
	        $line = str_replace('!!td_javascript!!',$td_javascript , $tpl_search_perso_liste_tableau_ligne);
	        $line = str_replace('!!tr_surbrillance!!',$tr_surbrillance , $line);
	        $line = str_replace('!!pair_impair!!',$pair_impair , $line);
	
			$line =str_replace('!!id!!', $this->search_perso_user[$i]->id, $line);
			$line = str_replace('!!name!!', $this->search_perso_user[$i]->name, $line);
			$line = str_replace('!!comment!!', $this->search_perso_user[$i]->comment, $line);
			$line = str_replace('!!human!!', $this->search_perso_user[$i]->human, $line);		
			$line = str_replace('!!shortname!!', $this->search_perso_user[$i]->shortname, $line);
			if($this->search_perso_user[$i]->directlink)
				$directlink="<img src='./images/tick.gif' border='0'  hspace='0' align='middle'  class='bouton-nav' value='=' />";
			else $directlink="";
			$line = str_replace('!!directlink!!', $directlink, $line);
			
			$liste.=$line;
		}
		$tpl_search_perso_liste_tableau = str_replace('!!lignes_tableau!!',$liste , $tpl_search_perso_liste_tableau);
		return $forms_search.$tpl_search_perso_liste_tableau;	
	}

	public function get_forms_list() {
		
		if($this->type == 'AUTHORITIES') {
			$searcher_tabs = new searcher_tabs();
			$my_search=new search_authorities(true, 'search_fields_authorities');
		} else {
			$my_search=new search();
		}
		$forms_search='';
		$links='';
		for($i=0;$i<count($this->search_perso_user);$i++) {
			if($this->type == 'AUTHORITIES') {
				$target_url = "./autorites.php?categ=search&mode=".$searcher_tabs->get_mode_multi_search_criteria($this->search_perso_user[$i]->id);
			} else {
				$target_url = "./catalog.php?categ=search&mode=6";
			}
			//composer le formulaire de la recherche
			$my_search->unserialize_search($this->search_perso_user[$i]->query);
			$forms_search.= $my_search->make_hidden_search_form($target_url,"search_form".$this->search_perso_user[$i]->id);
			$libelle= $this->search_perso_user[$i]->name;
			$links.="
				<span>
					<a href=\"javascript:document.forms['search_form".$this->search_perso_user[$i]->id."'].submit();\">$libelle</a>
				</span><br/>";
		}
		return $forms_search.$links;
	}

	// suppression d'une collection ou de toute les collections d'un p�riodique
	public function delete() {
		if($this->id) {
			pmb_mysql_query("DELETE from search_perso WHERE search_id='".$this->id."' ");
			$this->fetch_search_perso_user();
		}
	}
	
	// fonction permettant d'acc�der directement � une recherche pr�d�finie
	public function launch() {
		if($this->id) {
			$my_search=new search();
			$my_search->unserialize_search($this->query);
			print $my_search->make_hidden_search_form("./catalog.php?categ=search&mode=6","search_form".$this->id);
			print "<script type='text/javascript'>document.forms['search_form".$this->id."'].submit();</script>";
		} else {
			print $this->do_list();
		}
	}

} // fin d�finition classe
