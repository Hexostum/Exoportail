<?php
class ep_enregistrement_sql extends ep_enregistrement
{
	function __construct($array)
	{
		$params = new ep_params($array);
		$this->table_nom=$params->table_nom;
		$this->id_colonne_nom =$params->id_colonne_nom;
		$this->propriete_liste_initialiser($params->propriete_liste);
		if(count($this->propriete_liste)>0):
			$this->statut = true;
			$this->id_colonne_valeur = $this->propriete_liste[$this->id_colonne_nom];
		else:
			$this->statut = false;
		endif;
	}
}
?>