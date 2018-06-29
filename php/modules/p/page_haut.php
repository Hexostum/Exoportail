<?php
class ep_module_page_haut extends ep_module
{
	function __construct(exoportail &$my_serveur)
	{
		$this->module_nom='page_haut';
		parent::__construct($my_serveur);
	}
	function contenu()
	{
		$contenu = array();
		$contenu[] = parent::contenu();
		$contenu[] = ep_html::html_tag
		(
			'div',
			array(),
			array('class' => 'module' , 'id'=> $this->module_nom )
		);
		$contenu[] = parent::contenu();
		return $contenu;
	}
}
?>