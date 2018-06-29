<?php
/**

Cette classe regroupe des fonctions utile de texte et les variables texte sous 
forme {var_nom}. Elle regroupe �galement des fonctions statiques utile � la classe

Pour tout signalement de bogue merci de contacter l'auteur a' finalportail@exostum.net

/**/
class ep_texte extends ep_objet
{
	const quote='"';
	const apostrophe = '\'';
	const joker_start = '{';
	const joker_end = '}';
	const tab = "\t";
	const ligne = "\r\n";

	private $texte = '';
	private $texte_var = array();

	public function __construct($params=array())
	{
		parent::__construct();
		
		if(is_array($params)):
			$_params = new ep_params($params);
			$this->texte = $_params->texte;
			$this->texte_var = $_params->texte_var;
		else:
			$this->texte = $params;
		endif;
	}
	
	public function &__call($name, $arguments) 
	{
		switch (count($arguments)):
			case 0:
				$this->texte = $name($this->texte);
			break;
			case 1:
				$this->texte = $name($this->texte,$arguments[0]);
			break;
			case 2:
				$this->texte = $name($this->texte,$arguments[0],$arguments[1]);
			break;
			case 3:
				$this->texte = $name($this->texte,$arguments[0],$arguments[1],$arguments[2]);
			break;
		endswitch;		
		return $this;	
	}
	
	public function __tostring()
	{
		if
			(
				count($this->texte_var) > 0
			)
		:
			$joker_clef = array();
			$joker_valeur = array();
			foreach($this->texte_var as $clef => $valeur):
				$joker_clef[] =ep_texte::joker_faire($clef);
				$joker_valeur[] = $valeur; 
			endforeach;
			$this->replace($joker_clef,$joker_valeur);
			/**
				permet d'afficher { et } si mis en {{} et {}}
				
			/**/
			$this->replace(ep_texte::joker_faire('}'),'}');
			$this->replace(ep_texte::joker_faire('{'),'{');
		endif;
		return (string)$this->texte;
	}
	
	public function trouver($texte)
	{
		if(is_array($texte)):
			$retour_tableau = array();
			foreach ($texte as $valeur):
				if( strstr ($this->texte,$valeur) ):
					$retour_tableau[$valeur]=true;
				else:
					$retour_tableau[$valeur]=false;
				endif;
			endforeach;
			return $retour_tableau;
		elseif(is_string($texte)):
			if( strstr ($this->texte,$texte) ):
				return true;
			endif;
		endif;
		
	}

	/**
		Cette fonction permet d'ajouter une variable texte. Si on utilise sous la
		forme texte_var( 'nom_var', array('clef'=>'valeur') La variable nom_var 
		sera remplac� par clef="valeur" (comme une propri�t� html).

		Comme la fonction renvoie une r�f�rence sur le pointeur this, le chainage 
		de fonction est possible.
		
		exemple : 
		$texte = new ep_texte();
		$texte
		->texte_ajouter("<a {lien_href}>{lien_texte}</a>")
		->texte_var('lien_href',array('href'=>'http://www.exostum.net'))
		->texte_var('lien_texte','Site du cr�ateur de final Portail');

		$texte contiendra : 
		
		"<a href="http://www.exostum.net">Site du cr�ateur de final Portail</a>"

	/**/

	public function &texte_var($id,$valeur)
	{
		if
			(
				is_array($valeur)
			)
		:
			$texte = '';
			foreach($valeur as $clef => $valeur):
				$texte .= $clef . '=' . ep_texte::double_quote($valeur);
			endforeach;
			$this->texte_var[$id]=$texte;
			return $this;
		else:
			$this->texte_var[$id]=$valeur;
			return $this;
		endif;
	}
	
	/**
		Cette fonction permet d'ajouter du texte au contenu d�j� existant. Comme 
		la fonction renvoie une r�f�rence sur le pointeur this, le chainage de 
		fonction est possible.
		
		Exemple : 
		$texte = new ep_texte();
		$texte
		->texte_ajouter(' truc ')
		->texte_ajouter(' muche ');
		
		la valeur du texte sera donc " truc muche "

		Note : vous pouvez passer directement par la propri�t� texte mais du coup
		le chainage des fonctions n'est plus possible (voir exemple de texte_var)
		$texte = new ep_texte();
		$texte->texte .= ' truc ' . ' muche ';
		
	/**/

	public function &texte_ajouter($texte)
	{
		$this->texte .= $texte;
		return $this;
	}

	/** 
		une fonction de remplacement. Une fois de plus le renvoie du pointeur this 
		en r�f�rence permet le chainage de fonction
		
		$texte = new ep_texte(" a b c d e f g h i j ");
		$texte
		->replace("a","1")
		->replace("b","2")
		->replace("c","3")
		->replace("d","4")
		->replace("e","5")
		->replace("f","6");

		$texte contiendra " 1 2 3 4 5 6 g h i j ";
	/**/

	public function &replace($texte_chercher,$texte_remplacer)
	{
		$this->texte = str_replace($texte_chercher,$texte_remplacer,$this->texte);
		return $this;
	}
	
	public function &encadrer($carac)
	{
		$this->texte = ep_texte::s_encadrer($this->texte, $carac);
		return $this;
	}
	
	public function &espace_unique()
	{
		$this->texte = ep_texte::s_espace_unique($this->texte);
		return $this;
	}
	
	public function &tabulation($tabulation_niveau)
	{
		$this->texte = ep_texte::s_tabulation($tabulation_niveau) . $this->texte;
		return $this;
	}
	
	public static function s_espace_unique($texte)
	{
		$texte = preg_replace('/( {2,})/',' ',$texte);
		return $texte;
	}
	
	public static function s_encadrer($texte,$carac)
	{
		return $carac . $texte . $carac;
	}
	
	public static function s_tabulation ($tabulation_niveau)
	{
		return str_repeat(ep_texte::tab, $tabulation_niveau);
	}
	/**
		Cette fonction statique cr�er le joker pour le remplacement d'une texte_var
		exemple : si la texte_var a pour nom "lien_href" la fonction la transformera en
		{lien_href}
	/**/
	public static function joker_faire($texte)
	{
		return self::joker_start . $texte . self::joker_end;
	}
	/**
		cette fonction statique encadre une chaine d'un double quote, deux modes
		sont disponibles : un mode qui rajoute � coup sur, et un autre qui rajoute
		sauf si les double quotes sont d�j� pr�sent 
	/**/

	public static function double_quote($texte,$ignorer_doublon=false)
	{

		if
			(
				$ignorer_doublon
			)
		:
			return self::quote . $texte . self::quote;
		else:
			if
				(
					strlen($texte) > 1
				)
			:
				if
					(
						self::quote == $texte[0]
					)
				:
					$quote_debut = '';
				else:
					$quote_debut = self::quote;
				endif;

				if
					(
						self::quote == $texte[strlen($texte)-1]
					)
				:
					$quote_fin = '';
				else:
					$quote_fin = self::quote;
				endif;
				return $quote_debut . $texte . $quote_fin;
			else:
				return self::quote . $texte . self::quote;
			endif;
		endif;
		
	}
}
?>