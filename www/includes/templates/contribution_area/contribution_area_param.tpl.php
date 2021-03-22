<?php 
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: contribution_area_param.tpl.php,v 1.3 2017-05-18 14:33:48 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$contribution_area_param_form = 
"<form class='form-".$current_module."' id='contribution_area' name='contribution_area'  method='post' action=\"admin.php?categ=contribution_area&sub=param&action=save\" >
	<h3>".$msg['admin_contribution_area_param_title']."</h3>
	<div class='form-contenu'>
		<div class='row'>
			<label class='etiquette' for='user_name'>".$msg['es_user_username']."</label>
		</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='user_name' id='user_name' value='!!user_name!!' />
		</div>
		<div class='row'>
			<label class='etiquette' for='user_password'>".$msg['es_user_password']."</label>
		</div>
		<div class='row'>
			<input type='password' name='user_password' id='user_password' value='!!user_password!!'/>
		</div>
		<div class='row'>
			<label class='etiquette' for='source_url'>".$msg['demandes_url_docnum']."</label>
		</div>
		<div class='row'>
			<input type='text' class='saisie-50em' name='source_url' id='source_url' value='!!source_url!!' />
		</div>			
		<div class='row'> 
		</div>
	</div>	
	<h3>".$msg['admin_contribution_area_param_opac_title']."</h3>	
	<div class='form-contenu'>	
		<div class='row'> 
			<label for='show_sub_form'>".$msg['admin_contribution_area_show_sub_form']."</label>
			<input id='show_sub_form' type='checkbox' !!show_sub_form!! value='1' name='show_sub_form'>
		</div>			
		<div class='row'> 
		</div>
	</div>	
	<div class='row'>	
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['admin_nomenclature_voice_form_exit']."'  onclick=\"document.location='./admin.php?categ=contribution_area'\"  />
			<input type='submit' class='bouton' value='".$msg['admin_nomenclature_voice_form_save']."' onclick=\"document.getElementById('action').value='save';if (!test_form(this.form)) return false;\" />
		</div>
	</div>
<div class='row'></div>
</form>";