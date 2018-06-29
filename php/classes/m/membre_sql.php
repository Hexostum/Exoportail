<?php
class ep_membre_sql extends ep_enregistrement_sql
{
	
	function __construct(array $sql)
	{
		ep_deprecated('ep_membre_sql::construct','ep_membre(mode=>array)');
		parent::__construct
		(
			array
			(	
				'champs' => $sql,
				'table'=>'codesgratis_membres',
				'champ_id' => 'membre_id'
			)
		);
	}
	public function membre_existe()
	{
		return $this->statut();
	}
}
?>