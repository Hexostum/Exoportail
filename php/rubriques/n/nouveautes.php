<?php
class ep_rubrique_nouveautes extends ep_rubrique 
{
	
	public function __construct(exoportail &$my_serveur)
	{
		$this->rubrique_nom = 'nouveautes';
		parent::__construct($my_serveur);
	}
	
	public function contenu_analyse()
	{
		if(EP_BDD_STATUT):
			if($this->my_serveur->variable_serveur_statut('get','nouveaute_id')):
				$nouveaute_id_originale = $this->my_serveur->variable_serveur_lire('get','nouveaute_id');
				$nouveaute_id = intval($nouveaute_id_originale);
				if(strlen($nouveaute_id)==strlen($nouveaute_id_originale)):
					$maj = new ep_maj($nouveaute_id);
					if($maj->statut()):
						$maj->contenu_html($this->my_configuration->contenu_nouveaute->propriete_liste_copie());
						return $maj->contenu();
					else:
						if(!EP_DEBUG):
							$this->my_serveur->serveur_erreur(404);
						endif;
					endif;
				else:
					$this->my_serveur->redirect($this->my_serveur->page_courante(array('nouveaute_id'=>$nouveaute_id)));
				endif;
			else:
				$ces_nouveautes = new ep_nouveautes();
				$ces_nouveautes->contenu_erreur_nouveaute($this->my_configuration->contenu_erreur_nouveautes->propriete_liste_copie());
				$ces_nouveautes->contenu_html_tableau($this->my_configuration->contenu_resume_tableau->propriete_liste_copie());
				$ces_nouveautes->contenu_html_ligne($this->my_configuration->contenu_resume_tableau_ligne->propriete_liste_copie());
				return $ces_nouveautes->contenu();
			endif;
		else:
			$contenu_objet = $this->my_configuration->contenu_erreur_nouveautes;
			$contenu_objet->remplacer_var('{XTPL.NOUVEAUTE.ERREUR_MESSAGE}','{RESSOURCES.RUBRIQUE.NOUVEAUTES.BDD_KO}');
			$contenu_objet->remplacer_var('{XTPL.NOUVEAUTE.ERREUR_TITRE}','{RESSOURCES.RUBRIQUE.NOUVEAUTES.BDD_KO_TITRE}');
			$contenu_tableau = $contenu_objet->propriete_liste_copie();
			return $contenu_tableau;
		endif;
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
			$this->contenu_analyse(),
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