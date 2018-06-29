<?php

class ep_rubrique_erreur_404 extends ep_rubrique 
{
	
	public function __construct(exoportail &$my_serveur)
	{
		$this->rubrique_nom = 'erreur_404';
		parent::__construct($my_serveur);
		
	}
	
	public function contenu()
	{
		$ressource_id = 'rubrique.' .  $this->rubrique_nom ;
		
		$contenu = array();
		$contenu[] = parent::contenu();
		
		$contenu = array_merge($contenu,$this->modules_contenu(ep_module::POSITION_AVANT_RUBRIQUE));
		
		$contenu[] = ep_html::html_tag
		(
			'div',
			$this->contenu,
			array('class' => 'rubrique' , 'id' =>  $this->rubrique_nom)
		);
		
		if(EP_DEBUG):
			$contenu[] = $this->debug_contenu;
		endif;
		
		$contenu = array_merge($contenu,$this->modules_contenu(ep_module::POSITION_APRES_RUBRIQUE));
		
		$contenu[] = parent::contenu();
		if($this->conteneur!=''):
			$conteneur = 
				ep_html::html_tag
				(
					'div',
					$contenu,
					array('class'=>'conteneur','id'=> $this->conteneur)
				)
			;
			return array($conteneur);
		endif;
		return $contenu;
	}
}
?>