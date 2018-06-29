<?php
class ep_objet
{
	/**
	 * Numéro de l'identifiant unique
	 * @var int
	 */	
	protected static $objet_uid=0;
	
	/**
	 * Identifiant unique de l'objet
	 * @var string
	 */
	protected $objet_id;
	
	/**
	 * Nom de classe de l'objet 
	 * @var string
	 */
	protected $objet_nom;
	
	/**
	 * Liste des actions tracé pour le déboguage 
	 * @var array
	 */
	protected $debug_liste = array();
	
	/**
	 * Nombre d'actions tracées
	 * @var int
	 */
	protected $debug_liste_nombre = 0;
	
	/**
	 * Dernière erreur survenue
	 * @var string
	 */
	protected $debug_liste_erreur = '' ;
	
	/**
	 * Statut de l'activation du déboguage
	 * @var bool
	 */
	protected $debug_statut = true;
	
	protected $debug_trace_statut = true;

	/**
	 * Statut de la classe
	 * @var bool
	 */
	protected $statut=true;

	public function __construct ($parametres_liste = array())
	{
		$this->trace(__FUNCTION__,__LINE__,"[local.parametres] <= [ep_tableau([".print_r($parametres_liste,true)."])]");
		$parametres = new ep_tableau($parametres_liste);
		if(EP_DEBUG):
			$this->trace(__FUNCTION__,__LINE__,'[local.parametres] ['.print_r($parametres,true).']');
		endif;
		$this->trace(__FUNCTION__,__LINE__,'objet_id_initialiser()');
		$this->objet_id_initialiser($parametres->propriete_lire('objet_id',NULL));
		$this->trace(__FUNCTION__,__LINE__,'debug_initialiser()');
		$this->debug_initialiser($parametres->propriete_lire('debug_statut',EP_DEBUG));
		$this->trace(__FUNCTION__,__LINE__,'debug_trace_initialiser()');
		$this->debug_trace_initialiser($parametres->propriete_lire('debug_trace_statut',EP_DEBUG_TRACE));
	}

	public function __toString()
	{
		return (string)$this->debug();
	}

	public function get_parent()
	{
		return get_parent_class($this);
	}

	public function objet_id_initialiser ($id=NULL)
	{
		
		$this->objet_nom = get_class($this);
		$this->trace(__FUNCTION__,__LINE__,"[this.objet_nom] <= [$this->objet_nom]");
		if(is_null($id)):
			$this->trace(__FUNCTION__,__LINE__,'is_null(id) [OK]');
			$this->objet_id = $this->objet_nom . '_'  . ++self::$objet_uid;
		else:
			$this->trace(__FUNCTION__,__LINE__,'is_null(id) [KO]');
			$this->objet_id = $this->objet_nom . '_'  . $id;
		endif;
		$this->trace(__FUNCTION__,__LINE__,"[this.objet_id] <= [$this->objet_id]");
	}

	public function statut($statut_nom=NULL)
	{
		switch ($statut_nom):
			case 'debug':
				return $this->debug_statut;
			break;
		default:
			return $this->statut;
		endswitch;
	}

	public function debug_initialiser($debug_statut)
	{
		$this->trace(__FUNCTION__,__LINE__, "[this.debug_statut] [<=] [$debug_statut]");
		$this->debug_statut = $debug_statut;
	}
	
	public function debug_trace_initialiser($debug_trace_statut)
	{
		$this->trace(__FUNCTION__,__LINE__, "[this.debug_trace_statut] [<=] [$debug_trace_statut]");
		$this->debug_trace_statut = $debug_trace_statut;
	}

	protected function debug_commentaire($fonction_nom,$mode='HTML')
	{
		switch($mode):
			case 'texte':
				return 'ExoPortail (EXOSTUM) : ' . $this->objet_nom . '('.$this->objet_id.')::' . $fonction_nom .'()';
			break;
		case 'html':
		default:
			return ep_html::html_tag('!--',$this->debug_commentaire($fonction_nom,'texte'));
		endswitch;
	}

	protected function debug_proprietes()
	{
		$lignes = array();
		$liste_proprietes = get_object_vars($this);
		foreach($liste_proprietes as $propriete_nom => $propriete_valeur):
			$lignes[] = $this->debug_propriete($propriete_nom,$propriete_valeur);
		endforeach;
		return $lignes;
	}

	protected function debug_propriete($propriete_nom,$propriete_valeur)
	{
		if(is_array($propriete_valeur)):
			$tableau_titre = array();
			
			$tableau_titre[] =
				ep_html::html_tag
				(
					'tr',
					ep_html::html_tag
					(
						'th',
						$propriete_nom,
						array ('colspan' => 2 )
					)
				)
			;
			
			$tableau_titre[] =
				ep_html::html_tag
				(
					'tr',
					array
					(
						ep_html::html_tag('th','Propriété') ,
						ep_html::html_tag('th','Valeur')
					)
				)
			;
			
			
			$tableau_lignes = array();
			foreach($propriete_valeur as $clef => $valeur):
				$tableau_lignes[] = $this->debug_propriete($clef,$valeur);
			endforeach;
			
			$_tableau_lignes = array_merge($tableau_titre,$tableau_lignes);
			
			$tableau = 
				ep_html::html_tag
				(
					'table',
					$_tableau_lignes,
					array
					(
						'border' => 1,
						'width' => '100%'
					)		
				)
			;
			
			return
				ep_html::html_tag
				(
					'tr',
					array
					(
						ep_html::html_tag('td',(string)$propriete_nom) ,
						ep_html::html_tag('td',$tableau )
					)
				)
			;
		else:
			return 
				ep_html::html_tag
				(
					'tr',
					array
					(
						ep_html::html_tag('td',(string)$propriete_nom),
						ep_html::html_tag('td',
							ep_html::html_tag
							(
								'textarea',
							    array($propriete_valeur),
							    array('cols' => '100%')
							)
						)
					)
				)
			;
		endif;
	}

	public function debug()
	{
		if($this->debug_statut):
			$texte = '';
			
			$tableau_titre = array();
			
			$tableau_titre[] =
				ep_html::html_tag
				(
					'tr',
					ep_html::html_tag
					(
						'th',
						get_class($this),
						array
						(
							'colspan'=>2
						)		
					)
				)
			;
			
			$tableau_titre[] =
				ep_html::html_tag
				(
					'tr',
					array
					(
						ep_html::html_tag('th','Propriété') ,
						ep_html::html_tag('th','Valeur')
					)
				)
			;
			
			$tableau_lignes = $this->debug_proprietes();
			
			$tableau_lignes = array_merge($tableau_titre,$tableau_lignes);
			
			$table =
				ep_html::html_tag
				(
					'table',
					$tableau_lignes,
					array
					(
						'border' => 1,
						'width' => '100%'
					)		
				)
			;
			
			$div_debug_infos =
				ep_html::html_tag
				(
					'div',
					$table,
					array
					(
						'class' => 'debug_infos'
					)		
				)
			;
			
			return $div_debug_infos;
		endif;
	}

	protected function trace ($fonction , $ligne , $message , $erreur='')
	{
		
		if($this->debug_trace_statut):
			$this->debug_liste[$this->debug_liste_nombre]['fonction'] = $fonction;
			$this->debug_liste[$this->debug_liste_nombre]['ligne'] = $ligne;
			$this->debug_liste[$this->debug_liste_nombre]['message'] = $message;
			$this->debug_liste[$this->debug_liste_nombre]['erreur'] = $erreur;
			if(!empty($erreur)):
				$this->debug_liste_erreur=$erreur;
			endif;
			$this->debug_liste_nombre++;
		endif;
	}
}

class ep_exception extends Exception 
{
	protected $classe_nom;
	protected $classe_fonction;
	protected $exception_id;
	
	public function exception_init($classe_nom,$classe_fonction,$exception_id)
	{
		$this->classe_nom = $classe_nom;
		$this->classe_fonction = $classe_fonction;
		$this->exception_id = $exception_id;
	}
	
	public function debug_message()
	{
		if(EP_DEBUG):
			return parent::getMessage();
		else:
			return '<!-- [EXOSTUM] [EXOPORTAIL] [KO] -->';
		endif;
	}

	public function &__get($clef)
	{
		if(isset($this->$clef)):
			return $this->$clef;
		else:
			return NULL;
		endif;
	}
}
/**/
?>