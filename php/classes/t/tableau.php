<?php
class ep_tableau extends ep_objet implements iterator, ArrayAccess
{
	const PROPRIETE_ERREUR_IGNORE = 1;
	const PROPRIETE_ERREUR_EXCEPTION = 2;
	
	protected static $tableau_uid=0;

	protected $propriete_liste = array();
	protected $propriete_liste_clef = array();
	protected $propriete_liste_position = array();
	protected $propriete_liste_nombre=0;
	protected $propriete_liste_iterateur=0;
	protected $propriete_liste_alias = array();
	protected $propriete_erreur_mode = ep_tableau::PROPRIETE_ERREUR_IGNORE;
	
	public function propriete_erreur_mode($mode)
	{
		$this->propriete_erreur_mode = $mode;
	}

	public function __construct(array $array = array())
	{
		$this->debug_initialiser(EP_DEBUG);
		$this->objet_id_initialiser(++self::$tableau_uid);
		$this->propriete_liste_initialiser($array);
	}
	
	public function elements_nombre()
	{
		return $this->propriete_liste_nombre;
	}

	public function &__get($propriete_nom)
	{
		return $this->propriete_lire($propriete_nom);
	}

	public function __set($propriete_nom,$propriete_valeur)
	{
		$this->propriete_ecrire($propriete_nom,$propriete_valeur);
	}

	public function current()
	{
		return $this->propriete_liste[$this->propriete_liste_clef[$this->propriete_liste_iterateur]];
	}

	public function key()
	{
		return $this->propriete_liste_clef[$this->propriete_liste_iterateur];
	}

	public function next()
	{
		$this->propriete_liste_iterateur = $this->propriete_liste_iterateur + 1;
	}

	public function rewind()
	{
		$this->propriete_liste_iterateur=0;
	}

	public function valid ()
	{
		if($this->propriete_liste_iterateur < $this->propriete_liste_nombre ):
			return true;
		else:
			return false;
		endif;
	}
	
	public function offsetSet($offset, $value)
	{
		if($this->propriete_statut($offset)):
			$this->propriete_liste[$offset]=$value;
		endif;
	}
	public function offsetExists($offset)
	{
		return $this->propriete_statut($offset);
	}
	public function offsetUnset($offset)
	{
		if($this->propriete_statut($offset)):
			unset($this->propriete_liste[$offset]);
		endif;
	}
	public function offsetGet($offset)
	{
		return $this->propriete_liste[$this->propriete_liste_position[$offset]];
	}

	public function propriete_liste_initialiser(array $propriete_liste)
	{

		foreach($propriete_liste as $clef => $valeur):
			$this->propriete_initialiser($clef,$valeur);
		endforeach;
	}

	public function propriete_initialiser($propriete_nom,$propriete_valeur)
	{
		$position = count($this->propriete_liste);
		$this->propriete_liste[$propriete_nom] = $propriete_valeur;
		$this->propriete_liste_clef[$position] = $propriete_nom;
		$this->propriete_liste_position[$propriete_nom] = $position;
		$this->propriete_liste_nombre = count($this->propriete_liste);
	}
	
	public function propriete_alias_liste_initialiser(Array $alias_liste)
	{
		foreach ($alias_liste as $alias_clef => $alias_valeur):
			$this->propriete_alias_initialiser($alias_clef,$alias_valeur);
		endforeach;
	}

	public function propriete_alias_initialiser($alias_clef,$alias_valeur)
	{
		$this->propriete_liste_alias[$alias_clef] = $alias_valeur;
	}

	public function propriete_statut($propriete_nom)
	{
		if(array_key_exists($propriete_nom, $this->propriete_liste_position)):
			return true;
		else:
			return false;
		endif;
	}
		
	public function &propriete_lire($propriete_nom,$propriete_valeur_defaut=null)
	{

		if($this->propriete_statut($propriete_nom)):
			return $this->propriete_liste[$propriete_nom];
		else:
			if(isset($this->propriete_liste_alias[$propriete_nom])):
				switch ($this->propriete_erreur_mode):
					case ep_tableau::PROPRIETE_ERREUR_EXCEPTION:
						throw (new exception ( "{$this->objet_nom}::propriete_lire([$propriete_nom]) [ALIAS] [".$this->propriete_liste_alias[$propriete_nom]."] "));
					break;
					default:
						return $this->propriete_lire($this->propriete_liste_alias[$propriete_nom],$propriete_valeur_defaut) ;
					break;
				endswitch;
			else:
				return $propriete_valeur_defaut;
			endif;
		endif;
	}

	public function propriete_ecrire($propriete_nom,$propriete_valeur)
	{
		if($this->propriete_statut($propriete_nom)):
			$this->propriete_liste[$propriete_nom] = $propriete_valeur;
		else:
			if(isset($this->propriete_liste_alias[$propriete_nom])):
				$this->propriete_ecrire($this->propriete_liste_alias[$propriete_nom],$propriete_valeur);
			else:
				throw (new Exception("{$this->objet_nom}::propriete_ecrire([$propriete_nom],[$propriete_valeur]) propriete_statut([$propriete_nom]) [KO]"));
			endif;
		endif;
			
	}

	public function propriete_liste_copie()
	{
		return $this->propriete_liste ;
	}

	public function &propriete_liste_reference()
	{
		return $this->propriete_liste;
	}
	
	public static function s_propriete_statut(array $tableau, $clef)
	{
		if(array_key_exists($clef,$tableau)):
			return true;
		else:
			return false;
		endif;
	}
	
	public static function &s_propriete_lire(array $tableau,$clef,$valeur_defaut=NULL)
	{
		if(array_key_exists($clef,$tableau)):
			return $tableau[$clef];
		else:
			return $valeur_defaut;
		endif;
	}
	
}
?>