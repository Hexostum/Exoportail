<?php
class ep_liens_generiques extends ep_objet
{
	public $liens_liste = array();
	
	public function __construct(array $liens_liste=array('exostum','site'))
	{
		parent::__construct();
		foreach($liens_liste as $valeur):
			$this->liste_lien_initialiser($valeur);
			$this->liens_analyser($valeur);
		endforeach;
	}
	
	public function liste_lien_initialiser($lien_categorie)
	{
		$ressources = new ep_ressources('liens_' . $lien_categorie);
		$configuration = new ep_configuration('liens_' . $lien_categorie);
		
		if($configuration->statut()):
			foreach($configuration as $lien_infos):
				$this->liens_liste[$lien_categorie][$lien_infos->attribut_valeur('id')]['href'] = $lien_infos->attribut_valeur('href');
				$this->liens_liste[$lien_categorie][$lien_infos->attribut_valeur('id')]['lien'] = $ressources->propriete_lire($lien_infos->attribut_valeur('id').'_lien',"");
				$this->liens_liste[$lien_categorie][$lien_infos->attribut_valeur('id')]['title'] = $ressources->propriete_lire($lien_infos->attribut_valeur('id').'_title',""); 
			endforeach;
		endif;
	}
	
	
	
	
	
	
	/**
	 * Enter description here...
	 *
	 * @param string $lien_categorie
	 * @param ep_html $conteneur_liste
	 * @param ep_html $conteneur_lien
	 */
	
	public function generer_liste_lien($lien_categorie,$conteneur_liste,$conteneur_lien,$page_courante)
	{
		$liens = array();
		foreach ($this->liens_liste[$lien_categorie] as $clef => $valeur):
		$conteneur_lien2 = clone $conteneur_lien;
		/**/
			if(trim($valeur['href']) == trim($page_courante) ):
				$conteneur_lien2->propriete_liste_initialiser
				(
					array
					(
						ep_html::html_tag
						(
							'span',
							array( $valeur['lien']	),
							array( 'class' => 'site_courant' )
						)
					)
				);
			else:
			/**/
				$conteneur_lien2->propriete_liste_initialiser
				(
					array
					(
						ep_html::html_tag
						(
							'a',
							array
							(
								$valeur['lien']
							),
							array
							(
								'href' => $valeur['href'],
								'title'=>$valeur['title']
							)
						)
						
					)
				);
				/**/
			endif;
			/**/
			$liens[] = $conteneur_lien2;
		endforeach;
		$conteneur_liste->propriete_liste_initialiser
		(
			$liens
		);
		return $conteneur_liste;
	}
	
	public function liens_analyser($lien_categorie)
	{
		exoportail::debug_todo(__FILE__,__LINE__,__FUNCTION__);
		/**
			$lien_variable_id = '{CONFIGURATION.LIENS.'.strtoupper($lien_categorie).'.'.strtoupper($lien_infos->attribut_valeur('id')).'.LIEN}';
			$lien_href_variable_id = '{CONFIGURATION.LIENS.'.strtoupper($lien_categorie).'.'.strtoupper($lien_infos->attribut_valeur('id')).'.HREF}';
			$lien_href = $lien_infos->attribut_valeur('href');
			$lien_texte_variable_id = '{RESSOURCES.LIENS.'.strtoupper($lien_categorie).'.'.strtoupper($lien_infos->attribut_valeur('id')).'_LIEN}';
			$lien_title_variable_id = '{RESSOURCES.LIENS.'.strtoupper($lien_categorie).'.'.strtoupper($lien_infos->attribut_valeur('id')).'_TITLE}';
			$lien =
				ep_html::html_tag
				(
					'a',
					$lien_texte_variable_id,
					array('href' => $lien_href,'title'=>$lien_title_variable_id)
				)
			;
			ep_variables::variable_ajouter($lien_variable_id, $lien , true);
			ep_variables::variable_ajouter($lien_href_variable_id,$lien_href);				
		/**/
			
	}
	
	public static function configuration_variable($categorie,$id,$type)
	{
		return '{CONFIGURATION.LIENS.'.strtoupper($categorie).'.'.strtoupper($id).'.' . strtoupper($type).'}';
	}
	
	public static function ressource_variable($categorie,$id,$type)
	{
		return '{RESSOURCES.LIENS.'.strtoupper($categorie).'.'.strtoupper($id).'.' . strtoupper($type).'}';
	}
	
}
?>