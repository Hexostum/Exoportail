<?php
class ep_module_menu_exostum extends ep_module
{
	private $liens_liste = array();
	public function __construct($rubrique)
	{
		$this->module_nom='menu_exostum';
		parent::__construct($this->module_nom,$rubrique);
		$this->liens_initialiser();
	}
	public function liens_initialiser()
	{
		$config_liste_liens_xml_fichier_nom = (string)$this->my_configuration->liste_liens->texte_enfants("");
		try
		{
			$config_liste_liens = new ep_configuration($config_liste_liens_xml_fichier_nom);
			foreach($config_liste_liens as $valeur):
				$this->liens_liste[$valeur->attribut_valeur('id')] = $valeur->attribut_valeur('href');
			endforeach;
		}
		catch(Exception $e)
		{
			
		}
	}
	public function contenu()
	{
		$menu_ul = $this->my_configuration->menu_ul->propriete_liste_copie();
		
		$menu_titre = $this->my_configuration->menu_titre[0];
		$menu_titre->propriete_liste_initialiser(array (ep_variables::ressource_variable_id($this->module_nom, 'menu_titre')));
		
		$menu_li_template = $this->my_configuration->menu_li->propriete_liste_copie()[0];
		$liste_lis = array();
		foreach($this->liens_liste as $clef => $valeur):
			
			$current_li = clone $menu_li_template;
			$current_li->propriete_liste_initialiser
			(
				array
				(
					ep_html::html_tag
					(
						"a",
						"{RESSOURCES.LIENS.MENU_EXOSTUM.". $clef .".LIEN_TEXTE}",
						array('href' => $valeur,'title' => "{RESSOURCES.LIENS.MENU_EXOSTUM.". $clef .".LIEN_TITLE}")
					)
				)
			);
			$liste_lis[] = $current_li;
		endforeach;
		print_r($liste_lis);
		exit();
		
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