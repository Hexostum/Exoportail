<?php
class ep_module_modele extends ep_module
{
	function __construct(exoportail &$my_serveur)
	{
		$this->position;
		$this->position_ordre;
		$this->module_nom = 'modele';
		parent::__construct($my_serveur);
	}
	function contenu()
	{
		$contenu = array();
		$contenu[] = parent::contenu();
		$contenu[] = ep_html::html_tag
		(
			'div',
			array
			(
			),
			array('class' => 'module' , 'id'=> $this->module_nom)
		);
		$contenu[] = parent::contenu();
		return $contenu;
	}
}
?>