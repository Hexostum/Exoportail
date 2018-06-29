<?php
class ep_rubrique extends ep_objet
{
	
	public $rubrique_nom = 'rubrique';
	
	public $variable_serveur_liste_autorise = array();
	
	public $module_liste_noms = array();
	public $module_liste = array(ep_module::POSITION_AVANT_RUBRIQUE=>array(),ep_module::POSITION_APRES_RUBRIQUE=>array());
	
	public $conteneur;
	/**
	 * exoportail
	 *
	 * @var exoportail
	 */
	public $my_serveur;
	
	/**
	 * Ressource de langue
	 *
	 * @var ep_ressources
	 */
	
	protected $my_configuration;
	protected $debug_contenu;
	protected $page_url;
	protected $contenu;
	
	public function __construct(exoportail &$my_serveur)
	{
		parent::__construct(array('objet_id'=>$this->rubrique_nom));
		$this->my_serveur = &$my_serveur;
		
		$my_ressource = new ep_ressources('rubrique_' . $this->rubrique_nom);
		//$this->my_ressource = &new ep_ressources('rubrique_' . $this->rubrique_nom);
		
		$this->my_configuration = new ep_configuration('rubrique_' . $this->rubrique_nom);
		$this->conteneur = $this->my_configuration->conteneur->texte_enfants('global');
		$this->page_url = $this->my_configuration->page_url->texte_enfants('');
		
		$this->my_serveur->titre_element_ajouter(ep_variables::ressource_variable_id(array('rubrique',$this->rubrique_nom),'titre'));
		
		$this->module_liste_nom_initialiser($this->my_configuration->module_liste_noms);
		$this->module_liste_initialiser();
		$this->contenu = $this->my_configuration->contenu->propriete_liste_copie();
		$this->page_courante_sans_lien();
	}
	
	public function variable_serveur_liste_copie()
	{
		return $this->variable_serveur_liste_autorise;
	}
	
	public function module_liste_nom_initialiser($liste)
	{
		
		if($liste instanceof ep_html ):
			if($liste->html_enfants_statut()):
				foreach($liste as $valeur):
					$this->module_liste_noms[] = $valeur->attribut_valeur('id');
				endforeach;
			endif;
		endif;
		
	}
	
	protected function module_liste_initialiser()
	{
		
		if(is_array($this->module_liste_noms)):
			foreach($this->module_liste_noms as $clef => $module_nom):
				$module = new ep_module($module_nom,$this);
				$this->module_liste[$module->position][$module->position_ordre] = $module;
			endforeach;
		endif;
	
	}
	
	protected function module_statut($position)
	{
		if(ep_tableau::s_propriete_lire($this->module_liste, $position)):
			if(count($this->module_liste[$position])>0):
				return true;
			endif;
		endif;	
		return false;
	}
	
	protected function modules_contenu($position)
	{
		if($this->module_statut($position)):
			$contenu = array();
			foreach($this->module_liste[$position] as &$module):
				$contenu = array_merge($contenu,$module->contenu());
			endforeach;
			return $contenu;
		else:
			return array();
		endif;	
	}
	
	public function contenu_analyse()
	{
		return $this->contenu;
	}
	
	public function contenu()
	{
		return $this->debug_commentaire(__FUNCTION__);
	}
	
	public function debug_contenu($contenu)
	{
		$this->debug_contenu=$contenu;
	}
	
	public function page_courante()
	{
		return $this->page_url;
	}
	
	private function page_courante_sans_lien()
	{
		if(EP_ARG==$this->page_url):
			ep_variables::variable_ajouter('{CONFIGURATION.LIENS.SITE.'.strtoupper($this->rubrique_nom).'.LIEN}','{RESSOURCES.LIENS.SITE.'.strtoupper($this->rubrique_nom).'_LIEN}',true);
		endif;
	}
	
	public function xtpl_variable($clef)
	{
		return '{XTPL.RUBRIQUE.'.strtoupper($this->rubrique_nom).'.'.strtoupper($clef).'}';
	}
	
	public function ressource_variable($clef)
	{
		return '{RESSOURCE.RUBRIQUE.'.strtoupper($this->rubrique_nom).'.'.strtoupper($clef).'}';
	}
}
?>