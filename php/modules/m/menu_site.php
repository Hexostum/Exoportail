<?php
class ep_module_menu_exostum extends ep_module
{
	public function __construct(exoportail &$my_serveur)
	{
		//$this->position = ep_module::POSITION_AVANT_RUBRIQUE;
		//$this->position_ordre = 1;
		$this->module_nom='menu_exostum';
		parent::__construct($my_serveur);
	}
	/**
	public function contenu()
	{
		$contenu = array();
		$contenu[] = parent::contenu();

		
		$exostum_liens = 
			array
			(
				'{EXOSTUM:exostum}',
				'{EXOSTUM:codesgratis}',
				'{EXOSTUM:forum}',
				'{LIEN:dimensionrpg}',
				'{LIEN:blog}'
			)
		;
		
		$exostum_lis = array();
		foreach($exostum_liens as $lien):
			$exostum_lis[] = 
				ep_html::html_tag
				(
					'li',
					$lien
				)
			;
		endforeach;
		$menu_exostum = array();
		$menu_exostum[] = ep_html::html_tag
		(
			'h2',
			'Entreprise Exostum'
		);
		
		$menu_exostum[] = 
			ep_html::html_tag
			(
				'ul',
				$exostum_lis,
				array('id' => 'menu_exostum')
			)
		;
		
		
		$contenu[] = ep_html::html_tag
		(
			'div',
			$menu_exostum,
			array('class' => 'module' , 'id'=> $this->module_nom)
		);
		$contenu[] = parent::contenu();
		return $contenu;
	}
	/**/
}
?>