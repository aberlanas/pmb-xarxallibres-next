<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: searcher_authorities_concepts.class.php,v 1.1 2017-01-06 16:10:51 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/searcher/searcher_autorities.class.php');

class searcher_authorities_concepts extends searcher_autorities {
	
	/**
	 * 
	 * @var searcher_autorities_skos_concepts
	 */
	protected $searcher_authorities_skos_concept;

	public function __construct($user_query){
		parent::__construct($user_query);
		$this->authority_type = AUT_TABLE_CONCEPT;
		$this->searcher_authorities_skos_concept = new searcher_autorities_skos_concepts($user_query);
		$this->searcher_authorities_skos_concept->add_fields_restrict($this->field_restrict);
	}
	
	public function _get_search_type(){
		return parent::_get_search_type()."_concepts";
	}

	public function get_raw_query()
	{
		// TODO g�rer un selecteur de sch�ma dans authorities.xml, pour l'instant on regarde dans tous les sch�mas
		global $concept_scheme;
		$concept_scheme = -1;
		return 'select '.$this->object_key.' from ('.$this->searcher_authorities_skos_concept->get_raw_query().') as uni join authorities on uni.'.$this->searcher_authorities_skos_concept->object_key.' = authorities.num_object and type_object = '.$this->authority_type;
	}
	
	public function get_pert_result($query = false) {
		global $dbh;
		$this->table_tempo = '';
		if ($this->searcher_authorities_skos_concept->get_result() && ($this->user_query != '*')) {
			$pert_result = $this->searcher_authorities_skos_concept->get_pert_result($query);
			if ($query) {
				return 'select '.$this->object_key.', pert from ('.$pert_result.') as uni join authorities on uni.'.$this->searcher_authorities_skos_concept->object_key.' = authorities.num_object and type_object = '.$this->authority_type;
			}
			$this->table_tempo = 'search_result'.md5(microtime(true));
			$pert_result = 'select '.$this->object_key.', pert from '.$pert_result.' join authorities on '.$pert_result.'.'.$this->searcher_authorities_skos_concept->object_key.' = authorities.num_object and type_object = '.$this->authority_type;
			$rqt = 'create temporary table '.$this->table_tempo.' '.$pert_result;
			$res = pmb_mysql_query($rqt,$dbh);
			pmb_mysql_query('alter table '.$this->table_tempo.' add index i_id('.$this->object_key.')',$dbh);
		}
		return $this->table_tempo;
	}
	
}