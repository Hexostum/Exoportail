<?php
class ep_session extends ep_objet
{
	public $session_statut;
	public $session_id;
	
	public $my_membre;
	public $my_parrain;
	private $l_sql;
	
	public function __construct()
	{
		$this->l_sql = &$GLOBALS['exoportail']->lr_sql;
		
		
		
		
		$this->cookie();
		
		session_start();
		$this->trace(__FUNCTION__,__LINE__,'SESSION START');
		$this->session_id = session_id();
		$this->trace(__FUNCTION__,__LINE__,'this.session_id = ' . $this->session_id);
		$this->cookie_envoyer ($this->session_id,60*60*24*30);
		
		$my_session = new ep_enregistrement
		(
			array
			(
				'table_nom' => EP_TABLE_SESSIONS,
				'id_colonne_valeur' => $this->session_id,
				'id_colonne_nom'=> 'session_id',
				'id_colonne_valeur_type' => 'texte'
			)
			 
		);

		if
			(
				$my_session->statut()
			)
		:
			$my_session->session_date = time();
			
			if($my_session->membre_id=='MEMBRE_ID'):
				$my_membre = new ep_membre(NULL);				
				exoportail::debug_echo('no_membre',__FILE__,__LINE__);
			else:
				$my_membre = new ep_membre($my_session->membre_id);				
				exoportail::debug_echo('membre',__FILE__,__LINE__);
			endif;
			if
				(
					$my_membre->statut()
				)
			:
				
				/**/
				$sql_texte = new ep_sql_requete
				(
					array
					(
						'mode' => ep_sql_requete::MODE_DELETE,
						'requete' => array 
						(
							'table_nom' => EP_TABLE_SESSIONS,
							'clause_where' => array
							(
								array('valeur_type'=>'nombre','champ'=>'membre_id','condition'=> '=','valeur'=> $my_session->membre_id),
								array('conjonction' => 'AND', 'valeur_type'=>'nombre','champ'=>'session_id','condition'=> ' <> ','valeur'=> ep_sql_requete::champ_texte_proteger($this->session_id))
							)
						)
					)
				); //mysql_query('DELETE FROM codesgratis_sessions WHERE membre_id='.$my_session->membre_id.' AND session_id <> \'' . $this->session_id . '\'');
				$this->l_sql->requete((string)$sql_texte);
			endif;
		else:
			if($this->l_sql->sql_erreur_no!=1146):
				$sql_texte = ep_sql_requete::s_sql_insert
				(
					EP_TABLE_SESSIONS,
					array
					(
						'session_id' => ep_sql_requete::champ_texte_proteger($this->session_id),
						'session_ip' => ep_sql_requete::champ_texte_proteger($_SERVER['REMOTE_ADDR']),
						'session_date' => time(),
						'membre_id' => 'NULL',
						'session_referer' => ep_sql_requete::champ_texte_proteger(@$_SERVER['HTTP_REFERER']),
						'session_page'=> ep_sql_requete::champ_texte_proteger($_SERVER['PHP_SELF'])
							
					),
					true
				);
				$this->l_sql->requete((string)$sql_texte);
				$my_membre = new ep_membre(NULL);
			else:
				$my_membre = new ep_membre(NULL);
			endif;
		endif;
		$this->my_membre = $my_membre;
		$this->my_parrain = new ep_membre($my_membre->enregistrement->membre_parrain_id);
	}
	
	public function tables_verifier()
	{
		ep_todo('tables_verifier()');
		/**
		 $this->session_statut = ep_sql::tables_verifier(EP_TABLES_SESSIONS);
		/**/
	}
	
	public function cookie()
	{
		if
			(
				isset($_COOKIE['session_id'])
			)
		:
			$this->trace(__FUNCTION__,__LINE__,'$_COOKIE[SESSION_ID] [OK]');
			$this->trace(__FUNCTION__,__LINE__,'SESSION ID ['.$_COOKIE['session_id'].']');
			session_id($_COOKIE['session_id']);
		else:
			$this->trace(__FUNCTION__,__LINE__,'$_COOKIE[SESSION_ID] [KO]');
		endif;
	}
	
	public function connectes_nombre($time)
	{
		return $this->l_sql->resultat('SELECT COUNT(session_id) FROM codesgratis_sessions WHERE session_date > '. (time() - $time)  );
	}
	
	public function cookie_envoyer($session_id , $duree)
	{
		setcookie('session_id', $session_id , time()+$duree );		
		$this->trace(__FUNCTION__,__LINE__,'SET_COOKIE(session_id, '.$session_id.' , '.(time()+$duree).')');
	}
	
	public function & my_membre()
	{
		return $this->my_membre;
	}
	
	public function purger($purge_time)
	{
		$time = time() - ($purge_time);
		$sql_texte = new ep_sql_requete
		(
			array
			(
			
				'mode' => ep_sql_requete::MODE_DELETE,
				'requete' =>
				array
				( 	
					'table_nom' => EP_TABLE_SESSIONS,
					'clause_where' => array
					(
						array('valeur_type'=>'NULL','champ'=>'membre_id','condition'=> ' is '),
						array('conjonction' => 'AND', 'valeur_type'=>'nombre','champ'=>'session_date','condition'=> ' < ','valeur'=> $time)
					)
				)
					
			)
		);
		// 'DELETE FROM codesgratis_sessions WHERE membre_id is null AND session_date < '. $time;
		$this->l_sql->requete((string)$sql_texte,false);
	}
	public function membre_overdrive()
	{
		if
			( 
				$this->my_membre->is_admin() 
			)
		:
			if
				(
					isset($_GET['amembre_id'])
				)
			:
				$this->my_membre = new ep_membre(intval($_GET['amembre_id']));
				$this->my_parrain = new ep_membre(intval($this->my_membre->enregistrement->membre_parrain_id));
			endif;
		endif;
	}
	
	public function courriel_statut(&$contenu_texte)
	{
		if($this->my_membre->statut()):
			if($this->my_membre->enregistrement->membre_courriel==''):
				$contenu_texte[] = message ('Votre adresse courriel n\'est pas renseigné. Afin de pouvoir jouer à certains jeux ou gagner certains cadeaux, votre adresse courriel est requise. <a href="compte_courriel.php">Modifier votre adresse courriel</a>' , FP_MESSAGE_ERROR);
			else:
				if($this->my_membre->enregistrement->membre_courriel_ok==0):
					$contenu_texte[] = message ('Votre adresse courriel est renseigné, vous avez reçu un courriel contenant un code vous permettant de valider votre adresse courriel. <a href="compte_courriel.php">Rentrez le code</a>' , FP_MESSAGE_REPONSE);
				endif;
			endif;
		endif;
	}
	
	public function messagerie_statut(&$contenu_texte)
	{
		$messages_lu = $this->l_sql->resultat('SELECT count(message_id) FROM codesgratis_messagerie where message_to_lu=0 and message_code=0 and message_to_id = '.$this->my_membre->enregistrement->membre_id);
		if($messages_lu > 0):
			$contenu_texte[] = message ('Vous avez '.$messages_lu.' message(s) non lu(s)' , FP_MESSAGE_INFOS);
		endif;
	}
	
	public function membre_actualiser()
	{
		
	}
	
	public function maj_statut()
	{
		if
			(
				!isset($_GET['anomaj'])
			)
		:
			if(FP_MISE_A_JOUR):
				if
					(
						! ( $this->my_membre->is_admin() ) 
					)
				:
					$GLOBALS['exoportail']->redirect('miseajour.php');
				endif;
			endif;
		endif;
	}
}
?>