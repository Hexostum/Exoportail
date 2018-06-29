<?php
class old_ep_membre extends ep_enregistrement
{
	function __construct($valeur,$nom='membre_id')
	{
		parent::__construct
			(
				array
				(
					'table'=> FP_TABLE_MEMBRES,
					'valeur_id'=> $valeur,
					'champ_id'=> $nom
				)
			)
		;
	}
	public function membre_existe()
	{
		return $this->statut();
	}
}
?>