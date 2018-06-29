<?php
$GLOBALS['var_server_patrons'] = array();
$GLOBALS['var_server_remplacements'] = array();

class ep_variable
{
	private $nom;
	private $valeur;
	private $analyse;
	
	public function __construct($variable_nom,$variable_valeur,$variable_analyse=false)
	{
		
	}
}

class ep_variables
{
	private $variables = array();
	/**
	public function variable_ajouter($variable_nom,$variable_valeur,$variable_analyse)
	{
		$this->variables = new ep_variable($variable_nom,$variable_valeur,$variable_analyse);
	}
	/**/
	
	
	static public function analyse($texte)
	{
		return str_replace($GLOBALS['var_server_patrons'],$GLOBALS['var_server_remplacements'],$texte);
	}
	
	static public function ressource_variable_id($categorie,$clef)
	{
		if(is_array($categorie)):
			return '{RESSOURCES.' . strtoupper(implode('.',($categorie))) . '.' . strtoupper($clef) . '}';
		else:
			return '{RESSOURCES.' . strtoupper($categorie) . '.' . strtoupper($clef) . '}';
		endif;
	}
	
	static public function variable_ajouter($clef,$valeur,$analyse_statut=false)
	{
		$GLOBALS['var_server_patrons'][$clef] = $clef;
		if($analyse_statut):
			$GLOBALS['var_server_remplacements'][$clef] = ep_variables::analyse($valeur);
		else:
			$GLOBALS['var_server_remplacements'][$clef] = $valeur;
		endif;
	}
	static public function variables_ajouter(Array $liste)
	{
		
		foreach($liste as $clef => $valeur):
			ep_variables::variable_ajouter($clef,$valeur);
		endforeach;
		
	}
	static public function variable_valeur($id)
	{
		return ep_tableau::s_propriete_lire($GLOBALS['var_server_remplacements'], $id,strtolower($id));
	}
	static public function ajouter_variable($clef,$valeur)
	{
		exoportail::deprecated('ep_variables::ajouter_variable','ep_variables::variable_ajouter',__FILE__,__LINE__);
		ep_variables::variable_ajouter($clef,$valeur);
	}
	
	static public function ajouter_variables(Array $liste)
	{
		exoportail::deprecated('ep_variables::ajouter_variables', "ep_variables_variables_ajouter");		
	}
}
?>