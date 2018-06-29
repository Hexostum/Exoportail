<?php

$GLOBALS['html_tag_orphelin_statut'] = array
(
	'a' => false ,
	'html' => false,
	'body' => false,
	'head' => false,
	'div' => false ,
	'span' => false , 
	'p' => false,
	'h1' => false,
	'h2' => false,
	'h3' => false,
	'h4' => false,
	'h5' => false,
	'h6' => false,
	'table' => false,
	'tr' => false,
	'td' => false,
	'th' => false,
	'ul' => false,
	'li' => false,
	'title'=>false,
	
	'!--' => false,
	
	'link' => true,
	'base' => true,
	'meta' => true,
	'br' => true
	
);

$GLOBALS['html_tag_lined_statut'] = array
(
	'a' => false
);


class ep_html extends ep_tableau
{
	const doctype_html4_strict = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
	protected $tag_nom;
	protected $html_attributs = array();
	
	protected $is_xhtml = false;
	protected $is_lined = true;
	protected $is_orphelin = false;
	protected $tag_only=false;
	protected $tag_type;
	public $tabulation_niveau=0;
	protected static $html_uid = 0;
	
	public function __construct($params=array())
	{
		$this->objet_id_initialiser(++self::$html_uid);
		$this->debug_initialiser(EP_DEBUG);
		
		$_params = new ep_params($params);

		$_params->propriete_alias_initialiser('html_enfants','enfants');
		$_params->propriete_alias_initialiser('html_attributs','attributs');
		
		$this->tag_nom = $_params->propriete_lire('tag_nom','html');
		$this->tag_only = $_params->propriete_lire('tag_only',false);
		$this->tag_type = $_params->propriete_lire('tag_type',NULL);
		$this->is_orphelin = $_params->propriete_lire('is_orphelin',false);
		$this->is_lined = $_params->propriete_lire('is_lined',true);
		$this->is_xhtml = $_params->propriete_lire('is_xhtml',false);
		$this->html_attributs = $_params->propriete_lire('html_attributs',array());
		
		$html_enfants = $_params->propriete_lire('html_enfants',array());
		
		$this->propriete_liste_initialiser($html_enfants);
		
		$this->html_enfants_positions_init();
		
		
	}
	public function __clone()
	{
		foreach($this->propriete_liste as $clef => $valeur):
			if(is_object($valeur)):
				$this->propriete_liste[$clef] = clone $valeur;
			else:
				$this->propriete_liste[$clef] = $valeur;
			endif;
		endforeach;
	}
	/**
	public function copie($copie_xtpl_id , $copie_id)
	{
		$copie = new ep_html
		(
			array
			(
				'tag_nom' => $this->tag_nom,
				'tag_only' => $this->tag_only,
				'tag_type' => $this->tag_type,
				'is_orphelin' => $this->is_orphelin,
				'is_lined' => $this->is_lined,
				'is_xhtml' => $this->is_xhtml,
				'html_attributs' => $this->html_attributs ,
				'html_enfants' => $this->propriete_liste
			)
		);
		return $copie;
	}
	/**/
	
	public function remplacer_var_attributs($var_clef,$var_valeur)
	{
		$html_attributs = array();
		foreach($this->html_attributs as $clef => $valeur):
			$html_attributs[$clef] = str_replace($var_clef,$var_valeur,$valeur);
		endforeach;
		return $html_attributs;
	}
	
	public function remplacer_var_enfants($var_clef,$var_valeur)
	{
		$propriete_liste = array();
		foreach($this->propriete_liste as $clef => $valeur):
			if($valeur instanceof ep_html):
				$valeur->remplacer_var_enfants($var_clef,$var_valeur);
				$propriete_liste[$clef] = clone $valeur;
			elseif(is_string($valeur)):
				$propriete_liste[$clef] = str_replace($var_clef,$var_valeur,$valeur);
			endif;
		endforeach;
		return $propriete_liste;
	}
	
	public function remplacer_var($var_clef,$var_valeur)
	{
		$liste = array();
		foreach($this->html_attributs as $clef => $valeur):
			$liste[$clef] = str_replace($var_clef,$var_valeur,$valeur);
		endforeach;
		$this->html_attributs = $liste;
		unset($valeur);
		unset($liste);
		
		
		$liste2 = array();
		foreach($this->propriete_liste as $clef => $valeur):
			if($valeur instanceof ep_html):
				$valeur->remplacer_var($var_clef,$var_valeur);
				$liste2[$clef] = clone $valeur;
			elseif(is_string($valeur)):
				$liste2[$clef] = str_replace($var_clef,$var_valeur,$valeur);
			endif;
		endforeach;
		$this->propriete_liste = $liste2;
		unset($liste2);
		unset($valeur);
	}
	public function remplacer_vars (array $liste)
	{
		foreach ($liste as $clef => $valeur):
			$this->remplacer_var($clef,$valeur);
		endforeach;
	}
	
	protected function html_enfants_positions_init()
	{
		if(is_array($this->propriete_liste)):
			foreach($this->propriete_liste as $clef => $valeur):
				if(($valeur instanceof ep_html)):
					if($valeur->attribut_statut('id')):
						$this->propriete_liste_position[$valeur->attribut_valeur('id')] = $clef;
					endif;
				endif;
			endforeach;
		endif;
	}
	public function tag_nom()
	{
		return $this->tag_nom;
	}
	/**
	public function offsetSet($offset, $value) 
	{
		$nom = 'html_' . $offset;		
        $this->$nom = $value;
    }
    public function offsetExists($offset) 
    {
    	if(ep_tableau::s_propriete_statut($this->html_enfants_position,$offset)):
    		return true;
    	elseif(ep_tableau::s_propriete_statut($this->html_enfants,$offset)):
    		return true;
    	else:
    		return false;
    	endif;
    }
    public function offsetUnset($offset) 
    {
		throw (new Exception('UNSET FORBIDDEN'));
    }
    /**/
    public function offsetGet($offset) 
    {
    	if(ep_tableau::s_propriete_statut($this->propriete_liste_position,$offset)):
    		return parent::offsetGet($offset);
    	else:
    		return new ep_html();
    	endif;
    }
	/**/
	public function attribut_statut($attribut_nom)
	{
		return ep_tableau::s_propriete_statut($this->html_attributs,$attribut_nom);
	}
	public function attribut_valeur($attribut_nom)
	{
		return ep_tableau::s_propriete_lire($this->html_attributs,$attribut_nom);
	}
	public function attributs()
	{
		return $this->html_attributs;
	}
	public function lined_statut()
	{
		return $this->is_lined;
	}
	public function &html_enfants($tag_nom)
	{
		$resultat = array();
		foreach($this->propriete_liste as &$valeur):
			if($valeur instanceof ep_html):
				if($valeur->tag_nom()==$tag_nom):
					if($valeur->attribut_statut('id')):
						$resultat[$valeur->attribut_valeur('id')] = &$valeur;
					else:
						$resultat[] = &$valeur;
					endif;
				endif;
			endif;
		endforeach;
		return $resultat;
	}
	public function html_enfants_statut()
	{
		return ($this->propriete_liste_nombre > 0);
	}
	private function texte_attributs($ksort_statut=false)
	{
		$texte = new ep_texte('');
		if( count($this->html_attributs) > 0 ):
			if($ksort_statut):
				ksort($this->html_attributs);
			endif;
			foreach ($this->html_attributs as $clef => $valeur):
				if(''==$valeur):
					if($this->is_xhtml):
						$valeur = $clef;
						$texte
							->texte_ajouter(' ')
							->texte_ajouter(strtolower($clef))
							->texte_ajouter('=')
							->texte_ajouter( ep_texte::s_encadrer($valeur, '"') )
						;
					else:
						$texte
							->texte_ajouter(' ')
							->texte_ajouter(strtolower($clef))
						;
					endif;
				else:
					$texte
						->texte_ajouter(' ')
						->texte_ajouter(strtolower($clef))
						->texte_ajouter('=')
						->texte_ajouter( ep_texte::s_encadrer($valeur, '"') )
					;
				endif;
			endforeach;
		endif;
		return (string)$texte;
	}
	public function texte_enfants($defaut=NULL)
	{
		$texte = new ep_texte('');
		if(is_array($this->propriete_liste)):
			$lined_statut = false;
			foreach($this->propriete_liste as &$valeur):
				if($valeur instanceof ep_html):
					$valeur->tabulation_niveau = $this->tabulation_niveau+1;
					if(!$lined_statut):
						$lined_statut = $valeur->lined_statut();
					endif;
				endif;
				$texte->texte_ajouter((string)$valeur);			
			endforeach;
			if($lined_statut):
				$texte->texte_ajouter(ep_texte::ligne . ep_texte::s_tabulation($this->tabulation_niveau));
			endif;
		else:
			$texte = $this->propriete_liste;
		endif;
		if($texte==''):
			if($this->tag_nom=='html'):
				return $defaut;
			else:
				return '';
			endif;
		else:
			return (string)$texte;
		endif;
	}
	public function __toString()
	{
		if(!$this->tag_only):
			$texte_attributs = $this->texte_attributs();
			$texte_enfants = $this->texte_enfants();
			if($this->is_xhtml):
				if($this->is_orphelin):
					return ep_texte::ligne . '<' . $this->tag_nom . $texte_attributs . ' />';
				else:
					if($this->tag_nom=='!--'):
						return ep_texte::ligne  . ep_texte::s_tabulation($this->tabulation_niveau) .  '<' . $this->tag_nom . $texte_enfants  . '-->';
					else:
						/**
						return ep_texte::ligne . '<' . $this->tag_nom . $texte_attributs . '>' . $texte_enfants . '</'.$this->tag_nom.'>'; 
						/**/
						if($this->is_lined):
						 	return ep_texte::ligne  . ep_texte::s_tabulation($this->tabulation_niveau) .  '<' . $this->tag_nom . $texte_attributs . '>' . $texte_enfants  . '</'.$this->tag_nom.'>';
						 else:
						 	return  '<' . $this->tag_nom . $texte_attributs . '>' . $texte_enfants  . '</'.$this->tag_nom.'>';
						 endif;
					endif;
				endif;
			else:
				if($this->is_orphelin):
					return ep_texte::ligne . ep_texte::s_tabulation($this->tabulation_niveau). '<' . $this->tag_nom . $texte_attributs . '>' ;
				else:
					if($this->tag_nom=='!--'):
						return ep_texte::ligne  . ep_texte::s_tabulation($this->tabulation_niveau) .  '<!-- ' . $texte_enfants  . ' -->';
					else:
						if($this->is_lined):
						 	return ep_texte::ligne  . ep_texte::s_tabulation($this->tabulation_niveau) .  '<' . $this->tag_nom . $texte_attributs . '>' . $texte_enfants  . '</'.$this->tag_nom.'>';
						 else:
						 	return  '<' . $this->tag_nom . $texte_attributs . '>' . $texte_enfants  . '</'.$this->tag_nom.'>';
						 endif;
					 endif;
				endif;
			endif;
		else:
			if($this->tag_type=='END'):
				return '</' . $this->tag_nom . '>';
			else:
				$texte_attributs = $this->texte_attributs();
				return '<' . $this->tag_nom . $texte_attributs .'>';
			endif;
		endif;
	}
	
	static function html_tag($tag_nom,$html_enfants=array(),$html_attributs=array())
	{
		$_params = new ep_params(array());
		if(is_string($html_enfants)):
			$_params->propriete_initialiser('html_enfants',array(new ep_texte($html_enfants)));
		elseif(is_array($html_enfants)):
			$_params->propriete_initialiser('html_enfants', $html_enfants);
		else:
		$_params->propriete_initialiser('html_enfants', array($html_enfants));
		endif;
		$_params->propriete_initialiser('tag_nom',$tag_nom);
		$_params->propriete_initialiser('html_attributs', $html_attributs);
		$_params->propriete_initialiser('is_orphelin',ep_tableau::s_propriete_lire($GLOBALS['html_tag_orphelin_statut'],$tag_nom,false));
		$_params->propriete_initialiser('is_lined',ep_tableau::s_propriete_lire($GLOBALS['html_tag_lined_statut'],$tag_nom,true));
		return new ep_html($_params);
	}
	
	static function html_tag_fragment($tag_nom,$fragment_type,$params)
	{
		if(is_string($params)):
			$_params = new ep_params(array());
			$_params->propriete_initialiser('html_enfants',new ep_texte($params));
		else:
			$_params = new ep_params($params);			
		endif;
		$_params->propriete_initialiser('tag_nom',$tag_nom);
		$_params->propriete_initialiser('tag_only',true);
		$_params->propriete_initialiser('tag_type',$fragment_type);
		$_params->propriete_initialiser('is_orphelin',ep_tableau::valeur($GLOBALS['html_tag_orphelin_statut'],$tag_nom,false));
		$_params->propriete_initialiser('is_lined',ep_tableau::valeur($GLOBALS['html_tag_lined_statut'],$tag_nom,true));
		return new ep_html($_params);
	}
}
?>