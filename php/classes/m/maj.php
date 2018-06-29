<?php

class ep_maj extends ep_objet
{
	public $nouveaute_id;
	private $maj_enregistrement;
	private $webmestre_enregistrement;
	
	private $html;
	
	
	public function __construct($array)
	{
		if(!is_array($array)):
			$params = new 
				ep_params
				(
					array
					(
						'mode' => 'sql',
						'nouveaute_id' => $array
					)
				)
			;
		else:
			$params = new ep_params($array);	
		endif;
		switch($params->propriete_lire('mode','sql')):
			case 'sql':
				$this->maj_enregistrement = new ep_enregistrement
				(
					array
					(
						'table_nom' => EP_TABLE_NOUVEAUTES,
						'id_colonne_valeur'=>  $params->propriete_lire('nouveaute_id') ,
						'id_colonne_nom' => 'nouveaute_id'
					)
				);
			break;
			case 'array':
				$this->enregistrement = new ep_enregistrement_sql
				(
					array
					(
						'table_nom' => EP_TABLE_NOUVEAUTES,
						'propriete_liste'=>  $params->propriete_lire('propriete_liste') ,
						'id_colonne_nom' => 'nouveaute_id'
					)
				);
			break;
		endswitch;	
		$this->statut = $this->maj_enregistrement->statut();
		if($this->statut):
			$this->nouveaute_id = $this->maj_enregistrement->nouveaute_id;
			$this->webmestre_enregistrement = new ep_enregistrement
			(
				array
				(
					'table_nom' => EP_TABLE_WEBMESTRES,
					'id_colonne_valeur'=>  $this->maj_enregistrement->webmestre_id ,
					'id_colonne_nom' => 'webmestre_id'
				)
			);
 		endif;
	}
	
	public function contenu_html($html)
	{
		if(is_array($html)):
			$this->html = $html[0];
		else:
			$this->html = $html;
		endif;
		if($this->html instanceof ep_html):
			$this->html->remplacer_var('{XTPL.NOUVEAUTE.NOUVEAUTE_ID}',$this->nouveaute_id);
		endif;
	}
	
	public function xtpl_variable($clef)
	{
		return '{XTPL.NOUVEAUTE.'.strtoupper($clef).'}';
	}
	
	function contenu($commentaire_statut=false)
	{
		$this->html->remplacer_vars
			(
				array
				(
					$this->xtpl_variable('nouveaute_id') => $this->maj_enregistrement->nouveaute_id,
					$this->xtpl_variable('date') => $this->maj_enregistrement->nouveaute_date,
					$this->xtpl_variable('titre') => $this->maj_enregistrement->nouveaute_titre,
					$this->xtpl_variable('texte') => $this->maj_enregistrement->nouveaute_texte,
					$this->xtpl_variable('auteur') => $this->webmestre_enregistrement->webmestre_nom,
					$this->xtpl_variable('auteur_signature') => '',
					
				)
			)
		;
		/**
		var_dump($this->enregistrement);
		ep_variables::variable_ajouter();
		ep_variables::variable_ajouter($this->xtpl_variable('auteur'),'exostum');
		ep_variables::variable_ajouter($this->xtpl_variable('date'),ep_date::format($this->enregistrement->nouveaute_date));
		if($commentaire_statut):
			/**
			$ces_commentaires = new ep_commentaires('nouveautes',$this->enregistrement->nouveaute_id);
			ep_variables::variable_ajouter('{XTPL.NOUVEAUTE.MESSAGES}',$ces_commentaires->commentaires_tableau(true));
			ep_variables::variable_ajouter('{XTPL.NOUVEAUTE.PAGINATION.HAUT}',$ces_commentaires->commentaire_pagination('haut'));
			ep_variables::variable_ajouter('{XTPL.NOUVEAUTE.PAGINATION.BAS}',$ces_commentaires->commentaire_pagination('bas'));
			/**
		else:
			ep_variables::variable_ajouter($this->xtpl_variable('MESSAGES'),'');
			ep_variables::variable_ajouter($this->xtpl_variable('PAGINATION.HAUT'),'');
			ep_variables::variable_ajouter($this->xtpl_variable('PAGINATION.BAS'),'');
		endif;
		/**/
		
		$contenu = array();
		
		$contenu[] = $this->debug_commentaire(__FUNCTION__);
		$contenu[] = $this->html;
		$contenu[] = $this->debug_commentaire(__FUNCTION__);
		
		return $contenu;
		
	}
	
	function afficher_maj($b_coms=false)
	{
		$texte = array();
		
		if($b_coms):
			/* @Var $lr_sql ep_sql */
			$lr_sql = &$GLOBALS['lr_sql'];
			
			$pagination = new ep_pagination
			(
				array
				(
					'unit_par_page'=>10,
					'table_compteur_overdrive'=> $this->enregistrement->maj_coms
				)
			);
			$str_pagination = $pagination->pagination_texte;
											
			$lr_sql->requete('SELECT * FROM codesgratis_coms WHERE com_type=\'maj\'  AND com_type_id=\''. $this->enregistrement->maj_id .'\' ORDER BY com_date DESC LIMIT '. $pagination->clause_limit_texte);
			$sql_coms = array();
			$sql_membre_ids = array();
			while($lr_sql->prochain_enregistrement_a()):
				$sql_com = $lr_sql->enregistrement_a;
				$sql_coms[$sql_com['com_id']] = new ep_enregistrement_sql(array('champs'=>$lr_sql->enregistrement_a ,'table'=>'codesgratis_coms','champ_id'=>'com_type_id'));
				$sql_membre_ids[$sql_com['membre_id']] = $sql_com['membre_id'];
			endwhile;
		endif;
		
		//$contenu = str_replace('<br />','<br>',nl2br(stripslashes($this->enregistrement->maj_texte)));
			
		$texte[] = '<div class="maj" id="maj_' . $this->enregistrement->maj_id . '">';
		$texte[] = FP_TAB . '<h3>';
		if($b_coms): 
			$texte[] = bbcode_replace($this->enregistrement->maj_titre); 	
		else:
			$texte[] = FP_TAB .FP_TAB .  '<a href="index.php?maj_id='.$this->enregistrement->maj_id.'">' . bbcode_replace($this->enregistrement->maj_titre) . '</a>';
		endif;
		$texte[] = FP_TAB . FP_TAB . '<em>par ' . $this->enregistrement->maj_auteur . ' , le ' . format_date($this->enregistrement->maj_date) . '</em>';
		$texte[] = FP_TAB . '</h3>';
		
		$texte[] = FP_TAB . '<p>';
		$texte[] = FP_TAB . FP_TAB . str_replace(FP_LIGNE , FP_LIGNE . str_repeat(FP_TAB,5) ,bbcode_replace($contenu));
		$texte[] = FP_TAB . '</p>';
		if(!$b_coms):
			$texte[] = FP_TAB .  '<a href="index.php?maj_id=' . $this->enregistrement->maj_id . '">'.$this->enregistrement->maj_coms.' commentaires</a>';
		else:
			if($GLOBALS['my_membre']->membre_existe()):
				$texte[] = '<table>';
				$texte[] = '<tr><th colspan="2">Laisser un commentaires</th></tr>';
				$texte[] = '<tr><td>Aperçu</td><td><p id="com_message_infos"></p></td></tr>';
				$texte[] = '<form action="' . page_courante ( array ( 'maj_id' => $this->enregistrement->maj_id ) ) . '" method="post">';
				$texte[] = '<tr><td>Votre commentaire :</td><td><textarea class="bbcode" style="width:98%;" name="com_message" id="com_message">Entrez ici votre commentaire</textarea></td></tr>';
				$texte[] = '<tr><td colspan="2"><input type="submit" name="submit_com" id="submit_com" value="Envoyer votre commentaire"></tr></td>';
				$texte[] = '</form>';
				$texte[] = '</table>';
			endif;
			
			if
				(
					count($sql_coms) > 0
				)
			:
				$liste_membre = 
					ep_sql::liste
					(
						array
						(
							'table' => 'codesgratis_membres',
							'table_id'=>'membre_id',
							'liste' => $sql_membre_ids
						)
					)
				;
				$texte = array_merge($texte, $str_pagination);
				foreach($sql_coms as $sql_com):
					if(isset($liste_membre[$sql_com->membre_id])):
						$texte[] = '<table>';
						$texte[] = '<tr>';
						$texte[] = '<th rowspan="2" width="100">';
						$texte[] =  membre_pseudo($sql_com->membre_id);
						$texte[] =  membre_avatar($liste_membre[$sql_com->membre_id]);
						$texte =  array_merge($texte,membre_vip($liste_membre[$sql_com->membre_id]));
						$texte[] =  '</td>';
						$texte[] = '<th>' . format_date($sql_com->com_date) . '</th></tr>';
						$texte[] = '<tr><td colspan="2">' . bbcode_replace(stripslashes($sql_com->com_texte)) . '</td></tr>';
						$texte[] = '<tr><td colspan="2">' . bbcode_replace(stripslashes($liste_membre[$sql_com->membre_id]->membre_signature)) . '</td></tr>';
						$texte[] = '</table>';
					endif;
				endforeach;
			
				$texte = array_merge($texte, $str_pagination);
			endif;
		endif;
		$texte[] = '</div>';
		return format_texte($texte,3);
	}
}
?>