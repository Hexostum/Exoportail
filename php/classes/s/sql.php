<?php
class ep_sql extends ep_objet
{
	const ERROR_MODE_EXCEPTION = 1;
	const ERROR_MODE_IGNORE = 2;
	public $table_prefix;
	public $enregistrement_o;
	public $enregistrement_a;
	public $sql_erreur;
	public $sql_erreur_no;
	
	protected $ca_connexion = array();
	protected $sql_functions = array();
	
	private $sql_link;
	private $res_sql;
	private $requetes_nombre = 0;
	private $requetes = array();
	public $erreur_mode;
	protected $interface_type;
		
	function __construct()
	{
		parent::__construct();
		{				
			$config = new ep_xml('sql_' . EP_MODE);
			$this->ca_connexion = array
			(
				'sql_hote' => $config->configs['sql_hote']->texte_enfants('localhost'),
				'sql_utilisateur' => $config->configs['sql_utilisateur']->texte_enfants('root'),
				'sql_passe' => $config->configs['sql_passe']->texte_enfants(''),
				'sql_base_nom' => $config->configs['sql_base_nom']->texte_enfants('exoportail')
			);
			$this->table_prefix = $config->configs['sql_table_prefix']->texte_enfants('ep_');
			$this->interface_type = $config->configs['sql_interface']->texte_enfants('mysql');
			define('EP_IBDD',$this->interface_type);
			unset($config);
		}
		
		{
			$config = new ep_xml('sql_' . $this->interface_type );
			$this->sql_functions = array
			(
				'connection' => $config->configs['connection']->texte_enfants('mysql_connect'),
				'erreur' => $config->configs['erreur']->texte_enfants('mysql_error'),
				'erreur_no' => $config->configs['erreur_no']->texte_enfants('mysql_errno'),
				'selection_bdd' =>$config->configs['selection_bdd']->texte_enfants('mysql_select_db'),
				'requete' => $config->configs['requete']->texte_enfants('mysql_query'),
				'resultat' => $config->configs['resultat']->texte_enfants('mysql_result'),
				'get_enregistrement_objet' => $config->configs['get_enregistrement_objet']->texte_enfants('mysql_fetch_object'),
				'get_enregistrement_array' => $config->configs['get_enregistrement_array']->texte_enfants('mysql_fetch_array'),
				'set_encoding' =>$config->configs['set_encoding']->texte_enfants('mysql_set_charset')
			);
			$this->fonctions_verification($config->fichier_nom);	
			unset($config);
		}
		$this->statut = $this->connection(); 
	}
	
	private function fonctions_verification($fichier_nom)
	{
		$fonction_bonnes=0;
		$fonction_mauvaises=0;
		foreach ($this->sql_functions as $fonction_nom):
			if(function_exists($fonction_nom)):
				$fonction_bonnes++;
			else:
				$fonction_mauvaises++;
			endif;
		endforeach;
		if($fonction_mauvaises>0):
			throw (new exception('[EXOPORTAIL] ['.str_replace(EP_CHEMIN,'',$fichier_nom).'] [XML KO]'));
		endif;
	}
	
	private function connection()
	{
		$this->sql_link = 
			@$this->sql_functions['connection']
			(
				$this->ca_connexion['sql_hote'],
				$this->ca_connexion['sql_utilisateur'],
				$this->ca_connexion['sql_passe']
			)
		;
		$this->trace(__FUNCTION__,__LINE__, '[this.sql_link] = ['.$this->sql_link.']', $this->sql_functions['erreur']() );
		if(! $this->sql_link):
			$this->trace(__FUNCTION__,__LINE__, '[CONNECTION][this.statut] = ['.$this->statut.']', $this->sql_functions['erreur']() );
			$statut = false;
		else:
			$statut = $this->sql_functions['selection_bdd']($this->ca_connexion['sql_base_nom'],$this->sql_link);
			$this->trace(__FUNCTION__,__LINE__, '[SELECTION BASE]['.$this->ca_connexion['sql_base_nom'].'][this.statut] = ['.$this->statut.']', $this->sql_functions['erreur']() );
		endif;
		$this->ca_connexion = null;
		$this->trace(__FUNCTION__,__LINE__, '[this.ca_connexion] = [NULL]'  );
		define('EP_BDD_STATUT',$statut);
		return $statut;
	}
	/**
	 * permet de fixer l'encoding interne de la librairie SQL
	 *
	 * @param string $encoding
	 * @return bool
	 */
	function set_encoding($encoding)
	{
		//mysql_set_charset('utf8');
		if($this->statut):
			if(function_exists($this->sql_functions['set_encoding'])):
				exoportail::debug_echo('['.$this->sql_functions['set_encoding'].'] [OK]',__FILE__,__LINE__);
				$this->sql_functions['set_encoding']($encoding);
			else:
				exoportail::debug_echo('['.$this->sql_functions['set_encoding'].'] [KO]',__FILE__,__LINE__);
			endif;
			return true;
		else:
			return false;
		endif;
	}
	function tables_init()
	{
		$config = new ep_xml('tables');
		$tables = $config->configs->html_enfants('config');
		foreach($tables as $clef => $valeur):
			if($valeur->attribut_statut('table_prefix')):	
				define('EP_TABLE_'. strtoupper($clef), $valeur->attribut_valeur('table_prefix') .  $valeur->texte_enfants());
			else:
				define('EP_TABLE_' . strtoupper($clef), $this->table_prefix . $valeur->texte_enfants());	
			endif;
			exoportail::debug_echo('[' . 'EP_TABLE_' . strtoupper($clef) .'] ['. constant('EP_TABLE_' . strtoupper($clef)) .']');
			$this->trace(__FUNCTION__, __LINE__, '[' . 'EP_TABLE_' . strtoupper($clef) .'] ['. constant('EP_TABLE_' . strtoupper($clef)) .']');
		endforeach;
	}
	/**
	 * Lance une requête sur le serveur SQL (Lance-requête POWA)
	 *
	 * @param string $sql
	 * @return bool
	 */
	function requete($sql,$exception_on_error=true)
	{
		if($this->statut):
			$this->res_sql = $this->sql_functions['requete']($sql,$this->sql_link);
			$this->trace(__function__,__LINE__,'[SQL] ['.$sql.'] [RESULT] ['.$this->res_sql.']',$this->sql_functions['erreur']());
			$this->requetes[++$this->requetes_nombre] = array
			(
				'requete_texte' => $sql,
				'requete_erreur' => $this->sql_functions['erreur']()
			);
			$this->sql_erreur = $this->sql_functions['erreur']();
			$this->sql_erreur_no = $this->sql_functions['erreur_no']();
			$requete_statut = (bool)$this->res_sql;
			if($requete_statut):
				return true;
			else:
				if(($this->erreur_mode==ep_sql::ERROR_MODE_EXCEPTION) && ($exception_on_error) ):
					
					throw(new exception(exoportail::debug_echo('requete['.$sql.'] [KO] ['.$this->sql_erreur.']',__FILE__,__LINE__,'return')));
				else:
					exoportail::debug_echo('requete['.htmlentities($sql).'] [KO] ['.htmlentities($this->sql_erreur).']',__FILE__,__LINE__);
					return false;
				endif;
			endif;
		else:
			return false;
		endif;
	}
	
	/**
	 * Prépare le prochain enregistrement sous forme d'objet et retourne le statut
	 *
	 * @return bool
	 */
	function prochain_enregistrement_o()
	{
		if($this->statut):
			if(is_resource($this->res_sql)):
				$this->enregistrement_o = @$this->sql_functions['get_enregistrement_objet']($this->res_sql);
				$this->trace(__FUNCTION__,__LINE__, '['.(bool)$this->enregistrement_o.']' , $this->sql_functions['erreur']());
				return is_object($this->enregistrement_o);
			else:
				return false;
			endif;
		else:
			return false;
		endif;		
	}
	
	/**
	 * Prépare le prochain enregistrement sous forme de tableau et retourne le statut
	 *
	 * @return unknown
	 */
	function prochain_enregistrement_a()
	{
		if($this->statut):
			if(is_resource($this->res_sql)):
				$this->enregistrement_a = @$this->sql_functions['get_enregistrement_array']($this->res_sql,MYSQL_ASSOC);
				$this->trace(__FUNCTION__,__LINE__, '['.(string)print_r($this->enregistrement_a,true).']' , $this->sql_functions['erreur']());
				return is_array($this->enregistrement_a);
			else:
				return false;
			endif;
		else:
			return false;
		endif;
	}
	
	/**
	 * Retourne le résultat d'une requête SQL unaire, retourne la valeur defaut si pas de retour sql
	 *
	 * @param string $sql
	 * @param string $defaut
	 * @return string
	 */
	function resultat($sql,$defaut=null)
	{
		$this->res_sql = $this->sql_functions['requete']($sql,$this->sql_link);
		$this->trace(__function__,__LINE__,'[SQL] ['.$sql.'] [RESULT] ['.$this->res_sql.']',$this->sql_functions['erreur']());
		$this->requetes[++$this->requetes_nombre] = array
		(
			'requete_texte' => $sql,
			'requete_erreur' => $this->sql_functions['erreur']()
		);
		if(is_resource($this->res_sql)):
			if(mysql_num_rows($this->res_sql)>0):
				$resultat = $this->sql_functions['resultat']($this->res_sql,0); 
				$this->trace(__FUNCTION__,__LINE__, '[RETURN] ['.$resultat.']');
				return $resultat;
			else:
				return $defaut;
			endif;
		else:
			return $defaut;
		endif;
	}
	
	public static function liste($params)
	{
		/* @var $lr_sql ep_sql */
		$lr_sql = $GLOBALS['lr_sql'];
		
		$params = new ep_params($params);
		$select = $params->champ_valeur_g('select','*');
		$group_by = $params->group_by;
		$order_by = $params->order_by;
		$clef_tableau  = $params->clef_tableau;
		if($group_by):
			$sql = 'SELECT '.$select.' FROM '.$params->table.' WHERE '.$params->table_id.'  IN ('.implode(' , ',$params->liste).') GROUP BY '.$group_by;
		else: 
			$sql = 'SELECT '.$select.' FROM '.$params->table.' WHERE '.$params->table_id.'  IN ('.implode(' , ',$params->liste).')';
		endif;
		if($order_by):
			$sql .= ' ORDER BY ' . $order_by;
		endif;
		
		if($lr_sql->requete($sql)):
			while($lr_sql->prochain_enregistrement_a()):
				$sql_enr = $lr_sql->enregistrement_a;
				if($clef_tableau):
					$liste[$sql_enr[$clef_tableau]] = new ep_enregistrement_sql(array('champs'=>$sql_enr,'table'=>$params->table,'champ_id'=>$params->table_id)); 
				else:
					$liste[$sql_enr[$params->table_id]] = new ep_enregistrement_sql(array('champs'=>$sql_enr,'table'=>$params->table,'champ_id'=>$params->table_id)); 
				endif;
			endwhile;
			return $liste;
		else:
			return array();
		endif;
	}
}
?>