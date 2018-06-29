<?php
class ep_module extends ep_objet
{
	/**
	 * Position du module : Avant une rubrique
	 *
	 */
	const POSITION_AVANT_RUBRIQUE='avant_rubrique';
	/**
	 * Position du module : Après une rubrique
	 *
	 */
	const POSITION_APRES_RUBRIQUE='apres_rubrique';
	/**
	 * Position du module : Dans un autre module
	 *
	 */
	const POSITION_DANS_MODULE = 'dans_module';
	const POSITION_DECORATION_AVANT_MODULE = 'decoration_avant_module';
	const POSITION_DECORATION_APRES_MODULE = 'decoration_apres_module';
	const POSITION_DECORATION_MODULE = 'decoration_module';
	
	public $module_nom;
	public $module_liste_noms = array();
	public $module_liste = array();
	
	public $position;
	public $position_ordre=0;
	/**
	 * Enter description here...
	 *
	 * @var ep_html
	 */
	public $conteneur;
	public $conteneur_statut = false;
	
	/**
	 * @var ep_html
	 */
	public $conteneur_contenu;
	public $conteneur_contenu_statut=false;
	
	/**
	 * Ressource de langue
	 *
	 * @var ep_ressources
	 */
	protected $my_ressource;
	
	/**
	 * Configuration du module
	 * @var ep_configuration
	 */
	protected $my_configuration;
	
	protected $config_par_defaut;
	
	protected $contenu_html;
	
	
	/**
	 * Liste des modules de décoration
	 *
	 * @var unknown_type
	 */	
	protected $decorations = array(ep_module::POSITION_DECORATION_AVANT_MODULE=>array(),ep_module::POSITION_DECORATION_APRES_MODULE=>array());
	
	public $rubrique;
	
	public function __construct($module_nom,$rubrique)
	{
		parent::__construct();
		$this->module_nom = $module_nom;
		$this->objet_id=$module_nom;
		$this->rubrique = $rubrique;
		try 
		{
			$this->config_par_defaut = false;
			$this->my_ressource = new ep_ressources('module_' . $this->module_nom);
			$this->ressources_variables();
			$this->my_configuration = new ep_configuration('module_' . $this->module_nom );
			$this->position = $this->my_configuration->position->texte_enfants();
			$this->position_ordre = $this->my_configuration->position_ordre->texte_enfants();
			if($this->my_configuration->conteneur->html_enfants_statut()):
				$this->conteneur =  $this->my_configuration->conteneur[0];
				$this->conteneur_statut=true;
			else:
				$this->conteneur_statut=false;
			endif;
			if($this->my_configuration->conteneur_contenu->html_enfants_statut()):
				$this->conteneur_contenu =  $this->my_configuration->conteneur_contenu[0];
				$this->conteneur_contenu_statut=true;
			else:
				$this->conteneur_contenu_statut=false;
			endif;
			$this->module_liste_nom_initialiser($this->my_configuration->module_liste_noms);
			$this->module_liste_initialiser();
			$this->analyser_contenu_html();
			$this->session_module = $this->my_configuration->session_module->texte_enfants(0);
			$this->decorations_initialiser($this->my_configuration->decorations);
		}
		catch (ep_exception $e)
		{
			if(($e->exception_id == ep_xml::EXCEPTION_FICHIER_XML)):
				$this->config_par_defaut=true;
			else:
				throw($e);
			endif;
		}
	}
	
	public function analyser_contenu_html()
	{
		if($this->my_configuration->contenu->attribut_valeur('use_php')=='true'):
			if($this->my_configuration->propriete_statut('php_variable')):
				foreach ($this->my_configuration->php_variable as $php_variable):
						$variable_name = $php_variable->attribut_valeur('id');
						$variable_valeur = $php_variable[0];
						$$variable_name = $variable_valeur;
				endforeach;
			endif;
			$php_code = $this->my_configuration->contenu[0];
			if(eval('return true;')):
				$this->contenu_html = eval($php_code);
			else:
				$this->contenu_html = array();
			endif;
		else:
			$this->contenu_html= $this->my_configuration->contenu->propriete_liste_copie();
		endif;
	}
	
	public function module_liste_nom_initialiser($liste)
	{
		if($liste instanceof ep_html ):
			if($liste->html_enfants_statut()):
				foreach($liste as $valeur):
					$this->module_liste_noms[$valeur->attribut_valeur('order')]['id'] = $valeur->attribut_valeur('id');
					$this->module_liste_noms[$valeur->attribut_valeur('order')]['class'] = $valeur->attribut_valeur('class','ep_module');
				endforeach;
			endif;
		endif;
	}
	
	protected function module_liste_initialiser()
	{
		if(is_array($this->module_liste_noms)):
			foreach($this->module_liste_noms as $clef => $module_nom):
				if(class_exists($module_nom['class'])):
					$this->module_liste[$clef] = new $module_nom['class']($this->rubrique);
				else:
					$this->module_liste[$clef] = new ep_module($module_nom['id'],$this->rubrique);
				endif;
			endforeach;
		endif;
		
	}
	
	/**
	 * Enter description here...
	 *
	 * @param ep_html $liste
	 */
	
	public function decorations_initialiser($liste)
	{
		if($liste->elements_nombre() > 0):
			foreach ($liste as $valeur):
				/* @var $valeur ep_html  */
				
				if($valeur->elements_nombre()>0):
					$this->decorations[$valeur->attribut_valeur('position')][$valeur->attribut_valeur('ordre')] 
					= 
						array
						(
							$valeur->attribut_valeur('id') => $valeur->propriete_liste_copie()
						)
					;					
				else:
					$this->decorations[$valeur->attribut_valeur('position')][$valeur->attribut_valeur('ordre')] = $valeur->attribut_valeur('id');
				endif;				
			endforeach;
		endif;
	}
	
	public function decorations_statut($position)
	{
		return (count($this->decorations[$position])>0);
			 	
	}
	
	/**
	 * Enter description here...
	 *
	 * @param ep_html $valeur
	 */
	protected function decoration_contenu_niveau2($valeur)
	{
		if($valeur instanceof ep_html):
			if($valeur->html_enfants_statut()):
				$contenu = array();
				foreach($valeur as $enfant):
					$contenu[] = $this->decoration_contenu_niveau2($enfant);
				endforeach;
				return  
					ep_html::html_tag
					(
						'div',
						$contenu,
						array('class' => 'decoration_' . $valeur->attribut_valeur('id') , 'id'=> 'module_'.$this->module_nom . '_' . $valeur->attribut_valeur('id'))					
					)
				;
				/**/
			else:
				return  
					ep_html::html_tag
					(
						'div',
						array(),
						array('class' => 'decoration_' . $valeur->attribut_valeur('id') , 'id'=> 'module_'.$this->module_nom . '_' . $valeur->attribut_valeur('id'))					
					)
				;
				
			endif;
		endif;
	}
	
	protected function decoration_contenu($valeur)
	{
		if(is_array($valeur)):
			$id = key($valeur);
			$elements = $valeur[$id];
			$contenu = array();
			foreach($elements as $element):
				$contenu[] = $this->decoration_contenu_niveau2($element);
			endforeach;

			return 
				ep_html::html_tag
					(
						'div',
						$contenu,
						array('class' => 'decoration_' . $id , 'id'=> 'module_'.$this->module_nom . '_' . $id)					
					)
				;
			/**/
		else:
			return  
					ep_html::html_tag
					(
						'div',
						array(),
						array('class' => 'decoration_' . $valeur , 'id'=> 'module_'.$this->module_nom . '_' . $valeur)					
					)
				;
		endif;
					
	}
	
	public function decorations_contenu($position)
	{
		$contenu = array();
		if($this->decorations_statut($position)):
			foreach ($this->decorations[$position] as $valeur):
				$contenu[] = $this->decoration_contenu($valeur);
			endforeach;
		endif;
		return $contenu;
	}
	
	
	protected function sous_module_contenu()
	{
		
		$contenu = array();
		if($this->sous_module_statut()):
			foreach($this->module_liste as &$module):
				$contenu = array_merge($contenu , $module->contenu());
			endforeach;
		endif;
		return $contenu;
		
	}
	
	protected function sous_module_statut()
	{
		return (count($this->module_liste)>0);
	}
	
	protected function ressources_variables()
	{
		$categorie = $this->my_ressource->categorie;
		foreach($this->my_ressource as $clef => $valeur):
			ep_variables::variable_ajouter(ep_variables::ressource_variable_id($categorie, $clef), $valeur);
		endforeach;
	}
	
	public function contenu()
	{
		$contenu = array();
		$contenu[] = $this->debug_commentaire(__FUNCTION__);
		
		$contenu = array_merge($contenu,$this->decorations_contenu(ep_module::POSITION_DECORATION_AVANT_MODULE));
		
		if($this->sous_module_statut()):
			if($this->conteneur_contenu_statut):
				$this->conteneur_contenu->propriete_liste_initialiser($this->sous_module_contenu());
				$contenu[] = $this->conteneur_contenu;
			else:
				$contenu[] = ep_html::html_tag
				(
						'div',
						$this->sous_module_contenu(),
						array('class' => 'module' , 'id'=> $this->module_nom)
				);
			endif;
		else:
			if($this->conteneur_contenu_statut):
				$this->conteneur_contenu->propriete_liste_initialiser($this->contenu_html);
				$contenu[] = $this->conteneur_contenu;
			else:
				$contenu[] = ep_html::html_tag
				(
						'div',
						$this->contenu_html,
						array('class' => 'module' , 'id'=> $this->module_nom)
				);				
			endif;
		endif;
		
		$contenu = array_merge($contenu,$this->decorations_contenu(ep_module::POSITION_DECORATION_APRES_MODULE));
		
		$contenu[] = $this->debug_commentaire(__FUNCTION__);
		
		if($this->conteneur_statut):
			$this->conteneur->propriete_liste_initialiser($contenu);
			return array
			(	
				$this->debug_commentaire("conteneur"),
				$this->conteneur,
				$this->debug_commentaire("conteneur")
			);
		else:
			return $contenu;
		endif;
		
	}
	
}
?>