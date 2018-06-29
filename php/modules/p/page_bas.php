<?php
class ep_module_page_bas extends ep_module
{
	function __construct(&$my_serveur)
	{
		$this->module_nom='page_bas';
		parent::__construct($my_serveur);
	}
	function contenu()
	{
		$contenu = array();
		$contenu[] = parent::contenu();
		
/**
	
	<div class="partenaires">
		<h1 class="petit_titre_h">Partenaires :</h1>
		<ul class="menu_h">
			<li><a href="http://www.gagner-argent-internet.org" title="GSI : Gagner de l\'argent avec l\'Internet">GSI</a></li>
			<li><a href="http://www.envoyersms.biz/" target="_blank" title="Envoyer SMS">Envoyer SMS</a></li>
		</ul>
	</div>
**/
		// <div id="footer"></div>
		if($this->sous_module_statut()):
			$div = 
				ep_html::html_tag
				(
					'div',
					$this->sous_module_contenu(),
					array('class' => 'module' , 'id'=> $this->module_nom )
				)
			;
		endif;
		//<div id="footer_b"></div>
		if($this->module_decoration_statut()):
			$div_page_bas_decoration = 
				ep_html::html_tag
				(
					'div',
					array(),
					array('class' => 'module' , 'id' => 'page_bas' , 'id' => 'page_bas_decoration')
				)
			;
		endif;
		$contenu[] = $div_page_bas;
		$contenu[] = $div_page_bas_decoration;
		$contenu[] = parent::contenu();
		return $contenu ;
	}
}
?>