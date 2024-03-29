<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_timeline_datasource_authors.class.php,v 1.1.2.1 2017-10-09 15:22:57 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/notice.class.php');
require_once($class_path.'/parametres_perso.class.php');

class cms_module_timeline_datasource_authors extends cms_module_timeline_datasource_authorities{

	protected static $prefix = 'author';
	
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_generic_authorities_authors", //A revoir assez rapidement 
		);
	}

	/**
	 * D�finition des champs utilisables pour la valorisation 
	 * de la structure JSON renvoy�e par la source
	 */
	protected function init_usable_fields(){
		/** Les diff�rents champs de titres + les champs perso non r�petable de type small texte **/
		$this->title_fields = array_merge(array(
			"isbd" => $this->msg['cms_module_timeline_datasource_authors_isbd'],
		), $this->get_perso_fields('text', 'small_text'));

		/** Le champs r�sum� + les champs de type text large unique **/
		$this->resume_fields = array_merge(array(
			"author_comment" => $this->msg['cms_module_timeline_datasource_authors_comment']
		), $this->get_perso_fields('text', 'text'));
		
		
		$this->image_fields = array_merge(array(
			"thumbnail_url" => $this->msg['cms_module_timeline_datasource_authors_thumbnail_url']
		), $this->get_perso_fields('url', 'text'));
		
		$this->date_fields = array_merge(array(), $this->get_perso_fields('date_box', 'date'));
		
	}
}