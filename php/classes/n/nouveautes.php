<?php
class ep_nouveautes extends ep_objet 
{
	/**
	 * 
	 *
	 * @var ep_html
	 */
	private $html_tableau;
	
	/**
	 * 
	 *
	 * @var ep_html
	 */
	private $erreur_nouveautes;
	
	/**
	 * 
	 *
	 * @var ep_html
	 */
	private $html_tableau_ligne;
	
	private $html_tableau_lignes = array();
	
	private $nouveautes_sql = array();
	
	private $pagination;
	
	public function __construct()
	{
		$this->objet_id_initialiser();
		
		$l_sql = &$GLOBALS['exoportail']->lr_sql;
		
		/* @var $l_sql ep_sql */
		($l_sql instanceof ep_sql) or exit('<!-- ERROR -->');
		
		$page_courante = $GLOBALS['exoportail']->variable_serveur_lire('get','page',0);
		
		$sql_requete = new ep_sql_requete
		(
			array
			(
				'mode' => ep_sql_requete::MODE_SELECT,
				'requete' => 
				array
				(
					'clause_select' =>	array('*'),
					'clause_from' => array(EP_TABLE_NOUVEAUTES,EP_TABLE_SITES,EP_TABLE_WEBMESTRES),
					'clause_where' => array
					(
						array('champ' => EP_TABLE_NOUVEAUTES.'.site_id' , 'valeur' => EP_TABLE_SITES.'.site_id' , 'condition' => '=' , 'valeur_type' => 'nombre'),
						array('champ' => EP_TABLE_SITES.'.site_nom' , 'valeur' => EP_SITE_NOM_2 , 'condition' => '=' , 'valeur_type' => 'texte','conjonction' => 'AND')
					),
					'clause_limit' => ep_sql_requete::s_limit(2)
				)
			)
		);
		if($l_sql->requete($sql_requete)):
			$this->statut = false;
			while($l_sql->prochain_enregistrement_o()):
				
				$this->statut = true;
				$nouveaute = $l_sql->enregistrement_o;
				$this->nouveautes_sql[$l_sql->enregistrement_o->nouveaute_id] = $nouveaute;
			endwhile;
			if($this->statut):
				$this->pagination =  new ep_pagination
				(
					array
					(
						'table_compteur' => 'nouveaute_id',
						'table_nom' => EP_TABLE_NOUVEAUTES
					)
				);
				
			else:
				if($page_courante==0):
					$this->erreur_titre = '';
					$this->erreur_message = '{RESSOURCES.RUBRIQUE.NOUVEAUTES.PAS_ENCORE}';
				else:
					/**
					$this->statut=true;
					$this->pagination =  new ep_pagination(array('enregistrements_nombre' => 100,'enregistrements_page' => 2));
					$this->erreur_message = 'test';
					/**/
					$GLOBALS['exoportail']->serveur_erreur(404);
				endif;
			endif;
		else:
			$this->statut=false;
			$this->erreur_titre = '{RESSOURCES.RUBRIQUE.NOUVEAUTES.SQL_KO_TITRE}';
			$this->erreur_message = '{RESSOURCES.RUBRIQUE.NOUVEAUTES.SQL_KO}';
		endif;
	}
	
	public function contenu_html_tableau($html)
	{
		if(is_array($html)):
			$this->html_tableau = $html[0];
		else:
			$this->html_tableau = $html;
		endif;
	}
	
	public function contenu_html_ligne($html)
	{
		if(is_array($html)):
			$this->html_tableau_ligne = $html[0];
		else:
			$this->html_tableau_ligne = $html;
		endif;
	}
	
	public function contenu_erreur_nouveaute($html)
	{
		if(is_array($html)):
			$this->erreur_nouveautes = ep_html::html_tag
			(
				'div',
				$html,
				array('class' => 'erreur_contenu')
			);
		else:
			$this->erreur_nouveautes = $html;
		endif;
	}
	
	private function lignes_construire()
	{
		foreach($this->nouveautes_sql as $valeur):
			$ligne = clone $this->html_tableau_ligne;
			$nouveaute_ressources = new ep_ressources('nouveaute_' . $valeur->nouveaute_id);
			$ligne->remplacer_vars
				(
					array
					(
						'{XTPL.NOUVEAUTE.NOUVEAUTE_ID}' => $valeur->nouveaute_id,
						'{XTPL.NOUVEAUTE.TITRE}' => ep_html::html_tag('a',array($nouveaute_ressources->nouveaute_titre),array('href'=>exoportail::page_courante(array('nouveaute_id'=>$valeur->nouveaute_id)))),
						'{XTPL.NOUVEAUTE.DATE}' => $valeur->nouveaute_date,
						'{XTPL.NOUVEAUTE.AUTEUR}' => ep_html::html_tag('a',array($valeur->webmestre_nom),array('href'=>exoportail::url_fabriquer(array('rubrique_id'=>'equipe','webmestre_id'=>$valeur->webmestre_id) )))
					)
				)
			;
			$this->html_tableau_lignes[] = $ligne;
			unset($ligne);
		endforeach;
	}
	
	public function contenu()
	{
		if($this->statut):
			$this->lignes_construire();
			$this->html_tableau->propriete_liste_initialiser(array_merge($this->html_tableau->propriete_liste_copie(),$this->html_tableau_lignes));
			ep_variables::variable_ajouter('{XTPL.NOUVEAUTE.NOMBRE}',$this->pagination->enregistrements_nombre);
			$contenu = array();
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
			$contenu[] = $this->pagination->pagination_texte;
			$contenu[] = $this->html_tableau;
			$contenu[] = $this->pagination->pagination_texte;
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
		else:
			$this->erreur_nouveautes->remplacer_var('{XTPL.NOUVEAUTE.ERREUR_TITRE}',$this->erreur_titre);
			$this->erreur_nouveautes->remplacer_var('{XTPL.NOUVEAUTE.ERREUR_MESSAGE}',$this->erreur_message);
			$contenu = array();
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
			$contenu[] = $this->erreur_nouveautes;
			$contenu[] = $this->debug_commentaire(__FUNCTION__);
		endif;
		return $contenu;
	}
}
?>