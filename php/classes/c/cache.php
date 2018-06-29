<?php
class ep_cache
{
	static $cache=array();
	
	public static function propriete_initialiser($variable_cache_nom,$variable_nom,$variable_valeur)
	{
		ep_cache::$cache[$variable_cache_nom][$variable_nom] = $variable_valeur;
	}
	
	public static function propriete_statut($variable_cache_nom,$variable_nom)
	{
		if(isset(ep_cache::$cache[$variable_cache_nom][$variable_nom])):
			return true;
		else:
			return false;
		endif;
	}
	
	public static function propriete_lire($variable_cache_nom,$variable_nom,$valeur_defaut=NULL)
	{
		if(ep_cache::propriete_statut($variable_cache_nom,$variable_nom)):
			return ep_cache::$cache[$variable_cache_nom][$variable_nom];
		else:
			return $valeur_defaut;
		endif;
	}
	public static function propriete_ecrire($variable_cache_nom,$variable_nom,$variable_valeur)
	{
		if(ep_cache::propriete_statut($variable_cache_nom,$variable_nom)):
			ep_cache::$cache[$variable_cache_nom][$variable_nom] = $variable_valeur;
		else:
			throw("ep_cache::propriete_ecrire([$variable_cache_nom],[$variable_nom],[$variable_valeur]) [KO]");
		endif;
	}
	
	public static function membre_pseudo($membre_id)
	{
		return ep_cache::cache_valeur('membre_pseudo',$membre_id);		
	}
	
	public static function code_designation($code_id)
	{
		return ep_cache::cache_valeur('code_designation',$code_id);
	}	
}
?>