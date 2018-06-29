<?php 

class ep_sql_requete extends ep_objet
{
	const MODE_SELECT = 'SELECT';
	const MODE_INSERT = 'INSERT';
	const MODE_UPDATE = 'UPDATE';
	const MODE_DELETE = 'DELETE';
	const SELECT_TEXTE = 'SELECT {champ_noms} ';
	const FROM_TEXTE = 'FROM {table_noms} ';
	const LIMIT_TEXTE = 'LIMIT {limit_start} , {limit_end} ';
	const INSERT_TEXTE = 'INSERT {insert_ignore} INTO {table_nom} ( {champ_clefs} ) VALUES ( {champ_valeurs} );';
	const DELETE_TEXTE = 'DELETE FROM {table_nom}{clause_where}';
	const UPDATE_TEXTE = 'UPDATE {table_nom} SET {clause_set}{clause_where}';
	
	static $sql_requete_uid=0;
	
	private $requete;
	
	public function __construct(array $array)
	{
		$this->objet_id_initialiser(++self::$sql_requete_uid);
		$params = new ep_params($array);
		$requete = '';
		switch ($params->propriete_lire('mode',ep_sql_requete::MODE_SELECT)):
			case ep_sql_requete::MODE_SELECT:
				$requete = $this->mode_select($params->requete);
			break;
			case ep_sql_requete::MODE_INSERT:
				$requete = $this->mode_insert($params->requete,$params->propriete_lire('insert_ignore',false));
			break;
			case ep_sql_requete::MODE_UPDATE:
				$requete = $this->mode_update($params->requete);
			break;
			case ep_sql_requete::MODE_DELETE:
				$requete = $this->mode_delete($params->requete);
			break;
		endswitch;
		$this->requete = $requete;
		
	}
	
	public function __tostring()
	{
		return $this->requete;
	}
	
	private function mode_select($array)
	{
		$this->trace(__FUNCTION__,__LINE__,'[START]['.print_r($array,true).']');
		$params = new ep_params($array);
		$where_statut = $params->propriete_statut('clause_where');
		$this->trace(__FUNCTION__,__LINE__,'[WHERE_STATUT]['.$where_statut.']');
		$limit_statut = $params->propriete_statut('clause_limit');
		$this->trace(__FUNCTION__,__LINE__,'[LIMIT_STATUT]['.$limit_statut.']');
		$have_statut = $params->propriete_statut('clause_have');
		$this->trace(__FUNCTION__,__LINE__,'[HAVE_STATUT]['.$have_statut.']');
		$texte = new ep_texte('{clause_select}{clause_from}{clause_where}{clause_limit}{clause_have};');
		
		$texte->texte_var('clause_select' , $this->clause_select($params->clause_select));
		$texte->texte_var('clause_from' , $this->clause_from($params->clause_from));
		
		if($where_statut):
			$texte->texte_var('clause_where',$this->clause_where($params->clause_where));
		else:
			$texte->texte_var('clause_where','');
		endif;
		
		if($limit_statut):
			$texte->texte_var('clause_limit',$this->clause_limit($params->clause_limit));
		else:
			$texte->texte_var('clause_limit','');
		endif;
		
		if($have_statut):
			$texte->texte_var('clause_have',$this->clause_have($params->clause_have));
		else:
			$texte->texte_var('clause_have','');
		endif;
		return (string)$texte;
		
	} 
	
	private function mode_insert($array,$insert_ignore=false)
	{
		$params = new ep_params($array);
		$table = $params->table_nom;
		$champs = $params->table_champs;
		$champ_clefs = array();
		$champ_valeurs = array();
		foreach($champs as $clef => $valeur):
			$champ_clefs[] = $clef;
			$champ_valeurs[] = $valeur; 	
		endforeach;
		$texte = new ep_texte( ep_sql_requete::INSERT_TEXTE );
		$texte
			->texte_var('insert_ignore',($insert_ignore) ? 'IGNORE' : '' )
			->texte_var('table_nom',$table)
			->texte_var('champ_clefs',implode( ' , ' , $champ_clefs))
			->texte_var('champ_valeurs' , implode (' , ' , $champ_valeurs))
		;
		return (string)$texte;
	}
	
	private function mode_update($array)
	{
		$params = new ep_params($array);
		$table = $params->table_nom;
		$champs = $params->table_champs;
		
		$clause_set = '';
		foreach($champs as $clef => $valeur):
			$clause_set .= $clef . ' = ' . $valeur . ' ';
		endforeach;
		$texte = new ep_texte( ep_sql_requete::UPDATE_TEXTE );
		$texte
			->texte_var('table_nom',$table)
			->texte_var('clause_set', $clause_set)
			->texte_var('clause_where',$this->clause_where($params->clause_where))
		;
		return (string)$texte;
	}

	private function mode_delete($array)
	{
		$params = new ep_params($array);
		$table = $params->table_nom;
		$texte = new ep_texte( ep_sql_requete::DELETE_TEXTE );
		$texte
			->texte_var('table_nom',$table)
			->texte_var('clause_where',$this->clause_where($params->clause_where))
		;
		return (string)$texte;
	}
	
	private function clause_select($array)
	{
		$clause_texte = new ep_texte ('SELECT ');
		foreach ($array as $clef => $valeur):
			if(is_numeric($clef)):
				// Pas d'alias
				if($valeur=='*'):
					$clause_texte->texte_ajouter($valeur);
				else:
					$clause_texte->texte_ajouter(ep_texte::s_encadrer($valeur, ' '));
				endif;
			else:
				$clause_texte
					->texte_ajouter(ep_texte::s_encadrer($clef, ' '))
					->texte_ajouter(' AS ')
					->texte_ajouter(ep_texte::s_encadrer($valeur, '`'))
				;
			endif;
		endforeach;
		$clause_texte->texte_ajouter(' ');
		return (string)$clause_texte;
	}
	
	private function clause_from($array)
	{
		$clause_from = new ep_texte(' FROM ');
		if(is_array($array)):
			$i=0;
			foreach ($array as $clef => $valeur):
				if($i > 0):
					$clause_from->texte_ajouter(' , ');
				endif;
				if(is_numeric($clef)):
					// Pas d'alias	
					$clause_from->texte_ajouter(ep_texte::s_encadrer($valeur, '`'));
				else:
					$clause_from
						->texte_ajouter(ep_texte::s_encadrer($clef, '`'))
						->texte_ajouter(' AS ')
						->texte_ajouter(ep_texte::s_encadrer($valeur, '`'))
					;
				endif;
				$i++;
			endforeach;
			$clause_from->texte_ajouter('  ');
		else:
			$clause_from->texte_ajouter((string)$array);
		endif;
		return (string)$clause_from;
	}
	
	private function clause_where($array)
	{
		$where = new ep_texte(' WHERE ');
		foreach($array as $valeur):
			if(isset($valeur['conjonction'])):
				$where->texte_ajouter( ep_texte::s_encadrer($valeur['conjonction'], ' ') ) ;
			endif;
			switch($valeur['valeur_type']):
				
					
				case 'nombre':
					$where
						->texte_ajouter ($valeur['champ'])
						->texte_ajouter ($valeur['condition'])
						->texte_ajouter (ep_sql_requete::champ_nombre($valeur['valeur']))
					;
				break;
				
				case 'NULL':
					$where
						->texte_ajouter ($valeur['champ'])
						->texte_ajouter ($valeur['condition'])
						->texte_ajouter ('null')
					;
				break;
				
				case 'texte':
				default:
					$where
						->texte_ajouter($valeur['champ'])
						->texte_ajouter($valeur['condition'])
						->texte_ajouter(ep_sql_requete::champ_texte_proteger($valeur['valeur']) )
					;
				break;
				
			endswitch;
		endforeach;
		$where->texte_ajouter(' ');
		$this->trace(__FUNCTION__,__LINE__,'[RETURN] . ['.(string)$where.']');
		return (string)$where;
	}
	
	private function clause_limit($array)
	{
		$params = new ep_params($array);
		$texte = new ep_texte (ep_sql_requete::LIMIT_TEXTE);
		$texte
			->texte_var('limit_start',$params->propriete_lire('limit_start',0))
			->texte_var('limit_end',$params->propriete_lire('limit_end',20))
		;
		return $texte;
	}
	
	private function clause_have($array)
	{
		
	}
		
	public static function s_limit($page_nombre,$page_nom='page')
	{
		if
			(
				isset($_GET[$page_nom])
			)
		:
			$page = intval($_GET[$page_nom]);
		else:
			$page = 0;
		endif;
		$limit_start = $page * $page_nombre; 	
		$limit_end = $page_nombre; 	
		return array
			(
				'limit_start' => $limit_start,
				'limit_end' => $limit_end
			)
		;  	
	}
	
	public static function champ_nombre($texte)
	{
		if(is_numeric($texte)):
			return $texte;
		endif;
		if(is_null($texte)):
			return 0;
		endif;
	}
	
	public static function champ_texte_proteger($texte)
	{
		switch (EP_IBDD):
			case 'mysql':
				if(EP_BDD_STATUT):
					$fonction_nom = 'mysql_real_escape_string';
				else:
					$fonction_nom = 'addslashes';
				endif;
			break;
			default:
				$fonction_nom = 'addslashes';
			break;
		endswitch;
		$texte_protege = new ep_texte($texte);
		$texte_protege
			->stripslashes()
			->$fonction_nom()
			->encadrer(ep_texte::apostrophe)
		;
		return $texte_protege;
	}
	
	public static function s_sql_insert($table,$champs,$insert_ignore=false)
	{
		return new ep_sql_requete
		(
			array
			(
				'mode'=> ep_sql_requete::MODE_INSERT,
				'requete'=> array
				(
					'table_nom' => $table,
					'table_champs'=> $champs
				),
				'insert_ignore' => $insert_ignore
			)
		);
	}
	
	public static function s_sql_update($_params)
	{
		return new ep_sql_requete
		(
			array
			(
				'mode'=> ep_sql_requete::MODE_UPDATE,
				'requete'=> $_params
			)
		);
	}
	
}
?>