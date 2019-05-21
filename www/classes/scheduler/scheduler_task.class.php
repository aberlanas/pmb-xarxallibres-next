<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scheduler_task.class.php,v 1.1.2.2 2018-02-09 09:45:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path, $class_path;
require_once($include_path."/parser.inc.php");
require_once($include_path."/templates/taches.tpl.php");
require_once($include_path."/connecteurs_out_common.inc.php");
require_once($class_path."/scheduler/scheduler_planning.class.php");
require_once($class_path."/scheduler/scheduler_task_docnum.class.php");
require_once($class_path."/upload_folder.class.php");
require_once($class_path."/xml_dom.class.php");

//commands
define('RESUME','1');
define('SUSPEND','2');
define('STOP','3');
define('RETRY','4');
define('ABORT','5');
define('FAIL','6');

//status
define('WAITING','1');
define('RUNNING','2');
define('ENDED','3');
define('SUSPENDED','4');
define('STOPPED','5');
define('FAILED','6');
define('ABORTED','7');
		
class scheduler_task {
	protected $msg;							// Messages propres au type de t�che
	public $proxy;							// classe contenant les m�thodes de l'API
	public $id_tache=0;					//identifiant de la t�che
	public $report=array();				// rapport de la t�che
	public $statut;
	
	public function __construct($id_tache=0) {
		$this->id_tache = $id_tache+0;
		$this->get_messages();
	}
	
	public function get_id_type() {
		return $this->id_type;
	}
	
	//messages 
	public function get_messages() {
		global $base_path, $lang;
		
		$tache_path = $base_path."/admin/planificateur/".str_replace('scheduler_', '', get_called_class());
		if (file_exists($tache_path."/messages/".$lang.".xml")) {
			$file_name=$tache_path."/messages/".$lang.".xml";
		} else if (file_exists($tache_path."/messages/fr_FR.xml")) {
			$file_name=$tache_path."/messages/fr_FR.xml";
		}
		if ($file_name) {
			$xmllist=new XMLlist($file_name);
			$xmllist->analyser();
			$this->msg=$xmllist->table;
		}
	}
	
	public function setEsProxy($proxy) {
		$this->proxy = $proxy;
	}
	
	public function listen_commande($methode_callback) {
		global $dbh;
		
		$query_commande = "select status, commande, next_state from taches where id_tache=".$this->id_tache;
		$result = pmb_mysql_query($query_commande, $dbh);
		
		if (pmb_mysql_result($result,0,"commande") != '0') {
			$cmd = pmb_mysql_result($result,0,"commande");			
			$requete = "update taches set status=".pmb_mysql_result($result,0, "next_state").", commande=0, next_state=0 where id_tache=".$this->id_tache."";
			$res = pmb_mysql_query($requete, $dbh);
			if ($res) {
				$this->statut = pmb_mysql_result($result,0, "next_state");
				call_user_func($methode_callback,$cmd);	
			}
		}
	}
	
	// Envoi d'une commande par la tache, changement du statut de la t�che...
	public function send_command($state=''){
		global $dbh;
		
		if ($state != '') {
			$this->statut = $state;
			pmb_mysql_query("update taches set status=".$this->statut." where id_tache='".$this->id_tache."'", $dbh);
		}
	}
	
	protected function add_section_report($content='', $css_class='scheduler_report_section') {
		$this->report[] = "<tr><th class='".$css_class."'>".$content."</th></tr>";
	}
	
	protected function add_content_report($content='', $css_class='scheduler_report_content') {
		$this->report[] = "<tr><td class='".$css_class."'>".$content."</td></tr>";
	}
	
	protected function add_function_rights_report($method='', $group='') {
		global $msg;
		global $PMBusername;
		
		$this->report[] = "<tr><td>".sprintf($msg["planificateur_function_rights"],$method,$group,$PMBusername)."</td></tr>";
	}
	
	protected function add_rights_bad_user_report() {
		global $msg;
		global $PMBusername;
	
		$this->report[] = "<tr><th>".sprintf($msg["planificateur_rights_bad_user_rights"], $PMBusername)."</th></tr>";
	}
	
	/*
	 * Ex�cution de la t�che - M�thode appel�e par la classe sp�cifique
	 * Modification des donn�es de la base
	 */
	public function execute() {
		global $dbh,$charset;
			 
		//initialisation de la t�che planifi�e sur la base
		$this->initialize();
		//appel de la m�thode sp�cifique
		$this->execution();
		//finalisation de la t�che planifi�e sur la base
		$this->finalize();

		$result_success = pmb_mysql_query("select num_planificateur from taches where id_tache=".$this->id_tache);
		//mise � jour de la prochaine exec
		if (pmb_mysql_num_rows($result_success) == 1) {
			//planification d'une nouvelle t�che
			$scheduler_planning = new scheduler_planning(pmb_mysql_result($result_success,0,"num_planificateur"));
			$scheduler_planning->calcul_execution();
			$scheduler_planning->insertOfTask();
		}
	}
	
	public function get_task_params() {
		$params = "";
		if ($this->id_tache) {
			$result = pmb_mysql_query("select param from planificateur, taches where id_planificateur=num_planificateur and id_tache=".$this->id_tache);
			if ($result) $params = unserialize(pmb_mysql_result($result, 0,"param"));
		}
		return $params; 
	} 
	
	public function initialize() {
		global $dbh;
		
		$this->statut = RUNNING;

		$requete = "update taches set start_at = CURRENT_TIMESTAMP, status = ".$this->statut."
			where id_tache='".$this->id_tache."'";
		
		pmb_mysql_query($requete,$dbh);
	}

	public function finalize() {
		global $dbh,$base_path,$charset;
							
		$res = pmb_mysql_query("select indicat_progress from taches where id_tache=".$this->id_tache);
		$progress = pmb_mysql_result($res,0, "indicat_progress");
		
		if ($progress == 100) $this->statut=ENDED;
		else $this->statut = FAILED;
		
		//fin de l'ex�cution, mise � jour sur la base
		$req = "update taches set end_at = CURRENT_TIMESTAMP, status = ".$this->statut.", commande=0, rapport = '".htmlspecialchars(serialize($this->report), ENT_QUOTES,$charset)."',id_process=0
			where id_tache='".$this->id_tache."'";
		pmb_mysql_query($req,$dbh);
	}
	
	public function update_progression($percent) {
		global $dbh,$charset;
		
		if ($this->id_tache) {
			$requete = "update taches set indicat_progress ='".$percent."', rapport='".htmlspecialchars(serialize($this->report), ENT_QUOTES,$charset)."' where id_tache=".$this->id_tache;
			pmb_mysql_query($requete,$dbh);
		}
	}
	
	public function isUploadValide($id_tache) {
		global $dbh;
		
		$query_sel = "select distinct p.libelle_tache, p.rep_upload, p.path_upload from planificateur p
			left join taches t on t.num_planificateur = p.id_planificateur
			left join taches_docnum tdn on tdn.tache_docnum_repertoire=p.rep_upload
			where t.id_tache=".$id_tache;
		$res_query = pmb_mysql_query($query_sel, $dbh);
		if ($res_query) {
			$row = pmb_mysql_fetch_object($res_query);
			
			$up = new upload_folder($row->rep_upload);
			$nom_chemin = $up->formate_nom_to_path($up->repertoire_nom.$row->path_upload);
			if ((is_dir($nom_chemin)) && (is_writable($nom_chemin)))
				return true;
		}
		return false;
	}
	
	// que passer � cette fonction datas ou object ?? (objet pdf , contenu xls)
	public function generate_docnum($id_tache, $content, $mimetype="application/pdf", $ext_fichier="pdf") {
		global $dbh,$msg, $base_path;
		
		$tdn = new scheduler_task_docnum();
		
		$tdn->num_tache = $id_tache;
		
		$query_sel = "select distinct p.libelle_tache, p.rep_upload, p.path_upload from planificateur p
			left join taches t on t.num_planificateur = p.id_planificateur
			left join taches_docnum tdn on tdn.tache_docnum_repertoire=p.rep_upload
			where t.id_tache=".$tdn->num_tache;
		$res_query = pmb_mysql_query($query_sel, $dbh);
		if ($res_query) {
			$row = pmb_mysql_fetch_object($res_query);
			
			$up = new upload_folder($row->rep_upload);
			$nom_chemin = $up->formate_nom_to_path($up->repertoire_nom.$row->path_upload);
//			if ((!is_dir($nom_chemin)) || (!is_writable($nom_chemin))) {
//				$nom_chemin = $base_path."/temp/";
//			}
			//appel de fonction pour le calcul de nom de fichier
			$date_now = date('Ymd');
//			$tdn->tache_docnum_nomfichier = str_replace(" ", "_", $row->libelle_tache)."_".$date_now;
			$tdn->tache_docnum_nomfichier = clean_string_to_base($row->libelle_tache)."_".$date_now;
			$tdn->tache_docnum_contenu = $content;
			$tdn->tache_docnum_extfichier= $ext_fichier;
			$tdn->tache_docnum_file = "";
			$tdn->tache_docnum_mimetype = $mimetype;
			$tdn->tache_docnum_repertoire = $row->rep_upload;
			$tdn->tache_docnum_path = $row->path_upload;
			$path_absolu = $nom_chemin.$tdn->tache_docnum_nomfichier.".".$tdn->tache_docnum_extfichier;
			if (file_exists($path_absolu)) {
				$i=2;
				while (file_exists($nom_chemin.$tdn->tache_docnum_nomfichier."_".$i.".".$tdn->tache_docnum_extfichier)) {
					$i++;
				}
				$path_absolu = $nom_chemin.$tdn->tache_docnum_nomfichier."_".$i.".".$tdn->tache_docnum_extfichier;
				$tdn->tache_docnum_nomfichier = $tdn->tache_docnum_nomfichier."_".$i;
			}
			$path_absolu = $up->encoder_chaine($path_absolu);
						
			//verifier permissions d'ecriture...
			if (is_writable($nom_chemin)) {
				switch ($mimetype) {
					case "application/pdf" :
						$content->Output($path_absolu,"F");
						break;
					case "application/ms-excel" :
						file_put_contents($path_absolu, $content);
						break;
				}
//				if ($mimetype == "application/pdf") {
//					$content->Output($path_absolu,"F");	
//				} else if ($mimetype == "application/ms-excel") {
//					file_put_contents($path_absolu, $content);
//				}
				
				$tdn->save();
				$this->report[] = "<tr><td>".$msg["planificateur_write_success"]." : <a target='_blank' href='./tache_docnum.php?tache_docnum_id=".$tdn->id_tache_docnum."'>".$tdn->tache_docnum_nomfichier.".".$tdn->tache_docnum_extfichier."</a></td></tr>";
				return true;
			} else {
				$this->report[] = "<tr><td>".sprintf($msg["planificateur_write_error"],$path_absolu)."</td></tr>";
				return false;
			}		
		}
	}
	
	public function unserialize_task_params() {
		return $this->get_task_params();
	}
	
	public function suspend() {
		while ($this->statut == SUSPENDED) {
			sleep(20);
			$this->statut = $this->listen_commande(array(&$this,"traite_commande"));
		}
	}
	
	public function traite_commande($cmd,$message = '') {
		switch ($cmd) {
			case RESUME :
				$this->send_command(WAITING);
				break;
			case SUSPEND :
				$this->suspend();
				break;
			case STOP :
				$this->finalize();
				die();
				break;
			case ABORT :
				$this->abort();
				$this->finalize();
				die();
				break;
			case FAIL :
				$this->finalize();
				die();
				break;
		}
	}
}